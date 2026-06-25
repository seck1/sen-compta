<?php
$dept_colors = [
    'Direction'        => ['bg'=>'#1e3a5f', 'text'=>'#fff', 'accent'=>'#c9a96e'],
    'Finance'          => ['bg'=>'#1e4d2b', 'text'=>'#fff', 'accent'=>'#86efac'],
    'Comptabilité'     => ['bg'=>'#1a3a5c', 'text'=>'#fff', 'accent'=>'#93c5fd'],
    'RH'               => ['bg'=>'#4a1c40', 'text'=>'#fff', 'accent'=>'#f0abfc'],
    'Commercial'       => ['bg'=>'#7c2d12', 'text'=>'#fff', 'accent'=>'#fdba74'],
    'Informatique'     => ['bg'=>'#1e3a5f', 'text'=>'#fff', 'accent'=>'#67e8f9'],
    'Production'       => ['bg'=>'#3f3f00', 'text'=>'#fff', 'accent'=>'#d9f99d'],
    'Marketing'        => ['bg'=>'#4c0519', 'text'=>'#fff', 'accent'=>'#fda4af'],
    'Logistique'       => ['bg'=>'#1c1917', 'text'=>'#fff', 'accent'=>'#d6d3d1'],
    'Non défini'       => ['bg'=>'#374151', 'text'=>'#fff', 'accent'=>'#d1d5db'],
];
$default_color = ['bg'=>'#374151', 'text'=>'#fff', 'accent'=>'#d1d5db'];
$contrat_colors = ['CDI'=>'#dbeafe','CDD'=>'#fef3c7','Stage'=>'#f3e8ff','Consultant'=>'#fce7f3','Journalier'=>'#fef9c3'];
$contrat_text   = ['CDI'=>'#1e3a5f','CDD'=>'#92400e','Stage'=>'#6b21a8','Consultant'=>'#be185d','Journalier'=>'#854d0e'];
?>
<div class="page-header">
    <div>
        <div class="page-title">Organigramme</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — <?= count($employes) ?> employé(s) actif(s)</div>
    </div>
    <div style="display:flex;gap:10px">
        <button onclick="window.print()" class="btn btn-secondary" style="display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Imprimer
        </button>
    </div>
</div>

<?php if(empty($par_dept)): ?>
<div class="card" style="padding:40px;text-align:center;color:#888">
    <div style="font-size:40px;margin-bottom:10px">🏢</div>
    <div style="font-size:13px;font-weight:600;margin-bottom:6px">Aucun employé actif</div>
    <div style="font-size:14px">Ajoutez des employés pour visualiser l'organigramme.</div>
</div>
<?php else: ?>

<!-- Vue grille par département -->
<div style="display:flex;flex-direction:column;gap:24px">
<?php foreach($par_dept as $dept => $membres):
    $color = $dept_colors[$dept] ?? $default_color;
    $nb = count($membres);
?>
    <div class="card" style="padding:0;overflow:hidden;border-top:4px solid <?= $color['accent'] ?>">
        <!-- Département header -->
        <div style="background:<?= $color['bg'] ?>;color:<?= $color['text'] ?>;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:36px;height:36px;border-radius:50%;background:<?= $color['accent'] ?>22;border:2px solid <?= $color['accent'] ?>;display:flex;align-items:center;justify-content:center;font-size:13px">
                    🏢
                </div>
                <div>
                    <div style="font-size:13px;font-weight:800;letter-spacing:.3px"><?= e($dept) ?></div>
                    <div style="font-size:14px;opacity:.7"><?= $nb ?> employé<?= $nb>1?'s':'' ?></div>
                </div>
            </div>
            <div style="font-size:24px;font-weight:900;opacity:.3"><?= $nb ?></div>
        </div>

        <!-- Membres -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0;padding:0">
        <?php foreach($membres as $j => $emp):
            $initiales = strtoupper(substr($emp['prenom'],0,1).substr($emp['nom'],0,1));
            $hue = (crc32($emp['nom'].$emp['prenom']) % 12) * 30;
            $cc = $contrat_colors[$emp['type_contrat']] ?? '#f3f4f6';
            $ct = $contrat_text[$emp['type_contrat']] ?? '#374151';
        ?>
            <div style="padding:16px 18px;border-right:1px solid #eee;border-bottom:1px solid #eee;background:#fff;transition:background .15s"
                 onmouseover="this.style.background='#f0f4f8'" onmouseout="this.style.background='#fff'">
                <div style="display:flex;align-items:flex-start;gap:12px">
                    <div style="width:42px;height:42px;border-radius:50%;background:hsl(<?= $hue ?>,60%,88%);color:hsl(<?= $hue ?>,60%,30%);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">
                        <?= $initiales ?>
                    </div>
                    <div style="min-width:0">
                        <div style="font-weight:700;font-size:14px;color:#1a1a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= e($emp['prenom']) ?> <?= e($emp['nom']) ?>
                        </div>
                        <div style="font-size:14px;color:#666;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= e($emp['poste']) ?: 'Poste non défini' ?>
                        </div>
                        <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
                            <span style="display:inline-block;padding:1px 7px;border-radius:8px;font-size:13px;font-weight:700;background:<?= $cc ?>;color:<?= $ct ?>">
                                <?= e($emp['type_contrat']) ?>
                            </span>
                            <?php if($emp['matricule']): ?>
                            <span style="display:inline-block;padding:1px 7px;border-radius:8px;font-size:13px;background:#f3f4f6;color:#666;font-family:monospace">
                                <?= e($emp['matricule']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php if($emp['date_embauche']): ?>
                        <div style="font-size:13px;color:#999;margin-top:4px">
                            Depuis <?= date('M Y', strtotime($emp['date_embauche'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- Légende -->
<div style="margin-top:24px;padding:14px 18px;background:#f8f9fb;border-radius:8px;font-size:13px;color:#555">
    <strong style="color:#1e3a5f">Légende contrats :</strong>&nbsp;
    <?php foreach($contrat_colors as $type => $bg): ?>
    <span style="display:inline-flex;align-items:center;gap:4px;margin-right:14px">
        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:<?= $bg ?>"></span>
        <?= $type ?>
    </span>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<style>
@media print {
    .no-print, .sidebar, .topbar { display: none !important; }
    .card { box-shadow: none; page-break-inside: avoid; }
    body { font-size: 14px; }
}
</style>
