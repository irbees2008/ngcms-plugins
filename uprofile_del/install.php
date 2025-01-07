<?php
if (!defined('NGCMS'))
{
	die ('HAL');
}

include_once(root . "/plugins/uprofile_del/lib/common.php");

function plugin_uprofile_del_install($action) {
	global $lang;

	if ($action != 'autoapply')
		loadPluginLang('uprofile_del', 'config', '', '', ':');

	if (!getPluginStatusActive('uprofile')) {
		msg(['type' => 'error', 'text' => $lang['uprofile_del:uprofile_error']]);
		return print_msg( 'warning', $lang['uprofile_del:uprofile_del'], $lang['uprofile_del:uprofile_error'], 'javascript:history.go(-1)' );
	}
	
	switch ($action) {
		case 'confirm':
			generate_install_page('uprofile_del', $lang['uprofile_del:install']);
			break;
		case 'autoapply':
		case 'apply':
			plugin_mark_installed('uprofile_del');
			create_uprofile_del_urls();
			
            extra_commit_changes();
			
			break;
	}

	return true;

}