@extends('layouts.app')
@section('title', 'Tables')
@section('page_title', 'Plan de salle')
@section('breadcrumb', 'Gestion des tables')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Tables</h5>
        <small class="text-muted">
            {{ $tables->where('statut','libre')->count() }} libre(s) —
            {{ $tables->whereIn('statut',['occupee'])->count() }} occupée(s)
        </small>
    </div>
    <button class="btn btn-dark px-4" onclick="openTableModal()">
        <i class="bi bi-plus-lg me-2"></i>Nouvelle table
    </button>
</div>

{{-- LÉGENDE --}}
<div class="d-flex gap-3 mb-3">
    @foreach(['libre'=>['#198754','Libre'], 'occupee'=>['#c9a96e','Occupée'], 'reservee'=>['#0d6efd','Réservée']] as $s => [$c, $l])
    <div class="d-flex align-items-center gap-1">
        <div style="width:10px;height:10px;border-radius:3px;background:{{ $c }}"></div>
        <small class="text-muted">{{ $l }}</small>
    </div>
    @endforeach
</div>

{{-- FILTRE PAR STATUT --}}
<div class="d-flex flex-wrap gap-2 mb-4" id="filtre-statut-tables">
    <button type="button" class="btn btn-sm btn-dark statut-filter-btn active" data-statut=""
        onclick="filtrerTables('', this)">
        Toutes <span class="badge bg-secondary ms-1">{{ $tables->count() }}</span>
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary statut-filter-btn" data-statut="libre"
        onclick="filtrerTables('libre', this)">
        Libres <span class="badge bg-secondary ms-1">{{ $tables->where('statut','libre')->count() }}</span>
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary statut-filter-btn" data-statut="occupee"
        onclick="filtrerTables('occupee', this)">
        Occupées <span class="badge bg-secondary ms-1">{{ $tables->where('statut','occupee')->count() }}</span>
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary statut-filter-btn" data-statut="reservee"
        onclick="filtrerTables('reservee', this)">
        Réservées <span class="badge bg-secondary ms-1">{{ $tables->where('statut','reservee')->count() }}</span>
    </button>
</div>

{{-- PLAN DE SALLE --}}
<div class="row g-3" id="grille-tables">
    @forelse($tables as $table)
    @php
        $colors = ['libre'=>'#198754','occupee'=>'#c9a96e','reservee'=>'#0d6efd'];
        $c = $colors[$table->statut] ?? '#6c757d';
        $cmd = $table->commandeActive;
    @endphp
    <div class="col-6 col-md-4 col-lg-3 col-xl-2" id="table-card-{{ $table->id }}" data-statut="{{ $table->statut }}">
        <div class="card table-plan-card" style="border-top:3px solid {{ $c }};cursor:pointer"
            onclick="showTableDetail({{ $table->id }})">
            <div class="card-body text-center py-3">
                <div style="font-size:32px;color:{{ $c }}">
                    <i class="bi bi-{{ $table->statut === 'libre' ? 'circle' : 'circle-fill' }}"></i>
                </div>
                <div class="fw-bold fs-5 my-1">T{{ $table->numero }}</div>
                @if($table->nom)
                    <div class="text-muted" style="font-size:11px">{{ $table->nom }}</div>
                @endif
                <div class="d-flex justify-content-center align-items-center gap-1 mt-1">
                    <i class="bi bi-people text-muted" style="font-size:12px"></i>
                    <small class="text-muted">{{ $table->capacite }} pers.</small>
                </div>
                @if($cmd)
                <div class="mt-2 p-1 rounded" style="background:{{ $c }}15;font-size:11px;color:{{ $c }}">
                    {{ $cmd->numero }} · {{ number_format($cmd->total,0,',',' ') }} F
                </div>
                @endif
                @if($table->statut === 'reservee' && $table->motif_reservation)
                <div class="mt-1 px-1" style="font-size:10.5px;color:#0d6efd;font-style:italic;line-height:1.3">
                    <i class="bi bi-info-circle me-1"></i>{{ $table->motif_reservation }}
                </div>
                @endif
            </div>
            <div class="card-footer border-0 bg-transparent p-2 d-flex justify-content-center gap-2">
                {{-- Ajouter ce bouton dans les actions de chaque table card --}}
                        <button class="btn btn-sm btn-outline-secondary py-0"
                    onclick="event.stopPropagation();voirQR('{{ $table->uuid }}', '{{ $table->numero }}')"
                    title="Code QR">
                    <i class="bi bi-qr-code"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary py-0" onclick="event.stopPropagation();openTableModal({{ $table->id }})">
                    <i class="bi bi-pencil"></i>
                </button>
                @if($table->statut === 'libre')
                <button class="btn btn-sm btn-outline-primary py-0" onclick="event.stopPropagation();reserverTable({{ $table->id }}, '{{ $table->numero }}')">
                    <i class="bi bi-calendar2-check"></i>
                </button>
                @elseif($table->statut === 'reservee')
                <button class="btn btn-sm btn-outline-success py-0" onclick="event.stopPropagation();terminerTable({{ $table->id }}, '{{ $table->numero }}')">
                    <i class="bi bi-check2-circle"></i>
                </button>
                @endif
                <button class="btn btn-sm btn-outline-danger py-0" onclick="event.stopPropagation();deleteTable({{ $table->id }}, '{{ $table->numero }}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-grid fs-1 d-block mb-2 opacity-25"></i>Aucune table configurée.
    </div>
    @endforelse
</div>

{{-- Message affiché si le filtre ne retourne aucune table --}}
<div class="col-12 text-center py-5 text-muted d-none" id="filtre-tables-vide">
    <i class="bi bi-funnel fs-1 d-block mb-2 opacity-25"></i>Aucune table pour ce statut.
</div>

{{-- MODAL CREATE/EDIT TABLE --}}
<div class="modal fade" id="tableModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="tableModalTitle">Nouvelle table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tbl_id">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-500">Numéro <span class="text-danger">*</span></label>
                        <input type="text" id="tbl_numero" class="form-control" placeholder="1, 2A, VIP...">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-500">Capacité <span class="text-danger">*</span></label>
                        <input type="number" id="tbl_capacite" class="form-control" value="4" min="1">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500">Nom / Description</label>
                        <input type="text" id="tbl_nom" class="form-control" placeholder="Ex: Terrasse, Salle VIP...">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="tbl_actif" checked>
                            <label class="form-check-label" for="tbl_actif">Table active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-4" onclick="saveTable()">
                    <span id="tblSaveTxt"><i class="bi bi-check2 me-1"></i>Enregistrer</span>
                    <span id="tblSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL TABLE --}}
<div class="modal fade" id="tableDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Détail table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="tableDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL QR CODE --}}
{{-- MODAL QR CODE --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content border-0 shadow text-center">
            <div class="modal-header border-0 pb-0 justify-content-center position-relative">
                <h5 class="modal-title fw-bold" id="qr-modal-title">Code QR Table</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">

                {{-- QR Code affiché ici --}}
                <div id="qr-wrap"
                    style="background:#fff;border:2px solid #eaeaef;border-radius:16px;
                    padding:20px;margin:16px auto;display:inline-block">
                    <div id="qr-container"></div>
                </div>

                {{-- Nom table + URL --}}
                <div class="mb-1 fw-bold" id="qr-table-label" style="font-size:15px"></div>
                <div class="text-muted mb-3" id="qr-url"
                    style="font-size:10px;word-break:break-all;
                    background:#f8f9fa;border-radius:8px;padding:6px 10px">
                </div>

                <p class="text-muted mb-3" style="font-size:12px">
                    <i class="bi bi-phone me-1"></i>
                    Le client scanne ce QR avec son téléphone pour accéder au menu
                </p>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-dark flex-fill" onclick="imprimerQR()">
                        <i class="bi bi-printer me-1"></i>Imprimer
                    </button>
                    <button class="btn btn-dark flex-fill" onclick="telechargerQR()">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.table-plan-card { transition: transform .15s, box-shadow .15s; }
.table-plan-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.1) !important; }
.fw-500 { font-weight: 500; }
.statut-filter-btn { transition: all .15s; }
</style>
@endpush

@push('scripts')
<script>
const tableModal       = new bootstrap.Modal('#tableModal');
const tableDetailModal = new bootstrap.Modal('#tableDetailModal');

function openTableModal(id = null) {
    document.getElementById('tbl_id').value       = '';
    document.getElementById('tbl_numero').value   = '';
    document.getElementById('tbl_nom').value      = '';
    document.getElementById('tbl_capacite').value = '4';
    document.getElementById('tbl_actif').checked  = true;
    document.getElementById('tableModalTitle').textContent = id ? 'Modifier la table' : 'Nouvelle table';

    if (id) {
        fetch(`/tables/${id}`)
            .then(r => r.json())
            .then(d => {
                document.getElementById('tbl_id').value       = d.id;
                document.getElementById('tbl_numero').value   = d.numero;
                document.getElementById('tbl_nom').value      = d.nom ?? '';
                document.getElementById('tbl_capacite').value = d.capacite;
                document.getElementById('tbl_actif').checked  = !!d.actif;
            });
    }
    tableModal.show();
}

function saveTable() {
    const id  = document.getElementById('tbl_id').value;
    const url = id ? `/tables/${id}` : '/tables';

    const payload = {
        numero:   document.getElementById('tbl_numero').value,
        nom:      document.getElementById('tbl_nom').value,
        capacite: document.getElementById('tbl_capacite').value,
        actif:    document.getElementById('tbl_actif').checked ? 1 : 0,
        _token:   '{{ csrf_token() }}',
    };

    document.getElementById('tblSaveTxt').classList.add('d-none');
    document.getElementById('tblSaveSpinner').classList.remove('d-none');

    fetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('tblSaveTxt').classList.remove('d-none');
        document.getElementById('tblSaveSpinner').classList.add('d-none');
        if (d.success) {
            tableModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            toastr.error(d.message ?? 'Erreur de validation.');
        }
    });
}

function showTableDetail(id) {
    document.getElementById('tableDetailBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>';
    tableDetailModal.show();

    fetch(`/tables/${id}`)
        .then(r => r.json())
        .then(d => {
            const cmd = d.commande_active;
            const statusColors = { libre:'#198754', occupee:'#c9a96e', reservee:'#0d6efd' };
            const sc = statusColors[d.statut] ?? '#6c757d';

            let cmdHtml = cmd ? `
                <div class="p-3 rounded-3 mt-3" style="background:#f8f9fa">
                    <div class="fw-bold mb-1">${cmd.numero}</div>
                    <div class="text-muted" style="font-size:12px">${cmd.items?.length ?? 0} article(s) — <strong>${Number(cmd.total).toLocaleString('fr')} F</strong></div>
                    <a href="/commandes/${cmd.id}" class="btn btn-sm btn-outline-dark mt-2 w-100">
                        <i class="bi bi-receipt me-1"></i>Voir la commande
                    </a>
                </div>` : `<div class="text-center text-muted py-2">Aucune commande active</div>`;

            document.getElementById('tableDetailBody').innerHTML = `
                <div class="text-center mb-3">
                    <div style="font-size:48px;color:${sc}">
                        <i class="bi bi-${d.statut === 'libre' ? 'circle' : 'circle-fill'}"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Table ${d.numero}</h4>
                    ${d.nom ? `<small class="text-muted">${d.nom}</small>` : ''}
                    <div class="mt-1">
                        <span class="badge" style="background:${sc}20;color:${sc}">${d.statut}</span>
                    </div>
                </div>
                <table class="table table-sm">
                    <tr><td class="text-muted" width="40%">Capacité</td><td>${d.capacite} personne(s)</td></tr>
                    <tr><td class="text-muted">Statut</td><td><span class="badge" style="background:${sc}">${d.statut}</span></td></tr>
                    ${d.statut === 'reservee' && d.motif_reservation ? `
                    <tr><td class="text-muted">Motif</td><td>${d.motif_reservation}</td></tr>` : ''}
                </table>
                <div class="fw-500 mb-1">Commande en cours</div>
                ${cmdHtml}
                <div class="d-flex gap-2 mt-3">
                    ${d.statut === 'libre' ? `<button class="btn btn-outline-primary flex-fill" onclick="tableDetailModal.hide();reserverTable(${d.id}, '${d.numero}')">
                        <i class="bi bi-calendar2-check me-1"></i>Réserver
                    </button>` : ''}
                    ${d.statut === 'reservee' ? `<button class="btn btn-outline-success flex-fill" onclick="tableDetailModal.hide();terminerTable(${d.id}, '${d.numero}')">
                        <i class="bi bi-check2-circle me-1"></i>Terminer
                    </button>` : ''}
                    <button class="btn btn-outline-dark flex-fill" onclick="tableDetailModal.hide();openTableModal(${d.id})">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </button>
                </div>`;
        });
}

function changerStatutTable(id, statut, label, motif = null) {
    const body = { statut };
    if (statut === 'reservee') body.motif = motif || null;
    if (statut === 'libre')    body.motif = null;

    fetch(`/tables/${id}/statut`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(body),
    }).then(r => r.json()).then(d => {
        if (d.success) {
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            toastr.error(d.message || 'Erreur');
        }
    });
}

function reserverTable(id, num) {
    Swal.fire({
        title: `Réserver la table ${num} ?`,
        html: `
            <p class="text-muted mb-2" style="font-size:13.5px">
                La table sera marquée comme réservée jusqu'au départ du client.
            </p>
            <div class="text-start">
                <label class="form-label mb-1" style="font-size:12.5px;color:#6b7280">
                    Motif de la réservation (optionnel)
                </label>
                <input type="text" id="swal-motif-reservation" class="form-control"
                    placeholder="Ex: Anniversaire, groupe de 8, client VIP...">
            </div>`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Réserver',
        preConfirm: () => document.getElementById('swal-motif-reservation').value.trim(),
    }).then(r => {
        if (r.isConfirmed) changerStatutTable(id, 'reservee', 'réservée', r.value || null);
    });
}

function terminerTable(id, num) {
    Swal.fire({
        title: `Terminer la réservation de la table ${num} ?`,
        text: 'La table sera libérée et disponible à nouveau.',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#198754', cancelButtonText: 'Annuler', confirmButtonText: 'Terminer',
    }).then(r => { if (r.isConfirmed) changerStatutTable(id, 'libre', 'libre'); });
}

function deleteTable(id, num) {
    Swal.fire({
        title: 'Supprimer la table ?',
        text: `La table ${num} sera supprimée définitivement.`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/tables/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        }).then(r => r.json()).then(d => {
            if (d.success) {
                toastr.success(d.message);
                document.getElementById(`table-card-${id}`)?.remove();
            } else {
                toastr.error(d.message);
            }
        });
    });
}

// ══════════════════════════════════════════════════════
// FILTRE PAR STATUT
// ══════════════════════════════════════════════════════
function filtrerTables(statut, btn) {
    document.querySelectorAll('.statut-filter-btn').forEach(b => {
        b.classList.remove('active', 'btn-dark');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('active', 'btn-dark');

    let visibles = 0;
    document.querySelectorAll('#grille-tables > [id^="table-card-"]').forEach(el => {
        const correspond = !statut || el.dataset.statut === statut;
        el.style.display = correspond ? '' : 'none';
        if (correspond) visibles++;
    });

    document.getElementById('filtre-tables-vide').classList.toggle('d-none', visibles > 0);
}
</script>
@endpush

@push('scripts')
<script>
const qrModal = new bootstrap.Modal('#qrModal');
let currentQRUrl   = '';
let currentTableNum = '';
const qrBaseUrl = '{{ url('menu/table') }}';

function voirQR(uuid, numero) {
    currentQRUrl    = `${qrBaseUrl}/${uuid}`;
    currentTableNum = numero;

    document.getElementById('qr-modal-title').textContent  = `Code QR — Table ${numero}`;
    document.getElementById('qr-table-label').textContent  = `Table ${numero}`;
    document.getElementById('qr-url').textContent          = currentQRUrl;

    // Vider le container
    document.getElementById('qr-container').innerHTML = '';

    qrModal.show();

    // Attendre que le modal soit visible avant de générer
    document.getElementById('qrModal').addEventListener('shown.bs.modal', genererQR, { once: true });
}

function genererQR() {
    const container = document.getElementById('qr-container');
    container.innerHTML = '';

    // ✅ Méthode 1 : Google Charts API (toujours disponible, fiable)
    const size = 200;
    const url  = encodeURIComponent(currentQRUrl);
    const img  = document.createElement('img');
    img.src    = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${url}&format=png&margin=0&color=0f1117&bgcolor=ffffff`;
    img.alt    = 'Code QR Table ' + currentTableNum;
    img.style.cssText = `width:${size}px;height:${size}px;display:block;border-radius:4px`;
    img.id     = 'qr-image';

    // Fallback si l'image ne charge pas
    img.onerror = () => {
        container.innerHTML = `
            <div style="width:200px;height:200px;display:flex;align-items:center;justify-content:center;
                background:#f8f9fa;border-radius:8px;color:#9299a8;font-size:13px;text-align:center;padding:16px">
                <div>
                    <i class="bi bi-wifi-off d-block fs-2 mb-2"></i>
                    QR non disponible hors ligne.<br>
                    <small>Copiez le lien ci-dessous.</small>
                </div>
            </div>`;
    };

    container.appendChild(img);
}

function imprimerQR() {
    const img    = document.getElementById('qr-image');
    const titre  = document.getElementById('qr-modal-title').textContent;
    const resto  = '{{ \App\Models\Parametre::get("restaurant_nom","RestoPro") }}';

    const win = window.open('', '_blank', 'width=400,height=600');
    win.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<title>${titre}</title>
<style>
  * { margin:0;padding:0;box-sizing:border-box; }
  body {
    font-family:'Segoe UI',sans-serif;
    text-align:center;
    padding:32px 24px;
    background:#fff;
  }
  .resto { font-size:18px; font-weight:700; color:#0f1117; margin-bottom:4px; }
  .table-name {
    font-size:28px; font-weight:800; color:#0f1117;
    margin:12px 0;
  }
  .qr-wrap {
    border:2px solid #eaeaef; border-radius:16px;
    padding:20px; display:inline-block; margin:16px 0;
  }
  .qr-wrap img { width:200px;height:200px;display:block; }
  .instruction {
    font-size:13px; color:#6b7280;
    margin:8px 0 16px;
  }
  .url {
    font-size:9px; color:#9ca3af;
    word-break:break-all; margin-top:8px;
  }
  .separator { border:none; border-top:1px dashed #eaeaef; margin:16px 0; }
</style>
</head>
<body>
  <div class="resto">🍽 ${resto}</div>
  <hr class="separator">
  <div class="table-name">${titre.replace('Code QR — ','')}</div>
  <div class="instruction">
    📱 Scannez pour voir le menu & commander
  </div>
  <div class="qr-wrap">
    <img src="${img ? img.src : ''}" alt="Code QR">
  </div>
  <div class="instruction">Commandez directement depuis votre téléphone !</div>
  <div class="url">${currentQRUrl}</div>
</body></html>`);
    win.document.close();
    setTimeout(() => { win.print(); win.close(); }, 800);
}

function telechargerQR() {
    // Télécharger l'image QR directement
    const size = 400;
    const url  = encodeURIComponent(currentQRUrl);
    const src  = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${url}&format=png&margin=10&color=0f1117&bgcolor=ffffff`;

    const link     = document.createElement('a');
    link.href      = src;
    link.download  = `qrcode-table-${currentTableNum}.png`;
    link.target    = '_blank';
    link.click();
}
</script>
@endpush