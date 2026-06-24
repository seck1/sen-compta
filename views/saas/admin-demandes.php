<?php
$activePage = 'superadmin';
$pageTitle  = 'Demandes cabinets';
ob_start();
?>
<style>
.sa-wrap { padding:30px; max-width:1200px; }
.sa-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.sa-header h1 { font-size:1.4rem; font-weight:700; color:#1e3a5f; margin:0; }
.badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:.75rem; font-weight:600; }
.badge-en_attente { background:#fef9c3; color:#854d0e; }
.badge-accepte { background:#dcfce7; color:#166534; }
.badge-refuse { background:#fee2e2; color:#991b1b; }
.data-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.data-table th { background:#f8f9fb; padding:10px 14px; text-align:left; font-size:.8rem; text-transform:uppercase; color:#6b7280; font-weight:600; }
.data-table td { padding:12px 14px; border-top:1px solid #f1f3f7; font-size:.88rem; vertical-align:middle; }
.action-btn { padding:5px 12px; border-radius:6px; font-size:.78rem; font-weight:600; cursor:pointer; border:none; }
.btn-accepter { background:#dcfce7; color:#166534; }
.btn-refuser { background:#fee2e2; color:#991b1b; }
.msg-box { background:#f8f9fb; border-left:3px solid #c9d4e8; padding:8px 12px; border-radius:0 6px 6px 0; font-size:.82rem; color:#374151; margin-top:4px; max-width:300px; }
</style>

<div class="sa-wrap">
    <div class="sa-header">
        <h1>Demandes des cabinets</h1>
        <a href="<?= APP_URL ?>/superadmin" style="background:#e0e7ff;color:#3730a3;text-decoration:none;padding:8px 16px;border-radius:8px;font-size:.88rem;font-weight:600">← Dashboard</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cabinet</th>
                <th>Type</th>
                <th>Plan demandé</th>
                <th>Message</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($demandes as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($d['cabinet_nom']) ?></strong><br>
                    <small style="color:#6b7280"><?= htmlspecialchars($d['cabinet_email']) ?></small>
                </td>
                <td><?= ['upgrade'=>'Upgrade plan','support'=>'Support','autre'=>'Autre'][$d['type']] ?? $d['type'] ?></td>
                <td><?= htmlspecialchars($d['plan_demande_nom'] ?? '—') ?></td>
                <td>
                    <?php if ($d['message']): ?>
                        <div class="msg-box"><?= nl2br(htmlspecialchars($d['message'])) ?></div>
                    <?php else: ?>
                        <span style="color:#9ca3af">—</span>
                    <?php endif; ?>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                <td><span class="badge badge-<?= $d['statut'] ?>"><?= ['en_attente'=>'En attente','accepte'=>'Accepté','refuse'=>'Refusé'][$d['statut']] ?? $d['statut'] ?></span></td>
                <td>
                    <?php if ($d['statut'] === 'en_attente'): ?>
                    <form method="POST" action="<?= APP_URL ?>/superadmin/demandes" style="display:flex;gap:6px">
                        <?= csrfField() ?>
                        <input type="hidden" name="demande_id" value="<?= $d['id'] ?>">
                        <button name="action" value="accepter" class="action-btn btn-accepter">✓ Accepter</button>
                        <button name="action" value="refuser" class="action-btn btn-refuser">✗ Refuser</button>
                    </form>
                    <?php else: ?>
                        <span style="color:#6b7280;font-size:.8rem">Traité</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($demandes)): ?>
            <tr><td colspan="8" style="text-align:center;padding:30px;color:#6b7280">Aucune demande en cours</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/layouts/main.php';
