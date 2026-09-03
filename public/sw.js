/*
 * Service worker: sotuv ekrani (/pos/tez-sotuv) va unga kerak barcha fayllar
 * (CSS, JS, shriftlar, rasmlar) o'rnatilganda keshlanadi. Internet yo'q
 * paytda istalgan manzil sotuv ekranini ochadi. API keshlanmaydi.
 */
const VERSION = 'pos-v3';
const POS_PAGE = '/pos/tez-sotuv';
const ASSET_RE = /\.(css|js|png|jpe?g|svg|webp|gif|ico|woff2?|ttf|eot)(\?|$)/i;

function sameOrigin(url) { return url.origin === self.location.origin; }

async function precache() {
    const cache = await caches.open(VERSION);
    const page = await fetch(POS_PAGE, { credentials: 'same-origin' });
    if (!page.ok || page.redirected) return;
    await cache.put(POS_PAGE, page.clone());

    const html = await page.text();
    const urls = new Set();
    for (const m of html.matchAll(/(?:href|src)=["']([^"']+)["']/g)) {
        try {
            const u = new URL(m[1], self.location.origin);
            if (sameOrigin(u) && (ASSET_RE.test(u.pathname + u.search) || u.pathname.startsWith('/livewire/livewire'))) urls.add(u.href);
        } catch (e) {}
    }

    // CSS ichidagi shrift va rasmlar (url(...)) ham keshlanadi
    const cssUrls = [...urls].filter(u => /\.css(\?|$)/i.test(new URL(u).pathname + new URL(u).search));
    for (const href of cssUrls) {
        try {
            const res = await fetch(href, { credentials: 'same-origin' });
            if (!res.ok) continue;
            const text = await res.clone().text();
            await cache.put(href, res);
            for (const m of text.matchAll(/url\(\s*["']?([^"')]+)["']?\s*\)/g)) {
                if (m[1].startsWith('data:')) continue;
                try { const u = new URL(m[1], href); if (sameOrigin(u)) urls.add(u.href); } catch (e) {}
            }
        } catch (e) {}
    }

    await Promise.all([...urls].map(u => cache.match(u).then(hit => hit ? null : cache.add(new Request(u, { credentials: 'same-origin' })).catch(() => null))));
}

self.addEventListener('install', (event) => {
    event.waitUntil(precache().catch(() => null).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

// Sahifa "yangila" desa (masalan, sotuv ekrani ochilganda) kesh yangilanadi
self.addEventListener('message', (event) => {
    if (event.data === 'precache') event.waitUntil(precache().catch(() => null));
});

function isStatic(request) {
    const url = new URL(request.url);
    if (!sameOrigin(url)) return false;
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/livewire/update') || url.pathname.startsWith('/_debugbar')) return false;
    return ['style', 'script', 'font', 'image'].includes(request.destination) || ASSET_RE.test(url.pathname) || url.pathname.startsWith('/livewire/livewire');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    if (request.mode === 'navigate') {
        const url = new URL(request.url);
        event.respondWith(
            fetch(request).then((res) => {
                if (url.pathname === POS_PAGE && res.ok && !res.redirected) caches.open(VERSION).then((c) => c.put(POS_PAGE, res.clone()));
                return res;
            }).catch(() => caches.match(POS_PAGE))
        );
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
