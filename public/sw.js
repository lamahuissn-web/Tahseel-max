const CACHE_NAME = 'tahseel-cache-v2';

// Install event
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/assets/media/logos/default.svg',
                '/assets/media/logos/favicon.ico',
            ]);
        })
    );
});

// Activate event — clean old caches and take control
self.addEventListener('activate', (event) => {
    event.waitUntil(Promise.all([
        self.clients.claim(),
        caches.keys().then((keys) => {
            return Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)));
        }),
    ]));
});

// Fetch event (Network first)
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith('http')) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Check if we received a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Clone the response
                var responseToCache = response.clone();

                caches.open(CACHE_NAME)
                    .then((cache) => {
                        cache.put(event.request, responseToCache);
                    });

                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});
