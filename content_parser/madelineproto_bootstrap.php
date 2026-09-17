<?php

/**
 * MadelineProto Bootstrap для Content Parser
 *
 * Проверяет наличие MadelineProto и устанавливает константы
 */

if (!defined('NGCMS')) die('HAL');

// Путь к локальной установке MadelineProto в плагине
$vendorAutoload = __DIR__ . '/lib/MadelineProto-8/vendor/autoload.php';

// Пытаемся загрузить автозагрузчик зависимостей
// Подавляем вывод предупреждений MadelineProto (о Windows производительности)
if (file_exists($vendorAutoload)) {
    ob_start();
    require_once $vendorAutoload;
    ob_end_clean();
}

// Проверяем, доступна ли MadelineProto
// (через локальный vendor или корневой vendor)
if (class_exists('danog\\MadelineProto\\API')) {
    define('MADELINEPROTO_LOADED', true);
    define('MADELINEPROTO_AVAILABLE', true);
    return true;
}

// MadelineProto не найдена - веб-парсинг будет использоваться по умолчанию
define('MADELINEPROTO_LOADED', false);
define('MADELINEPROTO_AVAILABLE', false);
return false;
