const CACHE_NAME = 'sushako-shell-v2';
const APP_SHELL = [
    '/',
    '/site.webmanifest',
    '/assets/brand/sushako-shopping-official-icon-192.png',
    '/assets/brand/sushako-shopping-official-icon-512.png',
];

const NEVER_CACHE = ['/admin', '/seller', '/account', '/checkout', '/orders', '/cart', '/webhooks'];
const COMMERCE_PAGES = ['/shop', '/products', '/category', '/departments', '/collections', '/stores', '/search'];

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

    // Commerce HTML is always network-first so prices, availability and seller
    // identity do not become stale. The cached shell is only a safe fallback.
    if (COMMERCE_PAGES.some((prefix) => url.pathname.startsWith(prefix))) {
        event.respondWith(fetch(request).catch(() => caches.match(request).then((cached) => cached || caches.match('/'))));
        return;
    }

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
