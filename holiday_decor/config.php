<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
LoadPluginLang('holiday_decor', 'config', '', '', ':');
$yesNo = ['0' => $lang['holiday_decor:no'], '1' => $lang['holiday_decor:yes']];
$cfg = [];
$group = [];
array_push($group, [
    'name' => 'enable_garland',
    'title' => $lang['holiday_decor:enable_garland_title'],
    'descr' => $lang['holiday_decor:enable_garland_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_garland')
]);
array_push($group, [
    'name' => 'garland_mode',
    'title' => $lang['holiday_decor:garland_mode_title'],
    'descr' => $lang['holiday_decor:garland_mode_descr'],
    'type' => 'select',
    'values' => [
        'sprite' => $lang['holiday_decor:mode_sprite'],
        'modern' => $lang['holiday_decor:mode_modern'],
        'lightrope' => $lang['holiday_decor:mode_lightrope'],
    ],
    'value' => extra_get_param($plugin, 'garland_mode') ?: 'sprite'
]);
array_push($group, [
    'name' => 'garland_style',
    'title' => $lang['holiday_decor:garland_style_title'],
    'descr' => $lang['holiday_decor:garland_style_descr'],
    'type' => 'select',
    'values' => [
        '1' => $lang['holiday_decor:garland_style_1'],
        '2' => $lang['holiday_decor:garland_style_2'],
    ],
    'value' => extra_get_param($plugin, 'garland_style') ?: '1'
]);
array_push($group, [
    'name' => 'garland_position',
    'title' => $lang['holiday_decor:garland_position_title'],
    'descr' => $lang['holiday_decor:garland_position_descr'],
    'type' => 'select',
    'values' => [
        'absolute' => $lang['holiday_decor:position_absolute'],
        'fixed' => $lang['holiday_decor:position_fixed'],
    ],
    'value' => extra_get_param($plugin, 'garland_position') ?: 'absolute'
]);
array_push($group, [
    'name' => 'enable_snow',
    'title' => $lang['holiday_decor:enable_snow_title'],
    'descr' => $lang['holiday_decor:enable_snow_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_snow')
]);
// Доп. эффекты
array_push($group, [
    'name' => 'enable_fireworks',
    'title' => $lang['holiday_decor:fireworks_title'],
    'descr' => $lang['holiday_decor:fireworks_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_fireworks') ?: '0'
]);
array_push($group, [
    'name' => 'enable_cursor_snow',
    'title' => $lang['holiday_decor:cursor_snow_title'],
    'descr' => $lang['holiday_decor:cursor_snow_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_cursor_snow') ?: '0'
]);
array_push($group, [
    'name' => 'enable_jq_snowfall',
    'title' => $lang['holiday_decor:jq_snowfall_title'],
    'descr' => $lang['holiday_decor:jq_snowfall_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_jq_snowfall') ?: '0'
]);
array_push($group, [
    'name' => 'enable_big_snow',
    'title' => $lang['holiday_decor:big_snow_title'],
    'descr' => $lang['holiday_decor:big_snow_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_big_snow') ?: '0'
]);
array_push($group, [
    'name' => 'enable_falling_stars',
    'title' => $lang['holiday_decor:falling_stars_title'],
    'descr' => $lang['holiday_decor:falling_stars_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_falling_stars') ?: '0'
]);
array_push($group, [
    'name' => 'enable_countdown_santa',
    'title' => $lang['holiday_decor:countdown_santa_title'],
    'descr' => $lang['holiday_decor:countdown_santa_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_countdown_santa') ?: '0'
]);
array_push($group, [
    'name' => 'enable_countdown_banner',
    'title' => $lang['holiday_decor:countdown_banner_title'],
    'descr' => $lang['holiday_decor:countdown_banner_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'enable_countdown_banner') ?: '0'
]);
array_push($group, [
    'name' => 'countdown_show_seconds',
    'title' => $lang['holiday_decor:countdown_show_seconds_title'],
    'descr' => $lang['holiday_decor:countdown_show_seconds_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'countdown_show_seconds') ?: '1'
]);
array_push($group, [
    'name' => 'snow_count',
    'title' => $lang['holiday_decor:snow_count_title'],
    'descr' => $lang['holiday_decor:snow_count_descr'],
    'type' => 'input',
    'value' => extra_get_param($plugin, 'snow_count') ?: '150'
]);
array_push($group, [
    'name' => 'snow_speed',
    'title' => $lang['holiday_decor:snow_speed_title'],
    'descr' => $lang['holiday_decor:snow_speed_descr'],
    'type' => 'select',
    'values' => [
        '0.5' => $lang['holiday_decor:speed_slow'],
        '1.5' => $lang['holiday_decor:speed_medium'],
        '3' => $lang['holiday_decor:speed_fast'],
    ],
    'value' => extra_get_param($plugin, 'snow_speed') ?: '1.5'
]);
array_push($group, [
    'name' => 'show_switch',
    'title' => $lang['holiday_decor:show_switch_title'],
    'descr' => $lang['holiday_decor:show_switch_descr'],
    'type' => 'select',
    'values' => $yesNo,
    'value' => extra_get_param($plugin, 'show_switch') ?: '1'
]);
array_push($cfg, ['mode' => 'group', 'title' => '<b>' . $lang['holiday_decor:settings_title'] . '</b>', 'entries' => $group]);
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
