<?php
// $bilan, $entreprise, $exercice available
$a = $bilan['actif'];
$p = $bilan['passif'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Bilan — <?= htmlspecialchars($entreprise['raison_sociale']) ?> — <?= $exercice ?></title>
<style>
@page { size: A4 landscape; margin: 1.2cm; }
@media print { body { font-size: 9pt; } .no-print { display: none !important; } }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; color: #111; background: #fff; font-size: 10px; }
.no-print { padding: 12px 20px; background: #f0f3f8; border-bottom: 2px solid #1e3a5f; display: flex; gap: 10px; align-items: center; }
.btn-print { padding: 8px 20px; background: #1e3a5f; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
.btn-close  { padding: 8px 20px; background: #fff; color: #333; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; font-size: 13px; }
.print-area { padding: 16px 20px; }
.doc-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 14px; }
.company-block .name { font-size: 16pt; font-weight: bold; color: #1e3a5f; }
.company-block .meta { font-size: 8pt; color: #555; margin-top: 3px; line-height: 1.6; }
.doc-block { text-align: right; }
.doc-block .title { font-size: 14pt; font-weight: bold; color: #1e3a5f; }
.doc-block .subtitle { font-size: 8.5pt; color: #555; margin-top: 3px; }
.equilibre { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 9pt; font-weight: bold; margin-top: 6px; }
.equilibre-ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.equilibre-ko { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.bilan-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.col-header { background: #1e3a5f; color: white; padding: 7px 10px; font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-align: center; }
table { width: 100%; border-collapse: collapse; }
th { background: #e8edf4; color: #1e3a5f; padding: 5px 8px; text-align: left; font-size: 8pt; text-transform: uppercase; border-bottom: 1px solid #c5d0e0; }
th.num { text-align: right; }
td { padding: 4px 8px; border-bottom: 1px solid #eef1f6; font-size: 9pt; vertical-align: middle; }
td.num { text-align: right; font-family: monospace; font-size: 8.5pt; }
.section-row td { background: #dde5f0; font-weight: 700; font-size: 8pt; text-transform: uppercase; color: #1e3a5f; padding: 5px 8px; }
.subtotal-row td { background: #f0f3f8; font-weight: 600; }
.total-row td { background: #1e3a5f; color: white; font-weight: bold; font-size: 10pt; }
.total-row td.num { font-family: monospace; }
.neg { color: #dc2626; }

.footer { margin-top: 18px; font-size: 7.5pt; color: #888; border-top: 1px solid #ddd; padding-top: 8px; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Enregistrer PDF</button>
    <button class="btn-close" onclick="window.close()">Fermer</button>
    <span style="font-size:12px;color:#555;margin-left:10px">Conseils : Firefox ou Chrome → "Enregistrer au format PDF" · Orientation paysage</span>
</div>

<div class="print-area">
    <div class="doc-header">
        <div class="company-block" style="display:flex;align-items:center;gap:14px">
            <?php if(!empty($entreprise['logo'])): ?>
            <img src="<?= APP_URL ?>/logos/<?= htmlspecialchars($entreprise['logo']) ?>" alt="Logo" style="height:60px;width:auto;max-width:140px;object-fit:contain">
            <?php endif; ?>
            <div>
                <div class="name"><?= htmlspecialchars($entreprise['raison_sociale']) ?></div>
                <div class="meta">
                    NINEA : <?= htmlspecialchars($entreprise['ninea'] ?? '—') ?> &nbsp;|&nbsp;
                    RCCM : <?= htmlspecialchars($entreprise['rccm'] ?? '—') ?><br>
                    <?= htmlspecialchars($entreprise['adresse'] ?? '') ?><br>
                    Régime fiscal : <?= htmlspecialchars($entreprise['regime_fiscal'] ?? '—') ?> &nbsp;|&nbsp;
                    <?= htmlspecialchars($entreprise['forme_juridique'] ?? '') ?>
                </div>
            </div>
        </div>
        <div class="doc-block">
            <div class="title">BILAN</div>
            <div class="subtitle">Exercice du 01/01/<?= $exercice ?> au 31/12/<?= $exercice ?><br>SYSCOHADA Révisé</div>
            <?php if ($bilan['equilibre']): ?>
                <span class="equilibre equilibre-ok">Bilan équilibré</span>
            <?php else: ?>
                <span class="equilibre equilibre-ko">Déséquilibre : <?= number_format(abs($bilan['ecart']),0,',',' ') ?> F</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="bilan-wrap">
        <!-- ACTIF -->
        <div>
            <div class="col-header">ACTIF</div>
            <table>
                <thead><tr><th>Désignation</th><th class="num">Brut</th><th class="num">Amort/Dép.</th><th class="num">Net N</th></tr></thead>
                <tbody>
                <tr class="section-row"><td colspan="4">ACTIF IMMOBILISÉ</td></tr>
                <tr><td>Immobilisations incorporelles</td>
                    <td class="num"><?= number_format($a['immobilise']['incorporelles']['brut'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['immobilise']['incorporelles']['amort'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['immobilise']['incorporelles']['net'],0,',',' ') ?></td></tr>
                <tr><td>Immobilisations corporelles</td>
                    <td class="num"><?= number_format($a['immobilise']['corporelles']['brut'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['immobilise']['corporelles']['amort'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['immobilise']['corporelles']['net'],0,',',' ') ?></td></tr>
                <tr><td>Immobilisations financières</td>
                    <td class="num"><?= number_format($a['immobilise']['financieres']['brut'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['immobilise']['financieres']['dep'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['immobilise']['financieres']['net'],0,',',' ') ?></td></tr>
                <tr><td>Immobilisations en cours</td>
                    <td class="num"><?= number_format($a['immobilise']['en_cours'],0,',',' ') ?></td>
                    <td class="num">—</td>
                    <td class="num"><?= number_format($a['immobilise']['en_cours'],0,',',' ') ?></td></tr>
                <tr class="subtotal-row"><td><strong>Total actif immobilisé</strong></td>
                    <td></td><td></td>
                    <td class="num"><strong><?= number_format($a['immobilise']['total'],0,',',' ') ?></strong></td></tr>

                <tr class="section-row"><td colspan="4">ACTIF CIRCULANT</td></tr>
                <tr><td>Stocks et encours</td>
                    <td class="num"><?= number_format($a['circulant']['stocks']['brut'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['circulant']['stocks']['dep'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['circulant']['stocks']['net'],0,',',' ') ?></td></tr>
                <tr><td>Créances clients</td>
                    <td class="num"><?= number_format($a['circulant']['clients']['brut'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['circulant']['clients']['dep'],0,',',' ') ?></td>
                    <td class="num"><?= number_format($a['circulant']['clients']['net'],0,',',' ') ?></td></tr>
                <tr><td>Autres créances</td>
                    <td class="num"><?= number_format($a['circulant']['autres_creances'],0,',',' ') ?></td>
                    <td class="num">—</td>
                    <td class="num"><?= number_format($a['circulant']['autres_creances'],0,',',' ') ?></td></tr>
                <tr class="subtotal-row"><td><strong>Total actif circulant</strong></td>
                    <td></td><td></td>
                    <td class="num"><strong><?= number_format($a['circulant']['total'],0,',',' ') ?></strong></td></tr>

                <tr class="section-row"><td colspan="4">TRÉSORERIE ACTIVE</td></tr>
                <tr><td>Disponibilités (50x-58x)</td>
                    <td class="num"><?= number_format($a['tresorerie'],0,',',' ') ?></td>
                    <td class="num">—</td>
                    <td class="num"><?= number_format($a['tresorerie'],0,',',' ') ?></td></tr>

                <tr class="total-row">
                    <td>TOTAL ACTIF</td><td></td><td></td>
                    <td class="num"><?= number_format($a['total'],0,',',' ') ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- PASSIF -->
        <div>
            <div class="col-header">PASSIF</div>
            <table>
                <thead><tr><th>Désignation</th><th class="num">Montant N</th></tr></thead>
                <tbody>
                <tr class="section-row"><td colspan="2">CAPITAUX PROPRES</td></tr>
                <tr><td>Capital social</td><td class="num"><?= number_format($p['capitaux_propres']['capital'],0,',',' ') ?></td></tr>
                <tr><td>Primes et réserves</td><td class="num"><?= number_format($p['capitaux_propres']['primes']+$p['capitaux_propres']['reserves'],0,',',' ') ?></td></tr>
                <tr><td>Report à nouveau créditeur</td><td class="num"><?= number_format($p['capitaux_propres']['report_crediteur'],0,',',' ') ?></td></tr>
                <?php if($p['capitaux_propres']['report_debiteur'] > 0): ?>
                <tr><td>Report à nouveau débiteur</td><td class="num neg">( <?= number_format($p['capitaux_propres']['report_debiteur'],0,',',' ') ?> )</td></tr>
                <?php endif; ?>
                <tr><td>Résultat de l'exercice</td>
                    <td class="num <?= $p['capitaux_propres']['resultat_net'] < 0 ? 'neg' : '' ?>">
                        <?= $p['capitaux_propres']['resultat_net'] < 0 ? '(' : '' ?>
                        <?= number_format(abs($p['capitaux_propres']['resultat_net']),0,',',' ') ?>
                        <?= $p['capitaux_propres']['resultat_net'] < 0 ? ')' : '' ?>
                    </td></tr>
                <?php if($p['capitaux_propres']['subventions_inv'] > 0): ?>
                <tr><td>Subventions d'investissement</td><td class="num"><?= number_format($p['capitaux_propres']['subventions_inv'],0,',',' ') ?></td></tr>
                <?php endif; ?>
                <tr class="subtotal-row"><td><strong>Total capitaux propres</strong></td><td class="num"><strong><?= number_format($p['capitaux_propres']['total'],0,',',' ') ?></strong></td></tr>

                <tr class="section-row"><td colspan="2">RESSOURCES STABLES</td></tr>
                <tr><td>Emprunts long terme</td><td class="num"><?= number_format($p['dettes_fin']['emprunts'],0,',',' ') ?></td></tr>
                <tr><td>Autres dettes financières</td><td class="num"><?= number_format($p['dettes_fin']['autres'],0,',',' ') ?></td></tr>
                <tr><td>Provisions pour risques</td><td class="num"><?= number_format($p['provisions'],0,',',' ') ?></td></tr>
                <tr class="subtotal-row"><td><strong>Total ressources durables</strong></td><td class="num"><strong><?= number_format($p['ressources_durables'],0,',',' ') ?></strong></td></tr>

                <tr class="section-row"><td colspan="2">PASSIF CIRCULANT</td></tr>
                <tr><td>Dettes fournisseurs</td><td class="num"><?= number_format($p['passif_circulant']['fournisseurs'],0,',',' ') ?></td></tr>
                <tr><td>Dettes fiscales</td><td class="num"><?= number_format($p['passif_circulant']['dettes_fiscales'],0,',',' ') ?></td></tr>
                <tr><td>Dettes sociales</td><td class="num"><?= number_format($p['passif_circulant']['dettes_sociales'],0,',',' ') ?></td></tr>
                <tr><td>Autres dettes</td><td class="num"><?= number_format($p['passif_circulant']['autres_dettes'],0,',',' ') ?></td></tr>
                <tr class="subtotal-row"><td><strong>Total passif circulant</strong></td><td class="num"><strong><?= number_format($p['passif_circulant']['total'],0,',',' ') ?></strong></td></tr>

                <tr class="section-row"><td colspan="2">TRÉSORERIE PASSIVE</td></tr>
                <tr><td>Concours bancaires</td><td class="num"><?= number_format($p['tresorerie_passive'],0,',',' ') ?></td></tr>

                <tr class="total-row">
                    <td>TOTAL PASSIF</td>
                    <td class="num"><?= number_format($p['total'],0,',',' ') ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <span>SenCompta — Expertise Comptable · Audit · Conseil</span>
        <span>Document généré le <?= date('d/m/Y à H:i') ?></span>
        <span>Exercice <?= $exercice ?> — SYSCOHADA Révisé</span>
    </div>
</div>
</body>
</html>
