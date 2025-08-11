<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
LoadPluginLang('xmenu', 'config');
// Функция для проверки существования поля
function xmenu_column_exists($mysql, $table, $column)
{
	$result = $mysql->select("SHOW COLUMNS FROM " . prefix . "_" . $table . " LIKE '" . $column . "'");
	return is_array($result) && count($result) > 0;
}
// Основной процесс установки
if ($_REQUEST['action'] == 'commit') {
	// Устанавливаем параметры по умолчанию
	extra_set_param('xmenu', 'localsource', 0);
	extra_set_param('xmenu', 'cache', 1);
	extra_set_param('xmenu', 'cacheExpire', 3600);
	extra_set_param('xmenu', 'menu_count', 9); // Добавляем параметр количества меню
	// Обрабатываем таблицу category
	$field_exists = xmenu_column_exists($mysql, 'category', 'xmenu');
	if ($field_exists) {
		$query = "ALTER TABLE " . prefix . "_category
                 MODIFY COLUMN xmenu VARCHAR(255) NOT NULL DEFAULT '_________'";
	} else {
		$query = "ALTER TABLE " . prefix . "_category
                 ADD COLUMN xmenu VARCHAR(255) NOT NULL DEFAULT '_________'";
	}
	$mysql->query($query);
	// Обрабатываем таблицу static
	$static_field_exists = xmenu_column_exists($mysql, 'static', 'xmenu');
	if ($static_field_exists) {
		$query = "ALTER TABLE " . prefix . "_static
                 MODIFY COLUMN xmenu VARCHAR(255) NOT NULL DEFAULT '_________'";
	} else {
		$query = "ALTER TABLE " . prefix . "_static
                 ADD COLUMN xmenu VARCHAR(255) NOT NULL DEFAULT '_________'";
	}
	$result = $mysql->query($query);
	if ($result !== false) {
		// Успешная установка
		plugin_mark_installed('xmenu');
		$text = "Плагин <b>xmenu</b> успешно установлен<br>Добавлена поддержка динамического количества меню";
		// Явная переадресация после успешной установки
		header("Location: " . admin_url . "/admin.php?mod=extras&plugin=xmenu");
		exit;
	} else {
		// Ошибка установки
		$text = "Ошибка при изменении структуры базы данных: " . $mysql->error;
	}
	generate_install_page('xmenu', $text);
} else {
	// Страница подтверждения установки
	$field_exists = xmenu_column_exists($mysql, 'category', 'xmenu');
	$static_field_exists = xmenu_column_exists($mysql, 'static', 'xmenu');
	$action1 = $field_exists ? "обновлено" : "добавлено";
	$action2 = $static_field_exists ? "обновлено" : "добавлено";
	$text = "Плагин <b>xmenu</b> реализует расширенные возможности генерации меню.<br><br>"
		. "При установке будет:<br>"
		. "- $action1 поле xmenu в таблице категорий (VARCHAR 255)<br>"
		. "- $action2 поле xmenu в таблице статических страниц (VARCHAR 255)<br>"
		. "- Добавлена поддержка динамического количества меню";
	generate_install_page('xmenu', $text);
}
