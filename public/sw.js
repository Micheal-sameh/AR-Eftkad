const CACHE_NAME = 'eftkad-shell-v5';
const APP_SHELL = [
    '/login',
    '/manifest.webmanifest',
    '/icons/icon-192.png?v=2',
    '/icons/icon-512.png?v=2',
];

// Shown when a page is requested offline and was never cached. (The sign-in page is not a
// substitute: a signed-in user would be bounced around instead of being told they are offline.)
const OFFLINE_PAGE = '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8">'
    + '<meta name="viewport" content="width=device-width,initial-scale=1"><title>Eftkad</title>'
    + '<style>body{margin:0;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;'
    + 'background:#faf9f6;color:#263547;font-family:sans-serif;text-align:center;padding:24px;box-sizing:border-box}'
    + 'p{margin:0;line-height:1.7}button{background:#3f5d6d;color:#fff;border:0;border-radius:12px;padding:12px 28px;font-size:16px}</style></head><body>'
    + '<p style="font-size:18px">لا يوجد اتصال بالإنترنت<br>برجاء التحقق من الاتصال والمحاولة مرة أخرى</p>'
    + '<p style="font-size:14px;color:#647080">No internet connection. Please check your connection and try again.</p>'
    + '<button onclick="location.reload()">إعادة المحاولة / Retry</button>'
    + '<script>addEventListener("online",function(){location.reload()})</script></body></html>';

function offlineResponse() {
    return new Response(OFFLINE_PAGE, {
        status: 503,
        headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' },
    });
}

// Only successful responses are worth keeping: caching a 502/500 page would replay that error
// to the user later, long after the server has recovered.
function remember(request, response) {
    if (response && response.ok) {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(function (cache) { cache.put(request, copy); });
    }
    return response;
}

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(APP_SHELL);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (key) { return key !== CACHE_NAME; })
                    .map(function (key) { return caches.delete(key); })
            );
        })
    );
    self.clients.claim();
});

// Network-first for navigations and API calls (data must stay fresh),
// falling back to cache when offline. Cache-first for static assets.
self.addEventListener('fetch', function (event) {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request).catch(function () {
                return caches.match(request).then(function (cached) { return cached || Response.error(); });
            })
        );
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(function (response) { return remember(request, response); })
                .catch(function () {
                    // the sign-in shell is cached on install, so /login itself still works offline
                    return caches.match(request).then(function (cached) { return cached || offlineResponse(); });
                })
        );
        return;
    }

    event.respondWith(
        caches.match(request).then(function (cached) {
            return cached || fetch(request).then(function (response) { return remember(request, response); });
        })
    );
});
