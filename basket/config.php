<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
$plugin = 'basket';
LoadPluginLang($plugin, 'config', '', '', ':');
// Load XFields config
if (!function_exists('xf_configLoad')) {
	print "XFields plugin is not loaded now!";
} else {
	$XFc = xf_configLoad();
	$xfCatList = array('' => $lang['basket:xf.none']);
	foreach ($XFc['news'] as $k => $v) {
		if ($v['type'] == 'images')
			continue;
		$xfCatList[$k] = $k . ' - ' . $v['title'];
	}
	$xfNTableList = array('' => $lang['basket:xf.none']);
	foreach ($XFc['tdata'] as $k => $v) {
		if ($v['type'] == 'images')
			continue;
		$xfNTableList[$k] = $k . ' - ' . $v['title'];
	}
}
// Check if `feedback` plugin is installed
$feedbackFormList = array();
if (getPluginStatusInstalled('feedback')) {
	foreach ($mysql->select("select * from " . prefix . "_feedback order by id", 1) as $frow) {
		$feedbackFormList[$frow['id']] = $frow['id'] . ' - ' . $frow['title'];
	}
	if (!count($feedbackFormList)) {
		$feedbackFormList[0] = $lang['basket:feedback.none'];
	}
} else {
	$feedbackFormList[0] = $lang['basket:feedback.not_installed'];
}
// Fill configuration parameters
$cfg = array();
$boolValues = array(0 => $lang['basket:bool.no'], 1 => $lang['basket:bool.yes']);
$activationValues = array(0 => $lang['basket:mode.all'], 1 => $lang['basket:mode.xfields']);
array_push($cfg, array('descr' => $lang['basket:description']));
/*
$cfgX = array();
array_push($cfgX, array('name' => 'catalog_flag', 'type' => 'select', 'title' => 'Включить корзину для элементов каталога', 'descr' => '<b>Да</b> - корзина будет активна для элементов каталога<br/><b>Нет</b> - корзина не будет активна для элементов каталога', 'values' => array ( 0 => 'Нет', 1 => 'Да'), 'value' => pluginGetVariable('basket','catalog_flag')));
array_push($cfgX, array('name' => 'catalog_activated', 'title' => "Активация корзины в каталоге по..", 'type' => 'select', 'descr' => '<b>Всем записям</b> - "положить в корзину" будет доступно для всех элементов<br/><b>Полю <i>xfields</i></b> - "положить в корзину" можно будет только те записи, в которых значение указанного поля <b>> 0</b> (больше нуля)', 'values' => array(0 => 'Всем записям', 1 => 'Полю xfields'), 'value' => pluginGetVariable('basket','catalog_activated')));
array_push($cfgX, array('name' => 'catalog_xfield', 'title' => "Поле xfields", 'type' => 'select', 'descr' => 'Поле для параметра "активация корзины по.."', 'values' => $xfCatList, 'value' => pluginGetVariable('basket','catalog_xfield')));
array_push($cfgX, array('name' => 'catalog_price', 'title' => "Поле с ценой", 'type' => 'select', 'descr' => 'Поле xfields с ценой товара', 'values' => $xfCatList, 'value' => pluginGetVariable('basket','catalog_price')));
array_push($cfgX, array('name' => 'catalog_itemname', 'type' => 'input', 'title' => 'Формат заголовка наименования товара:', 'descr' => 'Доступные переменные:<br/><b>{title}</b> - наименование элемента каталога<br/><b>{x:NAME}</b> (где <b>NAME</b> - название поля XFIELDS) - вывести доп. поле', 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable('basket','catalog_itemname')?pluginGetVariable('basket','catalog_itemname'):'{title}'));
array_push($cfg,  array('mode' => 'group', 'title' => '<b>Работа с каталогом</b>', 'entries' => $cfgX));
*/
$cfgX = array();
array_push($cfgX, array('name' => 'ntable_flag', 'type' => 'select', 'title' => $lang['basket:ntable_flag'], 'descr' => $lang['basket:ntable_flag#desc'], 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'ntable_flag')));
array_push($cfgX, array('name' => 'ntable_activated', 'title' => $lang['basket:ntable_activated'], 'type' => 'select', 'descr' => $lang['basket:ntable_activated#desc'], 'values' => $activationValues, 'value' => pluginGetVariable($plugin, 'ntable_activated')));
array_push($cfgX, array('name' => 'ntable_xfield', 'title' => $lang['basket:ntable_xfield'], 'type' => 'select', 'descr' => $lang['basket:ntable_xfield#desc'], 'values' => $xfNTableList, 'value' => pluginGetVariable($plugin, 'ntable_xfield')));
array_push($cfgX, array('name' => 'ntable_price', 'title' => $lang['basket:ntable_price'], 'type' => 'select', 'descr' => $lang['basket:ntable_price#desc'], 'values' => $xfNTableList, 'value' => pluginGetVariable($plugin, 'ntable_price')));
array_push($cfgX, array('name' => 'ntable_itemname', 'type' => 'input', 'title' => $lang['basket:ntable_itemname'], 'descr' => $lang['basket:ntable_itemname#desc'], 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable($plugin, 'ntable_itemname') ? pluginGetVariable($plugin, 'ntable_itemname') : '{title}'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['basket:group.ntable'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'news_flag', 'type' => 'select', 'title' => $lang['basket:news_flag'], 'descr' => $lang['basket:news_flag#desc'], 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'news_flag')));
array_push($cfgX, array('name' => 'news_activated', 'title' => $lang['basket:news_activated'], 'type' => 'select', 'descr' => $lang['basket:news_activated#desc'], 'values' => $activationValues, 'value' => pluginGetVariable($plugin, 'news_activated')));
array_push($cfgX, array('name' => 'news_xfield', 'title' => $lang['basket:news_xfield'], 'type' => 'select', 'descr' => $lang['basket:news_xfield#desc'], 'values' => $xfCatList, 'value' => pluginGetVariable($plugin, 'news_xfield')));
array_push($cfgX, array('name' => 'news_price', 'title' => $lang['basket:news_price'], 'type' => 'select', 'descr' => $lang['basket:news_price#desc'], 'values' => $xfCatList, 'value' => pluginGetVariable($plugin, 'news_price')));
array_push($cfgX, array('name' => 'news_itemname', 'type' => 'input', 'title' => $lang['basket:news_itemname'], 'descr' => $lang['basket:news_itemname#desc'], 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable($plugin, 'ntable_itemname') ? pluginGetVariable($plugin, 'news_itemname') : '{title}'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['basket:group.news'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'feedback_form', 'type' => 'select', 'title' => $lang['basket:feedback_form'], 'descr' => $lang['basket:feedback_form#desc'], 'values' => $feedbackFormList, 'value' => pluginGetVariable($plugin, 'feedback_form')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['basket:group.integration'], 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
