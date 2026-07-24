@extends('layouts.app')
@section('title', 'Mon compte')
@section('page_title', 'Mon compte')
@section('breadcrumb', 'Paramètres personnels')

@section('content')

<div class="profil-layout">

    {{-- ══ COLONNE GAUCHE — Carte identité ══ --}}
    <aside class="profil-aside">

        <div class="id-card">
            <div class="id-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            <div class="id-name">{{ $user->name }}</div>
            <div class="id-role">{{ $user->role->label }}</div>
            <div class="id-email">
                <i class="bi bi-envelope me-1"></i>{{ $user->email }}
            </div>
            <div class="id-divider"></div>
            <div class="id-meta-row">
                <div class="id-meta-item">
                    <div class="id-meta-label">Membre depuis</div>
                    <div class="id-meta-val">{{ $user->created_at->translatedFormat('d F Y') }}</div>
                </div>
                <div class="id-meta-item">
                    <div class="id-meta-label">Statut</div>
                    <div class="id-meta-val">
                        <span class="status-dot"></span> Actif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sécurité --}}
        <div class="side-sec-card">
            <div class="ssc-title">
                <i class="bi bi-shield-check me-2"></i>Sécurité
            </div>
            <div class="ssc-list">
                <div class="ssc-item">
                    <div class="ssc-item-icon"><i class="bi bi-person-badge"></i></div>
                    <div>
                        <div class="ssc-item-label">Rôle</div>
                        <div class="ssc-item-val">{{ $user->role->label }}</div>
                    </div>
                </div>
                <div class="ssc-item">
                    <div class="ssc-item-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="ssc-item-label">Dernière connexion</div>
                        <div class="ssc-item-val">{{ now()->translatedFormat('d F Y à H:i') }}</div>
                    </div>
                </div>
                <div class="ssc-item">
                    <div class="ssc-item-icon"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="ssc-item-label">Compte</div>
                        <div class="ssc-item-val text-success">Actif</div>
                    </div>
                </div>
            </div>
            <div class="ssc-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Se déconnecter de tous les appareils
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ══ COLONNE DROITE — Formulaires ══ --}}
    <div class="profil-main">

        {{-- Infos personnelles --}}
        <div class="profil-section">
            <div class="ps-header">
                <div class="ps-header-icon"><i class="bi bi-person"></i></div>
                <div>
                    <div class="ps-title">Informations personnelles</div>
                    <div class="ps-sub">Modifiez votre nom et votre adresse email</div>
                </div>
            </div>
            <div class="ps-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">Nom complet</label>
                            <input type="text" id="info_name" class="rp-input"
                                value="{{ $user->name }}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">Adresse email</label>
                            <input type="email" id="info_email" class="rp-input"
                                value="{{ $user->email }}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">Rôle</label>
                            <input type="text" class="rp-input rp-disabled"
                                value="{{ $user->role->label }}" disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">Membre depuis</label>
                            <input type="text" class="rp-input rp-disabled"
                                value="{{ $user->created_at->translatedFormat('d F Y') }}" disabled>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="rp-btn-save" onclick="saveInfos()">
                            <span id="info-txt">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                            </span>
                            <span id="info-spin" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mot de passe --}}
        <div class="profil-section">
            <div class="ps-header">
                <div class="ps-header-icon"><i class="bi bi-lock"></i></div>
                <div>
                    <div class="ps-title">Changer le mot de passe</div>
                    <div class="ps-sub">Utilisez un mot de passe fort et unique</div>
                </div>
            </div>
            <div class="ps-body">
                <div class="row g-3">

                    <div class="col-12">
                        <div class="rp-field">
                            <label class="rp-label">
                                Mot de passe actuel <span class="rp-req">*</span>
                            </label>
                            <div class="pwd-wrap">
                                <input type="password" id="current_password"
                                    class="rp-input rp-input--pwd"
                                    placeholder="Votre mot de passe actuel">
                                <button class="pwd-eye" type="button"
                                    onclick="togglePwd('current_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="rp-error d-none" id="err-current">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <span id="err-current-txt"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">
                                Nouveau mot de passe <span class="rp-req">*</span>
                            </label>
                            <div class="pwd-wrap">
                                <input type="password" id="new_password"
                                    class="rp-input rp-input--pwd"
                                    placeholder="Minimum 6 caractères"
                                    oninput="checkStrength(this.value)">
                                <button class="pwd-eye" type="button"
                                    onclick="togglePwd('new_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="strength-bar mt-2" id="strength-bar" style="display:none">
                                <div class="strength-track">
                                    <div class="strength-fill" id="strength-fill"></div>
                                </div>
                                <div class="strength-label" id="strength-label"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="rp-field">
                            <label class="rp-label">
                                Confirmer le mot de passe <span class="rp-req">*</span>
                            </label>
                            <div class="pwd-wrap">
                                <input type="password" id="confirm_password"
                                    class="rp-input rp-input--pwd"
                                    placeholder="Répéter le mot de passe"
                                    oninput="checkMatch()">
                                <button class="pwd-eye" type="button"
                                    onclick="togglePwd('confirm_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="match-msg d-none" id="match-msg"></div>
                        </div>
                    </div>

                    {{-- Règles --}}
                    <div class="col-12">
                        <div class="pwd-rules">
                            <div class="pwd-rule" id="rule-len">
                                <i class="bi bi-circle"></i>
                                Minimum 6 caractères
                            </div>
                            <div class="pwd-rule" id="rule-upper">
                                <i class="bi bi-circle"></i>
                                Une lettre majuscule
                            </div>
                            <div class="pwd-rule" id="rule-num">
                                <i class="bi bi-circle"></i>
                                Un chiffre
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="rp-btn-save" id="btn-pwd" onclick="savePassword()">
                            <span id="pwd-txt">
                                <i class="bi bi-shield-check me-1"></i>
                                Changer le mot de passe
                            </span>
                            <span id="pwd-spin"
                                class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- fin profil-main --}}
</div>

@endsection

@push('styles')
<style>

/* ══════════════════════════════
   LAYOUT
══════════════════════════════ */
.profil-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 991px) {
    .profil-layout { grid-template-columns: 1fr; }
}

/* ══════════════════════════════
   ASIDE — CARTE IDENTITÉ
══════════════════════════════ */
.profil-aside { display: flex; flex-direction: column; gap: 16px; }

.id-card {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 16px;
    padding: 28px 24px;
    text-align: center;
}
.id-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #1a1a2e;
    color: #fff;
    font-size: 24px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
    letter-spacing: 1px;
}
.id-name {
    font-size: 17px; font-weight: 700;
    color: #1a1a2e; margin-bottom: 3px;
}
.id-role {
    display: inline-block;
    font-size: 11px; font-weight: 700;
    color: #6b7280;
    background: #f3f4f6;
    padding: 3px 12px; border-radius: 20px;
    text-transform: uppercase; letter-spacing: .07em;
    margin-bottom: 8px;
}
.id-email {
    font-size: 12.5px; color: #9299a8;
}
.id-divider {
    height: 1px; background: #f0f0f5;
    margin: 18px 0;
}
.id-meta-row {
    display: flex; justify-content: space-around; gap: 12px;
}
.id-meta-item { text-align: center; }
.id-meta-label {
    font-size: 10px; font-weight: 700;
    color: #b0b7c3; text-transform: uppercase;
    letter-spacing: .07em; margin-bottom: 4px;
}
.id-meta-val {
    font-size: 12px; font-weight: 600; color: #374151;
    display: flex; align-items: center; justify-content: center; gap: 5px;
}
.status-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e; display: inline-block;
}

/* Sécurité sidebar */
.side-sec-card {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 16px;
    overflow: hidden;
}
.ssc-title {
    padding: 16px 20px 12px;
    font-size: 12px; font-weight: 700;
    color: #374151;
    text-transform: uppercase; letter-spacing: .07em;
    border-bottom: 1px solid #f0f0f5;
}
.ssc-list { padding: 8px 0; }
.ssc-item {
    display: flex; align-items: center;
    gap: 12px; padding: 11px 20px;
    border-bottom: 1px solid #f8f9fa;
    transition: background .1s;
}
.ssc-item:last-child { border-bottom: none; }
.ssc-item:hover { background: #fafafa; }
.ssc-item-icon {
    width: 32px; height: 32px; border-radius: 9px;
    background: #f3f4f6; color: #6b7280;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.ssc-item-label {
    font-size: 10px; font-weight: 600;
    color: #9299a8; text-transform: uppercase; letter-spacing: .06em;
}
.ssc-item-val {
    font-size: 12.5px; font-weight: 600; color: #1a1a2e; margin-top: 1px;
}
.ssc-footer {
    padding: 14px 20px;
    border-top: 1px solid #f0f0f5;
    background: #fafafa;
}
.logout-btn {
    width: 100%; border: 1px solid #fde8e8;
    background: #fff; color: #dc3545;
    border-radius: 9px; padding: 9px 14px;
    font-size: 12.5px; font-weight: 600;
    cursor: pointer; transition: all .12s;
    display: flex; align-items: center; justify-content: center;
}
.logout-btn:hover { background: #fde8e8; }

/* ══════════════════════════════
   MAIN — SECTIONS
══════════════════════════════ */
.profil-main { display: flex; flex-direction: column; gap: 20px; }

.profil-section {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow .18s;
}
.profil-section:hover { box-shadow: 0 4px 20px rgba(0,0,0,.05); }

.ps-header {
    padding: 18px 24px 16px;
    border-bottom: 1px solid #f0f0f5;
    display: flex; align-items: center; gap: 14px;
}
.ps-header-icon {
    width: 40px; height: 40px; border-radius: 11px;
    background: #f3f4f6; color: #374151;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.ps-title {
    font-size: 14px; font-weight: 700; color: #1a1a2e;
}
.ps-sub {
    font-size: 12px; color: #9299a8; margin-top: 2px;
}
.ps-body { padding: 22px 24px; }

/* ══════════════════════════════
   CHAMPS FORMULAIRE
══════════════════════════════ */
.rp-field { display: flex; flex-direction: column; gap: 6px; }
.rp-label {
    font-size: 12px; font-weight: 600; color: #374151;
}
.rp-req { color: #ef4444; }
.rp-input {
    width: 100%; padding: 10px 14px;
    border: 1.5px solid #e8e8ed; border-radius: 10px;
    font-size: 13.5px; color: #1a1a2e;
    font-family: inherit; background: #fff; outline: none;
    transition: border-color .15s, box-shadow .15s;
    -webkit-appearance: none;
}
.rp-input:focus {
    border-color: #1a1a2e;
    box-shadow: 0 0 0 3px rgba(26,26,46,.07);
}
.rp-disabled {
    background: #fafafa; color: #9299a8; cursor: not-allowed;
}
.rp-disabled:focus { border-color: #e8e8ed; box-shadow: none; }

/* ── MOT DE PASSE ── */
.pwd-wrap { position: relative; }
.rp-input--pwd { padding-right: 44px; }
.pwd-eye {
    position: absolute; right: 10px; top: 50%;
    transform: translateY(-50%);
    width: 28px; height: 28px; border-radius: 7px;
    border: none; background: transparent;
    color: #9299a8; cursor: pointer; font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    transition: color .12s, background .12s;
}
.pwd-eye:hover { color: #1a1a2e; background: #f0f0f5; }

/* Force mot de passe */
.strength-bar {
    display: flex; align-items: center; gap: 10px;
}
.strength-track {
    flex: 1; height: 5px; background: #f0f0f5;
    border-radius: 3px; overflow: hidden;
}
.strength-fill {
    height: 100%; border-radius: 3px;
    transition: width .35s ease, background .35s ease;
}
.strength-label {
    font-size: 11px; font-weight: 700; min-width: 65px;
    text-align: right;
}

/* Règles */
.pwd-rules {
    display: flex; flex-wrap: wrap; gap: 10px;
    padding: 12px 16px; border-radius: 10px;
    background: #fafafa; border: 1px solid #f0f0f5;
}
.pwd-rule {
    font-size: 12px; color: #9299a8;
    display: flex; align-items: center; gap: 6px;
    transition: color .2s;
}
.pwd-rule i { font-size: 11px; }
.pwd-rule.ok { color: #16a34a; }
.pwd-rule.ok i { color: #16a34a; }

/* Match */
.match-msg { font-size: 12px; margin-top: 5px; font-weight: 500; }
.match-msg.ok  { color: #16a34a; }
.match-msg.err { color: #dc3545; }

/* Erreur */
.rp-error {
    font-size: 12px; color: #dc3545;
    display: flex; align-items: center; gap: 4px;
    margin-top: 5px; font-weight: 500;
}

/* Bouton save */
.rp-btn-save {
    background: #1a1a2e; color: #fff;
    border: none; border-radius: 10px;
    padding: 11px 24px; font-size: 13.5px; font-weight: 600;
    cursor: pointer; display: inline-flex;
    align-items: center; gap: 8px;
    transition: background .15s, transform .15s, box-shadow .15s;
}
.rp-btn-save:hover {
    background: #0f1117;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(15,17,23,.2);
}
.rp-btn-save:disabled { opacity: .6; transform: none; cursor: not-allowed; }
</style>
@endpush

@push('scripts')
<script>
// ── TOGGLE ŒIL ────────────────────────────────────────
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type     = 'password';
        icon.className = 'bi bi-eye';
    }
}

// ── FORCE DU MOT DE PASSE ────────────────────────────
function checkStrength(val) {
    const bar   = document.getElementById('strength-bar');
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');

    bar.style.display = val.length > 0 ? '' : 'none';

    const hasLen   = val.length >= 6;
    const hasUpper = /[A-Z]/.test(val);
    const hasNum   = /[0-9]/.test(val);
    const hasSpec  = /[^a-zA-Z0-9]/.test(val);

    _setRule('rule-len',   hasLen);
    _setRule('rule-upper', hasUpper);
    _setRule('rule-num',   hasNum);

    const score = [hasLen, hasUpper, hasNum, hasSpec, val.length >= 10]
        .filter(Boolean).length;

    const cfgs = [
        { pct:'20%', bg:'#ef4444', txt:'Très faible', color:'#ef4444' },
        { pct:'40%', bg:'#f97316', txt:'Faible',      color:'#f97316' },
        { pct:'60%', bg:'#eab308', txt:'Moyen',       color:'#854d0e' },
        { pct:'80%', bg:'#3b82f6', txt:'Fort',        color:'#1d4ed8' },
        { pct:'100%',bg:'#22c55e', txt:'Très fort',   color:'#15803d' },
    ];
    const cfg = cfgs[score - 1] ?? cfgs[0];

    fill.style.width      = cfg.pct;
    fill.style.background = cfg.bg;
    label.textContent     = cfg.txt;
    label.style.color     = cfg.color;

    checkMatch();
}

function _setRule(id, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('ok', ok);
    el.querySelector('i').className = ok
        ? 'bi bi-check-circle-fill'
        : 'bi bi-circle';
}

// ── VÉRIFIER LA CORRESPONDANCE ───────────────────────
function checkMatch() {
    const pwd     = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;
    const msg     = document.getElementById('match-msg');

    if (!confirm) { msg.classList.add('d-none'); return; }
    msg.classList.remove('d-none');

    if (pwd === confirm) {
        msg.textContent = '✓ Les mots de passe correspondent';
        msg.className   = 'match-msg ok';
    } else {
        msg.textContent = '✗ Les mots de passe ne correspondent pas';
        msg.className   = 'match-msg err';
    }
}

// ── SAUVEGARDER LES INFOS ────────────────────────────
function saveInfos() {
    document.getElementById('info-txt').classList.add('d-none');
    document.getElementById('info-spin').classList.remove('d-none');

    fetch('{{ route("profil.infos") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            name:  document.getElementById('info_name').value,
            email: document.getElementById('info_email').value,
        }),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('info-txt').classList.remove('d-none');
        document.getElementById('info-spin').classList.add('d-none');
        if (d.success) toastr.success(d.message);
        else           toastr.error(d.message ?? 'Erreur.');
    })
    .catch(() => {
        document.getElementById('info-txt').classList.remove('d-none');
        document.getElementById('info-spin').classList.add('d-none');
        toastr.error('Erreur réseau.');
    });
}

// ── CHANGER LE MOT DE PASSE ──────────────────────────
function savePassword() {
    const current = document.getElementById('current_password').value;
    const pwd     = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;

    document.getElementById('err-current').classList.add('d-none');

    if (!current) { toastr.warning('Entrez votre mot de passe actuel.'); return; }
    if (!pwd || pwd.length < 6) {
        toastr.warning('Le nouveau mot de passe doit contenir au moins 6 caractères.');
        return;
    }
    if (pwd !== confirm) {
        toastr.warning('Les mots de passe ne correspondent pas.');
        return;
    }

    const btn = document.getElementById('btn-pwd');
    document.getElementById('pwd-txt').classList.add('d-none');
    document.getElementById('pwd-spin').classList.remove('d-none');
    btn.disabled = true;

    fetch('{{ route("profil.password") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            current_password:      current,
            password:              pwd,
            password_confirmation: confirm,
        }),
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('pwd-txt').classList.remove('d-none');
        document.getElementById('pwd-spin').classList.add('d-none');
        btn.disabled = false;

        if (d.success) {
            toastr.success(d.message);
            ['current_password','new_password','confirm_password']
                .forEach(id => document.getElementById(id).value = '');
            document.getElementById('strength-bar').style.display = 'none';
            document.getElementById('match-msg').classList.add('d-none');
            ['rule-len','rule-upper','rule-num'].forEach(id => _setRule(id, false));
        } else {
            const errEl = document.getElementById('err-current');
            errEl.classList.remove('d-none');
            document.getElementById('err-current-txt').textContent = d.message;
            document.getElementById('current_password').focus();
        }
    })
    .catch(() => {
        document.getElementById('pwd-txt').classList.remove('d-none');
        document.getElementById('pwd-spin').classList.add('d-none');
        btn.disabled = false;
        toastr.error('Erreur réseau.');
    });
}
</script>
@endpush