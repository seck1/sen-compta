<style>
.cat-root { padding:32px 36px;max-width:1000px; }
.cat-root h1 { font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--navy-dark);margin-bottom:6px; }
.cat-root .sub { font-size:13px;color:var(--text-muted);margin-bottom:28px; }

.cat-layout { display:grid;grid-template-columns:1fr 380px;gap:24px; }

/* List */
.cat-list { }
.cat-item { background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin-bottom:12px;display:flex;align-items:flex-start;gap:16px;transition:all 0.2s; }
.cat-item:hover { border-color:#cbd5e1;box-shadow:0 4px 12px rgba(30,58,95,0.08); }
.cat-item-icon { width:42px;height:42px;border-radius:10px;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px; }
.cat-item-body { flex:1; }
.cat-item-name { font-size:15px;font-weight:700;color:var(--navy-dark);margin-bottom:4px; }
.cat-item-desc { font-size:12px;color:var(--text-muted);margin-bottom:8px; }
.cat-item-meta { display:flex;gap:12px;align-items:center; }
.cat-price { font-size:16px;font-weight:800;color:var(--gold-dark); }
.cat-unit { font-size:11px;color:var(--text-muted);background:#f8fafc;padding:2px 8px;border-radius:6px; }
.cat-category { font-size:11px;color:var(--navy);background:#dbeafe;padding:2px 8px;border-radius:6px; }
.cat-actions { display:flex;gap:6px;margin-top:10px; }

/* Form panel */
.cat-form-panel { background:#fff;border:1px solid var(--border);border-radius:16px;padding:24px;position:sticky;top:24px; }
.fp-title { font-size:16px;font-weight:700;color:var(--navy-dark);margin-bottom:4px; }
.fp-sub { font-size:12px;color:var(--text-muted);margin-bottom:20px; }
.form-group { display:flex;flex-direction:column;gap:6px;margin-bottom:14px; }
label { font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px; }
input,select,textarea { padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;color:var(--text);font-family:inherit;background:#fff;transition:border-color 0.2s; }
input:focus,select:focus,textarea:focus { outline:none;border-color:var(--navy); }
textarea { resize:vertical;min-height:70px; }

.btn { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-outline { background:transparent;color:var(--navy);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);background:#f8fafc; }
.btn-danger { background:#fee2e2;color:#dc2626;border:none; }
.btn-danger:hover { background:#ef4444;color:#fff; }
.btn-sm { padding:6px 12px;font-size:12px; }

.empty-state { text-align:center;padding:48px 20px;color:var(--text-muted);background:#f8fafc;border-radius:14px; }
</style>

<div class="cat-root">
    <h1>Catalogue des prestations</h1>
    <p class="sub"><?= count($catalogue) ?> prestation(s) · Gérez vos services et tarifs</p>

    <div class="cat-layout">
        <!-- Liste -->
        <div class="cat-list">
            <?php if (empty($catalogue)): ?>
            <div class="empty-state">
                <div style="font-size:40px;margin-bottom:10px">📋</div>
                <div style="font-size:16px;font-weight:600;color:var(--navy-dark);margin-bottom:6px">Catalogue vide</div>
                <div>Ajoutez vos premières prestations pour les utiliser dans les devis et factures</div>
            </div>
            <?php else: ?>
            <?php foreach ($catalogue as $c): ?>
            <div class="cat-item">
                <div class="cat-item-icon">📌</div>
                <div class="cat-item-body">
                    <div class="cat-item-name"><?= e($c['designation']) ?></div>
                    <?php if ($c['description']): ?>
                    <div class="cat-item-desc"><?= e(substr($c['description'], 0, 100)) ?><?= strlen($c['description']) > 100 ? '…' : '' ?></div>
                    <?php endif; ?>
                    <div class="cat-item-meta">
                        <span class="cat-price"><?= number_format($c['prix_unitaire'], 0, ',', ' ') ?> F</span>
                        <span class="cat-unit"><?= e($c['unite'] ?? 'forfait') ?></span>
                        <?php if ($c['categorie']): ?><span class="cat-category"><?= e($c['categorie']) ?></span><?php endif; ?>
                    </div>
                    <div class="cat-actions">
                        <button type="button" class="btn btn-outline btn-sm" onclick="editItem(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)">Modifier</button>
                        <form method="POST" action="<?= APP_URL ?>/commercial/catalogue/store" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette prestation ?')">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Panel form -->
        <div class="cat-form-panel">
            <div class="fp-title" id="fpTitle">Nouvelle prestation</div>
            <div class="fp-sub">Ajoutez une prestation à votre catalogue</div>

            <form method="POST" action="<?= APP_URL ?>/commercial/catalogue/store" id="catForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="_action" value="save">
                <input type="hidden" name="id" id="fpId" value="">

                <div class="form-group">
                    <label>Désignation *</label>
                    <input type="text" name="designation" id="fpDesig" required placeholder="Ex: Tenue de comptabilité mensuelle">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="fpDesc" rows="3" placeholder="Détail de la prestation..."></textarea>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="categorie" id="fpCat">
                        <option value="">— Aucune —</option>
                        <option value="Comptabilité">Comptabilité</option>
                        <option value="Fiscalité">Fiscalité</option>
                        <option value="Juridique">Juridique</option>
                        <option value="Conseil">Conseil</option>
                        <option value="Social">Social &amp; Paie</option>
                        <option value="Audit">Audit</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label>Prix HT (FCFA) *</label>
                        <input type="number" name="prix_unitaire" id="fpPrix" required min="0" step="500" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Unité</label>
                        <select name="unite" id="fpUnite">
                            <option value="forfait">Forfait</option>
                            <option value="heure">Heure</option>
                            <option value="mois">Mois</option>
                            <option value="dossier">Dossier</option>
                            <option value="journee">Journée</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;margin-top:8px" id="fpSubmit">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Ajouter au catalogue
                </button>
                <button type="button" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:8px;display:none" id="fpCancel" onclick="resetForm()">Annuler</button>
            </form>
        </div>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('fpTitle').textContent = 'Modifier prestation';
    document.getElementById('fpId').value = item.id;
    document.getElementById('fpDesig').value = item.designation || '';
    document.getElementById('fpDesc').value = item.description || '';
    document.getElementById('fpCat').value = item.categorie || '';
    document.getElementById('fpPrix').value = item.prix_unitaire || '';
    document.getElementById('fpUnite').value = item.unite || 'forfait';
    document.getElementById('fpSubmit').textContent = 'Enregistrer les modifications';
    document.getElementById('fpCancel').style.display = '';
    document.getElementById('fpDesig').focus();
    document.querySelector('.cat-form-panel').scrollIntoView({behavior:'smooth'});
}

function resetForm() {
    document.getElementById('catForm').reset();
    document.getElementById('fpId').value = '';
    document.getElementById('fpTitle').textContent = 'Nouvelle prestation';
    document.getElementById('fpSubmit').textContent = 'Ajouter au catalogue';
    document.getElementById('fpCancel').style.display = 'none';
}
</script>
