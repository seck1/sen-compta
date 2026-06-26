<style>
.sa-flash { padding: 13px 18px; border-radius: 11px; margin-bottom: 18px; font-size: 14px; font-weight: 500; }
.sa-flash.ok  { background: rgba(31,110,78,0.10); color: #1f6e4e; border: 1px solid rgba(31,110,78,0.25); }
.sa-flash.err { background: rgba(192,57,43,0.08); color: #c0392b; border: 1px solid rgba(192,57,43,0.25); }
.sa-layout { display: grid; grid-template-columns: 320px 1fr; gap: 20px; align-items: start; }
@media (max-width: 900px){ .sa-layout { grid-template-columns: 1fr; } }
.sa-card { background:#fff; border:1px solid var(--border); border-radius:14px; padding:22px; }
.sa-card h3 { font-size:16px; font-weight:700; color:var(--navy-dark); margin-bottom:14px; }
.sa-field { margin-bottom:13px; }
.sa-field label { display:block; font-size:12.5px; font-weight:600; color:var(--text-muted); margin-bottom:5px; }
.sa-field input, .sa-field textarea {
    width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:9px;
    font-size:14px; font-family:inherit;
}
.sa-field textarea { resize:vertical; min-height:54px; }
.sa-btn { display:inline-flex; align-items:center; gap:7px; border:none; cursor:pointer;
    background:linear-gradient(135deg,#2a8a63,#1f6e4e); color:#fff;
    padding:10px 18px; border-radius:9px; font-size:14px; font-weight:600; font-family:inherit; }
.sa-btn:hover { filter:brightness(1.05); }
.sa-empty { text-align:center; padding:40px 20px; color:var(--text-muted); }
.sa-table { width:100%; border-collapse:collapse; }
.sa-table th { text-align:left; padding:10px 12px; font-size:8.5pt; text-transform:uppercase; letter-spacing:.5px; color:#fff; background:var(--green); }
.sa-table td { padding:11px 12px; border-bottom:1px solid var(--border); font-size:14px; }
.sa-code { font-family:monospace; font-weight:700; color:var(--navy-dark); }
.sa-pill { display:inline-block; font-size:12px; padding:2px 9px; border-radius:20px; background:rgba(31,110,78,0.1); color:#1f6e4e; font-weight:600; }
.sa-act { display:inline-flex; gap:6px; }
.sa-act a, .sa-act button { font-size:13px; padding:4px 10px; border-radius:7px; cursor:pointer; text-decoration:none; border:1px solid var(--border); background:#fff; color:var(--text); font-family:inherit; }
.sa-act .del { color:#c0392b; border-color:rgba(192,57,43,.25); }
</style>

<div class="page-header" style="margin-bottom:16px">
    <div>
        <h1 class="page-title">Sections analytiques</h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> · Centres de coûts / activités / projets</p>
    </div>
</div>

<!-- Aide : à quoi ça sert -->
<details class="sa-help" open>
    <summary>💡 À quoi servent les sections analytiques ?</summary>
    <div class="sa-help-body">
        <p>Une <strong>section analytique</strong> est une « étiquette » (un service, un chantier, un projet, une boutique…) que vous posez sur vos charges et produits pour savoir <strong>quelle activité vous coûte ou vous rapporte de l'argent</strong>.</p>
        <p style="margin-top:8px"><strong>Comment l'utiliser, en 3 étapes :</strong></p>
        <ol class="sa-steps">
            <li><strong>Créez vos sections</strong> ici (ex. code <code>AUDIT</code> → « Service Audit »).</li>
            <li>Lors de la <strong>saisie d'une écriture</strong>, choisissez la section dans la colonne « Section » sur les lignes de charges (classe 6) et de produits (classe 7).</li>
            <li>Consultez le <a href="<?= APP_URL ?>/dossier/rapport-analytique?id=<?= $entreprise['id'] ?>"><strong>Rapport analytique</strong></a> pour voir la rentabilité (Produits − Charges) de chaque section.</li>
        </ol>
        <p style="margin-top:8px;font-size:12.5px;color:var(--text-muted)">La colonne « Lignes ventilées » indique combien de lignes d'écriture sont déjà affectées à chaque section. La ventilation est <strong>optionnelle</strong> : une écriture sans section reste valide.</p>
    </div>
</details>
<style>
.sa-help { background:#fff; border:1px solid var(--border); border-left:4px solid var(--green); border-radius:12px; padding:14px 18px; margin-bottom:20px; }
.sa-help summary { cursor:pointer; font-weight:700; font-size:14px; color:var(--navy-dark); list-style:none; }
.sa-help summary::-webkit-details-marker { display:none; }
.sa-help-body { margin-top:12px; font-size:13.5px; color:var(--text); line-height:1.6; }
.sa-help-body code { background:rgba(31,110,78,.1); color:#1f6e4e; padding:1px 6px; border-radius:5px; font-size:12.5px; }
.sa-help-body a { color:#1f6e4e; font-weight:600; }
.sa-steps { margin:6px 0 0 20px; } .sa-steps li { margin-bottom:5px; }
</style>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="sa-flash ok"><?= e($_SESSION['flash_success']) ?></div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="sa-flash err"><?= e($_SESSION['flash_error']) ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="sa-layout">

    <!-- Formulaire création / édition -->
    <div class="sa-card">
        <h3><?= $edit ? 'Modifier la section' : 'Nouvelle section' ?></h3>
        <form method="POST" action="<?= APP_URL ?>/dossier/sections-analytiques/store">
            <?= csrfField() ?>
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="section_id" value="<?= $edit['id'] ?? '' ?>">
            <div class="sa-field">
                <label>Code <span style="color:#c0392b">*</span></label>
                <input type="text" name="code" maxlength="20" placeholder="ex. AUDIT, CHANTIER-01" value="<?= e($edit['code'] ?? '') ?>" required>
            </div>
            <div class="sa-field">
                <label>Libellé <span style="color:#c0392b">*</span></label>
                <input type="text" name="libelle" maxlength="150" placeholder="ex. Service Audit" value="<?= e($edit['libelle'] ?? '') ?>" required>
            </div>
            <div class="sa-field">
                <label>Description</label>
                <textarea name="description" placeholder="Optionnel"><?= e($edit['description'] ?? '') ?></textarea>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <button type="submit" class="sa-btn"><?= $edit ? 'Enregistrer' : 'Créer la section' ?></button>
                <?php if ($edit): ?>
                <a href="<?= APP_URL ?>/dossier/sections-analytiques?id=<?= $entreprise['id'] ?>" style="font-size:13px;color:var(--text-muted)">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Liste -->
    <div class="sa-card" style="padding:0;overflow:hidden">
        <?php if (empty($sections)): ?>
        <div class="sa-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" style="opacity:.4;margin-bottom:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5"/></svg>
            <div style="font-weight:600">Aucune section analytique</div>
            <div style="font-size:13px;margin-top:4px">Créez vos premières sections (services, chantiers, projets…) pour ventiler vos charges et produits.</div>
        </div>
        <?php else: ?>
        <table class="sa-table">
            <thead><tr><th>Code</th><th>Libellé</th><th style="text-align:center">Lignes ventilées</th><th style="text-align:right">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($sections as $s): ?>
            <tr>
                <td><span class="sa-code"><?= e($s['code']) ?></span></td>
                <td>
                    <?= e($s['libelle']) ?>
                    <?php if (!empty($s['description'])): ?>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= e($s['description']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="text-align:center"><span class="sa-pill"><?= (int)$s['nb_lignes'] ?></span></td>
                <td style="text-align:right">
                    <div class="sa-act">
                        <a href="<?= APP_URL ?>/dossier/sections-analytiques?id=<?= $entreprise['id'] ?>&edit=<?= $s['id'] ?>">Modifier</a>
                        <form method="POST" action="<?= APP_URL ?>/dossier/sections-analytiques/supprimer" style="display:inline" onsubmit="return confirm('Archiver cette section ?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
                            <input type="hidden" name="section_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="del">Archiver</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
