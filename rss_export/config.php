<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
LoadPluginLang('rss_export', 'config', '', '', ':');
$xfEnclosureValues = array('' => '');
// Нормализация URL: убираем двойные слеши в пути, сохраняя https://
if (!function_exists('rss_export_normalize_url')) {
	function rss_export_normalize_url($url)
	{
		$parts = @parse_url($url);
		if ($parts === false) {
			return $url;
		}
		$scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
		$user = $parts['user'] ?? '';
		$pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
		$auth = $user ? ($user . $pass . '@') : '';
		$host = $parts['host'] ?? '';
		$port = isset($parts['port']) ? ':' . $parts['port'] : '';
		$path = $parts['path'] ?? '';
		// Схлопываем повторные слеши и гарантируем один ведущий слеш
		$path = '/' . ltrim(preg_replace('#/+#', '/', $path), '/');
		$query = isset($parts['query']) ? ('?' . $parts['query']) : '';
		$fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
		return $scheme . $auth . $host . $port . $path . $query . $fragment;
	}
}
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
$__rss_full_url = rss_export_normalize_url(generatePluginLink('rss_export', '', array(), array(), true, true));
$__rss_cat_url = ($demoCategory != '') ? rss_export_normalize_url(generatePluginLink('rss_export', 'category', array('category' => $demoCategory), array(), true, true)) : '';
// Построим примеры ссылок для обоих режимов: с ЧПУ и без ЧПУ
$baseUrl = '';
if (isset($config['home_url']) && $config['home_url']) {
	$baseUrl = rtrim($config['home_url'], '/');
} else {
	$pr = @parse_url($__rss_full_url);
	if (is_array($pr) && isset($pr['scheme'], $pr['host'])) {
		$baseUrl = $pr['scheme'] . '://' . $pr['host'] . (isset($pr['port']) ? (':' . $pr['port']) : '');
	}
}
$__rss_full_url_seo = ($baseUrl !== '') ? rss_export_normalize_url($baseUrl . '/rss.xml') : $__rss_full_url;
$__rss_full_url_no_seo = ($baseUrl !== '') ? rss_export_normalize_url($baseUrl . '/plugin/rss_export/') : $__rss_full_url;
$__rss_cat_url_seo = ($demoCategory != '' && $baseUrl !== '') ? rss_export_normalize_url($baseUrl . '/' . $demoCategory . '.xml') : $__rss_cat_url;
$__rss_cat_url_no_seo = ($demoCategory != '' && $baseUrl !== '') ? rss_export_normalize_url($baseUrl . '/plugin/rss_export/category/?category=' . urlencode($demoCategory)) : $__rss_cat_url;
array_push($cfg, array(
	'descr' =>
	'<b>' . $lang['rss_export:description'] . '</b>' .
		'<br>' . $lang['rss_export:info.full_feed'] .
		'<br/>' . $lang['rss_export:info.with_seo'] . ' <b>' . $__rss_full_url_seo . '</b>' .
		'<br/>' . $lang['rss_export:info.no_seo'] . ' <b>' . $__rss_full_url_no_seo . '</b>' .
		(($demoCategory != '')
			? ('<br/><br/>' . $lang['rss_export:info.cat_feed'] . ' <i>' . $catz[$demoCategory]['name'] . '</i>:' .
				'<br/>' . $lang['rss_export:info.with_seo'] . ' <b>' . $__rss_cat_url_seo . '</b>' .
				'<br/>' . $lang['rss_export:info.no_seo'] . ' <b>' . $__rss_cat_url_no_seo . '</b>')
			: '')
));
array_push($cfgX, array('type' => 'select', 'name' => 'feed_title_format', 'title' => $lang['rss_export:feed_title_format.title'], 'descr' => $lang['rss_export:feed_title_format.descr'], 'values' => array('site' => $lang['rss_export:feed_title_format.opt.site'], 'site_title' => $lang['rss_export:feed_title_format.opt.site_title'], 'handy' => $lang['rss_export:feed_title_format.opt.handy']), 'value' => pluginGetVariable('rss_export', 'feed_title_format')));
array_push($cfgX, array('type' => 'input', 'name' => 'feed_title_value', 'title' => $lang['rss_export:feed_title_value.title'], 'descr' => $lang['rss_export:feed_title_value.descr'], 'html_flags' => 'style="width: 250px;"', 'value' => pluginGetVariable('rss_export', 'feed_title_value')));
array_push($cfgX, array('type' => 'select', 'name' => 'news_title', 'title' => $lang['rss_export:news_title.title'], 'descr' => $lang['rss_export:news_title.descr'], 'values' => array('0' => $lang['rss_export:news_title.opt.name'], '1' => $lang['rss_export:news_title.opt.cat_name']), 'value' => pluginGetVariable('rss_export', 'news_title')));
array_push($cfgX, array('type' => 'input', 'name' => 'news_count', 'title' => $lang['rss_export:news_count.title'], 'value' => pluginGetVariable('rss_export', 'news_count')));
array_push($cfgX, array('type' => 'select', 'name' => 'use_hide', 'title' => $lang['rss_export:use_hide.title'], 'descr' => $lang['rss_export:use_hide.descr'], 'values' => array('0' => $lang['rss_export:opt.no'], '1' => $lang['rss_export:opt.yes']), 'value' => pluginGetVariable('rss_export', 'use_hide')));
array_push($cfgX, array('type' => 'select', 'name' => 'content_show', 'title' => $lang['rss_export:content_show.title'], 'descr' => $lang['rss_export:content_show.descr'], 'values' => array('0' => $lang['rss_export:content_show.opt.both'], '1' => $lang['rss_export:content_show.opt.short'], '2' => $lang['rss_export:content_show.opt.long']), 'value' => pluginGetVariable('rss_export', 'content_show')));
array_push($cfgX, array('type' => 'input', 'name' => 'truncate', 'title' => $lang['rss_export:truncate.title'], 'descr' => $lang['rss_export:truncate.descr'], 'value' => intval(pluginGetVariable('rss_export', 'truncate'))));
array_push($cfgX, array('type' => 'input', 'name' => 'delay', 'title' => $lang['rss_export:delay.title'], 'descr' => $lang['rss_export:delay.descr'], 'value' => pluginGetVariable('rss_export', 'delay')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_export:group.general'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'xfEnclosureEnabled', 'title' => $lang['rss_export:xfEnclosureEnabled.title'], 'descr' => $lang['rss_export:xfEnclosureEnabled.descr'], 'type' => 'select', 'values' => array('1' => $lang['rss_export:opt.yes'], '0' => $lang['rss_export:opt.no']), 'value' => intval(pluginGetVariable($plugin, 'xfEnclosureEnabled'))));
array_push($cfgX, array('name' => 'xfEnclosure', 'title' => $lang['rss_export:xfEnclosure.title'], 'type' => 'select', 'values' => $xfEnclosureValues, 'value' => pluginGetVariable($plugin, 'xfEnclosure')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_export:group.enclosure'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $lang['rss_export:cache.title'], 'descr' => $lang['rss_export:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['rss_export:opt.yes'], '0' => $lang['rss_export:opt.no']), 'value' => intval(pluginGetVariable($plugin, 'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => $lang['rss_export:cacheExpire.title'], 'descr' => $lang['rss_export:cacheExpire.descr'], 'type' => 'input', 'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? pluginGetVariable($plugin, 'cacheExpire') : '60'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_export:group.cache'], 'entries' => $cfgX));
// RUN
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
