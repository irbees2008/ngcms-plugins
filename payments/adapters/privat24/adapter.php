<?php

/**
 * Privat24 adapter for payments plugin
 */
if (!defined('NGCMS')) die('HAL');

/**
 * Redirect user to Privat24 payment form
 */
function payments_adapter_redirect(int $orderId, array $order, array $cfg): void
{
    $merchantId       = $cfg['merchant_id']       ?? '';
    $merchantPassword = $cfg['merchant_password']  ?? '';
    $currency         = pluginGetVariable('payments', 'currency') ?: 'UAH';
    $amount           = number_format((float)$order['total'], 2, '.', '');

    $data = base64_encode(
        '<?xml version="1.0" encoding="UTF-8"?>'
            . '<request><version>1.0</version>'
            . '<merchant><id>' . htmlspecialchars($merchantId, ENT_XML1) . '</id>'
            . '<signature>' . sha1(md5("amount={$amount}&currency={$currency}&merchant_id={$merchantId}&order_id={$orderId}&return_url=" . home . "/payments/success/?order_id={$orderId}&server_url=" . home . "/payments/notify/?method=privat24{$merchantPassword}")) . '</signature>'
            . '</merchant>'
            . '<purchase>'
            . '<order><merchant_order_id>' . $orderId . '</merchant_order_id>'
            . '<description>Заказ #' . $orderId . '</description>'
            . '<currency>' . htmlspecialchars($currency, ENT_XML1) . '</currency>'
            . '<amount>' . $amount . '</amount>'
            . '</order>'
            . '<return_url>' . home . '/payments/success/?order_id=' . $orderId . '</return_url>'
            . '<server_url>' . home . '/payments/notify/?method=privat24</server_url>'
            . '</purchase>'
            . '</request>'
    );

    $url = 'https://api.privatbank.ua/p24api/ishop?' . http_build_query(['data' => $data]);
    header('Location: ' . $url);
}

/**
 * Handle IPN callback from Privat24
 */
function payments_adapter_notify(array $data, array $cfg): void
{
    $merchantPassword = $cfg['merchant_password'] ?? '';

    $rawData = $data['data'] ?? '';
    if (empty($rawData)) {
        die('Bad request');
    }

    $decoded = base64_decode($rawData);
    $xml     = @simplexml_load_string($decoded);
    if (!$xml) {
        die('Bad XML');
    }

    $orderId   = intval((string)($xml->purchase->order->merchant_order_id ?? 0));
    $state     = (string)($xml->purchase->order->state ?? '');
    $amount    = (string)($xml->purchase->order->amount ?? '');
    $currency  = (string)($xml->purchase->order->currency ?? '');
    $merchantId = (string)($xml->merchant->id ?? '');

    $signSrc  = md5("amount={$amount}&currency={$currency}&merchant_id={$merchantId}&order_id={$orderId}"
        . "&return_url=" . home . "/payments/success/?order_id={$orderId}"
        . "&server_url=" . home . "/payments/notify/?method=privat24"
        . $merchantPassword);
    $expectedSign = sha1($signSrc);
    $receivedSign = (string)($xml->merchant->signature ?? '');

    if ($expectedSign !== $receivedSign) {
        die('bad sign');
    }

    if ($state === 'ok') {
        payments_mark_paid($orderId, 'privat24', (string)$orderId, ['state' => $state, 'amount' => $amount]);
        echo 'OK';
    }
}
