<?php
if (!defined('NGCMS')) die ('HAL');
pluginsLoadConfig();
LoadPluginLang('text_replace', 'config', '', '', ':');
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['text_replace:description']));
$cfgX = array();
array_push($cfgX, array('name' => 'p_count', 'title' => $lang['text_replace:p_count'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'p_count'))));
array_push($cfgX, array('name' => 'c_replace', 'title' => $lang['text_replace:c_replace'], 'type' => 'select', 'values' => array('0' => $lang['text_replace:c_replace.0'], '1' => $lang['text_replace:c_replace.1'], '2' => $lang['text_replace:c_replace.2']), 'value' => intval(extra_get_param($plugin, 'c_replace'))));
array_push($cfgX, array('name' => 'replace', 'title' => $lang['text_replace:replace'], 'type' => 'text', 'html_flags' => 'rows=20 cols=130', 'value' => extra_get_param($plugin, 'replace')));
array_push($cfgX, array('name' => 'str_url', 'title' => $lang['text_replace:str_url'], 'type' => 'input', 'html_flags' => 'size="80"', 'value' => extra_get_param($plugin, 'str_url')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['text_replace:group'], 'entries' => $cfgX));
if ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes('text_replace', $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page('text_replace', $cfg);
}
