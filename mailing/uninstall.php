<?php

/**
 * Uninstallation script for Mailing plugin
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 */

if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

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
    $info = '<b>Внимание!</b> Будут удалены следующие данные:<br/><br/>';
    $info .= '• Таблица <b>mailing_campaigns</b> (все кампании рассылок)<br/>';
    $info .= '• Таблица <b>mailing_queue</b> (очередь отправки)<br/>';
    $info .= '• Таблица <b>mailing_attachments</b> (вложения)<br/>';
    $info .= '• Таблица <b>mailing_unsub</b> (отписки)<br/>';
    $info .= '• Все загруженные файлы вложений<br/><br/>';
    $info .= '<b>Это действие необратимо!</b>';

    generate_install_page($plugin, $info, 'deinstall');
}
