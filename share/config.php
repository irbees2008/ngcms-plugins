<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('share', 'config', '', '', ':');

// Prepare list of available skins
$skList = array();
$skinDir = __DIR__ . '/tpl/site';
if (is_dir($skinDir) && $handle = opendir($skinDir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != '.' && $entry != '..' && is_dir($skinDir . '/' . $entry)) {
            $skList[$entry] = $entry;
        }
    }
    closedir($handle);
}

// Add default skin if list is empty
if (empty($skList)) {
    $skList['basic'] = 'basic';
}

// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => $lang['share:description']));

$cfgX = array();
$currentSkin = pluginGetVariable('share', 'skin');
if (empty($currentSkin)) {
    $currentSkin = 'basic';
}
$currentLocalsource = intval(pluginGetVariable('share', 'localsource'));

array_push($cfgX, array(
    'name' => 'localsource',
    'title' => $lang['share:localsource'],
    'descr' => $lang['share:localsource#desc'],
    'type' => 'select',
    'values' => array(
        '1' => $lang['share:localsource.plugin'],
        '0' => $lang['share:localsource.theme']
    ),
    'value' => $currentLocalsource,
));
array_push($cfgX, array(
    'name' => 'skin',
    'title' => $lang['share:skin'],
    'descr' => $lang['share:skin#desc'],
    'type' => 'select',
    'values' => $skList,
    'value' => $currentSkin,
));
array_push($cfg, array(
    'mode' => 'group',
    'title' => '<b>' . $lang['share:group.source'] . '</b>',
    'entries' => $cfgX,
));

// Социальные сети
$cfgNetworks = array();
array_push($cfgNetworks, array(
    'name' => 'networks',
    'title' => $lang['share:group.networks'],
    'descr' => $lang['share:networks#desc'],
    'type' => 'text',
    'html_flags' => 'style="display:none;"',
    'value' => '',
));

$networks = array(
    'facebook' => $lang['share:network.facebook'],
    'x' => $lang['share:network.x'],
    'vk' => $lang['share:network.vk'],
    'ok' => $lang['share:network.ok'],
    'telegram' => $lang['share:network.telegram'],
    'whatsapp' => $lang['share:network.whatsapp'],
    'viber' => $lang['share:network.viber'],
    'mailru' => $lang['share:network.mailru'],
    'linkedin' => $lang['share:network.linkedin'],
    'pinterest' => $lang['share:network.pinterest'],
    'reddit' => $lang['share:network.reddit'],
    'instagram' => $lang['share:network.instagram'],
    'threads' => $lang['share:network.threads'],
    'print' => $lang['share:network.print'],
);

foreach ($networks as $key => $title) {
    array_push($cfgNetworks, array(
        'name' => 'network_' . $key,
        'title' => $title,
        'type' => 'select',
        'values' => array('1' => 'Да', '0' => 'Нет'),
        'value' => intval(pluginGetVariable('share', 'network_' . $key)) ?: '1',
    ));
}

array_push($cfg, array(
    'mode' => 'group',
    'title' => '<b>' . $lang['share:group.networks'] . '</b>',
    'entries' => $cfgNetworks,
));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes('share', $cfg);
    print_commit_complete('share');
} else {
    generate_config_page('share', $cfg);
}
