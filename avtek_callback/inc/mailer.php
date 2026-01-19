<?php
if (!defined('NGCMS')) {
    die('HAL');
}

require_once __DIR__ . '/helpers.php';

function avtek_cb_send_mail(array $to, string $subject, string $body, string $from = ''): bool
{
    global $config;

    $to = array_values(array_filter($to));
    if (!$to) {
        return false;
    }

    // Prefer PHPMailer if available (vendor autoloaded in core)
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false);

            $fromEmail = trim($from) ?: (trim($config['mailfrom'] ?? '') ?: ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')));
            $fromName  = trim($config['mailfrom_name'] ?? '') ?: 'Site';
            $mail->setFrom($fromEmail, $fromName);

            // SMTP mode if configured
            $mode = strtolower((string)($config['mail_mode'] ?? 'mail'));
            if ($mode === 'smtp' && isset($config['mail']['smtp']) && is_array($config['mail']['smtp'])) {
                $smtp = $config['mail']['smtp'];
                if (!empty($smtp['host'])) {
                    $mail->isSMTP();
                    $mail->Host = (string)$smtp['host'];
                    if (!empty($smtp['port'])) {
                        $mail->Port = (int)$smtp['port'];
                    }
                    $mail->SMTPAuth = !empty($smtp['auth']);
                    if (!empty($smtp['login'])) {
                        $mail->Username = (string)$smtp['login'];
                    }
                    if (!empty($smtp['pass'])) {
                        $mail->Password = (string)$smtp['pass'];
                    }
                    if (!empty($smtp['secure'])) {
                        $mail->SMTPSecure = (string)$smtp['secure'];
                    }
                }
            }

            foreach ($to as $addr) {
                $mail->addAddress($addr);
            }
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $body;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            // continue to fallback
        }
    }

    // If NGCMS has a mail wrapper, use it
    if (function_exists('sendMail')) {
        $ok = true;
        foreach ($to as $addr) {
            $r = @sendMail($addr, $subject, $body);
            $ok = $ok && (bool)$r;
        }
        return $ok;
    }

    // Fallback: native mail()
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $fromHeader = trim($from) ?: (trim($config['mailfrom'] ?? '') ?: '');
    if ($fromHeader) {
        $headers[] = 'From: ' . $fromHeader;
    }
    $hdr = implode("\r\n", $headers);

    $ok = true;
    foreach ($to as $addr) {
        $r = @mail($addr, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $hdr);
        $ok = $ok && (bool)$r;
    }
    return $ok;
}

function avtek_cb_format_mail_body(string $formTitle, array $data, array $meta = []): string
{
    $lines = [];
    $lines[] = 'Заявка с сайта: ' . $formTitle;
    $lines[] = '---';
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $v = implode(', ', $v);
        }
        $lines[] = $k . ': ' . (string)$v;
    }
    $lines[] = '---';
    foreach ($meta as $k => $v) {
        $lines[] = $k . ': ' . (string)$v;
    }
    return implode("\n", $lines);
}
