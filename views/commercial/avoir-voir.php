<?php $fmt = fn($v)=>number_format((float)$v,0,',',' ');
$motifs=['retour'=>'Retour','remboursement'=>'Remboursement','geste_commercial'=>'Geste commercial','erreur'=>'Erreur de facturation','autre'=>'Autre']; ?>
<style>
.avv-hero{background:linear-gradient(135deg,#7c2d2d,#a8443f);border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:22px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;}
.avv-hero .num{font-size:24px;font-weight:800;}
.avv-hero .sub{opacity:.85;font-size:14px;margin-top:4px;}
.avv-ttc{font-size:28px;font-weight:800;font-family:'Cormorant Garamond',serif;}
.avv-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:18px;}
.avv-card h3{font-size:15px;font-weight:700;color:var(--navy-dark);margin-bottom:14px;}
.avv-table{width:100%;border-collapse:collapse;}
.avv-table th{text-align:left;padding:9px 10px;font-size:8.5pt;text-transform:uppercase;color:#fff;background:var(--green);}
.avv-table td{padding:10px;border-bottom:1px solid var(--border);font-size:14px;}
.avv-flash{padding:13px 18px;border-radius:11px;margin-bottom:18px;font-size:14px;font-weight:500;background:rgba(31,110,78,0.10);color:#1f6e4e;border:1px solid rgba(31,110,78,0.25);}
.avv-info td{padding:7px 0;font-size:14px;border-bottom:1px solid var(--border);}
.avv-info td:first-child{color:var(--text-muted);}
.avv-info td:last-child{text-align:right;font-weight:600;}
</style>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="avv-flash"><?= e($_SESSION['flash_success']) ?></div>
<?php unset($_SESSION['flash_success']); endif; ?>

<div class="avv-hero">
    <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;opacity:.8">Note de crédit</div>
        <div class="num"><?= e($avoir['numero']) ?></div>
        <div class="sub"><?= e($avoir['client']) ?> · sur facture <?= e($avoir['facture_numero']) ?> · <?= date('d/m/Y', strtotime($avoir['date_avoir'])) ?></div>
    </div>
    <div style="text-align:right">
        <div style="font-size:11px;opacity:.8;text-transform:uppercase">Montant TTC</div>
        <div class="avv-ttc"><?= $fmt($avoir['montant_ttc']) ?> <span style="font-size:15px">FCFA</span></div>
    </div>
</div>

<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= (int)$avoir['facture_id'] ?>" style="padding:9px 18px;background:#fff;border:1px solid var(--border);border-radius:9px;text-decoration:none;color:var(--text);font-size:14px;font-weight:600">📄 Voir la facture d'origine</a>
    <a href="<?= APP_URL ?>/commercial/avoirs" style="padding:9px 18px;background:#fff;border:1px solid var(--border);border-radius:9px;text-decoration:none;color:var(--text);font-size:14px;font-weight:600">← Tous les avoirs</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">
    <div class="avv-card">
        <h3>Détail de l'avoir</h3>
        <table class="avv-table">
            <thead><tr><th>Désignation</th><th style="text-align:right">Qté</th><th style="text-align:right">P.U.</th><th style="text-align:right">Remise</th><th style="text-align:right">Montant HT</th></tr></thead>
            <tbody>
            <?php foreach ($lignes as $l): ?>
                <tr>
                    <td><?= e($l['designation']) ?><?php if(!empty($l['description'])): ?><div style="font-size:12px;color:var(--text-muted)"><?= e($l['description']) ?></div><?php endif; ?></td>
                    <td style="text-align:right;font-family:monospace"><?= rtrim(rtrim(number_format((float)$l['quantite'],3,',',' '),'0'),',') ?></td>
                    <td style="text-align:right;font-family:monospace"><?= $fmt($l['prix_unitaire']) ?></td>
                    <td style="text-align:right;font-family:monospace"><?= (float)$l['remise'] ?>%</td>
                    <td style="text-align:right;font-family:monospace"><?= $fmt($l['montant_ht']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:14px;display:flex;justify-content:flex-end">
            <table style="font-size:14px">
                <tr><td style="padding:4px 16px">Total HT</td><td style="text-align:right;font-family:monospace"><?= $fmt($avoir['montant_ht']) ?></td></tr>
                <tr><td style="padding:4px 16px">TVA (<?= (float)$avoir['taux_tva'] ?>%)</td><td style="text-align:right;font-family:monospace"><?= $fmt($avoir['montant_tva']) ?></td></tr>
                <tr><td style="padding:4px 16px;font-weight:700;font-size:16px">Total TTC</td><td style="text-align:right;font-weight:700;font-size:16px;font-family:monospace"><?= $fmt($avoir['montant_ttc']) ?></td></tr>
            </table>
        </div>
    </div>

    <div class="avv-card">
        <h3>Informations</h3>
        <table style="width:100%" class="avv-info">
            <tr><td>Client</td><td><?= e($avoir['client']) ?></td></tr>
            <tr><td>Facture d'origine</td><td><?= e($avoir['facture_numero']) ?></td></tr>
            <tr><td>Date</td><td><?= date('d/m/Y', strtotime($avoir['date_avoir'])) ?></td></tr>
            <tr><td>Motif</td><td><?= e($motifs[$avoir['motif']] ?? $avoir['motif']) ?></td></tr>
            <tr><td>Statut</td><td><?= $avoir['statut']==='emis'?'Émis':'Annulé' ?></td></tr>
        </table>
        <?php if (!empty($avoir['raison'])): ?>
        <div style="margin-top:12px;font-size:13px;color:var(--text-muted);background:#f6f8f7;padding:10px 12px;border-radius:8px"><?= e($avoir['raison']) ?></div>
        <?php endif; ?>
    </div>
</div>
