<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

include_once(__DIR__.'/lib/LiqPay.php');

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
                if (!empty($rData['data']) and !empty($rData['signature'])) {

                    $sign = base64_encode(
                        sha1(
                            $payment_options['private_key'].
                            $rData['data'].
                            $payment_options['private_key']
                            ,
                            1
                        )
                    );

                    if ($sign == $rData['signature']) {

                        $data = json_decode(base64_decode($rData['data']), 1);
                        $a_or_id = explode("_", $data['order_id']);
                        $order_id = $a_or_id[1];

                        $filter [] = '(id = '.db_squote($order_id).')';
                        $sqlQ = "SELECT * FROM ".prefix."_eshop_orders ".(count($filter) ? "WHERE ".implode(
                                    " AND ",
                                    $filter
                                ) : '')." LIMIT 1";
                        $row = $mysql->record($sqlQ);

                        if (in_array(
                                $data['status'],
                                ['success', 'sandbox']
                            ) && $data['amount'] == $row['total_price']
                        ) {

                            $mysql->query(
                                'INSERT INTO '.prefix.'_eshop_purchases (dt, order_id, info)
                                VALUES
                                ('.db_squote($current_time).',
                                    '.db_squote($order_id).',
                                    '.db_squote(json_encode($row)).'
                                )
                            '
                            );

                            $mysql->query(
                                'UPDATE '.prefix.'_eshop_orders SET
                                paid = 1
                                WHERE id = '.$order_id.'
                            '
                            );
                        }

                    } else {
                        $error = 'Incorrect sign';
                    }

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

                $result_url = home.'/eshop/payment/?result=2&payment_id=liqpay';
                $success_url = home.'/eshop/payment/?result=3&payment_id=liqpay';

                $liqpay = new LiqPay($payment_options['public_key'], $payment_options['private_key']);
                $html = $liqpay->cnb_form(
                    array(
                        'action' => 'pay',
                        'amount' => $row['total_price'],
                        'currency' => $SYSTEM_FLAGS['eshop']['currency'][0]['code'],
                        'description' => 'Оплата по заказу ID: ' . $uniqid . '_' . $order_id,
                        'order_id' => $uniqid . '_' . $order_id,
                        'version' => '3',
                        'sandbox' => $payment_options['sandbox'],
                        'result_url' => $success_url,
                        'server_url' => $result_url,
                    )
                );

                echo '
                <!DOCTYPE html><html><body>
                    '.$html.'
                    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
                    <script>$("document").ready(function() {$("form").submit();});</script>
                </body></html>';
                exit;
            } else {
                redirect_eshop(link_eshop());
            }

        }
    }

}