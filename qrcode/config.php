<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
	
//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();
loadPluginLang('qrcode', 'config', '', '', ':');
global $lang, $plugin;
	
// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => $lang['qrcode:config.description']));

$cfgX = array();     
array_push($cfgX, array('name' => 'chs', 'title' => $lang['qrcode:config.size'], 'type' => 'input', 'value' => intval(pluginGetVariable($plugin,'chs'))?pluginGetVariable($plugin,'chs'):'150'));
array_push($cfgX, array('name' => 'chld', 'title' => $lang['qrcode:config.error_correction'], 'descr' => $lang['qrcode:config.error_correction_help'], 'type' => 'select', 'values' => array ( 'L' => 'L', 'M' => 'M', 'Q' => 'Q', 'H' => 'H'), 'value' => pluginGetVariable($plugin,'chld')));
array_push($cfgX, array('name' => 'margin', 'title' => $lang['qrcode:config.margin'], 'type' => 'input', 'value' => intval(pluginGetVariable($plugin,'margin'))?pluginGetVariable($plugin,'margin'):'4'));	
array_push($cfgX, array('name' => 'upload', 'title' => $lang['qrcode:config.upload'], 'type' => 'checkbox', 'value' => pluginGetVariable($plugin,'upload')));	
array_push($cfg,  array('mode' => 'group', 'title' => '<b>' . $lang['qrcode:config.group_main'] . '</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $lang['qrcode:config.template_source'], 'descr' => $lang['qrcode:config.template_source_help'], 'type' => 'select', 'values' => array ( '0' => $lang['qrcode:config.template_source_site'], '1' => $lang['qrcode:config.template_source_plugin']), 'value' => intval(pluginGetVariable($plugin,'localsource'))));
array_push($cfg,  array('mode' => 'group', 'title' => '<b>' . $lang['qrcode:config.group_display'] . '</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'clear_qrcode', 'title' => $lang['qrcode:config.cleanup'], 'type' => 'select', 'value' => 0, 'values' => array ( 0 => $lang['noa'], 1 => $lang['yesa']), 'nosave' => 1));
array_push($cfg,  array('mode' => 'group', 'title' => '<b>' . $lang['qrcode:config.group_cleanup'] . '</b>', 'entries' => $cfgX));

// RUN 
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	if ($_REQUEST['clear_qrcode']) {
		clear_qrcode();
	}
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}

function clear_qrcode() {
	global $mysql, $fmanager, $config;
	
	@include_once root.'includes/classes/upload.class.php';
	@include_once root.'includes/inc/file_managment.php';

	$fmanager = new file_managment();

	foreach (($mysql->select("select id, description from ".prefix."_images where folder='qrcode'")) as $file) {
		// Check if referred news not exists
		if (!is_array($mysql->record("select * from ".prefix."_news where id = ".db_squote($file['description'])))) {
			$fmanager->file_delete(array('type' => 'image', 'category' => 'qrcode', 'id' => $file['id']));
			if (($dir = get_plugcache_dir('qrcode'))) {
				if ($handle = opendir($dir)) {
					unlink ($dir.md5('qrcode'.$file['description'].$config['home_url'].$config['theme'].$config['default_lang']).'.txt');
					closedir($handle); 
				}
			}
		}
	}
	msg(array('type' => 'info', 'info' => $lang['qrcode:config.cleanup_images_removed']));
	msg(array('type' => 'info', 'info' => $lang['qrcode:config.cleanup_cache_cleared']));
}