const CACHE_NAME = 'sushako-shell-v3';
const APP_SHELL = [
    '/offline.html',
    '/site.webmanifest',
    '/assets/brand/sushako-shopping-official-icon-192.png',
    '/assets/brand/sushako-shopping-official-icon-512.png',
];

const NEVER_CACHE = ['/admin', '/seller', '/account', '/checkout', '/orders', '/cart', '/webhooks'];
const COMMERCE_PAGES = ['/shop', '/deals', '/products', '/category', '/departments', '/collections', '/stores', '/search'];
const STATIC_ASSET = /\.(?:css|js|woff2?|ttf|otf|eot|png|jpe?g|webp|avif|svg|ico|webmanifest)$/i;

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)),
        )),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) return;

    const url = new URL(request.url);
    if (NEVER_CACHE.some((prefix) => url.pathname.startsWith(prefix))) {
        return;
    }

    // Live commerce and navigation HTML must never fall back to cached prices.
    if (COMMERCE_PAGES.some((prefix) => url.pathname.startsWith(prefix)) || request.mode === 'navigate' || url.pathname === '/') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    if (!STATIC_ASSET.test(url.pathname) && url.pathname !== '/') return;

    event.respondWith(
        fetch(request)
            .then((response) => {
                if (response.ok && response.type === 'basic' && request.destination !== 'document') {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return response;
            })
            .catch(() => caches.match(request).then((cached) => cached || caches.match('/'))),
    );
});
