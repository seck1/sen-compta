<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Connexion</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:       #1e3a5f;
    --navy-dark:  #122540;
    --gold:       #b8923f;
    --gold-soft:  #c9a96e;
    --gold-light: #e7d9b8;
    --ink:        #1a2433;
    --muted:      #6b7689;
    --line:       #e7e3da;
    --cream:      #f7f5ef;
    --cream-2:    #efeae0;
    --white:      #ffffff;
    --green:      #2f7d5b;
}

html, body {
    min-height: 100%;
    font-family: 'DM Sans', sans-serif;
    color: var(--ink);
    background: var(--cream);
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

/* ── Texture de fond lumineuse ── */
body::before {
    content: '';
    position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background:
        radial-gradient(1100px 600px at 12% -5%, rgba(184,146,63,0.07), transparent 60%),
        radial-gradient(900px 600px at 105% 110%, rgba(30,58,95,0.06), transparent 55%),
        linear-gradient(180deg, #faf8f3 0%, var(--cream) 45%, var(--cream-2) 100%);
}
body::after {
    content: '';
    position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.5;
    background-image:
        linear-gradient(rgba(30,58,95,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(30,58,95,0.035) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(ellipse at 30% 30%, #000 0%, transparent 75%);
}

.page {
    position: relative; z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1px 480px;
    min-height: 100vh;
    max-width: 1480px;
    margin: 0 auto;
}

/* ============ PANNEAU GAUCHE ============ */
.left {
    padding: 56px 64px;
    display: flex; flex-direction: column;
    overflow: hidden;
}

.brand { display: flex; align-items: center; gap: 16px; margin-bottom: 48px; animation: rise .7s ease both; }
.brand-mark { width: 60px; height: 60px; flex-shrink: 0; }
.brand-mark img { width: 60px; height: 60px; object-fit: contain; display: block; filter: drop-shadow(0 6px 14px rgba(30,58,95,0.18)); }
.brand-name { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 700; color: var(--navy); letter-spacing: 0.3px; line-height: 1; }
.brand-sub  { font-size: 11px; letter-spacing: 3px; text-transform: uppercase; color: var(--gold); margin-top: 5px; font-weight: 600; }

.hero { margin-bottom: 40px; max-width: 620px; }
.hero-tagline {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 600;
    font-size: clamp(40px, 4.6vw, 62px);
    line-height: 1.04;
    color: var(--navy-dark);
    letter-spacing: -0.5px;
    animation: rise .7s ease .08s both;
}
.hero-tagline em {
    font-style: italic; font-weight: 500;
    color: var(--gold);
    position: relative;
}
.hero-desc {
    margin-top: 22px;
    font-size: 16px; line-height: 1.7; color: var(--muted);
    max-width: 560px;
    animation: rise .7s ease .16s both;
}

.modules-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    animation: rise .7s ease .24s both;
}
.mod-card {
    display: flex; align-items: center; gap: 13px;
    padding: 15px 16px;
    background: rgba(255,255,255,0.72);
    border: 1px solid var(--line);
    border-radius: 14px;
    backdrop-filter: blur(6px);
    transition: transform .25s cubic-bezier(.2,.7,.3,1), box-shadow .25s, border-color .25s;
}
.mod-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 26px -14px rgba(30,58,95,0.28);
    border-color: rgba(184,146,63,0.4);
}
.mod-icon {
    width: 38px; height: 38px; flex-shrink: 0;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
}
.mod-body { flex: 1; min-width: 0; }
.mod-title { font-size: 14px; font-weight: 600; color: var(--ink); line-height: 1.25; }
.mod-desc  { font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.3; }
.mod-badge {
    font-size: 10px; font-weight: 700; letter-spacing: 0.5px;
    padding: 3px 9px; border-radius: 20px; flex-shrink: 0;
    text-transform: uppercase;
}
.badge-live { background: rgba(47,125,91,0.1); color: var(--green); border: 1px solid rgba(47,125,91,0.22); }
.badge-soon { background: rgba(107,118,137,0.1); color: var(--muted); border: 1px solid rgba(107,118,137,0.2); }

/* Séparateur */
.sep { background: linear-gradient(180deg, transparent, var(--line) 15%, var(--line) 85%, transparent); }

/* ============ PANNEAU DROIT (carte connexion) ============ */
.right {
    display: flex; flex-direction: column; justify-content: center;
    padding: 56px 56px;
    background: linear-gradient(180deg, #ffffff, #fdfcf9);
    box-shadow: -24px 0 60px -40px rgba(30,58,95,0.18);
}
.form-box { width: 100%; max-width: 380px; margin: 0 auto; animation: rise .7s ease .12s both; }

.form-eyebrow {
    font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold); font-weight: 700; margin-bottom: 14px;
    display: flex; align-items: center; gap: 10px;
}
.form-eyebrow::before { content: ''; width: 28px; height: 1px; background: var(--gold); }
.form-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 40px; font-weight: 700; line-height: 1.05;
    color: var(--navy-dark); letter-spacing: -0.4px;
}
.form-sub { font-size: 14px; color: var(--muted); margin-top: 12px; margin-bottom: 32px; }

.field { margin-bottom: 20px; }
.field label { display: block; font-size: 12px; font-weight: 600; color: var(--ink); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 9px; }
.input-wrap { position: relative; }
.input-wrap input {
    width: 100%;
    padding: 15px 16px 15px 46px;
    font-family: 'DM Sans', sans-serif; font-size: 15px; color: var(--ink);
    background: var(--cream);
    border: 1.5px solid var(--line);
    border-radius: 13px;
    transition: border-color .2s, background .2s, box-shadow .2s;
}
.input-wrap input::placeholder { color: #aab0bd; }
.input-wrap input:focus {
    outline: none;
    border-color: var(--gold);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(184,146,63,0.12);
}
.input-wrap svg {
    position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
    width: 19px; height: 19px; color: var(--gold); opacity: 0.85; pointer-events: none;
}

.btn-login {
    width: 100%;
    padding: 16px;
    margin-top: 4px;
    font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;
    color: #fff;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%);
    border: none; border-radius: 13px;
    cursor: pointer;
    box-shadow: 0 12px 26px -12px rgba(18,37,64,0.6);
    transition: transform .2s, box-shadow .2s, filter .2s;
}
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 18px 34px -14px rgba(18,37,64,0.7); filter: brightness(1.08); }
.btn-login:active { transform: translateY(0); }

.alert-error {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 15px; margin-bottom: 22px;
    background: rgba(220,38,38,0.06);
    border: 1px solid rgba(220,38,38,0.22);
    border-radius: 12px;
    color: #b91c1c; font-size: 13.5px; font-weight: 500;
}
.alert-error svg { width: 19px; height: 19px; flex-shrink: 0; }

.alt-sep { display: flex; align-items: center; gap: 14px; margin: 22px 0 18px; color: var(--muted); font-size: 12px; }
.alt-sep::before, .alt-sep::after { content: ''; flex: 1; height: 1px; background: var(--line); }

.btn-secondary {
    display: block; width: 100%; padding: 14px; text-align: center;
    border: 1.5px solid var(--line); border-radius: 13px;
    color: var(--navy); font-size: 14px; font-weight: 600; text-decoration: none;
    transition: all .2s; background: #fff;
}
.btn-secondary:hover { border-color: var(--gold); background: rgba(184,146,63,0.05); color: var(--gold); }

.forgot-link { font-size: 12px; color: var(--gold); text-decoration: none; font-weight: 600; transition: opacity .2s; }
.forgot-link:hover { opacity: 0.7; }

.trust-badges { display: flex; gap: 18px; margin-top: 30px; flex-wrap: wrap; }
.trust-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--muted); font-weight: 500; }
.trust-badge svg { width: 14px; height: 14px; color: var(--green); }

.form-footer { margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--line); }
.form-footer p { font-size: 11.5px; color: var(--muted); line-height: 1.7; }
.form-footer strong { color: var(--navy); font-weight: 600; }

@keyframes rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

/* ============ RESPONSIVE ============ */
@media (max-width: 1200px) { .page { grid-template-columns: 1fr 1px 420px; } .left { padding: 44px 48px; } }
@media (max-width: 960px) {
    .page { grid-template-columns: 1fr; }
    .sep { display: none; }
    .left { padding: 40px 32px; }
    .right { box-shadow: none; border-top: 1px solid var(--line); padding: 44px 32px; }
    .modules-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
    .left { padding: 32px 18px; }
    .right { padding: 36px 18px; }
    .hero-tagline { font-size: clamp(34px, 9vw, 44px); }
    .form-title { font-size: 32px; }
    .modules-grid { grid-template-columns: 1fr; }
    .trust-badges { gap: 12px; }
}
</style>
</head>
<body>

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
    ['bg'=>'rgba(184,146,63,0.1)','stroke'=>'#b8923f','svg'=>'<path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-8"/>','title'=>'Comptabilité SYSCOHADA','desc'=>'Grand livre, bilan, journaux OHADA','live'=>true],
    ['bg'=>'rgba(47,125,91,0.1)','stroke'=>'#2f7d5b','svg'=>'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>','title'=>'Multi-entreprises','desc'=>'20+ dossiers, un seul espace','live'=>true],
    ['bg'=>'rgba(79,70,229,0.1)','stroke'=>'#4f46e5','svg'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','title'=>'CRM & Prospects','desc'=>'Pipeline Kanban, devis, factures','live'=>true],
    ['bg'=>'rgba(202,138,4,0.1)','stroke'=>'#ca8a04','svg'=>'<rect x="3" y="3" width="16" height="16" rx="2"/><path d="M3 9h18M9 21V9"/>','title'=>'Scan IA','desc'=>'Documents → écritures automatiques','live'=>true],
    ['bg'=>'rgba(124,58,237,0.1)','stroke'=>'#7c3aed','svg'=>'<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>','title'=>'États Financiers','desc'=>'Bilan, résultat, trésorerie SYSCOHADA','live'=>true],
    ['bg'=>'rgba(5,150,105,0.1)','stroke'=>'#059669','svg'=>'<path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>','title'=>'Rapprochement Bancaire','desc'=>'Import CSV, lettrage automatique','live'=>true],
    ['bg'=>'rgba(234,88,12,0.1)','stroke'=>'#ea580c','svg'=>'<circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0112 0v2"/><path d="M18 8l1.5 1.5L22 7"/>','title'=>'Droits & Collaborateurs','desc'=>'Rôles Admin / Superviseur / Collab.','live'=>true],
    ['bg'=>'rgba(220,38,38,0.09)','stroke'=>'#dc2626','svg'=>'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>','title'=>'Déclarations Fiscales','desc'=>'TVA, IS, retenues à la source','live'=>true],
    ['bg'=>'rgba(13,148,136,0.1)','stroke'=>'#0d9488','svg'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M6 15h.01M10 15h4"/>','title'=>'Gestion de Paie','desc'=>'Bulletins, IPRES/CSS, salaires','live'=>true],
    ['bg'=>'rgba(8,145,178,0.1)','stroke'=>'#0891b2','svg'=>'<rect x="3" y="4" width="16" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>','title'=>'Planning & Honoraires','desc'=>'Missions, échéances fiscales','live'=>true],
];
foreach ($modules as $m): ?>
            <div class="mod-card">
                <div class="mod-icon" style="background:<?= $m['bg'] ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $m['stroke'] ?>" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $m['svg'] ?></svg>
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
      <div class="form-box">

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
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:9px">
                    <label for="password" style="margin-bottom:0">Mot de passe</label>
                    <a href="<?= APP_URL ?>/mot-de-passe-oublie" class="forgot-link">Mot de passe oublié ?</a>
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

        <div class="alt-sep">Pas encore de compte ?</div>
        <a href="<?= APP_URL ?>/inscription" class="btn-secondary">Créer un espace cabinet →</a>

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

</div>
</body>
</html>
