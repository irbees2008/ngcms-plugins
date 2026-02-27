<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
pluginsLoadConfig();
LoadPluginLang($plugin, 'main', '', 'similar', ':');
include_once('inc/similar.php');
$cfg = array();
array_push($cfg, array('name' => 'rebuild', 'title' => $lang['similar:rebuild'], 'descr' => $lang['similar:rebuild_desc'], 'type' => 'select', 'value' => 0, 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'nosave' => 1));
$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $lang['similar:localsource'], 'descr' => $lang['similar:localsource_desc'], 'type' => 'select', 'values' => array('0' => $lang['similar:lsrc_site'], '1' => $lang['similar:lsrc_plugin']), 'value' => intval(extra_get_param($plugin, 'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['similar:cfg_display'] . '</b>', 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'similar_enabled', 'title' => $lang['similar:similar_enabled'], 'descr' => $lang['similar:similar_enabled_desc'], 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => extra_get_param($plugin, 'similar_enabled')));
array_push($cfgX, array('name' => 'count', 'title' => $lang['similar:similar_count'], 'descr' => $lang['similar:similar_count_desc'], 'type' => 'input', 'html_flags' => 'size="4"', 'value' => extra_get_param($plugin, 'count')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['similar:cfg_similar'] . '</b>', 'entries' => $cfgX));
//$cfgX = array();
//array_push($cfgX, array('name' => 'samecat_enabled', 'title' => $lang['similar:samecat_enabled'], 'descr' => $lang['similar:samecat_enabled_desc'], 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => extra_get_param($plugin, 'samecat_enabled')));
//array_push($cfgX, array('name' => 'samecat_count', 'title' => $lang['similar:samecat_count'], 'descr' => $lang['similar:samecat_count_desc'], 'type' => 'input', 'html_flags' => 'size="4"', 'value' => extra_get_param($plugin, 'samecat_count')));
//array_push($cfg,  array('mode' => 'group', 'title' => '<b>'.$lang['similar:cfg_samecateg'].'</b>', 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'pcall', 'title' => $lang['similar:pcall.title'], 'type' => 'select', 'values' => array('1' => $lang['yesa'], '0' => $lang['noa']), 'value' => intval(extra_get_param($plugin, 'pcall'))));
array_push($cfgX, array('name' => 'pcall_mode', 'title' => $lang['similar:pcall_mode.title'], 'descr' => $lang['similar:pcall_mode.descr'], 'type' => 'select', 'values' => array('0' => $lang['similar:pcall_mode.val.export'], '1' => $lang['similar:pcall_mode.val.short'], '2' => $lang['similar:pcall_mode.val.full']), 'value' => intval(extra_get_param($plugin, 'pcall_mode'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['similar:cfg_integration'] . '</b>', 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'countX', 'title' => $lang['similar:similarity']));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['similar:cfg_similarity'] . '</b>', 'entries' => $cfgX));
if (!$_REQUEST['action']) {
	generate_config_page($plugin, $cfg);
} elseif ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes($plugin, $cfg);
	if ($_REQUEST['rebuild']) {
		// Rebuild index table
		// * Truncate index
		$mysql->query("truncate table " . prefix . "_similar_index");
		// * Mark all news to have broken index
		$mysql->query("update " . prefix . "_news set similar_status = 0");
		print $lang['similar:rebuild_done'] . "<br/>";
	}
	print_commit_complete($plugin);
}
