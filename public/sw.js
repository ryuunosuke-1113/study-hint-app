const CACHE_NAME = "study-hint-v5";

self.addEventListener("install", (event) => {
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener("fetch", (event) => {
    // まずは何もしない。PWAインストール対応だけにする
});
