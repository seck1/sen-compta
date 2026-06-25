<?php
// $tafire from EtatsFinanciersController::tafire()
extract($tafire);
?>
<style>
.tf-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
.tf-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
.tf-col { background:#fff; border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.tf-col-header { padding:14px 20px; font-size:16px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; border-bottom:2px solid var(--border); }
.tf-col.emplois .tf-col-header { background:rgba(239,68,68,0.05); color:#dc2626; }
.tf-col.ressources .tf-col-header { background:rgba(31,110,78,0.05); color:#1f6e4e; }
.tf-row { display:flex; justify-content:space-between; padding:10px 20px; border-bottom:1px solid rgba(228,233,240,0.4); font-size:16px; gap:12px; }
.tf-row.sub { padding-left:32px; color:var(--text-muted); font-size:15px; }
.tf-row.subtotal { background:rgba(240,243,248,0.7); font-weight:600; }
.tf-row.total-row { background:var(--navy-dark); color:white; font-weight:700; font-size:17px; }
.tf-num { font-family:monospace; text-align:right; min-width:140px; }
.tf-summary { background:#fff; border:1px solid var(--border); border-radius:14px; padding:24px; }
.tf-summary h3 { font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:400; margin-bottom:16px; color:var(--navy-dark); }
.tf-summary-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.tf-sum-item { border:1px solid var(--border); border-radius:12px; padding:16px; text-align:center; }
.tf-sum-label { font-size:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.tf-sum-val { font-family:'Cormorant Garamond',serif; font-size:26px; font-weight:600; }
.tf-sum-val.pos { color:#1f6e4e; }
.tf-sum-val.neg { color:#dc2626; }
</style>

<div class="tf-header">
    <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:31px;font-weight:400;color:var(--navy-dark)">
            TAFIRE — Exercice <?= e($exerciceN) ?>
        </div>
        <div style="font-size:16px;color:var(--text-muted);margin-top:4px">
            Tableau de Financement des Ressources et Emplois · Comparaison N / N-1 (<?= $exerciceN1 ?>)
        </div>
    </div>
    <button onclick="window.print()" class="btn btn-outline btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
        Imprimer
    </button>
</div>

<div class="tf-grid">
    <!-- EMPLOIS -->
    <div class="tf-col emplois">
        <div class="tf-col-header">Emplois</div>
        <div class="tf-row sub">
            <span>Acquisitions d'immobilisations</span>
            <span class="tf-num"><?= number_format($acquisitions_immo,0,',',' ') ?></span>
        </div>
        <div class="tf-row sub">
            <span>Remboursements dettes financières</span>
            <span class="tf-num"><?= number_format($remb_dettes,0,',',' ') ?></span>
        </div>
        <div class="tf-row sub">
            <span>Dividendes distribués</span>
            <span class="tf-num"><?= number_format($dividendes,0,',',' ') ?></span>
        </div>
        <div class="tf-row total-row">
            <span>TOTAL EMPLOIS</span>
            <span class="tf-num"><?= number_format($emplois_total,0,',',' ') ?></span>
        </div>
    </div>

    <!-- RESSOURCES -->
    <div class="tf-col ressources">
        <div class="tf-col-header">Ressources</div>
        <div class="tf-row sub">
            <span>Résultat net de l'exercice</span>
            <span class="tf-num" style="<?= $resultat_net >= 0 ? 'color:#1f6e4e' : 'color:#dc2626' ?>">
                <?= number_format($resultat_net,0,',',' ') ?>
            </span>
        </div>
        <div class="tf-row sub">
            <span>Dotations aux amortissements</span>
            <span class="tf-num"><?= number_format($dotations_amort,0,',',' ') ?></span>
        </div>
        <div class="tf-row sub">
            <span>Augmentation dettes financières</span>
            <span class="tf-num"><?= number_format($aug_dettes_fin,0,',',' ') ?></span>
        </div>
        <div class="tf-row sub">
            <span>Produits de cessions d'immobilisations</span>
            <span class="tf-num"><?= number_format($cessions_immo,0,',',' ') ?></span>
        </div>
        <div class="tf-row total-row">
            <span>TOTAL RESSOURCES</span>
            <span class="tf-num"><?= number_format($ressources_total,0,',',' ') ?></span>
        </div>
    </div>
</div>

<!-- Analyse BFR et Trésorerie -->
<div class="tf-summary">
    <h3>Analyse des variations — FRN, BFR et Trésorerie</h3>
    <div class="tf-summary-grid">
        <div class="tf-sum-item">
            <div class="tf-sum-label">Variation FRN<br><small>Fond de Roulement Net</small></div>
            <div class="tf-sum-val <?= $variation_frn >= 0 ? 'pos' : 'neg' ?>">
                <?= $variation_frn >= 0 ? '+' : '' ?><?= number_format($variation_frn/1000,0,',',' ') ?> K
            </div>
            <div style="font-size:15px;color:var(--text-muted);margin-top:4px"><?= formatMontant($variation_frn) ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:6px">= Ressources – Emplois</div>
        </div>
        <div class="tf-sum-item">
            <div class="tf-sum-label">Variation BFR<br><small>Besoin en Fonds de Roulement</small></div>
            <div class="tf-sum-val <?= $variation_bfr >= 0 ? 'neg' : 'pos' ?>">
                <?= $variation_bfr >= 0 ? '+' : '' ?><?= number_format($variation_bfr/1000,0,',',' ') ?> K
            </div>
            <div style="font-size:15px;color:var(--text-muted);margin-top:4px"><?= formatMontant($variation_bfr) ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:6px">
                ΔActif circ. <?= formatMontant($var_actif_circ) ?> — ΔPassif circ. <?= formatMontant($var_passif_circ) ?>
            </div>
        </div>
        <div class="tf-sum-item" style="border:2px solid <?= $variation_tresorerie >= 0 ? '#1f6e4e' : '#ef4444' ?>">
            <div class="tf-sum-label">Variation Trésorerie<br><small>= ΔFRN – ΔBFR</small></div>
            <div class="tf-sum-val <?= $variation_tresorerie >= 0 ? 'pos' : 'neg' ?>">
                <?= $variation_tresorerie >= 0 ? '+' : '' ?><?= number_format($variation_tresorerie/1000,0,',',' ') ?> K
            </div>
            <div style="font-size:15px;color:var(--text-muted);margin-top:4px"><?= formatMontant($variation_tresorerie) ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:6px">Contrôle direct : <?= formatMontant($var_trezo_directe) ?></div>
        </div>
    </div>

    <div style="margin-top:20px;padding:14px 18px;background:rgba(30,58,95,0.04);border-radius:10px;font-size:15px;color:var(--text-muted)">
        <strong style="color:var(--navy)">Lecture :</strong>
        <?php if($variation_tresorerie >= 0): ?>
            La trésorerie s'est améliorée de <?= formatMontant($variation_tresorerie) ?> sur l'exercice <?= $exerciceN ?>.
        <?php else: ?>
            La trésorerie s'est dégradée de <?= formatMontant(abs($variation_tresorerie)) ?> sur l'exercice <?= $exerciceN ?>.
        <?php endif; ?>
        <?php if($variation_frn >= 0): ?>
            Le FRN est positif, signe d'une bonne structure financière à long terme.
        <?php else: ?>
            Le FRN est négatif, ce qui indique un déséquilibre structurel à corriger.
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .ent-colorbar, .tf-header .btn { display:none !important; }
    .main-wrap { margin-left:0 !important; }
}
</style>
