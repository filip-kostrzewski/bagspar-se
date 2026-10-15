const CACHE = 'bagspar-demo-v3';

const PRECACHE = [
    '/demo',
    '/css/site.css',
    '/js/site.js',
    '/js/demo.js',
    '/favicon.svg',
    '/images/bagspar-mark.svg',
    '/images/demo-qr.svg',
    '/fonts/fraunces-latin.woff2',
    '/fonts/manrope-latin.woff2',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate' && url.pathname === '/demo') {
        event.respondWith(networkFirst(request));

        return;
    }

    if (PRECACHE.some((path) => url.pathname === path)) {
        event.respondWith(networkFirst(request));
    }
});

async function networkFirst(request) {
    const cache = await caches.open(CACHE);

    try {
        const response = await fetch(request);

        if (response.ok) {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request) || await caches.match('/demo');

        if (cached) {
            return cached;
        }

        throw error;
    }
}

