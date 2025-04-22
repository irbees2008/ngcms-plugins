<?php
// Защита от прямого вызова
if (!defined('NGCMS')) die('HAL');

// Инициализация
pluginsLoadConfig();
LoadPluginLang('xmenu', 'config');

// Функция для проверки существования поля (такая же как в установке)
function xmenu_column_exists($mysql, $table, $column)
{
    $result = $mysql->select("SHOW COLUMNS FROM " . prefix . "_" . $table . " LIKE '" . $column . "'");
    return is_array($result) && count($result) > 0;
}

if ($_REQUEST['action'] == 'commit') {
    // Проверяем существование поля перед удалением
    $field_exists = xmenu_column_exists($mysql, 'category', 'xmenu');

    if ($field_exists) {
        // Пытаемся удалить поле
        $result = $mysql->query("ALTER TABLE " . prefix . "_category DROP COLUMN xmenu");

        if ($result === false) {
            // Ошибка удаления поля
            $text = "Ошибка при удалении поля xmenu из таблицы категорий";
            generate_install_page('xmenu', $text);
            exit;
        }
    }

    // Удаление конфигурации плагина
    $mysql->query("DELETE FROM " . prefix . "_config WHERE module = 'plugin.xmenu'");
    $mysql->query("DELETE FROM " . prefix . "_config_cat WHERE module = 'plugin.xmenu'");

    // Отметка плагина как удаленного
    plugin_mark_deinstalled('xmenu');

    // Перенаправление в раздел плагинов
    header("Location: " . admin_url . "/admin.php?mod=extras");
    exit;
} else {
    // Формирование страницы подтверждения удаления
    generate_install_page(
        'xmenu',
        '<h4>Подтверждение удаления плагина xmenu</h4>'
            . 'Будут выполнены следующие действия:<ul>'
            . '<li>Удалено поле <b>xmenu</b> из таблицы <b>' . prefix . '_category</b></li>'
            . '<li>Удалены все настройки плагина</li></ul>'
            . '<div class="alert alert-warning">Это действие необратимо!</div>',
        'deinstall'
    );
}
