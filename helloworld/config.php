<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Preload config
pluginsLoadConfig();
LoadPluginLang('helloworld', 'config', '', '', ':');

$cfg = array();
$cfgGroup = array();

array_push($cfgGroup, array(
    'name' => 'add_suffix',
    'title' => $lang['helloworld:add_suffix_title'],
    'descr' => $lang['helloworld:add_suffix_descr'],
    'type' => 'select',
    'values' => array('0' => $lang['helloworld:no'], '1' => $lang['helloworld:yes']),
    'value' => extra_get_param($plugin, 'add_suffix')
));

array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['helloworld:settings_title'] . '</b>', 'entries' => $cfgGroup));

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
