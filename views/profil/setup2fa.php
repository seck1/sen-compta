<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Configurer le 2FA</h1>
        <p class="page-subtitle">Authentification à deux facteurs</p>
    </div>
    <a href="<?= APP_URL ?>/profil" class="btn btn-outline btn-sm">← Annuler</a>
</div>

<?php if (isset($_GET['error'])): ?>
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#dc2626">
    Code invalide. Vérifiez l'heure de votre téléphone et réessayez.
</div>
<?php endif; ?>

<div class="card" style="max-width:480px">
    <div style="text-align:center;margin-bottom:28px">
        <div style="font-size:14px;color:var(--text-muted);margin-bottom:16px">
            1. Ouvrez <strong>Google Authenticator</strong> ou <strong>Authy</strong> sur votre téléphone<br>
            2. Scannez ce QR code ou entrez la clé manuellement<br>
            3. Entrez le code à 6 chiffres affiché
        </div>
        <!-- QR code généré localement en JS — le secret ne transite pas vers un service tiers -->
        <div id="qrcode" style="display:inline-block;border:1px solid var(--border);border-radius:12px;padding:8px;background:#fff"></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
        new QRCode(document.getElementById('qrcode'), {
            text: <?= json_encode($otpauthUri) ?>,
            width: 200, height: 200,
            colorDark: '#1e3a5f', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
        </script>
        <div style="margin-top:12px;font-size:12px;color:var(--text-muted)">Clé manuelle :</div>
        <div style="font-family:monospace;font-size:14px;font-weight:600;letter-spacing:3px;color:var(--navy);margin-top:4px"><?= e(chunk_split($_SESSION['totp_pending'] ?? '', 4, ' ')) ?></div>
    </div>

    <form method="post" action="<?= APP_URL ?>/profil/confirm-2fa">
        <div class="form-field" style="margin-bottom:20px">
            <label style="text-align:center;display:block">Code de vérification (6 chiffres)</label>
            <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000"
                   style="text-align:center;font-size:24px;letter-spacing:6px;font-family:monospace" autofocus required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Activer le 2FA</button>
    </form>
</div>
