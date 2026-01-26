<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}
pluginsLoadConfig();
$db_update = [
    [
        'table'  => 'news_templates',
        'action' => 'drop',
    ],
];
if ($_REQUEST['action'] == 'commit') {
    if (fixdb_plugin_install('news_templates', $db_update, 'deinstall')) {
        // Remove config
        pluginSetVariable('news_templates', 'count', null);
        pluginsSaveConfig();
        // Mark plugin as deinstalled
        plugin_mark_deinstalled('news_templates');
    }
} else {
    generate_install_page('news_templates', 'Удаление плагина «Шаблоны новостей». Будет удалена таблица и настройки.', 'deinstall');
}
