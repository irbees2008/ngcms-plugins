<?php
if (!defined('NGCMS')) die('HAL');

if (!defined('RSS_IMPORT_MAX_PROFILES')) {
    define('RSS_IMPORT_MAX_PROFILES', 25);
}

function rss_import_storage_table()
{
    return prefix . '_rss_import';
}

function rss_import_storage_table_exists()
{
    global $mysql;

    return is_array($mysql->record('SHOW TABLES LIKE ' . db_squote(rss_import_storage_table())));
}

function rss_import_storage_schema()
{
    return array(
        array(
            'table' => 'rss_import',
            'action' => 'cmodify',
            'key' => 'primary key (id), UNIQUE KEY rss_import_profile_id (profile_id), UNIQUE KEY rss_import_legacy_index (legacy_index), KEY rss_import_sort_order (sort_order, id)',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL AUTO_INCREMENT'),
                array('action' => 'cmodify', 'name' => 'profile_id', 'type' => 'varchar(100)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'title', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'enabled', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 1'),
                array('action' => 'cmodify', 'name' => 'feed_url', 'type' => 'text', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'template', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT 'rss1'"),
                array('action' => 'cmodify', 'name' => 'item_count', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 10'),
                array('action' => 'cmodify', 'name' => 'title_length', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 100'),
                array('action' => 'cmodify', 'name' => 'short_length', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 100'),
                array('action' => 'cmodify', 'name' => 'include_content', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'show_image', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 1'),
                array('action' => 'cmodify', 'name' => 'image_source', 'type' => 'varchar(20)', 'params' => "NOT NULL DEFAULT 'enclosure'"),
                array('action' => 'cmodify', 'name' => 'sort_order', 'type' => 'int(11)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'legacy_index', 'type' => 'int(11)', 'params' => 'UNSIGNED DEFAULT NULL'),
            )
        )
    );
}

function rss_import_storage_execute($sql, $errorMessage)
{
    global $mysql;

    if ($mysql->query($sql) === null) {
        throw new RuntimeException($errorMessage);
    }
}

function rss_import_storage_ensure_table()
{
    if (rss_import_storage_table_exists()) {
        return;
    }

    if (!fixdb_plugin_install('rss_import', rss_import_storage_schema(), 'install', true)
        || !rss_import_storage_table_exists()) {
        throw new RuntimeException('Unable to create the RSS Import profiles table.');
    }
}

function rss_import_storage_has_legacy_profiles()
{
    global $PLUGINS;

    $legacy = isset($PLUGINS['config']['rss_import']) && is_array($PLUGINS['config']['rss_import'])
        ? $PLUGINS['config']['rss_import']
        : array();
    for ($i = 1; $i <= 20; $i++) {
        if (trim((string)($legacy['rss' . $i . '_url'] ?? '')) !== ''
            || trim((string)($legacy['rss' . $i . '_name'] ?? '')) !== '') {
            return true;
        }
    }

    return false;
}

function rss_import_storage_should_offer_update()
{
    if (!rss_import_storage_has_legacy_profiles()) {
        return false;
    }
    if (!rss_import_storage_table_exists()) {
        return true;
    }

    return rss_import_storage_count() === 0;
}

function rss_import_storage_update()
{
    global $mysql, $PLUGINS;

    if (!fixdb_plugin_install('rss_import', rss_import_storage_schema(), 'install', true)
        || !rss_import_storage_table_exists()) {
        throw new RuntimeException('RSS Import database update failed.');
    }

    $columns = $mysql->select('SHOW COLUMNS FROM ' . rss_import_storage_table());
    if (!is_array($columns)) {
        throw new RuntimeException('Unable to inspect the RSS Import profiles table.');
    }
    $columnNames = array();
    foreach ($columns as $column) {
        $columnNames[] = $column['Field'];
    }
    $requiredColumns = array();
    foreach (rss_import_storage_schema()[0]['fields'] as $field) {
        $requiredColumns[] = $field['name'];
    }
    $missingColumns = array_diff($requiredColumns, $columnNames);
    if (count($missingColumns)) {
        throw new RuntimeException('RSS Import database update did not create fields: ' . implode(', ', $missingColumns));
    }

    rss_import_storage_migrate_legacy_profiles();
}

function rss_import_storage_migrate_legacy_profiles()
{
    global $mysql, $PLUGINS;

    rss_import_storage_ensure_table();
    $legacy = isset($PLUGINS['config']['rss_import']) && is_array($PLUGINS['config']['rss_import'])
        ? $PLUGINS['config']['rss_import']
        : array();
    if ((string)pluginGetVariable('rss_import', 'storage_version') === '1'
        && !rss_import_storage_has_legacy_profiles()) {
        return;
    }

    $legacyCount = (int)($legacy['count'] ?? 1);
    if ($legacyCount < 1 || $legacyCount > 20) {
        $legacyCount = 1;
    }

    $backupFile = conf_pconfig . '.rss-import-migration.bak';
    if (is_file(conf_pconfig) && !is_file($backupFile) && !@copy(conf_pconfig, $backupFile)) {
        throw new RuntimeException('Unable to back up plugin configuration before RSS Import migration.');
    }

    rss_import_storage_execute('START TRANSACTION', 'Unable to start RSS Import migration.');
    try {
        $sortOrder = 0;
        for ($i = 1; $i <= 20; $i++) {
            $url = trim((string)($legacy['rss' . $i . '_url'] ?? ''));
            $title = trim((string)($legacy['rss' . $i . '_name'] ?? ''));
            if ($url === '' && $title === '') {
                continue;
            }

            $existing = $mysql->record('SELECT id FROM ' . rss_import_storage_table()
                . ' WHERE legacy_index=' . $i . ' LIMIT 1');
            if (is_array($existing)) {
                $sortOrder++;
                continue;
            }
            $profileId = 'rss' . $i;
            $suffix = 0;
            while (is_array($mysql->record('SELECT id FROM ' . rss_import_storage_table()
                . ' WHERE profile_id=' . db_squote($profileId) . ' LIMIT 1'))) {
                $suffix++;
                $profileId = 'legacy_rss' . $i . '_' . $suffix;
            }

            $values = array(
                'profile_id' => $profileId,
                'title' => $title,
                'enabled' => $i <= $legacyCount ? 1 : 0,
                'feed_url' => $url,
                'template' => 'rss' . $i,
                'item_count' => max(1, (int)($legacy['rss' . $i . '_number'] ?? 10)),
                'title_length' => max(1, (int)($legacy['rss' . $i . '_maxlength'] ?? 100)),
                'short_length' => max(1, (int)($legacy['rss' . $i . '_newslength'] ?? 100)),
                'include_content' => !empty($legacy['rss' . $i . '_content']) ? 1 : 0,
                'show_image' => !isset($legacy['rss' . $i . '_showImage']) || (string)$legacy['rss' . $i . '_showImage'] !== '0' ? 1 : 0,
                'image_source' => in_array(($legacy['rss' . $i . '_imageSource'] ?? ''), array('desc', 'enclosure'), true)
                    ? $legacy['rss' . $i . '_imageSource']
                    : 'enclosure',
            );
            $columns = array_keys($values);
            $sqlValues = array();
            foreach ($values as $key => $value) {
                $sqlValues[] = in_array($key, array('profile_id', 'title', 'feed_url', 'template', 'image_source'), true)
                    ? db_squote($value)
                    : (int)$value;
            }
            rss_import_storage_execute('INSERT INTO ' . rss_import_storage_table()
                . ' (' . implode(', ', $columns) . ', sort_order, legacy_index) VALUES ('
                . implode(', ', $sqlValues) . ', ' . $sortOrder . ', ' . $i . ')',
                'Unable to migrate RSS Import profile #' . $i . '.');
            $sortOrder++;
        }
        rss_import_storage_execute('COMMIT', 'Unable to commit RSS Import migration.');
    } catch (Throwable $e) {
        $mysql->query('ROLLBACK');
        throw $e;
    }

    $oldConfig = $PLUGINS['config']['rss_import'] ?? array();
    for ($i = 1; $i <= 20; $i++) {
        foreach (array('name', 'url', 'number', 'maxlength', 'newslength', 'content', 'img', 'showImage', 'imageSource') as $suffix) {
            unset($PLUGINS['config']['rss_import']['rss' . $i . '_' . $suffix]);
        }
    }
    unset($PLUGINS['config']['rss_import']['count'], $PLUGINS['config']['rss_import']['localsource']);
    $PLUGINS['config']['rss_import']['storage_version'] = '1';
    if (!pluginsSaveConfig()) {
        $PLUGINS['config']['rss_import'] = $oldConfig;
        throw new RuntimeException('Unable to save the RSS Import migration marker.');
    }
}

function rss_import_storage_get_all()
{
    global $mysql;

    $rows = $mysql->select('SELECT * FROM ' . rss_import_storage_table() . ' ORDER BY sort_order, id');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load RSS Import profiles.');
    }
    return $rows;
}

function rss_import_storage_count()
{
    global $mysql;

    $row = $mysql->record('SELECT COUNT(*) AS profile_count FROM ' . rss_import_storage_table());
    if (!is_array($row)) {
        throw new RuntimeException('Unable to count RSS Import profiles.');
    }
    return (int)$row['profile_count'];
}

function rss_import_storage_get_by_id($id)
{
    global $mysql;

    $rows = $mysql->select('SELECT * FROM ' . rss_import_storage_table() . ' WHERE id=' . (int)$id . ' LIMIT 1');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load RSS Import profile.');
    }
    return isset($rows[0]) ? $rows[0] : null;
}

function rss_import_storage_get_by_profile_id($profileId)
{
    global $mysql;

    $rows = $mysql->select('SELECT * FROM ' . rss_import_storage_table()
        . ' WHERE profile_id=' . db_squote((string)$profileId) . ' LIMIT 1');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load RSS Import profile.');
    }
    return isset($rows[0]) ? $rows[0] : null;
}

function rss_import_storage_get_by_legacy_index($index)
{
    global $mysql;

    $rows = $mysql->select('SELECT * FROM ' . rss_import_storage_table()
        . ' WHERE legacy_index=' . (int)$index . ' LIMIT 1');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load RSS Import profile.');
    }
    return isset($rows[0]) ? $rows[0] : null;
}
