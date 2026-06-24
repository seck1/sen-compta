<style>
.tiers-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:14px; }
.tiers-card {
    background:#fff; border:1px solid var(--border); border-radius:14px;
    padding:18px 20px; transition:box-shadow .2s, transform .2s;
    position:relative;
}
.tiers-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.08); transform:translateY(-1px); }
.tiers-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:14px 14px 0 0; }
.tiers-card.fournisseur::before { background:#f59e0b; }
.tiers-card.client::before      { background:#3b82f6; }
.tiers-card.les_deux::before    { background:linear-gradient(90deg,#f59e0b,#3b82f6); }

.tiers-actions { flex-wrap:wrap; }

.tiers-type-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:20px; font-size:15px; font-weight:600;
    margin-bottom:10px;
}
.badge-fourn { background:rgba(245,158,11,.1); color:#d97706; }
.badge-cli   { background:rgba(59,130,246,.1); color:#2563eb; }
.badge-deux  { background:rgba(139,92,246,.1); color:#7c3aed; }

.tiers-nom { font-size:19px; font-weight:700; color:var(--navy-dark); margin-bottom:8px; }
.tiers-info { font-size:16px; color:var(--text-muted); display:flex; flex-direction:column; gap:3px; }
.tiers-info span { display:flex; align-items:center; gap:6px; }

.tiers-actions { display:flex; gap:8px; margin-top:14px; padding-top:12px; border-top:1px solid var(--border); }

.filter-bar {
    display:flex; align-items:center; gap:10px;
    background:#fff; border:1px solid var(--border); border-radius:12px;
    padding:12px 18px; margin-bottom:20px;
}
.filter-btn {
    padding:6px 16px; border-radius:20px; border:1px solid var(--border);
    background:none; cursor:pointer; font-size:16px; font-weight:500;
    color:var(--text-muted); transition:all .15s; font-family:'DM Sans',sans-serif;
}
.filter-btn.active { background:var(--navy); color:#fff; border-color:var(--navy); }
.filter-search {
    flex:1; padding:7px 12px; border:1px solid var(--border); border-radius:8px;
    font-size:17px; font-family:'DM Sans',sans-serif; outline:none;
}
.filter-search:focus { border-color:var(--navy); }

.empty-tiers {
    text-align:center; padding:60px 32px;
    background:#fff; border:1px solid var(--border); border-radius:14px;
}
.empty-tiers-icon { font-size:48px; margin-bottom:14px; }
.empty-tiers h3 { font-size:17px; font-weight:600; color:var(--navy-dark); margin-bottom:6px; }
.empty-tiers p  { font-size:17px; color:var(--text-muted); }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Tiers</h1>
        <p class="page-subtitle">
            Fournisseurs &amp; clients de <?= e($entreprise['raison_sociale']) ?>
        </p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/dossier/tiers/form?id=<?= $entreprise['id'] ?>&type=fournisseur" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
            Nouveau fournisseur
        </a>
        <a href="<?= APP_URL ?>/dossier/tiers/form?id=<?= $entreprise['id'] ?>&type=client" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            Nouveau client
        </a>
    </div>
</div>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
        <div style="width:42px;height:42px;border-radius:10px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;color:#d97706"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></div>
        <div>
            <div style="font-size:26px;font-weight:700;color:var(--navy-dark);font-family:Arial"><?= $stats['fournisseur'] + $stats['les_deux'] ?></div>
            <div style="font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px">Fournisseurs</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
        <div style="width:42px;height:42px;border-radius:10px;background:rgba(59,130,246,.1);display:flex;align-items:center;justify-content:center;color:#2563eb"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></div>
        <div>
            <div style="font-size:26px;font-weight:700;color:var(--navy-dark);font-family:Arial"><?= $stats['client'] + $stats['les_deux'] ?></div>
            <div style="font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px">Clients</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
        <div style="width:42px;height:42px;border-radius:10px;background:rgba(139,92,246,.1);display:flex;align-items:center;justify-content:center;color:#7c3aed"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg></div>
        <div>
            <div style="font-size:26px;font-weight:700;color:var(--navy-dark);font-family:Arial"><?= array_sum($stats) ?></div>
            <div style="font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px">Total tiers</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<form method="GET" action="">
    <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
    <div class="filter-bar">
        <?php if (!$filtre_type): ?>
            <button type="submit" name="type" value="" class="filter-btn active">Tous</button>
            <button type="submit" name="type" value="fournisseur" class="filter-btn" style="display:inline-flex;align-items:center;gap:5px;border-color:#f59e0b;color:#d97706">
                Fournisseurs <span style="background:rgba(245,158,11,.15);color:#d97706;border-radius:20px;padding:1px 7px;font-size:13px;font-weight:700"><?= $stats['fournisseur'] ?></span>
            </button>
            <button type="submit" name="type" value="client" class="filter-btn" style="display:inline-flex;align-items:center;gap:5px;border-color:#3b82f6;color:#2563eb">
                Clients <span style="background:rgba(59,130,246,.12);color:#2563eb;border-radius:20px;padding:1px 7px;font-size:13px;font-weight:700"><?= $stats['client'] ?></span>
            </button>
            <?php if ($stats['les_deux'] > 0): ?>
            <button type="submit" name="type" value="les_deux" class="filter-btn" style="display:inline-flex;align-items:center;gap:5px;border-color:#7c3aed;color:#7c3aed">
                Les deux <span style="background:rgba(139,92,246,.12);color:#7c3aed;border-radius:20px;padding:1px 7px;font-size:13px;font-weight:700"><?= $stats['les_deux'] ?></span>
            </button>
            <?php endif; ?>
        <?php elseif ($filtre_type === 'fournisseur'): ?>
            <span class="filter-btn active" style="display:inline-flex;align-items:center;gap:5px;background:#f59e0b;color:#fff;border-color:#f59e0b">
                Fournisseurs <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:20px;padding:1px 7px;font-size:13px;font-weight:700"><?= $stats['fournisseur'] ?></span>
            </span>
        <?php elseif ($filtre_type === 'client'): ?>
            <span class="filter-btn active" style="display:inline-flex;align-items:center;gap:5px;background:#3b82f6;color:#fff;border-color:#3b82f6">
                Clients <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:20px;padding:1px 7px;font-size:13px;font-weight:700"><?= $stats['client'] ?></span>
            </span>
        <?php else: ?>
            <span class="filter-btn active" style="display:inline-flex;align-items:center;gap:5px;background:#7c3aed;color:#fff;border-color:#7c3aed">
                Les deux <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:20px;padding:1px 7px;font-size:13px;font-weight:700"><?= $stats['les_deux'] ?></span>
            </span>
        <?php endif; ?>
        <input type="text" name="q" class="filter-search" placeholder="Rechercher par nom…" value="<?= e($filtre_q) ?>">
        <?php if ($filtre_q): ?>
        <a href="<?= APP_URL ?>/dossier/tiers?id=<?= $entreprise['id'] ?>" style="font-size:16px;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:4px"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Effacer</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($tiers)): ?>
<div class="empty-tiers">
    <div class="empty-tiers-icon" style="display:flex;justify-content:center;color:var(--border)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" style="width:48px;height:48px"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg></div>
    <h3>Aucun tiers pour l'instant</h3>
    <p>Ajoutez vos fournisseurs et clients pour les retrouver rapidement lors de la saisie.</p>
    <a href="<?= APP_URL ?>/dossier/tiers/form?id=<?= $entreprise['id'] ?>" class="btn btn-primary" style="margin-top:16px">+ Créer le premier tiers</a>
</div>
<?php else: ?>
<div class="table-wrap" style="width:100%">
    <table style="width:100%">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>NINEA</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($tiers as $t):
        $badgeClass = $t['type'] === 'fournisseur' ? 'badge-fourn' : ($t['type'] === 'client' ? 'badge-cli' : 'badge-deux');
        $typeLabel  = $t['type'] === 'fournisseur' ? 'Fournisseur' : ($t['type'] === 'client' ? 'Client' : 'Fourn. & Client');
    ?>
        <tr>
            <td><strong style="color:var(--navy-dark)"><?= e($t['nom']) ?></strong></td>
            <td><span class="tiers-type-badge <?= $badgeClass ?>"><?= $typeLabel ?></span></td>
            <td style="color:var(--text-muted)"><?= e($t['ninea'] ?? '—') ?></td>
            <td style="white-space:nowrap"><?= e($t['telephone'] ?? '—') ?></td>
            <td><?= e($t['email'] ?? '—') ?></td>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($t['adresse'] ?? '—') ?></td>
            <td style="white-space:nowrap">
                <a href="<?= APP_URL ?>/dossier/tiers/voir?id=<?= $entreprise['id'] ?>&tiers_id=<?= $t['id'] ?>&retour_type=<?= e($filtre_type) ?>" class="btn btn-outline btn-sm">Voir</a>
                <a href="<?= APP_URL ?>/dossier/tiers/form?id=<?= $entreprise['id'] ?>&tiers_id=<?= $t['id'] ?>" class="btn btn-outline btn-sm">Modifier</a>
                <button type="button" onclick="supprimerTiers(<?= $t['id'] ?>, '<?= e(addslashes($t['nom'])) ?>')" class="btn btn-danger btn-sm">Supprimer</button>
            </td>
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function supprimerTiers(id, nom) {
    if (!confirm('Supprimer "' + nom + '" ?')) return;
    fetch('<?= APP_URL ?>/dossier/tiers/supprimer', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'entreprise_id=<?= $entreprise['id'] ?>&tiers_id=' + id
    }).then(() => location.reload());
}
</script>
