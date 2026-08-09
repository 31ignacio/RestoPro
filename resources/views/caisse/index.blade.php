@extends('layouts.app')
@section('title', 'Caisse')
@section('page_title', 'Interface Caisse')
@section('breadcrumb', 'Encaissement')

@section('content')

    {{-- ══ NAVIGATION ONGLETS ══ --}}
    <div class="caisse-nav mb-4">
        <button class="caisse-nav-btn active" id="tab-caisse-btn"
            onclick="switchCaisseTab('caisse', this)">
            <i class="bi bi-cash-register me-2"></i>Caisse
        </button>
        <button class="caisse-nav-btn" id="tab-historique-btn"
            onclick="switchCaisseTab('historique', this)">
            <i class="bi bi-clock-history me-2"></i>Historique
            <span class="badge bg-secondary ms-1">{{ $stats_jour['nb_encais'] }}</span>
        </button>
    </div>

    {{-- ══════════════════════════════════════
        ONGLET CAISSE
    ══════════════════════════════════════ --}}
    <div id="tab-caisse">

        {{-- STATS DU JOUR --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="bi bi-graph-up me-1"></i>CA du jour</div>
                    <div class="stat-value">
                        {{ number_format($stats_jour['ca'], 0, ',', ' ') }}
                        <small class="text-muted ms-1" style="font-size:13px">{{ $monnaie }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="bi bi-receipt me-1"></i>Encaissements</div>
                    <div class="stat-value">{{ $stats_jour['nb_encais'] }}</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="bi bi-cash me-1"></i>Espèces</div>
                    <div class="stat-value">
                        {{ number_format($stats_jour['especes'], 0, ',', ' ') }}
                        <small class="text-muted ms-1" style="font-size:13px">{{ $monnaie }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-label"><i class="bi bi-phone me-1"></i>Mobile Money</div>
                    <div class="stat-value">
                        {{ number_format($stats_jour['mobile'], 0, ',', ' ') }}
                        <small class="text-muted ms-1" style="font-size:13px">{{ $monnaie }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            {{-- ── LISTE COMMANDES PRÊTES ── --}}
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                        <span class="fw-bold">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Commandes prêtes
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <div class="live-dot-wrap">
                                <div class="live-dot"></div>
                                <span style="font-size:11px;color:#9299a8"></span>
                            </div>
                            <span class="badge bg-success rounded-pill" id="count-pretes">
                                {{ $commandes_pretes->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-2 overflow-auto" style="max-height:560px" id="list-pretes">
                        @forelse($commandes_pretes as $cmd)
                        <div class="commande-prete-item" id="prete-{{ $cmd->id }}"
                            onclick="selectionnerCommande({{ $cmd->id }})">
                            <div class="d-flex align-items-center gap-3">
                                <div class="prete-icon">
                                    <i class="bi bi-receipt"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-bold" style="font-size:14px">{{ $cmd->numero }}</div>
                                    <div style="font-size:12px;color:#9299a8">
                                        {{ $cmd->table ? 'Table '.$cmd->table->numero : ($cmd->type === 'livraison' ? 'Livraison' : 'Emporter') }}
                                        · {{ $cmd->items->count() }} article(s)
                                        @if($cmd->client) · {{ $cmd->client->nom }} @endif
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="fw-bold" style="color:#c9a96e;font-size:15px">
                                        {{ number_format($cmd->total, 0, ',', ' ') }} {{ $monnaie }}
                                    </div>
                                    <div class="prete-time" style="font-size:11px;color:#9299a8"
                                        data-prete-at="{{ $cmd->prete_at?->toIso8601String() }}">
                                        {{ $cmd->prete_at?->diffForHumans() ?? '—' }}
                                    </div>
                                </div>
                                <button type="button" class="tbl-action-btn flex-shrink-0"
                                    title="Imprimer la facture provisoire"
                                    onclick="imprimerFactureProvisoire({{ $cmd->id }}, event)">
                                    <i class="bi bi-printer"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted" id="empty-pretes">
                            <i class="bi bi-hourglass-split d-block fs-1 mb-2 opacity-25"></i>
                            <div style="font-size:14px">Aucune commande prête</div>
                            <small>Les commandes validées par la cuisine apparaîtront ici</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ── CAISSE POS ── --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header py-3 px-4 fw-bold d-flex align-items-center justify-content-between">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-cash-register"></i>Caisse
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="btn-facture-provisoire"
                            onclick="imprimerFactureProvisoireSelection()" disabled>
                            <i class="bi bi-printer me-1"></i>Facture provisoire
                        </button>
                    </div>
                    <div class="card-body">

                        {{-- État vide --}}
                        <div id="ticket-vide" class="ticket-vide-state">
                            <div class="tve-icon">💳</div>
                            <div class="tve-title">Sélectionnez une commande</div>
                            <div class="tve-sub">Cliquez sur une commande prête pour l'encaisser</div>
                        </div>

                        {{-- Ticket --}}
                        <div id="ticket-content" style="display:none">

                            {{-- Header ticket --}}
                            <div class="ticket-header mb-3">
                                <div class="th-left">
                                    <div class="th-num" id="ticket-numero">—</div>
                                    <div class="th-info" id="ticket-info">—</div>
                                </div>
                                <div class="th-right">
                                    <div class="th-total" id="ticket-total">0 F</div>
                                    <div class="th-count" id="ticket-items-count">0 article(s)</div>
                                </div>
                            </div>

                            {{-- Articles --}}
                            <div class="ticket-items-wrap mb-3" id="ticket-items"></div>

                            {{-- Sous-total / Remise / Frais livraison --}}
                            <div id="ticket-remise-wrap" style="display:none">
                                <div class="d-flex justify-content-between mb-1"
                                    style="font-size:13px;color:#9299a8">
                                    <span>Remise</span>
                                    <span id="ticket-remise-val" class="text-danger">—</span>
                                </div>
                            </div>
                            <div id="ticket-livraison-wrap" style="display:none">
                                <div class="d-flex justify-content-between mb-1"
                                    style="font-size:13px;color:#9299a8">
                                    <span><i class="bi bi-bicycle me-1"></i>Frais de livraison</span>
                                    <span id="ticket-livraison-val" style="color:#0d9fd8">—</span>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- Mode de paiement --}}
                            <div class="mb-3">
                                <div class="section-label mb-2">Mode de paiement</div>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="pay-btn active" data-mode="especes"
                                            onclick="selectMode('especes', this)">
                                            <i class="bi bi-cash"></i>
                                            <div>Espèces</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="pay-btn" data-mode="mobile_money"
                                            onclick="selectMode('mobile_money', this)">
                                            <i class="bi bi-phone"></i>
                                            <div>Mobile Money</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="pay-btn" data-mode="carte"
                                            onclick="selectMode('carte', this)">
                                            <i class="bi bi-credit-card"></i>
                                            <div>Carte</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Référence --}}
                            <div id="ref-wrap" class="mb-3" style="display:none">
                                <div class="section-label mb-1">Référence transaction</div>
                                <input type="text" id="ref-input" class="form-control"
                                    placeholder="N° transaction, code de confirmation...">
                            </div>

                            {{-- Montant reçu --}}
                            <div class="mb-3">
                                <div class="section-label mb-1">Montant reçu</div>
                                <div class="input-group input-group-lg">
                                    <input type="number" id="montant-recu"
                                        class="form-control montant-input"
                                        placeholder="0"
                                        oninput="calculerMonnaie()">
                                    <span class="input-group-text fw-bold">{{ $monnaie }}</span>
                                </div>
                            </div>

                            {{-- Raccourcis --}}
                            <div class="row g-2 mb-3" id="shortcuts"></div>

                            {{-- Monnaie --}}
                            <div id="monnaie-box" class="monnaie-box mb-3" style="display:none">
                                <div class="monnaie-label">MONNAIE À RENDRE</div>
                                <div class="monnaie-val" id="monnaie-rendue">0 F</div>
                            </div>

                            {{-- Bouton encaisser --}}
                            <button class="btn-encaisser-full" id="btn-encaisser"
                                onclick="encaisser()" disabled>
                                <span id="enc-txt">
                                    <i class="bi bi-check-circle me-2"></i>Encaisser
                                </span>
                                <div class="enc-spinner d-none" id="enc-spin"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
        ONGLET HISTORIQUE
    ══════════════════════════════════════ --}}
    <div id="tab-historique" style="display:none">

        {{-- Filtres --}}
        <div class="filtre-bar mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label fw-500 mb-1" style="font-size:11px;text-transform:uppercase;color:#9299a8">Du</label>
                    <input type="date" id="hist-debut" class="form-control form-control-sm"
                        value="{{ today()->format('Y-m-d') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-500 mb-1" style="font-size:11px;text-transform:uppercase;color:#9299a8">Au</label>
                    <input type="date" id="hist-fin" class="form-control form-control-sm"
                        value="{{ today()->format('Y-m-d') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-500 mb-1" style="font-size:11px;text-transform:uppercase;color:#9299a8">Mode</label>
                    <select id="hist-mode" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="especes">Espèces</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="carte">Carte</option>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <label class="form-label mb-1 d-block" style="font-size:11px;color:transparent">.</label>
                    <div class="d-flex gap-1">
                        @foreach(['today'=>"Auj.",'week'=>'Semaine','month'=>'Mois'] as $p => $l)
                        <button class="btn btn-sm btn-outline-secondary period-btn {{ $p==='today'?'active':'' }}"
                            data-period="{{ $p }}" onclick="setPeriodHist('{{ $p }}', this)">
                            {{ $l }}
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-auto ms-md-auto">
                    <label class="form-label mb-1 d-block" style="font-size:11px;color:transparent">.</label>
                    <button class="btn btn-dark btn-sm px-4" onclick="chargerHistorique()">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                </div>
            </div>
        </div>

        {{-- KPIs historique --}}
        <div class="row g-3 mb-4" id="hist-kpis">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">CA période</div>
                    <div class="stat-value" id="hist-ca">—</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Transactions</div>
                    <div class="stat-value" id="hist-nb">—</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Ticket moyen</div>
                    <div class="stat-value" id="hist-moy">—</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Mode dominant</div>
                    <div class="stat-value" id="hist-mode-dom">—</div>
                </div>
            </div>
        </div>

        {{-- Table historique --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                <span class="fw-bold">
                    <i class="bi bi-list-ul me-2"></i>Transactions
                </span>
                <span class="text-muted" style="font-size:13px" id="hist-count-label">—</span>
            </div>
            <div class="card-body p-0">
                <div id="hist-loading" class="text-center py-5">
                    <div class="spinner-border text-secondary"></div>
                    <div class="text-muted mt-2" style="font-size:13px">Chargement...</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 d-none" id="hist-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Commande</th>
                                <th>Client / Table</th>
                                <th>Mode</th>
                                <th class="text-end">Montant reçu</th>
                                <th class="text-end">Total dû</th>
                                <th class="text-end">Monnaie</th>
                                <th>Caissier</th>
                                <th>Heure</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="hist-tbody"></tbody>
                    </table>
                </div>
                <div id="hist-empty" class="empty-state d-none">
                    <i class="bi bi-receipt-cutoff"></i>
                    <h6>Aucune transaction</h6>
                    <p>Aucun encaissement sur cette période.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL SUCCÈS ══ --}}
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0">
                    <div class="success-layout">

                        {{-- Gauche : succès --}}
                        <div class="success-left">
                            <div class="success-check">✓</div>
                            <h4 class="fw-bold mb-1">Paiement réussi !</h4>
                            <div class="text-muted mb-1" id="success-num" style="font-size:14px">—</div>
                            <div class="success-total" id="success-total">—</div>
                            <div id="success-monnaie" class="mt-2"></div>
                            <div class="success-mode mt-2" id="success-mode"></div>

                            <div class="d-flex gap-2 mt-4 flex-wrap justify-content-center">
                                <button class="btn btn-outline-light btn-sm px-3"
                                    onclick="imprimerTicket()">
                                    <i class="bi bi-printer me-1"></i>Imprimer
                                </button>
                                <button class="btn btn-light btn-sm px-3"
                                    onclick="fermerSuccess()">
                                    <i class="bi bi-arrow-right me-1"></i>Suivant
                                </button>
                            </div>
                        </div>

                        {{-- Droite : ticket détaillé --}}
                        <div class="success-right" id="success-ticket-detail">
                            {{-- Rempli par JS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL DÉTAIL PAIEMENT ══ --}}
    <div class="modal fade" id="detailPaiementModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i>Détail du paiement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detail-paiement-body">
                    <div class="text-center py-4">
                        <div class="spinner-border text-secondary"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-dark" onclick="imprimerDepuisDetail()">
                        <i class="bi bi-printer me-1"></i>Imprimer la quittance
                    </button>
                    <button class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .fw-500 { font-weight: 500; }

        /* ── NAVIGATION ONGLETS ── */
        .caisse-nav {
            display: flex; gap: 6px;
            background: #fff; border: 1px solid #eaeaef;
            border-radius: 14px; padding: 6px;
            width: fit-content;
        }
        .caisse-nav-btn {
            padding: 9px 20px; border-radius: 10px;
            border: none; background: transparent;
            font-size: 13.5px; font-weight: 600;
            color: #9299a8; cursor: pointer;
            transition: all .15s; font-family: inherit;
        }
        .caisse-nav-btn.active {
            background: #212529; color: #fff;
        }
        .caisse-nav-btn:hover:not(.active) { background: #f5f5f7; color: #374151; }

        /* ── EN DIRECT ── */
        .live-dot-wrap { display: flex; align-items: center; gap: 4px; }
        .live-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #198754; animation: blink 1.5s infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

        /* ── COMMANDE PRÊTE ITEM ── */
        .commande-prete-item {
            padding: 12px 14px; border-radius: 12px;
            cursor: pointer; transition: all .15s;
            border: 2px solid transparent;
            margin-bottom: 6px; background: #f8f9fa;
        }
        .commande-prete-item:hover { background: #f0f0f5; }
        .commande-prete-item.active {
            background: rgba(201,169,110,.08);
            border-color: #c9a96e;
        }
        .commande-prete-item.nouvelle-commande {
            animation: nouvelleCommandePulse 1.8s ease-out 2;
            border-color: #198754;
        }
        @keyframes nouvelleCommandePulse {
            0%   { background: rgba(25,135,84,.20); }
            100% { background: #f8f9fa; }
        }
        .prete-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: #d1e7dd; color: #198754;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }

        /* ── TICKET ── */
        .ticket-vide-state {
            text-align: center; padding: 48px 20px;
            color: #9299a8;
        }
        .tve-icon  { font-size: 52px; margin-bottom: 14px; }
        .tve-title { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .tve-sub   { font-size: 13px; }

        .ticket-header {
            display: flex; align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #0f1117, #1a2035);
            border-radius: 12px; padding: 16px 20px;
            color: #fff;
        }
        .th-num   { font-size: 18px; font-weight: 800; letter-spacing: .5px; }
        .th-info  { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 2px; }
        .th-total { font-size: 24px; font-weight: 800; color: #c9a96e; text-align: right; }
        .th-count { font-size: 11px; color: rgba(255,255,255,.4); text-align: right; margin-top: 2px; }

        .ticket-items-wrap {
            background: #f8f9fa; border-radius: 10px; padding: 12px;
        }
        .ticket-item-line {
            display: flex; justify-content: space-between;
            align-items: center; padding: 6px 0;
            border-bottom: 1px dashed #eaeaef; font-size: 13px;
        }
        .ticket-item-line:last-child { border-bottom: none; }

        .section-label {
            font-size: 11px; font-weight: 600; color: #9299a8;
            text-transform: uppercase; letter-spacing: .06em;
        }

        /* ── PAIEMENT BUTTONS ── */
        .pay-btn {
            padding: 14px 8px; border: 2px solid #eaeaef;
            border-radius: 12px; text-align: center;
            cursor: pointer; transition: all .15s;
            font-size: 13px; font-weight: 600; color: #6b7280;
        }
        .pay-btn i { font-size: 22px; display: block; margin-bottom: 4px; }
        .pay-btn:hover { border-color: #c9a96e; color: #c9a96e; }
        .pay-btn.active {
            border-color: #c9a96e;
            background: rgba(201,169,110,.08);
            color: #c9a96e;
        }

        /* ── MONTANT INPUT ── */
        .montant-input {
            font-size: 22px !important; font-weight: 700 !important;
            text-align: right;
        }

        /* ── SHORTCUTS ── */
        .shortcut-btn {
            width: 100%; padding: 9px;
            border: 1px solid #eaeaef; border-radius: 9px;
            background: #fff; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: all .1s;
        }
        .shortcut-btn:hover { background: #212529; color: #fff; border-color: #212529; }

        /* ── MONNAIE BOX ── */
        .monnaie-box {
            border-radius: 12px; padding: 14px;
            text-align: center; transition: background .2s;
        }
        .monnaie-label { font-size: 11px; font-weight: 600; color: #6b7280; letter-spacing: .08em; text-transform: uppercase; }
        .monnaie-val   { font-size: 32px; font-weight: 800; color: #198754; margin-top: 4px; }

        /* ── BOUTON ENCAISSER ── */
        .btn-encaisser-full {
            width: 100%; padding: 16px;
            background: #212529; color: #fff;
            border: none; border-radius: 14px;
            font-size: 16px; font-weight: 700;
            cursor: pointer; transition: opacity .15s;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-encaisser-full:disabled { opacity: .5; cursor: not-allowed; }
        .btn-encaisser-full:not(:disabled):hover { background: #0f1117; }
        .enc-spinner {
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── MODAL SUCCÈS ── */
        .success-layout {
            display: flex; min-height: 400px;
        }
        .success-left {
            background: linear-gradient(135deg, #0f1117, #1a2035);
            color: #fff; padding: 40px 32px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; flex: 1;
            border-radius: 0.5rem 0 0 0.5rem;
        }
        .success-check {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(201,169,110,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 40px; color: #c9a96e; margin-bottom: 20px;
            border: 2px solid rgba(201,169,110,.3);
        }
        .success-total { font-size: 32px; font-weight: 800; color: #c9a96e; }
        .success-mode  {
            display: inline-block; padding: 4px 14px;
            background: rgba(255,255,255,.1); border-radius: 20px;
            font-size: 12px; color: rgba(255,255,255,.7);
        }

        .success-right {
            flex: 1; padding: 28px; overflow-y: auto;
            background: #fff; border-radius: 0 0.5rem 0.5rem 0;
        }

        /* ── TICKET PRINT AREA ── */
        .ticket-print {
            font-family: 'Courier New', monospace;
            max-width: 280px;
        }
        .ticket-print .tp-center { text-align: center; }
        .ticket-print .tp-sep { border-top: 1px dashed #dee2e6; margin: 8px 0; }
        .ticket-print .tp-line {
            display: flex; justify-content: space-between;
            font-size: 12px; margin: 2px 0;
        }
        .ticket-print .tp-total {
            display: flex; justify-content: space-between;
            font-size: 15px; font-weight: 700; margin: 4px 0;
        }
        .ticket-print .tp-item {
            display: flex; justify-content: space-between;
            align-items: flex-start;
            font-size: 12px; padding: 3px 0;
            border-bottom: 1px dotted #dee2e6;
        }
        .ticket-print .tp-item:last-child { border: none; }
        .ticket-print .tp-item .tp-item-pu {
            display: block; color: #9299a8; font-size: 10.5px;
        }
        .ticket-print .tp-provisoire-badge {
            background: #fff3cd; color: #7a5b00;
            border: 1px dashed #d9a441; border-radius: 6px;
            text-align: center; font-size: 11px; font-weight: 800;
            letter-spacing: .04em; text-transform: uppercase;
            padding: 6px 8px; margin-bottom: 8px;
        }

        /* ── HISTORIQUE ── */
        .filtre-bar {
            background: #fff; border: 1px solid #eaeaef;
            border-radius: 14px; padding: 16px 18px;
        }
        .empty-state {
            text-align: center; padding: 48px 20px; color: #9299a8;
        }
        .empty-state i { font-size: 48px; opacity: .2; display: block; margin-bottom: 12px; }
        .empty-state h6 { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; }

        /* MODE BADGE */
        .mode-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .mode-especes     { background: #d1e7dd; color: #0a3622; }
        .mode-mobile_money{ background: #cff4fc; color: #055160; }
        .mode-carte       { background: #cfe2ff; color: #084298; }

        /* ACTION BUTTONS */
        .tbl-action-btn {
            width: 30px; height: 30px; border-radius: 7px;
            border: 1px solid #eaeaef; background: #fff;
            display: inline-flex; align-items: center;
            justify-content: center; font-size: 13px;
            color: #6b7280; cursor: pointer; transition: all .12s;
        }
        .tbl-action-btn:hover { background: #f5f5f7; color: #1a1a2e; }
    </style>
@endpush

@push('scripts')
    <script>
        // ══════════════════════════════════════════════════════
        // CONFIG
        // ══════════════════════════════════════════════════════
        let selectedCmd  = null;
        let selectedMode = 'especes';
        let lastPaiement = null;
        let currentDetailPaiement = null;

        const MONNAIE  = '{{ $monnaie }}';
        const RESTO    = {
            nom:     '{{ \App\Models\Parametre::get("restaurant_nom","RestoPro") }}',
            adresse: '{{ \App\Models\Parametre::get("restaurant_adresse","") }}',
            tel:     '{{ \App\Models\Parametre::get("restaurant_tel","") }}',
            message: '{{ \App\Models\Parametre::get("ticket_message","Merci de votre visite !") }}',
        };

        const successModal       = new bootstrap.Modal('#successModal');
        const detailPaiementModal= new bootstrap.Modal('#detailPaiementModal');

        // Convertit n'importe quelle valeur en nombre fini, sinon 0.
        // Évite tout affichage "NaN" quand une donnée est absente, nulle ou mal formée.
        function num(x) {
            const n = Number(x);
            return Number.isFinite(n) ? n : 0;
        }

        // ── AJOUT : détecte si une commande a des frais de livraison à afficher ──
        function hasFraisLivraison(cmd) {
            return cmd && cmd.type === 'livraison' && num(cmd.frais_livraison) > 0;
        }

        // ══════════════════════════════════════════════════════
        // ONGLETS
        // ══════════════════════════════════════════════════════
        function switchCaisseTab(tab, btn) {
            document.querySelectorAll('.caisse-nav-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-caisse').style.display     = tab === 'caisse'     ? '' : 'none';
            document.getElementById('tab-historique').style.display = tab === 'historique' ? '' : 'none';
            if (tab === 'historique') chargerHistorique();
        }

        // ══════════════════════════════════════════════════════
        // CAISSE — COMMANDES PRÊTES EN TEMPS RÉEL (AJAX polling)
        // ══════════════════════════════════════════════════════
        let currentPretesIds = new Set(@json($commandes_pretes->pluck('id')));

        setInterval(actualiserCommandesPretes, 5000);
        setInterval(rafraichirTempsEcoule, 15000);
        rafraichirTempsEcoule();

        async function actualiserCommandesPretes() {
            try {
                const r = await fetch('/caisse/pretes', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) return;
                const cmds = await r.json();
                renderCommandesPretes(cmds);
            } catch (e) {}
        }

        function tempsEcoule(dateStr) {
            if (!dateStr) return '—';
            const date = new Date(dateStr);
            if (Number.isNaN(date.getTime())) return '—';
            const diffMin = Math.floor((Date.now() - date.getTime()) / 60000);
            if (diffMin < 1)  return "à l'instant";
            if (diffMin < 60) return `il y a ${diffMin} min`;
            const diffH = Math.floor(diffMin / 60);
            if (diffH < 24) return `il y a ${diffH} h`;
            return `il y a ${Math.floor(diffH / 24)} j`;
        }

        function rafraichirTempsEcoule() {
            document.querySelectorAll('.prete-time').forEach(el => {
                const val = el.dataset.preteAt;
                el.textContent = val ? tempsEcoule(val) : '—';
            });
        }

        function jouerNotificationSonore() {
            try {
                const ctx  = new (window.AudioContext || window.webkitAudioContext)();
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.value = 880;
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
            } catch (e) {}
        }

        function renderCommandesPretes(cmds) {
            const newIds = new Set(cmds.map(c => c.id));

            const inchangee = newIds.size === currentPretesIds.size
                && [...newIds].every(id => currentPretesIds.has(id));
            if (inchangee) return;

            const nouvelles = [...newIds].filter(id => !currentPretesIds.has(id));
            currentPretesIds = newIds;

            document.getElementById('count-pretes').textContent = cmds.length;

            const container = document.getElementById('list-pretes');

            if (cmds.length === 0) {
                container.innerHTML = `
                <div class="text-center py-5 text-muted" id="empty-pretes">
                    <i class="bi bi-hourglass-split d-block fs-1 mb-2 opacity-25"></i>
                    <div style="font-size:14px">Aucune commande prête</div>
                    <small>Les commandes validées par la cuisine apparaîtront ici</small>
                </div>`;
                return;
            }

            container.innerHTML = cmds.map(cmd => `
                <div class="commande-prete-item ${nouvelles.includes(cmd.id) ? 'nouvelle-commande' : ''}"
                    id="prete-${cmd.id}" onclick="selectionnerCommande(${cmd.id})">
                    <div class="d-flex align-items-center gap-3">
                        <div class="prete-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold" style="font-size:14px">${cmd.numero}</div>
                            <div style="font-size:12px;color:#9299a8">
                                ${cmd.table ? 'Table ' + cmd.table.numero : (cmd.type === 'livraison' ? 'Livraison' : 'Emporter')}
                                · ${cmd.items_count ?? (cmd.items ? cmd.items.length : 0)} article(s)
                                ${cmd.client ? ' · ' + cmd.client.nom : ''}
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold" style="color:#c9a96e;font-size:15px">
                                ${num(cmd.total).toLocaleString('fr')} ${MONNAIE}
                            </div>
                            <div class="prete-time" style="font-size:11px;color:#9299a8"
                                data-prete-at="${cmd.prete_at ?? ''}">${tempsEcoule(cmd.prete_at)}</div>
                        </div>
                        <button type="button" class="tbl-action-btn flex-shrink-0"
                            title="Imprimer la facture provisoire"
                            onclick="imprimerFactureProvisoire(${cmd.id}, event)">
                            <i class="bi bi-printer"></i>
                        </button>
                    </div>
                </div>`).join('');

            if (selectedCmd && newIds.has(selectedCmd.id)) {
                document.getElementById(`prete-${selectedCmd.id}`)?.classList.add('active');
            }

            if (nouvelles.length > 0) jouerNotificationSonore();
        }

        // ══════════════════════════════════════════════════════
        // SÉLECTIONNER UNE COMMANDE
        // ══════════════════════════════════════════════════════
        function selectionnerCommande(id) {
            document.querySelectorAll('.commande-prete-item')
                .forEach(el => el.classList.remove('active'));
            document.getElementById(`prete-${id}`)?.classList.add('active');

            document.getElementById('ticket-vide').style.display    = 'none';
            document.getElementById('ticket-content').style.display = '';
            document.getElementById('ticket-items').innerHTML =
                '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';
            document.getElementById('btn-facture-provisoire').disabled = true;

            fetch(`/commandes/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(cmd => {
                selectedCmd = cmd;

                document.getElementById('ticket-numero').textContent = cmd.numero;
                document.getElementById('ticket-info').textContent   =
                    (cmd.table ? 'Table ' + cmd.table.numero : (cmd.type === 'livraison' ? 'Livraison' : 'Emporter')) +
                    ' · ' + cmd.items.length + ' article(s)' +
                    (cmd.client ? ' · ' + cmd.client.nom : '') +
                    (cmd.type === 'livraison' && cmd.livreur ? ' · ' + cmd.livreur.name : '');
                document.getElementById('ticket-total').textContent  =
                    num(cmd.total).toLocaleString('fr') + ' ' + MONNAIE;
                document.getElementById('ticket-items-count').textContent =
                    cmd.items.length + ' article(s)';

                // Articles (quantité + prix unitaire + sous-total)
                document.getElementById('ticket-items').innerHTML =
                    '<div class="ticket-items-wrap">' +
                    cmd.items.map(it => {
                        const q  = num(it.quantite);
                        const st = num(it.sous_total);
                        const pu = it.prix_unitaire != null ? num(it.prix_unitaire) : (q > 0 ? st / q : 0);
                        return `
                        <div class="ticket-item-line">
                            <div>
                                <span class="fw-bold me-2">${q}×</span>
                                ${it.produit?.nom ?? ''}
                                <small class="text-muted d-block">
                                    ${num(pu).toLocaleString('fr')} ${MONNAIE} / unité
                                </small>
                                ${it.notes ? `<small class="text-muted d-block fst-italic">${it.notes}</small>` : ''}
                            </div>
                            <div class="fw-bold">${st.toLocaleString('fr')} ${MONNAIE}</div>
                        </div>`;
                    }).join('') +
                    '</div>';

                // Remise
                if (num(cmd.remise) > 0) {
                    document.getElementById('ticket-remise-wrap').style.display = '';
                    document.getElementById('ticket-remise-val').textContent =
                        '-' + num(cmd.remise).toLocaleString('fr') + ' ' + MONNAIE;
                } else {
                    document.getElementById('ticket-remise-wrap').style.display = 'none';
                }

                // ── AJOUT : Frais de livraison ──
                if (hasFraisLivraison(cmd)) {
                    document.getElementById('ticket-livraison-wrap').style.display = '';
                    document.getElementById('ticket-livraison-val').textContent =
                        '+' + num(cmd.frais_livraison).toLocaleString('fr') + ' ' + MONNAIE;
                } else {
                    document.getElementById('ticket-livraison-wrap').style.display = 'none';
                }

                buildShortcuts(cmd.total);

                // Reset
                document.getElementById('montant-recu').value = '';
                document.getElementById('ref-input').value    = '';
                document.getElementById('monnaie-box').style.display = 'none';
                document.getElementById('btn-encaisser').disabled = true;
                document.getElementById('btn-facture-provisoire').disabled = false;
            })
            .catch(err => {
                toastr.error('Erreur chargement commande : ' + err.message);
                document.getElementById('ticket-vide').style.display    = '';
                document.getElementById('ticket-content').style.display = 'none';
                document.getElementById('btn-facture-provisoire').disabled = true;
            });
        }

        function buildShortcuts(total) {
            const t = num(total);
            const arr = t % 1000 === 0
                ? [t, t + 500, t + 1000, t + 2000]
                : [Math.ceil(t/500)*500, Math.ceil(t/1000)*1000,
                    Math.ceil(t/1000)*1000+1000, Math.ceil(t/2000)*2000];

            document.getElementById('shortcuts').innerHTML =
                [...new Set(arr)].slice(0,4).map(v => `
                <div class="col-3">
                    <button class="shortcut-btn" onclick="setMontant(${v})">
                        ${num(v).toLocaleString('fr')} ${MONNAIE}
                    </button>
                </div>`).join('');
        }

        function setMontant(val) {
            document.getElementById('montant-recu').value = val;
            calculerMonnaie();
        }

        function selectMode(mode, el) {
            selectedMode = mode;
            document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('ref-wrap').style.display =
                (mode === 'mobile_money' || mode === 'carte') ? '' : 'none';
            calculerMonnaie();
        }

        function calculerMonnaie() {
            if (!selectedCmd) return;
            const recu    = num(document.getElementById('montant-recu').value);
            const total   = num(selectedCmd.total);
            const monnaie = Math.max(0, recu - total);
            const box     = document.getElementById('monnaie-box');

            if (recu > 0) {
                box.style.display = '';
                document.getElementById('monnaie-rendue').textContent =
                    monnaie.toLocaleString('fr') + ' ' + MONNAIE;
                box.style.background = monnaie >= 0 ? '#d1fae5' : '#fee2e2';
                document.getElementById('monnaie-rendue').style.color =
                    monnaie >= 0 ? '#198754' : '#dc3545';
            } else {
                box.style.display = 'none';
            }

            document.getElementById('btn-encaisser').disabled = (recu < total);
        }

        // ══════════════════════════════════════════════════════
        // FACTURE PROVISOIRE (avant paiement — n'affecte pas le statut)
        // ══════════════════════════════════════════════════════
        function buildTicketProvisoireHTML(cmd) {
            const items = (cmd.items || []).map(it => {
                const q  = num(it.quantite);
                const st = num(it.sous_total);
                const pu = it.prix_unitaire != null ? num(it.prix_unitaire) : (q > 0 ? st / q : 0);
                return `
                <div class="tp-item">
                    <span>${q}× ${it.produit?.nom ?? ''}
                        <span class="tp-item-pu">${num(pu).toLocaleString('fr')} ${MONNAIE} / unité</span>
                    </span>
                    <span>${st.toLocaleString('fr')} ${MONNAIE}</span>
                </div>`;
            }).join('');

            return `
            <div class="ticket-print" id="ticket-printable-provisoire">
                <div class="tp-provisoire-badge">Facture provisoire</div>
                <div class="tp-center mb-2">
                    <div style="font-size:16px;font-weight:800">${RESTO.nom}</div>
                    ${RESTO.adresse ? `<div style="font-size:11px;color:#6b7280">${RESTO.adresse}</div>` : ''}
                    ${RESTO.tel     ? `<div style="font-size:11px;color:#6b7280">${RESTO.tel}</div>` : ''}
                </div>
                <div class="tp-sep"></div>
                <div class="tp-line">
                    <span>Commande</span>
                    <span style="font-weight:700">${cmd.numero}</span>
                </div>
                ${cmd.table
                    ? `<div class="tp-line"><span>Table</span><span>${cmd.table.numero}</span></div>`
                    : `<div class="tp-line"><span>Type</span><span>${cmd.type === 'livraison' ? 'Livraison' : 'Emporter'}</span></div>`}
                ${cmd.type === 'livraison' && cmd.livreur ? `<div class="tp-line"><span>Livreur</span><span>${cmd.livreur.name}</span></div>` : ''}
                ${cmd.client ? `<div class="tp-line"><span>Client</span><span>${cmd.client.nom}</span></div>` : ''}
                <div class="tp-line">
                    <span>Date</span>
                    <span>${new Date().toLocaleDateString('fr')}</span>
                </div>
                <div class="tp-line">
                    <span>Heure</span>
                    <span>${new Date().toLocaleTimeString('fr',{hour:'2-digit',minute:'2-digit'})}</span>
                </div>
                <div class="tp-sep"></div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                    color:#9299a8;margin-bottom:6px">Articles</div>
                ${items}
                <div class="tp-sep"></div>
                ${num(cmd.remise) > 0 ? `
                <div class="tp-line">
                    <span>Remise</span>
                    <span>-${num(cmd.remise).toLocaleString('fr')} ${MONNAIE}</span>
                </div>` : ''}
                ${hasFraisLivraison(cmd) ? `
                <div class="tp-line">
                    <span>Frais de livraison</span>
                    <span>+${num(cmd.frais_livraison).toLocaleString('fr')} ${MONNAIE}</span>
                </div>` : ''}
                <div class="tp-total">
                    <span>TOTAL À PAYER</span>
                    <span style="color:#c9a96e">${num(cmd.total).toLocaleString('fr')} ${MONNAIE}</span>
                </div>
                <div class="tp-sep"></div>
                <div class="tp-center" style="font-size:11px;color:#dc3545;font-weight:700;margin-top:8px;line-height:1.4">
                    Document non définitif.<br>À remettre à la caisse pour paiement.
                </div>
                <div class="tp-center" style="font-size:12px;color:#6b7280;margin-top:6px">
                    ${RESTO.message}
                </div>
            </div>`;
        }

        function imprimerFactureProvisoire(id, event) {
            if (event) event.stopPropagation();

            fetch(`/commandes/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(cmd => {
                _imprimer(buildTicketProvisoireHTML(cmd), 'provisoire ' + cmd.numero);
            })
            .catch(err => toastr.error('Erreur chargement facture provisoire : ' + err.message));
        }

        function imprimerFactureProvisoireSelection() {
            if (!selectedCmd) {
                toastr.warning('Sélectionnez une commande.');
                return;
            }
            _imprimer(buildTicketProvisoireHTML(selectedCmd), 'provisoire ' + selectedCmd.numero);
        }

        // ══════════════════════════════════════════════════════
        // ENCAISSER
        // ══════════════════════════════════════════════════════
        function encaisser() {
            if (!selectedCmd) return;
            const recu = num(document.getElementById('montant-recu').value);
            if (!recu || recu < num(selectedCmd.total)) {
                toastr.warning('Montant insuffisant.');
                return;
            }

            document.getElementById('enc-txt').classList.add('d-none');
            document.getElementById('enc-spin').classList.remove('d-none');
            document.getElementById('btn-encaisser').disabled = true;

            fetch(`/caisse/encaisser/${selectedCmd.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    mode:         selectedMode,
                    montant_recu: recu,
                    reference:    document.getElementById('ref-input').value || null,
                }),
            })
            .then(r => r.json())
            .then(d => {
                document.getElementById('enc-txt').classList.remove('d-none');
                document.getElementById('enc-spin').classList.add('d-none');

                if (d.success) {
                    lastPaiement = d;

                    // ── Remplir le modal succès ──
                    const modeLabels = {
                        especes:'💵 Espèces', mobile_money:'📱 Mobile Money',
                        carte:'💳 Carte bancaire', mixte:'🔀 Mixte'
                    };

                    document.getElementById('success-num').textContent   = d.commande_num;
                    document.getElementById('success-total').textContent =
                        num(d.total).toLocaleString('fr') + ' ' + MONNAIE;
                    document.getElementById('success-mode').textContent  =
                        modeLabels[d.mode] ?? d.mode;
                    document.getElementById('success-monnaie').innerHTML = num(d.monnaie_rendue) > 0
                        ? `<div style="background:rgba(255,255,255,.1);border-radius:10px;padding:10px 14px;margin-top:8px">
                            <div style="font-size:11px;color:rgba(255,255,255,.5)">MONNAIE À RENDRE</div>
                            <div style="font-size:24px;font-weight:800;color:#4ade80">
                                ${num(d.monnaie_rendue).toLocaleString('fr')} ${MONNAIE}
                            </div>
                            </div>`
                        : '';

                    // ── Ticket détaillé côté droit (facture finale) ──
                    document.getElementById('success-ticket-detail').innerHTML =
                        buildTicketHTML(d);

                    successModal.show();

                    // Retirer de la liste
                    document.getElementById(`prete-${selectedCmd.id}`)?.remove();
                    currentPretesIds.delete(selectedCmd.id);
                    document.getElementById('ticket-content').style.display = 'none';
                    document.getElementById('ticket-vide').style.display    = '';
                    document.getElementById('btn-facture-provisoire').disabled = true;

                    const countEl = document.getElementById('count-pretes');
                    countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);

                    selectedCmd = null;

                } else {
                    document.getElementById('btn-encaisser').disabled = false;
                    toastr.error(d.message);
                }
            })
            .catch(err => {
                document.getElementById('enc-txt').classList.remove('d-none');
                document.getElementById('enc-spin').classList.add('d-none');
                document.getElementById('btn-encaisser').disabled = false;
                toastr.error('Erreur réseau : ' + err.message);
            });
        }

        // ══════════════════════════════════════════════════════
        // TICKET HTML (FACTURE FINALE — après paiement)
        // ══════════════════════════════════════════════════════
        function buildTicketHTML(d) {
            const modeLabels = {
                especes:'Espèces', mobile_money:'Mobile Money',
                carte:'Carte bancaire', mixte:'Mixte'
            };
            const items = (d.items || []).map(it => {
                const q  = num(it.quantite);
                const st = num(it.sous_total);
                const pu = it.prix_unitaire != null ? num(it.prix_unitaire) : (q > 0 ? st / q : 0);
                return `
                <div class="tp-item">
                    <span>${q}× ${it.nom ?? ''}
                        <span class="tp-item-pu">${num(pu).toLocaleString('fr')} ${MONNAIE} / unité</span>
                    </span>
                    <span>${st.toLocaleString('fr')} ${MONNAIE}</span>
                </div>`;
            }).join('');

            return `
            <div class="ticket-print" id="ticket-printable">
                <div class="tp-center mb-2">
                    <div style="font-size:16px;font-weight:800">${RESTO.nom}</div>
                    ${RESTO.adresse ? `<div style="font-size:11px;color:#6b7280">${RESTO.adresse}</div>` : ''}
                    ${RESTO.tel     ? `<div style="font-size:11px;color:#6b7280">${RESTO.tel}</div>` : ''}
                </div>
                <div class="tp-sep"></div>
                <div class="tp-line">
                    <span>Commande</span>
                    <span style="font-weight:700">${d.commande_num}</span>
                </div>
                ${d.table ? `<div class="tp-line"><span>Table</span><span>${d.table}</span></div>` : ''}
                ${d.type === 'livraison' ? `<div class="tp-line"><span>Type</span><span>Livraison</span></div>` : ''}
                ${d.livreur ? `<div class="tp-line"><span>Livreur</span><span>${d.livreur}</span></div>` : ''}
                ${d.client ? `<div class="tp-line"><span>Client</span><span>${d.client}</span></div>` : ''}
                <div class="tp-line">
                    <span>Date</span>
                    <span>${new Date().toLocaleDateString('fr')}</span>
                </div>
                <div class="tp-line">
                    <span>Heure</span>
                    <span>${new Date().toLocaleTimeString('fr',{hour:'2-digit',minute:'2-digit'})}</span>
                </div>
                <div class="tp-sep"></div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                    color:#9299a8;margin-bottom:6px">Articles</div>
                ${items}
                <div class="tp-sep"></div>
                ${num(d.remise) > 0 ? `
                <div class="tp-line">
                    <span>Remise</span>
                    <span>-${num(d.remise).toLocaleString('fr')} ${MONNAIE}</span>
                </div>` : ''}
                ${d.type === 'livraison' && num(d.frais_livraison) > 0 ? `
                <div class="tp-line">
                    <span>Frais de livraison</span>
                    <span>+${num(d.frais_livraison).toLocaleString('fr')} ${MONNAIE}</span>
                </div>` : ''}
                <div class="tp-total">
                    <span>TOTAL</span>
                    <span style="color:#c9a96e">
                        ${num(Number(d.total || 0) + Number(d.frais_livraison || 0)).toLocaleString('fr')} ${MONNAIE}
                    </span>
                </div>
                <div class="tp-line" style="margin-top:4px">
                    <span>Mode paiement</span>
                    <span style="font-weight:600">${modeLabels[d.mode] ?? d.mode}</span>
                </div>
                <div class="tp-line">
                    <span>Montant reçu</span>
                    <span>${num(d.montant_recu ?? d.total).toLocaleString('fr')} ${MONNAIE}</span>
                </div>
                ${num(d.monnaie_rendue) > 0 ? `
                <div class="tp-line" style="color:#198754;font-weight:700">
                    <span>Monnaie rendue</span>
                    <span>${num(Number(d.monnaie_rendue|| 0) - Number(d.frais_livraison || 0)).toLocaleString('fr')} ${MONNAIE}</span>
                </div>` : ''}
                <div class="tp-sep"></div>
                <div class="tp-center" style="font-size:12px;color:#6b7280;margin-top:6px">
                    ${RESTO.message}
                </div>
            </div>`;
        }

        function fermerSuccess() {
            successModal.hide();
        }

        // ══════════════════════════════════════════════════════
        // IMPRESSION
        // ══════════════════════════════════════════════════════
        function imprimerTicket() {
            _imprimer(document.getElementById('ticket-printable')?.innerHTML || '', lastPaiement?.commande_num);
        }

        function imprimerDepuisDetail() {
            _imprimer(document.getElementById('ticket-printable-detail')?.innerHTML || '', currentDetailPaiement?.commande?.numero);
        }

        function _imprimer(innerHTML, titre = '') {
            if (!innerHTML) { toastr.warning('Aucun ticket disponible.'); return; }
            const win = window.open('', '_blank',
                'width=380,height=650,toolbar=no,location=no,menubar=no,status=no,scrollbars=yes,resizable=yes');
            win.document.write(`<!DOCTYPE html><html><head>
                <meta charset="UTF-8"><title>Ticket ${titre}</title>
                <style>
                    *{margin:0;padding:0;box-sizing:border-box}
                    body{font-family:'Courier New',monospace;font-size:13px;
                        width:300px;margin:0 auto;padding:16px 12px;background:#fff}
                    .tp-center{text-align:center}
                    .tp-sep{border-top:1px dashed #999;margin:8px 0}
                    .tp-line{display:flex;justify-content:space-between;margin:3px 0;font-size:12px}
                    .tp-total{display:flex;justify-content:space-between;font-size:16px;
                        font-weight:700;margin:5px 0}
                    .tp-item{display:flex;justify-content:space-between;align-items:flex-start;
                        font-size:12px;padding:4px 0;border-bottom:1px dotted #ccc}
                    .tp-item:last-child{border:none}
                    .tp-item-pu{display:block;color:#9299a8;font-size:10.5px}
                    .tp-provisoire-badge{background:#fff3cd;color:#7a5b00;
                        border:1px dashed #d9a441;border-radius:6px;text-align:center;
                        font-size:11px;font-weight:800;letter-spacing:.04em;
                        text-transform:uppercase;padding:6px 8px;margin-bottom:8px}
                    @page{margin:5mm}
                    @media print{body{width:auto}}
                </style>
                </head><body>${innerHTML}</body></html>`);
            win.document.close();
            setTimeout(() => { win.print(); setTimeout(() => win.close(), 500); }, 400);
        }

        // ══════════════════════════════════════════════════════
        // HISTORIQUE
        // ══════════════════════════════════════════════════════
        function setPeriodHist(period, btn) {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const today = new Date();
            const fmt   = d => d.toISOString().split('T')[0];
            const debut = document.getElementById('hist-debut');
            const fin   = document.getElementById('hist-fin');

            if (period === 'today') {
                debut.value = fin.value = fmt(today);
            } else if (period === 'week') {
                const mon = new Date(today);
                mon.setDate(today.getDate() - today.getDay() + 1);
                debut.value = fmt(mon); fin.value = fmt(today);
            } else if (period === 'month') {
                debut.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
                fin.value   = fmt(today);
            }
        }

        async function chargerHistorique() {
            const debut = document.getElementById('hist-debut').value;
            const fin   = document.getElementById('hist-fin').value;
            const mode  = document.getElementById('hist-mode').value;

            document.getElementById('hist-loading').style.display = '';
            document.getElementById('hist-table').classList.add('d-none');
            document.getElementById('hist-empty').classList.add('d-none');

            try {
                const params = new URLSearchParams({ debut, fin });
                if (mode) params.set('mode', mode);

                const r    = await fetch(`/caisse/historique?${params}&json=1`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await r.json();

                document.getElementById('hist-loading').style.display = 'none';

                if (!data.paiements || data.paiements.length === 0) {
                    document.getElementById('hist-empty').classList.remove('d-none');
                    _updateHistKpis(data.stats);
                    return;
                }

                const modeLabels = {
                    especes:'💵 Espèces', mobile_money:'📱 Mobile Money',
                    carte:'💳 Carte', mixte:'🔀 Mixte'
                };
                const modeCls = {
                    especes:'mode-especes', mobile_money:'mode-mobile_money',
                    carte:'mode-carte', mixte:'mode-especes'
                };

                document.getElementById('hist-tbody').innerHTML = data.paiements.map(p => `
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold" style="font-size:13px">${p.commande?.numero ?? '—'}</div>
                        </td>
                        <td>
                            <div style="font-size:13px">${p.commande?.table ? 'Table '+p.commande.table.numero : (p.commande?.type === 'livraison' ? 'Livraison' : 'Emporter')}</div>
                            <div style="font-size:11px;color:#9299a8">${p.commande?.client?.nom ?? '—'}</div>
                        </td>
                        <td>
                            <span class="mode-badge ${modeCls[p.mode] ?? ''}">
                                ${modeLabels[p.mode] ?? p.mode}
                            </span>
                            ${p.reference ? `<div style="font-size:10px;color:#9299a8">${p.reference}</div>` : ''}
                        </td>
                        <td class="text-end fw-bold">${num(p.montant_recu).toLocaleString('fr')} ${MONNAIE}</td>
                        <td class="text-end fw-bold" style="color:#c9a96e">
                            ${num(Number(p.montant_du || 0) + Number(p.commande?.frais_livraison || 0)).toLocaleString('fr')} ${MONNAIE}
                        </td>
                        <td class="text-end" style="color:${num(p.monnaie_rendue - p.commande.frais_livraison ) > 0 ? '#198754' : '#9299a8'}">
                            ${num(p.monnaie_rendue) > 0 ? num(p.monnaie_rendue - p.commande.frais_livraison).toLocaleString('fr')+' '+MONNAIE : '—'}
                        </td>
                        <td style="font-size:12px">${p.caissier?.name ?? '—'}</td>
                        <td style="font-size:12px;color:#9299a8">
                            ${new Date(p.created_at).toLocaleString('fr',{hour:'2-digit',minute:'2-digit'})}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <button class="tbl-action-btn" onclick="voirDetailPaiement(${p.id})" title="Voir détail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="tbl-action-btn" onclick="imprimerPaiement(${p.id})" title="Imprimer">
                                    <i class="bi bi-printer"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`).join('');

                document.getElementById('hist-table').classList.remove('d-none');
                document.getElementById('hist-count-label').textContent =
                    data.paiements.length + ' transaction(s)';

                _updateHistKpis(data.stats, data.paiements);

            } catch(e) {
                document.getElementById('hist-loading').style.display = 'none';
                document.getElementById('hist-empty').classList.remove('d-none');
                toastr.error('Erreur chargement historique.');
            }
        }

        function _updateHistKpis(stats, paiements = []) {
            const ca  = num(stats?.total ?? paiements.reduce((s,p) => s + num(p.montant_du), 0));
            const nb  = num(stats?.count ?? paiements.length);
            const moy = nb > 0 ? ca / nb : 0;

            document.getElementById('hist-ca').textContent  = ca.toLocaleString('fr') + ' ' + MONNAIE;
            document.getElementById('hist-nb').textContent  = nb;
            document.getElementById('hist-moy').textContent = num(moy).toLocaleString('fr') + ' ' + MONNAIE;

            if (paiements.length > 0) {
                const modes = {};
                paiements.forEach(p => { modes[p.mode] = (modes[p.mode] ?? 0) + 1; });
                const dom = Object.entries(modes).sort((a,b) => b[1]-a[1])[0];
                const modeLabels = { especes:'Espèces', mobile_money:'Mobile Money', carte:'Carte' };
                document.getElementById('hist-mode-dom').textContent =
                    modeLabels[dom?.[0]] ?? dom?.[0] ?? '—';
            }
        }

        // ══════════════════════════════════════════════════════
        // DÉTAIL PAIEMENT
        // ══════════════════════════════════════════════════════
        function voirDetailPaiement(id) {
            document.getElementById('detail-paiement-body').innerHTML =
                '<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>';
            detailPaiementModal.show();

            fetch(`/caisse/historique/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(p => {
                currentDetailPaiement = p;

                const modeLabels = { especes:'Espèces', mobile_money:'Mobile Money', carte:'Carte', mixte:'Mixte' };
                const modeCls    = { especes:'mode-especes', mobile_money:'mode-mobile_money', carte:'mode-carte' };

                const ticketData = {
                    commande_num:   p.commande.numero,
                    total:          p.montant_du,
                    monnaie_rendue: p.monnaie_rendue,
                    mode:           p.mode,
                    montant_recu:   p.montant_recu,
                    table:          p.commande.table?.numero,
                    type:           p.commande.type,
                    livreur:        p.commande.livreur?.name,
                    client:         p.commande.client?.nom,
                    remise:         p.commande.remise,
                    frais_livraison:p.commande.frais_livraison,
                    items:          (p.commande.items ?? []).map(i => ({
                        nom:           i.produit.nom,
                        quantite:      i.quantite,
                        prix_unitaire: i.prix_unitaire,
                        sous_total:    i.sous_total,
                    })),
                };

                const ticketHtml = buildTicketHTML(ticketData)
                    .replace('id="ticket-printable"', 'id="ticket-printable-detail"');

                document.getElementById('detail-paiement-body').innerHTML = `
                    <div class="row g-4">
                        <div class="col-md-5">
                            {{-- Infos paiement --}}
                            <div style="background:#f8f9fa;border-radius:12px;padding:16px;margin-bottom:14px">
                                <div style="font-size:11px;color:#9299a8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
                                    Paiement #${p.id}
                                </div>
                                <div style="font-size:22px;font-weight:800;color:#c9a96e;margin-bottom:12px">
                                    ${num(p.montant_du).toLocaleString('fr')} ${MONNAIE}
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Commande</span>
                                        <span class="fw-bold">${p.commande.numero}</span>
                                    </div>
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Table / Type</span>
                                        <span>${p.commande.table ? 'Table '+p.commande.table.numero : (p.commande.type === 'livraison' ? 'Livraison' : 'Emporter')}</span>
                                    </div>
                                    ${p.commande.type === 'livraison' && p.commande.livreur ? `
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Livreur</span>
                                        <span>${p.commande.livreur.name}</span>
                                    </div>` : ''}
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Client</span>
                                        <span>${p.commande.client?.nom ?? '—'}</span>
                                    </div>
                                    ${p.commande.type === 'livraison' && num(p.commande.frais_livraison) > 0 ? `
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Frais de livraison</span>
                                        <span style="color:#0d9fd8;font-weight:600">+${num(p.commande.frais_livraison).toLocaleString('fr')} ${MONNAIE}</span>
                                    </div>` : ''}
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Mode</span>
                                        <span class="mode-badge ${modeCls[p.mode] ?? ''}">${modeLabels[p.mode] ?? p.mode}</span>
                                    </div>
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Montant reçu</span>
                                        <span class="fw-bold">${num(p.montant_recu).toLocaleString('fr')} ${MONNAIE}</span>
                                    </div>
                                    ${num(p.monnaie_rendue) > 0 ? `
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Monnaie rendue</span>
                                        <span class="fw-bold text-success">${num(p.monnaie_rendue - p.commande.frais_livraison).toLocaleString('fr')} ${MONNAIE}</span>
                                    </div>` : ''}
                                    ${p.reference ? `
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Référence</span>
                                        <span>${p.reference}</span>
                                    </div>` : ''}
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Caissier</span>
                                        <span>${p.caissier?.name ?? '—'}</span>
                                    </div>
                                    <div class="d-flex justify-content-between" style="font-size:13px">
                                        <span class="text-muted">Date/Heure</span>
                                        <span>${new Date(p.created_at).toLocaleString('fr')}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            {{-- Ticket visuel --}}
                            <div style="background:#f8f9fa;border-radius:12px;padding:16px;
                                font-family:'Courier New',monospace">
                                ${ticketHtml}
                            </div>
                        </div>
                    </div>`;
            });
        }

        function imprimerPaiement(id) {
            fetch(`/caisse/historique/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(p => {
                const ticketData = {
                    commande_num:   p.commande.numero,
                    total:          p.montant_du,
                    monnaie_rendue: p.monnaie_rendue,
                    mode:           p.mode,
                    montant_recu:   p.montant_recu,
                    table:          p.commande.table?.numero,
                    type:           p.commande.type,
                    livreur:        p.commande.livreur?.name,
                    client:         p.commande.client?.nom,
                    remise:         p.commande.remise,
                    frais_livraison:p.commande.frais_livraison,
                    items:          (p.commande.items ?? []).map(i => ({
                        nom:           i.produit.nom,
                        quantite:      i.quantite,
                        prix_unitaire: i.prix_unitaire,
                        sous_total:    i.sous_total,
                    })),
                };
                _imprimer(buildTicketHTML(ticketData), p.commande.numero);
            })
            .catch(() => toastr.error('Erreur chargement du paiement.'));
        }
    </script>
@endpush