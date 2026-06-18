<?php
if (!defined('NGCMS')) die('HAL');

/**
 * Audio Player Widget Plugin
 * Отображает HTML5 плеер с плейлистом из указанной папки uploads
 */
function plugin_audioplayer_show($params)
{
    global $config, $twig;

    // Параметры из вызова или из настроек
    $folder       = isset($params['folder'])   ? $params['folder']   : pluginGetVariable('audioplayer', 'folder');
    $title        = isset($params['title'])    ? $params['title']    : pluginGetVariable('audioplayer', 'title');
    $autoplay     = isset($params['autoplay']) ? $params['autoplay'] : pluginGetVariable('audioplayer', 'autoplay');
    $show_list    = isset($params['show_list']) ? $params['show_list'] : pluginGetVariable('audioplayer', 'show_list');
    $skin         = isset($params['skin'])     ? $params['skin']     : pluginGetVariable('audioplayer', 'skin');
    // mode: widget | popup | page
    $mode         = isset($params['mode'])     ? $params['mode']     : pluginGetVariable('audioplayer', 'mode');
    $template_name = isset($params['template']) ? $params['template'] : 'audioplayer';

    // Значения по умолчанию
    if (empty($folder))    $folder    = 'files/music';
    if (empty($title))     $title     = 'Музыкальный плеер';
    if (empty($skin))      $skin      = 'dark';
    if (empty($mode))      $mode      = 'widget';
    if ($autoplay  === null) $autoplay  = '0';
    if ($show_list === null) $show_list = '1';

    // Формируем путь к папке на диске
    $folder = trim($folder, '/\\');
    $disk_path = site_root . '/uploads/' . $folder;
    $web_path  = home . '/uploads/' . $folder;

    // Читаем треки из папки
    $tracks = array();
    if (is_dir($disk_path)) {
        $files = @scandir($disk_path);
        if (is_array($files)) {
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!in_array($ext, array('mp3', 'ogg', 'wav', 'm4a'))) {
                    continue;
                }
                // Человекочитаемое имя: убираем расширение, заменяем _ и - на пробелы
                $name = pathinfo($file, PATHINFO_FILENAME);
                $name = preg_replace('/[_\-]+/', ' ', $name);
                // Убираем числовые хеши в конце (8+ цифр)
                $name = preg_replace('/\s+\d{6,}$/', '', $name);
                $name = trim($name);
                $tracks[] = array(
                    'file' => $web_path . '/' . rawurlencode($file),
                    'name' => $name,
                    'ext'  => $ext,
                );
            }
        }
    }

    if (empty($tracks)) {
        return '<!-- audioplayer: папка "' . htmlspecialchars($folder) . '" пуста или не найдена -->';
    }

    // Выбираем шаблон по режиму
    if ($template_name === 'audioplayer') {
        if ($mode === 'popup') {
            $template_name = 'audioplayer_popup';
        } elseif ($mode === 'page') {
            $template_name = 'audioplayer_page';
        }
    }

    // Загружаем шаблон
    $tpath = locatePluginTemplates(array($template_name), 'audioplayer', pluginGetVariable('audioplayer', 'localsource'));
    $template = $twig->loadTemplate($tpath[$template_name] . $template_name . '.tpl');

    return $template->render(array(
        'title'     => $title,
        'tracks'    => $tracks,
        'autoplay'  => $autoplay,
        'show_list' => $show_list,
        'skin'      => $skin,
        'mode'      => $mode,
        'tpl_url'   => tpl_url,
    ));
}

twigRegisterFunction('audioplayer', 'show', 'plugin_audioplayer_show');
