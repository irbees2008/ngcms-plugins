<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Fallback for array_get if ng-helpers not available
if (!function_exists('Plugins\\array_get')) {
    function gsmg_array_get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
} else {
    function gsmg_array_get($array, $key, $default = null)
    {
        return \Plugins\array_get($array, $key, $default);
    }
}
//
// Configuration file for plugin
//
// Preload config file
LoadPluginLang('gsmg', 'config', '', '', ':');
pluginsLoadConfig();
// Fill configuration parameters
$cfg = array();
$cfgX = array();
$siteUrl = rtrim(home, '/');
$sitemapLink = $siteUrl . '/gsmg.xml';
$pluginLink = $siteUrl . '/plugin/gsmg/';
$description = str_replace(
    array('{sitemap_link}', '{plugin_link}'),
    array($sitemapLink, $pluginLink),
    $lang['gsmg:description']
);
array_push($cfg, array('descr' => $description));
array_push($cfgX, array('name' => 'main', 'title' => $lang['gsmg:main.title'], 'descr' => $lang['gsmg:main.descr'], 'type' => 'select', 'values' => array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']), 'value' => intval(extra_get_param($plugin, 'main'))));
array_push($cfgX, array('name' => 'main_pr', 'title' => $lang['gsmg:main_pr.title'], 'descr' => $lang['gsmg:main_pr.descr'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'main_pr') == '') ? '1.0' : extra_get_param($plugin, 'main_pr')));
array_push($cfgX, array('name' => 'mainp', 'title' => $lang['gsmg:mainp.title'], 'descr' => $lang['gsmg:mainp.descr'], 'type' => 'select', 'values' => array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']), 'value' => intval(extra_get_param($plugin, 'mainp'))));
array_push($cfgX, array('name' => 'mainp_pr', 'title' => $lang['gsmg:mainp_pr.title'], 'descr' => $lang['gsmg:mainp_pr.descr'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'mainp_pr') == '') ? '0.5' : extra_get_param($plugin, 'mainp_pr')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['gsmg:group.main'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cat', 'title' => $lang['gsmg:cat.title'], 'type' => 'select', 'values' => array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']), 'value' => intval(extra_get_param($plugin, 'cat'))));
array_push($cfgX, array('name' => 'cat_pr', 'title' => $lang['gsmg:cat_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'cat_pr') == '') ? '0.5' : extra_get_param($plugin, 'cat_pr')));
array_push($cfgX, array('name' => 'catp', 'title' => $lang['gsmg:catp.title'], 'type' => 'select', 'values' => array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']), 'value' => intval(extra_get_param($plugin, 'catp'))));
array_push($cfgX, array('name' => 'catp_pr', 'title' => $lang['gsmg:catp_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'catp_pr') == '') ? '0.5' : extra_get_param($plugin, 'catp_pr')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['gsmg:group.cat'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'news', 'title' => $lang['gsmg:news.title'], 'type' => 'select', 'values' => array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']), 'value' => intval(extra_get_param($plugin, 'news'))));
array_push($cfgX, array('name' => 'news_pr', 'title' => $lang['gsmg:news_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'news_pr') == '') ? '0.3' : extra_get_param($plugin, 'news_pr')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['gsmg:group.news'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'static', 'title' => $lang['gsmg:static.title'], 'type' => 'select', 'values' => array('0' => $lang['gsmg:option.no'], '1' => $lang['gsmg:option.yes']), 'value' => intval(extra_get_param($plugin, 'static'))));
array_push($cfgX, array('name' => 'static_pr', 'title' => $lang['gsmg:static_pr.title'], 'type' => 'input', 'value' => (extra_get_param($plugin, 'static_pr') == '') ? '0.3' : extra_get_param($plugin, 'static_pr')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['gsmg:group.static'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => $lang['gsmg:cache.title'], 'descr' => $lang['gsmg:cache.descr'], 'type' => 'select', 'values' => array('1' => $lang['gsmg:option.yes'], '0' => $lang['gsmg:option.no']), 'value' => intval(extra_get_param($plugin, 'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => $lang['gsmg:cacheExpire.title'], 'descr' => $lang['gsmg:cacheExpire.descr'], 'type' => 'input', 'value' => intval(extra_get_param($plugin, 'cacheExpire')) ? extra_get_param($plugin, 'cacheExpire') : '10800'));
array_push($cfg, array('mode' => 'group', 'title' => $lang['gsmg:group.cache'], 'entries' => $cfgX));
// RUN
if (gsmg_array_get($_REQUEST, 'action', '') == 'commit') {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
