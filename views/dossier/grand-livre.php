<?php
$baseUrl = APP_URL . '/dossier/grand-livre?id=' . $entreprise['id'];
$queryParams = array_filter(['compte'=>$compte_filtre,'classe'=>$classe_filtre,'journal'=>$journal_filtre,'date_debut'=>$date_debut,'date_fin'=>$date_fin,'exercice'=>$exercice!=$entreprise['exercice_courant']?$exercice:'']);
$hasFiltre = !empty($compte_filtre) || !empty($classe_filtre) || !empty($journal_filtre) || !empty($date_debut) || !empty($date_fin);
?>
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Grand livre</h1>
        <p class="page-subtitle">Exercice <?= $exercice ?> · <?= $total_comptes ?> compte<?= $total_comptes>1?'s':'' ?> · Page <?= $page ?>/<?= $total_pages ?></p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="<?= APP_URL ?>/export/grand-livre?id=<?= $entreprise['id'] ?>&exercice=<?= $exercice ?><?= $compte_filtre?'&compte='.urlencode($compte_filtre):'' ?>" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export CSV
        </a>
        <button onclick="toggleFiltres()" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
            Filtres<?= $hasFiltre ? ' ●' : '' ?>
        </button>
    </div>
</div>

<!-- Panel filtres -->
<div id="panel-filtres" style="display:<?= $hasFiltre?'block':'none' ?>;margin-bottom:20px">
    <form method="GET" action="<?= APP_URL ?>/dossier/grand-livre">
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
        <div style="display:flex;gap:10px;flex-wrap:wrap;padding:18px;background:white;border:1px solid var(--border);border-radius:12px;align-items:flex-end">
            <div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:5px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Exercice</div>
                <select name="exercice" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
                    <?php foreach ($exercicesDispos as $ex): ?>
                    <option value="<?= $ex ?>" <?= $ex==$exercice?'selected':'' ?>>Exercice <?= $ex ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:5px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Compte</div>
                <input type="text" name="compte" value="<?= e($compte_filtre) ?>" placeholder="Ex: 40, 411, 60" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;width:130px;font-family:inherit">
            </div>
            <div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:5px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Classe</div>
                <select name="classe" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
                    <option value="">Toutes</option>
                    <?php foreach ([1=>'Comptes de ressources',2=>'Dettes financières',3=>'Comptes de stocks',4=>'Tiers',5=>'Trésorerie',6=>'Charges',7=>'Produits',8=>'Résultat'] as $cl=>$lbl): ?>
                    <option value="<?= $cl ?>" <?= $classe_filtre==$cl?'selected':'' ?>>Classe <?= $cl ?> — <?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:5px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Journal</div>
                <select name="journal" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
                    <option value="">Tous</option>
                    <?php foreach ($journaux_liste as $j): ?>
                    <option value="<?= e($j['code']) ?>" <?= $journal_filtre===$j['code']?'selected':'' ?>><?= e($j['code']) ?> — <?= e($j['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:5px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Du</div>
                <input type="date" name="date_debut" value="<?= e($date_debut) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
            </div>
            <div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:5px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Au</div>
                <input type="date" name="date_fin" value="<?= e($date_fin) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit">
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
                <a href="<?= APP_URL ?>/dossier/grand-livre?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Réinit.</a>
            </div>
        </div>
    </form>
</div>

<?php if (empty($comptes_gl)): ?>
<div class="card">
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
        <h3>Aucun mouvement</h3>
        <p>Aucune écriture ne correspond aux filtres sélectionnés</p>
    </div>
</div>
<?php else: ?>

<?php foreach ($comptes_gl as $num => $compte): ?>
<div class="table-wrap" style="margin-bottom:20px">
    <div class="table-header" style="background:rgba(30,58,95,0.04)">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:36px;height:36px;border-radius:9px;background:var(--ent-color);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:white">
                <?= $compte['classe'] ?>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--navy-dark)"><?= e($num) ?> — <?= e($compte['intitule']) ?></div>
                <div style="font-size:13px;color:var(--text-muted)"><?= count($compte['lignes']) ?> mouvement<?= count($compte['lignes'])>1?'s':'' ?></div>
            </div>
        </div>
        <div style="display:flex;gap:20px;font-size:14px">
            <div style="text-align:right">
                <div style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Débit</div>
                <div class="montant-debit"><?= number_format($compte['total_debit'],0,',',' ') ?></div>
            </div>
            <div style="text-align:right">
                <div style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Crédit</div>
                <div class="montant-credit"><?= number_format($compte['total_credit'],0,',',' ') ?></div>
            </div>
            <div style="text-align:right">
                <div style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Solde</div>
                <?php $solde = $compte['total_debit'] - $compte['total_credit']; ?>
                <div style="font-family:monospace;font-weight:600;color:<?= $solde>=0?'var(--danger)':'var(--success)' ?>">
                    <?= number_format(abs($solde),0,',',' ') ?> <?= $solde>=0?'D':'C' ?>
                </div>
            </div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>N° Pièce</th>
                <th>Journal</th>
                <th>Libellé</th>
                <th>Lettrage</th>
                <th style="text-align:right">Débit</th>
                <th style="text-align:right">Crédit</th>
                <th style="text-align:right">Solde cumulé</th>
            </tr>
        </thead>
        <tbody>
        <?php $cumulD = 0; $cumulC = 0; ?>
        <?php foreach ($compte['lignes'] as $l): ?>
        <?php $cumulD += $l['debit']; $cumulC += $l['credit']; $solcum = $cumulD - $cumulC; ?>
        <tr>
            <td style="font-size:13px;white-space:nowrap"><?= date('d/m/Y', strtotime($l['date_ecriture'])) ?></td>
            <td style="font-size:14px;font-family:monospace;color:var(--text-muted)"><?= e($l['numero_piece'] ?? '') ?></td>
            <td><span class="badge badge-navy" style="font-size:13px"><?= e($l['journal_code']) ?></span></td>
            <td style="font-size:14px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($l['ligne_libelle'] ?: $l['ecriture_libelle']) ?></td>
            <td style="font-size:14px;font-family:monospace;color:var(--success)"><?= e($l['code_lettrage'] ?? '') ?></td>
            <td class="montant-debit" style="text-align:right"><?= $l['debit']>0 ? number_format($l['debit'],0,',',' ') : '—' ?></td>
            <td class="montant-credit" style="text-align:right"><?= $l['credit']>0 ? number_format($l['credit'],0,',',' ') : '—' ?></td>
            <td style="text-align:right;font-family:monospace;font-size:13px;color:<?= $solcum>=0?'var(--danger)':'var(--success)' ?>">
                <?= number_format(abs($solcum),0,',',' ') ?> <?= $solcum>=0?'D':'C' ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
    <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($queryParams, ['page'=>$page-1])) ?>" class="btn btn-outline btn-sm">← Précédent</a>
    <?php endif; ?>

    <?php for ($p = max(1,$page-2); $p <= min($total_pages,$page+2); $p++): ?>
    <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($queryParams, ['page'=>$p])) ?>"
       class="btn btn-sm <?= $p==$page?'btn-primary':'btn-outline' ?>"><?= $p ?></a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
    <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($queryParams, ['page'=>$page+1])) ?>" class="btn btn-outline btn-sm">Suivant →</a>
    <?php endif; ?>

    <span style="font-size:13px;color:var(--text-muted);margin-left:8px"><?= $total_comptes ?> comptes · Page <?= $page ?>/<?= $total_pages ?></span>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
function toggleFiltres() {
    const p = document.getElementById('panel-filtres');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
}
</script>
