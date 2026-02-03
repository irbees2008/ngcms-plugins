<?php

use function Plugins\{array_get, logger, get_ip};

pluginsLoadConfig();
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => 'Плагин позволяет использовать сервис Akismet для фильтрации спама в комментариях'));
array_push($cfgX, array('name' => 'akismet_server', 'title' => "API-сервер", 'type' => 'input', 'value' => extra_get_param($plugin, 'akismet_server') ? extra_get_param($plugin, 'akismet_server') : 'rest.akismet.com'));
array_push($cfgX, array('name' => 'akismet_apikey', 'title' => "API-ключ", 'type' => 'input', 'value' => extra_get_param($plugin, 'akismet_apikey')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Настройки</b>', 'entries' => $cfgX));
if (array_get($_REQUEST, 'action', '') == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	logger('Akismet config saved, IP=' . get_ip(), 'info', 'comments_akismet.log');
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
