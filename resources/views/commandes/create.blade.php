@extends('layouts.app')
@section('title', 'Nouvelle commande')
@section('page_title', 'Nouvelle commande')
@section('breadcrumb', 'Prise de commande')

@section('content')

<div class="row g-3" style="height:calc(100vh - 140px)">

    {{-- ══ COLONNE GAUCHE : Menu ══ --}}
    <div class="col-lg-7 d-flex flex-column" style="overflow:hidden;height:100%">

        {{-- En-tête commande --}}
        <div class="card mb-3 flex-shrink-0">
            <div class="card-body py-3 px-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-500 mb-1" style="font-size:12px">
                            <i class="bi bi-receipt me-1"></i>Type
                        </label>
                        <select id="cmd_type" class="form-select form-select-sm" onchange="toggleTable()">
                            <option value="sur_place">🪑 Sur place</option>
                            <option value="emporter">🛍 À emporter</option>
                            <option value="livraison">🚴 Livraison</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="table-wrap">
                        <label class="form-label fw-500 mb-1" style="font-size:12px">
                            <i class="bi bi-grid me-1"></i>Table
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

                    <div class="col-md-4">
                        <label class="form-label fw-500 mb-1" style="font-size:12px">
                            <i class="bi bi-person me-1"></i>Client
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
                        <label class="form-label fw-500 mb-1" style="font-size:12px">
                            <i class="bi bi-bicycle me-1"></i>Livraison / Livreur
                        </label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select id="cmd_livreur" class="form-select form-select-sm select2-livreur">
                                    <option value="">-- Aucun livreur --</option>
                                    @foreach(
                                        App\Models\User::with('role')->whereHas('role', fn($q) => $q->where('nom','serveur'))->orderBy('name')->get()
                                        as $livreur)
                                    <option value="{{ $livreur->id }}">{{ $livreur->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="number" id="cmd_frais_livraison" class="form-control form-control-sm" value="0" min="0" placeholder="Frais livraison">
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-500 mb-1" style="font-size:12px">
                            <i class="bi bi-person-badge me-1"></i>Cuisinier assigné
                        </label>
                        <select id="cmd_cuisinier" class="form-select form-select-sm select2-cuisinier">
                            <option value="">-- Aucune attribution --</option>
                            @foreach(
                                App\Models\User::with('role')->whereHas('role', fn($q) => $q->where('nom','cuisinier'))->orderBy('name')->get()
                                as $cook)
                            <option value="{{ $cook->id }}">{{ $cook->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recherche + filtre catégorie --}}
        <div class="card mb-3 flex-shrink-0">
            <div class="card-body py-2 px-3">
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
        <div class="overflow-auto flex-grow-1 pe-1" id="produits-grid">
            <div class="row g-2">
                @foreach($produits as $catNom => $prods)
                    @foreach($prods as $p)
                    <div class="col-6 col-md-4 prod-item"
                        data-cat="{{ Str::slug($catNom) }}"
                        data-search="{{ strtolower($p->nom) }}">
                        <div class="prod-card"
                            onclick="ajouterAuPanier({{ $p->id }}, '{{ addslashes($p->nom) }}', {{ $p->prix }})">
                            <div class="prod-card-img">
                                <img src="{{ $p->photo_url }}" alt="{{ $p->nom }}">
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
                {{-- Message aucun résultat --}}
                <div class="col-12 d-none text-center py-5 text-muted" id="no-results">
                    <i class="bi bi-search d-block fs-1 mb-2 opacity-25"></i>
                    Aucun produit trouvé
                </div>
            </div>
        </div>
    </div>

    {{-- ══ COLONNE DROITE : Panier ══ --}}
    <div class="col-lg-5 d-flex flex-column" style="height:100%">
        <div class="card d-flex flex-column" style="height:100%;overflow:hidden">

            {{-- Header panier --}}
            <div class="card-header d-flex align-items-center justify-content-between py-2 px-3 flex-shrink-0">
                <span class="fw-bold">
                    <i class="bi bi-cart3 me-2 text-muted"></i>Panier
                    <span class="badge bg-dark ms-1" id="panier-count" style="display:none">0</span>
                </span>
                <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="viderPanier()">
                    <i class="bi bi-trash me-1"></i>Vider
                </button>
            </div>

            {{-- Liste articles --}}
            <div class="flex-grow-1 overflow-auto p-3" id="panier-list">
                {{-- État vide --}}
                <div id="panier-empty" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x d-block fs-1 mb-2 opacity-25"></i>
                    <div style="font-size:14px">Panier vide</div>
                    <small>Cliquez sur un produit pour l'ajouter</small>
                </div>
            </div>

            {{-- Footer panier --}}
            <div class="flex-shrink-0 border-top">

                {{-- Notes cuisine --}}
                <div class="px-3 pt-3 pb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-chat-left-text text-muted"></i>
                        </span>
                        <input type="text" id="cmd_notes" class="form-control border-start-0"
                            placeholder="Notes pour la cuisine...">
                    </div>
                </div>

                {{-- Remise --}}
                <div class="px-3 pb-2">
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
                <div class="px-3 pb-3">
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size:13px">
                        <span>Sous-total</span>
                        <span id="display-sous-total">0 F</span>
                    </div>
                    <div class="d-flex justify-content-between text-danger mb-1"
                        style="font-size:13px;display:none!important" id="remise-row">
                        <span>Remise</span>
                        <span id="display-remise">-0 F</span>
                    </div>
                        <div class="d-flex justify-content-between text-muted mb-1" style="font-size:13px;display:none!important" id="frais-livraison-row">
                        <span>Frais livraison</span>
                        <span id="display-frais-livraison">0 F</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mt-2 pt-2 border-top">
                        <span style="font-size:16px">TOTAL</span>
                        <span id="display-total" style="font-size:20px;color:#c9a96e;font-weight:700">0 F</span>
                    </div>
                </div>

                {{-- Bouton envoyer --}}
                <div class="px-3 pb-3">
                    <button class="btn btn-dark w-100 py-3 fw-bold"
                        id="btn-envoyer" onclick="envoyerCommande()" disabled>
                        <span id="btn-envoyer-txt">
                            <i class="bi bi-send-fill me-2"></i>Envoyer à la cuisine
                        </span>
                        <span id="btn-envoyer-spin"
                            class="spinner-border spinner-border-sm d-none" role="status"></span>
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

<style>
/* ── GÉNÉRAL ── */
.fw-500 { font-weight: 500; }

/* ── PRODUIT CARD ── */
.prod-card {
    background: #fff;
    border: 1.5px solid #eaeaef;
    border-radius: 12px;
    cursor: pointer;
    transition: all .15s;
    overflow: hidden;
    height: 100%;
}
.prod-card:hover {
    border-color: #c9a96e;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(201,169,110,.2);
}
.prod-card-img {
    position: relative;
    height: 80px;
    overflow: hidden;
    background: #f5f5f7;
}
.prod-card-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .2s;
}
.prod-card:hover .prod-card-img img {
    transform: scale(1.05);
}
.prod-card-overlay {
    position: absolute; inset: 0;
    background: rgba(201,169,110,.25);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .15s;
    font-size: 28px; color: #c9a96e;
}
.prod-card:hover .prod-card-overlay { opacity: 1; }
.prod-card-body {
    padding: 8px 10px;
}
.prod-nom {
    font-size: 12.5px; font-weight: 600;
    color: #1a1a2e; line-height: 1.3;
    white-space: nowrap; overflow: hidden;
    text-overflow: ellipsis;
}
.prod-prix {
    font-size: 13px; font-weight: 700;
    color: #c9a96e; margin-top: 2px;
}

/* ── PANIER ITEM ── */
.panier-item {
    background: #f8f9fa;
    border: 1px solid #eaeaef;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 8px;
    animation: slideIn .2s ease;
}
@keyframes slideIn {
    from { opacity:0; transform: translateY(-8px); }
    to   { opacity:1; transform: translateY(0); }
}
.panier-item-nom {
    font-size: 13px; font-weight: 600; color: #1a1a2e;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.panier-item-prix {
    font-size: 12px; color: #c9a96e; font-weight: 700;
}
.panier-item-prix .unit {
    font-weight: 400; color: #9299a8; font-size: 11px;
}
.qty-btn {
    width: 26px; height: 26px;
    border-radius: 6px; border: 1px solid #dee2e6;
    background: #fff; font-size: 14px; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .1s; flex-shrink: 0;
    color: #374151;
}
.qty-btn:hover { background: #212529; color: #fff; border-color: #212529; }
.qty-display {
    min-width: 24px; text-align: center;
    font-size: 14px; font-weight: 700; color: #1a1a2e;
}

/* ── SELECT2 intégré Bootstrap ── */
.select2-container--default .select2-selection--single {
    border: 1px solid #dee2e6 !important;
    border-radius: 6px !important;
    height: 31px !important;
    padding: 2px 8px !important;
    font-size: 13.5px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 27px !important;
    color: #212529 !important;
    padding: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 29px !important;
}
.select2-dropdown {
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,.1) !important;
    font-size: 13px !important;
}
.select2-container--default .select2-results__option--highlighted {
    background: #c9a96e !important;
}
.select2-search--dropdown .select2-search__field {
    border-radius: 6px !important;
    border: 1px solid #dee2e6 !important;
    font-size: 13px !important;
}
.select2-container { width: 100% !important; }
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
    // Catégories
    $('#filter-categorie').select2({
        minimumResultsForSearch: Infinity,
        placeholder: '📂 Toutes les catégories',
    }).on('change', function () {
        showCat($(this).val());
    });

    // Tables
    $('#cmd_table').select2({
        placeholder: '-- Choisir une table --',
        allowClear: true,
    });

    // Clients
    $('#cmd_client').select2({
        placeholder: '-- Aucun client --',
        allowClear: true,
    });

    // Cuisiniers
    $('#cmd_cuisinier').select2({
        placeholder: '-- Aucune attribution --',
        allowClear: true,
    });

    // Livreurs
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

    // Réinitialiser le filtre catégorie si on cherche
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

    // Feedback visuel
    const card = event.currentTarget;
    card.style.transform = 'scale(0.95)';
    setTimeout(() => { card.style.transform = ''; }, 150);
}

// ── PANIER : CHANGER QUANTITÉ ─────────────────────────
function changerQuantite(id, delta) {
    id = String(id);
    if (!panier[id]) return;
    // Pas de 0.5 au lieu de 1, arrondi pour éviter les erreurs flottantes JS
    panier[id].quantite = Math.round((panier[id].quantite + delta) * 2) / 2;
    if (panier[id].quantite <= 0) {
        delete panier[id];
    }
    renderPanier();
}

// ── PANIER : RETIRER ──────────────────────────────────
function retirerItem(id) {
    id = String(id);
    // Animation avant suppression
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
    const empty = document.getElementById('panier-empty');
    const count = document.getElementById('panier-count');
    const btnEnvoyer = document.getElementById('btn-envoyer');

    const ids = Object.keys(panier);

    if (ids.length === 0) {
        // ✅ Fix : reconstruire le contenu au lieu de manipuler un élément déplacé
        list.innerHTML = `
            <div id="panier-empty" class="text-center py-5 text-muted">
                <i class="bi bi-cart-x d-block fs-1 mb-2 opacity-25"></i>
                <div style="font-size:14px">Panier vide</div>
                <small>Cliquez sur un produit pour l'ajouter</small>
            </div>`;
        count.style.display = 'none';
        btnEnvoyer.disabled = true;
        recalculerTotal();
        return;
    }

    // ✅ Construire le HTML sans manipuler le DOM élément par élément
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

    // Badge compteur
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

    // État chargement
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