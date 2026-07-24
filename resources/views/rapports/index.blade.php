@extends('layouts.app')
@section('title', 'Rapports')
@section('page_title', 'Rapports & Statistiques')
@section('breadcrumb', 'Analyse des performances')

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="rpt-header mb-5">
    <div>
        <h4 class="rpt-title">Rapports & Statistiques</h4>
        <p class="rpt-sub">Analyse des performances · Période : <strong>{{ ['jour'=>"Aujourd'hui",'semaine'=>'Cette semaine','mois'=>'Ce mois','annee'=>'Cette année'][$periode] ?? $periode }}</strong></p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- Filtres période --}}
        <div class="periode-tabs">
            @foreach(['jour'=>"Aujourd'hui",'semaine'=>'Semaine','mois'=>'Mois','annee'=>'Année'] as $p => $label)
            <a href="{{ route('rapports', ['periode' => $p]) }}"
               class="ptab {{ $periode === $p ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <button class="btn-print" onclick="window.print()">
            <i class="bi bi-printer"></i>
            <span>Imprimer</span>
        </button>
    </div>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="row g-3 mb-5">

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon" style="--ic:#c9a96e;--icbg:#c9a96e18">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="kpi-val">
                {{ number_format($ca_total, 0, ',', ' ') }}
                <span class="kpi-unit">F</span>
            </div>
            <div class="kpi-lbl">Chiffre d'affaires</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon" style="--ic:#6b9fd4;--icbg:#6b9fd418">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $nb_commandes }}</div>
            <div class="kpi-lbl">Commandes payées</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon" style="--ic:#e07070;--icbg:#e0707018">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="kpi-val" style="color:#e07070">
                {{ number_format($total_depenses, 0, ',', ' ') }}
                <span class="kpi-unit">F</span>
            </div>
            <div class="kpi-lbl">Dépenses</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon" style="--ic:{{ $benefice >= 0 ? '#7ec88a' : '#e07070' }};--icbg:{{ $benefice >= 0 ? '#7ec88a18' : '#e0707018' }}">
                    <i class="bi bi-coin"></i>
                </div>
            </div>
            <div class="kpi-val" style="color:{{ $benefice >= 0 ? '#39a85b' : '#e07070' }}">
                {{ number_format($benefice, 0, ',', ' ') }}
                <span class="kpi-unit">F</span>
            </div>
            <div class="kpi-lbl">Bénéfice net</div>
        </div>
    </div>

</div>

{{-- ══ LIGNE 1 : Ventes + Modes paiement ══ --}}
<div class="row g-4 mb-4">

    <div class="col-xl-8">
        <div class="rpt-panel">
            <div class="rpt-panel-head">
                <div class="rpt-panel-title">
                    <i class="bi bi-bar-chart-line"></i>
                    Ventes des 30 derniers jours
                </div>
            </div>
            <div class="rpt-panel-body">
                <canvas id="chartVentes" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="rpt-panel h-100 d-flex flex-column">
            <div class="rpt-panel-head">
                <div class="rpt-panel-title">
                    <i class="bi bi-pie-chart"></i>
                    Modes de paiement
                </div>
            </div>
            <div class="rpt-panel-body flex-fill d-flex flex-column">
                <div class="donut-wrap mb-3">
                    <canvas id="chartModes"></canvas>
                </div>
                <div class="mode-list">
                    @foreach($modes as $m)
                    <div class="mode-item">
                        <div class="mode-icon">
                            <i class="bi bi-{{ $m->mode === 'especes' ? 'cash' : ($m->mode === 'mobile_money' ? 'phone' : 'credit-card') }}"></i>
                        </div>
                        <div class="mode-label">{{ ucfirst(str_replace('_',' ',$m->mode)) }}</div>
                        <div class="mode-right">
                            <div class="mode-amount">{{ number_format($m->total, 0, ',', ' ') }} F</div>
                            <div class="mode-count">{{ $m->nb }} encaissement(s)</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══ LIGNE 2 : Top produits + Dépenses + Pic ══ --}}
<div class="row g-4">

    {{-- TOP PRODUITS --}}
    <div class="col-xl-6">
        <div class="rpt-panel">
            <div class="rpt-panel-head">
                <div class="rpt-panel-title">
                    <i class="bi bi-trophy"></i>
                    Top produits vendus
                </div>
            </div>
            <div class="table-wrap">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Produit</th>
                            <th class="text-center">Vendus</th>
                            <th class="text-end">CA généré</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($top_produits as $i => $p)
                        <tr>
                            <td>
                                @if($i === 0)<span class="medal gold">1</span>
                                @elseif($i === 1)<span class="medal silver">2</span>
                                @elseif($i === 2)<span class="medal bronze">3</span>
                                @else<span class="rank-num">{{ $i+1 }}</span>
                                @endif
                            </td>
                            <td class="prod-nom">{{ $p->nom }}</td>
                            <td class="text-center">
                                <span class="qty-badge">{{ $p->total_vendu }}</span>
                            </td>
                            <td class="text-end prod-ca">{{ number_format($p->ca, 0, ',', ' ') }} F</td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-row"><i class="bi bi-inbox"></i><span>Aucune vente.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DÉPENSES PAR CATÉGORIE --}}
    <div class="col-xl-3">
        <div class="rpt-panel h-100 d-flex flex-column">
            <div class="rpt-panel-head">
                <div class="rpt-panel-title">
                    <i class="bi bi-tags"></i>
                    Dépenses par catégorie
                </div>
            </div>
            <div class="rpt-panel-body flex-fill p-0">
                @forelse($depenses_cat as $dc)
                @php $pct = $total_depenses > 0 ? round($dc->total / $total_depenses * 100) : 0; @endphp
                <div class="dep-item">
                    <div class="dep-row">
                        <span class="dep-label">{{ ucfirst($dc->categorie) }}</span>
                        <span class="dep-amount">{{ number_format($dc->total,0,',',' ') }} F</span>
                    </div>
                    <div class="dep-bar-wrap">
                        <div class="dep-bar" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="dep-pct">{{ $pct }}%</div>
                </div>
                @empty
                <div class="empty-row" style="padding:32px 0">
                    <i class="bi bi-wallet2"></i>
                    <span>Aucune dépense.</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- PIC D'ACTIVITÉ --}}
    <div class="col-xl-3">
        <div class="rpt-panel h-100 d-flex flex-column">
            <div class="rpt-panel-head">
                <div class="rpt-panel-title">
                    <i class="bi bi-clock"></i>
                    Pic d'activité
                </div>
            </div>
            <div class="rpt-panel-body flex-fill d-flex align-items-center">
                <canvas id="chartHeures" style="width:100%!important"></canvas>
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
.rpt-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.rpt-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--ink);
    margin: 0 0 4px;
    letter-spacing: -.3px;
}
.rpt-sub {
    font-size: 13px;
    color: var(--muted);
    margin: 0;
}
.rpt-sub strong { color: var(--ink); }

/* Période tabs */
.periode-tabs {
    display: flex;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
}
.ptab {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--muted);
    text-decoration: none;
    transition: all .15s;
}
.ptab:hover  { color: var(--ink); background: var(--bg); }
.ptab.active { background: var(--ink); color: #fff; }

/* Bouton print */
.btn-print {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: var(--white);
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s;
}
.btn-print:hover { border-color: var(--ink); color: var(--ink); }

/* ══════════════════════════════════════
   KPI CARDS
══════════════════════════════════════ */
.kpi-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 20px 18px;
    transition: box-shadow .2s, transform .15s;
}
.kpi-card:hover {
    box-shadow: 0 8px 28px rgba(26,26,46,.08);
    transform: translateY(-2px);
}
.kpi-top { margin-bottom: 14px; }
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    background: var(--icbg);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    color: var(--ic);
}
.kpi-val {
    font-size: 26px;
    font-weight: 800;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 5px;
    letter-spacing: -.5px;
}
.kpi-unit { font-size: 14px; font-weight: 500; color: var(--muted); }
.kpi-lbl  { font-size: 12px; color: var(--muted); font-weight: 500; }

/* ══════════════════════════════════════
   PANELS
══════════════════════════════════════ */
.rpt-panel {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
.rpt-panel-head {
    padding: 16px 22px;
    border-bottom: 1px solid var(--border);
}
.rpt-panel-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 8px;
}
.rpt-panel-title i { color: var(--gold); font-size: 15px; }
.rpt-panel-body { padding: 20px 22px; }

/* ══════════════════════════════════════
   MODES PAIEMENT
══════════════════════════════════════ */
.donut-wrap {
    max-height: 170px;
    display: flex;
    justify-content: center;
}
.mode-list { display: flex; flex-direction: column; gap: 2px; }
.mode-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid #f2f3f6;
}
.mode-item:last-child { border-bottom: none; }
.mode-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--bg);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: var(--muted);
    flex-shrink: 0;
}
.mode-label { flex: 1; font-size: 13px; font-weight: 600; color: var(--ink); }
.mode-right { text-align: right; }
.mode-amount { font-size: 13px; font-weight: 700; color: var(--gold); }
.mode-count  { font-size: 11px; color: var(--muted); }

/* ══════════════════════════════════════
   TABLE TOP PRODUITS
══════════════════════════════════════ */
.table-wrap { overflow-x: auto; }
.rpt-table {
    width: 100%;
    border-collapse: collapse;
}
.rpt-table thead tr { border-bottom: 1px solid var(--border); }
.rpt-table thead th {
    padding: 10px 22px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
}
.rpt-table tbody tr {
    border-bottom: 1px solid #f2f3f6;
    transition: background .1s;
}
.rpt-table tbody tr:last-child { border-bottom: none; }
.rpt-table tbody tr:hover { background: var(--bg); }
.rpt-table tbody td {
    padding: 12px 22px;
    font-size: 13px;
    color: #374151;
    vertical-align: middle;
}

/* Médailles */
.medal {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px; height: 22px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
}
.medal.gold   { background: #c9a96e20; color: #c9a96e; }
.medal.silver { background: #b0b7c320; color: #8a93a5; }
.medal.bronze { background: #cd7f3220; color: #cd7f32; }
.rank-num     { font-size: 12px; color: var(--muted); font-weight: 700; }

.prod-nom { font-weight: 700; color: var(--ink); }
.prod-ca  { font-weight: 700; color: var(--gold) !important; }
.qty-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    background: var(--bg);
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
}

/* ══════════════════════════════════════
   DÉPENSES PAR CATÉGORIE
══════════════════════════════════════ */
.dep-item {
    padding: 12px 22px;
    border-bottom: 1px solid #f2f3f6;
}
.dep-item:last-child { border-bottom: none; }
.dep-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.dep-label  { font-size: 13px; font-weight: 600; color: var(--ink); }
.dep-amount { font-size: 13px; font-weight: 700; color: #e07070; }
.dep-bar-wrap {
    height: 4px;
    background: var(--bg);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 3px;
}
.dep-bar {
    height: 100%;
    background: linear-gradient(90deg, #e07070, #f0a0a0);
    border-radius: 4px;
    transition: width .6s ease;
}
.dep-pct { font-size: 11px; color: var(--muted); text-align: right; }

/* ══════════════════════════════════════
   EMPTY
══════════════════════════════════════ */
.empty-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 32px 0;
    color: var(--muted);
    font-size: 13px;
}
.empty-row i { font-size: 26px; opacity: .25; }

/* ══════════════════════════════════════
   PRINT
══════════════════════════════════════ */
@media print {
    #sidebar, #topbar, .btn-print, .periode-tabs { display: none !important; }
    #main-content { margin-left: 0 !important; padding-top: 0 !important; }
    .rpt-panel { break-inside: avoid; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#9299a8';

// ── Données PHP ──────────────────────────────────────
const ventesLabels = @json($ventes_par_jour->pluck('date'));
const ventesData   = @json($ventes_par_jour->pluck('total'));

const modesLabels  = @json($modes->map(fn($m) => ucfirst(str_replace('_',' ',$m->mode))));
const modesData    = @json($modes->pluck('total'));

const heuresLabels = Array.from({length:24}, (_,i) => i+'h');
const heuresRaw    = @json($par_heure);
const heuresData   = Array.from({length:24}, (_,i) => {
    const found = heuresRaw.find(h => h.heure == i);
    return found ? found.nb : 0;
});

// ── Ventes 30 jours ─────────────────────────────────
new Chart(document.getElementById('chartVentes'), {
    type: 'bar',
    data: {
        labels: ventesLabels,
        datasets: [{
            label: 'Ventes (F)',
            data: ventesData,
            backgroundColor: 'rgba(201,169,110,0.15)',
            borderColor: '#c9a96e',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a1a2e',
                titleColor: '#fff',
                bodyColor: '#c9a96e',
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: ctx => '  ' + ctx.parsed.y.toLocaleString('fr') + ' F'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                border: { display: false },
                ticks: { callback: v => v.toLocaleString('fr') + ' F' }
            },
            x: {
                grid: { display: false },
                border: { display: false }
            }
        }
    }
});

// ── Modes paiement ───────────────────────────────────
new Chart(document.getElementById('chartModes'), {
    type: 'doughnut',
    data: {
        labels: modesLabels,
        datasets: [{
            data: modesData,
            backgroundColor: ['#c9a96e','#6b9fd4','#7ec88a','#e07070'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '68%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1a1a2e',
                titleColor: '#fff',
                bodyColor: '#c9a96e',
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: ctx => '  ' + ctx.parsed.toLocaleString('fr') + ' F'
                }
            }
        }
    }
});

// ── Pic d'activité ───────────────────────────────────
const maxHeure = Math.max(...heuresData);
new Chart(document.getElementById('chartHeures'), {
    type: 'bar',
    data: {
        labels: heuresLabels,
        datasets: [{
            label: 'Commandes',
            data: heuresData,
            backgroundColor: heuresData.map(v =>
                v === maxHeure && v > 0 ? '#c9a96e' : 'rgba(201,169,110,0.18)'
            ),
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a1a2e',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10,
                cornerRadius: 8,
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0,0,0,0.04)' },
                border: { display: false }
            },
            x: {
                grid: { display: false },
                border: { display: false },
                ticks: {
                    // N'afficher que les heures paires pour ne pas surcharger
                    callback: (_, i) => i % 2 === 0 ? heuresLabels[i] : ''
                }
            }
        }
    }
});
</script>
@endpush