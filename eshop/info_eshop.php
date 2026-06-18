<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

include_once(__DIR__.'/cache.php');

loadPluginLibrary('uprofile', 'lib');
LoadPluginLibrary('gsmg', 'common');

LoadPluginLang('eshop', 'main', '', '', '#');
add_act('core', 'eshop_infovars_show');
add_act('index', 'plugin_eshop_compare');
add_act('index', 'plugin_ebasket_total');
add_act('index', 'plugin_ebasket_notify');

register_htmlvar('js', admin_url.'/plugins/eshop/tpl/js/breeze.min.js');
register_htmlvar('js', admin_url.'/plugins/eshop/tpl/js/eshop.js');

function eshop_infovars_show()
{
    global $SYSTEM_FLAGS, $mysql, $twig, $userROW, $ngCookieDomain;

    $SYSTEM_FLAGS["eshop"]["description_delivery"] = pluginGetVariable('eshop', 'description_delivery');
    $SYSTEM_FLAGS["eshop"]["description_order"] = pluginGetVariable('eshop', 'description_order');
    $SYSTEM_FLAGS["eshop"]["description_phones"] = pluginGetVariable('eshop', 'description_phones');

    $eshop_dir = get_plugcfg_dir('eshop');
    generate_currency_cache();

    if (file_exists($eshop_dir.'/cache_currency.php')) {
        $currency_tEntry = unserialize(file_get_contents($eshop_dir.'/cache_currency.php'));
    } else {
        $currency_tEntry = array();
    }

    $SYSTEM_FLAGS["eshop"]["currency"] = $currency_tEntry;

    $current_currency = array();

    if (!isset($_COOKIE['ngCurrencyID'])) {
        $ngCurrencyID = $currency_tEntry[0]['id'];
        $current_currency = $currency_tEntry[0];
        @setcookie('ngCurrencyID', $ngCurrencyID, time() + 86400 * 365, '/', $ngCookieDomain, 0, 1);
    } else {
        $ngCurrencyID = $_COOKIE['ngCurrencyID'];
        foreach ($currency_tEntry as $cc) {
            if ($cc['id'] == $ngCurrencyID) {
                $current_currency = $cc;
            }
        }

        if (empty($current_currency)) {
            $current_currency = $currency_tEntry[0];
        }

    }

    $SYSTEM_FLAGS["eshop"]["current_currency"] = $current_currency;

    generate_catz_cache();

    if (file_exists($eshop_dir.'/cache_catz.php')) {
        $catz_tEntry = unserialize(file_get_contents($eshop_dir.'/cache_catz.php'));
    } else {
        $catz_tEntry = array();
    }

    $SYSTEM_FLAGS["eshop"]["catz"] = $catz_tEntry;

    generate_features_cache();

    if (file_exists($eshop_dir.'/cache_features.php')) {
        $features_tEntry = unserialize(file_get_contents($eshop_dir.'/cache_features.php'));
    } else {
        $features_tEntry = array();
    }

    $SYSTEM_FLAGS["eshop"]["features"] = $features_tEntry;

    generate_categories_features_cache();

    if (file_exists($eshop_dir.'/cache_categories_features.php')) {
        $categories_features_tEntry = unserialize(file_get_contents($eshop_dir.'/cache_categories_features.php'));
    } else {
        $categories_features_tEntry = array();
    }

    $SYSTEM_FLAGS["eshop"]["categories_features"] = $categories_features_tEntry;

    //$twig->addFunction('IsCatFeatures', new TwigFunction('IsCatFeatures'));
	$twig->addFunction(new \Twig\TwigFunction('IsCatFeatures', 'IsCatFeatures'));


    $filter = array();
    if (is_array($userROW)) {
        $filter [] = '(user_id = '.db_squote($userROW['id']).')';
    }

    if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
        $filter [] = '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
    }

    $compare_link = checkLinkAvailable('eshop', 'compare') ?
        generateLink('eshop', 'compare', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'compare'), array());

    $basket_link = checkLinkAvailable('eshop', 'ebasket_list') ?
        generateLink('eshop', 'ebasket_list', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'ebasket_list'), array());

    if (count($filter) > 0) {
        $tCount = 0;
        $tEntries = array();
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_compare WHERE ".join(" or ", $filter)."") as $row) {
            $tEntries[] = $row;
            $tCount += 1;
        }

        $compare_tVars = array(
            'count' => $tCount,
            'link' => $compare_link,
            'entries' => $tEntries,
        );

        $tCount = 0;
        $tPrice = 0;
        $tEntries = array();
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_ebasket WHERE ".join(" or ", $filter)."") as $row) {
            $tEntries[] = $row;
            $tCount += 1;
            $tPrice += $row['price'] * $row['count'];
        }

        $basket_tVars = array(
            'count' => $tCount,
            'price' => $tPrice,
            'entries' => $tEntries,
            'basket_link' => $basket_link,
        );
    } else {
        $compare_tVars = array(
            'count' => 0,
            'link' => $compare_link,
            'entries' => array(),
        );

        $basket_tVars = array(
            'count' => 0,
            'price' => 0,
            'entries' => array(),
            'basket_link' => $basket_link,
        );
    }

    $SYSTEM_FLAGS["eshop"]["compare"] = $compare_tVars;
    $SYSTEM_FLAGS["eshop"]["basket"] = $basket_tVars;

}

// Отображение общей информации о сравнеии продукции
function plugin_eshop_compare()
{
    global $twig, $SYSTEM_FLAGS;

    $tpath = locatePluginTemplates(array('compare_block_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
    $xt = $twig->loadTemplate($tpath['compare_block_eshop'].'compare_block_eshop.tpl');

    return $xt->render($SYSTEM_FLAGS["eshop"]["compare"]);
}

// Отображение общей информации/остатков в корзине
function plugin_ebasket_total()
{
    global $twig, $SYSTEM_FLAGS;

	$tpath = locatePluginTemplates(array('ebasket/total'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
	
    $xt = $twig->loadTemplate($tpath['ebasket/total'].'ebasket/'.'total.tpl');

    return $xt->render($SYSTEM_FLAGS["eshop"]["basket"]);
}

// Отображение блока нотификации при добавлении продукта в корзину
function plugin_ebasket_notify()
{
    global $twig;

	$tpath = locatePluginTemplates(array('ebasket/notify'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));

    $basket_link = checkLinkAvailable('eshop', 'ebasket_list') ?
        generateLink('eshop', 'ebasket_list', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'ebasket_list'), array());

    $tVars = array(
        'basket_link' => $basket_link,
    );

    $xt = $twig->loadTemplate($tpath['ebasket/notify'].'ebasket/'.'notify.tpl');

    return $xt->render($tVars);
}

twigRegisterFunction('eshop', 'compare', 'plugin_eshop_compare');
twigRegisterFunction('eshop', 'notify', 'plugin_ebasket_notify');
twigRegisterFunction('eshop', 'total', 'plugin_ebasket_total');

if (class_exists('p_uprofileFilter')) {
    class uOrderFilter extends p_uprofileFilter
    {
        public function editProfileForm($userID, $SQLrow, &$tvars)
        {
            global $mysql;

            $conditions = array();

            if ($userID) {
                array_push($conditions, "author_id = ".db_squote($userID));
            }

            $fSort = " ORDER BY id DESC";
            $sqlQPart = "FROM ".prefix."_eshop_orders ".(count($conditions) ? "WHERE ".implode(
                        " AND ",
                        $conditions
                    ) : '').$fSort;
            $sqlQ = "SELECT * ".$sqlQPart;

            foreach ($mysql->select($sqlQ) as $row) {

                $order_link = checkLinkAvailable('eshop', 'order') ?
                    generateLink('eshop', 'order', array(), array('id' => $row['id'], 'uniqid' => $row['uniqid'])) :
                    generateLink(
                        'core',
                        'plugin',
                        array('plugin' => 'eshop', 'handler' => 'order'),
                        array(),
                        array('id' => $row['id'], 'uniqid' => $row['uniqid'])
                    );

                $row['order_link'] = $order_link;
                $tEntry[] = $row;

            }

            $tvars['eshop']['orders'] = $tEntry;
        }
    }

    register_filter('plugin.uprofile', 'orders', new uOrderFilter);
}

if (class_exists('gsmgFilter')) {
    class gShopFilter extends gsmgFilter
    {
        public function onShow(&$output)
        {
            global $mysql;

            if (pluginGetVariable('eshop', 'integrate_gsmg') == "1") {

                $new_output = "";

                $lm = $mysql->record("SELECT date(from_unixtime(max(date))) AS pd FROM ".prefix."_eshop_products");
                foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_categories ORDER BY position, id") as $rows) {
                    $cat_link = checkLinkAvailable('eshop', '') ?
                        generateLink('eshop', '', array('alt' => $rows['url'])) :
                        generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $rows['url']));
                    $new_output .= "<url>";
                    $new_output .= "<loc>".home.$cat_link."</loc>";
                    $new_output .= "<priority>".(float)pluginGetVariable('gsmg', 'catp_pr')."</priority>";
                    $new_output .= "<lastmod>".$lm['pd']."</lastmod>";
                    $new_output .= "<changefreq>daily</changefreq>";
                    $new_output .= "</url>";
                }

                $query = "SELECT * FROM ".prefix."_eshop_products WHERE active = 1 ORDER BY id DESC";

                foreach ($mysql->select($query) as $rec) {
                    $link = checkLinkAvailable('eshop', 'show') ?
                        generateLink('eshop', 'show', array('alt' => $rec['url'])) :
                        generateLink(
                            'core',
                            'plugin',
                            array('plugin' => 'eshop', 'handler' => 'show'),
                            array('alt' => $rec['url'])
                        );;
                    $new_output .= "<url>";
                    $new_output .= "<loc>".home.$link."</loc>";
                    $new_output .= "<priority>".(float)pluginGetVariable('gsmg', 'news_pr')."</priority>";
                    $new_output .= "<lastmod>".strftime("%Y-%m-%d", max($rec['editdate'], $rec['date']))."</lastmod>";
                    $new_output .= "<changefreq>daily</changefreq>";
                    $new_output .= "</url>";
                }

                $output = $output.$new_output;
            }

        }
    }

    register_filter('gsmg', 'eshop', new gShopFilter);
}

function IsCatFeatures($category_id, $feature_id, $array)
{
    if (is_array($array)) {
        foreach ($array as $row) {
            if ($category_id == $row['category_id'] && $feature_id == $row['feature_id']) {
                return true;
            }
        }

        return false;
    } else {
        return false;
    }
}