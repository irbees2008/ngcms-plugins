<?php

/**
 * LiqPay adapter for payments plugin
 */
if (!defined('NGCMS')) die('HAL');

include_once __DIR__ . '/LiqPay.php';

/**
 * Redirect user to LiqPay payment form
 */
function payments_adapter_redirect(int $orderId, array $order, array $cfg): void
{
    $publicKey  = $cfg['public_key'] ?? '';
    $privateKey = $cfg['private_key'] ?? '';
    $currency   = pluginGetVariable('payments', 'currency') ?: 'UAH';

    $liqpay = new LiqPay($publicKey, $privateKey);
    $html   = $liqpay->cnb_form([
        'action'      => 'pay',
        'amount'      => number_format((float)$order['total'], 2, '.', ''),
        'currency'    => $currency,
        'description' => 'Заказ #' . $orderId,
        'order_id'    => 'order_' . $orderId,
        'version'     => '3',
        'result_url'  => home . '/payments/notify/?method=liqpay&result=2',
        'server_url'  => home . '/payments/notify/?method=liqpay&result=2',
    ]);

    // Output LiqPay form and auto-submit
    echo '<!DOCTYPE html><html><head><title>Оплата...</title></head><body>'
        . '<p>Перенаправление на страницу оплаты...</p>'
        . $html
        . '<script>document.forms[0].submit();</script>'
        . '</body></html>';
}

/**
 * Handle IPN callback from LiqPay
 */
function payments_adapter_notify(array $data, array $cfg): void
{
    $privateKey = $cfg['private_key'] ?? '';

    if (empty($data['data']) || empty($data['signature'])) {
        die('Bad request');
    }

    $expectedSign = base64_encode(sha1($privateKey . $data['data'] . $privateKey, true));
    if ($expectedSign !== $data['signature']) {
        http_response_code(403);
        die('bad sign');
    }

    $payload = json_decode(base64_decode($data['data']), true);
    if (!$payload) {
        die('bad data');
    }

    $parts   = explode('_', $payload['order_id'] ?? '');
    $orderId = intval(end($parts));

    if (in_array($payload['status'] ?? '', ['success', 'sandbox'], true)) {
        payments_mark_paid($orderId, 'liqpay', $payload['payment_id'] ?? '', $payload);
        echo 'OK';
    }
}
