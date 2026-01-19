<?php

/**
 * Web Push Send - отправка уведомлений
 * Защищено секретным ключом
 *
 * Modified with ng-helpers inspired approach (2026)
 * - Added logging support
 * - Added validation
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
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'WebPush disabled'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверяем секретный ключ
$secret = $_GET['secret'] ?? $_POST['secret'] ?? '';
$configSecret = $pluginConfig['webpush']['send_secret'] ?? '';

if (!$secret || !$configSecret || $secret !== $configSecret) {
    webpush_log('Send attempt with invalid secret', 'warning');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
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

// Подключаем autoload для Web Push библиотеки (локальный в плагине)
$autoload = __DIR__ . '/lib/vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Web Push library not found. Install manually from GitHub or run: composer install in plugin folder'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $autoload;

// Проверяем наличие библиотеки
if (!class_exists(\Minishlink\WebPush\VAPID::class)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'minishlink/web-push not installed. Run: composer require minishlink/web-push'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Генерация VAPID ключей
 */
function generateVapidKeys(): array
{
    global $root, $pluginConfigFile, $pluginConfig;

    $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

    // Сохраняем в конфигурацию
    $pluginConfig['webpush']['vapid_public'] = $keys['publicKey'];
    $pluginConfig['webpush']['vapid_private'] = $keys['privateKey'];

    file_put_contents($pluginConfigFile, serialize($pluginConfig));

    return $keys;
}

/**
 * Загрузка подписок из БД
 */
function loadSubscriptionsFromDB(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM " . prefix . "_webpush_subscriptions");
    return $stmt->fetchAll();
}

/**
 * Удаление мёртвых подписок
 */
function removeDeadSubscriptions(PDO $pdo, array $hashes): int
{
    if (empty($hashes)) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($hashes), '?'));
    $stmt = $pdo->prepare("DELETE FROM " . prefix . "_webpush_subscriptions WHERE hash IN ($placeholders)");
    $stmt->execute($hashes);

    return $stmt->rowCount();
}

// Генерация VAPID ключей
if (($_GET['action'] ?? '') === 'genkeys') {
    try {
        $keys = generateVapidKeys();
        webpush_log('VAPID keys generated');
        echo json_encode([
            'ok' => true,
            'publicKey' => $keys['publicKey'],
            'privateKey' => $keys['privateKey'],
        ], JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
        webpush_log('VAPID key generation failed: ' . $e->getMessage(), 'error');
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Получаем параметры уведомления
$title = $_POST['title'] ?? $_GET['title'] ?? 'Уведомление';
$body = $_POST['body'] ?? $_GET['body'] ?? '';
$url = $_POST['url'] ?? $_GET['url'] ?? '/';
$icon = $_POST['icon'] ?? $_GET['icon'] ?? $pluginConfig['webpush']['default_icon'] ?? '';
$badge = $_POST['badge'] ?? $_GET['badge'] ?? $pluginConfig['webpush']['default_badge'] ?? '';

// Получаем VAPID настройки
$vapidPublic = $pluginConfig['webpush']['vapid_public'] ?? '';
$vapidPrivate = $pluginConfig['webpush']['vapid_private'] ?? '';
$vapidSubject = $pluginConfig['webpush']['vapid_subject'] ?? 'mailto:admin@example.com';

if (empty($vapidPublic) || empty($vapidPrivate)) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'VAPID keys are empty. Generate them: send.php?action=genkeys&secret=' . htmlspecialchars($configSecret),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Настройка аутентификации
$auth = [
    'VAPID' => [
        'subject' => $vapidSubject,
        'publicKey' => $vapidPublic,
        'privateKey' => $vapidPrivate,
    ],
];

// Создаём WebPush объект
try {
    $webPush = new \Minishlink\WebPush\WebPush($auth);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'WebPush initialization failed: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Подготавливаем payload
$payload = json_encode([
    'title' => $title,
    'body' => $body,
    'url' => $url,
    'icon' => $icon,
    'badge' => $badge,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Загружаем подписки
$subscriptions = loadSubscriptionsFromDB($pdo);

if (empty($subscriptions)) {
    echo json_encode(['ok' => true, 'sent' => 0, 'removed' => 0, 'message' => 'No subscriptions found'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Добавляем уведомления в очередь
foreach ($subscriptions as $sub) {
    if (empty($sub['endpoint']) || empty($sub['p256dh']) || empty($sub['auth'])) {
        continue;
    }

    try {
        $subscription = \Minishlink\WebPush\Subscription::create([
            'endpoint' => $sub['endpoint'],
            'publicKey' => $sub['p256dh'],
            'authToken' => $sub['auth'],
            'contentEncoding' => 'aesgcm',
        ]);

        $webPush->queueNotification($subscription, $payload);
    } catch (\Exception $e) {
        // Пропускаем невалидные подписки
        continue;
    }
}

// Отправляем уведомления
$sent = 0;
$deadHashes = [];

try {
    foreach ($webPush->flush() as $report) {
        if ($report->isSuccess()) {
            $sent++;
        } else {
            // Подписка мертва - добавляем в список на удаление
            $endpoint = (string)$report->getRequest()->getUri();
            $hash = hash('sha256', $endpoint);
            $deadHashes[] = $hash;
        }
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Send failed: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Удаляем мёртвые подписки
webpush_log(sprintf(
    'Push sent: title="%s", sent=%d, removed=%d, total=%d',
    substr($title, 0, 50),
    $sent,
    $removed,
    count($subscriptions)
));

$removed = removeDeadSubscriptions($pdo, $deadHashes);

echo json_encode([
    'ok' => true,
    'sent' => $sent,
    'removed' => $removed,
    'total' => count($subscriptions),
], JSON_UNESCAPED_UNICODE);
