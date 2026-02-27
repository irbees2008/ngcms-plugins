<?php
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
LoadPluginLang('rss_import', 'config', '', '', ':');
$count = extra_get_param($plugin, 'count');
if ((intval($count) < 1) || (intval($count) > 20))
	$count = 1;
// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => $lang['rss_import:description']));
array_push($cfg, array('name' => 'count', 'title' => $lang['rss_import:count.title'], 'type' => 'input', 'value' => $count));
for ($i = 1; $i <= $count; $i++) {
	$cfgX = array();
	array_push($cfgX, array('name' => 'rss' . $i . '_name', 'title' => $lang['rss_import:rss_name.title'] . '<br /><small>' . $lang['rss_import:rss_name.example'] . '</small>', 'type' => 'input', 'value' => extra_get_param($plugin, 'rss' . $i . '_name')));
	array_push($cfgX, array('name' => 'rss' . $i . '_url', 'title' => $lang['rss_import:rss_url.title'] . '<br /><small>' . $lang['rss_import:rss_url.example'] . '</small>', 'type' => 'input', 'value' => extra_get_param($plugin, 'rss' . $i . '_url')));
	array_push($cfgX, array('name' => 'rss' . $i . '_number', 'title' => $lang['rss_import:rss_number.title'] . '<br /><small>' . $lang['rss_import:rss_number.default'] . '</small>', 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'rss' . $i . '_number')) ? extra_get_param($plugin, 'rss' . $i . '_number') : '10'));
	array_push($cfgX, array('name' => 'rss' . $i . '_maxlength', 'title' => $lang['rss_import:rss_maxlength.title'] . '<br /><small>' . $lang['rss_import:rss_maxlength.descr'] . '<br />' . $lang['rss_import:rss_maxlength.default'] . '</small>', 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'rss' . $i . '_maxlength')) ? extra_get_param($plugin, 'rss' . $i . '_maxlength') : '100'));
	array_push($cfgX, array('name' => 'rss' . $i . '_newslength', 'title' => $lang['rss_import:rss_newslength.title'] . '<br /><small>' . $lang['rss_import:rss_newslength.descr'] . '<br />' . $lang['rss_import:rss_newslength.default'] . '</small>', 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'rss' . $i . '_newslength')) ? extra_get_param($plugin, 'rss' . $i . '_newslength') : '100'));
	array_push($cfgX, array('name' => 'rss' . $i . '_content', 'title' => $lang['rss_import:rss_content.title'], 'type' => 'checkbox', 'value' => extra_get_param($plugin, 'rss' . $i . '_content')));
	array_push($cfgX, array('name' => 'rss' . $i . '_img', 'title' => $lang['rss_import:rss_img.title'], 'type' => 'checkbox', 'value' => extra_get_param($plugin, 'rss' . $i . '_img')));
	array_push($cfgX, array('name' => 'rss' . $i . '_showImage', 'title' => $lang['rss_import:rss_showImage.title'], 'type' => 'checkbox', 'value' => extra_get_param($plugin, 'rss' . $i . '_showImage')));
	array_push($cfgX, array('name' => 'rss' . $i . '_imageSource', 'title' => $lang['rss_import:rss_imageSource.title'], 'type' => 'select', 'values' => array('desc' => $lang['rss_import:rss_imageSource.opt.desc'], 'enclosure' => $lang['rss_import:rss_imageSource.opt.enclosure']), 'value' => (extra_get_param($plugin, 'rss' . $i . '_imageSource') ? extra_get_param($plugin, 'rss' . $i . '_imageSource') : 'enclosure')));
	array_push($cfg, array('mode' => 'group', 'title' => '<b>' . $lang['rss_import:group.block'] . ' ' . $i . '</b> {rss' . $i . '}', 'entries' => $cfgX));
}
$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => $lang['rss_import:localsource.title'], 'descr' => $lang['rss_import:localsource#desc'], 'type' => 'select', 'values' => array('0' => $lang['rss_import:localsource.opt.site'], '1' => $lang['rss_import:localsource.opt.plugin']), 'value' => intval(extra_get_param($plugin, 'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_import:group.source'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $lang['rss_import:cache.title'], 'descr' => $lang['rss_import:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['rss_import:opt.yes'], '0' => $lang['rss_import:opt.no']), 'value' => intval(extra_get_param($plugin, 'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => $lang['rss_import:cacheExpire.title'], 'descr' => $lang['rss_import:cacheExpire.descr'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'cacheExpire')) ? extra_get_param($plugin, 'cacheExpire') : '60'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['rss_import:group.cache'], 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
