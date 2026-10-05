<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('webpush', 'config', '', 'webpush', ':');
global $lang;

$cfg = [];
$grp = [];
$jsMessages = [
    'buttonLoading' => $lang['webpush:generate_button_loading'],
    'generateButton' => $lang['webpush:generate_button'],
    'statusLoading' => $lang['webpush:generate_status_loading'],
    'toastTitle' => $lang['webpush:generate_toast_title'],
    'toastText' => $lang['webpush:generate_toast_text'],
    'successHtml' => $lang['webpush:generate_success_html'],
    'doneButton' => $lang['webpush:generate_done_button'],
    'errorTitle' => $lang['webpush:generate_error_title'],
    'errorDetail' => $lang['webpush:generate_error_detail'],
    'unknownError' => $lang['webpush:generate_unknown_error'],
    'errorHtml' => $lang['webpush:generate_error_html'],
    'retryButton' => $lang['webpush:generate_retry_button'],
];
$jsMessagesJson = json_encode($jsMessages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// Основные настройки
array_push($grp, [
    'name'   => 'enabled',
    'title'  => $lang['webpush:enabled_title'],
    'descr'  => $lang['webpush:enabled_descr'],
    'type'   => 'select',
    'values' => ['0' => $lang['webpush:option_no'], '1' => $lang['webpush:option_yes']],
    'value'  => extra_get_param($plugin, 'enabled'),
]);

array_push($grp, [
    'name'   => 'show_button',
    'title'  => $lang['webpush:show_button_title'],
    'descr'  => $lang['webpush:show_button_descr'],
    'type'   => 'select',
    'values' => ['0' => $lang['webpush:option_no'], '1' => $lang['webpush:option_yes']],
    'value'  => extra_get_param($plugin, 'show_button'),
]);

array_push($grp, [
    'name'  => 'subscribe_text',
    'title' => $lang['webpush:subscribe_text_title'],
    'descr' => $lang['webpush:subscribe_text_descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'subscribe_text'),
]);

array_push($grp, [
    'name'   => 'auto_send',
    'title'  => $lang['webpush:auto_send_title'],
    'descr'  => $lang['webpush:auto_send_descr'],
    'type'   => 'select',
    'values' => ['0' => $lang['webpush:option_no'], '1' => $lang['webpush:option_yes']],
    'value'  => extra_get_param($plugin, 'auto_send'),
]);

// Проверяем наличие плагина mailing для интеграции
$mailingActive = function_exists('pluginIsActive') && pluginIsActive('mailing');

array_push($grp, [
    'name'   => 'mailing_integration',
    'title'  => $lang['webpush:mailing_integration_title'],
    'descr'  => $lang['webpush:mailing_integration_descr'] .
        ($mailingActive ? ' <span style="color:green;">✓ ' . $lang['webpush:mailing_active'] . '</span>' : ' <span style="color:orange;">⚠ ' . $lang['webpush:mailing_inactive'] . '</span>'),
    'type'   => 'select',
    'values' => ['0' => $lang['webpush:option_no'], '1' => $lang['webpush:option_yes']],
    'value'  => extra_get_param($plugin, 'mailing_integration'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => $lang['webpush:general_group'],
    'entries' => $grp,
]);

// VAPID настройки
$grp = [];

array_push($grp, [
    'name'  => 'vapid_public',
    'title' => $lang['webpush:vapid_public_title'],
    'descr' => $lang['webpush:vapid_public_descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'vapid_public'),
]);

array_push($grp, [
    'name'  => 'vapid_private',
    'title' => $lang['webpush:vapid_private_title'],
    'descr' => $lang['webpush:vapid_private_descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'vapid_private'),
]);

array_push($grp, [
    'name'  => 'vapid_subject',
    'title' => $lang['webpush:vapid_subject_title'],
    'descr' => $lang['webpush:vapid_subject_descr'] .
        '<div style="margin-top:15px; padding:12px; background:#f0f7ff; border:1px solid #b3d9ff; border-radius:5px;">' .
        '<button type="button" id="webpush-generate-keys" class="btn btn-success" style="padding:8px 16px; font-size:14px; margin-right:10px;" onclick="webpushGenerateKeys()">' .
        '<span id="webpush-gen-icon">🔑</span> ' . $lang['webpush:generate_button'] .
        '</button>' .
        '<span style="color:#666; font-size:13px;">' . $lang['webpush:generate_auto_fill'] . '</span>' .
        '<div id="webpush-gen-status" style="margin-top:10px; display:none; padding:10px; border-radius:5px;"></div>' .
        '</div>' .
        '<script>' .
        'const webpushMessages = ' . $jsMessagesJson . ';' .
        'function webpushGenerateKeys() {' .
        '  const generateBtn = document.getElementById("webpush-generate-keys");' .
        '  const statusDiv = document.getElementById("webpush-gen-status");' .
        '  const iconSpan = document.getElementById("webpush-gen-icon");' .
        '  generateBtn.disabled = true;' .
        '  iconSpan.textContent = "⏳";' .
        '  generateBtn.innerHTML = iconSpan.outerHTML + " " + webpushMessages.buttonLoading;' .
        '  statusDiv.style.display = "block";' .
        '  statusDiv.style.background = "#e3f2fd";' .
        '  statusDiv.style.color = "#1976d2";' .
        '  statusDiv.innerHTML = webpushMessages.statusLoading;' .
        '  fetch("' . home . '/engine/plugins/webpush/generate_keys.php", {method: "GET", cache: "no-store"})' .
        '    .then(r => r.ok ? r.json() : Promise.reject("HTTP " + r.status))' .
        '    .then(data => {' .
        '      if (data.ok && data.keys) {' .
        '        const publicInput = document.querySelector("input[name=\'webpush_conf[vapid_public]\']") || document.querySelector("input[name*=\'vapid_public\']");' .
        '        const privateInput = document.querySelector("input[name=\'webpush_conf[vapid_private]\']") || document.querySelector("input[name*=\'vapid_private\']");' .
        '        const subjectInput = document.querySelector("input[name=\'webpush_conf[vapid_subject]\']") || document.querySelector("input[name*=\'vapid_subject\']");' .
        '        console.log("Fields found:", {public: !!publicInput, private: !!privateInput, subject: !!subjectInput});' .
        '        console.log("Public key:", data.keys.publicKey.substring(0, 50));' .
        '        if (publicInput) { publicInput.value = data.keys.publicKey; console.log("Public key filled"); }' .
        '        if (privateInput) { privateInput.value = data.keys.privateKey; console.log("Private key filled"); }' .
        '        if (subjectInput && !subjectInput.value) { subjectInput.value = "' . home . '"; console.log("Subject filled"); }' .
        '        if (typeof ngNotifications !== "undefined") {' .
        '        ngNotifications.show({title: webpushMessages.toastTitle, text: webpushMessages.toastText, type: "success", time: 8000});' .
        '        }' .
        '        statusDiv.style.background = "#e8f5e9";' .
        '        statusDiv.style.color = "#2e7d32";' .
        '        statusDiv.innerHTML = webpushMessages.successHtml.replace("%s", data.keys.publicKey.substring(0, 40));' .
        '        iconSpan.textContent = "✅";' .
        '        generateBtn.innerHTML = iconSpan.outerHTML + " " + webpushMessages.doneButton;' .
        '        setTimeout(() => {' .
        '          statusDiv.style.display = "none";' .
        '          generateBtn.disabled = false;' .
        '          iconSpan.textContent = "🔑";' .
        '          generateBtn.innerHTML = iconSpan.outerHTML + " " + webpushMessages.generateButton;' .
        '        }, 15000);' .
        '      } else { throw new Error(data.error || webpushMessages.unknownError); }' .
        '    })' .
        '    .catch(error => {' .
        '      console.error("Key generation error:", error);' .
        '      if (typeof ngNotifications !== "undefined") {' .
        '        ngNotifications.show({title: webpushMessages.errorTitle, text: error + ". " + webpushMessages.errorDetail, type: "error", time: 6000});' .
        '      }' .
        '      statusDiv.style.background = "#ffebee";' .
        '      statusDiv.style.color = "#c62828";' .
        '      statusDiv.innerHTML = webpushMessages.errorHtml.replace("%s", error);' .
        '      generateBtn.disabled = false;' .
        '      iconSpan.textContent = "❌";' .
        '      generateBtn.innerHTML = iconSpan.outerHTML + " " + webpushMessages.retryButton;' .
        '      setTimeout(() => {' .
        '        iconSpan.textContent = "🔑";' .
        '        generateBtn.innerHTML = iconSpan.outerHTML + " " + webpushMessages.generateButton;' .
        '      }, 3000);' .
        '    });' .
        '}' .
        '</script>',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'vapid_subject'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => $lang['webpush:vapid_group'],
    'entries' => $grp,
]);

// Внешний вид уведомлений
$grp = [];

array_push($grp, [
    'name'  => 'default_icon',
    'title' => $lang['webpush:default_icon_title'],
    'descr' => $lang['webpush:default_icon_descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'default_icon'),
]);

array_push($grp, [
    'name'  => 'default_badge',
    'title' => $lang['webpush:default_badge_title'],
    'descr' => $lang['webpush:default_badge_descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'default_badge'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => $lang['webpush:icon_group'],
    'entries' => $grp,
]);

// Безопасность
$grp = [];

array_push($grp, [
    'name'  => 'send_secret',
    'title' => $lang['webpush:send_secret_title'],
    'descr' => $lang['webpush:send_secret_descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'send_secret'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => $lang['webpush:security_group'],
    'entries' => $grp,
]);

array_push($cfg, [
    'mode'  => 'info',
    'title' => $lang['webpush:info_title'],
]);

// Обработка сохранения
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
