<?php
if (!defined('NGCMS')) {
	exit('HAL');
}

LoadPluginLang('csv_reimport', 'config', '', '', ':');
pluginsLoadConfig();

function csvreimport_lang(string $key): string
{
	global $lang;
	$fullKey = 'csv_reimport:' . $key;
	if (!isset($lang[$fullKey])) {
		throw new RuntimeException('Missing csv_reimport language key: ' . $key);
	}

	return $lang[$fullKey];
}

function csvreimport_render_page(array $variables): void
{
	global $twig, $lang, $PHP_SELF;
	$mainKey = 'config/main';
	$viewKey = 'config/import';
	$paths = locatePluginTemplates([$mainKey, $viewKey], 'csv_reimport', 1);
	if (empty($paths[$mainKey]) || empty($paths[$viewKey])) {
		throw new RuntimeException(csvreimport_lang('error_templates_missing'));
	}

	$variables['lang'] = $lang;
	$view = $twig->loadTemplate($paths[$viewKey] . $viewKey . '.tpl');
	$entries = $view->render($variables);
	$layout = $twig->loadTemplate($paths[$mainKey] . $mainKey . '.tpl');
	echo $layout->render([
		'current_title' => csvreimport_lang('title'),
		'entries' => $entries,
		'lang' => $lang,
		'php_self' => $PHP_SELF,
	]);
}

function csvreimport_list_files(string $uploadDir): array
{
	$files = [];
	foreach (glob($uploadDir . 'csv_reimport_*.csv') ?: [] as $path) {
		if (!preg_match('/^csv_reimport_[a-f0-9]{16}\.csv$/', basename($path))) {
			continue;
		}
		$files[] = [
			'name' => basename($path),
			'size' => round(filesize($path) / 1024, 1),
			'modified' => date('Y-m-d H:i', filemtime($path)),
		];
	}
	usort($files, static function ($left, $right) {
		return strcmp($right['name'], $left['name']);
	});

	return $files;
}

function csvreimport_upload(string $uploadDir): array
{
	if (!isset($_FILES['csvfile']) || $_FILES['csvfile']['error'] === UPLOAD_ERR_NO_FILE) {
		return ['type' => 'error', 'text' => csvreimport_lang('msg_file_not_selected')];
	}
	if ($_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
		return ['type' => 'error', 'text' => csvreimport_lang('msg_upload_failed')];
	}
	$originalName = (string)($_FILES['csvfile']['name'] ?? '');
	if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'csv') {
		return ['type' => 'error', 'text' => csvreimport_lang('msg_file_type_invalid')];
	}
	if (!is_writable($uploadDir)) {
		return ['type' => 'error', 'text' => csvreimport_lang('msg_upload_directory_unwritable')];
	}

	$fileName = 'csv_reimport_' . bin2hex(random_bytes(8)) . '.csv';
	if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadDir . $fileName)) {
		return ['type' => 'error', 'text' => csvreimport_lang('msg_upload_failed')];
	}

	return ['type' => 'success', 'text' => sprintf(csvreimport_lang('msg_file_uploaded'), $fileName)];
}

function csvreimport_process_file(string $path): array
{
	global $mysql;
	if (filesize($path) === 0) {
		return ['error' => csvreimport_lang('msg_file_empty')];
	}
	if (!function_exists('xf_configLoad') || !function_exists('xf_decode') || !function_exists('xf_encode')) {
		return ['error' => csvreimport_lang('msg_xfields_unavailable')];
	}
	$xfConfig = xf_configLoad();
	if (!is_array($xfConfig) || !isset($xfConfig['news']) || !is_array($xfConfig['news'])) {
		return ['error' => csvreimport_lang('msg_xfields_unavailable')];
	}

	$handle = fopen($path, 'r');
	if ($handle === false) {
		return ['error' => csvreimport_lang('msg_file_read_failed')];
	}

	$stats = ['updated' => 0, 'not_found' => 0, 'invalid' => 0, 'skipped' => 0];
	$line = 0;
	while (($row = fgetcsv($handle, 0, ';')) !== false) {
		$line++;
		$row = array_map(static function ($value) {
			return mb_convert_encoding((string)$value, 'UTF-8', 'UTF-8,CP1251,Windows-1251');
		}, $row);
		if ($line === 1 && isset($row[0])) {
			$row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
		}

		$title = trim((string)($row[1] ?? ''));
		if ($line === 1 && in_array(mb_strtolower($title, 'UTF-8'), ['title', 'название', 'заголовок'], true)) {
			continue;
		}
		if ($title === '') {
			$stats['skipped']++;
			continue;
		}
		if (count($row) < 8) {
			$stats['invalid']++;
			continue;
		}

		$news = $mysql->record(
			'SELECT id, xfields FROM ' . prefix . '_news WHERE title = ' . db_squote($title) . ' ORDER BY id LIMIT 1'
		);
		if (!$news) {
			$stats['not_found']++;
			continue;
		}

		$incoming = [
			'gosreg' => $row[2],
			'refusalfire' => $row[3],
			'refusal' => $row[4],
			'conformity' => $row[5],
			'voluntaryfire' => $row[6],
			'roomab' => $row[7],
		];
		$oldFields = xf_decode((string)($news['xfields'] ?? ''));
		$xfields = [];
		$storageUpdates = [];
		$hasRequiredValue = true;

		foreach ($xfConfig['news'] as $id => $field) {
			if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$id)) {
				$stats['invalid']++;
				$hasRequiredValue = false;
				break;
			}
			if (array_key_exists($id, $oldFields) && $oldFields[$id] !== '') {
				$xfields[$id] = $oldFields[$id];
				continue;
			}
			if (($field['type'] ?? '') === 'images') {
				continue;
			}

			$value = (string)($incoming[$id] ?? '');
			if ($value !== '') {
				$xfields[$id] = $value;
				if (!empty($field['storage'])) {
					$storageUpdates['xfields_' . $id] = $value;
				}
			} elseif (!empty($field['required'])) {
				$hasRequiredValue = false;
				break;
			}
		}
		if (!$hasRequiredValue) {
			continue;
		}

		$updates = ['xfields = ' . db_squote(xf_encode($xfields))];
		foreach ($storageUpdates as $column => $value) {
			$updates[] = '`' . $column . '` = ' . db_squote($value);
		}
		$result = $mysql->query(
			'UPDATE ' . prefix . '_news SET ' . implode(', ', $updates) . ' WHERE id = ' . (int)$news['id']
		);
		if ($result === false) {
			$stats['invalid']++;
			continue;
		}
		$stats['updated']++;
	}
	fclose($handle);

	if ($line === 0) {
		return ['error' => csvreimport_lang('msg_file_empty')];
	}

	return ['stats' => $stats];
}

$uploadDir = __DIR__ . '/upload/';
if (!is_dir($uploadDir)) {
	@mkdir($uploadDir, 0755, true);
}
$notice = null;
$action = (string)($_REQUEST['action'] ?? '');
if ($action === 'upload') {
	$notice = csvreimport_upload($uploadDir);
} elseif ($action === 'run') {
	$fileName = (string)($_POST['file'] ?? '');
	if (!preg_match('/^csv_reimport_[a-f0-9]{16}\.csv$/', $fileName) || !is_file($uploadDir . $fileName)) {
		$notice = ['type' => 'error', 'text' => csvreimport_lang('msg_file_not_found')];
	} else {
		$result = csvreimport_process_file($uploadDir . $fileName);
		$notice = isset($result['error'])
			? ['type' => 'error', 'text' => $result['error']]
			: ['type' => 'success', 'text' => sprintf(
				csvreimport_lang('msg_import_summary'),
				$result['stats']['updated'],
				$result['stats']['not_found'],
				$result['stats']['invalid'],
				$result['stats']['skipped']
			)];
	}
}

$cfg = [['descr' => csvreimport_lang('plugin_description')]];
if (($_REQUEST['action'] ?? '') === 'commit') {
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete('csv_reimport');
} else {
	csvreimport_render_page([
		'files' => csvreimport_list_files($uploadDir),
		'notice' => $notice,
	]);
}
