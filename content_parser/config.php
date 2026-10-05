<?php
// Защита от прямого доступа
if (!defined('NGCMS')) {
    exit('HAL');
}

// Для AJAX запросов - ВЫКЛЮЧАЕМ HTML вывод ошибок (логируем в файл)
$isAjaxRequest = isset($_GET['action']) && in_array($_GET['action'], ['check_telegram_auth', 'start_telegram_auth', 'complete_telegram_auth', 'complete_2fa_telegram_auth', 'reset_telegram_auth']);
$isPluginRoute = isset($_POST['actionName']) || isset($_POST['source']); // Парсинг через /plugin/content_parser/

if ($isAjaxRequest || $isPluginRoute) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // ВЫКЛЮЧЕНО - чтобы не портить JSON
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/telegram_php_errors.log');

    // Регистрируем error handler для подавления любых ошибок после JSON вывода
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        error_log("[$errno] $errstr in $errfile:$errline", 3, __DIR__ . '/telegram_php_errors.log');
        return true; // Не передавать ошибку стандартному обработчику
    });

    // ОТЛАДКА: Логируем вызов
    if ($isPluginRoute) {
        error_log("=== PLUGIN ROUTE CALLED ===");
        error_log("POST: " . json_encode($_POST));
        error_log("GET: " . json_encode($_GET));
    }
}

// Загружаем языковые файлы плагина
LoadPluginLang('content_parser', 'config', '', '', ':');

// Подключаем конфигурацию плагина
pluginsLoadConfig();

// Подключаем модуль MadelineProto для Telegram (если установлен)
$madelineProtoFile = __DIR__ . '/telegram_madelineproto.php';
if (file_exists($madelineProtoFile)) {
    require_once $madelineProtoFile;
}

// Нормализация URL для защиты от дубликатов
function u_trim($s)
{
    // Обрезаем пробелы, включая юникодные
    return preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $s);
}
function normalize_url($url)
{
    $url = u_trim($url);
    if ($url === '') {
        return '';
    }
    // Если нет схемы, считаем http
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'http://' . $url;
    }
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        return '';
    }
    $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'http';
    $host = strtolower($parts['host']);
    $port = isset($parts['port']) ? (int)$parts['port'] : null;
    $path = isset($parts['path']) ? $parts['path'] : '/';
    // Удаляем лишние слеши в пути
    $path = preg_replace('#/{2,}#', '/', $path);
    // Убираем завершающий слеш, кроме корня
    if ($path !== '/' && substr($path, -1) === '/') {
        $path = substr($path, 0, -1);
    }
    $query = isset($parts['query']) ? $parts['query'] : '';
    // Сортируем параметры запроса для стабильности
    if ($query !== '') {
        parse_str($query, $q);
        ksort($q);
        $query = http_build_query($q);
    }
    // Сборка без фрагмента
    $norm = $scheme . '://' . $host;
    // Добавляем порт, если нестандартный
    if ($port && !(($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443))) {
        $norm .= ':' . $port;
    }
    $norm .= $path;
    if ($query !== '') {
        $norm .= '?' . $query;
    }
    return $norm;
}
// Сохранение настроек плагина
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $didChannelsChange = false;

    // Сохранение VK API токена
    if (isset($_POST['save_vk_token'])) {
        $vkToken = trim($_POST['vk_token']);
        pluginSetVariable('content_parser', 'vk_token', $vkToken);
        $didChannelsChange = true;
        $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_save_vk']);
        msg($msg_params);
    }

    // Сохранение настроек Telegram API
    if (isset($_POST['save_tg_api'])) {
        $tgApiId = trim($_POST['tg_api_id']);
        $tgApiHash = trim($_POST['tg_api_hash']);
        $tgUseMadelineProto = isset($_POST['tg_use_madelineproto']) ? 1 : 0;

        pluginSetVariable('content_parser', 'tg_api_id', $tgApiId);
        pluginSetVariable('content_parser', 'tg_api_hash', $tgApiHash);
        pluginSetVariable('content_parser', 'tg_use_madelineproto', $tgUseMadelineProto);
        $didChannelsChange = true;
        $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_save_telegram']);
        msg($msg_params);
    }

    // Добавление нового RSS-канала
    if (isset($_POST['new_rss_url'])) {
        $newRaw = u_trim($_POST['new_rss_url']);
        $newNorm = normalize_url($newRaw);
        if ($newNorm === '') {
            $msg_params = array('type' => 'error', 'info' => $lang['content_parser:error_invalid_rss_url']);
            msg($msg_params);
        } else {
            $channelsRaw = pluginGetVariable('content_parser', 'rss_channels');
            $channels = json_decode($channelsRaw ?: '[]', true);
            if (!is_array($channels)) {
                $channels = [];
            }
            if (!in_array($newNorm, $channels, true)) {
                $channels[] = $newNorm;
                pluginSetVariable('content_parser', 'rss_channels', json_encode($channels, JSON_UNESCAPED_UNICODE));
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_rss_added']);
                msg($msg_params);
            } else {
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_rss_exists']);
                msg($msg_params);
            }
            $didChannelsChange = true;
        }
    }
    // Удаление RSS-канала
    if (isset($_POST['delete_rss_url'])) {
        $delRaw = u_trim($_POST['delete_rss_url']);
        $delNorm = normalize_url($delRaw);
        $channelsRaw = pluginGetVariable('content_parser', 'rss_channels');
        $channels = json_decode($channelsRaw ?: '[]', true);
        if (!is_array($channels)) {
            $channels = [];
        }
        $newList = [];
        foreach ($channels as $c) {
            if ($c !== $delNorm) {
                $newList[] = $c;
            }
        }
        pluginSetVariable('content_parser', 'rss_channels', json_encode($newList, JSON_UNESCAPED_UNICODE));
        $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_rss_deleted']);
        msg($msg_params);
        $didChannelsChange = true;
    }
    // Добавление VK группы
    if (isset($_POST['new_vk_group'])) {
        $rawGroup = trim($_POST['new_vk_group']);
        if ($rawGroup === '') {
            $msg_params = array('type' => 'error', 'info' => $lang['content_parser:error_invalid_vk_group']);
            msg($msg_params);
        } else {
            $vkRaw = pluginGetVariable('content_parser', 'vk_groups');
            $vkGroups = json_decode($vkRaw ?: '[]', true);
            if (!is_array($vkGroups)) {
                $vkGroups = [];
            }
            if (!in_array($rawGroup, $vkGroups, true)) {
                $vkGroups[] = $rawGroup;
                pluginSetVariable('content_parser', 'vk_groups', json_encode($vkGroups, JSON_UNESCAPED_UNICODE));
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_vk_added']);
                msg($msg_params);
            } else {
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_vk_exists']);
                msg($msg_params);
            }
            $didChannelsChange = true;
        }
    }
    // Удаление VK группы
    if (isset($_POST['delete_vk_group'])) {
        $rawGroup = trim($_POST['delete_vk_group']);
        $vkRaw = pluginGetVariable('content_parser', 'vk_groups');
        $vkGroups = json_decode($vkRaw ?: '[]', true);
        if (!is_array($vkGroups)) {
            $vkGroups = [];
        }
        $newVk = [];
        foreach ($vkGroups as $g) {
            if ($g !== $rawGroup) {
                $newVk[] = $g;
            }
        }
        pluginSetVariable('content_parser', 'vk_groups', json_encode($newVk, JSON_UNESCAPED_UNICODE));
        $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_vk_deleted']);
        msg($msg_params);
        $didChannelsChange = true;
    }

    // Добавление Telegram канала
    if (isset($_POST['new_tg_channel'])) {
        $rawChannel = trim($_POST['new_tg_channel']);
        if ($rawChannel === '') {
            $msg_params = array('type' => 'error', 'info' => $lang['content_parser:error_invalid_tg_channel']);
            msg($msg_params);
        } else {
            $tgRaw = pluginGetVariable('content_parser', 'tg_channels');
            $tgChannels = json_decode($tgRaw ?: '[]', true);
            if (!is_array($tgChannels)) {
                $tgChannels = [];
            }
            if (!in_array($rawChannel, $tgChannels, true)) {
                $tgChannels[] = $rawChannel;
                pluginSetVariable('content_parser', 'tg_channels', json_encode($tgChannels, JSON_UNESCAPED_UNICODE));
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_tg_added']);
                msg($msg_params);
            } else {
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_tg_exists']);
                msg($msg_params);
            }
            $didChannelsChange = true;
        }
    }

    // Удаление Telegram канала
    if (isset($_POST['delete_tg_channel'])) {
        $rawChannel = trim($_POST['delete_tg_channel']);
        $tgRaw = pluginGetVariable('content_parser', 'tg_channels');
        $tgChannels = json_decode($tgRaw ?: '[]', true);
        if (!is_array($tgChannels)) {
            $tgChannels = [];
        }
        $newTg = [];
        foreach ($tgChannels as $c) {
            if ($c !== $rawChannel) {
                $newTg[] = $c;
            }
        }
        pluginSetVariable('content_parser', 'tg_channels', json_encode($newTg, JSON_UNESCAPED_UNICODE));
        $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_tg_deleted']);
        msg($msg_params);
        $didChannelsChange = true;
    }

    // Добавление источника-сайта (URL + id/class блока новости)
    if (isset($_POST['new_site_url'])) {
        $rawUrl = u_trim($_POST['new_site_url']);
        $rawSelector = trim($_POST['new_site_selector'] ?? '');
        $normUrl = normalize_url($rawUrl);
        if ($normUrl === '' || $rawSelector === '') {
            $msg_params = array('type' => 'error', 'info' => $lang['content_parser:error_invalid_site_url']);
            msg($msg_params);
        } else {
            $siteRaw = pluginGetVariable('content_parser', 'site_sources');
            $sites = json_decode($siteRaw ?: '[]', true);
            if (!is_array($sites)) {
                $sites = [];
            }
            $exists = false;
            foreach ($sites as $s) {
                if (($s['url'] ?? '') === $normUrl && ($s['selector'] ?? '') === $rawSelector) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $sites[] = ['url' => $normUrl, 'selector' => $rawSelector];
                pluginSetVariable('content_parser', 'site_sources', json_encode($sites, JSON_UNESCAPED_UNICODE));
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_site_added']);
                msg($msg_params);
            } else {
                $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_site_exists']);
                msg($msg_params);
            }
            $didChannelsChange = true;
        }
    }
    // Удаление источника-сайта по индексу
    if (isset($_POST['delete_site_index'])) {
        $delIndex = (int)$_POST['delete_site_index'];
        $siteRaw = pluginGetVariable('content_parser', 'site_sources');
        $sites = json_decode($siteRaw ?: '[]', true);
        if (!is_array($sites)) {
            $sites = [];
        }
        $newSites = [];
        foreach ($sites as $i => $s) {
            if ($i !== $delIndex) {
                $newSites[] = $s;
            }
        }
        pluginSetVariable('content_parser', 'site_sources', json_encode(array_values($newSites), JSON_UNESCAPED_UNICODE));
        $msg_params = array('type' => 'info', 'info' => $lang['content_parser:info_site_deleted']);
        msg($msg_params);
        $didChannelsChange = true;
    }

    // Если были изменения, сохраняем конфигурацию
    if ($didChannelsChange) {
        pluginsSaveConfig();
    }
}

/**
 * Рекурсивное удаление директории
 * @param string $dir Путь к директории
 * @return bool Успешность удаления
 */
function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return @unlink($dir);
    }

    $items = array_diff(scandir($dir), ['.', '..']);

    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }

    return @rmdir($dir);
}

// Основная функция для отображения интерфейса автоматизации
function automation()
{
    global $twig, $PHP_SELF, $mysql, $lang;
    // Определяем пути к шаблонам
    $tpath = locatePluginTemplates(
        ['config/main', 'config/automation'],
        'content_parser',
        1
    );
    // Проверяем существование шаблонов
    if (empty($tpath['config/main']) || empty($tpath['config/automation'])) {
        die($lang['content_parser:error_templates_missing']);
    }
    try {
        // Загружаем основной шаблон
        $mainTemplate = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
        // Загружаем шаблон автоматизации
        $automationTemplate = $twig->loadTemplate($tpath['config/automation'] . 'config/automation.tpl');
        // Получаем текущие настройки плагина
        $rssUrl = pluginGetVariable('content_parser', 'rss_url');
        $rssLimit = pluginGetVariable('content_parser', 'rss_limit');
        $cacheEnabled = pluginGetVariable('content_parser', 'cache_enabled');
        $cacheExpire = pluginGetVariable('content_parser', 'cache_expire');
        $rssChannelsRaw = pluginGetVariable('content_parser', 'rss_channels');
        $rssChannels = json_decode($rssChannelsRaw ?: '[]', true);
        if (!is_array($rssChannels)) {
            $rssChannels = [];
        }
        $vkGroupsRaw = pluginGetVariable('content_parser', 'vk_groups');
        $vkGroups = json_decode($vkGroupsRaw ?: '[]', true);
        if (!is_array($vkGroups)) {
            $vkGroups = [];
        }

        $tgChannelsRaw = pluginGetVariable('content_parser', 'tg_channels');
        $tgChannels = json_decode($tgChannelsRaw ?: '[]', true);
        if (!is_array($tgChannels)) {
            $tgChannels = [];
        }

        $siteSourcesRaw = pluginGetVariable('content_parser', 'site_sources');
        $siteSources = json_decode($siteSourcesRaw ?: '[]', true);
        if (!is_array($siteSources)) {
            $siteSources = [];
        }

        $vkToken = pluginGetVariable('content_parser', 'vk_token') ?: '';

        $tgApiId = pluginGetVariable('content_parser', 'tg_api_id') ?: '';
        $tgApiHash = pluginGetVariable('content_parser', 'tg_api_hash') ?: '';
        $tgUseMadelineProto = pluginGetVariable('content_parser', 'tg_use_madelineproto') ?: 0;

        // Проверяем, установлена ли MadelineProto
        $madelineProtoInstalled = function_exists('isMadelineProtoInstalled')
            ? isMadelineProtoInstalled()
            : class_exists('danog\\MadelineProto\\API');

        // Предупреждение о производительности на Windows
        $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        if ($madelineProtoInstalled && $isWindows) {
            $msg_params = array('type' => 'warning', 'info' => $lang['content_parser:info_madelineproto_windows']);
            msg($msg_params);
        }

        // Загружаем список категорий из базы данных
        $categories = [];
        $catRows = $mysql->select("SELECT id, name FROM " . prefix . "_category ORDER BY name");
        if (is_array($catRows) && count($catRows) > 0) {
            foreach ($catRows as $row) {
                $categories[] = [
                    'id' => intval($row['id']),
                    'name' => $row['name'],
                ];
            }
        }
        $contentParserLang = [];
        $contentParserPrefix = 'content_parser:';
        foreach ($lang as $key => $value) {
            if (strpos($key, $contentParserPrefix) === 0) {
                $contentParserLang[substr($key, strlen($contentParserPrefix))] = $value;
            }
        }
        $parserLangJson = json_encode(
            $contentParserLang,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
        if ($parserLangJson === false) {
            throw new RuntimeException('Unable to encode content_parser language strings: ' . json_last_error_msg());
        }

        // Переменные для шаблона автоматизации
        $tVarsAutomation = [
            'rss_url' => $rssUrl,
            'rss_limit' => $rssLimit,
            'cache_enabled' => $cacheEnabled,
            'cache_expire' => $cacheExpire,
            'rss_channels' => $rssChannels,
            'vk_groups' => $vkGroups,
            'tg_channels' => $tgChannels,
            'site_sources' => $siteSources,
            'vk_token' => $vkToken,
            'tg_api_id' => $tgApiId,
            'tg_api_hash' => $tgApiHash,
            'tg_use_madelineproto' => $tgUseMadelineProto,
            'madelineproto_installed' => $madelineProtoInstalled,
            'categories' => $categories,
            'lang' => $lang,
            'parser_lang_json' => $parserLangJson,
        ];
        // Рендерим шаблон автоматизации
        $renderedAutomation = $automationTemplate->render($tVarsAutomation);
        // Переменные для основного шаблона
        $tVarsMain = [
            'entries' => $renderedAutomation,
            'php_self' => $PHP_SELF,
            'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=content_parser',
            'skins_url' => skins_url,
            'admin_url' => admin_url,
            'home' => home,
            'current_title' => $lang['content_parser:title_settings'],
            'lang' => $lang,
        ];
        // Выводим основной шаблон
        echo $mainTemplate->render($tVarsMain);
    } catch (Exception $e) {
        // Обработка ошибок Twig
        die($lang['content_parser:error_template_render'] . $e->getMessage());
    }
}
// Основной обработчик запросов
switch ($_REQUEST['action'] ?? '') {
    case 'ajax_parse':
        // Проксируем AJAX-запрос в серверный обработчик парсинга
        include_once root . 'engine/plugins/content_parser/content_parser.php';
        plugin_content_parse();
        break;

    // AJAX: Тест MadelineProto (диагностика)
    case 'test_madelineproto':
        @ob_clean(); // Очистка буфера вывода
        header('Content-Type: application/json');
        $result = [
            'test_time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'session_status' => session_status(),
            'session_id' => session_id(),
            'madelineproto_file_exists' => file_exists(__DIR__ . '/telegram_madelineproto.php'),
            'bootstrap_file_exists' => file_exists(__DIR__ . '/madelineproto_bootstrap.php'),
            'vendor_exists' => file_exists(__DIR__ . '/lib/MadelineProto-8/vendor/autoload.php'),
            'constant_defined' => defined('MADELINEPROTO_AVAILABLE'),
            'constant_value' => defined('MADELINEPROTO_AVAILABLE') ? MADELINEPROTO_AVAILABLE : null,
            'class_exists' => class_exists('danog\\MadelineProto\\API'),
            'function_exists' => [
                'checkTelegramAuth' => function_exists('checkTelegramAuth'),
                'startTelegramAuth' => function_exists('startTelegramAuth'),
                'completeTelegramAuth' => function_exists('completeTelegramAuth'),
                'complete2FATelegramAuth' => function_exists('complete2FATelegramAuth'),
            ],
            'api_credentials' => [
                'api_id_set' => !empty(pluginGetVariable('content_parser', 'tg_api_id')),
                'api_hash_set' => !empty(pluginGetVariable('content_parser', 'tg_api_hash')),
            ]
        ];
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;

        // AJAX: Проверка авторизации MadelineProto
    case 'check_telegram_auth':
        @ob_clean(); // Очистка буфера вывода
        header('Content-Type: application/json');
        try {
            if (!file_exists(__DIR__ . '/telegram_madelineproto.php')) {
                echo json_encode(['authorized' => false, 'error' => $lang['content_parser:error_telegram_handler_missing']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            if (!function_exists('checkTelegramAuth')) {
                echo json_encode(['authorized' => false, 'error' => $lang['content_parser:error_check_auth_missing']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            $result = checkTelegramAuth();
            echo json_encode($result);

            // Немедленная отправка ответа клиенту и закрытие соединения
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Отправляет ответ и закрывает соединение
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit; // НЕМЕДЛЕННЫЙ выход для предотвращения ngShutdownHandler
        } catch (\Throwable $e) {
            echo json_encode([
                'authorized' => false,
                'error' => $lang['content_parser:error_prefix'] . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit;
        }

        // AJAX: Начало авторизации (отправка номера)
    case 'start_telegram_auth':
        @ob_clean(); // Очистка буфера вывода
        header('Content-Type: application/json');
        $logFile = __DIR__ . '/telegram_auth_debug.log';
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "=== START AUTH REQUEST ===\n", FILE_APPEND);

        try {
            file_put_contents($logFile, "1. Получение номера телефона...\n", FILE_APPEND);
            $phone = trim($_POST['phone'] ?? '');
            file_put_contents($logFile, "2. Номер: " . $phone . "\n", FILE_APPEND);

            if (empty($phone)) {
                file_put_contents($logFile, "3. ОШИБКА: Номер пустой\n", FILE_APPEND);
                echo json_encode(['success' => false, 'error' => $lang['content_parser:error_auth_phone_required']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            file_put_contents($logFile, "4. Проверка функции startTelegramAuth...\n", FILE_APPEND);
            if (!function_exists('startTelegramAuth')) {
                file_put_contents($logFile, "5. ОШИБКА: Функция не найдена\n", FILE_APPEND);
                echo json_encode(['success' => false, 'error' => $lang['content_parser:error_start_auth_missing']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            file_put_contents($logFile, "6. Вызов startTelegramAuth...\n", FILE_APPEND);
            $result = startTelegramAuth($phone);
            file_put_contents($logFile, "7. Результат: " . json_encode($result) . "\n", FILE_APPEND);
            file_put_contents($logFile, "=== END AUTH REQUEST (SUCCESS) ===\n\n", FILE_APPEND);

            echo json_encode($result);

            // Немедленная отправка ответа клиенту и закрытие соединения
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Отправляет ответ и закрывает соединение
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit; // НЕМЕДЛЕННЫЙ выход для предотвращения ngShutdownHandler
        } catch (\Throwable $e) {
            $errorInfo = [
                'success' => false,
                'error' => $lang['content_parser:error_prefix'] . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            file_put_contents($logFile, "8. EXCEPTION: " . json_encode($errorInfo) . "\n", FILE_APPEND);
            file_put_contents($logFile, "=== END AUTH REQUEST (ERROR) ===\n\n", FILE_APPEND);

            echo json_encode($errorInfo);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit;
        }

        // AJAX: Завершение авторизации (ввод кода)
    case 'complete_telegram_auth':
        @ob_clean(); // Очистка буфера вывода
        header('Content-Type: application/json');
        try {
            $code = trim($_POST['code'] ?? '');
            if (empty($code)) {
                echo json_encode(['success' => false, 'error' => $lang['content_parser:error_auth_code_required']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            if (!function_exists('completeTelegramAuth')) {
                echo json_encode(['success' => false, 'error' => $lang['content_parser:error_complete_auth_missing']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            $result = completeTelegramAuth($code);
            echo json_encode($result);

            // Немедленная отправка ответа клиенту и закрытие соединения
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Отправляет ответ и закрывает соединение
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit; // НЕМЕДЛЕННЫЙ выход для предотвращения ngShutdownHandler
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => $lang['content_parser:error_prefix'] . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit;
        }

        // AJAX: Завершение 2FA авторизации
    case 'complete_2fa_telegram_auth':
        @ob_clean(); // Очистка буфера вывода
        header('Content-Type: application/json');
        try {
            $password = $_POST['password'] ?? '';
            if (empty($password)) {
                echo json_encode(['success' => false, 'error' => $lang['content_parser:error_auth_password_required']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            if (!function_exists('complete2FATelegramAuth')) {
                echo json_encode(['success' => false, 'error' => $lang['content_parser:error_complete_2fa_missing']]);
                if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
                else while (ob_get_level()) @ob_end_flush();
                exit;
            }

            $result = complete2FATelegramAuth($password);
            echo json_encode($result);

            // Немедленная отправка ответа клиенту и закрытие соединения
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Отправляет ответ и закрывает соединение
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit; // НЕМЕДЛЕННЫЙ выход для предотвращения ngShutdownHandler
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => $lang['content_parser:error_prefix'] . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit;
        }

    case 'reset_telegram_auth':
        @ob_clean();
        header('Content-Type: application/json');

        try {
            $sessionFile = __DIR__ . '/telegram_session.madeline';
            $sessionLockFile = $sessionFile . '.lock';

            if (!file_exists($sessionFile)) {
                echo json_encode(['success' => true, 'message' => $lang['content_parser:info_auth_session_missing']]);
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    while (ob_get_level()) @ob_end_flush();
                }
                exit;
            }

            // Удаляем lock файл если есть
            if (file_exists($sessionLockFile)) {
                @unlink($sessionLockFile);
            }

            // telegram_session.madeline может быть как файлом, так и директорией
            $deleted = false;

            if (is_dir($sessionFile)) {
                // Это директория - используем рекурсивное удаление
                $deleted = deleteDirectory($sessionFile);
            } else {
                // Это файл - используем unlink
                $deleted = @unlink($sessionFile);
            }

            if ($deleted) {
                echo json_encode(['success' => true, 'message' => $lang['content_parser:info_auth_session_deleted']]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $lang['content_parser:error_auth_session_delete'],
                    'manual_path' => $sessionFile,
                    'hint' => 'Remove-Item "' . $sessionFile . '" -Recurse -Force'
                ]);
            }

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                while (ob_get_level()) @ob_end_flush();
            }
            exit;
        }

    default:
        automation();
        break;
}
