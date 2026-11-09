<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts.
if (! defined('NGCMS')) {
    die('HAL');
}

// Duplicate var.
$plugin = 'x_filter';

// Load lang files
LoadPluginLang($plugin, 'backend', '', $plugin, ':');

function plugin_x_filter_install($action)
{
    global $mysql, $lang, $plugin;

    // Apply requested action
    switch ($action) {
        case 'confirm':
            if (isset($_SESSION['x_filter_install_error'])) {
                $installError = $_SESSION['x_filter_install_error'];
                unset($_SESSION['x_filter_install_error']);
                msg(['type' => 'error', 'text' => $installError]);
            }
            generate_install_page($plugin, $lang[$plugin . ':desc_install']);
            break;
        case 'autoapply':
        case 'apply':
            if (!$mysql->record("SHOW INDEX FROM " . prefix . "_news WHERE Key_name = 'search'")) {
                if (!$mysql->query("ALTER TABLE " . prefix . "_news ADD FULLTEXT search(title, content)")) {
                    if ('apply' === $action) {
                        $_SESSION['x_filter_install_error'] = $lang[$plugin . ':msg.index_failed'];
                        if (!empty($mysql->error)) {
                            $_SESSION['x_filter_install_error'] .= ' ' . $mysql->error;
                        }
                        header('Location: ' . admin_url . '/admin.php?mod=extra-config&plugin=' . rawurlencode($plugin) . '&stype=install');
                        exit;
                    }
                    return false;
                }
            }

            if (!plugin_mark_installed($plugin)) {
                if ('apply' === $action) {
                    $_SESSION['x_filter_install_error'] = $lang[$plugin . ':msg.mark_failed'];
                    header('Location: ' . admin_url . '/admin.php?mod=extra-config&plugin=' . rawurlencode($plugin) . '&stype=install');
                    exit;
                }
                return false;
            }

            if ('apply' === $action) {
                $_SESSION['x_filter_install_success'] = 1;
                header('Location: ' . admin_url . '/admin.php?mod=extra-config&plugin=' . rawurlencode($plugin));
                exit;
            }

            break;
    }
    return true;
}
