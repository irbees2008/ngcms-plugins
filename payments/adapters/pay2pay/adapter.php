<?php

/**
 * Pay2Pay adapter for payments plugin
 */
if (!defined('NGCMS')) die('HAL');

/**
 * Redirect user to Pay2Pay payment form
 */
function payments_adapter_redirect(int $orderId, array $order, array $cfg): void
{
    $shopId = $cfg['shop_id'] ?? '';
    $secret = $cfg['secret']  ?? '';
    $amount = number_format((float)$order['total'], 2, '.', '');
    $desc   = 'Заказ #' . $orderId;

    $sign = md5($shopId . ':' . $orderId . ':' . $amount . ':' . $secret);

    $url = 'https://pay2pay.ru/pay?' . http_build_query([
        'shop_id'    => $shopId,
        'order_id'   => $orderId,
        'amount'     => $amount,
        'description' => $desc,
        'sign'       => $sign,
        'success_url' => home . '/payments/success/?order_id=' . $orderId,
        'fail_url'   => home . '/payments/fail/?order_id=' . $orderId,
        'result_url' => home . '/payments/notify/?method=pay2pay',
    ]);

    header('Location: ' . $url);
}

/**
 * Handle IPN callback from Pay2Pay
 */
function payments_adapter_notify(array $data, array $cfg): void
{
    $secret  = $cfg['secret']  ?? '';
    $shopId  = $cfg['shop_id'] ?? '';
    $orderId = intval($data['order_id'] ?? 0);
    $amount  = $data['amount'] ?? '';
    $sign    = $data['sign']   ?? '';
    $status  = $data['status'] ?? '';

    $expectedSign = md5($shopId . ':' . $orderId . ':' . $amount . ':' . $secret);
    if (!hash_equals($expectedSign, $sign)) {
        http_response_code(403);
        die('bad sign');
    }

    if ($status === 'success') {
        payments_mark_paid($orderId, 'pay2pay', $data['transaction_id'] ?? '', $data);
        echo 'OK';
    }
}
