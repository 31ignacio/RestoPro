@php
$statusConfig = [
    'en_attente' => ['⏳ En attente',     's-attente', 0],
    'en_cuisson' => ['🔥 En préparation',  's-cuisson', 1],
    'prete'      => ['✅ Prête à servir',  's-prete',   2],
    'servie'     => ['🍽 Servie',          's-servie',  3],
];
[$statusLabel, $statusCls, $statusPos] = $statusConfig[$cmd->statut] ?? [$cmd->statut, '', 0];

$modifiable = in_array($cmd->statut, ['en_attente', 'en_cuisson']);
$estServie  = $cmd->statut === 'servie';

$steps = [
    ['Commande reçue',  'bi-check-circle', 0],
    ['En préparation',  'bi-fire',         1],
    ['Prête à servir',  'bi-bell',         2],
    ['Servie',          'bi-emoji-smile',  3],
];

// ✅ Encoder proprement pour attribut data-*
$itemsForJs = $cmd->items->map(fn($i) => [
    'produit_id'    => (int) $i->produit_id,
    'nom'           => $i->produit->nom,
    'prix_unitaire' => (float) $i->prix_unitaire,
    'quantite'      => (float) $i->quantite,
    'sous_total'    => (float) $i->sous_total,
])->values()->toArray();

// ✅ json_encode avec flags pour éviter les problèmes HTML
$itemsJson = json_encode($itemsForJs, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
@endphp

<div class="commande-card" id="card-{{ $cmd->id }}">

    {{-- Header --}}
    <div class="cmd-header">
        <div>
            <div class="cmd-num">{{ $cmd->numero }}</div>
            <div class="cmd-time">{{ $cmd->created_at->diffForHumans() }}</div>
        </div>
        <span class="statut-badge {{ $statusCls }}">{{ $statusLabel }}</span>
        <div class="cmd-total">
            {{ number_format($cmd->total, 0, ',', ' ') }} {{ $monnaie }}
        </div>
    </div>

    {{-- Timeline --}}
    <div class="cmd-timeline">
        <ul class="tl">
            @foreach($steps as [$label, $icon, $pos])
            @php
                $done = $pos < $statusPos;
                $actv = $pos === $statusPos;
                $wait = $pos > $statusPos;
                $cls  = $done ? 'done' : ($actv ? 'active pulse' : 'wait');
            @endphp
            <li class="tl-row">
                <div class="tl-dot {{ $cls }}">
                    @if($done)
                        <i class="bi bi-check-lg" style="font-size:11px"></i>
                    @elseif($actv)
                        <i class="bi {{ $icon }}" style="font-size:11px"></i>
                    @else
                        <span style="font-size:9px;font-weight:700">{{ $pos + 1 }}</span>
                    @endif
                </div>
                <div class="tl-label {{ $wait ? 'muted' : '' }}">{{ $label }}</div>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Articles --}}
    <div class="cmd-items">
        @foreach($cmd->items as $item)
        <div class="item-row">
            <div style="display:flex;align-items:center;flex:1;min-width:0">
                <span class="item-qty">{{ $item->quantite }}×</span>
                <span style="flex:1;font-size:13px;color:#1a1a2e">
                    {{ $item->produit->nom }}
                </span>
            </div>
            <span class="item-prix">
                {{ number_format($item->sous_total, 0, ',', ' ') }} {{ $monnaie }}
            </span>
        </div>
        @endforeach
    </div>

    {{-- Message servie --}}
    <div class="servi-section" @if(!$estServie) style="display:none" @endif>
        <div class="servi-msg">
            <div class="servi-emoji">🍽</div>
            <div>
                <div class="servi-title">Votre commande a été servie !</div>
                <div class="servi-sub">Bon appétit ! Merci de votre visite. 😊</div>
            </div>
        </div>
    </div>

    {{-- ✅ Actions — données dans data-* pour éviter les conflits JSON --}}
    @if(!$estServie)
    <div class="cmd-actions" id="actions-{{ $cmd->id }}">
        @if($modifiable)
        {{-- ✅ Stocker le JSON dans data-items, pas dans onclick --}}
        <button class="btn-modifier"
            id="btn-modifier-{{ $cmd->id }}"
            data-numero="{{ $cmd->numero }}"
            data-modifiable="1"
            data-items="{{ htmlspecialchars($itemsJson, ENT_QUOTES, 'UTF-8') }}"
            onclick="ouvrirModifFromBtn(this)">
            <i class="bi bi-pencil" style="font-size:13px"></i>
            Modifier la commande
        </button>
        @else
        <button class="btn-modifier locked"
            onclick="showToast('⚠️ Cette commande est prête — modification impossible.', 'warning')">
            <i class="bi bi-lock" style="font-size:13px"></i>
            Prête — modification impossible
        </button>
        @endif
    </div>
    @endif

</div>