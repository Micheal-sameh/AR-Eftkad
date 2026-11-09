package com.eftkad.app;

import android.Manifest;
import android.content.Context;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.hardware.fingerprint.FingerprintManager;
import android.os.CancellationSignal;
import android.widget.TextView;
import android.graphics.Typeface;
import android.graphics.drawable.GradientDrawable;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.webkit.JavascriptInterface;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebView;
import android.app.Dialog;
import android.widget.Button;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.biometric.BiometricManager;
import androidx.biometric.BiometricPrompt;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.content.FileProvider;
import androidx.appcompat.app.AlertDialog;

import android.content.Intent;
import android.provider.Settings;

import org.json.JSONObject;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.MessageDigest;
import java.util.concurrent.atomic.AtomicBoolean;

import com.getcapacitor.BridgeActivity;
import com.getcapacitor.BridgeWebViewClient;

public class MainActivity extends BridgeActivity {

    private static final String BASE_URL = "https://eftkad.avarewase.com";
    private static final String VISITS_URL = BASE_URL + "/visits";
    private static final String LOGIN_URL = BASE_URL + "/login";
    private static final String ABOUT_URL = BASE_URL + "/about/info?app=1";
    private static final String OFFLINE_URL = "file:///android_asset/public/offline.html";
    // Same page, in "server problem" mode: says the service is unavailable and retries by itself.
    private static final String SERVER_ERROR_URL = OFFLINE_URL + "#server";
    // One background availability check at a time (the retry button and the 5 s auto-retry share it).
    private final AtomicBoolean retrying = new AtomicBoolean(false);
    private static final long SPLASH_TIMEOUT_MS = 10000;
    private static final long RELOCK_AFTER_MS = 60000;

    // Runs inside the site page: is there a token, and does the API still accept it?
    // A rejected token is cleared so the site doesn't bounce visits -> login afterwards.
    private static final String CHECK_AUTH_JS =
        "(async function(){"
            + "var t=localStorage.getItem('eftkad_token');"
            + "if(!t){EftkadNative.route('login');return;}"
            + "try{"
            + "var r=await fetch('/api/user',{headers:{Accept:'application/json',Authorization:'Bearer '+t}});"
            + "if(r.status===401){localStorage.removeItem('eftkad_token');localStorage.removeItem('eftkad_user');EftkadNative.route('login');}"
            + "else{EftkadNative.route('visits');}"
            + "}catch(e){EftkadNative.route('visits');}"
            + "})();";

    // True when the loaded page is a server-error page rather than the app: Cloudflare's "502: Bad
    // gateway" family (title "host | 502: ...", element #cf-error-details) or nginx's own 5xx page.
    private static final String SERVER_ERROR_PAGE_JS =
        "(function(){var t=document.title||'';"
            + "return /(^|\\|)\\s*5\\d\\d\\b/.test(t)"
            + "||/Bad gateway|Gateway time-?out|Service Unavailable|Web server is down|Host Error/i.test(t)"
            + "||!!document.getElementById('cf-error-details');})()";

    private static final String CLEAR_AUTH_JS =
        "localStorage.removeItem('eftkad_token');localStorage.removeItem('eftkad_user');";

    private final Handler handler = new Handler(Looper.getMainLooper());
    private final Runnable splashTimeout = () -> hideSplash(getBridge().getWebView());
    private boolean splashHidden = false;
    private String target = null; // "visits" or "login", set once the auth check has run
    private long stoppedAt = 0;
    private boolean prompting = false;
    // A shared visit link (https://eftkad.avarewase.com/visits/<id>) the app was opened with; shown once
    // the user is signed in and unlocked, instead of the visits list.
    private String pendingLink = null;

    private View lockOverlay;
    // Set while the app is locked: what to run once the user passes the biometric check.
    private Runnable pendingUnlock = null;
    // The lock screen's own fingerprint UI (icon + status), driven by FingerprintManager.
    private ImageView fpIcon;
    private TextView fpStatus;
    private Button unlockButton;
    private CancellationSignal fpCancel;

    private static final int COLOR_IDLE = Color.parseColor("#3F5D6D");
    private static final int COLOR_ERROR = Color.parseColor("#C0392B");
    private static final int COLOR_OK = Color.parseColor("#2E7D32");

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        pendingLink = visitLink(getIntent());

        // Hold the splash while the app decides where to go: /visits when the stored
        // token is valid (after biometric unlock), /login otherwise. The splash is
        // hidden only once that page has loaded.
        getBridge().setWebViewClient(new BridgeWebViewClient(getBridge()) {
            // The site answered with a server error (e.g. Cloudflare's 502 while the app container
            // restarts): show our own retrying page instead of leaving the user on a dead error page.
            @Override
            public void onReceivedHttpError(WebView view, WebResourceRequest request, WebResourceResponse errorResponse) {
                super.onReceivedHttpError(view, request, errorResponse);
                if (request.isForMainFrame() && errorResponse.getStatusCode() >= 500) {
                    view.loadUrl(SERVER_ERROR_URL);
                }
            }

            // Page could not be loaded at all (connection refused/reset, TLS or DNS failure).
            @Override
            public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                super.onReceivedError(view, request, error);
                if (request.isForMainFrame() && !String.valueOf(request.getUrl()).startsWith("file:")) {
                    view.loadUrl(isOnline() ? SERVER_ERROR_URL : OFFLINE_URL);
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                if (url == null) return;
                if (!url.startsWith("file:")) {
                    // The site's service worker answers navigations, and the web view does not report
                    // HTTP errors for those (onReceivedHttpError never fires), so look at the page that
                    // actually loaded: a Cloudflare / nginx 5xx page gets replaced by our retrying page.
                    view.evaluateJavascript(SERVER_ERROR_PAGE_JS, result -> {
                        if ("true".equals(result)) view.loadUrl(SERVER_ERROR_URL);
                    });
                }
                if (url.startsWith("file:")) {
                    removeLockOverlay();
                    hideSplash(view);
                    return;
                }
                // First visits page after a fresh sign-in: ask for location (not while the biometric lock is up)
                String loadedPath = Uri.parse(url).getPath();
                if (loadedPath != null && loadedPath.startsWith("/visits") && !prompting && lockOverlay == null) {
                    handler.postDelayed(MainActivity.this::askForLocationOnce, 1500);
                    handler.postDelayed(MainActivity.this::checkForUpdate, 5000);
                }
                if (lockOverlay != null && !prompting && pendingUnlock == null && target != null && onTargetPage(view)) {
                    handler.postDelayed(MainActivity.this::removeLockOverlay, 350);
                }
                if (splashHidden) return;
                if (target == null) {
                    view.evaluateJavascript(CHECK_AUTH_JS, null);
                    return;
                }
                if (!prompting && onTargetPage(view)) {
                    // let the page paint before revealing it
                    handler.postDelayed(() -> hideSplash(view), 350);
                }
            }
        });

        handler.postDelayed(splashTimeout, SPLASH_TIMEOUT_MS);
    }

    /** The visit URL carried by a VIEW intent, or null when it isn't one of our visit links. */
    private String visitLink(Intent intent) {
        Uri uri = intent == null ? null : intent.getData();
        if (uri == null || !"https".equals(uri.getScheme()) || !"eftkad.avarewase.com".equals(uri.getHost())) return null;
        String path = uri.getPath();
        return path != null && path.matches("/visits/\\d+") ? BASE_URL + path : null;
    }

    // Tapped a shared link while the app is already running.
    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        String link = visitLink(intent);
        if (link == null) return;
        pendingLink = link;
        // Already signed in and unlocked: go straight to it. Otherwise it opens after sign-in / unlock.
        if ("visits".equals(target) && lockOverlay == null && pendingUnlock == null) {
            pendingLink = null;
            getBridge().getWebView().loadUrl(link);
        }
    }

    private boolean onTargetPage(WebView view) {
        String path = Uri.parse(String.valueOf(view.getUrl())).getPath();
        return target != null && path != null && path.startsWith("/" + target);
    }

    private void hideSplash(WebView view) {
        if (splashHidden) return;
        splashHidden = true;
        handler.removeCallbacks(splashTimeout);
        view.evaluateJavascript("window.Capacitor&&Capacitor.Plugins&&Capacitor.Plugins.SplashScreen&&Capacitor.Plugins.SplashScreen.hide()", null);
    }

    // ---- biometric lock ----

    private boolean biometricAvailable() {
        return BiometricManager.from(this).canAuthenticate(BiometricManager.Authenticators.BIOMETRIC_WEAK)
            == BiometricManager.BIOMETRIC_SUCCESS;
    }

    /** Asks for fingerprint/face. onUnlocked runs on success; any cancel/failure signs out to the login page. */
    private void authenticate(Runnable onUnlocked) {
        if (prompting) return;
        pendingUnlock = onUnlocked;
        // Fingerprint: read straight from our own lock screen. Face-only phones use the system prompt.
        if (lockOverlay != null && fingerprintEnrolled()) {
            listenForFingerprint(onUnlocked);
            return;
        }
        prompting = true;
        BiometricPrompt prompt = new BiometricPrompt(this, ContextCompat.getMainExecutor(this),
            new BiometricPrompt.AuthenticationCallback() {
                @Override
                public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                    prompting = false;
                    pendingUnlock = null;
                    onUnlocked.run();
                }

                @Override
                public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                    prompting = false;
                    if (errorCode == BiometricPrompt.ERROR_NEGATIVE_BUTTON
                        || errorCode == BiometricPrompt.ERROR_LOCKOUT
                        || errorCode == BiometricPrompt.ERROR_LOCKOUT_PERMANENT) {
                        // "Use login", or too many wrong attempts: fall back to a full sign-in
                        pendingUnlock = null;
                        signOut();
                    }
                    // otherwise (dismissed, system cancel): the lock screen stays up with its
                    // Unlock and Use login buttons, so the user can try again or sign in instead
                }
            });

        BiometricPrompt.PromptInfo info = new BiometricPrompt.PromptInfo.Builder()
            .setTitle("افتح إفتقاد / Unlock Eftkad")
            .setSubtitle("بصمتك أو وجهك / Fingerprint or face")
            .setNegativeButtonText("تسجيل الدخول بحساب / Use login")
            .setAllowedAuthenticators(BiometricManager.Authenticators.BIOMETRIC_WEAK)
            .setConfirmationRequired(false)
            .build();
        prompt.authenticate(info);
    }

    @SuppressWarnings("deprecation")
    private boolean fingerprintEnrolled() {
        try {
            FingerprintManager fm = getSystemService(FingerprintManager.class);
            return fm != null && fm.isHardwareDetected() && fm.hasEnrolledFingerprints();
        } catch (Exception e) {
            return false;
        }
    }

    private void setFpState(String text, int color, boolean showRetry) {
        if (fpStatus == null) return;
        fpStatus.setText(text);
        fpStatus.setTextColor(color);
        fpIcon.setColorFilter(color, PorterDuff.Mode.SRC_IN);
        unlockButton.setVisibility(showRetry ? View.VISIBLE : View.GONE);
    }

    private void stopListening() {
        if (fpCancel != null) {
            fpCancel.cancel();
            fpCancel = null;
        }
        prompting = false;
    }

    /** Listens for the fingerprint from the lock screen itself, so it shows our UI instead of the system popup. */
    @SuppressWarnings("deprecation")
    private void listenForFingerprint(Runnable onUnlocked) {
        FingerprintManager fm = getSystemService(FingerprintManager.class);
        stopListening();
        prompting = true;
        fpCancel = new CancellationSignal();
        setFpState("المس مستشعر البصمة / Touch the fingerprint sensor", COLOR_IDLE, false);
        fm.authenticate(null, fpCancel, 0, new FingerprintManager.AuthenticationCallback() {
            @Override
            public void onAuthenticationSucceeded(FingerprintManager.AuthenticationResult result) {
                prompting = false;
                fpCancel = null;
                setFpState("تم / Unlocked", COLOR_OK, false);
                handler.postDelayed(() -> { pendingUnlock = null; onUnlocked.run(); }, 250);
            }

            @Override
            public void onAuthenticationFailed() {
                // a finger was read but not recognised: the sensor keeps listening
                setFpState("لم يتم التعرف على البصمة، حاول مرة أخرى / Not recognised, try again", COLOR_ERROR, false);
            }

            @Override
            public void onAuthenticationHelp(int helpCode, CharSequence helpString) {
                if (helpString != null) setFpState(helpString.toString(), COLOR_ERROR, false);
            }

            @Override
            public void onAuthenticationError(int errorCode, CharSequence errString) {
                prompting = false;
                fpCancel = null;
                if (errorCode == FingerprintManager.FINGERPRINT_ERROR_CANCELED) return; // we stopped it ourselves
                if (errorCode == FingerprintManager.FINGERPRINT_ERROR_LOCKOUT
                    || errorCode == FingerprintManager.FINGERPRINT_ERROR_LOCKOUT_PERMANENT) {
                    // too many wrong attempts: fall back to a full sign-in
                    pendingUnlock = null;
                    signOut();
                    return;
                }
                setFpState("حاول مرة أخرى / Try again", COLOR_ERROR, true);
            }
        }, null);
    }

    private void signOut() {
        stopListening();
        WebView view = getBridge().getWebView();
        view.evaluateJavascript(CLEAR_AUTH_JS, null);
        target = "login";
        if (onTargetPage(view)) {
            handler.postDelayed(() -> { hideSplash(view); removeLockOverlay(); }, 350);
        } else {
            view.loadUrl(LOGIN_URL);
        }
    }

    private int dp(int v) {
        return Math.round(v * getResources().getDisplayMetrics().density);
    }

    // "1.1 (2)": versionName and versionCode of the installed build
    @SuppressWarnings("deprecation")
    private String installedVersionLabel() {
        try {
            android.content.pm.PackageInfo info = getPackageManager().getPackageInfo(getPackageName(), 0);
            return info.versionName;
        } catch (Exception e) {
            return "";
        }
    }

    private void showLockOverlay() {
        if (lockOverlay != null) return;
        FrameLayout root = new FrameLayout(this);
        root.setBackgroundColor(Color.WHITE);
        root.setClickable(true);

        // the logo on white (same image as the splash), shown behind the fingerprint prompt
        ImageView logo = new ImageView(this);
        logo.setImageResource(R.drawable.splash);
        logo.setScaleType(ImageView.ScaleType.CENTER_CROP);
        root.addView(logo, new FrameLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));

        // Unlock / Use login: what's left on screen when the fingerprint prompt is dismissed
        Button unlock = new Button(this);
        unlock.setText("فتح / Unlock");
        unlock.setAllCaps(false);
        unlock.setTextColor(Color.WHITE);
        unlock.setTextSize(16);
        unlock.setTypeface(Typeface.DEFAULT_BOLD);
        GradientDrawable bg = new GradientDrawable();
        bg.setColor(Color.parseColor("#3F5D6D"));
        bg.setCornerRadius(dp(14));
        unlock.setBackground(bg);
        unlock.setOnClickListener(v -> { if (pendingUnlock != null) authenticate(pendingUnlock); });
        unlockButton = unlock;
        unlock.setText("حاول مرة أخرى / Try again");
        unlock.setVisibility(View.GONE);

        fpIcon = new ImageView(this);
        fpIcon.setImageResource(R.drawable.ic_fingerprint);
        fpIcon.setColorFilter(COLOR_IDLE, PorterDuff.Mode.SRC_IN);
        fpStatus = new TextView(this);
        fpStatus.setTextSize(15);
        fpStatus.setGravity(Gravity.CENTER);
        fpStatus.setTextColor(COLOR_IDLE);

        Button login = new Button(this);
        login.setText("تسجيل الدخول بحساب / Use login");
        login.setAllCaps(false);
        login.setTextColor(Color.parseColor("#647080"));
        login.setTextSize(14);
        login.setBackgroundColor(Color.TRANSPARENT);
        login.setOnClickListener(v -> { pendingUnlock = null; signOut(); });

        Button about = new Button(this);
        about.setText("عن التطبيق / About");
        about.setAllCaps(false);
        about.setTextColor(Color.parseColor("#647080"));
        about.setTextSize(14);
        about.setBackgroundColor(Color.TRANSPARENT);
        about.setOnClickListener(v -> showAboutDialog());

        LinearLayout actions = new LinearLayout(this);
        actions.setOrientation(LinearLayout.VERTICAL);
        actions.setGravity(Gravity.CENTER_HORIZONTAL);
        actions.addView(fpIcon, new LinearLayout.LayoutParams(dp(72), dp(72)));
        LinearLayout.LayoutParams statusParams = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        statusParams.topMargin = dp(12);
        statusParams.bottomMargin = dp(16);
        actions.addView(fpStatus, statusParams);
        actions.addView(unlock, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(52)));
        LinearLayout.LayoutParams loginParams = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(48));
        loginParams.topMargin = dp(8);
        actions.addView(login, loginParams);
        actions.addView(about, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(48)));

        FrameLayout.LayoutParams actionsParams = new FrameLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT, Gravity.BOTTOM);
        actionsParams.setMargins(dp(32), 0, dp(32), dp(40));
        root.addView(actions, actionsParams);

        // build number, small and centred at the bottom of the splash-looking lock screen
        TextView version = new TextView(this);
        version.setText(installedVersionLabel());
        version.setTextSize(12);
        version.setTextColor(Color.parseColor("#647080"));
        version.setGravity(Gravity.CENTER);
        root.addView(version, new FrameLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT, Gravity.BOTTOM));
        ((FrameLayout.LayoutParams) version.getLayoutParams()).bottomMargin = dp(14);

        lockOverlay = root;
        ((ViewGroup) findViewById(android.R.id.content)).addView(lockOverlay,
            new ViewGroup.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));
    }

    /** Public About page (no sign-in needed), shown full screen over the lock screen with a close button. */
    private void showAboutDialog() {
        Dialog dialog = new Dialog(this, android.R.style.Theme_Black_NoTitleBar_Fullscreen);
        FrameLayout box = new FrameLayout(this);
        box.setBackgroundColor(Color.WHITE);

        WebView web = new WebView(this);
        web.getSettings().setJavaScriptEnabled(true);
        web.getSettings().setDomStorageEnabled(true);
        web.setWebViewClient(new android.webkit.WebViewClient());
        web.loadUrl(ABOUT_URL);
        box.addView(web, new FrameLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));

        // small round close button floating over the page (top corner, below the status bar)
        TextView close = new TextView(this);
        close.setText("✕");
        close.setTextSize(18);
        close.setTextColor(Color.WHITE);
        close.setGravity(Gravity.CENTER);
        GradientDrawable closeBg = new GradientDrawable();
        closeBg.setShape(GradientDrawable.OVAL);
        closeBg.setColor(Color.parseColor("#99263547"));
        close.setBackground(closeBg);
        close.setContentDescription("إغلاق / Close");
        close.setOnClickListener(v -> dialog.dismiss());
        FrameLayout.LayoutParams closeParams = new FrameLayout.LayoutParams(dp(44), dp(44), Gravity.TOP | Gravity.END);
        closeParams.topMargin = dp(32);
        closeParams.setMarginEnd(dp(16));
        box.addView(close, closeParams);

        dialog.setContentView(box);
        dialog.setOnDismissListener(d -> web.destroy());
        dialog.show();
    }

    private void removeLockOverlay() {
        stopListening();
        fpIcon = null;
        fpStatus = null;
        unlockButton = null;
        if (lockOverlay == null) return;
        ((ViewGroup) lockOverlay.getParent()).removeView(lockOverlay);
        lockOverlay = null;
    }

    // ---- lifecycle ----

    @Override
    public void onStart() {
        super.onStart();
        getBridge().getWebView().addJavascriptInterface(new Object() {
            @JavascriptInterface
            public void retry() {
                if (!isOnline()) {
                    runOnUiThread(() -> {
                        Toast.makeText(MainActivity.this, "لا يوجد اتصال بالإنترنت / No internet connection", Toast.LENGTH_SHORT).show();
                        retryFinished("offline");
                    });
                    return;
                }
                if (!retrying.compareAndSet(false, true)) return;
                // Ask the server in the background first: the app is only opened on a 2xx answer, so a
                // 5xx (or no answer) keeps the user on this page instead of flashing a broken site.
                new Thread(() -> {
                    int code = -1;
                    HttpURLConnection conn = null;
                    try {
                        conn = (HttpURLConnection) new URL(VISITS_URL).openConnection();
                        conn.setConnectTimeout(8000);
                        conn.setReadTimeout(8000);
                        conn.setRequestProperty("Cache-Control", "no-cache");
                        code = conn.getResponseCode();
                    } catch (Exception ignored) {
                        // unreachable: treated like a server error below
                    } finally {
                        if (conn != null) conn.disconnect();
                        retrying.set(false);
                    }
                    final boolean up = code >= 200 && code < 300;
                    runOnUiThread(() -> {
                        if (up) getBridge().getWebView().loadUrl(VISITS_URL);
                        else retryFinished("server");
                    });
                }).start();
            }

            @JavascriptInterface
            public String appVersion() {
                return installedVersionLabel();
            }

            @JavascriptInterface
            public void route(String destination) {
                runOnUiThread(() -> {
                    if (target != null) return;
                    WebView view = getBridge().getWebView();
                    if ("login".equals(destination)) {
                        signOutToLogin(view);
                    } else if (biometricAvailable()) {
                        // a valid session: unlock it with biometrics before showing visits
                        // The splash is drawn on top of dialogs, so swap it for an overlay with the
                        // same logo, otherwise the fingerprint prompt is hidden behind it.
                        target = "visits";
                        showLockOverlay();
                        hideSplash(view);
                        authenticate(() -> openVisits(view));
                    } else {
                        target = "visits";
                        openVisits(view);
                    }
                });
            }
        }, "EftkadNative");
    }

    // Tells the error page the check is over and the service is still not available (it re-enables its button).
    private void retryFinished(String reason) {
        getBridge().getWebView().evaluateJavascript("window.onRetryResult && window.onRetryResult('" + reason + "')", null);
    }

    private void signOutToLogin(WebView view) {
        target = "login";
        if (onTargetPage(view)) {
            handler.postDelayed(() -> { hideSplash(view); removeLockOverlay(); }, 350);
        } else {
            view.loadUrl(LOGIN_URL);
        }
    }

    // ---- in-app updates (OTA) ----
    // The site serves /app/version.json ({versionCode, versionName, url, sha256, notes, force}) and the
    // APK it points to (see scripts/publish-apk.mjs). When versionCode is newer than this build, the app
    // offers to download and install it, so the APK never has to be sent around again.

    private static final String UPDATE_INFO_URL = BASE_URL + "/app/version.json";
    private boolean updateChecked = false;
    private JSONObject pendingUpdate = null; // waiting for the "install unknown apps" permission
    private AlertDialog updateDialog;

    @SuppressWarnings("deprecation")
    private int installedVersionCode() {
        try {
            return getPackageManager().getPackageInfo(getPackageName(), 0).versionCode;
        } catch (Exception e) {
            return Integer.MAX_VALUE; // unknown: never offer an update
        }
    }

    private void checkForUpdate() {
        if (updateChecked || lockOverlay != null || pendingUnlock != null || !isOnline()) return;
        updateChecked = true;
        new Thread(() -> {
            try {
                // cache-buster: neither Cloudflare nor the site's service worker may answer with an old file
                HttpURLConnection c = (HttpURLConnection) new URL(UPDATE_INFO_URL + "?t=" + System.currentTimeMillis()).openConnection();
                c.setConnectTimeout(8000);
                c.setReadTimeout(8000);
                if (c.getResponseCode() != 200) return;
                StringBuilder sb = new StringBuilder();
                try (InputStream in = c.getInputStream()) {
                    byte[] buf = new byte[4096];
                    int n;
                    while ((n = in.read(buf)) > 0) sb.append(new String(buf, 0, n, "UTF-8"));
                }
                JSONObject info = new JSONObject(sb.toString());
                String apkUrl = info.optString("url", "");
                // only ever download from our own site
                if (info.getInt("versionCode") <= installedVersionCode() || !apkUrl.startsWith(BASE_URL + "/")) return;
                runOnUiThread(() -> showUpdateDialog(info));
            } catch (Exception ignored) {
                // no update info reachable: carry on silently
            }
        }).start();
    }

    private void showUpdateDialog(JSONObject info) {
        if (isFinishing() || updateDialog != null) return;
        boolean force = info.optBoolean("force", false);
        String notes = info.optString("notes", "");

        LinearLayout card = dialogCard();
        card.addView(dialogLogo());
        card.addView(dialogText("تحديث جديد / Update available", 18, "#263547", true, 14));

        TextView chip = dialogText("v" + info.optString("versionName"), 13, "#3F5D6D", true, 10);
        GradientDrawable chipBg = new GradientDrawable();
        chipBg.setColor(Color.parseColor("#E6EDEF"));
        chipBg.setCornerRadius(dp(20));
        chip.setBackground(chipBg);
        chip.setPadding(dp(14), dp(5), dp(14), dp(5));
        LinearLayout.LayoutParams chipLp = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        chipLp.gravity = Gravity.CENTER_HORIZONTAL;
        card.addView(chip, chipLp);

        if (!notes.isEmpty()) card.addView(dialogText(notes, 14, "#263547", false, 14));
        card.addView(dialogText("لأفضل تجربة، حدّث التطبيق الآن.\nUpdate Eftkad now for the best experience.", 13, "#647080", false, 14));

        AlertDialog dialog = new AlertDialog.Builder(this).setView(card).setCancelable(!force).create();

        Button update = new Button(this);
        update.setText("تحديث / Update");
        update.setAllCaps(false);
        update.setTextColor(Color.WHITE);
        update.setTextSize(16);
        update.setTypeface(Typeface.DEFAULT_BOLD);
        GradientDrawable btnBg = new GradientDrawable();
        btnBg.setColor(Color.parseColor("#3F5D6D"));
        btnBg.setCornerRadius(dp(14));
        update.setBackground(btnBg);
        update.setOnClickListener(v -> { updateDialog = null; dialog.dismiss(); startUpdate(info); });
        LinearLayout.LayoutParams updateLp = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(50));
        updateLp.topMargin = dp(20);
        card.addView(update, updateLp);

        if (!force) {
            Button later = new Button(this);
            later.setText("لاحقاً / Later");
            later.setAllCaps(false);
            later.setTextColor(Color.parseColor("#647080"));
            later.setTextSize(14);
            later.setBackgroundColor(Color.TRANSPARENT);
            later.setOnClickListener(v -> dialog.dismiss());
            card.addView(later, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(46)));
        }

        updateDialog = dialog;
        dialog.setOnDismissListener(d -> updateDialog = null);
        dialog.show();
        styleDialogWindow(dialog);
    }

    // ---- dialog styling (same palette as the site: warm surface, slate-blue primary, rounded corners) ----

    private LinearLayout dialogCard() {
        LinearLayout card = new LinearLayout(this);
        card.setOrientation(LinearLayout.VERTICAL);
        card.setPadding(dp(24), dp(24), dp(24), dp(14));
        GradientDrawable bg = new GradientDrawable();
        bg.setColor(Color.parseColor("#FFFEFA"));
        bg.setCornerRadius(dp(22));
        card.setBackground(bg);
        return card;
    }

    private ImageView dialogLogo() {
        ImageView logo = new ImageView(this);
        logo.setImageResource(R.mipmap.ic_launcher);
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(dp(64), dp(64));
        lp.gravity = Gravity.CENTER_HORIZONTAL;
        lp.bottomMargin = dp(14);
        logo.setLayoutParams(lp);
        return logo;
    }

    private TextView dialogText(String text, int sp, String color, boolean bold, int topMarginDp) {
        TextView t = new TextView(this);
        t.setText(text);
        t.setTextSize(sp);
        t.setTextColor(Color.parseColor(color));
        t.setGravity(Gravity.CENTER);
        t.setTextDirection(View.TEXT_DIRECTION_LOCALE);
        if (bold) t.setTypeface(Typeface.DEFAULT_BOLD);
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        lp.topMargin = dp(topMarginDp);
        t.setLayoutParams(lp);
        return t;
    }

    private void styleDialogWindow(AlertDialog dialog) {
        if (dialog.getWindow() == null) return;
        dialog.getWindow().setBackgroundDrawable(new android.graphics.drawable.ColorDrawable(Color.TRANSPARENT));
        dialog.getWindow().setLayout(Math.min(dp(340), getResources().getDisplayMetrics().widthPixels - dp(40)), ViewGroup.LayoutParams.WRAP_CONTENT);
    }

    // Before Android 8 there is no per-app "install unknown apps" switch (one global setting instead)
    private boolean canInstallPackages() {
        return android.os.Build.VERSION.SDK_INT < android.os.Build.VERSION_CODES.O || getPackageManager().canRequestPackageInstalls();
    }

    private void startUpdate(JSONObject info) {
        // Android asks the user to allow installs from this app once.
        if (!canInstallPackages()) {
            pendingUpdate = info;
            Toast.makeText(this, "اسمح بالتثبيت من هذا التطبيق ثم ارجع / Allow installs from this app, then come back", Toast.LENGTH_LONG).show();
            startActivity(new Intent(Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES, Uri.parse("package:" + getPackageName())));
            return;
        }
        pendingUpdate = null;

        LinearLayout card = dialogCard();
        card.setPadding(dp(24), dp(24), dp(24), dp(24));
        card.addView(dialogLogo());
        card.addView(dialogText("جاري التحميل / Downloading", 17, "#263547", true, 4));
        TextView pct = dialogText("0%", 14, "#647080", false, 10);
        card.addView(pct);
        android.widget.ProgressBar bar = new android.widget.ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal);
        bar.setMax(100);
        bar.setProgressTintList(android.content.res.ColorStateList.valueOf(Color.parseColor("#3F5D6D")));
        bar.setProgressBackgroundTintList(android.content.res.ColorStateList.valueOf(Color.parseColor("#E6EDEF")));
        LinearLayout.LayoutParams barLp = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(8));
        barLp.topMargin = dp(12);
        card.addView(bar, barLp);
        AlertDialog progress = new AlertDialog.Builder(this).setView(card).setCancelable(false).create();
        progress.show();
        styleDialogWindow(progress);

        new Thread(() -> {
            try {
                File dir = new File(getCacheDir(), "updates");
                if (!dir.exists() && !dir.mkdirs()) throw new Exception("no cache dir");
                File apk = new File(dir, "eftkad.apk");
                HttpURLConnection c = (HttpURLConnection) new URL(info.getString("url")).openConnection();
                c.setConnectTimeout(15000);
                c.setReadTimeout(30000);
                long total = c.getContentLengthLong();
                MessageDigest sha = MessageDigest.getInstance("SHA-256");
                try (InputStream in = c.getInputStream(); OutputStream out = new FileOutputStream(apk)) {
                    byte[] buf = new byte[16384];
                    long done = 0;
                    int last = -1, n;
                    while ((n = in.read(buf)) > 0) {
                        out.write(buf, 0, n);
                        sha.update(buf, 0, n);
                        done += n;
                        int pct0 = total > 0 ? (int) (done * 100 / total) : -1;
                        if (pct0 != last) {
                            last = pct0;
                            final String text = pct0 >= 0 ? pct0 + "%" : "…";
                            final int shown = Math.max(pct0, 0);
                            runOnUiThread(() -> { pct.setText(text); bar.setProgress(shown); });
                        }
                    }
                }
                // refuse a corrupted or swapped file
                StringBuilder hex = new StringBuilder();
                for (byte x : sha.digest()) hex.append(String.format("%02x", x));
                String expected = info.optString("sha256", "");
                if (!expected.isEmpty() && !expected.equalsIgnoreCase(hex.toString())) throw new Exception("checksum mismatch");

                runOnUiThread(() -> {
                    progress.dismiss();
                    Uri uri = FileProvider.getUriForFile(this, getPackageName() + ".fileprovider", apk);
                    Intent install = new Intent(Intent.ACTION_VIEW)
                        .setDataAndType(uri, "application/vnd.android.package-archive")
                        .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION | Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(install);
                });
            } catch (Exception e) {
                runOnUiThread(() -> {
                    progress.dismiss();
                    Toast.makeText(this, "تعذر التحديث، حاول لاحقاً / Update failed, try again later", Toast.LENGTH_LONG).show();
                });
            }
        }).start();
    }

    // ---- location permission ----

    private static final int LOCATION_REQUEST_CODE = 4321;
    private boolean askedForLocation = false;

    /**
     * Asks for location once per launch, after the user has signed in / unlocked, so the system
     * dialog never lands on top of the fingerprint prompt. The visit form's "Use current location"
     * button (navigator.geolocation) is then granted by Capacitor's web chrome client without
     * asking again; if the user declined, that button still re-asks when it is tapped.
     */
    private void askForLocationOnce() {
        if (askedForLocation) return;
        askedForLocation = true;
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) return;
        ActivityCompat.requestPermissions(this,
            new String[] { Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION },
            LOCATION_REQUEST_CODE);
    }

    private void openVisits(WebView view) {
        // give the page a moment to paint before the system dialog appears
        handler.postDelayed(this::askForLocationOnce, 1500);
        String link = pendingLink;
        pendingLink = null;
        if (link != null) {
            view.loadUrl(link);
        } else if (onTargetPage(view)) {
            handler.postDelayed(() -> { hideSplash(view); removeLockOverlay(); }, 350);
        } else {
            view.loadUrl(VISITS_URL);
        }
    }

    @Override
    public void onStop() {
        super.onStop();
        // the sensor must not stay claimed in the background; onResume listens again
        if (fpCancel != null) stopListening();
        stoppedAt = System.currentTimeMillis();
    }

    // The site's service worker serves cached pages when offline, so the WebView never
    // reports a load error. Check connectivity natively and show the bundled offline page.
    // Also re-lock with biometrics when returning after a while in the background.
    @Override
    public void onResume() {
        super.onResume();
        final boolean relock = stoppedAt > 0 && "visits".equals(target) && splashHidden && !prompting && lockOverlay == null
            && System.currentTimeMillis() - stoppedAt > RELOCK_AFTER_MS && biometricAvailable();
        stoppedAt = 0;
        // back from the "install unknown apps" settings screen with the permission granted
        if (pendingUpdate != null && canInstallPackages()) startUpdate(pendingUpdate);
        if (!relock && lockOverlay != null && pendingUnlock != null && !prompting && fingerprintEnrolled()) {
            authenticate(pendingUnlock);
        }
        if (relock) {
            showLockOverlay();
            authenticate(this::removeLockOverlay);
        }
        handler.postDelayed(() -> {
            if (!isOnline() && getBridge() != null) {
                getBridge().getWebView().loadUrl(OFFLINE_URL);
            }
        }, 400);
    }

    private boolean isOnline() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo info = cm == null ? null : cm.getActiveNetworkInfo();
        return info != null && info.isConnected();
    }
}