<?php

/**
 * Email sending functions for Mailing plugin
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 */

if (!defined('NGCMS')) die('HAL');

/**
 * Отправить email
 *
 * @param string $toEmail Email получателя
 * @param string $toName Имя получателя
 * @param string $subject Тема письма
 * @param string $htmlBody HTML-версия письма
 * @param string $textBody Текстовая версия
 * @param array $attachments Вложения
 * @param array $headersExtra Дополнительные заголовки
 * @return array [bool $success, string $error]
 */
function mailing_send_email(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $textBody,
    array $attachments = [],
    array $headersExtra = []
): array {
    // Попытка использовать PHPMailer
    if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
        return mailing_send_phpmailer($toEmail, $toName, $subject, $htmlBody, $textBody, $attachments, $headersExtra);
    }

    // Fallback на стандартную mail()
    return mailing_send_mail_fallback($toEmail, $toName, $subject, $htmlBody, $textBody, $attachments, $headersExtra);
}

/**
 * Отправка email через PHPMailer
 */
function mailing_send_phpmailer(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $textBody,
    array $attachments = [],
    array $headersExtra = []
): array {
    $fromEmail = mailing_cfg('from_email', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $fromName = mailing_cfg('from_name', 'Site');
    $replyTo = mailing_cfg('reply_to', $fromEmail);
    $useSMTP = mailing_cfg_bool('smtp_enable', false);

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        if ($useSMTP) {
            $mail->isSMTP();
            $mail->Host = mailing_cfg('smtp_host', '');
            $mail->Port = (int)mailing_cfg('smtp_port', '587');
            $mail->SMTPAuth = mailing_cfg_bool('smtp_auth', true);
            $mail->Username = mailing_cfg('smtp_user', '');
            $mail->Password = mailing_cfg('smtp_pass', '');

            $secure = trim(mailing_cfg('smtp_secure', 'tls'));
            if ($secure) {
                $mail->SMTPSecure = $secure;
            }
        }

        $mail->setFrom($fromEmail, $fromName);

        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }

        $mail->addAddress($toEmail, $toName ?: $toEmail);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);

        // Дополнительные заголовки
        foreach ($headersExtra as $key => $value) {
            $mail->addCustomHeader($key, $value);
        }

        // Вложения
        foreach ($attachments as $attachment) {
            if (empty($attachment['path']) || !file_exists($attachment['path'])) {
                continue;
            }

            $mail->addAttachment(
                $attachment['path'],
                $attachment['name'] ?? basename($attachment['path']),
                'base64',
                $attachment['type'] ?? ''
            );
        }

        $mail->send();
        return [true, ''];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}

/**
 * Fallback отправка через стандартную функцию mail()
 */
function mailing_send_mail_fallback(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $textBody,
    array $attachments = [],
    array $headersExtra = []
): array {
    $fromEmail = mailing_cfg('from_email', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $fromName = mailing_cfg('from_name', 'Site');
    $replyTo = mailing_cfg('reply_to', $fromEmail);

    $boundaryMixed = 'b1_' . md5(uniqid('', true));
    $boundaryAlt = 'b2_' . md5(uniqid('', true));

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'From: ' . mailing_encode_header($fromName) . ' <' . $fromEmail . '>';

    if ($replyTo) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    foreach ($headersExtra as $key => $value) {
        $headers[] = "{$key}: {$value}";
    }

    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundaryMixed . '"';

    $body = "--{$boundaryMixed}\r\n";
    $body .= 'Content-Type: multipart/alternative; boundary="' . $boundaryAlt . "\"\r\n\r\n";

    // Текстовая часть
    $body .= "--{$boundaryAlt}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= ($textBody ?: strip_tags($htmlBody)) . "\r\n\r\n";

    // HTML часть
    $body .= "--{$boundaryAlt}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $htmlBody . "\r\n\r\n";

    $body .= "--{$boundaryAlt}--\r\n";

    // Вложения
    foreach ($attachments as $attachment) {
        if (empty($attachment['path']) || !file_exists($attachment['path'])) {
            continue;
        }

        $file = $attachment['path'];
        $name = $attachment['name'] ?? basename($file);
        $type = $attachment['type'] ?? 'application/octet-stream';
        $data = chunk_split(base64_encode(file_get_contents($file)));

        $body .= "--{$boundaryMixed}\r\n";
        $body .= "Content-Type: {$type}; name=\"" . mailing_encode_header($name) . "\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= 'Content-Disposition: attachment; filename="' . mailing_encode_header($name) . "\"\r\n\r\n";
        $body .= $data . "\r\n";
    }

    $body .= "--{$boundaryMixed}--\r\n";

    $to = $toName ? mailing_encode_header($toName) . " <{$toEmail}>" : $toEmail;

    $success = @mail($to, mailing_encode_header($subject), $body, implode("\r\n", $headers));
    return [$success, $success ? '' : 'mail() failed'];
}

/**
 * Кодирование заголовка по RFC2047
 */
function mailing_encode_header(string $string): string
{
    if (empty($string)) {
        return '';
    }

    return '=?UTF-8?B?' . base64_encode($string) . '?=';
}
