<?php
// Compute totals across all accounts
$totals = ['total'=>0,'courant'=>0,'j30_60'=>0,'j60_90'=>0,'j90_180'=>0,'plus180'=>0];
foreach ($comptes as $c) {
    foreach ($totals as $k => $_) {
        $totals[$k] += $c[$k];
    }
}
$enRetard  = $totals['j30_60'] + $totals['j60_90'];
$critique  = $totals['j90_180'] + $totals['plus180'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Balance Âgée</h1>
        <p class="page-subtitle">Analyse des créances et dettes non lettrées au <?= date('d/m/Y', strtotime($dateRef)) ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimer
        </button>
    </div>
</div>

<!-- Tabs + filters -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
    <div style="display:flex;gap:4px;background:white;border:1px solid var(--border);border-radius:11px;padding:4px">
        <a href="?id=<?= $entreprise['id'] ?>&type=clients&date_ref=<?= urlencode($dateRef) ?>"
           style="padding:7px 18px;border-radius:8px;font-size:16px;font-weight:500;text-decoration:none;transition:all .2s;<?= $type==='clients' ? 'background:var(--navy);color:white' : 'color:var(--text-muted)' ?>">
            Clients
        </a>
        <a href="?id=<?= $entreprise['id'] ?>&type=fournisseurs&date_ref=<?= urlencode($dateRef) ?>"
           style="padding:7px 18px;border-radius:8px;font-size:16px;font-weight:500;text-decoration:none;transition:all .2s;<?= $type==='fournisseurs' ? 'background:var(--navy);color:white' : 'color:var(--text-muted)' ?>">
            Fournisseurs
        </a>
    </div>
    <form method="get" style="display:flex;align-items:center;gap:8px">
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <label style="font-size:16px;color:var(--text-muted)">Date de référence :</label>
        <input type="date" name="date_ref" value="<?= e($dateRef) ?>" style="padding:7px 12px;border:1px solid var(--border);border-radius:9px;font-size:16px;background:white">
        <button type="submit" class="btn btn-outline btn-sm">Actualiser</button>
    </form>
</div>

<!-- KPI row -->
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="kpi-card">
        <div class="kpi-label">Total <?= $type === 'clients' ? 'créances' : 'dettes' ?></div>
        <div class="kpi-value" style="font-size:22px"><?= formatMontant($totals['total']) ?></div>
        <div class="kpi-sub"><?= count($comptes) ?> compte(s)</div>
    </div>
    <div class="kpi-card" style="border-top-color:#1f6e4e">
        <div class="kpi-label" style="color:#1f6e4e">Courantes (0–30j)</div>
        <div class="kpi-value" style="font-size:22px;color:#1f6e4e"><?= formatMontant($totals['courant']) ?></div>
        <div class="kpi-sub"><?= $totals['total'] > 0 ? round($totals['courant']/$totals['total']*100) : 0 ?>% du total</div>
    </div>
    <div class="kpi-card" style="border-top-color:#f59e0b">
        <div class="kpi-label" style="color:#d97706">En retard (31–90j)</div>
        <div class="kpi-value" style="font-size:22px;color:#d97706"><?= formatMontant($enRetard) ?></div>
        <div class="kpi-sub"><?= $totals['total'] > 0 ? round($enRetard/$totals['total']*100) : 0 ?>% du total</div>
    </div>
    <div class="kpi-card" style="border-top-color:#ef4444">
        <div class="kpi-label" style="color:#dc2626">Critique (&gt;90j)</div>
        <div class="kpi-value" style="font-size:22px;color:#dc2626"><?= formatMontant($critique) ?></div>
        <div class="kpi-sub"><?= $totals['total'] > 0 ? round($critique/$totals['total']*100) : 0 ?>% du total</div>
    </div>
</div>

<?php if (empty($comptes)): ?>
<div class="card" style="text-align:center;padding:50px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:44px;height:44px;color:var(--border);margin:0 auto 14px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75A.75.75 0 013.75 6h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 6.75zM3 12a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 12zm0 5.25a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75z" /></svg>
    <div style="font-size:18px;font-weight:500;color:var(--text-muted)">Aucune <?= $type === 'clients' ? 'créance client' : 'dette fournisseur' ?> non lettrée</div>
    <div style="font-size:16px;color:var(--text-muted);opacity:0.7;margin-top:6px">Toutes les lignes sont lettrées ou le solde est nul.</div>
</div>
<?php else: ?>

<!-- Legend -->
<div style="display:flex;align-items:center;gap:16px;margin-bottom:14px;font-size:15px;flex-wrap:wrap">
    <span style="font-weight:500;color:var(--text-muted)">Légende :</span>
    <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:rgba(31,110,78,0.25);border-radius:3px;display:inline-block"></span>0–30j (courant)</span>
    <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:rgba(245,158,11,0.25);border-radius:3px;display:inline-block"></span>31–60j</span>
    <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:rgba(249,115,22,0.25);border-radius:3px;display:inline-block"></span>61–90j</span>
    <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:rgba(239,68,68,0.25);border-radius:3px;display:inline-block"></span>&gt;90j (critique)</span>
</div>

<div class="table-wrap">
    <div class="table-header">
        <span class="table-title">Balance âgée — <?= $type === 'clients' ? 'Comptes 41x' : 'Comptes 40x' ?></span>
        <span style="font-size:15px;color:var(--text-muted)"><?= count($comptes) ?> compte(s) avec solde</span>
    </div>
    <div style="overflow-x:auto">
    <table style="min-width:900px">
        <thead>
            <tr>
                <th>Compte</th>
                <th>Intitulé</th>
                <th style="text-align:right">Total</th>
                <th style="text-align:right;background:rgba(31,110,78,0.08)">0–30j</th>
                <th style="text-align:right;background:rgba(245,158,11,0.08)">31–60j</th>
                <th style="text-align:right;background:rgba(249,115,22,0.08)">61–90j</th>
                <th style="text-align:right;background:rgba(239,68,68,0.1)">91–180j</th>
                <th style="text-align:right;background:rgba(239,68,68,0.15)">&gt;180j</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comptes as $c): ?>
            <tr>
                <td><code style="font-size:15px;background:var(--bg);padding:2px 6px;border-radius:5px"><?= e($c['numero']) ?></code></td>
                <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($c['intitule']) ?></td>
                <td style="text-align:right;font-family:monospace;font-weight:600"><?= formatMontant($c['total']) ?></td>
                <td style="text-align:right;font-family:monospace;background:rgba(31,110,78,0.05);color:<?= $c['courant'] > 0 ? '#1f6e4e' : 'var(--text-muted)' ?>">
                    <?= $c['courant'] > 0.01 ? formatMontant($c['courant']) : '—' ?>
                </td>
                <td style="text-align:right;font-family:monospace;background:rgba(245,158,11,0.05);color:<?= $c['j30_60'] > 0 ? '#d97706' : 'var(--text-muted)' ?>">
                    <?= $c['j30_60'] > 0.01 ? formatMontant($c['j30_60']) : '—' ?>
                </td>
                <td style="text-align:right;font-family:monospace;background:rgba(249,115,22,0.05);color:<?= $c['j60_90'] > 0 ? '#ea580c' : 'var(--text-muted)' ?>">
                    <?= $c['j60_90'] > 0.01 ? formatMontant($c['j60_90']) : '—' ?>
                </td>
                <td style="text-align:right;font-family:monospace;background:rgba(239,68,68,0.07);color:<?= $c['j90_180'] > 0 ? '#dc2626' : 'var(--text-muted)' ?>">
                    <?= $c['j90_180'] > 0.01 ? formatMontant($c['j90_180']) : '—' ?>
                </td>
                <td style="text-align:right;font-family:monospace;background:rgba(239,68,68,0.12);color:<?= $c['plus180'] > 0 ? '#991b1b' : 'var(--text-muted)' ?>;font-weight:<?= $c['plus180'] > 0 ? '600' : '400' ?>">
                    <?= $c['plus180'] > 0.01 ? formatMontant($c['plus180']) : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:rgba(30,58,95,0.06);font-weight:700;font-size:16px">
                <td colspan="2">TOTAL</td>
                <td style="text-align:right;font-family:monospace"><?= formatMontant($totals['total']) ?></td>
                <td style="text-align:right;font-family:monospace;background:rgba(31,110,78,0.1);color:#1f6e4e"><?= formatMontant($totals['courant']) ?></td>
                <td style="text-align:right;font-family:monospace;background:rgba(245,158,11,0.1);color:#d97706"><?= formatMontant($totals['j30_60']) ?></td>
                <td style="text-align:right;font-family:monospace;background:rgba(249,115,22,0.1);color:#ea580c"><?= formatMontant($totals['j60_90']) ?></td>
                <td style="text-align:right;font-family:monospace;background:rgba(239,68,68,0.12);color:#dc2626"><?= formatMontant($totals['j90_180']) ?></td>
                <td style="text-align:right;font-family:monospace;background:rgba(239,68,68,0.2);color:#991b1b"><?= formatMontant($totals['plus180']) ?></td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

<!-- Visual bar chart of aging -->
<?php if ($totals['total'] > 0): ?>
<div class="card" style="margin-top:16px">
    <div style="font-size:16px;font-weight:600;color:var(--navy-dark);margin-bottom:14px">Répartition par ancienneté</div>
    <?php
    $bars = [
        ['label'=>'0–30j','val'=>$totals['courant'],'color'=>'#1f6e4e'],
        ['label'=>'31–60j','val'=>$totals['j30_60'],'color'=>'#f59e0b'],
        ['label'=>'61–90j','val'=>$totals['j60_90'],'color'=>'#f97316'],
        ['label'=>'91–180j','val'=>$totals['j90_180'],'color'=>'#ef4444'],
        ['label'=>'>180j','val'=>$totals['plus180'],'color'=>'#991b1b'],
    ];
    ?>
    <div style="display:flex;height:16px;border-radius:8px;overflow:hidden;gap:2px;margin-bottom:10px">
        <?php foreach ($bars as $bar):
            $pct = $totals['total'] > 0 ? ($bar['val'] / $totals['total'] * 100) : 0;
            if ($pct < 0.5) continue;
        ?>
        <div style="width:<?= $pct ?>%;background:<?= $bar['color'] ?>;transition:width .3s" title="<?= $bar['label'] ?> : <?= formatMontant($bar['val']) ?>"></div>
        <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:16px;flex-wrap:wrap">
        <?php foreach ($bars as $bar):
            $pct = $totals['total'] > 0 ? round($bar['val'] / $totals['total'] * 100, 1) : 0;
        ?>
        <div style="display:flex;align-items:center;gap:6px;font-size:15px">
            <span style="width:10px;height:10px;border-radius:50%;background:<?= $bar['color'] ?>;display:inline-block"></span>
            <span style="color:var(--text-muted)"><?= $bar['label'] ?></span>
            <span style="font-weight:600"><?= $pct ?>%</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
