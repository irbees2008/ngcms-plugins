<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

function plugin_helloworld_install($action)
{
    // Таблица для счётчика посещений страницы плагина
    $db_update = array(
        array(
            'table'  => 'helloworld_hits',
            'action' => 'cmodify',
            'key'    => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'cnt', 'type' => 'int', 'params' => "not null default '0'"),
            ),
        ),
    );

    switch ($action) {
        case 'confirm':
            generate_install_page('helloworld', 'Будет установлена таблица счётчика посещений.');
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('helloworld', $db_update, 'install', ($action == 'autoapply') ? true : false)) {
                // Параметры по умолчанию
                extra_set_param('helloworld', 'add_suffix', 1);
                extra_commit_changes();
                plugin_mark_installed('helloworld');
            } else {
                return false;
            }
            break;
    }
    return true;
}
