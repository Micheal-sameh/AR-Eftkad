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

        toggleLocale() {
            const next = this.locale() === 'ar' ? 'en' : 'ar';
            localStorage.setItem(this.LOCALE_KEY, next);
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

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        Eftkad.deferredInstallPrompt = e;
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>
