@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')
@section('breadcrumb', 'Vue d\'ensemble du service')

@section('content')

    {{-- ══ EN-TÊTE ══ --}}
    <div class="dash-header mb-5">
        @php
            $heure = now()->format('H');
            $salut = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');
        @endphp
        <div class="dash-header-left">
            <div class="dash-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div>
                <h4 class="dash-title">{{ $salut }}, {{ auth()->user()->name }}</h4>
                <p class="dash-sub">{{ now()->locale('fr')->translatedFormat('l d F Y') }} — Une belle journée s'annonce !</p>
            </div>
        </div>
        <div class="dash-live">
            <span class="live-dot"></span>
            <span class="live-label" id="liveClock"></span>
        </div>
    </div>

    @php $role = auth()->user()->role->nom; @endphp

    {{-- ══ STATS CARDS ══ --}}
    <div class="row g-3 mb-5">

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon" style="--ic:#c9a96e;--icbg:#c9a96e14">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="kpi-badge up">
                        <i class="bi bi-arrow-up-right"></i> Aujourd'hui
                    </span>
                </div>
                @if ($role === 'admin')
                    <div class="kpi-val">
                        {{ number_format($stats['ca_jour'], 0, ',', ' ') }}
                        <span class="kpi-unit">F</span>
                    </div>
                    <div class="kpi-lbl">Chiffre d'affaires</div>
                @else
                    <div class="kpi-val kpi-masked">•••</div>
                    <div class="kpi-lbl">
                        <span class="kpi-marquee"><span>Nom du Restaurant</span></span>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon" style="--ic:#6b9fd4;--icbg:#6b9fd414">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <span class="kpi-badge neutral">aujourd'hui</span>
                </div>
                <div class="kpi-val">{{ $stats['commandes_jour'] }}</div>
                <div class="kpi-lbl">Commandes</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon" style="--ic:#7ec88a;--icbg:#7ec88a14">
                        <i class="bi bi-grid-1x2"></i>
                    </div>
                    <span class="kpi-badge neutral">
                        {{ $stats['tables_total'] > 0 ? round(($stats['tables_occupees'] / $stats['tables_total']) * 100) : 0 }}%
                        occupé
                    </span>
                </div>
                <div class="kpi-val">
                    {{ $stats['tables_occupees'] }}
                    <span class="kpi-unit">/ {{ $stats['tables_total'] }}</span>
                </div>
                <div class="kpi-lbl">Tables occupées</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon" style="--ic:#b08ecf;--icbg:#b08ecf14">
                        <i class="bi bi-coin"></i>
                    </div>
                    <span class="kpi-badge neutral">par commande</span>
                </div>
                @if ($role === 'admin')
                    <div class="kpi-val">
                        {{ number_format($stats['ticket_moyen'], 0, ',', ' ') }}
                        <span class="kpi-unit">F</span>
                    </div>
                    <div class="kpi-lbl">Ticket moyen</div>
                @else
                    <div class="kpi-val kpi-masked">•••</div>
                    <div class="kpi-lbl">
                        <span class="kpi-marquee"><span>Nom du Restaurant</span></span>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ══ CONTENU PRINCIPAL ══ --}}
    <div class="row g-4">

        {{-- COMMANDES EN COURS --}}
        <div class="col-xl-8">
            <div class="dash-panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="bi bi-receipt-cutoff"></i>
                        Commandes en cours
                    </div>
                    <a href="{{ route('commandes.index') }}" class="panel-link">
                        Voir tout <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>N° commande</th>
                                <th>Table</th>
                                <th>Statut</th>
                                <th>Montant</th>
                                <th>Heure</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['commandes_actives'] as $cmd)
                                <tr>
                                    <td>
                                        <span class="cmd-num">#{{ $cmd->numero }}</span>
                                    </td>
                                    <td>
                                        <span class="table-badge">
                                            <i class="bi bi-grid me-1 opacity-50"></i>
                                            {{ $cmd->table?->numero ?? 'Emporter' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-pill status-{{ $cmd->statut }}">
                                            <span class="status-dot"></span>
                                            {{ ucfirst(str_replace('_', ' ', $cmd->statut)) }}
                                        </span>
                                    </td>
                                    <td class="amount-cell">
                                        {{ number_format($cmd->total, 0, ',', ' ') }} F
                                    </td>
                                    <td class="time-cell">
                                        <i class="bi bi-clock me-1 opacity-40"></i>
                                        {{ $cmd->created_at->format('H:i') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('commandes.show', $cmd) }}" class="row-action">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-row">
                                            <i class="bi bi-inbox"></i>
                                            <span>Aucune commande en cours</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SIDEBAR DROITE --}}
        <div class="col-xl-4 d-flex flex-column gap-4">

            {{-- TOP PRODUITS --}}
            <div class="dash-panel flex-fill">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="bi bi-star"></i>
                        Top produits du jour
                    </div>
                </div>
                <div class="top-list">
                    @php $max = $stats['top_produits']->first()?->total_vendu ?? 1; @endphp
                    @forelse($stats['top_produits'] as $i => $p)
                        <div class="top-item">
                            <div
                                class="top-rank {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                                {{ $i + 1 }}
                            </div>
                            <div class="top-info">
                                <div class="top-nom">{{ $p->nom }}</div>
                                <div class="top-bar-wrap">
                                    <div class="top-bar" style="width:{{ round(($p->total_vendu / $max) * 100) }}%"></div>
                                </div>
                            </div>
                            <div class="top-count">{{ $p->total_vendu }}<span>×</span></div>
                        </div>
                    @empty
                        <div class="empty-row">
                            <i class="bi bi-bar-chart"></i>
                            <span>Pas encore de ventes</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- OCCUPATION TABLES --}}
            <div class="dash-panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="bi bi-layout-wtf"></i>
                        Occupation des tables
                    </div>
                </div>
                <div class="occ-wrap">
                    @php
                        $pct =
                            $stats['tables_total'] > 0
                                ? round(($stats['tables_occupees'] / $stats['tables_total']) * 100)
                                : 0;
                    @endphp
                    <div class="occ-ring-wrap">
                        <svg class="occ-ring" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="32" class="ring-bg" />
                            <circle cx="40" cy="40" r="32" class="ring-fill"
                                stroke-dasharray="{{ round((201 * $pct) / 100) }} 201" />
                        </svg>
                        <div class="occ-pct">{{ $pct }}<span>%</span></div>
                    </div>
                    <div class="occ-legend">
                        <div class="occ-leg-item">
                            <span class="occ-dot occupied"></span>
                            <span>Occupées</span>
                            <strong>{{ $stats['tables_occupees'] }}</strong>
                        </div>
                        <div class="occ-leg-item">
                            <span class="occ-dot free"></span>
                            <span>Libres</span>
                            <strong>{{ $stats['tables_total'] - $stats['tables_occupees'] }}</strong>
                        </div>
                        <div class="occ-leg-item">
                            <span class="occ-dot total"></span>
                            <span>Total</span>
                            <strong>{{ $stats['tables_total'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ══════════════════════════════════════
        TOKENS
        ══════════════════════════════════════ */
        :root {
            --gold:    #c9a96e;
            --gold-s:  #c9a96e1a;
            --ink:     #14141f;
            --ink2:    #374151;
            --muted:   #97a0ae;
            --border:  #eef0f4;
            --bg:      #f6f7fa;
            --white:   #ffffff;
            --radius:  18px;
            --radius-sm: 12px;
            --shadow:  0 1px 2px rgba(20,20,31,.04), 0 8px 24px rgba(20,20,31,.05);
            --shadow-hov: 0 4px 10px rgba(20,20,31,.06), 0 16px 40px rgba(20,20,31,.09);
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ══════════════════════════════════════
        HEADER
        ══════════════════════════════════════ */
        .dash-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .dash-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .dash-avatar {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--gold), #e3c98f);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(201,169,110,.35);
        }
        .dash-title {
            font-size: 21px;
            font-weight: 800;
            color: var(--ink);
            margin: 0 0 3px;
            letter-spacing: -.4px;
        }
        .dash-sub {
            font-size: 12.5px;
            color: var(--muted);
            margin: 0;
            font-weight: 500;
        }
        .dash-live {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 8px 16px;
            font-size: 12.5px;
            color: var(--ink2);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            box-shadow: var(--shadow);
        }
        .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #39c26a;
            box-shadow: 0 0 0 3px #39c26a22;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50%     { opacity: .5; transform: scale(.8); }
        }

        /* ══════════════════════════════════════
        KPI CARDS
        ══════════════════════════════════════ */
        .kpi-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
            transition: box-shadow .25s ease, transform .2s ease, border-color .2s ease;
            height: 100%;
        }
        .kpi-card:hover {
            box-shadow: var(--shadow-hov);
            transform: translateY(-3px);
            border-color: #e2e5ec;
        }
        .kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--icbg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--ic);
        }
        .kpi-badge {
            font-size: 10.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .kpi-badge.up { background: #e9fbf0; color: #22a35a; }
        .kpi-badge.neutral { background: var(--bg); color: var(--muted); }
        .kpi-val {
            font-size: 27px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 6px;
            letter-spacing: -.6px;
            font-variant-numeric: tabular-nums;
        }
        .kpi-val.kpi-masked { letter-spacing: 3px; color: #c7cbd4; }
        .kpi-unit {
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
        }
        .kpi-lbl {
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
        }
        .kpi-marquee { display: block; overflow: hidden; white-space: nowrap; }
        .kpi-marquee span {
            display: inline-block;
            animation: kpiScroll 7s linear infinite;
        }
        @keyframes kpiScroll {
            0%   { transform: translateX(0%); }
            100% { transform: translateX(-100%); }
        }

        /* ══════════════════════════════════════
        PANELS
        ══════════════════════════════════════ */
        .dash-panel {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
        }
        .panel-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .panel-title i {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--gold-s);
            color: var(--gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
        }
        .panel-link {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color .15s, gap .15s;
        }
        .panel-link:hover { color: var(--ink); gap: 7px; }

        /* ══════════════════════════════════════
        TABLE
        ══════════════════════════════════════ */
        .table-wrap { overflow-x: auto; }
        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table thead tr { border-bottom: 1px solid var(--border); }
        .dash-table thead th {
            padding: 12px 24px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            white-space: nowrap;
            background: #fbfbfd;
        }
        .dash-table tbody tr {
            border-bottom: 1px solid #f2f3f6;
            transition: background .12s;
        }
        .dash-table tbody tr:last-child { border-bottom: none; }
        .dash-table tbody tr:hover { background: #fafbfd; }
        .dash-table tbody td {
            padding: 14px 24px;
            font-size: 13px;
            color: var(--ink2);
            vertical-align: middle;
        }
        .cmd-num { font-weight: 700; color: var(--ink); font-size: 13px; }
        .table-badge { font-size: 12px; color: var(--muted); font-weight: 500; }
        .amount-cell { font-weight: 800; color: var(--gold) !important; font-variant-numeric: tabular-nums; }
        .time-cell { font-size: 12px !important; color: var(--muted) !important; font-variant-numeric: tabular-nums; }
        .row-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            border: 1px solid var(--border);
            color: var(--muted);
            text-decoration: none;
            font-size: 13px;
            transition: all .15s;
        }
        .row-action:hover {
            background: var(--ink);
            border-color: var(--ink);
            color: #fff;
            transform: translateX(2px);
        }

        /* ── Status pills ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .02em;
        }
        .status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        .status-en_attente { background: #fff8ec; color: #d4860a; }
        .status-en_cours    { background: #eaf4ff; color: #2b7cd3; }
        .status-pret        { background: #e9fbf0; color: #22a35a; }
        .status-servi       { background: #f1f1f5; color: #6b7280; }
        .status-annule      { background: #fef1f1; color: #dc3545; }
        .status-valide      { background: #e9fbf0; color: #22a35a; }

        /* ── Empty row ── */
        .empty-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 44px 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
        }
        .empty-row i {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--bg);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            opacity: .5;
        }

        /* ══════════════════════════════════════
        TOP PRODUITS
        ══════════════════════════════════════ */
        .top-list { padding: 8px 24px 20px; }
        .top-item {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 0;
            border-bottom: 1px solid #f2f3f6;
        }
        .top-item:last-child { border-bottom: none; }
        .top-rank {
            width: 26px; height: 26px;
            border-radius: 8px;
            background: var(--bg);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800;
            color: var(--muted);
            flex-shrink: 0;
        }
        .top-rank.gold   { background: #c9a96e22; color: #c9a96e; }
        .top-rank.silver { background: #b0b7c322; color: #8a93a5; }
        .top-rank.bronze { background: #cd7f3222; color: #cd7f32; }
        .top-info { flex: 1; min-width: 0; }
        .top-nom {
            font-size: 13px; font-weight: 600; color: var(--ink);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            margin-bottom: 6px;
        }
        .top-bar-wrap { height: 5px; background: var(--bg); border-radius: 4px; overflow: hidden; }
        .top-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--gold), #e8c98a);
            border-radius: 4px;
            transition: width .6s ease;
        }
        .top-count { font-size: 13px; font-weight: 800; color: var(--ink); min-width: 34px; text-align: right; font-variant-numeric: tabular-nums; }
        .top-count span { font-size: 10px; color: var(--muted); margin-left: 1px; }

        /* ══════════════════════════════════════
        OCCUPATION RING
        ══════════════════════════════════════ */
        .occ-wrap {
            padding: 20px 24px 24px;
            display: flex;
            align-items: center;
            gap: 26px;
        }
        .occ-ring-wrap { position: relative; width: 82px; height: 82px; flex-shrink: 0; }
        .occ-ring { width: 82px; height: 82px; transform: rotate(-90deg); }
        .ring-bg { fill: none; stroke: var(--bg); stroke-width: 8; }
        .ring-fill {
            fill: none;
            stroke: var(--gold);
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dasharray .8s ease;
        }
        .occ-pct {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 800; color: var(--ink);
        }
        .occ-pct span { font-size: 11px; font-weight: 500; color: var(--muted); margin-left: 1px; }
        .occ-legend { flex: 1; display: flex; flex-direction: column; gap: 9px; }
        .occ-leg-item { display: flex; align-items: center; gap: 9px; font-size: 12.5px; color: var(--ink2); font-weight: 500; }
        .occ-leg-item strong { margin-left: auto; font-weight: 700; color: var(--ink); }
        .occ-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .occ-dot.occupied { background: var(--gold); }
        .occ-dot.free { background: #eceef2; border: 1px solid #d0d4dc; }
        .occ-dot.total { background: #b0b7c3; }

        /* ══════════════════════════════════════
        RESPONSIVE
        ══════════════════════════════════════ */
        @media (max-width: 767px) {
            .dash-header { align-items: flex-start; }
            .dash-title { font-size: 18px; }
            .kpi-val { font-size: 22px; }
            .occ-wrap { flex-direction: column; align-items: flex-start; gap: 16px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        setTimeout(() => location.reload(), 60000);

        function updateLiveClock() {
            var el = document.getElementById('liveClock');
            if (!el) return;
            var now = new Date();
            el.textContent = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();
    </script>
@endpush