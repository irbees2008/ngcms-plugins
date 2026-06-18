<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
loadPluginLang('payments', 'config', '', '', ':');

$db_update = [
    [
        'table'  => 'payments_transactions',
        'action' => 'cmodify',
        'key'    => 'primary key(id)',
        'fields' => [
            ['action' => 'cmodify', 'name' => 'id',          'type' => 'int(11)',      'params' => 'NOT NULL AUTO_INCREMENT'],
            ['action' => 'cmodify', 'name' => 'order_id',    'type' => 'int(11)',      'params' => 'NOT NULL DEFAULT 0'],
            ['action' => 'cmodify', 'name' => 'method',      'type' => 'varchar(50)',  'params' => "NOT NULL DEFAULT ''"],
            ['action' => 'cmodify', 'name' => 'external_id', 'type' => 'varchar(255)', 'params' => "NOT NULL DEFAULT ''"],
            ['action' => 'cmodify', 'name' => 'amount',      'type' => 'decimal(12,2)', 'params' => 'NOT NULL DEFAULT 0'],
            ['action' => 'cmodify', 'name' => 'raw_data',    'type' => 'text',         'params' => ''],
            ['action' => 'cmodify', 'name' => 'created_at',  'type' => 'int(11)',      'params' => 'NOT NULL DEFAULT 0'],
        ],
    ],
];

if ($_REQUEST['action'] === 'commit') {
    if (fixdb_plugin_install('payments', $db_update)) {
        plugin_mark_installed('payments');
    }
} else {
    generate_install_page('payments', 'Плагин добавляет поддержку платёжных шлюзов к корзине (basket). '
        . 'Создаётся таблица <b>payments_transactions</b>. '
        . 'Требует: basket, feedback, xfields.');
}
