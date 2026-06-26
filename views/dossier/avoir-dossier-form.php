<?php $fmt = fn($v)=>number_format((float)$v,0,',',' ');
$totalDebit = array_sum(array_map(fn($l)=>(float)$l['debit'], $lignes));
$montantClient = 0;
foreach ($lignes as $l) { if (strpos((string)$l['numero'],'411')===0) $montantClient += (float)$l['debit']; }
?>
<style>
.adf-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px;}
.adf-card h3{font-size:15px;font-weight:700;color:var(--navy-dark);margin-bottom:14px;}
.adf-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;}
.adf-field label{display:block;font-size:12.5px;font-weight:600;color:var(--text-muted);margin-bottom:5px;}
.adf-field input,.adf-field select,.adf-field textarea{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;}
.adf-table{width:100%;border-collapse:collapse;font-size:13px;}
.adf-table th{text-align:left;padding:8px 10px;font-size:8pt;text-transform:uppercase;color:#fff;background:var(--green);}
.adf-table th.r,.adf-table td.r{text-align:right;}
.adf-table td{padding:8px 10px;border-bottom:1px solid var(--border);font-family:monospace;}
.adf-banner{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:12px 16px;font-size:13.5px;color:#92400e;margin-bottom:18px;}
.adf-btn{display:inline-flex;align-items:center;gap:8px;border:none;cursor:pointer;background:linear-gradient(135deg,#a8443f,#7c2d2d);color:#fff;padding:12px 24px;border-radius:10px;font-size:15px;font-weight:700;font-family:inherit;}
.adf-flash{padding:13px 18px;border-radius:11px;margin-bottom:18px;font-size:14px;background:rgba(192,57,43,0.08);color:#c0392b;border:1px solid rgba(192,57,43,0.25);}
.adf-mode{display:flex;gap:10px;margin-bottom:12px;}
.adf-mode label{flex:1;text-align:center;cursor:pointer;font-size:13.5px;font-weight:600;padding:10px;border:1.5px solid var(--border);border-radius:9px;color:var(--text-muted);}
.adf-mode input{display:none;}
.adf-mode input:checked+label{border-color:#a8443f;background:rgba(168,68,63,.07);color:#a8443f;}
</style>

<div class="page-header" style="margin-bottom:16px">
    <div>
        <h1 class="page-title">Nouvel avoir de vente</h1>
        <p class="page-subtitle">Extourne de la facture <strong><?= e($facture['numero_facture'] ?: $facture['numero_piece']) ?></strong> · <?= e($facture['libelle']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/dossier/avoirs?id=<?= $entreprise['id'] ?>" style="font-size:14px;color:var(--text-muted);text-decoration:none">← Retour</a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="adf-flash"><?= e($_SESSION['flash_error']) ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="adf-banner">
    ℹ️ L'avoir crée une <strong>écriture inverse</strong> (extourne) dans le journal VTE : le client sera crédité, les produits et la TVA débités. Montant client de la facture : <strong><?= $fmt($montantClient) ?> FCFA</strong>.
</div>

<!-- Aperçu de la facture d'origine -->
<div class="adf-card">
    <h3>Écriture d'origine</h3>
    <table class="adf-table">
        <thead><tr><th>Compte</th><th style="font-family:inherit">Intitulé</th><th class="r">Débit</th><th class="r">Crédit</th></tr></thead>
        <tbody>
        <?php foreach ($lignes as $l): ?>
            <tr>
                <td><?= e($l['numero']) ?></td>
                <td style="font-family:inherit"><?= e($l['intitule']) ?></td>
                <td class="r"><?= (float)$l['debit'] ? $fmt($l['debit']) : '' ?></td>
                <td class="r"><?= (float)$l['credit'] ? $fmt($l['credit']) : '' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<form method="POST" action="<?= APP_URL ?>/dossier/avoirs/store" id="adf-form">
    <?= csrfField() ?>
    <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
    <input type="hidden" name="ecriture_id" value="<?= (int)$facture['id'] ?>">

    <div class="adf-card">
        <h3>Paramètres de l'avoir</h3>

        <div class="adf-mode">
            <input type="radio" id="m-total" name="mode" value="total" checked onclick="document.getElementById('taux').value=100;document.getElementById('taux').disabled=true;majMontant()">
            <label for="m-total">Avoir total (100 %)</label>
            <input type="radio" id="m-partiel" name="mode" value="partiel" onclick="document.getElementById('taux').disabled=false;majMontant()">
            <label for="m-partiel">Avoir partiel</label>
        </div>

        <div class="adf-row">
            <div class="adf-field">
                <label>Pourcentage à extourner</label>
                <input type="number" id="taux" name="taux" value="100" min="0.01" max="100" step="0.01" disabled oninput="majMontant()">
            </div>
            <div class="adf-field">
                <label>Date de l'avoir</label>
                <input type="date" name="date_avoir" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>
        <div class="adf-row">
            <div class="adf-field">
                <label>Motif</label>
                <select name="motif">
                    <option value="retour">Retour de marchandise/prestation</option>
                    <option value="remboursement">Remboursement</option>
                    <option value="erreur">Erreur de facturation</option>
                    <option value="geste_commercial">Geste commercial</option>
                    <option value="autre" selected>Autre</option>
                </select>
            </div>
            <div class="adf-field">
                <label>Montant TTC extourné</label>
                <input type="text" id="montant-aff" value="<?= $fmt($montantClient) ?> FCFA" readonly style="background:#f6f8f7;font-weight:700">
            </div>
        </div>
        <div class="adf-field">
            <label>Raison / commentaire</label>
            <textarea name="raison" rows="2" placeholder="Optionnel"></textarea>
        </div>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
        <button type="submit" class="adf-btn">✓ Créer l'avoir (extourne)</button>
        <a href="<?= APP_URL ?>/dossier/avoirs?id=<?= $entreprise['id'] ?>" style="font-size:14px;color:var(--text-muted)">Annuler</a>
    </div>
</form>

<script>
const MONTANT_CLIENT = <?= (float)$montantClient ?>;
function majMontant(){
    const t = parseFloat(document.getElementById('taux').value)||0;
    const m = Math.round(MONTANT_CLIENT * t/100);
    document.getElementById('montant-aff').value = m.toLocaleString('fr-FR') + ' FCFA';
}
</script>
