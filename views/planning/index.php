<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Planning des missions</h1>
        <p class="page-subtitle"><?= count($missions) ?> mission<?= count($missions)>1?'s':'' ?> · <?= date('Y') ?></p>
    </div>
    <div class="page-header-actions" style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/planning/creer" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouvelle mission
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
    <?php
    $kpis = [
        ['En cours', $stats['en_cours'], 'navy', '#3b82f6'],
        ['Planifiées', $stats['planifiee'], 'gold', '#c9a96e'],
        ['En retard', $stats['retard'], 'orange', '#ef4444'],
        ['Terminées', $stats['termine'], 'green', '#22c55e'],
    ];
    foreach ($kpis as [$lbl, $val, $cls, $col]):
    ?>
    <div class="kpi-card">
        <div class="kpi-label"><?= $lbl ?></div>
        <div class="kpi-value" style="color:<?= $col ?>"><?= $val ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtres -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
    <form method="get" action="<?= APP_URL ?>/planning" style="display:flex;gap:8px;flex-wrap:wrap">
        <select name="statut" class="btn btn-outline btn-sm" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <?php foreach (['planifiee'=>'Planifiées','en_cours'=>'En cours','terminee'=>'Terminées','facturee'=>'Facturées','annulee'=>'Annulées'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($_GET['statut']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type" class="btn btn-outline btn-sm" onchange="this.form.submit()">
            <option value="">Tous types</option>
            <?php foreach (['comptabilite'=>'Comptabilité','audit'=>'Audit','fiscalite'=>'Fiscalité','paie'=>'Paie','conseil'=>'Conseil','autre'=>'Autre'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($_GET['type']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isAdmin() && !empty($users)): ?>
        <select name="user_id" class="btn btn-outline btn-sm" onchange="this.form.submit()">
            <option value="">Tous collaborateurs</option>
            <?php foreach ($users as $usr): ?>
            <option value="<?= $usr['id'] ?>" <?= ($_GET['user_id']??'')==$usr['id']?'selected':'' ?>><?= e($usr['prenom'].' '.$usr['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </form>
</div>

<!-- Liste missions -->
<?php if (empty($missions)): ?>
<div class="card" style="text-align:center;padding:60px">
    <h3 style="font-size:16px;color:var(--text-muted)">Aucune mission trouvée</h3>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px">
<?php
$statutColors = ['planifiee'=>['#f59e0b','#fffbeb'],'en_cours'=>['#3b82f6','#eff6ff'],'terminee'=>['#22c55e','#f0fdf4'],'facturee'=>['#c9a96e','#fdf8ef'],'annulee'=>['#9ca3af','#f9fafb']];
$typeIcons = ['comptabilite'=>'📊','audit'=>'🔍','fiscalite'=>'📋','paie'=>'💰','conseil'=>'💼','autre'=>'📁'];
foreach ($missions as $m):
    $jr = (int)$m['jours_restants'];
    $enRetard = in_array($m['statut'], ['planifiee','en_cours']) && $jr < 0;
    [$sc, $sbg] = $statutColors[$m['statut']] ?? ['#9ca3af','#f9fafb'];
    $progPct = ($m['budget_heures'] > 0) ? min(100, round($m['heures_passees']/$m['budget_heures']*100)) : 0;
?>
<div class="card" style="padding:20px;border-left:4px solid <?= $sc ?>;<?= $enRetard ? 'background:rgba(239,68,68,0.02)' : '' ?>">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                <span style="font-size:18px"><?= $typeIcons[$m['type']] ?? '📁' ?></span>
                <span style="font-size:15px;font-weight:600"><?= e($m['libelle']) ?></span>
                <span style="font-size:12px;background:<?= $sbg ?>;color:<?= $sc ?>;padding:3px 10px;border-radius:20px;font-weight:500"><?= ucfirst(str_replace('_',' ',$m['statut'])) ?></span>
                <?php if ($enRetard): ?><span class="badge badge-danger">En retard</span><?php endif; ?>
            </div>
            <div style="display:flex;gap:20px;font-size:13px;color:var(--text-muted)">
                <span>🏢 <?= e($m['raison_sociale']) ?></span>
                <span>👤 <?= e($m['prenom'].' '.$m['nom']) ?></span>
                <span>📅 <?= date('d/m/Y', strtotime($m['date_debut'])) ?></span>
                <?php if ($m['date_fin_prevue']): ?>
                <span style="<?= $enRetard ? 'color:var(--danger);font-weight:500' : '' ?>">⏰ Fin : <?= date('d/m/Y', strtotime($m['date_fin_prevue'])) ?>
                    <?php if (!in_array($m['statut'],['terminee','facturee','annulee'])): ?>
                    (<?= $jr >= 0 ? "dans $jr j" : abs($jr)." j de retard" ?>)
                    <?php endif; ?>
                </span>
                <?php endif; ?>
            </div>
            <?php if ($m['budget_heures'] > 0): ?>
            <div style="margin-top:12px">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-bottom:4px">
                    <span>Avancement : <?= $m['heures_passees'] ?>h / <?= $m['budget_heures'] ?>h</span>
                    <span><?= $progPct ?>%</span>
                </div>
                <div style="height:6px;background:var(--border);border-radius:4px;overflow:hidden">
                    <div style="height:100%;width:<?= $progPct ?>%;background:<?= $progPct >= 100 ? '#ef4444' : $sc ?>;border-radius:4px;transition:width 0.3s"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;flex-shrink:0">
            <span style="font-size:11px;font-family:monospace;color:var(--text-muted)"><?= e($m['reference']) ?></span>
            <?php if ($m['montant_forfait']): ?>
            <span style="font-size:14px;font-weight:600;color:var(--navy)"><?= number_format($m['montant_forfait'],0,',',' ') ?> F</span>
            <?php endif; ?>
            <?php if (!in_array($m['statut'], ['terminee','facturee','annulee'])): ?>
            <select onchange="updateStatut(<?= $m['id'] ?>, this.value)" class="btn btn-outline btn-sm" style="font-size:12px">
                <option>Changer statut</option>
                <?php foreach (['planifiee'=>'Planifiée','en_cours'=>'En cours','terminee'=>'Terminée','facturee'=>'Facturée','annulee'=>'Annulée'] as $v=>$l): ?>
                <option value="<?= $v ?>"><?= $l ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function updateStatut(id, statut) {
    if (!statut || statut === 'Changer statut') return;
    const fd = new FormData();
    fd.append('id', id);
    fd.append('statut', statut);
    fetch('<?= APP_URL ?>/planning/statut', {method:'POST',body:fd})
        .then(() => location.reload());
}
</script>
