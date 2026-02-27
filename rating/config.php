<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
loadPluginLang($plugin, 'admin', '', '', ':');
// Fill configuration parameters
$skList = array();
if ($skDir = opendir(extras_dir . '/rating/tpl/skins')) {
	while ($skFile = readdir($skDir)) {
		if (!preg_match('/^\./', $skFile)) {
			$skList[$skFile] = $skFile;
		}
	}
	closedir($skDir);
}
// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['rating:description']));
array_push($cfgX, array('name' => 'regonly', 'title' => $lang['rating:regonly.title'], 'descr' => $lang['rating:regonly.descr'], 'type' => 'select', 'values' => array('0' => $lang['rating:opt.no'], '1' => $lang['rating:opt.yes']), 'value' => extra_get_param($plugin, 'regonly')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rating:group.plugin'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $lang['rating:localsource.title'], 'type' => 'select', 'values' => array('0' => $lang['rating:localsource.opt.site'], '1' => $lang['rating:localsource.opt.plugin']), 'value' => intval(extra_get_param($plugin, 'localsource'))));
array_push($cfgX, array('name' => 'localskin', 'title' => $lang['rating:localskin.title'], 'type' => 'select', 'values' => $skList, 'value' => extra_get_param($plugin, 'localskin') ? extra_get_param($plugin, 'localskin') : 'basic'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rating:group.display'], 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
