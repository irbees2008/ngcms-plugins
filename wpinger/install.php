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
function plugin_wpinger_install($action)
{

	global $lang;
	if ($action != 'autoapply')
		loadPluginLang('wpinger', 'config', '', '', ':');
	// Apply requested action
	switch ($action) {
		case 'confirm':
			generate_install_page('wpinger', $lang['wpinger:install_text']);
			break;
		case 'autoapply':
		case 'apply':
			// Установка параметров по умолчанию
			$params = array(
				'proxy' => 1,
				'urls'  => "http://ping.blogs.yandex.ru/RPC2\nhttp://blogsearch.google.ru/ping/RPC2",
			);

			// Установка параметров через extra_set_param
			foreach ($params as $k => $v) {
				if (!extra_set_param('wpinger', $k, $v)) {
					error_log("Failed to set param: {$k} => {$v}");
					return false;
				}
			}

			// Пометка плагина как установленного
			if (!plugin_mark_installed('wpinger')) {
				error_log("Failed to mark plugin 'wpinger' as installed.");
				return false;
			}

			// Финальные изменения
			extra_commit_changes();
			$url = home . "/engine/admin.php?mod=extras";
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: {$url}");
			break;
	}

	return true;
}
