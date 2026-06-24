<?php
// [#13] $type déjà validé en liste blanche dans le contrôleur, on sécurise côté vue aussi
$type = in_array($type ?? '', ['clients','fournisseurs'], true) ? $type : 'clients';

$error_msgs = [
    'no_selection'     => 'Veuillez sélectionner au moins 2 lignes à lettrer.',
    'multi_compte'     => 'Les lignes sélectionnées appartiennent à des comptes différents.',
    'desequilibre'     => 'Les montants ne sont pas équilibrés (débit ≠ crédit).',
    'invalid'          => 'Sélection invalide.',
    'already_lettered' => 'Une ou plusieurs lignes sont déjà lettrées.',
    'db_error'         => 'Erreur base de données — opération annulée.',
];

// Séparation lettrées / non-lettrées
$allNonLettrees  = [];
$allLettrees     = [];
// [#8] Clé composite nom||compte pour éviter fusion multi-comptes
$soldesGlobTiers = [];

foreach ($comptes as $num => $compte) {
    foreach ($compte['lignes'] as $l) {
        $l['compte_numero']  = $num;
        $l['compte_libelle'] = $compte['libelle'];
        if (empty($l['code_lettrage'])) {
            $allNonLettrees[] = $l;
        } else {
            $allLettrees[$l['code_lettrage']]['lignes'][] = $l;
            $allLettrees[$l['code_lettrage']]['compte']   = $num;
        }
        $nom = !empty($l['nom_tiers']) ? $l['nom_tiers'] : '(sans tiers)';
        $key = $nom . '||' . $num;
        if (!isset($soldesGlobTiers[$key])) {
            $soldesGlobTiers[$key] = ['nom' => $nom, 'debit' => 0, 'credit' => 0, 'compte' => $num];
        }
        // [#12] Arrondi sur cumul
        $soldesGlobTiers[$key]['debit']  = round($soldesGlobTiers[$key]['debit']  + (float)$l['debit'],  2);
        $soldesGlobTiers[$key]['credit'] = round($soldesGlobTiers[$key]['credit'] + (float)$l['credit'], 2);
    }
}
ksort($allLettrees);
ksort($soldesGlobTiers);
$cntNL = count($allNonLettrees);
$cntL  = count($allLettrees);

// [#6] Calcul suggestions IA AVANT le rendu HTML (pour que $cntIA soit disponible dans les onglets)
$suggestions = [];
if (!empty($allNonLettrees)) {
    $debits  = array_values(array_filter($allNonLettrees, fn($l) => (float)$l['debit']  > 0));
    $credits = array_values(array_filter($allNonLettrees, fn($l) => (float)$l['credit'] > 0));
    $creditsByCompte = [];
    foreach ($credits as $i => $c) {
        $creditsByCompte[$c['compte_numero']][] = $i;
    }
    $usedCredits = [];
    $usedDebits  = [];
    foreach ($debits as $di => $d) {
        if (isset($usedDebits[$di])) continue;
        $montantD = round((float)$d['debit'], 2);
        $cptD     = $d['compte_numero'];
        $tiersD   = $d['nom_tiers'] ?? '';
        $pieceD   = $d['numero_facture'] ?? '';
        $dateD    = strtotime($d['date_ecriture']);
        $candidates = $creditsByCompte[$cptD] ?? [];
        // Match exact 1:1
        $bestScore = -1; $bestIdx = null;
        foreach ($candidates as $ci) {
            if (isset($usedCredits[$ci])) continue;
            $c = $credits[$ci];
            if (abs($montantD - round((float)$c['credit'], 2)) > 0.01) continue;
            $score = 50;
            if (!empty($tiersD) && ($c['nom_tiers'] ?? '') === $tiersD) $score += 30;
            if (!empty($pieceD) && ($c['numero_facture'] ?? '') === $pieceD) $score += 15;
            $daysDiff = abs($dateD - strtotime($c['date_ecriture'])) / 86400;
            if ($daysDiff <= 7) $score += 5; elseif ($daysDiff <= 30) $score += 2;
            if ($score > $bestScore) { $bestScore = $score; $bestIdx = $ci; }
        }
        if ($bestIdx !== null) {
            $suggestions[] = ['lignes' => [$d, $credits[$bestIdx]], 'score' => $bestScore, 'type' => 'exact', 'ids' => [$d['id'], $credits[$bestIdx]['id']]];
            $usedDebits[$di] = true; $usedCredits[$bestIdx] = true;
            continue;
        }
        // [#9] Match multi-crédits amélioré : tri par montant pour éviter non-déterminisme
        $candidateCredits = [];
        foreach ($candidates as $ci) {
            if (isset($usedCredits[$ci])) continue;
            $c = $credits[$ci];
            if (empty($tiersD) || ($c['nom_tiers'] ?? '') === $tiersD) {
                $candidateCredits[$ci] = round((float)$c['credit'], 2);
            }
        }
        arsort($candidateCredits); // Trier par montant décroissant pour meilleur matching
        $combo = []; $sum = 0;
        foreach ($candidateCredits as $ci => $montantC) {
            if ($sum + $montantC > $montantD + 0.01) continue; // Skip si dépasse
            $sum = round($sum + $montantC, 2);
            $combo[] = $ci;
            if (abs($sum - $montantD) < 0.01) break;
        }
        if (!empty($combo) && abs($sum - $montantD) < 0.01) {
            $score = 40 + (!empty($tiersD) ? 25 : 0);
            $lignes = [$d]; $ids = [$d['id']];
            foreach ($combo as $ci) { $lignes[] = $credits[$ci]; $ids[] = $credits[$ci]['id']; $usedCredits[$ci] = true; }
            $suggestions[] = ['lignes' => $lignes, 'score' => $score, 'type' => 'multi', 'ids' => $ids];
            $usedDebits[$di] = true;
        }
    }
    usort($suggestions, fn($a,$b) => $b['score'] - $a['score']);
}
$cntIA = count($suggestions);
?>
<style>
.type-tab-nav { display:flex; gap:4px; margin-bottom:20px; }
.type-tab-btn { padding:9px 20px; border-radius:9px; font-size:16px; font-weight:500; text-decoration:none; color:var(--text-muted); border:2px solid transparent; transition:all 0.2s; }
.type-tab-btn.tab-clients        { border-color:#2563eb22; color:#2563eb; }
.type-tab-btn.tab-clients.active { border-color:#2563eb; color:#2563eb; background:#eff6ff; }
.type-tab-btn.tab-clients:hover:not(.active) { border-color:#2563eb66; background:#eff6ff88; }
.type-tab-btn.tab-fournisseurs        { border-color:#d9770622; color:#d97706; }
.type-tab-btn.tab-fournisseurs.active { border-color:#d97706; color:#d97706; background:#fffbeb; }
.type-tab-btn.tab-fournisseurs:hover:not(.active) { border-color:#d9770666; background:#fffbeb88; }

.inner-tab-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #e5e7eb;
    background: #f8fafc;
    border-radius: 14px 14px 0 0;
    padding: 12px 20px 0;
}
.inner-tab {
    padding: 9px 16px;
    font-size: 16px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    border: 2px solid #e5e7eb;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
    margin-bottom: -2px;
    display: flex;
    align-items: center;
    gap: 7px;
    user-select: none;
    transition: all 0.18s;
    white-space: nowrap;
    background: #fff;
    position: relative;
}
.inner-tab:hover { color: var(--navy); border-color: #cbd5e1; background: #fff; }

/* Non lettrées — rouge/orange */
.itab-nonlettrees { border-color: #fecaca; color: #dc2626; background: #fff5f5; }
.itab-nonlettrees:hover { border-color: #f87171; background: #fff; }
.itab-nonlettrees.active {
    color: #dc2626; background: #fff;
    border-color: #f87171; border-bottom-color: #fff;
    font-weight: 600;
    box-shadow: 0 -2px 0 0 #dc2626 inset;
}
.itab-nonlettrees .tab-count {
    background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;
    border-radius: 10px; padding: 1px 8px; font-size: 14px; font-weight: 700;
    transition: all 0.15s;
}
.itab-nonlettrees.active .tab-count { background: #dc2626; color: #fff; border-color: #dc2626; }

/* Lettrées — vert */
.itab-lettrees { border-color: #bbf7d0; color: #16a34a; background: #f0fdf4; }
.itab-lettrees:hover { border-color: #4ade80; background: #fff; }
.itab-lettrees.active {
    color: #16a34a; background: #fff;
    border-color: #4ade80; border-bottom-color: #fff;
    font-weight: 600;
    box-shadow: 0 -2px 0 0 #16a34a inset;
}
.itab-lettrees .tab-count {
    background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0;
    border-radius: 10px; padding: 1px 8px; font-size: 14px; font-weight: 700;
    transition: all 0.15s;
}
.itab-lettrees.active .tab-count { background: #16a34a; color: #fff; border-color: #16a34a; }

/* Suggestions IA — violet */
.itab-ia { border-color: #ddd6fe; color: #7c3aed; background: #faf5ff; }
.itab-ia:hover { border-color: #a78bfa; background: #fff; }
.itab-ia.active {
    color: #7c3aed; background: #fff;
    border-color: #a78bfa; border-bottom-color: #fff;
    font-weight: 600;
    box-shadow: 0 -2px 0 0 #7c3aed inset;
}
.itab-ia .tab-count {
    background: #ede9fe; color: #7c3aed; border: 1px solid #ddd6fe;
    border-radius: 10px; padding: 1px 8px; font-size: 14px; font-weight: 700;
    transition: all 0.15s;
}
.itab-ia.active .tab-count { background: #7c3aed; color: #fff; border-color: #7c3aed; }

.lettrage-action-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 20px; background: #f8fafc;
    border-bottom: 1px solid #e5e7eb; min-height: 46px;
}
.sel-counter { font-size: 16px; color: var(--text-muted); }
.sel-counter strong { color: var(--navy-dark); }

.lettrage-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.lettrage-table { width: 100%; border-collapse: collapse; }
.lettrage-table thead tr { background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
.lettrage-table th { padding: 10px 14px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); white-space: nowrap; }
.lettrage-table td { padding: 10px 14px; font-size: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
.lettrage-table tbody tr:hover { background: #f8fafc; }
.lettrage-table tbody tr:last-child td { border-bottom: none; }

.badge-nonlettre { display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:20px;padding:2px 10px;font-size:17px;font-weight:600; }
.badge-lettre { display:inline-flex;align-items:center;gap:4px;background:rgba(201,169,110,0.15);color:var(--gold-dark);border:1px solid rgba(201,169,110,0.3);border-radius:20px;padding:2px 10px;font-size:17px;font-weight:600;font-family:monospace; }

.lettre-group { border-top: 1px solid #e5e7eb; }
.lettre-group:first-child { border-top: none; }
.lettre-group-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 11px 20px; background: #f0fdf4; cursor: pointer; user-select: none;
}
.lettre-group-header:hover { background: #dcfce7; }

.compte-chip {
    display: inline-flex; align-items: center;
    background: rgba(30,58,95,.06); border: 1px solid rgba(30,58,95,.12);
    border-radius: 20px; padding: 2px 9px;
    font-size: 14px; color: var(--navy); font-weight: 500; font-family: monospace;
}
.tab-panel { display: none; }
.tab-panel.active { display: block; }
.ia-placeholder {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 60px 20px; color: var(--text-muted); gap: 12px;
}
</style>

<div class="page-header">
    <div>
        <div class="page-title">Lettrage</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Exercice <?= e($entreprise['exercice_courant']) ?></div>
    </div>
</div>

<?php if(isset($_GET['lettre'])): ?>
<div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#16a34a;font-size:16px">
    Lettrage <strong><?= e($_GET['lettre']) ?></strong> effectué avec succès.
</div>
<?php elseif(isset($_GET['delettre'])): ?>
<div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#92400e;font-size:16px">
    Lettrage <strong><?= e($_GET['delettre']) ?></strong> supprimé.
</div>
<?php elseif(isset($_GET['error'])): ?>
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#dc2626;font-size:16px">
    <?= e($error_msgs[$_GET['error']] ?? 'Erreur.') ?>
</div>
<?php endif; ?>

<div class="type-tab-nav">
    <a href="?id=<?= (int)$entreprise['id'] ?>&type=clients" class="type-tab-btn tab-clients <?= $type==='clients'?'active':'' ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Clients (41x)
    </a>
    <a href="?id=<?= (int)$entreprise['id'] ?>&type=fournisseurs" class="type-tab-btn tab-fournisseurs <?= $type==='fournisseurs'?'active':'' ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>Fournisseurs (40x)
    </a>
</div>

<?php if(empty($comptes)): ?>
<div class="card" style="text-align:center;padding:48px;color:var(--text-muted)">
    <p>Aucune écriture trouvée pour les comptes <?= $type==='clients' ? '41x' : '40x' ?> sur l'exercice courant.</p>
</div>
<?php else: ?>

<div class="lettrage-card">

    <div class="inner-tab-bar">
        <div class="inner-tab itab-nonlettrees active" onclick="switchTab('nonlettrees')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Non lettrées
            <span class="tab-count"><?= $cntNL ?></span>
        </div>
        <div class="inner-tab itab-lettrees" onclick="switchTab('lettrees')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            Lettrées
            <span class="tab-count"><?= $cntL ?></span>
        </div>
        <div class="inner-tab itab-ia" onclick="switchTab('ia')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2"/><path d="M9 12l2 2 4-4"/></svg>
            Suggestions IA
            <span class="tab-count"><?= $cntIA ?></span>
        </div>
    </div>

    <!-- Tab: Non lettrées -->
    <div class="tab-panel active" id="panel-nonlettrees">
        <form method="POST" action="<?= APP_URL ?>/dossier/lettrage/lettrer" id="form-lettrer">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= (int)$entreprise['id'] ?>">
            <input type="hidden" name="type" value="<?= e($type) ?>">


            <!-- Filtres -->
            <div style="padding:12px 20px;background:#f8fafc;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <!-- Recherche tiers -->
                <div style="position:relative;flex:1;min-width:200px;max-width:280px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" id="search-tiers" oninput="applyFilters()" placeholder="Rechercher un tiers…"
                        style="width:100%;padding:7px 30px 7px 32px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;background:#fff;color:var(--text);transition:border-color .15s"
                        onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='#e2e8f0'">
                    <button type="button" id="clear-search" onclick="document.getElementById('search-tiers').value='';applyFilters()" style="display:none;position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;font-size:15px;padding:0">&#215;</button>
                </div>
                <!-- Filtre compte -->
                <?php $comptesList = array_unique(array_column($allNonLettrees, 'compte_numero')); sort($comptesList); ?>
                <?php if (count($comptesList) > 1): ?>
                <select id="filter-compte" onchange="applyFilters()" style="padding:7px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;background:#fff;color:var(--text);cursor:pointer;transition:border-color .15s" onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">Tous les comptes</option>
                    <?php foreach ($comptesList as $cn): ?>
                    <option value="<?= e($cn) ?>"><?= e($cn) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <!-- Filtre journal -->
                <?php $journauxList = array_unique(array_column($allNonLettrees, 'journal_code')); sort($journauxList); ?>
                <?php if (count($journauxList) > 1): ?>
                <select id="filter-journal" onchange="applyFilters()" style="padding:7px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;background:#fff;color:var(--text);cursor:pointer;transition:border-color .15s" onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">Tous les journaux</option>
                    <?php foreach ($journauxList as $jc): ?>
                    <option value="<?= e($jc) ?>"><?= e($jc) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <div id="search-info" style="font-size:15px;color:var(--text-muted);display:none;margin-left:4px"></div>
                <button type="button" id="btn-reset-filters" onclick="resetFilters()" style="display:none;padding:6px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:15px;background:#fff;color:var(--text-muted);cursor:pointer">&#215; Réinitialiser</button>
            </div>

            <!-- Soldes par tiers EN HAUT -->
            <?php if (!empty($soldesGlobTiers)): ?>
            <div style="border-bottom:2px solid #e2e8f0;background:#f8fafc">
                <div style="padding:10px 16px 6px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);display:flex;align-items:center;gap:6px">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Soldes par tiers — toutes lignes (lettrées + non lettrées)
                </div>
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="background:#edf0f5">
                            <th style="padding:7px 16px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);text-align:left">Tiers</th>
                            <th style="padding:7px 16px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);text-align:left">Compte</th>
                            <th style="padding:7px 16px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--danger);text-align:right">Solde Débit</th>
                            <th style="padding:7px 16px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#16a34a;text-align:right">Solde Crédit</th>
                            <th style="padding:7px 16px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);text-align:right">Solde net</th>
                            <th style="padding:7px 16px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);text-align:center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soldesGlobTiers as $_key => $t):
                            $solde   = round($t['debit'] - $t['credit'], 2);
                            $isSolde = abs($solde) < 0.01;
                            $isDebit = $solde > 0.01;
                            $nomDisplay = $t['nom'];
                        ?>
                        <tr class="tiers-top-row" data-tiers-name="<?= strtolower(e($nomDisplay)) ?>" data-tiers-raw="<?= e($nomDisplay) ?>" onclick="selectByTiersRow(this)" title="Cliquer pour sélectionner toutes les lignes de ce tiers" style="border-top:1px solid #e5e7eb;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#e9eef5'" onmouseout="this.style.background=''">
                            <td style="padding:8px 16px;font-size:15px;font-weight:500;color:var(--navy-dark)">
                                <?php if ($nomDisplay !== '(sans tiers)'): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block"></span>
                                    <?= e($nomDisplay) ?>
                                </span>
                                <?php else: ?>
                                <span style="color:var(--text-muted);font-style:italic;font-size:15px">(sans tiers)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:8px 16px"><span class="compte-chip"><?= e($t['compte'] ?? '—') ?></span></td>
                            <td style="padding:8px 16px;text-align:right;font-family:monospace;font-size:16px;font-weight:600;color:var(--danger)"><?= $t['debit'] > 0 ? number_format($t['debit'],0,',',' ') : '—' ?></td>
                            <td style="padding:8px 16px;text-align:right;font-family:monospace;font-size:16px;font-weight:600;color:#16a34a"><?= $t['credit'] > 0 ? number_format($t['credit'],0,',',' ') : '—' ?></td>
                            <td style="padding:8px 16px;text-align:right;font-family:monospace;font-size:16px;font-weight:700;color:<?= $isSolde ? '#16a34a' : ($isDebit ? 'var(--danger)' : '#2563eb') ?>">
                                <?= $isSolde ? '0' : number_format(abs($solde),0,',',' ') ?>
                            </td>
                            <td style="padding:8px 16px;text-align:center">
                                <?php if ($isSolde): ?>
                                <span style="display:inline-flex;align-items:center;gap:3px;background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;border-radius:20px;padding:2px 9px;font-size:14px;font-weight:600">&#10003; Soldé</span>
                                <?php elseif ($isDebit): ?>
                                <span style="display:inline-flex;align-items:center;gap:3px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:20px;padding:2px 9px;font-size:14px;font-weight:600">&#8679; Débiteur</span>
                                <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:3px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:20px;padding:2px 9px;font-size:14px;font-weight:600">&#8681; Créditeur</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Barre d'action -->
            <div class="lettrage-action-bar">
                <div class="sel-counter">
                    <strong id="sel-count">0</strong> ligne(s) sélectionnée(s)
                    <span id="sel-balance-info" style="margin-left:12px;display:none;font-size:15px"></span>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <label style="display:flex;align-items:center;gap:6px;font-size:15px;color:var(--text-muted);cursor:pointer">
                        <input type="checkbox" id="chk-all" onchange="toggleAll(this)" style="accent-color:var(--navy)"> Tout sélectionner
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-lettrer" disabled>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        Lettrer la sélection
                    </button>
                </div>
            </div>

            <?php if (empty($allNonLettrees)): ?>
            <div style="padding:48px;text-align:center;color:var(--text-muted)">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3;display:block;margin:0 auto 12px"><path d="M20 6L9 17l-5-5"/></svg>
                Toutes les lignes sont lettrées.
            </div>
            <?php else: ?>
            <table class="lettrage-table" id="tbl-nonlettrees">
                <thead>
                    <tr>
                        <th style="width:36px"></th>
                        <th>Statut</th>
                        <th class="sortable" data-col="date" onclick="sortTable('date')" style="cursor:pointer;user-select:none">Date <span class="sort-icon" id="si-date">↕</span></th>
                        <th class="sortable" data-col="compte" onclick="sortTable('compte')" style="cursor:pointer;user-select:none">Compte <span class="sort-icon" id="si-compte">↕</span></th>
                        <th>N° Pièce</th>
                        <th>Libellé</th>
                        <th class="sortable" data-col="tiers" onclick="sortTable('tiers')" style="cursor:pointer;user-select:none">Tiers <span class="sort-icon" id="si-tiers">↕</span></th>
                        <th>Journal</th>
                        <th class="sortable" data-col="debit" onclick="sortTable('debit')" style="cursor:pointer;user-select:none;text-align:right">Débit <span class="sort-icon" id="si-debit">↕</span></th>
                        <th class="sortable" data-col="credit" onclick="sortTable('credit')" style="cursor:pointer;user-select:none;text-align:right">Crédit <span class="sort-icon" id="si-credit">↕</span></th>
                    </tr>
                </thead>
                <tbody id="tbody-nonlettrees">
                    <?php foreach($allNonLettrees as $l): ?>
                    <tr class="nl-row"
                        data-debit="<?= (float)$l['debit'] ?>"
                        data-credit="<?= (float)$l['credit'] ?>"
                        data-compte="<?= e($l['compte_numero']) ?>"
                        data-tiers="<?= strtolower(e($l['nom_tiers'] ?? '')) ?>"
                        data-journal="<?= e($l['journal_code']) ?>"
                        data-date="<?= e($l['date_ecriture']) ?>">
                        <td style="text-align:center"><input type="checkbox" name="lignes[]" value="<?= $l['id'] ?>" class="ligne-chk" onchange="updateCounter()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--navy)"></td>
                        <td><span class="badge-nonlettre">● Non lettré</span></td>
                        <td style="font-size:15px;white-space:nowrap;color:var(--text-muted)"><?= e(date('d/m/Y', strtotime($l['date_ecriture']))) ?></td>
                        <td><span class="compte-chip"><?= e($l['compte_numero']) ?></span></td>
                        <td style="font-size:15px;font-family:monospace;color:var(--text-muted)"><?= !empty($l['numero_facture']) ? e($l['numero_facture']) : '<span style="color:#cbd5e1">—</span>' ?></td>
                        <td style="font-size:16px"><?= e($l['libelle'] ?: $l['ecriture_libelle']) ?></td>
                        <td style="font-size:15px">
                            <?php if (!empty($l['nom_tiers'])): ?>
                            <span class="tiers-chip" data-tiers-raw="<?= e($l['nom_tiers']) ?>" onclick="selectByTiersRow(this)" title="Cliquer pour sélectionner toutes les lignes de ce tiers" style="display:inline-flex;align-items:center;background:rgba(30,58,95,.06);border:1px solid rgba(30,58,95,.12);border-radius:20px;padding:2px 9px;font-size:14px;color:var(--navy);font-weight:500;cursor:pointer;transition:all .15s"><?= e($l['nom_tiers']) ?></span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><span class="badge badge-navy" style="font-size:14px"><?= e($l['journal_code']) ?></span></td>
                        <td style="text-align:right;font-family:monospace;font-size:16px;color:var(--danger)"><?= $l['debit'] > 0 ? number_format($l['debit'],0,',',' ') : '—' ?></td>
                        <td style="text-align:right;font-family:monospace;font-size:16px;color:#16a34a"><?= $l['credit'] > 0 ? number_format($l['credit'],0,',',' ') : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

        </form>
    </div>

    <!-- Tab: Lettrées -->
    <div class="tab-panel" id="panel-lettrees">
        <?php if (empty($allLettrees)): ?>
        <div style="padding:48px;text-align:center;color:var(--text-muted)">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3;display:block;margin:0 auto 12px"><path d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
            Aucune ligne lettrée.
        </div>
        <?php else: ?>
        <!-- Barre recherche tiers (lettrées) -->
        <div style="padding:12px 20px 0;background:#f8fafc;border-bottom:1px solid #f1f5f9">
            <div style="position:relative;max-width:320px">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="search-tiers-l" oninput="filterTiersLettrees(this.value)" placeholder="Rechercher un tiers…"
                    style="width:100%;padding:7px 34px 7px 34px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:16px;outline:none;background:#fff;color:var(--text);transition:border-color .15s"
                    onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#e2e8f0'">
                <button type="button" id="clear-search-l" onclick="clearSearchLettrees()" style="display:none;position:absolute;right:9px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;font-size:16px;line-height:1;padding:0">&#215;</button>
            </div>
            <div id="search-info-l" style="font-size:15px;color:var(--text-muted);padding:4px 2px 8px;display:none"></div>
        </div>
        <?php
        // Calcul soldes par tiers (toutes lignes lettrées)
        $soldesParTiers = [];
        foreach ($allLettrees as $code => $g) {
            foreach ($g['lignes'] as $l) {
                $nom = !empty($l['nom_tiers']) ? $l['nom_tiers'] : '(sans tiers)';
                $cpt = $l['compte_numero'];
                $key = $nom . '||' . $cpt;
                if (!isset($soldesParTiers[$key])) {
                    $soldesParTiers[$key] = ['nom' => $nom, 'compte' => $cpt, 'debit' => 0, 'credit' => 0];
                }
                $soldesParTiers[$key]['debit']  = round($soldesParTiers[$key]['debit']  + (float)$l['debit'],  2);
                $soldesParTiers[$key]['credit'] = round($soldesParTiers[$key]['credit'] + (float)$l['credit'], 2);
            }
        }
        uasort($soldesParTiers, fn($a,$b) => strcmp($a['nom'], $b['nom']));
        ?>
        <!-- Résumé soldes par tiers -->
        <div style="padding:16px 20px 0">
            <div style="font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:10px;display:flex;align-items:center;gap:8px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Soldes par tiers
            </div>
            <table style="width:100%;border-collapse:collapse;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:20px">
                <thead>
                    <tr style="background:#f1f5f9;border-bottom:1px solid #e5e7eb">
                        <th style="padding:8px 14px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);text-align:left">Tiers</th>
                        <th style="padding:8px 14px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);text-align:left">Compte</th>
                        <th style="padding:8px 14px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--danger);text-align:right">Solde Débit</th>
                        <th style="padding:8px 14px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#16a34a;text-align:right">Solde Crédit</th>
                        <th style="padding:8px 14px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);text-align:right">Solde</th>
                        <th style="padding:8px 14px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);text-align:center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($soldesParTiers as $row):
                        $solde   = round($row['debit'] - $row['credit'], 2);
                        $isSolde = abs($solde) < 0.01;
                        $isDebit = $solde > 0.01;
                    ?>
                    <tr class="tiers-solde-row" data-tiers="<?= strtolower(e($row['nom'])) ?>" style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:9px 14px;font-size:16px;font-weight:500;color:var(--navy-dark)">
                            <?php if ($row['nom'] !== '(sans tiers)'): ?>
                            <span style="display:inline-flex;align-items:center;gap:5px">
                                <span style="width:7px;height:7px;border-radius:50%;background:#94a3b8;display:inline-block"></span>
                                <?= e($row['nom']) ?>
                            </span>
                            <?php else: ?>
                            <span style="color:var(--text-muted);font-style:italic;font-size:15px">(sans tiers)</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:9px 14px"><span class="compte-chip"><?= e($row['compte']) ?></span></td>
                        <td style="padding:9px 14px;text-align:right;font-family:monospace;font-size:16px;color:var(--danger);font-weight:500">
                            <?= $row['debit'] > 0 ? number_format($row['debit'],0,',',' ') : '—' ?>
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:monospace;font-size:16px;color:#16a34a;font-weight:500">
                            <?= $row['credit'] > 0 ? number_format($row['credit'],0,',',' ') : '—' ?>
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:monospace;font-size:16px;font-weight:700;color:<?= $isSolde ? '#16a34a' : ($isDebit ? 'var(--danger)' : '#2563eb') ?>">
                            <?= $isSolde ? '0' : number_format(abs($solde),0,',',' ') ?>
                        </td>
                        <td style="padding:9px 14px;text-align:center">
                            <?php if ($isSolde): ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;border-radius:20px;padding:3px 10px;font-size:14px;font-weight:600">
                                &#10003; Soldé
                            </span>
                            <?php elseif ($isDebit): ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:20px;padding:3px 10px;font-size:14px;font-weight:600">
                                &#8679; Débiteur
                            </span>
                            <?php else: ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:20px;padding:3px 10px;font-size:14px;font-weight:600">
                                &#8681; Créditeur
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Séparateur -->
        <div style="padding:0 20px 12px;font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);display:flex;align-items:center;gap:8px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
            Groupes de lettrage
        </div>
        <?php foreach ($allLettrees as $code => $g): ?>
        <?php
            $gDebit  = round(array_sum(array_column($g['lignes'], 'debit')),  2);
            $gCredit = round(array_sum(array_column($g['lignes'], 'credit')), 2);
            $gEquil  = abs($gDebit - $gCredit) < 0.01;
            $grpId   = 'lg_' . preg_replace('/\W/','_', $code);
        ?>
        <?php
            $grpTiers = array_unique(array_map(fn($l) => strtolower(!empty($l['nom_tiers']) ? $l['nom_tiers'] : ''), $g['lignes']));
            $grpTiersStr = implode(' ', $grpTiers);
        ?>
        <div class="lettre-group lettre-group-filterable" data-tiers="<?= e($grpTiersStr) ?>">
            <div class="lettre-group-header" onclick="toggleGroup('<?= $grpId ?>')">
                <span style="display:flex;align-items:center;gap:10px">
                    <span id="arr-<?= $grpId ?>" style="font-size:9px;color:#16a34a;transition:transform .18s">&#9658;</span>
                    <span class="badge-lettre" style="font-size:16px;padding:3px 13px"><?= e($code) ?></span>
                    <span style="font-size:15px;color:var(--text-muted)"><?= count($g['lignes']) ?> écriture<?= count($g['lignes'])>1?'s':'' ?> · compte <span class="compte-chip" style="font-size:14px"><?= e($g['compte']) ?></span></span>
                    <?php if ($gEquil): ?>
                    <span style="font-size:14px;background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;border-radius:6px;padding:1px 8px;font-weight:600">&#10003; Équilibré</span>
                    <?php else: ?>
                    <span style="font-size:14px;background:#fee2e2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;padding:1px 8px;font-weight:600">&#9888; Déséquilibré</span>
                    <?php endif; ?>
                </span>
                <span style="display:flex;gap:14px;align-items:center">
                    <?php if ($gDebit > 0): ?><span style="font-size:15px;font-family:monospace;color:var(--danger)"><?= number_format($gDebit,0,',',' ') ?></span><?php endif; ?>
                    <?php if ($gCredit > 0): ?><span style="font-size:15px;font-family:monospace;color:#16a34a"><?= number_format($gCredit,0,',',' ') ?></span><?php endif; ?>
                    <form method="POST" action="<?= APP_URL ?>/dossier/lettrage/delettrer" style="display:inline" onsubmit="return confirm('Supprimer le lettrage <?= e(addslashes($code)) ?> ?')">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= (int)$entreprise['id'] ?>">
                        <input type="hidden" name="code" value="<?= e($code) ?>">
                        <input type="hidden" name="type" value="<?= e($type) ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 11px;font-size:14px">x Dé-lettrer</button>
                    </form>
                </span>
            </div>
            <div id="<?= $grpId ?>" style="display:none">
                <table class="lettrage-table" style="background:#fafff8">
                    <thead>
                        <tr style="background:#f0fdf4">
                            <th>Date</th><th>Compte</th><th>Libellé</th><th>Tiers</th><th>Journal</th>
                            <th style="text-align:right">Débit</th><th style="text-align:right">Crédit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($g['lignes'] as $l): ?>
                        <tr>
                            <td style="font-size:15px;white-space:nowrap;color:var(--text-muted)"><?= e(date('d/m/Y', strtotime($l['date_ecriture']))) ?></td>
                            <td><span class="compte-chip"><?= e($l['compte_numero']) ?></span></td>
                            <td style="font-size:16px"><?= e($l['libelle'] ?: $l['ecriture_libelle']) ?></td>
                            <td style="font-size:15px">
                                <?php if (!empty($l['nom_tiers'])): ?>
                                <span style="display:inline-flex;align-items:center;background:rgba(30,58,95,.06);border:1px solid rgba(30,58,95,.12);border-radius:20px;padding:2px 9px;font-size:14px;color:var(--navy);font-weight:500"><?= e($l['nom_tiers']) ?></span>
                                <?php else: ?>--<?php endif; ?>
                            </td>
                            <td><span class="badge badge-navy" style="font-size:14px"><?= e($l['journal_code']) ?></span></td>
                            <td style="text-align:right;font-family:monospace;font-size:16px;color:var(--danger)"><?= $l['debit'] > 0 ? number_format($l['debit'],0,',',' ') : '--' ?></td>
                            <td style="text-align:right;font-family:monospace;font-size:16px;color:#16a34a"><?= $l['credit'] > 0 ? number_format($l['credit'],0,',',' ') : '--' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tab: Suggestions IA -->
    <div class="tab-panel" id="panel-ia">
    <?php if (empty($suggestions)): ?>
    <div style="padding:60px 20px;text-align:center;color:var(--text-muted)">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.5" style="display:block;margin:0 auto 16px;opacity:.5"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2"/><path d="M9 12l2 2 4-4"/></svg>
        <div style="font-size:17px;font-weight:600;color:#7c3aed;margin-bottom:6px">Aucune suggestion trouvée</div>
        <div style="font-size:16px;max-width:340px;margin:0 auto;line-height:1.6">
            <?php if (empty($allNonLettrees)): ?>
            Toutes les lignes sont déjà lettrées. 🎉
            <?php else: ?>
            Aucune correspondance débit/crédit automatique détectée sur les <?= $cntNL ?> ligne<?= $cntNL>1?'s':'' ?> non lettrées.
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Header -->
    <div style="padding:14px 20px;background:#faf5ff;border-bottom:2px solid #e9d5ff;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#a78bfa);display:flex;align-items:center;justify-content:center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:#5b21b6"><?= $cntIA ?> suggestion<?= $cntIA>1?'s':'' ?> de lettrage</div>
                <div style="font-size:17px;color:#7c3aed;margin-top:1px">Correspondances détectées automatiquement — vérifiez avant de valider</div>
            </div>
        </div>
        <button type="button" onclick="applyAllSuggestions()" style="padding:7px 16px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            Tout lettrer
        </button>
    </div>

    <?php foreach ($suggestions as $si => $sug):
        $score    = $sug['score'];
        $pct      = min(100, $score);
        $scoreColor = $score >= 90 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
        $scoreBg    = $score >= 90 ? '#dcfce7' : ($score >= 60 ? '#fef9c3' : '#fee2e2');
        $scoreBorder= $score >= 90 ? '#bbf7d0' : ($score >= 60 ? '#fde047' : '#fecaca');
        $scoreLabel = $score >= 90 ? 'Très fiable' : ($score >= 60 ? 'Probable' : 'Incertain');
        $totalD = array_sum(array_column($sug['lignes'], 'debit'));
        $totalC = array_sum(array_column($sug['lignes'], 'credit'));
        $balanced = abs($totalD - $totalC) < 0.01;
        $ids_json = json_encode($sug['ids']);
    ?>
    <div class="ia-suggestion" id="ia-sug-<?= $si ?>" style="border-bottom:1px solid #f3e8ff;background:#fff">
        <!-- En-tête suggestion -->
        <div style="padding:12px 20px;background:#faf5ff;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span style="font-size:15px;font-weight:700;color:#5b21b6">Suggestion #<?= $si+1 ?></span>
                <!-- Score -->
                <span style="display:inline-flex;align-items:center;gap:5px;background:<?= $scoreBg ?>;border:1px solid <?= $scoreBorder ?>;border-radius:20px;padding:2px 10px;font-size:14px;font-weight:700;color:<?= $scoreColor ?>">
                    <?= $scoreLabel ?> — <?= $score ?>%
                    <span style="width:40px;height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;display:inline-block">
                        <span style="display:block;height:100%;width:<?= $pct ?>%;background:<?= $scoreColor ?>;border-radius:2px"></span>
                    </span>
                </span>
                <!-- Type -->
                <?php if ($sug['type'] === 'multi'): ?>
                <span style="font-size:14px;background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;border-radius:20px;padding:2px 9px;font-weight:600">Multi-lignes</span>
                <?php else: ?>
                <span style="font-size:14px;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;border-radius:20px;padding:2px 9px;font-weight:600">Exact 1:1</span>
                <?php endif; ?>
                <!-- Équilibre -->
                <?php if ($balanced): ?>
                <span style="font-size:14px;background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;border-radius:6px;padding:2px 8px;font-weight:600">✓ Équilibré</span>
                <?php endif; ?>
                <!-- Montant -->
                <span style="font-size:15px;font-family:monospace;font-weight:600;color:var(--navy-dark)"><?= number_format($totalC ?: $totalD, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <button type="button" onclick="applySuggestion(<?= $si ?>, <?= htmlspecialchars($ids_json) ?>)"
                    style="padding:6px 14px;background:#7c3aed;color:#fff;border:none;border-radius:7px;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    Lettrer
                </button>
                <button type="button" onclick="dismissSuggestion(<?= $si ?>)"
                    style="padding:6px 10px;background:#fff;color:#94a3b8;border:1px solid #e5e7eb;border-radius:7px;font-size:15px;cursor:pointer" title="Ignorer">✕</button>
            </div>
        </div>
        <!-- Lignes de la suggestion -->
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f5f3ff">
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#7c3aed;text-align:left">Date</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#7c3aed;text-align:left">Compte</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#7c3aed;text-align:left">N° Pièce</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#7c3aed;text-align:left">Libellé</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#7c3aed;text-align:left">Tiers</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#7c3aed;text-align:left">Journal</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--danger);text-align:right">Débit</th>
                    <th style="padding:6px 14px;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#16a34a;text-align:right">Crédit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sug['lignes'] as $l): ?>
                <tr style="border-top:1px solid #f3e8ff">
                    <td style="padding:8px 14px;font-size:15px;color:var(--text-muted);white-space:nowrap"><?= e(date('d/m/Y', strtotime($l['date_ecriture']))) ?></td>
                    <td style="padding:8px 14px"><span class="compte-chip"><?= e($l['compte_numero']) ?></span></td>
                    <td style="padding:8px 14px;font-size:15px;font-family:monospace;color:var(--text-muted)"><?= !empty($l['numero_facture']) ? e($l['numero_facture']) : '—' ?></td>
                    <td style="padding:8px 14px;font-size:16px"><?= e($l['libelle'] ?: $l['ecriture_libelle']) ?></td>
                    <td style="padding:8px 14px;font-size:15px">
                        <?php if (!empty($l['nom_tiers'])): ?>
                        <span style="display:inline-flex;align-items:center;background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.15);border-radius:20px;padding:2px 9px;font-size:14px;color:#7c3aed;font-weight:500"><?= e($l['nom_tiers']) ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="padding:8px 14px"><span class="badge badge-navy" style="font-size:14px"><?= e($l['journal_code']) ?></span></td>
                    <td style="padding:8px 14px;text-align:right;font-family:monospace;font-size:16px;font-weight:600;color:var(--danger)"><?= $l['debit'] > 0 ? number_format($l['debit'],0,',',' ') : '—' ?></td>
                    <td style="padding:8px 14px;text-align:right;font-family:monospace;font-size:16px;font-weight:600;color:#16a34a"><?= $l['credit'] > 0 ? number_format($l['credit'],0,',',' ') : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Metadonnées pour JS -->
    <script>
    var iaSuggestions = <?= json_encode(array_map(fn($s) => ['ids' => $s['ids']], $suggestions)) ?>;
    </script>
    </div>

</div>

<?php endif; ?>
<style>
.sort-icon { font-size:16px; color:#cbd5e1; margin-left:2px; }
.sortable:hover .sort-icon { color:#94a3b8; }
.tiers-chip:hover { background:rgba(30,58,95,.12) !important; border-color:rgba(30,58,95,.3) !important; }
.tiers-top-row:hover td { background:#e9eef5; }
</style>
<script>
/* ── Tab switching ── */
function switchTab(name) {
    document.querySelectorAll('.inner-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelector('.itab-' + name).classList.add('active');
    document.getElementById('panel-' + name).classList.add('active');
}

/* ── Lettrées group toggle ── */
function toggleGroup(id) {
    var panel = document.getElementById(id);
    var arrow = document.getElementById('arr-' + id);
    var open  = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : '';
    arrow.style.transform = open ? '' : 'rotate(90deg)';
}

/* ── Selection counter + balance ── */
function updateCounter() {
    var visibleChks = document.querySelectorAll('.nl-row:not([style*="display: none"]) .ligne-chk:checked, .nl-row:not([style*="display:none"]) .ligne-chk:checked');
    var allChks = document.querySelectorAll('.nl-row .ligne-chk:checked');
    // use all checked (visible)
    var chks = document.querySelectorAll('.ligne-chk:checked');
    var count = chks.length;
    document.getElementById('sel-count').textContent = count;
    document.getElementById('btn-lettrer').disabled = count < 2;

    var debit = 0, credit = 0;
    chks.forEach(function(c) {
        var row = c.closest('tr');
        debit  += parseFloat(row.dataset.debit  || 0);
        credit += parseFloat(row.dataset.credit || 0);
    });
    var info = document.getElementById('sel-balance-info');
    if (count >= 2) {
        var diff = Math.abs(debit - credit);
        var bal  = diff < 0.01;
        info.style.display = '';
        info.textContent = '';
        var span = document.createElement('span');
        span.style.fontWeight = '600';
        if (bal) { span.style.color = '#16a34a'; span.textContent = '✓ Équilibré'; }
        else { span.style.color = '#dc2626'; span.textContent = '⚠ Écart : ' + diff.toLocaleString('fr-FR') + ' FCFA'; }
        info.appendChild(span);
    } else { info.style.display = 'none'; }

    var all = document.querySelectorAll('.ligne-chk');
    var chkAll = document.getElementById('chk-all');
    if (chkAll) {
        chkAll.indeterminate = count > 0 && count < all.length;
        chkAll.checked = count === all.length && all.length > 0;
    }
}

function toggleAll(chk) {
    document.querySelectorAll('.nl-row').forEach(function(row) {
        if (row.style.display !== 'none') {
            var c = row.querySelector('.ligne-chk');
            if (c) c.checked = chk.checked;
        }
    });
    updateCounter();
}

/* ── Filtres combinés ── */
function applyFilters() {
    var q       = (document.getElementById('search-tiers').value || '').trim().toLowerCase();
    var compte  = (document.getElementById('filter-compte')  ? document.getElementById('filter-compte').value  : '');
    var journal = (document.getElementById('filter-journal') ? document.getElementById('filter-journal').value : '');

    var clearBtn   = document.getElementById('clear-search');
    var info       = document.getElementById('search-info');
    var resetBtn   = document.getElementById('btn-reset-filters');
    var hasFilter  = q || compte || journal;

    clearBtn.style.display  = q ? '' : 'none';
    resetBtn.style.display  = hasFilter ? '' : 'none';

    var shown = 0;
    document.querySelectorAll('.nl-row').forEach(function(row) {
        var matchTiers   = !q      || row.dataset.tiers.includes(q);
        var matchCompte  = !compte  || row.dataset.compte === compte;
        var matchJournal = !journal || row.dataset.journal === journal;
        var visible = matchTiers && matchCompte && matchJournal;
        row.style.display = visible ? '' : 'none';
        if (!visible) { var c = row.querySelector('.ligne-chk'); if (c) c.checked = false; }
        if (visible) shown++;
    });

    // sync tableau soldes par tiers
    document.querySelectorAll('.tiers-top-row').forEach(function(row) {
        var match = !q || row.dataset.tiersName.includes(q);
        row.style.display = match ? '' : 'none';
    });

    if (hasFilter) {
        info.style.display = '';
        info.textContent = shown + ' ligne' + (shown !== 1 ? 's' : '');
        info.style.color = shown === 0 ? '#dc2626' : 'var(--text-muted)';
    } else { info.style.display = 'none'; }

    updateCounter();
}

function resetFilters() {
    document.getElementById('search-tiers').value = '';
    var fc = document.getElementById('filter-compte');  if (fc) fc.value = '';
    var fj = document.getElementById('filter-journal'); if (fj) fj.value = '';
    applyFilters();
}

/* ── Sélection par tiers ── */
function selectByTiers(nom) {
    var nomLower = nom.toLowerCase();
    document.querySelectorAll('.ligne-chk').forEach(function(c){ c.checked = false; });
    document.querySelectorAll('.nl-row').forEach(function(row) {
        if (row.style.display === 'none') return;
        if (row.dataset.tiers === nomLower) {
            var c = row.querySelector('.ligne-chk');
            if (c) c.checked = true;
        }
    });
    updateCounter();
    var inp = document.getElementById('search-tiers');
    if (inp) { inp.value = nom; applyFilters(); }
}

function selectByTiersRow(el) {
    var nom = el.dataset.tiersRaw || el.dataset.tiers || '';
    selectByTiers(nom);
}

/* ── Tri des colonnes ── */
var sortState = { col: null, dir: 1 };
function sortTable(col) {
    var tbody = document.getElementById('tbody-nonlettrees');
    if (!tbody) return;
    var rows = Array.from(tbody.querySelectorAll('.nl-row'));
    var dir = (sortState.col === col) ? -sortState.dir : 1;
    sortState = { col: col, dir: dir };

    // Reset icons
    document.querySelectorAll('.sort-icon').forEach(function(el){ el.textContent = '↕'; el.style.color = '#cbd5e1'; });
    var icon = document.getElementById('si-' + col);
    if (icon) { icon.textContent = dir === 1 ? '↑' : '↓'; icon.style.color = 'var(--navy)'; }

    rows.sort(function(a, b) {
        var av, bv;
        if (col === 'date')   { av = a.dataset.date;   bv = b.dataset.date; }
        if (col === 'compte') { av = a.dataset.compte; bv = b.dataset.compte; }
        if (col === 'tiers')  { av = a.dataset.tiers;  bv = b.dataset.tiers; }
        if (col === 'debit')  { av = parseFloat(a.dataset.debit);  bv = parseFloat(b.dataset.debit);  return dir * (av - bv); }
        if (col === 'credit') { av = parseFloat(a.dataset.credit); bv = parseFloat(b.dataset.credit); return dir * (av - bv); }
        return dir * String(av).localeCompare(String(bv));
    });
    rows.forEach(function(r){ tbody.appendChild(r); });
}

/* ── Filtre lettrées ── */
function filterTiersLettrees(q) {
    q = q.trim().toLowerCase();
    var clearBtn = document.getElementById('clear-search-l');
    var info     = document.getElementById('search-info-l');
    clearBtn.style.display = q ? '' : 'none';
    var shownSoldes = 0, shownGroups = 0;
    document.querySelectorAll('.tiers-solde-row').forEach(function(row) {
        var match = !q || row.dataset.tiers.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) shownSoldes++;
    });
    document.querySelectorAll('.lettre-group-filterable').forEach(function(grp) {
        var match = !q || grp.dataset.tiers.includes(q);
        grp.style.display = match ? '' : 'none';
        if (match) shownGroups++;
    });
    if (q) {
        info.style.display = '';
        info.textContent = shownSoldes + ' tiers · ' + shownGroups + ' groupe' + (shownGroups !== 1 ? 's' : '') + ' pour "' + q + '"';
        info.style.color = (shownSoldes + shownGroups) === 0 ? '#dc2626' : 'var(--text-muted)';
    } else { info.style.display = 'none'; }
}

function clearSearchLettrees() {
    var inp = document.getElementById('search-tiers-l');
    inp.value = '';
    filterTiersLettrees('');
    inp.focus();
}

/* ── Suggestions IA ── */
function applySuggestion(idx, ids) {
    if (!confirm('Lettrer ces ' + ids.length + ' lignes ?')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = <?= json_encode(APP_URL) ?> + '/dossier/lettrage/lettrer';
    var fields = {
        'csrf_token': <?= json_encode(generateCsrfToken()) ?>,
        'id':         '<?= (int)$entreprise['id'] ?>',
        'type':       <?= json_encode($type) ?>
    };
    for (var k in fields) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = fields[k];
        form.appendChild(inp);
    }
    ids.forEach(function(id) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'lignes[]'; inp.value = id;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
}

function dismissSuggestion(idx) {
    var el = document.getElementById('ia-sug-' + idx);
    if (el) {
        el.style.transition = 'opacity .3s, max-height .3s';
        el.style.opacity = '0';
        el.style.overflow = 'hidden';
        el.style.maxHeight = el.offsetHeight + 'px';
        setTimeout(function(){ el.style.maxHeight = '0'; el.style.padding = '0'; }, 10);
        setTimeout(function(){ el.remove(); }, 320);
    }
}

function applyAllSuggestions() {
    if (!confirm('Lettrer toutes les suggestions automatiquement ?\nChaque groupe sera soumis séquentiellement.')) return;
    if (typeof iaSuggestions === 'undefined' || iaSuggestions.length === 0) return;
    // Soumettre la première suggestion — la page rechargera et continuera
    applySuggestion(0, iaSuggestions[0].ids);
}
</script>
