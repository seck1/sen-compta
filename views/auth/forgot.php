<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SenCompta — Mot de passe oublié</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --navy: #1e3a5f; --navy-dark: #122540; --navy-light: #2a4f7c;
    --gold: #c9a96e; --gold-light: #e2c99a; --gold-dark: #a8843f;
    --white: #ffffff;
}
html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--navy-dark); }

.bg-canvas { position: fixed; inset: 0; z-index: 0; background: linear-gradient(140deg, #0a1628 0%, #1a3352 45%, #0d1f38 100%); }
.bg-grid {
    position: fixed; inset: 0; z-index: 0;
    background-image: linear-gradient(rgba(201,169,110,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(201,169,110,0.04) 1px, transparent 1px);
    background-size: 56px 56px;
}
.orb { position: fixed; border-radius: 50%; filter: blur(90px); opacity: 0.12; animation: float 9s ease-in-out infinite; }
.orb-1 { width: 600px; height: 600px; background: var(--gold); top: -200px; right: -150px; }
.orb-2 { width: 400px; height: 400px; background: #1f6e4e; bottom: -120px; left: -100px; animation-delay: 4s; }
@keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-28px); } }

.page {
    position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh; padding: 40px 20px;
}

.card {
    background: rgba(255,255,255,0.035);
    backdrop-filter: blur(24px);
    border: 1px solid rgba(201,169,110,0.12);
    border-radius: 24px;
    padding: 52px 48px;
    width: 100%; max-width: 480px;
    animation: fadeUp .6s cubic-bezier(.16,1,.3,1) both;
}
@keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

.back-link {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13px; color: rgba(255,255,255,0.4);
    text-decoration: none; margin-bottom: 32px;
    transition: color .2s;
}
.back-link:hover { color: var(--gold); }
.back-link svg { width: 15px; height: 15px; }

.brand { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
.brand img { width: 44px; height: 44px; border-radius: 10px; object-fit: contain; }
.brand-name { font-family: 'Cormorant Garamond', serif; font-size: 20px; color: var(--white); }
.brand-name strong { color: var(--gold); }

.eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; color: var(--gold); text-transform: uppercase;
    letter-spacing: 2.5px; font-weight: 500; margin-bottom: 14px;
}
.eyebrow::before { content:''; display:block; width:24px; height:1.5px; background:var(--gold); opacity:.6; }

.title { font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 400; color: var(--white); line-height: 1.15; margin-bottom: 8px; }
.subtitle { font-size: 14px; color: rgba(255,255,255,0.38); margin-bottom: 32px; line-height: 1.6; }

/* Success state */
.success-box {
    background: rgba(34,197,94,0.08);
    border: 1px solid rgba(34,197,94,0.25);
    border-radius: 14px;
    padding: 24px 20px;
    text-align: center;
}
.success-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(34,197,94,0.12);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.success-title { font-size: 16px; font-weight: 700; color: #6ee7b7; margin-bottom: 8px; }
.success-desc { font-size: 13.5px; color: rgba(255,255,255,0.45); line-height: 1.65; }

/* Alert */
.alert-error {
    display: flex; align-items: center; gap: 12px;
    background: rgba(220,53,69,0.1); border: 1px solid rgba(220,53,69,0.3);
    border-radius: 12px; padding: 14px 18px;
    color: #ff9090; font-size: 14px; margin-bottom: 24px;
}

/* Field */
.field { margin-bottom: 22px; }
.field label { display: block; font-size: 12px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 500; margin-bottom: 10px; }
.input-wrap { position: relative; }
.input-wrap svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: rgba(255,255,255,0.22); pointer-events: none; transition: color .2s; }
.input-wrap:focus-within svg { color: var(--gold); }
.field input {
    width: 100%; padding: 16px 16px 16px 50px;
    background: rgba(255,255,255,0.055); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; color: var(--white); font-size: 15px;
    font-family: 'DM Sans', sans-serif; outline: none; transition: all .22s;
}
.field input::placeholder { color: rgba(255,255,255,0.18); }
.field input:focus { background: rgba(255,255,255,0.08); border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,169,110,0.12); }

.btn-submit {
    width: 100%; padding: 17px; margin-top: 4px;
    background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--gold-dark));
    border: none; border-radius: 12px;
    color: var(--navy-dark); font-size: 15px; font-weight: 700;
    font-family: 'DM Sans', sans-serif; cursor: pointer;
    transition: all .25s; box-shadow: 0 4px 20px rgba(201,169,110,0.3);
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(201,169,110,0.45); }
.btn-submit:active { transform: translateY(0); }

.hint { font-size: 12.5px; color: rgba(255,255,255,0.28); text-align: center; margin-top: 20px; line-height: 1.7; }
.hint a { color: var(--gold); opacity: .7; text-decoration: none; }
.hint a:hover { opacity: 1; }
</style>
</head>
<body>
<div class="bg-canvas"></div>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="page">
    <div class="card">

        <a href="<?= APP_URL ?>/login" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Retour à la connexion
        </a>

        <div class="brand">
            <img src="<?= APP_URL ?>/logo/logo.png" alt="SenCompta">
            <div class="brand-name"><strong>Cabinet</strong> SMC</div>
        </div>

        <?php if ($sent): ?>

        <div class="success-box">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#6ee7b7" style="width:26px;height:26px"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </div>
            <div class="success-title">Email envoyé !</div>
            <div class="success-desc">
                Si cette adresse est associée à un compte, vous recevrez un lien de réinitialisation dans quelques minutes.<br><br>
                Vérifiez également vos spams.
            </div>
        </div>

        <div class="hint" style="margin-top:28px">
            <a href="<?= APP_URL ?>/login">← Retour à la connexion</a>
        </div>

        <?php else: ?>

        <p class="eyebrow">Sécurité</p>
        <h2 class="title">Mot de passe<br>oublié ?</h2>
        <p class="subtitle">Saisissez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

        <?php if ($error === 'invalid'): ?>
        <div class="alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
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

            <button type="submit" class="btn-submit">Envoyer le lien de réinitialisation →</button>
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
