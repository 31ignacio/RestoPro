<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page non trouvée</title>
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
            background-image: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1600&q=80&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            filter: brightness(.3) saturate(.7);
            z-index: 0;
        }

        /* Overlay dégradé */
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15, 17, 23, .88) 0%,
                rgba(15, 17, 23, .60) 50%,
                rgba(201, 169, 110, .10) 100%
            );
            z-index: 1;
        }

        /* Grain subtil */
        .grain {
            position: fixed;
            inset: 0;
            z-index: 2;
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
            text-decoration: none;
            z-index: 20;
        }
        .brand-icon {
            width: 40px; height: 40px;
            border-radius: 11px;
            background: #c9a96e;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 900;
            color: #0f1117;
        }
        .brand-name {
            font-size: 15px; font-weight: 700;
            color: rgba(255,255,255,.9);
            letter-spacing: -.2px;
        }

        /* ══ CONTENU ══ */
        .page {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }

        /* Badge */
        .error-badge {
            display: inline-flex;
            align-items: center; gap: 8px;
            background: rgba(201, 169, 110, .15);
            border: 1px solid rgba(201, 169, 110, .3);
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 12px; font-weight: 700;
            color: #c9a96e;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .error-badge i { font-size: 13px; }

        /* Grand code */
        .error-code {
            font-size: clamp(90px, 18vw, 160px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -6px;
            background: linear-gradient(135deg, #ffffff 30%, #c9a96e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            user-select: none;
        }

        /* Ligne déco */
        .divider {
            width: 56px; height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, #c9a96e, transparent);
            margin: 0 auto 32px;
        }

        /* Titre */
        .error-title {
            font-size: clamp(22px, 4vw, 32px);
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 14px;
            letter-spacing: -.4px;
        }

        /* Message */
        .error-msg {
            max-width: 480px;
            font-size: 15px; line-height: 1.65;
            color: rgba(255,255,255,.6);
            margin-bottom: 10px;
        }

        /* Note */
        .error-note {
            display: flex;
            align-items: center; gap: 7px;
            font-size: 13px;
            color: rgba(201, 169, 110, .8);
            margin-bottom: 40px;
        }
        .error-note i { font-size: 14px; color: #c9a96e; }

        /* Boutons */
        .btn-group {
            display: flex; gap: 12px;
            flex-wrap: wrap; justify-content: center;
        }
       
        .btn-back {
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
        .btn-back:hover {
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.3);
            color: #fff;
            transform: translateY(-1px);
        }

        
        .ql-item {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px;
            font-size: 12.5px; font-weight: 500;
            color: rgba(255,255,255,.6);
            text-decoration: none;
            transition: all .15s;
            backdrop-filter: blur(4px);
        }
        .ql-item:hover {
            background: rgba(201,169,110,.15);
            border-color: rgba(201,169,110,.35);
            color: #c9a96e;
        }
        .ql-item i { font-size: 13px; }

        /* Responsive */
        @media (max-width: 480px) {
            .brand { left: 20px; top: 20px; }
            .btn-group { flex-direction: column; align-items: center; }
            .btn-back { width: 100%; max-width: 280px; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>
    <div class="grain"></div>

    {{-- Logo --}}
    <a href="{{ url('/') }}" class="brand">
        <div class="brand-icon">R</div>
        <span class="brand-name">RestoPro</span>
    </a>

    {{-- Contenu --}}
    <main class="page">

        <div class="error-badge">
            <i class="bi bi-search"></i>
            Erreur 404
        </div>

        <div class="error-code">404</div>

        <div class="divider"></div>

        <h1 class="error-title">Oups, page introuvable</h1>

        <p class="error-msg">
            Le lien que vous avez suivi n'existe pas ou a été déplacé.
            Vérifiez l'URL ou retournez à l'accueil pour continuer.
        </p>

        <div class="error-note">
            <i class="bi bi-info-circle-fill"></i>
            Si vous pensez que c'est une erreur du site, contactez l'administrateur.
        </div>

        <div class="btn-group">
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left"></i>
                Page précédente
            </a>
        </div>

    </main>

</body>
</html>