<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport de gestion — <?= e($entreprise['raison_sociale']) ?> — <?= $mois_labels[$mois_courant] ?> <?= $exercice ?></title>
<style>
/* ============================================================
   RESET & BASE
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:      #1e3a5f;
    --navy-deep: #0f2040;
    --navy-mid:  #2a4f7c;
    --gold:      #c9a96e;
    --gold-dark: #a8843f;
    --gold-light:#e8d5a8;
    --white:     #ffffff;
    --ink:       #111827;
    --ink-soft:  #374151;
    --bg-row:    #f7f8fa;
    --border:    #d1d5db;
    --green:     #15803d;
    --red:       #b91c1c;
    --blue:      #1d4ed8;
}

@page {
    size: A4 portrait;
    margin: 0;
}

@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .no-print { display: none !important; }
    body { margin: 0; }
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10pt;
    color: var(--ink);
    background: #fff;
    line-height: 1.5;
}

/* ============================================================
   NO-PRINT TOOLBAR
   ============================================================ */
.no-print {
    position: fixed; top: 0; left: 0; right: 0; z-index: 999;
    background: var(--navy-deep);
    padding: 10px 20px;
    display: flex; align-items: center; gap: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}
.no-print .btn {
    padding: 7px 18px; border-radius: 5px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    border: none; letter-spacing: 0.3px;
}
.no-print .btn-print { background: var(--gold); color: var(--navy-deep); }
.no-print .btn-print:hover { background: var(--gold-dark); }
.no-print .btn-close  { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); }
.no-print .btn-close:hover { background: rgba(255,255,255,0.2); }
.no-print .hint { font-size: 11px; color: rgba(255,255,255,0.45); margin-left: 6px; }

/* ============================================================
   PAGE WRAPPER
   ============================================================ */
.page {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding-top: 52px; /* for no-print bar */
    background: #fff;
}

@media print {
    .page { padding-top: 0; width: 100%; }
}

/* ============================================================
   HEADER
   ============================================================ */
.report-header {
    background: var(--navy-deep);
    position: relative;
    overflow: hidden;
    padding: 18px 22px 14px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

/* Decorative gold diagonal stripe */
.report-header::after {
    content: '';
    position: absolute;
    top: 0; right: 80px; bottom: 0;
    width: 4px;
    background: var(--gold);
    transform: skewX(-8deg);
    opacity: 0.6;
}

.header-left { display: flex; align-items: flex-start; gap: 14px; flex: 1; }
.header-logo {
    width: 44px; height: 44px; flex-shrink: 0;
    background: rgba(255,255,255,0.08);
    border-radius: 6px; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(201,169,110,0.3);
}
.header-logo img { width: 100%; height: 100%; object-fit: contain; padding: 3px; }
.header-logo .initials {
    font-family: Georgia, serif;
    font-size: 14pt; font-weight: 700;
    color: var(--gold); letter-spacing: -1px;
}
.header-company .name {
    font-family: Georgia, serif;
    font-size: 13pt; font-weight: 700;
    color: #fff; line-height: 1.1;
    letter-spacing: -0.3px;
}
.header-company .meta {
    font-size: 7pt; color: rgba(255,255,255,0.5);
    margin-top: 4px; line-height: 1.7;
}
.header-company .meta span { margin-right: 10px; }

.header-right { text-align: right; flex-shrink: 0; }
.header-right .doc-type {
    font-family: Georgia, serif;
    font-size: 8pt; font-weight: 700;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 3px;
}
.header-right .doc-period {
    font-size: 14pt; font-weight: 700;
    color: #fff; line-height: 1.1;
}
.header-right .doc-sub {
    font-size: 7pt; color: rgba(255,255,255,0.4);
    margin-top: 4px;
}

/* Gold separator bar */
.gold-bar {
    height: 3px;
    background: linear-gradient(to right, var(--gold-dark), var(--gold), var(--gold-light), transparent);
    margin-bottom: 12px;
}

/* Cabinet tagline */
.cabinet-line {
    display: flex; justify-content: space-between; align-items: center;
    padding: 4px 22px;
    background: var(--bg-row);
    border-bottom: 1px solid var(--border);
    margin-bottom: 10px;
    font-size: 7pt; color: var(--ink-soft);
}
.cabinet-line .cab-name { font-weight: 700; color: var(--navy); letter-spacing: 0.5px; }
.cabinet-line .period-tag {
    background: var(--navy);
    color: #fff;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 6.5pt; font-weight: 700;
    letter-spacing: 0.5px;
}

/* ============================================================
   CONTENT AREA
   ============================================================ */
.content { padding: 0 18px 14px; }

/* Section title */
.section-title {
    font-family: Georgia, serif;
    font-size: 9pt; font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--navy);
    border-left: 3px solid var(--gold);
    padding-left: 8px;
    margin: 10px 0 7px;
    line-height: 1;
}

/* ============================================================
   KPI GRID
   ============================================================ */
.kpi-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 7px;
    margin-bottom: 7px;
}
.kpi-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 7px;
    margin-bottom: 10px;
}
.kpi-card {
    border: 1px solid var(--border);
    border-left-width: 3px;
    border-radius: 4px;
    padding: 7px 9px;
    background: #fff;
    min-width: 0;
}
.kpi-card.c-green  { border-left-color: var(--green); }
.kpi-card.c-red    { border-left-color: var(--red); }
.kpi-card.c-blue   { border-left-color: var(--blue); }
.kpi-card.c-navy   { border-left-color: var(--navy); }
.kpi-card.c-gold   { border-left-color: var(--gold-dark); }
.kpi-card.c-orange { border-left-color: #d97706; }

.kpi-label {
    font-size: 7pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1px;
    color: var(--ink-soft);
    margin-bottom: 4px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.kpi-val {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt; font-weight: 700;
    line-height: 1.2;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.kpi-val.green  { color: var(--green); }
.kpi-val.red    { color: var(--red); }
.kpi-val.blue   { color: var(--blue); }
.kpi-val.navy   { color: var(--navy); }
.kpi-val.orange { color: #b45309; }
.kpi-sub {
    font-size: 7.5pt; color: var(--ink-soft);
    margin-top: 3px; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ============================================================
   TABLE
   ============================================================ */
table { width: 100%; border-collapse: collapse; font-size: 9pt; }
thead th {
    background: var(--navy);
    color: #fff;
    padding: 6px 8px;
    font-size: 7.5pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px;
    text-align: left;
}
thead th.r { text-align: right; }
tbody tr { border-bottom: 1px solid #eaecf0; }
tbody tr:nth-child(even) { background: var(--bg-row); }
tbody td { padding: 5px 8px; color: var(--ink); vertical-align: middle; }
tbody td.r { text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 9pt; }
tbody td.bold { font-weight: 700; }
.tr-total td {
    background: var(--navy-deep) !important;
    color: #fff !important;
    font-weight: 700;
    padding: 6px 8px;
}
.tr-total td.r { font-family: Arial, Helvetica, sans-serif; }

/* Mini bar in table cell */
.mini-bar-wrap { display: flex; gap: 2px; margin-top: 2px; }
.mini-bar { height: 4px; border-radius: 2px; }

/* ============================================================
   TWO COLUMNS DETAIL
   ============================================================ */
.two-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.detail-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
.detail-table thead th {
    background: var(--navy);
    color: #fff;
    padding: 5px 7px;
    font-size: 7pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px;
    text-align: left;
}
.detail-table thead th.r { text-align: right; }
.detail-table tbody tr { border-bottom: 1px solid #eaecf0; }
.detail-table tbody tr:nth-child(even) { background: var(--bg-row); }
.detail-table tbody td { padding: 5px 7px; color: var(--ink); vertical-align: middle; }
.detail-table tbody td.r { text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; white-space: nowrap; }
.detail-table tbody td.pct { text-align: right; color: var(--ink-soft); font-size: 8pt; font-weight: 700; width: 30px; }

/* Progress bar row */
.bar-row { height: 3px; background: #e5e7eb; border-radius: 2px; margin-top: 2px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 2px; }

/* ============================================================
   SYNTHESE
   ============================================================ */
.synthese-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.synthese-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 6px 10px;
    border-radius: 4px;
    border: 1px solid var(--border);
}
.synthese-item .s-label { font-size: 9pt; font-weight: 600; color: var(--ink); }
.synthese-item .s-val {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9.5pt; font-weight: 700;
    white-space: nowrap;
}
.synthese-item.highlight {
    background: var(--navy-deep);
    border-color: var(--navy-deep);
}
.synthese-item.highlight .s-label { color: rgba(255,255,255,0.8); }
.synthese-item.highlight .s-val { color: var(--gold); }

/* ============================================================
   FOOTER
   ============================================================ */
.report-footer {
    margin-top: 10px;
    padding: 6px 18px;
    border-top: 2px solid var(--navy);
    display: flex; justify-content: space-between; align-items: center;
    font-size: 6.5pt; color: var(--ink-soft);
    background: var(--bg-row);
}
.report-footer .footer-brand { font-weight: 700; color: var(--navy); }
.report-footer .footer-conf {
    background: var(--gold);
    color: var(--navy-deep);
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 700; font-size: 6pt;
    letter-spacing: 0.5px;
}
</style>
</head>
<body>

<?php
/* ============================================================
   PHP CALCULATIONS
   ============================================================ */
$db    = getDB();
$id_ent = $entreprise['id'];
$mois_abbr = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

// CA cumulé
$stmt = $db->prepare("SELECT COALESCE(SUM(le.credit-le.debit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%' AND MONTH(e.date_ecriture)<=?");
$stmt->execute([$id_ent,$exercice,$mois_courant]); $ca_cumule=(float)$stmt->fetchColumn();

// Charges cumulées
$stmt = $db->prepare("SELECT COALESCE(SUM(le.debit-le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' AND MONTH(e.date_ecriture)<=?");
$stmt->execute([$id_ent,$exercice,$mois_courant]); $charges_cumulees=(float)$stmt->fetchColumn();

$resultat_cumule = $ca_cumule - $charges_cumulees;
$marge_pct = $ca_cumule > 0 ? round($resultat_cumule / $ca_cumule * 100, 1) : 0;

// Mois précédent
$mois_prec = $mois_courant - 1;
$ca_prec_cum = 0; $ch_prec_cum = 0;
if ($mois_prec >= 1) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(le.credit-le.debit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%' AND MONTH(e.date_ecriture)<=?");
    $stmt->execute([$id_ent,$exercice,$mois_prec]); $ca_prec_cum=(float)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COALESCE(SUM(le.debit-le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' AND MONTH(e.date_ecriture)<=?");
    $stmt->execute([$id_ent,$exercice,$mois_prec]); $ch_prec_cum=(float)$stmt->fetchColumn();
}
$ca_mois      = $ca_cumule - $ca_prec_cum;
$charges_mois = $charges_cumulees - $ch_prec_cum;
$resultat_mois = $ca_mois - $charges_mois;

// Trésorerie
$stmt = $db->prepare("SELECT COALESCE(SUM(le.debit-le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '5%' AND MONTH(e.date_ecriture)<=?");
$stmt->execute([$id_ent,$exercice,$mois_courant]); $tresorerie=(float)$stmt->fetchColumn();

// Créances / Dettes
$stmt = $db->prepare("SELECT COALESCE(SUM(le.debit-le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND c.numero LIKE '411%' AND (le.code_lettrage IS NULL OR le.code_lettrage='')");
$stmt->execute([$id_ent]); $creances=(float)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT COALESCE(SUM(le.credit-le.debit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND c.numero LIKE '401%' AND (le.code_lettrage IS NULL OR le.code_lettrage='')");
$stmt->execute([$id_ent]); $dettes=(float)$stmt->fetchColumn();

// Évolution mensuelle
$evo = []; $max_val = 1;
for ($m=1; $m<=$mois_courant; $m++) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(le.credit-le.debit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%' AND MONTH(e.date_ecriture)=?");
    $stmt->execute([$id_ent,$exercice,$m]); $ca_m=(float)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COALESCE(SUM(le.debit-le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' AND MONTH(e.date_ecriture)=?");
    $stmt->execute([$id_ent,$exercice,$m]); $ch_m=(float)$stmt->fetchColumn();
    $evo[$m] = ['ca'=>$ca_m,'ch'=>$ch_m];
    $max_val = max($max_val, $ca_m, $ch_m);
}

// Top produits & charges
$stmt = $db->prepare("SELECT c.numero,c.intitule,COALESCE(SUM(le.credit-le.debit),0) as t FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%' AND MONTH(e.date_ecriture)<=? AND LENGTH(c.numero)<=4 GROUP BY c.id HAVING t>0 ORDER BY t DESC LIMIT 5");
$stmt->execute([$id_ent,$exercice,$mois_courant]); $top_produits=$stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT c.numero,c.intitule,COALESCE(SUM(le.debit-le.credit),0) as t FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' AND MONTH(e.date_ecriture)<=? AND LENGTH(c.numero)<=4 GROUP BY c.id HAVING t>0 ORDER BY t DESC LIMIT 5");
$stmt->execute([$id_ent,$exercice,$mois_courant]); $top_charges=$stmt->fetchAll(PDO::FETCH_ASSOC);

function fmtN($v) {
    return number_format(abs($v), 0, ',', ' ') . ' F';
}
$taux_charges = $ca_cumule > 0 ? round($charges_cumulees/$ca_cumule*100,1) : 0;
?>

<!-- NO-PRINT TOOLBAR -->
<div class="no-print">
    <button class="btn btn-print" onclick="window.print()">⎙ Imprimer / PDF</button>
    <button class="btn btn-close" onclick="window.close()">✕ Fermer</button>
    <span class="hint">Chrome → Imprimer → Enregistrer au format PDF · Portrait · Marges : Aucune</span>
</div>

<div class="page">

    <!-- HEADER -->
    <div class="report-header">
        <div class="header-left">
            <div class="header-logo">
                <?php if(!empty($entreprise['logo'])): ?>
                    <img src="<?= APP_URL ?>/logos/<?= e($entreprise['logo']) ?>" alt="Logo">
                <?php else: ?>
                    <div class="initials"><?= strtoupper(substr($entreprise['raison_sociale'],0,2)) ?></div>
                <?php endif; ?>
            </div>
            <div class="header-company">
                <div class="name"><?= e($entreprise['raison_sociale']) ?></div>
                <div class="meta">
                    <span><?= e($entreprise['forme_juridique']) ?> · <?= e($entreprise['code_dossier']) ?></span><br>
                    <?php if(!empty($entreprise['ninea'])): ?><span>NINEA : <?= e($entreprise['ninea']) ?></span><?php endif; ?>
                    <?php if(!empty($entreprise['rccm'])): ?><span>RCCM : <?= e($entreprise['rccm']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">Rapport de Gestion</div>
            <div class="doc-period"><?= $mois_labels[$mois_courant] ?> <?= $exercice ?></div>
            <div class="doc-sub">Généré le <?= date('d/m/Y à H:i') ?></div>
        </div>
    </div>
    <div class="gold-bar"></div>

    <div class="cabinet-line">
        <span><span class="cab-name">SenCompta</span> — Expert-comptable &amp; Commissaire aux comptes</span>
        <span class="period-tag">Jan – <?= substr($mois_labels[$mois_courant],0,3) ?> <?= $exercice ?></span>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- KPIs LIGNE 1 -->
        <div class="section-title">Indicateurs clés de performance</div>
        <div class="kpi-grid-4">
            <div class="kpi-card c-green">
                <div class="kpi-label">CA cumulé (<?= $mois_courant ?>M)</div>
                <div class="kpi-val green"><?= fmtN($ca_cumule) ?></div>
                <div class="kpi-sub">Ce mois : <?= fmtN($ca_mois) ?></div>
            </div>
            <div class="kpi-card c-red">
                <div class="kpi-label">Charges cumulées</div>
                <div class="kpi-val red"><?= fmtN($charges_cumulees) ?></div>
                <div class="kpi-sub">Ce mois : <?= fmtN($charges_mois) ?></div>
            </div>
            <div class="kpi-card <?= $resultat_cumule>=0?'c-blue':'c-red' ?>">
                <div class="kpi-label">Résultat net cumulé</div>
                <div class="kpi-val <?= $resultat_cumule>=0?'blue':'red' ?>"><?= ($resultat_cumule<0?'– ':'').fmtN($resultat_cumule) ?></div>
                <div class="kpi-sub">Marge nette : <?= $marge_pct ?>%</div>
            </div>
            <div class="kpi-card <?= $tresorerie>=0?'c-navy':'c-red' ?>">
                <div class="kpi-label">Trésorerie nette</div>
                <div class="kpi-val <?= $tresorerie>=0?'navy':'red' ?>"><?= ($tresorerie<0?'– ':'').fmtN($tresorerie) ?></div>
                <div class="kpi-sub">&nbsp;</div>
            </div>
        </div>

        <!-- KPIs LIGNE 2 -->
        <div class="kpi-grid-3">
            <div class="kpi-card c-gold">
                <div class="kpi-label">Créances clients (non lettrées)</div>
                <div class="kpi-val navy"><?= fmtN($creances) ?></div>
            </div>
            <div class="kpi-card c-orange">
                <div class="kpi-label">Dettes fournisseurs (non lettrées)</div>
                <div class="kpi-val navy"><?= fmtN($dettes) ?></div>
            </div>
            <div class="kpi-card c-blue">
                <div class="kpi-label">Taux de charges / CA</div>
                <div class="kpi-val blue"><?= $taux_charges ?>%</div>
                <div class="kpi-sub">Résultat mois : <?= ($resultat_mois<0?'– ':'').fmtN($resultat_mois) ?></div>
            </div>
        </div>

        <!-- ÉVOLUTION MENSUELLE -->
        <div class="section-title">Évolution mensuelle — CA vs Charges</div>
        <table>
            <thead>
                <tr>
                    <th style="width:40px">Mois</th>
                    <th class="r">CA réalisé</th>
                    <th class="r">Charges</th>
                    <th class="r">Résultat</th>
                    <th style="width:34%">Visualisation</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $ca_tot=0; $ch_tot=0;
            foreach($evo as $m => $v):
                $ca_tot+=$v['ca']; $ch_tot+=$v['ch'];
                $res_m = $v['ca']-$v['ch'];
                $ca_w = $max_val>0 ? round($v['ca']/$max_val*100) : 0;
                $ch_w = $max_val>0 ? round($v['ch']/$max_val*100) : 0;
            ?>
            <tr>
                <td class="bold"><?= $mois_abbr[$m-1] ?></td>
                <td class="r" style="color:var(--green)"><?= fmtN($v['ca']) ?></td>
                <td class="r" style="color:var(--red)"><?= fmtN($v['ch']) ?></td>
                <td class="r" style="color:<?= $res_m>=0?'var(--blue)':'var(--red)' ?>;font-weight:700"><?= ($res_m<0?'– ':'').fmtN($res_m) ?></td>
                <td style="padding:3px 7px">
                    <div style="display:flex;align-items:center;gap:2px">
                        <div style="width:<?= $ca_w ?>%;height:5px;background:#15803d;border-radius:2px;min-width:<?= $v['ca']>0?2:0 ?>px"></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:2px;margin-top:2px">
                        <div style="width:<?= $ch_w ?>%;height:5px;background:#b91c1c;border-radius:2px;min-width:<?= $v['ch']>0?2:0 ?>px"></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr class="tr-total">
                <td class="bold">TOTAL</td>
                <td class="r"><?= fmtN($ca_tot) ?></td>
                <td class="r"><?= fmtN($ch_tot) ?></td>
                <td class="r"><?= ($ca_tot-$ch_tot<0?'– ':'').fmtN($ca_tot-$ch_tot) ?></td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <!-- DÉTAIL PRODUITS & CHARGES -->
        <div class="two-cols" style="margin-top:10px">
            <div>
                <div class="section-title">Détail des produits</div>
                <table class="detail-table">
                    <thead><tr><th>Compte</th><th class="r">Montant</th><th class="pct">%</th></tr></thead>
                    <tbody>
                    <?php foreach($top_produits as $p):
                        $pct = $ca_cumule>0 ? round($p['t']/$ca_cumule*100) : 0;
                    ?>
                    <tr>
                        <td>
                            <span style="font-family:'Courier New',monospace;font-size:6.5pt;color:var(--ink-soft)"><?= e($p['numero']) ?></span>
                            <span style="margin-left:4px"><?= e($p['intitule']) ?></span>
                            <div class="bar-row"><div class="bar-fill" style="width:<?= $pct ?>%;background:#15803d"></div></div>
                        </td>
                        <td class="r" style="color:var(--green);font-weight:700"><?= fmtN($p['t']) ?></td>
                        <td class="pct"><?= $pct ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($top_produits)): ?>
                    <tr><td colspan="3" style="text-align:center;padding:10px;color:var(--ink-soft)">Aucun produit</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div>
                <div class="section-title">Détail des charges</div>
                <table class="detail-table">
                    <thead><tr><th>Compte</th><th class="r">Montant</th><th class="pct">%</th></tr></thead>
                    <tbody>
                    <?php foreach($top_charges as $c):
                        $pct = $charges_cumulees>0 ? round($c['t']/$charges_cumulees*100) : 0;
                    ?>
                    <tr>
                        <td>
                            <span style="font-family:'Courier New',monospace;font-size:6.5pt;color:var(--ink-soft)"><?= e($c['numero']) ?></span>
                            <span style="margin-left:4px"><?= e($c['intitule']) ?></span>
                            <div class="bar-row"><div class="bar-fill" style="width:<?= $pct ?>%;background:#b91c1c"></div></div>
                        </td>
                        <td class="r" style="color:var(--red);font-weight:700"><?= fmtN($c['t']) ?></td>
                        <td class="pct"><?= $pct ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($top_charges)): ?>
                    <tr><td colspan="3" style="text-align:center;padding:10px;color:var(--ink-soft)">Aucune charge</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SYNTHÈSE FINANCIÈRE -->
        <div class="section-title">Synthèse financière</div>
        <div class="synthese-grid">
            <div class="synthese-item" style="background:#f0fdf4;border-color:#bbf7d0">
                <span class="s-label">Chiffre d'affaires cumulé</span>
                <span class="s-val" style="color:var(--green)"><?= fmtN($ca_cumule) ?></span>
            </div>
            <div class="synthese-item" style="background:<?= $tresorerie>=0?'#eff6ff':'#fef2f2' ?>;border-color:<?= $tresorerie>=0?'#bfdbfe':'#fecaca' ?>">
                <span class="s-label">Trésorerie nette</span>
                <span class="s-val" style="color:<?= $tresorerie>=0?'var(--blue)':'var(--red)' ?>"><?= ($tresorerie<0?'– ':'').fmtN($tresorerie) ?></span>
            </div>
            <div class="synthese-item" style="background:#fef2f2;border-color:#fecaca">
                <span class="s-label">Total charges cumulées</span>
                <span class="s-val" style="color:var(--red)">– <?= fmtN($charges_cumulees) ?></span>
            </div>
            <div class="synthese-item" style="background:#f8fafc;border-color:var(--border)">
                <span class="s-label">Créances clients (non lettrées)</span>
                <span class="s-val"><?= fmtN($creances) ?></span>
            </div>
            <div class="synthese-item highlight">
                <span class="s-label">Résultat net <?= $mois_courant ?>M</span>
                <span class="s-val"><?= ($resultat_cumule<0?'– ':'').fmtN($resultat_cumule) ?></span>
            </div>
            <div class="synthese-item" style="background:#f8fafc;border-color:var(--border)">
                <span class="s-label">Dettes fournisseurs (non lettrées)</span>
                <span class="s-val"><?= fmtN($dettes) ?></span>
            </div>
        </div>

    </div><!-- /content -->

    <!-- FOOTER -->
    <div class="report-footer">
        <span><span class="footer-brand">SenCompta</span> — Rapport confidentiel · Document généré automatiquement</span>
        <span class="footer-conf">CONFIDENTIEL</span>
        <span><?= e($entreprise['raison_sociale']) ?> · Exercice <?= $exercice ?> · <?= $mois_labels[$mois_courant] ?></span>
    </div>

</div><!-- /page -->
</body>
</html>
