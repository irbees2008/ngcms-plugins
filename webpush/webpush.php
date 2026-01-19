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
    global $template, $twig;

    // Проверяем, включен ли плагин
    $enabled = pluginGetVariable('webpush', 'enabled');
    if (!$enabled) {
        $template['vars']['webpush'] = '<!-- WebPush: disabled -->';
        logger('webpush', 'Plugin disabled in config');
        return;
    }

    // Проверяем, нужно ли показывать кнопку
    $showButton = pluginGetVariable('webpush', 'show_button');
    if (!$showButton) {
        $template['vars']['webpush'] = '<!-- WebPush: button hidden -->';
        logger('webpush', 'Button hidden in config');
        return;
    }

    // Загружаем локализацию
    LoadPluginLang('webpush', 'site', '', '', ':');

    // Находим шаблон
    $tpath = locatePluginTemplates(['webpush'], 'webpush', pluginGetVariable('webpush', 'localsource'));

    if (!isset($tpath['webpush'])) {
        $template['vars']['webpush'] = '<!-- WebPush: template not found -->';
        logger('webpush', 'Template not found: ' . var_export($tpath, true));
        return;
    }

    // Подготавливаем переменные для шаблона
    $tvars = [
        'endpoint' => home . '/engine/plugins/webpush/endpoint.php',
        'subscribe_text' => pluginGetVariable('webpush', 'subscribe_text') ?: 'Включить уведомления',
        'unsubscribe_text' => $GLOBALS['lang']['webpush:unsubscribe_text'] ?? 'Отключить уведомления',
        'js_path' => home . '/engine/plugins/webpush/js/webpush.js',
        'public_key' => pluginGetVariable('webpush', 'vapid_public'),
    ];

    // Логируем для отладки
    logger('webpush', sprintf(
        'Injecting code: enabled=%d, showButton=%d, template=%s, IP=%s',
        $enabled,
        $showButton,
        $tpath['webpush'],
        get_ip()
    ));

    // Генерируем HTML через Twig
    try {
        $xt = $twig->loadTemplate($tpath['webpush'] . 'webpush.tpl');
        $template['vars']['webpush'] = $xt->render($tvars);

        logger('webpush', 'Code injected successfully');
    } catch (Exception $e) {
        $template['vars']['webpush'] = '<!-- WebPush: Error rendering template: ' . htmlspecialchars($e->getMessage()) . ' -->';
        logger('webpush', 'Error rendering template: ' . $e->getMessage());
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
        logger('webpush', 'Send notification cancelled: plugin disabled');
        return ['ok' => false, 'error' => 'Plugin disabled'];
    }

    // Получаем VAPID ключи
    $vapidPublic = pluginGetVariable('webpush', 'vapid_public');
    $vapidPrivate = pluginGetVariable('webpush', 'vapid_private');
    $vapidSubject = pluginGetVariable('webpush', 'vapid_subject');

    if (empty($vapidPublic) || empty($vapidPrivate)) {
        logger('webpush', 'Send notification failed: VAPID keys not configured');
        return ['ok' => false, 'error' => 'VAPID keys not configured'];
    }

    // Подключаем библиотеку Web Push
    $autoload = __DIR__ . '/lib/vendor/autoload.php';
    if (!file_exists($autoload)) {
        logger('webpush', 'Send notification failed: library not found');
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
        logger('webpush', 'WebPush init error: ' . $e->getMessage());
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
        logger('webpush', 'Send notification cancelled: no subscriptions');
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
        logger('webpush', 'Send error: ' . $e->getMessage());
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

    logger('webpush', sprintf(
        'Push sent: title="%s", sent=%d, removed=%d, total=%d',
        substr($title, 0, 50),
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

    /*logger('webpush', sprintf(
        'Stats requested: total=%d, today=%d, week=%d, IP=%s',
        $stats['total'],
        $stats['today'],
        $stats['week'],
        get_ip()
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
            \Plugins\logger('webpush', sprintf(
                'Auto-sent for news #%d: "%s", sent=%d',
                $newsID,
                mb_substr($title, 0, 50),
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
                    \Plugins\logger('webpush', sprintf(
                        'Mailing integration: queued email for news #%d',
                        $newsID
                    ));
                }
            } catch (Exception $e) {
                if (function_exists('Plugins\logger')) {
                    \Plugins\logger('webpush', sprintf(
                        'Mailing integration error: %s',
                        $e->getMessage()
                    ));
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
