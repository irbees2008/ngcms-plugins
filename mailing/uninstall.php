<?php

/**
 * Uninstallation script for Mailing plugin
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 */

if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('mailing', 'main', '', '', ':');

// Описание таблиц для удаления
$db_update = array(
    array(
        'table'  => 'mailing_campaigns',
        'action' => 'drop',
    ),
    array(
        'table'  => 'mailing_queue',
        'action' => 'drop',
    ),
    array(
        'table'  => 'mailing_attachments',
        'action' => 'drop',
    ),
    array(
        'table'  => 'mailing_unsub',
        'action' => 'drop',
    ),
);

if ($_REQUEST['action'] == 'commit') {
    // Выполняем деинсталляцию
    if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
        // Удаляем директорию с загруженными файлами (вложениями)
        $uploadDir = dirname(__FILE__) . '/uploads';
        if (is_dir($uploadDir)) {
            // Удаляем все файлы в директории
            $files = glob($uploadDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($uploadDir);
        }

        plugin_mark_deinstalled($plugin);
    }
} else {
    // Показываем страницу подтверждения
    $info = '<b>' . $lang['mailing:uninstall_warning'] . '</b><br/><br/>';
    $info .= '• ' . $lang['mailing:uninstall_campaigns'] . '<br/>';
    $info .= '• ' . $lang['mailing:uninstall_queue'] . '<br/>';
    $info .= '• ' . $lang['mailing:uninstall_attachments'] . '<br/>';
    $info .= '• ' . $lang['mailing:uninstall_unsub'] . '<br/>';
    $info .= '• ' . $lang['mailing:uninstall_files'] . '<br/><br/>';
    $info .= '<b>' . $lang['mailing:uninstall_irreversible'] . '</b>';

    generate_install_page($plugin, $info, 'deinstall');
}
