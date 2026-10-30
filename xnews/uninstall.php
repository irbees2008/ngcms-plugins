<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
include_once __DIR__ . '/storage.php';

if (($_REQUEST['action'] ?? '') === 'commit') {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || !isset($_POST['token'])
        || !is_string($_POST['token'])
        || !hash_equals((string)genUToken('admin.extra-config'), $_POST['token'])) {
        throw new RuntimeException('Invalid Xnews uninstall request token.');
    }

    global $PLUGINS, $tpl, $PHP_SELF;
    xnews_storage_execute('DROP TABLE IF EXISTS ' . xnews_storage_table(), 'Unable to remove the Xnews profiles table.');
    unset($PLUGINS['config']['xnews']);
    if (!pluginsSaveConfig()) {
        throw new RuntimeException('Unable to remove Xnews configuration.');
    }
    if (!plugin_mark_deinstalled($plugin)) {
        throw new RuntimeException('Unable to mark Xnews as deinstalled.');
    }

    $tpath = locatePluginTemplates(array('conf.uninstall'), 'xnews', 1);
    $tpl->template('conf.uninstall', $tpath['conf.uninstall']);
    $tpl->vars('conf.uninstall', array('vars' => array(
        'mode' => 'success',
        'php_self' => $PHP_SELF,
    )));
    print $tpl->show('conf.uninstall');
} else {
    global $tpl, $PHP_SELF;
    $tpath = locatePluginTemplates(array('conf.uninstall'), 'xnews', 1);
    $tpl->template('conf.uninstall', $tpath['conf.uninstall']);
    $tpl->vars('conf.uninstall', array('vars' => array(
        'mode' => 'confirm',
        'php_self' => $PHP_SELF,
        'token' => htmlspecialchars((string)genUToken('admin.extra-config'), ENT_QUOTES, 'UTF-8'),
        'table_name' => xnews_storage_table(),
    )));
    print $tpl->show('conf.uninstall');
}
