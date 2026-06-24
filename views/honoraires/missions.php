<?php
$type_colors = [
    'comptabilite' => ['border'=>'#1e3a5f', 'bg'=>'#e8eef5', 'text'=>'#1e3a5f', 'label'=>'Comptabilité'],
    'audit'        => ['border'=>'#7c3aed', 'bg'=>'#f5f3ff', 'text'=>'#7c3aed', 'label'=>'Audit'],
    'fiscalite'    => ['border'=>'#b91c1c', 'bg'=>'#fef2f2', 'text'=>'#b91c1c', 'label'=>'Fiscalité'],
    'paie'         => ['border'=>'#166534', 'bg'=>'#f0fdf4', 'text'=>'#166534', 'label'=>'Paie'],
    'conseil'      => ['border'=>'#c9a96e', 'bg'=>'#fffbeb', 'text'=>'#92400e', 'label'=>'Conseil'],
    'autre'        => ['border'=>'#555',    'bg'=>'#f3f4f6', 'text'=>'#374151', 'label'=>'Autre'],
];
$statut_styles = [
    'planifiee' => ['bg'=>'#e8eef5', 'text'=>'#1e3a5f', 'label'=>'Planifiée'],
    'en_cours'  => ['bg'=>'#dbeafe', 'text'=>'#1d4ed8', 'label'=>'En cours'],
    'terminee'  => ['bg'=>'#f0fdf4', 'text'=>'#166534', 'label'=>'Terminée'],
    'facturee'  => ['bg'=>'#fef3c7', 'text'=>'#92400e', 'label'=>'Facturée'],
    'annulee'   => ['bg'=>'#fef2f2', 'text'=>'#b91c1c', 'label'=>'Annulée'],
];
?>
<div class="page-header">
    <div>
        <div class="page-title" style="font-family:'Cormorant Garamond',serif;font-size:28px">Missions</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:3px"><?= count($missions) ?> mission(s)</div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/honoraires" class="btn btn-outline btn-sm">Honoraires</a>
        <a href="<?= APP_URL ?>/honoraires/mission/creer" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouvelle mission
        </a>
    </div>
</div>

<?php if(empty($missions)): ?>
<div class="card" style="padding:48px;text-align:center;color:#aaa">
    <div style="font-size:40px;margin-bottom:12px">📋</div>
    <div style="font-size:16px;font-weight:600;color:#555;margin-bottom:6px">Aucune mission</div>
    <div style="font-size:13px">Créez une première mission pour commencer.</div>
    <a href="<?= APP_URL ?>/honoraires/mission/creer" class="btn btn-primary" style="margin-top:16px;display:inline-flex">+ Nouvelle mission</a>
</div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px">
<?php foreach($missions as $m):
    $tc = $type_colors[$m['type_mission'] ?? $m['type'] ?? 'autre'] ?? $type_colors['autre'];
    $ss = $statut_styles[$m['statut']] ?? $statut_styles['planifiee'];
    $pct = 0;
    $h_passees = $m['heures_passees'] ?? $m['heures_realisees'] ?? 0;
    if(!empty($m['budget_heures']) && $m['budget_heures'] > 0 && $h_passees > 0) {
        $pct = min(100, round($h_passees / $m['budget_heures'] * 100));
    }
    $jours_restants = null;
    if(!empty($m['date_fin_prevue'])) {
        $jours_restants = (int)floor((strtotime($m['date_fin_prevue']) - time()) / 86400);
    }
?>
<div class="card" style="padding:0;overflow:hidden;border-top:4px solid <?= $tc['border'] ?>;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'" onmouseout="this.style.boxShadow=''">

    <!-- Header carte -->
    <div style="padding:16px 20px 12px;border-bottom:1px solid #f0f0f0">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px">
            <div>
                <div style="font-size:11px;font-weight:700;color:<?= $tc['text'] ?>;background:<?= $tc['bg'] ?>;display:inline-block;padding:2px 8px;border-radius:8px;margin-bottom:6px">
                    <?= $tc['label'] ?>
                </div>
                <div style="font-size:15px;font-weight:700;color:#1a1a1a;line-height:1.3"><?= e($m['libelle'] ?? $m['reference']) ?></div>
                <div style="font-size:12px;color:#888;margin-top:2px"><?= e($m['raison_sociale']) ?></div>
            </div>
            <span style="display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;background:<?= $ss['bg'] ?>;color:<?= $ss['text'] ?>;white-space:nowrap;flex-shrink:0">
                <?= $ss['label'] ?>
            </span>
        </div>

        <div style="display:flex;gap:16px;font-size:11px;color:#666;flex-wrap:wrap">
            <span>📅 <strong>Début :</strong> <?= $m['date_debut'] ? date('d/m/Y', strtotime($m['date_debut'])) : '—' ?></span>
            <?php if($m['date_fin_prevue']): ?>
            <span style="color:<?= $jours_restants !== null && $jours_restants < 0 ? '#b91c1c' : ($jours_restants < 14 ? '#c2410c' : '#555') ?>">
                🏁 <strong>Fin :</strong> <?= date('d/m/Y', strtotime($m['date_fin_prevue'])) ?>
                <?php if($jours_restants !== null): ?>
                (<?= $jours_restants < 0 ? abs($jours_restants).'j dépassé' : $jours_restants.'j restants' ?>)
                <?php endif; ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Infos mission -->
    <div style="padding:12px 20px;border-bottom:1px solid #f0f0f0">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div style="background:#f8f9fb;border-radius:6px;padding:10px 12px">
                <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.4px">Référence</div>
                <div style="font-size:13px;font-weight:700;color:#1e3a5f;font-family:monospace;margin-top:2px"><?= e($m['reference']) ?></div>
            </div>
            <div style="background:#f8f9fb;border-radius:6px;padding:10px 12px">
                <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.4px">Budget HT</div>
                <div style="font-size:13px;font-weight:700;color:#166534;font-family:monospace;margin-top:2px">
                    <?php
                    $budget_affiche = $m['montant_forfait'] ?? $m['budget_ht'] ?? null;
                    if (!$budget_affiche && !empty($m['taux_horaire']) && !empty($m['budget_heures'])) {
                        $budget_affiche = $m['taux_horaire'] * $m['budget_heures'];
                    }
                    echo $budget_affiche ? number_format($budget_affiche,0,',',' ').' F' : '—';
                    ?>
                </div>
            </div>
        </div>

        <?php if(!empty($m['heures_estimees']) && $m['heures_estimees'] > 0): ?>
        <div style="margin-top:12px">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:4px">
                <span>Avancement</span>
                <span style="font-weight:700;color:<?= $tc['text'] ?>"><?= $pct ?>%</span>
            </div>
            <div style="background:#eee;border-radius:100px;height:7px;overflow:hidden">
                <div style="height:100%;background:<?= $tc['border'] ?>;border-radius:100px;width:<?= $pct ?>%;transition:width .3s"></div>
            </div>
            <div style="font-size:10px;color:#aaa;margin-top:3px"><?= $h_passees ?>h / <?= $m['budget_heures'] ?>h estimées</div>
        </div>
        <?php endif; ?>

        <?php if(!empty($m['note'])): ?>
        <div style="margin-top:10px;font-size:11px;color:#666;font-style:italic;border-left:2px solid <?= $tc['border'] ?>;padding-left:8px;line-height:1.5">
            <?= e(substr($m['note'], 0, 100)).(strlen($m['note'])>100?'...':'') ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions -->
    <div style="padding:12px 20px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <a href="<?= APP_URL ?>/honoraires/mission/lettre-mission?mission_id=<?= $m['id'] ?>" target="_blank"
           style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:<?= $tc['bg'] ?>;color:<?= $tc['text'] ?>;border:1.5px solid <?= $tc['border'] ?>44;border-radius:7px;font-size:12px;font-weight:700;text-decoration:none">
            📄 Lettre de mission
        </a>
        <div style="margin-left:auto;font-size:11px;color:#aaa">
            Créée le <?= date('d/m/Y', strtotime($m['created_at'])) ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
