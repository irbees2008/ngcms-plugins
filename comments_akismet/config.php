<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
LoadPluginLang('comments_akismet', 'config', '', 'comments_akismet', ':');
$plugin = 'comments_akismet';
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['comments_akismet:description']));
array_push($cfgX, array('name' => 'akismet_server', 'title' => $lang['comments_akismet:option.server'], 'type' => 'input', 'value' => extra_get_param($plugin, 'akismet_server') ? extra_get_param($plugin, 'akismet_server') : 'rest.akismet.com'));
array_push($cfgX, array('name' => 'akismet_apikey', 'title' => $lang['comments_akismet:option.apikey'], 'type' => 'input', 'value' => extra_get_param($plugin, 'akismet_apikey')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['comments_akismet:group.settings'], 'entries' => $cfgX));
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
