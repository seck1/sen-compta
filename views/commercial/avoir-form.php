<style>
.av-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px;}
.av-card h3{font-size:16px;font-weight:700;color:var(--navy-dark);margin-bottom:14px;}
.av-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;}
.av-field label{display:block;font-size:12.5px;font-weight:600;color:var(--text-muted);margin-bottom:5px;}
.av-field input,.av-field select,.av-field textarea{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;}
.av-table{width:100%;border-collapse:collapse;}
.av-table th{text-align:left;padding:9px 8px;font-size:8.5pt;text-transform:uppercase;letter-spacing:.4px;color:#fff;background:var(--green);}
.av-table td{padding:6px 8px;border-bottom:1px solid var(--border);}
.av-table input{width:100%;padding:7px 9px;border:1px solid var(--border);border-radius:7px;font-size:13px;}
.av-table input.r{text-align:right;font-family:monospace;}
.av-totaux{margin-top:16px;display:flex;justify-content:flex-end;}
.av-totaux table td{padding:5px 14px;font-size:14px;}
.av-totaux .ttc{font-weight:700;font-size:17px;color:var(--navy-dark);}
.av-btn{display:inline-flex;align-items:center;gap:8px;border:none;cursor:pointer;background:linear-gradient(135deg,#2a8a63,#1f6e4e);color:#fff;padding:12px 24px;border-radius:10px;font-size:15px;font-weight:700;font-family:inherit;}
.av-del{background:none;border:none;color:#c0392b;cursor:pointer;font-size:16px;}
.av-flash{padding:13px 18px;border-radius:11px;margin-bottom:18px;font-size:14px;font-weight:500;}
.av-flash.err{background:rgba(192,57,43,0.08);color:#c0392b;border:1px solid rgba(192,57,43,0.25);}
.av-banner{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:12px 16px;font-size:13.5px;color:#92400e;margin-bottom:18px;}
</style>

<div class="page-header" style="margin-bottom:18px">
    <div>
        <h1 class="page-title">Nouvel avoir</h1>
        <p class="page-subtitle">Note de crédit sur la facture <strong><?= e($facture['numero']) ?></strong> · <?= e($facture['prospect_nom']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= (int)$facture['id'] ?>" style="font-size:14px;color:var(--text-muted);text-decoration:none">← Retour à la facture</a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="av-flash err"><?= e($_SESSION['flash_error']) ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="av-banner">
    ℹ️ Cet avoir <strong>réduira le reste dû</strong> de la facture <?= e($facture['numero']) ?> (TTC actuel : <?= number_format($facture['montant_ttc'],0,',',' ') ?> FCFA).
    Les lignes ci-dessous sont reprises de la facture — ajustez les quantités/montants pour un avoir partiel, ou supprimez des lignes.
</div>

<form method="POST" action="<?= APP_URL ?>/commercial/avoirs/store" id="avoir-form">
    <?= csrfField() ?>
    <input type="hidden" name="facture_id" value="<?= (int)$facture['id'] ?>">

    <div class="av-card">
        <h3>Informations</h3>
        <div class="av-row">
            <div class="av-field">
                <label>Date de l'avoir</label>
                <input type="date" name="date_avoir" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="av-field">
                <label>Motif</label>
                <select name="motif">
                    <option value="retour">Retour de marchandise/prestation</option>
                    <option value="remboursement">Remboursement</option>
                    <option value="geste_commercial">Geste commercial</option>
                    <option value="erreur">Erreur de facturation</option>
                    <option value="autre" selected>Autre</option>
                </select>
            </div>
        </div>
        <div class="av-field">
            <label>Raison / commentaire</label>
            <textarea name="raison" rows="2" placeholder="Optionnel"></textarea>
        </div>
    </div>

    <div class="av-card">
        <h3>Lignes de l'avoir</h3>
        <table class="av-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th style="width:90px">Qté</th>
                    <th style="width:120px">P.U.</th>
                    <th style="width:80px">Remise %</th>
                    <th style="width:130px;text-align:right">Montant HT</th>
                    <th style="width:36px"></th>
                </tr>
            </thead>
            <tbody id="av-lignes">
                <?php foreach ($lignes as $i => $l): ?>
                <tr class="av-ligne">
                    <td><input type="text" name="lignes[<?= $i ?>][designation]" value="<?= e($l['designation']) ?>"></td>
                    <td><input type="number" step="0.001" class="r qte" name="lignes[<?= $i ?>][quantite]" value="<?= (float)$l['quantite'] ?>"></td>
                    <td><input type="number" step="0.01" class="r pu" name="lignes[<?= $i ?>][prix_unitaire]" value="<?= (float)$l['prix_unitaire'] ?>"></td>
                    <td><input type="number" step="0.01" class="r rem" name="lignes[<?= $i ?>][remise]" value="<?= (float)$l['remise'] ?>"></td>
                    <td style="text-align:right"><span class="montant-ht" style="font-family:monospace;font-size:13px">0</span>
                        <input type="hidden" name="lignes[<?= $i ?>][tva_taux]" value="<?= (float)($l['tva_taux'] ?? $facture['taux_tva'] ?? 18) ?>"></td>
                    <td><button type="button" class="av-del" onclick="this.closest('tr').remove();recalc()">✕</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="av-totaux">
            <table>
                <tr><td>Total HT</td><td style="text-align:right;font-family:monospace"><span id="t-ht">0</span> FCFA</td></tr>
                <tr><td>TVA (<?= (float)($facture['taux_tva'] ?? 18) ?>%)</td><td style="text-align:right;font-family:monospace"><span id="t-tva">0</span> FCFA</td></tr>
                <tr><td class="ttc">Total TTC avoir</td><td style="text-align:right" class="ttc"><span id="t-ttc">0</span> FCFA</td></tr>
            </table>
        </div>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
        <button type="submit" class="av-btn">✓ Créer l'avoir</button>
        <a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= (int)$facture['id'] ?>" style="font-size:14px;color:var(--text-muted)">Annuler</a>
    </div>
</form>

<script>
const TAUX_TVA = <?= (float)($facture['taux_tva'] ?? 18) ?>;
function recalc(){
    let ht = 0;
    document.querySelectorAll('.av-ligne').forEach(function(tr){
        const q = parseFloat(tr.querySelector('.qte').value)||0;
        const pu= parseFloat(tr.querySelector('.pu').value)||0;
        const r = parseFloat(tr.querySelector('.rem').value)||0;
        const m = q*pu*(1-r/100);
        tr.querySelector('.montant-ht').textContent = Math.round(m).toLocaleString('fr-FR');
        ht += m;
    });
    const tva = ht*TAUX_TVA/100;
    document.getElementById('t-ht').textContent  = Math.round(ht).toLocaleString('fr-FR');
    document.getElementById('t-tva').textContent = Math.round(tva).toLocaleString('fr-FR');
    document.getElementById('t-ttc').textContent = Math.round(ht+tva).toLocaleString('fr-FR');
}
document.getElementById('avoir-form').addEventListener('input', recalc);
recalc();
</script>
