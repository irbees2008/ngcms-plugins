<?php

// Защита от попыток взлома.
if (!defined('NGCMS')) {
    die('HAL');
}

// Autoload классов плагина ng-captcha
spl_autoload_register(function ($class) {
    $prefix = 'Plugins\\Captcha\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
