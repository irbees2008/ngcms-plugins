<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

add_act('index', 'rss_import_block');

// Рендер одного блока RSS (rss1, rss2, ... ) через Twig
function rss_import_render_block($index)
{
    global $config, $template, $parse, $twig;

    $vv = 'rss' . intval($index);

    $number     = intval(extra_get_param('rss_import', $vv . '_number'));
    $maxlength  = intval(extra_get_param('rss_import', $vv . '_maxlength'));
    $newslength = intval(extra_get_param('rss_import', $vv . '_newslength'));

    if ($number < 1) {
        $number = 10;
    }
    if ($maxlength < 1) {
        $maxlength = 100;
    }
    if ($newslength < 1) {
        $newslength = 100;
    }

    // Пути шаблонов через локатор (учитываем подкаталог rssN как block)
    $tpath = locatePluginTemplates(array('rss', 'entries'), 'rss_import', intval(extra_get_param('rss_import', 'localsource')), '', $vv);

    // Кэш на блок
    $cacheFileName = md5($vv . $config['theme'] . $config['default_lang']) . '.txt';
    if (extra_get_param('rss_import', 'cache')) {
        $cacheData = cacheRetrieveFile($cacheFileName, extra_get_param('rss_import', 'cacheExpire'), 'rss_import');
        if ($cacheData !== false) {
            return $cacheData;
        }
    }

    $url = extra_get_param('rss_import', $vv . '_url');

    // Проверка наличия URL
    if (empty($url)) {
        return 'RSS не доступен: URL не настроен в админке';
    }

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
        return 'RSS не доступен: не удается загрузить ' . htmlspecialchars($url);
    }

    // Проверяем на PHP ошибки в начале
    $xmlContent = trim($xmlContent);
    if (preg_match('/^(Notice|Warning|Fatal|Error|Parse error|Deprecated|Strict Standards):/i', $xmlContent)) {
        // Извлекаем первую строку ошибки
        $firstLine = explode("\n", $xmlContent)[0];
        return 'RSS не доступен: сервер возвращает PHP ошибки: ' . htmlspecialchars(mb_substr($firstLine, 0, 200));
    }

    // Проверяем, что это XML, а не HTML
    if (stripos($xmlContent, '<?xml') !== 0 && stripos($xmlContent, '<rss') !== 0) {
        // Показываем первые 500 символов для диагностики
        $preview = mb_substr($xmlContent, 0, 500);
        return 'RSS не доступен: URL возвращает не RSS. Начало ответа: ' . htmlspecialchars($preview);
    }

    // Включаем отображение ошибок для диагностики
    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($xmlContent);

    if (empty($rss)) {
        $errors = libxml_get_errors();
        libxml_clear_errors();

        // Формируем сообщение об ошибке
        $errorMsg = 'RSS не доступен';
        if (!empty($errors)) {
            $errorMsg .= ': ' . trim($errors[0]->message);
            // Показываем проблемную строку
            if (!empty($errors[0]->line)) {
                $lines = explode("\n", $xmlContent);
                $lineNum = $errors[0]->line - 1;
                if (isset($lines[$lineNum])) {
                    $errorMsg .= '. Строка ' . $errors[0]->line . ': ' . htmlspecialchars(mb_substr($lines[$lineNum], 0, 100));
                }
            }
        }

        return $errorMsg;
    }

    $entries = array();
    $imageSource = extra_get_param('rss_import', $vv . '_imageSource');
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
        if (extra_get_param('rss_import', $vv . '_content')) {
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
        $showImage = extra_get_param('rss_import', $vv . '_showImage');
        $showImage = ($showImage === null || $showImage === '') ? 1 : intval($showImage);
        if (!$showImage) {
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
        'tpl_url' => tpl_url,
        'author'  => extra_get_param('rss_import', $vv . '_name'),
    );

    // Рендер Twig шаблона блока
    $xt = $twig->loadTemplate($tpath['rss'] . 'rss.tpl');
    $output = $xt->render($tVars);

    if (extra_get_param('rss_import', 'cache')) {
        cacheStoreFile($cacheFileName, $output, 'rss_import');
    }

    return $output;
}

// Автоматическая вставка блоков как раньше: {rss1}, {rss2}, ...
function rss_import_block()
{
    global $template;
    $count = intval(extra_get_param('rss_import', 'count'));
    if ($count < 1 || $count > 20) {
        $count = 1;
    }
    for ($i = 1; $i <= $count; $i++) {
        $template['vars']['rss' . $i] = rss_import_render_block($i);
    }
}

// Вызов из Twig: {{ rss_import.show({ index: 1 }) }}
function plugin_rss_import_showTwig($params)
{
    $idx = isset($params['index']) ? intval($params['index']) : 1;
    if ($idx < 1) {
        $idx = 1;
    }
    return rss_import_render_block($idx);
}

twigRegisterFunction('rss_import', 'show', 'plugin_rss_import_showTwig');
