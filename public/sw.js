const CACHE_NAME = 'ims-offline-cache-v5';

const FILES_TO_CACHE = [
    '/offline',
    '/css/welcome.css',
    '/css/admin.css',   
    '/js/admin.js'     
];

// 1. Install Event: Save files to cache gracefully
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Install Event triggered');
    
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[ServiceWorker] Caching files...');
            // Using your map/catch method so one missing file doesn't crash the whole process!
            return Promise.all(
                FILES_TO_CACHE.map(url => {
                    return cache.add(url).catch(err => {
                        console.error('[ServiceWorker] Failed to cache:', url, err);
                    });
                })
            );
        })
    );
    self.skipWaiting();
});

// 2. Activate Event: Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    console.log('[ServiceWorker] Removing old cache:', key);
                    return caches.delete(key);
                }
            }));
        })
    );
    self.clients.claim();
});

// 3. Fetch Event: Intercept network requests
self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        // ignoreSearch allows /offline?returnTo=... to load the cached /offline page!
                        return cache.match('/offline', { ignoreSearch: true });
                    });
                })
        );
    }
});