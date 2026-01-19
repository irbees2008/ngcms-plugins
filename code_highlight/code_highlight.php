<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, cache_get, cache_put};

// Плагин подсветки кода: подключает локальные файлы SyntaxHighlighter из папки плагина
// и активирует их на полной новости. Если в конфиге включён CDN — можно быстро
// переключиться на внешние ресурсы.

// Подключаем конфиг (если есть)
pluginsLoadConfig();

// Twig helper: проверить, включена ли кисть (с учётом дефолтов)
if (!function_exists('code_highlight_twig_brushEnabled')) {
    function code_highlight_twig_brushEnabled($params)
    {
        $name = isset($params['name']) ? mb_strtolower(trim($params['name'])) : '';
        // Сопоставление алиасов к ключам настроек плагина
        $aliases = [
            'js' => 'jscript',
            'javascript' => 'jscript',
            'node' => 'jscript',
            'nodejs' => 'jscript',
            'php' => 'php',
            'sql' => 'sql',
            'mysql' => 'sql',
            'pgsql' => 'sql',
            'postgres' => 'sql',
            'xml' => 'xml',
            'html' => 'xml',
            'xhtml' => 'xml',
            'xslt' => 'xml',
            'svg' => 'xml',
            'css' => 'css',
            'scss' => 'css',
            'sass' => 'css',
            'less' => 'css',
            'plain' => 'plain',
            'text' => 'plain',
            'txt' => 'plain',
            'bash' => 'bash',
            'shell' => 'bash',
            'sh' => 'bash',
            'zsh' => 'bash',
            'python' => 'python',
            'py' => 'python',
            'java' => 'java',
            'c#' => 'csharp',
            'csharp' => 'csharp',
            'cs' => 'csharp',
            'c++' => 'cpp',
            'cpp' => 'cpp',
            'c' => 'cpp',
            'delphi' => 'delphi',
            'pascal' => 'delphi',
            'diff' => 'diff',
            'patch' => 'diff',
            'ruby' => 'ruby',
            'rb' => 'ruby',
            'perl' => 'perl',
            'pl' => 'perl',
            'vb' => 'vb',
            'vbnet' => 'vb',
            'vba' => 'vb',
            'powershell' => 'powershell',
            'ps' => 'powershell',
            'ps1' => 'powershell',
            'scala' => 'scala',
            'groovy' => 'groovy',
        ];
        $key = isset($aliases[$name]) ? $aliases[$name] : $name;
        if ($key === '' || preg_match('~[^a-z0-9_]~', $key)) {
            return false;
        }
        $v = pluginGetVariable('code_highlight', 'enable_' . $key);
        // По умолчанию все кисти включены, если настройка отсутствует
        return ($v === null) ? true : (intval($v) !== 0);
    }
}

// Twig helper: проверить, есть ли хотя бы одна активная кисть
if (!function_exists('code_highlight_twig_hasAnyEnabled')) {
    function code_highlight_twig_hasAnyEnabled($params = [])
    {
        // Список ключей кистей, соответствующих настройкам enable_*
        $keys = [
            'jscript',
            'php',
            'sql',
            'xml',
            'css',
            'plain',
            'bash',
            'python',
            'java',
            'csharp',
            'cpp',
            'delphi',
            'diff',
            'ruby',
            'perl',
            'vb',
            'powershell',
            'scala',
            'groovy',
        ];
        foreach ($keys as $k) {
            $val = pluginGetVariable('code_highlight', 'enable_' . $k);
            // По умолчанию (отсутствие явной настройки) трактуем как включено
            if ($val === null || intval($val) !== 0) {
                return true;
            }
        }
        return false;
    }
}

// Зарегистрировать Twig-функцию code_highlight.brushEnabled
if (function_exists('twigRegisterFunction')) {
    twigRegisterFunction('code_highlight', 'brushEnabled', 'code_highlight_twig_brushEnabled');
    twigRegisterFunction('code_highlight', 'hasAnyEnabled', 'code_highlight_twig_hasAnyEnabled');
}

// Генерируем HTML для подключения SyntaxHighlighter (локально или через CDN)
function code_highlight_build_assets_html()
{
    $useCDN = intval(pluginGetVariable('code_highlight', 'use_cdn')) !== 0;
    // Значения по умолчанию, если настроек ещё нет
    if (pluginGetVariable('code_highlight', 'use_cdn') === null) {
        $useCDN = 1;
    }
    $theme = pluginGetVariable('code_highlight', 'theme');
    if (!$theme) {
        $theme = 'Default';
    }

    // Cache key based on configuration
    $cacheKey = 'code_highlight_assets_' . ($useCDN ? 'cdn' : 'local') . '_' . $theme;

    // Try to get from cache
    $cached = cache_get($cacheKey);
    if ($cached !== null) {
        return $cached;
    }

    logger('code_highlight', 'Building assets HTML: mode=' . ($useCDN ? 'CDN' : 'local') . ', theme=' . $theme);

    $html = [];

    // Функция: список включённых кистей из настроек
    $enabled = function ($k) {
        $val = pluginGetVariable('code_highlight', 'enable_' . $k);
        if ($val === null) return true; // по умолчанию включено
        return intval($val) !== 0;
    };

    if ($useCDN) {
        // CDN версии
        $html[] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/styles/shCore.min.css" />';
        $html[] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/styles/shTheme' . htmlspecialchars($theme) . '.min.css" />';
        $html[] = '<script src="https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shCore.min.js"></script>';
        $map = array(
            'jscript'    => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushJScript.min.js',
            'php'        => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushPhp.min.js',
            'sql'        => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushSql.min.js',
            'xml'        => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushXml.min.js',
            'css'        => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushCss.min.js',
            'plain'      => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushPlain.min.js',
            // Дополнительные
            'bash'       => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushBash.min.js',
            'python'     => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushPython.min.js',
            'java'       => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushJava.min.js',
            'csharp'     => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushCSharp.min.js',
            'cpp'        => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushCpp.min.js',
            'delphi'     => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushDelphi.min.js',
            'diff'       => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushDiff.min.js',
            'ruby'       => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushRuby.min.js',
            'perl'       => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushPerl.min.js',
            'vb'         => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushVb.min.js',
            'powershell' => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushPowerShell.min.js',
            'scala'      => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushScala.min.js',
            'groovy'     => 'https://cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushGroovy.min.js',
        );
        foreach ($map as $k => $src) {
            if ($enabled($k)) {
                $html[] = '<script src="' . $src . '"></script>';
            }
        }
    } else {
        // Локальные версии из папки плагина
        global $config;
        $home = isset($config['home_url']) ? rtrim($config['home_url'], '/') : '';
        $base = $home . '/engine/plugins/code_highlight';
        $html[] = '<link rel="stylesheet" href="' . $base . '/styles/shCore.css" />';
        $html[] = '<link rel="stylesheet" href="' . $base . '/styles/shTheme' . htmlspecialchars($theme) . '.css" />';
        $html[] = '<script src="' . $base . '/scripts/shCore.js"></script>';
        // Локальные файлы: подключаем только те, что реально присутствуют в папке плагина
        $mapLocal = array(
            'jscript'    => 'shBrushJScript.js',
            'php'        => 'shBrushPhp.js',
            'sql'        => 'shBrushSql.js',
            'xml'        => 'shBrushXml.js',
            'css'        => 'shBrushCss.js',
            'plain'      => 'shBrushPlain.js',
            // Дополнительные
            'bash'       => 'shBrushBash.js',
            'python'     => 'shBrushPython.js',
            'java'       => 'shBrushJava.js',
            'csharp'     => 'shBrushCSharp.js',
            'cpp'        => 'shBrushCpp.js',
            'delphi'     => 'shBrushDelphi.js',
            'diff'       => 'shBrushDiff.js',
            'ruby'       => 'shBrushRuby.js',
            'perl'       => 'shBrushPerl.js',
            'vb'         => 'shBrushVb.js',
            'powershell' => 'shBrushPowerShell.js',
            'scala'      => 'shBrushScala.js',
            'groovy'     => 'shBrushGroovy.js',
        );
        $dirFS = __DIR__ . '/scripts/';
        foreach ($mapLocal as $k => $file) {
            if (!$enabled($k)) {
                continue;
            }
            $fs = $dirFS . $file;
            if (file_exists($fs)) {
                $html[] = '<script src="' . $base . '/scripts/' . $file . '"></script>';
            }
        }
    }

    // Настройки и инициализация + кнопка копирования
    global $config;
    $home = isset($config['home_url']) ? rtrim($config['home_url'], '/') : '';
    $iconCopy  = $home . '/engine/plugins/code_highlight/images/page_white_copy.png';
    $iconTick  = $home . '/engine/plugins/code_highlight/images/tick.png';
    $iconError = $home . '/engine/plugins/code_highlight/images/error.png';
    $html[] = '<script>' .
        'SyntaxHighlighter.config.bloggerMode = true;' .
        'SyntaxHighlighter.config.stripBrs = true;' .
        'SyntaxHighlighter.defaults["auto-links"] = false;' .
        'SyntaxHighlighter.defaults["smart-tabs"] = true;' .
        'SyntaxHighlighter.defaults["wrap-lines"] = true;' .
        'SyntaxHighlighter.defaults["tab-size"] = 1;' .
        'SyntaxHighlighter.defaults.toolbar = true;' .
        'window.addEventListener("DOMContentLoaded", function(){' .
        '  try { SyntaxHighlighter.all(); } catch(e) {}' .
        '  try {' .
        '    document.querySelectorAll(".clipboard").forEach(function(el){ el.remove(); });' .
        '    document.querySelectorAll(".syntaxhighlighter").forEach(function(block){' .
        '      var toolbar = block.querySelector(".toolbar"); if (!toolbar) return;' .
        '      var copyBtn = document.createElement("a");' .
        '      copyBtn.className = "item copy-to-clipboard";' .
        '      copyBtn.title = "Копировать в буфер";' .
        '      copyBtn.innerHTML = "<img src=\"' . $iconCopy . '\" alt=\"Copy\" style=\"vertical-align:middle\">";' .
        '      var printBtn = toolbar.querySelector(".printSource");' .
        '      if (printBtn) { toolbar.insertBefore(copyBtn, printBtn); } else { toolbar.appendChild(copyBtn); }' .
        '      copyBtn.addEventListener("click", function(e){' .
        '        e.preventDefault();' .
        '        try {' .
        '          var code = block.querySelector(".container").textContent;' .
        '          if (navigator.clipboard && navigator.clipboard.writeText) {' .
        '            navigator.clipboard.writeText(code).then(function(){' .
        '              var original = copyBtn.innerHTML; copyBtn.innerHTML = "<img src=\"' . $iconTick . '\" alt=\"Copied!\" style=\"vertical-align:middle\">";' .
        '              setTimeout(function(){ copyBtn.innerHTML = original; }, 2000);' .
        '            }).catch(function(){' .
        '              var original = copyBtn.innerHTML; copyBtn.innerHTML = "<img src=\"' . $iconError . '\" alt=\"Error\" style=\"vertical-align:middle\">";' .
        '              setTimeout(function(){ copyBtn.innerHTML = "<img src=\"' . $iconCopy . '\" alt=\"Copy\" style=\"vertical-align:middle\">"; }, 2000);' .
        '            });' .
        '          }' .
        '        } catch(err) {' .
        '          var original = copyBtn.innerHTML; copyBtn.innerHTML = "<img src=\"' . $iconError . '\" alt=\"Error\" style=\"vertical-align:middle\">";' .
        '          setTimeout(function(){ copyBtn.innerHTML = "<img src=\"' . $iconCopy . '\" alt=\"Copy\" style=\"vertical-align:middle\">"; }, 2000);' .
        '        }' .
        '      });' .
        '    });' .
        '  } catch(e) {}' .
        '});' .
        '</script>';

    $result = implode("\n", $html);

    // Cache for 24 hours (configuration doesn't change often)
    cache_put($cacheKey, $result, 86400);

    // Count enabled brushes for logging
    $enabledCount = 0;
    $brushKeys = ['jscript', 'php', 'sql', 'xml', 'css', 'plain', 'bash', 'python', 'java', 'csharp', 'cpp', 'delphi', 'diff', 'ruby', 'perl', 'vb', 'powershell', 'scala', 'groovy'];
    foreach ($brushKeys as $k) {
        if ($enabled($k)) {
            $enabledCount++;
        }
    }
    logger('code_highlight', 'Assets cached: ' . $enabledCount . ' brushes enabled');

    return $result;
}

// Фильтр новостей: подключаем подсветку только на полной новости
class CodeHighlightNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
    {
        if ($mode['style'] != 'full') {
            return 1;
        }
        logger('code_highlight', 'Syntax highlighting attached for news: id=' . $newsID);
        if (function_exists('register_htmlvar')) {
            register_htmlvar('plain', code_highlight_build_assets_html());
        }
        return 1;
    }
}

// Регистрируем фильтр
register_filter('news', 'code_highlight', new CodeHighlightNewsFilter);
