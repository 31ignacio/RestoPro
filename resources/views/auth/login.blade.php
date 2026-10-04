<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion · RestoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <style>
        /* ── reset & base ── */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #111713;
            --panel: #19211b;
            --panel-2: #20291f;
            --border: #344034;
            --gold: #d3a66b;
            --gold-2: #efd09a;
            --gold-dim: #d3a66b33;
            --ink: #eef0f5;
            --dim: #6b7280;
            --error: #f87171;
            --success: #4ade80;
            --serif: 'Fraunces', serif;
            --sans: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(ellipse at 12% 10%, rgba(211, 166, 107, .17), transparent 38%),
                radial-gradient(ellipse at 88% 88%, rgba(105, 130, 92, .2), transparent 40%),
                linear-gradient(145deg, #101612 0%, #19221b 52%, #111713 100%);
            font-family: var(--sans);
            padding: 20px;
            position: relative;
        }

        /* ── fond ambiant ── */
        .ambient {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .ambient span {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: .30;
        }

        .ambient span:nth-child(1) {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #d3a66b, transparent 70%);
            top: -200px;
            left: -150px;
            animation: floatA 28s ease-in-out infinite alternate;
        }

        .ambient span:nth-child(2) {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #718565, transparent 70%);
            bottom: -180px;
            right: -120px;
            animation: floatB 32s ease-in-out infinite alternate;
        }

        @keyframes floatA {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(80px, 50px) scale(1.2);
            }
        }

        @keyframes floatB {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(-70px, -50px) scale(1.15);
            }
        }

        /* ── carte principale ── */
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: rgba(25, 33, 27, 0.84);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 40px;
            padding: 48px 40px 44px;
            box-shadow: 0 40px 90px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.04);
            opacity: 0;
            transform: translateY(24px) scale(0.98);
            animation: cardIn .7s cubic-bezier(.16, 1, .3, 1) .05s forwards;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        @keyframes cardIn {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── en-tête ── */
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 38px;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--gold), #a8875a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--serif);
            font-size: 24px;
            font-weight: 700;
            color: #0b0e18;
            flex-shrink: 0;
            box-shadow: 0 10px 28px rgba(201, 169, 110, 0.3);
        }

        .brand-name {
            font-family: var(--serif);
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            letter-spacing: -.3px;
        }

        .brand-name span {
            color: var(--gold-2);
            font-weight: 500;
        }

        .header {
            margin-bottom: 32px;
        }

        .header h1 {
            font-family: var(--serif);
            font-size: 30px;
            font-weight: 600;
            color: #fff;
            letter-spacing: -.4px;
            margin-bottom: 4px;
        }

        .header h1 i {
            color: var(--gold-2);
            font-style: italic;
            font-weight: 500;
        }

        .header p {
            color: var(--dim);
            font-size: 14px;
        }

        /* ── alerte ── */
        .alert-box {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 16px;
            font-size: 13px;
            margin-bottom: 22px;
            border: 1px solid;
            animation: shake .4s ease;
        }

        .alert-box.error {
            background: rgba(220, 53, 69, 0.08);
            border-color: rgba(220, 53, 69, 0.2);
            color: #f87171;
        }

        .alert-box.success {
            background: rgba(34, 197, 94, 0.08);
            border-color: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }

        .alert-box.show {
            display: flex;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-6px);
            }

            40% {
                transform: translateX(6px);
            }

            60% {
                transform: translateX(-4px);
            }

            80% {
                transform: translateX(4px);
            }
        }

        /* ── champs ── */
        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--dim);
            margin-bottom: 6px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .field-inner {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-inner .fi-ic {
            position: absolute;
            left: 16px;
            font-size: 17px;
            color: #4b5563;
            pointer-events: none;
            transition: color .25s, transform .25s;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 15px 48px 15px 46px;
            border-radius: 16px;
            border: 1px solid var(--border);
            background: rgba(12, 15, 25, 0.6);
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color .25s, box-shadow .25s, background .25s;
        }

        .field input:hover,
        .field select:hover {
            border-color: #2a3049;
            background: rgba(12, 15, 25, 0.8);
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 4px var(--gold-dim);
            background: rgba(14, 18, 32, 0.9);
        }

        .field-inner:focus-within .fi-ic {
            color: var(--gold);
            transform: scale(1.05);
        }

        .field input::placeholder {
            color: #3a3f52;
        }

        .field select {
            appearance: none;
            cursor: pointer;
        }

        .field select:invalid { color: #8c928c; }
        .field select option { color: #eef0f5; background: #19211b; }

        .field.has-error input,
        .field.has-error select {
            border-color: var(--error);
        }

        .toggle-pwd {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            cursor: pointer;
            color: #4b5563;
            font-size: 18px;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color .2s, transform .15s;
        }

        .toggle-pwd:hover {
            color: var(--gold-2);
            transform: scale(1.08);
        }

        /* ── options ── */
        .row-opts {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 6px 0 26px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 17px;
            height: 17px;
            accent-color: var(--gold);
            cursor: pointer;
        }

        .remember span {
            font-size: 13px;
            color: var(--dim);
        }

        .forgot {
            font-size: 13px;
            color: var(--gold-2);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: color .2s;
        }

        .forgot::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -3px;
            height: 1.5px;
            background: var(--gold-2);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform .25s;
        }

        .forgot:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* ── bouton ── */
        .btn-login {
            width: 100%;
            padding: 15px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--gold), #dabb82);
            color: #12151f;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform .15s, box-shadow .25s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(201, 169, 110, 0.2);
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -60%;
            width: 40%;
            height: 100%;
            background: linear-gradient(115deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transform: skewX(-25deg);
            transition: left .6s ease;
        }

        .btn-login:hover:not(:disabled)::after {
            left: 130%;
        }

        .btn-login:hover:not(:disabled) {
            box-shadow: 0 12px 36px rgba(201, 169, 110, 0.4);
            transform: translateY(-2px);
        }

        .btn-login:active:not(:disabled) {
            transform: translateY(0) scale(.97);
        }

        .btn-login:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(18, 21, 31, .2);
            border-top-color: #12151f;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
            flex-shrink: 0;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-inner i {
            font-size: 18px;
            transition: transform .2s;
        }

        .btn-login:hover:not(:disabled) .btn-inner i {
            transform: translateX(3px);
        }

        /* ── pied ── */
        .footer {
            margin-top: 28px;
            text-align: center;
            color: var(--dim);
            font-size: 13px;
        }

        .footer a {
            color: var(--gold-2);
            text-decoration: none;
            font-weight: 500;
            position: relative;
        }

        .footer a::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -2px;
            height: 1.5px;
            background: var(--gold-2);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform .25s;
        }

        .footer a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* ── responsivité ── */
        @media (max-width: 520px) {
            body {
                padding: 12px;
            }

            .login-card {
                padding: 32px 20px 30px;
                border-radius: 28px;
            }

            .brand-icon {
                width: 44px;
                height: 44px;
                font-size: 20px;
            }

            .brand-name {
                font-size: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .field input,
            .field select {
                padding: 13px 44px 13px 42px;
            }
        }

        @media (max-width: 380px) {
            .login-card {
                padding: 24px 16px 26px;
                border-radius: 20px;
            }

            .brand {
                margin-bottom: 28px;
            }

            .header h1 {
                font-size: 21px;
            }

            .row-opts {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }

            .login-card {
                opacity: 1;
                transform: none;
            }
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 2px solid var(--gold-2);
            outline-offset: 2px;
        }
    </style>
</head>

<body>

    <div class="ambient"><span></span><span></span></div>

    <div class="login-card">

        <!-- marque -->
        <div class="brand">
            <div class="brand-icon">R</div>
            <div class="brand-name">Resto<span>Pro</span></div>
        </div>

        <!-- en-tête -->
        <div class="header">
            <h1>Bienvenue <i>chez vous</i></h1>
            <p>Connectez-vous à votre espace de gestion</p>
        </div>

        <!-- erreur -->
        @if ($errors->any())
            <div class="alert-box error show">
                <i class="bi bi-exclamation-circle" style="flex-shrink:0; font-size:18px;"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <!-- formulaire -->
        <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
            @csrf

            <div class="field">
                <label for="user_id">Nom</label>
                <div class="field-inner">
                    <i class="bi bi-person-badge fi-ic"></i>
                    <select id="user_id" name="user_id" required autofocus>
                        <option value="">Choisissez votre nom</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" data-role="{{ $user->role?->label }}" @selected((string) old('user_id') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="role-display">Votre rôle</label>
                <div class="field-inner">
                    <i class="bi bi-shield-lock fi-ic"></i>
                    <input type="text" id="role-display" placeholder="Le rôle s’affichera ici"
                        value="" readonly aria-live="polite" tabindex="-1">
                </div>
            </div>

            <div class="field">
                <label for="pwd">Mot de passe</label>
                <div class="field-inner">
                    <i class="bi bi-lock fi-ic"></i>
                    <input type="password" id="pwd" name="password" placeholder="••••••••"
                        autocomplete="current-password" required>
                    <button type="button" class="toggle-pwd" onclick="togglePwd()"
                        aria-label="Afficher le mot de passe">
                        <i class="bi bi-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <div class="row-opts">
                <label class="remember">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Se souvenir de moi</span>
                </label>
               
            </div>

            <button type="submit" class="btn-login" id="btn-login">
                <span class="btn-inner" id="btn-inner">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Se connecter
                </span>
                <div class="spinner" id="spinner"></div>
            </button>

        </form>



    </div>

    <script>
        // ── toggle ──
        function togglePwd() {
            const p = document.getElementById('pwd');
            const i = document.getElementById('eye-icon');
            p.type = p.type === 'password' ? 'text' : 'password';
            i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        // ── loader ──
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const userField = document.getElementById('user_id');
            const pwdField = document.getElementById('pwd');
            const pwd = pwdField.value;

            if (!userField.value || !pwd) {
                e.preventDefault();
                [userField, pwdField].forEach(f => {
                    if (!f.value.trim()) {
                        f.closest('.field').classList.add('has-error');
                        f.addEventListener(f.tagName === 'SELECT' ? 'change' : 'input', () => f.closest('.field').classList.remove(
                        'has-error'), {
                            once: true
                        });
                    }
                });
                return;
            }

            const btn = document.getElementById('btn-login');
            const inn = document.getElementById('btn-inner');
            const spin = document.getElementById('spinner');

            btn.disabled = true;
            inn.style.display = 'none';
            spin.style.display = 'block';

            setTimeout(function() {
                btn.disabled = false;
                inn.style.display = 'flex';
                spin.style.display = 'none';
            }, 8000);
        });

        const userSelect = document.getElementById('user_id');
        const roleDisplay = document.getElementById('role-display');
        function displaySelectedRole() {
            roleDisplay.value = userSelect.selectedOptions[0]?.dataset.role || '';
        }
        userSelect.addEventListener('change', displaySelectedRole);
        displaySelectedRole();

        // ── Entrée sur le nom → focus mot de passe ──
        document.getElementById('user_id').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('pwd').focus();
            }
        });
    </script>

</body>

</html>
