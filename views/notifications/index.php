<?php
$typeIcons = [
    'info'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>',
    'success' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'warning' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
    'danger'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>',
];
$typeColors = [
    'info'    => '#1f6e4e',
    'success' => '#1f6e4e',
    'warning' => '#f59e0b',
    'danger'  => '#ef4444',
];
?>
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Historique de vos alertes et messages</p>
    </div>
</div>

<?php if (empty($notifications)): ?>
<div class="card" style="text-align:center;padding:60px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:56px;height:56px;color:var(--border);margin:0 auto 16px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
    <h3 style="font-size:16px;font-weight:500;color:var(--text-muted)">Aucune notification</h3>
    <p style="font-size:14px;color:var(--text-muted);margin-top:8px">Vous êtes à jour !</p>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($notifications as $n):
        $color = $typeColors[$n['type']] ?? '#1f6e4e';
        $icon  = $typeIcons[$n['type']] ?? $typeIcons['info'];
    ?>
    <div style="display:flex;align-items:flex-start;gap:16px;padding:18px 24px;border-bottom:1px solid var(--border);<?= $n['lu'] ? 'opacity:0.6' : 'background:rgba(31,110,78,0.03)' ?>">
        <div style="width:38px;height:38px;border-radius:10px;background:<?= $color ?>1a;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <?php echo $icon ?>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:14px;font-weight:<?= $n['lu'] ? '400' : '600' ?>;color:var(--text)"><?= e($n['titre']) ?></div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:3px"><?= e($n['message']) ?></div>
            <?php if ($n['lien']): ?>
            <a href="<?= APP_URL . e($n['lien']) ?>" style="font-size:12px;color:var(--navy);text-decoration:none;margin-top:6px;display:inline-block">Voir →</a>
            <?php endif; ?>
        </div>
        <div style="font-size:11px;color:var(--text-muted);white-space:nowrap;flex-shrink:0"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></div>
        <?php if (!$n['lu']): ?>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--navy);flex-shrink:0;margin-top:6px"></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
