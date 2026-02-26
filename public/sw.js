const CACHE = 'gvr-v1';

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    const req = e.request;

    // Only intercept GET requests to the same origin
    if (req.method !== 'GET' || !req.url.startsWith(self.location.origin)) return;

    // Skip admin and auth routes — always fresh
    const url = new URL(req.url);
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/login')) return;

    // Cache-first for Vite-compiled static assets (they have content-hash filenames)
    if (url.pathname.startsWith('/build/')) {
        e.respondWith(
            caches.match(req).then(cached => cached ?? fetch(req).then(res => {
                const clone = res.clone();
                caches.open(CACHE).then(c => c.put(req, clone));
                return res;
            }))
        );
        return;
    }

    // Network-first for everything else (pages, media)
    e.respondWith(
        fetch(req).catch(() => caches.match(req))
    );
});
