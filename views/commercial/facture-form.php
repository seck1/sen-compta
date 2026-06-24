<?php $isEdit = !empty($facture); ?>
<style>
.ff-root { padding:32px 36px;max-width:900px; }
.ff-root h1 { font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--navy-dark);margin-bottom:6px; }
.ff-root .sub { font-size:13px;color:var(--text-muted);margin-bottom:28px; }
.form-card { background:#fff;border-radius:16px;border:1px solid var(--border);padding:28px;margin-bottom:20px; }
.form-section-title { font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid var(--gold);display:inline-block; }
.form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.form-group { display:flex;flex-direction:column;gap:6px; }
.form-group.full { grid-column:1/-1; }
label { font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px; }
input,select,textarea { padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;color:var(--text);font-family:inherit;background:#fff;transition:border-color 0.2s; }
input:focus,select:focus,textarea:focus { outline:none;border-color:var(--navy); }
textarea { resize:vertical;min-height:70px; }
.lines-header { display:grid;grid-template-columns:3fr 1fr 1.5fr 1fr 1.5fr 40px;gap:8px;padding:0 0 8px;border-bottom:2px solid var(--border);margin-bottom:8px; }
.lines-header span { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted); }
.line-row { display:grid;grid-template-columns:3fr 1fr 1.5fr 1fr 1.5fr 40px;gap:8px;align-items:start;padding:8px 0;border-bottom:1px solid #f8fafc; }
.line-row input { padding:8px 10px;font-size:13px; }
.line-total { padding:9px 12px;background:#f8fafc;border-radius:8px;font-size:13px;font-weight:600;color:var(--navy-dark);border:1.5px solid transparent; }
.btn-del-line { width:32px;height:36px;background:#fee2e2;color:#ef4444;border:none;border-radius:8px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center; }
.btn-del-line:hover { background:#ef4444;color:#fff; }
.catalogue-quick { display:flex;align-items:center;gap:8px;margin-bottom:16px; }
.catalogue-quick select { flex:1;padding:8px 12px;font-size:13px; }
.btn-add-cat { padding:8px 16px;background:var(--navy);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer; }
.totaux-panel { display:flex;justify-content:flex-end;margin-top:20px; }
.totaux-box { min-width:280px;background:#f8fafc;border-radius:12px;padding:16px 20px; }
.totaux-row { display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px; }
.totaux-row .lbl { color:var(--text-muted); }
.totaux-row.total-final { font-size:17px;font-weight:800;color:var(--navy-dark);margin-top:8px;padding-top:8px;border-top:2px solid var(--navy-dark); }
.totaux-row.total-final .val { color:var(--gold); }
.btn { display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-outline { background:transparent;color:var(--text-muted);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);color:var(--navy); }
.form-actions { display:flex;gap:12px;align-items:center;margin-top:24px; }
</style>

<div class="ff-root">
    <h1><?= $isEdit ? 'Modifier facture' : 'Nouvelle facture' ?></h1>
    <p class="sub"><?= $isEdit ? e($facture['reference']) . ' · ' . e($facture['prospect_nom'] ?? '') : 'Créer une nouvelle facture de prestation' ?></p>

    <form method="POST" action="<?= APP_URL ?>/commercial/factures/store">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $facture['id'] ?>"><?php endif; ?>

        <div class="form-card">
            <div class="form-section-title">En-tête facture</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Client / Prospect *</label>
                    <select name="prospect_id" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($prospects as $pr): ?>
                        <option value="<?= $pr['id'] ?>" <?= ($facture['prospect_id'] ?? '') == $pr['id'] ? 'selected' : '' ?>>
                            <?= e($pr['raison_sociale']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut">
                        <option value="brouillon" <?= ($facture['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="envoyee" <?= ($facture['statut'] ?? '') === 'envoyee' ? 'selected' : '' ?>>Envoyée</option>
                        <option value="payee" <?= ($facture['statut'] ?? '') === 'payee' ? 'selected' : '' ?>>Payée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date facture *</label>
                    <input type="date" name="date_facture" value="<?= $facture['date_facture'] ?? date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Date d'échéance</label>
                    <input type="date" name="date_echeance" value="<?= $facture['date_echeance'] ?? date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
                <div class="form-group full">
                    <label>Objet *</label>
                    <input type="text" name="objet" value="<?= e($facture['objet'] ?? '') ?>" required placeholder="Mission d'expertise comptable — exercice 2024">
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-section-title">Prestations</div>
            <div class="catalogue-quick">
                <select id="catSelect">
                    <option value="">+ Ajouter depuis catalogue</option>
                    <?php foreach ($catalogue as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        data-designation="<?= htmlspecialchars($cat['designation'], ENT_QUOTES) ?>"
                        data-prix="<?= (int)$cat['prix_unitaire'] ?>"
                        data-description="<?= htmlspecialchars($cat['description'] ?? '', ENT_QUOTES) ?>">
                        <?= e($cat['designation']) ?> — <?= number_format($cat['prix_unitaire'], 0, ',', ' ') ?> F
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn-add-cat" id="btnAddFromCat">Ajouter</button>
                <button type="button" class="btn-add-cat" style="background:var(--gold);color:var(--navy-dark)" id="btnAddLine">+ Ligne vide</button>
            </div>
            <div class="lines-header">
                <span>Désignation</span><span>Qté</span><span>Prix U. HT</span><span>Remise %</span><span>Montant HT</span><span></span>
            </div>
            <div id="linesContainer">
                <?php
                $lignesExist = !empty($lignes) ? $lignes : [['designation'=>'','description'=>'','quantite'=>1,'prix_unitaire'=>0,'remise'=>0,'montant_ht'=>0]];
                foreach ($lignesExist as $idx => $l):
                ?>
                <div class="line-row">
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <input type="text" name="lignes[<?= $idx ?>][designation]" value="<?= e($l['designation']) ?>" placeholder="Désignation" required>
                        <input type="text" name="lignes[<?= $idx ?>][description]" value="<?= e($l['description'] ?? '') ?>" placeholder="Description (optionnel)" style="font-size:12px">
                    </div>
                    <input type="number" name="lignes[<?= $idx ?>][quantite]" value="<?= $l['quantite'] ?? 1 ?>" min="0.01" step="0.01" class="line-qty">
                    <input type="number" name="lignes[<?= $idx ?>][prix_unitaire]" value="<?= $l['prix_unitaire'] ?? 0 ?>" min="0" step="100" class="line-pu">
                    <input type="number" name="lignes[<?= $idx ?>][remise]" value="<?= $l['remise'] ?? 0 ?>" min="0" max="100" step="0.1" class="line-rem">
                    <div class="line-total" data-total><?= number_format($l['montant_ht'] ?? 0, 0, ',', ' ') ?> F</div>
                    <button type="button" class="btn-del-line" onclick="this.closest('.line-row').remove();calcTotaux()">×</button>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="totaux-panel">
                <div class="totaux-box">
                    <div class="totaux-row"><span class="lbl">Sous-total HT</span><span id="tHT">0 F</span></div>
                    <div class="totaux-row">
                        <span class="lbl">Remise globale</span>
                        <span style="display:flex;align-items:center;gap:6px">
                            <input type="number" name="remise_globale" id="remiseGlobale" value="<?= $facture['remise_globale'] ?? 0 ?>" min="0" max="100" step="0.5" style="width:60px;padding:4px 8px;font-size:12px"> %
                        </span>
                    </div>
                    <div class="totaux-row">
                        <span class="lbl">TVA</span>
                        <span style="display:flex;align-items:center;gap:6px">
                            <input type="number" name="taux_tva" id="tauxTva" value="<?= $facture['taux_tva'] ?? 18 ?>" min="0" max="100" step="0.5" style="width:60px;padding:4px 8px;font-size:12px"> %
                        </span>
                    </div>
                    <div class="totaux-row"><span class="lbl">Montant TVA</span><span id="tTVA">0 F</span></div>
                    <div class="totaux-row total-final"><span class="lbl">Total TTC</span><span class="val" id="tTTC">0 F</span></div>
                    <input type="hidden" name="montant_ht" id="hiddenHT" value="0">
                    <input type="hidden" name="montant_tva" id="hiddenTVA" value="0">
                    <input type="hidden" name="montant_ttc" id="hiddenTTC" value="0">
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-section-title">Conditions & Notes</div>
            <div class="form-grid">
                <div class="form-group full">
                    <label>Conditions de paiement</label>
                    <input type="text" name="conditions_paiement" value="<?= e($facture['conditions_paiement'] ?? 'Paiement à 30 jours') ?>">
                </div>
                <div class="form-group full">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"><?= e($facture['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                <?= $isEdit ? 'Enregistrer les modifications' : 'Créer la facture' ?>
            </button>
            <a href="<?= APP_URL ?>/commercial/factures" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<script>
let lineIdx = <?= count($lignesExist) ?>;

function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' F';
}

function calcLine(row) {
    const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
    const pu  = parseFloat(row.querySelector('.line-pu').value)  || 0;
    const rem = parseFloat(row.querySelector('.line-rem').value) || 0;
    const mt  = qty * pu * (1 - rem / 100);
    row.querySelector('[data-total]').textContent = fmt(mt);
    return mt;
}

function calcTotaux() {
    let sumHT = 0;
    document.querySelectorAll('.line-row').forEach(row => { sumHT += calcLine(row); });
    const remG = parseFloat(document.getElementById('remiseGlobale').value) || 0;
    const htNet = sumHT * (1 - remG / 100);
    const tva  = parseFloat(document.getElementById('tauxTva').value) || 0;
    const tvaM = htNet * tva / 100;
    const ttc  = htNet + tvaM;
    document.getElementById('tHT').textContent   = fmt(htNet);
    document.getElementById('tTVA').textContent  = fmt(tvaM);
    document.getElementById('tTTC').textContent  = fmt(ttc);
    document.getElementById('hiddenHT').value  = Math.round(htNet);
    document.getElementById('hiddenTVA').value = Math.round(tvaM);
    document.getElementById('hiddenTTC').value = Math.round(ttc);
}

function addLine(designation, description, prix) {
    const c = document.getElementById('linesContainer');
    const div = document.createElement('div');
    div.className = 'line-row';

    const descWrap = document.createElement('div');
    descWrap.style.cssText = 'display:flex;flex-direction:column;gap:4px';

    const inpDesig = document.createElement('input');
    inpDesig.type = 'text';
    inpDesig.name = 'lignes[' + lineIdx + '][designation]';
    inpDesig.value = designation || '';
    inpDesig.placeholder = 'Désignation';
    inpDesig.required = true;

    const inpDesc = document.createElement('input');
    inpDesc.type = 'text';
    inpDesc.name = 'lignes[' + lineIdx + '][description]';
    inpDesc.value = description || '';
    inpDesc.placeholder = 'Description (optionnel)';
    inpDesc.style.fontSize = '12px';

    descWrap.appendChild(inpDesig);
    descWrap.appendChild(inpDesc);

    const inpQty = document.createElement('input');
    inpQty.type = 'number'; inpQty.name = 'lignes[' + lineIdx + '][quantite]';
    inpQty.value = '1'; inpQty.min = '0.01'; inpQty.step = '0.01';
    inpQty.className = 'line-qty';

    const inpPu = document.createElement('input');
    inpPu.type = 'number'; inpPu.name = 'lignes[' + lineIdx + '][prix_unitaire]';
    inpPu.value = String(prix || 0); inpPu.min = '0'; inpPu.step = '100';
    inpPu.className = 'line-pu';

    const inpRem = document.createElement('input');
    inpRem.type = 'number'; inpRem.name = 'lignes[' + lineIdx + '][remise]';
    inpRem.value = '0'; inpRem.min = '0'; inpRem.max = '100'; inpRem.step = '0.1';
    inpRem.className = 'line-rem';

    const totalDiv = document.createElement('div');
    totalDiv.className = 'line-total';
    totalDiv.dataset.total = '';
    totalDiv.textContent = '0 F';

    const delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.className = 'btn-del-line';
    delBtn.textContent = '×';
    delBtn.addEventListener('click', function() {
        div.remove();
        calcTotaux();
    });

    div.appendChild(descWrap);
    div.appendChild(inpQty);
    div.appendChild(inpPu);
    div.appendChild(inpRem);
    div.appendChild(totalDiv);
    div.appendChild(delBtn);

    [inpQty, inpPu, inpRem].forEach(i => i.addEventListener('input', calcTotaux));

    c.appendChild(div);
    lineIdx++;
    calcTotaux();
}

document.getElementById('btnAddLine').addEventListener('click', function() { addLine('', '', 0); });

document.getElementById('btnAddFromCat').addEventListener('click', function() {
    const sel = document.getElementById('catSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    addLine(opt.dataset.designation, opt.dataset.description, parseInt(opt.dataset.prix, 10));
    sel.selectedIndex = 0;
});

document.getElementById('remiseGlobale').addEventListener('input', calcTotaux);
document.getElementById('tauxTva').addEventListener('input', calcTotaux);
document.querySelectorAll('.line-row input').forEach(function(i) { i.addEventListener('input', calcTotaux); });
calcTotaux();
</script>
