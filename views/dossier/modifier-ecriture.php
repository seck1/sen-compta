<?php
$comptesJson = json_encode(array_map(fn($c) => [
    'id'      => (int)$c['id'],
    'numero'  => $c['numero'],
    'intitule'=> $c['intitule'],
], $comptes), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$lignesJson = json_encode(array_map(fn($l) => [
    'compte_id' => (int)$l['compte_id'],
    'numero'    => $l['numero'],
    'intitule'  => $l['intitule'],
    'debit'     => (float)$l['debit'],
    'credit'    => (float)$l['credit'],
    'libelle'   => $l['libelle'],
    'tiers_id'  => $l['tiers_id'] ? (int)$l['tiers_id'] : null,
    'tiers_nom' => $l['tiers'] ?? '',
], $lignes), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script id="comptes-data" type="application/json"><?= $comptesJson ?></script>
<script id="lignes-data"  type="application/json"><?= $lignesJson ?></script>

<style>
#form-ecriture { padding-bottom: 80px; }
.ec-page-title { font-family:'Cormorant Garamond',serif; font-size:28px; font-weight:400; color:var(--navy-dark); letter-spacing:-.3px; }
.ec-page-sub   { font-size:16px; color:var(--text-muted); margin-top:2px; display:flex; align-items:center; gap:8px; }
.ec-badge-double { display:inline-flex; align-items:center; gap:5px; background:rgba(30,58,95,.07); border:1px solid rgba(30,58,95,.15); border-radius:20px; padding:2px 10px; font-size:15px; color:var(--navy); font-weight:500; }
.ec-card { background:#fff; border:1px solid var(--border); border-radius:14px; margin-bottom:14px; overflow:hidden; }
.ec-card-head { display:flex; align-items:center; gap:10px; padding:13px 20px; border-bottom:1px solid var(--border); background:var(--bg); }
.ec-card-head-icon { font-size:19px; }
.ec-card-head-title { font-size:16px; font-weight:700; color:var(--navy-dark); text-transform:uppercase; letter-spacing:.7px; }
.ec-card-body { padding:18px 20px; }
.ec-header-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:14px; }
.form-label { display:block; font-size:14px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:6px; }
.req { color:var(--danger); }
.date-saisie-info { background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:9px 12px; font-size:16px; color:var(--navy-dark); min-height:38px; display:flex; align-items:center; }
.ec-field { width:100%; padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:17px; font-family:'DM Sans',sans-serif; color:var(--navy-dark); background:#fff; box-sizing:border-box; outline:none; transition:border-color .15s; }
.ec-field:focus { border-color:var(--navy); }
.ec-field-mono { font-family:Arial,sans-serif; text-align:right; }
.montant-input::-webkit-inner-spin-button, .montant-input::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
.montant-input { -moz-appearance:textfield; }
.ec-table { width:100%; border-collapse:collapse; }
.ec-table th { padding:10px 8px; font-size:14px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); text-align:left; background:var(--bg); }
.ec-table td { border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.ec-table tbody tr:hover { background:rgba(30,58,95,.02); }
.btn-add-ligne { display:flex; align-items:center; gap:8px; padding:10px 16px; width:100%; border:2px dashed var(--border); border-radius:8px; background:none; cursor:pointer; font-size:17px; color:var(--text-muted); font-family:'DM Sans',sans-serif; transition:border-color .15s,color .15s; margin-top:8px; }
.btn-add-ligne:hover { border-color:#3b82f6; color:#3b82f6; }
.btn-ico { border:none; background:none; cursor:pointer; border-radius:6px; display:flex; align-items:center; justify-content:center; transition:background .12s; }
.btn-ico-del { width:28px; height:28px; color:var(--danger); font-size:16px; }
.btn-ico-del:hover { background:rgba(239,68,68,.1); }
/* Statut bar */
#status-bar { position:fixed; bottom:0; left:0; right:0; background:var(--navy-dark); color:#fff; padding:0 24px; height:56px; display:flex; align-items:center; gap:20px; z-index:200; box-shadow:0 -2px 12px rgba(0,0,0,.15); }
.sb-group { display:flex; align-items:center; gap:8px; }
.sb-label { font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; opacity:.5; }
.sb-val { font-size:19px; font-weight:700; font-family:Arial,sans-serif; }
.sb-val.debit-val { color:#4ade80; }
.sb-val.credit-val { color:#f87171; }
.sb-status { flex:1; text-align:center; }
.sb-ok { color:#4ade80; font-weight:700; font-size:17px; }
.sb-ko { color:#f87171; font-weight:700; font-size:17px; }
#btn-enregistrer { flex-shrink:0; }
#btn-enregistrer:disabled { opacity:.4; cursor:not-allowed; }
/* Autocomplete */
.ac-wrapper { position:relative; }
.ac-input { padding-right:28px !important; }
.ac-clear { position:absolute; right:6px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:17px; display:none; line-height:1; padding:2px; }
.ac-clear.visible { display:block; }
.ac-dropdown { position:absolute; top:calc(100% + 4px); left:0; right:0; background:#fff; border:1.5px solid var(--navy); border-radius:10px; box-shadow:0 8px 24px rgba(30,58,95,.13); z-index:500; max-height:220px; overflow-y:auto; display:none; }
.ac-dropdown.open { display:block; }
.ac-item { padding:8px 12px; cursor:pointer; font-size:16px; display:flex; align-items:center; gap:6px; }
.ac-item:hover, .ac-item.active { background:rgba(30,58,95,.06); }
.ac-num { font-family:monospace; font-weight:700; color:var(--navy); font-size:15px; min-width:52px; }
.ac-int { color:var(--text-muted); }
.ac-selected .ac-input { background:rgba(30,58,95,.04); border-color:var(--navy); }
/* Upload */
.upload-zone { border:2px dashed var(--border); border-radius:10px; padding:20px; text-align:center; cursor:pointer; position:relative; transition:border-color .15s,background .15s; }
.upload-zone:hover, .upload-zone.drag { border-color:#3b82f6; background:rgba(59,130,246,.04); }
.upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; }
.upload-zone-icon { font-size:22px; display:block; margin-bottom:6px; }
.upload-zone-text { font-size:16px; color:var(--text-muted); }
.upload-zone-text strong { color:var(--navy); }
.upload-name { font-size:16px; color:var(--navy); font-weight:500; margin-top:6px; display:none; }
/* Pièce jointe existante */
.pj-existant { display:flex; align-items:center; gap:10px; padding:10px 14px; background:rgba(30,58,95,.04); border:1px solid var(--border); border-radius:10px; margin-bottom:12px; }
.pj-existant a { font-size:16px; font-weight:600; color:var(--navy); text-decoration:none; }
.pj-existant a:hover { text-decoration:underline; }
</style>

<div class="page-header">
    <div>
        <h1 class="ec-page-title">Modifier l'écriture</h1>
        <p class="ec-page-sub">
            <span class="ec-badge-double">⚖️ Saisie en partie double — Σ Débit = Σ Crédit</span>
            <?php if ($ecriture['statut'] === 'validee'): ?>
            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:20px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);font-size:15px;color:#16a34a;font-weight:600">✓ Validée</span>
            <?php else: ?>
            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:20px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);font-size:15px;color:#92400e;font-weight:600">⏳ Brouillon</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour
    </a>
</div>

<?php if ($error ?? null): ?>
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:13px 16px;color:var(--danger);margin-bottom:16px;font-size:17px;display:flex;align-items:center;gap:8px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
    <?= e($error) ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/dossier/update-ecriture" id="form-ecriture" enctype="multipart/form-data">
    <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
    <input type="hidden" name="ecriture_id"   value="<?= $ecriture['id'] ?>">

    <!-- ── En-tête ── -->
    <div class="ec-card">
        <div class="ec-card-head">
            <span class="ec-card-head-icon">📓</span>
            <span class="ec-card-head-title">En-tête de l'écriture</span>
        </div>
        <div class="ec-card-body">
            <div class="ec-header-grid">
                <div>
                    <label class="form-label">📓 Journal <span class="req">*</span></label>
                    <select name="journal_id" class="ec-field" required>
                        <option value="">— Choisir un journal —</option>
                        <?php foreach ($journaux as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $j['code'] === $ecriture['journal_code'] ? 'selected' : '' ?>><?= e($j['code']) ?> — <?= e($j['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">📅 Date de pièce <span class="req">*</span></label>
                    <input type="date" name="date_ecriture" class="ec-field" value="<?= e($ecriture['date_ecriture']) ?>" required>
                </div>
                <div>
                    <label class="form-label">🕐 Date de saisie</label>
                    <div class="date-saisie-info">
                        <strong><?= date('d/m/Y H:i', strtotime($ecriture['created_at'])) ?></strong>
                    </div>
                </div>
                <div>
                    <label class="form-label">🔖 N° Pièce</label>
                    <input type="text" name="numero_piece" class="ec-field" value="<?= e($ecriture['numero_piece'] ?? '') ?>" autocomplete="off">
                </div>
                <div>
                    <label class="form-label">🧾 N° Facture fournisseur</label>
                    <input type="text" name="numero_facture" class="ec-field" value="<?= e($ecriture['numero_facture'] ?? '') ?>" autocomplete="off" placeholder="Ex: FAC-853-ACO-1">
                </div>

                <!-- Moyen de paiement -->
                <div>
                    <label class="form-label">💳 Moyen de paiement</label>
                    <?php $mp = $ecriture['moyen_paiement'] ?? ''; ?>
                    <select name="moyen_paiement" class="ec-field">
                        <option value="" <?= !$mp?'selected':'' ?>>— Non précisé —</option>
                        <option value="virement"     <?= $mp==='virement'?'selected':'' ?>>Virement bancaire</option>
                        <option value="cheque"       <?= $mp==='cheque'?'selected':'' ?>>Chèque</option>
                        <option value="especes"      <?= $mp==='especes'?'selected':'' ?>>Espèces</option>
                        <option value="orange_money" <?= $mp==='orange_money'?'selected':'' ?>>Orange Money</option>
                        <option value="wave"         <?= $mp==='wave'?'selected':'' ?>>Wave</option>
                        <option value="free_money"   <?= $mp==='free_money'?'selected':'' ?>>Free Money</option>
                        <option value="carte"        <?= $mp==='carte'?'selected':'' ?>>Carte bancaire</option>
                        <option value="autre"        <?= $mp==='autre'?'selected':'' ?>>Autre</option>
                    </select>
                </div>
                <div style="grid-column:1/-1">
                    <label class="form-label">📝 Libellé <span class="req">*</span></label>
                    <input type="text" name="libelle" class="ec-field" value="<?= e($ecriture['libelle']) ?>" required>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Pièce jointe ── -->
    <div class="ec-card">
        <div class="ec-card-head">
            <span class="ec-card-head-icon">📎</span>
            <span class="ec-card-head-title">Pièce jointe (PDF, image · max 5 Mo)</span>
        </div>
        <div class="ec-card-body" style="padding:14px 20px">
            <?php if (!empty($ecriture['piece_jointe'])): ?>
            <?php
                $pjUrl = APP_URL . '/public/uploads/justificatifs/' . $ecriture['piece_jointe'];
                $pjExt = strtolower(pathinfo($ecriture['piece_jointe'], PATHINFO_EXTENSION));
            ?>
            <div class="pj-existant">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;color:var(--navy);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                <div style="flex:1">
                    <div style="font-size:15px;color:var(--text-muted);margin-bottom:2px">Justificatif actuel</div>
                    <a href="<?= $pjUrl ?>" target="_blank"><?= e($ecriture['piece_jointe']) ?></a>
                </div>
                <span style="font-size:14px;color:var(--text-muted)">Uploader un nouveau fichier pour remplacer</span>
            </div>
            <?php endif; ?>
            <div class="upload-zone" id="upload-zone">
                <input type="file" name="piece_jointe" id="piece-jointe" accept=".pdf,.jpg,.jpeg,.png,.webp" onchange="handleFile(this)">
                <span class="upload-zone-icon">☁️</span>
                <div class="upload-zone-text">
                    <strong><?= !empty($ecriture['piece_jointe']) ? 'Remplacer le fichier' : 'Cliquez ou glissez un fichier ici' ?></strong>
                </div>
                <div class="upload-name" id="upload-name"></div>
            </div>
        </div>
    </div>

    <!-- ── Lignes ── -->
    <div class="ec-card">
        <div class="ec-card-head">
            <span class="ec-card-head-icon">📊</span>
            <span class="ec-card-head-title">Lignes (débit = crédit obligatoire)</span>
        </div>
        <div class="ec-card-body" style="padding:0">
            <div style="overflow-x:auto">
                <table class="ec-table">
                    <thead>
                        <tr>
                            <th style="width:220px;padding-left:20px">N° Compte</th>
                            <th style="width:130px">Débit</th>
                            <th style="width:130px">Crédit</th>
                            <th>Libellé ligne</th>
                            <th style="width:44px"></th>
                        </tr>
                    </thead>
                    <tbody id="lignes-tbody"></tbody>
                </table>
            </div>
            <div style="padding:12px 20px 16px">
                <button type="button" onclick="ajouterLigne()" class="btn-add-ligne">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    + Ajouter une ligne
                </button>
            </div>
        </div>
    </div>
</form>

<!-- ── Barre de statut ── -->
<div id="status-bar">
    <div class="sb-group">
        <span class="sb-label">Débit</span>
        <span class="sb-val debit-val" id="sb-debit">0,00</span>
    </div>
    <div class="sb-group">
        <span class="sb-label">Crédit</span>
        <span class="sb-val credit-val" id="sb-credit">0,00</span>
    </div>
    <div class="sb-status">
        <span id="sb-status-icon">— Aucun montant</span>
    </div>
    <button type="button" id="btn-enregistrer" class="btn btn-ent" onclick="document.getElementById('form-ecriture').submit()" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        Enregistrer les modifications
    </button>
</div>

<script>
(function () {
'use strict';

const COMPTES  = JSON.parse(document.getElementById('comptes-data').textContent);
const LIGNES   = JSON.parse(document.getElementById('lignes-data').textContent);
const ENT_ID   = <?= (int)$entreprise['id'] ?>;
const APP_URL  = '<?= APP_URL ?>';
const fmt      = v => new Intl.NumberFormat('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}).format(v);
const parseNum = s => parseFloat((s||'').toString().replace(',','.')) || 0;

function mkEl(tag, attrs, styles) {
    const el = document.createElement(tag);
    if (attrs) Object.entries(attrs).forEach(([k,v]) => { if (k==='class') el.className=v; else el.setAttribute(k,v); });
    if (styles) Object.assign(el.style, styles);
    return el;
}

/* ── Autocomplete comptes ── */
function filterComptes(q) {
    const ql = q.toLowerCase();
    return COMPTES.filter(c => c.numero.startsWith(q) || c.numero.toLowerCase().includes(ql) || c.intitule.toLowerCase().includes(ql)).slice(0, 60);
}

function createAutocomplete(wrapper, onSelect) {
    const hidden   = wrapper.querySelector('input[type="hidden"]');
    const textInp  = wrapper.querySelector('.ac-input');
    const clearBtn = wrapper.querySelector('.ac-clear');
    const dropdown = wrapper.querySelector('.ac-dropdown');
    let activeIdx = -1, visibleItems = [];

    function renderDropdown(results) {
        while (dropdown.firstChild) dropdown.removeChild(dropdown.firstChild);
        activeIdx = -1; visibleItems = [];
        if (!results.length) { dropdown.classList.remove('open'); return; }
        results.forEach(c => {
            const div = mkEl('div', {'class':'ac-item'});
            div.dataset.id = c.id; div.dataset.num = c.numero; div.dataset.int = c.intitule;
            const sNum = mkEl('span',{'class':'ac-num'}); sNum.textContent = c.numero;
            const sSep = document.createTextNode(' — ');
            const sInt = mkEl('span',{'class':'ac-int'}); sInt.textContent = c.intitule;
            div.appendChild(sNum); div.appendChild(sSep); div.appendChild(sInt);
            div.addEventListener('mousedown', function(e) { e.preventDefault(); selectItem(this); });
            dropdown.appendChild(div); visibleItems.push(div);
        });
        dropdown.classList.add('open');
    }

    function selectItem(div) {
        hidden.value = div.dataset.id;
        textInp.value = div.dataset.num + ' — ' + div.dataset.int;
        clearBtn.classList.add('visible'); wrapper.classList.add('ac-selected');
        dropdown.classList.remove('open'); activeIdx = -1;
        if (onSelect) onSelect(div.dataset.num);
    }

    function clearSel() {
        hidden.value=''; textInp.value='';
        clearBtn.classList.remove('visible'); wrapper.classList.remove('ac-selected');
        dropdown.classList.remove('open');
    }

    // Pré-remplir depuis les données existantes
    wrapper._selectById = function(id, num, intitule) {
        hidden.value = id;
        textInp.value = num + ' — ' + intitule;
        clearBtn.classList.add('visible'); wrapper.classList.add('ac-selected');
    };

    textInp.addEventListener('input', () => {
        const q = textInp.value.trim();
        if (q.length < 2) { dropdown.classList.remove('open'); return; }
        renderDropdown(filterComptes(q));
    });
    textInp.addEventListener('keydown', e => {
        if (!dropdown.classList.contains('open')) return;
        if (e.key==='ArrowDown') { e.preventDefault(); activeIdx=Math.min(activeIdx+1,visibleItems.length-1); visibleItems.forEach((it,i)=>it.classList.toggle('active',i===activeIdx)); }
        else if (e.key==='ArrowUp') { e.preventDefault(); activeIdx=Math.max(activeIdx-1,0); visibleItems.forEach((it,i)=>it.classList.toggle('active',i===activeIdx)); }
        else if (e.key==='Enter'||e.key==='Tab') { if (activeIdx>=0) { e.preventDefault(); selectItem(visibleItems[activeIdx]); } }
        else if (e.key==='Escape') dropdown.classList.remove('open');
    });
    textInp.addEventListener('blur', () => {
        setTimeout(() => dropdown.classList.remove('open'), 180);
        if (!hidden.value && textInp.value.trim().length >= 2) {
            const res = filterComptes(textInp.value.trim());
            if (res.length===1) { hidden.value=res[0].id; textInp.value=res[0].numero+' — '+res[0].intitule; clearBtn.classList.add('visible'); wrapper.classList.add('ac-selected'); if (onSelect) onSelect(res[0].numero); }
        }
    });
    clearBtn.addEventListener('click', clearSel);
}

/* ── Cache tiers ── */
let TIERS_ALL = null;
function loadTiers(cb) {
    if (TIERS_ALL !== null) { cb(TIERS_ALL); return; }
    fetch(APP_URL + '/dossier/tiers/json?id=' + ENT_ID + '&type=tous')
        .then(r => r.json()).then(list => { TIERS_ALL = list; cb(list); }).catch(() => { TIERS_ALL = []; cb([]); });
}

function tiersTypeFromCompte(numero) {
    if (!numero) return null;
    const n = numero.toString();
    if (n.startsWith('40')) return 'fournisseur';
    if (n.startsWith('41')) return 'client';
    return null;
}

/* ── Ligne tiers (sous chaque ligne comptable) ── */
function creerTiersRow(ncols) {
    const tr = mkEl('tr', {'class':'tiers-row'});
    tr.style.cssText = 'display:none;background:rgba(30,58,95,.02);border-bottom:2px solid var(--border)';
    const td = mkEl('td', {colspan: ncols});
    td.style.cssText = 'padding:10px 20px 12px 36px';

    const hiddenId = mkEl('input', {type:'hidden', name:'tiers_id[]', value:''});
    td.appendChild(hiddenId);

    const labelType = mkEl('div');
    labelType.style.cssText = 'font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;color:#9ca3af';
    td.appendChild(labelType);

    const bloc = mkEl('div');
    bloc.style.cssText = 'display:flex;align-items:center;gap:12px;flex-wrap:wrap';

    const nomDetecte = mkEl('span');
    nomDetecte.style.cssText = 'font-size:17px;font-weight:600;color:var(--navy-dark);min-width:120px';
    nomDetecte.textContent = '—';

    const sel = mkEl('select');
    sel.style.cssText = 'padding:5px 10px;border:1px solid var(--border);border-radius:8px;font-size:16px;font-family:"DM Sans",sans-serif;color:var(--navy-dark);min-width:220px;outline:none;background:#fff';
    const optDefault = mkEl('option', {value:''});
    optDefault.textContent = '— Lier à un tiers existant —';
    sel.appendChild(optDefault);

    const ou = mkEl('span');
    ou.style.cssText = 'font-size:15px;color:var(--text-muted)';
    ou.textContent = 'ou';

    const lienCreer = mkEl('a', {href:'#', target:'_blank'});
    lienCreer.style.cssText = 'font-size:15px;color:#3b82f6;font-weight:500;white-space:nowrap;text-decoration:none;display:flex;align-items:center;gap:4px';
    const svgPlus = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svgPlus.setAttribute('fill','none'); svgPlus.setAttribute('viewBox','0 0 24 24'); svgPlus.setAttribute('stroke-width','2'); svgPlus.setAttribute('stroke','currentColor'); svgPlus.style.cssText='width:13px;height:13px';
    const pathPlus = document.createElementNS('http://www.w3.org/2000/svg','path');
    pathPlus.setAttribute('stroke-linecap','round'); pathPlus.setAttribute('stroke-linejoin','round'); pathPlus.setAttribute('d','M12 4.5v15m7.5-7.5h-15');
    svgPlus.appendChild(pathPlus); lienCreer.appendChild(svgPlus);
    lienCreer.appendChild(document.createTextNode('Créer ce tiers'));

    const badge = mkEl('span');
    badge.style.cssText = 'display:none;align-items:center;gap:4px;padding:3px 9px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);border-radius:20px;font-size:15px;color:#16a34a;font-weight:600';
    const svgCheck = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svgCheck.setAttribute('fill','none'); svgCheck.setAttribute('viewBox','0 0 24 24'); svgCheck.setAttribute('stroke-width','2'); svgCheck.setAttribute('stroke','currentColor'); svgCheck.style.cssText='width:12px;height:12px';
    const pathCheck = document.createElementNS('http://www.w3.org/2000/svg','path');
    pathCheck.setAttribute('stroke-linecap','round'); pathCheck.setAttribute('stroke-linejoin','round'); pathCheck.setAttribute('d','M4.5 12.75l6 6 9-13.5');
    svgCheck.appendChild(pathCheck); badge.appendChild(svgCheck);
    badge.appendChild(document.createTextNode('Lié'));

    bloc.appendChild(nomDetecte); bloc.appendChild(sel); bloc.appendChild(ou); bloc.appendChild(lienCreer); bloc.appendChild(badge);
    td.appendChild(bloc); tr.appendChild(td);

    tr._activate = function(type, nom, tiersId) {
        labelType.textContent = type === 'fournisseur' ? 'Fournisseur' : 'Client';
        labelType.style.color = type === 'fournisseur' ? '#d97706' : '#2563eb';
        nomDetecte.textContent = nom || '—';
        while (sel.options.length > 1) sel.remove(1);
        hiddenId.value = ''; badge.style.display = 'none';
        loadTiers(list => {
            const filtered = type === 'fournisseur'
                ? list.filter(t => t.type==='fournisseur'||t.type==='les_deux')
                : list.filter(t => t.type==='client'||t.type==='les_deux');
            filtered.forEach(t => {
                const o = mkEl('option', {value: t.id});
                o.textContent = t.nom;
                const nomLow = nom ? nom.toLowerCase() : '';
                const match = (tiersId && t.id == tiersId)
                    || (!tiersId && nomLow && (t.nom.toLowerCase().includes(nomLow) || nomLow.includes(t.nom.toLowerCase())));
                if (match) { o.selected=true; hiddenId.value=t.id; badge.style.display='inline-flex'; }
                sel.appendChild(o);
            });
            lienCreer.href = APP_URL + '/dossier/tiers/form?id=' + ENT_ID + '&type=' + type + (nom ? '&nom=' + encodeURIComponent(nom) : '');
        });
        tr.style.display = 'table-row';
    };
    tr._hide = function() { tr.style.display='none'; hiddenId.value=''; };
    sel.addEventListener('change', function() { hiddenId.value=this.value; badge.style.display=this.value?'inline-flex':'none'; });
    return tr;
}

/* ── Cellule compte ── */
function makeCellCompte(onSelect) {
    const td = mkEl('td', null, {padding:'5px 8px', paddingLeft:'20px'});
    const wrapper = mkEl('div', {'class':'ac-wrapper'});
    const hidden  = mkEl('input', {type:'hidden', name:'compte_id[]'});
    const textInp = mkEl('input', {type:'text', 'class':'ec-field ac-input', placeholder:'Ex: 401 ou 411…', autocomplete:'off'});
    const clearBtn = mkEl('button', {type:'button', 'class':'ac-clear', title:'Effacer'}); clearBtn.textContent='✕';
    const dropdown = mkEl('div', {'class':'ac-dropdown'});
    wrapper.appendChild(hidden); wrapper.appendChild(textInp); wrapper.appendChild(clearBtn); wrapper.appendChild(dropdown);
    td.appendChild(wrapper);
    createAutocomplete(wrapper, onSelect);
    td._acWrapper = wrapper;
    return td;
}

/* ── Cellule montant ── */
function makeCellMontant(name) {
    const td  = mkEl('td', null, {padding:'5px 8px'});
    const inp = mkEl('input', {type:'number', name:name, step:'0.01', min:'0', placeholder:'0,00', 'class':'ec-field ec-field-mono montant-input'});
    td.appendChild(inp); return td;
}

/* ── Cellule texte ── */
function makeCellText(name, placeholder) {
    const td  = mkEl('td', null, {padding:'5px 8px'});
    const inp = mkEl('input', {type:'text', name:name, placeholder:placeholder||'', 'class':'ec-field'});
    td.appendChild(inp); return td;
}

/* ── Créer une ligne ── */
function creerLigne(data) {
    const NCOLS = 5;
    const tr = mkEl('tr', {'class':'ligne-ecriture'});
    const trTiers = creerTiersRow(NCOLS);

    const tdCompte = makeCellCompte(function(numeroCompte) {
        const type = tiersTypeFromCompte(numeroCompte);
        if (type) trTiers._activate(type, null, null);
        else trTiers._hide();
    });
    const tdDeb = makeCellMontant('debit[]');
    const tdCre = makeCellMontant('credit[]');
    const tdLib = makeCellText('ligne_libelle[]', 'Optionnel');

    const tdBtn  = mkEl('td', null, {padding:'5px 6px'});
    const btnDel = mkEl('button', {type:'button', 'class':'btn-ico btn-ico-del', title:'Supprimer'}); btnDel.textContent='✕';
    tdBtn.appendChild(btnDel);

    tr.appendChild(tdCompte); tr.appendChild(tdDeb); tr.appendChild(tdCre); tr.appendChild(tdLib); tr.appendChild(tdBtn);

    const inpDeb = tdDeb.querySelector('input');
    const inpCre = tdCre.querySelector('input');
    inpDeb.addEventListener('input', ()=>{autoBalance(tr,'debit');calculerTotaux();});
    inpCre.addEventListener('input', ()=>{autoBalance(tr,'credit');calculerTotaux();});
    btnDel.addEventListener('click', ()=>{if(document.querySelectorAll('.ligne-ecriture').length>1){tr.remove();trTiers.remove();calculerTotaux();}});

    // Pré-remplir si données fournies
    if (data) {
        if (tdCompte._acWrapper && tdCompte._acWrapper._selectById) {
            tdCompte._acWrapper._selectById(data.compte_id, data.numero, data.intitule);
        }
        inpDeb.value = data.debit > 0 ? data.debit : '';
        inpCre.value = data.credit > 0 ? data.credit : '';
        tdLib.querySelector('input').value = data.libelle || '';
        const type = tiersTypeFromCompte(data.numero);
        if (type) trTiers._activate(type, data.tiers_nom || null, data.tiers_id || null);
    }

    tr._trTiers = trTiers;
    return tr;
}

/* ── Solde intelligent ── */
function autoBalance(tr, side) {
    const lignes = document.getElementById('lignes-tbody').querySelectorAll('.ligne-ecriture');
    if (lignes.length !== 2) return;
    const idx = Array.prototype.indexOf.call(lignes, tr); if (idx<0) return;
    const other = lignes[idx===0?1:0];
    const myDeb=tr.querySelector('[name="debit[]"]'), myCre=tr.querySelector('[name="credit[]"]');
    const otDeb=other.querySelector('[name="debit[]"]'), otCre=other.querySelector('[name="credit[]"]');
    if (side==='debit' && parseNum(myDeb.value)>0 && !parseNum(otDeb.value) && !parseNum(otCre.value)) otCre.value=myDeb.value;
    else if (side==='credit' && parseNum(myCre.value)>0 && !parseNum(otDeb.value) && !parseNum(otCre.value)) otDeb.value=myCre.value;
}

/* ── Totaux ── */
function calculerTotaux() {
    let totalD=0, totalC=0;
    document.querySelectorAll('[name="debit[]"]').forEach(i=>totalD+=parseNum(i.value));
    document.querySelectorAll('[name="credit[]"]').forEach(i=>totalC+=parseNum(i.value));
    document.getElementById('sb-debit').textContent  = fmt(totalD);
    document.getElementById('sb-credit').textContent = fmt(totalC);
    const balanced = Math.abs(totalD-totalC)<0.005;
    const hasAmt   = totalD>0||totalC>0;
    const icon = document.getElementById('sb-status-icon');
    if (balanced && hasAmt) { icon.textContent='✅ Équilibrée'; icon.className='sb-ok'; }
    else if (!hasAmt)       { icon.textContent='— Aucun montant'; icon.className=''; }
    else { icon.textContent='⚠️ Déséquilibrée ('+fmt(Math.abs(totalD-totalC))+')'; icon.className='sb-ko'; }
    document.getElementById('btn-enregistrer').disabled = !(balanced && hasAmt);
}

function ajouterLigne() {
    const tbody = document.getElementById('lignes-tbody');
    const tr = creerLigne();
    tbody.appendChild(tr);
    tbody.appendChild(tr._trTiers);
    calculerTotaux();
}
window.ajouterLigne = ajouterLigne;

/* ── Charger les lignes existantes ── */
(function() {
    const tbody = document.getElementById('lignes-tbody');
    const src = LIGNES.length > 0 ? LIGNES : [null, null];
    src.forEach(l => {
        const tr = creerLigne(l || undefined);
        tbody.appendChild(tr);
        tbody.appendChild(tr._trTiers);
    });
    calculerTotaux();
}());

/* ── Upload ── */
window.handleFile = function(input) {
    const nameEl = document.getElementById('upload-name');
    if (input.files && input.files[0]) {
        nameEl.textContent = '📄 ' + input.files[0].name;
        nameEl.style.display = 'block';
        document.querySelector('.upload-zone-text').style.display = 'none';
        document.querySelector('.upload-zone-icon').style.display = 'none';
    }
};
const zone = document.getElementById('upload-zone');
zone.addEventListener('dragover', e=>{e.preventDefault();zone.classList.add('drag');});
zone.addEventListener('dragleave', ()=>zone.classList.remove('drag'));
zone.addEventListener('drop', e=>{
    e.preventDefault(); zone.classList.remove('drag');
    const input = document.getElementById('piece-jointe');
    input.files = e.dataTransfer.files;
    window.handleFile(input);
});

/* ── Moyen de paiement → auto-update compte + journal ── */
const mpCompteMap = {
    virement:     { journal: 'BNQ', compte: '521' },
    cheque:       { journal: 'BNQ', compte: '521' },
    carte:        { journal: 'BNQ', compte: '521' },
    especes:      { journal: 'CAI', compte: '571' },
    orange_money: { journal: 'BNQ', compte: '5223' },
    wave:         { journal: 'BNQ', compte: '5224' },
    free_money:   { journal: 'BNQ', compte: '5225' },
    autre:        { journal: 'BNQ', compte: '521' },
};
const allComptes = JSON.parse(document.getElementById('comptes-data').textContent);
document.querySelector('select[name="moyen_paiement"]').addEventListener('change', function() {
    const cfg = mpCompteMap[this.value];
    if (!cfg) return;

    // 1. Changer le journal
    const selJournal = document.querySelector('select[name="journal_id"]');
    for (const opt of selJournal.options) {
        if (opt.text.includes(cfg.journal)) { selJournal.value = opt.value; break; }
    }

    // 2. Remplacer le compte 52x/57x dans les lignes
    const compteTarget = allComptes.find(c => c.numero === cfg.compte)
                      || allComptes.find(c => c.numero.startsWith(cfg.compte.substring(0,3)));
    if (!compteTarget) return;

    // Trouver les lignes dont le compte actuel est 52x ou 57x et remplacer
    document.querySelectorAll('#lignes-tbody tr').forEach(tr => {
        const hiddenCompte = tr.querySelector('input[name="compte_id[]"]');
        if (!hiddenCompte) return;
        const compteActuel = allComptes.find(c => c.id == hiddenCompte.value);
        if (!compteActuel) return;
        const num = compteActuel.numero;
        if (num.startsWith('52') || num.startsWith('57')) {
            // Le _acWrapper est sur le premier td (tdCompte)
            const tdCompte = tr.querySelector('td');
            const acWrapper = tdCompte?._acWrapper;
            if (acWrapper && acWrapper._selectById) {
                acWrapper._selectById(compteTarget.id, compteTarget.numero, compteTarget.intitule);
            } else {
                hiddenCompte.value = compteTarget.id;
            }
        }
    });
});

/* ── F9 ── */
document.addEventListener('keydown', e => {
    if (e.key==='F9') { e.preventDefault(); const btn=document.getElementById('btn-enregistrer'); if (!btn.disabled) document.getElementById('form-ecriture').submit(); }
});

}());
</script>
