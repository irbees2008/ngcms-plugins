<?php
// Защита от прямого доступа
if (!defined('NGCMS')) die('HAL');

use function Plugins\logger;
use function Plugins\benchmark;
use function Plugins\sanitize;
use function Plugins\get_ip;
use function Plugins\validate_url;

// Подключаем модуль MadelineProto для Telegram (если установлен)
$madelineProtoFile = __DIR__ . '/telegram_madelineproto.php';
if (file_exists($madelineProtoFile)) {
    require_once $madelineProtoFile;
}

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
                // Формируем путь и нормализуем слэши
                $fullPath = str_replace('\\', '/', $baseDir . $category . $name);
                // Удаляем абсолютный путь диска (C:/, D:/ и т.д.) и оставляем только относительный путь от корня сайта
                if (preg_match('#/(uploads|files)/.+$#i', $fullPath, $matches)) {
                    $fullPath = $matches[0];
                } else {
                    $fullPath = '/' . trim($fullPath, '/');
                }
                logger('Media downloaded: type=' . $type . ', url=' . sanitize($url, 'info', 'content_parser.log') . ', path=' . $fullPath);
                return $fullPath;
            } elseif ($name) {
                // Для файлов (не изображений) формируем путь аналогично
                $fullPath = str_replace('\\', '/', $baseDir . $category . $name);
                // Удаляем абсолютный путь диска и оставляем только относительный путь
                if (preg_match('#/(uploads|files)/.+$#i', $fullPath, $matches)) {
                    $fullPath = $matches[0];
                } else {
                    $fullPath = '/' . trim($fullPath, '/');
                }
                logger('File downloaded: type=' . $type . ', url=' . sanitize($url, 'info', 'content_parser.log') . ', path=' . $fullPath);
                return $fullPath;
            }
        }
    }
    logger('Media download failed: url=' . sanitize($url, 'info', 'content_parser.log'));
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
        logger('RSS load failed: url=' . sanitize($rssUrl, 'info', 'content_parser.log') . ', error=' . $e->getMessage());
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
    $elapsed = (microtime(true) - $startTime) * 1000;
    logger('RSS parsed: url=' . sanitize($rssUrl, 'info', 'content_parser.log') . ', items=' . $parsedCount . ', elapsed=' . round($elapsed, 2) . 'ms');
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
        if ($errorCode == 27) {
            throw new Exception('VK API ошибка (27): токен сообщества не может читать стену (wall.get). Нужен пользовательский токен с правом "wall" — см. подсказку под полем токена на вкладке VK.');
        }
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
                    // end() требует переменную по ссылке
                    if (!empty($sizes)) {
                        $largest = end($sizes);
                        $imageUrl = $largest['url'] ?? '';
                    }
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

/**
 * Нормализация имени Telegram канала
 * @param string $input Имя канала или URL
 * @return string Нормализованное имя канала
 */
function normalizeTelegramChannel($input)
{
    $input = trim($input);
    if ($input === '') {
        return '';
    }

    // Убираем @ в начале
    $input = preg_replace('#^@#', '', $input);

    // Извлекаем имя канала из различных форматов URL
    // Поддержка: @channel, t.me/channel, https://t.me/channel, https://t.me/s/channel
    if (preg_match('#t\.me/s/([a-zA-Z0-9_]+)#i', $input, $m)) {
        return $m[1];
    } elseif (preg_match('#t\.me/([a-zA-Z0-9_]+)#i', $input, $m)) {
        return $m[1];
    }

    // Просто имя канала
    return preg_replace('#[^a-zA-Z0-9_]#', '', $input);
}

/**
 * Парсинг постов из публичного Telegram канала
 * @param string $channelName Имя канала
 * @param int $count Количество постов
 * @return array Массив постов
 */
function parseTelegramChannel($channelName, $count)
{
    $channelName = normalizeTelegramChannel($channelName);
    if ($channelName === '') {
        throw new Exception('Некорректное имя канала Telegram');
    }

    // Проверяем настройки плагина: использовать ли MadelineProto
    $useMadelineProto = pluginGetVariable('content_parser', 'tg_use_madelineproto');
    $hasApiCredentials = pluginGetVariable('content_parser', 'tg_api_id') && pluginGetVariable('content_parser', 'tg_api_hash');
    $madelineProtoInstalled = function_exists('isMadelineProtoInstalled') && isMadelineProtoInstalled();

    logger(
        'Telegram parsing mode check: use_madelineproto=' . ($useMadelineProto ? 'yes' : 'no') .
            ', has_credentials=' . ($hasApiCredentials ? 'yes' : 'no') .
            ', installed=' . ($madelineProtoInstalled ? 'yes' : 'no'),
        'info',
        'content_parser.log'
    );

    // Если MadelineProto включен, есть API credentials и библиотека установлена - используем его
    if ($useMadelineProto && $hasApiCredentials && $madelineProtoInstalled) {
        try {
            logger('Using MadelineProto for Telegram parsing: channel=' . $channelName, 'info', 'content_parser.log');
            return parseTelegramChannelWithAuth($channelName, $count);
        } catch (\Throwable $e) {
            // Если MadelineProto не сработал, падаем обратно на веб-парсинг
            logger('MadelineProto failed, falling back to web parsing: ' . $e->getMessage(), 'warning', 'content_parser.log');
        }
    } elseif (!$madelineProtoInstalled) {
        logger('MadelineProto not installed, using web parsing (public channels only)', 'info', 'content_parser.log');
    }

    // Используем веб-парсинг (публичная embed-версия канала)
    logger('Using web parsing for channel: ' . $channelName, 'info', 'content_parser.log');
    return parseTelegramChannelViaWeb($channelName, $count);
}

/**
 * Парсинг Telegram через веб-версию (без авторизации, только публичные каналы)
 * @param string $channelName Имя канала
 * @param int $count Количество постов
 * @return array Массив постов
 */
function parseTelegramChannelViaWeb($channelName, $count)
{
    $startTime = microtime(true);

    // Используем публичную embed-версию канала (не требует авторизации)
    $url = 'https://t.me/s/' . $channelName;

    logger('Telegram parsing: channel=' . $channelName . ', url=' . $url, 'debug', 'content_parser.log');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $html = curl_exec($ch);
    $curlErrno = curl_errno($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErrno) {
        $hint = '';
        if ($curlErrno === 6) {
            $hint = ' Не удалось разрешить t.me — возможно, Telegram заблокирован на сервере.';
        } elseif ($curlErrno === 28) {
            $hint = ' Превышен таймаут — соединение блокируется.';
        }
        throw new Exception('Telegram недоступен: curl #' . $curlErrno . ' — ' . $curlError . '.' . $hint);
    }

    logger('Telegram HTTP response: code=' . $httpCode . ', size=' . strlen($html), 'debug', 'content_parser.log');

    if ($httpCode === 404) {
        throw new Exception('Канал @' . $channelName . ' не найден. Проверьте правильность имени канала.');
    }

    if ($httpCode >= 400) {
        throw new Exception('Ошибка доступа к Telegram: HTTP ' . $httpCode);
    }

    if (empty($html)) {
        throw new Exception('Пустой ответ от Telegram');
    }

    // Проверяем, что канал существует (проверяем несколько маркеров)
    $hasTitle = stripos($html, 'tgme_page_title') !== false;
    $hasChannel = stripos($html, 'tgme_channel_info') !== false;
    $hasPosts = stripos($html, 'tgme_widget_message') !== false;

    logger('Telegram HTML markers: title=' . ($hasTitle ? 'yes' : 'no') . ', channel=' . ($hasChannel ? 'yes' : 'no') . ', posts=' . ($hasPosts ? 'yes' : 'no'), 'debug', 'content_parser.log');

    // Проверяем признаки приватного канала
    if (stripos($html, 'tgme_page_additional') !== false && stripos($html, 'private channel') !== false) {
        $madelineProtoStatus = function_exists('isMadelineProtoInstalled') && isMadelineProtoInstalled()
            ? 'установлена'
            : 'НЕ УСТАНОВЛЕНА';
        throw new Exception(
            'Канал @' . $channelName . ' является приватным. ' .
                'Веб-парсинг работает только с публичными каналами. ' .
                'Для приватных каналов установите MadelineProto (composer install) и настройте API. ' .
                'Статус MadelineProto: ' . $madelineProtoStatus
        );
    }

    if (!$hasTitle && !$hasChannel && !$hasPosts) {
        // Сохраняем начало HTML для диагностики
        $htmlPreview = mb_substr(strip_tags($html), 0, 200);
        logger('Telegram unexpected HTML: ' . $htmlPreview, 'warning', 'content_parser.log');

        $madelineProtoStatus = function_exists('isMadelineProtoInstalled') && isMadelineProtoInstalled()
            ? 'установлена'
            : 'НЕ УСТАНОВЛЕНА (установите через: cd lib/MadelineProto-8 && composer install)';

        throw new Exception(
            'Не удалось распознать страницу канала @' . $channelName . '. ' .
                'Возможные причины: 1) Канал приватный (нужна MadelineProto), ' .
                '2) Канал не существует, 3) Telegram изменил структуру страницы, ' .
                '4) Блокировка доступа. ' .
                'Статус MadelineProto: ' . $madelineProtoStatus
        );
    }

    $items = [];

    // Парсим посты через DOMDocument
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Находим все посты (div с классом tgme_widget_message)
    $postNodes = $xpath->query('//div[contains(@class, "tgme_widget_message")]');

    if ($postNodes->length === 0) {
        throw new Exception('Не удалось найти посты в канале. Возможно, канал пустой.');
    }

    $parsed = 0;
    foreach ($postNodes as $postNode) {
        if ($parsed >= $count) {
            break;
        }

        // Извлекаем текст поста
        $textNodes = $xpath->query('.//div[contains(@class, "tgme_widget_message_text")]', $postNode);
        $text = '';
        if ($textNodes->length > 0) {
            $text = trim($textNodes->item(0)->textContent);
        }

        // Если текста нет, пропускаем пост
        if (empty($text)) {
            // Проверяем, может быть это медиа-пост без текста
            $mediaNodes = $xpath->query('.//a[contains(@class, "tgme_widget_message_photo_wrap")]', $postNode);
            if ($mediaNodes->length === 0) {
                continue;
            }
            $text = 'Медиа пост';
        }

        // Генерируем заголовок (первые 100 символов текста)
        $title = mb_substr($text, 0, 100);
        if (mb_strlen($text) > 100) {
            $title .= '...';
        }
        if (empty($title)) {
            $title = 'Telegram пост';
        }

        // Извлекаем изображение
        $imageUrl = '';
        $imageNodes = $xpath->query('.//a[contains(@class, "tgme_widget_message_photo_wrap")]', $postNode);
        if ($imageNodes->length > 0) {
            $style = $imageNodes->item(0)->getAttribute('style');
            if (preg_match("#background-image:url\('([^']+)'\)#", $style, $m)) {
                $imageUrl = $m[1];
            }
        }

        // Если не нашли в style, ищем в img
        if (empty($imageUrl)) {
            $imgNodes = $xpath->query('.//img[@class="tgme_widget_message_photo"]', $postNode);
            if ($imgNodes->length > 0) {
                $imageUrl = $imgNodes->item(0)->getAttribute('src');
            }
        }

        // Извлекаем дату
        $postDate = time();
        $dateNodes = $xpath->query('.//time', $postNode);
        if ($dateNodes->length > 0) {
            $datetime = $dateNodes->item(0)->getAttribute('datetime');
            if ($datetime) {
                $ts = strtotime($datetime);
                if ($ts) {
                    $postDate = $ts;
                }
            }
        }

        // Загружаем изображение на сервер
        $localImage = '';
        if (!empty($imageUrl)) {
            $downloaded = downloadMediaToServer($imageUrl);
            if ($downloaded !== false) {
                $localImage = $downloaded;
            } else {
                // Если не удалось загрузить, используем оригинальный URL
                $localImage = $imageUrl;
            }
        }

        // Формируем контент
        $body = '';
        if (!empty($localImage)) {
            $body .= '[img]' . $localImage . '[/img]' . "\n\n";
        }
        $body .= $text;

        $items[] = [
            'title' => secure_html($title),
            'content' => $body,
            'image' => $localImage,
            'postdate' => $postDate,
        ];

        $parsed++;
    }

    if (empty($items)) {
        throw new Exception('Не удалось извлечь посты из канала');
    }

    $elapsed = (microtime(true) - $startTime) * 1000;
    logger('Telegram parsed: channel=' . $channelName . ', items=' . $parsed . ', elapsed=' . round($elapsed, 2) . 'ms', 'info', 'content_parser.log');

    return $items;
}

/**
 * Разрешает относительный URL (href/src) в абсолютный на основе базового адреса страницы
 */
function resolveSiteUrl($base, $relative)
{
    $relative = trim($relative);
    if ($relative === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $relative)) {
        return $relative;
    }
    $baseParts = parse_url($base);
    if (!$baseParts || empty($baseParts['host'])) {
        return $relative;
    }
    $scheme = $baseParts['scheme'] ?? 'https';
    $host = $baseParts['host'];
    $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
    if (strpos($relative, '//') === 0) {
        return $scheme . ':' . $relative;
    }
    if (strpos($relative, '/') === 0) {
        return $scheme . '://' . $host . $port . $relative;
    }
    $basePath = $baseParts['path'] ?? '/';
    $basePath = substr($basePath, 0, strrpos($basePath, '/') + 1);
    return $scheme . '://' . $host . $port . $basePath . $relative;
}
/**
 * Парсинг новостей с произвольного сайта по id/классу блока
 * @param string $url Адрес страницы со списком новостей
 * @param string $selector id или class блока новости (например: "news-item" или "#news" или ".card")
 * @param int $count Количество новостей для парсинга
 */
function parseSiteNews($url, $selector, $count)
{
    $startTime = microtime(true);
    $html = loadHtml($url);
    if ($html === false || empty($html)) {
        throw new Exception('Не удалось загрузить страницу сайта: ' . sanitize($url, 'info', 'content_parser.log'));
    }

    $selector = trim($selector);
    $isId = false;
    if (strpos($selector, '#') === 0) {
        $isId = true;
        $selector = substr($selector, 1);
    } elseif (strpos($selector, '.') === 0) {
        $selector = substr($selector, 1);
    }
    if ($selector === '') {
        throw new Exception('Не указан id или class блока новости');
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    if ($isId) {
        $nodes = $xpath->query('//*[@id="' . $selector . '"]');
    } else {
        $nodes = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " ' . $selector . ' ")]');
    }

    if ($nodes === false || $nodes->length === 0) {
        throw new Exception('Блоки новостей по указанному id/class не найдены на странице');
    }

    $items = [];
    $parsed = 0;
    foreach ($nodes as $node) {
        if ($parsed >= $count) {
            break;
        }
        $nodeXpath = new DOMXPath($dom);
        // Заголовок: первый заголовочный тег или ссылка внутри блока
        $title = '';
        $headingNode = $nodeXpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6', $node)->item(0);
        if ($headingNode) {
            $title = trim($headingNode->textContent);
        }
        $linkNode = $nodeXpath->query('.//a[@href]', $node)->item(0);
        if ($title === '' && $linkNode) {
            $title = trim($linkNode->textContent);
        }
        if ($title === '') {
            $title = trim(mb_substr($node->textContent, 0, 100));
        }
        if ($title === '') {
            $parsed++;
            continue;
        }
        // Изображение
        $imageUrl = '';
        $imgNode = $nodeXpath->query('.//img[@src]', $node)->item(0);
        if ($imgNode) {
            $src = $imgNode->getAttribute('data-src') ?: $imgNode->getAttribute('src');
            $imageUrl = resolveSiteUrl($url, $src);
        }
        // Дата публикации
        $postDate = time();
        $timeNode = $nodeXpath->query('.//time[@datetime]', $node)->item(0);
        if ($timeNode) {
            $ts = strtotime($timeNode->getAttribute('datetime'));
            if ($ts !== false) {
                $postDate = $ts;
            }
        }
        // Контент — текст блока целиком (без заголовка)
        $content = trim($node->textContent);
        if ($content === '') {
            $content = $title;
        }
        if (!empty($imageUrl)) {
            $localImage = downloadMediaToServer($imageUrl);
            if ($localImage !== false) {
                $imageUrl = $localImage;
            }
        }
        $items[] = [
            'title' => secure_html($title),
            'content' => secure_html($content),
            'image' => $imageUrl,
            'postdate' => $postDate,
        ];
        $parsed++;
    }

    if (empty($items)) {
        throw new Exception('Не удалось извлечь ни одной новости из найденных блоков');
    }

    $elapsed = (microtime(true) - $startTime) * 1000;
    logger('Site parsed: url=' . sanitize($url, 'info', 'content_parser.log') . ', selector=' . $selector . ', items=' . count($items) . ', elapsed=' . round($elapsed, 2) . 'ms', 'info', 'content_parser.log');

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

    logger('addNewsDirect called', 'info', 'content_parser.log');

    // Проверяем наличие необходимых глобальных переменных
    if (!is_array($userROW) || empty($userROW['id'])) {
        logger('$userROW not initialized or empty', 'error', 'content_parser.log');
        return false;
    }

    $category = intval($_REQUEST['category'] ?? 0);
    $title = $_REQUEST['title'];
    $content = $_REQUEST['ng_news_content'];

    logger('addNewsDirect params: category=' . $category . ', title_length=' . mb_strlen($title) . ', content_length=' . mb_strlen($content), 'info', 'content_parser.log');

    // Проверяем наличие $parse
    if (!is_object($parse)) {
        logger('$parse not initialized, creating new instance', 'warning', 'content_parser.log');
        if (class_exists('parse')) {
            $parse = new parse();
        } else {
            logger('parse class not found, using simple transliteration', 'warning', 'content_parser.log');
            // Простая транслитерация
            $alt_name = mb_strtolower($title);
            $alt_name = preg_replace('/[^a-z0-9_-]/u', '_', $alt_name);
        }
    }

    // Генерируем alt_name
    if (!isset($alt_name)) {
        try {
            $alt_name = mb_strtolower($parse->translit(trim($title), 1));
        } catch (\Throwable $e) {
            logger('translit error: ' . $e->getMessage() . ', using fallback', 'warning', 'content_parser.log');
            $alt_name = mb_strtolower($title);
            $alt_name = preg_replace('/[^a-z0-9_-]/u', '_', $alt_name);
        }
    }
    // Массивы должны быть в переменных для PHP 8+
    $patterns = ['/\./', '/(_{2,20})/', '/^(_+)/', '/(_+)$/'];
    $replacements = ['_', '_', '', ''];
    $alt_name = preg_replace($patterns, $replacements, $alt_name);
    if ($alt_name == '') {
        $alt_name = '_';
    }
    // Проверяем уникальность alt_name
    $i = '';
    $checkAltName = $alt_name . $i;
    while (is_array($mysql->record('select id from ' . prefix . '_news where alt_name = ' . db_squote($checkAltName) . ' limit 1'))) {
        $i++;
        $checkAltName = $alt_name . $i;
    }
    $alt_name = $checkAltName;
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
        // Сохраняем результат db_squote в переменную (PHP 8+ требует переменную по ссылке)
        $quotedValue = db_squote($v);
        $vparams[] = $quotedValue;
    }

    logger('Executing INSERT query with ' . count($vnames) . ' fields', 'info', 'content_parser.log');

    try {
        $mysql->query('insert into ' . prefix . '_news (' . implode(',', $vnames) . ') values (' . implode(',', $vparams) . ')');
        $id = $mysql->result('SELECT LAST_INSERT_ID() as id');

        logger('INSERT result: ID=' . ($id ?: 'NULL'), 'info', 'content_parser.log');

        if (!$id) {
            logger('Failed to get LAST_INSERT_ID', 'error', 'content_parser.log');
            return false;
        }

        // Добавляем в карту категорий
        if ($category > 0) {
            $quotedId = db_squote($id);
            $quotedCategory = db_squote($category);
            $mysql->query('insert into ' . prefix . '_news_map (newsID, categoryID, dt) values (' . $quotedId . ', ' . $quotedCategory . ', now())');
            logger('Added to category map: newsID=' . $id . ', categoryID=' . $category, 'info', 'content_parser.log');
        }

        logger('addNewsDirect success: ID=' . $id, 'info', 'content_parser.log');
        return $id;
    } catch (\Throwable $e) {
        logger('addNewsDirect SQL error: ' . $e->getMessage(), 'error', 'content_parser.log');
        return false;
    }
}
function createContentFromRss($type, $items)
{
    global $SUPRESS_TEMPLATE_SHOW, $mysql;
    $stats = ['added' => 0, 'skipped' => 0, 'errors' => []];

    logger('createContentFromRss called: type=' . $type . ', items=' . count($items), 'info', 'content_parser.log');

    foreach ($items as $index => $item) {
        logger('Processing item ' . ($index + 1) . ': title=' . ($item['title'] ?? 'N/A'), 'info', 'content_parser.log');

        if ($type === 'news') {
            // Проверяем дубликат ДО попытки добавления
            $itemTitle = $item['title'];
            $quotedTitle = db_squote($itemTitle);
            $existing = $mysql->record('SELECT id FROM ' . prefix . '_news WHERE title=' . $quotedTitle . ' LIMIT 1');
            if ($existing) {
                logger('Item skipped (duplicate): ' . $item['title'], 'info', 'content_parser.log');
                $stats['skipped']++;
                continue; // Пропускаем и идём к следующей
            }

            logger('Item is not duplicate, attempting to add...', 'info', 'content_parser.log');
            // Готовим данные для добавления через addNews
            $_REQUEST['title'] = $item['title'];
            // Категория публикации (передаётся из запроса)
            $_REQUEST['category'] = intval($_REQUEST['category'] ?? 0);
            $_POST['category'] = $_REQUEST['category'];
            // Инициализируем xfields сразу (для избежания ошибок "Undefined array key")
            if (!isset($_REQUEST['xfields']) || !is_array($_REQUEST['xfields'])) {
                $_REQUEST['xfields'] = [];
            }
            if (!isset($_POST['xfields']) || !is_array($_POST['xfields'])) {
                $_POST['xfields'] = [];
            }
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
            $_REQUEST['favorite'] = 0;
            $_REQUEST['pinned'] = 0;
            $_REQUEST['catpinned'] = 0;
            $_REQUEST['postdate'] = $item['postdate'];

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

            logger('Calling addNews with: title=' . $_REQUEST['title'] . ', category=' . $_REQUEST['category'] . ', approve=' . $_REQUEST['approve'], 'info', 'content_parser.log');

            // Добавляем новость через функцию CMS
            include_once(root . 'includes/inc/lib_admin.php');

            try {
                $added = addNews(['no.token' => true, 'no.editurl' => 1, 'no.files' => 1, 'no.meta' => 1]);
                logger('addNews returned: ' . ($added ? 'true (ID=' . $added . ')' : 'false'), 'info', 'content_parser.log');
            } catch (\Throwable $e) {
                logger('addNews exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 'error', 'content_parser.log');
                $added = false;
            }

            if (!$added) {
                logger('Fallback to addNewsDirect...', 'info', 'content_parser.log');
                // ФОЛБЭК: Прямое добавление в БД
                $addedDirect = addNewsDirect($item);

                logger('addNewsDirect returned: ' . ($addedDirect ? 'true (ID=' . $addedDirect . ')' : 'false'), 'info', 'content_parser.log');

                if (!$addedDirect) {
                    $stats['errors'][] = 'Не удалось добавить: ' . $item['title'];
                    logger('Failed to add item: ' . $item['title'], 'error', 'content_parser.log');
                    continue; // Пропускаем и идём к следующей
                }
                $stats['added']++;
            } else {
                $stats['added']++;
            }
        }
    }

    logger('createContentFromRss finished: added=' . $stats['added'] . ', skipped=' . $stats['skipped'] . ', errors=' . count($stats['errors']), 'info', 'content_parser.log');

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
            if ($source === 'vk') {
                $vkGroup = $_REQUEST['vk_group'] ?? '';
                if (!$vkGroup) {
                    throw new Exception('Не указана группа VK');
                }
                $vkId = normalizeVkGroup($vkGroup);
                if (!$vkId) {
                    throw new Exception('Некорректный идентификатор группы VK');
                }
                $items = parseVkPosts($vkId, $count);
            } elseif ($source === 'telegram') {
                $tgChannel = $_REQUEST['tg_channel'] ?? '';
                if (!$tgChannel) {
                    throw new Exception('Не указан канал Telegram');
                }
                $tgChannel = normalizeTelegramChannel($tgChannel);
                if (!$tgChannel) {
                    throw new Exception('Некорректное имя канала Telegram');
                }
                $items = parseTelegramChannel($tgChannel, $count);
            } elseif ($source === 'site') {
                $siteUrl = trim($_REQUEST['site_url'] ?? '');
                $siteSelector = trim($_REQUEST['site_selector'] ?? '');
                if (!$siteUrl || !validate_url($siteUrl)) {
                    throw new Exception('Некорректный URL сайта');
                }
                if (!$siteSelector) {
                    throw new Exception('Не указан id или class блока новости');
                }
                $items = parseSiteNews($siteUrl, $siteSelector, $count);
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
                // ОТЛАДКА: Добавляем информацию о том, что вернула функция
                $debugMsg = 'Не удалось получить посты из источника. ';
                $debugMsg .= 'Проверьте правильность указанных данных (URL канала, имя пользователя, токен VK API). ';

                throw new Exception($debugMsg);
            }
            // Создаем новости
            $stats = createContentFromRss('news', $items);
            if (function_exists('ob_get_level') && ob_get_level() > 0) {
                ob_end_clean();
            }

            // Формируем HTML для уведомлений в стиле NGCMS msg()
            $successMsg = '<div class="ok" style="margin: 15px 0; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">';
            $successMsg .= '✅ <strong>Парсинг завершен!</strong><br>';
            $successMsg .= 'Добавлено: <strong>' . $stats['added'] . '</strong>, ';
            $successMsg .= 'Пропущено: <strong>' . $stats['skipped'] . '</strong>';

            if (!empty($stats['errors'])) {
                $successMsg .= ', Ошибок: <strong>' . count($stats['errors']) . '</strong>';
            }
            $successMsg .= '</div>';

            // Если есть ошибки, показываем их отдельно
            if (!empty($stats['errors'])) {
                $errorList = array_slice($stats['errors'], 0, 5); // Показываем первые 5 ошибок
                $successMsg .= '<div class="warning" style="margin: 15px 0; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">';
                $successMsg .= '⚠️ <strong>Ошибки парсинга:</strong><br>';
                $successMsg .= implode('<br>', array_map('htmlspecialchars', $errorList));
                if (count($stats['errors']) > 5) {
                    $successMsg .= '<br>... и еще ' . (count($stats['errors']) - 5) . ' ошибок';
                }
                $successMsg .= '</div>';
            }

            $response = [
                'status' => 'success',
                'added' => $stats['added'],
                'skipped' => $stats['skipped'],
                'msg' => $successMsg
            ];

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
