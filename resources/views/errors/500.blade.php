<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Erreur serveur</title>
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
            background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1600&q=80&auto=format&fit=crop');
            background-size: cover; background-position: center;
            filter: brightness(.2) saturate(.4);
            z-index: 0;
        }
        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15,17,23,.95) 0%,
                rgba(15,17,23,.68) 50%,
                rgba(239,68,68,.06) 100%
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

        /* Badge orange-rouge */
        .error-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(239,68,68,.13);
            border: 1px solid rgba(239,68,68,.28);
            border-radius: 30px; padding: 6px 18px;
            font-size: 12px; font-weight: 700; color: #fca5a5;
            letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 24px;
        }
        .error-badge i { font-size: 13px; }

        /* Icône alerte animée */
        .alert-icon {
            width: 72px; height: 72px; border-radius: 20px;
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #fca5a5;
            margin: 0 auto 20px;
            animation: pulse-alert 2s ease-in-out infinite;
        }
        @keyframes pulse-alert {
            0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,.25); }
            50%      { box-shadow: 0 0 0 12px rgba(239,68,68,.0); }
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
        
        /* ══ BLOC INFO ERREUR ══ */
        .error-box {
            margin-top: 44px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 16px; padding: 0;
            width: 100%; max-width: 420px;
            backdrop-filter: blur(8px);
            overflow: hidden;
            text-align: left;
        }
        .error-box-head {
            padding: 14px 20px;
            background: rgba(239,68,68,.08);
            border-bottom: 1px solid rgba(239,68,68,.15);
            display: flex; align-items: center; gap: 8px;
        }
        .error-box-head i { color: #fca5a5; font-size: 14px; }
        .error-box-head span { font-size: 12px; font-weight: 700; color: #fca5a5; text-transform: uppercase; letter-spacing: .07em; }

        .error-box-row {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 11px 20px;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }
        .error-box-row:last-child { border-bottom: none; }
        .ebr-key {
            font-size: 11.5px; font-weight: 700;
            color: rgba(255,255,255,.3); text-transform: uppercase;
            letter-spacing: .06em; min-width: 80px; padding-top: 1px;
        }
        .ebr-val { font-size: 13px; color: rgba(255,255,255,.65); font-family: 'Courier New', monospace; word-break: break-all; }
        .ebr-val.highlight { color: #fca5a5; }

        /* Dots */
        .loading-dots { display: flex; gap: 5px; justify-content: center; margin-top: 28px; }
        .loading-dots span {
            width: 7px; height: 7px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            animation: dotpulse 1.4s ease-in-out infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: .2s; }
        .loading-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes dotpulse {
            0%,80%,100% { transform: scale(1); background: rgba(255,255,255,.15); }
            40%          { transform: scale(1.4); background: #fca5a5; }
        }

        @media (max-width: 480px) {
            .brand { left: 20px; top: 20px; }
            .btn-group { flex-direction: column; align-items: center; }
            .btn-primary { width: 100%; max-width: 280px; justify-content: center; }
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
            <i class="bi bi-exclamation-triangle-fill"></i>
            Erreur 500
        </div>

        <div class="alert-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>

        <div class="error-code">500</div>

        <div class="divider"></div>

        <h1 class="error-title">Une erreur est survenue</h1>

        <p class="error-msg">
            Le serveur rencontre un problème temporaire. Réessayez dans quelques
            instants ou retournez à l'accueil pour reprendre.
        </p>

        <div class="error-note">
            <i class="bi bi-info-circle-fill"></i>
            Notre équipe technique a été prévenue si le problème persiste.
        </div>

        <div class="btn-group">
            <button class="btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                Réessayer
            </button>
        </div>

        {{-- Infos techniques --}}
        <div class="error-box">
            <div class="error-box-head">
                <i class="bi bi-terminal"></i>
                <span>Détails techniques</span>
            </div>
            <div class="error-box-row">
                <div class="ebr-key">Code</div>
                <div class="ebr-val highlight">HTTP 500 — Erreur interne du serveur</div>
            </div>
            <div class="error-box-row">
                <div class="ebr-key">Heure</div>
                <div class="ebr-val" id="err-time">—</div>
            </div>
            <div class="error-box-row">
                <div class="ebr-key">URL</div>
                <div class="ebr-val" id="err-url">—</div>
            </div>
            <div class="error-box-row">
                <div class="ebr-key">Réf.</div>
                <div class="ebr-val" id="err-ref">—</div>
            </div>
        </div>

        <div class="loading-dots"><span></span><span></span><span></span></div>

    </main>

    <script>
        // Infos techniques dynamiques
        document.getElementById('err-time').textContent =
            new Date().toLocaleString('fr-FR', { timeZone: 'Europe/Paris', dateStyle:'short', timeStyle:'medium' });
        document.getElementById('err-url').textContent =
            window.location.pathname || '/';
        document.getElementById('err-ref').textContent =
            'ERR-' + Math.random().toString(36).substring(2,10).toUpperCase();
    </script>

</body>
</html>