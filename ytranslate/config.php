<?php
if (!defined('NGCMS')) die('HAL');
// Загружаем языковой файл
LoadPluginLang('ytranslate', 'config', '', 'ytranslate', ':');
pluginsLoadConfig();
;
$cfg = array();
$cfgX = array();
array_push($cfgX, array(
    'name' => 'default_lang',
    'title' => $lang['ytranslate:default_lang_title'],
    'descr' => $lang['ytranslate:default_lang_descr'],
    'type' => 'select',
    'values' => array(
        'ru' => $lang['ytranslate:language_russian'],
        'en' => $lang['ytranslate:language_english'],
        'de' => $lang['ytranslate:language_german'],
        'fr' => $lang['ytranslate:language_french'],
        'es' => $lang['ytranslate:language_spanish'],
        'uk' => $lang['ytranslate:language_ukrainian'],
        'kk' => $lang['ytranslate:language_kazakh']
        ),
    'value' => pluginGetVariable('ytranslate', 'default_lang') ?: 'ru'
));
array_push($cfgX, array(
    'name' => 'localsource',
    'title' => $lang['ytranslate:localsource_title'],
    'descr' => $lang['ytranslate:localsource_descr'],
    'type' => 'select',
    'values' => array(
        '0' => $lang['ytranslate:localsource_0'],
        '1' => $lang['ytranslate:localsource_1']
    ),
    'value' => pluginGetVariable('ytranslate', 'localsource')
));
array_push($cfg, array('mode' => 'group', 'title' => $lang['ytranslate:group_title'], 'entries' => $cfgX));
if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('ytranslate', $cfg);
    print_commit_complete('ytranslate');
} else {
    generate_config_page('ytranslate', $cfg);
}
