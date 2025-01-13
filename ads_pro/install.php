<?php
if (!defined('NGCMS')) {
	die('HAL');
}

function plugin_ads_pro_install($action)
{
	$db_create = [
		[
			'table'  => 'ads_pro',
			'action' => 'cmodify',
			'key'    => 'PRIMARY KEY (id)',
			'fields' => [
				['action' => 'cmodify', 'name' => 'id', 'type' => 'INT(11)', 'params' => 'UNSIGNED NOT NULL AUTO_INCREMENT'],
				['action' => 'cmodify', 'name' => 'ads_blok', 'type' => 'TEXT', 'params' => 'NOT NULL'],
			],
		],
	];

	switch ($action) {
		case 'confirm':
			generate_install_page('ads_pro', 'Сейчас плагин будет установлен');
			break;

		case 'autoapply':
		case 'apply':
			$autoApply = ($action === 'autoapply');
			if (fixdb_plugin_install('ads_pro', $db_create, 'install', $autoApply)) {
				plugin_mark_installed('ads_pro');
			} else {
				return false;
			}
			break;

		default:
			return false; // Обработка неизвестного действия
	}

	return true;
}