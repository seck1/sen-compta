<?php
$mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$regime_label = ($tva['regime_tva'] ?? 'mensuel') === 'trimestriel' ? 'Trimestriel' : 'Mensuel';

// Date limite DGID : 15 du mois suivant
$now = new DateTime();
$limite = new DateTime(sprintf('%04d-%02d-15', $mois === 12 ? $annee+1 : $annee, $mois === 12 ? 1 : $mois+1));
$jours_restants = (int)$now->diff($limite)->format('%r%a');
$alerte_delai = $jours_restants >= 0 && $jours_restants <= 10;

function tva_row(string $label, float $montant, string $note = '', bool $bold = false, string $color = ''): void {
    $style_label = 'font-size:14px;' . ($bold ? 'font-weight:600;' : '') . ($color ? "color:$color;" : '');
    $style_val   = 'font-family:monospace;font-size:14px;white-space:nowrap;' . ($bold ? 'font-weight:600;' : '') . ($color ? "color:$color;" : '');
    echo '<div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border)">';
    echo '<span style="' . $style_label . '">' . htmlspecialchars($label);
    if ($note) echo ' <span style="font-size:14px;font-weight:400;color:var(--text-muted);margin-left:6px">' . htmlspecialchars($note) . '</span>';
    echo '</span>';
    echo '<span style="' . $style_val . '">' . formatMontant($montant) . '</span>';
    echo '</div>';
}
?>
<div style="max-width:900px">
    <div class="page-header">
        <div>
            <div class="page-title">Déclaration de TVA</div>
            <div class="page-subtitle">
                <?= e($entreprise['raison_sociale']) ?> —
                <?php if($tva): ?>
                    Période : <?= $mois_noms[$mois] ?><?= $mois_fin !== $mois ? ' à ' . $mois_noms[$mois_fin] : '' ?> <?= $annee ?> — Régime <?= $regime_label ?>
                <?php else: ?>
                    Sélectionnez une période
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(isset($_GET['saved'])): ?>
    <div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#1f6e4e;font-size:14px;display:flex;align-items:center;gap:8px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        Déclaration enregistrée avec succès.
    </div>
    <?php endif; ?>
    <?php if(isset($_GET['paid'])): ?>
    <div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#1f6e4e;font-size:14px;display:flex;align-items:center;gap:8px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
        Paiement enregistré avec succès.
    </div>
    <?php endif; ?>

    <?php if($alerte_delai && !$declaration_existante): ?>
    <div style="background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.35);border-radius:10px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ca8a04" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        <span style="font-size:14px;color:#92400e">
            <strong>Date limite DGID :</strong> <?= $limite->format('d/m/Y') ?> —
            <?= $jours_restants === 0 ? "C'est aujourd'hui !" : "encore $jours_restants jour" . ($jours_restants > 1 ? 's' : '') ?>
        </span>
    </div>
    <?php endif; ?>

    <?php if(!empty($credit_auto) && !isset($_GET['calculer'])): ?>
    <div style="background:rgba(31,110,78,0.08);border:1px solid rgba(31,110,78,0.2);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#2563eb">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;display:inline;margin-right:6px;vertical-align:-2px"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
        Crédit de TVA reportable du mois précédent : <strong><?= formatMontant($credit_auto) ?></strong> — pré-rempli dans "Crédit antérieur"
    </div>
    <?php endif; ?>

    <!-- Formulaire de période -->
    <div class="card" style="margin-bottom:20px">
        <form method="GET" action="<?= APP_URL ?>/dossier/tva" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <div class="form-field" style="min-width:150px">
                <label>Du mois</label>
                <select name="mois">
                    <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m==$mois?'selected':'' ?>><?= $mois_noms[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-field" style="min-width:150px">
                <label>Au mois</label>
                <select name="mois_fin">
                    <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m==$mois_fin?'selected':'' ?>><?= $mois_noms[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-field" style="min-width:120px">
                <label>Année</label>
                <select name="annee">
                    <?php for($y=date('Y');$y>=date('Y')-5;$y--): ?>
                    <option value="<?= $y ?>" <?= $y==$annee?'selected':'' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-field" style="min-width:180px">
                <label>Crédit antérieur (FCFA)</label>
                <input type="number" name="credit_anterieur" value="<?= $tva['credit_anterieur'] ?? $credit_auto ?? 0 ?>" step="1" min="0">
            </div>
            <button type="submit" name="calculer" value="1" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Calculer
            </button>
        </form>
    </div>

    <?php if($declaration_existante && !$tva): ?>
    <div style="background:rgba(31,110,78,0.08);border:1px solid rgba(31,110,78,0.2);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#2563eb">
        Une déclaration existe déjà pour <?= $mois_noms[$mois] ?> <?= $annee ?> —
        montant déclaré : <strong><?= formatMontant($declaration_existante['tva_a_payer']) ?></strong>
    </div>
    <?php endif; ?>

    <?php if($tva): ?>

    <!-- ===== SECTION 1 : TVA COLLECTÉE ===== -->
    <div style="background:rgba(31,110,78,0.05);border:1px solid rgba(31,110,78,0.18);border-radius:14px;padding:24px;margin-bottom:16px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:#2563eb;margin-bottom:14px">
            1 — TVA Collectée
        </div>

        <?php tva_row('TVA sur ventes (4431)', $tva['tva_ventes']) ?>
        <?php tva_row('TVA sur prestations de services (4432)', $tva['tva_services']) ?>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0 2px">
            <span style="font-size:14px;font-weight:600;color:#1d4ed8">= Sous-total TVA brute collectée</span>
            <span style="font-family:monospace;font-size:14px;font-weight:700;color:#1d4ed8"><?= formatMontant($tva['tva_collectee']) ?></span>
        </div>

        <?php if($tva['retenue_source'] > 0): ?>
        <div style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.18);border-radius:8px;padding:10px 14px;margin-top:12px;display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:14px;color:#dc2626">
                (-) Retenue à la source — 4445
                <span style="font-size:14px;font-weight:400;margin-left:6px">(30% marchés publics)</span>
            </span>
            <span style="font-family:monospace;font-size:14px;font-weight:600;color:#dc2626">- <?= formatMontant($tva['retenue_source']) ?></span>
        </div>
        <?php endif; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0 4px;border-top:2px solid rgba(31,110,78,0.2);margin-top:12px">
            <span style="font-size:14px;font-weight:700;color:#1e3a8a">= TVA collectée nette</span>
            <span style="font-family:monospace;font-size:13px;font-weight:700;color:#1e3a8a"><?= formatMontant($tva['tva_collectee_nette']) ?></span>
        </div>
    </div>

    <!-- ===== SECTION 2 : TVA DÉDUCTIBLE ===== -->
    <div style="background:rgba(31,110,78,0.05);border:1px solid rgba(31,110,78,0.2);border-radius:14px;padding:24px;margin-bottom:16px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:#1f6e4e;margin-bottom:14px">
            2 — TVA Déductible
        </div>

        <?php tva_row('TVA sur achats de biens (4441)', $tva['tva_ded_biens']) ?>
        <?php tva_row('TVA sur immobilisations (4442)', $tva['tva_ded_immo']) ?>
        <?php tva_row('TVA sur importations (4443)', $tva['tva_importation']) ?>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0 4px;border-top:2px solid rgba(31,110,78,0.25);margin-top:4px">
            <span style="font-size:14px;font-weight:700;color:#18583f">= Total TVA déductible</span>
            <span style="font-family:monospace;font-size:13px;font-weight:700;color:#18583f"><?= formatMontant($tva['tva_deductible']) ?></span>
        </div>
    </div>

    <!-- ===== SECTION 3 : RÉSULTAT ===== -->
    <div style="border:2px solid <?= $tva['tva_a_payer'] > 0 ? 'rgba(239,68,68,0.35)' : 'rgba(31,110,78,0.35)' ?>;border-radius:14px;padding:28px;margin-bottom:20px;<?= $tva['tva_a_payer'] > 0 ? 'background:rgba(239,68,68,0.04)' : 'background:rgba(31,110,78,0.04)' ?>">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--text-muted);margin-bottom:18px">
            3 — Résultat de la déclaration
        </div>

        <div style="max-width:520px">
            <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:14px">
                <span>TVA collectée nette</span>
                <span style="font-family:monospace"><?= formatMontant($tva['tva_collectee_nette']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:14px">
                <span>(-) TVA déductible</span>
                <span style="font-family:monospace">- <?= formatMontant($tva['tva_deductible']) ?></span>
            </div>
            <?php if($tva['credit_anterieur'] > 0): ?>
            <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:14px;color:var(--text-muted)">
                <span>(-) Crédit de TVA antérieur</span>
                <span style="font-family:monospace">- <?= formatMontant($tva['credit_anterieur']) ?></span>
            </div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:14px;font-weight:600;border-bottom:1px solid var(--border)">
                <span>= TVA nette</span>
                <span style="font-family:monospace"><?= formatMontant($tva['tva_nette']) ?></span>
            </div>
        </div>

        <!-- Résultat final centré -->
        <div style="text-align:center;margin-top:24px">
            <?php if($tva['tva_a_payer'] > 0): ?>
                <div style="font-size:13px;text-transform:uppercase;letter-spacing:1.5px;color:#dc2626;font-weight:700;margin-bottom:8px">TVA A PAYER</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:52px;font-weight:700;color:#dc2626;line-height:1">
                    <?= number_format($tva['tva_a_payer'], 0, ',', ' ') ?>
                </div>
                <div style="font-size:14px;color:#dc2626;margin-top:4px;letter-spacing:0.5px">FCFA</div>
            <?php else: ?>
                <div style="font-size:13px;text-transform:uppercase;letter-spacing:1.5px;color:#1f6e4e;font-weight:700;margin-bottom:8px">Credit a Reporter</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:52px;font-weight:700;color:#1f6e4e;line-height:1">
                    <?= number_format($tva['credit_reportable'], 0, ',', ' ') ?>
                </div>
                <div style="font-size:14px;color:#1f6e4e;margin-top:4px;letter-spacing:0.5px">FCFA</div>
            <?php endif; ?>
        </div>

        <?php if($tva['ca_ht'] > 0): ?>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted);text-align:center">
            CA HT de la période : <?= formatMontant($tva['ca_ht']) ?> —
            TVA théorique 18% : <?= formatMontant($tva['ca_ht'] * 0.18) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bouton enregistrement -->
    <form method="POST" action="<?= APP_URL ?>/dossier/tva/store">
        <?= csrfField() ?>
        <input type="hidden" name="entreprise_id"   value="<?= $entreprise['id'] ?>">
        <input type="hidden" name="mois"             value="<?= $mois ?>">
        <input type="hidden" name="annee"            value="<?= $annee ?>">
        <input type="hidden" name="credit_anterieur" value="<?= $tva['credit_anterieur'] ?>">
        <input type="hidden" name="tva_collectee"    value="<?= $tva['tva_collectee'] ?>">
        <input type="hidden" name="tva_deductible"   value="<?= $tva['tva_deductible'] ?>">
        <input type="hidden" name="tva_a_payer"      value="<?= $tva['tva_a_payer'] ?>">
        <input type="hidden" name="credit_reportable" value="<?= $tva['credit_reportable'] ?>">
        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Enregistrer la déclaration
        </button>
    </form>

    <?php else: ?>
    <!-- Etat vide avant calcul -->
    <div class="card" style="text-align:center;padding:56px 48px;color:var(--text-muted)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:52px;height:52px;margin:0 auto 16px;display:block;color:var(--border)"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
        <p style="font-size:14px;margin:0">Sélectionnez une période et cliquez sur <strong>Calculer</strong> pour lancer le calcul de la TVA.</p>
        <p style="font-size:13px;margin:8px 0 0;color:var(--text-muted)">Taux normal : 18% — Retenue à la source marchés publics : 30% — Comptes OHADA 4431/4432/4441/4442/4443/4445</p>
    </div>
    <?php endif; ?>

    <!-- ===== HISTORIQUE DES DÉCLARATIONS ===== -->
    <?php if(!empty($historique_tva)): ?>
    <div style="margin-top:32px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--text-muted);margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Historique des déclarations
        </div>
        <div class="card" style="padding:0;overflow:hidden">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                    <tr style="border-bottom:2px solid var(--border);background:var(--bg-secondary)">
                        <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Période</th>
                        <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">TVA collectée</th>
                        <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">TVA déductible</th>
                        <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">TVA nette</th>
                        <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">À payer / Crédit</th>
                        <th style="padding:11px 16px;text-align:center;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Statut</th>
                        <th style="padding:11px 16px;text-align:center;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Date dépôt</th>
                        <th style="padding:11px 16px;text-align:center;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($historique_tva as $decl): ?>
                <?php
                    $dHasTva  = $decl['tva_a_payer'] > 0;
                    $isCredit = $decl['credit_reportable'] > 0;
                    $statutConfig = match($decl['statut']) {
                        'paye'    => ['label'=>'Payée',    'bg'=>'rgba(31,110,78,0.12)',  'color'=>'#1f6e4e'],
                        'depose'  => ['label'=>'Déposée',  'bg'=>'rgba(31,110,78,0.12)', 'color'=>'#2563eb'],
                        default   => ['label'=>'Brouillon','bg'=>'rgba(107,114,128,0.12)','color'=>'#6b7280'],
                    };
                    $isCurrent = $decl['periode_mois'] == $mois && $decl['periode_annee'] == $annee;
                    $modalId = 'modal-pay-' . $decl['id'];
                ?>
                <tr style="border-bottom:1px solid var(--border);<?= $isCurrent ? 'background:rgba(31,110,78,0.04)' : '' ?>">
                    <td style="padding:11px 16px;font-weight:<?= $isCurrent ? '600' : '400' ?>">
                        <?= $mois_noms[$decl['periode_mois']] ?> <?= $decl['periode_annee'] ?>
                        <?php if($isCurrent): ?>
                        <span style="font-size:13px;background:rgba(31,110,78,0.15);color:#2563eb;border-radius:4px;padding:1px 6px;margin-left:6px;font-weight:600">En cours</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace"><?= formatMontant($decl['tva_collectee']) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace"><?= formatMontant($decl['tva_deductible_biens'] + $decl['tva_deductible_immo']) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace"><?= formatMontant($decl['tva_nette']) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:600;color:<?= $dHasTva ? '#dc2626' : ($isCredit ? '#1f6e4e' : 'var(--text-muted)') ?>">
                        <?php if($dHasTva): ?>- <?= formatMontant($decl['tva_a_payer']) ?>
                        <?php elseif($isCredit): ?>+ <?= formatMontant($decl['credit_reportable']) ?>
                        <?php else: ?>0 FCFA<?php endif; ?>
                    </td>
                    <td style="padding:11px 16px;text-align:center">
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $statutConfig['bg'] ?>;color:<?= $statutConfig['color'] ?>">
                            <?= $statutConfig['label'] ?>
                        </span>
                    </td>
                    <td style="padding:11px 16px;text-align:center;color:var(--text-muted);font-size:13px">
                        <?php if($decl['statut'] === 'paye' && $decl['date_paiement']): ?>
                            <?= date('d/m/Y', strtotime($decl['date_paiement'])) ?>
                            <?php if($decl['reference_paiement']): ?>
                            <div style="font-size:14px;color:var(--text-muted)"><?= e($decl['reference_paiement']) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= $decl['date_depot'] ? date('d/m/Y', strtotime($decl['date_depot'])) : '—' ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:11px 16px;text-align:right;white-space:nowrap">
                        <a href="<?= APP_URL ?>/dossier/tva?id=<?= $entreprise['id'] ?>&mois=<?= $decl['periode_mois'] ?>&annee=<?= $decl['periode_annee'] ?>&calculer=1"
                           style="font-size:13px;color:#6b7280;text-decoration:none;margin-right:10px">Voir</a>
                        <?php if($decl['statut'] === 'depose' && $dHasTva): ?>
                        <button onclick="document.getElementById('<?= $modalId ?>').style.display='flex'"
                                style="font-size:13px;font-weight:600;color:#1f6e4e;background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:6px;padding:3px 10px;cursor:pointer">
                            ✓ Marquer payée
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php if($decl['statut'] === 'depose' && $dHasTva): ?>
                <!-- Modal paiement -->
                <tr id="<?= $modalId ?>" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
                    <td colspan="8" style="padding:0;border:none">
                        <div style="background:var(--bg-card);border-radius:16px;padding:28px;width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
                            <div style="font-size:13px;font-weight:700;margin-bottom:4px">Enregistrer le paiement</div>
                            <div style="font-size:14px;color:var(--text-muted);margin-bottom:20px">
                                <?= $mois_noms[$decl['periode_mois']] ?> <?= $decl['periode_annee'] ?> —
                                <strong style="color:#dc2626"><?= formatMontant($decl['tva_a_payer']) ?></strong>
                            </div>
                            <form method="POST" action="<?= APP_URL ?>/dossier/tva/payer">
                                <?= csrfField() ?>
                                <input type="hidden" name="entreprise_id"  value="<?= $entreprise['id'] ?>">
                                <input type="hidden" name="declaration_id" value="<?= $decl['id'] ?>">
                                <input type="hidden" name="mois"           value="<?= $mois ?>">
                                <input type="hidden" name="annee"          value="<?= $annee ?>">
                                <div class="form-field" style="margin-bottom:14px">
                                    <label>Date de paiement</label>
                                    <input type="date" name="date_paiement" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-field" style="margin-bottom:20px">
                                    <label>Référence / N° quittance <span style="font-weight:400;color:var(--text-muted)">(optionnel)</span></label>
                                    <input type="text" name="reference_paiement" placeholder="ex: QIT-2026-0512">
                                </div>
                                <div style="display:flex;gap:10px;justify-content:flex-end">
                                    <button type="button" onclick="document.getElementById('<?= $modalId ?>').style.display='none'"
                                            style="padding:8px 16px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:14px;color:var(--text-muted)">
                                        Annuler
                                    </button>
                                    <button type="submit"
                                            style="padding:8px 18px;border-radius:8px;border:none;background:#1f6e4e;color:#fff;font-size:14px;font-weight:600;cursor:pointer">
                                        Confirmer le paiement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
