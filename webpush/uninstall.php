<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

$db_update = [
    [
        'table'  => 'webpush_subscriptions',
        'action' => 'drop',
    ],
];

if (($_REQUEST['action'] ?? '') === 'commit') {
    if (fixdb_plugin_install('webpush', $db_update, 'deinstall')) {
        $root = dirname(__DIR__, 3);

        // Удаляем директорию конфигурации (опционально)
        // @unlink($root . '/engine/conf/extras/webpush/config.php');

        // Удаляем service worker из корня сайта
        $swDst = $root . '/webpush-sw.js';
        if (file_exists($swDst)) {
            @unlink($swDst);
        }

        plugin_mark_deinstalled('webpush');
    }
} else {
    generate_install_page(
        'webpush',
        '<p>Плагин Web Push будет удалён, таблица подписок будет удалена.</p>' .
            '<p>Service worker файл (webpush-sw.js) в корне сайта будет удалён автоматически.</p>' .
            '<p>Директория uploads/webpush останется (иконки/бейджи) — при необходимости удалите её вручную.</p>',
        'deinstall'
    );
}
