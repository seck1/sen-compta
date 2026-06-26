<?php
// Configuration email SenCompta
// Les valeurs SMTP viennent du .env (SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT, MAIL_FROM)

/** Lit un paramètre SMTP : base de données (app_settings) en priorité, sinon .env. */
function mailSetting(string $key, string $envKey, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $db = function_exists('getDB') ? getDB() : null;
            if ($db) {
                $rows = $db->query("SELECT cle, valeur FROM app_settings WHERE cle LIKE 'smtp_%' OR cle LIKE 'mail_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
                $cache = $rows ?: [];
            }
        } catch (\Throwable $e) { $cache = []; }
    }
    if (!empty($cache[$key])) return (string)$cache[$key];
    $env = getenv($envKey);
    return ($env !== false && $env !== '') ? $env : $default;
}

define('MAIL_FROM',      mailSetting('mail_from', 'MAIL_FROM', 'sencompta1@gmail.com'));
define('MAIL_FROM_NAME', mailSetting('mail_from_name', 'MAIL_FROM_NAME', 'SenCompta'));
define('MAIL_REPLY_TO',  mailSetting('mail_reply_to', 'MAIL_REPLY_TO', MAIL_FROM));
// SMTP (Gmail : host smtp.gmail.com, port 587, user = sencompta1@gmail.com, pass = mot de passe d'application)
define('SMTP_HOST', mailSetting('smtp_host', 'SMTP_HOST', ''));
define('SMTP_PORT', (int)mailSetting('smtp_port', 'SMTP_PORT', '587'));
define('SMTP_USER', mailSetting('smtp_user', 'SMTP_USER', ''));
define('SMTP_PASS', mailSetting('smtp_pass', 'SMTP_PASS', ''));

/**
 * Envoi via SMTP (Gmail) en STARTTLS, sans dépendance externe.
 * Retourne true si l'email est accepté par le serveur SMTP.
 */
function sendMailSmtp(string $to, string $toName, string $subject, string $bodyHtml): bool {
    if (SMTP_HOST === '' || SMTP_USER === '' || SMTP_PASS === '') return false;
    $crlf = "\r\n";
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 15);
    if (!$fp) { error_log("SMTP connect failed: $errstr"); return false; }
    stream_set_timeout($fp, 15);
    $read = function() use ($fp) { $d=''; while($line=fgets($fp,515)){ $d.=$line; if(isset($line[3]) && $line[3]===' ') break; } return $d; };
    $cmd  = function($c) use ($fp,$read){ if($c!==null) fwrite($fp,$c."\r\n"); return $read(); };
    $code = function($r){ return (int)substr(trim($r),0,3); };

    $read(); // greeting
    $host = parse_url(APP_URL ?? 'https://sen-compta.com', PHP_URL_HOST) ?: 'sen-compta.com';
    if ($code($cmd("EHLO $host")) !== 250) { fclose($fp); return false; }
    if ($code($cmd("STARTTLS")) !== 220) { fclose($fp); return false; }
    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
    $cmd("EHLO $host");
    $cmd("AUTH LOGIN");
    if ($code($cmd(base64_encode(SMTP_USER))) !== 334) { fclose($fp); return false; }
    if ($code($cmd(base64_encode(SMTP_PASS))) !== 235) { error_log("SMTP auth failed"); fclose($fp); return false; }
    if ($code($cmd("MAIL FROM:<" . MAIL_FROM . ">")) !== 250) { fclose($fp); return false; }
    if (!in_array($code($cmd("RCPT TO:<$to>")), [250,251])) { fclose($fp); return false; }
    if ($code($cmd("DATA")) !== 354) { fclose($fp); return false; }

    $headers  = "From: =?UTF-8?B?".base64_encode(MAIL_FROM_NAME)."?= <".MAIL_FROM.">".$crlf;
    $headers .= "To: =?UTF-8?B?".base64_encode($toName)."?= <$to>".$crlf;
    $headers .= "Reply-To: ".MAIL_REPLY_TO.$crlf;
    $headers .= "Subject: =?UTF-8?B?".base64_encode($subject)."?=".$crlf;
    $headers .= "MIME-Version: 1.0".$crlf;
    $headers .= "Content-Type: text/html; charset=UTF-8".$crlf;
    $headers .= "Content-Transfer-Encoding: base64".$crlf;
    $data = $headers . $crlf . chunk_split(base64_encode($bodyHtml));
    // protéger les lignes commençant par un point
    $data = preg_replace('/^\./m', '..', $data);
    fwrite($fp, $data . $crlf . "." . $crlf);
    $ok = ($code($read()) === 250);
    $cmd("QUIT");
    fclose($fp);
    return $ok;
}

function sendMail(string $to, string $toName, string $subject, string $bodyHtml, string $bodyText = ''): bool {
    if (empty($to)) return false;

    // 1) SMTP si configuré (Gmail) — fiable depuis un VPS
    if (sendMailSmtp($to, $toName, $subject, $bodyHtml)) return true;

    // 2) Fallback : mail() natif
    $from     = MAIL_FROM;
    $fromName = MAIL_FROM_NAME;

    $boundary = md5(uniqid());
    $headers  = implode("\r\n", [
        'MIME-Version: 1.0',
        "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>",
        "Reply-To: " . MAIL_REPLY_TO,
        "Content-Type: multipart/alternative; boundary=\"$boundary\"",
        'X-Mailer: CabinetSMC/1.0',
    ]);

    if (empty($bodyText)) {
        $bodyText = strip_tags(str_replace(['<br>', '<br/>', '<br />','</p>','</div>'], "\n", $bodyHtml));
        $bodyText = html_entity_decode($bodyText, ENT_QUOTES, 'UTF-8');
    }

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($bodyText)) . "\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($bodyHtml)) . "\r\n";
    $body .= "--$boundary--";

    $subjectEncoded = "=?UTF-8?B?" . base64_encode($subject) . "?=";

    return mail($to, $subjectEncoded, $body, $headers);
}

function mailRelanceClient(string $to, string $clientNom, string $entrepriseNom, float $montant, int $niveau, string $notes = ''): bool {
    $niveaux = [1 => 'Rappel amiable', 2 => 'Relance formelle', 3 => 'Mise en demeure'];
    $label   = $niveaux[$niveau] ?? 'Relance';
    $montantFormate = number_format($montant, 0, ',', ' ') . ' FCFA';

    $subject = "$label — Créance impayée — $entrepriseNom";

    $bodyHtml = "
<!DOCTYPE html>
<html><head><meta charset='UTF-8'></head>
<body style='font-family:Arial,sans-serif;color:#111;background:#f8f9fa;padding:0;margin:0'>
<div style='max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #dde5f0'>
  <div style='background:#1e3a5f;padding:24px 30px;color:#fff'>
    <div style='font-size:20px;font-weight:700'>$entrepriseNom</div>
    <div style='font-size:13px;opacity:.7;margin-top:4px'>$label</div>
  </div>
  <div style='padding:30px'>
    <p style='font-size:15px;margin-bottom:20px'>Madame, Monsieur <strong>$clientNom</strong>,</p>
    <p style='margin-bottom:16px;line-height:1.6'>
      " . ($niveau == 1
        ? "Sauf erreur ou omission de notre part, nous n'avons pas reçu le règlement de la facture dont le détail figure ci-dessous. Nous vous remercions de bien vouloir procéder au règlement dans les meilleurs délais."
        : ($niveau == 2
          ? "Malgré notre précédent rappel, nous constatons que notre facture reste impayée. Nous vous mettons en demeure de régler la somme due dans un délai de <strong>8 jours</strong>."
          : "En l'absence de règlement suite à nos relances précédentes, nous nous verrons dans l'obligation d'engager une procédure de recouvrement judiciaire si le paiement n'est pas effectué sous <strong>48 heures</strong>.")) . "
    </p>
    <div style='background:#fef2f2;border-left:4px solid #dc2626;border-radius:6px;padding:16px 20px;margin:20px 0'>
      <div style='font-size:12px;color:#666;margin-bottom:4px'>MONTANT DÛ</div>
      <div style='font-size:24px;font-weight:700;color:#dc2626;font-family:monospace'>$montantFormate</div>
    </div>
    " . (!empty($notes) ? "<p style='font-size:13px;color:#555;margin-bottom:16px'><em>$notes</em></p>" : "") . "
    <p style='margin-bottom:8px;line-height:1.6'>Pour tout règlement ou information, veuillez contacter notre service comptabilité.</p>
    <p style='margin-top:24px;color:#555;font-size:13px'>Cordialement,<br><strong>$entrepriseNom</strong><br>Service Comptabilité</p>
  </div>
  <div style='background:#f0f3f8;padding:12px 30px;font-size:11px;color:#888;text-align:center'>
    Document généré par SenCompta — " . date('d/m/Y') . "
  </div>
</div>
</body></html>";

    return sendMail($to, $clientNom, $subject, $bodyHtml);
}

function mailNotificationTVA(string $to, string $userName, string $entrepriseNom, int $mois, int $annee): bool {
    $moisLabels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $moisLabel  = $moisLabels[$mois] ?? $mois;
    $subject    = "Rappel déclaration TVA — $moisLabel $annee — $entrepriseNom";

    $bodyHtml = "
<!DOCTYPE html>
<html><head><meta charset='UTF-8'></head>
<body style='font-family:Arial,sans-serif;color:#111;background:#f8f9fa;padding:0;margin:0'>
<div style='max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #dde5f0'>
  <div style='background:#1e3a5f;padding:24px 30px;color:#fff;display:flex;align-items:center;gap:16px'>
    <div style='background:#c9a96e;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px'>⚠</div>
    <div>
      <div style='font-size:18px;font-weight:700'>Rappel fiscal</div>
      <div style='font-size:13px;opacity:.7'>SenCompta — Gestion Comptable</div>
    </div>
  </div>
  <div style='padding:30px'>
    <p style='font-size:15px;margin-bottom:20px'>Bonjour <strong>$userName</strong>,</p>
    <div style='background:#fffbeb;border-left:4px solid #f59e0b;border-radius:6px;padding:16px 20px;margin:20px 0'>
      <div style='font-weight:700;color:#92400e;margin-bottom:6px'>Déclaration TVA à effectuer</div>
      <div style='font-size:14px;color:#78350f'>Dossier : <strong>$entrepriseNom</strong></div>
      <div style='font-size:14px;color:#78350f'>Période : <strong>$moisLabel $annee</strong></div>
    </div>
    <p style='line-height:1.6'>La déclaration de TVA pour la période <strong>$moisLabel $annee</strong> n'a pas encore été enregistrée dans le système. Merci de procéder à son enregistrement dès que possible.</p>
    <div style='margin-top:24px'>
      <a href='" . APP_URL . "/dossier/tva' style='display:inline-block;padding:12px 24px;background:#1e3a5f;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px'>
        Accéder aux déclarations TVA →
      </a>
    </div>
    <p style='margin-top:24px;color:#555;font-size:13px'>Cordialement,<br><strong>SenCompta</strong></p>
  </div>
  <div style='background:#f0f3f8;padding:12px 30px;font-size:11px;color:#888;text-align:center'>
    Notification automatique SenCompta — " . date('d/m/Y') . "
  </div>
</div>
</body></html>";

    return sendMail($to, $userName, $subject, $bodyHtml);
}

/**
 * Email de bienvenue avec code de vérification à 4 chiffres (inscription cabinet).
 */
function mailVerificationCode(string $to, string $cabinetNom, string $code): bool {
    $appUrl = defined('APP_URL') ? APP_URL : 'https://sen-compta.com';
    $logo   = $appUrl . '/logo/logo.png';
    $subject = "Bienvenue sur SenCompta — Votre code : $code";
    $codeSpaced = implode(' ', str_split($code));
    $bodyHtml = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#eef1f0;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f0;padding:32px 12px">
<tr><td align="center">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 10px 40px -12px rgba(30,58,95,.25)">
    <!-- header -->
    <tr><td style="background:linear-gradient(135deg,#1e3a5f,#15293f);padding:34px 36px;text-align:center">
      <img src="'.$logo.'" alt="SenCompta" width="64" height="64" style="display:inline-block;border-radius:14px;background:#fff;padding:8px">
      <div style="color:#fff;font-size:23px;font-weight:700;margin-top:14px;letter-spacing:-.3px">Bienvenue sur SenCompta</div>
      <div style="color:#d9b876;font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:700;margin-top:5px">Le SaaS comptable du Sénégal</div>
    </td></tr>
    <!-- body -->
    <tr><td style="padding:36px 40px">
      <p style="font-size:16px;color:#1e3a5f;margin:0 0 16px;font-weight:600">Bonjour,</p>
      <p style="font-size:15px;color:#3a4750;line-height:1.65;margin:0 0 24px">
        Merci d\'avoir créé l\'espace cabinet <strong style="color:#1e3a5f">'.htmlspecialchars($cabinetNom).'</strong>.
        Pour activer votre compte, saisissez le code de vérification ci-dessous sur la page d\'inscription.
      </p>
      <!-- code box -->
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      <tr><td align="center" style="background:#f6f8f7;border:2px solid #1f6e4e;border-radius:14px;padding:26px 20px">
        <div style="font-size:12px;color:#5e6b62;text-transform:uppercase;letter-spacing:1.5px;font-weight:700">Votre code de vérification</div>
        <div style="font-size:42px;font-weight:800;color:#1f6e4e;letter-spacing:14px;margin:10px 0 4px;font-family:Georgia,serif">'.$codeSpaced.'</div>
        <div style="font-size:12px;color:#9aa39c">Valable 30 minutes</div>
      </td></tr>
      </table>
      <p style="font-size:13.5px;color:#5e6b62;line-height:1.6;margin:26px 0 0">
        Vous n\'êtes pas à l\'origine de cette inscription ? Ignorez simplement cet email, aucun compte ne sera activé.
      </p>
    </td></tr>
    <!-- footer -->
    <tr><td style="background:#f6f8f7;padding:18px 36px;text-align:center;border-top:1px solid #e3e7e5">
      <div style="font-size:12px;color:#8a948c">© '.date('Y').' SenCompta · Comptabilité SYSCOHADA · DGID · IPRES</div>
    </td></tr>
  </table>
</td></tr></table>
</body></html>';
    return sendMail($to, $cabinetNom, $subject, $bodyHtml);
}

/**
 * Notification au super-admin : un nouveau cabinet vient de s\'inscrire.
 */
function mailNouvelleInscriptionAdmin(string $adminEmail, string $cabinetNom, string $email, string $responsable, string $planNom): bool {
    $appUrl = defined('APP_URL') ? APP_URL : 'https://sen-compta.com';
    $subject = "Nouvelle inscription cabinet : $cabinetNom";
    $bodyHtml = '
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;background:#eef1f0;font-family:Arial,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:28px 12px"><tr><td align="center">
  <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #dde2e0">
    <tr><td style="background:#1e3a5f;padding:22px 30px;color:#fff;font-size:17px;font-weight:700">Nouvelle inscription cabinet</td></tr>
    <tr><td style="padding:28px 32px">
      <p style="font-size:14.5px;color:#3a4750;margin:0 0 18px">Un nouveau cabinet vient de créer un espace sur SenCompta :</p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#1e3a5f">
        <tr><td style="padding:8px 0;color:#5e6b62;width:140px">Cabinet</td><td style="padding:8px 0;font-weight:700">'.htmlspecialchars($cabinetNom).'</td></tr>
        <tr><td style="padding:8px 0;color:#5e6b62;border-top:1px solid #eef1ef">Responsable</td><td style="padding:8px 0;border-top:1px solid #eef1ef">'.htmlspecialchars($responsable).'</td></tr>
        <tr><td style="padding:8px 0;color:#5e6b62;border-top:1px solid #eef1ef">Email</td><td style="padding:8px 0;border-top:1px solid #eef1ef">'.htmlspecialchars($email).'</td></tr>
        <tr><td style="padding:8px 0;color:#5e6b62;border-top:1px solid #eef1ef">Formule</td><td style="padding:8px 0;border-top:1px solid #eef1ef">'.htmlspecialchars($planNom).'</td></tr>
        <tr><td style="padding:8px 0;color:#5e6b62;border-top:1px solid #eef1ef">Date</td><td style="padding:8px 0;border-top:1px solid #eef1ef">'.date('d/m/Y H:i').'</td></tr>
      </table>
      <a href="'.$appUrl.'/superadmin/cabinets" style="display:inline-block;margin-top:22px;background:#1f6e4e;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:11px 22px;border-radius:9px">Voir dans l\'admin</a>
    </td></tr>
  </table>
</td></tr></table>
</body></html>';
    return sendMail($adminEmail, 'Admin SenCompta', $subject, $bodyHtml);
}
