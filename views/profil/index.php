<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Mon profil</h1>
        <p class="page-subtitle">Informations personnelles et sécurité</p>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#166534">
    ✓ Profil mis à jour <?= isset($_GET['2fa']) ? '— Authentification 2FA activée !' : '' ?>
</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'password'): ?>
<div style="background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#dc3545">
    ✗ Mot de passe invalide : 8 caractères minimum, une majuscule et un chiffre requis.
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px">
    <!-- Informations personnelles -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:20px">Informations personnelles</h3>
        <form method="post" action="<?= APP_URL ?>/profil/update">
            <div class="form-field" style="margin-bottom:16px">
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= e($user['prenom']) ?>" required>
            </div>
            <div class="form-field" style="margin-bottom:16px">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= e($user['nom']) ?>" required>
            </div>
            <div class="form-field" style="margin-bottom:16px">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= e($user['telephone'] ?? '') ?>" placeholder="+221 7X XXX XX XX">
            </div>
            <div style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:16px">
                <div style="font-size:13px;font-weight:500;margin-bottom:12px;color:var(--text)">Changer le mot de passe</div>
                <input type="password" name="password" placeholder="Nouveau mot de passe" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit" minlength="8" pattern="(?=.*[A-Z])(?=.*[0-9]).{8,}">
                <div style="font-size:11px;color:var(--text-muted);margin-top:6px">8 caractères min · 1 majuscule · 1 chiffre — Laisser vide pour ne pas changer</div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Enregistrer</button>
        </form>
    </div>

    <!-- Sécurité 2FA -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:6px">Authentification à deux facteurs</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">Sécurisez votre compte avec une application d'authentification (Google Authenticator, Authy…)</p>

        <?php if ($user['totp_actif']): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:14px;background:rgba(34,197,94,0.06);border:1px solid rgba(34,197,94,0.2);border-radius:10px;margin-bottom:20px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#22c55e" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            <div>
                <div style="font-size:13px;font-weight:600;color:#166534">2FA activée</div>
                <div style="font-size:12px;color:#166534;opacity:0.8">Votre compte est protégé</div>
            </div>
        </div>
        <form method="post" action="<?= APP_URL ?>/profil/disable-2fa">
            <button type="submit" class="btn btn-danger" style="width:100%" onclick="return confirm('Désactiver le 2FA ?')">Désactiver le 2FA</button>
        </form>
        <?php else: ?>
        <div style="display:flex;align-items:center;gap:10px;padding:14px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:10px;margin-bottom:20px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f59e0b" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            <div>
                <div style="font-size:13px;font-weight:600;color:#92400e">2FA non activée</div>
                <div style="font-size:12px;color:#92400e;opacity:0.8">Recommandé pour les comptes admin</div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/profil/setup-2fa" class="btn btn-primary" style="width:100%;justify-content:center">Activer le 2FA</a>
        <?php endif; ?>

        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
            <div style="font-size:12px;color:var(--text-muted)">
                <div style="margin-bottom:6px"><strong>Email :</strong> <?= e($user['email']) ?></div>
                <div style="margin-bottom:6px"><strong>Rôle :</strong> <?= ucfirst(e($user['role'])) ?></div>
                <div><strong>Dernière connexion :</strong> <?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : 'N/A' ?></div>
            </div>
        </div>
    </div>
</div>
