<?php

// Protect against hack attempts
if (!defined('NGCMS')) {
    die('HAL');
}

/**
 * ng-helpers - Коллекция вспомогательных функций для плагинов NGCMS
 * @version 0.2.1
 * @author https://github.com/russsiq
 */

// Define plugin version
define('NG_HELPERS_VERSION', '0.2.1');

// Load helper functions
$helpersFile = __DIR__ . '/src/helpers.php';

if (file_exists($helpersFile)) {
    require_once $helpersFile;
} else {
    // Fallback to old location (for backward compatibility)
    $oldFile = __DIR__ . '/ng-helpers.php';
    if (file_exists($oldFile) && $oldFile !== __FILE__) {
        require_once $oldFile;
    } else {
        // Log error if helper file not found
        if (function_exists('msg')) {
            msg(array(
                'type' => 'error',
                'message' => 'ng-helpers: Cannot load helper functions file'
            ));
        }
    }
}

// Register plugin information
function plugin_ng_helpers_info()
{
    return array(
        'name'        => 'ng-helpers',
        'title'       => 'NG Helpers',
        'description' => 'Коллекция вспомогательных функций для плагинов NGCMS',
        'version'     => NG_HELPERS_VERSION,
        'author'      => 'russsiq',
        'url'         => 'https://github.com/russsiq/ng-helpers',
    );
}

// No action handlers needed - this is a library plugin
