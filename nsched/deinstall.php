<?php

// #====================================================================================#
// # Наименование плагина: nsched [ News SCHEDuller ]                                   #
// # Разрешено к использованию с: Next Generation CMS                                   #
// # Автор: Vitaly A Ponomarev, vp7@mail.ru                                             #
// #====================================================================================#
// #====================================================================================#
// # Деинсталл скрипт плагина                                                             #
// #====================================================================================#

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

pluginsLoadConfig();
LoadPluginLang('nsched', 'deinstall', '', '', ':');

$db_update = [
    [
        'table' => 'news',
        'action' => 'modify',
        'fields' => [
            ['action' => 'drop', 'name' => 'nsched_activate'],
            ['action' => 'drop', 'name' => 'nsched_deactivate'],
        ],
    ],
];

if ($_REQUEST['action'] == 'commit') {
    // If submit requested, do config save
    global $mysql;

    // ALTER ... DROP COLUMN пересобирает таблицу и валидирует все datetime-поля строки под strict mode
    $originalSqlMode = $mysql->result('SELECT @@SESSION.sql_mode');
    $mysql->query("SET SESSION sql_mode = ''");

    if (fixdb_plugin_install('nsched', $db_update, 'deinstall', '')) {
        plugin_mark_deinstalled('nsched');
    }

    $mysql->query("SET SESSION sql_mode = '" . $originalSqlMode . "'");
} else {
    generate_install_page('nsched', $lang['nsched:description'] ?? 'Деинсталляция плагина nsched', 'deinstall');
}
