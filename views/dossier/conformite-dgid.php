<?php
$gradeColors = ['A'=>'#1f6e4e','B'=>'#2563eb','C'=>'#f59e0b','D'=>'#ef4444'];
$gradeColor  = $gradeColors[$grade] ?? '#6b7280';
$gradeLabels = ['A'=>'Excellent','B'=>'Bien','C'=>'Moyen','D'=>'Insuffisant'];

// SVG icons
function svgIcon(string $name, string $size = '18px'): string {
    $s = "width:{$size};height:{$size};flex-shrink:0";
    $icons = [
        'shield-check' => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
        'building'     => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>',
        'envelope'     => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>',
        'chart-bar'    => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
        'briefcase'    => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/></svg>',
        'user'         => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
        'academic'     => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>',
        'pencil'       => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>',
        'trending'     => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>',
        'book'         => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
        'check-circle' => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'x-circle'     => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'warning'      => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z"/></svg>',
        'download'     => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>',
        'refresh'      => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>',
        'arrow-left'   => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>',
        'star'         => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd"/></svg>',
        'clipboard'    => '<svg style="'.$s.'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>',
    ];
    return $icons[$name] ?? '';
}

$sectionIcons = [
    'Identification' => 'building',
    'Contacts'       => 'envelope',
    'Comptable'      => 'chart-bar',
    'Activite'       => 'trending',
    'Dirigeant'      => 'user',
    'Professionnel'  => 'academic',
    'Signature'      => 'pencil',
];
$sectionIconColors = [
    'Identification' => '#2563eb',
    'Contacts'       => '#0891b2',
    'Comptable'      => '#b8923f',
    'Activite'       => '#059669',
    'Dirigeant'      => '#d97706',
    'Professionnel'  => '#dc2626',
    'Signature'      => '#1e3a5f',
];
?>
<style>
.conf-grid { display:grid; grid-template-columns:300px 1fr; gap:20px; margin-bottom:20px; }
.conf-card { background:#fff; border:1px solid var(--border); border-radius:16px; padding:28px; }
.score-circle { position:relative; width:140px; height:140px; margin:0 auto 12px; }
.score-circle svg { transform:rotate(-90deg); }
.score-val { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.score-val .pct { font-size:36px; font-weight:800; color:var(--navy-dark); line-height:1; }
.score-val .lbl { font-size:13px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-top:3px; }
.export-status { margin-top:18px; padding:14px 16px; border-radius:12px; display:flex; align-items:flex-start; gap:12px; }
.export-status.ok  { background:#f0fdf4; border:1px solid #bbf7d0; }
.export-status.nok { background:#fef2f2; border:1px solid #fecaca; }
.export-status .es-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.export-status.ok .es-icon  { background:#dcfce7; color:#1f6e4e; }
.export-status.nok .es-icon { background:#fee2e2; color:#dc2626; }
.export-status .es-title { font-size:14px; font-weight:700; }
.export-status .es-sub { font-size:14px; color:var(--text-muted); margin-top:3px; line-height:1.4; }

.sections-list { display:flex; flex-direction:column; gap:10px; }
.sec-row { display:flex; align-items:center; gap:14px; padding:12px 16px; border-radius:12px; border:1px solid var(--border); background:#fafafa; transition:background .15s; }
.sec-row:hover { background:#f3f4f6; }
.sec-icon-wrap { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sec-info { flex:1; min-width:0; }
.sec-label { font-size:14px; font-weight:600; color:var(--navy-dark); }
.sec-sub { font-size:14px; color:var(--text-muted); margin-top:2px; }
.sec-bar-wrap { width:120px; flex-shrink:0; }
.sec-bar-bg { background:#e5e7eb; border-radius:99px; height:5px; overflow:hidden; }
.sec-bar-fill { height:5px; border-radius:99px; }
.sec-pct { font-size:14px; font-weight:700; text-align:right; margin-top:3px; }
.sec-status { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

.checklist-card { background:#fff; border:1px solid var(--border); border-radius:16px; padding:24px; margin-top:20px; }
.check-item { display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:9px; margin-bottom:4px; }
.check-item:hover { background:#fef2f2; }
.check-item .ci-dot { width:28px; height:28px; border-radius:7px; background:#fee2e2; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#dc2626; }
.check-item .ci-text { font-size:14px; color:var(--text); flex:1; }
.check-item .ci-text strong { color:var(--navy-dark); }
.check-item .ci-section { font-size:14px; color:var(--text-muted); display:flex; align-items:center; gap:4px; }

.action-btn { display:flex; align-items:center; justify-content:center; gap:8px; padding:11px 16px; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; width:100%; }
.action-btn-primary { background:var(--navy); color:#fff; }
.action-btn-primary:hover { background:var(--navy-light); }
.action-btn-outline { background:#fff; color:var(--navy); border:1.5px solid var(--border); }
.action-btn-outline:hover { border-color:var(--navy); background:#f8faff; }
.action-btn-gold { background:linear-gradient(135deg,#c9a96e,#a8843f); color:#fff; }
.action-btn-gold:hover { transform:translateY(-1px); box-shadow:0 4px 16px rgba(201,169,110,.35); }
</style>

<!-- Header -->
<div class="page-header" style="margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:14px">
        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#1e3a5f,#2a5080);display:flex;align-items:center;justify-content:center;color:#c9a96e">
            <?= svgIcon('shield-check','22px') ?>
        </div>
        <div>
            <h1 class="page-title">Rapport de Conformité DGID</h1>
            <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?></p>
        </div>
    </div>
    <a href="<?= APP_URL ?>/dossier/profil?id=<?= $id ?>" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:8px">
        <?= svgIcon('arrow-left','15px') ?> Retour aux paramètres
    </a>
</div>

<div class="conf-grid">
    <!-- Colonne gauche : score + export + actions -->
    <div style="display:flex;flex-direction:column;gap:16px">

        <!-- Score card -->
        <div class="conf-card" style="text-align:center">
            <div class="score-circle">
                <?php $circ = 2 * M_PI * 54; $filled_arc = $circ * $scorePct / 100; ?>
                <svg width="140" height="140" viewBox="0 0 140 140">
                    <circle cx="70" cy="70" r="54" fill="none" stroke="#f1f5f9" stroke-width="11"/>
                    <circle cx="70" cy="70" r="54" fill="none" stroke="<?= $gradeColor ?>" stroke-width="11"
                        stroke-dasharray="<?= round($filled_arc,1) ?> <?= round($circ,1) ?>"
                        stroke-linecap="round"/>
                </svg>
                <div class="score-val">
                    <span class="pct" style="color:<?= $gradeColor ?>"><?= $scorePct ?>%</span>
                    <span class="lbl">Complétude</span>
                </div>
            </div>

            <div style="font-size:13px;color:var(--text-muted);margin-bottom:14px"><?= $totalFilled ?> / <?= $totalFields ?> champs renseignés</div>

            <!-- Grade -->
            <div style="display:inline-flex;align-items:center;gap:10px;background:<?= $gradeColor ?>12;border:1.5px solid <?= $gradeColor ?>33;border-radius:12px;padding:10px 20px">
                <div style="width:36px;height:36px;border-radius:9px;background:<?= $gradeColor ?>;display:flex;align-items:center;justify-content:center;color:#fff">
                    <?= svgIcon('star','18px') ?>
                </div>
                <div style="text-align:left">
                    <div style="font-size:13px;font-weight:800;color:<?= $gradeColor ?>;line-height:1">Grade <?= $grade ?></div>
                    <div style="font-size:14px;color:var(--text-muted);margin-top:1px"><?= $gradeLabels[$grade] ?></div>
                </div>
            </div>
        </div>

        <!-- Export status -->
        <div class="conf-card" style="padding:20px">
            <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:12px">État de conformité</div>
            <div class="export-status <?= $exportPret ? 'ok' : 'nok' ?>">
                <div class="es-icon">
                    <?= svgIcon($exportPret ? 'check-circle' : 'x-circle', '18px') ?>
                </div>
                <div>
                    <div class="es-title" style="color:<?= $exportPret ? '#1f6e4e' : '#dc2626' ?>">
                        Export DGID — <?= $exportPret ? 'Prêt' : 'Non prêt' ?>
                    </div>
                    <div class="es-sub">
                        <?php if ($exportPret): ?>
                            Le dossier peut être exporté au format DGID.
                        <?php elseif ($nbEcritures === 0): ?>
                            Aucune écriture validée pour l'exercice <?= $entreprise['exercice_courant'] ?>.
                        <?php else: ?>
                            Complétude insuffisante (minimum 70% requis).
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="conf-card" style="padding:20px;display:flex;flex-direction:column;gap:8px">
            <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:4px">Actions</div>
            <a href="<?= APP_URL ?>/dossier/profil?id=<?= $id ?>" class="action-btn action-btn-primary">
                <?= svgIcon('pencil','16px') ?> Compléter les informations
            </a>
            <a href="<?= APP_URL ?>/dossier/profil/conformite-dgid?id=<?= $id ?>" class="action-btn action-btn-outline">
                <?= svgIcon('refresh','16px') ?> Recalculer
            </a>
            <?php if ($exportPret): ?>
            <a href="<?= APP_URL ?>/dossier/etat-financier-dgid/telecharger?id=<?= $id ?>&exercice=<?= $entreprise['exercice_courant'] ?>" class="action-btn action-btn-gold">
                <?= svgIcon('download','16px') ?> Télécharger DGID Excel
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Colonne droite : sections -->
    <div class="conf-card">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px">
            <div style="color:var(--navy)"><?= svgIcon('clipboard','18px') ?></div>
            <h3 style="font-size:13px;font-weight:700;color:var(--navy-dark);margin:0">Complétude par section</h3>
        </div>
        <div class="sections-list">
            <?php foreach ($sections as $secKey => $sec):
                $barColor = $sec['pct'] >= 80 ? '#1f6e4e' : ($sec['pct'] >= 50 ? '#f59e0b' : '#ef4444');
                $iconKey  = $sectionIcons[$secKey] ?? 'building';
                $iconColor = $sectionIconColors[$secKey] ?? '#1e3a5f';
            ?>
            <div class="sec-row">
                <div class="sec-icon-wrap" style="background:<?= $iconColor ?>12;color:<?= $iconColor ?>">
                    <?= svgIcon($iconKey,'16px') ?>
                </div>
                <div class="sec-info">
                    <div class="sec-label"><?= $sec['label'] ?></div>
                    <div class="sec-sub"><?= $sec['filled'] ?>/<?= $sec['total'] ?> champs</div>
                </div>
                <div class="sec-bar-wrap">
                    <div class="sec-bar-bg">
                        <div class="sec-bar-fill" style="width:<?= $sec['pct'] ?>%;background:<?= $barColor ?>"></div>
                    </div>
                    <div class="sec-pct" style="color:<?= $barColor ?>"><?= $sec['pct'] ?>%</div>
                </div>
                <div class="sec-status" style="background:<?= $barColor ?>15;color:<?= $barColor ?>">
                    <?= svgIcon($sec['pct'] === 100 ? 'check-circle' : ($sec['pct'] >= 50 ? 'warning' : 'x-circle'), '15px') ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Activités R2 -->
            <?php $actColor = $nbActivites > 0 ? '#1f6e4e' : '#ef4444'; ?>
            <div class="sec-row">
                <div class="sec-icon-wrap" style="background:#05966912;color:#059669">
                    <?= svgIcon('trending','16px') ?>
                </div>
                <div class="sec-info">
                    <div class="sec-label">Activités R2</div>
                    <div class="sec-sub"><?= $nbActivites ?> activité<?= $nbActivites > 1 ? 's' : '' ?> saisie<?= $nbActivites > 1 ? 's' : '' ?></div>
                </div>
                <div class="sec-bar-wrap">
                    <div class="sec-bar-bg">
                        <div class="sec-bar-fill" style="width:<?= $nbActivites > 0 ? 100 : 0 ?>%;background:<?= $actColor ?>"></div>
                    </div>
                    <div class="sec-pct" style="color:<?= $actColor ?>"><?= $nbActivites > 0 ? '100%' : '0%' ?></div>
                </div>
                <div class="sec-status" style="background:<?= $actColor ?>15;color:<?= $actColor ?>">
                    <?= svgIcon($nbActivites > 0 ? 'check-circle' : 'x-circle','15px') ?>
                </div>
            </div>

            <!-- Écritures -->
            <?php $ecrColor = $nbEcritures > 0 ? '#1f6e4e' : '#ef4444'; ?>
            <div class="sec-row">
                <div class="sec-icon-wrap" style="background:#2563eb12;color:#2563eb">
                    <?= svgIcon('book','16px') ?>
                </div>
                <div class="sec-info">
                    <div class="sec-label">Écritures validées</div>
                    <div class="sec-sub"><?= $nbEcritures ?> écriture<?= $nbEcritures > 1 ? 's' : '' ?> — exercice <?= $entreprise['exercice_courant'] ?></div>
                </div>
                <div class="sec-bar-wrap">
                    <div class="sec-bar-bg">
                        <div class="sec-bar-fill" style="width:<?= $nbEcritures > 0 ? 100 : 0 ?>%;background:<?= $ecrColor ?>"></div>
                    </div>
                    <div class="sec-pct" style="color:<?= $ecrColor ?>"><?= $nbEcritures > 0 ? '100%' : '0%' ?></div>
                </div>
                <div class="sec-status" style="background:<?= $ecrColor ?>15;color:<?= $ecrColor ?>">
                    <?= svgIcon($nbEcritures > 0 ? 'check-circle' : 'x-circle','15px') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Champs manquants -->
<?php
$manquants = [];
foreach ($sections as $secKey => $sec) {
    foreach ($sec['fields'] as $f) {
        if (empty($entreprise[$f])) {
            $labels = [
                'raison_sociale'=>'Raison sociale','ninea'=>'NINEA','rccm'=>'RCCM',
                'forme_juridique'=>'Forme juridique','sigle'=>'Sigle usuel',
                'numero_contribuable'=>'N° fiscal','telephone'=>'Téléphone',
                'email'=>'Email','adresse'=>'Adresse','ville'=>'Ville','pays'=>'Pays',
                'boite_postale'=>'Boîte postale','regime_fiscal'=>'Régime fiscal',
                'regime_tva'=>'Régime TVA','num_caisse_sociale'=>'N° Caisse sociale',
                'greffe'=>'Greffe','debut_exercice_social'=>'Début exercice social',
                'fin_exercice_social'=>'Fin exercice social','secteur_activite'=>'Secteur activité',
                'code_activite_naf'=>'Code activité NAF','description_activite'=>'Description activité',
                'dirigeant_nom'=>'Nom dirigeant','dirigeant_prenom'=>'Prénom dirigeant',
                'dirigeant_qualite'=>'Qualité dirigeant','expert_comptable_nom'=>'Nom expert-comptable',
                'expert_comptable_cabinet'=>'Cabinet expert-comptable','signataire_nom'=>'Nom signataire',
                'signataire_qualite'=>'Qualité signataire','banque_domiciliation'=>'Banque',
                'personne_contact'=>'Personne à contacter',
            ];
            $manquants[] = [
                'section'   => $sec['label'],
                'iconKey'   => $sectionIcons[$secKey] ?? 'building',
                'iconColor' => $sectionIconColors[$secKey] ?? '#1e3a5f',
                'champ'     => $labels[$f] ?? $f,
            ];
        }
    }
}
?>
<?php if (!empty($manquants)): ?>
<div class="checklist-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="color:#dc2626"><?= svgIcon('x-circle','18px') ?></div>
            <h3 style="font-size:13px;font-weight:700;color:var(--navy-dark);margin:0">Champs manquants</h3>
        </div>
        <span style="background:#fee2e2;color:#dc2626;font-size:13px;font-weight:700;padding:3px 10px;border-radius:20px"><?= count($manquants) ?> champ<?= count($manquants) > 1 ? 's' : '' ?></span>
    </div>
    <?php foreach ($manquants as $m): ?>
    <div class="check-item">
        <div class="ci-dot"><?= svgIcon('x-circle','14px') ?></div>
        <div class="ci-text">
            <strong><?= $m['champ'] ?></strong>
        </div>
        <div class="ci-section" style="color:<?= $m['iconColor'] ?>">
            <div style="color:<?= $m['iconColor'] ?>"><?= svgIcon($m['iconKey'],'13px') ?></div>
            <?= $m['section'] ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($scorePct === 100 && $nbActivites > 0 && $nbEcritures > 0): ?>
<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:16px;padding:28px;text-align:center;margin-top:16px">
    <div style="width:56px;height:56px;border-radius:16px;background:#1f6e4e;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:#fff">
        <?= svgIcon('shield-check','28px') ?>
    </div>
    <div style="font-size:14px;font-weight:700;color:#18583f">Dossier 100% conforme DGID</div>
    <div style="font-size:14px;color:#166534;margin-top:6px">Toutes les informations sont renseignées. Le fichier Excel est prêt à déposer.</div>
</div>
<?php endif; ?>
