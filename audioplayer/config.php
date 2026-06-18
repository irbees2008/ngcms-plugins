<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('audioplayer', 'config', '', '', ':');

$cfg  = array();
$cfgX = array();

array_push($cfgX, array(
    'name'  => 'title',
    'title' => $lang['audioplayer:title'],
    'descr' => $lang['audioplayer:title#desc'],
    'type'  => 'input',
    'value' => pluginGetVariable('audioplayer', 'title') ?: 'Музыкальный плеер'
));

array_push($cfgX, array(
    'name'  => 'folder',
    'title' => $lang['audioplayer:folder'],
    'descr' => $lang['audioplayer:folder#desc'],
    'type'  => 'input',
    'value' => pluginGetVariable('audioplayer', 'folder') ?: 'files/music'
));

array_push($cfgX, array(
    'name'   => 'skin',
    'title'  => $lang['audioplayer:skin'],
    'descr'  => $lang['audioplayer:skin#desc'],
    'type'   => 'select',
    'value'  => pluginGetVariable('audioplayer', 'skin') ?: 'dark',
    'values' => array(
        'dark'  => $lang['audioplayer:skin.dark'],
        'light' => $lang['audioplayer:skin.light'],
        'blue'  => $lang['audioplayer:skin.blue'],
    )
));

array_push($cfgX, array(
    'name'  => 'autoplay',
    'title' => $lang['audioplayer:autoplay'],
    'descr' => $lang['audioplayer:autoplay#desc'],
    'type'  => 'checkbox',
    'value' => (pluginGetVariable('audioplayer', 'autoplay') === null) ? '0' : pluginGetVariable('audioplayer', 'autoplay')
));

array_push($cfgX, array(
    'name'  => 'show_list',
    'title' => $lang['audioplayer:show_list'],
    'descr' => $lang['audioplayer:show_list#desc'],
    'type'  => 'checkbox',
    'value' => (pluginGetVariable('audioplayer', 'show_list') === null) ? '1' : pluginGetVariable('audioplayer', 'show_list')
));

array_push($cfgX, array(
    'name'   => 'mode',
    'title'  => $lang['audioplayer:mode'],
    'descr'  => $lang['audioplayer:mode#desc'],
    'type'   => 'select',
    'value'  => pluginGetVariable('audioplayer', 'mode') ?: 'widget',
    'values' => array(
        'widget' => $lang['audioplayer:mode.widget'],
        'popup'  => $lang['audioplayer:mode.popup'],
        'page'   => $lang['audioplayer:mode.page'],
    )
));

array_push($cfg, array(
    'mode'    => 'group',
    'title'   => $lang['audioplayer:group.config'],
    'entries' => $cfgX
));

if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('audioplayer', $cfg);
    print_commit_complete('audioplayer');
} else {
    generate_config_page('audioplayer', $cfg);
}
