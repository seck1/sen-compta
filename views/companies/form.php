<?php $editMode = $editMode ?? false; ?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><?= $editMode ? 'Modifier le dossier' : 'Nouveau dossier entreprise' ?></h1>
        <p class="page-subtitle"><?= $editMode ? e($entreprise['raison_sociale']) : 'Créer un nouveau dossier comptable' ?></p>
    </div>
    <a href="<?= APP_URL ?>/entreprises" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Retour
    </a>
</div>

<?php if ($error ?? null): ?>
<div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:14px 18px;color:var(--danger);margin-bottom:20px;font-size:14px">
    <?= e($error) ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="<?= APP_URL ?>/entreprises/<?= $editMode ? 'update' : 'store' ?>" enctype="multipart/form-data">
        <?= csrfField() ?>
        <?php if ($editMode): ?>
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
        <?php endif; ?>

        <div style="margin-bottom:28px">
            <h3 style="font-size:13px;font-weight:600;color:var(--navy-dark);margin-bottom:4px">Informations générales</h3>
            <p style="font-size:13px;color:var(--text-muted)">Identité juridique de l'entreprise</p>
        </div>

        <div class="form-grid" style="margin-bottom:24px">
            <?php if (!$editMode): ?>
            <div class="form-field">
                <label>Code dossier <span style="color:var(--danger)">*</span></label>
                <input type="text" name="code_dossier" placeholder="EX: DSS-001" required
                       value="<?= e($_POST['code_dossier'] ?? '') ?>" style="text-transform:uppercase">
            </div>
            <?php endif; ?>

            <div class="form-field" <?= !$editMode ? '' : 'style="grid-column:1/-1"' ?>>
                <label>Raison sociale <span style="color:var(--danger)">*</span></label>
                <input type="text" name="raison_sociale" placeholder="Nom de l'entreprise" required
                       value="<?= e($entreprise['raison_sociale'] ?? $_POST['raison_sociale'] ?? '') ?>">
            </div>

            <div class="form-field">
                <label>Forme juridique</label>
                <select name="forme_juridique">
                    <?php foreach (['SA','SARL','SAS','GIE','EI','SUARL','Autre'] as $fj): ?>
                    <option value="<?= $fj ?>" <?= ($entreprise['forme_juridique'] ?? '') === $fj ? 'selected' : '' ?>><?= $fj ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label>Régime fiscal <span style="color:var(--danger)">*</span></label>
                <select name="regime_fiscal" id="regime_fiscal_select" onchange="toggleRegimeFields()">
                    <?php
                    $rf = $entreprise['regime_fiscal'] ?? 'CGI';
                    $regimes = [
                        'CGI'     => 'Régime Réel Normal (CGI) — CA > 50M FCFA',
                        'CGU'     => 'Contribution Globale Unique (CGU) — CA 5M–50M',
                        'RNS'     => 'Régime Non-Salarié / BNC (professions libérales)',
                        'MICRO'   => 'Micro-entreprise / Impôt Libératoire — CA < 5M',
                        'EXONERE' => 'Régime Exonéré / Zone Franche',
                    ];
                    foreach ($regimes as $val => $lbl):
                    ?>
                    <option value="<?= $val ?>" <?= $rf === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label>Régime TVA</label>
                <select name="regime_tva">
                    <?php
                    $rtva = $entreprise['regime_tva'] ?? 'mensuel';
                    $tvaOptions = ['mensuel' => 'Mensuel', 'trimestriel' => 'Trimestriel', 'annuel' => 'Annuel', 'non_assujetti' => 'Non assujetti'];
                    foreach ($tvaOptions as $val => $lbl):
                    ?>
                    <option value="<?= $val ?>" <?= $rtva === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label>CA annuel estimé (FCFA)</label>
                <input type="number" name="ca_annuel_estime" min="0" step="1000"
                       placeholder="Ex: 25000000"
                       value="<?= e($entreprise['ca_annuel_estime'] ?? 0) ?>">
            </div>

            <div class="form-field">
                <label>Secteur d'activité détaillé</label>
                <select name="secteur_activite_detail">
                    <?php
                    $sad = $entreprise['secteur_activite_detail'] ?? '';
                    foreach (['commerce', 'services', 'industrie', 'artisanat', 'transport', 'btp', 'agriculture', 'profession_liberale', 'autre'] as $s):
                    ?>
                    <option value="<?= $s ?>" <?= $sad === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label>NINEA</label>
                <input type="text" name="ninea" placeholder="Numéro d'identification fiscale"
                       value="<?= e($entreprise['ninea'] ?? '') ?>">
            </div>

            <div class="form-field">
                <label>N° Contribuable</label>
                <input type="text" name="numero_contribuable" placeholder="Numéro contribuable DGI"
                       value="<?= e($entreprise['numero_contribuable'] ?? '') ?>">
            </div>

            <div class="form-field">
                <label>RCCM</label>
                <input type="text" name="rccm" placeholder="Registre du commerce"
                       value="<?= e($entreprise['rccm'] ?? '') ?>">
            </div>

            <div class="form-field">
                <label>N° Registre Commerce</label>
                <input type="text" name="numero_registre_commerce" placeholder="Ex: SN DKR 2024 B 12345"
                       value="<?= e($entreprise['numero_registre_commerce'] ?? '') ?>">
            </div>

            <div class="form-field regime-exonere-field" style="display:none">
                <label>Date début exonération</label>
                <input type="date" name="date_debut_exoneration"
                       value="<?= e($entreprise['date_debut_exoneration'] ?? '') ?>">
            </div>

            <div class="form-field regime-exonere-field" style="display:none">
                <label>Date fin exonération</label>
                <input type="date" name="date_fin_exoneration"
                       value="<?= e($entreprise['date_fin_exoneration'] ?? '') ?>">
            </div>

            <script>
            function toggleRegimeFields() {
                var r = document.getElementById('regime_fiscal_select').value;
                var exFields = document.querySelectorAll('.regime-exonere-field');
                exFields.forEach(function(el) { el.style.display = (r === 'EXONERE') ? '' : 'none'; });
            }
            document.addEventListener('DOMContentLoaded', toggleRegimeFields);
            </script>

            <div class="form-field">
                <label>Secteur d'activité</label>
                <input type="text" name="secteur_activite" placeholder="Ex: Commerce général"
                       value="<?= e($entreprise['secteur_activite'] ?? '') ?>">
            </div>

            <div class="form-field">
                <label>Exercice courant</label>
                <input type="number" name="exercice_courant" min="2000" max="2099"
                       value="<?= e($entreprise['exercice_courant'] ?? date('Y')) ?>">
            </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin-bottom:24px">

        <div style="margin-bottom:20px">
            <h3 style="font-size:13px;font-weight:600;color:var(--navy-dark);margin-bottom:4px">Contact & localisation</h3>
        </div>

        <div class="form-grid" style="margin-bottom:24px">
            <div class="form-field">
                <label>Téléphone</label>
                <input type="text" name="telephone" placeholder="+221 77 000 00 00"
                       value="<?= e($entreprise['telephone'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label>Email</label>
                <input type="email" name="email" placeholder="contact@entreprise.sn"
                       value="<?= e($entreprise['email'] ?? '') ?>">
            </div>
            <div class="form-field" style="grid-column:1/-1">
                <label>Adresse</label>
                <textarea name="adresse" placeholder="Adresse complète"><?= e($entreprise['adresse'] ?? '') ?></textarea>
            </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin-bottom:24px">

        <div style="display:flex;align-items:flex-start;gap:20px;margin-bottom:24px;flex-wrap:wrap">
            <div class="form-field" style="flex:1;min-width:160px">
                <label>Couleur du dossier</label>
                <input type="color" name="couleur" value="<?= e($entreprise['couleur'] ?? '#1e3a5f') ?>"
                       style="width:60px;height:42px;padding:4px;border-radius:8px;cursor:pointer">
            </div>

            <div class="form-field" style="flex:2;min-width:220px">
                <label>Logo de l'entreprise</label>
                <div style="display:flex;align-items:center;gap:16px">
                    <?php if ($editMode && !empty($entreprise['logo'])): ?>
                    <div id="logo-preview-wrap" style="width:72px;height:72px;border-radius:10px;border:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                        <img id="logo-preview" src="<?= APP_URL ?>/logos/<?= e($entreprise['logo']) ?>" style="width:100%;height:100%;object-fit:contain;padding:4px;box-sizing:border-box">
                    </div>
                    <?php else: ?>
                    <div id="logo-preview-wrap" style="width:72px;height:72px;border-radius:10px;border:2px dashed var(--border);background:var(--bg);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                        <img id="logo-preview" src="" style="width:100%;height:100%;object-fit:contain;padding:4px;box-sizing:border-box;display:none">
                        <svg id="logo-placeholder" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:28px;height:28px;color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    </div>
                    <?php endif; ?>
                    <div>
                        <input type="file" name="logo" id="logo-input" accept="image/*" style="display:none" onchange="previewLogo(this)">
                        <button type="button" onclick="document.getElementById('logo-input').click()" class="btn btn-outline btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                            Choisir un logo
                        </button>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:6px">PNG, JPG — max 2 Mo</p>
                        <?php if ($editMode && !empty($entreprise['logo'])): ?>
                        <label style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:5px;margin-top:4px;cursor:pointer">
                            <input type="checkbox" name="supprimer_logo" value="1"> Supprimer le logo actuel
                        </label>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($editMode): ?>
            <div class="form-field" style="flex:1;min-width:160px">
                <label>Statut</label>
                <select name="statut">
                    <option value="actif" <?= ($entreprise['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="suspendu" <?= ($entreprise['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                    <option value="archive" <?= ($entreprise['statut'] ?? '') === 'archive' ? 'selected' : '' ?>>Archivé</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
        <script>
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('logo-preview');
                    var ph  = document.getElementById('logo-placeholder');
                    var wrap = document.getElementById('logo-preview-wrap');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (ph) ph.style.display = 'none';
                    wrap.style.border = '1px solid var(--border)';
                    wrap.style.background = '#fff';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>

        <div class="form-actions">
            <button type="submit" class="btn btn-gold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <?= $editMode ? 'Enregistrer les modifications' : 'Créer le dossier' ?>
            </button>
            <a href="<?= APP_URL ?>/entreprises" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
