<style>
.imp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(330px, 1fr)); gap: 20px; margin-top: 8px; }
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
.imp-ico.teal  { background: linear-gradient(135deg, #3aa6a0, #1f6e6e); }
.imp-ico.blue  { background: linear-gradient(135deg, #3b6ea5, #1e3a5f); }
.imp-ico.gold  { background: linear-gradient(135deg, #d9b876, #a8843f); }
.imp-card h3 { font-size: 17px; font-weight: 700; color: var(--navy-dark); }
.imp-card p.desc { font-size: 13.5px; color: var(--text-muted); line-height: 1.5; margin: 4px 0 14px; }
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

/* Toggle Fusion / Remplacement */
.imp-mode { display: flex; gap: 8px; margin-bottom: 14px; }
.imp-mode input { position: absolute; opacity: 0; pointer-events: none; }
.imp-mode label {
    flex: 1; text-align: center; cursor: pointer; font-size: 13px; font-weight: 600;
    padding: 9px 8px; border: 1.5px solid var(--border); border-radius: 9px;
    color: var(--text-muted); transition: all .15s; user-select: none;
}
.imp-mode label small { display: block; font-size: 10.5px; font-weight: 500; opacity: .8; margin-top: 2px; }
.imp-mode input:checked + label.fusion {
    border-color: #1f6e4e; background: rgba(31,110,78,0.08); color: #1f6e4e;
}
.imp-mode input:checked + label.remplacement {
    border-color: #c0392b; background: rgba(192,57,43,0.07); color: #c0392b;
}
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

<?php
// Bloc réutilisable du toggle Fusion / Remplacement
if (!function_exists('impModeToggle')):
function impModeToggle(string $uid, string $hintRemp): void { ?>
    <div class="imp-mode">
        <input type="radio" id="m-fus-<?= $uid ?>" name="mode" value="fusion" checked>
        <label class="fusion" for="m-fus-<?= $uid ?>">Fusion<small>Ajoute aux données</small></label>
        <input type="radio" id="m-rem-<?= $uid ?>" name="mode" value="remplacement">
        <label class="remplacement" for="m-rem-<?= $uid ?>">Remplacement<small><?= e($hintRemp) ?></small></label>
    </div>
<?php }
endif;
?>

<div class="imp-grid">

    <!-- Clients -->
    <div class="imp-card">
        <div class="imp-card-head">
            <div class="imp-ico green"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg></div>
            <h3>Clients</h3>
        </div>
        <p class="desc">Importez votre liste de clients (comptes 411).</p>
        <div class="imp-cols"><b>Colonnes :</b> nom, ninea, telephone, email, adresse</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/clients" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <?php impModeToggle('cli', 'Archive les clients actuels'); ?>
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

    <!-- Fournisseurs -->
    <div class="imp-card">
        <div class="imp-card-head">
            <div class="imp-ico teal"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" /></svg></div>
            <h3>Fournisseurs</h3>
        </div>
        <p class="desc">Importez votre liste de fournisseurs (comptes 401).</p>
        <div class="imp-cols"><b>Colonnes :</b> nom, ninea, telephone, email, adresse</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/fournisseurs" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <?php impModeToggle('four', 'Archive les fournisseurs actuels'); ?>
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
        <p class="desc">Importez vos comptes SYSCOHADA. Le <b>type</b> est déduit de la classe si absent.</p>
        <div class="imp-cols"><b>Colonnes :</b> numero, intitule, type_compte</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/plan-comptable" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <?php impModeToggle('pc', 'Supprime les comptes inutilisés'); ?>
            <div class="imp-file"><input type="file" name="fichier" accept=".csv,.xlsx,.xls,.txt" required></div>
            <div class="imp-actions">
                <button type="submit" class="imp-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Importer
                </button>
                <a class="imp-modele" href="<?= APP_URL ?>/dossier/import/modele?id=<?= $entreprise['id'] ?>&type=comptes">⬇ Modèle CSV</a>
            </div>
            <div class="imp-warn">En remplacement, les comptes déjà utilisés dans des écritures sont conservés.</div>
        </form>
    </div>

    <!-- Balance d'ouverture N-1 -->
    <div class="imp-card">
        <div class="imp-card-head">
            <div class="imp-ico gold"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg></div>
            <h3>Balance d'ouverture N-1</h3>
        </div>
        <p class="desc">Crée une écriture « Report à nouveau » <b>en brouillon</b> (journal OD), à valider. Comptes manquants créés. Doit être équilibrée.</p>
        <div class="imp-cols"><b>Colonnes :</b> numero, intitule, debit, credit</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/import/balance" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <?php impModeToggle('bal', 'Écrase le report existant'); ?>
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
        </form>
    </div>

</div>
