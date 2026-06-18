<?php
/**
 * CSV / YML Import — Admin Config
 * Полностью переписан: навигация, AdminLTE-стиль, исправлен SQL запрос категорий
 */
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();

// ─── Helpers ───────────────────────────────────────────────────────────────

function csvimport_get_xf_fields(): array
{
	if (!function_exists('xf_configLoad')) return [];
	$xfc    = xf_configLoad();
	$result = [];
	foreach ($xfc['news'] ?? [] as $k => $v) {
		$result[$k] = $k . ' — ' . $v['title'];
	}
	return $result;
}

function csvimport_get_xf_image_fields(): array
{
	if (!function_exists('xf_configLoad')) return [];
	$xfc    = xf_configLoad();
	$result = [];
	foreach ($xfc['news'] ?? [] as $k => $v) {
		if (($v['type'] ?? '') === 'images') {
			$result[$k] = $k . ' — ' . $v['title'];
		}
	}
	return $result;
}

function csvimport_read_csv(string $path, string $delimiter = ';'): array
{
	$rows = [];
	if (!file_exists($path)) return $rows;
	$handle = fopen($path, 'r');
	if (!$handle) return $rows;
	while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
		$rows[] = array_map(static function ($v) {
			return mb_convert_encoding($v, 'UTF-8', 'UTF-8,cp1251,windows-1251');
		}, $row);
	}
	fclose($handle);
	return $rows;
}

/**
 * Список категорий для select-а.
 * Исправлено: поля alt и name (не alt_name / cat_name).
 */
function csvimport_get_categories(): array
{
	global $mysql;
	$rows   = $mysql->select("SELECT id, alt, name FROM " . prefix . "_category ORDER BY name", 1) ?: [];
	$result = [0 => '— без категории —'];
	foreach ($rows as $row) {
		$result[(int)$row['id']] = htmlspecialchars($row['name']) . ' (' . htmlspecialchars($row['alt']) . ')';
	}
	return $result;
}

/**
 * Выводит breadcrumb + nav-tabs по образцу AdminLTE / ads_pro.
 * @param string $active  '' = Файлы, 'settings' = Настройки
 */
function csvimport_nav(string $active): void
{
	$base = 'admin.php?mod=extra-config&plugin=csv_import';
	$tabs = [
		''         => '<i class="fa fa-upload mr-1"></i>Файлы',
		'settings' => '<i class="fa fa-cog mr-1"></i>Настройки',
	];
	echo '<div class="container-fluid">';
	echo   '<div class="row mb-2">';
	echo     '<div class="col-sm-6">';
	echo       '<h1 class="m-0 text-dark" style="padding:20px 0 0 0;">CSV / YML Импорт</h1>';
	echo     '</div>';
	echo     '<div class="col-sm-6">';
	echo       '<ol class="breadcrumb float-sm-right">';
	echo         '<li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>';
	echo         '<li class="breadcrumb-item"><a href="admin.php?mod=extras">Плагины</a></li>';
	echo         '<li class="breadcrumb-item active">CSV / YML Импорт</li>';
	echo       '</ol>';
	echo     '</div>';
	echo   '</div>';
	echo '</div>';
	echo '<ul class="nav nav-tabs nav-fill mb-3 d-md-flex d-block" role="tablist">';
	foreach ($tabs as $key => $label) {
		$cls = ($active === $key) ? 'active' : '';
		$url = $base . ($key ? '&action=' . rawurlencode($key) : '');
		echo '<li class="nav-item">';
		echo   '<a href="' . $url . '" class="nav-link ' . $cls . '">' . $label . '</a>';
		echo '</li>';
	}
	echo '</ul>';
}

// ─── Upload dir ────────────────────────────────────────────────────────────
$uploadDir = __DIR__ . '/upload/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// ─── Router ────────────────────────────────────────────────────────────────
$csvAction = (string)($_REQUEST['action'] ?? '');
switch ($csvAction) {
	case 'upload':
		csvimport_action_upload($uploadDir);
		break;
	case 'preview':
		csvimport_action_preview($uploadDir);
		break;
	case 'run':
		csvimport_action_run($uploadDir);
		break;
	case 'delete_file':
		csvimport_action_delete($uploadDir);
		break;
	case 'yml_preview':
		csvimport_action_yml_preview($uploadDir);
		break;
	case 'yml_run':
		csvimport_action_yml_run($uploadDir);
		break;
	case 'settings_save':
		csvimport_action_settings_save();
		break;
	case 'settings':
		csvimport_page_settings();
		break;
	default:
		csvimport_page_main($uploadDir);
}

// ─── Page: файлы (главная) ─────────────────────────────────────────────────
function csvimport_page_main(string $uploadDir): void
{
	$files    = glob($uploadDir . '*.csv') ?: [];
	$fileList = [];
	foreach ($files as $f) {
		$fileList[] = [
			'name'     => basename($f),
			'size'     => round(filesize($f) / 1024, 1) . ' KB',
			'modified' => date('d.m.Y H:i', filemtime($f)),
		];
	}
	$ymlFiles    = array_merge(glob($uploadDir . '*.yml') ?: [], glob($uploadDir . '*.xml') ?: []);
	$ymlFileList = [];
	foreach ($ymlFiles as $f) {
		$ymlFileList[] = [
			'name'     => basename($f),
			'size'     => round(filesize($f) / 1024, 1) . ' KB',
			'modified' => date('d.m.Y H:i', filemtime($f)),
		];
	}
	$xfFields = csvimport_get_xf_fields();
	csvimport_nav('');
	include __DIR__ . '/tpl/main.php';
}

// ─── Page: настройки ───────────────────────────────────────────────────────
function csvimport_page_settings(): void
{
	$xfImageFields = csvimport_get_xf_image_fields();
	$cats          = csvimport_get_categories();
	csvimport_nav('settings');
	include __DIR__ . '/tpl/settings.php';
}

// ─── Action: сохранить настройки ───────────────────────────────────────────
function csvimport_action_settings_save(): void
{
	$allowed = ['default_delimiter', 'default_approve', 'default_mainpage',
	            'yml_image_xfield', 'yml_max_images'];
	foreach ($allowed as $key) {
		if (isset($_POST[$key])) {
			pluginSetVariable('csv_import', $key, (string)$_POST[$key]);
		}
	}
	pluginsSaveConfig();
	msg(['type' => 'ok', 'text' => 'Настройки сохранены']);
	$xfImageFields = csvimport_get_xf_image_fields();
	$cats          = csvimport_get_categories();
	csvimport_nav('settings');
	include __DIR__ . '/tpl/settings.php';
}

// ─── Action: upload CSV / YML ───────────────────────────────────────────────
function csvimport_action_upload(string $uploadDir): void
{
	if (empty($_FILES['csvfile']['tmp_name'])) {
		msg(['type' => 'error', 'text' => 'Файл не выбран']);
		csvimport_page_main($uploadDir);
		return;
	}
	$origName = basename($_FILES['csvfile']['name']);
	if (!preg_match('/\.(csv|yml|xml)$/i', $origName)) {
		msg(['type' => 'error', 'text' => 'Разрешены только .csv и .yml/.xml файлы']);
		csvimport_page_main($uploadDir);
		return;
	}
	$safeName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $origName);
	$dest     = $uploadDir . $safeName;
	if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], $dest)) {
		msg(['type' => 'error', 'text' => 'Ошибка загрузки файла']);
	} else {
		msg(['type' => 'ok', 'text' => 'Файл загружен: ' . $safeName]);
	}
	csvimport_page_main($uploadDir);
}

// ─── Action: preview CSV + column mapping form ──────────────────────────────
function csvimport_action_preview(string $uploadDir): void
{
	$filename  = basename($_REQUEST['file'] ?? '');
	$path      = $uploadDir . $filename;
	if (!$filename || !file_exists($path)) {
		msg(['type' => 'error', 'text' => 'Файл не найден']);
		csvimport_page_main($uploadDir);
		return;
	}
	$delimiter = (string)($_REQUEST['delimiter'] ?? ';') ?: ';';
	$hasHeader = !empty($_REQUEST['has_header']);
	$rows      = csvimport_read_csv($path, $delimiter);
	$preview   = array_slice($rows, 0, 5);
	$headers   = $hasHeader && isset($rows[0]) ? $rows[0] : array_keys($rows[0] ?? []);
	$cats      = csvimport_get_categories();
	$xfFields  = csvimport_get_xf_fields();
	csvimport_nav('');
	include __DIR__ . '/tpl/preview.php';
}

// ─── Action: run CSV import ─────────────────────────────────────────────────
function csvimport_action_run(string $uploadDir): void
{
	global $mysql, $config;
	$filename = basename($_POST['file'] ?? '');
	$path     = $uploadDir . $filename;
	if (!$filename || !file_exists($path)) {
		msg(['type' => 'error', 'text' => 'Файл не найден']);
		csvimport_page_main($uploadDir);
		return;
	}
	$delimiter  = (string)($_POST['delimiter'] ?? ';') ?: ';';
	$hasHeader  = !empty($_POST['has_header']);
	$catId      = intval($_POST['cat_id'] ?? 0);
	$updateMode = !empty($_POST['update_mode']);
	$approve    = intval($_POST['approve'] ?? 1);
	$mainpage   = intval($_POST['mainpage'] ?? 1);
	$newsMap    = [];
	$xfMap      = [];
	foreach ($_POST['map'] ?? [] as $colIdx => $target) {
		$colIdx = intval($colIdx);
		$target = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$target);
		if (!$target || $target === 'skip') continue;
		if (strpos($target, 'xf_') === 0) {
			$xfMap[substr($target, 3)] = $colIdx;
		} else {
			$newsMap[$target] = $colIdx;
		}
	}
	if (!isset($newsMap['title'])) {
		msg(['type' => 'error', 'text' => 'Не задан столбец для поля "Заголовок"']);
		csvimport_page_main($uploadDir);
		return;
	}
	$rows = csvimport_read_csv($path, $delimiter);
	if ($hasHeader) array_shift($rows);
	$created = 0;
	$updated = 0;
	$errors  = 0;
	$now     = time() + (($config['date_adjust'] ?? 0) * 60);
	foreach ($rows as $row) {
		$title = trim($row[$newsMap['title']] ?? '');
		if ($title === '') { $errors++; continue; }
		$altName = isset($newsMap['alt_name']) ? trim($row[$newsMap['alt_name']] ?? '') : '';
		if (!$altName) {
			$altName = function_exists('transliterate')
				? transliterate($title)
				: preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($title));
		}
		$altName  = preg_replace('/[^a-z0-9_\-]/', '-', mb_strtolower($altName));
		$short    = isset($newsMap['short_story']) ? trim($row[$newsMap['short_story']] ?? '') : '';
		$full     = isset($newsMap['full_story'])  ? trim($row[$newsMap['full_story']]  ?? '') : '';
		$tags     = isset($newsMap['tags'])         ? trim($row[$newsMap['tags']]         ?? '') : '';
		$rowCatId = isset($newsMap['category'])
			? intval($row[$newsMap['category']] ?? $catId) : $catId;
		$xfData   = [];
		foreach ($xfMap as $xfField => $colIdx) {
			$xfData[$xfField] = trim($row[$colIdx] ?? '');
		}
		$existing = $updateMode
			? $mysql->record("SELECT id FROM " . prefix . "_news WHERE alt_name=" . db_squote($altName) . " LIMIT 1")
			: null;
		if ($existing) {
			$mysql->query(
				"UPDATE " . prefix . "_news SET
				 title="       . db_squote($title) . ",
				 short_story=" . db_squote($short) . ",
				 full_story="  . db_squote($full)  . ",
				 keywords="    . db_squote($tags)  . ",
				 date="        . db_squote($now)   . "
				 WHERE id="    . intval($existing['id'])
			);
			$newsId = (int)$existing['id'];
			$updated++;
		} else {
			$mysql->query(
				"INSERT INTO " . prefix . "_news
				 (title, short_story, full_story, alt_name, category, approve, mainpage, keywords, date, user_id)
				 VALUES ("
					. db_squote($title)    . ','
					. db_squote($short)    . ','
					. db_squote($full)     . ','
					. db_squote($altName)  . ','
					. db_squote($rowCatId) . ','
					. db_squote($approve)  . ','
					. db_squote($mainpage) . ','
					. db_squote($tags)     . ','
					. db_squote($now)      . ',0)'
			);
			$newsId = (int)$mysql->insert_id();
			$created++;
		}
		if ($newsId && count($xfData) && function_exists('xf_saveNewsData')) {
			xf_saveNewsData($newsId, $xfData);
		}
	}
	msg(['type' => 'ok', 'text' => "Импорт завершён. Создано: $created, обновлено: $updated, ошибок: $errors"]);
	csvimport_page_main($uploadDir);
}

// ─── Action: delete file ────────────────────────────────────────────────────
function csvimport_action_delete(string $uploadDir): void
{
	$filename = basename($_REQUEST['file'] ?? '');
	$path     = $uploadDir . $filename;
	if ($filename && file_exists($path) && preg_match('/\.(csv|yml|xml)$/i', $filename)) {
		@unlink($path);
		msg(['type' => 'ok', 'text' => 'Файл удалён']);
	}
	csvimport_page_main($uploadDir);
}

// ─── YML helpers ────────────────────────────────────────────────────────────

function csvimport_parse_yml(string $path): ?array
{
	libxml_use_internal_errors(true);
	$xml = @simplexml_load_file($path);
	if (!$xml) return null;
	$result = [
		'shop_name'  => (string)($xml->shop->name ?? ''),
		'categories' => [],
		'offers'     => [],
		'fields'     => [],
		'params'     => [],
	];
	foreach ($xml->shop->categories->category ?? [] as $cat) {
		$id                        = (string)$cat['id'];
		$result['categories'][$id] = (string)$cat;
	}
	$stdFields  = ['name','url','price','oldprice','currencyId','categoryId','description',
	               'vendor','model','barcode','vendorCode','count','weight','volume',
	               'typePrefix','country_of_origin'];
	$seenFields = [];
	$seenParams = [];
	foreach ($xml->shop->offers->offer ?? [] as $offer) {
		$item = [
			'id'        => (string)($offer['id'] ?? ''),
			'available' => (string)($offer['available'] ?? 'true'),
			'pictures'  => [],
			'params'    => [],
		];
		foreach ($stdFields as $f) {
			if (isset($offer->$f)) {
				$item[$f]       = (string)$offer->$f;
				$seenFields[$f] = true;
			}
		}
		foreach ($offer->picture as $pic) {
			$item['pictures'][] = (string)$pic;
		}
		foreach ($offer->param as $param) {
			$name                  = (string)$param['name'];
			$item['params'][$name] = (string)$param;
			$seenParams[$name]     = true;
		}
		$result['offers'][] = $item;
	}
	$result['fields'] = array_keys($seenFields);
	$result['params'] = array_keys($seenParams);
	return $result;
}

function csvimport_download_image(string $url, int $newsId, string $xfField): bool
{
	global $mysql, $config;
	if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
	$scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
	if (!in_array($scheme, ['http', 'https'], true)) return false;
	$origName = basename(parse_url($url, PHP_URL_PATH) ?? '');
	if (!preg_match('/\.(jpe?g|png|gif|webp)$/i', $origName)) {
		$origName .= '.jpg';
	}
	$dsnPath   = sprintf('%04d/%02d', (int)(floor($newsId / 1000) * 1000), (int)(floor($newsId / 100) * 100));
	$targetDir = rtrim($config['attach_dir'], '/\\') . DIRECTORY_SEPARATOR
		. str_replace('/', DIRECTORY_SEPARATOR, $dsnPath);
	if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
	$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'jpg';
	$base     = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
	$safeName = $base . '_' . $newsId . '_' . substr(md5($url), 0, 6) . '.' . $ext;
	$destFile = $targetDir . DIRECTORY_SEPARATOR . $safeName;
	if (!file_exists($destFile)) {
		$ctx  = stream_context_create([
			'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0', 'follow_location' => 1, 'max_redirects' => 3],
			'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
		]);
		$data = @file_get_contents($url, false, $ctx);
		if ($data === false || strlen($data) < 100) return false;
		if (file_put_contents($destFile, $data) === false) return false;
	}
	$info = @getimagesize($destFile);
	if (!$info) { @unlink($destFile); return false; }
	[$width, $height] = $info;
	$exists = $mysql->record(
		"SELECT id FROM " . prefix . "_images WHERE linked_id=" . intval($newsId)
			. " AND plugin='xfields' AND pidentity=" . db_squote($xfField)
			. " AND name=" . db_squote($safeName) . " LIMIT 1"
	);
	if ($exists) return true;
	$mysql->query(
		"INSERT INTO " . prefix . "_images
		 (name, orig_name, folder, date, user, category, linked_ds, linked_id,
		  plugin, pidentity, description, width, height, preview, p_width, p_height, stamp, storage)
		 VALUES ("
			. db_squote($safeName) . ','
			. db_squote($origName) . ','
			. db_squote($dsnPath)  . ','
			. db_squote(time())    . ','
			. db_squote('admin')   . ','
			. db_squote('default') . ','
			. '1,'
			. intval($newsId)      . ','
			. db_squote('xfields') . ','
			. db_squote($xfField)  . ','
			. db_squote('')        . ','
			. intval($width)       . ','
			. intval($height)      . ','
			. '0,0,0,0,1)'
	);
	return (bool)$mysql->affected_rows();
}

// ─── Action: YML preview ────────────────────────────────────────────────────
function csvimport_action_yml_preview(string $uploadDir): void
{
	$filename = basename($_REQUEST['file'] ?? '');
	$path     = $uploadDir . $filename;
	if (!$filename || !file_exists($path)) {
		msg(['type' => 'error', 'text' => 'Файл не найден']);
		csvimport_page_main($uploadDir);
		return;
	}
	$data = csvimport_parse_yml($path);
	if ($data === null) {
		$errors = libxml_get_errors();
		$errMsg = $errors ? ': ' . $errors[0]->message : '';
		msg(['type' => 'error', 'text' => 'Ошибка разбора YML файла' . $errMsg]);
		csvimport_page_main($uploadDir);
		return;
	}
	$cats     = csvimport_get_categories();
	$xfFields = csvimport_get_xf_fields();
	csvimport_nav('');
	include __DIR__ . '/tpl/yml_preview.php';
}

// ─── Action: YML run import ─────────────────────────────────────────────────
function csvimport_action_yml_run(string $uploadDir): void
{
	global $mysql, $config;
	$filename = basename($_POST['file'] ?? '');
	$path     = $uploadDir . $filename;
	if (!$filename || !file_exists($path)) {
		msg(['type' => 'error', 'text' => 'Файл не найден']);
		csvimport_page_main($uploadDir);
		return;
	}
	$data = csvimport_parse_yml($path);
	if ($data === null) {
		msg(['type' => 'error', 'text' => 'Ошибка разбора YML файла']);
		csvimport_page_main($uploadDir);
		return;
	}
	$defaultCatId   = intval($_POST['cat_id'] ?? 0);
	$updateMode     = !empty($_POST['update_mode']);
	$approve        = intval($_POST['approve'] ?? 1);
	$mainpage       = intval($_POST['mainpage'] ?? 1);
	$downloadImgs   = !empty($_POST['download_images']);
	$imageXfField   = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($_POST['image_xf_field'] ?? ''));
	$maxImgPerOffer = max(1, min(10, intval($_POST['max_images'] ?? 3)));
	$fieldMap       = [];
	foreach ($_POST['field_map'] ?? [] as $src => $dest) {
		$src  = (string)$src;
		$dest = preg_replace('/[^a-zA-Z0-9_:]/', '', (string)$dest);
		if ($dest && $dest !== 'skip') $fieldMap[$src] = $dest;
	}
	$catMap = [];
	foreach ($_POST['cat_map'] ?? [] as $ymlId => $ngId) {
		$catMap[(string)$ymlId] = intval($ngId);
	}
	$newsDbFields = ['title', 'short_story', 'full_story', 'alt_name', 'tags'];
	$now          = time() + (($config['date_adjust'] ?? 0) * 60);
	$created      = 0;
	$updated      = 0;
	$errors       = 0;
	foreach ($data['offers'] as $offer) {
		$newsData = [];
		$xfData   = [];
		foreach ($fieldMap as $src => $dest) {
			$val = strpos($src, 'param:') === 0
				? ($offer['params'][substr($src, 6)] ?? '')
				: ($offer[$src] ?? '');
			$val = trim((string)$val);
			if (strpos($dest, 'xf_') === 0) {
				$xfData[substr($dest, 3)] = $val;
			} elseif (in_array($dest, $newsDbFields, true)) {
				$newsData[$dest] = $val;
			}
		}
		if (empty($newsData['title'])) {
			$newsData['title'] = trim((string)($offer['name'] ?? ''));
		}
		if ($newsData['title'] === '') { $errors++; continue; }
		if (empty($newsData['alt_name'])) {
			$newsData['alt_name'] = function_exists('transliterate')
				? transliterate($newsData['title'])
				: preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($newsData['title']));
		}
		$newsData['alt_name'] = preg_replace('/[^a-z0-9_\-]/', '-', mb_strtolower($newsData['alt_name']));
		$ymlCatId             = (string)($offer['categoryId'] ?? '');
		$rowCatId             = $catMap[$ymlCatId] ?? $defaultCatId;
		$existing             = $updateMode
			? $mysql->record("SELECT id FROM " . prefix . "_news WHERE alt_name=" . db_squote($newsData['alt_name']) . " LIMIT 1")
			: null;
		if ($existing) {
			$mysql->query(
				"UPDATE " . prefix . "_news SET
				 title="       . db_squote($newsData['title'])            . ",
				 short_story=" . db_squote($newsData['short_story'] ?? '') . ",
				 full_story="  . db_squote($newsData['full_story']  ?? '') . ",
				 keywords="    . db_squote($newsData['tags']         ?? '') . ",
				 date="        . db_squote($now)                           . "
				 WHERE id="    . intval($existing['id'])
			);
			$newsId = (int)$existing['id'];
			$updated++;
		} else {
			$mysql->query(
				"INSERT INTO " . prefix . "_news
				 (title, short_story, full_story, alt_name, category, approve, mainpage, keywords, date, user_id)
				 VALUES ("
					. db_squote($newsData['title'])             . ','
					. db_squote($newsData['short_story'] ?? '') . ','
					. db_squote($newsData['full_story']  ?? '') . ','
					. db_squote($newsData['alt_name'])          . ','
					. intval($rowCatId)                         . ','
					. intval($approve)                          . ','
					. intval($mainpage)                         . ','
					. db_squote($newsData['tags'] ?? '')        . ','
					. db_squote($now) . ',0)'
			);
			$newsId = (int)$mysql->insert_id();
			$created++;
		}
		if ($newsId && count($xfData) && function_exists('xf_saveNewsData')) {
			xf_saveNewsData($newsId, $xfData);
		}
		if ($newsId && $downloadImgs && $imageXfField && !empty($offer['pictures'])) {
			$count = 0;
			foreach ($offer['pictures'] as $picUrl) {
				if ($count >= $maxImgPerOffer) break;
				if (csvimport_download_image($picUrl, $newsId, $imageXfField)) $count++;
			}
		}
	}
	msg(['type' => 'ok', 'text' => "YML импорт завершён. Создано: $created, обновлено: $updated, ошибок: $errors"]);
	csvimport_page_main($uploadDir);
}
