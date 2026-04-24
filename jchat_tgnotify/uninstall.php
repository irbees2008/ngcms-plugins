<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
loadPluginLang('jchat_tgnotify', 'config', '', '', ':');
if ($_REQUEST['action'] == 'commit') {
    global $PLUGINS;
    if (isset($PLUGINS['config']['jchat_tgnotify'])) {
        foreach (['enabled', 'bot_token', 'chat_id', 'guests_only', 'first_only', 'flood_seconds'] as $param) {
            unset($PLUGINS['config']['jchat_tgnotify'][$param]);
        }
        if (empty($PLUGINS['config']['jchat_tgnotify'])) {
            unset($PLUGINS['config']['jchat_tgnotify']);
        }
    }
    extra_commit_changes();
    plugin_mark_deinstalled('jchat_tgnotify');
    header('Location: ' . home . '/engine/admin.php?mod=extras');
    exit;
} else {
    generate_install_page('jchat_tgnotify', $lang['jchat_tgnotify:desc_uninstall'], 'deinstall');
}
