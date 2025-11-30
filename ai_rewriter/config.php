<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Preload config
pluginsLoadConfig();

$cfg = [];

// Group: Основные
$gMain = [];
$providers = [
    '' => '— Выключено —',
    'openai' => 'OpenAI / OpenAI-совместимые',
    'openai_compat' => 'Совместимый (кастомный API Base)',
    'anthropic' => 'Anthropic (Claude)'
];

$temp = pluginGetVariable($plugin, 'temperature');
if ($temp === null || $temp === '') {
    $temp = '0.7';
}

array_push($gMain, ['type' => 'select', 'name' => 'provider', 'title' => 'Провайдер ИИ', 'values' => $providers, 'value' => pluginGetVariable($plugin, 'provider')]);
array_push($gMain, ['type' => 'input', 'name' => 'model', 'title' => 'Модель', 'descr' => 'Напр.: gpt-4o-mini, gpt-4.1, claude-3-haiku-20240307', 'value' => pluginGetVariable($plugin, 'model') ?: 'gpt-4o-mini']);
array_push($gMain, ['type' => 'input', 'name' => 'api_key', 'title' => 'API ключ', 'descr' => 'Ключ доступа к API провайдера (хранится в конфиге движка).', 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable($plugin, 'api_key')]);
array_push($gMain, ['type' => 'input', 'name' => 'api_base', 'title' => 'API Base (опционально)', 'descr' => 'Для OpenAI-совместимых/прокси, напр.: https://api.openai.com/v1 или ваш шлюз', 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable($plugin, 'api_base')]);
array_push($gMain, ['type' => 'input', 'name' => 'originality', 'title' => 'Процент оригинальности', 'descr' => '0–100, по умолчанию 60', 'value' => pluginGetVariable($plugin, 'originality') ?: '60']);
array_push($gMain, ['type' => 'input', 'name' => 'tone', 'title' => 'Тональность (опционально)', 'descr' => 'Напр.: нейтральный, информационный, дружелюбный', 'value' => pluginGetVariable($plugin, 'tone') ?: '']);
array_push($gMain, ['type' => 'input', 'name' => 'temperature', 'title' => 'Temperature', 'descr' => '0.0–1.0, креативность. По умолчанию 0.7', 'value' => $temp]);
array_push($gMain, ['type' => 'input', 'name' => 'timeout', 'title' => 'Таймаут запроса (сек)', 'descr' => 'HTTP таймаут для запроса к API. По умолчанию 20 сек.', 'value' => pluginGetVariable($plugin, 'timeout') ?: '20']);

array_push($cfg, ['mode' => 'group', 'title' => '<b>Основные настройки</b>', 'entries' => $gMain]);

// Group: Автоприменение
$gAuto = [];
array_push($gAuto, ['type' => 'select', 'name' => 'enable_on_add', 'title' => 'Рерайт при добавлении', 'values' => ['0' => 'Нет', '1' => 'Да'], 'value' => intval(pluginGetVariable($plugin, 'enable_on_add'))]);
array_push($gAuto, ['type' => 'select', 'name' => 'enable_on_edit', 'title' => 'Рерайт при редактировании', 'values' => ['0' => 'Нет', '1' => 'Да'], 'value' => intval(pluginGetVariable($plugin, 'enable_on_edit'))]);
array_push($cfg, ['mode' => 'group', 'title' => '<b>Автоприменение</b>', 'entries' => $gAuto]);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
