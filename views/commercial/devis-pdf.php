<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Devis <?= e($devis['numero']) ?></title>
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
.party-box { }
.party-label { font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:#c9a96e;font-weight:700;margin-bottom:8px; }
.party-name { font-size:16px;font-weight:700;color:#1e3a5f;margin-bottom:4px; }
.party-info { font-size:12px;color:#475569;line-height:1.7; }

.devis-meta { background:#f8fafc;border-radius:10px;padding:16px 20px;margin-bottom:28px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px; }
.meta-item .meta-label { font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin-bottom:3px; }
.meta-item .meta-value { font-size:13px;font-weight:600;color:#1e3a5f; }

.section-title { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#1e3a5f;margin-bottom:12px; }

table.lines { width:100%;border-collapse:collapse;margin-bottom:24px; }
table.lines thead th { background:#1e3a5f;color:#fff;padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px; }
table.lines thead th:last-child, table.lines thead th:nth-child(2), table.lines thead th:nth-child(3), table.lines thead th:nth-child(4) { text-align:right; }
table.lines tbody td { padding:11px 12px;border-bottom:1px solid #f1f5f9;font-size:13px; }
table.lines tbody td:last-child, table.lines tbody td:nth-child(2), table.lines tbody td:nth-child(3), table.lines tbody td:nth-child(4) { text-align:right; }
table.lines tbody tr:nth-child(even) td { background:#fafbfc; }
.td-desc-main { font-weight:600;color:#1e3a5f; }
.td-desc-sub { font-size:11px;color:#64748b;margin-top:2px; }

.totaux { display:flex;justify-content:flex-end;margin-bottom:24px; }
.totaux-box { min-width:260px; }
.totaux-row { display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid #f1f5f9; }
.totaux-row .lbl { color:#64748b; }
.totaux-row.total { padding-top:12px;border-top:2px solid #1e3a5f;border-bottom:none;margin-top:4px; }
.totaux-row.total .lbl { font-size:15px;font-weight:700;color:#1e3a5f; }
.totaux-row.total .val { font-size:18px;font-weight:800;color:#c9a96e; }

.conditions { background:#fffbeb;border-left:3px solid #c9a96e;padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:24px;font-size:12px;color:#78350f; }
.conditions strong { display:block;margin-bottom:4px;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#c9a96e; }

.validity { background:#f0f9ff;border-left:3px solid #3b82f6;padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:24px;font-size:12px;color:#1e40af; }
.validity strong { font-weight:700; }

.notes-block { background:#f8fafc;border-radius:10px;padding:14px 16px;margin-bottom:24px;font-size:12px;color:#475569; }
.notes-block strong { display:block;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#94a3b8;margin-bottom:6px; }

.signature { display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-top:32px; }
.sig-box { border:1.5px dashed #e2e8f0;border-radius:10px;padding:20px;text-align:center; }
.sig-box .sig-label { font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin-bottom:4px; }
.sig-box .sig-name { font-size:12px;font-weight:600;color:#1e3a5f;margin-bottom:40px; }
.sig-line { border-top:1px solid #cbd5e1;font-size:10px;color:#94a3b8;padding-top:6px; }

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
<?php if ($devis['statut'] === 'accepte'): ?>
<div class="status-stamp">Accepté</div>
<?php elseif ($devis['statut'] === 'converti'): ?>
<div class="status-stamp" style="color:#8b5cf6;border-color:#8b5cf6">Facturé</div>
<?php endif; ?>

<!-- Print button -->
<div class="no-print" style="position:fixed;top:20px;right:20px;display:flex;gap:8px;z-index:100">
    <button onclick="window.print()" style="background:#1e3a5f;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
        🖨️ Imprimer
    </button>
    <a href="<?= APP_URL ?>/commercial/devis/voir?id=<?= $devis['id'] ?>" style="background:#fff;color:#1e3a5f;border:1.5px solid #e2e8f0;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none">
        ← Retour
    </a>
</div>

<div class="page">
    <!-- En-tête -->
    <div class="header">
        <div class="logo-block">
            <img src="<?= APP_URL ?>/logo/logo.png" alt="SenCompta" style="height:52px;object-fit:contain;margin-bottom:4px"><br><h1 style="display:none">SenCompta</h1>
            <div class="tagline">Expertise Comptable · Conseil · Gestion</div>
        </div>
        <div class="doc-type">
            <div class="label">Devis</div>
            <div class="ref"><?= e($devis['numero']) ?></div>
            <div class="date">Dakar, le <?= date('d/m/Y', strtotime($devis['date_devis'])) ?></div>
        </div>
    </div>

    <div class="gold-bar"></div>

    <!-- Parties -->
    <div class="parties">
        <div class="party-box">
            <div class="party-label">De</div>
            <div class="party-name">SenCompta</div>
            <div class="party-info">
                Expert-Comptable inscrit à l'ONECCA<br>
                Dakar, Sénégal<br>
                Tel: +221 XX XXX XX XX
            </div>
        </div>
        <div class="party-box">
            <div class="party-label">À l'attention de</div>
            <div class="party-name"><?= e($devis['prospect_nom'] ?? '') ?></div>
            <div class="party-info">
                <?php if ($devis['prospect_forme']): ?><?= e($devis['prospect_forme']) ?><br><?php endif; ?>
                <?php if ($devis['prospect_ville']): ?><?= e($devis['prospect_ville']) ?><br><?php endif; ?>
                <?php if ($devis['contact_nom']): ?>À l'att. de: <?= e($devis['contact_prenom'] ?? '') . ' ' . e($devis['contact_nom']) ?><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Méta devis -->
    <div class="devis-meta">
        <div class="meta-item">
            <div class="meta-label">Référence</div>
            <div class="meta-value"><?= e($devis['numero']) ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Date d'émission</div>
            <div class="meta-value"><?= date('d/m/Y', strtotime($devis['date_devis'])) ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Valable jusqu'au</div>
            <div class="meta-value"><?= $devis['date_validite'] ? date('d/m/Y', strtotime($devis['date_validite'])) : '—' ?></div>
        </div>
    </div>

    <?php if ($devis['objet']): ?>
    <div style="margin-bottom:20px">
        <div class="section-title">Objet</div>
        <div style="font-size:14px;font-weight:600;color:#1e3a5f"><?= e($devis['objet']) ?></div>
    </div>
    <?php endif; ?>

    <!-- Lignes -->
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
                    <div class="td-desc-main"><?= e($l['designation']) ?></div>
                    <?php if ($l['description']): ?><div class="td-desc-sub"><?= e($l['description']) ?></div><?php endif; ?>
                </td>
                <td><?= number_format($l['quantite'], 2, ',', ' ') ?></td>
                <td><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> F</td>
                <td><?= $l['remise'] > 0 ? $l['remise'].'%' : '—' ?></td>
                <td style="font-weight:600"><?= number_format($l['montant_ht'], 0, ',', ' ') ?> F</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="totaux">
        <div class="totaux-box">
            <div class="totaux-row"><span class="lbl">Sous-total HT</span><span><?= number_format($devis['montant_ht'], 0, ',', ' ') ?> F</span></div>
            <?php if ($devis['remise_globale'] > 0):
                $remiseMt = $devis['montant_ht'] * $devis['remise_globale'] / 100;
            ?>
            <div class="totaux-row"><span class="lbl">Remise (<?= $devis['remise_globale'] ?>%)</span><span>−<?= number_format($remiseMt, 0, ',', ' ') ?> F</span></div>
            <?php endif; ?>
            <div class="totaux-row"><span class="lbl">TVA (<?= $devis['taux_tva'] ?>%)</span><span><?= number_format($devis['montant_tva'], 0, ',', ' ') ?> F</span></div>
            <div class="totaux-row total"><span class="lbl">Total TTC</span><span class="val"><?= number_format($devis['montant_ttc'], 0, ',', ' ') ?> F</span></div>
        </div>
    </div>

    <?php if ($devis['conditions_paiement']): ?>
    <div class="conditions">
        <strong>Conditions de paiement</strong>
        <?= e($devis['conditions_paiement']) ?>
    </div>
    <?php endif; ?>

    <?php if ($devis['date_validite']): ?>
    <div class="validity">
        Ce devis est valable jusqu'au <strong><?= date('d/m/Y', strtotime($devis['date_validite'])) ?></strong>. Passé ce délai, les tarifs pourront être révisés.
    </div>
    <?php endif; ?>

    <?php if ($devis['notes_internes']): ?>
    <div class="notes-block">
        <strong>Notes et remarques</strong>
        <?= nl2br(e($devis['notes_internes'])) ?>
    </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="signature">
        <div class="sig-box">
            <div class="sig-label">Bon pour accord — Client</div>
            <div class="sig-name"><?= e($devis['prospect_nom'] ?? '') ?></div>
            <div class="sig-line">Date et signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Prestataire</div>
            <div class="sig-name">SenCompta</div>
            <div class="sig-line">Cachet et signature</div>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        SenCompta · Expert-Comptable · ONECCA Sénégal<br>
        Ce document a été généré électroniquement et constitue un engagement commercial.
    </div>
</div>
</body>
</html>
