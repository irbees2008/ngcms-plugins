<?php
// Защита от прямого вызова
if (!defined('NGCMS')) die('HAL');

// Инициализация
pluginsLoadConfig();

// Описание операций для удаления таблиц
$db_update = array(
    array(
        'table' => 'ads_pro',
        'action' => 'drop'
    )
);

if ($_REQUEST['action'] == 'commit') {
    // Основной процесс удаления
    if (fixdb_plugin_install('ads_pro', $db_update, 'deinstall')) {
        // Удаление конфигурации плагина
        $mysql->query("DELETE FROM " . prefix . "_config WHERE module = 'plugin.ads_pro'");
        $mysql->query("DELETE FROM " . prefix . "_config_cat WHERE module = 'plugin.ads_pro'");

        // Отметка плагина как удаленного
        plugin_mark_deinstalled('ads_pro');

        // Перенаправление в раздел плагинов
        header("Location: " . admin_url . "/admin.php?mod=extras");
        exit;
    }
} else {
    // Формирование страницы подтверждения удаления
    generate_install_page(
        'ads_pro',
        '<h4>Подтверждение удаления плагина ads_pro</h4>'
            . 'Будут выполнены следующие действия:<ul>'
            . '<li>Удалена таблица <b>' . prefix . '_ads_pro</b></li>'
            . '<li>Удалены все настройки плагина</li></ul>'
            . '<div class="alert alert-warning">Это действие необратимо!</div>',
        'deinstall'
    );
}
