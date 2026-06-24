<?php $isEdit = !empty($devis); ?>
<style>
.form-root{padding:32px 36px;max-width:1000px}
.form-root h1{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--navy-dark);margin-bottom:6px}
.sub{font-size:13px;color:var(--text-muted);margin-bottom:24px}
.form-card{background:#fff;border-radius:16px;border:1px solid var(--border);padding:24px;margin-bottom:20px}
.form-section-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--gold);display:inline-block}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px}
input,select,textarea{padding:9px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;color:var(--text);font-family:inherit;background:#fff;transition:border-color 0.2s}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--navy)}
.lignes-table{width:100%;border-collapse:collapse;margin-bottom:12px}
.lignes-table th{font-size:11px;text-transform:uppercase;color:var(--text-muted);padding:8px 10px;background:#fafbfc;border-bottom:1px solid var(--border);text-align:left;font-weight:600}
.lignes-table td{padding:8px 6px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.lignes-table input,.lignes-table select{padding:7px 10px;font-size:12px}
.totaux{background:#f8fafc;border-radius:10px;padding:16px;margin-top:12px;display:flex;justify-content:flex-end}
.totaux-table{min-width:280px}
.totaux-row{display:flex;justify-content:space-between;padding:5px 0;font-size:13px}
.totaux-row.total{font-size:16px;font-weight:700;color:var(--navy-dark);border-top:2px solid var(--border);margin-top:6px;padding-top:10px}
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s}
.btn-gold{background:var(--gold);color:var(--navy-dark)}
.btn-gold:hover{background:var(--gold-dark);color:#fff}
.btn-outline{background:transparent;color:var(--text-muted);border:1.5px solid var(--border)}
.btn-add-ligne{display:flex;align-items:center;gap:6px;padding:8px 14px;border:2px dashed var(--border);border-radius:8px;font-size:12px;font-weight:600;color:var(--text-muted);background:none;cursor:pointer;width:100%;justify-content:center;transition:all 0.2s}
.btn-add-ligne:hover{border-color:var(--navy);color:var(--navy)}
.btn-del{background:none;border:none;cursor:pointer;color:#ef4444;padding:4px 8px;border-radius:5px;font-size:18px;line-height:1}
.btn-del:hover{background:#fee2e2}
.prestation-select{width:100%;padding:9px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;cursor:pointer;background:#fff;margin-bottom:14px}
</style>

<div class="form-root">
    <h1><?= $isEdit ? 'Modifier ' . e($devis['numero']) : 'Nouveau devis' ?></h1>
    <p class="sub"><?= $isEdit ? e($devis['raison_sociale']) : 'Créer un devis pour un prospect ou client' ?></p>

    <form method="POST" action="<?= APP_URL ?>/commercial/devis/store" id="devisForm">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $devis['id'] ?>"><?php endif; ?>
        <input type="hidden" name="montant_ht" id="inp_ht" value="0">
        <input type="hidden" name="montant_tva" id="inp_tva" value="0">
        <input type="hidden" name="montant_ttc" id="inp_ttc" value="0">

        <div class="form-card">
            <div class="form-section-title">Informations générales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Client / Prospect *</label>
                    <select name="prospect_id" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($prospects as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= ($devis['prospect_id'] ?? $prospectId) == $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['raison_sociale']) ?> · <?= e($p['ville']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut">
                        <?php foreach (['brouillon'=>'Brouillon','envoye'=>'Envoyé','accepte'=>'Accepté','refuse'=>'Refusé'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($devis['statut'] ?? 'brouillon') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date du devis</label>
                    <input type="date" name="date_devis" value="<?= e($devis['date_devis'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Date de validité</label>
                    <input type="date" name="date_validite" value="<?= e($devis['date_validite'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                </div>
                <div class="form-group full">
                    <label>Objet du devis</label>
                    <input type="text" name="objet" value="<?= e($devis['objet'] ?? '') ?>" placeholder="Ex: Mission d'expertise comptable exercice 2026">
                </div>
            </div>
        </div>

        <!-- Lignes -->
        <div class="form-card">
            <div class="form-section-title">Prestations</div>
            <select id="catalogueSelect" class="prestation-select" onchange="ajouterDepuisCatalogue(this)">
                <option value="">+ Ajouter depuis le catalogue de prestations...</option>
                <?php
                $cats = ['expertise_comptable'=>'Expertise Comptable','audit'=>'Audit','fiscal'=>'Fiscal','social'=>'Social','conseil'=>'Conseil','juridique'=>'Juridique','autre'=>'Autre'];
                $grouped = [];
                foreach ($prestations as $p) $grouped[$p['categorie']][] = $p;
                foreach ($cats as $catKey => $catLabel):
                    if (empty($grouped[$catKey])) continue;
                ?>
                <optgroup label="— <?= $catLabel ?> —">
                <?php foreach ($grouped[$catKey] as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"
                        data-prix="<?= (float)$p['prix_unitaire'] ?>"
                        data-unite="<?= e($p['unite']) ?>"
                        data-tva="<?= (float)$p['tva_taux'] ?>"
                        data-desc="<?= e($p['designation']) ?>">
                        <?= e($p['designation']) ?> — <?= number_format($p['prix_unitaire'],0,',',' ') ?> F/<?= e($p['unite']) ?>
                    </option>
                <?php endforeach; ?>
                </optgroup>
                <?php endforeach; ?>
            </select>

            <table class="lignes-table">
                <thead>
                    <tr>
                        <th style="width:32%">Désignation</th>
                        <th>Description</th>
                        <th style="width:55px">Qté</th>
                        <th style="width:75px">Unité</th>
                        <th style="width:105px">P.U. HT</th>
                        <th style="width:55px">Rem%</th>
                        <th style="width:55px">TVA%</th>
                        <th style="width:105px">Total HT</th>
                        <th style="width:32px"></th>
                    </tr>
                </thead>
                <tbody id="lignesBody">
                <?php if (!empty($lignes)): ?>
                <?php foreach ($lignes as $l): ?>
                <tr class="ligne-row">
                    <td>
                        <input type="hidden" name="ligne_prestation_id[]" value="<?= (int)($l['prestation_id'] ?? 0) ?>">
                        <input type="text" name="ligne_designation[]" value="<?= e($l['designation']) ?>" required style="width:100%">
                    </td>
                    <td><input type="text" name="ligne_desc[]" value="<?= e($l['description'] ?? '') ?>" style="width:100%"></td>
                    <td><input type="number" name="ligne_qte[]" value="<?= (float)$l['quantite'] ?>" min="0" step="0.5" onchange="recalcRow(this)" style="width:100%"></td>
                    <td><input type="text" name="ligne_unite[]" value="<?= e($l['unite']) ?>" style="width:100%"></td>
                    <td><input type="number" name="ligne_pu[]" value="<?= (float)$l['prix_unitaire'] ?>" min="0" onchange="recalcRow(this)" style="width:100%"></td>
                    <td><input type="number" name="ligne_remise[]" value="<?= (float)$l['remise'] ?>" min="0" max="100" onchange="recalcRow(this)" style="width:100%"></td>
                    <td><input type="number" name="ligne_tva[]" value="<?= (float)$l['tva_taux'] ?>" min="0" max="30" onchange="recalcRow(this)" style="width:100%"></td>
                    <td><input type="text" name="ligne_total_ht[]" class="ligne-ht" value="<?= number_format((float)$l['montant_ht'],0) ?>" readonly style="width:100%;background:#f8fafc;font-weight:600"></td>
                    <td><button type="button" class="btn-del" onclick="supprimerLigne(this)">×</button></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <button type="button" class="btn-add-ligne" onclick="ajouterLigne()">+ Ajouter une ligne</button>

            <div class="totaux">
                <div class="totaux-table">
                    <div class="totaux-row"><span>Total HT</span><span id="tot_ht">0 F</span></div>
                    <div class="totaux-row"><span>TVA</span><span id="tot_tva">0 F</span></div>
                    <div class="totaux-row total"><span>Total TTC</span><span id="tot_ttc">0 F</span></div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-section-title">Conditions & Notes</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Conditions de paiement</label>
                    <input type="text" name="conditions_paiement" value="<?= e($devis['conditions_paiement'] ?? 'Paiement à 30 jours après acceptation') ?>">
                </div>
                <div class="form-group">
                    <label>Remise globale (%)</label>
                    <input type="number" name="remise_globale" value="<?= (float)($devis['remise_globale'] ?? 0) ?>" min="0" max="100">
                </div>
                <div class="form-group full">
                    <label>Note pour le client</label>
                    <textarea name="notes_client" rows="2"><?= e($devis['notes_client'] ?? '') ?></textarea>
                </div>
                <div class="form-group full">
                    <label>Note interne</label>
                    <textarea name="notes_internes" rows="2"><?= e($devis['notes_internes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-gold">
                <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le devis' ?>
            </button>
            <a href="<?= APP_URL ?>/commercial/devis" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<script>
function fmt(n){return new Intl.NumberFormat('fr-FR').format(Math.round(n))+' F';}

function supprimerLigne(btn){ btn.closest('tr').remove(); recalcTotaux(); }

function recalcRow(inp){
    const row = inp.closest('tr');
    const qte = parseFloat(row.querySelector('[name="ligne_qte[]"]').value)||0;
    const pu  = parseFloat(row.querySelector('[name="ligne_pu[]"]').value)||0;
    const rem = parseFloat(row.querySelector('[name="ligne_remise[]"]').value)||0;
    row.querySelector('.ligne-ht').value = new Intl.NumberFormat('fr-FR').format(Math.round(qte*pu*(1-rem/100)));
    recalcTotaux();
}

function recalcTotaux(){
    let ht=0, tva=0;
    document.querySelectorAll('.ligne-row').forEach(row => {
        const qte=parseFloat(row.querySelector('[name="ligne_qte[]"]').value)||0;
        const pu =parseFloat(row.querySelector('[name="ligne_pu[]"]').value)||0;
        const rem=parseFloat(row.querySelector('[name="ligne_remise[]"]').value)||0;
        const tv =parseFloat(row.querySelector('[name="ligne_tva[]"]').value)||0;
        const h  =qte*pu*(1-rem/100);
        ht+=h; tva+=h*tv/100;
    });
    document.getElementById('tot_ht').textContent=fmt(ht);
    document.getElementById('tot_tva').textContent=fmt(tva);
    document.getElementById('tot_ttc').textContent=fmt(ht+tva);
    document.getElementById('inp_ht').value=Math.round(ht);
    document.getElementById('inp_tva').value=Math.round(tva);
    document.getElementById('inp_ttc').value=Math.round(ht+tva);
}

function ajouterLigne(pId, designation, desc, qte, unite, pu, rem, tva){
    pId=pId||''; designation=designation||''; desc=desc||'';
    qte=qte||1; unite=unite||'forfait'; pu=pu||0; rem=rem||0; tva=tva||18;

    const tbody = document.getElementById('lignesBody');
    const tr = document.createElement('tr');
    tr.className = 'ligne-row';

    const cells = [
        // Désignation + prestation_id
        (function(){
            const td = document.createElement('td');
            const hidden = document.createElement('input');
            hidden.type='hidden'; hidden.name='ligne_prestation_id[]'; hidden.value=pId;
            const inp = document.createElement('input');
            inp.type='text'; inp.name='ligne_designation[]'; inp.required=true;
            inp.style.width='100%'; inp.placeholder='Désignation'; inp.value=designation;
            td.appendChild(hidden); td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='text'; inp.name='ligne_desc[]'; inp.style.width='100%';
            inp.placeholder='Détails...'; inp.value=desc;
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='number'; inp.name='ligne_qte[]'; inp.value=qte;
            inp.min='0'; inp.step='0.5'; inp.style.width='100%';
            inp.addEventListener('change',()=>recalcRow(inp));
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='text'; inp.name='ligne_unite[]'; inp.value=unite; inp.style.width='100%';
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='number'; inp.name='ligne_pu[]'; inp.value=pu; inp.min='0'; inp.style.width='100%';
            inp.addEventListener('change',()=>recalcRow(inp));
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='number'; inp.name='ligne_remise[]'; inp.value=rem; inp.min='0'; inp.max='100'; inp.style.width='100%';
            inp.addEventListener('change',()=>recalcRow(inp));
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='number'; inp.name='ligne_tva[]'; inp.value=tva; inp.min='0'; inp.max='30'; inp.style.width='100%';
            inp.addEventListener('change',()=>recalcRow(inp));
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const inp=document.createElement('input');
            inp.type='text'; inp.name='ligne_total_ht[]'; inp.className='ligne-ht';
            inp.value='0'; inp.readOnly=true; inp.style.cssText='width:100%;background:#f8fafc;font-weight:600';
            td.appendChild(inp); return td;
        })(),
        (function(){
            const td=document.createElement('td');
            const btn=document.createElement('button');
            btn.type='button'; btn.className='btn-del'; btn.textContent='×';
            btn.addEventListener('click',()=>{tr.remove();recalcTotaux();});
            td.appendChild(btn); return td;
        })(),
    ];
    cells.forEach(td=>tr.appendChild(td));
    tbody.appendChild(tr);
    if(pu>0) recalcRow(tr.querySelector('[name="ligne_pu[]"]'));
    else recalcTotaux();
}

function ajouterDepuisCatalogue(sel){
    const opt=sel.options[sel.selectedIndex];
    if(!opt.value) return;
    ajouterLigne(opt.value, opt.dataset.desc, '', 1, opt.dataset.unite, parseFloat(opt.dataset.prix)||0, 0, parseFloat(opt.dataset.tva)||18);
    sel.value='';
}

document.addEventListener('DOMContentLoaded', recalcTotaux);
</script>
