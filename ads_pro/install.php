<?php
if (!defined('NGCMS')) die('HAL');
function plugin_ads_pro_install($action)
{

    $db_create = array(
        array(
            'table'  => 'ads_pro',
            'action' => 'cmodify',
            'key'    => 'primary key (id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL AUTO_INCREMENT'),
                array('action' => 'cmodify', 'name' => 'block_name', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'description', 'type' => 'text', 'params' => ''),
                array('action' => 'cmodify', 'name' => 'ads_blok', 'type' => 'mediumtext', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'type', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'state', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'start_view', 'type' => 'int(11)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'end_view', 'type' => 'int(11)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'location', 'type' => 'mediumtext', 'params' => ''),
                array('action' => 'cmodify', 'name' => 'sort_order', 'type' => 'int(11)', 'params' => 'NOT NULL DEFAULT 0')
            )
        )
    );
    switch ($action) {
        case 'confirm':
            generate_install_page('ads_pro', 'Cейчас плагин будет установлен');
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('ads_pro', $db_create, 'install', ($action == 'autoapply') ? true : false)) {
                plugin_mark_installed('ads_pro');
            } else {
                return false;
            }
            break;
    }

    return true;
}
