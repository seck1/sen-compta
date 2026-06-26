<style>
/* Bouton Scan IA — look IA premium (dégradé animé, brillance, étincelle) */
.btn-scan-ia {
    position:relative; overflow:hidden;
    display:flex; align-items:center; gap:9px;
    padding:10px 20px; border:none; border-radius:12px;
    background:linear-gradient(135deg,#0f6fba 0%,#0891b2 50%,#1f6e4e 100%);
    background-size:200% 100%;
    color:#fff; font-size:14px; font-weight:700; letter-spacing:.3px;
    text-decoration:none; cursor:pointer;
    box-shadow:0 6px 18px rgba(15,111,186,.4), inset 0 1px 0 rgba(255,255,255,.2);
    transition:all .25s cubic-bezier(.4,0,.2,1);
    animation:scanIaGrad 5s ease infinite;
}
@keyframes scanIaGrad { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
.btn-scan-ia::before {
    content:''; position:absolute; top:0; left:-100%; width:55%; height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.4),transparent);
    transition:left .6s ease;
}
.btn-scan-ia:hover::before { left:160%; }
.btn-scan-ia:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(15,111,186,.55), inset 0 1px 0 rgba(255,255,255,.3); }
.btn-scan-ia svg { animation:scanIaSpark 2.2s ease-in-out infinite; }
@keyframes scanIaSpark { 0%,100%{transform:scale(1) rotate(0)} 50%{transform:scale(1.2) rotate(10deg)} }
.btn-scan-ia-badge {
    background:rgba(255,255,255,0.25); padding:1px 8px; border-radius:20px;
    font-size:13px; font-weight:800; letter-spacing:.5px;
    box-shadow:inset 0 0 0 1px rgba(255,255,255,.2);
}

/* Tableau des écritures : compact pour tenir sur toute la largeur sans scroll horizontal */
.ecr-table { width: 100%; table-layout: fixed; }
.ecr-table thead th { padding: 9px 7px; font-size: 8.5pt; letter-spacing: .3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
/* Compacte uniquement les lignes principales (pas la ligne de détail dépliée) */
.ecr-table > tbody > tr:not([id^="detail-"]) > td { padding: 9px 7px; font-size: 10pt; overflow: hidden; text-overflow: ellipsis; }
/* Le libellé absorbe l'espace restant et tronque proprement */
.ecr-table td:nth-child(6) > div { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
/* Colonne Actions : boutons compacts empilés, non tronqués */
.ecr-table > tbody > tr:not([id^="detail-"]) > td:last-child { overflow: visible; padding: 6px; }
.ecr-table > tbody > tr:not([id^="detail-"]) > td:last-child > div { flex-direction: column; gap: 4px !important; align-items: stretch !important; }
.ecr-table > tbody > tr:not([id^="detail-"]) > td:last-child a,
.ecr-table > tbody > tr:not([id^="detail-"]) > td:last-child button {
    padding: 4px 8px !important; font-size: 12px !important; white-space: nowrap;
    justify-content: center; width: 100%;
}
/* La ligne de détail dépliée s'affiche normalement, pleine largeur */
.ecr-table tr[id^="detail-"] > td { padding: 0; overflow: visible; }
@media (min-width: 1100px) {
    /* Sur grand écran, plus de scroll : tout rentre */
    .table-wrap:has(.ecr-table) { overflow-x: visible; }
}
</style>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Écritures comptables</h1>
        <p class="page-subtitle"><?= count($ecritures) ?> écriture<?= count($ecritures)>1?'s':'' ?> · Exercice <?= $exercice ?></p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <?php if (count($exercicesDispos) > 1): ?>
        <select onchange="location.href='<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>&exercice='+this.value" style="padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;cursor:pointer">
            <?php foreach ($exercicesDispos as $ex): ?>
            <option value="<?= $ex ?>" <?= $ex==$exercice?'selected':'' ?>>Exercice <?= $ex ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/dossier/ecriture-scan?id=<?= $entreprise['id'] ?>" class="btn-scan-ia">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
            <span>Scan <span class="btn-scan-ia-badge">IA</span></span>
        </a>
        <a href="<?= APP_URL ?>/export/ecritures?id=<?= $entreprise['id'] ?>&exercice=<?= $exercice ?>" class="btn btn-outline btn-sm" title="Exporter en Excel/CSV">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export CSV
        </a>
        <button type="button" onclick="document.getElementById('modal-an').style.display='flex'" class="btn btn-outline btn-sm">
            ↩ Report AN
        </button>
        <a href="<?= APP_URL ?>/dossier/nouvelle-ecriture?id=<?= $entreprise['id'] ?>" class="btn btn-ent">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouvelle écriture
        </a>
    </div>
</div>

<!-- Modal Report AN -->
<div id="modal-an" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:white;border-radius:16px;padding:28px;width:380px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <h3 style="margin:0 0 16px;font-size:13px;color:var(--navy-dark)">Générer les reports à nouveau</h3>
        <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px">Reporte les soldes de clôture de l'exercice précédent en début d'exercice.</p>
        <form method="POST" action="<?= APP_URL ?>/dossier/report-an">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text-muted)">EXERCICE À OUVRIR</label>
                <select name="exercice" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
                    <?php foreach ($exercicesDispos as $ex): ?>
                    <option value="<?= $ex ?>" <?= $ex==$exercice?'selected':'' ?>><?= $ex ?></option>
                    <?php endforeach; ?>
                    <?php
                    $nextEx = max($exercicesDispos) + 1;
                    echo "<option value=\"$nextEx\">$nextEx (nouvel exercice)</option>";
                    ?>
                </select>
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary" style="flex:1">Générer</button>
                <button type="button" onclick="document.getElementById('modal-an').style.display='none'" class="btn btn-outline" style="flex:1">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Filtre journaux -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php $exParam = isset($_GET['exercice']) ? '&exercice='.(int)$_GET['exercice'] : ''; ?>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?><?= $exParam ?>"
       class="btn btn-sm <?= !isset($_GET['journal']) ? 'btn-primary' : 'btn-outline' ?>">Tous</a>
    <?php foreach ($journaux as $j): ?>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>&journal=<?= $j['code'] ?><?= $exParam ?>"
       class="btn btn-sm <?= ($_GET['journal'] ?? '') === $j['code'] ? 'btn-primary' : 'btn-outline' ?>">
        <?= e($j['code']) ?>
    </a>
    <?php endforeach; ?>
</div>

<?php
$nbBrouillons = count(array_filter($ecritures, fn($e) => $e['statut'] === 'brouillon'));
$filtreStatut = $_GET['statut'] ?? '';
?>

<!-- Barre actions groupées -->
<?php if ($nbBrouillons > 0): ?>
<div style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.25);border-radius:12px;margin-bottom:16px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    <span style="font-size:14px;color:#92400e;font-weight:500"><?= $nbBrouillons ?> écriture<?= $nbBrouillons>1?'s':'' ?> en brouillon</span>
    <button onclick="validerTout()" style="padding:6px 14px;background:#f59e0b;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;margin-left:auto">
        ✓ Valider tout (<?= $nbBrouillons ?>)
    </button>
</div>
<?php endif; ?>

<!-- Filtre statut -->
<div style="display:flex;gap:6px;margin-bottom:12px">
    <?php foreach ([''=> 'Tous', 'brouillon'=>'Brouillons', 'validee'=>'Validées', 'rejetee'=>'Rejetées'] as $val=>$lbl): ?>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?><?= isset($_GET['journal'])? '&journal='.urlencode($_GET['journal']) : '' ?><?= $exParam ?>&statut=<?= $val ?>"
       class="btn btn-sm <?= $filtreStatut===$val?'btn-primary':'btn-outline' ?>"><?= $lbl ?></a>
    <?php endforeach; ?>
</div>

<!-- Barre de recherche -->
<div style="margin-bottom:12px">
    <div style="position:relative;max-width:400px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text-muted);pointer-events:none"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" /></svg>
        <input type="text" id="search-ecritures" placeholder="Rechercher libellé, tiers, N° pièce..." oninput="filtrerEcritures(this.value)"
            style="width:100%;padding:8px 12px 8px 36px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;outline:none;transition:border .2s"
            onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--border)'">
    </div>
</div>

<div class="table-wrap">
    <div class="table-header">
        <div class="table-title">Liste des écritures</div>
        <span style="font-size:13px;color:var(--text-muted)" id="count-ecritures"><?= count($ecritures) ?> écriture<?= count($ecritures)>1?'s':'' ?></span>
    </div>
    <?php if (empty($ecritures)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
        <h3>Aucune écriture</h3>
        <p>Commencez la saisie comptable</p>
    </div>
    <?php else: ?>
    <table class="ecr-table">
        <thead>
            <tr>
                <th style="width:26px"></th>
                <th style="width:88px">Date</th><th style="width:96px">N° pièce</th><th style="width:80px">N° Facture</th><th style="width:78px">Journal</th><th style="width:230px">Libellé</th><th style="width:96px">Tiers</th>
                <th style="text-align:right;width:84px">Débit</th><th style="text-align:right;width:84px">Crédit</th>
                <th style="width:50px;text-align:center">Lignes</th><th style="text-align:center;width:72px">Justif.</th><th style="width:80px">Statut</th><th style="width:70px">Saisie</th><th style="width:124px">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ecritures as $ec): ?>
        <?php $lignesEc = $lignesParEcriture[$ec['id']] ?? []; ?>
        <tr id="row-<?= $ec['id'] ?>" data-search="<?= strtolower(e($ec['libelle']) . ' ' . e($ec['nom_tiers'] ?? '') . ' ' . e($ec['numero_piece'] ?? '') . ' ' . e($ec['numero_facture'] ?? '')) ?>" style="<?= $ec['statut']==='brouillon'?'background:rgba(245,158,11,0.03)':($ec['statut']==='rejetee'?'background:rgba(239,68,68,0.03)':'') ?>;cursor:pointer" onclick="toggleLignes(<?= $ec['id'] ?>, event)">
            <td style="text-align:center;width:32px;padding:0 8px" onclick="toggleLignes(<?= $ec['id'] ?>, event)">
                <span id="arrow-<?= $ec['id'] ?>" style="display:inline-block;transition:transform .2s;font-size:13px;color:var(--text-muted)">▶</span>
            </td>
            <td style="font-size:14px;white-space:nowrap"><?= date('d/m/Y', strtotime($ec['date_ecriture'])) ?></td>
            <td style="font-size:14px;font-family:monospace;color:var(--text-muted)"><?= e($ec['numero_piece'] ?? '—') ?></td>
            <td style="font-size:14px;font-family:monospace;color:var(--text-muted);white-space:nowrap"><?= e($ec['numero_facture'] ?? '—') ?></td>
            <td>
                <span class="badge badge-navy"><?= e($ec['journal_code']) ?></span>
                <?php if (!empty($ec['moyen_paiement'])): ?>
                <?php
                    $mpLabels = ['virement'=>'Virement','cheque'=>'Chèque','especes'=>'Espèces','orange_money'=>'Orange Money','wave'=>'Wave','free_money'=>'Free Money','carte'=>'Carte','autre'=>'Autre'];
                    $mpColors = ['virement'=>'#2563eb','cheque'=>'#b8923f','especes'=>'#1f6e4e','orange_money'=>'#ea580c','wave'=>'#0284c7','free_money'=>'#dc2626','carte'=>'#0891b2','autre'=>'#6b7280'];
                    $mpKey = $ec['moyen_paiement'];
                    $mpLabel = $mpLabels[$mpKey] ?? $mpKey;
                    $mpColor = $mpColors[$mpKey] ?? '#6b7280';
                ?>
                <span style="display:inline-block;margin-top:3px;font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:<?= $mpColor ?>18;color:<?= $mpColor ?>;border:1px solid <?= $mpColor ?>33;letter-spacing:.3px;text-transform:uppercase"><?= $mpLabel ?></span>
                <?php endif; ?>
            </td>
            <td>
                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($ec['libelle']) ?>"><?= e($ec['libelle']) ?></div>
                <?php if ($ec['statut']==='rejetee' && !empty($ec['motif_rejet'])): ?>
                <div style="margin-top:2px;font-size:13px;color:#dc2626;font-style:italic;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($ec['motif_rejet']) ?>">✗ <?= e($ec['motif_rejet']) ?></div>
                <?php endif; ?>
            </td>
            <td style="font-size:13px;white-space:nowrap;color:var(--text-muted)">
                <?php if (!empty($ec['nom_tiers'])): ?>
                <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(30,58,95,.06);border:1px solid rgba(30,58,95,.12);border-radius:20px;padding:2px 9px;font-size:14px;color:var(--navy);font-weight:500">
                    <?= e($ec['nom_tiers']) ?>
                </span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td class="montant-debit" style="text-align:right;white-space:nowrap"><?= number_format($ec['total_debit'],0,',',' ') ?></td>
            <td class="montant-credit" style="text-align:right;white-space:nowrap"><?= number_format($ec['total_credit'],0,',',' ') ?></td>
            <td style="text-align:center;color:var(--text-muted);font-size:14px"><?= $ec['nb_lignes'] ?></td>
            <td style="text-align:center">
                <?php if (!empty($ec['piece_jointe'])): ?>
                <?php
                    $pjUrl  = APP_URL . '/public/uploads/justificatifs/' . $ec['piece_jointe'];
                    $pjExt  = strtolower(pathinfo($ec['piece_jointe'], PATHINFO_EXTENSION));
                    $isPdf  = $pjExt === 'pdf';
                    $isImg  = in_array($pjExt, ['jpg','jpeg','png','webp']);
                ?>
                <div style="display:inline-flex;gap:5px;align-items:center">
                    <a href="<?= $pjUrl ?>" target="_blank" title="Ouvrir le justificatif"
                       style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:7px;font-size:14px;font-weight:600;text-decoration:none;<?= $isPdf ? 'background:rgba(239,68,68,.08);color:#dc2626;border:1px solid rgba(239,68,68,.2)' : 'background:rgba(31,110,78,.08);color:#2563eb;border:1px solid rgba(31,110,78,.2)' ?>">
                        <?php if ($isPdf): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        PDF
                        <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        Image
                        <?php endif; ?>
                    </a>
                    <?php if ($isImg): ?>
                    <span style="position:relative;display:inline-block" class="pj-preview-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;cursor:pointer;color:var(--text-muted)" onmouseover="showPjPreview(this,'<?= $pjUrl ?>')" onmouseout="hidePjPreview()"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <span style="color:var(--border);font-size:13px">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($ec['statut']==='validee'): ?>
                <span class="badge badge-success" id="badge-<?= $ec['id'] ?>">✓ Validée</span>
                <?php elseif ($ec['statut']==='rejetee'): ?>
                <span class="badge" id="badge-<?= $ec['id'] ?>" style="background:rgba(239,68,68,0.12);color:#dc2626;border:1px solid rgba(239,68,68,0.3)">✗ Rejeté</span>
                <?php else: ?>
                <span class="badge badge-warning" id="badge-<?= $ec['id'] ?>">⏳ Brouillon</span>
                <?php endif; ?>
            </td>
            <td style="font-size:13px;color:var(--text-muted)"><?= e($ec['prenom'].' '.$ec['nom']) ?></td>
            <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap">
                    <a href="<?= APP_URL ?>/dossier/modifier-ecriture?id=<?= $ec['id'] ?>&ent=<?= $entreprise['id'] ?>" title="Modifier" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(30,58,95,0.07);color:var(--navy);border:1px solid rgba(30,58,95,0.18);border-radius:6px;font-size:14px;font-weight:600;text-decoration:none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Modifier
                    </a>
                <?php if ($ec['statut'] === 'brouillon'): ?>
                    <button onclick="validerEcriture(<?= $ec['id'] ?>)" title="Valider" style="padding:4px 10px;background:rgba(31,110,78,0.1);color:#1f6e4e;border:1px solid rgba(31,110,78,0.3);border-radius:6px;font-size:14px;font-weight:600;cursor:pointer">✓ Valider</button>
                    <?php if (canValiderEcriture()): ?>
                    <button onclick="rejeterEcriture(<?= $ec['id'] ?>)" title="Rejeter" style="padding:4px 10px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.3);border-radius:6px;font-size:14px;font-weight:600;cursor:pointer">✗ Rejeter</button>
                    <?php endif; ?>
                    <button onclick="supprimerEcriture(<?= $ec['id'] ?>)" title="Supprimer" style="padding:4px 8px;background:rgba(239,68,68,0.08);color:#dc2626;border:1px solid rgba(239,68,68,0.2);border-radius:6px;font-size:14px;cursor:pointer">✕</button>
                <?php elseif ($ec['statut'] === 'rejetee'): ?>
                    <?php if (isAdmin()): ?>
                    <button onclick="enBrouillon(<?= $ec['id'] ?>)" title="Remettre en brouillon" style="padding:4px 10px;background:rgba(245,158,11,0.08);color:#92400e;border:1px solid rgba(245,158,11,0.2);border-radius:6px;font-size:14px;cursor:pointer">↩ Brouillon</button>
                    <?php endif; ?>
                <?php elseif ($ec['statut'] === 'validee'): ?>
                    <?php if (!in_array($ec['journal_code'], ['BNQ','CAI','MOB'])): ?>
                    <?php
                        $montantTiers = (float)($ec['montant_tiers'] ?? 0);
                        $dejaRegle    = (float)($ec['deja_regle']    ?? 0);
                        $soldeRestant = max(0, round($montantTiers - $dejaRegle, 2));
                    ?>
                    <?php if ($montantTiers > 0 && $soldeRestant > 0): ?>
                    <button onclick="ouvrirRegler(<?= $ec['id'] ?>, '<?= e(addslashes($ec['libelle'])) ?>', <?= $montantTiers ?>, <?= $soldeRestant ?>)" title="Régler cette facture — Solde restant : <?= number_format($soldeRestant,0,',',' ') ?> FCFA" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(31,110,78,0.08);color:#2563eb;border:1px solid rgba(31,110,78,0.25);border-radius:6px;font-size:14px;font-weight:600;cursor:pointer">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Régler<?= $dejaRegle > 0 ? ' <span style="background:rgba(31,110,78,0.15);padding:1px 5px;border-radius:4px;font-size:13px">partiel</span>' : '' ?>
                    </button>
                    <?php elseif ($montantTiers > 0): ?>
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(22,163,74,0.08);color:#1f6e4e;border:1px solid rgba(22,163,74,0.2);border-radius:6px;font-size:14px;font-weight:600">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                        Soldé
                    </span>
                    <?php endif; ?>
                    <?php else: ?>
                    <button onclick="supprimerReglement(<?= $ec['id'] ?>)" title="Annuler ce règlement et dé-lettrer" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(239,68,68,0.08);color:#dc2626;border:1px solid rgba(239,68,68,0.2);border-radius:6px;font-size:14px;font-weight:600;cursor:pointer">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        Annuler
                    </button>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                    <button onclick="invaliderEcriture(<?= $ec['id'] ?>)" title="Repasser en brouillon" style="padding:4px 10px;background:rgba(245,158,11,0.08);color:#92400e;border:1px solid rgba(245,158,11,0.2);border-radius:6px;font-size:14px;cursor:pointer">↩ Brouillon</button>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
            </td>
        </tr>
        <!-- Ligne de détail accordéon -->
        <tr id="detail-<?= $ec['id'] ?>" style="display:none;background:#f8fafc">
            <td colspan="14" style="padding:0">
                <div style="padding:12px 16px 16px 48px;overflow-x:auto">
                    <table style="width:100%;min-width:560px;border-collapse:collapse;font-size:13px">
                        <thead>
                            <tr style="background:#f1f5f9">
                                <th style="padding:6px 10px;text-align:left;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f;background:#f1f5f9;color:#4a554f;border-bottom:1px solid #e2e8f0">N° Compte</th>
                                <th style="padding:6px 10px;text-align:left;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f;background:#f1f5f9;color:#4a554f;border-bottom:1px solid #e2e8f0">Intitulé</th>
                                <th style="padding:6px 10px;text-align:left;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f;background:#f1f5f9;color:#4a554f;border-bottom:1px solid #e2e8f0">Libellé ligne</th>
                                <th style="padding:6px 10px;text-align:right;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f;background:#f1f5f9;color:#4a554f;border-bottom:1px solid #e2e8f0">Débit</th>
                                <th style="padding:6px 10px;text-align:right;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f;background:#f1f5f9;color:#4a554f;border-bottom:1px solid #e2e8f0">Crédit</th>
                                <th style="padding:6px 10px;text-align:center;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f;background:#f1f5f9;color:#4a554f;border-bottom:1px solid #e2e8f0">Lettrage</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lignesEc as $lg): ?>
                        <tr style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:7px 10px;font-weight:700;color:var(--navy);font-family:monospace;font-size:13px"><?= e($lg['compte_numero']) ?></td>
                            <td style="padding:7px 10px;color:#374151"><?= e($lg['compte_intitule']) ?></td>
                            <td style="padding:7px 10px;color:var(--text-muted)"><?= e($lg['libelle']) ?></td>
                            <td style="padding:7px 10px;text-align:right;font-weight:600;color:<?= $lg['debit'] > 0 ? '#dc2626' : 'var(--text-muted)' ?>">
                                <?= $lg['debit'] > 0 ? number_format($lg['debit'], 2, ',', ' ') : '—' ?>
                            </td>
                            <td style="padding:7px 10px;text-align:right;font-weight:600;color:<?= $lg['credit'] > 0 ? '#1f6e4e' : 'var(--text-muted)' ?>">
                                <?= $lg['credit'] > 0 ? number_format($lg['credit'], 2, ',', ' ') : '—' ?>
                            </td>
                            <td style="padding:7px 10px;text-align:center">
                                <?php if (!empty($lg['code_lettrage'])): ?>
                                <span style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:4px;padding:1px 7px;font-size:14px;font-weight:700"><?= e($lg['code_lettrage']) ?></span>
                                <?php else: ?>
                                <span style="color:var(--border)">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:#f1f5f9;font-weight:700">
                                <td colspan="3" style="padding:6px 10px;font-size:14px;color:var(--navy-dark)">Totaux</td>
                                <td style="padding:6px 10px;text-align:right;color:#dc2626"><?= number_format(array_sum(array_column($lignesEc,'debit')), 2, ',', ' ') ?></td>
                                <td style="padding:6px 10px;text-align:right;color:#1f6e4e"><?= number_format(array_sum(array_column($lignesEc,'credit')), 2, ',', ' ') ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:var(--bg);font-weight:600">
                <td colspan="6" style="padding:12px 18px;font-size:14px;color:var(--navy-dark)">TOTAL</td>
                <td class="montant-debit" style="padding:12px 18px;text-align:right;white-space:nowrap"><?= number_format(array_sum(array_column($ecritures,'total_debit')),0,',',' ') ?></td>
                <td class="montant-credit" style="padding:12px 18px;text-align:right;white-space:nowrap"><?= number_format(array_sum(array_column($ecritures,'total_credit')),0,',',' ') ?></td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<!-- ══ Modal Règlement ══ -->
<div id="regl-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:480px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div>
                <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--navy-dark)">💳 Enregistrer un règlement</h3>
                <p style="margin:4px 0 0;font-size:13px;color:var(--text-muted)">Crée l'écriture de paiement et lettre automatiquement</p>
            </div>
            <button onclick="fermerRegler()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted)">×</button>
        </div>

        <!-- Onglets -->
        <div style="display:flex;gap:6px;margin-bottom:18px">
            <button type="button" id="regl-tab-infos" onclick="reglTab('infos')" style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;font-size:13px;font-weight:700;background:var(--navy);color:#fff;border:2px solid var(--navy);border-radius:8px;cursor:pointer;transition:all .15s;letter-spacing:.3px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                Informations
            </button>
            <button type="button" id="regl-tab-pj" onclick="reglTab('pj')" style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;font-size:13px;font-weight:700;background:#fff;color:var(--text-muted);border:2px solid var(--border);border-radius:8px;cursor:pointer;transition:all .15s;letter-spacing:.3px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                Justificatif
                <span id="regl-pj-badge" style="display:none;background:#1f6e4e;color:#fff;font-size:13px;padding:1px 6px;border-radius:10px;font-weight:700">1</span>
            </button>
        </div>

        <form id="regl-form" enctype="multipart/form-data">
            <input type="hidden" id="regl-ecriture-id">

            <!-- Panneau Informations -->
            <div id="regl-panel-infos">
            <div id="regl-solde-banner" style="display:none;margin-bottom:12px;padding:10px 14px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;font-size:13px;color:#92400e">
                ⚠️ Règlement partiel — Solde restant dû : <strong id="regl-solde-val"></strong> FCFA
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div>
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Montant (FCFA)</label>
                    <input type="number" id="regl-montant" step="1" min="1" required style="width:100%;margin-top:5px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-weight:600;box-sizing:border-box" oninput="verifierMontantRegl()">
                    <div id="regl-montant-err" style="display:none;margin-top:4px;font-size:14px;color:#dc2626;font-weight:600"></div>
                </div>
                <div>
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Date règlement</label>
                    <input type="date" id="regl-date" required style="width:100%;margin-top:5px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Moyen de paiement</label>
                    <select id="regl-mode" onchange="reglAutoCompte()" style="width:100%;margin-top:5px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;box-sizing:border-box">
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
                <div>
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Compte de règlement</label>
                    <select id="regl-compte" style="width:100%;margin-top:5px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;box-sizing:border-box">
                        <?php
                        // Charger tous les comptes 52x et 57x du dossier
                        $dbTmp = getDB();
                        $stmtMp = $dbTmp->prepare("SELECT numero, intitule FROM comptes WHERE entreprise_id=? AND (numero LIKE '52%' OR numero LIKE '57%') ORDER BY numero");
                        $stmtMp->execute([$entreprise['id']]);
                        $comptesRegl = $stmtMp->fetchAll();
                        foreach ($comptesRegl as $cr):
                        ?>
                        <option value="<?= e($cr['numero']) ?>" data-num="<?= e($cr['numero']) ?>"><?= e($cr['numero']) ?> — <?= e($cr['intitule']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1/-1">
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Libellé</label>
                    <input type="text" id="regl-libelle" required style="width:100%;margin-top:5px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
            </div>
            <div style="margin-top:10px;padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:13px;color:#1e40af">
                ℹ️ L'écriture de règlement sera créée et les lignes 401/411 seront lettrées automatiquement.
            </div>
            </div>

            <!-- Panneau Justificatif -->
            <div id="regl-panel-pj" style="display:none">
                <div id="regl-dropzone" onclick="document.getElementById('regl-file').click()" ondragover="event.preventDefault();this.style.borderColor='var(--navy)'" ondragleave="this.style.borderColor='#d1d5db'" ondrop="reglDrop(event)" style="border:2px dashed #d1d5db;border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;transition:border-color .2s;background:#fafafa">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:36px;height:36px;color:#9ca3af;margin:0 auto 10px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <p style="margin:0;font-size:14px;font-weight:600;color:#374151">Cliquez ou glissez un fichier</p>
                    <p style="margin:6px 0 0;font-size:14px;color:#9ca3af">PDF, JPEG, PNG, WEBP · max 5 Mo</p>
                </div>
                <input type="file" id="regl-file" name="justificatif" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none" onchange="reglFileSelected(this)">
                <div id="regl-file-preview" style="display:none;margin-top:12px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;display:none;align-items:center;gap:10px">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#1f6e4e" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    <span id="regl-file-name" style="font-size:14px;font-weight:600;color:#18583f;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
                    <button type="button" onclick="reglRemoveFile()" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;padding:0">×</button>
                </div>
                <p style="margin:12px 0 0;font-size:14px;color:#9ca3af;text-align:center">Le fichier sera attaché comme justificatif à l'écriture de règlement.</p>
            </div>

            <div style="display:flex;gap:10px;margin-top:18px">
                <button type="button" onclick="fermerRegler()" style="flex:1;padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:8px;font-size:14px;cursor:pointer">Annuler</button>
                <button type="submit" id="regl-btn" style="flex:2;padding:10px;background:var(--navy);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Enregistrer le règlement</button>
            </div>
        </form>
    </div>
</div>

<!-- Tooltip preview image justificatif -->
<div id="pj-tooltip" style="display:none;position:fixed;z-index:9999;pointer-events:none;border-radius:10px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.25);border:2px solid #fff">
    <img id="pj-tooltip-img" src="" alt="" style="display:block;max-width:260px;max-height:200px;object-fit:contain;background:#f8fafc">
</div>

<script>
function showPjPreview(el, url) {
    const tip = document.getElementById('pj-tooltip');
    document.getElementById('pj-tooltip-img').src = url;
    tip.style.display = 'block';
    const rect = el.getBoundingClientRect();
    tip.style.left = (rect.left - 280) + 'px';
    tip.style.top  = Math.max(8, rect.top - 50) + 'px';
}
function hidePjPreview() {
    document.getElementById('pj-tooltip').style.display = 'none';
}

const entId = <?= $entreprise['id'] ?>;

function toggleLignes(id, event) {
    // Ne pas déclencher si on clique sur un bouton/lien dans la ligne
    if (event && (event.target.closest('a,button,select,input'))) return;
    const detail = document.getElementById('detail-' + id);
    const arrow  = document.getElementById('arrow-' + id);
    const isOpen = detail.style.display !== 'none';
    detail.style.display = isOpen ? 'none' : 'table-row';
    arrow.style.transform = isOpen ? '' : 'rotate(90deg)';
}

function postAction(url, data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k,v));
    return fetch(url, {method:'POST', body:fd}).then(r=>r.json());
}

function validerEcriture(id) {
    postAction('<?= APP_URL ?>/dossier/valider-ecriture', {ecriture_id:id, entreprise_id:entId, action:'valider'})
        .then(d => {
            if (d.ok) {
                const badge = document.getElementById('badge-'+id);
                if (badge) { badge.className='badge badge-success'; badge.textContent='✓ Validée'; }
                const row = document.getElementById('row-'+id);
                if (row) { row.style.background=''; row.querySelector('div')?.remove(); }
                location.reload();
            }
        });
}

function invaliderEcriture(id) {
    postAction('<?= APP_URL ?>/dossier/valider-ecriture', {ecriture_id:id, entreprise_id:entId, action:'invalider'})
        .then(d => { if (d.ok) location.reload(); });
}

function supprimerEcriture(id) {
    if (!confirm('Supprimer cette écriture brouillon ?')) return;
    postAction('<?= APP_URL ?>/dossier/supprimer-ecriture', {ecriture_id:id, entreprise_id:entId})
        .then(d => {
            if (d.ok) document.getElementById('row-'+id)?.remove();
            else alert(d.error || 'Erreur');
        });
}

function supprimerReglement(id) {
    if (!confirm('Annuler ce règlement ? Le lettrage associé sera automatiquement supprimé.')) return;
    postAction('<?= APP_URL ?>/dossier/supprimer-ecriture', {ecriture_id:id, entreprise_id:entId})
        .then(d => {
            if (d.ok) location.reload();
            else alert(d.error || 'Erreur');
        });
}

function validerTout() {
    if (!confirm('Valider toutes les écritures en brouillon ?')) return;
    postAction('<?= APP_URL ?>/dossier/valider-ecriture', {entreprise_id:entId, action:'valider_tout', exercice:<?= $exercice ?>})
        .then(d => { if (d.ok) location.reload(); });
}

function rejeterEcriture(id) {
    const motif = prompt('Motif du rejet (obligatoire) :');
    if (motif === null) return; // annulé
    postAction('<?= APP_URL ?>/dossier/valider-ecriture', {ecriture_id:id, entreprise_id:entId, action:'rejeter', motif: motif || 'Rejeté'})
        .then(d => {
            if (d.ok) location.reload();
            else alert(d.error || 'Erreur');
        });
}

function enBrouillon(id) {
    if (!confirm('Remettre cette écriture en brouillon ?')) return;
    postAction('<?= APP_URL ?>/dossier/valider-ecriture', {ecriture_id:id, entreprise_id:entId, action:'en_brouillon'})
        .then(d => { if (d.ok) location.reload(); else alert(d.error || 'Erreur'); });
}
</script>

<!-- =================== MODALE SCAN IA =================== -->
<div id="scanIAModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(10,15,30,0.75);backdrop-filter:blur(4px);align-items:center;justify-content:center">
<div style="background:#fff;border-radius:20px;width:700px;max-width:95vw;max-height:92vh;overflow-y:auto;box-shadow:0 32px 100px rgba(79,70,229,0.25),0 8px 30px rgba(0,0,0,.15);position:relative;border:1px solid rgba(15,111,186,0.08)">

  <div style="background:linear-gradient(135deg,#0f6fba,#0891b2);padding:24px 28px;border-radius:20px 20px 0 0;position:relative;overflow:hidden">
    <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:rgba(255,255,255,0.08);border-radius:50%"></div>
    <div style="position:absolute;bottom:-20px;left:20px;width:80px;height:80px;background:rgba(255,255,255,0.05);border-radius:50%"></div>
    <div style="display:flex;justify-content:space-between;align-items:center;position:relative">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          <div style="width:32px;height:32px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:13px">✨</div>
          <div style="font-size:13px;font-weight:700;color:#fff;letter-spacing:-.2px">Nouvelle écriture par scan</div>
        </div>
        <div style="font-size:13px;color:rgba(255,255,255,.6);padding-left:42px">Analysée par Claude AI · OHADA SYSCOHADA Révisé · Sénégal</div>
      </div>
      <button onclick="closeScanIA()" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);cursor:pointer;color:#fff;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;transition:all .2s" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
    </div>
  </div>

  <!-- Steps indicator -->
  <div style="display:flex;align-items:center;padding:18px 28px;gap:4px;border-bottom:1px solid #f3f4f6" id="scan-steps">
    <?php foreach(['Import','Analyse IA','Prévisualisation','Validation'] as $si=>$sl): ?>
    <div style="display:flex;align-items:center;gap:8px;<?= $si<3?'flex:1':'' ?>">
      <div id="sc<?= $si+1 ?>" style="width:30px;height:30px;border-radius:50%;background:<?= $si===0?'linear-gradient(135deg,#0f6fba,#0891b2)':'#f3f4f6' ?>;color:<?= $si===0?'#fff':'#9ca3af' ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;transition:all .3s;box-shadow:<?= $si===0?'0 4px 10px rgba(15,111,186,0.35)':'' ?>"><?= $si+1 ?></div>
      <span id="sl<?= $si+1 ?>" style="font-size:13px;color:<?= $si===0?'#0f6fba':'#9ca3af' ?>;font-weight:<?= $si===0?'700':'400' ?>;white-space:nowrap"><?= $sl ?></span>
      <?php if($si<3): ?><div id="sline<?= $si+1 ?>" style="flex:1;height:2px;background:linear-gradient(90deg,<?= $si===0?'#0f6fba,#e5e7eb':'#e5e7eb,#e5e7eb' ?>);margin:0 6px;border-radius:2px;transition:all .3s"></div><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="padding:0 28px 28px">

    <!-- Step 1: Upload -->
    <div id="sp1">
      <div id="dropz" onclick="document.getElementById('sfile').click()"
           ondragover="event.preventDefault();this.style.borderColor='#0f6fba';this.style.background='#f0f9ff';this.style.transform='scale(1.01)'"
           ondragleave="this.style.borderColor='#93c5fd';this.style.background='#f8fbff';this.style.transform='scale(1)'"
           ondrop="scanDrop(event)"
           style="border:2px dashed #93c5fd;border-radius:18px;padding:52px 24px;text-align:center;cursor:pointer;background:linear-gradient(135deg,#f8fbff,#f0f9ff);transition:all .25s;position:relative;overflow:hidden">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#0f6fba,#0891b2,#38bdf8);border-radius:18px 18px 0 0"></div>
        <div style="width:72px;height:72px;background:linear-gradient(135deg,#eff8ff,#bfdbfe);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;box-shadow:0 4px 16px rgba(15,111,186,0.15)">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#0f6fba" style="width:36px;height:36px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        </div>
        <div style="font-size:13px;font-weight:700;color:#0c2340;margin-bottom:6px">Glissez votre facture ici</div>
        <div style="font-size:14px;color:#6b7280;margin-bottom:20px">ou <span style="color:#0f6fba;font-weight:600;text-decoration:underline">cliquez pour sélectionner</span> un fichier</div>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <span style="background:#eff8ff;padding:5px 14px;border-radius:20px;font-size:14px;font-weight:700;color:#0f6fba;border:1px solid #7dd3fc">JPEG</span>
          <span style="background:#eff8ff;padding:5px 14px;border-radius:20px;font-size:14px;font-weight:700;color:#0f6fba;border:1px solid #7dd3fc">PNG</span>
          <span style="background:#eff8ff;padding:5px 14px;border-radius:20px;font-size:14px;font-weight:700;color:#0f6fba;border:1px solid #7dd3fc">WEBP</span>
          <span style="background:#fef3c7;padding:5px 14px;border-radius:20px;font-size:14px;font-weight:700;color:#d97706;border:1px solid #fcd34d">max 5 Mo</span>
        </div>
      </div>
      <input type="file" id="sfile" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="scanFileSet(this.files[0])">
      <div id="sprev" style="display:none;margin-top:14px;padding:14px;background:#f8fafc;border-radius:12px;align-items:center;gap:14px">
        <img id="sprevimg" src="" style="width:68px;height:68px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;flex-shrink:0">
        <div style="flex:1">
          <div id="sprevname" style="font-weight:600;font-size:14px;color:#1f2937"></div>
          <div id="sprevsize" style="font-size:13px;color:#9ca3af;margin-top:2px"></div>
        </div>
        <button onclick="scanReset()" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:13px">✕</button>
      </div>
      <button id="sbtn" onclick="scanLancer()" disabled
              style="width:100%;margin-top:16px;padding:15px;background:linear-gradient(135deg,#0f6fba,#0891b2);color:#fff;border:2px solid rgba(8,145,178,0.25);border-radius:14px;font-size:13px;font-weight:700;cursor:pointer;opacity:.4;transition:all .25s;box-shadow:0 4px 20px rgba(15,111,186,0.25);letter-spacing:.2px;display:flex;align-items:center;justify-content:center;gap:10px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
        Analyser avec Claude IA →
      </button>
    </div>

    <!-- Step 2: Loading -->
    <div id="sp2" style="display:none;text-align:center;padding:52px 0">
      <div style="width:64px;height:64px;border:4px solid #eff8ff;border-top-color:#0f6fba;border-radius:50%;margin:0 auto 20px;animation:spspin 1s linear infinite"></div>
      <div style="font-size:13px;font-weight:600;color:#1f2937;margin-bottom:8px">Claude analyse votre document...</div>
      <div style="font-size:14px;color:#9ca3af">Extraction · Sélection comptes OHADA · Vérification équilibre</div>
      <style>@keyframes spspin{to{transform:rotate(360deg)}}</style>
    </div>

    <!-- Step 3: Preview -->
    <div id="sp3" style="display:none">
      <div id="spresult"></div>
      <div style="display:flex;gap:10px;margin-top:18px">
        <button onclick="scanGoStep(1)" style="flex:1;padding:12px;background:#f3f4f6;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;color:#374151">← Retour</button>
        <button onclick="scanValider()" id="svbtn" style="flex:2;padding:12px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">✓ Valider et enregistrer</button>
      </div>
    </div>

    <!-- Step 4: Success -->
    <div id="sp4" style="display:none;text-align:center;padding:52px 0">
      <div style="width:72px;height:72px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px">✓</div>
      <div style="font-size:13px;font-weight:700;color:#1f2937;margin-bottom:8px">Écriture enregistrée !</div>
      <div style="font-size:14px;color:#6b7280;margin-bottom:24px">Créée en brouillon · Vérifiez avant validation finale</div>
      <div style="display:flex;gap:10px;justify-content:center">
        <button onclick="closeScanIA();location.reload()" style="padding:12px 24px;background:#1e3a5f;color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer">Voir les écritures</button>
        <button onclick="scanGoStep(1);scanReset()" style="padding:12px 24px;background:#f3f4f6;border:none;border-radius:10px;font-weight:600;cursor:pointer;color:#374151">Nouveau scan</button>
      </div>
    </div>

  </div>
</div>
</div>

<script>
var scanFile=null, scanIaData=null;
function openScanIA(){document.getElementById('scanIAModal').style.display='flex';}
function closeScanIA(){document.getElementById('scanIAModal').style.display='none';}

function scanGoStep(n){
  [1,2,3,4].forEach(function(i){
    document.getElementById('sp'+i).style.display=i===n?'block':'none';
    var c=document.getElementById('sc'+i),l=document.getElementById('sl'+i);
    if(i<=n){c.style.background='#0f6fba';c.style.color='#fff';l.style.color='#0f6fba';l.style.fontWeight='600';}
    else{c.style.background='#e5e7eb';c.style.color='#9ca3af';l.style.color='#9ca3af';l.style.fontWeight='400';}
    if(i<4){var ln=document.getElementById('sline'+i);if(ln)ln.style.background=i<n?'#0f6fba':'#e5e7eb';}
  });
  if(n===2)document.getElementById('sp2').style.display='block';
}

function scanDrop(e){e.preventDefault();if(e.dataTransfer.files[0])scanFileSet(e.dataTransfer.files[0]);}

function scanFileSet(f){
  if(!f)return;
  if(!['image/jpeg','image/png','image/webp'].includes(f.type)){alert('Format non supporté (JPEG/PNG/WEBP)');return;}
  if(f.size>5242880){alert('Fichier trop lourd (max 5 Mo)');return;}
  scanFile=f;
  var r=new FileReader();
  r.onload=function(e){
    document.getElementById('sprevimg').src=e.target.result;
    document.getElementById('sprevname').textContent=f.name;
    document.getElementById('sprevsize').textContent=(f.size/1024).toFixed(0)+' Ko';
    document.getElementById('sprev').style.display='flex';
    document.getElementById('dropz').style.display='none';
    document.getElementById('sbtn').disabled=false;
    document.getElementById('sbtn').style.opacity='1';
  };
  r.readAsDataURL(f);
}

function scanReset(){
  scanFile=null;
  document.getElementById('sprev').style.display='none';
  document.getElementById('dropz').style.display='block';
  document.getElementById('sbtn').disabled=true;
  document.getElementById('sbtn').style.opacity='.4';
  document.getElementById('sfile').value='';
}

function scanLancer(){
  if(!scanFile)return;
  scanGoStep(2);
  var fd=new FormData();
  fd.append('entreprise_id','<?= $entreprise['id'] ?>');
  fd.append('facture',scanFile);
  fetch('<?= APP_URL ?>/scan-ia/analyser',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(j){
      if(j.error){alert('Erreur IA: '+j.error);scanGoStep(1);return;}
      scanIaData=j.ecriture;
      scanRenderPreview(j.ecriture);
      scanGoStep(3);
    })
    .catch(function(e){alert('Erreur réseau: '+e.message);scanGoStep(1);});
}

function scanFmt(n){return new Intl.NumberFormat('fr-SN',{maximumFractionDigits:0}).format(n)+' FCFA';}

function scanRenderPreview(e){
  var confColors={'haute':'background:#dcfce7;color:#1f6e4e','moyenne':'background:#fef9c3;color:#ca8a04','faible':'background:#fee2e2;color:#dc2626'};
  var conf=confColors[e.confiance]||confColors['moyenne'];
  var lignesRows=(e.lignes||[]).map(function(l){
    return '<tr style="border-bottom:1px solid #f3f4f6"><td style="padding:9px 14px;font-family:monospace;font-size:13px;color:#0f6fba;font-weight:700">'+l.compte+'</td><td style="padding:9px 14px;font-size:14px;color:#374151">'+l.intitule+'</td><td style="padding:9px 14px;text-align:right;font-size:14px;color:#059669">'+( l.debit>0?scanFmt(l.debit):'' )+'</td><td style="padding:9px 14px;text-align:right;font-size:14px;color:#dc2626">'+( l.credit>0?scanFmt(l.credit):'' )+'</td></tr>';
  }).join('');
  var equil=e.equilibre
    ? '<div style="margin-top:10px;padding:8px 14px;background:#dcfce7;border-radius:8px;font-size:13px;color:#1f6e4e;font-weight:600">✓ Écriture équilibrée — Débit = Crédit = '+scanFmt(e.total_debit)+'</div>'
    : '<div style="margin-top:10px;padding:8px 14px;background:#fee2e2;border-radius:8px;font-size:13px;color:#dc2626;font-weight:600">⚠ Attention : écriture non équilibrée</div>';
  var notes=e.notes?'<div style="margin-top:10px;padding:10px 14px;background:#fef9c3;border-radius:8px;font-size:13px;color:#92400e">💡 '+e.notes+'</div>':'';
  var html='<div style="background:#f8fafc;border-radius:14px;padding:18px">'
    +'<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">'
    +'<div><div style="font-size:13px;font-weight:700;color:#1f2937">'+e.libelle+'</div>'
    +'<div style="font-size:13px;color:#9ca3af;margin-top:3px">'+e.fournisseur_client+' · '+e.date+' · Réf: '+(e.reference||'—')+'</div></div>'
    +'<span style="'+conf+';padding:4px 12px;border-radius:20px;font-size:14px;font-weight:700">Confiance '+e.confiance+'</span></div>'
    +'<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">'
    +'<div style="background:#fff;border-radius:10px;padding:12px;text-align:center;border:1px solid #e5e7eb"><div style="font-size:13px;color:#9ca3af;text-transform:uppercase">HT</div><div style="font-size:13px;font-weight:700;color:#1f2937;margin-top:4px">'+scanFmt(e.montant_ht)+'</div></div>'
    +'<div style="background:#fff;border-radius:10px;padding:12px;text-align:center;border:1px solid #e5e7eb"><div style="font-size:13px;color:#9ca3af;text-transform:uppercase">TVA 18%</div><div style="font-size:13px;font-weight:700;color:#f59e0b;margin-top:4px">'+scanFmt(e.montant_tva)+'</div></div>'
    +'<div style="background:#1e3a5f;border-radius:10px;padding:12px;text-align:center"><div style="font-size:13px;color:rgba(255,255,255,.6);text-transform:uppercase">TTC</div><div style="font-size:13px;font-weight:700;color:#fff;margin-top:4px">'+scanFmt(e.montant_ttc)+'</div></div>'
    +'</div>'
    +'<div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden">'
    +'<table style="width:100%;border-collapse:collapse">'
    +'<thead><tr style="background:#f8fafc;border-bottom:1px solid #e5e7eb">'
    +'<th style="padding:8px 14px;text-align:left;font-size:13px;background:#f8fafc;background:#f8fafc;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Compte</th>'
    +'<th style="padding:8px 14px;text-align:left;font-size:13px;background:#f8fafc;background:#f8fafc;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Intitulé</th>'
    +'<th style="padding:8px 14px;text-align:right;font-size:13px;color:#059669;text-transform:uppercase;letter-spacing:.5px">Débit</th>'
    +'<th style="padding:8px 14px;text-align:right;font-size:13px;color:#dc2626;text-transform:uppercase;letter-spacing:.5px">Crédit</th>'
    +'</tr></thead>'
    +'<tbody>'+lignesRows+'</tbody>'
    +'<tfoot><tr style="background:#f8fafc;border-top:2px solid #e5e7eb">'
    +'<td colspan="2" style="padding:10px 14px;font-weight:700;font-size:14px">TOTAL</td>'
    +'<td style="padding:10px 14px;text-align:right;font-weight:700;font-size:14px;color:#059669">'+scanFmt(e.total_debit)+'</td>'
    +'<td style="padding:10px 14px;text-align:right;font-weight:700;font-size:14px;color:#dc2626">'+scanFmt(e.total_credit)+'</td>'
    +'</tr></tfoot></table></div>'
    +equil+notes+'</div>';
  document.getElementById('spresult').innerHTML=html;
}

function scanValider(){
  if(!scanIaData)return;
  var btn=document.getElementById('svbtn');
  btn.textContent='Enregistrement...';btn.disabled=true;
  var payload=JSON.parse(JSON.stringify(scanIaData));
  payload.entreprise_id=<?= $entreprise['id'] ?>;
  fetch('<?= APP_URL ?>/scan-ia/valider',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
    .then(function(r){return r.json();})
    .then(function(j){
      if(j.error){alert('Erreur: '+j.error);btn.textContent='✓ Valider et enregistrer';btn.disabled=false;return;}
      scanGoStep(4);
    })
    .catch(function(e){alert('Erreur: '+e.message);btn.textContent='✓ Valider et enregistrer';btn.disabled=false;});
}

document.getElementById('scanIAModal').addEventListener('click',function(e){if(e.target===this)closeScanIA();});

/* ══════════════════════════════════════════
   MODAL RÈGLEMENT
══════════════════════════════════════════ */
let reglMontantMax = 0;
function ouvrirRegler(ecritureId, libelle, montantTtc, soldeRestant) {
    const solde = (soldeRestant !== undefined) ? soldeRestant : montantTtc;
    reglMontantMax = solde;
    document.getElementById('regl-ecriture-id').value  = ecritureId;
    document.getElementById('regl-montant').value      = solde;
    document.getElementById('regl-montant').max        = solde;
    document.getElementById('regl-libelle').value      = 'Règlement — ' + libelle;
    document.getElementById('regl-date').value         = new Date().toISOString().split('T')[0];
    document.getElementById('regl-solde-banner').style.display = 'none';
    document.getElementById('regl-montant-err').style.display  = 'none';
    document.getElementById('regl-btn').disabled = false;
    document.getElementById('regl-mode').value = 'virement';
    reglAutoCompte();
    document.getElementById('regl-modal').style.display = 'flex';
}
function fermerRegler() {
    document.getElementById('regl-modal').style.display = 'none';
    reglRemoveFile();
    reglTab('infos');
}
function reglTab(tab) {
    document.getElementById('regl-panel-infos').style.display = tab === 'infos' ? '' : 'none';
    document.getElementById('regl-panel-pj').style.display    = tab === 'pj'    ? '' : 'none';
    const btnInfos = document.getElementById('regl-tab-infos');
    const btnPj    = document.getElementById('regl-tab-pj');
    if (tab === 'infos') {
        btnInfos.style.background = 'var(--navy)'; btnInfos.style.color = '#fff'; btnInfos.style.borderColor = 'var(--navy)';
        btnPj.style.background    = '#fff';         btnPj.style.color    = 'var(--text-muted)'; btnPj.style.borderColor = 'var(--border)';
    } else {
        btnPj.style.background    = 'var(--navy)'; btnPj.style.color = '#fff'; btnPj.style.borderColor = 'var(--navy)';
        btnInfos.style.background = '#fff';         btnInfos.style.color = 'var(--text-muted)'; btnInfos.style.borderColor = 'var(--border)';
    }
}
function reglFileSelected(input) {
    if (!input.files || !input.files[0]) return;
    const f = input.files[0];
    if (f.size > 5 * 1024 * 1024) { alert('Fichier trop lourd (max 5 Mo)'); input.value=''; return; }
    document.getElementById('regl-file-name').textContent = f.name;
    document.getElementById('regl-file-preview').style.display = 'flex';
    document.getElementById('regl-dropzone').style.display = 'none';
    document.getElementById('regl-pj-badge').style.display = 'inline';
}
function reglRemoveFile() {
    document.getElementById('regl-file').value = '';
    document.getElementById('regl-file-preview').style.display = 'none';
    document.getElementById('regl-dropzone').style.display = '';
    document.getElementById('regl-pj-badge').style.display = 'none';
}
function reglDrop(e) {
    e.preventDefault();
    document.getElementById('regl-dropzone').style.borderColor = '#d1d5db';
    const files = e.dataTransfer.files;
    if (!files || !files[0]) return;
    const dt = new DataTransfer();
    dt.items.add(files[0]);
    const input = document.getElementById('regl-file');
    input.files = dt.files;
    reglFileSelected(input);
}
// Mapping moyen → préfixe compte attendu
const reglCompteMap = {
    virement:     '521',
    cheque:       '521',
    carte:        '521',
    especes:      '571',
    orange_money: '5223',
    wave:         '5224',
    free_money:   '5225',
    autre:        '521',
};
function reglAutoCompte() {
    const mode = document.getElementById('regl-mode').value;
    const prefCible = reglCompteMap[mode] || '521';
    const sel = document.getElementById('regl-compte');
    // Chercher d'abord une correspondance exacte, sinon le meilleur préfixe
    let bestOpt = null, bestLen = 0;
    for (const opt of sel.options) {
        const num = opt.dataset.num || opt.value;
        if (num === prefCible) { bestOpt = opt; break; }
        // correspondance par préfixe décroissant
        for (let l = Math.min(num.length, prefCible.length); l >= 2; l--) {
            if (num.startsWith(prefCible.substring(0, l)) && l > bestLen) {
                bestLen = l; bestOpt = opt;
            }
        }
    }
    if (bestOpt) sel.value = bestOpt.value;
}
function verifierMontantRegl() {
    const val = parseFloat(document.getElementById('regl-montant').value) || 0;
    const err = document.getElementById('regl-montant-err');
    const banner = document.getElementById('regl-solde-banner');
    if (val > reglMontantMax + 0.01) {
        err.textContent = 'Montant supérieur au solde dû (' + reglMontantMax.toLocaleString('fr-FR') + ' FCFA max)';
        err.style.display = 'block';
        document.getElementById('regl-btn').disabled = true;
    } else {
        err.style.display = 'none';
        document.getElementById('regl-btn').disabled = false;
        if (val < reglMontantMax - 0.01 && val > 0) {
            const restant = reglMontantMax - val;
            document.getElementById('regl-solde-val').textContent = restant.toLocaleString('fr-FR');
            banner.style.display = 'block';
        } else {
            banner.style.display = 'none';
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('regl-modal').addEventListener('click', function(e) {
        if (e.target === this) fermerRegler();
    });
    document.getElementById('regl-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('regl-btn');
        btn.disabled = true; btn.textContent = 'Enregistrement…';
        const fd = new FormData();
        fd.append('ecriture_id',      document.getElementById('regl-ecriture-id').value);
        fd.append('entreprise_id',    '<?= $entreprise['id'] ?>');
        fd.append('montant',          document.getElementById('regl-montant').value);
        fd.append('date',             document.getElementById('regl-date').value);
        fd.append('mode',             document.getElementById('regl-mode').value);
        fd.append('compte_reglement', document.getElementById('regl-compte').value);
        fd.append('libelle',          document.getElementById('regl-libelle').value);
        const fileInput = document.getElementById('regl-file');
        if (fileInput.files && fileInput.files[0]) fd.append('justificatif', fileInput.files[0]);
        fetch('<?= APP_URL ?>/dossier/regler-ecriture', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(j => {
            if (j.error) { alert('Erreur: ' + j.error); btn.disabled=false; btn.textContent='Enregistrer le règlement'; return; }
            fermerRegler();
            const msg = '✅ Règlement enregistré (' + j.numero_piece + ')' + (j.lettre ? ' · Lettré ' + j.lettre : '');
            const flash = document.createElement('div');
            flash.style = 'position:fixed;top:20px;right:20px;z-index:9999;background:#1f6e4e;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,.2)';
            flash.textContent = msg;
            document.body.appendChild(flash);
            setTimeout(() => { flash.remove(); location.reload(); }, 2500);
        })
        .catch(err => { alert('Erreur réseau'); btn.disabled=false; btn.textContent='Enregistrer le règlement'; });
    });
});

function filtrerEcritures(q) {
    q = q.toLowerCase().trim();
    const rows = document.querySelectorAll('tbody tr[data-search]');
    let visible = 0;
    rows.forEach(row => {
        const match = !q || row.dataset.search.includes(q);
        row.style.display = match ? '' : 'none';
        // Cacher aussi les lignes de détail associées
        const id = row.id.replace('row-', '');
        const detail = document.getElementById('lignes-' + id);
        if (detail) detail.style.display = 'none';
        if (match) visible++;
    });
    const counter = document.getElementById('count-ecritures');
    if (counter) counter.textContent = visible + ' écriture' + (visible > 1 ? 's' : '');
}
</script>
