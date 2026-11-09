<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Ensure ng-helpers is loaded
if (!function_exists('Plugins\\logger')) {
    $ngHelpersPath = __DIR__ . '/../ng-helpers/ng-helpers.php';
    if (file_exists($ngHelpersPath)) {
        require_once $ngHelpersPath;
    } else {
        die('ng-helpers plugin is required for rss_import plugin');
    }
}

// Modernized with ng-helpers v0.2.2 (2026)
// - Using cache_get/cache_put for caching
// - Added logger() for enhanced logging
// - Requires PHP 8.0+

use function Plugins\{cache_get, cache_put, logger};

include_once __DIR__ . '/storage.php';

LoadPluginLang('rss_import', 'main', '', '', ':');

add_act('index', 'rss_import_block');

function rss_import_legacy_profile($index)
{
    $vv = 'rss' . (int)$index;
    return array(
        'profile_id' => $vv,
        'title' => (string)extra_get_param('rss_import', $vv . '_name'),
        'enabled' => 1,
        'feed_url' => (string)extra_get_param('rss_import', $vv . '_url'),
        'template' => $vv,
        'item_count' => (int)extra_get_param('rss_import', $vv . '_number') ?: 10,
        'title_length' => (int)extra_get_param('rss_import', $vv . '_maxlength') ?: 100,
        'short_length' => (int)extra_get_param('rss_import', $vv . '_newslength') ?: 100,
        'include_content' => (int)extra_get_param('rss_import', $vv . '_content'),
        'show_image' => extra_get_param('rss_import', $vv . '_showImage') === null
            || extra_get_param('rss_import', $vv . '_showImage') === ''
            ? 1
            : (int)extra_get_param('rss_import', $vv . '_showImage'),
        'image_source' => (string)extra_get_param('rss_import', $vv . '_imageSource') ?: 'enclosure',
    );
}

function rss_import_resolve_template($template)
{
    global $config;

    if (!preg_match('/^[A-Za-z0-9_-]+$/', (string)$template)) {
        return null;
    }
    $theme = $config['theme'] ?? 'default';
    $directories = array(
        site_root . 'templates/' . $theme . '/plugins/rss_import/' . $template,
        tpl_site . 'plugins/rss_import/' . $template,
        extras_dir . '/rss_import/tpl/' . $template,
    );
    foreach (array_unique($directories) as $directory) {
        if (is_file($directory . '/rss.tpl') && is_file($directory . '/entries.tpl')) {
            return $directory;
        }
    }
    return null;
}

function rss_import_profiles_installed()
{
    static $installed = null;
    if ($installed === null) {
        $installed = rss_import_storage_table_exists();
    }
    return $installed;
}

function rss_import_render_profile($profile)
{
    global $config, $parse, $twig, $lang;

    if ((int)$profile['enabled'] !== 1) {
        return '';
    }
    $templateDir = rss_import_resolve_template($profile['template']);
    if ($templateDir === null) {
        return '[RSS_IMPORT: NO TEMPLATE "' . htmlspecialchars((string)$profile['template'], ENT_QUOTES, 'UTF-8') . '"]';
    }

    $number = max(1, (int)$profile['item_count']);
    $maxlength = max(1, (int)$profile['title_length']);
    $newslength = max(1, (int)$profile['short_length']);
    $url = trim((string)$profile['feed_url']);
    $cacheExpire = pluginGetVariable('rss_import', 'cache')
        ? max(0, (int)pluginGetVariable('rss_import', 'cacheExpire')) * 60
        : 0;
    $cacheFileName = md5(implode('|', array(
        (string)$profile['profile_id'],
        (string)$profile['title'],
        (string)$profile['template'],
        (string)$profile['feed_url'],
        (string)$config['theme'],
        (string)$config['default_lang'],
        (string)$profile['item_count'],
        (string)$profile['title_length'],
        (string)$profile['short_length'],
        (string)$profile['include_content'],
        (string)$profile['show_image'],
        (string)$profile['image_source'],
    ))) . '.txt';
    if ($cacheExpire > 0) {
        $cacheKey = 'rss_import_' . $cacheFileName;
        $cached = cache_get($cacheKey);
        if ($cached !== null) {
            logger('[rss_import] Cache hit: profile=' . $profile['profile_id'], 'debug', 'rss_import.log');
            return $cached;
        }
    }

    if ($url === '') {
        return $lang['rss_import:error.no_url'];
    }
    $urlParts = parse_url($url);
    if (!is_array($urlParts) || !in_array(strtolower($urlParts['scheme'] ?? ''), array('http', 'https'), true)) {
        return $lang['rss_import:error.load_failed'] . htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    $vv = (string)$profile['profile_id'];
    // Загружаем содержимое с таймаутом
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (compatible; NGCMS RSS Import)',
            'follow_location' => true
        ]
    ]);
    $xmlContent = @file_get_contents($url, false, $context);
    if ($xmlContent === false) {
        return $lang['rss_import:error.load_failed'] . htmlspecialchars($url);
    }
    // Проверяем на PHP ошибки в начале
    $xmlContent = trim($xmlContent);
    if (preg_match('/^(Notice|Warning|Fatal|Error|Parse error|Deprecated|Strict Standards):/i', $xmlContent)) {
        // Извлекаем первую строку ошибки
        $firstLine = explode("\n", $xmlContent)[0];
        return $lang['rss_import:error.php_errors'] . htmlspecialchars(mb_substr($firstLine, 0, 200));
    }
    // Проверяем, что это XML, а не HTML
    if (stripos($xmlContent, '<?xml') !== 0 && stripos($xmlContent, '<rss') !== 0) {
        // Показываем первые 500 символов для диагностики
        $preview = mb_substr($xmlContent, 0, 500);
        return $lang['rss_import:error.not_rss'] . htmlspecialchars($preview);
    }
    // Включаем отображение ошибок для диагностики
    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($xmlContent);
    if (empty($rss)) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        // Формируем сообщение об ошибке
        $errorMsg = $lang['rss_import:error.parse_failed'];
        if (!empty($errors)) {
            $errorMsg .= ': ' . trim($errors[0]->message);
            // Показываем проблемную строку
            if (!empty($errors[0]->line)) {
                $lines = explode("\n", $xmlContent);
                $lineNum = $errors[0]->line - 1;
                if (isset($lines[$lineNum])) {
                    $errorMsg .= '. ' . sprintf($lang['rss_import:error.line'], $errors[0]->line) . ': ' . htmlspecialchars(mb_substr($lines[$lineNum], 0, 100));
                }
            }
        }
        return $errorMsg;
    }
    $entries = array();
    $imageSource = (string)$profile['image_source'];
    if (!$imageSource) {
        $imageSource = 'enclosure';
    }
    $j = 0;
    foreach ($rss->xpath('//item') as $item) {
        $entry = array();
        $title = (string)$item->title;
        $title = secure_html($title);
        if (mb_strlen($title) > $maxlength) {
            $title = mb_substr($title, 0, $maxlength);
        }
        $entry['title'] = $title;
        // Изображение из enclosure (как HTML, совместимо с текущими шаблонами)
        $entry['image'] = '';
        if (isset($item->enclosure)) {
            $enclosure = $item->enclosure;
            if (strpos($enclosure['type'], 'image/') !== false) {
                $image_url = (string)$enclosure['url'];
                // Показываем картинку из <enclosure> независимо от опции rssN_img (она относится к short_news)
                $entry['image'] = '<div class="rss-image-wrapper"><img src="' . secure_html($image_url) . '" alt="' . $title . '" /></div>';
            }
        }
        // Первое изображение из тела описания (<description>), выводим в `images`
        $entry['images'] = '';
        try {
            $descRaw = (string)$item->description;
            if ($descRaw) {
                $m = [];
                // Ищем src в первом img
                if (preg_match('#<img[^>]+src\s*=\s*([\"\'])(.*?)\1#is', $descRaw, $m) && !empty($m[2])) {
                    $imgUrl = trim($m[2]);
                } else if (preg_match('#<img[^>]+data-(?:src|original)\s*=\s*([\"\'])(.*?)\1#is', $descRaw, $m) && !empty($m[2])) {
                    // Популярные lazy-атрибуты
                    $imgUrl = trim($m[2]);
                } else {
                    $imgUrl = '';
                }
                if ($imgUrl !== '') {
                    $entry['images'] = '<div class="rss-image-wrapper"><img src="' . secure_html($imgUrl) . '" alt="' . $title . '" /></div>';
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        // Короткая новость
        if ((int)$profile['include_content']) {
            $short_news = strip_tags((string)$item->description, '<p><a><br><strong><em><ul><ol><li>');
            if ($config['blocks_for_reg']) {
                $short_news = $parse->userblocks($short_news);
            }
            if ($config['use_bbcodes']) {
                $short_news = $parse->bbcodes($short_news);
            }
            if ($config['use_smilies']) {
                $short_news = $parse->smilies($short_news);
            }
            $short_news = strip_tags($short_news);
            if (mb_strlen($short_news) > $newslength) {
                $short_news = mb_substr($short_news, 0, $newslength) . '...';
            }
            $entry['short_news'] = $short_news;
        } else {
            $entry['short_news'] = '';
        }
        $entry['link'] = (string)$item->link;
        // Выбор изображения по настройке (desc|enclosure) БЕЗ фолбэка на другой источник
        if ($imageSource == 'desc') {
            $entry['pic'] = $entry['images'];
        } else { // enclosure
            $entry['pic'] = $entry['image'];
        }
        // Глобальный флаг показа изображения для блока (по умолчанию — показывать)
        if (!(int)$profile['show_image']) {
            $entry['pic'] = '';
        }
        $entries[] = $entry;
        $j++;
        if ($j >= $number) {
            break;
        }
    }
    $tVars = array(
        'entries' => $entries,
        'entries_template' => $templateDir . '/entries.tpl',
        'tpl_url' => tpl_url,
        'author'  => (string)$profile['title'],
    );
    // Рендер Twig шаблона блока
    $xt = $twig->loadTemplate($templateDir . '/rss.tpl');
    $output = $xt->render($tVars);
    if ($cacheExpire > 0) {
        $cacheKey = 'rss_import_' . $cacheFileName;
        cache_put($cacheKey, $output, $cacheExpire / 60);
        logger('[rss_import] Cached: profile=' . $vv . ', size=' . strlen($output), 'info', 'rss_import.log');
    }
    return $output;
}

function rss_import_render_block($index)
{
    $index = (int)$index;
    if ($index < 1) {
        return '';
    }
    if (rss_import_profiles_installed()) {
        $profile = rss_import_storage_get_by_legacy_index($index);
        return is_array($profile) ? rss_import_render_profile($profile) : '';
    }
    return rss_import_render_profile(rss_import_legacy_profile($index));
}

function rss_import_block()
{
    global $template;
    if (rss_import_profiles_installed()) {
        foreach (rss_import_storage_get_all() as $profile) {
            if ($profile['legacy_index'] !== null) {
                $template['vars']['rss' . (int)$profile['legacy_index']] = rss_import_render_profile($profile);
            }
        }
        return;
    }
    $count = (int)extra_get_param('rss_import', 'count');
    if ($count < 1 || $count > 20) {
        $count = 1;
    }
    for ($i = 1; $i <= $count; $i++) {
        $template['vars']['rss' . $i] = rss_import_render_block($i);
    }
}

function plugin_rss_import_showTwig($params)
{
    if (isset($params['id']) && is_string($params['id']) && $params['id'] !== '') {
        if (!rss_import_profiles_installed()) {
            return '[RSS_IMPORT: PROFILES NOT INSTALLED]';
        }
        $profile = rss_import_storage_get_by_profile_id($params['id']);
        return is_array($profile) ? rss_import_render_profile($profile) : '[RSS_IMPORT: NO PROFILE "' . htmlspecialchars($params['id'], ENT_QUOTES, 'UTF-8') . '"]';
    }
    $idx = isset($params['index']) ? (int)$params['index'] : 1;
    return rss_import_render_block($idx);
}
twigRegisterFunction('rss_import', 'show', 'plugin_rss_import_showTwig');
