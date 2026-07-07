<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
class OGNEWSNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        global $CurrentHandler, $config;

        // Загружаем настройки (оптимальные значения для соцсетей и SEO)
        $titleLen        = intval(pluginGetVariable('ognews', 'title_length')) ?: 60;   // Google обрезает после ~60 символов
        $descrLen        = intval(pluginGetVariable('ognews', 'description_length')) ?: 125; // Соцсети показывают ~125 символов
        $descrSource     = pluginGetVariable('ognews', 'description_source') ?: 'description';
        $twTitleLen      = intval(pluginGetVariable('ognews', 'twitter_title_length')) ?: 60;
        $twDescrLen      = intval(pluginGetVariable('ognews', 'twitter_description_length')) ?: 125;
        $keywordsLen     = intval(pluginGetVariable('ognews', 'keywords_length')) ?: 0; // 0 = без ограничения
        // Функция безопасной обрезки (UTF-8)
        $trim = function ($text, $len) {
            if (!$len) return $text;
            $text = strip_tags($text);
            if (function_exists('mb_substr')) return mb_substr($text, 0, $len, 'UTF-8');
            return substr($text, 0, $len);
        };

        // Функция получения размеров изображения
        $getImageSize = function ($imagePath) use ($config) {
            $width = 1200;  // значения по умолчанию
            $height = 630;

            // Если это URL, пытаемся получить размеры
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                // Для внешних URL пробуем getimagesize с ограничением времени
                $ctx = stream_context_create(['http' => ['timeout' => 3]]);
                $size = @getimagesize($imagePath, $info, $ctx);
                if ($size) {
                    $width = $size[0];
                    $height = $size[1];
                }
            } else {
                // Для относительных путей (например /uploads/...)
                $localPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                if (file_exists($localPath)) {
                    $size = @getimagesize($localPath);
                    if ($size) {
                        $width = $size[0];
                        $height = $size[1];
                    }
                }
            }

            return ['width' => $width, 'height' => $height];
        };

        // Функция для создания реального OG-изображения 1200×630
        $createOGImage = function ($imagePath) use ($config) {
            $targetWidth = 1200;
            $targetHeight = 630;

            // Определяем пути
            $ogDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/og/';
            $ogUrlBase = rtrim($config['home_url'], '/') . '/uploads/og/';

            // Создаем директорию если её нет
            if (!is_dir($ogDir)) {
                @mkdir($ogDir, 0755, true);
            }

            // Получаем локальный путь к исходному изображению
            $sourcePath = '';
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                // Для внешних URL - скачиваем временно или используем оригинал
                return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
            } else {
                $sourcePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $imagePath;
            }

            if (!file_exists($sourcePath)) {
                return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            // Генерируем имя для OG-версии
            $pathInfo = pathinfo($imagePath);
            $ogFileName = md5($imagePath) . '_og.' . $pathInfo['extension'];
            $ogFilePath = $ogDir . $ogFileName;
            $ogFileUrl = $ogUrlBase . $ogFileName;

            // Если OG-версия уже существует и новее оригинала - используем её
            if (file_exists($ogFilePath) && filemtime($ogFilePath) >= filemtime($sourcePath)) {
                return ['url' => $ogFileUrl, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            // Получаем размеры и тип исходного изображения
            $imageInfo = @getimagesize($sourcePath);
            if (!$imageInfo) {
                return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            list($srcWidth, $srcHeight, $srcType) = $imageInfo;

            // Если изображение уже нужного размера - используем оригинал
            if ($srcWidth == $targetWidth && $srcHeight == $targetHeight) {
                return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            // Проверяем наличие GD
            if (!function_exists('imagecreatefromjpeg')) {
                return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            // Создаем исходное изображение
            $srcImage = null;
            switch ($srcType) {
                case IMAGETYPE_JPEG:
                    $srcImage = @imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $srcImage = @imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $srcImage = @imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $srcImage = @imagecreatefromwebp($sourcePath);
                    }
                    break;
            }

            if (!$srcImage) {
                return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            // Вычисляем размеры с сохранением пропорций (crop to fit)
            $srcRatio = $srcWidth / $srcHeight;
            $targetRatio = $targetWidth / $targetHeight;

            if ($srcRatio > $targetRatio) {
                // Исходное изображение шире - обрезаем по ширине
                $newHeight = $srcHeight;
                $newWidth = round($srcHeight * $targetRatio);
                $cropX = round(($srcWidth - $newWidth) / 2);
                $cropY = 0;
            } else {
                // Исходное изображение выше - обрезаем по высоте
                $newWidth = $srcWidth;
                $newHeight = round($srcWidth / $targetRatio);
                $cropX = 0;
                $cropY = round(($srcHeight - $newHeight) / 2);
            }

            // Создаем новое изображение 1200×630
            $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

            // Сохраняем прозрачность для PNG
            if ($srcType == IMAGETYPE_PNG) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
                imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);
            }

            // Копируем и ресайзим
            imagecopyresampled(
                $dstImage,
                $srcImage,
                0,
                0,                    // dst x, y
                $cropX,
                $cropY,          // src x, y
                $targetWidth,
                $targetHeight,  // dst w, h
                $newWidth,
                $newHeight    // src w, h
            );

            // Сохраняем результат
            $saved = false;
            switch ($srcType) {
                case IMAGETYPE_JPEG:
                    $saved = @imagejpeg($dstImage, $ogFilePath, 85);
                    break;
                case IMAGETYPE_PNG:
                    $saved = @imagepng($dstImage, $ogFilePath, 8);
                    break;
                case IMAGETYPE_GIF:
                    $saved = @imagegif($dstImage, $ogFilePath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagewebp')) {
                        $saved = @imagewebp($dstImage, $ogFilePath, 85);
                    }
                    break;
            }

            // Освобождаем память
            imagedestroy($srcImage);
            imagedestroy($dstImage);

            // Возвращаем результат
            if ($saved && file_exists($ogFilePath)) {
                return ['url' => $ogFileUrl, 'width' => $targetWidth, 'height' => $targetHeight];
            }

            return ['url' => $imagePath, 'width' => $targetWidth, 'height' => $targetHeight];
        };
        if (($CurrentHandler['handlerName'] == 'news') || ($CurrentHandler['handlerName'] == 'print')) {
            if (isset($mode['style']) && $mode['style'] == 'full') {
                $alink = checkLinkAvailable('uprofile', 'show') ?
                    generateLink('uprofile', 'show', array('name' => $SQLnews['author'], 'id' => $SQLnews['author_id'])) :
                    generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $SQLnews['author'], 'id' => $SQLnews['author_id']));
                // Источник описания
                $rawDescr = ($descrSource == 'content') ? $SQLnews['content'] : $SQLnews['description'];
                $cleanDescr = stripBBCode($rawDescr);
                $ogTitle       = secure_html($trim($SQLnews['title'], $titleLen));
                $ogDescr       = secure_html($trim($cleanDescr, $descrLen));
                $twitterTitle  = secure_html($trim($SQLnews['title'], $twTitleLen));
                $twitterDescr  = secure_html($trim($cleanDescr, $twDescrLen));
                $keywords      = secure_html(($keywordsLen ? $trim($SQLnews['keywords'], $keywordsLen) : $SQLnews['keywords']));

                // Open Graph основные теги
                register_htmlvar('plain', '<meta property="og:type" content="article">');
                register_htmlvar('plain', '<meta property="og:url" content="' . home . newsGenerateLink($SQLnews) . '">');
                register_htmlvar('plain', '<meta property="og:site_name" content="' . secure_html($config["home_title"]) . '">');
                register_htmlvar('plain', '<meta property="og:title" content="' . $ogTitle . '">');
                register_htmlvar('plain', '<meta property="og:description" content="' . $ogDescr . '">');
                register_htmlvar('plain', '<meta property="og:locale" content="ru_RU">');

                // Article теги
                register_htmlvar('plain', '<meta property="article:published_time" content="' . date('c', $SQLnews['postdate']) . '">');
                if ($SQLnews['editdate']) {
                    register_htmlvar('plain', '<meta property="article:modified_time" content="' . date('c', $SQLnews['editdate']) . '">');
                }
                register_htmlvar('plain', '<meta property="article:author" content="' . home . $alink . '">');
                register_htmlvar('plain', '<meta property="article:section" content="' . explode(',', strip_tags(@GetCategories($SQLnews['catid'])))[0] . '">');
                if ($keywords) {
                    register_htmlvar('plain', '<meta property="article:tag" content="' . $keywords . '">');
                }
                if ($tvars['vars']['news']['embed']['imgCount'] > 0) {
                    foreach ($tvars['vars']['news']['embed']['images'] as $img_item) {
                        // Создаем реальное OG-изображение 1200×630
                        $ogImage = $createOGImage($img_item);

                        register_htmlvar('plain', '<meta property="og:image" content="' . $ogImage['url'] . '">');

                        // Если URL начинается с https://, добавляем secure_url
                        if (strpos($ogImage['url'], 'https://') === 0) {
                            register_htmlvar('plain', '<meta property="og:image:secure_url" content="' . $ogImage['url'] . '">');
                        }

                        register_htmlvar('plain', '<meta property="og:image:width" content="' . $ogImage['width'] . '">');
                        register_htmlvar('plain', '<meta property="og:image:height" content="' . $ogImage['height'] . '">');

                        // Определяем тип изображения по расширению
                        $ext = strtolower(pathinfo($ogImage['url'], PATHINFO_EXTENSION));
                        $mimeTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
                        if (isset($mimeTypes[$ext])) {
                            register_htmlvar('plain', '<meta property="og:image:type" content="' . $mimeTypes[$ext] . '">');
                        }

                        // Alt из заголовка новости
                        register_htmlvar('plain', '<meta property="og:image:alt" content="' . $ogTitle . '">');
                    }
                }
                if (!empty($SQLnews['#images'])) {
                    foreach ($SQLnews['#images'] as $img_item) {
                        $imgLocalPath = '/' . $img_item['folder'] . '/' . $img_item['name'];

                        // Создаем реальное OG-изображение 1200×630
                        $ogImage = $createOGImage($imgLocalPath);

                        // Получаем MIME-тип из локального файла
                        $mimeType = '';
                        $fullLocalPath = rtrim($config['attach_dir'], '/') . $imgLocalPath;
                        if (file_exists($fullLocalPath)) {
                            $size = @getimagesize($fullLocalPath);
                            if ($size) {
                                $mimeType = $size['mime'];
                            }
                        }

                        register_htmlvar('plain', '<meta property="og:image" content="' . $ogImage['url'] . '">');

                        // Если URL начинается с https://, добавляем secure_url
                        if (strpos($ogImage['url'], 'https://') === 0) {
                            register_htmlvar('plain', '<meta property="og:image:secure_url" content="' . $ogImage['url'] . '">');
                        }

                        register_htmlvar('plain', '<meta property="og:image:width" content="' . $ogImage['width'] . '">');
                        register_htmlvar('plain', '<meta property="og:image:height" content="' . $ogImage['height'] . '">');

                        if ($mimeType) {
                            register_htmlvar('plain', '<meta property="og:image:type" content="' . $mimeType . '">');
                        }

                        // Alt текст из описания изображения, если есть, иначе заголовок новости
                        $altText = !empty($img_item['description']) ? $img_item['description'] : $ogTitle;
                        register_htmlvar('plain', '<meta property="og:image:alt" content="' . secure_html($altText) . '">');
                    }
                }

                // Twitter Card теги
                register_htmlvar('plain', '<meta name="twitter:card" content="summary_large_image">');
                register_htmlvar('plain', '<meta name="twitter:title" content="' . $twitterTitle . '">');
                register_htmlvar('plain', '<meta name="twitter:description" content="' . $twitterDescr . '">');

                // Twitter изображения (используем OG-версии для качества)
                if (!empty($SQLnews['#images'])) {
                    foreach ($SQLnews['#images'] as $img_item) {
                        $imgLocalPath = '/' . $img_item['folder'] . '/' . $img_item['name'];
                        $ogImage = $createOGImage($imgLocalPath);
                        register_htmlvar('plain', '<meta name="twitter:image" content="' . $ogImage['url'] . '">');
                        break; // Twitter использует только первое изображение
                    }
                } elseif ($tvars['vars']['news']['embed']['imgCount'] > 0) {
                    foreach ($tvars['vars']['news']['embed']['images'] as $img_item) {
                        $ogImage = $createOGImage($img_item);
                        register_htmlvar('plain', '<meta name="twitter:image" content="' . $ogImage['url'] . '">');
                        break; // Twitter использует только первое изображение
                    }
                }

                // Twitter site username (опционально)
                $twitterSite = pluginGetVariable('ognews', 'twitter_site');
                if ($twitterSite) {
                    register_htmlvar('plain', '<meta name="twitter:site" content="' . secure_html($twitterSite) . '">');
                }
            }
        }
        return 1;
    }
}
function stripBBCode($text_to_search)
{
    $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
    $replace = '';
    return preg_replace($pattern, $replace, $text_to_search);
}
register_filter('news', 'ognews', new OGNEWSNewsFilter);
