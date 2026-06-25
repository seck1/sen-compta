<?php
// $entreprise, $regime, $modules, $label, $color, $echeances, $caHt, $exercice available
$isAdmin = isAdmin();

// Format number helper
$fmt = fn(float $n): string => number_format($n, 0, ',', ' ');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Fiche Régime Fiscal</h1>
        <p class="page-subtitle">Obligations fiscales et modules applicables pour <?= e($entreprise['raison_sociale']) ?></p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <?php if ($isAdmin): ?>
        <a href="<?= APP_URL ?>/entreprises/edit?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            Modifier le régime
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Régime principal badge -->
<div style="display:flex;align-items:stretch;gap:20px;margin-bottom:24px;flex-wrap:wrap">
    <div class="card" style="flex:1;min-width:280px;border-left:5px solid <?= $color ?>;padding:24px 28px">
        <div style="display:flex;align-items:flex-start;gap:18px">
            <div style="width:56px;height:56px;border-radius:14px;background:<?= $color ?>20;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="<?= $color ?>" style="width:28px;height:28px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                    <span style="display:inline-flex;padding:4px 12px;border-radius:20px;font-size:15px;font-weight:700;color:#fff;background:<?= $color ?>;letter-spacing:1px"><?= e($regime) ?></span>
                </div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:20px;color:var(--navy-dark);font-weight:600;margin-bottom:4px"><?= e($label) ?></div>
                <div style="font-size:16px;color:var(--text-muted)">
                    <?php if ($caHt > 0): ?>
                    CA HT exercice <?= $exercice ?> : <strong style="color:var(--navy)"><?= $fmt($caHt) ?> FCFA</strong>
                    <?php else: ?>
                    Aucun chiffre d'affaires enregistré pour l'exercice <?= $exercice ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Infos complémentaires -->
    <div class="card" style="flex:1;min-width:240px">
        <div style="font-size:15px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px">Informations fiscales</div>
        <div style="display:grid;gap:10px">
            <?php if ($entreprise['numero_contribuable'] ?? ''): ?>
            <div style="display:flex;justify-content:space-between;font-size:16px">
                <span style="color:var(--text-muted)">N° Contribuable</span>
                <span style="font-weight:500;color:var(--navy)"><?= e($entreprise['numero_contribuable']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($entreprise['ninea'] ?? ''): ?>
            <div style="display:flex;justify-content:space-between;font-size:16px">
                <span style="color:var(--text-muted)">NINEA</span>
                <span style="font-weight:500;color:var(--navy)"><?= e($entreprise['ninea']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($entreprise['numero_registre_commerce'] ?? ''): ?>
            <div style="display:flex;justify-content:space-between;font-size:16px">
                <span style="color:var(--text-muted)">RCCM</span>
                <span style="font-weight:500;color:var(--navy)"><?= e($entreprise['numero_registre_commerce']) ?></span>
            </div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-size:16px">
                <span style="color:var(--text-muted)">Régime TVA</span>
                <span style="font-weight:500;color:var(--navy)"><?= ucfirst(str_replace('_', ' ', $entreprise['regime_tva'] ?? 'mensuel')) ?></span>
            </div>
            <?php if ($entreprise['ca_annuel_estime'] ?? 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:16px">
                <span style="color:var(--text-muted)">CA estimé</span>
                <span style="font-weight:500;color:var(--navy)"><?= $fmt((float)$entreprise['ca_annuel_estime']) ?> FCFA</span>
            </div>
            <?php endif; ?>
            <?php if ($regime === 'EXONERE' && ($entreprise['date_debut_exoneration'] ?? '')): ?>
            <div style="display:flex;justify-content:space-between;font-size:16px">
                <span style="color:var(--text-muted)">Période exonération</span>
                <span style="font-weight:500;color:#b45309"><?= date('d/m/Y', strtotime($entreprise['date_debut_exoneration'])) ?> → <?= $entreprise['date_fin_exoneration'] ? date('d/m/Y', strtotime($entreprise['date_fin_exoneration'])) : '?' ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modules inclus / exclus -->
<div class="card" style="margin-bottom:24px">
    <div style="font-size:18px;font-weight:600;color:var(--navy-dark);margin-bottom:18px">Obligations & modules fiscaux</div>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--bg)">
                <th style="padding:10px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Obligation / Module</th>
                <th style="padding:10px 16px;text-align:center;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Applicable</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Détails</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $moduleRows = [
            'tva'         => ['TVA',                       'Déclaration mensuelle ou trimestrielle'],
            'is'          => ['IS — Impôt sur les Sociétés', '30% bénéfice imposable, min 500 000 F'],
            'cfce'        => ['CFCE',                      '3% masse salariale brute (charge employeur)'],
            'patente'     => ['Patente',                   'Déclaration annuelle'],
            'cgu'         => ['CGU — Contribution Globale Unique', '5% CA HT (remplace IS + TVA + Patente)'],
            'bnc'         => ['IR / BNC (Bénéfices non commerciaux)', 'Barème progressif CGI Art. 163'],
            'liberatoire' => ['Impôt Libératoire',         'Montant fixe trimestriel selon secteur'],
            'exonere'     => ['Exonération IS',            'IS = 0% pendant la période conventionnée'],
        ];
        foreach ($moduleRows as $key => [$libelle, $detail]):
            $val = $modules[$key] ?? false;
            if ($val === true) {
                $badge = '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:15px;font-weight:600;background:rgba(34,197,94,0.1);color:#16a34a"><svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'2.5\' stroke=\'currentColor\' style=\'width:12px;height:12px\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4.5 12.75l6 6 9-13.5\' /></svg>Oui</span>';
            } elseif ($val === false) {
                $badge = '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:15px;font-weight:600;background:rgba(239,68,68,0.07);color:#dc2626"><svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'2.5\' stroke=\'currentColor\' style=\'width:12px;height:12px\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M6 18L18 6M6 6l12 12\' /></svg>Non</span>';
            } else {
                $badge = '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:15px;font-weight:600;background:rgba(245,158,11,0.1);color:#d97706">Optionnel</span>';
            }
        ?>
        <tr style="border-bottom:1px solid rgba(228,233,240,0.6)">
            <td style="padding:12px 16px;font-size:16px;color:var(--text);font-weight:500"><?= $libelle ?></td>
            <td style="padding:12px 16px;text-align:center"><?= $badge ?></td>
            <td style="padding:12px 16px;font-size:15px;color:var(--text-muted)"><?= $detail ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="border-bottom:1px solid rgba(228,233,240,0.6);background:rgba(240,243,248,0.4)">
            <td style="padding:12px 16px;font-size:16px;color:var(--text);font-weight:500">Retenues salariales (IPRES, TRIMF, IR, IPM)</td>
            <td style="padding:12px 16px;text-align:center"><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:15px;font-weight:600;background:rgba(34,197,94,0.1);color:#16a34a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>Oui</span></td>
            <td style="padding:12px 16px;font-size:15px;color:var(--text-muted)">Identiques pour tous les régimes employeurs (sauf RNS sans salariés)</td>
        </tr>
        </tbody>
    </table>
    </div>
</div>

<!-- Prochaines échéances -->
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <div style="font-size:18px;font-weight:600;color:var(--navy-dark)">Prochaines échéances fiscales <?= $exercice ?></div>
        <?php if (in_array($regime, ['CGU', 'MICRO'])): ?>
        <a href="<?= APP_URL ?>/dossier/fiscalite/cgu?id=<?= $entreprise['id'] ?>" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Calculer la déclaration
        </a>
        <?php elseif ($modules['tva']): ?>
        <a href="<?= APP_URL ?>/dossier/tva?id=<?= $entreprise['id'] ?>" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Déclaration TVA
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($echeances)): ?>
    <div class="empty-state">
        <p>Aucune échéance définie pour ce régime.</p>
    </div>
    <?php else: ?>
    <div style="display:grid;gap:10px">
    <?php
    $today = date('Y-m-d');
    foreach ($echeances as $ech):
        $isPast    = $ech['date'] < $today;
        $isUrgent  = !$isPast && $ech['date'] <= date('Y-m-d', strtotime('+30 days'));
        $borderColor = $isPast ? '#6b7280' : ($isUrgent || ($ech['urgence'] ?? '') === 'high' ? '#ef4444' : '#c9a96e');
        $bgColor     = $isPast ? 'rgba(107,114,128,0.06)' : ($isUrgent ? 'rgba(239,68,68,0.06)' : 'rgba(201,169,110,0.06)');
    ?>
    <div style="display:flex;align-items:center;gap:16px;padding:14px 18px;background:<?= $bgColor ?>;border-radius:10px;border-left:4px solid <?= $borderColor ?>">
        <div style="text-align:center;min-width:56px">
            <div style="font-size:18px;font-weight:700;font-family:'Cormorant Garamond',serif;color:<?= $isPast ? '#9ca3af' : 'var(--navy-dark)' ?>"><?= date('d', strtotime($ech['date'])) ?></div>
            <div style="font-size:13px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)"><?= moisFr(strtotime($ech['date'])) ?></div>
        </div>
        <div style="flex:1">
            <div style="font-size:16px;font-weight:500;color:<?= $isPast ? 'var(--text-muted)' : 'var(--text)' ?>"><?= e($ech['libelle']) ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:2px">
                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-weight:600;font-size:13px;background:rgba(30,58,95,0.08);color:var(--navy)"><?= e($ech['type']) ?></span>
            </div>
        </div>
        <?php if ($isPast): ?>
        <span style="font-size:14px;color:#9ca3af">Passée</span>
        <?php elseif ($isUrgent): ?>
        <span style="font-size:14px;font-weight:600;color:#ef4444">Urgent</span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
