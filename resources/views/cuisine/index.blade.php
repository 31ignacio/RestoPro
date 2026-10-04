@extends('layouts.app')
@section('title', 'Cuisine')
@section('page_title', 'Interface Cuisine')
@section('breadcrumb', 'Commandes en cours')

@section('content')

{{-- STATS RAPIDES --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label"><i class="bi bi-hourglass-split me-1"></i>En attente</div>
            <div class="stat-value" id="count-attente">
                {{ $commandes->where('statut','en_attente')->count() }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label"><i class="bi bi-fire me-1"></i>En cuisson</div>
            <div class="stat-value" id="count-cuisson">
                {{ $commandes->where('statut','en_cuisson')->count() }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label"><i class="bi bi-clock me-1"></i>Temps moyen</div>
            <div class="stat-value" id="avg-time">
                @php
                    $avg = $commandes->where('statut','en_cuisson')
                        ->avg(fn($c) => $c->created_at->diffInMinutes(now()));
                @endphp
                {{ $avg ? round($avg).' MIN' : '—' }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center d-flex align-items-center justify-content-center gap-2">
            <div class="live-dot"></div>
            <div>
                <div class="stat-label">Actualisation</div>
                <div class="stat-value" style="font-size:18px" id="last-update">En direct</div>
            </div>
        </div>
    </div>
</div>

{{-- COLONNES : EN ATTENTE | EN CUISSON --}}
<div class="row g-4" id="cuisine-board">

    {{-- COLONNE EN ATTENTE --}}
    <div class="col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:10px;height:10px;border-radius:3px;background:#ffc107"></div>
            <h6 class="mb-0 fw-bold">En attente</h6>
            <span class="badge bg-warning text-dark ms-1" id="badge-attente">
                {{ $commandes->where('statut','en_attente')->count() }}
            </span>
        </div>
        <div id="col-attente" class="cuisine-col">
            @foreach($commandes->where('statut','en_attente') as $cmd)
                @include('cuisine._card', ['cmd' => $cmd])
            @endforeach
            @if($commandes->where('statut','en_attente')->count() === 0)
                <div class="empty-col" id="empty-attente">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                    Aucune commande en attente
                </div>
            @endif
        </div>
    </div>

    {{-- COLONNE EN CUISSON --}}
    <div class="col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:10px;height:10px;border-radius:3px;background:#0dcaf0"></div>
            <h6 class="mb-0 fw-bold">En cuisson</h6>
            <span class="badge bg-info text-dark ms-1" id="badge-cuisson">
                {{ $commandes->where('statut','en_cuisson')->count() }}
            </span>
        </div>
        <div id="col-cuisson" class="cuisine-col">
            @foreach($commandes->where('statut','en_cuisson') as $cmd)
                @include('cuisine._card', ['cmd' => $cmd])
            @endforeach
            @if($commandes->where('statut','en_cuisson')->count() === 0)
                <div class="empty-col" id="empty-cuisson">
                    <i class="bi bi-fire fs-1 d-block mb-2 opacity-25"></i>
                    Aucune commande en cuisson
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.cuisine-col {
    min-height: 300px;
}
.cuisine-card {
    background: #fff;
    border: 1px solid #eaeaef;
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 12px;
    transition: box-shadow .15s;
    position: relative;
    overflow: hidden;
}
.cuisine-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.cuisine-card.urgent::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: #dc3545;
    border-radius: 4px 0 0 4px;
}
.cuisine-card.normal::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: #ffc107;
    border-radius: 4px 0 0 4px;
}
.cuisine-card.cuisson::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: #0dcaf0;
    border-radius: 4px 0 0 4px;
}
.timer-badge {
    font-size: 13px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
}
.timer-ok     { background: #d1e7dd; color: #0a3622; }
.timer-warn   { background: #fff3cd; color: #856404; }
.timer-danger { background: #f8d7da; color: #842029; animation: pulse-red 1s infinite; }
@keyframes pulse-red {
    0%,100% { opacity: 1; } 50% { opacity: .6; }
}
.item-line {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 0;
    border-bottom: 1px solid #f5f5f7;
    font-size: 13px;
}
.item-line:last-child { border-bottom: none; }
.item-line-old {
    color: #8a8f98;
    background: #f8f9fa;
    border-radius: 8px;
    padding-left: 6px;
    text-decoration: line-through;
}
.item-line-old .item-qty {
    background: #adb5bd;
}
.item-old-badge {
    color: #6c757d;
    font-size: 11px;
    white-space: nowrap;
    text-decoration: none;
}
.item-separator {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 8px 0 5px;
    color: #0d6efd;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.item-separator::before,
.item-separator::after {
    content: '';
    height: 1px;
    background: #cfe2ff;
    flex: 1;
}
.item-qty {
    min-width: 26px; height: 26px;
    background: #212529; color: #fff;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
}
.btn-cuisine {
    width: 100%; padding: 10px;
    border-radius: 10px; border: none;
    font-weight: 600; font-size: 13px;
    cursor: pointer; transition: all .15s;
}
.btn-prendre {
    background: #ffc107; color: #000;
}
.btn-prendre:hover { background: #e0a800; }
.btn-prete {
    background: #198754; color: #fff;
}
.btn-prete:hover { background: #157347; }
.live-dot {
    width: 10px; height: 10px;
    border-radius: 50%; background: #198754;
    animation: blink 1.5s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }
.empty-col {
    text-align: center;
    padding: 40px 20px;
    color: #9299a8;
    background: #f8f9fa;
    border-radius: 14px;
    border: 2px dashed #eaeaef;
}
</style>
@endpush

@push('scripts')
<script>
// Audio notification
const audioCtx = window.AudioContext ? new AudioContext() : null;
function beep(freq = 880, dur = 200) {
    if (!audioCtx) return;
    const osc  = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + dur/1000);
    osc.start(); osc.stop(audioCtx.currentTime + dur/1000);
}

let knownIds = new Set([
    @foreach($commandes as $cmd) {{ $cmd->id }}, @endforeach
]);
let pollInterval;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);
}

function buildCard(cmd) {
    const mins   = Math.max(0, parseInt(cmd.minutes || 0, 10));
    const urgent = mins >= 20;
    const warn   = mins >= 10;
    const timerClass = urgent ? 'timer-danger' : warn ? 'timer-warn' : 'timer-ok';
    const cardClass  = cmd.statut === 'en_cuisson'
        ? 'cuisson'
        : (urgent ? 'urgent' : 'normal');

    // Comparaison au statut courant de la commande (marche pour les 2 colonnes)
    const hasCurrentItems = cmd.items.some(it => it.statut === cmd.statut);
    const hasOldItems     = cmd.items.some(it => it.statut !== cmd.statut);
    let separatorShown = false;

    const oldBadgeLabel = (statut) => {
        if (statut === 'prete') return 'déjà servi';
        if (statut === 'en_cuisson') return 'déjà en cuisson';
        return 'déjà transmis';
    };

    const items = cmd.items.map(it => {
        const isOld = hasCurrentItems && it.statut !== cmd.statut;
        const separator = hasCurrentItems && hasOldItems && it.statut === cmd.statut && !separatorShown
            ? (separatorShown = true, '<div class="item-separator"><span>Ajout client</span></div>')
            : '';
        return `${separator}
        <div class="item-line ${isOld ? 'item-line-old' : ''}">
            <div class="item-qty">${it.quantite}</div>
            <div class="flex-grow-1">${escapeHtml(it.nom)}</div>
            ${isOld ? `<small class="item-old-badge"><i class="bi bi-check2"></i> ${oldBadgeLabel(it.statut)}</small>` : ''}
            ${it.notes ? `<small class="text-muted">${escapeHtml(it.notes)}</small>` : ''}
        </div>`;
    }).join('');

    const btn = cmd.can_manage
        ? (cmd.statut === 'en_attente'
            ? `<button class="btn-cuisine btn-prendre mt-2" onclick="prendreCuisson(${cmd.id}, this)">
                  <i class="bi bi-fire me-1"></i>Prendre en charge
               </button>`
            : `<button class="btn-cuisine btn-prete mt-2" onclick="marquerPrete(${cmd.id}, this)">
                  <i class="bi bi-check-lg me-1"></i>Marquer prête
               </button>`)
        : `<div class="btn-cuisine text-center mt-2" style="background:#f3f4f6;color:#6b7280;cursor:default"><i class="bi bi-eye me-1"></i>Consultation seule</div>`;

    return `
    <div class="cuisine-card ${cardClass}" id="cuisine-card-${cmd.id}">
        <div class="d-flex align-items-start justify-content-between mb-2">
            <div>
                <div class="fw-bold">${escapeHtml(cmd.numero)}</div>
                <small class="text-muted">
                    ${cmd.table ? 'Table '+escapeHtml(cmd.table) : 'Emporter'}
                    · ${cmd.type === 'sur_place' ? 'Sur place' : cmd.type}
                </small>
            </div>
            <span class="timer-badge ${timerClass}">
                <i class="bi bi-clock me-1"></i>${mins} MIN
            </span>
        </div>
        <div class="mb-2">${items}</div>
        ${cmd.notes ? `<div class="alert alert-light py-1 px-2 mb-2" style="font-size:12px">
            <i class="bi bi-chat-left-text me-1"></i>${escapeHtml(cmd.notes)}</div>` : ''}
        <div class="small text-muted mb-2">
            <i class="bi bi-person-badge me-1"></i>${cmd.cuisinier ? 'Attribuée à ' + cmd.cuisinier : 'Non attribuée'}
        </div>
        ${btn}
    </div>`;
}

function updateTimers() {
    document.querySelectorAll('[data-created]').forEach(el => {
        const created = new Date(el.dataset.created);
        const mins    = Math.floor((Date.now() - created) / 60000);
        const badge   = el.querySelector('.timer-badge');
        if (!badge) return;
        badge.textContent = mins + ' min';
        badge.className   = 'timer-badge ' + (mins >= 20 ? 'timer-danger' : mins >= 10 ? 'timer-warn' : 'timer-ok');
    });
}

async function poll() {
    try {
        const r    = await fetch('/cuisine/poll', { headers: { 'Accept': 'application/json' } });
        const cmds = await r.json();

        // Détecter nouvelles commandes
        const newIds = new Set(cmds.map(c => c.id));
        cmds.forEach(c => {
            if (!knownIds.has(c.id)) {
                beep(880, 200);
                setTimeout(() => beep(1100, 200), 250);
                toastr.info(`Nouvelle commande : ${c.numero}`, 'Cuisine');
                knownIds.add(c.id);
            }
        });

        // Mettre à jour les colonnes
        const attente = cmds.filter(c => c.statut === 'en_attente');
        const cuisson = cmds.filter(c => c.statut === 'en_cuisson');

        document.getElementById('col-attente').innerHTML =
            attente.length ? attente.map(buildCard).join('') :
            `<div class="empty-col">
                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                Aucune commande en attente</div>`;

        document.getElementById('col-cuisson').innerHTML =
            cuisson.length ? cuisson.map(buildCard).join('') :
            `<div class="empty-col">
                <i class="bi bi-fire fs-1 d-block mb-2 opacity-25"></i>
                Aucune commande en cuisson</div>`;

        // Compteurs
        document.getElementById('count-attente').textContent = attente.length;
        document.getElementById('count-cuisson').textContent = cuisson.length;
        document.getElementById('badge-attente').textContent = attente.length;
        document.getElementById('badge-cuisson').textContent = cuisson.length;

        // Titre onglet
        const total = attente.length + cuisson.length;
        document.title = total > 0 ? `(${total}) Cuisine — RestoPro` : 'Cuisine — RestoPro';

        document.getElementById('last-update').textContent = new Date().toLocaleTimeString('fr-FR', {
            timeZone: 'Africa/Porto-Novo',
            hour: '2-digit',
            minute: '2-digit'
        });

    } catch(e) {
        console.warn('Poll error', e);
    }
}

function prendreCuisson(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/cuisine/${id}/prendre`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toastr.success(d.message);
            beep(660, 150);
            poll();
        } else {
            toastr.error(d.message);
            btn.disabled = false;
        }
    });
}

function marquerPrete(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/cuisine/${id}/prete`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            beep(440, 100);
            setTimeout(() => beep(660, 100), 120);
            setTimeout(() => beep(880, 200), 250);
            toastr.success(d.message);
            poll();
        } else {
            toastr.error(d.message);
            btn.disabled = false;
        }
    });
}

// Démarrer le polling toutes les 8 secondes
poll();
pollInterval = setInterval(poll, 8000);
setInterval(updateTimers, 30000);

// Arrêter si on quitte la page
window.addEventListener('beforeunload', () => clearInterval(pollInterval));
</script>
@endpush
