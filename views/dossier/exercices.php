<?php
$id = $entreprise['id'];
?>
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Gestion des exercices</h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/dossier?id=<?= $id ?>" class="btn" style="background:var(--bg);border:1px solid var(--border);color:var(--text);display:flex;align-items:center;gap:7px;font-size:17px;padding:9px 16px;border-radius:10px;text-decoration:none">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour au tableau de bord
    </a>
</div>

<?php if ($saved): ?>
<div style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:rgba(31,110,78,0.08);border:1px solid rgba(31,110,78,0.25);border-radius:10px;font-size:16px;color:#14532d;margin-bottom:18px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;color:#1f6e4e;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Modifications enregistrées avec succès.
</div>
<?php endif; ?>
<?php if ($error === 'invalid'): ?>
<div style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:10px;font-size:16px;color:#7f1d1d;margin-bottom:18px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;color:#ef4444;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
    Données invalides. Veuillez renseigner l'année, la date de début et la date de fin.
</div>
<?php endif; ?>

<!-- Tableau des exercices -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h2 style="font-size:19px;font-weight:600;color:var(--text)">Exercices comptables</h2>
        <span style="font-size:16px;color:var(--text-muted)"><?= count($exercices) ?> exercice<?= count($exercices)>1?'s':'' ?></span>
    </div>
    <?php if (empty($exercices)): ?>
    <div style="padding:40px;text-align:center;color:var(--text-muted);font-size:17px">Aucun exercice créé pour ce dossier.</div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:17px">
        <thead>
            <tr style="background:var(--bg)">
                <th style="padding:11px 22px;text-align:left;font-weight:600;font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Année</th>
                <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Période</th>
                <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Statut</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Écritures</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Validées</th>
                <th style="padding:11px 22px;text-align:right;font-weight:600;font-size:15px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($exercices as $ex): ?>
        <?php $isCourant = ($ex['annee'] == ($entreprise['exercice_courant'] ?? 0)); ?>
        <tr style="border-top:1px solid var(--border)">
            <td style="padding:14px 22px">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-weight:700;font-size:19px;color:var(--text)"><?= e($ex['annee']) ?></span>
                    <?php if ($isCourant): ?>
                    <span style="font-size:14px;font-weight:600;padding:2px 8px;border-radius:6px;background:rgba(201,169,110,0.15);color:var(--gold-dark)">Actif</span>
                    <?php endif; ?>
                </div>
            </td>
            <td style="padding:14px 16px;color:var(--text-muted)">
                <?= e(date('d/m/Y', strtotime($ex['date_debut']))) ?> — <?= e(date('d/m/Y', strtotime($ex['date_fin']))) ?>
            </td>
            <td style="padding:14px 16px">
                <?php if ($ex['statut'] === 'ouvert'): ?>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:8px;font-size:15px;font-weight:600;background:rgba(31,110,78,0.1);color:#18583f">
                    <span style="width:6px;height:6px;border-radius:50%;background:#1f6e4e;display:inline-block"></span>
                    Ouvert
                </span>
                <?php else: ?>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:8px;font-size:15px;font-weight:600;background:rgba(107,114,148,0.1);color:var(--text-muted)">
                    <span style="width:6px;height:6px;border-radius:50%;background:var(--text-muted);display:inline-block"></span>
                    Clôturé
                </span>
                <?php endif; ?>
            </td>
            <td style="padding:14px 16px;text-align:right;font-weight:600;color:var(--text)"><?= number_format($ex['nb_ecritures']) ?></td>
            <td style="padding:14px 16px;text-align:right;color:var(--text-muted)"><?= number_format($ex['nb_validees']) ?></td>
            <td style="padding:14px 22px;text-align:right">
                <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end">
                    <?php if ($ex['statut'] === 'ouvert'): ?>
                        <?php if (!$isCourant): ?>
                        <form method="post" action="<?= APP_URL ?>/dossier/exercice/switch">
                            <input type="hidden" name="entreprise_id" value="<?= $id ?>">
                            <input type="hidden" name="annee" value="<?= $ex['annee'] ?>">
                            <button type="submit" style="padding:6px 13px;border-radius:8px;font-size:16px;font-weight:600;border:1px solid var(--border);background:var(--bg);color:var(--text);cursor:pointer">
                                Activer
                            </button>
                        </form>
                        <?php endif; ?>
                        <?php if (isAdmin()): ?>
                        <button onclick="toggleEditForm(<?= $ex['annee'] ?>)" style="padding:6px 13px;border-radius:8px;font-size:16px;font-weight:600;border:1px solid var(--gold);background:rgba(201,169,110,0.08);color:var(--gold-dark);cursor:pointer">
                            Modifier dates
                        </button>
                        <?php endif; ?>
                    <?php else: ?>
                    <span style="font-size:16px;color:var(--text-muted)">—</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php if ($ex['statut'] === 'ouvert' && isAdmin()): ?>
        <tr id="edit-form-<?= $ex['annee'] ?>" style="display:none;background:rgba(201,169,110,0.04);border-top:1px solid rgba(201,169,110,0.2)">
            <td colspan="6" style="padding:18px 22px">
                <form method="post" action="<?= APP_URL ?>/dossier/exercice/modifier" style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                    <input type="hidden" name="entreprise_id" value="<?= $id ?>">
                    <input type="hidden" name="annee" value="<?= $ex['annee'] ?>">
                    <div>
                        <label style="display:block;font-size:15px;font-weight:600;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em">Date début</label>
                        <input type="date" name="date_debut" value="<?= e($ex['date_debut']) ?>"
                            style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:17px;font-family:'DM Sans',sans-serif;color:var(--text)">
                    </div>
                    <div>
                        <label style="display:block;font-size:15px;font-weight:600;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em">Date fin</label>
                        <input type="date" name="date_fin" value="<?= e($ex['date_fin']) ?>"
                            style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:17px;font-family:'DM Sans',sans-serif;color:var(--text)">
                    </div>
                    <button type="submit" style="padding:9px 18px;border-radius:8px;font-size:17px;font-weight:600;border:none;background:var(--navy);color:white;cursor:pointer">
                        Enregistrer
                    </button>
                    <button type="button" onclick="toggleEditForm(<?= $ex['annee'] ?>)" style="padding:9px 18px;border-radius:8px;font-size:17px;font-weight:600;border:1px solid var(--border);background:var(--bg);color:var(--text);cursor:pointer">
                        Annuler
                    </button>
                </form>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Créer un nouvel exercice -->
<?php if (isAdmin()): ?>
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border)">
        <h2 style="font-size:19px;font-weight:600;color:var(--text)">Créer un nouvel exercice</h2>
    </div>
    <div style="padding:22px">
        <form method="post" action="<?= APP_URL ?>/dossier/exercice/creer" style="display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap">
            <input type="hidden" name="entreprise_id" value="<?= $id ?>">
            <div>
                <label style="display:block;font-size:15px;font-weight:600;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em">Année</label>
                <input type="number" name="annee" min="2000" max="2100" value="<?= date('Y') + 1 ?>"
                    style="width:120px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:17px;font-family:'DM Sans',sans-serif;color:var(--text)">
            </div>
            <div>
                <label style="display:block;font-size:15px;font-weight:600;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em">Date début</label>
                <input type="date" name="date_debut" value="<?= date('Y') + 1 ?>-01-01"
                    style="padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:17px;font-family:'DM Sans',sans-serif;color:var(--text)">
            </div>
            <div>
                <label style="display:block;font-size:15px;font-weight:600;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em">Date fin</label>
                <input type="date" name="date_fin" value="<?= date('Y') + 1 ?>-12-31"
                    style="padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:17px;font-family:'DM Sans',sans-serif;color:var(--text)">
            </div>
            <button type="submit" style="padding:10px 22px;border-radius:8px;font-size:17px;font-weight:600;border:none;background:var(--navy);color:white;cursor:pointer;display:flex;align-items:center;gap:7px">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Créer l'exercice
            </button>
        </form>
        <p style="margin-top:12px;font-size:15px;color:var(--text-muted)">L'exercice créé sera automatiquement activé et les dates peuvent être modifiées à tout moment tant qu'il reste ouvert.</p>
    </div>
</div>
<?php endif; ?>

<script>
function toggleEditForm(annee) {
    var row = document.getElementById('edit-form-' + annee);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>
