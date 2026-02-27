<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Preload config & language
pluginsLoadConfig();
LoadPluginLang($plugin, 'config', '', '', ':');

$cfg = [];

// Group: main settings
$gMain = [];
$providers = [
    '' => $lang['ai_rewriter:provider.disabled'],
    'openai' => $lang['ai_rewriter:provider.openai'],
    'openai_compat' => $lang['ai_rewriter:provider.compat'],
    'anthropic' => $lang['ai_rewriter:provider.anthropic'],
];

$temp = pluginGetVariable($plugin, 'temperature');
if ($temp === null || $temp === '') {
    $temp = '0.7';
}

array_push($gMain, ['type' => 'select', 'name' => 'provider', 'title' => $lang['ai_rewriter:provider'], 'values' => $providers, 'value' => pluginGetVariable($plugin, 'provider')]);
array_push($gMain, ['type' => 'input', 'name' => 'model', 'title' => $lang['ai_rewriter:model'], 'descr' => $lang['ai_rewriter:model#desc'], 'value' => pluginGetVariable($plugin, 'model') ?: 'gpt-4o-mini']);
array_push($gMain, ['type' => 'input', 'name' => 'api_key', 'title' => $lang['ai_rewriter:api_key'], 'descr' => $lang['ai_rewriter:api_key#desc'], 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable($plugin, 'api_key')]);
array_push($gMain, ['type' => 'input', 'name' => 'api_base', 'title' => $lang['ai_rewriter:api_base'], 'descr' => $lang['ai_rewriter:api_base#desc'], 'html_flags' => 'style="width: 300px;"', 'value' => pluginGetVariable($plugin, 'api_base')]);
array_push($gMain, ['type' => 'input', 'name' => 'originality', 'title' => $lang['ai_rewriter:originality'], 'descr' => $lang['ai_rewriter:originality#desc'], 'value' => pluginGetVariable($plugin, 'originality') ?: '60']);
array_push($gMain, ['type' => 'input', 'name' => 'tone', 'title' => $lang['ai_rewriter:tone'], 'descr' => $lang['ai_rewriter:tone#desc'], 'value' => pluginGetVariable($plugin, 'tone') ?: '']);
array_push($gMain, ['type' => 'input', 'name' => 'temperature', 'title' => $lang['ai_rewriter:temperature'], 'descr' => $lang['ai_rewriter:temperature#desc'], 'value' => $temp]);
array_push($gMain, ['type' => 'input', 'name' => 'timeout', 'title' => $lang['ai_rewriter:timeout'], 'descr' => $lang['ai_rewriter:timeout#desc'], 'value' => pluginGetVariable($plugin, 'timeout') ?: '20']);

array_push($cfg, ['mode' => 'group', 'title' => $lang['ai_rewriter:group.main'], 'entries' => $gMain]);

// Group: auto apply
$gAuto = [];
array_push($gAuto, ['type' => 'select', 'name' => 'enable_on_add', 'title' => $lang['ai_rewriter:enable_on_add'], 'values' => ['0' => $lang['ai_rewriter:bool.no'], '1' => $lang['ai_rewriter:bool.yes']], 'value' => intval(pluginGetVariable($plugin, 'enable_on_add'))]);
array_push($gAuto, ['type' => 'select', 'name' => 'enable_on_edit', 'title' => $lang['ai_rewriter:enable_on_edit'], 'values' => ['0' => $lang['ai_rewriter:bool.no'], '1' => $lang['ai_rewriter:bool.yes']], 'value' => intval(pluginGetVariable($plugin, 'enable_on_edit'))]);
array_push($cfg, ['mode' => 'group', 'title' => $lang['ai_rewriter:group.auto'], 'entries' => $gAuto]);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
