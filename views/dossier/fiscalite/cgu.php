<?php
// $entreprise, $regime, $annee, $secteur, $caHt, $caTtc, $calcul, $liberatoire, $declaration_existante
$fmt = fn(float $n): string => number_format($n, 0, ',', ' ');
$isCGU = $regime === 'CGU';
$color = $isCGU ? '#b8923f' : '#18583f';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $isCGU ? 'Déclaration CGU' : 'Impôt Libératoire' ?></h1>
        <p class="page-subtitle">
            <?= $isCGU
                ? 'Contribution Globale Unique — exercice ' . $annee
                : 'Impôt Libératoire Micro-entreprise — ' . $annee ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <span style="display:inline-flex;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700;color:#fff;background:<?= $color ?>"><?= e($regime) ?></span>
        <a href="<?= APP_URL ?>/dossier/fiscalite/regime?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Fiche régime
        </a>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div style="background:rgba(31,110,78,0.08);border:1px solid rgba(31,110,78,0.2);border-radius:10px;padding:12px 18px;color:#1f6e4e;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:8px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Déclaration enregistrée avec succès.
</div>
<?php endif; ?>

<?php if ($isCGU): ?>
<!-- ============================================================
     CGU — Contribution Globale Unique
     ============================================================ -->

<!-- KPIs -->
<div class="kpi-grid" style="margin-bottom:24px">
    <div class="kpi-card">
        <div class="kpi-label">CA TTC estimé</div>
        <div class="kpi-value" style="font-size:22px"><?= $fmt($caTtc) ?></div>
        <div class="kpi-sub">FCFA (comptes 70x × 1.18)</div>
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">CA HT</div>
        <div class="kpi-value" style="font-size:22px"><?= $fmt($calcul['ca_ht']) ?></div>
        <div class="kpi-sub">FCFA (÷ 1.18)</div>
    </div>
    <div class="kpi-card" style="border-color:<?= $color ?>40">
        <div class="kpi-label">CGU due</div>
        <div class="kpi-value" style="font-size:22px;color:<?= $color ?>"><?= $fmt($calcul['cgu_due']) ?></div>
        <div class="kpi-sub">FCFA — max(base, minimum)</div>
        <div class="kpi-icon" style="background:<?= $color ?>15;color:<?= $color ?>"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" /></svg></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Acompte trimestriel</div>
        <div class="kpi-value" style="font-size:22px"><?= $fmt($calcul['acomptes_trimestriels']) ?></div>
        <div class="kpi-sub">FCFA / trimestre</div>
    </div>
</div>

<!-- Détail calcul -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
    <div class="card">
        <div style="font-size:14px;font-weight:600;color:var(--navy-dark);margin-bottom:16px">Détail du calcul</div>
        <div style="display:grid;gap:12px">
            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--bg);border-radius:8px">
                <span style="font-size:14px;color:var(--text-muted)">CA HT (base de calcul)</span>
                <span style="font-size:14px;font-weight:600;color:var(--navy)"><?= $fmt($calcul['ca_ht']) ?> FCFA</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--bg);border-radius:8px">
                <span style="font-size:14px;color:var(--text-muted)">Taux CGU</span>
                <span style="font-size:14px;font-weight:600;color:var(--navy)">5%</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--bg);border-radius:8px">
                <span style="font-size:14px;color:var(--text-muted)">CGU calculée (CA HT × 5%)</span>
                <span style="font-size:14px;font-weight:600;color:var(--navy)"><?= $fmt($calcul['cgu_base']) ?> FCFA</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--bg);border-radius:8px">
                <span style="font-size:14px;color:var(--text-muted)">Minimum secteur (<?= e($secteur) ?>)</span>
                <span style="font-size:14px;font-weight:600;color:var(--navy)"><?= $fmt($calcul['minimum_secteur']) ?> FCFA</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 14px;background:<?= $color ?>10;border-radius:8px;border:1px solid <?= $color ?>30">
                <span style="font-size:14px;font-weight:600;color:var(--text)">CGU due = max(base, minimum)</span>
                <span style="font-size:14px;font-weight:700;color:<?= $color ?>"><?= $fmt($calcul['cgu_due']) ?> FCFA</span>
            </div>
        </div>
    </div>

    <!-- Calendrier échéances -->
    <div class="card">
        <div style="font-size:14px;font-weight:600;color:var(--navy-dark);margin-bottom:16px">Calendrier des versements</div>
        <div style="display:grid;gap:10px">
            <?php
            $versements = [
                ['date' => '31 mars N+1',  'label' => 'Solde annuel (N-1)',  'montant' => $calcul['solde'],   'type' => 'solde'],
                ['date' => '30 juin',      'label' => '1er acompte',         'montant' => $calcul['acompte_t1'], 'type' => 'acompte'],
                ['date' => '30 septembre', 'label' => '2ème acompte',        'montant' => $calcul['acompte_t2'], 'type' => 'acompte'],
                ['date' => '31 décembre',  'label' => '3ème acompte',        'montant' => $calcul['acompte_t3'], 'type' => 'acompte'],
            ];
            foreach ($versements as $v):
                $isS = $v['type'] === 'solde';
            ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg);border-radius:8px">
                <div style="width:8px;height:8px;border-radius:50%;background:<?= $isS ? '#ef4444' : $color ?>;flex-shrink:0"></div>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:500;color:var(--text)"><?= $v['label'] ?></div>
                    <div style="font-size:14px;color:var(--text-muted)"><?= $v['date'] ?></div>
                </div>
                <div style="font-size:14px;font-weight:700;color:<?= $isS ? '#ef4444' : 'var(--navy)' ?>"><?= $fmt($v['montant']) ?> F</div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:12px;padding:10px 14px;background:rgba(201,169,110,0.08);border-radius:8px;border-left:3px solid var(--gold)">
            <div style="font-size:13px;color:var(--text-muted);line-height:1.6">
                La CGU remplace l'IS, la TVA, la Patente, la CFCE et la taxe sur véhicules. Les retenues salariales (IPRES, TRIMF, IR, IPM) restent dues.
            </div>
        </div>
    </div>
</div>

<!-- Formulaire enregistrement -->
<div class="card">
    <div style="font-size:14px;font-weight:600;color:var(--navy-dark);margin-bottom:18px">Enregistrer la déclaration</div>
    <form method="POST" action="<?= APP_URL ?>/dossier/fiscalite/cgu/store">
        <?= csrfField() ?>
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
        <input type="hidden" name="annee" value="<?= $annee ?>">
        <input type="hidden" name="ca_ttc" value="<?= $caTtc ?>">
        <input type="hidden" name="secteur" value="<?= e($secteur) ?>">

        <div class="form-grid" style="margin-bottom:20px">
            <div class="form-field">
                <label>CA TTC saisi manuellement (FCFA)</label>
                <input type="number" name="ca_ttc_override" min="0" step="1000"
                       placeholder="Laisser vide = calculé depuis écritures"
                       value="<?= $declaration_existante ? (float)$declaration_existante['ca_ttc'] : '' ?>">
                <span style="font-size:14px;color:var(--text-muted)">CA calculé depuis les écritures : <?= $fmt($caTtc) ?> FCFA</span>
            </div>

            <div class="form-field">
                <label>Statut</label>
                <select name="statut">
                    <option value="brouillon" <?= ($declaration_existante['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                    <option value="depose" <?= ($declaration_existante['statut'] ?? '') === 'depose' ? 'selected' : '' ?>>Déposé à la DGI</option>
                    <option value="paye" <?= ($declaration_existante['statut'] ?? '') === 'paye' ? 'selected' : '' ?>>Payé</option>
                </select>
            </div>

            <div class="form-field" style="grid-column:1/-1">
                <label>Notes</label>
                <textarea name="notes" placeholder="Observations, références de paiement..."><?= e($declaration_existante['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                Enregistrer la déclaration
            </button>
            <?php if ($declaration_existante): ?>
            <span class="badge badge-<?= $declaration_existante['statut'] === 'paye' ? 'success' : ($declaration_existante['statut'] === 'depose' ? 'info' : 'warning') ?>" style="align-self:center">
                <?= ucfirst($declaration_existante['statut']) ?>
            </span>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ============================================================
     MICRO — Impôt Libératoire
     ============================================================ -->

<div class="card" style="margin-bottom:24px;border-left:5px solid <?= $color ?>">
    <div style="font-size:14px;color:var(--text-muted);margin-bottom:8px">Régime Micro-entreprise</div>
    <div style="font-size:13px;font-weight:600;color:var(--navy-dark);margin-bottom:12px">Montants forfaitaires trimestriels — <?= $annee ?></div>
    <div style="font-size:14px;color:var(--text-muted);line-height:1.7">
        L'impôt libératoire est un montant fixe payé chaque trimestre selon le secteur d'activité.
        Il remplace tous les autres impôts (IS, TVA, Patente). Aucune déclaration TVA ni IS n'est requise.
    </div>
</div>

<div class="kpi-grid" style="margin-bottom:24px">
    <?php
    $sectorMontants = [
        'commerce' => 12500, 'services' => 12500, 'artisanat' => 6250, 'transport' => 12500
    ];
    $montant = $sectorMontants[$secteur] ?? 12500;
    ?>
    <div class="kpi-card">
        <div class="kpi-label">Montant / trimestre</div>
        <div class="kpi-value"><?= $fmt($montant) ?></div>
        <div class="kpi-sub">FCFA — secteur : <?= e($secteur) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Montant annuel</div>
        <div class="kpi-value"><?= $fmt($montant * 4) ?></div>
        <div class="kpi-sub">FCFA (4 trimestres)</div>
    </div>
</div>

<div class="card">
    <div style="font-size:14px;font-weight:600;color:var(--navy-dark);margin-bottom:16px">Échéances trimestrielles</div>
    <div style="display:grid;gap:10px">
    <?php foreach ($liberatoire as $t => $lib): ?>
    <div style="display:flex;align-items:center;gap:16px;padding:14px 18px;background:var(--bg);border-radius:10px;border-left:4px solid <?= $color ?>">
        <div style="width:36px;height:36px;border-radius:9px;background:<?= $color ?>15;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:<?= $color ?>;flex-shrink:0">T<?= $t ?></div>
        <div style="flex:1">
            <div style="font-size:14px;font-weight:500;color:var(--text)"><?= e($lib['echeance']['libelle']) ?></div>
            <div style="font-size:14px;color:var(--text-muted)">Échéance : <?= date('d/m/Y', strtotime($lib['echeance']['date'])) ?></div>
        </div>
        <div style="font-size:13px;font-weight:700;color:<?= $color ?>"><?= $fmt($lib['montant_trimestriel']) ?> FCFA</div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>

<!-- Année navigation -->
<div style="display:flex;justify-content:center;gap:10px;margin-top:24px">
    <a href="<?= APP_URL ?>/dossier/fiscalite/cgu?id=<?= $entreprise['id'] ?>&annee=<?= $annee - 1 ?>" class="btn btn-outline btn-sm">
        ← <?= $annee - 1 ?>
    </a>
    <span style="padding:6px 16px;font-size:14px;font-weight:600;color:var(--navy);background:rgba(30,58,95,0.07);border-radius:8px"><?= $annee ?></span>
    <a href="<?= APP_URL ?>/dossier/fiscalite/cgu?id=<?= $entreprise['id'] ?>&annee=<?= $annee + 1 ?>" class="btn btn-outline btn-sm">
        <?= $annee + 1 ?> →
    </a>
</div>
