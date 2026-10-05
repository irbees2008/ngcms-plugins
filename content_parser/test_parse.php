<?php

/**
 * Тест парсинга Telegram канала
 */

// Путь к ядру NGCMS
define('NGCMS', true);
require_once __DIR__ . '/../../core.php';

echo "=== Тест парсинга Telegram ===\n\n";

// Подключаем плагин
require_once __DIR__ . '/content_parser.php';

// Тестовый канал
$channel = 'durov';
$count = 3;

echo "Парсим канал: @$channel\n";
echo "Количество постов: $count\n\n";

try {
    $posts = parseTelegramChannel($channel, $count);

    echo "✅ Успешно спарсено постов: " . count($posts) . "\n\n";

    foreach ($posts as $i => $post) {
        echo "--- Пост #" . ($i + 1) . " ---\n";
        echo "Заголовок: " . mb_substr($post['title'], 0, 50) . "...\n";
        echo "Текст: " . mb_substr($post['content'], 0, 100) . "...\n";
        if (!empty($post['image'])) {
            echo "Изображение: " . $post['image'] . "\n";
        }
        echo "\n";
    }

    echo "🎉 Парсинг прошел успешно!\n";
    echo "Теперь попробуйте в админке плагина\n";
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "\nВозможные причины:\n";
    echo "1. Telegram недоступен на сервере\n";
    echo "2. Канал не существует или приватный\n";
    echo "3. Проблемы с интернет-соединением\n";
}
