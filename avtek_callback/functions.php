<?php
if (!defined('NGCMS')) {
    die('HAL');
}

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/mailer.php';
require_once __DIR__ . '/inc/telegram.php';
require_once __DIR__ . '/inc/crm.php';

function avtek_cb_is_valid_email(string $email): bool
{
    $email = trim($email);
    if ($email === '') {
        return false;
    }
    // IDN domain support: convert domain to ASCII (punycode) before filter_var
    $atPos = mb_strpos($email, '@');
    if ($atPos !== false) {
        $local = mb_substr($email, 0, $atPos);
        $domain = mb_substr($email, $atPos + 1);
        $domainAscii = $domain;
        if (function_exists('idn_to_ascii')) {
            try {
                if (defined('INTL_IDNA_VARIANT_UTS46')) {
                    $tmp = idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
                } else {
                    $tmp = idn_to_ascii($domain);
                }
                if (is_string($tmp) && $tmp !== '') {
                    $domainAscii = $tmp;
                }
            } catch (\Throwable $e) {
                // ignore, fallback below
            }
        }
        $email = $local . '@' . $domainAscii;
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    // Fallback: simple pattern allowing common cases
    return (bool)preg_match('~^[^\s@]+@[^\s@]+\.[^\s@]+$~u', $email);
}

function avtek_cb_settings_table(): string
{
    return avtek_cb_db_table('avtek_callback_settings');
}

function avtek_cb_get_setting(string $name, string $default = ''): string
{
    $t = avtek_cb_settings_table();
    $row = avtek_cb_db_getrow('SELECT `value` FROM `' . $t . '` WHERE `name`=' . (function_exists('db_squote') ? db_squote($name) : ("'" . avtek_cb_db_escape($name) . "'")));
    if (!$row || !isset($row['value'])) {
        return $default;
    }
    return (string)$row['value'];
}

function avtek_cb_set_setting(string $name, string $value): void
{
    $t = avtek_cb_settings_table();
    $nq = function_exists('db_squote') ? db_squote($name) : ("'" . avtek_cb_db_escape($name) . "'");
    $vq = function_exists('db_squote') ? db_squote($value) : ("'" . avtek_cb_db_escape($value) . "'");
    avtek_cb_db_q('INSERT INTO `' . $t . '` (`name`,`value`) VALUES (' . $nq . ',' . $vq . ') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
}

function avtek_cb_forms_table(): string
{
    return avtek_cb_db_table('avtek_callback_forms');
}

function avtek_cb_leads_table(): string
{
    return avtek_cb_db_table('avtek_callback_leads');
}

function avtek_cb_get_form(int $id): ?array
{
    $t = avtek_cb_forms_table();
    return avtek_cb_db_getrow('SELECT * FROM `' . $t . '` WHERE id=' . (int)$id);
}

function avtek_cb_list_forms(): array
{
    $t = avtek_cb_forms_table();
    return avtek_cb_db_getall('SELECT * FROM `' . $t . '` ORDER BY id DESC');
}

function avtek_cb_save_form(array $form): int
{
    $t = avtek_cb_forms_table();
    $id = (int)($form['id'] ?? 0);

    $title = avtek_cb_trim((string)($form['title'] ?? ''), 200);
    $slug  = avtek_cb_slug((string)($form['slug'] ?? $title));

    $emails = avtek_cb_trim((string)($form['emails'] ?? ''), 2000);
    $fields = avtek_cb_json_encode($form['fields'] ?? []);
    $settings = avtek_cb_json_encode($form['settings'] ?? []);

    $sq = function_exists('db_squote') ? 'db_squote' : null;
    $q = function ($s) use ($sq) {
        if ($sq) {
            return $sq($s);
        }
        return "'" . avtek_cb_db_escape($s) . "'";
    };

    if ($id > 0) {
        avtek_cb_db_q(
            'UPDATE `' . $t . '` SET '
                . 'title=' . $q($title) . ','
                . 'slug=' . $q($slug) . ','
                . 'emails=' . $q($emails) . ','
                . 'fields=' . $q($fields) . ','
                . 'settings=' . $q($settings) . ','
                . 'updated_at=' . $q(avtek_cb_now())
                . ' WHERE id=' . $id
        );
        return $id;
    }

    avtek_cb_db_q(
        'INSERT INTO `' . $t . '` (title, slug, emails, fields, settings, created_at, updated_at) VALUES ('
            . $q($title) . ','
            . $q($slug) . ','
            . $q($emails) . ','
            . $q($fields) . ','
            . $q($settings) . ','
            . $q(avtek_cb_now()) . ','
            . $q(avtek_cb_now())
            . ')'
    );
    return avtek_cb_db_lastid();
}

function avtek_cb_delete_form(int $id): void
{
    $t = avtek_cb_forms_table();
    $l = avtek_cb_leads_table();
    avtek_cb_db_q('DELETE FROM `' . $l . '` WHERE form_id=' . (int)$id);
    avtek_cb_db_q('DELETE FROM `' . $t . '` WHERE id=' . (int)$id);
}

function avtek_cb_render_form(int $formId, string $mode = 'embed'): string
{
    // Локализация сайта
    if (function_exists('LoadPluginLang')) {
        LoadPluginLang('avtek_callback', 'site');
    }
    $form = avtek_cb_get_form($formId);
    if (!$form) {
        return '<!-- avtek_callback: form not found -->';
    }

    $fields = avtek_cb_json_decode($form['fields'] ?? '');
    $settings = avtek_cb_json_decode($form['settings'] ?? '');

    $endpoint = (string)($settings['endpoint'] ?? '');
    if ($endpoint === '') {
        // Works with NGCMS plugin router: /plugin/avtek_callback/
        $endpoint = (string)($_SERVER['SCRIPT_NAME'] ?? '') . '?action=avtek_callback&sub=submit';
        // Many NGCMS installs use /plugin/<id>/
        $endpoint = '/plugin/avtek_callback/?sub=submit';
    }

    $defaultSubmit = isset($lang['avtek_callback_submit']) ? (string)$lang['avtek_callback_submit'] : 'Отправить';
    $defaultThanks = isset($lang['avtek_callback_thanks']) ? (string)$lang['avtek_callback_thanks'] : 'Спасибо! Заявка отправлена.';
    $submitText = avtek_cb_h((string)($settings['button_text'] ?? $defaultSubmit));
    $thanksText = avtek_cb_h((string)($settings['success_text'] ?? $defaultThanks));
    $successMode = (string)($settings['success_mode'] ?? 'inline'); // inline|redirect
    $redirectUrl = (string)($settings['success_redirect_url'] ?? '');

    $formUid = 'avtek_cb_' . $formId . '_' . avtek_cb_rand_token(8);
    $hpName = 'cb_hp_' . $formId;

    $html = '';

    $renderFields = function () use ($fields, $formUid) {
        $out = '';
        foreach ($fields as $f) {
            $type = (string)($f['type'] ?? 'text');
            $name = (string)($f['name'] ?? 'field');
            $label = (string)($f['label'] ?? $name);
            $req = !empty($f['required']);
            $ph = (string)($f['placeholder'] ?? '');
            $opts = (array)($f['options'] ?? []);

            $nameAttr = 'f[' . avtek_cb_h($name) . ']';
            $id = $formUid . '_' . preg_replace('~[^a-zA-Z0-9_]+~', '_', $name);

            $out .= '<div class="avtek-cb-field">';
            $out .= '<label for="' . avtek_cb_h($id) . '">' . avtek_cb_h($label);
            if ($req) {
                $out .= ' <span class="avtek-cb-req">*</span>';
            }
            $out .= '</label>';

            if ($type === 'email') {
                $out .= '<input type="email" id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" placeholder="' . avtek_cb_h($ph) . '" ' . ($req ? 'required' : '') . ' />';
            } elseif ($type === 'phone') {
                $out .= '<input type="tel" id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" placeholder="+3" ' . ($req ? 'required' : '') . ' />';
            } elseif ($type === 'city') {
                $out .= '<input type="text" id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" placeholder="' . avtek_cb_h($ph) . '" ' . ($req ? 'required' : '') . ' />';
            } elseif ($type === 'date') {
                $out .= '<input type="date" id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" ' . ($req ? 'required' : '') . ' />';
            } elseif ($type === 'select') {
                $out .= '<select id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" ' . ($req ? 'required' : '') . '>';
                $out .= '<option value="">—</option>';
                foreach ($opts as $o) {
                    $out .= '<option value="' . avtek_cb_h((string)$o) . '">' . avtek_cb_h((string)$o) . '</option>';
                }
                $out .= '</select>';
            } elseif ($type === 'textarea') {
                $out .= '<textarea id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" placeholder="' . avtek_cb_h($ph) . '" ' . ($req ? 'required' : '') . '></textarea>';
            } else {
                $out .= '<input type="text" id="' . avtek_cb_h($id) . '" name="' . $nameAttr . '" placeholder="' . avtek_cb_h($ph) . '" ' . ($req ? 'required' : '') . ' />';
            }

            $out .= '</div>';
        }
        return $out;
    };

    $formHtml = '';
    $formHtml .= '<form class="avtek-cb" id="' . avtek_cb_h($formUid) . '" method="post" action="' . avtek_cb_h($endpoint) . '"'
        . ' data-avtek-cb-form="1"'
        . ' data-avtek-cb-success-mode="' . avtek_cb_h($successMode) . '"'
        . ' data-avtek-cb-redirect-url="' . avtek_cb_h($redirectUrl) . '"'
        . ' data-avtek-cb-success-text="' . $thanksText . '"'
        . '>';
    $formHtml .= '<input type="hidden" name="form_id" value="' . (int)$formId . '" />';
    $formHtml .= '<input type="hidden" name="' . avtek_cb_h($hpName) . '" value="" />'; // honeypot
    $formHtml .= '<div class="avtek-cb-grid">' . $renderFields() . '</div>';
    $formHtml .= '<div class="avtek-cb-actions">';
    $formHtml .= '<button type="submit">' . $submitText . '</button>';
    $formHtml .= '<div class="avtek-cb-msg" style="display:none"></div>';
    $formHtml .= '</div>';
    $formHtml .= '</form>';

    // Ensure frontend toast CSS is available
    if (function_exists('register_stylesheet')) {
        register_stylesheet(home . '/lib/notify.css');
    }
    $js = "<script>(function(){\n"
        . "var f=document.getElementById('" . addslashes($formUid) . "'); if(!f) return;\n"
        . "function ensureToast(){ if(!window.showToast){ try{ var s=document.createElement('script'); s.src='/lib/notify.js'; document.head.appendChild(s); }catch(e){} } }\n"
        . "function toast(msg, type, title){ ensureToast(); try{ if(window.showToast){ window.showToast(msg, {type:type||'info', title:title||''}); } }catch(e){} }\n"
        . "function setMsg(t, ok){var el=f.querySelector('.avtek-cb-msg'); if(!el) return; el.style.display='block'; el.innerHTML=t; el.className='avtek-cb-msg ' + (ok?'ok':'err');}\n"
        . "f.addEventListener('submit', function(ev){ev.preventDefault();\n"
        . "var btn=f.querySelector('button[type=submit]'); if(btn) btn.disabled=true;\n"
        . "var fd=new FormData(f);\n"
        . "fetch(f.action, {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})\n"
        . ".then(function(r){return r.json();}).then(function(j){\n"
        . "if(btn) btn.disabled=false;\n"
        . "if(j && j.ok){\n"
        . "  if(j.success_mode==='redirect' && j.redirect_url){ window.location.href=j.redirect_url; return; }\n"
        . "  toast(j.message || '" . addslashes($thanksText) . "','success','Заявка');\n"
        . "  setMsg(j.message || '" . addslashes($thanksText) . "', true);\n"
        . "  try{ if(j.conversion_js){ (new Function(j.conversion_js))(); } }catch(e){}\n"
        . "  try{ f.reset(); }catch(e){}\n"
        . "} else { var emsg=(j && j.error) ? j.error : 'Ошибка отправки'; toast(emsg,'error','Заявка'); setMsg(emsg, false); }\n"
        . "}).catch(function(){ if(btn) btn.disabled=false; var nerr='" . addslashes(isset($lang['avtek_callback_error_network']) ? (string)$lang['avtek_callback_error_network'] : 'Ошибка сети') . "'; toast(nerr,'error','Заявка'); setMsg(nerr, false);});\n"
        . "});\n"
        . "})();</script>";

    $css = "<style>\n"
        . ".avtek-cb{max-width:640px}\n"
        . ".avtek-cb-grid{display:grid;grid-template-columns:1fr;gap:10px}\n"
        . ".avtek-cb-field label{display:block;font-weight:600;margin:0 0 4px}\n"
        . ".avtek-cb-field input,.avtek-cb-field select,.avtek-cb-field textarea{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;box-sizing:border-box}\n"
        . ".avtek-cb-field textarea{min-height:90px}\n"
        . ".avtek-cb-actions{margin-top:10px;display:flex;gap:12px;align-items:center;flex-wrap:wrap}\n"
        . ".avtek-cb-actions button{padding:10px 16px;border:0;border-radius:10px;cursor:pointer}\n"
        . ".avtek-cb-msg{padding:8px 12px;border-radius:10px}\n"
        . ".avtek-cb-msg.ok{background:#e8fff1}\n"
        . ".avtek-cb-msg.err{background:#fff0f0}\n"
        . ".avtek-cb-req{color:#c00}\n"
        . ".avtek-cb-modal-backdrop{position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.55);display:none;z-index:9999}\n"
        . ".avtek-cb-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:18px;border-radius:14px;max-width:720px;width:92%;display:none;z-index:10000}\n"
        . ".avtek-cb-modal .close{position:absolute;right:12px;top:10px;cursor:pointer;font-size:18px}\n"
        . "</style>";

    // Optional skins (PHP templates) in /engine/plugins/avtek_callback/tpl/
    $skin = '';
    if ($mode === 'modal') {
        $skin = (string)($settings['skin_modal'] ?? '');
    } else {
        $skin = (string)($settings['skin_embed'] ?? '');
    }
    if ($skin !== '') {
        $skinFile = __DIR__ . '/tpl/' . basename($skin);
        if (is_file($skinFile)) {
            // variables for skin
            $btnText = (string)($settings['modal_button_text'] ?? 'Открыть форму');
            $mid = $formUid . '_modal';
            $bid = $formUid . '_bd';
            ob_start();
            include $skinFile;
            return (string)ob_get_clean();
        }
    }

    if ($mode === 'embed') {
        // Для EMBED используем внешний CSS, без инлайновых стилей/скриптов
        if (function_exists('register_stylesheet')) {
            register_stylesheet(home . '/engine/plugins/avtek_callback/tpl/avtek_cb.css');
            register_stylesheet(home . '/lib/notify.css');
        }
        $html .= $formHtml;
    } elseif ($mode === 'modal') {
        $btnText = avtek_cb_h((string)($settings['modal_button_text'] ?? (isset($lang['avtek_callback_open_form']) ? (string)$lang['avtek_callback_open_form'] : 'Открыть форму')));
        $mid = $formUid . '_modal';
        $bid = $formUid . '_bd';
        $html .= $css;
        $html .= '<button type="button" class="avtek-cb-open" data-avtek-modal="' . avtek_cb_h($mid) . '">' . $btnText . '</button>';
        $html .= '<div id="' . avtek_cb_h($bid) . '" class="avtek-cb-modal-backdrop"></div>';
        $html .= '<div id="' . avtek_cb_h($mid) . '" class="avtek-cb-modal">';
        $html .= '<span class="close" aria-label="Close">✕</span>';
        $html .= $formHtml;
        $html .= '</div>';
        $html .= $js;
        $html .= "<script>(function(){var mid='" . addslashes($mid) . "', bid='" . addslashes($bid) . "';\n"
            . "var m=document.getElementById(mid), b=document.getElementById(bid);\n"
            . "var btn=document.querySelector('[data-avtek-modal=\"'+mid+'\"]');\n"
            . "function openM(){ if(b) b.style.display='block'; if(m) m.style.display='block'; }\n"
            . "function closeM(){ if(b) b.style.display='none'; if(m) m.style.display='none'; }\n"
            . "if(btn) btn.addEventListener('click',openM);\n"
            . "if(b) b.addEventListener('click',closeM);\n"
            . "if(m){ var c=m.querySelector('.close'); if(c) c.addEventListener('click',closeM); }\n"
            . "})();</script>";
    } else {
        // Fallback: оставить прежнее поведение (инлайн CSS/JS)
        $html .= $css . $formHtml . $js;
    }

    return $html;
}

function avtek_cb_handle_submit(): void
{
    if (function_exists('LoadPluginLang')) {
        LoadPluginLang('avtek_callback', 'site');
    }
    $formId = (int)avtek_cb_req('form_id', 0);
    $form = avtek_cb_get_form($formId);
    if (!$form) {
        $err = isset($lang['avtek_callback_form_not_found']) ? (string)$lang['avtek_callback_form_not_found'] : 'Форма не найдена';
        avtek_cb_response_json(['ok' => false, 'error' => $err], 404);
    }

    $settings = avtek_cb_json_decode($form['settings'] ?? '');

    // honeypot
    $hpName = 'cb_hp_' . $formId;
    if (!empty($_POST[$hpName])) {
        avtek_cb_response_json(['ok' => true, 'message' => 'OK']);
    }

    $fields = avtek_cb_json_decode($form['fields'] ?? '');
    $raw = (array)($_POST['f'] ?? []);
    $data = [];

    // validate required fields
    $errors = [];
    foreach ($fields as $f) {
        $name = (string)($f['name'] ?? '');
        $label = (string)($f['label'] ?? $name);
        $req = !empty($f['required']);
        if ($name === '') {
            continue;
        }

        $v = $raw[$name] ?? '';
        if (is_array($v)) {
            $v = array_map('strval', $v);
            $v = array_map(function ($x) {
                return avtek_cb_trim($x, 500);
            }, $v);
        } else {
            $v = avtek_cb_trim((string)$v, 2000);
        }

        if ($req) {
            $empty = is_array($v) ? (count(array_filter($v)) === 0) : ($v === '');
            if ($empty) {
                $errors[] = (isset($lang['avtek_callback_fill_field']) ? (string)$lang['avtek_callback_fill_field'] : 'Заполните поле') . ': ' . $label;
                continue;
            }
        }

        // minimal type checks
        $type = (string)($f['type'] ?? 'text');
        if ($type === 'email' && $v !== '' && !avtek_cb_is_valid_email((string)$v)) {
            $errors[] = (isset($lang['avtek_callback_invalid_email']) ? (string)$lang['avtek_callback_invalid_email'] : 'Неверный email') . ': ' . $label;
        }

        $data[$label] = $v;
    }

    if ($errors) {
        avtek_cb_response_json(['ok' => false, 'error' => implode('<br>', $errors)], 422);
    }

    $meta = [
        'IP' => avtek_cb_getip(),
        'User-Agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'Referer' => (string)($_SERVER['HTTP_REFERER'] ?? ''),
        'URL' => (string)($settings['page_url_override'] ?? ($_SERVER['HTTP_REFERER'] ?? '')),
        'Дата' => avtek_cb_now(),
    ];

    // save lead
    $t = avtek_cb_leads_table();
    $sq = function_exists('db_squote') ? 'db_squote' : null;
    $q = function ($s) use ($sq) {
        if ($sq) {
            return $sq((string)$s);
        }
        return "'" . avtek_cb_db_escape((string)$s) . "'";
    };

    $pageUrl = (string)($_SERVER['HTTP_REFERER'] ?? '');
    avtek_cb_db_q(
        'INSERT INTO `' . $t . '` (form_id, created_at, ip, user_agent, referer, page_url, data, email_sent, telegram_sent, crm_response) VALUES ('
            . (int)$formId . ','
            . $q(avtek_cb_now()) . ','
            . $q($meta['IP']) . ','
            . $q($meta['User-Agent']) . ','
            . $q($meta['Referer']) . ','
            . $q($pageUrl) . ','
            . $q(avtek_cb_json_encode($data)) . ',0,0,'
            . $q('')
            . ')'
    );
    $leadId = avtek_cb_db_lastid();

    // send email
    $emails = (string)$form['emails'];
    if ($emails === '') {
        $emails = avtek_cb_get_setting('default_emails', '');
    }
    $to = avtek_cb_parse_list($emails);

    $subject = (string)($settings['mail_subject'] ?? ('Заявка: ' . (string)$form['title']));
    $body = avtek_cb_format_mail_body((string)$form['title'], $data, $meta);
    $from = avtek_cb_get_setting('mail_from', '');

    $emailOk = false;
    if ($to) {
        $emailOk = avtek_cb_send_mail($to, $subject, $body, $from);
    }

    // send telegram
    $tgOk = false;
    $tgToken = avtek_cb_get_setting('telegram_token', '');
    $tgChat = (string)($settings['telegram_chat_ids'] ?? avtek_cb_get_setting('telegram_chat_ids', ''));
    $tgChats = avtek_cb_parse_list($tgChat);
    if ($tgToken !== '' && $tgChats) {
        $text = avtek_cb_format_tg((string)$form['title'], $data, ['ID' => (string)$leadId, 'URL' => $pageUrl]);
        $tgOk = true;
        foreach ($tgChats as $cid) {
            $r = avtek_cb_tg_send($tgToken, $cid, $text);
            $tgOk = $tgOk && !empty($r['ok']);
        }
    }

    // CRM
    $crmResp = ['ok' => true];
    $crmType = (string)($settings['crm_type'] ?? avtek_cb_get_setting('crm_type', ''));
    $crmSettings = avtek_cb_json_decode((string)($settings['crm_settings'] ?? avtek_cb_get_setting('crm_settings', '')));

    $lead = [
        'id' => $leadId,
        'title' => (string)($form['title'] ?? 'Заявка с сайта'),
        'data' => $data,
        'meta' => $meta,
        'page_url' => $pageUrl,
        'created_at' => avtek_cb_now(),
        'message' => $body,
    ];

    if ($crmType) {
        $crmResp = avtek_cb_send_to_crm($crmType, $crmSettings, $lead);
    }

    // update flags
    avtek_cb_db_q(
        'UPDATE `' . $t . '` SET '
            . 'email_sent=' . (int)($emailOk ? 1 : 0) . ', '
            . 'telegram_sent=' . (int)($tgOk ? 1 : 0) . ', '
            . 'crm_response=' . $q(avtek_cb_json_encode($crmResp))
            . ' WHERE id=' . (int)$leadId
    );

    $conversionJs = (string)($settings['conversion_js'] ?? '');

    avtek_cb_response_json([
        'ok' => true,
        'id' => $leadId,
        'message' => (string)($settings['success_text'] ?? (isset($lang['avtek_callback_thanks']) ? (string)$lang['avtek_callback_thanks'] : 'Спасибо! Заявка отправлена.')),
        'success_mode' => (string)($settings['success_mode'] ?? 'inline'),
        'redirect_url' => (string)($settings['success_redirect_url'] ?? ''),
        'conversion_js' => $conversionJs,
        'email_sent' => (int)($emailOk ? 1 : 0),
        'telegram_sent' => (int)($tgOk ? 1 : 0),
        'crm_ok' => (int)(!empty($crmResp['ok']) ? 1 : 0),
    ]);
}
