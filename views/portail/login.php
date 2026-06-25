<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Espace client · SenCompta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1f6e4e;--green-light:#2a8a63;--green-dark:#18583f;--navy:#1e3a5f;--gold:#b8923f;--ink:#18241f;--muted:#4a554f;--line:#d9dcdb;--red:#c0392b}
html,body{min-height:100%;font-family:'DM Sans',sans-serif;color:var(--ink);
  background:radial-gradient(1100px 560px at 80% -5%,rgba(31,110,78,.07),transparent 60%),radial-gradient(800px 460px at 0% 100%,rgba(30,58,95,.05),transparent 55%),#eef1f0;
  display:flex;align-items:center;justify-content:center;padding:24px;-webkit-font-smoothing:antialiased}
.card{position:relative;overflow:hidden;width:100%;max-width:410px;background:#fff;border:1px solid var(--line);border-radius:22px;padding:40px 36px 32px;box-shadow:0 1px 2px rgba(0,0,0,.04),0 30px 60px -28px rgba(24,36,31,.4)}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),#d9b876,var(--gold))}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:26px}
.brand img{width:46px;height:46px;object-fit:contain;padding:6px;border-radius:12px;background:linear-gradient(160deg,#f2f7f4,#e9f1ec);box-shadow:inset 0 0 0 1px rgba(31,110,78,.12)}
.brand .n{font-size:16pt;font-weight:700;color:var(--navy)}
.brand .s{font-size:8pt;letter-spacing:2px;text-transform:uppercase;color:var(--gold);font-weight:700;margin-top:2px}
h1{font-size:18pt;font-weight:800;letter-spacing:-.4px;margin-bottom:6px}
h1 span{color:var(--green)}
.lead{font-size:10.5pt;color:var(--muted);margin-bottom:24px}
label{font-size:9.5pt;font-weight:700;text-transform:uppercase;letter-spacing:.3px;display:block;margin-bottom:7px}
input{width:100%;padding:13px 15px;border:1.5px solid var(--line);border-radius:11px;font-size:11pt;font-family:inherit;margin-bottom:16px}
input:focus{outline:none;border-color:var(--green);box-shadow:0 0 0 3px rgba(31,110,78,.16)}
.btn{width:100%;padding:14px;border:none;border-radius:999px;font-family:inherit;font-size:11pt;font-weight:700;color:#fff;background:linear-gradient(180deg,var(--green-light),var(--green));cursor:pointer;box-shadow:0 10px 24px -10px rgba(31,110,78,.55);transition:filter .15s}
.btn:hover{filter:brightness(1.05)}
.err{background:#fdecec;border:1px solid #f5c2c2;color:var(--red);padding:11px 14px;border-radius:10px;font-size:10pt;font-weight:500;margin-bottom:18px}
.foot{text-align:center;font-size:9.5pt;color:var(--muted);margin-top:22px}
.foot a{color:var(--green);font-weight:700;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
    <div><div class="n">SenCompta</div><div class="s">Espace Client</div></div>
  </div>
  <h1>Votre <span>espace</span></h1>
  <p class="lead">Consultez votre dossier comptable et échangez avec votre cabinet.</p>

  <?php if (!empty($error)): ?><div class="err"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="<?= APP_URL ?>/portail/auth">
    <label>Email</label>
    <input type="email" name="email" placeholder="vous@entreprise.sn" required autofocus>
    <label>Mot de passe</label>
    <input type="password" name="password" placeholder="••••••••" required>
    <button type="submit" class="btn">Se connecter</button>
  </form>

  <div class="foot">Cabinet comptable ? <a href="<?= APP_URL ?>/login">Accès cabinet</a></div>
</div>
<?php require APP_ROOT . '/views/partials/cookie-banner.php'; ?>
</body>
</html>
