<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
LoadPluginLang('complain', 'config', '', 'complain', ':');
$plugin = 'complain';
// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => $lang['complain:description']));
$boolOptions = array('0' => $lang['complain:option.no'], '1' => $lang['complain:option.yes']);
$informReporterOptions = array('0' => $lang['complain:option.no'], '1' => $lang['complain:option.yes'], '2' => $lang['complain:option.on_request']);
$allowTextOptions = array('0' => $lang['complain:option.no'], '1' => $lang['complain:option.reg_only'], '2' => $lang['complain:option.yes']);
$cfgX = array();
// Убраны настройки отображения и вариант inline-формы: плагин всегда работает через AJAX-модалку, шаблоны берутся из каталога плагина
array_push($cfgX, array('name' => 'errlist', 'title' => $lang['complain:errlist.title'], 'descr' => $lang['complain:errlist.descr'], 'type' => 'text', 'html_flags' => 'cols=50 rows=6', 'value' => pluginGetVariable($plugin, 'errlist')));
array_push($cfgX, array('name' => 'inform_author', 'title' => $lang['complain:inform_author.title'], 'descr' => $lang['complain:inform_author.descr'], 'type' => 'select', 'values' => $boolOptions, 'value' => intval(pluginGetVariable($plugin, 'inform_author'))));
array_push($cfgX, array('name' => 'inform_admin', 'title' => $lang['complain:inform_admin.title'], 'descr' => $lang['complain:inform_admin.descr'], 'type' => 'select', 'values' => $boolOptions, 'value' => intval(pluginGetVariable($plugin, 'inform_admin'))));
array_push($cfgX, array('name' => 'inform_reporter', 'title' => $lang['complain:inform_reporter.title'], 'descr' => $lang['complain:inform_reporter.descr'], 'type' => 'select', 'values' => $informReporterOptions, 'value' => intval(pluginGetVariable($plugin, 'inform_reporter'))));
array_push($cfgX, array('name' => 'allow_unreg', 'title' => $lang['complain:allow_unreg.title'], 'descr' => $lang['complain:allow_unreg.descr'], 'type' => 'select', 'values' => $boolOptions, 'value' => intval(pluginGetVariable($plugin, 'allow_unreg'))));
array_push($cfgX, array('name' => 'allow_unreg_inform', 'title' => $lang['complain:allow_unreg_inform.title'], 'descr' => $lang['complain:allow_unreg_inform.descr'], 'type' => 'select', 'values' => $boolOptions, 'value' => intval(pluginGetVariable($plugin, 'allow_unreg_inform'))));
array_push($cfgX, array('name' => 'allow_text', 'title' => $lang['complain:allow_text.title'], 'descr' => $lang['complain:allow_text.descr'], 'type' => 'select', 'values' => $allowTextOptions, 'value' => intval(pluginGetVariable($plugin, 'allow_text'))));
array_push($cfg, array('mode' => 'group', 'title' => $lang['complain:group.notifications'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'admins', 'title' => $lang['complain:admins.title'], 'descr' => $lang['complain:admins.descr'], 'type' => 'text', 'html_flags' => 'cols=50 rows=2', 'value' => pluginGetVariable($plugin, 'admins')));
array_push($cfgX, array('name' => 'inform_admins', 'title' => $lang['complain:inform_admins.title'], 'descr' => $lang['complain:inform_admins.descr'], 'type' => 'select', 'values' => $boolOptions, 'value' => intval(pluginGetVariable($plugin, 'inform_admins'))));
array_push($cfg, array('mode' => 'group', 'title' => $lang['complain:group.access'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $lang['complain:cache.title'], 'descr' => $lang['complain:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['complain:option.yes'], '0' => $lang['complain:option.no']), 'value' => intval(pluginGetVariable($plugin, 'cache'))));
array_push($cfg, array('mode' => 'group', 'title' => $lang['complain:group.cache'], 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
