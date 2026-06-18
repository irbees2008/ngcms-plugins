<?php
if (!defined('NGCMS')) {
    exit('HAL');
}
LoadPluginLang('eshop', 'main', '', '', '#');
add_act('index', 'eshop_header_show');
register_plugin_page('eshop', '', 'eshop');
register_plugin_page('eshop', 'show', 'show_eshop');
register_plugin_page('eshop', 'search', 'search_eshop');
register_plugin_page('eshop', 'stocks', 'stocks_eshop');
register_plugin_page('eshop', 'compare', 'compare_eshop');
register_plugin_page('eshop', 'currency', 'currency_eshop');
register_plugin_page('eshop', 'yml_export', 'yml_export_eshop');
register_plugin_page('eshop', 'ebasket_list', 'plugin_ebasket_list');
register_plugin_page('eshop', 'ebasket_update', 'plugin_ebasket_update');
register_plugin_page('eshop', 'order', 'order_eshop');
register_plugin_page('eshop', 'payment', 'payment_eshop');
register_plugin_page('eshop', 'api', 'api_eshop');
include_once(__DIR__ . '/cache.php');
include_once(__DIR__ . '/functions.php');
function eshop_header_show()
{
    global $CurrentHandler, $SYSTEM_FLAGS, $template, $lang;
    if (empty($_REQUEST['page'])) {
        $page = $CurrentHandler['params']['page'];
    } else {
        $page = $_REQUEST['page'];
    }
    $pageNo = isset($page) ? str_replace('%count%', (int)$page, '/ Страница %count%') : '';
    switch ($CurrentHandler['handlerName']) {
        case '':
            $titles = str_replace(
                array('%name_site%', '%separator%', '%group%', '%others%', '%num%'),
                array(
                    $SYSTEM_FLAGS['info']['title']['header'],
                    $SYSTEM_FLAGS['info']['title']['separator'],
                    $SYSTEM_FLAGS['info']['title']['group'],
                    $SYSTEM_FLAGS['info']['title']['others'],
                    $pageNo,
                ),
                $lang['eshop']['titles']
            );
            break;
        case 'show':
            $titles = str_replace(
                array('%name_site%', '%group%', '%others%'),
                array(
                    $SYSTEM_FLAGS['info']['title']['header'],
                    $SYSTEM_FLAGS['info']['title']['group'],
                    $SYSTEM_FLAGS['info']['title']['others'],
                ),
                $lang['eshop']['titles_common']
            );
            break;
        case 'search':
        case 'stocks':
        case 'compare':
            $SYSTEM_FLAGS['info']['breadcrumbs'][0]['text'] = $SYSTEM_FLAGS['info']['title']['others'];
            $titles = str_replace(
                array('%name_site%', '%group%', '%others%'),
                array(
                    $SYSTEM_FLAGS['info']['title']['header'],
                    $SYSTEM_FLAGS['info']['title']['group'],
                    $SYSTEM_FLAGS['info']['title']['others'],
                ),
                $lang['eshop']['titles_common']
            );
            break;
        case 'ebasket_list':
        case 'order':
            $SYSTEM_FLAGS['info']['breadcrumbs'][0]['text'] = $SYSTEM_FLAGS['info']['title']['group'];
            $titles = str_replace(
                array('%name_site%', '%group%', '%others%'),
                array(
                    $SYSTEM_FLAGS['info']['title']['header'],
                    $SYSTEM_FLAGS['info']['title']['group'],
                    $SYSTEM_FLAGS['info']['title']['others'],
                ),
                $lang['eshop']['titles_common']
            );
            break;
    }
    $template['vars']['titles'] = trim($titles);
}
function eshop($params)
{
    global $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $lang, $CurrentHandler, $tt;
    $sort = array();
    $cat = preg_match('#^[A-Za-z0-9\.\_\-]+$#s', $params['alt']) ? $params['alt'] : $_REQUEST['alt'];
    //$cat = preg_match('#^[A-Za-z0-9\.\_\-]+$#s', $params['alt']) ? input_filter_com(convert($params['alt'])) : '';
    $cat = str_replace("/", "", $cat);
    $conditions = array();
    array_push($conditions, "p.active = 1");
    if (isset($cat) && !empty($cat)) {
        $fCategory = filter_var($cat, FILTER_SANITIZE_STRING);
        $cats = $SYSTEM_FLAGS["eshop"]["catz"]["tree"];
        recursiveCategory($cats, $fCategory);
        $catz_filter = $tt;
        if (empty($catz_filter)) {
            return redirect_eshop(home);
        }
        $catz_filter_comma_separated = implode(",", $catz_filter);
        array_push($conditions, "pc.category_id IN (" . $catz_filter_comma_separated . ") ");
        $sort['alt'] = $fCategory;
    }
    $order = filter_var($_REQUEST['order'], FILTER_SANITIZE_STRING);
    if (isset($order) && !empty($order)) {
        switch ($order) {
            case 'date_desc':
                //$fOrder = " ORDER BY p.editdate DESC";
                $fOrder = " ORDER BY p.id DESC";
                break;
            case 'name_asc':
                $fOrder = " ORDER BY p.name ASC";
                break;
            case 'price_asc':
                $fOrder = " ORDER BY v.price ASC";
                break;
            case 'price_desc':
                $fOrder = " ORDER BY v.price DESC";
                break;
            case 'stock_desc':
                $fOrder = " ORDER BY v.stock DESC";
                break;
            case 'likes_desc':
                $fOrder = " ORDER BY p.likes DESC";
                break;
            default:
                $fOrder = " ORDER BY p.id DESC";
                break;
        }
        $x_sort['order'] = $order;
    } else {
        $fOrder = " ORDER BY p.id DESC";
    }
    $current_filter = array();
    $filter_conditions = array();
    $filter_req = filter_var((array)$_REQUEST['filter'], FILTER_SANITIZE_STRING);
    $filters_req = filter_var_array((array)$_REQUEST['filters'], FILTER_SANITIZE_STRING);
    if ($filter_req == 1) {
        foreach ($filters_req as $f_k => $f_v) {
            if ($f_v != "") {
                array_push($filter_conditions, " (o.feature_id = " . db_squote($f_k) . " AND o.value = " . db_squote($f_v) . ") ");
                $current_filter['filter'][$f_k] = $f_v;
                $x_sort['filter_' . $f_k] = $f_v;
            }
        }
    } else {
        $filters_req = filter_var_array($_REQUEST, FILTER_SANITIZE_STRING);
        foreach ($filters_req as $f_k => $f_v) {
            if (strpos($f_k, 'filter_') !== false) {
                $filter_id = str_replace('filter_', '', $f_k);
                array_push($filter_conditions, " (o.feature_id = " . db_squote($filter_id) . " AND o.value = " . db_squote($f_v) . ") ");
                $current_filter['filter'][$filter_id] = $f_v;
                $x_sort['filter_' . $filter_id] = $f_v;
            }
        }
    }
    /*
    TODO:
    SELECT
    *
    FROM
    (SELECT product_id FROM ng_eshop_options WHERE (feature_id = '2' AND value = '1')) AS t1
    INNER JOIN
    (SELECT product_id FROM ng_eshop_options WHERE (feature_id = '3' AND value = '2')) AS t2
    ON t1.product_id = t2.product_id
    */
    if (!empty($filter_conditions)) {
        $ids_cond_list = array();
        foreach ($filter_conditions as $cond) {
            $fsqlQ = "SELECT o.product_id AS product_id FROM " . prefix . "_eshop_options o WHERE " . $cond . " GROUP BY o.product_id";
            foreach ($mysql->select($fsqlQ) as $frow) {
                $one_filter_ids[] = $frow['product_id'];
            }
            $ids_cond_list[] = $one_filter_ids;
        }
        if (count($ids_cond_list) == 1) {
            $filter_ids = $ids_cond_list[0];
        } elseif (count($ids_cond_list) > 1) {
            $filter_ids = call_user_func_array('array_intersect', $ids_cond_list);
        } else {
            $filter_ids = null;
        }
        /*
        $fsqlQ = "SELECT o.product_id AS product_id FROM ".prefix."_eshop_options o ".(count($filter_conditions)?"WHERE ".implode(" AND ", $filter_conditions):'')." GROUP BY o.product_id";
        $filter_ids = array();
        foreach ($mysql->select($fsqlQ) as $frow)
        {
            $filter_ids[] = $frow['product_id'];
        }
        */
        if (!empty($filter_ids)) {
            array_push($conditions, "p.id IN (" . implode(",", $filter_ids) . ") ");
        } else {
            array_push($conditions, "p.id IS NULL ");
        }
    }
    $url = pluginGetVariable('eshop', 'url');
    switch ($CurrentHandler['handlerParams']['value']['pluginName']) {
        case 'core':
            if (isset($url) && !empty($url) && empty($params['page']) && empty($_REQUEST['page']) && empty($sort)) {
                return redirect_eshop(generateLink('eshop', ''));
            } else {
                if (isset($url) && !empty($url) or (!empty($params['page']) or !empty($_REQUEST['page']) or !empty($sort))) {
                    //return redirect_eshop(generatePageLink(array('pluginName' => 'eshop', 'pluginHandler' => '', 'params' => array('cat' => $sort['cat']), 'xparams' => array(), 'paginator' => array('page', 0, false)), intval($_REQUEST['page'])));
                }
            }
            break;
    }
    if (isset($_SESSION['eshop']['info']) && !empty($_SESSION['eshop']['info'])) {
        $info = $_SESSION['eshop']['info'];
        unset($_SESSION['eshop']['info']);
    } else {
        $info = '';
    }
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_plugin'];
    $tpath = locatePluginTemplates(array('eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['eshop'] . 'eshop.tpl');
    $cat_array = array();
    $cat_ids = $catz_filter[0];
    $i = 0;
    $location_tmp = array();
    $location = array();
    if ($cat_ids != "") {
        do {
            $result_cat = array();
            getCatFromTreeByID($SYSTEM_FLAGS["eshop"]["catz"]["tree"], $cat_ids, $result_cat);
            $bcat_row = $result_cat;
            if ($i == 0) {
                $cat_array = $bcat_row;
                $catlink = checkLinkAvailable('eshop', '') ?
                    generateLink('eshop', '', array('alt' => $cat_array['url'])) :
                    generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $cat_array['url']));
                $cat_array['link'] = $catlink;
                $cat_array['cnt'] = $SYSTEM_FLAGS["eshop"]["catz"]["cnt"]["count"][$cat_ids];
            }
            $cat_ids = $bcat_row['parent_id'];
            $catlink = checkLinkAvailable('eshop', '') ?
                generateLink('eshop', '', array('alt' => $bcat_row['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $bcat_row['url']));
            $location_tmp[] = array(
                'text' => $bcat_row['name'],
                'link' => $catlink,
            );
            $i += 1;
        } while ($cat_ids != 0);
    }
    $location = array_merge($location, array_reverse($location_tmp));
    foreach ($location as $loc_k => $loc) {
        $SYSTEM_FLAGS['info']['breadcrumbs'][$loc_k]['text'] = $loc['text'];
        $SYSTEM_FLAGS['info']['breadcrumbs'][$loc_k]['link'] = $loc['link'];
    }
    $SYSTEM_FLAGS['meta']['description'] = ($cat_array['meta_description']) ? $cat_array['meta_description'] : '';
    $SYSTEM_FLAGS['meta']['keywords'] = ($cat_array['meta_keywords']) ? $cat_array['meta_keywords'] : '';
    $SYSTEM_FLAGS['info']['title']['others'] = $cat_array['meta_title'];
    $SYSTEM_FLAGS['info']['title']['separator'] = $lang['eshop']['separator'];
    $cmp_array = array();
    foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
        $cmp_array[] = $cmp_row['linked_fld'];
    }
    $limitCount = (int)pluginGetVariable('eshop', 'count');
    // Защита от деления на ноль - устанавливаем значение по умолчанию
    if ($limitCount <= 0) {
        $limitCount = 10; // Значение по умолчанию
    }
    $pageNo = (int)$params['page'] ? (int)$params['page'] : (int)$_REQUEST['page'];
    if ($pageNo < 1) {
        $pageNo = 1;
    }
    if (!$limitStart) {
        $limitStart = ($pageNo - 1) * $limitCount;
    }
    //$fSort = " GROUP BY p.id ".$fOrder;
    $fSort = " " . $fOrder;
    $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id LEFT JOIN " . prefix . "_eshop_variants v ON p.id = v.product_id " . (count(
        $conditions
    ) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
    $sqlQ = "SELECT p.id AS id, p.url AS url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url AS curl, c.name AS category, v.price AS price " . $sqlQPart;
    $sqlQCount = "SELECT COUNT(*) AS CNT FROM (" . $sqlQ . ") AS T ";
    $count = $mysql->result($sqlQCount);
    $errors = array();
    if ($count == 0) {
        $errors[] = array("type" => "error", "text" => "В данной категории пока что нету продукции");
        goto prepere_vars;
    }
    $countPages = ceil($count / $limitCount);
    if ($countPages < $pageNo) {
        $errors[] = array("type" => "error", "text" => "Подстраницы не существует");
        goto prepere_vars;
    }
    if ($countPages > 1 && $countPages >= $pageNo) {
        $paginationParams = checkLinkAvailable('eshop', '') ?
            array(
                'pluginName' => 'eshop',
                'pluginHandler' => '',
                'params' => $sort,
                'xparams' => $x_sort,
                'paginator' => array('page', 0, false),
            ) :
            array(
                'pluginName' => 'core',
                'pluginHandler' => 'plugin',
                'params' => array('plugin' => 'eshop'),
                'xparams' => $x_sort,
                'paginator' => array('page', 1, false),
            );
        $navigations = LoadVariables();
        $pages = generatePagination($pageNo, 1, $countPages, 10, $paginationParams, $navigations);
    }
    foreach ($mysql->select($sqlQ . ' LIMIT ' . $limitStart . ', ' . $limitCount) as $row) {
        $fulllink = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
        $catlink = checkLinkAvailable('eshop', '') ?
            generateLink('eshop', '', array('alt' => $row['calt'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));
        $cmp_flag = in_array($row['id'], $cmp_array);
        $entries[$row['id']] = array(
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'annotation' => $row['annotation'],
            'body' => $row['body'],
            'active' => $row['active'],
            'featured' => $row['featured'],
            'price' => $row['price'],
            'compare_price' => $row['compare_price'],
            'stock' => $row['stock'],
            'meta_title' => $row['meta_title'],
            'meta_keywords' => $row['meta_keywords'],
            'meta_description' => $row['meta_description'],
            'fulllink' => $fulllink,
            'date' => (empty($row['date'])) ? '' : $row['date'],
            'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],
            'views' => $row['views'],
            'cat_name' => $row['category'],
            'cid' => $row['cid'],
            'catlink' => $catlink,
            'compare' => $cmp_flag,
            'home' => home,
            'image_filepath' => $row['image_filepath'],
            'tpl_url' => home . '/templates/' . $config['theme'],
        );
    }
    $entries_array_ids = array_keys($entries);
    if (isset($entries_array_ids) && !empty($entries_array_ids)) {
        $entries_string_ids = implode(',', $entries_array_ids);
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id IN (' . $entries_string_ids . ') ORDER BY i.position, i.id') as $irow) {
            $entries[$irow['product_id']]['images'][] = $irow;
        }
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id IN (' . $entries_string_ids . ') ORDER BY v.position, v.id') as $vrow) {
            $entries[$vrow['product_id']]['variants'][] = $vrow;
        }
    }
    prepere_vars:
    $fltr['order'] = $order;
    $tVars = array(
        'filter' => $fltr,
        'current_filter' => $current_filter['filter'],
        'errors' => $errors,
        'cat_info' => $cat_array,
        'info' => isset($info) ? $info : '',
        'entries' => isset($entries) ? $entries : '',
        'pages' => array(
            'true' => (isset($pages) && $pages) ? 1 : 0,
            'print' => isset($pages) ? $pages : '',
        ),
        'prevlink' => array(
            'true' => !empty($limitStart) ? 1 : 0,
            'link' => str_replace(
                '%page%',
                "$1",
                str_replace(
                    '%link%',
                    checkLinkAvailable('eshop', '') ?
                        generatePageLink(
                            array(
                                'pluginName' => 'eshop',
                                'pluginHandler' => '',
                                'params' => $sort,
                                'xparams' => $x_sort,
                                'paginator' => array('page', 0, false),
                            ),
                            $prev = floor($limitStart / $limitCount)
                        ) :
                        generatePageLink(
                            array(
                                'pluginName' => 'core',
                                'pluginHandler' => 'plugin',
                                'params' => array('plugin' => 'eshop'),
                                'xparams' => $x_sort,
                                'paginator' => array('page', 1, false),
                            ),
                            $prev = floor($limitStart / $limitCount)
                        ),
                    isset($navigations['prevlink']) ? $navigations['prevlink'] : ''
                )
            ),
        ),
        'nextlink' => array(
            'true' => ($prev + 2 <= $countPages) ? 1 : 0,
            'link' => str_replace(
                '%page%',
                "$1",
                str_replace(
                    '%link%',
                    checkLinkAvailable('eshop', '') ?
                        generatePageLink(
                            array(
                                'pluginName' => 'eshop',
                                'pluginHandler' => '',
                                'params' => $sort,
                                'xparams' => $x_sort,
                                'paginator' => array('page', 0, false),
                            ),
                            $prev + 2
                        ) :
                        generatePageLink(
                            array(
                                'pluginName' => 'core',
                                'pluginHandler' => 'plugin',
                                'params' => array('plugin' => 'eshop'),
                                'xparams' => $x_sort,
                                'paginator' => array('page', 1, false),
                            ),
                            $prev + 2
                        ),
                    isset($navigations['nextlink']) ? $navigations['nextlink'] : ''
                )
            ),
        ),
        'tpl_url' => home . '/templates/' . $config['theme'],
        'tpl_home' => admin_url,
    );
    $template['vars']['mainblock'] .= $xt->render($tVars);
}
function search_eshop($params)
{
    global $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $CurrentHandler, $lang;
    $url = pluginGetVariable('eshop', 'url');
    switch ($CurrentHandler['handlerParams']['value']['pluginName']) {
        case 'core':
            if (isset($url) && !empty($url)) {
                return redirect_eshop(generateLink('eshop', 'search'));
            }
            break;
        case 'eshop':
            if (empty($url)) {
                return redirect_eshop(generateLink('core', 'plugin', array('plugin' => 'eshop')));
            }
            break;
    }
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_plugin'];
    $SYSTEM_FLAGS['info']['title']['others'] = $lang['eshop']['name_search'];
    $SYSTEM_FLAGS['template.main.name'] = pluginGetVariable('eshop', 'main_template') ? pluginGetVariable('eshop', 'main_template') : 'main';
    $SYSTEM_FLAGS['meta']['description'] = (pluginGetVariable('eshop', 'description')) ? pluginGetVariable('eshop', 'description') : $SYSTEM_FLAGS['meta']['description'];
    $SYSTEM_FLAGS['meta']['keywords'] = (pluginGetVariable('eshop', 'keywords')) ? pluginGetVariable('eshop', 'keywords') : $SYSTEM_FLAGS['meta']['keywords'];
    $tpath = locatePluginTemplates(array('search_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['search_eshop'] . 'search_eshop.tpl');
    if (isset($_REQUEST['keywords']) && $_REQUEST['keywords']) {
        $keywords = filter_var($_REQUEST['keywords'], FILTER_SANITIZE_STRING);
        $search = substr($keywords, 0, 64);
        if (strlen($search) < 3) {
            $output = msg(array("type" => "error", "text" => "Слишком короткое слово"), 1, 2);
        }
        $keywords = array();
        $get_url = $search;
        $search = str_replace(" +", " ", $search);
        $stemmer = new Lingua_Stem_Ru();
        $tmp = explode(" ", $search);
        foreach ($tmp as $wrd) {
            $keywords[] = $stemmer->stem_word($wrd);
        }
        $string = implode("* ", $keywords);
        $string = $string . '*';
        $text = implode('|', $keywords);
        $conditions = array();
        if (isset($text) && !empty($text)) {
            array_push($conditions, "MATCH (p.name, p.annotation, p.body) AGAINST ('{$string}' IN BOOLEAN MODE) ");
            array_push($conditions, "p.active = 1 ");
        }
        $limitCount = pluginGetVariable('eshop', 'count_search');
        $pageNo = (int)$params['page'] ? (int)$params['page'] : (int)$_REQUEST['page'];
        if ($pageNo < 1) {
            $pageNo = 1;
        }
        if (!$limitStart) {
            $limitStart = ($pageNo - 1) * $limitCount;
        }
        //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
        $fSort = " ORDER BY p.id DESC";
        $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id " . (count(
            $conditions
        ) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
        $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url as curl, c.name AS category " . $sqlQPart;
        $sqlQCount = "SELECT COUNT(*) AS CNT FROM (" . $sqlQ . ") AS T ";
        $count = $mysql->result($sqlQCount);
        $countPages = ceil($count / $limitCount);
        if ($countPages > 1 && $countPages >= $pageNo) {
            $paginationParams = checkLinkAvailable('eshop', '') ?
                array(
                    'pluginName' => 'eshop',
                    'pluginHandler' => 'search',
                    'params' => array('keywords' => $get_url ? $get_url : ''),
                    'xparams' => array('keywords' => $get_url ? $get_url : ''),
                    'paginator' => array('page', 0, false),
                ) :
                array(
                    'pluginName' => 'core',
                    'pluginHandler' => 'plugin',
                    'params' => array('plugin' => 'eshop'),
                    'xparams' => array('keywords' => $get_url ? $get_url : ''),
                    'paginator' => array('page', 1, false),
                );
            $navigations = LoadVariables();
            $pages = generatePagination($pageNo, 1, $countPages, 10, $paginationParams, $navigations);
        }
        $cmp_array = array();
        foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
            $cmp_array[] = $cmp_row['linked_fld'];
        }
        foreach ($mysql->select($sqlQ . ' LIMIT ' . $limitStart . ', ' . $limitCount) as $row) {
            $fulllink = checkLinkAvailable('eshop', 'show') ?
                generateLink('eshop', 'show', array('alt' => $row['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
            $catlink = checkLinkAvailable('eshop', '') ?
                generateLink('eshop', '', array('alt' => $row['curl'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));
            $cmp_flag = in_array($row['id'], $cmp_array);
            $entries[$row['id']] = array(
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'annotation' => $row['annotation'],
                'body' => $row['body'],
                'active' => $row['active'],
                'featured' => $row['featured'],
                'meta_title' => $row['meta_title'],
                'meta_keywords' => $row['meta_keywords'],
                'meta_description' => $row['meta_description'],
                'fulllink' => $fulllink,
                'date' => (empty($row['date'])) ? '' : $row['date'],
                'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],
                'views' => $row['views'],
                'cat_name' => $row['category'],
                'cid' => $row['cid'],
                'catlink' => $catlink,
                'compare' => $cmp_flag,
                'home' => home,
                'tpl_url' => home . '/templates/' . $config['theme'],
            );
        }
        $entries_array_ids = array_keys($entries);
        if (isset($entries_array_ids) && !empty($entries_array_ids)) {
            $entries_string_ids = implode(',', $entries_array_ids);
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id IN (' . $entries_string_ids . ') ORDER BY i.position, i.id') as $irow) {
                $entries[$irow['product_id']]['images'][] = $irow;
            }
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id IN (' . $entries_string_ids . ') ORDER BY v.position, v.id') as $vrow) {
                $entries[$vrow['product_id']]['variants'][] = $vrow;
            }
        }
    }
    $tVars = array(
        'search_request' => $get_url,
        'info' => isset($info) ? $info : '',
        'entries' => isset($entries) ? $entries : '',
        'pages' => array(
            'true' => (isset($pages) && $pages) ? 1 : 0,
            'print' => isset($pages) ? $pages : '',
        ),
        'prevlink' => array(
            'true' => !empty($limitStart) ? 1 : 0,
            'link' => str_replace(
                '%page%',
                "$1",
                str_replace(
                    '%link%',
                    checkLinkAvailable('eshop', '') ?
                        generatePageLink(
                            array(
                                'pluginName' => 'eshop',
                                'pluginHandler' => 'search',
                                'params' => array('keywords' => $get_url ? $get_url : ''),
                                'xparams' => array('keywords' => $get_url ? $get_url : ''),
                                'paginator' => array('page', 0, false),
                            ),
                            $prev = floor($limitStart / $limitCount)
                        ) :
                        generatePageLink(
                            array(
                                'pluginName' => 'core',
                                'pluginHandler' => 'plugin',
                                'params' => array('plugin' => 'eshop'),
                                'xparams' => array('keywords' => $get_url ? $get_url : ''),
                                'paginator' => array('page', 1, false),
                            ),
                            $prev = floor($limitStart / $limitCount)
                        ),
                    isset($navigations['prevlink']) ? $navigations['prevlink'] : ''
                )
            ),
        ),
        'nextlink' => array(
            'true' => ($prev + 2 <= $countPages) ? 1 : 0,
            'link' => str_replace(
                '%page%',
                "$1",
                str_replace(
                    '%link%',
                    checkLinkAvailable('eshop', '') ?
                        generatePageLink(
                            array(
                                'pluginName' => 'eshop',
                                'pluginHandler' => 'search',
                                'params' => array('keywords' => $get_url ? $get_url : ''),
                                'xparams' => array('keywords' => $get_url ? $get_url : ''),
                                'paginator' => array('page', 0, false),
                            ),
                            $prev + 2
                        ) :
                        generatePageLink(
                            array(
                                'pluginName' => 'core',
                                'pluginHandler' => 'plugin',
                                'params' => array('plugin' => 'eshop'),
                                'xparams' => array('keywords' => $get_url ? $get_url : ''),
                                'paginator' => array('page', 1, false),
                            ),
                            $prev + 2
                        ),
                    isset($navigations['nextlink']) ? $navigations['nextlink'] : ''
                )
            ),
        ),
        'tpl_url' => home . '/templates/' . $config['theme'],
        'tpl_home' => admin_url,
    );
    $template['vars']['mainblock'] .= $xt->render($tVars);
}
function stocks_eshop($params)
{
    global $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $CurrentHandler, $lang;
    $url = pluginGetVariable('eshop', 'url');
    switch ($CurrentHandler['handlerParams']['value']['pluginName']) {
        case 'core':
            if (isset($url) && !empty($url)) {
                return redirect_eshop(generateLink('eshop', 'stocks'));
            }
            break;
        case 'eshop':
            if (empty($url)) {
                return redirect_eshop(generateLink('core', 'plugin', array('plugin' => 'eshop')));
            }
            break;
    }
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_plugin'];
    $SYSTEM_FLAGS['info']['title']['others'] = $lang['eshop']['name_stocks'];
    $SYSTEM_FLAGS['template.main.name'] = pluginGetVariable('eshop', 'main_template') ? pluginGetVariable('eshop', 'main_template') : 'main';
    $SYSTEM_FLAGS['meta']['description'] = (pluginGetVariable('eshop', 'description')) ? pluginGetVariable('eshop', 'description') : $SYSTEM_FLAGS['meta']['description'];
    $SYSTEM_FLAGS['meta']['keywords'] = (pluginGetVariable('eshop', 'keywords')) ? pluginGetVariable('eshop', 'keywords') : $SYSTEM_FLAGS['meta']['keywords'];
    $tpath = locatePluginTemplates(array('stocks_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['stocks_eshop'] . 'stocks_eshop.tpl');
    if (isset($_REQUEST['mode']) && $_REQUEST['mode']) {
        $mode = filter_var($_REQUEST['mode'], FILTER_SANITIZE_STRING);
    } else {
        $mode = "stocks";
    }
    $conditions = array();
    array_push($conditions, "p.active = 1 ");
    switch ($mode) {
        case "stocks":
            array_push($conditions, "p.stocked = 1");
            $orderby = " ORDER BY p.editdate DESC ";
            break;
        default:
            array_push($conditions, "p.stocked = 1");
            $orderby = " ORDER BY p.editdate DESC ";
            break;
    }
    $limitCount = pluginGetVariable('eshop', 'count_stocks');
    $pageNo = (int)$params['page'] ? (int)$params['page'] : (int)$_REQUEST['page'];
    if ($pageNo < 1) {
        $pageNo = 1;
    }
    if (!$limitStart) {
        $limitStart = ($pageNo - 1) * $limitCount;
    }
    //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
    $fSort = " ORDER BY p.id DESC";
    $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id  " . (count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
    $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url as curl, c.name AS category " . $sqlQPart;
    $sqlQCount = "SELECT COUNT(*) AS CNT FROM (" . $sqlQ . ") AS T ";
    $count = $mysql->result($sqlQCount);
    $countPages = ceil($count / $limitCount);
    if ($countPages < $pageNo) {
        return msg(array("type" => "error", "text" => "Подстраницы не существует"));
    }
    if ($countPages > 1 && $countPages >= $pageNo) {
        $paginationParams = checkLinkAvailable('eshop', '') ?
            array(
                'pluginName' => 'eshop',
                'pluginHandler' => 'stocks',
                'params' => array('keywords' => $get_url),
                'xparams' => array(),
                'paginator' => array('page', 0, false),
            ) :
            array(
                'pluginName' => 'core',
                'pluginHandler' => 'plugin',
                'params' => array('plugin' => 'eshop'),
                'xparams' => array('keywords' => $get_url),
                'paginator' => array('page', 1, false),
            );
        $navigations = LoadVariables();
        $pages = generatePagination($pageNo, 1, $countPages, 10, $paginationParams, $navigations);
    }
    $cmp_array = array();
    foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
        $cmp_array[] = $cmp_row['linked_fld'];
    }
    foreach ($mysql->select($sqlQ . ' LIMIT ' . $limitStart . ', ' . $limitCount) as $row) {
        $fulllink = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
        $catlink = checkLinkAvailable('eshop', '') ?
            generateLink('eshop', '', array('alt' => $row['curl'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));
        $cmp_flag = in_array($row['id'], $cmp_array);
        $entries[$row['id']] = array(
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'annotation' => $row['annotation'],
            'body' => $row['body'],
            'active' => $row['active'],
            'featured' => $row['featured'],
            'price' => $row['price'],
            'compare_price' => $row['compare_price'],
            'stock' => $row['stock'],
            'meta_title' => $row['meta_title'],
            'meta_keywords' => $row['meta_keywords'],
            'meta_description' => $row['meta_description'],
            'fulllink' => $fulllink,
            'date' => (empty($row['date'])) ? '' : $row['date'],
            'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],
            'views' => $row['views'],
            'cat_name' => $row['category'],
            'cid' => $row['cid'],
            'catlink' => $catlink,
            'compare' => $cmp_flag,
            'home' => home,
            'image_filepath' => $row['image_filepath'],
            'tpl_url' => home . '/templates/' . $config['theme'],
        );
    }
    $entries_array_ids = array_keys($entries);
    if (isset($entries_array_ids) && !empty($entries_array_ids)) {
        $entries_string_ids = implode(',', $entries_array_ids);
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id IN (' . $entries_string_ids . ') ORDER BY i.position, i.id') as $irow) {
            $entries[$irow['product_id']]['images'][] = $irow;
        }
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id IN (' . $entries_string_ids . ') ORDER BY v.position, v.id') as $vrow) {
            $entries[$vrow['product_id']]['variants'][] = $vrow;
        }
    }
    $tVars = array(
        'info' => isset($info) ? $info : '',
        'entries' => isset($entries) ? $entries : '',
        'pages' => array(
            'true' => (isset($pages) && $pages) ? 1 : 0,
            'print' => isset($pages) ? $pages : '',
        ),
        'prevlink' => array(
            'true' => !empty($limitStart) ? 1 : 0,
            'link' => str_replace(
                '%page%',
                "$1",
                str_replace(
                    '%link%',
                    checkLinkAvailable('eshop', '') ?
                        generatePageLink(
                            array(
                                'pluginName' => 'eshop',
                                'pluginHandler' => 'stocks',
                                'params' => array(),
                                'xparams' => array(),
                                'paginator' => array('page', 0, false),
                            ),
                            $prev = floor($limitStart / $limitCount)
                        ) :
                        generatePageLink(
                            array(
                                'pluginName' => 'core',
                                'pluginHandler' => 'plugin',
                                'params' => array('plugin' => 'eshop'),
                                'xparams' => array(),
                                'paginator' => array('page', 1, false),
                            ),
                            $prev = floor($limitStart / $limitCount)
                        ),
                    isset($navigations['prevlink']) ? $navigations['prevlink'] : ''
                )
            ),
        ),
        'nextlink' => array(
            'true' => ($prev + 2 <= $countPages) ? 1 : 0,
            'link' => str_replace(
                '%page%',
                "$1",
                str_replace(
                    '%link%',
                    checkLinkAvailable('eshop', '') ?
                        generatePageLink(
                            array(
                                'pluginName' => 'eshop',
                                'pluginHandler' => 'stocks',
                                'params' => array(),
                                'xparams' => array(),
                                'paginator' => array('page', 0, false),
                            ),
                            $prev + 2
                        ) :
                        generatePageLink(
                            array(
                                'pluginName' => 'core',
                                'pluginHandler' => 'plugin',
                                'params' => array('plugin' => 'eshop'),
                                'xparams' => array(),
                                'paginator' => array('page', 1, false),
                            ),
                            $prev + 2
                        ),
                    isset($navigations['nextlink']) ? $navigations['nextlink'] : ''
                )
            ),
        ),
        'tpl_url' => home . '/templates/' . $config['theme'],
        'tpl_home' => admin_url,
    );
    $template['vars']['mainblock'] .= $xt->render($tVars);
}
function compare_eshop($params)
{
    global $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $CurrentHandler, $lang;
    $url = pluginGetVariable('eshop', 'url');
    switch ($CurrentHandler['handlerParams']['value']['pluginName']) {
        case 'core':
            if (isset($url) && !empty($url)) {
                return redirect_eshop(generateLink('eshop', 'stocks'));
            }
            break;
        case 'eshop':
            if (empty($url)) {
                return redirect_eshop(generateLink('core', 'plugin', array('plugin' => 'eshop')));
            }
            break;
    }
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_plugin'];
    $SYSTEM_FLAGS['info']['title']['others'] = $lang['eshop']['name_compare'];
    $SYSTEM_FLAGS['template.main.name'] = pluginGetVariable('eshop', 'main_template') ? pluginGetVariable('eshop', 'main_template') : 'main';
    $SYSTEM_FLAGS['meta']['description'] = (pluginGetVariable('eshop', 'description')) ? pluginGetVariable('eshop', 'description') : $SYSTEM_FLAGS['meta']['description'];
    $SYSTEM_FLAGS['meta']['keywords'] = (pluginGetVariable('eshop', 'keywords')) ? pluginGetVariable('eshop', 'keywords') : $SYSTEM_FLAGS['meta']['keywords'];
    $tpath = locatePluginTemplates(array('compare_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['compare_eshop'] . 'compare_eshop.tpl');
    $cmp_array = array();
    foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
        $cmp_array[] = $cmp_row['linked_fld'];
    }
    $conditions = array();
    if (isset($cmp_array) && !empty($cmp_array)) {
        $compare = implode(",", $cmp_array);
        array_push($conditions, "p.id IN (" . $compare . ") ");
        array_push($conditions, "p.active = 1 ");
        $features_list = array();
        foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_features ORDER BY position, id") as $frow) {
            $features_list[] = $frow;
        }
        //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
        $fSort = " ORDER BY p.id DESC";
        $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id " . (count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
        $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url as curl, c.name AS category " . $sqlQPart;
        foreach ($mysql->select($sqlQ) as $row) {
            $fulllink = checkLinkAvailable('eshop', 'show') ?
                generateLink('eshop', 'show', array('alt' => $row['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
            $catlink = checkLinkAvailable('eshop', '') ?
                generateLink('eshop', '', array('alt' => $row['curl'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));
            $cmp_flag = in_array($row['id'], $cmp_array);
            $qid = $row['id'];
            $options_array = array();
            foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_options LEFT JOIN " . prefix . "_eshop_features ON " . prefix . "_eshop_features.id=" . prefix . "_eshop_options.feature_id WHERE " . prefix . "_eshop_options.product_id = '$qid' ORDER BY position, id") as $orow) {
                if ($orow['ftype'] == 2) {
                    $foptions = json_decode($orow['foptions'], true);
                    $orow['value'] = $foptions[$orow['value']];
                }
                $options_array[$orow['id']] = $orow['value'];
            }
            $features_array = array();
            foreach ($features_list as $frow) {
                $frow['value'] = $options_array[$frow['id']];
                $features_array[] = $frow;
            }
            $entries[$row['id']] = array(
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'annotation' => $row['annotation'],
                'body' => $row['body'],
                'active' => $row['active'],
                'featured' => $row['featured'],
                'meta_title' => $row['meta_title'],
                'meta_keywords' => $row['meta_keywords'],
                'meta_description' => $row['meta_description'],
                'fulllink' => $fulllink,
                'date' => (empty($row['date'])) ? '' : $row['date'],
                'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],
                'views' => $row['views'],
                'cat_name' => $row['category'],
                'cid' => $row['cid'],
                'catlink' => $catlink,
                'compare' => $cmp_flag,
                'features' => $features_array,
                'home' => home,
                'tpl_url' => home . '/templates/' . $config['theme'],
            );
        }
        $entries_array_ids = array_keys($entries);
        if (isset($entries_array_ids) && !empty($entries_array_ids)) {
            $entries_string_ids = implode(',', $entries_array_ids);
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id IN (' . $entries_string_ids . ') ORDER BY i.position, i.id') as $irow) {
                $entries[$irow['product_id']]['images'][] = $irow;
            }
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id IN (' . $entries_string_ids . ') ORDER BY v.position, v.id') as $vrow) {
                $entries[$vrow['product_id']]['variants'][] = $vrow;
            }
        }
    }
    $tVars = array(
        'info' => isset($info) ? $info : '',
        'entries' => isset($entries) ? $entries : '',
        'features_list' => isset($features_list) ? $features_list : '',
        'tpl_url' => home . '/templates/' . $config['theme'],
        'tpl_home' => admin_url,
    );
    $template['vars']['mainblock'] .= $xt->render($tVars);
}
function currency_eshop($params)
{
    global $ngCookieDomain;
    $ngCurrencyID = (int)(filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING));
    if (($ngCurrencyID) != 0) {
        @setcookie('ngCurrencyID', $ngCurrencyID, time() + 86400 * 365, '/', $ngCookieDomain, 0, 1);
    } else {
        $ngCurrencyID = $_COOKIE['ngCurrencyID'];
    }
    redirect_eshop($_SERVER['HTTP_REFERER']);
}
function show_eshop($params)
{
    global $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $CurrentHandler, $lang;
    $id = isset($params['id']) ? abs((int)($params['id'])) : abs((int)($_REQUEST['id']));
    $alt = preg_match('#^[A-Za-z0-9\.\_\-]+$#s', $params['alt']) ? $params['alt'] : $_REQUEST['alt'];
    //$alt = preg_match('#^[A-Za-z0-9\.\_\-]+$#s', $params['alt']) ? input_filter_com(convert($params['alt'])) : '';
    $url = pluginGetVariable('eshop', 'url');
    switch ($CurrentHandler['handlerParams']['value']['pluginName']) {
        case 'core':
            if (isset($url) && !empty($url)) {
                return redirect_eshop(generateLink('eshop', 'show', array('alt' => $alt)));
            }
            break;
        case 'eshop':
            if (empty($url)) {
                return redirect_eshop(generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $alt)));
            }
            break;
    }
    $conditions = array();
    if (isset($alt) && !empty($alt)) {
        array_push($conditions, "p.url = " . db_squote($alt) . " ");
    } else {
        redirect_eshop(link_eshop());
    }
    array_push($conditions, "p.active = 1 ");
    $tpath = locatePluginTemplates(array('show_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['show_eshop'] . 'show_eshop.tpl');
    //$fSort = " GROUP BY p.id ORDER BY p.id DESC LIMIT 1";
    $fSort = " ORDER BY p.id DESC LIMIT 1";
    $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id LEFT JOIN " . prefix . "_eshop_variants v ON p.id = v.product_id " . (count(
        $conditions
    ) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
    $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url as curl, c.name AS category " . $sqlQPart;
    $row = $mysql->record($sqlQ);
    if (isset($row) && !empty($row)) {
        $qid = $row['id'];
        $fulllink = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
        $cat_ids = $row['cid'];
        $i = 0;
        $location_tmp = array();
        $location = array();
        $location_tmp[] = array(
            'text' => $row['name'],
            'link' => $fulllink,
        );
        if ($cat_ids != "") {
            do {
                $result_cat = array();
                getCatFromTreeByID($SYSTEM_FLAGS["eshop"]["catz"]["tree"], $cat_ids, $result_cat);
                $bcat_row = $result_cat;
                $cat_ids = $bcat_row['parent_id'];
                $catlink = checkLinkAvailable('eshop', '') ?
                    generateLink('eshop', '', array('alt' => $bcat_row['url'])) :
                    generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $bcat_row['url']));
                $location_tmp[] = array(
                    'text' => $bcat_row['name'],
                    'link' => $catlink,
                );
                $i += 1;
            } while ($cat_ids != 0);
        }
        $location = array_merge($location, array_reverse($location_tmp));
        foreach ($location as $loc_k => $loc) {
            $SYSTEM_FLAGS['info']['breadcrumbs'][$loc_k]['text'] = $loc['text'];
            $SYSTEM_FLAGS['info']['breadcrumbs'][$loc_k]['link'] = $loc['link'];
        }
        $entriesImg = array();
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images WHERE product_id = ' . $row['id'] . ' ORDER BY position, id ') as $row2) {
            $entriesImg[] = $row2;
        }
        $entriesVariants = array();
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants WHERE product_id = ' . $row['id'] . ' ORDER BY position, id ') as $vrow) {
            $entriesVariants[] = $vrow;
        }
        $features_array = array();
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_options LEFT JOIN ' . prefix . '_eshop_features ON ' . prefix . '_eshop_features.id=' . prefix . '_eshop_options.feature_id WHERE ' . prefix . '_eshop_options.product_id = ' . $row['id'] . ' ORDER BY position, id') as $orow) {
            if ($orow['ftype'] == 2) {
                $foptions = json_decode($orow['foptions'], true);
                $orow['value'] =  $foptions[$orow['value']];
            }
            $features_array[] = $orow;
        }
        $related_array = [];
        foreach ($mysql->select('SELECT p.id AS id, p.url AS url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views FROM ' . prefix . '_eshop_related_products rp LEFT JOIN ' . prefix . '_eshop_products p ON p.id=rp.related_id WHERE rp.product_id = ' . $row['id'] . ' AND p.active = 1 ORDER BY rp.position') as $rrow) {
            $fulllink = checkLinkAvailable('eshop', 'show') ?
                generateLink('eshop', 'show', array('alt' => $rrow['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $rrow['url']));
            $rrow['fulllink'] = $fulllink;
            $related_array[$rrow['id']] = $rrow;
        }
        $related_array_ids = array_keys($related_array);
        if (isset($related_array_ids) && !empty($related_array_ids)) {
            $related_string_ids = implode(',', $related_array_ids);
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id IN (' . $related_string_ids . ') ORDER BY i.position, i.id') as $irow) {
                $related_array[$irow['product_id']]['images'][] = $irow;
            }
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id IN (' . $related_string_ids . ') ORDER BY v.position, v.id') as $vrow) {
                $related_array[$vrow['product_id']]['variants'][] = $vrow;
            }
        }
        $SYSTEM_FLAGS['info']['title']['others'] = $row['meta_title'];
        $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_plugin'];
        $SYSTEM_FLAGS['meta']['description'] = ($row['meta_description']) ? $row['meta_description'] : '';
        $SYSTEM_FLAGS['meta']['keywords'] = ($row['meta_keywords']) ? $row['meta_keywords'] : '';
        $cmode = (int)(pluginGetVariable('eshop', 'views_count'));
        if ($cmode > 1) {
            // Delayed update of counters
            $mysql->query("INSERT INTO " . prefix . "_eshop_products_view (id, cnt) VALUES (" . db_squote($row['id']) . ", 1) ON DUPLICATE KEY UPDATE cnt = cnt + 1");
        } else {
            if ($cmode > 0) {
                $mysql->query("update " . prefix . "_eshop_products set views=views+1 where id = " . db_squote($row['id']));
            }
        }
        $cmp_array = array();
        foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
            $cmp_array[] = $cmp_row['linked_fld'];
        }
        $cmp_flag = in_array($qid, $cmp_array);
        $likes_tpath = locatePluginTemplates(array('likes_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
        $likes_xt = $twig->loadTemplate($likes_tpath['likes_eshop'] . 'likes_eshop.tpl');
        $likes = $mysql->record("SELECT COUNT(*) AS count FROM " . prefix . "_eshop_products_likes l WHERE l.product_id='" . $qid . "'");
        $likes_tVars = array(
            'count' => $likes['count'],
            'id' => $qid,
        );
        $comments_tpath = locatePluginTemplates(array('comments.form_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
        $comments_xt = $twig->loadTemplate($comments_tpath['comments.form_eshop'] . 'comments.form_eshop.tpl');
        $comments_tVars = array(
            'id' => $qid,
        );
        $fulllink = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
        $catlink = checkLinkAvailable('eshop', '') ?
            generateLink('eshop', '', array('alt' => $row['curl'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));
        // Загружаем плагин bookmarks и получаем кнопки закладок
        loadPlugin('bookmarks');
        $plugin_bookmarks_product = '';
        if (function_exists('bookmarks_eshop_show')) {
            bookmarks_eshop_show($row['id'], $row);
            $plugin_bookmarks_product = $template['vars']['plugin_bookmarks_product'] ?? '';
        }
        $tVars = array(
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'annotation' => $row['annotation'],
            'body' => $row['body'],
            'active' => $row['active'],
            'featured' => $row['featured'],
            'meta_title' => $row['meta_title'],
            'meta_keywords' => $row['meta_keywords'],
            'meta_description' => $row['meta_description'],
            'full_link' => $fulllink,
            'edit_link' => admin_url . "/admin.php?mod=extra-config&plugin=eshop&action=edit_product&id=" . $row['id'] . "",
            'date' => (empty($row['date'])) ? '' : $row['date'],
            'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],
            'views' => $row['views'] + 1,
            'cat_name' => $row['category'],
            'cid' => $row['cid'],
            'catlink' => $catlink,
            'home' => home,
            'tpl_url' => home . '/templates/' . $config['theme'],
            'entriesImg' => isset($entriesImg) ? $entriesImg : '',
            'entriesVariants' => isset($entriesVariants) ? $entriesVariants : '',
            'entriesFeatures' => isset($features_array) ? $features_array : '',
            'entriesRelated' => isset($related_array) ? $related_array : '',
            'compare' => $cmp_flag,
            'likes_form' => $likes_xt->render($likes_tVars),
            'comments_form' => $comments_xt->render($comments_tVars),
            'plugin_bookmarks_product' => $plugin_bookmarks_product,
        );
        $template['vars']['mainblock'] .= $xt->render($tVars);
    } else {
        error404();
    }
}
function yml_export_eshop($params)
{
    global $twig, $mysql, $SYSTEM_FLAGS, $config, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;
    $SUPRESS_TEMPLATE_SHOW = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;
    @header('Content-type: text/xml; charset=utf-8');
    $SYSTEM_FLAGS['http.headers'] = array(
        'content-type' => 'application/xml; charset=charset=utf-8',
        'cache-control' => 'private',
    );
    $cat_array = array();
    foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_categories ORDER BY position ASC') as $cat_row) {
        $catlink = checkLinkAvailable('eshop', '') ?
            generateLink('eshop', '', array('cat' => $cat_row['id'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop'), array('cat' => $cat_row['id']));
        $cat_array[] = array(
            'id' => $cat_row['id'],
            'url' => $cat_row['url'],
            'image' => $cat_row['image'],
            'name' => $cat_row['name'],
            'description' => $cat_row['description'],
            'parent_id' => $cat_row['parent_id'],
            'position' => $cat_row['position'],
            'meta_title' => $cat_row['meta_title'],
            'meta_keywords' => $cat_row['meta_keywords'],
            'meta_description' => $cat_row['meta_description'],
            'link' => $catlink,
        );
    }
    $conditions = array();
    array_push($conditions, "p.active = 1");
    $tpath = locatePluginTemplates(array('yml_export_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['yml_export_eshop'] . 'yml_export_eshop.tpl');
    //$limitCount = "10000";
    //$fSort = " GROUP BY p.id ORDER BY p.id DESC ";
    $fSort = " ORDER BY p.id DESC ";
    $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id " . (count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
    $sqlQ = "SELECT p.id AS id, p.url AS url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.name AS category " . $sqlQPart;
    $entries = array();
    foreach ($mysql->select($sqlQ) as $row) {
        $entriesImg = array();
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id = ' . $row['id'] . ' ') as $row2) {
            $entriesImg[] = $row2;
        }
        $entriesVariants = array();
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id = ' . $row['id'] . ' ') as $vrow) {
            $entriesVariants[] = $vrow;
        }
        $features_array = array();
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_options LEFT JOIN ' . prefix . '_eshop_features ON ' . prefix . '_eshop_features.id=' . prefix . '_eshop_options.feature_id WHERE ' . prefix . '_eshop_options.product_id = ' . $row['id'] . ' ORDER BY position, id') as $orow) {
            if ($orow['ftype'] == 2) {
                $foptions = json_decode($orow['foptions'], true);
                $orow['value'] = $foptions[$orow['value']];
            }
            $features_array[] = $orow;
        }
        $fulllink = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
        $catlink = checkLinkAvailable('eshop', '') ?
            generateLink('eshop', '', array('alt' => $row['curl'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));
        $entries[] = array(
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'annotation' => $row['annotation'],
            'body' => $row['body'],
            'active' => $row['active'],
            'featured' => $row['featured'],
            'meta_title' => $row['meta_title'],
            'meta_keywords' => $row['meta_keywords'],
            'meta_description' => $row['meta_description'],
            'fulllink' => $fulllink,
            'date' => (empty($row['date'])) ? '' : $row['date'],
            'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],
            'views' => $row['views'],
            'cat_name' => $row['category'],
            'cid' => $row['cid'],
            'catlink' => $catlink,
            'images' => $entriesImg,
            'variants' => $entriesVariants,
            'features' => $features_array,
            'home' => home,
            'tpl_url' => home . '/templates/' . $config['theme'],
        );
    }
    $entries_variants = array();
    foreach ($entries as $entry) {
        $entry_id = $entry['id'];
        $entry_images = isset($entry['images']) ? $entry['images'] : array(); // Сохраняем изображения
        foreach ($entry['variants'] as $variant) {
            $entry['id'] = $entry_id . '-' . $variant['id'];
            $entry['product_id'] = $entry_id; // Сохраняем оригинальный ID для путей к изображениям
            $entry['images'] = $entry_images; // Явно копируем изображения
            $entry['variants'] = $variant;
            $entries_variants[] = $entry;
        }
    }
    $tVars = array(
        'cat_info' => $cat_array,
        'entries' => isset($entries_variants) ? $entries_variants : '',
        'tpl_url' => home . '/templates/' . $config['theme'],
        'tpl_home' => admin_url,
    );
    print($xt->render($tVars));
}
// Показать содержимое корзины
function plugin_ebasket_list()
{
    global $mysql, $twig, $userROW, $template, $ip, $SYSTEM_FLAGS, $lang, $config;
    $filter = array();
    if (is_array($userROW)) {
        $filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
    }
    if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
        $filter[] = '(cookie = ' . db_squote($_COOKIE['ngTrackID']) . ')';
    }
    $recs = array();
    $total = 0;
    if (count($filter)) {
        foreach ($SYSTEM_FLAGS["eshop"]["basket"]["entries"] as $rec) {
            $total += round($rec['price'] * $rec['count'], 2);
            $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
            $rec['xfields'] = unserialize($rec['linked_fld']);
            unset($rec['linked_fld']);
            $recs[] = $rec;
        }
    }
    $error_text = [];
    $notify_text = [];
    if (!empty($_POST)) {
        $SQL['name'] = filter_var($_REQUEST['userInfo']['fullName'], FILTER_SANITIZE_STRING);
        if (empty($SQL['name'])) {
            $error_text[] = 'Имя не задано';
        }
        $SQL['email'] = filter_var($_REQUEST['userInfo']['email'], FILTER_SANITIZE_STRING);
        if (empty($SQL['email'])) {
            $error_text[] = 'Email не задан';
        }
        $SQL['phone'] = filter_var($_REQUEST['userInfo']['phone'], FILTER_SANITIZE_STRING);
        if (empty($SQL['phone'])) {
            $error_text[] = 'Телефон не задан';
        }
        $SQL['address'] = filter_var($_REQUEST['userInfo']['deliverTo'], FILTER_SANITIZE_STRING);
        $SQL['comment'] = filter_var($_REQUEST['userInfo']['commentText'], FILTER_SANITIZE_STRING);
        $SQL['dt'] = time() + ($config['date_adjust'] * 60);
        $SQL['ip'] = $ip;
        $SQL['type'] = "1";
        $SQL['paid'] = 0;
        $SQL['total_price'] = $total;
        $SQL['payment_type_id'] = filter_var($_REQUEST['payment_type'], FILTER_SANITIZE_STRING);
        $SQL['delivery_type_id'] = filter_var($_REQUEST['delivery_type'], FILTER_SANITIZE_STRING);
        if (isset($userROW)) {
            $SQL['author_id'] = $userROW['id'];
        }
        $SQL['uniqid'] = substr(str_shuffle(MD5(microtime())), 0, 10);
        foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_ebasket WHERE " . join(" or ", $filter), 1) as $rec) {
            $r_count = $rec['count'];
            $linked_id = $rec['linked_id'];
            $linked_fld = unserialize($rec['linked_fld']);
            $variant_id = $linked_fld['item']['v_id'];
            $conditions = array();
            if ($linked_id) {
                array_push($conditions, "p.id = " . db_squote($linked_id));
            }
            if ($variant_id != 0) {
                array_push($conditions, "v.id = " . db_squote($variant_id));
            }
            // $fSort = " GROUP BY p.id ORDER BY p.id DESC";
            $fSort = " ORDER BY p.id DESC";
            $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id LEFT JOIN (SELECT * FROM " . prefix . "_eshop_images ORDER BY position, id) i ON i.product_id = p.id LEFT JOIN " . prefix . "_eshop_variants v ON p.id = v.product_id " . (count(
                $conditions
            ) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
            $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.active AS active, p.featured AS featured, p.position AS position, c.url as curl, c.name AS category, i.filepath AS image_filepath, v.id AS v_id, v.sku AS v_sku, v.name AS v_name, v.amount AS v_amount, v.price AS price, v.compare_price AS compare_price, v.stock AS stock " . $sqlQPart;
            // Retrieve news record
            $item_rec = $mysql->record($sqlQ);
            if ($item_rec['v_amount'] != null) {
                if ($r_count > $item_rec['v_amount']) {
                    $error_text[] = 'Невозможно купить продукт: ' . $item_rec["name"] . '. Максимальное количество доступное для заказа: ' . $item_rec['v_amount'];
                }
            }
        }
        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_orders SET ' . implode(', ', $vnames) . ' ');
            $qid = lastid('eshop_orders');
            if ($qid != null) {
                foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_ebasket WHERE " . join(" or ", $filter), 1) as $rec) {
                    $r_linked_id = $rec['linked_id'];
                    $r_title = $rec['title'];
                    $r_count = $rec['count'];
                    $r_price = $rec['price'];
                    $r_linked_fld = $rec['linked_fld'];
                    $mysql->query("INSERT INTO " . prefix . "_eshop_order_basket (`order_id`, `linked_id`, `title`, `count`, `price`, `linked_fld`) VALUES ('$qid','$r_linked_id','$r_title','$r_count','$r_price','$r_linked_fld')");
                }
                if (count($filter)) {
                    $mysql->query("DELETE FROM " . prefix . "_eshop_ebasket WHERE " . join(" or ", $filter));
                    foreach ($recs as $rec) {
                        $v_id = $rec['xfields']['item']['v_id'];
                        $variant = $mysql->record("SELECT amount FROM " . prefix . "_eshop_variants WHERE id = '" . (int)$v_id . "'");
                        $current_amount = $variant['amount'];
                        $r_count = $rec['count'];
                        if ($current_amount != null) {
                            if ($current_amount - $r_count > 0) {
                                $mysql->query("update " . prefix . "_eshop_variants set amount = amount - " . (int)$r_count . " where id = " . (int)$v_id);
                            } else {
                                $mysql->query("update " . prefix . "_eshop_variants set amount = 0 where id = " . (int)$v_id);
                            }
                        }
                    }
                }
                // Определяем условия выборки
                $filter = array();
                if ($qid) {
                    $filter[] = '(order_id = ' . db_squote($qid) . ')';
                }
                $total = 0;
                foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_order_basket WHERE " . join(" or ", $filter), 1) as $rec) {
                    $total += round($rec['price'] * $rec['count'], 2);
                    $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
                    $rec['xfields'] = unserialize($rec['linked_fld']);
                    unset($rec['linked_fld']);
                    $basket[] = $rec;
                }
                $notify_tpath = locatePluginTemplates(array('mail/lfeedback'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
                $notify_xt = $twig->loadTemplate($notify_tpath['mail/lfeedback'] . 'mail/' . 'lfeedback.tpl');
                $pVars = array(
                    'recs' => count($recs),
                    'entries' => $recs,
                    'total' => sprintf('%9.2f', $total),
                    'vnames' => $SQL,
                );
                $mailBody = $notify_xt->render($pVars);
                $mailSubject = "Новый заказ с сайта";
                $mailTo = pluginGetVariable('eshop', 'email_notify_orders');
                $mail_from = pluginGetVariable('eshop', 'email_notify_back');
                if ($mail_from == "") {
                    $mail_from = false;
                }
                if ($mailTo != "") {
                    sendEmailMessage(
                        $mailTo,
                        $mailSubject,
                        $mailBody,
                        $filename = false,
                        $mail_from,
                        $ctype = 'text/html'
                    );
                }
                $notify_text[] = 'Заказ добавлен.';
                $order_link = checkLinkAvailable('eshop', 'order') ?
                    generateLink('eshop', 'order', array(), array('id' => $qid, 'uniqid' => $SQL['uniqid'])) :
                    generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'order'), array(), array('id' => $qid, 'uniqid' => $SQL['uniqid']));
                return redirect_eshop($order_link);
            }
        }
    }
    /*
    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= "<p>".$error."</p>";
        }
    }
    $notify_input = '';
    if (!empty($notify_text)) {
        foreach ($notify_text as $notify) {
            $notify_input .= msg(array("type" => "info", "text" => $notify));
        }
    }
    */
    foreach ($SQL as $k => $v) {
        $tFormEntry[$k] = $v;
    }
    $tFormEntry['error'] = $error_text;
    $tFormEntry['notify'] = $notify_text;
    $tFormEntry['id'] = $qid;
    $basket_link = checkLinkAvailable('eshop', 'ebasket_list') ?
        generateLink('eshop', 'ebasket_list', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'ebasket_list'), array());
    $tVars = array(
        'formEntry' => $tFormEntry,
        'recs' => count($recs),
        'entries' => $recs,
        'total' => sprintf('%9.2f', $total),
        'basket_link' => $basket_link,
        'entriesDeliveryTypes' => getDeliveryTypes(),
        'entriesPaymentTypes' => getPaymentTypes(),
    );
    $tpath = locatePluginTemplates(array('ebasket/list'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['ebasket/list'] . 'ebasket/' . 'list.tpl');
    $template['vars']['mainblock'] = $xt->render($tVars);
    $SYSTEM_FLAGS['info']['title']['others'] = "";
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_basket'];
    $SYSTEM_FLAGS['meta']['description'] = "";
    $SYSTEM_FLAGS['meta']['keywords'] = "";
}
function order_eshop()
{
    global $mysql, $twig, $template, $SYSTEM_FLAGS, $lang;
    $filter = array();
    $SQL['id'] = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING);
    if (empty($SQL['id'])) {
        $error_text[] = 'ID не задано';
    } else {
        $filter[] = '(id = ' . db_squote($SQL['id']) . ')';
    }
    $SQL['uniqid'] = filter_var($_REQUEST['uniqid'], FILTER_SANITIZE_STRING);
    if (empty($SQL['uniqid'])) {
        $error_text[] = 'Uniqid не задан';
    } else {
        $filter[] = '(uniqid = ' . db_squote($SQL['uniqid']) . ')';
    }
    if (empty($error_text)) {
        $sqlQ = "SELECT * FROM " . prefix . "_eshop_orders " . (count($filter) ? "WHERE " . implode(" AND ", $filter) : '') . " LIMIT 1";
        $row = $mysql->record($sqlQ);
        if (!empty($row)) {
            $qid = $row['id'];
            $filter = array();
            if ($qid) {
                $filter[] = '(order_id = ' . db_squote($qid) . ')';
            }
            $total = 0;
            foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_order_basket WHERE " . join(" or ", $filter), 1) as $rec) {
                $total += round($rec['price'] * $rec['count'], 2);
                $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
                $rec['xfields'] = unserialize($rec['linked_fld']);
                unset($rec['linked_fld']);
                $basket[] = $rec;
            }
            $row['delivery_type'] = getEntityRow(prefix . "_eshop_delivery_type", $SQL['id']);
            $row['payment_type'] = getEntityRow(prefix . "_eshop_payment_type", $SQL['id']);
        } else {
            $error_text[] = 'Неврные парметры заказа';
        }
    }
    /*
    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= "<p>".$error."</p>";
        }
    }
    */
    foreach ($row as $k => $v) {
        $tFormEntry[$k] = $v;
    }
    $tFormEntry['error'] = $error_text;
    $tFormEntry['id'] = $qid;
    $payment = array();
    if ($handle = opendir(__DIR__ . '/payment')) {
        $blacklist = array('.', '..');
        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, $blacklist)) {
                $row['name'] = $file;
                $systems[] = $row;
            }
        }
        closedir($handle);
    }
    $payment_link = checkLinkAvailable('eshop', 'payment') ?
        generateLink('eshop', 'payment', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'payment'), array());
    $payment['systems'] = $systems;
    $payment['link'] = $payment_link;
    $basket_link = checkLinkAvailable('eshop', 'ebasket_list') ?
        generateLink('eshop', 'ebasket_list', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'ebasket_list'), array());
    $tVars = array(
        'formEntry' => $tFormEntry,
        'recs' => isset($basket) ? count($basket) : '',
        'entries' => $basket,
        'payment' => $payment,
        'total' => sprintf('%9.2f', $total),
        'basket_link' => $basket_link,
    );
    $tpath = locatePluginTemplates(array('order_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['order_eshop'] . 'order_eshop.tpl');
    $template['vars']['mainblock'] = $xt->render($tVars);
    $SYSTEM_FLAGS['info']['title']['others'] = "";
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_order'];
    $SYSTEM_FLAGS['meta']['description'] = "";
    $SYSTEM_FLAGS['meta']['keywords'] = "";
}
function payment_eshop()
{
    global $mysql, $twig, $template, $SYSTEM_FLAGS, $lang;
    $filter = array();
    $SQL = array();
    $payment_name = "";
    $payment_options = array();
    $SQL['payment_id'] = filter_var($_REQUEST['payment_id'], FILTER_SANITIZE_STRING);
    if (empty($SQL['payment_id'])) {
        $error_text[] = 'ID платежной системы не задано';
    } else {
        $filter[] = '(name = ' . db_squote($SQL['payment_id']) . ')';
        $sqlQ = "SELECT * FROM " . prefix . "_eshop_payment " . (count($filter) ? "WHERE " . implode(" AND ", $filter) : '') . " LIMIT 1";
        $row = $mysql->record($sqlQ);
        if (empty($row)) {
            $error_text[] = 'Платежной системы с таким ID не существует или не заданы настройки';
        } else {
            $payment_name = $row['name'];
            $payment_options = json_decode($row['options'], true);
        }
    }
    if (empty($error_text)) {
        $payment_filename = __DIR__ . '/payment/' . $payment_name . '/payment.php';
        if (file_exists($payment_filename)) {
            include_once($payment_filename);
            payment_action($payment_name, $payment_options, $_REQUEST);
        } else {
            $error_text[] = "Файл $payment_filename не существует";
        }
    }
    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= "<p>" . $error . "</p>";
        }
    }
    $tVars = array(
        'errors' => $error_text,
        'errors_text' => $error_input,
    );
    $tpath = locatePluginTemplates(array('payment_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    $xt = $twig->loadTemplate($tpath['payment_eshop'] . 'payment_eshop.tpl');
    $template['vars']['mainblock'] = $xt->render($tVars);
    $SYSTEM_FLAGS['info']['title']['others'] = "";
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['eshop']['name_payment'];
    $SYSTEM_FLAGS['meta']['description'] = "";
    $SYSTEM_FLAGS['meta']['keywords'] = "";
}
function api_eshop($params)
{
    include_once(__DIR__ . '/api/api.php');
    $version = (int)$params['v'];
    $api_filename = __DIR__ . '/api/version/v' . $version . '/api.php';
    if (file_exists($api_filename)) {
        include_once($api_filename);
        $api = new ApiEshopController($version);
        $api->run();
    }
}
function recursiveCategory($arr, $flg)
{
    global $tt;
    foreach ($arr as $k => $v) {
        if ($v['url'] == $flg) {
            $tt[] = $k;
            if (!empty($v['children'])) {
                $tt = array_merge($tt, array_keys($v['children']));
            }
        }
        if (!empty($v['children'])) {
            recursiveCategory($v['children'], $flg);
        }
    }
}
function input_filter_com($text)
{
    $text = trim($text);
    $search = array("<", ">");
    $replace = array("&lt;", "&gt;");
    $text = preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", str_replace($search, $replace, $text));
    return $text;
}
function link_eshop()
{
    $eshopURL = checkLinkAvailable('eshop', '') ?
        generateLink('eshop', '') :
        generateLink('core', 'plugin', array('plugin' => 'eshop'));
    return $eshopURL;
}
function LoadVariables()
{
    $tpath = locatePluginTemplates(array(':'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
    return parse_ini_file($tpath[':'] . '/variables.ini', true);
}
function redirect_eshop($url)
{
    if (headers_sent()) {
        echo "<script>document.location.href='{$url}';</script>\n";
        exit;
    } else {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: {$url}");
        exit;
    }
}
function plugin_eshop_cron($isSysCron, $handler)
{
    if ($handler == 'eshop_views') {
        eshopUpdateDelayedCounters();
    }
}
// Update delayed news counters
function eshopUpdateDelayedCounters()
{
    global $mysql;
    // Lock tables
    $mysql->query("lock tables " . prefix . "_eshop_products_view write, " . prefix . "_eshop write");
    // Read data and update counters
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_products_view") as $vrec) {
        $mysql->query("update " . prefix . "_eshop_products set views = views + " . (int)$vrec['cnt'] . " where id = " . (int)$vrec['id']);
    }
    // Truncate view table
    //$mysql->query("truncate table ".prefix."_eshop_view");
    // DUE TO BUG IN MYSQL - USE DELETE + OPTIMIZE
    $mysql->query("DELETE FROM " . prefix . "_eshop_products_view");
    $mysql->query("optimize table " . prefix . "_eshop_products_view");
    // Unlock tables
    $mysql->query("unlock tables");
    return true;
}
// Generate a random character string
function rand_str($length = 10, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    // Length of character list
    $chars_length = (strlen($chars) - 1);
    // Start our string
    $string = $chars[rand(0, $chars_length)];
    //$string = substr(str_shuffle($chars_length), 0, 5);
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string)) {
        // Grab a random character from our list
        $r = $chars[rand(0, $chars_length)];
        //$r = substr(str_shuffle($chars_length), 0, 5);
        // Make sure the same two characters don't appear next to each other
        if ($r != $string[$i - 1]) {
            $string .= $r;
        }
        /*         if ($r != $string) {
            $string .= $r;
        } */
    }
    // Return the string
    return $string;
}
function secure_search_eshop($text)
{
    $text = trim($text);
    $text = preg_replace("/[^\w\x7F-\xFF\s]/", "", $text);
    return secure_html($text);
}
class Lingua_Stem_Ru
{
    public $VERSION = "0.02";
    public $Stem_Caching = 0;
    public $Stem_Cache = array();
    public $VOWEL = '/аеиоуыэюя/';
    public $PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/';
    public $REFLEXIVE = '/(с[яь])$/';
    public $ADJECTIVE = '/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|ую|юю|ая|яя|ою|ею)$/';
    public $PARTICIPLE = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/';
    public $VERB = '/((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/';
    public $NOUN = '/(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я)$/';
    public $RVRE = '/^(.*?[аеиоуыэюя])(.*)$/';
    public $DERIVATIONAL = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/';
    public function s(&$s, $re, $to)
    {
        $orig = $s;
        $s = preg_replace($re, $to, $s);
        return $orig !== $s;
    }
    public function m($s, $re)
    {
        return preg_match($re, $s);
    }
    public function stem_word($word)
    {
        $word = strtolower($word);
        $word = strtr($word, 'ё', 'е');
        if ($this->Stem_Caching && isset($this->Stem_Cache[$word])) {
            return $this->Stem_Cache[$word];
        }
        $stem = $word;
        do {
            if (!preg_match($this->RVRE, $word, $p)) {
                break;
            }
            $start = $p[1];
            $RV = $p[2];
            if (!$RV) {
                break;
            }
            if (!$this->s($RV, $this->PERFECTIVEGROUND, '')) {
                $this->s($RV, $this->REFLEXIVE, '');
                if ($this->s($RV, $this->ADJECTIVE, '')) {
                    $this->s($RV, $this->PARTICIPLE, '');
                } else {
                    if (!$this->s($RV, $this->VERB, '')) {
                        $this->s($RV, $this->NOUN, '');
                    }
                }
            }
            $this->s($RV, '/и$/', '');
            if ($this->m($RV, $this->DERIVATIONAL)) {
                $this->s($RV, '/ость?$/', '');
            }
            if (!$this->s($RV, '/ь$/', '')) {
                $this->s($RV, '/ейше?/', '');
                $this->s($RV, '/нн$/', 'н');
            }
            $stem = $start . $RV;
        } while (false);
        if ($this->Stem_Caching) {
            $this->Stem_Cache[$word] = $stem;
        }
        return $stem;
    }
    public function stem_caching($parm_ref)
    {
        $caching_level = @$parm_ref['-level'];
        if ($caching_level) {
            if (!$this->m($caching_level, '/^[012]$/')) {
                die(__CLASS__ . "::stem_caching() - Legal values are '0','1' or '2'. '$caching_level' is not a legal value");
            }
            $this->Stem_Caching = $caching_level;
        }
        return $this->Stem_Caching;
    }
    public function clear_stem_cache()
    {
        $this->Stem_Cache = array();
    }
}
