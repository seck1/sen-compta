<?php
$fmt = fn($v) => number_format((float)$v, 0, ',', ' ');
// Pour les barres : on compare les résultats en valeur absolue
$maxAbs = 0;
foreach ($sections as $s) { $maxAbs = max($maxAbs, abs($s['resultat'])); }
$maxAbs = $maxAbs ?: 1;
?>
<style>
.ra-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
.ra-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:22px; }
@media(max-width:760px){ .ra-kpis{ grid-template-columns:1fr; } }
.ra-kpi { background:#fff; border:1px solid var(--border); border-radius:14px; padding:18px 20px; }
.ra-kpi .lbl { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); font-weight:700; }
.ra-kpi .val { font-family:'Cormorant Garamond',serif; font-size:30px; font-weight:700; margin-top:4px; }
.ra-kpi .val.green { color:#1f6e4e; } .ra-kpi .val.gold { color:#a8843f; } .ra-kpi .val.pos { color:#1f6e4e; } .ra-kpi .val.neg { color:#c0392b; }
.ra-card { background:#fff; border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.ra-table { width:100%; border-collapse:collapse; }
.ra-table th { text-align:left; padding:11px 16px; font-size:8.5pt; text-transform:uppercase; letter-spacing:.5px; color:#fff; background:var(--green); }
.ra-table th.r, .ra-table td.r { text-align:right; }
.ra-table td { padding:12px 16px; border-bottom:1px solid var(--border); font-size:14px; }
.ra-code { font-family:monospace; font-weight:700; color:var(--navy-dark); background:rgba(31,110,78,.08); padding:2px 8px; border-radius:6px; font-size:12px; }
.ra-mono { font-family:monospace; }
.ra-pos { color:#1f6e4e; font-weight:700; } .ra-neg { color:#c0392b; font-weight:700; }
.ra-bar-wrap { display:flex; align-items:center; gap:8px; }
.ra-bar { height:8px; border-radius:4px; min-width:2px; }
.ra-bar.pos { background:linear-gradient(90deg,#2a8a63,#1f6e4e); }
.ra-bar.neg { background:linear-gradient(90deg,#e07a6f,#c0392b); }
.ra-total td { font-weight:700; background:#f6f8f7; border-top:2px solid var(--green); font-size:15px; }
.ra-empty { text-align:center; padding:48px 20px; color:var(--text-muted); }
.ra-novent { color:var(--text-muted); font-style:italic; }
</style>

<div class="page-header ra-head">
    <div>
        <h1 class="page-title">Rapport analytique</h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> · Rentabilité par section · Exercice <?= (int)$exercice ?></p>
    </div>
    <?php if (count($exercicesDispos) > 1): ?>
    <select onchange="location.href='<?= APP_URL ?>/dossier/rapport-analytique?id=<?= $entreprise['id'] ?>&exercice='+this.value"
            style="padding:8px 14px;border:1px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;cursor:pointer">
        <?php foreach ($exercicesDispos as $ex): ?>
        <option value="<?= $ex ?>" <?= $ex==$exercice?'selected':'' ?>>Exercice <?= $ex ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
</div>

<?php if (empty($sections)): ?>
<div class="ra-card">
    <div class="ra-empty">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" style="opacity:.4;margin-bottom:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        <div style="font-weight:600">Aucune donnée analytique pour cet exercice</div>
        <div style="font-size:13px;margin-top:4px">Ventilez vos charges (classe 6) et produits (classe 7) sur des sections lors de la saisie d'écriture.</div>
    </div>
</div>
<?php else: ?>

<div class="ra-kpis">
    <div class="ra-kpi"><div class="lbl">Total Produits</div><div class="val green"><?= $fmt($totaux['produits']) ?> <span style="font-size:14px;color:var(--text-muted)">FCFA</span></div></div>
    <div class="ra-kpi"><div class="lbl">Total Charges</div><div class="val gold"><?= $fmt($totaux['charges']) ?> <span style="font-size:14px;color:var(--text-muted)">FCFA</span></div></div>
    <div class="ra-kpi"><div class="lbl">Résultat global</div><div class="val <?= $totaux['resultat']>=0?'pos':'neg' ?>"><?= $fmt($totaux['resultat']) ?> <span style="font-size:14px;color:var(--text-muted)">FCFA</span></div></div>
</div>

<div class="ra-card">
    <table class="ra-table">
        <thead>
            <tr>
                <th style="width:110px">Code</th>
                <th>Section</th>
                <th class="r" style="width:150px">Produits</th>
                <th class="r" style="width:150px">Charges</th>
                <th class="r" style="width:160px">Résultat</th>
                <th style="width:150px">Rentabilité</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($sections as $s): ?>
            <?php $pos = $s['resultat'] >= 0; $w = round(abs($s['resultat']) / $maxAbs * 100); ?>
            <tr>
                <td><?php if ($s['code']): ?><span class="ra-code"><?= e($s['code']) ?></span><?php else: ?>—<?php endif; ?></td>
                <td class="<?= $s['code'] ? '' : 'ra-novent' ?>"><?= e($s['libelle']) ?></td>
                <td class="r ra-mono"><?= $fmt($s['produits']) ?></td>
                <td class="r ra-mono"><?= $fmt($s['charges']) ?></td>
                <td class="r ra-mono <?= $pos?'ra-pos':'ra-neg' ?>"><?= $fmt($s['resultat']) ?></td>
                <td>
                    <div class="ra-bar-wrap">
                        <div class="ra-bar <?= $pos?'pos':'neg' ?>" style="width:<?= $w ?>%"></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="ra-total">
                <td colspan="2">TOTAL</td>
                <td class="r ra-mono"><?= $fmt($totaux['produits']) ?></td>
                <td class="r ra-mono"><?= $fmt($totaux['charges']) ?></td>
                <td class="r ra-mono <?= $totaux['resultat']>=0?'ra-pos':'ra-neg' ?>"><?= $fmt($totaux['resultat']) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<p style="font-size:12.5px;color:var(--text-muted);margin-top:12px">
    Produits = soldes créditeurs des comptes de classe 7. Charges = soldes débiteurs des comptes de classe 6.
    Les lignes sans section apparaissent sous « Non ventilé ».
</p>

<?php endif; ?>
