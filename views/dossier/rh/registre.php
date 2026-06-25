<?php
$mois_noms = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
$type_contrat_labels = ['CDI'=>'CDI','CDD'=>'CDD','Stage'=>'Stage','Consultant'=>'Consultant','Journalier'=>'Journalier'];
$statut_colors = ['actif'=>'#166534','inactif'=>'#991b1b','suspendu'=>'#92400e'];
?>
<div class="page-header">
    <div>
        <div class="page-title">Registre du personnel</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — <?= count($employes) ?> employé(s)</div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/dossier/rh/creer?id=<?= $entreprise['id'] ?>" class="btn btn-primary">+ Nouvel employé</a>
        <button onclick="window.print()" class="btn btn-secondary" style="display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Imprimer
        </button>
    </div>
</div>

<!-- Filtres -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
        <select name="statut" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;background:#fff">
            <option value="">Tous statuts</option>
            <option value="actif" <?= ($_GET['statut']??'')==='actif'?'selected':'' ?>>Actifs</option>
            <option value="inactif" <?= ($_GET['statut']??'')==='inactif'?'selected':'' ?>>Inactifs</option>
            <option value="suspendu" <?= ($_GET['statut']??'')==='suspendu'?'selected':'' ?>>Suspendus</option>
        </select>
        <select name="departement" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;background:#fff">
            <option value="">Tous départements</option>
            <?php foreach($departements as $dept): ?>
            <option value="<?= e($dept) ?>" <?= ($_GET['departement']??'')===$dept?'selected':'' ?>><?= e($dept) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- KPIs -->
<?php
$actifs    = count(array_filter($employes, fn($e) => $e['statut']==='actif'));
$inactifs  = count(array_filter($employes, fn($e) => $e['statut']==='inactif'));
$cdi_count = count(array_filter($employes, fn($e) => $e['type_contrat']==='CDI'));
$cdd_count = count(array_filter($employes, fn($e) => $e['type_contrat']==='CDD'));
?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <?php foreach([
        ['Effectif total', count($employes), '#1e3a5f', '#e8eef5'],
        ['Actifs', $actifs, '#166534', '#f0fdf4'],
        ['CDI', $cdi_count, '#1e3a5f', '#e8eef5'],
        ['CDD / Autres', count($employes) - $cdi_count, '#92400e', '#fffbeb'],
    ] as [$label,$val,$col,$bg]): ?>
    <div class="card" style="padding:16px 20px;background:<?= $bg ?>;border:1px solid <?= $col ?>22">
        <div style="font-size:26px;font-weight:800;color:<?= $col ?>"><?= $val ?></div>
        <div style="font-size:13px;color:#555;margin-top:2px"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if(empty($employes)): ?>
<div class="card" style="padding:40px;text-align:center;color:#888">
    <div style="font-size:32px;margin-bottom:8px">👥</div>
    <div>Aucun employé trouvé avec ces filtres.</div>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:#1e3a5f;color:#fff">
                <th style="padding:11px 14px;text-align:left;font-weight:600">Matricule</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">Nom & Prénom</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">Poste / Département</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">Contrat</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">Embauche</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">Salaire base</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">N° IPRES</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">N° CSS</th>
                <th style="padding:11px 14px;text-align:left;font-weight:600">Statut</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($employes as $i => $emp): ?>
            <tr style="background:<?= $i%2===0?'#fff':'#f8f9fb' ?>;border-bottom:1px solid #eee" onmouseover="this.style.background='#f0f4f8'" onmouseout="this.style.background='<?= $i%2===0?'#fff':'#f8f9fb' ?>'">
                <td style="padding:10px 14px;font-weight:600;color:#1e3a5f;font-family:monospace"><?= e($emp['matricule']) ?: '—' ?></td>
                <td style="padding:10px 14px">
                    <div style="font-weight:600;color:#1a1a1a"><?= e($emp['nom']) ?> <?= e($emp['prenom']) ?></div>
                    <?php if($emp['banque']): ?>
                    <div style="font-size:14px;color:#888"><?= e($emp['banque']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px">
                    <div style="color:#333"><?= e($emp['poste']) ?: '—' ?></div>
                    <?php if($emp['departement']): ?>
                    <div style="font-size:14px;color:#888"><?= e($emp['departement']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px">
                    <span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:14px;font-weight:700;background:<?= $emp['type_contrat']==='CDI'?'#dbeafe':'#fef3c7' ?>;color:<?= $emp['type_contrat']==='CDI'?'#1e3a5f':'#92400e' ?>">
                        <?= e($emp['type_contrat']) ?>
                    </span>
                    <?php if($emp['type_contrat']==='CDD' && !empty($emp['date_fin_contrat'])): ?>
                    <div style="font-size:14px;color:#991b1b;margin-top:2px">Fin: <?= date('d/m/Y', strtotime($emp['date_fin_contrat'])) ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px;color:#444">
                    <?= $emp['date_embauche'] ? date('d/m/Y', strtotime($emp['date_embauche'])) : '—' ?>
                </td>
                <td style="padding:10px 14px;font-weight:600;color:#1a1a1a;text-align:right">
                    <?= number_format($emp['salaire_base'], 0, ',', ' ') ?> F
                </td>
                <td style="padding:10px 14px;color:#555;font-family:monospace;font-size:13px"><?= e($emp['num_ipres']) ?: '—' ?></td>
                <td style="padding:10px 14px;color:#555;font-family:monospace;font-size:13px"><?= e($emp['num_css']) ?: '—' ?></td>
                <td style="padding:10px 14px">
                    <span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:14px;font-weight:700;background:<?= $emp['statut']==='actif'?'#f0fdf4':'#fef2f2' ?>;color:<?= $statut_colors[$emp['statut']] ?? '#555' ?>">
                        <?= ucfirst(e($emp['statut'])) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<style>
@media print {
    .no-print, .sidebar, .topbar, .page-header .btn { display: none !important; }
    body { font-size: 14px; }
    .card { box-shadow: none; }
}
</style>
