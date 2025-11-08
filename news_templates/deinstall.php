<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}

function plugin_news_templates_deinstall($action)
{
    if ($action == 'confirm') {
        generate_install_page('news_templates', 'Удаление плагина «Шаблоны новостей». Будет удалена таблица и настройки.', 'deinstall');
        return true;
    }

    if ($action != 'apply') {
        return false;
    }

    $db_update = [
        [
            'table'  => 'news_templates',
            'action' => 'drop',
        ],
    ];

    $t = fixdb_plugin_install('news_templates', $db_update, 'deinstall');

    // Remove config
    pluginSetVariable('news_templates', 'count', null);
    pluginsSaveConfig();

    // Mark plugin as deinstalled
    plugin_mark_deinstalled('news_templates');

    return $t;
}
