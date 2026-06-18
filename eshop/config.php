<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

pluginsLoadConfig();
LoadPluginLang('eshop', 'config', '', '', '#');

include_once(__DIR__ . '/cache.php');
include_once(__DIR__ . '/functions.php');

switch ($_REQUEST['action']) {

    case 'list_product':
        list_product();
        break;
    case 'add_product':
        add_product();
        break;
    case 'edit_product':
        edit_product();
        break;
    case 'modify_product':
        modify_product();
        list_product();
        break;

    case 'list_feature':
        list_feature();
        break;
    case 'add_feature':
        add_feature();
        break;
    case 'edit_feature':
        edit_feature();
        break;
    case 'modify_feature':
        modify_feature();
        list_feature();
        break;

    case 'list_cat':
        list_cat();
        break;
    case 'add_cat':
        add_cat();
        break;
    case 'edit_cat':
        edit_cat();
        break;
    case 'del_cat':
        del_cat();
        list_cat();
        break;

    case 'list_order':
        list_order();
        break;
    case 'edit_order':
        edit_order();
        break;
    case 'modify_order':
        modify_order();
        list_order();
        break;

    case 'list_comment':
        list_comment();
        break;
    case 'modify_comment':
        modify_comment();
        list_comment();
        break;

    case 'list_currencies':
        list_currencies();
        break;
    case 'add_currency':
        add_currency();
        break;
    case 'edit_currency':
        edit_currency();
        break;
    case 'del_currency':
        del_currency();
        list_currencies();
        break;

    case 'list_payment_type':
        list_payment_type();
        break;
    case 'add_payment_type':
        add_payment_type();
        break;
    case 'edit_payment_type':
        edit_payment_type();
        break;
    case 'del_payment_type':
        del_payment_type();
        list_payment_type();
        break;

    case 'list_delivery_type':
        list_delivery_type();
        break;
    case 'add_delivery_type':
        add_delivery_type();
        break;
    case 'edit_delivery_type':
        edit_delivery_type();
        break;
    case 'del_delivery_type':
        del_delivery_type();
        list_delivery_type();
        break;

    case 'list_api':
        list_api();
        break;
    case 'add_api':
        add_api();
        break;
    case 'edit_api':
        edit_api();
        break;
    case 'del_api':
        del_api();
        list_api();
        break;

    case 'options':
        options();
        break;

    case 'urls':
        urls();
        break;
    case 'automation':
        automation();
        break;

    case 'list_payment':
        list_payment();
        break;
    case 'edit_payment':
        edit_payment();
        break;

    default:
        list_product();
}

function list_product()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_product'), 'eshop', 1);

    $tVars = array();

    $cats = getCats2();

    // Load admin page based cookies
    $admCookie = admcookie_get();

    $fName = $_REQUEST['fname'];
    $fStatus = $_REQUEST['fstatus'];
    $fCategory = $_REQUEST['fcategory'];
    $fId = $_REQUEST['fid'];
    $fCode = $_REQUEST['fcode'];

    $news_per_page = isset($_REQUEST['rpp']) ? (int)$_REQUEST['rpp'] : (int)$admCookie['eshop']['pp'];
    // - Set default value for `Records Per Page` parameter
    if (($news_per_page < 2) || ($news_per_page > 2000)) {
        $news_per_page = 10;
    }

    // - Save into cookies current value
    $admCookie['eshop']['pp'] = $news_per_page;
    admcookie_set($admCookie);

    $conditions = array();
    if ($fName) {
        array_push($conditions, "p.name LIKE " . db_squote("%" . $fName . "%"));
    }

    if ($fStatus == '0' || $fStatus == '1') {
        array_push($conditions, "p.active = " . db_squote($fStatus));
    }

    if ($fCategory) {
        $catz_filter = array();
        $catz_filter = getChildIdsArray($cats, $fCategory, 0);
        $catz_filter[] = $fCategory;
        $catz_filter_comma_separated = implode(",", $catz_filter);
        array_push($conditions, "pc.category_id IN (" . $catz_filter_comma_separated . ") ");
    }

    if ($fId) {
        array_push($conditions, "p.id = " . db_squote($fId));
    }

    if ($fCode) {
        array_push($conditions, "p.code = " . db_squote($fCode));
    }

    //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
    $fSort = " ORDER BY p.id DESC";
    $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id " . (count(
        $conditions
    ) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
    $sqlQ = "SELECT p.id AS id, p.url AS url, p.code AS code, p.name AS name, p.active AS active, p.featured AS featured, p.position AS position, c.name AS category " . $sqlQPart;

    $sqlQCount = "SELECT COUNT(*) AS CNT FROM (" . $sqlQ . ") AS T ";

    //$sqlQCount = "SELECT COUNT(p.id) FROM ng_eshop_products p ORDER BY p.id DESC";

    $pageNo = (int)$_REQUEST['page'] ? $_REQUEST['page'] : 0;
    if ($pageNo < 1) {
        $pageNo = 1;
    }
    if (!$start_from) {
        $start_from = ($pageNo - 1) * $news_per_page;
    }

    $count = $mysql->result($sqlQCount);
    $countPages = ceil($count / $news_per_page);

    //var_dump($sqlQ);
    //var_dump($sqlQ.' LIMIT '.$start_from.', '.$news_per_page);

    foreach ($mysql->select($sqlQ . ' LIMIT ' . $start_from . ', ' . $news_per_page) as $row) {

        $view_link = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));

        $tEntry[$row['id']] = array(
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],

            'category' => $row['category'],
            'image_filepath' => $row['image_filepath'],

            'price' => $row['price'],
            'compare_price' => $row['compare_price'],
            'stock' => $row['stock'],

            'active' => $row['active'],
            'featured' => $row['featured'],
            'stocked' => $row['stocked'],

            'position' => $row['position'],

            'date' => $row['date'],
            'editdate' => $row['editdate'],

            'edit_link' => "?mod=extra-config&plugin=eshop&action=edit_product&id=" . $row['id'] . "",
            'view_link' => checkLinkAvailable('eshop', 'show') ?
                generateLink('eshop', 'show', array('alt' => $row['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url'])),
        );
    }

    $entries_array_ids = array_keys((array)$tEntry);

    if (isset($entries_array_ids) && !empty($entries_array_ids)) {

        $entries_string_ids = implode(',', $entries_array_ids);

        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id IN (' . $entries_string_ids . ') ORDER BY i.position, i.id') as $irow) {
            $tEntry[$irow['product_id']]['images'][] = $irow;
        }

        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id IN (' . $entries_string_ids . ') ORDER BY v.position, v.id') as $vrow) {
            $tEntry[$vrow['product_id']]['variants'][] = $vrow;
        }
    }

    $xt = $twig->loadTemplate($tpath['config/list_product'] . 'config/' . 'list_product.tpl');

    $tVars = array(
        'php_self' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'filter_cats' => getTree($cats, $fCategory, 0),
        'pagesss' => generateAdminPagelist(
            array(
                'current' => $pageNo,
                'count' => $countPages,
                'url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop' . ($news_per_page ? '&rpp=' . $news_per_page : '') . ($fName ? '&fname=' . $fName : '') . ($fStatus ? '&fstatus=' . $fStatus : '') . ($fCategory ? '&fcategory=' . $fCategory : '') . '&page=%page%',
            )
        ),
        'rpp' => $news_per_page,
        'fname' => secure_html($fName),
        'fstatus' => secure_html($fStatus),
        'fcategory' => secure_html($fCategory),
        'fid' => secure_html($fId),
        'fcode' => secure_html($fCode),
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Продукция',
    );

    print $xg->render($tVars);
}

function add_product()
{
    global $tpl, $config, $mysql, $twig, $parse;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_product'), 'eshop', 1);

    if (isset($_REQUEST['handler'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название продукта не задано';
        }

        $SQL['url'] = input_filter_com(convert($_REQUEST['url']));
        if (empty($SQL['url'])) {
            $SQL['url'] = strtolower($parse->translit($SQL['name'], 1, 1));
        }

        /*         if ($SQL['url']) {
            if (is_array($mysql->record("SELECT id FROM ".prefix."_eshop_products WHERE url = ".db_squote($SQL['url'])." LIMIT 1"))) {
                $error_text[] = 'Такой altname уже существует.';
            }
        } */

        $SQL['code'] = secure_html(trim($_REQUEST['code']));

        $SQL['meta_title'] = secure_html(trim($_REQUEST['meta_title']));
        if (empty($SQL['meta_title'])) {
            $SQL['meta_title'] = $SQL['name'];
        }

        $SQL['meta_keywords'] = secure_html(trim($_REQUEST['meta_keywords']));
        $SQL['meta_description'] = secure_html(trim($_REQUEST['meta_description']));

        $SQL['external_id'] = $_REQUEST['external_id'] ? $_REQUEST['external_id'] : '0';

        $SQL['annotation'] = $_REQUEST['annotation'];
        $SQL['body'] = $_REQUEST['body'];

        $SQL['active'] = (int)$_REQUEST['active'];
        $SQL['featured'] = (int)$_REQUEST['featured'];
        $SQL['stocked'] = (int)$_REQUEST['stocked'];

        $SQL['date'] = time() + ($config['date_adjust'] * 60);
        $SQL['editdate'] = $SQL['date'];

        //Дополнительные поля
        if (!empty($_REQUEST['data']['features'])) {
            $features = $_REQUEST['data']['features'];
        } else {
            $features = null;
        }

        //Изображения
        $images = $_REQUEST['data']['images'];

        if ($_REQUEST['linked-products'] != "") {
            $linked_products = explode(",", $_REQUEST['linked-products']);
        } else {
            $linked_products = null;
        }

        if (empty($error_text)) {
            $vnames = [];
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_products SET ' . implode(', ', $vnames));

            $qid = lastid('eshop_products');

            if ($images != null) {

                makeUploadsDirs('/eshop/products/' . $qid . '/');

                foreach ($images as $inx_img => $img) {
                    $timestamp = time();
                    $iname = $timestamp . "-" . $img;

                    $productsTempPath = '/eshop/products/';
                    moveFromTemp($qid, $productsTempPath, $img, $iname);

                    $mysql->query("INSERT INTO " . prefix . "_eshop_images (`filepath`, `product_id`, `position`) VALUES ('$iname','$qid','$inx_img')");
                }
            }


            if ($features != null) {
                foreach ($features as $f_key => $f_value) {
                    if ($f_value != '') {
                        $mysql->query("INSERT INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')");
                    }
                }
            }

            $bidirect_linked_products = pluginGetVariable('eshop', 'bidirect_linked_products');

            if ($linked_products != null) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_related_products WHERE product_id='$qid'");
                foreach ($linked_products as $p_key => $p_value) {
                    $mysql->query("INSERT INTO " . prefix . "_eshop_related_products (`product_id`, `related_id`, `position`) VALUES ('$qid','$p_value','0')");
                    if ($bidirect_linked_products == "1") {
                        $lprd = $mysql->record("SELECT * FROM " . prefix . "_eshop_related_products WHERE product_id = " . db_squote($p_value) . " AND related_id = " . db_squote($qid) . " ");
                        if (empty($lprd)) {
                            $mysql->query("INSERT INTO " . prefix . "_eshop_related_products (`product_id`, `related_id`, `position`) VALUES ('$p_value','$qid','0')");
                        }
                    }
                }
            }

            $category_id = (int)$_REQUEST['parent'];

            if ($category_id != 0) {
                $mysql->query("INSERT INTO " . prefix . "_eshop_products_categories (`product_id`, `category_id`) VALUES ('$qid','$category_id')");
            }

            $optlist = array();
            if (isset($_REQUEST['so_data']) && is_array($_REQUEST['so_data'])) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_variants WHERE product_id='$qid'");
                foreach ($_REQUEST['so_data'] as $k => $v) {
                    if (is_array($v)) {
                        $v[2] = $v[2] ? $v[2] : '0.00';
                        $v[3] = $v[3] ? $v[3] : '0.00';
                        $v[4] = $v[4] ? $v[4] : 'NULL';
                        $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `sku`, `name`, `price`, `compare_price`, `amount`, `stock`) VALUES ('$qid', '$v[0]', '$v[1]', '$v[2]', '$v[3]', $v[4], '$v[5]')");
                    }
                }
            }

            generate_catz_cache(true);
            msg(array('type' => 'info', 'info' => 'Новый товар успешно добавлен'));
            redirect_eshop('?mod=extra-config&plugin=eshop');
        }
    }

    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    } else {
        $error_input = '';
    }

    foreach ($SQL as $k => $v) {
        $tEntry[$k] = $v;
    }

    $cats = getCats2();

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_features ORDER BY position, id") as $frow) {
        $frow['value'] = '';
        $frow['foptions'] = json_decode($frow['foptions'], true);
        foreach ($frow['foptions'] as $key => $value) {
            $frow['foptions'][$key] = $value;
        }
        $features_array[] = $frow;
    }

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_categories_features") as $cfrow) {
        $cat_features_array[$cfrow['category_id']][] = $cfrow['feature_id'];
    }

    $tEntry['catz'] = getTree($cats);
    $tEntry['features'] = $features_array;
    $tEntry['cat_features'] = json_encode($cat_features_array);
    $tEntry['error'] = $error_input;
    $tEntry['id'] = lastid('eshop_products');
    $tEntry['mode'] = "add";

    $xt = $twig->loadTemplate($tpath['config/add_product'] . 'config/' . 'add_product.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Продукция: Добавление продукта',
    );

    print $xg->render($tVars);
}

function edit_product()
{
    global $config, $mysql, $twig, $parse;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_product'), 'eshop', 1);

    $qid = intval($_REQUEST['id']);
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_products LEFT JOIN ' . prefix . '_eshop_products_categories ON ' . prefix . '_eshop_products.id=' . prefix . '_eshop_products_categories.product_id WHERE id = ' . db_squote($qid) . ' LIMIT 1');

    $cats = getCats2();

    $options_array = array();
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_options LEFT JOIN " . prefix . "_eshop_features ON " . prefix . "_eshop_features.id=" . prefix . "_eshop_options.feature_id WHERE " . prefix . "_eshop_options.product_id = '$qid' ORDER BY position, id") as $orow) {
        $options_array[$orow['id']] = $orow['value'];
    }

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_features ORDER BY position, id") as $frow) {
        $frow['value'] = $options_array[$frow['id']];
        $frow['foptions'] = json_decode($frow['foptions'], true);
        foreach ($frow['foptions'] as $key => $value) {
            $frow['foptions'][$key] = iconv("utf-8", "windows-1251", $value);
        }
        $features_array[] = $frow;
    }

    $positions_img = array();
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_images WHERE product_id = '$qid' ORDER BY position, id") as $irow) {
        $images_array[] =
            array(
                'id' => $irow['id'],
                'filepath' => $irow['filepath'],
                'product_id' => $irow['product_id'],
                'position' => $irow['position'],
                'del_link' => home . '/engine/admin.php?mod=extra-config&plugin=eshop&action=edit_product&id=' . $qid . '&delimg=' . $irow['id'] . '&filepath=' . $irow['filepath'] . '',
            );
        $positions_img[] = $irow['position'];
    }

    if (!empty($positions_img)) {
        $max_img_pos = max($positions_img) + 1;
    } else {
        $max_img_pos = 0;
    }

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_related_products rp LEFT JOIN " . prefix . "_eshop_products p ON p.id=rp.related_id WHERE rp.product_id = '$qid' ORDER BY rp.position") as $rrow) {
        $related_array[] =
            array(
                'name' => $rrow['name'],
                'product_id' => $rrow['product_id'],
                'related_id' => $rrow['related_id'],
                'position' => $rrow['position'],
            );
    }

    $price_array = [];
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_variants WHERE product_id = '$qid' ORDER BY position, id") as $vrow) {
        $price_array[] = $vrow;
    }

    $sOpts = array();
    $fNum = 1;
    if (isset($price_array) && is_array($price_array)) {
        foreach ($price_array as $k => $v) {
            $check_stock_array = array();
            $check_stock = $v['stock'];
            switch ($check_stock) {
                case '5':
                    $check_stock_array[5] = 'selected="selected"';
                    $check_stock_array[0] = '';
                    $check_stock_array[1] = '';
                    break;
                case '0':
                    $check_stock_array[5] = '';
                    $check_stock_array[0] = 'selected="selected"';
                    $check_stock_array[1] = '';
                    break;
                case '1':
                    $check_stock_array[5] = '';
                    $check_stock_array[0] = '';
                    $check_stock_array[1] = 'selected="selected"';
                    break;
                default:
                    $check_stock_array[5] = 'selected="selected"';
                    $check_stock_array[0] = '';
                    $check_stock_array[1] = '';
                    break;
            }
            array_push(
                $sOpts,
                '<tr>
                <td><input type="text" size="12" name="so_data[' . ($fNum) . '][0]" value="' . htmlspecialchars($v['sku'], ENT_COMPAT | ENT_HTML401, 'cp1251') . '"/></td>
                <td><input type="text" size="45" name="so_data[' . ($fNum) . '][1]" value="' . htmlspecialchars($v['name'], ENT_COMPAT | ENT_HTML401, 'cp1251') . '"/></td>
                <td><input type="text" size="12" name="so_data[' . ($fNum) . '][2]" value="' . $v['price'] . '"/></td>
                <td><input type="text" size="12" name="so_data[' . ($fNum) . '][3]" value="' . $v['compare_price'] . '"/></td>
                <td><input type="text" size="12" name="so_data[' . ($fNum) . '][4]" value="' . htmlspecialchars($v['amount'], ENT_COMPAT | ENT_HTML401, 'cp1251') . '"/></td>
                <td>
                    <select name="so_data[' . ($fNum) . '][5]" style="width: 100px;">
                        <option ' . $check_stock_array[5] . ' value="5">Есть</option>
                        <option ' . $check_stock_array[0] . ' value="0">Нет</option>
                        <option ' . $check_stock_array[1] . ' value="1">На заказ</option>
                    </select>
                </td>
                <td><a href="#" onclick="return false;"><img src="' . admin_url . '/plugins/eshop/tpl/img/delete.png" alt="DEL" width="12" height="12" /></a></td>
                </tr>'
            );
            $fNum++;
        }
    }
    if (!count($sOpts)) {
        array_push(
            $sOpts,
            '<tr>
            <td><input size="12" name="so_data[1][0]" type="text" value=""/></td>
            <td><input size="45" name="so_data[1][1]" type="text" value=""/></td>
            <td><input size="12" name="so_data[1][2]" type="text" value=""/></td>
            <td><input size="12" name="so_data[1][3]" type="text" value=""/></td>
            <td><input size="12" name="so_data[1][4]" type="text" value=""/></td>
            <td>
                <select name="so_data[1][5]" style="width: 100px;">
                    <option selected="selected" value="5">Есть</option>
                    <option value="0">Нет</option>
                    <option value="1">На заказ</option>
                </select>
            </td>
            <td><a href="#" onclick="return false;"><img src="' . admin_url . '/plugins/eshop/tpl/img/delete.png" alt="DEL" width="12" height="12" /></a></td>
        </tr>'
        );
    }

    $sOpts = implode("\n", $sOpts);

    if (isset($_REQUEST['handler'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название продукта не задано';
        }

        $SQL['code'] = secure_html(trim($_REQUEST['code']));

        $SQL['url'] = secure_html(trim($_REQUEST['url']));
        if (empty($SQL['url'])) {
            $SQL['url'] = strtolower($parse->translit($SQL['name'], 1, 1));
        }
        if (empty($SQL['url'])) {
            $SQL['url'] = strtolower($parse->translit($SQL['name'], 1, 1));
        }

        if ($SQL["url"]) {
            if (is_array($mysql->record("SELECT id FROM " . prefix . "_eshop_products WHERE url = " . db_squote($SQL["url"]) . " AND id <> " . $row['id'] . " LIMIT 1"))) {
                $error_text[] = 'Такой altname уже существует.';
            }
        }

        $SQL['meta_title'] = secure_html(trim($_REQUEST['meta_title']));
        $SQL['meta_keywords'] = secure_html(trim($_REQUEST['meta_keywords']));
        $SQL['meta_description'] = secure_html(trim($_REQUEST['meta_description']));

        $SQL['external_id'] = $_REQUEST['external_id'];

        $SQL['annotation'] = $_REQUEST['annotation'];
        $SQL['body'] = $_REQUEST['body'];

        $SQL['active'] = (int)$_REQUEST['active'];
        $SQL['featured'] = (int)$_REQUEST['featured'];
        $SQL['stocked'] = (int)$_REQUEST['stocked'];

        $SQL['editdate'] = time() + ($config['date_adjust'] * 60);

        $features = $_REQUEST['data']['features'];
        $images = $_REQUEST['data']['images'];
        if ($_REQUEST['linked-products'] != "") {
            $linked_products = explode(",", $_REQUEST['linked-products']);
        } else {
            $linked_products = null;
        }

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('UPDATE ' . prefix . '_eshop_products SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$qid . '\'  ');

            if ($images != null) {

                makeUploadsDirs('/eshop/products/' . $qid . '/');

                foreach ($images as $inx_img => $img) {
                    $timestamp = time();
                    $iname = $timestamp . "-" . $img;

                    $productsTempPath = '/eshop/products/';
                    moveFromTemp($qid, $productsTempPath, $img, $iname);

                    $pos = $max_img_pos + $inx_img;

                    $mysql->query("INSERT INTO " . prefix . "_eshop_images (`filepath`, `product_id`, `position`) VALUES ('$iname','$qid','$pos')");
                }
            }

            if ($features != null) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_options WHERE product_id='$qid'");
                foreach ($features as $f_key => $f_value) {
                    if ($f_value != '') {
                        $mysql->query("REPLACE INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value') ");
                    }
                }
            }

            $bidirect_linked_products = pluginGetVariable('eshop', 'bidirect_linked_products');

            if ($linked_products != null) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_related_products WHERE product_id='$qid'");
                foreach ($linked_products as $p_key => $p_value) {
                    $mysql->query("INSERT INTO " . prefix . "_eshop_related_products (`product_id`, `related_id`, `position`) VALUES ('$qid','$p_value','0')");
                    if ($bidirect_linked_products == "1") {
                        $lprd = $mysql->record("SELECT * FROM " . prefix . "_eshop_related_products WHERE product_id = " . db_squote($p_value) . " AND related_id = " . db_squote($qid) . " ");
                        if (empty($lprd)) {
                            $mysql->query("INSERT INTO " . prefix . "_eshop_related_products (`product_id`, `related_id`, `position`) VALUES ('$p_value','$qid','0')");
                        }
                    }
                }
            } else {
                $mysql->query("DELETE FROM " . prefix . "_eshop_related_products WHERE product_id='$qid'");
            }

            $category_id = (int)$_REQUEST['parent'];

            if ($category_id != 0) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_products_categories WHERE product_id='$qid'");
                $mysql->query("INSERT INTO " . prefix . "_eshop_products_categories (`product_id`, `category_id`) VALUES ('$qid','$category_id')");
            } else {
                $mysql->query("DELETE FROM " . prefix . "_eshop_products_categories WHERE product_id='$qid'");
            }

            if (isset($_REQUEST['so_data']) && is_array($_REQUEST['so_data'])) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_variants WHERE product_id='$qid'");
                foreach ($_REQUEST['so_data'] as $k => $v) {
                    if (is_array($v)) {
                        $v[2] = $v[2] ? $v[2] : '0.00';
                        $v[3] = $v[3] ? $v[3] : '0.00';
                        $v[4] = $v[4] ? $v[4] : 'NULL';
                        $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `sku`, `name`, `price`, `compare_price`, `amount`, `stock`) VALUES ('$qid', '$v[0]', '$v[1]', '$v[2]', '$v[3]', $v[4], '$v[5]')");
                    }
                }
            }

            generate_catz_cache(true);
            msg(array('type' => 'info', 'info' => 'Товар успешно отредактирован'));
            //return list_product();
            redirect_eshop('?mod=extra-config&plugin=eshop');
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($row as $k => $v) {
        $tEntry[$k] = $v;
    }

    if (isset($_REQUEST['delimg']) && isset($_REQUEST['filepath'])) {
        $imgID = (int)$_REQUEST['delimg'];
        $imgPath = secure_html(trim($_REQUEST['filepath']));
        $mysql->query("DELETE FROM " . prefix . "_eshop_images WHERE id = " . $imgID);
        delete_product_image($imgPath, $qid);

        $r_pos = 0;
        foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images WHERE product_id = ' . $row['id'] . ' ORDER BY position, id ') as $img_row) {
            $mysql->query("update " . prefix . "_eshop_images set position = " . db_squote($r_pos) . " where (id = " . db_squote($img_row['id']) . ") ");
            $r_pos += 1;
        }
        msg(array('type' => 'info', 'info' => "Изображение удалено"));
        //return print_commit_complete('eshop');
        redirect_eshop('?mod=extra-config&plugin=eshop&action=edit_product&id=' . $qid . '');
    }

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_categories_features") as $cfrow) {
        $cat_features_array[$cfrow['category_id']][] = iconv("windows-1251", "utf-8", $cfrow['feature_id']);
    }

    $tEntry['cat_features'] = json_encode($cat_features_array);
    $tEntry['catz'] = getTree($cats, $row['category_id'], 0);
    $tEntry['features'] = $features_array;
    $tEntry['entriesImg'] = $images_array;
    $tEntry['related'] = $related_array;
    $tEntry['sOpts'] = $sOpts;

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "edit";

    $view_link = checkLinkAvailable('eshop', 'show') ?
        generateLink('eshop', 'show', array('alt' => $row['url'])) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
    $prd_link = home . $view_link;


    $xt = $twig->loadTemplate($tpath['config/add_product'] . 'config/' . 'add_product.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Продукция: Редактирование продукта (Опубликован &#8594; <small><a href="' . $prd_link . '" target="_blank">' . $prd_link . '</a></small>)',
    );

    print $xg->render($tVars);
}

function modify_product()
{
    global $mysql;

    $selected_product = array();

    $selected_product = $_REQUEST['selected_product'];

    if (!$selected_product)
        return msg(array("type" => "error", "text" => "Не выбран ID!"));

    $subaction = $_REQUEST['subaction'];

    $id = implode(',', $selected_product);

    switch ($subaction) {
        case 'mass_delete':
            $del = true;
            break;
        case 'mass_active_add':
            $active_add = true;
            break;
        case 'mass_active_remove':
            $active_remove = true;
            break;
        case 'mass_featured_add':
            $featured_add = true;
            break;
        case 'mass_featured_remove':
            $featured_remove = true;
            break;
        case 'mass_stocked_add':
            $stocked_add = true;
            break;
        case 'mass_stocked_remove':
            $stocked_remove = true;
            break;
    }

    if (isset($del)) {
        $mysql->query("delete from " . prefix . "_eshop_products where id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_options where product_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_products_likes where product_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_products_comments where product_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_related_products where product_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_related_products where related_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_variants where product_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_ebasket where linked_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_compare where linked_fld in ({$id})");

        foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_images WHERE product_id in ({$id})") as $irow) {
            delete_product_image($irow['filepath'], $irow['product_id']);
        }
        $mysql->query("delete from " . prefix . "_eshop_images where product_id in ({$id})");

        $mysql->query("DELETE FROM " . prefix . "_eshop_products_categories WHERE product_id in ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} удалены!"));
    }

    if (isset($active_add)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products SET active = 1 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    if (isset($active_remove)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products SET active = 0 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    if (isset($featured_add)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products SET featured = 1 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    if (isset($featured_remove)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products SET featured = 0 WHERE id IN ({$id})");

        //edirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    if (isset($stocked_add)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products SET stocked = 1 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    if (isset($stocked_remove)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products SET stocked = 0 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    generate_catz_cache(true);
}

function getCats2()
{
    global $mysql;

    $levels = array();
    $tree = array();

    $rows = $mysql->select("SELECT * FROM " . prefix . "_eshop_categories ORDER BY position, id");

    foreach ($rows as $row) {

        $cur = &$levels[$row['id']];
        $cur['parent_id'] = $row['parent_id'];
        $cur['name'] = $row['name'];

        if ($row['parent_id'] == 0) {
            $tree[$row['id']] = &$cur;
        } else {
            $levels[$row['parent_id']]['children'][$row['id']] = &$cur;
        }
    }

    return $tree;
}

function getTree($arr, $flg = null, $l = 0)
{
    $flg;
    $out = '';
    $ft = '&#8212; ';
    foreach ($arr as $k => $v) {

        if ($k == $flg) {
            $out .= '<option value="' . $k . '" selected>' . str_repeat($ft, $l) . $v['name'] . '</option>';
        } else {
            $out .= '<option value="' . $k . '">' . str_repeat($ft, $l) . $v['name'] . '</option>';
        }
        if (!empty($v['children'])) {
            //$l = $l + 1;
            $out .= getTree($v['children'], $flg, $l + 1);
            //$l = $l - 1;
        }
    }

    return $out;
}

function getChildIdsArray($arr, $flg)
{
    $out = array();
    $flg;

    foreach ($arr as $k => $v) {

        if ($k == $flg) {
            $out = array_merge($out, array_keys($v['children']));
            /*
            foreach($v['children'] as $k1=>$v1){
                if(array_key_exists("children",$v1)) {
                    getChildIdsArray($v, $k1);
                }
            }
            */
        }
    }

    return $out;
}

function upload_cat_image()
{

    if (!empty($_FILES["image"])) {

        $myFile = $_FILES["image"];

        if ($myFile["error"] !== UPLOAD_ERR_OK) {
            return "";
        }

        // ensure a safe filename
        $img_name = preg_replace("/[^A-Z0-9._-]/i", "_", $myFile["name"]);

        // don't overwrite an existing file
        $i = 0;
        $parts = pathinfo($img_name);
        $uploadsDir = getUploadsDir();
        $upload_dir = $uploadsDir . '/eshop/categories/';
        $upload_thumbnail_dir = $uploadsDir . '/eshop/categories/thumb/';

        while (file_exists($upload_dir . $img_name)) {
            $i++;
            $img_name = $parts["filename"] . "-" . $i . "." . $parts["extension"];
        }

        $extensions = array_map('trim', explode(',', pluginGetVariable('eshop', 'catz_ext_image')));
        $ext = pathinfo($myFile['name'], PATHINFO_EXTENSION);

        if (!in_array($ext, $extensions)) {
            return "";
        }

        // preserve file from temporary directory
        $success = move_uploaded_file($myFile["tmp_name"], $upload_dir . $img_name);

        if (!$success) {
            return "";
        }

        $tempFile = $upload_dir . $img_name;
        $extension = $parts["extension"];

        $extensions = array_map('trim', explode(',', pluginGetVariable('eshop', 'catz_ext_image')));

        if (!in_array($extension, $extensions)) {
            return "";
        }

        // CREATE THUMBNAIL
        if ($extension == "jpg" || $extension == "jpeg") {
            $src = imagecreatefromjpeg($tempFile);
        } else {
            if ($extension == "png") {
                $src = imagecreatefrompng($tempFile);
            } else {
                $src = imagecreatefromgif($tempFile);
            }
        }

        list($width, $height) = getimagesize($tempFile);

        $newwidth = pluginGetVariable('eshop', 'catz_width_thumb');
        $newheight = ($height / $width) * $newwidth;
        $tmp = imagecreatetruecolor($newwidth, $newheight);

        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        $thumbname = $upload_thumbnail_dir . $img_name;

        if (file_exists($thumbname)) {
            unlink($thumbname);
        }

        imagejpeg($tmp, $thumbname, 100);

        imagedestroy($src);
        imagedestroy($tmp);
    }

    return $img_name;
}

function delete_cat_image($img_name)
{
    $uploadsDir = getUploadsDir();
    $upload_dir = $uploadsDir . '/eshop/categories/';
    $upload_thumbnail_dir = $uploadsDir . '/eshop/categories/thumb/';

    $imgname = $upload_dir . $img_name;
    $thumbname = $upload_thumbnail_dir . $img_name;

    if (file_exists($imgname)) {
        unlink($imgname);
    }

    if (file_exists($thumbname)) {
        unlink($thumbname);
    }
}

function delete_product_image($img_name, $qid)
{
    $uploadsDir = getUploadsDir();
    $upload_dir = $uploadsDir . '/eshop/products/' . $qid . '/';
    $upload_thumbnail_dir = $uploadsDir . '/eshop/products/' . $qid . '/thumb/';

    $imgname = $upload_dir . $img_name;
    $thumbname = $upload_thumbnail_dir . $img_name;

    if (file_exists($imgname)) {
        unlink($imgname);
    }

    if (file_exists($thumbname)) {
        unlink($thumbname);
    }
}

function add_cat()
{
    global $mysql, $twig, $parse;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_cat'), 'eshop', 1);

    if (isset($_REQUEST['submit'])) {

        $cat_name = secure_html(trim($_REQUEST['cat_name']));
        if (empty($cat_name)) {
            $error_text[] = 'Название категории не задано';
        }

        $description = secure_html(trim($_REQUEST['description']));

        $url = secure_html(trim($_REQUEST['url']));
        if (empty($SQL['url'])) {
            $SQL['url'] = strtolower($parse->translit($SQL['name'], 1, 1));
        }
        if (empty($url)) {
            $url = strtolower($parse->translit($cat_name, 1, 1));
        }

        if ($url) {
            if (is_array(
                $mysql->record("SELECT id FROM " . prefix . "_eshop_categories WHERE url = " . db_squote($url) . " LIMIT 1")
            )) {
                $error_text[] = 'Такой altname уже существует.';
            }
        }

        $meta_title = secure_html(trim($_REQUEST['meta_title']));
        if (empty($meta_title)) {
            $meta_title = $cat_name;
        }

        $meta_keywords = secure_html(trim($_REQUEST['meta_keywords']));
        $meta_description = secure_html(trim($_REQUEST['meta_description']));

        $parent_id = (int)$_REQUEST['parent'];

        $position = (int)$_REQUEST['position'];
        if (empty($position)) {
            $position = 0;
        }

        $active = "1";

        $img_name = upload_cat_image();

        if (empty($error_text)) {
            $mysql->query(
                'INSERT INTO ' . prefix . '_eshop_categories (name, description, url, meta_title, meta_keywords,  meta_description, parent_id, position, image, active)
                VALUES
                (' . db_squote($cat_name) . ',
                    ' . db_squote($description) . ',
                    ' . db_squote($url) . ',
                    ' . db_squote($meta_title) . ',
                    ' . db_squote($meta_keywords) . ',
                    ' . db_squote($meta_description) . ',
                    ' . db_squote($parent_id) . ',
                    ' . db_squote($position) . ',
                    ' . db_squote($img_name) . ',
                    ' . (int)$active . '
                )
            '
            );

            generate_catz_cache(true);
            msg(array('type' => 'info', 'info' => "Категория успешно добавлена"));
            return list_cat();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    $cats = getCats2();

    $tEntry = array(
        'cat_name' => $cat_name,
        'description' => $description,

        'url' => $url,
        'meta_title' => $meta_title,
        'meta_keywords' => $meta_keywords,
        'meta_description' => $meta_description,

        'parent_id' => $parent_id,
        'position' => $position,

        'image' => $img_name,
        'active' => $active,

        'error' => $error_input,
        'catz' => getTree($cats),
    );

    $xt = $twig->loadTemplate($tpath['config/add_cat'] . 'config/' . 'add_cat.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Категории: Добавление категории',
    );

    print $xg->render($tVars);
}

function edit_cat()
{
    global $mysql, $twig, $parse;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_cat'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_categories WHERE id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $cat_name = secure_html(trim($_REQUEST['cat_name']));
        if (empty($cat_name)) {
            $error_text[] = 'Название категории не задано';
        }

        $url = secure_html(trim($_REQUEST['url']));
        if (empty($url)) {
            $url = strtolower($parse->translit($cat_name, 1, 1));
        }
        if (empty($url)) {
            $url = strtolower($parse->translit($cat_name, 1, 1));
        }

        if ($url) {
            if (is_array(
                $mysql->record("SELECT id FROM " . prefix . "_eshop_categories WHERE url = " . db_squote($url) . " AND id <> " . $id . " LIMIT 1")
            )) {
                $error_text[] = 'Такой altname уже существует.';
            }
        }

        $description = input_filter_com($_REQUEST['description']);

        $meta_title = secure_html(trim($_REQUEST['meta_title']));
        if (empty($meta_title)) {
            $meta_title = $cat_name;
        }

        $meta_keywords = secure_html(trim($_REQUEST['meta_keywords']));
        $meta_description = secure_html(trim($_REQUEST['meta_description']));

        $parent_id = (int)$_REQUEST['parent'];

        $position = (int)$_REQUEST['position'];
        if (empty($position)) {
            $position = 0;
        }

        $active = "1";

        $img_name = upload_cat_image();
        if ($img_name != "") {
            $image_sql = "image = " . db_squote($img_name) . ",";
        } else {
            $image_sql = "";
        }

        $image_del = (int)$_REQUEST['image_del'];
        if ($image_del == 1) {
            delete_cat_image($row['image']);
            $image_sql = "image = '',";
        }

        if (empty($error_text)) {

            $mysql->query('UPDATE ' . prefix . '_eshop_categories SET   name = ' . db_squote($cat_name) . ', description = ' . db_squote($description) . ',  url = ' . db_squote($url) . ', meta_title = ' . db_squote($meta_title) . ', meta_keywords = ' . db_squote($meta_keywords) . ', meta_description = ' . db_squote($meta_description) . ', parent_id = ' . db_squote($parent_id) . ', position = ' . db_squote($position) . ', ' . $image_sql . 'active = ' . db_squote($active) . 'WHERE id = ' . $id . '');

            generate_catz_cache(true);
            msg(array('type' => 'info', 'info' => "Категория успешно отредактированна!"));
            return list_cat();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    $cats = getCats2();


    $tEntry = array(
        'cat_name' => $row['name'],
        'description' => $row['description'],

        'url' => $row['url'],
        'meta_title' => $row['meta_title'],
        'meta_keywords' => $row['meta_keywords'],
        'meta_description' => $row['meta_description'],

        'parent_id' => $row['parent_id'],
        'position' => $row['position'],

        'image' => $row['image'],
        'active' => $row['active'],

        'error' => $error_input,
        'catz' => getTree($cats, $row['parent_id'], 0),
    );

    $xt = $twig->loadTemplate($tpath['config/add_cat'] . 'config/' . 'add_cat.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Категории: Редактирование категории',
    );

    print $xg->render($tVars);
}

function del_cat()
{
    global $mysql;

    $id = (int)$_REQUEST['id'];

    if (empty($id)) {
        return msg(array("type" => "error", "text" => "Ошибка, вы не выбрали что хотите удалить"));
    }

    $cnt_products_in_cat = $mysql->record('SELECT COUNT(*) AS cnt FROM ' . prefix . '_eshop_products_categories WHERE category_id = ' . db_squote($id));

    if ($cnt_products_in_cat['cnt'] == 0) {
        $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_categories WHERE id = ' . db_squote($id) . ' LIMIT 1');
        delete_cat_image($row['image']);
        $mysql->query("DELETE FROM " . prefix . "_eshop_categories WHERE id = {$id}");
        msg(array("type" => "info", "info" => "Категория удалена"));
        generate_catz_cache(true);
    } else {
        msg(array("type" => "info", "info" => "Категория не может быть удалена, т.к. в ней есть продукция"));
    }
}

function list_cat()
{
    global $mysql, $twig, $config;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_cat', 'config/list_cat_tree'), 'eshop', 1);

    $tVars = array();

    //get all categories
    $catz_array = array();

    foreach ($mysql->select("SELECT *, (SELECT COUNT(*) FROM " . prefix . "_eshop_products_categories WHERE category_id = id) AS catCnt FROM " . prefix . "_eshop_categories ORDER BY position, id") as $row) {

        $catz_array[$row['id']] =
            array(
                'name' => $row['name'],
                'alt' => $row['url'],
                'parent' => $row['parent_id'],
                'CategoryID' => $row['id'],
                'CategoryName' => $row['name'],
                'Description' => $row['description'],
                'SortOrder' => $row['position'],
                'IconFile' => $row['image'],
                'catCnt' => $row['catCnt'],
            );
    }
    //generate menu starting with parent categories (that have a 0 parent)
    $tEntry = generate_menu(0, $catz_array);
    //var_dump($tEntry);

    if (pluginGetVariable('eshop', 'cat_tree')) {
        $xt = $twig->loadTemplate($tpath['config/list_cat'] . 'config/' . 'list_cat_tree.tpl');
    } else {
        $xt = $twig->loadTemplate($tpath['config/list_cat'] . 'config/' . 'list_cat.tpl');
    }

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Категории',
    );

    print $xg->render($tVars);
}

//function that lists categories
function generate_menu($parent, $catz_array)
{
    global $gvars, $mysql;
    $has_childs = false;

    $addspaces = '';

    foreach ($catz_array as $key => $value) {

        $view_link = checkLinkAvailable('eshop', '') ?
            generateLink('eshop', '', array('alt' => $value['alt'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $value['alt']));

        if ($value['parent'] == $parent) {

            $gvars[] = array(
                'id' => $value['CategoryID'],
                'alt' => $value['alt'],
                'cat_name' => $value['CategoryName'],
                'edit_link' => "?mod=extra-config&plugin=eshop&action=edit_cat&id=" . $value['CategoryID'],
                'del_link' => "?mod=extra-config&plugin=eshop&action=del_cat&id=" . $value['CategoryID'],
                'view_link' => $view_link,
                'prefix' => get_prefix($value['CategoryID']),
                'parent' => $value['parent'],
                'position' => $value['SortOrder'],
                'image' => $value['IconFile'],
                'catCnt' => $value['catCnt'],
            );

            if ($key != 0) {
                $addspaces .= '&nbsp;';
            }

            //call function again to generate list for subcategories belonging to current category
            generate_menu($key, $catz_array);
        }

        unset($temp_child_catz);
    }

    #var_dump($gvars);
    #var_dump("=========================================");

    #var_dump($gvars);

    if (pluginGetVariable('eshop', 'cat_tree')) {
        $tree = cat_tree($catz_array);
        $cat_menu = cat_show($tree);

        return $cat_menu;
    } else {
        return $gvars;
    }
}

function cat_tree($dataset)
{
    $tree = array();

    foreach ($dataset as $id => &$node) {
        if (!$node['parent']) {
            $tree[$id] = &$node;
        } else {
            $dataset[$node['parent']]['childs'][$id] = &$node;
        }
    }
    return $tree;
}

function cat_gen_menu($category)
{
    global $mysql;

    if ($category['parent'] == $parent) {
        $span = ' class="cat_active"';
        $class = ' class="cat_bar"';
    } else {
        $span = '';
        $class = '';
    }

    $view_link = checkLinkAvailable('eshop', '') ?
        generateLink('eshop', '', array('alt' => $category['alt'])) :
        generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $category['alt']));

    $del_link = "?mod=extra-config&plugin=eshop&action=del_cat&id=" . $category['CategoryID'];
    $edit_link = "?mod=extra-config&plugin=eshop&action=edit_cat&id=" . $category['CategoryID'];
    $thumb = $category['IconFile'] ? '<span class="cat_img" style="padding-left:8px;"><img src="/uploads/eshop/categories/thumb/' . $category['IconFile'] . '" /></span>' : '';

    $menu = "<li" . $class . " style='list-style-type: none;padding: 10px;background: azure;'>
	<div class='action-list'>
	" . $thumb . "
	<span style='padding-left:8px;'><a href='" . $edit_link . "' title=''><img src='" . admin_url . "/plugins/eshop/tpl/img/edit.png'></a></span>
	<span style='padding-left:8px;'><a href='" . $del_link . "'/><img src='" . admin_url . "/plugins/eshop/tpl/img/delete.png'></a></span>
	<span>#" . $category['CategoryID'] . "</span>
	</div>";

    $menu .= "<span" . $span . "><a href='" . $view_link . "' title='" . $category['CategoryName'] . "'>" . $category['CategoryName'] . "</a><br><small><a href='" . home . $view_link . "' title=''>" . home . $view_link . "</a></small></span>";
    if (isset($category['childs'])) {
        $menu .= '<ul>' . cat_show($category['childs']) . '</ul>';
    }
    $menu .= '</li>';

    return $menu;
}

function cat_show($data)
{
    $string = '';
    foreach ($data as $item) {
        $string .= cat_gen_menu($item);
    }
    return $string;
}

//get spaces to list subcategories
function get_prefix($CategoryID)
{
    global $mysql, $prefixed;
    $ParentID = $mysql->result('SELECT parent_id FROM ' . prefix . '_eshop_categories WHERE id = ' . $CategoryID . ' ');

    $prefixed[$CategoryID]['f'] .= '&nbsp;&nbsp;&nbsp;';
    #$add_prefix .= '&nbsp;&nbsp;&nbsp;';
    {
        if ($ParentID == 0) {
            $add_prefix .= '';
        } else {
            $prefixed[$CategoryID]['s'] .= '<img src="/engine/plugins/eshop/tpl/img/tree.gif">&nbsp;&nbsp;&nbsp;';
            $add_prefix .= '<img src="/engine/plugins/eshop/tpl/img/tree.gif">&nbsp;&nbsp;&nbsp;';

            foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_categories WHERE id=" . $ParentID . " ") as $row2) {
                $CategoryID2 = $row2['id'];
                $ParentID2 = $row2['parent_id'];
            }

            get_prefix($CategoryID2);
        }
    }

    return $add_prefix;
}


function list_feature()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_feature'), 'eshop', 1);

    $tVars = array();

    //get all categories
    $catz_array = array();

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_features ORDER BY position, id") as $row) {
        $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_feature&id=" . $row['id'];
        $row['del_link'] = "?mod=extra-config&plugin=eshop&action=del_feature&id=" . $row['id'];
        $row['foptions'] = json_decode($row['foptions'], true);
        foreach ($row['foptions'] as $key => $value) {
            $row['foptions'][$key] = iconv("utf-8", "windows-1251", $value);
        }
        $tEntry[] = $row;
    }

    $xt = $twig->loadTemplate($tpath['config/list_feature'] . 'config/' . 'list_feature.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Свойства',
    );

    print $xg->render($tVars);
}

function add_feature()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_feature'), 'eshop', 1);

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название свойства не задано';
        }

        $SQL['position'] = (int)$_REQUEST['position'];
        if (empty($SQL['position'])) {
            $SQL['position'] = 0;
        }

        $SQL['in_filter'] = (int)$_REQUEST['in_filter'];
        if (empty($SQL['in_filter'])) {
            $SQL['in_filter'] = 0;
        }

        $ftype = secure_html(trim($_REQUEST['ftype']));
        switch ($ftype) {
            case 'text':
                $SQL['ftype'] = '0';
                $SQL['fdefault'] = secure_html(trim($_REQUEST['text_default']));
                break;
            case 'checkbox':
                $SQL['ftype'] = '1';
                $SQL['fdefault'] = (int)$_REQUEST['checkbox_default'];
                break;
            case 'select':
                $SQL['ftype'] = '2';
                $SQL['fdefault'] = secure_html(trim($_REQUEST['select_default']));
                $optlist = array();
                if (isset($_REQUEST['so_data']) && is_array($_REQUEST['so_data'])) {
                    foreach ($_REQUEST['so_data'] as $k => $v) {
                        if (is_array($v) && isset($v[0]) && isset($v[1]) && (($v[0] != '') || ($v[1] != ''))) {
                            if ($v[0] != '') {
                                $optlist[$v[0]] = iconv("windows-1251", "utf-8", $v[1]);
                            } else {
                                $optlist[] = iconv("windows-1251", "utf-8", $v[1]);
                            }
                        }
                    }
                }
                $SQL['foptions'] = json_encode($optlist);
                break;
            case 'html':
                $SQL['ftype'] = '3';
                $SQL['fdefault'] = $_REQUEST['html_default'];
                break;
        }

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_features SET ' . implode(', ', $vnames) . ' ');

            $rowID = lastid('eshop_features');

            $ids = $_REQUEST['feature_categories'];

            foreach ($ids as $id) {
                $mysql->query('INSERT INTO ' . prefix . '_eshop_categories_features (category_id, feature_id) VALUES (' . db_squote($id) . ',' . db_squote($rowID) . ')');
            }

            generate_features_cache(true);
            generate_categories_features_cache(true);

            msg(array('type' => 'info', 'info' => "Новое свойство успешно добавлено!"));
            return list_feature();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    $cats = getCats2();

    foreach ($SQL as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['catz'] = getTree($cats);

    $xt = $twig->loadTemplate($tpath['config/add_feature'] . 'config/' . 'add_feature.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
        'mode' => "add",
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Свойства: Добавление свойства',
    );

    print $xg->render($tVars);
}

function edit_feature()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_feature'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_features WHERE id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название свойства не задано';
        }

        $SQL['position'] = (int)$_REQUEST['position'];
        if (empty($SQL['position'])) {
            $SQL['position'] = 0;
        }

        $SQL['in_filter'] = (int)$_REQUEST['in_filter'];
        if (empty($SQL['in_filter'])) {
            $SQL['in_filter'] = 0;
        }

        $ftype = secure_html(trim($_REQUEST['ftype']));
        switch ($ftype) {
            case 'text':
                $SQL['ftype'] = '0';
                $SQL['fdefault'] = secure_html(trim($_REQUEST['text_default']));
                break;
            case 'checkbox':
                $SQL['ftype'] = '1';
                $SQL['fdefault'] = (int)$_REQUEST['checkbox_default'];
                break;
            case 'select':
                $SQL['ftype'] = '2';
                $SQL['fdefault'] = secure_html(trim($_REQUEST['select_default']));
                $optlist = array();
                if (isset($_REQUEST['so_data']) && is_array($_REQUEST['so_data'])) {
                    foreach ($_REQUEST['so_data'] as $k => $v) {
                        if (is_array($v) && isset($v[0]) && isset($v[1]) && (($v[0] != '') || ($v[1] != ''))) {
                            if ($v[0] != '') {
                                $optlist[$v[0]] = iconv("windows-1251", "utf-8", $v[1]);
                            } else {
                                $optlist[] = iconv("windows-1251", "utf-8", $v[1]);
                            }
                        }
                    }
                }
                $SQL['foptions'] = json_encode($optlist);
                break;
            case 'html':
                $SQL['ftype'] = '3';
                $SQL['fdefault'] = $_REQUEST['html_default'];
                break;
        }

        if (empty($error_text)) {

            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('UPDATE ' . prefix . '_eshop_features SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$id . '\' ');

            $mysql->query("delete from " . prefix . "_eshop_categories_features where feature_id in ({$id})");
            $ids = $_REQUEST['feature_categories'];

            foreach ($ids as $id_x) {
                $mysql->query('INSERT INTO ' . prefix . '_eshop_categories_features (category_id, feature_id) VALUES (' . db_squote($id_x) . ',' . db_squote($id) . ')');
            }

            generate_features_cache(true);
            generate_categories_features_cache(true);

            msg(array('type' => 'info', 'info' => "Свойство успешно отредактированно"));
            return list_feature();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    $cats = getCats2();

    $cat_ids = array();
    foreach (
        $mysql->select(
            "SELECT * FROM " . prefix . "_eshop_categories_features WHERE feature_id = " . db_squote($id) . ""
        ) as $frow
    ) {
        $cat_ids[] = $frow['category_id'];
    }

    foreach ($row as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['catz'] = getMultiTree($cats, $cat_ids, 0);
    $tEntry['foptions'] = json_decode($tEntry['foptions'], true);

    $sOpts = array();
    $fNum = 1;
    if ($tEntry['ftype'] == '2') {
        if (is_array($tEntry['foptions'])) {
            foreach ($tEntry['foptions'] as $k => $v) {
                array_push($sOpts, '<tr><td><input size="12" name="so_data[' . ($fNum) . '][0]" type="text" value="' . ($tEntry['foptions'] ? htmlspecialchars($k, ENT_COMPAT | ENT_HTML401, 'cp1251') : '') . '"/></td><td><input type="text" size="55" name="so_data[' . ($fNum) . '][1]" value="' . iconv("utf-8", "windows-1251", $v) . '"/></td><td><a href="#" onclick="return false;"><img src="' . admin_url . '/plugins/eshop/tpl/img/delete.png" alt="DEL" width="12" height="12" /></a></td></tr>');
                $fNum++;
            }
        }
    }
    if (!count($sOpts)) {
        array_push($sOpts, '<tr><td><input size="12" name="so_data[1][0]" type="text" value=""/></td><td><input type="text" size="55" name="so_data[1][1]" value=""/></td><td><a href="#" onclick="return false;"><img src="' . admin_url . '/plugins/eshop/tpl/img/delete.png" alt="DEL" width="12" height="12" /></a></td></tr>');
    }

    $tEntry['sOpts'] = implode("\n", $sOpts);

    $xt = $twig->loadTemplate($tpath['config/add_feature'] . 'config/' . 'add_feature.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
        'mode' => "edit",
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Свойства: Редактирование свойства',
    );

    print $xg->render($tVars);
}

function getMultiTree($arr, $flg, $l)
{
    $flg;
    $out = '';
    $ft = '&#8212; ';

    foreach ($arr as $k => $v) {
        if (in_array($k, $flg)) {
            $out .= '<option value="' . $k . '" selected>' . str_repeat($ft, $l) . $v['name'] . '</option>';
        } else {
            $out .= '<option value="' . $k . '">' . str_repeat($ft, $l) . $v['name'] . '</option>';
        }
        if (!empty($v['children'])) {
            //$l = $l + 1;
            $out .= getMultiTree($v['children'], $flg, $l + 1);
            //$l = $l - 1;
        }
    }

    return $out;
}

function modify_feature()
{
    global $mysql;

    $selected_feature = $_REQUEST['selected_feature'];

    if (!$selected_feature)
        return msg(array("type" => "error", "text" => "Не выбран ID!"));

    $subaction = $_REQUEST['subaction'];

    $id = implode(',', $selected_feature);

    switch ($subaction) {
        case 'mass_delete':
            $del = true;
            break;
    }

    if (isset($del)) {
        $mysql->query("delete from " . prefix . "_eshop_features where id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_categories_features where feature_id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_options where feature_id in ({$id})");
        msg(array("type" => "info", "info" => "Записи с ID${id} удалены!"));
    }

    generate_features_cache(true);
}


function list_order()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_order'), 'eshop', 1);

    $tVars = array();

    // Load admin page based cookies
    $admCookie = admcookie_get();

    $fName = $_REQUEST['fname'];
    $fPhone = $_REQUEST['fphone'];
    $fAdress = $_REQUEST['fadress'];
    $fCustomerName = $_REQUEST['an'];

    // Selected date
    $fDateStart = $_REQUEST['dr1'];
    $fDateEnd = $_REQUEST['dr2'];

    if ($fDateStart == 'DD.MM.YYYY') {
        $fDateStart = '';
    }
    if ($fDateEnd == 'DD.MM.YYYY') {
        $fDateEnd = '';
    }

    $news_per_page = isset($_REQUEST['rpp']) ? (int)$_REQUEST['rpp'] : (int)$admCookie['eshop']['pp_order'];
    // - Set default value for `Records Per Page` parameter
    if (($news_per_page < 2) || ($news_per_page > 2000)) {
        $news_per_page = 10;
    }

    // - Save into cookies current value
    $admCookie['eshop']['pp_order'] = $news_per_page;
    admcookie_set($admCookie);

    $conditions = array();
    if ($fName) {
        array_push($conditions, "name LIKE " . db_squote("%" . $fName . "%"));
    }

    if ($fPhone) {
        array_push($conditions, "phone LIKE " . db_squote("%" . $fPhone . "%"));
    }

    if ($fAdress) {
        array_push($conditions, "address LIKE " . db_squote("%" . $fAdress . "%"));
    }

    if ($fCustomerName) {
        $sqlQCustomer = "SELECT id FROM " . prefix . "_users WHERE name = " . db_squote($fCustomerName) . ";";
        $customer_id = $mysql->result($sqlQCustomer);
        array_push($conditions, "author_id = " . db_squote($customer_id));
    }

    if ($fDateStart && $fDateEnd) {
        $fDateEndWithTime = $fDateEnd . " 23:59:59";
        array_push($conditions, "dt BETWEEN UNIX_TIMESTAMP(STR_TO_DATE(" . db_squote($fDateStart) . ",'%d.%m.%Y')) AND UNIX_TIMESTAMP(STR_TO_DATE(" . db_squote($fDateEndWithTime) . ",'%d.%m.%Y %H:%i:%s'))");
    } elseif ($fDateStart) {
        array_push($conditions, "dt BETWEEN UNIX_TIMESTAMP(STR_TO_DATE(" . db_squote($fDateStart) . ",'%d.%m.%Y')) AND NOW()");
    } elseif ($fDateEnd) {
        $fDateEndWithTime = $fDateEnd . " 23:59:59";
        array_push($conditions, "dt BETWEEN UNIX_TIMESTAMP(STR_TO_DATE('01.01.1970','%d.%m.%Y')) AND UNIX_TIMESTAMP(STR_TO_DATE(" . db_squote($fDateEndWithTime) . ",'%d.%m.%Y %H:%i:%s'))");
    }

    $fSort = " ORDER BY id DESC";
    $sqlQPart = "FROM " . prefix . "_eshop_orders " . (count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
    $sqlQ = "SELECT * " . $sqlQPart;

    $sqlQCount = "SELECT COUNT(*) AS CNT FROM (" . $sqlQ . ") AS T ";

    //$sqlQCount = "SELECT COUNT(p.id) FROM ng_eshop_products p ORDER BY p.id DESC";
    //var_dump($sqlQ);

    $pageNo = (int)$_REQUEST['page'] ? $_REQUEST['page'] : 0;
    if ($pageNo < 1) {
        $pageNo = 1;
    }
    if (!$start_from) {
        $start_from = ($pageNo - 1) * $news_per_page;
    }

    $count = $mysql->result($sqlQCount);
    $countPages = ceil($count / $news_per_page);

    foreach ($mysql->select($sqlQ . ' LIMIT ' . $start_from . ', ' . $news_per_page) as $row) {

        $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_order&id=" . $row['id'];
        $tEntry[] = $row;
    }

    $xt = $twig->loadTemplate($tpath['config/list_order'] . 'config/' . 'list_order.tpl');

    $tVars = array(
        'pagesss' => generateAdminPagelist(
            array(
                'current' => $pageNo,
                'count' => $countPages,
                'url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop&action=list_order' . ($news_per_page ? '&rpp=' . $news_per_page : '') . ($fName ? '&fname=' . $fName : '') . ($fPhone ? '&fphone=' . $fPhone : '') . ($fAdress ? '&fadress=' . $fAdress : '') . '&page=%page%',
            )
        ),
        'rpp' => $news_per_page,
        'fname' => secure_html($fName),
        'fphone' => secure_html($fPhone),
        'fadress' => secure_html($fAdress),
        'an' => secure_html($fCustomerName),
        'fDateStart' => $fDateStart ? $fDateStart : '',
        'fDateEnd' => $fDateEnd ? $fDateEnd : '',
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Заказы',
    );

    print $xg->render($tVars);
}

function edit_order()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_order'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT *, o.id AS id, o.ip AS ip, o.name AS name, u.name AS author, o.paid AS paid FROM ' . prefix . '_eshop_orders o LEFT JOIN ' . prefix . '_users u ON o.author_id = u.id WHERE o.id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $name = $_REQUEST['name'];
        $email = $_REQUEST['email'];
        $phone = $_REQUEST['phone'];
        $address = $_REQUEST['address'];

        $comment = $_REQUEST['comment'];
        $paid = secure_html(trim($_REQUEST['paid']));

        $payment_type_id = filter_var($_REQUEST['payment_type_id'], FILTER_SANITIZE_STRING);
        $delivery_type_id = filter_var($_REQUEST['delivery_type_id'], FILTER_SANITIZE_STRING);

        if (empty($error_text)) {

            $mysql->query('UPDATE ' . prefix . '_eshop_orders SET name = ' . db_squote($name) . ', email = ' . db_squote($email) . ', phone = ' . db_squote($phone) . ', address = ' . db_squote($address) . ', comment = ' . db_squote($comment) . ', paid = ' . db_squote($paid) . ', payment_type_id = ' . db_squote($payment_type_id) . ', delivery_type_id = ' . db_squote($delivery_type_id) . ' WHERE id = ' . $id . '');
            msg(array("type" => "info", "info" => "Заказ от {$name} успешно отрдактирован"));
            return list_order();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    $filter = array();
    if ($id) {
        $filter[] = '(order_id = ' . db_squote($id) . ')';
    }

    $basket = array();
    $total = 0;
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_order_basket WHERE " . join(" or ", $filter), 1) as $rec) {
        $total += round($rec['price'] * $rec['count'], 2);

        $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
        $rec['xfields'] = unserialize($rec['linked_fld']);
        unset($rec['linked_fld']);

        $basket[] = $rec;
    }

    $purchases = array();
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_purchases WHERE " . join(" or ", $filter), 1) as $prow) {
        $prow['info'] = json_decode($prow['info'], true);
        foreach ($prow['info'] as $k_info => $v_info) {
            $prow['info_string'] .= $k_info . " => " . iconv("utf-8", "windows-1251", $v_info) . "<br/>";
        }
        $purchases[] = $prow;
    }

    $row['profile_link'] = checkLinkAvailable('uprofile', 'show') ?
        generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])) :
        generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('id' => $row['author_id']));

    $tEntry = $row;
    $tEntry['error'] = $error_input;
    $tEntry['basket'] = $basket;
    $tEntry['purchases'] = $purchases;
    $tEntry['basket_total'] = $total;
    $tEntry['entriesDeliveryTypes'] = getDeliveryTypes();
    $tEntry['entriesPaymentTypes'] = getPaymentTypes();

    $xt = $twig->loadTemplate($tpath['config/add_order'] . 'config/' . 'add_order.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Заказ: Карточка заказа (ID: ' . $id . ')',
    );

    print $xg->render($tVars);
}

function modify_order()
{
    global $mysql;

    $selected_order = $_REQUEST['selected_order'];

    if (!$selected_order)
        return msg(array("type" => "error", "text" => "Не выбран ID!"));

    $subaction = $_REQUEST['subaction'];

    $id = implode(',', $selected_order);

    switch ($subaction) {
        case 'mass_delete':
            $del = true;
            break;
    }

    if (isset($del)) {
        $mysql->query("delete from " . prefix . "_eshop_orders where id in ({$id})");
        $mysql->query("delete from " . prefix . "_eshop_order_basket where order_id in ({$id})");
        msg(array("type" => "info", "info" => "Записи с ID${id} удалены!"));
    }
}

function list_comment()
{
    global $mysql, $twig, $parse, $config, $TemplateCache;

    $admCookie = admcookie_get();

    $tpath = locatePluginTemplates(array('config/main', 'config/list_comment'), 'eshop', 1);

    $tVars = array();

    $news_per_page = isset($_REQUEST['rpp']) ? (int)$_REQUEST['rpp'] : (int)$admCookie['eshop']['pp_comment'];
    // - Set default value for `Records Per Page` parameter
    if (($news_per_page < 2) || ($news_per_page > 2000)) {
        $news_per_page = 10;
    }

    // - Save into cookies current value
    $admCookie['eshop']['pp_comment'] = $news_per_page;
    admcookie_set($admCookie);

    $conditions = array();

    $fSort = "ORDER BY c.postdate DESC";
    $sqlQPart = "from " . prefix . "_eshop_products_comments c LEFT JOIN " . prefix . "_users u ON c.author_id = u.id LEFT JOIN " . prefix . "_eshop_products p ON c.product_id = p.id " . (count($conditions) ? "where " . implode(" AND ", $conditions) : '') . ' ' . $fSort;
    $sqlQ = "select c.id as cid, u.id as uid, u.name as uname, c.name as name, p.id as product_id, p.url as url, p.name as title, c.mail as mail, c.postdate as postdate, c.author as author, c.author_id as author_id, u.avatar as avatar, c.reg as reg, c.text as text, c.status as status " . $sqlQPart;

    $sqlQCount = "SELECT COUNT(*) AS CNT FROM (" . $sqlQ . ") AS T ";

    $pageNo = (int)$_REQUEST['page'] ? $_REQUEST['page'] : 0;
    if ($pageNo < 1) {
        $pageNo = 1;
    }
    if (!$start_from) {
        $start_from = ($pageNo - 1) * $news_per_page;
    }

    $count = $mysql->result($sqlQCount);
    $countPages = ceil($count / $news_per_page);

    foreach ($mysql->select($sqlQ . ' LIMIT ' . $start_from . ', ' . $news_per_page) as $row) {
        // Add [hide] tag processing
        $text = $row['text'];

        if ($config['use_bbcodes']) {
            $text = $parse->bbcodes($text);
        }
        if ($config['use_htmlformatter']) {
            $text = $parse->htmlformatter($text);
        }
        if ($config['use_smilies']) {
            $text = $parse->smilies($text);
        }

        if ($config['use_avatars']) {

            $tplVars = $TemplateCache['site']['#variables'];
            $noAvatarURL = (isset($tplVars['configuration']) && is_array(
                $tplVars['configuration']
            ) && isset($tplVars['configuration']['noAvatarImage']) && $tplVars['configuration']['noAvatarImage']) ? (tpl_url . "/" . $tplVars['configuration']['noAvatarImage']) : (avatars_url . "/noavatar.gif");

            if ($row['avatar']) {
                $avatar = avatars_url . "/" . $row['avatar'];
            } else {
                $avatar = $noAvatarURL;
            }
        } else {
            $avatar = '';
        }

        $view_link = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $row['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));

        $tEntries[] = array(
            'id' => $row['cid'],
            'mail' => $row['mail'],
            'author' => $row['name'],
            'date' => $row['postdate'],
            'profile_link' => checkLinkAvailable('uprofile', 'show') ?
                generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])) :
                generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('id' => $row['author_id'])),
            'avatar' => $avatar,
            'name' => $row['uname'],
            'commenttext' => $text,
            'title' => $row['title'],
            'view_link' => $view_link,
            'product_edit_link' => "?mod=extra-config&plugin=eshop&action=edit_product&id=" . $row['product_id'] . "",
            'reg' => $row['reg'],
            'status' => $row['status'],
        );
    }

    $xt = $twig->loadTemplate($tpath['config/list_comment'] . 'config/' . 'list_comment.tpl');

    $tVars = array(
        'entries' => isset($tEntries) ? $tEntries : '',
        'pagesss' => generateAdminPagelist(
            array(
                'current' => $pageNo,
                'count' => $countPages,
                'url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop&action=list_comment' . ($news_per_page ? '&rpp=' . $news_per_page : '') . '&page=%page%',
            )
        ),
        'rpp' => $news_per_page,
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Комментарии',
    );

    print $xg->render($tVars);
}

function modify_comment()
{
    global $mysql;

    $selected_comment = $_REQUEST['selected_comment'];

    if (!$selected_comment)
        return msg(array("type" => "error", "text" => "Не выбран ID!"));

    $subaction = $_REQUEST['subaction'];

    $id = implode(',', $selected_comment);

    switch ($subaction) {
        case 'mass_delete':
            $del = true;
            break;
        case 'mass_active_add':
            $active_add = true;
            break;
        case 'mass_active_remove':
            $active_remove = true;
            break;
    }

    if (isset($del)) {
        foreach ($selected_comment as $com_id) {
            $com_row = $mysql->record("SELECT * FROM " . prefix . "_eshop_products_comments WHERE id=" . db_squote($com_id) . " ");
            $mysql->query("DELETE FROM " . prefix . "_eshop_products_comments WHERE id = " . db_squote($com_id) . " ");
            $mysql->query("update " . prefix . "_eshop_products set comments = comments - 1 where id = " . db_squote($com_row['product_id']) . " ");
        }

        msg(array("type" => "info", "info" => "Записи с ID${id} удалены!"));
    }

    if (isset($active_add)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products_comments SET status = 1 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }

    if (isset($active_remove)) {
        $mysql->query("UPDATE " . prefix . "_eshop_products_comments SET status = 0 WHERE id IN ({$id})");

        //redirect_eshop('?mod=extra-config&plugin=eshop');
        msg(array("type" => "info", "info" => "Записи с ID ${id} обновлены!"));
    }
}


function add_currency()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_currencies'), 'eshop', 1);

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название валюты не задано';
        }

        $SQL['sign'] = secure_html(trim($_REQUEST['sign']));
        if (empty($SQL['sign'])) {
            $error_text[] = 'Знак валюты не задан';
        }

        $SQL['code'] = secure_html(trim($_REQUEST['code']));
        if (empty($SQL['code'])) {
            $error_text[] = 'Код валюты не задан';
        }

        $SQL['rate_from'] = intval($_REQUEST['rate_from']);
        if (empty($SQL['rate_from'])) {
            $error_text[] = 'Конверсия не задана';
        }

        $SQL['rate_to'] = 1;
        $SQL['cents'] = 1;
        $SQL['position'] = secure_html(trim($_REQUEST['position']));
        if ($SQL['position'] == "") {
            $SQL['position'] = 0;
        }
        $SQL['enabled'] = secure_html(trim($_REQUEST['enabled']));

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_currencies SET ' . implode(', ', $vnames) . ' ');
            generate_currency_cache(true);
            msg(array("type" => "info", "info" => "Новая валюта успешно добавлена!"));
            return list_currencies();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($SQL as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "add";

    $xt = $twig->loadTemplate($tpath['config/add_currencies'] . 'config/' . 'add_currencies.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );


    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты: Добавление валюты',
    );

    print $xg->render($tVars);
}

function edit_currency()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_currencies'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_currencies WHERE id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название валюты не задано';
        }

        $SQL['sign'] = secure_html(trim($_REQUEST['sign']));
        if (empty($SQL['sign'])) {
            $error_text[] = 'Знак валюты не задан';
        }

        $SQL['code'] = secure_html(trim($_REQUEST['code']));
        if (empty($SQL['code'])) {
            $error_text[] = 'Код валюты не задан';
        }

        $SQL['rate_from'] = secure_html(trim($_REQUEST['rate_from']));
        if (empty($SQL['rate_from'])) {
            $error_text[] = 'Конверсия не задана';
        }

        $SQL['rate_to'] = 1;
        $SQL['cents'] = 1;
        $SQL['position'] = secure_html(trim($_REQUEST['position']));
        if ($SQL['position'] == "") {
            $SQL['position'] = 0;
        }
        $SQL['enabled'] = secure_html(trim($_REQUEST['enabled']));

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('UPDATE ' . prefix . '_eshop_currencies SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$id . '\' ');

            generate_currency_cache(true);

            msg(array('type' => 'info', 'info' => "Валюта успешно отредактирована!"));
            return list_currencies();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($row as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "edit";

    $xt = $twig->loadTemplate($tpath['config/add_currencies'] . 'config/' . 'add_currencies.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты: Редактирование валюты',
    );

    print $xg->render($tVars);
}

function del_currency()
{
    global $mysql;

    $id = (int)$_REQUEST['id'];

    if (empty($id)) {
        return msg(array("type" => "error", "text" => "Ошибка, вы не выбрали что хотите удалить"));
    }

    if ($id == "1") {
        return msg(array("type" => "error", "text" => "Ошибка, вы не можете уалить основную валюту"));
    }

    $mysql->query("DELETE FROM " . prefix . "_eshop_currencies WHERE id = {$id}");
    generate_currency_cache(true);
    msg(array("type" => "info", "info" => "Валюта удалена"));
}

function list_currencies()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_currencies'), 'eshop', 1);

    $tVars = array();

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_currencies ORDER BY position, id") as $row) {

        $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_currency&id=" . $row['id'] . "";
        $row['del_link'] = "?mod=extra-config&plugin=eshop&action=del_currency&id=" . $row['id'] . "";
        $tEntry[] = $row;
    }

    $xt = $twig->loadTemplate($tpath['config/list_currencies'] . 'config/' . 'list_currencies.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты',
    );

    print $xg->render($tVars);
}

function add_payment_type()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_payment_type'), 'eshop', 1);

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название не задано';
        }

        $SQL['description'] = secure_html(trim($_REQUEST['description']));

        $SQL['position'] = secure_html(trim($_REQUEST['position']));
        if ($SQL['position'] == "") {
            $SQL['position'] = 0;
        }
        $SQL['active'] = secure_html(trim($_REQUEST['active']));

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_payment_type SET ' . implode(', ', $vnames) . ' ');

            msg(array('type' => 'info', 'info' => "Способ оплаты успешно добавлен!"));
            return list_payment_type();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($SQL as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "add";

    $xt = $twig->loadTemplate($tpath['config/add_payment_type'] . 'config/' . 'add_payment_type.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );


    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты: Добавление способа оплаты',
    );

    print $xg->render($tVars);
}

function edit_payment_type()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_payment_type'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_payment_type WHERE id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название не задано';
        }

        $SQL['description'] = secure_html(trim($_REQUEST['description']));

        $SQL['position'] = secure_html(trim($_REQUEST['position']));
        if ($SQL['position'] == "") {
            $SQL['position'] = 0;
        }
        $SQL['active'] = secure_html(trim($_REQUEST['active']));

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('UPDATE ' . prefix . '_eshop_payment_type SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$id . '\' ');

            msg(array('type' => 'info', 'info' => "Способ оплаты успешно отредактирован!"));
            return list_payment_type();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($row as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "edit";

    $xt = $twig->loadTemplate($tpath['config/add_payment_type'] . 'config/' . 'add_payment_type.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты: Редактирование способа оплаты',
    );

    print $xg->render($tVars);
}

function del_payment_type()
{
    global $mysql;

    $id = (int)$_REQUEST['id'];

    if (empty($id)) {
        return msg(array("type" => "error", "text" => "Ошибка, вы не выбрали что хотите удалить"));
    }

    if ($id == "1") {
        return msg(array("type" => "error", "text" => "Ошибка, вы не можете уалить основную валюту"));
    }

    $mysql->query("DELETE FROM " . prefix . "_eshop_payment_type WHERE id = {$id}");
    msg(array("type" => "info", "info" => "Способ оплаты удален"));
}

function list_payment_type()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_payment_type'), 'eshop', 1);

    $tVars = array();

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_payment_type ORDER BY position, id") as $row) {

        $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_payment_type&id=" . $row['id'] . "";
        $row['del_link'] = "?mod=extra-config&plugin=eshop&action=del_payment_type&id=" . $row['id'] . "";
        $tEntry[] = $row;
    }

    $xt = $twig->loadTemplate($tpath['config/list_payment_type'] . 'config/' . 'list_payment_type.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты',
    );

    print $xg->render($tVars);
}

function add_delivery_type()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_delivery_type'), 'eshop', 1);

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название не задано';
        }

        $SQL['description'] = secure_html(trim($_REQUEST['description']));

        $SQL['position'] = secure_html(trim($_REQUEST['position']));
        if ($SQL['position'] == "") {
            $SQL['position'] = 0;
        }
        $SQL['active'] = secure_html(trim($_REQUEST['active']));

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_delivery_type SET ' . implode(', ', $vnames) . ' ');

            msg(array('type' => 'info', 'info' => "Сопсоб доставки успешно добавлен!"));
            return list_delivery_type();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($SQL as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "add";

    $xt = $twig->loadTemplate($tpath['config/add_delivery_type'] . 'config/' . 'add_delivery_type.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );


    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Доставка: Добавление способа доставки',
    );

    print $xg->render($tVars);
}

function edit_delivery_type()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_delivery_type'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_delivery_type WHERE id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $SQL['name'] = secure_html(trim($_REQUEST['name']));
        if (empty($SQL['name'])) {
            $error_text[] = 'Название не задано';
        }

        $SQL['description'] = secure_html(trim($_REQUEST['description']));

        $SQL['position'] = secure_html(trim($_REQUEST['position']));
        if ($SQL['position'] == "") {
            $SQL['position'] = 0;
        }
        $SQL['active'] = secure_html(trim($_REQUEST['active']));

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('UPDATE ' . prefix . '_eshop_delivery_type SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$id . '\' ');

            msg(array('type' => 'info', 'info' => "Способ доставки успешно отредактирован!"));
            return list_delivery_type();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($row as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "edit";

    $xt = $twig->loadTemplate($tpath['config/add_delivery_type'] . 'config/' . 'add_delivery_type.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Доставка: Редактирование способа доставки',
    );

    print $xg->render($tVars);
}

function del_delivery_type()
{
    global $mysql;

    $id = (int)$_REQUEST['id'];

    if (empty($id)) {
        return msg(array("type" => "error", "text" => "Ошибка, вы не выбрали что хотите удалить"));
    }

    if ($id == "1") {
        return msg(array("type" => "error", "text" => "Ошибка, вы не можете уалить основную валюту"));
    }

    $mysql->query("DELETE FROM " . prefix . "_eshop_delivery_type WHERE id = {$id}");
    msg(array("type" => "info", "info" => "Способ оплаты удален"));
}

function list_delivery_type()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_delivery_type'), 'eshop', 1);

    $tVars = array();

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_delivery_type ORDER BY position, id") as $row) {

        $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_delivery_type&id=" . $row['id'] . "";
        $row['del_link'] = "?mod=extra-config&plugin=eshop&action=del_delivery_type&id=" . $row['id'] . "";
        $tEntry[] = $row;
    }

    $xt = $twig->loadTemplate($tpath['config/list_delivery_type'] . 'config/' . 'list_delivery_type.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Валюты',
    );

    print $xg->render($tVars);
}

function list_api()
{
    global $mysql, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_api'), 'eshop', 1);

    $tVars = array();

    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_api") as $row) {

        $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_api&id=" . $row['id'] . "";
        $row['del_link'] = "?mod=extra-config&plugin=eshop&action=del_api&id=" . $row['id'] . "";
        $tEntry[] = $row;
    }

    $xt = $twig->loadTemplate($tpath['config/list_api'] . 'config/' . 'list_api.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'API',
    );

    print $xg->render($tVars);
}

function add_api()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_api'), 'eshop', 1);

    if (isset($_REQUEST['submit'])) {

        $SQL['token'] = secure_html(trim($_REQUEST['token']));
        if (empty($SQL['token'])) {
            $error_text[] = 'Токен не задан';
        }

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('INSERT INTO ' . prefix . '_eshop_api SET ' . implode(', ', $vnames));
            msg(array('type' => 'info', 'info' => "Токен добавлен!"));
            return list_api();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($SQL as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "add";

    $xt = $twig->loadTemplate($tpath['config/add_api'] . 'config/' . 'add_api.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );


    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'API: Добавление токена',
    );

    print $xg->render($tVars);
}

function edit_api()
{
    global $mysql, $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/add_api'), 'eshop', 1);

    $id = (int)$_REQUEST['id'];
    $row = $mysql->record('SELECT * FROM ' . prefix . '_eshop_api WHERE id = ' . db_squote($id) . ' LIMIT 1');

    if (isset($_REQUEST['submit'])) {

        $SQL['token'] = secure_html(trim($_REQUEST['token']));
        if (empty($SQL['token'])) {
            $error_text[] = 'Токен не задан';
        }

        if (empty($error_text)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                $vnames[] = $k . ' = ' . db_squote($v);
            }
            $mysql->query('UPDATE ' . prefix . '_eshop_api SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$id . '\' ');

            msg(array('type' => 'info', 'info' => "Токен отредактирован!"));
            return list_api();
        }
    }

    $error_input = '';
    if (!empty($error_text)) {
        foreach ($error_text as $error) {
            $error_input .= msg(array("type" => "error", "text" => $error));
        }
    }

    foreach ($row as $k => $v) {
        $tEntry[$k] = $v;
    }

    $tEntry['error'] = $error_input;
    $tEntry['mode'] = "edit";

    $xt = $twig->loadTemplate($tpath['config/add_api'] . 'config/' . 'add_api.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'API: Редактирование токена',
    );

    print $xg->render($tVars);
}

function del_api()
{
    global $mysql;

    $id = (int)$_REQUEST['id'];

    if (empty($id)) {
        return msg(array("type" => "error", "text" => "Ошибка"));
    }

    $mysql->query("DELETE FROM " . prefix . "_eshop_api WHERE id = {$id}");
    msg(array("type" => "info", "info" => "Токен удален"));
}

function list_payment()
{
    global $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/list_payment'), 'eshop', 1);

    $tVars = array();

    if ($handle = opendir(__DIR__ . '/payment')) {
        $blacklist = array('.', '..');
        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, $blacklist)) {
                $row['name'] = $file;
                $row['edit_link'] = "?mod=extra-config&plugin=eshop&action=edit_payment&id=" . $file . "";
                $tEntry[] = $row;
            }
        }
        closedir($handle);
    }

    $xt = $twig->loadTemplate($tpath['config/list_payment'] . 'config/' . 'list_payment.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Системы оплаты',
    );

    print $xg->render($tVars);
}

function edit_payment()
{
    $tpath = locatePluginTemplates(array('config/main', 'config/add_currencies'), 'eshop', 1);

    $id = $_REQUEST['id'];
    $config_filename = __DIR__ . '/payment/' . $id . '/config.php';

    if (file_exists($config_filename)) {
        include_once($config_filename);
        payment_config($id);
    } else {
        $error = "Файл $config_filename не существует";
        msg(array("type" => "error", "text" => $error));
        return list_payment();
    }
}

function urls()
{
    global $twig;
    $tpath = locatePluginTemplates(array('config/main', 'config/urls'), 'eshop', 1);

    $url = pluginGetVariable('eshop', 'url');

    if (isset($_REQUEST['submit'])) {
        if (($url != '1') && ($_REQUEST['url'] == '1')) {
            create_urls();
        } elseif (($_REQUEST['url'] == '0')) {

            remove_urls();
        }

        pluginSetVariable('eshop', 'url', (int)$_REQUEST['url']);
        pluginsSaveConfig();
        msg(array('type' => 'info', 'info' => "Настройки успешно сохранены"));

        //redirect_eshop('?mod=extra-config&plugin=eshop&action=urls');
    }

    $url = '<option value="0" ' . (empty($url) ? 'selected' : '') . '>Нет</option><option value="1" ' . (!empty($url) ? 'selected' : '') . '>Да</option>';

    $xt = $twig->loadTemplate($tpath['config/urls'] . 'config/' . 'urls.tpl');

    $uVars = array(
        'info' => $url,
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($uVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Настройка ЧПУ',
    );

    print $xg->render($tVars);
}


function automation()
{
    global $mysql, $twig, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

    $tpath = locatePluginTemplates(array('config/main', 'config/automation'), 'eshop', 1);

    $yml_url = $_REQUEST['yml_url'];

    if (!empty($yml_url) && isset($_REQUEST['import'])) {

        $import_result = import_yml($yml_url);

        if ($import_result && is_array($import_result)) {
            // Формируем сообщение для toast
            $message = "Импорт завершен! ";
            if ($import_result['added'] > 0) {
                $message .= "Добавлено: " . $import_result['added'] . " ";
            }
            if ($import_result['updated'] > 0) {
                $message .= "Обновлено: " . $import_result['updated'] . " ";
            }
            if (!empty($import_result['warnings'])) {
                $message .= "Предупреждения: " . implode(', ', $import_result['warnings']);
            }

            $info = $message;
        } else {
            $info = "Импорт YML завершен";
        }

        msg(array("type" => "info", "info" => $info));
    }

    if (isset($_REQUEST['currency'])) {

        $rates_str = update_currency();
        $info = "Валюты обновлены<br/><br/>" . $rates_str;

        msg(array("type" => "info", "info" => $info));
    }

    if (isset($_REQUEST['change_price'])) {
        $change_price_type = (int)$_REQUEST['change_price_type'];
        $change_price_qnt = (int)$_REQUEST['change_price_qnt'];

        update_prices($change_price_type, $change_price_qnt);
        $info = "Цены обновлены<br/>";
        msg(array("type" => "info", "info" => $info));
    }

    if (isset($_REQUEST['export_csv'])) {

        $SUPRESS_TEMPLATE_SHOW = 1;
        $SUPRESS_MAINBLOCK_SHOW = 1;

        require_once __DIR__ . '/csv_lib/CsvImportInterface.php';
        require_once __DIR__ . '/csv_lib/CsvImport.php';
        require_once __DIR__ . '/csv_lib/CsvExportInterface.php';
        require_once __DIR__ . '/csv_lib/CsvExport.php';

        $export = new CsvExport();

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

        //$limitCount = "10000";

        //$fSort = " GROUP BY p.id ORDER BY p.id DESC ";
        $fSort = " ORDER BY p.id DESC ";
        $sqlQPart = "FROM " . prefix . "_eshop_products p LEFT JOIN " . prefix . "_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN " . prefix . "_eshop_categories c ON pc.category_id = c.id " . (count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '') . $fSort;
        $sqlQ = "SELECT p.id AS id, p.url AS url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.stocked AS stocked, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.name AS category " . $sqlQPart;

        $entries = array();

        foreach ($mysql->select($sqlQ) as $row) {

            $entriesImg = array();
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_images i WHERE i.product_id = ' . $row['id'] . ' ') as $row2) {
                $entriesImg[] = $row2['filepath'];
            }

            $entriesVariants = array();
            foreach ($mysql->select('SELECT * FROM ' . prefix . '_eshop_variants v WHERE v.product_id = ' . $row['id'] . ' ') as $vrow) {
                $entriesVariants[] = $vrow;
            }

            $options_array = array();
            foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_options LEFT JOIN " . prefix . "_eshop_features ON " . prefix . "_eshop_features.id=" . prefix . "_eshop_options.feature_id WHERE " . prefix . "_eshop_options.product_id = " . $row['id'] . " ORDER BY position, id") as $orow) {
                $options_array[$orow['id']] = $orow['value'];
            }

            $xf_name_id = array();
            $features_array = array();
            foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_features ORDER BY position, id") as $frow) {
                $frow['value'] = $options_array[$frow['id']];
                $frow['foptions'] = json_decode($frow['foptions'], true);
                foreach ($frow['foptions'] as $key => $value) {
                    $frow['foptions'][$key] = iconv("utf-8", "windows-1251", $value);
                }
                $features_array["xfields_" . $frow['name']] = $frow['value'];
                $xf_name_id[$frow['name']] = $frow['id'];
            }

            $images_comma_separated = implode(",", $entriesImg);

            $entry = array();

            $entry = array(
                'id' => $row['id'],
                'code' => $row['code'],
                'url' => $row['url'],
                'name' => $row['name'],

                'variants' => $entriesVariants,

                'annotation' => $row['annotation'],
                'body' => $row['body'],

                'active' => $row['active'],
                'featured' => $row['featured'],
                'stocked' => $row['stocked'],

                'meta_title' => $row['meta_title'],
                'meta_keywords' => $row['meta_keywords'],
                'meta_description' => $row['meta_description'],

                'date' => (empty($row['date'])) ? '' : $row['date'],
                'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],

                'cat_name' => $row['category'],
                'cid' => $row['cid'],

                'images' => $images_comma_separated,
            );

            foreach ($features_array as $fk => $fv) {
                $entry[$fk] = $fv;
            }

            $entries[] = $entry;
        }

        $count = 0;
        foreach ($entries as $entry) {
            foreach ($entry['variants'] as $variant) {

                $entry_row = array();
                $entry_row = $entry;
                unset($entry_row['variants']);

                $entry_row['v_id'] = $variant['id'];
                $entry_row['v_sku'] = $variant['sku'];
                $entry_row['v_name'] = $variant['name'];
                $entry_row['v_price'] = $variant['price'];
                $entry_row['v_compare_price'] = $variant['compare_price'];
                $entry_row['v_stock'] = $variant['stock'];
                $entry_row['v_amount'] = $variant['amount'];

                if ($count == 0) {
                    $export->setHeader(array_keys($entry_row));
                }
                $count += 1;

                $export->append(
                    array(
                        $entry_row,
                    )
                );

                unset($entry_row);
            }
        }

        $export->export('products.csv', ';');
        die();
    }

    if (isset($_REQUEST['import_csv'])) {

        $SUPRESS_TEMPLATE_SHOW = 1;
        $SUPRESS_MAINBLOCK_SHOW = 1;

        if (is_uploaded_file($_FILES["filename"]["tmp_name"])) {

            require_once __DIR__ . '/csv_lib/CsvImportInterface.php';
            require_once __DIR__ . '/csv_lib/CsvImport.php';
            require_once __DIR__ . '/csv_lib/CsvExportInterface.php';
            require_once __DIR__ . '/csv_lib/CsvExport.php';

            $import = new CsvImport();

            $filepath = __DIR__ . "/import/" . $_FILES["filename"]["name"];
            move_uploaded_file($_FILES["filename"]["tmp_name"], $filepath);
            $import->setFile($filepath, 10000, ";");
            $items = $import->getRows();

            // Инициализируем счетчики для статистики
            $csv_stats = array(
                'added' => 0,
                'updated' => 0,
                'total' => count($items)
            );

            $xf_name_id = array();
            foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_features ORDER BY position, id") as $frow) {
                $xf_name_id[$frow['name']] = $frow['id'];
            }

            foreach ($items as $iv) {

                $current_time = time();

                $id = $iv['id'];
                $code = empty($iv['code']) ? "" : $iv['code'];
                $url = empty($iv['url']) ? "" : $iv['url'];
                $name = empty($iv['name']) ? "" : $iv['name'];

                $v_id = empty($iv['v_id']) ? "" : $iv['v_id'];
                $v_sku = empty($iv['v_sku']) ? "" : $iv['v_sku'];
                $v_name = empty($iv['v_name']) ? "" : $iv['v_name'];
                $v_price = empty($iv['v_price']) ? "" : $iv['v_price'];
                $v_compare_price = empty($iv['v_compare_price']) ? "" : $iv['v_compare_price'];
                $v_stock = empty($iv['v_stock']) ? "" : $iv['v_stock'];
                $v_amount = empty($iv['v_amount']) ? "" : $iv['v_amount'];

                if ($v_amount == '') {
                    $v_amount = 'NULL';
                }

                $annotation = empty($iv['annotation']) ? "" : $iv['annotation'];
                $body = empty($iv['body']) ? "" : $iv['body'];
                $active = empty($iv['active']) ? "1" : $iv['active'];
                $featured = empty($iv['featured']) ? "0" : $iv['featured'];
                $stocked = empty($iv['stocked']) ? "0" : $iv['stocked'];
                $meta_title = empty($iv['meta_title']) ? "" : $iv['meta_title'];
                $meta_keywords = empty($iv['meta_keywords']) ? "" : $iv['meta_keywords'];
                $meta_description = empty($iv['meta_description']) ? "" : $iv['meta_description'];
                $date = empty($iv['date']) ? $current_time : $iv['date'];
                $editdate = empty($iv['editdate']) ? $current_time : $iv['editdate'];
                $cat_name = empty($iv['cat_name']) ? "" : $iv['cat_name'];
                $cid = empty($iv['cid']) ? "0" : $iv['cid'];
                $images = explode(',', $iv['images']);

                $xfields_items = array();
                foreach ($iv as $xfk => $xfv) {
                    preg_match("/xfields_/", $xfk, $find_xf);
                    if (isset($find_xf[0])) {
                        $xf_name = str_replace('xfields_', '', $xfk);
                        $xf_id = $xf_name_id[$xf_name];
                        $xfields_items[$xf_id] = $xfv;
                    }
                }

                if ($iv['id'] != "") {

                    $product_row = $mysql->record("SELECT * FROM " . prefix . "_eshop_products WHERE id=" . db_squote($iv['id']) . " ");

                    if (empty($product_row)) {
                        if ($url != "") {
                            if (!is_array(
                                $mysql->record("SELECT id FROM " . prefix . "_eshop_products WHERE url = " . db_squote($product_row["url"]) . " LIMIT 1")
                            )) {
                                $mysql->query("INSERT INTO " . prefix . "_eshop_products (`id`, `code`, `url`, `name`, `annotation`, `body`, `active`, `featured`, `stocked`, `meta_title`, `meta_keywords`, `meta_description`, `date`, `editdate`) VALUES ('$id','$code','$url','$name','$annotation','$body','$active','$featured','$stocked','$meta_title','$meta_keywords','$meta_description','$date','$editdate')");

                                $qid = lastid('eshop_products');
                                $csv_stats['added']++;

                                import_upload_images($qid);

                                if (!empty($xfields_items)) {
                                    foreach ($xfields_items as $f_key => $f_value) {
                                        if ($f_value != '') {
                                            $mysql->query("INSERT INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')");
                                        }
                                    }
                                }

                                $category_id = (int)$cid;

                                if ($category_id != 0) {
                                    $mysql->query("INSERT INTO " . prefix . "_eshop_products_categories (`product_id`, `category_id`) VALUES ('$qid','$category_id')");
                                }

                                $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `sku`, `name`, `price`, `compare_price`, `stock`, `amount`) VALUES ('$qid', '$v_sku', '$v_name', '$v_price', '$v_compare_price', '$v_stock', '$v_amount')");
                            }
                        }
                    } else {
                        if ($product_row["url"]) {
                            if (is_array(
                                $mysql->record("SELECT id FROM " . prefix . "_eshop_products WHERE url = " . db_squote($url) . " LIMIT 1")
                            )) {
                                $mysql->query("REPLACE INTO " . prefix . "_eshop_products (`id`, `code`, `url`, `name`, `annotation`, `body`, `active`, `featured`, `stocked`, `meta_title`, `meta_keywords`, `meta_description`, `date`, `editdate`) VALUES ('$id','$code','$url','$name','$annotation','$body','$active','$featured','$stocked','$meta_title','$meta_keywords','$meta_description','$date','$editdate')");

                                $qid = $product_row["id"];
                                $csv_stats['updated']++;

                                import_upload_images($qid);

                                if (!empty($xfields_items)) {
                                    //$mysql->query("DELETE FROM ".prefix."_eshop_options WHERE product_id='$qid'");
                                    foreach ($xfields_items as $f_key => $f_value) {
                                        if ($f_value != '') {
                                            $mysql->query("REPLACE INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')");
                                        }
                                    }
                                }

                                $category_id = (int)$cid;

                                if ($category_id != 0) {
                                    //$mysql->query("DELETE FROM ".prefix."_eshop_products_categories WHERE product_id='$qid'");
                                    $mysql->query("REPLACE INTO " . prefix . "_eshop_products_categories (`product_id`, `category_id`) VALUES ('$qid','$category_id')");
                                } else {
                                    $mysql->query("DELETE FROM " . prefix . "_eshop_products_categories WHERE product_id='$qid'");
                                }

                                $SQLv = array();
                                $SQLv['product_id'] = $qid;
                                $SQLv['sku'] = $v_sku;
                                $SQLv['name'] = $v_name;
                                $SQLv['price'] = $v_price;
                                $SQLv['compare_price'] = $v_compare_price;
                                $SQLv['stock'] = $v_stock;
                                $SQLv['amount'] = $v_amount;

                                foreach ($SQLv as $k => $v) {
                                    $vnames[] = $k . ' = ' . db_squote($v);
                                }

                                if ($v_id != "") {
                                    $mysql->query('UPDATE ' . prefix . '_eshop_variants SET ' . implode(', ', $vnames) . ' WHERE id = \'' . (int)$v_id . '\'  ');
                                } else {
                                    $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `sku`, `name`, `price`, `compare_price`, `stock`, `amount`) VALUES ('$qid', '$v_sku', '$v_name', '$v_price', '$v_compare_price', '$v_stock', '$v_amount')");
                                }

                                //$mysql->query("DELETE FROM ".prefix."_eshop_variants WHERE product_id='$qid'");
                                //$mysql->query("INSERT INTO ".prefix."_eshop_variants (`product_id`, `price`, `compare_price`, `stock`) VALUES ('$qid', '$price', '$compare_price', '$stock')");

                            }
                        }
                    }
                } else {

                    $mysql->query("INSERT INTO " . prefix . "_eshop_products (`code`, `url`, `name`, `annotation`, `body`, `active`, `featured`, `stocked`, `meta_title`, `meta_keywords`, `meta_description`, `date`, `editdate`) VALUES ('$code','$url','$name','$annotation','$body','$active','$featured','$stocked','$meta_title','$meta_keywords','$meta_description','$date','$editdate')");

                    $qid = lastid('eshop_products');
                    $csv_stats['added']++;

                    import_upload_images($qid);

                    if (!empty($xfields_items)) {
                        foreach ($xfields_items as $f_key => $f_value) {
                            if ($f_value != '') {
                                $mysql->query("INSERT INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')");
                            }
                        }
                    }

                    $category_id = (int)$cid;

                    if ($category_id != 0) {
                        $mysql->query("INSERT INTO " . prefix . "_eshop_products_categories (`product_id`, `category_id`) VALUES ('$qid','$category_id')");
                    }

                    $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `sku`, `name`, `price`, `compare_price`, `stock`, `amount`) VALUES ('$qid', '$v_sku', '$v_name', '$v_price', '$v_compare_price', '$v_stock', '$v_amount')");
                }
            }

            unlink($filepath);

            // Формируем сообщение со статистикой
            $info = "Импорт CSV завершен! ";
            if (isset($csv_stats)) {
                $info .= "Обработано товаров: " . $csv_stats['total'];
                if ($csv_stats['added'] > 0) {
                    $info .= ", добавлено: " . $csv_stats['added'];
                }
                if ($csv_stats['updated'] > 0) {
                    $info .= ", обновлено: " . $csv_stats['updated'];
                }
            }

            msg(array("type" => "info", "info" => $info));
            die();
        } else {
            $info = "Ошибка загрузки файла<br/>";
            msg(array("type" => "info", "info" => $info));
            die();
        }
    }

    if (isset($_REQUEST['multiple_upload_images'])) {

        $images = $_REQUEST['data']['images'];

        if ($images != null) {

            foreach ($images as $inx_img => $img) {

                $img_parts = explode(".", $img);
                $img_s = explode("_", $img_parts[0]);
                $qid = $img_s[0];
                //$qid = lastid('eshop_products');

                if ($qid != "") {

                    $prd_row = $mysql->record("SELECT * FROM " . prefix . "_eshop_products WHERE id = " . db_squote($qid) . " LIMIT 1");
                    if (!is_array($prd_row)) {
                        break;
                    }

                    $positions_img = array();
                    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_images WHERE product_id = '$qid' ORDER BY position, id") as $irow) {
                        $positions_img[] = $irow['position'];
                    }

                    if (!empty($positions_img)) {
                        $max_img_pos = max($positions_img) + 1;
                    } else {
                        $max_img_pos = 0;
                    }

                    $inx_img = $max_img_pos;

                    makeUploadsDirs('/eshop/products/' . $qid . '/');

                    $timestamp = time();
                    $iname = $timestamp . "-" . $img;

                    $productsTempPath = '/eshop/products/';
                    moveFromTemp($qid, $productsTempPath, $img, $iname);

                    $mysql->query("INSERT INTO " . prefix . "_eshop_images (`filepath`, `product_id`, `position`) VALUES ('$iname','$qid','$inx_img')");
                }
            }
        }

        $info .= "Изображения загружены";
        msg(array("type" => "info", "info" => $info));
    }

    $xt = $twig->loadTemplate($tpath['config/automation'] . 'config/' . 'automation.tpl');

    $yml_export_link = checkLinkAvailable('eshop', 'yml_export') ?
        generateLink('eshop', 'yml_export', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'yml_export'), array());

    $tVars = array(
        'yml_export_link' => $yml_export_link,
        'info' => '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Автоматизация',
    );

    print $xg->render($tVars);
}


function import_upload_images($qid)
{
    global $mysql, $config;

    $positions_img = array();
    foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_images WHERE product_id = '$qid' ORDER BY position, id") as $irow) {
        $positions_img[] = $irow['position'];
    }

    if (!empty($positions_img)) {
        $max_img_pos = max($positions_img) + 1;
    } else {
        $max_img_pos = 0;
    }

    $inx_img = $max_img_pos;

    $uploadsDir = dirname($config['images_dir']);
    $img_dir = __DIR__ . "/import/images/" . $qid . "/";
    $images = array_map('basename', glob($img_dir . "*.{jpg,png,gif}", GLOB_BRACE));

    if (!empty($images)) {

        foreach ($images as $name) {

            $file_path = $uploadsDir . "/eshop/products/temp/" . $name;
            rename($img_dir . $name, $file_path);
            $fileParts = pathinfo($file_path);
            $extension = $fileParts['extension'];

            $extensions = array_map('trim', explode(',', pluginGetVariable('eshop', 'ext_image')));

            $pre_quality = pluginGetVariable('eshop', 'pre_quality');

            if (!in_array($extension, $extensions)) {
                return "0";
            }

            // CREATE THUMBNAIL
            if ($extension == "jpg" || $extension == "jpeg") {
                $src = imagecreatefromjpeg($file_path);
            } else {
                if ($extension == "png") {
                    $src = imagecreatefrompng($file_path);
                } else {
                    $src = imagecreatefromgif($file_path);
                }
            }

            list($width, $height) = getimagesize($file_path);

            $newwidth = pluginGetVariable('eshop', 'width_thumb');

            if ($width > $newwidth) {
                $newheight = ($height / $width) * $newwidth;
                $tmp = imagecreatetruecolor($newwidth, $newheight);

                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                $thumbname = $uploadsDir . "/eshop/products/temp/thumb/" . $name;

                if (file_exists($thumbname)) {
                    unlink($thumbname);
                }

                imagejpeg($tmp, $thumbname, ($pre_quality >= 10 && $pre_quality <= 100) ? $pre_quality : 100);

                imagedestroy($src);
                imagedestroy($tmp);
            } else {
                if ($extension == "jpg" || $extension == "jpeg") {
                    $src = imagecreatefromjpeg($file_path);
                } else {
                    if ($extension == "png") {
                        $src = imagecreatefrompng($file_path);
                    } else {
                        $src = imagecreatefromgif($file_path);
                    }
                }
                imagejpeg($src, $file_path, ($pre_quality >= 10 && $pre_quality <= 100) ? $pre_quality : 100);
                $thumbname = $uploadsDir . "/eshop/products/temp/thumb/" . $name;
                copy($file_path, $thumbname);

                imagedestroy($src);
            }

            $newwidth = pluginGetVariable('eshop', 'pre_width');
            if (isset($newwidth) && ($newwidth != '0')) {

                if ($extension == "jpg" || $extension == "jpeg") {
                    $src = imagecreatefromjpeg($file_path);
                } else {
                    if ($extension == "png") {
                        $src = imagecreatefrompng($file_path);
                    } else {
                        $src = imagecreatefromgif($file_path);
                    }
                }

                list($width, $height) = getimagesize($file_path);
                $newheight = ($height / $width) * $newwidth;
                $tmp = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                $thumbname = $file_path;

                imagejpeg($tmp, $thumbname, ($pre_quality >= 10 && $pre_quality <= 100) ? $pre_quality : 100);

                imagedestroy($src);
                imagedestroy($tmp);
            }

            $img = $name;

            $timestamp = time();
            $iname = $timestamp . "-" . $img;

            makeUploadsDirs('/eshop/products/' . $qid . '/');

            $productsTempPath = '/eshop/products/';
            moveFromTemp($qid, $productsTempPath, $img, $iname);

            $mysql->query("INSERT INTO " . prefix . "_eshop_images (`filepath`, `product_id`, `position`) VALUES ('$iname','$qid','$inx_img')");

            $inx_img += 1;
        }
    }
}

function options()
{
    global $cron, $twig;

    $tpath = locatePluginTemplates(array('config/main', 'config/general.from'), 'eshop', 1);

    $tVars = array();

    if (isset($_REQUEST['submit'])) {
        pluginSetVariable('eshop', 'localsource', (int)$_REQUEST['localsource']);
        pluginSetVariable('eshop', 'localskin', secure_html(trim($_REQUEST['localskin'])));

        pluginSetVariable('eshop', 'mysql', (int)$_REQUEST['mysql']);
        pluginSetVariable('eshop', 'cat_tree', (int)$_REQUEST['cat_tree']);
        pluginSetVariable('eshop', 'count', (int)$_REQUEST['count']);
        pluginSetVariable('eshop', 'count_search', secure_html($_REQUEST['count_search']));
        pluginSetVariable('eshop', 'count_stocks', secure_html($_REQUEST['count_stocks']));

        pluginSetVariable('eshop', 'views_count', $_REQUEST['views_count']);
        pluginSetVariable('eshop', 'bidirect_linked_products', $_REQUEST['bidirect_linked_products']);
        pluginSetVariable('eshop', 'approve_comments', $_REQUEST['approve_comments']);
        pluginSetVariable('eshop', 'sort_comments', $_REQUEST['sort_comments']);
        pluginSetVariable('eshop', 'integrate_gsmg', $_REQUEST['integrate_gsmg']);

        pluginSetVariable('eshop', 'max_image_size', (int)$_REQUEST['max_image_size']);
        pluginSetVariable('eshop', 'width_thumb', (int)$_REQUEST['width_thumb']);
        pluginSetVariable('eshop', 'width', (int)$_REQUEST['width']);
        pluginSetVariable('eshop', 'height', (int)$_REQUEST['height']);

        pluginSetVariable('eshop', 'ext_image', check_php_str($_REQUEST['ext_image']));

        pluginSetVariable('eshop', 'pre_width', (int)$_REQUEST['pre_width']);
        pluginSetVariable('eshop', 'pre_quality', (int)$_REQUEST['pre_quality']);

        pluginSetVariable('eshop', 'catz_max_image_size', (int)$_REQUEST['catz_max_image_size']);
        pluginSetVariable('eshop', 'catz_width_thumb', (int)$_REQUEST['catz_width_thumb']);
        pluginSetVariable('eshop', 'catz_width', (int)$_REQUEST['catz_width']);
        pluginSetVariable('eshop', 'catz_height', (int)$_REQUEST['catz_height']);
        pluginSetVariable('eshop', 'catz_ext_image', check_php_str($_REQUEST['catz_ext_image']));

        pluginSetVariable('eshop', 'email_notify_orders', $_REQUEST['email_notify_orders']);
        pluginSetVariable('eshop', 'email_notify_comments', $_REQUEST['email_notify_comments']);
        pluginSetVariable('eshop', 'email_notify_back', $_REQUEST['email_notify_back']);

        pluginSetVariable('eshop', 'description_delivery', $_REQUEST['description_delivery']);
        pluginSetVariable('eshop', 'description_order', $_REQUEST['description_order']);
        pluginSetVariable('eshop', 'description_phones', $_REQUEST['description_phones']);

        pluginsSaveConfig();
        msg(array('type' => 'info', 'info' => "Настройки успешно сохранены"));

        //redirect_eshop('?mod=extra-config&plugin=eshop&action=options');
    }

    $views_cnt = (int)pluginGetVariable('eshop', 'views_count');

    if ($views_cnt == 2) {

        $cron_row = $cron->getConfig();
        foreach ($cron_row as $key => $value) {
            if (($value['plugin'] == 'eshop') && ($value['handler'] == 'eshop_views')) {
                $cron_min = $value['min'];
                $cron_hour = $value['hour'];
                $cron_day = $value['day'];
                $cron_month = $value['month'];
            }
        }
        if (!isset($cron_min)) {
            $cron_min = '0,15,30,45';
        }
        if (!isset($cron_hour)) {
            $cron_hour = '*';
        }
        if (!isset($cron_day)) {
            $cron_day = '*';
        }
        if (!isset($cron_month)) {
            $cron_month = '*';
        }

        $cron->unregisterTask('eshop', 'eshop_views');
        $cron->registerTask('eshop', 'eshop_views', $cron_min, $cron_hour, $cron_day, $cron_month, '*');
    } else {
        $cron->unregisterTask('eshop', 'eshop_views');
    }

    $count = pluginGetVariable('eshop', 'count');
    $count_search = pluginGetVariable('eshop', 'count_search');
    $count_stocks = pluginGetVariable('eshop', 'count_stocks');

    $views_count = pluginGetVariable('eshop', 'views_count');
    $views_count = '<option value="0" ' . ($views_count == 0 ? 'selected' : '') . '>Нет</option><option value="1" ' . ($views_count == 1 ? 'selected' : '') . '>Да</option><option value="2" ' . ($views_count == 2 ? 'selected' : '') . '>Отложенное</option>';

    $bidirect_linked_products = pluginGetVariable('eshop', 'bidirect_linked_products');
    $bidirect_linked_products = '<option value="0" ' . ($bidirect_linked_products == 0 ? 'selected' : '') . '>Нет</option><option value="1" ' . ($bidirect_linked_products == 1 ? 'selected' : '') . '>Да</option>';

    $approve_comments = pluginGetVariable('eshop', 'approve_comments');
    $approve_comments = '<option value="0" ' . ($approve_comments == 0 ? 'selected' : '') . '>Нет</option><option value="1" ' . ($approve_comments == 1 ? 'selected' : '') . '>Да</option>';

    $sort_comments = pluginGetVariable('eshop', 'sort_comments');
    $sort_comments = '<option value="0" ' . ($sort_comments == 0 ? 'selected' : '') . '>Новые снизу</option><option value="1" ' . ($sort_comments == 1 ? 'selected' : '') . '>Новые сверху</option>';

    $integrate_gsmg = pluginGetVariable('eshop', 'integrate_gsmg');
    $integrate_gsmg = '<option value="0" ' . ($integrate_gsmg == 0 ? 'selected' : '') . '>Нет</option><option value="1" ' . ($integrate_gsmg == 1 ? 'selected' : '') . '>Да</option>';

    $max_image_size = pluginGetVariable('eshop', 'max_image_size');
    $width_thumb = pluginGetVariable('eshop', 'width_thumb');
    $width = pluginGetVariable('eshop', 'width');
    $height = pluginGetVariable('eshop', 'height');
    $ext_image = pluginGetVariable('eshop', 'ext_image');

    $pre_width = pluginGetVariable('eshop', 'pre_width');
    $pre_quality = pluginGetVariable('eshop', 'pre_quality');

    $catz_max_image_size = pluginGetVariable('eshop', 'catz_max_image_size');
    $catz_width_thumb = pluginGetVariable('eshop', 'catz_width_thumb');
    $catz_width = pluginGetVariable('eshop', 'catz_width');
    $catz_height = pluginGetVariable('eshop', 'catz_height');
    $catz_ext_image = pluginGetVariable('eshop', 'catz_ext_image');

    $email_notify_orders = pluginGetVariable('eshop', 'email_notify_orders');
    $email_notify_comments = pluginGetVariable('eshop', 'email_notify_comments');
    $email_notify_back = pluginGetVariable('eshop', 'email_notify_back');

    $description_delivery = pluginGetVariable('eshop', 'description_delivery');
    $description_order = pluginGetVariable('eshop', 'description_order');
    $description_phones = pluginGetVariable('eshop', 'description_phones');

    if (pluginGetVariable('eshop', 'localsource'))
        $localskin = extras_dir . '/eshop/tpl/skins/';
    else {
        if (file_exists(tpl_site . 'plugins/eshop/skins/'))
            $localskin = tpl_site . 'plugins/eshop/skins/';
        else {
            $localskin = extras_dir . '/eshop/tpl/skins/';
            //msg(array("type" => "error", "text" => 'Ошибка поддержки скина. Надо выбрать шаблон: Плагин. Чтобы использовать предустановленные скины eshop'));
        }
    }

    $tEntry = array(
        'localsource'   => MakeDropDown(array(0 => 'Шаблон сайта', 1 => 'Плагина'), 'localsource', (int)pluginGetVariable('eshop', 'localsource')),
        'localskin'     => MakeDropDown(ListFiles($localskin, ''), 'localskin', pluginGetVariable('eshop', 'localskin')),
        'mysql'           => MakeDropDown(array(0 => 'Нет', 1 => 'Да'), 'mysql', (int)pluginGetVariable('eshop', 'mysql')),
        'cat_tree'           => MakeDropDown(array(0 => 'Нет', 1 => 'Да'), 'cat_tree', (int)pluginGetVariable('eshop', 'cat_tree')),
        'count'         => $count,
        'count_search'     => $count_search,
        'count_stocks'     => $count_stocks,

        'views_count'     => $views_count,
        'bidirect_linked_products' => $bidirect_linked_products,

        'approve_comments' => $approve_comments,
        'sort_comments' => $sort_comments,
        'integrate_gsmg' => $integrate_gsmg,

        'max_image_size' => $max_image_size,
        'width_thumb' => $width_thumb,
        'width' => $width,
        'height' => $height,
        'ext_image' => $ext_image,

        'pre_width' => $pre_width,
        'pre_quality' => $pre_quality,

        'catz_max_image_size' => $catz_max_image_size,
        'catz_width_thumb' => $catz_width_thumb,
        'catz_width' => $catz_width,
        'catz_height' => $catz_height,
        'catz_ext_image' => $catz_ext_image,

        'email_notify_orders' => $email_notify_orders,
        'email_notify_comments' => $email_notify_comments,
        'email_notify_back' => $email_notify_back,

        'description_delivery' => $description_delivery,
        'description_order' => $description_order,
        'description_phones' => $description_phones,

    );

    $xt = $twig->loadTemplate($tpath['config/general.from'] . 'config/' . 'general.from.tpl');

    $tVars = array(
        'entries' => isset($tEntry) ? $tEntry : '',
    );

    $xg = $twig->loadTemplate($tpath['config/main'] . 'config/' . 'main.tpl');

    $tVars = array(
        'entries' => $xt->render($tVars),
        'php_self' => $_SERVER['PHP_SELF'],
        'plugin_url' => admin_url . '/admin.php?mod=extra-config&plugin=eshop',
        'skins_url' => skins_url,
        'admin_url' => admin_url,
        'home' => home,
        'current_title' => 'Настройки',
    );

    print $xg->render($tVars);
}

function redirect_eshop($url)
{
    if (headers_sent()) {
        echo "<script>document.location.href='{$url}';</script>\n";
        exit;
    } else {
        header('HTTP/1.1 302 Moved Permanently');
        header("Location: {$url}");
        exit;
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
