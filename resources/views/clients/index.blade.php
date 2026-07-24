@extends('layouts.app')
@section('title', 'Clients')
@section('page_title', 'Clients')
@section('breadcrumb', 'Gestion de la clientèle')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4 header-actions">
    <div>
        <h5 class="mb-0 fw-bold">Clients</h5>
        <small class="text-muted" id="client-count-label">
            {{ $clients->count() }} client(s) enregistré(s)
        </small>
    </div>
    <button class="btn btn-dark px-4 btn-new-client" onclick="openClientModal()">
        <i class="bi bi-person-plus me-2"></i>Nouveau client
    </button>
</div>

{{-- ══ KPIs ══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:#e8f4fd;color:#0d6efd">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['total'] }}</div>
                <div class="kpi-label">Total clients</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:#fff3cd;color:#c9a96e">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['nb_commandes'] }}</div>
                <div class="kpi-label">Commandes totales</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:#d1e7dd;color:#198754">
                <i class="bi bi-graph-up"></i>
            </div>
            <div>
                <div class="kpi-val" style="font-size:18px">
                    {{ number_format($stats['ca_total'], 0, ',', ' ') }}<small style="font-size:12px"> F</small>
                </div>
                <div class="kpi-label">CA total généré</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:#f8d7da;color:#dc3545">
                <i class="bi bi-star"></i>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['fideles'] }}</div>
                <div class="kpi-label">Clients fidèles</div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTRES ══ --}}
<div class="filtre-bar mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="search-wrap">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="search-client"
                    class="search-input"
                    placeholder="Rechercher par nom, téléphone, email..."
                    oninput="filtrerClients()">
                <button class="search-clear d-none" id="search-clear-btn"
                    onclick="clearSearch()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select id="filter-fidelite" class="form-select" onchange="filtrerClients()">
                <option value="">Tous les clients</option>
                <option value="fidele">Fidèles (3+ cmds)</option>
                <option value="nouveau">Nouveaux (1 cmd)</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select id="sort-by" class="form-select" onchange="sortClients()">
                <option value="nom">Trier par nom</option>
                <option value="commandes">Par commandes</option>
                <option value="ca">Par CA généré</option>
            </select>
        </div>
    </div>
</div>

{{-- ══ TABLEAU (devient une liste de cartes sous 768px, voir CSS) ══ --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0" id="clients-table">
            <thead>
                <tr>
                    <th class="ps-4">Client</th>
                    <th>Contact</th>
                    <th class="text-center">Commandes</th>
                    <th class="text-end">CA généré</th>
                    <th>Fidélité</th>
                    <th>Points</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody id="clients-tbody">
                @forelse($clients as $cl)
                <tr id="cl-row-{{ $cl->id }}"
                    data-nom="{{ strtolower($cl->nom) }}"
                    data-tel="{{ strtolower($cl->telephone ?? '') }}"
                    data-email="{{ strtolower($cl->email ?? '') }}"
                    data-cmds="{{ $cl->commandes_count }}"
                    data-ca="{{ $cl->commandes_sum_total ?? 0 }}">

                    <td class="ps-4 cell-client">
                        <div class="d-flex align-items-center gap-3">
                            <div class="client-avatar"
                                style="background:{{ '#' . substr(md5($cl->nom), 0, 6) }}22;
                                       color:{{ '#' . substr(md5($cl->nom), 0, 6) }}">
                                {{ strtoupper(substr($cl->nom, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:13px">{{ $cl->nom }}</div>
                                @if($cl->commandes_count > 3)
                                <span class="badge-fidele">
                                    <i class="bi bi-star-fill me-1"></i>Fidèle
                                </span>
                                @elseif($cl->commandes_count === 0)
                                <span style="font-size:11px;color:#9299a8">Nouveau</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td data-label="Contact">
                        <div style="font-size:13px">
                            @if($cl->telephone)
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <i class="bi bi-phone text-muted" style="font-size:12px"></i>
                                {{ $cl->telephone }}
                            </div>
                            @endif
                            @if($cl->email)
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-envelope text-muted" style="font-size:12px"></i>
                                <span class="text-muted" style="font-size:12px">{{ $cl->email }}</span>
                            </div>
                            @endif
                            @if(!$cl->telephone && !$cl->email)
                            <span class="text-muted" style="font-size:12px">—</span>
                            @endif
                        </div>
                    </td>

                    <td class="text-center" data-label="Commandes">
                        <span class="commandes-badge">{{ $cl->commandes_count }}</span>
                    </td>

                    <td class="text-end" data-label="CA généré">
                        <span class="fw-bold" style="color:#c9a96e;font-size:13px">
                            {{ number_format($cl->commandes_sum_total ?? 0, 0, ',', ' ') }} F
                        </span>
                    </td>

                    <td data-label="Fidélité" class="cell-fidelite">
                        @php
                            $cmds = $cl->commandes_count;
                            $pct  = min(100, ($cmds / 10) * 100);
                            $color= $cmds >= 8 ? '#198754' : ($cmds >= 4 ? '#c9a96e' : '#dee2e6');
                        @endphp
                        <div style="width:80px">
                            <div style="height:5px;background:#f0f0f5;border-radius:3px;overflow:hidden">
                                <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};border-radius:3px;transition:width .3s"></div>
                            </div>
                            <div style="font-size:10px;color:#9299a8;margin-top:3px">{{ $cmds }}/10</div>
                        </div>
                    </td>

                    <td data-label="Points">
                        <span class="points-badge">
                            <i class="bi bi-coin me-1"></i>
                            {{ $cl->points_fidelite }}
                        </span>
                    </td>

                    <td class="text-end pe-4 cell-actions" data-label="Actions">
                        <div class="d-flex justify-content-end gap-1">
                            <button class="tbl-action-btn"
                                onclick="showClientDetail({{ $cl->id }})" title="Voir la fiche">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="tbl-action-btn"
                                onclick="openClientModal({{ $cl->id }})" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="tbl-action-btn danger"
                                onclick="deleteClient({{ $cl->id }}, '{{ addslashes($cl->nom) }}')"
                                title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted table-empty-cell">
                        <i class="bi bi-people d-block fs-1 mb-2 opacity-25"></i>
                        Aucun client enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="empty-state d-none" id="no-result">
            <i class="bi bi-search"></i>
            <h6>Aucun résultat</h6>
            <p>Aucun client ne correspond à votre recherche.</p>
        </div>
    </div>
</div>

{{-- ══ MODAL CLIENT ══ --}}
<div class="modal fade" id="clientModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="clientModalTitle">Nouveau client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cl_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-500">Nom complet <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" id="cl_nom" class="form-control"
                                placeholder="Prénom et nom du client">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-500">Téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="tel" id="cl_telephone" class="form-control"
                                placeholder="+229 XX XX XX XX">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-500">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="cl_email" class="form-control"
                                placeholder="email@exemple.com">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500">Adresse</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <textarea id="cl_adresse" class="form-control" rows="2"
                                placeholder="Adresse complète..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-5" onclick="saveClient()">
                    <span id="clSaveTxt"><i class="bi bi-check2 me-1"></i>Enregistrer</span>
                    <span id="clSaveSpin" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DÉTAIL CLIENT ══ --}}
<div class="modal fade" id="clientDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Fiche client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clientDetailBody">
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

/* ── EN-TÊTE ── */
@media (max-width: 575.98px) {
    .header-actions .btn-new-client { width: 100%; }
}

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
}
@media (max-width: 575.98px) {
    .filtre-bar #filter-fidelite,
    .filtre-bar #sort-by { font-size: 12.5px; padding-top: 7px; padding-bottom: 7px; }
}

/* ── KPI ── */
.kpi-card {
    background: #fff; border: 1px solid #eaeaef;
    border-radius: 14px; padding: 18px 20px;
    display: flex; align-items: center; gap: 14px;
    transition: box-shadow .15s;
    height: 100%;
}
.kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
.kpi-icon-wrap {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.kpi-val   { font-size: 22px; font-weight: 700; color: #1a1a2e; line-height: 1; }
.kpi-label { font-size: 11px; color: #9299a8; text-transform: uppercase; letter-spacing: .05em; margin-top: 3px; }
@media (max-width: 400px) {
    .kpi-card { padding: 12px 14px; gap: 10px; border-radius: 12px; }
    .kpi-icon-wrap { width: 36px; height: 36px; font-size: 16px; border-radius: 10px; }
    .kpi-val { font-size: 17px; }
    .kpi-label { font-size: 9.5px; }
}

/* ── TABLE ── */
.client-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.badge-fidele {
    display: inline-flex; align-items: center;
    background: #fff3cd; color: #856404;
    font-size: 10px; font-weight: 600;
    padding: 2px 7px; border-radius: 20px;
}
.commandes-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 50%;
    background: #f0f0f5; color: #374151;
    font-size: 12px; font-weight: 700;
}
.points-badge {
    display: inline-flex; align-items: center;
    background: rgba(201,169,110,.12); color: #c9a96e;
    font-size: 11px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px;
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

/* ── EMPTY ── */
.empty-state {
    text-align: center; padding: 48px 20px; color: #9299a8;
}
.empty-state i { font-size: 48px; opacity: .2; display: block; margin-bottom: 12px; }
.empty-state h6 { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.empty-state p  { font-size: 13px; }

/* ══════════════════════════════════════════════════════
   TABLEAU → CARTES EMPILÉES SOUS 768px
   Chaque ligne <tr> devient une carte, chaque <td> affiche
   son libellé via data-label (grâce à ::before).
══════════════════════════════════════════════════════ */
@media (max-width: 767.98px) {
    #clients-table thead { display: none; }

    #clients-table,
    #clients-table tbody,
    #clients-table tr,
    #clients-table td {
        display: block;
        width: 100%;
    }

    #clients-table tbody { padding: 12px; }

    #clients-table tr {
        margin: 0 0 12px;
        padding: 14px;
        border: 1px solid #eaeaef;
        border-radius: 14px;
        background: #fff;
    }
    #clients-table tr:last-child { margin-bottom: 0; }

    #clients-table td {
        border: none !important;
        padding: 6px 0;
        text-align: left !important;
    }

    /* Cellule "Client" = en-tête de la carte */
    #clients-table td.cell-client {
        padding: 0 0 10px;
        margin-bottom: 8px;
        border-bottom: 1px solid #f0f0f5 !important;
    }

    /* Libellé auto au-dessus de chaque valeur */
    #clients-table td[data-label]::before {
        content: attr(data-label);
        display: block;
        font-size: 10px; font-weight: 700;
        color: #9299a8; text-transform: uppercase;
        letter-spacing: .05em; margin-bottom: 3px;
    }

    /* La barre de fidélité prend toute la largeur de la carte */
    #clients-table td.cell-fidelite > div { width: 100% !important; }

    /* Actions : pas de libellé, boutons alignés à gauche, séparés par un trait */
    #clients-table td.cell-actions {
        padding-top: 10px;
        margin-top: 4px;
        border-top: 1px solid #f0f0f5 !important;
    }
    #clients-table td.cell-actions::before { display: none; }
    #clients-table td.cell-actions .d-flex { justify-content: flex-start; gap: 8px; }

    /* Ligne "aucun client" : on garde le centrage d'origine */
    #clients-table td.table-empty-cell {
        text-align: center !important;
        padding: 20px 0 !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
const clientModal       = new bootstrap.Modal('#clientModal');
const clientDetailModal = new bootstrap.Modal('#clientDetailModal');

// ── FILTRE ───────────────────────────────────────────
function filtrerClients() {
    const q       = document.getElementById('search-client').value.toLowerCase().trim();
    const fidelite= document.getElementById('filter-fidelite').value;

    document.getElementById('search-clear-btn').classList.toggle('d-none', !q);

    let visible = 0;
    document.querySelectorAll('#clients-tbody tr[data-nom]').forEach(tr => {
        const matchQ = !q ||
            tr.dataset.nom.includes(q) ||
            tr.dataset.tel.includes(q) ||
            tr.dataset.email.includes(q);

        const cmds   = parseInt(tr.dataset.cmds);
        const matchF = !fidelite ||
            (fidelite === 'fidele'  && cmds > 3) ||
            (fidelite === 'nouveau' && cmds <= 1);

        const show   = matchQ && matchF;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('no-result').classList.toggle('d-none', visible > 0);
    document.getElementById('client-count-label').textContent =
        visible + ' client(s) affiché(s)';
}

function clearSearch() {
    document.getElementById('search-client').value = '';
    filtrerClients();
    document.getElementById('search-client').focus();
}

// ── TRI ──────────────────────────────────────────────
function sortClients() {
    const by   = document.getElementById('sort-by').value;
    const tbody= document.getElementById('clients-tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-nom]'));

    rows.sort((a, b) => {
        if (by === 'nom')       return a.dataset.nom.localeCompare(b.dataset.nom);
        if (by === 'commandes') return parseInt(b.dataset.cmds) - parseInt(a.dataset.cmds);
        if (by === 'ca')        return parseFloat(b.dataset.ca) - parseFloat(a.dataset.ca);
        return 0;
    });

    rows.forEach(r => tbody.appendChild(r));
}

// ── MODAL ────────────────────────────────────────────
function openClientModal(id = null) {
    ['cl_id','cl_nom','cl_telephone','cl_email','cl_adresse']
        .forEach(i => { const el = document.getElementById(i); if(el) el.value = ''; });
    document.getElementById('clientModalTitle').textContent =
        id ? 'Modifier le client' : 'Nouveau client';

    if (id) {
        fetch(`/clients/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            document.getElementById('cl_id').value        = d.id;
            document.getElementById('cl_nom').value       = d.nom;
            document.getElementById('cl_telephone').value = d.telephone ?? '';
            document.getElementById('cl_email').value     = d.email ?? '';
            document.getElementById('cl_adresse').value   = d.adresse ?? '';
        });
    }
    clientModal.show();
}

function saveClient() {
    const id  = document.getElementById('cl_id').value;
    const url = id ? `/clients/${id}` : '/clients';

    const payload = {
        nom:       document.getElementById('cl_nom').value,
        telephone: document.getElementById('cl_telephone').value || null,
        email:     document.getElementById('cl_email').value || null,
        adresse:   document.getElementById('cl_adresse').value || null,
    };

    document.getElementById('clSaveTxt').classList.add('d-none');
    document.getElementById('clSaveSpin').classList.remove('d-none');

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
        document.getElementById('clSaveTxt').classList.remove('d-none');
        document.getElementById('clSaveSpin').classList.add('d-none');
        if (d.success) {
            clientModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            toastr.error(d.message ?? 'Erreur.');
        }
    });
}

// ── DÉTAIL ───────────────────────────────────────────
function showClientDetail(id) {
    document.getElementById('clientDetailBody').innerHTML =
        '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';
    clientDetailModal.show();

    fetch(`/clients/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        const initials = d.nom.substring(0, 2).toUpperCase();
        const hash     = d.nom.split('').reduce((a, c) => a + c.charCodeAt(0), 0);
        const hue      = hash % 360;
        const avgTicket= d.commandes_count > 0
            ? Math.round((d.commandes_sum_total ?? 0) / d.commandes_count)
            : 0;

        const cmdsHtml = (d.commandes ?? []).slice(0, 5).map(c => {
            const statusBadges = {
                en_attente:'#fff3cd;color:#856404',
                en_cuisson:'#cff4fc;color:#055160',
                prete:'#d1e7dd;color:#0a3622',
                payee:'#e2e3e5;color:#41464b',
                annulee:'#f8d7da;color:#842029',
            };
            const badge = statusBadges[c.statut] ?? '#f0f0f5;color:#374151';
            return `
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div>
                    <div style="font-size:13px;font-weight:600">${c.numero}</div>
                    <div style="font-size:11px;color:#9299a8">${new Date(c.created_at).toLocaleDateString('fr-FR', { timeZone: 'Europe/Paris' })}</div>
                </div>
                <div style="text-align:right">
                    <span style="display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;background:${badge}">
                        ${c.statut.replace('_',' ')}
                    </span>
                    <div style="font-size:13px;font-weight:700;color:#c9a96e;margin-top:2px">
                        ${Number(c.total).toLocaleString('fr')} F
                    </div>
                </div>
            </div>`;
        }).join('') || '<p class="text-muted text-center py-3" style="font-size:13px">Aucune commande</p>';

        document.getElementById('clientDetailBody').innerHTML = `
            <div class="row g-4">
                {{-- Profil --}}
                <div class="col-12 col-md-5">
                    <div class="text-center mb-3">
                        <div style="width:72px;height:72px;border-radius:50%;
                            background:hsl(${hue},60%,92%);color:hsl(${hue},60%,35%);
                            display:flex;align-items:center;justify-content:center;
                            font-size:24px;font-weight:800;margin:0 auto 12px">
                            ${initials}
                        </div>
                        <h5 class="fw-bold mb-1">${d.nom}</h5>
                        ${d.commandes_count > 3
                            ? '<span style="background:#fff3cd;color:#856404;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600"><i class="bi bi-star-fill me-1"></i>Client fidèle</span>'
                            : '<span style="font-size:12px;color:#9299a8">Client standard</span>'}
                    </div>

                    <div style="background:#f8f9fa;border-radius:12px;padding:14px">
                        ${d.telephone ? `
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-phone text-muted"></i>
                            <span style="font-size:13px">${d.telephone}</span>
                        </div>` : ''}
                        ${d.email ? `
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-envelope text-muted"></i>
                            <span style="font-size:12px;color:#6b7280">${d.email}</span>
                        </div>` : ''}
                        ${d.adresse ? `
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-muted"></i>
                            <span style="font-size:12px;color:#6b7280">${d.adresse}</span>
                        </div>` : ''}
                        ${!d.telephone && !d.email && !d.adresse
                            ? '<p class="text-muted mb-0" style="font-size:13px">Aucune information de contact</p>'
                            : ''}
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div style="background:#fff;border:1px solid #eaeaef;border-radius:10px;padding:12px;text-align:center">
                                <div style="font-size:20px;font-weight:700;color:#1a1a2e">${d.commandes_count}</div>
                                <div style="font-size:11px;color:#9299a8">Commandes</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#fff;border:1px solid #eaeaef;border-radius:10px;padding:12px;text-align:center">
                                <div style="font-size:14px;font-weight:700;color:#c9a96e">${Number(d.commandes_sum_total ?? 0).toLocaleString('fr')} F</div>
                                <div style="font-size:11px;color:#9299a8">CA total</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#fff;border:1px solid #eaeaef;border-radius:10px;padding:12px;text-align:center">
                                <div style="font-size:14px;font-weight:700;color:#1a1a2e">${Number(avgTicket).toLocaleString('fr')} F</div>
                                <div style="font-size:11px;color:#9299a8">Ticket moyen</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:rgba(201,169,110,.08);border:1px solid rgba(201,169,110,.2);border-radius:10px;padding:12px;text-align:center">
                                <div style="font-size:20px;font-weight:700;color:#c9a96e">${d.points_fidelite}</div>
                                <div style="font-size:11px;color:#9299a8">Points</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-outline-dark flex-fill btn-sm"
                            onclick="clientDetailModal.hide(); openClientModal(${d.id})">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </button>
                        <button class="btn btn-outline-danger btn-sm"
                            onclick="clientDetailModal.hide(); deleteClient(${d.id},'${d.nom.replace(/'/g,"\\'")}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                {{-- Historique --}}
                <div class="col-12 col-md-7">
                    <div style="font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:12px">
                        <i class="bi bi-clock-history me-2 text-muted"></i>
                        Historique des commandes
                    </div>
                    <div style="max-height:340px;overflow-y:auto">${cmdsHtml}</div>
                </div>
            </div>`;
    });
}

// ── SUPPRIMER ────────────────────────────────────────
function deleteClient(id, nom) {
    Swal.fire({
        title: 'Supprimer ce client ?',
        html:  `<span style="color:#6b7280"><strong>${nom}</strong> sera supprimé définitivement.</span>`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/clients/${id}`, {
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
                document.getElementById(`cl-row-${id}`)?.remove();
                filtrerClients();
            } else {
                toastr.error(d.message);
            }
        });
    });
}
</script>
@endpush