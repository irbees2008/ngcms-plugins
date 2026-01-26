<?php
if (!defined('NGCMS')) {
	die('HAL');
}

include_once(root . "/plugins/uprofile_del/lib/common.php");

function plugin_uprofile_del_install($action)
{
	global $lang;

	if ($action != 'autoapply')
		loadPluginLang('uprofile_del', 'config', '', '', ':');

	if (!getPluginStatusActive('uprofile')) {
		msg(['type' => 'error', 'text' => $lang['uprofile_del:uprofile_del'], 'info' =>  $lang['uprofile_del:uprofile_error']]);
	}

	$db_update = array(
		array(
			'table'  => 'users',
			'action' => 'cmodify',
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'user_act', 'type' => 'int(6)', 'params' => "DEFAULT '0'"),
				array('action' => 'cmodify', 'name' => 'user_del_date', 'type' => 'int(11)', 'params' => "DEFAULT '0'"),
			)
		),
	);

	switch ($action) {
		case 'confirm':
			generate_install_page('uprofile_del', $lang['uprofile_del:install']);
			break;
		case 'autoapply':
		case 'apply':
			if (fixdb_plugin_install('uprofile_del', $db_update, 'install', ($action == 'autoapply') ? true : false)) {
				plugin_mark_installed('uprofile_del');
				create_uprofile_del_urls();
			} else {
				return false;
			}

			extra_commit_changes();

			break;
	}

	return true;
}
