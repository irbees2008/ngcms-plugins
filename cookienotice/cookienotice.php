<?php
if (!defined('NGCMS')) die('HAL');

/**
 * Cookie Notice Widget Plugin
 * Показывает всплывающее уведомление об использовании кук один раз для гостей
 */
function plugin_cookienotice_show($params)
{
    global $config, $twig, $is_logged;

    // Показываем только гостям (незарегистрированным пользователям)
    $only_guests = pluginGetVariable('cookienotice', 'only_guests');
    if ($only_guests == '1' && $is_logged) {
        return '';
    }

    // Получаем настройки
    $title   = pluginGetVariable('cookienotice', 'title');
    $text    = pluginGetVariable('cookienotice', 'text');
    $btn_ok  = pluginGetVariable('cookienotice', 'btn_ok');
    $cookie_days = intval(pluginGetVariable('cookienotice', 'cookie_days'));
    $position    = pluginGetVariable('cookienotice', 'position');
    $template_name = isset($params['template']) ? $params['template'] : 'cookienotice';

    // Значения по умолчанию
    if (empty($title))       $title       = 'Использование файлов Cookie';
    if (empty($text))        $text        = 'Мы используем файлы cookie для улучшения работы сайта, анализа трафика и персонализации контента. Продолжая пользоваться сайтом, вы соглашаетесь с нашей <a href="/privacy">политикой конфиденциальности</a>.';
    if (empty($btn_ok))      $btn_ok      = 'Принять';
    if ($cookie_days <= 0)   $cookie_days = 365;
    if (empty($position))    $position    = 'bottom';

    // Загружаем шаблон
    $tpath = locatePluginTemplates(array($template_name), 'cookienotice', pluginGetVariable('cookienotice', 'localsource'));

    $template = $twig->loadTemplate($tpath[$template_name] . $template_name . '.tpl');
    $output   = $template->render(array(
        'title'       => $title,
        'text'        => $text,
        'btn_ok'      => $btn_ok,
        'cookie_days' => $cookie_days,
        'position'    => $position,
        'tpl_url'     => tpl_url
    ));

    return $output;
}

// Регистрируем TWIG функцию
twigRegisterFunction('cookienotice', 'show', 'plugin_cookienotice_show');
