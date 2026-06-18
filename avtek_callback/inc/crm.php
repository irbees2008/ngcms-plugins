<?php
if (!defined('NGCMS')) { die('HAL'); }

require_once __DIR__ . '/helpers.php';

function avtek_cb_http_post_json(string $url, array $payload, array $headers = [], int $timeout = 12): array {
    $ch = curl_init($url);
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $hdrs = array_merge(['Content-Type: application/json'], $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'ok' => ($resp !== false && $code >= 200 && $code < 300),
        'http' => $code,
        'error' => ($resp === false ? ($err ?: 'cURL error') : ''),
        'raw' => ($resp === false ? '' : $resp),
    ];
}

function avtek_cb_send_to_crm(string $type, array $settings, array $lead): array {
    $type = trim(mb_strtolower($type, 'UTF-8'));

    // Универсальный режим: webhook на ваш обработчик (любой CRM / Zapier / Make / n8n)
    if ($type === 'webhook') {
        $url = trim((string)($settings['url'] ?? ''));
        if ($url === '') {
            return ['ok' => false, 'error' => 'CRM webhook URL is empty'];
        }
        $headers = [];
        if (!empty($settings['header_auth'])) {
            $headers[] = (string)$settings['header_auth'];
        }
        return avtek_cb_http_post_json($url, $lead, $headers);
    }

    // Bitrix24 (самый частый сценарий: входящий вебхук)
    if ($type === 'bitrix24') {
        $url = trim((string)($settings['webhook_url'] ?? ''));
        if ($url === '') {
            return ['ok' => false, 'error' => 'Bitrix24 webhook URL is empty'];
        }
        // ожидаем url вида https://your.bitrix24.ua/rest/1/xxxx/crm.lead.add.json
        $fields = [
            'TITLE' => (string)($lead['title'] ?? 'Заявка с сайта'),
            'NAME' => (string)($lead['data']['Имя'] ?? $lead['data']['Name'] ?? ''),
            'PHONE' => [],
            'EMAIL' => [],
            'COMMENTS' => (string)($lead['message'] ?? ''),
        ];
        $phone = (string)($lead['data']['Номер телефона'] ?? $lead['data']['Телефон'] ?? $lead['data']['Phone'] ?? '');
        if ($phone !== '') {
            $fields['PHONE'][] = ['VALUE' => $phone, 'VALUE_TYPE' => 'WORK'];
        }
        $email = (string)($lead['data']['Єлектронный адрес'] ?? $lead['data']['Email'] ?? '');
        if ($email !== '') {
            $fields['EMAIL'][] = ['VALUE' => $email, 'VALUE_TYPE' => 'WORK'];
        }

        return avtek_cb_http_post_json($url, ['fields' => $fields]);
    }

    // Заготовки под расширение. Реальные OAuth-CRM (amoCRM/Zoho/Salesforce и т.п.)
    // требуют полноценной авторизации и токен-обновления.
    return ['ok' => false, 'error' => 'CRM type not implemented: ' . $type . ' (use Webhook mode)'];
}
