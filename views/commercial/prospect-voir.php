<?php
$stageLabels = ['nouveau'=>'Nouveau','qualifie'=>'Qualifié','devis_envoye'=>'Devis envoyé','negociation'=>'Négociation','client'=>'Client','perdu'=>'Perdu'];
$stageColors = ['nouveau'=>'#94a3b8','qualifie'=>'#3b82f6','devis_envoye'=>'#f59e0b','negociation'=>'#8b5cf6','client'=>'#22c55e','perdu'=>'#ef4444'];
$stageBg    = ['nouveau'=>'#f1f5f9','qualifie'=>'#dbeafe','devis_envoye'=>'#fef3c7','negociation'=>'#ede9fe','client'=>'#dcfce7','perdu'=>'#fee2e2'];
$stageText  = ['nouveau'=>'#475569','qualifie'=>'#2563eb','devis_envoye'=>'#d97706','negociation'=>'#7c3aed','client'=>'#16a34a','perdu'=>'#dc2626'];
$color = $stageColors[$prospect['pipeline_stage']] ?? '#1e3a5f';
$initiales = strtoupper(substr($prospect['raison_sociale'],0,1)) . (strpos($prospect['raison_sociale'],' ') ? strtoupper(substr(strstr($prospect['raison_sociale'],' '),1,1)) : '');
function statutDevisBadge(string $s): string {
    return match($s) {
        'brouillon'=>'<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Brouillon</span>',
        'envoye'=>'<span style="background:#dbeafe;color:#2563eb;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Envoyé</span>',
        'accepte'=>'<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Accepté</span>',
        'refuse'=>'<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Refusé</span>',
        'converti'=>'<span style="background:#ede9fe;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Converti</span>',
        default=>'<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">' . e($s) . '</span>',
    };
}
function statutFacBadge(string $s): string {
    return match($s) {
        'brouillon'=>'<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Brouillon</span>',
        'envoyee'=>'<span style="background:#dbeafe;color:#2563eb;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Envoyée</span>',
        'payee'=>'<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Payée</span>',
        'en_retard'=>'<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">En retard</span>',
        'partiellement_payee'=>'<span style="background:#fef3c7;color:#d97706;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Partiel</span>',
        default=>'<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">' . e($s) . '</span>',
    };
}
?>
<style>
.voir-root { padding:32px 36px;max-width:1100px; }
.voir-hero { background:var(--navy-dark);border-radius:20px;padding:32px;margin-bottom:24px;position:relative;overflow:hidden; }
.voir-hero::before { content:'';position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(201,169,110,0.08); }
.voir-hero::after { content:'';position:absolute;bottom:-60px;right:60px;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,0.03); }
.voir-hero-top { display:flex;align-items:flex-start;gap:20px;margin-bottom:24px; }
.voir-avatar { width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:#fff;flex-shrink:0; }
.voir-name { font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:#fff;line-height:1.2; }
.voir-sub { font-size:13px;color:rgba(255,255,255,0.5);margin-top:4px; }
.voir-stage { display:inline-flex;align-items:center;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;margin-top:10px; }
.voir-hero-meta { display:flex;gap:24px;flex-wrap:wrap; }
.voir-meta-item { display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.6);font-size:13px; }
.voir-meta-item svg { width:14px;height:14px; }
.voir-meta-value { color:#fff;font-weight:500; }
.voir-actions { display:flex;gap:10px;margin-left:auto; }

.content-grid { display:grid;grid-template-columns:320px 1fr;gap:20px; }
.info-card { background:#fff;border-radius:16px;border:1px solid var(--border);padding:22px;margin-bottom:16px; }
.info-card h3 { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border); }
.info-row { display:flex;flex-direction:column;gap:3px;margin-bottom:14px; }
.info-row:last-child { margin-bottom:0; }
.info-label { font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px; }
.info-value { font-size:13px;color:var(--text);font-weight:500; }

.pipeline-steps { display:flex;gap:0;margin-bottom:20px;overflow:hidden;border-radius:10px;border:1px solid var(--border); }
.pipeline-step { flex:1;padding:10px 8px;text-align:center;font-size:11px;font-weight:600;cursor:pointer;background:#fff;color:var(--text-muted);border:none;border-right:1px solid var(--border);transition:all 0.2s; }
.pipeline-step:last-child { border-right:none; }
.pipeline-step.active { color:#fff; }
.pipeline-step:hover { opacity:0.85; }

.table-card { background:#fff;border-radius:16px;border:1px solid var(--border);overflow:hidden;margin-bottom:16px; }
.table-head { padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
.table-head h3 { font-size:14px;font-weight:700;color:var(--navy-dark); }
table.tbl { width:100%;border-collapse:collapse; }
table.tbl th { font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);font-weight:600;padding:10px 16px;background:#fafbfc;border-bottom:1px solid var(--border);text-align:left; }
table.tbl td { padding:12px 16px;font-size:13px;border-bottom:1px solid #f3f4f6; }
table.tbl tr:last-child td { border-bottom:none; }

.btn { display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all 0.2s; }
.btn-primary { background:var(--navy);color:#fff; }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-outline { background:transparent;color:rgba(255,255,255,0.7);border:1.5px solid rgba(255,255,255,0.2); }
.btn-outline:hover { background:rgba(255,255,255,0.1);color:#fff; }
.btn-sm { padding:5px 12px;font-size:12px; }
.btn-light { background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.15); }
.btn-light:hover { background:rgba(255,255,255,0.2); }
</style>

<div class="voir-root">
    <!-- Hero -->
    <div class="voir-hero">
        <div class="voir-hero-top">
            <div class="voir-avatar" style="background:<?= $color ?>"><?= e($initiales) ?></div>
            <div style="flex:1">
                <div class="voir-name"><?= e($prospect['raison_sociale']) ?></div>
                <div class="voir-sub"><?= e($prospect['forme_juridique']) ?> · <?= e($prospect['secteur'] ?: 'Secteur non défini') ?> · <?= e($prospect['ville']) ?></div>
                <div class="voir-stage" style="background:<?= $stageBg[$prospect['pipeline_stage']] ?? '#f1f5f9' ?>;color:<?= $stageText[$prospect['pipeline_stage']] ?? '#475569' ?>">
                    <?= $stageLabels[$prospect['pipeline_stage']] ?? $prospect['pipeline_stage'] ?>
                </div>
            </div>
            <div class="voir-actions">
                <a href="<?= APP_URL ?>/commercial/prospect/edit?id=<?= $prospect['id'] ?>" class="btn btn-light">✏️ Modifier</a>
                <a href="<?= APP_URL ?>/commercial/devis/nouveau?prospect_id=<?= $prospect['id'] ?>" class="btn btn-gold">+ Nouveau devis</a>
            </div>
        </div>
        <div class="voir-hero-meta">
            <?php if ($prospect['telephone']): ?>
            <div class="voir-meta-item">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6 19.79 19.79 0 0 1 1.58 5a2 2 0 0 1 1.98-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 6 6z"/></svg>
                <span class="voir-meta-value"><?= e($prospect['telephone']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($prospect['email']): ?>
            <div class="voir-meta-item">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <span class="voir-meta-value"><?= e($prospect['email']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($prospect['ca_potentiel'] > 0): ?>
            <div class="voir-meta-item">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                CA potentiel : <span class="voir-meta-value"><?= number_format($prospect['ca_potentiel'],0,',',' ') ?> FCFA</span>
            </div>
            <?php endif; ?>
            <div class="voir-meta-item">Réf : <span class="voir-meta-value"><?= e($prospect['reference']) ?></span></div>
        </div>
    </div>

    <div class="content-grid">
        <!-- Colonne gauche -->
        <div>
            <!-- Pipeline -->
            <div class="info-card">
                <h3>Avancer dans le pipeline</h3>
                <div class="pipeline-steps">
                    <?php
                    $stages = ['nouveau'=>['Nouveau','#94a3b8'],'qualifie'=>['Qualifié','#3b82f6'],'devis_envoye'=>['Devis','#f59e0b'],'negociation'=>['Négo.','#8b5cf6'],'client'=>['Client','#22c55e'],'perdu'=>['Perdu','#ef4444']];
                    foreach ($stages as $k => [$label, $col]):
                        $isActive = $prospect['pipeline_stage'] === $k;
                    ?>
                    <button class="pipeline-step <?= $isActive ? 'active' : '' ?>"
                            style="<?= $isActive ? "background:$col" : '' ?>"
                            onclick="changerStage('<?= $k ?>')"
                            title="<?= $label ?>">
                        <?= $label ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Infos société -->
            <div class="info-card">
                <h3>Société</h3>
                <?php if ($prospect['ninea']): ?>
                <div class="info-row"><div class="info-label">NINEA</div><div class="info-value"><?= e($prospect['ninea']) ?></div></div>
                <?php endif; ?>
                <div class="info-row"><div class="info-label">Ville</div><div class="info-value"><?= e($prospect['ville']) ?></div></div>
                <?php if ($prospect['adresse']): ?>
                <div class="info-row"><div class="info-label">Adresse</div><div class="info-value"><?= e($prospect['adresse']) ?></div></div>
                <?php endif; ?>
                <?php if ($prospect['site_web']): ?>
                <div class="info-row"><div class="info-label">Site web</div><div class="info-value"><a href="<?= e($prospect['site_web']) ?>" target="_blank" style="color:var(--navy)"><?= e($prospect['site_web']) ?></a></div></div>
                <?php endif; ?>
                <div class="info-row"><div class="info-label">Source</div><div class="info-value"><?= e($prospect['source']) ?></div></div>
            </div>

            <!-- Contact -->
            <?php if ($prospect['contact_nom']): ?>
            <div class="info-card">
                <h3>Contact principal</h3>
                <div class="info-row"><div class="info-label">Nom</div><div class="info-value"><?= e($prospect['contact_prenom'] . ' ' . $prospect['contact_nom']) ?></div></div>
                <?php if ($prospect['contact_poste']): ?>
                <div class="info-row"><div class="info-label">Fonction</div><div class="info-value"><?= e($prospect['contact_poste']) ?></div></div>
                <?php endif; ?>
                <?php if ($prospect['contact_telephone']): ?>
                <div class="info-row"><div class="info-label">Tél</div><div class="info-value"><?= e($prospect['contact_telephone']) ?></div></div>
                <?php endif; ?>
                <?php if ($prospect['contact_email']): ?>
                <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?= e($prospect['contact_email']) ?></div></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($prospect['notes']): ?>
            <div class="info-card">
                <h3>Notes</h3>
                <p style="font-size:13px;color:var(--text);line-height:1.6"><?= nl2br(e($prospect['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Colonne droite -->
        <div>
            <!-- Devis -->
            <div class="table-card">
                <div class="table-head">
                    <h3>Devis (<?= count($devis) ?>)</h3>
                    <a href="<?= APP_URL ?>/commercial/devis/nouveau?prospect_id=<?= $prospect['id'] ?>" class="btn btn-primary btn-sm">+ Nouveau devis</a>
                </div>
                <?php if (empty($devis)): ?>
                <div style="padding:28px;text-align:center;color:var(--text-muted);font-size:13px">Aucun devis</div>
                <?php else: ?>
                <table class="tbl">
                    <thead><tr><th>N°</th><th>Date</th><th>Objet</th><th>Montant TTC</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php foreach ($devis as $d): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/commercial/devis/voir?id=<?= $d['id'] ?>" style="color:var(--navy);font-weight:600"><?= e($d['numero']) ?></a></td>
                        <td><?= date('d/m/Y', strtotime($d['date_devis'])) ?></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($d['objet']) ?></td>
                        <td style="font-weight:600"><?= number_format($d['montant_ttc'],0,',',' ') ?></td>
                        <td><?= statutDevisBadge($d['statut']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Factures -->
            <div class="table-card">
                <div class="table-head">
                    <h3>Factures (<?= count($factures) ?>)</h3>
                    <a href="<?= APP_URL ?>/commercial/factures/nouvelle?prospect_id=<?= $prospect['id'] ?>" class="btn btn-primary btn-sm">+ Nouvelle facture</a>
                </div>
                <?php if (empty($factures)): ?>
                <div style="padding:28px;text-align:center;color:var(--text-muted);font-size:13px">Aucune facture</div>
                <?php else: ?>
                <table class="tbl">
                    <thead><tr><th>N°</th><th>Date</th><th>TTC</th><th>Payé</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php foreach ($factures as $f): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/commercial/factures/voir?id=<?= $f['id'] ?>" style="color:var(--navy);font-weight:600"><?= e($f['numero']) ?></a></td>
                        <td><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                        <td style="font-weight:600"><?= number_format($f['montant_ttc'],0,',',' ') ?></td>
                        <td style="color:<?= $f['montant_paye'] >= $f['montant_ttc'] ? '#16a34a' : '#d97706' ?>"><?= number_format($f['montant_paye'],0,',',' ') ?></td>
                        <td><?= statutFacBadge($f['statut']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<form id="stageForm" method="POST" action="<?= APP_URL ?>/commercial/prospect/stage">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input type="hidden" name="id" value="<?= $prospect['id'] ?>">
    <input type="hidden" name="stage" id="stageInput">
</form>
<script>
function changerStage(stage) {
    document.getElementById('stageInput').value = stage;
    document.getElementById('stageForm').submit();
}
</script>
