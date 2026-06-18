<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('payments', 'config', '', '', '#');

$pluginDir = __DIR__;
$adapters  = [];
foreach (glob($pluginDir . '/adapters/*/adapter.php') as $file) {
    $name = basename(dirname($file));
    $adapters[$name] = $name;
}

$cfg = [];

// ─── General section ───────────────────────────────────────────────────────
array_push($cfg, [
    'descr' => 'Плагин обеспечивает интеграцию платёжных шлюзов с корзиной (basket). '
        . 'Каждый адаптер активируется отдельно и настраивается ключами соответствующего сервиса.',
]);
array_push($cfg, [
    'name'  => 'currency',
    'type'  => 'input',
    'title' => 'Валюта по умолчанию',
    'descr' => 'ISO-код валюты (UAH, RUB, USD…)',
    'value' => pluginGetVariable('payments', 'currency') ?: 'RUB',
]);
array_push($cfg, [
    'name'  => 'success_redirect',
    'type'  => 'input',
    'title' => 'Redirect после успешной оплаты',
    'descr' => 'URL или пусто — будет использована страница /payments/success/',
    'value' => pluginGetVariable('payments', 'success_redirect'),
]);
array_push($cfg, [
    'name'  => 'fail_redirect',
    'type'  => 'input',
    'title' => 'Redirect после неудачной оплаты',
    'descr' => 'URL или пусто — будет использована страница /payments/fail/',
    'value' => pluginGetVariable('payments', 'fail_redirect'),
]);

// ─── Per-adapter sections ──────────────────────────────────────────────────
$adapterMeta = [
    'liqpay'    => ['title' => 'LiqPay', 'fields' => [
        ['name' => 'public_key',  'title' => 'Public key'],
        ['name' => 'private_key', 'title' => 'Private key'],
    ]],
    'robokassa' => ['title' => 'Robokassa', 'fields' => [
        ['name' => 'mrh_login', 'title' => 'Логин магазина'],
        ['name' => 'mrh_pass1', 'title' => 'Password 1'],
        ['name' => 'mrh_pass2', 'title' => 'Password 2'],
        ['name' => 'test_mode', 'title' => 'Тестовый режим', 'type' => 'select', 'values' => [0 => 'Нет', 1 => 'Да']],
    ]],
    'unitpay'   => ['title' => 'Unitpay', 'fields' => [
        ['name' => 'public_key',  'title' => 'Public key'],
        ['name' => 'secret_key',  'title' => 'Secret key'],
        ['name' => 'project_id',  'title' => 'ID проекта'],
    ]],
    'pay2pay'   => ['title' => 'Pay2Pay', 'fields' => [
        ['name' => 'shop_id',  'title' => 'Shop ID'],
        ['name' => 'secret',   'title' => 'Secret key'],
    ]],
    'privat24'  => ['title' => 'Privat24 (Приват24)', 'fields' => [
        ['name' => 'merchant_id',       'title' => 'Merchant ID'],
        ['name' => 'merchant_password', 'title' => 'Merchant password'],
    ]],
    'yoomoney'  => ['title' => 'ЮMoney (YooMoney)', 'fields' => [
        ['name' => 'shop_id',      'title' => 'shopId'],
        ['name' => 'secret_key',   'title' => 'Secret key'],
        ['name' => 'return_url',   'title' => 'Return URL'],
    ]],
    'freekassa' => ['title' => 'FreeKassa', 'fields' => [
        ['name' => 'merchant_id', 'title' => 'Merchant ID'],
        ['name' => 'secret1',     'title' => 'Secret word 1'],
        ['name' => 'secret2',     'title' => 'Secret word 2'],
    ]],
];

foreach ($adapterMeta as $adapterName => $meta) {
    $cfgX = [];
    array_push($cfgX, [
        'name'   => $adapterName . '_enabled',
        'type'   => 'select',
        'title'  => 'Включить ' . $meta['title'],
        'values' => [0 => 'Нет', 1 => 'Да'],
        'value'  => pluginGetVariable('payments', $adapterName . '_enabled'),
    ]);
    array_push($cfgX, [
        'name'  => $adapterName . '_label',
        'type'  => 'input',
        'title' => 'Название для пользователя',
        'descr' => 'Отображается в форме выбора способа оплаты',
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
