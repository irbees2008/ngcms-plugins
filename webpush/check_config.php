<?php

/**
 * Проверка конфигурации WebPush плагина
 * Запустить через браузер: /engine/plugins/webpush/check_config.php
 */

// Подключаем NGCMS
define('NGCMS', 1);
$root = dirname(__DIR__, 2);
require_once $root . '/core.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>WebPush - Проверка конфигурации</title>
    <style>
        body {
            font-family: monospace;
            background: #f5f5f5;
            padding: 20px;
        }

        .box {
            background: white;
            border: 2px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .ok {
            border-color: #4CAF50;
            background: #f1f8f4;
        }

        .error {
            border-color: #f44336;
            background: #fef1f1;
        }

        .warning {
            border-color: #ff9800;
            background: #fff8e1;
        }

        h2 {
            margin: 0 0 10px 0;
        }

        pre {
            background: #f9f9f9;
            padding: 10px;
            overflow-x: auto;
        }

        .key {
            word-break: break-all;
        }
    </style>
</head>

<body>
    <h1>🔍 WebPush - Диагностика конфигурации</h1>

    <?php
    // 1. Проверка активации плагина
    echo '<div class="box ' . (pluginIsActive('webpush') ? 'ok' : 'error') . '">';
    echo '<h2>1. Статус плагина</h2>';
    echo '<p>Плагин ' . (pluginIsActive('webpush') ? '<b>АКТИВЕН ✓</b>' : '<b>НЕ АКТИВЕН ✗</b>') . '</p>';
    echo '</div>';

    // 2. Проверка настроек
    $enabled = pluginGetVariable('webpush', 'enabled');
    $showButton = pluginGetVariable('webpush', 'show_button');
    $subscribeText = pluginGetVariable('webpush', 'subscribe_text');
    $vapidPublic = pluginGetVariable('webpush', 'vapid_public');
    $vapidPrivate = pluginGetVariable('webpush', 'vapid_private');
    $vapidSubject = pluginGetVariable('webpush', 'vapid_subject');

    echo '<div class="box ' . ($enabled ? 'ok' : 'warning') . '">';
    echo '<h2>2. Основные настройки</h2>';
    echo '<pre>';
    echo "enabled: " . ($enabled ? 'ДА' : 'НЕТ') . "\n";
    echo "show_button: " . ($showButton ? 'ДА' : 'НЕТ') . "\n";
    echo "subscribe_text: " . htmlspecialchars($subscribeText ?: '(пусто)') . "\n";
    echo '</pre>';
    echo '</div>';

    // 3. Проверка VAPID ключей
    $publicOk = !empty($vapidPublic) && strlen($vapidPublic) > 50;
    $privateOk = !empty($vapidPrivate) && strlen($vapidPrivate) > 30;
    $subjectOk = !empty($vapidSubject) && (strpos($vapidSubject, 'mailto:') === 0 || strpos($vapidSubject, 'https://') === 0);

    echo '<div class="box ' . ($publicOk && $privateOk && $subjectOk ? 'ok' : 'error') . '">';
    echo '<h2>3. VAPID ключи</h2>';
    echo '<p><b>Public Key:</b> ' . ($publicOk ? '✓ Настроен' : '✗ НЕ НАСТРОЕН или неверный') . '</p>';
    if ($vapidPublic) {
        echo '<div class="key" style="font-size: 10px; color: #666;">' . htmlspecialchars(substr($vapidPublic, 0, 100)) . '...</div>';
    } else {
        echo '<p style="color: red;">Публичный ключ ПУСТОЙ!</p>';
    }

    echo '<p><b>Private Key:</b> ' . ($privateOk ? '✓ Настроен' : '✗ НЕ НАСТРОЕН или неверный') . '</p>';
    if ($vapidPrivate) {
        echo '<div style="font-size: 10px; color: #666;">' . htmlspecialchars(substr($vapidPrivate, 0, 50)) . '... (скрыт)</div>';
    } else {
        echo '<p style="color: red;">Приватный ключ ПУСТОЙ!</p>';
    }

    echo '<p><b>Subject:</b> ' . ($subjectOk ? '✓ Настроен' : '✗ НЕ НАСТРОЕН') . '</p>';
    echo '<div style="font-size: 11px;">' . htmlspecialchars($vapidSubject ?: '(пусто)') . '</div>';
    echo '</div>';

    // 4. Проверка библиотеки WebPush
    $webpushLibPath = __DIR__ . '/lib/vendor/autoload.php';
    $libOk = file_exists($webpushLibPath);

    echo '<div class="box ' . ($libOk ? 'ok' : 'error') . '">';
    echo '<h2>4. Библиотека WebPush</h2>';
    echo '<p>lib/vendor/autoload.php: ' . ($libOk ? '✓ Найден' : '✗ НЕ НАЙДЕН') . '</p>';

    if ($libOk) {
        require_once $webpushLibPath;
        echo '<p>Проверка библиотеки minishlink/web-push: ';
        if (class_exists('Minishlink\WebPush\WebPush')) {
            echo '✓ Установлена</p>';
            echo '<p style="color: #666; font-size: 13px;">Библиотека находится в папке плагина</p>';
        } else {
            echo '✗ НЕ НАЙДЕНА</p>';
            echo '<p style="color: red;">Библиотека повреждена. Переустановите плагин.</p>';
        }
    } else {
        echo '<p style="color: red;">Библиотека отсутствует в папке плагина!</p>';
        echo '<p style="font-size: 13px;">Путь: <code>engine/plugins/webpush/lib/vendor/</code></p>';
    }
    echo '</div>';

    // 5. Проверка таблицы БД
    global $mysql;
    $tableExists = false;
    $subCount = 0;

    try {
        $rec = $mysql->record("SHOW TABLES LIKE '" . prefix . "_webpush_subscriptions'");
        $tableExists = !empty($rec);

        if ($tableExists) {
            $rec = $mysql->record("SELECT COUNT(*) as cnt FROM " . prefix . "_webpush_subscriptions");
            $subCount = (int)($rec['cnt'] ?? 0);
        }
    } catch (Exception $e) {
        // ignore
    }

    echo '<div class="box ' . ($tableExists ? 'ok' : 'error') . '">';
    echo '<h2>5. База данных</h2>';
    echo '<p>Таблица подписок: ' . ($tableExists ? '✓ Создана' : '✗ НЕ СОЗДАНА') . '</p>';
    if ($tableExists) {
        echo '<p>Подписчиков: <b>' . $subCount . '</b></p>';
    } else {
        echo '<p style="color: red;">Запустите установку плагина через админ-панель</p>';
    }
    echo '</div>';

    // 6. Проверка Service Worker
    $swFile = $root . '/webpush-sw.js';
    $swExists = file_exists($swFile);

    echo '<div class="box ' . ($swExists ? 'ok' : 'warning') . '">';
    echo '<h2>6. Service Worker</h2>';
    echo '<p>Файл webpush-sw.js: ' . ($swExists ? '✓ Найден' : '✗ НЕ НАЙДЕН в корне сайта') . '</p>';
    if (!$swExists) {
        echo '<p style="color: orange;">Скопируйте файл из engine/plugins/webpush/sw/webpush-sw.js в корень сайта</p>';
    }
    echo '</div>';

    // 7. Проверка HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

    echo '<div class="box ' . ($isHttps ? 'ok' : 'warning') . '">';
    echo '<h2>7. HTTPS</h2>';
    echo '<p>Протокол: ' . ($isHttps ? '<b>HTTPS ✓</b>' : '<b>HTTP ✗</b>') . '</p>';
    if (!$isHttps) {
        echo '<p style="color: orange;">Push-уведомления требуют HTTPS (кроме localhost)</p>';
    }
    echo '</div>';

    // ИТОГО
    $allOk = pluginIsActive('webpush') && $enabled && $publicOk && $privateOk && $libOk && $tableExists;

    echo '<div class="box ' . ($allOk ? 'ok' : 'error') . '" style="margin-top: 20px;">';
    echo '<h2>📋 ИТОГО</h2>';
    if ($allOk) {
        echo '<p style="font-size: 16px;"><b>✓ Все проверки пройдены! Плагин должен работать.</b></p>';
        echo '<p>Если кнопка не появляется, проверьте:</p>';
        echo '<ol>';
        echo '<li>Очистите кеш: <code>engine/cache/</code></li>';
        echo '<li>Проверьте шаблон main.tpl: должно быть <code>{{ webpush|raw }}</code></li>';
        echo '<li>Откройте консоль браузера (F12) и проверьте на ошибки</li>';
        echo '<li>Проверьте логи: <code>engine/plugins/webpush/logs/webpush.log</code></li>';
        echo '</ol>';
    } else {
        echo '<p style="font-size: 16px; color: red;"><b>✗ Найдены проблемы конфигурации!</b></p>';
        echo '<p>Исправьте ошибки выше и перезагрузите страницу.</p>';
    }
    echo '</div>';
    ?>

    <hr>
    <p style="color: #666; font-size: 12px;">Дата: <?php echo date('Y-m-d H:i:s'); ?></p>
</body>

</html>
