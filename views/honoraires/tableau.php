<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Suivi des paiements</h1>
        <p class="page-subtitle">Factures en attente et relances — <?= date('Y') ?></p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/honoraires" class="btn btn-outline btn-sm">← Retour honoraires</a>
        <a href="<?= APP_URL ?>/honoraires/creer" class="btn btn-gold btn-sm">+ Nouvelle facture</a>
    </div>
</div>

<!-- KPIs paiements -->
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
    <div class="kpi-card">
        <div class="kpi-icon green"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg></div>
        <div class="kpi-label">Encaissé <?= date('Y') ?></div>
        <div class="kpi-value" style="font-size:20px"><?= number_format($stats['encaisse'],0,',',' ') ?></div>
        <div class="kpi-trend up">FCFA</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon gold"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
        <div class="kpi-label">En attente</div>
        <div class="kpi-value" style="font-size:20px"><?= number_format($stats['en_attente'],0,',',' ') ?></div>
        <div class="kpi-trend">FCFA</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(239,68,68,0.1);color:var(--danger)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg></div>
        <div class="kpi-label">En retard</div>
        <div class="kpi-value" style="font-size:20px;color:var(--danger)"><?= number_format($stats['en_retard'],0,',',' ') ?></div>
        <div class="kpi-trend down"><?= $stats['nb_retard'] ?> facture(s)</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon navy"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg></div>
        <div class="kpi-label">Taux encaissement</div>
        <?php $total = $stats['encaisse'] + $stats['en_attente']; $taux = $total > 0 ? round($stats['encaisse']/$total*100) : 0; ?>
        <div class="kpi-value" style="font-size:28px"><?= $taux ?>%</div>
        <div class="kpi-trend"><?= date('Y') ?></div>
    </div>
</div>

<!-- Table factures en attente -->
<div class="table-wrap">
    <div class="table-header">
        <div class="table-title">Factures en attente de paiement</div>
    </div>
    <?php if (empty($impayes)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3>Tout est encaissé !</h3>
        <p>Aucune facture en attente de paiement.</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>N° Facture</th>
                <th>Entreprise</th>
                <th>Date facture</th>
                <th>Échéance</th>
                <th>Retard</th>
                <th style="text-align:right">Montant TTC</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($impayes as $h):
            $retard = (int)$h['jours_retard'];
            $enRetard = $retard > 0;
        ?>
        <tr style="<?= $enRetard ? 'background:rgba(239,68,68,0.03)' : '' ?>">
            <td>
                <a href="<?= APP_URL ?>/honoraires/voir?id=<?= $h['id'] ?>" style="font-weight:600;color:var(--navy);text-decoration:none"><?= e($h['numero_facture']) ?></a>
            </td>
            <td><?= e($h['raison_sociale']) ?></td>
            <td style="font-size:13px"><?= date('d/m/Y', strtotime($h['date_facture'])) ?></td>
            <td style="font-size:13px;<?= $enRetard ? 'color:var(--danger);font-weight:500' : '' ?>"><?= date('d/m/Y', strtotime($h['date_echeance'])) ?></td>
            <td>
                <?php if ($enRetard): ?>
                <span class="badge badge-danger"><?= $retard ?> j</span>
                <?php else: ?>
                <span style="font-size:12px;color:var(--success)">Dans <?= -$retard ?> j</span>
                <?php endif; ?>
            </td>
            <td style="text-align:right;font-family:monospace;font-weight:600"><?= number_format($h['montant_ttc'],0,',',' ') ?></td>
            <td><span class="badge badge-warning">En attente</span></td>
            <td>
                <div style="display:flex;gap:6px">
                    <button onclick="marquerPaye(<?= $h['id'] ?>, '<?= e($h['numero_facture']) ?>')" class="btn btn-sm" style="background:rgba(31,110,78,0.1);color:#1f6e4e;border:1px solid rgba(31,110,78,0.3)">
                        ✓ Marquer payé
                    </button>
                    <a href="<?= APP_URL ?>/honoraires/pdf?id=<?= $h['id'] ?>" target="_blank" class="btn btn-outline btn-sm">PDF</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Modal paiement -->
<div id="modal-paiement" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:16px;padding:32px;width:420px;max-width:90vw">
        <h3 style="font-size:13px;font-weight:600;margin-bottom:20px">Enregistrer le paiement</h3>
        <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px" id="modal-facture-info"></p>
        <div class="form-field" style="margin-bottom:16px">
            <label>Date de paiement</label>
            <input type="date" id="paiement-date" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-field" style="margin-bottom:24px">
            <label>Mode de paiement</label>
            <select id="paiement-mode">
                <option value="virement">Virement bancaire</option>
                <option value="cheque">Chèque</option>
                <option value="especes">Espèces</option>
                <option value="mobile_money">Mobile Money</option>
            </select>
        </div>
        <div style="display:flex;gap:10px">
            <button onclick="confirmerPaiement()" class="btn btn-primary" style="flex:1">Confirmer</button>
            <button onclick="document.getElementById('modal-paiement').style.display='none'" class="btn btn-outline">Annuler</button>
        </div>
    </div>
</div>

<script>
let payId = null;
function marquerPaye(id, ref) {
    payId = id;
    document.getElementById('modal-facture-info').textContent = 'Facture : ' + ref;
    document.getElementById('modal-paiement').style.display = 'flex';
}
function confirmerPaiement() {
    if (!payId) return;
    const fd = new FormData();
    fd.append('id', payId);
    fd.append('date_paiement', document.getElementById('paiement-date').value);
    fd.append('mode_paiement', document.getElementById('paiement-mode').value);
    fetch('<?= APP_URL ?>/honoraires/marquer-paye', { method:'POST', body: fd })
        .then(r => r.json())
        .then(() => location.reload());
}
</script>
