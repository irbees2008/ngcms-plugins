<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
LoadPluginLang('rss_yandex', 'config', '', '', ':');
$xfEnclosureValues = array('' => '');
//
// IF plugin 'XFIELDS' is enabled - load it to prepare `enclosure` integration
if (getPluginStatusActive('xfields')) {
	include_once(root . "/plugins/xfields/xfields.php");
	// Load XFields config
	if (is_array($xfc = xf_configLoad())) {
		foreach ($xfc['news'] as $fid => $fdata) {
			$xfEnclosureValues[$fid] = $fid . ' (' . $fdata['title'] . ')';
		}
	}
}
// For example - find 1st category with news for demo URL
$demoCategory = '';
foreach ($catz as $scanCat) {
	if ($scanCat['posts'] > 0) {
		$demoCategory = $scanCat['alt'];
		break;
	}
}
// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => '<b>' . $lang['rss_yandex:description'] . '</b><br>' . $lang['rss_yandex:info.full_feed'] . ' <b>' . generatePluginLink('rss_yandex', '', array(), array(), true, true) . '</b>' . (($demoCategory != '') ? '<br/>' . $lang['rss_yandex:info.cat_feed'] . ' <i>' . $catz[$demoCategory]['name'] . '</i>: ' . generatePluginLink('rss_yandex', 'category', array('category' => $demoCategory), array(), true, true) : '')));
array_push($cfgX, array('type' => 'input', 'name' => 'feed_title', 'title' => $lang['rss_yandex:feed_title.title'], 'descr' => $lang['rss_yandex:feed_title.descr'], 'html_flags' => 'style="width: 250px;"', 'value' => pluginGetVariable('rss_yandex', 'feed_title') ? pluginGetVariable('rss_yandex', 'feed_title') : '%site_title%'));
array_push($cfgX, array('type' => 'text', 'name' => 'news_title', 'title' => $lang['rss_yandex:news_title.title'], 'descr' => $lang['rss_yandex:news_title.descr'], 'html_flags' => 'style="width: 350px;"', 'value' => pluginGetVariable('rss_yandex', 'news_title') ? pluginGetVariable('rss_yandex', 'news_title') : '%cat_title% %news_title%'));
array_push($cfgX, array('type' => 'select', 'name' => 'full_format', 'title' => $lang['rss_yandex:full_format.title'], 'descr' => $lang['rss_yandex:full_format.descr'], 'values' => array('0' => $lang['rss_yandex:full_format.opt.full'], '1' => $lang['rss_yandex:full_format.opt.full_short']), 'value' => pluginGetVariable('rss_yandex', 'full_format')));
array_push($cfgX, array('type' => 'input', 'name' => 'news_age', 'title' => $lang['rss_yandex:news_age.title'], 'descr' => $lang['rss_yandex:news_age.descr'], 'value' => pluginGetVariable('rss_yandex', 'news_age')));
array_push($cfgX, array('type' => 'input', 'name' => 'delay', 'title' => $lang['rss_yandex:delay.title'], 'descr' => $lang['rss_yandex:delay.descr'], 'value' => pluginGetVariable('rss_yandex', 'delay')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_yandex:group.general'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('type' => 'input', 'name' => 'feed_image_title', 'title' => $lang['rss_yandex:feed_image_title.title'], 'html_flags' => 'style="width: 250px;"', 'value' => pluginGetVariable('rss_yandex', 'feed_image_title')));
array_push($cfgX, array('type' => 'input', 'name' => 'feed_image_link', 'title' => $lang['rss_yandex:feed_image_link.title'], 'descr' => $lang['rss_yandex:feed_image_link.descr'], 'html_flags' => 'style="width: 250px;"', 'value' => pluginGetVariable('rss_yandex', 'feed_image_link')));
array_push($cfgX, array('type' => 'input', 'name' => 'feed_image_url', 'title' => $lang['rss_yandex:feed_image_url.title'], 'descr' => $lang['rss_yandex:feed_image_url.descr'], 'html_flags' => 'style="width: 250px;"', 'value' => pluginGetVariable('rss_yandex', 'feed_image_url')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_yandex:group.logo'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'xfEnclosureEnabled', 'title' => $lang['rss_yandex:xfEnclosureEnabled.title'], 'descr' => $lang['rss_yandex:xfEnclosureEnabled.descr'], 'type' => 'select', 'values' => array('1' => $lang['rss_yandex:opt.yes'], '0' => $lang['rss_yandex:opt.no']), 'value' => intval(pluginGetVariable($plugin, 'xfEnclosureEnabled'))));
array_push($cfgX, array('name' => 'xfEnclosure', 'title' => $lang['rss_yandex:xfEnclosure.title'], 'type' => 'select', 'values' => $xfEnclosureValues, 'value' => pluginGetVariable($plugin, 'xfEnclosure')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_yandex:group.enclosure_xf'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'textEnclosureEnabled', 'title' => $lang['rss_yandex:textEnclosureEnabled.title'], 'descr' => $lang['rss_yandex:textEnclosureEnabled.descr'], 'type' => 'select', 'values' => array('1' => $lang['rss_yandex:opt.yes'], '0' => $lang['rss_yandex:opt.no']), 'value' => intval(pluginGetVariable($plugin, 'textEnclosureEnabled'))));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_yandex:group.enclosure_text'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $lang['rss_yandex:cache.title'], 'descr' => $lang['rss_yandex:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['rss_yandex:opt.yes'], '0' => $lang['rss_yandex:opt.no']), 'value' => intval(pluginGetVariable($plugin, 'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => $lang['rss_yandex:cacheExpire.title'], 'descr' => $lang['rss_yandex:cacheExpire.descr'], 'type' => 'input', 'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? pluginGetVariable($plugin, 'cacheExpire') : '60'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_yandex:group.cache'], 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
