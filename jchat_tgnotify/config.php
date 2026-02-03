<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('jchat_tgnotify', 'config', '', '', '#');

$cfg = [];
$cfgX = [];

// Основные настройки
array_push($cfgX, [
    'name'   => 'enabled',
    'title'  => 'Включить уведомления',
    'descr'  => 'Включить отправку уведомлений в Telegram',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'enabled')
]);

array_push($cfgX, [
    'name'  => 'bot_token',
    'title' => 'Telegram Bot Token',
    'descr' => 'Токен бота от @BotFather',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'bot_token')
]);

array_push($cfgX, [
    'name'  => 'chat_id',
    'title' => 'Admin Chat ID',
    'descr' => 'ID чата для отправки уведомлений (узнайте через @userinfobot)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'chat_id')
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>Основные настройки</b>',
    'entries' => $cfgX
]);

// Фильтры уведомлений
$cfgF = [];

array_push($cfgF, [
    'name'   => 'guests_only',
    'title'  => 'Только сообщения гостей',
    'descr'  => 'Уведомлять только о сообщениях незарегистрированных пользователей',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'guests_only')
]);

array_push($cfgF, [
    'name'   => 'first_only',
    'title'  => 'Только первое сообщение',
    'descr'  => 'Уведомлять только о первом сообщении пользователя за сессию',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'first_only')
]);

array_push($cfgF, [
    'name'  => 'flood_seconds',
    'title' => 'Антифлуд (секунды)',
    'descr' => 'Минимальный интервал между уведомлениями (0 = выключено, рекомендуется 15-30)',
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'flood_seconds')
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>Фильтры уведомлений (только для jChat)</b>',
    'entries' => $cfgF
]);

// Типы уведомлений
$cfgTypes = [];

array_push($cfgTypes, [
    'name'   => 'notify_jchat',
    'title'  => '💬 jChat',
    'descr'  => 'Уведомлять о новых сообщениях в чате',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'notify_jchat') ?? '1'
]);

array_push($cfgTypes, [
    'name'   => 'notify_feedback',
    'title'  => '📧 Обратная связь',
    'descr'  => 'Уведомлять о новых обращениях через формы обратной связи',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'notify_feedback') ?? '0'
]);

array_push($cfgTypes, [
    'name'   => 'notify_pm',
    'title'  => '✉️ Личные сообщения',
    'descr'  => 'Уведомлять о новых личных сообщениях администратору',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'notify_pm') ?? '0'
]);

array_push($cfgTypes, [
    'name'   => 'notify_complain',
    'title'  => '⚠️ Жалобы',
    'descr'  => 'Уведомлять о новых жалобах (на новости, посты форума)',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'notify_complain') ?? '0'
]);

array_push($cfgTypes, [
    'name'   => 'notify_comment',
    'title'  => '💭 Комментарии',
    'descr'  => 'Уведомлять о новых комментариях (требует модификации)',
    'type'   => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value'  => extra_get_param($plugin, 'notify_comment') ?? '0'
]);

array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>Типы уведомлений</b>',
    'entries' => $cfgTypes
]);

// Обработка сохранения
if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
