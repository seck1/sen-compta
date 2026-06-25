<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Créer votre espace cabinet</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --navy:#1e3a5f;--gold:#c9a96e;--gold-l:#e2c99a;
  --green:#059669;--red:#ef4444;--gray:#6b7280;
  --bg:#f9fafb;--white:#fff;--border:#e5e7eb;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--navy);min-height:100vh;}

/* ── HEADER ── */
.header{background:var(--navy);padding:16px 40px;display:flex;align-items:center;gap:14px;}
.logo-badge{width:40px;height:40px;border-radius:50%;border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;}
.logo-badge span{font-size:14px;font-weight:700;color:var(--gold);}
.logo-name{font-size:20px;font-weight:700;color:#fff;}
.header-tagline{margin-left:auto;font-size:12px;color:rgba(255,255,255,0.4);}

/* ── LAYOUT ── */
.page{display:grid;grid-template-columns:1fr 480px;min-height:calc(100vh - 72px);}

/* ── LEFT ── */
.left{background:var(--navy);padding:60px 50px;display:flex;flex-direction:column;justify-content:center;gap:40px;}
.left h1{font-size:36px;font-weight:800;color:#fff;line-height:1.2;}
.left h1 em{color:var(--gold);font-style:normal;}
.left p{font-size:15px;color:rgba(255,255,255,0.5);line-height:1.7;}
.plans-preview{display:flex;flex-direction:column;gap:12px;}
.plan-row{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;}
.plan-row.featured{border-color:var(--gold);background:rgba(201,162,39,0.08);}
.plan-name{font-size:14px;font-weight:600;color:#fff;}
.plan-detail{font-size:12px;color:rgba(255,255,255,0.4);margin-top:2px;}
.plan-price{font-size:16px;font-weight:700;color:var(--gold);text-align:right;}
.plan-price small{display:block;font-size:10px;color:rgba(255,255,255,0.3);font-weight:400;}
.badge-essai{background:var(--green);color:#fff;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px;letter-spacing:.5px;}

/* ── RIGHT (formulaire) ── */
.right{background:var(--white);padding:50px 40px;display:flex;flex-direction:column;justify-content:center;gap:28px;overflow-y:auto;}
.form-title{font-size:22px;font-weight:700;color:var(--navy);}
.form-title span{color:var(--gold);}
.form-sub{font-size:13px;color:var(--gray);}

.alert{padding:12px 16px;border-radius:10px;font-size:13px;font-weight:500;}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:var(--red);}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:var(--green);}

.form-group{display:flex;flex-direction:column;gap:6px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{font-size:12px;font-weight:600;color:var(--navy);letter-spacing:.3px;text-transform:uppercase;}
input,select{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;font-family:'Inter',sans-serif;color:var(--navy);transition:border .2s;}
input:focus,select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,162,39,0.1);}

/* Sélecteur de plan */
.plan-selector{display:flex;flex-direction:column;gap:8px;}
.plan-option{display:flex;align-items:center;gap:12px;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;transition:all .2s;}
.plan-option:hover{border-color:var(--gold);}
.plan-option input[type=radio]{display:none;}
.plan-option.selected{border-color:var(--gold);background:rgba(201,162,39,0.04);}
.plan-option-dot{width:18px;height:18px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;transition:all .2s;}
.plan-option.selected .plan-option-dot{border-color:var(--gold);background:var(--gold);}
.plan-option-info{flex:1;}
.plan-option-name{font-size:13px;font-weight:600;color:var(--navy);}
.plan-option-desc{font-size:11px;color:var(--gray);margin-top:1px;}
.plan-option-price{font-size:13px;font-weight:700;color:var(--gold);}

.btn-submit{background:var(--navy);color:#fff;border:none;padding:14px;border-radius:12px;font-size:15px;font-weight:600;cursor:pointer;width:100%;font-family:'Inter',sans-serif;transition:background .2s;}
.btn-submit:hover{background:#162840;}

.form-footer{text-align:center;font-size:13px;color:var(--gray);}
.form-footer a{color:var(--gold);text-decoration:none;font-weight:500;}

.divider{height:1px;background:var(--border);}

@media(max-width:900px){
  .page{grid-template-columns:1fr;}
  .left{display:none;}
}
@media(max-width:600px){
  .form-row{grid-template-columns:1fr;}
}
html,body{overflow-x:hidden;max-width:100%;}
</style>
</head>
<body>

<div class="header">
  <div class="logo-badge"><span>SC</span></div>
  <div class="logo-name">SenCompta</div>
  <div class="header-tagline">Le SaaS comptable du Sénégal</div>
</div>

<div class="page">

  <!-- ── Gauche ── -->
  <div class="left">
    <div>
      <h1>Gérez tous vos dossiers<br>depuis <em>une seule plateforme</em></h1>
      <p>SenCompta est le SaaS comptable conçu pour les cabinets sénégalais. Comptabilité, paie, facturation et fiscalité — tout en un.</p>
    </div>

    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <span style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px;">Nos formules</span>
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
          <div class="plan-price">
            <?php if ($p['prix_mois'] > 0): ?>
              <?= number_format($p['prix_mois'], 0, ',', ' ') ?> FCFA
              <small>/mois</small>
            <?php else: ?>
              Sur devis
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ── Droite (formulaire) ── -->
  <div class="right">
    <div>
      <div class="form-title">Créer votre espace <span>cabinet</span></div>
      <div class="form-sub">Essai gratuit 14 jours · Aucune carte requise</div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/inscription/post">
      <?= csrfField() ?>

      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="form-group">
          <label>Nom du cabinet *</label>
          <input type="text" name="nom_cabinet" placeholder="Ex: Cabinet Diallo & Associés" required>
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
          <input type="email" name="email" placeholder="contact@votre-cabinet.sn" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Mot de passe *</label>
            <input type="password" name="password" placeholder="Min. 8 caractères" required>
          </div>
          <div class="form-group">
            <label>Confirmer *</label>
            <input type="password" name="password2" placeholder="Répéter" required>
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
              <div class="plan-option-price">
                <?= $p['prix_mois'] > 0 ? number_format($p['prix_mois'], 0, ',', ' ') . ' FCFA/mois' : 'Gratuit 14j' ?>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="btn-submit">Créer mon espace gratuitement →</button>

        <div class="form-footer">
          Déjà un compte ? <a href="<?= APP_URL ?>/login">Se connecter</a>
        </div>
      </div>
    </form>
  </div>

</div>

<script>
function selectPlan(el, code) {
  document.querySelectorAll('.plan-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input[type=radio]').checked = true;
}
</script>
</body>
</html>
