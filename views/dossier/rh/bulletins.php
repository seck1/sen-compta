<?php
$mois_noms = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
?>
<div class="page-header">
    <div>
        <div class="page-title">Bulletins de paie</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — <?= count($bulletins) ?> bulletin(s)</div>
    </div>
    <a href="<?= APP_URL ?>/dossier/rh/bulletin/creer?id=<?= $entreprise['id'] ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Générer bulletin
    </a>
</div>

<!-- Filtres -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;align-items:flex-end;flex-wrap:wrap">
    <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
    <div class="form-field">
        <label>Mois</label>
        <select name="mois">
            <option value="">Tous</option>
            <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= ($filtre_mois==$m)?'selected':'' ?>><?= $mois_noms[$m] ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="form-field">
        <label>Année</label>
        <select name="annee">
            <option value="">Toutes</option>
            <?php for($y=date('Y');$y>=date('Y')-5;$y--): ?>
            <option value="<?= $y ?>" <?= ($filtre_annee==$y)?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline btn-sm">Filtrer</button>
</form>

<div class="table-wrap">
    <div class="table-header"><span class="table-title">Liste des bulletins</span></div>
    <?php if(empty($bulletins)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:48px;height:48px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        <h3>Aucun bulletin</h3>
        <p>Cliquez sur "Générer bulletin" pour créer le premier bulletin de paie.</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Employé</th>
                <th>Période</th>
                <th style="text-align:right">Salaire brut</th>
                <th style="text-align:right">Retenues</th>
                <th style="text-align:right">Net à payer</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($bulletins as $b): ?>
            <tr>
                <td><code style="font-size:13px;background:var(--bg);padding:2px 7px;border-radius:5px"><?= e($b['matricule']) ?></code></td>
                <td style="font-weight:500"><?= e($b['nom'].' '.$b['prenom']) ?></td>
                <td><?= $mois_noms[(int)$b['periode_mois']] ?> <?= $b['periode_annee'] ?></td>
                <td style="text-align:right;font-family:monospace"><?= number_format($b['salaire_brut'],0,',',' ') ?></td>
                <td style="text-align:right;font-family:monospace;color:var(--danger)">-<?= number_format($b['total_retenues'],0,',',' ') ?></td>
                <td style="text-align:right;font-family:monospace;font-weight:700;color:var(--navy-dark)"><?= number_format($b['net_a_payer'],0,',',' ') ?></td>
                <td>
                    <?php if($b['statut']==='valide'): ?>
                        <span class="badge badge-success">Validé</span>
                    <?php elseif($b['statut']==='paye'): ?>
                        <span class="badge badge-success">Payé</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Brouillon</span>
                    <?php endif; ?>
                </td>
                <td style="display:flex;gap:6px;align-items:center">
                    <a href="<?= APP_URL ?>/dossier/rh/bulletin?id=<?= $entreprise['id'] ?>&bulletin_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">Voir</a>
                    <?php if($b['statut']==='brouillon'): ?>
                    <button onclick="changerStatut(<?= $b['id'] ?>,'valide')" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;color:#16a34a;font-size:13px;padding:4px 10px;cursor:pointer;font-weight:600">Valider</button>
                    <?php elseif($b['statut']==='valide'): ?>
                    <button onclick="changerStatut(<?= $b['id'] ?>,'paye')" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;color:#2563eb;font-size:13px;padding:4px 10px;cursor:pointer;font-weight:600">Marquer payé</button>
                    <?php endif; ?>
                    <button onclick="supprimerBulletin(<?= $b['id'] ?>)" style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:13px;padding:4px 10px;cursor:pointer;font-weight:600">Supprimer</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
function supprimerBulletin(id) {
    if (!confirm('Supprimer ce bulletin ? Cette action est irréversible.')) return;
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $entreprise['id'] ?>');
    fd.append('bulletin_id', id);
    fetch('<?= APP_URL ?>/dossier/rh/bulletin/supprimer', { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d.ok) location.reload(); });
}
function changerStatut(id, statut) {
    var msg = statut === 'valide' ? 'Valider ce bulletin ? Il ne pourra plus être modifié.' : 'Marquer ce bulletin comme payé ?';
    if (!confirm(msg)) return;
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $entreprise['id'] ?>');
    fd.append('bulletin_id', id);
    fd.append('statut', statut);
    fetch('<?= APP_URL ?>/dossier/rh/bulletin/statut', { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d.ok) location.reload(); else alert(d.error || 'Erreur'); });
}
</script>
