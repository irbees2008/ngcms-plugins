<?php
if (!defined('NGCMS')) die('HAL');
function plugin_xnews_install($action)
{

    $db_create = array(
        array(
            'table'  => 'xnews',
            'action' => 'cmodify',
            'key'    => 'primary key (id), UNIQUE KEY xnews_legacy_index (legacy_index), KEY xnews_block_name (block_name), KEY xnews_sort_order (sort_order, id)',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL AUTO_INCREMENT'),
                array('action' => 'cmodify', 'name' => 'block_name', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'enabled', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 1'),
                array('action' => 'cmodify', 'name' => 'template', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'visibility_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'visibility_categories', 'type' => 'text', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'category_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'categories', 'type' => 'text', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'main_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'pin_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'fav_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'skip_current', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'extract_embedded_items', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'show_no_news', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'news_count', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 10'),
                array('action' => 'cmodify', 'name' => 'news_skip', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'max_age', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'order_by', 'type' => 'varchar(20)', 'params' => "NOT NULL DEFAULT 'viewed'"),
                array('action' => 'cmodify', 'name' => 'sort_order', 'type' => 'int(11)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'legacy_index', 'type' => 'int(11)', 'params' => 'UNSIGNED DEFAULT NULL'),
            )
        )
    );
    switch ($action) {
        case 'confirm':
            generate_install_page('xnews', 'Сейчас плагин будет установлен');
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('xnews', $db_create, 'install', ($action == 'autoapply') ? true : false)) {
                plugin_mark_installed('xnews');
            } else {
                return false;
            }
            break;
    }

    return true;
}
