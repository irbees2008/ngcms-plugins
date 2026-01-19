<?php

// Protect against hack attempts
if (!defined('NGCMS')) {
    die('HAL');
}

/**
 * Helper function to delete image comments when image is deleted
 *
 * @param int|array $imageIds Single image ID or array of image IDs
 * @return int Number of deleted comments
 */
function gallery_delete_comments($imageIds)
{
    if (!getPluginStatusActive('comments')) {
        return 0;
    }

    global $mysql;

    // Convert single ID to array
    if (!is_array($imageIds)) {
        $imageIds = [$imageIds];
    }

    // Sanitize IDs
    $imageIds = array_map('intval', $imageIds);

    if (empty($imageIds)) {
        return 0;
    }

    // Delete comments in one query
    $stmt = $mysql->query(
        'DELETE FROM ' . prefix . '_comments ' .
            'WHERE module="images" AND id IN (' . implode(',', $imageIds) . ')'
    );

    $deletedCount = $mysql->affected_rows($stmt);

    if (function_exists('Plugins\logger')) {
        \Plugins\logger('gallery', 'Deleted ' . $deletedCount . ' comments for images: ' . implode(',', $imageIds));
    }

    return $deletedCount;
}

/**
 * Register CSS and JS files for gallery skin
 * Automatically includes skin.css and skin.js if they exist
 *
 * @param string $skin Skin name (e.g., 'default', 'lightbox')
 * @param string $type Template type ('category', 'page_gallery', 'page_image', 'widget', 'page_index')
 * @return void
 */
function gallery_register_skin_assets($skin = '', $type = '')
{
    global $template;

    if (empty($skin)) {
        $skin = pluginGetVariable('gallery', 'skin') ?: 'default';
    }

    if (empty($type)) {
        return;
    }

    // Get current theme name
    $themeName = isset($template['theme']) ? $template['theme'] : 'default';

    // Priority 1: Template-specific skin files
    $templateDir = root . 'templates/' . $themeName . '/plugins/gallery/' . $type . '/' . $skin;
    $templateCssFile = $templateDir . '/skin.css';
    $templateJsFile = $templateDir . '/skin.js';

    // Priority 2: Plugin default skin files
    $pluginDir = root . 'engine/plugins/gallery/tpl/' . $type . '/' . $skin;
    $pluginCssFile = $pluginDir . '/skin.css';
    $pluginJsFile = $pluginDir . '/skin.js';

    // Initialize arrays if not exist
    if (!isset($template['regCss'])) {
        $template['regCss'] = [];
    }
    if (!isset($template['regHead'])) {
        $template['regHead'] = [];
    }

    // Check and register CSS
    if (file_exists($templateCssFile)) {
        $cssUrl = home . 'templates/' . $themeName . '/plugins/gallery/' . $type . '/' . $skin . '/skin.css';
        if (!in_array($cssUrl, $template['regCss'])) {
            $template['regCss'][] = $cssUrl;
        }
    } elseif (file_exists($pluginCssFile)) {
        $cssUrl = home . 'engine/plugins/gallery/tpl/' . $type . '/' . $skin . '/skin.css';
        if (!in_array($cssUrl, $template['regCss'])) {
            $template['regCss'][] = $cssUrl;
        }
    }

    // Check and register JS
    if (file_exists($templateJsFile)) {
        $jsUrl = home . 'templates/' . $themeName . '/plugins/gallery/' . $type . '/' . $skin . '/skin.js';
        $jsTag = '<script src="' . $jsUrl . '"></script>';
        if (!in_array($jsTag, $template['regHead'])) {
            $template['regHead'][] = $jsTag;
        }
    } elseif (file_exists($pluginJsFile)) {
        $jsUrl = home . 'engine/plugins/gallery/tpl/' . $type . '/' . $skin . '/skin.js';
        $jsTag = '<script src="' . $jsUrl . '"></script>';
        if (!in_array($jsTag, $template['regHead'])) {
            $template['regHead'][] = $jsTag;
        }
    }
}
