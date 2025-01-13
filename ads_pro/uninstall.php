<?php
// Защита от попыток взлома
if (!defined('NGCMS')) {
	die('HAL');
}

// Обработка действия удаления
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'commit') {
	plugin_mark_deinstalled('ads_pro');
} else {
	$text = 'Сейчас плагин будет удален';
	generate_install_page('ads_pro', $text, 'deinstall');
}