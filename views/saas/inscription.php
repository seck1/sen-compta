<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Créer votre espace cabinet</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

:root{
    --green:      #1f6e4e;
    --green-dark: #18583f;
    --green-light:#2a8a63;
    --navy:       #1e3a5f;
    --gold:       #b8923f;
    --gold-light: #d9b876;
    --ink:        #18241f;
    --muted:      #4a554f;
    --line:       #d9dcdb;
    --bg:         #eef1f0;
    --white:      #ffffff;
    --red:        #c0392b;
}

html,body{
    min-height:100%;
    font-family:'DM Sans',-apple-system,sans-serif;
    color:var(--ink);
    background:
        radial-gradient(1200px 600px at 80% -5%, rgba(31,110,78,0.06), transparent 60%),
        radial-gradient(900px 500px at 0% 100%, rgba(30,58,95,0.05), transparent 55%),
        var(--bg);
    overflow-x:hidden; max-width:100%;
    -webkit-font-smoothing:antialiased;
}

.shell{
    min-height:100vh;
    display:grid;
    grid-template-columns:1fr 480px;
    align-items:start;
    gap:48px;
    max-width:1340px; margin:0 auto;
    padding:48px 32px 40px;
}
.col-left{ position:sticky; top:48px; }

/* ============ COLONNE GAUCHE : presentation + plans ============ */
.brand{ display:flex; align-items:center; gap:14px; margin-bottom:34px; }
.brand img{ width:52px; height:52px; object-fit:contain; padding:7px; border-radius:14px; background:linear-gradient(160deg,#f2f7f4,#e9f1ec); box-shadow:inset 0 0 0 1px rgba(31,110,78,0.12); }
.brand .name{ font-size:20px; font-weight:700; color:var(--navy); letter-spacing:-0.2px; }
.brand .sub{ font-size:11px; letter-spacing:2.5px; text-transform:uppercase; color:var(--gold); margin-top:3px; font-weight:700; }

.intro h1{ font-size:clamp(30px,3.4vw,42px); font-weight:800; line-height:1.12; color:var(--ink); letter-spacing:-0.8px; }
.intro h1 em{ color:var(--green); font-style:normal; }
.intro p{ margin-top:16px; font-size:14px; line-height:1.7; color:var(--muted); max-width:560px; }

.plans-head{ display:flex; align-items:center; justify-content:space-between; margin:34px 0 16px; }
.plans-head .lbl{ font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:var(--navy); }
.badge-essai{ background:var(--green); color:#fff; font-size:11px; font-weight:700; padding:4px 11px; border-radius:20px; letter-spacing:.4px; }

.plans-preview{ display:flex; flex-direction:column; gap:12px; }
.plan-row{ background:var(--white); border:1px solid var(--line); border-radius:14px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; transition:transform .2s, box-shadow .2s, border-color .2s; }
.plan-row:hover{ transform:translateY(-2px); box-shadow:0 10px 22px -14px rgba(24,36,31,0.2); }
.plan-row.featured{ border-color:var(--gold); box-shadow:0 0 0 1px var(--gold); }
.plan-name{ font-size:13px; font-weight:700; color:var(--ink); }
.plan-detail{ font-size:12.5px; color:var(--muted); margin-top:3px; }
.plan-price{ font-size:14px; font-weight:800; color:var(--green); text-align:right; }
.plan-price small{ display:block; font-size:10px; color:var(--muted); font-weight:500; }

/* ============ COLONNE DROITE : carte formulaire ============ */
.card{
    position:relative; overflow:hidden;
    background:var(--white); border:1px solid var(--line); border-radius:22px;
    padding:38px 38px 32px;
    box-shadow:0 1px 2px rgba(0,0,0,0.04), 0 30px 60px -28px rgba(24,36,31,0.42);
    animation:rise .5s ease both;
}
.card::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--gold),var(--gold-light),var(--gold)); }

.form-title{ font-size:26px; font-weight:800; color:var(--ink); letter-spacing:-0.5px; }
.form-title span{ color:var(--green); }
.form-sub{ font-size:14px; color:var(--muted); margin-top:8px; margin-bottom:24px; }

.alert{ padding:12px 15px; border-radius:10px; font-size:14px; font-weight:500; margin-bottom:18px; }
.alert-error{ background:#fdecec; border:1px solid #f5c2c2; color:var(--red); }
.alert-success{ background:#eaf6ef; border:1px solid #b7e0c7; color:var(--green-dark); }

.form-group{ display:flex; flex-direction:column; gap:7px; margin-bottom:14px; }
.form-row{ display:grid; grid-template-columns:1fr 1fr; gap:14px; }
label{ font-size:12px; font-weight:700; color:var(--ink); letter-spacing:.3px; text-transform:uppercase; }
input,select{
    width:100%; padding:13px 15px;
    border:1.5px solid var(--line); border-radius:10px;
    font-size:14.5px; font-family:inherit; color:var(--ink);
    background:var(--white); transition:border .15s, box-shadow .15s;
}
input::placeholder{ color:#6b7570; }
input:focus,select:focus{ outline:none; border-color:var(--green); box-shadow:0 0 0 3px rgba(31,110,78,0.16); }

.divider{ height:1px; background:var(--line); margin:18px 0; }

/* Selecteur de plan */
.plan-selector{ display:flex; flex-direction:column; gap:9px; }
.plan-option{ display:flex; align-items:center; gap:12px; padding:13px 16px; border:1.5px solid var(--line); border-radius:12px; cursor:pointer; transition:all .15s; }
.plan-option:hover{ border-color:var(--green); }
.plan-option input[type=radio]{ display:none; }
.plan-option.selected{ border-color:var(--green); background:rgba(31,110,78,0.04); }
.plan-option-dot{ width:18px; height:18px; border-radius:50%; border:2px solid var(--line); flex-shrink:0; transition:all .15s; }
.plan-option.selected .plan-option-dot{ border-color:var(--green); background:var(--green); box-shadow:inset 0 0 0 3px #fff; }
.plan-option-info{ flex:1; }
.plan-option-name{ font-size:13.5px; font-weight:700; color:var(--ink); }
.plan-option-desc{ font-size:11.5px; color:var(--muted); margin-top:1px; }
.plan-option-price{ font-size:13px; font-weight:800; color:var(--green); }

.btn-submit{
    width:100%; padding:15px; margin-top:6px;
    font-family:inherit; font-size:14px; font-weight:700; color:#fff;
    background:linear-gradient(180deg,var(--green-light),var(--green));
    border:none; border-radius:999px; cursor:pointer;
    box-shadow:0 10px 24px -10px rgba(31,110,78,0.55);
    transition:box-shadow .18s, filter .15s, transform .15s;
}
.btn-submit:hover{ box-shadow:0 14px 28px -10px rgba(31,110,78,0.65); filter:brightness(1.05); }
.btn-submit:active{ transform:translateY(1px); }

.form-footer{ text-align:center; font-size:13.5px; color:var(--muted); margin-top:18px; }
.form-footer a{ color:var(--green); text-decoration:none; font-weight:700; }
.form-footer a:hover{ text-decoration:underline; }

a:focus-visible, button:focus-visible, input:focus-visible{ outline:3px solid rgba(31,110,78,0.45); outline-offset:2px; }

@keyframes rise{ from{ opacity:0; transform:translateY(14px);} to{ opacity:1; transform:translateY(0);} }

@media(max-width:980px){
    .shell{ grid-template-columns:1fr; max-width:560px; gap:36px; }
    .col-left{ position:static; }
}
@media(max-width:520px){
    .shell{ padding:28px 14px; }
    .card{ padding:30px 20px 26px; border-radius:16px; }
    .form-row{ grid-template-columns:1fr; }
    .intro h1{ font-size:28px; }
}
</style>
</head>
<body>

<div class="shell">

  <!-- ── Colonne gauche : presentation + plans ── -->
  <div class="col-left">

    <div class="brand">
      <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
      <div>
        <div class="name">SenCompta</div>
        <div class="sub">Le SaaS Comptable du Sénégal</div>
      </div>
    </div>

    <div class="intro">
      <h1>Gérez tous vos dossiers depuis <em>une seule plateforme</em></h1>
      <p>SenCompta est le SaaS comptable conçu pour les cabinets sénégalais. Comptabilité SYSCOHADA, paie, facturation et fiscalité — tout en un, conforme OHADA.</p>
    </div>

    <div class="plans-head">
      <span class="lbl">Nos formules</span>
      <span class="badge-essai">14 jours gratuits</span>
    </div>
    <div class="plans-preview">
      <?php foreach ($plans as $p): ?>
      <div class="plan-row <?= $p['code'] === 'pro' ? 'featured' : '' ?>">
        <div>
          <div class="plan-name"><?= e($p['nom']) ?></div>
          <div class="plan-detail">
            <?= $p['max_entreprises'] == -1 ? 'Illimité' : $p['max_entreprises'] ?> entreprise<?= $p['max_entreprises'] > 1 || $p['max_entreprises'] == -1 ? 's' : '' ?> ·
            <?= $p['max_users'] == -1 ? 'Illimité' : $p['max_users'] ?> utilisateur<?= $p['max_users'] > 1 || $p['max_users'] == -1 ? 's' : '' ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Colonne droite : formulaire ── -->
  <div class="col-right">
    <div class="card">

      <div class="form-title">Créer votre espace <span>cabinet</span></div>
      <div class="form-sub">Essai gratuit 14 jours · Aucune carte bancaire requise</div>

      <?php if ($error): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?= e($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= APP_URL ?>/inscription/post">
        <?= csrfField() ?>

        <div class="form-group">
          <label>Nom du cabinet *</label>
          <input type="text" name="nom_cabinet" placeholder="Ex: Cabinet Diallo & Associés" required autofocus>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Responsable *</label>
            <input type="text" name="responsable" placeholder="Prénom Nom" required>
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input type="tel" name="telephone" placeholder="77 000 00 00">
          </div>
        </div>

        <div class="form-group">
          <label>Email professionnel *</label>
          <input type="email" name="email" placeholder="contact@votre-cabinet.sn" required autocomplete="email">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Mot de passe *</label>
            <input type="password" name="password" placeholder="Min. 8 caractères" required autocomplete="new-password">
          </div>
          <div class="form-group">
            <label>Confirmer *</label>
            <input type="password" name="password2" placeholder="Répéter" required autocomplete="new-password">
          </div>
        </div>

        <div class="divider"></div>

        <div class="form-group">
          <label>Choisir votre formule</label>
          <div class="plan-selector">
            <?php foreach ($plans as $p): if ($p['code'] === 'enterprise') continue; ?>
            <label class="plan-option <?= $p['code'] === 'solo' ? 'selected' : '' ?>" onclick="selectPlan(this, '<?= $p['code'] ?>')">
              <input type="radio" name="plan" value="<?= $p['code'] ?>" <?= $p['code'] === 'solo' ? 'checked' : '' ?>>
              <div class="plan-option-dot"></div>
              <div class="plan-option-info">
                <div class="plan-option-name"><?= e($p['nom']) ?> — <?= $p['max_entreprises'] ?> entreprise<?= $p['max_entreprises'] > 1 ? 's' : '' ?></div>
                <div class="plan-option-desc"><?= $p['max_users'] ?> utilisateurs inclus</div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;cursor:pointer;font-size:12.5px;color:var(--muted);line-height:1.5">
          <input type="checkbox" name="accept_cgu" value="1" required style="margin-top:2px;width:17px;height:17px;accent-color:var(--green);flex-shrink:0">
          <span>J'accepte les <a href="<?= APP_URL ?>/cgu" target="_blank" style="color:var(--green);font-weight:600">conditions générales d'utilisation</a> et la <a href="<?= APP_URL ?>/confidentialite" target="_blank" style="color:var(--green);font-weight:600">politique de confidentialité</a>, et je consens au traitement de mes données conformément à la réglementation.</span>
        </label>

        <button type="submit" class="btn-submit">Créer mon espace gratuitement →</button>

        <div class="form-footer">
          Déjà un compte ? <a href="<?= APP_URL ?>/login">Se connecter</a>
        </div>
      </form>

    </div>
  </div>

</div>

<script>
function selectPlan(el, code) {
  document.querySelectorAll('.plan-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input[type=radio]').checked = true;
}
</script>
<?php require APP_ROOT . '/views/partials/cookie-banner.php'; ?>
</body>
</html>
