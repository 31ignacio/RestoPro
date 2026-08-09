@extends('layouts.app')
@section('title', 'Commandes')
@section('page_title', 'Commandes')
@section('breadcrumb', 'Gestion des commandes')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="cmd-page-header mb-4">
    <div>
        <h5 class="cmd-page-title">Commandes du jour</h5>
        <div class="cmd-page-sub">
            <span class="live-dot-mini" title="Actualisation automatique"></span>
            <span id="subtitle-count">{{ $commandes->total() }} commande(s)</span>
        </div>
    </div>
    <a href="{{ route('commandes.create') }}" class="btn-new-cmd">
        <i class="bi bi-plus-lg"></i>Nouvelle commande
    </a>
</div>

{{-- ══ KPI RAPIDES ══ --}}
<div class="row g-3 mb-4">
    @php
        $kpis = [
            ['key'=>'en_attente', 'label'=>'En attente', 'count'=>$commandes->getCollection()->where('statut','en_attente')->count(), 'accent'=>'#c9a96e','icon'=>'hourglass-split'],
            ['key'=>'en_cuisson', 'label'=>'En cuisson', 'count'=>$commandes->getCollection()->where('statut','en_cuisson')->count(), 'accent'=>'#6b9fd4','icon'=>'fire'],
            ['key'=>'prete',      'label'=>'Prêtes',     'count'=>$commandes->getCollection()->where('statut','prete')->count(),      'accent'=>'#5f9c76','icon'=>'check-circle'],
            ['key'=>'payee',      'label'=>'Payées',     'count'=>$commandes->getCollection()->where('statut','payee')->count(),      'accent'=>'#212529','icon'=>'wallet'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon" style="color:{{ $kpi['accent'] }};background:{{ $kpi['accent'] }}16">
                    <i class="bi bi-{{ $kpi['icon'] }}"></i>
                </div>
            </div>
            <div class="kpi-value" id="kpi-value-{{ $kpi['key'] }}">{{ $kpi['count'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
            <div class="kpi-bar" style="background:{{ $kpi['accent'] }}"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ FILTRES ══ --}}
<div class="card filter-card mb-4">
    <div class="card-body">

        {{-- Ligne 1 : Statut --}}
        <div class="filter-block mb-0">
            <span class="filter-label">Statut</span>
            <div class="d-flex gap-2 flex-wrap" id="statut-btns">
                @foreach([
                    'tous'       => 'Toutes',
                    'en_attente' => 'En attente',
                    'en_cuisson' => 'En cuisson',
                    'prete'      => 'Prête ✓',
                    'servie'     => 'Servie',
                    'payee'      => 'Payée',
                    'annulee'    => 'Annulée',
                ] as $s => $label)
                <button class="filter-statut-btn {{ $s === 'tous' ? 'active' : '' }}"
                    data-statut="{{ $s }}"
                    onclick="setStatut('{{ $s }}', this)">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        <hr class="filter-sep">

        {{-- Ligne 2 : Période / dates / recherche / bouton ── grille alignée ── --}}
        <div class="filter-row-2">

            <div class="filter-block filter-period">
                <span class="filter-label">Période rapide</span>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach([
                        'today'   => "Aujourd'hui",
                        'week'    => 'Semaine',
                        'month'   => 'Mois',
                        'all'     => 'Tout',
                    ] as $p => $label)
                    <button class="period-btn {{ $p === 'today' ? 'active' : '' }}"
                        data-period="{{ $p }}"
                        onclick="setPeriod('{{ $p }}', this)">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="filter-block filter-date">
                <span class="filter-label">Du</span>
                <input type="date" id="date-debut" class="form-control form-control-sm date-input"
                    value="{{ today()->format('Y-m-d') }}">
            </div>

            <div class="filter-block filter-date">
                <span class="filter-label">Au</span>
                <input type="date" id="date-fin" class="form-control form-control-sm date-input"
                    value="{{ today()->format('Y-m-d') }}">
            </div>

            <div class="filter-block filter-search">
                <span class="filter-label">Recherche</span>
                <div class="input-group input-group-sm search-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted" style="font-size:12px"></i>
                    </span>
                    <input type="text" class="form-control border-start-0"
                        id="search-cmd"
                        placeholder="N°, table, serveur..."
                        oninput="searchCommandes(this.value)">
                </div>
            </div>

            <div class="filter-block filter-submit">
                <span class="filter-label filter-label-ghost">Filtrer</span>
                <button class="btn-apply-filter" onclick="appliquerFiltres()">
                    <i class="bi bi-funnel"></i>Filtrer
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══ TABLEAU ══ --}}
<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="cmds-table">
                <thead>
                    <tr>
                        <th class="ps-4">N° Commande</th>
                        <th>Table / Type</th>
                        <th>Client</th>
                        <th>Serveur</th>
                        <th class="text-center">Articles</th>
                        <th class="text-end">Total</th>
                        <th>Statut</th>
                        <th>Heure</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="cmds-tbody">
                    @forelse($commandes as $cmd)
                    <tr id="cmd-row-{{ $cmd->id }}"
                        data-statut="{{ $cmd->statut }}"
                        data-search="{{ strtolower($cmd->numero.' '.($cmd->table?->numero ?? '').' '.$cmd->serveur->name) }}"
                        data-date="{{ $cmd->created_at->format('Y-m-d') }}">

                        <td class="ps-4">
                            <div class="cell-primary">{{ $cmd->numero }}</div>
                            <small class="text-muted">
                                {{ $cmd->type === 'sur_place' ? 'Sur place' : ($cmd->type === 'emporter' ? 'Emporter' : 'Livraison') }}
                            </small>
                        </td>

                        <td>
                            @if($cmd->table)
                            <span class="table-badge">
                                <i class="bi bi-grid-3x3-gap me-1"></i>T{{ $cmd->table->numero }}
                            </span>
                            @else
                            <span class="table-badge emporter">
                                <i class="bi bi-bag me-1"></i>Emporter
                            </span>
                            @endif
                        </td>

                        <td class="cell-muted">
                            {{ $cmd->client?->nom ?? '—' }}
                        </td>

                        <td>
                            <div class="serveur-cell">
                                <div class="serveur-avatar">
                                    {{ strtoupper(substr($cmd->serveur->name, 0, 2)) }}
                                </div>
                                <span class="serveur-nom">{{ Str::limit($cmd->serveur->name, 12) }}</span>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="qty-badge">{{ $cmd->items->count() }}</span>
                        </td>

                        <td class="text-end cell-total">
                            {{ number_format($cmd->total, 0, ',', ' ') }} F
                        </td>

                        <td>
                            @php
                            $statusConfig = [
                                'en_attente' => ['En attente', 'badge-en_attente'],
                                'en_cuisson' => ['En cuisson', 'badge-en_cuisson'],
                                'prete'      => ['Prête ✓',    'badge-prete'],
                                'servie'     => ['Servie',     'badge-servie'],
                                'payee'      => ['Payée',      'badge-payee'],
                                'annulee'    => ['Annulée',    'badge-annulee'],
                            ];
                            [$slabel, $sclass] = $statusConfig[$cmd->statut] ?? [$cmd->statut, 'badge-secondary'];
                            @endphp
                            <span class="statut-badge {{ $sclass }}"><span class="statut-dot"></span>{{ $slabel }}</span>
                        </td>

                        <td>
                            <div class="cell-time">{{ $cmd->created_at->format('H:i') }}</div>
                            <small class="text-muted cell-time-rel">
                                {{ $cmd->created_at->diffForHumans() }}
                            </small>
                        </td>

                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="action-btn js-cmd-detail" data-cmd-id="{{ $cmd->id }}"
                                    title="Voir le détail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if(!in_array($cmd->statut, ['payee','annulee']))
                                <a href="{{ route('commandes.show', $cmd) }}"
                                    class="action-btn" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif

                                @if(!in_array($cmd->statut, ['payee','annulee','en_cuisson','prete']))
                                <button type="button" class="action-btn danger js-cmd-annuler"
                                    data-cmd-id="{{ $cmd->id }}" data-cmd-num="{{ $cmd->numero }}"
                                    title="Annulerfff">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-receipt"></i>
                                <span>Aucune commande aujourd'hui.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Message aucun résultat filtre --}}
        <div id="no-filter-result" class="d-none">
            <div class="empty-state">
                <i class="bi bi-funnel"></i>
                <span>Aucune commande ne correspond aux filtres.</span>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($commandes->hasPages())
    <div class="card-footer table-footer">
        <small class="text-muted">
            {{ $commandes->firstItem() }}–{{ $commandes->lastItem() }} sur {{ $commandes->total() }}
        </small>
        {{ $commandes->links() }}
    </div>
    @endif
</div>

{{-- ══ MODAL DÉTAIL ══ --}}
<div class="modal fade" id="cmdDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Détail commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="cmdDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary"></div>
                    <div class="text-muted mt-2" style="font-size:13px">Chargement...</div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        :root {
            --gold:   #c9a96e;
            --ink:    #14141f;
            --ink2:   #374151;
            --muted:  #9299a8;
            --border: #eef0f4;
            --bg:     #f6f7fa;
        }

        .fw-500 { font-weight: 500; }

        /* ── EN-TÊTE PAGE ── */
        .cmd-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .cmd-page-title {
            font-size: 19px;
            font-weight: 800;
            color: var(--ink);
            margin: 0 0 5px;
            letter-spacing: -.3px;
        }
        .cmd-page-sub {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--muted);
            font-weight: 500;
        }
        .live-dot-mini {
            width: 7px; height: 7px; border-radius: 50%;
            background: #198754; flex-shrink: 0;
            animation: liveBlink 1.6s infinite;
        }
        @keyframes liveBlink { 0%,100%{opacity:1} 50%{opacity:.25} }

        .btn-new-cmd {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 22px;
            border-radius: 12px;
            border: none;
            background: var(--ink);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all .2s;
            box-shadow: 0 4px 14px rgba(20,20,31,.18);
        }
        .btn-new-cmd:hover {
            background: #000;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(20,20,31,.26);
        }

        /* ── KPI ── */
        .kpi-card {
            position: relative;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px 22px 22px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(20,20,31,.04), 0 6px 18px rgba(20,20,31,.05);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .kpi-card:hover { box-shadow: 0 4px 10px rgba(20,20,31,.06), 0 14px 32px rgba(20,20,31,.08); transform: translateY(-2px); }
        .kpi-top { margin-bottom: 14px; }
        .kpi-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .kpi-value {
            font-size: 27px; font-weight: 800; color: var(--ink); line-height: 1;
            margin-bottom: 6px; letter-spacing: -.5px; font-variant-numeric: tabular-nums;
        }
        .kpi-label {
            font-size: 11.5px; font-weight: 600;
            color: var(--muted); text-transform: uppercase;
            letter-spacing: .05em;
        }
        .kpi-bar {
            position: absolute; left: 0; bottom: 0;
            width: 100%; height: 3px; opacity: .55;
        }

        /* ── CARTE FILTRES ── */
        .filter-card { border: 1px solid var(--border); border-radius: 16px; }
        .filter-card .card-body { padding: 22px 24px; }
        .filter-block { display: flex; flex-direction: column; gap: 8px; }
        .filter-label {
            font-size: 10.5px; font-weight: 700;
            color: var(--muted); text-transform: uppercase;
            letter-spacing: .06em;
        }
        .filter-label-ghost { color: transparent; user-select: none; }
        .filter-sep { border: none; border-top: 1px solid #f0f0f4; margin: 20px 0; }

        /* Grille de filtres — alignement propre en une seule ligne sur desktop */
        .filter-row-2 {
            display: grid;
            grid-template-columns: auto 142px 142px minmax(200px, 1fr) auto;
            gap: 18px;
            align-items: end;
        }
        .filter-period  { min-width: 0; }
        .filter-date     { min-width: 0; }
        .filter-search   { min-width: 0; }
        .filter-submit   { min-width: 0; }

        .date-input { width: 100%; }
        .search-group { width: 100%; }
        .search-group .form-control,
        .search-group .input-group-text {
            border-color: #e2e4ea;
        }
        .search-group .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,169,110,.12);
        }

        /* Boutons statut / période — même gabarit, cohérents */
        .filter-statut-btn, .period-btn {
            padding: 6px 15px; border-radius: 20px;
            border: 1px solid #e8e9ee; background: #fbfbfc;
            font-size: 12.5px; font-weight: 600; color: #6b7280;
            cursor: pointer; transition: all .15s; white-space: nowrap;
        }
        .filter-statut-btn:hover { border-color: var(--gold); color: #a9834a; background: #fff; }
        .filter-statut-btn.active { background: var(--ink); border-color: var(--ink); color: #fff; }

        .period-btn:hover { border-color: #9299a8; color: var(--ink); background: #fff; }
        .period-btn.active { background: #f4f0e8; border-color: var(--gold); color: #8a6d3b; }

        .btn-apply-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 22px;
            border-radius: 10px;
            border: none;
            background: var(--ink);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all .18s;
            height: 32px;
        }
        .btn-apply-filter:hover { background: #000; transform: translateY(-1px); }

        /* ── TABLE ── */
        .table-card { border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(20,20,31,.04), 0 6px 18px rgba(20,20,31,.05); }
        .table-responsive { overflow-x: auto; }
        #cmds-table thead th {
            padding: 12px 16px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            background: #fbfbfd;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        #cmds-table tbody td {
            padding: 14px 16px;
            font-size: 13px;
            color: var(--ink2);
            vertical-align: middle;
            border-bottom: 1px solid #f2f3f6;
        }
        #cmds-table tbody tr:last-child td { border-bottom: none; }
        #cmds-table tbody tr { transition: background-color .15s; }
        #cmds-table tbody tr:hover { background-color: #fafafa; }

        .cell-primary { font-weight: 700; font-size: 13px; color: var(--ink); }
        .cell-muted   { font-size: 12.5px; color: #6b7280; }
        .cell-total   { font-weight: 800; color: var(--gold); font-size: 14px; font-variant-numeric: tabular-nums; }
        .cell-time    { font-size: 13px; font-weight: 600; color: var(--ink2); }
        .cell-time-rel{ font-size: 11px; }

        .table-badge {
            display: inline-flex; align-items: center;
            padding: 4px 11px; border-radius: 7px;
            font-size: 12px; font-weight: 700;
            background: #f5f5f8; color: #374151;
        }
        .table-badge.emporter {
            background: #fbfbfc; color: var(--muted); border: 1px solid var(--border);
        }

        .serveur-cell { display: flex; align-items: center; gap: 9px; }
        .serveur-avatar {
            width: 27px; height: 27px; border-radius: 7px;
            background: #eeeef3; color: #374151;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 800; flex-shrink: 0;
        }
        .serveur-nom { font-size: 12.5px; font-weight: 500; }

        .qty-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 24px; height: 22px; padding: 0 7px;
            border-radius: 7px; background: var(--bg); border: 1px solid var(--border);
            font-size: 11px; font-weight: 700; color: var(--ink2);
        }

        .statut-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px 5px 10px;
            border-radius: 20px; font-size: 11px; font-weight: 700;
            transition: background-color .3s, color .3s;
        }
        .statut-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; opacity: .8; flex-shrink: 0; }
        .badge-en_attente { background:#fff3cd; color:#856404; }
        .badge-en_cuisson { background:#e0f0fb; color:#0c5e88; }
        .badge-prete      { background:#d9f2e3; color:#0a7a45; }
        .badge-servie     { background:#dbe8fe; color:#1d4ed8; }
        .badge-payee      { background:var(--ink); color:#fff; }
        .badge-annulee    { background:#fbdfe1; color:#b91c2c; }

        @keyframes rowFlash {
            0%   { background-color: rgba(201,169,110,.16); }
            100% { background-color: transparent; }
        }
        #cmds-table tbody tr.row-flash { animation: rowFlash 1.8s ease-out; }

        /* ── ACTIONS ── */
        .action-btn {
            width: 31px; height: 31px;
            border-radius: 9px; border: 1px solid var(--border);
            background: #fff; display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 13px; color: #6b7280; cursor: pointer;
            transition: all .15s; text-decoration: none;
        }
        .action-btn:hover { background: #f5f5f7; color: var(--ink); border-color: #d0d0d8; transform: translateY(-1px); }
        .action-btn.success { color: #198754; border-color: #cdeadb; }
        .action-btn.success:hover { background: #d9f2e3; }
        .action-btn.danger  { color: #dc3545; border-color: #f6d3d6; }
        .action-btn.danger:hover  { background: #fbdfe1; }

        /* ── ÉTAT VIDE ── */
        .empty-state {
            display: flex; flex-direction: column; align-items: center; gap: 12px;
            padding: 56px 24px; color: var(--muted); text-align: center;
        }
        .empty-state i {
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--bg); display: flex; align-items: center; justify-content: center;
            font-size: 24px; opacity: .55;
        }
        .empty-state span { font-size: 13.5px; font-weight: 500; }

        /* ── FOOTER TABLE / PAGINATION ── */
        .table-footer {
            background: #fff; border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 24px; flex-wrap: wrap; gap: 10px;
        }
        .pagination { margin: 0; }
        .page-link { font-size: 13px; color: var(--ink2); border-color: var(--border); }
        .page-item.active .page-link { background: var(--ink); border-color: var(--ink); }

        /* ── MODAL DÉTAIL ── */
        .detail-info-box {
            background: #f8f9fa; border-radius: 12px;
            padding: 15px 17px;
        }
        .detail-info-box .dib-label {
            font-size: 10px; font-weight: 700;
            color: var(--muted); text-transform: uppercase;
            letter-spacing: .06em; margin-bottom: 5px;
        }
        .detail-info-box .dib-value {
            font-size: 14px; font-weight: 700; color: var(--ink);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1199.98px) {
            .filter-row-2 {
                grid-template-columns: 1fr 1fr;
                row-gap: 16px;
            }
            .filter-search { grid-column: 1 / -1; }
            .filter-submit { grid-column: 1 / -1; }
            .btn-apply-filter { width: 100%; }
        }
        @media (max-width: 767.98px) {
            .filter-row-2 { grid-template-columns: 1fr; }
            .date-input { width: 100%; }
            .cmd-page-header { align-items: flex-start; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // ── ÉTAT FILTRES ──────────────────────────────────────
        let currentStatut = 'tous';
        let currentSearch = '';

        // ── FILTRE STATUT ─────────────────────────────────────
        function setStatut(statut, btn) {
            currentStatut = statut;
            document.querySelectorAll('.filter-statut-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            appliquerFiltresLocaux();
        }

        // ── FILTRE RECHERCHE ──────────────────────────────────
        function searchCommandes(q) {
            currentSearch = q.toLowerCase().trim();
            appliquerFiltresLocaux();
        }

        // ── PÉRIODE RAPIDE ────────────────────────────────────
        function setPeriod(period, btn) {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const today  = new Date();
            const fmt    = d => d.toISOString().split('T')[0];
            const debut  = document.getElementById('date-debut');
            const fin    = document.getElementById('date-fin');

            if (period === 'today') {
                debut.value = fin.value = fmt(today);
            } else if (period === 'week') {
                const mon = new Date(today);
                mon.setDate(today.getDate() - today.getDay() + 1);
                debut.value = fmt(mon);
                fin.value   = fmt(today);
            } else if (period === 'month') {
                debut.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
                fin.value   = fmt(today);
            } else if (period === 'all') {
                debut.value = '2020-01-01';
                fin.value   = fmt(today);
            }
        }

        // ── FILTRES LOCAUX (sans rechargement) ───────────────
        function appliquerFiltresLocaux() {
            const rows   = document.querySelectorAll('#cmds-tbody tr[data-statut]');
            let visible  = 0;

            rows.forEach(tr => {
                const statutOk  = currentStatut === 'tous' || tr.dataset.statut === currentStatut;
                const searchOk  = !currentSearch || tr.dataset.search.includes(currentSearch);
                const show      = statutOk && searchOk;
                tr.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('no-filter-result').classList.toggle('d-none', visible > 0);
        }

        // ── FILTRES SERVEUR (avec rechargement page) ──────────
        function appliquerFiltres() {
            const debut  = document.getElementById('date-debut').value;
            const fin    = document.getElementById('date-fin').value;
            const statut = currentStatut !== 'tous' ? currentStatut : '';
            const search = document.getElementById('search-cmd').value;

            const params = new URLSearchParams();
            if (debut)  params.set('debut',  debut);
            if (fin)    params.set('fin',    fin);
            if (statut) params.set('statut', statut);
            if (search) params.set('search', search);

            window.location.href = '{{ route("commandes.index") }}?' + params.toString();
        }

        // ── MODAL DÉTAIL : initialisation PARESSEUSE ──────────
        function getCmdDetailModal() {
            const el = document.getElementById('cmdDetailModal');
            if (!el) {
                console.error('[Commandes] Élément #cmdDetailModal introuvable dans le DOM.');
                return null;
            }
            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                console.error('[Commandes] bootstrap.js n\'est pas chargé (vérifiez l\'ordre des <script> dans votre layout).');
                return null;
            }
            return bootstrap.Modal.getOrCreateInstance(el);
        }

        function showCmdDetail(id) {
            const modal = getCmdDetailModal();
            if (!modal) {
                if (typeof toastr !== 'undefined') {
                    toastr.error("Impossible d'ouvrir le détail : Bootstrap n'est pas chargé.");
                } else {
                    alert("Impossible d'ouvrir le détail (Bootstrap non chargé). Voir la console.");
                }
                return;
            }

            document.getElementById('cmdDetailBody').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary"></div>
                    <div class="text-muted mt-2" style="font-size:13px">Chargement...</div>
                </div>`;
            modal.show();

            fetch(`/commandes/${id}`, {
                headers: {
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(r => {
                if (!r.ok) throw new Error('Erreur ' + r.status);
                return r.json();
            })
            .then(d => {
                const statusConfig = {
                    en_attente: ['En attente', 'badge-en_attente'],
                    en_cuisson: ['En cuisson', 'badge-en_cuisson'],
                    prete:      ['Prête ✓',    'badge-prete'],
                    servie:     ['Servie',     'badge-servie'],
                    payee:      ['Payée',      'badge-payee'],
                    annulee:    ['Annulée',    'badge-annulee'],
                };
                const [slabel, sclass] = statusConfig[d.statut] ?? [d.statut, ''];

                const itemsHtml = d.items.map(it => `
                    <tr>
                        <td class="ps-3">
                            <div class="fw-500" style="font-size:13px">${it.produit.nom}</div>
                            ${it.notes ? `<small class="text-muted fst-italic">${it.notes}</small>` : ''}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light border text-dark">${it.quantite}</span>
                        </td>
                        <td class="text-end" style="font-size:13px">
                            ${Number(it.prix_unitaire).toLocaleString('fr')} F
                        </td>
                        <td class="text-end fw-bold" style="color:#c9a96e">
                            ${Number(it.sous_total).toLocaleString('fr')} F
                        </td>
                    </tr>`).join('');

                const remiseRow = d.remise > 0 ? `
                    <tr>
                        <td colspan="3" class="text-end text-muted pe-3">Remise</td>
                        <td class="text-end text-danger pe-3">
                            −${Number(d.remise).toLocaleString('fr')} F
                        </td>
                    </tr>` : '';

                // ── AJOUT : ligne frais de livraison (uniquement si type=livraison et montant > 0) ──
                const fraisLivraisonRow = (d.type === 'livraison' && Number(d.frais_livraison) > 0) ? `
                    <tr>
                        <td colspan="3" class="text-end text-muted pe-3">
                            <i class="bi bi-bicycle me-1"></i>Frais de livraison
                        </td>
                        <td class="text-end pe-3" style="color:#0d9fd8">
                            +${Number(d.frais_livraison).toLocaleString('fr')} F
                        </td>
                    </tr>` : '';

                const actionsHtml = (() => {
                    let btns = '';
                    if (!['payee','annulee','en_cuisson'].includes(d.statut)) {
                        btns += `<a href="/commandes/${d.id}" class="btn btn-outline-dark flex-fill">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </a>`;
                    }

                    if (!['payee','annulee','en_cuisson','prete'].includes(d.statut)) {
                        btns += `<button class="btn btn-outline-danger"
                            onclick="annulerCmdModal(${d.id},'${d.numero}')">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>`;
                    }
                    return btns;
                })();

                const motifHtml = (d.statut === 'annulee' && d.motif_annulation) ? `
                    <div class="alert alert-danger d-flex gap-2 align-items-start mb-3">
                        <i class="bi bi-x-circle text-danger mt-1"></i>
                        <div>
                            <div style="font-size:10px;font-weight:600;color:#842029;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">
                                Motif d'annulation
                            </div>
                            <span style="font-size:13px">${d.motif_annulation}</span>
                        </div>
                    </div>` : '';

                document.getElementById('cmdDetailBody').innerHTML = `

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="detail-info-box">
                                <div class="dib-label">Commande</div>
                                <div class="dib-value">${d.numero}</div>
                                <span class="statut-badge ${sclass} mt-2 d-inline-block">${slabel}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-info-box">
                                <div class="dib-label">Table / Type</div>
                                <div class="dib-value">${d.table ? 'Table ' + d.table.numero : (d.type === 'livraison' ? 'Livraison' : 'Emporter')}</div>
                                <small class="text-muted">
                                    ${d.type.replace(/_/g,' ')}
                                    ${d.type === 'livraison' && d.livreur ? ' · ' + d.livreur.name : ''}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-info-box">
                                <div class="dib-label">Serveur</div>
                                <div class="dib-value">${d.serveur?.name ?? '—'}</div>
                                <small class="text-muted">${new Date(d.created_at).toLocaleString('fr-FR', { timeZone: 'Europe/Paris' })}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-info-box" style="background:rgba(201,169,110,.08);border:1px solid rgba(201,169,110,.2)">
                                <div class="dib-label">Total</div>
                                <div style="font-size:22px;font-weight:700;color:#c9a96e">
                                    ${Number(d.total).toLocaleString('fr')} F
                                </div>
                                <small class="text-muted">${d.items.length} article(s)</small>
                            </div>
                        </div>
                    </div>

                    ${motifHtml}

                    <div class="table-responsive mb-3">
                        <table class="table table-sm mb-0" style="border-radius:10px;overflow:hidden">
                            <thead style="background:#f8f9fa">
                                <tr>
                                    <th class="ps-3">Produit</th>
                                    <th class="text-center">Qté</th>
                                    <th class="text-end">P.U</th>
                                    <th class="text-end">Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                            <tfoot style="background:#f8f9fa">
                                <tr>
                                    <td colspan="3" class="text-end text-muted pe-3">Sous-total</td>
                                    <td class="text-end pe-3">${Number(d.sous_total).toLocaleString('fr')} F</td>
                                </tr>
                                ${remiseRow}
                                ${fraisLivraisonRow}
                                <tr>
                                    <td colspan="3" class="text-end fw-bold pe-3">TOTAL</td>
                                    <td class="text-end fw-bold pe-3" style="color:#c9a96e;font-size:15px">
                                        ${Number(d.total).toLocaleString('fr')} F
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    ${d.notes ? `
                    <div class="alert alert-light border d-flex gap-2 align-items-start mb-3">
                        <i class="bi bi-chat-left-text text-muted mt-1"></i>
                        <span style="font-size:13px">${d.notes}</span>
                    </div>` : ''}

                    ${actionsHtml ? `<div class="d-flex gap-2 flex-wrap">${actionsHtml}</div>` : ''}
                `;
            })
            .catch(err => {
                document.getElementById('cmdDetailBody').innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle d-block fs-2 mb-2"></i>
                        Erreur de chargement : ${err.message}
                    </div>`;
            });
        }

        // ── ANNULER (depuis la liste) : motif facultatif ──────
        function annulerCmd(id, num) {
            Swal.fire({
                title: 'Annuler cette commande ?',
                html: `<p style="color:#6b7280;margin-bottom:10px;text-align:left">
                           La commande <strong>${num}</strong> sera marquée comme annulée.
                       </p>
                       <label style="display:block;text-align:left;font-size:11px;font-weight:600;
                                      color:#9299a8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">
                           Motif (facultatif)
                       </label>`,
                input: 'textarea',
                inputPlaceholder: "Ex : erreur de saisie, client absent, rupture de stock...",
                inputAttributes: { 'aria-label': 'Motif annulation' },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText:   'Non, garder',
                confirmButtonText:  'Oui, annuler',
            }).then(r => {
                if (!r.isConfirmed) return;
                _doAnnuler(id, num, (r.value || '').trim());
            });
        }

        // ── ANNULER (depuis le modal détail) : motif facultatif ──
        function annulerCmdModal(id, num) {
            Swal.fire({
                title: 'Annuler cette commande ?',
                html: `<p style="color:#6b7280;margin-bottom:10px;text-align:left">
                           La commande <strong>${num}</strong> sera annulée.
                       </p>
                       <label style="display:block;text-align:left;font-size:11px;font-weight:600;
                                      color:#9299a8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">
                           Motif (facultatif)
                       </label>`,
                input: 'textarea',
                inputPlaceholder: "Ex : erreur de saisie, client absent, rupture de stock...",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText:  'Non',
                confirmButtonText: 'Oui, annuler',
            }).then(r => {
                if (!r.isConfirmed) return;
                const modal = getCmdDetailModal();
                if (modal) modal.hide();
                _doAnnuler(id, num, (r.value || '').trim());
            });
        }

        function _doAnnuler(id, num, motif = '') {
            fetch(`/commandes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                },
                body: JSON.stringify({ motif_annulation: motif }),
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    toastr.success(d.message);
                    const row = document.getElementById(`cmd-row-${id}`);
                    if (row) {
                        row.dataset.statut = 'annulee';
                        row.querySelector('.statut-badge').outerHTML =
                            '<span class="statut-badge badge-annulee"><span class="statut-dot"></span>Annulée</span>';
                        row.querySelectorAll('.action-btn.danger, .action-btn.success, a[href*="show"]')
                            .forEach(el => el.remove());
                    }
                } else {
                    toastr.error(d.message);
                }
            })
            .catch(() => toastr.error('Erreur réseau.'));
        }

        // ── DÉLÉGATION D'ÉVÉNEMENTS ──
        document.getElementById('cmds-tbody').addEventListener('click', (e) => {
            const detailBtn = e.target.closest('.js-cmd-detail');
            if (detailBtn) {
                showCmdDetail(Number(detailBtn.dataset.cmdId));
                return;
            }
            const annulerBtn = e.target.closest('.js-cmd-annuler');
            if (annulerBtn) {
                annulerCmd(Number(annulerBtn.dataset.cmdId), annulerBtn.dataset.cmdNum);
            }
        });

        // ── APPLIQUER FILTRES AU CHARGEMENT (depuis URL) ──────
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);

            const statut = params.get('statut');
            if (statut) {
                const btn = document.querySelector(`[data-statut="${statut}"]`);
                if (btn) setStatut(statut, btn);
            }

            const debut = params.get('debut');
            const fin   = params.get('fin');
            const today = new Date().toISOString().split('T')[0];

            if (debut === today && fin === today) {
                document.querySelector('[data-period="today"]')?.classList.add('active');
            } else if (debut && fin) {
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            }

            const search = params.get('search');
            if (search) {
                document.getElementById('search-cmd').value = search;
                currentSearch = search.toLowerCase();
                appliquerFiltresLocaux();
            }
        });

        // ══════════════════════════════════════════════════════
        // ACTUALISATION AUTOMATIQUE SILENCIEUSE
        // ══════════════════════════════════════════════════════
        const STATUT_CONFIG = {
            en_attente: ['En attente', 'badge-en_attente'],
            en_cuisson: ['En cuisson', 'badge-en_cuisson'],
            prete:      ['Prête ✓',    'badge-prete'],
            servie:     ['Servie',     'badge-servie'],
            payee:      ['Payée',      'badge-payee'],
            annulee:    ['Annulée',    'badge-annulee'],
        };

        function heureRelativeCmd(dateStr) {
            const diffMin = Math.floor((Date.now() - new Date(dateStr).getTime()) / 60000);
            if (diffMin < 1)  return "à l'instant";
            if (diffMin < 60) return `il y a ${diffMin} min`;
            const h = Math.floor(diffMin / 60);
            if (h < 24) return `il y a ${h} h`;
            return `il y a ${Math.floor(h / 24)} j`;
        }

        function buildActionsHtmlCmd(cmd) {
            let html = `<button type="button" class="action-btn js-cmd-detail" data-cmd-id="${cmd.id}" title="Voir le détail"><i class="bi bi-eye"></i></button>`;
            if (!['payee','annulee'].includes(cmd.statut)) {
                html += `<a href="/commandes/${cmd.id}" class="action-btn" title="Modifier"><i class="bi bi-pencil"></i></a>`;
            }
            if (cmd.statut === 'prete') {
                html += `<a href="{{ route('caisse.index') }}" class="action-btn success" title="Encaisser"><i class="bi bi-currency-exchange me-1"></i></a>`;
            }
            if (!['payee','annulee','prete','en_attente'].includes(cmd.statut)) {
                html += `<button type="button" class="action-btn danger js-cmd-annuler" data-cmd-id="${cmd.id}" data-cmd-num="${cmd.numero}" title="Annuler"><i class="bi bi-x-circle"></i></button>`;
            }
            return html;
        }

        function buildRowCmd(cmd) {
            const [slabel, sclass] = STATUT_CONFIG[cmd.statut] ?? [cmd.statut, ''];
            const tableHtml = cmd.table
                ? `<span class="table-badge"><i class="bi bi-grid-3x3-gap me-1"></i>T${cmd.table.numero}</span>`
                : `<span class="table-badge emporter"><i class="bi bi-bag me-1"></i>Emporter</span>`;
            const typeLabel = cmd.type === 'sur_place' ? 'Sur place' : (cmd.type === 'emporter' ? 'Emporter' : 'Livraison');
            const searchStr = `${cmd.numero} ${cmd.table?.numero ?? ''} ${cmd.serveur?.name ?? ''}`.toLowerCase();
            const dateStr   = (cmd.created_at || '').slice(0, 10);
            const heure     = new Date(cmd.created_at).toLocaleTimeString('fr', { hour: '2-digit', minute: '2-digit' });
            const serveurInit = (cmd.serveur?.name ?? '??').substring(0, 2).toUpperCase();
            const serveurNom  = (cmd.serveur?.name ?? '').substring(0, 12);

            return `
            <tr id="cmd-row-${cmd.id}" data-statut="${cmd.statut}" data-search="${searchStr}" data-date="${dateStr}">
                <td class="ps-4">
                    <div class="cell-primary">${cmd.numero}</div>
                    <small class="text-muted">${typeLabel}</small>
                </td>
                <td>${tableHtml}</td>
                <td class="cell-muted">${cmd.client?.nom ?? '—'}</td>
                <td>
                    <div class="serveur-cell">
                        <div class="serveur-avatar">${serveurInit}</div>
                        <span class="serveur-nom">${serveurNom}</span>
                    </div>
                </td>
                <td class="text-center"><span class="qty-badge">${cmd.items_count ?? 0}</span></td>
                <td class="text-end cell-total">${Number(cmd.total).toLocaleString('fr')} F</td>
                <td><span class="statut-badge ${sclass}"><span class="statut-dot"></span>${slabel}</span></td>
                <td>
                    <div class="cell-time">${heure}</div>
                    <small class="text-muted cell-time-rel">${heureRelativeCmd(cmd.created_at)}</small>
                </td>
                <td class="text-end pe-4">
                    <div class="d-flex justify-content-end gap-1">${buildActionsHtmlCmd(cmd)}</div>
                </td>
            </tr>`;
        }

        function flashRow(el) {
            el.classList.remove('row-flash');
            void el.offsetWidth;
            el.classList.add('row-flash');
        }

        async function pollCommandes() {
            try {
                const debut = document.getElementById('date-debut').value;
                const fin   = document.getElementById('date-fin').value;
                const params = new URLSearchParams({ debut, fin });

                const r = await fetch(`{{ route('commandes.index') }}/poll?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                });
                if (!r.ok) return;
                const cmds = await r.json();
                if (!Array.isArray(cmds)) return;

                const tbody = document.getElementById('cmds-tbody');
                const kpiCounts = { en_attente: 0, en_cuisson: 0, prete: 0, payee: 0 };

                cmds.forEach(cmd => {
                    if (kpiCounts[cmd.statut] !== undefined) kpiCounts[cmd.statut]++;

                    const existing = document.getElementById(`cmd-row-${cmd.id}`);

                    if (!existing) {
                        tbody.insertAdjacentHTML('afterbegin', buildRowCmd(cmd));
                        const el = document.getElementById(`cmd-row-${cmd.id}`);
                        if (el) flashRow(el);
                        return;
                    }

                    const totalActuel = Number(existing.querySelector('td:nth-child(6)')?.textContent.replace(/[^\d]/g, '')) || 0;
                    const aChange = existing.dataset.statut !== cmd.statut || totalActuel !== Math.round(Number(cmd.total));

                    if (aChange) {
                        const temp = document.createElement('tbody');
                        temp.innerHTML = buildRowCmd(cmd);
                        const nouvelleLigne = temp.firstElementChild;
                        existing.replaceWith(nouvelleLigne);
                        flashRow(nouvelleLigne);
                    }
                });

                if (cmds.length > 0) {
                    tbody.querySelector('tr td[colspan]')?.closest('tr')?.remove();
                }

                appliquerFiltresLocaux();

                document.getElementById('subtitle-count').textContent = `${cmds.length} commande(s)`;
                Object.entries(kpiCounts).forEach(([k, v]) => {
                    const el = document.getElementById(`kpi-value-${k}`);
                    if (el && el.textContent !== String(v)) el.textContent = v;
                });

            } catch (e) { /* réseau indisponible — on retente au prochain tick, sans rien signaler */ }
        }

        setInterval(pollCommandes, 10000);
    </script>
@endpush