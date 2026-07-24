<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Accès interdit</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ══ FOND AVEC IMAGE ══ */
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            position: relative;
            overflow: hidden;
            background: #0f1117;
        }

        /* Image de fond : restaurant sombre et élégant */
        .bg-image {
            position: fixed;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1600&q=80&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            filter: brightness(.35) saturate(.8);
            z-index: 0;
        }

        /* Overlay dégradé par-dessus l'image */
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15, 17, 23, .85) 0%,
                rgba(15, 17, 23, .65) 50%,
                rgba(201, 169, 110, .12) 100%
            );
            z-index: 1;
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

        /* Logo / marque */
        .brand {
            position: fixed;
            top: 28px;
            left: 36px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            z-index: 20;
        }
        .brand-icon {
            width: 40px; height: 40px;
            border-radius: 11px;
            background: #c9a96e;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #0f1117;
            font-weight: 900;
        }
        .brand-name {
            font-size: 15px;
            font-weight: 700;
            color: rgba(255,255,255,.9);
            letter-spacing: -.2px;
        }

        /* Badge code */
        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(248, 113, 113, .15);
            border: 1px solid rgba(248, 113, 113, .3);
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 12px;
            font-weight: 700;
            color: #f87171;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .error-badge i { font-size: 13px; }

        /* Grand chiffre 403 */
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
            font-size: 15px;
            line-height: 1.65;
            color: rgba(255,255,255,.6);
            margin-bottom: 10px;
        }

        /* Note secondaire */
        .error-note {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: rgba(201, 169, 110, .8);
            margin-bottom: 40px;
        }
        .error-note i { font-size: 14px; color: #c9a96e; }

        /* Boutons */
        .btn-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
       
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.85);
            border: 1.5px solid rgba(255,255,255,.15);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s;
            backdrop-filter: blur(6px);
        }
        .btn-back:hover {
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.3);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Ligne décorative */
        .divider {
            width: 56px;
            height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, #c9a96e, transparent);
            margin: 0 auto 32px;
        }

        /* Grain texture overlay subtil */
        .grain {
            position: fixed;
            inset: 0;
            z-index: 2;
            opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 180px;
            pointer-events: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .brand { left: 20px; top: 20px; }
            .btn-group { flex-direction: column; align-items: center; }
            .btn-back { width: 100%; max-width: 280px; justify-content: center; }
        }
    </style>
</head>
<body>

    {{-- Fond image --}}
    <div class="bg-image"></div>
    <div class="bg-overlay"></div>
    <div class="grain"></div>

    {{-- Logo --}}
    <a href="{{ url('/') }}" class="brand">
        <div class="brand-icon">R</div>
        <span class="brand-name">RestoPro</span>
    </a>

    {{-- Contenu central --}}
    <main class="page">

        <div class="error-badge">
            <i class="bi bi-shield-lock-fill"></i>
            Erreur 403
        </div>

        <div class="error-code">403</div>

        <div class="divider"></div>

        <h1 class="error-title">Accès refusé</h1>

        <p class="error-msg">
            Vous n'avez pas les droits nécessaires pour afficher cette page.
            Connectez-vous avec un compte autorisé ou retournez à l'accueil.
        </p>

        <div class="error-note">
            <i class="bi bi-info-circle-fill"></i>
            Si vous êtes déjà connecté, votre session peut ne pas avoir les bons droits.
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