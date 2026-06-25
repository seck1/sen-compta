<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Journal des actions</h1>
        <p class="page-subtitle">Traçabilité complète de toutes les opérations</p>
    </div>
</div>

<div class="table-wrap">
    <div class="table-header">
        <div class="table-title">Audit Trail</div>
        <span style="font-size:13px;color:var(--text-muted)"><?= count($logs) ?> dernières actions</span>
    </div>
    <?php if (empty($logs)): ?>
    <div class="empty-state">
        <h3>Aucune action enregistrée</h3>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Dossier</th>
                <th>Table</th>
                <th>Détails</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td style="font-size:13px;color:var(--text-muted);white-space:nowrap"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
            <td>
                <span style="font-weight:600;font-size:14px"><?= e($log['prenom'].' '.$log['nom']) ?></span>
            </td>
            <td>
                <span style="background:rgba(30,58,95,0.08);color:var(--navy);padding:4px 11px;border-radius:20px;font-size:14px;font-weight:600;font-family:monospace">
                    <?= e($log['action']) ?>
                </span>
            </td>
            <td style="font-size:14px"><?= e($log['raison_sociale'] ?? '—') ?></td>
            <td style="font-size:13px;color:var(--text-muted)"><?= e($log['table_cible'] ?? '—') ?></td>
            <td style="font-size:13px;color:var(--text-muted);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($log['details'] ?? '') ?></td>
            <td style="font-size:14px;font-family:monospace;color:var(--text-muted)"><?= e($log['ip_address'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
