<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Fallback functions if ng-helpers is not available
if (!function_exists('Plugins\\logger')) {
    function gsmg_logger($plugin, $message, $level = 'info', $file = '')
    {
        // Silent fallback - do nothing if ng-helpers not available
        return;
    }
} else {
    function gsmg_logger($plugin, $message, $level = 'info', $file = '')
    {
        return \Plugins\logger($plugin, $message, $level, $file);
    }
}

if (!function_exists('Plugins\\get_ip')) {
    function gsmg_get_ip()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
} else {
    function gsmg_get_ip()
    {
        return \Plugins\get_ip();
    }
}

if (!function_exists('Plugins\\array_get')) {
    function gsmg_array_get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
} else {
    function gsmg_array_get($array, $key, $default = null)
    {
        return \Plugins\array_get($array, $key, $default);
    }
}

register_plugin_page('gsmg', '', 'plugin_gsmg_screen', 0);

// Load library
include_once(root . "/plugins/gsmg/lib/common.php");
function gsmg_xml_escape($value)
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function plugin_gsmg_screen()
{
    global $config, $mysql, $catz, $catmap, $SUPRESS_TEMPLATE_SHOW, $SYSTEM_FLAGS, $PFILTERS;
    global $siteDomainName, $multiDomainName, $multimaster, $multiconfig;

    $SUPRESS_TEMPLATE_SHOW = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;

    $currentSiteDomain = !empty($siteDomainName) ? trim((string)$siteDomainName) : '';
    if ($currentSiteDomain === '' && !empty($config['home_url'])) {
        $currentSiteDomain = (string)parse_url($config['home_url'], PHP_URL_HOST);
    }
    $primarySiteDomain = '';
    if (!empty($multimaster) && isset($multiconfig[$multimaster]['domains'][0])) {
        $primarySiteDomain = trim((string)$multiconfig[$multimaster]['domains'][0]);
    }
    $isSeparateSite = !empty($multiDomainName) && !empty($multimaster) && ($multiDomainName !== $multimaster);
    if (!$isSeparateSite && $currentSiteDomain !== '' && $primarySiteDomain !== '') {
        $isSeparateSite = strcasecmp($currentSiteDomain, $primarySiteDomain) !== 0;
    }
    $sitemapSiteSuffix = $isSeparateSite
        ? substr(sha1(strtolower($currentSiteDomain !== '' ? $currentSiteDomain : $multiDomainName)), 0, 12)
        : '';
    $sitemapIndexCacheFile = 'sitemap_index' . ($sitemapSiteSuffix !== '' ? '_' . $sitemapSiteSuffix : '') . '.xml';

    // Log sitemap generation start
    gsmg_logger(sprintf('Sitemap generation started from IP: %s', gsmg_get_ip()), 'info', 'gsmg.log');

    @header('Content-type: text/xml; charset=utf-8');
    $SYSTEM_FLAGS['http.headers'] = array(
        'content-type' => 'application/xml; charset=utf-8',
        'cache-control' => 'private',
    );
    // Проверяем кэш (если включён)
    if (extra_get_param('gsmg', 'cache')) {
        $cacheData = cacheRetrieveFile($sitemapIndexCacheFile, extra_get_param('gsmg', 'cacheExpire'), 'gsmg');
        if ($cacheData != false) {
            gsmg_logger('Sitemap served from cache', 'info', 'gsmg.log');
            print $cacheData;
            exit;
        }
    }
    // Максимальное количество URL в одном файле (50 000 по стандарту Google)
    $maxUrlsPerFile = 50000;
    $sitemapParts = array(); // Массив для хранения частей sitemap
    $currentPart = 0;        // Текущая часть sitemap
    $urlCount = 0;           // Счётчик URL в текущей части
    // Инициализация первой части
    $sitemapParts[$currentPart] = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemapParts[$currentPart] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $lastModifiedRow = $mysql->record("select date(from_unixtime(max(postdate))) as pd from " . prefix . "_news");
    $lastModified = !empty($lastModifiedRow['pd']) ? $lastModifiedRow['pd'] : date('Y-m-d');
    // ===== 1. Главная страница и пагинация =====
    if (extra_get_param('gsmg', 'main')) {
        $sitemapParts[$currentPart] .= "<url>";
        $sitemapParts[$currentPart] .= "<loc>" . gsmg_xml_escape(generateLink('news', 'main', array(), array(), false, true)) . "</loc>";
        $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'main_pr')) . "</priority>";
        $sitemapParts[$currentPart] .= "<lastmod>" . gsmg_xml_escape($lastModified) . "</lastmod>";
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
                $sitemapParts[$currentPart] .= "<loc>" . gsmg_xml_escape(generateLink('news', 'main', array('page' => $i), array(), false, true)) . "</loc>";
                $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'mainp_pr')) . "</priority>";
                $sitemapParts[$currentPart] .= "<lastmod>" . gsmg_xml_escape($lastModified) . "</lastmod>";
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
            $sitemapParts[$currentPart] .= "<loc>" . gsmg_xml_escape(generateLink('news', 'by.category', array('category' => $altname, 'catid' => $id), array(), false, true)) . "</loc>";
            $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'cat_pr')) . "</priority>";
            $sitemapParts[$currentPart] .= "<lastmod>" . gsmg_xml_escape($lastModified) . "</lastmod>";
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
                    $sitemapParts[$currentPart] .= "<loc>" . gsmg_xml_escape(generateLink('news', 'by.category', array('category' => $altname, 'catid' => $id, 'page' => $i), array(), false, true)) . "</loc>";
                    $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'catp_pr')) . "</priority>";
                    $sitemapParts[$currentPart] .= "<lastmod>" . gsmg_xml_escape($lastModified) . "</lastmod>";
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
            $sitemapParts[$currentPart] .= "<loc>" . gsmg_xml_escape($link) . "</loc>";
            $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'news_pr')) . "</priority>";
            $sitemapParts[$currentPart] .= "<lastmod>" . date('Y-m-d', max($rec['editdate'], $rec['postdate'])) . "</lastmod>";
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
            $sitemapParts[$currentPart] .= "<loc>" . gsmg_xml_escape($link) . "</loc>";
            $sitemapParts[$currentPart] .= "<priority>" . floatval(extra_get_param('gsmg', 'static_pr')) . "</priority>";
            $sitemapParts[$currentPart] .= "<lastmod>" . gsmg_xml_escape($lastModified) . "</lastmod>";
            $sitemapParts[$currentPart] .= "<changefreq>weekly</changefreq>";
            $sitemapParts[$currentPart] .= "</url>";
            $urlCount++;
        }
    }
    // ===== Фильтры плагинов =====
    if (!empty($PFILTERS['gsmg']) && is_array($PFILTERS['gsmg'])) {
        foreach ($PFILTERS['gsmg'] as $k => $v) {
            $v->onShow($sitemapParts[$currentPart]);
        }
    }
    // Закрываем последний файл
    $sitemapParts[$currentPart] .= "</urlset>";
    // ===== Генерация индекса sitemap =====
    $sitemapIndex = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($sitemapParts as $part => $content) {
        $fileName = 'sitemap' . ($sitemapSiteSuffix !== '' ? '_' . $sitemapSiteSuffix : '') . "_part{$part}.xml";
        // Сохраняем в корень сайта (используем dirname(root) или $_SERVER['DOCUMENT_ROOT'])
        $filePath = dirname(root) . "/" . $fileName;

        if (file_put_contents($filePath, $content) !== false) {
            gsmg_logger(sprintf('Sitemap part saved: %s (URLs: %d)', $fileName, substr_count($content, '<url>')), 'info', 'gsmg.log');
        } else {
            gsmg_logger(sprintf('Failed to save sitemap part: %s', $fileName), 'error', 'gsmg.log');
        }
        $sitemapIndex .= "  <sitemap>\n";
        $sitemapIndex .= "    <loc>" . gsmg_xml_escape(rtrim($config['home_url'], '/') . "/{$fileName}") . "</loc>\n";
        $sitemapIndex .= "    <lastmod>" . date("Y-m-d") . "</lastmod>\n";
        $sitemapIndex .= "  </sitemap>\n";
    }
    $sitemapIndex .= "</sitemapindex>\n";
    // Сохраняем в кэш (если включён)
    if (extra_get_param('gsmg', 'cache')) {
        cacheStoreFile($sitemapIndexCacheFile, $sitemapIndex, 'gsmg');
        gsmg_logger('Sitemap index cached successfully', 'info', 'gsmg.log');
    }

    // Log completion
    gsmg_logger(sprintf('Sitemap generation completed. Total parts: %d', count($sitemapParts)), 'info', 'gsmg.log');
    print $sitemapIndex;
    exit;
}
