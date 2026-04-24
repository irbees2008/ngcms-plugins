<?php
if (!defined('NGCMS')) die('HAL');
function plugin_ytranslate_show($params)
{
    global $twig;
    // Получаем параметры
    $position = isset($params['position']) ? $params['position'] : 'fixed';
    $theme = isset($params['theme']) ? $params['theme'] : 'light';
    $default_lang = isset($params['default_lang']) ? $params['default_lang'] : 'ru';
    // Список поддерживаемых языков
    $langs = array(
        'ru' => array('code' => 'ru', 'name' => 'Русский'),
        'en' => array('code' => 'en', 'name' => 'English'),
        'de' => array('code' => 'de', 'name' => 'Deutsch'),
        'fr' => array('code' => 'fr', 'name' => 'Français'),
        'es' => array('code' => 'es', 'name' => 'Español'),
        'uk' => array('code' => 'uk', 'name' => 'Українська'),
        'kk' => array('code' => 'kk', 'name' => 'Қазақша')
    );
    // Подготовка данных для шаблона
    $output = array(
        'position' => $position,
        'theme' => $theme,
        'default_lang' => $default_lang,
        'langs' => $langs,
        'tpl_url' => tpl_url
    );
    // Загружаем шаблон
    $tpath = locatePluginTemplates(array('translator'), 'ytranslate', pluginGetVariable('ytranslate', 'localsource'));
    $template = $twig->loadTemplate($tpath['translator'] . 'translator.tpl');
    return $template->render(array('translator' => $output));
}
// Регистрируем TWIG функцию
twigRegisterFunction('ytranslate', 'show', 'plugin_ytranslate_show');
