<?php
$mois_labels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
$taux_produits = $total_budget_produits > 0 ? min(100, round($total_realise_produits / $total_budget_produits * 100)) : 0;
$taux_charges  = $total_budget_charges  > 0 ? min(100, round($total_realise_charges  / $total_budget_charges  * 100)) : 0;
?>
<div class="page-header">
    <div>
        <div class="page-title">Budget vs Réalisé</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Exercice <?= $exercice ?></div>
    </div>
</div>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:24px">
    <div class="card" style="padding:20px">
        <div style="font-size:17px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px">Produits — Réalisé vs Budget</div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:18px;font-weight:700;color:#1f6e4e;font-family:monospace"><?= formatMontant($total_realise_produits) ?></span>
            <span style="font-size:16px;color:var(--text-muted)">/ <?= formatMontant($total_budget_produits) ?></span>
        </div>
        <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?= $taux_produits ?>%;background:#1f6e4e;border-radius:4px;transition:width .5s"></div>
        </div>
        <div style="font-size:18px;color:var(--text-muted);margin-top:6px"><?= $taux_produits ?>% du budget</div>
    </div>
    <div class="card" style="padding:20px">
        <div style="font-size:17px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px">Charges — Réalisé vs Budget</div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:18px;font-weight:700;color:#dc2626;font-family:monospace"><?= formatMontant($total_realise_charges) ?></span>
            <span style="font-size:16px;color:var(--text-muted)">/ <?= formatMontant($total_budget_charges) ?></span>
        </div>
        <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?= $taux_charges ?>%;background:<?= $taux_charges > 100 ? '#dc2626' : '#f59e0b' ?>;border-radius:4px;transition:width .5s"></div>
        </div>
        <div style="font-size:18px;color:var(--text-muted);margin-top:6px"><?= $taux_charges ?>% du budget<?= $taux_charges > 100 ? ' ⚠ Dépassement !' : '' ?></div>
    </div>
</div>

<!-- Graphique mensuel -->
<div class="card" style="padding:20px;margin-bottom:24px">
    <div style="font-weight:700;font-size:18px;margin-bottom:16px">Évolution mensuelle — Réalisé vs Budget</div>
    <canvas id="budgetChart" height="90"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('budgetChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($mois_labels) ?>,
        datasets: [
            {
                label: 'Produits réalisés',
                data: <?= json_encode(array_map(fn($m) => round($realise_mois[$m]['produits']), range(1,12))) ?>,
                backgroundColor: '#1f6e4e',
                borderRadius: 4,
            },
            {
                label: 'Budget produits',
                data: <?= json_encode(array_map(fn($m) => round($budget_mois[$m]['produits']), range(1,12))) ?>,
                backgroundColor: 'rgba(22,163,74,0.2)',
                borderColor: '#1f6e4e',
                borderWidth: 2,
                borderDash: [4,4],
                borderRadius: 4,
                type: 'bar',
            },
            {
                label: 'Charges réalisées',
                data: <?= json_encode(array_map(fn($m) => round($realise_mois[$m]['charges']), range(1,12))) ?>,
                backgroundColor: '#dc2626',
                borderRadius: 4,
            },
            {
                label: 'Budget charges',
                data: <?= json_encode(array_map(fn($m) => round($budget_mois[$m]['charges']), range(1,12))) ?>,
                backgroundColor: 'rgba(220,38,38,0.2)',
                borderColor: '#dc2626',
                borderWidth: 2,
                borderRadius: 4,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => (v/1000000).toFixed(1)+'M', font: { size: 11 } },
                grid: { color: '#f0f0f0' }
            },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>

<!-- Tableau par compte -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-weight:700;font-size:18px">Détail par compte</div>
        <div style="font-size:18px;color:var(--text-muted)">Cliquez sur un montant pour le modifier</div>
    </div>
    <?php if(empty($lignes)): ?>
    <div style="text-align:center;padding:40px;color:var(--text-muted)">Aucun mouvement sur cet exercice. Saisissez d'abord des écritures ou définissez un budget.</div>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:16px">
        <thead>
            <tr style="background:var(--bg-secondary);border-bottom:2px solid var(--border)">
                <th style="padding:10px 16px;text-align:left;font-size:17px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Compte</th>
                <th style="padding:10px 16px;text-align:right;font-size:17px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Réalisé</th>
                <th style="padding:10px 16px;text-align:right;font-size:17px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Budget</th>
                <th style="padding:10px 16px;text-align:right;font-size:17px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Écart</th>
                <th style="padding:10px 16px;text-align:center;font-size:17px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">%</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($lignes as $l):
            $ecart = $l['realise_total'] - $l['budget_total'];
            $pct   = $l['budget_total'] > 0 ? round($l['realise_total']/$l['budget_total']*100) : ($l['realise_total']>0 ? 999 : 0);
            $is_charge = str_starts_with($l['numero'], '6');
            $depasse = $is_charge && $pct > 100;
        ?>
        <tr style="border-bottom:1px solid var(--border);<?= $depasse ? 'background:rgba(239,68,68,.03)' : '' ?>">
            <td style="padding:11px 16px">
                <span style="font-weight:600;font-family:monospace;font-size:18px;color:var(--text-muted)"><?= e($l['numero']) ?></span>
                <span style="margin-left:8px"><?= e($l['intitule']) ?></span>
            </td>
            <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:600;color:<?= $is_charge?'#dc2626':'#1f6e4e' ?>">
                <?= formatMontant($l['realise_total']) ?>
            </td>
            <td style="padding:11px 16px;text-align:right;font-family:monospace;color:var(--text-muted)">
                <span onclick="editerBudget(<?= $l['id'] ?>, '<?= e(addslashes($l['intitule'])) ?>', <?= $l['budget_total'] ?>)"
                      style="cursor:pointer;text-decoration:underline dotted"
                      title="Cliquer pour modifier">
                    <?= $l['budget_total'] > 0 ? formatMontant($l['budget_total']) : '— Définir' ?>
                </span>
            </td>
            <td style="padding:11px 16px;text-align:right;font-family:monospace;color:<?= ($is_charge&&$ecart>0)||(!$is_charge&&$ecart<0) ? '#dc2626' : '#1f6e4e' ?>">
                <?= $ecart >= 0 ? '+' : '' ?><?= formatMontant($ecart) ?>
            </td>
            <td style="padding:11px 16px;text-align:center">
                <?php if($l['budget_total'] > 0): ?>
                <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:18px;font-weight:700;background:<?= $depasse?'#dc262622':'#1f6e4e22' ?>;color:<?= $depasse?'#dc2626':'#1f6e4e' ?>">
                    <?= $pct ?>%
                </span>
                <?php else: ?>
                <span style="color:var(--text-muted);font-size:18px">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Modal budget -->
<div id="modalBudget" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:28px;width:420px;max-width:95vw">
        <div style="font-size:17px;font-weight:700;margin-bottom:6px">Définir le budget</div>
        <div id="budget_compte_nom" style="font-size:16px;color:var(--text-muted);margin-bottom:20px"></div>
        <div style="margin-bottom:14px">
            <label style="font-size:18px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Mois</label>
            <select id="budget_mois" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:17px">
                <?php foreach($mois_labels as $i=>$ml): ?>
                <option value="<?= $i+1 ?>"><?= $ml ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-bottom:20px">
            <label style="font-size:18px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Montant budget (FCFA)</label>
            <input type="number" id="budget_montant" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:17px" placeholder="0">
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button onclick="document.getElementById('modalBudget').style.display='none'" style="padding:9px 20px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer">Annuler</button>
            <button onclick="enregistrerBudget()" style="padding:9px 20px;border-radius:8px;background:#2563eb;color:#fff;border:none;cursor:pointer;font-weight:600">Enregistrer</button>
        </div>
    </div>
</div>

<script>
var _budgetCompteId=0;
function editerBudget(compteId, nom, budgetActuel) {
    _budgetCompteId=compteId;
    document.getElementById('budget_compte_nom').textContent=nom;
    document.getElementById('budget_montant').value=budgetActuel||'';
    document.getElementById('budget_mois').value=new Date().getMonth()+1;
    document.getElementById('modalBudget').style.display='flex';
}
function enregistrerBudget() {
    var fd=new FormData();
    fd.append('entreprise_id','<?= $entreprise['id'] ?>');
    fd.append('compte_id',_budgetCompteId);
    fd.append('mois',document.getElementById('budget_mois').value);
    fd.append('montant',document.getElementById('budget_montant').value);
    fetch('<?= APP_URL ?>/dossier/budget/store',{method:'POST',body:fd})
        .then(()=>{ document.getElementById('modalBudget').style.display='none'; location.reload(); });
}
</script>
