<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 — Service indisponible</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            position: relative; overflow: hidden;
            background: #0f1117;
        }

        /* ══ FOND IMAGE ══ */
        .bg-image {
            position: fixed; inset: 0;
            background-image: url('https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=1600&q=80&auto=format&fit=crop');
            background-size: cover; background-position: center;
            filter: brightness(.22) saturate(.5);
            z-index: 0;
        }
        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15,17,23,.94) 0%,
                rgba(15,17,23,.65) 50%,
                rgba(59,130,246,.07) 100%
            );
            z-index: 1;
        }
        .grain {
            position: fixed; inset: 0; z-index: 2; opacity: .025;
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

        /* Badge bleu */
        .error-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(59,130,246,.13);
            border: 1px solid rgba(59,130,246,.28);
            border-radius: 30px; padding: 6px 18px;
            font-size: 12px; font-weight: 700; color: #93c5fd;
            letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 24px;
        }
        .error-badge i { font-size: 13px; }

        /* Icône animée maintenance */
        .maint-icon {
            width: 72px; height: 72px; border-radius: 20px;
            background: rgba(59,130,246,.12);
            border: 1px solid rgba(59,130,246,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: #93c5fd;
            margin: 0 auto 20px;
            animation: wobble 3s ease-in-out infinite;
        }
        @keyframes wobble {
            0%,100% { transform: rotate(-4deg) scale(1); }
            50%      { transform: rotate(4deg)  scale(1.06); }
        }

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
        .error-note  {
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

        /* ══ STATUT SERVICES ══ */
        .status-box {
            margin-top: 44px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 16px; padding: 20px 24px;
            width: 100%; max-width: 400px;
            backdrop-filter: blur(8px);
            text-align: left;
        }
        .status-box-title {
            font-size: 11px; font-weight: 700;
            color: rgba(255,255,255,.35);
            text-transform: uppercase; letter-spacing: .1em;
            margin-bottom: 14px;
        }
        .status-row {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .status-row:last-child { border-bottom: none; }
        .status-dot {
            width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0;
        }
        .status-dot.ok       { background: #7ec88a; box-shadow: 0 0 6px #7ec88a60; }
        .status-dot.warn     { background: #c9a96e; box-shadow: 0 0 6px #c9a96e60; animation: blink 1.4s ease-in-out infinite; }
        .status-dot.down     { background: #f87171; box-shadow: 0 0 6px #f8717160; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
        .status-name  { flex: 1; font-size: 13px; font-weight: 600; color: rgba(255,255,255,.75); }
        .status-label { font-size: 11.5px; font-weight: 600; }
        .status-label.ok   { color: #7ec88a; }
        .status-label.warn { color: #c9a96e; }
        .status-label.down { color: #f87171; }

        /* Pulse loading dots */
        .loading-dots {
            display: flex; gap: 5px; justify-content: center;
            margin-top: 36px;
        }
        .loading-dots span {
            width: 7px; height: 7px; border-radius: 50%;
            background: rgba(255,255,255,.25);
            animation: dotpulse 1.4s ease-in-out infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: .2s; }
        .loading-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes dotpulse {
            0%,80%,100% { transform: scale(1); background: rgba(255,255,255,.2); }
            40%          { transform: scale(1.4); background: #c9a96e; }
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
            <i class="bi bi-tools"></i>
            Erreur 503
        </div>

        <div class="maint-icon">
            <i class="bi bi-tools"></i>
        </div>

        <div class="error-code">503</div>

        <div class="divider"></div>

        <h1 class="error-title">Service temporairement indisponible</h1>

        <p class="error-msg">
            Le service est momentanément indisponible, peut-être à cause d'une
            maintenance ou d'un trafic élevé. Réessayez dans quelques minutes.
        </p>

        <div class="error-note">
            <i class="bi bi-info-circle-fill"></i>
            En cas de maintenance, le service reprendra bientôt.
        </div>

        <div class="btn-group">
            <button class="btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                Réessayer
            </button>
            <a href="{{ url('/') }}" class="btn-secondary">
                <i class="bi bi-house-fill"></i>
                Accueil
            </a>
        </div>

        {{-- Statut des services --}}
        <div class="status-box">
            <div class="status-box-title">État des services</div>
            <div class="status-row">
                <span class="status-dot warn"></span>
                <span class="status-name">Application</span>
                <span class="status-label warn">Maintenance</span>
            </div>
            <div class="status-row">
                <span class="status-dot ok"></span>
                <span class="status-name">Base de données</span>
                <span class="status-label ok">Opérationnel</span>
            </div>
            <div class="status-row">
                <span class="status-dot ok"></span>
                <span class="status-name">Stockage</span>
                <span class="status-label ok">Opérationnel</span>
            </div>
            <div class="status-row">
                <span class="status-dot down"></span>
                <span class="status-name">API serveur</span>
                <span class="status-label down">Indisponible</span>
            </div>
        </div>

        {{-- Dots d'attente --}}
        <div class="loading-dots">
            <span></span><span></span><span></span>
        </div>

    </main>

    <script>
        // Retry automatique toutes les 60s
        setTimeout(() => location.reload(), 60000);
    </script>

</body>
</html>