<?php
$statusColors = ['brouillon'=>'#94a3b8','envoyee'=>'#1f6e4e','payee'=>'#1f6e4e','partiel'=>'#f59e0b','retard'=>'#ef4444','annulee'=>'#6b7280'];
$statusBg     = ['brouillon'=>'#f1f5f9','envoyee'=>'#dbeafe','payee'=>'#dcfce7','partiel'=>'#fef3c7','retard'=>'#fee2e2','annulee'=>'#f3f4f6'];
$statusText   = ['brouillon'=>'#475569','envoyee'=>'#2563eb','payee'=>'#1f6e4e','partiel'=>'#d97706','retard'=>'#dc2626','annulee'=>'#374151'];
$statusLabels = ['brouillon'=>'Brouillon','envoyee'=>'Envoyée','payee'=>'Payée','partiel'=>'Paiement partiel','retard'=>'En retard','annulee'=>'Annulée'];
$st = $facture['statut'] ?? 'brouillon';
$reste = $facture['montant_ttc'] - $facture['montant_paye'];
$pct = $facture['montant_ttc'] > 0 ? min(100, round($facture['montant_paye'] / $facture['montant_ttc'] * 100)) : 0;
?>
<style>
.fv-root { padding:32px 36px;max-width:1100px; }
.fv-hero { background:var(--navy-dark);border-radius:20px;padding:30px 36px;color:#fff;margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start;gap:20px; }
.fv-hero-left h2 { font-family:'Playfair Display',serif;font-size:22px;font-weight:700;margin-bottom:4px; }
.fv-hero-left .ref { font-size:13px;opacity:0.65;margin-bottom:14px; }
.fv-badge { display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700; }
.fv-hero-right { text-align:right; }
.fv-amount { font-size:36px;font-weight:800;color:var(--gold);font-family:'Playfair Display',serif; }
.fv-amount-label { font-size:11px;opacity:0.6;text-transform:uppercase;letter-spacing:1px;margin-top:2px; }
.fv-progress { background:rgba(255,255,255,0.15);border-radius:6px;height:8px;width:200px;margin:10px 0 6px auto;overflow:hidden; }
.fv-progress-bar { height:100%;border-radius:6px;background:#1f6e4e; }
.fv-progress-text { font-size:12px;opacity:0.8; }

.fv-actions { display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap; }
.btn { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-primary { background:var(--navy);color:#fff; }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-outline { background:transparent;color:var(--navy);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);background:#f8fafc; }
.btn-sm { padding:7px 14px;font-size:12px; }
.btn-green { background:#1f6e4e;color:#fff; }

.fv-grid { display:grid;grid-template-columns:1fr 320px;gap:20px; }
.fv-card { background:#fff;border-radius:16px;border:1px solid var(--border);padding:24px;margin-bottom:20px; }
.fv-section-title { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid var(--gold); }

.lines-table { width:100%;border-collapse:collapse; }
.lines-table th { padding:10px 12px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:2px solid var(--border); }
.lines-table td { padding:12px;font-size:13px;color:var(--text);border-bottom:1px solid #f1f5f9; }
.lines-table tr:last-child td { border-bottom:none; }
.td-right { text-align:right; }
.totaux-block { margin-top:20px;border-top:2px solid var(--border);padding-top:16px; }
.totaux-row { display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px; }
.totaux-row.total-final { font-size:14px;font-weight:800;margin-top:8px;padding-top:8px;border-top:2px solid var(--navy-dark); }
.totaux-row .label { color:var(--text-muted); }

.side-row { display:flex;justify-content:space-between;margin-bottom:10px;font-size:13px; }
.side-row .lbl { color:var(--text-muted); }
.side-row .val { font-weight:600;color:var(--text); }

/* Paiements */
.paiement-item { display:flex;align-items:center;gap:12px;padding:12px;background:#f8fafc;border-radius:10px;margin-bottom:8px; }
.pai-icon { width:36px;height:36px;border-radius:9px;background:#dcfce7;color:#1f6e4e;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.pai-date { font-size:12px;color:var(--text-muted); }
.pai-amount { font-size:13px;font-weight:700;color:#1f6e4e;margin-left:auto; }

/* Paiement form */
.paiement-form { background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:18px;margin-top:12px; }
.paiement-form h4 { font-size:13px;font-weight:700;color:#18583f;margin-bottom:14px; }
.pf-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px; }
.pf-grid input, .pf-grid select { padding:8px 12px;border:1.5px solid #bbf7d0;border-radius:8px;font-size:13px;color:var(--text);background:#fff; }
.pf-grid input:focus, .pf-grid select:focus { outline:none;border-color:#1f6e4e; }
</style>

<div class="fv-root">
    <!-- Hero -->
    <div class="fv-hero">
        <div class="fv-hero-left">
            <h2><?= e($facture['objet'] ?: 'Facture') ?></h2>
            <div class="ref"><?= e($facture['numero']) ?> · <?= e($facture['prospect_nom'] ?? '') ?></div>
            <span class="fv-badge" style="background:<?= $statusBg[$st] ?? '#f1f5f9' ?>;color:<?= $statusText[$st] ?? '#475569' ?>">
                <?= $statusLabels[$st] ?? $st ?>
            </span>
        </div>
        <div class="fv-hero-right">
            <div class="fv-amount"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> F</div>
            <div class="fv-amount-label">Total TTC</div>
            <div class="fv-progress">
                <div class="fv-progress-bar" style="width:<?= $pct ?>%"></div>
            </div>
            <div class="fv-progress-text"><?= $pct ?>% encaissé · Reste <?= number_format(max(0,$reste), 0, ',', ' ') ?> F</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="fv-actions">
        <a href="<?= APP_URL ?>/commercial/factures/pdf?id=<?= $facture['id'] ?>" class="btn btn-outline" target="_blank">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
            Aperçu PDF
        </a>
        <?php if (!in_array($st, ['payee','annulee'])): ?>
        <a href="<?= APP_URL ?>/commercial/factures/edit?id=<?= $facture['id'] ?>" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
        </a>
        <?php endif; ?>
        <?php if (!in_array($st, ['brouillon','annulee'])): ?>
        <a href="<?= APP_URL ?>/commercial/avoirs/creer?facture_id=<?= $facture['id'] ?>" class="btn btn-outline" style="color:#a8443f;border-color:rgba(168,68,63,.3)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 14l6-6M9 8h.01M15 14h.01M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
            Créer un avoir
        </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/commercial/factures" class="btn btn-outline">← Retour</a>
    </div>

    <div class="fv-grid">
        <div>
            <!-- Lignes -->
            <div class="fv-card">
                <div class="fv-section-title">Prestations facturées</div>
                <table class="lines-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="td-right">Qté</th>
                            <th class="td-right">P.U. HT</th>
                            <th class="td-right">Remise</th>
                            <th class="td-right">Montant HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignes as $l): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;color:var(--navy-dark)"><?= e($l['designation']) ?></div>
                                <?php if ($l['description']): ?><div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?= e($l['description']) ?></div><?php endif; ?>
                            </td>
                            <td class="td-right"><?= number_format($l['quantite'], 2, ',', ' ') ?></td>
                            <td class="td-right"><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> F</td>
                            <td class="td-right"><?= $l['remise'] > 0 ? $l['remise'].'%' : '—' ?></td>
                            <td class="td-right" style="font-weight:600"><?= number_format($l['montant_ht'], 0, ',', ' ') ?> F</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="totaux-block">
                    <div class="totaux-row"><span class="label">Sous-total HT</span><span><?= number_format($facture['montant_ht'], 0, ',', ' ') ?> F</span></div>
                    <?php if ($facture['remise_globale'] > 0): ?>
                    <div class="totaux-row"><span class="label">Remise globale (<?= $facture['remise_globale'] ?>%)</span><span>−<?= number_format($facture['montant_ht'] * $facture['remise_globale'] / 100, 0, ',', ' ') ?> F</span></div>
                    <?php endif; ?>
                    <div class="totaux-row"><span class="label">TVA (<?= $facture['taux_tva'] ?>%)</span><span><?= number_format($facture['montant_tva'], 0, ',', ' ') ?> F</span></div>
                    <div class="totaux-row total-final"><span class="label">Total TTC</span><span style="color:var(--gold)"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> F</span></div>
                </div>
            </div>

            <!-- Paiements -->
            <div class="fv-card">
                <div class="fv-section-title">Paiements reçus</div>
                <?php if (empty($paiements)): ?>
                <div style="color:var(--text-muted);font-size:13px;padding:12px 0">Aucun paiement enregistré</div>
                <?php else: ?>
                <?php foreach ($paiements as $p): ?>
                <div class="paiement-item">
                    <div class="pai-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22V12m0 0l-3-3m3 3l3-3"/><path d="M20 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600"><?= e($p['mode_paiement'] ?? 'Virement') ?></div>
                        <div class="pai-date"><?= date('d/m/Y', strtotime($p['date_paiement'])) ?><?= $p['reference_paiement'] ? ' · Réf: '.e($p['reference_paiement']) : '' ?></div>
                    </div>
                    <div class="pai-amount"><?= number_format($p['montant'], 0, ',', ' ') ?> F</div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($reste > 0.01 && !in_array($st, ['annulee'])): ?>
                <div class="paiement-form">
                    <h4>Enregistrer un paiement</h4>
                    <form method="POST" action="<?= APP_URL ?>/commercial/factures/paiement">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="facture_id" value="<?= $facture['id'] ?>">
                        <div class="pf-grid">
                            <input type="date" name="date_paiement" value="<?= date('Y-m-d') ?>" required>
                            <input type="number" name="montant" value="<?= round($reste) ?>" min="1" step="100" placeholder="Montant" required>
                            <select name="mode_paiement">
                                <option value="virement">Virement bancaire</option>
                                <option value="cheque">Chèque</option>
                                <option value="especes">Espèces</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                            <input type="text" name="reference_paiement" placeholder="Réf. bancaire (optionnel)">
                        </div>
                        <button type="submit" class="btn btn-green" style="width:100%;justify-content:center">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
                            Valider le paiement
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="fv-card">
                <div class="fv-section-title">Client</div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                    <div style="width:44px;height:44px;border-radius:11px;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700">
                        <?= strtoupper(substr($facture['prospect_nom'] ?? 'C', 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight:700;color:var(--navy-dark)"><?= e($facture['prospect_nom'] ?? '—') ?></div>
                        <div style="font-size:12px;color:var(--text-muted)"><?= e($facture['prospect_forme'] ?? '') ?></div>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/commercial/prospect?id=<?= $facture['prospect_id'] ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center">Voir la fiche</a>
            </div>

            <div class="fv-card">
                <div class="fv-section-title">Informations</div>
                <div class="side-row"><span class="lbl">Référence</span><span class="val"><?= e($facture['numero']) ?></span></div>
                <div class="side-row"><span class="lbl">Date émission</span><span class="val"><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></span></div>
                <div class="side-row"><span class="lbl">Échéance</span><span class="val"><?= $facture['date_echeance'] ? date('d/m/Y', strtotime($facture['date_echeance'])) : '—' ?></span></div>
                <div class="side-row"><span class="lbl">Conditions</span><span class="val" style="font-size:11px"><?= e($facture['conditions_paiement'] ?? '—') ?></span></div>
            </div>

            <div class="fv-card">
                <div class="fv-section-title">Recouvrement</div>
                <div class="side-row"><span class="lbl">Total TTC</span><span class="val"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> F</span></div>
                <div class="side-row"><span class="lbl">Encaissé</span><span class="val" style="color:#1f6e4e"><?= number_format($facture['montant_paye'], 0, ',', ' ') ?> F</span></div>
                <div class="side-row" style="padding-top:8px;border-top:2px solid var(--navy-dark);margin-top:4px">
                    <span class="lbl" style="font-weight:700;color:var(--navy-dark)">Restant dû</span>
                    <span class="val" style="font-size:14px;color:<?= $reste > 0 ? '#ef4444' : '#1f6e4e' ?>"><?= number_format(max(0,$reste), 0, ',', ' ') ?> F</span>
                </div>
                <div style="background:#f1f5f9;border-radius:6px;height:8px;margin-top:10px;overflow:hidden">
                    <div style="height:100%;border-radius:6px;background:#1f6e4e;width:<?= $pct ?>%"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;text-align:right"><?= $pct ?>% encaissé</div>
            </div>
        </div>
    </div>
</div>
