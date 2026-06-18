<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

$db_update = [
    // No custom DB tables — uses cookie + news table + xfields_data
];

if ($_REQUEST['action'] === 'commit') {
    plugin_mark_installed('compare');
    print_commit_complete('compare');
} else {
    generate_install_page('compare', 'Плагин сравнения товаров. Не требует создания дополнительных таблиц — использует cookies и стандартные таблицы новостей + xfields. Требует: xfields.');
}
