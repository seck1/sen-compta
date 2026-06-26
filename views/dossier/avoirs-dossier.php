<?php $fmt = fn($v)=>number_format((float)$v,0,',',' ');
$motifs=['retour'=>'Retour','remboursement'=>'Remboursement','erreur'=>'Erreur','geste_commercial'=>'Geste commercial','autre'=>'Autre']; ?>
<style>
.avd-flash{padding:13px 18px;border-radius:11px;margin-bottom:18px;font-size:14px;font-weight:500;}
.avd-flash.ok{background:rgba(31,110,78,0.10);color:#1f6e4e;border:1px solid rgba(31,110,78,0.25);}
.avd-flash.err{background:rgba(192,57,43,0.08);color:#c0392b;border:1px solid rgba(192,57,43,0.25);}
.avd-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:22px;}
.avd-card h3{font-size:15px;font-weight:700;color:var(--navy-dark);padding:16px 18px 0;}
.avd-table{width:100%;border-collapse:collapse;}
.avd-table th{text-align:left;padding:10px 16px;font-size:8.5pt;text-transform:uppercase;letter-spacing:.4px;color:#fff;background:var(--green);}
.avd-table th.r,.avd-table td.r{text-align:right;}
.avd-table td{padding:11px 16px;border-bottom:1px solid var(--border);font-size:14px;}
.avd-table tr:hover{background:#f8fafc;}
.avd-num{font-family:monospace;font-weight:700;color:#a8443f;}
.avd-mono{font-family:monospace;}
.avd-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:rgba(168,68,63,.08);color:#a8443f;border:1px solid rgba(168,68,63,.25);border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;}
.avd-empty{text-align:center;padding:34px 20px;color:var(--text-muted);font-size:14px;}
.avd-help{background:#fff;border:1px solid var(--border);border-left:4px solid var(--green);border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:13.5px;color:var(--text);line-height:1.6;}
.avd-help b{color:var(--navy-dark);}
</style>

<div class="page-header" style="margin-bottom:16px">
    <div>
        <h1 class="page-title">Avoirs de vente</h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> · Notes de crédit sur factures de vente · Exercice <?= (int)$exercice ?></p>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="avd-flash ok"><?= e($_SESSION['flash_success']) ?></div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="avd-flash err"><?= e($_SESSION['flash_error']) ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="avd-help">
    💡 Un <b>avoir de vente</b> annule tout ou partie d'une facture de vente. Le système crée automatiquement l'<b>écriture d'extourne</b> (inverse) dans le journal VTE : le client (411) est crédité, les produits (70x) et la TVA sont débités. Choisissez une facture ci-dessous pour créer son avoir.
</div>

<!-- Avoirs déjà émis -->
<div class="avd-card">
    <h3>Avoirs émis</h3>
    <?php if (empty($avoirs)): ?>
    <div class="avd-empty">Aucun avoir émis sur cet exercice.</div>
    <?php else: ?>
    <table class="avd-table">
        <thead><tr><th>N° Avoir</th><th>Facture d'origine</th><th>Date</th><th>Motif</th><th class="r">Taux</th><th class="r">Montant TTC</th></tr></thead>
        <tbody>
        <?php foreach ($avoirs as $a): ?>
            <tr>
                <td><span class="avd-num"><?= e($a['numero']) ?></span></td>
                <td class="avd-mono"><?= e($a['numero_facture_origine']) ?></td>
                <td><?= date('d/m/Y', strtotime($a['date_avoir'])) ?></td>
                <td><?= e($motifs[$a['motif']] ?? $a['motif']) ?></td>
                <td class="r"><?= rtrim(rtrim(number_format((float)$a['taux'],1,',',' '),'0'),',') ?>%</td>
                <td class="r avd-mono"><?= $fmt($a['montant']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Factures de vente extournables -->
<div class="avd-card">
    <h3>Factures de vente</h3>
    <?php if (empty($factures)): ?>
    <div class="avd-empty">Aucune facture de vente (écriture du journal VTE avec un client) sur cet exercice.</div>
    <?php else: ?>
    <table class="avd-table">
        <thead><tr><th>N° Pièce</th><th>N° Facture</th><th>Date</th><th>Libellé</th><th class="r">Montant client</th><th class="r">Action</th></tr></thead>
        <tbody>
        <?php foreach ($factures as $f): ?>
            <tr>
                <td class="avd-mono"><?= e($f['numero_piece']) ?></td>
                <td class="avd-mono"><?= e($f['numero_facture'] ?: '—') ?></td>
                <td><?= date('d/m/Y', strtotime($f['date_ecriture'])) ?></td>
                <td><?= e($f['libelle']) ?></td>
                <td class="r avd-mono"><?= $fmt($f['montant_client']) ?></td>
                <td class="r">
                    <a class="avd-btn" href="<?= APP_URL ?>/dossier/avoirs/creer?id=<?= $entreprise['id'] ?>&ecriture=<?= (int)$f['id'] ?>">+ Créer un avoir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
