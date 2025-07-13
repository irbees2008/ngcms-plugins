<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
add_act('index', 'rss_import_block');
function rss_import_block() {
    global $config, $tpl, $template, $parse;
    $count = extra_get_param('rss_import', 'count');
    if ((intval($count) < 1) || (intval($count) > 20))
        $count = 1;
    for ($i = 1; $i <= $count; $i++) {
        $vv = 'rss' . $i;
        $number = intval(extra_get_param('rss_import', $vv . '_number'));
        $maxlength = intval(extra_get_param('rss_import', $vv . '_maxlength'));
        $newslength = intval(extra_get_param('rss_import', $vv . '_newslength'));
        // Determine paths for all template files
        if (intval(extra_get_param('rss_import', 'localsource')) == 1) $overrideTemplatePath = root . '/plugins/rss_import/tpl/' . $vv . '';
        else $overrideTemplatePath = tpl_site . 'plugins/rss_import/' . $vv . '';
        $tpath = array('entries' => $overrideTemplatePath, 'rss' => $overrideTemplatePath);
        // Generate cache file name [ we should take into account SWITCHER plugin ]
        $cacheFileName = md5($vv . $config['theme'] . $config['default_lang']) . '.txt';
        if (extra_get_param('rss_import', 'cache')) {
            $cacheData = cacheRetrieveFile($cacheFileName, extra_get_param('rss_import', 'cacheExpire'), 'rss_import');
            if ($cacheData != false) {
                // We got data from cache. Return it and stop
                $template['vars'][$vv] = $cacheData;
                return;
            }
        }
        if (!$number) {
            $number = 10;
        }
        if (!$maxlength) {
            $maxlength = 100;
        }
        if (!$newslength) {
            $newslength = 100;
        }
        $url = extra_get_param('rss_import', $vv . '_url');       //адрес RSS ленты
        $rss = simplexml_load_file($url);       //Интерпретирует XML-файл в объект
        if (empty($rss))
            return $template['vars'][$vv] = 'RSS не доступен';
        //цикл для обхода всей RSS ленты
        $j = 1;
        $result = '';
        foreach ($rss->xpath('//item') as $item) {
            $tvars = array('vars' => array());
            $title = $item->title;
            // Обработка заголовка
            if (strlen($title) > $maxlength) {
                $tvars['vars']['title'] = substr(secure_html($title), 0, $maxlength) . "";
            } else {
                $tvars['vars']['title'] = secure_html($title);
            }
            // Обработка изображения (новая переменная)
            $tvars['vars']['image'] = ''; // По умолчанию пусто
            if (isset($item->enclosure)) {
                $enclosure = $item->enclosure;
                if (strpos($enclosure['type'], 'image/') !== false) {
                    $image_url = (string)$enclosure['url'];
                    if (!extra_get_param('rss_import', $vv . '_img')) {
                        $tvars['vars']['image'] = '<div class="rss-image-wrapper"><img src="' . secure_html($image_url) . '" alt="' . secure_html($title) . '" /></div>';
                    }
                }
            }
            // Обработка short_news
            // Обработка short_news
            if (extra_get_param('rss_import', $vv . '_content')) {
                $short_news = strip_tags($item->description, '<p><a><br><strong><em><ul><ol><li>');
                // Применяем обработку BB-кодов и смайлов
                if ($config['blocks_for_reg']) $short_news = $parse->userblocks($short_news);
                if ($config['use_bbcodes']) $short_news = $parse->bbcodes($short_news);
                if ($config['use_smilies']) $short_news = $parse->smilies($short_news);
                // Удаляем все HTML теги после обработки (если нужно чисто текст)
                $short_news = strip_tags($short_news);
                // ОБРЕЗКА ТЕКСТА ДО УКАЗАННОЙ ДЛИНЫ
                if (strlen($short_news) > $newslength) {
                    $short_news = substr($short_news, 0, $newslength) . '...';
                }
                $tvars['vars']['short_news'] = $short_news;
            }
            $tvars['vars']['link'] = (string)$item->link;
            $tpl->template('entries', $tpath['entries']);
            $tpl->vars('entries', $tvars);
            $result .= $tpl->show('entries');
            if ($j == $number) break;
            $j++;
        }
        $tpl->template('rss', $tpath['rss']);
        $tpl->vars('rss', array('vars' => array('entries' => $result, 'author' => extra_get_param('rss_import', $vv . '_name'))));
        $output = $tpl->show('rss');
        $template['vars'][$vv] = $output;
        if (extra_get_param('rss_import', 'cache')) {
            cacheStoreFile($cacheFileName, $output, 'rss_import');
        }
    }
}
