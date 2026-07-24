@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page_title', 'Gestion des utilisateurs')
@section('breadcrumb', 'Administration')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="usr-header mb-5">
    <div>
        <h4 class="usr-title">Utilisateurs</h4>
        <p class="usr-sub" id="usr-count-label">{{ $users->count() }} compte(s) enregistré(s)</p>
    </div>
    <button class="btn-new-user" onclick="openUserModal()">
        <i class="bi bi-person-plus"></i>
        <span class="btn-new-user-label">Nouvel utilisateur</span>
    </button>
</div>

{{-- ══ STATS PAR RÔLE ══ --}}
<div class="row g-3 mb-5">
    @foreach($roles as $role)
    @php
        $count = $users->where('role_id', $role->id)->count();
        $roleColors = [
            'admin'     => ['#e07070','#e0707018'],
            'caissier'  => ['#c9a96e','#c9a96e18'],
            'serveur'   => ['#6b9fd4','#6b9fd418'],
            'cuisinier' => ['#7ec88a','#7ec88a18'],
        ];
        [$rc, $rbg] = $roleColors[$role->nom] ?? ['#b0b7c3','#b0b7c318'];
    @endphp
    <div class="col-6 col-md-3">
        <div class="role-stat" style="--rc:{{ $rc }};--rbg:{{ $rbg }}">
            <div class="role-stat-icon">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="role-stat-val">{{ $count }}</div>
            <div class="role-stat-lbl">{{ $role->label }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ BARRE FILTRES ══ --}}
<div class="usr-filter-bar mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="usr-search-wrap">
                <i class="bi bi-search usr-search-icon"></i>
                <input type="text" id="usr-search"
                    class="usr-search-input"
                    placeholder="Rechercher par nom ou email..."
                    oninput="filterUsers()">
                <button class="usr-search-clear d-none" id="usr-clear-btn" onclick="clearUserSearch()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select id="filter-role" class="form-select" onchange="filterUsers()">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select id="filter-statut" class="form-select" onchange="filterUsers()">
                <option value="">Tous statuts</option>
                <option value="1">Actifs</option>
                <option value="0">Inactifs</option>
            </select>
        </div>
    </div>
</div>

{{-- ══ TABLEAU ══ --}}
<div class="usr-panel">
    <div class="usr-table-scroll">
        <table class="usr-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th class="col-email">Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th class="col-date">Créé le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="usr-tbody">
                @forelse($users as $u)
                @php
                    $roleColors = [
                        'admin'     => ['danger',  '#e0707018', '#e07070'],
                        'caissier'  => ['warning', '#c9a96e18', '#c9a96e'],
                        'serveur'   => ['info',    '#6b9fd418', '#6b9fd4'],
                        'cuisinier' => ['success', '#7ec88a18', '#7ec88a'],
                    ];
                    [$rc, $rbg, $rcol] = $roleColors[$u->role->nom] ?? ['secondary','#b0b7c318','#b0b7c3'];
                    $initials = strtoupper(substr($u->name, 0, 2));
                @endphp
                <tr class="usr-row"
                    id="user-row-{{ $u->id }}"
                    data-name="{{ strtolower($u->name) }}"
                    data-email="{{ strtolower($u->email) }}"
                    data-role="{{ $u->role_id }}"
                    data-actif="{{ $u->actif ? '1' : '0' }}">

                    {{-- Avatar + nom (+ email visible en mobile) --}}
                    <td>
                        <div class="usr-identity">
                            <div class="usr-avatar {{ !$u->actif ? 'inactive' : '' }}"
                                style="--av:{{ $u->actif ? $rcol : '#b0b7c3' }}">
                                {{ $initials }}
                            </div>
                            <div class="usr-identity-text">
                                <div class="usr-name">
                                    {{ $u->name }}
                                    @if($u->id === auth()->id())
                                    <span class="usr-you">vous</span>
                                    @endif
                                </div>
                                <div class="usr-email-mobile">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Email (masqué sur mobile, visible dès md) --}}
                    <td class="usr-email col-email">{{ $u->email }}</td>

                    {{-- Rôle --}}
                    <td>
                        <span class="role-pill" style="background:{{ $rbg }};color:{{ $rcol }}">
                            {{ $u->role->label }}
                        </span>
                    </td>

                    {{-- Toggle statut --}}
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                {{ $u->actif ? 'checked' : '' }}
                                {{ $u->id === auth()->id() ? 'disabled' : '' }}
                                onchange="toggleUser({{ $u->id }}, this)">
                        </div>
                    </td>

                    {{-- Date (masquée sur mobile) --}}
                    <td class="usr-date col-date">{{ $u->created_at->format('d/m/Y') }}</td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="usr-actions">
                            <button class="ua-btn" onclick="openUserModal({{ $u->id }})" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if($u->id !== auth()->id())
                            <button class="ua-btn danger" onclick="deleteUser({{ $u->id }}, '{{ addslashes($u->name) }}')" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="usr-empty">
                            <i class="bi bi-people"></i>
                            <span>Aucun utilisateur enregistré.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- État vide après filtre --}}
    <div class="usr-empty d-none" id="usr-no-result">
        <i class="bi bi-search"></i>
        <span>Aucun utilisateur ne correspond à votre recherche.</span>
    </div>
</div>

{{-- ══ MODAL UTILISATEUR ══ --}}
<div class="modal fade" id="userModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="userModalTitle">Nouvel utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="u_id">

                {{-- Aperçu avatar --}}
                <div class="modal-avatar-preview mb-4" id="modal-avatar-preview">
                    <div class="map-avatar" id="map-av">??</div>
                    <div>
                        <div class="map-name" id="map-name">Nom complet</div>
                        <div class="map-role" id="map-role">Rôle</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-500">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" id="u_name" class="form-control"
                            placeholder="Prénom Nom" oninput="updateModalPreview()">
                        <div class="invalid-feedback" id="err_name"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                        <input type="email" id="u_email" class="form-control" placeholder="email@restopro.com">
                        <div class="invalid-feedback" id="err_email"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500">Rôle <span class="text-danger">*</span></label>
                        <select id="u_role" class="form-select" onchange="updateModalPreview()">
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-label="{{ $role->label }}">{{ $role->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500">
                            Mot de passe
                            <span id="pwd_required" class="text-danger">*</span>
                            <span id="pwd_optional" class="text-muted d-none" style="font-weight:400;font-size:12px">(laisser vide pour ne pas changer)</span>
                        </label>
                        <div class="input-group">
                            <input type="password" id="u_password" class="form-control" placeholder="••••••••">
                            <span class="input-group-text" style="cursor:pointer" onclick="togglePwd('u_password')">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback" id="err_password"></div>
                    </div>
                    <div class="col-12" id="pwd_confirm_wrap">
                        <label class="form-label fw-500">Confirmer le mot de passe</label>
                        <input type="password" id="u_password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="col-12" id="actif_wrap" style="display:none">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="u_actif" checked>
                            <label class="form-check-label fw-500" for="u_actif">Compte actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-dark px-5" onclick="saveUser()">
                    <span id="uSaveTxt"><i class="bi bi-check2 me-1"></i>Enregistrer</span>
                    <span id="uSaveSpin" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>

/* ══════════════════════════════════════
   TOKENS
══════════════════════════════════════ */
:root {
    --gold:   #c9a96e;
    --ink:    #1a1a2e;
    --muted:  #9299a8;
    --border: #eceef2;
    --bg:     #f7f8fa;
    --white:  #ffffff;
    --radius: 16px;
}

/* ══════════════════════════════════════
   HEADER
══════════════════════════════════════ */
.usr-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.usr-title {
    font-size: 22px; font-weight: 800;
    color: var(--ink); margin: 0 0 4px;
    letter-spacing: -.3px;
}
.usr-sub { font-size: 13px; color: var(--muted); margin: 0; }

.btn-new-user {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 20px;
    background: var(--ink); color: #fff;
    border: none; border-radius: 11px;
    font-size: 13.5px; font-weight: 600;
    cursor: pointer; transition: opacity .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.btn-new-user:hover { opacity: .85; }

/* ══════════════════════════════════════
   ROLE STATS
══════════════════════════════════════ */
.role-stat {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px 16px 14px;
    text-align: center;
    transition: box-shadow .2s, transform .15s;
}
.role-stat:hover {
    box-shadow: 0 8px 28px rgba(26,26,46,.08);
    transform: translateY(-2px);
}
.role-stat-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: var(--rbg);
    color: var(--rc);
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    margin: 0 auto 10px;
}
.role-stat-val  { font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1; margin-bottom: 4px; }
.role-stat-lbl  { font-size: 12px; color: var(--muted); font-weight: 500; }

/* ══════════════════════════════════════
   BARRE FILTRES
══════════════════════════════════════ */
.usr-filter-bar {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 16px;
}
.usr-search-wrap {
    position: relative;
    display: flex; align-items: center;
}
.usr-search-icon {
    position: absolute; left: 12px;
    color: var(--muted); font-size: 14px;
    pointer-events: none;
}
.usr-search-input {
    width: 100%;
    padding: 9px 36px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 13.5px;
    outline: none;
    transition: border-color .15s;
    font-family: inherit;
    color: var(--ink);
}
.usr-search-input:focus { border-color: var(--gold); }
.usr-search-clear {
    position: absolute; right: 10px;
    background: none; border: none;
    color: var(--muted); font-size: 16px;
    cursor: pointer; padding: 2px 4px; line-height: 1;
}

/* ══════════════════════════════════════
   TABLEAU
══════════════════════════════════════ */
.usr-panel {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
/* Le scroll horizontal ne s'active que si le contenu déborde réellement
   (colonnes email/date masquées sur mobile, donc rarement nécessaire) */
.usr-table-scroll {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.usr-table {
    width: 100%;
    min-width: 560px; /* évite l'écrasement des colonnes restantes */
    border-collapse: collapse;
}
.usr-table thead tr { border-bottom: 1px solid var(--border); }
.usr-table thead th {
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    white-space: nowrap;
}
.usr-row {
    border-bottom: 1px solid #f2f3f6;
    transition: background .1s;
}
.usr-row:last-child { border-bottom: none; }
.usr-row:hover { background: var(--bg); }
.usr-row td {
    padding: 14px 20px;
    font-size: 13px;
    vertical-align: middle;
}

/* Avatar + identité */
.usr-identity { display: flex; align-items: center; gap: 11px; }
.usr-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: var(--av);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 13px; color: #fff;
    flex-shrink: 0;
    opacity: 1;
    transition: opacity .15s;
}
.usr-avatar.inactive { opacity: .45; filter: grayscale(1); }
.usr-identity-text { min-width: 0; }
.usr-name {
    font-weight: 700; color: var(--ink); font-size: 13.5px;
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.usr-you  {
    display: inline-block;
    font-size: 10.5px; font-weight: 600;
    background: var(--gold);
    color: #fff;
    padding: 1px 7px;
    border-radius: 20px;
}
/* Email affiché sous le nom uniquement sur mobile, quand la colonne email est masquée */
.usr-email-mobile {
    display: none;
    font-size: 11.5px;
    color: var(--muted);
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 46vw;
}

.usr-email { color: var(--muted); font-size: 12.5px; }
.usr-date  { font-size: 12px; color: var(--muted); }

/* Role pill */
.role-pill {
    display: inline-block;
    padding: 3px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .02em;
    white-space: nowrap;
}

/* Actions */
.usr-actions { display: flex; justify-content: flex-end; gap: 5px; }
.ua-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: var(--white);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; color: var(--muted);
    cursor: pointer; transition: all .12s;
    flex-shrink: 0;
}
.ua-btn:hover           { background: var(--bg); color: var(--ink); border-color: #d0d4dc; }
.ua-btn.danger          { color: #e07070; border-color: #f8d7da; }
.ua-btn.danger:hover    { background: #fff0f0; }

/* Empty */
.usr-empty {
    display: flex; flex-direction: column;
    align-items: center; gap: 8px;
    padding: 44px 0;
    color: var(--muted); font-size: 13px;
    text-align: center;
}
.usr-empty i { font-size: 30px; opacity: .2; }

/* ══════════════════════════════════════
   MODAL
══════════════════════════════════════ */
.fw-500 { font-weight: 500; }

.modal-avatar-preview {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
}
.map-avatar {
    width: 46px; height: 46px;
    border-radius: 50%;
    background: var(--gold);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 15px; color: #fff;
    flex-shrink: 0;
    transition: background .2s;
}
.map-name { font-size: 14px; font-weight: 700; color: var(--ink); }
.map-role { font-size: 12px; color: var(--muted); }

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */

/* Tablette : on garde tout, on resserre un peu */
@media (max-width: 991.98px) {
    .usr-table thead th,
    .usr-row td { padding: 12px 14px; }
}

/* Mobile : on masque Email + Date en colonnes, on les replie dans l'identité,
   on allège les paddings et on empile l'en-tête si besoin */
@media (max-width: 767.98px) {
    .usr-title { font-size: 19px; }

    .btn-new-user { padding: 9px 14px; font-size: 13px; }

    .role-stat { padding: 14px 10px 12px; }
    .role-stat-icon { width: 32px; height: 32px; font-size: 15px; margin-bottom: 8px; }
    .role-stat-val { font-size: 21px; }
    .role-stat-lbl { font-size: 11px; }

    .usr-filter-bar { padding: 12px; }

    .col-email, .col-date { display: none; }
    .usr-email-mobile { display: block; }

    .usr-table { min-width: 0; }
    .usr-table thead th,
    .usr-row td { padding: 12px; }
    .usr-table thead th:first-child,
    .usr-row td:first-child { padding-left: 14px; }
    .usr-table thead th:last-child,
    .usr-row td:last-child { padding-right: 14px; }

    .usr-avatar { width: 34px; height: 34px; font-size: 12px; }
    .usr-name { font-size: 13px; }

    .ua-btn { width: 28px; height: 28px; }
}

/* Très petit écran : bouton "Nouvel utilisateur" en icône seule pour libérer
   de la place à côté du titre */
@media (max-width: 420px) {
    .btn-new-user-label { display: none; }
    .btn-new-user { padding: 10px; border-radius: 50%; }
}
</style>
@endpush

@push('scripts')
<script>
const userModal = new bootstrap.Modal('#userModal');

// Couleurs rôle pour l'avatar modal
const roleColorMap = {
    'admin':     '#e07070',
    'caissier':  '#c9a96e',
    'serveur':   '#6b9fd4',
    'cuisinier': '#7ec88a',
};

// ── FILTRE ────────────────────────────────────────────
function filterUsers() {
    const q      = document.getElementById('usr-search').value.toLowerCase().trim();
    const role   = document.getElementById('filter-role').value;
    const statut = document.getElementById('filter-statut').value;

    document.getElementById('usr-clear-btn').classList.toggle('d-none', !q);

    let visible = 0;
    document.querySelectorAll('.usr-row').forEach(row => {
        const matchQ = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
        const matchR = !role   || row.dataset.role   === role;
        const matchS = !statut || row.dataset.actif  === statut;
        const show   = matchQ && matchR && matchS;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('usr-no-result').classList.toggle('d-none', visible > 0);
    document.getElementById('usr-count-label').textContent =
        visible + ' utilisateur(s) affiché(s)';
}

function clearUserSearch() {
    document.getElementById('usr-search').value = '';
    filterUsers();
    document.getElementById('usr-search').focus();
}

// ── MODAL PREVIEW ─────────────────────────────────────
function updateModalPreview() {
    const name  = document.getElementById('u_name').value.trim() || 'Nom complet';
    const roleEl = document.getElementById('u_role');
    const roleLabel = roleEl.options[roleEl.selectedIndex]?.dataset.label || 'Rôle';
    const roleNom = roleEl.options[roleEl.selectedIndex]?.text || '';

    // Déterminer la couleur de l'avatar
    let avatarColor = '#c9a96e';
    for (const [nom, col] of Object.entries(roleColorMap)) {
        if (roleNom.toLowerCase().includes(nom)) { avatarColor = col; break; }
    }

    document.getElementById('map-av').textContent        = name.substring(0, 2).toUpperCase();
    document.getElementById('map-av').style.background   = avatarColor;
    document.getElementById('map-name').textContent      = name;
    document.getElementById('map-role').textContent      = roleLabel;
}

// ── TOGGLE MOT DE PASSE ───────────────────────────────
function togglePwd(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

// ── RESET FORM ────────────────────────────────────────
function resetUserForm() {
    ['u_id','u_name','u_email','u_password','u_password_confirmation'].forEach(i => {
        const el = document.getElementById(i);
        if (el) el.value = '';
    });
    ['u_name','u_email','u_password'].forEach(id => {
        document.getElementById(id)?.classList.remove('is-invalid');
    });
    ['err_name','err_email','err_password'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
    document.getElementById('u_actif').checked = true;
    updateModalPreview();
}

// ── OUVRIR MODAL ──────────────────────────────────────
function openUserModal(id = null) {
    resetUserForm();
    document.getElementById('userModalTitle').textContent =
        id ? "Modifier l'utilisateur" : 'Nouvel utilisateur';
    document.getElementById('pwd_required').classList.toggle('d-none', !!id);
    document.getElementById('pwd_optional').classList.toggle('d-none', !id);
    document.getElementById('actif_wrap').style.display = id ? '' : 'none';

    if (id) {
        fetch(`/admin/users/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            document.getElementById('u_id').value     = d.id;
            document.getElementById('u_name').value   = d.name;
            document.getElementById('u_email').value  = d.email;
            document.getElementById('u_role').value   = d.role_id;
            document.getElementById('u_actif').checked = !!d.actif;
            updateModalPreview();
        })
        .catch(() => toastr.error('Impossible de charger l\'utilisateur.'));
    }

    userModal.show();
}

// ── SAUVEGARDER ───────────────────────────────────────
function saveUser() {
    const id  = document.getElementById('u_id').value;
    const url = id ? `/admin/users/${id}` : '/admin/users';

    // Reset erreurs
    ['u_name','u_email','u_password'].forEach(i => {
        document.getElementById(i)?.classList.remove('is-invalid');
    });

    const payload = {
        name:                  document.getElementById('u_name').value.trim(),
        email:                 document.getElementById('u_email').value.trim(),
        role_id:               document.getElementById('u_role').value,
        actif:                 document.getElementById('u_actif').checked ? 1 : 0,
        password:              document.getElementById('u_password').value,
        password_confirmation: document.getElementById('u_password_confirmation').value,
    };

    document.getElementById('uSaveTxt').classList.add('d-none');
    document.getElementById('uSaveSpin').classList.remove('d-none');

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
        document.getElementById('uSaveTxt').classList.remove('d-none');
        document.getElementById('uSaveSpin').classList.add('d-none');

        if (d.success) {
            userModal.hide();
            toastr.success(d.message);
            setTimeout(() => location.reload(), 700);
        } else {
            // Afficher les erreurs de validation
            if (d.errors) {
                if (d.errors.name) {
                    document.getElementById('u_name').classList.add('is-invalid');
                    document.getElementById('err_name').textContent = d.errors.name[0];
                }
                if (d.errors.email) {
                    document.getElementById('u_email').classList.add('is-invalid');
                    document.getElementById('err_email').textContent = d.errors.email[0];
                }
                if (d.errors.password) {
                    document.getElementById('u_password').classList.add('is-invalid');
                    document.getElementById('err_password').textContent = d.errors.password[0];
                }
            } else {
                toastr.error(d.message ?? 'Erreur de validation.');
            }
        }
    })
    .catch(() => {
        document.getElementById('uSaveTxt').classList.remove('d-none');
        document.getElementById('uSaveSpin').classList.add('d-none');
        toastr.error('Erreur de connexion.');
    });
}

// ── TOGGLE ACTIF ──────────────────────────────────────
function toggleUser(id, checkbox) {
    fetch(`/admin/users/${id}/toggle`, {
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
            const row = checkbox.closest('.usr-row');
            if (row) row.dataset.actif = d.actif ? '1' : '0';
            // Griser l'avatar si inactif
            const avatar = row?.querySelector('.usr-avatar');
            if (avatar) avatar.classList.toggle('inactive', !d.actif);
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

// ── SUPPRIMER ─────────────────────────────────────────
function deleteUser(id, name) {
    Swal.fire({
        title: 'Supprimer cet utilisateur ?',
        html:  `<span style="color:#6b7280"><strong>${name}</strong> sera supprimé définitivement.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/admin/users/${id}`, {
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
                document.getElementById(`user-row-${id}`)?.remove();
                filterUsers();
            } else {
                toastr.error(d.message);
            }
        })
        .catch(() => toastr.error('Erreur de connexion.'));
    });
}
</script>
@endpush