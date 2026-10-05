<?php
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
global $lang, $plugin;
LoadPluginLang($plugin, 'config', '', '', '#');
$langConfig = $lang[$plugin];
$count = extra_get_param($plugin, 'count');
if ((intval($count) < 1) || (intval($count) > 20))
	$count = 1;
// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => $langConfig['description']));
array_push($cfg, array('name' => 'count', 'title' => $langConfig['count.title'], 'type' => 'input', 'value' => $count));
for ($i = 1; $i <= $count; $i++) {
	$cfgX = array();
	array_push($cfgX, array('name' => 'rss' . $i . '_name', 'title' => $langConfig['rss_name.title'] . '<br /><small>' . $langConfig['rss_name.example'] . '</small>', 'type' => 'input', 'value' => extra_get_param($plugin, 'rss' . $i . '_name')));
	array_push($cfgX, array('name' => 'rss' . $i . '_url', 'title' => $langConfig['rss_url.title'] . '<br /><small>' . $langConfig['rss_url.example'] . '</small>', 'type' => 'input', 'value' => extra_get_param($plugin, 'rss' . $i . '_url')));
	array_push($cfgX, array('name' => 'rss' . $i . '_number', 'title' => $langConfig['rss_number.title'] . '<br /><small>' . $langConfig['rss_number.default'] . '</small>', 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'rss' . $i . '_number')) ? extra_get_param($plugin, 'rss' . $i . '_number') : '10'));
	array_push($cfgX, array('name' => 'rss' . $i . '_maxlength', 'title' => $langConfig['rss_maxlength.title'], 'descr' => $langConfig['rss_maxlength.descr'] . '<br />' . $langConfig['rss_maxlength.default'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'rss' . $i . '_maxlength')) ? extra_get_param($plugin, 'rss' . $i . '_maxlength') : '100'));
	array_push($cfgX, array('name' => 'rss' . $i . '_newslength', 'title' => $langConfig['rss_newslength.title'], 'descr' => $langConfig['rss_newslength.descr'] . '<br />' . $langConfig['rss_newslength.default'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'rss' . $i . '_newslength')) ? extra_get_param($plugin, 'rss' . $i . '_newslength') : '100'));
	array_push($cfgX, array('name' => 'rss' . $i . '_content', 'title' => $langConfig['rss_content.title'], 'type' => 'checkbox', 'value' => extra_get_param($plugin, 'rss' . $i . '_content')));
	array_push($cfgX, array('name' => 'rss' . $i . '_img', 'title' => $langConfig['rss_img.title'], 'type' => 'checkbox', 'value' => extra_get_param($plugin, 'rss' . $i . '_img')));
	array_push($cfgX, array('name' => 'rss' . $i . '_showImage', 'title' => $langConfig['rss_showImage.title'], 'type' => 'checkbox', 'value' => extra_get_param($plugin, 'rss' . $i . '_showImage')));
	array_push($cfgX, array('name' => 'rss' . $i . '_imageSource', 'title' => $langConfig['rss_imageSource.title'], 'type' => 'select', 'values' => array('desc' => $langConfig['rss_imageSource.opt.desc'], 'enclosure' => $langConfig['rss_imageSource.opt.enclosure']), 'value' => (extra_get_param($plugin, 'rss' . $i . '_imageSource') ? extra_get_param($plugin, 'rss' . $i . '_imageSource') : 'enclosure')));
	array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $langConfig['group.block'] . ' <b>' . $i . '</b> {rss' . $i . '}', 'entries' => $cfgX));
}
$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $langConfig['localsource.title'], 'descr' => $langConfig['localsource#desc'], 'type' => 'select', 'values' => array('0' => $langConfig['localsource.opt.site'], '1' => $langConfig['localsource.opt.plugin']), 'value' => intval(extra_get_param($plugin, 'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => $langConfig['group.source'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $langConfig['cache.title'], 'descr' => $langConfig['cache.descr'], 'type' => 'select', 'values' => array('1' => $langConfig['opt.yes'], '0' => $langConfig['opt.no']), 'value' => intval(extra_get_param($plugin, 'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => $langConfig['cacheExpire.title'], 'descr' => $langConfig['cacheExpire.descr'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'cacheExpire')) ? extra_get_param($plugin, 'cacheExpire') : '60'));
array_push($cfg, array('mode' => 'group', 'title' => $langConfig['group.cache'], 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
