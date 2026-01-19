<?php
if (!defined('NGCMS')) {
    die('HAL');
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/inc/export.php';
// Локализация (секция config)
if (function_exists('loadPluginLang')) {
    loadPluginLang('avtek_callback', 'config', '', '', ':');
}
// Minimal admin guard
$canAdmin = true;
if (function_exists('isAdmin')) {
    $canAdmin = (bool)isAdmin();
}
if (!$canAdmin) {
    $msg = isset($lang['avtek_callback:no_access']) ? $lang['avtek_callback:no_access'] : 'Нет доступа';
    echo '<div class="alert alert-danger">' . avtek_cb_h($msg) . '</div>';
    return;
}
// =====================================================================
// Admin helper: generate ready-to-copy snippets for templates (TWIG / HTML)
// EMBED ONLY (без modal)
// =====================================================================
function avtek_cb_admin_snippet_box(int $formId): string
{
    // TWIG: встроенная форма
    $twigEmbed =
        "{# Встроенная форма (embed): #}\n"
        . "{{ callPlugin('avtek_callback', 'show', {'id': " . $formId . ", 'mode': 'embed'})|raw }}";
    // TWIG: модалка с кнопкой
    $twigModal =
        "{# Модальное окно (кнопка + форма): #}\n"
        . "{{ callPlugin('avtek_callback', 'show', {'id': " . $formId . ", 'mode': 'modal'})|raw }}";
    // Серверная вставка через фильтр новостей (без TWIG)
    $serverInject =
        "{# Серверная вставка: в news.full.tpl #}\n"
        . "{{ plugin_avtek_cb|raw }}\n"
        . "{# ID/режим задаются в настройках плагина (auto_form_id, auto_mode). #}";
    $out  = "<div style=\"display:flex;gap:8px;flex-direction:column\">";
    $out .= "<div><b>TWIG: Встроенная форма</b></div>";
    $out .= "<textarea readonly style=\"width:100%;min-height:90px;resize:vertical\" onclick=\"this.select()\">"
        . htmlspecialchars($twigEmbed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . "</textarea>";
    $out .= "<div><b>TWIG: Модальное окно</b></div>";
    $out .= "<textarea readonly style=\"width:100%;min-height:90px;resize:vertical\" onclick=\"this.select()\">"
        . htmlspecialchars($twigModal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . "</textarea>";
    $out .= "<div><b>Серверная вставка (без TWIG)</b></div>";
    $out .= "<textarea readonly style=\"width:100%;min-height:90px;resize:vertical\" onclick=\"this.select()\">"
        . htmlspecialchars($serverInject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . "</textarea>";
    $out .= "<div style=\"color:#666\">Проверка рендера: <code>/plugin/avtek_callback/?sub=render&amp;form_id=" . (int)$formId . "&amp;mode=embed</code></div>";
    $out .= "</div>";
    return $out;
}
function avtek_cb_admin_begin(string $activeMode): void
{
    // Подключаем CSS только для страниц плагина (без правок системных файлов)
    echo '<link rel="stylesheet" href="' . home . '/engine/plugins/avtek_callback/tpl/admin.css?v=1">';
    // Подключаем тосты (единая система уведомлений)
    echo '<link rel="stylesheet" href="' . home . '/lib/notify.css?v=1">';
    echo '<script>(function(){ if(!window.showToast){ try{ var s=document.createElement("script"); s.src="' . home . '/lib/notify.js"; document.head.appendChild(s); }catch(e){} } })();</script>';
    // Хлебные крошки + заголовок по общему виду админки
    global $PHP_SELF, $lang;
    $alang = LoadLang('extra-config', 'admin');
    $pluginTitle = isset($lang['cfg_title']) ? (string)$lang['cfg_title'] : 'AVTEK Callback';
    echo '<div class="container-fluid">'
        . '<div class="row mb-2">'
        . '  <div class="col-sm-6 d-none d-md-block ">'
        . '    <h1 class="m-0 text-dark">' . avtek_cb_h($pluginTitle) . '</h1>'
        . '  </div>'
        . '  <div class="col-sm-6">'
        . '    <ol class="breadcrumb float-sm-right">'
        . '      <li class="breadcrumb-item"><a href="' . $PHP_SELF . '"><i class="fa fa-home"></i></a></li>'
        . '      <li class="breadcrumb-item"><a href="' . $PHP_SELF . '?mod=extras">' . avtek_cb_h(isset($alang['extras']) ? $alang['extras'] : 'Управление плагинами') . '</a></li>'
        . '      <li class="breadcrumb-item active" aria-current="page">' . avtek_cb_h($pluginTitle) . '</li>'
        . '    </ol>'
        . '  </div>'
        . '</div>'
        . '</div>';
    echo '<div class="avtek_cb_admin_wrap">';
    // Верхнее меню
    $items = [
        'forms'    => ['title' => 'Формы',     'url' => '?mod=extra-config&plugin=avtek_callback&mode=forms'],
        'leads'    => ['title' => 'Заявки',    'url' => '?mod=extra-config&plugin=avtek_callback&mode=leads'],
        'settings' => ['title' => 'Настройки', 'url' => '?mod=extra-config&plugin=avtek_callback&mode=settings'],
    ];
    echo '<div class="avtek_cb_tabs">';
    foreach ($items as $mode => $i) {
        $isActive = ($activeMode === $mode) || ($activeMode === '' && $mode === 'forms');
        $cls = 'btn btn-ghost' . ($isActive ? ' btn-primary' : '');
        echo '<a class="' . $cls . '" href="' . $i['url'] . '">' . $i['title'] . '</a>';
    }
    echo '</div>';
}
function avtek_cb_admin_end(): void
{
    echo '</div>';
}
$mode = (string)avtek_cb_req('mode', 'forms');
// handle global settings save
if ($mode === 'settings' && !empty($_POST['save_settings'])) {
    avtek_cb_set_setting('default_emails', avtek_cb_trim((string)($_POST['default_emails'] ?? ''), 2000));
    avtek_cb_set_setting('mail_from', avtek_cb_trim((string)($_POST['mail_from'] ?? ''), 200));
    avtek_cb_set_setting('telegram_token', avtek_cb_trim((string)($_POST['telegram_token'] ?? ''), 300));
    avtek_cb_set_setting('telegram_chat_ids', avtek_cb_trim((string)($_POST['telegram_chat_ids'] ?? ''), 2000));
    avtek_cb_set_setting('crm_type', avtek_cb_trim((string)($_POST['crm_type'] ?? ''), 50));
    avtek_cb_set_setting('crm_settings', avtek_cb_trim((string)($_POST['crm_settings'] ?? ''), 20000));
    // Автовставка
    avtek_cb_set_setting('auto_form_id', avtek_cb_trim((string)($_POST['auto_form_id'] ?? '1'), 10));
    $am = (string)($_POST['auto_mode'] ?? 'embed');
    avtek_cb_set_setting('auto_mode', ($am === 'modal') ? 'modal' : 'embed');
    $saved = isset($lang['avtek_callback:saved']) ? $lang['avtek_callback:saved'] : 'Сохранено';
    echo '<script>try{ if(window.showToast){ window.showToast("' . addslashes((string)$saved) . '", {type:"success", title:"AVTEK Callback"}); } }catch(e){} </script>';
}
// handle form save
if ($mode === 'form_edit' && !empty($_POST['save_form'])) {
    $id = (int)($_POST['id'] ?? 0);
    $title = (string)($_POST['title'] ?? '');
    $slug = (string)($_POST['slug'] ?? '');
    $emails = (string)($_POST['emails'] ?? '');
    // Labels (allow rename per-form)
    $label_name = (string)($_POST['label_name'] ?? 'Имя');
    $label_email = (string)($_POST['label_email'] ?? 'Электронный адрес');
    $label_phone = (string)($_POST['label_phone'] ?? 'Номер телефона +3');
    $label_city = (string)($_POST['label_city'] ?? 'Город');
    $label_variants = (string)($_POST['label_variants'] ?? 'Варианты ответа');
    $label_calendar = (string)($_POST['label_calendar'] ?? 'Календарь');
    $label_comment = (string)($_POST['label_comment'] ?? 'Комментарий');
    // Build fields
    $fields = [];
    $addField = function (string $name, string $label, string $type, bool $req, string $ph = '', array $opts = []) use (&$fields) {
        $fields[] = [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => $req ? 1 : 0,
            'placeholder' => $ph,
            'options' => $opts,
        ];
    };
    if (!empty($_POST['f_name'])) {
        $addField('name', $label_name, 'text', !empty($_POST['r_name']), (string)($_POST['ph_name'] ?? ''));
    }
    if (!empty($_POST['f_email'])) {
        $addField('email', $label_email, 'email', !empty($_POST['r_email']), (string)($_POST['ph_email'] ?? ''));
    }
    if (!empty($_POST['f_phone'])) {
        $addField('phone', $label_phone, 'phone', !empty($_POST['r_phone']), (string)($_POST['ph_phone'] ?? '+3'));
    }
    if (!empty($_POST['f_city'])) {
        $addField('city', $label_city, 'city', !empty($_POST['r_city']), (string)($_POST['ph_city'] ?? ''));
    }
    if (!empty($_POST['f_variants'])) {
        $opts = preg_split('~\r?\n~', (string)($_POST['variants_list'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $opts = array_map('trim', $opts ?: []);
        $addField('variants', $label_variants, 'select', !empty($_POST['r_variants']), '', $opts);
    }
    if (!empty($_POST['f_calendar'])) {
        $addField('calendar', $label_calendar, 'date', !empty($_POST['r_calendar']), '');
    }
    if (!empty($_POST['f_comment'])) {
        $addField('comment', $label_comment, 'textarea', !empty($_POST['r_comment']), (string)($_POST['ph_comment'] ?? ''));
    }
    // custom fields: one per line: label|type|required(0/1)|options(comma)
    $custom = (string)($_POST['custom_fields'] ?? '');
    $lines = preg_split('~\r?\n~', $custom, -1, PREG_SPLIT_NO_EMPTY);
    $i = 1;
    foreach ($lines as $ln) {
        $ln = trim($ln);
        if ($ln === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $ln));
        $label = $parts[0] ?? ('Поле ' . $i);
        $type = $parts[1] ?? 'text';
        $req = !empty($parts[2]) && $parts[2] !== '0';
        $optStr = $parts[3] ?? '';
        $opts = [];
        if ($type === 'select' && $optStr !== '') {
            $opts = array_map('trim', preg_split('~\s*,\s*~', $optStr));
        }
        $name = 'custom_' . $i;
        $addField($name, $label, $type, $req, '', $opts);
        $i++;
    }
    // per-form settings (EMBED ONLY)
    $settings = [
        'button_text' => (string)($_POST['button_text'] ?? 'Отправить'),
        'success_mode' => (string)($_POST['success_mode'] ?? 'inline'),
        'success_text' => (string)($_POST['success_text'] ?? 'Спасибо! Заявка отправлена.'),
        'success_redirect_url' => (string)($_POST['success_redirect_url'] ?? ''),
        'telegram_chat_ids' => (string)($_POST['telegram_chat_ids_form'] ?? ''),
        'mail_subject' => (string)($_POST['mail_subject'] ?? ''),
        'conversion_js' => (string)($_POST['conversion_js'] ?? ''),
        'crm_type' => (string)($_POST['crm_type_form'] ?? ''),
        'crm_settings' => (string)($_POST['crm_settings_form'] ?? ''),
        'skin_embed' => (string)($_POST['skin_embed'] ?? ''),
    ];
    $savedId = avtek_cb_save_form([
        'id' => $id,
        'title' => $title,
        'slug' => $slug,
        'emails' => $emails,
        'fields' => $fields,
        'settings' => $settings,
    ]);
    $fs = isset($lang['avtek_callback:form_saved']) ? $lang['avtek_callback:form_saved'] : 'Форма сохранена';
    echo '<script>try{ if(window.showToast){ window.showToast("' . addslashes((string)$fs) . ' (ID: ' . (int)$savedId . ')", {type:"success", title:"AVTEK Callback"}); } }catch(e){} </script>';
}
// delete form
if ($mode === 'form_delete') {
    $id = (int)avtek_cb_req('id', 0);
    if ($id > 0) {
        avtek_cb_delete_form($id);
        $fd = isset($lang['avtek_callback:form_deleted']) ? $lang['avtek_callback:form_deleted'] : 'Форма удалена';
        echo '<script>try{ if(window.showToast){ window.showToast("' . addslashes((string)$fd) . '", {type:"success", title:"AVTEK Callback"}); } }catch(e){} </script>';
    }
    $mode = 'forms';
}
// export
if ($mode === 'export') {
    $formId = (int)avtek_cb_req('form_id', 0);
    avtek_cb_export_xls($formId);
}
// UI
avtek_cb_admin_begin($mode);
if ($mode === 'settings') {
    $defaultEmails = avtek_cb_h(avtek_cb_get_setting('default_emails', ''));
    $mailFrom = avtek_cb_h(avtek_cb_get_setting('mail_from', ''));
    $tgToken = avtek_cb_h(avtek_cb_get_setting('telegram_token', ''));
    $tgChats = avtek_cb_h(avtek_cb_get_setting('telegram_chat_ids', ''));
    $crmType = avtek_cb_h(avtek_cb_get_setting('crm_type', ''));
    $crmSettings = avtek_cb_h(avtek_cb_get_setting('crm_settings', ''));
    $g = isset($lang['avtek_callback:global_settings']) ? $lang['avtek_callback:global_settings'] : 'Глобальные настройки';
    echo '<h3>' . avtek_cb_h($g) . '</h3>';
    echo '<form method="post">'
        . '<div><label>Emails по умолчанию (через запятую):</label><br><textarea name="default_emails" style="width:100%;max-width:900px;height:60px">' . $defaultEmails . '</textarea></div><br>'
        . '<div><label>From для mail() (опционально):</label><br><input name="mail_from" style="width:100%;max-width:900px" value="' . $mailFrom . '"></div><br>'
        . '<div><label>Telegram Bot Token:</label><br><input name="telegram_token" style="width:100%;max-width:900px" value="' . $tgToken . '"></div><br>'
        . '<div><label>Telegram chat_id (1 или несколько через запятую):</label><br><textarea name="telegram_chat_ids" style="width:100%;max-width:900px;height:60px">' . $tgChats . '</textarea></div><br>'
        . '<div><label>CRM тип (рекомендуется: webhook или bitrix24):</label><br><input name="crm_type" style="width:100%;max-width:900px" value="' . $crmType . '"></div><br>'
        . '<div><label>CRM настройки (JSON):</label><br><textarea name="crm_settings" style="width:100%;max-width:900px;height:140px">' . $crmSettings . '</textarea></div><br>'
        // Автовставка в полную новость
        . '<hr><h4>Автовставка в полную новость</h4>'
        . '<div><label>ID формы для автовставки:</label><br><input name="auto_form_id" style="width:100%;max-width:300px" value="' . avtek_cb_h(avtek_cb_get_setting('auto_form_id', '1')) . '"></div><br>'
        . '<div><label>Режим автовставки:</label><br><select name="auto_mode">'
        . '<option value="embed"' . ((avtek_cb_get_setting('auto_mode', 'embed') !== 'modal') ? ' selected' : '') . '>Встроенная форма (embed)</option>'
        . '<option value="modal"' . ((avtek_cb_get_setting('auto_mode', 'embed') === 'modal') ? ' selected' : '') . '>Модальное окно (кнопка)</option>'
        . '</select></div><br>'
        . '<button class="btn btn-primary" type="submit" name="save_settings" value="1">' . avtek_cb_h(isset($lang['avtek_callback:save']) ? $lang['avtek_callback:save'] : 'Сохранить') . '</button>'
        . '</form>';
    echo '<hr><h4>Пример CRM webhook JSON</h4>';
    echo '<pre style="background:#f6f6f6;padding:10px;border-radius:10px;max-width:900px">{
  "url": "https://your-endpoint.example/lead",
  "header_auth": "Authorization: Bearer TOKEN"
}</pre>';
    echo '<h4>Пример Bitrix24 JSON</h4>';
    echo '<pre style="background:#f6f6f6;padding:10px;border-radius:10px;max-width:900px">{
  "webhook_url": "https://YOUR.bitrix24.ua/rest/1/XXXX/crm.lead.add.json"
}</pre>';
    avtek_cb_admin_end();
    return;
}
if ($mode === 'leads') {
    $tLeads = avtek_cb_leads_table();
    $tForms = avtek_cb_forms_table();
    $rows = avtek_cb_db_getall('SELECT l.*, f.title AS form_title FROM `' . $tLeads . '` l LEFT JOIN `' . $tForms . '` f ON f.id=l.form_id ORDER BY l.id DESC LIMIT 200');
    echo '<h3>' . avtek_cb_h(isset($lang['avtek_callback:leads_last200']) ? $lang['avtek_callback:leads_last200'] : 'Заявки (последние 200)') . '</h3>';
    echo '<div style="margin:10px 0"><a href="?mod=extra-config&plugin=avtek_callback&mode=export">' . avtek_cb_h(isset($lang['avtek_callback:download_xls_all']) ? $lang['avtek_callback:download_xls_all'] : 'Скачать XLS (все)') . '</a></div>';
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:1200px">';
    echo '<tr><th>ID</th><th>Дата</th><th>Форма</th><th>IP</th><th>Страница</th><th>Данные</th><th>E-mail</th><th>Почта (статус)</th><th>Telegram</th></tr>';
    foreach ($rows as $r) {
        $data = avtek_cb_json_decode($r['data'] ?? '');
        $pairs = [];
        $leadEmail = '';
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = implode(', ', $v);
            }
            $pairs[] = avtek_cb_h((string)$k) . ': ' . avtek_cb_h((string)$v);
            // Try to detect email address for a separate column
            if ($leadEmail === '' && is_string($v)) {
                if (function_exists('avtek_cb_is_valid_email') && avtek_cb_is_valid_email($v)) {
                    $leadEmail = $v;
                }
            }
        }
        echo '<tr>'
            . '<td>' . (int)$r['id'] . '</td>'
            . '<td>' . avtek_cb_h((string)($r['created_at'] ?? '')) . '</td>'
            . '<td>' . avtek_cb_h((string)($r['form_title'] ?? '')) . '</td>'
            . '<td>' . avtek_cb_h((string)($r['ip'] ?? '')) . '</td>'
            . '<td style="max-width:260px;word-break:break-all">' . avtek_cb_h((string)($r['page_url'] ?? '')) . '</td>'
            . '<td style="max-width:420px">' . implode('<br>', $pairs) . '</td>'
            . '<td>' . avtek_cb_h($leadEmail) . '</td>'
            . '<td>' . ((int)($r['email_sent'] ?? 0) ? '✔' : '—') . '</td>'
            . '<td>' . ((int)($r['telegram_sent'] ?? 0) ? '✔' : '—') . '</td>'
            . '</tr>';
    }
    echo '</table>';
    return;
}
if ($mode === 'form_edit') {
    $id = (int)avtek_cb_req('id', 0);
    $form = $id ? avtek_cb_get_form($id) : null;
    $settings = $form ? avtek_cb_json_decode($form['settings'] ?? '') : [];
    $title = avtek_cb_h((string)($form['title'] ?? ''));
    $slug = avtek_cb_h((string)($form['slug'] ?? ''));
    $emails = avtek_cb_h((string)($form['emails'] ?? ''));
    $tEdit = isset($lang['avtek_callback:edit_form']) ? $lang['avtek_callback:edit_form'] : 'Редактировать форму';
    $tNew = isset($lang['avtek_callback:new_form']) ? $lang['avtek_callback:new_form'] : 'Новая форма';
    echo '<h3>' . ($id ? avtek_cb_h($tEdit) : avtek_cb_h($tNew)) . '</h3>';
    echo '<form method="post">'
        . '<input type="hidden" name="id" value="' . (int)$id . '">';
    echo '<div><label>Название:</label><br><input name="title" style="width:100%;max-width:900px" value="' . $title . '"></div><br>';
    echo '<div><label>Slug (уникально):</label><br><input name="slug" style="width:100%;max-width:900px" value="' . $slug . '"></div><br>';
    echo '<div><label>Email получатели (если пусто — берём из глобальных настроек):</label><br><textarea name="emails" style="width:100%;max-width:900px;height:60px">' . $emails . '</textarea></div><br>';
    echo '<h4>Поля формы</h4>';
    // Load saved fields schema (for edit)
    $savedFields = $form ? avtek_cb_json_decode($form['fields'] ?? '') : [];
    $fmap = [];
    if (is_array($savedFields)) {
        foreach ($savedFields as $ff) {
            if (is_array($ff) && isset($ff['name'])) {
                $fmap[(string)$ff['name']] = $ff;
            }
        }
    }
    // Helpers for UI values
    $get = function (string $fname, string $key, $default = '') use (&$fmap) {
        if (!isset($fmap[$fname]) || !is_array($fmap[$fname])) {
            return $default;
        }
        return $fmap[$fname][$key] ?? $default;
    };
    $isEnabled = function (string $fname) use (&$fmap): bool {
        return isset($fmap[$fname]);
    };
    // Standard fields values
    $ui = [
        'name' => [
            'title' => 'Имя',
            'label' => (string)$get('name', 'label', 'Имя'),
            'required' => (int)$get('name', 'required', 0),
            'ph' => (string)$get('name', 'placeholder', ''),
        ],
        'email' => [
            'title' => 'Электронный адрес',
            'label' => (string)$get('email', 'label', 'Электронный адрес'),
            'required' => (int)$get('email', 'required', 0),
            'ph' => (string)$get('email', 'placeholder', ''),
        ],
        'phone' => [
            'title' => 'Номер телефона +3',
            'label' => (string)$get('phone', 'label', 'Номер телефона +3'),
            'required' => (int)$get('phone', 'required', 0),
            'ph' => (string)$get('phone', 'placeholder', '+3'),
        ],
        'city' => [
            'title' => 'Город',
            'label' => (string)$get('city', 'label', 'Город'),
            'required' => (int)$get('city', 'required', 0),
            'ph' => (string)$get('city', 'placeholder', ''),
        ],
        'variants' => [
            'title' => 'Варианты ответа (select)',
            'label' => (string)$get('variants', 'label', 'Варианты ответа'),
            'required' => (int)$get('variants', 'required', 0),
            'ph' => '',
        ],
        'calendar' => [
            'title' => 'Календарь (date)',
            'label' => (string)$get('calendar', 'label', 'Календарь'),
            'required' => (int)$get('calendar', 'required', 0),
            'ph' => '',
        ],
        'comment' => [
            'title' => 'Комментарий (textarea)',
            'label' => (string)$get('comment', 'label', 'Комментарий'),
            'required' => (int)$get('comment', 'required', 0),
            'ph' => (string)$get('comment', 'placeholder', ''),
        ],
    ];
    // Variants list
    $variantsList = '';
    if ($isEnabled('variants')) {
        $opts = $get('variants', 'options', []);
        if (is_array($opts)) {
            $opts = array_map('strval', $opts);
            $variantsList = implode("\n", $opts);
        }
    }
    // Custom fields (rebuild textarea)
    $customLines = [];
    foreach ($fmap as $fname => $ff) {
        if (strpos((string)$fname, 'custom_') === 0) {
            $lbl = (string)($ff['label'] ?? '');
            $type = (string)($ff['type'] ?? 'text');
            $req = !empty($ff['required']) ? 1 : 0;
            $opts = $ff['options'] ?? [];
            $optStr = '';
            if ($type === 'select' && is_array($opts) && count($opts)) {
                $optStr = implode(',', array_map('strval', $opts));
            }
            $customLines[] = $lbl . '|' . $type . '|' . $req . '|' . $optStr;
        }
    }
    $customFieldsText = implode("\n", $customLines);
    // UI table
    echo '<table class="table table-striped" style="max-width:980px">'
        . '<tr><th style="width:170px">Поле</th><th style="width:70px">Вкл</th><th style="width:90px">Обяз.</th><th style="width:250px">Название (label)</th><th>Placeholder</th></tr>';
    $renderRow = function (string $key, array $meta) use ($isEnabled) {
        $enabled = $isEnabled($key) ? ' checked' : '';
        $req = !empty($meta['required']) ? ' checked' : '';
        $label = avtek_cb_h((string)$meta['label']);
        $ph = avtek_cb_h((string)$meta['ph']);
        $title = avtek_cb_h((string)$meta['title']);
        echo '<tr>'
            . '<td>' . $title . '</td>'
            . '<td><input type="checkbox" name="f_' . $key . '" value="1"' . $enabled . '></td>'
            . '<td><input type="checkbox" name="r_' . $key . '" value="1"' . $req . '></td>'
            . '<td><input type="text" name="label_' . $key . '" value="' . $label . '" style="width:100%"></td>'
            . '<td><input type="text" name="ph_' . $key . '" value="' . $ph . '" style="width:100%"></td>'
            . '</tr>';
    };
    $renderRow('name', $ui['name']);
    $renderRow('email', $ui['email']);
    $renderRow('phone', $ui['phone']);
    $renderRow('city', $ui['city']);
    $renderRow('variants', $ui['variants']);
    $renderRow('calendar', $ui['calendar']);
    $renderRow('comment', $ui['comment']);
    echo '</table><br>';
    echo '<div><label>Варианты ответа (если включено) — каждая строка отдельный вариант:</label>'
        . '<br><textarea name="variants_list" style="width:100%;max-width:900px;height:90px" placeholder="Вариант 1\nВариант 2">' . avtek_cb_h($variantsList) . '</textarea></div><br>';
    echo '<div><label>Дополнительные поля (каждая строка): <code>Название|type|required(0/1)|options</code></label>'
        . '<br><textarea name="custom_fields" style="width:100%;max-width:900px;height:120px" placeholder="Компания|text|1|\nТема|select|0|Сервис,Продажи,Другое">' . avtek_cb_h($customFieldsText) . '</textarea></div><br>';
    echo '<div style="color:#666;max-width:900px">Подсказка: если вы переименовали label — именно это название уйдёт в Email/Telegram и в таблицу заявок. Для поля «Календарь» формат значения будет <code>YYYY-MM-DD</code>.</div><br>';
    echo '<h4>Поведение/уведомления</h4>';
    echo '<div><label>Текст кнопки:</label><br><input name="button_text" style="width:100%;max-width:900px" value="' . avtek_cb_h((string)($settings['button_text'] ?? 'Отправить')) . '"></div><br>';
    echo '<div><label>Спасибо (inline):</label><br><input name="success_text" style="width:100%;max-width:900px" value="' . avtek_cb_h((string)($settings['success_text'] ?? 'Спасибо! Заявка отправлена.')) . '"></div><br>';
    echo '<h4>Скины (шаблоны)</h4>';
    echo '<div><label>Skin для встроенной формы (файл из /tpl, например: skin_embed.php):</label><br><input name="skin_embed" style="width:100%;max-width:900px" value="' . avtek_cb_h((string)($settings['skin_embed'] ?? '')) . '"></div><br>';
    echo '<div><label>Режим после отправки:</label><br>'
        . '<select name="success_mode">'
        . '<option value="inline"' . (((string)($settings['success_mode'] ?? 'inline') === 'inline') ? ' selected' : '') . '>Показать Спасибо в форме</option>'
        . '<option value="redirect"' . (((string)($settings['success_mode'] ?? '') === 'redirect') ? ' selected' : '') . '>Редирект</option>'
        . '</select></div><br>';
    echo '<div><label>URL для редиректа (если выбран redirect):</label><br><input name="success_redirect_url" style="width:100%;max-width:900px" value="' . avtek_cb_h((string)($settings['success_redirect_url'] ?? '')) . '"></div><br>';
    echo '<div><label>Telegram chat_id (если нужно переопределить для этой формы):</label><br><textarea name="telegram_chat_ids_form" style="width:100%;max-width:900px;height:60px">' . avtek_cb_h((string)($settings['telegram_chat_ids'] ?? '')) . '</textarea></div><br>';
    echo '<div><label>Тема письма (опционально):</label><br><input name="mail_subject" style="width:100%;max-width:900px" value="' . avtek_cb_h((string)($settings['mail_subject'] ?? '')) . '"></div><br>';
    echo '<h4>Google Ads конверсия (JS, выполнится в браузере после успешной отправки)</h4>';
    echo '<textarea name="conversion_js" style="width:100%;max-width:900px;height:120px" placeholder="gtag(\'event\',\'conversion\',{send_to:\'AW-XXXXX/YYY\'});">' . avtek_cb_h((string)($settings['conversion_js'] ?? '')) . '</textarea><br><br>';
    echo '<h4>CRM (на уровне формы)</h4>';
    echo '<div><label>CRM тип (webhook/bitrix24):</label><br><input name="crm_type_form" style="width:100%;max-width:900px" value="' . avtek_cb_h((string)($settings['crm_type'] ?? '')) . '"></div><br>';
    echo '<div><label>CRM настройки JSON (если пусто — берём глобальные):</label><br><textarea name="crm_settings_form" style="width:100%;max-width:900px;height:140px">' . avtek_cb_h((string)($settings['crm_settings'] ?? '')) . '</textarea></div><br>';
    echo '<button class="btn btn-primary" type="submit" name="save_form" value="1">Сохранить форму</button>';
    echo '</form>';
    if ($id) {
        echo '<hr><h4>Как вставить форму</h4>';
        echo avtek_cb_admin_snippet_box((int)$id);
        $delLbl = isset($lang['avtek_callback:delete_form']) ? $lang['avtek_callback:delete_form'] : 'Удалить форму';
        $delAsk = isset($lang['avtek_callback:delete_confirm']) ? $lang['avtek_callback:delete_confirm'] : 'Удалить форму?';
        echo '<div style="margin-top:10px"><a style="color:#c00" href="?mod=extra-config&plugin=avtek_callback&mode=form_delete&id=' . (int)$id . '" onclick="return confirm(\'' . addslashes($delAsk) . '\')">' . avtek_cb_h($delLbl) . '</a></div>';
    }
    avtek_cb_admin_end();
    return;
}
// forms list by default
$forms = avtek_cb_list_forms();
echo '<div style="display:flex;justify-content:space-between;align-items:center;max-width:1200px"'
    . '><h3>' . avtek_cb_h(isset($lang['avtek_callback:forms']) ? $lang['avtek_callback:forms'] : 'Формы') . '</h3>'
    . '<a class="btn btn-primary" href="?mod=extra-config&plugin=avtek_callback&mode=form_edit">' . avtek_cb_h(isset($lang['avtek_callback:new_form']) ? $lang['avtek_callback:new_form'] : '+ Новая форма') . '</a>'
    . '</div>';
echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:1200px">';
echo '<tr><th>ID</th><th>Название</th><th>Slug</th><th>Email</th><th>Действия</th></tr>';
foreach ($forms as $f) {
    $id = (int)$f['id'];
    echo '<tr>'
        . '<td>' . $id . '</td>'
        . '<td>' . avtek_cb_h((string)$f['title']) . '</td>'
        . '<td>' . avtek_cb_h((string)$f['slug']) . '</td>'
        . '<td style="max-width:260px">' . avtek_cb_h((string)$f['emails']) . '</td>'
        . '<td><a class="btn btn-sm btn-ghost" href="?mod=extra-config&plugin=avtek_callback&mode=form_edit&id=' . $id . '">' . avtek_cb_h(isset($lang['avtek_callback:edit']) ? $lang['avtek_callback:edit'] : 'Редактировать') . '</a> '
        . '<a class="btn btn-sm btn-ghost" href="?mod=extra-config&plugin=avtek_callback&mode=export&form_id=' . $id . '">' . avtek_cb_h(isset($lang['avtek_callback:xls']) ? $lang['avtek_callback:xls'] : 'XLS') . '</a></td>'
        . '</tr>';
}
echo '</table>';
avtek_cb_admin_end();
