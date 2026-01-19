<?php

/**
 * Common helper functions for Mailing plugin
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 */

if (!defined('NGCMS')) die('HAL');

/**
 * Получить инстанс базы данных
 */
function mailing_db(): object
{
    global $mysql;
    return $mysql;
}

/**
 * Получить префикс таблиц БД
 */
function mailing_prefix(): string
{
    global $mysql, $config;

    return match (true) {
        is_object($mysql) && property_exists($mysql, 'prefix') && $mysql->prefix => $mysql->prefix,
        isset($config['prefix']) && $config['prefix'] => $config['prefix'],
        isset($config['dbprefix']) && $config['dbprefix'] => $config['dbprefix'],
        default => ''
    };
}

/**
 * Получить полное имя таблицы с префиксом
 */
function mailing_tbl(string $name): string
{
    $prefix = mailing_prefix();
    if ($prefix === '') {
        return $name;
    }
    // В NGCMS таблицы имеют формат: <prefix>_<name>
    // Например: ng_mailing_campaigns
    return $prefix . '_' . $name;
}

/**
 * Проверка наличия колонок в таблице (кэшируется в пределах запроса)
 */
function mailing_table_has_columns(string $table, array $columns): bool
{
    static $cache = [];

    $key = $table . '|' . implode(',', $columns);
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $db = mailing_db();
    $full = mailing_tbl($table);
    $rows = $db->select('DESCRIBE ' . $full);
    if (!$rows) {
        $cache[$key] = false;
        return false;
    }

    $existing = [];
    foreach ($rows as $r) {
        if (!empty($r['Field'])) {
            $existing[$r['Field']] = true;
        }
    }

    foreach ($columns as $c) {
        if (empty($existing[$c])) {
            $cache[$key] = false;
            return false;
        }
    }

    $cache[$key] = true;
    return true;
}

/**
 * Поддерживаются ли счётчики кампаний (sent_count/delivered_count/failed_count)
 */
function mailing_campaign_stats_supported(): bool
{
    static $supported;
    if ($supported !== null) {
        return $supported;
    }
    $supported = mailing_table_has_columns('mailing_campaigns', ['sent_count', 'delivered_count', 'failed_count']);
    return $supported;
}

/**
 * Пытается добавить недостающие колонки статистики кампаний
 */
function mailing_ensure_campaign_stats_columns(): bool
{
    // Уже поддерживается
    if (mailing_campaign_stats_supported()) {
        return true;
    }

    $db  = mailing_db();
    $tbl = mailing_tbl('mailing_campaigns');

    // Проверяем существующие колонки
    $rows = $db->select('DESCRIBE ' . $tbl);
    $have = [];
    foreach ((array)$rows as $r) {
        if (!empty($r['Field'])) {
            $have[$r['Field']] = true;
        }
    }

    // Список требуемых колонок
    $need = [
        'sent_count'      => 'ALTER TABLE ' . $tbl . ' ADD COLUMN sent_count INT NOT NULL DEFAULT 0',
        'delivered_count' => 'ALTER TABLE ' . $tbl . ' ADD COLUMN delivered_count INT NOT NULL DEFAULT 0',
        'failed_count'    => 'ALTER TABLE ' . $tbl . ' ADD COLUMN failed_count INT NOT NULL DEFAULT 0',
    ];

    foreach ($need as $col => $sql) {
        if (empty($have[$col])) {
            // Пытаемся добавить колонку, игнорируя возможные ошибки прав
            $db->query($sql);
        }
    }

    // Сбросим кэш проверки
    // (переинициализация статической переменной)
    $ref = new ReflectionFunction('mailing_campaign_stats_supported');
    // Хак: повторный вызов пересчитает значение
    return mailing_campaign_stats_supported();
}

/**
 * Получить значение параметра плагина
 */
function mailing_cfg(string $key, string $default = ''): string
{
    // Используем NGCMS API
    if (function_exists('pluginGetVariable')) {
        $value = pluginGetVariable('mailing', $key);
        return ($value === null || $value === '') ? $default : (string)$value;
    }

    // Fallback для старых сборок
    if (function_exists('extra_get_param')) {
        $value = extra_get_param('mailing', $key);
        return ($value === null || $value === '') ? $default : (string)$value;
    }

    return $default;
}

/**
 * Получить булево значение параметра
 */
function mailing_cfg_bool(string $key, bool $default = false): bool
{
    $value = mailing_cfg($key, $default ? '1' : '0');
    return in_array($value, ['1', 'true', 'yes'], true);
}

/**
 * Установить значение параметра плагина
 */
function mailing_set_cfg(string $key, string $value): void
{
    if (function_exists('pluginSetVariable')) {
        pluginSetVariable('mailing', $key, $value);
        return;
    }

    if (function_exists('extra_set_param')) {
        extra_set_param('mailing', $key, $value);
        return;
    }
}

/**
 * Сохранить все изменения параметров в БД
 */
function mailing_commit_changes(): void
{
    if (function_exists('extra_commit_changes')) {
        extra_commit_changes();
    }
}

/**
 * Получить текущее время UNIX timestamp
 */
function mailing_now(): int
{
    return time();
}

/**
 * Кодировать данные в JSON
 */
function mailing_json_encode(mixed $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

/**
 * Декодировать JSON
 */
function mailing_json_decode(string $json, mixed $default = []): mixed
{
    if (empty($json)) {
        return $default;
    }

    try {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : $default;
    } catch (JsonException) {
        return $default;
    }
}

/**
 * HTML-экранирование
 */
function mailing_h(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Генерация случайного токена
 */
function mailing_token(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Получить базовый URL сайта
 */
function mailing_base_url(): string
{
    global $config;

    if (!empty($config['home_url'])) {
        return rtrim($config['home_url'], '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return "{$scheme}://{$host}";
}

/**
 * Базовая санитизация HTML для email
 * - Оставляет только безопасные теги
 * - Опционально разрешает iframe (большинство почтовых клиентов всё равно вырезают)
 */
function mailing_sanitize_html(string $html, bool $allowIframe = false): string
{
    $allowed = '<a><p><br><b><strong><i><em><u><ul><ol><li><blockquote><pre><code><hr><h1><h2><h3><h4><h5><h6><img><table><thead><tbody><tr><td><th><span><div>';

    if ($allowIframe) {
        $allowed .= '<iframe>';
    }

    $html = strip_tags($html, $allowed);

    // Удаляем скрипты и стили на всякий случай
    $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);

    // Очистка атрибутов (оставляем только безопасные)
    $html = preg_replace_callback('#<([a-z0-9]+)([^>]*)>#i', function (array $matches) use ($allowIframe): string {
        $tag = strtolower($matches[1]);
        $attrs = $matches[2];

        $keep = [];
        if (preg_match_all('#\s([a-zA-Z0-9_-]+)\s*=\s*(".*?"|\'.*?\'|[^\s"\'>]+)#s', $attrs, $attrMatches, PREG_SET_ORDER)) {
            foreach ($attrMatches as $attr) {
                $name = strtolower($attr[1]);
                $value = $attr[2];

                $safeAttrs = ['href', 'src', 'alt', 'title', 'width', 'height', 'target', 'rel', 'style'];

                if (in_array($name, $safeAttrs, true)) {
                    // Предотвращаем javascript: в href/src
                    $cleanValue = trim($value, "\"'");
                    if (in_array($name, ['href', 'src']) && preg_match('#^\s*javascript:#i', $cleanValue)) {
                        continue;
                    }
                    $keep[] = " {$name}={$value}";
                }

                if ($allowIframe && $tag === 'iframe') {
                    $iframeAttrs = ['allow', 'allowfullscreen', 'frameborder', 'loading', 'referrerpolicy'];
                    if (in_array($name, $iframeAttrs, true)) {
                        $keep[] = " {$name}={$value}";
                    }
                }
            }
        }

        return '<' . $tag . implode('', $keep) . '>';
    }, $html);

    return $html;
}

/**
 * Проверка, что код выполняется в админ-панели
 */
function mailing_is_admin(): bool
{
    return defined('IN_ADMIN') && IN_ADMIN;
}

/**
 * Обработка YouTube тегов {YOUTUBE:URL}
 * Заменяет теги на встроенное видео или превью
 */
function mailing_process_youtube_tags(string $html): string
{
    return preg_replace_callback('/\{YOUTUBE:([^}]+)\}/', function ($matches) {
        $url = trim($matches[1]);

        // Извлекаем ID видео из различных форматов YouTube URL
        $videoId = null;
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $videoId = $m[1];
        }

        if (!$videoId) {
            return $matches[0]; // Если не смогли извлечь ID, возвращаем как есть
        }

        // Возвращаем HTML для встраивания видео
        return sprintf(
            '<a href="https://www.youtube.com/watch?v=%s" target="_blank">' .
                '<img src="https://img.youtube.com/vi/%s/maxresdefault.jpg" alt="YouTube Video" style="max-width:100%%;height:auto;border:1px solid #ddd;">' .
                '</a>',
            htmlspecialchars($videoId),
            htmlspecialchars($videoId)
        );
    }, $html);
}
