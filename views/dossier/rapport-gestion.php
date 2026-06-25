<?php
$mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
?>
<div class="page-header">
    <div>
        <div class="page-title">Rapport de gestion</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Exercice <?= $exercice ?></div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <form method="get" action="" style="display:flex;gap:8px;align-items:center">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <select name="mois" onchange="this.form.submit()" style="padding:8px 12px;border-radius:8px;border:1px solid var(--border);font-size:14px;background:#fff">
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m==$mois_courant?'selected':'' ?>><?= $mois_labels[$m] ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <a href="<?= APP_URL ?>/dossier/rapport-gestion/export?id=<?= $entreprise['id'] ?>&mois=<?= $mois_courant ?>" target="_blank" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimer / PDF
        </a>
    </div>
</div>

<div style="background:linear-gradient(135deg,var(--navy-dark),var(--navy-light));border-radius:16px;padding:24px 28px;margin-bottom:24px;color:#fff;display:flex;align-items:center;justify-content:space-between">
    <div>
        <div style="font-size:14px;opacity:.7;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px">Rapport de gestion</div>
        <div style="font-size:22px;font-weight:700"><?= $mois_labels[$mois_courant] ?> <?= $exercice ?></div>
        <div style="font-size:14px;opacity:.6;margin-top:4px">Généré le <?= date('d/m/Y') ?></div>
    </div>
    <div style="text-align:right">
        <div style="font-size:14px;opacity:.7">Dossier</div>
        <div style="font-size:13px;font-weight:600"><?= e($entreprise['raison_sociale']) ?></div>
        <div style="font-size:13px;opacity:.5"><?= e($entreprise['forme_juridique']) ?> · <?= e($entreprise['code_dossier']) ?></div>
    </div>
</div>

<div style="text-align:center;padding:60px 0;color:var(--text-muted)">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:48px;height:48px;margin:0 auto 16px;display:block;opacity:.3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
    <div style="font-size:13px;font-weight:500;margin-bottom:8px">Cliquez sur "Imprimer / PDF" pour générer le rapport complet</div>
    <div style="font-size:14px;opacity:.6">Le rapport s'ouvrira dans un nouvel onglet, prêt à imprimer ou enregistrer en PDF</div>
    <div style="margin-top:24px">
        <a href="<?= APP_URL ?>/dossier/rapport-gestion/export?id=<?= $entreprise['id'] ?>&mois=<?= $mois_courant ?>" target="_blank" class="btn btn-primary" style="font-size:13px;padding:12px 28px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Générer le rapport <?= $mois_labels[$mois_courant] ?> <?= $exercice ?>
        </a>
    </div>
</div>
