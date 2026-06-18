<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
pluginsLoadConfig();
loadPluginLang('basket', 'config', '', '', ':');
$db_update = array(
	array(
		'table'  => 'basket',
		'action' => 'cmodify',
		'key'    => 'primary key(id)',
		'fields' => array(
			array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
			array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int', 'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'cookie', 'type' => 'char(50)', 'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'linked_ds', 'type' => 'int', 'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'linked_id', 'type' => 'int', 'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'title', 'type' => 'char(120)', 'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'linked_fld', 'type' => 'text'),
			array('action' => 'cmodify', 'name' => 'price', 'type' => 'decimal(12,2)', 'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'count', 'type' => 'int', 'params' => 'default 0'),
		)
	),
	// Orders table (populated on feedback form submit)
	array(
		'table'  => 'basket_orders',
		'action' => 'cmodify',
		'key'    => 'primary key(id), KEY user_id (user_id), KEY status (status)',
		'fields' => array(
			array('action' => 'cmodify', 'name' => 'id',             'type' => 'int(11)',       'params' => 'NOT NULL AUTO_INCREMENT'),
			array('action' => 'cmodify', 'name' => 'user_id',        'type' => 'int(11)',       'params' => 'NOT NULL DEFAULT 0'),
			array('action' => 'cmodify', 'name' => 'cookie',         'type' => 'char(50)',      'params' => "NOT NULL DEFAULT ''"),
			array('action' => 'cmodify', 'name' => 'uniqid',         'type' => 'char(32)',      'params' => "NOT NULL DEFAULT ''"),
			array('action' => 'cmodify', 'name' => 'total',          'type' => 'decimal(12,2)', 'params' => 'NOT NULL DEFAULT 0'),
			array('action' => 'cmodify', 'name' => 'status',         'type' => 'varchar(20)',   'params' => "NOT NULL DEFAULT 'new'"),
			array('action' => 'cmodify', 'name' => 'payment_method', 'type' => 'varchar(50)',   'params' => "NOT NULL DEFAULT ''"),
			array('action' => 'cmodify', 'name' => 'items_json',     'type' => 'text',          'params' => ''),
			array('action' => 'cmodify', 'name' => 'contact_json',   'type' => 'text',          'params' => ''),
			array('action' => 'cmodify', 'name' => 'created_at',     'type' => 'int(11)',       'params' => 'NOT NULL DEFAULT 0'),
			array('action' => 'cmodify', 'name' => 'paid_at',        'type' => 'int(11)',       'params' => 'NOT NULL DEFAULT 0'),
		)
	),
);
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	if (fixdb_plugin_install('basket', $db_update)) {
		plugin_mark_installed('basket');
	}
} else {
	$text = $lang['basket:desc_install'];
	generate_install_page('basket', $text);
}
