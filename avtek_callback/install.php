<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
loadPluginLang('avtek_callback', 'config', '', '', ':');

global $mysql, $plugin;
if (!isset($plugin) || !$plugin) {
	$plugin = 'avtek_callback';
}

/**
 * IMPORTANT:
 * We keep DATETIME columns nullable to avoid fixdb issues with DEFAULT CURRENT_TIMESTAMP,
 * because your code always provides created_at/updated_at values explicitly.
 */

// --- 1) FIXDB schema (NGCMS-style) ---
$db_update = array(
	// FORMS
	array(
		'table'  => 'avtek_callback_forms',
		'action' => 'cmodify',
		'key'    => 'primary key(id), key(slug)',
		'fields' => array(
			array('action' => 'cmodify', 'name' => 'id',         'type' => 'int',           'params' => 'not null auto_increment'),
			array('action' => 'cmodify', 'name' => 'title',      'type' => 'varchar(190)',  'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'slug',       'type' => 'varchar(190)',  'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'emails',     'type' => 'text'),
			array('action' => 'cmodify', 'name' => 'fields',     'type' => 'longtext'),
			array('action' => 'cmodify', 'name' => 'settings',   'type' => 'longtext'),
			array('action' => 'cmodify', 'name' => 'created_at', 'type' => 'datetime',      'params' => 'null'),
			array('action' => 'cmodify', 'name' => 'updated_at', 'type' => 'datetime',      'params' => 'null'),
		),
	),

	// LEADS
	array(
		'table'  => 'avtek_callback_leads',
		'action' => 'cmodify',
		'key'    => 'primary key(id), key(form_id), key(created_at)',
		'fields' => array(
			array('action' => 'cmodify', 'name' => 'id',            'type' => 'int',          'params' => 'not null auto_increment'),
			array('action' => 'cmodify', 'name' => 'form_id',       'type' => 'int',          'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'created_at',    'type' => 'datetime',     'params' => 'null'),
			array('action' => 'cmodify', 'name' => 'ip',            'type' => 'varchar(64)',  'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'user_agent',    'type' => 'varchar(255)', 'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'referer',       'type' => 'varchar(255)', 'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'page_url',      'type' => 'varchar(255)', 'params' => 'default ""'),
			array('action' => 'cmodify', 'name' => 'data',          'type' => 'longtext'),
			array('action' => 'cmodify', 'name' => 'email_sent',    'type' => 'tinyint',      'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'telegram_sent', 'type' => 'tinyint',      'params' => 'default 0'),
			array('action' => 'cmodify', 'name' => 'crm_response',  'type' => 'longtext'),
		),
	),

	// SETTINGS (no id, PK=name)
	array(
		'table'  => 'avtek_callback_settings',
		'action' => 'cmodify',
		'key'    => 'primary key(name)',
		'fields' => array(
			array('action' => 'cmodify', 'name' => 'name',  'type' => 'varchar(64)', 'params' => 'not null default ""'),
			array('action' => 'cmodify', 'name' => 'value', 'type' => 'longtext'),
		),
	),
);

// --- Fallback create (if fixdb has issues in this build) ---
function avtek_cb_install_create_tables_fallback()
{
	global $mysql;

	if (!is_object($mysql) || !method_exists($mysql, 'query')) {
		return false;
	}

	// Build table names with prefix
	if (!function_exists('avtek_cb_db_table')) {
		// Safety: when helper isn't loaded for some reason, fallback to non-prefixed
		function avtek_cb_db_table($name)
		{
			return $name;
		}
	}
	$tblForms    = avtek_cb_db_table('avtek_callback_forms');
	$tblLeads    = avtek_cb_db_table('avtek_callback_leads');
	$tblSettings = avtek_cb_db_table('avtek_callback_settings');

	$sqlForms = "CREATE TABLE IF NOT EXISTS `" . $tblForms . "` (
		`id` int NOT NULL AUTO_INCREMENT,
		`title` varchar(190) NOT NULL DEFAULT '',
		`slug` varchar(190) NOT NULL DEFAULT '',
		`emails` text,
		`fields` longtext,
		`settings` longtext,
		`created_at` datetime NULL,
		`updated_at` datetime NULL,
		PRIMARY KEY (`id`),
		KEY `slug` (`slug`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

	$sqlLeads = "CREATE TABLE IF NOT EXISTS `" . $tblLeads . "` (
		`id` int NOT NULL AUTO_INCREMENT,
		`form_id` int NOT NULL DEFAULT 0,
		`created_at` datetime NULL,
		`ip` varchar(64) NOT NULL DEFAULT '',
		`user_agent` varchar(255) NOT NULL DEFAULT '',
		`referer` varchar(255) NOT NULL DEFAULT '',
		`page_url` varchar(255) NOT NULL DEFAULT '',
		`data` longtext,
		`email_sent` tinyint NOT NULL DEFAULT 0,
		`telegram_sent` tinyint NOT NULL DEFAULT 0,
		`crm_response` longtext,
		PRIMARY KEY (`id`),
		KEY `form_id` (`form_id`),
		KEY `created_at` (`created_at`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

	$sqlSettings = "CREATE TABLE IF NOT EXISTS `" . $tblSettings . "` (
		`name` varchar(64) NOT NULL DEFAULT '',
		`value` longtext,
		PRIMARY KEY (`name`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

	$r1 = $mysql->query($sqlForms);
	$r2 = $mysql->query($sqlLeads);
	$r3 = $mysql->query($sqlSettings);

	return ($r1 !== false) && ($r2 !== false) && ($r3 !== false);
}

// --- Install flow like basket ---
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {

	$ok = false;

	if (function_exists('fixdb_plugin_install')) {
		$ok = fixdb_plugin_install($plugin, $db_update);
	}

	if (!$ok) {
		$ok = avtek_cb_install_create_tables_fallback();
	}

	if ($ok) {
		plugin_mark_installed($plugin);
	}
} else {
	$text = 'Установка плагина AVTEK Callback: будут созданы/обновлены таблицы forms/leads/settings.';
	generate_install_page($plugin, $text);
}
