import UIKit
import WebKit
import Capacitor
import LocalAuthentication
import CoreLocation
import Network

/// iOS counterpart of the Android shell's MainActivity.java. The app is a Capacitor web view of
/// https://eftkad.avarewase.com; this controller adds what the site cannot do by itself:
///
///  - Face ID / Touch ID lock: a signed-in user must unlock when the app opens, and again after
///    more than a minute in the background. "Use login" signs out and goes to the sign-in page.
///  - A page that turns out to be a server-error page (Cloudflare 502, nginx 5xx) is replaced with
///    the bundled retrying page (www/offline.html#server), which returns to the site on its own.
///  - The offline page when the phone has no connection, and back to the site when it returns.
///  - Location permission is requested once per launch, after the user is past the lock, so the
///    visit form's "Use current location" button works.
///
/// The site's own JavaScript is not changed: two small scripts are injected into the web view and
/// talk to this class through the "eftkad" message handler.
class EftkadViewController: CAPBridgeViewController {

    // MARK: - Configuration

    private let siteHost = "eftkad.avarewase.com"
    private let visitsURL = URL(string: "https://eftkad.avarewase.com/visits")!
    private let loginURL = URL(string: "https://eftkad.avarewase.com/login")!
    private let messageName = "eftkad"
    private let relockAfter: TimeInterval = 60

    /// Runs at document start on every page: exposes window.EftkadNative.retry() (used by
    /// offline.html) and reports whether a login token exists, before the page renders.
    private static let bridgeScript = #"""
    (function () {
      function post(m) { try { window.webkit.messageHandlers.eftkad.postMessage(m); } catch (e) {} }
      window.EftkadNative = { retry: function () { post({ type: 'retry' }); } };
      var token = null, dark = false;
      try { token = localStorage.getItem('eftkad_token'); dark = localStorage.getItem('eftkad_theme') === 'dark'; } catch (e) {}
      post({ type: 'start', host: location.host, path: location.pathname, hasToken: !!token, dark: dark });
    })();
    """#

    /// Runs when the page has loaded: reports Cloudflare / nginx server-error pages. The site's
    /// service worker answers navigations, and the web view does not report HTTP errors for those,
    /// so the loaded page itself is inspected (same check as the Android shell).
    private static let errorPageScript = #"""
    (function () {
      if (location.host === 'localhost') { return; }  // Capacitor's own local pages (offline.html)
      var t = document.title || '';
      var bad = /(^|\|)\s*5\d\d\b/.test(t)
        || /Bad gateway|Gateway time-?out|Service Unavailable|Web server is down|Host Error/i.test(t)
        || !!document.getElementById('cf-error-details');
      if (bad) { try { window.webkit.messageHandlers.eftkad.postMessage({ type: 'servererror' }); } catch (e) {} }
    })();
    """#

    // MARK: - State

    private var lockOverlay: UIView?
    private var authenticating = false
    private var sessionUnlocked = false   // past the lock (or signed in fresh) during this launch
    private var hasToken = false
    private var backgroundedAt: Date?
    private var askedForLocation = false
    private var isOnline = true
    private let locationManager = CLLocationManager()
    private let monitor = NWPathMonitor()

    // MARK: - Capacitor hooks

    override func webViewConfiguration(for instanceConfiguration: InstanceConfiguration) -> WKWebViewConfiguration {
        let configuration = super.webViewConfiguration(for: instanceConfiguration)
        let controller = configuration.userContentController
        controller.add(ScriptMessageProxy(owner: self), name: messageName)
        controller.addUserScript(WKUserScript(source: Self.bridgeScript, injectionTime: .atDocumentStart, forMainFrameOnly: true))
        controller.addUserScript(WKUserScript(source: Self.errorPageScript, injectionTime: .atDocumentEnd, forMainFrameOnly: true))
        return configuration
    }

    override func capacitorDidLoad() {
        super.capacitorDidLoad()
        // The site has its own pull-to-refresh (Eftkad.initPullToRefresh); the native rubber band
        // would fight with it.
        webView?.scrollView.bounces = false
        webView?.allowsLinkPreview = false
    }

    override func viewDidLoad() {
        super.viewDidLoad()

        let center = NotificationCenter.default
        center.addObserver(self, selector: #selector(appDidEnterBackground), name: UIApplication.didEnterBackgroundNotification, object: nil)
        center.addObserver(self, selector: #selector(appWillEnterForeground), name: UIApplication.willEnterForegroundNotification, object: nil)

        monitor.pathUpdateHandler = { [weak self] path in
            let online = path.status == .satisfied
            DispatchQueue.main.async { self?.connectivityChanged(online: online) }
        }
        monitor.start(queue: DispatchQueue(label: "com.eftkad.app.network"))
    }

    deinit {
        monitor.cancel()
        NotificationCenter.default.removeObserver(self)
    }

    // MARK: - Messages from the page

    fileprivate func handle(_ body: [String: Any]) {
        guard let type = body["type"] as? String else { return }

        switch type {
        case "start":
            // Only the real site matters; Capacitor's local pages (offline.html) also report in.
            guard (body["host"] as? String) == siteHost else { return }
            applyStatusBar(dark: body["dark"] as? Bool ?? false)
            hasToken = body["hasToken"] as? Bool ?? false
            if !hasToken {
                sessionUnlocked = true        // signed out, or about to sign in: nothing to lock
            } else if !sessionUnlocked && biometricsAvailable() {
                showLockOverlay()
                authenticate()
            } else {
                sessionUnlocked = true
                askForLocationOnce()
            }
        case "theme":
            applyStatusBar(dark: body["dark"] as? Bool ?? false)   // the site's own dark-mode switch
        case "servererror":
            showServerErrorPage()
        case "retry":
            if isOnline { webView?.load(URLRequest(url: visitsURL)) }
        default:
            break
        }
    }

    // MARK: - Status bar

    /// The site has its own dark mode, independent of the phone's. Without this the clock and battery
    /// follow the phone's setting and become unreadable (dark text on the dark header, or the reverse).
    private func applyStatusBar(dark: Bool) {
        setStatusBarStyle(dark ? .lightContent : .darkContent)
    }

    // MARK: - Biometric lock

    private func biometricsAvailable() -> Bool {
        var error: NSError?
        return LAContext().canEvaluatePolicy(.deviceOwnerAuthenticationWithBiometrics, error: &error)
    }

    private func authenticate() {
        guard !authenticating else { return }
        authenticating = true

        let context = LAContext()
        context.localizedCancelTitle = "تسجيل الدخول بحساب / Use login"
        context.evaluatePolicy(.deviceOwnerAuthenticationWithBiometrics,
                               localizedReason: "افتح التطبيق بالبصمة / Unlock Eftkad") { [weak self] success, _ in
            DispatchQueue.main.async {
                guard let self = self else { return }
                self.authenticating = false
                if success { self.unlock() }
                // on cancel / failure the overlay stays, with its Unlock and Use login buttons
            }
        }
    }

    private func unlock() {
        sessionUnlocked = true
        removeLockOverlay()
        askForLocationOnce()
    }

    /// "Use login": forget the stored session and go to the sign-in page.
    private func signOutToLogin() {
        webView?.evaluateJavaScript("localStorage.removeItem('eftkad_token');localStorage.removeItem('eftkad_user');", completionHandler: nil)
        hasToken = false
        sessionUnlocked = true
        removeLockOverlay()
        webView?.load(URLRequest(url: loginURL))
    }

    private func showLockOverlay() {
        guard lockOverlay == nil else { return }

        let overlay = UIView()
        overlay.backgroundColor = .white
        overlay.translatesAutoresizingMaskIntoConstraints = false

        // the same image as the launch screen, so unlocking looks seamless
        let logo = UIImageView(image: UIImage(named: "Splash"))
        logo.contentMode = .scaleAspectFill
        logo.translatesAutoresizingMaskIntoConstraints = false
        overlay.addSubview(logo)

        var unlockConfig = UIButton.Configuration.filled()
        unlockConfig.title = "فتح / Unlock"
        unlockConfig.cornerStyle = .large
        unlockConfig.baseBackgroundColor = UIColor(red: 0.247, green: 0.365, blue: 0.427, alpha: 1)   // #3f5d6d
        let unlockButton = UIButton(configuration: unlockConfig, primaryAction: UIAction { [weak self] _ in self?.authenticate() })

        var loginConfig = UIButton.Configuration.plain()
        loginConfig.title = "تسجيل الدخول بحساب / Use login"
        loginConfig.baseForegroundColor = UIColor(red: 0.392, green: 0.439, blue: 0.502, alpha: 1)  // #647080
        let loginButton = UIButton(configuration: loginConfig, primaryAction: UIAction { [weak self] _ in self?.signOutToLogin() })

        let stack = UIStackView(arrangedSubviews: [unlockButton, loginButton])
        stack.axis = .vertical
        stack.spacing = 8
        stack.translatesAutoresizingMaskIntoConstraints = false
        overlay.addSubview(stack)

        view.addSubview(overlay)
        NSLayoutConstraint.activate([
            overlay.topAnchor.constraint(equalTo: view.topAnchor),
            overlay.bottomAnchor.constraint(equalTo: view.bottomAnchor),
            overlay.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            overlay.trailingAnchor.constraint(equalTo: view.trailingAnchor),

            logo.topAnchor.constraint(equalTo: overlay.topAnchor),
            logo.bottomAnchor.constraint(equalTo: overlay.bottomAnchor),
            logo.leadingAnchor.constraint(equalTo: overlay.leadingAnchor),
            logo.trailingAnchor.constraint(equalTo: overlay.trailingAnchor),

            stack.leadingAnchor.constraint(equalTo: overlay.leadingAnchor, constant: 32),
            stack.trailingAnchor.constraint(equalTo: overlay.trailingAnchor, constant: -32),
            stack.bottomAnchor.constraint(equalTo: overlay.safeAreaLayoutGuide.bottomAnchor, constant: -24),
        ])
        lockOverlay = overlay
    }

    private func removeLockOverlay() {
        lockOverlay?.removeFromSuperview()
        lockOverlay = nil
    }

    // MARK: - App lifecycle: lock again after a while in the background

    @objc private func appDidEnterBackground() {
        backgroundedAt = Date()
    }

    @objc private func appWillEnterForeground() {
        guard sessionUnlocked, hasToken, lockOverlay == nil, biometricsAvailable(),
              let since = backgroundedAt, Date().timeIntervalSince(since) > relockAfter else { return }
        showLockOverlay()
        authenticate()
    }

    // MARK: - Location permission

    /// Once per launch, a moment after the user is past the lock, so the system dialog never lands
    /// on top of the Face ID prompt. The visit form's "Use current location" button is then served
    /// by the web view's normal geolocation.
    private func askForLocationOnce() {
        guard !askedForLocation else { return }
        askedForLocation = true
        DispatchQueue.main.asyncAfter(deadline: .now() + 1.5) { [weak self] in
            guard let self = self else { return }
            if self.locationManager.authorizationStatus == .notDetermined {
                self.locationManager.requestWhenInUseAuthorization()
            }
        }
    }

    // MARK: - Offline and server-error pages

    private func errorPageURL(fragment: String?) -> URL? {
        guard let base = bridge?.config.errorPathURL else { return nil }
        guard var components = URLComponents(url: base, resolvingAgainstBaseURL: false) else { return base }
        components.fragment = fragment
        return components.url ?? base
    }

    private func showServerErrorPage() {
        guard let url = errorPageURL(fragment: "server") else { return }
        webView?.load(URLRequest(url: url))
    }

    private func connectivityChanged(online: Bool) {
        isOnline = online
        guard let current = webView?.url else { return }

        if !online {
            // the site's service worker would serve cached pages offline, so show the bundled page
            if current.host == siteHost, let url = errorPageURL(fragment: nil) {
                webView?.load(URLRequest(url: url))
            }
        } else if current.host == "localhost", current.path.hasSuffix("offline.html"), current.fragment != "server" {
            webView?.load(URLRequest(url: visitsURL))   // back online: leave the "no internet" page
        }
    }
}

/// Holds the controller weakly: WKUserContentController keeps its handlers alive for as long as the
/// web view exists, which would otherwise be a retain cycle.
@MainActor
private final class ScriptMessageProxy: NSObject, WKScriptMessageHandler {
    weak var owner: EftkadViewController?

    init(owner: EftkadViewController) {
        self.owner = owner
    }

    func userContentController(_ userContentController: WKUserContentController, didReceive message: WKScriptMessage) {
        guard let body = message.body as? [String: Any] else { return }
        owner?.handle(body)
    }
}
