<?php

/**
 * Mailing plugin for NGCMS
 *
 * Email рассылки для зарегистрированных пользователей с сегментацией
 * - Вложения (изображения, pdf, xls, doc и т.д.)
 * - Ссылка отписки + заголовки List-Unsubscribe
 * - Отложенная отправка + обработка очереди
 * - Опционально: авто-рассылка новых опубликованных новостей
 *
 * Modified with ng-helpers v0.2.0 functions (2026)
 * - Added validate_email for email validation
 * - Added array_pluck for data extraction
 * - Added benchmark for performance measurement
 * - Added logger for sending logs
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 * @requires NGCMS with Twig support
 */

if (!defined('NGCMS')) die('HAL');

// Import ng-helpers functions
use function Plugins\{validate_email, array_pluck, benchmark, logger};

require_once __DIR__ . '/lib/common.php';
require_once __DIR__ . '/lib/mailer.php';
require_once __DIR__ . '/lib/queue.php';

// Регистрация актов (хуков)
add_act('core', 'mailing_tick');           // фоновая обработка при посещениях
add_act('index', 'mailing_router');         // обработка отписки и cron
add_act('admin', 'mailing_admin');          // админ-модуль
add_act('admin:mod:news', 'mailing_news_admin_hook');

// Регистрация Twig-функций для вывода в шаблонах
if (function_exists('twigRegisterFunction')) {
    twigRegisterFunction('mailing', 'stats', 'mailing_twig_stats');
    twigRegisterFunction('mailing', 'form', 'mailing_twig_subscription_form');
}

/**
 * Обработка очереди при посещениях сайта (если включено)
 * Рекомендуется также настроить cron для более надёжной работы
 */
function mailing_tick(): void
{
    if (!mailing_cfg_bool('enable_tick', false)) {
        return;
    }

    // Случайный запуск для снижения нагрузки при высоком трафике
    $chance = max(1, min(100, (int)mailing_cfg('tick_chance', '10')));

    if (mt_rand(1, 100) > $chance) {
        return;
    }

    mailing_process_due_queue();

    if (mailing_cfg_bool('auto_news_enable', false)) {
        mailing_autonews_scan_and_queue();
    }
}

/**
 * Маршрутизатор для обработки отписки и cron-задач
 *
 * Endpoints:
 * - /?mailing_unsub=1&token=... - отписка от рассылки
 * - /?mailing_cron=1&secret=... - запуск обработки очереди через cron
 */
function mailing_router(): void
{
    // Обработка отписки
    if (!empty($_REQUEST['mailing_unsub'])) {
        $token = trim($_REQUEST['token'] ?? '');
        mailing_handle_unsubscribe($token);
        return;
    }

    // Обработка cron-запросов
    if (!empty($_REQUEST['mailing_cron'])) {
        $secret = trim($_REQUEST['secret'] ?? '');
        $configSecret = mailing_cfg('cron_secret', '');

        if (!$configSecret || !hash_equals($configSecret, $secret)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }

        // Запуск обработки
        mailing_process_due_queue();

        if (mailing_cfg_bool('auto_news_enable', false)) {
            mailing_autonews_scan_and_queue();
        }

        header('Content-Type: text/plain; charset=UTF-8');
        echo "OK\n";
        exit;
    }
}

/**
 * Точка входа для админ-панели
 * Обычно настройки плагина доступны через: admin.php?mod=extra-config&plugin=mailing
 */
function mailing_admin(): void
{
    if (!defined('IN_ADMIN')) {
        return;
    }

    if (($_REQUEST['mod'] ?? '') === 'mailing') {
        require_once __DIR__ . '/config.php';
        exit;
    }
}

/**
 * Хук для модуля новостей в админке
 * Placeholder для будущей интеграции с событиями публикации новостей
 */
function mailing_news_admin_hook(): void
{
    // Будущая интеграция: автоматическая постановка в очередь при публикации
    // Сейчас используется периодическое сканирование в mailing_autonews_scan_and_queue()
}

/**
 * CRON-обработчик для системного крона NGCMS
 * Вызывается автоматически через syscron.php при настроенном cron
 *
 * Для настройки добавьте в crontab:
 * каждые 5 минут: /usr/bin/php /path/to/site/syscron.php >/dev/null 2>&1
 *
 * Или через wget/curl каждые 5 минут:
 * wget -q -O - http://your-site.ru/syscron.php >/dev/null 2>&1
 */
function plugin_mailing_cron(): void
{
    // Проверяем настройки периода обработки
    $period = mailing_cfg('period', '5m');

    // Если период = 0, значит cron отключен
    if ($period === '0') {
        return;
    }

    // Логируем начало выполнения
    logger("Mailing CRON START", 'info', 'mailing_cron.log');

    // Замер производительности обработки очереди
    $result = benchmark(function () {
        // Обработка очереди отправки
        mailing_process_due_queue();

        // Авто-рассылка новых новостей (если включено)
        if (mailing_cfg_bool('auto_news_enable', false)) {
            mailing_autonews_scan_and_queue();
        }
    });

    // Логируем завершение с метриками производительности
    logger("Mailing CRON FINISHED in {$result['time']}s, Memory: {$result['memory']}, Peak: {$result['peak_memory']}", 'info', 'mailing_cron.log');
}

/**
 * Twig-функция: статистика рассылок
 * Использование в шаблоне: {{ callPlugin("mailing.stats") }}
 */
function mailing_twig_stats(array $params = []): string
{
    global $twig;

    $tpath = locatePluginTemplates(['stats'], 'mailing', 1);
    if (empty($tpath['stats'])) {
        return '';
    }

    $db = mailing_db();
    $tblCamp = mailing_tbl('mailing_campaigns');
    $tblQueue = mailing_tbl('mailing_queue');

    $stats = $db->record(
        "SELECT
            COUNT(DISTINCT c.id) as total_campaigns,
            SUM(CASE WHEN q.status='sent' THEN 1 ELSE 0 END) as total_sent,
            SUM(CASE WHEN q.status='pending' THEN 1 ELSE 0 END) as total_pending
        FROM {$tblCamp} c
        LEFT JOIN {$tblQueue} q ON q.campaign_id = c.id"
    );

    $tvars = [
        'total_campaigns' => (int)($stats['total_campaigns'] ?? 0),
        'total_sent' => (int)($stats['total_sent'] ?? 0),
        'total_pending' => (int)($stats['total_pending'] ?? 0),
    ];

    $template = $twig->loadTemplate($tpath['stats'] . 'stats.tpl');
    return $template->render($tvars);
}

/**
 * Twig-функция: форма подписки на рассылку
 * Использование: {{ callPlugin("mailing.form") }}
 */
function mailing_twig_subscription_form(array $params = []): string
{
    global $twig, $userROW;

    $tpath = locatePluginTemplates(['subscription_form'], 'mailing', 1);
    if (empty($tpath['subscription_form'])) {
        return '';
    }

    $isSubscribed = false;
    if (is_array($userROW)) {
        $userId = (int)$userROW['id'];
        $email = $userROW['mail'] ?? $userROW['email'] ?? '';
        $isSubscribed = !mailing_is_unsubscribed($userId, $email);
    }

    $tvars = [
        'is_logged' => is_array($userROW),
        'is_subscribed' => $isSubscribed,
        'action_url' => '/?mailing_subscribe=1',
    ];

    $template = $twig->loadTemplate($tpath['subscription_form'] . 'subscription_form.tpl');
    return $template->render($tvars);
}
