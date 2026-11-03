<?php
declare(strict_types=1);

/**
 * Mailversand über SMTP mit PHPMailer (app/vendor, per Composer installiert).
 * Zugangsdaten: app/smtp.php (Vorlage app/smtp.example.php).
 */

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once APP . '/vendor/autoload.php';

/** SMTP-Einstellungen oder null, wenn app/smtp.php fehlt bzw. unvollständig ist. */
function smtp_config(): ?array
{
    $file = APP . '/smtp.php';
    if (!is_file($file)) {
        return null;
    }
    $cfg = require $file;
    if (!is_array($cfg) || empty($cfg['host']) || empty($cfg['from_email'])) {
        return null;
    }
    return $cfg + ['port' => 587, 'encryption' => 'tls', 'username' => '', 'password' => '', 'from_name' => config('site_name')];
}

/** Fertig konfigurierter PHPMailer (SMTP, UTF-8) oder Exception. */
function smtp_mailer(): PHPMailer
{
    $smtp = smtp_config();
    if ($smtp === null) {
        throw new RuntimeException('SMTP ist nicht eingerichtet (app/smtp.php fehlt oder ist unvollständig).');
    }
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = (string) $smtp['host'];
    $mail->Port       = (int) $smtp['port'];
    $mail->SMTPAuth   = $smtp['username'] !== '';
    $mail->Username   = (string) $smtp['username'];
    $mail->Password   = (string) $smtp['password'];
    $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS
        : ($smtp['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : '');
    $mail->SMTPAutoTLS = $smtp['encryption'] !== '';
    $mail->Timeout    = 15;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;
    $mail->Encoding   = PHPMailer::ENCODING_QUOTED_PRINTABLE;
    $mail->setLanguage('de');
    $mail->setFrom((string) $smtp['from_email'], (string) $smtp['from_name']);
    return $mail;
}

/**
 * Sendet eine Textmail an mail_to aus der config. Gibt true/false zurück, Fehler landen im PHP-Error-Log.
 * Auf dem lokalen Entwicklungsserver (php -S) wird nicht gesendet, sondern nach data/mail-dev.log geschrieben.
 */
function send_site_mail(string $subject, string $body, string $replyTo = '', string $replyName = ''): bool
{
    if (PHP_SAPI === 'cli-server') {
        $log = "=== $subject\nReply-To: $replyName <$replyTo>\n$body\n";
        return file_put_contents(DATA_DIR . '/mail-dev.log', $log, FILE_APPEND | LOCK_EX) !== false;
    }
    try {
        // Empfänger kommt AUSSCHLIESSLICH aus app/config.php (mail_to) – niemals aus Formulardaten.
        // Die Adresse des Anfragenden wird nur als Reply-To gesetzt; an sie wird nichts gesendet.
        $to = (string) config('mail_to');
        if (!PHPMailer::validateAddress($to)) {
            throw new RuntimeException('mail_to in app/config.php ist keine gültige Adresse.');
        }
        $mail = smtp_mailer();
        $mail->addAddress($to);
        if ($replyTo !== '' && PHPMailer::validateAddress($replyTo)) {
            $mail->addReplyTo($replyTo, $replyName);
        }
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->isHTML(false);
        return $mail->send();
    } catch (MailException | RuntimeException $ex) {
        error_log('Café am Schloss – Mailversand fehlgeschlagen: ' . $ex->getMessage());
        return false;
    }
}
