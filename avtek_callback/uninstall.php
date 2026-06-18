<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
loadPluginLang('avtek_callback', 'config', '', '', ':');

global $plugin;
if (!isset($plugin) || !$plugin) {
	$plugin = 'avtek_callback';
}

$db_update = array(
	array('table' => 'avtek_callback_leads', 'action' => 'drop'),
	array('table' => 'avtek_callback_forms', 'action' => 'drop'),
	array('table' => 'avtek_callback_settings', 'action' => 'drop'),
);

// Deinstall
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	$txt = 'Удаление плагина AVTEK Callback: будут удалены таблицы форм и заявок.';
	generate_install_page($plugin, $txt, 'deinstall');
}
