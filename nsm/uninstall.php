<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
//
// Configuration file for plugin
//
pluginsLoadConfig();
if ($_REQUEST['action'] == 'commit') {
	$ULIB = new urlLibrary();
	$ULIB->loadConfig();
	$ULIB->removeCommand('nsm', '');
	$ULIB->removeCommand('nsm', 'add');
	$ULIB->removeCommand('nsm', 'edit');
	$ULIB->removeCommand('nsm', 'del');
	$ULIB->saveConfig();
	
plugin_mark_deinstalled('nsm');
    $url = home . "/engine/admin.php?mod=extras";
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$url}");
} else {
	$text = 'Cейчас плагин будет удален';
	generate_install_page('nsm', "Удаление NSM", 'deinstall');
}