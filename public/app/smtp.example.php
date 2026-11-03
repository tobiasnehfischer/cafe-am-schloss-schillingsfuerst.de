<?php
/**
 * SMTP-Zugang für das Anfrageformular (PHPMailer).
 *
 * Vorlage: Diese Datei nach  app/smtp.php  kopieren und dort die Zugangsdaten eintragen.
 * app/smtp.php ist per .gitignore vom Repository ausgeschlossen und per .htaccess gesperrt.
 * Test ohne Mailversand:   C:\php85\php.exe tools/smtp-test.php
 * Testmail an mail_to:     C:\php85\php.exe tools/smtp-test.php --send
 */
return [
    'host'       => 'smtp.example.de',
    'port'       => 587,
    'encryption' => 'ssl',
    'username'   => 'info@cafe-am-schloss-schillingsfuerst.de',
    'password'   => '',

    // Absender der Formular-Mails. Muss zum SMTP-Konto passen, sonst lehnt der Server ab oder die Mail landet im Spam.
    'from_email' => 'info@cafe-am-schloss-schillingsfuerst.de',
    'from_name'  => 'Website Café am Schloss',
];
