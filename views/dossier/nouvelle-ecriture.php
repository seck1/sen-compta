<?php
$comptesJson = json_encode(array_map(fn($c) => [
    'id'      => (int)$c['id'],
    'numero'  => $c['numero'],
    'intitule'=> $c['intitule'],
], $comptes), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script id="comptes-data" type="application/json"><?= $comptesJson ?></script>
<script id="sections-data" type="application/json"><?= $sectionsJson ?? '[]' ?></script>

<style>
#form-ecriture { padding-bottom: 80px; }

/* ── En-tête page ── */
.ec-page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px; font-weight: 400;
    color: var(--navy-dark); letter-spacing: -.3px;
}
.ec-page-sub {
    font-size: 16px; color: var(--text-muted); margin-top: 2px;
    display: flex; align-items: center; gap: 8px;
}
.ec-badge-double {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(30,58,95,.07); border: 1px solid rgba(30,58,95,.15);
    border-radius: 20px; padding: 2px 10px;
    font-size: 15px; color: var(--navy); font-weight: 500;
}

/* ── Sections card ── */
.ec-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    margin-bottom: 14px;
    overflow: hidden;
}
.ec-card-head {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
}
.ec-card-head-icon {
    font-size: 19px; line-height: 1;
}
.ec-card-head-title {
    font-size: 16px; font-weight: 700;
    color: var(--navy-dark);
    text-transform: uppercase; letter-spacing: .7px;
}
.ec-card-body { padding: 18px 20px; }

/* ── Grille en-tête ── */
.ec-header-grid {
    display: grid;
    grid-template-columns: 1.4fr 160px 1fr 1fr;
    gap: 12px 16px;
    align-items: end;
}
.ec-header-grid .span2 { grid-column: span 2; }

/* ── Label ── */
.form-label {
    display: block;
    font-size: 15px; font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 5px;
    text-transform: uppercase; letter-spacing: .6px;
}
.form-label .req { color: var(--danger); margin-left: 2px; }

/* ── Champs ── */
.ec-field {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 17px;
    font-family: 'DM Sans', sans-serif;
    background: #fff; color: inherit;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.ec-field:focus { outline: none; border-color: #1f6e4e; box-shadow: 0 0 0 3px rgba(31,110,78,.15); }
.ec-field-mono { font-family: Arial, sans-serif; text-align: right; }

/* Masquer les spinners number */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number] { -moz-appearance: textfield; }

/* Info date saisie */
.date-saisie-info {
    display: flex; align-items: center; gap: 6px;
    padding: 9px 12px;
    background: rgba(30,58,95,.04);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 16px; color: var(--text-muted);
}
.date-saisie-info strong { color: var(--navy); }

/* ── Upload pièce jointe ── */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 18px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.upload-zone:hover, .upload-zone.drag { border-color: #1f6e4e; background: rgba(31,110,78,.04); }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; }
.upload-zone-icon { font-size: 22px; display: block; margin-bottom: 6px; }
.upload-zone-text { font-size: 16px; color: var(--text-muted); }
.upload-zone-text strong { color: var(--navy); }
.upload-name { font-size: 16px; color: var(--navy); font-weight: 500; margin-top: 6px; display: none; }

/* ── Table lignes ── */
.ec-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 17px;
}
.ec-table thead th {
    padding: 9px 10px;
    background: var(--bg);
    font-size: 14px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .8px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.ec-table thead th:nth-child(3),
.ec-table thead th:nth-child(4) { text-align: right; }
.ec-table tbody tr:nth-child(even) { background: rgba(0,0,0,.018); }
.ec-table tbody tr:hover { background: rgba(31,110,78,.04); }
.ec-table td { padding: 6px 8px; vertical-align: middle; }
.ec-table tfoot td {
    padding: 10px 10px;
    border-top: 2px solid var(--border);
    font-weight: 700; font-size: 17px;
    font-family: monospace;
}

/* ── Autocomplete ── */
.ac-wrapper { position: relative; width: 100%; min-width: 160px; }
.ac-clear {
    position: absolute; right: 7px; top: 50%; transform: translateY(-50%);
    cursor: pointer; font-size: 15px; color: var(--text-muted);
    display: none; background: none; border: none; padding: 2px; line-height: 1;
}
.ac-clear.visible { display: block; }
.ac-dropdown {
    position: absolute; top: calc(100% + 3px); left: 0; z-index: 9999;
    background: #fff; border: 1px solid var(--border); border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.14);
    max-height: 240px; overflow-y: auto;
    width: max-content; min-width: 300px; display: none;
}
.ac-dropdown.open { display: block; }
.ac-item {
    padding: 8px 14px; cursor: pointer; font-size: 16px;
    white-space: nowrap; border-bottom: 1px solid rgba(0,0,0,.04);
    display: flex; align-items: baseline; gap: 8px;
}
.ac-item:last-child { border-bottom: none; }
.ac-item:hover, .ac-item.active { background: rgba(31,110,78,.1); }
.ac-item .ac-num { font-weight: 700; font-family: monospace; font-size: 16px; }
.ac-item .ac-int { color: var(--text-muted); font-size: 15px; }
.ac-wrapper.ac-selected .ec-field { border-color: #1f6e4e; background: rgba(31,110,78,.04); }

/* ── Boutons icône ── */
.btn-ico {
    width: 28px; height: 28px; border: none; border-radius: 7px;
    cursor: pointer; font-size: 17px; line-height: 1;
    display: inline-flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.btn-ico-del { background: rgba(239,68,68,.08); color: var(--danger); }
.btn-ico-del:hover { background: rgba(239,68,68,.2); }
.btns-ligne { display: flex; gap: 4px; justify-content: center; }

/* ── Barre de statut collante ── */
#status-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;
    background: var(--navy-dark);
    color: #fff;
    display: flex; align-items: center; gap: 0;
    height: 58px;
    border-top: 2px solid rgba(255,255,255,.08);
    box-shadow: 0 -4px 20px rgba(0,0,0,.22);
}
.sb-group { display: flex; align-items: center; gap: 6px; padding: 0 24px; border-right: 1px solid rgba(255,255,255,.08); height: 100%; }
.sb-label { font-size: 14px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .8px; font-weight: 500; }
.sb-val { font-family: Arial, sans-serif; font-weight: 700; font-size: 18px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 10px; padding: 0 28px; flex: 1; justify-content: center; }
.sb-ok { color: #4ade80; font-weight: 700; font-size: 17px; }
.sb-ko { color: #f87171; font-weight: 700; font-size: 17px; }
.sb-diff-ok { color: #4ade80; }
.sb-diff-ko { color: #f87171; }
#btn-enregistrer { flex-shrink: 0; }
#btn-enregistrer:disabled { opacity: .4; cursor: not-allowed; }

/* ── Ligne ajouter ── */
.btn-add-ligne {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; width: 100%;
    border: 2px dashed var(--border); border-radius: 8px;
    background: none; cursor: pointer; font-size: 17px;
    color: var(--text-muted); font-family: 'DM Sans', sans-serif;
    transition: border-color .15s, color .15s;
    margin-top: 8px;
}
.btn-add-ligne:hover { border-color: #1f6e4e; color: #1f6e4e; }
</style>

<div class="page-header">
    <div>
        <h1 class="ec-page-title">Nouvelle écriture manuelle</h1>
        <p class="ec-page-sub">
            <span class="ec-badge-double">⚖️ Saisie en partie double — Σ Débit = Σ Crédit</span>
        </p>
    </div>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        ✕
    </a>
</div>

<?php if ($error ?? null): ?>
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:13px 16px;color:var(--danger);margin-bottom:16px;font-size:14px;display:flex;align-items:center;gap:8px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
    <?= e($error) ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/dossier/store-ecriture" id="form-ecriture" enctype="multipart/form-data">
    <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

    <!-- ── Section En-tête ── -->
    <div class="ec-card">
        <div class="ec-card-head">
            <span class="ec-card-head-icon">📓</span>
            <span class="ec-card-head-title">En-tête de l'écriture</span>
        </div>
        <div class="ec-card-body">
            <div class="ec-header-grid">
                <!-- Journal -->
                <div>
                    <label class="form-label">📓 Journal <span class="req">*</span></label>
                    <select name="journal_id" class="ec-field" required>
                        <option value="">— Choisir un journal —</option>
                        <?php foreach ($journaux as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= e($j['code']) ?> — <?= e($j['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date pièce -->
                <div>
                    <label class="form-label">📅 Date de pièce <span class="req">*</span></label>
                    <input type="date" name="date_ecriture" class="ec-field" value="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Date saisie auto -->
                <div>
                    <label class="form-label">🕐 Date de saisie</label>
                    <div class="date-saisie-info">
                        <strong id="date-saisie-val"><?= date('d/m/Y H:i') ?></strong>
                        <span style="font-size:14px;margin-left:4px">(générée automatiquement à l'enregistrement)</span>
                    </div>
                </div>

                <!-- N° pièce -->
                <div>
                    <label class="form-label">🔖 N° Pièce (auto)</label>
                    <input type="text" name="numero_piece" id="numero-piece" class="ec-field" placeholder="[généré automatiquement]" autocomplete="off">
                </div>

                <!-- N° facture fournisseur -->
                <div>
                    <label class="form-label">🧾 N° Facture fournisseur</label>
                    <input type="text" name="numero_facture" class="ec-field" placeholder="Ex: FAC-853-ACO-1" autocomplete="off">
                </div>

                <!-- Moyen de paiement -->
                <div>
                    <label class="form-label">💳 Moyen de paiement</label>
                    <select name="moyen_paiement" class="ec-field">
                        <option value="">— Non précisé —</option>
                        <option value="virement">Virement bancaire</option>
                        <option value="cheque">Chèque</option>
                        <option value="especes">Espèces</option>
                        <option value="orange_money">Orange Money</option>
                        <option value="wave">Wave</option>
                        <option value="free_money">Free Money</option>
                        <option value="carte">Carte bancaire</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <!-- Libellé général — pleine largeur -->
                <div style="grid-column:1/-1">
                    <label class="form-label">📝 Libellé <span class="req">*</span></label>
                    <input type="text" name="libelle" class="ec-field" placeholder="Description de l'écriture" required>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Section Pièce jointe ── -->
    <div class="ec-card">
        <div class="ec-card-head">
            <span class="ec-card-head-icon">📎</span>
            <span class="ec-card-head-title">Pièce jointe (PDF, image · max 5 Mo)</span>
        </div>
        <div class="ec-card-body" style="padding:14px 20px">
            <div class="upload-zone" id="upload-zone">
                <input type="file" name="piece_jointe" id="piece-jointe" accept=".pdf,.jpg,.jpeg,.png,.webp" onchange="handleFile(this)">
                <span class="upload-zone-icon">☁️</span>
                <div class="upload-zone-text">
                    <strong>Cliquez ou glissez un fichier ici</strong>
                </div>
                <div class="upload-name" id="upload-name"></div>
            </div>
        </div>
    </div>

    <!-- ── Section Lignes ── -->
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
                            <?php if (!empty($sectionsJson) && $sectionsJson !== '[]'): ?>
                            <th style="width:150px">Section</th>
                            <?php endif; ?>
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

<!-- ── Barre de statut collante ── -->
<div id="status-bar">
    <div class="sb-group">
        <span class="sb-label">Débit</span>
        <span class="sb-val" id="sb-debit">0,00</span>
    </div>
    <div class="sb-group">
        <span class="sb-label">Crédit</span>
        <span class="sb-val" id="sb-credit">0,00</span>
    </div>
    <div class="sb-status">
        <span id="sb-status-icon" class="sb-ok">✅ Équilibrée</span>
    </div>
    <button type="button" id="btn-enregistrer" class="btn btn-ent" onclick="document.getElementById('form-ecriture').submit()" disabled>
        🚀 Enregistrer l'écriture
    </button>
</div>

<script>
(function () {
'use strict';

const COMPTES  = JSON.parse(document.getElementById('comptes-data').textContent);
const SECTIONS = JSON.parse((document.getElementById('sections-data')||{}).textContent || '[]');
const HAS_SECTIONS = SECTIONS.length > 0;
const ENT_ID   = <?= (int)$entreprise['id'] ?>;
const APP_URL  = '<?= APP_URL ?>';
const fmt = v => new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(v);
const parseNum = s => parseFloat((s || '').toString().replace(',', '.')) || 0;

/* ── Cache tiers (chargé une fois) ── */
let TIERS_ALL = null;
function loadTiers(cb) {
    if (TIERS_ALL !== null) { cb(TIERS_ALL); return; }
    fetch(APP_URL + '/dossier/tiers/json?id=' + ENT_ID + '&type=tous')
        .then(r => r.json())
        .then(list => { TIERS_ALL = list; cb(list); })
        .catch(() => { TIERS_ALL = []; cb([]); });
}

function mkEl(tag, attrs, styles) {
    const el = document.createElement(tag);
    if (attrs) Object.entries(attrs).forEach(([k,v]) => {
        if (k === 'class') el.className = v;
        else el.setAttribute(k, v);
    });
    if (styles) Object.assign(el.style, styles);
    return el;
}

/* ── Autocomplete ── */
function filterComptes(q) {
    const ql = q.toLowerCase();
    return COMPTES.filter(c =>
        c.numero.startsWith(q) ||
        c.numero.toLowerCase().includes(ql) ||
        c.intitule.toLowerCase().includes(ql)
    ).slice(0, 60);
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
            const div = mkEl('div', {'class': 'ac-item'});
            div.dataset.id  = c.id;
            div.dataset.num = c.numero;
            div.dataset.int = c.intitule;
            const sNum = mkEl('span', {'class': 'ac-num'}); sNum.textContent = c.numero;
            const sSep = document.createTextNode(' — ');
            const sInt = mkEl('span', {'class': 'ac-int'}); sInt.textContent = c.intitule;
            div.appendChild(sNum); div.appendChild(sSep); div.appendChild(sInt);
            div.addEventListener('mousedown', function(e) { e.preventDefault(); selectItem(this); });
            dropdown.appendChild(div);
            visibleItems.push(div);
        });
        dropdown.classList.add('open');
    }

    function selectItem(div) {
        hidden.value   = div.dataset.id;
        textInp.value  = div.dataset.num + ' — ' + div.dataset.int;
        clearBtn.classList.add('visible');
        wrapper.classList.add('ac-selected');
        dropdown.classList.remove('open');
        activeIdx = -1;
        if (onSelect) onSelect(div.dataset.num);
    }

    function clearSel() {
        hidden.value = ''; textInp.value = '';
        clearBtn.classList.remove('visible');
        wrapper.classList.remove('ac-selected');
        dropdown.classList.remove('open');
    }

    function updateActive() {
        visibleItems.forEach((it,i) => it.classList.toggle('active', i === activeIdx));
        if (activeIdx >= 0) visibleItems[activeIdx].scrollIntoView({block:'nearest'});
    }

    textInp.addEventListener('input', () => {
        const q = textInp.value.trim();
        if (q.length < 2) { dropdown.classList.remove('open'); return; }
        renderDropdown(filterComptes(q));
    });
    textInp.addEventListener('keydown', e => {
        if (!dropdown.classList.contains('open')) return;
        if (e.key === 'ArrowDown')  { e.preventDefault(); activeIdx = Math.min(activeIdx+1, visibleItems.length-1); updateActive(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx-1, 0); updateActive(); }
        else if (e.key === 'Enter' || e.key === 'Tab') { if (activeIdx >= 0) { e.preventDefault(); selectItem(visibleItems[activeIdx]); } }
        else if (e.key === 'Escape') dropdown.classList.remove('open');
    });
    textInp.addEventListener('blur', () => {
        setTimeout(() => dropdown.classList.remove('open'), 180);
        if (!hidden.value && textInp.value.trim().length >= 2) {
            const res = filterComptes(textInp.value.trim());
            if (res.length === 1) { hidden.value = res[0].id; textInp.value = res[0].numero+' — '+res[0].intitule; clearBtn.classList.add('visible'); wrapper.classList.add('ac-selected'); if (onSelect) onSelect(res[0].numero); }
        }
    });
    clearBtn.addEventListener('click', clearSel);
}

/* ── Cellule compte ── */
function makeCellCompte(placeholder, onSelect) {
    const td = mkEl('td', null, {padding:'5px 8px', paddingLeft:'20px'});
    const wrapper  = mkEl('div', {'class':'ac-wrapper'});
    const hidden   = mkEl('input', {type:'hidden', name:'compte_id[]'});
    const textInp  = mkEl('input', {type:'text', 'class':'ec-field ac-input', placeholder: placeholder || 'Ex: 411 ou Clients', autocomplete:'off'});
    const clearBtn = mkEl('button', {type:'button', 'class':'ac-clear', title:'Effacer'});
    clearBtn.textContent = '✕';
    const dropdown = mkEl('div', {'class':'ac-dropdown'});
    wrapper.appendChild(hidden); wrapper.appendChild(textInp);
    wrapper.appendChild(clearBtn); wrapper.appendChild(dropdown);
    td.appendChild(wrapper);
    createAutocomplete(wrapper, onSelect);
    return td;
}

/* ── Cellule montant ── */
function makeCellMontant(name) {
    const td  = mkEl('td', null, {padding:'5px 8px'});
    const inp = mkEl('input', {type:'number', name:name, step:'0.01', min:'0', placeholder:'0,00', 'class':'ec-field ec-field-mono montant-input'});
    td.appendChild(inp);
    return td;
}

/* ── Cellule texte ── */
function makeCellText(name, placeholder) {
    const td  = mkEl('td', null, {padding:'5px 8px'});
    const inp = mkEl('input', {type:'text', name:name, placeholder:placeholder||'', 'class':'ec-field'});
    td.appendChild(inp);
    return td;
}

/* ── Cellule section analytique (optionnelle) ── */
function makeCellSection() {
    const td  = mkEl('td', null, {padding:'5px 8px'});
    const sel = mkEl('select', {name:'section_analytique_id[]', 'class':'ec-field'});
    sel.appendChild(mkEl('option', {value:''}, null)).textContent = '—';
    SECTIONS.forEach(function(s){
        const opt = mkEl('option', {value:String(s.id)});
        opt.textContent = s.code + ' · ' + s.libelle;
        sel.appendChild(opt);
    });
    td.appendChild(sel);
    return td;
}

/* ── Détecter type tiers depuis numéro compte ── */
function tiersTypeFromCompte(numero) {
    if (!numero) return null;
    const n = numero.toString();
    if (n.startsWith('40')) return 'fournisseur';
    if (n.startsWith('41')) return 'client';
    return null;
}

/* ── Ligne tiers (sous la ligne comptable) ── */
function creerTiersRow(ncols, tiersId, tiersNom) {
    const tr = mkEl('tr', {'class':'tiers-row'});
    tr.style.cssText = 'display:none;background:rgba(30,58,95,.02);border-bottom:2px solid var(--border)';

    const td = mkEl('td', {colspan: ncols});
    td.style.cssText = 'padding:10px 20px 12px 36px';

    // hidden input tiers_id
    const hiddenId = mkEl('input', {type:'hidden', name:'tiers_id[]', value:''});
    td.appendChild(hiddenId);

    // Label type (FOURNISSEUR / CLIENT)
    const labelType = mkEl('div');
    labelType.style.cssText = 'font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;color:#9ca3af';
    td.appendChild(labelType);

    // Bloc principal
    const bloc = mkEl('div');
    bloc.style.cssText = 'display:flex;align-items:center;gap:12px;flex-wrap:wrap';

    // Nom détecté (texte affiché)
    const nomDetecte = mkEl('span');
    nomDetecte.style.cssText = 'font-size:14px;font-weight:600;color:var(--navy-dark);min-width:120px';
    nomDetecte.textContent = '—';

    // Select lier
    const sel = mkEl('select');
    sel.style.cssText = 'padding:5px 10px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:"DM Sans",sans-serif;color:var(--navy-dark);min-width:220px;outline:none;background:#fff';
    const optDefault = mkEl('option', {value:''});
    optDefault.textContent = '— Lier à un tiers existant —';
    sel.appendChild(optDefault);

    // Séparateur "ou"
    const ou = mkEl('span');
    ou.style.cssText = 'font-size:13px;color:var(--text-muted)';
    ou.textContent = 'ou';

    // Lien créer
    const lienCreer = mkEl('a', {href:'#', target:'_blank'});
    lienCreer.style.cssText = 'font-size:13px;color:#1f6e4e;font-weight:500;white-space:nowrap;text-decoration:none;display:flex;align-items:center;gap:4px';
    const svgPlus = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svgPlus.setAttribute('fill','none'); svgPlus.setAttribute('viewBox','0 0 24 24'); svgPlus.setAttribute('stroke-width','2'); svgPlus.setAttribute('stroke','currentColor'); svgPlus.style.cssText='width:13px;height:13px';
    const pathPlus = document.createElementNS('http://www.w3.org/2000/svg','path');
    pathPlus.setAttribute('stroke-linecap','round'); pathPlus.setAttribute('stroke-linejoin','round'); pathPlus.setAttribute('d','M12 4.5v15m7.5-7.5h-15');
    svgPlus.appendChild(pathPlus); lienCreer.appendChild(svgPlus);
    lienCreer.appendChild(document.createTextNode('Créer ce tiers'));

    // Badge lié
    const badge = mkEl('span');
    badge.style.cssText = 'display:none;align-items:center;gap:4px;padding:3px 9px;background:rgba(31,110,78,.1);border:1px solid rgba(31,110,78,.2);border-radius:20px;font-size:13px;color:#1f6e4e;font-weight:600';
    const svgCheck = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svgCheck.setAttribute('fill','none'); svgCheck.setAttribute('viewBox','0 0 24 24'); svgCheck.setAttribute('stroke-width','2'); svgCheck.setAttribute('stroke','currentColor'); svgCheck.style.cssText='width:12px;height:12px';
    const pathCheck = document.createElementNS('http://www.w3.org/2000/svg','path');
    pathCheck.setAttribute('stroke-linecap','round'); pathCheck.setAttribute('stroke-linejoin','round'); pathCheck.setAttribute('d','M4.5 12.75l6 6 9-13.5');
    svgCheck.appendChild(pathCheck); badge.appendChild(svgCheck);
    badge.appendChild(document.createTextNode('Lié'));

    bloc.appendChild(nomDetecte);
    bloc.appendChild(sel);
    bloc.appendChild(ou);
    bloc.appendChild(lienCreer);
    bloc.appendChild(badge);
    td.appendChild(bloc);
    tr.appendChild(td);

    // Méthode pour activer la ligne tiers avec un type
    tr._activate = function(type, nom) {
        const col  = type === 'fournisseur' ? '#d97706' : '#2563eb';
        const label = type === 'fournisseur' ? 'Fournisseur' : 'Client';
        labelType.textContent = label;
        labelType.style.color = col;
        nomDetecte.textContent = nom || '—';

        // Réinitialiser le select
        while (sel.options.length > 1) sel.remove(1);
        hiddenId.value = '';
        badge.style.display = 'none';

        loadTiers(list => {
            const filtered = type === 'fournisseur'
                ? list.filter(t => t.type === 'fournisseur' || t.type === 'les_deux')
                : list.filter(t => t.type === 'client'      || t.type === 'les_deux');

            filtered.forEach(t => {
                const o = mkEl('option', {value: t.id});
                o.textContent = t.nom;
                // Pré-sélectionner si le nom correspond
                if (tiersId && t.id == tiersId) {
                    o.selected = true;
                    hiddenId.value = t.id;
                    badge.style.display = 'inline-flex';
                } else if (!tiersId && nom && t.nom.toLowerCase() === nom.toLowerCase()) {
                    o.selected = true;
                    hiddenId.value = t.id;
                    badge.style.display = 'inline-flex';
                }
                sel.appendChild(o);
            });

            lienCreer.href = APP_URL + '/dossier/tiers/form?id=' + ENT_ID + '&type=' + type + (nom ? '&nom=' + encodeURIComponent(nom) : '');
        });

        tr.style.display = 'table-row';
    };

    tr._hide = function() {
        tr.style.display = 'none';
        hiddenId.value = '';
    };

    sel.addEventListener('change', function() {
        hiddenId.value = this.value;
        badge.style.display = this.value ? 'inline-flex' : 'none';
    });

    // Pré-remplir si déjà lié
    if (tiersId) {
        hiddenId.value = tiersId;
    }

    return tr;
}

/* ── Création ligne ── */
function creerLigne() {
    const NCOLS = HAS_SECTIONS ? 6 : 5; // +1 colonne si sections analytiques
    const tr = mkEl('tr', {'class':'ligne-ecriture'});

    const tdCompte = makeCellCompte('Ex: 401 ou 411…', function(numeroCompte) {
        const type = tiersTypeFromCompte(numeroCompte);
        if (type) {
            trTiers._activate(type, null);
        } else {
            trTiers._hide();
        }
    });
    const tdDeb = makeCellMontant('debit[]');
    const tdCre = makeCellMontant('credit[]');
    const tdLib = makeCellText('ligne_libelle[]', 'Optionnel');

    const tdBtn  = mkEl('td', null, {padding:'5px 6px'});
    const btnDel = mkEl('button', {type:'button', 'class':'btn-ico btn-ico-del', title:'Supprimer'});
    btnDel.textContent = '✕';
    tdBtn.appendChild(btnDel);

    tr.appendChild(tdCompte);
    tr.appendChild(tdDeb);
    tr.appendChild(tdCre);
    tr.appendChild(tdLib);
    if (HAS_SECTIONS) tr.appendChild(makeCellSection());
    tr.appendChild(tdBtn);

    const trTiers = creerTiersRow(NCOLS);

    const inpDeb = tdDeb.querySelector('input');
    const inpCre = tdCre.querySelector('input');
    inpDeb.addEventListener('input', () => { autoBalance(tr, 'debit'); calculerTotaux(); });
    inpCre.addEventListener('input', () => { autoBalance(tr, 'credit'); calculerTotaux(); });

    btnDel.addEventListener('click', () => {
        if (document.querySelectorAll('.ligne-ecriture').length > 2) {
            tr.remove(); trTiers.remove(); calculerTotaux();
        }
    });

    // Retourner les deux lignes ensemble
    tr._trTiers = trTiers;
    return tr;
}

/* ── Solde intelligent ── */
function autoBalance(tr, side) {
    const tbody  = document.getElementById('lignes-tbody');
    const lignes = tbody.querySelectorAll('.ligne-ecriture');
    if (lignes.length !== 2) return;
    const idx = Array.prototype.indexOf.call(lignes, tr);
    if (idx < 0) return;
    const other   = lignes[idx === 0 ? 1 : 0];
    const myDeb   = tr.querySelector('[name="debit[]"]');
    const myCre   = tr.querySelector('[name="credit[]"]');
    const otDeb   = other.querySelector('[name="debit[]"]');
    const otCre   = other.querySelector('[name="credit[]"]');
    if (side === 'debit' && parseNum(myDeb.value) > 0 && !parseNum(otDeb.value) && !parseNum(otCre.value))
        otCre.value = myDeb.value;
    else if (side === 'credit' && parseNum(myCre.value) > 0 && !parseNum(otDeb.value) && !parseNum(otCre.value))
        otDeb.value = myCre.value;
}

/* ── Totaux ── */
function calculerTotaux() {
    let totalD = 0, totalC = 0;
    document.querySelectorAll('[name="debit[]"]').forEach(i  => totalD += parseNum(i.value));
    document.querySelectorAll('[name="credit[]"]').forEach(i => totalC += parseNum(i.value));

    document.getElementById('sb-debit').textContent  = fmt(totalD);
    document.getElementById('sb-credit').textContent = fmt(totalC);

    const balanced = Math.abs(totalD - totalC) < 0.005;
    const hasAmt   = totalD > 0 || totalC > 0;

    const icon = document.getElementById('sb-status-icon');
    if (balanced && hasAmt) {
        icon.textContent = '✅ Équilibrée';
        icon.className = 'sb-ok';
    } else if (!hasAmt) {
        icon.textContent = '— Aucun montant';
        icon.className = '';
        icon.style.color = 'rgba(255,255,255,.35)';
    } else {
        icon.textContent = '⚠️ Déséquilibrée (' + fmt(Math.abs(totalD - totalC)) + ')';
        icon.className = 'sb-ko';
        icon.style.color = '';
    }

    document.getElementById('btn-enregistrer').disabled = !(balanced && hasAmt);
}

/* ── Ajouter ligne ── */
function ajouterLigne() {
    const tbody = document.getElementById('lignes-tbody');
    const tr = creerLigne();
    tbody.appendChild(tr);
    tbody.appendChild(tr._trTiers);
    calculerTotaux();
}
window.ajouterLigne = ajouterLigne;

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

// Drag & drop
const zone = document.getElementById('upload-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag');
    const input = document.getElementById('piece-jointe');
    input.files = e.dataTransfer.files;
    window.handleFile(input);
});

/* ── Init 2 lignes ── */
(function() {
    const tbody = document.getElementById('lignes-tbody');
    [creerLigne(), creerLigne()].forEach(tr => { tbody.appendChild(tr); tbody.appendChild(tr._trTiers); });
    calculerTotaux();
}());

/* ── F9 ── */
document.addEventListener('keydown', e => {
    if (e.key === 'F9') {
        e.preventDefault();
        const btn = document.getElementById('btn-enregistrer');
        if (!btn.disabled) document.getElementById('form-ecriture').submit();
    }
});

}());
</script>
