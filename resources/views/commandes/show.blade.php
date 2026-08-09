@extends('layouts.app')
@section('title', 'Commande '.$commande->numero)
@section('page_title', 'Commande '.$commande->numero)
@section('breadcrumb', 'Détail & modification')

@section('content')

@php
    $peutAnnuler = $commande->statut === 'en_attente' && !$commande->deja_envoyee_cuisine;
    $peutAjouter = in_array($commande->statut, ['en_attente', 'prete'], true);
    $peutModifierLigne = fn($item) => $item->statut === 'en_attente';
    $itemsParVague = $commande->items->groupBy('vague')->sortKeys();
    $plusieursVagues = $itemsParVague->count() > 1;
    $fmtQty = fn($q) => rtrim(rtrim(number_format((float) $q, 2, ',', ' '), '0'), ',');
    $peutGererCuisine = auth()->user() && (auth()->user()->hasRole('cuisinier') || auth()->user()->hasRole('admin'));
    $labels = [
        'en_attente'=>'En attente','en_cuisson'=>'En cuisson',
        'prete'=>'Prête','servie'=>'Servie',
        'payee'=>'Payée','annulee'=>'Annulée'
    ];
    $estLivraison = $commande->type === 'livraison';
@endphp

<div class="cmd-detail">

    {{-- INFO + ITEMS --}}
    <div class="cd-left">

        {{-- Infos générales --}}
        <div class="cd-card cd-info-card">
            <div class="cd-info-grid">
                <div class="cd-info-cell">
                    <div class="cd-info-lbl">Statut</div>
                    <span class="cd-status-badge badge-{{ $commande->statut }}">{{ $labels[$commande->statut] }}</span>
                    @if($plusieursVagues)
                    <div class="cd-info-sub"><i class="bi bi-layers"></i>{{ $itemsParVague->count() }} vagues</div>
                    @endif
                </div>
                <div class="cd-info-cell">
                    <div class="cd-info-lbl">Table</div>
                    <div class="cd-info-val">{{ $commande->table?->numero ?? ($estLivraison ? '—' : '—') }}</div>
                    <div class="cd-info-sub">
                        {{ $commande->type === 'sur_place' ? 'Sur place' : ($estLivraison ? 'Livraison' : 'Emporter') }}
                        @if($estLivraison && $commande->livreur)
                            · {{ $commande->livreur->name }}
                        @endif
                    </div>
                </div>
                <div class="cd-info-cell">
                    <div class="cd-info-lbl">Serveur</div>
                    <div class="cd-info-val cd-info-val-sm">{{ $commande->serveur->name }}</div>
                    <div class="cd-info-sub">{{ $commande->created_at->format('H:i') }}</div>
                </div>
                <div class="cd-info-cell">
                    <div class="cd-info-lbl">Total</div>
                    <div class="cd-info-total">{{ number_format($commande->total, 0, ',', ' ') }} F</div>
                    @if($estLivraison && $commande->frais_livraison > 0)
                    <div class="cd-info-sub"><i class="bi bi-bicycle"></i>dont {{ number_format($commande->frais_livraison, 0, ',', ' ') }} F livraison</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Motif d'annulation --}}
        @if($commande->statut === 'annulee' && $commande->motif_annulation)
        <div class="cd-card cd-motif-card">
            <i class="bi bi-x-circle"></i>
            <div>
                <div class="cd-motif-title">Motif d'annulation</div>
                <p class="cd-motif-text">{{ $commande->motif_annulation }}</p>
            </div>
        </div>
        @endif

        {{-- Articles --}}
        <div class="cd-card cd-items-card">
            <div class="cd-card-head">
                <span class="cd-card-head-title">Articles ({{ $commande->items->count() }})</span>
                @if($peutAjouter)
                <button class="cd-btn-add" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-lg"></i>
                    {{ $commande->statut === 'prete' ? 'Ajouter un complément' : 'Ajouter' }}
                </button>
                @else
                <span class="cd-locked-badge">
                    <i class="bi bi-lock-fill"></i>Modification indisponible
                </span>
                @endif
            </div>

            {{-- Table desktop --}}
            <div class="cd-table-wrap">
                <table class="cd-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Produit</th>
                            <th class="text-center">Qté</th>
                            <th class="text-end">Prix unit.</th>
                            <th class="text-end">Sous-total</th>
                            <th>Notes</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsParVague as $numVague => $items)
                            @if($plusieursVagues)
                            <tr class="cd-vague-row">
                                <td colspan="6">
                                    <i class="bi bi-arrow-down-circle"></i>
                                    {{ $numVague == 1 ? 'Commande initiale' : 'Ajout — vague '.$numVague }}
                                </td>
                            </tr>
                            @endif
                            @foreach($items as $item)
                            @php $editable = $peutModifierLigne($item); @endphp
                            <tr id="item-row-{{ $item->id }}" class="{{ !$editable && $item->statut === 'prete' ? 'cd-row-muted' : '' }}"
                                data-produit="{{ $item->produit->nom }}"
                                data-qte="{{ $fmtQty($item->quantite) }}"
                                data-pu="{{ number_format($item->prix_unitaire, 0, ',', ' ') }} F"
                                data-st="{{ number_format($item->sous_total, 0, ',', ' ') }} F"
                                data-notes="{{ $item->notes ?? '—' }}">
                                <td class="ps-4 fw-500">
                                    {{ $item->produit->nom }}
                                    @if($item->statut === 'prete' && $plusieursVagues)
                                        <i class="bi bi-check2-circle text-success ms-1" title="Déjà préparé"></i>
                                    @endif
                                </td>
                                <td class="text-center">{{ $fmtQty($item->quantite) }}</td>
                                <td class="text-end">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} F</td>
                                <td class="text-end cd-cell-total">{{ number_format($item->sous_total, 0, ',', ' ') }} F</td>
                                <td class="cd-cell-notes">{{ $item->notes ?? '—' }}</td>
                                <td>
                                    @if($editable)
                                    <button class="cd-btn-remove" onclick="retirerItem({{ $commande->id }}, {{ $item->id }})">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end text-muted ps-4">Sous-total</td>
                            <td class="text-end" colspan="2">{{ number_format($commande->sous_total, 0, ',', ' ') }} F</td>
                        </tr>
                        @if($commande->remise > 0)
                        <tr>
                            <td colspan="4" class="text-end text-muted">Remise</td>
                            <td class="text-end text-danger" colspan="2">-{{ number_format($commande->remise, 0, ',', ' ') }} F</td>
                        </tr>
                        @endif
                        @if($estLivraison && $commande->frais_livraison > 0)
                        <tr>
                            <td colspan="4" class="text-end text-muted"><i class="bi bi-bicycle me-1"></i>Frais de livraison</td>
                            <td class="text-end cd-cell-livraison" colspan="2">+{{ number_format($commande->frais_livraison, 0, ',', ' ') }} F</td>
                        </tr>
                        @endif
                        <tr class="cd-total-row">
                            <td colspan="4" class="text-end fw-bold">TOTAL</td>
                            <td class="text-end cd-cell-total-final" colspan="2">
                                {{ number_format($commande->total, 0, ',', ' ') }} F
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Totaux (visible aussi sur mobile, sous les cartes) --}}
            <div class="cd-mobile-totaux">
                <div class="cd-total-line">
                    <span>Sous-total</span>
                    <span>{{ number_format($commande->sous_total, 0, ',', ' ') }} F</span>
                </div>
                @if($commande->remise > 0)
                <div class="cd-total-line cd-total-line-danger">
                    <span>Remise</span>
                    <span>-{{ number_format($commande->remise, 0, ',', ' ') }} F</span>
                </div>
                @endif
                @if($estLivraison && $commande->frais_livraison > 0)
                <div class="cd-total-line cd-total-line-livraison">
                    <span><i class="bi bi-bicycle me-1"></i>Frais de livraison</span>
                    <span>+{{ number_format($commande->frais_livraison, 0, ',', ' ') }} F</span>
                </div>
                @endif
                <div class="cd-total-line cd-total-line-final">
                    <span>TOTAL</span>
                    <span>{{ number_format($commande->total, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="cd-right">
        <div class="cd-card cd-actions-card">
            <div class="cd-card-head">
                <span class="cd-card-head-title">Actions</span>
            </div>
            <div class="cd-actions-body">

                @if($commande->statut === 'servie')
                <button class="cd-btn-action cd-btn-success" onclick="changerStatut('payee')">
                    <i class="bi bi-check-circle"></i>Marquer payée
                </button>
                @endif

                @if($commande->statut === 'en_attente' && $peutGererCuisine)
                <button class="cd-btn-action cd-btn-info" onclick="changerStatut('en_cuisson')">
                    <i class="bi bi-fire"></i>Envoyer en cuisine
                </button>
                @elseif($commande->statut === 'en_attente')
                <div class="cd-alert">
                    <i class="bi bi-hourglass-split"></i>En attente de prise en charge par la cuisine.
                </div>
                @endif

                @if($peutAnnuler)
                <div class="cd-sep"></div>
                <button class="cd-btn-action cd-btn-danger-outline" onclick="annulerCommande()">
                    <i class="bi bi-x-circle"></i>Annuler la commande
                </button>
                @elseif(!in_array($commande->statut, ['payee','annulee']))
                <div class="cd-sep"></div>
                <div class="cd-alert cd-alert-center">
                    <i class="bi bi-lock-fill"></i>
                    {{ $commande->deja_envoyee_cuisine
                        ? 'Commande déjà transmise en cuisine, annulation impossible'
                        : 'Annulation indisponible à ce stade' }}
                </div>
                @endif

                <div class="cd-sep"></div>
                <a href="{{ route('commandes.index') }}" class="cd-btn-action cd-btn-ghost">
                    <i class="bi bi-arrow-left"></i>Retour à la liste
                </a>
            </div>
        </div>

        @if($commande->notes)
        <div class="cd-card cd-notes-card">
            <div class="cd-notes-title"><i class="bi bi-chat-left-text"></i>Notes</div>
            <p class="cd-notes-text">{{ $commande->notes }}</p>
        </div>
        @endif
    </div>
</div>

{{-- MODAL AJOUTER ARTICLE --}}
@if($peutAjouter)
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow cd-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    {{ $commande->statut === 'prete' ? 'Ajouter un complément (nouvelle vague)' : 'Ajouter des articles' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($commande->statut === 'prete')
                <div class="cd-modal-info">
                    <i class="bi bi-info-circle"></i>
                    Les plats déjà préparés resteront inchangés. Ce complément sera envoyé en cuisine comme une nouvelle demande.
                </div>
                @endif
                <div class="cd-modal-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="add-search" placeholder="Rechercher un produit..." oninput="filterAddProduits(this.value)">
                </div>
                <div class="row g-2" id="add-produits-grid">
                    @php
                        $tousProds = \App\Models\Produit::with('categorie')
                            ->where('disponible',true)->orderBy('ordre')->get();
                    @endphp
                    @foreach($tousProds as $p)
                    <div class="col-6 col-md-4 add-prod-item" data-search="{{ strtolower($p->nom) }}">
                        <div class="cd-prod-card" onclick="ajouterItemExistant({{ $p->id }}, '{{ addslashes($p->nom) }}', {{ $p->prix }})">
                            <div class="cd-prod-nom">{{ $p->nom }}</div>
                            <div class="cd-prod-prix">{{ number_format($p->prix, 0, ',', ' ') }} F</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div id="add-panier" class="cd-add-panier" hidden></div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn cd-btn-ghost-modal" data-bs-dismiss="modal">Annuler</button>
                <button class="cd-btn-confirm" id="addItemBtn" onclick="confirmerAjout()" disabled>
                    <span id="addTxt"><i class="bi bi-check2"></i>
                        {{ $commande->statut === 'prete' ? 'Envoyer en cuisine' : 'Ajouter au ticket' }}
                    </span>
                    <span id="addSpin" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

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

/* ══════════════════════════════════════
   LAYOUT
   ══════════════════════════════════════ */
.cmd-detail {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
.cd-left, .cd-right { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

.cd-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(20,20,31,.04), 0 6px 18px rgba(20,20,31,.05);
    overflow: hidden;
}

/* ── INFOS GÉNÉRALES ── */
.cd-info-card { padding: 22px 24px; }
.cd-info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}
.cd-info-cell {
    text-align: center;
    padding-left: 18px;
    border-left: 1px solid var(--border);
}
.cd-info-cell:first-child { border-left: none; padding-left: 0; }
.cd-info-lbl {
    font-size: 10.5px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px;
}
.cd-info-val { font-size: 19px; font-weight: 800; color: var(--ink); }
.cd-info-val-sm { font-size: 15px; }
.cd-info-total { font-size: 23px; font-weight: 800; color: var(--gold); font-variant-numeric: tabular-nums; }
.cd-info-sub { font-size: 11.5px; color: var(--muted); margin-top: 4px; font-weight: 500; }
.cd-info-sub i { margin-right: 3px; }

.cd-status-badge {
    display: inline-flex; align-items: center;
    padding: 6px 16px; border-radius: 30px;
    font-size: 13px; font-weight: 700; margin-top: 2px;
}
.badge-en_attente { background:#fff3cd; color:#856404; }
.badge-en_cuisson { background:#e0f0fb; color:#0c5e88; }
.badge-prete      { background:#d9f2e3; color:#0a7a45; }
.badge-servie     { background:#dbe8fe; color:#1d4ed8; }
.badge-payee      { background:var(--ink); color:#fff; }
.badge-annulee    { background:#fbdfe1; color:#b91c2c; }

/* ── MOTIF ANNULATION ── */
.cd-motif-card {
    padding: 18px 20px;
    background: #fdf2f2;
    border-color: #fbdfe1;
    display: flex; gap: 14px; align-items: flex-start;
}
.cd-motif-card i { color: #dc3545; font-size: 22px; flex-shrink: 0; margin-top: 2px; }
.cd-motif-title { font-weight: 800; color: #dc3545; font-size: 12.5px; text-transform: uppercase; letter-spacing: .05em; }
.cd-motif-text { margin: 6px 0 0; color: var(--ink2); font-size: 13.5px; }

/* ── CARD HEAD (commun) ── */
.cd-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px;
    border-bottom: 1px solid var(--border);
}
.cd-card-head-title { font-size: 14px; font-weight: 800; color: var(--ink); }

.cd-btn-add {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 15px; border-radius: 9px;
    border: 1.5px solid var(--ink); background: #fff; color: var(--ink);
    font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all .15s;
}
.cd-btn-add:hover { background: var(--ink); color: #fff; }

.cd-locked-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 20px;
    background: var(--bg); border: 1px solid var(--border); color: var(--muted);
    font-size: 11px; font-weight: 600;
}

/* ── TABLE ARTICLES ── */
.cd-table-wrap { overflow-x: auto; }
.cd-table { width: 100%; border-collapse: collapse; }
.cd-table thead th {
    padding: 11px 16px;
    font-size: 10.5px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .05em;
    background: #fbfbfd; border-bottom: 1px solid var(--border);
    white-space: nowrap; text-align: left;
}
.cd-table tbody td {
    padding: 13px 16px; font-size: 13.5px; color: var(--ink2);
    border-bottom: 1px solid #f2f3f6; vertical-align: middle;
}
.cd-table tbody tr:hover td { background: #fafbfd; }
.cd-row-muted td { color: var(--muted); }
.cd-vague-row td {
    background: #f4f8ff; padding: 9px 16px;
    font-size: 11px; font-weight: 700; color: #0d6efd;
    text-transform: uppercase; letter-spacing: .05em;
}
.cd-cell-total { font-weight: 800; color: var(--gold); }
.cd-cell-notes { font-size: 12px; color: var(--muted); }
.cd-cell-livraison { font-weight: 700; color: #0d9fd8; }
.cd-btn-remove {
    width: 28px; height: 28px; border-radius: 8px;
    border: 1px solid #f6d3d6; background: #fff; color: #dc3545;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; cursor: pointer; transition: all .15s;
}
.cd-btn-remove:hover { background: #fbdfe1; }

.cd-table tfoot td { padding: 11px 16px; font-size: 13px; border-top: 1px solid var(--border); }
.cd-total-row td { border-top: 1.5px solid var(--ink); }
.cd-cell-total-final { font-size: 17px; font-weight: 800; color: var(--gold); }

/* Totaux mobile — masqués sur desktop, affichés en dessous des cartes sur mobile */
.cd-mobile-totaux { display: none; }

/* ── ACTIONS ── */
.cd-actions-card { }
.cd-actions-body { padding: 18px 20px; display: flex; flex-direction: column; gap: 10px; }

.cd-btn-action {
    width: 100%; padding: 12px 16px; border-radius: 11px;
    font-size: 13.5px; font-weight: 700; cursor: pointer; text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    border: none; transition: all .18s;
}
.cd-btn-success { background: #198754; color: #fff; box-shadow: 0 5px 14px rgba(25,135,84,.25); }
.cd-btn-success:hover { background: #157347; transform: translateY(-1px); }
.cd-btn-info { background: #0d9fd8; color: #fff; box-shadow: 0 5px 14px rgba(13,159,216,.25); }
.cd-btn-info:hover { background: #0b87b8; transform: translateY(-1px); }
.cd-btn-danger-outline {
    background: #fff; border: 1.5px solid #f6d3d6; color: #dc3545;
}
.cd-btn-danger-outline:hover { background: #fbdfe1; }
.cd-btn-ghost {
    background: #fff; border: 1.5px solid var(--border); color: var(--ink2);
}
.cd-btn-ghost:hover { background: var(--bg); color: var(--ink); }

.cd-sep { height: 1px; background: var(--border); margin: 4px 0; }

.cd-alert {
    display: flex; align-items: center; gap: 8px;
    padding: 11px 14px; border-radius: 10px;
    background: var(--bg); border: 1px solid var(--border);
    font-size: 12px; color: var(--ink2); font-weight: 500;
}
.cd-alert-center { justify-content: center; text-align: center; }

/* ── NOTES ── */
.cd-notes-card { padding: 18px 20px; }
.cd-notes-title {
    display: flex; align-items: center; gap: 8px;
    font-weight: 800; color: var(--ink); font-size: 13px; margin-bottom: 8px;
}
.cd-notes-text { margin: 0; color: var(--ink2); font-size: 13px; line-height: 1.6; }

/* ══════════════════════════════════════
   MODAL AJOUT ARTICLE
   ══════════════════════════════════════ */
.cd-modal { border-radius: 18px; overflow: hidden; }
.cd-modal-info {
    display: flex; align-items: flex-start; gap: 9px;
    background: #e0f0fb; border: 1px solid #bfe3f7; border-radius: 10px;
    padding: 11px 14px; font-size: 12.5px; color: #0c5e88; margin-bottom: 14px;
}
.cd-modal-search {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg); border: 1.5px solid var(--border); border-radius: 10px;
    padding: 10px 14px; margin-bottom: 14px;
}
.cd-modal-search i { color: var(--muted); font-size: 13px; }
.cd-modal-search input {
    flex: 1; border: none; background: transparent; outline: none; font-size: 13.5px;
}

.cd-prod-card {
    background: #fff; border: 1.5px solid var(--border); border-radius: 12px;
    padding: 12px 10px; text-align: center; cursor: pointer;
    transition: transform .12s, border-color .12s, box-shadow .12s;
}
.cd-prod-card:hover { transform: translateY(-2px); border-color: var(--gold); box-shadow: 0 6px 16px rgba(201,169,110,.18); }
.cd-prod-nom { font-size: 13px; font-weight: 600; color: var(--ink); }
.cd-prod-prix { color: var(--gold); font-size: 12px; font-weight: 700; margin-top: 3px; }

.cd-add-panier {
    margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);
}
.cd-btn-ghost-modal {
    border: 1.5px solid var(--border); color: var(--ink2); font-weight: 600;
}
.cd-btn-confirm {
    padding: 10px 24px; border-radius: 10px; border: none;
    background: var(--ink); color: #fff; font-weight: 700; font-size: 13.5px;
    display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all .15s;
}
.cd-btn-confirm:hover:not(:disabled) { background: #000; }
.cd-btn-confirm:disabled { opacity: .4; cursor: not-allowed; }

/* ══════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════ */
@media (max-width: 1199.98px) {
    .cmd-detail { grid-template-columns: 1fr 300px; }
}

@media (max-width: 991.98px) {
    .cmd-detail { grid-template-columns: 1fr; }
    .cd-right { order: -1; } /* Actions visibles en premier sur mobile, avant les articles */
}

@media (max-width: 767.98px) {
    .cd-info-card { padding: 18px 16px; }
    .cd-info-grid { grid-template-columns: 1fr 1fr; row-gap: 20px; }
    .cd-info-cell:nth-child(3) { border-left: none; }
    .cd-info-cell:nth-child(1), .cd-info-cell:nth-child(3) { border-left: none; padding-left: 0; }
    .cd-info-cell:nth-child(2), .cd-info-cell:nth-child(4) { border-left: 1px solid var(--border); padding-left: 14px; }

    /* Table → cartes empilées */
    .cd-table thead { display: none; }
    .cd-table, .cd-table tbody, .cd-table tr, .cd-table td { display: block; width: 100%; }
    .cd-table tfoot { display: none; } /* remplacé par .cd-mobile-totaux */

    .cd-table tbody tr:not(.cd-vague-row) {
        border: 1px solid var(--border);
        border-radius: 12px;
        margin: 0 16px 10px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(20,20,31,.04);
    }
    .cd-table tbody tr:first-child { margin-top: 16px; }
    .cd-table tbody tr:hover td { background: transparent; }

    .cd-table tbody td {
        padding: 9px 14px;
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        text-align: right; border-bottom: 1px solid #f2f3f6;
    }
    .cd-table tbody td:last-child { border-bottom: none; }
    .cd-table tbody td::before {
        content: attr(data-label);
        font-size: 10px; font-weight: 700; color: var(--muted);
        text-transform: uppercase; letter-spacing: .05em; flex-shrink: 0;
    }
    .cd-table tbody td:nth-child(1)::before { content: 'Produit'; }
    .cd-table tbody td:nth-child(2)::before { content: 'Quantité'; }
    .cd-table tbody td:nth-child(3)::before { content: 'Prix unit.'; }
    .cd-table tbody td:nth-child(4)::before { content: 'Sous-total'; }
    .cd-table tbody td:nth-child(5)::before { content: 'Notes'; }
    .cd-table tbody td:nth-child(1) { text-align: left; font-weight: 700; }
    .cd-table tbody td:nth-child(6) { justify-content: flex-end; }
    .cd-table tbody td:nth-child(6)::before { display: none; }

    .cd-vague-row { margin: 0 16px 10px !important; border: none !important; box-shadow: none !important; }
    .cd-vague-row td { display: block !important; text-align: left !important; padding: 8px 4px !important; }
    .cd-vague-row td::before { content: '' !important; }

    .cd-mobile-totaux {
        display: block;
        margin: 4px 16px 18px;
        padding: 14px 16px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 12px;
    }
    .cd-total-line {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 13px; color: var(--muted); font-weight: 600; padding: 4px 0;
    }
    .cd-total-line-danger { color: #dc3545; }
    .cd-total-line-livraison { color: #0d9fd8; }
    .cd-total-line-final {
        margin-top: 8px; padding-top: 10px; border-top: 1px dashed var(--border);
        font-size: 15px; font-weight: 800; color: var(--ink);
    }
    .cd-total-line-final span:last-child { color: var(--gold); font-size: 18px; }

    .cd-card-head { padding: 14px 16px; }
}
</style>
@endpush

@push('scripts')
<script>
let addPanier = {};

function formatQty(q) {
    return parseFloat(q.toFixed(2)).toString();
}

function filterAddProduits(q) {
    const v = q.toLowerCase();
    document.querySelectorAll('.add-prod-item').forEach(el => {
        el.style.display = el.dataset.search.includes(v) ? '' : 'none';
    });
}

function ajouterItemExistant(id, nom, prix) {
    if (addPanier[id]) addPanier[id].quantite += 1;
    else addPanier[id] = { id, nom, prix, quantite: 1 };
    renderAddPanier();
}

function changerQuantiteAjout(id, delta) {
    if (!addPanier[id]) return;
    const nouvelle = Math.round((addPanier[id].quantite + delta) * 2) / 2;
    if (nouvelle < 0.5) {
        delete addPanier[id];
    } else {
        addPanier[id].quantite = nouvelle;
    }
    renderAddPanier();
}

function renderAddPanier() {
    const wrap = document.getElementById('add-panier');
    const ids  = Object.keys(addPanier);
    if (ids.length === 0) { wrap.hidden = true; document.getElementById('addItemBtn').disabled = true; return; }

    wrap.hidden = false;
    document.getElementById('addItemBtn').disabled = false;
    wrap.innerHTML = `<div class="fw-bold mb-2">À ajouter :</div>` + ids.map(id => {
        const it = addPanier[id];
        return `<div class="d-flex align-items-center gap-2 mb-1">
            <span class="flex-grow-1" style="font-size:13px">${it.nom}</span>
            <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changerQuantiteAjout(${id}, -0.5)">−</button>
            <span style="min-width:28px;text-align:center">${formatQty(it.quantite)}</span>
            <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changerQuantiteAjout(${id}, 0.5)">+</button>
            <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="delete addPanier[${id}];renderAddPanier()"><i class="bi bi-x"></i></button>
        </div>`;
    }).join('');
}

function confirmerAjout() {
    const ids = Object.keys(addPanier);
    if (!ids.length) return;

    document.getElementById('addTxt').classList.add('d-none');
    document.getElementById('addSpin').classList.remove('d-none');

    fetch('/commandes/{{ $commande->id }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            items: ids.map(id => ({
                produit_id: parseInt(id),
                quantite: addPanier[id].quantite,
            })),
        }),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('addTxt').classList.remove('d-none');
        document.getElementById('addSpin').classList.add('d-none');
        if (d.success) {
            toastr.success(d.message);
            bootstrap.Modal.getInstance('#addItemModal').hide();
            setTimeout(() => location.reload(), 600);
        } else {
            toastr.error(d.message);
            if (d.bloquee) setTimeout(() => location.reload(), 800);
        }
    });
}

function retirerItem(cmdId, itemId) {
    Swal.fire({
        title: 'Retirer cet article ?',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Non',
        confirmButtonText: 'Oui',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/commandes/${cmdId}/items/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        }).then(r => r.json()).then(d => {
            if (d.success) {
                toastr.success(d.message);
                document.getElementById(`item-row-${itemId}`)?.remove();
            } else {
                toastr.error(d.message);
                if (d.bloquee) setTimeout(() => location.reload(), 800);
            }
        });
    });
}

function changerStatut(statut) {
    fetch('/commandes/{{ $commande->id }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ statut }),
    }).then(r => r.json()).then(d => {
        if (d.success) { toastr.success(d.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(d.message);
    });
}

function annulerCommande() {
    Swal.fire({
        title: 'Annuler cette commande ?',
        html: `<label style="display:block;text-align:left;font-size:11px;font-weight:600;
                    color:#9299a8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">
                    Motif (facultatif)
                </label>`,
        input: 'textarea',
        inputPlaceholder: "Ex : erreur de saisie, client absent, rupture de stock...",
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Non',
        confirmButtonText: 'Oui, annuler',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('/commandes/{{ $commande->id }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ motif_annulation: (r.value || '').trim() }),
        }).then(r => r.json()).then(d => {
            if (d.success) {
                toastr.success(d.message);
                window.location.href = '/commandes';
            } else {
                toastr.error(d.message);
            }
        });
    });
}
</script>
@endpush