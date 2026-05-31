const CACHE_NAME = 'meu-caixa-nail-v2';

const urlsToCache = [
    '/',
    '/login.php',
    '/dashboard.php',
    '/resumo.php',
    '/lancamentos/index.php',
    '/assets/css/style.css',
    '/manifest.json',
    '/assets/img/icon-192.png',
    '/assets/img/icon-512.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});