<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

loadPluginLang('uprofile_del', 'config', '', '', ':');

if ($_REQUEST['action'] == 'commit') {
	plugin_mark_deinstalled('uprofile_del');
	remove_uprofile_del_urls();
} else {
	generate_install_page('uprofile_del', $lang['uprofile_del:deinstall'], 'deinstall');
}