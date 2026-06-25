<?php
$statusColors = ['brouillon'=>'#94a3b8','envoyee'=>'#1f6e4e','payee'=>'#1f6e4e','partiel'=>'#f59e0b','retard'=>'#ef4444','annulee'=>'#6b7280'];
$statusBg     = ['brouillon'=>'#f1f5f9','envoyee'=>'#dbeafe','payee'=>'#dcfce7','partiel'=>'#fef3c7','retard'=>'#fee2e2','annulee'=>'#f3f4f6'];
$statusText   = ['brouillon'=>'#475569','envoyee'=>'#2563eb','payee'=>'#1f6e4e','partiel'=>'#d97706','retard'=>'#dc2626','annulee'=>'#374151'];
$statusLabels = ['brouillon'=>'Brouillon','envoyee'=>'Envoyée','payee'=>'Payée','partiel'=>'Paiement partiel','retard'=>'En retard','annulee'=>'Annulée'];
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap');
.fac-root { padding:32px 36px;max-width:1400px; }
.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
.page-header h1 { font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--navy-dark); }
.page-header p { font-size:13px;color:var(--text-muted);margin-top:3px; }

/* Stats */
.stats-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px; }
.stat-card { background:#fff;border-radius:14px;border:1px solid var(--border);padding:18px 20px;position:relative;overflow:hidden; }
.stat-card::before { content:'';position:absolute;top:0;left:0;right:0;height:3px; }
.stat-card.blue::before { background:#1f6e4e; }
.stat-card.green::before { background:#1f6e4e; }
.stat-card.amber::before { background:#f59e0b; }
.stat-card.red::before { background:#ef4444; }
.stat-card.navy::before { background:var(--navy); }
.stat-label { font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);font-weight:600;margin-bottom:8px; }
.stat-val { font-size:20px;font-weight:800;color:var(--navy-dark);font-family:'Playfair Display',serif; }
.stat-sub { font-size:11px;color:var(--text-muted);margin-top:4px; }

/* Filters */
.filters-bar { display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center; }
.filter-btn { padding:7px 16px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--text-muted);text-decoration:none;transition:all 0.2s; }
.filter-btn:hover,.filter-btn.active { background:var(--navy);color:#fff;border-color:var(--navy); }
.search-box { flex:1;max-width:280px;position:relative; }
.search-box input { width:100%;padding:8px 14px 8px 36px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:#fff;color:var(--text); }
.search-box input:focus { outline:none;border-color:var(--navy); }
.search-box svg { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-muted); }

/* Table */
.fac-table-wrap { background:#fff;border-radius:16px;border:1px solid var(--border);overflow:hidden; }
table.fac-table { width:100%;border-collapse:collapse; }
table.fac-table thead th { padding:12px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);background:#f8fafc;border-bottom:2px solid var(--border); }
table.fac-table tbody td { padding:14px 16px;font-size:13px;color:var(--text);border-bottom:1px solid #f8fafc; }
table.fac-table tbody tr:hover td { background:#f8fafc; }
table.fac-table tbody tr:last-child td { border-bottom:none; }
.fac-ref { font-weight:700;color:var(--navy-dark); }
.fac-client { font-size:12px;color:var(--text-muted);margin-top:2px; }
.badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600; }
.fac-progress { background:#f1f5f9;border-radius:4px;height:5px;width:80px;overflow:hidden;margin-top:4px; }
.fac-progress-bar { height:100%;border-radius:4px;background:#1f6e4e; }
.td-right { text-align:right; }

.btn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-primary { background:var(--navy);color:#fff; }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-outline { background:transparent;color:var(--navy);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);background:#f8fafc; }
.btn-sm { padding:5px 10px;font-size:11px; }

.empty-state { text-align:center;padding:60px 20px;color:var(--text-muted); }
</style>

<div class="fac-root">
    <div class="page-header">
        <div>
            <h1>Factures</h1>
            <p><?= count($factures) ?> facture(s) · <?= number_format($stats['ca_facture'] ?? 0, 0, ',', ' ') ?> F CA facturé</p>
        </div>
        <div style="display:flex;gap:10px">
            <a href="<?= APP_URL ?>/commercial/factures/nouvelle" class="btn btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Nouvelle facture
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card navy">
            <div class="stat-label">CA Facturé</div>
            <div class="stat-val"><?= number_format($stats['ca_facture'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-sub">FCFA total émis</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Encaissé</div>
            <div class="stat-val"><?= number_format($stats['ca_encaisse'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-sub">FCFA reçus</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-label">En attente</div>
            <div class="stat-val"><?= number_format($stats['ca_attente'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-sub">FCFA à encaisser</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">En retard</div>
            <div class="stat-val"><?= number_format($stats['ca_retard'] ?? 0, 0, ',', ' ') ?></div>
            <div class="stat-sub">FCFA en souffrance</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Taux recouvrement</div>
            <div class="stat-val"><?= ($stats['ca_facture'] ?? 0) > 0 ? round(($stats['ca_encaisse'] ?? 0) / $stats['ca_facture'] * 100) : 0 ?>%</div>
            <div class="stat-sub">Du CA facturé</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-bar">
        <div class="search-box">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Rechercher..." value="<?= e($_GET['q'] ?? '') ?>">
        </div>
        <a href="<?= APP_URL ?>/commercial/factures" class="filter-btn <?= !isset($_GET['statut']) ? 'active' : '' ?>">Toutes (<?= count($factures) ?>)</a>
        <?php foreach ($statusLabels as $key => $label): ?>
        <a href="<?= APP_URL ?>/commercial/factures?statut=<?= $key ?>" class="filter-btn <?= ($_GET['statut'] ?? '') === $key ? 'active' : '' ?>"
           style="<?= ($_GET['statut'] ?? '') === $key ? "background:{$statusColors[$key]};border-color:{$statusColors[$key]};color:#fff" : '' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <?php if (empty($factures)): ?>
    <div class="empty-state">
        <div style="font-size:48px;margin-bottom:12px">🧾</div>
        <div style="font-size:16px;font-weight:600;color:var(--navy-dark);margin-bottom:6px">Aucune facture</div>
        <div style="margin-bottom:20px">Créez votre première facture ou convertissez un devis accepté</div>
        <a href="<?= APP_URL ?>/commercial/factures/nouvelle" class="btn btn-gold">Nouvelle facture</a>
    </div>
    <?php else: ?>
    <div class="fac-table-wrap">
        <table class="fac-table" id="facTable">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Échéance</th>
                    <th class="td-right">Montant TTC</th>
                    <th class="td-right">Payé</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factures as $f):
                    $st = $f['statut'];
                    $pct = $f['montant_ttc'] > 0 ? min(100, round($f['montant_paye'] / $f['montant_ttc'] * 100)) : 0;
                    $isLate = in_array($st, ['envoyee','partiel']) && $f['date_echeance'] && $f['date_echeance'] < date('Y-m-d');
                    if ($isLate && $st !== 'retard') $st = 'retard';
                ?>
                <tr class="fac-row">
                    <td>
                        <div class="fac-ref"><?= e($f['reference']) ?></div>
                        <?php if ($f['objet']): ?><div class="fac-client"><?= e(substr($f['objet'], 0, 40)) ?></div><?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;color:var(--navy-dark)"><?= e($f['prospect_nom'] ?? '') ?></div>
                        <div class="fac-client"><?= e($f['prospect_ville'] ?? '') ?></div>
                    </td>
                    <td><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                    <td><?= $f['date_echeance'] ? date('d/m/Y', strtotime($f['date_echeance'])) : '—' ?></td>
                    <td class="td-right" style="font-weight:700"><?= number_format($f['montant_ttc'], 0, ',', ' ') ?> F</td>
                    <td class="td-right">
                        <div><?= number_format($f['montant_paye'], 0, ',', ' ') ?> F</div>
                        <div class="fac-progress"><div class="fac-progress-bar" style="width:<?= $pct ?>%"></div></div>
                    </td>
                    <td>
                        <span class="badge" style="background:<?= $statusBg[$st] ?? '#f1f5f9' ?>;color:<?= $statusText[$st] ?? '#475569' ?>">
                            <?= $statusLabels[$st] ?? $st ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= $f['id'] ?>" class="btn btn-outline btn-sm">Voir</a>
                            <a href="<?= APP_URL ?>/commercial/factures/pdf?id=<?= $f['id'] ?>" class="btn btn-outline btn-sm" target="_blank" title="PDF">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.fac-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
