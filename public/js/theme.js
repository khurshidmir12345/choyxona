/*
 * Tungi / kunduzgi rejim. Tanlov localStorage'da ("theme": dark | light),
 * <html data-theme="..."> orqali qo'llanadi. Skript <head> ichida CSS'dan
 * oldin ishlaydi — sahifa ochilganda "yaltirash" bo'lmaydi.
 */
(function () {
    var KEY = 'theme';

    function preferred() {
        try {
            var saved = localStorage.getItem(KEY);
            if (saved === 'dark' || saved === 'light') return saved;
        } catch (e) {}
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.style.colorScheme = theme;
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            var icon = btn.querySelector('i');
            if (icon) icon.className = theme === 'dark' ? 'mdi mdi-white-balance-sunny' : 'mdi mdi-weather-night';
            btn.setAttribute('title', theme === 'dark' ? 'Kunduzgi rejim' : 'Tungi rejim');
        });
    }

    window.toggleTheme = function () {
        var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        try { localStorage.setItem(KEY, next); } catch (e) {}
        apply(next);
    };

    apply(preferred());
    document.addEventListener('DOMContentLoaded', function () { apply(preferred()); });
})();
