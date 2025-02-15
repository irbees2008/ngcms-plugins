<?php
if (!defined('NGCMS')) die('HAL');

/**
 * Функция установки плагина Bookmarks.
 *
 * @param string $action Режим действия:
 *                       - confirm: Подтверждение установки
 *                       - apply: Применение установки
 *                       - autoapply: Автоматическая установка
 * @return bool True при успехе, false при ошибке
 */
function plugin_bookmarks_install($action)
{
	// Определение структуры таблицы для создания
	$db_create = array(
		array(
			'table'  => prefix . '_bookmarks', // Имя таблицы
			'action' => 'cmodify',             // Действие (создание/модификация)
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int(8)', 'params' => 'DEFAULT NULL'),
				array('action' => 'cmodify', 'name' => 'news_id', 'type' => 'int(8)', 'params' => 'DEFAULT NULL')
			)
		)
	);

	switch ($action) {
		case 'confirm':
			// Генерация страницы подтверждения установки
			generate_install_page('bookmarks', 'Плагин выводит закладки пользователя');
			break;

		case 'autoapply':
		case 'apply':
			try {
				// Проверка режима автоматической установки
				$isAutoApply = ($action === 'autoapply');

				// Выполнение установки через fixdb_plugin_install
				if (fixdb_plugin_install('bookmarks', $db_create, 'install', $isAutoApply)) {
					plugin_mark_installed('bookmarks');
				} else {
					error_log("Не удалось установить плагин 'bookmarks' для действия: $action");
					return false;
				}
			} catch (Exception $e) {
				// Логирование ошибок
				error_log("Ошибка при установке плагина: " . $e->getMessage());
				return false;
			}
			break;

		default:
			// Логирование неизвестного действия
			error_log("Неизвестное действие: $action");
			return false;
	}

	return true;
}