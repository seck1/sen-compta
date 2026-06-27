<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Solde de tout compte — <?= e($employe['prenom'].' '.$employe['nom']) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Georgia,serif;font-size:13px;color:#1a1a1a;background:#fff}
@page{size:A4 portrait;margin:0}
.page{width:210mm;min-height:297mm;padding:18mm 22mm;display:flex;flex-direction:column}
.no-print{position:fixed;top:16px;right:16px;display:flex;gap:8px;z-index:9999}
.no-print button{padding:8px 18px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600}
.btn-print{background:#1e3a5f;color:#fff}
.btn-close{background:#eee;color:#333}
.header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10mm;padding-bottom:6mm;border-bottom:2px solid #1e3a5f}
.ent-name{font-size:17px;font-weight:700;color:#1e3a5f}
.ent-info{font-size:11px;color:#555;margin-top:4px;line-height:1.6}
.doc-title{text-align:center;margin-bottom:8mm}
.doc-title h1{font-size:20px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:2px}
.doc-title .underline{width:80px;height:3px;background:#c9a96e;margin:8px auto 0}
.section-title{font-size:11px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin:6mm 0 3mm;padding-bottom:4px;border-bottom:1px solid #1e3a5f33}
table.info{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:4mm}
table.info td{padding:6px 10px;border:1px solid #ddd}
table.info td:first-child{font-weight:700;color:#555;background:#f8f9fb;width:220px}
table.calc{width:100%;border-collapse:collapse;font-size:13px}
table.calc th{padding:8px 12px;background:#1e3a5f;color:#fff;font-weight:600;text-align:left}
table.calc th:last-child{text-align:right}
table.calc td{padding:8px 12px;border-bottom:1px solid #eee}
table.calc td:last-child{text-align:right;font-family:monospace;font-weight:600}
table.calc tr.subtotal td{background:#f8f9fb;font-weight:700;border-top:2px solid #ddd}
table.calc tr.total td{background:#1e3a5f;color:#fff;font-weight:800;font-size:15px}
table.calc tr.total td:last-child{color:#c9a96e}
.body-text{font-size:12.5px;line-height:1.9;color:#1a1a1a;text-align:justify;margin:4mm 0}
.signature-bloc{margin-top:auto;padding-top:8mm;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10mm;border-top:1px solid #ddd}
.sig-col{text-align:center;font-size:11px}
.sig-box{width:120px;height:45px;border:1px dashed #ccc;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:10px;margin:6px auto}
.footer-doc{margin-top:6mm;font-size:10px;color:#888;text-align:center;line-height:1.6;border-top:1px solid #eee;padding-top:4mm}
.watermark{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-45deg);font-size:70px;color:rgba(30,58,95,0.04);font-weight:900;pointer-events:none;white-space:nowrap}
@media print{.no-print{display:none!important}}
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimer</button>
    <?php if (function_exists('isAdmin') && isAdmin()): ?>
    <form method="POST" action="<?= APP_URL ?>/dossier/rh/solde-tout-compte/payer" style="display:inline"
          onsubmit="return confirm('Enregistrer ce solde de tout compte, le comptabiliser et marquer l\'employé comme parti ?');">
        <?= csrfField() ?>
        <input type="hidden" name="entreprise_id" value="<?= (int)$entreprise['id'] ?>">
        <input type="hidden" name="employe_id" value="<?= (int)$employe['id'] ?>">
        <input type="hidden" name="date_depart" value="<?= e($date_depart) ?>">
        <input type="hidden" name="motif" value="licenciement">
        <input type="hidden" name="dernier_net" value="<?= (int)($dernier_bulletin['net_a_payer'] ?? 0) ?>">
        <input type="hidden" name="indemnite_licenciement" value="<?= (int)$indemnite_licenciement ?>">
        <input type="hidden" name="indemnite_conges" value="<?= (int)$indemnite_conges ?>">
        <button type="submit" class="btn-print" style="background:#1f6e4e">💵 Payer &amp; Comptabiliser</button>
    </form>
    <?php endif; ?>
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
</div>
<div class="watermark">SOLDE TOUT COMPTE</div>
<div class="page">

    <!-- En-tête -->
    <div class="header">
        <div style="display:flex;align-items:center;gap:14px">
            <?php if(!empty($entreprise['logo'])):
                $logo_path = APP_ROOT . '/public/logos/' . $entreprise['logo'];
                if(file_exists($logo_path)):
                    $logo_data = base64_encode(file_get_contents($logo_path));
                    $logo_mime = mime_content_type($logo_path);
            ?>
            <img src="data:<?= $logo_mime ?>;base64,<?= $logo_data ?>" style="width:110px;height:110px;object-fit:contain;border-radius:6px">
            <?php endif; endif; ?>
            <div>
                <div class="ent-name"><?= e($entreprise['raison_sociale']) ?></div>
                <div class="ent-info">
                    <?= e($entreprise['adresse'] ?? '') ?><br>
                    <?php if($entreprise['ninea']): ?>NINEA : <?= e($entreprise['ninea']) ?><?php endif; ?>
                    <?php if($entreprise['rccm']): ?> &nbsp;|&nbsp; RCCM : <?= e($entreprise['rccm']) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div style="text-align:right;font-size:11px;color:#666">
            <div>N° Réf. : <strong style="font-family:monospace;color:#1e3a5f">STC-<?= $employe['id'] ?>-<?= date('Y') ?></strong></div>
            <div style="margin-top:6px">Dakar, le <?= date('d/m/Y') ?></div>
        </div>
    </div>

    <!-- Titre -->
    <div class="doc-title">
        <h1>Solde de tout compte</h1>
        <div class="underline"></div>
    </div>

    <!-- Infos employé -->
    <div class="section-title">Informations de l'employé</div>
    <table class="info">
        <tr><td>Nom & Prénom</td><td><strong><?= e(strtoupper($employe['nom']).' '.$employe['prenom']) ?></strong></td></tr>
        <tr><td>Matricule</td><td><?= e($employe['matricule'] ?: '—') ?></td></tr>
        <tr><td>Poste occupé</td><td><?= e($employe['poste'] ?: '—') ?></td></tr>
        <tr><td>Type de contrat</td><td><?= e($employe['type_contrat']) ?></td></tr>
        <tr><td>Date d'entrée</td><td><?= $employe['date_embauche'] ? date('d/m/Y', strtotime($employe['date_embauche'])) : '—' ?></td></tr>
        <tr><td>Date de départ</td><td><?= date('d/m/Y', strtotime($date_depart)) ?></td></tr>
        <tr><td>Ancienneté</td><td><?= $anciennete_label ?> (<?= $anciennete_mois ?> mois)</td></tr>
    </table>

    <!-- Calcul des indemnités -->
    <div class="section-title">Décompte des sommes dues</div>
    <table class="calc">
        <thead>
            <tr>
                <th>Élément</th>
                <th>Base de calcul</th>
                <th style="text-align:right">Montant (F CFA)</th>
            </tr>
        </thead>
        <tbody>
            <?php if($dernier_bulletin): ?>
            <tr>
                <td>Salaire du mois en cours (prorata)</td>
                <td style="font-size:11px;color:#666">Dernier salaire brut</td>
                <td><?= number_format($dernier_bulletin['net_a_payer'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>

            <?php if($jours_conges_restants > 0): ?>
            <tr>
                <td>Indemnité compensatrice de congés</td>
                <td style="font-size:11px;color:#666"><?= number_format($jours_conges_restants,1) ?> jour(s) × <?= number_format($salaire_moyen/26,0,',',' ') ?> F/j</td>
                <td><?= number_format($indemnite_conges,0,',',' ') ?></td>
            </tr>
            <?php endif; ?>

            <?php if($employe['type_contrat'] === 'CDI' && $indemnite_licenciement > 0): ?>
            <tr>
                <td>Indemnité de licenciement</td>
                <td style="font-size:11px;color:#666"><?= $annees_completes ?> an(s) × 1/3 mois × <?= number_format($salaire_moyen,0,',',' ') ?> F</td>
                <td><?= number_format($indemnite_licenciement,0,',',' ') ?></td>
            </tr>
            <?php endif; ?>

            <tr class="subtotal">
                <td colspan="2">TOTAL BRUT DES SOMMES DUES</td>
                <td><?= number_format(($dernier_bulletin?$dernier_bulletin['net_a_payer']:0) + $indemnite_conges + $indemnite_licenciement, 0,',',' ') ?></td>
            </tr>
            <tr class="total">
                <td colspan="2">NET À PERCEVOIR</td>
                <td><?= number_format(($dernier_bulletin?$dernier_bulletin['net_a_payer']:0) + $indemnite_conges + $indemnite_licenciement, 0,',',' ') ?> F CFA</td>
            </tr>
        </tbody>
    </table>

    <!-- Texte légal -->
    <div class="body-text">
        Le soussigné <strong><?= e($employe['prenom'].' '.strtoupper($employe['nom'])) ?></strong> reconnaît avoir reçu de
        <strong><?= e($entreprise['raison_sociale']) ?></strong> la somme mentionnée ci-dessus en règlement de tous
        comptes afférents à l'exécution du contrat de travail et à sa rupture. Le présent reçu pour solde de tout
        compte peut être dénoncé dans un délai de <strong>six (6) mois</strong> à compter de sa signature.
    </div>

    <!-- Signatures -->
    <div class="signature-bloc">
        <div class="sig-col">
            <strong>L'employé(e)</strong>
            <div class="sig-box">Signature</div>
            <?= e(strtoupper($employe['nom']).' '.$employe['prenom']) ?>
        </div>
        <div class="sig-col">
            <strong>Date de signature</strong>
            <div class="sig-box" style="font-size:12px;color:#333">____/____/______</div>
            &nbsp;
        </div>
        <div class="sig-col">
            <strong>L'employeur</strong>
            <div class="sig-box">Cachet & Signature</div>
            <?= e($entreprise['raison_sociale']) ?>
        </div>
    </div>

    <div class="footer-doc">
        Établi en deux (2) exemplaires originaux — <?= date('d/m/Y') ?><br>
        Ce document vaut reçu définitif après signature des deux parties · Délai de contestation : 6 mois (Art. L.149 Code du travail sénégalais)
    </div>
</div>
</body>
</html>
