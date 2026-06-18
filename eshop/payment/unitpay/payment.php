<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

function payment_action($payment_name, $payment_options, $rData)
{
    global $config, $mysql, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $SYSTEM_FLAGS;

    $SUPRESS_TEMPLATE_SHOW = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;

    $current_time = time() + ($config['date_adjust'] * 60);
    $result = (int)$rData['result'];

    if (!empty($result)) {
        switch ($result) {
            case '1':
                // fail_url
                redirect_eshop(link_eshop());
                break;
            case '2':
                // result_url

                $method = $rData['method'];
                $params = $rData['params'];
                $secretKey = $payment_options['secretKey'];

                if ($method == 'check') {
                    $message = 'CHECK is successful';

                    return json_encode(
                        array(
                            "jsonrpc" => "2.0",
                            "result" => array(
                                "message" => $message,
                            ),
                        )
                    );

                } elseif ($method == 'pay') {

                    if ($params['sign'] == getMd5Sign($params, $secretKey)) {

                        $merchant_purse = $params;
                        $amount = $rData['OutSum'];
                        $order_id = intval($rData['InvId']);

                        $info = array(
                            'payment_name' => $payment_name,
                            'merchant_purse' => $merchant_purse,
                            'amount' => $amount,
                            'order_id' => $order_id,
                        );

                        $mysql->query(
                            'INSERT INTO '.prefix.'_eshop_purchases (dt, order_id, info)
                            VALUES
                            ('.db_squote($current_time).',
                                '.db_squote($order_id).',
                                '.db_squote(json_encode($info)).'
                            )
                        '
                        );

                        $mysql->query(
                            'UPDATE '.prefix.'_eshop_orders SET
                            paid = 1
                            WHERE id = '.$order_id.'
                        '
                        );

                        $message = 'PAY is successful';

                        return json_encode(
                            array(
                                "jsonrpc" => "2.0",
                                "result" => array(
                                    "message" => $message,
                                ),
                            )
                        );

                    } else {

                        $message = 'Incorrect digital signature';

                        return json_encode(
                            array(
                                "jsonrpc" => "2.0",
                                "error" => array(
                                    "code" => -32000,
                                    "message" => $message,
                                ),
                            )
                        );

                    }

                } else {

                    $message = $method.' not supported';

                    return json_encode(
                        array(
                            "jsonrpc" => "2.0",
                            "error" => array(
                                "code" => -32000,
                                "message" => $message,
                            ),
                        )
                    );
                }

                break;
            case '3':
                // success_url
                redirect_eshop(link_eshop());
                break;
            default:
                break;
        }
    } else {

        $filter = array();
        $SQL = array();

        $order_id = filter_var($rData['order_id'], FILTER_SANITIZE_STRING);
        $uniqid = filter_var($rData['order_uniqid'], FILTER_SANITIZE_STRING);
        if (empty($order_id) || empty($uniqid)) {
            redirect_eshop(link_eshop());
        } else {
            $filter [] = '(id = '.db_squote($order_id).')';
            $filter [] = '(uniqid = '.db_squote($uniqid).')';
            $sqlQ = "SELECT * FROM ".prefix."_eshop_orders ".(count($filter) ? "WHERE ".implode(
                        " AND ",
                        $filter
                    ) : '')." LIMIT 1";
            $row = $mysql->record($sqlQ);

            if ($row['paid'] == 1) {
                redirect_eshop(link_eshop());
            } elseif (!empty($row)) {


                if (!empty($_SERVER['REMOTE_ADDR'])) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }

                $paymentType = "card";
                $sum = $row['total_price'];
                $account = $order_id;
                $projectId = $payment_options['projectId'];
                $secretKey = $payment_options['secretKey'];
                $desc = 'Оплата по заказу ID: '.$order_id;
                $account = $order_id;
                $fail_url = home.'/eshop/payment/?result=1&payment_id=unitpay';
                $result_url = home.'/eshop/payment/?result=2&payment_id=unitpay';
                $success_url = home.'/eshop/payment/?result=3&payment_id=unitpay';
                $currency = $SYSTEM_FLAGS['eshop']['currency'][0]['code'];

                // build URL
                $url = "https://unitpay.ru/api?method=initPayment&".
                    "params[paymentType]=$paymentType&params[sum]=$sum&params[account]=$account&params[projectId]=$projectId&params[secretKey]=$secretKey&params[ip]=$ip&params[resultUrl]=$success_url&params[currency]=$currency";

                header('Location: '.$url.'');

                exit;
            } else {
                redirect_eshop(link_eshop());
            }

        }
    }
}

function getMd5Sign($params, $secretKey)
{
    ksort($params);
    unset($params['sign']);

    return md5(join(null, $params).$secretKey);
}
