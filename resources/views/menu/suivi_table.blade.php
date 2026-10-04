<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Suivi — {{ $restaurant['nom'] }}</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{
  --bg:    #F7F3EB;
  --white: #FFFFFF;
  --border:#E9E1D5;
  --ink:   #111110;
  --ink2:  #6B6B69;
  --ink3:  #AEAEAC;
  --pop:   #9A593A;
  --pop-lt:rgba(154,89,58,.09);
  --green: #16A34A;
  --green-lt:#F0FDF4;
  --amber: #D97706;
  --amber-lt:#FFFBEB;
  --red:   #DC2626;
  --sans:  'Inter', system-ui, sans-serif;
  --head:  'Syne',  system-ui, sans-serif;
  --r: 16px; --r-sm: 10px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:radial-gradient(ellipse at 5% 0%,rgba(204,155,99,.13),transparent 34%),radial-gradient(ellipse at 95% 45%,rgba(154,89,58,.055),transparent 32%),var(--bg);color:var(--ink);-webkit-font-smoothing:antialiased;padding-bottom:40px;overflow-x:hidden}

/* ── TICKER ── */
.ticker{background:var(--ink);overflow:hidden;white-space:nowrap;padding:8px 0}
.ticker-inner{display:inline-block;animation:tick 38s linear infinite;white-space:nowrap}
.ticker-inner span{font-size:11.5px;color:rgba(255,255,255,.55);padding:0 22px;letter-spacing:.02em}
.ticker-inner span b{color:#fff;font-weight:600}
.ticker-inner .sep{color:rgba(255,255,255,.2);padding:0 4px}
@keyframes tick{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── HEADER ── */
.hdr{background:var(--white);border-bottom:1px solid var(--border);padding:14px 18px 0}
.hdr-top{display:flex;align-items:center;justify-content:space-between;padding-bottom:13px}
.brand{display:flex;align-items:center;gap:10px}
.brand-ico{width:36px;height:36px;border-radius:10px;background:var(--ink);display:flex;align-items:center;justify-content:center;font-family:var(--head);font-size:16px;font-weight:800;color:#fff;flex-shrink:0}
.brand-name{font-family:var(--head);font-size:17px;font-weight:700;color:var(--ink)}
.brand-sub{font-size:11px;color:var(--ink3);margin-top:1px}
.tbl-chip{display:flex;align-items:center;gap:5px;border:1px solid var(--border);border-radius:30px;padding:6px 12px;font-size:12px;font-weight:500;color:var(--ink2);background:var(--bg)}

/* ── LIVE BAR ── */
.live-bar{
  display:flex;align-items:center;gap:8px;
  padding:9px 18px;
  background:var(--bg);
  border-bottom:1px solid var(--border);
  font-size:12px;color:var(--ink3);
}
.live-dot{
  width:8px;height:8px;border-radius:50%;
  background:var(--green);flex-shrink:0;
  animation:pulse-dot 2s infinite;
}
@keyframes pulse-dot{
  0%,100%{box-shadow:0 0 0 0 rgba(22,163,74,.5)}
  50%    {box-shadow:0 0 0 5px rgba(22,163,74,0)}
}
.live-time{margin-left:auto;font-size:11px;color:var(--ink3)}

/* ── PAGE ── */
.page{padding:18px 18px 0;max-width:560px;margin:0 auto}

/* ── GAME CTA ── */
.game-cta{
  background:var(--white);border:1.5px solid var(--border);
  border-left:3px solid #F59E0B;border-radius:var(--r);
  padding:14px 16px;margin-bottom:16px;
  display:flex;align-items:center;gap:12px;
  box-shadow:0 1px 6px rgba(0,0,0,.04);cursor:pointer;
  transition:transform .12s;-webkit-appearance:none;
}
.game-cta:active{transform:scale(.98)}
.game-cta-ico{font-size:24px;flex-shrink:0}
.game-cta-txt{flex:1;min-width:0}
.game-cta-ttl{font-size:14px;font-weight:700;color:var(--ink);font-family:var(--head)}
.game-cta-sub{font-size:12px;color:var(--ink3);margin-top:1px}

/* ── CMD CARD ── */
.cmd-card{
  background:var(--white);border:1.5px solid var(--border);
  border-radius:var(--r);margin-bottom:16px;
  overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04);
}

/* Card header */
.cmd-hdr{
  padding:15px 17px 13px;
  display:flex;align-items:flex-start;justify-content:space-between;
  border-bottom:1px solid var(--border);
}
.cmd-num{font-family:var(--head);font-size:16px;font-weight:700;color:var(--ink);letter-spacing:.02em}
.cmd-time{font-size:11px;color:var(--ink3);margin-top:3px}
.cmd-total{font-family:var(--head);font-size:17px;font-weight:700;color:var(--ink);transition:color .3s,transform .3s}

/* Status badge */
.s-badge{
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 11px;border-radius:20px;
  font-size:11.5px;font-weight:600;
}
.s-attente{background:var(--amber-lt);color:var(--amber)}
.s-cuisson{background:#EFF6FF;color:var(--pop)}
.s-prete  {background:var(--green-lt);color:var(--green)}
.s-servie {background:#F0F9FF;color:#0369A1}
.s-payee  {background:#F3F4F6;color:#6B7280}

/* Timeline */
.tl-wrap{padding:16px 17px}
.tl{list-style:none;padding:0;margin:0;display:flex;gap:0}
.tl-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative}
.tl-step:not(:last-child)::after{
  content:'';position:absolute;
  top:14px;left:calc(50% + 14px);
  width:calc(100% - 28px);height:2px;
  background:var(--border);z-index:0;
  transition:background .4s;
}
.tl-step.done:not(:last-child)::after{background:var(--green)}
.tl-ic{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:12px;z-index:1;position:relative;
  transition:all .3s;
}
.tl-ic.done  {background:var(--green);color:#fff}
.tl-ic.active{background:var(--pop);color:#fff;animation:ring 1.5s infinite}
.tl-ic.wait  {background:var(--border);color:var(--ink3)}
@keyframes ring{
  0%,100%{box-shadow:0 0 0 0 rgba(43,108,176,.4)}
  50%    {box-shadow:0 0 0 7px rgba(43,108,176,0)}
}
.tl-lbl{font-size:10px;color:var(--ink3);margin-top:5px;text-align:center;line-height:1.3;font-weight:500}
.tl-lbl.active{color:var(--pop);font-weight:600}
.tl-lbl.done  {color:var(--green)}

/* Items */
.cmd-items{padding:0 17px 4px}
.item-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;
}
.item-row:last-child{border-bottom:none}
.item-qty{
  background:var(--ink);color:#fff;
  border-radius:5px;padding:2px 7px;
  font-size:10.5px;font-weight:700;margin-right:8px;flex-shrink:0;
}
.item-px{color:var(--ink2);font-weight:600;flex-shrink:0;font-size:12.5px}
.item-lock{font-size:10px;color:var(--ink3);margin-left:5px}

/* Servi banner */
.servi-banner{
  padding:14px 17px;
  background:var(--green-lt);
  border-top:1px solid #BBF7D0;
  display:flex;align-items:center;gap:12px;
}
.servi-ico{font-size:26px;flex-shrink:0}
.servi-ttl{font-size:14px;font-weight:700;color:var(--green)}
.servi-sub{font-size:12px;color:#22C55E;margin-top:2px}

/* Actions */
.cmd-actions{padding:0 17px 16px;display:flex;gap:8px}
.btn-mod{
  flex:1;padding:10px 14px;
  background:var(--bg);border:1.5px solid var(--border);
  border-radius:var(--r-sm);font-size:13px;font-weight:600;
  color:var(--ink);cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:6px;
  transition:border-color .14s,color .14s;
  font-family:var(--sans);text-decoration:none;
}
.btn-mod:hover{border-color:var(--pop);color:var(--pop)}
.btn-mod:active{transform:scale(.97)}
.btn-mod.locked{color:var(--ink3);border-color:var(--border);cursor:not-allowed;opacity:.55}

.btn-menu{
  flex:1;padding:10px 14px;
  background:var(--ink);border:1.5px solid var(--ink);
  border-radius:var(--r-sm);font-size:13px;font-weight:600;
  color:#fff;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:6px;
  font-family:var(--sans);text-decoration:none;
}
.btn-menu:hover{opacity:.9}
.btn-menu:active{transform:scale(.97)}
.btn-menu.locked{background:var(--ink3);border-color:var(--ink3);cursor:not-allowed;opacity:.55}

/* ── EMPTY ── */
.empty-all{text-align:center;padding:64px 20px}
.empty-all .e-ico{font-size:56px;margin-bottom:16px}
.empty-all h3{font-family:var(--head);font-size:22px;font-weight:700;color:var(--ink);margin-bottom:8px}
.empty-all p{font-size:14px;color:var(--ink2);margin-bottom:24px;line-height:1.6}
.btn-back{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--ink);color:#fff;border:none;border-radius:var(--r-sm);
  padding:14px 28px;font-size:14px;font-weight:700;
  cursor:pointer;font-family:var(--head);text-decoration:none;
}

/* ── BACKDROP ── */
.backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:400;opacity:0;pointer-events:none;transition:opacity .22s}
.backdrop.on{opacity:1;pointer-events:all}

/* ── MODAL MODIF ── */
.mod-sheet{
  position:fixed;bottom:0;left:0;right:0;z-index:500;
  background:var(--white);border-radius:22px 22px 0 0;
  max-height:94vh;overflow:hidden;
  display:flex;flex-direction:column;
  transform:translateY(100%);
  transition:transform .36s cubic-bezier(.34,1.2,.64,1);
  border-top:1px solid var(--border);
  box-shadow:0 -4px 32px rgba(0,0,0,.1);
}
.mod-sheet.on{transform:translateY(0)}
.mod-drag{text-align:center;padding:12px 0 5px;flex-shrink:0}
.mod-drag span{display:inline-block;width:38px;height:4px;background:var(--border);border-radius:2px}
.mod-hdr{display:flex;align-items:center;justify-content:space-between;padding:5px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0}
.mod-ttl{font-family:var(--head);font-size:18px;font-weight:700;color:var(--ink)}
.mod-x{width:31px;height:31px;border-radius:50%;background:var(--bg);border:none;display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--ink2);cursor:pointer}

/* Tabs */
.mod-tabs{display:flex;gap:5px;padding:12px 20px 0;flex-shrink:0}
.m-tab{
  flex:1;padding:9px 10px;border-radius:var(--r-sm);
  border:1.5px solid var(--border);background:var(--bg);
  font-size:12.5px;font-weight:600;color:var(--ink2);
  cursor:pointer;font-family:var(--sans);
  display:flex;align-items:center;justify-content:center;gap:6px;
  transition:all .14s;
}
.m-tab.on{background:var(--ink);border-color:var(--ink);color:#fff}
.m-tab-badge{
  background:var(--pop);color:#fff;
  width:18px;height:18px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:9.5px;font-weight:800;
}
.m-tab.on .m-tab-badge{background:rgba(255,255,255,.25)}

.mod-body{flex:1;overflow-y:auto;padding:12px 20px 0}
.mod-body::-webkit-scrollbar{width:2px}
.mod-body::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px}

/* Panier items */
.m-item{display:flex;align-items:center;gap:11px;padding:12px 0;border-bottom:1px solid var(--border)}
.m-item:last-child{border-bottom:none}
.m-nom{font-size:13.5px;font-weight:600;color:var(--ink);font-family:var(--head)}
.m-px{font-size:12px;color:var(--ink2);margin-top:2px}
.m-qty{display:flex;align-items:center;gap:8px;flex-shrink:0}
.m-btn{width:30px;height:30px;border-radius:50%;border:1.5px solid var(--border);background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:17px;cursor:pointer;color:var(--ink);touch-action:manipulation}
.m-btn:active{transform:scale(.82)}
.m-btn.up{background:var(--ink);color:#fff;border-color:var(--ink)}
.m-n{font-size:15px;font-weight:700;min-width:18px;text-align:center;font-family:var(--head)}
.m-locked-hdr{font-size:11px;color:var(--ink3);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin:4px 0 6px}
.m-locked-hdr.first{margin-top:0}

/* Catalogue */
.cat-search{
  display:flex;align-items:center;gap:8px;
  background:var(--bg);border:1.5px solid var(--border);
  border-radius:10px;padding:9px 13px;margin-bottom:10px;
}
.cat-search input{background:transparent;border:none;outline:none;color:var(--ink);font-size:13.5px;flex:1;font-family:var(--sans)}
.cat-search input::placeholder{color:var(--ink3)}
.cat-pills{display:flex;gap:6px;overflow-x:auto;padding-bottom:8px;margin-bottom:6px}
.cat-pills::-webkit-scrollbar{display:none}
.c-cat{padding:6px 13px;border-radius:30px;border:1.5px solid var(--border);background:var(--white);color:var(--ink2);font-size:12px;font-weight:500;cursor:pointer;white-space:nowrap;font-family:var(--sans);transition:all .13s}
.c-cat.on{background:var(--ink);border-color:var(--ink);color:#fff}
.cat-item{display:flex;align-items:center;gap:11px;padding:10px 0;border-bottom:1px solid var(--border)}
.cat-item:last-child{border-bottom:none}
.cat-item img{width:50px;height:50px;border-radius:10px;object-fit:cover;background:var(--bg);flex-shrink:0}
.cat-nom{font-size:13px;font-weight:600;color:var(--ink);line-height:1.3}
.cat-px{font-size:12.5px;font-weight:700;color:var(--ink2);margin-top:2px;font-family:var(--head)}
.add-btn{width:34px;height:34px;border-radius:50%;background:var(--ink);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;touch-action:manipulation;flex-shrink:0}
.add-btn:active{transform:scale(.82)}
.qty-inline{display:flex;align-items:center;gap:6px}
.qi-btn{width:28px;height:28px;border-radius:50%;border:1.5px solid var(--border);background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;color:var(--ink);touch-action:manipulation}
.qi-btn:active{transform:scale(.82)}
.qi-btn.up{background:var(--pop);color:#fff;border-color:var(--pop)}
.qi-n{font-size:14px;font-weight:700;min-width:17px;text-align:center;font-family:var(--head)}

/* Footer modal */
.mod-footer{flex-shrink:0;padding:12px 20px 28px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px}
.mod-total-wrap{flex:1}
.mod-total-lbl{font-size:10.5px;color:var(--ink3);text-transform:uppercase;letter-spacing:.07em;font-weight:600}
.mod-total-val{font-family:var(--head);font-size:20px;font-weight:700;color:var(--ink)}
.btn-save{
  background:var(--ink);color:#fff;border:none;border-radius:var(--r-sm);
  padding:12px 22px;font-size:14px;font-weight:700;
  cursor:pointer;font-family:var(--head);letter-spacing:.01em;
  display:flex;align-items:center;gap:7px;touch-action:manipulation;
  white-space:nowrap;
}
.btn-save:disabled{opacity:.45;cursor:not-allowed}

/* Misc */
.spinner{width:15px;height:15px;border:2px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.d-none{display:none!important}
.empty{text-align:center;padding:32px 20px;color:var(--ink3)}
.empty i{font-size:36px;display:block;margin-bottom:8px;opacity:.2}
.empty p{font-size:13px;line-height:1.7}

/* Toast */
.toast{
  position:fixed;bottom:24px;left:50%;
  transform:translateX(-50%) translateY(20px);
  background:var(--ink);color:#fff;border-radius:12px;
  padding:12px 20px;font-size:13.5px;font-weight:500;
  z-index:9999;opacity:0;transition:all .28s;
  max-width:300px;text-align:center;
  box-shadow:0 8px 28px rgba(0,0,0,.22);
  pointer-events:none;white-space:nowrap;
}
.toast.on{opacity:1;transform:translateX(-50%) translateY(0)}

/* ── MINI-JEUX : HUB ── */
.game-ov{
  position:fixed;inset:0;z-index:700;
  background:var(--bg);
  display:flex;flex-direction:column;
  opacity:0;pointer-events:none;transform:translateY(12px);
  transition:opacity .22s,transform .22s;
}
.game-ov.on{opacity:1;pointer-events:all;transform:translateY(0)}
.game-top{
  display:flex;align-items:center;gap:10px;
  padding:14px 16px;background:var(--white);border-bottom:1px solid var(--border);flex-shrink:0;
}
.game-back{
  width:31px;height:31px;border-radius:50%;background:var(--bg);border:none;
  display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--ink2);cursor:pointer;
  flex-shrink:0;touch-action:manipulation;
}
.game-title{font-family:var(--head);font-size:15px;font-weight:700;color:var(--ink);flex:1;min-width:0}
.game-stats{font-size:13px;color:var(--ink2);white-space:nowrap;flex-shrink:0}
.game-stats b{color:var(--ink);font-family:var(--head)}
.game-close{
  width:31px;height:31px;border-radius:50%;background:var(--bg);border:none;
  display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--ink2);cursor:pointer;
  touch-action:manipulation;flex-shrink:0;
}
.game-canvas-wrap{flex:1;position:relative;overflow:hidden}
.game-canvas-wrap canvas{width:100%;height:100%;display:block;touch-action:none}

/* Menu de sélection */
.game-menu{
  position:absolute;inset:0;overflow-y:auto;
  display:flex;flex-direction:column;gap:12px;
  padding:22px 18px;justify-content:center;
}
.gm-card{
  background:var(--white);border:1.5px solid var(--border);border-radius:var(--r);
  padding:16px;display:flex;align-items:center;gap:14px;cursor:pointer;
  box-shadow:0 1px 6px rgba(0,0,0,.04);transition:transform .12s;
}
.gm-card:active{transform:scale(.97)}
.gm-ico{font-size:30px;flex-shrink:0}
.gm-txt{flex:1;min-width:0}
.gm-ttl{font-family:var(--head);font-size:15px;font-weight:700;color:var(--ink)}
.gm-sub{font-size:12px;color:var(--ink3);margin-top:2px;line-height:1.4}

.g-panel{position:absolute;inset:0}

.game-over,.game-start{
  position:absolute;inset:0;background:rgba(244,244,242,.97);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:6px;padding:30px;text-align:center;
}
.go-emoji{font-size:50px;margin-bottom:6px}
.go-ttl{font-family:var(--head);font-size:20px;font-weight:700;color:var(--ink)}
.go-sub{font-size:13px;color:var(--ink3);max-width:260px;line-height:1.6;margin-bottom:8px}
.go-score{font-size:15px;color:var(--ink2);margin-bottom:10px;font-weight:600}
.go-btn{
  width:100%;max-width:220px;padding:14px;background:var(--ink);color:#fff;
  border:none;border-radius:var(--r-sm);font-size:14px;font-weight:700;
  cursor:pointer;font-family:var(--head);
  display:flex;align-items:center;justify-content:center;gap:8px;
  touch-action:manipulation;margin-top:6px;
}
.go-btn:active{opacity:.88}
.go-ghost{width:100%;max-width:220px;padding:10px;background:none;border:none;font-size:13px;color:var(--ink3);cursor:pointer;font-family:var(--sans);margin-top:2px}

/* Jeu Mémoire */
.memory-wrap{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:16px}
.memory-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;width:100%;max-width:360px}
.mem-card{
  aspect-ratio:1;border-radius:10px;background:var(--ink);
  display:flex;align-items:center;justify-content:center;font-size:24px;
  cursor:pointer;transition:background .2s,opacity .2s;user-select:none;
}
.mem-card .mem-face{display:none}
.mem-card.flipped{background:var(--white);border:1.5px solid var(--border)}
.mem-card.flipped .mem-face{display:block}
.mem-card.matched{background:var(--green-lt);border:1.5px solid #BBF7D0;opacity:.6;cursor:default}

/* Jeu Scrabble (anagramme) */
.scrabble-wrap{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:16px;gap:20px}
.scr-timer-wrap{width:100%;max-width:300px;height:6px;background:var(--border);border-radius:3px;overflow:hidden}
.scr-timer{height:100%;background:var(--pop);width:100%}
.scr-hint{font-size:11.5px;color:var(--ink3);text-align:center;font-weight:500;letter-spacing:.02em;text-transform:uppercase}
.scr-slots{display:flex;gap:6px;flex-wrap:wrap;justify-content:center;max-width:320px}
.scr-slot{
  width:36px;height:40px;border-radius:9px;border:1.5px dashed var(--border);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--head);font-size:18px;font-weight:700;color:var(--ink);
  background:var(--white);cursor:pointer;touch-action:manipulation;transition:all .12s;
}
.scr-slot.filled{border-style:solid;border-color:var(--pop);background:var(--pop-lt)}
.scr-slot.shake{animation:scrShake .35s}
@keyframes scrShake{
  0%,100%{transform:translateX(0)}
  20%{transform:translateX(-6px)}
  40%{transform:translateX(6px)}
  60%{transform:translateX(-4px)}
  80%{transform:translateX(4px)}
}
.scr-tiles{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;max-width:320px}
.scr-tile{
  width:40px;height:44px;border-radius:9px;background:var(--ink);color:#fff;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--head);font-size:18px;font-weight:700;cursor:pointer;
  touch-action:manipulation;transition:transform .1s,opacity .15s,visibility .15s;
  box-shadow:0 2px 6px rgba(0,0,0,.12);
}
.scr-tile:active{transform:scale(.9)}
.scr-tile.used{opacity:0;visibility:hidden;pointer-events:none}
.scr-actions{display:flex;gap:10px}
.scr-btn{
  padding:9px 18px;border-radius:20px;border:1.5px solid var(--border);
  background:var(--white);font-size:12.5px;font-weight:600;color:var(--ink2);
  cursor:pointer;font-family:var(--sans);touch-action:manipulation;
}
.scr-btn:active{transform:scale(.96)}
</style>
</head>
<body>

{{-- ── TICKER ── --}}
<div class="ticker" aria-hidden="true">
  <div class="ticker-inner">

    <span> Votre commande est visible en temps réel par la cuisine</span><span class="sep">·</span>
    <span> Vous pouvez modifier votre commande tant qu'elle est en attente</span><span class="sep">·</span>
    <span> Gardez cette page ouverte pour recevoir les notifications de statut</span><span class="sep">·</span>
    <span> Votre commande est visible en temps réel par la cuisine</span><span class="sep">·</span>
    <span> Vous pouvez modifier votre commande tant qu'elle est en attente</span><span class="sep">·</span>
    <span> Gardez cette page ouverte pour recevoir les notifications de statut</span><span class="sep">·</span>
  </div>
</div>

{{-- ── HEADER ── --}}
<div class="hdr">
  <div class="hdr-top">
    <div class="brand">
      <div class="brand-ico">{{ strtoupper(substr($restaurant['nom'], 0, 1)) }}</div>
      <div>
        <div class="brand-name">{{ $restaurant['nom'] }}</div>
        <div class="brand-sub">Suivi de commande</div>
      </div>
    </div>
    <div class="tbl-chip">
      <i class="bi bi-grid-2x2" style="font-size:11px"></i>
      Table {{ $table->numero }}
    </div>
  </div>
  <div class="live-bar">
    <div class="live-dot"></div>
    <span></span>
    <span class="live-time" id="live-time">—</span>
  </div>
</div>

{{-- ── PAGE ── --}}
<div class="page" id="page-content">

  @if($commandesActives->count())
  <div class="game-cta" onclick="openGame()" role="button">
    <div class="game-cta-ico">🎮</div>
    <div class="game-cta-txt">
      <div class="game-cta-ttl">Un petit jeu en attendant ?</div>
      <div class="game-cta-sub">3 mini-jeux disponibles pendant la préparation !</div>
    </div>
    <i class="bi bi-chevron-right" style="color:var(--ink3)"></i>
  </div>
  @endif

  @forelse($commandesActives as $cmd)
  @php
    $stMap = [
      'en_attente' => ['label'=>'⏳ En attente',     'cls'=>'s-attente', 'tl'=>0],
      'en_cuisson' => ['label'=>'🔥 En préparation', 'cls'=>'s-cuisson', 'tl'=>1],
      'prete'      => ['label'=>'✅ Prête à servir',  'cls'=>'s-prete',   'tl'=>2],
      'servie'     => ['label'=>'🍽 Servie',          'cls'=>'s-servie',  'tl'=>3],
    ];
    $st   = $stMap[$cmd->statut] ?? ['label'=>$cmd->statut,'cls'=>'','tl'=>-1];
    $tlLabels = ['En attente','En cuisson','Prête','Servie'];
    $tlIcons  = ['bi-clock','bi-fire','bi-check-circle','bi-emoji-smile'];

    // ── Règles de verrouillage ──
    // Modifier/Supprimer : uniquement quand la commande entière est en_attente.
    // Ajouter : uniquement quand la commande est prete (repasse alors en_attente,
    // et seuls les NOUVEAUX articles, créés avec statut='en_attente', sont concernés
    // par le prochain cycle prendre()/prete() côté cuisine).
    // Un article est définitivement verrouillé dès que item->statut !== 'en_attente'.
    $canEdit = $cmd->statut === 'en_attente';
    $canAdd  = $cmd->statut === 'prete';
  @endphp

  <div class="cmd-card" id="card-{{ $cmd->id }}">

    {{-- Header carte --}}
    <div class="cmd-hdr">
      <div>
        <div class="cmd-num">{{ $cmd->numero }}</div>
        <div class="cmd-time">{{ $cmd->created_at->format('H:i') }} · {{ $cmd->created_at->diffForHumans() }}</div>
        <div style="margin-top:8px">
          <span class="s-badge {{ $st['cls'] }}" id="badge-{{ $cmd->id }}">{{ $st['label'] }}</span>
        </div>
      </div>
      <div class="cmd-total" id="total-{{ $cmd->id }}">
        {{ number_format($cmd->total, 0, ',', ' ') }} {{ $restaurant['monnaie'] }}
      </div>
    </div>

    {{-- Timeline --}}
    <div class="tl-wrap">
      <ul class="tl">
        @foreach($tlLabels as $i => $lbl)
        @php
          $state = $i < $st['tl'] ? 'done' : ($i === $st['tl'] ? 'active' : 'wait');
          $icon  = $i < $st['tl'] ? 'bi-check' : $tlIcons[$i];
        @endphp
        <li class="tl-step {{ $state }}" id="tls-{{ $cmd->id }}-{{ $i }}">
          <div class="tl-ic {{ $state }}"><i class="bi {{ $icon }}"></i></div>
          <div class="tl-lbl {{ $state }}">{{ $lbl }}</div>
        </li>
        @endforeach
      </ul>
    </div>

    {{-- Articles --}}
    <div class="cmd-items" id="items-{{ $cmd->id }}">
      @foreach($cmd->items as $item)
      @php
        $qFloat = (float) $item->quantite;
        $qDisp  = number_format($qFloat, ($qFloat == (int)$qFloat ? 0 : 1), ',', '');
        $itemVerrouille = ($item->statut ?? 'en_attente') !== 'en_attente';
      @endphp
      <div class="item-row">
        <div style="display:flex;align-items:center;flex:1;min-width:0">
          <span class="item-qty">{{ $qDisp }}×</span>
          <span style="font-size:13px;color:var(--ink);flex:1">
            {{ $item->produit->nom ?? $item->nom }}
            @if($itemVerrouille)
              <i class="bi bi-lock-fill item-lock" title="Déjà en préparation"></i>
            @endif
          </span>
        </div>
        <span class="item-px">{{ number_format($item->sous_total, 0, ',', ' ') }} {{ $restaurant['monnaie'] }}</span>
      </div>
      @endforeach
    </div>

    {{-- Servi banner (caché par défaut) --}}
    <div class="servi-banner" id="servi-{{ $cmd->id }}" style="{{ $cmd->statut === 'servie' ? '' : 'display:none' }}">
      <div class="servi-ico">🎉</div>
      <div>
        <div class="servi-ttl">Bon appétit !</div>
        <div class="servi-sub">Votre commande a été servie</div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="cmd-actions" id="actions-{{ $cmd->id }}" style="{{ $cmd->statut === 'servie' ? 'display:none' : '' }}">
      @if($cmd->statut === 'servie')
        {{-- rien --}}

      @elseif($cmd->statut === 'en_cuisson')
      {{-- Commande en préparation : totalement verrouillée --}}
      <button class="btn-mod locked" type="button"
        onclick="toast('⚠️ Commande en préparation, modification impossible.','amber')">
        <i class="bi bi-lock" style="font-size:12px"></i> Modification impossible
      </button>
      <button class="btn-menu locked" type="button"
        onclick="toast('⚠️ Ajout disponible une fois la commande prête.','amber')">
        <i class="bi bi-lock" style="font-size:13px"></i> Ajout verrouillé
      </button>

      @elseif($canEdit)
      {{-- en_attente : modifier/supprimer/ajouter dans le même panier --}}
      <button class="btn-mod" type="button"
        data-numero="{{ $cmd->numero }}"
        data-modifiable="1"
        data-items="{{ json_encode($cmd->items->map(fn($i)=>[
            'produit_id'=>$i->produit_id,
            'nom'=>$i->produit->nom ?? $i->nom,
            'prix_unitaire'=>$i->prix_unitaire,
            'quantite'=>$i->quantite,
        ])) }}"
        onclick="openModif(this)">
        <i class="bi bi-pencil" style="font-size:12px"></i> Modifier
      </button>
      <button class="btn-menu locked" type="button"
        onclick="toast('⚠️ Ajout disponible une fois la commande prête.','amber')">
        <i class="bi bi-lock" style="font-size:13px"></i> Ajout verrouillé
      </button>

      @elseif($canAdd)
      {{-- prete : les articles déjà préparés sont verrouillés pour toujours,
           seul un nouvel ajout est possible --}}
      <button class="btn-mod locked" type="button"
        onclick="toast('⚠️ Articles déjà préparés, non modifiables.','amber')">
        <i class="bi bi-lock" style="font-size:12px"></i> Modification impossible
      </button>
      <button class="btn-menu" type="button"
        data-numero="{{ $cmd->numero }}"
        onclick="openAjout(this)">
        <i class="bi bi-plus" style="font-size:13px"></i> Ajouter
      </button>
      @endif
    </div>

  </div>
  @empty
  <div class="empty-all">
    <div class="e-ico">✅</div>
    <h3>Aucune commande active</h3>
    <p>Toutes vos commandes ont été servies.<br>Merci de votre visite !</p>
    <a href="{{ route('menu.index', $table->uuid) }}" class="btn-back">
      <i class="bi bi-arrow-left"></i> Retour au menu
    </a>
  </div>
  @endforelse
</div>

{{-- ── BACKDROP ── --}}
<div class="backdrop" id="backdrop" onclick="closeModif()"></div>

{{-- ── MODAL MODIFICATION ── --}}
<div class="mod-sheet" id="mod-sheet">
  <div class="mod-drag"><span></span></div>
  <div class="mod-hdr">
    <div class="mod-ttl" id="mod-ttl">Modifier la commande</div>
    <button class="mod-x" onclick="closeModif()" type="button">✕</button>
  </div>

  {{-- Tabs --}}
  <div class="mod-tabs">
    <button class="m-tab on" onclick="switchTab('panier',this)" type="button" id="tab-panier-btn">
      🛒 Panier
      <span class="m-tab-badge" id="tab-badge">0</span>
    </button>
    <button class="m-tab" onclick="switchTab('catalogue',this)" type="button" id="tab-cat-btn">
      📋 Ajouter des plats
    </button>
  </div>

  {{-- Tab panier --}}
  <div class="mod-body" id="tab-panier">
    <div id="mod-panier-locked"></div>
    <div id="mod-panier-list"></div>
    <div class="empty d-none" id="mod-panier-empty">
      <i class="bi bi-bag-x"></i>
      <p>Panier vide — ajoutez des plats<br>depuis l'onglet catalogue.</p>
    </div>
  </div>

  {{-- Tab catalogue --}}
  <div class="mod-body d-none" id="tab-catalogue">
    <div class="cat-search">
      <i class="bi bi-search" style="color:var(--ink3);font-size:13px;flex-shrink:0"></i>
      <input type="text" placeholder="Rechercher…" oninput="searchCat(this.value)" id="cat-search-inp">
    </div>
    <div class="cat-pills" id="cat-pills">
      <button class="c-cat on" onclick="filterModCat('all',this)" type="button">Tout</button>
      @foreach($categories as $cat)
      <button class="c-cat" onclick="filterModCat('{{ $cat->id }}',this)" type="button" data-cat="{{ $cat->id }}">
        {{ $cat->nom }}
      </button>
      @endforeach
    </div>
    <div id="cat-prod-list">
      @foreach($categories as $cat)
        @foreach($cat->produits as $p)
        <div class="cat-item" id="catp-{{ $p->id }}" data-cat="{{ $cat->id }}" data-nom="{{ strtolower($p->nom) }}">
          <img src="{{ $p->photo_url }}" alt="{{ $p->nom }}"
               onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=100&q=60'" loading="lazy">
          <div style="flex:1;min-width:0">
            <div class="cat-nom">{{ $p->nom }}</div>
            <div class="cat-px">{{ number_format($p->prix,0,',',' ') }} {{ $restaurant['monnaie'] }}</div>
          </div>
          <div id="catp-ctrl-{{ $p->id }}">
            <button class="add-btn" type="button"
              onclick="catAdd({{ $p->id }},'{{ addslashes($p->nom) }}',{{ $p->prix }})">
              <i class="bi bi-plus" style="font-size:16px"></i>
            </button>
          </div>
        </div>
        @endforeach
      @endforeach
      <div class="empty d-none" id="cat-nores">
        <i class="bi bi-search"></i><p>Aucun plat trouvé</p>
      </div>
    </div>
  </div>

  {{-- Footer --}}
  <div class="mod-footer">
    <div class="mod-total-wrap">
      <div class="mod-total-lbl" id="mod-total-lbl">Total modifié</div>
      <div class="mod-total-val" id="mod-total-val">0 {{ $restaurant['monnaie'] }}</div>
    </div>
    <button class="btn-save" id="btn-save" onclick="saveModif()" type="button">
      <i class="bi bi-check2"></i>
      <span id="save-txt">Enregistrer</span>
      <div class="spinner d-none" id="save-spin"></div>
    </button>
  </div>
</div>

<div class="toast" id="toast-el"></div>

{{-- ── HUB MINI-JEUX (2-3 jeux) ── --}}
<div class="game-ov" id="gameOv">
  <div class="game-top">
    <button class="game-back d-none" id="gameBackBtn" onclick="backToMenu()" type="button">
      <i class="bi bi-arrow-left"></i>
    </button>
    <div class="game-title" id="gameTitle">Choisissez un jeu</div>
    <div class="game-stats" id="gameStats"></div>
    <button class="game-close" onclick="closeGame()" type="button">✕</button>
  </div>

  <div class="game-canvas-wrap">

    {{-- Menu de sélection --}}
    <div class="game-menu" id="gameMenu">
      <div class="gm-card" onclick="selectGame('catch')" role="button">
        <div class="gm-ico">🍽️</div>
        <div class="gm-txt">
          <div class="gm-ttl">Attrape les plats</div>
          <div class="gm-sub">Glissez pour attraper les plats, évitez les bombes</div>
        </div>
        <i class="bi bi-chevron-right" style="color:var(--ink3)"></i>
      </div>
      <div class="gm-card" onclick="selectGame('memory')" role="button">
        <div class="gm-ico">🧠</div>
        <div class="gm-txt">
          <div class="gm-ttl">Mémoire</div>
          <div class="gm-sub">Retrouvez toutes les paires de plats</div>
        </div>
        <i class="bi bi-chevron-right" style="color:var(--ink3)"></i>
      </div>
      <div class="gm-card" onclick="selectGame('scrabble')" role="button">
        <div class="gm-ico">🔤</div>
        <div class="gm-txt">
          <div class="gm-ttl">Scrabble</div>
          <div class="gm-sub">Reformez le mot avant la fin du chrono</div>
        </div>
        <i class="bi bi-chevron-right" style="color:var(--ink3)"></i>
      </div>
    </div>

    {{-- Jeu 1 : Attrape les plats --}}
    <div class="g-panel d-none" id="panel-catch">
      <canvas id="gameCanvas"></canvas>
      <div class="game-start" id="catchStart">
        <div class="go-emoji">🎮</div>
        <div class="go-ttl">Attrape les plats !</div>
        <div class="go-sub">Faites glisser votre doigt pour déplacer l'assiette et attraper les plats qui tombent. Évitez les bombes 💣 !</div>
        <button class="go-btn" onclick="startCatch()" type="button">Jouer</button>
      </div>
      <div class="game-over d-none" id="catchOver">
        <div class="go-emoji">🍽️</div>
        <div class="go-ttl">Partie terminée !</div>
        <div class="go-score">Score final : <span id="gFinalScore">0</span></div>
        <button class="go-btn" onclick="startCatch()" type="button">Rejouer</button>
        <button class="go-ghost" onclick="backToMenu()" type="button">Autres jeux</button>
      </div>
    </div>

    {{-- Jeu 2 : Mémoire --}}
    <div class="g-panel d-none" id="panel-memory">
      <div class="memory-wrap">
        <div class="memory-grid" id="memoryGrid"></div>
      </div>
      <div class="game-start" id="memoryStart">
        <div class="go-emoji">🧠</div>
        <div class="go-ttl">Jeu de mémoire</div>
        <div class="go-sub">Retournez les cartes et retrouvez les 8 paires de plats le plus vite possible !</div>
        <button class="go-btn" onclick="startMemory()" type="button">Jouer</button>
      </div>
      <div class="game-over d-none" id="memoryOver">
        <div class="go-emoji">🏆</div>
        <div class="go-ttl">Bravo, terminé !</div>
        <div class="go-score">En <span id="memFinalMoves">0</span> coups · <span id="memFinalTime">0</span>s</div>
        <button class="go-btn" onclick="startMemory()" type="button">Rejouer</button>
        <button class="go-ghost" onclick="backToMenu()" type="button">Autres jeux</button>
      </div>
    </div>

    {{-- Jeu 3 : Scrabble (anagramme) --}}
    <div class="g-panel d-none" id="panel-scrabble">
      <div class="scrabble-wrap">
        <div class="scr-hint">Reformez le mot</div>
        <div class="scr-timer-wrap"><div class="scr-timer" id="scrBar"></div></div>
        <div class="scr-slots" id="scrSlots"></div>
        <div class="scr-tiles" id="scrTiles"></div>
        <div class="scr-actions">
          <button class="scr-btn" onclick="scrClear()" type="button">
            <i class="bi bi-arrow-counterclockwise"></i> Effacer
          </button>
        </div>
      </div>
      <div class="game-start" id="scrabbleStart">
        <div class="go-emoji">🔤</div>
        <div class="go-ttl">Scrabble</div>
        <div class="go-sub">Des lettres mélangées apparaissent : touchez-les dans le bon ordre pour reformer un mot gourmand avant la fin du chrono. 3 vies !</div>
        <button class="go-btn" onclick="startScrabble()" type="button">Jouer</button>
      </div>
      <div class="game-over d-none" id="scrabbleOver">
        <div class="go-emoji">📝</div>
        <div class="go-ttl">Partie terminée !</div>
        <div class="go-score">Score final : <span id="scrFinalScore">0</span></div>
        <button class="go-btn" onclick="startScrabble()" type="button">Rejouer</button>
        <button class="go-ghost" onclick="backToMenu()" type="button">Autres jeux</button>
      </div>
    </div>

  </div>
</div>

<script>
const TBL  = '{{ $table->uuid }}';
const MON  = '{{ $restaurant["monnaie"] }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const QTY_STEP = 0.5; /* pas de quantité : 0.5, 1, 1.5, 2, 2.5 … */

/* ── HELPERS QUANTITÉS DÉCIMALES ── */
function round05(v){
  return Math.round(v/QTY_STEP)*QTY_STEP;
}
function fmtQty(v){
  v=round05(+v||0);
  const s = Number.isInteger(v) ? String(v) : v.toFixed(1);
  return s.replace('.',',');
}
function fmtMoney(v){
  return Math.round(v).toLocaleString('fr')+' '+MON;
}
function escapeHtml(value){
  return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

/* ══════════════════════════════════════
   POLLING — robuste, sans blocage
══════════════════════════════════════ */
let pollTimer = null;

function startPoll(){ pollTimer = setInterval(poll, 7000); }
function stopPoll() { clearInterval(pollTimer); }

async function poll(){
  try{
    const r    = await fetch('/menu/table/'+TBL+'/statut',{
      headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
      cache:'no-store'
    });
    if(!r.ok) return;
    const data = await r.json();

    /* Heure de mise à jour */
    document.getElementById('live-time').textContent =
      ''+new Date().toLocaleTimeString('fr',{hour:'2-digit',minute:'2-digit'});

    /* Aucune commande active → retour menu */
    if(data.count===0){
      stopPoll();
      window.location.href='/menu/table/'+TBL;
      return;
    }

    data.commandes.forEach(cmd => updateCard(cmd));

  }catch(e){ /* réseau indisponible — on réessaie au prochain tick */ }
}

function updateCard(cmd){
  const card = document.getElementById('card-'+cmd.id);
  if(!card) return;

  const stMap={
    en_attente:{label:'⏳ En attente',    cls:'s-attente',tl:0},
    en_cuisson:{label:'🔥 En préparation',cls:'s-cuisson',tl:1},
    prete:     {label:'✅ Prête à servir', cls:'s-prete',  tl:2},
    servie:    {label:'🍽 Servie',         cls:'s-servie', tl:3},
  };
  const st = stMap[cmd.statut] ?? {label:cmd.statut,cls:'',tl:-1};
  const tlIcons=['bi-clock','bi-fire','bi-check-circle','bi-emoji-smile'];

  /* Badge statut */
  const badge=document.getElementById('badge-'+cmd.id);
  if(badge){ badge.className='s-badge '+st.cls; badge.textContent=st.label; }

  /* Total */
  const totEl=document.getElementById('total-'+cmd.id);
  if(totEl){
    const nv=fmtMoney(Number(cmd.total));
    if(totEl.textContent.trim()!==nv){
      totEl.textContent=nv;
      totEl.style.color='var(--green)';
      setTimeout(()=>totEl.style.color='var(--ink)',800);
    }
  }

  /* Timeline */
  for(let i=0;i<4;i++){
    const step=document.getElementById('tls-'+cmd.id+'-'+i);
    if(!step) continue;
    const state = i<st.tl?'done': i===st.tl?'active':'wait';
    const icon  = i<st.tl?'bi-check':tlIcons[i];
    step.className='tl-step '+state;
    const ic=step.querySelector('.tl-ic');
    const lb=step.querySelector('.tl-lbl');
    if(ic){ ic.className='tl-ic '+state; ic.innerHTML='<i class="bi '+icon+'"></i>'; }
    if(lb){ lb.className='tl-lbl '+state; }
  }

  /* Articles — avec cadenas sur les lignes déjà en préparation
     (item.statut !== 'en_attente', ou item.verrouille si le backend l'expose) */
  if(cmd.items && cmd.items.length){
    const wrap=document.getElementById('items-'+cmd.id);
    if(wrap) wrap.innerHTML=cmd.items.map(it=>{
      const verrouille = (typeof it.verrouille!=='undefined')
        ? !!it.verrouille
        : (it.statut && it.statut!=='en_attente');
      return `
      <div class="item-row">
        <div style="display:flex;align-items:center;flex:1;min-width:0">
          <span class="item-qty">${fmtQty(it.quantite)}×</span>
          <span style="font-size:13px;color:var(--ink);flex:1">${escapeHtml(it.nom)}${verrouille?' <i class="bi bi-lock-fill item-lock"></i>':''}</span>
        </div>
        <span class="item-px">${fmtMoney(Number(it.sous_total))}</span>
      </div>`;
    }).join('');
  }

  /* Servi banner */
  const servi=document.getElementById('servi-'+cmd.id);
  if(servi) servi.style.display = cmd.statut==='servie' ? '' : 'none';

  /* Boutons Modifier / Ajouter — 3 états, cohérents avec le rendu Blade initial :
     - servie      : rien
     - en_cuisson  : tout verrouillé
     - en_attente  : Modifier actif, Ajouter verrouillé
     - prete       : Modifier verrouillé (articles déjà préparés), Ajouter actif */
  const actDiv=document.getElementById('actions-'+cmd.id);
  if(!actDiv) return;

  if(cmd.statut==='servie'){
    actDiv.style.display='none';

  }else if(cmd.statut==='en_cuisson'){
    actDiv.style.display='';
    actDiv.innerHTML=`
      <button class="btn-mod locked" type="button"
        onclick="toast('⚠️ Commande en préparation, modification impossible.','amber')">
        <i class="bi bi-lock" style="font-size:12px"></i> Modification impossible
      </button>
      <button class="btn-menu locked" type="button"
        onclick="toast('⚠️ Ajout disponible une fois la commande prête.','amber')">
        <i class="bi bi-lock" style="font-size:13px"></i> Ajout verrouillé
      </button>`;

  }else if(cmd.statut==='en_attente'){
    actDiv.style.display='';
    const esc=JSON.stringify(cmd.items).replace(/"/g,'&quot;');
    actDiv.innerHTML=`
      <button class="btn-mod" type="button"
        data-numero="${cmd.numero}"
        data-modifiable="1"
        data-items="${esc}"
        onclick="openModif(this)">
        <i class="bi bi-pencil" style="font-size:12px"></i> Modifier
      </button>
      <button class="btn-menu locked" type="button"
        onclick="toast('⚠️ Ajout disponible une fois la commande prête.','amber')">
        <i class="bi bi-lock" style="font-size:13px"></i> Ajout verrouillé
      </button>`;

  }else if(cmd.statut==='prete'){
    actDiv.style.display='';
    actDiv.innerHTML=`
      <button class="btn-mod locked" type="button"
        onclick="toast('⚠️ Articles déjà préparés, non modifiables.','amber')">
        <i class="bi bi-lock" style="font-size:12px"></i> Modification impossible
      </button>
      <button class="btn-menu" type="button"
        data-numero="${cmd.numero}"
        onclick="openAjout(this)">
        <i class="bi bi-plus" style="font-size:13px"></i> Ajouter
      </button>`;
  }
}

/* Démarrage immédiat */
poll();
startPoll();

/* ══════════════════════════════════════
   MODAL MODIFICATION
══════════════════════════════════════ */
let MP={}, MN='', MM='modifier';
let LOCKED_ITEMS=[]; /* articles déjà en préparation, affichés en lecture seule dans "Modifier" */

function openModif(btn){
  const numero = btn.dataset.numero;
  if(btn.dataset.modifiable!=='1'){
    toast('⚠️ Commande non modifiable.','amber'); return;
  }
  let items;
  try{ items=JSON.parse(btn.dataset.items.replace(/&quot;/g,'"')); }
  catch(e){ toast('❌ Erreur de lecture.','red'); return; }

  MN=numero; MM='modifier'; MP={}; LOCKED_ITEMS=[];
  items.forEach(it=>{
    // Sécurité : si jamais un item verrouillé arrive ici (ne devrait pas
    // se produire puisque ce bouton n'existe que quand statut===en_attente),
    // on l'isole en lecture seule plutôt que de l'exposer comme éditable.
    const verrouille = (typeof it.verrouille!=='undefined')
      ? !!it.verrouille
      : (it.statut && it.statut!=='en_attente');
    if(verrouille){
      LOCKED_ITEMS.push(it);
    }else{
      MP[it.produit_id]={id:it.produit_id,nom:it.nom,prix:+it.prix_unitaire,q:round05(+it.quantite)};
    }
  });
  document.getElementById('mod-ttl').textContent='Modifier '+numero;
  document.getElementById('mod-total-lbl').textContent='Total modifié';
  document.getElementById('save-txt').textContent='Enregistrer';
  switchTab('panier', document.getElementById('tab-panier-btn'));
  syncAllCatControls();
  renderLockedPanier();
  renderModPanier();
  updateModTotal();
  updateTabBadge();
  document.getElementById('backdrop').classList.add('on');
  document.getElementById('mod-sheet').classList.add('on');
  document.body.style.overflow='hidden';
}
function closeModif(){
  document.getElementById('backdrop').classList.remove('on');
  document.getElementById('mod-sheet').classList.remove('on');
  document.body.style.overflow='';
  setTimeout(poll,300);
}

/* ── TABS ── */
function openAjout(btn){
  const numero = btn.dataset.numero;
  if(!numero){
    toast('Commande introuvable.','amber'); return;
  }
  MN=numero; MM='ajouter'; MP={}; LOCKED_ITEMS=[];
  document.getElementById('mod-ttl').textContent='Ajouter à '+numero;
  document.getElementById('mod-total-lbl').textContent='Total à ajouter';
  document.getElementById('save-txt').textContent='Ajouter';
  switchTab('catalogue', document.getElementById('tab-cat-btn'));
  syncAllCatControls();
  renderLockedPanier();
  renderModPanier();
  updateModTotal();
  updateTabBadge();
  document.getElementById('backdrop').classList.add('on');
  document.getElementById('mod-sheet').classList.add('on');
  document.body.style.overflow='hidden';
}

function switchTab(tab,btn){
  document.querySelectorAll('.m-tab').forEach(b=>b.classList.remove('on'));
  if(btn) btn.classList.add('on');
  document.getElementById('tab-panier').classList.toggle('d-none', tab!=='panier');
  document.getElementById('tab-catalogue').classList.toggle('d-none', tab!=='catalogue');
}

/* ── RENDER PANIER : bloc "déjà en préparation" (lecture seule) ── */
function renderLockedPanier(){
  const wrap=document.getElementById('mod-panier-locked');
  if(!wrap)return;
  if(!LOCKED_ITEMS.length){ wrap.innerHTML=''; return; }
  wrap.innerHTML = `<div class="m-locked-hdr first">🔒 Déjà en préparation</div>`
    + LOCKED_ITEMS.map(it=>`
      <div class="m-item" style="opacity:.55">
        <div style="flex:1;min-width:0">
          <div class="m-nom">${it.nom}</div>
          <div class="m-px">${fmtMoney((+it.prix_unitaire)*(+it.quantite))}
            <span style="color:var(--ink3);font-size:11px">× ${fmtQty(it.quantite)}</span>
          </div>
        </div>
      </div>`).join('')
    + `<div class="m-locked-hdr">Modifiable</div>`;
}

/* ── RENDER PANIER ── */
function renderModPanier(){
  const ids=Object.keys(MP);
  const list=document.getElementById('mod-panier-list');
  const empty=document.getElementById('mod-panier-empty');
  if(!ids.length){
    list.innerHTML=''; empty.classList.remove('d-none'); return;
  }
  empty.classList.add('d-none');
  list.innerHTML=ids.map(id=>{
    const it=MP[id];
    return`<div class="m-item" id="mi-${id}">
      <div style="flex:1;min-width:0">
        <div class="m-nom">${it.nom}</div>
        <div class="m-px">${fmtMoney(it.prix*it.q)}
          <span style="color:var(--ink3);font-size:11px">× ${fmtQty(it.q)}</span>
        </div>
      </div>
      <div class="m-qty">
        <button class="m-btn" onclick="mRem(${id})" type="button">−</button>
        <span class="m-n" id="mn-${id}">${fmtQty(it.q)}</span>
        <button class="m-btn up" onclick="mAdd(${id})" type="button">+</button>
      </div>
    </div>`;
  }).join('');
  updateModTotal(); updateTabBadge();
}
function mAdd(id){
  if(!MP[id])return;
  MP[id].q=round05(MP[id].q+QTY_STEP);
  const el=document.getElementById('mn-'+id);
  if(el) el.textContent=fmtQty(MP[id].q);
  syncCatControl(id); updateModTotal(); updateTabBadge();
}
function mRem(id){
  if(!MP[id])return;
  MP[id].q=round05(MP[id].q-QTY_STEP);
  if(MP[id].q<=0){
    delete MP[id];
    const el=document.getElementById('mi-'+id);
    if(el){ el.style.transition='opacity .15s';el.style.opacity='0';setTimeout(()=>renderModPanier(),150); }
  }else{
    const el=document.getElementById('mn-'+id); if(el) el.textContent=fmtQty(MP[id].q);
  }
  syncCatControl(id); updateModTotal(); updateTabBadge();
}

/* ── CATALOGUE ── */
function catAdd(id,nom,prix){
  MP[id]=MP[id]||{id,nom,prix:+prix,q:0};
  MP[id].q=round05(MP[id].q+QTY_STEP);
  syncCatControl(id); updateModTotal(); updateTabBadge();
  toast('✓ '+nom+' ajouté','green');
}
function catPlus(id){ if(MP[id]){MP[id].q=round05(MP[id].q+QTY_STEP);const e=document.getElementById('qin-'+id);if(e)e.textContent=fmtQty(MP[id].q);updateModTotal();updateTabBadge();} }
function catMinus(id){
  if(!MP[id])return;
  MP[id].q=round05(MP[id].q-QTY_STEP);
  if(MP[id].q<=0){ delete MP[id]; syncCatControl(id); }
  else{ const e=document.getElementById('qin-'+id);if(e)e.textContent=fmtQty(MP[id].q); }
  updateModTotal(); updateTabBadge();
}
function syncCatControl(id){
  const wrap=document.getElementById('catp-ctrl-'+id);
  if(!wrap)return;
  const q=MP[id]?.q??0;
  if(q>0){
    wrap.innerHTML=`<div class="qty-inline">
      <button class="qi-btn" onclick="catMinus(${id})" type="button">−</button>
      <span class="qi-n" id="qin-${id}">${fmtQty(q)}</span>
      <button class="qi-btn up" onclick="catPlus(${id})" type="button">+</button>
    </div>`;
  }else{
    wrap.innerHTML=`<button class="add-btn" type="button" onclick="catAdd(${id},'${(MP[id]?.nom??'').replace(/'/g,"\\'")}',${MP[id]?.prix??0})">
      <i class="bi bi-plus" style="font-size:16px"></i></button>`;
    /* Récupérer nom/prix depuis le DOM */
    const item=document.getElementById('catp-'+id);
    if(item){
      const btn=wrap.querySelector('button');
      const nomEl=item.querySelector('.cat-nom');
      const pxEl =item.querySelector('.cat-px');
      if(btn&&nomEl&&pxEl){
        const n=nomEl.textContent.trim();
        const p=parseFloat(pxEl.textContent.replace(/[^\d]/g,''));
        btn.onclick=()=>catAdd(id,n,p);
      }
    }
  }
}
function syncAllCatControls(){
  document.querySelectorAll('.cat-item').forEach(el=>{
    const id=parseInt(el.id.replace('catp-',''));
    if(id) syncCatControl(id);
  });
}
function searchCat(q){
  const v=q.toLowerCase().trim();
  let n=0;
  document.querySelectorAll('.cat-item').forEach(el=>{
    const show=!v||el.dataset.nom.includes(v);
    el.style.display=show?'':'none';
    if(show)n++;
  });
  document.getElementById('cat-nores').classList.toggle('d-none',n>0);
}
function filterModCat(cat,btn){
  document.querySelectorAll('.c-cat').forEach(b=>b.classList.remove('on'));
  if(btn)btn.classList.add('on');
  document.querySelectorAll('.cat-item').forEach(el=>{
    el.style.display=(cat==='all'||el.dataset.cat===String(cat))?'':'none';
  });
  const si=document.getElementById('cat-search-inp');
  if(si) si.value='';
  document.getElementById('cat-nores').classList.add('d-none');
}

/* ── HELPERS ── */
function updateModTotal(){
  const tot=Object.values(MP).reduce((s,it)=>s+it.prix*it.q,0);
  document.getElementById('mod-total-val').textContent=fmtMoney(tot);
}
function updateTabBadge(){
  const nb=Object.values(MP).reduce((s,it)=>s+it.q,0);
  document.getElementById('tab-badge').textContent=fmtQty(nb);
}

/* ── SAVE ── */
function saveModif(){
  const editToken=localStorage.getItem('restopro-order-'+MN);
  if(!editToken){ toast('Seul l’appareil à l’origine de la commande peut la modifier.','amber'); return; }
  const ids=Object.keys(MP);
  if(!ids.length){ toast('Panier vide.','amber'); return; }
  const btn=document.getElementById('btn-save');
  document.getElementById('save-txt').classList.add('d-none');
  document.getElementById('save-spin').classList.remove('d-none');
  btn.disabled=true;
  fetch('/menu/commande/'+MN+'/modifier',{
    method:'PUT',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
    body:JSON.stringify({mode:MM,edit_token:editToken,items:ids.map(id=>({produit_id:+id,quantite:MP[id].q,notes:''}))})
  })
  .then(r=>r.json())
  .then(d=>{
    document.getElementById('save-txt').classList.remove('d-none');
    document.getElementById('save-spin').classList.add('d-none');
    btn.disabled=false;
    if(d.success){
      closeModif();
      toast(MM==='ajouter' ? '✅ Complément envoyé en cuisine !' : '✅ Commande modifiée !','green');
      setTimeout(()=>location.reload(),1200);
    }
    else if(d.bloquee){ closeModif(); toast('⚠️ '+d.message,'amber'); }
    else toast('❌ '+d.message,'red');
  })
  .catch(()=>{
    document.getElementById('save-txt').classList.remove('d-none');
    document.getElementById('save-spin').classList.add('d-none');
    btn.disabled=false;
    toast('❌ Erreur réseau.','red');
  });
}

/* ── TOAST ── */
function toast(msg,type='green'){
  const col={green:'var(--green)',amber:'var(--amber)',red:'var(--red)'};
  const el=document.getElementById('toast-el');
  el.textContent=msg;
  el.style.borderBottom='3px solid '+(col[type]||col.green);
  el.classList.add('on');
  clearTimeout(el._t);
  el._t=setTimeout(()=>el.classList.remove('on'),2800);
}

/* ── SWIPE DOWN ── */
let _ty=0;
const _sh=document.getElementById('mod-sheet');
_sh.addEventListener('touchstart',e=>{_ty=e.touches[0].clientY;},{passive:true});
_sh.addEventListener('touchend',e=>{if(e.changedTouches[0].clientY-_ty>80)closeModif();},{passive:true});

/* ══════════════════════════════════════════════
   HUB MINI-JEUX (3 jeux max, isolé du reste)
   ══════════════════════════════════════════════ */
let currentGame=null;
const GOOD_ITEMS=['🍕','🍔','🍟','🍗','🍰','🍩','🥗','🍜'];
const BAD_ITEMS=['💣','🦴'];

function setStats(html){ document.getElementById('gameStats').innerHTML=html; }

/* ── Ouverture / fermeture du hub ── */
function openGame(){
  document.getElementById('gameOv').classList.add('on');
  document.body.style.overflow='hidden';
  backToMenu();
}
function closeGame(){
  document.getElementById('gameOv').classList.remove('on');
  document.body.style.overflow='';
  stopAllGames();
}
function stopAllGames(){
  stopCatchGame();
  stopMemory();
  stopScrabble();
  currentGame=null;
}
function backToMenu(){
  stopAllGames();
  document.getElementById('gameMenu').classList.remove('d-none');
  document.querySelectorAll('.g-panel').forEach(p=>p.classList.add('d-none'));
  document.getElementById('gameBackBtn').classList.add('d-none');
  document.getElementById('gameTitle').textContent='Choisissez un jeu';
  setStats('');
}
function selectGame(game){
  currentGame=game;
  document.getElementById('gameMenu').classList.add('d-none');
  document.querySelectorAll('.g-panel').forEach(p=>p.classList.add('d-none'));
  document.getElementById('gameBackBtn').classList.remove('d-none');
  const titles={catch:'Attrape les plats',memory:'Mémoire',scrabble:'Scrabble'};
  document.getElementById('gameTitle').textContent=titles[game];
  document.getElementById('panel-'+game).classList.remove('d-none');

  if(game==='catch'){
    setupGameCanvas();
    document.getElementById('catchStart').classList.remove('d-none');
    document.getElementById('catchOver').classList.add('d-none');
    setStats('Score : <b>0</b> &nbsp; ❤️❤️❤️');
  }else if(game==='memory'){
    document.getElementById('memoryStart').classList.remove('d-none');
    document.getElementById('memoryOver').classList.add('d-none');
    setStats('');
  }else if(game==='scrabble'){
    document.getElementById('scrabbleStart').classList.remove('d-none');
    document.getElementById('scrabbleOver').classList.add('d-none');
    setStats('');
  }
}

/* ══════════════════
   JEU 1 : ATTRAPE LES PLATS
══════════════════ */
let catchRunning=false, gameCtx, gameCanvas, gW, gH;
let plateX=0, plateW=70, plateH=14;
let gItems=[], gScore=0, gLives=3, gSpeed=2.2, gSpawnTimer=0, gRAF=null;

function setupGameCanvas(){
  gameCanvas=document.getElementById('gameCanvas');
  if(!gameCanvas)return;
  gameCtx=gameCanvas.getContext('2d');
  const wrap=gameCanvas.parentElement;
  const r=wrap.getBoundingClientRect();
  gW=gameCanvas.width=r.width;
  gH=gameCanvas.height=r.height;
  plateX=gW/2-plateW/2;
}
function startCatch(){
  document.getElementById('catchStart').classList.add('d-none');
  document.getElementById('catchOver').classList.add('d-none');
  if(!gameCanvas || gW===0 || gH===0) setupGameCanvas();
  gItems=[];gScore=0;gLives=3;gSpeed=2.2;gSpawnTimer=0;
  updateCatchHud();
  catchRunning=true;
  gameLoop();
}
function stopCatchGame(){
  catchRunning=false;
  if(gRAF)cancelAnimationFrame(gRAF);
}
function endCatch(){
  catchRunning=false;
  if(gRAF)cancelAnimationFrame(gRAF);
  document.getElementById('gFinalScore').textContent=gScore;
  document.getElementById('catchOver').classList.remove('d-none');
}
function updateCatchHud(){
  setStats('Score : <b>'+gScore+'</b> &nbsp; '+'❤️'.repeat(Math.max(gLives,0))+'🖤'.repeat(Math.max(3-gLives,0)));
}
function spawnGameItem(){
  const isBad=Math.random()<0.22;
  const emoji=isBad
    ? BAD_ITEMS[Math.floor(Math.random()*BAD_ITEMS.length)]
    : GOOD_ITEMS[Math.floor(Math.random()*GOOD_ITEMS.length)];
  gItems.push({x:Math.random()*(gW-30)+15,y:-20,e:emoji,bad:isBad,size:26+Math.random()*8});
}
function gameLoop(){
  if(!catchRunning)return;
  gameCtx.clearRect(0,0,gW,gH);

  gSpawnTimer++;
  const spawnRate=Math.max(28,42-Math.floor(gScore/5));
  if(gSpawnTimer>spawnRate){gSpawnTimer=0;spawnGameItem();}

  /* assiette (joueur) */
  gameCtx.fillStyle='#111110';
  gameCtx.fillRect(plateX,gH-30,plateW,plateH);
  gameCtx.fillStyle='#2B6CB0';
  gameCtx.fillRect(plateX,gH-30,plateW,3);

  for(let i=gItems.length-1;i>=0;i--){
    const it=gItems[i];
    it.y+=gSpeed;
    gameCtx.font=it.size+'px sans-serif';
    gameCtx.textAlign='center';
    gameCtx.textBaseline='middle';
    gameCtx.fillText(it.e,it.x,it.y);

    /* collision avec l'assiette */
    if(it.y>gH-38 && it.y<gH-14 && it.x>plateX-10 && it.x<plateX+plateW+10){
      if(it.bad){gLives--;}else{gScore++;}
      updateCatchHud();
      gItems.splice(i,1);
      if(gLives<=0){endCatch();return;}
      continue;
    }
    /* sortie de l'écran */
    if(it.y>gH+20){
      if(!it.bad){
        gLives--;updateCatchHud();
        if(gLives<=0){endCatch();return;}
      }
      gItems.splice(i,1);
    }
  }
  gSpeed=Math.min(6,2.2+gScore*0.04);
  gRAF=requestAnimationFrame(gameLoop);
}
function moveGamePlate(clientX){
  const rect=gameCanvas.getBoundingClientRect();
  let x=clientX-rect.left-plateW/2;
  x=Math.max(0,Math.min(gW-plateW,x));
  plateX=x;
}
document.addEventListener('DOMContentLoaded',()=>{
  const wrap=document.querySelector('.game-canvas-wrap');
  if(!wrap)return;
  wrap.addEventListener('touchmove',e=>{
    if(currentGame!=='catch'||!catchRunning)return;
    moveGamePlate(e.touches[0].clientX);
    e.preventDefault();
  },{passive:false});
  wrap.addEventListener('mousemove',e=>{
    if(currentGame==='catch'&&catchRunning)moveGamePlate(e.clientX);
  });
});
window.addEventListener('resize',()=>{
  if(currentGame==='catch'){
    const ov=document.getElementById('gameOv');
    if(ov && ov.classList.contains('on'))setupGameCanvas();
  }
});

/* ══════════════════
   JEU 2 : MÉMOIRE
══════════════════ */
let memCards=[], memFlipped=[], memMatched=0, memMoves=0, memTime=0, memTimer=null, memLock=false;

function startMemory(){
  document.getElementById('memoryStart').classList.add('d-none');
  document.getElementById('memoryOver').classList.add('d-none');
  clearInterval(memTimer);
  memMoves=0;memMatched=0;memTime=0;memLock=false;memFlipped=[];
  const deck=[...GOOD_ITEMS,...GOOD_ITEMS].sort(()=>Math.random()-.5);
  memCards=deck.map((e,i)=>({id:i,e,flipped:false,matched:false}));
  renderMemory();
  updateMemHud();
  memTimer=setInterval(()=>{memTime++;updateMemHud();},1000);
}
function stopMemory(){ clearInterval(memTimer); }
function renderMemory(){
  const grid=document.getElementById('memoryGrid');
  if(!grid)return;
  grid.innerHTML=memCards.map(c=>`
    <div class="mem-card ${c.flipped?'flipped':''} ${c.matched?'matched':''}" id="mem-${c.id}" onclick="flipMem(${c.id})">
      <span class="mem-face">${c.e}</span>
    </div>`).join('');
}
function flipMem(id){
  if(memLock)return;
  const c=memCards.find(x=>x.id===id);
  if(!c||c.flipped||c.matched)return;
  c.flipped=true;
  renderMemory();
  memFlipped.push(c);
  if(memFlipped.length===2){
    memMoves++; updateMemHud();
    memLock=true;
    const [a,b]=memFlipped;
    if(a.e===b.e){
      a.matched=true;b.matched=true;
      memMatched+=2;
      memFlipped=[];memLock=false;
      renderMemory();
      if(memMatched===memCards.length) endMemory();
    }else{
      setTimeout(()=>{
        a.flipped=false;b.flipped=false;
        memFlipped=[];memLock=false;
        renderMemory();
      },700);
    }
  }
}
function endMemory(){
  clearInterval(memTimer);
  document.getElementById('memFinalMoves').textContent=memMoves;
  document.getElementById('memFinalTime').textContent=memTime;
  document.getElementById('memoryOver').classList.remove('d-none');
}
function updateMemHud(){
  setStats('Coups : <b>'+memMoves+'</b> &nbsp; ⏱ <b>'+memTime+'s</b>');
}

/* ══════════════════
   JEU 3 : SCRABBLE (anagramme)
══════════════════ */
const SCR_WORDS = [
    // Restaurant
    'RESTAURANT', 'BISTROT', 'BRASSERIE', 'CAFE', 'SNACK',
    'FASTFOOD', 'TRAITEUR', 'CUISINE', 'MENU', 'BUFFET',
    'SERVICE', 'TABLE', 'SERVEUR', 'CUISINIER', 'CHEF',
    'RECETTE', 'COMMANDE', 'FACTURE', 'LIVRAISON', 'RESERVATION',

    // Plats
    'PIZZA', 'BURGER', 'SANDWICH', 'TACOS', 'KEBAB',
    'PANINI', 'HOTDOG', 'PATES', 'SPAGHETTI', 'LASAGNES',
    'RIZ', 'COUSCOUS', 'PAELLA', 'RISOTTO', 'OMELETTE',
    'CREPE', 'SOUPE', 'SALADE', 'GRATIN', 'QUICHE',
    'TARTE', 'POTAGE', 'PUREE',

    // Viandes et poissons
    'POULET', 'BOEUF', 'PORC', 'AGNEAU', 'DINDE',
    'POISSON', 'SAUMON', 'THON', 'CREVETTES', 'CALAMAR',

    // Accompagnements
    'FRITES', 'LEGUMES', 'HARICOTS', 'CAROTTES', 'POMMESDETERRE',
    'RIZBLANC', 'SEMOULE', 'MANIOC', 'IGNAME', 'BANANEPLANTAIN',

    // Desserts
    'DESSERT', 'GATEAU', 'GLACE', 'MOUSSE', 'BROWNIE',
    'TIRAMISU', 'FLAN', 'BEIGNET', 'DONUT', 'CROISSANT',
    'YAOURT', 'CHOCOLAT',

    // Fruits
    'POMME', 'BANANE', 'ORANGE', 'ANANAS', 'MANGUE',
    'PASTEQUE', 'FRAISE', 'RAISIN', 'CITRON', 'AVOCAT',

    // Boissons
    'CAFE', 'EXPRESSO', 'CAPPUCCINO', 'LATTE', 'THE',
    'CHOCOLATCHAUD', 'JUS', 'LIMONADE', 'EAU', 'SODA',
    'COLA', 'MOJITO', 'SMOOTHIE', 'MILKSHAKE',

    // Ingrédients
    'FROMAGE', 'TOMATE', 'OIGNON', 'AIL', 'POIVRE',
    'SEL', 'HUILE', 'BEURRE', 'SAUCE', 'EPICES',
    'BASILIC', 'PERSIL', 'OLIVE', 'CHAMPIGNON', 'MOZZARELLA'
];
let scrScore=0, scrLives=3, scrWord='', scrLetters=[], scrSlots=[], scrUsedIdx=new Set(), scrTimeout=null, scrDuration=9000;

function startScrabble(){
  document.getElementById('scrabbleStart').classList.add('d-none');
  document.getElementById('scrabbleOver').classList.add('d-none');
  scrScore=0;scrLives=3;scrDuration=9000;
  updateScrHud();
  nextScrRound();
}
function stopScrabble(){
  clearTimeout(scrTimeout);
}
function nextScrRound(){
  clearTimeout(scrTimeout);
  scrWord=SCR_WORDS[Math.floor(Math.random()*SCR_WORDS.length)];
  const letters=scrWord.split('');
  let shuffled;
  do{
    shuffled=[...letters].sort(()=>Math.random()-.5);
  }while(letters.length>1 && shuffled.join('')===scrWord);
  scrLetters=shuffled;
  scrSlots=new Array(scrWord.length).fill(null);
  scrUsedIdx=new Set();
  renderScrSlots();
  renderScrTiles();

  const bar=document.getElementById('scrBar');
  if(bar){
    bar.style.transition='none'; bar.style.width='100%';
    requestAnimationFrame(()=>{
      bar.style.transition='width '+scrDuration+'ms linear';
      bar.style.width='0%';
    });
  }
  scrTimeout=setTimeout(()=>{ scrMiss(); }, scrDuration);
}
function renderScrSlots(){
  const wrap=document.getElementById('scrSlots');
  if(!wrap)return;
  wrap.innerHTML=scrSlots.map((v,i)=>
    `<div class="scr-slot ${v!==null?'filled':''}" id="scrslot-${i}" onclick="scrRemoveSlot(${i})">${v!==null?scrLetters[v]:''}</div>`
  ).join('');
}
function renderScrTiles(){
  const wrap=document.getElementById('scrTiles');
  if(!wrap)return;
  wrap.innerHTML=scrLetters.map((l,i)=>
    `<div class="scr-tile ${scrUsedIdx.has(i)?'used':''}" onclick="scrTapTile(${i})">${l}</div>`
  ).join('');
}
function scrTapTile(i){
  if(currentGame!=='scrabble')return;
  if(scrUsedIdx.has(i))return;
  const emptyIdx=scrSlots.indexOf(null);
  if(emptyIdx===-1)return;
  scrSlots[emptyIdx]=i;
  scrUsedIdx.add(i);
  renderScrSlots();
  renderScrTiles();
  if(scrSlots.indexOf(null)===-1) checkScrWord();
}
function scrRemoveSlot(i){
  if(currentGame!=='scrabble')return;
  const val=scrSlots[i];
  if(val===null)return;
  scrSlots[i]=null;
  scrUsedIdx.delete(val);
  renderScrSlots();
  renderScrTiles();
}
function scrClear(){
  if(currentGame!=='scrabble')return;
  scrSlots=scrSlots.map(()=>null);
  scrUsedIdx=new Set();
  renderScrSlots();
  renderScrTiles();
}
function checkScrWord(){
  const built=scrSlots.map(i=>scrLetters[i]).join('');
  if(built===scrWord){
    clearTimeout(scrTimeout);
    scrScore++;
    scrDuration=Math.max(4500,scrDuration-300);
    updateScrHud();
    toast('✓ '+scrWord,'green');
    setTimeout(nextScrRound,500);
  }else{
    document.querySelectorAll('.scr-slot').forEach(s=>s.classList.add('shake'));
    setTimeout(()=>{ scrClear(); },350);
  }
}
function scrMiss(){
  clearTimeout(scrTimeout);
  scrLives--;
  updateScrHud();
  toast('⏱ Le mot était : '+scrWord,'amber');
  if(scrLives<=0){ endScrabble(); return; }
  nextScrRound();
}
function endScrabble(){
  document.getElementById('scrFinalScore').textContent=scrScore;
  document.getElementById('scrabbleOver').classList.remove('d-none');
}
function updateScrHud(){
  setStats('Score : <b>'+scrScore+'</b> &nbsp; '+'❤️'.repeat(Math.max(scrLives,0))+'🖤'.repeat(Math.max(3-scrLives,0)));
}
</script>
</body>
</html>
