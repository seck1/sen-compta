<?php
/**
 * Page publique « Fonctionnalités » — vitrine SenCompta.
 * Rendue par SaasController::fonctionnalites(). Aucune donnée sensible.
 */
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fonctionnalités — SenCompta · Le SaaS comptable du Sénégal</title>
<meta name="description" content="SenCompta : comptabilité SYSCOHADA, fiscalité DGID, paie IPRES/CSS, scan IA et gestion commerciale pour les cabinets et PME du Sénégal.">
<link rel="icon" type="image/svg+xml" href="/logo/sencompta-icon.svg">
<link rel="icon" type="image/png" href="/logo/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --green:#1f6e4e; --green-dark:#164d37; --green-light:#2a8a63;
  --navy:#1e3a5f; --navy-deep:#15293f;
  --gold:#b8923f; --gold-light:#d9b876; --gold-soft:#efe3c8;
  --paper:#f7f4ee; --paper-2:#fffdf9; --ink:#1d2620; --muted:#5e6b62;
  --line:rgba(30,58,95,.12);
  --serif:'Fraunces',Georgia,serif;
  --sans:'DM Sans',-apple-system,sans-serif;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:var(--sans); color:var(--ink); background:var(--paper);
  -webkit-font-smoothing:antialiased; line-height:1.5; overflow-x:hidden;
}
/* grain texture overlay */
body::before{
  content:""; position:fixed; inset:0; z-index:1; pointer-events:none; opacity:.035;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}
a{color:inherit;text-decoration:none}
.wrap{max-width:1200px;margin:0 auto;padding:0 28px;position:relative;z-index:2}

/* ===== NAVBAR ===== */
.nav{
  position:sticky;top:0;z-index:50;
  background:rgba(247,244,238,.82); backdrop-filter:blur(14px) saturate(1.4);
  border-bottom:1px solid var(--line);
}
.nav-in{max-width:1200px;margin:0 auto;padding:14px 28px;display:flex;align-items:center;gap:20px}
.brand{display:flex;align-items:center;gap:14px}
.brand img{width:50px;height:50px;border-radius:13px;object-fit:contain;
  background:linear-gradient(160deg,#fff,#f0ebe0);box-shadow:inset 0 0 0 1px rgba(31,110,78,.14)}
.brand b{font-family:var(--serif);font-weight:600;font-size:22px;color:var(--navy);letter-spacing:-.3px}
.brand span{display:block;font-size:10px;letter-spacing:2px;color:var(--gold);font-weight:700;text-transform:uppercase;margin-top:-1px}
.foot-in .brand img{width:46px;height:46px;border-radius:12px}
.nav-links{margin-left:auto;display:flex;align-items:center;gap:8px}
.nav-link{padding:9px 14px;font-size:14.5px;font-weight:600;color:var(--navy);border-radius:9px;transition:.18s}
.nav-link:hover{background:rgba(30,58,95,.06)}
.btn{display:inline-flex;align-items:center;gap:8px;font-family:var(--sans);font-weight:600;
  cursor:pointer;border:none;transition:.2s;white-space:nowrap}
.btn-green{background:var(--green);color:#fff;padding:11px 20px;border-radius:11px;font-size:14.5px;
  box-shadow:0 8px 20px -10px rgba(31,110,78,.7)}
.btn-green:hover{background:var(--green-dark);transform:translateY(-1px);box-shadow:0 14px 28px -12px rgba(31,110,78,.75)}
.btn-ghost{padding:10px 18px;border-radius:11px;font-size:14.5px;color:var(--navy);
  border:1.5px solid var(--line);background:transparent}
.btn-ghost:hover{border-color:var(--green);color:var(--green)}
.nav-burger{display:none}

/* ===== HERO ===== */
.hero{position:relative;padding:84px 0 70px;overflow:hidden}
.hero::after{content:"";position:absolute;top:-180px;right:-160px;width:520px;height:520px;border-radius:50%;
  background:radial-gradient(circle,rgba(31,110,78,.10),transparent 65%);z-index:0}
.hero::before{content:"";position:absolute;bottom:-200px;left:-140px;width:460px;height:460px;border-radius:50%;
  background:radial-gradient(circle,rgba(184,146,63,.12),transparent 65%);z-index:0}
.hero .wrap{position:relative;z-index:2}
.hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center}
.hero-text{max-width:600px}
/* Visuel hero */
.hero-visual{position:relative;height:420px}
.hv-glow{position:absolute;inset:-40px;background:radial-gradient(circle at 60% 40%,rgba(31,110,78,.16),transparent 60%);filter:blur(20px)}
.hv-card{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:0 30px 60px -28px rgba(21,41,63,.45)}
.hv-main{position:absolute;top:30px;right:0;width:90%;padding:22px;z-index:2;animation:float 6s ease-in-out infinite}
.hv-head{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:700;color:var(--navy);margin-bottom:18px}
.hv-head b{margin-left:6px}
.hv-dot{width:9px;height:9px;border-radius:50%;background:#e3ddd0}
.hv-dot:first-child{background:#e88}.hv-head .hv-dot:nth-child(2){background:#e9c46a}.hv-head .hv-dot:nth-child(3){background:#8bc7a3}
.hv-kpis{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px}
.hv-kpi{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:13px 15px}
.hv-kpi small{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;font-weight:700}
.hv-kpi strong{display:block;font-family:var(--serif);font-size:24px;color:var(--navy-deep);margin-top:3px}
.hv-kpi .up{font-size:12px;font-weight:700;color:var(--green)}
.hv-bars{display:flex;align-items:flex-end;gap:9px;height:90px;padding-top:6px}
.hv-bar{flex:1;background:linear-gradient(180deg,#cfe0d6,#a9cbb8);border-radius:5px 5px 0 0;min-height:8px}
.hv-bar.g{background:linear-gradient(180deg,var(--green-light),var(--green))}
.hv-float{position:absolute;display:flex;align-items:center;gap:11px;padding:13px 16px;z-index:3;border-radius:14px}
.hv-float b{display:block;font-size:13.5px;color:var(--navy-deep);font-weight:700}
.hv-float span{font-size:12px;color:var(--muted)}
.hv-float-1{bottom:46px;left:-14px;animation:float 5s ease-in-out infinite .4s}
.hv-float-2{bottom:-12px;right:24px;animation:float 5.5s ease-in-out infinite .8s}
.hv-mini-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;
  background:rgba(31,110,78,.12);color:var(--green);flex-shrink:0}
.hv-mini-ic svg{width:19px;height:19px}
.hv-mini-ic.gold{background:rgba(184,146,63,.14);color:var(--gold)}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
.kicker{display:inline-flex;align-items:center;gap:9px;font-size:12px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--green);background:rgba(31,110,78,.08);
  border:1px solid rgba(31,110,78,.18);padding:7px 15px;border-radius:30px;margin-bottom:26px}
.kicker .dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 0 4px rgba(31,110,78,.18)}
.hero h1{font-family:var(--serif);font-weight:400;font-size:clamp(40px,6.4vw,76px);line-height:1.02;
  letter-spacing:-1.4px;color:var(--navy-deep);max-width:14ch}
.hero h1 em{font-style:italic;color:var(--green);position:relative}
.hero h1 em::after{content:"";position:absolute;left:0;right:0;bottom:6px;height:10px;
  background:var(--gold-soft);z-index:-1;border-radius:3px}
.hero p.lead{font-size:clamp(17px,2vw,20px);color:var(--muted);max-width:60ch;margin:28px 0 38px;line-height:1.6}
.hero-cta{display:flex;gap:14px;flex-wrap:wrap;align-items:center}
.btn-lg{padding:15px 28px;font-size:16px;border-radius:13px}
.hero-trust{display:flex;gap:26px;margin-top:46px;flex-wrap:wrap}
.hero-trust div{display:flex;align-items:center;gap:9px;font-size:13.5px;color:var(--muted);font-weight:500}
.hero-trust svg{width:17px;height:17px;color:var(--green);flex-shrink:0}

/* marquee bandeau */
.strip{border-top:1px solid var(--line);border-bottom:1px solid var(--line);background:var(--navy-deep);
  padding:18px 0;overflow:hidden;position:relative;z-index:2}
.strip-track{display:flex;gap:60px;white-space:nowrap;animation:slide 32s linear infinite;width:max-content}
.strip-track span{font-family:var(--serif);font-size:18px;color:rgba(255,255,255,.55);font-style:italic;display:flex;align-items:center;gap:60px}
.strip-track span::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--gold)}
@keyframes slide{to{transform:translateX(-50%)}}

/* ===== SECTION HEADER ===== */
.sec{padding:88px 0}
.sec-head{max-width:680px;margin-bottom:54px}
.sec-num{font-family:var(--serif);font-size:14px;font-style:italic;color:var(--gold);font-weight:500;letter-spacing:1px}
.sec-head h2{font-family:var(--serif);font-weight:400;font-size:clamp(30px,4.4vw,46px);line-height:1.08;
  letter-spacing:-1px;color:var(--navy-deep);margin:10px 0 16px}
.sec-head p{font-size:17px;color:var(--muted);line-height:1.6}

/* ===== MODULES GRID ===== */
.mods{display:grid;grid-template-columns:repeat(2,1fr);gap:0;border:1px solid var(--line);border-radius:20px;
  overflow:hidden;background:var(--paper-2)}
.mod{padding:30px 30px;border-right:1px solid var(--line);border-bottom:1px solid var(--line);
  position:relative;transition:.25s;background:var(--paper-2)}
.mods .mod:nth-child(2n){border-right:none}
.mod:hover{background:#fff;z-index:2}
.mod-top{display:flex;align-items:flex-start;gap:16px}
.mod-ic{width:48px;height:48px;flex-shrink:0;border-radius:13px;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(155deg,rgba(31,110,78,.10),rgba(31,110,78,.04));
  border:1px solid rgba(31,110,78,.14);color:var(--green);transition:.25s}
.mod:hover .mod-ic{background:var(--green);color:#fff;transform:rotate(-4deg) scale(1.04)}
.mod-ic svg{width:23px;height:23px;stroke-width:1.6}
.mod-idx{margin-left:auto;font-family:var(--serif);font-style:italic;font-size:15px;color:var(--line);font-weight:500}
.mod:hover .mod-idx{color:var(--gold)}
.mod h3{font-size:18px;font-weight:700;color:var(--navy-deep);margin:18px 0 8px;letter-spacing:-.2px}
.mod p{font-size:14.5px;color:var(--muted);line-height:1.58}
.mod .tag{display:inline-block;margin-top:6px;font-size:11px;font-weight:700;letter-spacing:.5px;
  text-transform:uppercase;color:var(--gold);background:rgba(184,146,63,.1);padding:3px 9px;border-radius:6px}

/* ===== TARIFS ===== */
.tarifs{background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Crect x='29' y='0' width='2' height='2' fill='%23b8923f' opacity='.09'/%3E%3Crect x='0' y='29' width='2' height='2' fill='%23b8923f' opacity='.09'/%3E%3C/svg%3E"),linear-gradient(180deg,var(--navy-deep),#0f2031);color:#fff;position:relative;z-index:2}
.tarifs .sec-head h2{color:#fff}
.tarifs .sec-head p{color:rgba(255,255,255,.62)}
.tarifs .sec-num{color:var(--gold-light)}
.plans{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;align-items:stretch}
.plan{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:34px 30px;
  display:flex;flex-direction:column;transition:.25s;position:relative}
.plan:hover{transform:translateY(-6px);background:rgba(255,255,255,.07)}
.plan.feat{background:linear-gradient(170deg,rgba(184,146,63,.16),rgba(255,255,255,.05));
  border:1.5px solid var(--gold);box-shadow:0 30px 70px -30px rgba(184,146,63,.5);
  animation:glow-gold 3.4s ease-in-out infinite}
.plan-badge{position:absolute;top:-13px;left:50%;transform:translateX(-50%);
  background:var(--gold);color:var(--navy-deep);font-size:11.5px;font-weight:700;letter-spacing:1px;
  text-transform:uppercase;padding:6px 16px;border-radius:30px;box-shadow:0 8px 20px -8px rgba(184,146,63,.7)}
.plan-name{font-family:var(--serif);font-size:27px;font-weight:500;letter-spacing:-.4px}
.plan-tagline{font-size:13.5px;color:rgba(255,255,255,.55);margin-top:4px}
.plan-price{margin:24px 0 6px;display:flex;align-items:baseline;gap:8px}
.plan-price b{font-family:var(--serif);font-size:34px;font-weight:600}
.plan-price small{font-size:14px;color:rgba(255,255,255,.55)}
.plan-line{height:1px;background:rgba(255,255,255,.12);margin:22px 0}
.plan ul{list-style:none;display:flex;flex-direction:column;gap:13px;margin-bottom:30px;flex:1}
.plan li{display:flex;align-items:flex-start;gap:11px;font-size:14.5px;color:rgba(255,255,255,.85)}
.plan li svg{width:18px;height:18px;flex-shrink:0;margin-top:1px;color:var(--gold-light)}
.plan .btn{justify-content:center;width:100%}
.plan .btn-green{background:var(--green-light)}
.plan .btn-green:hover{background:var(--green)}
.plan.feat .btn-green{background:var(--gold);color:var(--navy-deep)}
.plan.feat .btn-green:hover{background:var(--gold-light)}
.plan .btn-outline-w{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.25);
  padding:13px 20px;border-radius:11px;justify-content:center;width:100%}
.plan .btn-outline-w:hover{border-color:var(--gold-light);color:var(--gold-light)}

/* ===== CTA FINAL — constellation ===== */
.final{padding:120px 0;text-align:center;position:relative;z-index:2;overflow:hidden}
.final-constel{background:radial-gradient(120% 130% at 50% 0%,#1e3a5f 0%,var(--navy-deep) 45%,#0e1d2e 100%)}
.final-constel #constel-canvas{position:absolute;inset:0;width:100%;height:100%;z-index:0;display:block}
.final-constel .constel-veil{position:absolute;inset:0;z-index:1;pointer-events:none;
  background:radial-gradient(60% 60% at 50% 55%,rgba(14,29,46,.0) 0%,rgba(14,29,46,.55) 100%)}
.final .wrap{position:relative;z-index:2;pointer-events:none}
.final .wrap .btn{pointer-events:auto}
.final h2{font-family:var(--serif);font-weight:400;font-size:clamp(32px,5vw,56px);line-height:1.05;
  letter-spacing:-1px;max-width:18ch;margin:0 auto 22px}
.final p{font-size:18px;max-width:54ch;margin:0 auto 34px}
/* Glow doré pulsant sur la carte tarif Populaire */
@keyframes glow-gold{0%,100%{box-shadow:0 30px 70px -30px rgba(184,146,63,.5),0 0 0 0 rgba(184,146,63,0)}
  50%{box-shadow:0 30px 70px -30px rgba(184,146,63,.65),0 0 30px -4px rgba(184,146,63,.3)}}
/* Shimmer one-shot sur les boutons verts */
.btn-green{position:relative;overflow:hidden}
.btn-green::after{content:"";position:absolute;top:0;left:-110%;width:60%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.28),transparent);transform:skewX(-20deg)}
.btn-green.shimmer-run::after{animation:shimmer-pass .6s ease forwards}
@keyframes shimmer-pass{from{left:-110%}to{left:170%}}

/* ===== FOOTER ===== */
.foot{border-top:1px solid var(--line);background:var(--paper-2);position:relative;z-index:2}
.foot-in{max-width:1200px;margin:0 auto;padding:38px 28px;display:flex;align-items:center;
  justify-content:space-between;flex-wrap:wrap;gap:18px}
.foot-in .brand b{font-size:18px}
.foot-links{display:flex;gap:24px;flex-wrap:wrap}
.foot-links a{font-size:13.5px;color:var(--muted);font-weight:500}
.foot-links a:hover{color:var(--green)}
.foot-copy{font-size:13px;color:var(--muted);width:100%;border-top:1px solid var(--line);padding-top:18px;margin-top:6px}

/* reveal on scroll */
.reveal{opacity:0;transform:translateY(32px) scale(.97);transition:opacity .65s cubic-bezier(.22,.68,0,1.15),transform .65s cubic-bezier(.22,.68,0,1.15)}
.mod{will-change:transform}
.reveal.in{opacity:1;transform:none}

@media(max-width:980px){
  .hero-grid{grid-template-columns:1fr}
  .hero-visual{display:none}
}
@media(max-width:900px){
  .mods{grid-template-columns:1fr}
  .mods .mod{border-right:none}
  .plans{grid-template-columns:1fr;max-width:440px;margin:0 auto}
  .plan.feat{order:-1}
  .nav-links .nav-link[href="#modules"],.nav-links .nav-link[href="#tarifs"]{display:none}
  .stats-band{grid-template-columns:repeat(2,1fr)!important}
}
@media(max-width:560px){
  .wrap{padding:0 20px}
  .hero{padding:56px 0 48px}
  .hero-cta .btn{width:100%;justify-content:center}
  /* Navbar compacte : logo + nom réduits, CTA toujours visible */
  .nav-in{padding:10px 14px;gap:8px}
  .brand img{width:38px;height:38px;border-radius:10px}
  .brand b{font-size:17px}
  .brand span{display:none}            /* masque "Comptable du Sénégal" */
  .nav-links{gap:4px}
  .nav-links .nav-link{padding:8px 8px;font-size:13px}
  .nav-links .btn-green{padding:9px 13px;font-size:13px;border-radius:9px;box-shadow:none}
  .nav-links .cta-extra{display:none}   /* "Créer un compte" suffit sur mobile */
}
@media(max-width:400px){
  .nav-in{gap:6px}
  .brand b{font-size:15px}
  .nav-links .nav-link{padding:7px 6px;font-size:12px}
  .btn-green{padding:8px 11px;font-size:12px}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="nav">
  <div class="nav-in">
    <a href="<?= APP_URL ?>/login" class="brand">
      <img src="/logo/sencompta-icon.svg" alt="SenCompta">
      <span style="line-height:1.05"><b>SenCompta</b><span>Comptable du Sénégal</span></span>
    </a>
    <div class="nav-links">
      <a href="#modules" class="nav-link">Fonctionnalités</a>
      <a href="#tarifs" class="nav-link">Tarifs</a>
      <a href="<?= APP_URL ?>/login" class="nav-link">Se connecter</a>
      <a href="<?= APP_URL ?>/inscription" class="btn btn-green">Créer un compte<span class="cta-extra">&nbsp;gratuit</span></a>
    </div>
  </div>
</nav>

<!-- HERO -->
<header class="hero">
  <div class="wrap hero-grid">
    <div class="hero-text">
      <span class="kicker reveal"><span class="dot"></span>SYSCOHADA · DGID · IPRES / CSS</span>
      <h1 class="reveal">Le SaaS comptable<br>du <em>Sénégal</em></h1>
      <p class="lead reveal">SenCompta réunit en un seul espace la comptabilité SYSCOHADA, la fiscalité DGID, la paie sénégalaise et la gestion commerciale. Pensé pour les cabinets d'expertise et les PME qui veulent tenir une comptabilité juste, conforme et rapide.</p>
      <div class="hero-cta reveal">
        <a href="<?= APP_URL ?>/inscription" class="btn btn-green btn-lg">Créer un compte gratuit
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        <a href="#modules" class="btn btn-ghost btn-lg">Voir les fonctionnalités</a>
      </div>
      <div class="hero-trust reveal">
        <div><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Conforme SYSCOHADA révisé</div>
        <div><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>Données chiffrées · 2FA</div>
      </div>
    </div>

    <!-- Visuel : carte tableau de bord flottante -->
    <div class="hero-visual reveal">
      <div class="hv-glow"></div>
      <div class="hv-card hv-main">
        <div class="hv-head"><span class="hv-dot"></span><span class="hv-dot"></span><span class="hv-dot"></span><b>Tableau de bord — Cabinet</b></div>
        <div class="hv-kpis">
          <div class="hv-kpi"><small>Résultat exercice</small><strong>+ 4,2 M</strong><span class="up">▲ 12%</span></div>
          <div class="hv-kpi"><small>Trésorerie</small><strong>8,7 M</strong><span class="up">FCFA</span></div>
        </div>
        <div class="hv-bars">
          <div class="hv-bar" style="height:38%"></div><div class="hv-bar" style="height:62%"></div>
          <div class="hv-bar g" style="height:88%"></div><div class="hv-bar" style="height:54%"></div>
          <div class="hv-bar" style="height:71%"></div><div class="hv-bar g" style="height:46%"></div>
          <div class="hv-bar" style="height:80%"></div>
        </div>
      </div>
      <div class="hv-card hv-float hv-float-1">
        <div class="hv-mini-ic"><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div><b>Écriture équilibrée</b><span>Débit = Crédit · validée</span></div>
      </div>
      <div class="hv-card hv-float hv-float-2">
        <div class="hv-mini-ic gold"><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg></div>
        <div><b>Scan IA</b><span>Facture lue en 2 s</span></div>
      </div>
    </div>
  </div>
</header>

<!-- STRIP -->
<div class="strip">
  <div class="strip-track">
    <span>Bilan<span>Compte de résultat</span><span>TAFIRE</span><span>Liasse DGID</span><span>Déclaration TVA</span><span>Bulletins de paie</span><span>Rapprochement bancaire</span><span>Analytique</span></span>
    <span>Bilan<span>Compte de résultat</span><span>TAFIRE</span><span>Liasse DGID</span><span>Déclaration TVA</span><span>Bulletins de paie</span><span>Rapprochement bancaire</span><span>Analytique</span></span>
  </div>
</div>

<!-- STATS BAND -->
<section style="padding:64px 0 0">
  <div class="wrap">
    <div class="stats-band reveal" style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--line);border-radius:18px;overflow:hidden;background:var(--paper-2)">
      <?php
      // [valeur affichée, label, data-count (num), data-suffix]
      $stats = [
        ['16','modules intégrés','16',''],
        ['3','référentiels (SYSCOHADA, DGID, IPRES)','3',''],
        ['2 s','pour lire une facture au scan IA',null,''],
        ['100%','en ligne · multi-utilisateurs','100','%'],
      ];
      foreach ($stats as $k=>$s): ?>
      <div style="padding:30px 26px;<?= $k<3?'border-right:1px solid var(--line)':'' ?>">
        <div style="font-family:var(--serif);font-size:40px;font-weight:600;color:var(--green);line-height:1;letter-spacing:-1px"<?= $s[2]!==null?' data-count="'.$s[2].'" data-suffix="'.$s[3].'"':'' ?>><?= $s[0] ?></div>
        <div style="font-size:13.5px;color:var(--muted);margin-top:8px;line-height:1.4"><?= $s[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- MODULES -->
<section class="sec" id="modules">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-num">01 — La plateforme</div>
      <h2>Seize modules, une seule comptabilité</h2>
      <p>De la première écriture jusqu'à la liasse fiscale, chaque étape du travail comptable est couverte. Voici comment chaque module vous fait gagner du temps au quotidien.</p>
    </div>
    <div class="mods">
      <?php
      $modules = [
        ['t'=>'Comptabilité SYSCOHADA','tag'=>'Cœur','d'=>"Saisissez vos écritures en partie double : le solde Débit = Crédit est vérifié en direct. Journaux, grand livre, balance et plan comptable OHADA sont tenus automatiquement.",'p'=>'M9 6.75H15m-6 3.75h6m-6 3.75h3M6.75 21h10.5a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
        ['t'=>'États financiers','tag'=>'','d'=>"Bilan, compte de résultat et TAFIRE se construisent tout seuls à partir de vos écritures. Plus de tableurs : vos états sont toujours à jour et exportables.",'p'=>'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5'],
        ['t'=>'États financiers DGID','tag'=>'Sénégal','d'=>"Générez la liasse fiscale et la DSF au format officiel de la Direction Générale des Impôts et Domaines, prête à déposer, en un clic depuis le dossier.",'p'=>'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ['t'=>'Déclarations fiscales','tag'=>'','d'=>"TVA, IS, CGU et retenues à la source : préparez vos déclarations et suivez le calendrier fiscal sénégalais pour ne jamais manquer une échéance.",'p'=>'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
        ['t'=>'Gestion de paie','tag'=>'','d'=>"Éditez les bulletins de salaire avec calcul automatique des cotisations IPRES, CSS et IPM. Gérez les congés et les déclarations sociales du personnel.",'p'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['t'=>'Scan IA','tag'=>'Intelligence','d'=>"Photographiez ou déposez une facture : l'intelligence artificielle lit le document, identifie le tiers, les montants et la TVA, puis propose l'écriture à valider.",'p'=>'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z'],
        ['t'=>'Comptabilité analytique','tag'=>'','d'=>"Créez des sections (chantiers, activités, services) et ventilez vos charges et produits. Le rapport de rentabilité vous montre ce que chaque activité rapporte.",'p'=>'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
        ['t'=>'Rapprochement bancaire','tag'=>'','d'=>"Importez le relevé CSV de votre banque : SenCompta rapproche les opérations avec vos écritures et lettre automatiquement ce qui correspond.",'p'=>'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
        ['t'=>'Lettrage & relances','tag'=>'','d'=>"Pointez les factures avec leurs règlements pour suivre les soldes par tiers, et relancez en quelques clics les clients dont les factures restent impayées.",'p'=>'M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244'],
        ['t'=>'Avoirs & notes de crédit','tag'=>'','d'=>"Annulez tout ou partie d'une facture : l'avoir génère automatiquement l'écriture d'extourne équilibrée et met à jour le solde du tiers.",'p'=>'M9 14l6-6M9 8h.01M15 14h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z'],
        ['t'=>'Import de données','tag'=>'','d'=>"Démarrez vite : importez vos clients, fournisseurs, plan comptable et balance d'ouverture N-1 depuis un fichier CSV ou Excel, avec modèles fournis.",'p'=>'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3'],
        ['t'=>'Immobilisations','tag'=>'','d'=>"Enregistrez vos biens et laissez SenCompta calculer les amortissements en mode linéaire ou dégressif, exercice après exercice.",'p'=>'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z'],
        ['t'=>'CRM & prospects','tag'=>'','d'=>"Suivez vos prospects dans un pipeline Kanban, créez des devis et convertissez-les en factures commerciales pour piloter l'activité du cabinet.",'p'=>'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
        ['t'=>'Multi-entreprises','tag'=>'','d'=>"Gérez plus de 20 dossiers d'entreprises depuis un espace unique. Basculez d'un client à l'autre sans jamais perdre le fil.",'p'=>'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
        ['t'=>'Droits & collaborateurs','tag'=>'','d'=>"Invitez votre équipe et attribuez des rôles Admin, Superviseur ou Collaborateur. Chacun n'accède qu'à ce qui le concerne, en toute traçabilité.",'p'=>'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        ['t'=>'Portail client','tag'=>'','d'=>"Donnez à vos clients un accès dédié : ils consultent leurs états financiers et déposent leurs pièces justificatives, qui arrivent directement dans le dossier.",'p'=>'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
      ];
      foreach ($modules as $i=>$m): ?>
      <div class="mod reveal">
        <div class="mod-top">
          <span class="mod-ic"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $m['p'] ?>"/></svg></span>
          <span class="mod-idx"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></span>
        </div>
        <h3><?= $m['t'] ?></h3>
        <p><?= $m['d'] ?></p>
        <?php if($m['tag']): ?><span class="tag"><?= $m['tag'] ?></span><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TARIFS -->
<section class="sec tarifs" id="tarifs">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-num">02 — Tarifs</div>
      <h2>Une formule pour chaque structure</h2>
      <p>Du comptable indépendant au cabinet établi, choisissez le nombre de dossiers et d'utilisateurs dont vous avez besoin. Tous les modules sont inclus dans chaque formule.</p>
    </div>
    <div class="plans">

      <div class="plan reveal">
        <div class="plan-name">Solo</div>
        <div class="plan-tagline">Le comptable indépendant</div>
        <div class="plan-price"><b>1</b><small>entreprise gérée</small></div>
        <div class="plan-line"></div>
        <ul>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><b>2&nbsp;utilisateurs</b>&nbsp;inclus</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Les 16 modules sans restriction</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Scan IA &amp; liasse DGID</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Sauvegardes &amp; sécurité 2FA</li>
        </ul>
        <a href="<?= APP_URL ?>/inscription" class="btn btn-outline-w">Créer un compte</a>
      </div>

      <div class="plan feat reveal">
        <span class="plan-badge">Populaire</span>
        <div class="plan-name">Pro</div>
        <div class="plan-tagline">La PME en croissance</div>
        <div class="plan-price"><b>5</b><small>entreprises gérées</small></div>
        <div class="plan-line"></div>
        <ul>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><b>5&nbsp;utilisateurs</b>&nbsp;inclus</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Tout le plan Solo</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Rôles &amp; portail client</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Analytique &amp; multi-exercices</li>
        </ul>
        <a href="<?= APP_URL ?>/inscription" class="btn btn-green">Créer un compte</a>
      </div>

      <div class="plan reveal">
        <div class="plan-name">Cabinet</div>
        <div class="plan-tagline">Le cabinet d'expertise</div>
        <div class="plan-price"><b>10</b><small>entreprises gérées</small></div>
        <div class="plan-line"></div>
        <ul>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><b>10&nbsp;utilisateurs</b>&nbsp;inclus</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Tout le plan Pro</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Supervision multi-collaborateurs</li>
          <li><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Accompagnement prioritaire</li>
        </ul>
        <a href="<?= APP_URL ?>/inscription" class="btn btn-outline-w">Créer un compte</a>
      </div>

    </div>
    <p style="text-align:center;margin-top:30px;font-size:14px;color:rgba(255,255,255,.5)">Tarification détaillée communiquée à l'inscription · sans engagement.</p>
  </div>
</section>

<!-- CTA FINAL — constellation interactive -->
<section class="final final-constel">
  <canvas id="constel-canvas"></canvas>
  <div class="constel-veil"></div>
  <div class="wrap">
    <div class="sec-num reveal" style="color:var(--gold-light)">03 — Un écosystème connecté</div>
    <h2 class="reveal" style="color:#fff">Toutes vos données,<br>reliées entre elles.</h2>
    <p class="reveal" style="color:rgba(255,255,255,.66)">Écritures, tiers, fiscalité, paie : chaque saisie alimente automatiquement vos états, vos déclarations et vos rapports. Ouvrez votre espace en quelques minutes.</p>
    <a href="<?= APP_URL ?>/inscription" class="btn btn-green btn-lg reveal">Créer un compte gratuit
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></a>
  </div>
</section>

<!-- FOOTER -->
<footer class="foot">
  <div class="foot-in">
    <a href="<?= APP_URL ?>/login" class="brand">
      <img src="/logo/sencompta-icon.svg" alt="SenCompta">
      <span style="line-height:1.05"><b>SenCompta</b><span>Comptable du Sénégal</span></span>
    </a>
    <div class="foot-links">
      <a href="#modules">Fonctionnalités</a>
      <a href="#tarifs">Tarifs</a>
      <a href="<?= APP_URL ?>/login">Se connecter</a>
      <a href="<?= APP_URL ?>/confidentialite">Confidentialité</a>
      <a href="<?= APP_URL ?>/mentions-legales">Mentions légales</a>
      <a href="<?= APP_URL ?>/cgu">CGU</a>
    </div>
    <div class="foot-copy">© <?= date('Y') ?> SenCompta · Tous droits réservés · Le SaaS comptable du Sénégal.</div>
  </div>
</footer>

<script>
(function(){
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Cascade dédiée aux modules (par rangée)
  document.querySelectorAll('.mod').forEach(function(el,i){
    var row = Math.floor(i/2), col = i%2;
    el.style.transitionDelay = (row*70 + col*55) + 'ms';
  });

  // Reveal au scroll
  var io = new IntersectionObserver(function(es){
    es.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }});
  },{threshold:.12, rootMargin:'0px 0px -40px 0px'});
  document.querySelectorAll('.reveal').forEach(function(el){
    if (!el.style.transitionDelay) el.style.transitionDelay = '0ms';
    io.observe(el);
  });

  // Compteurs animés sur les stats
  function easeOutCubic(t){ return 1 - Math.pow(1-t, 3); }
  function animateCount(el){
    var target = parseFloat(el.getAttribute('data-count'));
    var suffix = el.getAttribute('data-suffix') || '';
    if (reduce){ el.textContent = target + suffix; return; }
    var dur = 1400, start = null;
    function step(ts){
      if (!start) start = ts;
      var t = Math.min((ts-start)/dur, 1);
      el.textContent = Math.round(easeOutCubic(t)*target) + suffix;
      if (t < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  var statObs = new IntersectionObserver(function(es){
    es.forEach(function(e){ if(e.isIntersecting){ animateCount(e.target); statObs.unobserve(e.target); }});
  },{threshold:.6});
  document.querySelectorAll('[data-count]').forEach(function(el){ statObs.observe(el); });

  // Hover 3D subtil sur les cartes modules (desktop uniquement)
  if (!reduce && window.matchMedia('(hover:hover)').matches){
    document.querySelectorAll('.mod').forEach(function(card){
      card.addEventListener('mousemove', function(e){
        var r = card.getBoundingClientRect();
        var x = (e.clientX-r.left)/r.width - .5, y = (e.clientY-r.top)/r.height - .5;
        card.style.transition = 'transform .08s ease';
        card.style.transform = 'perspective(700px) rotateY('+(x*5)+'deg) rotateX('+(-y*5)+'deg) translateZ(4px)';
      });
      card.addEventListener('mouseleave', function(){
        card.style.transition = 'transform .45s cubic-bezier(.2,.7,.2,1)';
        card.style.transform = 'perspective(700px) rotateY(0) rotateX(0) translateZ(0)';
      });
    });
  }

  // Shimmer one-shot sur le CTA final quand il entre dans le viewport
  var ctaBtn = document.querySelector('.final .btn-green');
  if (ctaBtn && !reduce){
    var ob = new IntersectionObserver(function(es){
      if (es[0].isIntersecting){ setTimeout(function(){ ctaBtn.classList.add('shimmer-run'); }, 400); ob.disconnect(); }
    },{threshold:.8});
    ob.observe(ctaBtn);
  }
})();

// ===== Constellation interactive (CTA final) =====
(function(){
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var cv = document.getElementById('constel-canvas');
  if (!cv) return;
  var ctx = cv.getContext('2d'), W=0, H=0, dpr = Math.min(window.devicePixelRatio||1, 2);
  var pts = [], mouse = {x:-9999, y:-9999};
  var GOLD = '184,146,63', GREEN='42,138,99', LIGHT='255,255,255';

  function resize(){
    var r = cv.getBoundingClientRect();
    W = r.width; H = r.height;
    cv.width = W*dpr; cv.height = H*dpr; ctx.setTransform(dpr,0,0,dpr,0,0);
    var n = Math.round(Math.min(72, (W*H)/16000));
    pts = [];
    for (var i=0;i<n;i++){
      pts.push({x:Math.random()*W, y:Math.random()*H,
        vx:(Math.random()-.5)*.22, vy:(Math.random()-.5)*.22,
        r:Math.random()*1.6+1, g:Math.random()<.22});
    }
  }
  function tick(){
    ctx.clearRect(0,0,W,H);
    for (var i=0;i<pts.length;i++){
      var p=pts[i];
      p.x+=p.vx; p.y+=p.vy;
      if(p.x<0||p.x>W)p.vx*=-1; if(p.y<0||p.y>H)p.vy*=-1;
      // attraction douce vers la souris
      var dxm=mouse.x-p.x, dym=mouse.y-p.y, dm=Math.hypot(dxm,dym);
      var near = dm<140;
      if(near){ p.x+=dxm*0.0015; p.y+=dym*0.0015; }
      // liaisons
      for (var j=i+1;j<pts.length;j++){
        var q=pts[j], dx=p.x-q.x, dy=p.y-q.y, d=Math.hypot(dx,dy);
        if (d<118){
          var a=(1-d/118)*0.16;
          var col = (near||Math.hypot(mouse.x-q.x,mouse.y-q.y)<140) ? GOLD : LIGHT;
          ctx.strokeStyle='rgba('+col+','+a+')'; ctx.lineWidth=.6;
          ctx.beginPath(); ctx.moveTo(p.x,p.y); ctx.lineTo(q.x,q.y); ctx.stroke();
        }
      }
      // point
      var base = p.g?GREEN:LIGHT;
      var pa = near? .9 : .42;
      ctx.fillStyle='rgba('+(near?GOLD:base)+','+pa+')';
      ctx.beginPath(); ctx.arc(p.x,p.y,near?p.r+.7:p.r,0,Math.PI*2); ctx.fill();
      if(near){ ctx.fillStyle='rgba('+GOLD+',.14)'; ctx.beginPath(); ctx.arc(p.x,p.y,p.r+6,0,Math.PI*2); ctx.fill(); }
    }
    raf=requestAnimationFrame(tick);
  }
  var raf;
  function start(){ if(!raf) tick(); }
  function stop(){ if(raf){ cancelAnimationFrame(raf); raf=null; } }

  cv.parentElement.addEventListener('pointermove', function(e){
    var r=cv.getBoundingClientRect(); mouse.x=e.clientX-r.left; mouse.y=e.clientY-r.top;
  });
  cv.parentElement.addEventListener('pointerleave', function(){ mouse.x=-9999; mouse.y=-9999; });

  window.addEventListener('resize', resize);
  resize();
  if (reduce){ tick(); stop(); } // dessine une frame statique
  else {
    // n'anime que lorsque visible (perf)
    var vo=new IntersectionObserver(function(es){ es[0].isIntersecting?start():stop(); },{threshold:.05});
    vo.observe(cv);
  }
})();
</script>
</body>
</html>
