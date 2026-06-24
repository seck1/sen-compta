<?php
function statutDevisBadge(string $s): string {
    return match($s) {
        'brouillon'=>'<span class="badge badge-gray">Brouillon</span>',
        'envoye'=>'<span class="badge badge-blue">Envoyé</span>',
        'accepte'=>'<span class="badge badge-green">Accepté</span>',
        'refuse'=>'<span class="badge badge-red">Refusé</span>',
        'expire'=>'<span class="badge badge-orange">Expiré</span>',
        'converti'=>'<span class="badge badge-purple">Converti</span>',
        default=>'<span class="badge badge-gray">'.e($s).'</span>',
    };
}
?>
<style>
.list-root{padding:32px 36px;max-width:1200px}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px}
.page-header h1{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--navy-dark)}
.page-header p{font-size:13px;color:var(--text-muted);margin-top:3px}
.stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px}
.stat-card{background:#fff;border-radius:12px;border:1px solid var(--border);padding:16px;text-align:center}
.stat-num{font-size:22px;font-weight:700;color:var(--navy-dark);font-family:'Playfair Display',serif}
.stat-label{font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-top:4px}
.filters-bar{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.filter-btn{padding:7px 16px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--text-muted);text-decoration:none;transition:all 0.2s}
.filter-btn:hover,.filter-btn.active{background:var(--navy);color:#fff;border-color:var(--navy)}
.table-card{background:#fff;border-radius:16px;border:1px solid var(--border);overflow:hidden}
table.tbl{width:100%;border-collapse:collapse}
table.tbl th{font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);font-weight:600;padding:12px 16px;background:#fafbfc;border-bottom:1px solid var(--border);text-align:left}
table.tbl td{padding:13px 16px;font-size:13px;border-bottom:1px solid #f3f4f6}
table.tbl tr:last-child td{border-bottom:none}
table.tbl tr:hover td{background:#fafbfc}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-gray{background:#f3f4f6;color:#6b7280}
.badge-blue{background:#dbeafe;color:#2563eb}
.badge-green{background:#dcfce7;color:#16a34a}
.badge-red{background:#fee2e2;color:#dc2626}
.badge-orange{background:#fef3c7;color:#d97706}
.badge-purple{background:#ede9fe;color:#7c3aed}
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s}
.btn-primary{background:var(--navy);color:#fff}
.btn-gold{background:var(--gold);color:var(--navy-dark)}
.btn-sm{padding:5px 12px;font-size:12px}
.empty-state{text-align:center;padding:60px;color:var(--text-muted)}
</style>
<div class="list-root">
    <div class="page-header">
        <div><h1>Devis</h1><p><?= count($devisList) ?> devis au total</p></div>
        <a href="<?= APP_URL ?>/commercial/devis/nouveau" class="btn btn-gold">+ Nouveau devis</a>
    </div>
    <div class="stats-row">
        <?php
        $allStats = ['brouillon'=>['Brouillons','#6b7280'],'envoye'=>['Envoyés','#2563eb'],'accepte'=>['Acceptés','#16a34a'],'refuse'=>['Refusés','#dc2626'],'converti'=>['Convertis','#7c3aed']];
        foreach ($allStats as $k => [$label, $color]): ?>
        <div class="stat-card">
            <div class="stat-num" style="color:<?= $color ?>"><?= $statsMap[$k]['nb'] ?? 0 ?></div>
            <div class="stat-label"><?= $label ?></div>
            <?php if (($statsMap[$k]['total'] ?? 0) > 0): ?>
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px"><?= number_format(($statsMap[$k]['total']??0)/1000,'0',',',' ') ?>k FCFA</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="filters-bar">
        <a href="<?= APP_URL ?>/commercial/devis" class="filter-btn <?= !isset($_GET['statut']) ? 'active' : '' ?>">Tous</a>
        <?php foreach (['brouillon'=>'Brouillons','envoye'=>'Envoyés','accepte'=>'Acceptés','refuse'=>'Refusés','converti'=>'Convertis'] as $k => $v): ?>
        <a href="<?= APP_URL ?>/commercial/devis?statut=<?= $k ?>" class="filter-btn <?= ($_GET['statut']??'') === $k ? 'active' : '' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>
    <div class="table-card">
        <?php if (empty($devisList)): ?>
        <div class="empty-state"><div style="font-size:40px;margin-bottom:12px">📋</div><div>Aucun devis</div></div>
        <?php else: ?>
        <table class="tbl">
            <thead><tr><th>N° Devis</th><th>Client/Prospect</th><th>Objet</th><th>Date</th><th>Validité</th><th>Montant TTC</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($devisList as $d): ?>
            <tr>
                <td><a href="<?= APP_URL ?>/commercial/devis/voir?id=<?= $d['id'] ?>" style="color:var(--navy);font-weight:700"><?= e($d['numero']) ?></a></td>
                <td style="font-weight:500"><?= e($d['raison_sociale']) ?><div style="font-size:11px;color:var(--text-muted)"><?= e($d['ville']) ?></div></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($d['objet']) ?></td>
                <td><?= date('d/m/Y', strtotime($d['date_devis'])) ?></td>
                <td><?= $d['date_validite'] ? date('d/m/Y', strtotime($d['date_validite'])) : '—' ?></td>
                <td style="font-weight:700"><?= number_format($d['montant_ttc'],0,',',' ') ?> F</td>
                <td><?= statutDevisBadge($d['statut']) ?></td>
                <td><a href="<?= APP_URL ?>/commercial/devis/voir?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary">Voir</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
