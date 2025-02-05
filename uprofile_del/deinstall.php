<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

loadPluginLang('uprofile_del', 'config', '', '', ':');

include_once(root . "/plugins/uprofile_del/lib/common.php");

$db_update = array(
	array(
		'table'  => 'users',
		'action' => 'modify',
		'fields' => array(
			array('action' => 'drop', 'name' => 'user_act'),
		)
	)
);

if ($_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install('uprofile_del', $db_update, 'deinstall')) {
		plugin_mark_deinstalled('uprofile_del');
	}
	remove_uprofile_del_urls();
} else {
	generate_install_page('uprofile_del', $lang['uprofile_del:deinstall'], 'deinstall');
}