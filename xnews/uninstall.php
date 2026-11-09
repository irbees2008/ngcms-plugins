<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
include_once __DIR__ . '/storage.php';

$db_update = array(
    array(
        'table' => 'xnews',
        'action' => 'drop',
    ),
);

if (($_REQUEST['action'] ?? '') === 'commit') {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || !isset($_POST['token'])
        || !is_string($_POST['token'])
        || !hash_equals((string)genUToken('admin.extra-config'), $_POST['token'])) {
        throw new RuntimeException('Invalid Xnews uninstall request token.');
    }

    global $PLUGINS;
    if (mysql_table_exists(xnews_storage_table())
        && !fixdb_plugin_install('xnews', $db_update, 'deinstall')) {
        throw new RuntimeException('Unable to remove the Xnews profiles table.');
    }

    unset($PLUGINS['config']['xnews']);
    if (!pluginsSaveConfig()) {
        throw new RuntimeException('Unable to remove Xnews configuration.');
    }

    if (!plugin_mark_deinstalled('xnews')) {
        throw new RuntimeException('Unable to mark Xnews as deinstalled.');
    }

    header('Location: ' . admin_url . '/admin.php?mod=extras');
    exit;
}

$token = htmlspecialchars((string)genUToken('admin.extra-config'), ENT_QUOTES, 'UTF-8');
$tableName = htmlspecialchars(prefix . '_xnews', ENT_QUOTES, 'UTF-8');
$confirmation = '<input type="hidden" name="token" value="' . $token . '">'
    . '<h4>Подтверждение удаления плагина Xnews</h4>'
    . 'Будут выполнены следующие действия:<ul>'
    . '<li>Удалена таблица <b>' . $tableName . '</b></li>'
    . '<li>Удалены все профили и настройки плагина</li></ul>'
    . '<div class="alert alert-warning">Это действие необратимо!</div>';

generate_install_page('xnews', $confirmation, 'deinstall');
