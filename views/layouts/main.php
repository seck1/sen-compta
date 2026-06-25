<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'SenCompta') ?> — SenCompta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy: #1e3a5f;
    --navy-dark: #122540;
    --navy-mid: #1a3252;
    --navy-light: #2a4f7c;
    --gold: #c9a96e;
    --gold-light: #e2c99a;
    --gold-dark: #a8843f;
    --white: #ffffff;
    --bg: #f0f3f8;
    --bg-card: #ffffff;
    --text: #1a2535;
    --text-muted: #6b7a94;
    --border: #e4e9f0;
    --sidebar-w: 290px;
    --header-h: 68px;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
}

html { font-size: 19px; }
body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 19px; line-height: 1.5; }

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: var(--sidebar-w);
    background: var(--navy-dark);
    display: flex;
    flex-direction: column;
    z-index: 100;
    border-right: 1px solid rgba(201,169,110,0.1);
}

.sidebar-logo {
    padding: 24px 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
}

.logo-mark {
    width: 48px; height: 48px;
    border-radius: 11px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(201,169,110,0.25);
    display: block;
    object-fit: contain;
}

.logo-info { line-height: 1.2; }
.logo-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 600;
    color: var(--white);
    letter-spacing: 0.2px;
}
.logo-sub {
    font-size: 13px;
    color: var(--gold);
    letter-spacing: 1px;
    text-transform: uppercase;
    opacity: 0.8;
}

/* Nav sidebar */
.sidebar-nav {
    flex: 1;
    padding: 20px 12px;
    overflow-y: auto;
}

.nav-section {
    margin-bottom: 28px;
}

.nav-section-label {
    font-size: 9pt;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 4px 12px;
    margin-bottom: 8px;
    font-weight: 700;
    display: flex; align-items: center; gap: 7px;
}
.nav-section-label::before {
    content: ''; display: inline-block;
    width: 6px; height: 6px; border-radius: 50%;
    flex-shrink: 0;
}
.label-principal     { color: #93c5fd; }
.label-principal::before     { background: #93c5fd; box-shadow: 0 0 6px #93c5fd88; }
.label-admin         { color: #fca5a5; }
.label-admin::before         { background: #fca5a5; box-shadow: 0 0 6px #fca5a588; }
.label-commercial    { color: #6ee7b7; }
.label-commercial::before    { background: #6ee7b7; box-shadow: 0 0 6px #6ee7b788; }
.label-compte        { color: var(--gold); }
.label-compte::before        { background: var(--gold); box-shadow: 0 0 6px rgba(201,169,110,.7); }
.label-compta        { color: #c4b5fd; }
.label-compta::before        { background: #c4b5fd; box-shadow: 0 0 6px #c4b5fd88; }

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 16px;
    border-radius: 10px;
    color: rgba(255,255,255,0.95);
    text-decoration: none;
    font-size: 12pt;
    font-weight: 500;
    transition: all 0.2s ease;
    margin-bottom: 3px;
    position: relative;
}

.nav-item svg { width: 20px; height: 20px; flex-shrink: 0; }

.nav-item:hover {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.9);
}

.nav-item.active {
    background: rgba(201,169,110,0.12);
    color: var(--gold);
    font-weight: 500;
}

.nav-item.active::before {
    content: '';
    position: absolute;
    left: 0; top: 25%; bottom: 25%;
    width: 3px;
    background: var(--gold);
    border-radius: 0 3px 3px 0;
}

.nav-badge {
    margin-left: auto;
    background: var(--gold);
    color: var(--navy-dark);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 20px;
    min-width: 20px;
    text-align: center;
}

/* Footer sidebar */
.sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

.user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 10px;
    background: rgba(255,255,255,0.04);
}

.user-avatar {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--navy-light), var(--gold-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    font-weight: 600;
    color: var(--white);
    flex-shrink: 0;
}

.user-info { flex: 1; min-width: 0; }
.user-name {
    font-size: 15px;
    font-weight: 500;
    color: var(--white);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.user-role {
    font-size: 13px;
    color: var(--gold);
    text-transform: capitalize;
    opacity: 0.8;
}

.btn-logout {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.4);
    transition: all 0.2s;
    flex-shrink: 0;
    text-decoration: none;
}
.btn-logout:hover { background: rgba(239,68,68,0.2); color: #ef4444; }
.btn-logout svg { width: 16px; height: 16px; }

/* ===== MAIN CONTENT ===== */
.main-wrap {
    margin-left: var(--sidebar-w);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header */
.topbar {
    position: sticky;
    top: 0;
    height: var(--header-h);
    background: rgba(255,255,255,0.97);
    backdrop-filter: blur(12px);
    border-bottom: 2px solid var(--border);
    box-shadow: 0 1px 8px rgba(30,58,95,0.06);
    display: flex;
    align-items: center;
    padding: 0 32px;
    gap: 20px;
    z-index: 50;
}

.topbar-breadcrumb {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 16px;
}

.topbar-breadcrumb a {
    color: var(--navy);
    text-decoration: none;
    background: rgba(30,58,95,0.07);
    border: 1px solid rgba(30,58,95,0.15);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 15px;
    display: flex; align-items: center; gap: 5px;
    transition: background 0.15s, border-color 0.15s;
}
.topbar-breadcrumb a:hover {
    background: rgba(30,58,95,0.14);
    border-color: rgba(30,58,95,0.3);
}

.topbar-breadcrumb .sep {
    color: var(--text-muted);
    font-size: 16px;
    opacity: 0.5;
    margin: 0 1px;
}

.topbar-breadcrumb .current {
    color: var(--white);
    background: var(--navy);
    border: 1px solid var(--navy);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 15px;
}

.topbar-breadcrumb svg { width: 14px; height: 14px; }

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-date {
    font-size: 15px;
    color: var(--text-muted);
    padding: 6px 14px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 8px;
}

.topbar-notif {
    position: relative;
    width: 38px; height: 38px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-muted);
    text-decoration: none;
}
.topbar-notif:hover { border-color: var(--navy); color: var(--navy); }
.topbar-notif svg { width: 18px; height: 18px; }

.notif-dot {
    position: absolute;
    top: 6px; right: 6px;
    width: 18px; height: 18px;
    background: var(--danger);
    border-radius: 50%;
    border: 2px solid white;
    font-size: 10px;
    font-weight: 700;
    color: white;
    display: flex; align-items: center; justify-content: center;
    line-height: 1;
}

/* Dropdown notifications */
.notif-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 380px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    z-index: 200;
    display: none;
    overflow: hidden;
}
.notif-dropdown.open { display: block; }
.notif-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    font-size: 15px;
}
.notif-dropdown-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(228,233,240,0.5);
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    color: inherit;
}
.notif-dropdown-item:hover { background: var(--bg); }
.notif-dropdown-item.unread { background: rgba(30,58,95,0.02); }
.notif-icon-wrap {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.notif-icon-wrap svg { width: 18px; height: 18px; }
.notif-dropdown-footer {
    padding: 12px 20px;
    text-align: center;
}
.notif-dropdown-footer a {
    font-size: 14px;
    color: var(--navy);
    text-decoration: none;
    font-weight: 500;
}

/* Page content */
.page-content {
    flex: 1;
    padding: 32px;
}

/* ===== COMPOSANTS RÉUTILISABLES ===== */

/* Page header */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 20px;
}

.page-header-left {}

.page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 38px;
    font-weight: 400;
    color: var(--navy-dark);
    letter-spacing: -0.3px;
    line-height: 1.2;
}

.page-subtitle {
    font-size: 17px;
    color: var(--text-muted);
    margin-top: 4px;
}

/* Boutons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 10px;
    font-size: 17px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.btn svg { width: 16px; height: 16px; }

.btn-primary {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: var(--white);
    box-shadow: 0 4px 12px rgba(30,58,95,0.25);
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(30,58,95,0.35); }

.btn-gold {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: var(--navy-dark);
    box-shadow: 0 4px 12px rgba(201,169,110,0.3);
}
.btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(201,169,110,0.4); }

.btn-outline {
    background: var(--white);
    color: var(--text);
    border: 1px solid var(--border);
}
.btn-outline:hover { border-color: var(--navy); color: var(--navy); }

.btn-danger {
    background: rgba(239,68,68,0.08);
    color: var(--danger);
    border: 1px solid rgba(239,68,68,0.2);
}
.btn-danger:hover { background: var(--danger); color: white; }

.btn-sm { padding: 8px 16px; font-size: 15.5px; }

/* Cards */
.card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}

/* Stats KPI */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.kpi-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--navy), var(--gold));
}

.kpi-label {
    font-size: 14px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    font-weight: 600;
}

.kpi-value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 46px;
    font-weight: 600;
    color: var(--navy-dark);
    line-height: 1;
    margin-bottom: 6px;
}

.kpi-trend {
    font-size: 15px;
    color: var(--text-muted);
}
.kpi-trend.up { color: var(--success); }
.kpi-trend.down { color: var(--danger); }

.kpi-icon {
    position: absolute;
    top: 20px; right: 20px;
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.kpi-icon svg { width: 22px; height: 22px; }
.kpi-icon.navy { background: rgba(30,58,95,0.08); color: var(--navy); }
.kpi-icon.gold  { background: rgba(201,169,110,0.12); color: var(--gold-dark); }
.kpi-icon.green { background: rgba(34,197,94,0.1); color: var(--success); }
.kpi-icon.orange { background: rgba(245,158,11,0.1); color: var(--warning); }

/* Table */
.table-wrap {
    border-radius: 16px;
    border: 1px solid var(--border);
    overflow: hidden;
    background: var(--bg-card);
}

.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
}

.table-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--navy-dark);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    padding: 14px 20px;
    text-align: left;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    background: var(--bg);
    border-bottom: 1px solid var(--border);
}

tbody tr {
    border-bottom: 1px solid rgba(228,233,240,0.6);
    transition: background 0.15s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(240,243,248,0.8); }

tbody td {
    padding: 15px 20px;
    font-size: 17px;
    color: var(--text);
    vertical-align: middle;
}

/* Badges statut */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 15px;
    font-weight: 500;
}
.badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }

.badge-success { background: rgba(34,197,94,0.1); color: #16a34a; }
.badge-success::before { background: #22c55e; }
.badge-warning { background: rgba(245,158,11,0.1); color: #d97706; }
.badge-warning::before { background: #f59e0b; }
.badge-danger  { background: rgba(239,68,68,0.1); color: #dc2626; }
.badge-danger::before  { background: #ef4444; }
.badge-info    { background: rgba(59,130,246,0.1); color: #2563eb; }
.badge-info::before    { background: #3b82f6; }
.badge-navy    { background: rgba(30,58,95,0.08); color: var(--navy); }
.badge-navy::before    { background: var(--navy); }

/* Avatar entreprise */
.ent-avatar {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

/* Formulaires */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-field label {
    font-size: 14px;
    font-weight: 500;
    color: var(--text);
}

.form-field input,
.form-field select,
.form-field textarea {
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 15px;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    background: var(--white);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(30,58,95,0.08);
}

.form-field textarea { resize: vertical; min-height: 90px; }

.form-actions {
    display: flex;
    gap: 12px;
    padding-top: 8px;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 40px;
}
.empty-state svg { width: 56px; height: 56px; color: var(--border); margin: 0 auto 16px; display: block; }
.empty-state h3 { font-size: 18px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
.empty-state p  { font-size: 15px; color: var(--text-muted); opacity: 0.7; }

/* Scrollbar */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(30,58,95,0.15); border-radius: 3px; }

/* Responsive */
@media (max-width: 1280px) {
    :root { --sidebar-w: 240px; }
}

@media (max-width: 1024px) {
    :root { --sidebar-w: 200px; }
    .page-content { padding: 20px; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .form-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    :root { --sidebar-w: 0px; }

    /* Sidebar cachée par défaut sur mobile/tablette */
    .sidebar {
        transform: translateX(-260px);
        width: 260px;
        transition: transform 0.3s ease;
        z-index: 200;
    }
    .sidebar.open { transform: translateX(0); }

    /* Overlay quand sidebar ouverte */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 199;
    }
    .sidebar-overlay.open { display: block; }

    .main-wrap { margin-left: 0; }

    .topbar { padding: 0 16px; }
    .topbar-date { display: none; }

    /* Bouton hamburger */
    .hamburger {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 38px; height: 38px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        color: var(--navy);
        flex-shrink: 0;
    }

    .page-content { padding: 16px; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .kpi-value { font-size: 28px; }
    .page-header { flex-direction: column; gap: 12px; }
    .page-header .btn { width: 100%; justify-content: center; }
    .form-grid { grid-template-columns: 1fr; }
    .table-wrap { overflow-x: auto; }
    table { min-width: 600px; }
}

@media (max-width: 480px) {
    .kpi-grid { grid-template-columns: 1fr; }
    .topbar-breadcrumb .sep { display: none; }
    .topbar-breadcrumb a { display: none; }
}
</style>
</head>
<body>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <a href="<?= APP_URL ?>/dashboard" class="sidebar-logo">
        <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta" class="logo-mark">
        <div class="logo-info">
            <div class="logo-name">SenCompta</div>
            <div class="logo-sub">Gestion comptable</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <?php if (isSuperAdmin()): ?>
        <!-- ===== MENU SUPER-ADMIN (proprietaire plateforme SenCompta) ===== -->
        <div class="nav-section">
            <div class="nav-section-label label-admin">Supervision plateforme</div>
            <a href="<?= APP_URL ?>/superadmin" class="nav-item <?= ($activePage ?? '') === 'superadmin' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Tableau de bord
            </a>
            <a href="<?= APP_URL ?>/superadmin/cabinets" class="nav-item <?= ($activePage ?? '') === 'superadmin-cabinets' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" /></svg>
                Cabinets
            </a>
            <a href="<?= APP_URL ?>/superadmin/paiements" class="nav-item <?= ($activePage ?? '') === 'superadmin-paiements' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                Suivi des paiements
            </a>
            <a href="<?= APP_URL ?>/superadmin/demandes" class="nav-item <?= ($activePage ?? '') === 'superadmin-demandes' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                Demandes d'upgrade
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label label-compte">Mon compte</div>
            <a href="<?= APP_URL ?>/profil" class="nav-item <?= ($activePage ?? '') === 'profil' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Profil
            </a>
        </div>
        <?php else: ?>
        <div class="nav-section">
            <div class="nav-section-label label-principal">Principal</div>
            <a href="<?= APP_URL ?>/dashboard" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Tableau de bord
            </a>
            <a href="<?= APP_URL ?>/entreprises" class="nav-item <?= ($activePage ?? '') === 'entreprises' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                Dossiers Entreprises
                <span class="nav-badge" id="nb-entreprises">—</span>
            </a>
        </div>

        <?php if (isSuperviseur()): ?>
        <div class="nav-section">
            <div class="nav-section-label label-admin">
                <?= isAdmin() ? 'Administration' : 'Supervision' ?>
            </div>
            <?php if (isAdmin()): ?>
            <a href="<?= APP_URL ?>/users" class="nav-item <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                Collaborateurs
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/planning" class="nav-item <?= ($activePage ?? '') === 'planning' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H18v-.008zm0 2.25h.008v.008H18V15z" /></svg>
                Planning missions
            </a>
            <?php if (isAdmin()): ?>
            <a href="<?= APP_URL ?>/honoraires/tableau" class="nav-item <?= ($activePage ?? '') === 'suivi-paiements' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Suivi paiements
            </a>
            <a href="<?= APP_URL ?>/honoraires" class="nav-item <?= ($activePage ?? '') === 'honoraires' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                Honoraires
            </a>
            <a href="<?= APP_URL ?>/rapport-temps" class="nav-item <?= ($activePage ?? '') === 'rapport-temps' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Rapport temps global
            </a>
            <a href="<?= APP_URL ?>/notifications-email" class="nav-item <?= ($activePage ?? '') === 'notifications-email' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                Notifications email
            </a>
            <a href="<?= APP_URL ?>/audit-log" class="nav-item <?= ($activePage ?? '') === 'audit' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
                Journal des actions
            </a>
            <a href="<?= APP_URL ?>/admin/backups" class="nav-item <?= ($activePage ?? '') === 'backups' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                Sauvegardes
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Commercial — SenCompta uniquement -->
        <div class="nav-section">
            <div class="nav-section-label label-commercial">Commercial</div>
            <a href="<?= APP_URL ?>/commercial" class="nav-item <?= in_array($activePage ?? '', ['commercial','commercial-dashboard']) ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/></svg>
                Tableau de bord
            </a>
            <a href="<?= APP_URL ?>/commercial/prospects" class="nav-item <?= str_starts_with($activePage ?? '', 'commercial-prospects') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                Prospects & Clients
            </a>
            <a href="<?= APP_URL ?>/commercial/devis" class="nav-item <?= str_starts_with($activePage ?? '', 'commercial-devis') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Devis
            </a>
            <a href="<?= APP_URL ?>/commercial/factures" class="nav-item <?= str_starts_with($activePage ?? '', 'commercial-factures') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                Factures
            </a>
            <a href="<?= APP_URL ?>/commercial/catalogue" class="nav-item <?= ($activePage ?? '') === 'commercial-catalogue' ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                Catalogue
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-compte">Mon compte</div>
            <a href="<?= APP_URL ?>/notifications" class="nav-item <?= ($activePage ?? '') === 'notifications' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                Notifications
                <?php if (isset($notifCount) && $notifCount > 0): ?>
                <span class="nav-badge"><?= (int)$notifCount ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label label-compta">Comptabilité</div>
            <a href="#" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                Écritures
            </a>
            <a href="#" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg>
                États financiers
            </a>
            <a href="#" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                Échéances fiscales
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <?php $u = auth(); ?>
        <div class="user-card">
            <a href="<?= APP_URL ?>/profil" style="text-decoration:none;display:flex;align-items:center;gap:12px;flex:1;min-width:0">
            <div class="user-avatar"><?= strtoupper(substr($u['prenom'],0,1) . substr($u['nom'],0,1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= e($u['prenom'] . ' ' . $u['nom']) ?></div>
                <div class="user-role" style="display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;letter-spacing:0.5px;color:white;background:<?= getRoleBadgeColor($u['role']) ?>"><?= getRoleLabel($u['role']) ?></span>
                    <?= !empty($u['totp_actif']) ? '<span title="2FA activé" style="font-size:11px">🔐</span>' : '' ?>
                </div>
            </div>
            </a>
            <form method="post" action="<?= APP_URL ?>/logout" style="display:contents">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <button type="submit" class="btn-logout" title="Déconnexion" style="flex-shrink:0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
    <!-- Topbar -->
    <header class="topbar">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()" style="display:none;margin-right:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
        </button>
        <div class="topbar-breadcrumb">
            <a href="<?= APP_URL ?>/dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                SenCompta
            </a>
            <span class="sep">›</span>
            <span class="current"><?= e($pageTitle ?? 'Dashboard') ?></span>
        </div>
        <div class="topbar-actions">
            <div class="topbar-date">
                <?= date('d/m/Y') ?>
            </div>
            <?php
            $notifCount = 0;
            if (auth()) {
                require_once APP_ROOT . '/src/Services/NotificationService.php';
                $notifCount = NotificationService::countNonLues(auth()['id']);
                $notifItems = NotificationService::getNonLues(auth()['id']);
            }
            ?>
            <div style="position:relative">
                <button class="topbar-notif" id="notif-btn" onclick="toggleNotif(event)" style="background:none;border:1px solid var(--border);width:38px;height:38px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);position:relative;transition:all 0.2s">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                    <?php if ($notifCount > 0): ?>
                    <span class="notif-dot" id="notif-count"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="notif-dropdown" id="notif-dropdown">
                    <div class="notif-dropdown-header">
                        <span>Notifications <?php if($notifCount > 0): ?><span style="background:var(--danger);color:white;border-radius:20px;padding:2px 8px;font-size:11px;margin-left:6px"><?= (int)$notifCount ?></span><?php endif; ?></span>
                        <button onclick="marquerLues()" style="font-size:12px;color:var(--navy);background:none;border:none;cursor:pointer;font-weight:500">Tout marquer lu</button>
                    </div>
                    <div id="notif-list" style="max-height:380px;overflow-y:auto">
                    <?php if (empty($notifItems)): ?>
                        <div style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:13px">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 10px;display:block;opacity:0.3"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                            Aucune nouvelle notification
                        </div>
                    <?php else: ?>
                    <?php
                    $notifColors = ['info'=>'#3b82f6','success'=>'#22c55e','warning'=>'#f59e0b','danger'=>'#ef4444'];
                    foreach (array_slice($notifItems, 0, 8) as $ni):
                        $nc = $notifColors[$ni['type']] ?? '#3b82f6';
                    ?>
                    <a href="<?= ($ni['lien'] && str_starts_with($ni['lien'], '/')) ? APP_URL . e($ni['lien']) : APP_URL.'/notifications' ?>" class="notif-dropdown-item <?= !$ni['lu'] ? 'unread' : '' ?>">
                        <div class="notif-icon-wrap" style="background:<?= $nc ?>1a;color:<?= $nc ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:13px;font-weight:<?= !$ni['lu'] ? '600':'400' ?>"><?= e($ni['titre']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($ni['message']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:4px"><?= date('d/m H:i', strtotime($ni['created_at'])) ?></div>
                        </div>
                        <?php if (!$ni['lu']): ?><div style="width:7px;height:7px;border-radius:50%;background:var(--navy);flex-shrink:0;margin-top:4px"></div><?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                    <div class="notif-dropdown-footer">
                        <a href="<?= APP_URL ?>/notifications">Voir toutes les notifications →</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu de la page -->
    <main class="page-content">
        <?= $content ?? '' ?>
    </main>
</div>

<script>
// Sidebar responsive
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('open');
}
// Afficher hamburger sur mobile
function checkViewport() {
    const hamburger = document.getElementById('hamburger');
    if (window.innerWidth <= 768) {
        hamburger.style.display = 'flex';
    } else {
        hamburger.style.display = 'none';
        closeSidebar();
    }
}
window.addEventListener('resize', checkViewport);
checkViewport();

// Badge nb entreprises
fetch('<?= APP_URL ?>/entreprises?api=count')
    .then(r => r.json())
    .then(d => {
        const el = document.getElementById('nb-entreprises');
        if (el && d.count !== undefined) el.textContent = d.count;
    }).catch(() => {
        const el = document.getElementById('nb-entreprises');
        if (el) el.textContent = '';
    });

// Notifications dropdown
function toggleNotif(e) {
    e.stopPropagation();
    document.getElementById('notif-dropdown').classList.toggle('open');
}
document.addEventListener('click', () => {
    document.getElementById('notif-dropdown')?.classList.remove('open');
});
document.getElementById('notif-dropdown')?.addEventListener('click', e => e.stopPropagation());

// Token CSRF global disponible pour tous les fetch() POST
const CSRF_TOKEN = '<?= generateCsrfToken() ?>';

// Intercepter tous les formulaires pour injecter le token CSRF automatiquement
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(form => {
        if (!form.querySelector('input[name="csrf_token"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = CSRF_TOKEN;
            form.appendChild(input);
        }
    });
});

function marquerLues() {
    fetch('<?= APP_URL ?>/notifications/marquer-lues', {method:'POST', headers:{'X-CSRF-Token': CSRF_TOKEN}})
        .then(() => {
            document.getElementById('notif-count')?.remove();
            document.querySelectorAll('.notif-dropdown-item.unread').forEach(el => el.classList.remove('unread'));
            document.querySelectorAll('[style*="background:var(--navy)"]').forEach(el => {
                if (el.style.borderRadius === '50%') el.remove();
            });
        });
}
</script>
</body>
</html>
