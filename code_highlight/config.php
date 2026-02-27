<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
// Конфигурация плагина code_highlight
LoadPluginLang('code_highlight', 'config', '', 'code_highlight', ':');
pluginsLoadConfig();
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => $lang['code_highlight:description']));
$themes = array(
    'Default' => $lang['code_highlight:theme.default'],
    'Django' => $lang['code_highlight:theme.django'],
    'Eclipse' => $lang['code_highlight:theme.eclipse'],
    'Emacs' => $lang['code_highlight:theme.emacs'],
    'FadeToGrey' => $lang['code_highlight:theme.fadetogrey'],
    'MDUltra' => $lang['code_highlight:theme.mdultra'],
    'Midnight' => $lang['code_highlight:theme.midnight'],
    'RDark' => $lang['code_highlight:theme.rdark'],
);
$plugin = 'code_highlight';
$boolOptions = array('1' => $lang['code_highlight:option.yes'], '0' => $lang['code_highlight:option.no']);
array_push($cfgX, array('name' => 'use_cdn', 'title' => $lang['code_highlight:option.use_cdn'], 'type' => 'select', 'values' => $boolOptions, 'value' => intval(pluginGetVariable($plugin, 'use_cdn') ?? 1)));
array_push($cfgX, array('name' => 'theme', 'title' => $lang['code_highlight:option.theme'], 'type' => 'select', 'values' => $themes, 'value' => strval(pluginGetVariable($plugin, 'theme') ?? 'Default')));
array_push($cfg, array('mode' => 'group', 'title' => $lang['code_highlight:group.syntax'], 'entries' => $cfgX));
// Выбор подключаемых кистей (checkbox per brush)
$cfgX = array();
$brushes = array(
    'jscript'    => 'javascript',
    'php'        => 'php',
    'sql'        => 'sql',
    'xml'        => 'xml',
    'css'        => 'css',
    'plain'      => 'plain',
    'bash'       => 'bash',
    'python'     => 'python',
    'java'       => 'java',
    'csharp'     => 'csharp',
    'cpp'        => 'cpp',
    'delphi'     => 'delphi',
    'diff'       => 'diff',
    'ruby'       => 'ruby',
    'perl'       => 'perl',
    'vb'         => 'vb',
    'powershell' => 'powershell',
    'scala'      => 'scala',
    'groovy'     => 'groovy',
);
foreach ($brushes as $key => $langKey) {
    $cfgX[] = array(
        'name'  => 'enable_' . $key,
        'title' => sprintf($lang['code_highlight:brush.enable'], $lang['code_highlight:brush.' . $langKey]),
        'type'  => 'select',
        'values' => $boolOptions,
        'value' => intval(pluginGetVariable($plugin, 'enable_' . $key) ?? 1),
    );
}
array_push($cfg, array('mode' => 'group', 'title' => $lang['code_highlight:group.brushes'], 'entries' => $cfgX));
if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
