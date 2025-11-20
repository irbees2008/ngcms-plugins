<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
class OGNEWSNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        global $CurrentHandler, $config;
        // Загружаем настройки
        $titleLen        = intval(pluginGetVariable('ognews', 'title_length')) ?: 200;
        $descrLen        = intval(pluginGetVariable('ognews', 'description_length')) ?: 220;
        $descrSource     = pluginGetVariable('ognews', 'description_source') ?: 'description';
        $twTitleLen      = intval(pluginGetVariable('ognews', 'twitter_title_length')) ?: $titleLen;
        $twDescrLen      = intval(pluginGetVariable('ognews', 'twitter_description_length')) ?: $descrLen;
        $keywordsLen     = intval(pluginGetVariable('ognews', 'keywords_length')) ?: 0; // 0 = без ограничения

        // Функция безопасной обрезки (UTF-8)
        $trim = function ($text, $len) {
            if (!$len) return $text;
            $text = strip_tags($text);
            if (function_exists('mb_substr')) return mb_substr($text, 0, $len, 'UTF-8');
            return substr($text, 0, $len);
        };
        if (($CurrentHandler['handlerName'] == 'news') || ($CurrentHandler['handlerName'] == 'print')) {
            if ($SQLnews['alt_name'] == $CurrentHandler['params']['altname']) {
                if (isset($mode)) {
                    $alink = checkLinkAvailable('uprofile', 'show') ?
                        generateLink('uprofile', 'show', array('name' => $SQLnews['author'], 'id' => $SQLnews['author_id'])) :
                        generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $SQLnews['author'], 'id' => $SQLnews['author_id']));
                    // Источник описания
                    $rawDescr = ($descrSource == 'content') ? $SQLnews['content'] : $SQLnews['description'];
                    $cleanDescr = stripBBCode($rawDescr);
                    $ogTitle       = secure_html($trim($SQLnews['title'], $titleLen));
                    $ogDescr       = secure_html($trim($cleanDescr, $descrLen));
                    $twitterTitle  = secure_html($trim($SQLnews['title'], $twTitleLen));
                    $twitterDescr  = secure_html($trim($cleanDescr, $twDescrLen));
                    $keywords      = secure_html(($keywordsLen ? $trim($SQLnews['keywords'], $keywordsLen) : $SQLnews['keywords']));
                    register_htmlvar('plain', '<meta property="og:type" content="article">');
                    register_htmlvar('plain', '<meta property="og:url" content="' . home . newsGenerateLink($SQLnews) . '">');
                    register_htmlvar('plain', '<meta property="og:site_name" content="' . secure_html($config["home_title"]) . '">');
                    register_htmlvar('plain', '<meta property="og:title" content="' . $ogTitle . '">');
                    register_htmlvar('plain', '<meta property="og:description" content="' . $ogDescr . '">');
                    /*
                    register_htmlvar('plain','<meta property="og:description" content="'.secure_html(substr(strip_tags(stripBBCode($SQLnews["content"])), 0, 220)).'">');
                    */
                    register_htmlvar('plain', '<meta property="article:author" content="' . home . $alink . '">');
                    register_htmlvar('plain', '<meta property="article:section" content="' . explode(',', strip_tags(@GetCategories($SQLnews['catid'])))[0] . '">');
                    register_htmlvar('plain', '<meta property="article:tag" content="' . $keywords . '">');
                    if ($tvars['vars']['news']['embed']['imgCount'] > 0) {
                        foreach ($tvars['vars']['news']['embed']['images'] as $img_item) {
                            register_htmlvar('plain', '<meta property="og:image" content="' . $img_item . '" />');
                        }
                        /*
                        register_htmlvar('plain','<meta property="og:image" content="'.$tvars['vars']['news']['embed']['images'][0].'" />');
                        */
                    }
                    if (!empty($SQLnews['#images'])) {
                        foreach ($SQLnews['#images'] as $img_item) {
                            register_htmlvar('plain', '<meta property="og:image" content="' . home . '/uploads/dsn/' . $img_item['folder'] . '/' . $img_item['name'] . '" />');
                        }
                    }
                    register_htmlvar('plain', '<meta property="twitter:card" content="summary_large_image">');
                    register_htmlvar('plain', '<meta property="twitter:title" content="' . $twitterTitle . '">');
                    register_htmlvar('plain', '<meta property="twitter:description" content="' . $twitterDescr . '">');
                    if (!empty($SQLnews['#images'])) {
                        foreach ($SQLnews['#images'] as $img_item) {
                            register_htmlvar('plain', '<meta property="twitter:image:src" content="' . home . '/uploads/dsn/' . $img_item['folder'] . '/' . $img_item['name'] . '" />');
                        }
                    }
                    /* if ($tvars['vars']['news']['embed']['imgCount'] > 0) {
                        foreach ($tvars['vars']['news']['embed']['images'] as $img_item) {
                            register_htmlvar('plain', '<meta property="twitter:image:src" content="' . $img_item . '" />');
                        }
                    }*/
                    register_htmlvar('plain', '<meta property="twitter:url" content="' . home . newsGenerateLink($SQLnews) . '">');
                    register_htmlvar('plain', '<meta property="twitter:domain" content="' . home . '">');
                    register_htmlvar('plain', '<meta property="twitter:site" content="' . secure_html($config["home_title"]) . '">');
                }
            }
        }
        return 1;
    }
}
function stripBBCode($text_to_search)
{
    $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
    $replace = '';
    return preg_replace($pattern, $replace, $text_to_search);
}
register_filter('news', 'ognews', new OGNEWSNewsFilter);
