<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
use function Plugins\{logger, cache_get, cache_put};
include_once root . "/includes/news.php";
register_plugin_page('rss_export', '', 'plugin_rss_export', 0);
register_plugin_page('rss_export', 'category', 'plugin_rss_export_category', 0);
// Обработчик для URL вида {category}.xml
add_act('index', 'rss_export_check_category_url', 1);
function rss_export_check_category_url()
{
	global $catz;
	// Получаем запрошенный URL
	$requestUri = $_SERVER['REQUEST_URI'] ?? '';
	// Проверяем формат: /category.xml или category.xml
	if (preg_match('#/([a-z0-9_-]+)\.xml$#i', $requestUri, $matches)) {
		$catName = $matches[1];
		// Пропускаем rss.xml - это главная лента
		if ($catName === 'rss') {
			return;
		}
		// Проверяем существует ли такая категория (с учетом того что $catz может быть не загружен)
		if (!empty($catz) && isset($catz[$catName])) {
			echo "<!-- DEBUG: Generating RSS for category: $catName -->\n";
			// Вызываем генерацию RSS для категории
			plugin_rss_export_generate($catName);
			exit;
		} else {
			echo "<!-- DEBUG: Category not found or \$catz not loaded -->\n";
		}
	}
}
function plugin_rss_export()
{
	plugin_rss_export_generate();
}
function plugin_rss_export_category($params)
{
	$cat = '';
	if (isset($params['category']) && $params['category'] !== '') {
		$cat = $params['category'];
	} elseif (!empty($_REQUEST['category'])) {
		$cat = $_REQUEST['category'];
	}
	plugin_rss_export_generate($cat);
}
function rss_export_accepts_gzip($acceptEncoding)
{
	$gzipQuality = null;
	$wildcardQuality = null;
	foreach (explode(',', $acceptEncoding) as $encoding) {
		$parts = explode(';', $encoding);
		$name = strtolower(trim(array_shift($parts)));
		if ($name !== 'gzip' && $name !== '*') {
			continue;
		}
		$quality = 1.0;
		foreach ($parts as $parameter) {
			if (preg_match('/^\s*q\s*=\s*(.*?)\s*$/i', $parameter, $match)) {
				$quality = is_numeric($match[1]) ? (float)$match[1] : 0.0;
				if ($quality < 0 || $quality > 1) {
					$quality = 0.0;
				}
				break;
			}
		}
		if ($name === 'gzip') {
			$gzipQuality = $quality;
		} else {
			$wildcardQuality = $quality;
		}
	}
	return ($gzipQuality ?? $wildcardQuality ?? 0) > 0;
}
function rss_export_cached_last_modified($cacheFileName)
{
	$cacheDir = get_plugcache_dir('rss_export');
	$cachePath = $cacheDir . $cacheFileName;
	$lastModified = @filemtime($cachePath);
	return ($lastModified === false) ? time() : (int)$lastModified;
}
function rss_export_image_mime_type($fileName)
{
	$mimeTypes = array(
		'avif' => 'image/avif',
		'bmp' => 'image/bmp',
		'gif' => 'image/gif',
		'heic' => 'image/heic',
		'heif' => 'image/heif',
		'ico' => 'image/vnd.microsoft.icon',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'svg' => 'image/svg+xml',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'webp' => 'image/webp',
	);
	$extension = strtolower(pathinfo((string)$fileName, PATHINFO_EXTENSION));
	return $mimeTypes[$extension] ?? 'application/octet-stream';
}
function rss_export_send_response($output, $lastModified)
{
	$etagHash = hash('sha256', $output);
	$etag = 'W/"' . $etagHash . '"';
	$lastModified = gmdate('D, d M Y H:i:s', max(0, (int)$lastModified)) . ' GMT';
	header('ETag: ' . $etag);
	header('Last-Modified: ' . $lastModified);
	header('Vary: Accept-Encoding', false);

	$notModified = false;
	if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
		$ifNoneMatch = trim((string)$_SERVER['HTTP_IF_NONE_MATCH']);
		$notModified = ($ifNoneMatch === '*')
			|| (bool)preg_match('/(?:^|,)\s*(?:W\/)?"' . preg_quote($etagHash, '/') . '"\s*(?:,|$)/', $ifNoneMatch);
	} elseif (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$ifModifiedSince = strtotime((string)$_SERVER['HTTP_IF_MODIFIED_SINCE']);
		$lastModifiedTime = strtotime($lastModified);
		$notModified = ($ifModifiedSince !== false && $lastModifiedTime !== false && $ifModifiedSince >= $lastModifiedTime);
	}
	if ($notModified) {
		http_response_code(304);
		exit;
	}

	if (extension_loaded('zlib') && function_exists('gzencode')
		&& rss_export_accepts_gzip((string)($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''))) {
		$compressed = gzencode($output, 6, FORCE_GZIP);
		if ($compressed !== false) {
			header('Content-Encoding: gzip');
			$output = $compressed;
		}
	}
	if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'HEAD') {
		echo $output;
	}
	exit;
}
function plugin_rss_export_generate($catname = '')
{
	global $lang, $PFILTERS, $template, $config, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $mysql, $catz, $parse, $userROW;
	// Агрессивная очистка буферов перед выводом XML
	while (ob_get_level()) {
		ob_end_clean();
	}
	// Отключаем вывод ошибок в XML
	@ini_set('display_errors', '0');
	// Устанавливаем заголовок Content-Type
	header('Content-Type: application/rss+xml; charset=utf-8');
	actionDisable('index');
	$SUPRESS_TEMPLATE_SHOW = 1;
	$SUPRESS_MAINBLOCK_SHOW = 1;
	if (($catname != '') && (!isset($catz[$catname]))) {
		header('HTTP/1.1 404 Not found');
		exit;
	}
	$xcat = (($catname != '') && isset($catz[$catname])) ? $catz[$catname] : '';
	$cacheFileName = md5('rss_export_xml_v3' . $config['theme'] . $config['home_url'] . $config['default_lang'] . (is_array($xcat) ? $xcat['id'] : '') . pluginGetVariable('rss_export', 'use_hide') . is_array($userROW)) . '.txt';
	// Start benchmark
	$startTime = microtime(true);
	if (pluginGetVariable('rss_export', 'cache')) {
		$cacheExpire = intval(pluginGetVariable('rss_export', 'cacheExpire'));
		$cached = cacheRetrieveFile($cacheFileName, $cacheExpire > 0 ? $cacheExpire * 60 : 3600, 'rss_export');
		if ($cached !== false) {
			// Убираем возможный BOM из кеша
			$cached = preg_replace('/^\xEF\xBB\xBF/', '', $cached);
			rss_export_send_response($cached, rss_export_cached_last_modified($cacheFileName));
		}
	}
	// Нормализация URL (коллапс двойных слешей в пути)
	if (!function_exists('rss_export_normalize_url')) {
		function rss_export_normalize_url($url)
		{
			$parts = @parse_url($url);
			if ($parts === false) {
				return $url;
			}
			$scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
			$user = $parts['user'] ?? '';
			$pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
			$auth = $user ? ($user . $pass . '@') : '';
			$host = $parts['host'] ?? '';
			$port = isset($parts['port']) ? ':' . $parts['port'] : '';
			$path = $parts['path'] ?? '';
			$path = '/' . ltrim(preg_replace('#/+#', '/', $path), '/');
			$query = isset($parts['query']) ? ('?' . $parts['query']) : '';
			$fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
			return $scheme . $auth . $host . $port . $path . $query . $fragment;
		}
	}
	// Канонический self URL ленты
	// Канонический self URL всегда берём из конфигурации сайта
	if ($catname) {
		$selfUrl = rss_export_normalize_url(rtrim($config['home_url'], '/') . '/' . $catname . '.xml');
	} else {
		$selfUrl = rss_export_normalize_url(rtrim($config['home_url'], '/') . '/rss.xml');
	}
	// Начинаем буферизацию XML вывода
	ob_start();
	// Генерация XML заголовков и канала
	echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/">' . "\n";
	echo "<channel>\n";
	echo '<atom:link rel="self" href="' . htmlspecialchars($selfUrl, ENT_QUOTES, 'UTF-8') . '" type="application/rss+xml" />' . "\n";
	// Заголовок канала (CDATA не требует htmlspecialchars)
	if (pluginGetVariable('rss_export', 'feed_title_format') == 'handy') {
		echo "<title>" . pluginGetVariable('rss_export', 'feed_title_value') . "</title>\n";
	} else if ((pluginGetVariable('rss_export', 'feed_title_format') == 'site_title') && is_array($xcat)) {
		echo "<title>" . $config['home_title'] . (is_array($xcat) ? ' :: ' . $xcat['name'] : '') . "</title>\n";
	} else {
		echo "<title>" . $config['home_title'] . "</title>\n";
	}
	// Обязательный элемент link - ссылка на главную страницу сайта
	echo "<link>" . htmlspecialchars($config['home_url'], ENT_QUOTES, 'UTF-8') . "</link>\n";
	// Определяем язык ленты: приоритет для ручной настройки, иначе авто из системного языка
	$rssLang = 'ru';
	$mode = pluginGetVariable('rss_export', 'feed_lang_mode');
	$manual = trim(strval(pluginGetVariable('rss_export', 'feed_lang_code')));
	if ($mode === 'manual' && $manual !== '') {
		$__rawLang = strtolower($manual);
		$__rawLang = str_replace('_', '-', $__rawLang);
		$parts = explode('-', $__rawLang);
		$langCode = isset($parts[0]) ? $parts[0] : '';
		if ($langCode === 'ua') {
			$langCode = 'uk';
		}
		if (preg_match('/^[a-z]{2,3}$/', $langCode)) {
			$rssLang = $langCode;
			if (isset($parts[1]) && preg_match('/^[a-z]{2}$/', $parts[1])) {
				$rssLang = $langCode . '-' . strtoupper($parts[1]);
			}
		}
	} else {
		// Авто: из системной настройки default_lang
		$__rawLang = strtolower(strval($config['default_lang']));
		$__rawLang = str_replace('_', '-', $__rawLang);
		$parts = explode('-', $__rawLang);
		$langCode = isset($parts[0]) ? $parts[0] : '';
		if ($langCode === 'ua') {
			$langCode = 'uk';
		}
		// Преобразуем длинные названия в ISO-639 коды
		$langMap = array(
			'russian' => 'ru',
			'english' => 'en',
			'ukrainian' => 'uk',
			'deutsch' => 'de',
			'german' => 'de',
			'french' => 'fr',
			'spanish' => 'es',
			'italian' => 'it',
			'polish' => 'pl',
			'czech' => 'cs',
			'portuguese' => 'pt',
			'chinese' => 'zh',
			'japanese' => 'ja',
			'korean' => 'ko',
			'turkish' => 'tr',
			'arabic' => 'ar'
		);
		if (isset($langMap[$langCode])) {
			$langCode = $langMap[$langCode];
		}
		if (preg_match('/^[a-z]{2,3}$/', $langCode)) {
			$rssLang = $langCode;
			if (isset($parts[1]) && preg_match('/^[a-z]{2}$/', $parts[1])) {
				$rssLang = $langCode . '-' . strtoupper($parts[1]);
			}
		}
	}
	echo "<language>" . $rssLang . "</language>\n";
	echo "<description>" . $config['description'] . "</description>\n";
	echo "<generator><![CDATA[Plugin RSS_EXPORT (0.07) // Next Generation CMS (" . engineVersion . ")]]></generator>\n";
	// Инициализация xfields
	$xFList = array();
	$encImages = array();
	$enclosureIsImages = false;
	if (pluginGetVariable('rss_export', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
		$xFList = xf_configLoad();
		$eFieldName = pluginGetVariable('rss_export', 'xfEnclosure');
		if (isset($xFList['news'][$eFieldName]) && ($xFList['news'][$eFieldName]['type'] == 'images')) {
			$enclosureIsImages = true;
		}
	}
	// Получение новостей
	$limit = pluginGetVariable('rss_export', 'news_count');
	$delay = intval(pluginGetVariable('rss_export', 'delay'));
	if ((!is_numeric($limit)) || ($limit < 0) || ($limit > 500)) $limit = 50;
	$old_locale = setlocale(LC_TIME, 0);
	setlocale(LC_TIME, 'en_EN');
	if (is_array($xcat)) {
		$orderBy = (!empty($xcat['orderby']) && in_array($xcat['orderby'], array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc'))) ? $xcat['orderby'] : 'id desc';
		// Match category ID inside comma-separated list: start/middle/end positions
		$catPattern = '(^|,)' . intval($xcat['id']) . '(,|$)';
		$query = "select * from " . prefix . "_news where catid REGEXP " . db_squote($catPattern) . " and approve=1 " . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . " order by " . $orderBy;
	} else {
		$pluginOrder = pluginGetVariable('rss_export', 'news_order');
		$allowedOrders = array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc');
		if (!empty($pluginOrder) && $pluginOrder !== 'auto' && in_array($pluginOrder, $allowedOrders)) {
			$globalOrderBy = $pluginOrder;
		} elseif (!empty($config['default_newsorder']) && in_array($config['default_newsorder'], $allowedOrders)) {
			$globalOrderBy = $config['default_newsorder'];
		} else {
			$globalOrderBy = 'id desc';
		}
		$query = "select * from " . prefix . "_news where approve=1" . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . " order by " . $globalOrderBy;
	}
	$sqlData = $mysql->select($query . " limit $limit");
	// Подготовка изображений для enclosure
	if ($enclosureIsImages) {
		$nAList = array();
		foreach ($sqlData as $row) {
			if ($row['num_images'] > 0)
				$nAList[] = $row['id'];
		}
		if (count($nAList)) {
			$iQuery = "select * from " . prefix . "_images where (linked_ds = 1) and (linked_id in (" . join(",", $nAList) . ")) and (plugin = 'xfields') and (pidentity = " . db_squote($eFieldName) . ")";
			foreach ($mysql->select($iQuery) as $row) {
				if (!isset($encImages[$row['linked_id']]))
					$encImages[$row['linked_id']] = $row;
			}
		}
	}
	$truncateLen = intval(pluginGetVariable('rss_export', 'truncate'));
	if ($truncateLen < 0) $truncateLen = 0;
	foreach ($sqlData as $row) {
		// Обработка контента
		$export_mode = 'export_body';
		switch (pluginGetVariable('rss_export', 'content_show')) {
			case '1':
				$export_mode = 'export_short';
				break;
			case '2':
				$export_mode = 'export_full';
				break;
		}
		$content = news_showone($row['id'], '', array('emulate' => $row, 'style' => $export_mode, 'plugin' => 'rss_export'));
		if ($truncateLen > 0) {
			$content = $parse->truncateHTML($content, $truncateLen, '...');
		}
		// Удаляем нежелательные теги
		$content = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $content);
		$content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);
		// Удаляем битые короткие закрывающие теги
		$content = preg_replace('/<\/[a-zA-Zа-яА-ЯёЁ]{1,2}>/u', '', $content);
		$content = preg_replace('/<\/\s*>/', '', $content);
		// Превращаем относительные ссылки и источники в абсолютные
		$base = rtrim($config['home_url'], '/');
		$content = preg_replace_callback('/\b(?:src|href)=([\"\'])([^\"\']+)\1/i', function ($m) use ($base) {
			$attr = $m[0];
			$q = $m[1];
			$u = $m[2];
			// Пропускаем абсолютные URL, протокол-агностичные, data:, mailto:, tel:, якоря
			if (preg_match('#^(?:[a-z]+:)?//#i', $u) || preg_match('#^[a-z]+:#i', $u) || ($u !== '' && mb_substr($u, 0, 1) === '#')) {
				return $attr;
			}
			if ($u !== '' && mb_substr($u, 0, 1) === '/') {
				$new = $base . $u;
			} else {
				$new = $base . '/' . $u;
			}
			return preg_replace('/=([\"\']).*?\1/', '=' . $q . $new . $q, $attr, 1);
		}, $content);
		// Заменяем одиночные амперсанды на &amp;
		$content = preg_replace_callback('/&(?!amp;|lt;|gt;|quot;|apos;)/', function ($m) {
			return '&amp;';
		}, $content);
		// Экранируем HTML-теги в спецсимволы для безопасного отображения
		$content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
		// Обработка enclosure
		$enclosure = '';
		$enclosureType = 'application/octet-stream';
		if (pluginGetVariable('rss_export', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
			include_once(root . "/plugins/xfields/xfields.php");
			if (is_array($xfd = xf_decode($row['xfields'])) && isset($xfd[pluginGetVariable('rss_export', 'xfEnclosure')])) {
				if ($enclosureIsImages) {
					if (isset($encImages[$row['id']])) {
						$enclosure = ($encImages[$row['id']]['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $encImages[$row['id']]['folder'] . '/' . $encImages[$row['id']]['name'];
						$enclosureType = rss_export_image_mime_type($encImages[$row['id']]['name']);
					}
				} else {
					$enclosure = $xfd[pluginGetVariable('rss_export', 'xfEnclosure')];
				}
			}
		}
		// Приводим протокол enclosure к протоколу сайта
		if ($enclosure != '') {
			$siteProtocol = (stripos($config['home_url'], 'https://') === 0) ? 'https://' : 'http://';
			$enclosure = preg_replace('/^https?:\/\//i', $siteProtocol, $enclosure);
		}
		echo "  <item>\n";
		$rssTitle = ((pluginGetVariable('rss_export', 'news_title') == 1) && GetCategories($row['catid'], true) ? GetCategories($row['catid'], true) . ' :: ' : '') . $row['title'];
		$rssTitle = strip_tags(html_entity_decode($rssTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
		echo '   <title>' . htmlspecialchars($rssTitle, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</title>\n";
		echo "   <link>" . htmlspecialchars(newsGenerateLink($row, false, 0, true), ENT_QUOTES, 'UTF-8') . "</link>\n";
		echo "   <description><![CDATA[" . $content . "]]></description>\n";
		if ($enclosure != '') {
			echo '   <enclosure url="' . htmlspecialchars($enclosure, ENT_QUOTES, 'UTF-8') . '" length="0" type="' . $enclosureType . '" />' . "\n";
		}
		$__catTitle = trim(strval(GetCategories($row['catid'], true)));
		if ($__catTitle !== '') {
			echo "   <category>" . htmlspecialchars($__catTitle, ENT_QUOTES, 'UTF-8') . "</category>\n";
		}
		echo "   <guid isPermaLink=\"false\">" . htmlspecialchars($config['home_url'] . "?id=" . $row['id'], ENT_QUOTES, 'UTF-8') . "</guid>\n";
		// Проверка даты на валидность
		$pubDate = ($row['postdate'] > time()) ? time() : $row['postdate'];
		echo "   <pubDate>" . gmdate('r', $pubDate) . "</pubDate>\n";
		echo "  </item>\n";
	}
	setlocale(LC_TIME, $old_locale);
	echo " </channel>\n</rss>\n";
	// Сохраняем и отдаём буфер
	$output = ob_get_clean();
	// Убираем BOM и лишние пробелы в начале
	$output = preg_replace('/^\xEF\xBB\xBF/', '', $output);
	$output = ltrim($output);
	$itemCount = count($sqlData);
	$elapsed = round((microtime(true) - $startTime) * 1000, 2);
	$lastModified = time();
	if (pluginGetVariable('rss_export', 'cache')) {
		cacheStoreFile($cacheFileName, $output, 'rss_export');
		$lastModified = rss_export_cached_last_modified($cacheFileName);
	}
	rss_export_send_response($output, $lastModified);
}
