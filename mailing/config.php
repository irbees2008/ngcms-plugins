<?php

/**
 * Admin UI for plugin "mailing"
 * Called via: admin.php?mod=extra-config&plugin=mailing
 *
 * @version 2.0.0
 */
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
require_once __DIR__ . '/lib/common.php';
require_once __DIR__ . '/lib/queue.php';
// Роутер действий
switch ($_REQUEST['action'] ?? 'settings') {
    case 'compose':
        show_compose();
        break;
    case 'campaigns':
        show_campaigns();
        break;
    case 'cron':
        show_cron();
        break;
    case 'upgrade':
        show_upgrade();
        break;
    case 'settings':
    default:
        show_settings();
        break;
}
/**
 * Страница настроек
 */
function show_settings()
{
    global $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/settings'), 'mailing', 1);
    // Обработка сохранения настроек
    if (isset($_REQUEST['submit'])) {
        pluginSetVariable('mailing', 'from_email', secure_html($_REQUEST['from_email'] ?? ''));
        pluginSetVariable('mailing', 'from_name', secure_html($_REQUEST['from_name'] ?? ''));
        pluginSetVariable('mailing', 'reply_to', secure_html($_REQUEST['reply_to'] ?? ''));
        pluginSetVariable('mailing', 'smtp_enable', isset($_REQUEST['smtp_enable']) && $_REQUEST['smtp_enable'] == '1' ? '1' : '0');
        pluginSetVariable('mailing', 'smtp_host', secure_html($_REQUEST['smtp_host'] ?? ''));
        pluginSetVariable('mailing', 'smtp_port', secure_html($_REQUEST['smtp_port'] ?? '587'));
        pluginSetVariable('mailing', 'smtp_auth', isset($_REQUEST['smtp_auth']) && $_REQUEST['smtp_auth'] == '1' ? '1' : '0');
        pluginSetVariable('mailing', 'smtp_user', secure_html($_REQUEST['smtp_user'] ?? ''));
        pluginSetVariable('mailing', 'smtp_pass', secure_html($_REQUEST['smtp_pass'] ?? ''));
        pluginSetVariable('mailing', 'smtp_secure', secure_html($_REQUEST['smtp_secure'] ?? 'tls'));
        pluginSetVariable('mailing', 'send_batch', intval($_REQUEST['send_batch'] ?? 50));
        pluginSetVariable('mailing', 'max_tries', intval($_REQUEST['max_tries'] ?? 3));
        pluginSetVariable('mailing', 'allow_iframe', isset($_REQUEST['allow_iframe']) && $_REQUEST['allow_iframe'] == '1' ? '1' : '0');
        pluginSetVariable('mailing', 'period', secure_html($_REQUEST['period'] ?? '5m'));
        pluginSetVariable('mailing', 'enable_tick', isset($_REQUEST['enable_tick']) && $_REQUEST['enable_tick'] == '1' ? '1' : '0');
        pluginSetVariable('mailing', 'tick_chance', intval($_REQUEST['tick_chance'] ?? 10));
        pluginSetVariable('mailing', 'cron_secret', secure_html($_REQUEST['cron_secret'] ?? ''));
        pluginSetVariable('mailing', 'auto_news_enable', isset($_REQUEST['auto_news_enable']) && $_REQUEST['auto_news_enable'] == '1' ? '1' : '0');
        pluginSetVariable('mailing', 'auto_news_category', intval($_REQUEST['auto_news_category'] ?? 0));
        pluginSetVariable('mailing', 'auto_news_groups', secure_html($_REQUEST['auto_news_groups'] ?? '[]'));
        pluginSetVariable('mailing', 'auto_news_scan_limit', intval($_REQUEST['auto_news_scan_limit'] ?? 5));
        pluginsSaveConfig();
        msg(array("text" => "Настройки успешно сохранены"));
    }
    // Получаем текущие значения
    $from_email = pluginGetVariable('mailing', 'from_email');
    $from_name = pluginGetVariable('mailing', 'from_name');
    $reply_to = pluginGetVariable('mailing', 'reply_to');
    $smtp_enable = pluginGetVariable('mailing', 'smtp_enable');
    $smtp_host = pluginGetVariable('mailing', 'smtp_host');
    $smtp_port = pluginGetVariable('mailing', 'smtp_port');
    $smtp_auth = pluginGetVariable('mailing', 'smtp_auth');
    $smtp_user = pluginGetVariable('mailing', 'smtp_user');
    $smtp_pass = pluginGetVariable('mailing', 'smtp_pass');
    $smtp_secure = pluginGetVariable('mailing', 'smtp_secure');
    $send_batch = pluginGetVariable('mailing', 'send_batch');
    $max_tries = pluginGetVariable('mailing', 'max_tries');
    $allow_iframe = pluginGetVariable('mailing', 'allow_iframe');
    $period = pluginGetVariable('mailing', 'period');
    $enable_tick = pluginGetVariable('mailing', 'enable_tick');
    $tick_chance = pluginGetVariable('mailing', 'tick_chance');
    $cron_secret = pluginGetVariable('mailing', 'cron_secret');
    $auto_news_enable = pluginGetVariable('mailing', 'auto_news_enable');
    $auto_news_category = pluginGetVariable('mailing', 'auto_news_category');
    $auto_news_groups = pluginGetVariable('mailing', 'auto_news_groups');
    $auto_news_scan_limit = pluginGetVariable('mailing', 'auto_news_scan_limit');
    // Рендерим шаблон настроек
    $xt = $twig->loadTemplate($tpath['config/settings'] . 'config/settings.tpl');
    $tVars = array(
        'from_email' => $from_email,
        'from_name' => $from_name,
        'reply_to' => $reply_to,
        'smtp_enable' => $smtp_enable,
        'smtp_host' => $smtp_host,
        'smtp_port' => $smtp_port,
        'smtp_auth' => $smtp_auth,
        'smtp_user' => $smtp_user,
        'smtp_pass' => $smtp_pass,
        'smtp_secure' => $smtp_secure,
        'send_batch' => $send_batch,
        'max_tries' => $max_tries,
        'allow_iframe' => $allow_iframe,
        'period' => $period,
        'enable_tick' => $enable_tick,
        'tick_chance' => $tick_chance,
        'cron_secret' => $cron_secret,
        'auto_news_enable' => $auto_news_enable,
        'auto_news_category' => $auto_news_category,
        'auto_news_groups' => $auto_news_groups,
        'auto_news_scan_limit' => $auto_news_scan_limit,
    );
    // Рендерим главный шаблон
    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
    print $xg->render(array('entries' => $xt->render($tVars)));
}
/**
 * Страница создания рассылки
 */
function show_compose()
{
    global $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/compose'), 'mailing', 1);
    $db = mailing_db();
    $tblCamp = mailing_tbl('mailing_campaigns');
    $tblAtt = mailing_tbl('mailing_attachments');
    // Обработка создания кампании
    if (isset($_POST['create_campaign'])) {
        $title = trim($_POST['title'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $html = $_POST['body_html'] ?? '';
        $text = $_POST['body_text'] ?? '';
        $sendAt = intval($_POST['send_at'] ?? time());
        $groups = array();
        if (!empty($_POST['groups'])) {
            foreach ((array)$_POST['groups'] as $g) $groups[] = intval($g);
        }
        $segment = array(
            'send_at' => $sendAt ? $sendAt : time(),
            'groups' => $groups,
            'only_active' => isset($_POST['only_active']) ? 1 : 0,
            'created_by' => intval($_POST['created_by'] ?? 0),
            'limit' => intval($_POST['limit'] ?? 0),
        );
        $cid = mailing_create_campaign_and_queue($title ?: $subject, $subject, $html, $text, $segment);
        // Обработка вложений
        $uploadDir = dirname(__FILE__) . '/uploads';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                if ($_FILES['attachments']['error'][$i] != UPLOAD_ERR_OK) continue;
                $origName = $_FILES['attachments']['name'][$i];
                $tmpPath = $_FILES['attachments']['tmp_name'][$i];
                $size = intval($_FILES['attachments']['size'][$i]);
                if ($size <= 0) continue;
                $safeName = preg_replace('#[^a-zA-Z0-9._-]+#', '_', $origName);
                $dest = $uploadDir . '/' . time() . '_' . $i . '_' . $safeName;
                if (@move_uploaded_file($tmpPath, $dest)) {
                    $mime = function_exists('mime_content_type') ? @mime_content_type($dest) : 'application/octet-stream';
                    $db->query("INSERT INTO " . $tblAtt . " (campaign_id, file_path, file_name, mime, size) VALUES (" .
                        intval($cid) . ", '" . db_squote($dest) . "', '" . db_squote($origName) . "', '" . db_squote($mime) . "', " . $size .
                        ")");
                }
            }
        }
        msg(array("text" => "Кампания создана, письма поставлены в очередь"));
        show_campaigns();
        return;
    }
    // Рендерим форму создания
    $xt = $twig->loadTemplate($tpath['config/compose'] . 'config/compose.tpl');
    $tVars = array();
    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
    print $xg->render(array('entries' => $xt->render($tVars)));
}
/**
 * Страница списка кампаний
 */
function show_campaigns()
{
    global $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/campaigns'), 'mailing', 1);
    $db = mailing_db();
    // Мягко добавим недостающие колонки статистики (если прав хватает)
    mailing_ensure_campaign_stats_columns();
    $hasStats = mailing_campaign_stats_supported();
    $tblCamp = mailing_tbl('mailing_campaigns');
    $rows = $db->select("SELECT * FROM " . $tblCamp . " ORDER BY id DESC LIMIT 50");
    $entries = array();
    if ($rows) {
        foreach ($rows as $r) {
            $cid = intval($r['id']);
            $q = $db->record("SELECT COUNT(*) as c, SUM(status='sent') as s, SUM(status='fail') as f FROM " . mailing_tbl('mailing_queue') . " WHERE campaign_id=" . $cid);
            $sendAt = intval($r['send_at']);
            $entries[] = array(
                'id' => $cid,
                'subject' => $r['subject'],
                'status' => $r['status'],
                'send_at_formatted' => $sendAt ? date('Y-m-d H:i', $sendAt) : '',
                'queue_total' => intval($q['c']),
                'queue_sent' => intval($q['s']),
                'queue_failed' => intval($q['f']),
                // Статистика кампании: используем колонки если есть, иначе считаем по очереди
                'sent_count' => $hasStats ? intval($r['sent_count'] ?? 0) : intval($q['s']),
                'delivered_count' => $hasStats ? intval($r['delivered_count'] ?? 0) : intval($q['s']),
                'failed_count' => $hasStats ? intval($r['failed_count'] ?? 0) : intval($q['f']),
            );
        }
    }
    // Рендерим список кампаний
    $xt = $twig->loadTemplate($tpath['config/campaigns'] . 'config/campaigns.tpl');
    $tVars = array(
        'entries' => $entries,
        'hasStats' => $hasStats,
    );
    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
    print $xg->render(array('entries' => $xt->render($tVars)));
}
/**
 * Страница CRON информации
 */
function show_cron()
{
    global $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/cron'), 'mailing', 1);
    $period = pluginGetVariable('mailing', 'period');
    $cron_secret = pluginGetVariable('mailing', 'cron_secret');
    $enable_tick = pluginGetVariable('mailing', 'enable_tick');
    $tick_chance = pluginGetVariable('mailing', 'tick_chance');
    $periods = [
        '0' => 'Отключено',
        '5m' => '5 минут',
        '10m' => '10 минут',
        '15m' => '15 минут',
        '1h' => '1 час',
        '2h' => '2 часа',
        '3h' => '3 часа',
        '4h' => '4 часа',
        '6h' => '6 часов',
        '8h' => '8 часов',
        '12h' => '12 часов',
        '1d' => '1 день'
    ];
    $period_label = $periods[$period] ?? 'Не настроен';
    $cron_url = mailing_base_url() . '/?mailing_cron=1&secret=' . $cron_secret;
    // Рендерим информацию о CRON
    $xt = $twig->loadTemplate($tpath['config/cron'] . 'config/cron.tpl');
    $tVars = array(
        'period_label' => $period_label,
        'period' => $period,
        'cron_secret' => $cron_secret,
        'cron_url' => $cron_url,
        'enable_tick' => $enable_tick,
        'tick_chance' => $tick_chance,
    );
    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
    print $xg->render(array('entries' => $xt->render($tVars)));
}
/**
 * Страница обновления схемы БД (мягкая миграция)
 */
function show_upgrade()
{
    global $twig;
    $tpath = locatePluginTemplates(array('config/main'), 'mailing', 1);
    $ok = mailing_ensure_campaign_stats_columns();
    $msg = $ok
        ? '<div class="alert alert-success">Колонки статистики кампаний проверены/добавлены. Статистика будет обновляться при отправке.</div>'
        : '<div class="alert alert-warning">Не удалось добавить колонки статистики (возможно, недостаточно прав). Плагин продолжит работать с расчётом по очереди.</div>';
    $links = '<p><a class="btn btn-sm btn-outline-success" href="admin.php?mod=extra-config&plugin=mailing&action=campaigns">Перейти к кампаниям</a></p>';
    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
    print $xg->render(array('entries' => $msg . $links));
}
