<?php
$activePage = 'superadmin';
$pageTitle  = 'Suivi des paiements';
ob_start();
?>
<style>
.sa-wrap { padding:30px; max-width:1400px; }
.sa-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.sa-header h1 { font-size:1.4rem; font-weight:700; color:#1e3a5f; margin:0; }
.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:.75rem; font-weight:600; }
.badge-valide { background:#dcfce7; color:#166534; }
.badge-en_attente { background:#fef9c3; color:#854d0e; }
.badge-refuse { background:#fee2e2; color:#991b1b; }
.data-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.data-table th { background:#f8f9fb; padding:10px 14px; text-align:left; font-size:.8rem; text-transform:uppercase; color:#6b7280; font-weight:600; }
.data-table td { padding:12px 14px; border-top:1px solid #f1f3f7; font-size:.88rem; vertical-align:middle; }
.action-btn { padding:5px 12px; border-radius:6px; font-size:.78rem; font-weight:600; cursor:pointer; border:none; }
.btn-valider { background:#dcfce7; color:#166534; }
.btn-refuser { background:#fee2e2; color:#991b1b; }
</style>

<div class="sa-wrap">
    <div class="sa-header">
        <h1>Paiements d'abonnement</h1>
        <a href="<?= APP_URL ?>/superadmin" class="action-btn" style="background:#e0e7ff;color:#3730a3;text-decoration:none;padding:8px 16px">← Dashboard</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cabinet</th>
                <th>Plan</th>
                <th>Montant</th>
                <th>Moyen</th>
                <th>Référence</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($paiements as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($p['cabinet_nom']) ?></strong><br>
                    <small style="color:#6b7280"><?= htmlspecialchars($p['cabinet_email']) ?></small>
                </td>
                <td><?= htmlspecialchars($p['plan_nom']) ?></td>
                <td><strong><?= number_format($p['montant'],0,',',' ') ?> FCFA</strong><br><small><?= $p['periodicite'] === 'annuel' ? 'Annuel' : 'Mensuel' ?></small></td>
                <td><?= htmlspecialchars($p['moyen_paiement'] ?? '—') ?></td>
                <td><code><?= htmlspecialchars($p['reference'] ?? '—') ?></code></td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td><span class="badge badge-<?= $p['statut'] ?>"><?= ['en_attente'=>'En attente','valide'=>'Validé','refuse'=>'Refusé'][$p['statut']] ?? $p['statut'] ?></span></td>
                <td>
                    <?php if ($p['statut'] === 'en_attente'): ?>
                    <form method="POST" action="<?= APP_URL ?>/superadmin/paiements/valider" style="display:inline;display:flex;gap:6px">
                        <?= csrfField() ?>
                        <input type="hidden" name="paiement_id" value="<?= $p['id'] ?>">
                        <button name="action" value="valider" class="action-btn btn-valider">✓ Valider</button>
                        <button name="action" value="refuser" class="action-btn btn-refuser">✗ Refuser</button>
                    </form>
                    <?php else: ?>
                        <span style="color:#6b7280;font-size:.8rem">Traité</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($paiements)): ?>
            <tr><td colspan="9" style="text-align:center;padding:30px;color:#6b7280">Aucun paiement enregistré</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/layouts/main.php';
