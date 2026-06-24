<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Nouvelle mission</h1>
        <p class="page-subtitle">Planifier une mission pour un dossier</p>
    </div>
    <a href="<?= APP_URL ?>/planning" class="btn btn-outline btn-sm">← Retour</a>
</div>

<div class="card" style="max-width:800px">
    <form method="post" action="<?= APP_URL ?>/planning/store">
        <div class="form-grid">
            <div class="form-field">
                <label>Dossier entreprise *</label>
                <select name="entreprise_id" required>
                    <option value="">Sélectionner…</option>
                    <?php foreach ($entreprises as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= e($e['raison_sociale']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (isAdmin()): ?>
            <div class="form-field">
                <label>Collaborateur responsable *</label>
                <select name="user_id" required>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id']==auth()['id']?'selected':'' ?>><?= e($u['prenom'].' '.$u['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-field" style="grid-column:1/-1">
                <label>Libellé de la mission *</label>
                <input type="text" name="libelle" placeholder="Ex: Arrêté des comptes 2024" required>
            </div>
            <div class="form-field">
                <label>Type de mission</label>
                <select name="type">
                    <option value="comptabilite">Comptabilité</option>
                    <option value="audit">Audit</option>
                    <option value="fiscalite">Fiscalité</option>
                    <option value="paie">Paie</option>
                    <option value="conseil">Conseil</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-field">
                <label>Statut initial</label>
                <select name="statut">
                    <option value="planifiee">Planifiée</option>
                    <option value="en_cours">En cours</option>
                </select>
            </div>
            <div class="form-field">
                <label>Date de début *</label>
                <input type="date" name="date_debut" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-field">
                <label>Date de fin prévue</label>
                <input type="date" name="date_fin_prevue">
            </div>
            <div class="form-field">
                <label>Budget heures</label>
                <input type="number" name="budget_heures" step="0.5" placeholder="0">
            </div>
            <div class="form-field">
                <label>Taux horaire (FCFA)</label>
                <input type="number" name="taux_horaire" step="100" value="0">
            </div>
            <div class="form-field">
                <label>Montant forfait (FCFA)</label>
                <input type="number" name="montant_forfait" step="1000" placeholder="Optionnel">
            </div>
            <div class="form-field" style="grid-column:1/-1">
                <label>Notes</label>
                <textarea name="note" placeholder="Remarques, instructions…"></textarea>
            </div>
        </div>
        <div class="form-actions" style="margin-top:24px">
            <button type="submit" class="btn btn-primary">Créer la mission</button>
            <a href="<?= APP_URL ?>/planning" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
