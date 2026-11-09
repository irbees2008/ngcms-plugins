<?php

// #====================================================================================#
// # Наименование плагина: nsched [ News SCHEDuller ]                                   #
// # Разрешено к использованию с: Next Generation CMS                                   #
// # Автор: Vitaly A Ponomarev, vp7@mail.ru                                             #
// #====================================================================================#

if (!defined('NGCMS')) {
    die('HAL');
}

function plugin_nsched_install($action)
{
    global $lang, $mysql, $config;

    pluginsLoadConfig();
    LoadPluginLang('nsched', 'install', '', '', ':');

    $db_update = [
        [
            'table' => 'news',
            'action' => 'modify',
            'fields' => [
                ['action' => 'cmodify', 'name' => 'nsched_activate', 'type' => 'int(10)', 'params' => 'not null default "0"'],
                ['action' => 'cmodify', 'name' => 'nsched_deactivate', 'type' => 'int(10)', 'params' => 'not null default "0"'],
            ],
        ],
    ];

    if ($action !== 'apply' && $action !== 'autoapply') {
        generate_install_page('nsched', $lang['nsched:description']);
        return true;
    }

    $installError = '';
    $originalSqlMode = $mysql->result('SELECT @@SESSION.sql_mode');
    if (!is_string($originalSqlMode)) {
        $installError = 'Unable to read the current MySQL SQL mode.';
    } elseif ($mysql->query("SET SESSION sql_mode = ''") === null) {
        $installError = 'Unable to disable strict SQL mode for the NSched update.';
    } else {
        try {
            $newsTable = '`' . $config['prefix'] . '_news`';
            $columns = $mysql->select("SHOW COLUMNS FROM $newsTable WHERE Field IN ('nsched_activate', 'nsched_deactivate')");
            if (!is_array($columns)) {
                $installError = 'Unable to inspect the NSched fields in the news table.';
            } else {
                $existingFields = array_column($columns, 'Field');
                foreach (['nsched_activate', 'nsched_deactivate'] as $field) {
                    if (in_array($field, $existingFields, true)
                        && $mysql->query("UPDATE $newsTable SET `$field` = NULL WHERE `$field` IN ('0000-00-00 00:00:00', '0')") === null) {
                        $installError = 'Unable to clean existing values in the ' . $field . ' field.';
                        break;
                    }
                }
            }

            if ($installError === '') {
                fixdb_plugin_install('nsched', $db_update, 'install');

                $columns = $mysql->select("SHOW COLUMNS FROM $newsTable WHERE Field IN ('nsched_activate', 'nsched_deactivate')");
                $fieldTypes = [];
                foreach (is_array($columns) ? $columns : [] as $column) {
                    $fieldTypes[$column['Field']] = strtolower($column['Type']);
                }
                foreach (['nsched_activate', 'nsched_deactivate'] as $field) {
                    if (!isset($fieldTypes[$field]) || strpos($fieldTypes[$field], 'int') !== 0) {
                        $installError = 'The NSched field ' . $field . ' was not created with an integer type.';
                        break;
                    }
                }
            }

            if ($installError === '' && !plugin_mark_installed('nsched')) {
                $installError = 'The database was updated, but NSched installation status could not be saved.';
            }

            if ($installError === '') {
                $indexName = 'idx_news_approve_nsched_deactivate';
                $indexRows = $mysql->select("SHOW INDEX FROM $newsTable WHERE Key_name = '$indexName'");
                if (!is_array($indexRows)) {
                    $installError = 'Unable to inspect the NSched deactivation index.';
                } else {
                    $indexColumns = array_column($indexRows, 'Column_name');
                    if (!$indexColumns) {
                        if ($mysql->query("ALTER TABLE $newsTable ADD INDEX `$indexName` (`approve`, `nsched_deactivate`)") === null) {
                            $installError = 'Failed to create the NSched deactivation index.';
                            if (!empty($mysql->error)) {
                                $installError .= ' ' . $mysql->error;
                            }
                        }
                    } elseif ($indexColumns !== ['approve', 'nsched_deactivate']) {
                        $installError = "The index `$indexName` exists with an unexpected definition.";
                    }
                }
            }
        } finally {
            if ($mysql->query("SET SESSION sql_mode = " . db_squote($originalSqlMode)) === null
                && $installError === '') {
                $installError = 'NSched was installed, but the original MySQL SQL mode could not be restored.';
            }
        }
    }

    if ($installError !== '') {
        msg(['type' => 'error', 'text' => $installError]);
    } else {
        header('Location: ' . admin_url . '/admin.php?mod=extras');
        exit;
    }

    return $installError === '';
}
