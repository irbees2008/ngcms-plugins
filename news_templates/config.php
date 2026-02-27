<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}
// Приводим конфиг к стандартному виду extra-config (как в archive)
pluginsLoadConfig();
loadPluginLang('news_templates', 'config', '', '', ':');
global $mysql, $lang, $plugin;
// Короткие алиасы для lang-строк, используемых в цикле
$_l = [
    'legend'           => $lang['news_templates:tpl.legend'],
    'field_title'      => $lang['news_templates:field.title'],
    'field_active'     => $lang['news_templates:field.active'],
    'field_content'    => $lang['news_templates:field.content'],
    'btn_paragraph'    => $lang['news_templates:btn.paragraph'],
    'btn_bold'         => $lang['news_templates:btn.bold'],
    'btn_italic'       => $lang['news_templates:btn.italic'],
    'btn_underline'    => $lang['news_templates:btn.underline'],
    'btn_strike'       => $lang['news_templates:btn.strikethrough'],
    'align_left'       => $lang['news_templates:btn.align_left'],
    'align_center'     => $lang['news_templates:btn.align_center'],
    'align_right'      => $lang['news_templates:btn.align_right'],
    'align_justify'    => $lang['news_templates:btn.align_justify'],
    'list_ul'          => $lang['news_templates:btn.list_ul'],
    'list_ol'          => $lang['news_templates:btn.list_ol'],
    'btn_code'         => $lang['news_templates:btn.code'],
    'btn_quote'        => $lang['news_templates:btn.quote'],
    'btn_spoiler'      => $lang['news_templates:btn.spoiler'],
    'btn_acronym'      => $lang['news_templates:btn.acronym'],
    'btn_hide'         => $lang['news_templates:btn.hide'],
    'btn_url'          => $lang['news_templates:btn.url'],
    'btn_email'        => $lang['news_templates:btn.email'],
    'btn_image'        => $lang['news_templates:btn.image'],
    'cancel'           => $lang['news_templates:btn.cancel'],
    'insert'           => $lang['news_templates:btn.insert'],
];
// Заголовок/описание
$cfg = [];
$cfgX = [];
array_push($cfg, ['descr' => $lang['news_templates:plugin.descr']]);
// 1) Количество шаблонов (сохраняется в параметрах плагина)
$currentCount = intval(pluginGetVariable('news_templates', 'count'));
if ($currentCount < 0) $currentCount = 0;
if ($currentCount > 50) $currentCount = 50;
array_push($cfgX, [
    'name'  => 'tpl_count',
    'title' => $lang['news_templates:tpl_count.title'],
    'descr' => $lang['news_templates:tpl_count.descr'],
    'type'  => 'input',
    'value' => $currentCount ?: 3,
    'html_flags' => 'pattern="\\d+"'
]);
array_push($cfg, ['mode' => 'group', 'title' => '<b>' . $lang['news_templates:group.main'] . '</b>', 'entries' => $cfgX]);
// 2) Динамический блок с полями для самих шаблонов
// Загружаем текущие шаблоны
$rows = $mysql->select('SELECT * FROM ' . prefix . '_news_templates ORDER BY ord ASC, id ASC');
$byOrd = [];
foreach ($rows as $r) {
    $byOrd[intval($r['ord'])] = $r;
}
$countForRender = ($currentCount ?: 3);
$html = '<style>.nt-textarea-holder{position:relative}</style>'
    . '<div class="alert alert-info">' . $lang['news_templates:alert.info'] . '</div>';
for ($i = 1; $i <= $countForRender; $i++) {
    $r = isset($byOrd[$i]) ? $byOrd[$i] : ['title' => '', 'content' => '', 'active' => 1];
    $html .= '<fieldset class="border rounded p-2 mb-3">'
        . '<legend class="w-auto px-2">' . $_l['legend'] . $i . '</legend>'
        . '<div class="form-row">'
        . '<div class="form-group col-md-8">'
        . '<label for="nt_title_' . $i . '">' . $_l['field_title'] . '</label>'
        . '<input type="text" class="form-control" name="nt_title_' . $i . '" id="nt_title_' . $i . '" value="' . htmlspecialchars($r['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '" />'
        . '</div>'
        . '<div class="form-group col-md-4">'
        . '<div class="form-check mt-4">'
        . '<input class="form-check-input" type="checkbox" name="nt_active_' . $i . '" id="nt_active_' . $i . '" value="1"' . ($r['active'] ? ' checked' : '') . ' />'
        . '<label class="form-check-label" for="nt_active_' . $i . '">' . $_l['field_active'] . '</label>'
        . '</div>'
        . '</div>'
        . '</div>'
        // Toolbar BBCode
        . '<div class="btn-toolbar mb-2" role="toolbar">'
        // Параграф
        . '<div class="btn-group btn-group-sm mr-2">'
        . '<button type="button" class="btn btn-outline-dark" title="' . $_l['btn_paragraph'] . '" onclick="insertext(\'[p]\', \'[/p]\', \'nt_content_' . $i . '\')"><i class="fa fa-paragraph"></i></button>'
        . '</div>'
        // Шрифт (dropdown)
        . '<div class="btn-group btn-group-sm mr-2">'
        . '<button id="tags-font-' . $i . '" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-font"></i></button>'
        . '<div class="dropdown-menu" aria-labelledby="tags-font-' . $i . '">'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[b]\', \'[/b]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-bold"></i> ' . $_l['btn_bold'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[i]\', \'[/i]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-italic"></i> ' . $_l['btn_italic'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[u]\', \'[/u]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-underline"></i> ' . $_l['btn_underline'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[s]\', \'[/s]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-strikethrough"></i> ' . $_l['btn_strike'] . '</a>'
        . '</div>'
        . '</div>'
        // Выравнивание (dropdown)
        . '<div class="btn-group btn-group-sm mr-2">'
        . '<button id="tags-align-' . $i . '" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-align-left"></i></button>'
        . '<div class="dropdown-menu" aria-labelledby="tags-align-' . $i . '">'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[left]\', \'[/left]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-align-left"></i> ' . $_l['align_left'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[center]\', \'[/center]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-align-center"></i> ' . $_l['align_center'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[right]\', \'[/right]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-align-right"></i> ' . $_l['align_right'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[justify]\', \'[/justify]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-align-justify"></i> ' . $_l['align_justify'] . '</a>'
        . '</div>'
        . '</div>'
        // Блоки/списки/код (dropdown)
        . '<div class="btn-group btn-group-sm mr-2">'
        . '<button id="tags-block-' . $i . '" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-quote-left"></i></button>'
        . '<div class="dropdown-menu" aria-labelledby="tags-block-' . $i . '">'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[ul]\\n[li][/li]\\n[li][/li]\\n[li][/li]\\n[/ul]\', \'\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-list-ul"></i> ' . $_l['list_ul'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[ol]\\n[li][/li]\\n[li][/li]\\n[li][/li]\\n[/ol]\', \'\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-list-ol"></i> ' . $_l['list_ol'] . '</a>'
        . '<div class="dropdown-divider"></div>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[code]\', \'[/code]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-code"></i> ' . $_l['btn_code'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[quote]\', \'[/quote]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-quote-left"></i> ' . $_l['btn_quote'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[spoiler]\', \'[/spoiler]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-list-alt"></i> ' . $_l['btn_spoiler'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[acronym=]\', \'[/acronym]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-tags"></i> ' . $_l['btn_acronym'] . '</a>'
        . '<a href="#" class="dropdown-item" onclick="insertext(\'[hide]\', \'[/hide]\', \'nt_content_' . $i . '\'); return false;"><i class="fa fa-shield"></i> ' . $_l['btn_hide'] . '</a>'
        . '</div>'
        . '</div>'
        // Ссылки (dropdown)
        . '<div class="btn-group btn-group-sm mr-2">'
        . '<button id="tags-link-' . $i . '" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-link"></i></button>'
        . '<div class="dropdown-menu" aria-labelledby="tags-link-' . $i . '">'
        . '<a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-insert-url" onclick="prepareUrlModal(\'nt_content_' . $i . '\'); showModalById(\'modal-insert-url\'); return false;"><i class="fa fa-link"></i> ' . $_l['btn_url'] . '</a>'
        . '<a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-insert-email" onclick="prepareEmailModal(\'nt_content_' . $i . '\'); showModalById(\'modal-insert-email\'); return false;"><i class="fa fa-envelope-o"></i> ' . $_l['btn_email'] . '</a>'
        . '<a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-insert-image" onclick="prepareImgModal(\'nt_content_' . $i . '\'); showModalById(\'modal-insert-image\'); return false;"><i class="fa fa-file-image-o"></i> ' . $_l['btn_image'] . '</a>'
        . '</div>'
        . '</div>'
        // Media
        . '<div class="btn-group btn-group-sm mr-2">'
        . '<button id="tags-media-' . $i . '" type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#modal-insert-media" onclick="prepareMediaModal(\'nt_content_' . $i . '\'); showModalById(\'modal-insert-media\'); return false;" title="[media]"><i class="fa fa-play-circle"></i></button>'
        . '</div>'
        . '</div>'
        . '<div class="form-group nt-textarea-holder position-relative">'
        . '<label for="nt_content_' . $i . '">' . $_l['field_content'] . '</label>'
        . '<textarea rows="4" class="form-control" name="nt_content_' . $i . '" id="nt_content_' . $i . '">' . htmlspecialchars($r['content'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '</textarea>'
        . '</div>'
        . '</fieldset>';
}
// Подключаем скрипт редактора и модальные окна один раз
$mUrl   = $lang['news_templates:modal.url.title'];
$mImg   = $lang['news_templates:modal.image.title'];
$mEmail = $lang['news_templates:modal.email.title'];
$mMedia = $lang['news_templates:modal.media.title'];
$lCancel = $_l['cancel'];
$lInsert = $_l['insert'];
$html .= '<script src="/lib/news_editor.js"></script>'
    . '<div class="modal fade" id="modal-insert-url" tabindex="-1" role="dialog" aria-hidden="true">'
    . '  <div class="modal-dialog" role="document">'
    . '    <div class="modal-content">'
    . '      <div class="modal-header">'
    . '        <h5 class="modal-title">' . $mUrl . '</h5>'
    . '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
    . '      </div>'
    . '      <div class="modal-body">'
    . '        <input type="hidden" id="urlAreaId" value="" />'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.url.url_label'] . '</label><input type="text" class="form-control" id="urlHref" placeholder="https://example.com" /></div>'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.url.text_label'] . '</label><input type="text" class="form-control" id="urlText" placeholder="' . $lang['news_templates:modal.url.text_placeholder'] . '" /></div>'
    . '        <div class="form-row">'
    . '          <div class="form-group col-md-6"><label>' . $lang['news_templates:modal.url.target_label'] . '</label><select id="urlTarget" class="form-control"><option value="">' . $lang['news_templates:modal.url.target_default'] . '</option><option value="_blank">' . $lang['news_templates:modal.url.target_blank'] . '</option></select></div>'
    . '          <div class="form-group col-md-6"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" id="urlNofollow" /> <label class="form-check-label" for="urlNofollow">nofollow</label></div></div>'
    . '        </div>'
    . '      </div>'
    . '      <div class="modal-footer">'
    . '        <button type="button" class="btn btn-secondary" data-dismiss="modal">' . $lCancel . '</button>'
    . '        <button type="button" class="btn btn-primary" onclick="insertUrlFromModal(); return false;">' . $lInsert . '</button>'
    . '      </div>'
    . '    </div>'
    . '  </div>'
    . '</div>'
    . '<div class="modal fade" id="modal-insert-image" tabindex="-1" role="dialog" aria-hidden="true">'
    . '  <div class="modal-dialog" role="document">'
    . '    <div class="modal-content">'
    . '      <div class="modal-header">'
    . '        <h5 class="modal-title">' . $mImg . '</h5>'
    . '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
    . '      </div>'
    . '      <div class="modal-body">'
    . '        <input type="hidden" id="imgAreaId" value="" />'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.image.url_label'] . '</label><input type="text" class="form-control" id="imgHref" placeholder="https://.../image.jpg" /></div>'
    . '        <div class="form-row">'
    . '          <div class="form-group col-md-6"><label>' . $lang['news_templates:modal.image.alt_label'] . '</label><input type="text" class="form-control" id="imgAlt" /></div>'
    . '          <div class="form-group col-md-3"><label>' . $lang['news_templates:modal.image.width_label'] . '</label><input type="text" class="form-control" id="imgWidth" /></div>'
    . '          <div class="form-group col-md-3"><label>' . $lang['news_templates:modal.image.height_label'] . '</label><input type="text" class="form-control" id="imgHeight" /></div>'
    . '        </div>'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.image.align_label'] . '</label><select id="imgAlign" class="form-control"><option value="">' . $lang['news_templates:modal.image.align_none'] . '</option><option value="left">' . $lang['news_templates:modal.image.align_left'] . '</option><option value="right">' . $lang['news_templates:modal.image.align_right'] . '</option><option value="center">' . $lang['news_templates:modal.image.align_center'] . '</option></select></div>'
    . '        <div class="form-group">'
    . '          <label>' . $lang['news_templates:modal.image.upload_label'] . '</label>'
    . '          <div class="input-group">'
    . '            <input type="file" class="form-control" id="uploadimage" />'
    . '            <div class="input-group-append">'
    . '              <button type="button" class="btn btn-outline-primary" onclick="uploadNewsImage(document.getElementById(\'imgAreaId\').value); return false;">' . $lang['news_templates:modal.image.upload_btn'] . '</button>'
    . '            </div>'
    . '          </div>'
    . '        </div>'
    . '      </div>'
    . '      <div class="modal-footer">'
    . '        <button type="button" class="btn btn-secondary" data-dismiss="modal">' . $lCancel . '</button>'
    . '        <button type="button" class="btn btn-primary" onclick="insertImgFromModal(); return false;">' . $lInsert . '</button>'
    . '      </div>'
    . '    </div>'
    . '  </div>'
    . '</div>'
    . '<div class="modal fade" id="modal-insert-email" tabindex="-1" role="dialog" aria-hidden="true">'
    . '  <div class="modal-dialog" role="document">'
    . '    <div class="modal-content">'
    . '      <div class="modal-header">'
    . '        <h5 class="modal-title">' . $mEmail . '</h5>'
    . '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
    . '      </div>'
    . '      <div class="modal-body">'
    . '        <input type="hidden" id="emailAreaId" value="" />'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.email.address_label'] . '</label><input type="text" class="form-control" id="emailAddress" placeholder="user@example.com" /></div>'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.email.text_label'] . '</label><input type="text" class="form-control" id="emailText" placeholder="' . $lang['news_templates:modal.email.text_placeholder'] . '" /></div>'
    . '      </div>'
    . '      <div class="modal-footer">'
    . '        <button type="button" class="btn btn-secondary" data-dismiss="modal">' . $lCancel . '</button>'
    . '        <button type="button" class="btn btn-primary" onclick="insertEmailFromModal(); return false;">' . $lInsert . '</button>'
    . '      </div>'
    . '    </div>'
    . '  </div>'
    . '</div>'
    . '<div class="modal fade" id="modal-insert-media" tabindex="-1" role="dialog" aria-hidden="true">'
    . '  <div class="modal-dialog" role="document">'
    . '    <div class="modal-content">'
    . '      <div class="modal-header">'
    . '        <h5 class="modal-title">' . $mMedia . '</h5>'
    . '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
    . '      </div>'
    . '      <div class="modal-body">'
    . '        <input type="hidden" id="mediaAreaId" value="" />'
    . '        <div class="form-group"><label>' . $lang['news_templates:modal.media.url_label'] . '</label><input type="text" class="form-control" id="mediaHref" placeholder="https://example.com/embed.mp4" /></div>'
    . '        <div class="form-row">'
    . '          <div class="form-group col-md-4"><label>' . $lang['news_templates:modal.media.width_label'] . '</label><input type="number" min="0" class="form-control" id="mediaWidth" /></div>'
    . '          <div class="form-group col-md-4"><label>' . $lang['news_templates:modal.media.height_label'] . '</label><input type="number" min="0" class="form-control" id="mediaHeight" /></div>'
    . '          <div class="form-group col-md-4"><label>' . $lang['news_templates:modal.media.preview_label'] . '</label><input type="text" class="form-control" id="mediaPreview" placeholder="' . $lang['news_templates:modal.media.preview_placeholder'] . '" /></div>'
    . '        </div>'
    . '      </div>'
    . '      <div class="modal-footer">'
    . '        <button type="button" class="btn btn-secondary" data-dismiss="modal">' . $lCancel . '</button>'
    . '        <button type="button" class="btn btn-primary" onclick="insertMediaFromModal(); return false;">' . $lInsert . '</button>'
    . '      </div>'
    . '    </div>'
    . '  </div>'
    . '</div>';
// Выводим блок шаблонов на всю ширину формы, без левой колонки заголовка группы
array_push($cfg, [
    'type'  => 'flat',
    'input' =>  $html,
]);
// Commit / Render
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    // Сохраняем количество через стандартный механизм
    commit_plugin_config_changes('news_templates', [
        ['name' => 'tpl_count', 'nosave' => false],
    ]);
    // Нормализуем и читаем заданное количество
    $count = intval(isset($_POST['tpl_count']) ? $_POST['tpl_count'] : $currentCount);
    if ($count < 0) $count = 0;
    if ($count > 50) $count = 50;
    pluginSetVariable('news_templates', 'count', (string)$count);
    pluginsSaveConfig();
    // Сохраняем шаблоны в БД
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
