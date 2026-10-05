<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('payments', 'config', '', '', ':');
global $lang;

$pluginDir = __DIR__;
$adapters  = [];
foreach (glob($pluginDir . '/adapters/*/adapter.php') as $file) {
    $name = basename(dirname($file));
    $adapters[$name] = $name;
}

$cfg = [];

// ─── General section ───────────────────────────────────────────────────────
array_push($cfg, [
    'descr' => $lang['payments:config.description'],
]);
array_push($cfg, [
    'name'  => 'currency',
    'type'  => 'input',
    'title' => $lang['payments:config.currency'],
    'descr' => $lang['payments:config.currency_help'],
    'value' => pluginGetVariable('payments', 'currency') ?: 'RUB',
]);
array_push($cfg, [
    'name'  => 'success_redirect',
    'type'  => 'input',
    'title' => $lang['payments:config.success_redirect'],
    'descr' => $lang['payments:config.success_redirect_help'],
    'value' => pluginGetVariable('payments', 'success_redirect'),
]);
array_push($cfg, [
    'name'  => 'fail_redirect',
    'type'  => 'input',
    'title' => $lang['payments:config.fail_redirect'],
    'descr' => $lang['payments:config.fail_redirect_help'],
    'value' => pluginGetVariable('payments', 'fail_redirect'),
]);

// ─── Per-adapter sections ──────────────────────────────────────────────────
$adapterMeta = [
    'liqpay'    => ['title' => 'LiqPay', 'fields' => [
        ['name' => 'public_key',  'title' => $lang['payments:config.field.public_key']],
        ['name' => 'private_key', 'title' => $lang['payments:config.field.private_key']],
    ]],
    'robokassa' => ['title' => 'Robokassa', 'fields' => [
        ['name' => 'mrh_login', 'title' => $lang['payments:config.field.store_login']],
        ['name' => 'mrh_pass1', 'title' => $lang['payments:config.field.password_1']],
        ['name' => 'mrh_pass2', 'title' => $lang['payments:config.field.password_2']],
        ['name' => 'test_mode', 'title' => $lang['payments:config.test_mode'], 'type' => 'select', 'values' => [0 => $lang['payments:config.option_no'], 1 => $lang['payments:config.option_yes']]],
    ]],
    'unitpay'   => ['title' => 'Unitpay', 'fields' => [
        ['name' => 'public_key',  'title' => $lang['payments:config.field.public_key']],
        ['name' => 'secret_key',  'title' => $lang['payments:config.field.secret_key']],
        ['name' => 'project_id',  'title' => $lang['payments:config.field.project_id']],
    ]],
    'pay2pay'   => ['title' => 'Pay2Pay', 'fields' => [
        ['name' => 'shop_id',  'title' => $lang['payments:config.field.shop_id']],
        ['name' => 'secret',   'title' => $lang['payments:config.field.secret_key']],
    ]],
    'privat24'  => ['title' => 'Privat24', 'fields' => [
        ['name' => 'merchant_id',       'title' => $lang['payments:config.field.merchant_id']],
        ['name' => 'merchant_password', 'title' => $lang['payments:config.field.merchant_password']],
    ]],
    'yoomoney'  => ['title' => 'YooMoney', 'fields' => [
        ['name' => 'shop_id',      'title' => $lang['payments:config.field.shop_id']],
        ['name' => 'secret_key',   'title' => $lang['payments:config.field.secret_key']],
        ['name' => 'return_url',   'title' => $lang['payments:config.field.return_url']],
    ]],
    'freekassa' => ['title' => 'FreeKassa', 'fields' => [
        ['name' => 'merchant_id', 'title' => $lang['payments:config.field.merchant_id']],
        ['name' => 'secret1',     'title' => $lang['payments:config.field.secret_word_1']],
        ['name' => 'secret2',     'title' => $lang['payments:config.field.secret_word_2']],
    ]],
];

foreach ($adapterMeta as $adapterName => $meta) {
    $cfgX = [];
    array_push($cfgX, [
        'name'   => $adapterName . '_enabled',
        'type'   => 'select',
        'title'  => $lang['payments:config.enable_adapter'] . ' ' . $meta['title'],
        'values' => [0 => $lang['payments:config.option_no'], 1 => $lang['payments:config.option_yes']],
        'value'  => pluginGetVariable('payments', $adapterName . '_enabled'),
    ]);
    array_push($cfgX, [
        'name'  => $adapterName . '_label',
        'type'  => 'input',
        'title' => $lang['payments:config.adapter_label'],
        'descr' => $lang['payments:config.adapter_label_help'],
        'value' => pluginGetVariable('payments', $adapterName . '_label') ?: $meta['title'],
    ]);
    foreach ($meta['fields'] as $f) {
        $fType = $f['type'] ?? 'input';
        $entry = [
            'name'  => $adapterName . '_' . $f['name'],
            'type'  => $fType,
            'title' => $f['title'],
            'value' => pluginGetVariable('payments', $adapterName . '_' . $f['name']),
        ];
        if ($fType === 'select') {
            $entry['values'] = $f['values'];
        } else {
            $entry['html_flags'] = 'style="width:380px"';
        }
        array_push($cfgX, $entry);
    }
    array_push($cfg, ['mode' => 'group', 'title' => '<b>' . $meta['title'] . '</b>', 'entries' => $cfgX]);
}

if ($_REQUEST['action'] === 'commit') {
    commit_plugin_config_changes('payments', $cfg);
    print_commit_complete('payments');
} else {
    generate_config_page('payments', $cfg);
}
