<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

$db_update = array(
    array(
        'table'  => 'helloworld_hits',
        'action' => 'drop',
    ),
);

if ($_REQUEST['action'] == 'commit') {
    if (fixdb_plugin_install('helloworld', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('helloworld');
    }
} else {
    generate_install_page('helloworld', 'Плагин будет удалён, таблица счётчика удалена.', 'deinstall');
}
