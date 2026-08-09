@extends('layouts.app')
@section('title', 'Nouvelle commande')
@section('page_title', 'Nouvelle commande')
@section('breadcrumb', 'Prise de commande')

@section('content')

<div class="cmd-builder">

    {{-- ══ COLONNE GAUCHE : Menu ══ --}}
    <div class="cb-left">

        {{-- En-tête commande --}}
       <div class="cb-card cb-flex-none">
    <div class="cb-card-body">
        <div class="row gx-2 gy-3">

            <div class="col-6 col-md-4">
                <label class="cb-label">
                    <i class="bi bi-receipt"></i> Type
                </label>
                <select id="cmd_type" class="form-select form-select-sm" onchange="toggleTable()">
                    <option value="sur_place">🪑 Sur place</option>
                    <option value="emporter">🛍 À emporter</option>
                    <option value="livraison">🚴 Livraison</option>
                </select>
            </div>

            <div class="col-6 col-md-4" id="table-wrap">
                <label class="cb-label">
                    <i class="bi bi-grid"></i> Table
                </label>
                <select id="cmd_table" class="form-select form-select-sm select2-table">
                    <option value="">-- Choisir une table --</option>
                    @foreach($tables as $t)
                        <option value="{{ $t->id }}"
                            {{ $t->statut !== 'libre' ? 'disabled' : '' }}
                            data-statut="{{ $t->statut }}">
                            Table {{ $t->numero }}
                            {{ $t->nom ? '· '.$t->nom : '' }}
                            · {{ $t->capacite }} pers.
                            {{ $t->statut !== 'libre' ? '(occupée)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="cb-label">
                    <i class="bi bi-person"></i> Client
                </label>
                <select id="cmd_client" class="form-select form-select-sm select2-client">
                    <option value="">-- Aucun --</option>
                    @foreach($clients as $cl)
                        <option value="{{ $cl->id }}">
                            {{ $cl->nom }}{{ $cl->telephone ? ' ('.$cl->telephone.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12" id="livreur-wrap" style="display:none">
                <div class="row gx-2 gy-3">

                    <div class="col-md-6">
                        <label for="cmd_livreur" class="cb-label">
                            <i class="bi bi-bicycle"></i> Livreur
                        </label>

                        <select id="cmd_livreur" class="form-select form-select-sm select2-livreur">
                            <option value="">-- Aucun livreur --</option>
                            @foreach(
                                App\Models\User::with('role')
                                    ->whereHas('role', fn($q) => $q->where('nom', 'serveur'))
                                    ->orderBy('name')
                                    ->get()
                                as $livreur
                            )
                                <option value="{{ $livreur->id }}">
                                    {{ $livreur->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="cmd_frais_livraison" class="cb-label">
                            <i class="bi bi-truck"></i> Frais de livraison
                        </label>

                        <input
                            type="number"
                            id="cmd_frais_livraison"
                            class="form-control form-control-sm"
                            value="0"
                            min="0"
                            placeholder="Frais livraison"
                        >
                    </div>

                </div>
            </div>

            <div class="col-12">
                <label class="cb-label">
                    <i class="bi bi-person-badge"></i> Cuisinier assigné
                </label>

                <select id="cmd_cuisinier" class="form-select form-select-sm select2-cuisinier">
                    <option value="">-- Aucune attribution --</option>
                    @foreach(
                        App\Models\User::with('role')
                            ->whereHas('role', fn($q) => $q->where('nom','cuisinier'))
                            ->orderBy('name')
                            ->get()
                        as $cook)
                        <option value="{{ $cook->id }}">
                            {{ $cook->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
</div>

        {{-- Recherche + filtre catégorie --}}
        <div class="cb-card cb-flex-none">
            <div class="cb-card-body cb-card-body-sm">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0"
                                id="search-produit"
                                placeholder="Rechercher un plat..."
                                oninput="filterProduits(this.value)">
                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                onclick="document.getElementById('search-produit').value='';filterProduits('')">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select id="filter-categorie" class="form-select form-select-sm select2-cat">
                            <option value="tous">📂 Toutes les catégories</option>
                            @foreach($produits as $catNom => $prods)
                            <option value="{{ Str::slug($catNom) }}">{{ $catNom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grille produits --}}
        <div class="cb-produits-grid" id="produits-grid">
            <div class="row g-2">
                @foreach($produits as $catNom => $prods)
                    @foreach($prods as $p)
                    <div class="col-6 col-md-4 col-xl-3 prod-item"
                        data-cat="{{ Str::slug($catNom) }}"
                        data-search="{{ strtolower($p->nom) }}">
                        <div class="prod-card"
                            onclick="ajouterAuPanier({{ $p->id }}, '{{ addslashes($p->nom) }}', {{ $p->prix }})">
                            <div class="prod-card-img">
                                <img src="{{ $p->photo_url }}" alt="{{ $p->nom }}" loading="lazy">
                                <div class="prod-card-overlay">
                                    <i class="bi bi-plus-circle-fill"></i>
                                </div>
                            </div>
                            <div class="prod-card-body">
                                <div class="prod-nom">{{ $p->nom }}</div>
                                <div class="prod-prix">{{ number_format($p->prix, 0, ',', ' ') }} F</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endforeach
                <div class="col-12 d-none" id="no-results">
                    <div class="cb-empty">
                        <i class="bi bi-search"></i>
                        <span>Aucun produit trouvé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ COLONNE DROITE : Panier ══ --}}
    <div class="cb-right">
        <div class="cb-panier-card">

            {{-- Header panier --}}
            <div class="cb-panier-head">
                <span class="cb-panier-title">
                    <i class="bi bi-cart3"></i>Panier
                    <span class="cb-panier-badge" id="panier-count" style="display:none">0</span>
                </span>
                <button class="cb-btn-vider" onclick="viderPanier()">
                    <i class="bi bi-trash"></i>Vider
                </button>
            </div>

            {{-- Liste articles --}}
            <div class="cb-panier-list" id="panier-list">
                <div id="panier-empty" class="cb-empty cb-empty-panier">
                    <i class="bi bi-cart-x"></i>
                    <span>Panier vide</span>
                    <small>Cliquez sur un produit pour l'ajouter</small>
                </div>
            </div>

            {{-- Footer panier --}}
            <div class="cb-panier-footer">

                {{-- Notes cuisine --}}
                <div class="cb-footer-block">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-chat-left-text text-muted"></i>
                        </span>
                        <input type="text" id="cmd_notes" class="form-control border-start-0"
                            placeholder="Notes pour la cuisine...">
                    </div>
                </div>

                {{-- Remise --}}
                <div class="cb-footer-block cb-footer-block-tight">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-tag text-muted"></i>
                        </span>
                        <input type="number" id="cmd_remise" class="form-control border-start-0"
                            value="0" min="0" placeholder="Remise"
                            oninput="recalculerTotal()">
                        <span class="input-group-text">F</span>
                    </div>
                </div>

                {{-- Totaux --}}
                <div class="cb-totaux">
                    <div class="cb-total-row cb-total-muted">
                        <span>Sous-total</span>
                        <span id="display-sous-total">0 F</span>
                    </div>
                    <div class="cb-total-row cb-total-danger" style="display:none!important" id="remise-row">
                        <span>Remise</span>
                        <span id="display-remise">-0 F</span>
                    </div>
                    <div class="cb-total-row cb-total-muted" style="display:none!important" id="frais-livraison-row">
                        <span>Frais livraison</span>
                        <span id="display-frais-livraison">0 F</span>
                    </div>
                    <div class="cb-total-final">
                        <span>TOTAL</span>
                        <span id="display-total">0 F</span>
                    </div>
                </div>

                {{-- Bouton envoyer --}}
                <div class="cb-footer-block">
                    <button class="cb-btn-envoyer" id="btn-envoyer" onclick="envoyerCommande()" disabled>
                        <span id="btn-envoyer-txt">
                            <i class="bi bi-send-fill"></i>Envoyer à la cuisine
                        </span>
                        <span id="btn-envoyer-spin" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
{{-- Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
   LAYOUT PRINCIPAL — responsive par défaut
   ══════════════════════════════════════ */
.cmd-builder {
    display: grid;
    grid-template-columns: 1.55fr 1fr;
    gap: 16px;
    align-items: stretch;
    height: calc(100vh - 140px);
    min-height: 560px;
}
.cb-left, .cb-right {
    display: flex;
    flex-direction: column;
    min-height: 0; /* essentiel pour que overflow-y fonctionne dans un flex/grid item */
    height: 100%;
}
.cb-flex-none { flex-shrink: 0; }

/* ── CARTES (générique) ── */
.cb-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 1px 2px rgba(20,20,31,.04), 0 4px 14px rgba(20,20,31,.04);
    margin-bottom: 12px;
}
.cb-card-body { padding: 14px 16px; }
.cb-card-body-sm { padding: 10px 16px; }
.cb-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .04em;
    margin-bottom: 6px;
}
.cb-label i { font-size: 11px; color: var(--gold); }

.form-select-sm, .form-control-sm {
    border-color: #e2e4ea;
    font-size: 13px;
}
.form-select-sm:focus, .form-control-sm:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,169,110,.12);
}

/* ── GRILLE PRODUITS ── */
.cb-produits-grid {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding-right: 4px;
}
.cb-produits-grid::-webkit-scrollbar { width: 5px; }
.cb-produits-grid::-webkit-scrollbar-thumb { background: #e2e4ea; border-radius: 4px; }

.prod-card {
    background: #fff;
    border: 1.5px solid #eaeaef;
    border-radius: 13px;
    cursor: pointer;
    transition: all .15s;
    overflow: hidden;
    height: 100%;
}
.prod-card:active { transform: scale(.97); }
.prod-card:hover {
    border-color: var(--gold);
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(201,169,110,.22);
}
.prod-card-img {
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: #f5f5f7;
}
.prod-card-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .25s;
}
.prod-card:hover .prod-card-img img { transform: scale(1.06); }
.prod-card-overlay {
    position: absolute; inset: 0;
    background: rgba(201,169,110,.28);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .15s;
    font-size: 26px; color: #fff;
}
.prod-card:hover .prod-card-overlay { opacity: 1; }
.prod-card-body { padding: 9px 11px; }
.prod-nom {
    font-size: 12.5px; font-weight: 700; color: var(--ink);
    line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.prod-prix { font-size: 13px; font-weight: 800; color: var(--gold); margin-top: 3px; }

/* ── ÉTAT VIDE générique ── */
.cb-empty {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 44px 16px; color: var(--muted); text-align: center;
}
.cb-empty i { font-size: 30px; opacity: .3; margin-bottom: 4px; }
.cb-empty span { font-size: 13.5px; font-weight: 600; color: var(--ink2); }
.cb-empty small { font-size: 12px; }
.cb-empty-panier { padding: 56px 16px; }

/* ══════════════════════════════════════
   PANIER (colonne droite)
   ══════════════════════════════════════ */
.cb-panier-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(20,20,31,.04), 0 6px 20px rgba(20,20,31,.06);
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
    overflow: hidden;
}

.cb-panier-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.cb-panier-title {
    display: flex; align-items: center; gap: 9px;
    font-size: 14.5px; font-weight: 800; color: var(--ink);
}
.cb-panier-title i { color: var(--muted); font-size: 15px; }
.cb-panier-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 20px; padding: 0 6px;
    border-radius: 10px; background: var(--ink); color: #fff;
    font-size: 11px; font-weight: 800;
}
.cb-btn-vider {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 8px;
    border: 1px solid #f6d3d6; background: #fff; color: #dc3545;
    font-size: 12px; font-weight: 700; cursor: pointer; transition: all .15s;
}
.cb-btn-vider:hover { background: #fbdfe1; }

.cb-panier-list {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 14px;
}
.cb-panier-list::-webkit-scrollbar { width: 5px; }
.cb-panier-list::-webkit-scrollbar-thumb { background: #e2e4ea; border-radius: 4px; }

.panier-item {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 11px 13px;
    margin-bottom: 9px;
    animation: slideIn .2s ease;
}
.panier-item:last-child { margin-bottom: 0; }
@keyframes slideIn {
    from { opacity:0; transform: translateY(-8px); }
    to   { opacity:1; transform: translateY(0); }
}
.panier-item-nom {
    font-size: 13px; font-weight: 700; color: var(--ink);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.panier-item-prix { font-size: 12.5px; color: var(--gold); font-weight: 800; }
.panier-item-prix .unit { font-weight: 500; color: var(--muted); font-size: 11px; }

.qty-btn {
    width: 27px; height: 27px;
    border-radius: 7px; border: 1px solid #dee2e6;
    background: #fff; font-size: 15px; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .12s; flex-shrink: 0;
    color: var(--ink2);
}
.qty-btn:hover { background: var(--ink); color: #fff; border-color: var(--ink); }
.qty-display { min-width: 24px; text-align: center; font-size: 14px; font-weight: 800; color: var(--ink); }

/* ── FOOTER PANIER ── */
.cb-panier-footer {
    flex-shrink: 0;
    border-top: 1px solid var(--border);
    background: #fff;
}
.cb-footer-block { padding: 12px 16px 0; }
.cb-footer-block-tight { padding-top: 8px; }

.cb-totaux { padding: 10px 16px 14px; }
.cb-total-row {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 12.5px; margin-bottom: 5px;
}
.cb-total-muted { color: var(--muted); font-weight: 600; }
.cb-total-danger { color: #dc3545; font-weight: 600; }
.cb-total-final {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border);
}
.cb-total-final span:first-child { font-size: 14.5px; font-weight: 800; color: var(--ink); }
.cb-total-final span:last-child { font-size: 21px; font-weight: 800; color: var(--gold); font-variant-numeric: tabular-nums; }

.cb-btn-envoyer {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 13px;
    background: var(--ink);
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s;
    box-shadow: 0 6px 18px rgba(20,20,31,.22);
    margin-bottom: 16px;
}
.cb-btn-envoyer:hover:not(:disabled) {
    background: #000;
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(20,20,31,.3);
}
.cb-btn-envoyer:disabled { opacity: .4; cursor: not-allowed; box-shadow: none; transform: none; }
.cb-btn-envoyer i { font-size: 14px; }

/* ── SELECT2 ── */
.select2-container--default .select2-selection--single {
    border: 1px solid #e2e4ea !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 2px 8px !important;
    font-size: 13px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 27px !important;
    color: #212529 !important;
    padding: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 30px !important; }
.select2-dropdown {
    border: 1px solid #e2e4ea !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 26px rgba(20,20,31,.14) !important;
    font-size: 13px !important;
}
.select2-container--default .select2-results__option--highlighted { background: var(--gold) !important; }
.select2-search--dropdown .select2-search__field {
    border-radius: 7px !important;
    border: 1px solid #e2e4ea !important;
    font-size: 13px !important;
}
.select2-container { width: 100% !important; }

/* ══════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════ */

/* Tablette : colonnes plus équilibrées */
@media (max-width: 1199.98px) {
    .cmd-builder { grid-template-columns: 1.3fr 1fr; }
}

/* Mobile / petite tablette : empilement vertical, plus de hauteur forcée */
@media (max-width: 991.98px) {
    .cmd-builder {
        display: flex;
        flex-direction: column;
        height: auto;
        min-height: 0;
    }
    .cb-left { height: auto; }
    .cb-produits-grid {
        max-height: 55vh;
        overflow-y: auto;
    }
    .cb-right { height: auto; }
    .cb-panier-card {
        height: auto;
        max-height: none;
    }
    .cb-panier-list {
        max-height: 320px;
    }
    /* Le bouton "Envoyer" reste toujours visible en bas de l'écran sur mobile */
    .cb-panier-footer {
        position: sticky;
        bottom: 0;
        z-index: 5;
    }
}

@media (max-width: 575.98px) {
    .cb-card-body { padding: 12px 14px; }
    .prod-card-body { padding: 8px 9px; }
    .prod-nom { font-size: 12px; }
    .cb-panier-list { max-height: 260px; }
}
</style>
@endpush

@push('scripts')
{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// ── ÉTAT ─────────────────────────────────────────────
let panier = {}; // { id: { id, nom, prix, quantite, notes } }

// ── INIT SELECT2 ─────────────────────────────────────
$(function () {
    $('#filter-categorie').select2({
        minimumResultsForSearch: Infinity,
        placeholder: '📂 Toutes les catégories',
    }).on('change', function () {
        showCat($(this).val());
    });

    $('#cmd_table').select2({
        placeholder: '-- Choisir une table --',
        allowClear: true,
    });

    $('#cmd_client').select2({
        placeholder: '-- Aucun client --',
        allowClear: true,
    });

    $('#cmd_cuisinier').select2({
        placeholder: '-- Aucune attribution --',
        allowClear: true,
    });

    $('#cmd_livreur').select2({
        placeholder: '-- Aucun livreur --',
        allowClear: true,
    });
});

// ── TOGGLE TABLE ──────────────────────────────────────
function toggleTable() {
    const type = document.getElementById('cmd_type').value;
    const wrap = document.getElementById('table-wrap');
    const livreurWrap = document.getElementById('livreur-wrap');
    wrap.style.display = type === 'sur_place' ? '' : 'none';
    livreurWrap.style.display = type === 'livraison' ? '' : 'none';
    if (type !== 'sur_place') {
        $('#cmd_table').val(null).trigger('change');
    }
    if (type !== 'livraison') {
        $('#cmd_livreur').val(null).trigger('change');
        document.getElementById('cmd_frais_livraison').value = '0';
    }
    recalculerTotal();
}

// ── FILTRE CATÉGORIE ──────────────────────────────────
function showCat(cat) {
    let visible = 0;
    document.querySelectorAll('.prod-item').forEach(el => {
        const match = (cat === 'tous' || el.dataset.cat === cat);
        el.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('no-results').classList.toggle('d-none', visible > 0);
}

// ── FILTRE RECHERCHE ──────────────────────────────────
function filterProduits(q) {
    const v = q.toLowerCase().trim();
    let visible = 0;
    document.querySelectorAll('.prod-item').forEach(el => {
        const match = !v || el.dataset.search.includes(v);
        el.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('no-results').classList.toggle('d-none', visible > 0);

    if (v) {
        $('#filter-categorie').val('tous').trigger('change.select2');
    }
}

// ── PANIER : AJOUTER ──────────────────────────────────
function ajouterAuPanier(id, nom, prix) {
    if (panier[id]) {
        panier[id].quantite++;
    } else {
        panier[id] = { id, nom, prix: parseFloat(prix), quantite: 1, notes: '' };
    }
    renderPanier();

    const card = event.currentTarget;
    card.style.transform = 'scale(0.95)';
    setTimeout(() => { card.style.transform = ''; }, 150);
}

// ── PANIER : CHANGER QUANTITÉ ─────────────────────────
function changerQuantite(id, delta) {
    id = String(id);
    if (!panier[id]) return;
    panier[id].quantite = Math.round((panier[id].quantite + delta) * 2) / 2;
    if (panier[id].quantite <= 0) {
        delete panier[id];
    }
    renderPanier();
}

// ── PANIER : RETIRER ──────────────────────────────────
function retirerItem(id) {
    id = String(id);
    const el = document.getElementById('pitem-' + id);
    if (el) {
        el.style.transition = 'all .2s ease';
        el.style.opacity    = '0';
        el.style.transform  = 'translateX(20px)';
        setTimeout(() => {
            delete panier[id];
            renderPanier();
        }, 200);
    } else {
        delete panier[id];
        renderPanier();
    }
}

// ── PANIER : VIDER ────────────────────────────────────
function viderPanier() {
    if (Object.keys(panier).length === 0) return;
    Swal.fire({
        title: 'Vider le panier ?',
        text: 'Tous les articles seront retirés.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Vider',
        buttonsStyling: true,
    }).then(r => {
        if (r.isConfirmed) {
            panier = {};
            renderPanier();
        }
    });
}

// ── PANIER : RENDER ───────────────────────────────────
function renderPanier() {
    const list  = document.getElementById('panier-list');
    const count = document.getElementById('panier-count');
    const btnEnvoyer = document.getElementById('btn-envoyer');

    const ids = Object.keys(panier);

    if (ids.length === 0) {
        list.innerHTML = `
            <div id="panier-empty" class="cb-empty cb-empty-panier">
                <i class="bi bi-cart-x"></i>
                <span>Panier vide</span>
                <small>Cliquez sur un produit pour l'ajouter</small>
            </div>`;
        count.style.display = 'none';
        btnEnvoyer.disabled = true;
        recalculerTotal();
        return;
    }

    const itemsHtml = ids.map(id => {
        const item = panier[id];
        const sous = (item.prix * item.quantite).toLocaleString('fr');
        const unit = item.prix.toLocaleString('fr');
        return `
        <div class="panier-item" id="pitem-${id}">
            <div class="d-flex align-items-center gap-2">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="panier-item-nom">${item.nom}</div>
                    <div class="panier-item-prix">
                        ${sous} F
                        <span class="unit">(${unit} × ${item.quantite})</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <button class="qty-btn" onclick="changerQuantite(${id}, -0.5)" title="Diminuer">−</button>
                    <div class="qty-display">${item.quantite}</div>
                    <button class="qty-btn" onclick="changerQuantite(${id}, 0.5)" title="Augmenter">+</button>
                    <button class="qty-btn ms-1"
                        style="border-color:#f8d7da;color:#dc3545"
                        onclick="retirerItem(${id})"
                        title="Retirer">
                        <i class="bi bi-x" style="font-size:14px"></i>
                    </button>
                </div>
            </div>
            <input type="text"
                class="form-control form-control-sm mt-2"
                style="font-size:12px;border-color:#e8e8ed"
                placeholder="Note (sans sel, bien cuit...)"
                value="${item.notes.replace(/"/g, '&quot;')}"
                oninput="panier['${id}'].notes = this.value">
        </div>`;
    }).join('');

    list.innerHTML = itemsHtml;

    const total_items = ids.reduce((s, id) => s + panier[id].quantite, 0);
    count.textContent   = total_items;
    count.style.display = '';

    btnEnvoyer.disabled = false;
    recalculerTotal();
}

// ── RECALCULER TOTAL ──────────────────────────────────
function recalculerTotal() {
    const sousTotal = Object.values(panier)
        .reduce((s, i) => s + i.prix * i.quantite, 0);
    const remise = parseFloat(document.getElementById('cmd_remise').value) || 0;
    const fraisLivraison = (document.getElementById('cmd_type').value === 'livraison')
        ? (parseFloat(document.getElementById('cmd_frais_livraison').value) || 0)
        : 0;
    const total  = Math.max(0, sousTotal + fraisLivraison - remise);

    document.getElementById('display-sous-total').textContent =
        sousTotal.toLocaleString('fr') + ' F';
    document.getElementById('display-total').textContent =
        total.toLocaleString('fr') + ' F';

    const fraisRow = document.getElementById('frais-livraison-row');
    if (fraisLivraison > 0) {
        fraisRow.style.display = 'flex';
        document.getElementById('display-frais-livraison').textContent =
            fraisLivraison.toLocaleString('fr') + ' F';
    } else {
        fraisRow.style.display = 'none';
    }

    const remiseRow = document.getElementById('remise-row');
    if (remise > 0) {
        remiseRow.style.setProperty('display', 'flex', 'important');
        document.getElementById('display-remise').textContent =
            '-' + remise.toLocaleString('fr') + ' F';
    } else {
        remiseRow.style.setProperty('display', 'none', 'important');
    }
}

// ── ENVOYER COMMANDE ──────────────────────────────────
function envoyerCommande() {
    const ids = Object.keys(panier);
    if (ids.length === 0) {
        toastr.warning('Le panier est vide.');
        return;
    }

    const type       = document.getElementById('cmd_type').value;
    const tableId    = $('#cmd_table').val();
    const clientId   = $('#cmd_client').val();
    const cuisinierId = $('#cmd_cuisinier').val();
    const livreurId  = $('#cmd_livreur').val();
    const fraisLivraison = parseFloat(document.getElementById('cmd_frais_livraison').value) || 0;

    if (type === 'sur_place' && !tableId) {
        toastr.warning('Veuillez sélectionner une table.');
        return;
    }

    const payload = {
        type,
        table_id:     tableId  || null,
        client_id:    clientId || null,
        cuisinier_id: cuisinierId || null,
        livreur_id:   livreurId || null,
        frais_livraison: fraisLivraison,
        notes:        document.getElementById('cmd_notes').value.trim(),
        remise:    parseFloat(document.getElementById('cmd_remise').value) || 0,
        items: ids.map(id => ({
            produit_id: parseInt(id),
            quantite:   panier[id].quantite,
            notes:      panier[id].notes,
        })),
    };

    const btnTxt  = document.getElementById('btn-envoyer-txt');
    const btnSpin = document.getElementById('btn-envoyer-spin');
    const btn     = document.getElementById('btn-envoyer');

    btnTxt.classList.add('d-none');
    btnSpin.classList.remove('d-none');
    btn.disabled = true;

    fetch('/commandes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept':       'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => {
        if (!r.ok) return r.json().then(e => { throw new Error(e.message || 'Erreur serveur'); });
        return r.json();
    })
    .then(d => {
        btnTxt.classList.remove('d-none');
        btnSpin.classList.add('d-none');

        if (d.success) {
            Swal.fire({
                icon: 'success',
                title: 'Commande envoyée !',
                html: `<div style="color:#6b7280">${d.message}</div>`,
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = '/commandes';
            });
        } else {
            btn.disabled = false;
            toastr.error(d.message || 'Une erreur est survenue.');
        }
    })
    .catch(err => {
        btnTxt.classList.remove('d-none');
        btnSpin.classList.add('d-none');
        btn.disabled = false;
        toastr.error(err.message || 'Erreur de connexion.');
        console.error(err);
    });
}
</script>
@endpush