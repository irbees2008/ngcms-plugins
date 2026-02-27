<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('Galaxy in danger');
// Preload config file
pluginsLoadConfig();
loadPluginLang('neighboring_news', 'config', '', '', ':');
// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => $lang['neighboring_news:plugin.descr']));
array_push($cfg, array('name' => 'full_mode',   'title' => $lang['neighboring_news:full_mode.title'],  'type' => 'checkbox', 'value' => extra_get_param('neighboring_news', 'full_mode')));
array_push($cfg, array('name' => 'short_mode',  'title' => $lang['neighboring_news:short_mode.title'], 'descr' => $lang['neighboring_news:short_mode.descr'], 'type' => 'checkbox', 'value' => extra_get_param('neighboring_news', 'short_mode')));
array_push($cfg, array('name' => 'compare',     'title' => $lang['neighboring_news:compare.title'],    'type' => 'select', 'values' => array('1' => $lang['neighboring_news:compare.opt1'], '2' => $lang['neighboring_news:compare.opt2']), 'value' => intval(extra_get_param('neighboring_news', 'compare'))));
array_push($cfg, array('name' => 'localsource', 'title' => $lang['neighboring_news:localsource.title'], 'descr' => $lang['neighboring_news:localsource.descr'], 'type' => 'select', 'values' => array('0' => $lang['neighboring_news:lsrc.site'], '1' => $lang['neighboring_news:lsrc.plugin']), 'value' => intval(extra_get_param('neighboring_news', 'localsource'))));
// RUN
if ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes('neighboring_news', $cfg);
	print_commit_complete('neighboring_news');
} else {
	generate_config_page('neighboring_news', $cfg);
}
