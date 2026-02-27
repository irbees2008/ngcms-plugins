<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
loadPluginLang('lastcomments', 'config', '', '', ':');
// Fill configuration parameters
$cfg  = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['lastcomments:plugin.descr']));

// --- Sidebar panel ---
array_push($cfgX, array('name' => 'sidepanel', 'title' => $lang['lastcomments:sidepanel.title'], 'descr' => $lang['lastcomments:sidepanel.descr'], 'type' => 'select', 'values' => array('0' => $lang['noa'], '1' => $lang['yesa']), 'value' => pluginGetVariable('lastcomments', 'sidepanel')));
array_push($cfgX, array('name' => 'number', 'title' => $lang['lastcomments:number.title'], 'descr' => $lang['lastcomments:number.descr'], 'type' => 'input', 'html_flags' => 'size=5', 'value' => pluginGetVariable('lastcomments', 'number')));
array_push($cfgX, array('name' => 'comm_length', 'title' => $lang['lastcomments:comm_length.title'], 'descr' => $lang['lastcomments:comm_length.descr'], 'type' => 'input', 'html_flags' => 'size=5', 'value' => pluginGetVariable('lastcomments', 'comm_length')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['lastcomments:group.sidepanel'] . '</b>', 'entries' => $cfgX));

// --- Plugin page ---
$cfgX = array();
array_push($cfgX, array('name' => 'ppage', 'title' => $lang['lastcomments:ppage.title'], 'descr' => $lang['lastcomments:ppage.descr'], 'type' => 'select', 'values' => array('0' => $lang['noa'], '1' => $lang['yesa']), 'value' => pluginGetVariable('lastcomments', 'ppage')));
array_push($cfgX, array('name' => 'pp_number', 'title' => $lang['lastcomments:pp_number.title'], 'descr' => $lang['lastcomments:pp_number.descr'], 'type' => 'input', 'html_flags' => 'size=5', 'value' => pluginGetVariable('lastcomments', 'pp_number')));
array_push($cfgX, array('name' => 'pp_comm_length', 'title' => $lang['lastcomments:pp_comm_length.title'], 'descr' => $lang['lastcomments:pp_comm_length.descr'], 'type' => 'input', 'html_flags' => 'size=5', 'value' => pluginGetVariable('lastcomments', 'pp_comm_length')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['lastcomments:group.ppage'] . '</b>', 'entries' => $cfgX));

// --- RSS feed ---
$cfgX = array();
array_push($cfgX, array('name' => 'rssfeed', 'title' => $lang['lastcomments:rssfeed.title'], 'descr' => $lang['lastcomments:rssfeed.descr'], 'type' => 'select', 'values' => array('0' => $lang['noa'], '1' => $lang['yesa']), 'value' => pluginGetVariable('lastcomments', 'rssfeed')));
array_push($cfgX, array('name' => 'rss_number', 'title' => $lang['lastcomments:rss_number.title'], 'descr' => $lang['lastcomments:rss_number.descr'], 'type' => 'input', 'html_flags' => 'size=5', 'value' => pluginGetVariable('lastcomments', 'rss_number')));
array_push($cfgX, array('name' => 'rss_comm_length', 'title' => $lang['lastcomments:rss_comm_length.title'], 'descr' => $lang['lastcomments:rss_comm_length.descr'], 'type' => 'input', 'html_flags' => 'size=5', 'value' => pluginGetVariable('lastcomments', 'rss_comm_length')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['lastcomments:group.rssfeed'] . '</b>', 'entries' => $cfgX));

// --- Display ---
$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $lang['lastcomments:localsource.title'], 'descr' => $lang['lastcomments:localsource.descr'], 'type' => 'select', 'values' => array('0' => $lang['lastcomments:lsrc.site'], '1' => $lang['lastcomments:lsrc.plugin']), 'value' => intval(pluginGetVariable($plugin, 'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['lastcomments:group.display'] . '</b>', 'entries' => $cfgX));

// --- Cache ---
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $lang['lastcomments:cache.title'], 'descr' => $lang['lastcomments:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['yesa'], '0' => $lang['noa']), 'value' => intval(pluginGetVariable($plugin, 'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => $lang['lastcomments:cacheExpire.title'], 'descr' => $lang['lastcomments:cacheExpire.descr'], 'type' => 'input', 'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? pluginGetVariable($plugin, 'cacheExpire') : '60'));
array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['lastcomments:group.cache'] . '</b>', 'entries' => $cfgX));

// RUN
if ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes('lastcomments', $cfg);
	print_commit_complete('lastcomments');
} else {
	generate_config_page('lastcomments', $cfg);
}
