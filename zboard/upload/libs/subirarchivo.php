<?php
function zboardUploadReply($status, $message, $data = [])
{
    http_response_code($status);
    echo json_encode($data ?: ['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$rootpath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
if ($rootpath === '') {
    zboardUploadReply(400, 'Некорректный запрос.');
}
@include_once $rootpath . '/engine/core.php';
if (!defined('NGCMS')) {
    zboardUploadReply(403, 'Доступ запрещён.');
}
header('Content-Type: application/json; charset=UTF-8');

$token = (string)($_POST['token'] ?? '');
$sessionToken = (string)($_SESSION['zboard']['upload_token'] ?? '');
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$uploadSids = $_SESSION['zboard']['upload_sids'] ?? [];
if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token) || !$id || !is_array($uploadSids) || !isset($uploadSids[$id]) || (int)$uploadSids[$id] < time() - 7200) {
    zboardUploadReply(403, 'Сессия загрузки недействительна. Обновите страницу.');
}

$file = $_FILES['Filedata'] ?? null;
if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    zboardUploadReply(400, 'Файл не загружен.');
}

$size = (int)($file['size'] ?? 0);
$maxSizeMb = (int)pluginGetVariable('zboard', 'max_image_size');
if ($maxSizeMb < 1) {
    $maxSizeMb = 5;
}
if ($size < 1 || $size > $maxSizeMb * 1024 * 1024) {
    zboardUploadReply(413, 'Превышен допустимый размер файла.');
}

$imageInfo = @getimagesize($file['tmp_name']);
$extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
$configuredExtensions = preg_split('/[;,|]+/', strtolower((string)pluginGetVariable('zboard', 'ext_image')));
$configuredExtensions = array_filter(array_map(function ($value) {
    return ltrim(trim($value), '*.');
}, $configuredExtensions));
$allowedByType = [
    IMAGETYPE_JPEG => ['jpg', 'jpeg'],
    IMAGETYPE_PNG => ['png'],
    IMAGETYPE_GIF => ['gif'],
];
if (!is_array($imageInfo) || !isset($allowedByType[$imageInfo[2]]) || !in_array($extension, $allowedByType[$imageInfo[2]], true) || ($configuredExtensions && !in_array($extension, $configuredExtensions, true))) {
    zboardUploadReply(415, 'Разрешены только изображения JPG, PNG и GIF.');
}
if (!function_exists('imagecreatefromstring')) {
    zboardUploadReply(500, 'Обработка изображений недоступна на сервере.');
}

$maxWidth = (int)pluginGetVariable('zboard', 'width');
$maxHeight = (int)pluginGetVariable('zboard', 'height');
$maxWidth = min($maxWidth > 0 ? $maxWidth : 2000, 8192);
$maxHeight = min($maxHeight > 0 ? $maxHeight : 2000, 8192);
$width = (int)$imageInfo[0];
$height = (int)$imageInfo[1];
if ($width < 1 || $height < 1 || $width > $maxWidth || $height > $maxHeight || $width * $height > 20000000) {
    zboardUploadReply(413, 'Размеры изображения превышают допустимые.');
}

$targetPath = $rootpath . '/uploads/zboard/';
$targetThumbPath = $targetPath . 'thumb/';
foreach ([$targetPath, $targetThumbPath] as $directory) {
    if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
        zboardUploadReply(500, 'Не удалось подготовить каталог загрузки.');
    }
    if (!is_writable($directory)) {
        zboardUploadReply(500, 'Нет прав на запись в каталог загрузки.');
    }
}

try {
    $imagen = bin2hex(random_bytes(16)) . '.' . $extension;
} catch (Exception $e) {
    $imagen = md5(uniqid((string)mt_rand(), true)) . '.' . $extension;
}
$targetFile = $targetPath . $imagen;
$targetThumb = $targetThumbPath . $imagen;
$source = @imagecreatefromstring(file_get_contents($file['tmp_name']));
if (!$source) {
    zboardUploadReply(415, 'Не удалось обработать изображение.');
}

$thumbWidth = (int)pluginGetVariable('zboard', 'width_thumb');
$thumbWidth = min($thumbWidth > 0 ? $thumbWidth : 200, 2000, $width);
$thumbHeight = max(1, (int)round($height * ($thumbWidth / $width)));
$thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
if (!$thumbnail) {
    imagedestroy($source);
    zboardUploadReply(500, 'Не удалось создать миниатюру.');
}
if ($imageInfo[2] === IMAGETYPE_PNG || $imageInfo[2] === IMAGETYPE_GIF) {
    imagealphablending($thumbnail, false);
    imagesavealpha($thumbnail, true);
    $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
    imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);
}
imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
$imageSaved = false;
if ($imageInfo[2] === IMAGETYPE_JPEG) {
    $imageSaved = imagejpeg($source, $targetFile, 90);
} elseif ($imageInfo[2] === IMAGETYPE_PNG) {
    $imageSaved = imagepng($source, $targetFile, 7);
} elseif ($imageInfo[2] === IMAGETYPE_GIF) {
    $imageSaved = imagegif($source, $targetFile);
}
$thumbSaved = false;
if ($imageInfo[2] === IMAGETYPE_JPEG) {
    $thumbSaved = imagejpeg($thumbnail, $targetThumb, 90);
} elseif ($imageInfo[2] === IMAGETYPE_PNG) {
    $thumbSaved = imagepng($thumbnail, $targetThumb, 7);
} elseif ($imageInfo[2] === IMAGETYPE_GIF) {
    $thumbSaved = imagegif($thumbnail, $targetThumb);
}
imagedestroy($source);
imagedestroy($thumbnail);
if (!$imageSaved || !$thumbSaved) {
    @unlink($targetFile);
    @unlink($targetThumb);
    zboardUploadReply(500, 'Не удалось сохранить изображение.');
}

global $mysql;
$inserted = $mysql->query('INSERT INTO ' . prefix . '_zboard_images (`filepath`, `zid`) VALUES (' . db_squote($imagen) . ', ' . db_squote($id) . ')');
if (!$inserted) {
    @unlink($targetFile);
    @unlink($targetThumb);
    zboardUploadReply(500, 'Не удалось сохранить данные изображения.');
}
$pid = (int)$mysql->result('SELECT LAST_INSERT_ID() as id');
echo json_encode(['pid' => $pid, 'filepath' => $imagen], JSON_UNESCAPED_UNICODE);
