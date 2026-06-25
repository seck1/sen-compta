<div class="page-header">
    <div>
        <div class="page-title">Ressources Humaines</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — <?= count($employes) ?> employé(s)</div>
    </div>
    <a href="<?= APP_URL ?>/dossier/rh/creer?id=<?= $entreprise['id'] ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nouvel employé
    </a>
</div>

<?php if(isset($_GET['created'])): ?>
<div class="alert-success" style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.25);border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#1f6e4e;font-size:16px">
    Employé créé avec succès.
</div>
<?php endif; ?>

<!-- Filtres -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;align-items:flex-end">
    <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
    <div class="form-field">
        <label>Statut</label>
        <select name="statut" onchange="this.form.submit()">
            <option value="">Tous</option>
            <option value="actif" <?= ($_GET['statut']??'')==='actif'?'selected':'' ?>>Actif</option>
            <option value="suspendu" <?= ($_GET['statut']??'')==='suspendu'?'selected':'' ?>>Suspendu</option>
            <option value="licencie" <?= ($_GET['statut']??'')==='licencie'?'selected':'' ?>>Licencié</option>
        </select>
    </div>
</form>

<div class="table-wrap">
    <div class="table-header">
        <span class="table-title">Liste des employés</span>
        <a href="<?= APP_URL ?>/dossier/rh/bulletins?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Bulletins de paie</a>
    </div>
    <?php if(empty($employes)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        <h3>Aucun employé</h3>
        <p>Commencez par créer un premier employé.</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom & Prénom</th>
                <th>Poste</th>
                <th>Département</th>
                <th>Contrat</th>
                <th style="text-align:right">Salaire de base</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($employes as $emp): ?>
            <tr>
                <td><code style="font-size:15px;background:var(--bg);padding:2px 7px;border-radius:5px"><?= e($emp['matricule']) ?></code></td>
                <td>
                    <div style="font-weight:500"><?= e($emp['nom'].' '.$emp['prenom']) ?></div>
                    <div style="font-size:14px;color:var(--text-muted)"><?= e($emp['sexe']=='F'?'Femme':'Homme') ?></div>
                </td>
                <td><?= e($emp['poste']) ?></td>
                <td><?= e($emp['departement']) ?></td>
                <td><span class="badge badge-info"><?= e($emp['type_contrat']) ?></span></td>
                <td style="text-align:right;font-family:monospace;font-weight:500"><?= formatMontant($emp['salaire_base']) ?></td>
                <td>
                    <?php
                    $sb = ['actif'=>'badge-success','suspendu'=>'badge-warning','licencie'=>'badge-danger'];
                    ?>
                    <span class="badge <?= $sb[$emp['statut']] ?? 'badge-navy' ?>"><?= ucfirst($emp['statut']) ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/dossier/rh/employe?id=<?= $entreprise['id'] ?>&employe_id=<?= $emp['id'] ?>" class="btn btn-outline btn-sm">Fiche</a>
                        <a href="<?= APP_URL ?>/dossier/rh/edit?id=<?= $entreprise['id'] ?>&employe_id=<?= $emp['id'] ?>" class="btn btn-outline btn-sm">Éditer</a>
                        <a href="<?= APP_URL ?>/dossier/rh/bulletin/creer?id=<?= $entreprise['id'] ?>&employe_id=<?= $emp['id'] ?>" class="btn btn-sm" style="background:rgba(201,169,110,0.1);color:var(--gold-dark);border:1px solid rgba(201,169,110,0.3)">Bulletin</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
