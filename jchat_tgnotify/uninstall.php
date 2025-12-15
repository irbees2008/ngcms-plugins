<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

if ($_REQUEST['action'] == 'commit') {
    // Удаляем все параметры плагина
    extra_unset_param('jchat_tgnotify', 'enabled');
    extra_unset_param('jchat_tgnotify', 'bot_token');
    extra_unset_param('jchat_tgnotify', 'chat_id');
    extra_unset_param('jchat_tgnotify', 'guests_only');
    extra_unset_param('jchat_tgnotify', 'first_only');
    extra_unset_param('jchat_tgnotify', 'flood_seconds');

    extra_commit_changes();
    plugin_mark_deinstalled('jchat_tgnotify');
} else {
    generate_install_page('jchat_tgnotify', 'Плагин будет удалён, все настройки будут очищены.', 'deinstall');
}
