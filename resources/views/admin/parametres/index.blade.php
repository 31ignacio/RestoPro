@extends('layouts.app')
@section('title', 'Paramètres')
@section('page_title', 'Paramètres système')
@section('breadcrumb', 'Configuration du restaurant')

@section('content')

<form id="params-form" enctype="multipart/form-data">
@csrf

{{-- ══ EN-TÊTE ══ --}}
<div class="d-flex align-items-center justify-content-between mb-5">
    <div>
        <h4 class="prm-title">Paramètres</h4>
        <p class="prm-sub">Configuration générale de votre restaurant</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn-prm-reset" onclick="location.reload()">
            <i class="bi bi-arrow-counterclockwise"></i>
            Réinitialiser
        </button>
        <button type="button" class="btn-prm-save" onclick="saveParams()">
            <span id="paramSaveTxt">
                <i class="bi bi-check2-circle me-1"></i>Enregistrer
            </span>
            <span id="paramSaveSpin" class="spinner-border spinner-border-sm d-none"></span>
        </button>
    </div>
</div>

<div class="row g-4">

    {{-- ══ COLONNE GAUCHE ══ --}}
    <div class="col-lg-6 d-flex flex-column gap-4">

        {{-- IDENTITÉ DU RESTAURANT --}}
        <div class="prm-panel">
            <div class="prm-panel-head">
                <div class="prm-panel-title">
                    <i class="bi bi-shop"></i>
                    Identité du restaurant
                </div>
            </div>
            <div class="prm-panel-body">

                {{-- Logo --}}
                <div class="logo-section mb-4">
                    <div class="logo-preview-wrap" id="logo-preview-wrap">
                        @if($params->get('logo'))
                        <img src="{{ asset('storage/'.$params->get('logo')) }}"
                            id="logo-preview" class="logo-img">
                        @else
                        <div class="logo-placeholder" id="logo-placeholder">
                            {{ strtoupper(substr($params->get('restaurant_nom', 'R'), 0, 1)) }}
                        </div>
                        @endif
                    </div>
                    <div class="logo-info">
                        <div class="logo-name">Logo du restaurant</div>
                        <div class="logo-hint">PNG, JPG · max 1 Mo · 200×200px recommandé</div>
                        <label class="btn-upload" for="logo-input">
                            <i class="bi bi-upload me-1"></i>Changer le logo
                        </label>
                        <input type="file" id="logo-input" name="logo"
                            class="d-none" accept="image/*" onchange="previewLogo(this)">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="prm-label">Nom du restaurant <span class="text-danger">*</span></label>
                        <input type="text" name="restaurant_nom" class="form-control"
                            value="{{ $params->get('restaurant_nom', 'RestoPro') }}"
                            placeholder="Mon Restaurant" required>
                    </div>
                    <div class="col-12">
                        <label class="prm-label">Adresse</label>
                        <input type="text" name="restaurant_adresse" class="form-control"
                            value="{{ $params->get('restaurant_adresse', '') }}"
                            placeholder="Cotonou, Bénin">
                    </div>
                    <div class="col-md-6">
                        <label class="prm-label">Téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone" style="color:#9299a8"></i></span>
                            <input type="text" name="restaurant_tel" class="form-control"
                                value="{{ $params->get('restaurant_tel', '') }}"
                                placeholder="+229 XX XX XX XX">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="prm-label">Email de contact</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope" style="color:#9299a8"></i></span>
                            <input type="email" name="restaurant_email" class="form-control"
                                value="{{ $params->get('restaurant_email', '') }}"
                                placeholder="contact@restaurant.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TICKET DE CAISSE --}}
        <div class="prm-panel">
            <div class="prm-panel-head">
                <div class="prm-panel-title">
                    <i class="bi bi-receipt"></i>
                    Ticket de caisse
                </div>
            </div>
            <div class="prm-panel-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="prm-label">Message d'accueil (en-tête du ticket)</label>
                        <input type="text" name="ticket_entete" class="form-control"
                            value="{{ $params->get('ticket_entete', 'Bienvenue !') }}"
                            placeholder="Bienvenue chez nous !">
                    </div>
                    <div class="col-12">
                        <label class="prm-label">Message de fin (pied du ticket)</label>
                        <input type="text" name="ticket_message" class="form-control"
                            value="{{ $params->get('ticket_message', 'Merci de votre visite !') }}"
                            placeholder="Merci de votre visite !">
                    </div>
                    <div class="col-12">
                        <label class="prm-label">Mentions légales / Note</label>
                        <textarea name="ticket_note" class="form-control" rows="2"
                            placeholder="Ex : RCCM : BJ-COT-… · NIF : …">{{ $params->get('ticket_note', '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                name="ticket_logo" id="ticket_logo" value="1"
                                {{ $params->get('ticket_logo', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label prm-label mb-0" for="ticket_logo">
                                Afficher le logo sur le ticket
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ COLONNE DROITE ══ --}}
    <div class="col-lg-6 d-flex flex-column gap-4">

        {{-- MONNAIE & TVA --}}
        <div class="prm-panel">
            <div class="prm-panel-head">
                <div class="prm-panel-title">
                    <i class="bi bi-currency-exchange"></i>
                    Monnaie & TVA
                </div>
            </div>
            <div class="prm-panel-body">
                <div class="row g-3">
                    <div class="col-8">
                        <label class="prm-label">Monnaie</label>
                        <input type="text" name="monnaie" class="form-control"
                            value="{{ $params->get('monnaie', 'FCFA') }}"
                            placeholder="FCFA, EUR, USD...">
                    </div>
                    <div class="col-4">
                        <label class="prm-label">Symbole</label>
                        <input type="text" name="monnaie_symbole" class="form-control"
                            value="{{ $params->get('monnaie_symbole', 'F') }}"
                            placeholder="F, €, $">
                    </div>
                    <div class="col-12">
                        <div class="tva-toggle-row">
                            <div>
                                <div class="prm-label mb-0">TVA activée</div>
                                <div style="font-size:12px;color:#9299a8">Appliquer la TVA sur les commandes</div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                    name="tva_active" id="tva_active" value="1"
                                    {{ $params->get('tva_active') == '1' ? 'checked' : '' }}
                                    onchange="document.getElementById('tva_taux_wrap').style.display=this.checked?'':'none'">
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="tva_taux_wrap"
                        style="display:{{ $params->get('tva_active') == '1' ? '' : 'none' }}">
                        <label class="prm-label">Taux de TVA (%)</label>
                        <div class="input-group">
                            <input type="number" name="tva_taux" class="form-control"
                                value="{{ $params->get('tva_taux', '18') }}"
                                min="0" max="100" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- HORAIRES D'OUVERTURE --}}
        <div class="prm-panel">
            <div class="prm-panel-head">
                <div class="prm-panel-title">
                    <i class="bi bi-clock"></i>
                    Horaires d'ouverture
                </div>
            </div>
            <div class="prm-panel-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="prm-label">Ouverture</label>
                        <input type="time" name="heure_ouverture" class="form-control"
                            value="{{ $params->get('heure_ouverture', '08:00') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="prm-label">Fermeture</label>
                        <input type="time" name="heure_fermeture" class="form-control"
                            value="{{ $params->get('heure_fermeture', '23:00') }}">
                    </div>
                    <div class="col-12">
                        <label class="prm-label">Jours d'ouverture</label>
                        <div class="jours-grid">
                            @php
                                $jours = ['lun'=>'Lun','mar'=>'Mar','mer'=>'Mer','jeu'=>'Jeu','ven'=>'Ven','sam'=>'Sam','dim'=>'Dim'];
                                $joursActifs = explode(',', $params->get('jours_ouverture', 'lun,mar,mer,jeu,ven,sam'));
                            @endphp
                            @foreach($jours as $key => $label)
                            <label class="jour-btn {{ in_array($key, $joursActifs) ? 'active' : '' }}">
                                <input type="checkbox" name="jours_ouverture[]"
                                    value="{{ $key }}"
                                    {{ in_array($key, $joursActifs) ? 'checked' : '' }}
                                    class="d-none" onchange="toggleJour(this)">
                                {{ $label }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PRÉFÉRENCES COMMANDES --}}
        <div class="prm-panel">
            <div class="prm-panel-head">
                <div class="prm-panel-title">
                    <i class="bi bi-sliders"></i>
                    Préférences commandes
                </div>
            </div>
            <div class="prm-panel-body">
                <div class="d-flex flex-column gap-3">

                    <div class="pref-row">
                        <div>
                            <div class="prm-label mb-0">Commandes à emporter</div>
                            <div class="pref-hint">Autoriser les commandes sans table</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                name="emporter_actif" id="emporter_actif" value="1"
                                {{ $params->get('emporter_actif', '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="pref-row">
                        <div>
                            <div class="prm-label mb-0">Impression automatique</div>
                            <div class="pref-hint">Imprimer le ticket à la validation</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                name="impression_auto" id="impression_auto" value="1"
                                {{ $params->get('impression_auto', '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="pref-row">
                        <div>
                            <div class="prm-label mb-0">Confirmation avant suppression</div>
                            <div class="pref-hint">Demander confirmation pour les suppressions</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                name="confirm_suppression" id="confirm_suppression" value="1"
                                {{ $params->get('confirm_suppression', '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="col-12 pt-1">
                        <label class="prm-label">Nombre de tables</label>
                        <input type="number" name="nb_tables" class="form-control"
                            value="{{ $params->get('nb_tables', '10') }}"
                            min="1" max="200"
                            placeholder="Ex : 12">
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

{{-- Bouton save bas de page --}}
<div class="d-flex justify-content-end gap-2 mt-4 pb-2">
    <button type="button" class="btn-prm-reset" onclick="location.reload()">
        <i class="bi bi-arrow-counterclockwise"></i>
        Réinitialiser
    </button>
    <button type="button" class="btn-prm-save" onclick="saveParams()">
        <span id="paramSaveTxt2">
            <i class="bi bi-check2-circle me-1"></i>Enregistrer les paramètres
        </span>
        <span id="paramSaveSpin2" class="spinner-border spinner-border-sm d-none"></span>
    </button>
</div>

</form>

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
.prm-title {
    font-size: 22px; font-weight: 800;
    color: var(--ink); margin: 0 0 4px;
    letter-spacing: -.3px;
}
.prm-sub { font-size: 13px; color: var(--muted); margin: 0; }

.btn-prm-reset {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 18px;
    background: var(--white);
    border: 1.5px solid var(--border);
    border-radius: 11px;
    font-size: 13px; font-weight: 600;
    color: var(--muted); cursor: pointer;
    transition: all .15s;
}
.btn-prm-reset:hover { border-color: var(--ink); color: var(--ink); }

.btn-prm-save {
    display: flex; align-items: center; gap: 7px;
    padding: 10px 24px;
    background: var(--ink); color: #fff;
    border: none; border-radius: 11px;
    font-size: 13.5px; font-weight: 600;
    cursor: pointer; transition: opacity .15s;
}
.btn-prm-save:hover { opacity: .85; }

/* ══════════════════════════════════════
   PANELS
══════════════════════════════════════ */
.prm-panel {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
.prm-panel-head {
    padding: 15px 22px;
    border-bottom: 1px solid var(--border);
}
.prm-panel-title {
    font-size: 13.5px; font-weight: 700;
    color: var(--ink);
    display: flex; align-items: center; gap: 8px;
}
.prm-panel-title i { color: var(--gold); font-size: 15px; }
.prm-panel-body { padding: 20px 22px; }

/* ── Label ── */
.prm-label {
    display: block;
    font-size: 13px; font-weight: 600;
    color: var(--ink); margin-bottom: 6px;
}

/* ══════════════════════════════════════
   LOGO
══════════════════════════════════════ */
.logo-section {
    display: flex; align-items: center; gap: 18px;
}
.logo-preview-wrap {
    flex-shrink: 0;
}
.logo-img, .logo-placeholder {
    width: 76px; height: 76px;
    border-radius: 14px;
    object-fit: cover;
    border: 2px solid var(--border);
}
.logo-placeholder {
    background: var(--gold);
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; font-weight: 800;
    color: #fff;
}
.logo-name { font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 3px; }
.logo-hint { font-size: 11.5px; color: var(--muted); margin-bottom: 10px; }
.btn-upload {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 12px; font-weight: 600;
    color: var(--muted); cursor: pointer;
    transition: all .15s;
}
.btn-upload:hover { border-color: var(--ink); color: var(--ink); }

/* ══════════════════════════════════════
   TVA TOGGLE ROW
══════════════════════════════════════ */
.tva-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg);
    border-radius: 10px;
    padding: 12px 14px;
}

/* ══════════════════════════════════════
   JOURS
══════════════════════════════════════ */
.jours-grid {
    display: flex; gap: 6px; flex-wrap: wrap;
}
.jour-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 44px; height: 36px;
    border-radius: 9px;
    border: 1.5px solid var(--border);
    background: var(--white);
    font-size: 12px; font-weight: 700;
    color: var(--muted); cursor: pointer;
    transition: all .15s;
    user-select: none;
}
.jour-btn.active {
    background: var(--ink); border-color: var(--ink); color: #fff;
}
.jour-btn:hover:not(.active) { border-color: var(--ink); color: var(--ink); }

/* ══════════════════════════════════════
   PREF ROWS
══════════════════════════════════════ */
.pref-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 14px;
    background: var(--bg);
    border-radius: 10px;
}
.pref-hint { font-size: 12px; color: var(--muted); margin-top: 2px; }

/* ══════════════════════════════════════
   FORM CONTROLS
══════════════════════════════════════ */
.form-control, .form-select {
    border-color: var(--border);
    font-size: 13.5px;
    border-radius: 10px;
    color: var(--ink);
    transition: border-color .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,169,110,.12);
}
.input-group-text {
    background: var(--bg);
    border-color: var(--border);
    border-radius: 10px 0 0 10px;
}
.input-group .form-control { border-radius: 0 10px 10px 0; }
.input-group .form-control:last-child { border-radius: 0 10px 10px 0; }
.input-group-text:last-child { border-radius: 0 10px 10px 0; }
</style>
@endpush

@push('scripts')
<script>
// ── Logo preview ──────────────────────────────────────
function previewLogo(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logo-preview-wrap').innerHTML =
            `<img src="${e.target.result}" class="logo-img" id="logo-preview">`;
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Jours boutons toggle ──────────────────────────────
function toggleJour(checkbox) {
    checkbox.closest('.jour-btn').classList.toggle('active', checkbox.checked);
}

// ── Sauvegarde ────────────────────────────────────────
function saveParams() {
    const form     = document.getElementById('params-form');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    // Spinners
    ['paramSaveTxt','paramSaveTxt2'].forEach(id => {
        document.getElementById(id)?.classList.add('d-none');
    });
    ['paramSaveSpin','paramSaveSpin2'].forEach(id => {
        document.getElementById(id)?.classList.remove('d-none');
    });

    fetch('/admin/parametres/1', {
        method: 'POST',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData,
    })
    .then(r => r.json())
    .then(d => {
        ['paramSaveTxt','paramSaveTxt2'].forEach(id => {
            document.getElementById(id)?.classList.remove('d-none');
        });
        ['paramSaveSpin','paramSaveSpin2'].forEach(id => {
            document.getElementById(id)?.classList.add('d-none');
        });

        if (d.success) {
            toastr.success(d.message ?? 'Paramètres enregistrés.');
        } else {
            toastr.error(d.message ?? 'Erreur lors de la sauvegarde.');
        }
    })
    .catch(() => {
        ['paramSaveTxt','paramSaveTxt2'].forEach(id => {
            document.getElementById(id)?.classList.remove('d-none');
        });
        ['paramSaveSpin','paramSaveSpin2'].forEach(id => {
            document.getElementById(id)?.classList.add('d-none');
        });
        toastr.error('Erreur de connexion.');
    });
}
</script>
@endpush