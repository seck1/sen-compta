<div class="page-header">
    <div>
        <div class="page-title">Paramètres des congés</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?></div>
    </div>
    <a href="<?= APP_URL ?>/dossier/rh/conges?id=<?= $entreprise['id'] ?>" class="btn btn-secondary">← Retour</a>
</div>

<?php if(isset($_GET['ok'])): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:16px">Paramètres enregistrés.</div>
<?php endif; ?>

<div style="max-width:600px">
    <form method="post" action="<?= APP_URL ?>/dossier/rh/conges/parametres/store">
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

        <!-- Acquisition -->
        <div class="card" style="margin-bottom:20px;padding:24px">
            <div style="font-weight:700;font-size:18px;color:var(--navy-dark);margin-bottom:4px">Acquisition des congés</div>
            <div style="font-size:15px;color:var(--text-muted);margin-bottom:20px">Règle légale au Sénégal : 1,5 j/mois (18j/an). Convention collective : 2 j/mois (24j/an).</div>

            <div style="margin-bottom:16px">
                <label style="font-size:15px;font-weight:700;color:#333;display:block;margin-bottom:6px">Jours acquis par mois travaillé</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <?php foreach([1.5=>'1,5 j/mois (légal — 18j/an)', 2.0=>'2 j/mois (convention — 24j/an)', 2.5=>'2,5 j/mois (30j/an)'] as $val => $label): ?>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1.5px solid <?= abs($val - (float)$params_conges['jours_par_mois']) < 0.01 ? '#1e3a5f' : '#ddd' ?>;border-radius:8px;cursor:pointer;font-size:16px;background:<?= abs($val - (float)$params_conges['jours_par_mois']) < 0.01 ? '#f0f4f8' : '#fff' ?>">
                        <input type="radio" name="jours_par_mois" value="<?= $val ?>" <?= abs($val - (float)$params_conges['jours_par_mois']) < 0.01 ? 'checked' : '' ?>>
                        <?= $label ?>
                    </label>
                    <?php endforeach; ?>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;cursor:pointer;font-size:16px">
                        <input type="radio" name="jours_par_mois" value="custom" id="radio_custom">
                        Personnalisé :
                        <input type="number" id="custom_val" step="0.5" min="0.5" max="5" placeholder="ex: 1.75"
                               style="width:80px;padding:4px 8px;border:1px solid #ccc;border-radius:5px;font-size:16px"
                               onchange="document.getElementById('radio_custom').checked=true;this.name='jours_par_mois'">
                    </label>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0">
                <input type="checkbox" name="calcul_automatique" id="calcul_auto" value="1" <?= $params_conges['calcul_automatique'] ? 'checked' : '' ?> style="width:16px;height:16px">
                <label for="calcul_auto" style="font-size:16px;font-weight:600;color:#166534;cursor:pointer">
                    Calcul automatique des soldes (prorata date d'embauche)
                </label>
            </div>
            <div style="font-size:17px;color:var(--text-muted);margin-top:6px;padding-left:2px">Si activé, les soldes annuels sont calculés automatiquement selon la date d'embauche de chaque employé. Vous pouvez toujours les ajuster manuellement.</div>
        </div>

        <!-- Report N-1 -->
        <div class="card" style="margin-bottom:20px;padding:24px">
            <div style="font-weight:700;font-size:18px;color:var(--navy-dark);margin-bottom:4px">Report des congés N-1</div>
            <div style="font-size:15px;color:var(--text-muted);margin-bottom:20px">Jours non pris de l'année précédente pouvant être reportés sur l'année en cours.</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div>
                    <label style="font-size:15px;font-weight:700;color:#333;display:block;margin-bottom:6px">Plafond de report (jours)</label>
                    <input type="number" name="plafond_report_n1" value="<?= $params_conges['plafond_report_n1'] ?>" min="0" max="60" step="0.5"
                           style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:17px;font-weight:600;color:#1a1a1a">
                    <div style="font-size:17px;color:var(--text-muted);margin-top:4px">0 = aucun report autorisé</div>
                </div>
                <div>
                    <label style="font-size:15px;font-weight:700;color:#333;display:block;margin-bottom:6px">Expiration du report (mois)</label>
                    <select name="expiration_report_mois" style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:16px;color:#1a1a1a">
                        <?php
                        $mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
                        for($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $m==$params_conges['expiration_report_mois']?'selected':'' ?>><?= $mois_noms[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                    <div style="font-size:17px;color:var(--text-muted);margin-top:4px">Les jours reportés expirent à la fin de ce mois</div>
                </div>
            </div>
        </div>

        <!-- Récapitulatif règle -->
        <div style="background:#f0f4f8;border:1px solid #c5d0de;border-radius:10px;padding:16px 20px;margin-bottom:24px">
            <div style="font-size:15px;font-weight:700;color:#1e3a5f;margin-bottom:8px">📋 Résumé de la règle configurée</div>
            <div style="font-size:15px;color:#333;line-height:1.8">
                Chaque employé acquiert <strong><?= $params_conges['jours_par_mois'] ?> jour(s)/mois</strong> travaillé,
                soit <strong><?= $params_conges['jours_par_mois'] * 12 ?> jours/an</strong> pour une année complète.<br>
                Les jours non pris de N-1 sont reportables jusqu'au <strong>mois de <?= $mois_noms[$params_conges['expiration_report_mois']] ?></strong>
                dans la limite de <strong><?= $params_conges['plafond_report_n1'] ?> jours</strong>.
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
    </form>
</div>
