<?php
// Защита от попыток взлома.
if (! defined('NGCMS')) {
    die('HAL');
}
// Дублирование глобальных переменных.
$plugin = 'ng-turnstile';
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
// Ключи Cloudflare Turnstile.
array_push($cfg, [
    'mode' => 'group',
    'title' => trans($plugin . ':group_keys'),
    'entries' => [
        [
            'name' => 'site_key',
            'title' => trans($plugin . ':site_key'),
            'descr' => trans($plugin . ':site_key#descr'),
            'type' => 'input',
            'html_flags' => 'style="min-width:260px;text-align:right;" autocomplete="off" required',
            'value' => setting($plugin, 'site_key', null),
        ],
        [
            'name' => 'secret_key',
            'title' => trans($plugin . ':secret_key'),
            'descr' => trans($plugin . ':secret_key#descr'),
            'type' => 'input',
            'html_flags' => 'min-width:260px;text-align:right;" autocomplete="off" required',
            'value' => setting($plugin, 'secret_key', null),
        ],
    ],
]);
// Настройки виджета.
array_push($cfg, [
    'mode' => 'group',
    'title' => trans($plugin . ':group_widget'),
    'entries' => [
        [
            'name' => 'theme',
            'title' => trans($plugin . ':theme'),
            'descr' => trans($plugin . ':theme#descr'),
            'type' => 'select',
            'values' => [
                'auto' => trans($plugin . ':theme_auto'),
                'light' => trans($plugin . ':theme_light'),
                'dark' => trans($plugin . ':theme_dark'),
            ],
            'value' => setting($plugin, 'theme', 'auto'),
        ],
        [
            'name' => 'size',
            'title' => trans($plugin . ':size'),
            'descr' => trans($plugin . ':size#descr'),
            'type' => 'select',
            'values' => [
                'normal' => trans($plugin . ':size_normal'),
                'compact' => trans($plugin . ':size_compact'),
            ],
            'value' => setting($plugin, 'size', 'normal'),
        ],
        [
            'name' => 'appearance',
            'title' => trans($plugin . ':appearance'),
            'descr' => trans($plugin . ':appearance#descr'),
            'type' => 'select',
            'values' => [
                'always' => trans($plugin . ':appearance_always'),
                'execute' => trans($plugin . ':appearance_execute'),
                'interaction-only' => trans($plugin . ':appearance_interaction'),
            ],
            'value' => setting($plugin, 'appearance', 'always'),
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
    ],
]);
// Если была отправлена форма, то сохраняем настройки.
if ('commit' === $_REQUEST['action']) {
    // Валидация входящих обязательных параметров.
    try {
        if (empty($site_key = trim(secure_html($_POST['site_key'])))) {
            throw new \InvalidArgumentException('empty-site-key');
        }
        if (empty($secret_key = trim(secure_html($_POST['secret_key'])))) {
            throw new \InvalidArgumentException('empty-secret-key');
        }
    } catch (\InvalidArgumentException $e) {
        $message = $e->getMessage();
        msg([
            'type' => 'error',
            'text' => trans("$plugin:error.required_parameters"),
            'info' => trans("$plugin:error.$message"),
        ]);
        return generate_config_page($plugin, $cfg);
    }
    commit_plugin_config_changes($plugin, $cfg);
    return print_commit_complete($plugin);
}

generate_config_page($plugin, $cfg);
