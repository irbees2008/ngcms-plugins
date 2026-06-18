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
                $merchant_pass = $payment_options['merchantpass'];
                $payment = $rData['payment'];
                $signature = sha1(md5($payment.$merchant_pass));
                parse_str($payment, $response);
                $order_id = $response['order'];
                $amount = $response['amt'];

                if ($rData['signature'] == $signature) {
                    if ($response['state'] == 'test' || $response['state'] == 'ok') {

                        $info = array(
                            'payment_name' => $payment_name,
                            'merchant_purse' => $response,
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

                        die("OK".$order_id."\n");

                    } elseif ($response['state'] == 'fail') {
                        // error
                        redirect_eshop(link_eshop());
                    }
                } else {
                    die("bad sign\n");
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

                $merchant_id = $payment_options['merchantid'];
                $merchantpass = $payment_options['merchantpass'];
                $amount = $row['total_price']; // Сумма заказа
                $currency = $SYSTEM_FLAGS['eshop']['currency'][0]['code']; // Валюта заказа
                $desc = 'Оплата по заказу ID: '.$order_id; // Описание заказа
                $fail_url = home.'/eshop/payment/?result=1&payment_id=privat24';
                $result_url = home.'/eshop/payment/?result=2&payment_id=privat24';
                $success_url = home.'/eshop/payment/?result=3&payment_id=privat24';

                echo '
                <!DOCTYPE html><html><body>'.
                    '<form action="https://api.privatbank.ua/p24api/ishop" method="POST"/>'.
                    '<input type="hidden" name="amt" value="'.$amount.'"/>'.
                    '<input type="hidden" name="ccy" value="'.$currency.'" />'.
                    '<input type="hidden" name="merchant" value="'.$merchant_id.'" />'.
                    '<input type="hidden" name="order" value="'.$order_id.'" />'.
                    '<input type="hidden" name="details" value="'.$desc.'" />'.
                    '<input type="hidden" name="ext_details" value="" />'.
                    '<input type="hidden" name="pay_way" value="privat24" />'.
                    '<input type="hidden" name="return_url" value="'.$success_url.'" />'.
                    '<input type="hidden" name="server_url" value="'.$result_url.'" />'.
                    '<input type="submit" value="Отправить" />'.
                    '</form>'.
                    '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
                    <script>$("document").ready(function() {$("#b-site").submit();});</script>
                </body></html>';
                exit;
            } else {
                redirect_eshop(link_eshop());
            }

        }
    }
}
