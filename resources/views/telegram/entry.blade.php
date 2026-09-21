<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Choyxona POS — Telegram</title>
    <script src="https://telegram.org/js/telegram-web-app.js?59"></script>
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <style>
        :root {
            --bg: var(--tg-theme-bg-color, #f6f7fb);
            --card: var(--tg-theme-secondary-bg-color, #ffffff);
            --text: var(--tg-theme-text-color, #1e283d);
            --hint: var(--tg-theme-hint-color, #8e94a9);
            --accent: var(--tg-theme-button-color, #1F3BB3);
            --accent-text: var(--tg-theme-button-text-color, #ffffff);
            --line: rgba(128, 128, 128, .18);
            --safe-top: calc(var(--tg-safe-area-inset-top, 0px) + var(--tg-content-safe-area-inset-top, 0px));
            --safe-bottom: var(--tg-safe-area-inset-bottom, 0px);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: var(--bg); color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; flex-direction: column; min-height: 100vh; min-height: 100dvh;
            padding: calc(var(--safe-top) + 24px) 20px calc(var(--safe-bottom) + 24px); }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.15rem; margin-bottom: 28px; }
        .brand i { font-size: 1.6rem; color: var(--accent); }
        .card { background: var(--card); border-radius: 18px; padding: 22px 20px; box-shadow: 0 8px 28px rgba(0,0,0,.06); }
        h1 { font-size: 1.35rem; margin: 0 0 6px; }
        p.hint { color: var(--hint); margin: 0 0 18px; font-size: .95rem; line-height: 1.45; }
        label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
        .field { display: flex; align-items: stretch; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 14px; background: var(--bg); }
        .field span { display: flex; align-items: center; padding: 0 12px; font-weight: 700; color: var(--hint); border-right: 1px solid var(--line); }
        .field input { flex: 1; min-width: 0; border: 0; background: transparent; color: var(--text); padding: 14px 12px; font-size: 1.05rem; font-variant-numeric: tabular-nums; outline: none; }
        .btn { width: 100%; border: 0; border-radius: 12px; padding: 15px; font-size: 1.05rem; font-weight: 700;
            background: var(--accent); color: var(--accent-text); cursor: pointer; }
        .btn:disabled { opacity: .6; }
        .error { background: rgba(243,121,126,.14); color: #d9534f; border-radius: 10px; padding: 10px 12px; font-size: .92rem; margin-bottom: 14px; }
        .loading { text-align: center; padding: 48px 0; color: var(--hint); }
        .loading i { font-size: 2.4rem; display: block; margin-bottom: 10px; color: var(--accent); }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        [hidden] { display: none !important; }
        .foot { margin-top: auto; padding-top: 24px; text-align: center; color: var(--hint); font-size: .8rem; }
        .foot a { color: var(--accent); text-decoration: none; }
    </style>
</head>
<body>
<div class="brand"><i class="mdi mdi-store"></i> Choyxona POS</div>

<div class="card">
    <div id="loading" class="loading">
        <i class="mdi mdi-loading spin"></i>
        Tekshirilmoqda...
    </div>

    <div id="outside" hidden>
        <h1>Telegram ichida oching</h1>
        <p class="hint">Bu sahifa Telegram botdagi «Kassa» tugmasi orqali ochiladi.</p>
        <a class="btn" style="display:block;text-align:center;text-decoration:none" href="{{ route('login') }}">Oddiy kirish</a>
    </div>

    <form id="login" hidden autocomplete="off">
        <h1>Xush kelibsiz<span id="hello"></span></h1>
        <p class="hint">Birinchi marta: telefon raqam va parolingizni kiriting. Keyingi safar kassa o'zi ochiladi.</p>

        <div id="error" class="error" hidden></div>

        <label for="phone">Telefon raqam</label>
        <div class="field">
            <span>+998</span>
            <input id="phone" name="phone_number" type="tel" inputmode="numeric" autocomplete="tel" placeholder="90 123 45 67" required>
        </div>

        <label for="password">Parol</label>
        <div class="field">
            <input id="password" name="password" type="password" inputmode="numeric" autocomplete="current-password" placeholder="8 ta raqam" required>
        </div>

        <button id="submit" class="btn" type="submit">Kirish</button>
    </form>
</div>

<div class="foot">Choyxona POS · <a href="{{ route('login') }}">brauzerda ochish</a></div>

<script>
    (function () {
        const tg = window.Telegram && window.Telegram.WebApp;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const $ = (id) => document.getElementById(id);
        const show = (id) => { ['loading', 'outside', 'login'].forEach(k => $(k).hidden = k !== id); };

        if (tg) {
            try { tg.ready(); tg.expand(); } catch (e) {}
            try { tg.disableVerticalSwipes && tg.disableVerticalSwipes(); } catch (e) {}
            try { if (tg.isVersionAtLeast && tg.isVersionAtLeast('8.0') && !tg.isFullscreen) tg.requestFullscreen(); } catch (e) {}
        }

        const initData = (tg && tg.initData) || '';

        const post = (url, body) => fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body),
        }).then(r => r.json());

        const go = (url) => { window.location.replace(url); };

        if (!initData) {
            show('outside');
            return;
        }

        post(@js(route('telegram.auth')), { init_data: initData }).then((d) => {
            if (d.ok && d.redirect) return go(d.redirect);
            if (d.need_login) {
                if (d.name) $('hello').textContent = ', ' + d.name;
                show('login');
                setTimeout(() => $('phone').focus(), 50);
                return;
            }
            show('outside');
        }).catch(() => show('outside'));

        $('login').addEventListener('submit', (e) => {
            e.preventDefault();
            $('error').hidden = true;
            $('submit').disabled = true;

            post(@js(route('telegram.login')), {
                init_data: initData,
                phone_number: $('phone').value,
                password: $('password').value,
            }).then((d) => {
                if (d.ok && d.redirect) return go(d.redirect);
                $('error').textContent = d.message || 'Kirishda xatolik.';
                $('error').hidden = false;
                $('submit').disabled = false;
            }).catch(() => {
                $('error').textContent = 'Internet bilan aloqa yo\'q.';
                $('error').hidden = false;
                $('submit').disabled = false;
            });
        });
    })();
</script>
</body>
</html>
