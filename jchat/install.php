<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Install script for plugin.
// $action: possible action modes
// 	confirm		- screen for installation confirmation
//	apply		- apply installation, with handy confirmation
//	autoapply       - apply installation in automatic mode [INSTALL script]
//
pluginsLoadConfig();
function plugin_jchat_install($action)
{

	global $lang;
	if ($action != 'autoapply')
		loadPluginLang('jchat', 'config', '', '', ':');
	$db_update = array(
		array(
			'table'   => 'jchat',
			'action'  => 'cmodify',
			'charset' => 'utf8mb4',
			'collate' => 'utf8mb4_unicode_ci',
			'key'     => 'primary key(id)',
			'fields'  => array(
				array('action' => 'cmodify', 'name' => 'id',        'type' => 'int',          'params' => 'not null auto_increment'),
				array('action' => 'cmodify', 'name' => 'chatid',    'type' => 'int',          'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'postdate',  'type' => 'int',          'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'author',    'type' => 'varchar(100)', 'params' => 'character set utf8mb4 collate utf8mb4_unicode_ci not null default \'\''),
				array('action' => 'cmodify', 'name' => 'author_id', 'type' => 'int',          'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'status',    'type' => 'int',          'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'ip',        'type' => 'varchar(45)',  'params' => 'not null default \'\''),
				array('action' => 'cmodify', 'name' => 'text',      'type' => 'text',         'params' => 'character set utf8mb4 collate utf8mb4_unicode_ci not null'),
			)
		),
		array(
			'table'  => 'jchat_events',
			'action' => 'cmodify',
			'charset' => 'utf8mb4',
			'collate' => 'utf8mb4_unicode_ci',
			'key'    => 'primary key(id)',
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'id',      'type' => 'int', 'params' => 'not null auto_increment'),
				array('action' => 'cmodify', 'name' => 'chatid',  'type' => 'int', 'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'postdate', 'type' => 'int', 'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'type',    'type' => 'int', 'params' => 'default 0'),
			)
		),
	);
	// Apply requested action
	switch ($action) {
		case 'confirm':
			generate_install_page('jchat', $lang['jchat:desc_install']);
			break;
		case 'autoapply':
		case 'apply':
			if (fixdb_plugin_install('jchat', $db_update, 'install', ($action == 'autoapply') ? true : false)) {
				plugin_mark_installed('jchat');
			}
			// Force-convert existing table to utf8mb4 (handles pre-existing tables with wrong charset)
			global $mysql;
			$mysql->query("ALTER TABLE " . prefix . "_jchat CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
			$mysql->query("ALTER TABLE " . prefix . "_jchat MODIFY COLUMN author  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''");
			$mysql->query("ALTER TABLE " . prefix . "_jchat MODIFY COLUMN text    text         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
			$mysql->query("ALTER TABLE " . prefix . "_jchat MODIFY COLUMN ip      varchar(45)  NOT NULL DEFAULT ''");
			$mysql->query("ALTER TABLE " . prefix . "_jchat_events CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
			// Now we need to set some default params
			$params = array(
				'access'       => 1,
				'rate_limit'   => 0,
				'maxwlen'      => 40,
				'maxlen'       => 500,
				'refresh'      => 30,
				'history'      => 30,
				'maxidle'      => 0,
				'order'        => 0,
				'win.refresh'  => 30,
				'win.history'  => 30,
				'win.maxidle'  => 0,
				'win.order'    => 0,
				'enable.panel' => 1,
				'enable.win'   => 0,
				'localsource'  => 0,
			);
			foreach ($params as $k => $v) {
				extra_set_param('jchat', $k, $v);
			}
			extra_commit_changes();
			break;
	}

	return true;
}
