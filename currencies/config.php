<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();

$cfg = [];
array_push($cfg, [
    'name'  => 'base',
    'type'  => 'input',
    'title' => 'Базовая валюта (ISO-код)',
    'descr' => 'Например: RUB, UAH, USD. Цены в системе хранятся в этой валюте.',
    'html_flags' => 'style="width:80px" placeholder="RUB"',
    'value' => pluginGetVariable('currencies', 'base') ?: 'RUB',
]);
array_push($cfg, [
    'name'  => 'rate_source',
    'type'  => 'select',
    'title' => 'Источник курсов валют',
    'values' => ['cbr' => 'ЦБ РФ (cbr.ru)', 'manual' => 'Ручной ввод'],
    'value' => pluginGetVariable('currencies', 'rate_source') ?: 'cbr',
]);
array_push($cfg, [
    'name'  => 'format',
    'type'  => 'input',
    'title' => 'Формат отображения цены',
    'descr' => '%s — сумма, %c — символ валюты. Например: %s %c',
    'html_flags' => 'style="width:200px" placeholder="%s %c"',
    'value' => pluginGetVariable('currencies', 'format') ?: '%s %c',
]);

if ($_REQUEST['action'] === 'commit') {
    commit_plugin_config_changes('currencies', $cfg);
    print_commit_complete('currencies');
} else {
    generate_config_page('currencies', $cfg);
}
