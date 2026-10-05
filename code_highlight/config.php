<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Ensure ng-helpers is loaded
if (!function_exists('Plugins\\logger')) {
    $ngHelpersPath = __DIR__ . '/../ng-helpers/ng-helpers.php';
    if (file_exists($ngHelpersPath)) {
        require_once $ngHelpersPath;
    }
}

use function Plugins\{logger, array_get, sanitize, get_ip};

// Конфигурация плагина code_highlight
pluginsLoadConfig();
$plugin = 'code_highlight';
global $lang;
LoadPluginLang($plugin, 'config', '', 'code_highlight.', '');

$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['code_highlight.description']));
$themes = array(
    'Default' => $lang['code_highlight.theme.default'],
    'Django' => $lang['code_highlight.theme.django'],
    'Eclipse' => $lang['code_highlight.theme.eclipse'],
    'Emacs' => $lang['code_highlight.theme.emacs'],
    'FadeToGrey' => $lang['code_highlight.theme.fadetogrey'],
    'MDUltra' => $lang['code_highlight.theme.mdultra'],
    'Midnight' => $lang['code_highlight.theme.midnight'],
    'RDark' => $lang['code_highlight.theme.rdark'],
);
array_push($cfgX, array('name' => 'use_cdn', 'title' => $lang['code_highlight.option.use_cdn'], 'type' => 'select', 'values' => array('1' => $lang['code_highlight.option.yes'], '0' => $lang['code_highlight.option.no']), 'value' => intval(pluginGetVariable($plugin, 'use_cdn') ?? 1)));
array_push($cfgX, array('name' => 'theme', 'title' => $lang['code_highlight.option.theme'], 'type' => 'select', 'values' => $themes, 'value' => strval(pluginGetVariable($plugin, 'theme') ?? 'Default')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['code_highlight.group.syntax'], 'entries' => $cfgX));
// Выбор подключаемых кистей (checkbox per brush)
$cfgX = array();
$brushes = array(
    // Базовые
    'jscript',
    'php',
    'sql',
    'xml',
    'css',
    'plain',
    // Дополнительные
    'bash',
    'python',
    'java',
    'csharp',
    'cpp',
    'delphi',
    'diff',
    'ruby',
    'perl',
    'vb',
    'powershell',
    'scala',
    'groovy',
);
foreach ($brushes as $key) {
    $cfgX[] = array(
        'name'  => 'enable_' . $key,
        'title' => sprintf($lang['code_highlight.brush.enable'], $lang['code_highlight.brush.' . $key]),
        'type'  => 'select',
        'values' => array('1' => $lang['code_highlight.option.yes'], '0' => $lang['code_highlight.option.no']),
        'value' => intval(pluginGetVariable($plugin, 'enable_' . $key) ?? 1),
    );
}
array_push($cfg, array('mode' => 'group', 'title' => $lang['code_highlight.group.brushes'], 'entries' => $cfgX));
if (array_get($_REQUEST, 'action', '') == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    logger('Code highlight config saved, IP=' . get_ip(), 'info', 'code_highlight.log');
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
