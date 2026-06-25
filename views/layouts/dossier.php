<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? '') ?> — <?= e($entreprise['raison_sociale']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --navy: #1e3a5f; --navy-dark: #122540; --navy-light: #2a4f7c;
    --green: #1f6e4e; --green-light: #2a8a63; --green-dark: #18583f;
    --green-soft: rgba(31,110,78,0.06); --green-tint: rgba(31,110,78,0.10);
    --gold: #b8923f; --gold-light: #d9b876; --gold-dark: #a8843f;
    --white: #fff; --bg: #eef1f0; --bg-card: #fff;
    --text: #18241f; --text-muted: #4a554f; --border: #d9dcdb;
    --sidebar-w: 248px; --header-h: 64px;
    --success: #1f6e4e; --warning: #f59e0b; --danger: #ef4444; --info: #1f6e4e;
    --debit: #c0392b; --credit: #1f6e4e;
    --ent-color: <?= e($entreprise['couleur'] ?? '#1f6e4e') ?>;
}
html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 11pt; overflow-x: hidden; max-width: 100%; }

/* SIDEBAR */
.sidebar {
    position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sidebar-w);
    background: var(--navy-dark);
    display: flex; flex-direction: column;
    z-index: 100;
    border-right: 1px solid rgba(201,169,110,0.1);
}

.sidebar-back {
    display: flex; align-items: center; gap: 10px;
    margin: 14px 14px 14px;
    padding: 11px 16px;
    border: 1.5px solid rgba(201,169,110,0.35);
    border-radius: 10px;
    text-decoration: none;
    color: rgba(255,255,255,0.75);
    font-size: 15px;
    font-weight: 500;
    background: rgba(255,255,255,0.05);
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.08);
}
.sidebar-back:hover {
    color: var(--white);
    border-color: var(--gold);
    background: rgba(201,169,110,0.12);
    box-shadow: 0 4px 14px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.1);
    transform: translateY(-1px);
}
.sidebar-back svg { width: 16px; height: 16px; flex-shrink: 0; }

/* En-tête dossier dans sidebar */
@keyframes pulse-border {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.15); }
    50%       { box-shadow: 0 0 0 6px rgba(255,255,255,0); }
}
@keyframes shimmer {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}

.dossier-header {
    padding: 12px 20px 12px;
    border-bottom: 3px solid rgba(201,169,110,0.5);
    background: linear-gradient(145deg, var(--ent-color), color-mix(in srgb, var(--ent-color) 55%, #000));
    position: relative;
    overflow: hidden;
}
/* Bandeau "DOSSIER ACTIF" en haut */
.dossier-header::before {
    content: '● DOSSIER ACTIF';
    position: absolute;
    top: 0; left: 0; right: 0;
    padding: 3px 10px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
    text-align: center;
    color: var(--navy-dark);
    background: linear-gradient(90deg, var(--gold-dark), var(--gold), var(--gold-dark));
    background-size: 200% auto;
    animation: shimmer 3s linear infinite;
}
/* Cercle décoratif */
.dossier-header::after {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 130px; height: 130px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none;
}
.dossier-avatar {
    width: 44px; height: 44px; border-radius: 12px;
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; color: white;
    margin-top: 6px;
    margin-bottom: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    animation: pulse-border 2.5s ease-in-out infinite;
}
.dossier-name {
    font-size: 18px; font-weight: 700; color: var(--white);
    line-height: 1.2; margin-bottom: 3px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
.dossier-meta {
    font-size: 13px; color: rgba(255,255,255,0.75);
    text-transform: uppercase; letter-spacing: 1px;
}
.dossier-exercice {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 8px; padding: 5px 12px;
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(201,169,110,0.4);
    border-radius: 20px;
    font-size: 15px; color: var(--gold-light);
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Nav */
.sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
.nav-section { margin-bottom: 24px; }
.nav-section-label {
    font-size: 8pt;
    text-transform: uppercase; letter-spacing: 2px;
    padding: 4px 12px; margin-bottom: 6px; font-weight: 700;
    display: flex; align-items: center; gap: 7px;
}
.nav-section-label::before {
    content: ''; display: inline-block;
    width: 6px; height: 6px; border-radius: 50%;
    flex-shrink: 0;
}
.label-overview  { color: #93c5fd; }
.label-overview::before  { background: #93c5fd; box-shadow: 0 0 6px #93c5fd88; }
.label-compta    { color: #6ee7b7; }
.label-compta::before    { background: #6ee7b7; box-shadow: 0 0 6px #6ee7b788; }
.label-etats     { color: #c4b5fd; }
.label-etats::before     { background: #c4b5fd; box-shadow: 0 0 6px #c4b5fd88; }
.label-fiscalite { color: #fca5a5; }
.label-fiscalite::before { background: #fca5a5; box-shadow: 0 0 6px #fca5a588; }
.label-rh        { color: #fdba74; }
.label-rh::before        { background: #fdba74; box-shadow: 0 0 6px #fdba7488; }
.label-tiers     { color: #f9a8d4; }
.label-tiers::before     { background: #f9a8d4; box-shadow: 0 0 6px #f9a8d488; }
.label-outils    { color: var(--gold); }
.label-outils::before    { background: var(--gold); box-shadow: 0 0 6px rgba(201,169,110,.7); }
.nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 16px; border-radius: 10px;
    color: rgba(255,255,255,0.95);
    text-decoration: none; font-size: 11pt; font-weight: 500;
    transition: all 0.2s; margin-bottom: 3px; position: relative;
}
.nav-item svg { width: 20px; height: 20px; flex-shrink: 0; }
.nav-item:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.9); }
.nav-item.active { background: rgba(42,138,99,0.16); color: #5fc89a; font-weight: 500; }
.nav-item.active::before {
    content: ''; position: absolute; left: 0; top: 25%; bottom: 25%;
    width: 3px; background: var(--green-light); border-radius: 0 3px 3px 0;
}

/* Footer sidebar */
.sidebar-footer { padding: 14px 12px; border-top: 1px solid rgba(255,255,255,0.06); }
.user-card {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; border-radius: 10px;
    background: rgba(255,255,255,0.04);
}
.user-avatar {
    width: 34px; height: 34px; border-radius: 9px;
    background: linear-gradient(135deg, var(--navy-light), var(--gold-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 600; color: white; flex-shrink: 0;
}
.user-info { flex: 1; min-width: 0; }
.user-name { font-size: 15px; font-weight: 500; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: 13px; color: var(--gold); opacity: 0.8; text-transform: capitalize; }
.btn-logout {
    width: 28px; height: 28px; border-radius: 7px;
    background: rgba(255,255,255,0.06); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.4); transition: all 0.2s;
    text-decoration: none; flex-shrink: 0;
}
.btn-logout:hover { background: rgba(239,68,68,0.2); color: #ef4444; }
.btn-logout svg { width: 15px; height: 15px; }

/* MAIN */
.main-wrap { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

/* Topbar */
.topbar {
    position: sticky; top: 0; height: var(--header-h);
    background: rgba(255,255,255,0.97); backdrop-filter: blur(12px);
    border-bottom: 2px solid var(--border);
    display: flex; align-items: center; padding: 0 28px; gap: 16px; z-index: 50;
    box-shadow: 0 1px 8px rgba(30,58,95,0.06);
}
.topbar-left { flex: 1; display: flex; align-items: center; gap: 6px; font-size: 16px; }
.topbar-left a {
    color: var(--navy);
    text-decoration: none;
    background: rgba(30,58,95,0.07);
    border: 1px solid rgba(30,58,95,0.15);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 16px;
    transition: background 0.15s, border-color 0.15s;
}
.topbar-left a:hover {
    background: rgba(30,58,95,0.14);
    border-color: rgba(30,58,95,0.3);
}
.topbar-left .sep { color: var(--text-muted); font-size: 16px; opacity: 0.5; margin: 0 1px; }
.topbar-left .current {
    color: var(--white);
    background: var(--navy);
    border: 1px solid var(--navy);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 16px;
}
.topbar-right { display: flex; align-items: center; gap: 10px; }

/* Barre couleur entreprise en haut */
.ent-colorbar { height: 3px; background: var(--ent-color); }

/* Page content */
.page-content { flex: 1; padding: 28px 32px 100px; }

/* Page header */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; gap: 16px; }
.page-title { font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 400; color: var(--navy-dark); letter-spacing: -0.3px; }
.page-subtitle { font-size: 17px; color: var(--text-muted); margin-top: 3px; }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 10px; font-size: 17px; font-family: 'DM Sans', sans-serif; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; white-space: nowrap; }
.btn svg { width: 17px; height: 17px; }
.btn-primary { background: linear-gradient(135deg, var(--green-light), var(--green)); color: var(--white); box-shadow: 0 4px 12px rgba(31,110,78,0.28); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(31,110,78,0.38); }
.btn-ent { background: var(--ent-color); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.btn-ent:hover { transform: translateY(-1px); filter: brightness(1.1); }
.btn-outline { background: var(--white); color: var(--text); border: 1px solid var(--border); }
.btn-outline:hover { border-color: var(--green); color: var(--green); }
.btn-danger { background: rgba(239,68,68,0.08); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); }
.btn-danger:hover { background: var(--danger); color: white; }
.btn-sm { padding: 8px 16px; font-size: 15.5px; }

/* Cards */
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 22px; }

/* KPI */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; }
.kpi-card {
    background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
    padding: 18px 20px; position: relative; overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.07); }
.kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--ent-color); }
.kpi-label { font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600; }
.kpi-value { font-family: 'Cormorant Garamond', serif; font-size: 46px; font-weight: 600; color: var(--navy-dark); line-height: 1; margin-bottom: 6px; }
.kpi-sub { font-size: 15px; color: var(--text-muted); }
.kpi-icon { position: absolute; top: 16px; right: 16px; width: 38px; height: 38px; border-radius: 9px; display: flex; align-items: center; justify-content: center; background: rgba(30,58,95,0.07); color: var(--navy); }
.kpi-icon svg { width: 20px; height: 20px; }

/* Table */
.table-wrap { border-radius: 14px; border: 1px solid var(--border); overflow-x: auto; -webkit-overflow-scrolling: touch; background: var(--bg-card); }
.table-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); }
.table-title { font-size: 18px; font-weight: 600; color: var(--navy-dark); }
table { width: 100%; border-collapse: collapse; }
thead th { padding: 11px 16px; text-align: left; font-size: 9pt; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: var(--green); border-bottom: 1px solid var(--green-dark); }
tbody tr { border-bottom: 1px solid rgba(228,233,240,0.6); transition: background 0.15s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--green-soft); }
tbody td { padding: 11px 16px; font-size: 11pt; color: var(--text); vertical-align: middle; }

/* Badges */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 20px; font-size: 15px; font-weight: 500; }
.badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
.badge-success { background: var(--green-tint); color: var(--green-dark); } .badge-success::before { background: var(--green); }
.badge-warning { background: rgba(245,158,11,0.1); color: #d97706; } .badge-warning::before { background: #f59e0b; }
.badge-danger  { background: rgba(239,68,68,0.1); color: #dc2626; }  .badge-danger::before  { background: #ef4444; }
.badge-info    { background: rgba(31,110,78,0.1); color: #2563eb; }  .badge-info::before    { background: #1f6e4e; }
.badge-navy    { background: rgba(30,58,95,0.08); color: var(--navy); } .badge-navy::before { background: var(--navy); }

/* Form */
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field label { font-size: 14px; font-weight: 500; color: var(--text); }
.form-field input, .form-field select, .form-field textarea {
    padding: 10px 13px; border: 1px solid var(--border); border-radius: 9px;
    font-size: 15px; font-family: 'DM Sans', sans-serif; color: var(--text);
    background: var(--white); outline: none; transition: border-color 0.2s, box-shadow 0.2s;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,110,78,0.16);
}
.form-field textarea { resize: vertical; min-height: 80px; }

/* Montants */
.montant-debit  { color: var(--danger); font-weight: 500; font-family: Arial, sans-serif; }
.montant-credit { color: var(--success); font-weight: 500; font-family: Arial, sans-serif; }
.montant-solde  { font-weight: 600; font-family: Arial, sans-serif; }

/* Empty state */
.empty-state { text-align: center; padding: 50px 32px; }
.empty-state svg { width: 48px; height: 48px; color: var(--border); margin: 0 auto 14px; display: block; }
.empty-state h3 { font-size: 16px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
.empty-state p  { font-size: 14px; color: var(--text-muted); opacity: 0.7; }

::-webkit-scrollbar { width: 5px; } ::-webkit-scrollbar-thumb { background: rgba(30,58,95,0.15); border-radius: 3px; }

/* Responsive */
@media (max-width: 1024px) {
    :root { --sidebar-w: 250px; }
    .page-content { padding: 16px; }
}

@media (max-width: 768px) {
    :root { --sidebar-w: 0px; }
    .sidebar {
        transform: translateX(-260px);
        width: 260px !important;
        transition: transform 0.3s ease;
        z-index: 200;
    }
    .sidebar.open { transform: translateX(0); }
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 199;
    }
    .sidebar-overlay.open { display: block; }
    .main-wrap { margin-left: 0 !important; }
    /* Topbar wrappable (sinon chrono/boutons debordent) */
    .topbar { padding: 8px 14px; height: auto; min-height: var(--header-h); flex-wrap: wrap; gap: 8px; }
    .topbar-right { flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
    #chrono-widget { flex-wrap: wrap; padding: 4px 8px; font-size: 12px; }
    .hamburger-btn {
        display: flex !important;
        align-items: center; justify-content: center;
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        cursor: pointer;
        color: white;
        flex-shrink: 0;
        margin-right: 8px;
    }
    .page-content { padding: 14px; }
    .stats-grid, .kpi-row { grid-template-columns: repeat(2,1fr) !important; }
    .form-grid-2, .form-grid-3 { grid-template-columns: 1fr !important; }
    /* Tableaux : scroll horizontal propre (le vrai conteneur est .table-wrap) */
    .table-wrap, .table-responsive { overflow-x: auto !important; -webkit-overflow-scrolling: touch; }
    .table-wrap table, .table-responsive table { min-width: 600px; }
    /* Panneau notes & dropdown exercice : pleine largeur au lieu de px fixes */
    #notes-panel { width: 85%; max-width: 360px; }
    #exDropdown { min-width: 0; max-width: calc(100vw - 24px); }
    #notes-toggle { bottom: 16px; right: 16px; padding: 10px 14px; font-size: 13px; }
    .page-actions, .page-header-actions { flex-wrap: wrap; }
    .topbar-date { display: none; }
}

@media (max-width: 480px) {
    html, body { font-size: 16px; }
    .page-title { font-size: 28px; }
    .stats-grid, .kpi-row { grid-template-columns: 1fr !important; }
    .btn { padding: 9px 14px; font-size: 14px; }
    /* Breadcrumb : garder le titre courant, masquer liens + separateurs (fix bug :not(.current-link)) */
    .topbar-left a { display: none; }
    .topbar-left .sep { display: none; }
    .topbar-left .current { display: inline; }
}

/* Grilles dashboard dossier (remplacent les styles inline non responsive) */
.dash-kpi3   { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
.dash-2col   { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.dash-side   { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
@media (max-width: 900px) {
    .dash-2col, .dash-side { grid-template-columns: 1fr; }
    .dash-kpi3 { grid-template-columns: repeat(2,1fr); }
}
@media (max-width: 560px) {
    .dash-kpi3 { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <a href="<?= APP_URL ?>/dashboard" class="sidebar-back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Tous les dossiers
    </a>

    <?php
        require_once APP_ROOT . '/src/Services/RegimeFiscalService.php';
        require_once APP_ROOT . '/src/Services/NotificationService.php';
        require_once APP_ROOT . '/src/Services/AlerteService.php';
        $regime  = $entreprise['regime_fiscal'] ?? 'CGI';
        $modules = RegimeFiscalService::getModulesDisponibles($regime);
        $regimeColor = RegimeFiscalService::getBadgeColor($regime);
        $regimeLabel = RegimeFiscalService::getLabel($regime);
        // Génère les alertes automatiquement à chaque chargement
        $u = auth();
        AlerteService::genererAlertes($entreprise['id'], $u['id']);
    ?>
    <div class="dossier-header">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div class="dossier-avatar" style="<?= !empty($entreprise['logo']) ? 'background:#fff;padding:0;width:56px;height:56px;' : '' ?>">
                <?php if(!empty($entreprise['logo'])): ?>
                    <img src="<?= APP_URL ?>/logos/<?= e($entreprise['logo']) ?>" alt="" style="width:100%;height:100%;object-fit:contain;border-radius:12px;background:#fff;display:block">
                <?php else: ?>
                    <?= strtoupper(substr($entreprise['raison_sociale'], 0, 2)) ?>
                <?php endif; ?>
            </div>
            <a href="<?= APP_URL ?>/dossier/profil?id=<?= $entreprise['id'] ?>" title="Profil & Paramètres DGID" style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.45);transition:all .2s;text-decoration:none;flex-shrink:0;margin-top:9px" onmouseover="this.style.background='rgba(201,169,110,0.18)';this.style.color='var(--gold)'" onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.color='rgba(255,255,255,0.45)'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </a>
        </div>
        <div class="dossier-name" style="margin-top:4px"><?= e($entreprise['raison_sociale']) ?></div>
        <div class="dossier-meta"><?= e($entreprise['forme_juridique']) ?> · <?= e($entreprise['code_dossier']) ?></div>
        <div style="margin-top:4px">
            <a href="<?= APP_URL ?>/dossier/fiscalite/regime?id=<?= $entreprise['id'] ?>" style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;font-size:10.5px;font-weight:600;color:#fff;background:<?= $regimeColor ?>;text-decoration:none;letter-spacing:0.5px;opacity:0.92" title="<?= e($regimeLabel) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:10px;height:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                <?= e($regime) ?>
            </a>
        </div>
        <div style="position:relative;margin-top:6px" id="exDropdownWrap">
            <button onclick="var dd=document.getElementById('exDropdown'),btn=this,r=btn.getBoundingClientRect();dd.style.top=r.bottom+6+'px';dd.style.left=r.left+'px';dd.classList.toggle('ex-open')"
                    style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:rgba(201,169,110,0.12);border-radius:20px;font-size:11px;color:var(--gold);font-weight:500;border:none;cursor:pointer;width:auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                Exercice <?= e($entreprise['exercice_courant']) ?>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:10px;height:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            </button>
            <div id="exDropdown" style="display:none;position:fixed;background:#fff;border:1px solid #e4e9f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.2);min-width:210px;z-index:9999;overflow:auto;max-height:80vh">
                <div style="padding:8px 12px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#6b7a94;border-bottom:1px solid #e4e9f0">Changer d'exercice</div>
                <?php foreach(($entreprise['_exercices'] ?? [$entreprise['exercice_courant']]) as $ex): ?>
                <?php $isCurrent = ($ex == $entreprise['exercice_courant']); ?>
                <a href="<?= APP_URL ?>/dossier/exercice/switch?id=<?= $entreprise['id'] ?>&annee=<?= $ex ?>"
                   style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 14px;background:<?= $isCurrent ? 'rgba(31,110,78,0.1)' : 'transparent' ?>;font-size:13px;color:<?= $isCurrent ? '#2563eb' : '#1a2535' ?>;font-weight:<?= $isCurrent ? '600' : '400' ?>;text-decoration:none">
                    <?= $ex ?>
                    <?php if($isCurrent): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#2563eb" style="width:13px;height:13px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
                <div style="border-top:1px solid #e4e9f0;padding:8px;display:flex;gap:6px">
                    <input type="number" id="exNewAnnee" placeholder="2027" min="2020" max="2040"
                           style="flex:1;padding:5px 8px;border-radius:6px;border:1px solid #e4e9f0;font-size:12px;width:0">
                    <button onclick="var a=document.getElementById('exNewAnnee').value; if(a) window.location='<?= APP_URL ?>/dossier/exercice/creer?id=<?= $entreprise['id'] ?>&annee='+a;"
                            style="padding:5px 10px;border-radius:6px;background:#2563eb;color:#fff;border:none;cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap">+ Créer</button>
                </div>
            </div>
        </div>
        <style>.ex-open { display:block !important }</style>
        <script>
        document.addEventListener('click', function(e) {
            var wrap = document.getElementById('exDropdownWrap');
            if (wrap && !wrap.contains(e.target)) {
                var dd = document.getElementById('exDropdown');
                if (dd) dd.classList.remove('ex-open');
            }
        });
        </script>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-label label-overview">Vue d'ensemble</div>
            <a href="<?= APP_URL ?>/dossier?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Tableau de bord
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-compta">Comptabilité</div>
            <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'ecritures' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                Saisie des écritures
            </a>
            <a href="<?= APP_URL ?>/dossier/journaux?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'journaux' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                Journaux
            </a>
            <a href="<?= APP_URL ?>/dossier/grand-livre?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'grand-livre' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                Grand livre
            </a>
            <a href="<?= APP_URL ?>/dossier/plan-comptable?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'plan-comptable' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                Plan comptable
            </a>
            <a href="<?= APP_URL ?>/dossier/balance?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'balance' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg>
                Balance générale
            </a>
            <a href="<?= APP_URL ?>/dossier/livre-auxiliaire?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'livre-auxiliaire' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" /></svg>
                Livre auxiliaire
            </a>
            <a href="<?= APP_URL ?>/dossier/balance-auxiliaire?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'balance-auxiliaire' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Balance auxiliaire
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-etats">États financiers</div>
            <a href="<?= APP_URL ?>/dossier/bilan?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'bilan' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                Bilan
            </a>
            <a href="<?= APP_URL ?>/dossier/compte-resultat?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'compte-resultat' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                Compte de résultat
            </a>
            <a href="<?= APP_URL ?>/dossier/tafire?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'tafire' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                TAFIRE
            </a>
            <a href="<?= APP_URL ?>/dossier/etat-financier-dgid?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'dgid' ? 'active' : '' ?>" style="position:relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                État Financier DGID
                <span style="margin-left:auto;font-size:9px;font-weight:700;padding:2px 6px;background:rgba(201,169,110,0.2);color:#c9a96e;border-radius:8px;letter-spacing:.5px">XLSX</span>
            </a>
            <a href="<?= APP_URL ?>/dossier/profil/conformite-dgid?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'conformite' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                Conformité DGID
            </a>
            <a href="<?= APP_URL ?>/dossier/export/bilan?id=<?= $entreprise['id'] ?>" target="_blank" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                Imprimer Bilan
            </a>
            <a href="<?= APP_URL ?>/dossier/export/compte-resultat?id=<?= $entreprise['id'] ?>" target="_blank" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                Imprimer CR
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-fiscalite">Fiscalité</div>
            <a href="<?= APP_URL ?>/dossier/fiscalite/regime?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'regime' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                Fiche régime fiscal
            </a>
            <?php if ($modules['tva']): ?>
            <a href="<?= APP_URL ?>/dossier/tva?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'tva' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                Déclaration TVA
            </a>
            <?php endif; ?>
            <?php if ($modules['cgu']): ?>
            <a href="<?= APP_URL ?>/dossier/fiscalite/cgu?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'cgu' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" /></svg>
                Déclaration CGU
            </a>
            <?php endif; ?>
            <?php if ($modules['liberatoire']): ?>
            <a href="<?= APP_URL ?>/dossier/fiscalite/cgu?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'cgu' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" /></svg>
                Impôt Libératoire
            </a>
            <?php endif; ?>
            <?php if ($modules['is']): ?>
            <a href="<?= APP_URL ?>/dossier/fiscalite/is?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'is' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z" /></svg>
                Déclaration IS
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/dossier/fiscalite/declaration-sociale?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'decl-sociale' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                Déclarations sociales
            </a>
            <a href="<?= APP_URL ?>/dossier/fiscalite/calendrier?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'calendrier' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                Calendrier fiscal
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-rh">RH &amp; Paie</div>
            <a href="<?= APP_URL ?>/dossier/rh?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'rh' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                Employés
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/bulletins?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'bulletins' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Bulletins de paie
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/conges?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'conges' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                Congés
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/registre?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'registre' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
                Registre du personnel
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/declarations-sociales?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'decl-sociales-rh' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" /></svg>
                Déclarations sociales
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/organigramme?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'organigramme' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Organigramme
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/conges/parametres?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'conges-params' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Paramètres congés
            </a>
            <a href="<?= APP_URL ?>/dossier/rh/parametres?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'rh-params' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Paramètres Paie
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-tiers">Tiers</div>
            <a href="<?= APP_URL ?>/dossier/tiers?id=<?= $entreprise['id'] ?>&type=fournisseur" class="nav-item <?= ($activeTab ?? '') === 'fournisseurs' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                Fournisseurs
            </a>
            <a href="<?= APP_URL ?>/dossier/tiers?id=<?= $entreprise['id'] ?>&type=client" class="nav-item <?= ($activeTab ?? '') === 'clients' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                Clients
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-outils">Outils</div>
            <a href="<?= APP_URL ?>/dossier/balance-agee?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'balance-agee' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" /></svg>
                Balance âgée
            </a>
            <a href="<?= APP_URL ?>/dossier/lettrage?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'lettrage' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                Lettrage
            </a>
            <a href="<?= APP_URL ?>/dossier/immo?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'immo' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                Immobilisations
            </a>
            <a href="<?= APP_URL ?>/dossier/rapprochement?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'rapprochement' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                Rapprochement bancaire
            </a>
            <a href="<?= APP_URL ?>/dossier/relances?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'relances' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                Relances clients
            </a>
            <a href="<?= APP_URL ?>/dossier/portail?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'portail' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" style="display:none"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                Portail client
            </a>
            <a href="<?= APP_URL ?>/dossier/budget?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'budget' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                Budget vs Réalisé
            </a>
            <a href="<?= APP_URL ?>/dossier/notes-frais?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'notes-frais' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" /></svg>
                Notes de frais
            </a>
            <a href="<?= APP_URL ?>/dossier/temps?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'temps' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Suivi du temps
            </a>
            <a href="<?= APP_URL ?>/dossier/modeles?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'modeles' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                Modèles d'écritures
            </a>
            <a href="<?= APP_URL ?>/dossier/rapport-gestion?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'rapport-gestion' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Rapport de gestion
            </a>
            <a href="<?= APP_URL ?>/dossier/cloture/checklist?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'cloture-checklist' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Checklist clôture
            </a>
            <a href="<?= APP_URL ?>/dossier/cloture?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'cloture' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                Clôture exercice
            </a>
            <a href="<?= APP_URL ?>/dossier/exercices?id=<?= $entreprise['id'] ?>" class="nav-item <?= ($activeTab ?? '') === 'exercices' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                Gestion exercices
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <?php $u = auth(); ?>
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= e($u['prenom'].' '.$u['nom']) ?></div>
                <div class="user-role"><?= e($u['role']) ?></div>
            </div>
            <a href="<?= APP_URL ?>/logout" class="btn-logout">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
            </a>
        </div>
    </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
    <div class="ent-colorbar"></div>
    <header class="topbar">
        <button class="hamburger-btn" id="hamburger" onclick="toggleSidebar()" style="display:none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
        </button>
        <div class="topbar-left">
            <a href="<?= APP_URL ?>/dashboard">SenCompta</a>
            <span class="sep">›</span>
            <a href="<?= APP_URL ?>/dossier?id=<?= $entreprise['id'] ?>"><?= e($entreprise['raison_sociale']) ?></a>
            <span class="sep">›</span>
            <span class="current"><?= e($pageTitle ?? '') ?></span>
        </div>
        <div class="topbar-right" style="display:flex;align-items:center;gap:10px">
            <!-- Chronomètre -->
            <div id="chrono-widget" style="display:flex;align-items:center;gap:8px;background:var(--white);border:1px solid var(--border);padding:5px 12px;border-radius:8px;font-size:13px">
                <span id="chrono-dot" style="width:8px;height:8px;border-radius:50%;background:#6b7280;display:inline-block"></span>
                <span id="chrono-display" style="font-family:monospace;font-weight:600;color:var(--text-muted);min-width:52px">00:00:00</span>
                <button id="chrono-btn" onclick="chronoToggle()" style="padding:3px 10px;border-radius:6px;border:none;cursor:pointer;font-size:12px;font-weight:600;background:var(--navy-dark);color:#fff">▶ Start</button>
                <button id="chrono-save-btn" onclick="chronoSave()" style="padding:3px 10px;border-radius:6px;border:1px solid #1f6e4e;color:#1f6e4e;background:none;cursor:pointer;font-size:12px;font-weight:600;display:none">✓ Enregistrer</button>
            </div>
            <span style="font-size:12px;color:var(--text-muted);background:var(--white);border:1px solid var(--border);padding:5px 12px;border-radius:8px">
                <?= date('d/m/Y') ?>
            </span>
        </div>
    </header>

    <!-- Modal enregistrement chrono -->
    <div id="modal-chrono" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:16px;padding:28px;width:420px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2)">
            <div style="font-size:17px;font-weight:700;margin-bottom:6px;color:var(--navy-dark)">Enregistrer le temps</div>
            <div id="chrono-recap" style="font-size:13px;color:var(--text-muted);margin-bottom:20px"></div>
            <form method="post" action="<?= APP_URL ?>/dossier/temps/store">
                <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
                <input type="hidden" name="date_travail" id="ch_date">
                <input type="hidden" name="heures" id="ch_heures">
                <input type="hidden" name="minutes" id="ch_minutes">
                <div class="form-field" style="margin-bottom:14px">
                    <label>Catégorie</label>
                    <select name="categorie">
                        <option value="saisie">Saisie comptable</option>
                        <option value="revision">Révision / Contrôle</option>
                        <option value="declaration">Déclaration fiscale</option>
                        <option value="reunion">Réunion client</option>
                        <option value="rapport">Rapport / Bilan</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="form-field" style="margin-bottom:14px">
                    <label>Description (optionnel)</label>
                    <input type="text" name="description" id="ch_desc" placeholder="Ex: Saisie relevés avril...">
                </div>
                <div style="margin-bottom:20px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="facturable" checked style="width:16px;height:16px;accent-color:var(--navy-dark)">
                        <span style="font-size:13px;font-weight:500">Temps facturable</span>
                    </label>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end">
                    <button type="button" onclick="chronoAnnulerSave()" style="padding:9px 20px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

<script>
(function(){
var ENT_ID   = <?= (int)$entreprise['id'] ?>;
var STORE_KEY = 'chrono_ent_' + ENT_ID;
var tickTimer = null;

// État persistant via localStorage
function getState() {
    try { return JSON.parse(localStorage.getItem(STORE_KEY)) || {}; } catch(e) { return {}; }
}
function setState(s) { localStorage.setItem(STORE_KEY, JSON.stringify(s)); }

function pad(n) { return String(n).padStart(2,'0'); }

function formatSeconds(sec) {
    var h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60), s = sec%60;
    return pad(h)+':'+pad(m)+':'+pad(s);
}

function updateUI() {
    var st = getState();
    var dot = document.getElementById('chrono-dot');
    var disp = document.getElementById('chrono-display');
    var btn  = document.getElementById('chrono-btn');
    var saveBtn = document.getElementById('chrono-save-btn');

    if (st.running && st.startTs) {
        var elapsed = Math.floor((Date.now() - st.startTs) / 1000) + (st.accumulated || 0);
        disp.textContent = formatSeconds(elapsed);
        dot.style.background = '#ef4444';
        btn.textContent = '■ Stop';
        btn.style.background = '#ef4444';
        saveBtn.style.display = 'none';
    } else if (st.accumulated > 0) {
        disp.textContent = formatSeconds(st.accumulated);
        dot.style.background = '#f59e0b';
        btn.textContent = '▶ Reprendre';
        btn.style.background = '#2563eb';
        saveBtn.style.display = 'inline-block';
    } else {
        disp.textContent = '00:00:00';
        dot.style.background = '#6b7280';
        btn.textContent = '▶ Start';
        btn.style.background = 'var(--navy-dark)';
        saveBtn.style.display = 'none';
    }
}

var WARN_SEC     = 2 * 3600; // alerte visuelle après 2h
var REMIND_SEC   = 4 * 3600; // rappel après 4h
var IDLE_SEC     = 10 * 60;  // pause auto après 10 min d'inactivité
var warnShown    = false;
var lastActivity = Date.now();
var idlePaused   = false;

function resetActivity() {
    lastActivity = Date.now();
    // Si le chrono était en pause pour inactivité, on le reprend
    if (idlePaused) {
        idlePaused = false;
        var st = getState();
        if (!st.running) {
            setState({running: true, startTs: Date.now(), accumulated: st.accumulated || 0});
            if (!tickTimer) tickTimer = setInterval(tick, 1000);
            updateUI();
            // Enlever l'indication d'inactivité
            var widget = document.getElementById('chrono-widget');
            widget.title = '';
            widget.style.borderColor = '';
            document.getElementById('chrono-display').style.color = '';
        }
    }
}

['mousemove','keydown','click','scroll','touchstart'].forEach(function(ev) {
    document.addEventListener(ev, resetActivity, {passive: true});
});

function tick() {
    var st = getState();
    if (!st.running) { clearInterval(tickTimer); tickTimer = null; return; }
    var elapsed = Math.floor((Date.now() - st.startTs) / 1000) + (st.accumulated || 0);
    document.getElementById('chrono-display').textContent = formatSeconds(elapsed);

    // Pause automatique après 10 min d'inactivité
    var idleTime = Math.floor((Date.now() - lastActivity) / 1000);
    if (idleTime >= IDLE_SEC && !idlePaused) {
        idlePaused = true;
        var acc = elapsed;
        setState({running: false, accumulated: acc, startTs: null});
        clearInterval(tickTimer); tickTimer = null;
        updateUI();
        var widget = document.getElementById('chrono-widget');
        widget.style.borderColor = '#f59e0b';
        widget.title = 'Chrono mis en pause — inactivité détectée. Bougez la souris pour reprendre.';
        document.getElementById('chrono-display').style.color = '#f59e0b';
        return;
    }

    // Alerte visuelle à 2h
    if (elapsed >= WARN_SEC && elapsed < REMIND_SEC && !warnShown) {
        warnShown = true;
        document.getElementById('chrono-display').style.color = '#f59e0b';
        document.getElementById('chrono-widget').title = 'Chrono actif depuis plus de 2h';
    }

    // Rappel à 4h (et toutes les heures après)
    if (elapsed >= REMIND_SEC && elapsed % 3600 < 2) {
        document.getElementById('chrono-display').style.color = '#ef4444';
        document.getElementById('chrono-btn').style.boxShadow = '0 0 0 3px rgba(239,68,68,.3)';
        document.getElementById('chrono-widget').title = 'Chrono actif depuis ' + Math.floor(elapsed/3600) + 'h — pensez à enregistrer !';
    }
}

window.chronoToggle = function() {
    var st = getState();
    if (st.running) {
        // Stop
        var elapsed = Math.floor((Date.now() - st.startTs) / 1000) + (st.accumulated || 0);
        setState({running: false, accumulated: elapsed, startTs: null});
        clearInterval(tickTimer); tickTimer = null;
    } else {
        // Start
        setState({running: true, startTs: Date.now(), accumulated: st.accumulated || 0});
        if (!tickTimer) tickTimer = setInterval(tick, 1000);
    }
    updateUI();
};

window.chronoSave = function() {
    var st = getState();
    var sec = st.accumulated || 0;
    if (sec < 60) { alert('Durée trop courte (moins d\'1 minute)'); return; }
    var h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60);
    document.getElementById('ch_date').value = new Date().toISOString().slice(0,10);
    document.getElementById('ch_heures').value = h;
    document.getElementById('ch_minutes').value = m >= 45 ? 45 : (m >= 30 ? 30 : (m >= 15 ? 15 : 0));
    document.getElementById('chrono-recap').textContent = 'Durée enregistrée : ' + pad(h)+'h'+pad(m)+'min sur <?= e($entreprise['raison_sociale']) ?>';
    document.getElementById('modal-chrono').style.display = 'flex';
};

window.chronoAnnulerSave = function() {
    document.getElementById('modal-chrono').style.display = 'none';
};

// Reset après soumission (si on revient sur la page)
<?php if(isset($_GET['ok'])): ?>
localStorage.removeItem(STORE_KEY);
<?php endif; ?>

// Init — démarrer automatiquement dès qu'on ouvre un dossier
var st = getState();
setState({running: true, startTs: Date.now(), accumulated: st.accumulated || 0});
idlePaused = false;
updateUI();
tickTimer = setInterval(tick, 1000);
})();
</script>

    <main class="page-content">
        <?= $content ?? '' ?>
    </main>
</div>

<!-- ===== PANNEAU NOTES / COMMENTAIRES ===== -->
<style>
#notes-panel {
    position: fixed; top: 0; right: 0; bottom: 0; width: 420px; max-width: 90vw;
    background: #fff; box-shadow: -4px 0 24px rgba(0,0,0,.12);
    z-index: 500; display: flex; flex-direction: column;
    transform: translateX(100%);
    transition: transform .3s cubic-bezier(.4,0,.2,1);
    border-left: 1px solid var(--border);
}
#notes-panel.open { transform: translateX(0); }
#notes-toggle {
    position: fixed; bottom: 28px; right: 28px;
    background: var(--navy-dark); color: #fff;
    border: none; border-radius: 50px; padding: 12px 20px;
    cursor: pointer; font-size: 14px; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 16px rgba(18,37,64,.3);
    z-index: 499; transition: transform .15s;
}
#notes-toggle:hover { transform: scale(1.05); }
#notes-badge { background: #ef4444; color: #fff; border-radius: 10px; font-size: 11px; font-weight: 700; padding: 1px 6px; display: none; }
.notes-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--navy-dark); color: #fff; }
.notes-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; background: #f8fafc; }
.note-item { background: #fff; border-radius: 10px; padding: 12px 14px; border: 1px solid var(--border); border-left: 4px solid #e4e9f0; }
.note-item[data-type="alerte"] { border-left-color: #ef4444; }
.note-item[data-type="tache"]  { border-left-color: #f59e0b; }
.note-item[data-type="info"]   { border-left-color: #1f6e4e; }
.note-item[data-type="note"]   { border-left-color: #8b5cf6; }
.note-item.resolu { opacity: .5; }
.note-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap; }
.note-avatar { width: 26px; height: 26px; border-radius: 50%; background: var(--navy-dark); color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.note-author { font-size: 12px; font-weight: 600; }
.note-time   { font-size: 11px; color: var(--text-muted); margin-left: auto; }
.note-type-badge { font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; text-transform: uppercase; }
.note-message { font-size: 13px; color: var(--text); line-height: 1.5; word-break: break-word; }
.note-actions { display: flex; gap: 8px; margin-top: 8px; }
.note-action-btn { font-size: 11px; color: var(--text-muted); background: none; border: none; cursor: pointer; padding: 2px 0; }
.note-action-btn:hover { color: var(--navy-dark); }
.notes-form { padding: 14px 16px; border-top: 1px solid var(--border); background: #fff; }
.notes-form textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; font-family: inherit; resize: none; outline: none; color: var(--text); }
.notes-form textarea:focus { border-color: var(--navy-dark); }
.notes-form-controls { display: flex; gap: 8px; margin-top: 8px; align-items: center; }
.notes-form select { padding: 6px 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 12px; color: var(--text); background: #fff; }
.notes-submit { margin-left: auto; padding: 8px 18px; background: var(--navy-dark); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; }
.notes-empty { text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 13px; }
</style>

<button id="notes-toggle" onclick="toggleNotes()">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
    Notes dossier
    <span id="notes-badge">0</span>
</button>

<div id="notes-panel">
    <div class="notes-header">
        <div>
            <div style="font-weight:700;font-size:15px">Notes &amp; Suivi</div>
            <div style="font-size:12px;opacity:.7;margin-top:2px"><?= e($entreprise['raison_sociale']) ?></div>
        </div>
        <button onclick="toggleNotes()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:20px;line-height:1">&times;</button>
    </div>
    <div class="notes-messages" id="notes-list">
        <p class="notes-empty">Chargement...</p>
    </div>
    <div class="notes-form">
        <textarea id="note-message" rows="3" placeholder="Ajouter une note, alerte, tâche... (Ctrl+Entrée pour envoyer)"></textarea>
        <div class="notes-form-controls">
            <select id="note-type">
                <option value="note">Note</option>
                <option value="info">Info</option>
                <option value="tache">Tâche</option>
                <option value="alerte">Alerte</option>
            </select>
            <select id="note-priorite">
                <option value="normale">Normale</option>
                <option value="haute">Haute</option>
                <option value="urgente">Urgente</option>
            </select>
            <button class="notes-submit" onclick="envoyerNote()">Envoyer</button>
        </div>
    </div>
</div>

<script>
(function() {
var ENT_ID  = <?= (int)$entreprise['id'] ?>;
var USER_ID = <?= (int)(auth()['id'] ?? 0) ?>;
var APP     = '<?= APP_URL ?>';
var panelOpen = false;
var pollTimer = null;
var typeColors = {note:'#8b5cf6',alerte:'#ef4444',tache:'#f59e0b',info:'#1f6e4e'};
var typeLabels = {note:'Note',alerte:'Alerte',tache:'Tâche',info:'Info'};
var typeIcons  = {note:'📝',alerte:'🔴',tache:'✅',info:'ℹ️'};

window.toggleNotes = function() {
    panelOpen = !panelOpen;
    document.getElementById('notes-panel').classList.toggle('open', panelOpen);
    if (panelOpen) { chargerNotes(); pollTimer = setInterval(chargerNotes, 10000); }
    else { clearInterval(pollTimer); }
};

function mk(tag, cls, txt) {
    var el = document.createElement(tag);
    if (cls) el.className = cls;
    if (txt !== undefined) el.textContent = txt;
    return el;
}

function chargerNotes() {
    fetch(APP + '/dossier/commentaires?id=' + ENT_ID)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var list = document.getElementById('notes-list');
            while (list.firstChild) list.removeChild(list.firstChild);

            if (!data.length) {
                list.appendChild(mk('p', 'notes-empty', 'Aucune note pour ce dossier. Soyez le premier à en ajouter une.'));
                return;
            }

            data.forEach(function(c) {
                var color = typeColors[c.type] || '#6b7a94';
                var label = (typeIcons[c.type]||'') + ' ' + (typeLabels[c.type] || c.type);
                var dt = new Date(c.created_at.replace(' ','T'));
                var dateStr = dt.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'})
                            + ' ' + dt.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});

                var item = mk('div', 'note-item' + (c.resolu ? ' resolu' : ''));
                item.setAttribute('data-type', c.type);
                item.setAttribute('data-id', c.id);

                // Meta
                var meta = mk('div', 'note-meta');
                var ini  = ((c.prenom||'')[0]||'').toUpperCase() + ((c.user_nom||'')[0]||'').toUpperCase();
                meta.appendChild(mk('div', 'note-avatar', ini));
                meta.appendChild(mk('span', 'note-author', c.prenom + ' ' + c.user_nom));
                var badge = mk('span', 'note-type-badge', label);
                badge.style.background = color + '22';
                badge.style.color = color;
                meta.appendChild(badge);
                if (c.priorite === 'urgente') { var p = mk('span', null, '⚡ URGENT'); p.style.cssText='font-size:10px;font-weight:700;color:#ef4444'; meta.appendChild(p); }
                else if (c.priorite === 'haute') { var p = mk('span', null, '↑ HAUTE'); p.style.cssText='font-size:10px;font-weight:700;color:#f59e0b'; meta.appendChild(p); }
                meta.appendChild(mk('span', 'note-time', dateStr));
                item.appendChild(meta);

                // Message
                var msgDiv = mk('div', 'note-message');
                c.message.split('\n').forEach(function(line, i) {
                    if (i > 0) msgDiv.appendChild(document.createElement('br'));
                    msgDiv.appendChild(document.createTextNode(line));
                });
                item.appendChild(msgDiv);

                // Actions
                var actions = mk('div', 'note-actions');
                if (!c.resolu && c.user_id != USER_ID) {
                    var btn = mk('button', 'note-action-btn', '✓ Marquer résolu');
                    (function(id){ btn.addEventListener('click', function(){ resoludre(id); }); })(c.id);
                    actions.appendChild(btn);
                }
                if (c.user_id == USER_ID) {
                    var btn2 = mk('button', 'note-action-btn', 'Supprimer');
                    (function(id){ btn2.addEventListener('click', function(){ supprimerNote(id); }); })(c.id);
                    actions.appendChild(btn2);
                }
                if (actions.children.length) item.appendChild(actions);
                list.appendChild(item);
            });
        });
}

var CSRF_TOKEN = '<?= generateCsrfToken() ?>';

// Injecter le token CSRF dans tous les formulaires POST
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function(form) {
        if (!form.querySelector('input[name="csrf_token"]')) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = CSRF_TOKEN;
            form.appendChild(input);
        }
    });
});

window.envoyerNote = function() {
    var msg = document.getElementById('note-message').value.trim();
    if (!msg) return;
    var fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('entreprise_id', ENT_ID);
    fd.append('message', msg);
    fd.append('type', document.getElementById('note-type').value);
    fd.append('priorite', document.getElementById('note-priorite').value);
    fetch(APP + '/dossier/commentaires/store', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(d){ if(d.ok){ document.getElementById('note-message').value=''; chargerNotes(); }});
};

function resoludre(id) {
    var fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('entreprise_id', ENT_ID);
    fd.append('comment_id', id);
    fetch(APP + '/dossier/commentaires/resoudre', {method:'POST', body:fd}).then(chargerNotes);
}

function supprimerNote(id) {
    if (!confirm('Supprimer cette note ?')) return;
    var fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('entreprise_id', ENT_ID);
    fd.append('comment_id', id);
    fetch(APP + '/dossier/commentaires/supprimer', {method:'POST', body:fd}).then(chargerNotes);
}

document.getElementById('note-message').addEventListener('keydown', function(ev) {
    if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) window.envoyerNote();
});
})();
</script>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('open');
}
function checkViewport() {
    const h = document.getElementById('hamburger');
    if (window.innerWidth <= 768) {
        h.style.display = 'flex';
    } else {
        h.style.display = 'none';
        closeSidebar();
    }
}
window.addEventListener('resize', checkViewport);
checkViewport();
</script>

</body>
</html>
