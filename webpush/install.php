<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

function plugin_webpush_install(string $action): bool
{
    global $config;

    $root = dirname(__DIR__, 3);
    $confDir = $root . '/engine/conf/extras/webpush';

    // Структура таблицы для подписок
    $db_update = [
        [
            'table'  => 'webpush_subscriptions',
            'action' => 'cmodify',
            'key'    => 'primary key(id), unique key hash_idx(hash)',
            'fields' => [
                ['action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'],
                ['action' => 'cmodify', 'name' => 'hash', 'type' => 'varchar(64)', 'params' => 'not null default ""'],
                ['action' => 'cmodify', 'name' => 'endpoint', 'type' => 'text', 'params' => 'not null'],
                ['action' => 'cmodify', 'name' => 'p256dh', 'type' => 'varchar(255)', 'params' => 'not null default ""'],
                ['action' => 'cmodify', 'name' => 'auth', 'type' => 'varchar(255)', 'params' => 'not null default ""'],
                ['action' => 'cmodify', 'name' => 'user_agent', 'type' => 'varchar(255)', 'params' => 'not null default ""'],
                ['action' => 'cmodify', 'name' => 'ip', 'type' => 'varchar(45)', 'params' => 'not null default ""'],
                ['action' => 'cmodify', 'name' => 'created', 'type' => 'int', 'params' => 'not null default 0'],
                ['action' => 'cmodify', 'name' => 'updated', 'type' => 'int', 'params' => 'not null default 0'],
            ],
        ],
    ];

    switch ($action) {
        case 'confirm':
            generate_install_page(
                'webpush',
                '<p>Будет создана таблица для хранения подписок на Web Push уведомления.</p>' .
                    '<p><strong>Важно:</strong></p>' .
                    '<ul>' .
                    '<li>Web Push работает только по HTTPS (кроме localhost)</li>' .
                    '<li>Требуется установить Composer пакет: <code>composer require minishlink/web-push</code></li>' .
                    '<li>Service Worker будет автоматически скопирован в корень сайта (<code>/webpush-sw.js</code>).</li>' .
                    '<li>После установки выполните генерацию VAPID ключей через send.php?action=genkeys&secret=...</li>' .
                    '</ul>'
            );
            break;

        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('webpush', $db_update, 'install', ($action === 'autoapply'))) {
                // Создаём директорию конфигурации
                if (!is_dir($confDir)) {
                    @mkdir($confDir, 0775, true);
                }

                // Дефолтные параметры плагина
                extra_set_param('webpush', 'enabled', 1);
                extra_set_param('webpush', 'vapid_subject', 'mailto:admin@' . ($config['home_url'] ?? 'example.com'));
                extra_set_param('webpush', 'default_icon', '/uploads/webpush/icon.png');
                extra_set_param('webpush', 'default_badge', '/uploads/webpush/badge.png');
                extra_set_param('webpush', 'subscribe_text', 'Включить уведомления');
                extra_set_param('webpush', 'show_button', 1);
                extra_set_param('webpush', 'send_secret', bin2hex(random_bytes(16)));
                extra_commit_changes();

                // Копируем/обновляем service worker в корень сайта
                $swSrc = __DIR__ . '/sw/webpush-sw.js';
                $swDst = $root . '/webpush-sw.js';
                if (file_exists($swSrc)) {
                    // Копируем, если отсутствует или отличается по хэшу
                    if (!file_exists($swDst) || (@md5_file($swSrc) !== @md5_file($swDst))) {
                        @copy($swSrc, $swDst);
                    }
                }

                // Создаём директорию для иконок
                $uploadsDir = $root . '/uploads/webpush';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0775, true);
                }

                plugin_mark_installed('webpush');
            } else {
                return false;
            }
            break;
    }

    return true;
}
