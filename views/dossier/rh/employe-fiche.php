<?php
$mois_noms = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
$sit_fam = ['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'];
$anciennete_ans = floor($anciennete_mois / 12);
$anciennete_reste = $anciennete_mois % 12;
$anciennete_label = ($anciennete_ans > 0 ? $anciennete_ans.' an'.($anciennete_ans>1?'s':'') : '').
                    ($anciennete_ans > 0 && $anciennete_reste > 0 ? ' ' : '').
                    ($anciennete_reste > 0 ? $anciennete_reste.' mois' : '') ?: 'Moins d\'un mois';
$initiales = strtoupper(substr($employe['prenom'],0,1).substr($employe['nom'],0,1));
$hue = crc32($employe['nom'].$employe['prenom']) % 360;
?>
<div class="page-header">
    <div style="display:flex;align-items:center;gap:16px">
        <?php if(!empty($employe['photo'])): ?>
        <img src="<?= APP_URL ?>/uploads/employes/<?= e($employe['photo']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #ddd">
        <?php else: ?>
        <div style="width:56px;height:56px;border-radius:50%;background:hsl(<?= $hue ?>,55%,85%);color:hsl(<?= $hue ?>,55%,30%);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;flex-shrink:0"><?= $initiales ?></div>
        <?php endif; ?>
        <div>
            <div class="page-title"><?= e($employe['prenom'].' '.$employe['nom']) ?></div>
            <div class="page-subtitle"><?= e($employe['poste'] ?: 'Poste non défini') ?> — <?= e($employe['departement'] ?: 'Département non défini') ?></div>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="<?= APP_URL ?>/dossier/rh/employe/bulletins?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" class="btn btn-outline btn-sm">📄 Bulletins</a>
        <a href="<?= APP_URL ?>/dossier/rh/employe/attestation?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" target="_blank" class="btn btn-outline btn-sm">🖨 Attestation</a>
        <a href="<?= APP_URL ?>/dossier/rh/employe/solde-tout-compte?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" target="_blank" class="btn btn-outline btn-sm">📋 Solde de tout compte</a>
        <a href="<?= APP_URL ?>/dossier/rh/edit?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" class="btn btn-primary btn-sm">✏️ Modifier</a>
    </div>
</div>

<!-- KPIs rapides -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="card" style="padding:16px 20px;background:#e8eef5;border:1px solid #1e3a5f22">
        <div style="font-size:14px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:.5px">Matricule</div>
        <div style="font-size:13px;font-weight:800;color:#1e3a5f;font-family:monospace;margin-top:4px"><?= e($employe['matricule']) ?: '—' ?></div>
    </div>
    <div class="card" style="padding:16px 20px;background:#f0fdf4;border:1px solid #16653422">
        <div style="font-size:14px;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.5px">Salaire de base</div>
        <div style="font-size:13px;font-weight:800;color:#166534;margin-top:4px"><?= number_format($employe['salaire_base'],0,',',' ') ?> F</div>
    </div>
    <div class="card" style="padding:16px 20px;background:#fffbeb;border:1px solid #c9a96e22">
        <div style="font-size:14px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.5px">Ancienneté</div>
        <div style="font-size:13px;font-weight:800;color:#92400e;margin-top:4px"><?= $anciennete_label ?></div>
    </div>
    <div class="card" style="padding:16px 20px;background:<?= $employe['statut']==='actif'?'#f0fdf4':'#fef2f2' ?>;border:1px solid <?= $employe['statut']==='actif'?'#16653422':'#99111122' ?>">
        <div style="font-size:14px;font-weight:700;color:<?= $employe['statut']==='actif'?'#166534':'#991b1b' ?>;text-transform:uppercase;letter-spacing:.5px">Statut</div>
        <div style="font-size:13px;font-weight:800;color:<?= $employe['statut']==='actif'?'#166534':'#991b1b' ?>;margin-top:4px"><?= ucfirst(e($employe['statut'])) ?></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Colonne gauche -->
<div style="display:flex;flex-direction:column;gap:20px">

<!-- Identité -->
<div class="card" style="padding:24px">
    <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        👤 Identité
    </div>
    <?php $rows = [
        ['Nom complet', $employe['prenom'].' '.$employe['nom']],
        ['Sexe', $employe['sexe']==='F' ? 'Féminin' : 'Masculin'],
        ['Date de naissance', $employe['date_naissance'] ? date('d/m/Y',strtotime($employe['date_naissance'])) : '—'],
        ['Lieu de naissance', $employe['lieu_naissance'] ?: '—'],
        ['Nationalité', $employe['nationalite'] ?: '—'],
        ['N° CNI / Passeport', $employe['num_cni'] ?: '—'],
        ['Situation familiale', $sit_fam[$employe['situation_familiale']] ?? '—'],
        ['Enfants à charge', $employe['nombre_enfants'] ?? '0'],
        ['Téléphone', $employe['telephone'] ?: '—'],
        ['Email', $employe['email'] ?: '—'],
        ['Adresse', $employe['adresse'] ?: '—'],
        ['Lieu de travail', $employe['lieu_travail'] ?: '—'],
    ]; ?>
    <?php foreach($rows as $i => [$label, $val]): ?>
    <div style="display:flex;justify-content:space-between;padding:7px 0;<?= $i>0?'border-top:1px solid #f0f0f0':'' ?>">
        <span style="font-size:13px;color:#888;min-width:160px"><?= $label ?></span>
        <span style="font-size:13px;color:#1a1a1a;font-weight:500;text-align:right"><?= e($val) ?></span>
    </div>
    <?php endforeach; ?>
</div>

<!-- Organismes sociaux -->
<div class="card" style="padding:24px">
    <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        🏛 Organismes sociaux & Fiscal
    </div>
    <?php $rows2 = [
        ['N° IPRES', $employe['num_ipres'] ?: '—'],
        ['N° CSS', $employe['num_css'] ?: '—'],
        ['N° IPM', $employe['num_ipm'] ?: '—'],
        ['Régime fiscal', $employe['regime_fiscal']==='exonere' ? 'Exonéré' : 'Imposable (IR)'],
        ['Nombre de parts', $employe['nombre_parts'] ?? '1.0'],
    ]; ?>
    <?php foreach($rows2 as $i => [$label, $val]): ?>
    <div style="display:flex;justify-content:space-between;padding:7px 0;<?= $i>0?'border-top:1px solid #f0f0f0':'' ?>">
        <span style="font-size:13px;color:#888;min-width:160px"><?= $label ?></span>
        <span style="font-size:13px;color:#1a1a1a;font-weight:500;font-family:<?= in_array($label,['N° IPRES','N° CSS','N° IPM'])?'monospace':'inherit' ?>"><?= e($val) ?></span>
    </div>
    <?php endforeach; ?>
</div>

<!-- Paiement -->
<div class="card" style="padding:24px">
    <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        💳 Paiement
    </div>
    <?php $rows3 = [
        ['Mode', ucfirst(str_replace('_',' ',$employe['mode_paiement'] ?? 'virement'))],
        ['Banque', $employe['banque'] ?: '—'],
        ['IBAN / Compte', $employe['iban'] ?: '—'],
    ]; ?>
    <?php foreach($rows3 as $i => [$label, $val]): ?>
    <div style="display:flex;justify-content:space-between;padding:7px 0;<?= $i>0?'border-top:1px solid #f0f0f0':'' ?>">
        <span style="font-size:13px;color:#888;min-width:120px"><?= $label ?></span>
        <span style="font-size:13px;color:#1a1a1a;font-weight:500;font-family:<?= $label==='IBAN / Compte'?'monospace':'inherit' ?>"><?= e($val) ?></span>
    </div>
    <?php endforeach; ?>
</div>

</div><!-- fin colonne gauche -->

<!-- Colonne droite -->
<div style="display:flex;flex-direction:column;gap:20px">

<!-- Contrat -->
<div class="card" style="padding:24px">
    <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        📋 Contrat & Poste
    </div>
    <?php $rows4 = [
        ['Poste', $employe['poste'] ?: '—'],
        ['Département', $employe['departement'] ?: '—'],
        ['Catégorie', $employe['categorie'] ?: '—'],
        ['Type de contrat', $employe['type_contrat'] ?: '—'],
        ['Date d\'embauche', $employe['date_embauche'] ? date('d/m/Y',strtotime($employe['date_embauche'])) : '—'],
        ['Date fin contrat', $employe['date_fin_contrat'] ? date('d/m/Y',strtotime($employe['date_fin_contrat'])) : '—'],
        ['Période d\'essai', ($employe['periode_essai_mois'] ?? 0) > 0 ? $employe['periode_essai_mois'].' mois' : 'Aucune'],
    ]; ?>
    <?php foreach($rows4 as $i => [$label, $val]): ?>
    <div style="display:flex;justify-content:space-between;padding:7px 0;<?= $i>0?'border-top:1px solid #f0f0f0':'' ?>">
        <span style="font-size:13px;color:#888;min-width:150px"><?= $label ?></span>
        <span style="font-size:13px;color:#1a1a1a;font-weight:500"><?= e($val) ?></span>
    </div>
    <?php endforeach; ?>
</div>

<!-- Rémunération -->
<div class="card" style="padding:24px">
    <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        💰 Rémunération
    </div>
    <?php
    $rems = [
        ['Salaire de base', $employe['salaire_base']],
        ['Sursalaire', $employe['sursalaire']],
        ['Ind. logement', $employe['indemnite_logement']],
        ['Ind. transport', $employe['indemnite_transport']],
        ['Ind. représentation', $employe['indemnite_representation']],
        ['Autres indemnités', $employe['autres_indemnites'] ?? 0],
    ];
    $brut_total = array_sum(array_column($rems,'1'));
    ?>
    <?php foreach($rems as $i => [$label, $val]): if((float)$val == 0 && $label !== 'Salaire de base') continue; ?>
    <div style="display:flex;justify-content:space-between;padding:7px 0;<?= $i>0?'border-top:1px solid #f0f0f0':'' ?>">
        <span style="font-size:13px;color:#888"><?= $label ?></span>
        <span style="font-size:13px;color:#1a1a1a;font-weight:600;font-family:monospace"><?= number_format((float)$val,0,',',' ') ?> F</span>
    </div>
    <?php endforeach; ?>
    <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:2px solid #1e3a5f;margin-top:4px">
        <span style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase">Brut estimé</span>
        <span style="font-size:13px;font-weight:800;color:#1e3a5f;font-family:monospace"><?= number_format($brut_total,0,',',' ') ?> F</span>
    </div>
</div>

<!-- Congés -->
<?php if($solde_conges): ?>
<div class="card" style="padding:24px">
    <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        🌴 Congés <?= date('Y') ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
        <?php foreach([
            ['Acquis N', $solde_conges['jours_acquis'], '#1e3a5f'],
            ['Report N-1', $solde_conges['jours_reportes_n1'] ?? 0, '#c9a96e'],
            ['Pris', $solde_conges['jours_pris'], '#b91c1c'],
            ['Solde restant', $solde_conges['jours_restants'], '#166534'],
        ] as [$label, $val, $col]): ?>
        <div style="padding:12px;background:#f8f9fb;border-radius:8px;border-left:3px solid <?= $col ?>">
            <div style="font-size:13px;color:#888;text-transform:uppercase;letter-spacing:.4px"><?= $label ?></div>
            <div style="font-size:20px;font-weight:800;color:<?= $col ?>;margin-top:2px"><?= number_format((float)$val,1) ?> j</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Derniers bulletins -->
<div class="card" style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #1e3a5f15">
        <div style="font-size:13px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.8px">📊 Derniers bulletins</div>
        <a href="<?= APP_URL ?>/dossier/rh/employe/bulletins?id=<?= $entreprise['id'] ?>&employe_id=<?= $employe['id'] ?>" style="font-size:13px;color:#1e3a5f;text-decoration:none">Voir tout →</a>
    </div>
    <?php if(empty($derniers_bulletins)): ?>
    <div style="text-align:center;color:#aaa;padding:20px;font-size:13px">Aucun bulletin généré</div>
    <?php else: ?>
    <?php foreach($derniers_bulletins as $b): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f0">
        <div>
            <div style="font-size:13px;font-weight:600;color:#1a1a1a"><?= $mois_noms[$b['periode_mois']] ?> <?= $b['periode_annee'] ?></div>
            <div style="font-size:14px;color:#888">Brut : <?= number_format($b['salaire_brut'],0,',',' ') ?> F</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:13px;font-weight:700;color:#166534"><?= number_format($b['net_a_payer'],0,',',' ') ?> F</div>
            <a href="<?= APP_URL ?>/dossier/rh/bulletin?id=<?= $entreprise['id'] ?>&bulletin_id=<?= $b['id'] ?>" target="_blank" style="font-size:14px;color:#1e3a5f;text-decoration:none">PDF →</a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

</div><!-- fin colonne droite -->
</div>
