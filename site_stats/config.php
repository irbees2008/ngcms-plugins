<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Load lang files
LoadPluginLang($plugin, 'config', '', '', ':');

// Prepare configuration parameters
if(empty(pluginGetVariable($plugin, 'site_exists'))) {
    if (is_array($row = $mysql->select("SELECT reg FROM ".prefix."_users WHERE id = '1' limit 1"))) {
        $dReg = date('d.m.Y', $row[0]['reg']);
    } else {
        $dReg = date('d.m.Y', time());
    }
}

// Fill configuration parameters
$cfg = array();
array_push($cfg, array(
    'descr' => $lang[$plugin.':description'],
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'outNW',
        'title' => $lang[$plugin.':outNW.title'],
        'descr' => $lang[$plugin.':outNW.descr'],
        'type' => 'select',
        'values' => array ( '0' => $lang[$plugin.':outNW.val.numbers'], '1' => $lang[$plugin.':outNW.val.words']),
        'value' => pluginGetVariable($plugin, 'outNW'),
        ));
    array_push($cfgX, array(
        'name' => 'site_exists',
        'title' => $lang[$plugin.':site_exists.title'],
        'descr' => $lang[$plugin.':site_exists.descr'],
        'type' => 'input',
        'html_flags' => 'placeholder="'.$dReg.'"',
        'value' => pluginGetVariable($plugin, 'site_exists') ? pluginGetVariable($plugin, 'site_exists') : $dReg,
        ));
array_push($cfg, array(
	'mode' => 'group',
	'title' => $lang[$plugin.':group.config'],
	'entries' => $cfgX,
	));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'static',
        'title' => $lang[$plugin.':static.title'],
        'descr' => $lang[$plugin.':static.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin, 'static'),
        ));
    array_push($cfgX, array(
        'name' => 'category',
        'title' => $lang[$plugin.':category.title'],
        'descr' => $lang[$plugin.':category.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin, 'category'),
        ));
    array_push($cfgX, array(
        'name' => 'news',
        'title' => $lang[$plugin.':news.title'],
        'descr' => $lang[$plugin.':news.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin, 'news'),
        ));
    array_push($cfgX, array(
        'name' => 'news_na',
        'title' => $lang[$plugin.':news_na.title'],
        'descr' => $lang[$plugin.':news_na.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'news_na'),
        ));
    if (getPluginStatusActive('comments')) {
        array_push($cfgX, array(
            'name' => 'comments',
            'title' => $lang[$plugin.':comments.title'],
            'descr' => $lang[$plugin.':comments.descr'],
            'type' => 'checkbox',
            'value' => pluginGetVariable($plugin,'comments'),
            ));
    }
    array_push($cfgX, array(
        'name' => 'images',
        'title' => $lang[$plugin.':images.title'],
        'descr' => $lang[$plugin.':images.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'images'),
        ));
    array_push($cfgX, array(
        'name' => 'files',
        'title' => $lang[$plugin.':files.title'],
        'descr' => $lang[$plugin.':files.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'files'),
        ));
    array_push($cfgX, array(
        'name' => 'users',
        'title' => $lang[$plugin.':users.title'],
        'descr' => $lang[$plugin.':users.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'users'),
        ));
    array_push($cfgX, array(
        'name' => 'users_na',
        'title' => $lang[$plugin.':users_na.title'],
        'descr' => $lang[$plugin.':users_na.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'users_na'),
        ));
    array_push($cfgX, array(
        'name' => 'ipban',
        'title' => $lang[$plugin.':ipban.title'],
        'descr' => $lang[$plugin.':ipban.descr'],
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'ipban'),
        ));
    array_push($cfgX, array(
        'name' => 'cache',
        'title' => $lang[$plugin.':cache'],
        'descr' => $lang[$plugin.':cache#desc'],
        'type' => 'select',
        'values' => array('0' => $lang['noa'], '1' => $lang['yesa']),
        'value' => pluginGetVariable($plugin, 'cache'),
        ));
    array_push($cfgX, array(
        'name' => 'cacheExpire',
        'title' => $lang[$plugin.':cacheExpire'],
        'descr' => $lang[$plugin.':cacheExpire#desc'],
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 86400,
        ));
array_push($cfg,  array(
    'mode' => 'group',
    'title' => $lang[$plugin.':group.stats'],
    'toggle' => '1',
    'toggle.mode' => 'hide',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'last_time',
        'title' => $lang[$plugin.':last_time.title'],
        'descr' => $lang[$plugin.':last_time.descr'],
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'last_time')) ? intval(pluginGetVariable($plugin, 'last_time')) : 500,
        ));
array_push($cfg,  array(
    'mode' => 'group',
    'title' => $lang[$plugin.':group.online'],
    'toggle' => '1',
    'toggle.mode' => 'hide',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'localsource',
        'title' => $lang[$plugin.':localsource'],
        'descr' => $lang[$plugin.':localsource#descr'],
        'type' => 'select',
        'values' => array(
            '0' => $lang[$plugin.':localsource_0'],
            '1' => $lang[$plugin.':localsource_1'],
            ),
        'value' => intval(pluginGetVariable($plugin, 'localsource')) ? intval(pluginGetVariable($plugin, 'localsource')) : 1,
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => $lang[$plugin.':group.source'],
    'entries' => $cfgX,
    ));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
