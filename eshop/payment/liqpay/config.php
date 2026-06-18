<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

function payment_config($id)
{
    global $twig, $mysql;

    $row = $mysql->record('SELECT * FROM '.prefix.'_eshop_payment WHERE name = '.db_squote($id).' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $PARAMS['name'] = $id;
        $PARAMS['merchant_id'] = $_REQUEST['merchant_id'];
        $PARAMS['public_key'] = $_REQUEST['public_key'];
        $PARAMS['private_key'] = $_REQUEST['private_key'];
        $PARAMS['sandbox'] = $_REQUEST['sandbox'];

        $SQL['name'] = $id;
        $SQL['options'] = json_encode($PARAMS);

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k.' = '.db_squote($v);
            }
            $mysql->query("REPLACE INTO ".prefix."_eshop_payment SET ".implode(', ', $vnames)." ");
			msg(array('type' => 'info', 'info' => "Настройки cистема оплаты LiqPay успешно отредактировано"));
			return list_payment();
            //redirect_eshop('?mod=extra-config&plugin=eshop&action=list_payment');
        }

    }

    $tEntry = array();
    $tEntry['name'] = !empty($row['name']) ? $row['name'] : $id;
    $tEntry['options'] = json_decode($row['options'], true);

    $payment_config_tpl = __DIR__.'/tpl/config.tpl';
    $xt = $twig->loadTemplate($payment_config_tpl);

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $tpath = locatePluginTemplates(array('config/main'), 'eshop', 1);
    $xg = $twig->loadTemplate($tpath['config/main'].'config/'.'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url.'/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Системы оплаты ['.$id.']',
    );

    print $xg->render($tVars);
}