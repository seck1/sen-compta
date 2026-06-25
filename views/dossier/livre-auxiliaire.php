<?php
$type_label = $type === 'client' ? 'Clients' : 'Fournisseurs';
$solde_cumule = $solde_ouverture;
?>
<div style="max-width:1100px">
    <div class="page-header">
        <div>
            <div class="page-title">Livre Auxiliaire <?= $type_label ?></div>
            <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Mouvements par tiers</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="<?= APP_URL ?>/dossier/livre-auxiliaire?id=<?= $entreprise['id'] ?>&type=client<?= $tiers_id ? '&tiers_id='.$tiers_id : '' ?>&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>"
               style="padding:7px 14px;border-radius:8px;font-size:16px;font-weight:600;text-decoration:none;<?= $type==='client' ? 'background:var(--accent);color:#fff' : 'background:var(--bg-secondary);color:var(--text-muted);border:1px solid var(--border)' ?>">
                Clients
            </a>
            <a href="<?= APP_URL ?>/dossier/livre-auxiliaire?id=<?= $entreprise['id'] ?>&type=fournisseur<?= $tiers_id ? '&tiers_id='.$tiers_id : '' ?>&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>"
               style="padding:7px 14px;border-radius:8px;font-size:16px;font-weight:600;text-decoration:none;<?= $type==='fournisseur' ? 'background:var(--accent);color:#fff' : 'background:var(--bg-secondary);color:var(--text-muted);border:1px solid var(--border)' ?>">
                Fournisseurs
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card" style="margin-bottom:20px">
        <form method="GET" action="<?= APP_URL ?>/dossier/livre-auxiliaire" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="type" value="<?= $type ?>">
            <div class="form-field" style="min-width:200px">
                <label>Tiers</label>
                <select name="tiers_id">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($liste_tiers as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $t['id']==$tiers_id ? 'selected' : '' ?>><?= e($t['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field" style="min-width:140px">
                <label>Du</label>
                <input type="date" name="date_debut" value="<?= $date_debut ?>">
            </div>
            <div class="form-field" style="min-width:140px">
                <label>Au</label>
                <input type="date" name="date_fin" value="<?= $date_fin ?>">
            </div>
            <button type="submit" class="btn btn-primary">Afficher</button>
        </form>
    </div>

    <?php if($tiers_id && $tiers_courant): ?>

    <!-- En-tête tiers -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px 20px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center">
        <div>
            <div style="font-size:18px;font-weight:700"><?= e($tiers_courant['nom']) ?></div>
            <div style="font-size:15px;color:var(--text-muted);margin-top:2px">
                Période : <?= date('d/m/Y', strtotime($date_debut)) ?> → <?= date('d/m/Y', strtotime($date_fin)) ?>
            </div>
        </div>
        <div style="text-align:right">
            <div style="font-size:17px;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted)">Solde ouverture</div>
            <div style="font-family:monospace;font-size:18px;font-weight:700;color:<?= $solde_ouverture > 0 ? '#dc2626' : ($solde_ouverture < 0 ? '#1f6e4e' : 'var(--text-muted)') ?>">
                <?= formatMontant(abs($solde_ouverture)) ?> <?= $solde_ouverture > 0 ? 'D' : ($solde_ouverture < 0 ? 'C' : '') ?>
            </div>
        </div>
    </div>

    <?php if(empty($mouvements)): ?>
    <div class="card" style="text-align:center;padding:40px;color:var(--text-muted)">
        Aucun mouvement sur cette période pour ce tiers.
    </div>
    <?php else: ?>

    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse;font-size:16px">
            <thead>
                <tr style="border-bottom:2px solid var(--border);background:var(--bg-secondary)">
                    <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Date</th>
                    <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">N° Pièce</th>
                    <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Journal</th>
                    <th style="padding:11px 16px;text-align:left;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Libellé</th>
                    <th style="padding:11px 16px;text-align:center;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Ltr</th>
                    <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Débit</th>
                    <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Crédit</th>
                    <th style="padding:11px 16px;text-align:right;font-weight:600;font-size:15px;text-transform:uppercase;letter-spacing:0.5px;background:#f1f5f9;color:#4a554f">Solde cumulé</th>
                </tr>
                <!-- Solde ouverture -->
                <?php if($solde_ouverture != 0): ?>
                <tr style="background:rgba(31,110,78,0.04);border-bottom:1px solid var(--border)">
                    <td colspan="5" style="padding:8px 16px;font-size:15px;color:var(--text-muted);font-style:italic">Solde au <?= date('d/m/Y', strtotime($date_debut)) ?></td>
                    <td style="padding:8px 16px;text-align:right;font-family:monospace;font-size:15px"><?= $solde_ouverture > 0 ? formatMontant($solde_ouverture) : '—' ?></td>
                    <td style="padding:8px 16px;text-align:right;font-family:monospace;font-size:15px"><?= $solde_ouverture < 0 ? formatMontant(abs($solde_ouverture)) : '—' ?></td>
                    <td style="padding:8px 16px;text-align:right;font-family:monospace;font-size:15px;font-weight:600"><?= formatMontant(abs($solde_ouverture)) ?> <?= $solde_ouverture > 0 ? 'D' : 'C' ?></td>
                </tr>
                <?php endif; ?>
            </thead>
            <tbody>
            <?php
            $total_debit = 0;
            $total_credit = 0;
            foreach($mouvements as $m):
                $solde_cumule += $m['debit'] - $m['credit'];
                $total_debit  += $m['debit'];
                $total_credit += $m['credit'];
            ?>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:10px 16px;white-space:nowrap"><?= date('d/m/Y', strtotime($m['date_ecriture'])) ?></td>
                <td style="padding:10px 16px;font-size:15px;color:#2563eb"><?= e($m['numero_piece'] ?? '—') ?></td>
                <td style="padding:10px 16px">
                    <span style="font-size:17px;font-weight:600;background:var(--bg-secondary);border:1px solid var(--border);border-radius:4px;padding:2px 6px"><?= e($m['journal']) ?></span>
                </td>
                <td style="padding:10px 16px;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($m['libelle']) ?></td>
                <td style="padding:10px 16px;text-align:center;font-size:17px;font-weight:700;color:var(--text-muted)"><?= e($m['code_lettrage'] ?? '') ?></td>
                <td style="padding:10px 16px;text-align:right;font-family:monospace;color:<?= $m['debit']>0 ? '#dc2626' : 'var(--text-muted)' ?>">
                    <?= $m['debit'] > 0 ? formatMontant($m['debit']) : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;font-family:monospace;color:<?= $m['credit']>0 ? '#1f6e4e' : 'var(--text-muted)' ?>">
                    <?= $m['credit'] > 0 ? formatMontant($m['credit']) : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;font-family:monospace;font-weight:600;color:<?= $solde_cumule > 0 ? '#dc2626' : ($solde_cumule < 0 ? '#1f6e4e' : 'var(--text-muted)') ?>">
                    <?= formatMontant(abs($solde_cumule)) ?> <?= $solde_cumule > 0 ? 'D' : ($solde_cumule < 0 ? 'C' : '') ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--border);background:var(--bg-secondary)">
                    <td colspan="5" style="padding:11px 16px;font-weight:700;font-size:16px">TOTAL PÉRIODE</td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#dc2626"><?= formatMontant($total_debit) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#1f6e4e"><?= formatMontant($total_credit) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:<?= $solde_cumule > 0 ? '#dc2626' : ($solde_cumule < 0 ? '#1f6e4e' : 'var(--text-muted)') ?>">
                        <?= formatMontant(abs($solde_cumule)) ?> <?= $solde_cumule > 0 ? 'D' : ($solde_cumule < 0 ? 'C' : '') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <?php elseif($tiers_id == 0): ?>
    <div class="card" style="text-align:center;padding:48px;color:var(--text-muted)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:48px;height:48px;margin:0 auto 14px;display:block;color:var(--border)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        <p style="font-size:17px;margin:0">Sélectionnez un tiers pour afficher son livre auxiliaire.</p>
    </div>
    <?php endif; ?>
</div>
