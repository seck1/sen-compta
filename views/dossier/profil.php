<?php
require_once APP_ROOT . '/src/Services/RegimeFiscalService.php';
$regime = $entreprise['regime_fiscal'] ?? 'CGI';
$regimeLabel = RegimeFiscalService::getLabel($regime);
$regimeColor = RegimeFiscalService::getBadgeColor($regime);

// Calcul score complétude profil
$score_fields = [
    // Identification
    'raison_sociale','ninea','rccm','forme_juridique','sigle','numero_contribuable',
    // Contacts
    'telephone','email','adresse','ville','pays','boite_postale',
    // Comptable
    'regime_fiscal','regime_tva','num_caisse_sociale','greffe','debut_exercice_social','fin_exercice_social',
    // Activité
    'secteur_activite','code_activite_naf','description_activite',
    // Dirigeant
    'dirigeant_nom','dirigeant_prenom','dirigeant_qualite',
    // Expert-comptable
    'expert_comptable_nom','expert_comptable_cabinet',
    // Signature & Banque
    'signataire_nom','signataire_qualite','banque_domiciliation','personne_contact',
];
$filled = array_filter($score_fields, fn($f) => !empty($entreprise[$f]));
$score = round(count($filled) / count($score_fields) * 100);
$score_color = $score >= 80 ? '#1f6e4e' : ($score >= 50 ? '#f59e0b' : '#ef4444');
?>

<style>
.profil-tabs { display:flex; gap:4px; margin-bottom:24px; background:#fff; border:1px solid var(--border); border-radius:12px; padding:5px; width:fit-content; }
.profil-tab { padding:10px 22px; border-radius:9px; font-size:16px; font-weight:500; cursor:pointer; border:none; background:none; color:var(--text-muted); transition:all .2s; }
.profil-tab.active { background:var(--navy); color:#fff; }
.profil-tab:hover:not(.active) { background:var(--bg); color:var(--text); }

.section-card { background:#fff; border:1px solid var(--border); border-radius:14px; margin-bottom:20px; overflow:hidden; }
.section-head { padding:18px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; cursor:pointer; user-select:none; }
.section-head-left { display:flex; align-items:center; gap:14px; }
.section-icon { width:40px; height:40px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.section-title { font-size:17px; font-weight:600; color:var(--navy-dark); }
.section-subtitle { font-size:14px; color:var(--text-muted); margin-top:2px; }
.section-chevron { width:20px; height:20px; color:var(--text-muted); transition:transform .2s; }
.section-chevron.open { transform:rotate(180deg); }
.section-body { padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.section-body.cols1 { grid-template-columns:1fr; }
.section-body.cols3 { grid-template-columns:1fr 1fr 1fr; }
.section-body.hidden { display:none; }

.form-field { display:flex; flex-direction:column; gap:6px; }
.form-field label { font-size:14px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; }
.form-field label .req { color:var(--danger); margin-left:2px; }
.form-field input, .form-field select, .form-field textarea {
    padding:11px 14px; border:1.5px solid var(--border); border-radius:9px;
    font-size:16px; font-family:'DM Sans',sans-serif; color:var(--text);
    background:#fff; transition:border-color .15s;
    width:100%;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    outline:none; border-color:var(--green); box-shadow:0 0 0 3px rgba(31,110,78,0.16);
}
.form-field textarea { resize:vertical; min-height:90px; }
.form-field .hint { font-size:13px; color:var(--text-muted); margin-top:2px; }
.full-col { grid-column:1/-1; }

.score-ring { position:relative; width:80px; height:80px; }
.score-ring svg { transform:rotate(-90deg); }
.score-ring .score-val { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:700; color:var(--navy-dark); }

.profil-hero { background:linear-gradient(135deg, var(--navy-dark), var(--navy-light)); border-radius:14px; padding:28px; margin-bottom:20px; display:flex; align-items:center; gap:24px; color:#fff; }
.profil-hero-avatar { width:80px; height:80px; border-radius:18px; background:var(--ent-color); display:flex; align-items:center; justify-content:center; font-size:30px; font-weight:700; color:#fff; flex-shrink:0; box-shadow:0 4px 16px rgba(0,0,0,.3); }
.profil-hero-info { flex:1; }
.profil-hero-name { font-family:'Cormorant Garamond',serif; font-size:28px; font-weight:400; margin-bottom:4px; }
.profil-hero-meta { font-size:15px; opacity:.7; display:flex; gap:16px; flex-wrap:wrap; margin-top:6px; }
.profil-hero-meta span { display:flex; align-items:center; gap:5px; }
.regime-badge-lg { display:inline-flex; align-items:center; gap:6px; padding:6px 15px; border-radius:20px; font-size:14px; font-weight:700; letter-spacing:.5px; margin-top:8px; }

@media(max-width:800px) {
    .section-body, .section-body.cols3, .section-body.cols1 { grid-template-columns:1fr; }
}
@media(max-width:700px) {
    .activite-r2-row { grid-template-columns:1fr !important; }
}
</style>

<!-- Bouton conformité -->
<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <a href="<?= APP_URL ?>/dossier/profil/conformite-dgid?id=<?= $entreprise['id'] ?>"
       style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#1e3a5f,#2a5080);color:#fff;padding:12px 22px;border-radius:10px;font-size:16px;font-weight:600;text-decoration:none;box-shadow:0 2px 10px rgba(30,58,95,.2)">
        📋 Rapport de Conformité DGID
        <span style="background:<?= $score_color ?>;color:#fff;border-radius:20px;padding:3px 10px;font-size:14px;font-weight:700"><?= $score ?>%</span>
    </a>
</div>

<?php if($saved): ?>
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    Profil enregistré avec succès. Les états financiers utilisent maintenant ces informations.
</div>
<?php endif; ?>

<!-- Hero -->
<div class="profil-hero">
    <div class="profil-hero-avatar" style="position:relative;overflow:visible;cursor:pointer" onclick="document.getElementById('logo-upload-input').click()" title="Cliquer pour changer le logo">
        <?php if(!empty($entreprise['logo'])): ?>
            <img src="<?= APP_URL ?>/logos/<?= e($entreprise['logo']) ?>" alt="Logo" style="width:72px;height:72px;border-radius:18px;object-fit:contain;background:#fff;padding:4px">
        <?php else: ?>
            <?= strtoupper(substr($entreprise['raison_sociale'], 0, 2)) ?>
        <?php endif; ?>
        <div style="position:absolute;bottom:-6px;right:-6px;width:22px;height:22px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.3)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#fff" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
        </div>
    </div>
    <div class="profil-hero-info">
        <div class="profil-hero-name"><?= e($entreprise['raison_sociale']) ?></div>
        <div>
            <span class="regime-badge-lg" style="background:<?= $regimeColor ?>22;color:<?= $regimeColor ?>;border:1px solid <?= $regimeColor ?>44">
                <?= e($regimeLabel) ?>
            </span>
        </div>
        <div class="profil-hero-meta">
            <?php if($entreprise['ninea']): ?><span>🔢 NINEA : <?= e($entreprise['ninea']) ?></span><?php endif; ?>
            <?php if($entreprise['rccm']): ?><span>📋 RCCM : <?= e($entreprise['rccm']) ?></span><?php endif; ?>
            <?php if($entreprise['forme_juridique']): ?><span>🏢 <?= e($entreprise['forme_juridique']) ?></span><?php endif; ?>
            <?php if($entreprise['ville']): ?><span>📍 <?= e($entreprise['ville']) ?></span><?php endif; ?>
        </div>
    </div>
    <!-- Score -->
    <div style="text-align:center;flex-shrink:0">
        <div style="font-size:13px;color:rgba(255,255,255,.6);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">Complétude profil</div>
        <div style="position:relative;width:80px;height:80px;margin:0 auto">
            <svg width="80" height="80" style="transform:rotate(-90deg)">
                <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="8"/>
                <circle cx="40" cy="40" r="32" fill="none" stroke="<?= $score_color ?>" stroke-width="8"
                    stroke-dasharray="<?= round(2 * M_PI * 32 * $score / 100) ?> 201"
                    stroke-linecap="round"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff"><?= $score ?>%</div>
        </div>
        <div style="font-size:13px;color:rgba(255,255,255,.5);margin-top:6px"><?= count($filled) ?>/<?= count($score_fields) ?> champs</div>
    </div>
</div>

<form method="POST" action="<?= APP_URL ?>/dossier/profil/store" enctype="multipart/form-data" id="profil-form">
<input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
<input type="file" id="logo-upload-input" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display:none" onchange="document.getElementById('profil-form').submit()">

<!-- 1. INFOS GÉNÉRALES -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#eff6ff">🏢</div>
            <div>
                <div class="section-title">Données Additionnelles DGID</div>
                <div class="section-subtitle">Forme juridique, N° fiscal, registre commerce, exercice social</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div class="form-field">
            <label>Raison sociale <span class="req">*</span></label>
            <input type="text" name="raison_sociale" value="<?= e($entreprise['raison_sociale']) ?>" required>
        </div>
        <div class="form-field">
            <label>Sigle usuel</label>
            <input type="text" name="sigle" value="<?= e($entreprise['sigle'] ?? '') ?>" placeholder="ex: SMC">
            <span class="hint">Abréviation ou sigle de l'entreprise (fiche R1 DGID)</span>
        </div>
        <div class="form-field">
            <label>Forme juridique</label>
            <select name="forme_juridique">
                <?php foreach(['SA','SARL','SAS','SUARL','SNC','SCS','GIE','EI','Coopérative','Association','ONG','Autre'] as $fj): ?>
                <option value="<?= $fj ?>" <?= ($entreprise['forme_juridique']==$fj)?'selected':'' ?>><?= $fj ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label>NINEA (Numéro Identifiant Entreprise)</label>
            <input type="text" name="ninea" value="<?= e($entreprise['ninea'] ?? '') ?>" placeholder="ex: 00123456 7Z1">
        </div>
        <div class="form-field">
            <label>RCCM (Registre Commerce)</label>
            <input type="text" name="rccm" value="<?= e($entreprise['rccm'] ?? '') ?>" placeholder="ex: SN-DKR-2020-B-12345">
        </div>
        <div class="form-field">
            <label>Numéro Fiscal Entreprise (NIF)</label>
            <input type="text" name="numero_contribuable" value="<?= e($entreprise['numero_contribuable'] ?? '') ?>" placeholder="ex: NIF-XXXXXX">
        </div>
        <div class="form-field">
            <label>N° Code Importateur</label>
            <input type="text" name="code_importateur" value="<?= e($entreprise['code_importateur'] ?? '') ?>" placeholder="ex: IMP-123456">
            <span class="hint">N° code importateur (Fiche R1 ZE)</span>
        </div>
        <div class="form-field">
            <label>Boîte Postale</label>
            <input type="text" name="boite_postale" value="<?= e($entreprise['boite_postale'] ?? '') ?>" placeholder="ex: BP 1234">
        </div>
        <div class="form-field">
            <label>Nombre d'employés</label>
            <input type="number" name="nombre_employes" value="<?= (int)($entreprise['nombre_employes'] ?? 0) ?>" min="0">
        </div>
        <div class="form-field">
            <label>Début exercice social</label>
            <input type="date" name="debut_exercice_social" value="<?= e($entreprise['debut_exercice_social'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label>Fin exercice social</label>
            <input type="date" name="fin_exercice_social" value="<?= e($entreprise['fin_exercice_social'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label>Affectation du résultat</label>
            <select name="affectation_resultat">
                <option value="">-- Sélectionner --</option>
                <?php foreach(['report_nouveau'=>'Report à nouveau','dividendes'=>'Distribution dividendes','reserve_legale'=>'Réserve légale','reserve_statutaire'=>'Réserve statutaire','mixte'=>'Mixte'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($entreprise['affectation_resultat']==$v)?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label>Site web</label>
            <input type="url" name="site_web" value="<?= e($entreprise['site_web'] ?? '') ?>" placeholder="https://www.example.com">
        </div>
        <div class="form-field">
            <label>Auditeur externe</label>
            <input type="text" name="auditeur_externe" value="<?= e($entreprise['auditeur_externe'] ?? '') ?>" placeholder="ex: Jean Dupont">
        </div>
        <div class="form-field">
            <label>Cabinet d'audit / Conseil juridique</label>
            <input type="text" name="cabinet_audit_juridique" value="<?= e($entreprise['cabinet_audit_juridique'] ?? '') ?>" placeholder="ex: Cabinet Conseil Juridique">
        </div>
    </div>
</div>

<!-- 2. PARAMÈTRES COMPTABLES & DGID -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#fef9c3">📊</div>
            <div>
                <div class="section-title">Paramètres Comptables & DGID</div>
                <div class="section-subtitle">Régime fiscal, TVA, caisse sociale, greffe</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div class="form-field">
            <label>Régime fiscal <span class="req">*</span></label>
            <select name="regime_fiscal">
                <option value="CGI" <?= ($regime=='CGI')?'selected':'' ?>>Régime Réel Normal (CGI) — CA ≥ 100M FCFA</option>
                <option value="CGU" <?= ($regime=='CGU')?'selected':'' ?>>Contribution Globale Unique (CGU) — CA 15M–100M FCFA</option>
                <option value="RNS" <?= ($regime=='RNS')?'selected':'' ?>>Régime Non-Salarié / BNC — Professions libérales</option>
                <option value="MICRO" <?= ($regime=='MICRO')?'selected':'' ?>>Micro-entreprise / Impôt Libératoire — CA < 15M FCFA</option>
                <option value="EXONERE" <?= ($regime=='EXONERE')?'selected':'' ?>>Régime Exonéré / Zone Franche (APIX)</option>
            </select>
        </div>
        <div class="form-field">
            <label>Régime TVA</label>
            <select name="regime_tva">
                <option value="mensuel" <?= (($entreprise['regime_tva']??'')=='mensuel')?'selected':'' ?>>Mensuel</option>
                <option value="trimestriel" <?= (($entreprise['regime_tva']??'')=='trimestriel')?'selected':'' ?>>Trimestriel</option>
                <option value="annuel" <?= (($entreprise['regime_tva']??'')=='annuel')?'selected':'' ?>>Annuel</option>
                <option value="non_assujetti" <?= (($entreprise['regime_tva']??'')=='non_assujetti')?'selected':'' ?>>Non assujetti</option>
            </select>
        </div>
        <div class="form-field">
            <label>CA annuel estimé (FCFA)</label>
            <input type="number" name="ca_annuel_estime" value="<?= (float)($entreprise['ca_annuel_estime'] ?? 0) ?>" step="1000000" min="0" placeholder="ex: 25000000">
        </div>
        <div class="form-field">
            <label>Numéro de Caisse Sociale</label>
            <input type="text" name="num_caisse_sociale" value="<?= e($entreprise['num_caisse_sociale'] ?? '') ?>" placeholder="ex: CNSS-123456">
        </div>
        <div class="form-field">
            <label>Greffe (Tribunal de Commerce)</label>
            <input type="text" name="greffe" value="<?= e($entreprise['greffe'] ?? '') ?>" placeholder="ex: Greffe de Dakar">
        </div>
        <div class="form-field">
            <label>Année de première clôture d'exercice</label>
            <input type="number" name="annee_premiere_cloture" value="<?= e($entreprise['annee_premiere_cloture'] ?? '') ?>" min="1990" max="2050" placeholder="ex: 2020">
        </div>
        <?php if($regime === 'EXONERE'): ?>
        <div class="form-field">
            <label>Début exonération</label>
            <input type="date" name="date_debut_exoneration" value="<?= e($entreprise['date_debut_exoneration'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label>Fin exonération</label>
            <input type="date" name="date_fin_exoneration" value="<?= e($entreprise['date_fin_exoneration'] ?? '') ?>">
        </div>
        <?php else: ?>
        <input type="hidden" name="date_debut_exoneration" value="">
        <input type="hidden" name="date_fin_exoneration" value="">
        <?php endif; ?>
    </div>
</div>

<!-- 3. DIRIGEANTS -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#f0fdf4">👔</div>
            <div>
                <div class="section-title">Dirigeants & Conseil</div>
                <div class="section-subtitle">Informations du dirigeant principal</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div class="form-field">
            <label>Nom du dirigeant</label>
            <input type="text" name="dirigeant_nom" value="<?= e($entreprise['dirigeant_nom'] ?? '') ?>" placeholder="ex: Dupont">
        </div>
        <div class="form-field">
            <label>Prénom du dirigeant</label>
            <input type="text" name="dirigeant_prenom" value="<?= e($entreprise['dirigeant_prenom'] ?? '') ?>" placeholder="ex: Jean">
        </div>
        <div class="form-field">
            <label>Qualité du dirigeant</label>
            <select name="dirigeant_qualite">
                <option value="">-- Sélectionner --</option>
                <?php foreach(['Gérant','PDG','DG','Directeur','Administrateur','Associé Gérant','Président'] as $q): ?>
                <option value="<?= $q ?>" <?= (($entreprise['dirigeant_qualite']??'')===$q)?'selected':'' ?>><?= $q ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label>Numéro fiscal dirigeant</label>
            <input type="text" name="dirigeant_num_fiscal" value="<?= e($entreprise['dirigeant_num_fiscal'] ?? '') ?>" placeholder="ex: NF-123456">
        </div>
        <div class="form-field full-col">
            <label>Adresse du dirigeant</label>
            <textarea name="dirigeant_adresse" placeholder="Adresse complète du dirigeant..."><?= e($entreprise['dirigeant_adresse'] ?? '') ?></textarea>
        </div>
    </div>
</div>

<!-- 4. PROFESSIONNELS COMPTABLES -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#fdf4ff">🏛️</div>
            <div>
                <div class="section-title">Professionnels Comptables</div>
                <div class="section-subtitle">Expert-comptable et commissaire aux comptes</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div style="grid-column:1/-1;font-size:12px;font-weight:600;color:var(--navy);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border);padding-bottom:8px">Expert-Comptable</div>
        <div class="form-field">
            <label>Nom</label>
            <input type="text" name="expert_comptable_nom" value="<?= e($entreprise['expert_comptable_nom'] ?? '') ?>" placeholder="ex: Martin">
        </div>
        <div class="form-field">
            <label>Cabinet</label>
            <input type="text" name="expert_comptable_cabinet" value="<?= e($entreprise['expert_comptable_cabinet'] ?? '') ?>" placeholder="ex: SenCompta">
        </div>
        <div class="form-field">
            <label>Téléphone</label>
            <input type="text" name="expert_comptable_telephone" value="<?= e($entreprise['expert_comptable_telephone'] ?? '') ?>" placeholder="+221-XX-XXX-XX-XX">
        </div>
        <div class="form-field">
            <label>Adresse du cabinet</label>
            <input type="text" name="expert_comptable_adresse" value="<?= e($entreprise['expert_comptable_adresse'] ?? '') ?>" placeholder="Adresse du cabinet">
        </div>
        <div style="grid-column:1/-1;font-size:12px;font-weight:600;color:var(--navy);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border);padding-bottom:8px;margin-top:8px">Commissaire aux Comptes</div>
        <div class="form-field">
            <label>Nom</label>
            <input type="text" name="commissaire_nom" value="<?= e($entreprise['commissaire_nom'] ?? '') ?>" placeholder="ex: Durand">
        </div>
        <div class="form-field">
            <label>Adresse</label>
            <input type="text" name="commissaire_adresse" value="<?= e($entreprise['commissaire_adresse'] ?? '') ?>" placeholder="Adresse complète">
        </div>
        <div class="form-field full-col">
            <label>Infos complémentaires (email, téléphone, site web…)</label>
            <textarea name="commissaire_infos" placeholder="Email, téléphone, site web..."><?= e($entreprise['commissaire_infos'] ?? '') ?></textarea>
        </div>
    </div>
</div>

<!-- 5. ACTIVITÉ -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#fff7ed">🏭</div>
            <div>
                <div class="section-title">Description de l'Activité</div>
                <div class="section-subtitle">Secteur, code NAF/NACE, description principale</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div class="form-field">
            <label>Secteur d'activité</label>
            <select name="secteur_activite">
                <?php foreach(['Commerce','Industrie','Services','Agriculture','BTP','Transport','Finance','Santé','Education','Autre'] as $s): ?>
                <option value="<?= strtolower($s) ?>" <?= (strtolower($entreprise['secteur_activite']??'')==strtolower($s))?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label>Code Activité (NAF/NACE) <span class="req">*</span></label>
            <input type="text" name="code_activite_naf" value="<?= e($entreprise['code_activite_naf'] ?? '') ?>" placeholder="ex: 4711Z">
        </div>
        <div class="form-field">
            <label>Secteur détail</label>
            <input type="text" name="secteur_activite_detail" value="<?= e($entreprise['secteur_activite_detail'] ?? '') ?>" placeholder="ex: Commerce de détail alimentaire">
        </div>
        <div class="form-field full-col">
            <label>Description de l'activité principale <span class="req">*</span></label>
            <textarea name="description_activite" placeholder="Décrivez l'activité principale de votre entreprise..."><?= e($entreprise['description_activite'] ?? '') ?></textarea>
        </div>
    </div>
</div>

<!-- 6. CONTACTS -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#f0f9ff">📧</div>
            <div>
                <div class="section-title">Contacts & Informations</div>
                <div class="section-subtitle">Email, téléphone, adresse, ville, pays</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div class="form-field">
            <label>Email principal</label>
            <input type="email" name="email" value="<?= e($entreprise['email'] ?? '') ?>" placeholder="contact@entreprise.sn">
        </div>
        <div class="form-field">
            <label>Email secondaire</label>
            <input type="email" name="email_secondaire" value="<?= e($entreprise['email_secondaire'] ?? '') ?>" placeholder="comptable@entreprise.sn">
        </div>
        <div class="form-field">
            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?= e($entreprise['telephone'] ?? '') ?>" placeholder="+221 77 000 00 00">
        </div>
        <div class="form-field">
            <label>Ville</label>
            <input type="text" name="ville" value="<?= e($entreprise['ville'] ?? '') ?>" placeholder="ex: Dakar">
        </div>
        <div class="form-field full-col">
            <label>Adresse complète</label>
            <textarea name="adresse" placeholder="Adresse complète de l'entreprise..."><?= e($entreprise['adresse'] ?? '') ?></textarea>
        </div>
        <div class="form-field">
            <label>Pays</label>
            <input type="text" name="pays" value="<?= e($entreprise['pays'] ?? 'Sénégal') ?>">
        </div>
    </div>
</div>

<!-- 7. ACTIVITÉS R2 -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#f0fdf4">🏭</div>
            <div>
                <div class="section-title">Activités de l'entreprise — Fiche R2</div>
                <div class="section-subtitle">Lister les activités par ordre décroissant de CA HT ou Valeur Ajoutée</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body cols1" style="padding-bottom:8px">
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">Ces données seront injectées automatiquement dans la feuille <strong>R2</strong> du fichier Excel DGID.</div>
        <div id="activites-r2-list">
            <?php
            $activitesAffichees = !empty($activitesR2) ? $activitesR2 : [
                ['designation'=>'','code_nomenclature'=>'','valeur_ajoutee'=>''],
                ['designation'=>'','code_nomenclature'=>'','valeur_ajoutee'=>''],
            ];
            foreach ($activitesAffichees as $i => $act): ?>
            <div class="activite-r2-row" style="display:grid;grid-template-columns:1fr 180px 180px auto;gap:10px;margin-bottom:10px;align-items:end">
                <div class="form-field" style="margin:0">
                    <?php if($i===0): ?><label>Désignation de l'activité</label><?php endif; ?>
                    <input type="text" form="form-activites-r2" name="designation[]" value="<?= e($act['designation']) ?>" placeholder="ex: Commerce de détail alimentaire">
                </div>
                <div class="form-field" style="margin:0">
                    <?php if($i===0): ?><label>Code nomenclature</label><?php endif; ?>
                    <input type="text" form="form-activites-r2" name="code_nomenclature[]" value="<?= e($act['code_nomenclature']) ?>" placeholder="ex: 4711Z" style="font-family:monospace">
                </div>
                <div class="form-field" style="margin:0">
                    <?php if($i===0): ?><label>Valeur Ajoutée HT (FCFA)</label><?php endif; ?>
                    <input type="number" form="form-activites-r2" name="valeur_ajoutee[]" value="<?= $act['valeur_ajoutee'] ?>" placeholder="0" step="1000" min="0">
                </div>
                <div style="<?= $i===0 ? 'margin-top:22px' : '' ?>">
                    <button type="button" onclick="supprimerActivite(this)" style="background:none;border:1.5px solid #fca5a5;color:#ef4444;border-radius:8px;width:36px;height:36px;cursor:pointer;display:flex;align-items:center;justify-content:center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="ajouterActivite()" style="display:inline-flex;align-items:center;gap:7px;background:none;border:1.5px dashed var(--border);color:var(--text-muted);border-radius:9px;padding:8px 16px;font-size:13px;cursor:pointer;margin-top:4px;transition:all .2s" onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Ajouter une activité
        </button>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
            <button type="submit" form="form-activites-r2" class="btn btn-primary" style="padding:10px 22px;font-size:13px">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Enregistrer les activités
            </button>
        </div>
    </div>
</div>
<!-- 8. SIGNATURE & BANQUE -->
<div class="section-card">
    <div class="section-head" onclick="toggle(this)">
        <div class="section-head-left">
            <div class="section-icon" style="background:#f0fdf4">✍️</div>
            <div>
                <div class="section-title">Signature & Domiciliation Bancaire</div>
                <div class="section-subtitle">Signataire des états financiers, personne à contacter, banque (Fiche R1 DGID)</div>
            </div>
        </div>
        <svg class="section-chevron open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
    </div>
    <div class="section-body">
        <div class="form-field">
            <label>Nom du signataire des états financiers</label>
            <input type="text" name="signataire_nom" value="<?= e($entreprise['signataire_nom'] ?? '') ?>" placeholder="ex: Mor Seck">
        </div>
        <div class="form-field">
            <label>Qualité du signataire</label>
            <input type="text" name="signataire_qualite" value="<?= e($entreprise['signataire_qualite'] ?? '') ?>" placeholder="ex: Gérant, DG, PDG...">
        </div>
        <div class="form-field full-col">
            <label>Personne à contacter (nom, adresse, qualité)</label>
            <textarea name="personne_contact" placeholder="Nom, adresse et qualité de la personne à contacter en cas de demande d'informations complémentaires..."><?= e($entreprise['personne_contact'] ?? '') ?></textarea>
            <span class="hint">Fiche R1 DGID — ZI</span>
        </div>
        <div style="grid-column:1/-1;font-size:12px;font-weight:600;color:var(--navy);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border);padding-bottom:8px;margin-top:4px">Domiciliation Bancaire</div>
        <div class="form-field">
            <label>Banque</label>
            <input type="text" name="banque_domiciliation" value="<?= e($entreprise['banque_domiciliation'] ?? '') ?>" placeholder="ex: CBAO, UBA, SGBS...">
        </div>
        <div class="form-field">
            <label>Numéro de compte bancaire</label>
            <input type="text" name="numero_compte_bancaire" value="<?= e($entreprise['numero_compte_bancaire'] ?? '') ?>" placeholder="ex: SN28 0100 1234 5678 9012 3456">
        </div>
    </div>
</div>

<!-- Footer actions -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding:16px 0">
    <a href="<?= APP_URL ?>/dossier?id=<?= $entreprise['id'] ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
        Retour
    </a>
    <button type="submit" class="btn btn-primary" style="padding:12px 28px;font-size:14px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        Enregistrer
    </button>
</div>

</form>

<!-- Form activités R2 — hors du form principal pour éviter les forms imbriqués -->
<form id="form-activites-r2" method="POST" action="<?= APP_URL ?>/dossier/profil/activites">
    <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
</form>

<script>
function toggle(head) {
    const body = head.nextElementSibling;
    const chevron = head.querySelector('.section-chevron');
    body.classList.toggle('hidden');
    chevron.classList.toggle('open');
}

function creerInputActivite(name, placeholder, extra) {
    const wrap = document.createElement('div');
    wrap.className = 'form-field';
    wrap.style.margin = '0';
    const input = document.createElement('input');
    input.setAttribute('form', 'form-activites-r2');
    input.name = name;
    input.placeholder = placeholder;
    if (extra) Object.assign(input, extra);
    wrap.appendChild(input);
    return wrap;
}

function ajouterActivite() {
    const list = document.getElementById('activites-r2-list');
    const row = document.createElement('div');
    row.className = 'activite-r2-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 180px 180px auto;gap:10px;margin-bottom:10px;align-items:end';

    row.appendChild(creerInputActivite('designation[]', 'ex: Commerce de détail alimentaire', {type:'text'}));
    row.appendChild(creerInputActivite('code_nomenclature[]', 'ex: 4711Z', {type:'text', style:'font-family:monospace'}));
    row.appendChild(creerInputActivite('valeur_ajoutee[]', '0', {type:'number', step:'1000', min:'0'}));

    const btnWrap = document.createElement('div');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.onclick = () => supprimerActivite(btn);
    btn.style.cssText = 'background:none;border:1.5px solid #fca5a5;color:#ef4444;border-radius:8px;width:36px;height:36px;cursor:pointer;display:flex;align-items:center;justify-content:center';
    const svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svg.setAttribute('fill','none'); svg.setAttribute('viewBox','0 0 24 24');
    svg.setAttribute('stroke-width','2'); svg.setAttribute('stroke','currentColor');
    svg.style.cssText = 'width:16px;height:16px';
    const path = document.createElementNS('http://www.w3.org/2000/svg','path');
    path.setAttribute('stroke-linecap','round'); path.setAttribute('stroke-linejoin','round');
    path.setAttribute('d','M6 18L18 6M6 6l12 12');
    svg.appendChild(path); btn.appendChild(svg); btnWrap.appendChild(btn);
    row.appendChild(btnWrap);

    list.appendChild(row);
}

function supprimerActivite(btn) {
    const rows = document.querySelectorAll('.activite-r2-row');
    if (rows.length <= 1) return;
    btn.closest('.activite-r2-row').remove();
}
</script>
