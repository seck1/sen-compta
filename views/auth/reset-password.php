<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Nouveau mot de passe</title>
<link rel="icon" type="image/svg+xml" href="/logo/sencompta-icon.svg">
<link rel="icon" type="image/png" href="/logo/logo.png">
<link rel="apple-touch-icon" href="/logo/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..600&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --green:#1f6e4e; --green-dark:#18583f; --green-light:#2a8a63;
    --navy:#1e3a5f; --gold:#b8923f; --gold-light:#d9b876;
    --ink:#18241f; --muted:#4a554f; --line:#d9dcdb; --bg:#eef1f0; --white:#ffffff;
}
html, body {
    min-height: 100%;
    font-family: 'DM Sans', -apple-system, sans-serif; color: var(--ink);
    background:
        radial-gradient(1200px 600px at 80% -5%, rgba(31,110,78,0.06), transparent 60%),
        radial-gradient(900px 500px at 0% 100%, rgba(30,58,95,0.05), transparent 55%),
        var(--bg);
    overflow-x: hidden; -webkit-font-smoothing: antialiased;
}
.page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
.card {
    position: relative; overflow: hidden;
    width: 100%; max-width: 460px;
    background: var(--white); border: 1px solid var(--line); border-radius: 22px;
    padding: 42px 42px 36px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 30px 60px -28px rgba(24,36,31,0.42);
    animation: rise .5s ease both;
}
@keyframes rise { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }
.card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold)); }

.back-link { display:inline-flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted); font-weight:500; text-decoration:none; margin-bottom:24px; transition:color .15s; }
.back-link:hover { color: var(--green); }
.back-link svg { width:15px; height:15px; }

.card-logo { display:flex; flex-direction:column; align-items:center; margin-bottom:26px; }
.card-logo img { width:76px; height:76px; object-fit:contain; padding:10px; border-radius:18px; background:linear-gradient(160deg,#f2f7f4,#e9f1ec); box-shadow:inset 0 0 0 1px rgba(31,110,78,0.12); }
.card-logo .name { font-size:20px; font-weight:700; color:var(--navy); margin-top:12px; letter-spacing:-0.2px; }
.card-logo .sub { font-size:11px; letter-spacing:2.5px; text-transform:uppercase; color:var(--gold); margin-top:4px; font-weight:700; }

.eyebrow { display:inline-flex; align-items:center; gap:8px; font-size:11px; color:var(--gold); text-transform:uppercase; letter-spacing:2.5px; font-weight:700; margin-bottom:12px; }
.eyebrow::before { content:''; display:block; width:24px; height:1.5px; background:var(--gold); opacity:.7; }
.title { font-family:'Fraunces',Georgia,serif; font-size:32px; font-weight:600; color:var(--navy); line-height:1.1; letter-spacing:-0.5px; margin-bottom:8px; }
.subtitle { font-size:15px; color:var(--muted); margin-bottom:28px; line-height:1.6; }

.alert-error { display:flex; align-items:center; gap:12px; background:rgba(192,57,43,0.07); border:1px solid rgba(192,57,43,0.28); border-radius:12px; padding:14px 18px; color:#c0392b; font-size:14px; font-weight:500; margin-bottom:22px; }
.alert-error svg { width:18px; height:18px; flex-shrink:0; }

.invalid-box { background:rgba(192,57,43,0.06); border:1px solid rgba(192,57,43,0.22); border-radius:14px; padding:26px 22px; text-align:center; }
.invalid-box .ic { width:52px; height:52px; border-radius:50%; background:rgba(192,57,43,0.1); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
.invalid-box h3 { font-size:16px; font-weight:700; color:#c0392b; margin-bottom:8px; }
.invalid-box p { font-size:13.5px; color:var(--muted); line-height:1.65; }

.field { margin-bottom:18px; }
.field label { display:block; font-size:14px; font-weight:700; color:var(--ink); margin-bottom:8px; }
.pw-wrap { position:relative; }
.pw-wrap input { width:100%; padding:14px 48px 14px 16px; font-family:inherit; font-size:15px; color:var(--ink); background:var(--white); border:1.5px solid var(--line); border-radius:10px; transition:border-color .15s, box-shadow .15s; }
.pw-wrap input::placeholder { color:#6b7570; }
.pw-wrap input:focus { outline:none; border-color:var(--green); box-shadow:0 0 0 3px rgba(31,110,78,0.16); }
.pw-toggle { position:absolute; right:6px; top:50%; transform:translateY(-50%); width:38px; height:38px; border:none; background:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--muted); border-radius:8px; transition:color .15s, background .15s; }
.pw-toggle:hover { color:var(--green); background:rgba(31,110,78,0.06); }
.pw-toggle svg { width:20px; height:20px; }

.btn-primary { width:100%; padding:15px; margin-top:6px; font-family:inherit; font-size:16px; font-weight:700; color:#fff; background:linear-gradient(180deg, var(--green-light), var(--green)); border:none; border-radius:10px; cursor:pointer; box-shadow:0 8px 22px -10px rgba(31,110,78,0.7); transition:transform .15s, box-shadow .15s, filter .15s; }
.btn-primary:hover { transform:translateY(-1px); filter:brightness(1.04); box-shadow:0 12px 28px -10px rgba(31,110,78,0.8); }
.btn-primary:active { transform:translateY(0); }

.hint { font-size:13.5px; color:var(--muted); text-align:center; margin-top:22px; line-height:1.7; }
.hint a { color:var(--green); font-weight:700; text-decoration:none; }
.hint a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="page">
    <div class="card">

        <a href="<?= APP_URL ?>/login" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Retour à la connexion
        </a>

        <div class="card-logo">
            <img src="<?= APP_URL ?>/logo/sencompta-icon.svg" alt="SenCompta">
            <div class="name">SenCompta</div>
            <div class="sub">Le SaaS Comptable du Sénégal</div>
        </div>

        <?php if (!$valid): ?>

        <div class="invalid-box">
            <div class="ic">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#c0392b" style="width:26px;height:26px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            </div>
            <h3>Lien invalide ou expiré</h3>
            <p>Ce lien de réinitialisation n'est plus valable (il expire après 1 heure). Veuillez refaire une demande.</p>
        </div>
        <p class="hint" style="margin-top:24px">
            <a href="<?= APP_URL ?>/mot-de-passe-oublie">Demander un nouveau lien</a>
        </p>

        <?php else: ?>

        <p class="eyebrow">Sécurité</p>
        <h2 class="title">Nouveau mot de passe</h2>
        <p class="subtitle">Choisissez un nouveau mot de passe pour votre compte. 8 caractères minimum.</p>

        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/reset-password/post">
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= e($_GET['token'] ?? '') ?>">

            <div class="field">
                <label for="password">Nouveau mot de passe</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="new-password" minlength="8">
                    <button type="button" class="pw-toggle" onclick="togglePw('password',this)" aria-label="Afficher">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
            </div>

            <div class="field">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <div class="pw-wrap">
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" required autocomplete="new-password" minlength="8">
                    <button type="button" class="pw-toggle" onclick="togglePw('password_confirm',this)" aria-label="Afficher">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">Réinitialiser mon mot de passe →</button>
        </form>

        <?php endif; ?>

    </div>
</div>
<script>
function togglePw(id, btn){
    var i = document.getElementById(id);
    i.type = i.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
