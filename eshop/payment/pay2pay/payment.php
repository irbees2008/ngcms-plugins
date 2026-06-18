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
                $rData['sign'] = str_replace(' ', '+', $rData['sign']);
                $rData['xml'] = str_replace(' ', '+', $rData['xml']);

                // result_url
                if (!empty($rData['xml']) and !empty($rData['sign'])) {
                    $error = '';
                    $xml_encoded = str_replace(' ', '+', $rData['xml']);
                    $xml = base64_decode($xml_encoded);
                    $xml_vars = simplexml_load_string($xml);

                    if ($xml_vars->order_id) {

                        $hidden_key = $payment_options['hidden_key'];
                        $sign = md5($hidden_key.$xml.$hidden_key);
                        $sign_encode = base64_encode($sign);

                        $a_or_id = explode("_", $xml_vars->order_id);
                        $zid = $a_or_id[1];
                        $merchant_id = (string)$xml_vars->merchant_id;
                        $order_id = (string)$xml_vars->order_id;
                        $amount = (string)$xml_vars->amount;
                        $currency = (string)$xml_vars->currency;
                        $description = (string)$xml_vars->description;
                        $description = iconv("utf-8", "windows-1251", $description);
                        $paymode = (string)$xml_vars->paymode;
                        $trans_id = (string)$xml_vars->trans_id;
                        $status = (string)$xml_vars->status;
                        $error_msg = (string)$xml_vars->error_msg;
                        $test_mode = (string)$xml_vars->test_mode;

                        $info = array(
                            'payment_name' => $payment_name,
                            'merchant_id' => $merchant_id,
                            'amount' => $amount,
                            'currency' => $currency,
                            'description' => $description,
                            'paymode' => $paymode,
                            'trans_id' => $trans_id,
                            'status' => $status,
                            'error_msg' => $error_msg,
                            'test_mode' => $test_mode,
                        );

                        if ($sign_encode == $rData['sign']) {

                            if ($status == 'success') {

                                $mysql->query(
                                    'INSERT INTO '.prefix.'_eshop_purchases (dt, order_id, info)
                                    VALUES
                                    ('.db_squote($current_time).',
                                        '.db_squote($zid).',
                                        '.db_squote(json_encode($info)).'
                                    )
                                '
                                );

                                $mysql->query(
                                    'UPDATE '.prefix.'_eshop_orders SET
                                    paid = 1
                                    WHERE id = '.$zid.'
                                '
                                );
                            }

                        } else {
                            $error = 'Incorrect sign';
                        }
                    } else {
                        $error = 'Unknown order_id';
                    }

                    // Отвечаем серверу Pay2Pay
                    if ($error == '') {
                        $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                        <result>
                        <status>yes</status>
                        <err_msg></err_msg>
                        </result>";
                    } else {
                        $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                        <result>
                        <status>no</status>
                        <err_msg>$error</err_msg>
                        </result>";
                    }

                    die($ret);
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
                $merchant_id = $payment_options['merchant_id']; // Идентификатор магазина в Pay2Pay
                $secret_key = $payment_options['secret_key']; // Секретный ключ
                $hash_order_id = $current_time."_".$order_id; // Номер заказа
                $amount = $row['total_price']; // Сумма заказа
                $currency = $SYSTEM_FLAGS['eshop']['currency'][0]['code']; // Валюта заказа
                $desc = 'Оплата по заказу ID: '.$order_id; // Описание заказа
                $desc = iconv("windows-1251", "utf-8", $desc);
                $test_mode = $payment_options['test_mode']; // Тестовый режим
                // Формируем xml
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                 <request>
                 <version>1.2</version>
                 <merchant_id>$merchant_id</merchant_id>
                 <language>ru</language>
                 <order_id>$hash_order_id</order_id>
                 <amount>$amount</amount>
                 <currency>$currency</currency>
                 <description>$desc</description>
                 <test_mode>$test_mode</test_mode>
                 <other><![CDATA[$order_id]]></other>
                 </request>";
                // Вычисляем подпись
                $sign = md5($secret_key.$xml.$secret_key);
                // Кодируем данные в BASE64
                $xml_encode = base64_encode($xml);
                $sign_encode = base64_encode($sign);
                echo '
                <!DOCTYPE html><html><body>
                    <form id="b-site" action="https://merchant.pay2pay.com/?page=init" method="post">
                        <input type="hidden" name="xml" value="'.$xml_encode.'">
                        <input type="hidden" name="sign" value="'.$sign_encode.'">
                    </form>
                    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
                    <script>$("document").ready(function() {$("#b-site").submit();});</script>
                </body></html>';
                exit;
            } else {
                redirect_eshop(link_eshop());
            }

        }
    }

}