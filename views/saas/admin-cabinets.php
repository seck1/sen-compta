<?php
$activePage = 'superadmin';
$pageTitle  = 'Gestion des cabinets';
ob_start();
?>
<style>
.sa-wrap { padding: 30px; max-width: 1400px; }
.sa-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.sa-header h1 { font-size:1.4rem; font-weight:700; color:#1e3a5f; margin:0; }
.filter-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.filter-bar input, .filter-bar select { padding:8px 12px; border:1px solid #dde3ec; border-radius:8px; font-size:.88rem; }
.filter-bar button { padding:8px 16px; background:#1e3a5f; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.88rem; }
.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:.75rem; font-weight:600; }
.badge-actif { background:#dcfce7; color:#166534; }
.badge-essai { background:#fef9c3; color:#854d0e; }
.badge-suspendu { background:#fee2e2; color:#991b1b; }
.badge-attente { background:#e0e7ff; color:#3730a3; }
.data-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.data-table th { background:#f8f9fb; padding:10px 14px; text-align:left; font-size:.8rem; text-transform:uppercase; color:#6b7280; font-weight:600; }
.data-table td { padding:12px 14px; border-top:1px solid #f1f3f7; font-size:.88rem; vertical-align:middle; }
.data-table tr:hover td { background:#fafbfc; }
.action-btn { padding:4px 10px; border-radius:6px; font-size:.78rem; font-weight:600; cursor:pointer; border:none; }
.btn-activer { background:#dcfce7; color:#166534; }
.btn-suspendre { background:#fee2e2; color:#991b1b; }
.btn-plan { background:#e0e7ff; color:#3730a3; }
</style>

<div class="sa-wrap">
    <div class="sa-header">
        <h1>Cabinets inscrits</h1>
        <a href="<?= APP_URL ?>/superadmin" class="action-btn btn-plan" style="text-decoration:none;padding:8px 16px">← Dashboard</a>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select name="statut">
            <option value="">Tous les statuts</option>
            <option value="actif" <?= ($_GET['statut']??'')==='actif'?'selected':'' ?>>Actif</option>
            <option value="essai" <?= ($_GET['statut']??'')==='essai'?'selected':'' ?>>Essai</option>
            <option value="suspendu" <?= ($_GET['statut']??'')==='suspendu'?'selected':'' ?>>Suspendu</option>
            <option value="en_attente" <?= ($_GET['statut']??'')==='en_attente'?'selected':'' ?>>En attente</option>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cabinet</th>
                <th>Email</th>
                <th>Plan</th>
                <th>Entreprises</th>
                <th>Users</th>
                <th>Statut</th>
                <th>Inscrit le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cabinets as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><strong><?= htmlspecialchars($c['nom']) ?></strong><br><small style="color:#6b7280"><?= htmlspecialchars($c['responsable_nom'] ?? '') ?></small></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['plan_nom']) ?> <small style="color:#6b7280">(<?= number_format($c['prix_mois'],0,',',' ') ?> FCFA)</small></td>
                <td style="text-align:center"><?= $c['nb_entreprises'] ?></td>
                <td style="text-align:center"><?= $c['nb_users'] ?></td>
                <td>
                    <?php $sc = $c['statut'] ?? 'en_attente'; ?>
                    <span class="badge badge-<?= $sc === 'en_attente' ? 'attente' : $sc ?>">
                        <?= ['actif'=>'Actif','essai'=>'Essai','suspendu'=>'Suspendu','en_attente'=>'En attente'][$sc] ?? $sc ?>
                    </span>
                </td>
                <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                <td>
                    <form method="POST" action="<?= APP_URL ?>/superadmin/cabinets/action" style="display:inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="cabinet_id" value="<?= $c['id'] ?>">
                        <?php if ($c['statut'] !== 'actif'): ?>
                            <button name="action" value="activer" class="action-btn btn-activer">Activer</button>
                        <?php endif; ?>
                        <?php if ($c['statut'] !== 'suspendu'): ?>
                            <button name="action" value="suspendre" class="action-btn btn-suspendre">Suspendre</button>
                        <?php endif; ?>
                        <select name="plan_id" onchange="this.form.action='<?= APP_URL ?>/superadmin/cabinets/action'; this.form.querySelector('[name=action]').value='changer_plan'; this.form.submit();" style="padding:3px 6px;border:1px solid #dde3ec;border-radius:6px;font-size:.78rem">
                            <option value="">Changer plan</option>
                            <?php foreach ($plans as $pl): ?>
                                <option value="<?= $pl['id'] ?>" <?= $c['plan_id']==$pl['id']?'selected':'' ?>><?= htmlspecialchars($pl['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="action" value="">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($cabinets)): ?>
            <tr><td colspan="9" style="text-align:center;padding:30px;color:#6b7280">Aucun cabinet trouvé</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/layouts/main.php';
