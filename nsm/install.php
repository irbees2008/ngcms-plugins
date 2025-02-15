<?php
// Защита от попыток взлома
if (!defined('NGCMS')) die('HAL');

/**
 * Скрипт установки плагина NSM.
 *
 * @param string $action Возможные режимы действия:
 *                       - confirm: Экран подтверждения установки
 *                       - apply: Применение установки с ручным подтверждением
 *                       - autoapply: Автоматическая установка [INSTALL script]
 * @return bool True при успехе, false при ошибке
 */
function plugin_nsm_install($action)
{
	global $lang;

	try {
		// Выполняем запрошенное действие
		switch ($action) {
			case 'confirm':
				// Генерируем страницу подтверждения установки
				generate_install_page('nsm', "Установка NSM");
				break;

			case 'autoapply':
			case 'apply':
				// Регистрируем команды для URL-библиотеки
				$ULIB = new urlLibrary();
				$ULIB->loadConfig();
				$ULIB->registerCommand('nsm', '', [
					'descr' => ['russian' => 'Список']
				]);
				$ULIB->registerCommand('nsm', 'add', [
					'descr' => ['russian' => 'Добавление']
				]);
				$ULIB->registerCommand('nsm', 'edit', [
					'descr' => ['russian' => 'Редактирование']
				]);
				$ULIB->registerCommand('nsm', 'del', [
					'descr' => ['russian' => 'Удаление']
				]);
				$ULIB->saveConfig();

				// Устанавливаем плагин в базу данных
				if (!fixdb_plugin_install('nsm', 'install')) {
					throw new Exception("Не удалось установить плагин 'nsm'");
				}

				// Помечаем плагин как установленный
				plugin_mark_installed('nsm');
				break;

			default:
				// Логируем неизвестное действие
				error_log("Неизвестное действие: $action");
				return false;
		}
	} catch (Exception $e) {
		// Логируем ошибку и возвращаем false
		error_log("Ошибка при установке плагина: " . $e->getMessage());
		return false;
	}

	// Возвращаем true в случае успешной установки
	return true;
}