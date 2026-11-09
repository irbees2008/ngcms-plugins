<?php

// Configuration file for plugin

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

// Load lang files
loadPluginLang($plugin, 'admin', '', '', ':');

$db_update = [
    [
        'table' => 'images',
        'action' => 'modify',
        'fields' => [
            ['action' => 'drop', 'name' => 'com'],
            ['action' => 'drop', 'name' => 'views'],
        ],
    ],
    [
        'table' => 'gallery',
        'action' => 'drop',
    ],
];

// Delete comments for gallery images before removing tables
if ('commit' == $action && getPluginStatusActive('comments')) {
    // Get all image IDs
    $imageIds = $mysql->select("SELECT id FROM " . prefix . "_images WHERE folder != ''");
    if (is_array($imageIds) && count($imageIds) > 0) {
        $ids = array_map(function ($row) {
            return (int)$row['id'];
        }, $imageIds);
        if (!empty($ids)) {
            // Delete all comments for gallery images in one query
            $mysql->query("DELETE FROM " . prefix . "_comments WHERE module='images' AND id IN (" . implode(',', $ids) . ")");
        }
    }
}

if ('commit' == $action) {
    if (fixdb_plugin_install('gallery', $db_update, 'deinstall')) {
        $ULIB = new UrlLibrary();
        $ULIB->loadConfig();
        $ULIB->removeCommand('gallery', 'image');
        $ULIB->removeCommand('gallery', 'gallery');
        $ULIB->removeCommand('gallery', '');

        $UHANDLER = new UrlHandler();
        $UHANDLER->loadConfig();
        $UHANDLER->removePluginHandlers('gallery', 'image');
        $UHANDLER->removePluginHandlers('gallery', 'gallery');
        $UHANDLER->removePluginHandlers('gallery', '');

        // Save configuration parameters of plugins
        pluginsSaveConfig();
        $ULIB->saveConfig();
        $UHANDLER->saveConfig();

        plugin_mark_deinstalled('gallery');
    }
} else {
    generate_install_page('gallery', $lang['gallery:desc_deinstall'], 'deinstall');
}
