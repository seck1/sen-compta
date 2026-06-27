<?php
// Variables depuis ImportBancaireController::index()
// $entreprise, $exercice, $lignes, $stats, $ecritures_dispo, $flash_imported, $flash_error
$lignes_attente     = array_filter($lignes, fn($l) => !$l['rapprochee']);
$lignes_rapprochees = array_filter($lignes, fn($l) => $l['rapprochee']);
// Index ecritures_dispo par id pour lookup rapide
$ec_index = [];
foreach ($ecritures_dispo as $ec) $ec_index[$ec['id']] = $ec;
?>

<!-- ══ En-tête page ══ -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Import Relevé Bancaire</h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> &mdash; Exercice <?= $exercice ?></p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <button type="button" id="btn-auto-rappr"
                onclick="lancerRapprochementAuto()"
                style="display:flex;align-items:center;gap:8px;padding:9px 16px;background:linear-gradient(135deg,#b8860b,#d4a017);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;box-shadow:0 2px 8px rgba(184,134,11,.35)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
            <span id="btn-auto-label">⚡ Rapprochement automatique</span>
        </button>
        <button type="button" onclick="document.getElementById('section-import').scrollIntoView({behavior:'smooth'})"
                class="btn btn-primary" style="display:flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Importer un relevé
        </button>
    </div>
</div>

<!-- ══ Messages flash ══ -->
<?php
$importedCount = isset($flash_imported) ? $flash_imported : (isset($_GET['imported']) ? (int)$_GET['imported'] : null);
if ($importedCount !== null):
?>
<div style="display:flex;align-items:center;gap:10px;padding:13px 18px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;margin-bottom:18px;font-size:14px;color:#18583f;font-weight:500">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <?= (int)$importedCount ?> ligne<?= $importedCount > 1 ? 's' : '' ?> importée<?= $importedCount > 1 ? 's' : '' ?> avec succès.
</div>
<?php endif; ?>
<?php
$errRaw = isset($flash_error) ? $flash_error : ($_GET['error'] ?? null);
if ($errRaw):
    $errMsgs = ['nofile' => 'Aucun fichier sélectionné.', 'badfile' => 'Impossible de lire le fichier — vérifiez son format.'];
    $errMsg  = $errMsgs[$errRaw] ?? 'Une erreur est survenue lors de l\'import.';
?>
<div style="display:flex;align-items:center;gap:10px;padding:13px 18px;background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;margin-bottom:18px;font-size:14px;color:#dc2626;font-weight:500">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
    <?= e($errMsg) ?>
</div>
<?php endif; ?>

<!-- ══ Statistiques (4 cartes) ══ -->
<?php
$nbTotal   = isset($stats) ? $stats['nb_total']       : count($lignes);
$nbRappr   = isset($stats) ? $stats['nb_rapprochees'] : count($lignes_rapprochees);
$nbAttente = isset($stats) ? $stats['nb_en_attente']  : count($lignes_attente);
$soldeImp  = isset($stats) ? $stats['solde_importe']  : array_sum(array_map(fn($l) => $l['sens']==='credit' ? $l['montant'] : -$l['montant'], $lignes));
$soldeClr  = $soldeImp >= 0 ? '#1f6e4e' : '#dc2626';
?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:26px">
    <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="width:42px;height:42px;border-radius:11px;background:rgba(30,58,95,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--navy)" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        </div>
        <div>
            <div style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Total lignes</div>
            <div style="font-size:26px;font-weight:700;color:var(--navy-dark);line-height:1"><?= $nbTotal ?></div>
        </div>
    </div>
    <div style="background:#fff;border:1px solid #86efac;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="width:42px;height:42px;border-radius:11px;background:rgba(22,163,74,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#1f6e4e" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Rapprochées</div>
            <div style="font-size:26px;font-weight:700;color:#1f6e4e;line-height:1"><?= $nbRappr ?></div>
        </div>
    </div>
    <div style="background:#fff;border:1px solid #fcd34d;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="width:42px;height:42px;border-radius:11px;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#d97706" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">En attente</div>
            <div style="font-size:26px;font-weight:700;color:#d97706;line-height:1"><?= $nbAttente ?></div>
        </div>
    </div>
    <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="width:42px;height:42px;border-radius:11px;background:rgba(31,110,78,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#1f6e4e" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Solde importé</div>
            <div style="font-size:13px;font-weight:700;color:<?= $soldeClr ?>;line-height:1.2">
                <?= number_format(abs($soldeImp), 0, ',', ' ') ?> <span style="font-size:13px">FCFA</span>
                <?php if ($soldeImp != 0): ?>
                <span style="font-size:14px;background:<?= $soldeImp > 0 ? 'rgba(22,163,74,0.1)' : 'rgba(220,38,38,0.1)' ?>;color:<?= $soldeClr ?>;padding:2px 6px;border-radius:4px"><?= $soldeImp > 0 ? 'C' : 'D' ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ Zone d'import CSV ══ -->
<div id="section-import" class="card" style="margin-bottom:24px;padding:24px 26px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(30,58,95,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--navy)" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
        </div>
        <div>
            <div style="font-size:13px;font-weight:700;color:var(--navy-dark)">Importer un relevé bancaire CSV</div>
            <div style="font-size:13px;color:var(--text-muted)">Colonnes attendues : Date, Libellé, Débit, Crédit (ou Montant + Sens)</div>
        </div>
    </div>

    <form method="POST" action="<?= APP_URL ?>/dossier/import-bancaire/csv" enctype="multipart/form-data" id="import-form">
        <?= csrfField() ?>
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

        <!-- Drop zone -->
        <div id="drop-zone"
             onclick="document.getElementById('csv-input').click()"
             ondragover="event.preventDefault();document.getElementById('drop-zone').classList.add('dz-over')"
             ondragleave="document.getElementById('drop-zone').classList.remove('dz-over')"
             ondrop="dzDrop(event)"
             style="border:2px dashed var(--border);border-radius:14px;padding:38px 24px;text-align:center;cursor:pointer;background:#fafbfd;transition:border-color .2s,background .2s;margin-bottom:16px">
            <div id="dz-idle">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.3" stroke="#9ca3af" style="width:46px;height:46px;display:block;margin:0 auto 12px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <div style="font-size:14px;font-weight:600;color:#374151;margin-bottom:4px">Glissez votre fichier CSV / TXT ici</div>
                <div style="font-size:13px;color:#9ca3af">ou <span style="color:var(--navy);font-weight:600;text-decoration:underline">cliquez pour sélectionner</span></div>
            </div>
            <div id="dz-selected" style="display:none;align-items:center;justify-content:center;gap:12px">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#1f6e4e" style="width:26px;height:26px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <div style="text-align:left">
                    <div id="dz-fname" style="font-size:14px;font-weight:600;color:#18583f"></div>
                    <div id="dz-fsize" style="font-size:14px;color:var(--text-muted)"></div>
                </div>
                <button type="button" onclick="dzReset(event)" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:22px;line-height:1;margin-left:4px">×</button>
            </div>
        </div>
        <input type="file" id="csv-input" name="csv" accept=".csv,.txt" style="display:none" onchange="dzFileSet(this)">

        <!-- Options avancées (pliables) -->
        <div style="margin-bottom:16px">
            <button type="button" id="opts-toggle" onclick="toggleOpts()"
                    style="background:none;border:none;font-size:13px;color:var(--text-muted);cursor:pointer;display:inline-flex;align-items:center;gap:5px;padding:0;font-family:inherit">
                <svg id="opts-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:12px;height:12px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                Options avancées (séparateur, format de date)
            </button>
            <div id="opts-panel" style="display:none;margin-top:13px;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="display:block;font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">Séparateur de colonnes</label>
                    <select name="separateur" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
                        <option value=";">Point-virgule ; (défaut)</option>
                        <option value=",">Virgule ,</option>
                        <option value="&#9;">Tabulation (TSV)</option>
                        <option value="|">Pipe |</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">Format de date</label>
                    <select name="format" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
                        <option value="auto">Détection automatique</option>
                        <option value="dmy">JJ/MM/AAAA</option>
                        <option value="ymd">AAAA-MM-JJ (ISO)</option>
                        <option value="mdy">MM/JJ/AAAA</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Note encodage + colonnes attendues -->
        <div style="padding:11px 15px;background:rgba(31,110,78,0.05);border:1px solid rgba(31,110,78,0.18);border-radius:9px;font-size:13px;color:#1e40af;margin-bottom:18px;line-height:1.75">
            <strong>Colonnes reconnues automatiquement :</strong>
            <code style="background:rgba(31,110,78,0.1);padding:1px 5px;border-radius:4px;margin:0 2px">Date</code>
            <code style="background:rgba(31,110,78,0.1);padding:1px 5px;border-radius:4px;margin:0 2px">Libellé</code>
            <code style="background:rgba(31,110,78,0.1);padding:1px 5px;border-radius:4px;margin:0 2px">Débit</code>
            <code style="background:rgba(31,110,78,0.1);padding:1px 5px;border-radius:4px;margin:0 2px">Crédit</code>
            — ou colonne unique
            <code style="background:rgba(31,110,78,0.1);padding:1px 5px;border-radius:4px;margin:0 2px">Montant</code>
            (négatif = débit).<br>
            Encodage <strong>UTF-8</strong> ou <strong>ISO-8859-1</strong> détecté automatiquement. La première ligne doit contenir les en-têtes.
        </div>

        <button type="submit" id="import-btn" class="btn btn-primary" disabled
                style="display:inline-flex;align-items:center;gap:8px;opacity:.45;transition:opacity .2s">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Importer le relevé
        </button>
    </form>
</div>

<!-- ══ Tableau des lignes importées ══ -->
<div class="table-wrap">
    <div class="table-header">
        <div class="table-title">Lignes importées</div>
        <div style="display:flex;align-items:center;gap:10px">
            <?php if ($nbAttente > 0): ?>
            <span style="font-size:13px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.3);padding:3px 10px;border-radius:20px;font-weight:600"><?= $nbAttente ?> en attente</span>
            <?php endif; ?>
            <span style="font-size:13px;color:var(--text-muted)"><?= count($lignes) ?> ligne<?= count($lignes) > 1 ? 's' : '' ?></span>
        </div>
    </div>

    <?php if (empty($lignes)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        <h3>Aucune ligne importée</h3>
        <p>Importez votre premier relevé bancaire CSV ci-dessus pour commencer le rapprochement</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:100px">Date</th>
                <th>Libellé</th>
                <th style="text-align:right;width:130px">Débit</th>
                <th style="text-align:right;width:130px">Crédit</th>
                <th style="text-align:center;width:120px">Statut</th>
                <th style="text-align:center;width:90px">Match</th>
                <th style="width:230px">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lignes as $l): ?>
        <tr id="row-<?= $l['id'] ?>" style="<?= $l['rapprochee'] ? 'opacity:.65' : '' ?>">
            <td style="white-space:nowrap;font-size:14px;font-family:monospace;color:var(--text-muted)">
                <?= date('d/m/Y', strtotime($l['date_operation'])) ?>
            </td>
            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px" title="<?= e($l['libelle']) ?>">
                <?= e($l['libelle']) ?>
            </td>
            <td style="text-align:right;font-size:14px;font-family:monospace;font-weight:600;color:<?= $l['sens']==='debit' ? '#dc2626' : 'var(--text-muted)' ?>">
                <?= $l['sens']==='debit' ? number_format($l['montant'], 0, ',', ' ') : '—' ?>
            </td>
            <td style="text-align:right;font-size:14px;font-family:monospace;font-weight:600;color:<?= $l['sens']==='credit' ? '#1f6e4e' : 'var(--text-muted)' ?>">
                <?= $l['sens']==='credit' ? number_format($l['montant'], 0, ',', ' ') : '—' ?>
            </td>
            <td style="text-align:center">
                <?php if ($l['rapprochee']): ?>
                <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#1f6e4e;border:1px solid #86efac;border-radius:20px;padding:3px 10px;font-size:14px;font-weight:700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:11px;height:11px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Rapprochée
                </span>
                <?php else: ?>
                <span style="display:inline-flex;align-items:center;gap:4px;background:#fef3c7;color:#d97706;border:1px solid #fcd34d;border-radius:20px;padding:3px 10px;font-size:14px;font-weight:700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:11px;height:11px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    En attente
                </span>
                <?php endif; ?>
            </td>
            <td style="text-align:center">
                <?php if ($l['rapprochee']): ?>
                    <?php if (($l['match_type'] ?? null) === 'auto'): ?>
                    <span style="display:inline-flex;align-items:center;gap:3px;background:#dcfce7;color:#1f6e4e;border:1px solid #86efac;border-radius:20px;padding:2px 9px;font-size:13px;font-weight:700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:10px;height:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        Auto
                    </span>
                    <?php elseif (($l['match_type'] ?? null) === 'manuel'): ?>
                    <span style="display:inline-flex;align-items:center;gap:3px;background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd;border-radius:20px;padding:2px 9px;font-size:13px;font-weight:700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:10px;height:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Manuel
                    </span>
                    <?php else: ?>
                    <span style="color:var(--text-muted);font-size:13px">—</span>
                    <?php endif; ?>
                <?php else: ?>
                <span style="color:var(--text-muted);font-size:13px">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($l['rapprochee']): ?>
                    <?php
                    $ecLiee  = $ec_index[$l['ecriture_id']] ?? null;
                    $ecLabel = $ecLiee
                        ? date('d/m/Y', strtotime($ecLiee['date_ecriture'])) . ' · ' . mb_strimwidth($ecLiee['libelle'], 0, 35, '…')
                        : ($l['ecriture_id'] ? 'Écriture #' . $l['ecriture_id'] : 'Sans écriture liée');
                    ?>
                    <span style="font-size:14px;color:var(--text-muted);display:inline-block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($ecLabel) ?>">
                        <?= e($ecLabel) ?>
                    </span>
                <?php else: ?>
                <div style="display:flex;align-items:center;gap:6px">
                    <button type="button"
                            onclick="ouvrirRappr(<?= $l['id'] ?>, <?= (float)$l['montant'] ?>, '<?= $l['sens'] ?>')"
                            style="display:inline-flex;align-items:center;gap:4px;padding:5px 11px;background:rgba(30,58,95,0.07);color:var(--navy);border:1px solid rgba(30,58,95,0.2);border-radius:7px;font-size:14px;font-weight:600;cursor:pointer"
                            onmouseover="this.style.background='rgba(30,58,95,0.14)'"
                            onmouseout="this.style.background='rgba(30,58,95,0.07)'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:11px;height:11px"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        Rapprocher
                    </button>
                    <button type="button"
                            onclick="supprimerLigne(<?= $l['id'] ?>)"
                            title="Supprimer cette ligne"
                            style="display:inline-flex;align-items:center;padding:5px 8px;background:rgba(239,68,68,0.07);color:#dc2626;border:1px solid rgba(239,68,68,0.2);border-radius:7px;font-size:13px;cursor:pointer;line-height:1"
                            onmouseover="this.style.background='rgba(239,68,68,0.15)'"
                            onmouseout="this.style.background='rgba(239,68,68,0.07)'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:var(--bg);font-weight:600">
                <td colspan="2" style="padding:11px 18px;font-size:14px;color:var(--navy-dark)">TOTAL</td>
                <td style="padding:11px 18px;text-align:right;font-size:14px;font-family:monospace;color:#dc2626">
                    <?= number_format(array_sum(array_map(fn($l) => $l['sens']==='debit'  ? $l['montant'] : 0, $lignes)), 0, ',', ' ') ?>
                </td>
                <td style="padding:11px 18px;text-align:right;font-size:14px;font-family:monospace;color:#1f6e4e">
                    <?= number_format(array_sum(array_map(fn($l) => $l['sens']==='credit' ? $l['montant'] : 0, $lignes)), 0, ',', ' ') ?>
                </td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<!-- ══ Modal rapprochement ══ -->
<div id="modal-rappr" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:28px 30px;width:540px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
            <div>
                <h3 style="margin:0;font-size:13px;font-weight:700;color:var(--navy-dark)">Rapprocher une écriture</h3>
                <p style="margin:4px 0 0;font-size:13px;color:var(--text-muted)">Associez cette ligne bancaire à une écriture comptable</p>
            </div>
            <button onclick="fermerRappr()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--text-muted);line-height:1;padding:0 0 0 12px">×</button>
        </div>

        <div id="rappr-banner" style="padding:10px 14px;background:#fef9c3;border:1px solid #fcd34d;border-radius:9px;font-size:13px;color:#92400e;margin-bottom:16px">
            <strong>Ligne sélectionnée :</strong> <span id="rappr-info"></span>
        </div>

        <div style="margin-bottom:14px">
            <label style="display:block;font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px">Écriture à associer</label>
            <input type="text" id="rappr-search"
                   placeholder="Filtrer par libellé ou montant…"
                   oninput="filtrerEcritures()"
                   style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;margin-bottom:8px;font-family:inherit;box-sizing:border-box;outline:none"
                   onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--border)'">

            <?php if (empty($ecritures_dispo)): ?>
            <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:14px;border:1px solid var(--border);border-radius:8px;background:#fafafa">
                Aucune écriture disponible pour cet exercice.<br>
                <span style="font-size:14px">Toutes les écritures sont déjà rapprochées, ou aucune écriture validée n'existe.</span>
            </div>
            <?php else: ?>
            <select id="rappr-select" size="7"
                    style="width:100%;border:1px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;padding:4px;background:#fff">
                <?php foreach ($ecritures_dispo as $ec):
                    $total = max((float)$ec['total_debit'], (float)$ec['total_credit']);
                ?>
                <option value="<?= (int)$ec['id'] ?>"
                        data-montant="<?= $total ?>"
                        data-lib="<?= e(strtolower($ec['libelle'])) ?>"
                        style="padding:6px 10px">
                    <?= date('d/m/Y', strtotime($ec['date_ecriture'])) ?>
                    &nbsp;·&nbsp;<?= e(mb_strimwidth($ec['libelle'], 0, 48, '…')) ?>
                    &nbsp;·&nbsp;<?= number_format($total, 0, ',', ' ') ?> FCFA
                </option>
                <?php endforeach; ?>
            </select>
            <div style="font-size:14px;color:var(--text-muted);margin-top:5px">
                Le montant le plus proche de la ligne est présélectionné. Les écritures déjà rapprochées sont exclues.
            </div>
            <?php endif; ?>
        </div>

        <div style="display:flex;gap:10px;margin-top:18px">
            <button type="button" onclick="fermerRappr()"
                    style="flex:1;padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;cursor:pointer">
                Annuler
            </button>
            <button type="button" id="rappr-btn" onclick="validerRappr()"
                    style="flex:2;padding:10px;background:var(--navy);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer"
                    <?= empty($ecritures_dispo) ? 'disabled style="opacity:.45;cursor:not-allowed"' : '' ?>>
                Confirmer le rapprochement
            </button>
        </div>
    </div>
</div>

<!-- Toast succès (réutilisable) -->
<div id="toast-ok" style="display:none;position:fixed;top:20px;right:20px;z-index:9999;background:#1f6e4e;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,.2);align-items:center;gap:8px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
    <span id="toast-msg"></span>
</div>

<!-- Toast rapprochement auto (doré) -->
<div id="toast-auto" style="display:none;position:fixed;top:20px;right:20px;z-index:9999;background:linear-gradient(135deg,#b8860b,#d4a017);color:#fff;padding:14px 22px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(184,134,11,.4);max-width:380px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
        <span style="font-size:14px">Rapprochement automatique terminé</span>
    </div>
    <div id="toast-auto-detail" style="font-size:13px;opacity:.9;line-height:1.6"></div>
</div>

<style>
#drop-zone.dz-over {
    border-color: var(--navy) !important;
    background: rgba(30,58,95,0.03) !important;
}
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script>
const IMP_ENT  = <?= (int)$entreprise['id'] ?>;
const IMP_BASE = '<?= APP_URL ?>';
let rapprId = null;

/* ─── Rapprochement automatique ─── */
function lancerRapprochementAuto() {
    var btn   = document.getElementById('btn-auto-rappr');
    var label = document.getElementById('btn-auto-label');
    btn.disabled = true;
    btn.style.opacity = '.6';
    label.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px;animation:spin .8s linear infinite;display:inline-block"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Analyse en cours…';

    var fd = new FormData();
    fd.append('entreprise_id', IMP_ENT);

    fetch(IMP_BASE + '/dossier/import-bancaire/auto', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.style.opacity = '1';
            label.innerHTML = '⚡ Rapprochement automatique';

            if (!d.ok) { alert('Erreur lors du rapprochement automatique'); return; }

            var detail = '';
            if (d.auto > 0)         detail += '<span style="color:#d4edda">✔ ' + d.auto + ' rapproch' + (d.auto > 1 ? 'ées' : 'ée') + ' automatiquement</span><br>';
            if (d.suggestions > 0)  detail += '<span style="color:#fff3cd">~ ' + d.suggestions + ' suggestion' + (d.suggestions > 1 ? 's' : '') + ' (montant identique)</span><br>';
            if (d.non_trouvees > 0) detail += '<span style="opacity:.8">✗ ' + d.non_trouvees + ' non trouvée' + (d.non_trouvees > 1 ? 's' : '') + '</span>';

            document.getElementById('toast-auto-detail').innerHTML = detail || 'Aucune ligne à rapprocher.';
            var toast = document.getElementById('toast-auto');
            toast.style.display = 'block';

            var delai = d.auto > 0 ? 2500 : 3000;
            setTimeout(function() {
                toast.style.display = 'none';
                if (d.auto > 0) location.reload();
            }, delai);
        })
        .catch(function() {
            btn.disabled = false;
            btn.style.opacity = '1';
            label.innerHTML = '⚡ Rapprochement automatique';
            alert('Erreur réseau lors du rapprochement automatique');
        });
}

function showToast(msg) {
    const t = document.getElementById('toast-ok');
    document.getElementById('toast-msg').textContent = msg;
    t.style.display = 'flex';
    setTimeout(function() { t.style.display = 'none'; location.reload(); }, 1800);
}

/* ─── Drop zone ─── */
function dzFileSet(input) {
    const f = input.files && input.files[0];
    if (!f) return;
    document.getElementById('dz-idle').style.display = 'none';
    const sel = document.getElementById('dz-selected');
    sel.style.display = 'flex';
    document.getElementById('dz-fname').textContent = f.name;
    document.getElementById('dz-fsize').textContent = (f.size / 1024).toFixed(0) + ' Ko';
    document.getElementById('drop-zone').style.borderColor = '#86efac';
    const btn = document.getElementById('import-btn');
    btn.disabled = false;
    btn.style.opacity = '1';
}
function dzDrop(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('dz-over');
    const files = e.dataTransfer.files;
    if (!files || !files[0]) return;
    const dt = new DataTransfer();
    dt.items.add(files[0]);
    const inp = document.getElementById('csv-input');
    inp.files = dt.files;
    dzFileSet(inp);
}
function dzReset(e) {
    e.stopPropagation();
    document.getElementById('csv-input').value = '';
    document.getElementById('dz-idle').style.display = '';
    document.getElementById('dz-selected').style.display = 'none';
    document.getElementById('drop-zone').style.borderColor = '';
    const btn = document.getElementById('import-btn');
    btn.disabled = true;
    btn.style.opacity = '.45';
}

/* ─── Options avancées ─── */
var _optsOpen = false;
function toggleOpts() {
    _optsOpen = !_optsOpen;
    document.getElementById('opts-panel').style.display   = _optsOpen ? 'grid' : 'none';
    document.getElementById('opts-chevron').style.transform = _optsOpen ? 'rotate(90deg)' : '';
}

/* ─── Supprimer une ligne ─── */
function supprimerLigne(id) {
    if (!confirm('Supprimer définitivement cette ligne bancaire ?')) return;
    const fd = new FormData();
    fd.append('entreprise_id', IMP_ENT);
    fd.append('ligne_id', id);
    fetch(IMP_BASE + '/dossier/import-bancaire/supprimer', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                const row = document.getElementById('row-' + id);
                if (row) {
                    row.style.transition = 'opacity .3s';
                    row.style.opacity = '0';
                    setTimeout(function() { row.remove(); }, 300);
                }
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(function() { alert('Erreur réseau'); });
}

/* ─── Rapprochement modal ─── */
function ouvrirRappr(ligneId, montant, sens) {
    rapprId = ligneId;
    var sensLabel = sens === 'debit' ? 'Débit' : 'Crédit';
    document.getElementById('rappr-info').textContent =
        sensLabel + ' · ' + montant.toLocaleString('fr-SN') + ' FCFA';
    document.getElementById('rappr-search').value = '';

    var sel = document.getElementById('rappr-select');
    if (sel) {
        var bestIdx = -1, bestDiff = Infinity;
        var opts = sel.options;
        for (var i = 0; i < opts.length; i++) {
            opts[i].style.display = '';
            var m = parseFloat(opts[i].dataset.montant) || 0;
            var diff = Math.abs(m - montant);
            if (diff < bestDiff) { bestDiff = diff; bestIdx = i; }
        }
        if (bestIdx >= 0) sel.selectedIndex = bestIdx;
    }

    var btn = document.getElementById('rappr-btn');
    if (btn) { btn.disabled = false; btn.textContent = 'Confirmer le rapprochement'; }

    document.getElementById('modal-rappr').style.display = 'flex';
    setTimeout(function() { document.getElementById('rappr-search').focus(); }, 80);
}

function fermerRappr() {
    document.getElementById('modal-rappr').style.display = 'none';
    rapprId = null;
}

function filtrerEcritures() {
    var q   = document.getElementById('rappr-search').value.toLowerCase();
    var sel = document.getElementById('rappr-select');
    if (!sel) return;
    for (var i = 0; i < sel.options.length; i++) {
        var opt = sel.options[i];
        var lib = opt.dataset.lib || opt.textContent.toLowerCase();
        var m   = opt.dataset.montant || '';
        opt.style.display = (!q || lib.includes(q) || m.includes(q)) ? '' : 'none';
    }
}

function validerRappr() {
    var sel  = document.getElementById('rappr-select');
    var ecId = sel && sel.value;
    if (!ecId || !rapprId) { alert('Sélectionnez une écriture.'); return; }

    var btn = document.getElementById('rappr-btn');
    btn.disabled = true;
    btn.textContent = 'Rapprochement en cours…';

    var fd = new FormData();
    fd.append('entreprise_id', IMP_ENT);
    fd.append('ligne_id',      rapprId);
    fd.append('ecriture_id',   ecId);
    fetch(IMP_BASE + '/dossier/import-bancaire/rapprocher', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                fermerRappr();
                showToast('Ligne rapprochée avec succès');
            } else {
                alert('Erreur lors du rapprochement');
                btn.disabled = false;
                btn.textContent = 'Confirmer le rapprochement';
            }
        })
        .catch(function() {
            alert('Erreur réseau');
            btn.disabled = false;
            btn.textContent = 'Confirmer le rapprochement';
        });
}

document.getElementById('modal-rappr').addEventListener('click', function(e) {
    if (e.target === this) fermerRappr();
});
</script>
