<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
//
// Configuration file for plugin
//
// Preload config file
$plugin = 'gsmg';
pluginsLoadConfig();
LoadPluginLang($plugin, 'config', '', $plugin, ':');
// Fill configuration parameters
$cfg = array();
$cfgX = array();
$pl = generatePluginLink($plugin, null, [], [], false, true);
$yesNoValues = array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']);
$cfg[] = array('descr' => str_replace('{link}', $pl, $lang['gsmg:description']));
$cfgX[] = array('name' => 'main', 'title' => $lang['gsmg:main.title'], 'descr' => $lang['gsmg:main.descr'], 'type' => 'select', 'values' => $yesNoValues, 'value' => intval(extra_get_param($plugin, 'main')));
$cfgX[] = array('name' => 'main_pr', 'title' => $lang['gsmg:main_pr.title'], 'descr' => $lang['gsmg:main_pr.descr'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'main_pr') == '') ? '1.0' : extra_get_param($plugin, 'main_pr'));
$cfgX[] = array('name' => 'mainp', 'title' => $lang['gsmg:mainp.title'], 'descr' => $lang['gsmg:mainp.descr'], 'type' => 'select', 'values' => $yesNoValues, 'value' => intval(extra_get_param($plugin, 'mainp')));
$cfgX[] = array('name' => 'mainp_pr', 'title' => $lang['gsmg:mainp_pr.title'], 'descr' => $lang['gsmg:mainp_pr.descr'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'mainp_pr') == '') ? '0.5' : extra_get_param($plugin, 'mainp_pr'));
$cfg[] = array('mode' => 'group', 'title' => $lang['gsmg:group.main'], 'entries' => $cfgX);
$cfgX = array();
$cfgX[] = array('name' => 'cat', 'title' => $lang['gsmg:cat.title'], 'type' => 'select', 'values' => $yesNoValues, 'value' => intval(extra_get_param($plugin, 'cat')));
$cfgX[] = array('name' => 'cat_pr', 'title' => $lang['gsmg:cat_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'cat_pr') == '') ? '0.5' : extra_get_param($plugin, 'cat_pr'));
$cfgX[] = array('name' => 'catp', 'title' => $lang['gsmg:catp.title'], 'type' => 'select', 'values' => $yesNoValues, 'value' => intval(extra_get_param($plugin, 'catp')));
$cfgX[] = array('name' => 'catp_pr', 'title' => $lang['gsmg:catp_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'catp_pr') == '') ? '0.5' : extra_get_param($plugin, 'catp_pr'));
$cfg[] = array('mode' => 'group', 'title' => $lang['gsmg:group.cat'], 'entries' => $cfgX);
$cfgX = array();
$cfgX[] = array('name' => 'news', 'title' => $lang['gsmg:news.title'], 'type' => 'select', 'values' => $yesNoValues, 'value' => intval(extra_get_param($plugin, 'news')));
$cfgX[] = array('name' => 'news_pr', 'title' => $lang['gsmg:news_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'news_pr') == '') ? '0.3' : extra_get_param($plugin, 'news_pr'));
$cfg[] = array('mode' => 'group', 'title' => $lang['gsmg:group.news'], 'entries' => $cfgX);
$cfgX = array();
$cfgX[] = array('name' => 'static', 'title' => $lang['gsmg:static.title'], 'type' => 'select', 'values' => $yesNoValues, 'value' => intval(extra_get_param($plugin, 'static')));
$cfgX[] = array('name' => 'static_pr', 'title' => $lang['gsmg:static_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'static_pr') == '') ? '0.3' : extra_get_param($plugin, 'static_pr'));
$cfg[] = array('mode' => 'group', 'title' => $lang['gsmg:group.static'], 'entries' => $cfgX);
$cfgX = array();
$cfgX[] = array('name' => 'cache', 'title' => $lang['gsmg:cache.title'], 'descr' => $lang['gsmg:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['gsmg:option.yes'], '0' => $lang['gsmg:option.no']), 'value' => intval(extra_get_param($plugin, 'cache')));
$cfgX[] = array('name' => 'cacheExpire', 'title' => $lang['gsmg:cacheExpire.title'], 'descr' => $lang['gsmg:cacheExpire.descr'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'cacheExpire')) ? extra_get_param($plugin, 'cacheExpire') : '10800');
$cfg[] = array('mode' => 'group', 'title' => $lang['gsmg:group.cache'], 'entries' => $cfgX);
// RUN
if ($_REQUEST['action'] == 'commit') {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
?>
