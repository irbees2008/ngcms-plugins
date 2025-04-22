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
	// Проверяем существование поля
	$field_exists = xmenu_column_exists($mysql, 'category', 'xmenu');

	// Формируем SQL-запрос в зависимости от существования поля
	if ($field_exists) {
		$query = "ALTER TABLE " . prefix . "_category 
                 MODIFY COLUMN xmenu CHAR(9) NOT NULL DEFAULT '_________'";
	} else {
		$query = "ALTER TABLE " . prefix . "_category 
                 ADD COLUMN xmenu CHAR(9) NOT NULL DEFAULT '_________'";
	}

	// Выполняем запрос
	$result = $mysql->query($query);

	if ($result !== false) {
		// Успешная установка
		plugin_mark_installed('xmenu');
		$text = "Плагин <b>xmenu</b> успешно установлен";
		// Явная переадресация после успешной установки
		header("Location: " . admin_url . "/admin.php?mod=extras&plugin=xmenu");
		exit;
	} else {
		// Ошибка установки
		$text = "Ошибка при изменении структуры базы данных";
	}

	generate_install_page('xmenu', $text);
} else {
	// Страница подтверждения установки
	$field_exists = xmenu_column_exists($mysql, 'category', 'xmenu');
	$action = $field_exists ? "обновлено" : "добавлено";

	$text = "Плагин <b>xmenu</b> реализует расширенные возможности генерации меню.<br><br>"
		. "При установке будет $action поле xmenu в таблице категорий.";

	generate_install_page('xmenu', $text);
}
