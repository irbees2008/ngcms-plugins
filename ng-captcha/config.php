<?php
// Защита от попыток взлома.
if (!defined('NGCMS')) {
    die('HAL');
}
$plugin = 'ng-captcha';
// Подгрузка конфигурации и языковых файлов.
pluginsLoadConfig();
LoadPluginLang($plugin, 'config', '', '', ':');
// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\trans;
// Вспомогательная функция для безопасного получения перевода
if (!function_exists('safe_trans')) {
    function safe_trans($key, $default = '') {
        try {
            if (function_exists('trans')) {
                $result = trans($key);
                return $result ?: $default;
            }
        } catch (Exception $e) {
            return $default;
        }
        return $default;
    }
}
// Вспомогательная функция для безопасного получения настройки
if (!function_exists('safe_setting')) {
    function safe_setting($plugin, $name, $default = '') {
        try {
            if (function_exists('setting')) {
                return setting($plugin, $name, $default);
            }
        } catch (Exception $e) {
            return $default;
        }
        return $default;
    }
}
// Заполнить параметры конфигурации.
$cfg = [];
// Описание плагина.
array_push($cfg, [
    'descr' => safe_trans($plugin . ':description', 'Универсальный плагин защиты форм от ботов с поддержкой Google reCAPTCHA v3, Cloudflare Turnstile и Яндекс SmartCaptcha'),
]);
// Выбор провайдера капчи.
array_push($cfg, [
    'mode' => 'group',
    'title' => safe_trans($plugin . ':group_provider', 'Провайдер капчи'),
    'entries' => [
        [
            'name' => 'provider',
            'title' => safe_trans($plugin . ':provider', 'Провайдер'),
            'descr' => safe_trans($plugin . ':provider#descr', 'Выберите сервис для защиты форм'),
            'type' => 'select',
            'values' => [
                'google' => safe_trans($plugin . ':provider_google', 'Google reCAPTCHA v3'),
                'turnstile' => safe_trans($plugin . ':provider_turnstile', 'Cloudflare Turnstile'),
                'yandex' => safe_trans($plugin . ':provider_yandex', 'Яндекс SmartCaptcha'),
            ],
            'value' => safe_setting($plugin, 'provider', 'google'),
        ],
    ],
]);
// Ключи доступа.
array_push($cfg, [
    'mode' => 'group',
    'title' => safe_trans($plugin . ':group_keys', 'Ключи доступа'),
    'entries' => [
        [
            'name' => 'site_key',
            'title' => safe_trans($plugin . ':site_key', 'Ключ сайта'),
            'descr' => safe_trans($plugin . ':site_key#descr', 'Публичный ключ для отображения капчи'),
            'type' => 'input',
            'html_flags' => 'style="min-width:300px;" autocomplete="off"',
            'value' => safe_setting($plugin, 'site_key', ''),
        ],
        [
            'name' => 'secret_key',
            'title' => safe_trans($plugin . ':secret_key', 'Секретный ключ'),
            'descr' => safe_trans($plugin . ':secret_key#descr', 'Приватный ключ для проверки на сервере'),
            'type' => 'input',
            'html_flags' => 'style="min-width:300px;" autocomplete="off"',
            'value' => safe_setting($plugin, 'secret_key', ''),
        ],
    ],
]);
// Настройки Google reCAPTCHA.
array_push($cfg, [
    'mode' => 'group',
    'title' => safe_trans($plugin . ':group_google', 'Настройки Google reCAPTCHA'),
    'entries' => [
        [
            'name' => 'google_min_score',
            'title' => safe_trans($plugin . ':google_min_score', 'Минимальный score'),
            'descr' => safe_trans($plugin . ':google_min_score#descr', 'Оценка от 0.0 до 1.0. Рекомендуется 0.5'),
            'type' => 'select',
            'values' => [
                '0.1' => '0.1',
                '0.2' => '0.2',
                '0.3' => '0.3',
                '0.4' => '0.4',
                '0.5' => '0.5 (по умолчанию)',
                '0.6' => '0.6',
                '0.7' => '0.7',
                '0.8' => '0.8',
                '0.9' => '0.9',
            ],
            'value' => safe_setting($plugin, 'google_min_score', '0.5'),
        ],
    ],
]);
// Настройки Turnstile.
array_push($cfg, [
    'mode' => 'group',
    'title' => safe_trans($plugin . ':group_turnstile', 'Настройки Cloudflare Turnstile'),
    'entries' => [
        [
            'name' => 'turnstile_theme',
            'title' => safe_trans($plugin . ':turnstile_theme', 'Тема виджета'),
            'descr' => safe_trans($plugin . ':turnstile_theme#descr', 'Цветовая схема виджета'),
            'type' => 'select',
            'values' => [
                'auto' => safe_trans($plugin . ':theme_auto', 'Автоматически'),
                'light' => safe_trans($plugin . ':theme_light', 'Светлая'),
                'dark' => safe_trans($plugin . ':theme_dark', 'Темная'),
            ],
            'value' => safe_setting($plugin, 'turnstile_theme', 'auto'),
        ],
        [
            'name' => 'turnstile_size',
            'title' => safe_trans($plugin . ':turnstile_size', 'Размер виджета'),
            'descr' => safe_trans($plugin . ':turnstile_size#descr', 'Отображение виджета на странице'),
            'type' => 'select',
            'values' => [
                'normal' => safe_trans($plugin . ':size_normal', 'Обычный'),
                'compact' => safe_trans($plugin . ':size_compact', 'Компактный'),
            ],
            'value' => safe_setting($plugin, 'turnstile_size', 'normal'),
        ],
    ],
]);
// Основные настройки.
array_push($cfg, [
    'mode' => 'group',
    'title' => safe_trans($plugin . ':group_main', 'Основные настройки'),
    'entries' => [
        [
            'name' => 'guests_only',
            'title' => safe_trans($plugin . ':guests_only', 'Проверять только гостей'),
            'descr' => safe_trans($plugin . ':guests_only#descr', 'Да - только незарегистрированные, Нет - все пользователи'),
            'type' => 'select',
            'values' => [
                '0' => safe_trans($plugin . ':no', 'Нет'),
                '1' => safe_trans($plugin . ':yes', 'Да'),
            ],
            'value' => safe_setting($plugin, 'guests_only', 1),
        ],
        [
            'name' => 'use_api_js',
            'title' => safe_trans($plugin . ':use_api_js', 'Встраивать JavaScript API'),
            'descr' => safe_trans($plugin . ':use_api_js#descr', 'Автоматически добавлять скрипт провайдера'),
            'type' => 'select',
            'values' => [
                '0' => safe_trans($plugin . ':no', 'Нет'),
                '1' => safe_trans($plugin . ':yes', 'Да'),
            ],
            'value' => safe_setting($plugin, 'use_api_js', 1),
        ],
        [
            'name' => 'use_attach_js',
            'title' => safe_trans($plugin . ':use_attach_js', 'Встраивать JavaScript перехватчик'),
            'descr' => safe_trans($plugin . ':use_attach_js#descr', 'Добавлять JavaScript для автоматического перехвата форм'),
            'type' => 'select',
            'values' => [
                '0' => safe_trans($plugin . ':no', 'Нет'),
                '1' => safe_trans($plugin . ':yes', 'Да'),
            ],
            'value' => safe_setting($plugin, 'use_attach_js', 1),
        ],
        [
            'name' => 'localsource',
            'title' => safe_trans($plugin . ':localsource', 'Локальные шаблоны'),
            'descr' => safe_trans($plugin . ':localsource#descr', 'Использовать шаблоны из папки шаблона сайта'),
            'type' => 'select',
            'values' => [
                '0' => safe_trans($plugin . ':no', 'Нет'),
                '1' => safe_trans($plugin . ':yes', 'Да'),
            ],
            'value' => safe_setting($plugin, 'localsource', 0),
        ],
    ],
]);
// Если была отправлена форма, то сохраняем настройки.
if ('commit' === $_REQUEST['action']) {
    commit_plugin_config_changes($plugin, $cfg);
    return print_commit_complete($plugin);
}
generate_config_page($plugin, $cfg);
