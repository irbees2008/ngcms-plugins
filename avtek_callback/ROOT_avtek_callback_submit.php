<?php
// Скопируйте этот файл в корень сайта (рядом с index.php) и переименуйте, например, в avtek_callback_submit.php
// Это даёт максимально простой URL без роутера CMS.

@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

if (!defined('NGCMS')) {
    define('NGCMS', true);
}

$root = __DIR__;
if (file_exists($root . '/engine/core.php')) {
    require_once $root . '/engine/core.php';
} elseif (file_exists($root . '/engine/init.php')) {
    require_once $root . '/engine/init.php';
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'NGCMS core not found']);
    exit;
}

$pluginFile = $root . '/engine/plugins/avtek_callback/functions.php';
if (!file_exists($pluginFile)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'avtek_callback not installed']);
    exit;
}

require_once $pluginFile;

// Важно: ожидаем POST
avtek_cb_handle_submit();
