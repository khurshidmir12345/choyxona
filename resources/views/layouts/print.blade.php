<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Chek' }}</title>
    <style>
        /* Chek 58/80 mm termoprinter uchun. Tashqi CSS yuklanmaydi —
           kassa kompyuterida internet bo'lmasligi mumkin. */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', ui-monospace, monospace;
            font-size: 12px;
            line-height: 1.35;
            color: #000;
            background: #eceef2;
            padding: 24px 12px;
        }

        .sheet {
            width: 302px;
            margin: 0 auto;
            background: #fff;
            padding: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: 700; }
        .muted  { color: #555; }
        .small  { font-size: 11px; }

        .brand { font-size: 17px; font-weight: 700; letter-spacing: .5px; }

        .rule {
            border: 0;
            border-top: 1px dashed #999;
            margin: 10px 0;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 0; vertical-align: top; }
        thead th {
            font-size: 11px;
            text-align: left;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        tbody td { border-bottom: 1px dotted #ccc; }

        .totals { margin-top: 10px; }
        .totals div {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .totals .grand {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 6px;
            font-size: 15px;
            font-weight: 700;
        }

        .toolbar {
            width: 302px;
            margin: 0 auto 14px;
            display: flex;
            gap: 8px;
        }
        .toolbar button, .toolbar a {
            flex: 1;
            display: block;
            padding: 10px 12px;
            border: 0;
            border-radius: 8px;
            font: inherit;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar .go   { background: #12866f; color: #fff; }
        .toolbar .back { background: #fff; color: #384153; border: 1px solid #d5dae3; }

        @media print {
            body { background: #fff; padding: 0; }
            .sheet { width: auto; box-shadow: none; padding: 0; }
            .toolbar { display: none; }
            @page { margin: 4mm; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a class="back" href="{{ route('orders.index') }}">Orqaga</a>
    <button type="button" class="go" onclick="window.print()">Chop etish</button>
</div>

<div class="sheet">
    {{ $slot }}
</div>

<script>
    // Chek sahifasi ochilishi bilan chop etish oynasi chiqadi.
    window.addEventListener('load', () => setTimeout(() => window.print(), 350));
</script>
</body>
</html>
