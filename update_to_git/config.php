<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

LoadPluginLang('update_to_git', 'index', null, 'update', '::');

$documentationButton = '';
$documentationModal = '';
if (function_exists('plugin_documentation_markup')) {
    list($documentationButton, $documentationModal) = plugin_documentation_markup('update_to_git');
}

echo $twig->render('plugins/update_to_git/tpl/index.tpl', [
    'admin_url' => admin_url,
    'token' => genUToken('core.system.update'),
    'documentation_button' => $documentationButton,
    'documentation_modal' => $documentationModal,
]);
