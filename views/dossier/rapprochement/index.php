<?php
$moisNoms = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
$fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' F';
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Rapprochements bancaires</h1>
        <p class="page-subtitle">Suivi de concordance entre relevés et comptabilité</p>
    </div>
    <a href="<?= APP_URL ?>/dossier/rapprochement/creer?id=<?= $entreprise['id'] ?>" class="btn btn-ent btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nouveau rapprochement
    </a>
</div>

<?php if (empty($rapprochements)): ?>
<div class="empty-state">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
    <h3>Aucun rapprochement</h3>
    <p>Créez votre premier rapprochement bancaire.</p>
    <a href="<?= APP_URL ?>/dossier/rapprochement/creer?id=<?= $entreprise['id'] ?>" class="btn btn-ent" style="margin-top:12px">Démarrer</a>
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Période</th>
                <th>Compte banque</th>
                <th style="text-align:right">Solde relevé</th>
                <th style="text-align:right">Solde comptable</th>
                <th style="text-align:right">Écart</th>
                <th style="text-align:center">Statut</th>
                <th>Réalisé par</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rapprochements as $r): ?>
        <tr>
            <td style="font-weight:500"><?= ($moisNoms[$r['periode_mois']] ?? $r['periode_mois']) . ' ' . $r['periode_annee'] ?></td>
            <td><span class="badge badge-navy"><?= e($r['compte_banque']) ?></span></td>
            <td style="text-align:right"><?= $fmt($r['solde_releve']) ?></td>
            <td style="text-align:right"><?= $fmt($r['solde_comptable']) ?></td>
            <td style="text-align:right;color:<?= abs($r['ecart']) < 0.01 ? '#1f6e4e' : '#dc2626' ?>;font-weight:600">
                <?= $fmt(abs($r['ecart'])) ?><?= abs($r['ecart']) < 0.01 ? ' ✓' : '' ?>
            </td>
            <td style="text-align:center">
                <?php if ($r['statut'] === 'rapproche'): ?>
                <span style="padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:rgba(31,110,78,0.1);color:#1f6e4e">Rapproché</span>
                <?php else: ?>
                <span style="padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:rgba(31,110,78,0.1);color:#2563eb">En cours</span>
                <?php endif; ?>
            </td>
            <td style="font-size:14px"><?= e($r['prenom'] . ' ' . $r['nom']) ?></td>
            <td>
                <a href="<?= APP_URL ?>/dossier/rapprochement/voir?id=<?= $entreprise['id'] ?>&rap_id=<?= $r['id'] ?>" class="btn btn-outline btn-sm">Ouvrir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
