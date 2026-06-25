<?php
$mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
?>
<div class="page-header">
    <div>
        <div class="page-title">Déclarations sociales</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Récapitulatif IPRES / IR / CSS — <?= $annee ?></div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <form method="get" style="display:flex;gap:8px;align-items:center">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <select name="annee" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:16px">
                <?php for($y=2022;$y<=2027;$y++): ?>
                <option value="<?= $y ?>" <?= $y===$annee?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <button onclick="window.print()" class="btn btn-secondary" style="display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Imprimer
        </button>
    </div>
</div>

<?php if(empty($declarations)): ?>
<div class="card" style="padding:40px;text-align:center;color:#888">
    <div style="font-size:32px;margin-bottom:8px">📋</div>
    <div>Aucun bulletin généré pour <?= $annee ?>.</div>
    <div style="font-size:15px;margin-top:6px">Générez des bulletins de paie pour voir les déclarations sociales.</div>
</div>
<?php else: ?>

<!-- Info légale -->
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:15px;color:#92400e">
    <strong>Taux IPRES 2026 :</strong> Salarié 5,6% / Patronal 8,6% — CSS : Accident 1% / Prestations familiales 7% (patronal uniquement) — TRIMF : forfait salarié.
</div>

<!-- KPIs annuels -->
<?php if($totaux): ?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <?php foreach([
        ['Brut total', number_format($totaux['total_brut'],0,',',' ').' F', '#1e3a5f', '#e8eef5'],
        ['IPRES salarié', number_format($totaux['total_sal_ipres_general'],0,',',' ').' F', '#b91c1c', '#fef2f2'],
        ['IPRES patronal', number_format($totaux['total_pat_ipres'],0,',',' ').' F', '#c9a96e', '#fffbeb'],
        ['IR retenu', number_format($totaux['total_ir'],0,',',' ').' F', '#b8923f', '#f5f3ff'],
    ] as [$label,$val,$col,$bg]): ?>
    <div class="card" style="padding:16px 20px;background:<?= $bg ?>;border:1px solid <?= $col ?>22">
        <div style="font-size:18px;font-weight:800;color:<?= $col ?>"><?= $val ?></div>
        <div style="font-size:14px;color:#555;margin-top:2px"><?= $label ?> — <?= $annee ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tableau mensuel -->
<div class="card" style="padding:0;overflow:hidden;margin-bottom:24px">
    <div style="padding:14px 20px;background:#1e3a5f;color:#fff;font-weight:700;font-size:17px">
        Récapitulatif mensuel <?= $annee ?>
    </div>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:16px;min-width:800px">
        <thead>
            <tr style="background:#f0f4f8;border-bottom:2px solid #ddd">
                <th style="padding:10px 14px;text-align:left;font-weight:700;color:#1e3a5f">Mois</th>
                <th style="padding:10px 14px;text-align:center;font-weight:700;color:#555">Bulletins</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#1e3a5f">Brut total</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#b91c1c">IPRES sal.</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#c9a96e">IPRES pat.</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#b8923f">IR retenu</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#0369a1">TRIMF</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#059669">CSS total</th>
                <th style="padding:10px 14px;text-align:right;font-weight:700;color:#166534">Net payé</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($declarations as $i => $d): ?>
            <tr style="background:<?= $i%2===0?'#fff':'#f8f9fb' ?>;border-bottom:1px solid #eee">
                <td style="padding:10px 14px;font-weight:600;color:#1e3a5f"><?= $mois_noms[(int)$d['mois']] ?></td>
                <td style="padding:10px 14px;text-align:center;color:#555"><?= $d['nb_bulletins'] ?></td>
                <td style="padding:10px 14px;text-align:right;font-weight:600"><?= number_format($d['total_brut'],0,',',' ') ?></td>
                <td style="padding:10px 14px;text-align:right;color:#b91c1c"><?= number_format($d['total_sal_ipres_general'],0,',',' ') ?></td>
                <td style="padding:10px 14px;text-align:right;color:#c9a96e"><?= number_format($d['total_pat_ipres'],0,',',' ') ?></td>
                <td style="padding:10px 14px;text-align:right;color:#b8923f"><?= number_format($d['total_ir'],0,',',' ') ?></td>
                <td style="padding:10px 14px;text-align:right;color:#0369a1"><?= number_format($d['total_trimf'],0,',',' ') ?></td>
                <td style="padding:10px 14px;text-align:right;color:#059669"><?= number_format($d['total_css'],0,',',' ') ?></td>
                <td style="padding:10px 14px;text-align:right;color:#166534;font-weight:700"><?= number_format($d['total_net'],0,',',' ') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if($totaux): ?>
        <tfoot>
            <tr style="background:#1e3a5f;color:#fff;font-weight:700">
                <td style="padding:11px 14px">TOTAL <?= $annee ?></td>
                <td style="padding:11px 14px;text-align:center"><?= $totaux['nb_bulletins'] ?></td>
                <td style="padding:11px 14px;text-align:right"><?= number_format($totaux['total_brut'],0,',',' ') ?></td>
                <td style="padding:11px 14px;text-align:right"><?= number_format($totaux['total_sal_ipres_general'],0,',',' ') ?></td>
                <td style="padding:11px 14px;text-align:right;color:#c9a96e"><?= number_format($totaux['total_pat_ipres'],0,',',' ') ?></td>
                <td style="padding:11px 14px;text-align:right"><?= number_format($totaux['total_ir'],0,',',' ') ?></td>
                <td style="padding:11px 14px;text-align:right"><?= number_format($totaux['total_trimf'],0,',',' ') ?></td>
                <td style="padding:11px 14px;text-align:right"><?= number_format($totaux['total_css'],0,',',' ') ?></td>
                <td style="padding:11px 14px;text-align:right;color:#90ee90"><?= number_format($totaux['total_net'],0,',',' ') ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
    </div>
</div>

<!-- Récap coût employeur -->
<?php if($totaux && $totaux['cout_employeur']): ?>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px">
    <div class="card" style="padding:20px;background:#fef2f2;border:1px solid #fecaca">
        <div style="font-size:14px;font-weight:700;color:#991b1b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Charges salariales totales</div>
        <div style="font-size:20px;font-weight:800;color:#b91c1c"><?= number_format($totaux['total_sal_ipres_general'] + $totaux['total_ir'] + $totaux['total_trimf'],0,',',' ') ?> F</div>
        <div style="font-size:14px;color:#991b1b;margin-top:4px">IPRES + IR + TRIMF</div>
    </div>
    <div class="card" style="padding:20px;background:#fffbeb;border:1px solid #fde68a">
        <div style="font-size:14px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Charges patronales totales</div>
        <div style="font-size:20px;font-weight:800;color:#c9a96e"><?= number_format($totaux['total_pat_ipres'] + $totaux['total_css'],0,',',' ') ?> F</div>
        <div style="font-size:14px;color:#92400e;margin-top:4px">IPRES + CSS employeur</div>
    </div>
    <div class="card" style="padding:20px;background:#f0f4f8;border:1px solid #c5d0de">
        <div style="font-size:14px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Coût total employeur</div>
        <div style="font-size:20px;font-weight:800;color:#1e3a5f"><?= number_format($totaux['cout_employeur'],0,',',' ') ?> F</div>
        <div style="font-size:14px;color:#555;margin-top:4px">Calculé dans les bulletins</div>
    </div>
</div>
<?php endif; ?>

<!-- Note échéances -->
<div style="background:#f0f4f8;border-radius:8px;padding:14px 18px;font-size:15px;color:#444;line-height:1.8">
    <strong style="color:#1e3a5f">Échéances de déclarations :</strong><br>
    • <strong>IPRES :</strong> Déclaration et paiement avant le 15 du mois suivant<br>
    • <strong>IR / TRIMF :</strong> Déclaration mensuelle avant le 15 du mois suivant à la DGI<br>
    • <strong>CSS :</strong> Déclaration et paiement trimestriel
</div>

<?php endif; ?>

<style>
@media print {
    .no-print, .sidebar, .topbar { display: none !important; }
    body { font-size: 14px; }
}
</style>
