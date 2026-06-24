<?php
$isEdit = !empty($prospect);
$stageOptions = ['nouveau'=>'Nouveau','qualifie'=>'Qualifié','devis_envoye'=>'Devis envoyé','negociation'=>'Négociation','client'=>'Client','perdu'=>'Perdu'];
$sourceOptions = ['recommandation'=>'Recommandation','site_web'=>'Site web','reseau'=>'Réseau','prospection'=>'Prospection directe','autre'=>'Autre'];
?>
<style>
.form-root { padding:32px 36px;max-width:900px; }
.form-root h1 { font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--navy-dark);margin-bottom:6px; }
.form-root .sub { font-size:13px;color:var(--text-muted);margin-bottom:28px; }
.form-card { background:#fff;border-radius:16px;border:1px solid var(--border);padding:28px;margin-bottom:20px; }
.form-section-title { font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid var(--gold);display:inline-block; }
.form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.form-grid-3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px; }
.form-group { display:flex;flex-direction:column;gap:6px; }
.form-group.full { grid-column:1/-1; }
label { font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px; }
input,select,textarea { padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;color:var(--text);font-family:inherit;background:#fff;transition:border-color 0.2s; }
input:focus,select:focus,textarea:focus { outline:none;border-color:var(--navy); }
textarea { resize:vertical;min-height:80px; }
.form-actions { display:flex;gap:12px;align-items:center;margin-top:24px; }
.btn { display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-primary { background:var(--navy);color:#fff; }
.btn-primary:hover { background:var(--navy-light); }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-outline { background:transparent;color:var(--text-muted);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);color:var(--navy); }
</style>

<div class="form-root">
    <h1><?= $isEdit ? 'Modifier prospect' : 'Nouveau prospect' ?></h1>
    <p class="sub"><?= $isEdit ? e($prospect['reference']) . ' · ' . e($prospect['raison_sociale']) : 'Ajouter un prospect ou client au pipeline commercial' ?></p>

    <form method="POST" action="<?= APP_URL ?>/commercial/prospects/store">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $prospect['id'] ?>"><?php endif; ?>

        <!-- Informations société -->
        <div class="form-card">
            <div class="form-section-title">Informations société</div>
            <div class="form-grid">
                <div class="form-group full">
                    <label>Raison sociale *</label>
                    <input type="text" name="raison_sociale" value="<?= e($prospect['raison_sociale'] ?? '') ?>" required placeholder="Nom de l'entreprise">
                </div>
                <div class="form-group">
                    <label>Forme juridique</label>
                    <select name="forme_juridique">
                        <?php foreach (['SA','SARL','SAS','GIE','EI','SUARL','Autre','Particulier'] as $fj): ?>
                        <option value="<?= $fj ?>" <?= ($prospect['forme_juridique'] ?? 'Autre') === $fj ? 'selected' : '' ?>><?= $fj ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Secteur d'activité</label>
                    <input type="text" name="secteur" value="<?= e($prospect['secteur'] ?? '') ?>" placeholder="Ex: Agriculture, BTP...">
                </div>
                <div class="form-group">
                    <label>NINEA</label>
                    <input type="text" name="ninea" value="<?= e($prospect['ninea'] ?? '') ?>" placeholder="Numéro d'identification">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?= e($prospect['telephone'] ?? '') ?>" placeholder="77 000 00 00">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($prospect['email'] ?? '') ?>" placeholder="contact@entreprise.sn">
                </div>
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="ville" value="<?= e($prospect['ville'] ?? 'Dakar') ?>">
                </div>
                <div class="form-group">
                    <label>Site web</label>
                    <input type="text" name="site_web" value="<?= e($prospect['site_web'] ?? '') ?>" placeholder="www.exemple.sn">
                </div>
                <div class="form-group full">
                    <label>Adresse</label>
                    <textarea name="adresse" rows="2"><?= e($prospect['adresse'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Contact principal -->
        <div class="form-card">
            <div class="form-section-title">Contact principal</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="contact_nom" value="<?= e($prospect['contact_nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="contact_prenom" value="<?= e($prospect['contact_prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Poste</label>
                    <input type="text" name="contact_poste" value="<?= e($prospect['contact_poste'] ?? '') ?>" placeholder="DG, DAF, Gérant...">
                </div>
                <div class="form-group">
                    <label>Téléphone direct</label>
                    <input type="text" name="contact_telephone" value="<?= e($prospect['contact_telephone'] ?? '') ?>">
                </div>
                <div class="form-group full">
                    <label>Email direct</label>
                    <input type="email" name="contact_email" value="<?= e($prospect['contact_email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Pipeline & Commercial -->
        <div class="form-card">
            <div class="form-section-title">Pipeline & Commercial</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type_contact">
                        <option value="prospect" <?= ($prospect['type_contact'] ?? 'prospect') === 'prospect' ? 'selected' : '' ?>>Prospect</option>
                        <option value="client" <?= ($prospect['type_contact'] ?? '') === 'client' ? 'selected' : '' ?>>Client</option>
                        <option value="ancien_client" <?= ($prospect['type_contact'] ?? '') === 'ancien_client' ? 'selected' : '' ?>>Ancien client</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Étape pipeline</label>
                    <select name="pipeline_stage">
                        <?php foreach ($stageOptions as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($prospect['pipeline_stage'] ?? 'nouveau') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Source</label>
                    <select name="source">
                        <?php foreach ($sourceOptions as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($prospect['source'] ?? 'autre') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>CA potentiel (FCFA)</label>
                    <input type="number" name="ca_potentiel" value="<?= $prospect['ca_potentiel'] ?? 0 ?>" min="0" step="1000">
                </div>
                <div class="form-group full">
                    <label>Notes internes</label>
                    <textarea name="notes" rows="3" placeholder="Observations, historique, contexte..."><?= e($prospect['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le prospect' ?>
            </button>
            <a href="<?= APP_URL ?>/commercial/prospects" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
