<style>
.tiers-header-card {
    background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 100%);
    border-radius: 16px; padding: 28px 32px;
    color: #fff; margin-bottom: 20px;
    display: flex; align-items: center; gap: 24px;
}
.tiers-avatar {
    width: 64px; height: 64px; border-radius: 16px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; font-weight: 700;
}
.tiers-avatar.fournisseur { background: rgba(245,158,11,.25); }
.tiers-avatar.client      { background: rgba(59,130,246,.25); }
.tiers-avatar.les_deux    { background: rgba(139,92,246,.25); }
.tiers-header-info { flex: 1; }
.tiers-header-nom  { font-size: 24px; font-weight: 700; margin-bottom: 6px; }
.tiers-header-meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 16px; color: rgba(255,255,255,.6); }
.tiers-header-meta span { display: flex; align-items: center; gap: 6px; }

.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }
.info-item { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; }
.info-item-label { font-size: 14px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 6px; }
.info-item-val   { font-size: 18px; font-weight: 600; color: var(--navy-dark); }

.solde-card {
    border-radius: 12px; padding: 16px 20px;
    display: flex; flex-direction: column; gap: 4px;
}
.solde-debiteur  { background: rgba(239,68,68,.07);  border: 1px solid rgba(239,68,68,.2); }
.solde-crediteur { background: rgba(34,197,94,.07);  border: 1px solid rgba(34,197,94,.2); }
.solde-zero      { background: rgba(100,116,139,.07); border: 1px solid rgba(100,116,139,.2); }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= e($tiers['nom']) ?></h1>
        <p class="page-subtitle">Fiche tiers · <?= e($entreprise['raison_sociale']) ?></p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?= APP_URL ?>/dossier/tiers/form?id=<?= $entreprise['id'] ?>&tiers_id=<?= $tiers['id'] ?>" class="btn btn-outline"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Modifier</a>
        <a href="<?= APP_URL ?>/dossier/tiers?id=<?= $entreprise['id'] ?><?= isset($_GET['retour_type']) ? '&type='.htmlspecialchars($_GET['retour_type']) : '' ?>" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Retour
        </a>
    </div>
</div>

<!-- En-tête coloré -->
<?php
$svgTruck2  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>';
$svgUsers2  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>';
$svgSwitch2 = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>';
$typeLabel = $tiers['type'] === 'fournisseur' ? $svgTruck2 . ' Fournisseur' : ($tiers['type'] === 'client' ? $svgUsers2 . ' Client' : $svgSwitch2 . ' Fournisseur & Client');
$initiales = strtoupper(substr($tiers['nom'], 0, 2));
?>
<div class="tiers-header-card">
    <div class="tiers-avatar <?= $tiers['type'] ?>"><?= $initiales ?></div>
    <div class="tiers-header-info">
        <div class="tiers-header-nom"><?= e($tiers['nom']) ?></div>
        <div class="tiers-header-meta">
            <span><?= $typeLabel ?></span>
            <?php if ($tiers['ninea']): ?><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg> NINEA : <?= e($tiers['ninea']) ?></span><?php endif; ?>
            <?php if ($tiers['telephone']): ?><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg> <?= e($tiers['telephone']) ?></span><?php endif; ?>
            <?php if ($tiers['email']): ?><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg> <?= e($tiers['email']) ?></span><?php endif; ?>
            <?php if ($tiers['adresse']): ?><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> <?= e($tiers['adresse']) ?></span><?php endif; ?>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
        <?php
        $totalD = array_sum(array_column($ecritures, 'debit'));
        $totalC = array_sum(array_column($ecritures, 'credit'));
        $solde  = $totalD - $totalC;
        $soldeClass = $solde > 0.005 ? 'solde-debiteur' : ($solde < -0.005 ? 'solde-crediteur' : 'solde-zero');
        ?>
        <div style="font-size:14px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.8px">Solde courant</div>
        <div style="font-size:28px;font-weight:700;font-family:Arial">
            <?= number_format(abs($solde), 0, ',', ' ') ?> <span style="font-size:17px">FCFA</span>
        </div>
        <div style="font-size:15px;color:rgba(255,255,255,.5)">
            <?php
            $isClient = $tiers['type'] === 'client';
            $isFourn  = $tiers['type'] === 'fournisseur';
            if ($solde > 0.005): ?>
                <span style="display:inline-flex;align-items:center;gap:4px">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                <?= $isFourn ? 'Avance versée (vous avez trop payé)' : 'Solde débiteur (il vous doit)' ?></span>
            <?php elseif ($solde < -0.005): ?>
                <span style="display:inline-flex;align-items:center;gap:4px">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                <?= $isClient ? 'Avance client (il a trop payé)' : 'Solde créditeur (vous lui devez)' ?></span>
            <?php else: ?>
                <span style="display:inline-flex;align-items:center;gap:4px"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Soldé</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="info-grid">
    <div class="info-item">
        <div class="info-item-label">Total Débit</div>
        <div class="info-item-val" style="color:var(--danger);font-family:Arial"><?= number_format($totalD, 0, ',', ' ') ?> FCFA</div>
    </div>
    <div class="info-item">
        <div class="info-item-label">Total Crédit</div>
        <div class="info-item-val" style="color:var(--success);font-family:Arial"><?= number_format($totalC, 0, ',', ' ') ?> FCFA</div>
    </div>
    <div class="info-item">
        <div class="info-item-label">Nb écritures</div>
        <div class="info-item-val" style="font-family:Arial"><?= count($ecritures) ?></div>
    </div>
    <div class="info-item">
        <div class="info-item-label">Dernière écriture</div>
        <div class="info-item-val" style="font-size:17px">
            <?= $ecritures ? date('d/m/Y', strtotime($ecritures[0]['date_ecriture'])) : '—' ?>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom:16px;padding:16px 20px">
    <form method="GET" action="<?= APP_URL ?>/dossier/tiers/voir" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
        <input type="hidden" name="tiers_id" value="<?= $tiers['id'] ?>">
        <div class="form-field" style="min-width:130px">
            <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Exercice</label>
            <select name="exercice" style="padding:7px 10px;border-radius:8px;border:1px solid var(--border);font-size:16px;background:var(--bg);color:var(--text)">
                <option value="">Tous</option>
                <?php foreach ($exercices_dispo as $ex): ?>
                <option value="<?= $ex ?>" <?= $filtre_ex == $ex ? 'selected' : '' ?>><?= $ex ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field" style="min-width:140px">
            <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Du</label>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" style="padding:7px 10px;border-radius:8px;border:1px solid var(--border);font-size:16px">
        </div>
        <div class="form-field" style="min-width:140px">
            <label style="font-size:15px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Au</label>
            <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" style="padding:7px 10px;border-radius:8px;border:1px solid var(--border);font-size:16px">
        </div>
        <button type="submit" class="btn btn-primary" style="height:36px">Filtrer</button>
        <?php if ($filtre_ex || $date_debut || $date_fin): ?>
        <a href="<?= APP_URL ?>/dossier/tiers/voir?id=<?= $entreprise['id'] ?>&tiers_id=<?= $tiers['id'] ?>" class="btn btn-outline" style="height:36px">Tout afficher</a>
        <?php endif; ?>
    </form>
</div>

<!-- Historique écritures -->
<div class="table-wrap">
    <div class="table-header">
        <span class="table-title">Historique des écritures<?= $filtre_ex ? " — Exercice $filtre_ex" : '' ?></span>
        <span style="font-size:16px;color:var(--text-muted)"><?= count($ecritures) ?> ligne<?= count($ecritures) > 1 ? 's' : '' ?></span>
    </div>
    <?php if (empty($ecritures)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        <h3>Aucune écriture trouvée</h3>
        <p>Ce tiers n'apparaît dans aucune écriture pour l'instant.</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Journal</th>
                <th>N° Pièce</th>
                <th>Libellé</th>
                <th>Compte</th>
                <th style="text-align:right">Débit</th>
                <th style="text-align:right">Crédit</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ecritures as $e): ?>
            <tr>
                <td style="font-family:Arial;white-space:nowrap"><?= date('d/m/Y', strtotime($e['date_ecriture'])) ?></td>
                <td><span class="badge badge-navy"><?= e($e['journal_code']) ?></span></td>
                <td style="font-family:Arial;font-size:16px;color:var(--text-muted)"><?= e($e['numero_piece']) ?></td>
                <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($e['libelle']) ?></td>
                <td style="font-family:Arial;font-weight:600"><?= e($e['numero_compte']) ?></td>
                <td style="text-align:right;font-family:Arial" class="<?= $e['debit'] > 0 ? 'montant-debit' : '' ?>">
                    <?= $e['debit'] > 0 ? number_format($e['debit'], 0, ',', ' ') : '—' ?>
                </td>
                <td style="text-align:right;font-family:Arial" class="<?= $e['credit'] > 0 ? 'montant-credit' : '' ?>">
                    <?= $e['credit'] > 0 ? number_format($e['credit'], 0, ',', ' ') : '—' ?>
                </td>
                <td>
                    <?php
                    $sc = ['brouillon'=>'badge-warning','validee'=>'badge-success','rejetee'=>'badge-danger'];
                    $sl = ['brouillon'=>'Brouillon','validee'=>'Validée','rejetee'=>'Rejetée'];
                    ?>
                    <span class="badge <?= $sc[$e['statut']] ?? 'badge-navy' ?>"><?= $sl[$e['statut']] ?? e($e['statut']) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:var(--bg);font-weight:700">
                <td colspan="5" style="padding:11px 18px;font-size:16px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Totaux</td>
                <td style="text-align:right;padding:11px 18px;font-family:Arial;color:var(--danger)"><?= number_format($totalD, 0, ',', ' ') ?></td>
                <td style="text-align:right;padding:11px 18px;font-family:Arial;color:var(--success)"><?= number_format($totalC, 0, ',', ' ') ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>
