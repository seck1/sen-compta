<?php
// $entreprise, $comptesBanque available
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Nouveau rapprochement bancaire</h1>
        <p class="page-subtitle">Sélectionnez le compte et la période à rapprocher</p>
    </div>
    <a href="<?= APP_URL ?>/dossier/rapprochement?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Retour
    </a>
</div>

<div class="card" style="max-width:600px">
    <form method="post" action="<?= APP_URL ?>/dossier/rapprochement/store">
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

        <div style="display:grid;gap:20px">
            <div class="form-field">
                <label>Compte bancaire *</label>
                <select name="compte_banque" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($comptesBanque as $c): ?>
                    <option value="<?= e($c['numero']) ?>"><?= e($c['numero']) ?> — <?= e($c['intitule']) ?></option>
                    <?php endforeach; ?>
                    <?php if (empty($comptesBanque)): ?>
                    <option value="511" selected>511 — Banque principale</option>
                    <?php endif; ?>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-field">
                    <label>Mois *</label>
                    <select name="periode_mois" required>
                        <?php $moisNoms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; ?>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= $moisNoms[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Année *</label>
                    <select name="periode_annee" required>
                        <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-field">
                <label>Solde du relevé bancaire (FCFA) *</label>
                <input type="number" name="solde_releve" required step="1" placeholder="0">
                <span style="font-size:15px;color:var(--text-muted)">Solde final figurant sur votre relevé de compte.</span>
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border);padding-top:20px;margin-top:20px">
            <a href="<?= APP_URL ?>/dossier/rapprochement?id=<?= $entreprise['id'] ?>" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer et ouvrir</button>
        </div>
    </form>
</div>
