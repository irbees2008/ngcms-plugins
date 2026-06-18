<?php

/**
 * Robokassa adapter for payments plugin
 */
if (!defined('NGCMS')) die('HAL');

/**
 * Redirect user to Robokassa payment form
 */
function payments_adapter_redirect(int $orderId, array $order, array $cfg): void
{
    $login  = $cfg['mrh_login'] ?? '';
    $pass1  = $cfg['mrh_pass1'] ?? '';
    $isTest = !empty($cfg['test_mode']) ? 1 : 0;

    $amount  = number_format((float)$order['total'], 2, '.', '');
    $desc    = 'Заказ #' . $orderId;
    $currency = pluginGetVariable('payments', 'currency') ?: 'RUB';

    $crc = md5("{$login}:{$amount}:{$orderId}:{$currency}:{$pass1}");

    $url = "https://auth.robokassa.ru/Merchant/Index.aspx?"
        . http_build_query([
            'MrchLogin'      => $login,
            'OutSum'         => $amount,
            'InvId'          => $orderId,
            'Desc'           => $desc,
            'OutSumCurrency' => $currency,
            'SignatureValue'  => $crc,
            'IsTest'         => $isTest,
            'ResultURL'      => home . '/payments/notify/?method=robokassa',
            'SuccessURL'     => home . '/payments/success/?order_id=' . $orderId,
            'FailURL'        => home . '/payments/fail/?order_id=' . $orderId,
        ]);

    header('Location: ' . $url);
}

/**
 * Handle IPN callback from Robokassa
 */
function payments_adapter_notify(array $data, array $cfg): void
{
    $pass2  = $cfg['mrh_pass2'] ?? '';
    $amount = $data['OutSum'] ?? '';
    $invId  = intval($data['InvId'] ?? 0);
    $crc    = strtoupper($data['SignatureValue'] ?? '');

    $expectedCrc = strtoupper(md5("{$amount}:{$invId}:{$pass2}"));
    if ($crc !== $expectedCrc) {
        http_response_code(403);
        die("bad sign\n");
    }

    payments_mark_paid($invId, 'robokassa', (string)$invId, $data);
    echo "OK{$invId}\n";
}
