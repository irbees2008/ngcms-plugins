<?php
// Защита от прямого доступа
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, benchmark, sanitize, get_ip, validate_url};

register_plugin_page('content_parser', '', 'plugin_content_parse', 0);

/**
 * Загрузка медиафайла на сервер через NGCMS upload system
 * @param string $url URL изображения или видео
 * @param string $type Тип файла: 'image' или 'file'
 * @return string|false Путь к загруженному файлу или false при ошибке
 */
function downloadMediaToServer($url, $type = 'image')
{
	global $config;

	if (empty($url)) {
		return false;
	}

	// Загружаем классы NGCMS для работы с файлами
	if (!class_exists('file_managment')) {
		@include_once(root . 'includes/classes/upload.class.php');
	}

	if (!class_exists('file_managment')) {
		return false;
	}

	// Создаем экземпляр менеджера файлов
	$fmanager = new file_managment();

	// Используем встроенную функцию загрузки по URL
	$uploadParams = [
		'type' => $type,
		'manual' => 1,
		'url' => $url,
		'rpc' => 1,
		'category' => '', // Без категории
		'thumbnail' => 1, // Всегда создавать уменьшенную копию
		'do_preview' => 1, // Создать превью
	];

	$result = $fmanager->file_upload($uploadParams);

	// Проверяем результат
	if (is_array($result) && isset($result['status']) && $result['status'] == 1) {
		// Успешная загрузка - формируем путь к файлу
		if (isset($result['data']) && is_array($result['data'])) {
			$data = $result['data'];

			// Определяем базовый путь в зависимости от типа файла
			$baseDir = ($type == 'image') ? $config['images_dir'] : $config['files_dir'];

			// Убираем корневой путь из baseDir, если он там есть
			$baseDir = str_replace(root, '', $baseDir);

			// Формируем полный путь: baseDir + category + name
			$category = isset($data['category']) ? $data['category'] : '';
			$name = isset($data['name']) ? $data['name'] : '';

			if ($name && $type == 'image') {
				// Нормализуем путь - убираем двойные слэши и добавляем ведущий слэш
				$fullPath = '/' . trim($baseDir . $category . $name, '/');
				logger('content_parser', 'Media downloaded: type=' . $type . ', url=' . sanitize($url) . ', path=' . $fullPath);
				return $fullPath;
			} elseif ($name) {
				// Для файлов (не изображений) просто возвращаем путь
				$fullPath = '/' . trim($baseDir . $category . $name, '/');
				logger('content_parser', 'File downloaded: type=' . $type . ', url=' . sanitize($url) . ', path=' . $fullPath);
				return $fullPath;
			}
		}
	}

	logger('content_parser', 'Media download failed: url=' . sanitize($url));
	return false;
}
/**
 * Парсинг RSS-канала и создание новостей
 */
function parseRssFeed($rssUrl, $count)
{
	$startTime = microtime(true);

	// Загружаем RSS-канал через cURL (throws Exception on error)
	try {
		$rss = loadRssFeed($rssUrl);
	} catch (Exception $e) {
		logger('content_parser', 'RSS load failed: url=' . sanitize($rssUrl) . ', error=' . $e->getMessage());
		throw new Exception("Ошибка загрузки RSS: " . $e->getMessage());
	}

	$items = [];
	$parsedCount = 0;
	// Проверяем наличие тегов <item>
	if (!isset($rss->channel->item)) {
		throw new Exception('Некорректная структура RSS-канала: отсутствуют теги <item>');
	}
	foreach ($rss->channel->item as $item) {
		if ($parsedCount >= $count) {
			break;
		}
		// Извлекаем данные
		$title = secure_html((string)$item->title);
		$rawDescription = (string)$item->description;
		$content = extractDescription($rawDescription);
		$imageUrl = extractImageFromItem($item, $rawDescription);
		$pubDate = strtotime((string)$item->pubDate);

		// Загружаем изображение на сервер
		if (!empty($imageUrl)) {
			$localImage = downloadMediaToServer($imageUrl);
			if ($localImage !== false) {
				$imageUrl = $localImage;
			}
		}

		$items[] = [
			'title' => $title,
			'content' => $content,
			'image' => $imageUrl,
			'postdate' => $pubDate,
		];
		$parsedCount++;
	}

	$elapsed = round((microtime(true) - $startTime) * 1000, 2);
	logger('content_parser', 'RSS parsed: url=' . sanitize($rssUrl) . ', items=' . $parsedCount . ', elapsed=' . $elapsed . 'ms');

	return $items;
}
function loadRssFeed($rssUrl)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $rssUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if (curl_errno($ch)) {
		$error = curl_error($ch);
		curl_close($ch);
		throw new Exception("Ошибка cURL при загрузке RSS: $error");
	}
	curl_close($ch);

	if ($httpCode >= 400) {
		throw new Exception("HTTP ошибка $httpCode при загрузке RSS канала");
	}

	if (empty($response)) {
		throw new Exception("Пустой ответ от RSS канала");
	}

	// Преобразуем ответ в SimpleXML
	libxml_use_internal_errors(true);
	$rss = simplexml_load_string($response);
	if ($rss === false) {
		$errors = libxml_get_errors();
		$errorMsg = "Ошибка разбора XML RSS";
		if (!empty($errors)) {
			$errorMsg .= ": " . $errors[0]->message;
		}
		libxml_clear_errors();
		throw new Exception($errorMsg);
	}
	return $rss;
}
function loadHtml($url, $ignoreSSL = false)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36');
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
		'Referer: https://www.instagram.com/',
	]);
	// IPv4 предпочтительно
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	if ($ignoreSSL) {
		// Для локальной отладки Instagram может требовать актуальные корневые сертификаты
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	}
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		curl_close($ch);
		return false;
	}
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($code >= 400) {
		return false;
	}
	return $response;
}
function normalizeInstagramUsername($username)
{
	$username = trim($username);
	$username = preg_replace('#^@#', '', $username);
	$username = preg_replace('#\s+#', '', $username);
	return $username;
}

function normalizeVkGroup($input)
{
	$input = trim($input);
	if ($input === '') {
		return '';
	}

	// Извлекаем ID или screen_name из различных форматов URL
	// Поддержка: club123, public123, https://vk.com/club123, https://vk.com/screenname
	if (preg_match('#vk\.com/(club|public)(\d+)#i', $input, $m)) {
		// Числовой ID с префиксом club/public - возвращаем с минусом для API
		return '-' . $m[2];
	} elseif (preg_match('#vk\.com/([a-z0-9_]+)#i', $input, $m)) {
		// Screen name
		return $m[1];
	} elseif (preg_match('#^(club|public)(\d+)$#i', $input, $m)) {
		// Прямой ввод club123 или public123
		return '-' . $m[2];
	} else {
		// Просто screen_name или числовой ID
		return $input;
	}
}

function parseVkPosts($groupId, $count)
{
	// Получаем VK API токен из настроек
	$vkToken = pluginGetVariable('content_parser', 'vk_token');

	if (!empty($vkToken)) {
		// Используем официальный VK API
		return parseVkViaAPI($groupId, $count, $vkToken);
	}

	// Если токена нет, пробуем HTML парсинг (может не работать)
	$posts = parseVkFromHtml($groupId, $count);
	if (!empty($posts)) {
		return $posts;
	}

	throw new Exception('VK RSS недоступен. Для парсинга VK необходимо настроить VK API токен в настройках плагина. Убедитесь, что токен сохранен через форму "Настройка VK API".');
}

function parseVkViaAPI($groupId, $count, $token)
{
	// Преобразуем group ID в формат для API
	$ownerId = $groupId;
	if (preg_match('#^-?\d+$#', $ownerId)) {
		// Уже числовой ID
		if (strpos($ownerId, '-') !== 0) {
			$ownerId = '-' . $ownerId; // Для групп нужен минус
		}
	} else {
		// Screen name - нужно разрешить через resolveScreenName
		$ownerId = resolveVkScreenName($groupId, $token);
		if (!$ownerId) {
			throw new Exception('Не удалось определить ID группы VK по screen_name: ' . $groupId);
		}
	}

	// Вызываем wall.get API
	$apiUrl = 'https://api.vk.com/method/wall.get';
	$params = [
		'owner_id' => $ownerId,
		'count' => min($count, 100),
		'filter' => 'owner',
		'access_token' => $token,
		'v' => '5.131'
	];

	$url = $apiUrl . '?' . http_build_query($params);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpCode !== 200 || !$response) {
		throw new Exception('Ошибка обращения к VK API (HTTP ' . $httpCode . ')');
	}

	$data = json_decode($response, true);

	if (isset($data['error'])) {
		$errorMsg = $data['error']['error_msg'] ?? 'Unknown error';
		$errorCode = $data['error']['error_code'] ?? 'N/A';
		throw new Exception("VK API ошибка ($errorCode): $errorMsg");
	}

	if (!isset($data['response']['items'])) {
		throw new Exception('Некорректный ответ VK API - отсутствует поле items');
	}

	$postsCount = count($data['response']['items']);

	if ($postsCount === 0) {
		throw new Exception('VK API вернул 0 постов. Возможно, группа пустая или закрыта, либо у токена недостаточно прав (требуются права: wall, groups)');
	}

	$items = [];
	foreach ($data['response']['items'] as $post) {
		$text = $post['text'] ?? '';
		$title = mb_substr($text, 0, 100);
		if (mb_strlen($text) > 100) {
			$title .= '...';
		}
		if (empty($title)) {
			$title = 'Пост без текста';
		}

		// Ищем изображение
		$imageUrl = '';
		if (isset($post['attachments'])) {
			foreach ($post['attachments'] as $att) {
				if ($att['type'] === 'photo' && isset($att['photo']['sizes'])) {
					$sizes = $att['photo']['sizes'];
					$largest = end($sizes);
					$imageUrl = $largest['url'] ?? '';
					break;
				}
			}
		}

		// Загружаем изображение на сервер
		$localImage = $imageUrl;
		if (!empty($imageUrl)) {
			$downloaded = downloadMediaToServer($imageUrl);
			if ($downloaded !== false) {
				$localImage = $downloaded;
			}
		}

		$body = '';
		if (!empty($localImage)) {
			$body .= '[img]' . $localImage . '[/img]' . "\n\n";
		}
		$body .= $text;

		$items[] = [
			'title' => secure_html($title),
			'content' => $body,
			'image' => $localImage,
			'postdate' => $post['date'] ?? time(),
		];
	}

	return $items;
}

function resolveVkScreenName($screenName, $token)
{
	$apiUrl = 'https://api.vk.com/method/utils.resolveScreenName';
	$params = [
		'screen_name' => $screenName,
		'access_token' => $token,
		'v' => '5.131'
	];

	$url = $apiUrl . '?' . http_build_query($params);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);

	$data = json_decode($response, true);

	if (isset($data['response']['object_id']) && $data['response']['type'] === 'group') {
		return '-' . $data['response']['object_id'];
	}

	return null;
}

function parseVkFromHtml($groupId, $count)
{
	// Используем VK Widget API, который доступен публично
	// https://vk.com/dev/widget_api

	// Преобразуем group ID в правильный формат
	$ownerId = $groupId;
	if (!preg_match('#^-?\d+$#', $ownerId)) {
		// Это screen_name, нужно получить ID через resolve
		$resolveUrl = 'https://vk.com/' . $groupId;
		$html = loadHtml($resolveUrl, true);

		// Ищем owner_id в HTML
		if (preg_match('#"owner_id":(-?\d+)#', $html, $m)) {
			$ownerId = $m[1];
		} else {
			throw new Exception('Не удалось определить ID группы VK. Попробуйте указать числовой ID вместо screen_name (например, club123456)');
		}
	}

	// Формируем URL для VK widget (публичный JSON endpoint)
	$widgetUrl = sprintf(
		'https://vk.com/al_community.php?act=get_posts&owner_id=%s&offset=0&count=%d&type=own',
		$ownerId,
		min($count, 100)
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $widgetUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: */*',
		'Accept-Language: ru-RU,ru;q=0.9',
		'X-Requested-With: XMLHttpRequest',
		'Referer: https://vk.com/' . ltrim($groupId, '-'),
	]);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpCode !== 200 || !$response) {
		throw new Exception('VK недоступен или группа закрыта. Для закрытых групп требуется VK API токен.');
	}

	$items = [];

	// VK возвращает HTML в response, парсим его
	// Ищем посты через regex (базовый парсинг)
	if (preg_match_all('#<div class="wall_post_text">([^<]+)#', $response, $matches)) {
		foreach ($matches[1] as $i => $text) {
			if ($i >= $count) break;

			$text = strip_tags(html_entity_decode($text));
			$title = mb_substr($text, 0, 100) . (mb_strlen($text) > 100 ? '...' : '');

			$items[] = [
				'title' => secure_html($title),
				'content' => secure_html($text),
				'image' => '',
				'postdate' => time() - ($i * 3600), // Примерное время
			];
		}
	}

	if (empty($items)) {
		throw new Exception('Не удалось получить посты VK. Возможно, группа закрыта или требуется настройка VK API с токеном доступа.');
	}

	return $items;
}

function loadInstagramJson($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36');
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: application/json',
		'X-IG-App-ID: 936619743392459',
		'X-Requested-With: XMLHttpRequest',
	]);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		curl_close($ch);
		return false;
	}
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($code >= 400) {
		return false;
	}

	$data = json_decode($response, true);
	return is_array($data) ? $data : false;
}

function parseInstagramFromJson($data, $count, $username)
{
	$items = [];
	if (!isset($data['data']['user']['edge_owner_to_timeline_media']['edges'])) {
		return $items;
	}

	$edges = $data['data']['user']['edge_owner_to_timeline_media']['edges'];
	$parsed = 0;

	foreach ($edges as $edge) {
		if ($parsed >= $count) {
			break;
		}

		$node = $edge['node'];
		$image = isset($node['display_url']) ? $node['display_url'] : null;
		$caption = isset($node['edge_media_to_caption']['edges'][0]['node']['text'])
			? $node['edge_media_to_caption']['edges'][0]['node']['text']
			: '';
		$timestamp = isset($node['taken_at_timestamp']) ? $node['taken_at_timestamp'] : time();

		$title = $caption ? mb_substr($caption, 0, 80) : ('Instagram пост @' . $username);

		// Загружаем изображение на сервер
		$localImage = $image;
		if (!empty($image)) {
			$downloaded = downloadMediaToServer($image);
			if ($downloaded !== false) {
				$localImage = $downloaded;
			}
		}

		$body = '';
		if ($localImage) {
			$body .= '[img]' . $localImage . '[/img]' . "\n\n";
		}
		$body .= $caption;

		$items[] = [
			'title' => secure_html($title),
			'content' => $body,
			'image' => $localImage,
			'postdate' => $timestamp,
		];
		$parsed++;
	}

	return $items;
}
function parseInstagramPosts($username, $count)
{
	$username = normalizeInstagramUsername($username);
	if ($username === '') {
		throw new Exception('Некорректное имя пользователя Instagram');
	}

	// Попытка 1: JSON API (работает без авторизации)
	$jsonUrl = 'https://www.instagram.com/api/v1/users/web_profile_info/?username=' . $username;
	$jsonData = loadInstagramJson($jsonUrl);
	if ($jsonData !== false) {
		return parseInstagramFromJson($jsonData, $count, $username);
	}

	// Попытка 2: HTML Desktop
	$profileUrl = 'https://www.instagram.com/' . $username . '/';
	$html = loadHtml($profileUrl, true);
	if ($html === false) {
		// Попытка 3: HTML Mobile
		$profileUrl = 'https://m.instagram.com/' . $username . '/';
		$html = loadHtml($profileUrl, true);
	}
	if ($html === false) {
		throw new Exception('Не удалось загрузить профиль Instagram. На локальном сервере Instagram часто блокирует запросы. Попробуйте использовать прокси или VPN.');
	}
	$items = [];
	$links = [];
	if (preg_match_all('#href=\"(/p/[A-Za-z0-9_-]+/)\"#', $html, $m)) {
		$links = array_unique($m[1]);
	}
	// Поддержка Reels
	if (preg_match_all('#href=\"(/reel/[A-Za-z0-9_-]+/)\"#', $html, $mr)) {
		$links = array_unique(array_merge($links, $mr[1]));
	}
	if (empty($links) && preg_match_all('#\"shortcode\":\"([A-Za-z0-9_-]+)\"#', $html, $m2)) {
		foreach ($m2[1] as $sc) {
			$links[] = '/p/' . $sc . '/';
		}
		$links = array_unique($links);
	}
	if (empty($links)) {
		throw new Exception('Не удалось найти посты Instagram у пользователя');
	}
	$parsed = 0;
	foreach ($links as $path) {
		if ($parsed >= $count) {
			break;
		}
		$postUrl = 'https://www.instagram.com' . $path;
		$postHtml = loadHtml($postUrl, true);
		if ($postHtml === false) {
			continue;
		}
		$image = null;
		$contentText = '';
		$postDate = time();
		if (preg_match('#<meta property=\"og:image\" content=\"([^\"]+)\"#i', $postHtml, $mi)) {
			$image = $mi[1];
		}
		if (preg_match('#<meta property=\"og:description\" content=\"([^\"]+)\"#i', $postHtml, $md)) {
			$contentText = html_entity_decode($md[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
		}
		if (preg_match('#datetime=\"([^\"]+)\"#i', $postHtml, $dt)) {
			$ts = strtotime($dt[1]);
			if ($ts) {
				$postDate = $ts;
			}
		}
		$title = $contentText ? mb_substr($contentText, 0, 80) : ('Instagram пост @' . $username);

		// Загружаем изображение на сервер
		$localImage = $image;
		if (!empty($image)) {
			$downloaded = downloadMediaToServer($image);
			if ($downloaded !== false) {
				$localImage = $downloaded;
			}
		}

		$body = '';
		if ($localImage) {
			$body .= '[img]' . $localImage . '[/img]' . "\n\n";
		}
		$body .= $contentText;
		$items[] = [
			'title' => secure_html($title),
			'content' => $body,
			'image' => $localImage,
			'postdate' => $postDate,
		];
		$parsed++;
	}
	return $items;
}
function extractDescription($description)
{
	// Разрешаем базовые теги, включая img
	$allowedTags = '<p><br><ul><ol><li><a><strong><em><b><i><img>';
	return strip_tags($description, $allowedTags);
}
function extractImageFromItem($item, $htmlDescription)
{
	// enclosure с типом image/*
	if (isset($item->enclosure)) {
		$enc = $item->enclosure;
		$url = isset($enc['url']) ? (string)$enc['url'] : '';
		$type = isset($enc['type']) ? (string)$enc['type'] : '';
		if ($url && ($type === '' || preg_match('#^image/#', $type))) {
			return $url;
		}
	}
	// media:content / media:thumbnail (MRSS)
	$media = $item->children('http://search.yahoo.com/mrss/');
	if ($media && isset($media->content)) {
		foreach ($media->content as $mc) {
			$attrs = $mc->attributes();
			$url = isset($attrs['url']) ? (string)$attrs['url'] : '';
			$type = isset($attrs['type']) ? (string)$attrs['type'] : '';
			if ($url && ($type === '' || preg_match('#^image/#', $type))) {
				return $url;
			}
		}
	}
	if ($media && isset($media->thumbnail)) {
		foreach ($media->thumbnail as $thumb) {
			$attrs = $thumb->attributes();
			$url = isset($attrs['url']) ? (string)$attrs['url'] : '';
			if ($url) {
				return $url;
			}
		}
	}
	// Первая картинка из HTML описания
	if ($htmlDescription) {
		if (preg_match('#<img[^>]+src=["\']([^"\']+)["\']#i', $htmlDescription, $m)) {
			return $m[1];
		}
	}
	return null;
}
function addNewsDirect($item)
{
	global $mysql, $userROW, $parse;

	$category = intval($_REQUEST['category'] ?? 0);
	$title = $_REQUEST['title'];
	$content = $_REQUEST['ng_news_content'];

	// Генерируем alt_name
	$alt_name = mb_strtolower($parse->translit(trim($title), 1));
	$alt_name = preg_replace(['/\./', '/(_{2,20})/', '/^(_+)/', '/(_+)$/'], ['_', '_'], $alt_name);
	if ($alt_name == '') {
		$alt_name = '_';
	}

	// Проверяем уникальность alt_name
	$i = '';
	while (is_array($mysql->record('select id from ' . prefix . '_news where alt_name = ' . db_squote($alt_name . $i) . ' limit 1'))) {
		$i++;
	}
	$alt_name = $alt_name . $i;

	$postdate = isset($item['postdate']) ? $item['postdate'] : time();
	$postdate += 60 * 60 * 6; // date_adjust approximation

	$SQL = [
		'title' => $title,
		'alt_name' => $alt_name,
		'content' => $content,
		'postdate' => $postdate,
		'editdate' => $postdate,
		'author' => $userROW['name'],
		'author_id' => $userROW['id'],
		'catid' => $category,
		'flags' => 2, // HTML enabled
		'approve' => -1, // Draft
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

	$mysql->query('insert into ' . prefix . '_news (' . implode(',', $vnames) . ') values (' . implode(',', $vparams) . ')');
	$id = $mysql->result('SELECT LAST_INSERT_ID() as id');

	if (!$id) {
		return false;
	}

	// Добавляем в карту категорий
	if ($category > 0) {
		$mysql->query('insert into ' . prefix . '_news_map (newsID, categoryID, dt) values (' . db_squote($id) . ', ' . db_squote($category) . ', now())');
	}

	return $id;
}

function createContentFromRss($type, $items)
{
	global $SUPRESS_TEMPLATE_SHOW, $mysql;
	$stats = ['added' => 0, 'skipped' => 0, 'errors' => []];

	foreach ($items as $index => $item) {

		if ($type === 'news') {
			// Проверяем дубликат ДО попытки добавления
			$existing = $mysql->record('SELECT id FROM ' . prefix . '_news WHERE title=' . db_squote($item['title']) . ' LIMIT 1');
			if ($existing) {
				$stats['skipped']++;
				continue; // Пропускаем и идём к следующей
			}

			// Готовим данные для добавления через addNews
			$_REQUEST['title'] = $item['title'];
			// Категория публикации (передаётся из запроса)
			$_REQUEST['category'] = intval($_REQUEST['category'] ?? 0);
			$_POST['category'] = $_REQUEST['category'];
			// Не разрешаем HTML, используем BBCode
			$_REQUEST['flag_HTML'] = 0;
			$_REQUEST['flag_RAW'] = 0;
			$body = '';
			if (!empty($item['image'])) {
				$body .= '[img]' . $item['image'] . '[/img]' . "\n\n";
			}
			$body .= $item['content'];
			$_REQUEST['ng_news_content'] = $body;
			// Не публиковать (черновик)
			$_REQUEST['approve'] = -1;
			$_REQUEST['mainpage'] = 1;
			$_REQUEST['postdate'] = $item['postdate'];
			// Заполняем обязательные xfields, если настроены
			if (!is_array($_REQUEST['xfields'])) {
				$_REQUEST['xfields'] = [];
			}
			// Подгружаем конфиг xfields и проставляем дефолты для обязательных полей
			if (file_exists(root . 'engine/plugins/xfields/lib/common.php')) {
				include_once(root . 'engine/plugins/xfields/lib/common.php');
				$xfConf = xf_configLoad();
				if (is_array($xfConf) && isset($xfConf['news']) && is_array($xfConf['news'])) {
					foreach ($xfConf['news'] as $fid => $fmeta) {
						if (!empty($fmeta['disabled'])) {
							continue;
						}
						if (!empty($fmeta['required'])) {
							if (!isset($_REQUEST['xfields'][$fid]) || $_REQUEST['xfields'][$fid] === '') {
								// Проставим простое значение: заголовок или "auto"
								$_REQUEST['xfields'][$fid] = $item['title'] ?: 'auto';
							}
						}
					}
				}
			}
			// Добавляем новость через функцию CMS
			include_once(root . 'includes/inc/lib_admin.php');

			$added = addNews(['no.token' => true, 'no.editurl' => 1, 'no.files' => 1, 'no.meta' => 1]);

			if (!$added) {
				// ФОЛБЭК: Прямое добавление в БД
				$addedDirect = addNewsDirect($item);
				if (!$addedDirect) {
					$stats['errors'][] = 'Не удалось добавить: ' . $item['title'];
					continue; // Пропускаем и идём к следующей
				}
				$stats['added']++;
			} else {
				$stats['added']++;
			}
		}
	}

	return $stats;
}
function plugin_content_parse()
{
	global $SUPRESS_TEMPLATE_SHOW, $SYSTEM_FLAGS, $catmap, $userROW;

	// Очищаем все буферы вывода
	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	$SUPRESS_TEMPLATE_SHOW = 1;
	$SUPRESS_MAINBLOCK_SHOW = 1;
	@header('Content-type: application/json; charset=utf-8');
	$SYSTEM_FLAGS['http.headers'] = [
		'content-type'  => 'application/json; charset=utf-8',
		'cache-control' => 'private',
	];

	try {
		$count = (int)($_REQUEST['real_count'] ?? 0);
		$action = $_REQUEST['actionName'] ?? '';
		$source = $_REQUEST['source'] ?? 'rss';
		$rssUrl = $_REQUEST['rss_url'] ?? '';
		$category = intval($_REQUEST['category'] ?? 0);
		$igUser = $_REQUEST['ig_user'] ?? '';

		if ($count < 1) {
			echo json_encode(['error' => 'Invalid count']);
			exit();
		}
		if ($category <= 0) {
			echo json_encode(['error' => 'Не выбрана категория размещения']);
			exit();
		}
		// Проверка авторизации и прав до вызова addNews
		if (!is_array($userROW) || empty($userROW['id'])) {
			echo json_encode(['error' => 'Требуется авторизация администратора']);
			exit();
		}
		if (function_exists('checkPermission')) {
			$perm = checkPermission(['plugin' => '#admin', 'item' => 'news'], null, ['add']);
			if (!$perm['add']) {
				echo json_encode(['error' => 'Недостаточно прав для добавления новостей']);
				exit();
			}
		}
		// Убедимся, что карта категорий загружена и категория существует
		if (!is_array($catmap) || empty($catmap)) {
			if (function_exists('ngLoadCategories')) {
				ngLoadCategories();
			}
		}
		if (!isset($catmap[$category])) {
			echo json_encode(['error' => 'Выбранная категория не найдена']);
			exit();
		}
		try {
			if ($source === 'instagram') {
				if (!$igUser) {
					throw new Exception('Не указан Instagram пользователь');
				}
				$items = parseInstagramPosts($igUser, $count);
			} elseif ($source === 'vk') {
				$vkGroup = $_REQUEST['vk_group'] ?? '';
				if (!$vkGroup) {
					throw new Exception('Не указана группа VK');
				}
				$vkId = normalizeVkGroup($vkGroup);
				if (!$vkId) {
					throw new Exception('Некорректный идентификатор группы VK');
				}
				$items = parseVkPosts($vkId, $count);
			} else {
				if (empty($rssUrl)) {
					throw new Exception('Invalid RSS URL');
				}
				try {
					$items = parseRssFeed($rssUrl, $count);
				} catch (Exception $e) {
					throw new Exception('RSS парсинг: ' . $e->getMessage());
				}
			}

			// Прокидываем категорию
			$_REQUEST['category'] = $category;

			// Проверяем, что получены данные
			$itemsCount = is_array($items) ? count($items) : 0;

			if ($itemsCount === 0) {
				throw new Exception('Не удалось получить посты из источника. Проверьте правильность указанных данных (URL канала, имя пользователя, токен VK API).');
			}

			// Создаем новости
			$stats = createContentFromRss('news', $items);

			if (function_exists('ob_get_level') && ob_get_level() > 0) {
				ob_end_clean();
			}

			$response = [
				'status' => 'success',
				'count' => count($items),
				'added' => $stats['added'],
				'skipped' => $stats['skipped'],
				'action' => $action,
				'source' => $source,
				'debug' => [
					'items_received' => count($items),
					'first_item_title' => isset($items[0]['title']) ? $items[0]['title'] : 'N/A',
					'category' => $category,
					'type_param' => 'news'
				]
			];

			// Добавляем ошибки, если есть
			if (!empty($stats['errors'])) {
				$response['errors'] = $stats['errors'];
				$response['has_errors'] = true;
			}

			echo json_encode($response);
		} catch (Exception $e) {
			error_log("Ошибка в plugin_content_parse: " . $e->getMessage());
			echo json_encode(['error' => $e->getMessage()]);
		}
	} catch (Exception $globalError) {
		error_log("Критическая ошибка в plugin_content_parse: " . $globalError->getMessage());
		echo json_encode(['error' => 'Критическая ошибка: ' . $globalError->getMessage()]);
	} catch (Error $fatalError) {
		error_log("Фатальная ошибка в plugin_content_parse: " . $fatalError->getMessage());
		echo json_encode(['error' => 'Фатальная ошибка: ' . $fatalError->getMessage()]);
	}
	exit();
}
