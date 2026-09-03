/*
 * Service worker: oflayn kassa (/pos/oflayn) va statik fayllar keshlanadi.
 * Internet yo'q paytda istalgan sahifaga kirilsa — oflayn kassa ochiladi.
 * API va boshqa sahifalar keshlanmaydi.
 */
const VERSION = 'pos-v1';
const OFFLINE_PAGE = '/pos/oflayn';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(VERSION).then((cache) => cache.add(new Request(OFFLINE_PAGE, { credentials: 'same-origin' })).catch(() => null))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

function isStatic(request) {
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return false;
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/livewire/update') || url.pathname.startsWith('/_debugbar')) return false;
    return ['style', 'script', 'font', 'image'].includes(request.destination) || url.pathname.startsWith('/livewire/livewire');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    if (request.mode === 'navigate') {
        const url = new URL(request.url);
        if (url.pathname === OFFLINE_PAGE) {
            // Oflayn kassa: avval tarmoq, bo'lmasa kesh.
            event.respondWith(
                fetch(request).then((res) => {
                    if (res.ok) caches.open(VERSION).then((c) => c.put(OFFLINE_PAGE, res.clone()));
                    return res;
                }).catch(() => caches.match(OFFLINE_PAGE))
            );
        } else {
            event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_PAGE)));
        }
        return;
    }

    if (isStatic(request)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request).then((res) => {
                    if (res.ok) caches.open(VERSION).then((c) => c.put(request, res.clone()));
                    return res;
                }).catch(() => cached);
                return cached || network;
            })
        );
    }
});
