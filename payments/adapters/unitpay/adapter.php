<?php

/**
 * Unitpay adapter for payments plugin
 */
if (!defined('NGCMS')) die('HAL');

/**
 * Redirect user to Unitpay payment form
 */
function payments_adapter_redirect(int $orderId, array $order, array $cfg): void
{
    $publicKey = $cfg['public_key'] ?? '';
    $projectId = $cfg['project_id'] ?? '';
    $secretKey = $cfg['secret_key'] ?? '';
    $currency  = pluginGetVariable('payments', 'currency') ?: 'RUB';
    $amount    = number_format((float)$order['total'], 2, '.', '');
    $desc      = 'Заказ #' . $orderId;
    $account   = (string)$orderId;

    $signature = hash('sha256', implode('{up}', [$account, $currency, $desc, $amount, $secretKey]));

    $url = "https://unitpay.ru/pay/{$publicKey}?"
        . http_build_query([
            'sum'       => $amount,
            'account'   => $account,
            'desc'      => $desc,
            'currency'  => $currency,
            'signature' => $signature,
            'backUrl'   => home . '/payments/success/?order_id=' . $orderId,
        ]);

    header('Location: ' . $url);
}

/**
 * Handle IPN callback from Unitpay
 */
function payments_adapter_notify(array $data, array $cfg): void
{
    $secretKey = $cfg['secret_key'] ?? '';
    header('Content-Type: application/json');

    $method  = $data['method'] ?? '';
    $params  = $data['params'] ?? [];
    $orderId = intval($params['account'] ?? 0);
    $amount  = $params['orderSum'] ?? '';

    // Verify signature
    $hashStr = $method . '{up}'
        . ($params['account'] ?? '') . '{up}'
        . ($params['currency'] ?? '') . '{up}'
        . ($params['orderSum'] ?? '') . '{up}'
        . $secretKey;
    $expectedSign = hash('sha256', $hashStr);

    if (($params['signature'] ?? '') !== $expectedSign) {
        echo json_encode(['error' => ['message' => 'bad sign']]);
        return;
    }

    if ($method === 'pay') {
        payments_mark_paid($orderId, 'unitpay', $params['unitpayId'] ?? '', $params);
        echo json_encode(['result' => ['message' => 'ok']]);
    } else {
        echo json_encode(['result' => ['message' => 'ok']]);
    }
}
