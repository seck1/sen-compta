<?php $fmt = fn($v)=>number_format((float)$v,0,',',' ');
$motifs=['retour'=>'Retour','remboursement'=>'Remboursement','geste_commercial'=>'Geste commercial','erreur'=>'Erreur','autre'=>'Autre']; ?>
<style>
.avl-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;}
.avl-table{width:100%;border-collapse:collapse;}
.avl-table th{text-align:left;padding:11px 16px;font-size:8.5pt;text-transform:uppercase;letter-spacing:.4px;color:#fff;background:var(--green);}
.avl-table th.r,.avl-table td.r{text-align:right;}
.avl-table td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:14px;}
.avl-table tr:hover{background:#f8fafc;}
.avl-num{font-family:monospace;font-weight:700;color:#a8443f;}
.avl-empty{text-align:center;padding:48px 20px;color:var(--text-muted);}
.avl-link{color:#1f6e4e;font-weight:600;text-decoration:none;}
</style>

<div class="page-header" style="margin-bottom:18px">
    <div>
        <h1 class="page-title">Avoirs</h1>
        <p class="page-subtitle">Notes de crédit émises sur les factures</p>
    </div>
</div>

<div class="avl-card">
    <?php if (empty($avoirs)): ?>
    <div class="avl-empty">
        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" style="opacity:.4;margin-bottom:10px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <div style="font-weight:600">Aucun avoir pour le moment</div>
        <div style="font-size:13px;margin-top:4px">Créez un avoir depuis le détail d'une facture (bouton « Créer un avoir »).</div>
    </div>
    <?php else: ?>
    <table class="avl-table">
        <thead><tr><th>N° Avoir</th><th>Client</th><th>Facture</th><th>Date</th><th>Motif</th><th class="r">Montant TTC</th><th class="r">Action</th></tr></thead>
        <tbody>
        <?php foreach ($avoirs as $a): ?>
            <tr>
                <td><span class="avl-num"><?= e($a['numero']) ?></span></td>
                <td><?= e($a['client']) ?></td>
                <td><?= e($a['facture_numero']) ?></td>
                <td><?= date('d/m/Y', strtotime($a['date_avoir'])) ?></td>
                <td><?= e($motifs[$a['motif']] ?? $a['motif']) ?></td>
                <td class="r" style="font-family:monospace"><?= $fmt($a['montant_ttc']) ?></td>
                <td class="r"><a class="avl-link" href="<?= APP_URL ?>/commercial/avoirs/voir?id=<?= (int)$a['id'] ?>">Voir →</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
