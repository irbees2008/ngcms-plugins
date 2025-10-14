<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
// Конфигурация плагина code_highlight
pluginsLoadConfig();
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => 'Подключает SyntaxHighlighter для блоков <pre class="brush: ...">. Если плагин выключен — остаётся простая стилизация <pre> (без JS).'));
$themes = array(
    'Default' => 'Default',
    'Django' => 'Django',
    'Eclipse' => 'Eclipse',
    'Emacs' => 'Emacs',
    'FadeToGrey' => 'FadeToGrey',
    'MDUltra' => 'MDUltra',
    'Midnight' => 'Midnight',
    'RDark' => 'RDark',
);
$plugin = 'code_highlight';
array_push($cfgX, array('name' => 'use_cdn', 'title' => 'Использовать CDN для подключения SyntaxHighlighter', 'type' => 'select', 'values' => array('1' => 'Да', '0' => 'Нет'), 'value' => intval(pluginGetVariable($plugin, 'use_cdn') ?? 1)));
array_push($cfgX, array('name' => 'theme', 'title' => 'Тема подсветки', 'type' => 'select', 'values' => $themes, 'value' => strval(pluginGetVariable($plugin, 'theme') ?? 'Default')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Настройки SyntaxHighlighter</b>', 'entries' => $cfgX));
// Выбор подключаемых кистей (checkbox per brush)
$cfgX = array();
$brushes = array(
    // Базовые
    'jscript' => 'JavaScript',
    'php'     => 'PHP',
    'sql'     => 'SQL',
    'xml'     => 'HTML/XML',
    'css'     => 'CSS',
    'plain'   => 'Plain text',
    // Дополнительные
    'bash'    => 'Bash/Shell',
    'python'  => 'Python',
    'java'    => 'Java',
    'csharp'  => 'C#',
    'cpp'     => 'C/C++',
    'delphi'  => 'Delphi/Pascal',
    'diff'    => 'Diff/Patch',
    'ruby'    => 'Ruby',
    'perl'    => 'Perl',
    'vb'      => 'VB/VB.Net',
    'powershell' => 'PowerShell',
    'scala'   => 'Scala',
    'groovy'  => 'Groovy',
);
foreach ($brushes as $key => $title) {
    $cfgX[] = array(
        'name'  => 'enable_' . $key,
        'title' => 'Подключать кисть: ' . $title,
        'type'  => 'select',
        'values' => array('1' => 'Да', '0' => 'Нет'),
        'value' => intval(pluginGetVariable($plugin, 'enable_' . $key) ?? 1),
    );
}
array_push($cfg, array('mode' => 'group', 'title' => '<b>Кисти</b>', 'entries' => $cfgX));
if ($_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}
