<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Mot de passe oublié</title>
<link rel="icon" type="image/svg+xml" href="/logo/sencompta-icon.svg">
<link rel="icon" type="image/png" href="/logo/logo.png">
<link rel="apple-touch-icon" href="/logo/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..600&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
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
}

html, body {
    min-height: 100%;
    font-family: 'DM Sans', -apple-system, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(1200px 600px at 80% -5%, rgba(31,110,78,0.06), transparent 60%),
        radial-gradient(900px 500px at 0% 100%, rgba(30,58,95,0.05), transparent 55%),
        var(--bg);
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

.page {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 40px 20px;
}

/* ============ CARTE ============ */
.card {
    position: relative; overflow: hidden;
    width: 100%; max-width: 460px;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 22px;
    padding: 42px 42px 36px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 30px 60px -28px rgba(24,36,31,0.42);
    animation: rise .5s ease both;
}
@keyframes rise { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }
.card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
}

.back-link {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13.5px; color: var(--muted); font-weight: 500;
    text-decoration: none; margin-bottom: 24px;
    transition: color .15s;
}
.back-link:hover { color: var(--green); }
.back-link svg { width: 15px; height: 15px; }

.card-logo { display: flex; flex-direction: column; align-items: center; margin-bottom: 26px; }
.card-logo img {
    width: 76px; height: 76px; object-fit: contain;
    padding: 10px; border-radius: 18px;
    background: linear-gradient(160deg, #f2f7f4, #e9f1ec);
    box-shadow: inset 0 0 0 1px rgba(31,110,78,0.12);
}
.card-logo .name { font-size: 20px; font-weight: 700; color: var(--navy); margin-top: 12px; letter-spacing: -0.2px; }
.card-logo .sub  { font-size: 11px; letter-spacing: 2.5px; text-transform: uppercase; color: var(--gold); margin-top: 4px; font-weight: 700; }

.eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; color: var(--gold); text-transform: uppercase;
    letter-spacing: 2.5px; font-weight: 700; margin-bottom: 12px;
}
.eyebrow::before { content:''; display:block; width:24px; height:1.5px; background:var(--gold); opacity:.7; }

.title { font-family: 'Fraunces', Georgia, serif; font-size: 32px; font-weight: 600; color: var(--navy); line-height: 1.1; letter-spacing: -0.5px; margin-bottom: 8px; }
.subtitle { font-size: 15px; color: var(--muted); margin-bottom: 28px; line-height: 1.6; }

/* Success state */
.success-box {
    background: rgba(31,110,78,0.06);
    border: 1px solid rgba(31,110,78,0.2);
    border-radius: 14px;
    padding: 26px 22px;
    text-align: center;
}
.success-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(31,110,78,0.12);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.success-title { font-size: 16px; font-weight: 700; color: var(--green); margin-bottom: 8px; }
.success-desc { font-size: 13.5px; color: var(--muted); line-height: 1.65; }

/* Alert */
.alert-error {
    display: flex; align-items: center; gap: 12px;
    background: rgba(192,57,43,0.07); border: 1px solid rgba(192,57,43,0.28);
    border-radius: 12px; padding: 14px 18px;
    color: #c0392b; font-size: 14px; font-weight: 500; margin-bottom: 22px;
}
.alert-error svg { width: 18px; height: 18px; flex-shrink: 0; }

/* Field */
.field { margin-bottom: 20px; }
.field label { display: block; font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 8px; }
.input-wrap { position: relative; }
.input-wrap svg { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--muted); opacity: .55; pointer-events: none; transition: color .15s, opacity .15s; }
.input-wrap:focus-within svg { color: var(--green); opacity: 1; }
.field input {
    width: 100%; padding: 14px 16px 14px 46px;
    font-family: inherit; font-size: 15px; color: var(--ink);
    background: var(--white); border: 1.5px solid var(--line);
    border-radius: 10px; transition: border-color .15s, box-shadow .15s;
}
.field input::placeholder { color: #6b7570; }
.field input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,110,78,0.16); }

.btn-primary {
    width: 100%; padding: 15px; margin-top: 4px;
    font-family: inherit; font-size: 16px; font-weight: 700; color: #fff;
    background: linear-gradient(180deg, var(--green-light), var(--green));
    border: none; border-radius: 10px; cursor: pointer;
    box-shadow: 0 8px 22px -10px rgba(31,110,78,0.7);
    transition: transform .15s, box-shadow .15s, filter .15s;
}
.btn-primary:hover { transform: translateY(-1px); filter: brightness(1.04); box-shadow: 0 12px 28px -10px rgba(31,110,78,0.8); }
.btn-primary:active { transform: translateY(0); }

.hint { font-size: 13.5px; color: var(--muted); text-align: center; margin-top: 22px; line-height: 1.7; }
.hint a { color: var(--green); font-weight: 700; text-decoration: none; }
.hint a:hover { text-decoration: underline; }
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

        <?php if ($sent): ?>

        <div class="success-box">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="var(--green)" style="width:26px;height:26px"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </div>
            <div class="success-title">Email envoyé !</div>
            <div class="success-desc">
                Si cette adresse est associée à un compte, vous recevrez un lien de réinitialisation dans quelques minutes.<br><br>
                Vérifiez également vos spams.
            </div>
        </div>

        <p class="hint" style="margin-top:26px">
            <a href="<?= APP_URL ?>/login">← Retour à la connexion</a>
        </p>

        <?php else: ?>

        <p class="eyebrow">Sécurité</p>
        <h2 class="title">Mot de passe oublié ?</h2>
        <p class="subtitle">Saisissez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

        <?php if ($error === 'invalid'): ?>
        <div class="alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            Adresse email invalide. Veuillez vérifier.
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/mot-de-passe-oublie/post">
            <div class="field">
                <label for="email">Adresse email</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email"
                           placeholder="votre@cabinet-smc.sn"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           required autocomplete="email">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                </div>
            </div>

            <button type="submit" class="btn-primary">Envoyer le lien de réinitialisation →</button>
        </form>

        <p class="hint">
            Vous vous souvenez de votre mot de passe ?<br>
            <a href="<?= APP_URL ?>/login">Se connecter</a>
        </p>

        <?php endif; ?>

    </div>
</div>
</body>
</html>
