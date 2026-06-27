<?php
function pct(float $val): string { return number_format($val * 100, 2); }
?>
<div class="page-header">
    <div>
        <div class="page-title">Paramètres Paie Sénégal</div>
        <div class="page-subtitle">Configurez les taux de cotisations sociales propres à votre entreprise. Ces paramètres s'appliquent à tous les nouveaux bulletins.</div>
    </div>
</div>

<?php if($saved): ?>
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Paramètres enregistrés avec succès.
</div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/dossier/rh/parametres/store">
<input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

<style>
.params-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.params-card { background:#fff; border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.params-card-head { padding:16px 22px; background:var(--navy); color:#fff; display:flex; align-items:center; gap:10px; }
.params-card-head svg { width:18px; height:18px; opacity:.8; }
.params-card-head h3 { font-size:14px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; }
.params-card-body { padding:20px 22px; display:flex; flex-direction:column; gap:14px; }
.param-row { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; padding-bottom:14px; border-bottom:1px solid var(--border); }
.param-row:last-child { border-bottom:none; padding-bottom:0; }
.param-label { font-size:14px; color:var(--text); font-weight:500; }
.param-sub { font-size:14px; color:var(--text-muted); margin-top:2px; }
.param-input-wrap { display:flex; align-items:center; gap:6px; }
.param-input { width:90px; padding:7px 10px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; font-family:'DM Sans',sans-serif; text-align:right; transition:border-color .15s; }
.param-input:focus { outline:none; border-color:var(--gold); }
.param-unit { font-size:13px; color:var(--text-muted); font-weight:500; min-width:20px; }
.param-input-text { width:160px; padding:7px 10px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; font-family:'DM Sans',sans-serif; }
.param-input-text:focus { outline:none; border-color:var(--gold); }
.official-badge { display:inline-flex; align-items:center; gap:4px; font-size:13px; background:#eff6ff; color:#1d4ed8; padding:2px 7px; border-radius:20px; font-weight:600; margin-top:3px; }
.params-footer { display:flex; justify-content:flex-end; gap:12px; margin-top:24px; }
.plafond-input { width:130px; }
@media(max-width:800px){ .params-grid{ grid-template-columns:1fr; } }
</style>

<div class="params-grid">

    <!-- IPRES -->
    <div class="params-card">
        <div class="params-card-head">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
            <h3>IPRES — Institution de Prévoyance Retraite</h3>
        </div>
        <div class="params-card-body">
            <div class="param-row">
                <div>
                    <div class="param-label">Taux salarié — Tranche A</div>
                    <div class="param-sub">Plafond tranche A : <?= number_format($params['plafond_ipres_a'],0,',',' ') ?> FCFA/mois</div>
                    <span class="official-badge">Officiel 2024 : 5,60 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="ipres_salarie_a" class="param-input" step="0.01" min="0" max="20" value="<?= pct($params['ipres_salarie_a']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Taux patronal — Tranche A</div>
                    <span class="official-badge">Officiel 2024 : 8,40 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="ipres_patronal_a" class="param-input" step="0.01" min="0" max="20" value="<?= pct($params['ipres_patronal_a']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Plafond Tranche A (mensuel)</div>
                    <div class="param-sub">Au-delà → Tranche B (cadres)</div>
                    <span class="official-badge">Officiel 2024 : 768 000 FCFA</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="plafond_ipres_a" class="param-input plafond-input" step="1000" min="0" value="<?= $params['plafond_ipres_a'] ?>">
                    <span class="param-unit">F</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Taux salarié — Tranche B (cadres)</div>
                    <span class="official-badge">Officiel 2024 : 2,40 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="ipres_salarie_b" class="param-input" step="0.01" min="0" max="20" value="<?= pct($params['ipres_salarie_b']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Taux patronal — Tranche B (cadres)</div>
                    <span class="official-badge">Officiel 2024 : 3,60 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="ipres_patronal_b" class="param-input" step="0.01" min="0" max="20" value="<?= pct($params['ipres_patronal_b']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;padding-bottom:0">
                <div>
                    <div class="param-label">N° affiliation IPRES entreprise</div>
                </div>
                <input type="text" name="num_ipres_entreprise" class="param-input-text" placeholder="Ex: SN-IPRES-XXXX" value="<?= e($params['num_ipres_entreprise'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- CSS -->
    <div class="params-card">
        <div class="params-card-head">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            <h3>CSS — Caisse de Sécurité Sociale</h3>
        </div>
        <div class="params-card-body">
            <div class="param-row">
                <div>
                    <div class="param-label">Accidents du travail (patronal)</div>
                    <div class="param-sub">Variable selon le secteur d'activité</div>
                    <span class="official-badge">Fourchette : 1% à 5%</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="css_accidents_travail" class="param-input" step="0.1" min="0" max="10" value="<?= pct($params['css_accidents_travail']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Prestations familiales (patronal)</div>
                    <span class="official-badge">Officiel : 7 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="css_prestations_fam" class="param-input" step="0.01" min="0" max="20" value="<?= pct($params['css_prestations_fam']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Plafond prestations familiales</div>
                    <div class="param-sub">Base de calcul plafonnée</div>
                    <span class="official-badge">Officiel : 63 000 FCFA</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="css_plafond_pf" class="param-input plafond-input" step="1000" min="0" value="<?= $params['css_plafond_pf'] ?>">
                    <span class="param-unit">F</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;padding-bottom:0">
                <div>
                    <div class="param-label">N° immatriculation CSS entreprise</div>
                </div>
                <input type="text" name="num_css_entreprise" class="param-input-text" placeholder="Ex: SN-CSS-XXXX" value="<?= e($params['num_css_entreprise'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- IPM -->
    <div class="params-card">
        <div class="params-card-head">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
            <h3>IPM — Institution de Prévoyance Maladie</h3>
        </div>
        <div class="params-card-body">
            <div class="param-row">
                <div>
                    <div class="param-label">Cotisation salariale</div>
                    <span class="official-badge">Défaut : 0,50 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="ipm_salarie" class="param-input" step="0.01" min="0" max="10" value="<?= pct($params['ipm_salarie']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row">
                <div>
                    <div class="param-label">Cotisation patronale</div>
                    <span class="official-badge">Défaut : 3,00 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="ipm_patronal" class="param-input" step="0.01" min="0" max="10" value="<?= pct($params['ipm_patronal']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;padding-bottom:0">
                <div>
                    <div class="param-label">N° affiliation IPM entreprise</div>
                </div>
                <input type="text" name="num_ipm_entreprise" class="param-input-text" placeholder="Ex: SN-IPM-XXXX" value="<?= e($params['num_ipm_entreprise'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- CFCE -->
    <div class="params-card">
        <div class="params-card-head">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
            <h3>CFCE — Contribution Forfaitaire à la Charge de l'Employeur</h3>
        </div>
        <div class="params-card-body">
            <div class="param-row" style="border-bottom:none;padding-bottom:0">
                <div>
                    <div class="param-label">Taux CFCE (patronal)</div>
                    <div class="param-sub">Appliqué sur la masse salariale brute totale</div>
                    <span class="official-badge">Officiel : 3,00 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="cfce_taux" class="param-input" step="0.01" min="0" max="10" value="<?= pct($params['cfce_taux']) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
        </div>

        <!-- Récapitulatif charges -->
        <div style="margin:16px 22px;padding:14px;background:var(--bg);border-radius:10px;font-size:13px">
            <div style="font-weight:600;color:var(--navy);margin-bottom:8px;font-size:14px;text-transform:uppercase;letter-spacing:.05em">Rappel — charges non paramétrables</div>
            <div style="color:var(--text-muted);display:flex;flex-direction:column;gap:4px">
                <div>• <strong>TRIMF</strong> : barème progressif DGI Sénégal (fixe)</div>
                <div>• <strong>IR</strong> : barème CGI Art. 163 + quotient familial (fixe)</div>
                <div>• <strong>Plafond Tranche B IPRES</strong> : 1 536 000 FCFA (double du plafond A)</div>
            </div>
        </div>
    </div>

    <!-- Heures supplémentaires -->
    <div class="params-card" style="grid-column:1/-1">
        <div class="params-card-head">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <h3>Heures supplémentaires — Code du Travail Sénégal (Art. L.198)</h3>
        </div>
        <div class="params-card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0">
            <div class="param-row" style="border-bottom:none;border-right:1px solid var(--border);padding-right:22px;margin-right:0">
                <div>
                    <div class="param-label">Taux Tranche 1</div>
                    <div class="param-sub">Les <strong><?= $params['heures_supp_seuil'] ?? 8 ?> premières</strong> heures supp par semaine</div>
                    <span class="official-badge">Légal : 115 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="heures_supp_taux1" class="param-input" step="0.5" min="100" max="200" value="<?= number_format(($params['heures_supp_taux1'] ?? 1.15) * 100, 2) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;border-right:1px solid var(--border);padding:0 22px;margin:0">
                <div>
                    <div class="param-label">Taux Tranche 2</div>
                    <div class="param-sub">Au-delà des premières heures supp</div>
                    <span class="official-badge">Légal : 140 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="heures_supp_taux2" class="param-input" step="0.5" min="100" max="300" value="<?= number_format(($params['heures_supp_taux2'] ?? 1.40) * 100, 2) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;padding-left:22px;margin:0">
                <div>
                    <div class="param-label">Seuil passage Tranche 2</div>
                    <div class="param-sub">Nombre d'heures supp avant taux majoré</div>
                    <span class="official-badge">Standard : 8 heures</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="heures_supp_seuil" class="param-input" step="1" min="1" max="40" value="<?= $params['heures_supp_seuil'] ?? 8 ?>">
                    <span class="param-unit">h</span>
                </div>
            </div>

            <div class="param-row" style="border-bottom:none;border-right:1px solid var(--border);border-top:1px solid var(--border);padding:18px 22px 0 0;margin:0">
                <div>
                    <div class="param-label">Heures de nuit</div>
                    <div class="param-sub">Heures supp effectuées la nuit (jour ouvrable)</div>
                    <span class="official-badge">Légal : 160 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="heures_supp_taux_nuit" class="param-input" step="0.5" min="100" max="300" value="<?= number_format(($params['heures_supp_taux_nuit'] ?? 1.60) * 100, 2) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;border-right:1px solid var(--border);border-top:1px solid var(--border);padding:18px 22px 0;margin:0">
                <div>
                    <div class="param-label">Dimanche / férié — jour</div>
                    <div class="param-sub">Heures supp de jour un dimanche ou jour férié</div>
                    <span class="official-badge">Légal : 160 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="heures_supp_taux_dim" class="param-input" step="0.5" min="100" max="300" value="<?= number_format(($params['heures_supp_taux_dim'] ?? 1.60) * 100, 2) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
            <div class="param-row" style="border-bottom:none;border-top:1px solid var(--border);padding:18px 0 0 22px;margin:0">
                <div>
                    <div class="param-label">Dimanche / férié — nuit</div>
                    <div class="param-sub">Heures supp de nuit un dimanche ou jour férié</div>
                    <span class="official-badge">Légal : 200 %</span>
                </div>
                <div class="param-input-wrap">
                    <input type="number" name="heures_supp_taux_dim_nuit" class="param-input" step="0.5" min="100" max="400" value="<?= number_format(($params['heures_supp_taux_dim_nuit'] ?? 2.00) * 100, 2) ?>">
                    <span class="param-unit">%</span>
                </div>
            </div>
        </div>
        <div style="margin:0 22px 16px;padding:12px 14px;background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;font-size:13px;color:#92400e">
            <strong>Calcul (Art. L.198) :</strong> Taux horaire = Salaire base ÷ 173h. Jour : +15% (1-8h) puis +40%. Nuit : +60%. Dimanche/férié : +60% (jour) / +100% (nuit). Chaque type d'heures se saisit séparément sur le bulletin.
        </div>
    </div>

</div>

<!-- SMIG, CONGÉS & BRS -->
<div class="params-card" style="margin-top:18px">
    <div class="params-card-head">💼 SMIG, congés &amp; BRS</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0">
        <div class="param-row" style="border-bottom:none;border-right:1px solid var(--border);padding-right:22px">
            <div>
                <div class="param-label">SMIG mensuel</div>
                <div class="param-sub">Salaire minimum légal</div>
                <span class="official-badge">Décret 2023-1375 : 76 827 F</span>
            </div>
            <div class="param-input-wrap">
                <input type="number" name="smig_mensuel" class="param-input" style="width:110px" step="1" min="0" value="<?= (int)($params['smig_mensuel'] ?? 76827) ?>">
                <span class="param-unit">F</span>
            </div>
        </div>
        <div class="param-row" style="border-bottom:none;border-right:1px solid var(--border);padding:0 22px">
            <div>
                <div class="param-label">Base jours mensuels</div>
                <div class="param-sub">Pour le salaire journalier</div>
                <span class="official-badge">Standard : 30 j</span>
            </div>
            <div class="param-input-wrap">
                <input type="number" name="conges_base_jours" class="param-input" step="1" min="1" max="31" value="<?= (int)($params['conges_base_jours'] ?? 30) ?>">
                <span class="param-unit">j</span>
            </div>
        </div>
        <div class="param-row" style="border-bottom:none;padding-left:22px">
            <div>
                <div class="param-label">Droits congés annuels</div>
                <div class="param-sub">1,5 j/mois (Code du travail SN)</div>
                <span class="official-badge">Légal : 18 j/an</span>
            </div>
            <div class="param-input-wrap">
                <input type="number" name="conges_droits_annuels" class="param-input" step="0.5" min="0" value="<?= number_format((float)($params['conges_droits_annuels'] ?? 18), 1) ?>">
                <span class="param-unit">j</span>
            </div>
        </div>
        <div class="param-row" style="border-bottom:none;border-top:1px solid var(--border);border-right:1px solid var(--border);padding:18px 22px 0 0">
            <div>
                <div class="param-label">Taux indemnisation maladie</div>
                <div class="param-sub">100% = maintenu · 50% = moitié</div>
                <span class="official-badge">Selon convention</span>
            </div>
            <div class="param-input-wrap">
                <input type="number" name="conges_taux_maladie" class="param-input" step="0.5" min="0" max="100" value="<?= number_format((float)($params['conges_taux_maladie'] ?? 100), 2) ?>">
                <span class="param-unit">%</span>
            </div>
        </div>
        <div class="param-row" style="border-bottom:none;border-top:1px solid var(--border);border-right:1px solid var(--border);padding:18px 22px 0 0">
            <div>
                <div class="param-label">Transport exonéré (plafond)</div>
                <div class="param-sub">Indemnité transport exonérée d'IR jusqu'à ce plafond/mois</div>
                <span class="official-badge">Exonéré : 26 000 F</span>
            </div>
            <div class="param-input-wrap">
                <input type="number" name="transport_exonere_plafond" class="param-input" style="width:110px" step="1" min="0" value="<?= (int)($params['transport_exonere_plafond'] ?? 26000) ?>">
                <span class="param-unit">F</span>
            </div>
        </div>
        <div class="param-row" style="border-bottom:none;border-top:1px solid var(--border);padding:18px 0 0 22px">
            <div>
                <div class="param-label">Mode de calcul BRS</div>
                <div class="param-sub">Contribution Représentative</div>
            </div>
            <div class="param-input-wrap">
                <?php $brs = $params['brs_mode'] ?? 'desactive'; ?>
                <select name="brs_mode" class="param-input" style="width:140px">
                    <option value="desactive" <?= $brs==='desactive'?'selected':'' ?>>Désactivé</option>
                    <option value="auto" <?= $brs==='auto'?'selected':'' ?>>Auto (par tranches)</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- BARÈME TRIMF ÉDITABLE -->
<div class="params-card" style="margin-top:18px">
    <div class="params-card-head">📋 Barème TRIMF — DGID Sénégal (personnalisable)</div>
    <div style="padding:14px 22px">
        <p style="font-size:13px;color:#667;margin:0 0 14px">Le TRIMF est calculé par tranches de salaire brut <strong>annuel</strong>. Le montant est la retenue <strong>annuelle</strong> (mensualisée à /12). CGI Art. 282.</p>
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="text-align:left;color:#667;border-bottom:1px solid var(--border)">
                    <th style="padding:8px">Brut annuel min (F)</th>
                    <th style="padding:8px">Brut annuel max (F)</th>
                    <th style="padding:8px">Montant TRIMF/an (F)</th>
                    <th style="padding:8px">Libellé</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($params['bareme_trimf_rows'] ?? []) as $t): ?>
                <tr>
                    <td style="padding:5px 8px"><input type="number" name="trimf_min[]" value="<?= (int)$t['min'] ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px"></td>
                    <td style="padding:5px 8px"><input type="number" name="trimf_max[]" value="<?= (int)$t['max'] ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px"></td>
                    <td style="padding:5px 8px"><input type="number" name="trimf_montant[]" value="<?= (int)$t['montant'] ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px"></td>
                    <td style="padding:5px 8px"><input type="text" name="trimf_libelle[]" value="<?= e($t['libelle'] ?? '') ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px;color:#888"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- BARÈME IR ÉDITABLE -->
<div class="params-card" style="margin-top:18px">
    <div class="params-card-head">📊 Barème IR progressif — Sénégal (personnalisable)</div>
    <div style="padding:14px 22px">
        <p style="font-size:13px;color:#667;margin:0 0 14px">L'IR est calculé sur le revenu <strong>annuel imposable par part</strong> (après abattement 30% et quotient familial). Taux <strong>marginaux</strong> par tranche. CGI Art. 163.</p>
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="text-align:left;color:#667;border-bottom:1px solid var(--border)">
                    <th style="padding:8px">Revenu annuel min (F)</th>
                    <th style="padding:8px">Revenu annuel max (F)</th>
                    <th style="padding:8px">Taux (%)</th>
                    <th style="padding:8px">Libellé</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($params['bareme_ir_rows'] ?? []) as $t): ?>
                <tr>
                    <td style="padding:5px 8px"><input type="number" name="ir_min[]" value="<?= (int)$t['min'] ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px"></td>
                    <td style="padding:5px 8px"><input type="number" name="ir_max[]" value="<?= (int)$t['max'] ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px"></td>
                    <td style="padding:5px 8px"><input type="number" name="ir_taux[]" value="<?= (float)$t['taux'] ?>" step="0.5" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px"></td>
                    <td style="padding:5px 8px"><input type="text" name="ir_libelle[]" value="<?= e($t['libelle'] ?? '') ?>" style="width:100%;padding:7px;border:1px solid var(--border);border-radius:6px;color:#888"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="params-footer">
    <a href="<?= APP_URL ?>/dossier/rh?id=<?= $entreprise['id'] ?>" class="btn btn-outline">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        Enregistrer les paramètres
    </button>
</div>

</form>
