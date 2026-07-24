@extends('layouts.app')
@section('title', 'Stock')
@section('page_title', 'Gestion du stock')
@section('breadcrumb', 'Inventaire & mouvements')

@section('content')

{{-- ALERTES --}}
@if($alertes > 0)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>{{ $alertes }} produit(s)</strong> en dessous du seuil d'alerte.
        <a href="#" onclick="filtrerAlertes()" class="alert-link ms-2">Voir uniquement les alertes →</a>
    </div>
</div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Stock</h5>
        <small class="text-muted">{{ $stocks->count() }} produit(s) suivis</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="openMouvementModal('entree')">
            <i class="bi bi-box-arrow-in-down me-1"></i>Entrée stock
        </button>
        <button class="btn btn-outline-danger" onclick="openMouvementModal('sortie')">
            <i class="bi bi-box-arrow-up me-1"></i>Sortie stock
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="stock-table">
            <thead>
                <tr>
                    <th class="ps-4">Produit</th>
                    <th>Catégorie</th>
                    <th>Quantité</th>
                    <th>Seuil alerte</th>
                    <th>Niveau</th>
                    <th>Unité</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $s)
                @php
                    $pct    = $s->seuil_alerte > 0
                        ? min(100, round($s->quantite / ($s->seuil_alerte * 3) * 100))
                        : 100;
                    $color  = $s->isEnAlerte()
                        ? ($s->quantite <= 0 ? 'danger' : 'warning')
                        : ($pct < 60 ? 'info' : 'success');
                @endphp
                <tr id="stock-row-{{ $s->id }}" class="{{ $s->isEnAlerte() ? 'table-warning' : '' }}">
                    <td class="ps-4">
                        <div class="fw-bold">{{ $s->produit->nom }}</div>
                        @if($s->isEnAlerte())
                            <small class="text-danger">
                                <i class="bi bi-exclamation-triangle me-1"></i>Stock faible
                            </small>
                        @endif
                    </td>
                    <td>
                        <span class="badge rounded-pill"
                            style="background:{{ $s->produit->categorie->couleur }}20;color:{{ $s->produit->categorie->couleur }}">
                            {{ $s->produit->categorie->nom }}
                        </span>
                    </td>
                    <td>
                        <span class="fw-bold fs-5 {{ $s->isEnAlerte() ? 'text-danger' : 'text-dark' }}"
                            id="qty-{{ $s->id }}">
                            {{ $s->quantite }}
                        </span>
                        <span class="text-muted ms-1" style="font-size:12px">{{ $s->unite }}</span>
                    </td>
                    <td class="text-muted">{{ $s->seuil_alerte }} {{ $s->unite }}</td>
                    <td style="width:140px">
                        <div class="progress" style="height:6px;border-radius:3px">
                            <div class="progress-bar bg-{{ $color }}"
                                style="width:{{ $pct }}%;border-radius:3px"></div>
                        </div>
                        <small class="text-muted">{{ $pct }}%</small>
                    </td>
                    <td style="font-size:12px;color:#9299a8">{{ $s->unite }}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-success me-1"
                            onclick="quickEntree({{ $s->produit_id }})">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary me-1"
                            onclick="showHistorique({{ $s->id }})">
                            <i class="bi bi-clock-history"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-dark"
                            onclick="openSeuilModal({{ $s->id }}, {{ $s->seuil_alerte }}, '{{ $s->unite }}')">
                            <i class="bi bi-sliders"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-box-seam fs-1 d-block mb-2 opacity-25"></i>
                        Aucun produit suivi en stock.<br>
                        <small>Activez "Gérer le stock" sur un produit pour le voir ici.</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL ENTRÉE / SORTIE --}}
<div class="modal fade" id="mouvementModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="mouvModalTitle">Entrée stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mouv_type">
                <div class="mb-3">
                    <label class="form-label fw-500">Produit <span class="text-danger">*</span></label>
                    <select id="mouv_produit" class="form-select">
                        <option value="">Choisir un produit...</option>
                        @foreach($stocks as $s)
                            <option value="{{ $s->produit_id }}">
                                {{ $s->produit->nom }}
                                (stock actuel : {{ $s->quantite }} {{ $s->unite }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-500">Quantité <span class="text-danger">*</span></label>
                        <input type="number" id="mouv_quantite" class="form-control"
                            placeholder="0" min="0.001" step="0.001">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-500">Unité</label>
                        <input type="text" id="mouv_unite" class="form-control" placeholder="kg, L, unité...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Motif</label>
                    <input type="text" id="mouv_motif" class="form-control"
                        placeholder="Livraison fournisseur, ajustement...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-4" id="mouvSaveBtn" onclick="saveMouvement()">
                    <span id="mouvSaveTxt"><i class="bi bi-check2 me-1"></i>Enregistrer</span>
                    <span id="mouvSaveSpin" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SEUIL --}}
<div class="modal fade" id="seuilModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Paramètres stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="seuil_stock_id">
                <div class="mb-3">
                    <label class="form-label fw-500">Seuil d'alerte</label>
                    <input type="number" id="seuil_val" class="form-control" min="0" step="0.1">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Unité</label>
                    <input type="text" id="seuil_unite" class="form-control" placeholder="kg, L, unité...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-4" onclick="saveSeuil()">
                    <i class="bi bi-check2 me-1"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HISTORIQUE --}}
<div class="modal fade" id="historiqueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Historique des mouvements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historiqueBody">
                <div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>.fw-500{font-weight:500}</style>
@endpush

@push('scripts')
<script>
const mouvementModal  = new bootstrap.Modal('#mouvementModal');
const seuilModal      = new bootstrap.Modal('#seuilModal');
const historiqueModal = new bootstrap.Modal('#historiqueModal');

function filtrerAlertes() {
    document.querySelectorAll('#stock-table tbody tr').forEach(tr => {
        tr.style.display = tr.classList.contains('table-warning') ? '' : 'none';
    });
}

function openMouvementModal(type, produitId = null) {
    document.getElementById('mouv_type').value     = type;
    document.getElementById('mouv_produit').value  = produitId ?? '';
    document.getElementById('mouv_quantite').value = '';
    document.getElementById('mouv_motif').value    = '';
    document.getElementById('mouv_unite').value    = '';
    document.getElementById('mouvModalTitle').textContent =
        type === 'entree' ? 'Entrée stock' : 'Sortie stock';
    document.getElementById('mouvSaveBtn').className =
        'btn px-4 ' + (type === 'entree' ? 'btn-success' : 'btn-danger');
    mouvementModal.show();
}

function quickEntree(produitId) {
    openMouvementModal('entree', produitId);
}

function saveMouvement() {
    const type     = document.getElementById('mouv_type').value;
    const produit  = document.getElementById('mouv_produit').value;
    const quantite = document.getElementById('mouv_quantite').value;
    const motif    = document.getElementById('mouv_motif').value;
    const unite    = document.getElementById('mouv_unite').value;

    if (!produit || !quantite) {
        toastr.warning('Produit et quantité obligatoires.');
        return;
    }

    document.getElementById('mouvSaveTxt').classList.add('d-none');
    document.getElementById('mouvSaveSpin').classList.remove('d-none');

    fetch(`/stocks/${type}`, {
        method: 'POST',
        headers: {
            'Content-Type':  'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ produit_id: produit, quantite, motif, unite }),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('mouvSaveTxt').classList.remove('d-none');
        document.getElementById('mouvSaveSpin').classList.add('d-none');
        if (d.success) {
            mouvementModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            toastr.error(d.message);
        }
    });
}

function openSeuilModal(stockId, seuil, unite) {
    document.getElementById('seuil_stock_id').value = stockId;
    document.getElementById('seuil_val').value      = seuil;
    document.getElementById('seuil_unite').value    = unite;
    seuilModal.show();
}

function saveSeuil() {
    const id    = document.getElementById('seuil_stock_id').value;
    const seuil = document.getElementById('seuil_val').value;
    const unite = document.getElementById('seuil_unite').value;

    fetch(`/stocks/${id}/seuil`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ seuil_alerte: seuil, unite }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            seuilModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 600);
        } else {
            toastr.error(d.message);
        }
    });
}

function showHistorique(stockId) {
    document.getElementById('historiqueBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>';
    historiqueModal.show();

    fetch(`/stocks/${stockId}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        const rows = d.mouvements.map(m => {
            const icons = { entree:'bi-box-arrow-in-down text-success', sortie:'bi-box-arrow-up text-danger', ajustement:'bi-arrow-repeat text-info' };
            return `<tr>
                <td><i class="bi ${icons[m.type] ?? 'bi-circle'} me-1"></i>${m.type}</td>
                <td class="fw-bold ${m.type==='entree'?'text-success':'text-danger'}">
                    ${m.type==='entree'?'+':'−'}${m.quantite} ${d.stock.unite}
                </td>
                <td>${m.quantite_avant} → ${m.quantite_apres}</td>
                <td>${m.motif ?? '—'}</td>
                <td style="font-size:11px;color:#9299a8">${m.user?.name ?? '—'}</td>
                <td style="font-size:11px;color:#9299a8">${new Date(m.created_at).toLocaleString('fr-FR', { timeZone: 'Europe/Paris' })}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center text-muted py-3">Aucun mouvement</td></tr>';

        document.getElementById('historiqueBody').innerHTML = `
            <div class="d-flex justify-content-between mb-3">
                <h6 class="fw-bold mb-0">${d.produit.nom}</h6>
                <span class="badge bg-secondary-subtle text-secondary fs-6">
                    Stock actuel : ${d.stock.quantite} ${d.stock.unite}
                </span>
            </div>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Type</th><th>Quantité</th><th>Avant → Après</th>
                        <th>Motif</th><th>Par</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;
    });
}
</script>
@endpush