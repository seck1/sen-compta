<?php
// $bilan from EtatsFinanciersController::bilan()
// $exercice, $entreprise available
$a = $bilan['actif'];
$p = $bilan['passif'];
?>
<style>
.ef-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
.ef-title { font-family:'Cormorant Garamond',serif; font-size:28px; font-weight:400; color:var(--navy-dark); }
.ef-subtitle { font-size:16px; color:var(--text-muted); margin-top:4px; }
.equilibre-badge {
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 18px; border-radius:30px; font-size:16px; font-weight:600;
}
.equilibre-ok { background:rgba(34,197,94,0.1); color:#16a34a; border:1px solid rgba(34,197,94,0.25); }
.equilibre-ko { background:rgba(239,68,68,0.1); color:#dc2626; border:1px solid rgba(239,68,68,0.25); }

.bilan-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.bilan-col { background:#fff; border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.bilan-col-header {
    padding:14px 20px; font-size:16px; font-weight:700;
    text-transform:uppercase; letter-spacing:1.5px;
    border-bottom:2px solid var(--border);
}
.bilan-col.actif .bilan-col-header { background:rgba(30,58,95,0.04); color:var(--navy); }
.bilan-col.passif .bilan-col-header { background:rgba(201,169,110,0.08); color:var(--gold-dark); }

.bilan-section-title {
    padding:10px 20px; font-size:14px; font-weight:700;
    text-transform:uppercase; letter-spacing:1px;
    background:var(--bg); color:var(--text-muted);
    border-bottom:1px solid var(--border);
}
.bilan-row {
    display:grid; grid-template-columns:1fr auto auto auto auto;
    padding:9px 20px; border-bottom:1px solid rgba(228,233,240,0.4);
    font-size:16px; align-items:center; gap:12px;
}
.bilan-row:last-child { border-bottom:none; }
.bilan-row.sub { padding-left:32px; color:var(--text-muted); font-size:15px; }
.bilan-row.subtotal { background:rgba(240,243,248,0.6); font-weight:600; }
.bilan-row.total-row { background:var(--navy-dark); color:white; font-weight:700; font-size:17px; }
.col-head { font-size:13px; color:var(--text-muted); text-align:right; font-weight:600; text-transform:uppercase; }
.num { font-family:monospace; text-align:right; }
.num-debit { color:var(--text-muted); }
.num-net { font-weight:600; }
</style>

<div class="ef-header">
    <div>
        <div class="ef-title">Bilan — Exercice <?= e($exercice) ?></div>
        <div class="ef-subtitle"><?= e($entreprise['raison_sociale']) ?> · SYSCOHADA Révisé</div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <?php if ($bilan['equilibre']): ?>
            <span class="equilibre-badge equilibre-ok">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                Bilan équilibré
            </span>
        <?php else: ?>
            <span class="equilibre-badge equilibre-ko">
                Déséquilibre : <?= formatMontant(abs($bilan['ecart'])) ?>
            </span>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/dossier/export/bilan?id=<?= $entreprise['id'] ?>&exercice=<?= $exercice ?>" target="_blank" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimer PDF
        </a>
        <a href="<?= APP_URL ?>/dossier?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Retour</a>
    </div>
</div>

<?php if ($premierExercice ?? false): ?>
<div style="background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.25);border-radius:10px;padding:11px 18px;margin-bottom:16px;font-size:16px;color:#1d4ed8;display:flex;align-items:center;gap:10px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
    Premier exercice — aucune comparaison N-1 disponible.
</div>
<?php endif; ?>

<?php
// Comparaison N vs N-1
$aN1 = $bilanN1['actif'] ?? null;
$pN1 = $bilanN1['passif'] ?? null;
function evolPct($n, $n1): string {
    if (!$n1 || $n1 == 0) return '';
    $pct = (($n - $n1) / abs($n1)) * 100;
    $sign = $pct >= 0 ? '+' : '';
    $color = $pct >= 0 ? '#166534' : '#991b1b';
    return "<span style='font-size:14px;color:$color;margin-left:6px'>$sign" . round($pct,1) . "%</span>";
}
?>
<?php if ($aN1): ?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px">
    <?php
    $kpis = [
        ['Actif total N', $a['total'], $aN1['total'] ?? 0],
        ['Capitaux propres', $p['capitaux_propres']['total'], $pN1['capitaux_propres']['total'] ?? 0],
        ['Trésorerie nette', $a['tresorerie'] - ($p['tresorerie_passive'] ?? 0), ($aN1['tresorerie'] ?? 0) - ($pN1['tresorerie_passive'] ?? 0), true],
        ['Passif total N', $p['total'], $pN1['total'] ?? 0],
    ];
    foreach ($kpis as $kpi):
        [$label, $vN, $vN1] = $kpi;
        $signed = $kpi[3] ?? false;
        $color = $signed && $vN < 0 ? '#dc2626' : 'var(--navy-dark)';
        $prefix = $signed && $vN < 0 ? '−' : '';
    ?>
    <div class="card" style="padding:16px">
        <div style="font-size:14px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px"><?= $label ?></div>
        <div style="font-size:18px;font-weight:700;color:<?= $color ?>;font-family:monospace"><?= $prefix . number_format(abs($vN),0,',',' ') ?></div>
        <div style="font-size:14px;color:var(--text-muted);margin-top:4px">
            N-1 : <?= number_format($vN1,0,',',' ') ?>
            <?= evolPct($vN, $vN1) ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="bilan-grid">
    <!-- ACTIF -->
    <div class="bilan-col actif">
        <div class="bilan-col-header">ACTIF</div>

        <!-- En-tête colonnes -->
        <div class="bilan-row">
            <span></span>
            <span class="col-head">Brut</span>
            <span class="col-head">Amort/Dép.</span>
            <span class="col-head">Net N</span>
            <span class="col-head">Net N-1</span>
        </div>

        <div class="bilan-section-title">Actif immobilisé</div>
        <div class="bilan-row sub">
            <span>Immobilisations incorporelles</span>
            <span class="num num-debit"><?= number_format($a['immobilise']['incorporelles']['brut'],0,',',' ') ?></span>
            <span class="num num-debit"><?= number_format($a['immobilise']['incorporelles']['amort'],0,',',' ') ?></span>
            <span class="num num-net"><?= number_format($a['immobilise']['incorporelles']['net'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['immobilise']['incorporelles']['net']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row sub">
            <span>Immobilisations corporelles</span>
            <span class="num num-debit"><?= number_format($a['immobilise']['corporelles']['brut'],0,',',' ') ?></span>
            <span class="num num-debit"><?= number_format($a['immobilise']['corporelles']['amort'],0,',',' ') ?></span>
            <span class="num num-net"><?= number_format($a['immobilise']['corporelles']['net'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['immobilise']['corporelles']['net']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row sub">
            <span>Immobilisations financières</span>
            <span class="num num-debit"><?= number_format($a['immobilise']['financieres']['brut'],0,',',' ') ?></span>
            <span class="num num-debit"><?= number_format($a['immobilise']['financieres']['dep'],0,',',' ') ?></span>
            <span class="num num-net"><?= number_format($a['immobilise']['financieres']['net'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['immobilise']['financieres']['net']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row sub">
            <span>Immobilisations en cours</span>
            <span class="num num-debit"><?= number_format($a['immobilise']['en_cours'],0,',',' ') ?></span>
            <span class="num">—</span>
            <span class="num num-net"><?= number_format($a['immobilise']['en_cours'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['immobilise']['en_cours']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row subtotal">
            <span>TOTAL ACTIF IMMOBILISÉ</span>
            <span></span><span></span>
            <span class="num"><?= number_format($a['immobilise']['total'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['immobilise']['total']??0,0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-section-title">Actif circulant</div>
        <div class="bilan-row sub">
            <span>Stocks et encours</span>
            <span class="num num-debit"><?= number_format($a['circulant']['stocks']['brut'],0,',',' ') ?></span>
            <span class="num num-debit"><?= number_format($a['circulant']['stocks']['dep'],0,',',' ') ?></span>
            <span class="num num-net"><?= number_format($a['circulant']['stocks']['net'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['circulant']['stocks']['net']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row sub">
            <span>Créances clients</span>
            <span class="num num-debit"><?= number_format($a['circulant']['clients']['brut'],0,',',' ') ?></span>
            <span class="num num-debit"><?= number_format($a['circulant']['clients']['dep'],0,',',' ') ?></span>
            <span class="num num-net"><?= number_format($a['circulant']['clients']['net'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['circulant']['clients']['net']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row sub">
            <span>Autres créances</span>
            <span class="num num-debit"><?= number_format($a['circulant']['autres_creances'],0,',',' ') ?></span>
            <span class="num">—</span>
            <span class="num num-net"><?= number_format($a['circulant']['autres_creances'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['circulant']['autres_creances']??0,0,',',' ') : '—' ?></span>
        </div>
        <div class="bilan-row subtotal">
            <span>TOTAL ACTIF CIRCULANT</span>
            <span></span><span></span>
            <span class="num"><?= number_format($a['circulant']['total'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['circulant']['total']??0,0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-section-title">Trésorerie active</div>
        <div class="bilan-row sub">
            <span>Disponibilités</span>
            <span class="num"><?= number_format($a['tresorerie'],0,',',' ') ?></span>
            <span class="num">—</span>
            <span class="num num-net"><?= number_format($a['tresorerie'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $aN1 ? number_format($aN1['tresorerie']??0,0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-row total-row">
            <span>TOTAL ACTIF</span>
            <span></span><span></span>
            <span class="num"><?= number_format($a['total'],0,',',' ') ?></span>
            <span class="num" style="font-size:14px;opacity:0.75"><?= $aN1 ? number_format($aN1['total']??0,0,',',' ') : '—' ?></span>
        </div>
    </div>

    <!-- PASSIF -->
    <div class="bilan-col passif">
        <div class="bilan-col-header">PASSIF</div>
        <div class="bilan-row"><span></span><span class="col-head">Montant N</span><span></span><span></span><span class="col-head">N-1</span></div>

        <div class="bilan-section-title">Capitaux propres</div>
        <div class="bilan-row sub">
            <span>Capital social</span>
            <span class="num num-net" style="grid-column:4"><?= number_format($p['capitaux_propres']['capital'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Primes et réserves</span>
            <span class="num" style="grid-column:4"><?= number_format($p['capitaux_propres']['primes']+$p['capitaux_propres']['reserves'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Report à nouveau créditeur</span>
            <span class="num" style="grid-column:4"><?= number_format($p['capitaux_propres']['report_crediteur'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <?php if($p['capitaux_propres']['report_debiteur'] > 0): ?>
        <div class="bilan-row sub">
            <span>Report à nouveau débiteur</span>
            <span class="num" style="grid-column:4;color:var(--danger)">( <?= number_format($p['capitaux_propres']['report_debiteur'],0,',',' ') ?> )</span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <?php endif; ?>
        <div class="bilan-row sub">
            <span>Résultat de l'exercice</span>
            <span class="num" style="grid-column:4;<?= $p['capitaux_propres']['resultat_net'] >= 0 ? 'color:#16a34a' : 'color:var(--danger)' ?>;font-weight:600">
                <?= $p['capitaux_propres']['resultat_net'] >= 0 ? '' : '( ' ?>
                <?= number_format(abs($p['capitaux_propres']['resultat_net']),0,',',' ') ?>
                <?= $p['capitaux_propres']['resultat_net'] < 0 ? ' )' : '' ?>
            </span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <?php if($p['capitaux_propres']['subventions_inv'] > 0): ?>
        <div class="bilan-row sub">
            <span>Subventions d'investissement</span>
            <span class="num" style="grid-column:4"><?= number_format($p['capitaux_propres']['subventions_inv'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <?php endif; ?>
        <div class="bilan-row subtotal">
            <span>TOTAL CAPITAUX PROPRES</span>
            <span></span><span></span>
            <span class="num"><?= number_format($p['capitaux_propres']['total'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $pN1 ? number_format($pN1['capitaux_propres']['total']??0,0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-section-title">Ressources stables</div>
        <div class="bilan-row sub">
            <span>Emprunts long terme</span>
            <span class="num" style="grid-column:4"><?= number_format($p['dettes_fin']['emprunts'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Autres dettes financières</span>
            <span class="num" style="grid-column:4"><?= number_format($p['dettes_fin']['autres'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Provisions pour risques</span>
            <span class="num" style="grid-column:4"><?= number_format($p['provisions'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row subtotal">
            <span>TOTAL RESSOURCES DURABLES</span>
            <span></span><span></span>
            <span class="num"><?= number_format($p['ressources_durables'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $pN1 ? number_format(($pN1['dettes_fin']['total']??0)+($pN1['provisions']??0),0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-section-title">Passif circulant</div>
        <div class="bilan-row sub">
            <span>Dettes fournisseurs</span>
            <span class="num" style="grid-column:4"><?= number_format($p['passif_circulant']['fournisseurs'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Dettes fiscales</span>
            <span class="num" style="grid-column:4"><?= number_format($p['passif_circulant']['dettes_fiscales'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Dettes sociales</span>
            <span class="num" style="grid-column:4"><?= number_format($p['passif_circulant']['dettes_sociales'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row sub">
            <span>Autres dettes</span>
            <span class="num" style="grid-column:4"><?= number_format($p['passif_circulant']['autres_dettes'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px">—</span>
        </div>
        <div class="bilan-row subtotal">
            <span>TOTAL PASSIF CIRCULANT</span>
            <span></span><span></span>
            <span class="num"><?= number_format($p['passif_circulant']['total'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $pN1 ? number_format($pN1['passif_circulant']['total']??0,0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-section-title">Trésorerie passive</div>
        <div class="bilan-row sub">
            <span>Concours bancaires</span>
            <span class="num" style="grid-column:4"><?= number_format($p['tresorerie_passive'],0,',',' ') ?></span>
            <span class="num num-debit" style="font-size:14px"><?= $pN1 ? number_format($pN1['tresorerie_passive']??0,0,',',' ') : '—' ?></span>
        </div>

        <div class="bilan-row total-row">
            <span>TOTAL PASSIF</span>
            <span></span><span></span>
            <span class="num"><?= number_format($p['total'],0,',',' ') ?></span>
            <span class="num" style="font-size:14px;opacity:0.75"><?= $pN1 ? number_format($pN1['total']??0,0,',',' ') : '—' ?></span>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .ent-colorbar, .ef-header .btn { display:none !important; }
    .main-wrap { margin-left:0 !important; }
    .bilan-col.total-row { background:#1e3a5f !important; -webkit-print-color-adjust:exact; }
}
</style>
