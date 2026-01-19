<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

$cfg = [];
$grp = [];

// Основные настройки
array_push($grp, [
    'name'   => 'enabled',
    'title'  => 'Включить Web Push уведомления',
    'descr'  => 'Активировать систему push-уведомлений на сайте',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'enabled'),
]);

array_push($grp, [
    'name'   => 'show_button',
    'title'  => 'Показывать кнопку подписки',
    'descr'  => 'Автоматически показывать кнопку подписки на уведомления',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'show_button'),
]);

array_push($grp, [
    'name'  => 'subscribe_text',
    'title' => 'Текст кнопки подписки',
    'descr' => 'Текст на кнопке подписки на уведомления',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'subscribe_text'),
]);

array_push($grp, [
    'name'   => 'auto_send',
    'title'  => 'Автоматическая отправка при публикации',
    'descr'  => 'Автоматически отправлять уведомления всем подписчикам при публикации новой новости на главной',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'auto_send'),
]);

// Проверяем наличие плагина mailing для интеграции
$mailingActive = function_exists('pluginIsActive') && pluginIsActive('mailing');

array_push($grp, [
    'name'   => 'mailing_integration',
    'title'  => 'Интеграция с плагином Mailing',
    'descr'  => 'Отправлять также email-уведомление подписчикам mailing при публикации новости' .
        ($mailingActive ? ' <span style="color:green;">✓ Плагин mailing активен</span>' : ' <span style="color:orange;">⚠ Плагин mailing не активен</span>'),
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'mailing_integration'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>Основные настройки</b>',
    'entries' => $grp,
]);

// VAPID настройки
$grp = [];

array_push($grp, [
    'name'  => 'vapid_public',
    'title' => 'VAPID Public Key',
    'descr' => 'Публичный ключ VAPID (генерируется через send.php?action=genkeys)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'vapid_public'),
]);

array_push($grp, [
    'name'  => 'vapid_private',
    'title' => 'VAPID Private Key',
    'descr' => 'Приватный ключ VAPID (храните в секрете!)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'vapid_private'),
]);

array_push($grp, [
    'name'  => 'vapid_subject',
    'title' => 'VAPID Subject',
    'descr' => 'Email или URL сайта (формат: mailto:admin@example.com или https://example.com)' .
        '<div style="margin-top:15px; padding:12px; background:#f0f7ff; border:1px solid #b3d9ff; border-radius:5px;">' .
        '<button type="button" id="webpush-generate-keys" class="btn btn-success" style="padding:8px 16px; font-size:14px; margin-right:10px;" onclick="webpushGenerateKeys()">' .
        '<span id="webpush-gen-icon">🔑</span> Сгенерировать VAPID ключи' .
        '</button>' .
        '<span style="color:#666; font-size:13px;">Автоматически заполнит поля выше</span>' .
        '<div id="webpush-gen-status" style="margin-top:10px; display:none; padding:10px; border-radius:5px;"></div>' .
        '</div>' .
        '<script>' .
        'function webpushGenerateKeys() {' .
        '  const generateBtn = document.getElementById("webpush-generate-keys");' .
        '  const statusDiv = document.getElementById("webpush-gen-status");' .
        '  const iconSpan = document.getElementById("webpush-gen-icon");' .
        '  generateBtn.disabled = true;' .
        '  iconSpan.textContent = "⏳";' .
        '  generateBtn.innerHTML = iconSpan.outerHTML + " Генерация ключей...";' .
        '  statusDiv.style.display = "block";' .
        '  statusDiv.style.background = "#e3f2fd";' .
        '  statusDiv.style.color = "#1976d2";' .
        '  statusDiv.innerHTML = "⏳ Генерация VAPID ключей...";' .
        '  fetch("' . home . '/engine/plugins/webpush/generate_keys.php", {method: "GET", cache: "no-store"})' .
        '    .then(r => r.ok ? r.json() : Promise.reject("HTTP " + r.status))' .
        '    .then(data => {' .
        '      if (data.ok && data.keys) {' .
        '        const publicInput = document.querySelector("input[name=\'webpush_conf[vapid_public]\']") || document.querySelector("input[name*=\'vapid_public\']");' .
        '        const privateInput = document.querySelector("input[name=\'webpush_conf[vapid_private]\']") || document.querySelector("input[name*=\'vapid_private\']");' .
        '        const subjectInput = document.querySelector("input[name=\'webpush_conf[vapid_subject]\']") || document.querySelector("input[name*=\'vapid_subject\']");' .
        '        console.log("Найдены поля:", {public: !!publicInput, private: !!privateInput, subject: !!subjectInput});' .
        '        console.log("Публичный ключ:", data.keys.publicKey.substring(0, 50));' .
        '        if (publicInput) { publicInput.value = data.keys.publicKey; console.log("Public заполнен"); }' .
        '        if (privateInput) { privateInput.value = data.keys.privateKey; console.log("Private заполнен"); }' .
        '        if (subjectInput && !subjectInput.value) { subjectInput.value = "' . home . '"; console.log("Subject заполнен"); }' .
        '        if (typeof ngNotifications !== "undefined") {' .
        '          ngNotifications.show({title: "✅ Ключи сгенерированы!", text: "VAPID ключи успешно вставлены в поля выше. Не забудьте СОХРАНИТЬ ИЗМЕНЕНИЯ!", type: "success", time: 8000});' .
        '        }' .
        '        statusDiv.style.background = "#e8f5e9";' .
        '        statusDiv.style.color = "#2e7d32";' .
        '        statusDiv.innerHTML = "✅ <b>Ключи успешно сгенерированы и вставлены!</b><br><small>Публичный ключ: " + data.keys.publicKey.substring(0, 40) + "...</small><br><small style=\'color:#f57c00;\'>⚠️ Не забудьте нажать кнопку <b>СОХРАНИТЬ ИЗМЕНЕНИЯ</b> внизу страницы!</small>";' .
        '        iconSpan.textContent = "✅";' .
        '        generateBtn.innerHTML = iconSpan.outerHTML + " Ключи вставлены";' .
        '        setTimeout(() => {' .
        '          statusDiv.style.display = "none";' .
        '          generateBtn.disabled = false;' .
        '          iconSpan.textContent = "🔑";' .
        '          generateBtn.innerHTML = iconSpan.outerHTML + " Сгенерировать VAPID ключи";' .
        '        }, 15000);' .
        '      } else { throw new Error(data.error || "Неизвестная ошибка"); }' .
        '    })' .
        '    .catch(error => {' .
        '      console.error("Key generation error:", error);' .
        '      if (typeof ngNotifications !== "undefined") {' .
        '        ngNotifications.show({title: "❌ Ошибка генерации", text: error + ". Проверьте установку minishlink/web-push", type: "error", time: 6000});' .
        '      }' .
        '      statusDiv.style.background = "#ffebee";' .
        '      statusDiv.style.color = "#c62828";' .
        '      statusDiv.innerHTML = "❌ <b>Ошибка генерации:</b> " + error + "<br><small>Проверьте: 1) Установлен ли Composer пакет minishlink/web-push 2) Логи PHP</small>";' .
        '      generateBtn.disabled = false;' .
        '      iconSpan.textContent = "❌";' .
        '      generateBtn.innerHTML = iconSpan.outerHTML + " Ошибка. Попробовать снова";' .
        '      setTimeout(() => {' .
        '        iconSpan.textContent = "🔑";' .
        '        generateBtn.innerHTML = iconSpan.outerHTML + " Сгенерировать VAPID ключи";' .
        '      }, 3000);' .
        '    });' .
        '}' .
        '</script>',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'vapid_subject'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>VAPID настройки</b>',
    'entries' => $grp,
]);

// Внешний вид уведомлений
$grp = [];

array_push($grp, [
    'name'  => 'default_icon',
    'title' => 'Иконка уведомления',
    'descr' => 'Путь к изображению иконки (рекомендуется 192x192px)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'default_icon'),
]);

array_push($grp, [
    'name'  => 'default_badge',
    'title' => 'Badge иконка',
    'descr' => 'Путь к монохромному badge изображению (рекомендуется 96x96px)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'default_badge'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>Внешний вид уведомлений</b>',
    'entries' => $grp,
]);

// Безопасность
$grp = [];

array_push($grp, [
    'name'  => 'send_secret',
    'title' => 'Секретный ключ для отправки',
    'descr' => 'Токен для защиты send.php (используется при отправке уведомлений)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'send_secret'),
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>Безопасность</b>',
    'entries' => $grp,
]);

// Информация
$info = '<div class="alert alert-info">';
$info .= '<h4>Инструкция по настройке:</h4>';
$info .= '<ol>';
$info .= '<li><strong>Используйте кнопку "Сгенерировать VAPID ключи"</strong> в разделе VAPID настройки выше</li>';
$info .= '<li>Убедитесь, что файл webpush-sw.js находится в корне сайта</li>';
$info .= '<li>Для отправки уведомлений используйте: <br><code>POST /engine/plugins/webpush/send.php?secret=...<br>Параметры: title, body, url</code></li>';
$info .= '</ol>';
$info .= '<p><strong>Важно:</strong> Web Push работает только по HTTPS (кроме localhost для тестирования)</p>';
$info .= '<p style="color:#666; font-size:13px;">📦 Библиотека minishlink/web-push встроена в плагин (lib/vendor/)</p>';
$info .= '</div>';

array_push($cfg, [
    'mode'  => 'info',
    'title' => $info,
]);

// Обработка сохранения
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
