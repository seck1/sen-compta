<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Dossiers Entreprises</h1>
        <p class="page-subtitle"><?= count($entreprises) ?> dossier<?= count($entreprises) > 1 ? 's' : '' ?> géré<?= count($entreprises) > 1 ? 's' : '' ?></p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="<?= APP_URL ?>/entreprises/create" class="btn btn-gold">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nouveau dossier
    </a>
    <?php endif; ?>
</div>

<!-- Vue cartes -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:28px">
    <?php foreach ($entreprises as $ent): ?>
    <div class="card" style="padding:0;overflow:hidden;transition:transform 0.2s,box-shadow 0.2s;cursor:pointer"
         onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,0.1)'"
         onmouseleave="this.style.transform='';this.style.boxShadow=''">
        <!-- Bande couleur -->
        <div style="height:5px;background:<?= e($ent['couleur']) ?>"></div>
        <div style="padding:20px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
                <div style="display:flex;align-items:center;gap:12px">
                    <?php if(!empty($ent['logo'])): ?>
                    <div style="width:80px;height:80px;border-radius:12px;overflow:hidden;border:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,0.08)">
                        <img src="<?= APP_URL ?>/logos/<?= e($ent['logo']) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;background:#fff;padding:6px;box-sizing:border-box;">
                    </div>
                    <?php else: ?>
                    <div class="ent-avatar" style="background:<?= e($ent['couleur']) ?>;width:80px;height:80px;font-size:22px;border-radius:12px;flex-shrink:0">
                        <?= strtoupper(substr($ent['raison_sociale'], 0, 2)) ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:var(--navy-dark)"><?= e($ent['raison_sociale']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)"><?= e($ent['forme_juridique']) ?></div>
                    </div>
                </div>
                <?php if ($ent['statut'] === 'actif'): ?>
                    <span class="badge badge-success">Actif</span>
                <?php elseif ($ent['statut'] === 'suspendu'): ?>
                    <span class="badge badge-warning">Suspendu</span>
                <?php else: ?>
                    <span class="badge badge-navy">Archivé</span>
                <?php endif; ?>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
                <div style="background:var(--bg);border-radius:8px;padding:10px">
                    <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px">Code</div>
                    <div style="font-size:13px;font-weight:600;color:var(--navy)"><?= e($ent['code_dossier']) ?></div>
                </div>
                <div style="background:var(--bg);border-radius:8px;padding:10px">
                    <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px">Exercice</div>
                    <div style="font-size:13px;font-weight:600;color:var(--navy)"><?= e($ent['exercice_courant']) ?></div>
                </div>
            </div>

            <?php if ($ent['secteur_activite']): ?>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:14px">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                <?= e($ent['secteur_activite']) ?>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:8px">
                <a href="<?= APP_URL ?>/dossier?id=<?= $ent['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Ouvrir
                </a>
                <?php if (isAdmin()): ?>
                <a href="<?= APP_URL ?>/entreprises/delete?id=<?= $ent['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Archiver ce dossier ?')">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($entreprises)): ?>
    <div class="card" style="grid-column:1/-1">
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
            <h3>Aucun dossier entreprise</h3>
            <p>Créez votre premier dossier pour commencer la gestion comptable</p>
        </div>
    </div>
    <?php endif; ?>
</div>
