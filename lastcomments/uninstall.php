<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
$ULIB = new urlLibrary();
$ULIB->loadConfig();
$ULIB->removeCommand('lastcomments', '');
$ULIB->removeCommand('lastcomments', 'rss');
$db_update = [];
if ($_REQUEST['action'] == 'commit') {
	$ULIB->saveConfig();
	if (fixdb_plugin_install('lastcomments', $db_update, 'deinstall')) {
		plugin_mark_deinstalled('lastcomments');
	}
} else {
	generate_install_page('lastcomments', 'Будут удалены URL команды плагина', 'deinstall');
}
