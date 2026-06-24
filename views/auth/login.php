<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Connexion</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:       #1e3a5f;
    --navy-dark:  #122540;
    --navy-light: #2a4f7c;
    --gold:       #c9a96e;
    --gold-light: #e2c99a;
    --gold-dark:  #a8843f;
    --white:      #ffffff;
}

html, body {
    height: 100%;
    font-family: 'DM Sans', sans-serif;
    background: var(--navy-dark);
    overflow-x: hidden;
}

/* ── Fond ── */
.bg-canvas {
    position: fixed; inset: 0; z-index: 0;
    background: linear-gradient(140deg, #0a1628 0%, #1a3352 45%, #0d1f38 100%);
}
.bg-grid {
    position: fixed; inset: 0; z-index: 0;
    background-image:
        linear-gradient(rgba(201,169,110,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,169,110,0.04) 1px, transparent 1px);
    background-size: 56px 56px;
}
.orb {
    position: fixed; border-radius: 50%; filter: blur(90px); opacity: 0.12;
    animation: float 9s ease-in-out infinite;
}
.orb-1 { width: 600px; height: 600px; background: var(--gold);   top: -200px; right: -150px; animation-delay: 0s; }
.orb-2 { width: 400px; height: 400px; background: #3b82f6;       bottom: -120px; left: -100px; animation-delay: 4s; }
.orb-3 { width: 280px; height: 280px; background: var(--gold-light); top: 55%; left: 25%; animation-delay: 7s; }

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50%       { transform: translateY(-28px) scale(1.04); }
}

/* ── Layout split ── */
.page {
    position: relative; z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1px 480px;
    height: 100vh;
    overflow: hidden;
}

/* ── Panneau gauche ── */
.left {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 32px 52px 32px;
    height: 100vh;
    overflow: hidden;
    animation: fadeLeft .7s cubic-bezier(.16,1,.3,1) both;
}
@keyframes fadeLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to   { opacity: 1; transform: translateX(0); }
}

.brand {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
    margin-bottom: 20px;
}
.brand-mark {
    width: 64px; height: 64px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.brand-mark img {
    width: 64px; height: 64px;
    object-fit: contain;
    display: block;
}
.brand-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px; font-weight: 600;
    color: var(--white);
    letter-spacing: .5px;
    line-height: 1;
}
.brand-sub { font-size: 11px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1.5px; margin-top: 6px; }

.hero { flex-shrink: 0; margin-bottom: 20px; }
.hero-tagline {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(30px, 2.9vw, 44px);
    font-weight: 300;
    color: var(--white);
    line-height: 1.08;
    letter-spacing: -.5px;
    margin-bottom: 7px;
}
.hero-tagline em { font-style: italic; color: var(--gold); }
.hero-desc {
    font-size: 15px;
    color: rgba(255,255,255,0.55);
    line-height: 1.65;
}

.modules-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-auto-rows: 1fr;
    gap: 8px;
    flex: 1;
}
.mod-card {
    background: rgba(255,255,255,0.045);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 12px;
    padding: 0 14px;
    display: flex;
    align-items: center;
    gap: 13px;
    transition: background .2s, border-color .2s;
    cursor: default;
}
.mod-card:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(201,169,110,0.35);
}
.mod-icon {
    width: 38px; height: 38px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mod-body { flex: 1; min-width: 0; }
.mod-title {
    font-size: 15px; font-weight: 700;
    color: #f8fafc;
    line-height: 1.3;
}
.mod-desc {
    font-size: 13px;
    color: rgba(255,255,255,0.62);
    line-height: 1.4;
    margin-top: 2px;
}
.mod-badge {
    display: inline-block;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    flex-shrink: 0;
}
.badge-live { background: rgba(34,197,94,0.15); color: #6ee7b7; border: 1px solid rgba(34,197,94,0.3); }
.badge-soon { background: rgba(201,169,110,0.12); color: #e8c87a; border: 1px solid rgba(201,169,110,0.3); }

/* Séparateur vertical */
.sep {
    width: 1px;
    background: linear-gradient(to bottom,
        transparent 5%,
        rgba(201,169,110,0.25) 30%,
        rgba(201,169,110,0.25) 70%,
        transparent 95%);
}

/* ── Panneau droit formulaire ── */
.right {
    background: rgba(255,255,255,0.035);
    backdrop-filter: blur(24px);
    border-left: 1px solid rgba(201,169,110,0.12);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 64px 56px;
    animation: fadeRight .7s cubic-bezier(.16,1,.3,1) .1s both;
}
@keyframes fadeRight {
    from { opacity: 0; transform: translateX(30px); }
    to   { opacity: 1; transform: translateX(0); }
}

.form-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 2.5px;
    font-weight: 500;
    margin-bottom: 16px;
}
.form-eyebrow::before {
    content: '';
    display: block;
    width: 24px; height: 1.5px;
    background: var(--gold);
    opacity: .6;
}

.form-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 38px; font-weight: 400;
    color: var(--white);
    line-height: 1.15;
    letter-spacing: -.3px;
    margin-bottom: 8px;
}
.form-sub {
    font-size: 14.5px;
    color: rgba(255,255,255,0.35);
    margin-bottom: 40px;
}

/* Erreur */
.alert-error {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(220,53,69,0.1);
    border: 1px solid rgba(220,53,69,0.3);
    border-radius: 12px;
    padding: 14px 18px;
    color: #ff9090;
    font-size: 14px;
    margin-bottom: 28px;
}
.alert-error svg { flex-shrink: 0; width: 18px; height: 18px; }

/* Champs */
.field { margin-bottom: 22px; }
.field label {
    display: block;
    font-size: 12px;
    color: rgba(255,255,255,0.45);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 500;
    margin-bottom: 10px;
}
.input-wrap { position: relative; }
.input-wrap svg {
    position: absolute;
    left: 16px; top: 50%;
    transform: translateY(-50%);
    width: 18px; height: 18px;
    color: rgba(255,255,255,0.22);
    pointer-events: none;
    transition: color .2s;
    z-index: 2;
}
.input-wrap:focus-within svg { color: var(--gold); }
.field input {
    width: 100%;
    padding: 16px 16px 16px 50px;
    background: rgba(255,255,255,0.055);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    color: var(--white);
    font-size: 15.5px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: all .22s ease;
}
.field input::placeholder { color: rgba(255,255,255,0.18); }
.field input:focus {
    background: rgba(255,255,255,0.08);
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,169,110,0.12);
}

/* Bouton */
.btn-login {
    width: 100%;
    padding: 18px;
    margin-top: 8px;
    background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--gold-dark));
    background-size: 200% 200%;
    background-position: 0% 50%;
    border: none;
    border-radius: 12px;
    color: var(--navy-dark);
    font-size: 15.5px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    letter-spacing: .4px;
    cursor: pointer;
    transition: all .25s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(201,169,110,0.3);
}
.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 36px rgba(201,169,110,0.45);
    background-position: 100% 50%;
}
.btn-login:active { transform: translateY(0); }

/* Footer */
.form-footer {
    margin-top: 36px;
    text-align: center;
    font-size: 12.5px;
    color: rgba(255,255,255,0.18);
    line-height: 1.8;
}
.form-footer strong { color: rgba(201,169,110,0.6); font-weight: 500; }

/* Badges confiance */
.trust-badges {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 24px;
}
.trust-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: rgba(255,255,255,0.2);
}
.trust-badge svg { width: 14px; height: 14px; color: var(--gold); opacity: .5; }

/* ── Responsive ── */
@media (max-width: 1200px) {
    .page { grid-template-columns: 1fr 460px; }
    .left  { padding: 28px 44px; }
}
@media (max-width: 960px) {
    .page { grid-template-columns: 1fr 400px; }
    .left  { padding: 24px 36px; }
    .hero-tagline { font-size: 28px; }
}
@media (max-width: 768px) {
    .page { grid-template-columns: 1fr; grid-template-rows: auto auto; height: auto; overflow: visible; }
    .sep  { display: none; }
    .left { height: auto; min-height: auto; padding: 32px 24px; overflow: visible; }
    .right { height: auto; overflow: visible; padding: 32px 24px 40px; border-left: none; border-top: 1px solid rgba(255,255,255,0.08); justify-content: flex-start; }
    .hero-tagline { font-size: 26px; }
    .modules-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .form-title { font-size: 26px; }
    .form-box { max-width: 100%; }
    body { overflow-y: auto !important; height: auto !important; }
}
@media (max-width: 480px) {
    .left { padding: 24px 16px; }
    .right { padding: 24px 16px 32px; }
    .hero-tagline { font-size: 22px; }
    .modules-grid { grid-template-columns: 1fr 1fr; }
    .form-title { font-size: 22px; }
    .brand-mark img { width: 48px; height: 48px; }
}
@media (max-width: 380px) {
    .right { padding: 32px 16px; }
    .form-title { font-size: 22px; }
}
</style>
</head>
<body>

<div class="bg-canvas"></div>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="page">

    <!-- ── Panneau gauche ── -->
    <div class="left">

        <div class="brand">
            <div class="brand-mark">
                <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
            </div>
            <div>
                <div class="brand-name">SenCompta</div>
                <div class="brand-sub">Le SaaS Comptable du Sénégal</div>
            </div>
        </div>

        <div class="hero">
            <h1 class="hero-tagline">
                La comptabilité<br>
                <em>multi-entreprises</em><br>
                réinventée.
            </h1>
            <p class="hero-desc">
                Plateforme de gestion comptable conforme aux normes OHADA SYSCOHADA,
                conçue pour les cabinets d'expertise comptable au Sénégal.
                Gérez plusieurs dossiers simultanément avec précision.
            </p>
        </div>

        <div class="modules-grid">
<?php
$modules = [
    ['bg'=>'rgba(201,169,110,0.12)','border'=>'rgba(201,169,110,0.25)','stroke'=>'#c9a96e','svg'=>'<path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-8"/>','title'=>'Comptabilité SYSCOHADA','desc'=>'Grand livre, bilan, journaux OHADA','live'=>true],
    ['bg'=>'rgba(34,197,94,0.08)','border'=>'rgba(34,197,94,0.2)','stroke'=>'#4ade80','svg'=>'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>','title'=>'Multi-entreprises','desc'=>'20+ dossiers, un seul espace','live'=>true],
    ['bg'=>'rgba(99,102,241,0.12)','border'=>'rgba(99,102,241,0.25)','stroke'=>'#818cf8','svg'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','title'=>'CRM & Prospects','desc'=>'Pipeline Kanban, devis, factures','live'=>true],
    ['bg'=>'rgba(251,191,36,0.08)','border'=>'rgba(251,191,36,0.2)','stroke'=>'#fbbf24','svg'=>'<rect x="3" y="3" width="16" height="16" rx="2"/><path d="M3 9h18M9 21V9"/>','title'=>'Scan IA','desc'=>'Documents → écritures automatiques','live'=>true],
    ['bg'=>'rgba(139,92,246,0.1)','border'=>'rgba(139,92,246,0.25)','stroke'=>'#a78bfa','svg'=>'<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>','title'=>'États Financiers','desc'=>'Bilan, résultat, trésorerie SYSCOHADA','live'=>true],
    ['bg'=>'rgba(16,185,129,0.08)','border'=>'rgba(16,185,129,0.22)','stroke'=>'#34d399','svg'=>'<path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>','title'=>'Rapprochement Bancaire','desc'=>'Import CSV, lettrage automatique','live'=>true],
    ['bg'=>'rgba(251,146,60,0.08)','border'=>'rgba(251,146,60,0.22)','stroke'=>'#fb923c','svg'=>'<circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0112 0v2"/><path d="M18 8l1.5 1.5L22 7"/>','title'=>'Droits & Collaborateurs','desc'=>'Rôles Admin / Superviseur / Collab.','live'=>true],
    ['bg'=>'rgba(248,113,113,0.08)','border'=>'rgba(248,113,113,0.18)','stroke'=>'#f87171','svg'=>'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>','title'=>'Déclarations Fiscales','desc'=>'TVA, IS, retenues à la source','live'=>true],
    ['bg'=>'rgba(20,184,166,0.08)','border'=>'rgba(20,184,166,0.22)','stroke'=>'#2dd4bf','svg'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M6 15h.01M10 15h4"/>','title'=>'Gestion de Paie','desc'=>'Bulletins, IPRES/CSS, salaires','live'=>true],
    ['bg'=>'rgba(34,211,238,0.08)','border'=>'rgba(34,211,238,0.18)','stroke'=>'#22d3ee','svg'=>'<rect x="3" y="4" width="16" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>','title'=>'Planning & Honoraires','desc'=>'Missions, échéances fiscales','live'=>true],
];
foreach ($modules as $m): ?>
            <div class="mod-card">
                <div class="mod-icon" style="background:<?= $m['bg'] ?>;border:1px solid <?= $m['border'] ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="<?= $m['stroke'] ?>" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $m['svg'] ?></svg>
                </div>
                <div class="mod-body">
                    <div class="mod-title"><?= $m['title'] ?></div>
                    <div class="mod-desc"><?= $m['desc'] ?></div>
                </div>
                <span class="mod-badge <?= $m['live'] ? 'badge-live' : 'badge-soon' ?>"><?= $m['live'] ? 'Actif' : 'Bientôt' ?></span>
            </div>
<?php endforeach; ?>

        </div>

    </div>

    <div class="sep"></div>

    <!-- ── Formulaire ── -->
    <div class="right">

        <p class="form-eyebrow">Espace sécurisé</p>
        <h2 class="form-title">Connexion au<br>tableau de bord</h2>
        <p class="form-sub">Accès réservé aux membres du cabinet</p>

        <?php if ($error): ?>
        <div class="alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
            </svg>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/login/post" autocomplete="on">

            <div class="field">
                <label for="email">Adresse email</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email"
                           placeholder="votre@cabinet-smc.sn"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           required autocomplete="email">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
            </div>

            <div class="field">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <label for="password" style="margin-bottom:0">Mot de passe</label>
                    <a href="<?= APP_URL ?>/mot-de-passe-oublie" style="font-size:12px;color:var(--gold);opacity:.8;text-decoration:none;transition:opacity .2s" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.8">
                        Mot de passe oublié ?
                    </a>
                </div>
                <div class="input-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
            </div>

            <?= csrfField() ?>
            <button type="submit" class="btn-login">Se connecter →</button>

        </form>

        <div style="margin-top:16px;text-align:center;">
            <span style="font-size:13px;color:rgba(255,255,255,0.35);">Pas encore de compte ?</span>
            <a href="<?= APP_URL ?>/inscription" style="display:block;margin-top:10px;width:100%;padding:14px;border:1.5px solid rgba(201,169,110,0.4);border-radius:12px;color:var(--gold);font-size:14px;font-weight:600;text-align:center;text-decoration:none;transition:all .2s;"
               onmouseover="this.style.background='rgba(201,169,110,0.08)';this.style.borderColor='var(--gold)'"
               onmouseout="this.style.background='transparent';this.style.borderColor='rgba(201,169,110,0.4)'">
                Créer un espace cabinet →
            </a>
        </div>

        <div class="trust-badges">
            <span class="trust-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                Connexion chiffrée
            </span>
            <span class="trust-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                Authentification 2FA
            </span>
            <span class="trust-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Session 8 heures
            </span>
        </div>

        <div class="form-footer">
            <p>© <?= date('Y') ?> <strong>SenCompta</strong> · Tous droits réservés</p>
            <p>Propulsé par <strong>SENGESTION ERP</strong></p>
        </div>

    </div>

</div>
</body>
</html>
