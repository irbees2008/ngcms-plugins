<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

// Admin panel for currencies
switch ($_REQUEST['action'] ?? '') {
    case 'add':
        currencies_admin_add();
        break;
    case 'edit':
        currencies_admin_edit();
        break;
    case 'save':
        currencies_admin_save();
        break;
    case 'delete':
        currencies_admin_delete();
        break;
    case 'update_rates':
        currencies_update_rates_cbr();
        msg(['type' => 'ok', 'text' => 'Курсы обновлены']);
        currencies_admin_list();
        break;
    default:
        currencies_admin_list();
}

function currencies_admin_list()
{
    global $mysql, $twig, $template;
    $rows  = $mysql->select("SELECT * FROM " . prefix . "_currencies ORDER BY is_base DESC, code ASC", 1);
    $tpath = locatePluginTemplates(['list', 'edit'], 'currencies', 1);
    $xt    = $twig->loadTemplate($tpath['list'] . '/list.tpl');
    $template['vars']['mainblock'] = $xt->render([
        'currencies'   => $rows,
        'update_link'  => '?action=update_rates',
        'add_link'     => '?action=add',
    ]);
}

function currencies_admin_add()
{
    global $twig, $template;
    $tpath = locatePluginTemplates(['edit'], 'currencies', 1);
    $xt    = $twig->loadTemplate($tpath['edit'] . '/edit.tpl');
    $template['vars']['mainblock'] = $xt->render(['row' => ['id' => 0, 'code' => '', 'name' => '', 'symbol' => '', 'rate' => '1.000000', 'is_base' => 0]]);
}

function currencies_admin_edit()
{
    global $mysql, $twig, $template;
    $id  = intval($_REQUEST['id'] ?? 0);
    $row = $mysql->record("SELECT * FROM " . prefix . "_currencies WHERE id=" . db_squote($id) . " LIMIT 1");
    if (!$row) {
        currencies_admin_list();
        return;
    }
    $tpath = locatePluginTemplates(['edit'], 'currencies', 1);
    $xt    = $twig->loadTemplate($tpath['edit'] . '/edit.tpl');
    $template['vars']['mainblock'] = $xt->render(['row' => $row]);
}

function currencies_admin_save()
{
    global $mysql;
    $id     = intval($_POST['id'] ?? 0);
    $code   = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_POST['code'] ?? ''));
    $name   = htmlspecialchars(strip_tags($_POST['name'] ?? ''), ENT_QUOTES);
    $symbol = htmlspecialchars(strip_tags($_POST['symbol'] ?? ''), ENT_QUOTES);
    $rate   = max(0.000001, (float)($_POST['rate'] ?? 1));
    $isBase = intval($_POST['is_base'] ?? 0) ? 1 : 0;

    if (!$code) {
        currencies_admin_list();
        return;
    }

    if ($isBase) {
        $mysql->query("UPDATE " . prefix . "_currencies SET is_base=0");
    }

    if ($id > 0) {
        $mysql->query("UPDATE " . prefix . "_currencies SET
            code=" . db_squote($code) . ", name=" . db_squote($name) . ",
            symbol=" . db_squote($symbol) . ", rate=" . db_squote($rate) . ",
            is_base=" . db_squote($isBase) . ", updated_at=" . db_squote(time()) . "
            WHERE id=" . db_squote($id));
    } else {
        $mysql->query("INSERT INTO " . prefix . "_currencies (code, name, symbol, rate, is_base, updated_at)
            VALUES (" . db_squote($code) . "," . db_squote($name) . "," . db_squote($symbol) . "," . db_squote($rate) . "," . db_squote($isBase) . "," . db_squote(time()) . ")");
    }
    currencies_admin_list();
}

function currencies_admin_delete()
{
    global $mysql;
    $id = intval($_REQUEST['id'] ?? 0);
    $mysql->query("DELETE FROM " . prefix . "_currencies WHERE id=" . db_squote($id) . " AND is_base=0");
    currencies_admin_list();
}
