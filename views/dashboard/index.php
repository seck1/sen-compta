<?php $u = auth(); ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Bonjour, <?= e($u['prenom']) ?></h1>
        <p class="page-subtitle">Vue d'ensemble du cabinet — <?= date('d F Y') ?></p>
    </div>
    <?php if (isAdmin()): ?>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/entreprises/create" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouveau dossier
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Alertes panel -->
<?php if (!empty($inactifs) || !empty($bulletinsRetard) || !empty($brouillons) || !empty($tva_retard) || !empty($exercices_ouverts)): ?>
<div style="margin-bottom:20px;display:flex;flex-direction:column;gap:8px">
    <?php if (!empty($inactifs)): ?>
    <div style="padding:14px 18px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.22);border-radius:12px">
        <div style="font-size:13px;font-weight:600;color:#92400e;margin-bottom:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;display:inline;vertical-align:-3px;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            Dossiers sans activité depuis 30 jours
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($inactifs as $in): ?>
            <a href="<?= APP_URL ?>/dossier?id=<?= $in['id'] ?? '' ?>" style="padding:4px 12px;background:rgba(245,158,11,0.15);border-radius:20px;font-size:12px;color:#92400e;text-decoration:none">
                <?= e($in['raison_sociale']) ?> <?= $in['last'] ? '· '.date('d/m', strtotime($in['last'])) : '· jamais' ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($bulletinsRetard)): ?>
    <div style="padding:14px 18px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.18);border-radius:12px">
        <div style="font-size:13px;font-weight:600;color:#7f1d1d;margin-bottom:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;display:inline;vertical-align:-3px;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            Bulletins de paie en retard — <?= moisFr() ?>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($bulletinsRetard as $br): ?>
            <a href="<?= APP_URL ?>/dossier/rh/bulletins?id=<?= $br['id'] ?>" style="padding:4px 12px;background:rgba(239,68,68,0.1);border-radius:20px;font-size:12px;color:#dc2626;text-decoration:none">
                <?= e($br['raison_sociale']) ?> — <?= $br['nb_bul'] ?>/<?= $br['nb_emp'] ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($brouillons)): ?>
    <div style="padding:14px 18px;background:rgba(234,88,12,0.07);border:1px solid rgba(234,88,12,0.2);border-radius:12px">
        <div style="font-size:13px;font-weight:600;color:#9a3412;margin-bottom:8px">
            ✏️ Écritures en brouillon à valider
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($brouillons as $b): ?>
            <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $b['id'] ?>" style="padding:4px 12px;background:rgba(234,88,12,0.12);border-radius:20px;font-size:12px;color:#9a3412;text-decoration:none;font-weight:600">
                <?= e($b['raison_sociale']) ?> — <?= $b['nb'] ?> écriture<?= $b['nb']>1?'s':'' ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($tva_retard)): ?>
    <div style="padding:14px 18px;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.18);border-radius:12px">
        <div style="font-size:13px;font-weight:600;color:#7f1d1d;margin-bottom:8px">
            ⚠️ Déclaration TVA manquante — <?= moisFr(null, (int)$mois_tva, (int)$annee_tva) ?>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($tva_retard as $t): ?>
            <a href="<?= APP_URL ?>/dossier/tva?id=<?= $t['id'] ?>" style="padding:4px 12px;background:rgba(220,38,38,0.1);border-radius:20px;font-size:12px;color:#dc2626;text-decoration:none;font-weight:600">
                <?= e($t['raison_sociale']) ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($exercices_ouverts)): ?>
    <div style="padding:14px 18px;background:rgba(184,146,63,0.06);border:1px solid rgba(184,146,63,0.18);border-radius:12px">
        <div style="font-size:13px;font-weight:600;color:#4c1d95;margin-bottom:8px">
            🔒 Exercices anciens non clôturés
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($exercices_ouverts as $ex): ?>
            <a href="<?= APP_URL ?>/dossier/cloture?id=<?= $ex['id'] ?>" style="padding:4px 12px;background:rgba(184,146,63,0.1);border-radius:20px;font-size:12px;color:#b8923f;text-decoration:none;font-weight:600">
                <?= e($ex['raison_sociale']) ?> — Exercice <?= $ex['exercice'] ?>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
        </div>
        <div class="kpi-label">Dossiers actifs</div>
        <div class="kpi-value"><?= $nbEntreprises ?></div>
        <div class="kpi-trend">Entreprises gérées</div>
    </div>

    <?php if (isAdmin()): ?>
    <div class="kpi-card">
        <div class="kpi-icon gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        </div>
        <div class="kpi-label">Collaborateurs</div>
        <div class="kpi-value"><?= $nbUsers ?></div>
        <div class="kpi-trend">Comptes actifs</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="kpi-label">Honoraires du mois</div>
        <div class="kpi-value" style="font-size:20px"><?= number_format($honMois, 0, ',', ' ') ?></div>
        <div class="kpi-trend">FCFA · <?= moisFr() ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon orange">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
        </div>
        <div class="kpi-label">Missions en cours</div>
        <div class="kpi-value"><?= $nbMissions ?></div>
        <div class="kpi-trend">Missions actives</div>
    </div>
    <?php endif; ?>

    <div class="kpi-card">
        <div class="kpi-icon orange">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
        </div>
        <div class="kpi-label">Échéances en retard</div>
        <div class="kpi-value" style="color:<?= $nbEcheances > 0 ? 'var(--danger)' : 'inherit' ?>"><?= $nbEcheances ?></div>
        <div class="kpi-trend <?= $nbEcheances > 0 ? 'down' : '' ?>">
            <?= $nbEcheances > 0 ? 'Action requise' : 'Tout est à jour' ?>
        </div>
    </div>
</div>

<!-- Graphiques -->
<?php if (isAdmin()): ?>
<?php
// Données graphique: CA mensuel (produits classe 7) sur 6 mois
$caData = [];
$chargesData = [];
$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $d = new DateTime("first day of -$i month");
    $labels[] = $d->format('M Y');
    $yr = $d->format('Y');
    $mo = $d->format('m');
    $ca = $db->query("SELECT COALESCE(SUM(l.credit-l.debit),0) FROM lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id JOIN comptes c ON c.id=l.compte_id WHERE c.classe=7 AND YEAR(e.date_ecriture)=$yr AND MONTH(e.date_ecriture)=$mo")->fetchColumn();
    $ch = $db->query("SELECT COALESCE(SUM(l.debit-l.credit),0) FROM lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id JOIN comptes c ON c.id=l.compte_id WHERE c.classe=6 AND YEAR(e.date_ecriture)=$yr AND MONTH(e.date_ecriture)=$mo")->fetchColumn();
    $caData[] = max(0, (float)$ca);
    $chargesData[] = max(0, (float)$ch);
}
// Répartition charges par poste (classe 6)
$repartition = $db->query("SELECT LEFT(c.numero,2) as poste, SUM(l.debit-l.credit) as total FROM lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id JOIN comptes c ON c.id=l.compte_id WHERE c.classe=6 AND YEAR(e.date_ecriture)=".date('Y')." GROUP BY LEFT(c.numero,2) ORDER BY total DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
$repartLabels = array_map(fn($r)=>'Classe '.$r['poste'], $repartition);
$repartData   = array_map(fn($r)=>max(0,(float)$r['total']), $repartition);
?>
<div class="dash-charts">
    <!-- Graphique CA vs Charges -->
    <div class="card" style="padding:24px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <div>
                <div style="font-size:15px;font-weight:600;color:var(--navy-dark)">Produits vs Charges</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:3px">6 derniers mois — toutes entreprises</div>
            </div>
            <div style="display:flex;gap:14px;font-size:12px">
                <span style="display:flex;align-items:center;gap:5px"><span style="width:12px;height:12px;border-radius:3px;background:#1f6e4e;display:inline-block"></span>Produits</span>
                <span style="display:flex;align-items:center;gap:5px"><span style="width:12px;height:12px;border-radius:3px;background:#b8923f;display:inline-block"></span>Charges</span>
            </div>
        </div>
        <canvas id="chartCa" height="100"></canvas>
    </div>

    <!-- Répartition charges -->
    <div class="card" style="padding:24px">
        <div style="font-size:15px;font-weight:600;color:var(--navy-dark);margin-bottom:5px">Répartition charges</div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:20px">Exercice <?= date('Y') ?></div>
        <?php if (!empty($repartition)): ?>
        <canvas id="chartRep" height="180"></canvas>
        <?php else: ?>
        <div style="text-align:center;padding:40px;color:var(--text-muted);font-size:13px">Aucune écriture de charges</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const caLabels   = <?= json_encode($labels) ?>;
const caData     = <?= json_encode($caData) ?>;
const chData     = <?= json_encode($chargesData) ?>;
const repLabels  = <?= json_encode($repartLabels) ?>;
const repData    = <?= json_encode($repartData) ?>;

// Graphique barres CA vs Charges
new Chart(document.getElementById('chartCa'), {
    type: 'bar',
    data: {
        labels: caLabels,
        datasets: [
            { label: 'Produits', data: caData, backgroundColor: 'rgba(31,110,78,0.85)', borderRadius: 6 },
            { label: 'Charges',  data: chData,  backgroundColor: 'rgba(184,146,63,0.80)', borderRadius: 6 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: { callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'k' : v, font: { size: 11 } },
                grid: { color: 'rgba(0,0,0,0.04)' }
            },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

// Graphique donut charges
<?php if (!empty($repartition)): ?>
new Chart(document.getElementById('chartRep'), {
    type: 'doughnut',
    data: {
        labels: repLabels,
        datasets: [{ data: repData, backgroundColor: ['#1f6e4e','#2a8a63','#46a87d','#7ab59a','#b8923f','#d4b673'], borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
            tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + new Intl.NumberFormat('fr').format(ctx.raw) + ' F' } }
        },
        cutout: '65%'
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

<!-- Dossiers entreprises -->
<div class="table-wrap">
    <div class="table-header">
        <div class="table-title">Dossiers Entreprises</div>
        <?php if (isAdmin()): ?>
        <a href="<?= APP_URL ?>/entreprises" class="btn btn-outline btn-sm">
            Gérer
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($entreprises)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
        <h3>Aucun dossier entreprise</h3>
        <p>Créez votre premier dossier pour commencer</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Entreprise</th>
                <th>Régime fiscal</th>
                <th>Exercice</th>
                <th>Nb écritures</th>
                <th>Dernière activité</th>
                <th>Alertes</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entreprises as $e): ?>
            <?php
                $regimeBadge = match($e['regime_fiscal'] ?? '') {
                    'RN'  => 'badge-info',
                    'RS'  => 'badge-navy',
                    'CGI' => 'badge-warning',
                    default => 'badge-navy'
                };
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div class="ent-avatar" style="background:<?= !empty($e['logo']) ? '#f8fafc' : e($e['couleur'] ?? '#1e3a5f') ?>;<?= !empty($e['logo']) ? 'border:1px solid #e5e7eb;padding:2px;' : '' ?>">
                            <?php if(!empty($e['logo'])): ?>
                                <img src="<?= APP_URL ?>/logos/<?= e($e['logo']) ?>" alt="" style="width:100%;height:100%;object-fit:contain;border-radius:8px">
                            <?php else: ?>
                                <?= strtoupper(substr($e['raison_sociale'], 0, 2)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-weight:500"><?= e($e['raison_sociale']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= e($e['ninea'] ?? '—') ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="badge <?= $regimeBadge ?>"><?= e($e['regime_fiscal'] ?? '—') ?></span></td>
                <td><?= e($e['exercice_courant']) ?></td>
                <td style="font-family:monospace"><?= number_format($e['nb_ecritures'], 0, ',', ' ') ?></td>
                <td style="font-size:12px;color:var(--text-muted)">
                    <?= $e['last_activity'] ? date('d/m/Y', strtotime($e['last_activity'])) : '<span style="color:var(--danger)">Aucune</span>' ?>
                </td>
                <td>
                    <?php
                    $entBrouillon = array_filter($brouillons ?? [], fn($b) => $b['id'] == $e['id']);
                    $entTva = array_filter($tva_retard ?? [], fn($t) => $t['id'] == $e['id']);
                    $entEx = array_filter($exercices_ouverts ?? [], fn($x) => $x['id'] == $e['id']);
                    $entInactif = array_filter($inactifs ?? [], fn($i) => ($i['id'] ?? '') == $e['id']);
                    ?>
                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                    <?php if (!empty($entBrouillon)): ?>
                        <span style="padding:2px 8px;background:rgba(234,88,12,0.12);border-radius:10px;font-size:11px;color:#9a3412;font-weight:600">Brouillon</span>
                    <?php endif; ?>
                    <?php if (!empty($entTva)): ?>
                        <span style="padding:2px 8px;background:rgba(220,38,38,0.1);border-radius:10px;font-size:11px;color:#dc2626;font-weight:600">TVA</span>
                    <?php endif; ?>
                    <?php if (!empty($entEx)): ?>
                        <span style="padding:2px 8px;background:rgba(184,146,63,0.1);border-radius:10px;font-size:11px;color:#b8923f;font-weight:600">Clôture</span>
                    <?php endif; ?>
                    <?php if (!empty($entInactif)): ?>
                        <span style="padding:2px 8px;background:rgba(245,158,11,0.12);border-radius:10px;font-size:11px;color:#92400e;font-weight:600">Inactif</span>
                    <?php endif; ?>
                    <?php if (empty($entBrouillon) && empty($entTva) && empty($entEx) && empty($entInactif)): ?>
                        <span style="color:var(--text-muted);font-size:12px">—</span>
                    <?php endif; ?>
                    </div>
                </td>
                <td>
                    <?php if ($e['statut'] === 'actif'): ?>
                        <span class="badge badge-success">Actif</span>
                    <?php elseif ($e['statut'] === 'suspendu'): ?>
                        <span class="badge badge-warning">Suspendu</span>
                    <?php else: ?>
                        <span class="badge badge-navy">Archivé</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/dossier?id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Ouvrir</a>
                        <?php if (isAdmin()): ?>
                        <a href="<?= APP_URL ?>/entreprises/edit?id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Éditer</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
