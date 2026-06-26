<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Mon profil</h1>
        <p class="page-subtitle">Informations personnelles et sécurité</p>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#166534">
    ✓ Profil mis à jour <?= isset($_GET['2fa']) ? '— Authentification 2FA activée !' : '' ?>
</div>
<?php endif; ?>
<?php if (($_GET['rgpd'] ?? '') === 'demande'): ?>
<div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#166534">
    ✓ Votre demande de suppression a bien été enregistrée. Notre équipe la traitera dans les meilleurs délais et vous tiendra informé.
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
        <h3 style="font-size:14px;font-weight:600;margin-bottom:20px">Informations personnelles</h3>
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
        <h3 style="font-size:14px;font-weight:600;margin-bottom:6px">Authentification à deux facteurs</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">Sécurisez votre compte avec une application d'authentification (Google Authenticator, Authy…)</p>

        <?php if ($user['totp_actif']): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:14px;background:rgba(31,110,78,0.06);border:1px solid rgba(31,110,78,0.2);border-radius:10px;margin-bottom:20px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#1f6e4e" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
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

<?php if (!empty($isSuperAdmin)): ?>
<!-- ===== Paramétrage email (SMTP) — super-admin ===== -->
<div class="card" id="email" style="margin-top:24px">
    <h2 style="font-size:16px;font-weight:700;color:var(--navy);margin-bottom:4px;display:flex;align-items:center;gap:9px">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" style="color:var(--gold)"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        Paramétrage email (SMTP)
    </h2>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px">Configurez l'envoi automatique des emails (codes de vérification, notifications). Une fois enregistré, tout part automatiquement à chaque inscription.</p>
    <div style="margin-bottom:16px">
        <button type="button" onclick="prefillResend()" style="font-size:13px;font-weight:700;color:#fff;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;padding:8px 16px;border-radius:8px;cursor:pointer">⚡ Pré-remplir avec Resend</button>
        <span style="font-size:12px;color:var(--text-muted);margin-left:8px">(remplit tout sauf la clé — colle juste ta clé <code>re_…</code>)</span>
    </div>
    <script>
    function prefillResend(){
        document.getElementById('f_host').value = 'smtp.resend.com';
        document.getElementById('f_port').value = '587';
        document.getElementById('f_user').value = 'resend';
        document.getElementById('f_from').value = 'onboarding@resend.dev';
        document.getElementById('f_fromname').value = 'SenCompta';
        document.getElementById('f_pass').focus();
        document.getElementById('f_pass').placeholder = 'Collez votre clé re_… ici';
    }
    </script>

    <?php if (!empty($smtpFlash)): ?>
    <div style="padding:11px 15px;border-radius:9px;margin-bottom:16px;font-size:13.5px;font-weight:600;<?= $smtpFlash['ok']?'background:rgba(31,110,78,.1);color:#1f6e4e;border:1px solid rgba(31,110,78,.25)':'background:rgba(192,57,43,.08);color:#c0392b;border:1px solid rgba(192,57,43,.25)' ?>"><?= e($smtpFlash['msg']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= APP_URL ?>/profil/smtp">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Serveur SMTP</label>
                <input type="text" id="f_host" name="smtp_host" value="<?= e($smtp['smtp_host']) ?>" placeholder="smtp.resend.com" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Port</label>
                <input type="number" id="f_port" name="smtp_port" value="<?= e($smtp['smtp_port']) ?>" placeholder="587" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Utilisateur</label>
                <input type="text" id="f_user" name="smtp_user" value="<?= e($smtp['smtp_user']) ?>" placeholder="resend" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Mot de passe / clé API</label>
                <input type="password" id="f_pass" name="smtp_pass" value="" placeholder="<?= $smtp['smtp_pass']!==''?'•••••••• (inchangé)':'clé re_… (Resend)' ?>" autocomplete="new-password" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Expéditeur (From)</label>
                <input type="email" id="f_from" name="mail_from" value="<?= e($smtp['mail_from']) ?>" placeholder="onboarding@resend.dev" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Nom expéditeur</label>
                <input type="text" id="f_fromname" name="mail_from_name" value="<?= e($smtp['mail_from_name'] ?: 'SenCompta') ?>" placeholder="SenCompta" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px">
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">Enregistrer la configuration</button>
        </div>
    </form>

    <form method="post" action="<?= APP_URL ?>/profil/smtp/test" style="margin-top:12px;padding-top:14px;border-top:1px solid var(--border)">
        <?= csrfField() ?>
        <button type="submit" class="btn btn-outline">✉ Envoyer un email de test (à <?= e($user['email']) ?>)</button>
    </form>
</div>
<?php endif; ?>

<!-- ===== Mes données personnelles (RGPD) ===== -->
<div class="card" style="margin-top:24px">
    <h2 style="font-size:16px;font-weight:700;color:var(--navy);margin-bottom:4px;display:flex;align-items:center;gap:9px">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
        Mes données personnelles
    </h2>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">Conformément à la réglementation (RGPD &amp; loi sénégalaise 2008-12), vous pouvez exercer vos droits sur vos données.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <!-- Export / portabilite -->
        <div style="border:1px solid var(--border);border-radius:12px;padding:18px">
            <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px">Exporter mes données</div>
            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px">Téléchargez une copie de vos données personnelles au format JSON (droit d'accès et de portabilité).</p>
            <a href="<?= APP_URL ?>/profil/exporter-donnees" class="btn btn-outline" style="width:100%;justify-content:center">⬇ Télécharger (JSON)</a>
        </div>

        <!-- Suppression / droit a l'oubli -->
        <div style="border:1px solid rgba(239,68,68,0.25);border-radius:12px;padding:18px;background:rgba(239,68,68,0.02)">
            <div style="font-size:14px;font-weight:700;color:#b91c1c;margin-bottom:6px">Supprimer mon compte</div>
            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px">Demandez la suppression de votre compte et de vos données personnelles (droit à l'oubli). Les données comptables légalement obligatoires seront conservées le temps requis par la loi.</p>
            <button type="button" class="btn btn-danger" style="width:100%;justify-content:center" onclick="document.getElementById('rgpd-suppr-modal').style.display='flex'">Demander la suppression</button>
        </div>
    </div>
</div>

<!-- Modal demande de suppression -->
<div id="rgpd-suppr-modal" style="display:none;position:fixed;inset:0;background:rgba(18,36,31,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:16px;max-width:460px;width:100%;padding:28px">
        <h3 style="font-size:17px;font-weight:700;color:#b91c1c;margin-bottom:8px">Demander la suppression du compte</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:18px">Cette demande sera transmise à notre équipe. Vous serez recontacté avant toute suppression définitive. Vous pouvez préciser votre demande ci-dessous.</p>
        <form method="post" action="<?= APP_URL ?>/profil/demander-suppression">
            <?= csrfField() ?>
            <textarea name="message" rows="3" placeholder="Précision (optionnel)" style="width:100%;padding:12px;border:1px solid var(--border);border-radius:10px;font-family:inherit;font-size:14px;margin-bottom:16px;resize:vertical"></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('rgpd-suppr-modal').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-danger">Confirmer la demande</button>
            </div>
        </form>
    </div>
</div>
