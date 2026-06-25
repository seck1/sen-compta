<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Connexion</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --green:      #2f7d5b;
    --green-dark: #276a4d;
    --navy:       #1e3a5f;
    --gold:       #b8923f;
    --ink:        #1b2a23;
    --muted:      #5f6b66;
    --line:       #d9dcdb;
    --bg:         #eef0f0;
    --white:      #ffffff;
}

html, body {
    min-height: 100%;
    font-family: 'DM Sans', -apple-system, sans-serif;
    color: var(--ink);
    background: var(--bg);
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

.shell {
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center;
    padding: 48px 20px 32px;
}

/* ============ CARTE DE CONNEXION CENTRALE ============ */
.card {
    width: 100%; max-width: 460px;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 44px 44px 40px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 12px 34px -22px rgba(27,42,35,0.3);
    animation: rise .5s ease both;
}

.card-logo { display: flex; flex-direction: column; align-items: center; margin-bottom: 30px; }
.card-logo img { width: 64px; height: 64px; object-fit: contain; }
.card-logo .name { font-size: 22px; font-weight: 800; color: var(--navy); margin-top: 12px; letter-spacing: -0.3px; }
.card-logo .sub  { font-size: 11px; letter-spacing: 2.5px; text-transform: uppercase; color: var(--gold); margin-top: 4px; font-weight: 700; }

.card h1 { font-size: 27px; font-weight: 800; line-height: 1.2; color: var(--ink); letter-spacing: -0.6px; }
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
.field input::placeholder { color: #9aa3a0; }
.field input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(184,146,63,0.18);
}

.btn-primary {
    width: 100%;
    padding: 15px;
    margin-top: 6px;
    font-family: inherit; font-size: 16px; font-weight: 700;
    color: #fff;
    background: var(--green);
    border: none; border-radius: 999px;
    cursor: pointer;
    transition: background .15s, transform .15s;
}
.btn-primary:hover { background: var(--green-dark); }
.btn-primary:active { transform: translateY(1px); }

.forgot-link { font-size: 13px; color: var(--green); text-decoration: none; font-weight: 600; }
.forgot-link:hover { text-decoration: underline; }

.divider { display: flex; align-items: center; gap: 14px; margin: 24px 0 18px; color: var(--muted); font-size: 13px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--line); }

.btn-outline {
    display: block; width: 100%; padding: 14px; text-align: center;
    border: 1.5px solid var(--line); border-radius: 999px;
    color: var(--navy); font-size: 15px; font-weight: 700; text-decoration: none;
    transition: all .15s; background: #fff;
}
.btn-outline:hover { border-color: var(--green); color: var(--green); background: #f5faf7; }

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

/* ============ MODULES (sous la carte) ============ */
.modules-section { width: 100%; max-width: 920px; margin-top: 56px; }
.modules-title { text-align: center; font-size: 13px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); margin-bottom: 24px; }
.modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.mod-card {
    display: flex; align-items: center; gap: 13px;
    padding: 15px 16px;
    background: var(--white); border: 1px solid var(--line); border-radius: 14px;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}
.mod-card:hover { transform: translateY(-2px); box-shadow: 0 10px 22px -14px rgba(27,42,35,0.25); border-color: rgba(47,125,91,0.35); }
.mod-icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; display: flex; align-items: center; justify-content: center; }
.mod-body { flex: 1; min-width: 0; }
.mod-title { font-size: 14px; font-weight: 700; color: var(--ink); line-height: 1.2; }
.mod-desc  { font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.3; }
.mod-badge { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 20px; flex-shrink: 0; text-transform: uppercase; background: rgba(47,125,91,0.1); color: var(--green); border: 1px solid rgba(47,125,91,0.22); }

.foot { margin-top: 40px; display: flex; gap: 22px; flex-wrap: wrap; justify-content: center; }
.foot a, .foot span { font-size: 13px; color: var(--muted); text-decoration: none; }
.foot a:hover { color: var(--green); text-decoration: underline; }

@keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 760px) { .modules-grid { grid-template-columns: 1fr 1fr; } }
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

    <!-- ── Carte de connexion centrale ── -->
    <div class="card">

        <div class="card-logo">
            <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
            <div class="name">SenCompta</div>
            <div class="sub">Le SaaS Comptable du Sénégal</div>
        </div>

        <h1>Connectez-vous à<br>votre espace cabinet</h1>
        <p class="lead">Accès réservé aux membres du cabinet.</p>

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
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                       placeholder="votre@cabinet-smc.sn"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       required autocomplete="email">
            </div>

            <div class="field">
                <div class="row">
                    <label for="password">Mot de passe</label>
                    <a href="<?= APP_URL ?>/mot-de-passe-oublie" class="forgot-link">Mot de passe oublié ?</a>
                </div>
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       required autocomplete="current-password">
            </div>

            <?= csrfField() ?>
            <button type="submit" class="btn-primary">Se connecter</button>

        </form>

        <div class="divider">Pas encore de compte ?</div>
        <a href="<?= APP_URL ?>/inscription" class="btn-outline">Créer un espace cabinet</a>

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

    <!-- ── Modules (sous la carte) ── -->
    <div class="modules-section">
        <div class="modules-title">Tout votre cabinet, une seule plateforme</div>
        <div class="modules-grid">
<?php
$modules = [
    ['bg'=>'rgba(184,146,63,0.1)','stroke'=>'#b8923f','svg'=>'<path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-8"/>','title'=>'Comptabilité SYSCOHADA','desc'=>'Grand livre, bilan, journaux OHADA'],
    ['bg'=>'rgba(47,125,91,0.1)','stroke'=>'#2f7d5b','svg'=>'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>','title'=>'Multi-entreprises','desc'=>'20+ dossiers, un seul espace'],
    ['bg'=>'rgba(79,70,229,0.1)','stroke'=>'#4f46e5','svg'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','title'=>'CRM & Prospects','desc'=>'Pipeline Kanban, devis, factures'],
    ['bg'=>'rgba(202,138,4,0.1)','stroke'=>'#ca8a04','svg'=>'<rect x="3" y="3" width="16" height="16" rx="2"/><path d="M3 9h18M9 21V9"/>','title'=>'Scan IA','desc'=>'Documents → écritures automatiques'],
    ['bg'=>'rgba(124,58,237,0.1)','stroke'=>'#7c3aed','svg'=>'<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/>','title'=>'États Financiers','desc'=>'Bilan, résultat, trésorerie'],
    ['bg'=>'rgba(5,150,105,0.1)','stroke'=>'#059669','svg'=>'<path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>','title'=>'Rapprochement Bancaire','desc'=>'Import CSV, lettrage automatique'],
    ['bg'=>'rgba(234,88,12,0.1)','stroke'=>'#ea580c','svg'=>'<circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0112 0v2"/>','title'=>'Droits & Collaborateurs','desc'=>'Rôles Admin / Superviseur / Collab.'],
    ['bg'=>'rgba(220,38,38,0.09)','stroke'=>'#dc2626','svg'=>'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>','title'=>'Déclarations Fiscales','desc'=>'TVA, IS, retenues à la source'],
    ['bg'=>'rgba(13,148,136,0.1)','stroke'=>'#0d9488','svg'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M6 15h.01M10 15h4"/>','title'=>'Gestion de Paie','desc'=>'Bulletins, IPRES/CSS, salaires'],
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
                <span class="mod-badge">Actif</span>
            </div>
<?php endforeach; ?>
        </div>
    </div>

    <div class="foot">
        <span>© <?= date('Y') ?> SenCompta · Tous droits réservés</span>
        <span>Propulsé par SENGESTION ERP</span>
    </div>

</div>
</body>
</html>
