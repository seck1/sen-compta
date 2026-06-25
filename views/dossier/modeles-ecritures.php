<?php $id = $entreprise['id']; ?>
<style>
.modele-card { background:#fff; border:1px solid var(--border); border-radius:10px; padding:16px 20px; margin-bottom:12px; display:flex; align-items:flex-start; gap:16px; }
.modele-card:hover { border-color:var(--navy-dark); box-shadow:0 2px 8px rgba(30,58,95,.08); }
.modele-icon { width:40px; height:40px; border-radius:8px; background:var(--navy-dark); display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#fff; font-size:18px; }
.modele-info { flex:1 }
.modele-nom { font-weight:700; font-size:17px; color:var(--navy-dark); }
.modele-desc { font-size:15px; color:var(--text-muted); margin-top:2px; }
.modele-meta { font-size:14px; color:var(--text-muted); margin-top:6px; display:flex; gap:12px; }
.badge-journal { background:#f0f3f8; color:var(--navy-dark); font-size:14px; font-weight:700; padding:2px 8px; border-radius:4px; letter-spacing:.5px; }
.ligne-row { display:grid; grid-template-columns:120px 1fr 100px 100px 36px; gap:8px; align-items:center; margin-bottom:8px; }
.ligne-row input { padding:7px 10px; border:1px solid var(--border); border-radius:6px; font-size:16px; width:100%; box-sizing:border-box; }
.btn-add-ligne { background:none; border:1px dashed var(--border); border-radius:6px; padding:8px; width:100%; font-size:16px; color:var(--text-muted); cursor:pointer; margin-top:4px; }
.btn-add-ligne:hover { background:var(--bg); border-color:var(--navy-dark); color:var(--navy-dark); }
.appliquer-modal { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; display:none; align-items:center; justify-content:center; }
.appliquer-modal.open { display:flex; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Modèles d'écritures</div>
        <div class="page-subtitle">Écritures récurrentes pré-remplies en 1 clic</div>
    </div>
    <button onclick="ouvrirFormulaire()" class="btn btn-primary" style="display:flex;align-items:center;gap:6px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nouveau modèle
    </button>
</div>

<?php if(isset($_GET['ok'])): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:16px">Modèle enregistré.</div>
<?php endif; ?>
<?php if(isset($_GET['error'])): ?>
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:16px">Veuillez renseigner un nom et au moins une ligne avec un numéro de compte.</div>
<?php endif; ?>

<?php if(empty($modeles)): ?>
<div class="card" style="padding:56px;text-align:center;color:var(--text-muted)">
    <div style="font-size:40px;margin-bottom:16px">📋</div>
    <div style="font-weight:700;font-size:19px;margin-bottom:8px">Aucun modèle d'écriture</div>
    <div style="font-size:16px;margin-bottom:20px">Créez des modèles pour loyer, salaires, charges sociales… et passez vos écritures en 1 clic.</div>
    <button onclick="ouvrirFormulaire()" class="btn btn-primary">Créer le premier modèle</button>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<?php foreach($modeles as $m):
    $lignes = json_decode($m['lignes'],true) ?: [];
?>
<div class="modele-card">
    <div class="modele-icon">📋</div>
    <div class="modele-info">
        <div class="modele-nom"><?= e($m['nom']) ?></div>
        <?php if($m['description']): ?><div class="modele-desc"><?= e($m['description']) ?></div><?php endif; ?>
        <div class="modele-meta">
            <span class="badge-journal"><?= e($m['journal_code']) ?></span>
            <span><?= count($lignes) ?> ligne(s)</span>
            <span>par <?= e($m['prenom'].' '.$m['nom']) ?></span>
        </div>
        <div style="margin-top:10px;font-size:15px">
        <?php foreach(array_slice($lignes,0,3) as $l): ?>
            <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--navy-dark);font-weight:600"><?= e($l['compte']) ?></span>
                <span style="color:var(--text-muted);flex:1;margin:0 8px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($l['libelle']) ?></span>
                <span style="font-family:monospace;color:#1f6e4e"><?= $l['debit']>0 ? number_format($l['debit'],0,'.',','). ' D' : '' ?></span>
                <span style="font-family:monospace;color:#dc2626;margin-left:8px"><?= $l['credit']>0 ? number_format($l['credit'],0,'.',','). ' C' : '' ?></span>
            </div>
        <?php endforeach; ?>
        <?php if(count($lignes)>3): ?><div style="color:var(--text-muted);font-size:14px;padding-top:4px">+ <?= count($lignes)-3 ?> ligne(s) supplémentaire(s)</div><?php endif; ?>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
        <button onclick="appliquerModele(<?= $m['id'] ?>, '<?= e(addslashes($m['nom'])) ?>')" class="btn btn-primary" style="font-size:15px;padding:6px 12px">⚡ Appliquer</button>
        <button onclick="editerModele(<?= $m['id'] ?>)" class="btn btn-secondary" style="font-size:15px;padding:6px 12px">✏️ Éditer</button>
        <button onclick="supprimerModele(<?= $m['id'] ?>)" style="background:none;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:15px;padding:6px 12px;cursor:pointer">🗑️</button>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal formulaire -->
<div id="modaleForm" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;display:none;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:14px;width:760px;max-width:95vw;max-height:90vh;overflow-y:auto;padding:28px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div style="font-size:18px;font-weight:700;color:var(--navy-dark)" id="formTitre">Nouveau modèle d'écriture</div>
        <button onclick="fermerFormulaire()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted)">×</button>
    </div>
    <form method="post" action="<?= APP_URL ?>/dossier/modeles/store" id="formModele">
        <input type="hidden" name="entreprise_id" value="<?= $id ?>">
        <input type="hidden" name="modele_id" id="modele_id_hidden" value="0">
        <div style="display:grid;grid-template-columns:1fr 1fr 120px;gap:12px;margin-bottom:16px">
            <div>
                <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Nom du modèle *</label>
                <input type="text" name="nom" id="fm_nom" required placeholder="Ex: Loyer mensuel" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:16px;box-sizing:border-box">
            </div>
            <div>
                <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Description</label>
                <input type="text" name="description" id="fm_desc" placeholder="Optionnel" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:16px;box-sizing:border-box">
            </div>
            <div>
                <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Journal</label>
                <input type="text" name="journal_code" id="fm_journal" value="OD" maxlength="5" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:16px;box-sizing:border-box;text-transform:uppercase">
            </div>
        </div>

        <div style="margin-bottom:10px">
            <div style="font-size:15px;font-weight:700;color:var(--navy-dark);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Lignes d'écriture</div>
            <div style="display:grid;grid-template-columns:120px 1fr 100px 100px 36px;gap:8px;margin-bottom:6px">
                <div style="font-size:14px;color:var(--text-muted);font-weight:600">N° Compte</div>
                <div style="font-size:14px;color:var(--text-muted);font-weight:600">Libellé</div>
                <div style="font-size:14px;color:var(--text-muted);font-weight:600;text-align:right">Débit</div>
                <div style="font-size:14px;color:var(--text-muted);font-weight:600;text-align:right">Crédit</div>
                <div></div>
            </div>
            <div id="lignesContainer"></div>
            <button type="button" onclick="ajouterLigne()" class="btn-add-ligne">+ Ajouter une ligne</button>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
            <button type="button" onclick="fermerFormulaire()" class="btn btn-secondary">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
</div>

<!-- Modal appliquer -->
<div class="appliquer-modal" id="modaleAppliquer">
<div style="background:#fff;border-radius:14px;width:420px;padding:28px">
    <div style="font-size:17px;font-weight:700;margin-bottom:6px;color:var(--navy-dark)">Appliquer le modèle</div>
    <div style="font-size:16px;color:var(--text-muted);margin-bottom:20px" id="appliquerNom"></div>
    <div style="margin-bottom:14px">
        <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Date de l'écriture</label>
        <input type="date" id="appliquerDate" value="<?= date('Y-m-d') ?>" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:16px;box-sizing:border-box">
    </div>
    <div style="margin-bottom:20px">
        <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Libellé (optionnel)</label>
        <input type="text" id="appliquerLibelle" placeholder="Laissez vide pour utiliser le nom du modèle" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:16px;box-sizing:border-box">
    </div>
    <div id="appliquerResult" style="display:none;padding:10px;border-radius:6px;font-size:16px;margin-bottom:16px"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
        <button onclick="fermerAppliquer()" class="btn btn-secondary">Annuler</button>
        <button onclick="confirmerAppliquer()" class="btn btn-primary" id="btnConfirmerApp">⚡ Créer l'écriture</button>
    </div>
</div>
</div>

<script>
let ligneIdx = 0;
let appliquerModeleId = 0;

function clearContainer(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
}

function ajouterLigne(c, l, d, cr) {
    const container = document.getElementById('lignesContainer');
    const row = document.createElement('div');
    row.className = 'ligne-row';
    row.dataset.idx = ligneIdx;

    const makeInput = (name, val, ph, align) => {
        const inp = document.createElement('input');
        inp.type = 'text';
        inp.name = name;
        inp.value = val || '';
        inp.placeholder = ph || '';
        if (align) inp.style.textAlign = align;
        return inp;
    };

    const compte = makeInput('lignes[' + ligneIdx + '][compte]', c, '601000');
    const libelle = makeInput('lignes[' + ligneIdx + '][libelle]', l, 'Libellé...');
    const debit = makeInput('lignes[' + ligneIdx + '][debit]', d, '0', 'right');
    const credit = makeInput('lignes[' + ligneIdx + '][credit]', cr, '0', 'right');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = '×';
    btn.style.cssText = 'background:#fef2f2;border:1px solid #fecaca;border-radius:4px;color:#dc2626;cursor:pointer;font-size:16px;width:32px;height:32px;flex-shrink:0';
    btn.onclick = function() { row.remove(); };

    row.appendChild(compte);
    row.appendChild(libelle);
    row.appendChild(debit);
    row.appendChild(credit);
    row.appendChild(btn);
    container.appendChild(row);
    ligneIdx++;
}

function ouvrirFormulaire() {
    document.getElementById('formTitre').textContent = "Nouveau modèle d'écriture";
    document.getElementById('fm_nom').value = '';
    document.getElementById('fm_desc').value = '';
    document.getElementById('fm_journal').value = 'OD';
    document.getElementById('modele_id_hidden').value = '0';
    clearContainer(document.getElementById('lignesContainer'));
    ligneIdx = 0;
    ajouterLigne(); ajouterLigne();
    document.getElementById('modaleForm').style.display = 'flex';
}

function fermerFormulaire() {
    document.getElementById('modaleForm').style.display = 'none';
}

function editerModele(modeleId) {
    fetch('<?= APP_URL ?>/dossier/modeles/json?id=<?= $id ?>&modele_id=' + modeleId)
        .then(function(r) { return r.json(); }).then(function(data) {
            if (!data.ok) return;
            var m = data.modele;
            document.getElementById('formTitre').textContent = "Modifier le modèle";
            document.getElementById('fm_nom').value = m.nom;
            document.getElementById('fm_desc').value = m.description || '';
            document.getElementById('fm_journal').value = m.journal_code || 'OD';
            document.getElementById('modele_id_hidden').value = m.id;
            clearContainer(document.getElementById('lignesContainer'));
            ligneIdx = 0;
            (m.lignes || []).forEach(function(l) {
                ajouterLigne(l.compte, l.libelle, l.debit > 0 ? l.debit : '', l.credit > 0 ? l.credit : '');
            });
            if (!m.lignes || !m.lignes.length) { ajouterLigne(); ajouterLigne(); }
            document.getElementById('modaleForm').style.display = 'flex';
        });
}

function supprimerModele(modeleId) {
    if (!confirm('Supprimer ce modèle ?')) return;
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $id ?>');
    fd.append('modele_id', modeleId);
    fetch('<?= APP_URL ?>/dossier/modeles/supprimer', { method:'POST', body:fd })
        .then(function(r) { return r.json(); }).then(function(d) { if (d.ok) location.reload(); });
}

function appliquerModele(modeleId, nom) {
    appliquerModeleId = modeleId;
    document.getElementById('appliquerNom').textContent = nom;
    document.getElementById('appliquerLibelle').value = '';
    document.getElementById('appliquerResult').style.display = 'none';
    document.getElementById('modaleAppliquer').classList.add('open');
}

function fermerAppliquer() {
    document.getElementById('modaleAppliquer').classList.remove('open');
}

function confirmerAppliquer() {
    var btn = document.getElementById('btnConfirmerApp');
    btn.disabled = true;
    btn.textContent = '...';
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $id ?>');
    fd.append('modele_id', appliquerModeleId);
    fd.append('date', document.getElementById('appliquerDate').value);
    fd.append('libelle', document.getElementById('appliquerLibelle').value);
    fetch('<?= APP_URL ?>/dossier/modeles/appliquer', { method:'POST', body:fd })
        .then(function(r) { return r.json(); }).then(function(d) {
            var res = document.getElementById('appliquerResult');
            res.style.display = 'block';
            if (d.ok) {
                res.style.background = '#f0fdf4';
                res.style.color = '#166534';
                res.textContent = 'Écriture créée avec succès (brouillon). Redirection...';
                setTimeout(function() {
                    window.location = '<?= APP_URL ?>/dossier/ecritures?id=<?= $id ?>';
                }, 1200);
            } else {
                res.style.background = '#fef2f2';
                res.style.color = '#991b1b';
                res.textContent = 'Erreur : ' + (d.error || 'Vérifiez que les comptes existent dans le plan comptable.');
                btn.disabled = false;
                btn.textContent = 'Créer l\'écriture';
            }
        });
}
</script>
