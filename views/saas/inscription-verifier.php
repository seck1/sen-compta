<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vérification email — SenCompta</title>
<link rel="icon" type="image/svg+xml" href="/logo/sencompta-icon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..600&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--green:#1f6e4e;--green-dark:#164d37;--navy:#1e3a5f;--gold:#b8923f;--bg:#eef1f0;--ink:#18241f;--muted:#5e6b62;--line:#d9dcdb}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border:1px solid var(--line);border-radius:20px;max-width:440px;width:100%;padding:40px 38px;box-shadow:0 20px 60px -24px rgba(30,58,95,.28);text-align:center}
.logo{width:72px;height:72px;border-radius:18px;object-fit:contain;padding:10px;background:linear-gradient(160deg,#f2f7f4,#e9f1ec);box-shadow:inset 0 0 0 1px rgba(31,110,78,.12);margin:0 auto 18px}
h1{font-family:'Fraunces',serif;font-size:27px;font-weight:600;color:var(--navy);letter-spacing:-.4px;line-height:1.15}
.sub{font-size:14.5px;color:var(--muted);margin:10px 0 4px;line-height:1.55}
.sub b{color:var(--navy)}
.flash{padding:12px 16px;border-radius:11px;font-size:13.5px;font-weight:600;margin:20px 0 0}
.flash.err{background:rgba(192,57,43,.08);color:#c0392b;border:1px solid rgba(192,57,43,.25)}
.flash.ok{background:rgba(31,110,78,.10);color:#1f6e4e;border:1px solid rgba(31,110,78,.25)}
.code-inputs{display:flex;gap:12px;justify-content:center;margin:28px 0 8px}
.code-inputs input{width:62px;height:74px;text-align:center;font-size:32px;font-weight:700;font-family:'Fraunces',serif;
  color:var(--navy);border:2px solid var(--line);border-radius:14px;background:#fafbfb;transition:.15s;outline:none}
.code-inputs input:focus{border-color:var(--green);background:#fff;box-shadow:0 0 0 4px rgba(31,110,78,.12)}
.btn{display:block;width:100%;margin-top:24px;padding:15px;border:none;border-radius:12px;background:linear-gradient(180deg,#2a8a63,var(--green));
  color:#fff;font-size:15.5px;font-weight:700;font-family:inherit;cursor:pointer;transition:.2s;box-shadow:0 10px 24px -12px rgba(31,110,78,.7)}
.btn:hover{filter:brightness(1.05);transform:translateY(-1px)}
.resend{margin-top:20px;font-size:13.5px;color:var(--muted)}
.resend form{display:inline}
.resend button{background:none;border:none;color:var(--green);font-weight:700;font-size:13.5px;cursor:pointer;font-family:inherit;text-decoration:underline}
.expire{font-size:12.5px;color:#9aa39c;margin-top:14px}
</style>
</head>
<body>
<div class="card">
  <img src="/logo/sencompta-icon.svg" alt="SenCompta" class="logo">
  <h1>Vérifiez votre email</h1>
  <p class="sub">Nous avons envoyé un code à 4 chiffres à<br><b><?= htmlspecialchars($email) ?></b></p>

  <?php if (!empty($error)): ?><div class="flash err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if (!empty($info)): ?><div class="flash ok"><?= htmlspecialchars($info) ?></div><?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/inscription/verifier/post" id="verif-form">
    <?= csrfField() ?>
    <input type="hidden" name="code" id="code-hidden">
    <div class="code-inputs">
      <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" autofocus>
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
    </div>
    <button type="submit" class="btn">Valider mon compte</button>
  </form>

  <div class="resend">
    Vous n'avez pas reçu le code ?
    <form method="POST" action="<?= APP_URL ?>/inscription/renvoyer-code">
      <?= csrfField() ?>
      <button type="submit">Renvoyer le code</button>
    </form>
  </div>
  <div class="expire">Le code est valable 30 minutes. Pensez à vérifier vos spams.</div>
</div>

<script>
(function(){
  var inputs = Array.prototype.slice.call(document.querySelectorAll('.code-inputs input'));
  var hidden = document.getElementById('code-hidden');
  var form = document.getElementById('verif-form');
  function sync(){ hidden.value = inputs.map(function(i){return i.value;}).join(''); }
  inputs.forEach(function(inp,idx){
    inp.addEventListener('input', function(){
      inp.value = inp.value.replace(/\D/g,'');
      if(inp.value && idx<inputs.length-1) inputs[idx+1].focus();
      sync();
      if(inputs.every(function(i){return i.value;})) form.submit();
    });
    inp.addEventListener('keydown', function(e){
      if(e.key==='Backspace' && !inp.value && idx>0) inputs[idx-1].focus();
    });
    inp.addEventListener('paste', function(e){
      e.preventDefault();
      var d=(e.clipboardData.getData('text')||'').replace(/\D/g,'').slice(0,4).split('');
      d.forEach(function(c,i){ if(inputs[i]) inputs[i].value=c; });
      sync(); if(d.length===4) form.submit();
    });
  });
})();
</script>
</body>
</html>
