<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}
pluginsLoadConfig();
loadPluginLang('news_templates', 'main', '', '', ':');
if ($_REQUEST['action'] == 'commit') {
    $db_update = [
        [
            'table'  => 'news_templates',
            'action' => 'drop',
        ],
    ];
    fixdb_plugin_install('news_templates', $db_update, 'deinstall');
    // Remove config
    pluginSetVariable('news_templates', 'count', null);
    pluginsSaveConfig();
    // Mark plugin as deinstalled
    plugin_mark_deinstalled('news_templates');
    header('Location: ' . home . '/engine/admin.php?mod=extras');
    exit;
} else {
    generate_install_page('news_templates', $lang['news_templates:desc_deinstall'], 'deinstall');
}
