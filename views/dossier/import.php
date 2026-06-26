<style>
.imp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 8px; }
.imp-card {
    background: #fff; border: 1px solid var(--border); border-radius: 16px;
    padding: 24px; display: flex; flex-direction: column;
}
.imp-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
.imp-ico {
    width: 42px; height: 42px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; color: #fff;
}
.imp-ico svg { width: 22px; height: 22px; }
.imp-ico.green { background: linear-gradient(135deg, #2a8a63, #1f6e4e); }
.imp-ico.blue  { background: linear-gradient(135deg, #3b6ea5, #1e3a5f); }
.imp-ico.gold  { background: linear-gradient(135deg, #d9b876, #a8843f); }
.imp-card h3 { font-size: 17px; font-weight: 700; color: var(--navy-dark); }
.imp-card p.desc { font-size: 13.5px; color: var(--text-muted); line-height: 1.5; margin: 4px 0 16px; }
.imp-cols {
    font-size: 12px; color: var(--text-muted); background: #f6f8f7;
    border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; margin-bottom: 14px;
}
.imp-cols b { color: var(--navy-dark); }
.imp-file { margin-bottom: 12px; }
.imp-file input[type=file] {
    width: 100%; font-size: 13px; padding: 9px; border: 1.5px dashed var(--border);
    border-radius: 9px; background: #fafbfb; cursor: pointer;
}
.imp-actions { margin-top: auto; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.imp-btn {
    display: inline-flex; align-items: center; gap: 7px; border: none; cursor: pointer;
    background: linear-gradient(135deg, #2a8a63, #1f6e4e); color: #fff;
    padding: 10px 18px; border-radius: 9px; font-size: 14px; font-weight: 600; font-family: inherit;
}
.imp-btn:hover { filter: brightness(1.05); }
.imp-modele {
    font-size: 13px; color: var(--gold-dark); text-decoration: none; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.imp-modele:hover { text-decoration: underline; }
.imp-radio { display: inline-flex; gap: 14px; margin-bottom: 12px; font-size: 13.5px; }
.imp-radio label { display: inline-flex; align-items: center; gap: 5px; cursor: pointer; }
.imp-flash { padding: 13px 18px; border-radius: 11px; margin-bottom: 18px; font-size: 14px; font-weight: 500; }
.imp-flash.ok  { background: rgba(31,110,78,0.10); color: #1f6e4e; border: 1px solid rgba(31,110,78,0.25); }
.imp-flash.err { background: rgba(192,57,43,0.08); color: #c0392b; border: 1px solid rgba(192,57,43,0.25); }
.imp-warn { font-size: 12.5px; color: #a8843f; margin-top: 8px; }
</style>

<div class="page-header" style="margin-bottom:20px">
    <div>
        <h1 class="page-title">Importer des données</h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> · Exercice <?= (int)$exercice ?></p>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="imp-flash ok"><?= e($_SESSION['flash_success']) ?></div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="imp-flash err"><?= e($_SESSION['flash_error']) ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="imp-grid">

    <!-- Clients / Fournisseurs -->
    <div class="imp-card">
        <div class="imp-card-head">
            <div class="imp-ico green"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg></div>
            <h3>Clients & Fournisseurs</h3>
        </div>
        <p class="desc">Importez votre liste de tiers. Le champ <b>type</b> indique « client », « fournisseur » ou « les_deux ».</p>
        <div class="imp-cols"><b>Colonnes :</b> nom, type, ninea, telephone, email, adresse</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/tiers" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <div class="imp-radio">
                <label><input type="radio" name="type_force" value="" checked> Auto (selon fichier)</label>
                <label><input type="radio" name="type_force" value="client"> Tous clients</label>
                <label><input type="radio" name="type_force" value="fournisseur"> Tous fournisseurs</label>
            </div>
            <div class="imp-file"><input type="file" name="fichier" accept=".csv,.xlsx,.xls,.txt" required></div>
            <div class="imp-actions">
                <button type="submit" class="imp-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Importer
                </button>
                <a class="imp-modele" href="<?= APP_URL ?>/dossier/import/modele?id=<?= $entreprise['id'] ?>&type=tiers">⬇ Modèle CSV</a>
            </div>
        </form>
    </div>

    <!-- Plan comptable -->
    <div class="imp-card">
        <div class="imp-card-head">
            <div class="imp-ico blue"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg></div>
            <h3>Plan comptable</h3>
        </div>
        <p class="desc">Importez vos comptes SYSCOHADA. Les comptes déjà existants sont ignorés. Le <b>type</b> est déduit de la classe si absent.</p>
        <div class="imp-cols"><b>Colonnes :</b> numero, intitule, type_compte</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/plan-comptable" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <div class="imp-file"><input type="file" name="fichier" accept=".csv,.xlsx,.xls,.txt" required></div>
            <div class="imp-actions">
                <button type="submit" class="imp-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Importer
                </button>
                <a class="imp-modele" href="<?= APP_URL ?>/dossier/import/modele?id=<?= $entreprise['id'] ?>&type=comptes">⬇ Modèle CSV</a>
            </div>
        </form>
    </div>

    <!-- Balance d'ouverture N-1 -->
    <div class="imp-card">
        <div class="imp-card-head">
            <div class="imp-ico gold"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg></div>
            <h3>Balance d'ouverture N-1</h3>
        </div>
        <p class="desc">Crée une écriture « Report à nouveau » <b>en brouillon</b> (journal OD), à valider ensuite. Les comptes manquants sont créés. La balance doit être équilibrée.</p>
        <div class="imp-cols"><b>Colonnes :</b> numero, intitule, debit, credit</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/balance" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <div style="margin-bottom:12px">
                <label style="font-size:13.5px;color:var(--text-muted)">Exercice cible
                    <input type="number" name="exercice" value="<?= (int)$exercice ?>" min="2000" max="2100"
                           style="width:90px;padding:7px 10px;border:1px solid var(--border);border-radius:8px;margin-left:6px;font-size:14px">
                </label>
            </div>
            <div class="imp-file"><input type="file" name="fichier" accept=".csv,.xlsx,.xls,.txt" required></div>
            <div class="imp-actions">
                <button type="submit" class="imp-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Importer
                </button>
                <a class="imp-modele" href="<?= APP_URL ?>/dossier/import/modele?id=<?= $entreprise['id'] ?>&type=balance">⬇ Modèle CSV</a>
            </div>
            <div class="imp-warn">⚠ Refusé si un report à nouveau existe déjà pour cet exercice.</div>
        </form>
    </div>

</div>
