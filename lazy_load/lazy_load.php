<?php
if (!defined('NGCMS')) {
    die('HAL');
}

/**
 * Lazy Load for NGCMS
 * PHP 7.2+ compatible; designed for PHP 8.x.
 *
 * Важно: плагин использует output buffering. Убедитесь, что событие index
 * вызывается до начала вывода HTML-шаблона.
 */

add_act('index', 'lazy_load_init');

function lazy_load_init()
{
    static $started = false;

    if ($started) {
        return;
    }
    $started = true;

    $enabled = extra_get_param('lazy_load', 'enabled');
    if ($enabled === null || $enabled === '') {
        $enabled = 1;
    }

    $enabled = (int) $enabled;
    if ($enabled !== 1) {
        return;
    }

    // Не обрабатываем административные страницы, AJAX и запросы не GET/HEAD.
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($method !== 'GET' && $method !== 'HEAD') {
        return;
    }

    if ($isAjax || preg_match('~/(?:engine/|admin(?:/|\.php)|admin\.php)~i', $uri)) {
        return;
    }

    if (!function_exists('lazy_load_process_html')) {
        // Callback объявлена отдельно, чтобы её можно было протестировать.
        function lazy_load_process_html($html)
        {
            if (!is_string($html) || $html === '') {
                return $html;
            }

            // Обрабатываем только полноценные HTML-страницы.
            if (stripos($html, '<html') === false || stripos($html, '<img') === false) {
                return $html;
            }

            $skipFirst = extra_get_param('lazy_load', 'skip_first');
            if ($skipFirst === null || $skipFirst === '') {
                $skipFirst = 2;
            }
            $skipFirst = (int) $skipFirst;
            if ($skipFirst < 0) {
                $skipFirst = 0;
            }
            if ($skipFirst > 20) {
                $skipFirst = 20;
            }

            $excludeClasses = lazy_load_parse_csv(
                (string) extra_get_param('lazy_load', 'exclude_classes')
            );
            $excludeUrls = lazy_load_parse_csv(
                (string) extra_get_param('lazy_load', 'exclude_urls')
            );

            $imageIndex = 0;

            $result = preg_replace_callback(
                '~<img\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>~is',
                function ($match) use (&$imageIndex, $skipFirst, $excludeClasses, $excludeUrls) {
                    $tag = $match[0];
                    $imageIndex++;

                    // Первые N изображений оставляем без изменений.
                    if ($imageIndex <= $skipFirst) {
                        return $tag;
                    }

                    // Не трогаем изображения, явно исключённые разработчиком.
                    if (preg_match('~\bdata-(?:no-)?lazy(?:load)?\b~i', $tag)) {
                        return $tag;
                    }

                    $attrs = lazy_load_get_img_attributes($tag);
                    $src = $attrs['src'] ?? '';

                    // Пропускаем изображения, которые загружаются сторонним JS
                    // через data-src, а также пустые src и data URI.
                    if ($src === '' || stripos($src, 'data:') === 0) {
                        return $tag;
                    }

                    // Не переопределяем явно заданные loading="eager" и fetchpriority="high".
                    if (isset($attrs['loading']) && strtolower($attrs['loading']) === 'eager') {
                        return $tag;
                    }
                    if (isset($attrs['fetchpriority']) && strtolower($attrs['fetchpriority']) === 'high') {
                        return $tag;
                    }

                    $classText = (string) ($attrs['class'] ?? '');
                    $idText = (string) ($attrs['id'] ?? '');

                    foreach ($excludeClasses as $excludedClass) {
                        $excludedClass = trim($excludedClass);
                        if ($excludedClass === '') {
                            continue;
                        }

                        // Совпадение по отдельному CSS-классу или точному id.
                        if (preg_match(
                            '~(?:^|\s)' . preg_quote(ltrim($excludedClass, '.'), '~') . '(?:\s|$)~',
                            $classText
                        ) || $idText === ltrim($excludedClass, '#')) {
                            return $tag;
                        }
                    }

                    foreach ($excludeUrls as $excludedUrl) {
                        $excludedUrl = trim($excludedUrl);
                        if ($excludedUrl !== '' && stripos($src, $excludedUrl) !== false) {
                            return $tag;
                        }
                    }

                    // Нативный Lazy Load: не нужен отдельный JavaScript или библиотека.
                    if (!isset($attrs['loading'])) {
                        $tag = lazy_load_set_img_attribute($tag, 'loading', 'lazy');
                    }

                    if (!isset($attrs['decoding'])) {
                        $tag = lazy_load_set_img_attribute($tag, 'decoding', 'async');
                    }

                    return $tag;
                },
                $html
            );

            return is_string($result) ? $result : $html;
        }
    }

    if (!function_exists('lazy_load_parse_csv')) {
        function lazy_load_parse_csv($value)
        {
            if (!is_string($value) || trim($value) === '') {
                return array();
            }

            return array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'));
        }
    }

    if (!function_exists('lazy_load_get_img_attributes')) {
        function lazy_load_get_img_attributes($tag)
        {
            $attributes = array();

            if (preg_match_all(
                '~([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))~',
                $tag,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $attribute) {
                    $name = strtolower($attribute[1]);
                    $value = '';
                    if (isset($attribute[2]) && $attribute[2] !== '') {
                        $value = $attribute[2];
                    } elseif (isset($attribute[3]) && $attribute[3] !== '') {
                        $value = $attribute[3];
                    } elseif (isset($attribute[4])) {
                        $value = $attribute[4];
                    }
                    $attributes[$name] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }

            // Учитываем булевы атрибуты и атрибуты без значения.
            if (preg_match('~\sloading(?:\s|/?>)~i', $tag) && !isset($attributes['loading'])) {
                $attributes['loading'] = '';
            }

            return $attributes;
        }
    }

    if (!function_exists('lazy_load_set_img_attribute')) {
        function lazy_load_set_img_attribute($tag, $name, $value)
        {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Если атрибут уже существует без значения, задаём ему значение.
            if (preg_match('~\s' . preg_quote($name, '~') . '(?=\s|/?>)~i', $tag)) {
                return preg_replace(
                    '~\s' . preg_quote($name, '~') . '(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?~i',
                    ' ' . $name . '="' . $escapedValue . '"',
                    $tag,
                    1
                );
            }

            // Вставляем атрибут перед закрывающим > или />.
            return preg_replace(
                '~\s*/?>$~',
                ' ' . $name . '="' . $escapedValue . '"$0',
                $tag,
                1
            );
        }
    }

    ob_start('lazy_load_process_html');
}
