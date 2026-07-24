<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Trop de requêtes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            position: relative;
            overflow: hidden;
            background: #0f1117;
        }

        /* ══ FOND IMAGE ══ */
        .bg-image {
            position: fixed; inset: 0;
            background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600&q=80&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            filter: brightness(.25) saturate(.5);
            z-index: 0;
        }

        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15,17,23,.92) 0%,
                rgba(15,17,23,.62) 50%,
                rgba(99,102,241,.08) 100%
            );
            z-index: 1;
        }

        .grain {
            position: fixed; inset: 0; z-index: 2;
            opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 180px; pointer-events: none;
        }

        /* ══ LOGO ══ */
        .brand {
            position: fixed; top: 28px; left: 36px;
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; z-index: 20;
        }
        .brand-icon {
            width: 40px; height: 40px; border-radius: 11px;
            background: #c9a96e;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 900; color: #0f1117;
        }
        .brand-name { font-size: 15px; font-weight: 700; color: rgba(255,255,255,.9); letter-spacing: -.2px; }

        /* ══ PAGE ══ */
        .page {
            position: relative; z-index: 10;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px 20px; text-align: center;
        }

        /* Badge violet */
        .error-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(99,102,241,.14);
            border: 1px solid rgba(99,102,241,.28);
            border-radius: 30px; padding: 6px 18px;
            font-size: 12px; font-weight: 700; color: #a5b4fc;
            letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 24px;
        }
        .error-badge i { font-size: 13px; }

        /* Code */
        .error-code {
            font-size: clamp(90px, 18vw, 160px);
            font-weight: 900; line-height: 1; letter-spacing: -6px;
            background: linear-gradient(135deg, #ffffff 30%, #c9a96e 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 16px; user-select: none;
        }

        .divider {
            width: 56px; height: 3px; border-radius: 2px;
            background: linear-gradient(90deg, #c9a96e, transparent);
            margin: 0 auto 32px;
        }

        .error-title { font-size: clamp(22px, 4vw, 32px); font-weight: 800; color: #fff; margin-bottom: 14px; letter-spacing: -.4px; }
        .error-msg   { max-width: 480px; font-size: 15px; line-height: 1.65; color: rgba(255,255,255,.6); margin-bottom: 10px; }

        .error-note {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; color: rgba(201,169,110,.8); margin-bottom: 40px;
        }
        .error-note i { font-size: 14px; color: #c9a96e; }

        /* Boutons */
        .btn-group { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px; background: #c9a96e; color: #0f1117;
            border: none; border-radius: 12px; font-size: 14px; font-weight: 700;
            text-decoration: none; cursor: pointer; transition: opacity .15s, transform .12s;
        }
        .btn-primary:hover { opacity: .88; transform: translateY(-1px); color: #0f1117; }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px; background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.85); border: 1.5px solid rgba(255,255,255,.15);
            border-radius: 12px; font-size: 14px; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all .15s; backdrop-filter: blur(6px);
        }
        .btn-secondary:hover { background: rgba(255,255,255,.14); border-color: rgba(255,255,255,.3); color: #fff; transform: translateY(-1px); }

        /* ══ BARRE DE THROTTLE ══ */
        .throttle-box {
            margin-top: 44px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 16px; padding: 20px 28px;
            width: 100%; max-width: 400px;
            backdrop-filter: blur(8px);
        }
        .throttle-top {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 12px;
        }
        .throttle-label { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .08em; }
        .throttle-timer { font-size: 22px; font-weight: 800; color: #fff; font-variant-numeric: tabular-nums; }
        .throttle-timer span { font-size: 12px; font-weight: 500; color: rgba(255,255,255,.4); margin-left: 2px; }

        .throttle-track {
            height: 6px; background: rgba(255,255,255,.08);
            border-radius: 6px; overflow: hidden; margin-bottom: 10px;
        }
        .throttle-fill {
            height: 100%; border-radius: 6px;
            background: linear-gradient(90deg, #6366f1, #c9a96e);
            width: 100%;
            transition: width 1s linear;
        }
        .throttle-hint { font-size: 12px; color: rgba(255,255,255,.3); }

        @media (max-width: 480px) {
            .brand { left: 20px; top: 20px; }
            .btn-group { flex-direction: column; align-items: center; }
            .btn-primary, .btn-secondary { width: 100%; max-width: 280px; justify-content: center; }
            .throttle-box { padding: 16px 18px; }
        }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>
    <div class="grain"></div>

    <a href="{{ url('/') }}" class="brand">
        <div class="brand-icon">R</div>
        <span class="brand-name">RestoPro</span>
    </a>

    <main class="page">

        <div class="error-badge">
            <i class="bi bi-speedometer2"></i>
            Erreur 429
        </div>

        <div class="error-code">429</div>

        <div class="divider"></div>

        <h1 class="error-title">Trop de requêtes</h1>

        <p class="error-msg">
            Vous envoyez trop de requêtes en peu de temps.
            Attendez quelques instants puis réessayez.
        </p>

        <div class="error-note">
            <i class="bi bi-info-circle-fill"></i>
            Le système reprend normalement après un court instant.
        </div>

        <div class="btn-group">
            <button class="btn-primary" id="retry-btn" onclick="retryNow()" disabled
                style="opacity:.45;cursor:not-allowed">
                <i class="bi bi-arrow-clockwise"></i>
                Réessayer
            </button>
            <a href="{{ url('/') }}" class="btn-secondary">
                <i class="bi bi-house-fill"></i>
                Accueil
            </a>
        </div>

        {{-- Barre d'attente --}}
        <div class="throttle-box">
            <div class="throttle-top">
                <div class="throttle-label">Reprise dans</div>
                <div class="throttle-timer" id="throttle-num">30<span>s</span></div>
            </div>
            <div class="throttle-track">
                <div class="throttle-fill" id="throttle-fill"></div>
            </div>
            <div class="throttle-hint">Le bouton "Réessayer" se déverrouille automatiquement</div>
        </div>

    </main>

    <script>
        const WAIT = 30;
        let remaining = WAIT;

        const numEl  = document.getElementById('throttle-num');
        const fillEl = document.getElementById('throttle-fill');
        const btnEl  = document.getElementById('retry-btn');

        // Mise à jour initiale
        fillEl.style.width = '100%';

        const timer = setInterval(() => {
            remaining--;
            // Chiffre
            numEl.innerHTML = remaining + '<span>s</span>';
            // Barre qui se vide
            fillEl.style.width = (remaining / WAIT * 100) + '%';

            if (remaining <= 0) {
                clearInterval(timer);
                numEl.innerHTML = '0<span>s</span>';
                fillEl.style.width = '0%';
                // Déverrouiller le bouton
                btnEl.disabled = false;
                btnEl.style.opacity = '1';
                btnEl.style.cursor  = 'pointer';
            }
        }, 1000);

        function retryNow() {
            location.reload();
        }
    </script>

</body>
</html>