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
	$cacheFileName = md5('rss_export' . $config['theme'] . $config['home_url'] . $config['default_lang'] . (is_array($xcat) ? $xcat['id'] : '') . pluginGetVariable('rss_export', 'use_hide') . is_array($userROW)) . '.txt';
	// Start benchmark
	$startTime = microtime(true);
	if (pluginGetVariable('rss_export', 'cache')) {
		$cached = cache_get('rss_export_' . $cacheFileName);
		if ($cached !== null) {
			logger('rss_export', 'RSS feed served from cache: category=' . ($catname ?: 'all'));
			// Убираем возможный BOM из кеша
			$cached = preg_replace('/^\xEF\xBB\xBF/', '', $cached);
			echo $cached;
			exit;
		}
	}
	logger('rss_export', 'Generating RSS feed: category=' . ($catname ?: 'all'));
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
	// Пытаемся использовать реальный URL запроса, если он совпадает с настройками сайта
	$scheme = 'http';
	if (
		(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
		(isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
		(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	) {
		$scheme = 'https';
	}
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	$reqUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	// Проверяем что домен совпадает с настройками сайта
	$configHost = parse_url($config['home_url'], PHP_URL_HOST);
	if ($host && $configHost && $host === $configHost && $reqUri) {
		$selfUrl = rss_export_normalize_url($scheme . '://' . $host . $reqUri);
	} else {
		// Используем URL из конфигурации
		if ($catname) {
			$selfUrl = rtrim($config['home_url'], '/') . '/' . $catname . '.xml';
		} else {
			$selfUrl = rtrim($config['home_url'], '/') . '/rss.xml';
		}
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
		$query = "select * from " . prefix . "_news where approve=1" . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . " order by id desc";
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
		if (pluginGetVariable('rss_export', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
			include_once(root . "/plugins/xfields/xfields.php");
			if (is_array($xfd = xf_decode($row['xfields'])) && isset($xfd[pluginGetVariable('rss_export', 'xfEnclosure')])) {
				if ($enclosureIsImages) {
					if (isset($encImages[$row['id']])) {
						$enclosure = ($encImages[$row['id']]['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $encImages[$row['id']]['folder'] . '/' . $encImages[$row['id']]['name'];
					}
				} else {
					$enclosure = $xfd[pluginGetVariable('rss_export', 'xfEnclosure')];
				}
			}
		}
		echo "  <item>\n";
		echo "   <title><![CDATA[" . ((pluginGetVariable('rss_export', 'news_title') == 1) && GetCategories($row['catid'], true) ? GetCategories($row['catid'], true) . ' :: ' : '') . $row['title'] . "]]></title>\n";
		echo "   <link>" . htmlspecialchars(newsGenerateLink($row, false, 0, true), ENT_QUOTES, 'UTF-8') . "</link>\n";
		echo "   <description><![CDATA[" . $content . "]]></description>\n";
		if ($enclosure != '') {
			echo '   <enclosure url="' . htmlspecialchars($enclosure, ENT_QUOTES, 'UTF-8') . '" length="0" type="' . ($enclosureIsImages ? 'image/jpeg' : 'application/octet-stream') . '" />' . "\n";
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
	if (pluginGetVariable('rss_export', 'cache')) {
		$cacheExpire = intval(pluginGetVariable('rss_export', 'cacheExpire'));
		cache_put('rss_export_' . $cacheFileName, $output, $cacheExpire > 0 ? $cacheExpire * 60 : 3600);
		logger('rss_export', 'RSS feed cached: items=' . $itemCount . ', category=' . ($catname ?: 'all') . ', elapsed=' . $elapsed . 'ms');
	} else {
		logger('rss_export', 'RSS feed generated (no cache): items=' . $itemCount . ', category=' . ($catname ?: 'all') . ', elapsed=' . $elapsed . 'ms');
	}
	echo $output;
	exit;
}
