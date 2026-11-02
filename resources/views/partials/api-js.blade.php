<script>
    // Shared client-side helpers: token storage, API fetch wrapper, locale toggle.
    // Auth is fully token/JS-driven (Sanctum token in localStorage) - there is no
    // Laravel session-based auth for these pages.
    window.Eftkad = {
        TOKEN_KEY: 'eftkad_token',
        USER_KEY: 'eftkad_user',
        LOCALE_KEY: 'eftkad_locale',

        locale() {
            return localStorage.getItem(this.LOCALE_KEY) || 'ar';
        },

        // Syncs the `lang` cookie so the server (SetLocale middleware) picks
        // the same locale on the next full page load - localStorage alone
        // is invisible to server-rendered Blade pages.
        setLocaleCookie(locale) {
            document.cookie = 'lang=' + locale + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; samesite=lax';
        },

        toggleLocale() {
            const next = this.locale() === 'ar' ? 'en' : 'ar';
            localStorage.setItem(this.LOCALE_KEY, next);
            this.setLocaleCookie(next);
            window.location.reload();
        },

        token() {
            return localStorage.getItem(this.TOKEN_KEY);
        },

        user() {
            try {
                return JSON.parse(localStorage.getItem(this.USER_KEY) || 'null');
            } catch (e) {
                return null;
            }
        },

        setAuth(token, user) {
            localStorage.setItem(this.TOKEN_KEY, token);
            localStorage.setItem(this.USER_KEY, JSON.stringify(user || null));
        },

        clearAuth() {
            localStorage.removeItem(this.TOKEN_KEY);
            localStorage.removeItem(this.USER_KEY);
            sessionStorage.removeItem('eftkad_last_list');
            Object.keys(localStorage)
                .filter(function (k) { return k.indexOf(Eftkad.CACHE_PREFIX) === 0; })
                .forEach(function (k) { localStorage.removeItem(k); });
        },

        // GET with a short-lived localStorage cache, for rarely-changing
        // reference data (enums, father/servant filters) that every page
        // otherwise refetches on each navigation.
        CACHE_PREFIX: 'eftkad_cache:',

        async cachedApi(path, ttlMs = 10 * 60 * 1000) {
            const key = this.CACHE_PREFIX + this.locale() + ':' + path;
            try {
                const hit = JSON.parse(localStorage.getItem(key) || 'null');
                if (hit && Date.now() - hit.t < ttlMs) return hit.res;
            } catch (e) {}

            const res = await this.api(path);
            if (res.ok && res.data) {
                try {
                    localStorage.setItem(key, JSON.stringify({ t: Date.now(), res: res }));
                } catch (e) {}
            }
            return res;
        },

        // Redirects to /login when there's no token. Call at the top of any
        // authenticated page.
        requireAuth() {
            if (!this.token()) {
                window.location.href = '/login';
                return false;
            }
            return true;
        },

        // Minimal fetch wrapper: adds Authorization + Accept-Language headers,
        // JSON-encodes plain object bodies, and normalizes the response shape.
        async api(path, opts = {}) {
            const headers = Object.assign(
                { Accept: 'application/json', 'Accept-Language': this.locale() },
                opts.headers || {}
            );

            let body = opts.body;
            if (body && typeof body === 'object' && !(body instanceof FormData)) {
                body = JSON.stringify(body);
                headers['Content-Type'] = 'application/json';
            }

            const token = this.token();
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            let res;
            try {
                res = await fetch('/api' + path, { ...opts, body, headers });
            } catch (e) {
                return { ok: false, status: 0, data: null, networkError: true };
            }

            let data = null;
            try {
                data = await res.json();
            } catch (e) {
                // no JSON body
            }

            if (res.status === 401) {
                this.clearAuth();
                window.location.href = '/login';
            }

            return { ok: res.ok, status: res.status, data };
        },

        async logout() {
            await this.api('/auth/logout', { method: 'POST' });
            this.clearAuth();
            window.location.href = '/login';
        },

        // PWA install: the browser fires beforeinstallprompt asynchronously
        // (any time before or after DOMContentLoaded), so this listener is
        // registered eagerly, here, on every page - not inside a page's own
        // DOMContentLoaded handler, or the event could be missed.
        INSTALL_FLAG_KEY: 'eftkad_show_install_prompt',
        deferredInstallPrompt: null,

        markShowInstallPromptAfterLogin() {
            sessionStorage.setItem(this.INSTALL_FLAG_KEY, '1');
        },

        isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        },

        isIos() {
            return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        },
    };

    // Shared view helpers: inline SVG icons (same set as the icon Blade component),
    // HTML escaping, avatar initials, dark/light theme and a locale setter.
    Eftkad.ICONS = @json(include resource_path('icons.php'));

    Eftkad.icon = function (name, size) {
        size = size || 20;
        return '<svg class="icon" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (Eftkad.ICONS[name] || '') + '</svg>';
    };

    Eftkad.esc = function (str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    Eftkad.initials = function (name) {
        var words = String(name || '?').trim().split(/\s+/);
        if (words.length > 1) return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
        return words[0].slice(0, 2).toUpperCase();
    };

    Eftkad.setLocale = function (locale) {
        if (Eftkad.locale() === locale) return;
        localStorage.setItem(Eftkad.LOCALE_KEY, locale);
        Eftkad.setLocaleCookie(locale);
        window.location.reload();
    };

    Eftkad.toggleTheme = function () {
        var dark = document.documentElement.classList.toggle('dark');
        try { localStorage.setItem('eftkad_theme', dark ? 'dark' : 'light'); } catch (e) {}
        // Keep the phone's status bar / browser chrome in step with the theme.
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', dark ? '#1c2428' : '#faf9f6');
        document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
            el.innerHTML = Eftkad.icon(dark ? 'sun' : 'moon');
        });
    };

    // Wires the AR/EN pill and the theme button (used on every page that renders them).
    Eftkad.initChrome = function () {
        document.querySelectorAll('.language-toggle button').forEach(function (b) {
            b.classList.toggle('active', b.dataset.lang === Eftkad.locale());
            b.addEventListener('click', function () { Eftkad.setLocale(b.dataset.lang); });
        });
        var dark = document.documentElement.classList.contains('dark');
        document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
            el.innerHTML = Eftkad.icon(dark ? 'sun' : 'moon');
        });
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
            b.addEventListener('click', Eftkad.toggleTheme);
        });
    };

    // Keep the server-visible cookie in sync with localStorage even when the
    // user didn't just click the toggle (e.g. first visit after an update).
    Eftkad.setLocaleCookie(Eftkad.locale());

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        Eftkad.deferredInstallPrompt = e;
    });

    // iOS app shell only: capacitor.config.json holds the splash screen until it is hidden, and on
    // Android MainActivity does that itself after its biometric/route check. Nothing native does it
    // on iOS, so hide it as soon as the page is ready (otherwise it waits for the 10 s timeout).
    document.addEventListener('DOMContentLoaded', function () {
        try {
            if (window.Capacitor && typeof Capacitor.getPlatform === 'function' && Capacitor.getPlatform() === 'ios'
                && Capacitor.Plugins && Capacitor.Plugins.SplashScreen) {
                Capacitor.Plugins.SplashScreen.hide();
            }
        } catch (e) {}
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>
