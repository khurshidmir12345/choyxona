/*
 * Telegram mini ilova ichida sayt: to'liq ekran (Android/iOS, Bot API 8.0+),
 * xavfsiz maydonlar (soat/kamera va Telegram tugmalari ustiga chiqmaslik),
 * pastga surib yopilib ketmaslik. Faqat sessiyada telegram_app bo'lsa ulanadi
 * (layouts/admin.blade.php). Brauzerda ochilsa hech narsa qilmaydi.
 */
(function () {
    const tg = window.Telegram && window.Telegram.WebApp;
    if (!tg) return;

    // Skript oddiy brauzerda ham yuklanishi mumkin — u holda platform 'unknown' va initData bo'sh.
    const inside = (tg.initData && tg.initData.length > 0) || (tg.platform && tg.platform !== 'unknown');
    if (!inside) return;

    const root = document.documentElement;
    root.classList.add('is-telegram');
    root.classList.add('tg-' + (tg.platform || 'unknown'));

    try { tg.ready(); tg.expand(); } catch (e) {}
    try { tg.disableVerticalSwipes && tg.disableVerticalSwipes(); } catch (e) {}
    try { tg.enableClosingConfirmation && tg.enableClosingConfirmation(); } catch (e) {}

    const goFullscreen = () => {
        try {
            if (tg.isVersionAtLeast && tg.isVersionAtLeast('8.0') && !tg.isFullscreen) tg.requestFullscreen();
        } catch (e) {}
    };
    goFullscreen();

    const px = (v) => (Number(v) || 0) + 'px';

    const applyInsets = () => {
        const s = tg.safeAreaInset || {};
        const c = tg.contentSafeAreaInset || {};
        root.style.setProperty('--tg-safe-top', px((s.top || 0) + (c.top || 0)));
        root.style.setProperty('--tg-safe-bottom', px(s.bottom || 0));
        root.style.setProperty('--tg-safe-left', px((s.left || 0) + (c.left || 0)));
        root.style.setProperty('--tg-safe-right', px((s.right || 0) + (c.right || 0)));
        root.classList.toggle('tg-fullscreen', !!tg.isFullscreen);
    };
    applyInsets();

    const syncTheme = () => {
        const dark = root.getAttribute('data-theme') === 'dark' || document.body.classList.contains('dark');
        try {
            tg.setHeaderColor && tg.setHeaderColor(dark ? '#161a2b' : '#ffffff');
            tg.setBackgroundColor && tg.setBackgroundColor(dark ? '#161a2b' : '#f6f7fb');
            tg.setBottomBarColor && tg.setBottomBarColor(dark ? '#161a2b' : '#ffffff');
        } catch (e) {}
    };
    syncTheme();

    if (tg.onEvent) {
        ['safeAreaChanged', 'contentSafeAreaChanged', 'fullscreenChanged', 'viewportChanged'].forEach((ev) => tg.onEvent(ev, applyInsets));
        tg.onEvent('fullscreenFailed', () => { try { tg.expand(); } catch (e) {} });
        tg.onEvent('activated', goFullscreen);
    }

    new MutationObserver(syncTheme).observe(root, { attributes: true, attributeFilter: ['data-theme', 'class'] });
})();
