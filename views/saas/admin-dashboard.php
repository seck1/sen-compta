<?php
$pageTitle = 'Super Admin';
$activePage = 'superadmin';
ob_start();
?>
<div class="page-header" style="margin-bottom:28px;">
  <h1 style="font-size:22px;font-weight:700;color:var(--text);">Super Admin <span style="color:var(--gold);font-size:14px;font-weight:500;margin-left:8px;">SenCompta Platform</span></h1>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:32px;">
  <?php
  $cards = [
    ['label'=>'Cabinets total','value'=>$stats['total_cabinets'],'color'=>'var(--info)','icon'=>'🏢'],
    ['label'=>'Actifs','value'=>$stats['actifs'],'color'=>'var(--success)','icon'=>'✅'],
    ['label'=>'En essai','value'=>$stats['essai'],'color'=>'var(--warning)','icon'=>'⏳'],
    ['label'=>'Suspendus','value'=>$stats['suspendus'],'color'=>'var(--danger)','icon'=>'🔴'],
    ['label'=>'MRR (FCFA)','value'=>number_format($stats['mrr'],0,',',' '),'color'=>'var(--gold)','icon'=>'💰'],
    ['label'=>'Paiements en attente','value'=>$stats['paiements_attente'],'color'=>'var(--danger)','icon'=>'💳'],
    ['label'=>'Demandes','value'=>$stats['demandes_attente'],'color'=>'var(--warning)','icon'=>'📋'],
  ];
  foreach ($cards as $c): ?>
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:8px;">
    <div style="font-size:22px;"><?= $c['icon'] ?></div>
    <div style="font-size:22px;font-weight:700;color:<?= $c['color'] ?>;"><?= $c['value'] ?></div>
    <div style="font-size:12px;color:var(--text-muted);"><?= $c['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Liens rapides -->
<div style="display:flex;gap:12px;margin-bottom:28px;flex-wrap:wrap;">
  <a href="<?= APP_URL ?>/superadmin/cabinets" style="background:var(--navy-dark);color:#fff;padding:10px 20px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;">🏢 Gérer les cabinets</a>
  <a href="<?= APP_URL ?>/superadmin/paiements" style="background:var(--gold);color:var(--navy-dark);padding:10px 20px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;">💳 Paiements <?= $stats['paiements_attente'] > 0 ? '('.$stats['paiements_attente'].')' : '' ?></a>
  <a href="<?= APP_URL ?>/superadmin/demandes" style="background:var(--bg-card);color:var(--text);border:1px solid var(--border);padding:10px 20px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;">📋 Demandes <?= $stats['demandes_attente'] > 0 ? '('.$stats['demandes_attente'].')' : '' ?></a>
</div>

<!-- Derniers cabinets -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;">
  <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:15px;font-weight:600;">Derniers cabinets inscrits</span>
    <a href="<?= APP_URL ?>/superadmin/cabinets" style="font-size:12px;color:var(--gold);text-decoration:none;">Voir tout →</a>
  </div>
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--bg);">
          <th style="padding:12px 16px;text-align:left;color:var(--text-muted);font-weight:500;">Cabinet</th>
          <th style="padding:12px 16px;text-align:left;color:var(--text-muted);font-weight:500;">Plan</th>
          <th style="padding:12px 16px;text-align:center;color:var(--text-muted);font-weight:500;">Entreprises</th>
          <th style="padding:12px 16px;text-align:center;color:var(--text-muted);font-weight:500;">Users</th>
          <th style="padding:12px 16px;text-align:left;color:var(--text-muted);font-weight:500;">Statut</th>
          <th style="padding:12px 16px;text-align:left;color:var(--text-muted);font-weight:500;">Inscrit le</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($cabinets, 0, 10) as $c): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:14px 16px;">
            <div style="font-weight:600;color:var(--text);"><?= e($c['nom']) ?></div>
            <div style="font-size:11px;color:var(--text-muted);"><?= e($c['email']) ?></div>
          </td>
          <td style="padding:14px 16px;"><span style="background:rgba(201,162,39,0.1);color:var(--gold);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;"><?= e($c['plan_nom']) ?></span></td>
          <td style="padding:14px 16px;text-align:center;font-weight:600;"><?= $c['nb_entreprises'] ?></td>
          <td style="padding:14px 16px;text-align:center;"><?= $c['nb_users'] ?></td>
          <td style="padding:14px 16px;">
            <?php
            $colors = ['actif'=>'#22c55e','essai'=>'#f59e0b','suspendu'=>'#ef4444','expire'=>'#6b7280','annule'=>'#6b7280'];
            $col = $colors[$c['statut']] ?? '#6b7280';
            ?>
            <span style="color:<?= $col ?>;font-size:12px;font-weight:600;">● <?= ucfirst($c['statut']) ?></span>
          </td>
          <td style="padding:14px 16px;color:var(--text-muted);font-size:12px;"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_ROOT . '/views/layouts/main.php';
?>
