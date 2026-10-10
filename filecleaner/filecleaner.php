<?php
if (!defined('NGCMS')) {
    exit('HAL');
}

function filecleaner_sources(): array
{
    return filecleaner_get_sources();
}

function filecleaner_register_source(string $name, callable $source): void
{
    $sources = filecleaner_get_sources();
    $sources[$name] = $source;
    $GLOBALS['filecleaner_sources'] = $sources;
}

function filecleaner_get_sources(): array
{
    return isset($GLOBALS['filecleaner_sources']) && is_array($GLOBALS['filecleaner_sources'])
        ? $GLOBALS['filecleaner_sources'] : [];
}

filecleaner_register_source('news', static function (): array {
    global $mysql;
    $used = [];
    $rows = $mysql->select('SELECT * FROM ' . prefix . '_news', 1) ?: [];
    foreach ($rows as $row) {
        foreach ($row as $value) {
            if (is_string($value) && $value !== '') {
                $used = array_merge($used, filecleaner_extract_paths($value));
            }
        }
    }
    return $used;
});

function filecleaner_db_file_path(array $row, string $table = 'images'): string
{
    $folder = trim((string)($row['folder'] ?? ''), '/\\');
    $name = trim((string)($row['name'] ?? ''), '/\\');
    $root = !empty($row['storage']) ? 'dsn' : $table;
    return filecleaner_normalize($root . '/' . $folder . '/' . $name);
}

function filecleaner_table_exists(string $table): bool
{
    global $mysql;
    return is_array($mysql->record('SHOW TABLES LIKE ' . db_squote($table)));
}

filecleaner_register_source('registered_files', static function (): array {
    global $mysql;
    $records = [];
    foreach (['files', 'images'] as $table) {
        $rows = $mysql->select('SELECT folder, name, storage, owner_id, plugin FROM ' . prefix . '_' . $table, 1) ?: [];
        foreach ($rows as $row) {
            $path = filecleaner_db_file_path($row, $table);
            if ($path !== '') $records[] = ['path' => $path, 'status' => 'registered', 'source' => $table];
        }
    }
    return $records;
});

filecleaner_register_source('category_images', static function (): array {
    global $mysql;
    $records = [];
    $rows = $mysql->select('SELECT i.folder, i.name, i.storage FROM ' . prefix . '_category c INNER JOIN ' . prefix . '_images i ON i.id = c.image_id WHERE c.image_id > 0', 1) ?: [];
    foreach ($rows as $row) {
        $path = filecleaner_db_file_path($row, 'images');
        if ($path !== '') $records[] = ['path' => $path, 'status' => 'registered', 'source' => 'categories'];
    }
    return $records;
});

filecleaner_register_source('user_avatars', static function (): array {
    global $mysql;
    $records = [];
    $rows = $mysql->select('SELECT avatar FROM ' . prefix . '_users WHERE avatar IS NOT NULL AND avatar <> ""', 1) ?: [];
    foreach ($rows as $row) {
        $avatar = (string)$row['avatar'];
        $path = stripos($avatar, 'uploads/') === 0 ? filecleaner_normalize($avatar) : filecleaner_normalize('avatars/' . $avatar);
        if ($path !== '') $records[] = ['path' => $path, 'status' => 'registered', 'source' => 'avatars'];
    }
    return $records;
});

filecleaner_register_source('xfields_images', static function (): array {
    global $mysql;
    $records = [];
    $rows = $mysql->select('SELECT folder, name, storage FROM ' . prefix . '_images WHERE plugin = "xfields"', 1) ?: [];
    foreach ($rows as $row) {
        $path = filecleaner_db_file_path($row, 'images');
        if ($path !== '') $records[] = ['path' => $path, 'status' => 'registered', 'source' => 'xfields'];
    }
    return $records;
});

filecleaner_register_source('news_attachments', static function (): array {
    global $mysql;
    $records = [];
    foreach (['files', 'images'] as $table) {
        $rows = $mysql->select('SELECT folder, name, storage FROM ' . prefix . '_' . $table . ' WHERE linked_ds = 1 AND linked_id > 0', 1) ?: [];
        foreach ($rows as $row) {
            $path = filecleaner_db_file_path($row, $table);
            if ($path !== '') $records[] = ['path' => $path, 'status' => 'used', 'source' => 'news_attachments'];
        }
    }
    return $records;
});

filecleaner_register_source('xfields_attachments', static function (): array {
    global $mysql;
    $records = [];
    foreach (['files', 'images'] as $table) {
        $fields = $table === 'images' ? 'folder, name, storage, preview' : 'folder, name, storage';
        $rows = $mysql->select('SELECT ' . $fields . ' FROM ' . prefix . '_' . $table . ' WHERE plugin = "xfields" AND linked_ds > 0 AND linked_id > 0', 1) ?: [];
        foreach ($rows as $row) {
            $path = filecleaner_db_file_path($row, $table);
            if ($path !== '') $records[] = ['path' => $path, 'status' => 'used', 'source' => 'xfields_attachments'];
            if ($table === 'images' && !empty($row['preview'])) {
                $root = !empty($row['storage']) ? 'dsn' : 'images';
                $folder = trim((string)($row['folder'] ?? ''), '/\\');
                $name = trim((string)($row['name'] ?? ''), '/\\');
                $thumbPath = filecleaner_normalize($root . '/' . $folder . '/thumb/' . $name);
                if ($thumbPath !== '') $records[] = ['path' => $thumbPath, 'status' => 'used', 'source' => 'xfields_attachments'];
            }
        }
    }
    return $records;
});

filecleaner_register_source('gallery_images', static function (): array {
    global $mysql;
    $records = [];
    if (!getPluginStatusActive('gallery')) {
        return $records;
    }
    if (!filecleaner_table_exists(prefix . '_gallery') || !filecleaner_table_exists(prefix . '_images')) {
        return $records;
    }
    $rows = $mysql->select('SELECT i.folder, i.name, i.storage FROM ' . prefix . '_gallery g INNER JOIN ' . prefix . '_images i ON i.folder = g.name WHERE g.if_active = 1', 1) ?: [];
    foreach ($rows as $row) {
        $path = filecleaner_db_file_path($row, 'images');
        if ($path !== '') {
            $records[] = ['path' => $path, 'status' => 'used', 'source' => 'gallery'];
            $records[] = ['path' => filecleaner_normalize(dirname($path) . '/thumb/' . basename($path)), 'status' => 'used', 'source' => 'gallery'];
        }
    }
    return $records;
});

filecleaner_register_source('eshop_images', static function (): array {
    global $mysql;
    $records = [];
    if (!getPluginStatusActive('eshop')) {
        return $records;
    }
    if (!filecleaner_table_exists(prefix . '_eshop_images')) {
        return $records;
    }
    $rows = $mysql->select('SELECT filepath, product_id FROM ' . prefix . '_eshop_images WHERE filepath <> "" AND product_id > 0', 1) ?: [];
    foreach ($rows as $row) {
        $filepath = filecleaner_normalize((string)$row['filepath']);
        $productId = (int)$row['product_id'];
        if ($filepath === '' || $productId <= 0) continue;
        foreach (['', 'thumb/'] as $variant) {
            $path = filecleaner_normalize('eshop/products/' . $productId . '/' . $variant . $filepath);
            if ($path !== '') $records[] = ['path' => $path, 'status' => 'used', 'source' => 'eshop'];
        }
    }
    return $records;
});

filecleaner_register_source('eshop_category_images', static function (): array {
    global $mysql;
    $records = [];
    if (!getPluginStatusActive('eshop')) {
        return $records;
    }
    if (!filecleaner_table_exists(prefix . '_eshop_categories')) {
        return $records;
    }
    $rows = $mysql->select('SELECT image FROM ' . prefix . '_eshop_categories WHERE image <> ""', 1) ?: [];
    foreach ($rows as $row) {
        $filepath = filecleaner_normalize((string)$row['image']);
        if ($filepath === '') continue;
        foreach (['', 'thumb/'] as $variant) {
            $path = filecleaner_normalize('eshop/categories/' . $variant . $filepath);
            if ($path !== '') $records[] = ['path' => $path, 'status' => 'used', 'source' => 'eshop_categories'];
        }
    }
    return $records;
});

function filecleaner_root(): string
{
    global $config;
    $baseRoot = rtrim(dirname(__DIR__, 3), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
    $paths = [];
    foreach (['files_dir', 'avatars_dir', 'attach_dir', 'images_dir'] as $key) {
        if (!empty($config[$key])) {
            $path = realpath((string)$config[$key]);
            if ($path !== false && is_dir($path)) $paths[] = dirname($path);
        }
    }
    if (!$paths) return $baseRoot;
    $root = $paths[0];
    foreach ($paths as $path) {
        if (strcasecmp(rtrim($path, DIRECTORY_SEPARATOR), rtrim($root, DIRECTORY_SEPARATOR)) !== 0) return $baseRoot;
    }
    return $root;
}

function filecleaner_base_root(): string
{
    return rtrim(dirname(__DIR__, 3), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
}

function filecleaner_is_allowed_relative(string $relative): bool
{
    $relative = filecleaner_normalize($relative);
    $root = realpath(filecleaner_root());
    $baseRoot = realpath(filecleaner_base_root());
    if ($root !== false && $baseRoot !== false && strcasecmp($root, $baseRoot) === 0) {
        return $relative !== 'multi' && strpos($relative, 'multi/') !== 0;
    }
    return true;
}

function filecleaner_storage(string $name): string
{
    global $multiDomainName;
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    $siteKey = isset($multiDomainName) && $multiDomainName !== '' ? (string)$multiDomainName : 'main';
    $siteKey = preg_replace('/[^a-z0-9_.-]+/i', '_', $siteKey);
    if ($siteKey !== 'main') $dir .= DIRECTORY_SEPARATOR . $siteKey;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . DIRECTORY_SEPARATOR . $name;
}

function filecleaner_log_operation(string $action, string $status, array $details = []): void
{
    $record = ['date' => date('c'), 'action' => $action, 'status' => $status, 'details' => $details];
    file_put_contents(filecleaner_storage('operations.log'), json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
}

function filecleaner_available_dirs(): array
{
    $root = realpath(filecleaner_root());
    $dirs = [];
    if ($root === false || !is_dir($root)) return $dirs;
    foreach (['avatars', 'dsn'] as $directory) {
        $path = $root . DIRECTORY_SEPARATOR . $directory;
        if (is_dir($path) && filecleaner_is_allowed_relative($directory)) {
            $dirs[$directory] = $directory . '/';
        }
    }
    foreach (['images', 'files'] as $directory) {
        $path = $root . DIRECTORY_SEPARATOR . $directory;
        if (!is_dir($path) || !filecleaner_is_allowed_relative($directory)) continue;
        $dirs[$directory] = $directory . '/';
        foreach (new DirectoryIterator($path) as $child) {
            if ($child->isDot() || !$child->isDir() || $child->isLink()) continue;
            $relative = $directory . '/' . $child->getFilename();
            if (filecleaner_is_allowed_relative($relative)) $dirs[$relative] = $relative . '/';
        }
    }
    ksort($dirs, SORT_NATURAL | SORT_FLAG_CASE);
    return $dirs;
}

function filecleaner_selected_dirs(): array
{
    $raw = json_decode((string)pluginGetVariable('filecleaner', 'selected_dirs'), true);
    return is_array($raw) ? array_values(array_unique(array_map('strval', $raw))) : [];
}

function filecleaner_mask_options(): array
{
    return ['.htaccess', '*.ico', '*.svg', '*.tmp', '*.bak', '*.log'];
}

function filecleaner_protected_paths(): array
{
    return ['avatars/noavatar.gif', 'avatars/noavatar.png'];
}

function filecleaner_scan_ready(): bool
{
    $available = filecleaner_available_dirs();
    foreach (filecleaner_selected_dirs() as $directory) {
        if (isset($available[$directory])) return true;
    }
    return false;
}

function filecleaner_config(): array
{
    $storedDays = pluginGetVariable('filecleaner', 'protect_days');
    $days = ($storedDays === false || $storedDays === null || $storedDays === '') ? 30 : (int)$storedDays;
    $days = in_array($days, [0, 1, 3, 7, 14, 30, 60, 90], true) ? $days : 30;
    $excludedDirs = preg_split('/\R+/', (string)pluginGetVariable('filecleaner', 'excluded_dirs'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $excludedMasks = preg_split('/\R+/', (string)pluginGetVariable('filecleaner', 'excluded_masks'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (!in_array('.htaccess', $excludedMasks, true)) $excludedMasks[] = '.htaccess';
    $maskOptions = filecleaner_mask_options();
    return [
        'protect_days' => $days,
        'excluded_dirs' => $excludedDirs,
        'excluded_masks' => $excludedMasks,
        'selected_dirs' => filecleaner_selected_dirs(),
        'mask_options' => $maskOptions,
        'selected_masks' => array_values(array_intersect($maskOptions, $excludedMasks)),
        'custom_masks' => array_values(array_diff($excludedMasks, $maskOptions)),
    ];
}

function filecleaner_normalize(string $path): string
{
    $path = html_entity_decode(trim($path), ENT_QUOTES, 'UTF-8');
    if (preg_match('~^[a-z][a-z0-9+.-]*://~i', $path)) {
        $parsed = parse_url($path);
        $path = is_array($parsed) ? (string)($parsed['path'] ?? '') : '';
    }
    $path = rawurldecode($path);
    $path = str_replace('\\', '/', preg_replace('~/+~', '/', $path));
    $path = ltrim($path, './/');
    if (stripos($path, 'uploads/') === 0) {
        $path = substr($path, 8);
    }
    $parts = [];
    foreach (explode('/', $path) as $part) {
        if ($part === '' || $part === '.') continue;
        if ($part === '..') return '';
        $parts[] = $part;
    }
    return strtolower(implode('/', $parts));
}

function filecleaner_extract_paths(string $html): array
{
    $values = [];
    if (preg_match_all('/(?:src|href|data-src|data-original|poster)\s*=\s*["\']([^"\']+)["\']/i', $html, $matches)) {
        $values = $matches[1];
    }
    if (preg_match_all('~(?:https?://[^\s"\'<>]+|/?uploads/[^\s"\'<>]+)~i', $html, $matches)) {
        $values = array_merge($values, $matches[0]);
    }
    $result = [];
    foreach ($values as $value) {
        $normalized = filecleaner_normalize($value);
        if ($normalized !== '') $result[$normalized] = $normalized;
    }
    return array_values($result);
}

function filecleaner_thumbnail_source_exists(string $relative): bool
{
    $relative = filecleaner_normalize($relative);
    $marker = '/thumb/';
    $markerPosition = strpos($relative, $marker);
    if ($markerPosition === false) return false;
    $sourceRelative = substr($relative, 0, $markerPosition) . '/' . substr($relative, $markerPosition + strlen($marker));
    if ($sourceRelative === '' || $sourceRelative === $relative) return false;
    $root = realpath(filecleaner_root());
    if ($root === false) return false;
    $sourcePath = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sourceRelative));
    return $sourcePath !== false && is_file($sourcePath) && strpos($sourcePath, $root . DIRECTORY_SEPARATOR) === 0;
}

function filecleaner_is_excluded(string $relative, array $cfg): bool
{
    $relative = filecleaner_normalize($relative);
    if (in_array($relative, filecleaner_protected_paths(), true)) return true;
    foreach ($cfg['excluded_dirs'] as $dir) {
        $dir = rtrim(filecleaner_normalize($dir), '/') . '/';
        if ($dir !== '/' && strpos($relative . '/', $dir) === 0) return true;
    }
    foreach ($cfg['excluded_masks'] as $mask) {
        if (fnmatch(strtolower(trim($mask)), basename($relative))) return true;
    }
    return false;
}

function filecleaner_scan(): array
{
    filecleaner_log_operation('scan', 'started');
    $root = realpath(filecleaner_root());
    $cfg = filecleaner_config();
    $physical = [];
    $available = filecleaner_available_dirs();
    if ($root !== false && is_dir($root) && filecleaner_scan_ready()) {
        foreach (filecleaner_selected_dirs() as $selected) {
            if (!isset($available[$selected])) continue;
            $selectedRelative = $selected === '__root__' ? '' : trim($available[$selected], '/');
            $scanPath = $selected === '__root__' ? $root : realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $selectedRelative));
            if ($scanPath === false || strpos($scanPath, $root) !== 0 || !is_dir($scanPath)) continue;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanPath, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->isLink()) continue;
                $relative = filecleaner_normalize(substr($file->getPathname(), strlen($root) + 1));
                if ($relative === '' || !filecleaner_is_allowed_relative($relative) || filecleaner_is_excluded($relative, $cfg)) continue;
                $physical[$relative] = ['path' => $relative, 'size' => (int)$file->getSize(), 'mtime' => (int)$file->getMTime()];
            }
        }
    }
    $used = [];
    $registered = [];
    $sourceErrors = [];
    foreach (filecleaner_get_sources() as $sourceName => $source) {
        try {
            foreach ((array)call_user_func($source) as $record) {
                $isRecord = is_array($record);
                $path = $isRecord ? (string)($record['path'] ?? '') : (string)$record;
                $normalized = filecleaner_normalize($path);
                if ($normalized !== '' && (!$isRecord || ($record['status'] ?? '') === 'used')) $used[$normalized] = true;
                if ($normalized !== '' && $isRecord && ($record['status'] ?? '') === 'registered') $registered[$normalized] = true;
            }
        } catch (Throwable $e) {
            $sourceErrors[] = (string)$sourceName;
        }
    }
    $now = time();
    $protectBefore = $now - ($cfg['protect_days'] * 86400);
    $files = [];
    foreach ($physical as $entry) {
        $isUsed = isset($used[$entry['path']]) || filecleaner_thumbnail_source_exists($entry['path']);
        $protected = filecleaner_is_excluded($entry['path'], $cfg) || $entry['mtime'] > $protectBefore;
        $entry['status'] = $isUsed
            ? 'used'
            : (isset($registered[$entry['path']]) ? 'registered' : ($protected ? 'protected' : ($sourceErrors ? 'insufficient' : 'unused')));
        $entry['source_registered'] = isset($registered[$entry['path']]);
        $files[] = $entry;
    }
    $result = ['scanned_at' => $now, 'files' => $files, 'source_errors' => $sourceErrors];
    file_put_contents(filecleaner_storage('scan.json'), json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    filecleaner_log_operation('scan', 'completed', ['files' => count($files), 'source_errors' => $sourceErrors]);
    return $result;
}

function filecleaner_scan_step(int $batchSize = 500): array
{
    $stateFile = filecleaner_storage('scan-progress.json');
    $state = is_file($stateFile) ? json_decode((string)file_get_contents($stateFile), true) : null;
    if (!is_array($state) || !isset($state['files'], $state['cursor'], $state['used'], $state['registered'], $state['source_errors'])) {
        $root = realpath(filecleaner_root());
        $cfg = filecleaner_config();
        $physical = [];
        $available = filecleaner_available_dirs();
        if ($root !== false && is_dir($root) && filecleaner_scan_ready()) {
            foreach (filecleaner_selected_dirs() as $selected) {
                if (!isset($available[$selected])) continue;
                $selectedRelative = $selected === '__root__' ? '' : trim($available[$selected], '/');
                $scanPath = $selected === '__root__' ? $root : realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $selectedRelative));
                if ($scanPath === false || strpos($scanPath, $root) !== 0 || !is_dir($scanPath)) continue;
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanPath, FilesystemIterator::SKIP_DOTS)) as $file) {
                    if (!$file->isFile() || $file->isLink()) continue;
                    $relative = filecleaner_normalize(substr($file->getPathname(), strlen($root) + 1));
                    if ($relative === '' || !filecleaner_is_allowed_relative($relative) || filecleaner_is_excluded($relative, $cfg)) continue;
                    $physical[$relative] = ['path' => $relative, 'size' => (int)$file->getSize(), 'mtime' => (int)$file->getMTime()];
                }
            }
        }
        $used = [];
        $registered = [];
        $sourceErrors = [];
        foreach (filecleaner_get_sources() as $sourceName => $source) {
            try {
                foreach ((array)call_user_func($source) as $record) {
                    $isRecord = is_array($record);
                    $normalized = filecleaner_normalize($isRecord ? (string)($record['path'] ?? '') : (string)$record);
                    if ($normalized !== '' && (!$isRecord || ($record['status'] ?? '') === 'used')) $used[$normalized] = true;
                    if ($normalized !== '' && $isRecord && ($record['status'] ?? '') === 'registered') $registered[$normalized] = true;
                }
            } catch (Throwable $e) {
                $sourceErrors[] = (string)$sourceName;
            }
        }
        $state = ['files' => array_values($physical), 'cursor' => 0, 'used' => $used, 'registered' => $registered, 'source_errors' => $sourceErrors, 'result' => []];
    }
    $batchSize = max(1, $batchSize);
    $now = time();
    $protectBefore = $now - (filecleaner_config()['protect_days'] * 86400);
    $end = min(count($state['files']), (int)$state['cursor'] + $batchSize);
    for ($index = (int)$state['cursor']; $index < $end; $index++) {
        $entry = $state['files'][$index];
        $isUsed = isset($state['used'][$entry['path']]) || filecleaner_thumbnail_source_exists($entry['path']);
        $isRegistered = isset($state['registered'][$entry['path']]);
        $protected = filecleaner_is_excluded($entry['path'], filecleaner_config()) || $entry['mtime'] > $protectBefore;
        $entry['status'] = $isUsed ? 'used' : ($isRegistered ? 'registered' : ($protected ? 'protected' : ($state['source_errors'] ? 'insufficient' : 'unused')));
        $entry['source_registered'] = $isRegistered;
        $state['result'][] = $entry;
    }
    $state['cursor'] = $end;
    if ($end < count($state['files'])) {
        file_put_contents($stateFile, json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        filecleaner_log_operation('scan_step', 'progress', ['processed' => $end, 'total' => count($state['files'])]);
        return ['complete' => false, 'processed' => $end, 'total' => count($state['files'])];
    }
    $result = ['scanned_at' => $now, 'files' => $state['result'], 'source_errors' => $state['source_errors']];
    file_put_contents(filecleaner_storage('scan.json'), json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @unlink($stateFile);
    filecleaner_log_operation('scan_step', 'completed', ['files' => $end, 'source_errors' => $state['source_errors']]);
    return ['complete' => true, 'processed' => $end, 'total' => $end];
}

function filecleaner_load_scan(): array
{
    $file = filecleaner_storage('scan.json');
    $data = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    return is_array($data) && isset($data['files']) ? $data : ['scanned_at' => 0, 'files' => []];
}

function filecleaner_forget_scan_entry(string $relative): void
{
    $scan = filecleaner_load_scan();
    $scan['files'] = array_values(array_filter($scan['files'], static function (array $entry) use ($relative): bool {
        return filecleaner_normalize((string)($entry['path'] ?? '')) !== $relative;
    }));
    file_put_contents(filecleaner_storage('scan.json'), json_encode($scan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function filecleaner_delete(string $relative): array
{
    global $lang;
    $relative = filecleaner_normalize($relative);
    $root = realpath(filecleaner_root());
    $fullPath = $root === false ? false : realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
    $scan = filecleaner_load_scan();
    $candidate = null;
    foreach ($scan['files'] as $entry) {
        if (filecleaner_normalize((string)($entry['path'] ?? '')) === $relative) $candidate = $entry;
    }
    if (!$root || !filecleaner_is_allowed_relative($relative) || !$candidate || $candidate['status'] !== 'unused' || ($fullPath && strpos($fullPath, $root . DIRECTORY_SEPARATOR) !== 0)) {
        return [false, $lang['filecleaner:delete_recheck_failed']];
    }
    if (!$fullPath || !is_file($fullPath)) {
        filecleaner_forget_scan_entry($relative);
        filecleaner_log_operation('delete', 'missing', ['path' => $relative]);
        return [true, sprintf($lang['filecleaner:delete_missing'], $relative)];
    }
    $cfg = filecleaner_config();
    if (filecleaner_is_excluded($relative, $cfg) || filemtime($fullPath) > time() - ($cfg['protect_days'] * 86400)) {
        return [false, $lang['filecleaner:delete_protected']];
    }
    if (filecleaner_thumbnail_source_exists($relative)) {
        return [false, $lang['filecleaner:delete_in_use']];
    }
    foreach (filecleaner_get_sources() as $source) {
        try {
            foreach ((array)call_user_func($source) as $record) {
                $sourcePath = is_array($record) ? (string)($record['path'] ?? '') : (string)$record;
                if (filecleaner_normalize($sourcePath) === $relative) return [false, $lang['filecleaner:delete_in_use']];
            }
        } catch (Throwable $e) {
            return [false, $lang['filecleaner:delete_source_unavailable']];
        }
    }
    $size = filesize($fullPath);
    if (!unlink($fullPath)) return [false, $lang['filecleaner:delete_failed']];
    $log = filecleaner_storage('deletions.log');
    file_put_contents($log, date('c') . "\t" . $relative . "\t" . (int)$size . "\n", FILE_APPEND | LOCK_EX);
    filecleaner_log_operation('delete', 'completed', ['path' => $relative, 'size' => (int)$size]);
    return [true, sprintf($lang['filecleaner:delete_success'], $relative)];
}

function filecleaner_register_cron(): void
{
    global $cron;
    if (!isset($cron) || !is_object($cron)) return;
    $cron->unregisterTask('filecleaner', 'scan');
    $cronEnabled = pluginGetVariable('filecleaner', 'cron_enabled');
    if ($cronEnabled !== false && $cronEnabled !== null && $cronEnabled !== '' && (int)$cronEnabled !== 1) return;
    $minute = (string)pluginGetVariable('filecleaner', 'cron_minute');
    $hour = (string)pluginGetVariable('filecleaner', 'cron_hour');
    $minute = in_array($minute, ['0', '15', '30', '45'], true) ? $minute : '15';
    $hour = (string)(int)$hour;
    if ((int)$hour < 0 || (int)$hour > 23) $hour = '3';
    $cron->registerTask('filecleaner', 'scan', $minute, $hour, '*', '*', '*');
}

function plugin_filecleaner_cron(): void
{
    filecleaner_scan_step(500);
}

filecleaner_register_cron();
