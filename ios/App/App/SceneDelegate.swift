import UIKit
import Capacitor

class SceneDelegate: UIResponder, UIWindowSceneDelegate {
    var window: UIWindow?

    func scene(_ scene: UIScene, willConnectTo session: UISceneSession, options connectionOptions: UIScene.ConnectionOptions) {
        guard let windowScene = scene as? UIWindowScene else { return }

        window = UIWindow(windowScene: windowScene)
        let controller = EftkadViewController()
        window?.rootViewController = controller
        window?.makeKeyAndVisible()

        // Launched by tapping a shared visit link
        if let url = connectionOptions.userActivities.first(where: { $0.activityType == NSUserActivityTypeBrowsingWeb })?.webpageURL {
            controller.openSharedLink(url)
        }

        SceneDelegateProxy.shared.scene(scene, willConnectTo: session, options: connectionOptions)
    }

    func scene(_ scene: UIScene, openURLContexts URLContexts: Set<UIOpenURLContext>) {
        SceneDelegateProxy.shared.scene(scene, openURLContexts: URLContexts)
    }

    func scene(_ scene: UIScene, continue userActivity: NSUserActivity) {
        if userActivity.activityType == NSUserActivityTypeBrowsingWeb, let url = userActivity.webpageURL {
            (window?.rootViewController as? EftkadViewController)?.openSharedLink(url)
        }
        SceneDelegateProxy.shared.scene(scene, continue: userActivity)
    }
}
