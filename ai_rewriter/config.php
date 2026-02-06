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

// Language selection
$languages = [
    'auto' => 'Автоопределение',
    'russian' => 'Русский',
    'ukrainian' => 'Украинский',
    'english' => 'Английский',
    'german' => 'Немецкий',
    'french' => 'Французский',
    'spanish' => 'Испанский',
    'italian' => 'Итальянский',
    'portuguese' => 'Португальский',
    'polish' => 'Польский'
];
array_push($gMain, ['type' => 'select', 'name' => 'default_language', 'title' => 'Язык по умолчанию', 'descr' => 'Используется если автоопределение не сработало', 'values' => $languages, 'value' => pluginGetVariable($plugin, 'default_language') ?: 'auto']);

array_push($gMain, ['type' => 'input', 'name' => 'temperature', 'title' => 'Temperature', 'descr' => '0.0–1.0, креативность. По умолчанию 0.7', 'value' => $temp]);
array_push($gMain, ['type' => 'input', 'name' => 'timeout', 'title' => 'Таймаут запроса (сек)', 'descr' => 'HTTP таймаут для запроса к API. По умолчанию 20 сек.', 'value' => pluginGetVariable($plugin, 'timeout') ?: '20']);

array_push($cfg, ['mode' => 'group', 'title' => '<b>Основные настройки</b>', 'entries' => $gMain]);

// Group: Промпт
$gPrompt = [];
$defaultPrompt = 'Ты профессиональный редактор и копирайтер. Переписывай текст сохраняя смысл, факты, структуру и разметку. Строго сохраняй HTML-теги, BBCode, ссылки, URL, кавычки, номера и знаки препинания. НЕ используй HTML-сущности типа &nbsp; &quot; &amp; - используй обычные символы. Не добавляй фактов, не удаляй важный смысл. ВАЖНО: Переписанный текст должен быть на {язык} языке, сохраняя язык оригинала.';
array_push($gPrompt, [
    'type' => 'text',
    'name' => 'system_prompt',
    'title' => 'Системный промпт',
    'descr' => 'Инструкция для AI. Используйте {язык} для подстановки языка текста (русском, украинском и т.д.)',
    'html_flags' => 'rows="8" cols="80" style="width: 100%; font-family: monospace;"',
    'value' => pluginGetVariable($plugin, 'system_prompt') ?: $defaultPrompt
]);
array_push($cfg, ['mode' => 'group', 'title' => '<b>Промпт для AI</b>', 'entries' => $gPrompt]);

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
