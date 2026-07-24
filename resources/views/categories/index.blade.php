@extends('layouts.app')
@section('title', 'Catégories')
@section('page_title', 'Catégories')
@section('breadcrumb', 'Gestion du catalogue')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Catégories</h5>
        <small class="text-muted" id="cat-count-label">
            {{ $categories->total() }} catégorie(s)
        </small>
    </div>
    <button class="btn btn-dark px-4" onclick="openModal()">
        <i class="bi bi-plus-lg me-2"></i>Nouvelle catégorie
    </button>
</div>

{{-- ══ FILTRES ══ --}}
<div class="filtre-bar mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="search-wrap">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="search-cat"
                    class="search-input"
                    placeholder="Rechercher une catégorie..."
                    oninput="filtrerCategories()">
                <button class="search-clear d-none" id="search-clear-btn"
                    onclick="clearSearch()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select id="filter-statut" class="form-select" onchange="filtrerCategories()">
                <option value="">Tous statuts</option>
                <option value="1">Actives</option>
                <option value="0">Inactives</option>
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex justify-content-end gap-2">
            <button class="view-btn" id="btn-grid"
                onclick="setView('grid', this)" title="Vue grille">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
            <button class="view-btn active" id="btn-list"
                onclick="setView('list', this)" title="Vue liste">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
    </div>
</div>

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val">{{ $stats['total'] }}</div>
            <div class="ms-label">Total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val text-success">{{ $stats['actives'] }}</div>
            <div class="ms-label">Actives</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val text-danger">{{ $stats['inactives'] }}</div>
            <div class="ms-label">Inactives</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val" style="color:#c9a96e">
                {{ $stats['produits'] }}
            </div>
            <div class="ms-label">Produits total</div>
        </div>
    </div>
</div>

{{-- ══ VUE GRILLE ══ --}}
<div id="view-grid" style="display:none">
    <div class="row g-3" id="cats-grid">
        @forelse($categories as $cat)
        <div class="col-6 col-md-4 col-xl-3 cat-item"
            id="gi-{{ $cat->id }}"
            data-nom="{{ strtolower($cat->nom) }}"
            data-actif="{{ $cat->actif ? '1' : '0' }}">
            <div class="cat-grid-card {{ !$cat->actif ? 'inactive' : '' }}"
                style="--cat-color: {{ $cat->couleur ?? '#6c757d' }}">

                {{-- Header coloré --}}
                <div class="cgc-header">
                    <div class="cgc-icon-wrap">
                        <i class="bi bi-{{ $cat->icone ?? 'tag' }}"></i>
                    </div>
                    <div class="cgc-actions">
                        <button class="cgc-btn" onclick="showDetail({{ $cat->id }})" title="Détail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="cgc-btn" onclick="openModal({{ $cat->id }})" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="cgc-btn danger" onclick="deleteCategorie({{ $cat->id }}, '{{ addslashes($cat->nom) }}')" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="cgc-body">
                    <div class="cgc-nom">{{ $cat->nom }}</div>
                    <div class="cgc-meta">
                        <span class="cgc-count">
                            <i class="bi bi-bag me-1"></i>
                            {{ $cat->produits_count }} produit(s)
                        </span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                {{ $cat->actif ? 'checked' : '' }}
                                onchange="toggleActif({{ $cat->id }}, this)"
                                title="Activer / désactiver">
                        </div>
                    </div>
                    <div class="cgc-ordre">
                        <i class="bi bi-grip-vertical me-1 opacity-50"></i>
                        Ordre : {{ $cat->ordre }}
                    </div>
                </div>

                {{-- Barre couleur --}}
                <div class="cgc-color-bar"></div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state">
                <i class="bi bi-tag-fill"></i>
                <h6>Aucune catégorie</h6>
                <p>Créez votre première catégorie pour organiser votre menu.</p>
                <button class="btn btn-dark" onclick="openModal()">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle catégorie
                </button>
            </div>
        </div>
        @endforelse
    </div>
    <div class="empty-state d-none" id="no-result-grid">
        <i class="bi bi-search"></i>
        <h6>Aucun résultat</h6>
        <p>Aucune catégorie ne correspond à votre recherche.</p>
    </div>
</div>

{{-- ══ VUE LISTE ══ --}}
<div id="view-list">
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px">Icône</th>
                        <th>Nom</th>
                        <th>Produits</th>
                        <th>Couleur</th>
                        <th>Ordre</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="cats-tbody">
                    @forelse($categories as $cat)
                    <tr class="cat-item"
                        id="li-{{ $cat->id }}"
                        data-nom="{{ strtolower($cat->nom) }}"
                        data-actif="{{ $cat->actif ? '1' : '0' }}">
                        <td class="ps-4">
                            <div class="cat-icon-sm"
                                style="background:{{ $cat->couleur ?? '#6c757d' }}20;
                                       color:{{ $cat->couleur ?? '#6c757d' }}">
                                <i class="bi bi-{{ $cat->icone ?? 'tag' }}"></i>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold" style="font-size:13px">{{ $cat->nom }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ $cat->produits_count }} produit(s)
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:18px;height:18px;border-radius:5px;
                                    background:{{ $cat->couleur ?? '#6c757d' }};
                                    flex-shrink:0"></div>
                                <span style="font-size:12px;color:#9299a8">{{ $cat->couleur ?? '—' }}</span>
                            </div>
                        </td>
                        <td style="font-size:13px;color:#9299a8">{{ $cat->ordre }}</td>
                        <td>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                    {{ $cat->actif ? 'checked' : '' }}
                                    onchange="toggleActif({{ $cat->id }}, this)">
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <button class="tbl-action-btn"
                                    onclick="showDetail({{ $cat->id }})" title="Détail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="tbl-action-btn"
                                    onclick="openModal({{ $cat->id }})" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="tbl-action-btn danger"
                                    onclick="deleteCategorie({{ $cat->id }}, '{{ addslashes($cat->nom) }}')"
                                    title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-tag d-block fs-1 mb-2 opacity-25"></i>
                            Aucune catégorie.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="empty-state d-none" id="no-result-list">
                <i class="bi bi-search"></i>
                <h6>Aucun résultat</h6>
                <p>Aucune catégorie ne correspond à votre recherche.</p>
            </div>
        </div>
    </div>
</div>

{{-- ══ PAGINATION ══ --}}
@if($categories->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div style="font-size:13px;color:#9299a8">
        Affichage de {{ $categories->firstItem() }} à {{ $categories->lastItem() }}
        sur {{ $categories->total() }} catégorie(s)
    </div>
    <div class="pagination-wrap">
        {{ $categories->links() }}
    </div>
</div>
@endif

{{-- ══ MODAL CREATE / EDIT ══ --}}
<div class="modal fade" id="catModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="catModalTitle">Nouvelle catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cat_id">

                {{-- Aperçu live --}}
                <div class="preview-box mb-4" id="preview-box">
                    <div class="preview-icon" id="prev-icon-wrap">
                        <i class="bi bi-tag" id="prev-icon"></i>
                    </div>
                    <div>
                        <div class="preview-nom" id="prev-nom">Nom de la catégorie</div>
                        <div class="preview-sub">Aperçu en temps réel</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-500">Nom <span class="text-danger">*</span></label>
                        <input type="text" id="cat_nom" class="form-control"
                            placeholder="Ex: Entrées, Boissons, Desserts..."
                            oninput="updatePreview()">
                        <div class="invalid-feedback" id="err_nom"></div>
                    </div>

                    <div class="col-8">
                        <label class="form-label fw-500">
                            Icône Bootstrap
                            <a href="https://icons.getbootstrap.com" target="_blank"
                                class="ms-1" style="font-size:11px">Voir les icônes →</a>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" id="icon-preview-wrap">
                                <i class="bi bi-tag" id="icon-preview"></i>
                            </span>
                            <input type="text" id="cat_icone" class="form-control"
                                placeholder="tag, cup-hot, bag, fire..."
                                oninput="updateIcon(this.value); updatePreview()">
                        </div>

                        {{-- Icônes suggérées --}}
                        <div class="icon-suggestions mt-2">
                            @foreach(['tag','cup-hot','bag','fire','egg-fried','basket','cup-straw','archive','heart','star','gem','lightning','music-note','camera'] as $ic)
                            <button type="button" class="icon-sug-btn"
                                onclick="selectIcon('{{ $ic }}')" title="{{ $ic }}">
                                <i class="bi bi-{{ $ic }}"></i>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-4">
                        <label class="form-label fw-500">Couleur</label>
                        <input type="color" id="cat_couleur"
                            class="form-control form-control-color w-100"
                            value="#c9a96e"
                            oninput="updatePreview()">
                        {{-- Couleurs suggérées --}}
                        <div class="color-suggestions mt-2">
                            @foreach(['#c9a96e','#5cb87a','#5b9bd5','#e05c5c','#9b59b6','#e67e22','#1abc9c','#e74c3c'] as $col)
                            <button type="button" class="color-sug-btn"
                                style="background:{{ $col }}"
                                onclick="selectColor('{{ $col }}')"></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-6">
                        <label class="form-label fw-500">Ordre d'affichage</label>
                        <input type="number" id="cat_ordre" class="form-control" value="0" min="0">
                    </div>

                    <div class="col-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="cat_actif" checked>
                            <label class="form-check-label fw-500" for="cat_actif">Catégorie active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-5" onclick="saveCategorie()">
                    <span id="catSaveTxt"><i class="bi bi-check2 me-1"></i>Enregistrer</span>
                    <span id="catSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DÉTAIL ══ --}}
<div class="modal fade" id="catDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Détail catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="catDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.fw-500 { font-weight: 500; }

/* ── FILTRE BAR ── */
.filtre-bar {
    background: #fff; border: 1px solid #eaeaef;
    border-radius: 14px; padding: 16px 18px;
}
.search-wrap { position: relative; display: flex; align-items: center; }
.search-icon { position: absolute; left: 12px; color: #9299a8; font-size: 15px; pointer-events: none; }
.search-input {
    width: 100%; padding: 9px 36px;
    border: 1.5px solid #eaeaef; border-radius: 10px;
    font-size: 13.5px; outline: none; transition: border-color .15s;
    font-family: inherit;
}
.search-input:focus { border-color: #c9a96e; }
.search-clear {
    position: absolute; right: 10px;
    background: none; border: none;
    color: #9299a8; font-size: 16px; cursor: pointer;
    padding: 2px 4px; line-height: 1;
}

/* ── VUE BUTTONS ── */
.view-btn {
    width: 36px; height: 36px; border-radius: 9px;
    border: 1.5px solid #eaeaef; background: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #9299a8; cursor: pointer;
    transition: all .15s;
}
.view-btn.active { background: #212529; border-color: #212529; color: #fff; }

/* ── MINI STATS ── */
.mini-stat {
    background: #fff; border: 1px solid #eaeaef;
    border-radius: 12px; padding: 14px 16px; text-align: center;
}
.ms-val   { font-size: 24px; font-weight: 700; color: #1a1a2e; line-height: 1; }
.ms-label { font-size: 11px; color: #9299a8; text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }

/* ── CATEGORY GRID CARD ── */
.cat-grid-card {
    background: #fff; border: 1px solid #eaeaef;
    border-radius: 16px; overflow: hidden;
    transition: box-shadow .2s, transform .15s;
    height: 100%; position: relative;
}
.cat-grid-card:hover { box-shadow: 0 8px 32px rgba(0,0,0,.1); transform: translateY(-2px); }
.cat-grid-card.inactive { opacity: .65; }

.cgc-header {
    padding: 20px 16px 14px;
    display: flex; align-items: flex-start; justify-content: space-between;
    background: linear-gradient(135deg, color-mix(in srgb, var(--cat-color) 12%, transparent), color-mix(in srgb, var(--cat-color) 4%, transparent));
}
.cgc-icon-wrap {
    width: 52px; height: 52px; border-radius: 14px;
    background: var(--cat-color);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 4px 14px color-mix(in srgb, var(--cat-color) 40%, transparent);
}
.cgc-actions {
    display: flex; gap: 4px; opacity: 0;
    transition: opacity .15s;
}
.cat-grid-card:hover .cgc-actions { opacity: 1; }
.cgc-btn {
    width: 28px; height: 28px; border-radius: 7px;
    background: rgba(255,255,255,.8); border: 1px solid rgba(0,0,0,.06);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; color: #374151; cursor: pointer;
    transition: all .12s; backdrop-filter: blur(4px);
}
.cgc-btn:hover { background: #fff; color: #1a1a2e; }
.cgc-btn.danger:hover { background: #fee2e2; color: #dc3545; }

.cgc-body { padding: 14px 16px 12px; }
.cgc-nom {
    font-size: 15px; font-weight: 700; color: #1a1a2e;
    margin-bottom: 10px;
}
.cgc-meta {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px;
}
.cgc-count {
    font-size: 12px; color: #9299a8;
    background: #f5f5f7; padding: 3px 9px;
    border-radius: 20px;
}
.cgc-ordre { font-size: 11px; color: #c0c4ce; }

.cgc-color-bar {
    height: 3px; background: var(--cat-color);
    position: absolute; bottom: 0; left: 0; right: 0;
}

/* ── TABLE ACTIONS ── */
.tbl-action-btn {
    width: 30px; height: 30px; border-radius: 7px;
    border: 1px solid #eaeaef; background: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; color: #6b7280; cursor: pointer;
    transition: all .12s;
}
.tbl-action-btn:hover { background: #f5f5f7; color: #1a1a2e; border-color: #d0d0d8; }
.tbl-action-btn.danger { color: #dc3545; border-color: #f8d7da; }
.tbl-action-btn.danger:hover { background: #f8d7da; }

/* ── CAT ICON SM ── */
.cat-icon-sm {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
}

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center; padding: 48px 20px; color: #9299a8;
}
.empty-state i { font-size: 48px; opacity: .2; display: block; margin-bottom: 12px; }
.empty-state h6 { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.empty-state p  { font-size: 13px; margin-bottom: 16px; }

/* ── MODAL ── */
.preview-box {
    display: flex; align-items: center; gap: 14px;
    padding: 16px; border-radius: 12px;
    background: #f8f9fa; border: 1px solid #eaeaef;
    transition: background .2s;
}
.preview-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: #c9a96e;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #fff; flex-shrink: 0;
    transition: background .2s;
}
.preview-nom { font-size: 15px; font-weight: 700; color: #1a1a2e; }
.preview-sub { font-size: 12px; color: #9299a8; }

.icon-suggestions {
    display: flex; flex-wrap: wrap; gap: 4px;
}
.icon-sug-btn {
    width: 32px; height: 32px; border-radius: 8px;
    border: 1.5px solid #eaeaef; background: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; cursor: pointer; transition: all .12s; color: #374151;
}
.icon-sug-btn:hover { border-color: #c9a96e; color: #c9a96e; background: rgba(201,169,110,.08); }
.icon-sug-btn.selected { border-color: #c9a96e; background: rgba(201,169,110,.12); color: #c9a96e; }

.color-suggestions {
    display: flex; flex-wrap: wrap; gap: 5px;
}
.color-sug-btn {
    width: 22px; height: 22px; border-radius: 50%;
    border: 2px solid transparent; cursor: pointer;
    transition: transform .1s, border-color .1s;
}
.color-sug-btn:hover { transform: scale(1.2); }
.color-sug-btn.selected { border-color: #1a1a2e; }

/* ── PAGINATION ── */
.pagination-wrap .pagination {
    margin-bottom: 0;
    gap: 4px;
}
.pagination-wrap .page-link {
    border-radius: 8px !important;
    border: 1.5px solid #eaeaef;
    color: #374151;
    font-size: 13px;
    padding: 6px 12px;
    transition: all .12s;
}
.pagination-wrap .page-link:hover {
    background: #f5f5f7;
    border-color: #d0d0d8;
    color: #1a1a2e;
}
.pagination-wrap .page-item.active .page-link {
    background: #212529;
    border-color: #212529;
    color: #fff;
}
.pagination-wrap .page-item.disabled .page-link {
    opacity: .4;
}
</style>
@endpush

@push('scripts')
<script>
const catModal       = new bootstrap.Modal('#catModal');
const catDetailModal = new bootstrap.Modal('#catDetailModal');

// ── VUE ──────────────────────────────────────────────
function setView(view, btn) {
    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('view-grid').style.display = view === 'grid' ? '' : 'none';
    document.getElementById('view-list').style.display = view === 'list' ? '' : 'none';
    localStorage.setItem('cat_view', view);
}

// Restaurer la vue sauvegardée (défaut : liste)
(function () {
    const saved = localStorage.getItem('cat_view') || 'list';
    if (saved === 'grid') {
        document.getElementById('view-grid').style.display = '';
        document.getElementById('view-list').style.display = 'none';
        document.getElementById('btn-grid').classList.add('active');
        document.getElementById('btn-list').classList.remove('active');
    }
    // Si 'list' (ou rien), c'est déjà l'état par défaut dans le HTML
})();

// ── FILTRE ───────────────────────────────────────────
function filtrerCategories() {
    const q      = document.getElementById('search-cat').value.toLowerCase().trim();
    const statut = document.getElementById('filter-statut').value;

    document.getElementById('search-clear-btn').classList.toggle('d-none', !q);

    let visible = 0;
    document.querySelectorAll('.cat-item').forEach(el => {
        const matchQ = !q || el.dataset.nom.includes(q);
        const matchS = !statut || el.dataset.actif === statut;
        el.style.display = (matchQ && matchS) ? '' : 'none';
        if (matchQ && matchS) visible++;
    });

    document.getElementById('no-result-grid')?.classList.toggle('d-none', visible > 0);
    document.getElementById('no-result-list')?.classList.toggle('d-none', visible > 0);
    document.getElementById('cat-count-label').textContent = visible + ' catégorie(s) affichée(s)';
}

function clearSearch() {
    document.getElementById('search-cat').value = '';
    filtrerCategories();
    document.getElementById('search-cat').focus();
}

// ── APERÇU LIVE ──────────────────────────────────────
function updatePreview() {
    const nom     = document.getElementById('cat_nom').value || 'Nom de la catégorie';
    const couleur = document.getElementById('cat_couleur').value;
    const icone   = document.getElementById('cat_icone').value || 'tag';

    document.getElementById('prev-nom').textContent            = nom;
    document.getElementById('prev-icon').className             = `bi bi-${icone}`;
    document.getElementById('prev-icon-wrap').style.background = couleur;
}

function updateIcon(val) {
    const ic = val || 'tag';
    document.getElementById('icon-preview').className = `bi bi-${ic}`;
}

function selectIcon(ic) {
    document.getElementById('cat_icone').value = ic;
    document.querySelectorAll('.icon-sug-btn').forEach(b => b.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    updateIcon(ic);
    updatePreview();
}

function selectColor(col) {
    document.getElementById('cat_couleur').value = col;
    document.querySelectorAll('.color-sug-btn').forEach(b => b.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    updatePreview();
}

// ── MODAL ────────────────────────────────────────────
function resetForm() {
    document.getElementById('cat_id').value      = '';
    document.getElementById('cat_nom').value     = '';
    document.getElementById('cat_icone').value   = '';
    document.getElementById('cat_couleur').value = '#c9a96e';
    document.getElementById('cat_ordre').value   = '0';
    document.getElementById('cat_actif').checked = true;
    document.querySelectorAll('.icon-sug-btn').forEach(b => b.classList.remove('selected'));
    document.querySelectorAll('.color-sug-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('cat_nom').classList.remove('is-invalid');
    document.getElementById('err_nom').textContent = '';
    updateIcon('tag');
    updatePreview();
}

function openModal(id = null) {
    resetForm();
    document.getElementById('catModalTitle').textContent =
        id ? 'Modifier la catégorie' : 'Nouvelle catégorie';

    if (id) {
        fetch(`/categories/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            if (!r.ok) throw new Error('Erreur réseau');
            return r.json();
        })
        .then(d => {
            document.getElementById('cat_id').value      = d.id;
            document.getElementById('cat_nom').value     = d.nom ?? '';
            document.getElementById('cat_icone').value   = d.icone ?? '';
            document.getElementById('cat_couleur').value = d.couleur ?? '#c9a96e';
            document.getElementById('cat_ordre').value   = d.ordre ?? 0;
            document.getElementById('cat_actif').checked = !!d.actif;

            // Mettre à jour l'aperçu icône dans l'input-group
            updateIcon(d.icone ?? 'tag');

            // Marquer l'icône suggérée correspondante
            document.querySelectorAll('.icon-sug-btn').forEach(btn => {
                const ic = btn.getAttribute('title');
                btn.classList.toggle('selected', ic === d.icone);
            });

            // Marquer la couleur suggérée correspondante
            document.querySelectorAll('.color-sug-btn').forEach(btn => {
                const col = btn.style.background;
                // Comparer en créant un élément temporaire pour normaliser
                const tmp = document.createElement('div');
                tmp.style.background = d.couleur ?? '#c9a96e';
                btn.classList.toggle('selected', btn.style.background === tmp.style.background);
            });

            updatePreview();
        })
        .catch(() => {
            toastr.error('Impossible de charger la catégorie.');
        });
    }

    catModal.show();
}

function saveCategorie() {
    const id  = document.getElementById('cat_id').value;
    const url = id ? `/categories/${id}` : '/categories';

    // Reset erreurs
    document.getElementById('cat_nom').classList.remove('is-invalid');
    document.getElementById('err_nom').textContent = '';

    const payload = {
        nom:     document.getElementById('cat_nom').value.trim(),
        icone:   document.getElementById('cat_icone').value.trim() || 'tag',
        couleur: document.getElementById('cat_couleur').value,
        ordre:   parseInt(document.getElementById('cat_ordre').value) || 0,
        actif:   document.getElementById('cat_actif').checked ? 1 : 0,
    };

    if (!payload.nom) {
        document.getElementById('cat_nom').classList.add('is-invalid');
        document.getElementById('err_nom').textContent = 'Le nom est obligatoire.';
        return;
    }

    document.getElementById('catSaveTxt').classList.add('d-none');
    document.getElementById('catSaveSpinner').classList.remove('d-none');

    fetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('catSaveTxt').classList.remove('d-none');
        document.getElementById('catSaveSpinner').classList.add('d-none');

        if (d.success) {
            catModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            if (d.errors?.nom) {
                document.getElementById('cat_nom').classList.add('is-invalid');
                document.getElementById('err_nom').textContent = d.errors.nom[0];
            } else {
                toastr.error(d.message ?? 'Une erreur est survenue.');
            }
        }
    })
    .catch(() => {
        document.getElementById('catSaveTxt').classList.remove('d-none');
        document.getElementById('catSaveSpinner').classList.add('d-none');
        toastr.error('Erreur de connexion.');
    });
}

// ── TOGGLE ───────────────────────────────────────────
function toggleActif(id, checkbox) {
    fetch(`/categories/${id}/toggle`, {
        method: 'PATCH',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toastr.success(d.message);
            // Mettre à jour data-actif pour le filtre
            const item = checkbox.closest('.cat-item');
            if (item) item.dataset.actif = d.actif ? '1' : '0';
        } else {
            checkbox.checked = !checkbox.checked;
            toastr.error(d.message);
        }
    })
    .catch(() => {
        checkbox.checked = !checkbox.checked;
        toastr.error('Erreur de connexion.');
    });
}

// ── DÉTAIL ───────────────────────────────────────────
function showDetail(id) {
    document.getElementById('catDetailBody').innerHTML =
        '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';
    catDetailModal.show();

    fetch(`/categories/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur réseau');
        return r.json();
    })
    .then(d => {
        const produits = d.produits ?? [];
        const prodsHtml = produits.length > 0
            ? produits.slice(0, 8).map(p =>
                `<span style="display:inline-block;padding:3px 10px;background:#f5f5f7;
                border-radius:20px;font-size:12px;margin:2px">${p.nom}</span>`
              ).join('') + (produits.length > 8
                ? `<span style="font-size:12px;color:#9299a8;margin-left:4px">+${produits.length - 8} autres</span>`
                : '')
            : '<span class="text-muted" style="font-size:13px">Aucun produit associé</span>';

        const couleur = d.couleur ?? '#6c757d';
        const icone   = d.icone ?? 'tag';

        document.getElementById('catDetailBody').innerHTML = `
            <div class="text-center mb-4">
                <div style="width:72px;height:72px;border-radius:18px;background:${couleur};
                    display:flex;align-items:center;justify-content:center;margin:0 auto 14px;
                    box-shadow:0 6px 20px ${couleur}40">
                    <i class="bi bi-${icone}" style="font-size:32px;color:#fff"></i>
                </div>
                <h5 class="fw-bold mb-1">${d.nom}</h5>
                <span class="badge ${d.actif ? 'bg-success' : 'bg-secondary'}">
                    ${d.actif ? 'Active' : 'Inactive'}
                </span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-4">
                    <div style="background:#f8f9fa;border-radius:10px;padding:12px;text-align:center">
                        <div style="font-size:22px;font-weight:700;color:#1a1a2e">${d.produits_count ?? produits.length}</div>
                        <div style="font-size:11px;color:#9299a8;text-transform:uppercase">Produits</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="background:#f8f9fa;border-radius:10px;padding:12px;text-align:center">
                        <div style="font-size:22px;font-weight:700;color:#1a1a2e">${d.ordre ?? 0}</div>
                        <div style="font-size:11px;color:#9299a8;text-transform:uppercase">Ordre</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="background:#f8f9fa;border-radius:10px;padding:12px;text-align:center">
                        <div style="width:28px;height:28px;border-radius:7px;background:${couleur};margin:0 auto 4px"></div>
                        <div style="font-size:11px;color:#9299a8;text-transform:uppercase">Couleur</div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div style="font-size:12px;color:#9299a8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;font-weight:600">
                    Produits associés
                </div>
                <div>${prodsHtml}</div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-dark flex-fill"
                    onclick="catDetailModal.hide(); openModal(${d.id})">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </button>
                <button class="btn btn-outline-danger"
                    onclick="catDetailModal.hide(); deleteCategorie(${d.id}, '${(d.nom ?? '').replace(/'/g, "\\'")}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
    })
    .catch(() => {
        document.getElementById('catDetailBody').innerHTML =
            '<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>Impossible de charger le détail.</div>';
    });
}

// ── SUPPRIMER ────────────────────────────────────────
function deleteCategorie(id, nom) {
    Swal.fire({
        title: 'Supprimer cette catégorie ?',
        html:  `<span style="color:#6b7280"><strong>${nom}</strong> sera supprimée définitivement.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer',
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(`/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                toastr.success(d.message);
                document.getElementById('gi-' + id)?.remove();
                document.getElementById('li-' + id)?.remove();
                filtrerCategories();
            } else {
                toastr.error(d.message);
            }
        })
        .catch(() => toastr.error('Erreur de connexion.'));
    });
}
</script>
@endpush