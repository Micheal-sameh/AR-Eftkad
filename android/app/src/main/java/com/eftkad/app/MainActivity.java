package com.eftkad.app;

import android.content.Context;
import android.graphics.Color;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.ViewGroup;
import android.webkit.JavascriptInterface;
import android.webkit.WebView;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.biometric.BiometricManager;
import androidx.biometric.BiometricPrompt;
import androidx.core.content.ContextCompat;

import com.getcapacitor.BridgeActivity;
import com.getcapacitor.BridgeWebViewClient;

public class MainActivity extends BridgeActivity {

    private static final String BASE_URL = "https://eftkad.avarewase.com";
    private static final String VISITS_URL = BASE_URL + "/visits";
    private static final String LOGIN_URL = BASE_URL + "/login";
    private static final String OFFLINE_URL = "file:///android_asset/public/offline.html";
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

    private static final String CLEAR_AUTH_JS =
        "localStorage.removeItem('eftkad_token');localStorage.removeItem('eftkad_user');";

    private final Handler handler = new Handler(Looper.getMainLooper());
    private final Runnable splashTimeout = () -> hideSplash(getBridge().getWebView());
    private boolean splashHidden = false;
    private String target = null; // "visits" or "login", set once the auth check has run
    private long stoppedAt = 0;
    private boolean prompting = false;
    private View lockOverlay;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Hold the splash while the app decides where to go: /visits when the stored
        // token is valid (after biometric unlock), /login otherwise. The splash is
        // hidden only once that page has loaded.
        getBridge().setWebViewClient(new BridgeWebViewClient(getBridge()) {
            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                if (url == null) return;
                if (url.startsWith("file:")) {
                    removeLockOverlay();
                    hideSplash(view);
                    return;
                }
                if (lockOverlay != null && !prompting && target != null && onTargetPage(view)) {
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
        prompting = true;
        BiometricPrompt prompt = new BiometricPrompt(this, ContextCompat.getMainExecutor(this),
            new BiometricPrompt.AuthenticationCallback() {
                @Override
                public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                    prompting = false;
                    onUnlocked.run();
                }

                @Override
                public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                    prompting = false;
                    if (errorCode == BiometricPrompt.ERROR_NEGATIVE_BUTTON
                        || errorCode == BiometricPrompt.ERROR_LOCKOUT
                        || errorCode == BiometricPrompt.ERROR_LOCKOUT_PERMANENT) {
                        // "Use login", or too many wrong attempts: fall back to a full sign-in
                        signOut();
                    } else {
                        // dismissed (back button, switching apps, system cancel): stay signed in,
                        // close the app so the next launch asks for the fingerprint again
                        finish();
                    }
                }
            });

        BiometricPrompt.PromptInfo info = new BiometricPrompt.PromptInfo.Builder()
            .setTitle("افتح التطبيق بالبصمة / Unlock Eftkad")
            .setSubtitle("استخدم بصمتك أو وجهك للدخول / Use your fingerprint or face")
            .setNegativeButtonText("تسجيل الدخول بحساب / Use login")
            .setAllowedAuthenticators(BiometricManager.Authenticators.BIOMETRIC_WEAK)
            .setConfirmationRequired(false)
            .build();
        prompt.authenticate(info);
    }

    private void signOut() {
        WebView view = getBridge().getWebView();
        view.evaluateJavascript(CLEAR_AUTH_JS, null);
        target = "login";
        if (onTargetPage(view)) {
            handler.postDelayed(() -> { hideSplash(view); removeLockOverlay(); }, 350);
        } else {
            view.loadUrl(LOGIN_URL);
        }
    }

    private void showLockOverlay() {
        if (lockOverlay != null) return;
        // the logo on white (same image as the splash), shown behind the fingerprint prompt
        ImageView logo = new ImageView(this);
        logo.setImageResource(R.drawable.splash);
        logo.setScaleType(ImageView.ScaleType.CENTER_CROP);
        logo.setBackgroundColor(Color.WHITE);
        logo.setClickable(true);
        lockOverlay = logo;
        ((ViewGroup) findViewById(android.R.id.content)).addView(lockOverlay,
            new ViewGroup.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));
    }

    private void removeLockOverlay() {
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
                runOnUiThread(() -> {
                    if (isOnline()) {
                        getBridge().getWebView().loadUrl(VISITS_URL);
                    } else {
                        Toast.makeText(MainActivity.this, "لا يوجد اتصال بالإنترنت / No internet connection", Toast.LENGTH_SHORT).show();
                    }
                });
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

    private void signOutToLogin(WebView view) {
        target = "login";
        if (onTargetPage(view)) {
            handler.postDelayed(() -> { hideSplash(view); removeLockOverlay(); }, 350);
        } else {
            view.loadUrl(LOGIN_URL);
        }
    }

    private void openVisits(WebView view) {
        if (onTargetPage(view)) {
            handler.postDelayed(() -> { hideSplash(view); removeLockOverlay(); }, 350);
        } else {
            view.loadUrl(VISITS_URL);
        }
    }

    @Override
    public void onStop() {
        super.onStop();
        stoppedAt = System.currentTimeMillis();
    }

    // The site's service worker serves cached pages when offline, so the WebView never
    // reports a load error. Check connectivity natively and show the bundled offline page.
    // Also re-lock with biometrics when returning after a while in the background.
    @Override
    public void onResume() {
        super.onResume();
        final boolean relock = stoppedAt > 0 && "visits".equals(target) && splashHidden && !prompting
            && System.currentTimeMillis() - stoppedAt > RELOCK_AFTER_MS && biometricAvailable();
        stoppedAt = 0;
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