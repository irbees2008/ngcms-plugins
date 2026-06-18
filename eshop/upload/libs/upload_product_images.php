<?php
$rootpath = $_SERVER['DOCUMENT_ROOT'];
@include_once $rootpath.'/engine/core.php';

try {

    $arrayreempla=array("/","");
    $targetPath = $rootpath . '/uploads/eshop/products/temp' . '/';
    $targetThumbPath = $rootpath . '/uploads/eshop/products/temp' . '/thumb/';

    $archivo= str_replace($arrayreempla," ", $_FILES['Filedata']['name']);

    $tempFile = $_FILES['Filedata']['tmp_name'];
    $imagen= $archivo;
    $id = intval($_REQUEST['id']);
    $targetFile = str_replace("//", "/", $targetPath) . $imagen;
    $targetThumb = str_replace("//", "/", $targetThumbPath) . $imagen;
    $fileParts = pathinfo ( $_FILES ['Filedata'] ['name'] );
    $extension = $fileParts ['extension'];
    
    $extensions = array_map('trim', explode(',', pluginGetVariable('eshop', 'ext_image')));

    if(!in_array($extension, $extensions)) {
        return "0";
    }

    echo "1";
    
    $pre_quality = pluginGetVariable('eshop', 'pre_quality');

    // CREATE THUMBNAIL
    if ($extension == "jpg" || $extension == "jpeg") {
        $src = imagecreatefromjpeg ( $tempFile );
    } else if ($extension == "png") {
        $src = imagecreatefrompng ( $tempFile );
    } else {
        $src = imagecreatefromgif ( $tempFile );
    }

    list ( $width, $height ) = getimagesize ( $tempFile );

    $newwidth = pluginGetVariable('eshop', 'width_thumb');
    $newheight = ($height / $width) * $newwidth;
    $tmp = imagecreatetruecolor ( $newwidth, $newheight );

    imagecopyresampled ( $tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

    $thumbname = $targetThumb;

    if (file_exists ( $thumbname )) {
        unlink ( $thumbname );
    }

    imagejpeg ( $tmp, $thumbname, ($pre_quality>=10 && $pre_quality<=100)?$pre_quality:100  );

    imagedestroy ( $src );
    imagedestroy ( $tmp );
    
    $newwidth = pluginGetVariable('eshop', 'pre_width');
    if(isset($newwidth) && ($newwidth != '0')) {
        
        if ($extension == "jpg" || $extension == "jpeg") {
            $src = imagecreatefromjpeg ( $tempFile );
        } else if ($extension == "png") {
            $src = imagecreatefrompng ( $tempFile );
        } else {
            $src = imagecreatefromgif ( $tempFile );
        }
        
        list ( $width, $height ) = getimagesize ( $tempFile );
        $newheight = ($height / $width) * $newwidth;
        $tmp = imagecreatetruecolor ( $newwidth, $newheight );
        imagecopyresampled ( $tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

        $thumbname = $targetFile;

        if (file_exists ( $thumbname )) {
            unlink ( $thumbname );
        }
        
        imagejpeg ( $tmp, $thumbname, ($pre_quality>=10 && $pre_quality<=100)?$pre_quality:100 );
        
        imagedestroy ( $src );
        imagedestroy ( $tmp );
            
    }
    else {
        if ($extension == "jpg" || $extension == "jpeg") {
            $src = imagecreatefromjpeg ( $tempFile );
        } else if ($extension == "png") {
            $src = imagecreatefrompng ( $tempFile );
        } else {
            $src = imagecreatefromgif ( $tempFile );
        }
        imagejpeg ( $src, $tempFile, ($pre_quality>=10 && $pre_quality<=100)?$pre_quality:100 );
        move_uploaded_file($tempFile, $targetFile);
        imagedestroy ( $src );
    }

} catch (Exception $ex) {
    return "0";
}

