<?php
$mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$statut_colors = ['brouillon'=>['#92400e','#fffbeb'],'valide'=>['#166534','#f0fdf4'],'paye'=>['#1e3a5f','#e8eef5']];
?>
<div class="page-header">
    <div>
        <div class="page-title">Bulletins de paie</div>
        <div class="page-subtitle"><?= e($employe['prenom'].' '.$employe['nom']) ?> — <?= $annee ?></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <form method="get" style="display:flex;gap:8px;align-items:center">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="employe_id" value="<?= $employe['id'] ?>">
            <select name="annee" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:16px">
                <?php for($y=2022;$y<=2027;$y++): ?>
                <option value="<?= $y ?>" <?= $y===$annee?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <a href="<?= APP_URL ?>/dossier/rh/employe?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" class="btn btn-outline btn-sm">← Fiche</a>
        <a href="<?= APP_URL ?>/dossier/rh/bulletin/creer?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" class="btn btn-primary btn-sm">+ Nouveau bulletin</a>
    </div>
</div>

<!-- Totaux annuels -->
<?php if($totaux && $totaux['total_brut']): ?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <?php foreach([
        ['Brut total '.$annee, number_format($totaux['total_brut'],0,',',' ').' F', '#1e3a5f', '#e8eef5'],
        ['Net total '.$annee, number_format($totaux['total_net'],0,',',' ').' F', '#166534', '#f0fdf4'],
        ['IPRES salarié', number_format($totaux['total_ipres'],0,',',' ').' F', '#b91c1c', '#fef2f2'],
        ['IR retenu', number_format($totaux['total_ir'],0,',',' ').' F', '#7c3aed', '#f5f3ff'],
    ] as [$label,$val,$col,$bg]): ?>
    <div class="card" style="padding:16px 20px;background:<?= $bg ?>;border:1px solid <?= $col ?>22">
        <div style="font-size:18px;font-weight:800;color:<?= $col ?>"><?= $val ?></div>
        <div style="font-size:14px;color:#555;margin-top:2px"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(empty($bulletins)): ?>
<div class="card" style="padding:40px;text-align:center;color:#888">
    <div style="font-size:32px;margin-bottom:8px">📄</div>
    <div>Aucun bulletin pour <?= $annee ?>.</div>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:16px">
        <thead>
            <tr style="background:#1e3a5f;color:#fff">
                <th style="padding:11px 16px;text-align:left;font-weight:600">Période</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600">Salaire brut</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600">IPRES sal.</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600">TRIMF</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600">IR</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600">Total retenues</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600">Net à payer</th>
                <th style="padding:11px 16px;text-align:center;font-weight:600">Statut</th>
                <th style="padding:11px 16px;text-align:center;font-weight:600">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($bulletins as $i => $b):
            [$tc, $bg] = $statut_colors[$b['statut']] ?? ['#555','#f3f4f6'];
        ?>
            <tr style="background:<?= $i%2===0?'#fff':'#f8f9fb' ?>;border-bottom:1px solid #eee">
                <td style="padding:10px 16px;font-weight:700;color:#1e3a5f"><?= $mois_noms[$b['periode_mois']] ?> <?= $b['periode_annee'] ?></td>
                <td style="padding:10px 16px;text-align:right;font-family:monospace"><?= number_format($b['salaire_brut'],0,',',' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#b91c1c;font-family:monospace"><?= number_format($b['ipres_salarie'],0,',',' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#555;font-family:monospace"><?= number_format($b['trimf'],0,',',' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#7c3aed;font-family:monospace"><?= number_format($b['ir_salarie'],0,',',' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#b91c1c;font-family:monospace;font-weight:600"><?= number_format($b['total_retenues'],0,',',' ') ?></td>
                <td style="padding:10px 16px;text-align:right;font-weight:800;color:#166534;font-family:monospace;font-size:17px"><?= number_format($b['net_a_payer'],0,',',' ') ?></td>
                <td style="padding:10px 16px;text-align:center">
                    <span style="display:inline-block;padding:2px 10px;border-radius:10px;font-size:14px;font-weight:700;background:<?= $bg ?>;color:<?= $tc ?>"><?= ucfirst($b['statut']) ?></span>
                </td>
                <td style="padding:10px 16px;text-align:center">
                    <a href="<?= APP_URL ?>/dossier/rh/bulletin?id=<?= $entreprise['id'] ?>&bulletin_id=<?= $b['id'] ?>" target="_blank" style="font-size:15px;color:#1e3a5f;text-decoration:none;padding:4px 10px;border:1px solid #1e3a5f33;border-radius:5px">PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
