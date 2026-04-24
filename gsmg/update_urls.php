<?php

/**
 * Скрипт для обновления URL-хендлеров плагина gsmg
 * Запустите этот файл один раз через браузер: http://ваш-сайт.ru/engine/plugins/gsmg/update_urls.php
 */

// Load NGCMS core
define('NGCMS', 1);
require_once __DIR__ . '/../../core.php';
require_once __DIR__ . '/lib/common.php';

// Check admin rights
if (!is_array($userROW) || !isset($userROW['status']) || $userROW['status'] != 1) {
    die('Access denied. Admin only.');
}

echo '<html><head><meta charset="utf-8"><title>Обновление URL для gsmg</title></head><body>';
echo '<h1>Обновление URL-маршрутов для плагина gsmg</h1>';

// Remove old URLs
echo '<p>Удаление старых URL-хендлеров...</p>';
remove_gsmg_urls();

// Create new URLs
echo '<p>Создание новых URL-хендлеров...</p>';
create_gsmg_urls();

echo '<p style="color: green; font-weight: bold;">✓ URL-хендлеры успешно обновлены!</p>';
echo '<p>Теперь карта сайта доступна по адресам:</p>';
echo '<ul>';
echo '<li><a href="' . home . 'gsmg/" target="_blank">' . home . 'gsmg/</a> (основной)</li>';
echo '<li><a href="' . home . 'gsmg.xml" target="_blank">' . home . 'gsmg.xml</a> (альтернативный)</li>';
echo '</ul>';
echo '<p><strong>Важно:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home . 'engine/admin.php">← Вернуться в админку</a></p>';
echo '</body></html>';
