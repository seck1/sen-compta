<?php
$today = date('Y-m-d');

// Compute KPIs from DB entries
$kpi_reglees  = count(array_filter($echeances_db, fn($e) => $e['statut'] === 'regle'));
$kpi_attente  = count(array_filter($echeances_db, fn($e) => $e['statut'] === 'a_venir' && $e['date_echeance'] >= $today));
$kpi_retard   = count(array_filter($echeances_db, fn($e) => $e['statut'] !== 'regle' && $e['date_echeance'] < $today));
$kpi_total_db = count($echeances_db);

// Type badge colors
$typeColors = [
    'TVA'    => ['bg'=>'rgba(31,110,78,0.12)', 'color'=>'#2563eb'],
    'IS'     => ['bg'=>'rgba(184,146,63,0.12)', 'color'=>'#b8923f'],
    'IPRES'  => ['bg'=>'rgba(14,165,233,0.12)', 'color'=>'#0284c7'],
    'CFCE'   => ['bg'=>'rgba(245,158,11,0.12)', 'color'=>'#d97706'],
    'TF'     => ['bg'=>'rgba(239,68,68,0.12)',  'color'=>'#dc2626'],
    'Patente'=> ['bg'=>'rgba(168,131,63,0.12)', 'color'=>'#a8843f'],
    'Autre'  => ['bg'=>'rgba(107,114,128,0.12)','color'=>'#4b5563'],
    'CGU'    => ['bg'=>'rgba(184,146,63,0.12)', 'color'=>'#b8923f'],
];

// Group generated echeances by month for display
$byMonth = [];
foreach ($echeances as $ech) {
    $m = substr($ech['date'], 0, 7); // YYYY-MM
    $byMonth[$m][] = $ech;
}

// French month names
$moisFr = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin',
           '07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
?>

<?php if ($saved): ?>
<div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;color:#1f6e4e;font-size:14px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Calendrier fiscal <?= $annee ?> initialisé — <?= count($echeances) ?> échéances créées.
</div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Calendrier Fiscal</h1>
        <p class="page-subtitle">Échéancier <?= $annee ?> — Régime <strong><?= e($regime) ?></strong></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <!-- Year nav -->
        <a href="?id=<?= $entreprise['id'] ?>&annee=<?= $annee-1 ?>" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </a>
        <span style="padding:7px 14px;background:white;border:1px solid var(--border);border-radius:9px;font-size:14px;font-weight:600;color:var(--navy-dark);min-width:64px;text-align:center"><?= $annee ?></span>
        <a href="?id=<?= $entreprise['id'] ?>&annee=<?= $annee+1 ?>" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>

        <form method="post" action="<?= APP_URL ?>/dossier/fiscalite/calendrier/generer" style="margin:0">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="annee" value="<?= $annee ?>">
            <button type="submit" class="btn btn-primary btn-sm" title="Génère IPRES, TVA, IR, IS, CFCE, Patente pour <?= $annee ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <?= $kpi_total_db === 0 ? "Générer {$annee}" : "Compléter {$annee}" ?>
            </button>
        </form>
    </div>
</div>

<!-- KPI row -->
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
    <div class="kpi-card">
        <div class="kpi-label">Total échéances</div>
        <div class="kpi-value"><?= $kpi_total_db ?: count($echeances) ?></div>
        <div class="kpi-sub">dans le calendrier <?= $annee ?></div>
    </div>
    <div class="kpi-card" style="border-top-color:#1f6e4e">
        <div class="kpi-label" style="color:#1f6e4e">Réglées</div>
        <div class="kpi-value" style="color:#1f6e4e"><?= $kpi_reglees ?></div>
        <div class="kpi-sub">paiements confirmés</div>
    </div>
    <div class="kpi-card" style="border-top-color:#1f6e4e">
        <div class="kpi-label" style="color:#2563eb">En attente</div>
        <div class="kpi-value" style="color:#2563eb"><?= $kpi_attente ?></div>
        <div class="kpi-sub">à venir</div>
    </div>
    <div class="kpi-card" style="border-top-color:#ef4444">
        <div class="kpi-label" style="color:#dc2626">En retard</div>
        <div class="kpi-value" style="color:#dc2626"><?= $kpi_retard ?></div>
        <div class="kpi-sub">dépassées non réglées</div>
    </div>
</div>

<?php if ($kpi_total_db === 0 && !empty($echeances)): ?>
<!-- Preview mode (not yet initialized) -->
<div style="background:rgba(31,110,78,0.06);border:1px solid rgba(31,110,78,0.2);border-radius:12px;padding:16px 20px;margin-bottom:20px;font-size:14px;color:#1d4ed8">
    <strong>Aperçu du calendrier <?= $annee ?></strong> — Cliquez sur "Générer <?= $annee ?>" pour enregistrer les <?= count($echeances) ?> échéances en base et pouvoir les marquer comme réglées.
</div>
<?php endif; ?>

<!-- Timeline by month -->
<?php
$displaySource = $kpi_total_db > 0 ? 'db' : 'generated';
?>

<?php if ($displaySource === 'db'): ?>
<?php
// Group DB entries by month
$dbByMonth = [];
foreach ($echeances_db as $e) {
    $m = substr($e['date_echeance'], 0, 7);
    $dbByMonth[$m][] = $e;
}
ksort($dbByMonth);
foreach ($dbByMonth as $ym => $monthEchs):
    $parts = explode('-', $ym);
    $mLabel = ($moisFr[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
    $hasRetard = count(array_filter($monthEchs, fn($e)=>$e['statut']!=='regle'&&$e['date_echeance']<$today)) > 0;
?>
<div style="margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
        <div style="font-size:14px;font-weight:600;color:var(--navy-dark);min-width:120px"><?= $mLabel ?></div>
        <div style="flex:1;height:1px;background:var(--border)"></div>
        <?php if ($hasRetard): ?><span class="badge badge-danger">Retard</span><?php endif; ?>
    </div>
    <?php foreach ($monthEchs as $ech):
        $isRetard = $ech['statut'] !== 'regle' && $ech['date_echeance'] < $today;
        $bgRow = $ech['statut'] === 'regle' ? 'rgba(31,110,78,0.05)' : ($isRetard ? 'rgba(239,68,68,0.05)' : 'white');
        $tc = $typeColors[$ech['type']] ?? $typeColors['Autre'];
    ?>
    <div style="background:<?= $bgRow ?>;border:1px solid <?= $isRetard ? 'rgba(239,68,68,0.2)' : 'var(--border)' ?>;border-radius:12px;padding:14px 18px;margin-bottom:8px;display:flex;align-items:center;gap:14px">
        <span style="padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $tc['bg'] ?>;color:<?= $tc['color'] ?>;flex-shrink:0;min-width:56px;text-align:center"><?= e($ech['type']) ?></span>
        <div style="flex:1;min-width:0">
            <div style="font-size:14px;font-weight:500;color:var(--text)"><?= e($ech['libelle']) ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:2px">Échéance : <?= date('d/m/Y', strtotime($ech['date_echeance'])) ?></div>
        </div>
        <?php if ($ech['montant_estime']): ?>
        <div style="text-align:right;flex-shrink:0">
            <div style="font-size:14px;color:var(--text-muted)">Estimé</div>
            <div style="font-size:14px;font-family:monospace"><?= formatMontant($ech['montant_estime']) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($ech['montant_reel']): ?>
        <div style="text-align:right;flex-shrink:0">
            <div style="font-size:14px;color:var(--text-muted)">Réel</div>
            <div style="font-size:14px;font-family:monospace;font-weight:600;color:#1f6e4e"><?= formatMontant($ech['montant_reel']) ?></div>
        </div>
        <?php endif; ?>
        <div style="flex-shrink:0">
            <?php if ($ech['statut'] === 'regle'): ?>
            <span class="badge badge-success">Réglé le <?= date('d/m/Y', strtotime($ech['date_reglement'])) ?></span>
            <?php elseif ($isRetard): ?>
            <span class="badge badge-danger">En retard</span>
            <?php else: ?>
            <span class="badge badge-info">À venir</span>
            <?php endif; ?>
        </div>
        <?php if ($ech['statut'] !== 'regle'): ?>
        <!-- Mini form to mark as paid -->
        <button type="button" onclick="toggleForm(<?= $ech['id'] ?>)" style="padding:5px 12px;background:rgba(31,110,78,0.1);color:#1f6e4e;border:1px solid rgba(31,110,78,0.25);border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;flex-shrink:0">Marquer réglé</button>
        <?php endif; ?>
    </div>
    <?php if ($ech['statut'] !== 'regle'): ?>
    <div id="form_<?= $ech['id'] ?>" style="display:none;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px 18px;margin-bottom:8px;margin-top:-4px">
        <form method="post" action="<?= APP_URL ?>/dossier/fiscalite/calendrier/marquer" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="echeance_id" value="<?= $ech['id'] ?>">
            <input type="hidden" name="statut" value="regle">
            <div class="form-field" style="flex:1;min-width:140px">
                <label>Montant réel (FCFA)</label>
                <input type="number" name="montant_reel" min="0" step="1" placeholder="0" value="<?= round($ech['montant_estime'] ?? 0) ?>">
            </div>
            <div class="form-field" style="flex:1;min-width:140px">
                <label>Date de règlement</label>
                <input type="date" name="date_reglement" value="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" class="btn btn-sm" style="background:#1f6e4e;color:white;border:none;margin-bottom:1px">Confirmer</button>
            <button type="button" onclick="toggleForm(<?= $ech['id'] ?>)" class="btn btn-outline btn-sm" style="margin-bottom:1px">Annuler</button>
        </form>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php else: ?>
<!-- Preview from generated (not in DB yet) -->
<?php foreach ($byMonth as $ym => $monthEchs):
    $parts = explode('-', $ym);
    $mLabel = ($moisFr[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
?>
<div style="margin-bottom:20px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
        <div style="font-size:14px;font-weight:600;color:var(--navy-dark);min-width:120px"><?= $mLabel ?></div>
        <div style="flex:1;height:1px;background:var(--border)"></div>
    </div>
    <?php foreach ($monthEchs as $ech):
        $tc = $typeColors[$ech['type']] ?? $typeColors['Autre'];
        $isPast = $ech['date'] < $today;
    ?>
    <div style="background:<?= $isPast ? 'rgba(239,68,68,0.04)' : 'white' ?>;border:1px solid <?= $isPast ? 'rgba(239,68,68,0.15)' : 'var(--border)' ?>;border-radius:10px;padding:12px 16px;margin-bottom:6px;display:flex;align-items:center;gap:12px;opacity:0.8">
        <span style="padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $tc['bg'] ?>;color:<?= $tc['color'] ?>;flex-shrink:0;min-width:56px;text-align:center"><?= e($ech['type']) ?></span>
        <div style="flex:1">
            <div style="font-size:14px;color:var(--text)"><?= e($ech['libelle']) ?></div>
            <div style="font-size:14px;color:var(--text-muted)">Échéance : <?= date('d/m/Y', strtotime($ech['date'])) ?></div>
        </div>
        <?php if ($ech['montant_estime']): ?>
        <div style="font-family:monospace;font-size:14px;color:var(--text-muted)"><?= formatMontant($ech['montant_estime']) ?></div>
        <?php endif; ?>
        <?php if ($isPast): ?><span class="badge badge-danger">Passé</span><?php else: ?><span class="badge badge-info">À venir</span><?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php
// Contrats CDD / Stage / Intérim expirant dans les 60 jours
$db = getDB();
$stmt_cdd = $db->prepare("
    SELECT *, DATEDIFF(date_fin_contrat, CURDATE()) as jours_restants
    FROM employes
    WHERE entreprise_id=? AND type_contrat IN ('CDD','Stage','Interim')
      AND statut='actif' AND date_fin_contrat IS NOT NULL
      AND date_fin_contrat BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    ORDER BY date_fin_contrat ASC
");
$stmt_cdd->execute([$entreprise['id']]);
$cdd_expirants = $stmt_cdd->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if(!empty($cdd_expirants)): ?>
<div class="card" style="margin-top:24px;padding:0;overflow:hidden;border-top:3px solid #f59e0b">
    <div style="padding:14px 20px;background:#fffbeb;border-bottom:1px solid #fde68a;display:flex;align-items:center;justify-content:space-between">
        <div style="font-weight:700;font-size:14px;color:#92400e">
            ⚠️ Contrats expirant bientôt — <?= count($cdd_expirants) ?> employé(s)
        </div>
        <a href="<?= APP_URL ?>/dossier/rh?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Voir RH →</a>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:#f8f9fb;border-bottom:1px solid #eee">
                <th style="padding:9px 16px;text-align:left;font-weight:700;color:#555">Employé</th>
                <th style="padding:9px 16px;text-align:left;font-weight:700;color:#555">Poste</th>
                <th style="padding:9px 16px;text-align:left;font-weight:700;color:#555">Type</th>
                <th style="padding:9px 16px;text-align:left;font-weight:700;color:#555">Fin de contrat</th>
                <th style="padding:9px 16px;text-align:center;font-weight:700;color:#555">Délai</th>
                <th style="padding:9px 16px;text-align:center;font-weight:700;color:#555">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($cdd_expirants as $emp):
            $j = (int)$emp['jours_restants'];
            $bg_row = $j < 0 ? '#fef2f2' : ($j <= 7 ? '#fff7ed' : '#fff');
            $badge_col = $j < 0 ? '#b91c1c' : ($j <= 7 ? '#c2410c' : '#92400e');
            $badge_bg  = $j < 0 ? '#fee2e2' : ($j <= 7 ? '#fed7aa' : '#fef3c7');
            $label = $j < 0 ? abs($j).' j de retard' : ($j === 0 ? "Aujourd'hui" : $j.' jours');
        ?>
        <tr style="background:<?= $bg_row ?>;border-bottom:1px solid #eee">
            <td style="padding:10px 16px;font-weight:600;color:#1a1a1a"><?= e($emp['prenom'].' '.$emp['nom']) ?></td>
            <td style="padding:10px 16px;color:#555"><?= e($emp['poste'] ?: '—') ?></td>
            <td style="padding:10px 16px">
                <span style="display:inline-block;padding:2px 8px;border-radius:8px;font-size:14px;font-weight:700;background:#fef3c7;color:#92400e"><?= e($emp['type_contrat']) ?></span>
            </td>
            <td style="padding:10px 16px;color:#333;font-weight:600"><?= date('d/m/Y', strtotime($emp['date_fin_contrat'])) ?></td>
            <td style="padding:10px 16px;text-align:center">
                <span style="display:inline-block;padding:3px 10px;border-radius:10px;font-size:14px;font-weight:700;background:<?= $badge_bg ?>;color:<?= $badge_col ?>"><?= $label ?></span>
            </td>
            <td style="padding:10px 16px;text-align:center">
                <a href="<?= APP_URL ?>/dossier/rh/employe?id=<?= $entreprise['id'] ?>&employe_id=<?= $emp['id'] ?>" style="font-size:13px;color:#1e3a5f;text-decoration:none;padding:4px 10px;border:1px solid #1e3a5f33;border-radius:5px">Fiche →</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function toggleForm(id) {
    const el = document.getElementById('form_' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
