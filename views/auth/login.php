<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Connexion</title>
<link rel="icon" type="image/svg+xml" href="/logo/sencompta-icon.svg">
<link rel="icon" type="image/png" href="/logo/logo.png">
<link rel="apple-touch-icon" href="/logo/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    /* Vert SenCompta : plus profond et chaud que le vert Sage (#2f7d5b) */
    --green:      #1f6e4e;
    --green-dark: #18583f;
    --green-light:#2a8a63;
    --navy:       #1e3a5f;
    --gold:       #b8923f;
    --gold-light: #d9b876;
    --ink:        #18241f;
    --muted:      #4a554f;   /* fonce pour WCAG AA */
    --line:       #d9dcdb;
    --bg:         #eef1f0;
    --white:      #ffffff;
}

html, body {
    min-height: 100%;
    font-family: 'DM Sans', -apple-system, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(1200px 600px at 80% -5%, rgba(31,110,78,0.06), transparent 60%),
        radial-gradient(900px 500px at 0% 100%, rgba(30,58,95,0.05), transparent 55%),
        var(--bg);
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

.shell {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 460px;
    align-items: start;
    gap: 48px;
    max-width: 1340px;
    margin: 0 auto;
    padding: 56px 32px 40px;
}
/* carte de connexion a DROITE, modules a GAUCHE */
.col-left  { order: 2; position: sticky; top: 56px; }
.col-right { order: 1; padding-top: 4px; }

/* ============ CARTE DE CONNEXION ============ */
.card {
    position: relative; overflow: hidden;
    width: 100%;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 22px;
    padding: 42px 42px 36px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 30px 60px -28px rgba(24,36,31,0.42);
    animation: rise .5s ease both;
}
/* Filet or — signature premium */
.card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
}

.card-logo { display: flex; flex-direction: column; align-items: center; margin-bottom: 28px; }
.card-logo img {
    width: 58px; height: 58px; object-fit: contain;
    padding: 8px; border-radius: 16px;
    background: linear-gradient(160deg, #f2f7f4, #e9f1ec);
    box-shadow: inset 0 0 0 1px rgba(31,110,78,0.12);
}
.card-logo .name { font-size: 20px; font-weight: 700; color: var(--navy); margin-top: 12px; letter-spacing: -0.2px; }
.card-logo .sub  { font-size: 11px; letter-spacing: 2.5px; text-transform: uppercase; color: var(--gold); margin-top: 4px; font-weight: 700; }

.card h1 { font-size: 30px; font-weight: 800; line-height: 1.18; color: var(--ink); letter-spacing: -0.8px; max-width: 22ch; text-wrap: balance; }
.card .lead { font-size: 15px; color: var(--muted); margin-top: 10px; margin-bottom: 28px; }

.field { margin-bottom: 18px; }
.field label { display: block; font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 8px; }
.field .row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.field .row label { margin-bottom: 0; }

.field input {
    width: 100%;
    padding: 14px 16px;
    font-family: inherit; font-size: 15px; color: var(--ink);
    background: var(--white);
    border: 1.5px solid var(--line);
    border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
}
.field input::placeholder { color: #6b7570; }
.field input:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(31,110,78,0.16);
}

/* Champ mot de passe avec oeil */
.pw-wrap { position: relative; }
.pw-wrap input { padding-right: 48px; }
.pw-toggle {
    position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
    width: 38px; height: 38px; border: none; background: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--muted); border-radius: 8px; transition: color .15s, background .15s;
}
.pw-toggle:hover { color: var(--green); background: rgba(31,110,78,0.06); }
.pw-toggle svg { width: 20px; height: 20px; }

/* Case "rester connecte" */
.remember { display: flex; align-items: center; gap: 9px; margin: 4px 0 18px; cursor: pointer; }
.remember input { width: 17px; height: 17px; accent-color: var(--green); cursor: pointer; }
.remember span { font-size: 13.5px; color: var(--muted); }

.btn-primary {
    width: 100%;
    padding: 15px;
    margin-top: 4px;
    font-family: inherit; font-size: 16px; font-weight: 700;
    color: #fff;
    background: linear-gradient(180deg, var(--green-light), var(--green));
    border: none; border-radius: 999px;
    cursor: pointer;
    box-shadow: 0 10px 24px -10px rgba(31,110,78,0.55);
    transition: box-shadow .18s, transform .15s, filter .15s;
}
.btn-primary:hover { box-shadow: 0 14px 28px -10px rgba(31,110,78,0.65); filter: brightness(1.05); }
.btn-primary:active { transform: translateY(1px); }
.btn-primary:disabled { opacity: 0.7; cursor: wait; }

.forgot-link { font-size: 13px; color: var(--green); text-decoration: none; font-weight: 600; }
.forgot-link:hover { text-decoration: underline; }

a:focus-visible, button:focus-visible, input:focus-visible {
    outline: 3px solid rgba(31,110,78,0.45); outline-offset: 2px;
}

.divider { display: flex; align-items: center; gap: 14px; margin: 24px 0 18px; color: var(--muted); font-size: 13px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--line); }

.btn-outline {
    display: block; width: 100%; padding: 14px; text-align: center;
    border: 1.5px solid var(--line); border-radius: 999px;
    color: var(--navy); font-size: 15px; font-weight: 700; text-decoration: none;
    transition: all .15s; background: #fff;
}
.btn-outline:hover { border-color: var(--gold); color: var(--gold); background: #fdfaf3; }

.alert-error {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; margin-bottom: 22px;
    background: #fdecec; border: 1px solid #f5c2c2;
    border-radius: 10px; color: #c0392b; font-size: 14px; font-weight: 500;
}
.alert-error svg { width: 18px; height: 18px; flex-shrink: 0; }

.trust { display: flex; justify-content: center; gap: 20px; margin-top: 26px; flex-wrap: wrap; }
.trust span { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--muted); font-weight: 500; }
.trust svg { width: 14px; height: 14px; color: var(--green); }

/* ============ MODULES (colonne) ============ */
.modules-section { width: 100%; }
.modules-title {
    font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: var(--navy); margin-bottom: 18px;
    display: flex; align-items: center; gap: 12px;
}
.modules-title::after { content: ''; flex: 1; height: 1px; background: var(--line); }
.modules-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mod-card {
    display: flex; align-items: center; gap: 13px;
    padding: 16px 18px;
    background: var(--white); border: 1px solid var(--line); border-radius: 14px;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}
.mod-card:hover { transform: translateY(-2px); box-shadow: 0 10px 22px -14px rgba(24,36,31,0.2); border-color: rgba(31,110,78,0.32); }
/* Icones monochromes navy (discipline chromatique premium) */
.mod-icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; display: flex; align-items: center; justify-content: center; background: rgba(30,58,95,0.07); }
.mod-icon svg { stroke: var(--navy); }
.mod-body { flex: 1; min-width: 0; }
.mod-title { font-size: 14px; font-weight: 700; color: var(--ink); line-height: 1.2; }
.mod-desc  { font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.3; }
/* Badge "Actif" -> point vert discret */
.mod-badge { width: 7px; height: 7px; padding: 0; border: none; flex-shrink: 0; font-size: 0; background: var(--green); border-radius: 50%; box-shadow: 0 0 0 3px rgba(31,110,78,0.16); }

.foot { grid-column: 1 / -1; order: 3; margin-top: 32px; display: flex; gap: 22px; flex-wrap: wrap; justify-content: center; }
.foot a, .foot span { font-size: 13px; color: var(--muted); text-decoration: none; }
.foot a:hover { color: var(--green); text-decoration: underline; }

@keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

/* Tablette / mobile : la carte repasse au-dessus, modules en dessous */
@media (max-width: 980px) {
    .shell { grid-template-columns: 1fr; max-width: 560px; gap: 40px; }
    .col-left { position: static; order: 1; }   /* carte au-dessus sur mobile */
    .col-right { order: 2; }
    .modules-title { text-align: center; }
}
@media (max-width: 520px) {
    .shell { padding: 28px 14px; }
    .card { padding: 32px 22px 28px; border-radius: 16px; }
    .card h1 { font-size: 23px; }
    .modules-grid { grid-template-columns: 1fr; }
    .trust { gap: 14px; }
}
</style>
</head>
<body>

<div class="shell">

  <!-- ── Colonne gauche : carte de connexion ── -->
  <div class="col-left">
    <div class="card">

        <div class="card-logo">
            <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
            <div class="name">SenCompta</div>
            <div class="sub">Le SaaS Comptable du Sénégal</div>
        </div>

        <h1>Connectez-vous à votre espace cabinet</h1>
        <p class="lead">Accès réservé aux membres du cabinet.</p>

        <?php if ($error): ?>
        <div class="alert-error" role="alert" id="login-error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
            </svg>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/login/post" autocomplete="on" id="loginForm">

            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                       placeholder="votre@cabinet-smc.sn"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       required autocomplete="email"
                       <?= empty($_POST['email']) ? 'autofocus' : '' ?>>
            </div>

            <div class="field">
                <div class="row">
                    <label for="password">Mot de passe</label>
                    <a href="<?= APP_URL ?>/mot-de-passe-oublie" class="forgot-link">Mot de passe oublié ?</a>
                </div>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Afficher le mot de passe" aria-pressed="false">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
            </div>

            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                <span>Rester connecté sur cet appareil</span>
            </label>

            <?= csrfField() ?>
            <button type="submit" class="btn-primary" id="submitBtn">Se connecter</button>

        </form>

        <div class="divider">Pas encore de compte ?</div>
        <a href="<?= APP_URL ?>/inscription" class="btn-outline">Créer un espace cabinet</a>

        <a href="<?= APP_URL ?>/portail" style="display:flex;align-items:center;justify-content:center;gap:9px;margin-top:16px;padding:14px;border-radius:12px;background:rgba(30,58,95,0.06);border:1.5px solid rgba(30,58,95,0.18);color:var(--navy);font-weight:700;font-size:14px;text-decoration:none;transition:all .15s" onmouseover="this.style.background='rgba(30,58,95,0.12)';this.style.borderColor='rgba(30,58,95,0.35)'" onmouseout="this.style.background='rgba(30,58,95,0.06)';this.style.borderColor='rgba(30,58,95,0.18)'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:19px;height:19px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9v.01M9 12v.01M9 15v.01M9 18v.01" /></svg>
            Vous êtes client ? Accéder à votre espace
        </a>

        <div class="trust">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                Connexion chiffrée
            </span>
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                Authentification 2FA
            </span>
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Session 8 heures
            </span>
        </div>

    </div>
  </div><!-- /col-left -->

  <!-- ── Colonne droite : modules ── -->
  <div class="col-right">
    <div class="modules-section">
        <div class="modules-title">Tout votre cabinet, une seule plateforme</div>
        <div class="modules-grid">
<?php
$modules = [
    ['bg'=>'rgba(184,146,63,0.1)','stroke'=>'#b8923f','svg'=>'<path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-8"/>','title'=>'Comptabilité SYSCOHADA','desc'=>'Grand livre, bilan, journaux OHADA'],
    ['bg'=>'rgba(47,125,91,0.1)','stroke'=>'#2f7d5b','svg'=>'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>','title'=>'Multi-entreprises','desc'=>'20+ dossiers, un seul espace'],
    ['bg'=>'rgba(79,70,229,0.1)','stroke'=>'#4f46e5','svg'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','title'=>'CRM & Prospects','desc'=>'Pipeline Kanban, devis, factures'],
    ['bg'=>'rgba(202,138,4,0.1)','stroke'=>'#ca8a04','svg'=>'<rect x="3" y="3" width="16" height="16" rx="2"/><path d="M3 9h18M9 21V9"/>','title'=>'Scan IA','desc'=>'Documents → écritures automatiques'],
    ['bg'=>'rgba(184,146,63,0.1)','stroke'=>'#b8923f','svg'=>'<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/>','title'=>'États Financiers','desc'=>'Bilan, résultat, trésorerie'],
    ['bg'=>'rgba(5,150,105,0.1)','stroke'=>'#059669','svg'=>'<path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>','title'=>'Rapprochement Bancaire','desc'=>'Import CSV, lettrage automatique'],
    ['bg'=>'rgba(234,88,12,0.1)','stroke'=>'#ea580c','svg'=>'<circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0112 0v2"/>','title'=>'Droits & Collaborateurs','desc'=>'Rôles Admin / Superviseur / Collab.'],
    ['bg'=>'rgba(220,38,38,0.09)','stroke'=>'#dc2626','svg'=>'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>','title'=>'Déclarations Fiscales','desc'=>'TVA, IS, retenues à la source'],
    ['bg'=>'rgba(13,148,136,0.1)','stroke'=>'#0d9488','svg'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M6 15h.01M10 15h4"/>','title'=>'Gestion de Paie','desc'=>'Bulletins, IPRES/CSS, salaires'],
    ['bg'=>'rgba(30,58,95,0.1)','stroke'=>'#1e3a5f','svg'=>'<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>','title'=>'Portail Client','desc'=>'Consultent leur dossier, envoient leurs pièces'],
    ['bg'=>'rgba(124,58,237,0.1)','stroke'=>'#7c3aed','svg'=>'<line x1="6" y1="20" x2="6" y2="14"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="18" y1="20" x2="18" y2="10"/>','title'=>'Comptabilité Analytique','desc'=>'Sections, rentabilité par activité'],
    ['bg'=>'rgba(168,68,63,0.1)','stroke'=>'#a8443f','svg'=>'<path d="M9 14l6-6M9 8h.01M15 14h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>','title'=>'Avoirs & Notes de crédit','desc'=>'Extournes et avoirs commerciaux'],
    ['bg'=>'rgba(8,145,178,0.1)','stroke'=>'#0891b2','svg'=>'<path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>','title'=>'Import de Données','desc'=>'Clients, plan comptable, balance N-1'],
    ['bg'=>'rgba(31,110,78,0.1)','stroke'=>'#1f6e4e','svg'=>'<path d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>','title'=>'Lettrage & Relances','desc'=>'Pointage clients/fournisseurs'],
    ['bg'=>'rgba(180,131,9,0.1)','stroke'=>'#b48309','svg'=>'<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>','title'=>'Immobilisations','desc'=>'Amortissements linéaire / dégressif'],
];
foreach ($modules as $m): ?>
            <div class="mod-card">
                <div class="mod-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $m['svg'] ?></svg>
                </div>
                <div class="mod-body">
                    <div class="mod-title"><?= $m['title'] ?></div>
                    <div class="mod-desc"><?= $m['desc'] ?></div>
                </div>
                <span class="mod-badge">Actif</span>
            </div>
<?php endforeach; ?>
        </div>
    </div>
  </div><!-- /col-right -->

    <div class="foot">
        <span>© <?= date('Y') ?> SenCompta · Tous droits réservés</span>
        <span style="display:flex;gap:14px;flex-wrap:wrap">
            <a href="<?= APP_URL ?>/confidentialite" style="color:inherit;text-decoration:none">Confidentialité</a>
            <a href="<?= APP_URL ?>/mentions-legales" style="color:inherit;text-decoration:none">Mentions légales</a>
            <a href="<?= APP_URL ?>/cgu" style="color:inherit;text-decoration:none">CGU</a>
            <a href="<?= APP_URL ?>/cookies" style="color:inherit;text-decoration:none">Cookies</a>
        </span>
    </div>

</div>
<script>
// Oeil : afficher/masquer le mot de passe
(function(){
  var t = document.getElementById('pwToggle'), p = document.getElementById('password');
  if (t && p) t.addEventListener('click', function(){
    var show = p.type === 'password';
    p.type = show ? 'text' : 'password';
    t.setAttribute('aria-pressed', show);
    t.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
  });
  // Etat de chargement (evite le double-submit)
  var f = document.getElementById('loginForm'), b = document.getElementById('submitBtn');
  if (f && b) f.addEventListener('submit', function(){ b.disabled = true; b.textContent = 'Connexion…'; });
  // Autofocus mot de passe si email deja rempli
  var e = document.getElementById('email');
  if (e && e.value && p) p.focus();
})();
</script>
<?php require APP_ROOT . '/views/partials/cookie-banner.php'; ?>
</body>
</html>
