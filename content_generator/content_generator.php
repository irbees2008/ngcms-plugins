<?php
// Защита от прямого доступа
if (!defined('NGCMS')) die('HAL');

use function Plugins\{array_get, logger};

register_plugin_page('content_generator', '', 'plugin_content_generator', 0);
/**
 * Создание alt_name из заголовка (транслитерация)
 */
function createAltName($title)
{
	// Простая транслитерация
	$ru = ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' '];
	$en = ['a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shch', '', 'y', '', 'e', 'yu', 'ya', '-'];
	$title = mb_strtolower($title, 'UTF-8');
	$title = str_replace($ru, $en, $title);
	$title = preg_replace('/[^a-z0-9\-]/', '', $title);
	$title = preg_replace('/-+/', '-', $title);
	$title = trim($title, '-');
	return substr($title, 0, 200);
}
/**
 * Прямое добавление новости в БД (фолбэк, если addNews не работает)
 */
function addNewsDirectGenerator($title, $content)
{
	global $mysql, $userROW;
	$alt_name = createAltName($title);
	$postdate = time();
	$SQL = [
		'title' => $title,
		'alt_name' => $alt_name,
		'content' => $content,
		'postdate' => $postdate,
		'editdate' => $postdate,
		'author' => $userROW['name'],
		'author_id' => $userROW['id'],
		'catid' => 0,
		'flags' => 0, // Plain text
		'approve' => 1, // Опубликовано
		'mainpage' => 1,
		'favorite' => 0,
		'pinned' => 0,
		'catpinned' => 0,
		'description' => '',
		'keywords' => '',
		'xfields' => '',
	];
	$vnames = [];
	$vparams = [];
	foreach ($SQL as $k => $v) {
		$vnames[] = $k;
		$vparams[] = db_squote($v);
	}
	$mysql->query('INSERT INTO ' . prefix . '_news (' . implode(',', $vnames) . ') VALUES (' . implode(',', $vparams) . ')');
	$id = $mysql->result('SELECT LAST_INSERT_ID() as id');
	return $id ? $id : false;
}
function generateContent($type, $count)
{
	include_once(root . 'includes/inc/lib_admin.php');
	include_once(__DIR__ . '/lib/Faker/autoload.php');
	include_once(__DIR__ . '/lib/addStatics.php');
	global $SQL, $mysql, $userROW; // Получаем объекты
	$faker = Faker\Factory::create('ru_RU');
	$generated = 0;
	for ($i = 0; $i < $count; $i++) {
		$title = $faker->realText(30, 1);
		$content = $faker->realText();
		if ($type === 'news') {
			$_REQUEST['title'] = $title;
			$_REQUEST['ng_news_content'] = $content;
			$_REQUEST['ng_news_content_short'] = $content;
			$_REQUEST['ng_news_content_full'] = '';
			$_REQUEST['approve'] = 1;
			$_REQUEST['mainpage'] = 1;
			$_REQUEST['catid'] = 0;
			$_POST['category'] = 0;
			$_REQUEST['category'] = 0;
			$result = addNews(['no.token' => true, 'no.editurl' => true, 'no.meta' => true]);
			if ($result) {
				$generated++;
			} else {
				// Фолбэк: прямая вставка в БД
				$directId = addNewsDirectGenerator($title, $content);
				if ($directId) {
					$generated++;
				}
			}
		} elseif ($type === 'static') {
			// Подготовка данных для addStatics()
			$_REQUEST['title'] = $title;
			$_REQUEST['content'] = $content;
			$_REQUEST['flag_published'] = 1;
			// Вызов addStatics()
			$result = addStatics(['no.token' => true]);
			if ($result) {
				$generated++;
			}
		}
	}
	return ['generated' => $generated, 'user' => $userROW['name']];
}
function plugin_content_generator()
{
	global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $SYSTEM_FLAGS;
	// Всегда блокируем вывод шаблона для этого плагина
	$SUPRESS_TEMPLATE_SHOW = 1;
	$SUPRESS_MAINBLOCK_SHOW = 1;
	// Если это запрос на сохранение настроек - не обрабатываем здесь (обрабатывается в config.php)
	if (array_get($_POST, 'save', '') == '1' || array_get($_REQUEST, 'save', '') == '1') {
		return;
	}
	// Проверяем наличие actionName - если его нет, это не AJAX-запрос генерации
	$action = array_get($_REQUEST, 'actionName', '');
	if (empty($action)) {
		// Возвращаем пустой JSON для пустых запросов
		@header('Content-type: application/json; charset=utf-8');
		echo json_encode(['error' => 'No action specified']);
		return;
	}
	@header('Content-type: application/json; charset=utf-8');
	$SYSTEM_FLAGS['http.headers'] = [
		'content-type'  => 'application/json; charset=utf-8',
		'cache-control' => 'private',
	];
	// Загружаем конфигурацию плагинов из базы
	pluginsLoadConfig();
	// Читаем настройки через API
	$newsCountRaw = pluginGetVariable('content_generator', 'news_count');
	$staticCountRaw = pluginGetVariable('content_generator', 'static_count');
	$maxAllowedRaw = pluginGetVariable('content_generator', 'max_allowed');
	// Логируем что прочитали
	logger('AJAX after pluginsLoadConfig: newsCountRaw="' . $newsCountRaw . '", staticCountRaw="' . $staticCountRaw . '"', 'info', 'content_generator.log');
	$newsCount   = ($newsCountRaw !== null && $newsCountRaw !== '' && $newsCountRaw !== false) ? intval($newsCountRaw) : 10;
	$staticCount = ($staticCountRaw !== null && $staticCountRaw !== '' && $staticCountRaw !== false) ? intval($staticCountRaw) : 5;
	$maxAllowed  = ($maxAllowedRaw !== null && $maxAllowedRaw !== '' && $maxAllowedRaw !== false) ? intval($maxAllowedRaw) : 100;
	// Определяем количество на основании действия
	switch ($action) {
		case 'generate_news':
			$count = $newsCount;
			break;
		case 'generate_static':
			$count = $staticCount;
			break;
		default:
			$count = 0;
	}
	if ($count < 1) {
		logger('Error: Invalid count - action=' . $action . ', count=' . $count . ', ip=' . get_ip(), 'error', 'content_generator.log');
		echo json_encode([
			'error' => 'Invalid count (config value)',
			'debug' => [
				'action' => $action,
				'count' => $count,
				'newsCount' => $newsCount,
				'staticCount' => $staticCount,
				'maxAllowed' => $maxAllowed
			]
		]);
		exit();
	}
	try {
		// Применяем лимит из конфигурации
		if ($count > $maxAllowed) {
			$count = $maxAllowed;
		}
		switch ($action) {
			case 'generate_news':
				$result = generateContent('news', $count);
				break;
			case 'generate_static':
				$result = generateContent('static', $count);
				break;
			default:
				echo json_encode(['error' => 'Invalid action']);
				exit();
		}
		ob_end_clean();
		echo json_encode([
			'status' => 'success',
			'count' => $count,
			'generated' => $result['generated'],
			'user' => $result['user'],
			'action' => $action,
			'debug' => [
				'newsCountRaw' => $newsCountRaw,
				'newsCount' => $newsCount,
				'staticCount' => $staticCount,
				'maxAllowed' => $maxAllowed
			]
		]);
	} catch (Exception $e) {
		error_log("Ошибка в plugin_content_generator: " . $e->getMessage());
		echo json_encode(['error' => $e->getMessage()]);
	}
	exit();
}
