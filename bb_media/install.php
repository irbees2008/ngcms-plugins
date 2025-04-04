<?php
# protect against hack attempts
if (!defined('NGCMS')) die('HAL');
function plugin_bb_media_install($action)
{

	switch ($action) {
		case 'confirm':
			generate_install_page('bb_media', 'GoGoGo!!');
			break;
		case 'autoapply':
		case 'apply':
			$params = array(
				'player_name' => 'jwplayer',
			);
			foreach ($params as $k => $v) {
				pluginSetVariable('bb_media', $k, $v);
			}
			pluginsSaveConfig();
			plugin_mark_installed('bb_media');
			$url = home . "/engine/admin.php?mod=extras";
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: {$url}");
			break;
	}

	return true;
}
