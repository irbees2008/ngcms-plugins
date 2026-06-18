<?php
if (!defined('NGCMS')) { die('HAL'); }

require_once __DIR__ . '/helpers.php';

function avtek_cb_tg_send(string $token, string $chatId, string $text): array {
    $token = trim($token);
    $chatId = trim($chatId);
    if ($token === '' || $chatId === '') {
        return ['ok' => false, 'error' => 'Empty Telegram token or chat_id'];
    }
    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $post = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return ['ok' => false, 'error' => $err ?: 'cURL error'];
    }

    $data = json_decode($resp, true);
    if (!is_array($data) || empty($data['ok'])) {
        return ['ok' => false, 'error' => 'Telegram API error', 'http' => $code, 'raw' => $resp];
    }

    return ['ok' => true, 'http' => $code];
}

function avtek_cb_format_tg(string $formTitle, array $data, array $meta = []): string {
    $lines = [];
    $lines[] = '<b>Новая заявка</b> (' . avtek_cb_h($formTitle) . ')';
    $lines[] = '';
    foreach ($data as $k => $v) {
        if (is_array($v)) { $v = implode(', ', $v); }
        $lines[] = '<b>' . avtek_cb_h((string)$k) . ':</b> ' . avtek_cb_h((string)$v);
    }
    if ($meta) {
        $lines[] = '';
        foreach ($meta as $k => $v) {
            $lines[] = '<i>' . avtek_cb_h((string)$k) . ':</i> ' . avtek_cb_h((string)$v);
        }
    }
    return implode("\n", $lines);
}
