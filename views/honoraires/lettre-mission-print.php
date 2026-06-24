<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Lettre de mission — <?= e($mission['raison_sociale']) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Georgia,serif;font-size:13px;color:#1a1a1a;background:#fff}
@page{size:A4 portrait;margin:0}
.page{width:210mm;min-height:297mm;padding:18mm 22mm;display:flex;flex-direction:column;position:relative}
.no-print{position:fixed;top:16px;right:16px;display:flex;gap:8px;z-index:9999}
.no-print button{padding:8px 18px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600}
.btn-print{background:#1e3a5f;color:#fff}.btn-close{background:#eee;color:#333}

/* Barre top */
.top-bar{height:6px;background:linear-gradient(90deg,#1e3a5f,#c9a96e);margin-bottom:10mm}

/* Header */
.header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10mm;padding-bottom:6mm;border-bottom:2px solid #1e3a5f}
.cabinet-block{display:flex;align-items:center;gap:12px}
.cabinet-info .name{font-size:17px;font-weight:700;color:#1e3a5f;font-family:Georgia,serif}
.cabinet-info .sub{font-size:10px;color:#888;margin-top:2px;line-height:1.5}
.ref-block{text-align:right;font-size:11px;color:#666;line-height:1.8}
.ref-block strong{color:#1e3a5f;font-family:monospace;font-size:13px}

/* Destinataire */
.destinataire{background:#f0f4f8;border-left:4px solid #1e3a5f;padding:12px 16px;margin-bottom:8mm;border-radius:0 6px 6px 0;font-size:12px;line-height:1.8}
.destinataire .label{font-size:10px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}

/* Objet */
.objet{font-size:13px;font-weight:700;color:#1e3a5f;margin-bottom:8mm;padding:8px 0;border-bottom:1px solid #eee}

/* Corps */
.body-text{font-size:12.5px;line-height:1.9;color:#1a1a1a;text-align:justify;margin-bottom:5mm}

/* Section titre */
.section-title{font-size:12px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:.6px;margin:6mm 0 3mm;padding-bottom:4px;border-bottom:1px solid #1e3a5f33}

/* Table missions */
table.info{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:5mm}
table.info td{padding:7px 12px;border:1px solid #ddd}
table.info td:first-child{font-weight:700;color:#555;background:#f8f9fb;width:200px}

/* Prestations */
.prestation-item{display:flex;justify-content:space-between;padding:7px 12px;border-bottom:1px solid #eee;font-size:12px}
.prestation-item:nth-child(odd){background:#f8f9fb}
.prestation-item .label{color:#333}
.prestation-item .val{font-weight:600;color:#1e3a5f;font-family:monospace}

/* Honoraires box */
.honoraires-box{background:#1e3a5f;color:#fff;padding:14px 18px;border-radius:8px;margin:6mm 0;display:flex;justify-content:space-between;align-items:center}
.honoraires-box .label{font-size:11px;opacity:.8;text-transform:uppercase;letter-spacing:.5px}
.honoraires-box .montant{font-size:20px;font-weight:800;font-family:monospace;color:#c9a96e}

/* Conditions */
.condition-item{font-size:12px;padding:5px 0;padding-left:16px;position:relative;color:#333;line-height:1.6}
.condition-item::before{content:"•";position:absolute;left:4px;color:#c9a96e;font-weight:700}

/* Signatures */
.signature-bloc{margin-top:auto;padding-top:8mm;display:grid;grid-template-columns:1fr 1fr;gap:16mm;border-top:1px solid #ddd}
.sig-col{text-align:center;font-size:11px}
.sig-box{width:140px;height:50px;border:1px dashed #ccc;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:10px;margin:8px auto}

/* Footer */
.footer-doc{margin-top:6mm;font-size:10px;color:#888;text-align:center;line-height:1.6;border-top:1px solid #eee;padding-top:4mm}

/* Barre bottom */
.bottom-bar{position:fixed;bottom:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#c9a96e,#1e3a5f)}

@media print{.no-print{display:none!important}.bottom-bar{position:fixed}}
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimer / PDF</button>
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
</div>

<div class="top-bar"></div>

<div class="page">
    <!-- En-tête -->
    <div class="header">
        <div class="cabinet-block">
            <?php
            // Logo cabinet si disponible
            $logo_cabinet = APP_ROOT . '/public/logos/logo_cabinet.png';
            ?>
            <div class="cabinet-info">
                <div class="name"><?= e($cabinet['nom']) ?></div>
                <div class="sub">
                    <?= e($cabinet['adresse']) ?><br>
                    <?php if($cabinet['telephone']): ?><?= e($cabinet['telephone']) ?> — <?php endif; ?>
                    <?php if($cabinet['email']): ?><?= e($cabinet['email']) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="ref-block">
            <div>Référence : <strong>LM-<?= str_pad($mission['id'],4,'0',STR_PAD_LEFT) ?></strong></div>
            <div>Date : <?= date('d/m/Y') ?></div>
            <div style="margin-top:4px;padding:3px 8px;background:#f0fdf4;border-radius:4px;font-size:10px;color:#166534;font-weight:700;display:inline-block">
                <?= strtoupper($mission['statut'] ?? 'en_cours') ?>
            </div>
        </div>
    </div>

    <!-- Destinataire -->
    <div class="destinataire">
        <div class="label">À l'attention de</div>
        <strong style="font-size:14px;color:#1e3a5f"><?= e($mission['raison_sociale']) ?></strong><br>
        <?php if($mission['adresse']): ?><?= e($mission['adresse']) ?><br><?php endif; ?>
        <?php if($mission['ninea']): ?>NINEA : <?= e($mission['ninea']) ?><?php endif; ?>
        <?php if($mission['rccm']): ?> &nbsp;·&nbsp; RCCM : <?= e($mission['rccm']) ?><?php endif; ?>
    </div>

    <!-- Objet -->
    <div class="objet">Objet : Lettre de mission — <?= e($mission['libelle'] ?? $mission['type'] ?? 'Mission') ?></div>

    <!-- Introduction -->
    <div class="body-text">
        Madame, Monsieur,<br><br>
        Nous vous remercions de la confiance que vous nous accordez. Conformément à nos entretiens,
        nous avons l'honneur de vous soumettre la présente lettre de mission définissant les conditions
        dans lesquelles nous interviendrons auprès de votre société <strong><?= e($mission['raison_sociale']) ?></strong>.
    </div>

    <!-- Nature de la mission -->
    <div class="section-title">1. Nature et étendue de la mission</div>
    <?php
    $types = ['comptabilite'=>'Tenue de comptabilité','audit'=>'Audit et révision comptable','fiscalite'=>'Conseil et assistance fiscale','paie'=>'Gestion de la paie','conseil'=>'Conseil en gestion','autre'=>'Mission spécifique'];
    $type_label = $types[$mission['type']] ?? ucfirst($mission['type'] ?? 'Comptabilité');
    ?>
    <table class="info" style="margin-bottom:4mm">
        <tr><td>Type de mission</td><td><?= $type_label ?></td></tr>
        <tr><td>Référence mission</td><td><strong><?= e($mission['reference']) ?></strong></td></tr>
        <tr><td>Date de début</td><td><?= $mission['date_debut'] ? date('d/m/Y', strtotime($mission['date_debut'])) : '—' ?></td></tr>
        <tr><td>Date de fin prévue</td><td><?= $mission['date_fin_prevue'] ? date('d/m/Y', strtotime($mission['date_fin_prevue'])) : 'Indéterminée (mission permanente)' ?></td></tr>
        <?php if($mission['budget_heures']): ?>
        <tr><td>Budget heures estimé</td><td><?= number_format($mission['budget_heures'],1) ?> heures</td></tr>
        <?php endif; ?>
    </table>

    <?php if($mission['note']): ?>
    <div class="body-text" style="background:#f8f9fb;border-left:3px solid #c9a96e;padding:10px 14px;border-radius:0 6px 6px 0;font-size:12px">
        <?= nl2br(e($mission['note'])) ?>
    </div>
    <?php endif; ?>

    <!-- Prestations incluses -->
    <div class="section-title">2. Prestations incluses</div>
    <?php
    $prestations_par_type = [
        'comptabilite' => ['Saisie et traitement des pièces comptables','Établissement des états financiers (Bilan, CR, TAFIRE)','Rapprochements bancaires mensuels','Déclarations TVA et IS','Grand livre et balance'],
        'audit'        => ['Revue des procédures et contrôle interne','Vérification des cycles comptables majeurs','Rapport de commissariat aux comptes','Recommandations d\'amélioration'],
        'fiscalite'    => ['Optimisation de la charge fiscale','Préparation des déclarations fiscales','Assistance lors de vérifications DGID','Veille fiscale et conseils'],
        'paie'         => ['Calcul des bulletins de paie','Déclarations IPRES et CSS mensuelles','Déclarations IR/ITS','Gestion des congés et absences','Soldes de tout compte'],
        'conseil'      => ['Analyse financière et tableaux de bord','Conseils en organisation et gestion','Accompagnement stratégique','Études de rentabilité'],
        'autre'        => ['Prestations définies selon accord spécifique'],
    ];
    $prest = $prestations_par_type[$mission['type']] ?? $prestations_par_type['autre'];
    ?>
    <div style="background:#f8f9fb;border-radius:6px;padding:10px 16px;margin-bottom:5mm">
        <?php foreach($prest as $p): ?>
        <div class="condition-item"><?= e($p) ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Honoraires -->
    <div class="section-title">3. Honoraires</div>
    <?php
    $montant_display = '';
    if ($mission['montant_forfait'] > 0) {
        $montant_display = number_format($mission['montant_forfait'],0,',',' ') . ' F CFA HT (forfait)';
    } elseif ($mission['taux_horaire'] > 0 && $mission['budget_heures'] > 0) {
        $total = $mission['taux_horaire'] * $mission['budget_heures'];
        $montant_display = number_format($total,0,',',' ') . ' F CFA HT (' . number_format($mission['taux_horaire'],0,',',' ') . ' F/h × ' . $mission['budget_heures'] . 'h)';
    } else {
        $montant_display = 'À définir selon accord';
    }
    ?>
    <div class="honoraires-box">
        <div>
            <div class="label">Honoraires</div>
            <div style="font-size:11px;opacity:.7;margin-top:2px">Hors taxes — TVA 18% en sus</div>
        </div>
        <div class="montant"><?= $montant_display ?></div>
    </div>

    <!-- Conditions -->
    <div class="section-title">4. Conditions générales</div>
    <div style="background:#f8f9fb;border-radius:6px;padding:10px 16px;margin-bottom:5mm">
        <div class="condition-item">Les honoraires sont payables à réception de facture, sous 30 jours.</div>
        <div class="condition-item">Tout retard de paiement entraîne des pénalités de 1,5% par mois.</div>
        <div class="condition-item">La présente lettre est valable pour l'exercice en cours, renouvelable tacitement.</div>
        <div class="condition-item">Chaque partie peut résilier avec un préavis d'un (1) mois par lettre recommandée.</div>
        <div class="condition-item">Les documents et informations transmis sont traités de manière strictement confidentielle.</div>
        <div class="condition-item">En cas de litige, compétence exclusive aux tribunaux de Dakar.</div>
    </div>

    <!-- Signatures -->
    <div class="signature-bloc">
        <div class="sig-col">
            <strong>Pour le Client</strong><br>
            <em style="font-size:10px;color:#888"><?= e($mission['raison_sociale']) ?></em>
            <div class="sig-box">Lu et approuvé<br>Cachet & Signature</div>
            <div>Date : ____/____/______</div>
        </div>
        <div class="sig-col">
            <strong>Pour <?= e($cabinet['nom']) ?></strong><br>
            <em style="font-size:10px;color:#888">Le Directeur</em>
            <div class="sig-box">Cachet & Signature</div>
            <div>Date : <?= date('d/m/Y') ?></div>
        </div>
    </div>

    <div class="footer-doc">
        Lettre de mission établie le <?= date('d/m/Y') ?> — Réf. LM-<?= str_pad($mission['id'],4,'0',STR_PAD_LEFT) ?><br>
        <?= e($cabinet['nom']) ?> — <?= e($cabinet['adresse']) ?> — Document confidentiel
    </div>
</div>

<div class="bottom-bar"></div>
</body>
</html>
