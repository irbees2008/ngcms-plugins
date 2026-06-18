<?php

// Protect against hack attempts
if (!defined('NGCMS')) {
    die ('HAL');
}

include_once(__DIR__.'/functions.php');

//
// Configuration file for plugin
//

pluginsLoadConfig();

$db_update = array(
    array(
        'table' => 'eshop_products',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_products_comments',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_products_likes',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_products_view',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_features',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_options',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_related_products',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_categories',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_products_categories',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_categories_features',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_brands',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_purchases',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_orders',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_order_basket',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_compare',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_variants',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_images',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_currencies',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_ebasket',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_payment',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_api',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_payment_type',
        'action' => 'drop',
    ),
    array(
        'table' => 'eshop_delivery_type',
        'action' => 'drop',
    ),
);

if ($_REQUEST['action'] == 'commit') {
    if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
        $uploadsDir = getUploadsDir();
        deleteEshopUploadDirs($uploadsDir.'/eshop/');
        remove_urls();
        plugin_mark_deinstalled($plugin);
    }
} else {
    generate_install_page($plugin, 'Удаление плагина', 'deinstall');
}


function deleteEshopUploadDirs($path)
{
    if (empty($path)) {
        return false;
    }

    return is_file($path) ?
        @unlink($path) :
        array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

?>
