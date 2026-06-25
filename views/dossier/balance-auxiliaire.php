<?php
$type_label = $type === 'client' ? 'Clients' : 'Fournisseurs';
$total_debit  = array_sum(array_column($balance, 'total_debit'));
$total_credit = array_sum(array_column($balance, 'total_credit'));
$total_solde  = array_sum(array_column($balance, 'solde'));
$nb_debiteurs  = count(array_filter($balance, fn($r) => $r['solde'] > 0));
$nb_crediteurs = count(array_filter($balance, fn($r) => $r['solde'] < 0));
?>
<div style="max-width:1000px">
    <div class="page-header">
        <div>
            <div class="page-title">Balance Auxiliaire <?= $type_label ?></div>
            <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Synthèse par tiers</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="<?= APP_URL ?>/dossier/balance-auxiliaire?id=<?= $entreprise['id'] ?>&type=client&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>"
               style="padding:7px 14px;border-radius:8px;font-size:16px;font-weight:600;text-decoration:none;<?= $type==='client' ? 'background:var(--accent);color:#fff' : 'background:var(--bg-secondary);color:var(--text-muted);border:1px solid var(--border)' ?>">
                Clients
            </a>
            <a href="<?= APP_URL ?>/dossier/balance-auxiliaire?id=<?= $entreprise['id'] ?>&type=fournisseur&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>"
               style="padding:7px 14px;border-radius:8px;font-size:16px;font-weight:600;text-decoration:none;<?= $type==='fournisseur' ? 'background:var(--accent);color:#fff' : 'background:var(--bg-secondary);color:var(--text-muted);border:1px solid var(--border)' ?>">
                Fournisseurs
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card" style="margin-bottom:20px">
        <form method="GET" action="<?= APP_URL ?>/dossier/balance-auxiliaire" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <input type="hidden" name="type" value="<?= $type ?>">
            <div class="form-field" style="min-width:140px">
                <label>Du</label>
                <input type="date" name="date_debut" value="<?= $date_debut ?>">
            </div>
            <div class="form-field" style="min-width:140px">
                <label>Au</label>
                <input type="date" name="date_fin" value="<?= $date_fin ?>">
            </div>
            <button type="submit" class="btn btn-primary">Actualiser</button>
        </form>
    </div>

    <!-- KPIs -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
        <div class="card" style="padding:16px 20px">
            <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:6px">Total mouvements débit</div>
            <div style="font-family:monospace;font-size:18px;font-weight:700;color:#dc2626"><?= formatMontant($total_debit) ?></div>
        </div>
        <div class="card" style="padding:16px 20px">
            <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:6px">Total mouvements crédit</div>
            <div style="font-family:monospace;font-size:18px;font-weight:700;color:#1f6e4e"><?= formatMontant($total_credit) ?></div>
        </div>
        <div class="card" style="padding:16px 20px">
            <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:6px">
                Solde net — <?= $nb_debiteurs ?> débiteur<?= $nb_debiteurs>1?'s':'' ?> / <?= $nb_crediteurs ?> créditeur<?= $nb_crediteurs>1?'s':'' ?>
            </div>
            <div style="font-family:monospace;font-size:18px;font-weight:700;color:<?= $total_solde > 0 ? '#dc2626' : ($total_solde < 0 ? '#1f6e4e' : 'var(--text-muted)') ?>">
                <?= formatMontant(abs($total_solde)) ?> <?= $total_solde > 0 ? 'D' : ($total_solde < 0 ? 'C' : '') ?>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <?php if(empty($balance)): ?>
    <div class="card" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun tiers enregistré.</div>
    <?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse;font-size:16px">
            <thead>
                <tr style="border-bottom:2px solid var(--border);background:var(--bg-secondary)">
                    <th style="padding:11px 16px;text-align:left;font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)">Tiers</th>
                    <th style="padding:11px 16px;text-align:right;font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)">Débit</th>
                    <th style="padding:11px 16px;text-align:right;font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)">Crédit</th>
                    <th style="padding:11px 16px;text-align:right;font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)">Solde débiteur</th>
                    <th style="padding:11px 16px;text-align:right;font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)">Solde créditeur</th>
                    <th style="padding:11px 16px;text-align:center;font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)">Détail</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($balance as $row): ?>
            <tr style="border-bottom:1px solid var(--border)<?= $row['solde'] != 0 ? ';background:'.($row['solde']>0?'rgba(239,68,68,0.03)':'rgba(31,110,78,0.03)') : '' ?>">
                <td style="padding:11px 16px">
                    <div style="font-weight:600"><?= e($row['nom']) ?></div>
                    <?php if($row['telephone'] || $row['email']): ?>
                    <div style="font-size:14px;color:var(--text-muted);margin-top:2px">
                        <?= e($row['telephone'] ?? '') ?><?= $row['telephone'] && $row['email'] ? ' · ' : '' ?><?= e($row['email'] ?? '') ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace;color:#dc2626">
                    <?= $row['total_debit'] > 0 ? formatMontant($row['total_debit']) : '—' ?>
                </td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace;color:#1f6e4e">
                    <?= $row['total_credit'] > 0 ? formatMontant($row['total_credit']) : '—' ?>
                </td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:600;color:#dc2626">
                    <?= $row['solde'] > 0 ? formatMontant($row['solde']) : '—' ?>
                </td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:600;color:#1f6e4e">
                    <?= $row['solde'] < 0 ? formatMontant(abs($row['solde'])) : '—' ?>
                </td>
                <td style="padding:11px 16px;text-align:center">
                    <a href="<?= APP_URL ?>/dossier/livre-auxiliaire?id=<?= $entreprise['id'] ?>&type=<?= $type ?>&tiers_id=<?= $row['id'] ?>&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>"
                       style="font-size:15px;color:#2563eb;text-decoration:none;font-weight:600">Voir →</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--border);background:var(--bg-secondary)">
                    <td style="padding:11px 16px;font-weight:700;font-size:16px">TOTAL</td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#dc2626"><?= formatMontant($total_debit) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#1f6e4e"><?= formatMontant($total_credit) ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#dc2626"><?= $total_solde > 0 ? formatMontant($total_solde) : '—' ?></td>
                    <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#1f6e4e"><?= $total_solde < 0 ? formatMontant(abs($total_solde)) : '—' ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>
