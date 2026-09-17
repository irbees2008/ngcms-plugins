<?php

/**
 * CLI-скрипт для авторизации MadelineProto
 *
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Откройте терминал/cmd
 * 2. cd C:\OSPanel\home\it
 * 3. C:\OSPanel\modules\PHP-8.3\PHP\php.exe engine/plugins/content_parser/telegram_auth.php
 * 4. Следуйте инструкциям (введите номер телефона, код из Telegram)
 *
 * ВАЖНО:
 * - Для публичных каналов авторизация НЕ нужна
 * - Авторизация нужна только для приватных каналов
 * - После авторизации создается файл telegram_session.madeline
 */

// Не подключаем NGCMS - работаем автономно
// API credentials нужно ввести вручную или получить из админки

echo "=== Авторизация MadelineProto для NGCMS Content Parser ===\n\n";

// Запрашиваем API credentials у пользователя
echo "Telegram API ID (из админки или my.telegram.org): ";
$apiId = (int)trim(fgets(STDIN));

echo "Telegram API Hash (из админки или my.telegram.org): ";
$apiHash = trim(fgets(STDIN));

if (empty($apiId) || empty($apiHash)) {
    die("\nERROR: API ID и API Hash обязательны.\nПолучите их на https://my.telegram.org\n");
}

echo "\nAPI ID: $apiId\n";
echo "API Hash: " . substr($apiHash, 0, 8) . "...\n\n";

// Подключаем MadelineProto autoloader
$autoloadFile = __DIR__ . '/lib/MadelineProto-8/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die("ERROR: MadelineProto не установлена.\nУстановите зависимости:\ncd " . __DIR__ . "/lib/MadelineProto-8\ncomposer install\n");
}

require_once $autoloadFile;

if (!class_exists('danog\\MadelineProto\\API')) {
    die("ERROR: Класс MadelineProto\\API не найден.\n");
}

$sessionFile = __DIR__ . '/telegram_session.madeline';

// Настройки
$settings = new \danog\MadelineProto\Settings();
$appInfo = new \danog\MadelineProto\Settings\AppInfo();
$appInfo->setApiId($apiId);
$appInfo->setApiHash($apiHash);
$settings->setAppInfo($appInfo);

// Настройки соединения - увеличиваем таймауты
$connection = new \danog\MadelineProto\Settings\Connection();
$connection->setTimeout(30);
$connection->setRetry(true);
$settings->setConnection($connection);

// Устанавливаем глобальный обработчик ошибок EventLoop для игнорирования проблем со временем
\Revolt\EventLoop::setErrorHandler(function (\Throwable $e) {
    // Игнорируем ошибки синхронизации времени NTP
    if (
        strpos($e->getMessage(), 'sync your date using NTP') !== false
        || strpos($e->getMessage(), 'too new compared to') !== false
        || strpos($e->getMessage(), 'too old compared to') !== false
    ) {
        echo "[ПРЕДУПРЕЖДЕНИЕ] Проблема синхронизации времени (игнорируется): " . substr($e->getMessage(), 0, 100) . "...\n";
        return; // Игнорируем
    }
    // Другие ошибки пробрасываем
    throw $e;
});

echo "Инициализация MadelineProto...\n";

try {
    $API = new \danog\MadelineProto\API($sessionFile, $settings);

    echo "\n=== Процесс авторизации ===\n";
    echo "MadelineProto попросит ввести:\n";
    echo "1. Номер телефона (формат: +79001234567)\n";
    echo "2. Код подтверждения из Telegram\n";
    echo "3. (Опционально) Пароль 2FA, если включен\n\n";

    // Запускаем интерактивную авторизацию
    $API->start();

    echo "\n=== ✅ Авторизация успешна! ===\n";
    echo "Файл сессии создан: $sessionFile\n";
    echo "Теперь можно парсить приватные Telegram-каналы через админ-панель.\n";
} catch (\Throwable $e) {
    echo "\n=== ❌ ОШИБКА ===\n";
    echo $e->getMessage() . "\n";
    echo "\nПроверьте:\n";
    echo "- Правильность API ID и API Hash\n";
    echo "- Наличие интернет-соединения\n";
    echo "- Установлены ли зависимости composer\n";
    exit(1);
}
