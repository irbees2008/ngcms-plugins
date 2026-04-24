<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
pluginsLoadConfig();
LoadPluginLang('news_informer', 'config', '', '', ':');
// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['news_informer:description']));
array_push($cfgX, array(
    'name' => 'count',
    'title' => $lang['news_informer:count'],
    'descr' => $lang['news_informer:count#desc'],
    'type' => 'input',
    'value' => intval(pluginGetVariable('news_informer', 'count')) ? pluginGetVariable('news_informer', 'count') : '5'
));
array_push($cfgX, array(
    'name' => 'mode',
    'title' => "Режим работы",
    'descr' => "<b>Автоматически</b> - плагин выводится автоматически<br /><b>TWIG</b> - вывод только через TWIG функцию",
    'type' => 'select',
    'values' => array('0' => 'Автоматически', '1' => 'TWIG'),
    'value' => intval(pluginGetVariable('news_informer', 'mode'))
));
array_push($cfg, array('mode' => 'group', 'title' => $lang['news_informer:group.config'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array(
    'name' => 'localsource',
    'title' => $lang['news_informer:localsource'],
    'descr' => $lang['news_informer:localsource#desc'],
    'type' => 'select',
    'values' => array('0' => 'Шаблон сайта', '1' => 'Плагин'),
    'value' => intval(pluginGetVariable('news_informer', 'localsource'))
));
array_push($cfg, array('mode' => 'group', 'title' => $lang['news_informer:group.source'], 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array(
    'name' => 'cache',
    'title' => $lang['news_informer:cache'],
    'descr' => $lang['news_informer:cache#desc'],
    'type' => 'select',
    'values' => array('1' => 'Да', '0' => 'Нет'),
    'value' => intval(pluginGetVariable('news_informer', 'cache'))
));
array_push($cfgX, array(
    'name' => 'cacheExpire',
    'title' => $lang['news_informer:cacheExpire'],
    'descr' => $lang['news_informer:cacheExpire#desc'],
    'type' => 'input',
    'value' => intval(pluginGetVariable('news_informer', 'cacheExpire')) ? pluginGetVariable('news_informer', 'cacheExpire') : '3600'
));
array_push($cfg, array('mode' => 'group', 'title' => $lang['news_informer:group.cache'], 'entries' => $cfgX));
// Добавляем секцию с кодом для вставки
$cfgX = array();
// Генерируем URL для скрипта
$embedUrl = home . '/engine/?mod=extra-config&plugin=news_informer&action=embed';
array_push($cfgX, array(
    'name' => 'embed_code',
    'title' => "Код для вставки",
    'descr' => '
        <div style="margin-bottom:15px;">
            <h4>JavaScript вариант:</h4>
            <textarea style="width:100%; height:50px; font-family:monospace;" onclick="this.select();"><script src="' . $embedUrl . '"></script></textarea>
            <p>Просто вставьте этот код в HTML страницы, где должен отображаться информер.</p>
        </div>
        <div>
            <h4>Iframe вариант:</h4>
            <textarea style="width:100%; height:50px; font-family:monospace;" onclick="this.select();"><iframe src="' . $embedUrl . '&mode=html" width="300" height="500" frameborder="0" scrolling="auto"></iframe></textarea>
            <p>Используйте этот вариант, если JavaScript недоступен.</p>
        </div>
    ',
    'type' => 'static',
));
array_push($cfg, array('mode' => 'group', 'title' => 'Код для вставки на другие сайты', 'entries' => $cfgX));
// RUN
if ($_REQUEST['action'] == 'commit') {
    // If submit requested, do config save
    commit_plugin_config_changes('news_informer', $cfg);
    print_commit_complete('news_informer');
} else {
    generate_config_page('news_informer', $cfg);
}
