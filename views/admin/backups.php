<style>
.backup-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:28px; }
.backup-stat { background:#fff; border:1px solid var(--border); border-radius:14px; padding:20px; position:relative; overflow:hidden; }
.backup-stat::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
.backup-stat.navy::before { background:linear-gradient(90deg,#1e3a5f,#2a4f7c); }
.backup-stat.green::before { background:linear-gradient(90deg,#166534,#1f6e4e); }
.backup-stat.gold::before  { background:linear-gradient(90deg,#c9a96e,#f59e0b); }
.backup-stat-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:8px; font-weight:500; }
.backup-stat-val { font-family:'Cormorant Garamond',serif; font-size:32px; font-weight:600; color:var(--navy-dark); }
.backup-stat-sub { font-size:12px; color:var(--text-muted); margin-top:4px; }
</style>

<div class="page-header">
    <div>
        <div class="page-title" style="font-family:'Cormorant Garamond',serif;font-size:28px">Sauvegardes</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:3px">Gestion des sauvegardes de la base de données</div>
    </div>
    <a href="<?= APP_URL ?>/admin/backups/creer" class="btn btn-primary" onclick="return confirm('Créer une nouvelle sauvegarde maintenant ?')">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
        Sauvegarder maintenant
    </a>
</div>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-<?= $_GET['msg']==='ok' ? 'success' : ($_GET['msg']==='supprime' ? 'info' : 'danger') ?>" style="margin-bottom:16px;padding:12px 16px;border-radius:8px;background:<?= $_GET['msg']==='ok' ? '#f0fdf4' : ($_GET['msg']==='supprime' ? '#eff6ff' : '#fef2f2') ?>;border:1px solid <?= $_GET['msg']==='ok' ? '#86efac' : ($_GET['msg']==='supprime' ? '#bfdbfe' : '#fca5a5') ?>;color:<?= $_GET['msg']==='ok' ? '#166534' : ($_GET['msg']==='supprime' ? '#1d4ed8' : '#b91c1c') ?>">
    <?php if($_GET['msg']==='ok'): ?>
        ✅ Sauvegarde créée avec succès : <strong><?= e($_GET['fichier'] ?? '') ?></strong>
    <?php elseif($_GET['msg']==='supprime'): ?>
        🗑 Sauvegarde supprimée.
    <?php else: ?>
        ❌ Erreur : <?= e($_GET['detail'] ?? 'Échec de la sauvegarde') ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
$total_taille = array_sum(array_column($fichiers, 'taille'));
$derniere = !empty($fichiers) ? $fichiers[0] : null;
?>

<!-- Stats -->
<div class="backup-grid">
    <div class="backup-stat navy">
        <div class="backup-stat-label">Nombre de sauvegardes</div>
        <div class="backup-stat-val"><?= count($fichiers) ?></div>
        <div class="backup-stat-sub">Fichiers disponibles</div>
    </div>
    <div class="backup-stat green">
        <div class="backup-stat-label">Taille totale</div>
        <div class="backup-stat-val"><?= $total_taille > 1048576 ? round($total_taille/1048576,1).' Mo' : round($total_taille/1024,0).' Ko' ?></div>
        <div class="backup-stat-sub">Espace disque utilisé</div>
    </div>
    <div class="backup-stat gold">
        <div class="backup-stat-label">Dernière sauvegarde</div>
        <div class="backup-stat-val" style="font-size:20px;margin-top:4px"><?= $derniere ? date('d/m/Y', $derniere['date']) : '—' ?></div>
        <div class="backup-stat-sub"><?= $derniere ? date('H:i', $derniere['date']) : 'Aucune sauvegarde' ?></div>
    </div>
</div>

<!-- Info cron -->
<div class="card" style="margin-bottom:20px;padding:16px 20px;background:#f8f9fb;border:1px solid var(--border);border-radius:12px">
    <div style="display:flex;align-items:flex-start;gap:12px">
        <div style="font-size:24px">⚙️</div>
        <div>
            <div style="font-weight:700;color:var(--navy-dark);margin-bottom:4px">Sauvegarde automatique (nuit)</div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:8px">Pour activer la sauvegarde automatique chaque nuit à minuit, ajoutez cette ligne dans votre crontab :</div>
            <code style="display:block;background:#1e3a5f;color:#c9a96e;padding:10px 14px;border-radius:6px;font-size:12px;font-family:monospace">
                0 0 * * * curl -s "<?= APP_URL ?>/admin/backups/auto?token=smc_backup_secret_2026" > /dev/null 2>&1
            </code>
            <div style="font-size:11px;color:#aaa;margin-top:6px">⚠️ Changez le token dans votre config pour plus de sécurité. Les 30 dernières sauvegardes sont conservées automatiquement.</div>
        </div>
    </div>
</div>

<!-- Liste des sauvegardes -->
<div class="table-wrap">
    <div class="table-header">
        <span class="table-title"><?= count($fichiers) ?> sauvegarde(s)</span>
    </div>
    <?php if(empty($fichiers)): ?>
    <div class="empty-state" style="padding:48px;text-align:center;color:#aaa">
        <div style="font-size:48px;margin-bottom:12px">💾</div>
        <div style="font-size:14px;font-weight:600;color:#555;margin-bottom:6px">Aucune sauvegarde</div>
        <div style="font-size:13px">Cliquez sur "Sauvegarder maintenant" pour créer votre première sauvegarde.</div>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Fichier</th>
                <th>Date</th>
                <th>Heure</th>
                <th style="text-align:right">Taille</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($fichiers as $f): ?>
        <?php $auto = strpos($f['nom'], '_auto') !== false; ?>
        <tr>
            <td>
                <code style="font-size:11px;background:var(--bg);padding:2px 8px;border-radius:5px"><?= e($f['nom']) ?></code>
            </td>
            <td style="font-size:13px"><?= date('d/m/Y', $f['date']) ?></td>
            <td style="font-size:13px;color:var(--text-muted)"><?= date('H:i:s', $f['date']) ?></td>
            <td style="text-align:right;font-family:monospace;font-size:13px">
                <?= $f['taille'] > 1048576 ? round($f['taille']/1048576,2).' Mo' : round($f['taille']/1024,1).' Ko' ?>
            </td>
            <td>
                <span class="badge <?= $auto ? 'badge-navy' : 'badge-success' ?>">
                    <?= $auto ? 'Automatique' : 'Manuelle' ?>
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px">
                    <a href="<?= APP_URL ?>/admin/backups/telecharger?fichier=<?= urlencode($f['nom']) ?>" class="btn btn-outline btn-sm">
                        ⬇ Télécharger
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/admin/backups/supprimer" onsubmit="return confirm('Supprimer cette sauvegarde ?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="fichier" value="<?= e($f['nom']) ?>">
                        <button type="submit" class="btn btn-sm" style="background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
