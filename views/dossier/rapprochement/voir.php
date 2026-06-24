<?php
$moisNoms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' F';
$periode = ($moisNoms[$rapprochement['periode_mois']] ?? '') . ' ' . $rapprochement['periode_annee'];
$ecart = (float)$rapprochement['ecart'];
$ecartOk = abs($ecart) < 0.01;
$solde_releve = (float)$rapprochement['solde_releve'];
$solde_comptable = (float)$rapprochement['solde_comptable'];

// Charger les lignes du relevé importé
$db = getDB();
$stmtRel = $db->prepare("SELECT * FROM releve_lignes WHERE rapprochement_id=? ORDER BY date_operation, id");
$stmtRel->execute([$rapprochement['id']]);
$releveLignes = $stmtRel->fetchAll(PDO::FETCH_ASSOC);

$nbReleve      = count($releveLignes);
$nbRapproche   = count(array_filter($releveLignes, fn($r) => $r['rapproche']));
$nbNonRapproche = $nbReleve - $nbRapproche;
$pct = $nbReleve > 0 ? round($nbRapproche / $nbReleve * 100) : 0;

// Notifications
$imported = isset($_GET['imported']) ? (int)$_GET['imported'] : null;
$autoMatched = isset($_GET['auto']) ? (int)$_GET['auto'] : null;
$errorMsg = match($_GET['error'] ?? '') {
    'no_file'   => 'Aucun fichier sélectionné.',
    'file_error'=> 'Erreur lecture du fichier.',
    'not_found' => 'Rapprochement introuvable.',
    default     => null
};
?>

<div class="page-header" style="flex-wrap:wrap;gap:12px">
    <div>
        <h1 class="page-title">Rapprochement — <?= e($rapprochement['compte_banque']) ?></h1>
        <p class="page-subtitle">Période : <?= e($periode) ?></p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <!-- Lettrage auto -->
        <?php if ($nbReleve > 0 && $nbNonRapproche > 0): ?>
        <form method="post" action="<?= APP_URL ?>/dossier/rapprochement/lettrer-auto" style="display:inline">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="rap_id" value="<?= $rapprochement['id'] ?>">
            <button type="submit" class="btn btn-ent btn-sm" style="background:linear-gradient(135deg,#1e3a5f,#2a5298)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                Lettrage automatique
            </button>
        </form>
        <?php endif; ?>
        <!-- Import CSV -->
        <button onclick="document.getElementById('modal-csv').style.display='flex'" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            Importer relevé CSV
        </button>
        <a href="<?= APP_URL ?>/dossier/rapprochement?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">← Retour</a>
    </div>
</div>

<?php if ($errorMsg): ?>
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#dc2626;font-size:16px">⚠ <?= $errorMsg ?></div>
<?php endif; ?>

<?php if ($imported !== null): ?>
<div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#16a34a;font-size:16px">
    ✓ <?= $imported ?> ligne(s) importée(s) depuis le relevé CSV.
</div>
<?php endif; ?>

<?php if ($autoMatched !== null): ?>
<div style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#2563eb;font-size:16px">
    ✓ Lettrage automatique : <strong><?= $autoMatched ?> correspondance(s)</strong> trouvée(s) (montant exact ± date 3 jours).
</div>
<?php endif; ?>

<!-- KPI Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <div class="kpi-card">
        <div class="kpi-label">Solde relevé</div>
        <div class="kpi-value" style="font-size:18px"><?= $fmt($solde_releve) ?></div>
        <div class="kpi-sub">Document bancaire</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Solde comptable</div>
        <div class="kpi-value" style="font-size:18px"><?= $fmt($solde_comptable) ?></div>
        <div class="kpi-sub"><?= $nbRapproche ?> ligne(s) rapprochée(s)</div>
    </div>
    <div class="kpi-card" style="border:2px solid <?= $ecartOk ? 'rgba(34,197,94,0.4)' : 'rgba(239,68,68,0.4)' ?>">
        <div class="kpi-label">Écart</div>
        <div class="kpi-value" style="font-size:22px;color:<?= $ecartOk ? '#16a34a' : '#dc2626' ?>">
            <?= $ecartOk ? '0 F ✓' : $fmt(abs($ecart)) ?>
        </div>
        <div class="kpi-sub" style="color:<?= $ecartOk ? '#16a34a' : '#dc2626' ?>">
            <?= $ecartOk ? 'Équilibré' : 'À corriger' ?>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Avancement</div>
        <div class="kpi-value" style="font-size:22px"><?= $pct ?>%</div>
        <div style="margin-top:6px;height:5px;background:var(--border);border-radius:3px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct==100?'#16a34a':'#c9a96e' ?>;border-radius:3px;transition:width .4s"></div>
        </div>
    </div>
</div>

<?php if ($ecartOk && $rapprochement['statut'] === 'rapproche'): ?>
<div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:12px;padding:14px 20px;margin-bottom:20px;color:#16a34a;font-size:16px;display:flex;align-items:center;gap:10px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <strong>Rapprochement équilibré</strong> — tous les mouvements concordent parfaitement.
</div>
<?php endif; ?>

<!-- Contenu principal : 2 colonnes -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    <!-- Colonne Gauche : Relevé bancaire -->
    <div>
        <div class="table-wrap">
            <div class="table-header" style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div class="table-title">Relevé bancaire</div>
                    <div style="font-size:15px;color:var(--text-muted);margin-top:2px"><?= $nbReleve ?> ligne(s) · <?= $nbRapproche ?> rapprochée(s)</div>
                </div>
                <?php if ($nbReleve > 0): ?>
                <span style="font-size:14px;padding:3px 10px;border-radius:20px;background:<?= $nbNonRapproche>0?'rgba(245,158,11,0.1)':'rgba(34,197,94,0.1)' ?>;color:<?= $nbNonRapproche>0?'#d97706':'#16a34a' ?>;font-weight:600">
                    <?= $nbNonRapproche > 0 ? $nbNonRapproche . ' non rapprochée(s)' : '✓ Tout rapproché' ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if (empty($releveLignes)): ?>
            <div style="padding:40px 20px;text-align:center;color:var(--text-muted)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;display:block;opacity:.3"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                <div style="font-size:17px;font-weight:500;margin-bottom:6px">Aucun relevé importé</div>
                <div style="font-size:15px">Importez votre relevé bancaire CSV pour commencer</div>
                <button onclick="document.getElementById('modal-csv').style.display='flex'" class="btn btn-ent btn-sm" style="margin-top:14px">Importer CSV</button>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th style="text-align:right">Débit</th>
                        <th style="text-align:right">Crédit</th>
                        <th style="text-align:center;width:36px"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($releveLignes as $rl): ?>
                <tr style="<?= $rl['rapproche'] ? 'background:rgba(34,197,94,0.05)' : '' ?>;opacity:<?= $rl['rapproche'] ? '.7' : '1' ?>">
                    <td style="font-size:15px;white-space:nowrap"><?= date('d/m/Y', strtotime($rl['date_operation'])) ?></td>
                    <td style="font-size:15px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($rl['libelle']) ?>"><?= e($rl['libelle']) ?></td>
                    <td style="text-align:right;font-size:15px;color:#16a34a;font-weight:500"><?= $rl['debit'] > 0 ? $fmt($rl['debit']) : '' ?></td>
                    <td style="text-align:right;font-size:15px;color:#dc2626;font-weight:500"><?= $rl['credit'] > 0 ? $fmt($rl['credit']) : '' ?></td>
                    <td style="text-align:center">
                        <?php if ($rl['rapproche']): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#16a34a" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f59e0b" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Colonne Droite : Écritures comptables -->
    <div>
        <div class="table-wrap">
            <div class="table-header">
                <div class="table-title">Écritures comptables</div>
                <div style="font-size:15px;color:var(--text-muted);margin-top:2px">Compte <?= e($rapprochement['compte_banque']) ?> · <?= e($periode) ?></div>
            </div>

            <?php if (empty($lignes)): ?>
            <div style="padding:40px 20px;text-align:center;color:var(--text-muted)">
                <div style="font-size:17px;font-weight:500">Aucune écriture sur ce compte</div>
                <div style="font-size:15px;margin-top:4px">pour la période sélectionnée</div>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width:36px">Rap.</th>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th style="text-align:right">Débit</th>
                        <th style="text-align:right">Crédit</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lignes as $l): ?>
                <?php $isRap = in_array($l['ligne_id'], $rapIds); ?>
                <tr style="<?= $isRap ? 'background:rgba(34,197,94,0.05);opacity:.7' : '' ?>">
                    <td style="text-align:center">
                        <form method="post" action="<?= APP_URL ?>/dossier/rapprochement/marquer" style="display:inline">
                            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
                            <input type="hidden" name="rap_id" value="<?= $rapprochement['id'] ?>">
                            <input type="hidden" name="ligne_id" value="<?= $l['ligne_id'] ?>">
                            <input type="hidden" name="rapproche" value="<?= $isRap ? 0 : 1 ?>">
                            <button type="submit" style="background:none;border:none;cursor:pointer;padding:4px" title="<?= $isRap ? 'Décocher' : 'Marquer rapproché' ?>">
                                <?php if ($isRap): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#16a34a" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9ca3af" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <?php endif; ?>
                            </button>
                        </form>
                    </td>
                    <td style="font-size:15px;white-space:nowrap"><?= date('d/m/Y', strtotime($l['date_ecriture'])) ?></td>
                    <td style="font-size:15px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($l['ecriture_libelle'] ?: $l['ligne_libelle']) ?>"><?= e($l['ecriture_libelle'] ?: $l['ligne_libelle']) ?></td>
                    <td style="text-align:right;font-size:15px;color:#16a34a"><?= $l['debit'] > 0 ? $fmt($l['debit']) : '' ?></td>
                    <td style="text-align:right;font-size:15px;color:#dc2626"><?= $l['credit'] > 0 ? $fmt($l['credit']) : '' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div id="modal-csv" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.6);align-items:center;justify-content:center">
    <div style="background:var(--bg-card);border-radius:16px;padding:32px;width:500px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,0.4)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:17px;font-weight:700;color:var(--text)">Importer un relevé bancaire CSV</h3>
            <button onclick="document.getElementById('modal-csv').style.display='none'" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:20px;line-height:1">×</button>
        </div>

        <div style="background:rgba(201,169,110,0.08);border:1px solid rgba(201,169,110,0.2);border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:15px;color:var(--text-muted);line-height:1.7">
            <strong style="color:var(--text);display:block;margin-bottom:6px">Format CSV accepté :</strong>
            <div>• Séparateur <code style="background:var(--bg);padding:1px 5px;border-radius:4px">;</code> ou <code style="background:var(--bg);padding:1px 5px;border-radius:4px">,</code></div>
            <div>• Colonnes : <code style="background:var(--bg);padding:1px 5px;border-radius:4px">Date ; Libellé ; Débit ; Crédit</code></div>
            <div>• Ou : <code style="background:var(--bg);padding:1px 5px;border-radius:4px">Date ; Libellé ; Montant</code> (négatif = crédit)</div>
            <div>• Date : <code style="background:var(--bg);padding:1px 5px;border-radius:4px">dd/mm/yyyy</code> ou <code style="background:var(--bg);padding:1px 5px;border-radius:4px">yyyy-mm-dd</code></div>
            <div style="margin-top:8px;color:#f59e0b">⚠ L'import remplace les lignes relevé existantes.</div>
        </div>

        <form method="post" action="<?= APP_URL ?>/dossier/rapprochement/import-csv" enctype="multipart/form-data">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="rap_id" value="<?= $rapprochement['id'] ?>">

            <div id="drop-zone" style="border:2px dashed rgba(201,169,110,0.3);border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;transition:border-color .2s;margin-bottom:18px"
                 onclick="document.getElementById('csv-input').click()"
                 ondragover="event.preventDefault();this.style.borderColor='#c9a96e'"
                 ondragleave="this.style.borderColor='rgba(201,169,110,0.3)'"
                 ondrop="event.preventDefault();this.style.borderColor='rgba(201,169,110,0.3)';handleDrop(event)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#c9a96e" style="width:36px;height:36px;margin:0 auto 10px;display:block;opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                <div style="font-size:17px;color:var(--text);font-weight:500" id="drop-label">Glisser le fichier CSV ici</div>
                <div style="font-size:15px;color:var(--text-muted);margin-top:4px">ou cliquer pour sélectionner</div>
            </div>
            <input type="file" id="csv-input" name="csv_file" accept=".csv,.txt" style="display:none" onchange="document.getElementById('drop-label').textContent=this.files[0]?.name||'Glisser le fichier CSV ici'">

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modal-csv').style.display='none'" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-ent">Importer</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleDrop(e) {
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const inp = document.getElementById('csv-input');
    const dt = new DataTransfer();
    dt.items.add(file);
    inp.files = dt.files;
    document.getElementById('drop-label').textContent = file.name;
}
</script>
