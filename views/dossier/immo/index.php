<?php
$fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' F';
$typeLabels = ['corporelle'=>'Corporelles','incorporelle'=>'Incorporelles','financiere'=>'Financières'];
$amort_calcule = isset($_GET['amort_calcule']);
$exercice = $exercice ?? ($entreprise['exercice_courant'] ?? date('Y'));
// Alias totaux from controller variables
$total_brut  = $total_brut ?? 0;
$total_amort = $total_amort ?? 0;
$total_net   = $total_net ?? 0;
?>

<?php if ($amort_calcule): ?>
<div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;color:#1f6e4e;font-size:16px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Amortissements recalculés pour l'exercice <?= $exercice ?>.
</div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Immobilisations</h1>
        <p class="page-subtitle">Tableau des actifs immobilisés — Exercice <?= $exercice ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <form method="post" action="<?= APP_URL ?>/dossier/immo/amortissements" style="display:inline">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="exercice" value="<?= $exercice ?>">
            <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Recalculer les amortissements pour <?= $exercice ?> ?')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7l-.258-.26a3 3 0 01-.51-3.748c.294-.613.791-1.143 1.422-1.524" /></svg>
                Calculer amortissements <?= $exercice ?>
            </button>
        </form>
        <a href="<?= APP_URL ?>/dossier/immo/creer?id=<?= $entreprise['id'] ?>" class="btn btn-ent btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouvelle immobilisation
        </a>
    </div>
</div>

<!-- KPI -->
<div class="kpi-grid" style="margin-bottom:24px">
    <div class="kpi-card">
        <div class="kpi-label">Valeur brute totale</div>
        <div class="kpi-value" style="font-size:20px"><?= $fmt($total_brut) ?></div>
        <div class="kpi-sub"><?= array_sum(array_map('count', $grouped)) ?> immobilisation(s)</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Amortissements cumulés</div>
        <div class="kpi-value" style="font-size:20px;color:#dc2626"><?= $fmt($total_amort) ?></div>
        <?php $pct = $total_brut > 0 ? round($total_amort/$total_brut*100) : 0; ?>
        <div class="kpi-sub"><?= $pct ?>% amorti</div>
    </div>
    <div class="kpi-card" style="border-top:3px solid var(--gold)">
        <div class="kpi-label">Valeur nette comptable</div>
        <div class="kpi-value" style="font-size:20px;color:var(--navy)"><?= $fmt($total_net) ?></div>
        <div class="kpi-sub">VNC exercice <?= $exercice ?></div>
    </div>
</div>

<?php
$hasAny = !empty(array_filter($grouped, fn($items) => !empty($items)));
?>
<?php if (!$hasAny): ?>
<div class="empty-state">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
    <h3>Aucune immobilisation</h3>
    <p>Ajoutez vos actifs immobilisés pour commencer le suivi des amortissements.</p>
    <a href="<?= APP_URL ?>/dossier/immo/creer?id=<?= $entreprise['id'] ?>" class="btn btn-ent" style="margin-top:12px">Ajouter une immobilisation</a>
</div>
<?php else: ?>

<?php foreach ($grouped as $type => $items): ?>
<?php if (empty($items)) continue; ?>
<?php
$g_brut  = array_sum(array_column($items, 'valeur_brute'));
$g_amort = array_sum(array_column($items, 'amort_cumule'));
$g_net   = array_sum(array_column($items, 'valeur_nette'));
?>
<div class="table-wrap" style="margin-bottom:20px">
    <div class="table-header">
        <div class="table-title"><?= $typeLabels[$type] ?? ucfirst($type) ?></div>
        <div style="font-size:16px;color:var(--text-muted)">
            Brut: <strong><?= $fmt($g_brut) ?></strong> | Amort: <strong><?= $fmt($g_amort) ?></strong> | VNC: <strong style="color:var(--navy)"><?= $fmt($g_net) ?></strong>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Compte</th>
                <th>Date acq.</th>
                <th style="text-align:right">Valeur brute</th>
                <th style="text-align:right">Amort. cumulé</th>
                <th style="text-align:right">VNC</th>
                <th style="text-align:center">Durée</th>
                <th style="text-align:center">Méthode</th>
                <th style="text-align:center">Statut</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $immo): ?>
        <tr>
            <td>
                <div style="font-weight:500;color:var(--text)"><?= e($immo['designation']) ?></div>
                <?php if ($immo['categorie']): ?><div style="font-size:14px;color:var(--text-muted)"><?= e($immo['categorie']) ?></div><?php endif; ?>
            </td>
            <td><span class="badge badge-navy"><?= e($immo['compte_numero']) ?></span></td>
            <td style="font-size:16px"><?= date('d/m/Y', strtotime($immo['date_acquisition'])) ?></td>
            <td style="text-align:right"><?= $fmt($immo['valeur_brute']) ?></td>
            <td style="text-align:right;color:#dc2626"><?= $fmt($immo['amort_cumule']) ?></td>
            <td style="text-align:right;font-weight:600;color:var(--navy)"><?= $fmt($immo['valeur_nette']) ?></td>
            <td style="text-align:center"><?= $immo['duree_amort'] ?> ans</td>
            <td style="text-align:center">
                <span style="padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $immo['methode_amort']==='lineaire' ? 'rgba(31,110,78,0.1)' : 'rgba(184,146,63,0.1)' ?>;color:<?= $immo['methode_amort']==='lineaire' ? '#2563eb' : '#b8923f' ?>">
                    <?= ucfirst($immo['methode_amort']) ?>
                </span>
            </td>
            <td style="text-align:center">
                <?php $sc = ['actif'=>['rgba(31,110,78,0.1)','#1f6e4e'],'cede'=>['rgba(239,68,68,0.1)','#dc2626'],'mis_au_rebut'=>['rgba(107,114,128,0.1)','#4b5563']][$immo['statut']] ?? ['rgba(107,114,128,0.1)','#4b5563']; ?>
                <span style="padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $sc[0] ?>;color:<?= $sc[1] ?>"><?= ucfirst(str_replace('_',' ',$immo['statut'])) ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<?php endif; ?>
