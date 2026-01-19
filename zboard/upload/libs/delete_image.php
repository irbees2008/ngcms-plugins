<?php
$rootpath = $_SERVER['DOCUMENT_ROOT'];
@include_once $rootpath . '/engine/core.php';
header('Content-Type: text/plain; charset=utf-8');
try {
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
    $filepath = isset($_REQUEST['filepath']) ? trim($_REQUEST['filepath']) : '';
    if (!$id) {
        echo 'ERROR: invalid id';
        exit;
    }
    $row = null;
    if ($pid > 0) {
        $row = $mysql->record('SELECT * FROM ' . prefix . '_zboard_images WHERE pid = ' . db_squote($pid) . ' AND zid = ' . db_squote($id) . ' LIMIT 1');
    } elseif ($filepath !== '') {
        // Безопасим имя файла
        $safe = basename($filepath);
        $row = $mysql->record('SELECT * FROM ' . prefix . '_zboard_images WHERE filepath = ' . db_squote($safe) . ' AND zid = ' . db_squote($id) . ' LIMIT 1');
    } else {
        echo 'ERROR: no pid/filepath';
        exit;
    }
    if (!$row) {
        echo 'ERROR: not found';
        exit;
    }
    // Удаляем файлы
    $full = $rootpath . '/uploads/zboard/' . $row['filepath'];
    $thumb = $rootpath . '/uploads/zboard/thumb/' . $row['filepath'];
    if (is_file($full)) @unlink($full);
    if (is_file($thumb)) @unlink($thumb);
    // Удаляем запись
    $mysql->query('DELETE FROM ' . prefix . '_zboard_images WHERE pid = ' . db_squote($row['pid']) . ' LIMIT 1');
    echo 'OK';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
