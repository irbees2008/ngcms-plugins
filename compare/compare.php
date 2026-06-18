<?php

/**
 * Compare plugin for NGCMS
 * Product comparison via cookie, works with xfields on news entries
 */
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize};

LoadPluginLang('compare', 'main', '', '', '#');

// Register pages
register_plugin_page('compare', '',       'compare_list_page');
register_plugin_page('compare', 'add',    'compare_add');
register_plugin_page('compare', 'remove', 'compare_remove');
register_plugin_page('compare', 'clear',  'compare_clear');

// ─── Cookie management ─────────────────────────────────────────────────────
define('COMPARE_COOKIE', 'ng_compare');
define('COMPARE_LIMIT', 4);          // max items in compare list

function compare_get_ids(): array
{
    if (empty($_COOKIE[COMPARE_COOKIE])) return [];
    $raw = $_COOKIE[COMPARE_COOKIE];
    $ids = array_filter(array_map('intval', explode(',', $raw)));
    return array_values(array_unique($ids));
}

function compare_set_ids(array $ids): void
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    $ids = array_slice($ids, 0, COMPARE_LIMIT);
    $value = implode(',', $ids);
    setcookie(COMPARE_COOKIE, $value, time() + 86400 * 30, '/');
    $_COOKIE[COMPARE_COOKIE] = $value;
}

// ─── Add item ──────────────────────────────────────────────────────────────
function compare_add()
{
    global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

    $newsId = intval(sanitize($_REQUEST['id'] ?? '', 'int'));
    if ($newsId < 1) redirect(home);

    $ids = compare_get_ids();
    if (!in_array($newsId, $ids, true)) {
        if (count($ids) >= COMPARE_LIMIT) {
            array_shift($ids);  // drop oldest when limit reached
        }
        $ids[] = $newsId;
        compare_set_ids($ids);
        logger("Compare add: news_id=$newsId", 'info', 'compare.log');
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // AJAX: return JSON
        $SUPRESS_TEMPLATE_SHOW  = 1;
        $SUPRESS_MAINBLOCK_SHOW = 1;
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'count' => count($ids), 'ids' => $ids]);
        exit;
    }

    $back = $_SERVER['HTTP_REFERER'] ?? home;
    redirect($back);
}

// ─── Remove item ───────────────────────────────────────────────────────────
function compare_remove()
{
    global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

    $newsId = intval(sanitize($_REQUEST['id'] ?? '', 'int'));
    $ids    = compare_get_ids();
    $ids    = array_values(array_diff($ids, [$newsId]));
    compare_set_ids($ids);
    logger("Compare remove: news_id=$newsId", 'info', 'compare.log');

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $SUPRESS_TEMPLATE_SHOW  = 1;
        $SUPRESS_MAINBLOCK_SHOW = 1;
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'count' => count($ids), 'ids' => $ids]);
        exit;
    }

    $back = $_SERVER['HTTP_REFERER'] ?? generatePluginLink('compare', '');
    redirect($back);
}

// ─── Clear all ─────────────────────────────────────────────────────────────
function compare_clear()
{
    compare_set_ids([]);
    redirect(generatePluginLink('compare', ''));
}

// ─── Comparison list page ──────────────────────────────────────────────────
function compare_list_page()
{
    global $template, $twig, $mysql;

    $ids = compare_get_ids();

    $products = [];
    if ($ids) {
        $safeIds = implode(',', array_map('intval', $ids));
        $rows    = $mysql->select(
            "SELECT n.*, x.* FROM " . prefix . "_news n
             LEFT JOIN " . prefix . "_xfields_data x ON x.news_id = n.id
             WHERE n.id IN ($safeIds) AND n.approve = 1
             ORDER BY FIELD(n.id, $safeIds)",
            1
        );
        // Collect all xfields keys across all products for column headers
        $xfKeys = [];
        foreach ($rows as $row) {
            $products[] = $row;
            foreach ($row as $k => $v) {
                if (strpos($k, 'xfields_') === 0) {
                    $xfKeys[$k] = substr($k, 8); // strip prefix
                }
            }
        }
    } else {
        $xfKeys = [];
    }

    // Filter xfields to show — configurable via admin
    $showFields = pluginGetVariable('compare', 'xfields') ?: '';
    if ($showFields) {
        $allowed = array_flip(array_map('trim', explode(',', $showFields)));
        $xfKeys  = array_intersect_key($xfKeys, $allowed);
    }

    $tpath = locatePluginTemplates(['compare'], 'compare', 1);
    $xt    = $twig->loadTemplate($tpath['compare'] . '/compare.tpl');
    $template['vars']['mainblock'] = $xt->render([
        'products'   => $products,
        'xf_keys'    => $xfKeys,
        'ids'        => $ids,
        'limit'      => COMPARE_LIMIT,
        'clear_link' => generatePluginLink('compare', 'clear'),
        'add_link'   => generatePluginLink('compare', 'add'),
        'remove_link' => generatePluginLink('compare', 'remove'),
    ]);
}

// ─── News filter: inject compare button into news/product cards ────────────
class CompareNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        $ids = compare_get_ids();
        $tvars['vars']['compare_ids']         = $ids;
        $tvars['vars']['compare_count']        = count($ids);
        $tvars['vars']['in_compare']           = in_array($newsID, $ids, true);
        $tvars['vars']['compare_add_link']     = generatePluginLink('compare', 'add', [], ['id' => $newsID]);
        $tvars['vars']['compare_remove_link']  = generatePluginLink('compare', 'remove', [], ['id' => $newsID]);
        $tvars['vars']['compare_list_link']    = generatePluginLink('compare', '');
    }
}

register_filter('news', 'compare', new CompareNewsFilter);

// ─── Header widget: show compare counter ──────────────────────────────────
function compare_widget()
{
    global $template, $twig;
    $ids = compare_get_ids();
    $template['vars']['plugin_compare_count'] = count($ids);
    $template['vars']['plugin_compare_link']  = generatePluginLink('compare', '');
}

add_act('index', 'compare_widget');
