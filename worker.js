/* global self, caches, fetch */
'use strict'

var cachename = 'riichi-mahjong-go-shogi'
var urlstocache = [
  'index.css',
  'index.html',
  'vue-google-signin-button.min.js',
  'vue-facebook-signin-button.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css',
  'https://use.fontawesome.com/releases/v5.8.1/css/all.css',
  //'https://cdn.jsdelivr.net/npm/vue-ctk-date-time-picker@1.4.1/dist/vue-ctk-date-time-picker.css',
  //'https://cdn.jsdelivr.net/npm/vue-ctk-date-time-picker@1.4.1/dist/vue-ctk-date-time-picker.umd.min.js',
  "https://unpkg.com/vue-datetime@1.0.0-beta.8/dist/vue-datetime.min.css",
  "https://cdn.jsdelivr.net/npm/luxon@1.13.2/build/global/luxon.min.js",
  "https://cdn.jsdelivr.net/npm/weekstart@1.0.0/dist/commonjs/main.min.js",
  "https://unpkg.com/vue-datetime@1.0.0-beta.8/dist/vue-datetime.min.js",
];

// install/cache page assets
self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(cachename)
      .then(function (cache) {
        console.log('cache opened')
        return cache.addAll(urlstocache)
      })
  )
})

// intercept page requests
self.addEventListener('fetch', function (event) {
  console.log(event.request.url)
  event.respondWith(
    caches.match(event.request).then(function (response) {
      // serve requests from cache (if found)
      return response || fetch(event.request)
    })
  )
})

// service worker activated, remove outdated cache
self.addEventListener('activate', function (event) {
  console.log('worker activated')
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (key) {
          // filter old versioned keys
          return key !== cachename
        }).map(function (key) {
          return caches.delete(key)
        })
      )
    })
  )
})