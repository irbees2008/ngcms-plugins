<?php
if (!defined('NGCMS')) die ('HAL');
pluginsLoadConfig();
LoadPluginLang('wysiwyg', 'config');
$bb_list[] = $lang['wysiwyg:editor.standard'];
$bb_list = array_merge($bb_list, ListFiles(extras_dir . '/wysiwyg/bb_code', ''));
//print '<pre>'.var_export($bb_list).'</pre>';
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['wysiwyg:description']));
array_push($cfgX, array('name' => 'bb_editor', 'title' => $lang['wysiwyg:bb_editor'], 'descr' => $lang['wysiwyg:bb_editor#desc'], 'type' => 'select', 'values' => $bb_list, 'value' => pluginGetVariable($plugin, 'bb_editor')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['wysiwyg:group.general'], 'entries' => $cfgX));
if ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
