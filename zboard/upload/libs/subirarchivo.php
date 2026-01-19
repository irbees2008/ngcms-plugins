<?php
$rootpath = $_SERVER['DOCUMENT_ROOT'];
@include_once $rootpath . '/engine/core.php';
// Создание папок, если не существуют
$targetPath = $rootpath . '/uploads/zboard/';
$targetThumbPath = $rootpath . '/uploads/zboard/thumb/';
if (!is_dir($targetPath)) {
    mkdir($targetPath, 0777, true);
}
if (!is_dir($targetThumbPath)) {
    mkdir($targetThumbPath, 0777, true);
}
try {
    $arrayreempla = array("/", "");
    $archivo = str_replace($arrayreempla, " ", $_FILES['Filedata']['name']);
    $tempFile = $_FILES['Filedata']['tmp_name'];
    $imagen = time() . "-" . $archivo;
    $id = intval($_REQUEST['id']);
    $targetFile = str_replace("//", "/", $targetPath) . $imagen;
    $targetThumb = str_replace("//", "/", $targetThumbPath) . $imagen;
    $fileParts = pathinfo($_FILES['Filedata']['name']);
    $extension = strtolower($fileParts['extension']);
    // Проверка прав на запись
    if (!is_writable($targetPath) || !is_writable($targetThumbPath)) {
        echo "Ошибка: нет прав на запись в папку загрузки.";
        exit;
    }
    // Сначала перемещаем файл
    if (!move_uploaded_file($tempFile, $targetFile)) {
        echo "Ошибка: не удалось переместить файл.";
        exit;
    }
    // Запись в базу
    $resultadoi = $mysql->query("INSERT INTO " . prefix . "_zboard_images (`filepath`, `zid`) VALUES ('" . $imagen . "', '" . $id . "')");
    if (!$resultadoi) {
        echo "Ошибка: не удалось записать в базу.";
        exit;
    }
    $pid = intval($mysql->result('SELECT LAST_INSERT_ID() as id'));
    // Создание превью
    $src = false;
    if ($extension == "jpg" || $extension == "jpeg") {
        $src = @imagecreatefromjpeg($targetFile);
    } elseif ($extension == "png") {
        $src = @imagecreatefrompng($targetFile);
    } elseif ($extension == "gif") {
        $src = @imagecreatefromgif($targetFile);
    }
    if ($src) {
        list($width, $height) = getimagesize($targetFile);
        $newwidth = intval(pluginGetVariable('zboard', 'width_thumb'));
        if ($newwidth < 1) $newwidth = 200;
        $newheight = ($height / $width) * $newwidth;
        $tmp = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $thumbname = $targetThumb;
        if (file_exists($thumbname)) {
            unlink($thumbname);
        }
        // Сохраняем превью в нужном формате
        if ($extension == "jpg" || $extension == "jpeg") {
            imagejpeg($tmp, $thumbname, 90);
        } elseif ($extension == "png") {
            imagepng($tmp, $thumbname, 9);
        } elseif ($extension == "gif") {
            imagegif($tmp, $thumbname);
        }
        imagedestroy($src);
        imagedestroy($tmp);
    }
    // Логируем успех загрузки
    $logFile = dirname(__DIR__) . '/upload_log.log';
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " | id=$id | file=$imagen | pid=$pid | OK\n", FILE_APPEND);
    echo json_encode(['pid' => $pid, 'filepath' => $imagen], JSON_UNESCAPED_UNICODE);
} catch (Exception $ex) {
    $logFile = dirname(__DIR__) . '/upload_log.log';
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " | id=$id | ERROR: " . $ex->getMessage() . "\n", FILE_APPEND);
    echo "Ошибка: " . $ex->getMessage();
}
