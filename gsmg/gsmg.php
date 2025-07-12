<?php
if (!defined('NGCMS')) die ('HAL');

register_plugin_page('gsmg', '', 'plugin_gsmg_screen', 0);

include_once(root . "/plugins/gsmg/lib/common.php");

function plugin_gsmg_screen() {
    global $config, $mysql, $catz, $catmap, $SUPRESS_TEMPLATE_SHOW, $SYSTEM_FLAGS, $PFILTERS;

    $SUPRESS_TEMPLATE_SHOW = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;
    @header('Content-type: text/xml; charset=utf-8');
    $SYSTEM_FLAGS['http.headers'] = array(
        'content-type' => 'application/xml; charset=utf-8',
        'cache-control' => 'private',
    );

    // Проверяем кэш (если включён)
    if (extra_get_param('gsmg', 'cache')) {
        $cacheData = cacheRetrieveFile('sitemap_index.xml', extra_get_param('gsmg', 'cacheExpire'), 'gsmg');
        if ($cacheData != false) {
            print $cacheData;
            return;
        }
    }

    // Максимальное количество URL в одном файле
    $maxUrlsPerFile = 50000;
    $sitemapParts = array();
    $currentPart = 0;
    $urlCount = 0;

    // Инициализация первой части
    $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    // ===== 1. Главная страница и пагинация =====
    if (extra_get_param('gsmg', 'main')) {
        $sitemapParts[$currentPart] .= "<url>";
        $sitemapParts[$currentPart] .= "<loc>" . generateLink('news', 'main', array(), array(), false, true) . "</loc>";
        $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'main_pr')) . "</priority>";
        $lm = $mysql->record("select date(from_unixtime(max(postdate))) as pd from " . prefix . "_news");
        $sitemapParts[$currentPart] .= "<lastmod>" . $lm['pd'] . "</lastmod>";
        $sitemapParts[$currentPart] .= "<changefreq>daily</changefreq>";
        $sitemapParts[$currentPart] .= "</url>";
        $urlCount++;

        if (extra_get_param('gsmg', 'mainp')) {
            $cnt = $mysql->record("select count(*) as cnt from " . prefix . "_news");
            $pages = ceil($cnt['cnt'] / $config['number']);
            for ($i = 2; $i <= $pages; $i++) {
                if ($urlCount >= $maxUrlsPerFile) {
                    $sitemapParts[$currentPart] .= "</urlset>";
                    $currentPart++;
                    $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>';
                    $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                    $urlCount = 0;
                }
                $sitemapParts[$currentPart] .= "<url>";
                $sitemapParts[$currentPart] .= "<loc>" . generateLink('news', 'main', array('page' => $i), array(), false, true) . "</loc>";
                $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'mainp_pr')) . "</priority>";
                $sitemapParts[$currentPart] .= "<lastmod>" . $lm['pd'] . "</lastmod>";
                $sitemapParts[$currentPart] .= "<changefreq>daily</changefreq>";
                $sitemapParts[$currentPart] .= "</url>";
                $urlCount++;
            }
        }
    }

    // ===== 2. Категории =====
    if (extra_get_param('gsmg', 'cat')) {
        foreach ($catmap as $id => $altname) {
            if ($urlCount >= $maxUrlsPerFile) {
                $sitemapParts[$currentPart] .= "</urlset>";
                $currentPart++;
                $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>';
                $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                $urlCount = 0;
            }
            $sitemapParts[$currentPart] .= "<url>";
            $sitemapParts[$currentPart] .= "<loc>" . generateLink('news', 'by.category', array('category' => $altname, 'catid' => $id), array(), false, true) . "</loc>";
            $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'cat_pr')) . "</priority>";
            $sitemapParts[$currentPart] .= "<lastmod>" . $lm['pd'] . "</lastmod>";
            $sitemapParts[$currentPart] .= "<changefreq>daily</changefreq>";
            $sitemapParts[$currentPart] .= "</url>";
            $urlCount++;

            if (extra_get_param('gsmg', 'catp')) {
                $cn = ($catz[$altname]['number'] > 0) ? $catz[$altname]['number'] : $config['number'];
                $pages = ceil($catz[$altname]['posts'] / $cn);
                for ($i = 2; $i <= $pages; $i++) {
                    if ($urlCount >= $maxUrlsPerFile) {
                        $sitemapParts[$currentPart] .= "</urlset>";
                        $currentPart++;
                        $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>';
                        $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                        $urlCount = 0;
                    }
                    $sitemapParts[$currentPart] .= "<url>";
                    $sitemapParts[$currentPart] .= "<loc>" . generateLink('news', 'by.category', array('category' => $altname, 'catid' => $id, 'page' => $i), array(), false, true) . "</loc>";
                    $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'catp_pr')) . "</priority>";
                    $sitemapParts[$currentPart] .= "<lastmod>" . $lm['pd'] . "</lastmod>";
                    $sitemapParts[$currentPart] .= "<changefreq>daily</changefreq>";
                    $sitemapParts[$currentPart] .= "</url>";
                    $urlCount++;
                }
            }
        }
    }

    // ===== 3. Новости =====
    if (extra_get_param('gsmg', 'news')) {
        $query = "select id, postdate, author, author_id, alt_name, editdate, catid from " . prefix . "_news where approve = 1 order by id desc";
        foreach ($mysql->select($query, 1) as $rec) {
            if ($urlCount >= $maxUrlsPerFile) {
                $sitemapParts[$currentPart] .= "</urlset>";
                $currentPart++;
                $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>';
                $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                $urlCount = 0;
            }
            $link = newsGenerateLink($rec, false, 0, true);
            $sitemapParts[$currentPart] .= "<url>";
            $sitemapParts[$currentPart] .= "<loc>" . $link . "</loc>";
            $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'news_pr')) . "</priority>";
            $sitemapParts[$currentPart] .= "<lastmod>" . strftime("%Y-%m-%d", max($rec['editdate'], $rec['postdate'])) . "</lastmod>";
            $sitemapParts[$currentPart] .= "<changefreq>daily</changefreq>";
            $sitemapParts[$currentPart] .= "</url>";
            $urlCount++;
        }
    }

    // ===== 4. Статические страницы =====
    if (extra_get_param('gsmg', 'static')) {
        $query = "select id, alt_name from " . prefix . "_static where approve = 1";
        foreach ($mysql->select($query, 1) as $rec) {
            if ($urlCount >= $maxUrlsPerFile) {
                $sitemapParts[$currentPart] .= "</urlset>";
                $currentPart++;
                $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>';
                $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                $urlCount = 0;
            }
            $link = generatePluginLink('static', '', array('altname' => $rec['alt_name'], 'id' => $rec['id']), array(), false, true);
            $sitemapParts[$currentPart] .= "<url>";
            $sitemapParts[$currentPart] .= "<loc>" . $link . "</loc>";
            $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'static_pr')) . "</priority>";
            $sitemapParts[$currentPart] .= "<lastmod>" . $lm['pd'] . "</lastmod>";
            $sitemapParts[$currentPart] .= "<changefreq>weekly</changefreq>";
            $sitemapParts[$currentPart] .= "</url>";
            $urlCount++;
        }
    }

    // ===== Фильтры плагинов =====
    if (is_array($PFILTERS['gsmg'])) {
        foreach ($PFILTERS['gsmg'] as $k => $v) {
            $v->onShow($sitemapParts[$currentPart]);
        }
    }

    // Закрываем последний файл
    $sitemapParts[$currentPart] .= "</urlset>";

    // ===== Генерация индекса sitemap =====
    $sitemapIndex = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($sitemapParts as $part => $content) {
        $fileName = "sitemap_part{$part}.xml";
        file_put_contents(root . "/" . $fileName, $content);
        $sitemapIndex .= "<sitemap>";
        $sitemapIndex .= "<loc>" . $config['home_url'] . "/{$fileName}</loc>";
        $sitemapIndex .= "<lastmod>" . date("Y-m-d") . "</lastmod>";
        $sitemapIndex .= "</sitemap>";
    }

    $sitemapIndex .= "</sitemapindex>";

    // Выводим индексный файл
    print $sitemapIndex;

    // Сохраняем в кэш (если включён)
    if (extra_get_param('gsmg', 'cache')) {
        cacheStoreFile('sitemap_index.xml', $sitemapIndex, 'gsmg');
    }
}
