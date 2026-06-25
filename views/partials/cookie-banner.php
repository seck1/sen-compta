<!-- Bandeau de consentement cookies (RGPD / CDP). Cookies strictement necessaires uniquement. -->
<div id="cookie-banner" style="display:none;position:fixed;left:0;right:0;bottom:0;z-index:300;background:#1e3a5f;color:#fff;padding:16px 20px;box-shadow:0 -6px 24px rgba(0,0,0,.25)">
  <div style="max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:18px;flex-wrap:wrap">
    <div style="flex:1;min-width:260px;font-size:13.5px;line-height:1.55">
      🍪 SenCompta utilise uniquement des cookies <strong>strictement nécessaires</strong> à votre connexion et à la sécurité. Aucun traceur publicitaire.
      <a href="<?= APP_URL ?>/cookies" style="color:#b8923f;font-weight:600;text-decoration:none">En savoir plus</a>
    </div>
    <button onclick="acceptCookies()" style="background:linear-gradient(180deg,#2a8a63,#1f6e4e);color:#fff;border:none;padding:11px 22px;border-radius:999px;font-family:inherit;font-size:13.5px;font-weight:700;cursor:pointer;white-space:nowrap">J'ai compris</button>
  </div>
</div>
<script>
(function(){
  try{
    if(!localStorage.getItem('sc_cookie_consent')){
      document.getElementById('cookie-banner').style.display='block';
    }
  }catch(e){ document.getElementById('cookie-banner').style.display='block'; }
})();
function acceptCookies(){
  try{ localStorage.setItem('sc_cookie_consent', new Date().toISOString()); }catch(e){}
  document.getElementById('cookie-banner').style.display='none';
}
</script>
