<?php
$statusColors = ['brouillon'=>'#94a3b8','envoye'=>'#1f6e4e','accepte'=>'#1f6e4e','refuse'=>'#ef4444','expire'=>'#f59e0b','converti'=>'#8b5cf6'];
$statusBg     = ['brouillon'=>'#f1f5f9','envoye'=>'#dbeafe','accepte'=>'#dcfce7','refuse'=>'#fee2e2','expire'=>'#fef3c7','converti'=>'rgba(184,146,63,0.1)'];
$statusText   = ['brouillon'=>'#475569','envoye'=>'#2563eb','accepte'=>'#1f6e4e','refuse'=>'#dc2626','expire'=>'#d97706','converti'=>'#b8923f'];
$statusLabels = ['brouillon'=>'Brouillon','envoye'=>'Envoyé','accepte'=>'Accepté','refuse'=>'Refusé','expire'=>'Expiré','converti'=>'Converti en facture'];
$st = $devis['statut'] ?? 'brouillon';
?>
<style>
.dv-root { padding:32px 36px;max-width:1100px; }
.dv-root h1 { font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--navy-dark); }
.dv-hero { background:var(--navy-dark);border-radius:20px;padding:30px 36px;color:#fff;margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start;gap:20px; }
.dv-hero-left h2 { font-family:'Playfair Display',serif;font-size:22px;font-weight:700;margin-bottom:4px; }
.dv-hero-left .ref { font-size:13px;opacity:0.65;margin-bottom:14px; }
.dv-badge { display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700; }
.dv-hero-right { text-align:right; }
.dv-amount { font-size:36px;font-weight:800;color:var(--gold);font-family:'Playfair Display',serif; }
.dv-amount-label { font-size:11px;opacity:0.6;text-transform:uppercase;letter-spacing:1px;margin-top:2px; }
.dv-dates { display:flex;gap:24px;margin-top:12px; }
.dv-date-item { font-size:12px;opacity:0.7; }
.dv-date-item span { display:block;font-size:14px;font-weight:600;opacity:1;color:#fff; }

.dv-actions { display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap; }
.btn { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-primary { background:var(--navy);color:#fff; }
.btn-primary:hover { background:var(--navy-light); }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-gold:hover { background:var(--gold-dark);color:#fff; }
.btn-green { background:#1f6e4e;color:#fff; }
.btn-green:hover { background:#1f6e4e; }
.btn-red { background:#ef4444;color:#fff; }
.btn-red:hover { background:#dc2626; }
.btn-outline { background:transparent;color:var(--navy);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);background:#f8fafc; }
.btn-sm { padding:7px 14px;font-size:12px; }

.dv-grid { display:grid;grid-template-columns:1fr 320px;gap:20px; }
.dv-card { background:#fff;border-radius:16px;border:1px solid var(--border);padding:24px;margin-bottom:20px; }
.dv-section-title { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid var(--gold); }

.dv-prospect-info { display:flex;align-items:center;gap:14px;margin-bottom:16px; }
.dv-avatar { width:48px;height:48px;border-radius:12px;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;flex-shrink:0; }
.dv-prospect-name { font-size:16px;font-weight:700;color:var(--navy-dark); }
.dv-prospect-meta { font-size:12px;color:var(--text-muted);margin-top:2px; }
.info-row { display:flex;gap:8px;margin-bottom:8px;font-size:13px;color:var(--text-muted); }
.info-row strong { color:var(--text);min-width:130px; }

/* Lignes table */
.lines-table { width:100%;border-collapse:collapse; }
.lines-table th { padding:10px 12px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:2px solid var(--border); }
.lines-table td { padding:12px;font-size:13px;color:var(--text);border-bottom:1px solid #f1f5f9; }
.lines-table tr:last-child td { border-bottom:none; }
.lines-table .td-num { text-align:right; }

.totaux-block { margin-top:20px;border-top:2px solid var(--border);padding-top:16px; }
.totaux-row { display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px; }
.totaux-row.total-final { font-size:17px;font-weight:800;color:var(--navy-dark);margin-top:8px;padding-top:8px;border-top:2px solid var(--navy-dark); }
.totaux-row .label { color:var(--text-muted); }

/* Side info */
.side-info-row { display:flex;justify-content:space-between;margin-bottom:10px;font-size:13px; }
.side-info-row .lbl { color:var(--text-muted); }
.side-info-row .val { font-weight:600;color:var(--text); }

/* Status change form */
.status-form { margin-top:16px;padding-top:16px;border-top:1px solid var(--border); }
.status-form select { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;color:var(--text);background:#fff; }
.status-form button { width:100%;margin-top:8px; }
</style>

<div class="dv-root">
    <!-- Hero -->
    <div class="dv-hero">
        <div class="dv-hero-left">
            <h2><?= e($devis['objet'] ?: 'Devis sans objet') ?></h2>
            <div class="ref"><?= e($devis['numero']) ?> · <?= e($devis['prospect_nom'] ?? '') ?></div>
            <span class="dv-badge" style="background:<?= $statusBg[$st] ?? '#f1f5f9' ?>;color:<?= $statusText[$st] ?? '#475569' ?>">
                <?= $statusLabels[$st] ?? $st ?>
            </span>
        </div>
        <div class="dv-hero-right">
            <div class="dv-amount"><?= number_format($devis['montant_ttc'], 0, ',', ' ') ?> F</div>
            <div class="dv-amount-label">Montant TTC</div>
            <div class="dv-dates">
                <div class="dv-date-item">Émis le<span><?= date('d/m/Y', strtotime($devis['date_devis'])) ?></span></div>
                <div class="dv-date-item">Valide jusqu'au<span><?= $devis['date_validite'] ? date('d/m/Y', strtotime($devis['date_validite'])) : '—' ?></span></div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="dv-actions">
        <a href="<?= APP_URL ?>/commercial/devis/pdf?id=<?= $devis['id'] ?>" class="btn btn-outline" target="_blank">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
            Aperçu PDF
        </a>
        <?php if ($devis['statut'] !== 'converti'): ?>
        <a href="<?= APP_URL ?>/commercial/devis/edit?id=<?= $devis['id'] ?>" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
        </a>
        <?php endif; ?>
        <?php if (in_array($devis['statut'], ['accepte'])): ?>
        <form method="POST" action="<?= APP_URL ?>/commercial/devis/convertir" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $devis['id'] ?>">
            <button type="submit" class="btn btn-green" onclick="return confirm('Convertir ce devis en facture ?')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                Convertir en facture
            </button>
        </form>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/commercial/devis" class="btn btn-outline">← Retour</a>
    </div>

    <div class="dv-grid">
        <div>
            <!-- Lignes du devis -->
            <div class="dv-card">
                <div class="dv-section-title">Prestations</div>
                <table class="lines-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="td-num">Qté</th>
                            <th class="td-num">P.U. HT</th>
                            <th class="td-num">Remise</th>
                            <th class="td-num">Montant HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignes as $l): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;color:var(--navy-dark)"><?= e($l['designation']) ?></div>
                                <?php if ($l['description']): ?><div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?= e($l['description']) ?></div><?php endif; ?>
                            </td>
                            <td class="td-num"><?= number_format($l['quantite'], 2, ',', ' ') ?></td>
                            <td class="td-num"><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> F</td>
                            <td class="td-num"><?= $l['remise'] > 0 ? $l['remise'].'%' : '—' ?></td>
                            <td class="td-num" style="font-weight:600"><?= number_format($l['montant_ht'], 0, ',', ' ') ?> F</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="totaux-block">
                    <div class="totaux-row"><span class="label">Sous-total HT</span><span><?= number_format($devis['montant_ht'], 0, ',', ' ') ?> F</span></div>
                    <?php if ($devis['remise_globale'] > 0): ?>
                    <div class="totaux-row"><span class="label">Remise globale (<?= $devis['remise_globale'] ?>%)</span><span>−<?= number_format($devis['montant_ht'] * $devis['remise_globale'] / 100, 0, ',', ' ') ?> F</span></div>
                    <?php endif; ?>
                    <div class="totaux-row"><span class="label">TVA (<?= $devis['taux_tva'] ?>%)</span><span><?= number_format($devis['montant_tva'], 0, ',', ' ') ?> F</span></div>
                    <div class="totaux-row total-final"><span class="label">Total TTC</span><span style="color:var(--gold)"><?= number_format($devis['montant_ttc'], 0, ',', ' ') ?> F</span></div>
                </div>

                <?php if ($devis['conditions_paiement']): ?>
                <div style="margin-top:16px;padding:12px 16px;background:#f8fafc;border-radius:10px;font-size:12px;color:var(--text-muted)">
                    <strong style="color:var(--text)">Conditions de paiement :</strong> <?= e($devis['conditions_paiement']) ?>
                </div>
                <?php endif; ?>

                <?php if ($devis['notes_internes']): ?>
                <div style="margin-top:12px;padding:12px 16px;background:#fffbeb;border-radius:10px;font-size:12px;color:#92400e">
                    <strong>Notes :</strong> <?= e($devis['notes_internes']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Infos prospect -->
            <div class="dv-card">
                <div class="dv-section-title">Client / Prospect</div>
                <?php
                $initiales = strtoupper(substr($devis['prospect_nom'] ?? 'P', 0, 1));
                ?>
                <div class="dv-prospect-info">
                    <div class="dv-avatar"><?= $initiales ?></div>
                    <div>
                        <div class="dv-prospect-name"><?= e($devis['prospect_nom'] ?? '—') ?></div>
                        <div class="dv-prospect-meta"><?= e($devis['prospect_forme'] ?? '') ?></div>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/commercial/prospect?id=<?= $devis['prospect_id'] ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center">Voir la fiche</a>
            </div>

            <!-- Statut & actions -->
            <div class="dv-card">
                <div class="dv-section-title">Statut</div>
                <div class="side-info-row">
                    <span class="lbl">Statut actuel</span>
                    <span class="dv-badge" style="background:<?= $statusBg[$st] ?>;color:<?= $statusText[$st] ?>"><?= $statusLabels[$st] ?></span>
                </div>
                <div class="side-info-row"><span class="lbl">Créé le</span><span class="val"><?= date('d/m/Y', strtotime($devis['created_at'])) ?></span></div>
                <?php if ($devis['date_envoi']): ?>
                <div class="side-info-row"><span class="lbl">Envoyé le</span><span class="val"><?= date('d/m/Y', strtotime($devis['date_envoi'])) ?></span></div>
                <?php endif; ?>

                <div class="status-form">
                    <form method="POST" action="<?= APP_URL ?>/commercial/devis/store">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="_action" value="update_statut">
                        <input type="hidden" name="id" value="<?= $devis['id'] ?>">
                        <select name="statut">
                            <?php foreach ($statusLabels as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px">Mettre à jour</button>
                    </form>
                </div>
            </div>

            <!-- Récapitulatif montants -->
            <div class="dv-card">
                <div class="dv-section-title">Récapitulatif</div>
                <div class="side-info-row"><span class="lbl">HT</span><span class="val"><?= number_format($devis['montant_ht'], 0, ',', ' ') ?> F</span></div>
                <div class="side-info-row"><span class="lbl">TVA (<?= $devis['taux_tva'] ?>%)</span><span class="val"><?= number_format($devis['montant_tva'], 0, ',', ' ') ?> F</span></div>
                <div class="side-info-row" style="padding-top:8px;border-top:2px solid var(--navy-dark);margin-top:4px">
                    <span class="lbl" style="font-weight:700;color:var(--navy-dark)">TTC</span>
                    <span class="val" style="font-size:18px;color:var(--gold)"><?= number_format($devis['montant_ttc'], 0, ',', ' ') ?> F</span>
                </div>
            </div>
        </div>
    </div>
</div>
