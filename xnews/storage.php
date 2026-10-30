<?php
if (!defined('NGCMS')) die('HAL');

if (!defined('XNEWS_MAX_PROFILES')) {
    define('XNEWS_MAX_PROFILES', 25);
}

function xnews_storage_table()
{
    return prefix . '_xnews';
}

function xnews_storage_has_legacy_profiles()
{
    global $PLUGINS;

    $legacy = isset($PLUGINS['config']['xnews']) && is_array($PLUGINS['config']['xnews'])
        ? $PLUGINS['config']['xnews']
        : array();
    for ($i = 1; $i <= 50; $i++) {
        if (trim((string)($legacy[$i . '_name'] ?? '')) !== '') {
            return true;
        }
    }

    return false;
}

function xnews_storage_should_offer_update()
{
    if (!xnews_storage_has_legacy_profiles()) {
        return false;
    }
    if (!mysql_table_exists(xnews_storage_table())) {
        return true;
    }

    return xnews_storage_count() === 0;
}

function xnews_storage_schema()
{
    return array(
        array(
            'table' => 'xnews',
            'action' => 'cmodify',
            'key' => 'primary key (id), UNIQUE KEY xnews_legacy_index (legacy_index), KEY xnews_block_name (block_name), KEY xnews_sort_order (sort_order, id)',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL AUTO_INCREMENT'),
                array('action' => 'cmodify', 'name' => 'block_name', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'enabled', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 1'),
                array('action' => 'cmodify', 'name' => 'template', 'type' => 'varchar(190)', 'params' => "NOT NULL DEFAULT ''"),
                array('action' => 'cmodify', 'name' => 'visibility_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'visibility_categories', 'type' => 'text', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'category_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'categories', 'type' => 'text', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'main_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'pin_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'fav_mode', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'skip_current', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'extract_embedded_items', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'show_no_news', 'type' => 'tinyint(1)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'news_count', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 10'),
                array('action' => 'cmodify', 'name' => 'news_skip', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'max_age', 'type' => 'int(11)', 'params' => 'UNSIGNED NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'order_by', 'type' => 'varchar(20)', 'params' => "NOT NULL DEFAULT 'viewed'"),
                array('action' => 'cmodify', 'name' => 'sort_order', 'type' => 'int(11)', 'params' => 'NOT NULL DEFAULT 0'),
                array('action' => 'cmodify', 'name' => 'legacy_index', 'type' => 'int(11)', 'params' => 'UNSIGNED DEFAULT NULL'),
            )
        ) 
    );
}

function xnews_storage_ensure_table()
{
    if (mysql_table_exists(xnews_storage_table())) {
        return;
    }

    if (!fixdb_plugin_install('xnews', xnews_storage_schema(), 'install', true)
        || !mysql_table_exists(xnews_storage_table())) {
        throw new RuntimeException('Unable to create the Xnews profiles table.');
    }
}

function xnews_storage_update()
{
    global $mysql;

    if (!fixdb_plugin_install('xnews', xnews_storage_schema(), 'install', true)) {
        throw new RuntimeException('Xnews database update failed.');
    }
    if (!mysql_table_exists(xnews_storage_table())) {
        throw new RuntimeException('Xnews profiles table was not created.');
    }

    $columns = $mysql->select('SHOW COLUMNS FROM ' . xnews_storage_table());
    if (!is_array($columns)) {
        throw new RuntimeException('Unable to inspect the Xnews profiles table.');
    }
    $columnNames = array();
    foreach ($columns as $column) {
        $columnNames[] = $column['Field'];
    }
    $requiredColumns = array();
    foreach (xnews_storage_schema()[0]['fields'] as $field) {
        $requiredColumns[] = $field['name'];
    }
    $missingColumns = array_diff($requiredColumns, $columnNames);
    if (count($missingColumns)) {
        throw new RuntimeException('Xnews database update did not create fields: ' . implode(', ', $missingColumns));
    }

    $indexes = $mysql->select('SHOW INDEX FROM ' . xnews_storage_table());
    if (!is_array($indexes)) {
        throw new RuntimeException('Unable to inspect Xnews database indexes.');
    }
    $indexNames = array();
    foreach ($indexes as $index) {
        $indexNames[] = $index['Key_name'];
    }
    $indexStatements = array(
        'PRIMARY' => 'ALTER TABLE ' . xnews_storage_table() . ' ADD PRIMARY KEY (id)',
        'xnews_legacy_index' => 'ALTER TABLE ' . xnews_storage_table() . ' ADD UNIQUE KEY xnews_legacy_index (legacy_index)',
        'xnews_block_name' => 'ALTER TABLE ' . xnews_storage_table() . ' ADD KEY xnews_block_name (block_name)',
        'xnews_sort_order' => 'ALTER TABLE ' . xnews_storage_table() . ' ADD KEY xnews_sort_order (sort_order, id)',
    );
    foreach ($indexStatements as $indexName => $sql) {
        if (!in_array($indexName, $indexNames, true)) {
            xnews_storage_execute($sql, 'Unable to create Xnews index ' . $indexName . '.');
        }
    }

    xnews_storage_migrate_legacy_profiles();
}

function xnews_storage_execute($sql, $errorMessage)
{
    global $mysql;

    if ($mysql->query($sql) === null) {
        throw new RuntimeException($errorMessage);
    }
}

function xnews_storage_get_all()
{
    global $mysql;

    $rows = $mysql->select('SELECT * FROM ' . xnews_storage_table() . ' ORDER BY sort_order, id');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load Xnews profiles.');
    }
    return $rows;
}

function xnews_storage_count()
{
    global $mysql;

    $row = $mysql->record('SELECT COUNT(*) AS profile_count FROM ' . xnews_storage_table());
    if (!is_array($row)) {
        throw new RuntimeException('Unable to count Xnews profiles.');
    }
    return (int)$row['profile_count'];
}

function xnews_storage_get_by_id($id)
{
    global $mysql;

    $id = (int)$id;
    $rows = $mysql->select('SELECT * FROM ' . xnews_storage_table() . ' WHERE id=' . $id . ' LIMIT 1');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load Xnews profile.');
    }
    return isset($rows[0]) ? $rows[0] : null;
}

function xnews_storage_get_by_name($name)
{
    global $mysql;
    static $profiles = array();

    $name = (string)$name;
    if (array_key_exists($name, $profiles)) {
        return $profiles[$name];
    }

    $rows = $mysql->select('SELECT * FROM ' . xnews_storage_table()
        . ' WHERE block_name=' . db_squote($name) . ' ORDER BY sort_order, id LIMIT 1');
    if (!is_array($rows)) {
        throw new RuntimeException('Unable to load Xnews profile.');
    }
    $profiles[$name] = isset($rows[0]) ? $rows[0] : null;
    return $profiles[$name];
}

function xnews_storage_migrate_legacy_profiles()
{
    global $mysql, $PLUGINS;

    xnews_storage_ensure_table();
    $legacy = isset($PLUGINS['config']['xnews']) && is_array($PLUGINS['config']['xnews'])
        ? $PLUGINS['config']['xnews']
        : array();
    if ((string)pluginGetVariable('xnews', 'storage_version') === '1'
        && !xnews_storage_has_legacy_profiles()) {
        return;
    }

    $legacyCount = isset($legacy['count']) ? (int)$legacy['count'] : 1;
    if ($legacyCount < 1 || $legacyCount > 50) {
        $legacyCount = 1;
    }

    $backupFile = conf_pconfig . '.xnews-migration.bak';
    if (is_file(conf_pconfig) && !is_file($backupFile) && !@copy(conf_pconfig, $backupFile)) {
        throw new RuntimeException('Unable to back up plugin configuration before Xnews migration.');
    }

    xnews_storage_execute('START TRANSACTION', 'Unable to start Xnews profiles migration.');

    try {
        $order = 0;
        $suffixes = array(
            'name', 'enabled', 'template', 'visibilityMode', 'visibilityCList',
            'categoryMode', 'categories', 'mainMode', 'pinMode', 'favMode',
            'skipCurrent', 'extractEmbeddedItems', 'showNoNews', 'count',
            'skip', 'maxAge', 'order'
        );
        for ($i = 1; $i <= 50; $i++) {
            $name = trim((string)($legacy[$i . '_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $existing = $mysql->record('SELECT id FROM ' . xnews_storage_table()
                . ' WHERE legacy_index=' . $i . ' LIMIT 1');
            if (is_array($existing)) {
                $order++;
                continue;
            }

            $values = array(
                'block_name' => $name,
                'enabled' => ($i <= $legacyCount && (string)($legacy[$i . '_enabled'] ?? '1') !== '0') ? 1 : 0,
                'template' => (string)($legacy[$i . '_template'] ?? ''),
                'visibility_mode' => (int)($legacy[$i . '_visibilityMode'] ?? 0),
                'visibility_categories' => (string)($legacy[$i . '_visibilityCList'] ?? ''),
                'category_mode' => (int)($legacy[$i . '_categoryMode'] ?? 0),
                'categories' => (string)($legacy[$i . '_categories'] ?? ''),
                'main_mode' => (int)($legacy[$i . '_mainMode'] ?? 0),
                'pin_mode' => (int)($legacy[$i . '_pinMode'] ?? 0),
                'fav_mode' => (int)($legacy[$i . '_favMode'] ?? 0),
                'skip_current' => (int)($legacy[$i . '_skipCurrent'] ?? 0),
                'extract_embedded_items' => (int)($legacy[$i . '_extractEmbeddedItems'] ?? 0),
                'show_no_news' => !empty($legacy[$i . '_showNoNews']) ? 1 : 0,
                'news_count' => max(1, (int)($legacy[$i . '_count'] ?? 10)),
                'news_skip' => max(0, (int)($legacy[$i . '_skip'] ?? 0)),
                'max_age' => max(0, (int)($legacy[$i . '_maxAge'] ?? 0)),
                'order_by' => (string)($legacy[$i . '_order'] ?? 'viewed'),
            );
            $columns = array_keys($values);
            $sqlValues = array();
            foreach ($values as $key => $value) {
                $sqlValues[] = in_array($key, array('block_name', 'template', 'visibility_categories', 'categories', 'order_by'), true)
                    ? db_squote($value)
                    : (int)$value;
            }
            xnews_storage_execute('INSERT INTO ' . xnews_storage_table()
                . ' (' . implode(', ', $columns) . ', sort_order, legacy_index) VALUES ('
                . implode(', ', $sqlValues) . ', ' . $order . ', ' . $i . ')',
                'Unable to migrate Xnews profile #' . $i . '.');
            $order++;
        }

        xnews_storage_execute('COMMIT', 'Unable to commit Xnews profiles migration.');
    } catch (Throwable $e) {
        $mysql->query('ROLLBACK');
        throw $e;
    }

    $oldConfig = $PLUGINS['config']['xnews'] ?? array();
    foreach ($suffixes as $suffix) {
        for ($i = 1; $i <= 50; $i++) {
            unset($PLUGINS['config']['xnews'][$i . '_' . $suffix]);
        }
    }
    unset($PLUGINS['config']['xnews']['count']);
    $PLUGINS['config']['xnews']['storage_version'] = '1';
    if (!pluginsSaveConfig()) {
        $PLUGINS['config']['xnews'] = $oldConfig;
        throw new RuntimeException('Unable to save the Xnews migration marker.');
    }
}

function xnews_storage_profile_to_params($profile)
{
    if (!is_array($profile)) {
        return null;
    }

    return array(
        'enabled' => (int)$profile['enabled'],
        'name' => (string)$profile['block_name'],
        'template' => (string)$profile['template'],
        'visibilityMode' => (int)$profile['visibility_mode'],
        'visibilityCList' => (string)$profile['visibility_categories'],
        'categoryMode' => (int)$profile['category_mode'],
        'categories' => (string)$profile['categories'],
        'mainMode' => (int)$profile['main_mode'],
        'pinMode' => (int)$profile['pin_mode'],
        'favMode' => (int)$profile['fav_mode'],
        'skipCurrent' => (int)$profile['skip_current'],
        'extractEmbeddedItems' => (int)$profile['extract_embedded_items'],
        'showNoNews' => (int)$profile['show_no_news'],
        'count' => (int)$profile['news_count'],
        'skip' => (int)$profile['news_skip'],
        'maxAge' => (int)$profile['max_age'],
        'order' => (string)$profile['order_by'],
    );
}
