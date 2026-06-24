<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Attestation de travail — <?= e($employe['prenom'].' '.$employe['nom']) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Georgia,serif;font-size:13px;color:#1a1a1a;background:#fff;padding:0}
@page{size:A4 portrait;margin:0}
.page{width:210mm;min-height:297mm;padding:20mm 22mm;position:relative;display:flex;flex-direction:column}
.no-print{position:fixed;top:16px;right:16px;display:flex;gap:8px;z-index:9999}
.no-print button{padding:8px 18px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600}
.btn-print{background:#1e3a5f;color:#fff}
.btn-close{background:#eee;color:#333}
/* Header */
.header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12mm;padding-bottom:8mm;border-bottom:2px solid #1e3a5f}
.ent-name{font-size:18px;font-weight:700;color:#1e3a5f;font-family:Georgia,serif}
.ent-info{font-size:11px;color:#555;margin-top:4px;line-height:1.6}
.doc-ref{text-align:right;font-size:11px;color:#666}
.doc-ref .ref-num{font-size:13px;font-weight:700;color:#1e3a5f;font-family:monospace}
/* Titre */
.doc-title{text-align:center;margin-bottom:12mm}
.doc-title h1{font-size:22px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:2px;font-family:Georgia,serif}
.doc-title .underline{width:80px;height:3px;background:#c9a96e;margin:8px auto 0}
/* Corps */
.body-text{font-size:13px;line-height:2;color:#1a1a1a;text-align:justify;margin-bottom:8mm}
.highlight{font-weight:700;color:#1e3a5f}
/* Tableau infos */
.info-table{width:100%;border-collapse:collapse;margin:8mm 0;font-size:12px}
.info-table tr td{padding:7px 12px;border:1px solid #ddd}
.info-table tr td:first-child{font-weight:700;color:#555;background:#f8f9fb;width:200px}
.info-table tr td:last-child{color:#1a1a1a}
/* Signature */
.signature-bloc{margin-top:auto;padding-top:10mm;display:flex;justify-content:space-between;align-items:flex-end}
.sig-left{font-size:11px;color:#666;line-height:1.8}
.sig-right{text-align:center;font-size:12px}
.sig-box{width:140px;height:50px;border:1px dashed #ccc;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:11px;margin:8px auto}
/* Mentions */
.footer-doc{margin-top:8mm;padding-top:6mm;border-top:1px solid #ddd;font-size:10px;color:#888;text-align:center;line-height:1.6}
/* Filigrane */
.watermark{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-45deg);font-size:80px;color:rgba(30,58,95,0.04);font-weight:900;font-family:Georgia,serif;pointer-events:none;white-space:nowrap}
@media print{.no-print{display:none!important}.watermark{position:fixed}}
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimer</button>
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
</div>

<div class="watermark">ATTESTATION</div>

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
                    <?php if($entreprise['ninea']): ?>NINEA : <?= e($entreprise['ninea']) ?> &nbsp;|&nbsp; <?php endif; ?>
                    <?php if($entreprise['rccm']): ?>RCCM : <?= e($entreprise['rccm']) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="doc-ref">
            <div>N° de référence</div>
            <div class="ref-num">ATT-<?= $employe['id'] ?>-<?= date('Y') ?></div>
            <div style="margin-top:6px">Dakar, le <?php
                $mFr = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
                echo date('d') . ' ' . $mFr[(int)date('n')] . ' ' . date('Y');
            ?></div>
        </div>
    </div>

    <!-- Titre -->
    <div class="doc-title">
        <h1>Attestation de travail</h1>
        <div class="underline"></div>
    </div>

    <!-- Corps -->
    <div class="body-text">
        Je soussigné(e), représentant légal de la société <span class="highlight"><?= e($entreprise['raison_sociale']) ?></span>,
        certifie par la présente que :
    </div>

    <table class="info-table">
        <tr><td>Nom & Prénom</td><td><strong><?= e(strtoupper($employe['nom']).' '.$employe['prenom']) ?></strong></td></tr>
        <?php if($employe['date_naissance']): ?>
        <tr><td>Date de naissance</td><td><?= date('d/m/Y', strtotime($employe['date_naissance'])) ?><?= $employe['lieu_naissance'] ? ' à '.e($employe['lieu_naissance']) : '' ?></td></tr>
        <?php endif; ?>
        <?php if($employe['num_cni']): ?>
        <tr><td>N° CNI / Passeport</td><td><?= e($employe['num_cni']) ?></td></tr>
        <?php endif; ?>
        <tr><td>Poste occupé</td><td><?= e($employe['poste'] ?: 'Non précisé') ?></td></tr>
        <?php if($employe['departement']): ?>
        <tr><td>Département / Service</td><td><?= e($employe['departement']) ?></td></tr>
        <?php endif; ?>
        <tr><td>Type de contrat</td><td><?= e($employe['type_contrat']) ?></td></tr>
        <tr><td>Date d'entrée en fonctions</td><td><?= $employe['date_embauche'] ? date('d/m/Y', strtotime($employe['date_embauche'])) : '—' ?></td></tr>
        <tr><td>Ancienneté</td><td><?= $anciennete_label ?></td></tr>
        <tr><td>Statut actuel</td><td><?= ucfirst(e($employe['statut'])) ?></td></tr>
    </table>

    <div class="body-text">
        <?php if($employe['statut'] === 'actif'): ?>
        <span class="highlight"><?= e($employe['prenom'].' '.$employe['nom']) ?></span> est actuellement
        <span class="highlight">en poste</span> au sein de notre structure et s'acquitte de ses fonctions
        avec sérieux et professionnalisme.
        <?php else: ?>
        <span class="highlight"><?= e($employe['prenom'].' '.$employe['nom']) ?></span> a été employé(e)
        au sein de notre structure du <strong><?= $employe['date_embauche'] ? date('d/m/Y', strtotime($employe['date_embauche'])) : '—' ?></strong>
        <?php if($employe['date_fin_contrat']): ?>au <strong><?= date('d/m/Y', strtotime($employe['date_fin_contrat'])) ?></strong><?php endif; ?>.
        <?php endif; ?>
    </div>

    <div class="body-text">
        Cette attestation est délivrée à l'intéressé(e) à sa demande et pour servir et valoir ce que de droit.
    </div>

    <!-- Signature -->
    <div class="signature-bloc">
        <div class="sig-left">
            <strong>L'employé(e)</strong><br>
            <div class="sig-box">Signature</div>
            <?= e(strtoupper($employe['nom']).' '.$employe['prenom']) ?>
        </div>
        <div class="sig-right">
            <strong>Pour <?= e($entreprise['raison_sociale']) ?></strong><br>
            <div class="sig-box">Cachet & Signature</div>
            Le Directeur Général
        </div>
    </div>

    <!-- Pied -->
    <div class="footer-doc">
        Document établi le <?= date('d/m/Y') ?> — À conserver — Valable uniquement avec cachet et signature originaux
    </div>
</div>
</body>
</html>
