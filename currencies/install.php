<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

$db_update = [
    [
        'table'  => 'currencies',
        'action' => 'cmodify',
        'key'    => 'primary key(id), UNIQUE KEY code (code)',
        'fields' => [
            ['action' => 'cmodify', 'name' => 'id',         'type' => 'int(11)',      'params' => 'NOT NULL AUTO_INCREMENT'],
            ['action' => 'cmodify', 'name' => 'code',       'type' => 'char(10)',     'params' => "NOT NULL DEFAULT ''"],
            ['action' => 'cmodify', 'name' => 'name',       'type' => 'varchar(100)', 'params' => "NOT NULL DEFAULT ''"],
            ['action' => 'cmodify', 'name' => 'symbol',     'type' => 'varchar(10)',  'params' => "NOT NULL DEFAULT ''"],
            ['action' => 'cmodify', 'name' => 'rate',       'type' => 'decimal(14,6)', 'params' => 'NOT NULL DEFAULT 1.000000'],
            ['action' => 'cmodify', 'name' => 'is_base',    'type' => 'tinyint(1)',   'params' => 'NOT NULL DEFAULT 0'],
            ['action' => 'cmodify', 'name' => 'updated_at', 'type' => 'int(11)',      'params' => 'NOT NULL DEFAULT 0'],
        ],
    ],
];

if ($_REQUEST['action'] === 'commit') {
    if (fixdb_plugin_install('currencies', $db_update)) {
        // Seed base currency from settings
        global $mysql;
        $base = pluginGetVariable('currencies', 'base') ?: 'RUB';
        $exists = $mysql->record("SELECT id FROM " . prefix . "_currencies WHERE code=" . db_squote($base) . " LIMIT 1");
        if (!$exists) {
            $mysql->query("INSERT INTO " . prefix . "_currencies (code, name, symbol, rate, is_base, updated_at)
                           VALUES (" . db_squote($base) . "," . db_squote($base) . ", '', 1.000000, 1, " . db_squote(time()) . ")");
        }
        plugin_mark_installed('currencies');
    }
} else {
    generate_install_page('currencies', 'Плагин мультивалютности. Создаётся таблица <b>currencies</b>. '
        . 'Поддерживает автообновление курсов с ЦБ РФ. Интегрируется с plugins basket и payments.');
}
