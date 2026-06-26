<?php
$comptesJson = json_encode(array_map(fn($c) => [
    'id'      => (int)$c['id'],
    'numero'  => $c['numero'],
    'intitule'=> $c['intitule'],
], $comptes), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$journauxJson = json_encode(array_map(fn($j) => [
    'id'   => (int)$j['id'],
    'code' => $j['code'],
    'lib'  => $j['libelle'],
], $journaux), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script id="comptes-data"  type="application/json"><?= $comptesJson ?></script>
<script id="journaux-data" type="application/json"><?= $journauxJson ?></script>

<style>
#scan-wrap { padding-bottom: 80px; }

/* ── Titre page ── */
.ec-page-title { font-family:'Cormorant Garamond',serif; font-size:28px; font-weight:400; color:var(--navy-dark); letter-spacing:-.3px; }
.ec-page-sub   { font-size:14px; color:var(--text-muted); margin-top:2px; display:flex; align-items:center; gap:8px; }
.ec-badge { display:inline-flex; align-items:center; gap:5px; background:rgba(30,58,95,.07); border:1px solid rgba(30,58,95,.15); border-radius:20px; padding:2px 10px; font-size:13px; color:var(--navy); font-weight:500; }

/* ── Cards ── */
.ec-card { background:#fff; border:1px solid var(--border); border-radius:14px; margin-bottom:14px; overflow:hidden; }
.ec-card-head { display:flex; align-items:center; gap:10px; padding:13px 20px; border-bottom:1px solid var(--border); background:var(--bg); }
.ec-card-head-icon { font-size:13px; line-height:1; }
.ec-card-head-title { font-size:14px; font-weight:700; color:var(--navy-dark); text-transform:uppercase; letter-spacing:.7px; }
.ec-card-body { padding:18px 20px; }

/* ── Steps ── */
.steps { display:flex; align-items:center; gap:0; margin-bottom:20px; }
.step {
    display:flex; align-items:center; gap:10px;
    padding:12px 20px; border-radius:10px;
    font-size:14px; font-weight:500; color:var(--text-muted);
    flex:1; position:relative;
}
.step.active { background:var(--navy); color:#fff; }
.step.done   { background:rgba(31,110,78,.1); color:#1f6e4e; }
.step-num {
    width:26px; height:26px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:700; flex-shrink:0;
    background:rgba(0,0,0,.1);
}
.step.active .step-num { background:rgba(255,255,255,.2); color:#fff; }
.step.done   .step-num { background:#1f6e4e; color:#fff; }
.step-arrow { color:var(--border); font-size:13px; flex-shrink:0; }

/* ── Upload zone ── */
.upload-zone {
    border:2px dashed var(--border); border-radius:14px;
    padding:48px 32px; text-align:center; cursor:pointer;
    transition:border-color .2s, background .2s; position:relative;
}
.upload-zone:hover, .upload-zone.drag { border-color:#1f6e4e; background:rgba(31,110,78,.04); }
.upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; }
.upload-icon { font-size:48px; display:block; margin-bottom:14px; }
.upload-title { font-size:13px; font-weight:600; color:var(--navy-dark); margin-bottom:6px; }
.upload-sub   { font-size:14px; color:var(--text-muted); }
.upload-preview { display:none; align-items:center; gap:16px; padding:16px; background:rgba(31,110,78,.05); border-radius:12px; border:1px solid rgba(31,110,78,.2); }
.upload-preview img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:1px solid var(--border); }
.upload-preview-info { flex:1; text-align:left; }
.upload-preview-name { font-size:14px; font-weight:600; color:var(--navy-dark); }
.upload-preview-size { font-size:13px; color:var(--text-muted); margin-top:2px; }

/* ── Bouton analyser ── */
.btn-scan {
    position:relative; overflow:hidden;
    width:100%; padding:17px; margin-top:16px;
    background:linear-gradient(135deg,#0f6fba 0%,#0891b2 50%,#1f6e4e 100%);
    background-size:200% 100%;
    color:#fff; border:none; border-radius:14px;
    font-size:15px; font-weight:700; font-family:'DM Sans',sans-serif; letter-spacing:.3px;
    cursor:pointer; transition:all .25s cubic-bezier(.4,0,.2,1);
    display:flex; align-items:center; justify-content:center; gap:10px;
    box-shadow:0 6px 20px rgba(15,111,186,.35), inset 0 1px 0 rgba(255,255,255,.18);
    animation:btnScanGradient 5s ease infinite;
}
@keyframes btnScanGradient { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
/* Brillance qui traverse le bouton au survol */
.btn-scan::before {
    content:''; position:absolute; top:0; left:-100%; width:60%; height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.35),transparent);
    transition:left .6s ease;
}
.btn-scan:hover:not(:disabled)::before { left:140%; }
.btn-scan svg { animation:btnScanSpark 2.2s ease-in-out infinite; }
@keyframes btnScanSpark { 0%,100%{transform:scale(1) rotate(0)} 50%{transform:scale(1.18) rotate(8deg)} }
.btn-scan:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 12px 32px rgba(15,111,186,.5), inset 0 1px 0 rgba(255,255,255,.25); }
.btn-scan:disabled { opacity:.45; cursor:not-allowed; transform:none; animation:none; }
.btn-scan:disabled::before { display:none; }
.btn-scan:disabled svg { animation:none; }

/* ── Résultat IA ── */
#result-section { display:none; }

.ia-result-header {
    display:flex; align-items:center; gap:14px;
    padding:18px 20px;
    background:linear-gradient(135deg,rgba(16,185,129,.08),rgba(5,150,105,.04));
    border-bottom:1px solid rgba(16,185,129,.15);
}
.ia-badge-confiance {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:20px; font-size:13px; font-weight:600;
}
.confiance-haute   { background:rgba(31,110,78,.12); color:#1f6e4e; }
.confiance-moyenne { background:rgba(245,158,11,.12); color:#d97706; }
.confiance-faible  { background:rgba(239,68,68,.12);  color:#dc2626; }

.ia-info-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px; margin-bottom:16px;
}
.ia-info-item { display:flex; flex-direction:column; gap:3px; }
.ia-info-label { font-size:14px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.6px; }
.ia-info-val   { font-size:14px; font-weight:500; color:var(--navy-dark); }

/* ── Table résultat ── */
.ec-table { width:100%; border-collapse:collapse; font-size:14px; }
.ec-table thead th {
    padding:9px 12px; background:var(--bg);
    font-size:14px; font-weight:700;
    text-transform:uppercase; letter-spacing:.8px;
    color:var(--text-muted); border-bottom:1px solid var(--border);
}
.ec-table thead th:nth-child(3),
.ec-table thead th:nth-child(4) { text-align:right; }
.ec-table tbody tr:nth-child(even) { background:rgba(0,0,0,.018); }
.ec-table td { padding:10px 12px; vertical-align:middle; font-size:14px; }
.ec-field {
    width:100%; padding:8px 11px; border:1px solid var(--border); border-radius:8px;
    font-size:14px; font-family:'DM Sans',sans-serif; background:#fff; color:inherit;
    box-sizing:border-box; transition:border-color .15s;
}
.ec-field:focus { outline:none; border-color:#1f6e4e; box-shadow:0 0 0 3px rgba(31,110,78,.15); }
.ec-field-mono { font-family:Arial, sans-serif; text-align:right; }

/* Masquer les spinners number */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
input[type=number] { -moz-appearance:textfield; }
.montant-debit  { color:var(--danger); font-weight:600; font-family:Arial, sans-serif; }
.montant-credit { color:var(--success); font-weight:600; font-family:Arial, sans-serif; }

/* ── Form label ── */
.form-label { display:block; font-size:13px; font-weight:600; color:var(--text-muted); margin-bottom:5px; text-transform:uppercase; letter-spacing:.6px; }
.form-label .req { color:var(--danger); margin-left:2px; }

/* ── Loading ── */
.ia-loading {
    display:none; flex-direction:column; align-items:center; justify-content:center;
    padding:56px 32px; gap:16px;
}
.ia-spinner {
    width:48px; height:48px; border-radius:50%;
    border:4px solid rgba(31,110,78,.15);
    border-top-color:#1f6e4e;
    animation:spin .8s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }
.ia-loading-text { font-size:13px; color:var(--text-muted); font-weight:500; }
.ia-loading-sub  { font-size:14px; color:var(--text-muted); opacity:.6; }

/* ── Barre de statut ── */
#status-bar {
    position:fixed; bottom:0; left:0; right:0; z-index:1000;
    background:var(--navy-dark); color:#fff;
    display:flex; align-items:center;
    height:58px; border-top:2px solid rgba(255,255,255,.08);
    box-shadow:0 -4px 20px rgba(0,0,0,.22);
    padding:0 24px; gap:0;
}
.sb-group {
    display:flex; align-items:center; gap:6px;
    padding:0 24px; border-right:1px solid rgba(255,255,255,.08); height:100%;
}
.sb-label { font-size:14px; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.8px; font-weight:500; }
.sb-val   { font-family:Arial, sans-serif; font-weight:700; font-size:13px; color:#fff; }
.sb-divider { width:1px; background:rgba(255,255,255,.08); height:100%; }
.sb-status { display:flex; align-items:center; gap:10px; padding:0 28px; flex:1; justify-content:center; }
.sb-ok { color:#4ade80; font-weight:700; font-size:14px; }
.sb-ko { color:#f87171; font-weight:700; font-size:14px; }
#btn-valider-scan { flex-shrink:0; }
#btn-valider-scan:disabled { opacity:.4; cursor:not-allowed; }
</style>

<div class="page-header">
    <div>
        <h1 class="ec-page-title">Nouvelle écriture par scan</h1>
        <p class="ec-page-sub">
            <span class="ec-badge">🤖 Analyse automatique par IA — Claude Vision</span>
        </p>
    </div>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        ✕
    </a>
</div>

<!-- Steps -->
<div class="steps">
    <div class="step active" id="step-1">
        <span class="step-num">1</span>
        <span>Déposer le document</span>
    </div>
    <span class="step-arrow">›</span>
    <div class="step" id="step-2">
        <span class="step-num">2</span>
        <span>Résultat IA &amp; correction</span>
    </div>
    <span class="step-arrow">›</span>
    <div class="step" id="step-3">
        <span class="step-num">3</span>
        <span>Écriture enregistrée</span>
    </div>
</div>

<div id="scan-wrap">

    <!-- ── Étape 1 : Upload ── -->
    <div id="section-upload">
        <div class="ec-card">
            <div class="ec-card-head">
                <span class="ec-card-head-icon">📄</span>
                <span class="ec-card-head-title">Document à analyser</span>
            </div>
            <div class="ec-card-body">
                <div class="upload-zone" id="upload-zone">
                    <input type="file" id="scan-file" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="onFileSelect(this)">
                    <span class="upload-icon">☁️</span>
                    <div class="upload-title">Cliquez ou glissez votre document ici</div>
                    <div class="upload-sub">Facture, reçu, bon de commande… · PDF, JPEG, PNG, WEBP · max 10 Mo</div>
                </div>
                <div class="upload-preview" id="upload-preview">
                    <img id="preview-img" src="" alt="">
                    <div class="upload-preview-info">
                        <div class="upload-preview-name" id="preview-name"></div>
                        <div class="upload-preview-size" id="preview-size"></div>
                    </div>
                    <button type="button" onclick="resetFile()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:20px;padding:4px">✕</button>
                </div>

                <button type="button" id="btn-analyser" class="btn-scan" onclick="lancerAnalyse()" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    Analyser avec l'IA
                </button>
            </div>
        </div>
    </div>

    <!-- ── Loading ── -->
    <div class="ec-card" id="section-loading" style="display:none">
        <div class="ia-loading" id="ia-loading" style="display:flex">
            <div class="ia-spinner"></div>
            <div class="ia-loading-text">Analyse en cours…</div>
            <div class="ia-loading-sub">Claude Vision lit votre document et génère l'écriture OHADA</div>
        </div>
    </div>

    <!-- ── Étape 2 : Résultat ── -->
    <div id="result-section">

        <!-- Résumé IA -->
        <div class="ec-card">
            <div class="ia-result-header">
                <div style="font-size:22px">🤖</div>
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:700;color:var(--navy-dark)">Analyse IA terminée</div>
                    <div style="font-size:14px;color:var(--text-muted);margin-top:2px" id="ia-notes-text"></div>
                </div>
                <span class="ia-badge-confiance" id="ia-confiance-badge"></span>
            </div>
            <div class="ec-card-body">
                <div class="ia-info-grid">
                    <div class="ia-info-item">
                        <span class="ia-info-label">Type de document</span>
                        <span class="ia-info-val" id="ia-doc-type"></span>
                    </div>
                    <div class="ia-info-item" style="grid-column:span 2">
                        <span class="ia-info-label" id="ia-tiers-label">Tiers</span>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:2px">
                            <span class="ia-info-val" id="ia-tiers"></span>
                            <div id="ia-tiers-link" style="display:none;align-items:center;gap:8px">
                                <select id="tiers-select" style="padding:5px 10px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:'DM Sans',sans-serif;color:var(--navy-dark);min-width:200px;outline:none">
                                    <option value="">— Lier à un tiers existant —</option>
                                </select>
                                <span style="font-size:13px;color:var(--text-muted)">ou</span>
                                <a id="btn-creer-tiers" href="#" target="_blank" style="font-size:13px;color:#1f6e4e;font-weight:500;white-space:nowrap;text-decoration:none;display:flex;align-items:center;gap:4px">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Créer ce tiers
                                </a>
                                <span id="tiers-linked-badge" style="display:none;align-items:center;gap:4px;padding:3px 9px;background:rgba(31,110,78,.1);border:1px solid rgba(31,110,78,.2);border-radius:20px;font-size:13px;color:#1f6e4e;font-weight:600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Lié
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ia-info-item">
                        <span class="ia-info-label">Montant HT</span>
                        <span class="ia-info-val" id="ia-ht"></span>
                    </div>
                    <div class="ia-info-item">
                        <span class="ia-info-label">TVA (18%)</span>
                        <span class="ia-info-val" id="ia-tva"></span>
                    </div>
                    <div class="ia-info-item">
                        <span class="ia-info-label">Montant TTC</span>
                        <span class="ia-info-val" id="ia-ttc" style="font-weight:700;color:var(--navy-dark)"></span>
                    </div>
                    <div class="ia-info-item">
                        <span class="ia-info-label">Référence</span>
                        <span class="ia-info-val" id="ia-ref"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- En-tête de l'écriture à valider -->
        <div class="ec-card">
            <div class="ec-card-head">
                <span class="ec-card-head-icon">📓</span>
                <span class="ec-card-head-title">En-tête de l'écriture</span>
            </div>
            <div class="ec-card-body">
                <div style="display:grid;grid-template-columns:1.4fr 160px 1fr 1fr;gap:12px 16px;align-items:end">
                    <div>
                        <label class="form-label">📓 Journal <span class="req">*</span></label>
                        <select id="r-journal" class="ec-field">
                            <option value="">— Choisir —</option>
                            <?php foreach ($journaux as $j): ?>
                            <option value="<?= $j['code'] ?>"><?= e($j['code']) ?> — <?= e($j['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">📅 Date <span class="req">*</span></label>
                        <input type="date" id="r-date" class="ec-field" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label class="form-label">🔖 N° Pièce</label>
                        <input type="text" id="r-ref" class="ec-field" placeholder="[auto]">
                    </div>
                    <div>
                        <label class="form-label">🧾 N° Facture fournisseur</label>
                        <input type="text" id="r-num-facture" class="ec-field" placeholder="Ex: FAC-853-ACO-1">
                    </div>
                    <div>
                        <label class="form-label">🕐 Date de saisie</label>
                        <div style="padding:9px 12px;background:rgba(30,58,95,.04);border:1px solid var(--border);border-radius:8px;font-size:14px;color:var(--text-muted)">
                            <strong><?= date('d/m/Y H:i') ?></strong>
                        </div>
                    </div>
                    <div style="grid-column:1/-1">
                        <label class="form-label">📝 Libellé <span class="req">*</span></label>
                        <input type="text" id="r-libelle" class="ec-field" placeholder="Description de l'écriture">
                    </div>
                </div>
            </div>
        </div>

        <!-- Lignes générées -->
        <div class="ec-card">
            <div class="ec-card-head">
                <span class="ec-card-head-icon">📊</span>
                <span class="ec-card-head-title">Lignes générées par l'IA — vérifiez et corrigez si nécessaire</span>
            </div>
            <div class="ec-card-body" style="padding:0">
                <div style="overflow-x:auto">
                    <table class="ec-table">
                        <thead>
                            <tr>
                                <th style="padding-left:20px">N° Compte</th>
                                <th>Intitulé</th>
                                <th style="text-align:right;width:140px">Débit</th>
                                <th style="text-align:right;width:140px">Crédit</th>
                            </tr>
                        </thead>
                        <tbody id="ia-lignes-tbody"></tbody>
                        <tfoot>
                            <tr style="background:var(--bg)">
                                <td colspan="2" style="padding:10px 20px;font-size:14px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px">Totaux</td>
                                <td style="text-align:right;padding:10px 12px;font-family:monospace;font-weight:700;font-size:13px" id="ia-total-debit">0,00</td>
                                <td style="text-align:right;padding:10px 12px;font-family:monospace;font-weight:700;font-size:13px" id="ia-total-credit">0,00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /result-section -->

    <!-- ── Étape 3 : Succès ── -->
    <div id="section-success" style="display:none">
        <div class="ec-card">
            <div class="ec-card-body" style="text-align:center;padding:56px 32px">
                <div style="font-size:56px;margin-bottom:16px">✅</div>
                <div style="font-size:22px;font-weight:700;color:var(--navy-dark);margin-bottom:8px">Écriture enregistrée !</div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:28px">L'écriture a été créée en brouillon. Vous pouvez la valider depuis la liste des écritures.</div>
                <div style="display:flex;justify-content:center;gap:12px">
                    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="btn btn-primary">Voir les écritures</a>
                    <button type="button" onclick="resetAll()" class="btn btn-outline">🔄 Nouveau scan</button>
                </div>
            </div>
        </div>
    </div>

</div><!-- /scan-wrap -->

<!-- Barre statut (visible seulement étape 2) -->
<div id="status-bar" style="display:none">
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
    <button type="button" id="btn-valider-scan" class="btn btn-ent" onclick="validerEcriture()" disabled>
        🚀 Valider et enregistrer
    </button>
</div>

<script>
(function(){
'use strict';

const ENT_ID     = <?= (int)$entreprise['id'] ?>;
const APP_URL    = '<?= APP_URL ?>';
const COMPTES    = JSON.parse(document.getElementById('comptes-data').textContent);
const JOURNAUX   = JSON.parse(document.getElementById('journaux-data').textContent);
const fmt = v => new Intl.NumberFormat('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v);

let currentFile = null;
let iaData      = null;
let iaPieceJointe = null;

/* ── Upload ── */
function onFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    currentFile = input.files[0];
    showPreview(currentFile);
}
window.onFileSelect = onFileSelect;

function showPreview(file) {
    const preview = document.getElementById('upload-preview');
    const zone    = document.getElementById('upload-zone');
    preview.style.display = 'flex';
    zone.style.display = 'none';
    document.getElementById('preview-name').textContent = file.name;
    document.getElementById('preview-size').textContent = (file.size/1024).toFixed(0) + ' Ko';
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('preview-img').src = e.target.result; };
    reader.readAsDataURL(file);
    document.getElementById('btn-analyser').disabled = false;
}

window.resetFile = function() {
    currentFile = null;
    document.getElementById('scan-file').value = '';
    document.getElementById('upload-preview').style.display = 'none';
    document.getElementById('upload-zone').style.display = 'block';
    document.getElementById('btn-analyser').disabled = true;
};

// Drag & drop
const zone = document.getElementById('upload-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag');
    if (e.dataTransfer.files[0]) {
        currentFile = e.dataTransfer.files[0];
        const input = document.getElementById('scan-file');
        // can't assign to files directly on most browsers, just use the file
        showPreview(currentFile);
    }
});

/* ── Analyse ── */
function lancerAnalyse() {
    if (!currentFile) return;
    document.getElementById('section-upload').style.display = 'none';
    document.getElementById('section-loading').style.display = 'block';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('section-success').style.display = 'none';
    document.getElementById('status-bar').style.display = 'none';
    setStep(2);

    const fd = new FormData();
    fd.append('facture', currentFile);
    fd.append('entreprise_id', ENT_ID);

    fetch(APP_URL + '/scan-ia/analyser', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            document.getElementById('section-loading').style.display = 'none';
            if (data.error) { showError(data.error); return; }
            iaData = data.ecriture;
            iaPieceJointe = data.piece_jointe || null;
            afficherResultat(iaData);
        })
        .catch(() => { document.getElementById('section-loading').style.display='none'; showError('Erreur réseau'); });
}
window.lancerAnalyse = lancerAnalyse;

/* ── Affichage résultat ── */
function afficherResultat(ec) {
    document.getElementById('result-section').style.display = 'block';
    document.getElementById('status-bar').style.display = 'flex';

    // Confiance
    const badge = document.getElementById('ia-confiance-badge');
    const map = {haute:'confiance-haute',moyenne:'confiance-moyenne',faible:'confiance-faible'};
    const labels = {haute:'✅ Confiance haute',moyenne:'⚠️ Confiance moyenne',faible:'❌ Confiance faible'};
    badge.className = 'ia-badge-confiance ' + (map[ec.confiance] || 'confiance-faible');
    badge.textContent = labels[ec.confiance] || ec.confiance;

    // Infos
    const typeLabels = {facture_achat:'Facture achat',facture_vente:'Facture vente',recu:'Reçu',autre:'Autre document'};
    document.getElementById('ia-doc-type').textContent = typeLabels[ec.document_type] || ec.document_type || '—';
    document.getElementById('ia-tiers').textContent    = ec.fournisseur_client || '—';
    const tiersLabels = {facture_achat:'Fournisseur', facture_vente:'Client', recu:'Émetteur', autre:'Tiers'};
    document.getElementById('ia-tiers-label').textContent = tiersLabels[ec.document_type] || 'Tiers';

    // Tiers linking
    const tierType = ec.document_type === 'facture_vente' ? 'client' : 'fournisseur';
    const tiersLink = document.getElementById('ia-tiers-link');
    const tiersSel  = document.getElementById('tiers-select');
    const btnCreer  = document.getElementById('btn-creer-tiers');
    const linkedBadge = document.getElementById('tiers-linked-badge');

    // Reset
    while (tiersSel.options.length > 1) tiersSel.remove(1);
    linkedBadge.style.display = 'none';
    tiersLink.style.display = 'none';
    window._selectedTiersId = null;

    fetch(APP_URL + '/dossier/tiers/json?id=' + ENT_ID + '&type=' + tierType)
        .then(r => r.json())
        .then(list => {
            list.forEach(t => {
                const o = document.createElement('option');
                o.value = t.id;
                o.textContent = t.nom;
                // Pre-select if name matches detected tiers
                const fcLow = ec.fournisseur_client ? ec.fournisseur_client.toLowerCase() : '';
                const tLow  = t.nom.toLowerCase();
                if (fcLow && (tLow.includes(fcLow) || fcLow.includes(tLow) || tLow.includes(fcLow.substring(0,6)))) {
                    o.selected = true;
                    window._selectedTiersId = t.id;
                    linkedBadge.style.display = 'inline-flex';
                }
                tiersSel.appendChild(o);
            });
            btnCreer.href = APP_URL + '/dossier/tiers/form?id=' + ENT_ID + '&type=' + tierType
                + (ec.fournisseur_client ? '&nom=' + encodeURIComponent(ec.fournisseur_client) : '');
            tiersLink.style.display = 'flex';
        })
        .catch(() => { /* silently skip if no tiers */ });

    tiersSel.addEventListener('change', function() {
        if (this.value) {
            window._selectedTiersId = parseInt(this.value);
            linkedBadge.style.display = 'inline-flex';
        } else {
            window._selectedTiersId = null;
            linkedBadge.style.display = 'none';
        }
    });
    document.getElementById('ia-ht').textContent       = ec.montant_ht ? fmt(ec.montant_ht) + ' FCFA' : '—';
    document.getElementById('ia-tva').textContent      = ec.montant_tva ? fmt(ec.montant_tva) + ' FCFA' : '—';
    document.getElementById('ia-ttc').textContent      = ec.montant_ttc ? fmt(ec.montant_ttc) + ' FCFA' : '—';
    document.getElementById('ia-ref').textContent      = ec.reference || '—';
    document.getElementById('ia-notes-text').textContent = ec.notes || '';

    // En-tête
    document.getElementById('r-date').value        = ec.date || '<?= date('Y-m-d') ?>';
    document.getElementById('r-ref').value         = '';  // N° pièce = auto, ne pas confondre avec N° facture
    document.getElementById('r-num-facture').value = ec.reference || '';
    document.getElementById('r-libelle').value     = ec.libelle || '';

    // Journal — trouver par code
    const jSelect = document.getElementById('r-journal');
    if (ec.journal_code) {
        for (let opt of jSelect.options) { if (opt.value === ec.journal_code) { opt.selected = true; break; } }
    }

    // Lignes
    const tbody = document.getElementById('ia-lignes-tbody');
    while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

    let totalD = 0, totalC = 0;
    (ec.lignes || []).forEach(l => {
        const tr = document.createElement('tr');

        const tdC  = document.createElement('td'); tdC.style.paddingLeft = '20px';
        const tdI  = document.createElement('td');
        const tdD  = document.createElement('td');
        const tdCr = document.createElement('td');

        // Compte — input éditable
        const inputC = document.createElement('input');
        inputC.type = 'text'; inputC.className = 'ec-field'; inputC.value = l.compte || '';
        inputC.style.width = '120px'; inputC.style.fontFamily = 'monospace';
        tdC.appendChild(inputC);

        // Intitulé — résolu depuis plan
        const found = COMPTES.find(c => c.numero === l.compte);
        const intitule = found ? found.intitule : (l.intitule || '');
        const intDiv = document.createElement('div');
        intDiv.textContent = intitule || '—';
        intDiv.style.fontSize = '14px';
        tdI.appendChild(intDiv);
        tr.dataset.intitule = intitule;

        // Débit
        const inputD = document.createElement('input');
        inputD.type = 'number'; inputD.step = '0.01'; inputD.min = '0';
        inputD.className = 'ec-field ec-field-mono'; inputD.value = l.debit || '';
        inputD.placeholder = '0,00'; inputD.style.width = '120px';
        inputD.addEventListener('input', recalcTotaux);
        tdD.style.textAlign = 'right'; tdD.appendChild(inputD);

        // Crédit
        const inputCr = document.createElement('input');
        inputCr.type = 'number'; inputCr.step = '0.01'; inputCr.min = '0';
        inputCr.className = 'ec-field ec-field-mono'; inputCr.value = l.credit || '';
        inputCr.placeholder = '0,00'; inputCr.style.width = '120px';
        inputCr.addEventListener('input', recalcTotaux);
        tdCr.style.textAlign = 'right'; tdCr.appendChild(inputCr);

        tr.appendChild(tdC); tr.appendChild(tdI); tr.appendChild(tdD); tr.appendChild(tdCr);
        tbody.appendChild(tr);

        totalD += parseFloat(l.debit  || 0);
        totalC += parseFloat(l.credit || 0);
    });

    document.getElementById('ia-total-debit').textContent  = fmt(totalD);
    document.getElementById('ia-total-credit').textContent = fmt(totalC);
    updateStatusBar(totalD, totalC);
}

function recalcTotaux() {
    let totalD = 0, totalC = 0;
    document.querySelectorAll('#ia-lignes-tbody [type="number"]').forEach((inp, i) => {
        if (i % 2 === 0) totalD += parseFloat(inp.value) || 0;
        else             totalC += parseFloat(inp.value) || 0;
    });
    document.getElementById('ia-total-debit').textContent  = fmt(totalD);
    document.getElementById('ia-total-credit').textContent = fmt(totalC);
    updateStatusBar(totalD, totalC);
}

function updateStatusBar(totalD, totalC) {
    document.getElementById('sb-debit').textContent  = fmt(totalD);
    document.getElementById('sb-credit').textContent = fmt(totalC);
    const balanced = Math.abs(totalD - totalC) < 0.01;
    const hasAmt   = totalD > 0 || totalC > 0;
    const icon = document.getElementById('sb-status-icon');
    if (balanced && hasAmt) { icon.textContent = '✅ Équilibrée'; icon.className = 'sb-ok'; }
    else if (!hasAmt)       { icon.textContent = '— Aucun montant'; icon.className = ''; }
    else { icon.textContent = '⚠️ Déséquilibrée (' + fmt(Math.abs(totalD-totalC)) + ')'; icon.className = 'sb-ko'; }
    document.getElementById('btn-valider-scan').disabled = !(balanced && hasAmt);
}

/* ── Validation ── */
function validerEcriture() {
    if (!iaData) return;

    // Construire les lignes depuis le tableau éditable
    const lignes = [];
    const rows = document.querySelectorAll('#ia-lignes-tbody tr');
    rows.forEach(tr => {
        const inputs = tr.querySelectorAll('input');
        if (inputs.length >= 3) {
            lignes.push({
                compte   : inputs[0].value.trim(),
                intitule : tr.dataset.intitule || '',
                debit    : parseFloat(inputs[1].value) || 0,
                credit   : parseFloat(inputs[2].value) || 0,
            });
        }
    });

    const payload = {
        entreprise_id      : ENT_ID,
        journal_code       : document.getElementById('r-journal').value,
        date               : document.getElementById('r-date').value,
        reference          : document.getElementById('r-ref').value,
        numero_facture     : document.getElementById('r-num-facture').value,
        libelle            : document.getElementById('r-libelle').value,
        fournisseur_client : iaData.fournisseur_client || '',
        tiers_id           : window._selectedTiersId || null,
        piece_jointe       : iaPieceJointe,
        lignes             : lignes,
    };

    document.getElementById('btn-valider-scan').disabled = true;
    document.getElementById('btn-valider-scan').textContent = '⏳ Enregistrement…';

    fetch(APP_URL + '/scan-ia/valider', {
        method  : 'POST',
        headers : {'Content-Type':'application/json'},
        body    : JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showError(data.error); return; }
        // Étape 3
        document.getElementById('result-section').style.display = 'none';
        document.getElementById('section-success').style.display = 'block';
        document.getElementById('status-bar').style.display = 'none';
        setStep(3);
    })
    .catch(() => showError('Erreur réseau'));
}
window.validerEcriture = validerEcriture;

/* ── Reset ── */
window.resetAll = function() {
    iaData = null; iaPieceJointe = null; currentFile = null;
    document.getElementById('scan-file').value = '';
    document.getElementById('section-upload').style.display = 'block';
    document.getElementById('upload-zone').style.display = 'block';
    document.getElementById('upload-preview').style.display = 'none';
    document.getElementById('btn-analyser').disabled = true;
    document.getElementById('section-loading').style.display = 'none';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('section-success').style.display = 'none';
    document.getElementById('status-bar').style.display = 'none';
    setStep(1);
};

function setStep(n) {
    [1,2,3].forEach(i => {
        const el = document.getElementById('step-'+i);
        el.classList.remove('active','done');
        if (i < n) el.classList.add('done');
        else if (i === n) el.classList.add('active');
    });
}

function showError(msg) {
    document.getElementById('section-upload').style.display = 'block';
    document.getElementById('section-loading').style.display = 'none';
    setStep(1);
    const div = document.createElement('div');
    div.style.cssText = 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:13px 16px;color:var(--danger);margin-bottom:14px;font-size:14px;display:flex;align-items:center;gap:8px';
    div.textContent = '⚠️ ' + msg;
    document.getElementById('scan-wrap').prepend(div);
    setTimeout(() => div.remove(), 6000);
}

}());
</script>
