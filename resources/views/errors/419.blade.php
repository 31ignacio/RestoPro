<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 — Page expirée</title>
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
            position: fixed;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1559329007-40df8a9345d8?w=1600&q=80&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            filter: brightness(.28) saturate(.65);
            z-index: 0;
        }

        .bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15, 17, 23, .90) 0%,
                rgba(15, 17, 23, .60) 50%,
                rgba(148, 163, 184, .08) 100%
            );
            z-index: 1;
        }

        .grain {
            position: fixed;
            inset: 0; z-index: 2;
            opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 180px;
            pointer-events: none;
        }

        /* ══ LOGO ══ */
        .brand {
            position: fixed;
            top: 28px; left: 36px;
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; z-index: 20;
        }
        .brand-icon {
            width: 40px; height: 40px; border-radius: 11px;
            background: #c9a96e;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 900; color: #0f1117;
        }
        .brand-name {
            font-size: 15px; font-weight: 700;
            color: rgba(255,255,255,.9); letter-spacing: -.2px;
        }

        /* ══ PAGE ══ */
        .page {
            position: relative; z-index: 10;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px 20px; text-align: center;
        }

        /* Badge bleu-gris */
        .error-badge {
            display: inline-flex;
            align-items: center; gap: 8px;
            background: rgba(148, 163, 184, .13);
            border: 1px solid rgba(148, 163, 184, .28);
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 12px; font-weight: 700;
            color: #94a3b8;
            letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 24px;
        }
        .error-badge i { font-size: 13px; }

        /* Code */
        .error-code {
            font-size: clamp(90px, 18vw, 160px);
            font-weight: 900; line-height: 1;
            letter-spacing: -6px;
            background: linear-gradient(135deg, #ffffff 30%, #c9a96e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            user-select: none;
        }

        .divider {
            width: 56px; height: 3px; border-radius: 2px;
            background: linear-gradient(90deg, #c9a96e, transparent);
            margin: 0 auto 32px;
        }

        .error-title {
            font-size: clamp(22px, 4vw, 32px);
            font-weight: 800; color: #ffffff;
            margin-bottom: 14px; letter-spacing: -.4px;
        }

        .error-msg {
            max-width: 480px;
            font-size: 15px; line-height: 1.65;
            color: rgba(255,255,255,.6);
            margin-bottom: 10px;
        }

        .error-note {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; color: rgba(201,169,110,.8);
            margin-bottom: 40px;
        }
        .error-note i { font-size: 14px; color: #c9a96e; }

        /* Boutons */
        .btn-group {
            display: flex; gap: 12px;
            flex-wrap: wrap; justify-content: center;
        }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px;
            background: #c9a96e; color: #0f1117;
            border: none; border-radius: 12px;
            font-size: 14px; font-weight: 700;
            text-decoration: none; cursor: pointer;
            transition: opacity .15s, transform .12s;
        }
        .btn-primary:hover { opacity: .88; transform: translateY(-1px); color: #0f1117; }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px;
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.85);
            border: 1.5px solid rgba(255,255,255,.15);
            border-radius: 12px;
            font-size: 14px; font-weight: 600;
            text-decoration: none; cursor: pointer;
            transition: all .15s;
            backdrop-filter: blur(6px);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.3);
            color: #fff; transform: translateY(-1px);
        }

        /* Compteur de rechargement */
        .reload-hint {
            margin-top: 36px;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
        }
        .reload-ring {
            width: 52px; height: 52px;
            position: relative;
        }
        .reload-ring svg {
            width: 52px; height: 52px;
            transform: rotate(-90deg);
        }
        .ring-bg   { fill: none; stroke: rgba(255,255,255,.08); stroke-width: 4; }
        .ring-fill {
            fill: none; stroke: #c9a96e; stroke-width: 4;
            stroke-linecap: round;
            stroke-dasharray: 126;
            stroke-dashoffset: 126;
            animation: countdown 10s linear forwards;
        }
        @keyframes countdown {
            to { stroke-dashoffset: 0; }
        }
        .ring-num {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 800;
            color: rgba(255,255,255,.8);
        }
        .reload-txt {
            font-size: 12px; color: rgba(255,255,255,.35);
            letter-spacing: .03em;
        }

        @media (max-width: 480px) {
            .brand { left: 20px; top: 20px; }
            .btn-group { flex-direction: column; align-items: center; }
            .btn-primary, .btn-secondary { width: 100%; max-width: 280px; justify-content: center; }
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
            <i class="bi bi-hourglass-split"></i>
            Erreur 419
        </div>

        <div class="error-code">419</div>

        <div class="divider"></div>

        <h1 class="error-title">Session expirée</h1>

        <p class="error-msg">
            Votre session a expiré ou la page a été ouverte trop longtemps.
            Rafraîchissez la page ou reconnectez-vous pour continuer.
        </p>

        <div class="error-note">
            <i class="bi bi-info-circle-fill"></i>
            Si le problème persiste, déconnectez-vous puis reconnectez-vous.
        </div>

        <div class="btn-group">
            <button class="btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                Rafraîchir la page
            </button>
            <a href="{{ url('/login') }}" class="btn-secondary">
                <i class="bi bi-box-arrow-in-right"></i>
                Se reconnecter
            </a>
        </div>

        {{-- Compteur auto-reload --}}
        <div class="reload-hint">
            <div class="reload-ring">
                <svg viewBox="0 0 52 52">
                    <circle cx="26" cy="26" r="20" class="ring-bg"/>
                    <circle cx="26" cy="26" r="20" class="ring-fill"/>
                </svg>
                <div class="ring-num" id="countdown-num">10</div>
            </div>
            <div class="reload-txt">Rechargement automatique dans <span id="countdown-txt">10</span>s</div>
        </div>

    </main>

    <script>
        // Compte à rebours + reload auto dans 10s
        let t = 10;
        const numEl = document.getElementById('countdown-num');
        const txtEl = document.getElementById('countdown-txt');
        const timer = setInterval(() => {
            t--;
            numEl.textContent = t;
            txtEl.textContent = t;
            if (t <= 0) { clearInterval(timer); location.reload(); }
        }, 1000);
    </script>

</body>
</html>