<?php
if (!defined('NGCMS')) exit('HAL');
// Ensure ng-helpers is loaded
if (!function_exists('Plugins\\logger')) {
	$ngHelpersPath = __DIR__ . '/../ng-helpers/ng-helpers.php';
	if (file_exists($ngHelpersPath)) {
		require_once $ngHelpersPath;
	}
}

use function Plugins\{logger, array_get, sanitize, get_ip};

$lang = LoadLang('users', 'admin');
LoadPluginLang('clear_config', 'config', '', 'с_с', ':');
switch (array_get($_REQUEST, 'action', '')) {
	case 'delete':
		delete();
		break;
	case 'backup':
		backup();
		break;
	case 'restore':
		restore();
		break;
	case 'restore_confirm':
		restore_confirm();
		break;
	case 'delete_backup':
		delete_backup();
		break;
	default:
		showlist();
}
function showlist()
{

	global $tpl, $PLUGINS, $lang;
	pluginsLoadConfig();
	$ULIB = new urlLibrary();
	$ULIB->loadConfig();
	$plug = array();
	$conf = array();
	if (isset($PLUGINS['active']['active']) && is_array($PLUGINS['active']['active'])) {
		foreach ($PLUGINS['active']['active'] as $key => $row) {
			$plug[] = $key;
			$conf[$key][] = 'active';
		}
	}
	if (isset($PLUGINS['active']['actions']) && is_array($PLUGINS['active']['actions'])) {
		foreach ($PLUGINS['active']['actions'] as $key => $row) {
			if (!is_array($row)) continue;
			foreach ($row as $kkey => $rrow) {
				if (!in_array($kkey, $plug)) $plug[] = $kkey;
				if (!in_array('actions', $conf[$kkey])) $conf[$kkey][] = 'actions';
			}
		}
	}
	if (isset($PLUGINS['active']['installed']) && is_array($PLUGINS['active']['installed'])) {
		foreach ($PLUGINS['active']['installed'] as $key => $row) {
			if (!in_array($key, $plug)) $plug[] = $key;
			$conf[$key][] = 'installed';
		}
	}
	if (isset($PLUGINS['active']['libs']) && is_array($PLUGINS['active']['libs'])) {
		foreach ($PLUGINS['active']['libs'] as $key => $row) {
			if (!in_array($key, $plug)) $plug[] = $key;
			$conf[$key][] = 'libs';
		}
	}
	if (isset($PLUGINS['config']) && is_array($PLUGINS['config'])) {
		foreach ($PLUGINS['config'] as $key => $row) {
			if (!in_array($key, $plug)) $plug[] = $key;
			$conf[$key][] = 'config';
		}
	}
	if (isset($ULIB->CMD) && is_array($ULIB->CMD)) {
		foreach ($ULIB->CMD as $key => $row) {
			if ($key != 'core' && $key != 'static' && $key != 'search' && $key != 'news' && !in_array($key, $plug)) $plug[] = $key;
			$conf[$key][] = 'urlcmd';
		}
	}
	$tpath = locatePluginTemplates(array('conf.list', 'conf.list.row'), 'clear_config');
	$output = '';
	sort($plug);
	foreach ($plug as $key => $row) {
		$pvars['vars']['id'] = $row;
		$pvars['vars']['conf'] = '';
		foreach ($conf[$row] as $kkey => $rrow) {
			$pvars['vars']['conf'] .=
				'<a href="/engine/admin.php?mod=extra-config&plugin=clear_config&action=delete&id=' . $row .
				'&conf=' . $rrow .
				'" title="' . $lang['с_с:' . $rrow] .
				'" onclick="return confirm(\'' . sprintf($lang['с_с:confirm'], $lang['с_с:' . $rrow], $row) . '\');" ' .
				'><img src="/engine/plugins/clear_config/tpl/images/' . $rrow . '.png" /></a>&#160;';
		}
		$tpl->template('conf.list.row', $tpath['conf.list.row']);
		$tpl->vars('conf.list.row', $pvars);
		$output .= $tpl->show('conf.list.row');
	}
	$tvars['vars']['entries'] = $output;
	$tvars['vars']['backup_url'] = '/engine/admin.php?mod=extra-config&plugin=clear_config&action=backup';

	// Получаем правильный путь к conf и backups
	$enginePath = dirname(__DIR__, 2);
	$confPath = $enginePath . DIRECTORY_SEPARATOR . 'conf';
	$backupPath = $enginePath . DIRECTORY_SEPARATOR . 'backups';
	$tvars['vars']['conf_path'] = str_replace('\\', '/', $confPath);
	$tvars['vars']['backup_path'] = str_replace('\\', '/', $backupPath);

	// Получаем список доступных бэкапов
	$backups = array();
	if (is_dir($backupPath)) {
		$files = glob($backupPath . DIRECTORY_SEPARATOR . 'conf_backup_*.zip');
		if ($files) {
			rsort($files); // Сортировка по убыванию (новые первые)
			foreach ($files as $file) {
				$backups[] = array(
					'name' => basename($file),
					'size' => round(filesize($file) / 1024, 2),
					'date' => date('d.m.Y H:i:s', filemtime($file)),
					'url' => '/engine/admin.php?mod=extra-config&plugin=clear_config&action=restore&file=' . urlencode(basename($file)),
					'delete_url' => '/engine/admin.php?mod=extra-config&plugin=clear_config&action=delete_backup&file=' . urlencode(basename($file))
				);
			}
		}
	}
	$tvars['vars']['backups'] = $backups;
	$tvars['vars']['has_backups'] = count($backups) > 0;

	$tpl->template('conf.list', $tpath['conf.list']);
	$tpl->vars('conf.list', $tvars);
	print $tpl->show('conf.list');
}

function delete()
{

	global $PLUGINS, $lang;
	if (!array_get($_REQUEST, 'id', '') || !array_get($_REQUEST, 'conf', '')) {
		msg(array('type' => 'info', 'info' => $lang['с_с:error']));
		logger('Clear config failed: missing id or conf parameter, IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();

		return false;
	}
	$id = secure_html(convert(array_get($_REQUEST, 'id', '')));
	$conf = secure_html(convert(array_get($_REQUEST, 'conf', '')));
	switch ($conf) {
		case 'active':
			if (isset($PLUGINS['active']['active'][$id])) {
				unset($PLUGINS['active']['active'][$id]);
				msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_ok'], 'active', $id)));
				logger('Cleared active config for plugin: ' . sanitize($id, 'string') . ', IP=' . get_ip(), 'info', 'clear_config.log');
			} else msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_er'], 'active', $id)));
			break;
		case 'actions':
			$if_delete = false;
			if (isset($PLUGINS['active']['actions']) && is_array($PLUGINS['active']['actions'])) {
				foreach ($PLUGINS['active']['actions'] as $key => $row) {
					if (isset($PLUGINS['active']['actions'][$key][$id])) {
						unset($PLUGINS['active']['actions'][$key][$id]);
						$if_delete = true;
					}
				}
			}
			if ($if_delete) {
				msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_ok'], 'actions', $id)));
				logger('Cleared actions config for plugin: ' . sanitize($id, 'string') . ', IP=' . get_ip(), 'info', 'clear_config.log');
			} else msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_er'], 'actions', $id)));
			break;
		case 'installed':
			if (isset($PLUGINS['active']['installed'][$id])) {
				unset($PLUGINS['active']['installed'][$id]);
				msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_ok'], 'installed', $id)));
				logger('Cleared installed config for plugin: ' . sanitize($id, 'string') . ', IP=' . get_ip(), 'info', 'clear_config.log');
			} else msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_er'], 'installed', $id)));
			break;
		case 'libs':
			if (isset($PLUGINS['active']['libs'][$id])) {
				unset($PLUGINS['active']['libs'][$id]);
				msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_ok'], 'libs', $id)));
				logger('Cleared libs config for plugin: ' . sanitize($id, 'string') . ', IP=' . get_ip(), 'info', 'clear_config.log');
			} else msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_er'], 'libs', $id)));
			break;
		case 'config':
			if (isset($PLUGINS['config'][$id])) {
				unset($PLUGINS['config'][$id]);
				msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_ok'], 'config', $id)));
				logger('Cleared config for plugin: ' . sanitize($id, 'string') . ', IP=' . get_ip(), 'info', 'clear_config.log');
			} else msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_er'], 'config', $id)));
			break;
		case 'urlcmd':
			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			if (isset($ULIB->CMD[$id])) {
				unset($ULIB->CMD[$id]);
				msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_ok'], 'urlcmd', $id)));
				logger('Cleared urlcmd config for plugin: ' . sanitize($id, 'string') . ', IP=' . get_ip(), 'info', 'clear_config.log');
			} else msg(array('type' => 'info', 'info' => sprintf($lang['с_с:del_er'], 'urlcmd', $id)));
			$ULIB->saveConfig();
			break;
	}
	pluginsSaveConfig();
	savePluginsActiveList();
	showlist();
}

function backup()
{
	global $lang, $config;

	// Используем правильный путь к engine/conf
	$enginePath = dirname(__DIR__, 2); // Поднимаемся из plugins/clear_config в engine
	$confDir = $enginePath . DIRECTORY_SEPARATOR . 'conf';
	$backupDir = $enginePath . DIRECTORY_SEPARATOR . 'backups';

	if (!is_dir($backupDir)) {
		@mkdir($backupDir, 0755, true);
	}

	$timestamp = date('Y-m-d_H-i-s');
	$backupFile = $backupDir . DIRECTORY_SEPARATOR . 'conf_backup_' . $timestamp . '.zip';

	try {
		if (!class_exists('ZipArchive')) {
			throw new Exception('ZipArchive extension not available');
		}

		$zip = new ZipArchive();
		if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
			throw new Exception('Cannot create backup file');
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($confDir),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		$fileCount = 0;
		foreach ($files as $file) {
			if (!$file->isDir()) {
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($confDir) + 1);
				$zip->addFile($filePath, $relativePath);
				$fileCount++;
			}
		}

		$zip->close();

		if (file_exists($backupFile)) {
			$size = filesize($backupFile);
			$sizeKb = round($size / 1024, 2);
			msg(array(
				'type' => 'info',
				'info' => 'Резервная копия успешно создана: ' . basename($backupFile) . ' (' . $fileCount . ' файлов, ' . $sizeKb . ' KB)'
			));
			logger('Config backup created: ' . basename($backupFile) . ', files=' . $fileCount . ', size=' . $sizeKb . 'KB, IP=' . get_ip(), 'info', 'clear_config.log');
		} else {
			throw new Exception('Backup file was not created');
		}
	} catch (Exception $e) {
		msg(array(
			'type' => 'error',
			'info' => 'Ошибка создания резервной копии: ' . sanitize($e->getMessage(), 'string')
		));
		logger('Config backup failed: ' . sanitize($e->getMessage(), 'string') . ', IP=' . get_ip(), 'error', 'clear_config.log');
	}

	showlist();
}

function restore()
{
	global $lang, $tpl;

	$backupFile = array_get($_REQUEST, 'file', '');
	if (!$backupFile) {
		msg(array('type' => 'error', 'info' => 'Файл бэкапа не указан'));
		logger('Restore failed: backup file not specified, IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();
		return;
	}

	$enginePath = dirname(__DIR__, 2);
	$backupDir = $enginePath . DIRECTORY_SEPARATOR . 'backups';
	$backupPath = $backupDir . DIRECTORY_SEPARATOR . basename($backupFile);

	if (!file_exists($backupPath)) {
		msg(array('type' => 'error', 'info' => 'Файл бэкапа не найден'));
		logger('Restore failed: backup file not found: ' . sanitize($backupFile, 'string') . ', IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();
		return;
	}

	$size = round(filesize($backupPath) / 1024, 2);
	$date = date('d.m.Y H:i:s', filemtime($backupPath));

	echo '<div class="container-fluid">';
	echo '<div class="col-sm-12 mt-2">';
	echo '<div class="card card-warning">';
	echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-warning"></i> Подтверждение восстановления</h3></div>';
	echo '<div class="card-body">';
	echo '<p><strong>ВНИМАНИЕ!</strong> Все текущие файлы конфигурации будут заменены файлами из бэкапа.</p>';
	echo '<p>Восстанавливаемый файл: <code>' . htmlspecialchars($backupFile) . '</code></p>';
	echo '<p>Размер: ' . $size . ' KB, Дата создания: ' . $date . '</p>';
	echo '<p><strong>Рекомендация:</strong> Система автоматически создаст текущий бэкап перед восстановлением.</p>';
	echo '<a href="/engine/admin.php?mod=extra-config&plugin=clear_config&action=restore_confirm&file=' . urlencode($backupFile) . '" class="btn btn-danger"><i class="fa fa-refresh"></i> Подтвердить восстановление</a> ';
	echo '<a href="/engine/admin.php?mod=extra-config&plugin=clear_config" class="btn btn-secondary"><i class="fa fa-times"></i> Отмена</a>';
	echo '</div></div></div></div>';
}

function restore_confirm()
{
	global $lang;

	$backupFile = array_get($_REQUEST, 'file', '');
	if (!$backupFile) {
		msg(array('type' => 'error', 'info' => 'Файл бэкапа не указан'));
		logger('Restore confirm failed: backup file not specified, IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();
		return;
	}

	$enginePath = dirname(__DIR__, 2);
	$backupDir = $enginePath . DIRECTORY_SEPARATOR . 'backups';
	$confDir = $enginePath . DIRECTORY_SEPARATOR . 'conf';
	$backupPath = $backupDir . DIRECTORY_SEPARATOR . basename($backupFile);

	if (!file_exists($backupPath)) {
		msg(array('type' => 'error', 'info' => 'Файл бэкапа не найден'));
		logger('Restore confirm failed: backup file not found: ' . sanitize($backupFile, 'string') . ', IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();
		return;
	}

	try {
		if (!class_exists('ZipArchive')) {
			throw new Exception('ZipArchive extension not available');
		}

		// Создаём автоматический бэкап перед восстановлением
		$timestamp = date('Y-m-d_H-i-s');
		$autoBackup = $backupDir . DIRECTORY_SEPARATOR . 'conf_auto_before_restore_' . $timestamp . '.zip';
		$zip = new ZipArchive();
		if ($zip->open($autoBackup, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($confDir),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			foreach ($files as $file) {
				if (!$file->isDir()) {
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen($confDir) + 1);
					$zip->addFile($filePath, $relativePath);
				}
			}
			$zip->close();
			logger('Auto-backup created before restore: ' . basename($autoBackup) . ', IP=' . get_ip(), 'info', 'clear_config.log');
		}

		// Распаковываем выбранный бэкап
		$zip = new ZipArchive();
		if ($zip->open($backupPath) !== TRUE) {
			throw new Exception('Cannot open backup file');
		}

		// Удаляем старые файлы конфигурации
		$oldFiles = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($confDir),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($oldFiles as $file) {
			if ($file->isFile()) {
				@unlink($file->getRealPath());
			}
		}

		// Извлекаем файлы из архива
		$zip->extractTo($confDir);
		$fileCount = $zip->numFiles;
		$zip->close();

		msg(array(
			'type' => 'info',
			'info' => 'Конфигурация успешно восстановлена из: ' . $backupFile . ' (' . $fileCount . ' файлов). Авто-бэкап: ' . basename($autoBackup)
		));
		logger('Config restored from backup: ' . sanitize($backupFile, 'string') . ', files=' . $fileCount . ', IP=' . get_ip(), 'info', 'clear_config.log');
	} catch (Exception $e) {
		msg(array(
			'type' => 'error',
			'info' => 'Ошибка восстановления: ' . sanitize($e->getMessage(), 'string')
		));
		logger('Config restore failed: ' . sanitize($e->getMessage(), 'string') . ', IP=' . get_ip(), 'error', 'clear_config.log');
	}

	showlist();
}

function delete_backup()
{
	global $lang;

	$backupFile = array_get($_REQUEST, 'file', '');
	if (!$backupFile) {
		msg(array('type' => 'error', 'info' => 'Файл бэкапа не указан'));
		logger('Delete backup failed: backup file not specified, IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();
		return;
	}

	$enginePath = dirname(__DIR__, 2);
	$backupDir = $enginePath . DIRECTORY_SEPARATOR . 'backups';
	$backupPath = $backupDir . DIRECTORY_SEPARATOR . basename($backupFile);

	if (!file_exists($backupPath)) {
		msg(array('type' => 'error', 'info' => 'Файл бэкапа не найден'));
		logger('Delete backup failed: backup file not found: ' . sanitize($backupFile, 'string') . ', IP=' . get_ip(), 'warning', 'clear_config.log');
		showlist();
		return;
	}

	try {
		$size = round(filesize($backupPath) / 1024, 2);

		if (@unlink($backupPath)) {
			msg(array(
				'type' => 'info',
				'info' => 'Резервная копия успешно удалена: ' . $backupFile . ' (' . $size . ' KB)'
			));
			logger('Backup deleted: ' . sanitize($backupFile, 'string') . ', size=' . $size . 'KB, IP=' . get_ip(), 'info', 'clear_config.log');
		} else {
			throw new Exception('Cannot delete backup file');
		}
	} catch (Exception $e) {
		msg(array(
			'type' => 'error',
			'info' => 'Ошибка удаления резервной копии: ' . sanitize($e->getMessage(), 'string')
		));
		logger('Backup deletion failed: ' . sanitize($e->getMessage(), 'string') . ', IP=' . get_ip(), 'error', 'clear_config.log');
	}

	showlist();
}
