/*
 * Yuqori paneldagi onlayn/oflayn ko'rsatkich + sinxronlanmagan sotuvlar soni.
 * Service worker ham shu yerda ro'yxatdan o'tadi — oflayn kassa keshlanadi.
 */
(function () {
    function pending() {
        try { return (JSON.parse(localStorage.getItem('pos.quick.queue')) || []).length; } catch (e) { return 0; }
    }

    function render() {
        var online = navigator.onLine;
        var n = pending();
        document.querySelectorAll('[data-net-status]').forEach(function (el) {
            el.classList.toggle('is-online', online);
            el.classList.toggle('is-offline', !online);
            var icon = el.querySelector('i');
            if (icon) icon.className = 'mdi ' + (online ? 'mdi-wifi' : 'mdi-wifi-off');
            var label = el.querySelector('[data-net-label]');
            if (label) label.textContent = online ? 'Onlayn' : 'Oflayn';
            var badge = el.querySelector('[data-net-pending]');
            if (badge) { badge.textContent = n; badge.hidden = n === 0; }
            el.title = online
                ? (n ? n + ' ta sotuv sinxronlanmagan — sotuv ekranida "Sinxronlash"ni bosing' : 'Internet bor')
                : 'Internet yo\'q — sotuv ekrani oflayn ishlaydi';
        });
    }

    window.addEventListener('online', render);
    window.addEventListener('offline', render);
    window.addEventListener('pos-queue-changed', render);
    window.addEventListener('storage', render);
    document.addEventListener('DOMContentLoaded', render);
    render();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').then(function (reg) {
                // Har kirishda sotuv ekrani va fayllari keshi yangilanadi
                if (reg.active) reg.active.postMessage('precache');
            }).catch(function () {});
        });
    }
})();
