@extends('layouts.app')
@section('title', 'Commande '.$commande->numero)
@section('page_title', 'Commande '.$commande->numero)
@section('breadcrumb', 'Détail & modification')

@section('content')

@php
    // ✅ Annulation : jamais possible si déjà envoyée en cuisine au moins
    // une fois, même si le statut est redevenu "en_attente" (complément)
    $peutAnnuler = $commande->statut === 'en_attente' && !$commande->deja_envoyee_cuisine;

    $peutAjouter = in_array($commande->statut, ['en_attente', 'prete'], true);

    // ✅ Suppression d'une ligne : basée sur le statut PROPRE de l'article,
    // pas sur le statut global de la commande — un article reste
    // supprimable tant qu'il n'a jamais été transmis (statut en_attente),
    // peu importe sa vague.
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
@endphp

<div class="row g-4">

    {{-- INFO + ITEMS --}}
    <div class="col-lg-8">

        {{-- Infos générales --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 text-center">
                        <div class="text-muted" style="font-size:11px">STATUT</div>
                        <span class="badge badge-{{ $commande->statut }} fs-6 mt-1 px-3 py-2 rounded-pill">
                            {{ $labels[$commande->statut] }}
                        </span>
                        @if($plusieursVagues)
                        <div class="text-muted mt-1" style="font-size:11px">
                            <i class="bi bi-layers me-1"></i>{{ $itemsParVague->count() }} vagues
                        </div>
                        @endif
                    </div>
                    <div class="col-md-3 text-center border-start">
                        <div class="text-muted" style="font-size:11px">TABLE</div>
                        <div class="fw-bold fs-5">{{ $commande->table?->numero ?? '—' }}</div>
                        <small class="text-muted">{{ $commande->type === 'sur_place' ? 'Sur place' : ucfirst($commande->type) }}</small>
                    </div>
                    <div class="col-md-3 text-center border-start">
                        <div class="text-muted" style="font-size:11px">SERVEUR</div>
                        <div class="fw-bold">{{ $commande->serveur->name }}</div>
                        <small class="text-muted">{{ $commande->created_at->format('H:i') }}</small>
                    </div>
                    <div class="col-md-3 text-center border-start">
                        <div class="text-muted" style="font-size:11px">TOTAL</div>
                        <div class="fw-bold fs-4" style="color:#c9a96e">{{ number_format($commande->total, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Motif d'annulation --}}
        @if($commande->statut === 'annulee' && $commande->motif_annulation)
        <div class="card mb-4 border-0" style="background:#fdf2f2">
            <div class="card-body d-flex gap-3 align-items-start">
                <i class="bi bi-x-circle text-danger fs-4"></i>
                <div>
                    <div class="fw-bold text-danger" style="font-size:13px;text-transform:uppercase;letter-spacing:.04em">
                        Motif d'annulation
                    </div>
                    <p class="mb-0 text-muted mt-1">{{ $commande->motif_annulation }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Articles --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-2 px-4">
                <span class="fw-bold">Articles ({{ $commande->items->count() }})</span>
                @if($peutAjouter)
                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ $commande->statut === 'prete' ? 'Ajouter un complément' : 'Ajouter' }}
                </button>
                @else
                <span class="badge bg-light text-muted border" style="font-size:11px">
                    <i class="bi bi-lock-fill me-1"></i>Modification indisponible
                </span>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
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
                            <tr class="table-light">
                                <td colspan="6" class="py-2" style="font-size:11px;font-weight:700;color:#0d6efd;text-transform:uppercase;letter-spacing:.04em">
                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                    {{ $numVague == 1 ? 'Commande initiale' : 'Ajout — vague '.$numVague }}
                                </td>
                            </tr>
                            @endif
                            @foreach($items as $item)
                            @php $editable = $peutModifierLigne($item); @endphp
                            <tr id="item-row-{{ $item->id }}" class="{{ !$editable && $item->statut === 'prete' ? 'text-muted' : '' }}">
                                <td class="ps-4 fw-500">
                                    {{ $item->produit->nom }}
                                    @if($item->statut === 'prete' && $plusieursVagues)
                                        <i class="bi bi-check2-circle text-success ms-1" title="Déjà préparé"></i>
                                    @endif
                                </td>
                                <td class="text-center">{{ $fmtQty($item->quantite) }}</td>
                                <td class="text-end">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} F</td>
                                <td class="text-end fw-bold" style="color:#c9a96e">{{ number_format($item->sous_total, 0, ',', ' ') }} F</td>
                                <td style="font-size:12px;color:#9299a8">{{ $item->notes ?? '—' }}</td>
                                <td>
                                    @if($editable)
                                    <button class="btn btn-sm btn-outline-danger py-0"
                                        onclick="retirerItem({{ $commande->id }}, {{ $item->id }})">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot class="border-top">
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
                        <tr>
                            <td colspan="4" class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold fs-5" style="color:#c9a96e" colspan="2">
                                {{ number_format($commande->total, 0, ',', ' ') }} F
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-2 px-4 fw-bold">Actions</div>
            <div class="card-body d-flex flex-column gap-2">

                @if($commande->statut === 'prete')
                <a href="{{ route('caisse.index') }}" class="btn btn-warning w-100">
                    <i class="bi bi-cash-register me-2"></i>Encaisser
                </a>
                @endif

                @if($commande->statut === 'servie')
                <button class="btn btn-success w-100" onclick="changerStatut('payee')">
                    <i class="bi bi-check-circle me-2"></i>Marquer payée
                </button>
                @endif

                @if($commande->statut === 'en_attente' && $peutGererCuisine)
                <button class="btn btn-info w-100 text-white" onclick="changerStatut('en_cuisson')">
                    <i class="bi bi-fire me-2"></i>Envoyer en cuisine
                </button>
                @elseif($commande->statut === 'en_attente')
                <div class="alert alert-light border mb-0" style="font-size:12px">
                    <i class="bi bi-hourglass-split me-1"></i>En attente de prise en charge par la cuisine.
                </div>
                @endif

                {{-- ✅ Annulation : jamais dispo si déjà transmise en cuisine (même reconvertie en_attente) --}}
@if($peutAnnuler)
<hr class="my-1">
<button class="btn btn-outline-danger w-100" onclick="annulerCommande()">
    <i class="bi bi-x-circle me-2"></i>Annuler la commande
</button>
@elseif(!in_array($commande->statut, ['payee','annulee']))
<hr class="my-1">
<div class="alert alert-light border mb-0 text-center" style="font-size:12px">
    <i class="bi bi-lock-fill me-1"></i>
    {{ $commande->deja_envoyee_cuisine
        ? 'Commande déjà transmise en cuisine, annulation impossible'
        : 'Annulation indisponible à ce stade' }}
</div>
@endif

                <hr class="my-1">
                <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>
        </div>

        @if($commande->notes)
        <div class="card mt-3">
            <div class="card-body">
                <div class="fw-bold mb-1"><i class="bi bi-chat-left-text me-2"></i>Notes</div>
                <p class="text-muted mb-0">{{ $commande->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL AJOUTER ARTICLE --}}
@if($peutAjouter)
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    {{ $commande->statut === 'prete' ? 'Ajouter un complément (nouvelle vague)' : 'Ajouter des articles' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($commande->statut === 'prete')
                <div class="alert alert-info py-2" style="font-size:12.5px">
                    <i class="bi bi-info-circle me-1"></i>
                    Les plats déjà préparés resteront inchangés. Ce complément sera envoyé en cuisine comme une nouvelle demande.
                </div>
                @endif
                <input type="text" class="form-control mb-3" id="add-search"
                    placeholder="Rechercher un produit..." oninput="filterAddProduits(this.value)">
                <div class="row g-2" id="add-produits-grid">
                    @php
                        $tousProds = \App\Models\Produit::with('categorie')
                            ->where('disponible',true)->orderBy('ordre')->get();
                    @endphp
                    @foreach($tousProds as $p)
                    <div class="col-6 col-md-4 add-prod-item" data-search="{{ strtolower($p->nom) }}">
                        <div class="card prod-card" onclick="ajouterItemExistant({{ $p->id }}, '{{ addslashes($p->nom) }}', {{ $p->prix }})">
                            <div class="card-body p-2 text-center">
                                <div class="fw-500" style="font-size:13px">{{ $p->nom }}</div>
                                <div style="color:#c9a96e;font-size:12px;font-weight:600">{{ number_format($p->prix, 0, ',', ' ') }} F</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div id="add-panier" class="mt-3 border-top pt-3" hidden></div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-4" id="addItemBtn" onclick="confirmerAjout()" disabled>
                    <span id="addTxt"><i class="bi bi-check2 me-1"></i>
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
<style>
.fw-500 { font-weight: 500; }
.prod-card { cursor: pointer; transition: transform .1s, border-color .1s; }
.prod-card:hover { transform: translateY(-2px); border-color: #c9a96e; }
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