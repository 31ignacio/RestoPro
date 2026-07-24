@extends('layouts.app')
@section('title', 'Produits')
@section('page_title', 'Produits & Menu')
@section('breadcrumb', 'Gestion du catalogue')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold">Produits</h5>
        <small class="text-muted">
            {{ $produits->total() }} produit(s) au total
            @if(request()->hasAny(['search','categorie_id','disponible']))
            — <span class="text-warning">filtres actifs</span>
            @endif
        </small>
    </div>
    <button class="btn btn-dark px-4" onclick="openProduitModal()">
        <i class="bi bi-plus-lg me-2"></i>
        <span class="d-none d-sm-inline">Nouveau produit</span>
        <span class="d-sm-none">Nouveau</span>
    </button>
</div>

{{-- ══ FILTRES ══ --}}
<form method="GET" action="{{ route('Recette.index') }}" id="filter-form">
<div class="filtre-bar mb-4">
    <div class="row g-2 align-items-center">

        <div class="col-12 col-md-5">
            <div class="search-wrap">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="search" id="search-prod"
                    class="search-input"
                    placeholder="Rechercher un produit..."
                    value="{{ request('search') }}"
                    autocomplete="off">
                @if(request('search'))
                <a href="{{ route('Recette.index', request()->except(['search','page'])) }}"
                    class="search-clear" title="Effacer">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </div>

        <div class="col-6 col-md-3">
            <select name="categorie_id" id="filter-cat" class="form-select select2-filter">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('categorie_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nom }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-2">
            <select name="disponible" id="filter-dispo" class="form-select" onchange="submitFilters()">
                <option value="">Tous statuts</option>
                <option value="1" {{ request('disponible') === '1' ? 'selected' : '' }}>Disponible</option>
                <option value="0" {{ request('disponible') === '0' ? 'selected' : '' }}>Indisponible</option>
            </select>
        </div>

        <div class="col-12 col-md-2 d-flex justify-content-end gap-2">
            @if(request()->hasAny(['search','categorie_id','disponible']))
            <a href="{{ route('Recette.index') }}" class="view-btn" title="Réinitialiser">
                <i class="bi bi-x-circle"></i>
            </a>
            @endif
            <button type="button" class="view-btn" id="btn-grid"
                onclick="setView('grid', this)" title="Vue grille">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
            <button type="button" class="view-btn" id="btn-list"
                onclick="setView('list', this)" title="Vue liste">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
    </div>
</div>
</form>

{{-- ══ STATS RAPIDES ══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val">{{ $stats['total'] }}</div>
            <div class="ms-label">Total produits</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val text-success">{{ $stats['dispo'] }}</div>
            <div class="ms-label">Disponibles</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val text-danger">{{ $stats['indispo'] }}</div>
            <div class="ms-label">Indisponibles</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="ms-val" style="color:#c9a96e">{{ $stats['cats'] }}</div>
            <div class="ms-label">Catégories</div>
        </div>
    </div>
</div>

{{-- ══ VUE GRILLE ══ --}}
<div id="view-grid" style="display:none">
    <div class="row g-3">
        @forelse($produits as $p)
        <div class="col-6 col-md-4 col-xl-3">
            <div class="prod-grid-card {{ !$p->disponible ? 'unavailable' : '' }}" id="gi-{{ $p->id }}">
                <div class="pgc-img-wrap">
                    <img src="{{ asset('restopro/public/produits/' . $p->photo) }}" alt="{{ $p->nom }}" class="pgc-img" loading="lazy">
                    <span class="pgc-cat-badge" style="background:{{ $p->categorie->couleur ?? '#6c757d' }}">
                        {{ $p->categorie->nom }}
                    </span>
                    @if(!$p->disponible)
                    <div class="pgc-unavail-overlay"><span>Indisponible</span></div>
                    @endif
                    <div class="pgc-actions-overlay">
                        <button class="pgc-action-btn" onclick="showProduitDetail({{ $p->id }})" title="Voir">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="pgc-action-btn" onclick="openProduitModal({{ $p->id }})" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="pgc-action-btn danger"
                            onclick="deleteProduit({{ $p->id }}, '{{ addslashes($p->nom) }}')" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="pgc-body">
                    <div class="pgc-nom">{{ $p->nom }}</div>
                    @if($p->description)
                    <div class="prod-desc prod-desc--grid">{{ $p->description }}</div>
                    @endif
                    <div class="pgc-footer">
                        <div class="pgc-prix">{{ number_format($p->prix, 0, ',', ' ') }} F</div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                {{ $p->disponible ? 'checked' : '' }}
                                onchange="toggleDisponible({{ $p->id }}, this.checked)">
                        </div>
                    </div>
                    @if($p->gerer_stock && $p->stock)
                    <div class="pgc-stock {{ $p->stock->isEnAlerte() ? 'alerte' : '' }}">
                        <i class="bi bi-box-seam me-1"></i>
                        Stock : {{ $p->stock->quantite }} {{ $p->stock->unite }}
                        @if($p->stock->isEnAlerte()) <span class="ms-1">⚠️</span> @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state">
                <i class="bi bi-bag-x"></i>
                <h6>Aucun produit</h6>
                <p>{{ request()->hasAny(['search','categorie_id','disponible'])
                    ? 'Aucun produit ne correspond à vos critères.'
                    : 'Créez votre premier produit pour commencer.' }}</p>
                @if(!request()->hasAny(['search','categorie_id','disponible']))
                <button class="btn btn-dark" onclick="openProduitModal()">
                    <i class="bi bi-plus-lg me-2"></i>Nouveau produit
                </button>
                @else
                <a href="{{ route('Recette.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser les filtres
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($produits->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {!! $produits->links('vendor.pagination.custom') !!}
    </div>
    @endif
</div>

{{-- ══ VUE LISTE — lignes-cartes, pas de tableau ══ --}}
<div id="view-list">
    <div class="prod-list">
        @forelse($produits as $p)
        <div class="prod-row" id="li-{{ $p->id }}">

           <div class="prod-row-media">
                <img src="{{ asset('restopro/public/produits/' . $p->photo) }}"
                     alt="{{ $p->nom }}"
                     class="prod-row-thumb">
            
                <span class="prod-row-dot {{ $p->disponible ? 'on' : 'off' }}"
                      title="{{ $p->disponible ? 'Disponible' : 'Indisponible' }}"></span>
            </div>

            <div class="prod-row-main">
                <div class="prod-row-name">{{ $p->nom }}</div>
                @if($p->description)
                <div class="prod-row-desc">{{ $p->description }}</div>
                @endif
                <div class="prod-row-meta">
                    <span class="cat-pill-badge"
                        style="background:{{ $p->categorie->couleur ?? '#6c757d' }}20;
                               color:{{ $p->categorie->couleur ?? '#6c757d' }}">
                        <i class="bi bi-{{ $p->categorie->icone ?? 'tag' }} me-1"></i>{{ $p->categorie->nom }}
                    </span>
                    @if($p->gerer_stock && $p->stock)
                    <span class="stock-chip {{ $p->stock->isEnAlerte() ? 'alerte' : 'ok' }}">
                        {{ $p->stock->quantite }} {{ $p->stock->unite }}
                        @if($p->stock->isEnAlerte()) ⚠️ @endif
                    </span>
                    @endif
                </div>
            </div>

            <div class="prod-row-side">
                <div class="prod-row-price">{{ number_format($p->prix, 0, ',', ' ') }} F</div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                        {{ $p->disponible ? 'checked' : '' }}
                        onchange="toggleDisponible({{ $p->id }}, this.checked)">
                </div>
                <div class="prod-row-actions">
                    <button class="tbl-action-btn" onclick="showProduitDetail({{ $p->id }})" title="Voir le détail">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="tbl-action-btn" onclick="openProduitModal({{ $p->id }})" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="tbl-action-btn danger"
                        onclick="deleteProduit({{ $p->id }}, '{{ addslashes($p->nom) }}')" title="Supprimer">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="bi bi-bag-x"></i>
            <h6>Aucun produit</h6>
            <p>{{ request()->hasAny(['search','categorie_id','disponible'])
                ? 'Aucun produit ne correspond à vos critères.'
                : 'Créez votre premier produit pour commencer.' }}</p>
            @if(!request()->hasAny(['search','categorie_id','disponible']))
            <button class="btn btn-dark" onclick="openProduitModal()">
                <i class="bi bi-plus-lg me-2"></i>Nouveau produit
            </button>
            @else
            <a href="{{ route('Recette.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Réinitialiser
            </a>
            @endif
        </div>
        @endforelse
    </div>

    @if($produits->hasPages())
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-3 gap-2 px-1">
        <small class="text-muted order-2 order-sm-1">
            Affichage {{ $produits->firstItem() }}–{{ $produits->lastItem() }}
            sur {{ $produits->total() }} produit(s)
        </small>
        <div class="order-1 order-sm-2">
            {!! $produits->links('vendor.pagination.custom') !!}
        </div>
    </div>
    @endif
</div>

{{-- ══ MODAL CREATE / EDIT ══ --}}
<div class="modal fade" id="produitModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="produitModalTitle">Nouveau produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="produitForm" enctype="multipart/form-data">
                    <input type="hidden" id="prod_id">
                    <div class="row g-3">

                        <div class="col-12">
                            <div class="photo-upload-zone" id="photo-zone"
                                onclick="document.getElementById('prod_photo').click()">
                                <img id="prod_photo_preview"
                                    src="{{  asset('restopro/public/produits/' . $p->photo) }}"
                                    style="display:none;width:100%;height:100%;object-fit:cover;border-radius:12px">
                                <div id="photo-placeholder">
                                    <i class="bi bi-camera d-block fs-2 mb-2 opacity-50"></i>
                                    <div style="font-size:13px;color:#9299a8">Cliquez pour ajouter une photo</div>
                                    <small class="text-muted">JPG, PNG — max 2 Mo</small>
                                </div>
                            </div>
                            <input type="file" id="prod_photo" class="d-none"
                                accept="image/*" onchange="previewPhoto(this)">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-500">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" id="prod_nom" class="form-control"
                                placeholder="Ex: Tiéboudienne, Jus Bissap...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Prix (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" id="prod_prix" class="form-control" placeholder="0" min="0">
                                <span class="input-group-text">F</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Catégorie <span class="text-danger">*</span></label>
                            <select id="prod_categorie" class="form-select select2-modal">
                                <option value="">Choisir une catégorie...</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Ordre</label>
                            <input type="number" id="prod_ordre" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3 d-flex flex-column justify-content-end pb-1">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="prod_disponible" checked>
                                <label class="form-check-label fw-500" for="prod_disponible">Disponible</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="prod_gerer_stock">
                                <label class="form-check-label fw-500" for="prod_gerer_stock">Gérer stock</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea id="prod_description" class="form-control textarea-auto" rows="2"
                                placeholder="Ingrédients, allergènes, description..."
                                oninput="autoResize(this)"></textarea>
                            <div class="desc-counter"><span id="desc-chars">0</span> caractère(s)</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-5" onclick="saveProduit()">
                    <span id="prodSaveTxt"><i class="bi bi-check2 me-1"></i>Enregistrer</span>
                    <span id="prodSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DÉTAIL ══ --}}
<div class="modal fade" id="produitDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Détail produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="produitDetailBody">
                <div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
:root {
    --gold:   #c9a96e;
    --ink:    #1a1a2e;
    --muted:  #9299a8;
    --border: #eaeaef;
    --bg:     #f7f8fa;
}
.fw-500 { font-weight: 500; }

/* ══ DESCRIPTION ══ */
.prod-desc {
    font-size: 12px; color: var(--muted); line-height: 1.5;
    overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical;
    word-break: break-word;
}
.prod-desc--grid { -webkit-line-clamp: 2; margin-bottom: 10px; }
.prod-desc--detail {
    display: block; -webkit-line-clamp: unset;
    font-size: 13px; color: #6b7280; white-space: pre-line;
}

/* ══ FILTRE BAR ══ */
.filtre-bar { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 16px 18px; }
.search-wrap { position: relative; display: flex; align-items: center; }
.search-icon { position: absolute; left: 12px; color: var(--muted); font-size: 15px; pointer-events: none; }
.search-input {
    width: 100%; padding: 9px 36px; border: 1.5px solid var(--border); border-radius: 10px;
    font-size: 13.5px; outline: none; transition: border-color .15s; font-family: inherit;
}
.search-input:focus { border-color: var(--gold); }
.search-clear {
    position: absolute; right: 10px; background: none; border: none; color: var(--muted);
    font-size: 16px; cursor: pointer; padding: 2px 4px; line-height: 1; text-decoration: none;
}
.search-clear:hover { color: #374151; }

/* ══ VUE BUTTONS ══ */
.view-btn {
    width: 36px; height: 36px; border-radius: 9px; border: 1.5px solid var(--border); background: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--muted);
    cursor: pointer; transition: all .15s; text-decoration: none;
}
.view-btn:hover { border-color: var(--muted); color: #374151; }
.view-btn.active { background: var(--ink); border-color: var(--ink); color: #fff; }

/* ══ MINI STATS ══ */
.mini-stat { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; text-align: center; }
.ms-val   { font-size: 24px; font-weight: 700; color: var(--ink); line-height: 1; }
.ms-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }

/* ══ GRID CARD ══ */
.prod-grid-card {
    background: #fff; border: 1px solid var(--border); border-radius: 16px; overflow: hidden;
    transition: box-shadow .2s, transform .15s; height: 100%;
}
.prod-grid-card:hover { box-shadow: 0 8px 32px rgba(0,0,0,.10); transform: translateY(-2px); }
.prod-grid-card.unavailable { opacity: .7; }
.pgc-img-wrap { position: relative; height: 160px; overflow: hidden; background: #f5f5f7; }
.pgc-img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
.prod-grid-card:hover .pgc-img { transform: scale(1.04); }
.pgc-cat-badge {
    position: absolute; top: 10px; left: 10px; color: #fff; font-size: 10px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px; letter-spacing: .03em;
}
.pgc-unavail-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
}
.pgc-unavail-overlay span { background: rgba(220,53,69,.9); color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.pgc-actions-overlay {
    position: absolute; inset: 0; background: rgba(15,17,23,.6);
    display: flex; align-items: center; justify-content: center; gap: 8px; opacity: 0; transition: opacity .2s;
}
.prod-grid-card:hover .pgc-actions-overlay { opacity: 1; }
.pgc-action-btn {
    width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 15px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: background .15s; backdrop-filter: blur(4px);
}
.pgc-action-btn:hover { background: rgba(255,255,255,.3); }
.pgc-action-btn.danger:hover { background: rgba(220,53,69,.8); }
.pgc-body { padding: 14px; }
.pgc-nom  { font-size: 14px; font-weight: 600; color: var(--ink); margin-bottom: 4px; line-height: 1.3; }
.pgc-footer { display: flex; align-items: center; justify-content: space-between; }
.pgc-prix { font-size: 16px; font-weight: 700; color: var(--gold); }
.pgc-stock { margin-top: 8px; font-size: 11px; color: var(--muted); display: flex; align-items: center; }
.pgc-stock.alerte { color: #dc3545; }

/* ══════════════════════════════════════
   VUE LISTE — lignes-cartes
   Remplace l'ancien tableau : une seule structure flexbox
   qui se réorganise nativement sur mobile, sans colonnes
   masquées ni doublons de contenu.
══════════════════════════════════════ */
.prod-list { display: flex; flex-direction: column; gap: 10px; }

.prod-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: 14px;
    background: #fff; border: 1px solid var(--border); border-radius: 14px;
    padding: 12px 16px; transition: box-shadow .15s, border-color .15s;
}
.prod-row:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); border-color: #dcdfe6; }

.prod-row-media { position: relative; flex-shrink: 0; }
.prod-row-thumb { width: 54px; height: 54px; border-radius: 12px; object-fit: cover; background: #f0f0f5; display: block; }
.prod-row-dot {
    position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px;
    border-radius: 50%; border: 2px solid #fff;
}
.prod-row-dot.on  { background: #22c55e; }
.prod-row-dot.off { background: #ef4444; }

.prod-row-main { flex: 1 1 220px; min-width: 160px; }
.prod-row-name { font-size: 14px; font-weight: 700; color: var(--ink); line-height: 1.3; }
.prod-row-desc {
    font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.4;
    overflow: hidden; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; word-break: break-word;
}
.prod-row-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 6px; }

.prod-row-side {
    display: flex; align-items: center; gap: 18px; margin-left: auto;
    flex-shrink: 0;
}
.prod-row-price { font-size: 14px; font-weight: 700; color: var(--gold); white-space: nowrap; min-width: 70px; text-align: right; }
.prod-row-actions { display: flex; gap: 5px; }

/* ── Badges partagés ── */
.cat-pill-badge {
    display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600; white-space: nowrap;
}
.stock-chip { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.stock-chip.ok     { background: #d1e7dd; color: #0a3622; }
.stock-chip.alerte { background: #f8d7da; color: #842029; }

.tbl-action-btn {
    width: 30px; height: 30px; border-radius: 7px; border: 1px solid var(--border); background: #fff;
    display: inline-flex; align-items: center; justify-content: center; font-size: 13px; color: #6b7280;
    cursor: pointer; transition: all .12s;
}
.tbl-action-btn:hover { background: #f5f5f7; color: var(--ink); border-color: #d0d0d8; }
.tbl-action-btn.danger { color: #dc3545; border-color: #f8d7da; }
.tbl-action-btn.danger:hover { background: #f8d7da; }

/* ── Mobile : les contrôles passent sur une seconde ligne, séparés
     par un filet, au lieu de masquer des colonnes ── */
@media (max-width: 575px) {
    .prod-row { padding: 12px 14px; }
    .prod-row-side {
        flex-basis: 100%; margin-left: 0; justify-content: space-between;
        padding-top: 10px; margin-top: 4px; border-top: 1px solid #f2f3f6;
    }
    .prod-row-price { text-align: left; }
}

/* ══ EMPTY STATE ══ */
.empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-state i { font-size: 48px; opacity: .2; display: block; margin-bottom: 12px; }
.empty-state h6 { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.empty-state p  { font-size: 13px; margin-bottom: 16px; }

/* ══ PHOTO UPLOAD ══ */
.photo-upload-zone {
    height: 160px; border: 2px dashed var(--border); border-radius: 14px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; text-align: center;
    transition: border-color .15s; position: relative; overflow: hidden; background: #f8f9fa;
}
.photo-upload-zone:hover { border-color: var(--gold); background: rgba(201,169,110,.04); }

/* ══ TEXTAREA AUTO-RESIZE ══ */
.textarea-auto { resize: none; overflow: hidden; min-height: 70px; transition: height .1s ease; }
.desc-counter { font-size: 11px; color: var(--muted); text-align: right; margin-top: 4px; }

/* ══ VOIR PLUS (modal détail) ══ */
.desc-voir-plus-btn {
    background: none; border: none; padding: 0; font-size: 12px; color: var(--gold);
    cursor: pointer; font-weight: 600; margin-top: 4px; display: block;
}
.desc-voir-plus-btn:hover { text-decoration: underline; }

/* ══ PAGINATION ══ */
.pagination { margin: 0; gap: 4px; }
.pagination .page-item .page-link {
    width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
    border-radius: 8px !important; border: 1px solid var(--border); font-size: 13px; color: #6b7280;
    padding: 0; transition: all .15s;
}
.pagination .page-item.active .page-link { background: var(--ink); border-color: var(--ink); color: #fff; }
.pagination .page-item.disabled .page-link { opacity: .4; }
.pagination .page-item .page-link:hover { background: #f5f5f7; color: var(--ink); border-color: #d0d0d8; }

/* ══ SELECT2 ══ */
.select2-container--default .select2-selection--single {
    border: 1px solid #dee2e6 !important; border-radius: 8px !important; height: 38px !important;
    padding: 5px 10px !important; font-size: 13.5px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important; color: #212529 !important; padding: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
.select2-dropdown {
    border: 1px solid #dee2e6 !important; border-radius: 10px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.12) !important; font-size: 13.5px !important;
}
.select2-container--default .select2-results__option--highlighted { background: var(--gold) !important; }
.select2-search--dropdown .select2-search__field { border-radius: 7px !important; border: 1px solid #dee2e6 !important; }
.select2-container { width: 100% !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const produitModal       = new bootstrap.Modal('#produitModal');
const produitDetailModal = new bootstrap.Modal('#produitDetailModal');

let currentView = localStorage.getItem('produits_view') ?? 'list';

$(function () {
    $('#filter-cat').select2({
        placeholder: 'Toutes catégories',
        allowClear: true,
        minimumResultsForSearch: 5,
    }).on('change', () => submitFilters());

    $('#prod_categorie').select2({
        placeholder: 'Choisir une catégorie...',
        dropdownParent: $('#produitModal'),
    });

    document.getElementById('prod_description')
        .addEventListener('input', function () {
            document.getElementById('desc-chars').textContent = this.value.length;
        });

    let searchTimer;
    document.getElementById('search-prod').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => submitFilters(), 500);
    });

    applyView(currentView);
});

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

function submitFilters() {
    const form = document.getElementById('filter-form');
    form.querySelectorAll('[name="page"]').forEach(el => el.remove());
    form.submit();
}

function setView(view) {
    currentView = view;
    localStorage.setItem('produits_view', view);
    applyView(view);
}

function applyView(view) {
    document.getElementById('view-grid').style.display = view === 'grid' ? '' : 'none';
    document.getElementById('view-list').style.display = view === 'list' ? '' : 'none';
    document.getElementById('btn-grid').classList.toggle('active', view === 'grid');
    document.getElementById('btn-list').classList.toggle('active', view === 'list');
}

function previewPhoto(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('prod_photo_preview');
        prev.src = e.target.result;
        prev.style.display = '';
        document.getElementById('photo-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

function resetProduitForm() {
    const textarea = document.getElementById('prod_description');
    document.getElementById('prod_id').value             = '';
    document.getElementById('prod_nom').value            = '';
    document.getElementById('prod_prix').value           = '';
    textarea.value                                        = '';
    textarea.style.height                                 = 'auto';
    document.getElementById('desc-chars').textContent    = '0';
    document.getElementById('prod_ordre').value          = '0';
    document.getElementById('prod_disponible').checked   = true;
    document.getElementById('prod_gerer_stock').checked  = false;
    document.getElementById('prod_photo').value          = '';
    document.getElementById('prod_photo_preview').style.display = 'none';
    document.getElementById('photo-placeholder').style.display  = '';
    $('#prod_categorie').val(null).trigger('change');
}

function openProduitModal(id = null) {
    resetProduitForm();
    document.getElementById('produitModalTitle').textContent =
        id ? 'Modifier le produit' : 'Nouveau produit';

    if (id) {
        fetch(`/produits/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            document.getElementById('prod_id').value            = d.id;
            document.getElementById('prod_nom').value           = d.nom;
            document.getElementById('prod_prix').value          = d.prix;
            document.getElementById('prod_ordre').value         = d.ordre;
            document.getElementById('prod_disponible').checked  = !!d.disponible;
            document.getElementById('prod_gerer_stock').checked = !!d.gerer_stock;
            $('#prod_categorie').val(d.categorie_id).trigger('change');

            const textarea = document.getElementById('prod_description');
            textarea.value = d.description ?? '';
            document.getElementById('desc-chars').textContent = textarea.value.length;
            setTimeout(() => autoResize(textarea), 50);

            // photo_url gère déjà le fallback côté serveur (pas de photo -> no-photo.png)
            if (d.photo_url) {
                const prev = document.getElementById('prod_photo_preview');
                prev.src = d.photo_url;
                prev.style.display = '';
                document.getElementById('photo-placeholder').style.display = 'none';
            }
        });
    }
    produitModal.show();
}

function saveProduit() {
    const id  = document.getElementById('prod_id').value;
    const url = id ? `/Recette/${id}` : '/Recette';

    const formData = new FormData();
    formData.append('_token',       document.querySelector('meta[name="csrf-token"]').content);
    if (id) formData.append('_method', 'PUT');

    formData.append('nom',          document.getElementById('prod_nom').value);
    formData.append('categorie_id', document.getElementById('prod_categorie').value);
    formData.append('prix',         document.getElementById('prod_prix').value);
    formData.append('description',  document.getElementById('prod_description').value);
    formData.append('ordre',        document.getElementById('prod_ordre').value);
    formData.append('disponible',   document.getElementById('prod_disponible').checked  ? 1 : 0);
    formData.append('gerer_stock',  document.getElementById('prod_gerer_stock').checked ? 1 : 0);

    const photo = document.getElementById('prod_photo').files[0];
    if (photo) formData.append('photo', photo);

    document.getElementById('prodSaveTxt').classList.add('d-none');
    document.getElementById('prodSaveSpinner').classList.remove('d-none');

    fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: formData })
    .then(r => r.json())
    .then(d => {
        document.getElementById('prodSaveTxt').classList.remove('d-none');
        document.getElementById('prodSaveSpinner').classList.add('d-none');
        if (d.success) {
            produitModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            toastr.error(d.message ?? 'Erreur de validation.');
        }
    })
    .catch(() => {
        document.getElementById('prodSaveTxt').classList.remove('d-none');
        document.getElementById('prodSaveSpinner').classList.add('d-none');
        toastr.error('Erreur réseau.');
    });
}

const DESC_VOIR_PLUS_SEUIL = 200;

function buildDescriptionHtml(text) {
    if (!text) return '';
    const safe = text.replace(/</g,'&lt;').replace(/>/g,'&gt;');
    if (safe.length <= DESC_VOIR_PLUS_SEUIL) {
        return `<div class="prod-desc prod-desc--detail"
                    style="background:#f8f9fa;border-radius:10px;padding:12px;margin-bottom:12px">
                    ${safe}
                </div>`;
    }
    const court = safe.slice(0, DESC_VOIR_PLUS_SEUIL) + '…';
    return `<div style="background:#f8f9fa;border-radius:10px;padding:12px;margin-bottom:12px">
                <div class="prod-desc prod-desc--detail" id="desc-short">${court}</div>
                <div class="prod-desc prod-desc--detail" id="desc-full" style="display:none">${safe}</div>
                <button class="desc-voir-plus-btn" id="desc-toggle-btn" onclick="toggleDescDetail()">Voir plus ▾</button>
            </div>`;
}

function toggleDescDetail() {
    const s   = document.getElementById('desc-short');
    const f   = document.getElementById('desc-full');
    const btn = document.getElementById('desc-toggle-btn');
    const expanded = f.style.display !== 'none';
    s.style.display = expanded ? '' : 'none';
    f.style.display = expanded ? 'none' : '';
    btn.textContent = expanded ? 'Voir plus ▾' : 'Voir moins ▴';
}

function showProduitDetail(id) {
    document.getElementById('produitDetailBody').innerHTML =
        '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';
    produitDetailModal.show();

    fetch(`/produits/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        const nomEsc = d.nom.replace(/'/g, "\\'");
        const couleur = d.categorie?.couleur ?? '#6c757d';

        document.getElementById('produitDetailBody').innerHTML = `
            <div class="text-center mb-4">
                <img src="${d.photo}"
                    style="width:100px;height:100px;border-radius:16px;object-fit:cover;box-shadow:0 4px 20px rgba(0,0,0,.12)">
                <h5 class="fw-bold mt-3 mb-1">${d.nom}</h5>
                <span style="background:${couleur}20;color:${couleur};padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600">
                    ${d.categorie?.nom ?? '—'}
                </span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div style="background:#f8f9fa;border-radius:10px;padding:10px;text-align:center">
                        <div style="font-size:9px;color:#9299a8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Prix</div>
                        <div style="font-size:17px;font-weight:700;color:#c9a96e">${Number(d.prix).toLocaleString('fr')} F</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="background:#f8f9fa;border-radius:10px;padding:10px;text-align:center">
                        <div style="font-size:9px;color:#9299a8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Statut</div>
                        <span class="badge ${d.disponible ? 'bg-success' : 'bg-secondary'}">${d.disponible ? '✓ Dispo' : '✗ Indispo'}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div style="background:#f8f9fa;border-radius:10px;padding:10px;text-align:center">
                        <div style="font-size:9px;color:#9299a8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Ordre</div>
                        <div style="font-size:17px;font-weight:700;color:#1a1a2e">#${d.ordre ?? 0}</div>
                    </div>
                </div>
            </div>

            ${buildDescriptionHtml(d.description)}

            ${d.gerer_stock && d.stock
                ? `<div style="background:${d.stock.quantite<=d.stock.seuil_alerte ? '#fff3cd':'#d1e7dd'};
                      border-radius:10px;padding:10px 14px;font-size:13px;margin-bottom:12px">
                        <i class="bi bi-box-seam me-2"></i>
                        Stock : <strong>${d.stock.quantite} ${d.stock.unite}</strong>
                        ${d.stock.quantite<=d.stock.seuil_alerte ? '<span class="ms-2">⚠️ Stock faible</span>' : ''}
                   </div>`
                : ''}

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-outline-dark flex-fill" onclick="produitDetailModal.hide();openProduitModal(${d.id})">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </button>
                <button class="btn btn-outline-danger" onclick="produitDetailModal.hide();deleteProduit(${d.id},'${nomEsc}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
    });
}

function toggleDisponible(id, val) {
    // Route dédiée : plus fiable qu'une mise à jour partielle devinée côté serveur
    fetch(`/produits/${id}/toggle`, {
        method: 'PATCH',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) toastr.success(d.message);
        else            toastr.error(d.message);
    })
    .catch(() => toastr.error('Erreur réseau.'));
}

function deleteProduit(id, nom) {
    Swal.fire({
        title: 'Supprimer ce produit ?',
        html:  `<span style="color:#6b7280"><strong>${nom}</strong> sera supprimé définitivement.</span>`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText:   'Annuler',
        confirmButtonText:  'Supprimer',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/produits/${id}`, {
            method:  'DELETE',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                toastr.success(d.message);
                document.getElementById('gi-' + id)?.closest('[class*="col-"]')?.remove();
                document.getElementById('li-' + id)?.remove();
            } else {
                toastr.error(d.message);
            }
        })
        .catch(() => toastr.error('Erreur réseau.'));
    });
}
</script>
@endpush