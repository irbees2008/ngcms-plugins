<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Import ng-helpers functions
use function Plugins\{logger, sanitize};

//
// Universal Telegram Notifications Plugin (NGCMS → Telegram)
// Sends Telegram notifications about various events:
// - jChat messages
// - Feedback forms
// - Private messages (PM)
// - Complaints
// - Comments
// Modernized with ng-helpers v0.2.2
//
function jchat_tgnotify_storage_get(string $key)
{
    // Session
    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
        return $_SESSION['jchat_tgnotify'][$key] ?? null;
    }

    // Cookie fallback
    $ck = 'jchat_tgnotify_' . $key;
    return $_COOKIE[$ck] ?? null;
}

function jchat_tgnotify_storage_set(string $key, $value, int $ttl = 7200): void
{
    // Session
    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
        if (!isset($_SESSION['jchat_tgnotify'])) {
            $_SESSION['jchat_tgnotify'] = [];
        }
        $_SESSION['jchat_tgnotify'][$key] = $value;
        return;
    }

    // Cookie fallback
    $ck = 'jchat_tgnotify_' . $key;
    @setcookie($ck, (string)$value, time() + $ttl, '/');
    $_COOKIE[$ck] = (string)$value;
}

//
// Settings getters
//
function jchat_tgnotify_enabled(): bool
{
    $v = extra_get_param('jchat_tgnotify', 'enabled');
    return ($v == 1 || $v === '1' || $v === true);
}

function jchat_tgnotify_get_token(): string
{
    return trim((string)extra_get_param('jchat_tgnotify', 'bot_token'));
}

function jchat_tgnotify_get_chat_id(): string
{
    return trim((string)extra_get_param('jchat_tgnotify', 'chat_id'));
}

function jchat_tgnotify_guests_only(): bool
{
    $v = extra_get_param('jchat_tgnotify', 'guests_only');
    return ($v == 1 || $v === '1' || $v === true);
}

function jchat_tgnotify_first_only(): bool
{
    $v = extra_get_param('jchat_tgnotify', 'first_only');
    return ($v == 1 || $v === '1' || $v === true);
}

function jchat_tgnotify_flood_seconds(): int
{
    return max(0, intval(extra_get_param('jchat_tgnotify', 'flood_seconds')));
}

//
// Decision logic
//
function jchat_tgnotify_should_send(array $data): bool
{
    if (!jchat_tgnotify_enabled()) return false;

    $token  = jchat_tgnotify_get_token();
    $chatId = jchat_tgnotify_get_chat_id();
    if ($token === '' || $chatId === '') return false;

    // Guests-only filter
    if (jchat_tgnotify_guests_only()) {
        $isGuest = !empty($data['is_guest']);
        if (!$isGuest) return false;
    }

    // Anti-flood filter
    $flood = jchat_tgnotify_flood_seconds();
    if ($flood > 0) {
        $last = intval(jchat_tgnotify_storage_get('last_ts') ?? 0);
        $now  = time();
        if ($last > 0 && ($now - $last) < $flood) {
            return false;
        }
    }

    // Only first message per session filter
    if (jchat_tgnotify_first_only()) {
        $sent = intval(jchat_tgnotify_storage_get('sent_flag') ?? 0);
        if ($sent === 1) return false;
    }

    return true;
}

function jchat_tgnotify_mark_sent(): void
{
    $now = time();
    jchat_tgnotify_storage_set('last_ts', $now, 7200);

    if (jchat_tgnotify_first_only()) {
        jchat_tgnotify_storage_set('sent_flag', 1, 7200);
    }
}

/**
 * Send notification to Telegram
 *
 * Expected data keys:
 * author, text, datetime, ip, url, is_guest (bool)
 */
function jchat_tgnotify_send(array $data): bool
{
    logger('[jchat_tgnotify] Вызов jchat_tgnotify_send', 'INFO');
    logger('[jchat_tgnotify] Данные: ' . json_encode($data, JSON_UNESCAPED_UNICODE), 'DEBUG');

    if (!jchat_tgnotify_should_send($data)) {
        logger('[jchat_tgnotify] Уведомление блокировано фильтрами', 'INFO');
        return false;
    }

    $token  = jchat_tgnotify_get_token();
    $chatId = jchat_tgnotify_get_chat_id();

    // Sanitize input data
    $author   = sanitize($data['author']   ?? 'Guest');
    $text     = sanitize($data['text']     ?? '');
    $datetime = sanitize($data['datetime'] ?? date('Y-m-d H:i:s'));
    $ip       = sanitize($data['ip']       ?? '');
    $url      = sanitize($data['url']      ?? '');

    $text = trim($text);
    if ($text === '') {
        logger('[jchat_tgnotify] Пустое сообщение, пропускаем', 'WARNING');
        return false;
    }

    // Remove HTML tags
    $text = strip_tags($text);
    $text = mb_substr($text, 0, 800);

    $msg  = "🟦 jChat: новое сообщение\n";
    $msg .= "👤 Автор: {$author}\n";
    $msg .= "🕒 Время: {$datetime}\n";
    if ($ip)  $msg .= "🌐 IP: {$ip}\n";
    if ($url) $msg .= "🔗 Страница: {$url}\n";
    $msg .= "\n💬 {$text}";

    $payload = [
        'chat_id' => $chatId,
        'text' => $msg,
        'disable_web_page_preview' => true,
    ];

    $apiUrl = "https://api.telegram.org/bot{$token}/sendMessage";

    $ok = false;

    try {
        if (function_exists('curl_init')) {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($res !== false && $err === '' && $httpCode >= 200 && $httpCode < 300) {
                $ok = true;
                logger('[jchat_tgnotify] Уведомление успешно отправлено в Telegram', 'INFO');
            } else {
                logger('[jchat_tgnotify] Ошибка отправки через cURL: ' . $err . ', HTTP код: ' . $httpCode, 'ERROR');
            }
        } else {
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($payload),
                    'timeout' => 5,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $res = @file_get_contents($apiUrl, false, $ctx);
            if ($res !== false) {
                $ok = true;
                logger('[jchat_tgnotify] Уведомление успешно отправлено в Telegram (file_get_contents)', 'INFO');
            } else {
                logger('[jchat_tgnotify] Ошибка отправки через file_get_contents', 'ERROR');
            }
        }
    } catch (\Exception $e) {
        logger('[jchat_tgnotify] Исключение при отправке уведомления: ' . $e->getMessage(), 'ERROR');
        $ok = false;
    }

    if ($ok) {
        jchat_tgnotify_mark_sent();
    }

    return $ok;
}

/**
 * Universal notification sender for all NGCMS events
 *
 * @param string $type Тип события: 'jchat', 'feedback', 'pm', 'complain', 'comment'
 * @param array $data Данные для уведомления
 * @return bool
 */
function ngcms_tg_notify(string $type, array $data): bool
{
    if (!jchat_tgnotify_enabled()) {
        logger('[ngcms_tg_notify] Уведомления отключены в настройках', 'INFO');
        return false;
    }

    // Проверяем, включен ли данный тип уведомлений
    $typeEnabled = extra_get_param('jchat_tgnotify', 'notify_' . $type);
    if (!$typeEnabled) {
        logger('[ngcms_tg_notify] Уведомления типа ' . $type . ' отключены', 'INFO');
        return false;
    }

    $token = jchat_tgnotify_get_token();
    $chatId = jchat_tgnotify_get_chat_id();

    if ($token === '' || $chatId === '') {
        logger('[ngcms_tg_notify] Не настроены токен или chat_id', 'WARNING');
        return false;
    }

    // Санитизация данных
    $title = sanitize($data['title'] ?? '');
    $author = sanitize($data['author'] ?? 'Гость');
    $text = sanitize($data['text'] ?? '');
    $url = sanitize($data['url'] ?? '');
    $datetime = sanitize($data['datetime'] ?? date('Y-m-d H:i:s'));

    $text = trim(strip_tags($text));
    if ($text === '') {
        logger('[ngcms_tg_notify] Пустое сообщение для типа ' . $type, 'WARNING');
        return false;
    }

    $text = mb_substr($text, 0, 800);

    // Формируем сообщение в зависимости от типа
    $emoji = [
        'jchat' => '💬',
        'feedback' => '📧',
        'pm' => '✉️',
        'complain' => '⚠️',
        'comment' => '💭',
    ];

    $typeNames = [
        'jchat' => 'Новое сообщение в чате',
        'feedback' => 'Новое обращение',
        'pm' => 'Личное сообщение',
        'complain' => 'Новая жалоба',
        'comment' => 'Новый комментарий',
    ];

    $icon = $emoji[$type] ?? '🔔';
    $typeName = $typeNames[$type] ?? 'Уведомление';

    $msg = "{$icon} {$typeName}\n";
    if ($title) $msg .= "📌 {$title}\n";
    $msg .= "👤 Автор: {$author}\n";
    $msg .= "🕒 Время: {$datetime}\n";
    if ($url) $msg .= "🔗 Ссылка: {$url}\n";
    $msg .= "\n📝 {$text}";

    $payload = [
        'chat_id' => $chatId,
        'text' => $msg,
        'disable_web_page_preview' => true,
    ];

    $apiUrl = "https://api.telegram.org/bot{$token}/sendMessage";

    $ok = false;

    try {
        if (function_exists('curl_init')) {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($res !== false && $err === '' && $httpCode >= 200 && $httpCode < 300) {
                $ok = true;
                logger('[ngcms_tg_notify] Уведомление ' . $type . ' успешно отправлено', 'INFO');
            } else {
                logger('[ngcms_tg_notify] Ошибка отправки ' . $type . ': ' . $err, 'ERROR');
            }
        } else {
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($payload),
                    'timeout' => 5,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $res = @file_get_contents($apiUrl, false, $ctx);
            if ($res !== false) {
                $ok = true;
                logger('[ngcms_tg_notify] Уведомление ' . $type . ' успешно отправлено (file_get_contents)', 'INFO');
            } else {
                logger('[ngcms_tg_notify] Ошибка отправки ' . $type, 'ERROR');
            }
        }
    } catch (\Exception $e) {
        logger('[ngcms_tg_notify] Исключение при отправке ' . $type . ': ' . $e->getMessage(), 'ERROR');
        $ok = false;
    }

    return $ok;
}

// =============================================================================
// PLUGIN INITIALIZATION (GLOBAL LOAD)
// =============================================================================
// This plugin provides universal notification function ngcms_tg_notify()
// that must be available globally for all other plugins (complain, comments, etc.)
// The plugin file is automatically loaded by NGCMS if it's active in plugins.php
// No additional initialization needed - just having this file loaded is enough.
