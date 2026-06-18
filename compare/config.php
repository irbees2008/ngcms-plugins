<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

// Load xfields list for selection
$xfKeys = [];
if (function_exists('xf_configLoad')) {
    $xfc = xf_configLoad();
    foreach ($xfc['news'] ?? [] as $k => $v) {
        $xfKeys[$k] = $k . ' — ' . $v['title'];
    }
}

$cfg = [];
array_push($cfg, [
    'descr' => 'Плагин сравнения товаров. Работает с новостями NGCMS и доп. полями xfields.<br/>'
        . 'В шаблонах новостей используйте переменные <b>compare_add_link</b>, <b>compare_remove_link</b>, <b>in_compare</b>.',
]);
array_push($cfg, [
    'name'  => 'xfields',
    'type'  => 'input',
    'title' => 'Поля для сравнения (через запятую)',
    'descr' => 'Укажите имена полей xfields для отображения в таблице сравнения. Пусто = все поля.<br/>'
        . 'Доступные поля: ' . implode(', ', array_keys($xfKeys)),
    'html_flags' => 'style="width:500px" placeholder="price,weight,color"',
    'value' => pluginGetVariable('compare', 'xfields'),
]);
array_push($cfg, [
    'name'  => 'limit',
    'type'  => 'select',
    'title' => 'Максимум товаров в сравнении',
    'values' => [2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6'],
    'value' => pluginGetVariable('compare', 'limit') ?: 4,
]);

if ($_REQUEST['action'] === 'commit') {
    commit_plugin_config_changes('compare', $cfg);
    print_commit_complete('compare');
} else {
    generate_config_page('compare', $cfg);
}
