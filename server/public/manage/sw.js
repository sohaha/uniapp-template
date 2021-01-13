let RUNTIME = 'zls.admin'
let HOSTNAME_WHITELIST = [self.location.hostname, 'api.73zls.com', 'fonts.gstatic.com', 'fonts.googleapis.com', 'unpkg.com', 'cdn.jsdelivr.net']
const getFixedUrl = (req) => {
    var now = Date.now()
    let url = new URL(req.url)
    url.protocol = self.location.protocol
    if (url.hostname === self.location.hostname) { url.search += (url.search ? '&' : '?') + 'cache-bust=' + now }
    return url.href
}
self.addEventListener('install', event => { self.skipWaiting() })
self.addEventListener('activate', event => { event.waitUntil(self.clients.claim()) })
self.addEventListener('fetch', event => {
    if (HOSTNAME_WHITELIST.indexOf(new URL(event.request.url).hostname) > -1) {
        const cached = caches.match(event.request)
        const fixedUrl = getFixedUrl(event.request)
        const fetched = fetch(fixedUrl, { cache: 'no-store' })
        const fetchedCopy = fetched.then(resp => resp.clone())
        event.respondWith(Promise.race([fetched.catch(_ => cached), cached]).then(resp => resp || fetched).catch(_ => { }))
        event.waitUntil(Promise.all([fetchedCopy, caches.open(RUNTIME)]).then(([response, cache]) => response.ok && cache.put(event.request, response)).catch(_ => { }))
    }
})