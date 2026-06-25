<?php
// -----------------------------------------------------------------------
// Calculs agrégats
// -----------------------------------------------------------------------
$totAnD  = array_sum(array_column($balance, 'an_debit'));
$totAnC  = array_sum(array_column($balance, 'an_credit'));
$totMvtD = array_sum(array_column($balance, 'mouvement_debit'));
$totMvtC = array_sum(array_column($balance, 'mouvement_credit'));

$totSolD = 0;
$totSolC = 0;
foreach ($balance as $b) {
    $totalD = $b['an_debit']  + $b['mouvement_debit'];
    $totalC = $b['an_credit'] + $b['mouvement_credit'];
    $s = $totalD - $totalC;
    if ($s >= 0) $totSolD += $s;
    else         $totSolC += abs($s);
}

$totGeneralD = $totAnD + $totMvtD;
$totGeneralC = $totAnC + $totMvtC;

// Les colonnes AN sont-elles toutes à 0 ?
$hasAN = ($totAnD + $totAnC) > 0;

// URL de base pour les filtres
$baseUrl = APP_URL . '/dossier/balance?id=' . $entreprise['id'];

// Sous-totaux par classe
$classeData = [];
foreach ($balance as $b) {
    $cl = $b['classe'];
    if (!isset($classeData[$cl])) {
        $classeData[$cl] = ['an_debit'=>0,'an_credit'=>0,'mouvement_debit'=>0,'mouvement_credit'=>0,'sol_d'=>0,'sol_c'=>0];
    }
    $classeData[$cl]['an_debit']       += $b['an_debit'];
    $classeData[$cl]['an_credit']      += $b['an_credit'];
    $classeData[$cl]['mouvement_debit'] += $b['mouvement_debit'];
    $classeData[$cl]['mouvement_credit']+= $b['mouvement_credit'];
    $totalD = $b['an_debit']  + $b['mouvement_debit'];
    $totalC = $b['an_credit'] + $b['mouvement_credit'];
    $s = $totalD - $totalC;
    if ($s >= 0) $classeData[$cl]['sol_d'] += $s;
    else         $classeData[$cl]['sol_c'] += abs($s);
}

// Helper format montant
function fmt(float $v): string {
    return $v > 0 ? number_format($v, 0, ',', ' ') : '—';
}
?>

<style>
/* ===== Balance Sage 100 OHADA ===== */
.bal-filters {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.bal-filters .filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 130px;
}
.bal-filters .filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.bal-filters select,
.bal-filters input[type=text] {
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 7px;
    font-size: 16px;
    background: white;
    color: var(--text);
    outline: none;
    transition: border-color .15s;
}
.bal-filters select:focus,
.bal-filters input[type=text]:focus { border-color: var(--primary); }
.bal-filters .filter-actions { display: flex; gap: 8px; padding-bottom: 1px; }

.bal-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); }
.bal-table { width: 100%; border-collapse: collapse; font-size: 16px; }
.bal-table thead tr { background: var(--navy); color: white; }
.bal-table thead th {
    padding: 11px 14px;
    text-align: right;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: .4px;
    white-space: nowrap;
}
.bal-table thead th:first-child,
.bal-table thead th:nth-child(2) { text-align: left; }
.bal-table tbody tr:hover { background: rgba(30,58,95,.04); }
.bal-table tbody td {
    padding: 8px 14px;
    border-bottom: 1px solid var(--border);
    text-align: right;
    font-family: monospace;
    font-size: 15px;
    white-space: nowrap;
}
.bal-table tbody td:first-child { text-align: left; font-family: inherit; }
.bal-table tbody td:nth-child(2) { text-align: left; font-family: inherit; max-width: 260px; }

/* Ligne sous-total classe */
.bal-class-row td {
    background: rgba(30,58,95,.06) !important;
    font-weight: 700;
    font-size: 14px;
    color: var(--navy);
    text-transform: uppercase;
    letter-spacing: .8px;
    padding: 9px 14px !important;
    border-bottom: 2px solid rgba(30,58,95,.15) !important;
}
/* Ligne séparateur classe */
.bal-class-sep td {
    background: rgba(30,58,95,.03);
    font-weight: 700;
    color: var(--navy);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 7px 14px;
    border-top: 2px solid rgba(30,58,95,.12);
    border-bottom: 1px solid var(--border);
}
/* Totaux généraux */
.bal-total-row td {
    background: #1e3a5f;
    color: white;
    font-weight: 700;
    font-size: 16px;
    padding: 13px 14px;
    border: none;
}
.bal-total-row td:first-child,
.bal-total-row td:nth-child(2) { text-align: left; }

.montant-zero { color: var(--text-muted); font-size: 15px; }
.montant-debit  { color: #dc2626; }
.montant-credit { color: #1f6e4e; }
.montant-sold   { color: #1d4ed8; }
.code-compte {
    background: var(--bg);
    padding: 2px 7px;
    border-radius: 5px;
    font-size: 14px;
    font-family: monospace;
    font-weight: 600;
    color: var(--navy);
}

.bal-equilibre {
    margin-top: 16px;
    padding: 13px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 500;
}
.bal-equilibre.ok  { background: rgba(31,110,78,.08); border: 1px solid rgba(31,110,78,.25); color: #1f6e4e; }
.bal-equilibre.err { background: rgba(239,68,68,.08);  border: 1px solid rgba(239,68,68,.25);  color: #dc2626; }
.bal-equilibre svg { width: 20px; height: 20px; flex-shrink: 0; }

/* ===== IMPRESSION ===== */
@media print {
    .bal-filters,
    .page-header-actions,
    .btn,
    nav,
    aside,
    .sidebar { display: none !important; }
    .bal-wrap { border: none; overflow: visible; }
    .bal-table { font-size: 14px; }
    .bal-table thead tr { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bal-class-row td { background: #e8eef7 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bal-total-row td { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bal-equilibre { border: 1px solid #ccc !important; }
    body { font-size: 14px; }
    .page-title { font-size: 19px; }
}
</style>

<!-- ===== HEADER ===== -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Balance générale</h1>
        <p class="page-subtitle">
            Exercice <?= $exercice ?> · <?= count($balance) ?> compte<?= count($balance) > 1 ? 's' : '' ?> mouvementé<?= count($balance) > 1 ? 's' : '' ?>
            <?php if ($classe_filtre || $compte_filtre || $journal_filtre): ?>
            <span style="background:rgba(245,158,11,.15);color:#b45309;padding:2px 8px;border-radius:20px;font-size:14px;margin-left:6px">Filtres actifs</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-actions" style="display:flex;gap:8px">
        <a href="<?= APP_URL ?>/export/balance?id=<?= $entreprise['id'] ?>&exercice=<?= $exercice ?>&classe=<?= urlencode($classe_filtre) ?>&compte=<?= urlencode($compte_filtre) ?>&journal=<?= urlencode($journal_filtre) ?>"
           class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            Imprimer
        </button>
    </div>
</div>

<!-- ===== FILTRES ===== -->
<form method="get" action="<?= APP_URL ?>/dossier/balance" class="bal-filters">
    <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">

    <div class="filter-group">
        <label>Exercice</label>
        <select name="exercice">
            <?php foreach ($exercicesDispos as $ex): ?>
            <option value="<?= $ex ?>" <?= $ex == $exercice ? 'selected' : '' ?>><?= $ex ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label>Classe</label>
        <select name="classe">
            <option value="">Toutes les classes</option>
            <?php foreach (['1','2','3','4','5','6','7','8'] as $cl): ?>
            <option value="<?= $cl ?>" <?= $classe_filtre === $cl ? 'selected' : '' ?>>Classe <?= $cl ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label>Compte (debut)</label>
        <input type="text" name="compte" value="<?= htmlspecialchars($compte_filtre) ?>" placeholder="ex: 401" maxlength="10" style="width:110px">
    </div>

    <div class="filter-group">
        <label>Journal</label>
        <select name="journal">
            <option value="">Tous les journaux</option>
            <?php foreach ($journaux_liste as $j): ?>
            <option value="<?= htmlspecialchars($j['code']) ?>" <?= $journal_filtre === $j['code'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($j['code']) ?> — <?= htmlspecialchars($j['libelle']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
        <a href="<?= $baseUrl ?>" class="btn btn-outline btn-sm">Reinitialiser</a>
    </div>
</form>

<?php if (empty($balance)): ?>
<!-- ===== ETAT VIDE ===== -->
<div class="card">
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
        </svg>
        <h3>Balance vide</h3>
        <p>Aucun mouvement pour l'exercice <?= $exercice ?><?= $classe_filtre ? ' — classe '.$classe_filtre : '' ?><?= $journal_filtre ? ' — journal '.$journal_filtre : '' ?></p>
    </div>
</div>
<?php else: ?>

<!-- ===== TABLEAU BALANCE ===== -->
<div class="bal-wrap">
<table class="bal-table" id="balTable">
    <thead>
        <tr>
            <th style="text-align:left;min-width:90px">Compte</th>
            <th style="text-align:left;min-width:200px">Intitule</th>
            <th class="col-an">AN Debit</th>
            <th class="col-an">AN Credit</th>
            <th>Mvt Debit</th>
            <th>Mvt Credit</th>
            <th>Solde Debiteur</th>
            <th>Solde Crediteur</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $classeEnCours = null;
    foreach ($balance as $b):
        $totalD = $b['an_debit']  + $b['mouvement_debit'];
        $totalC = $b['an_credit'] + $b['mouvement_credit'];
        $s = $totalD - $totalC;
        $solD = $s >= 0 ? $s : 0;
        $solC = $s < 0  ? abs($s) : 0;

        // --- Séparateur de classe ---
        if ($b['classe'] !== $classeEnCours):
            // Sous-total classe précédente
            if ($classeEnCours !== null && isset($classeData[$classeEnCours])):
                $cd = $classeData[$classeEnCours];
    ?>
        <tr class="bal-class-row">
            <td colspan="2">Sous-total Classe <?= $classeEnCours ?></td>
            <td class="col-an"><?= fmt($cd['an_debit']) ?></td>
            <td class="col-an"><?= fmt($cd['an_credit']) ?></td>
            <td><?= fmt($cd['mouvement_debit']) ?></td>
            <td><?= fmt($cd['mouvement_credit']) ?></td>
            <td><?= fmt($cd['sol_d']) ?></td>
            <td><?= fmt($cd['sol_c']) ?></td>
        </tr>
    <?php
            endif;
            $classeEnCours = $b['classe'];
    ?>
        <tr class="bal-class-sep">
            <td colspan="8">Classe <?= $b['classe'] ?></td>
        </tr>
    <?php endif; ?>
        <tr>
            <td><span class="code-compte"><?= e($b['numero']) ?></span></td>
            <td style="font-size:15px;font-family:inherit"><?= e($b['intitule']) ?></td>
            <td class="col-an <?= $b['an_debit']  > 0 ? 'montant-debit' : 'montant-zero' ?>"><?= fmt($b['an_debit']) ?></td>
            <td class="col-an <?= $b['an_credit'] > 0 ? 'montant-credit' : 'montant-zero' ?>"><?= fmt($b['an_credit']) ?></td>
            <td class="<?= $b['mouvement_debit']  > 0 ? 'montant-debit' : 'montant-zero' ?>"><?= fmt($b['mouvement_debit']) ?></td>
            <td class="<?= $b['mouvement_credit'] > 0 ? 'montant-credit' : 'montant-zero' ?>"><?= fmt($b['mouvement_credit']) ?></td>
            <td class="<?= $solD > 0 ? 'montant-sold' : 'montant-zero' ?>"><?= fmt($solD) ?></td>
            <td class="<?= $solC > 0 ? 'montant-sold' : 'montant-zero' ?>"><?= fmt($solC) ?></td>
        </tr>
    <?php endforeach; ?>

    <?php // Sous-total de la dernière classe ?>
    <?php if ($classeEnCours !== null && isset($classeData[$classeEnCours])): $cd = $classeData[$classeEnCours]; ?>
        <tr class="bal-class-row">
            <td colspan="2">Sous-total Classe <?= $classeEnCours ?></td>
            <td class="col-an"><?= fmt($cd['an_debit']) ?></td>
            <td class="col-an"><?= fmt($cd['an_credit']) ?></td>
            <td><?= fmt($cd['mouvement_debit']) ?></td>
            <td><?= fmt($cd['mouvement_credit']) ?></td>
            <td><?= fmt($cd['sol_d']) ?></td>
            <td><?= fmt($cd['sol_c']) ?></td>
        </tr>
    <?php endif; ?>
    </tbody>

    <tfoot>
        <tr class="bal-total-row">
            <td colspan="2">TOTAUX GENERAUX</td>
            <td class="col-an" style="color:#fca5a5"><?= number_format($totAnD,  0, ',', ' ') ?></td>
            <td class="col-an" style="color:#86efac"><?= number_format($totAnC,  0, ',', ' ') ?></td>
            <td style="color:#fca5a5"><?= number_format($totMvtD, 0, ',', ' ') ?></td>
            <td style="color:#86efac"><?= number_format($totMvtC, 0, ',', ' ') ?></td>
            <td style="color:#93c5fd"><?= number_format($totSolD, 0, ',', ' ') ?></td>
            <td style="color:#86efac"><?= number_format($totSolC, 0, ',', ' ') ?></td>
        </tr>
    </tfoot>
</table>
</div>

<!-- ===== BANDEAU EQUILIBRE ===== -->
<?php
$diffD = $totAnD + $totMvtD;
$diffC = $totAnC + $totMvtC;
$equilibre = abs($diffD - $diffC) < 0.01;
?>
<div class="bal-equilibre <?= $equilibre ? 'ok' : 'err' ?>">
    <?php if ($equilibre): ?>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    Balance equilibree — Debit = Credit = <?= number_format($diffD, 0, ',', ' ') ?> FCFA
    <?php else: ?>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
    </svg>
    Balance desequilibree — Debit : <?= number_format($diffD, 0, ',', ' ') ?> FCFA · Credit : <?= number_format($diffC, 0, ',', ' ') ?> FCFA · Difference : <?= number_format(abs($diffD - $diffC), 0, ',', ' ') ?> FCFA
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- ===== JS : masquer colonnes AN si toutes à 0 ===== -->
<script>
(function () {
    var hasAN = <?= json_encode($hasAN) ?>;
    if (!hasAN) {
        document.querySelectorAll('.col-an').forEach(function (el) {
            el.style.display = 'none';
        });
    }
})();
</script>
