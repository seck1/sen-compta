<?php
// Variables disponibles: $entreprise, $exercice, $checks (tableau de vérifications)
// $checks est un tableau avec des items: ['label', 'status' => 'ok|warning|error', 'detail', 'link']
?>
<div class="page-header">
    <div>
        <div class="page-title">Checklist de clôture</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Exercice <?= $exercice ?></div>
    </div>
    <a href="<?= APP_URL ?>/dossier/cloture?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Clôturer l'exercice</a>
</div>

<?php
$nb_ok      = count(array_filter($checks, fn($c) => $c['status'] === 'ok'));
$nb_warning = count(array_filter($checks, fn($c) => $c['status'] === 'warning'));
$nb_error   = count(array_filter($checks, fn($c) => $c['status'] === 'error'));
$total      = count($checks);
$score      = $total > 0 ? round($nb_ok / $total * 100) : 0;
?>

<!-- Score global -->
<div class="card" style="padding:24px;margin-bottom:20px;display:flex;align-items:center;gap:24px">
    <div style="width:80px;height:80px;border-radius:50%;background:<?= $score==100?'#16a34a':($score>=70?'#f59e0b':'#dc2626') ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <span style="font-size:22px;font-weight:700;color:#fff"><?= $score ?>%</span>
    </div>
    <div style="flex:1">
        <div style="font-size:18px;font-weight:700;margin-bottom:6px">
            <?= $score==100 ? '✅ Dossier prêt pour la clôture' : ($score>=70 ? '⚠️ Vérifications en cours' : '❌ Actions requises avant clôture') ?>
        </div>
        <div style="display:flex;gap:16px;font-size:16px">
            <span style="color:#16a34a;font-weight:600">✓ <?= $nb_ok ?> validé<?= $nb_ok>1?'s':'' ?></span>
            <?php if($nb_warning): ?><span style="color:#f59e0b;font-weight:600">⚠ <?= $nb_warning ?> avertissement<?= $nb_warning>1?'s':'' ?></span><?php endif; ?>
            <?php if($nb_error): ?><span style="color:#dc2626;font-weight:600">✗ <?= $nb_error ?> erreur<?= $nb_error>1?'s':'' ?></span><?php endif; ?>
        </div>
        <div style="margin-top:10px;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?= $score ?>%;background:<?= $score==100?'#16a34a':($score>=70?'#f59e0b':'#dc2626') ?>;border-radius:4px;transition:width .5s"></div>
        </div>
    </div>
</div>

<!-- Checklist items -->
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach($checks as $i => $check):
        $colors = ['ok'=>['#16a34a','#f0fdf4','#bbf7d0'], 'warning'=>['#d97706','#fffbeb','#fde68a'], 'error'=>['#dc2626','#fef2f2','#fecaca']];
        $c = $colors[$check['status']];
        $icon = $check['status']==='ok' ? '✓' : ($check['status']==='warning' ? '⚠' : '✗');
    ?>
    <div style="display:flex;align-items:center;gap:16px;padding:14px 20px;border-bottom:1px solid var(--border);background:<?= $i%2==0?'#fff':'#fafbfc' ?>">
        <div style="width:28px;height:28px;border-radius:50%;background:<?= $c[1] ?>;border:2px solid <?= $c[2] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:17px;font-weight:700;color:<?= $c[0] ?>">
            <?= $icon ?>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:17px"><?= e($check['label']) ?></div>
            <?php if(!empty($check['detail'])): ?>
            <div style="font-size:15px;color:var(--text-muted);margin-top:2px"><?= e($check['detail']) ?></div>
            <?php endif; ?>
        </div>
        <?php if(!empty($check['link'])): ?>
        <a href="<?= $check['link'] ?>" style="padding:5px 14px;border-radius:8px;background:var(--navy);color:#fff;font-size:15px;font-weight:600;text-decoration:none;white-space:nowrap">
            Corriger →
        </a>
        <?php else: ?>
        <span style="padding:3px 12px;border-radius:20px;font-size:15px;font-weight:600;background:<?= $c[1] ?>;color:<?= $c[0] ?>;border:1px solid <?= $c[2] ?>">
            <?= $check['status']==='ok'?'OK':($check['status']==='warning'?'Attention':'Requis') ?>
        </span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
