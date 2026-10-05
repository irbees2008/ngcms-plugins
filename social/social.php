<?php

if (!defined('NGCMS')) die('HAL');

class socialNewsFilter extends NewsFilter
{

    function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        global $config, $twig;

        // Проверка кеша
        if (pluginGetVariable('social', 'cache')) {
            $cacheFileName = md5('social' . $newsID . $config['home_url'] . $config['skin'] . $config['default_lang']) . '.txt';

            $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('social', 'cacheExpire'), 'social');
            if ($cacheData != false) {
                $tvars['vars']['plugin_social'] = $cacheData;
                return 1;
            }
        }

        // Получаем URL новости
        $link = newsGenerateLink($SQLnews, false, 0, true);

        // Интеграция с bit.ly для сокращения URL (если включена)
        if (pluginGetVariable('social', 'integration') && pluginGetVariable('social', 'login') && pluginGetVariable('social', 'api_key')) {
            $link = make_bitly_url($link, pluginGetVariable('social', 'login'), pluginGetVariable('social', 'api_key'), 'json');
        }

        $title = $SQLnews['title'];

        // Извлекаем краткое содержание
        list($short_news, $full_news) = explode('<!--more-->', $SQLnews['content'], 2);
        $content = mb_substr(strip_tags($short_news ? $short_news : $full_news), 0, 200);

        // Получаем список сервисов
        $services = pluginGetVariable('social', 'services');

        $entries = array();
        if (is_array($services)) {
            foreach ($services as $id => $row) {
                if ($row['active']) {
                    // Формируем URL с подстановкой параметров
                    $service_url = str_replace(
                        array('%title%', '%content%', '%link%'),
                        array(urlencode($title), urlencode($content), urlencode($link)),
                        $row['link']
                    );

                    // Определяем путь к изображению
                    $img_path = '';
                    if (!pluginGetVariable('social', 'localsource') && is_dir(root . 'templates/' . $config['skin'] . '/plugins/social/images')) {
                        $img_path = $config['home_url'] . '/templates/' . $config['skin'] . '/plugins/social/images/' . $row['img'];
                    } elseif (pluginGetVariable('social', 'localsource') && pluginGetVariable('social', 'skin') && is_dir(__DIR__ . '/tpl/skins/' . pluginGetVariable('social', 'skin') . '/images')) {
                        $img_path = $config['home_url'] . '/engine/plugins/social/tpl/skins/' . pluginGetVariable('social', 'skin') . '/images/' . $row['img'];
                    } elseif (is_dir(__DIR__ . '/tpl/skins/default/images')) {
                        $img_path = $config['home_url'] . '/engine/plugins/social/tpl/skins/default/images/' . $row['img'];
                    }

                    $entries[] = array(
                        'url' => $service_url,
                        'title' => $title,
                        'desc' => $row['title'] ? $row['title'] : '',
                        'img' => $img_path,
                        'has_img' => !empty($img_path)
                    );
                }
            }
        }

        // Определяем путь к шаблонам
		$localsource = intval(pluginGetVariable('social', 'localsource'));
		$skin = pluginGetVariable('social', 'skin');
		if (empty($skin)) {
			$skin = 'default';
		}
		$tpath = locatePluginTemplates(array('social'), 'social', $localsource, $skin);
        $tVars = array(
            'home' => $config['home_url'],
            'entries' => $entries
        );

        $tvars['vars']['plugin_social'] = $twig->render($tpath['social'] . 'social.tpl', $tVars);

        // Сохраняем в кеш
        if (pluginGetVariable('social', 'cache')) {
            cacheStoreFile($cacheFileName, $tvars['vars']['plugin_social'], 'social');
        }

        return 1;
    }
}

register_filter('news', 'social', new socialNewsFilter);

/*
	Bit.ly shortener
	Based on code from David Walsh
	http://davidwalsh.name/bitly-php
*/
function make_bitly_url($url, $login, $appkey, $format = 'xml', $version = '2.0.1')
{
    // Создаем URL для API bit.ly
    $bitly = 'http://api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode($url) . '&login=' . $login . '&apiKey=' . $appkey . '&format=' . $format;

    // Получаем ответ
    $response = @file_get_contents($bitly);

    if (!$response) {
        return $url; // Возвращаем оригинальный URL при ошибке
    }

    // Парсим в зависимости от формата
    if (strtolower($format) == 'json') {
        $json = @json_decode($response, true);
        return isset($json['results'][$url]['shortUrl']) ? $json['results'][$url]['shortUrl'] : $url;
    } else { // xml
        $xml = @simplexml_load_string($response);
        return $xml && isset($xml->results->nodeKeyVal->hash) ? 'http://bit.ly/' . $xml->results->nodeKeyVal->hash : $url;
    }
}
