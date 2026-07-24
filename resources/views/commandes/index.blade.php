@extends('layouts.app')
@section('title', 'Commandes')
@section('page_title', 'Commandes')
@section('breadcrumb', 'Gestion des commandes')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Commandes du jour</h5>
        <small class="text-muted" id="subtitle-count">
            {{ $commandes->total() }} commande(s) — {{ now()->locale('fr')->translatedFormat('l d F Y') }}
        </small>
    </div>
    <a href="{{ route('commandes.create') }}" class="btn btn-dark px-4">
        <i class="bi bi-plus-lg me-2"></i>Nouvelle commande
    </a>
</div>

{{-- ══ KPI RAPIDES ══ --}}
<div class="row g-3 mb-4">
    @php
        $kpis = [
            ['label'=>'En attente', 'count'=>$commandes->getCollection()->where('statut','en_attente')->count(), 'accent'=>'#c9a96e','icon'=>'hourglass-split'],
            ['label'=>'En cuisson', 'count'=>$commandes->getCollection()->where('statut','en_cuisson')->count(), 'accent'=>'#8a94a6','icon'=>'fire'],
            ['label'=>'Prêtes',     'count'=>$commandes->getCollection()->where('statut','prete')->count(),      'accent'=>'#5f9c76','icon'=>'check-circle'],
            ['label'=>'Payées',     'count'=>$commandes->getCollection()->where('statut','payee')->count(),      'accent'=>'#212529','icon'=>'wallet'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                    <div class="kpi-value">{{ $kpi['count'] }}</div>
                </div>
                <div class="kpi-icon" style="color:{{ $kpi['accent'] }}">
                    <i class="bi bi-{{ $kpi['icon'] }}"></i>
                </div>
            </div>
            <div class="kpi-bar" style="background:{{ $kpi['accent'] }}"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ FILTRES ══ --}}
<div class="card filter-card mb-4">
    <div class="card-body">

        {{-- Ligne 1 : Statut (pleine largeur) --}}
        <div class="filter-block">
            <span class="filter-label">Statut</span>
            <div class="d-flex gap-2 flex-wrap" id="statut-btns">
                @foreach([
                    'tous'       => ['Toutes',     '#6c757d'],
                    'en_attente' => ['En attente', '#856404'],
                    'en_cuisson' => ['En cuisson', '#055160'],
                    'prete'      => ['Prête ✓',    '#0a3622'],
                    'servie'     => ['Servie',     '#084298'],
                    'payee'      => ['Payée',      '#fff'],
                    'annulee'    => ['Annulée',    '#842029'],
                ] as $s => [$label, $tc])
                <button class="filter-statut-btn {{ $s === 'tous' ? 'active' : '' }}"
                    data-statut="{{ $s }}"
                    onclick="setStatut('{{ $s }}', this)">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        <hr class="filter-sep">

        {{-- Ligne 2 : Période rapide / dates / recherche / bouton — tous alignés --}}
        <div class="d-flex flex-wrap align-items-end gap-3">

            <div class="filter-block">
                <span class="filter-label">Période rapide</span>
                <div class="d-flex gap-2">
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

            <div class="filter-block">
                <span class="filter-label">Du</span>
                <input type="date" id="date-debut" class="form-control form-control-sm date-input"
                    value="{{ today()->format('Y-m-d') }}">
            </div>

            <div class="filter-block">
                <span class="filter-label">Au</span>
                <input type="date" id="date-fin" class="form-control form-control-sm date-input"
                    value="{{ today()->format('Y-m-d') }}">
            </div>

            <div class="filter-block flex-grow-1" style="min-width:180px;max-width:260px">
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

            <div class="filter-block ms-md-auto">
                <span class="filter-label" style="color:transparent">.</span>
                <button class="btn btn-dark btn-sm px-4" onclick="appliquerFiltres()">
                    <i class="bi bi-funnel me-1"></i>Filtrer
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══ TABLEAU ══ --}}
<div class="card">
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
                            <div class="fw-bold" style="font-size:13px">{{ $cmd->numero }}</div>
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

                        <td style="font-size:12px;color:#6b7280">
                            {{ $cmd->client?->nom ?? '—' }}
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="serveur-avatar">
                                    {{ strtoupper(substr($cmd->serveur->name, 0, 2)) }}
                                </div>
                                <span style="font-size:12px">{{ Str::limit($cmd->serveur->name, 12) }}</span>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-light border text-dark" style="font-size:11px">
                                {{ $cmd->items->count() }}
                            </span>
                        </td>

                        <td class="text-end fw-bold" style="color:#c9a96e;font-size:14px">
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
                            <span class="statut-badge {{ $sclass }}">{{ $slabel }}</span>
                        </td>

                        <td>
                            <div style="font-size:13px;font-weight:500">{{ $cmd->created_at->format('H:i') }}</div>
                            <small class="text-muted" style="font-size:11px">
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
                                @if($cmd->statut === 'prete')
                                <a href="{{ route('caisse.index') }}"
                                    class="action-btn success" title="Encaisser">
                                    <i class="bi bi-currency-exchange me-1"></i>
                                </a>
                                @endif
                                @if(!in_array($cmd->statut, ['payee','annulee']))
                                <button type="button" class="action-btn danger js-cmd-annuler"
                                    data-cmd-id="{{ $cmd->id }}" data-cmd-num="{{ $cmd->numero }}"
                                    title="Annuler">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt d-block fs-1 mb-2 opacity-25"></i>
                            Aucune commande aujourd'hui.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Message aucun résultat filtre --}}
        <div id="no-filter-result" class="text-center py-5 text-muted d-none">
            <i class="bi bi-funnel d-block fs-1 mb-2 opacity-25"></i>
            Aucune commande ne correspond aux filtres.
        </div>
    </div>

    {{-- Pagination --}}
    @if($commandes->hasPages())
    <div class="card-footer bg-white d-flex align-items-center justify-content-between px-4 py-3">
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
    <style>
        .fw-500 { font-weight: 500; }

        /* ── KPI (sobres, un seul accent fin) ── */
        .kpi-card {
            position: relative;
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 12px;
            padding: 16px 20px 18px;
            overflow: hidden;
            transition: box-shadow .15s, transform .15s;
        }
        .kpi-card:hover { box-shadow: 0 6px 18px rgba(20,20,30,.06); transform: translateY(-1px); }
        .kpi-label {
            font-size: 11px; font-weight: 600;
            color: #9299a8; text-transform: uppercase;
            letter-spacing: .05em; margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 26px; font-weight: 700; color: #1a1a2e; line-height: 1;
        }
        .kpi-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
            background: #f7f7f9;
        }
        .kpi-bar {
            position: absolute; left: 0; bottom: 0;
            width: 100%; height: 3px; opacity: .55;
        }

        /* ── CARTE FILTRES ── */
        .filter-card { border: 1px solid #eef0f3; border-radius: 12px; }
        .filter-card .card-body { padding: 20px 22px; }
        .filter-block { display: flex; flex-direction: column; gap: 7px; }
        .filter-label {
            font-size: 11px; font-weight: 600;
            color: #9299a8; text-transform: uppercase;
            letter-spacing: .05em;
        }
        .filter-sep { border: none; border-top: 1px solid #f0f0f4; margin: 18px 0; }

        .date-input { width: 142px; }
        .search-group { width: 100%; }

        /* Boutons statut : sobres, un seul état actif contrasté */
        .filter-statut-btn {
            padding: 5px 14px; border-radius: 20px;
            border: 1px solid #e8e9ee; background: #fbfbfc;
            font-size: 12.5px; font-weight: 500; color: #6b7280;
            cursor: pointer; transition: all .15s; white-space: nowrap;
        }
        .filter-statut-btn:hover { border-color: #c9a96e; color: #a9834a; background: #fff; }
        .filter-statut-btn.active {
            background: #212529; border-color: #212529; color: #fff;
        }

        /* Boutons période : même gabarit que statut, plus discrets */
        .period-btn {
            padding: 5px 14px; border-radius: 20px;
            border: 1px solid #e8e9ee; background: #fbfbfc;
            font-size: 12.5px; font-weight: 500; color: #6b7280;
            cursor: pointer; transition: all .15s; white-space: nowrap;
        }
        .period-btn:hover { border-color: #9299a8; color: #1a1a2e; background: #fff; }
        .period-btn.active { background: #f0f0f5; border-color: #c9a96e; color: #1a1a2e; }

        /* ── TABLE ── */
        .table-badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 6px;
            font-size: 12px; font-weight: 600;
            background: #f5f5f8; color: #374151;
        }
        .table-badge.emporter {
            background: #fbfbfc; color: #9299a8; border: 1px solid #eef0f3;
        }
        .serveur-avatar {
            width: 26px; height: 26px; border-radius: 6px;
            background: #eeeef3; color: #374151;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; flex-shrink: 0;
        }
        .statut-badge {
            display: inline-block; padding: 4px 10px;
            border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .badge-en_attente { background:#fff3cd; color:#856404; }
        .badge-en_cuisson { background:#cff4fc; color:#055160; }
        .badge-prete      { background:#d1e7dd; color:#0a3622; }
        .badge-servie     { background:#cfe2ff; color:#084298; }
        .badge-payee      { background:#212529; color:#fff; }
        .badge-annulee    { background:#f8d7da; color:#842029; }

        /* ── ACTIONS ── */
        .action-btn {
            width: 30px; height: 30px;
            border-radius: 7px; border: 1px solid #eef0f3;
            background: #fff; display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 13px; color: #6b7280; cursor: pointer;
            transition: all .12s; text-decoration: none;
        }
        .action-btn:hover { background: #f5f5f7; color: #1a1a2e; border-color: #d0d0d8; }
        .action-btn.success { color: #198754; border-color: #d1e7dd; }
        .action-btn.success:hover { background: #d1e7dd; }
        .action-btn.danger  { color: #dc3545; border-color: #f8d7da; }
        .action-btn.danger:hover  { background: #f8d7da; }
        /* ── MODAL DÉTAIL ── */
        .detail-info-box {
            background: #f8f9fa; border-radius: 10px;
            padding: 14px 16px;
        }
        .detail-info-box .dib-label {
            font-size: 10px; font-weight: 600;
            color: #9299a8; text-transform: uppercase;
            letter-spacing: .06em; margin-bottom: 4px;
        }
        .detail-info-box .dib-value {
            font-size: 14px; font-weight: 600; color: #1a1a2e;
        }

        /* ── PAGINATION ── */
        .pagination { margin: 0; }
        .page-link { font-size: 13px; color: #374151; border-color: #eef0f3; }
        .page-item.active .page-link { background: #212529; border-color: #212529; }

        /* ── RESPONSIVE ── */
        @media (max-width: 767.98px) {
            .filter-block.ms-md-auto { margin-left: 0 !important; }
            .date-input { width: 100%; }
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
        // On ne crée l'instance bootstrap.Modal qu'au moment où on en a
        // besoin (au clic), jamais au chargement du script. Ça évite les
        // erreurs silencieuses si ce <script> s'exécute avant que le
        // bundle Bootstrap JS soit disponible, ou avant que le DOM du
        // modal soit prêt.
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

                const actionsHtml = (() => {
                    let btns = '';
                    if (!['payee','annulee'].includes(d.statut)) {
                        btns += `<a href="/commandes/${d.id}" class="btn btn-outline-dark flex-fill">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </a>`;
                    }
                    if (d.statut === 'prete') {
                        btns += `<a href="/caisse" class="btn btn-success flex-fill">
                            <i class="bi bi-cash-register me-1"></i>Encaisser
                        </a>`;
                    }
                    if (!['payee','annulee'].includes(d.statut)) {
                        btns += `<button class="btn btn-outline-danger"
                            onclick="annulerCmdModal(${d.id},'${d.numero}')">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>`;
                    }
                    return btns;
                })();

                // ── Bloc motif d'annulation (affiché uniquement si commande annulée) ──
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

                    {{-- Infos principales --}}
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
                                <div class="dib-value">${d.table ? 'Table ' + d.table.numero : 'Emporter'}</div>
                                <small class="text-muted">${d.type.replace(/_/g,' ')}</small>
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

                    {{-- Motif d'annulation --}}
                    ${motifHtml}

                    {{-- Articles --}}
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
                                <tr>
                                    <td colspan="3" class="text-end fw-bold pe-3">TOTAL</td>
                                    <td class="text-end fw-bold pe-3" style="color:#c9a96e;font-size:15px">
                                        ${Number(d.total).toLocaleString('fr')} F
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Notes --}}
                    ${d.notes ? `
                    <div class="alert alert-light border d-flex gap-2 align-items-start mb-3">
                        <i class="bi bi-chat-left-text text-muted mt-1"></i>
                        <span style="font-size:13px">${d.notes}</span>
                    </div>` : ''}

                    {{-- Actions --}}
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
                    // Mettre à jour le badge statut dans la ligne
                    const row = document.getElementById(`cmd-row-${id}`);
                    if (row) {
                        row.dataset.statut = 'annulee';
                        row.querySelector('.statut-badge').outerHTML =
                            '<span class="statut-badge badge-annulee">Annulée</span>';
                        // Cacher les boutons d'action
                        row.querySelectorAll('.action-btn.danger, .action-btn.success, a[href*="show"]')
                            .forEach(el => el.remove());
                    }
                } else {
                    toastr.error(d.message);
                }
            })
            .catch(() => toastr.error('Erreur réseau.'));
        }

        // ── DÉLÉGATION D'ÉVÉNEMENTS (remplace les onclick inline
        //     pour les boutons "voir détail" / "annuler" du tableau) ──
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

            // Restaurer filtre statut
            const statut = params.get('statut');
            if (statut) {
                const btn = document.querySelector(`[data-statut="${statut}"]`);
                if (btn) setStatut(statut, btn);
            }

            // Restaurer période active
            const debut = params.get('debut');
            const fin   = params.get('fin');
            const today = new Date().toISOString().split('T')[0];

            if (debut === today && fin === today) {
                document.querySelector('[data-period="today"]')?.classList.add('active');
            } else if (debut && fin) {
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            }

            // Restaurer recherche
            const search = params.get('search');
            if (search) {
                document.getElementById('search-cmd').value = search;
                currentSearch = search.toLowerCase();
                appliquerFiltresLocaux();
            }
        });
    </script>
@endpush