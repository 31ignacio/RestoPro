<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Erreur')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
            font-family: 'Inter', system-ui, sans-serif;
            background: #0f1117;
            color: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(circle at top, rgba(201,169,110,.14), transparent 38%),
                        linear-gradient(180deg, #11141c 0%, #0b0c10 100%);
        }
        .error-shell {
            width: 100%;
            max-width: 900px;
        }
        .error-panel {
            background: rgba(15,17,23,.96);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }
        .error-hero {
            padding: 44px 38px 38px;
            text-align: center;
        }
        .error-illustration {
            margin: 0 auto 26px;
            width: min(280px, 100%);
            height: 240px;
            display: grid;
            place-items: center;
            border-radius: 28px;
            background: radial-gradient(circle at top left, rgba(201,169,110,.24), transparent 35%),
                        radial-gradient(circle at bottom right, rgba(99,102,241,.18), transparent 30%),
                        linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.01));
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.04);
        }
        .error-illustration span {
            font-size: 82px;
            line-height: 1;
        }
        .error-code {
            font-size: clamp(60px, 9vw, 96px);
            font-weight: 800;
            margin: 0 0 10px;
            letter-spacing: -.05em;
            color: #c9a96e;
        }
        .error-headline {
            margin: 0 0 18px;
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 800;
            letter-spacing: -.03em;
        }
        .error-message {
            margin: 0 auto 28px;
            max-width: 620px;
            font-size: 16px;
            line-height: 1.8;
            color: #cbd5e1;
        }
        .error-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
        }
        .error-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
            padding: 14px 22px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .btn-primary {
            background: #c9a96e;
            color: #0f1117;
            box-shadow: 0 16px 35px rgba(201,169,110,.25);
        }
        .btn-secondary {
            background: rgba(255,255,255,.05);
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,.12);
        }
        .error-actions a:hover { transform: translateY(-1px); }
        .error-actions a:active { transform: translateY(0); }
        .error-note {
            margin-top: 20px;
            font-size: 13px;
            color: #94a3b8;
        }
        @media (max-width: 560px) {
            .error-hero { padding: 32px 24px 28px; }
            .error-illustration { height: 200px; }
            .error-actions { gap: 10px; }
            .error-actions a { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="error-shell">
        <div class="error-panel">
            <div class="error-hero">
                <div class="error-illustration">
                    @yield('illustration')
                </div>
                <div class="error-code">@yield('code')</div>
                <div class="error-headline">@yield('headline')</div>
                <div class="error-message">@yield('message')</div>
                <div class="error-actions">
                    <a class="btn-primary" href="{{ url('/') }}">Accueil</a>
                    <a class="btn-secondary" href="javascript:history.back()">Retour</a>
                </div>
                @hasSection('note')
                <div class="error-note">@yield('note')</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
