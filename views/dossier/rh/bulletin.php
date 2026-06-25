<?php
$mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$total_retenues = $bulletin['total_retenues'] ?? (
    ($bulletin['ipres_salarie'] ?? 0)
    + ($bulletin['trimf'] ?? 0)
    + ($bulletin['ir_salarie'] ?? 0)
    + ($bulletin['ipm_salarie'] ?? 0)
);
$salaire_brut = $bulletin['salaire_brut'] ?? (
    ($bulletin['salaire_base'] ?? 0)
    + ($bulletin['sursalaire'] ?? 0)
    + ($bulletin['indemnite_logement'] ?? 0)
    + ($bulletin['indemnite_transport'] ?? 0)
    + ($bulletin['indemnite_representation'] ?? 0)
    + ($bulletin['heures_supp'] ?? 0)
    + ($bulletin['primes'] ?? 0)
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Bulletin de paie — <?= e($bulletin['nom'].' '.$bulletin['prenom']) ?> — <?= $mois_noms[(int)$bulletin['periode_mois']] ?> <?= $bulletin['periode_annee'] ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f0f0f0;
    padding: 32px 16px 40px;
    color: #1a1a1a;
    font-size: 15px;
}

/* ── TOOLBAR ── */
.no-print {
    max-width: 210mm;
    margin: 0 auto 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-radius: 6px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.btn-imprimer {
    background: #1e3a5f;
    color: #fff;
    border: none;
    padding: 9px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.btn-retour {
    background: #fff;
    color: #1e3a5f;
    border: 1.5px solid #1e3a5f;
    padding: 9px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

/* ── FEUILLE ── */
.bulletin {
    max-width: 210mm;
    margin: 0 auto;
    background: #ffffff;
    box-shadow: 0 4px 24px rgba(0,0,0,.12);
    display: flex;
    flex-direction: column;
    min-height: 270mm;
}
.bulletin-body { flex: 1; }


/* Barre de couleur top */
.top-bar {
    height: 5px;
    background: #1e3a5f;
}

/* ── EN-TÊTE ── */
.entete {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    padding: 20px 28px 16px;
    border-bottom: 2px solid #1e3a5f;
    gap: 12px;
}
.entete-societe-nom {
    font-size: 18px;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 4px;
}
.entete-societe-meta {
    font-size: 9.5px;
    color: #333;
    line-height: 1.7;
}
.entete-titre {
    text-align: center;
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    padding: 0 20px;
}
.entete-titre-label {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: #444;
    display: block;
    margin-bottom: 5px;
}
.entete-titre-periode {
    font-size: 18px;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.1;
}
.entete-titre-annee {
    font-size: 16px;
    color: #333;
    display: block;
    margin-top: 2px;
}
.entete-meta {
    text-align: right;
}
.meta-row {
    margin-bottom: 5px;
}
.meta-label {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #444;
    display: block;
}
.meta-val {
    font-size: 14px;
    color: #1a1a1a;
    font-weight: 600;
}

/* ── BANDE EMPLOYÉ / EMPLOYEUR ── */
.emp-band {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-bottom: 1px solid #ddd;
}
.emp-col {
    padding: 14px 28px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.emp-col-left { background: #f7f9fc; border-right: 1px solid #ddd; }
.emp-col-right { background: #fff; }
.emp-section-title {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #1e3a5f;
    font-weight: 700;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
    margin-bottom: 2px;
}
.emp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.emp-field-label {
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #444;
    display: block;
    margin-bottom: 1px;
}
.emp-field-val {
    font-size: 14px;
    color: #1a1a1a;
    font-weight: 500;
}
.emp-name { font-size: 17px; font-weight: 700; color: #1e3a5f; }

/* ── TABLEAU RÉMUNÉRATION ── */
.section-header {
    background: #1e3a5f;
    padding: 6px 28px;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: #fff;
    font-weight: 700;
}

table.renum {
    width: 100%;
    border-collapse: collapse;
}
table.renum thead th {
    background: #f0f4f8;
    padding: 7px 28px;
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #333;
    font-weight: 700;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
table.renum thead th:not(:first-child) { text-align: right; }

table.renum tbody tr { border-bottom: 1px solid #eee; }
table.renum tbody tr:nth-child(even) { background: #fafafa; }
table.renum tbody td {
    padding: 6px 28px;
    font-size: 14px;
    color: #333;
}
table.renum tbody td:not(:first-child) {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-size: 14px;
}
.td-gain { color: #1a5c34; font-weight: 600; }
.td-retenue { color: #b91c1c; font-weight: 600; }

/* Ligne sous-total (brut / total retenues) */
.tr-subtotal td {
    background: #eef2f7 !important;
    font-weight: 700 !important;
    color: #1e3a5f !important;
    font-size: 15px !important;
    padding: 8px 28px !important;
    border-top: 1px solid #c5d0de !important;
    border-bottom: 1px solid #c5d0de !important;
}
.tr-subtotal td:not(:first-child) {
    font-family: 'Courier New', monospace !important;
    text-align: right !important;
}

/* ── NET À PAYER ── */
.net-block {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 28px;
    border-top: 3px solid #1e3a5f;
    border-bottom: 3px solid #1e3a5f;
    background: #f7f9fc;
}
.net-label {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #333;
    display: block;
    margin-bottom: 2px;
}
.net-sublabel { font-size: 9px; color: #444; }
.net-montant {
    font-size: 28px;
    font-weight: 700;
    color: #1e3a5f;
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
}
.net-currency {
    font-size: 17px;
    color: #333;
    margin-left: 5px;
}

/* ── CHARGES PATRONALES ── */
.pat-band {
    padding: 12px 28px;
    background: #fff;
    border-bottom: 1px solid #ddd;
}
.pat-title {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #444;
    font-weight: 700;
    margin-bottom: 8px;
}
.pat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
}
.pat-item { padding-right: 16px; }
.pat-item:not(:last-child) { border-right: 1px solid #eee; margin-right: 16px; }
.pat-item-label { font-size: 9px; color: #333; font-weight: 600; margin-bottom: 2px; }
.pat-item-val {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    font-family: 'Courier New', monospace;
}
.pat-item-total .pat-item-val { color: #1e3a5f; font-size: 16px; }

/* ── FOOTER ── */
.bul-footer {
    padding: 10px 28px 14px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-top: 1px solid #eee;
}
.footer-legal { font-size: 9px; color: #222; font-style: italic; line-height: 1.6; }
.footer-right { font-size: 9.5px; color: #333; text-align: right; }
.footer-right strong { display: block; font-size: 13px; color: #1e3a5f; margin-top: 2px; font-style: normal; }

.bottom-bar { height: 4px; background: #1e3a5f; }

/* ── PRINT ── */
@media print {
    body { background: white; padding: 0; }
    .no-print { display: none !important; }
    .bulletin { box-shadow: none; max-width: 100%; min-height: auto; }
    .top-bar, .bottom-bar, .section-header, .net-block { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .section-header { background: #1e3a5f !important; }
    .emp-col-left { background: #f7f9fc !important; }
    .net-block { background: #f7f9fc !important; }

    /* Empêcher les coupures à l'intérieur des blocs */
    .emp-band,
    .net-block,
    .pat-band,
    .bul-footer,
    .bottom-bar,
    .section-header,
    table.renum,
    table.renum tbody tr,
    .tr-subtotal { page-break-inside: avoid; break-inside: avoid; }

    /* Footer fixé en bas de page à l'impression */
    .bul-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        border-top: 1px solid #ddd;
        background: #fff;
        padding: 8px 28px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #1e3a5f !important;
    }
    .entete { page-break-after: avoid; break-after: avoid; }
    .net-block { page-break-before: avoid; break-before: avoid; }

    @page { size: A4 portrait; margin: 8mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <div style="font-size:14px;color:#333">
        <strong style="color:#1e3a5f"><?= e($bulletin['nom'].' '.$bulletin['prenom']) ?></strong>
        — Bulletin <?= $mois_noms[(int)$bulletin['periode_mois']] ?> <?= $bulletin['periode_annee'] ?>
    </div>
    <div style="display:flex;gap:10px">
        <button onclick="window.print()" class="btn-imprimer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" /></svg>
            Imprimer / PDF
        </button>
        <a href="<?= APP_URL ?>/dossier/rh/bulletins?id=<?= $entreprise['id'] ?>" class="btn-retour">← Retour</a>
    </div>
</div>

<div class="bulletin">
    <div class="top-bar"></div>

    <!-- EN-TÊTE -->
    <div class="entete">
        <div>
            <?php
            $logoPath = APP_ROOT . '/public/logos/' . ($entreprise['logo'] ?? '');
            if (!empty($entreprise['logo']) && file_exists($logoPath)):
                $logoMime = mime_content_type($logoPath) ?: 'image/png';
                $logoB64  = base64_encode(file_get_contents($logoPath));
            ?>
            <img src="data:<?= $logoMime ?>;base64,<?= $logoB64 ?>" alt="Logo"
                 style="height:50px;width:auto;max-width:120px;object-fit:contain;display:block;margin-bottom:8px">
            <?php endif; ?>
            <div class="entete-societe-nom"><?= e($entreprise['raison_sociale']) ?></div>
            <div class="entete-societe-meta">
                <?= e($entreprise['forme_juridique'] ?? '') ?>
                <?php if(!empty($entreprise['ninea'])): ?>&nbsp;·&nbsp;NINEA : <?= e($entreprise['ninea']) ?><?php endif; ?>
                <?php if(!empty($entreprise['rccm'])): ?>&nbsp;·&nbsp;RCCM : <?= e($entreprise['rccm']) ?><?php endif; ?>
                <?php if(!empty($entreprise['adresse'])): ?><br><?= e($entreprise['adresse']) ?><?php endif; ?>
            </div>
        </div>

        <div class="entete-titre">
            <span class="entete-titre-label">Bulletin de Paie</span>
            <div class="entete-titre-periode"><?= $mois_noms[(int)$bulletin['periode_mois']] ?></div>
            <span class="entete-titre-annee"><?= $bulletin['periode_annee'] ?></span>
        </div>

        <div class="entete-meta">
            <div class="meta-row">
                <span class="meta-label">Matricule</span>
                <span class="meta-val"><?= e($bulletin['matricule'] ?: '—') ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Date de paiement</span>
                <span class="meta-val">Fin <?= $mois_noms[(int)$bulletin['periode_mois']] ?> <?= $bulletin['periode_annee'] ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Émis le</span>
                <span class="meta-val"><?= date('d/m/Y') ?></span>
            </div>
        </div>
    </div>

    <div class="bulletin-body">
    <!-- EMPLOYÉ / EMPLOYEUR -->
    <div class="emp-band">
        <div class="emp-col emp-col-left">
            <div class="emp-section-title">Salarié</div>
            <div>
                <span class="emp-field-label">Nom & Prénom</span>
                <span class="emp-name"><?= e($bulletin['nom'].' '.$bulletin['prenom']) ?></span>
            </div>
            <div class="emp-grid">
                <div>
                    <span class="emp-field-label">Poste</span>
                    <span class="emp-field-val"><?= e($bulletin['poste'] ?: '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">Département</span>
                    <span class="emp-field-val"><?= e($bulletin['departement'] ?: '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">Date embauche</span>
                    <span class="emp-field-val"><?= !empty($bulletin['date_embauche']) ? date('d/m/Y', strtotime($bulletin['date_embauche'])) : '—' ?></span>
                </div>
                <div>
                    <span class="emp-field-label">Type contrat</span>
                    <span class="emp-field-val"><?= e($bulletin['type_contrat'] ?: '—') ?></span>
                </div>
            </div>
        </div>
        <div class="emp-col emp-col-right">
            <div class="emp-section-title">Employeur</div>
            <div>
                <span class="emp-field-label">Raison sociale</span>
                <span class="emp-name"><?= e($entreprise['raison_sociale']) ?></span>
            </div>
            <div class="emp-grid">
                <div>
                    <span class="emp-field-label">N° IPRES salarié</span>
                    <span class="emp-field-val"><?= e($bulletin['num_ipres'] ?: '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">N° IPRES patronal</span>
                    <span class="emp-field-val"><?= e($bulletin['num_ipres_entreprise'] ?: '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">N° CSS employeur</span>
                    <span class="emp-field-val"><?= e($bulletin['num_css_entreprise'] ?: '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">Régime fiscal</span>
                    <span class="emp-field-val"><?= e($bulletin['regime_fiscal'] ?? '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">Banque</span>
                    <span class="emp-field-val"><?= e($bulletin['banque'] ?: '—') ?></span>
                </div>
                <div>
                    <span class="emp-field-label">RIB / IBAN</span>
                    <span class="emp-field-val" style="font-size:13px"><?= e($bulletin['iban'] ?: '—') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLEAU RÉMUNÉRATION -->
    <div class="section-header">Éléments de rémunération</div>
    <table class="renum">
        <thead>
            <tr>
                <th style="width:44%">Rubrique</th>
                <th>Base de calcul</th>
                <th>Taux</th>
                <th>Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Salaire de base</td>
                <td><?= number_format($bulletin['salaire_base'],0,',',' ') ?></td>
                <td>—</td>
                <td class="td-gain"><?= number_format($bulletin['salaire_base'],0,',',' ') ?></td>
            </tr>
            <?php if(($bulletin['sursalaire'] ?? 0) > 0): ?>
            <tr>
                <td>Sursalaire</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['sursalaire'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['indemnite_logement'] ?? 0) > 0): ?>
            <tr>
                <td>Indemnité de logement</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['indemnite_logement'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['indemnite_transport'] ?? 0) > 0): ?>
            <tr>
                <td>Indemnité de transport</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['indemnite_transport'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['indemnite_representation'] ?? 0) > 0): ?>
            <tr>
                <td>Indemnité de représentation</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['indemnite_representation'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['prime_anciennete'] ?? 0) > 0): ?>
            <tr>
                <td>Prime d'ancienneté</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['prime_anciennete'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['heures_supp'] ?? 0) > 0): ?>
            <tr>
                <td>Heures supplémentaires</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['heures_supp'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['primes'] ?? 0) > 0): ?>
            <tr>
                <td>Primes</td>
                <td>—</td><td>—</td>
                <td class="td-gain"><?= number_format($bulletin['primes'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="tr-subtotal">
                <td colspan="3">Salaire brut</td>
                <td><?= number_format($salaire_brut,0,',',' ') ?></td>
            </tr>

            <?php if(($bulletin['ipres_salarie'] ?? 0) > 0): ?>
            <tr>
                <td>Cotisation IPRES (part salariale)</td>
                <td><?= number_format($salaire_brut,0,',',' ') ?></td>
                <td>5,6 %</td>
                <td class="td-retenue">- <?= number_format($bulletin['ipres_salarie'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['trimf'] ?? 0) > 0): ?>
            <tr>
                <td>TRIMF</td>
                <td><?= number_format($salaire_brut,0,',',' ') ?></td>
                <td>Barème</td>
                <td class="td-retenue">- <?= number_format($bulletin['trimf'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['ir_salarie'] ?? 0) > 0): ?>
            <tr>
                <td>Impôt sur le Revenu (IR)</td>
                <td><?= number_format($salaire_brut,0,',',' ') ?></td>
                <td>Barème</td>
                <td class="td-retenue">- <?= number_format($bulletin['ir_salarie'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(($bulletin['ipm_salarie'] ?? 0) > 0): ?>
            <?php
            $ipm_taux_pct = ($salaire_brut > 0)
                ? number_format($bulletin['ipm_salarie'] / $salaire_brut * 100, 2, ',', ' ') . ' %'
                : '0,5 %';
            ?>
            <tr>
                <td>Cotisation IPM (part salariale)</td>
                <td><?= number_format($salaire_brut, 0, ',', ' ') ?></td>
                <td><?= $ipm_taux_pct ?></td>
                <td class="td-retenue">- <?= number_format($bulletin['ipm_salarie'],0,',',' ') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="tr-subtotal">
                <td colspan="3">Total retenues salariales</td>
                <td class="td-retenue" style="color:#b91c1c !important">- <?= number_format($total_retenues,0,',',' ') ?></td>
            </tr>
        </tbody>
    </table>

    </div><!-- /bulletin-body -->

    <!-- NET À PAYER -->
    <div class="net-block">
        <div>
            <span class="net-label">Net à payer</span>
            <span class="net-sublabel">En francs CFA (FCFA)</span>
        </div>
        <div>
            <span class="net-montant"><?= number_format($bulletin['net_a_payer'],0,',',' ') ?></span>
            <span class="net-currency">FCFA</span>
        </div>
    </div>

    <!-- Cumuls annuels — Code Travail Art. L.137 -->
    <div style="margin:0 28px 0;padding:12px 0;border-top:1px solid #c9a96e">
        <div style="font-size:13px;font-weight:700;text-transform:uppercase;color:#1e3a5f;letter-spacing:1px;margin-bottom:8px">
            Cumuls annuels au <?= $mois_noms[(int)$bulletin['periode_mois']] ?? '' ?> <?= $bulletin['periode_annee'] ?>
        </div>
        <table style="width:100%;font-size:14px;border-collapse:collapse">
            <tr style="background:#f8f9fa">
                <td style="padding:4px 8px;border:1px solid #ddd">Salaire brut cumulé</td>
                <td style="padding:4px 8px;border:1px solid #ddd;text-align:right"><?= number_format($cumul_brut ?? 0, 0, ',', ' ') ?> F</td>
            </tr>
            <tr>
                <td style="padding:4px 8px;border:1px solid #ddd">IR cumulé</td>
                <td style="padding:4px 8px;border:1px solid #ddd;text-align:right"><?= number_format($cumul_ir ?? 0, 0, ',', ' ') ?> F</td>
            </tr>
            <tr style="background:#f8f9fa">
                <td style="padding:4px 8px;border:1px solid #ddd">IPRES salarié cumulé</td>
                <td style="padding:4px 8px;border:1px solid #ddd;text-align:right"><?= number_format($cumul_ipres ?? 0, 0, ',', ' ') ?> F</td>
            </tr>
        </table>
    </div>

    <!-- CHARGES PATRONALES -->
    <div class="pat-band">
        <div class="pat-title">Charges patronales (informatives)</div>
        <div class="pat-grid">
            <div class="pat-item">
                <div class="pat-item-label">IPRES patronal</div>
                <div class="pat-item-val"><?= number_format($bulletin['ipres_patronal'] ?? 0,0,',',' ') ?></div>
            </div>
            <div class="pat-item">
                <div class="pat-item-label">CSS patronal</div>
                <div class="pat-item-val"><?= number_format($bulletin['css_total'] ?? 0,0,',',' ') ?></div>
            </div>
            <div class="pat-item">
                <div class="pat-item-label">IPM patronal</div>
                <div class="pat-item-val"><?= number_format($bulletin['ipm_patronal'] ?? 0,0,',',' ') ?></div>
            </div>
            <div class="pat-item pat-item-total">
                <div class="pat-item-label">Coût total employeur</div>
                <div class="pat-item-val"><?= number_format(($bulletin['net_a_payer'] ?? 0) + ($bulletin['total_charges_patronales'] ?? 0),0,',',' ') ?> FCFA</div>
            </div>
        </div>
    </div>

    <!-- CONGÉS -->
    <?php
    $type_labels_conge = [
        'conge_paye'=>'Congé payé','maladie'=>'Maladie','maternite'=>'Maternité',
        'paternite'=>'Paternité','sans_solde'=>'Sans solde','autre'=>'Autre'
    ];
    $jours_acquis      = $solde_conges ? (float)$solde_conges['jours_acquis']       : 0;
    $jours_reportes_n1 = $solde_conges ? (float)$solde_conges['jours_reportes_n1']  : 0;
    $jours_pris        = $solde_conges ? (float)$solde_conges['jours_pris']          : 0;
    $jours_restants    = $solde_conges ? (float)$solde_conges['jours_restants']      : 0;
    ?>
    <div style="padding:12px 28px;border-top:1px solid #ddd;border-bottom:1px solid #ddd;background:#f7f9fc">
        <div style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#444;margin-bottom:10px">Solde des congés — <?= $annee_bulletin ?></div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr) 2px repeat(<?= count($conges_annee) ?: 1 ?>,1fr);gap:0;align-items:stretch">
            <!-- Solde global -->
            <div style="text-align:center;padding:0 12px">
                <div style="font-size:9px;color:#444;margin-bottom:3px">Acquis N</div>
                <div style="font-size:13px;font-weight:700;color:#1e3a5f;font-family:'Courier New',monospace"><?= $jours_acquis ?>j</div>
            </div>
            <div style="text-align:center;padding:0 12px;border-left:1px solid #ddd">
                <div style="font-size:9px;color:#444;margin-bottom:3px">Report N-1</div>
                <div style="font-size:13px;font-weight:700;color:#4338ca;font-family:'Courier New',monospace"><?= $jours_reportes_n1 ?>j</div>
            </div>
            <div style="text-align:center;padding:0 12px;border-left:1px solid #ddd">
                <div style="font-size:9px;color:#444;margin-bottom:3px">Pris</div>
                <div style="font-size:13px;font-weight:700;color:#92400e;font-family:'Courier New',monospace"><?= $jours_pris ?>j</div>
            </div>
            <div style="text-align:center;padding:0 12px;border-left:1px solid #ddd">
                <div style="font-size:9px;color:#444;margin-bottom:3px">Solde total</div>
                <div style="font-size:13px;font-weight:700;color:<?= $jours_restants > 0 ? '#16a34a' : '#dc2626' ?>;font-family:'Courier New',monospace"><?= $jours_restants ?>j</div>
            </div>
            <!-- Séparateur vertical -->
            <div style="background:#ddd;margin:0 16px"></div>
            <!-- Détail par type -->
            <?php if(empty($conges_annee)): ?>
            <div style="text-align:center;padding:0 12px;color:#888;font-size:14px;display:flex;align-items:center;justify-content:center">Aucune absence cette année</div>
            <?php else: ?>
            <?php foreach($conges_annee as $ca): ?>
            <div style="text-align:center;padding:0 12px;border-left:1px solid #eee">
                <div style="font-size:9px;color:#444;margin-bottom:3px"><?= $type_labels_conge[$ca['type_conge']] ?? $ca['type_conge'] ?></div>
                <div style="font-size:14px;font-weight:700;color:#1a1a1a;font-family:'Courier New',monospace"><?= $ca['total'] ?>j</div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="bul-footer">
        <div class="footer-legal">
            Bulletin de paie à conserver sans limitation de durée.<br>
            Émis par <?= e($entreprise['raison_sociale']) ?> — SenCompta Gestion Comptable.
        </div>
        <div class="footer-right">
            Généré le <?= date('d/m/Y à H:i') ?>
            <strong>SenCompta</strong>
        </div>
    </div>

    <div class="bottom-bar"></div>
</div>

</body>
</html>
