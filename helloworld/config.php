<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Preload config
pluginsLoadConfig();

$cfg = array();
$cfgGroup = array();

array_push($cfgGroup, array(
    'name' => 'add_suffix',
    'title' => 'Добавлять суффикс к заголовкам новостей',
    'descr' => 'Если включено, к заголовку новости добавляется слово из локализации (ключ helloworld_suffix).',
    'type' => 'select',
    'values' => array('0' => 'Нет', '1' => 'Да'),
    'value' => extra_get_param($plugin, 'add_suffix')
));

array_push($cfg, array('mode' => 'group', 'title' => '<b>Настройки HelloWorld</b>', 'entries' => $cfgGroup));

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
