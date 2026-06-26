<?php
$edit = !empty($employe);
$action = $edit
    ? APP_URL . '/dossier/rh/update'
    : APP_URL . '/dossier/rh/store';
$v = fn($k, $d='') => e($employe[$k] ?? $d);
$sel = fn($k, $val) => ($employe[$k] ?? '') === $val ? 'selected' : '';
?>
<div class="page-header">
    <div>
        <div class="page-title"><?= $edit ? 'Modifier employé' : 'Nouvel employé' ?></div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?></div>
    </div>
    <a href="<?= APP_URL ?>/dossier/rh?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">← Retour</a>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" style="max-width:960px">
    <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
    <?php if($edit): ?>
    <input type="hidden" name="employe_id" value="<?= $employe['id'] ?>">
    <?php endif; ?>

    <!-- IDENTITÉ -->
    <div class="card" style="margin-bottom:20px;padding:24px">
        <div style="font-size:14px;font-weight:800;color:var(--navy-dark);text-transform:uppercase;letter-spacing:.8px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #1e3a5f22;display:flex;align-items:center;gap:8px">
            <span style="width:24px;height:24px;background:#1e3a5f;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px">1</span>
            Identité
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div class="form-field">
                <label>Matricule *</label>
                <input type="text" name="matricule" value="<?= $v('matricule') ?>" required placeholder="EMP-001">
            </div>
            <div class="form-field">
                <label>Nom *</label>
                <input type="text" name="nom" value="<?= $v('nom') ?>" required>
            </div>
            <div class="form-field">
                <label>Prénom *</label>
                <input type="text" name="prenom" value="<?= $v('prenom') ?>" required>
            </div>
            <div class="form-field">
                <label>Sexe</label>
                <select name="sexe">
                    <option value="M" <?= $sel('sexe','M') ?: ($edit ? '' : 'selected') ?>>Masculin</option>
                    <option value="F" <?= $sel('sexe','F') ?>>Féminin</option>
                </select>
            </div>
            <div class="form-field">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance" value="<?= $v('date_naissance') ?>">
            </div>
            <div class="form-field">
                <label>Lieu de naissance</label>
                <input type="text" name="lieu_naissance" value="<?= $v('lieu_naissance') ?>">
            </div>
            <div class="form-field">
                <label>Nationalité</label>
                <input type="text" name="nationalite" value="<?= $v('nationalite','Sénégalaise') ?>">
            </div>
            <div class="form-field">
                <label>N° CNI / Passeport</label>
                <input type="text" name="num_cni" value="<?= $v('num_cni') ?>" placeholder="1234567890123">
            </div>
            <div class="form-field">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= $v('telephone') ?>" placeholder="+221 77 000 00 00">
            </div>
            <div class="form-field">
                <label>Email professionnel</label>
                <input type="email" name="email" value="<?= $v('email') ?>">
            </div>
            <div class="form-field" style="grid-column:span 2">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= $v('adresse') ?>" placeholder="Dakar, Sénégal">
            </div>
            <div class="form-field">
                <label>Lieu de travail</label>
                <input type="text" name="lieu_travail" value="<?= $v('lieu_travail') ?>" placeholder="Siège / Agence...">
            </div>
        </div>

        <!-- Photo -->
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #eee">
            <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:8px">Photo</label>
            <div style="display:flex;align-items:center;gap:16px">
                <?php if($edit && !empty($employe['photo'])): ?>
                <img src="<?= APP_URL ?>/uploads/employes/<?= e($employe['photo']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #ddd">
                <?php else: ?>
                <div style="width:56px;height:56px;border-radius:50%;background:#f0f4f8;border:2px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:20px">👤</div>
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" style="font-size:14px">
            </div>
        </div>
    </div>

    <!-- CONTRAT -->
    <div class="card" style="margin-bottom:20px;padding:24px">
        <div style="font-size:14px;font-weight:800;color:var(--navy-dark);text-transform:uppercase;letter-spacing:.8px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #1e3a5f22;display:flex;align-items:center;gap:8px">
            <span style="width:24px;height:24px;background:#1e3a5f;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px">2</span>
            Contrat & Poste
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div class="form-field">
                <label>Poste / Fonction</label>
                <input type="text" name="poste" value="<?= $v('poste') ?>">
            </div>
            <div class="form-field">
                <label>Département / Service</label>
                <input type="text" name="departement" value="<?= $v('departement') ?>">
            </div>
            <div class="form-field">
                <label>Catégorie professionnelle</label>
                <input type="text" name="categorie" value="<?= $v('categorie') ?>" placeholder="Cadre, Agent de maîtrise...">
            </div>
            <div class="form-field">
                <label>Type de contrat</label>
                <select name="type_contrat" id="type_contrat" onchange="toggleCDD()">
                    <option value="CDI"    <?= $sel('type_contrat','CDI') ?: ($edit ? '' : 'selected') ?>>CDI</option>
                    <option value="CDD"    <?= $sel('type_contrat','CDD') ?>>CDD</option>
                    <option value="Stage"  <?= $sel('type_contrat','Stage') ?>>Stage</option>
                    <option value="Interim"<?= $sel('type_contrat','Interim') ?>>Intérim</option>
                </select>
            </div>
            <div class="form-field">
                <label>Date d'embauche *</label>
                <input type="date" name="date_embauche" value="<?= $v('date_embauche') ?>" required>
            </div>
            <div class="form-field" id="bloc_fin_contrat" style="display:<?= in_array($employe['type_contrat']??'CDI',['CDD','Stage','Interim'])?'block':'none' ?>">
                <label>Date de fin de contrat</label>
                <input type="date" name="date_fin_contrat" value="<?= $v('date_fin_contrat') ?>">
            </div>
            <div class="form-field">
                <label>Période d'essai (mois)</label>
                <input type="number" name="periode_essai_mois" value="<?= $v('periode_essai_mois','0') ?>" min="0" max="12" placeholder="0 = aucune">
            </div>
            <div class="form-field">
                <label>Statut</label>
                <select name="statut">
                    <option value="actif"    <?= $sel('statut','actif') ?: ($edit ? '' : 'selected') ?>>Actif</option>
                    <option value="suspendu" <?= $sel('statut','suspendu') ?>>Suspendu</option>
                    <option value="inactif"  <?= $sel('statut','inactif') ?>>Inactif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- RÉMUNÉRATION -->
    <div class="card" style="margin-bottom:20px;padding:24px">
        <div style="font-size:14px;font-weight:800;color:var(--navy-dark);text-transform:uppercase;letter-spacing:.8px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #1e3a5f22;display:flex;align-items:center;gap:8px">
            <span style="width:24px;height:24px;background:#1e3a5f;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px">3</span>
            Rémunération
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div class="form-field">
                <label>Salaire de base (FCFA) *</label>
                <input type="number" name="salaire_base" value="<?= $v('salaire_base','0') ?>" step="1" min="0" required>
            </div>
            <div class="form-field">
                <label>Sursalaire (FCFA)</label>
                <input type="number" name="sursalaire" value="<?= $v('sursalaire','0') ?>" step="1" min="0">
            </div>
            <div class="form-field">
                <label>Autres indemnités (FCFA)</label>
                <input type="number" name="autres_indemnites" value="<?= $v('autres_indemnites','0') ?>" step="1" min="0">
            </div>
            <div class="form-field">
                <label>Indemnité logement (FCFA)</label>
                <input type="number" name="indemnite_logement" value="<?= $v('indemnite_logement','0') ?>" step="1" min="0">
            </div>
            <div class="form-field">
                <label>Indemnité transport (FCFA)</label>
                <input type="number" name="indemnite_transport" value="<?= $v('indemnite_transport','0') ?>" step="1" min="0">
            </div>
            <div class="form-field">
                <label>Indemnité représentation (FCFA)</label>
                <input type="number" name="indemnite_representation" value="<?= $v('indemnite_representation','0') ?>" step="1" min="0">
            </div>
        </div>

        <!-- Résumé brut estimé -->
        <div id="resume_remuneration" style="margin-top:16px;padding:12px 16px;background:#f0f4f8;border-radius:8px;font-size:14px;color:#1e3a5f">
            Brut estimé : <strong id="brut_estime">—</strong>
        </div>
    </div>

    <!-- SITUATION FAMILIALE & PARTS FISCALES IR -->
    <div class="card" style="margin-bottom:20px;padding:24px">
        <div style="font-size:14px;font-weight:800;color:var(--navy-dark);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;padding-bottom:12px;border-bottom:2px solid #1e3a5f22;display:flex;align-items:center;gap:8px">
            <span style="width:24px;height:24px;background:#1e3a5f;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px">4</span>
            Situation familiale &amp; Parts fiscales IR
        </div>
        <p style="font-size:13px;color:#667;line-height:1.6;margin:0 0 18px">
            Les parts déterminent le quotient familial IR (<strong>CGI Sénégal, Art. 165</strong>). Plus de parts = IR plus faible = net plus élevé.<br>
            Célibataire = <strong>1 part</strong> · Marié = <strong>1,5 part</strong> · <strong>+0,5 par enfant</strong> · maximum <strong>5 parts</strong>.
        </p>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div class="form-field">
                <label>Situation familiale</label>
                <select name="situation_familiale" id="sf_situation" onchange="calcParts()">
                    <option value="celibataire" <?= $sel('situation_familiale','celibataire') ?>>Célibataire</option>
                    <option value="marie" <?= $sel('situation_familiale','marie') ?>>Marié(e)</option>
                    <option value="divorce" <?= $sel('situation_familiale','divorce') ?>>Divorcé(e)</option>
                    <option value="veuf" <?= $sel('situation_familiale','veuf') ?>>Veuf / Veuve</option>
                </select>
                <div style="font-size:13px;color:#888;margin-top:3px">Impacte le nombre de parts de base.</div>
            </div>
            <div class="form-field">
                <label>Nombre d'enfants à charge</label>
                <input type="number" name="nombre_enfants" id="sf_enfants" value="<?= $v('nombre_enfants','0') ?>" min="0" max="20" oninput="calcParts()">
                <div style="font-size:13px;color:#888;margin-top:3px">+0,5 part par enfant (plafond 5 parts).</div>
            </div>
            <div class="form-field">
                <label>Nombre de parts fiscales</label>
                <input type="number" name="nombre_parts" id="sf_parts" value="<?= $v('nombre_parts','1.0') ?>" step="0.5" min="1" max="5" placeholder="1.0">
                <div style="font-size:13px;color:#888;margin-top:3px">Calculé automatiquement ou ajustable.</div>
            </div>
        </div>
        <div id="sf_calc" style="background:#f3f6f4;border:1px solid #d9e3dd;border-radius:8px;padding:10px 14px;font-size:13px;color:#1f6e4e;line-height:1.6;margin-top:14px"></div>
    </div>

    <!-- FISCAL & SOCIAL -->
    <div class="card" style="margin-bottom:20px;padding:24px">
        <div style="font-size:14px;font-weight:800;color:var(--navy-dark);text-transform:uppercase;letter-spacing:.8px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #1e3a5f22;display:flex;align-items:center;gap:8px">
            <span style="width:24px;height:24px;background:#1e3a5f;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px">5</span>
            Fiscal & Organismes sociaux
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div class="form-field">
                <label>Régime fiscal</label>
                <select name="regime_fiscal">
                    <option value="imposable" <?= $sel('regime_fiscal','imposable') ?: ($edit ? '' : 'selected') ?>>Imposable (IR)</option>
                    <option value="exonere"   <?= $sel('regime_fiscal','exonere') ?>>Exonéré</option>
                </select>
            </div>
            <div class="form-field">
                <label>N° IPRES</label>
                <input type="text" name="num_ipres" value="<?= $v('num_ipres') ?>" placeholder="SN-IPRES-XXXXX">
            </div>
            <div class="form-field">
                <label>N° CSS</label>
                <input type="text" name="num_css" value="<?= $v('num_css') ?>" placeholder="CSS-XXXXX">
            </div>
            <div class="form-field">
                <label>N° IPM</label>
                <input type="text" name="num_ipm" value="<?= $v('num_ipm') ?>" placeholder="IPM-XXXXX">
            </div>
        </div>
    </div>

    <!-- PAIEMENT -->
    <div class="card" style="margin-bottom:24px;padding:24px">
        <div style="font-size:14px;font-weight:800;color:var(--navy-dark);text-transform:uppercase;letter-spacing:.8px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #1e3a5f22;display:flex;align-items:center;gap:8px">
            <span style="width:24px;height:24px;background:#1e3a5f;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px">6</span>
            Paiement du salaire
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div class="form-field">
                <label>Mode de paiement</label>
                <select name="mode_paiement">
                    <option value="virement"     <?= $sel('mode_paiement','virement') ?: ($edit ? '' : 'selected') ?>>Virement bancaire</option>
                    <option value="especes"      <?= $sel('mode_paiement','especes') ?>>Espèces</option>
                    <option value="cheque"       <?= $sel('mode_paiement','cheque') ?>>Chèque</option>
                    <option value="mobile_money" <?= $sel('mode_paiement','mobile_money') ?>>Mobile Money</option>
                </select>
            </div>
            <div class="form-field">
                <label>Banque</label>
                <input type="text" name="banque" value="<?= $v('banque') ?>" placeholder="CBAO, Ecobank, BHS...">
            </div>
            <div class="form-field">
                <label>N° IBAN / Compte</label>
                <input type="text" name="iban" value="<?= $v('iban') ?>" placeholder="SN28 XXXX XXXX XXXX">
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;padding-bottom:40px">
        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <?= $edit ? 'Mettre à jour' : 'Créer l\'employé' ?>
        </button>
        <a href="<?= APP_URL ?>/dossier/rh?id=<?= $entreprise['id'] ?>" class="btn btn-outline">Annuler</a>
    </div>
</form>

<script>
function toggleCDD() {
    var tc = document.getElementById('type_contrat').value;
    document.getElementById('bloc_fin_contrat').style.display = ['CDD','Stage','Interim'].includes(tc) ? 'block' : 'none';
}

// Calcul brut estimé en temps réel
var remFields = ['salaire_base','sursalaire','autres_indemnites','indemnite_logement','indemnite_transport','indemnite_representation'];
remFields.forEach(function(name) {
    var el = document.querySelector('[name="'+name+'"]');
    if(el) el.addEventListener('input', updateBrut);
});

function updateBrut() {
    var total = 0;
    remFields.forEach(function(name) {
        var el = document.querySelector('[name="'+name+'"]');
        if(el) total += parseFloat(el.value) || 0;
    });
    var fmt = new Intl.NumberFormat('fr-FR').format(total);
    document.getElementById('brut_estime').textContent = fmt + ' F CFA';
}
updateBrut();

// Calcul automatique des parts fiscales IR (CGI Sénégal Art. 165)
var partsBase = { celibataire: 1, marie: 1.5, divorce: 1, veuf: 1 };
function fmtPart(n){ return (n % 1 === 0 ? n.toFixed(0) : n.toFixed(1).replace('.', ',')); }
function calcParts() {
    var sit = (document.getElementById('sf_situation') || {}).value || 'celibataire';
    var enf = parseInt((document.getElementById('sf_enfants') || {}).value) || 0;
    var base = partsBase[sit] != null ? partsBase[sit] : 1;
    var parts = Math.min(5, base + enf * 0.5);
    var champ = document.getElementById('sf_parts');
    if (champ) champ.value = fmtPart(parts);
    var labels = { celibataire:'Célibataire', marie:'Marié(e)', divorce:'Divorcé(e)', veuf:'Veuf / Veuve' };
    var info = document.getElementById('sf_calc');
    if (info) {
        info.innerHTML = 'Base <b>' + (labels[sit]||sit) + '</b> : <b>' + fmtPart(base) + ' part(s)</b>'
            + ' &nbsp;+&nbsp; ' + enf + ' enfant(s) × 0,5 = <b>' + fmtPart(enf*0.5) + '</b>'
            + ' &nbsp;→&nbsp; <b style="color:#1f6e4e">Total : ' + fmtPart(parts) + ' part(s)</b>'
            + (parts >= 5 ? ' &nbsp;⚠️ plafond 5 parts atteint' : '');
    }
}
calcParts();
</script>
