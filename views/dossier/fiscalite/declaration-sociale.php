<?php
$moisNoms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$moisLabel = ($moisNoms[$mois] ?? $mois) . ' ' . $annee;
$isDepose = $declaration_existante && $declaration_existante['statut'] !== 'brouillon';
?>

<?php if ($saved): ?>
<div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;color:#1f6e4e;font-size:14px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Déclaration sociale validée pour <?= e($moisLabel) ?>.
</div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Déclarations Sociales</h1>
        <p class="page-subtitle">IPRES · CSS · IPM · CFCE — <?= e($moisLabel) ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <!-- Period selector -->
        <form method="get" style="display:flex;align-items:center;gap:6px">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <select name="mois" style="padding:7px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:white">
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m==$mois?'selected':'' ?>><?= $moisNoms[$m] ?></option>
                <?php endfor; ?>
            </select>
            <select name="annee" style="padding:7px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:white">
                <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                <option value="<?= $y ?>" <?= $y==$annee?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Voir</button>
        </form>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimer
        </button>
    </div>
</div>

<?php if (empty($bulletins)): ?>
<div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:14px;padding:40px;text-align:center">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:44px;height:44px;color:var(--warning);margin:0 auto 16px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
    <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px">Aucun bulletin généré pour <?= e($moisLabel) ?></div>
    <div style="font-size:14px;color:var(--text-muted);margin-bottom:20px">Générez les bulletins de paie avant de valider la déclaration sociale.</div>
    <a href="<?= APP_URL ?>/dossier/rh/bulletin/creer?id=<?= $entreprise['id'] ?>" class="btn btn-primary btn-sm">Créer des bulletins</a>
</div>
<?php else: ?>

<!-- Summary KPIs -->
<div class="kpi-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:20px">
    <div class="kpi-card">
        <div class="kpi-label">IPRES Total</div>
        <div class="kpi-value" style="font-size:20px"><?= formatMontant($totaux['ipres_total']) ?></div>
        <div class="kpi-sub" style="color:var(--text-muted);font-size:14px">Sal: <?= formatMontant($totaux['ipres_salarie']) ?> · Pat: <?= formatMontant($totaux['ipres_patronal']) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">CSS Total</div>
        <div class="kpi-value" style="font-size:20px"><?= formatMontant($totaux['css_total']) ?></div>
        <div class="kpi-sub" style="font-size:14px">Acc: <?= formatMontant($totaux['css_accidents']) ?> · Prest: <?= formatMontant($totaux['css_prestations']) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">IPM Total</div>
        <div class="kpi-value" style="font-size:20px"><?= formatMontant($totaux['ipm_total']) ?></div>
        <div class="kpi-sub" style="font-size:14px">Sal: <?= formatMontant($totaux['ipm_salarie']) ?> · Pat: <?= formatMontant($totaux['ipm_patronal']) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">CFCE</div>
        <div class="kpi-value" style="font-size:20px"><?= formatMontant($totaux['cfce']) ?></div>
        <div class="kpi-sub"><?= $totaux['nb_salaries'] ?> salarié(s)</div>
    </div>
    <div class="kpi-card" style="border-color:var(--gold);background:rgba(201,169,110,0.06)">
        <div class="kpi-label" style="color:var(--gold-dark)">Total à payer</div>
        <div class="kpi-value" style="font-size:20px;color:var(--navy-dark)"><?= formatMontant($totaux['total_a_payer']) ?></div>
        <div class="kpi-sub">Masse: <?= formatMontant($totaux['masse_salariale']) ?></div>
    </div>
</div>

<!-- Salariés table -->
<div class="table-wrap" style="margin-bottom:20px">
    <div class="table-header">
        <span class="table-title">Détail par salarié — <?= e($moisLabel) ?></span>
        <?php if ($isDepose): ?>
        <span class="badge badge-success">Déclarée</span>
        <?php endif; ?>
    </div>
    <table>
        <thead>
            <tr>
                <th>Salarié</th>
                <th style="text-align:right">Brut</th>
                <th style="text-align:right">IPRES Sal.</th>
                <th style="text-align:right">IPRES Pat.</th>
                <th style="text-align:right">CSS Acc.</th>
                <th style="text-align:right">CSS Prest.</th>
                <th style="text-align:right">IPM Sal.</th>
                <th style="text-align:right">IPM Pat.</th>
                <th style="text-align:right">CFCE</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bulletins as $b): ?>
            <tr>
                <td>
                    <div style="font-weight:500"><?= e($b['prenom'].' '.$b['nom']) ?></div>
                </td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['salaire_brut']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['ipres_salarie']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['ipres_patronal']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['css_accident']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['css_prestation']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['ipm_salarie']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['ipm_patronal']) ?></td>
                <td style="text-align:right;font-family:monospace;font-size:13px"><?= formatMontant($b['cfce']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:rgba(30,58,95,0.06);font-weight:700">
                <td>TOTAL (<?= $totaux['nb_salaries'] ?> salarié(s))</td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['masse_salariale']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['ipres_salarie']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['ipres_patronal']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['css_accidents']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['css_prestations']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['ipm_salarie']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['ipm_patronal']) ?></td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totaux['cfce']) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Breakdown salarié vs patronal -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div class="card">
        <div style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px">IPRES</div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--text-muted)">Part salarié</span>
            <span style="font-family:monospace"><?= formatMontant($totaux['ipres_salarie']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--text-muted)">Part patronale</span>
            <span style="font-family:monospace"><?= formatMontant($totaux['ipres_patronal']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:14px;font-weight:600">
            <span>Total IPRES</span>
            <span style="font-family:monospace;color:var(--navy-dark)"><?= formatMontant($totaux['ipres_total']) ?></span>
        </div>
    </div>
    <div class="card">
        <div style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px">CSS</div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--text-muted)">Accidents travail</span>
            <span style="font-family:monospace"><?= formatMontant($totaux['css_accidents']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--text-muted)">Prestations familiales</span>
            <span style="font-family:monospace"><?= formatMontant($totaux['css_prestations']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:14px;font-weight:600">
            <span>Total CSS</span>
            <span style="font-family:monospace;color:var(--navy-dark)"><?= formatMontant($totaux['css_total']) ?></span>
        </div>
    </div>
    <div class="card">
        <div style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px">IPM</div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--text-muted)">Part salarié</span>
            <span style="font-family:monospace"><?= formatMontant($totaux['ipm_salarie']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--text-muted)">Part patronale</span>
            <span style="font-family:monospace"><?= formatMontant($totaux['ipm_patronal']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:14px;font-weight:600">
            <span>Total IPM</span>
            <span style="font-family:monospace;color:var(--navy-dark)"><?= formatMontant($totaux['ipm_total']) ?></span>
        </div>
    </div>
</div>

<!-- Total récap + action -->
<div style="background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:14px;padding:24px 28px;display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <div>
        <div style="font-size:13px;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,0.6);margin-bottom:6px">Total à déclarer et payer</div>
        <div style="font-size:36px;font-family:'Cormorant Garamond',serif;font-weight:600;color:var(--gold)"><?= formatMontant($totaux['total_a_payer']) ?></div>
        <div style="font-size:13px;color:rgba(255,255,255,0.5);margin-top:4px">Échéance : 15 <?= $moisNoms[$mois==12?1:$mois+1] ?> <?= $mois==12?$annee+1:$annee ?></div>
    </div>
    <?php if (!$isDepose): ?>
    <form method="post" action="<?= APP_URL ?>/dossier/fiscalite/declaration-sociale/store">
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
        <input type="hidden" name="mois" value="<?= $mois ?>">
        <input type="hidden" name="annee" value="<?= $annee ?>">
        <input type="hidden" name="nb_salaries" value="<?= $totaux['nb_salaries'] ?>">
        <input type="hidden" name="masse_salariale" value="<?= $totaux['masse_salariale'] ?>">
        <input type="hidden" name="ipres_salarie" value="<?= $totaux['ipres_salarie'] ?>">
        <input type="hidden" name="ipres_patronal" value="<?= $totaux['ipres_patronal'] ?>">
        <input type="hidden" name="ipres_total" value="<?= $totaux['ipres_total'] ?>">
        <input type="hidden" name="css_accidents" value="<?= $totaux['css_accidents'] ?>">
        <input type="hidden" name="css_prestations" value="<?= $totaux['css_prestations'] ?>">
        <input type="hidden" name="css_total" value="<?= $totaux['css_total'] ?>">
        <input type="hidden" name="ipm_salarie" value="<?= $totaux['ipm_salarie'] ?>">
        <input type="hidden" name="ipm_patronal" value="<?= $totaux['ipm_patronal'] ?>">
        <input type="hidden" name="ipm_total" value="<?= $totaux['ipm_total'] ?>">
        <input type="hidden" name="cfce" value="<?= $totaux['cfce'] ?>">
        <input type="hidden" name="total_a_payer" value="<?= $totaux['total_a_payer'] ?>">
        <button type="submit" style="padding:12px 24px;background:var(--gold);color:var(--navy-dark);border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s" onmouseover="this.style.background='#e2c99a'" onmouseout="this.style.background='var(--gold)'">
            Valider la déclaration
        </button>
    </form>
    <?php else: ?>
    <div style="text-align:center">
        <div class="badge badge-success" style="font-size:14px;padding:8px 16px">Déclarée le <?= date('d/m/Y', strtotime($declaration_existante['created_at'])) ?></div>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>
