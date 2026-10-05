<?php
if (!defined('NGCMS')) die('HAL');
LoadPluginLang('filecleaner', 'config', '', '', ':');
if (!getPluginStatusActive('filecleaner')) {
    msg(['type' => 'error', 'text' => $lang['filecleaner:plugin_disabled']]);
    return;
}

pluginsLoadConfig();
require_once __DIR__ . '/filecleaner.php';

function filecleaner_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function filecleaner_size(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $unit = 0;
    $value = (float)$bytes;
    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        $unit++;
    }
    return number_format($value, $unit ? 2 : 0, '.', ' ') . ' ' . $units[$unit];
}

function filecleaner_is_image(string $path): bool
{
    return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['avif', 'gif', 'jpeg', 'jpg', 'png', 'webp'], true);
}

function filecleaner_type(string $path): string
{
    if (filecleaner_is_image($path)) return 'image';
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($extension, ['doc', 'docx', 'odt', 'pdf', 'rtf', 'txt', 'xls', 'xlsx'], true)) return 'document';
    if (in_array($extension, ['7z', 'gz', 'rar', 'tar', 'zip'], true)) return 'archive';
    return 'other';
}

function filecleaner_url(string $path): string
{
    global $config;
    $path = filecleaner_normalize($path);
    $roots = [
        'avatars' => ['avatars_dir', 'avatars_url'],
        'files' => ['files_dir', 'files_url'],
        'dsn' => ['attach_dir', 'attach_url'],
        'images' => ['images_dir', 'images_url'],
    ];
    foreach ($roots as $folder => $keys) {
        $prefix = $folder . '/';
        if (strpos($path, $prefix) === 0 && !empty($config[$keys[1]])) {
            return rtrim((string)$config[$keys[1]], '/') . '/' . implode('/', array_map('rawurlencode', explode('/', substr($path, strlen($prefix)))));
        }
    }
    return rtrim((string)home, '/') . '/uploads/' . implode('/', array_map('rawurlencode', explode('/', $path)));
}

function filecleaner_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (empty($_SESSION['filecleaner_token'])) $_SESSION['filecleaner_token'] = bin2hex(random_bytes(16));
    return $_SESSION['filecleaner_token'];
}

function filecleaner_check_token(): bool
{
    return hash_equals(filecleaner_token(), (string)($_POST['token'] ?? ''));
}

function filecleaner_cron_settings(): array
{
    global $cron;
    $storedEnabled = pluginGetVariable('filecleaner', 'cron_enabled');
    $settings = ['enabled' => ($storedEnabled === false || $storedEnabled === null || $storedEnabled === '' || (int)$storedEnabled === 1), 'minute' => '15', 'hour' => '3'];
    if (isset($cron) && is_object($cron)) {
        foreach ((array)$cron->getConfig() as $task) {
            if (($task['plugin'] ?? '') === 'filecleaner' && ($task['handler'] ?? '') === 'scan') {
                $settings['enabled'] = true;
                $settings['minute'] = (string)($task['min'] ?? $settings['minute']);
                $settings['hour'] = (string)($task['hour'] ?? $settings['hour']);
                break;
            }
        }
    }
    return $settings;
}

function filecleaner_stats(array $files): array
{
    $stats = ['all' => count($files), 'used' => 0, 'registered' => 0, 'unused' => 0, 'protected' => 0, 'insufficient' => 0, 'size' => 0, 'trash' => 0];
    foreach ($files as $file) {
        $status = isset($file['status'], $stats[$file['status']]) ? $file['status'] : 'protected';
        $stats[$status]++;
        $stats['size'] += (int)$file['size'];
        if ($status === 'unused') $stats['trash'] += (int)$file['size'];
    }
    return $stats;
}

function filecleaner_deletion_log(): array
{
    $file = filecleaner_storage('deletions.log');
    if (!is_file($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $cutoff = time() - 30 * 86400;
    $keptLines = [];
    $entries = [];
    foreach ($lines as $line) {
        $parts = explode("\t", $line, 3);
        if (count($parts) !== 3) continue;
        $timestamp = strtotime($parts[0]);
        if ($timestamp === false || $timestamp < $cutoff) continue;
        $keptLines[] = $line;
        $entries[] = [
            'date' => date('d.m.Y H:i:s', $timestamp),
            'path' => $parts[1],
            'size_label' => filecleaner_size((int)$parts[2]),
        ];
    }
    if (count($keptLines) !== count($lines)) {
        file_put_contents($file, $keptLines ? implode("\n", $keptLines) . "\n" : '', LOCK_EX);
    }
    return array_reverse($entries);
}

function filecleaner_clear_deletion_log(): void
{
    $file = filecleaner_storage('deletions.log');
    if (is_file($file)) @unlink($file);
}

function filecleaner_clear_operations_log(): void
{
    $file = filecleaner_storage('operations.log');
    if (is_file($file)) @unlink($file);
}

function filecleaner_operations_log(): array
{
    global $lang;
    $file = filecleaner_storage('operations.log');
    if (!is_file($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $cutoff = time() - 30 * 86400;
    $keptLines = [];
    $entries = [];
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (!is_array($entry)) continue;
        $timestamp = strtotime((string)($entry['date'] ?? ''));
        if ($timestamp === false || $timestamp < $cutoff) continue;
        $keptLines[] = $line;
        $action = (string)($entry['action'] ?? '');
        $status = (string)($entry['status'] ?? '');
        $entries[] = [
            'date' => date('d.m.Y H:i:s', $timestamp),
            'action' => $lang['filecleaner:operation_action_' . $action] ?? $action,
            'status' => $lang['filecleaner:operation_status_' . $status] ?? $status,
            'details' => json_encode($entry['details'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }
    if (count($keptLines) !== count($lines)) {
        file_put_contents($file, $keptLines ? implode("\n", $keptLines) . "\n" : '', LOCK_EX);
    }
    return array_reverse($entries);
}

function filecleaner_render(array $toasts = []): void
{
    global $twig, $lang;
    $scan = filecleaner_load_scan();
    $filter = (string)($_GET['status'] ?? '');
    $typeFilter = (string)($_GET['type'] ?? '');
    $sizeFilter = (string)($_GET['size'] ?? '');
    $sort = (string)($_GET['sort'] ?? 'name');
    $order = strtolower((string)($_GET['order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
    $query = strtolower((string)($_GET['q'] ?? ''));
    $sizeRanges = [
        'small' => [0, 102400],
        'medium' => [102400, 1048576],
        'large' => [1048576, 10485760],
        'huge' => [10485760, PHP_INT_MAX],
    ];
    $files = [];
    $uploadsRoot = realpath(filecleaner_root());
    foreach ($scan['files'] as $file) {
        $relativePath = filecleaner_normalize((string)($file['path'] ?? ''));
        if (!filecleaner_is_allowed_relative($relativePath)) continue;
        $physicalPath = $uploadsRoot === false ? false : realpath($uploadsRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        if (!$physicalPath || !is_file($physicalPath) || strpos($physicalPath, $uploadsRoot . DIRECTORY_SEPARATOR) !== 0) continue;
        $file['path'] = $relativePath;
        if ($filter && $file['status'] !== $filter) continue;
        if ($typeFilter && filecleaner_type($file['path']) !== $typeFilter) continue;
        if ($sizeFilter && isset($sizeRanges[$sizeFilter])) {
            [$minSize, $maxSize] = $sizeRanges[$sizeFilter];
            if ((int)$file['size'] < $minSize || (int)$file['size'] >= $maxSize) continue;
        }
        if ($query && strpos(strtolower($file['path']), $query) === false) continue;
        $file['size_label'] = filecleaner_size((int)$file['size']);
        $file['date_label'] = date('d.m.Y H:i', (int)$file['mtime']);
        $file['url'] = filecleaner_url($file['path']);
        $file['is_image'] = filecleaner_is_image($file['path']);
        $file['type'] = filecleaner_type($file['path']);
        $file['can_delete'] = $file['status'] === 'unused';
        $files[] = $file;
    }
    usort($files, static function (array $left, array $right) use ($sort, $order): int {
        if ($sort === 'size') $result = (int)$left['size'] <=> (int)$right['size'];
        elseif ($sort === 'date') $result = (int)$left['mtime'] <=> (int)$right['mtime'];
        elseif ($sort === 'age') $result = (int)$right['mtime'] <=> (int)$left['mtime'];
        else $result = strnatcasecmp((string)$left['path'], (string)$right['path']);
        return $order === 'desc' ? -$result : $result;
    });
    $totalFiles = count($files);
    $perPageOptions = [30, 50, 70, 100, 200];
    $perPage = (int)($_GET['per_page'] ?? 30);
    if (!in_array($perPage, $perPageOptions, true)) $perPage = 30;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $pageCount = max(1, (int)ceil($totalFiles / $perPage));
    $page = min($page, $pageCount);
    $files = array_slice($files, ($page - 1) * $perPage, $perPage);
    $pageUrl = admin_url . '/admin.php?mod=extra-config&plugin=filecleaner'
        . '&q=' . rawurlencode((string)($_GET['q'] ?? ''))
        . '&status=' . rawurlencode($filter)
        . '&type=' . rawurlencode($typeFilter)
        . '&size=' . rawurlencode($sizeFilter)
        . '&sort=' . rawurlencode($sort)
        . '&order=' . rawurlencode($order)
        . '&per_page=' . $perPage
        . '&page=%page%';
    $deletionLog = filecleaner_deletion_log();
    $logPerPage = 30;
    $logPage = max(1, (int)($_GET['log_page'] ?? 1));
    $logPageCount = max(1, (int)ceil(count($deletionLog) / $logPerPage));
    $logPage = min($logPage, $logPageCount);
    $deletionLog = array_slice($deletionLog, ($logPage - 1) * $logPerPage, $logPerPage);
    $logPageUrl = admin_url . '/admin.php?mod=extra-config&plugin=filecleaner&log_page=%page%';
    $operationsLog = filecleaner_operations_log();
    $operationPage = max(1, (int)($_GET['operation_page'] ?? 1));
    $operationPageCount = max(1, (int)ceil(count($operationsLog) / $logPerPage));
    $operationPage = min($operationPage, $operationPageCount);
    $operationsLog = array_slice($operationsLog, ($operationPage - 1) * $logPerPage, $logPerPage);
    $operationPageUrl = admin_url . '/admin.php?mod=extra-config&plugin=filecleaner&operation_page=%page%';
    $stats = filecleaner_stats($scan['files']);
    $stats['trash_label'] = filecleaner_size((int)$stats['trash']);
    $tpath = locatePluginTemplates(['config/main', 'config/automation'], 'filecleaner', 1);
    $main = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
    $automation = $twig->loadTemplate($tpath['config/automation'] . 'config/automation.tpl');
    $cfg = filecleaner_config();
    $cronSettings = filecleaner_cron_settings();
    $availableDirs = filecleaner_available_dirs();
    $days = [0, 1, 3, 7, 14, 30, 60, 90];
    $automationHtml = $automation->render([
        'lang' => $lang,
        'token' => filecleaner_token(),
        'files' => $files,
        'stats' => $stats,
        'query' => (string)($_GET['q'] ?? ''),
        'filter' => $filter,
        'type_filter' => $typeFilter,
        'size_filter' => $sizeFilter,
        'sort' => $sort,
        'order' => $order,
        'per_page' => $perPage,
        'per_page_options' => $perPageOptions,
        'page' => $page,
        'page_count' => $pageCount,
        'total_files' => $totalFiles,
        'pagelist' => generateAdminPagelist(['current' => $page, 'count' => $pageCount, 'url' => $pageUrl]),
        'size_ranges' => ['small', 'medium', 'large', 'huge'],
        'scanned_at' => $scan['scanned_at'] ? date('d.m.Y H:i:s', $scan['scanned_at']) : $lang['filecleaner:not_scanned'],
        'config' => $cfg,
        'cron' => $cronSettings,
        'protect_days' => $days,
        'available_dirs' => $availableDirs,
        'deletion_log' => $deletionLog,
        'log_page_count' => $logPageCount,
        'log_pagelist' => generateAdminPagelist(['current' => $logPage, 'count' => $logPageCount, 'url' => $logPageUrl]),
        'operations_log' => $operationsLog,
        'operation_page_count' => $operationPageCount,
        'operation_pagelist' => generateAdminPagelist(['current' => $operationPage, 'count' => $operationPageCount, 'url' => $operationPageUrl]),
        'toast_json' => json_encode($toasts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
    ]);
    echo $main->render(['lang' => $lang, 'entries' => $automationHtml, 'current_title' => $lang['filecleaner:title']]);
}

$action = (string)($_REQUEST['action'] ?? '');
$toasts = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filecleaner_check_token()) {
    if ($action === 'scan') {
        if (!filecleaner_scan_ready()) {
            $toasts[] = ['type' => 'error', 'message' => $lang['filecleaner:scan_select_dirs']];
        } else {
            filecleaner_scan();
            $toasts[] = ['type' => 'success', 'message' => $lang['filecleaner:scan_complete']];
        }
    } elseif ($action === 'delete') {
        $items = isset($_POST['single']) ? [$_POST['single']] : (array)($_POST['files'] ?? []);
        $successes = [];
        $errors = [];
        foreach ($items as $item) {
            [$success, $result] = filecleaner_delete((string)$item);
            if ($success) $successes[] = $result;
            else $errors[] = $result;
        }
        if ($successes) $toasts[] = ['type' => 'success', 'message' => implode(' ', $successes)];
        if ($errors) $toasts[] = ['type' => 'error', 'message' => implode(' ', $errors)];
        if (!$items) $toasts[] = ['type' => 'error', 'message' => $lang['filecleaner:no_selected_files']];
    } elseif ($action === 'settings') {
        $days = (int)($_POST['protect_days'] ?? 30);
        $days = in_array($days, [0, 1, 3, 7, 14, 30, 60, 90], true) ? $days : 30;
        pluginSetVariable('filecleaner', 'protect_days', (string)$days);
        pluginSetVariable('filecleaner', 'excluded_dirs', trim((string)($_POST['excluded_dirs'] ?? '')));
        $maskOptions = filecleaner_mask_options();
        $selectedMasks = array_values(array_intersect($maskOptions, array_map('strval', (array)($_POST['selected_masks'] ?? []))));
        $customMasks = preg_split('/\R+/', trim((string)($_POST['custom_masks'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $customMasks = array_values(array_filter(array_map('trim', $customMasks), static function (string $mask): bool {
            return $mask !== '' && strlen($mask) <= 100 && preg_match('/^[^\\\/\r\n]+$/u', $mask) === 1;
        }));
        pluginSetVariable('filecleaner', 'excluded_masks', implode("\n", array_values(array_unique(array_merge($selectedMasks, $customMasks)))));
        $availableDirs = filecleaner_available_dirs();
        $selectedDirs = [];
        foreach ((array)($_POST['selected_dirs'] ?? []) as $directory) {
            $directory = (string)$directory;
            if (isset($availableDirs[$directory])) $selectedDirs[] = $directory;
        }
        pluginSetVariable('filecleaner', 'selected_dirs', json_encode(array_values(array_unique($selectedDirs)), JSON_UNESCAPED_UNICODE));
        pluginSetVariable('filecleaner', 'cron_enabled', !empty($_POST['cron_enabled']) ? '1' : '0');
        $cronMinute = (string)($_POST['cron_minute'] ?? '15');
        $cronHour = (int)($_POST['cron_hour'] ?? 3);
        pluginSetVariable('filecleaner', 'cron_minute', in_array($cronMinute, ['0', '15', '30', '45'], true) ? $cronMinute : '15');
        pluginSetVariable('filecleaner', 'cron_hour', ($cronHour >= 0 && $cronHour <= 23) ? (string)$cronHour : '3');
        pluginsSaveConfig();
        filecleaner_register_cron();
        $toasts[] = ['type' => 'success', 'message' => $lang['filecleaner:settings_saved']];
    } elseif ($action === 'clear_log') {
        filecleaner_clear_deletion_log();
        $toasts[] = ['type' => 'success', 'message' => $lang['filecleaner:log_cleared']];
    } elseif ($action === 'clear_operations_log') {
        filecleaner_clear_operations_log();
        $toasts[] = ['type' => 'success', 'message' => $lang['filecleaner:operations_log_cleared']];
    }
}

filecleaner_render($toasts);
