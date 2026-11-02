# Building the iOS app

The iOS project was generated and prepared on Windows. **None of the Swift code has been compiled
or run yet** (there is no Xcode here), so expect to fix a typo or two on the first build.

## What you need
- A Mac with Xcode (16 or newer; Capacitor 8 requires a current Xcode) and Node 22+.
- An Apple Developer account (needed to run on a real iPhone and to publish).
- The bundle id is `com.eftkad.app`; register it in the Apple Developer portal if it is not taken.

## First build
```bash
npm install                 # in the project root
npx cap sync ios            # copies www/ and capacitor.config.json into ios/App/App
open ios/App/App.xcodeproj  # Capacitor 8 uses Swift Package Manager: no CocoaPods step
```
In Xcode: select the **App** target, then *Signing & Capabilities*: choose your Team and let Xcode
manage signing. Pick a real iPhone (Face ID cannot be tried in the simulator reliably) and press Run.

## Where things are
| What | File |
|---|---|
| Face ID lock, server-error and offline pages, location prompt | `ios/App/App/EftkadViewController.swift` |
| Makes that class the app's root screen | `ios/App/App/SceneDelegate.swift` |
| Permission texts (Face ID, location), arm64, export compliance | `ios/App/App/Info.plist` |
| Minimum iOS 16.4 (the site's CSS uses `color-mix()`) | `IPHONEOS_DEPLOYMENT_TARGET` in `App.xcodeproj` |
| App icon (1024px, no transparency) and splash image | `ios/App/App/Assets.xcassets` |
| `ios` settings (safe areas, no link previews) | `capacitor.config.json` |

The app shows https://eftkad.avarewase.com/visits in a web view (`server.url`), so site changes
need no new app build. Only changes in `www/` or `ios/` do.

## What to test on the phone
1. **Launch:** splash, then the site. The splash is hidden by the page (`api-js.blade.php`), not by native code.
2. **Lock:** sign in, close the app fully, reopen: Face ID prompt over the logo. *Cancel* shows *Unlock* and
   *Use login*; *Use login* signs out. Background the app for more than 60 s: it locks again.
3. **Location:** after unlocking, the iOS location dialog appears once. Then *New visit > Use current location*.
4. **Offline:** turn on airplane mode: the "no internet" page appears; turn it off: it returns to the site.
5. **Server error:** hard to trigger; the logic is shared with Android (page title / `#cf-error-details`).
6. **Pull-to-refresh** at the top of a page (native bounce is switched off so it does not fight the site's gesture).
7. **Notch / home bar:** the header and bottom bar must not sit under the notch or the home indicator.
8. **Arabic (RTL) and dark mode** on every screen.

## Things that may need fixing on the first build
- If Xcode reports a Swift error in `EftkadViewController.swift`, it is most likely an API detail
  (for example a `@MainActor` warning); the logic itself mirrors `MainActivity.java`.
- If the web view does not keep the login after a few days, add the site's domains under
  `WKAppBoundDomains` in `Info.plist` and set `limitsNavigationsToAppBoundDomains` to `true`
  in `capacitor.config.json` (iOS can clear web storage for unlisted domains).
- The icon and splash were made from the Android artwork (the biggest source was 512 px / 1280x1920),
  so they are slightly soft. Replace `AppIcon-512@2x.png` (1024x1024, no transparency) and the
  `Splash.imageset` files with proper high-resolution artwork before publishing.

## Publishing (App Store / TestFlight)
- Product > Archive, then *Distribute App* to App Store Connect / TestFlight.
- Fill in the privacy policy URL and the *App Privacy* answers (location is used; it is not tracked).
- Apple can reject apps that only wrap a website (guideline 4.2). The Face ID lock, offline handling and
  location use are the native features that justify the app; mention them in the review notes.
- Sign-in uses the church's own Avarewase account, so "Sign in with Apple" is not required (guideline 4.8).
