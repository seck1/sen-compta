<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture <?= e($facture['numero']) ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@400;500;600&display=swap');
* { margin:0;padding:0;box-sizing:border-box; }
body { font-family:'DM Sans',sans-serif;color:#1e293b;background:#fff;font-size:13px; }
.page { max-width:800px;margin:0 auto;padding:48px; }
.header { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:40px; }
.logo-block h1 { font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#1e3a5f; }
.logo-block .tagline { font-size:11px;color:#64748b;margin-top:2px; }
.doc-type { text-align:right; }
.doc-type .label { font-size:11px;text-transform:uppercase;letter-spacing:2px;color:#64748b;margin-bottom:4px; }
.doc-type .ref { font-size:22px;font-weight:700;color:#1e3a5f;font-family:'Playfair Display',serif; }
.doc-type .date { font-size:12px;color:#64748b;margin-top:4px; }
.gold-bar { height:3px;background:linear-gradient(90deg,#c9a96e,#e8c896);border-radius:2px;margin-bottom:32px; }
.parties { display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:32px; }
.party-label { font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:#c9a96e;font-weight:700;margin-bottom:8px; }
.party-name { font-size:16px;font-weight:700;color:#1e3a5f;margin-bottom:4px; }
.party-info { font-size:12px;color:#475569;line-height:1.7; }
.fac-meta { background:#f8fafc;border-radius:10px;padding:16px 20px;margin-bottom:28px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px; }
.meta-label { font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin-bottom:3px; }
.meta-value { font-size:13px;font-weight:600;color:#1e3a5f; }
.section-title { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#1e3a5f;margin-bottom:12px; }
table.lines { width:100%;border-collapse:collapse;margin-bottom:24px; }
table.lines thead th { background:#1e3a5f;color:#fff;padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px; }
table.lines thead th:not(:first-child) { text-align:right; }
table.lines tbody td { padding:11px 12px;border-bottom:1px solid #f1f5f9;font-size:13px; }
table.lines tbody td:not(:first-child) { text-align:right; }
table.lines tbody tr:nth-child(even) td { background:#fafbfc; }
.totaux { display:flex;justify-content:flex-end;margin-bottom:24px; }
.totaux-box { min-width:280px; }
.totaux-row { display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid #f1f5f9; }
.totaux-row .lbl { color:#64748b; }
.totaux-row.total { padding-top:12px;border-top:2px solid #1e3a5f;border-bottom:none;margin-top:4px; }
.totaux-row.total .lbl { font-size:15px;font-weight:700;color:#1e3a5f; }
.totaux-row.total .val { font-size:18px;font-weight:800;color:#c9a96e; }
.paiements-block { background:#f0fdf4;border-left:3px solid #22c55e;padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:20px; }
.paiements-block strong { display:block;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#22c55e;margin-bottom:6px; }
.paiement-line { display:flex;justify-content:space-between;font-size:12px;color:#166534;padding:3px 0;border-bottom:1px solid #bbf7d0; }
.paiement-line:last-child { border-bottom:none; }
.solde-block { background:#fff7ed;border-left:3px solid #f59e0b;padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:24px; }
.solde-block .reste { font-size:18px;font-weight:800;color:#92400e; }
.conditions { background:#fffbeb;border-left:3px solid #c9a96e;padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:24px;font-size:12px;color:#78350f; }
.conditions strong { display:block;margin-bottom:4px;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#c9a96e; }
.footer { margin-top:48px;padding-top:16px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#94a3b8;line-height:1.8; }
.status-stamp {
    position:fixed;top:60px;right:60px;
    border:3px solid #22c55e;border-radius:8px;
    padding:6px 18px;font-size:20px;font-weight:900;
    color:#22c55e;text-transform:uppercase;letter-spacing:3px;
    transform:rotate(-15deg);opacity:0.5;
}
@media print {
    body { -webkit-print-color-adjust:exact;print-color-adjust:exact; }
    .no-print { display:none; }
    .page { padding:30px; }
}
</style>
</head>
<body>

<?php if ($facture['statut'] === 'payee'): ?>
<div class="status-stamp">Payée</div>
<?php elseif ($facture['statut'] === 'annulee'): ?>
<div class="status-stamp" style="color:#ef4444;border-color:#ef4444">Annulée</div>
<?php endif; ?>

<div class="no-print" style="position:fixed;top:20px;right:20px;display:flex;gap:8px;z-index:100">
    <button onclick="window.print()" style="background:#1e3a5f;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
        🖨️ Imprimer
    </button>
    <a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= $facture['id'] ?>" style="background:#fff;color:#1e3a5f;border:1.5px solid #e2e8f0;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none">
        ← Retour
    </a>
</div>

<div class="page">
    <div class="header">
        <div class="logo-block">
            <img src="<?= APP_URL ?>/logo/logo.png" alt="SenCompta" style="height:52px;object-fit:contain;margin-bottom:4px"><br><h1 style="display:none">SenCompta</h1>
            <div class="tagline">Expertise Comptable · Conseil · Gestion</div>
        </div>
        <div class="doc-type">
            <div class="label">Facture</div>
            <div class="ref"><?= e($facture['numero']) ?></div>
            <div class="date">Dakar, le <?= date('d/m/Y', strtotime($facture['date_facture'])) ?></div>
        </div>
    </div>

    <div class="gold-bar"></div>

    <div class="parties">
        <div>
            <div class="party-label">Émetteur</div>
            <div class="party-name">SenCompta</div>
            <div class="party-info">Expert-Comptable inscrit à l'ONECCA<br>Dakar, Sénégal<br>Tel: +221 XX XXX XX XX</div>
        </div>
        <div>
            <div class="party-label">Facturé à</div>
            <div class="party-name"><?= e($facture['prospect_nom'] ?? '') ?></div>
            <div class="party-info">
                <?php if ($facture['prospect_forme']): ?><?= e($facture['prospect_forme']) ?><br><?php endif; ?>
                <?php if ($facture['prospect_ville']): ?><?= e($facture['prospect_ville']) ?><br><?php endif; ?>
                <?php if ($facture['prospect_ninea']): ?>NINEA: <?= e($facture['prospect_ninea']) ?><br><?php endif; ?>
                <?php if ($facture['contact_nom']): ?>À l'att. de: <?= e(($facture['contact_prenom'] ?? '') . ' ' . $facture['contact_nom']) ?><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="fac-meta">
        <div><div class="meta-label">Référence</div><div class="meta-value"><?= e($facture['numero']) ?></div></div>
        <div><div class="meta-label">Date émission</div><div class="meta-value"><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></div></div>
        <div><div class="meta-label">Échéance</div><div class="meta-value"><?= $facture['date_echeance'] ? date('d/m/Y', strtotime($facture['date_echeance'])) : '—' ?></div></div>
        <div><div class="meta-label">Statut</div><div class="meta-value"><?= ['brouillon'=>'Brouillon','envoyee'=>'Envoyée','payee'=>'Payée','partiel'=>'Partiel','retard'=>'En retard','annulee'=>'Annulée'][$facture['statut']] ?? $facture['statut'] ?></div></div>
    </div>

    <?php if ($facture['objet']): ?>
    <div style="margin-bottom:20px">
        <div class="section-title">Objet</div>
        <div style="font-size:14px;font-weight:600;color:#1e3a5f"><?= e($facture['objet']) ?></div>
    </div>
    <?php endif; ?>

    <div class="section-title">Détail des prestations</div>
    <table class="lines">
        <thead>
            <tr>
                <th style="width:45%">Description</th>
                <th style="width:10%">Qté</th>
                <th style="width:18%">P.U. HT</th>
                <th style="width:10%">Remise</th>
                <th style="width:17%">Montant HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= e($l['designation']) ?></div>
                    <?php if ($l['description']): ?><div style="font-size:11px;color:#64748b;margin-top:2px"><?= e($l['description']) ?></div><?php endif; ?>
                </td>
                <td><?= number_format($l['quantite'], 2, ',', ' ') ?></td>
                <td><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> F</td>
                <td><?= $l['remise'] > 0 ? $l['remise'].'%' : '—' ?></td>
                <td style="font-weight:600"><?= number_format($l['montant_ht'], 0, ',', ' ') ?> F</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totaux">
        <div class="totaux-box">
            <div class="totaux-row"><span class="lbl">Sous-total HT</span><span><?= number_format($facture['montant_ht'], 0, ',', ' ') ?> F</span></div>
            <?php if ($facture['remise_globale'] > 0): ?>
            <div class="totaux-row"><span class="lbl">Remise (<?= $facture['remise_globale'] ?>%)</span><span>−<?= number_format($facture['montant_ht'] * $facture['remise_globale'] / 100, 0, ',', ' ') ?> F</span></div>
            <?php endif; ?>
            <div class="totaux-row"><span class="lbl">TVA (<?= $facture['taux_tva'] ?>%)</span><span><?= number_format($facture['montant_tva'], 0, ',', ' ') ?> F</span></div>
            <div class="totaux-row total"><span class="lbl">Total TTC</span><span class="val"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> F</span></div>
        </div>
    </div>

    <?php if (!empty($paiements)): ?>
    <div class="paiements-block">
        <strong>Paiements reçus</strong>
        <?php foreach ($paiements as $p): ?>
        <div class="paiement-line">
            <span><?= date('d/m/Y', strtotime($p['date_paiement'])) ?> · <?= e(ucfirst($p['mode_paiement'] ?? 'Virement')) ?><?= $p['reference_paiement'] ? ' · '.e($p['reference_paiement']) : '' ?></span>
            <span style="font-weight:700"><?= number_format($p['montant'], 0, ',', ' ') ?> F</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php $reste = $facture['montant_ttc'] - $facture['montant_paye']; ?>
    <?php if ($reste > 0.01): ?>
    <div class="solde-block">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#92400e;margin-bottom:4px">Solde restant dû</div>
        <div class="reste"><?= number_format($reste, 0, ',', ' ') ?> F</div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($facture['conditions_paiement']): ?>
    <div class="conditions">
        <strong>Conditions de paiement</strong>
        <?= e($facture['conditions_paiement']) ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        SenCompta · Expert-Comptable · ONECCA Sénégal<br>
        Facture émise électroniquement — valable sans signature selon la loi sénégalaise sur la facturation.<br>
        Tout retard de paiement entraîne des pénalités conformément aux conditions générales.
    </div>
</div>
</body>
</html>
