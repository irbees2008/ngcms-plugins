<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

$db_update = [];

if ($_REQUEST['action'] == 'commit') {
    // Удаляем все параметры плагина
    extra_set_param('jchat_tgnotify', 'enabled', null);
    extra_set_param('jchat_tgnotify', 'bot_token', null);
    extra_set_param('jchat_tgnotify', 'chat_id', null);
    extra_set_param('jchat_tgnotify', 'guests_only', null);
    extra_set_param('jchat_tgnotify', 'first_only', null);
    extra_set_param('jchat_tgnotify', 'flood_seconds', null);

    extra_commit_changes();

    if (fixdb_plugin_install('jchat_tgnotify', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('jchat_tgnotify');
    }
} else {
    generate_install_page('jchat_tgnotify', 'Будут удалены все настройки плагина: enabled, bot_token, chat_id, guests_only, first_only, flood_seconds', 'deinstall');
}
