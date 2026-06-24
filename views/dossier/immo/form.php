<?php
// $entreprise, $comptes_immo available
$comptes2xx = $comptes_immo ?? [];
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Nouvelle immobilisation</h1>
        <p class="page-subtitle">Enregistrer un actif immobilisé</p>
    </div>
    <a href="<?= APP_URL ?>/dossier/immo?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Retour
    </a>
</div>

<div class="card" style="max-width:780px">
    <form method="post" action="<?= APP_URL ?>/dossier/immo/store">
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
            <div style="grid-column:1/-1">
                <label class="form-label">Désignation *</label>
                <input type="text" name="designation" class="form-control" required placeholder="Ex: Matériel informatique, Véhicule...">
            </div>

            <div>
                <label class="form-label">Type *</label>
                <select name="type" class="form-control" required>
                    <option value="corporelle">Corporelle</option>
                    <option value="incorporelle">Incorporelle</option>
                    <option value="financiere">Financière</option>
                </select>
            </div>

            <div>
                <label class="form-label">Catégorie</label>
                <input type="text" name="categorie" class="form-control" placeholder="Ex: Matériel de transport, Logiciel...">
            </div>

            <div>
                <label class="form-label">Compte (2xx) *</label>
                <select name="compte_numero" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($comptes2xx as $c): ?>
                    <option value="<?= e($c['numero']) ?>"><?= e($c['numero']) ?> — <?= e($c['intitule']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Date d'acquisition *</label>
                <input type="date" name="date_acquisition" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>

            <div>
                <label class="form-label">Date de mise en service</label>
                <input type="date" name="date_mise_service" class="form-control">
            </div>

            <div>
                <label class="form-label">Valeur brute (FCFA) *</label>
                <input type="number" name="valeur_brute" class="form-control" required min="0" step="1" placeholder="0">
            </div>

            <div>
                <label class="form-label">Valeur résiduelle (FCFA)</label>
                <input type="number" name="valeur_residuelle" class="form-control" value="0" min="0" step="1">
            </div>

            <div>
                <label class="form-label">Méthode d'amortissement *</label>
                <select name="methode_amort" id="methode_amort" class="form-control" required onchange="updateTaux()">
                    <option value="lineaire">Linéaire</option>
                    <option value="degressif">Dégressif</option>
                </select>
            </div>

            <div>
                <label class="form-label">Durée (années) *</label>
                <select name="duree_amort" id="duree_amort" class="form-control" required onchange="updateTaux()">
                    <option value="3">3 ans</option>
                    <option value="5" selected>5 ans</option>
                    <option value="8">8 ans</option>
                    <option value="10">10 ans</option>
                    <option value="20">20 ans</option>
                    <option value="33">33 ans</option>
                </select>
            </div>

            <div>
                <label class="form-label">Taux calculé</label>
                <input type="text" id="taux_display" class="form-control" readonly value="20,00 %" style="background:var(--bg);color:var(--text-muted)">
            </div>

            <div>
                <label class="form-label">Fournisseur</label>
                <input type="text" name="fournisseur" class="form-control" placeholder="Nom du fournisseur">
            </div>

            <div>
                <label class="form-label">Référence / N° de facture</label>
                <input type="text" name="reference" class="form-control" placeholder="Référence">
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border);padding-top:18px">
            <a href="<?= APP_URL ?>/dossier/immo?id=<?= $entreprise['id'] ?>" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-ent">Enregistrer</button>
        </div>
    </form>
</div>

<script>
function updateTaux() {
    const methode = document.getElementById('methode_amort').value;
    const duree   = parseInt(document.getElementById('duree_amort').value);
    let taux;
    if (methode === 'degressif') {
        taux = (200 / duree).toFixed(2);
    } else {
        taux = (100 / duree).toFixed(2);
    }
    document.getElementById('taux_display').value = taux.replace('.', ',') + ' %';
}
updateTaux();
</script>
