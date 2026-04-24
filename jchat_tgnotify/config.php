<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
loadPluginLang('jchat_tgnotify', 'config', '', '', ':');
global $lang;
$cfg  = [];
$cfgX = [];
// Основные настройки
array_push($cfgX, [
    'name'   => 'enabled',
    'title'  => $lang['jchat_tgnotify:conf.enabled'],
    'descr'  => $lang['jchat_tgnotify:conf.enabled.descr'],
    'type'   => 'select',
    'values' => ['0' => $lang['no'], '1' => $lang['yes']],
    'value'  => extra_get_param($plugin, 'enabled')
]);
array_push($cfgX, [
    'name'  => 'bot_token',
    'title' => $lang['jchat_tgnotify:conf.bot_token'],
    'descr' => $lang['jchat_tgnotify:conf.bot_token.descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'bot_token')
]);
array_push($cfgX, [
    'name'  => 'chat_id',
    'title' => $lang['jchat_tgnotify:conf.chat_id'],
    'descr' => $lang['jchat_tgnotify:conf.chat_id.descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'chat_id')
]);
array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>' . $lang['jchat_tgnotify:conf.group.main'] . '</b>',
    'entries' => $cfgX
]);
// Фильтры уведомлений
$cfgF = [];
array_push($cfgF, [
    'name'   => 'guests_only',
    'title'  => $lang['jchat_tgnotify:conf.guests_only'],
    'descr'  => $lang['jchat_tgnotify:conf.guests_only.descr'],
    'type'   => 'select',
    'values' => ['0' => $lang['no'], '1' => $lang['yes']],
    'value'  => extra_get_param($plugin, 'guests_only')
]);
array_push($cfgF, [
    'name'   => 'first_only',
    'title'  => $lang['jchat_tgnotify:conf.first_only'],
    'descr'  => $lang['jchat_tgnotify:conf.first_only.descr'],
    'type'   => 'select',
    'values' => ['0' => $lang['no'], '1' => $lang['yes']],
    'value'  => extra_get_param($plugin, 'first_only')
]);
array_push($cfgF, [
    'name'  => 'flood_seconds',
    'title' => $lang['jchat_tgnotify:conf.flood_seconds'],
    'descr' => $lang['jchat_tgnotify:conf.flood_seconds.descr'],
    'type'  => 'input',
    'value' => extra_get_param($plugin, 'flood_seconds')
]);
array_push($cfg, [
    'mode'    => 'group',
    'title'   => '<b>' . $lang['jchat_tgnotify:conf.group.filters'] . '</b>',
    'entries' => $cfgF
]);
// Обработка сохранения
if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
