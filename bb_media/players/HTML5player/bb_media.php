<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

add_act('index', 'bbMediaInclude');
function bbMediaInclude() {
    // Удалены все зависимости VideoJS
    // Добавляем базовые стили для HTML5 плеера (опционально)
    register_htmlvar('css', '
        video {
            max-width: 100%;
            height: auto;
            background: #000;
        }
        .media-container {
            margin: 15px 0;
        }
    ');
}

function bbMediaProcess($content) {
    global $parse;
    
    if (preg_match_all("#\[media(\=| *)(.*?)\](.*?)\[\/media\]#is", $content, $pcatch, PREG_SET_ORDER)) {
        $rsrc = array();
        $rdest = array();
        
        foreach ($pcatch as $catch) {
            list ($line, $null, $paramLine, $alt) = $catch;
            array_push($rsrc, $line);
            
            // Парсинг параметров
            $keys = array();
            if (trim($paramLine)) {
                $keys = $parse->parseBBCodeParams((($null == '=') ? 'file=' : '') . $paramLine);
                if (!is_array($keys)) {
                    array_push($rdest, '[INVALID MEDIA BB CODE]');
                    continue;
                }
            }
            
            $keys['file'] = (!empty($keys['file']) ? $keys['file'] : $alt;
            
            // Обработка YouTube
            if (preg_match('~(?:youtube\.com|youtu\.be)/(?:watch\?v=)?([^&]+)~i', $keys['file'], $matches)) {
                $videoId = $matches[1];
                $width = !empty($keys['width']) ? $keys['width'] : 640;
                $height = !empty($keys['height']) ? $keys['height'] : 360;
                
                $ytParams = [
                    'rel' => 0,
                    'controls' => isset($keys['controls']) ? $keys['controls'] : 1,
                    'autoplay' => isset($keys['autoplay']) ? $keys['autoplay'] : 0
                ];
                
                $iframe = '<div class="media-container"><iframe width="'.$width.'" height="'.$height.'" 
                    src="https://www.youtube.com/embed/'.$videoId.'?'.http_build_query($ytParams).'" 
                    frameborder="0" allowfullscreen></iframe></div>';
                
                array_push($rdest, $iframe);
                continue;
            }
            
            // Обработка обычных видео
            $attributes = '';
            $defaults = [
                'width' => 640,
                'height' => 360,
                'controls' => true,
                'preload' => 'metadata'
            ];
            
            foreach ($defaults as $attr => $value) {
                if (isset($keys[$attr])) {
                    $attributes .= ' '.$attr.'="'.htmlspecialchars($keys[$attr]).'"';
                } else {
                    $attributes .= ' '.$attr.'="'.htmlspecialchars($value).'"';
                }
            }
            
            // Добавляем poster если указан
            if (!empty($keys['preview']) && preg_match("#\.(png|jpg|jpeg|gif)$#i", $keys['preview'])) {
                $attributes .= ' poster="'.htmlspecialchars($keys['preview']).'"';
            }
            
            $videoTag = '<div class="media-container"><video'.$attributes.'>
                <source src="'.htmlspecialchars($keys['file']).'" type="video/mp4">
                Ваш браузер не поддерживает HTML5 видео.
                </video></div>';
            
            array_push($rdest, $videoTag);
        }
        
        return str_replace($rsrc, $rdest, $content);
    }
    
    return $content;
}