<?php
if (!defined('NGCMS')) {
    die('HAL');
}

use function Plugins\logger;

load_extras('core', 'tags');
include_once 'inc/similar.php';

class SimilarNewsFilter extends NewsFilter
{
    function addNewsNotify(&$tvars, $SQL, $newsid)
    {
        plugin_similar_reset($newsid);
        if (!plugin_similar_clear_cache()) {
            logger("Similar: Failed to clear output cache after adding news #{$newsid}", 'warning', 'similar.log');
        }
        return 1;
    }

    function editNewsNotify($newsID, $SQLnews, &$SQLnew, &$tvars)
    {
        if (!plugin_similar_clear_cache()) {
            logger("Similar: Failed to clear output cache after editing news #{$newsID}", 'warning', 'similar.log');
        }
        if (empty($SQLnews['approve'])) {
            return 1;
        }

        plugin_similar_resetLinked($newsID);
        plugin_similar_reset($newsID);
        return 1;
    }

    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        global $mysql, $PFILTERS, $twig, $lang;

        if (!isset($mode['style']) || $mode['style'] !== 'full') {
            return 1;
        }

        $enabled = pluginGetVariable('similar', 'similar_enabled');
        if ($enabled === null || $enabled === false || $enabled === '') {
            $enabled = 1;
        }
        if (!(int) $enabled) {
            $tvars['vars']['plugin_similar_tags'] = '';
            $tvars['vars']['plugin_similar_categ'] = '';
            return 1;
        }

        $cacheKey = 'similar_news_' . (int) $newsID . '.txt';
        $cached = cacheRetrieveFile($cacheKey, 300, 'similar');
        if ($cached !== false) {
            $tvars['vars']['plugin_similar_tags'] = $cached;
            $tvars['vars']['plugin_similar_categ'] = '';
            return 1;
        }

        if (empty($SQLnews['similar_status'])) {
            $count = (int) pluginGetVariable('similar', 'count');
            if ($count < 1 || $count > 20) {
                $count = 5;
            }
            plugin_similar_recover($newsID, $count);
        }

        $callingParams = [];
        $integrationEnabled = (int) pluginGetVariable('similar', 'pcall') === 1;
        if ($integrationEnabled) {
            $callingParams['plugin'] = 'lastnews';
            switch ((int) pluginGetVariable('similar', 'pcall_mode')) {
                case 1:
                    $callingParams['style'] = 'short';
                    break;
                case 2:
                    $callingParams['style'] = 'full';
                    break;
                default:
                    $callingParams['style'] = 'export';
            }
            load_extras('news:show');
            load_extras('news:show:one');
        }

        $templatePaths = locatePluginTemplates(
            ['similar', 'similar_entry'],
            'similar',
            (int) pluginGetVariable('similar', 'localsource')
        );
        if (empty($templatePaths['similar']) || empty($templatePaths['similar_entry'])) {
            throw new RuntimeException('Similar plugin Twig templates could not be located.');
        }

        $query = "select n.*, "
            . "si.id as si_id, si.dimension as si_dimension, si.newsID as si_newsID, "
            . "si.refNewsID as si_refNewsID, si.refNewsQuantaty as si_refNewsQuantaty, "
            . "si.refNewsTitle as si_refNewsTitle, si.refNewsDate as si_refNewsDate "
            . "from " . prefix . "_similar_index si "
            . "inner join " . prefix . "_news n on n.id = si.refNewsID "
            . "where si.newsID = " . db_squote($newsID) . " and si.dimension = 0 "
            . "order by si.refNewsQuantaty desc limit 20";
        $similarRows = $mysql->select($query);
        if (!is_array($similarRows) || !$similarRows) {
            $tvars['vars']['plugin_similar_tags'] = '';
            $tvars['vars']['plugin_similar_categ'] = '';
            return 1;
        }

        if (!isset($twig) || !function_exists('newsFillVariables')) {
            throw new RuntimeException('Similar plugin requires the NGCMS Twig engine and newsFillVariables().');
        }

        $entryTemplate = $twig->loadTemplate($templatePaths['similar_entry'] . 'similar_entry.tpl');
        $entries = [];
        foreach ($similarRows as $similar) {
            if ($integrationEnabled && !empty($PFILTERS['news']) && is_array($PFILTERS['news'])) {
                foreach ($PFILTERS['news'] as $name => $filter) {
                    if ($name !== 'similar' && method_exists($filter, 'showNewsPre')) {
                        $filter->showNewsPre($similar['id'], $similar, $callingParams);
                    }
                }
            }

            $entryVars = newsFillVariables($similar, 0, 0, 1, []);
            if ($integrationEnabled && !empty($PFILTERS['news']) && is_array($PFILTERS['news'])) {
                foreach ($PFILTERS['news'] as $name => $filter) {
                    if ($name !== 'similar' && method_exists($filter, 'showNews')) {
                        $filter->showNews($similar['id'], $similar, $entryVars, $callingParams);
                    }
                }
            }

            $entries[] = $entryTemplate->render($entryVars['vars']);
        }

        $mainTemplate = $twig->loadTemplate($templatePaths['similar'] . 'similar.tpl');
        LoadPluginLang('similar', 'main', '', 'similar', ':');
        $tvars['vars']['plugin_similar_tags'] = $mainTemplate->render([
            'entries' => implode("\n", $entries),
            'heading' => isset($lang['similar:heading']) ? $lang['similar:heading'] : 'Similar news',
        ]);
        $tvars['vars']['plugin_similar_categ'] = '';
        cacheStoreFile($cacheKey, $tvars['vars']['plugin_similar_tags'], 'similar');
        logger('Similar: Rendered ' . count($similarRows) . ' similar news for #' . (int) $newsID, 'debug', 'similar.log');
        return 1;
    }

    function massModifyNewsNotify($idList, $setValue, $currentData)
    {
        if (isset($setValue['approve'])) {
            if (!$setValue['approve']) {
                plugin_similar_reset($idList);
            } else {
                plugin_similar_resetLinked($idList);
            }
        }
        if (!plugin_similar_clear_cache()) {
            logger('Similar: Failed to clear output cache after mass news update', 'warning', 'similar.log');
        }
        return 1;
    }

    function deleteNews($newsID, $SQLnews)
    {
        global $mysql;

        plugin_similar_resetLinked($newsID);
        $mysql->query('delete from ' . prefix . '_similar_index where newsID = ' . (int) $newsID);
        if (!plugin_similar_clear_cache()) {
            logger("Similar: Failed to clear output cache after deleting news #{$newsID}", 'warning', 'similar.log');
        }
        logger("Similar: Deleted similarity data for news #{$newsID}", 'info', 'similar.log');
    }
}

if (getPluginStatusActive('tags')) {
    register_filter('news', 'similar', new SimilarNewsFilter());
}
