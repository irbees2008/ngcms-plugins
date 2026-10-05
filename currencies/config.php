<?php
if (!defined('NGCMS')) die('HAL');
LoadPluginLang('currencies', 'config', '', '', ':');
pluginsLoadConfig();

$cfg = [];
array_push($cfg, [
    'name'  => 'base',
    'type'  => 'input',
    'title' => $lang['currencies:config_base_title'],
    'descr' => $lang['currencies:config_base_description'],
    'html_flags' => 'style="width:80px" placeholder="RUB"',
    'value' => pluginGetVariable('currencies', 'base') ?: 'RUB',
]);
array_push($cfg, [
    'name'  => 'rate_source',
    'type'  => 'select',
    'title' => $lang['currencies:config_rate_source_title'],
    'descr' => $lang['currencies:config_rate_source_description'],
    'values' => [
        'cbr' => $lang['currencies:rate_source_cbr'],
        'nbu' => $lang['currencies:rate_source_nbu'],
        'nbk' => $lang['currencies:rate_source_nbk'],
        'ecb' => $lang['currencies:rate_source_ecb'],
        'manual' => $lang['currencies:rate_source_manual'],
    ],
    'value' => pluginGetVariable('currencies', 'rate_source') ?: 'cbr',
]);
array_push($cfg, [
    'name'  => 'format',
    'type'  => 'input',
    'title' => $lang['currencies:config_format_title'],
    'descr' => $lang['currencies:config_format_description'],
    'html_flags' => 'style="width:200px" placeholder="%s %c"',
    'value' => pluginGetVariable('currencies', 'format') ?: '%s %c',
]);

if (($_REQUEST['action'] ?? '') === 'commit') {
    commit_plugin_config_changes('currencies', $cfg);
    print_commit_complete('currencies');
} else {
    generate_config_page('currencies', $cfg);
}
