<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Vérification 2FA — SenCompta</title>
<link rel="icon" type="image/svg+xml" href="/logo/sencompta-icon.svg">
<link rel="icon" type="image/png" href="/logo/logo.png">
<link rel="apple-touch-icon" href="/logo/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f0f3f8;min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:white;border-radius:20px;padding:48px;width:420px;box-shadow:0 20px 60px rgba(30,58,95,0.12);text-align:center}
.logo{width:56px;height:56px;background:linear-gradient(135deg,#1e3a5f,#2a4f7c);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-family:'Cormorant Garamond',serif;font-size:22px;color:white}
h1{font-family:'Cormorant Garamond',serif;font-size:26px;color:#122540;margin-bottom:8px}
p{font-size:14px;color:#6b7a94;margin-bottom:28px}
input[type=text]{width:100%;padding:16px;border:1px solid #e4e9f0;border-radius:12px;font-size:28px;text-align:center;letter-spacing:8px;font-family:monospace;outline:none;transition:border-color .2s}
input[type=text]:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08)}
button{width:100%;padding:14px;background:linear-gradient(135deg,#1e3a5f,#2a4f7c);color:white;border:none;border-radius:12px;font-size:13px;font-weight:500;cursor:pointer;margin-top:16px;transition:opacity .2s}
button:hover{opacity:0.9}
.error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#dc2626;border-radius:10px;padding:10px;margin-bottom:16px;font-size:13px}
a{font-size:13px;color:#6b7a94;text-decoration:none;display:block;margin-top:20px}
</style>
</head>
<body>
<div class="card">
    <div class="logo">SMC</div>
    <h1>Vérification 2FA</h1>
    <p>Entrez le code à 6 chiffres de votre application d'authentification</p>
    <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= APP_URL ?>/login/2fa">
        <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" autofocus autocomplete="one-time-code" required>
        <button type="submit">Vérifier</button>
    </form>
    <a href="<?= APP_URL ?>/login">← Retour à la connexion</a>
</div>
</body>
</html>
