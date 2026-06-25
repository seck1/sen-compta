<?php
// $cr from EtatsFinanciersController
$c = $cr['charges'];
$p = $cr['produits'];
$r = $cr['resultats'];
?>
<style>
.cr-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
.cr-kpis { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:24px; }
.cr-kpi { background:#fff; border:1px solid var(--border); border-radius:12px; padding:16px; text-align:center; }
.cr-kpi-label { font-size:13px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.cr-kpi-val { font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:600; }
.cr-kpi-val.pos { color:#1f6e4e; }
.cr-kpi-val.neg { color:#dc2626; }
.cr-kpi-val.neutral { color:var(--navy-dark); }

.cr-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.cr-col { background:#fff; border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.cr-col-header { padding:14px 20px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; border-bottom:2px solid var(--border); }
.cr-col.charges .cr-col-header { background:rgba(239,68,68,0.05); color:#dc2626; }
.cr-col.produits .cr-col-header { background:rgba(31,110,78,0.05); color:#1f6e4e; }
.cr-section-title { padding:9px 20px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1px; background:var(--bg); color:var(--text-muted); border-bottom:1px solid var(--border); }
.cr-row { display:flex; justify-content:space-between; align-items:center; padding:9px 20px; border-bottom:1px solid rgba(228,233,240,0.4); font-size:14px; gap:12px; }
.cr-row.sub { padding-left:32px; color:var(--text-muted); font-size:13px; }
.cr-row.subtotal { background:rgba(240,243,248,0.6); font-weight:600; }
.cr-row.total-row { background:var(--navy-dark); color:white; font-weight:700; font-size:14px; }
.cr-num { font-family:monospace; text-align:right; min-width:100px; }
.cr-num-n1 { font-family:monospace; text-align:right; min-width:90px; color:var(--text-muted); font-size:14px; }
</style>

<div class="cr-header">
    <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:400;color:var(--navy-dark)">
            Compte de résultat — Exercice <?= e($exercice) ?>
        </div>
        <div style="font-size:14px;color:var(--text-muted);margin-top:4px"><?= e($entreprise['raison_sociale']) ?> · SYSCOHADA Révisé</div>
    </div>
    <a href="<?= APP_URL ?>/dossier/export/compte-resultat?id=<?= $entreprise['id'] ?>&exercice=<?= $exercice ?>" target="_blank" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
        Imprimer PDF
    </a>
</div>

<!-- KPIs -->
<div class="cr-kpis">
    <?php
    $kpis = [
        ['Résultat exploitation', $r['exploitation']],
        ['Résultat financier',    $r['financier']],
        ['Résultat AO',          $r['ao']],
        ['Résultat HAO',         $r['hao']],
        ['Résultat net',         $r['net']],
    ];
    $rN1 = $crN1['resultats'] ?? null;
    $kpisN1 = $rN1 ? [$rN1['exploitation'],$rN1['financier'],$rN1['ao'],$rN1['hao'],$rN1['net']] : [];
    foreach($kpis as $i => [$lbl, $val]):
        $cls = $val >= 0 ? 'pos' : 'neg';
        $valN1 = $kpisN1[$i] ?? null;
        $evol = '';
        if ($valN1 !== null && $valN1 != 0) {
            $pct = (($val - $valN1) / abs($valN1)) * 100;
            $sign = $pct >= 0 ? '+' : '';
            $color = $pct >= 0 ? '#166534' : '#991b1b';
            $evol = "<div style='font-size:13px;color:$color;margin-top:3px'>$sign" . round($pct,1) . "% vs N-1</div>";
        }
    ?>
    <div class="cr-kpi">
        <div class="cr-kpi-label"><?= $lbl ?></div>
        <div class="cr-kpi-val <?= $cls ?>">
            <?= $val < 0 ? '(' : '' ?>
            <?= number_format(abs($val)/1000000, 2, ',', ' ') ?> M
            <?= $val < 0 ? ')' : '' ?>
        </div>
        <div style="font-size:14px;color:var(--text-muted);margin-top:2px"><?= formatMontant($val) ?></div>
        <?= $evol ?>
    </div>
    <?php endforeach; ?>
</div>

<div class="cr-grid">
    <!-- CHARGES -->
    <div class="cr-col charges">
        <div class="cr-col-header" style="display:flex;justify-content:space-between;align-items:center">
            <span>Charges</span>
            <span style="font-size:13px;font-weight:400;color:#999">N &nbsp;/&nbsp; N-1</span>
        </div>
        <?php $cN1 = $crN1['charges'] ?? null; ?>
        <div class="cr-section-title">Charges d'exploitation</div>
        <?php
        $charges_expl = [
            ['Achats de marchandises',    $c['exploitation']['achats_march'],       $cN1['exploitation']['achats_march'] ?? 0],
            ['Variation stocks march.',   $c['exploitation']['var_stocks_march'],   $cN1['exploitation']['var_stocks_march'] ?? 0],
            ['Achats matières premières', $c['exploitation']['achats_mat_prem'],    $cN1['exploitation']['achats_mat_prem'] ?? 0],
            ['Achats consommables',       $c['exploitation']['achats_consommables'],$cN1['exploitation']['achats_consommables'] ?? 0],
            ['Transports',                $c['exploitation']['transports'],          $cN1['exploitation']['transports'] ?? 0],
            ['Services extérieurs A',     $c['exploitation']['services_ext_a'],     $cN1['exploitation']['services_ext_a'] ?? 0],
            ['Services extérieurs B',     $c['exploitation']['services_ext_b'],     $cN1['exploitation']['services_ext_b'] ?? 0],
            ['Impôts et taxes',           $c['exploitation']['impots_taxes'],        $cN1['exploitation']['impots_taxes'] ?? 0],
            ['Charges de personnel',      $c['exploitation']['charges_personnel'],  $cN1['exploitation']['charges_personnel'] ?? 0],
            ['Autres charges',            $c['exploitation']['autres_charges'],      $cN1['exploitation']['autres_charges'] ?? 0],
            ['Dotations aux amort.',      $c['exploitation']['dot_amort'],           $cN1['exploitation']['dot_amort'] ?? 0],
        ];
        foreach($charges_expl as [$lbl, $val, $valN1]):
            if($val == 0 && $valN1 == 0) continue;
        ?>
        <div class="cr-row sub">
            <span><?= $lbl ?></span>
            <span style="display:flex;gap:16px;align-items:center">
                <span class="cr-num-n1"><?= $valN1 != 0 ? number_format($valN1,0,',',' ') : '—' ?></span>
                <span class="cr-num"><?= number_format($val,0,',',' ') ?></span>
            </span>
        </div>
        <?php endforeach; ?>
        <div class="cr-row subtotal">
            <span>Total charges exploitation</span>
            <span style="display:flex;gap:16px;align-items:center">
                <span class="cr-num-n1"><?= number_format($cN1['exploitation']['total'] ?? 0,0,',',' ') ?></span>
                <span class="cr-num"><?= number_format($c['exploitation']['total'],0,',',' ') ?></span>
            </span>
        </div>

        <div class="cr-section-title">Charges financières</div>
        <div class="cr-row sub"><span>Intérêts et frais financiers</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($cN1['financieres']['interets']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($c['financieres']['interets'],0,',',' ') ?></span></span></div>
        <div class="cr-row sub"><span>Escomptes accordés</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($cN1['financieres']['escomptes']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($c['financieres']['escomptes'],0,',',' ') ?></span></span></div>
        <div class="cr-row sub"><span>Pertes de change</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($cN1['financieres']['pertes_change']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($c['financieres']['pertes_change'],0,',',' ') ?></span></span></div>
        <div class="cr-row subtotal"><span>Total charges financières</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($cN1['financieres']['total']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($c['financieres']['total'],0,',',' ') ?></span></span></div>

        <div class="cr-section-title">Impôts sur résultat</div>
        <div class="cr-row sub"><span>Participation travailleurs</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($cN1['participation']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($c['participation'],0,',',' ') ?></span></span></div>
        <div class="cr-row sub"><span>Impôt sur les sociétés (IS)</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($cN1['is']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($c['is'],0,',',' ') ?></span></span></div>

        <div class="cr-row total-row">
            <span>TOTAL CHARGES</span>
            <span style="display:flex;gap:16px;align-items:center">
                <span style="font-family:monospace;text-align:right;min-width:90px;opacity:.6;font-size:13px"><?= number_format($cN1['total']??0,0,',',' ') ?></span>
                <span class="cr-num"><?= number_format($c['total'],0,',',' ') ?></span>
            </span>
        </div>

        <?php if($r['net'] > 0): ?>
        <div class="cr-row" style="background:rgba(31,110,78,0.08);font-weight:600;color:#1f6e4e">
            <span>Bénéfice de l'exercice</span>
            <span class="cr-num"><?= number_format($r['net'],0,',',' ') ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- PRODUITS -->
    <div class="cr-col produits">
        <div class="cr-col-header" style="display:flex;justify-content:space-between;align-items:center">
            <span>Produits</span>
            <span style="font-size:13px;font-weight:400;color:#999">N &nbsp;/&nbsp; N-1</span>
        </div>
        <?php $pN1 = $crN1['produits'] ?? null; ?>
        <div class="cr-section-title">Produits d'exploitation</div>
        <?php
        $produits_expl = [
            ['Ventes de marchandises',       $p['exploitation']['ventes_march'],     $pN1['exploitation']['ventes_march'] ?? 0],
            ['Ventes de produits finis',     $p['exploitation']['ventes_produits'],  $pN1['exploitation']['ventes_produits'] ?? 0],
            ['Travaux et services',          $p['exploitation']['travaux_services'], $pN1['exploitation']['travaux_services'] ?? 0],
            ['Production stockée',           $p['exploitation']['prod_stockee'],     $pN1['exploitation']['prod_stockee'] ?? 0],
            ['Production immobilisée',       $p['exploitation']['prod_immobilisee'], $pN1['exploitation']['prod_immobilisee'] ?? 0],
            ['Subventions d\'exploitation',  $p['exploitation']['subventions'],      $pN1['exploitation']['subventions'] ?? 0],
            ['Autres produits',              $p['exploitation']['autres_produits'],  $pN1['exploitation']['autres_produits'] ?? 0],
            ['Reprises provisions',          $p['exploitation']['reprises'],         $pN1['exploitation']['reprises'] ?? 0],
            ['Transferts de charges',        $p['exploitation']['transferts'],       $pN1['exploitation']['transferts'] ?? 0],
        ];
        foreach($produits_expl as [$lbl, $val, $valN1]):
            if($val == 0 && $valN1 == 0) continue;
        ?>
        <div class="cr-row sub">
            <span><?= $lbl ?></span>
            <span style="display:flex;gap:16px;align-items:center">
                <span class="cr-num-n1"><?= $valN1 != 0 ? number_format($valN1,0,',',' ') : '—' ?></span>
                <span class="cr-num"><?= number_format($val,0,',',' ') ?></span>
            </span>
        </div>
        <?php endforeach; ?>
        <div class="cr-row subtotal"><span>Total produits exploitation</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($pN1['exploitation']['total']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($p['exploitation']['total'],0,',',' ') ?></span></span></div>

        <div class="cr-section-title">Produits financiers</div>
        <div class="cr-row sub"><span>Intérêts et produits assimilés</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($pN1['financiers']['interets']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($p['financiers']['interets'],0,',',' ') ?></span></span></div>
        <div class="cr-row sub"><span>Escomptes obtenus</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($pN1['financiers']['escomptes']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($p['financiers']['escomptes'],0,',',' ') ?></span></span></div>
        <div class="cr-row sub"><span>Gains de change</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($pN1['financiers']['gains_change']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($p['financiers']['gains_change'],0,',',' ') ?></span></span></div>
        <div class="cr-row subtotal"><span>Total produits financiers</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($pN1['financiers']['total']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($p['financiers']['total'],0,',',' ') ?></span></span></div>

        <div class="cr-section-title">Produits HAO</div>
        <div class="cr-row sub"><span>Produits hors activités ordinaires</span><span style="display:flex;gap:16px"><span class="cr-num-n1"><?= number_format($pN1['hao']??0,0,',',' ') ?></span><span class="cr-num"><?= number_format($p['hao'],0,',',' ') ?></span></span></div>

        <div class="cr-row total-row">
            <span>TOTAL PRODUITS</span>
            <span style="display:flex;gap:16px;align-items:center">
                <span style="font-family:monospace;text-align:right;min-width:90px;opacity:.6;font-size:13px"><?= number_format($pN1['total']??0,0,',',' ') ?></span>
                <span class="cr-num"><?= number_format($p['total'],0,',',' ') ?></span>
            </span>
        </div>

        <?php if($r['net'] < 0): ?>
        <div class="cr-row" style="background:rgba(239,68,68,0.08);font-weight:600;color:#dc2626">
            <span>Perte de l'exercice</span>
            <span class="cr-num"><?= number_format(abs($r['net']),0,',',' ') ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .ent-colorbar, .cr-header .btn { display:none !important; }
    .main-wrap { margin-left:0 !important; }
}
</style>
