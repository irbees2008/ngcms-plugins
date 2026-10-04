<?php
if (!defined('NGCMS')) die('HAL');
LoadPluginLang('ytranslate', 'config', '', 'ytranslate', ':');
pluginsLoadConfig();

$providers = array(
    'yandex' => $lang['ytranslate:provider_yandex'],
    'google' => $lang['ytranslate:provider_google'],
    'microsoft' => $lang['ytranslate:provider_microsoft'],
    'libretranslate' => $lang['ytranslate:provider_libretranslate'],
    'deepl' => $lang['ytranslate:provider_deepl']
);
$languages = array(
    'ru' => $lang['ytranslate:language_russian'],
    'uk' => $lang['ytranslate:language_ukrainian'],
    'be' => $lang['ytranslate:language_belarusian'],
    'kk' => $lang['ytranslate:language_kazakh'],
    'uz' => $lang['ytranslate:language_uzbek'],
    'ky' => $lang['ytranslate:language_kyrgyz'],
    'tg' => $lang['ytranslate:language_tajik'],
    'en' => $lang['ytranslate:language_english'],
    'de' => $lang['ytranslate:language_german'],
    'zh' => $lang['ytranslate:language_chinese']
);
$cfg = array();
$providerFields = array(
    array('name' => 'default_lang', 'title' => $lang['ytranslate:default_lang_title'], 'descr' => $lang['ytranslate:default_lang_descr'], 'type' => 'select', 'values' => $languages, 'value' => pluginGetVariable('ytranslate', 'default_lang') ?: 'ru'),
    array('name' => 'primary_provider', 'title' => $lang['ytranslate:primary_provider'], 'descr' => $lang['ytranslate:primary_provider_descr'], 'type' => 'select', 'values' => $providers, 'value' => pluginGetVariable('ytranslate', 'primary_provider') ?: 'google')
);
foreach (array(1, 2, 3, 4) as $index) {
    $providerFields[] = array('name' => 'fallback_' . $index, 'title' => $lang['ytranslate:fallback'] . ' ' . $index, 'descr' => $lang['ytranslate:fallback_descr'], 'type' => 'select', 'values' => array('' => $lang['ytranslate:provider_disabled']) + $providers, 'value' => pluginGetVariable('ytranslate', 'fallback_' . $index));
}
$providerFields[] = array('name' => 'google_key', 'title' => $lang['ytranslate:google_key'], 'descr' => $lang['ytranslate:google_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'google_key'));
$providerFields[] = array('name' => 'microsoft_key', 'title' => $lang['ytranslate:microsoft_key'], 'descr' => $lang['ytranslate:microsoft_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'microsoft_key'));
$providerFields[] = array('name' => 'microsoft_region', 'title' => $lang['ytranslate:microsoft_region'], 'descr' => $lang['ytranslate:microsoft_region_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'microsoft_region'));
$providerFields[] = array('name' => 'libre_url', 'title' => $lang['ytranslate:libre_url'], 'descr' => $lang['ytranslate:libre_url_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'libre_url') ?: 'https://translate.ngcms.org');
$providerFields[] = array('name' => 'libre_key', 'title' => $lang['ytranslate:libre_key'], 'descr' => $lang['ytranslate:libre_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'libre_key'));
$providerFields[] = array('name' => 'deepl_key', 'title' => $lang['ytranslate:deepl_key'], 'descr' => $lang['ytranslate:deepl_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'deepl_key'));
$providerFields[] = array('name' => 'deepl_free', 'title' => $lang['ytranslate:deepl_free'], 'descr' => $lang['ytranslate:deepl_free_descr'], 'type' => 'checkbox', 'value' => pluginGetVariable('ytranslate', 'deepl_free'));
$providerFields[] = array('name' => 'yandex_key', 'title' => $lang['ytranslate:yandex_key'], 'descr' => $lang['ytranslate:yandex_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'yandex_key'));
$providerFields[] = array('name' => 'yandex_folder', 'title' => $lang['ytranslate:yandex_folder'], 'descr' => $lang['ytranslate:yandex_folder_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'yandex_folder'));
$providerFields[] = array('name' => 'auto_ip', 'title' => $lang['ytranslate:auto_ip'], 'descr' => $lang['ytranslate:auto_ip_descr'], 'type' => 'checkbox', 'value' => pluginGetVariable('ytranslate', 'auto_ip'));
$languageFields = array();
foreach ($languages as $code => $name) {
    $languageFields[] = array('name' => 'lang_' . $code, 'title' => $name, 'type' => 'checkbox', 'value' => ($code === 'ru' || pluginGetVariable('ytranslate', 'lang_' . $code)) ? '1' : '0');
}
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:group_provider'], 'entries' => $providerFields);
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:group_languages'], 'entries' => $languageFields);
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:group_template'], 'entries' => array(array('name' => 'localsource', 'title' => $lang['ytranslate:localsource_title'], 'descr' => $lang['ytranslate:localsource_descr'], 'type' => 'select', 'values' => array('0' => $lang['ytranslate:localsource_0'], '1' => $lang['ytranslate:localsource_1']), 'value' => pluginGetVariable('ytranslate', 'localsource'))));

if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('ytranslate', $cfg);
    print_commit_complete('ytranslate');
} else {
    generate_config_page('ytranslate', $cfg);
}
