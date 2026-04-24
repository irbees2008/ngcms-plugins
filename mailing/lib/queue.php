<?php

/**
 * Queue processing and campaign management for Mailing plugin
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 */

if (!defined('NGCMS')) die('HAL');

/**
 * Обработка отписки от рассылки
 */
function mailing_handle_unsubscribe(string $token): void
{
    $db = mailing_db();
    $token = trim($token);

    if (strlen($token) < 16) {
        mailing_render_simple_page('Ошибка', 'Некорректная ссылка отписки.');
        exit;
    }

    $tblUnsub = mailing_tbl('mailing_unsub');
    $rec = $db->record("SELECT * FROM {$tblUnsub} WHERE token = '" . db_squote($token) . "' LIMIT 1");

    if (!$rec) {
        mailing_render_simple_page('Ошибка', 'Ссылка отписки не найдена или устарела.');
        exit;
    }

    if ((int)$rec['unsub_at'] > 0) {
        mailing_render_simple_page('Готово', 'Вы уже отписаны от рассылки.');
        exit;
    }

    $unsubTime = mailing_now();
    $recId = (int)$rec['id'];
    $db->query("UPDATE {$tblUnsub} SET unsub_at = {$unsubTime} WHERE id = {$recId}");

    mailing_render_simple_page('Готово', 'Вы отписались от рассылки. Спасибо!');
    exit;
}

/**
 * Отображение простой HTML-страницы
 */
function mailing_render_simple_page(string $title, string $message): void
{
    header('Content-Type: text/html; charset=UTF-8');
    $titleEsc = mailing_h($title);
    $messageEsc = mailing_h($message);

    echo <<<HTML
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$titleEsc}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
        h2 { color: #333; }
        p { line-height: 1.6; color: #666; }
    </style>
</head>
<body>
    <h2>{$titleEsc}</h2>
    <p>{$messageEsc}</p>
</body>
</html>
HTML;
}

function mailing_process_due_queue()
{
    $db = mailing_db();

    $batch = intval(mailing_cfg('send_batch', '50'));
    if ($batch < 1) $batch = 1;
    if ($batch > 500) $batch = 500;

    $tblQueue = mailing_tbl('mailing_queue');
    $tblCamp  = mailing_tbl('mailing_campaigns');

    // Pick pending rows
    $rows = $db->select("SELECT q.*, c.subject, c.body_html, c.body_text
                         FROM " . $tblQueue . " q
                         LEFT JOIN " . $tblCamp . " c ON c.id = q.campaign_id
                         WHERE q.status = 'pending'
                         ORDER BY q.id ASC
                         LIMIT " . $batch);

    if (!$rows) return;

    foreach ($rows as $r) {
        $qid = intval($r['id']);
        $campaignId = intval($r['campaign_id']);

        // Load attachments
        $atts = mailing_get_campaign_attachments($campaignId);

        // Compose email with unsubscribe link
        $unsub = mailing_get_or_create_unsub_token($r['user_id'], $r['email']);
        $unsubUrl = mailing_base_url() . '/?mailing_unsub=1&token=' . $unsub;

        $headersExtra = array(
            'List-Unsubscribe' => '<' . $unsubUrl . '>',
        );

        $html = mailing_prepare_email_html($r['body_html'], $unsubUrl);
        $txt  = mailing_prepare_email_text($r['body_text'], $html, $unsubUrl);

        list($ok, $err) = mailing_send_email($r['email'], $r['name'], $r['subject'], $html, $txt, $atts, $headersExtra);

        if ($ok) {
            $db->query("UPDATE " . $tblQueue . " SET status='sent', last_try_at=" . intval(mailing_now()) . ", try_count=try_count+1, err_msg='' WHERE id=" . $qid);
            // Увеличиваем счётчики кампании, если колонки существуют
            if (mailing_campaign_stats_supported()) {
                $db->query("UPDATE " . $tblCamp . " SET sent_count=sent_count+1, delivered_count=delivered_count+1 WHERE id=" . $campaignId);
            }
        } else {
            $err = mb_substr($err, 0, 250);
            $tries = intval($r['try_count']) + 1;
            $newStatus = ($tries >= intval(mailing_cfg('max_tries', '3'))) ? 'fail' : 'pending';
            $db->query("UPDATE " . $tblQueue . " SET status='" . $newStatus . "', last_try_at=" . intval(mailing_now()) . ", try_count=" . $tries . ", err_msg=" . db_squote($err) . " WHERE id=" . $qid);
            // Увеличиваем счетчик ошибок если достигнут лимит попыток
            if ($newStatus === 'fail' && mailing_campaign_stats_supported()) {
                $db->query("UPDATE " . $tblCamp . " SET failed_count=failed_count+1 WHERE id=" . $campaignId);
            }
        }
    }
}

function mailing_prepare_email_html($html, $unsubUrl)
{
    $allowIframe = mailing_cfg_bool('allow_iframe', false);
    $html = mailing_sanitize_html($html, $allowIframe);

    // Replace placeholders
    $html = str_replace('{UNSUB_URL}', $unsubUrl, $html);

    // If YouTube placeholder exists: {YOUTUBE:URL}
    $html = preg_replace_callback('#\{YOUTUBE:([^}]+)\}#', function ($m) {
        $url = trim($m[1]);
        return mailing_youtube_block($url);
    }, $html);

    // Append footer if no unsub link exists
    if (stripos($html, 'UNSUB_URL') === false && stripos($html, 'отпис') === false) {
        $html .= '<hr><p style="font-size:12px;color:#666;">Чтобы отписаться от рассылки, нажмите: <a href="' . mailing_h($unsubUrl) . '">отписаться</a></p>';
    }
    return $html;
}

function mailing_prepare_email_text($text, $html, $unsubUrl)
{
    $text = trim((string)$text);
    if (!$text) $text = strip_tags($html);
    $text .= "\n\nОтписаться: " . $unsubUrl . "\n";
    return $text;
}

function mailing_youtube_block($url)
{
    // Emails usually don't render iframe, so we generate a clickable thumbnail.
    $id = '';
    if (preg_match('#youtu\.be/([a-zA-Z0-9_-]+)#', $url, $m)) $id = $m[1];
    if (preg_match('#v=([a-zA-Z0-9_-]+)#', $url, $m)) $id = $m[1];
    if (!$id) return '<p><a href="' . mailing_h($url) . '">' . mailing_h($url) . '</a></p>';
    $thumb = 'https://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
    $html  = '<p><a href="' . mailing_h($url) . '" target="_blank" rel="noopener">';
    $html .= '<img src="' . mailing_h($thumb) . '" alt="YouTube video" style="max-width:100%;height:auto;border:0;">';
    $html .= '</a></p>';
    return $html;
}

function mailing_get_or_create_unsub_token($userId, $email)
{
    $db = mailing_db();
    $tblUnsub = mailing_tbl('mailing_unsub');

    $userId = intval($userId);
    $email = trim($email);

    // Existing (even if already unsubbed) - keep stable token
    $rec = $db->record("SELECT * FROM " . $tblUnsub . " WHERE " . ($userId ? "user_id=" . $userId : "email='" . db_squote($email) . "'") . " LIMIT 1");
    if ($rec && $rec['token']) return $rec['token'];

    $token = mailing_token(32);
    $db->query("INSERT INTO " . $tblUnsub . " (user_id, email, token, created_at, unsub_at) VALUES (" . $userId . ", '" . db_squote($email) . "', '" . db_squote($token) . "', " . intval(mailing_now()) . ", 0)");
    return $token;
}

function mailing_get_campaign_attachments($campaignId)
{
    $db = mailing_db();
    $campaignId = intval($campaignId);
    if (!$campaignId) return array();

    $tblAtt = mailing_tbl('mailing_attachments');
    $rows = $db->select("SELECT * FROM " . $tblAtt . " WHERE campaign_id=" . $campaignId . " ORDER BY id ASC");
    $atts = array();
    foreach ($rows as $r) {
        $atts[] = array(
            'path' => $r['file_path'],
            'name' => $r['file_name'],
            'type' => $r['mime'],
        );
    }
    return $atts;
}

function mailing_is_unsubscribed($userId, $email)
{
    $db = mailing_db();
    $tblUnsub = mailing_tbl('mailing_unsub');
    $userId = intval($userId);
    $email = trim($email);

    $rec = $db->record("SELECT * FROM " . $tblUnsub . " WHERE (" . ($userId ? "user_id=" . $userId : "email='" . db_squote($email) . "'") . ") AND unsub_at > 0 LIMIT 1");
    return $rec ? true : false;
}

function mailing_create_campaign_and_queue($title, $subject, $htmlBody, $textBody, $segment)
{
    $db = mailing_db();
    $tblCamp = mailing_tbl('mailing_campaigns');

    $title = trim($title);
    $subject = trim($subject);

    $sendAt = intval($segment['send_at'] ?? mailing_now());
    $seg = $segment;
    unset($seg['send_at']);

    $db->query("INSERT INTO " . $tblCamp . " (title, subject, body_html, body_text, status, send_at, created_at, created_by, segment_json, auto_news_id) VALUES (" .
        db_squote($title) . ", " .
        db_squote($subject) . ", " .
        db_squote($htmlBody) . ", " .
        db_squote($textBody) . ", " .
        "'scheduled', " .
        $sendAt . ", " .
        intval(mailing_now()) . ", " .
        intval($segment['created_by'] ?? 0) . ", " .
        db_squote(mailing_json_encode($seg)) . ", " .
        intval($segment['auto_news_id'] ?? 0) .
        ")");

    // Получаем ID последней вставки через PDO lastInsertId()
    $cid = $db->lastid();

    mailing_queue_campaign($cid);

    return $cid;
}

function mailing_queue_campaign($campaignId)
{
    $db = mailing_db();

    $campaignId = intval($campaignId);
    if (!$campaignId) return 0;

    $tblCamp = mailing_tbl('mailing_campaigns');
    $camp = $db->record("SELECT * FROM " . $tblCamp . " WHERE id=" . $campaignId . " LIMIT 1");
    if (!$camp) return 0;

    $segment = mailing_json_decode($camp['segment_json'], array());

    // Select users by segment
    $users = mailing_select_users_for_segment($segment);
    if (!$users) return 0;

    $tblQueue = mailing_tbl('mailing_queue');
    $count = 0;

    foreach ($users as $u) {
        $uid = intval($u['id']);
        $email = trim($u['mail'] ?? $u['email'] ?? '');
        if (!$email) continue;

        if (mailing_is_unsubscribed($uid, $email)) continue;

        $name = $u['name'] ?? $u['login'] ?? '';

        $db->query("INSERT INTO " . $tblQueue . " (campaign_id, user_id, email, name, status, try_count, last_try_at, err_msg) VALUES (" .
            $campaignId . ", " . $uid . ", " . db_squote($email) . ", " . db_squote($name) . ", 'pending', 0, 0, ''" .
            ")");
        $count++;
    }

    return $count;
}

function mailing_select_users_for_segment($segment)
{
    // Segment example:
    // {
    //   "groups": [1,2],
    //   "only_active": 1,
    //   "registered_after": 0 (timestamp),
    //   "registered_before": 0 (timestamp)
    // }
    $db = mailing_db();

    $tblUsers = mailing_tbl('users');

    $where = array("1=1");

    // group/status field varies by build. Commonly: status
    if (!empty($segment['groups']) && is_array($segment['groups'])) {
        $ids = array();
        foreach ($segment['groups'] as $g) {
            $g = intval($g);
            if ($g >= 0) $ids[] = $g;
        }
        if ($ids) {
            $where[] = "status IN (" . implode(',', $ids) . ")";
        }
    }

    if (!empty($segment['only_active'])) {
        // В большинстве сборок признак активации хранится в поле activation (пустое = активирован)
        // Используем безопасную проверку без ссылки на несуществующие поля
        $where[] = "COALESCE(activation, '') = ''";
    }

    if (!empty($segment['registered_after'])) {
        $where[] = "reg >= " . intval($segment['registered_after']);
    }
    if (!empty($segment['registered_before'])) {
        $where[] = "reg <= " . intval($segment['registered_before']);
    }

    $limit = intval($segment['limit'] ?? 0);
    // Поля соответствуют схеме: id, name, mail, status, reg
    $sql = "SELECT id, name, mail, status, reg FROM " . $tblUsers . " WHERE " . implode(' AND ', $where);
    if ($limit > 0) $sql .= " LIMIT " . $limit;

    return $db->select($sql);
}

function mailing_autonews_scan_and_queue()
{
    // Scans for new published news and creates a campaign automatically.
    // Works even if there is no stable "on publish" hook.
    $db = mailing_db();

    $tblNews = mailing_tbl('news');

    $lastId = intval(mailing_cfg('auto_news_last_id', '0'));
    $catId  = intval(mailing_cfg('auto_news_category', '0'));
    $limit  = intval(mailing_cfg('auto_news_scan_limit', '5'));
    if ($limit < 1) $limit = 1;
    if ($limit > 20) $limit = 20;

    $where = array("id > " . $lastId);
    // best effort: posted=1 or approve=1 fields, may differ
    $where[] = "(approve = 1 OR approve IS NULL)";

    if ($catId > 0) {
        $where[] = "(catid = " . $catId . " OR category = " . $catId . ")";
    }

    $rows = $db->select("SELECT id, title, alt_name, short, content FROM " . $tblNews . " WHERE " . implode(' AND ', $where) . " ORDER BY id ASC LIMIT " . $limit);
    if (!$rows) return;

    foreach ($rows as $n) {
        $nid = intval($n['id']);

        // Build campaign body (simple)
        $title = (string)$n['title'];
        $link  = mailing_base_url() . '/?newsid=' . $nid;

        $html  = '<h2>' . mailing_h($title) . '</h2>';
        $html .= '<p><a href="' . mailing_h($link) . '" target="_blank" rel="noopener">Читать на сайте</a></p>';
        $html .= '<hr>';
        $html .= '<div>' . mailing_sanitize_html($n['short'] ?: $n['content']) . '</div>';
        $html .= '<p>Видео: {YOUTUBE:}</p>'; // placeholder example

        $segment = array(
            'send_at'     => mailing_now(),
            'groups'      => mailing_json_decode(mailing_cfg('auto_news_groups', '[]'), array()),
            'only_active' => 1,
            'created_by'  => 0,
            'auto_news_id' => $nid,
        );

        $cid = mailing_create_campaign_and_queue('Авторассылка: ' . $title, $title, $html, '', $segment);

        // Mark as processed
        $lastId = $nid;
        mailing_set_cfg('auto_news_last_id', $lastId);
    }
}
