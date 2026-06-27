<div class="page-header">
    <div>
        <div class="page-title" style="font-family:'Cormorant Garamond',serif;font-size:28px">Nouvelle mission</div>
    </div>
    <a href="<?= APP_URL ?>/honoraires/missions" class="btn btn-outline btn-sm">Retour</a>
</div>

<div style="max-width:700px">
    <form method="POST" action="<?= APP_URL ?>/honoraires/mission/store">
        <?= csrfField() ?>
        <div class="card" style="margin-bottom:24px">
            <div class="form-grid">
                <div class="form-field">
                    <label>Référence *</label>
                    <input type="text" name="reference" required placeholder="MISS-2026-001">
                </div>
                <div class="form-field">
                    <label>Client *</label>
                    <select name="entreprise_id" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($entreprises as $ent): ?>
                        <option value="<?= $ent['id'] ?>"><?= e($ent['raison_sociale']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Libellé *</label>
                    <input type="text" name="libelle" required placeholder="Ex: Tenue comptabilité 2026">
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
                    <label>Date de début</label>
                    <input type="date" name="date_debut" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-field">
                    <label>Date de fin prévue</label>
                    <input type="date" name="date_fin_prevue">
                </div>
                <div class="form-field">
                    <label>Budget heures</label>
                    <input type="number" name="budget_heures" value="0" step="0.5" min="0">
                </div>
                <div class="form-field">
                    <label>Taux horaire (FCFA/h)</label>
                    <input type="number" name="taux_horaire" value="0" step="1" min="0">
                </div>
                <div class="form-field">
                    <label>Montant forfait HT (FCFA)</label>
                    <input type="number" name="montant_forfait" value="0" step="1" min="0">
                </div>
                <div class="form-field" style="grid-column:1/-1">
                    <label>Note</label>
                    <textarea name="note" rows="3" placeholder="Périmètre de la mission, objectifs..."></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Créer la mission
        </button>
    </form>
</div>
