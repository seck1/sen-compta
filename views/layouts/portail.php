<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon espace · SenCompta</title>
<link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/logo/sencompta-icon.svg">
<link rel="icon" type="image/png" href="<?= APP_URL ?>/logo/logo.png">
<link rel="apple-touch-icon" href="<?= APP_URL ?>/logo/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --green:#1f6e4e; --green-light:#2a8a63; --green-dark:#18583f;
  --navy:#1e3a5f; --gold:#b8923f;
  --ink:#18241f; --muted:#4a554f; --line:#e3e7e5; --bg:#eef1f0; --white:#fff;
  --red:#c0392b;
}
html,body{font-family:'DM Sans',sans-serif;color:var(--ink);background:var(--bg);min-height:100%;font-size:11pt;-webkit-font-smoothing:antialiased}

/* Topbar client */
.pc-top{background:var(--navy);color:#fff;padding:0 28px;height:62px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:50}
.pc-top .brand{display:flex;align-items:center;gap:11px;text-decoration:none;color:#fff}
.pc-top .brand img{width:34px;height:34px;object-fit:contain;border-radius:8px}
.pc-top .brand .n{font-weight:700;font-size:14pt;letter-spacing:-.2px}
.pc-top .ent{margin-left:auto;font-size:10pt;opacity:.85;display:flex;align-items:center;gap:14px}
.pc-logout{color:#fff;text-decoration:none;background:rgba(255,255,255,.1);padding:7px 14px;border-radius:8px;font-size:10pt;font-weight:600;transition:background .15s}
.pc-logout:hover{background:rgba(239,68,68,.25)}

.pc-wrap{max-width:1080px;margin:0 auto;padding:32px 24px 60px}

/* Cards */
.pc-card{background:var(--white);border:1px solid var(--line);border-radius:16px;padding:24px;margin-bottom:22px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
.pc-card h2{font-size:13pt;font-weight:700;color:var(--navy);margin-bottom:4px;display:flex;align-items:center;gap:9px}
.pc-card .sub{font-size:10pt;color:var(--muted);margin-bottom:18px}

.pc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px}
.pc-stat{background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px}
.pc-stat .lbl{font-size:9pt;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--muted)}
.pc-stat .val{font-size:20pt;font-weight:800;color:var(--ink);margin-top:6px;line-height:1.1}
.pc-stat .hint{font-size:9pt;color:var(--muted);margin-top:4px}

table{width:100%;border-collapse:collapse}
thead th{text-align:left;font-size:9pt;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding:9px 12px;border-bottom:2px solid var(--line)}
tbody td{padding:11px 12px;font-size:10.5pt;border-bottom:1px solid var(--line)}
tbody tr:last-child td{border-bottom:none}

.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:9pt;font-weight:700}
.b-green{background:rgba(31,110,78,.12);color:var(--green-dark)}
.b-red{background:rgba(192,57,43,.1);color:var(--red)}
.b-amber{background:rgba(245,158,11,.14);color:#92400e}
.b-navy{background:rgba(30,58,95,.1);color:var(--navy)}

.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:10px;border:none;font-family:inherit;font-size:10.5pt;font-weight:700;cursor:pointer;text-decoration:none;transition:filter .15s,transform .1s}
.btn-green{background:linear-gradient(180deg,var(--green-light),var(--green));color:#fff;box-shadow:0 8px 18px -8px rgba(31,110,78,.5)}
.btn-green:hover{filter:brightness(1.06)}
.btn-green:active{transform:translateY(1px)}

input,select{width:100%;padding:11px 13px;border:1.5px solid var(--line);border-radius:10px;font-size:10.5pt;font-family:inherit;color:var(--ink)}
input:focus,select:focus{outline:none;border-color:var(--green);box-shadow:0 0 0 3px rgba(31,110,78,.14)}
label{font-size:9.5pt;font-weight:700;color:var(--ink);text-transform:uppercase;letter-spacing:.3px;display:block;margin-bottom:6px}

.pc-flash{padding:12px 16px;border-radius:10px;font-size:10.5pt;font-weight:600;margin-bottom:20px}
.flash-ok{background:#eaf6ef;border:1px solid #b7e0c7;color:var(--green-dark)}
.flash-err{background:#fdecec;border:1px solid #f5c2c2;color:var(--red)}

@media(max-width:760px){
  .pc-grid{grid-template-columns:1fr}
  .pc-top .ent .ent-name{display:none}
  .pc-depot-form{grid-template-columns:1fr !important}
  .pc-depot-form .btn{width:100%;justify-content:center}
}
</style>
</head>
<body>

<div class="pc-top">
  <a href="<?= APP_URL ?>/portail" class="brand">
    <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
    <span class="n">SenCompta</span>
  </a>
  <div class="ent">
    <span class="ent-name"><?= e($entreprise['raison_sociale'] ?? '') ?></span>
    <a href="<?= APP_URL ?>/portail/logout" class="pc-logout">Déconnexion</a>
  </div>
</div>

<div class="pc-wrap">
  <?= $content ?>
</div>

</body>
</html>
