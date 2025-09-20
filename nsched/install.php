<?php

// #====================================================================================#
// # Наименование плагина: nsched [ News SCHEDuller ]                                   #
// # Разрешено к использованию с: Next Generation CMS                                   #
// # Автор: Vitaly A Ponomarev, vp7@mail.ru                                             #
// #====================================================================================#
// #====================================================================================#
// # Инсталл скрипт плагина                                                             #
// #====================================================================================#

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

pluginsLoadConfig();
LoadPluginLang($plugin, 'install', '', '', ':');

$db_update = [
    [
        'table' => 'news',
        'action' => 'modify',
        'fields' => [
            ['action' => 'cmodify', 'name' => 'nsched_activate', 'type' => 'int(10)', 'params' => 'not null default "0"'],
            ['action' => 'cmodify', 'name' => 'nsched_deactivate', 'type' => 'int(10)', 'params' => 'not null default "0"'],
        ],
    ],
];

if ($_REQUEST['action'] == 'commit') {
    // If submit requested, do config save
    global $mysql, $config;

    // Сначала очищаем проблемные datetime значения перед изменением структуры (только если поля существуют)
    try {
        // Проверяем существование полей
        $columns = $mysql->select("SHOW COLUMNS FROM `" . $config['prefix'] . "_news` WHERE Field IN ('nsched_activate', 'nsched_deactivate')");

        if ($columns && count($columns) > 0) {
            $existingFields = array_column($columns, 'Field');

            // Очищаем только существующие поля
            if (in_array('nsched_activate', $existingFields)) {
                $mysql->query("UPDATE `" . $config['prefix'] . "_news` SET `nsched_activate` = NULL WHERE `nsched_activate` IN ('0000-00-00 00:00:00', '0')");
            }
            if (in_array('nsched_deactivate', $existingFields)) {
                $mysql->query("UPDATE `" . $config['prefix'] . "_news` SET `nsched_deactivate` = NULL WHERE `nsched_deactivate` IN ('0000-00-00 00:00:00', '0')");
            }
        }
    } catch (Exception $e) {
        // Игнорируем ошибки при проверке/очистке полей
    }

    if (fixdb_plugin_install($plugin, $db_update, 'install', '')) {
        plugin_mark_installed($plugin);
    }
} else {
    generate_install_page($plugin, $lang[$plugin . ':description']);
}
