<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><?= e($entreprise['raison_sociale']) ?></h1>
        <p class="page-subtitle"><?= e($entreprise['regime_fiscal']) ?> · <?= e($entreprise['secteur_activite'] ?? '—') ?></p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <!-- Switcher exercice -->
        <div style="position:relative">
            <select onchange="window.location='<?= APP_URL ?>/dossier?id=<?= $entreprise['id'] ?>&exercice='+this.value"
                    style="padding:9px 36px 9px 14px;border:1px solid var(--border);border-radius:10px;font-size:16px;font-family:'DM Sans',sans-serif;background:white;color:var(--text);cursor:pointer;appearance:none;font-weight:500">
                <?php foreach ($exercicesDispos as $ex): ?>
                <option value="<?= $ex ?>" <?= $ex==$exercice?'selected':'' ?>>Exercice <?= $ex ?></option>
                <?php endforeach; ?>
                <?php for ($y = max($exercicesDispos)-1; $y >= max($exercicesDispos)-3; $y--): ?>
                <?php if (!in_array($y, $exercicesDispos)): ?>
                <option value="<?= $y ?>">Exercice <?= $y ?></option>
                <?php endif; ?>
                <?php endfor; ?>
            </select>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;position:absolute;right:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
        </div>
        <?php if ($nbBrouillons > 0): ?>
        <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>&statut=brouillon" style="display:flex;align-items:center;gap:7px;padding:9px 14px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:10px;font-size:16px;font-weight:600;color:#92400e;text-decoration:none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            <?= $nbBrouillons ?> brouillon<?= $nbBrouillons>1?'s':'' ?> à valider
        </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/dossier/nouvelle-ecriture?id=<?= $entreprise['id'] ?>" class="btn btn-ent">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouvelle écriture
        </a>
    </div>
</div>

<?php if ($alerteBulletins || $alerteTVA): ?>
<div style="margin-bottom:18px;display:flex;flex-direction:column;gap:8px">
<?php if ($alerteBulletins): ?>
<div style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;font-size:16px;color:#92400e">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:#f59e0b;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    <strong>Alerte paie :</strong> <?= $bulletinsMois ?>/<?= $nbEmployes ?> bulletins générés pour <?= date('F Y') ?>.
    <a href="<?= APP_URL ?>/dossier/rh/bulletins?id=<?= $entreprise['id'] ?>" style="color:#d97706;font-weight:600;text-decoration:underline">Générer les bulletins</a>
</div>
<?php endif; ?>
<?php if ($alerteTVA): ?>
<div style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-size:16px;color:#7f1d1d">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:#ef4444;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    <strong>Alerte TVA :</strong> Aucune déclaration TVA pour <?= date('F Y') ?>.
    <a href="<?= APP_URL ?>/dossier/tva?id=<?= $entreprise['id'] ?>" style="color:#dc2626;font-weight:600;text-decoration:underline">Déclarer la TVA</a>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<!-- KPI Row 1: Financial -->
<div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px">
    <div class="kpi-card">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg></div>
        <div class="kpi-label">Solde trésorerie</div>
        <div class="kpi-value" style="font-size:22px;color:<?= $soldeTresorerie >= 0 ? 'var(--success)' : 'var(--danger)' ?>"><?= number_format($soldeTresorerie, 0, ',', ' ') ?></div>
        <div class="kpi-sub">FCFA · Comptes 50x-58x</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg></div>
        <div class="kpi-label">CA exercice</div>
        <div class="kpi-value" style="font-size:22px"><?= number_format($caMois, 0, ',', ' ') ?></div>
        <div class="kpi-sub">FCFA · Exercice <?= $exercice ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg></div>
        <div class="kpi-label">Résultat exercice</div>
        <div class="kpi-value" style="font-size:22px;color:<?= $resultatExercice >= 0 ? 'var(--success)' : 'var(--danger)' ?>"><?= number_format($resultatExercice, 0, ',', ' ') ?></div>
        <div class="kpi-sub">FCFA · Exercice <?= $exercice ?></div>
    </div>
</div>

<!-- KPI Row 2 -->
<div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px">
    <div class="kpi-card">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg></div>
        <div class="kpi-label">Créances clients</div>
        <div class="kpi-value" style="font-size:20px;color:var(--info)"><?= number_format($creancesClients, 0, ',', ' ') ?></div>
        <div class="kpi-sub">FCFA · Solde comptes 41x</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" /></svg></div>
        <div class="kpi-label">Dettes fournisseurs</div>
        <div class="kpi-value" style="font-size:20px;color:var(--warning)"><?= number_format($dettesFournisseurs, 0, ',', ' ') ?></div>
        <div class="kpi-sub">FCFA · Comptes 40x</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></div>
        <div class="kpi-label">Bulletins du mois</div>
        <div class="kpi-value"><?= $bulletinsMois ?></div>
        <div class="kpi-sub">sur <?= $nbEmployes ?> employé(s) · <?= date('F Y') ?></div>
    </div>
</div>

<!-- Graphiques -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px">
    <!-- CA vs Charges 6 mois -->
    <div class="card" style="padding:22px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div>
                <div style="font-size:17px;font-weight:600;color:var(--navy-dark)">Produits vs Charges — 6 mois</div>
                <div style="font-size:14px;color:var(--text-muted);margin-top:2px">Exercice <?= $exercice ?></div>
            </div>
            <div style="display:flex;gap:14px;font-size:14px;color:var(--text-muted)">
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:var(--ent-color);display:inline-block"></span>Produits</span>
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#ef4444;display:inline-block"></span>Charges</span>
            </div>
        </div>
        <canvas id="chartDossierCA" height="110"></canvas>
    </div>
    <!-- Top charges -->
    <div class="card" style="padding:22px">
        <div style="font-size:17px;font-weight:600;color:var(--navy-dark);margin-bottom:4px">Top charges</div>
        <div style="font-size:14px;color:var(--text-muted);margin-bottom:16px">Exercice <?= $exercice ?></div>
        <?php if (empty($topCharges)): ?>
        <div style="text-align:center;padding:30px;font-size:16px;color:var(--text-muted)">Aucune charge enregistrée</div>
        <?php else: ?>
        <?php $maxCharge = max(array_column($topCharges, 'total')); ?>
        <?php foreach ($topCharges as $tc): ?>
        <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;font-size:15px;margin-bottom:4px">
                <span style="color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px"><?= e($tc['numero']) ?> <?= e($tc['intitule']) ?></span>
                <span style="font-family:monospace;font-size:14px;color:var(--text-muted);flex-shrink:0;margin-left:8px"><?= number_format($tc['total'],0,',',' ') ?></span>
            </div>
            <div style="height:5px;background:var(--border);border-radius:3px">
                <div style="height:100%;width:<?= $maxCharge>0?round($tc['total']/$maxCharge*100):0 ?>%;background:var(--ent-color);border-radius:3px;opacity:0.7"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartDossierCA'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            { label:'Produits', data: <?= json_encode($chartCA) ?>, backgroundColor: <?= json_encode($entreprise['couleur'] ?? '#1e3a5f') ?>, borderRadius:5, opacity:0.8 },
            { label:'Charges',  data: <?= json_encode($chartCharges) ?>, backgroundColor:'rgba(239,68,68,0.65)', borderRadius:5 }
        ]
    },
    options: {
        responsive:true,
        plugins:{ legend:{ display:false } },
        scales:{
            y:{ ticks:{ callback: v => v>=1e6?(v/1e6).toFixed(1)+'M':v>=1e3?(v/1e3).toFixed(0)+'k':v, font:{size:10} }, grid:{color:'rgba(0,0,0,0.04)'} },
            x:{ grid:{display:false}, ticks:{font:{size:10}} }
        }
    }
});
</script>

<!-- Bottom section: écritures + échéances + journaux -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px">

    <!-- Dernières écritures -->
    <div class="table-wrap">
        <div class="table-header">
            <div class="table-title">5 dernières écritures</div>
            <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Voir tout</a>
        </div>
        <?php if (empty($dernieres)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            <h3>Aucune écriture</h3>
            <p>Commencez la saisie comptable</p>
        </div>
        <?php else: ?>
        <table>
            <thead><tr><th>Date</th><th>Journal</th><th>Libellé</th><th>Montant</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($dernieres as $ec): ?>
            <tr>
                <td style="font-size:16px"><?= date('d/m/Y', strtotime($ec['date_ecriture'])) ?></td>
                <td><span class="badge badge-navy"><?= e($ec['journal_code']) ?></span></td>
                <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($ec['libelle']) ?></td>
                <td class="montant-debit"><?= number_format($ec['total_debit'], 0, ',', ' ') ?> F</td>
                <td><span class="badge <?= $ec['statut']==='validee' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($ec['statut']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Colonne droite: Échéances + Journaux -->
    <div style="display:flex;flex-direction:column;gap:16px">

        <!-- Prochaines échéances -->
        <div class="card" style="padding:0;overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-size:16px;font-weight:600;color:var(--navy-dark)">
                Prochaines échéances fiscales
            </div>
            <?php if (empty($prochainesEcheances)): ?>
            <div style="padding:20px;text-align:center;font-size:16px;color:var(--text-muted)">Aucune échéance à venir</div>
            <?php else: ?>
            <?php foreach ($prochainesEcheances as $ech): ?>
            <div style="padding:12px 18px;border-bottom:1px solid rgba(228,233,240,0.5);display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:16px;font-weight:500;color:var(--text)"><?= e($ech['libelle'] ?? $ech['type'] ?? 'Échéance') ?></div>
                    <div style="font-size:14px;color:var(--text-muted)"><?= date('d/m/Y', strtotime($ech['date_echeance'])) ?></div>
                </div>
                <?php $diff = (new DateTime($ech['date_echeance']))->diff(new DateTime())->days; ?>
                <span class="badge <?= $diff <= 7 ? 'badge-danger' : ($diff <= 15 ? 'badge-warning' : 'badge-info') ?>">J-<?= $diff ?></span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Journaux -->
        <div class="card" style="padding:0;overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-size:16px;font-weight:600;color:var(--navy-dark)">Journaux</div>
            <?php foreach ($journaux as $j): ?>
            <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>&journal=<?= e($j['code']) ?>"
               style="display:flex;align-items:center;justify-content:space-between;padding:10px 18px;border-bottom:1px solid rgba(228,233,240,0.4);text-decoration:none;transition:background 0.15s"
               onmouseenter="this.style.background='var(--bg)'" onmouseleave="this.style.background=''">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:28px;height:28px;border-radius:7px;background:var(--ent-color);opacity:0.85;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:white"><?= e($j['code']) ?></div>
                    <div style="font-size:15px;color:var(--text)"><?= e($j['libelle']) ?></div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px;color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
