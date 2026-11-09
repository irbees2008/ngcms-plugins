<?php
if (!defined('NGCMS')) {
    die('HAL');
}

pluginsLoadConfig();
LoadPluginLang('lazy_load', 'config', '', '', ':');

$enabled = extra_get_param('lazy_load', 'enabled');
if ($enabled === null || $enabled === '') {
    $enabled = '1';
}

$skipFirst = extra_get_param('lazy_load', 'skip_first');
if ($skipFirst === null || $skipFirst === '') {
    $skipFirst = '2';
}

$cfg = array();
$cfgX = array();

$cfgX[] = array(
    'name'  => 'enabled',
    'title' => 'Включить Lazy Load (1 — да, 0 — нет)',
    'descr' => 'При включении плагин добавляет loading="lazy" изображениям ниже исключённого начального количества.',
    'type'  => 'input',
    'value' => (string) $enabled
);

$cfgX[] = array(
    'name'  => 'skip_first',
    'title' => 'Количество первых изображений без Lazy Load',
    'descr' => 'Например, 2: первые два изображения остаются без изменений. Рекомендуется 1–3 в зависимости от шаблона.',
    'type'  => 'input',
    'value' => (string) $skipFirst
);

$cfgX[] = array(
    'name'  => 'exclude_classes',
    'title' => 'Исключаемые CSS-классы и ID',
    'descr' => 'Через запятую. Например: no-lazy, logo, hero-image, #site-logo. Сравнение выполняется по классу или ID самого img.',
    'type'  => 'input',
    'value' => (string) extra_get_param('lazy_load', 'exclude_classes')
);

$cfgX[] = array(
    'name'  => 'exclude_urls',
    'title' => 'Исключаемые части URL изображений',
    'descr' => 'Через запятую. Например: /logo/, avatar, banner-top. Если src содержит указанную строку, изображение пропускается.',
    'type'  => 'input',
    'value' => (string) extra_get_param('lazy_load', 'exclude_urls')
);

$cfg[] = array(
    'mode'    => 'group',
    'title'   => 'Настройки Lazy Load',
    'entries' => $cfgX
);

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'commit') {
    commit_plugin_config_changes('lazy_load', $cfg);
    print_commit_complete('lazy_load');
} else {
    generate_config_page('lazy_load', $cfg);
}
