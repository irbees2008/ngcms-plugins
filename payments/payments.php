<?php

/**
 * Payments plugin for NGCMS
 * Provides payment gateway integration for basket plugin
 */
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize};

// Register frontend pages
register_plugin_page('payments', 'pay',     'payments_pay_page');
register_plugin_page('payments', 'success', 'payments_success_page');
register_plugin_page('payments', 'fail',    'payments_fail_page');
register_plugin_page('payments', 'notify',  'payments_notify_page');
register_plugin_page('payments', 'status',  'payments_status_page');

// ─── Load active adapters ──────────────────────────────────────────────────
function payments_load_adapters(): array
{
    $adapters = [];
    $dir = __DIR__ . '/adapters';
    if (!is_dir($dir)) return $adapters;
    foreach (glob($dir . '/*/adapter.php') as $file) {
        $name = basename(dirname($file));
        $adapters[$name] = $file;
    }
    return $adapters;
}

// ─── Get order from DB ─────────────────────────────────────────────────────
function payments_get_order(int $id): ?array
{
    global $mysql;
    $row = $mysql->record("SELECT * FROM " . prefix . "_basket_orders WHERE id = " . db_squote($id) . " LIMIT 1");
    return $row ?: null;
}

// ─── Mark order paid ───────────────────────────────────────────────────────
function payments_mark_paid(int $orderId, string $method, string $externalId, array $rawData = []): void
{
    global $mysql;
    $mysql->query("UPDATE " . prefix . "_basket_orders SET status='paid', payment_method=" . db_squote($method) . ", paid_at=" . db_squote(time()) . " WHERE id=" . db_squote($orderId));
    $mysql->query(
        "INSERT INTO " . prefix . "_payments_transactions
         (order_id, method, external_id, amount, raw_data, created_at)
         VALUES (" . db_squote($orderId) . "," . db_squote($method) . "," . db_squote($externalId) . ",
         (SELECT total FROM " . prefix . "_basket_orders WHERE id=" . db_squote($orderId) . "),
         " . db_squote(json_encode($rawData)) . "," . db_squote(time()) . ")"
    );
    logger("Order #$orderId marked paid via $method (ext: $externalId)", 'info', 'payments.log');
}

// ─── Get configured adapters list ─────────────────────────────────────────
function payments_active_adapters(): array
{
    $cfg = pluginsLoadConfig('payments');
    $result = [];
    foreach (payments_load_adapters() as $name => $file) {
        $adapterCfg = $cfg[$name] ?? [];
        if (!empty($adapterCfg['enabled'])) {
            $result[$name] = ['config' => $adapterCfg, 'file' => $file];
        }
    }
    return $result;
}

// ─── Page: payment selection / redirect to gateway ────────────────────────
function payments_pay_page()
{
    global $template, $twig, $mysql, $userROW, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

    $orderId = intval(sanitize($_REQUEST['order_id'] ?? '', 'int'));
    $uniqid  = preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['uniqid'] ?? '');
    $method  = preg_replace('/[^a-z0-9_]/', '', $_REQUEST['method'] ?? '');

    if (!$orderId || !$uniqid) {
        redirect(home);
    }

    $order = payments_get_order($orderId);
    if (!$order || $order['uniqid'] !== $uniqid) {
        redirect(home);
    }
    if ($order['status'] === 'paid') {
        redirect(generatePluginLink('payments', 'success', [], ['order_id' => $orderId, 'uniqid' => $uniqid]));
    }

    $adapters = payments_active_adapters();

    // If method selected — redirect to gateway
    if ($method && isset($adapters[$method])) {
        include_once $adapters[$method]['file'];
        $SUPRESS_TEMPLATE_SHOW = 1;
        $SUPRESS_MAINBLOCK_SHOW = 1;
        payments_adapter_redirect($orderId, $order, $adapters[$method]['config']);
        exit;
    }

    // Show payment method selection page
    $tpath = locatePluginTemplates(['pay'], 'payments', 1);
    $xt    = $twig->loadTemplate($tpath['pay'] . '/pay.tpl');
    $template['vars']['mainblock'] = $xt->render([
        'order'    => $order,
        'items'    => json_decode($order['items_json'], true),
        'adapters' => array_keys($adapters),
    ]);
}

// ─── Page: success ─────────────────────────────────────────────────────────
function payments_success_page()
{
    global $template, $twig;
    $orderId = intval(sanitize($_REQUEST['order_id'] ?? '', 'int'));
    $order   = $orderId ? payments_get_order($orderId) : null;
    $tpath   = locatePluginTemplates(['success'], 'payments', 1);
    $xt      = $twig->loadTemplate($tpath['success'] . '/success.tpl');
    $template['vars']['mainblock'] = $xt->render(['order' => $order]);
}

// ─── Page: fail ────────────────────────────────────────────────────────────
function payments_fail_page()
{
    global $template, $twig;
    $orderId = intval(sanitize($_REQUEST['order_id'] ?? '', 'int'));
    $order   = $orderId ? payments_get_order($orderId) : null;
    $tpath   = locatePluginTemplates(['fail'], 'payments', 1);
    $xt      = $twig->loadTemplate($tpath['fail'] . '/fail.tpl');
    $template['vars']['mainblock'] = $xt->render(['order' => $order]);
}

// ─── Page: status (AJAX) ───────────────────────────────────────────────────
function payments_status_page()
{
    global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;
    $SUPRESS_TEMPLATE_SHOW = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;
    $orderId = intval(sanitize($_REQUEST['order_id'] ?? '', 'int'));
    $uniqid  = preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['uniqid'] ?? '');
    if (!$orderId || !$uniqid) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error']);
        exit;
    }
    $order = payments_get_order($orderId);
    header('Content-Type: application/json');
    if (!$order || $order['uniqid'] !== $uniqid) {
        echo json_encode(['status' => 'error']);
    } else {
        echo json_encode(['status' => $order['status']]);
    }
    exit;
}

// ─── Page: payment gateway notify/callback ─────────────────────────────────
function payments_notify_page()
{
    global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;
    $SUPRESS_TEMPLATE_SHOW = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;

    $method = preg_replace('/[^a-z0-9_]/', '', $_REQUEST['method'] ?? '');
    if (!$method) {
        die('Bad request');
    }

    $adapters = payments_active_adapters();
    if (!isset($adapters[$method])) {
        logger("Notify for unknown/disabled method: $method", 'warn', 'payments.log');
        die('Unknown method');
    }

    include_once $adapters[$method]['file'];
    payments_adapter_notify($_REQUEST, $adapters[$method]['config']);
    exit;
}
