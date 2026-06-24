<?php
// Configuration email SenCompta
// Modifier ces valeurs selon votre serveur SMTP

define('MAIL_FROM',     'noreply@sen-compta.com');
define('MAIL_FROM_NAME','SenCompta');
define('MAIL_REPLY_TO', 'contact@sen-compta.com');

// Pour utiliser un vrai SMTP (ex: Gmail, Mailgun), installer PHPMailer
// Pour l'instant on utilise mail() natif de PHP / XAMPP sendmail

function sendMail(string $to, string $toName, string $subject, string $bodyHtml, string $bodyText = ''): bool {
    if (empty($to)) return false;

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
