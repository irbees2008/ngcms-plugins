<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('simple_title_pro', 'config', '', '', '#');
global $lang;

$db_update = [];

if ($_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page($plugin, $lang['simple_title_pro']['uninstall.confirm'], 'deinstall');
}
