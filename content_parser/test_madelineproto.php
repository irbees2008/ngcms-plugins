<?php

/**
 * Быстрая проверка MadelineProto
 */

define('NGCMS', true);

echo "=== Проверка установки MadelineProto ===\n\n";

// Путь к bootstrap
$bootstrapFile = __DIR__ . '/madelineproto_bootstrap.php';

echo "1. Проверка bootstrap файла...\n";
if (!file_exists($bootstrapFile)) {
    die("   [ОШИБКА] Bootstrap файл не найден!\n");
}
echo "   [OK] Bootstrap найден\n\n";

// Подключаем bootstrap
echo "2. Загрузка MadelineProto...\n";
$loaded = include($bootstrapFile);

if ($loaded && defined('MADELINEPROTO_LOADED')) {
    echo "   [OK] MadelineProto загружена через bootstrap\n\n";
} else {
    die("   [ОШИБКА] Не удалось загрузить MadelineProto\n");
}

// Проверяем классы
echo "3. Проверка классов...\n";
$classes = [
    'danog\\MadelineProto\\API',
    'danog\\MadelineProto\\Logger',
    'danog\\MadelineProto\\Settings',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   [OK] Класс {$class} найден\n";
    } else {
        echo "   [ОШИБКА] Класс {$class} НЕ найден\n";
    }
}

echo "\n4. Проверка vendor/...\n";
$vendorPath = __DIR__ . '/lib/MadelineProto-8/vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "   [OK] vendor/autoload.php найден\n";

    // Подсчет пакетов
    $vendorDir = dirname($vendorPath);
    $packages = glob($vendorDir . '/*', GLOB_ONLYDIR);
    $packageCount = count($packages);
    echo "   [OK] Установлено {$packageCount} vendor-пакетов\n";

    // Размер
    $size = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($vendorDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    $sizeMB = round($size / 1024 / 1024, 2);
    echo "   [OK] Размер vendor/: {$sizeMB} МБ\n";
} else {
    echo "   [ОШИБКА] vendor/autoload.php не найден\n";
}

echo "\n5. Проверка константы MADELINEPROTO_AVAILABLE...\n";
if (defined('MADELINEPROTO_AVAILABLE') && MADELINEPROTO_AVAILABLE) {
    echo "   [OK] MADELINEPROTO_AVAILABLE = true\n";
} else {
    echo "   [ПРЕДУПРЕЖДЕНИЕ] MADELINEPROTO_AVAILABLE не установлена\n";
}

echo "\n=== ИТОГО ===\n";
if (class_exists('danog\\MadelineProto\\API')) {
    echo "✅ MadelineProto готова к работе!\n\n";
    echo "Следующие шаги:\n";
    echo "1. Зайдите на https://my.telegram.org\n";
    echo "2. Получите API ID и API Hash\n";
    echo "3. Зайдите в админку Content Parser\n";
    echo "4. Настройте API credentials\n";
    echo "5. Включите опцию 'Использовать MadelineProto'\n";
} else {
    echo "❌ MadelineProto не работает\n";
    echo "Проверьте логи выше для диагностики\n";
}
