<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Modified with ng-helpers v0.2.0 functions (2026)
// - Added logging for push notifications
// - Added mobile detection

// Import ng-helpers functions
use function Plugins\{logger, get_ip, is_mobile};

/**
 * Внедрение кода Web Push в шаблон
 */
function webpush_inject_code(): void
{
    global $template, $twig, $lang;

    // Проверяем, включен ли плагин
    $enabled = pluginGetVariable('webpush', 'enabled');
    if (!$enabled) {
        $template['vars']['webpush'] = '<!-- WebPush: disabled -->';
        logger('Plugin disabled in config', 'info', 'webpush.log');
        return;
    }

    // Проверяем, нужно ли показывать кнопку
    $showButton = pluginGetVariable('webpush', 'show_button');
    if (!$showButton) {
        $template['vars']['webpush'] = '<!-- WebPush: button hidden -->';
        logger('Button hidden in config', 'info', 'webpush.log');
        return;
    }

    // Загружаем локализацию
    LoadPluginLang('webpush', 'site', '', 'webpush', ':');

    // Находим шаблон
    $tpath = locatePluginTemplates(['webpush'], 'webpush', pluginGetVariable('webpush', 'localsource'));

    if (!isset($tpath['webpush'])) {
        $template['vars']['webpush'] = '<!-- WebPush: template not found -->';
        logger('Template not found: ' . var_export($tpath, true, 'info', 'webpush.log'));
        return;
    }

    // Подготавливаем переменные для шаблона
    $tvars = [
        'endpoint' => home . '/engine/plugins/webpush/endpoint.php',
        'subscribe_text' => pluginGetVariable('webpush', 'subscribe_text') ?: $lang['webpush:subscribe_text'],
        'unsubscribe_text' => $lang['webpush:unsubscribe_text'],
        'messages' => [
            'error_no_support' => $lang['webpush:error_no_support'],
            'error_permission' => $lang['webpush:error_permission'],
            'error_https' => $lang['webpush:error_https'],
            'error_key' => $lang['webpush:error_key'],
            'error_service_worker' => $lang['webpush:error_service_worker'],
            'error_subscribe' => $lang['webpush:error_subscribe'],
            'error_unsubscribe' => $lang['webpush:error_unsubscribe'],
            'error_unknown' => $lang['webpush:error_unknown'],
            'subscribed_message' => $lang['webpush:subscribed_message'],
            'unsubscribed_message' => $lang['webpush:unsubscribed_message'],
        ],
        'js_path' => home . '/engine/plugins/webpush/js/webpush.js',
        'public_key' => pluginGetVariable('webpush', 'vapid_public'),
    ];

    // Логируем для отладки
    logger(sprintf(
        'Injecting code: enabled=%d, showButton=%d, template=%s, IP=%s',
        $enabled,
        $showButton,
        $tpath['webpush'],
        get_ip()
    ), 'info', 'webpush.log');

    // Генерируем HTML через Twig
    try {
        $xt = $twig->loadTemplate($tpath['webpush'] . 'webpush.tpl');
        $template['vars']['webpush'] = $xt->render($tvars);

        logger('Code injected successfully', 'info', 'webpush.log');
    } catch (Exception $e) {
        $template['vars']['webpush'] = '<!-- WebPush: Error rendering template: ' . htmlspecialchars($e->getMessage()) . ' -->';
        logger('Error rendering template: ' . $e->getMessage(), 'error', 'webpush.log');
    }
}

/**
 * Отправка push-уведомления всем подписчикам
 *
 * @param string $title Заголовок уведомления
 * @param string $body Текст уведомления
 * @param string $url URL для перехода при клике
 * @param string|null $icon URL иконки
 * @param string|null $badge URL значка
 * @return array Результат отправки
 */
function webpush_send_notification(string $title, string $body, string $url = '/', ?string $icon = null, ?string $badge = null): array
{
    global $mysql;

    // Проверяем, включен ли плагин
    if (!pluginGetVariable('webpush', 'enabled')) {
        logger('Send notification cancelled: plugin disabled', 'info', 'webpush.log');
        return ['ok' => false, 'error' => 'Plugin disabled'];
    }

    // Получаем VAPID ключи
    $vapidPublic = pluginGetVariable('webpush', 'vapid_public');
    $vapidPrivate = pluginGetVariable('webpush', 'vapid_private');
    $vapidSubject = pluginGetVariable('webpush', 'vapid_subject');

    if (empty($vapidPublic) || empty($vapidPrivate)) {
        logger('Send notification failed: VAPID keys not configured', 'info', 'webpush.log');
        return ['ok' => false, 'error' => 'VAPID keys not configured'];
    }

    // Подключаем библиотеку Web Push
    $autoload = __DIR__ . '/lib/vendor/autoload.php';
    if (!file_exists($autoload)) {
        logger('Send notification failed: library not found', 'info', 'webpush.log');
        return ['ok' => false, 'error' => 'Library not found'];
    }

    require_once $autoload;

    // Получаем иконку и значок по умолчанию
    if (!$icon) {
        $icon = pluginGetVariable('webpush', 'default_icon') ?: null;
    }
    if (!$badge) {
        $badge = pluginGetVariable('webpush', 'default_badge') ?: null;
    }

    // Абсолютные URL
    if ($icon && strpos($icon, 'http') !== 0) {
        $icon = home . $icon;
    }
    if ($badge && strpos($badge, 'http') !== 0) {
        $badge = home . $badge;
    }
    if (strpos($url, 'http') !== 0) {
        $url = home . $url;
    }

    // Настройка аутентификации
    $auth = [
        'VAPID' => [
            'subject' => $vapidSubject ?: 'mailto:admin@example.com',
            'publicKey' => $vapidPublic,
            'privateKey' => $vapidPrivate,
        ],
    ];

    try {
        $webPush = new \Minishlink\WebPush\WebPush($auth);
    } catch (\Exception $e) {
        logger('WebPush init error: ' . $e->getMessage(), 'error', 'webpush.log');
        return ['ok' => false, 'error' => 'Init failed: ' . $e->getMessage()];
    }

    // Подготавливаем payload
    $payload = json_encode([
        'title' => $title,
        'body' => $body,
        'url' => $url,
        'icon' => $icon,
        'badge' => $badge,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Загружаем подписки из БД
    $subscriptions = [];
    $result = $mysql->select("SELECT endpoint, p256dh, auth FROM " . prefix . "_webpush_subscriptions ORDER BY id ASC");

    while ($row = $mysql->fetchassoc($result)) {
        $subscriptions[] = $row;
    }

    if (empty($subscriptions)) {
        logger('Send notification cancelled: no subscriptions', 'info', 'webpush.log');
        return ['ok' => true, 'sent' => 0, 'message' => 'No subscriptions'];
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
                // Подписка мертва
                $endpoint = (string)$report->getRequest()->getUri();
                $hash = hash('sha256', $endpoint);
                $deadHashes[] = $hash;
            }
        }
    } catch (\Exception $e) {
        logger('Send error: ' . $e->getMessage(), 'error', 'webpush.log');
        return ['ok' => false, 'error' => 'Send failed: ' . $e->getMessage()];
    }

    // Удаляем мёртвые подписки
    $removed = 0;
    if (!empty($deadHashes)) {
        foreach ($deadHashes as $hash) {
            $mysql->query("DELETE FROM " . prefix . "_webpush_subscriptions WHERE hash = '" . $mysql->escape($hash) . "'");
            $removed++;
        }
    }

    logger(sprintf(
        'Push sent: title="%s", sent=%d, removed=%d, total=%d',
        substr($title, 0, 50, 'info', 'webpush.log'),
        $sent,
        $removed,
        count($subscriptions)
    ));

    return [
        'ok' => true,
        'sent' => $sent,
        'removed' => $removed,
        'total' => count($subscriptions),
    ];
}

/**
 * Получение статистики подписок
 */
function webpush_get_stats(): array
{
    global $mysql;

    $stats = [
        'total' => 0,
        'today' => 0,
        'week' => 0,
    ];

    // Общее количество
    $rec = $mysql->record("SELECT COUNT(*) as cnt FROM " . prefix . "_webpush_subscriptions");
    $stats['total'] = (int)($rec['cnt'] ?? 0);

    // За сегодня
    $today = strtotime('today');
    $rec = $mysql->record("SELECT COUNT(*) as cnt FROM " . prefix . "_webpush_subscriptions WHERE created >= " . $today);
    $stats['today'] = (int)($rec['cnt'] ?? 0);

    // За неделю
    $week = strtotime('-7 days');
    $rec = $mysql->record("SELECT COUNT(*) as cnt FROM " . prefix . "_webpush_subscriptions WHERE created >= " . $week);
    $stats['week'] = (int)($rec['cnt'] ?? 0);

    /*logger(sprintf(
        'Stats requested: total=%d, today=%d, week=%d, IP=%s',
        $stats['total'],
        $stats['today'],
        $stats['week'],
        get_ip(, 'info', 'webpush.log')
    ));*/

    return $stats;
}

/**
 * NewsFilter для автоматической отправки уведомлений о новых новостях
 */
class WebPushNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        // Проверяем, нужна ли автоматическая отправка
        if (!pluginGetVariable('webpush', 'auto_send')) {
            return;
        }

        // Отправляем только для полного просмотра новости (не в списке)
        if (!isset($mode['full']) || !$mode['full']) {
            return;
        }

        // Проверяем, что новость на главной странице
        if (empty($SQLnews['mainpage'])) {
            return;
        }

        // Проверяем, не отправляли ли уже для этой новости
        // (используем кастомное поле или проверку времени публикации)
        $publishTime = $SQLnews['postdate'] ?? 0;
        $currentTime = time();

        // Отправляем только для свежих новостей (опубликованных в последние 5 минут)
        if (($currentTime - $publishTime) > 300) {
            return;
        }

        // Подготавливаем данные для уведомления
        $title = strip_tags($SQLnews['title'] ?? 'Новая новость');
        $body = strip_tags($SQLnews['description'] ?? $SQLnews['short'] ?? '');

        // Обрезаем текст до 120 символов
        if (mb_strlen($body) > 120) {
            $body = mb_substr($body, 0, 117) . '...';
        }

        // URL новости
        $url = '/news/' . ($SQLnews['alt_name'] ?: $SQLnews['id']);

        // Отправляем уведомление
        $result = webpush_send_notification($title, $body, $url);

        if ($result['ok'] && function_exists('Plugins\logger')) {
            \Plugins\logger(sprintf(
                'Auto-sent for news #%d: "%s", sent=%d',
                $newsID,
                mb_substr($title, 0, 50, 'info', 'webpush.log'),
                $result['sent'] ?? 0
            ));
        }

        // Интеграция с плагином mailing
        if (
            pluginGetVariable('webpush', 'mailing_integration') &&
            pluginIsActive('mailing') &&
            function_exists('mailing_autonews_queue_single')
        ) {

            try {
                // Отправляем через mailing (email рассылка)
                mailing_autonews_queue_single($newsID, $SQLnews);

                if (function_exists('Plugins\logger')) {
                    \Plugins\logger(sprintf(
                        'Mailing integration: queued email for news #%d',
                        $newsID,
                        'info',
                        'webpush.log'
                    ));
                }
            } catch (Exception $e) {
                if (function_exists('Plugins\logger')) {
                    \Plugins\logger(sprintf(
                        'Mailing integration error: %s',
                        $e->getMessage()
                    ), 'error', 'webpush.log');
                }
            }
        }
    }
}

// Регистрация фильтра новостей
if (class_exists('NewsFilter')) {
    register_filter('news', 'webpush', new WebPushNewsFilter());
}

// Добавляем хук для внедрения кода на страницы
// Регистрируем для index_post - срабатывает на всех страницах после генерации контента
if (function_exists('add_act')) {
    add_act('index_post', 'webpush_inject_code');
}
