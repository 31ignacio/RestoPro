
@extends('layouts.app')
@section('title', 'Dépenses')
@section('page_title', 'Dépenses')
@section('breadcrumb', 'Suivi financier')

@section('content')

{{-- ══ KPI ══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-sun me-1"></i>Aujourd'hui</div>
            <div class="kpi-val">{{ number_format($stats['total_jour'], 0, ',', ' ') }}<span>F</span></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-calendar3 me-1"></i>Ce mois</div>
            <div class="kpi-val">{{ number_format($stats['total_mois'], 0, ',', ' ') }}<span>F</span></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-list-check me-1"></i>Entrées</div>
            <div class="kpi-val">{{ $depenses->total() }}<span>total</span></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-bar-chart me-1"></i>Top catégorie</div>
            @php $topCat = $stats['par_categorie']->first(); @endphp
            <div class="kpi-val" style="font-size:16px;letter-spacing:0">
                {{ $topCat ? ucfirst($topCat->categorie) : '—' }}
            </div>
            @if($topCat)
            <div class="kpi-sub">{{ number_format($topCat->total, 0, ',', ' ') }} F</div>
            @endif
        </div>
    </div>
</div>

{{-- ══ CORPS ══ --}}
<div class="row g-4 align-items-start">

    {{-- Sidebar gauche --}}
    <div class="col-12 col-lg-3">

        <button class="btn-new w-100 mb-3" onclick="openDepenseModal()">
            <i class="bi bi-plus-lg me-2"></i>Nouvelle dépense
        </button>

        @if($stats['par_categorie']->count())
        <div class="side-card">
            <div class="side-card-title">Répartition du mois</div>
            @php
                $totalMois = $stats['par_categorie']->sum('total');
                $catConfig = \App\Http\Controllers\DepenseController::CAT_CONFIG;
            @endphp
            <div class="rep-list">
                @foreach($stats['par_categorie'] as $cat)
                @php
                    $pct  = $totalMois > 0 ? round(($cat->total / $totalMois) * 100) : 0;
                    $meta = $catConfig[$cat->categorie] ?? $catConfig['autre'];
                @endphp
                <div class="rep-row">
                    <div class="rep-head">
                        <span class="rep-name">{{ ucfirst($cat->categorie) }}</span>
                        <span class="rep-pct">{{ $pct }}%</span>
                    </div>
                    <div class="rep-track">
                        <div class="rep-fill" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="rep-val">{{ number_format($cat->total, 0, ',', ' ') }} F</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Tableau --}}
    <div class="col-12 col-lg-9">
        <div class="main-card">

            <div class="mc-header">
                <div>
                    <div class="mc-title">Historique des dépenses</div>
                    <div class="mc-sub">{{ $depenses->total() }} entrée(s)</div>
                </div>
            </div>

            {{-- Table desktop --}}
            <div class="table-scroll d-none d-md-block">
                <table class="dep-table w-100">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Catégorie</th>
                            <th class="text-end">Montant</th>
                            <th class="d-none d-lg-table-cell">Date</th>
                            <th class="d-none d-xl-table-cell">Par</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depenses as $dep)
                        <tr class="dep-tr" id="dep-row-{{ $dep->id }}">
                            <td class="td-lib">
                                <div class="dep-lib">{{ $dep->libelle }}</div>
                                @if($dep->notes)
                                <div class="dep-note-hint">{{ Str::limit($dep->notes, 48) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="cat-pill">{{ ucfirst($dep->categorie) }}</span>
                            </td>
                            <td class="text-end td-amt">
                                {{ number_format($dep->montant, 0, ',', ' ') }}<span class="amt-cur">F</span>
                            </td>
                            <td class="d-none d-lg-table-cell td-date">
                                {{ $dep->date_depense->format('d/m/Y') }}
                            </td>
                            <td class="d-none d-xl-table-cell">
                                <div class="dep-user">
                                    <div class="dep-av">{{ strtoupper(substr($dep->user->name,0,1)) }}</div>
                                    <span>{{ $dep->user->name }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="act-grp">
                                    <button class="act-btn" onclick="showDetail({{ $dep->id }})" title="Détail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="act-btn" onclick="openDepenseModal({{ $dep->id }})" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="act-btn act-btn--del"
                                        onclick="deleteDepense({{ $dep->id }},'{{ addslashes($dep->libelle) }}')"
                                        title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="empty-block">
                                <i class="bi bi-wallet2"></i>
                                <p>Aucune dépense enregistrée.</p>
                                <button class="btn btn-sm btn-dark" onclick="openDepenseModal()">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                                </button>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile list --}}
            <div class="d-md-none mob-list">
                @forelse($depenses as $dep)
                <div class="mob-item" id="dep-row-{{ $dep->id }}">
                    <div class="mob-body">
                        <div class="mob-lib">{{ $dep->libelle }}</div>
                        <div class="mob-meta">
                            <span class="cat-pill">{{ ucfirst($dep->categorie) }}</span>
                            <span class="mob-date">{{ $dep->date_depense->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="mob-right">
                        <div class="mob-amt">
                            {{ number_format($dep->montant, 0, ',', ' ') }}<span>F</span>
                        </div>
                        <div class="act-grp act-grp--sm">
                            <button class="act-btn" onclick="showDetail({{ $dep->id }})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="act-btn" onclick="openDepenseModal({{ $dep->id }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-btn--del"
                                onclick="deleteDepense({{ $dep->id }},'{{ addslashes($dep->libelle) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-block py-5">
                    <i class="bi bi-wallet2"></i>
                    <p>Aucune dépense enregistrée.</p>
                </div>
                @endforelse
            </div>

            @if($depenses->hasPages())
            <div class="mc-footer">
                <small class="text-muted">
                    {{ $depenses->firstItem() }}–{{ $depenses->lastItem() }}
                    sur {{ $depenses->total() }}
                </small>
                {!! $depenses->links('vendor.pagination.custom') !!}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ══ MODAL FORMULAIRE ══ --}}
<div class="modal fade" id="depenseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rp-modal">
            <div class="rp-modal-header">
                <div>
                    <h5 class="rp-modal-title" id="depModalTitle">Nouvelle dépense</h5>
                    <p class="rp-modal-sub" id="depModalSub">Renseignez les informations</p>
                </div>
                <button class="rp-close" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="rp-modal-body">
                <input type="hidden" id="dep_id">

                <div class="rp-field">
                    <label class="rp-label">Libellé <span class="rp-req">*</span></label>
                    <input type="text" id="dep_libelle" class="rp-input"
                        placeholder="Achat légumes, Loyer, Salaire...">
                </div>

                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">Catégorie <span class="rp-req">*</span></label>
                            <select id="dep_categorie" class="rp-input rp-select">
                                <option value="fournisseur">Fournisseur</option>
                                <option value="salaire">Salaire</option>
                                <option value="loyer">Loyer</option>
                                <option value="energie">Énergie</option>
                                <option value="equipement">Équipement</option>
                                <option value="entretien">Entretien</option>
                                <option value="transport">Transport</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">Montant <span class="rp-req">*</span></label>
                            <div class="rp-input-wrap">
                                <input type="number" id="dep_montant" class="rp-input"
                                    placeholder="0" min="0" step="1">
                                <span class="rp-suffix">FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rp-field">
                    <label class="rp-label">Date <span class="rp-req">*</span></label>
                    <input type="date" id="dep_date" class="rp-input"
                        value="{{ today()->format('Y-m-d') }}">
                </div>

                <div class="rp-field">
                    <label class="rp-label">
                        Notes
                        <span class="rp-opt">optionnel</span>
                    </label>
                    <textarea id="dep_notes" class="rp-input rp-textarea"
                        placeholder="Numéro de facture, détails..."
                        oninput="autoResize(this)"></textarea>
                </div>
            </div>

            <div class="rp-modal-footer">
                <button class="rp-btn-cancel" data-bs-dismiss="modal">Annuler</button>
                <button class="rp-btn-save" onclick="saveDepense()">
                    <span id="depSaveTxt"><i class="bi bi-check-lg me-1"></i>Enregistrer</span>
                    <span id="depSaveSpin" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DÉTAIL ══ --}}
<div class="modal fade" id="depDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rp-modal" id="depDetailBody">
            <div class="text-center py-5">
                <div class="spinner-border spinner-border-sm text-secondary"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>

/* ══════════════════════
   KPI CARDS
══════════════════════ */
.kpi-card {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 14px;
    padding: 18px 20px;
    transition: box-shadow .18s;
}
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
.kpi-label {
    font-size: 11px; font-weight: 600;
    color: #9299a8; text-transform: uppercase;
    letter-spacing: .07em; margin-bottom: 8px;
}
.kpi-val {
    font-size: clamp(20px,3.5vw,26px); font-weight: 800;
    color: #1a1a2e; letter-spacing: -.5px; line-height: 1;
}
.kpi-val span {
    font-size: 12px; font-weight: 500;
    color: #9299a8; margin-left: 4px; letter-spacing: 0;
}
.kpi-sub { font-size: 11px; color: #9299a8; margin-top: 4px; }

/* ══════════════════════
   BOUTON NOUVEAU
══════════════════════ */
.btn-new {
    background: #1a1a2e; color: #fff;
    border: none; border-radius: 12px;
    padding: 12px 18px; font-size: 13.5px;
    font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, box-shadow .15s, transform .15s;
}
.btn-new:hover {
    background: #0f1117;
    box-shadow: 0 4px 16px rgba(15,17,23,.22);
    transform: translateY(-1px);
}

/* ══════════════════════
   SIDEBAR CARD
══════════════════════ */
.side-card {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 14px;
    padding: 18px 20px;
}
.side-card-title {
    font-size: 12px; font-weight: 700;
    color: #374151; text-transform: uppercase;
    letter-spacing: .06em; margin-bottom: 16px;
}
.rep-list { display: flex; flex-direction: column; gap: 14px; }
.rep-row {}
.rep-head {
    display: flex; justify-content: space-between;
    margin-bottom: 5px;
}
.rep-name { font-size: 12px; font-weight: 600; color: #374151; }
.rep-pct  { font-size: 11px; font-weight: 700; color: #6b7280; }
.rep-track {
    height: 5px; background: #f0f0f5;
    border-radius: 3px; overflow: hidden; margin-bottom: 3px;
}
.rep-fill {
    height: 100%; background: #1a1a2e;
    border-radius: 3px; width: 0;
    animation: barGrow .6s cubic-bezier(.4,0,.2,1) forwards .1s;
}
@keyframes barGrow { from{width:0} to{width:var(--w,100%)} }
/* on passe --w via l'attribut style width déjà dans le HTML */
.rep-val { font-size: 11px; color: #9299a8; text-align: right; }

/* ══════════════════════
   MAIN CARD
══════════════════════ */
.main-card {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 14px;
    overflow: hidden;
}
.mc-header {
    padding: 18px 22px 14px;
    border-bottom: 1px solid #f0f0f5;
    display: flex; align-items: center; justify-content: space-between;
}
.mc-title { font-size: 14px; font-weight: 700; color: #1a1a2e; }
.mc-sub   { font-size: 11.5px; color: #9299a8; margin-top: 2px; }

/* ── Table ── */
.table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.dep-table { border-collapse: collapse; }
.dep-table th {
    padding: 10px 16px;
    font-size: 10.5px; font-weight: 700;
    color: #9299a8; text-transform: uppercase;
    letter-spacing: .07em; background: #fafafa;
    border-bottom: 1px solid #f0f0f5;
    white-space: nowrap;
}
.dep-table th:first-child { padding-left: 22px; }
.dep-table th:last-child  { padding-right: 22px; }
.dep-table td {
    padding: 13px 16px;
    border-bottom: 1px solid #f5f5f7;
    vertical-align: middle;
}
.dep-table td:first-child { padding-left: 22px; }
.dep-table td:last-child  { padding-right: 22px; }
.dep-table tbody tr:last-child td { border-bottom: none; }
.dep-tr { transition: background .1s; }
.dep-tr:hover { background: #fafafa; }

.dep-lib { font-size: 13.5px; font-weight: 600; color: #1a1a2e; }
.dep-note-hint {
    font-size: 11.5px; color: #b0b7c3;
    margin-top: 2px; font-style: italic;
}
.cat-pill {
    display: inline-block;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
    background: #f3f4f6; color: #374151;
    white-space: nowrap;
}
.td-amt {
    font-size: 14px; font-weight: 800; color: #1a1a2e;
    white-space: nowrap;
}
.amt-cur {
    font-size: 10px; font-weight: 500;
    color: #9299a8; margin-left: 2px;
}
.td-date { font-size: 12px; color: #6b7280; white-space: nowrap; }

.dep-user {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; color: #374151;
}
.dep-av {
    width: 26px; height: 26px; border-radius: 7px;
    background: #f3f4f6; color: #374151;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── Actions ── */
.act-grp { display: flex; justify-content: flex-end; gap: 4px; }
.act-grp--sm .act-btn { width: 26px; height: 26px; font-size: 11px; }
.act-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid #e8e8ed; background: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; color: #6b7280; cursor: pointer;
    transition: all .12s;
}
.act-btn:hover { background: #f3f4f6; color: #1a1a2e; border-color: #d0d0d8; }
.act-btn--del  { color: #dc3545; border-color: #fde8e8; }
.act-btn--del:hover { background: #fde8e8; }

/* ── Footer pagination ── */
.mc-footer {
    padding: 12px 22px;
    border-top: 1px solid #f0f0f5;
    display: flex; align-items: center;
    justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
}
.pagination { margin: 0; gap: 3px; }
.pagination .page-item .page-link {
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px !important; border: 1px solid #e8e8ed;
    font-size: 12px; color: #6b7280; padding: 0; transition: all .12s;
}
.pagination .page-item.active .page-link  { background:#1a1a2e; border-color:#1a1a2e; color:#fff; }
.pagination .page-item.disabled .page-link{ opacity:.35; }
.pagination .page-item .page-link:hover   { background:#f3f4f6; color:#1a1a2e; }

/* ── Empty ── */
.empty-block { text-align: center; padding: 48px 20px; color: #9299a8; }
.empty-block i { font-size: 40px; opacity: .18; display: block; margin-bottom: 10px; }
.empty-block p { font-size: 13px; margin-bottom: 14px; }

/* ══════════════════════
   MOBILE LIST
══════════════════════ */
.mob-list { padding: 0 4px; }
.mob-item {
    display: flex; align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid #f5f5f7;
    gap: 12px; transition: background .1s;
}
.mob-item:last-child { border-bottom: none; }
.mob-item:hover { background: #fafafa; }
.mob-body { flex: 1; min-width: 0; }
.mob-lib  {
    font-size: 13.5px; font-weight: 600; color: #1a1a2e;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mob-meta {
    display: flex; align-items: center;
    gap: 8px; margin-top: 5px; flex-wrap: wrap;
}
.mob-date { font-size: 11px; color: #9299a8; }
.mob-right { flex-shrink: 0; text-align: right; }
.mob-amt {
    font-size: 14px; font-weight: 800; color: #1a1a2e;
    letter-spacing: -.3px; white-space: nowrap;
}
.mob-amt span { font-size: 10px; color: #9299a8; margin-left: 2px; }

/* ══════════════════════
   MODALS PARTAGÉS
══════════════════════ */
.rp-modal {
    border: 0; border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.13);
    overflow: hidden;
}

/* Header */
.rp-modal-header {
    display: flex; align-items: flex-start;
    justify-content: space-between;
    padding: 22px 24px 16px;
    border-bottom: 1px solid #f0f0f5;
}
.rp-modal-title {
    font-size: 16px; font-weight: 700;
    color: #1a1a2e; margin: 0 0 3px;
}
.rp-modal-sub { font-size: 12px; color: #9299a8; margin: 0; }
.rp-close {
    width: 32px; height: 32px; border-radius: 9px;
    border: 1px solid #e8e8ed; background: #fff;
    color: #6b7280; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; transition: all .12s; margin-left: 12px;
}
.rp-close:hover { background: #f3f4f6; color: #1a1a2e; }

/* Body */
.rp-modal-body {
    padding: 20px 24px;
    display: flex; flex-direction: column; gap: 16px;
}
.rp-field { display: flex; flex-direction: column; gap: 6px; }
.rp-label {
    font-size: 12px; font-weight: 600; color: #374151;
    display: flex; align-items: center; gap: 6px;
}
.rp-req { color: #ef4444; }
.rp-opt {
    font-weight: 400; color: #9299a8;
    font-size: 11px;
}
.rp-input {
    width: 100%; padding: 10px 14px;
    border: 1.5px solid #e8e8ed; border-radius: 10px;
    font-size: 13.5px; color: #1a1a2e;
    font-family: inherit; background: #fff; outline: none;
    transition: border-color .15s, box-shadow .15s;
    -webkit-appearance: none;
}
.rp-input:focus {
    border-color: #1a1a2e;
    box-shadow: 0 0 0 3px rgba(26,26,46,.07);
}
.rp-select { cursor: pointer; }
.rp-input-wrap { position: relative; }
.rp-input-wrap .rp-input { padding-right: 58px; }
.rp-suffix {
    position: absolute; right: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 10.5px; font-weight: 700;
    color: #9299a8; pointer-events: none; letter-spacing: .04em;
}

/* Textarea auto-resize */
.rp-textarea {
    resize: none; overflow: hidden;
    min-height: 72px; max-height: 260px;
    overflow-y: auto; line-height: 1.65;
}

/* Footer */
.rp-modal-footer {
    display: flex; gap: 10px;
    padding: 14px 24px 22px;
    border-top: 1px solid #f0f0f5;
}
.rp-btn-cancel {
    flex: 1; padding: 11px;
    background: #f3f4f6; border: 0;
    border-radius: 10px; font-size: 13.5px;
    font-weight: 600; color: #374151;
    cursor: pointer; transition: background .12s;
}
.rp-btn-cancel:hover { background: #e8e8ed; }
.rp-btn-save {
    flex: 2; padding: 11px;
    background: #1a1a2e; border: 0;
    border-radius: 10px; font-size: 13.5px;
    font-weight: 600; color: #fff;
    cursor: pointer; transition: background .12s, transform .12s;
}
.rp-btn-save:hover { background: #0f1117; transform: translateY(-1px); }

/* ══════════════════════
   MODAL DÉTAIL
══════════════════════ */
.det-hero {
    background: #fafafa;
    border-bottom: 1px solid #f0f0f5;
    padding: 24px;
    position: relative;
}
.det-ref {
    font-size: 10px; font-weight: 700;
    color: #b0b7c3; letter-spacing: .12em;
    text-transform: uppercase; margin-bottom: 10px;
}
.det-cat { margin-bottom: 10px; }
.det-lib {
    font-size: 20px; font-weight: 800;
    color: #1a1a2e; line-height: 1.2; margin-bottom: 8px;
}
.det-price {
    font-size: 34px; font-weight: 900;
    color: #1a1a2e; letter-spacing: -2px; line-height: 1;
}
.det-price sub {
    font-size: 15px; font-weight: 600;
    color: #9299a8; margin-left: 4px;
    letter-spacing: 0; vertical-align: baseline;
}

.det-body { padding: 20px 24px; }
.det-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 10px; margin-bottom: 16px;
}
.det-cell {
    background: #f8f9fa;
    border-radius: 10px; padding: 12px 14px;
}
.det-cell-label {
    font-size: 10px; font-weight: 700;
    color: #9299a8; text-transform: uppercase;
    letter-spacing: .07em; margin-bottom: 5px;
}
.det-cell-val {
    font-size: 13.5px; font-weight: 600; color: #1a1a2e;
}

.det-notes {
    border: 1px solid #e8e8ed;
    border-left: 3px solid #1a1a2e;
    border-radius: 10px; padding: 14px 16px;
    margin-bottom: 16px;
    background: #fafafa;
}
.det-notes-label {
    font-size: 10px; font-weight: 700;
    color: #9299a8; text-transform: uppercase;
    letter-spacing: .07em; margin-bottom: 8px;
}
.det-notes-text {
    font-size: 13px; color: #374151;
    line-height: 1.7; white-space: pre-line;
}

.det-actions {
    display: flex; gap: 10px;
    padding: 0 24px 22px;
}
.det-btn {
    flex: 1; padding: 11px;
    border: 1.5px solid #e8e8ed;
    border-radius: 10px; background: #fff;
    font-size: 13px; font-weight: 600;
    cursor: pointer; color: #374151;
    display: flex; align-items: center;
    justify-content: center; gap: 7px;
    transition: all .12s;
}
.det-btn:hover { background: #f3f4f6; }
.det-btn--del  { color: #dc3545; border-color: #fde8e8; }
.det-btn--del:hover { background: #fde8e8; }
.det-btn--icon { flex: 0 0 44px; padding: 0; }
</style>
@endpush

@push('scripts')
<script>
const depenseModal   = new bootstrap.Modal('#depenseModal');
const depDetailModal = new bootstrap.Modal('#depDetailModal');

// ── TEXTAREA AUTO-RESIZE ──────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 260) + 'px';
}

// ── MODAL FORMULAIRE ─────────────────────────────────
function openDepenseModal(id = null) {
    const notes = document.getElementById('dep_notes');
    document.getElementById('dep_id').value        = '';
    document.getElementById('dep_libelle').value   = '';
    document.getElementById('dep_montant').value   = '';
    notes.value = ''; notes.style.height = 'auto';
    document.getElementById('dep_date').value      = '{{ today()->format("Y-m-d") }}';
    document.getElementById('dep_categorie').value = 'fournisseur';
    document.getElementById('depModalTitle').textContent =
        id ? 'Modifier la dépense' : 'Nouvelle dépense';
    document.getElementById('depModalSub').textContent =
        id ? 'Modifiez les champs souhaités' : 'Renseignez les informations';

    if (id) {
        fetch(`/depenses/${id}`, {
            headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            document.getElementById('dep_id').value        = d.id;
            document.getElementById('dep_libelle').value   = d.libelle;
            document.getElementById('dep_categorie').value = d.categorie;
            document.getElementById('dep_montant').value   = d.montant;
            document.getElementById('dep_date').value      = d.date_depense.slice(0,10);
            notes.value = d.notes ?? '';
            setTimeout(() => autoResize(notes), 60);
        });
    }
    depenseModal.show();
}

// ── SAUVEGARDER ──────────────────────────────────────
function saveDepense() {
    const id      = document.getElementById('dep_id').value;
    const libelle = document.getElementById('dep_libelle').value.trim();
    const montant = document.getElementById('dep_montant').value;
    const date    = document.getElementById('dep_date').value;

    if (!libelle || !montant || !date) {
        toastr.warning('Veuillez remplir les champs obligatoires.');
        return;
    }

    document.getElementById('depSaveTxt').classList.add('d-none');
    document.getElementById('depSaveSpin').classList.remove('d-none');

    fetch(id ? `/depenses/${id}` : '/depenses', {
        method:  id ? 'PUT' : 'POST',
        headers: {
            'Content-Type':'application/json', 'Accept':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            libelle,
            categorie:    document.getElementById('dep_categorie').value,
            montant,
            date_depense: date,
            notes:        document.getElementById('dep_notes').value.trim(),
        }),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('depSaveTxt').classList.remove('d-none');
        document.getElementById('depSaveSpin').classList.add('d-none');
        if (d.success) {
            depenseModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            toastr.error(d.message ?? 'Erreur.');
        }
    })
    .catch(() => {
        document.getElementById('depSaveTxt').classList.remove('d-none');
        document.getElementById('depSaveSpin').classList.add('d-none');
        toastr.error('Erreur réseau.');
    });
}

// ── MODAL DÉTAIL ─────────────────────────────────────
function showDetail(id) {
    document.getElementById('depDetailBody').innerHTML =
        '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';
    depDetailModal.show();

    fetch(`/depenses/${id}`, {
        headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        const ref    = '#DEP-' + String(d.id).padStart(4,'0');
        const dateFr = new Date(d.date_depense).toLocaleDateString('fr-FR',
            { weekday:'long', day:'2-digit', month:'long', year:'numeric' });
        const libEsc = d.libelle.replace(/'/g,"\\'");
        const notes  = d.notes
            ? d.notes.replace(/</g,'&lt;').replace(/>/g,'&gt;')
            : null;
        const montant = Number(d.montant).toLocaleString('fr');

        document.getElementById('depDetailBody').innerHTML = `
            <div class="rp-modal-header">
                <div>
                    <div class="rp-modal-title">Détail dépense</div>
                    <div class="rp-modal-sub">${ref}</div>
                </div>
                <button class="rp-close" onclick="depDetailModal.hide()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="det-hero">
                <div class="det-cat">
                    <span class="cat-pill">${ucfirstJs(d.categorie)}</span>
                </div>
                <div class="det-lib">${d.libelle}</div>
                <div class="det-price">${montant}<sub>FCFA</sub></div>
            </div>

            <div class="det-body">
                <div class="det-grid">
                    <div class="det-cell">
                        <div class="det-cell-label"><i class="bi bi-calendar3 me-1"></i>Date</div>
                        <div class="det-cell-val" style="font-size:12px">${dateFr}</div>
                    </div>
                    <div class="det-cell">
                        <div class="det-cell-label"><i class="bi bi-person me-1"></i>Saisi par</div>
                        <div class="det-cell-val">${d.user?.name ?? '—'}</div>
                    </div>
                    <div class="det-cell">
                        <div class="det-cell-label"><i class="bi bi-tag me-1"></i>Catégorie</div>
                        <div class="det-cell-val">${ucfirstJs(d.categorie)}</div>
                    </div>
                    <div class="det-cell">
                        <div class="det-cell-label"><i class="bi bi-hash me-1"></i>Référence</div>
                        <div class="det-cell-val" style="font-family:monospace;font-size:12px">${ref}</div>
                    </div>
                </div>

                ${notes ? `
                <div class="det-notes">
                    <div class="det-notes-label"><i class="bi bi-sticky me-1"></i>Notes</div>
                    <div class="det-notes-text">${notes}</div>
                </div>` : ''}
            </div>

            <div class="det-actions">
                <button class="det-btn"
                    onclick="depDetailModal.hide();openDepenseModal(${d.id})">
                    <i class="bi bi-pencil"></i>Modifier
                </button>
                <button class="det-btn det-btn--del"
                    onclick="depDetailModal.hide();deleteDepense(${d.id},'${libEsc}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
    });
}

function ucfirstJs(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// ── SUPPRIMER ─────────────────────────────────────────
function deleteDepense(id, libelle) {
    Swal.fire({
        title: 'Supprimer ?',
        html: `<span style="color:#6b7280"><strong>${libelle}</strong> sera supprimée définitivement.</span>`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#1a1a2e',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/depenses/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                toastr.success(d.message);
                const el = document.getElementById('dep-row-' + id);
                if (el) {
                    el.style.transition = 'opacity .22s, transform .22s';
                    el.style.opacity = '0';
                    el.style.transform = 'translateX(14px)';
                    setTimeout(() => el.remove(), 240);
                }
            } else {
                toastr.error(d.message);
            }
        })
        .catch(() => toastr.error('Erreur réseau.'));
    });
}
</script>
@endpush
