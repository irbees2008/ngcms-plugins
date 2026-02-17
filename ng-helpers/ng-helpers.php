<?php

// Protect against hack attempts
if (!defined('NGCMS')) {
    die('HAL');
}

/**
 * ng-helpers - Коллекция вспомогательных функций для плагинов NGCMS
 * @version 0.2.2
 * @author https://github.com/russsiq
 */

// Define plugin version
define('NG_HELPERS_VERSION', '0.2.2');

// Register autoloader for ng-helpers classes and traits
spl_autoload_register(function ($class) {
    // Check if class belongs to Plugins\NgHelpers namespace
    $prefix = 'Plugins\\NgHelpers\\';
    $base_dir = __DIR__ . '/src/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    // and append .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

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
