<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}

pluginsLoadConfig();
loadPluginLang('news_templates', 'config', '', '', ':');
global $mysql, $lang, $twig;

$labels = [
    'alert_info' => $lang['news_templates:alert.info'],
    'legend' => $lang['news_templates:tpl.legend'],
    'field_title' => $lang['news_templates:field.title'],
    'field_active' => $lang['news_templates:field.active'],
    'field_content' => $lang['news_templates:field.content'],
    'btn_paragraph' => $lang['news_templates:btn.paragraph'],
    'btn_bold' => $lang['news_templates:btn.bold'],
    'btn_italic' => $lang['news_templates:btn.italic'],
    'btn_underline' => $lang['news_templates:btn.underline'],
    'btn_strike' => $lang['news_templates:btn.strikethrough'],
    'align_left' => $lang['news_templates:btn.align_left'],
    'align_center' => $lang['news_templates:btn.align_center'],
    'align_right' => $lang['news_templates:btn.align_right'],
    'align_justify' => $lang['news_templates:btn.align_justify'],
    'list_ul' => $lang['news_templates:btn.list_ul'],
    'list_ol' => $lang['news_templates:btn.list_ol'],
    'btn_code' => $lang['news_templates:btn.code'],
    'btn_quote' => $lang['news_templates:btn.quote'],
    'btn_spoiler' => $lang['news_templates:btn.spoiler'],
    'btn_acronym' => $lang['news_templates:btn.acronym'],
    'btn_hide' => $lang['news_templates:btn.hide'],
    'btn_url' => $lang['news_templates:btn.url'],
    'btn_email' => $lang['news_templates:btn.email'],
    'btn_image' => $lang['news_templates:btn.image'],
    'cancel' => $lang['news_templates:btn.cancel'],
    'insert' => $lang['news_templates:btn.insert'],
    'modal_close' => $lang['news_templates:modal.close'],
    'modal_url_title' => $lang['news_templates:modal.url.title'],
    'modal_url_url_label' => $lang['news_templates:modal.url.url_label'],
    'modal_url_text_label' => $lang['news_templates:modal.url.text_label'],
    'modal_url_text_placeholder' => $lang['news_templates:modal.url.text_placeholder'],
    'modal_url_target_label' => $lang['news_templates:modal.url.target_label'],
    'modal_url_target_default' => $lang['news_templates:modal.url.target_default'],
    'modal_url_target_blank' => $lang['news_templates:modal.url.target_blank'],
    'modal_image_title' => $lang['news_templates:modal.image.title'],
    'modal_image_url_label' => $lang['news_templates:modal.image.url_label'],
    'modal_image_alt_label' => $lang['news_templates:modal.image.alt_label'],
    'modal_image_width_label' => $lang['news_templates:modal.image.width_label'],
    'modal_image_height_label' => $lang['news_templates:modal.image.height_label'],
    'modal_image_upload_label' => $lang['news_templates:modal.image.upload_label'],
    'modal_image_upload_btn' => $lang['news_templates:modal.image.upload_btn'],
    'modal_image_align_label' => $lang['news_templates:modal.image.align_label'],
    'modal_image_align_none' => $lang['news_templates:modal.image.align_none'],
    'modal_image_align_left' => $lang['news_templates:modal.image.align_left'],
    'modal_image_align_right' => $lang['news_templates:modal.image.align_right'],
    'modal_image_align_center' => $lang['news_templates:modal.image.align_center'],
    'modal_email_title' => $lang['news_templates:modal.email.title'],
    'modal_email_address_label' => $lang['news_templates:modal.email.address_label'],
    'modal_email_text_label' => $lang['news_templates:modal.email.text_label'],
    'modal_email_text_placeholder' => $lang['news_templates:modal.email.text_placeholder'],
    'modal_media_title' => $lang['news_templates:modal.media.title'],
    'modal_media_url_label' => $lang['news_templates:modal.media.url_label'],
    'modal_media_width_label' => $lang['news_templates:modal.media.width_label'],
    'modal_media_height_label' => $lang['news_templates:modal.media.height_label'],
    'modal_media_preview_label' => $lang['news_templates:modal.media.preview_label'],
    'modal_media_preview_placeholder' => $lang['news_templates:modal.media.preview_placeholder'],
];

$cfg = [];
$cfgX = [];
$cfg[] = ['descr' => $lang['news_templates:plugin.descr']];

$currentCount = intval(pluginGetVariable('news_templates', 'count'));
if ($currentCount < 0) {
    $currentCount = 0;
}
if ($currentCount > 50) {
    $currentCount = 50;
}
$cfgX[] = [
    'name' => 'tpl_count',
    'title' => $lang['news_templates:tpl_count.title'],
    'descr' => $lang['news_templates:tpl_count.descr'],
    'type' => 'input',
    'value' => $currentCount ?: 3,
    'html_flags' => 'pattern="\\d+"',
];
$cfg[] = [
    'mode' => 'group',
    'title' => $lang['news_templates:group.main'],
    'entries' => $cfgX,
];

$rows = $mysql->select('SELECT * FROM ' . prefix . '_news_templates ORDER BY ord ASC, id ASC');
$byOrd = [];
foreach ($rows as $row) {
    $byOrd[intval($row['ord'])] = $row;
}

$countForRender = $currentCount ?: 3;
$entries = [];
for ($i = 1; $i <= $countForRender; $i++) {
    $row = isset($byOrd[$i]) ? $byOrd[$i] : ['title' => '', 'content' => '', 'active' => 1];
    $entries[] = [
        'ord' => $i,
        'title' => $row['title'],
        'content' => $row['content'],
        'active' => !empty($row['active']),
    ];
}

$templatePaths = locatePluginTemplates(['config'], 'news_templates', 1);
$template = $twig->loadTemplate($templatePaths['config'] . 'config.tpl');
$html = $template->render([
    'entries' => $entries,
    'lang' => $labels,
]);
$cfg[] = [
    'type' => 'flat',
    'input' => $html,
];

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('news_templates', [
        ['name' => 'tpl_count', 'nosave' => false],
    ]);

    $count = intval(isset($_POST['tpl_count']) ? $_POST['tpl_count'] : $currentCount);
    if ($count < 0) {
        $count = 0;
    }
    if ($count > 50) {
        $count = 50;
    }
    pluginSetVariable('news_templates', 'count', (string)$count);
    pluginsSaveConfig();

    $mysql->query('DELETE FROM ' . prefix . '_news_templates');
    for ($i = 1; $i <= $count; $i++) {
        $title = isset($_POST['nt_title_' . $i]) ? trim($_POST['nt_title_' . $i]) : '';
        $content = isset($_POST['nt_content_' . $i]) ? trim($_POST['nt_content_' . $i]) : '';
        $active = isset($_POST['nt_active_' . $i]) ? 1 : 0;
        if ($title === '' && $content === '') {
            continue;
        }
        $mysql->query('INSERT INTO ' . prefix . '_news_templates (ord, title, content, active, dt) VALUES ('
            . db_squote($i) . ', ' . db_squote($title) . ', ' . db_squote($content) . ', ' . db_squote($active) . ', ' . db_squote(time()) . ')');
    }
    print_commit_complete('news_templates');
} else {
    generate_config_page('news_templates', $cfg);
}
