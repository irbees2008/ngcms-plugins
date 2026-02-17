<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('weather', 'config', '', '', ':');

$cfg = array();
$cfgX = array();

array_push($cfgX, array(
    'name' => 'api_key',
    'title' => $lang['weather:api_key'],
    'descr' => $lang['weather:api_key#desc'],
    'type' => 'input',
    'value' => pluginGetVariable('weather', 'api_key') ?: ''
));

array_push($cfgX, array(
    'name' => 'cache_expire',
    'title' => $lang['weather:cache_expire'],
    'descr' => $lang['weather:cache_expire#desc'],
    'type' => 'input',
    'value' => pluginGetVariable('weather', 'cache_expire') ?: '3600'
));

array_push($cfg, array(
    'mode' => 'group',
    'title' => $lang['weather:group.config'],
    'entries' => $cfgX
));

if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('weather', $cfg);
    print_commit_complete('weather');
} else {
    generate_config_page('weather', $cfg);
}
