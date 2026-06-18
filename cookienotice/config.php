<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
LoadPluginLang('cookienotice', 'config', '', '', ':');
$cfg  = array();
$cfgX = array();
array_push($cfgX, array(
    'name'  => 'title',
    'title' => $lang['cookienotice:title'],
    'descr' => $lang['cookienotice:title#desc'],
    'type'  => 'input',
    'value' => pluginGetVariable('cookienotice', 'title') ?: 'Использование файлов Cookie'
));
array_push($cfgX, array(
    'name'  => 'text',
    'title' => $lang['cookienotice:text'],
    'descr' => $lang['cookienotice:text#desc'],
    'type'  => 'text',
    'value' => pluginGetVariable('cookienotice', 'text') ?: 'Мы используем файлы cookie для улучшения работы сайта, анализа трафика и персонализации контента. Продолжая пользоваться сайтом, вы соглашаетесь с нашей <a href="/privacy">политикой конфиденциальности</a>.'
));
array_push($cfgX, array(
    'name'  => 'btn_ok',
    'title' => $lang['cookienotice:btn_ok'],
    'descr' => $lang['cookienotice:btn_ok#desc'],
    'type'  => 'input',
    'value' => pluginGetVariable('cookienotice', 'btn_ok') ?: 'Принять'
));
array_push($cfgX, array(
    'name'  => 'cookie_days',
    'title' => $lang['cookienotice:cookie_days'],
    'descr' => $lang['cookienotice:cookie_days#desc'],
    'type'  => 'input',
    'value' => pluginGetVariable('cookienotice', 'cookie_days') ?: '365'
));
array_push($cfgX, array(
    'name'   => 'position',
    'title'  => $lang['cookienotice:position'],
    'descr'  => $lang['cookienotice:position#desc'],
    'type'   => 'select',
    'value'  => pluginGetVariable('cookienotice', 'position') ?: 'bottom',
    'values' => array(
        'bottom' => $lang['cookienotice:position.bottom'],
        'top'    => $lang['cookienotice:position.top'],
        'center' => $lang['cookienotice:position.center'],
    )
));
array_push($cfgX, array(
    'name'  => 'only_guests',
    'title' => $lang['cookienotice:only_guests'],
    'descr' => $lang['cookienotice:only_guests#desc'],
    'type'  => 'checkbox',
    'value' => (pluginGetVariable('cookienotice', 'only_guests') === null) ? '1' : pluginGetVariable('cookienotice', 'only_guests')
));
array_push($cfg, array(
    'mode'    => 'group',
    'title'   => $lang['cookienotice:group.config'],
    'entries' => $cfgX
));
if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('cookienotice', $cfg);
    print_commit_complete('cookienotice');
} else {
    generate_config_page('cookienotice', $cfg);
}
