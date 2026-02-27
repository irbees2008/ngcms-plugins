<?php
// Защита от попыток взлома.
if (! defined('NGCMS')) {
    die('HAL');
}
// Дублирование глобальных переменных.
$plugin = 'ng-advanced-captcha';
// Подгрузка библиотек-файлов плагина.
pluginsLoadConfig();
LoadPluginLang($plugin, 'config', '', '', ':');
// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\trans;
// Заполнить параметры конфигурации.
$cfg = [];
// Описание плагина.
array_push($cfg, [
    'descr' => trans($plugin . ':description'),
]);
// Типы капчи.
array_push($cfg, [
    'mode' => 'group',
    'title' => trans($plugin . ':group_types'),
    'entries' => [
        [
            'name' => 'type_math',
            'title' => trans($plugin . ':type_math'),
            'descr' => trans($plugin . ':type_math#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'type_math', true),
        ],
        [
            'name' => 'type_text',
            'title' => trans($plugin . ':type_text'),
            'descr' => trans($plugin . ':type_text#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'type_text', true),
        ],
        [
            'name' => 'type_question',
            'title' => trans($plugin . ':type_question'),
            'descr' => trans($plugin . ':type_question#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'type_question', true),
        ],
        [
            'name' => 'type_checkbox',
            'title' => trans($plugin . ':type_checkbox'),
            'descr' => trans($plugin . ':type_checkbox#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'type_checkbox', true),
        ],
        [
            'name' => 'type_slider',
            'title' => trans($plugin . ':type_slider'),
            'descr' => trans($plugin . ':type_slider#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'type_slider', true),
        ],
    ],
]);
// Основные настройки.
array_push($cfg, [
    'mode' => 'group',
    'title' => trans($plugin . ':group_main'),
    'entries' => [
        [
            'name' => 'guests_only',
            'title' => trans($plugin . ':guests_only'),
            'descr' => trans($plugin . ':guests_only#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'guests_only', false),
        ],
        [
            'name' => 'random_type',
            'title' => trans($plugin . ':random_type'),
            'descr' => trans($plugin . ':random_type#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'random_type', true),
        ],
        [
            'name' => 'session_timeout',
            'title' => trans($plugin . ':session_timeout'),
            'descr' => trans($plugin . ':session_timeout#descr'),
            'type' => 'input',
            'html_flags' => 'style="width:100px;" type="number" min="60" max="3600"',
            'value' => (int) setting($plugin, 'session_timeout', 300),
        ],
    ],
]);
// Настройки безопасности.
array_push($cfg, [
    'mode' => 'group',
    'title' => trans($plugin . ':group_security'),
    'entries' => [
        [
            'name' => 'min_time',
            'title' => trans($plugin . ':min_time'),
            'descr' => trans($plugin . ':min_time#descr'),
            'type' => 'input',
            'html_flags' => 'style="width:100px;" type="number" min="0" max="10"',
            'value' => (int) setting($plugin, 'min_time', 2),
        ],
        [
            'name' => 'max_attempts',
            'title' => trans($plugin . ':max_attempts'),
            'descr' => trans($plugin . ':max_attempts#descr'),
            'type' => 'input',
            'html_flags' => 'style="width:100px;" type="number" min="1" max="10"',
            'value' => (int) setting($plugin, 'max_attempts', 3),
        ],
        [
            'name' => 'use_honeypot',
            'title' => trans($plugin . ':use_honeypot'),
            'descr' => trans($plugin . ':use_honeypot#descr'),
            'type' => 'select',
            'values' => [
                trans('noa'),
                trans('yesa'),
            ],
            'value' => (int) setting($plugin, 'use_honeypot', true),
        ],
    ],
]);
// Если была отправлена форма, то сохраняем настройки.
if ('commit' === $_REQUEST['action']) {
    commit_plugin_config_changes($plugin, $cfg);
    return print_commit_complete($plugin);
}

generate_config_page($plugin, $cfg);
