<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
function plugin_jchat_tgnotify_install($action)
{
    global $lang;
    if ($action != 'autoapply') {
        loadPluginLang('jchat_tgnotify', 'config', '', '', ':');
    }
    switch ($action) {
        case 'confirm':
            generate_install_page('jchat_tgnotify', $lang['jchat_tgnotify:desc_install']);
            break;
        case 'autoapply':
        case 'apply':
            // Устанавливаем параметры плагина
            extra_set_param('jchat_tgnotify', 'enabled', '0');
            extra_set_param('jchat_tgnotify', 'bot_token', '');
            extra_set_param('jchat_tgnotify', 'chat_id', '');
            extra_set_param('jchat_tgnotify', 'guests_only', '0');
            extra_set_param('jchat_tgnotify', 'first_only', '1');
            extra_set_param('jchat_tgnotify', 'flood_seconds', '20');
            extra_commit_changes();
            plugin_mark_installed('jchat_tgnotify');
            header('Location: ' . home . '/engine/admin.php?mod=extras');
            exit;
            break;
    }
    return true;
}
