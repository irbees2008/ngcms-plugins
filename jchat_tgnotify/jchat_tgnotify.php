<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

//
// jChat Telegram Notifications Plugin
// Sends Telegram notifications about new chat messages
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
    // DEBUG: логирование вызова
    @file_put_contents(
        __DIR__ . '/debug.log',
        date('[Y-m-d H:i:s] ') . "ВЫЗОВ jchat_tgnotify_send\n" .
            "Данные: " . print_r($data, true) . "\n",
        FILE_APPEND
    );

    if (!jchat_tgnotify_should_send($data)) {
        @file_put_contents(
            __DIR__ . '/debug.log',
            date('[Y-m-d H:i:s] ') . "БЛОКИРОВАНО фильтрами\n\n",
            FILE_APPEND
        );
        return false;
    }

    $token  = jchat_tgnotify_get_token();
    $chatId = jchat_tgnotify_get_chat_id();

    $author   = $data['author']   ?? 'Guest';
    $text     = $data['text']     ?? '';
    $datetime = $data['datetime'] ?? date('Y-m-d H:i:s');
    $ip       = $data['ip']       ?? '';
    $url      = $data['url']      ?? '';

    $text = trim($text);
    if ($text === '') return false;

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

    if (function_exists('curl_init')) {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $ok = ($res !== false && $err === '');
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 5,
            ],
        ]);
        $res = @file_get_contents($apiUrl, false, $ctx);
        $ok = ($res !== false);
    }

    if ($ok) {
        jchat_tgnotify_mark_sent();
    }

    return $ok;
}
