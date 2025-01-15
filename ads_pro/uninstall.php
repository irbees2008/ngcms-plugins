<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
if ($_REQUEST['action'] == 'commit') {
	plugin_mark_deinstalled('ads_pro');
    $url = home . "/engine/admin.php?mod=extras";
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$url}");
} else {
	$text = 'Cейчас плагин будет удален';
	generate_install_page('ads_pro', $text, 'deinstall');
}