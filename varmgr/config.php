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
array_push($cfg, array('name' => 'extdate', 'title' => $lang['varmgr:extdate'], 'descr' => $lang['varmgr:extdate#desc'], 'type' => 'select', 'values' => array('0' => $lang['varmgr:opt.off'], '1' => $lang['varmgr:opt.on']), 'value' => extra_get_param($plugin, 'extdate')));
array_push($cfg, array('name' => 'newdate', 'title' => $lang['varmgr:newdate'], 'descr' => $lang['varmgr:newdate#desc'], 'type' => 'input', 'value' => extra_get_param($plugin, 'newdate')));
// RUN 
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
?>