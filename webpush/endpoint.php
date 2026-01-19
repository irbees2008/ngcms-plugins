<?php

/**
 * Web Push Endpoint - публичный API для подписок
 * Отдаёт публичный ключ и принимает подписки/отписки
 *
 * Modified with ng-helpers inspired approach (2026)
 * - Added URL validation
 * - Added logging support
 * - Added mobile detection
 */

// Отключаем отображение ошибок
ini_set('display_errors', '0');
error_reporting(0);

// Устанавливаем заголовок JSON сразу
header('Content-Type: application/json; charset=utf-8');

// Минимальная инициализация без core.php
$root = dirname(__DIR__, 3);

/**
 * Simple logger for standalone scripts
 */
function webpush_log($message, $level = 'info')
{
    global $root;
    $logDir = $root . '/engine/plugins/webpush/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/webpush.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $entry = "[$timestamp] [$level] $message | IP: $ip\n";
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Validate URL format
 */
function validate_webpush_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Detect mobile device
 */
function is_webpush_mobile()
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/(android|iphone|ipad|mobile)/i', $ua);
}

// Подключаем только конфигурацию
require_once $root . '/engine/conf/config.php';

// Устанавливаем константы
if (!defined('prefix')) {
    define('prefix', $config['prefix'] ?? 'ng');
}

// Загружаем конфигурацию плагинов из сериализованного файла
$pluginConfigFile = $root . '/engine/conf/plugdata.php';
$pluginConfig = [];

if (file_exists($pluginConfigFile) && filesize($pluginConfigFile) > 0) {
    $content = file_get_contents($pluginConfigFile);
    $pluginConfig = unserialize($content);
}

// Проверяем, включен ли плагин
if (empty($pluginConfig['webpush']['enabled'])) {
    webpush_log('WebPush disabled, endpoint access denied', 'warning');
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'WebPush disabled'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Создаём прямое PDO подключение
try {
    $dsn = "mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['dbuser'], $config['dbpasswd'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Загрузка подписок из БД
 */
function loadSubscriptions(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT endpoint, p256dh, auth FROM " . prefix . "_webpush_subscriptions ORDER BY id ASC");
    return $stmt->fetchAll();
}

/**
 * Сохранение подписки в БД
 */
function saveSubscription(PDO $pdo, array $data): bool
{
    $hash = hash('sha256', $data['endpoint']);
    $time = time();

    // Проверяем, существует ли подписка
    $stmt = $pdo->prepare("SELECT id FROM " . prefix . "_webpush_subscriptions WHERE hash = ?");
    $stmt->execute([$hash]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Обновляем существующую
        $stmt = $pdo->prepare(
            "UPDATE " . prefix . "_webpush_subscriptions SET " .
                "endpoint = ?, p256dh = ?, auth = ?, user_agent = ?, ip = ?, updated = ? " .
                "WHERE hash = ?"
        );
        $stmt->execute([
            $data['endpoint'],
            $data['p256dh'] ?? '',
            $data['auth'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $time,
            $hash
        ]);
    } else {
        // Создаём новую
        $stmt = $pdo->prepare(
            "INSERT INTO " . prefix . "_webpush_subscriptions " .
                "(hash, endpoint, p256dh, auth, user_agent, ip, created, updated) VALUES " .
                "(?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $hash,
            $data['endpoint'],
            $data['p256dh'] ?? '',
            $data['auth'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $time,
            $time
        ]);
    }

    return true;
}

/**
 * Удаление подписки из БД
 */
function removeSubscription(PDO $pdo, string $endpoint): bool
{
    $hash = hash('sha256', $endpoint);
    $stmt = $pdo->prepare("DELETE FROM " . prefix . "_webpush_subscriptions WHERE hash = ?");
    $stmt->execute([$hash]);

    return true;
}

// Определяем действие
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Отдаём публичный ключ
if ($action === 'key') {
    $publicKey = $pluginConfig['webpush']['vapid_public'] ?? '';
    webpush_log('Public key requested');
    echo json_encode([
        'ok' => true,
        'publicKey' => $publicKey,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Читаем тело запроса
$body = file_get_contents('php://input');
$payload = json_decode($body ?: '{}', true);

if (!is_array($payload)) {
    $payload = [];
}

$endpoint = $payload['endpoint'] ?? '';
$keys = $payload['keys'] ?? [];
$p256dh = $keys['p256dh'] ?? '';
$auth = $keys['auth'] ?? '';

// Проверяем наличие endpoint
if (empty($endpoint)) {
    webpush_log('Subscription attempt without endpoint', 'warning');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No endpoint'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Валидация URL endpoint
if (!validate_webpush_url($endpoint)) {
    webpush_log('Invalid endpoint URL: ' . substr($endpoint, 0, 100), 'warning');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid endpoint URL'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Отписка
if ($action === 'unsubscribe') {
    removeSubscription($pdo, $endpoint);
    webpush_log('Unsubscribe: ' . parse_url($endpoint, PHP_URL_HOST));
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

// Подписка / обновление
$deviceType = is_webpush_mobile() ? 'mobile' : 'desktop';
saveSubscription($pdo, [
    'endpoint' => $endpoint,
    'p256dh' => $p256dh,
    'auth' => $auth,
]);
webpush_log('Subscribe: ' . parse_url($endpoint, PHP_URL_HOST) . ' (' . $deviceType . ')');

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
