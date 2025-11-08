<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}
// Install DB structures for plugin 'news_templates'
function plugin_news_templates_install($action)
{
    global $lang;
    // Two-phase install: 'confirm' -> show page, 'apply' -> perform DB changes
    if ($action == 'confirm') {
        generate_install_page('news_templates', 'Установка плагина «Шаблоны новостей». Будет создана таблица для хранения шаблонов.');
        return true;
    }
    if ($action != 'apply') {
        return false;
    }
    $db_update = [
        [
            'table'  => 'news_templates',
            'action' => 'cmodify',
            'key'    => 'PRIMARY KEY(id), KEY ord (ord), KEY active (active)',
            'fields' => [
                ['action' => 'cmodify', 'name' => 'id',      'type' => 'int(11)',    'params' => 'NOT NULL AUTO_INCREMENT'],
                ['action' => 'cmodify', 'name' => 'ord',     'type' => 'int(11)',    'params' => 'NOT NULL DEFAULT 0'],
                ['action' => 'cmodify', 'name' => 'title',   'type' => 'varchar(190)', 'params' => 'NOT NULL DEFAULT ""'],
                ['action' => 'cmodify', 'name' => 'content', 'type' => 'mediumtext', 'params' => 'NOT NULL'],
                ['action' => 'cmodify', 'name' => 'active',  'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 1'],
                ['action' => 'cmodify', 'name' => 'dt',      'type' => 'int(11)',    'params' => 'NOT NULL DEFAULT 0'],
            ],
        ],
    ];
    $t = fixdb_plugin_install('news_templates', $db_update, 'install');
    // Default config
    pluginSetVariable('news_templates', 'count', '3');
    pluginsSaveConfig();
    // Mark plugin as installed in conf/plugins.php
    plugin_mark_installed('news_templates');
    return $t;
}
