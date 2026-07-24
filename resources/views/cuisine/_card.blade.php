@php
    $mins    = max(0, $cmd->created_at->diffInMinutes(now()));
    $urgent  = $mins >= 20;
    $warn    = $mins >= 10;
    $timerCl = $urgent ? 'timer-danger' : ($warn ? 'timer-warn' : 'timer-ok');
    $cardCl  = $cmd->statut === 'en_cuisson' ? 'cuisson' : ($urgent ? 'urgent' : 'normal');
    $isManaged = $cmd->canBeManagedBy(auth()->user());

    // Nouveau critère : on compare au statut COURANT de la commande,
    // plus uniquement à 'en_attente' → fonctionne aussi en colonne "En cuisson"
    $hasCurrentItems = $cmd->items->contains(fn($item) => $item->statut === $cmd->statut);
    $hasOldItems     = $cmd->items->contains(fn($item) => $item->statut !== $cmd->statut);
    $separatorShown  = false;

    $oldBadgeLabel = fn($statut) => match($statut) {
        'prete'      => 'déjà servi',
        'en_cuisson' => 'déjà en cuisson',
        default      => 'déjà transmis',
    };
@endphp

<div class="cuisine-card {{ $cardCl }}" id="cuisine-card-{{ $cmd->id }}"
    data-created="{{ $cmd->created_at->toIso8601String() }}">
    <div class="d-flex align-items-start justify-content-between mb-2">
        <div>
            <div class="fw-bold">{{ $cmd->numero }}</div>
            <small class="text-muted">
                {{ $cmd->table ? 'Table '.$cmd->table->numero : 'Emporter' }}
                · {{ $cmd->type === 'sur_place' ? 'Sur place' : ucfirst($cmd->type) }}
            </small>
        </div>
        <span class="timer-badge {{ $timerCl }}">
            <i class="bi bi-clock me-1"></i>{{ $mins }} MIN
        </span>
    </div>

    <div class="mb-2">
        @foreach($cmd->items as $item)
        @php $isOld = $hasCurrentItems && $item->statut !== $cmd->statut; @endphp
        @if($hasCurrentItems && $hasOldItems && $item->statut === $cmd->statut && ! $separatorShown)
            @php $separatorShown = true; @endphp
            <div class="item-separator"><span>Ajout client</span></div>
        @endif
        <div class="item-line {{ $isOld ? 'item-line-old' : '' }}">
            <div class="item-qty">{{ $item->quantite }}</div>
            <div class="flex-grow-1">{{ $item->produit->nom }}</div>
            @if($isOld)
                <small class="item-old-badge"><i class="bi bi-check2"></i> {{ $oldBadgeLabel($item->statut) }}</small>
            @endif
            @if($item->notes)
                <small class="text-muted fst-italic">{{ $item->notes }}</small>
            @endif
        </div>
        @endforeach
    </div>

    {{-- le reste (notes commande, cuisinier, bouton) ne change pas --}}
    @if($cmd->notes)
    <div class="alert alert-light py-1 px-2 mb-2" style="font-size:12px">
        <i class="bi bi-chat-left-text me-1"></i>{{ $cmd->notes }}
    </div>
    @endif

    <div class="small text-muted mb-2">
        <i class="bi bi-person-badge me-1"></i>
        {{ $cmd->cuisinier ? 'Attribuée à '.$cmd->cuisinier->name : 'Non attribuée' }}
    </div>

    @if($isManaged)
        @if($cmd->statut === 'en_attente')
        <button class="btn-cuisine btn-prendre" onclick="prendreCuisson({{ $cmd->id }}, this)">
            <i class="bi bi-fire me-1"></i>Prendre en charge
        </button>
        @else
        <button class="btn-cuisine btn-prete" onclick="marquerPrete({{ $cmd->id }}, this)">
            <i class="bi bi-check-lg me-1"></i>Marquer prête
        </button>
        @endif
    @else
        <div class="btn-cuisine text-center" style="background:#f3f4f6;color:#6b7280;cursor:default">
            <i class="bi bi-eye me-1"></i>Consultation seule
        </div>
    @endif
</div>