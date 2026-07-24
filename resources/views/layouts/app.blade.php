<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RestoPro') — RestoPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 78px;
            --sidebar-bg: linear-gradient(180deg, #111827 0%, #0f172a 100%);
            --sidebar-border: rgba(255,255,255,0.08);
            --topbar-height: 68px;
            --gold: #f59e0b;
            --gold-light: #fbbf24;
            --surface: #f8fafc;
            --surface-strong: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
        }

        * { box-sizing: border-box; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; color: var(--text); }

        /* ── SIDEBAR ── */
        #sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width); height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex; flex-direction: column;
            z-index: 1040; transition: transform .3s ease, width .25s ease;
            overflow-y: auto; overflow-x: hidden;
            box-shadow: 18px 0 40px rgba(15, 23, 42, 0.16);
        }
        body.sidebar-collapsed #sidebar {
            width: var(--sidebar-collapsed-width);
        }
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-track { background: transparent; }
        #sidebar::-webkit-scrollbar-thumb { background: #2a2f42; border-radius: 4px; }

        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex; align-items: center; gap: 10px;
            flex-shrink: 0;
            min-height: 76px;
            overflow: hidden;
        }
        .sidebar-logo .logo-icon {
            width: 36px; height: 36px; border-radius: 8px;
            background: var(--gold); display: flex;
            align-items: center; justify-content: center;
            font-weight: 700; font-size: 18px; color: #0f1117;
            flex-shrink: 0;
        }
        .sidebar-logo .logo-text {
            font-size: 18px; font-weight: 700;
            color: #fff; letter-spacing: -.3px;
            white-space: nowrap;
            opacity: 1;
            transition: opacity .15s ease;
        }
        body.sidebar-collapsed .sidebar-logo .logo-text {
            opacity: 0;
            pointer-events: none;
        }
        .sidebar-logo .logo-text span { color: var(--gold); }

        .sidebar-section {
            padding: 14px 12px 6px;
            font-size: 10px; font-weight: 600;
            color: #4a5068; letter-spacing: .1em;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            transition: opacity .15s ease;
        }
        body.sidebar-collapsed .sidebar-section {
            opacity: 0;
            pointer-events: none;
        }

        /* Lien de menu : icône + texte. Le texte est dans .nav-text
           pour pouvoir être masqué proprement quand le menu est réduit. */
        .nav-link-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; margin: 3px 8px;
            border-radius: 10px; color: #cbd5e1;
            text-decoration: none; font-size: 13.5px;
            font-weight: 500; transition: all .2s ease;
            white-space: nowrap; overflow: hidden;
        }
        .nav-link-item i {
            font-size: 17px; flex-shrink: 0;
            width: 20px; text-align: center;
        }
        .nav-link-item .nav-text {
            overflow: hidden;
            text-overflow: ellipsis;
            opacity: 1;
            transition: opacity .15s ease;
        }
        .nav-link-item:hover { background: rgba(255,255,255,0.08); color: #fff; transform: translateX(2px); }
        .nav-link-item.active { background: rgba(245,158,11,.16); color: var(--gold-light); box-shadow: inset 0 0 0 1px rgba(245,158,11,.15); }
        .nav-link-item .badge-nav {
            margin-left: auto; background: #e05c5c;
            color: #fff; font-size: 10px; font-weight: 600;
            border-radius: 10px; padding: 2px 6px; flex-shrink: 0;
        }
        .sidebar-nav-area { flex: 1; overflow-y: auto; padding-bottom: 8px; }

        /* ── MENU RÉDUIT (icônes seules, texte masqué) ── */
        body.sidebar-collapsed .nav-link-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            margin-left: 12px;
            margin-right: 12px;
            gap: 0;
        }
        body.sidebar-collapsed .nav-link-item:hover { transform: none; }
        body.sidebar-collapsed .nav-link-item .nav-text {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }
        /* Le badge (compteur) devient un petit point rouge sur l'icône plutôt
           que de forcer le lien à s'élargir */
        body.sidebar-collapsed .nav-link-item .badge-nav {
            position: absolute;
            margin-left: 0;
            transform: translate(10px, -12px);
            min-width: 8px; height: 8px;
            padding: 0; border-radius: 50%;
            font-size: 0; color: transparent;
            border: 2px solid #111827;
        }

        /* ── TOPBAR ── */
        #topbar {
            position: fixed; top: 0;
            left: var(--sidebar-width); right: 0;
            height: var(--topbar-height);
            background: rgba(255,255,255,.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(148,163,184,.16);
            display: flex; align-items: center;
            padding: 0 24px; gap: 12px; z-index: 1030;
            transition: left .25s ease;
        }
        body.sidebar-collapsed #topbar { left: var(--sidebar-collapsed-width); }
        .topbar-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
        .topbar-breadcrumb { font-size: 12px; color: #9299a8; margin-top: 1px; }

        .topbar-btn {
            width: 40px; height: 40px; border-radius: 12px;
            border: 1px solid rgba(148,163,184,.18); background: rgba(248,250,252,.9);
            display: flex; align-items: center; justify-content: center;
            color: #475569; cursor: pointer; text-decoration: none;
            transition: all .2s ease; font-size: 16px; flex-shrink: 0;
        }
        .topbar-btn:hover { background: #fff; color: var(--text); box-shadow: 0 8px 18px rgba(15,23,42,.08); }

        /* ── AVATAR DROPDOWN ── */
        .avatar-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 10px 4px 4px;
            border: 1px solid #e8e8ed; background: #f8f8fb;
            border-radius: 10px; cursor: pointer;
            transition: all .15s; text-decoration: none;
        }
        .avatar-btn:hover { background: #f0f0f5; border-color: #d0d0d8; }
        .avatar-circle {
            width: 28px; height: 28px; border-radius: 7px;
            background: var(--gold); color: #0f1117;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; flex-shrink: 0;
        }
        .avatar-info .avatar-name { font-size: 12px; font-weight: 600; color: #1a1a2e; line-height: 1.2; }
        .avatar-info .avatar-role { font-size: 10px; color: #9299a8; line-height: 1.2; }

        /* Dropdown menu */
        .topbar-dropdown {
            position: absolute; top: calc(100% + 8px); right: 0;
            min-width: 200px; background: #fff;
            border: 1px solid #eaeaef; border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
            z-index: 2000; overflow: hidden;
            display: none;
        }
        .topbar-dropdown.show { display: block; }
        .dropdown-header-user {
            padding: 14px 16px 10px;
            border-bottom: 1px solid #f0f0f5;
        }
        .dropdown-header-user .dh-name { font-size: 13px; font-weight: 700; color: #1a1a2e; }
        .dropdown-header-user .dh-email { font-size: 11px; color: #9299a8; }
        .dropdown-header-user .dh-role {
            display: inline-block; margin-top: 4px;
            font-size: 10px; font-weight: 600;
            padding: 2px 8px; border-radius: 10px;
            background: rgba(201,169,110,.15); color: var(--gold);
        }
        .dd-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px; font-size: 13px; color: #374151;
            text-decoration: none; transition: background .1s; cursor: pointer;
        }
        .dd-item i { font-size: 15px; color: #9299a8; }
        .dd-item:hover { background: #f8f9fa; color: #1a1a2e; }
        .dd-item:hover i { color: #374151; }
        .dd-item.dd-danger { color: #dc3545; }
        .dd-item.dd-danger i { color: #dc3545; }
        .dd-item.dd-danger:hover { background: #fef2f2; }
        .dd-divider { height: 1px; background: #f0f0f5; margin: 4px 0; }

        /* ── NOTIFICATIONS ── */
        .notif-btn-wrap { position: relative; }
        .notif-dot {
            position: absolute; top: 5px; right: 5px;
            width: 8px; height: 8px; border-radius: 50%;
            background: #e05c5c; border: 1.5px solid #fff;
        }
        .notif-count-bubble {
            position: absolute; top: -4px; right: -4px;
            min-width: 18px; height: 18px; border-radius: 9px;
            background: #e05c5c; color: #fff;
            font-size: 9px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff; padding: 0 4px;
        }

        .notif-panel {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 360px; background: #fff;
            border: 1px solid #eaeaef; border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
            z-index: 2000; overflow: hidden;
            display: none;
        }
        .notif-panel.show { display: block; }
        .notif-panel-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px 12px;
            border-bottom: 1px solid #f0f0f5;
        }
        .notif-panel-header .np-title {
            font-size: 14px; font-weight: 700; color: #1a1a2e;
        }
        .notif-panel-header .np-clear {
            font-size: 11px; color: #9299a8; cursor: pointer;
            border: none; background: none; padding: 0; transition: color .15s;
        }
        .notif-panel-header .np-clear:hover { color: var(--gold); }

        .notif-list { max-height: 400px; overflow-y: auto; }
        .notif-list::-webkit-scrollbar { width: 4px; }
        .notif-list::-webkit-scrollbar-thumb { background: #e8e8ed; border-radius: 4px; }

        .notif-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 18px; cursor: pointer;
            transition: background .1s; border-bottom: 1px solid #f8f9fa;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #fafafa; }
        .notif-item.unread { background: #fffdf8; }
        .notif-item.unread:hover { background: #fff8ee; }

        .notif-icon-wrap {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 18px;
        }
        .notif-body { flex: 1; min-width: 0; }
        .notif-body .nb-title { font-size: 13px; font-weight: 600; color: #1a1a2e; }
        .notif-body .nb-msg { font-size: 12px; color: #6b7280; margin-top: 2px; line-height: 1.4; }
        .notif-body .nb-time { font-size: 10px; color: #9ca3af; margin-top: 4px; }
        .notif-unread-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--gold); flex-shrink: 0; margin-top: 5px;
        }

        .notif-empty {
            padding: 40px 20px; text-align: center; color: #9299a8;
        }
        .notif-footer {
            padding: 10px 18px; border-top: 1px solid #f0f0f5;
            text-align: center;
        }
        .notif-footer a {
            font-size: 12px; color: var(--gold); text-decoration: none; font-weight: 500;
        }

        /* Animation cloche notification */
        @keyframes ring {
            0%   { transform: rotate(0deg); }
            10%  { transform: rotate(15deg); }
            20%  { transform: rotate(-15deg); }
            30%  { transform: rotate(12deg); }
            40%  { transform: rotate(-12deg); }
            50%  { transform: rotate(8deg); }
            60%  { transform: rotate(-8deg); }
            70%  { transform: rotate(4deg); }
            80%  { transform: rotate(-4deg); }
            90%  { transform: rotate(2deg); }
            100% { transform: rotate(0deg); }
        }
        .notif-ring {
            animation: ring 0.6s ease infinite;
            color: var(--gold) !important;
            border-color: var(--gold) !important;
            background: rgba(201,169,110,.1) !important;
        }

        /* ── MAIN CONTENT ── */
        #main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
            transition: margin-left .25s ease;
        }
        body.sidebar-collapsed #main-content { margin-left: var(--sidebar-collapsed-width); }
        .content-inner { padding: 28px; }

        /* ── CARDS ── */
        .card { border: 1px solid rgba(226,232,240,.9); border-radius: 16px; box-shadow: 0 12px 30px rgba(15,23,42,.04); background: rgba(255,255,255,.96); }
        .card-header {
            background: transparent; border-bottom: 1px solid rgba(226,232,240,.9);
            border-radius: 16px 16px 0 0 !important;
        }

        /* ── STAT CARDS ── */
        .stat-card {
            background: #fff; border: 1px solid #eaeaef;
            border-radius: 12px; padding: 20px;
        }
        .stat-card .stat-label {
            font-size: 11px; font-weight: 600; color: #9299a8;
            text-transform: uppercase; letter-spacing: .06em;
        }
        .stat-card .stat-value {
            font-size: 26px; font-weight: 700; color: #1a1a2e;
            margin: 6px 0 4px; line-height: 1;
        }
        .stat-card .stat-change { font-size: 12px; }

        /* ── BADGES STATUT ── */
        .badge-en_attente { background:#fff3cd; color:#856404; }
        .badge-en_cuisson { background:#cff4fc; color:#055160; }
        .badge-prete      { background:#d1e7dd; color:#0a3622; }
        .badge-servie     { background:#d1e7dd; color:#0a3622; }
        .badge-payee      { background:#e2e3e5; color:#41464b; }
        .badge-annulee    { background:#f8d7da; color:#842029; }

        /* ── TABLES ── */
        .table th {
            font-size: 11px; font-weight: 600; color: #9299a8;
            text-transform: uppercase; letter-spacing: .05em;
            border-bottom: 1px solid #f0f0f5;
        }
        .table td { font-size: 13px; vertical-align: middle; }

        /* ── OVERLAY MOBILE (clic en dehors = fermeture du menu) ── */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(15,23,42,.45);
            z-index: 1025; /* sous la topbar (1030) et le sidebar (1040),
                               au-dessus du contenu principal */
            opacity: 0;
            transition: opacity .2s ease;
        }
        .sidebar-overlay.show { display: block; opacity: 1; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); width: var(--sidebar-width); }
            #sidebar.show { transform: translateX(0); }
            body.sidebar-collapsed #sidebar { width: var(--sidebar-width); }
            #topbar { left: 0; }
            body.sidebar-collapsed #topbar { left: 0; }
            #main-content { margin-left: 0; }
            body.sidebar-collapsed #main-content { margin-left: 0; }

            /* Sur mobile le sidebar est toujours en pleine largeur : le texte
               du menu doit donc rester visible, même si la classe
               sidebar-collapsed est encore active (ex: laissée par le PC). */
            body.sidebar-collapsed .sidebar-logo .logo-text,
            body.sidebar-collapsed .sidebar-section,
            body.sidebar-collapsed .nav-link-item .nav-text {
                opacity: 1;
                width: auto;
                pointer-events: auto;
            }
            body.sidebar-collapsed .nav-link-item {
                justify-content: flex-start;
                padding-left: 12px;
                padding-right: 12px;
                gap: 10px;
            }
            body.sidebar-collapsed .nav-link-item .badge-nav {
                position: static;
                margin-left: auto;
                transform: none;
                min-width: 18px; height: auto;
                padding: 2px 6px; border-radius: 10px;
                font-size: 10px; color: #fff;
                border: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ══════════════════════════════════
     SIDEBAR
══════════════════════════════════ --}}
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<nav id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">R</div>
        <div class="logo-text">Resto<span>Pro</span></div>
    </div>

    @php $role = auth()->user()->role->nom; @endphp

    <div class="sidebar-nav-area">

        {{-- PRINCIPAL --}}
        {{-- @if($role === 'admin') --}}
        <div class="sidebar-section">Principal</div>
        <a href="{{ route('dashboard') }}" title="Tableau de bord"
            class="nav-link-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> <span class="nav-text">Tableau de bord</span>
        </a>
        {{-- @endif --}}

        {{-- SERVICE --}}
        @if(in_array($role, ['admin','caissier','serveur','cuisinier']))
        <div class="sidebar-section">Service</div>
        @endif

        @if(in_array($role, ['admin','serveur']))
        @php $enCours = \App\Models\Commande::whereNotIn('statut',['payee','annulee'])->count(); @endphp
        <a href="{{ route('commandes.index') }}" title="Commandes"
            class="nav-link-item {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> <span class="nav-text">Commandes</span>
            @if($enCours > 0)
                <span class="badge-nav">{{ $enCours }}</span>
            @endif
        </a>
        @endif
         @if($role === 'admin')
        <a href="{{ route('tables.index') }}" title="Tables"
            class="nav-link-item {{ request()->routeIs('tables.*') ? 'active' : '' }}">
            <i class="bi bi-grid"></i> <span class="nav-text">Tables</span>
        </a>
        @endif

        @if(in_array($role, ['admin','cuisinier']))
        @php $aCuire = \App\Models\Commande::whereIn('statut',['en_attente','en_cuisson'])->count(); @endphp
        <a href="{{ route('cuisine.index') }}" title="Cuisine"
            class="nav-link-item {{ request()->routeIs('cuisine.*') ? 'active' : '' }}">
            <i class="bi bi-fire"></i> <span class="nav-text">Cuisine</span>
            @if($aCuire > 0)
                <span class="badge-nav">{{ $aCuire }}</span>
            @endif
        </a>
        @endif

        @if(in_array($role, ['admin','caissier']))
        <a href="{{ route('caisse.index') }}" title="Caisse"
            class="nav-link-item {{ request()->routeIs('caisse.*') ? 'active' : '' }}">
            <i class="bi bi-currency-exchange"></i> <span class="nav-text">Caisse</span>
        </a>
        @endif

        {{-- CATALOGUE --}}
        @if(in_array($role, ['admin','caissier']))
        <div class="sidebar-section">Catalogue</div>
        <a href="{{ route('categories.index') }}" title="Catégories"
            class="nav-link-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tag"></i> <span class="nav-text">Catégories</span>
        </a>
        <a href="{{ route('Recette.index') }}" title="Produits"
            class="nav-link-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="bi bi-bag"></i> <span class="nav-text">Produits</span>
        </a>
        <a href="{{ route('clients.index') }}" title="Clients"
            class="nav-link-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> <span class="nav-text">Clients</span>
        </a>
        @endif

        {{-- GESTION --}}
        @if(in_array($role, ['admin','caissier']))
        <div class="sidebar-section">Gestion</div>
        <a href="{{ route('stocks.index') }}" title="Stock"
            class="nav-link-item {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> <span class="nav-text">Stock</span>
        </a>
        <a href="{{ route('depenses.index') }}" title="Dépenses"
            class="nav-link-item {{ request()->routeIs('depenses.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> <span class="nav-text">Dépenses</span>
        </a>
         @endif
        {{-- ADMINISTRATION --}}
        @if($role === 'admin')
        <a href="{{ route('rapports') }}" title="Rapports"
            class="nav-link-item {{ request()->routeIs('rapports') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i> <span class="nav-text">Rapports</span>
        </a>


        <div class="sidebar-section">Administration</div>
        <a href="{{ route('admin.users.index') }}" title="Utilisateurs"
            class="nav-link-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> <span class="nav-text">Utilisateurs</span>
        </a>
        <a href="{{ route('admin.parametres.index') }}" title="Paramètres"
            class="nav-link-item {{ request()->routeIs('admin.parametres.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> <span class="nav-text">Paramètres</span>
        </a>
        @endif

    </div>{{-- fin sidebar-nav-area --}}
</nav>

{{-- ══════════════════════════════════
     TOPBAR
══════════════════════════════════ --}}
<header id="topbar">

    {{-- Toggle unique : pliage du menu sur PC, ouverture/fermeture sur mobile --}}
    <button class="topbar-btn" id="sidebar-collapse-btn" type="button" title="Menu" aria-label="Ouvrir / fermer le menu">
        <i class="bi bi-list"></i>
    </button>

    {{-- Titre page --}}
    <div>
        <div class="topbar-title">@yield('page_title', 'Tableau de bord')</div>
        <div class="topbar-breadcrumb">@yield('breadcrumb', 'RestoPro')</div>
    </div>

    <div class="ms-auto d-flex align-items-center gap-2">

        {{-- ── CLOCHE NOTIFICATIONS ── --}}
        <div class="notif-btn-wrap">
            <div class="topbar-btn" id="notif-toggle-btn" onclick="toggleNotifPanel()"
                title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="notif-count-bubble d-none" id="notif-bubble">0</span>
            </div>

            {{-- Panel --}}
            <div class="notif-panel" id="notif-panel">
                <div class="notif-panel-header">
                    <span class="np-title">
                        Notifications
                        <span id="notif-badge-title" class="badge bg-dark ms-1"
                            style="font-size:10px;display:none">0</span>
                    </span>
                    <button class="np-clear" onclick="toutesLues()">
                        <i class="bi bi-check2-all me-1"></i>Tout marquer lu
                    </button>
                </div>
                <div class="notif-list" id="notif-list">
                    <div class="notif-empty">
                        <i class="bi bi-bell-slash d-block fs-2 mb-2 opacity-25"></i>
                        Aucune nouvelle notification
                    </div>
                </div>
                <div class="notif-footer">
                    <a href="{{ route('rapports') }}">Voir l'activité complète →</a>
                </div>
            </div>
        </div>

        {{-- ── AVATAR + DROPDOWN USER ── --}}
        <div class="position-relative">
            <div class="avatar-btn" id="user-dropdown-btn" onclick="toggleUserDropdown()">
                <div class="avatar-circle">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="avatar-info d-none d-md-block">
                    <div class="avatar-name">{{ auth()->user()->name }}</div>
                    <div class="avatar-role">{{ auth()->user()->role->label }}</div>
                </div>
                <i class="bi bi-chevron-down d-none d-md-block"
                    style="font-size:11px;color:#9299a8;margin-left:2px"></i>
            </div>

            {{-- Dropdown --}}
            <div class="topbar-dropdown" id="user-dropdown">
                {{-- Infos utilisateur --}}
                <div class="dropdown-header-user">
                    <div class="dh-name">{{ auth()->user()->name }}</div>
                    <div class="dh-email">{{ auth()->user()->email }}</div>
                    <span class="dh-role">{{ auth()->user()->role->label }}</span>
                </div>

                {{-- Menu items --}}
                @if(auth()->user()->hasRole('admin'))

                <a class="dd-item" href="{{ route('admin.parametres.index') }}">
                    <i class="bi bi-sliders"></i> Paramètres
                </a>
                <div class="dd-divider"></div>
                @endif

               <a class="dd-item" href="{{ route('profil.index') }}">
                    <i class="bi bi-person-gear"></i> Mon compte
                </a>

                <div class="dd-divider"></div>

                {{-- Déconnexion --}}
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="button" class="dd-item dd-danger w-100 text-start"
                        onclick="confirmLogout()" style="border:none;background:none">
                        <i class="bi bi-box-arrow-right"></i> Se déconnecter
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>

{{-- ══════════════════════════════════
     MAIN CONTENT
══════════════════════════════════ --}}
<main id="main-content">
    <div class="content-inner">

        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () =>
                toastr.success('{{ session('success') }}'));
        </script>
        @endif
        @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () =>
                toastr.error('{{ session('error') }}'));
        </script>
        @endif

        @yield('content')
    </div>
</main>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── CONFIG TOASTR ──────────────────────────────────
toastr.options = {
    positionClass: 'toast-top-right',
    timeOut: 3500, closeButton: true, progressBar: true,
};

// ── SIDEBAR MOBILE (ouverture / fermeture + overlay) ──
function setMobileSidebar(show) {
    const sidebarEl = document.getElementById('sidebar');
    const overlayEl = document.getElementById('sidebar-overlay');
    sidebarEl.classList.toggle('show', show);
    overlayEl.classList.toggle('show', show);
    document.getElementById('sidebar-collapse-btn').querySelector('i').className =
        show ? 'bi bi-x-lg' : 'bi bi-list';
}

// Clic sur l'overlay (le "vide") : referme le menu
document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
    setMobileSidebar(false);
});

// ── BOUTON UNIQUE : pliage sur PC, ouverture/fermeture sur mobile ──
document.getElementById('sidebar-collapse-btn')?.addEventListener('click', () => {
    if (window.innerWidth < 992) {
        const isOpen = document.getElementById('sidebar').classList.contains('show');
        setMobileSidebar(!isOpen);
        return;
    }

    document.body.classList.toggle('sidebar-collapsed');
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
    document.getElementById('sidebar-collapse-btn').querySelector('i').className = isCollapsed
        ? 'bi bi-layout-sidebar-inset-reverse'
        : 'bi bi-list';
});

// Si on repasse en desktop pendant que le menu mobile est ouvert,
// on nettoie l'état (sinon l'overlay pourrait rester actif)
window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) {
        setMobileSidebar(false);
    }
});

// ── CSRF AJAX ──────────────────────────────────────
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── USER DROPDOWN ──────────────────────────────────
let userDropOpen   = false;
let notifPanelOpen = false;

function toggleUserDropdown() {
    // FIX : fermer le panel notif si ouvert
    if (notifPanelOpen) {
        notifPanelOpen = false;
        document.getElementById('notif-panel').classList.remove('show');
    }
    userDropOpen = !userDropOpen;
    document.getElementById('user-dropdown').classList.toggle('show', userDropOpen);
}

function confirmLogout() {
    Swal.fire({
        title: 'Se déconnecter ?',
        text: 'Vous allez quitter votre session RestoPro.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#212529',
        cancelButtonText: 'Annuler',
        confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Déconnexion',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('logout-form').submit();
    });
}

// ── FERMER LES DROPDOWNS AU CLIC EXTÉRIEUR ─────────
document.addEventListener('click', e => {
    // User dropdown
    if (userDropOpen
        && !e.target.closest('#user-dropdown')
        && !e.target.closest('#user-dropdown-btn')) {
        userDropOpen = false;
        document.getElementById('user-dropdown').classList.remove('show');
    }
    // Notif panel
    if (notifPanelOpen
        && !e.target.closest('#notif-panel')
        && !e.target.closest('#notif-toggle-btn')) {
        notifPanelOpen = false;
        document.getElementById('notif-panel').classList.remove('show');
    }
});

// ── NOTIFICATIONS ──────────────────────────────────
let knownNotifIds = new Set();
let notifAudioCtx = null;
let isFetching    = false; // FIX : garde contre les fetch concurrents

// FIX : créer l'AudioContext après un geste utilisateur (politique navigateur)
function getAudioCtx() {
    if (!notifAudioCtx) {
        notifAudioCtx = window.AudioContext
            ? new AudioContext()
            : (window.webkitAudioContext ? new webkitAudioContext() : null);
    }
    return notifAudioCtx;
}

let activeSoundNodes = [];
let soundTimeout = null;

function playNotifSound(type = 'default') {
    const ac = getAudioCtx();
    if (!ac) return;
    if (ac.state === 'suspended') ac.resume();

    if (soundTimeout) clearTimeout(soundTimeout);
    activeSoundNodes.forEach(node => { try { node.stop(); } catch (e) {} });
    activeSoundNodes = [];

    const patterns = {
        'commande_prete': [
            { freq: 1047, start: 0,    dur: 0.15, vol: 0.5, type: 'sine' },
            { freq: 1319, start: 0.18, dur: 0.15, vol: 0.5, type: 'sine' },
            { freq: 1568, start: 0.36, dur: 0.25, vol: 0.6, type: 'sine' },
            { freq: 1568, start: 0.65, dur: 0.25, vol: 0.5, type: 'sine' },
            { freq: 1319, start: 0.94, dur: 0.15, vol: 0.4, type: 'sine' },
            { freq: 1047, start: 1.12, dur: 0.30, vol: 0.5, type: 'sine' },
        ],
        'cuisine': [
            { freq: 880, start: 0.00, dur: 0.18, vol: 0.42, type: 'triangle' },
            { freq: 660, start: 0.25, dur: 0.18, vol: 0.36, type: 'triangle' },
            { freq: 980, start: 0.50, dur: 0.24, vol: 0.44, type: 'triangle' },
        ],
        'serveur': [
            { freq: 740, start: 0.00, dur: 0.16, vol: 0.38, type: 'square' },
            { freq: 830, start: 0.18, dur: 0.16, vol: 0.38, type: 'square' },
            { freq: 920, start: 0.36, dur: 0.20, vol: 0.42, type: 'square' },
        ],
        'caisse': [
            { freq: 587, start: 0.00, dur: 0.14, vol: 0.34, type: 'sine' },
            { freq: 698, start: 0.16, dur: 0.14, vol: 0.34, type: 'sine' },
            { freq: 784, start: 0.32, dur: 0.20, vol: 0.4, type: 'sine' },
        ],
        'default': [
            { freq: 523, start: 0.00, dur: 0.18, vol: 0.4, type: 'square' },
            { freq: 659, start: 0.22, dur: 0.18, vol: 0.4, type: 'square' },
            { freq: 784, start: 0.44, dur: 0.28, vol: 0.5, type: 'square' },
        ],
    };

    const notes = patterns[type] ?? patterns['default'];
    const duration = Math.max(1.2, notes.reduce((sum, n) => sum + n.dur + 0.05, 0) * 3.2);

    for (let repeat = 0; repeat < 3; repeat++) {
        notes.forEach(n => {
            const osc = ac.createOscillator();
            const gain = ac.createGain();
            osc.connect(gain);
            gain.connect(ac.destination);
            osc.type = n.type ?? 'sine';
            osc.frequency.value = n.freq;
            const t = ac.currentTime + repeat * duration / 3 + n.start;
            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(n.vol, t + 0.02);
            gain.gain.setValueAtTime(n.vol, t + n.dur - 0.04);
            gain.gain.linearRampToValueAtTime(0, t + n.dur);
            osc.start(t);
            osc.stop(t + n.dur + 0.05);
            activeSoundNodes.push(osc);
        });
    }

    soundTimeout = setTimeout(() => {
        activeSoundNodes.forEach(node => { try { node.stop(); } catch (e) {} });
        activeSoundNodes = [];
    }, 10000);
}

function toggleNotifPanel() {
    // FIX : fermer le dropdown user si ouvert
    if (userDropOpen) {
        userDropOpen = false;
        document.getElementById('user-dropdown').classList.remove('show');
    }
    notifPanelOpen = !notifPanelOpen;
    document.getElementById('notif-panel').classList.toggle('show', notifPanelOpen);
    // FIX : passer false pour déclencher le son si nouvelles notifs à l'ouverture
    if (notifPanelOpen) fetchNotifs(false);
}

async function fetchNotifs(silent = false) {
    // FIX : éviter les appels concurrents
    if (isFetching) return;
    isFetching = true;

    try {
        const r = await fetch('/notifications', {
            headers: {
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        if (!r.ok) return;
        const data = await r.json();
        renderNotifs(data, silent);
    } catch(e) {
        // silence réseau
    } finally {
        // FIX : toujours libérer le verrou
        isFetching = false;
    }
}

const notifColors = {
    info:    { bg: '#e0f2fe', color: '#0369a1' },
    success: { bg: '#dcfce7', color: '#15803d' },
    warning: { bg: '#fef9c3', color: '#a16207' },
    danger:  { bg: '#fee2e2', color: '#b91c1c' },
    cuisine: { bg: '#fef9c3', color: '#a16207' },
    caisse:  { bg: '#dcfce7', color: '#15803d' },
    stock:   { bg: '#fee2e2', color: '#b91c1c' },
};

// FIX : échappement XSS pour les données serveur
function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

function renderNotifs(data, silent = false) {
    const list   = document.getElementById('notif-list');
    const bubble = document.getElementById('notif-bubble');
    const badgeT = document.getElementById('notif-badge-title');
    const count  = data.count ?? 0;

    // ✅ Détecter nouvelles notifs et jouer le son adapté
    let hasNew = false;
    let soundType = 'default';

    (data.items || []).forEach(n => {
        if (!knownNotifIds.has(n.id)) {
            if (!silent) {
                hasNew = true;
                if (n.titre && n.titre.toLowerCase().includes('prête')) {
                    soundType = 'commande_prete';
                } else if (n.type === 'cuisine' || n.titre?.toLowerCase().includes('commande')) {
                    soundType = 'cuisine';
                } else if (n.type === 'caisse') {
                    soundType = 'caisse';
                } else if (n.type === 'serveur') {
                    soundType = 'serveur';
                }
            }
            knownNotifIds.add(n.id);
        }
    });

    if (hasNew) {
        playNotifSound(soundType);

        // Notification navigateur
        if (typeof Notification !== 'undefined'
            && Notification.permission === 'granted') {
            const last = data.items[0];
            new Notification('RestoPro — ' + last.titre, {
                body: last.message,
                icon: '/favicon.ico',
            });
        }

        // Clignoter la cloche
        const btn = document.getElementById('notif-toggle-btn');
        btn.classList.add('notif-ring');
        setTimeout(() => btn.classList.remove('notif-ring'), 2000);
    }

    // Badge
    if (count > 0) {
        bubble.classList.remove('d-none');
        bubble.textContent   = count > 99 ? '99+' : count;
        badgeT.style.display = '';
        badgeT.textContent   = count;
    } else {
        bubble.classList.add('d-none');
        badgeT.style.display = 'none';
    }

    // Liste
    if (!data.items || data.items.length === 0) {
        list.innerHTML = `
            <div class="notif-empty" id="notif-empty">
                <i class="bi bi-bell-slash d-block fs-2 mb-2 opacity-25"></i>
                Aucune nouvelle notification
            </div>`;
        return;
    }

    list.innerHTML = data.items.map(n => {
        const cfg  = notifColors[n.couleur] ?? notifColors.info;
        const time = timeAgo(new Date(n.created_at));
        return `
        <div class="notif-item ${!n.lue ? 'unread' : ''}"
            id="notif-item-${n.id}"
            onclick="clickNotif(${n.id}, '${n.lien ?? ''}')">
            <div class="notif-icon-wrap"
                style="background:${cfg.bg};color:${cfg.color}">
                <i class="bi bi-${n.icone}"></i>
            </div>
            <div class="notif-body">
                <div class="nb-title">${n.titre}</div>
                <div class="nb-msg">${n.message}</div>
                <div class="nb-time">
                    <i class="bi bi-clock me-1"></i>${time}
                </div>
            </div>
            ${!n.lue ? '<div class="notif-unread-dot"></div>' : ''}
        </div>`;
    }).join('');
}

// Clic notif : animation de sortie + suppression de la liste + redirection
function clickNotif(id, lien) {
    const el = document.getElementById(`notif-item-${id}`);

    // Animation slide out
    if (el) {
        el.style.transition = 'all 0.25s ease';
        el.style.opacity    = '0';
        el.style.transform  = 'translateX(30px)';
        el.style.maxHeight  = el.offsetHeight + 'px';

        setTimeout(() => {
            el.style.maxHeight = '0';
            el.style.padding   = '0';
            el.style.overflow  = 'hidden';
        }, 200);

        setTimeout(() => el.remove(), 450);
    }

    // Marquer comme lue côté serveur
    fetch(`/notifications/${id}/lue`, {
        method: 'PATCH',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(() => {
        fetchNotifs(true);

        // FIX : lien est déjà null (pas la string 'null') grâce à JSON.stringify
        if (lien) {
            setTimeout(() => {
                window.location.href = lien;
            }, 300);
        }
    });
}

function toutesLues() {
    fetch('/notifications/toutes-lues', {
        method: 'POST',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            // Animer la disparition de tous les items
            document.querySelectorAll('.notif-item').forEach((el, i) => {
                setTimeout(() => {
                    el.style.transition = 'all 0.2s ease';
                    el.style.opacity    = '0';
                    el.style.transform  = 'translateX(20px)';
                }, i * 60);
            });

            setTimeout(() => {
                // FIX : vider le Set pour que le son se rejoue si de nouvelles notifs arrivent
                knownNotifIds.clear();
                fetchNotifs(true);
                toastr.success('Toutes les notifications marquées comme lues.');
            }, 400);
        }
    });
}

// FIX : correction du bug syntaxique (<script injecté) + ajout Math.max pour les dates futures
function timeAgo(date) {
    const diff = Math.max(0, Math.floor((Date.now() - date) / 1000));
    if (diff < 60)    return 'à l\'instant';
    if (diff < 3600)  return Math.floor(diff / 60) + ' min';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h';
    if (diff < 604800) return Math.floor(diff / 86400) + 'j';
    return new Intl.DateTimeFormat('fr-FR', { timeZone: 'Europe/Paris' }).format(date);
}

// FIX : warm-up AudioContext + permission notification au premier clic utilisateur
document.addEventListener('click', () => {
    getAudioCtx();
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}, { once: true });

// Chargement initial silencieux + polling toutes les 5s pour une réactivité plus fluide
fetchNotifs(true);
setInterval(() => fetchNotifs(false), 5000);
</script>

@stack('scripts')
</body>
</html>