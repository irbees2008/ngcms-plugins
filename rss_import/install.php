<?php
if (!defined('NGCMS')) die('HAL');

include_once __DIR__ . '/storage.php';

function plugin_rss_import_install($action)
{
    switch ($action) {
        case 'confirm':
            generate_install_page('rss_import', 'Будут созданы таблица и настройки профилей RSS.');
            break;
        case 'autoapply':
        case 'apply':
            if (!fixdb_plugin_install('rss_import', rss_import_storage_schema(), 'install', $action === 'autoapply')) {
                return false;
            }
            if (!rss_import_storage_table_exists()) {
                return false;
            }
            plugin_mark_installed('rss_import');
            break;
    }

    return true;
}
