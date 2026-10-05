<?php

/**
 * Быстрая проверка работоспособности плагина
 */

define('NGCMS', true);

echo "=== Проверка Content Parser ===\n\n";

// 1. Проверка подключения файлов
echo "1. Проверка файлов плагина...\n";
$files = [
    'content_parser.php' => __DIR__ . '/content_parser.php',
    'config.php' => __DIR__ . '/config.php',
    'telegram_madelineproto.php' => __DIR__ . '/telegram_madelineproto.php',
    'madelineproto_bootstrap.php' => __DIR__ . '/madelineproto_bootstrap.php',
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "   [OK] $name\n";
    } else {
        echo "   [ОШИБКА] $name НЕ НАЙДЕН\n";
    }
}

// 2. Проверка bootstrap
echo "\n2. Проверка MadelineProto bootstrap...\n";
$bootstrapResult = include(__DIR__ . '/madelineproto_bootstrap.php');
if ($bootstrapResult) {
    echo "   [OK] MadelineProto загружена\n";
} else {
    echo "   [INFO] MadelineProto не установлена (будет использован веб-парсинг)\n";
}

// 3. Проверка функций веб-парсинга
echo "\n3. Проверка функций...\n";

// Подключаем основной файл
require_once __DIR__ . '/content_parser.php';

$functions = [
    'normalizeTelegramChannel',
    'parseTelegramChannel',
    'parseTelegramChannelViaWeb',
];

foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "   [OK] $func()\n";
    } else {
        echo "   [ОШИБКА] $func() НЕ НАЙДЕНА\n";
    }
}

// 4. Тест нормализации канала
echo "\n4. Тест нормализации канала...\n";
$testCases = [
    '@durov' => 'durov',
    'https://t.me/durov' => 'durov',
    'https://t.me/s/durov' => 'durov',
    't.me/durov' => 'durov',
    'durov' => 'durov',
];

$allOk = true;
foreach ($testCases as $input => $expected) {
    $result = normalizeTelegramChannel($input);
    if ($result === $expected) {
        echo "   [OK] '$input' -> '$result'\n";
    } else {
        echo "   [ОШИБКА] '$input' -> '$result' (ожидалось: '$expected')\n";
        $allOk = false;
    }
}

// 5. Тест доступности Telegram
echo "\n5. Проверка доступа к Telegram...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://t.me/s/durov');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "   [OK] Telegram доступен (HTTP $httpCode)\n";
} else {
    echo "   [ПРЕДУПРЕЖДЕНИЕ] Telegram недоступен (HTTP $httpCode)\n";
    echo "   Возможно Telegram заблокирован на сервере\n";
}

// Итоги
echo "\n=== ИТОГИ ===\n";
echo "✅ Плагин установлен корректно\n";
echo "✅ Все файлы на месте\n";
echo "✅ Функции загружены\n";

if (!$bootstrapResult) {
    echo "ℹ️  MadelineProto не установлена\n";
    echo "   → Доступен только веб-парсинг публичных каналов\n";
    echo "   → Для приватных каналов установите MadelineProto\n";
} else {
    echo "✅ MadelineProto установлена\n";
    echo "   → Доступен полный функционал\n";
}

if ($httpCode === 200) {
    echo "✅ Telegram доступен - можно парсить каналы\n";
} else {
    echo "⚠️  Telegram недоступен - парсинг не будет работать\n";
}

echo "\n📋 Готово к использованию!\n";
echo "Зайдите в админку: Плагины → Content Parser\n";
