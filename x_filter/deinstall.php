<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts.
if (! defined('NGCMS')) {
    die('HAL');
}

global $mysql, $lang;

// Load lang files
$plugin = 'x_filter';
loadPluginLang($plugin, 'backend', '', $plugin, ':');

if (isset($_SESSION['x_filter_deinstall_error'])) {
    $deinstallError = $_SESSION['x_filter_deinstall_error'];
    unset($_SESSION['x_filter_deinstall_error']);
    msgSticker($deinstallError, 'error');
}

// RUN
if ('commit' == $action) {
    if ($mysql->record("SHOW INDEX FROM " . prefix . "_news WHERE Key_name = 'search'")) {
        if (!$mysql->query("ALTER TABLE " . prefix . "_news DROP INDEX `search`")) {
            $_SESSION['x_filter_deinstall_error'] = $lang[$plugin . ':msg.deinstall_failed'];
            if (!empty($mysql->error)) {
                $_SESSION['x_filter_deinstall_error'] .= ' ' . $mysql->error;
            }
            header('Location: ' . admin_url . '/admin.php?mod=extra-config&plugin=' . rawurlencode($plugin) . '&stype=deinstall');
            exit;
        }
    }

    if (!plugin_mark_deinstalled($plugin)) {
        $_SESSION['x_filter_deinstall_error'] = $lang[$plugin . ':msg.mark_deinstalled_failed'];
        header('Location: ' . admin_url . '/admin.php?mod=extra-config&plugin=' . rawurlencode($plugin) . '&stype=deinstall');
        exit;
    }

    $_SESSION['x_filter_deinstall_notice'] = $lang[$plugin . ':msg.deinstalled'];
    header('Location: ' . admin_url . '/admin.php?mod=extras');
    exit;
} else {
    generate_install_page($plugin, $lang[$plugin . ':desc_deinstall'], 'deinstall');
}
