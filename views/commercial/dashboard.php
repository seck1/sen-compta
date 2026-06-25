<?php
// Helpers
function statutFactureBadge(string $s): string {
    return match($s) {
        'brouillon'          => '<span class="badge badge-gray">Brouillon</span>',
        'envoyee'            => '<span class="badge badge-blue">Envoyée</span>',
        'partiellement_payee'=> '<span class="badge badge-orange">Partiel</span>',
        'payee'              => '<span class="badge badge-green">Payée</span>',
        'en_retard'          => '<span class="badge badge-red">En retard</span>',
        'annulee'            => '<span class="badge badge-gray">Annulée</span>',
        default              => '<span class="badge badge-gray">' . e($s) . '</span>',
    };
}
$moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap');

.com-root { padding: 32px 36px; max-width: 1400px; }

/* Header */
.com-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 32px;
    flex-wrap: wrap; gap: 16px;
}
.com-header-left h1 {
    font-family: 'Playfair Display', serif;
    font-size: 28px; font-weight: 700;
    color: var(--navy-dark);
    letter-spacing: -0.5px;
}
.com-header-left p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }
.com-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
@media (max-width: 640px) {
    .com-header-actions { width: 100%; }
    .com-header-actions .btn { flex: 1; justify-content: center; }
}

/* KPI Cards */
.kpi-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 20px; margin-bottom: 28px;
}
.kpi-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border);
    position: relative; overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(30,58,95,0.08); }
.kpi-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: var(--kpi-color, var(--navy));
}
.kpi-card.gold::before { background: var(--gold); }
.kpi-card.green::before { background: var(--green); }
.kpi-card.orange::before { background: var(--gold); }
.kpi-card.red::before { background: #c0392b; }
.kpi-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px; font-size: 20px;
}
.kpi-icon.gold { background: rgba(184,146,63,0.12); }
.kpi-icon.green { background: rgba(31,110,78,0.1); }
.kpi-icon.orange { background: rgba(184,146,63,0.12); }
.kpi-icon.blue { background: rgba(31,110,78,0.1); }
.kpi-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 600; margin-bottom: 8px; }
.kpi-value { font-size: 26px; font-weight: 700; color: var(--navy-dark); font-family: 'Playfair Display', serif; line-height: 1; }
.kpi-value.green { color: var(--green); }
.kpi-value.orange { color: var(--gold-dark); }
.kpi-value.red { color: #c0392b; }
.kpi-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

/* Main grid */
.com-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; margin-bottom: 24px; }

/* Chart card */
.chart-card {
    background: #fff; border-radius: 16px;
    border: 1px solid var(--border); padding: 24px;
}
.card-title {
    font-size: 15px; font-weight: 600; color: var(--navy-dark);
    margin-bottom: 4px;
}
.card-sub { font-size: 12px; color: var(--text-muted); margin-bottom: 20px; }

/* Pipeline */
.pipeline-card {
    background: #fff; border-radius: 16px;
    border: 1px solid var(--border); padding: 24px;
}
.pipeline-stage {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}
.pipeline-stage:last-child { border-bottom: none; }
.pipeline-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.pipeline-info { flex: 1; }
.pipeline-name { font-size: 13px; font-weight: 500; color: var(--text); }
.pipeline-meta { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.pipeline-count {
    font-size: 13px; font-weight: 700; color: var(--navy-dark);
    background: var(--bg); padding: 3px 10px; border-radius: 20px;
}
.pipeline-bar-wrap { height: 4px; background: var(--bg); border-radius: 2px; margin-top: 6px; }
.pipeline-bar { height: 100%; border-radius: 2px; transition: width 0.6s ease; }

/* Bottom grid */
.bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

/* Table */
.com-table-card {
    background: #fff; border-radius: 16px;
    border: 1px solid var(--border); overflow: hidden;
}
.com-table-head {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
table.com-table { width: 100%; border-collapse: collapse; }
table.com-table th {
    font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px;
    color: var(--text-muted); font-weight: 600;
    padding: 10px 16px; text-align: left;
    background: #fafbfc; border-bottom: 1px solid var(--border);
}
table.com-table td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
table.com-table tr:last-child td { border-bottom: none; }
table.com-table tr:hover td { background: #fafbfc; }

/* Badges */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-green  { background: var(--green-tint); color: var(--green-dark); }
.badge-blue   { background: rgba(30,58,95,0.1); color: var(--navy); }
.badge-orange { background: rgba(184,146,63,0.14); color: var(--gold-dark); }
.badge-red    { background: rgba(192,57,43,0.1); color: #c0392b; }
.badge-gray   { background: #eef1f0; color: #64748b; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
.btn-primary { background: linear-gradient(135deg, var(--green-light), var(--green)); color: #fff; }
.btn-primary:hover { filter: brightness(1.06); }
.btn-gold { background: var(--gold); color: #fff; }
.btn-gold:hover { background: var(--gold-dark); color: #fff; }
.btn-outline { background: transparent; color: var(--navy); border: 1.5px solid var(--border); }
.btn-outline:hover { border-color: var(--green); color: var(--green); background: #f5faf7; }
.btn-sm { padding: 6px 12px; font-size: 12px; }

/* Montant retard */
.retard-amount { font-weight: 700; color: #dc2626; }

@media (max-width: 1100px) {
    .kpi-grid { grid-template-columns: repeat(2,1fr); }
    .com-grid { grid-template-columns: 1fr; }
    .bottom-grid { grid-template-columns: 1fr; }
}
</style>

<div class="com-root">
    <!-- Header -->
    <div class="com-header">
        <div class="com-header-left">
            <h1>Gestion Commerciale</h1>
            <p>SenCompta · Exercice <?= $annee ?></p>
        </div>
        <div class="com-header-actions">
            <a href="<?= APP_URL ?>/commercial/prospects/nouveau" class="btn btn-outline">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Nouveau prospect
            </a>
            <a href="<?= APP_URL ?>/commercial/factures/nouvelle" class="btn btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Nouvelle facture
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card gold">
            <div class="kpi-icon gold">💰</div>
            <div class="kpi-label">CA Facturé <?= $annee ?></div>
            <div class="kpi-value"><?= number_format($caFacture/1000000, 2, ',', ' ') ?> M</div>
            <div class="kpi-sub"><?= number_format($caFacture, 0, ',', ' ') ?> FCFA</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-icon green">✅</div>
            <div class="kpi-label">CA Encaissé</div>
            <div class="kpi-value green"><?= number_format($caEncaisse/1000000, 2, ',', ' ') ?> M</div>
            <div class="kpi-sub"><?= number_format($caEncaisse, 0, ',', ' ') ?> FCFA</div>
        </div>
        <div class="kpi-card orange">
            <div class="kpi-icon orange">⏳</div>
            <div class="kpi-label">En attente</div>
            <div class="kpi-value orange"><?= number_format($caAttente/1000000, 2, ',', ' ') ?> M</div>
            <div class="kpi-sub"><?= number_format($caAttente, 0, ',', ' ') ?> FCFA</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon blue">📊</div>
            <div class="kpi-label">Taux conversion</div>
            <div class="kpi-value"><?= $tauxConversion ?>%</div>
            <div class="kpi-sub"><?= $nbClients ?> clients · <?= $nbProspects ?> prospects</div>
        </div>
    </div>

    <!-- Chart + Pipeline -->
    <div class="com-grid">
        <div class="chart-card">
            <div class="card-title">Évolution du CA mensuel</div>
            <div class="card-sub">Factures émises — <?= $annee ?></div>
            <canvas id="chartCA" height="100"></canvas>
        </div>
        <div class="pipeline-card">
            <div class="card-title">Pipeline commercial</div>
            <div class="card-sub" style="margin-bottom:16px">Répartition des prospects</div>
            <?php
            $stages = [
                'nouveau'      => ['label'=>'Nouveau', 'color'=>'#94a3b8'],
                'qualifie'     => ['label'=>'Qualifié', 'color'=>'#1f6e4e'],
                'devis_envoye' => ['label'=>'Devis envoyé', 'color'=>'#f59e0b'],
                'negociation'  => ['label'=>'Négociation', 'color'=>'#8b5cf6'],
                'client'       => ['label'=>'Client', 'color'=>'#1f6e4e'],
                'perdu'        => ['label'=>'Perdu', 'color'=>'#ef4444'],
            ];
            $totalProspects = array_sum(array_column($pipelineMap, 'nb')) ?: 1;
            foreach ($stages as $key => $s):
                $nb = $pipelineMap[$key]['nb'] ?? 0;
                $ca = $pipelineMap[$key]['total'] ?? 0;
                $pct = round($nb / $totalProspects * 100);
            ?>
            <div class="pipeline-stage">
                <div class="pipeline-dot" style="background:<?= $s['color'] ?>"></div>
                <div class="pipeline-info">
                    <div class="pipeline-name"><?= $s['label'] ?></div>
                    <?php if ($ca > 0): ?>
                    <div class="pipeline-meta"><?= number_format($ca, 0, ',', ' ') ?> FCFA potentiel</div>
                    <?php endif; ?>
                    <div class="pipeline-bar-wrap">
                        <div class="pipeline-bar" style="width:<?= $pct ?>%;background:<?= $s['color'] ?>"></div>
                    </div>
                </div>
                <div class="pipeline-count"><?= $nb ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom: Retards + Dernières factures -->
    <div class="bottom-grid">
        <!-- Factures en retard -->
        <div class="com-table-card">
            <div class="com-table-head">
                <div>
                    <div class="card-title" style="margin:0">⚠️ Factures en retard</div>
                    <div style="font-size:12px;color:#dc2626;margin-top:2px"><?= count($facturesRetard) ?> facture(s) impayée(s)</div>
                </div>
                <a href="<?= APP_URL ?>/commercial/factures?statut=en_retard" class="btn btn-sm btn-outline">Voir tout</a>
            </div>
            <?php if (empty($facturesRetard)): ?>
            <div style="padding:32px;text-align:center;color:var(--text-muted)">
                <div style="font-size:32px;margin-bottom:8px">✅</div>
                <div style="font-size:13px">Aucune facture en retard</div>
            </div>
            <?php else: ?>
            <table class="com-table">
                <thead><tr><th>Client</th><th>Facture</th><th>Échéance</th><th>Restant</th></tr></thead>
                <tbody>
                <?php foreach ($facturesRetard as $f): ?>
                <tr>
                    <td style="font-weight:500"><?= e($f['raison_sociale']) ?></td>
                    <td><a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= $f['id'] ?>" style="color:var(--navy);font-weight:600"><?= e($f['numero']) ?></a></td>
                    <td style="color:#dc2626"><?= date('d/m/Y', strtotime($f['date_echeance'])) ?></td>
                    <td class="retard-amount"><?= number_format($f['montant_ttc']-$f['montant_paye'],0,',',' ') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Dernières factures -->
        <div class="com-table-card">
            <div class="com-table-head">
                <div class="card-title" style="margin:0">Dernières factures</div>
                <a href="<?= APP_URL ?>/commercial/factures" class="btn btn-sm btn-outline">Voir tout</a>
            </div>
            <?php if (empty($dernieresFactures)): ?>
            <div style="padding:32px;text-align:center;color:var(--text-muted)">
                <div style="font-size:32px;margin-bottom:8px">📄</div>
                <div style="font-size:13px">Aucune facture</div>
            </div>
            <?php else: ?>
            <table class="com-table">
                <thead><tr><th>Client</th><th>N°</th><th>Montant</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach ($dernieresFactures as $f): ?>
                <tr>
                    <td style="font-weight:500;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($f['raison_sociale']) ?></td>
                    <td><a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= $f['id'] ?>" style="color:var(--navy);font-weight:600"><?= e($f['numero']) ?></a></td>
                    <td style="font-weight:600"><?= number_format($f['montant_ttc'],0,',',' ') ?></td>
                    <td><?= statutFactureBadge($f['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartCA'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($moisLabels) ?>,
        datasets: [{
            label: 'CA (FCFA)',
            data: <?= json_encode($caParMois) ?>,
            backgroundColor: 'rgba(201,169,110,0.75)',
            borderColor: '#c9a96e',
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => (v/1000000).toFixed(1)+'M', font: { size: 11 } }, grid: { color: '#f3f4f6' } },
            x: { ticks: { font: { size: 11 } }, grid: { display: false } }
        }
    }
});
</script>
