<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <div>
        <div class="page-title" style="font-family:'Cormorant Garamond',serif;font-size:28px">Facture <?= e($honoraire['numero_facture']) ?></div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:3px"><?= e($honoraire['raison_sociale']) ?></div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/honoraires/pdf?id=<?= $honoraire['id'] ?>" class="btn btn-primary" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimer / PDF
        </a>
        <a href="<?= APP_URL ?>/honoraires" class="btn btn-outline">Retour</a>
    </div>
</div>

<!-- Carte facture -->
<div style="max-width:900px;background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:28px 32px;background:var(--navy-dark);color:white">
        <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;letter-spacing:1px">SenCompta</div>
            <div style="font-size:12px;opacity:0.5;margin-top:4px">Expert-Comptable · OHADA SYSCOHADA</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:2px;opacity:0.5;margin-bottom:4px">Facture</div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:24px"><?= e($honoraire['numero_facture']) ?></div>
            <div style="font-size:12px;opacity:0.6;margin-top:4px">
                Date : <?= date('d/m/Y', strtotime($honoraire['date_facture'])) ?>
            </div>
            <?php if($honoraire['date_echeance']): ?>
            <div style="font-size:12px;opacity:0.6">Échéance : <?= date('d/m/Y', strtotime($honoraire['date_echeance'])) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Adresses -->
    <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)">
        <div style="padding:20px 32px;border-right:1px solid var(--border)">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:8px;font-weight:600">De</div>
            <div style="font-weight:700;font-size:13px;margin-bottom:4px">SenCompta</div>
            <div style="font-size:13px;color:var(--text-muted)">Expert-Comptable agréé</div>
        </div>
        <div style="padding:20px 32px">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:8px;font-weight:600">Facturé à</div>
            <div style="font-weight:700;font-size:13px;margin-bottom:4px"><?= e($honoraire['raison_sociale']) ?></div>
            <?php if($honoraire['ninea']): ?>
            <div style="font-size:12px;color:var(--text-muted)">NINEA : <?= e($honoraire['ninea']) ?></div>
            <?php endif; ?>
            <?php if($honoraire['adresse']): ?>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= e($honoraire['adresse']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Description -->
    <?php if($honoraire['description']): ?>
    <div style="padding:14px 32px;background:var(--bg);border-bottom:1px solid var(--border);font-size:13px;color:var(--text-muted)">
        <strong style="color:var(--text)">Objet :</strong> <?= e($honoraire['description']) ?>
    </div>
    <?php endif; ?>

    <!-- Lignes -->
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);background:var(--bg);border-bottom:1px solid var(--border)">
                <th style="padding:12px 32px;text-align:left;font-weight:600">Prestation</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;width:80px">Qté</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;width:160px">Prix unitaire</th>
                <th style="padding:12px 32px;text-align:right;font-weight:600;width:160px">Montant HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($lignes as $l): ?>
            <tr style="border-bottom:1px solid rgba(228,233,240,0.4)">
                <td style="padding:12px 32px;font-size:13.5px"><?= e($l['designation']) ?></td>
                <td style="padding:12px 16px;text-align:right;font-size:13px;color:var(--text-muted)"><?= number_format($l['quantite'],2,',',' ') ?></td>
                <td style="padding:12px 16px;text-align:right;font-family:monospace;font-size:13px"><?= number_format($l['prix_unitaire'],0,',',' ') ?></td>
                <td style="padding:12px 32px;text-align:right;font-family:monospace;font-weight:600"><?= number_format($l['montant'],0,',',' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totaux -->
    <div style="display:flex;justify-content:flex-end;padding:20px 32px;border-top:1px solid var(--border)">
        <div style="min-width:300px">
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13.5px">
                <span>Montant HT</span>
                <span style="font-family:monospace"><?= number_format($honoraire['montant_ht'],0,',',' ') ?> FCFA</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13.5px;color:var(--text-muted)">
                <span>TVA (<?= number_format($honoraire['taux_tva'],0) ?> %)</span>
                <span style="font-family:monospace"><?= number_format($honoraire['montant_tva'],0,',',' ') ?> FCFA</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 0;font-size:13px;font-weight:700">
                <span>Total TTC</span>
                <span style="font-family:monospace;color:var(--navy-dark)"><?= number_format($honoraire['montant_ttc'],0,',',' ') ?> FCFA</span>
            </div>
        </div>
    </div>

    <!-- Footer statut -->
    <div style="padding:14px 32px;background:var(--bg);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <?php
        $sb = ['en_attente'=>'badge-warning','paye'=>'badge-success','impaye'=>'badge-danger'];
        $sl = ['en_attente'=>'En attente de règlement','paye'=>'Payé','impaye'=>'Impayé'];
        ?>
        <span class="badge <?= $sb[$honoraire['statut']] ?? 'badge-navy' ?>" style="font-size:12px">
            <?= $sl[$honoraire['statut']] ?? $honoraire['statut'] ?>
        </span>
        <span style="font-size:12px;color:var(--text-muted)">Facture émise le <?= date('d/m/Y', strtotime($honoraire['date_facture'])) ?></span>
    </div>
</div>
