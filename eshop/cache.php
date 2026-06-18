<?php

if (!defined('NGCMS')) {
    die ('HAL');
}

function generate_catz_cache($load = false)
{
    global $mysql, $cat_tt;
    $eshop_dir = get_plugcfg_dir('eshop');
    if (!file_exists($eshop_dir.'/cache_catz.php') or $load) {
        $levels = array();
        $tree = array();
        $cur = array();
        $cat_cnt_arr = array();
        foreach ($mysql->select("SELECT category_id, COUNT(*) as cnt FROM ".prefix."_eshop_products_categories pc LEFT JOIN ".prefix."_eshop_products p ON p.id = pc.product_id  WHERE p.active = 1 GROUP BY pc.category_id") as $crow) {
            $cat_cnt_arr[$crow['category_id']] = $crow;
        }
        $catz_arr = array();
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_categories ORDER BY position, id") as $rows) {
            $catz_arr[] = $rows;
        }
        foreach ($catz_arr as $rows) {
            $cur = &$levels[$rows['id']];
            $cur['id'] = $rows['id'];
            $cur['url'] = $rows['url'];
            $cur['image'] = $rows['image'];
            $cur['parent_id'] = $rows['parent_id'];
            $cur['position'] = $rows['position'];
            $cur['name'] = $rows['name'];
            $cur['description'] = $rows['description'];
            $cur['meta_title'] = $rows['meta_title'];
            $cur['meta_keywords'] = $rows['meta_keywords'];
            $cur['meta_description'] = $rows['meta_description'];
            $cur['active'] = $rows['active'];
            $cat_link = checkLinkAvailable('eshop', '') ?
                generateLink('eshop', '', array('alt' => $rows['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $rows['url']));
            $cur['cat_link'] = $cat_link;
            if ($rows['parent_id'] == 0) {
                $tree[$rows['id']] = &$cur;
            } else {
                $levels[$rows['parent_id']]['children'][$rows['id']] = &$cur;
            }
        }
        $cnt_arr = array();
        foreach ($catz_arr as $rows) {
            $cat_tt = array();
            reCategory($tree, $rows['url']);
            $sum_cnt = 0;
            foreach ($cat_tt as $ctt) {
                if (isset($cat_cnt_arr[$ctt])) {
                    $sum_cnt += $cat_cnt_arr[$ctt]['cnt'];
                } else {
                    $sum_cnt += 0;
                }
            }
            $cnt_arr['count'][$rows['id']] = $sum_cnt;
        }
        $tVars = array(
            'tree' => $tree,
            'cnt' => $cnt_arr,
        );
        file_put_contents($eshop_dir.'/cache_catz.php', serialize($tVars));
    }
}

function generate_currency_cache($load = false)
{
    global $mysql;
    $eshop_dir = get_plugcfg_dir('eshop');
    if (!file_exists($eshop_dir.'/cache_currency.php') or $load) {
        $currency_link = checkLinkAvailable('eshop', 'currency') ?
            generateLink('eshop', 'currency', array()) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'currency'), array());
        $currency_tEntry = array();
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_currencies WHERE enabled = 1 ORDER BY position, id") as $row) {
            $row['currency_link'] = $currency_link."?id=".$row['id'];
            $currency_tEntry[] = $row;
        }
        file_put_contents($eshop_dir.'/cache_currency.php', serialize($currency_tEntry));
    }
}

function generate_categories_features_cache($load = false)
{
    global $mysql;
    $eshop_dir = get_plugcfg_dir('eshop');
    if (!file_exists($eshop_dir.'/cache_categories_features.php') or $load) {
        $categories_features_tEntry = array();
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_categories_features") as $row) {
            $categories_features_tEntry[] = $row;
        }
        file_put_contents($eshop_dir.'/cache_categories_features.php', serialize($categories_features_tEntry));
    }
}

function generate_features_cache($load = false)
{
    global $mysql;
    $eshop_dir = get_plugcfg_dir('eshop');
    if (!file_exists($eshop_dir.'/cache_features.php') or $load) {
        $features_tEntry = array();
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_features ORDER BY position, id") as $row) {
            $foptions = array();
            $foptions = json_decode($row['foptions'], true);
/*             $foptions = array_map(
                function ($array) {

                    return $array;
                },
                $foptions
            ); */
            $row['foptions'] = $foptions;
            $features_tEntry[] = $row;
        }
        file_put_contents($eshop_dir.'/cache_features.php', serialize($features_tEntry));
    }
}

function reCategory($arr, $flg)
{

    global $cat_tt;
    foreach ($arr as $k => $v) {
        if ($v['url'] == $flg) {
            $cat_tt[] = $k;
            if (!empty($v['children'])) {
                $cat_tt = array_merge($cat_tt, array_keys($v['children']));
            }
        }
        if (!empty($v['children'])) {
            reCategory($v['children'], $flg);
        }
    }
}

function getCatFromTreeByID($arr, $id, &$result_cat)
{

    foreach ($arr as $v) {
        if ($v['id'] == $id) {
            $result_cat = $v;
        }
        if (!empty($v['children'])) {
            getCatFromTreeByID($v['children'], $id, $result_cat);
        }
    }
}

function getCatFromTreeByAlt($arr, $url, &$result_cat)
{

    foreach ($arr as $v) {
        if ($v['url'] == $url) {
            $result_cat = $v;
        }
        if (!empty($v['children'])) {
            getCatFromTreeByID($v['children'], $url, $result_cat);
        }
    }
}