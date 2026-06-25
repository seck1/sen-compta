<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageLegalTitre) ?> · SenCompta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1f6e4e;--green-dark:#18583f;--navy:#1e3a5f;--gold:#b8923f;--ink:#18241f;--muted:#4a554f;--line:#e3e7e5;--bg:#eef1f0}
html,body{font-family:'DM Sans',sans-serif;color:var(--ink);background:var(--bg);font-size:11pt;line-height:1.6;-webkit-font-smoothing:antialiased}
.lg-top{background:var(--navy);height:62px;display:flex;align-items:center;padding:0 28px;position:sticky;top:0;z-index:10}
.lg-top a.brand{display:flex;align-items:center;gap:11px;text-decoration:none;color:#fff}
.lg-top img{width:34px;height:34px;object-fit:contain;border-radius:8px}
.lg-top .n{font-weight:700;font-size:14pt}
.lg-top .back{margin-left:auto;color:#fff;text-decoration:none;font-size:10pt;font-weight:600;background:rgba(255,255,255,.1);padding:8px 15px;border-radius:8px}
.lg-top .back:hover{background:rgba(255,255,255,.2)}
.lg-wrap{max-width:820px;margin:0 auto;padding:48px 24px 80px}
.lg-card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:44px 48px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.lg-card h1{font-size:22pt;font-weight:800;letter-spacing:-.5px;color:var(--navy);margin-bottom:6px}
.lg-maj{font-size:9.5pt;color:var(--muted);margin-bottom:30px;padding-bottom:20px;border-bottom:2px solid var(--line)}
.lg-card h2{font-size:14pt;font-weight:700;color:var(--green-dark);margin:30px 0 10px}
.lg-card h3{font-size:11.5pt;font-weight:700;color:var(--ink);margin:20px 0 6px}
.lg-card p{margin-bottom:12px;color:#2a3530}
.lg-card ul{margin:8px 0 14px 22px}
.lg-card li{margin-bottom:6px}
.lg-card a{color:var(--green);font-weight:600}
.lg-fill{background:rgba(184,146,63,.14);color:#7a5e1f;padding:1px 7px;border-radius:5px;font-weight:700;font-size:.92em;border:1px dashed rgba(184,146,63,.5)}
.lg-note{background:rgba(31,110,78,.06);border-left:3px solid var(--green);padding:14px 18px;border-radius:8px;margin:18px 0;font-size:10pt;color:var(--green-dark)}
.lg-foot{max-width:820px;margin:0 auto;padding:0 24px 50px;text-align:center;font-size:9.5pt;color:var(--muted)}
.lg-foot a{color:var(--green);text-decoration:none;font-weight:600;margin:0 8px}
@media(max-width:600px){.lg-card{padding:28px 22px}}
</style>
</head>
<body>
<div class="lg-top">
  <a href="<?= APP_URL ?>/login" class="brand"><img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta"><span class="n">SenCompta</span></a>
  <a href="<?= APP_URL ?>/login" class="back">← Retour</a>
</div>
<div class="lg-wrap">
  <div class="lg-card">
    <?= $contenuLegal ?>
  </div>
</div>
<div class="lg-foot">
  <a href="<?= APP_URL ?>/confidentialite">Confidentialité</a>·
  <a href="<?= APP_URL ?>/mentions-legales">Mentions légales</a>·
  <a href="<?= APP_URL ?>/cgu">CGU</a>·
  <a href="<?= APP_URL ?>/cookies">Cookies</a>
  <div style="margin-top:10px">© <?= date('Y') ?> SenCompta · Tous droits réservés</div>
</div>
</body>
</html>
