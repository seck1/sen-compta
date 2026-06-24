<?php
// $cr, $entreprise, $exercice available
$c = $cr['charges'];
$pr = $cr['produits'];
$r = $cr['resultats'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Compte de résultat — <?= htmlspecialchars($entreprise['raison_sociale']) ?> — <?= $exercice ?></title>
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

.kpi-row { display: grid; grid-template-columns: repeat(5,1fr); gap: 10px; margin-bottom: 14px; }
.kpi-box { border: 1px solid #dde5f0; border-radius: 8px; padding: 10px; text-align: center; }
.kpi-box .lbl { font-size: 7.5pt; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.kpi-box .val { font-size: 12pt; font-weight: bold; }
.pos { color: #166534; } .neg { color: #991b1b; } .neutral { color: #1e3a5f; }

.cr-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.col-header-ch { background: #dc2626; color: white; padding: 7px 10px; font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-align: center; }
.col-header-pr { background: #166534; color: white; padding: 7px 10px; font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-align: center; }
table { width: 100%; border-collapse: collapse; }
td { padding: 4px 8px; border-bottom: 1px solid #eef1f6; font-size: 9pt; vertical-align: middle; }
td.num { text-align: right; font-family: monospace; font-size: 8.5pt; }
.section-row td { background: #e8edf4; font-weight: 700; font-size: 8pt; text-transform: uppercase; color: #1e3a5f; padding: 5px 8px; }
.subtotal-row td { background: #f0f3f8; font-weight: 600; }
.total-row-ch td { background: #dc2626; color: white; font-weight: bold; font-size: 10pt; }
.total-row-pr td { background: #166534; color: white; font-weight: bold; font-size: 10pt; }
.benef-row td { background: #dcfce7; color: #166534; font-weight: 600; }
.perte-row td { background: #fee2e2; color: #991b1b; font-weight: 600; }

.footer { margin-top: 18px; font-size: 7.5pt; color: #888; border-top: 1px solid #ddd; padding-top: 8px; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Enregistrer PDF</button>
    <button class="btn-close" onclick="window.close()">Fermer</button>
    <span style="font-size:12px;color:#555;margin-left:10px">Orientation paysage recommandée</span>
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
                    Régime : <?= htmlspecialchars($entreprise['regime_fiscal'] ?? '—') ?> · <?= htmlspecialchars($entreprise['forme_juridique'] ?? '') ?>
                </div>
            </div>
        </div>
        <div class="doc-block">
            <div class="title">COMPTE DE RÉSULTAT</div>
            <div class="subtitle">Exercice du 01/01/<?= $exercice ?> au 31/12/<?= $exercice ?><br>SYSCOHADA Révisé</div>
        </div>
    </div>

    <!-- KPIs résultats -->
    <div class="kpi-row">
        <?php
        $kpis = [
            ['Résultat exploitation', $r['exploitation']],
            ['Résultat financier', $r['financier']],
            ['Résultat AO', $r['ao']],
            ['Résultat HAO', $r['hao']],
            ['Résultat net', $r['net']],
        ];
        foreach($kpis as [$lbl, $val]):
            $cls = $val >= 0 ? 'pos' : 'neg';
        ?>
        <div class="kpi-box">
            <div class="lbl"><?= $lbl ?></div>
            <div class="val <?= $cls ?>">
                <?= $val < 0 ? '(' : '' ?>
                <?= number_format(abs($val),0,',',' ') ?>
                <?= $val < 0 ? ')' : '' ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cr-wrap">
        <!-- CHARGES -->
        <div>
            <div class="col-header-ch">CHARGES</div>
            <table><tbody>
            <tr class="section-row"><td colspan="2">CHARGES D'EXPLOITATION</td></tr>
            <?php
            $ch_expl = [
                ['Achats marchandises', $c['exploitation']['achats_march']],
                ['Variation stocks march.', $c['exploitation']['var_stocks_march']],
                ['Achats matières premières', $c['exploitation']['achats_mat_prem']],
                ['Achats consommables', $c['exploitation']['achats_consommables']],
                ['Transports', $c['exploitation']['transports']],
                ['Services extérieurs A', $c['exploitation']['services_ext_a']],
                ['Services extérieurs B', $c['exploitation']['services_ext_b']],
                ['Impôts et taxes', $c['exploitation']['impots_taxes']],
                ['Charges de personnel', $c['exploitation']['charges_personnel']],
                ['Autres charges', $c['exploitation']['autres_charges']],
                ['Dotations aux amort.', $c['exploitation']['dot_amort']],
            ];
            foreach($ch_expl as [$lbl, $val]):
                if($val == 0) continue;
            ?>
            <tr><td><?= $lbl ?></td><td class="num"><?= number_format($val,0,',',' ') ?></td></tr>
            <?php endforeach; ?>
            <tr class="subtotal-row"><td><strong>Total charges exploitation</strong></td><td class="num"><strong><?= number_format($c['exploitation']['total'],0,',',' ') ?></strong></td></tr>

            <tr class="section-row"><td colspan="2">CHARGES FINANCIÈRES</td></tr>
            <?php if($c['financieres']['interets']): ?><tr><td>Intérêts et frais financiers</td><td class="num"><?= number_format($c['financieres']['interets'],0,',',' ') ?></td></tr><?php endif; ?>
            <?php if($c['financieres']['escomptes']): ?><tr><td>Escomptes accordés</td><td class="num"><?= number_format($c['financieres']['escomptes'],0,',',' ') ?></td></tr><?php endif; ?>
            <?php if($c['financieres']['pertes_change']): ?><tr><td>Pertes de change</td><td class="num"><?= number_format($c['financieres']['pertes_change'],0,',',' ') ?></td></tr><?php endif; ?>
            <tr class="subtotal-row"><td><strong>Total charges financières</strong></td><td class="num"><strong><?= number_format($c['financieres']['total'],0,',',' ') ?></strong></td></tr>

            <tr class="section-row"><td colspan="2">IMPÔTS SUR RÉSULTAT</td></tr>
            <?php if($c['participation']): ?><tr><td>Participation travailleurs</td><td class="num"><?= number_format($c['participation'],0,',',' ') ?></td></tr><?php endif; ?>
            <?php if($c['is']): ?><tr><td>Impôt sur les sociétés (IS)</td><td class="num"><?= number_format($c['is'],0,',',' ') ?></td></tr><?php endif; ?>

            <tr class="total-row-ch"><td><strong>TOTAL CHARGES</strong></td><td class="num"><strong><?= number_format($c['total'],0,',',' ') ?></strong></td></tr>
            <?php if($r['net'] > 0): ?>
            <tr class="benef-row"><td>Bénéfice de l'exercice</td><td class="num"><?= number_format($r['net'],0,',',' ') ?></td></tr>
            <?php endif; ?>
            </tbody></table>
        </div>

        <!-- PRODUITS -->
        <div>
            <div class="col-header-pr">PRODUITS</div>
            <table><tbody>
            <tr class="section-row"><td colspan="2">PRODUITS D'EXPLOITATION</td></tr>
            <?php
            $pr_expl = [
                ['Ventes de marchandises', $pr['exploitation']['ventes_march']],
                ['Ventes de produits finis', $pr['exploitation']['ventes_produits']],
                ['Travaux et services', $pr['exploitation']['travaux_services']],
                ['Production stockée', $pr['exploitation']['prod_stockee']],
                ['Production immobilisée', $pr['exploitation']['prod_immobilisee']],
                ['Subventions d\'exploitation', $pr['exploitation']['subventions']],
                ['Autres produits', $pr['exploitation']['autres_produits']],
                ['Reprises provisions', $pr['exploitation']['reprises']],
                ['Transferts de charges', $pr['exploitation']['transferts']],
            ];
            foreach($pr_expl as [$lbl, $val]):
                if($val == 0) continue;
            ?>
            <tr><td><?= $lbl ?></td><td class="num"><?= number_format($val,0,',',' ') ?></td></tr>
            <?php endforeach; ?>
            <tr class="subtotal-row"><td><strong>Total produits exploitation</strong></td><td class="num"><strong><?= number_format($pr['exploitation']['total'],0,',',' ') ?></strong></td></tr>

            <tr class="section-row"><td colspan="2">PRODUITS FINANCIERS</td></tr>
            <?php if($pr['financiers']['interets']): ?><tr><td>Intérêts et produits assimilés</td><td class="num"><?= number_format($pr['financiers']['interets'],0,',',' ') ?></td></tr><?php endif; ?>
            <?php if($pr['financiers']['escomptes']): ?><tr><td>Escomptes obtenus</td><td class="num"><?= number_format($pr['financiers']['escomptes'],0,',',' ') ?></td></tr><?php endif; ?>
            <?php if($pr['financiers']['gains_change']): ?><tr><td>Gains de change</td><td class="num"><?= number_format($pr['financiers']['gains_change'],0,',',' ') ?></td></tr><?php endif; ?>
            <tr class="subtotal-row"><td><strong>Total produits financiers</strong></td><td class="num"><strong><?= number_format($pr['financiers']['total'],0,',',' ') ?></strong></td></tr>

            <tr class="section-row"><td colspan="2">PRODUITS HAO</td></tr>
            <tr><td>Produits hors activités ordinaires</td><td class="num"><?= number_format($pr['hao'],0,',',' ') ?></td></tr>

            <tr class="total-row-pr"><td><strong>TOTAL PRODUITS</strong></td><td class="num"><strong><?= number_format($pr['total'],0,',',' ') ?></strong></td></tr>
            <?php if($r['net'] < 0): ?>
            <tr class="perte-row"><td>Perte de l'exercice</td><td class="num"><?= number_format(abs($r['net']),0,',',' ') ?></td></tr>
            <?php endif; ?>
            </tbody></table>
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
