<?php
// Защита от прямого доступа
if (!defined('NGCMS')) {
    exit('HAL');
}
// Подключаем конфигурацию плагина
pluginsLoadConfig();
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
// Нормализация Instagram username (для защиты от дубликатов)
function normalize_instagram_username($username)
{
    $u = trim($username);
    if ($u === '') {
        return '';
    }
    // убрать ведущий @ и пробелы
    $u = preg_replace('#^@#', '', $u);
    $u = preg_replace('#\s+#', '', $u);
    // нижний регистр для единообразия
    $u = mb_strtolower($u);
    return $u;
}
// Сохранение настроек плагина
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $didChannelsChange = false;
    // Сохранение VK API токена
    if (isset($_POST['save_vk_token'])) {
        $vkToken = trim($_POST['vk_token']);
        pluginSetVariable('content_parser', 'vk_token', $vkToken);
        $didChannelsChange = true;
        msg(['type' => 'info', 'message' => 'VK API токен сохранен']);
    }
    // Добавление нового RSS-канала
    if (isset($_POST['new_rss_url'])) {
        $newRaw = u_trim($_POST['new_rss_url']);
        $newNorm = normalize_url($newRaw);
        if ($newNorm === '') {
            msg(['type' => 'error', 'message' => 'Некорректный URL RSS-канала']);
        } else {
            $channelsRaw = pluginGetVariable('content_parser', 'rss_channels');
            $channels = json_decode($channelsRaw ?: '[]', true);
            if (!is_array($channels)) {
                $channels = [];
            }
            if (!in_array($newNorm, $channels, true)) {
                $channels[] = $newNorm;
                pluginSetVariable('content_parser', 'rss_channels', json_encode($channels, JSON_UNESCAPED_UNICODE));
                msg(['type' => 'info', 'message' => 'RSS-канал добавлен']);
            } else {
                msg(['type' => 'info', 'message' => 'Канал уже существует (после нормализации)']);
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
        msg(['type' => 'info', 'message' => 'RSS-канал удалён']);
        $didChannelsChange = true;
    }
    // Добавление Instagram-аккаунта
    if (isset($_POST['new_ig_user'])) {
        $rawUser = trim($_POST['new_ig_user']);
        $normUser = normalize_instagram_username($rawUser);
        if ($normUser === '') {
            msg(['type' => 'error', 'message' => 'Некорректное имя пользователя Instagram']);
        } else {
            $accRaw = pluginGetVariable('content_parser', 'ig_accounts');
            $acc = json_decode($accRaw ?: '[]', true);
            if (!is_array($acc)) {
                $acc = [];
            }
            if (!in_array($normUser, $acc, true)) {
                $acc[] = $normUser;
                pluginSetVariable('content_parser', 'ig_accounts', json_encode($acc, JSON_UNESCAPED_UNICODE));
                msg(['type' => 'info', 'message' => 'Instagram-аккаунт добавлен']);
            } else {
                msg(['type' => 'info', 'message' => 'Аккаунт уже существует (после нормализации)']);
            }
            $didChannelsChange = true;
        }
    }
    // Удаление Instagram-аккаунта
    if (isset($_POST['delete_ig_user'])) {
        $rawUser = trim($_POST['delete_ig_user']);
        $normUser = normalize_instagram_username($rawUser);
        $accRaw = pluginGetVariable('content_parser', 'ig_accounts');
        $acc = json_decode($accRaw ?: '[]', true);
        if (!is_array($acc)) {
            $acc = [];
        }
        $newAcc = [];
        foreach ($acc as $u) {
            if ($u !== $normUser) {
                $newAcc[] = $u;
            }
        }
        pluginSetVariable('content_parser', 'ig_accounts', json_encode($newAcc, JSON_UNESCAPED_UNICODE));
        msg(['type' => 'info', 'message' => 'Instagram-аккаунт удалён']);
        $didChannelsChange = true;
    }
    // Добавление VK группы
    if (isset($_POST['new_vk_group'])) {
        $rawGroup = trim($_POST['new_vk_group']);
        if ($rawGroup === '') {
            msg(['type' => 'error', 'message' => 'Некорректное имя/URL группы VK']);
        } else {
            $vkRaw = pluginGetVariable('content_parser', 'vk_groups');
            $vkGroups = json_decode($vkRaw ?: '[]', true);
            if (!is_array($vkGroups)) {
                $vkGroups = [];
            }
            if (!in_array($rawGroup, $vkGroups, true)) {
                $vkGroups[] = $rawGroup;
                pluginSetVariable('content_parser', 'vk_groups', json_encode($vkGroups, JSON_UNESCAPED_UNICODE));
                msg(['type' => 'info', 'message' => 'VK группа добавлена']);
            } else {
                msg(['type' => 'info', 'message' => 'Группа уже существует']);
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
        msg(['type' => 'info', 'message' => 'VK группа удалена']);
        $didChannelsChange = true;
    }
    // Если были изменения, сохраняем конфигурацию
    if ($didChannelsChange) {
        pluginsSaveConfig();
    }
}
// Основная функция для отображения интерфейса автоматизации
function automation()
{
    global $twig, $PHP_SELF, $mysql;
    // Определяем пути к шаблонам
    $tpath = locatePluginTemplates(
        ['config/main', 'config/automation'],
        'content_parser',
        1
    );
    // Проверяем существование шаблонов
    if (empty($tpath['config/main']) || empty($tpath['config/automation'])) {
        die('Ошибка: Не найдены необходимые шаблоны.');
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
        $igAccRaw = pluginGetVariable('content_parser', 'ig_accounts');
        $igAccounts = json_decode($igAccRaw ?: '[]', true);
        if (!is_array($igAccounts)) {
            $igAccounts = [];
        }
        $vkGroupsRaw = pluginGetVariable('content_parser', 'vk_groups');
        $vkGroups = json_decode($vkGroupsRaw ?: '[]', true);
        if (!is_array($vkGroups)) {
            $vkGroups = [];
        }
        $vkToken = pluginGetVariable('content_parser', 'vk_token') ?: '';
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
        // Переменные для шаблона автоматизации
        $tVarsAutomation = [
            'rss_url' => $rssUrl,
            'rss_limit' => $rssLimit,
            'cache_enabled' => $cacheEnabled,
            'cache_expire' => $cacheExpire,
            'rss_channels' => $rssChannels,
            'ig_accounts' => $igAccounts,
            'vk_groups' => $vkGroups,
            'vk_token' => $vkToken,
            'categories' => $categories,
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
            'current_title' => 'Настройки парсера RSS',
        ];
        // Выводим основной шаблон
        echo $mainTemplate->render($tVarsMain);
    } catch (Exception $e) {
        // Обработка ошибок Twig
        die('Ошибка шаблонизатора: ' . $e->getMessage());
    }
}
// Основной обработчик запросов
switch ($_REQUEST['action'] ?? '') {
    case 'ajax_parse':
        // Проксируем AJAX-запрос в серверный обработчик парсинга
        include_once root . 'engine/plugins/content_parser/content_parser.php';
        plugin_content_parse();
        break;
    default:
        automation();
        break;
}
