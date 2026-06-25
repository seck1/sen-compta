<?php
$modules = RegimeFiscalService::getModulesDisponibles($regime);
$isApplicable = $modules['is'];

// Compute IS figures from declaration or live data
$rc  = $declaration['resultat_comptable'] ?? $resultat_comptable;
$rei = $declaration['reintegrations']     ?? 0;
$ded = $declaration['deductions']         ?? 0;
$rf  = $declaration['resultat_fiscal']    ?? ($rc + $rei - $ded);
$is_du_saved  = $declaration['is_du']    ?? null;
$acomptes_db  = $declaration['acomptes_verses'] ?? $acomptes_verses;
$is_net_saved = $declaration['is_net']   ?? null;

// Live IS calculation for display
$taux_is      = 0.30;
$minimum_is   = max(500000, $ca_ht * 0.005); // CGI Art. 294 : assiette = CA HT
$is_theorique = round(max(0, $rf) * $taux_is, 2);
$is_du_calc   = $rf > 0 ? max($minimum_is, $is_theorique) : $minimum_is;
$is_net_calc  = max(0, $is_du_calc - $acomptes_db);

// Acomptes provisionnels CGI Art. 213 : 40% + 20% + 20% de l'IS N-1
// Dates légales : 15 avril / 15 juillet / 15 novembre
$base_acomptes = $is_du_n1 ?? 0;
$a1 = round($base_acomptes * 0.40);
$a2 = round($base_acomptes * 0.20);
$a3 = round($base_acomptes * 0.20);
?>

<?php if ($saved): ?>
<div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;color:#1f6e4e;font-size:16px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Déclaration IS enregistrée avec succès.
</div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Impôt sur les Sociétés</h1>
        <p class="page-subtitle">Calcul et déclaration IS — Exercice <?= $exercice ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <span style="padding:5px 14px;border-radius:20px;font-size:15px;font-weight:600;color:#fff;background:<?= RegimeFiscalService::getBadgeColor($regime) ?>"><?= e($regime) ?></span>
        <!-- Exercice selector -->
        <form method="get" style="display:flex;align-items:center;gap:6px">
            <input type="hidden" name="id" value="<?= (int)$entreprise['id'] ?>">
            <select name="exercice" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--border);border-radius:9px;font-size:16px;background:white;cursor:pointer">
                <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                <option value="<?= $y ?>" <?= $y == $exercice ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimer
        </button>
    </div>
</div>

<?php if ($isApplicable && $ca_ht == 0): ?>
<div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);border-radius:10px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;color:#92400e;font-size:16px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;color:#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    CA HT = 0 : le minimum IS de <strong>500 000 FCFA</strong> s'applique conformément au CGI (art. 206).
</div>
<?php endif; ?>
<?php if (!$isApplicable): ?>
<div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:12px;padding:20px 24px;margin-bottom:24px;display:flex;gap:14px;align-items:flex-start">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px;color:#d97706;flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    <div>
        <div style="font-weight:600;color:#d97706;margin-bottom:4px">IS non applicable</div>
        <div style="font-size:16px;color:#92400e">Le régime <strong><?= e($regime) ?></strong> n'est pas soumis à l'Impôt sur les Sociétés. L'IS s'applique uniquement au régime réel normal (CGI).</div>
    </div>
</div>
<?php endif; ?>

<!-- KPI Row -->
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="kpi-card">
        <div class="kpi-label">Résultat comptable</div>
        <div class="kpi-value" style="font-size:22px;color:<?= $rc >= 0 ? 'var(--success)' : 'var(--danger)' ?>"><?= formatMontant($rc) ?></div>
        <div class="kpi-sub">Compte de résultat</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Réintégrations</div>
        <div class="kpi-value" style="font-size:22px;color:var(--danger)"><?= formatMontant($rei) ?></div>
        <div class="kpi-sub">Charges non déductibles</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Déductions</div>
        <div class="kpi-value" style="font-size:22px;color:var(--success)"><?= formatMontant($ded) ?></div>
        <div class="kpi-sub">Produits exonérés</div>
    </div>
    <div class="kpi-card" style="border-color:var(--navy);background:rgba(30,58,95,0.03)">
        <div class="kpi-label">Résultat fiscal</div>
        <div class="kpi-value" style="font-size:22px;color:<?= $rf >= 0 ? 'var(--navy)' : 'var(--danger)' ?>"><?= formatMontant($rf) ?></div>
        <div class="kpi-sub">Base imposable IS</div>
    </div>
</div>

<form method="post" action="<?= APP_URL ?>/dossier/fiscalite/is/store">
<input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
<input type="hidden" name="entreprise_id" value="<?= (int)$entreprise['id'] ?>">
<input type="hidden" name="exercice" value="<?= (int)$exercice ?>">
<input type="hidden" name="ca_ht" value="<?= (float)$ca_ht ?>">

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    <!-- Passage du résultat comptable au fiscal -->
    <div class="card">
        <div style="font-size:17px;font-weight:600;color:var(--navy-dark);margin-bottom:18px;display:flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:var(--gold)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" /></svg>
            Passage comptable → fiscal
        </div>
        <div class="form-grid" style="gap:14px">
            <div class="form-field">
                <label>Résultat comptable (FCFA)</label>
                <input type="number" name="resultat_comptable" value="<?= round($resultat_comptable) ?>" step="1" required>
            </div>
            <div class="form-field">
                <label>Réintégrations</label>
                <input type="number" name="reintegrations" id="rei" value="<?= round($rei) ?>" step="1" min="0" oninput="recalcIS()">
                <span style="font-size:14px;color:var(--text-muted)">Charges non déductibles : amendes, dépréciation excédentaire…</span>
            </div>
            <div class="form-field">
                <label>Déductions</label>
                <input type="number" name="deductions" id="ded" value="<?= round($ded) ?>" step="1" min="0" oninput="recalcIS()">
                <span style="font-size:14px;color:var(--text-muted)">Produits exonérés, plus-values en remploi…</span>
            </div>
        </div>
        <div style="margin-top:16px;padding:14px;background:rgba(30,58,95,0.05);border-radius:10px;display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:16px;font-weight:600;color:var(--navy)">Résultat fiscal</span>
            <span id="rf_display" style="font-size:18px;font-weight:700;font-family:'Cormorant Garamond',serif;color:var(--navy-dark)"><?= formatMontant($rf) ?></span>
        </div>
    </div>

    <!-- Calcul IS -->
    <div class="card">
        <div style="font-size:17px;font-weight:600;color:var(--navy-dark);margin-bottom:18px;display:flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:var(--gold)"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z" /></svg>
            Calcul IS
        </div>
        <table style="width:100%;font-size:16px">
            <tbody>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:9px 0;color:var(--text-muted)">CA HT (base minimum)</td>
                    <td style="text-align:right;font-family:monospace"><?= formatMontant($ca_ht) ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:9px 0;color:var(--text-muted)">IS théorique (30%)</td>
                    <td id="is_theorique" style="text-align:right;font-family:monospace"><?= formatMontant($is_theorique) ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:9px 0;color:var(--text-muted)">Minimum IS (0,5% CA ou 500 000 F)</td>
                    <td style="text-align:right;font-family:monospace"><?= formatMontant($minimum_is) ?></td>
                </tr>
                <tr style="background:rgba(201,169,110,0.08)">
                    <td style="padding:11px 0;font-weight:700;color:var(--navy-dark)">IS dû</td>
                    <td id="is_du" style="text-align:right;font-size:19px;font-weight:700;font-family:'Cormorant Garamond',serif;color:var(--navy-dark)"><?= formatMontant($is_du_calc) ?></td>
                </tr>
            </tbody>
        </table>
        <div style="margin-top:14px;padding:12px;border:2px solid var(--gold);border-radius:10px;background:rgba(201,169,110,0.06)">
            <div style="font-size:14px;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dark);margin-bottom:4px">IS NET À PAYER</div>
            <div id="is_net" style="font-size:28px;font-family:'Cormorant Garamond',serif;font-weight:600;color:var(--navy-dark)"><?= formatMontant($is_net_calc) ?></div>
        </div>
    </div>
</div>

<!-- Acomptes provisionnels -->
<div class="card" style="margin-bottom:20px">
    <div style="font-size:17px;font-weight:600;color:var(--navy-dark);margin-bottom:6px;display:flex;align-items:center;gap:8px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:var(--gold)"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
        Acomptes provisionnels — IS <?= $exercice - 1 ?> (base N-1)
    </div>
    <?php if ($base_acomptes <= 0): ?>
    <div style="font-size:15px;color:var(--text-muted);margin-bottom:14px;padding:8px 12px;background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb">
        Aucune déclaration IS enregistrée pour <?= $exercice - 1 ?> — les acomptes provisionnels ne peuvent pas être calculés automatiquement.
        Base de calcul : IS dû de l'exercice <?= $exercice - 1 ?> (à renseigner après dépôt de la déclaration N-1).
    </div>
    <?php else: ?>
    <div style="font-size:15px;color:var(--text-muted);margin-bottom:14px">
        Basés sur l'IS dû <?= $exercice - 1 ?> : <strong style="color:var(--navy-dark)"><?= formatMontant($base_acomptes) ?></strong>
    </div>
    <?php endif; ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr) auto;gap:14px;align-items:start">
        <div style="padding:14px;background:var(--bg);border-radius:10px;border:1px solid var(--border)<?= $base_acomptes <= 0 ? ';opacity:0.5' : '' ?>">
            <div style="font-size:14px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">1er Acompte — 15 Avril</div>
            <div style="font-size:19px;font-weight:600;font-family:'Cormorant Garamond',serif;color:var(--navy-dark)"><?= $base_acomptes > 0 ? formatMontant($a1) : '— FCFA' ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:4px">40% IS <?= $exercice - 1 ?></div>
        </div>
        <div style="padding:14px;background:var(--bg);border-radius:10px;border:1px solid var(--border)<?= $base_acomptes <= 0 ? ';opacity:0.5' : '' ?>">
            <div style="font-size:14px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">2ème Acompte — 15 Juillet</div>
            <div style="font-size:19px;font-weight:600;font-family:'Cormorant Garamond',serif;color:var(--navy-dark)"><?= $base_acomptes > 0 ? formatMontant($a2) : '— FCFA' ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:4px">20% IS <?= $exercice - 1 ?></div>
        </div>
        <div style="padding:14px;background:var(--bg);border-radius:10px;border:1px solid var(--border)<?= $base_acomptes <= 0 ? ';opacity:0.5' : '' ?>">
            <div style="font-size:14px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">3ème Acompte — 15 Novembre</div>
            <div style="font-size:19px;font-weight:600;font-family:'Cormorant Garamond',serif;color:var(--navy-dark)"><?= $base_acomptes > 0 ? formatMontant($a3) : '— FCFA' ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:4px">20% IS <?= $exercice - 1 ?></div>
        </div>
        <div class="form-field" style="min-width:180px">
            <label>Acomptes versés (total réel)</label>
            <input type="number" name="acomptes_verses" id="acomptes_verses" value="<?= round($acomptes_db) ?>" step="1" min="0" oninput="recalcIS()">
            <span style="font-size:14px;color:var(--text-muted)">Somme effectivement versée au Trésor</span>
        </div>
    </div>
</div>

<!-- Actions -->
<div style="display:flex;justify-content:flex-end;gap:10px">
    <a href="<?= APP_URL ?>/dossier/fiscalite/regime?id=<?= (int)$entreprise['id'] ?>" class="btn btn-outline">Annuler</a>
    <button type="submit" class="btn btn-primary" <?= !$isApplicable ? 'style="opacity:0.5;pointer-events:none"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" /></svg>
        Enregistrer la déclaration
    </button>
</div>
</form>

<script>
const rc = <?= json_encode((float)$resultat_comptable) ?>;
const caHt = <?= json_encode((float)$ca_ht) ?>;
const minimumIs = <?= json_encode((float)$minimum_is) ?>;

function formatFcfa(v) {
    return new Intl.NumberFormat('fr-FR', {minimumFractionDigits:0, maximumFractionDigits:0}).format(Math.round(v)) + ' F';
}

function recalcIS() {
    const rei = parseFloat(document.getElementById('rei').value) || 0;
    const ded = parseFloat(document.getElementById('ded').value) || 0;
    const av  = parseFloat(document.getElementById('acomptes_verses').value) || 0;
    const rf  = rc + rei - ded;
    const isTh = Math.max(0, rf) * 0.30;
    const isDu = rf > 0 ? Math.max(minimumIs, isTh) : minimumIs;
    const isNet = Math.max(0, isDu - av);

    document.getElementById('rf_display').textContent  = formatFcfa(rf);
    document.getElementById('is_theorique').textContent = formatFcfa(isTh);
    document.getElementById('is_du').textContent        = formatFcfa(isDu);
    document.getElementById('is_net').textContent       = formatFcfa(isNet);
}
</script>
