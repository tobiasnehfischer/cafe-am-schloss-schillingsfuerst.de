<?php
/**
 * SMTP-Test für das Anfrageformular (nur lokal per Kommandozeile, wird nicht hochgeladen).
 *
 *   C:\php85\php.exe tools/smtp-test.php          Verbindung + Anmeldung prüfen, KEINE Mail
 *   C:\php85\php.exe tools/smtp-test.php --send   zusätzlich eine Testmail an mail_to senden
 *
 * Liest public/app/smtp.php (Vorlage: public/app/smtp.example.php). Das Passwort wird nicht ausgegeben.
 */
if (PHP_SAPI !== 'cli') {
    exit("Nur per Kommandozeile.\n");
}
require dirname(__DIR__) . '/public/app/init.php';
require APP . '/mailer.php';

$smtp = smtp_config();
if ($smtp === null) {
    fwrite(STDERR, "public/app/smtp.php fehlt oder ist unvollständig (host/from_email). Vorlage: smtp.example.php\n");
    exit(1);
}
printf("Server: %s:%d (%s), Benutzer: %s, Absender: %s, Empfänger: %s\n",
    $smtp['host'], $smtp['port'], $smtp['encryption'] ?: 'unverschlüsselt',
    $smtp['username'] ?: '(ohne Anmeldung)', $smtp['from_email'], config('mail_to'));

try {
    $mail = smtp_mailer();
    if (!$mail->smtpConnect()) {
        throw new RuntimeException('Verbindung/Anmeldung fehlgeschlagen: ' . $mail->ErrorInfo);
    }
    echo "OK: Verbindung und Anmeldung erfolgreich.\n";
    $mail->smtpClose();

    if (in_array('--send', $argv, true)) {
        $mail = smtp_mailer();
        $mail->addAddress((string) config('mail_to'));
        $mail->Subject = 'Testmail Website Café am Schloss – ' . date('d.m.Y H:i');
        $mail->Body    = "Diese Testmail wurde mit tools/smtp-test.php gesendet.\nUmlaute: äöü ß – passt.\n";
        $mail->send();
        echo 'OK: Testmail an ' . config('mail_to') . " gesendet.\n";
    }
} catch (Throwable $ex) {
    fwrite(STDERR, 'FEHLER: ' . $ex->getMessage() . "\n");
    exit(1);
}
