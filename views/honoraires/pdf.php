<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture <?= htmlspecialchars($honoraire['numero_facture']) ?> — SenCompta</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --navy: #1e3a5f;
    --gold: #c9a96e;
}
body { font-family: 'DM Sans', sans-serif; font-size: 13px; color: #1a2535; background: white; }

.invoice { max-width: 800px; margin: 0 auto; padding: 0; }

.inv-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 32px 40px; background: #1e3a5f; color: white; }
.inv-company-name { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 300; letter-spacing: 1px; }
.inv-company-sub { font-size: 11px; opacity: 0.5; margin-top: 4px; }
.inv-number-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.5; text-align: right; margin-bottom: 4px; }
.inv-number { font-family: 'Cormorant Garamond', serif; font-size: 26px; text-align: right; }
.inv-date { font-size: 11px; opacity: 0.6; text-align: right; margin-top: 4px; }

.inv-parties { display: flex; border-bottom: 2px solid #e4e9f0; }
.inv-party { flex: 1; padding: 20px 40px; }
.inv-party + .inv-party { border-left: 1px solid #e4e9f0; }
.inv-party-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7a94; margin-bottom: 8px; font-weight: 600; }
.inv-party-name { font-weight: 700; font-size: 15px; margin-bottom: 4px; }
.inv-party-meta { font-size: 12px; color: #6b7a94; line-height: 1.6; }

.inv-description { padding: 12px 40px; background: #f0f3f8; font-size: 12.5px; color: #6b7a94; border-bottom: 1px solid #e4e9f0; }

table { width: 100%; border-collapse: collapse; }
.inv-table thead th { padding: 10px 40px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6b7a94; background: #f8f9fc; border-bottom: 1px solid #e4e9f0; font-weight: 600; text-align: left; }
.inv-table thead th:last-child, .inv-table tbody td:last-child { text-align: right; }
.inv-table thead th:nth-child(2), .inv-table tbody td:nth-child(2),
.inv-table thead th:nth-child(3), .inv-table tbody td:nth-child(3) { text-align: right; }
.inv-table tbody td { padding: 11px 40px; border-bottom: 1px solid rgba(228,233,240,0.5); font-size: 13px; }

.inv-totals { display: flex; justify-content: flex-end; padding: 16px 40px; border-top: 1px solid #e4e9f0; }
.inv-totals-inner { min-width: 280px; }
.inv-total-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 13px; }
.inv-total-row.grand { font-size: 16px; font-weight: 700; border-top: 2px solid #1e3a5f; margin-top: 6px; padding-top: 10px; }
.inv-total-num { font-family: monospace; }

.inv-footer { padding: 16px 40px; background: #f0f3f8; border-top: 1px solid #e4e9f0; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #6b7a94; }
.inv-status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-en_attente { background: rgba(245,158,11,0.15); color: #d97706; }
.status-paye { background: rgba(34,197,94,0.15); color: #16a34a; }
.status-impaye { background: rgba(239,68,68,0.15); color: #dc2626; }

.no-print { display: flex; justify-content: center; padding: 16px; gap: 10px; }
.btn-print { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-family: 'DM Sans', sans-serif; }

@media print {
    .no-print { display: none !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .inv-header { background: #1e3a5f !important; }
    @page { margin: 10mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn-print">Imprimer / Enregistrer en PDF</button>
    <a href="<?= APP_URL ?>/honoraires/voir?id=<?= $honoraire['id'] ?>" style="display:inline-flex;align-items:center;padding:10px 20px;border:1px solid #e4e9f0;border-radius:8px;text-decoration:none;font-size:13px;color:#1a2535">Retour</a>
</div>

<div class="invoice">
    <!-- Header -->
    <div class="inv-header">
        <div>
            <div class="inv-company-name">SenCompta</div>
            <div class="inv-company-sub">Expert-Comptable · OHADA SYSCOHADA · Sénégal</div>
        </div>
        <div>
            <div class="inv-number-label">Facture</div>
            <div class="inv-number"><?= htmlspecialchars($honoraire['numero_facture']) ?></div>
            <div class="inv-date">
                Date : <?= date('d/m/Y', strtotime($honoraire['date_facture'])) ?>
                <?php if($honoraire['date_echeance']): ?>
                · Échéance : <?= date('d/m/Y', strtotime($honoraire['date_echeance'])) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="inv-parties">
        <div class="inv-party">
            <div class="inv-party-label">Émetteur</div>
            <div class="inv-party-name"><?= htmlspecialchars(CABINET_NOM) ?></div>
            <div class="inv-party-meta">
                <?= htmlspecialchars(CABINET_QUALITE) ?><br>
                <?php if (CABINET_NINEA): ?>NINEA : <?= htmlspecialchars(CABINET_NINEA) ?><br><?php endif; ?>
                <?php if (CABINET_RCCM): ?>RCCM : <?= htmlspecialchars(CABINET_RCCM) ?><br><?php endif; ?>
                <?php if (CABINET_ADRESSE): ?><?= htmlspecialchars(CABINET_ADRESSE) ?><br><?php endif; ?>
                <?php if (CABINET_TEL): ?>Tél : <?= htmlspecialchars(CABINET_TEL) ?><br><?php endif; ?>
                <?php if (CABINET_EMAIL): ?><?= htmlspecialchars(CABINET_EMAIL) ?><?php endif; ?>
            </div>
        </div>
        <div class="inv-party">
            <div class="inv-party-label">Client</div>
            <div class="inv-party-name"><?= htmlspecialchars($honoraire['raison_sociale']) ?></div>
            <div class="inv-party-meta">
                <?php if($honoraire['forme_juridique']): ?><?= htmlspecialchars($honoraire['forme_juridique']) ?><br><?php endif; ?>
                <?php if($honoraire['ninea']): ?>NINEA : <?= htmlspecialchars($honoraire['ninea']) ?><br><?php endif; ?>
                <?php if($honoraire['rccm']): ?>RCCM : <?= htmlspecialchars($honoraire['rccm']) ?><br><?php endif; ?>
                <?php if($honoraire['adresse']): ?><?= htmlspecialchars($honoraire['adresse']) ?><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($honoraire['description']): ?>
    <div class="inv-description">
        <strong>Objet :</strong> <?= htmlspecialchars($honoraire['description']) ?>
    </div>
    <?php endif; ?>

    <!-- Lignes -->
    <table class="inv-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Qté</th>
                <th>Prix unitaire HT</th>
                <th>Montant HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($lignes as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['designation'] ?? '') ?></td>
                <td><?= number_format($l['quantite'], 2, ',', ' ') ?></td>
                <td><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                <td><?= number_format($l['montant'], 0, ',', ' ') ?> FCFA</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="inv-totals">
        <div class="inv-totals-inner">
            <div class="inv-total-row">
                <span>Montant HT</span>
                <span class="inv-total-num"><?= number_format($honoraire['montant_ht'], 0, ',', ' ') ?> FCFA</span>
            </div>
            <div class="inv-total-row" style="color:#6b7a94">
                <span>TVA (<?= number_format($honoraire['taux_tva'], 0) ?> %)</span>
                <span class="inv-total-num"><?= number_format($honoraire['montant_tva'], 0, ',', ' ') ?> FCFA</span>
            </div>
            <div class="inv-total-row grand">
                <span>Total TTC</span>
                <span class="inv-total-num"><?= number_format($honoraire['montant_ttc'], 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>
    </div>

    <!-- Mention régime TVA — CGI Art. 358 bis -->
    <div style="font-size:10px;color:#6b7a94;padding:8px 20px;border-top:1px solid #e4e9f0;margin-top:8px">
        <?php if(($honoraire['taux_tva'] ?? 0) > 0): ?>
            TVA acquittée sur les débits — Assujetti régime réel — NINEA : <?= htmlspecialchars(CABINET_NINEA ?: 'À compléter') ?>
        <?php else: ?>
            TVA non applicable — Art. 395 CGI Sénégal
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="inv-footer">
        <span class="inv-status status-<?= htmlspecialchars($honoraire['statut']) ?>">
            <?php $sl = ['en_attente'=>'En attente','paye'=>'Payé','impaye'=>'Impayé']; ?>
            <?= $sl[$honoraire['statut']] ?? $honoraire['statut'] ?>
        </span>
        <span>Document généré le <?= date('d/m/Y') ?> · SenCompta · Expert-Comptable</span>
    </div>
</div>

</body>
</html>
