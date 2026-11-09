<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Modified with ng-helpers v0.2.2 functions (2026-01-29)
// - Enhanced caching with cache_get/cache_put
// - Added sanitization for security
// - Added comprehensive logging
// - Added benchmark for performance monitoring
// - Added array helpers for cleaner code

use function Plugins\{
    cache_get,
    cache_put,
    logger,
    sanitize,
    benchmark,
    array_get,
    array_only,
    clamp,
    is_ajax
};
// Classes for traffic handling
// - static pages
class ADSProStaticFilter extends StaticFilter
{
    function showStatic($staticID, $SQLstatic, &$tvars, $mode)
    {
        global $adsPRO_cache;
        $adsPRO_cache['flag.static'] = true;
        $adsPRO_cache['static.id'] = $staticID;
        return 1;
    }
}
// - news
class ADSProNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        global $adsPRO_cache;
        if ($mode['style'] == 'full') {
            $adsPRO_cache['flag.news'] = true;
            $adsPRO_cache['news.id'] = $newsID;
        }
        return 1;
    }
}
// Initiate interceptors
// - for common pages [ process page, generate output ]
add_act('index_post', 'plugin_ads_pro');
// - for extracting data from static pages
register_filter('static', 'ads_pro', new ADSProStaticFilter);
// - for extracting data from news
if (pluginGetVariable('ads_pro', 'support_news')) {
    register_filter('flag.news', 'ads_pro', new ADSProNewsFilter);
}
// Initiate global internal variable
$adsPRO_cache = array(
    'flag.static'   => false,
    'static.id'     => null,
    'flag.news'     => false,
    'news.id'       => null,
    'flag.category' => false,
    'category.id'   => null,
    'flag.main'     => false
);
// Main function of plugin
function plugin_ads_pro()
{
    global $template, $config, $CurrentHandler, $catmap, $mysql, $adsPRO_cache;
    if (ads_pro_migrate_legacy_data()) {
        $dataConfig = ads_pro_get_blocks();
    } else {
        $dataConfig = ads_pro_get_legacy_blocks();
    }
    if ($CurrentHandler['params'][0] == '/') $adsPRO_cache['flag.main'] = true;
    if (isset($CurrentHandler['params']['catid'])) {
        $adsPRO_cache['flag.category'] = true;
        $adsPRO_cache['category.id'] = $CurrentHandler['params']['catid'];
    } else if (isset($CurrentHandler['params']['category'])) {
        $adsPRO_cache['flag.category'] = true;
        $adsPRO_cache['category.id'] = array_search($CurrentHandler['params']['category'], $catmap);
    }
    if ($CurrentHandler['pluginName']) {
        $adsPRO_cache['flag.plugin'] = true;
        $adsPRO_cache['plugin.id'] = $CurrentHandler['pluginName'];
    }
    // Indexing structure for block display
    $blockDisplayList = array();
    $t_time = time();
    foreach ($dataConfig as $blockID => $blockRecords) {
        if (!$blockID) continue;
        // Обеспечиваем совместимость с Twig: добавляем префикс к числовым ID
        $tplVarName = $blockID;
        if (is_numeric($blockID)) {
            $tplVarName = 'block_' . $blockID;
        }
        // Initiate block output if it's not filled yet
        if (!isset($template['vars'][$tplVarName])) $template['vars'][$tplVarName] = '';
        // Scan all records of this block
        foreach ($blockRecords as $blockIndexNum => $blockInfo) {
            //print "<pre>ADS_PRO_DATA [$blockID][$blockIndexNum]:".var_export($blockInfo, true)."</pre>";
            // Skip inactive blocks
            if (!$blockInfo['state']) continue;
            // By default block is visible
            $blockIsVisible = true;
            // Skip blocks if they're displayed `by time` & shouldn't be displayed now
            if ($blockInfo['state'] == 2) {
                if ($blockInfo['start_view'] && $blockInfo['start_view'] > $t_time)
                    $blockIsVisible = false;
                if ($blockInfo['end_view'] && $blockInfo['end_view'] <= $t_time)
                    $blockIsVisible = false;
            }
            // Skip block if it's marked as `not to be displayed`
            if (!$blockIsVisible) continue;
            // Process location flags [if configured for specific block]
            if (is_array($blockInfo['location'])) {
                $blockIsVisible = false;
                $if_break = false;
                foreach ($blockInfo['location'] as $locRecord) {
                    // Scan visibility parameters
                    // view == 0 - display
                    // view == 1 - do not display
                    switch ($locRecord['mode']) {
                        // Everywhere
                        case 0:
                            if ($locRecord['view']) {
                                $blockIsVisible = false;
                                $if_break = true;
                            } else $blockIsVisible = true;
                            break;
                        // Main page
                        case 1:
                            if ($adsPRO_cache['flag.main']) {
                                if ($locRecord['view']) {
                                    $blockIsVisible = false;
                                    $if_break = true;
                                } else $blockIsVisible = true;
                            }
                            break;
                        // Everywhere EXCEPT main page
                        case 2:
                            if (!$adsPRO_cache['flag.main']) {
                                if ($locRecord['view']) {
                                    $blockIsVisible = false;
                                    $if_break = true;
                                } else $blockIsVisible = true;
                            }
                            break;
                        // In category
                        case 3:
                            if ($adsPRO_cache['flag.category']) {
                                if (!$locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                } else if ($adsPRO_cache['category.id'] == $locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                }
                            }
                            break;
                        // In static page
                        case 4:
                            if ($adsPRO_cache['flag.static']) {
                                if (!$locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                } else if ($adsPRO_cache['static.id'] == $locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                }
                            }
                            break;
                        // In news page
                        case 5:
                            if ($adsPRO_cache['flag.news']) {
                                if (!$locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                } else if ($adsPRO_cache['news.id'] == $locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                }
                            }
                            break;
                        // In plugin
                        case 6:
                            if ($adsPRO_cache['flag.plugin']) {
                                if (!$locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                } else if ($adsPRO_cache['plugin.id'] == $locRecord['id']) {
                                    if ($locRecord['view']) {
                                        $blockIsVisible = false;
                                        $if_break = true;
                                    } else $blockIsVisible = true;
                                }
                            }
                            break;
                    }
                    if ($if_break) break;
                }
                if (!$blockIsVisible) continue;
            }
            // Fine, block is visible, add it into display list
            $blockDisplayList[$blockID][] = $blockIndexNum;
        }
    }
    //print "<pre>ADS INDEXING:".var_export($blockDisplayList, true)."</pre>";
    // Scan blocks, marked to be displayed
    foreach ($blockDisplayList as $blockID => $blockRecList) {
        // Обеспечиваем совместимость с Twig: добавляем префикс к числовым ID
        $tplVarName = $blockID;
        if (is_numeric($blockID)) {
            $tplVarName = 'block_' . $blockID;
        }
        // Process multidisplay mode
        if ((count($blockRecList) > 1) && (($mdm = pluginGetVariable('ads_pro', 'multidisplay_mode')) > 0)) {
            // - First active
            if ($mdm == 1) {
                $blockRecList = array($blockRecList[0]);
            }
            // - Random
            if ($mdm == 2) {
                $blockRecList = array($blockRecList[array_rand($blockRecList)]);
            }
        }
        foreach ($blockRecList as $blockIndexNum) {
            // Retrieve block info
            $blockInfo = $dataConfig[$blockID][$blockIndexNum];
            //print "<pre>BID:".var_export($blockInfo, true)."</pre>";
            // Cache non-PHP ads blocks
            if ($blockInfo['type'] != 1) {
                $cacheKey = 'ads_pro:' . $blockID . ':' . $blockIndexNum . ':' . $blockInfo['type'];
                $cacheData = cache_get($cacheKey);

                if ($cacheData !== null) {
                    $template['vars'][$tplVarName] .= $cacheData;
                    logger('Cache HIT for block: id=' . $blockIndexNum . ', block=' . $blockID, 'debug', 'ads_pro.log');
                    continue;
                }

                logger('Cache MISS for block: id=' . $blockIndexNum . ', block=' . $blockID, 'debug', 'ads_pro.log');

                $description = $blockInfo['ads_blok'];
                if ($blockInfo['type'] == 2) {
                    $description = nl2br(sanitize($description, 'string'));
                }

                $template['vars'][$tplVarName] .= $description;

                // Cache for 8 hours (30000 seconds ≈ 8.3 hours)
                $cacheDuration = clamp(intval(pluginGetVariable('ads_pro', 'cache_duration', 30000)), 300, 86400);
                cache_put($cacheKey, $description, $cacheDuration);

                logger('Rendered and cached block: id=' . $blockIndexNum . ', size=' . strlen($description) . ' bytes', 'info', 'ads_pro.log');
            } else {
                // PHP type block - execute dynamically (no caching)
                $description = $blockInfo['ads_blok'];

                logger('Executing PHP block: id=' . $blockIndexNum . ', block=' . $blockID, 'info', 'ads_pro.log');

                // Execute PHP block and capture output
                $out2 = '';
                if ($description) {
                    ob_start();
                    try {
                        @eval($description);
                        $out2 = ob_get_contents();
                    } catch (Exception $e) {
                        logger('PHP block error: id=' . $blockIndexNum . ', error=' . $e->getMessage(), 'error', 'ads_pro.log');
                    }
                    ob_end_clean();
                }

                logger('PHP block executed: id=' . $blockIndexNum . ', output=' . strlen($out2) . ' bytes', 'info', 'ads_pro.log');

                $template['vars'][$tplVarName] .= $out2;
            }
        }
    }
}
function ads_pro_get_blocks()
{
    global $mysql;

    $blocks = array();
    $schema = $mysql->record("SHOW COLUMNS FROM " . prefix . "_ads_pro LIKE 'sort_order'");
    if (!is_array($schema)) {
        return ads_pro_get_legacy_blocks();
    }

    $rows = $mysql->select('SELECT id, block_name, description, ads_blok, type, state, start_view, end_view, location FROM ' . prefix . '_ads_pro WHERE block_name <> \'\' ORDER BY sort_order, id');
    foreach ($rows as $row) {
        $name = (string)$row['block_name'];
        $location = @unserialize($row['location'], array('allowed_classes' => false));
        if (!is_array($location)) {
            $location = array(1 => array('mode' => 0, 'view' => 0));
        }

        $blocks[$name][intval($row['id'])] = array(
            'description' => $row['description'],
            'ads_blok' => $row['ads_blok'],
            'type' => intval($row['type']),
            'state' => intval($row['state']),
            'start_view' => intval($row['start_view']) ?: null,
            'end_view' => intval($row['end_view']) ?: null,
            'location' => $location,
        );
    }

    return $blocks;
}

function ads_pro_get_legacy_blocks()
{
    global $mysql;

    $blocks = pluginGetVariable('ads_pro', 'data');
    if (!is_array($blocks)) {
        return array();
    }

    foreach ($blocks as $name => &$records) {
        foreach ($records as $id => &$record) {
            $row = $mysql->record('SELECT ads_blok FROM ' . prefix . '_ads_pro WHERE id=' . intval($id));
            $record['ads_blok'] = is_array($row) ? $row['ads_blok'] : '';
        }
        unset($record);
    }
    unset($records);

    return $blocks;
}

function ads_pro_migrate_legacy_data()
{
    global $mysql, $PLUGINS;

    $legacyData = pluginGetVariable('ads_pro', 'data');
    if (!is_array($legacyData)) {
        return true;
    }

    $schema = $mysql->record("SHOW COLUMNS FROM " . prefix . "_ads_pro LIKE 'sort_order'");
    if (!is_array($schema)) {
        return false;
    }

    $sortOrder = 0;
    foreach ($legacyData as $blockName => $records) {
        if (!is_array($records)) {
            return false;
        }

        foreach ($records as $id => $block) {
            $id = intval($id);
            if ($id < 1 || !is_array($block)) {
                return false;
            }

            $name = (string)$blockName;
            $description = isset($block['description']) ? $block['description'] : '';
            $type = max(0, min(2, intval(isset($block['type']) ? $block['type'] : 0)));
            $state = max(0, min(2, intval(isset($block['state']) ? $block['state'] : 0)));
            $startView = intval(isset($block['start_view']) ? $block['start_view'] : 0);
            $endView = intval(isset($block['end_view']) ? $block['end_view'] : 0);
            $location = isset($block['location']) && is_array($block['location'])
                ? $block['location']
                : array(1 => array('mode' => 0, 'view' => 0));

            if (
                $name === 'fase'
                && preg_match('/^Восстановленный блок(?: #\d+)?$/u', trim((string)$description))
                && $type === 0
                && $state === 0
                && !$startView
                && !$endView
                && $location === array(1 => array('mode' => 0, 'view' => 0))
            ) {
                $sortOrder++;
                continue;
            }

            $row = $mysql->record('SELECT id FROM ' . prefix . '_ads_pro WHERE id=' . $id);
            if (is_array($row)) {
                $queryResult = $mysql->query('UPDATE ' . prefix . '_ads_pro SET '
                    . 'block_name=' . db_squote($name) . ', '
                    . 'description=' . db_squote($description) . ', '
                    . 'type=' . $type . ', state=' . $state . ', '
                    . 'start_view=' . $startView . ', end_view=' . $endView . ', '
                    . 'location=' . db_squote(serialize($location)) . ', '
                    . 'sort_order=' . $sortOrder . ' WHERE id=' . $id . ' LIMIT 1');
            } else {
                $queryResult = $mysql->query('INSERT INTO ' . prefix . '_ads_pro '
                    . '(id, block_name, description, ads_blok, type, state, start_view, end_view, location, sort_order) VALUES ('
                    . $id . ', ' . db_squote($name) . ', ' . db_squote($description) . ', ' . db_squote('') . ', '
                    . $type . ', ' . $state . ', ' . $startView . ', ' . $endView . ', '
                    . db_squote(serialize($location)) . ', ' . $sortOrder . ')');
            }

            if ($queryResult === null) {
                return false;
            }
            if (!is_array($mysql->record('SELECT id FROM ' . prefix . '_ads_pro WHERE id=' . $id))) {
                return false;
            }
            $sortOrder++;
        }
    }

    $backupFile = conf_pconfig . '.ads_pro-migration.bak';
    if (is_file(conf_pconfig) && !is_file($backupFile) && !@copy(conf_pconfig, $backupFile)) {
        return false;
    }

    unset($PLUGINS['config']['ads_pro']['data']);
    if (!pluginsSaveConfig()) {
        $PLUGINS['config']['ads_pro']['data'] = $legacyData;
        return false;
    }

    return true;
}
