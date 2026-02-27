<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['uprofile:description']));
$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $lang['uprofile:localsource'], 'type' => 'select', 'values' => array('0' => $lang['uprofile:lsrc_site'], '1' => $lang['uprofile:lsrc_plugin']), 'value' => intval(extra_get_param($plugin, 'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => $lang['uprofile:group.display'], 'entries' => $cfgX));
// RUN 
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
?>