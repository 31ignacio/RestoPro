<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $restaurant['nom'] }} — Menu</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{
  --bg:     #F4F4F2;
  --white:  #FFFFFF;
  --card:   #FFFFFF;
  --border: #EAEAE8;
  --ink:    #111110;
  --ink2:   #6B6B69;
  --ink3:   #AEAEAC;
  --pop:    #2B6CB0;   /* bleu sobre — 1 seule couleur d'accent */
  --pop-lt: rgba(43,108,176,.08);
  --pop-md: rgba(43,108,176,.18);
  --sans:   'Inter',  system-ui, sans-serif;
  --head:   'Syne',   system-ui, sans-serif;
  --r: 14px; --r-sm: 9px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--bg);color:var(--ink);-webkit-font-smoothing:antialiased;padding-bottom:110px;overflow-x:hidden}

/* ─── TICKER ─── */
.ticker{background:var(--ink);overflow:hidden;white-space:nowrap;padding:8px 0}
.ticker-inner{display:inline-block;animation:tick 38s linear infinite;white-space:nowrap}
.ticker-inner span{font-size:11.5px;font-weight:400;color:rgba(255,255,255,.6);padding:0 22px;letter-spacing:.02em}
.ticker-inner span b{color:#fff;font-weight:600}
.ticker-inner .sep{color:rgba(255,255,255,.2);padding:0 2px}
@keyframes tick{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ─── HEADER ─── */
.hdr{background:var(--white);position:sticky;top:0;z-index:100;border-bottom:1px solid var(--border)}
.hdr-top{display:flex;align-items:center;justify-content:space-between;padding:13px 16px 11px}
.brand{display:flex;align-items:center;gap:10px}
.brand-ico{
  width:36px;height:36px;border-radius:10px;background:var(--ink);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--head);font-size:16px;font-weight:800;color:#fff;flex-shrink:0;
}
.brand-name{font-family:var(--head);font-size:17px;font-weight:700;color:var(--ink);line-height:1.1}
.brand-sub{font-size:11px;color:var(--ink3);margin-top:1px;letter-spacing:.02em}
.tbl-chip{
  display:flex;align-items:center;gap:5px;
  border:1px solid var(--border);border-radius:30px;
  padding:6px 12px;font-size:12px;font-weight:500;color:var(--ink2);
  background:var(--bg);
}

/* ─── SEARCH ─── */
.search-row{padding:10px 16px 0}
.search-box{
  display:flex;align-items:center;gap:8px;
  background:var(--bg);border:1.5px solid var(--border);
  border-radius:11px;padding:10px 14px;transition:border-color .15s;
}
.search-box:focus-within{border-color:var(--pop)}
.search-box input{background:transparent;border:none;outline:none;color:var(--ink);font-size:14px;flex:1;font-family:var(--sans)}
.search-box input::placeholder{color:var(--ink3)}
.search-box i{color:var(--ink3);font-size:14px;flex-shrink:0}
#s-clear{cursor:pointer;display:none}

/* ─── CATS ─── */
.cats{display:flex;gap:7px;overflow-x:auto;padding:10px 16px 13px}
.cats::-webkit-scrollbar{display:none}
.c-pill{
  display:inline-flex;align-items:center;gap:5px;
  padding:7px 14px;border-radius:30px;
  border:1.5px solid var(--border);background:var(--white);
  color:var(--ink2);font-size:12.5px;font-weight:500;
  white-space:nowrap;cursor:pointer;font-family:var(--sans);
  transition:all .14s;flex-shrink:0;-webkit-appearance:none;
}
.c-pill.on{background:var(--ink);border-color:var(--ink);color:#fff;font-weight:600}
.c-pill:hover:not(.on){border-color:var(--ink2);color:var(--ink)}

/* ─── ORDER ACTIVE ─── */
.ord-banner{
  margin:14px 16px 0;
  background:var(--white);border:1.5px solid var(--border);
  border-left:3px solid var(--pop);border-radius:var(--r);
  padding:12px 14px;display:flex;align-items:center;gap:11px;
  box-shadow:0 1px 8px rgba(0,0,0,.05);
}
.ord-ico{font-size:20px;flex-shrink:0}
.ord-lbl{font-size:10px;color:var(--ink3);text-transform:uppercase;letter-spacing:.07em;font-weight:600}
.ord-num{font-size:15px;font-weight:700;color:var(--ink);font-family:var(--head)}
.ord-st{font-size:12px;color:var(--pop);margin-top:1px;font-weight:500}
.ord-btn{
  background:var(--ink);color:#fff;border:none;border-radius:8px;
  padding:8px 13px;font-size:12px;font-weight:600;text-decoration:none;
  cursor:pointer;font-family:var(--sans);white-space:nowrap;
}

/* ─── PROMOS CARDS ─── */
.promo-wrap{padding:18px 0 0 16px}
.section-label{font-size:10.5px;font-weight:700;color:var(--ink3);letter-spacing:.09em;text-transform:uppercase;margin-bottom:11px}
.promo-row{display:flex;gap:11px;overflow-x:auto;padding:0 16px 2px 0}
.promo-row::-webkit-scrollbar{display:none}
.p-card{
  flex-shrink:0;width:155px;border-radius:var(--r);
  overflow:hidden;background:var(--card);border:1.5px solid var(--border);
  cursor:pointer;box-shadow:0 1px 6px rgba(0,0,0,.05);
  transition:box-shadow .2s,transform .15s;
}
.p-card:active{transform:scale(.97)}
.p-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.09)}
.p-card-img{position:relative;height:102px;overflow:hidden}
.p-card-img img{width:100%;height:100%;object-fit:cover;display:block}
.p-badge{
  position:absolute;top:8px;left:8px;
  background:var(--ink);color:#fff;
  font-size:9px;font-weight:700;letter-spacing:.04em;
  padding:3px 8px;border-radius:20px;font-family:var(--sans);
}
.p-body{padding:9px 10px 11px}
.p-nom{font-size:12.5px;font-weight:600;color:var(--ink);margin-bottom:4px;line-height:1.3}
.p-prix{font-size:14px;font-weight:700;color:var(--ink);font-family:var(--head)}
.p-old{font-size:10.5px;color:var(--ink3);text-decoration:line-through;margin-left:4px}

/* ─── SECTION ─── */
.sec-block{padding:22px 16px 0}
.sec-head{display:flex;align-items:center;gap:11px;margin-bottom:13px}
.sec-ttl{font-family:var(--head);font-size:18px;font-weight:700;color:var(--ink);white-space:nowrap}
.sec-line{flex:1;height:1px;background:var(--border)}

/* ─── PROD CARD ─── */
.prod{
  background:var(--card);border:1.5px solid var(--border);
  border-radius:var(--r);margin-bottom:9px;
  display:flex;align-items:stretch;overflow:hidden;
  transition:border-color .14s;
  box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.prod.sel{border-color:var(--pop);box-shadow:0 2px 12px var(--pop-md)}
.prod-img-col{flex-shrink:0;width:86px;position:relative;overflow:hidden}
.prod-img{width:100%;height:100%;object-fit:cover;display:block;min-height:86px}
.prod-badge{
  position:absolute;top:6px;right:6px;
  background:var(--pop);color:#fff;
  min-width:20px;height:20px;border-radius:10px;padding:0 5px;
  display:flex;align-items:center;justify-content:center;
  font-size:9.5px;font-weight:800;
  opacity:0;transform:scale(0) rotate(-90deg);
  transition:opacity .18s,transform .2s cubic-bezier(.34,1.56,.64,1);
}
.prod.sel .prod-badge{opacity:1;transform:scale(1) rotate(0)}
.prod-info{flex:1;min-width:0;padding:12px 10px 12px 12px;display:flex;flex-direction:column;justify-content:space-between}
.prod-nom{font-size:14px;font-weight:600;color:var(--ink);margin-bottom:3px;line-height:1.25;font-family:var(--head)}
.prod-desc{font-size:11.5px;color:var(--ink3);line-height:1.5;margin-bottom:7px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.prod-foot{display:flex;align-items:center;justify-content:space-between}
.prod-prix{font-size:15px;font-weight:700;color:var(--ink);font-family:var(--head)}
.prod-note{display:inline-flex;align-items:center;gap:3px;font-size:11px;color:var(--ink2)}

/* ─── QTY ─── */
.qty-col{flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:10px 11px 10px 0;gap:3px}
.qty-add{
  width:35px;height:35px;border-radius:50%;
  background:var(--ink);color:#fff;border:none;
  display:flex;align-items:center;justify-content:center;
  font-size:22px;font-weight:300;cursor:pointer;line-height:1;
  touch-action:manipulation;transition:transform .1s;
}
.qty-add:active{transform:scale(.82)}
.qty-ctr{display:none;flex-direction:column;align-items:center;gap:3px}
.qty-ctr.show{display:flex}
.q-up,.q-dn{
  width:31px;height:31px;border-radius:50%;
  border:1.5px solid var(--border);background:var(--bg);
  display:flex;align-items:center;justify-content:center;
  font-size:18px;font-weight:400;cursor:pointer;line-height:1;
  color:var(--ink);touch-action:manipulation;transition:transform .1s;
}
.q-up{background:var(--pop);color:#fff;border-color:var(--pop)}
.q-up:active,.q-dn:active{transform:scale(.82)}
.q-num{font-size:14px;font-weight:700;color:var(--ink);min-width:26px;text-align:center;font-family:var(--head)}

/* ─── CART BAR ─── */
.cart-bar{
  position:fixed;bottom:0;left:0;right:0;z-index:300;
  padding:6px 16px 26px;
  background:linear-gradient(to top,var(--bg) 60%,transparent);
  pointer-events:none;
}
.cart-inner{
  max-width:520px;margin:0 auto;
  background:var(--ink);border-radius:var(--r);
  padding:12px 15px;
  display:flex;align-items:center;justify-content:space-between;
  box-shadow:0 8px 28px rgba(0,0,0,.16);
  transform:translateY(90px);opacity:0;
  transition:transform .35s cubic-bezier(.34,1.56,.64,1),opacity .2s;
  pointer-events:all;
}
.cart-inner.on{transform:translateY(0);opacity:1}
.cart-left{display:flex;align-items:center;gap:10px}
.cart-bub{
  background:var(--pop);color:#fff;min-width:28px;height:28px;border-radius:14px;padding:0 6px;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:800;flex-shrink:0;font-family:var(--head);
}
.cart-lbl{font-size:10.5px;color:rgba(255,255,255,.45);font-weight:400}
.cart-price{font-size:16px;font-weight:700;color:#fff;font-family:var(--head)}
.cart-cta{
  background:#fff;color:var(--ink);border:none;border-radius:9px;
  padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;
  font-family:var(--head);white-space:nowrap;touch-action:manipulation;
}
.cart-cta:active{transform:scale(.96)}

/* ─── BACKDROP ─── */
.backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,.45);
  z-index:400;opacity:0;pointer-events:none;transition:opacity .22s;
}
.backdrop.on{opacity:1;pointer-events:all}

/* ─── BOTTOM SHEET ─── */
.sheet{
  position:fixed;bottom:0;left:0;right:0;z-index:500;
  background:var(--white);border-radius:22px 22px 0 0;
  max-height:92vh;overflow:hidden;
  display:flex;flex-direction:column;
  transform:translateY(100%);
  transition:transform .36s cubic-bezier(.34,1.2,.64,1);
  border-top:1px solid var(--border);
  box-shadow:0 -4px 32px rgba(0,0,0,.1);
}
.sheet.on{transform:translateY(0)}
.sheet-drag{text-align:center;padding:12px 0 5px;flex-shrink:0}
.sheet-drag span{display:inline-block;width:38px;height:4px;background:var(--border);border-radius:2px}
.sheet-hdr{
  display:flex;align-items:center;justify-content:space-between;
  padding:5px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0;
}
.sheet-ttl{font-family:var(--head);font-size:19px;font-weight:700;color:var(--ink)}
.sheet-x{
  width:31px;height:31px;border-radius:50%;background:var(--bg);
  border:none;display:flex;align-items:center;justify-content:center;
  font-size:14px;color:var(--ink2);cursor:pointer;touch-action:manipulation;
}
.sheet-body{flex:1;overflow-y:auto;padding:0 20px}
.sheet-body::-webkit-scrollbar{width:2px}
.sheet-footer{flex-shrink:0;padding:14px 20px 28px;border-top:1px solid var(--border)}

/* ─── SHEET ITEMS ─── */
.si{display:flex;align-items:center;gap:11px;padding:13px 0;border-bottom:1px solid var(--border)}
.si:last-child{border-bottom:none}
.si-ico{width:44px;height:44px;border-radius:11px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.si-nom{font-size:13.5px;font-weight:600;color:var(--ink);font-family:var(--head)}
.si-px{font-size:12px;color:var(--ink2);margin-top:2px;font-weight:500}
.si-unit{font-weight:400;font-size:10.5px;color:var(--ink3)}
.si-qty{display:flex;align-items:center;gap:8px;flex-shrink:0}
.si-btn{
  width:29px;height:29px;border-radius:50%;border:1.5px solid var(--border);
  background:var(--bg);display:flex;align-items:center;justify-content:center;
  font-size:16px;cursor:pointer;color:var(--ink);touch-action:manipulation;
}
.si-btn:active{transform:scale(.82)}
.si-btn.up{background:var(--ink);color:#fff;border-color:var(--ink)}
.si-n{font-size:14px;font-weight:700;min-width:26px;text-align:center;font-family:var(--head);color:var(--ink)}

/* ─── TOTALS ─── */
.t-row{display:flex;justify-content:space-between;padding:5px 0;font-size:13px;color:var(--ink3)}
.t-main{display:flex;justify-content:space-between;padding:12px 0 0;border-top:1px solid var(--border);margin-top:4px}
.t-main span:first-child{font-family:var(--head);font-size:18px;font-weight:700;color:var(--ink)}
.t-main span:last-child{font-family:var(--head);font-size:18px;font-weight:700;color:var(--pop)}

/* ─── STEPS ─── */
.dots{display:flex;gap:5px;justify-content:center;margin-bottom:13px}
.dot{width:6px;height:6px;border-radius:50%;background:var(--border);transition:all .2s}
.dot.on{background:var(--ink);width:20px;border-radius:3px}

/* ─── INPUTS ─── */
.iw{margin-bottom:11px}
.ilbl{font-size:10.5px;font-weight:600;color:var(--ink3);text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;display:block}
.ifield{
  width:100%;padding:12px 13px;
  border:1.5px solid var(--border);border-radius:11px;
  font-size:14px;font-family:var(--sans);color:var(--ink);
  outline:none;background:var(--bg);transition:border-color .14s;
}
.ifield:focus{border-color:var(--pop);background:var(--white)}
.ifield::placeholder{color:var(--ink3)}
textarea.ifield{resize:none}

/* ─── BTNS ─── */
.btn-dark{
  width:100%;padding:15px;background:var(--ink);color:#fff;
  border:none;border-radius:var(--r-sm);font-size:14.5px;font-weight:700;
  cursor:pointer;font-family:var(--head);
  display:flex;align-items:center;justify-content:center;gap:8px;
  touch-action:manipulation;margin-top:14px;
}
.btn-dark:active{opacity:.88}
.btn-dark:disabled{opacity:.45;cursor:not-allowed}
.btn-ghost{width:100%;padding:11px;background:none;border:none;font-size:13px;color:var(--ink3);cursor:pointer;font-family:var(--sans);margin-top:4px}

/* ─── MISC ─── */
.spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.empty{text-align:center;padding:44px 20px;color:var(--ink3)}
.empty i{font-size:40px;display:block;margin-bottom:10px;opacity:.22}
.empty p{font-size:14px;line-height:1.7}
.page-end{text-align:center;padding:28px 0 6px;font-size:10.5px;color:var(--ink3);letter-spacing:.2em;text-transform:uppercase}
.d-none{display:none!important}
@media(max-width:390px){.prod-img-col{width:76px}.p-card{width:142px}}
</style>
</head>
<body>

{{-- ── TICKER ── --}}
<div class="ticker" aria-hidden="true">
  <div class="ticker-inner" id="ticker">
    <span>🍽 Bienvenue — commandez depuis votre table en quelques secondes</span><span class="sep">·</span>
    <span>🧑‍🍳 <b>Tous nos plats sont préparés à la commande</b>, avec des produits frais</span><span class="sep">·</span>
    <span>⭐ Plat du jour recommandé par notre chef — demandez à votre serveur</span><span class="sep">·</span>
    <span>🥤 Boissons fraîches disponibles — consultez la section Boissons</span><span class="sep">·</span>
    <span>⚠️ Allergies ou régimes spéciaux ? Précisez-le dans les notes</span><span class="sep">·</span>
    <span>✅ Votre commande arrive <b>directement en cuisine</b> — sans attendre un serveur</span><span class="sep">·</span>
    <span>🍽 Bienvenue — commandez depuis votre table en quelques secondes</span><span class="sep">·</span>
    <span>🧑‍🍳 <b>Tous nos plats sont préparés à la commande</b>, avec des produits frais</span><span class="sep">·</span>
    <span>⭐ Plat du jour recommandé par notre chef — demandez à votre serveur</span><span class="sep">·</span>
    <span>🥤 Boissons fraîches disponibles — consultez la section Boissons</span><span class="sep">·</span>
    <span>⚠️ Allergies ou régimes spéciaux ? Précisez-le dans les notes</span><span class="sep">·</span>
    <span>✅ Votre commande arrive <b>directement en cuisine</b> — sans attendre un serveur</span><span class="sep">·</span>
  </div>
</div>

{{-- ── HEADER ── --}}
<div class="hdr">
  <div class="hdr-top">
    <div class="brand">
      <div class="brand-ico">{{ strtoupper(substr($restaurant['nom'], 0, 1)) }}</div>
      <div>
        <div class="brand-name">{{ $restaurant['nom'] }}</div>
        <div class="brand-sub">Menu · Commande à table</div>
      </div>
    </div>
    <div class="tbl-chip">
      <i class="bi bi-grid-2x2" style="font-size:12px"></i>
      Table {{ $table->numero }}
    </div>
  </div>

  <div class="search-row">
    <div class="search-box">
      <i class="bi bi-search"></i>
      <input type="text" id="s-input" placeholder="Rechercher un plat…"
             autocomplete="off" oninput="doSearch(this.value)">
      <i class="bi bi-x" id="s-clear" onclick="clearSearch()"></i>
    </div>
  </div>

  <div class="cats" id="cats">
    <button class="c-pill on" onclick="filterCat('all',this)" type="button">
      <i class="bi bi-grid-2x2"></i> Tout
    </button>
    @foreach($categories as $cat)
    <button class="c-pill" onclick="filterCat('{{ $cat->id }}',this)" type="button"
            data-cat="{{ $cat->id }}">
      <i class="bi bi-{{ $cat->icone ?? 'tag' }}"></i> {{ $cat->nom }}
    </button>
    @endforeach
  </div>
</div>

{{-- ── COMMANDE ACTIVE ── --}}
@if(isset($commandeActive) && $commandeActive)
@php $sl=['en_attente'=>'En attente','en_cuisson'=>'En cuisson 🔥','prete'=>'Prête ✓']; @endphp
<div style="padding:14px 16px 0">
  <div class="ord-banner">
    <div class="ord-ico">🧾</div>
    <div style="flex:1">
      <div class="ord-lbl">Commande en cours</div>
      <div class="ord-num">{{ $commandeActive->numero }}</div>
      <div class="ord-st">{{ $sl[$commandeActive->statut] ?? $commandeActive->statut }}</div>
    </div>
    <a href="{{ route('menu.suivi', $commandeActive->numero) }}" class="ord-btn">Suivre →</a>
  </div>
</div>
@endif

{{-- ── PROMOS ── --}}
@php $promos = collect($categories)->flatMap(fn($c)=>$c->produits)->where('mis_en_avant',true)->take(6); @endphp
@if($promos->count())
<div class="promo-wrap">
  <div class="section-label">Sélection du chef</div>
  <div class="promo-row">
    @foreach($promos as $p)
    <div class="p-card" onclick="quickAdd({{ $p->id }},'{{ addslashes($p->nom) }}',{{ $p->prix }})">
      <div class="p-card-img">
        <img src="{{ $p->photo_url }}" alt="{{ $p->nom }}"
             onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=65'"
             loading="lazy">
        <div class="p-badge">
          @if($p->remise)
            −{{ $p->remise }}%
          @else
            Populaire
          @endif
        </div>
      </div>
      <div class="p-body">
        <div class="p-nom">{{ $p->nom }}</div>
        <div style="display:flex;align-items:baseline;gap:4px">
          <span class="p-prix">{{ number_format($p->prix,0,',',' ') }} {{ $restaurant['monnaie'] }}</span>
          @if($p->prix_original)
          <span class="p-old">{{ number_format($p->prix_original,0,',',' ') }}</span>
          @endif
        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- ── MENU ── --}}
<div id="menu-wrap">
  @foreach($categories as $cat)
  <div class="sec-block" id="sec-{{ $cat->id }}" data-sec="{{ $cat->id }}">
    <div class="sec-head">
      <span class="sec-ttl">{{ $cat->nom }}</span>
      <div class="sec-line"></div>
    </div>

    @foreach($cat->produits as $p)
    <div class="prod" id="pc-{{ $p->id }}"
         data-nom="{{ strtolower($p->nom.' '.($p->description ?? '')) }}"
         data-cat="{{ $cat->id }}">
      <div class="prod-img-col">
        <img class="prod-img"
             src="{{ $p->photo_url }}" alt="{{ $p->nom }}" loading="lazy"
             onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=200&q=70'">
        <div class="prod-badge" id="badge-{{ $p->id }}">0</div>
      </div>
      <div class="prod-info">
        <div>
          <div class="prod-nom">{{ $p->nom }}</div>
          @if($p->description)
          <div class="prod-desc">{{ $p->description }}</div>
          @endif
        </div>
        <div class="prod-foot">
          <span class="prod-prix">{{ number_format($p->prix,0,',',' ') }} {{ $restaurant['monnaie'] }}</span>
          @if($p->note)
          <span class="prod-note"><i class="bi bi-star-fill" style="font-size:10px;color:#F59E0B"></i> {{ $p->note }}</span>
          @endif
        </div>
      </div>
      <div class="qty-col">
        <button class="qty-add" id="btn-add-{{ $p->id }}"
          onclick="add({{ $p->id }},'{{ addslashes($p->nom) }}',{{ $p->prix }})"
          type="button" aria-label="Ajouter">+</button>
        <div class="qty-ctr" id="ctr-{{ $p->id }}">
          <button class="q-up" onclick="add({{ $p->id }},'{{ addslashes($p->nom) }}',{{ $p->prix }})" type="button">+</button>
          <div class="q-num" id="num-{{ $p->id }}">0</div>
          <button class="q-dn" onclick="rem({{ $p->id }})" type="button">−</button>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endforeach

  <div class="page-end">· fin du menu ·</div>
  <div class="empty d-none" id="no-res">
    <i class="bi bi-search"></i>
    <p>Aucun plat ne correspond<br>à votre recherche.</p>
  </div>
</div>

{{-- ── CART BAR ── --}}
<div class="cart-bar" aria-live="polite">
  <div class="cart-inner" id="cart-inner">
    <div class="cart-left">
      <div class="cart-bub" id="cart-bub">0</div>
      <div>
        <div class="cart-lbl">Votre panier</div>
        <div class="cart-price" id="cart-price">0 {{ $restaurant['monnaie'] }}</div>
      </div>
    </div>
    <button class="cart-cta" onclick="openSheet()" type="button">Voir le panier →</button>
  </div>
</div>

{{-- ── BACKDROP ── --}}
<div class="backdrop" id="backdrop" onclick="closeSheet()"></div>

{{-- ── BOTTOM SHEET ── --}}
<div class="sheet" id="sheet">
  <div class="sheet-drag"><span></span></div>
  <div class="sheet-hdr">
    <div class="sheet-ttl" id="sheet-ttl">Mon panier</div>
    <button class="sheet-x" onclick="closeSheet()" type="button">✕</button>
  </div>

  {{-- STEP 1 : panier --}}
  <div id="s1" style="display:flex;flex-direction:column;flex:1;overflow:hidden">
    <div class="sheet-body" id="sheet-body"></div>
    <div class="sheet-footer">
      <div class="dots"><div class="dot on"></div><div class="dot"></div></div>
      <div class="t-row"><span>Sous-total</span><span id="subtotal">0 {{ $restaurant['monnaie'] }}</span></div>
      <div class="t-main">
        <span>Total</span>
        <span id="total-disp">0 {{ $restaurant['monnaie'] }}</span>
      </div>
      <button class="btn-dark" onclick="goS2()" type="button">
        Continuer <i class="bi bi-arrow-right"></i>
      </button>
    </div>
  </div>

  {{-- STEP 2 : infos --}}
  <div id="s2" style="display:none;flex-direction:column;flex:1;overflow:hidden">
    <div class="sheet-body" style="padding-top:16px">
      <div style="margin-bottom:16px">
        <div style="font-family:var(--head);font-size:17px;font-weight:700;color:var(--ink);margin-bottom:3px">Vos informations</div>
        <div style="font-size:12.5px;color:var(--ink3)">Pour préparer et servir votre commande</div>
      </div>
      <div class="iw">
        <label class="ilbl">Prénom et nom *</label>
        <input type="text" class="ifield" id="client-nom" placeholder="ex. Jean Kokou" autocomplete="name">
      </div>
      <div class="iw">
        <label class="ilbl">Téléphone</label>
        <input type="tel" class="ifield" id="client-tel" placeholder="Optionnel" autocomplete="tel">
      </div>
      <div class="iw">
        <label class="ilbl">Notes pour la cuisine</label>
        <textarea class="ifield" id="cmd-notes" rows="3" placeholder="Allergies, cuisson, sans sel…"></textarea>
      </div>
    </div>
    <div class="sheet-footer">
      <div class="dots"><div class="dot"></div><div class="dot on"></div></div>
      <button class="btn-dark" id="btn-cmd" onclick="sendOrder()" type="button">
        <i class="bi bi-send-fill"></i>
        <span id="btn-txt">Envoyer ma commande</span>
        <div class="spinner d-none" id="btn-spin"></div>
      </button>
      <button class="btn-ghost" onclick="goS1()" type="button">← Retour au panier</button>
    </div>
  </div>
</div>

<script>
const P={}, UUID='{{ $table->uuid }}', MON='{{ $restaurant["monnaie"] }}';
const QTY_STEP = 0.5; /* pas de quantité : 0.5, 1, 1.5, 2, 2.5 … */

/* ── HELPERS QUANTITÉS DÉCIMALES ── */
function round05(v){
  /* évite les erreurs de flottant (0.1+0.2 etc.) en arrondissant au 0.5 le plus proche */
  return Math.round(v/QTY_STEP)*QTY_STEP;
}
function fmtQty(v){
  v=round05(v);
  const s = Number.isInteger(v) ? String(v) : v.toFixed(1);
  return s.replace('.',',');
}
function fmtMoney(v){
  return Math.round(v).toLocaleString('fr')+' '+MON;
}

/* ── CORE ── */
function add(id,nom,prix){
  P[id]=P[id]||{id,nom,prix:+prix,q:0};
  P[id].q=round05(P[id].q+QTY_STEP);
  syncCard(id);syncBar();
}
function rem(id){
  if(!P[id])return;
  P[id].q=round05(P[id].q-QTY_STEP);
  if(P[id].q<=0)delete P[id];
  syncCard(id);syncBar();
}
function quickAdd(id,nom,prix){add(id,nom,prix);}

function syncCard(id){
  const btnAdd=document.getElementById('btn-add-'+id);
  const ctr   =document.getElementById('ctr-'+id);
  const num   =document.getElementById('num-'+id);
  const badge =document.getElementById('badge-'+id);
  const card  =document.getElementById('pc-'+id);
  if(!btnAdd)return;
  const q=P[id]?.q??0;
  if(q>0){
    btnAdd.style.display='none';ctr.classList.add('show');
    num.textContent=fmtQty(q);badge.textContent=fmtQty(q);card.classList.add('sel');
  }else{
    btnAdd.style.display='';ctr.classList.remove('show');
    badge.textContent='0';card.classList.remove('sel');
  }
}
function syncBar(){
  const ids=Object.keys(P);
  const tot=ids.reduce((s,id)=>s+P[id].prix*P[id].q,0);
  const nb =ids.reduce((s,id)=>s+P[id].q,0);
  document.getElementById('cart-bub').textContent=fmtQty(nb);
  document.getElementById('cart-price').textContent=fmtMoney(tot);
  document.getElementById('cart-inner').classList.toggle('on',nb>0);
}

/* ── SHEET ── */
function openSheet(){
  renderSheet();
  document.getElementById('s1').style.display='flex';
  document.getElementById('s2').style.display='none';
  document.getElementById('sheet-ttl').textContent='Mon panier';
  document.getElementById('backdrop').classList.add('on');
  document.getElementById('sheet').classList.add('on');
  document.body.style.overflow='hidden';
}
function closeSheet(){
  document.getElementById('backdrop').classList.remove('on');
  document.getElementById('sheet').classList.remove('on');
  document.body.style.overflow='';
}
function renderSheet(){
  const ids=Object.keys(P);
  const tot=ids.reduce((s,id)=>s+P[id].prix*P[id].q,0);
  document.getElementById('subtotal').textContent=fmtMoney(tot);
  document.getElementById('total-disp').textContent=fmtMoney(tot);
  const body=document.getElementById('sheet-body');
  if(!ids.length){
    body.innerHTML='<div class="empty"><i class="bi bi-bag-x"></i><p>Votre panier est vide.<br>Ajoutez des plats !</p></div>';
    return;
  }
  body.innerHTML=ids.map(id=>{
    const it=P[id];
    return `<div class="si" id="si-${id}">
      <div class="si-ico">🍽</div>
      <div style="flex:1;min-width:0">
        <div class="si-nom">${it.nom}</div>
        <div class="si-px">${fmtMoney(it.prix*it.q)} <span class="si-unit">× ${fmtQty(it.q)}</span></div>
      </div>
      <div class="si-qty">
        <button class="si-btn" onclick="remSheet(${id})" type="button">−</button>
        <span class="si-n">${fmtQty(it.q)}</span>
        <button class="si-btn up" onclick="addSheet(${id},'${it.nom.replace(/'/g,"\\'")}',${it.prix})" type="button">+</button>
      </div>
    </div>`;
  }).join('');
}
function addSheet(id,nom,prix){add(id,nom,prix);renderSheet();}
function remSheet(id){
  rem(id);
  if(!P[id]){
    const el=document.getElementById('si-'+id);
    if(el){el.style.transition='opacity .16s';el.style.opacity='0';setTimeout(()=>renderSheet(),160);}
  }else renderSheet();
}
function goS2(){
  if(!Object.keys(P).length)return;
  document.getElementById('s1').style.display='none';
  document.getElementById('s2').style.display='flex';
  document.getElementById('sheet-ttl').textContent='Vos informations';
  setTimeout(()=>document.getElementById('client-nom').focus(),100);
}
function goS1(){
  document.getElementById('s2').style.display='none';
  document.getElementById('s1').style.display='flex';
  document.getElementById('sheet-ttl').textContent='Mon panier';
}

/* ── SEND ── */
function sendOrder(){
  const nom=document.getElementById('client-nom').value.trim();
  if(!nom){
    const f=document.getElementById('client-nom');
    f.style.borderColor='#D94040';f.focus();
    setTimeout(()=>f.style.borderColor='',2200);return;
  }
  const ids=Object.keys(P);if(!ids.length)return;
  const btn=document.getElementById('btn-cmd');
  document.getElementById('btn-txt').classList.add('d-none');
  document.getElementById('btn-spin').classList.remove('d-none');
  btn.disabled=true;
  fetch('/menu/commander',{
    method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json',
             'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body:JSON.stringify({
      table_uuid:UUID,
      client_nom:nom,
      client_tel:document.getElementById('client-tel').value.trim()||null,
      notes:document.getElementById('cmd-notes').value.trim(),
      items:ids.map(id=>({produit_id:+id,quantite:P[id].q,notes:''})),
    }),
  })
  .then(r=>r.json())
  .then(d=>{
    document.getElementById('btn-txt').classList.remove('d-none');
    document.getElementById('btn-spin').classList.add('d-none');
    btn.disabled=false;
    if(d.success){
      closeSheet();
      Object.keys(P).forEach(id=>{delete P[id];syncCard(id);});
      syncBar();
      window.location.href=d.suivi_url;
    }else alert('❌ '+(d.message||'Erreur.'));
  })
  .catch(()=>{
    document.getElementById('btn-txt').classList.remove('d-none');
    document.getElementById('btn-spin').classList.add('d-none');
    btn.disabled=false;
    alert('❌ Erreur réseau.');
  });
}

/* ── FILTRES ── */
function filterCat(cat,btn){
  /* reset pills */
  document.querySelectorAll('.c-pill').forEach(b=>b.classList.remove('on'));
  if(btn)btn.classList.add('on');

  /* reset search */
  document.getElementById('s-input').value='';
  document.getElementById('s-clear').style.display='none';

  /* show/hide sections + cards */
  document.querySelectorAll('[data-sec]').forEach(sec=>{
    if(cat==='all'||sec.dataset.sec===String(cat)){
      sec.style.display='';
      /* all cards visible */
      sec.querySelectorAll('.prod').forEach(c=>c.style.display='');
    }else{
      sec.style.display='none';
    }
  });

  document.getElementById('no-res').classList.add('d-none');

  if(cat!=='all'){
    const t=document.getElementById('sec-'+cat);
    if(t) window.scrollTo({top:t.getBoundingClientRect().top+window.scrollY-160,behavior:'smooth'});
  }else{
    window.scrollTo({top:0,behavior:'smooth'});
  }
}

function doSearch(q){
  const v=q.toLowerCase().trim();
  document.getElementById('s-clear').style.display=v?'':'none';

  /* reset cat pills to none */
  document.querySelectorAll('.c-pill').forEach(b=>b.classList.remove('on'));
  if(!v){
    /* empty search → show all + activate "Tout" pill */
    document.querySelector('.c-pill').classList.add('on');
    document.querySelectorAll('[data-sec]').forEach(sec=>{
      sec.style.display='';
      sec.querySelectorAll('.prod').forEach(c=>c.style.display='');
    });
    document.getElementById('no-res').classList.add('d-none');
    return;
  }

  let found=0;
  document.querySelectorAll('[data-sec]').forEach(sec=>{
    let visible=0;
    sec.querySelectorAll('.prod').forEach(c=>{
      const match=c.dataset.nom.includes(v);
      c.style.display=match?'':'none';
      if(match)visible++;
    });
    sec.style.display=visible?'':'none';
    found+=visible;
  });
  document.getElementById('no-res').classList.toggle('d-none',found>0);
}

function clearSearch(){
  document.getElementById('s-input').value='';
  doSearch('');
  document.getElementById('s-input').focus();
}

/* ── SWIPE DOWN ── */
let _ty=0;
const _sh=document.getElementById('sheet');
_sh.addEventListener('touchstart',e=>{_ty=e.touches[0].clientY;},{passive:true});
_sh.addEventListener('touchend',e=>{if(e.changedTouches[0].clientY-_ty>80)closeSheet();},{passive:true});
</script>
</body>
</html>