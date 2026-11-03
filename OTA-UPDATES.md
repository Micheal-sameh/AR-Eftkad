# Eftkad Android: sending the app and updating it (OTA)

The app is a Capacitor shell around the live site (`https://eftkad.avarewase.com`).

| What changed | How people get it |
| --- | --- |
| Website / API (pages, text size, etc.) | Automatically. Just deploy the site. |
| Native app code (lock screen, update logic, permissions) | A new APK. Use the in-app update below. |

## 1. First time: send the app to people

Send **`public/app/eftkad.apk`** (same file as `android/app/build/outputs/apk/debug/app-debug.apk`, version 1.1).

On their phone: open the file, allow "install unknown apps" for the app they opened it from (WhatsApp, Files, ...), then install.

Only this first install is done by hand. Builds older than 1.1 cannot update themselves, so everyone needs 1.1 once.

## 2. Releasing an update

1. **Raise the version** in `android/app/build.gradle`:
   ```
   versionCode 3        // must go up every release (integer)
   versionName "1.2"    // shown to the user
   ```
2. **Build the APK** (on the same computer as before, see "Signing key"):
   ```
   npx cap sync android
   cd android
   gradlew assembleDebug
   ```
   On Windows set `ANDROID_HOME` first if needed: `$env:ANDROID_HOME="$env:LOCALAPPDATA\Android\Sdk"`.
3. **Publish it** from the project root:
   ```
   node scripts/publish-apk.mjs --notes "What changed"
   ```
   Add `--force` to make the update mandatory (no "Later" button).
   This copies the APK to `public/app/eftkad.apk` and writes `public/app/version.json` (version, SHA-256 checksum, notes).
4. **Deploy** the site so `public/app/` is live (rebuild the Docker image).
5. Check it in a browser: `https://eftkad.avarewase.com/app/version.json` shows the new `versionCode`.

## 3. What the user sees

1. They open the app and unlock it. A few seconds later, **"تحديث جديد / Update available"** appears.
2. **Update** downloads the APK, shows the progress, verifies the checksum, and opens Android's installer. **Later** dismisses it (it asks again next launch).
3. First update only: Android asks them to allow installs from Eftkad. They turn it on and press back. The download then starts by itself.

## 4. Signing key (important)

Android installs an update only if it is signed with the **same key** as the installed app. Right now all builds use the debug key of the computer that builds them.

- Always build releases on the same computer, or
- Set up a release keystore, keep it backed up, and always sign with it. If it is lost, every user must uninstall and reinstall.

## 5. Troubleshooting

| Problem | Likely cause |
| --- | --- |
| No update prompt appears | `versionCode` was not raised, the site has not been deployed, or the phone is offline. Check `/app/version.json`. |
| "App not installed" | The new APK is signed with a different key than the installed one. |
| "Update failed" | Checksum mismatch (the APK and `version.json` are out of sync) or the download was interrupted. Re-run the publish script and redeploy. |
| Prompt shows an old version | Cloudflare or a browser cache serving an old `version.json`. Purge it (the app already adds a cache-buster). |

## 6. iPhone

iOS cannot install apps from a link or file. iPhone builds go through Xcode and TestFlight on a Mac, see `ios/BUILD.md`.
