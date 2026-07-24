<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — RestoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0a0c14;
            --panel:     #11141f;
            --panel-2:   #151827;
            --border:    #1f2436;
            --gold:      #c9a96e;
            --gold-2:    #e8c97a;
            --gold-dim:  #c9a96e33;
            --ink:       #eef0f5;
            --dim:       #6b7280;
            --error:     #f87171;
            --success:   #4ade80;
            --serif:     'Fraunces', serif;
            --sans:      'Inter', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            font-family: var(--sans);
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* ── Fond ambiant animé ── */
        .ambient-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .ambient-glow span {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .35;
        }
        .ambient-glow span:nth-child(1) {
            width: 420px; height: 420px;
            background: radial-gradient(circle, #c9a96e, transparent 70%);
            top: -120px; left: -100px;
            animation: drift1 22s ease-in-out infinite;
        }
        .ambient-glow span:nth-child(2) {
            width: 380px; height: 380px;
            background: radial-gradient(circle, #6b4ecf, transparent 70%);
            bottom: -140px; right: -80px;
            animation: drift2 26s ease-in-out infinite;
        }
        @keyframes drift1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(60px, 40px) scale(1.15); }
        }
        @keyframes drift2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-50px, -30px) scale(1.1); }
        }

        /* ── WRAPPER ── */
        .login-wrap {
            position: relative;
            z-index: 1;
            display: flex;
            width: 100%;
            max-width: 920px;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,.65), 0 0 0 1px rgba(255,255,255,.03);
            opacity: 0;
            transform: translateY(18px);
            animation: wrapIn .7s cubic-bezier(.16,1,.3,1) .05s forwards;
        }
        @keyframes wrapIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── GAUCHE — image + ticket animé ── */
        .left-panel {
            flex: 1;
            position: relative;
            overflow: hidden;
            min-height: 560px;
        }
        .left-panel img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
            filter: brightness(.55) saturate(1.05);
            transform: scale(1.06);
            animation: kenburns 20s ease-in-out infinite alternate;
        }
        @keyframes kenburns {
            from { transform: scale(1.06) translate(0,0); }
            to   { transform: scale(1.12) translate(-1.5%, -1%); }
        }
        .left-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(170deg, rgba(6,9,20,.55) 0%, rgba(15,25,45,.35) 45%, rgba(6,9,20,.85) 100%);
            display: flex; flex-direction: column;
            justify-content: flex-start;
            padding: 40px;
        }
        .left-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(201,169,110,.14);
            border: 1px solid rgba(201,169,110,.32);
            border-radius: 30px; padding: 6px 14px;
            margin-bottom: 22px; width: fit-content;
            opacity: 0; animation: fadeUp .6s ease .3s forwards;
            backdrop-filter: blur(6px);
        }
        .left-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #c9a96e; flex-shrink: 0; box-shadow: 0 0 0 3px #c9a96e2a; animation: dotPulse 2s infinite; }
        @keyframes dotPulse { 0%,100%{ opacity:1 } 50%{ opacity:.4 } }
        .left-badge span { font-size: 11px; font-weight: 600; color: #e8c97a; letter-spacing: .08em; text-transform: uppercase; }

        .left-title {
            font-family: var(--serif);
            font-size: 34px; font-weight: 600; color: #fff; line-height: 1.22;
            margin-bottom: 12px; letter-spacing: -.3px;
            opacity: 0; animation: fadeUp .6s ease .42s forwards;
        }
        .left-title em { font-style: italic; color: var(--gold-2); font-weight: 500; }
        .left-sub {
            font-size: 13.5px; color: rgba(255,255,255,.55); line-height: 1.7; max-width: 340px;
            opacity: 0; animation: fadeUp .6s ease .54s forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Ticket de commande animé (élément signature) ── */
        .order-ticket {
            margin-top: auto;
            align-self: flex-start;
            width: 240px;
            background: #fffdf8;
            border-radius: 3px;
            padding: 16px 16px 14px;
            box-shadow: 0 20px 45px rgba(0,0,0,.5);
            position: relative;
            opacity: 0;
            transform: translateY(20px) rotate(-2deg);
            animation: ticketIn .7s cubic-bezier(.16,1,.3,1) .7s forwards;
        }
        @keyframes ticketIn {
            to { opacity: 1; transform: translateY(0) rotate(-2deg); }
        }
        .order-ticket::before {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -7px;
            height: 14px;
            background:
                linear-gradient(135deg, transparent 50%, #fffdf8 50%) 0 0 / 10px 14px repeat-x,
                linear-gradient(-135deg, transparent 50%, #fffdf8 50%) 0 0 / 10px 14px repeat-x;
            background-position: 0 0, 5px 0;
        }
        .ticket-head {
            display: flex; align-items: center; justify-content: space-between;
            font-family: 'Courier New', monospace;
            font-size: 10px; color: #9a9184; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            border-bottom: 1px dashed #d8d2c4; padding-bottom: 8px; margin-bottom: 8px;
        }
        .ticket-live { display: flex; align-items: center; gap: 5px; color: #b8621f; }
        .ticket-live .dot { width: 5px; height: 5px; border-radius: 50%; background: #b8621f; animation: dotPulse 1.4s infinite; }
        .ticket-lines {
            font-family: 'Courier New', monospace;
            font-size: 11.5px; color: #2b2820; line-height: 1.85;
            min-height: 58px;
        }
        .ticket-lines .tl-table { color: #8a8272; font-size: 10px; display: block; margin-bottom: 2px; }
        .ticket-cursor {
            display: inline-block; width: 6px; height: 12px;
            background: #2b2820; vertical-align: middle; margin-left: 2px;
            animation: blink 1s step-end infinite;
        }
        @keyframes blink { 50% { opacity: 0; } }
        .ticket-foot {
            border-top: 1px dashed #d8d2c4; margin-top: 10px; padding-top: 8px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .ticket-foot span { font-family: 'Courier New', monospace; font-size: 9.5px; color: #b0a996; }
        .ticket-bar { flex: 1; height: 2px; background: #eae5d8; margin-left: 10px; border-radius: 2px; overflow: hidden; }
        .ticket-bar-fill { height: 100%; width: 0%; background: #c9a96e; animation: printFill 4s linear infinite; }
        @keyframes printFill {
            0%   { width: 0%; }
            85%  { width: 100%; }
            100% { width: 100%; }
        }

        /* ── DROITE — formulaire ── */
        .right-panel {
            width: 380px; flex-shrink: 0;
            background: var(--panel);
            padding: 46px 38px;
            display: flex; flex-direction: column; justify-content: center;
            position: relative;
        }

        .logo {
            display: flex; align-items: center; gap: 10px; margin-bottom: 34px;
            opacity: 0; animation: fadeUp .55s ease .15s forwards;
        }
        .logo-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, var(--gold), #a8875a);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--serif);
            font-size: 19px; font-weight: 700; color: #0b0e18; flex-shrink: 0;
            box-shadow: 0 6px 18px rgba(201,169,110,.3);
        }
        .logo-name { font-family: var(--serif); font-size: 19px; font-weight: 600; color: #fff; letter-spacing: -.2px; }

        .form-head { margin-bottom: 28px; opacity: 0; animation: fadeUp .55s ease .22s forwards; }
        .form-title { font-family: var(--serif); font-size: 25px; font-weight: 600; color: #fff; margin-bottom: 5px; letter-spacing: -.3px; }
        .form-sub   { font-size: 13px; color: var(--dim); }

        .alert-box {
            display: none; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: 9px;
            font-size: 12px; margin-bottom: 16px; border: 1px solid;
            animation: shake .45s ease;
        }
        .alert-box.error   { background: rgba(220,53,69,.1);  border-color: rgba(220,53,69,.22);  color: #f87171; }
        .alert-box.success { background: rgba(34,197,94,.1);  border-color: rgba(34,197,94,.22);  color: #4ade80; }
        .alert-box.show    { display: flex; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%      { transform: translateX(-5px); }
            40%      { transform: translateX(5px); }
            60%      { transform: translateX(-3px); }
            80%      { transform: translateX(3px); }
        }

        .field {
            margin-bottom: 16px;
            opacity: 0; animation: fadeUp .5s ease forwards;
        }
        .field:nth-of-type(1) { animation-delay: .3s; }
        .field:nth-of-type(2) { animation-delay: .38s; }

        .field label {
            display: block; font-size: 11px; font-weight: 600;
            color: var(--dim); margin-bottom: 6px;
            letter-spacing: .06em; text-transform: uppercase;
            transition: color .2s;
        }
        .field-inner { position: relative; }
        .field-inner .fi-ic {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            font-size: 15px; color: #4b5563; pointer-events: none;
            transition: color .2s, transform .2s;
        }
        .field input {
            width: 100%; padding: 12px 40px 12px 38px;
            border-radius: 10px; border: 1px solid var(--border);
            background: #0c0f19; color: #fff;
            font-size: 14px; font-family: inherit; outline: none;
            transition: border-color .25s, box-shadow .25s, background .25s;
        }
        .field input:hover { border-color: #2a3049; }
        .field input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 4px var(--gold-dim);
            background: #0e1220;
        }
        .field input:focus ~ .fi-ic,
        .field-inner:focus-within .fi-ic { color: var(--gold); transform: translateY(-50%) scale(1.05); }
        .field input::placeholder { color: #3a3f52; }
        .field.has-error input { border-color: var(--error); }

        .toggle-pwd {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #4b5563; font-size: 15px; padding: 2px;
            display: flex; align-items: center;
            transition: color .15s, transform .15s;
        }
        .toggle-pwd:hover { color: var(--gold-2); transform: translateY(-50%) scale(1.1); }

        .row-opts {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            flex-wrap: wrap; gap: 8px;
            opacity: 0; animation: fadeUp .5s ease .44s forwards;
        }
        .remember { display: flex; align-items: center; gap: 7px; cursor: pointer; }
        .remember input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--gold); cursor: pointer; }
        .remember span { font-size: 12px; color: var(--dim); }
        .forgot { font-size: 12px; color: var(--gold-2); text-decoration: none; font-weight: 500; position: relative; }
        .forgot::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -2px; height: 1px;
            background: var(--gold-2); transform: scaleX(0); transform-origin: right;
            transition: transform .2s;
        }
        .forgot:hover::after { transform: scaleX(1); transform-origin: left; }

        .btn-login {
            width: 100%; padding: 13px; border-radius: 11px; border: none;
            background: linear-gradient(135deg, var(--gold), #dabb82);
            color: #12151f;
            font-size: 14px; font-weight: 700; font-family: inherit; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: transform .15s, box-shadow .2s; letter-spacing: .02em;
            position: relative; overflow: hidden;
            opacity: 0; animation: fadeUp .5s ease .5s forwards;
            box-shadow: 0 8px 22px rgba(201,169,110,.25);
        }
        .btn-login::after {
            content: '';
            position: absolute; top: 0; left: -60%; width: 40%; height: 100%;
            background: linear-gradient(115deg, transparent, rgba(255,255,255,.55), transparent);
            transform: skewX(-20deg);
            transition: left .55s ease;
        }
        .btn-login:hover:not(:disabled)::after { left: 130%; }
        .btn-login:hover:not(:disabled) { box-shadow: 0 10px 26px rgba(201,169,110,.4); transform: translateY(-1px); }
        .btn-login:active:not(:disabled) { transform: translateY(0) scale(.98); }
        .btn-login:disabled { opacity: .65; cursor: not-allowed; }

        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(18,21,31,.22);
            border-top-color: #12151f;
            border-radius: 50%;
            animation: spin .65s linear infinite;
            display: none; flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-inner { display: flex; align-items: center; gap: 8px; }
        .btn-inner i { transition: transform .2s; }
        .btn-login:hover:not(:disabled) .btn-inner i { transform: translateX(2px); }

        .divider {
            display: flex; align-items: center; gap: 10px;
            color: #333850; font-size: 12px; margin: 20px 0;
            opacity: 0; animation: fadeUp .5s ease .56s forwards;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .socials {
            display: flex; gap: 10px;
            opacity: 0; animation: fadeUp .5s ease .62s forwards;
        }
        .social-btn {
            flex: 1; padding: 10px 6px; border: 1px solid var(--border);
            border-radius: 10px; background: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 12px; font-weight: 500; color: var(--dim);
            font-family: inherit; white-space: nowrap;
            transition: background .18s, border-color .18s, color .18s, transform .15s;
        }
        .social-btn:hover { background: var(--panel-2); border-color: #2a3049; color: #d1d5db; transform: translateY(-1px); }

        /* ══ RESPONSIVE ══ */
        @media (min-width: 681px) and (max-width: 860px) {
            .left-title { font-size: 26px; }
            .right-panel { width: 330px; padding: 34px 28px; }
            .order-ticket { width: 200px; }
        }

        @media (max-width: 680px) {
            body { padding: 0; align-items: stretch; }
            .login-wrap {
                flex-direction: column;
                border-radius: 0;
                box-shadow: none;
                min-height: 100vh;
            }
            .left-panel  { min-height: 260px; flex: none; }
            .left-title  { font-size: 21px; }
            .left-overlay { padding: 24px 22px; }
            .order-ticket { display: none; }
            .right-panel { width: 100%; flex: 1; padding: 32px 24px 42px; }
            .form-title  { font-size: 20px; }
            .socials     { flex-direction: column; }
        }

        @media (max-width: 380px) {
            .left-panel  { min-height: 200px; }
            .left-title  { font-size: 19px; }
            .right-panel { padding: 26px 18px 32px; }
            .logo-icon   { width: 36px; height: 36px; font-size: 16px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
            .login-wrap, .left-badge, .left-title, .left-sub, .order-ticket,
            .logo, .form-head, .field, .row-opts, .btn-login, .divider, .socials {
                opacity: 1; transform: none;
            }
        }

        /* Focus visible clavier */
        a:focus-visible, button:focus-visible, input:focus-visible {
            outline: 2px solid var(--gold-2);
            outline-offset: 2px;
        }
    </style>
</head>
<body>

<div class="ambient-glow"><span></span><span></span></div>

<div class="login-wrap">

    {{-- ── GAUCHE — image + ticket animé ── --}}
    <div class="left-panel">
        <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=900&q=80"
             alt="Ambiance restaurant">
        <div class="left-overlay">
            <div class="left-badge">
                <div class="dot"></div>
                <span>Gestion &amp; Performance</span>
            </div>
            <div class="left-title">
                Pilotez votre restaurant<br><em>avec précision</em>
            </div>
            <div class="left-sub">
                Commandes, équipes, stocks et rapports
                — tout en un seul espace de travail.
            </div>

            {{-- Élément signature : ticket de commande animé --}}
            <div class="order-ticket">
                <div class="ticket-head">
                    <span>Cuisine</span>
                    <span class="ticket-live"><span class="dot"></span>Live</span>
                </div>
                <div class="ticket-lines" id="ticket-lines"></div>
                <div class="ticket-foot">
                    <span>#RP-2847</span>
                    <div class="ticket-bar"><div class="ticket-bar-fill"></div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DROITE — formulaire ── --}}
    <div class="right-panel">

        <div class="logo">
            <div class="logo-icon">R</div>
            <div class="logo-name">RestoPro</div>
        </div>

        <div class="form-head">
            <div class="form-title">Bon retour 👋</div>
            <div class="form-sub">Connectez-vous à votre espace de gestion</div>
        </div>

        {{-- Erreurs Laravel --}}
        @if($errors->any())
        <div class="alert-box error show">
            <i class="bi bi-exclamation-circle" style="flex-shrink:0"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
            @csrf

            {{-- Email --}}
            <div class="field">
                <label for="email">Adresse email</label>
                <div class="field-inner">
                    <i class="bi bi-envelope fi-ic"></i>
                    <input type="email" id="email" name="email"
                           placeholder="admin@restopro.com"
                           value="{{ old('email') }}"
                           autocomplete="email" required autofocus>
                </div>
            </div>

            {{-- Mot de passe --}}
            <div class="field" style="margin-bottom:10px">
                <label for="pwd">Mot de passe</label>
                <div class="field-inner">
                    <i class="bi bi-lock fi-ic"></i>
                    <input type="password" id="pwd" name="password"
                           placeholder="••••••••"
                           autocomplete="current-password" required>
                    <button type="button" class="toggle-pwd"
                            onclick="togglePwd()"
                            aria-label="Afficher le mot de passe">
                        <i class="bi bi-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            {{-- Mémoriser / oublié --}}
            <div class="row-opts">
                <label class="remember">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Se souvenir de moi</span>
                </label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot">
                    Mot de passe oublié ?
                </a>
                @endif
            </div>

            {{-- Bouton + loader --}}
            <button type="submit" class="btn-login" id="btn-login">
                <span class="btn-inner" id="btn-inner">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Se connecter
                </span>
                <div class="spinner" id="spinner"></div>
            </button>

        </form>

    </div>
</div>

<script>
    /* ── Afficher / masquer mot de passe ── */
    function togglePwd() {
        const p = document.getElementById('pwd');
        const i = document.getElementById('eye-icon');
        p.type      = p.type === 'password' ? 'text' : 'password';
        i.className = p.type === 'password'  ? 'bi bi-eye' : 'bi bi-eye-slash';
    }

    /* ── Loader au submit ── */
    document.getElementById('login-form').addEventListener('submit', function (e) {
        const emailField = document.getElementById('email');
        const pwdField   = document.getElementById('pwd');
        const email = emailField.value.trim();
        const pwd   = pwdField.value;

        if (!email || !pwd) {
            e.preventDefault();
            [emailField, pwdField].forEach(f => {
                if (!f.value.trim()) {
                    f.closest('.field').classList.add('has-error');
                    f.addEventListener('input', () => f.closest('.field').classList.remove('has-error'), { once: true });
                }
            });
            return;
        }

        const btn  = document.getElementById('btn-login');
        const inn  = document.getElementById('btn-inner');
        const spin = document.getElementById('spinner');

        btn.disabled        = true;
        inn.style.display   = 'none';
        spin.style.display  = 'block';

        /* Sécurité : réactiver après 8s si la page ne change pas */
        setTimeout(function () {
            btn.disabled       = false;
            inn.style.display  = 'flex';
            spin.style.display = 'none';
        }, 8000);
    });

    /* ── Enter dans email → focus mot de passe ── */
    document.getElementById('email').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('pwd').focus(); }
    });

    /* ── Ticket de commande animé (typewriter en boucle) ── */
    (function () {
        const el = document.getElementById('ticket-lines');
        if (!el) return;

        const orders = [
            { table: 'Table 04', items: ['2x Filet mignon', '1x Salade César'] },
            { table: 'Table 11', items: ['1x Plateau fruits de mer', '3x Vin blanc'] },
            { table: 'À emporter', items: ['1x Burger signature', '1x Frites truffe'] },
            { table: 'Table 07', items: ['2x Risotto champignons', '2x Tiramisu'] },
        ];

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let orderIndex = 0;

        function typeLine(text, container, speed, cb) {
            let i = 0;
            const cursor = document.createElement('span');
            cursor.className = 'ticket-cursor';
            container.appendChild(cursor);
            const interval = setInterval(() => {
                cursor.insertAdjacentText('beforebegin', text[i]);
                i++;
                if (i >= text.length) {
                    clearInterval(interval);
                    cursor.remove();
                    cb && cb();
                }
            }, speed);
        }

        function renderOrder() {
            const order = orders[orderIndex];
            el.innerHTML = '';

            const tableLabel = document.createElement('span');
            tableLabel.className = 'tl-table';
            tableLabel.textContent = order.table;
            el.appendChild(tableLabel);

            if (reduceMotion) {
                order.items.forEach(item => {
                    const line = document.createElement('div');
                    line.textContent = '· ' + item;
                    el.appendChild(line);
                });
                orderIndex = (orderIndex + 1) % orders.length;
                setTimeout(renderOrder, 4000);
                return;
            }

            let itemIdx = 0;
            function nextItem() {
                if (itemIdx >= order.items.length) {
                    orderIndex = (orderIndex + 1) % orders.length;
                    setTimeout(renderOrder, 1400);
                    return;
                }
                const line = document.createElement('div');
                el.appendChild(line);
                typeLine('· ' + order.items[itemIdx], line, 28, () => {
                    itemIdx++;
                    setTimeout(nextItem, 220);
                });
            }
            nextItem();
        }

        renderOrder();
    })();
</script>

</body>
</html>