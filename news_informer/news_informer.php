<?php
if (!defined('NGCMS')) die('HAL');
// Регистрация TWIG-функции
twigRegisterFunction('news_informer', 'show', 'plugin_news_informer_showTwig');
// Автоматический вывод (если не используется TWIG режим)
if (!pluginGetVariable('news_informer', 'mode')) {
    add_act('index', 'plugin_news_informer');
}
// Регистрируем новый обработчик для вывода данных
register_plugin_page('news_informer', 'embed', 'plugin_news_informer_embed', 0);
function plugin_news_informer_embed()
{
    global $config;
    // Определяем режим вывода
    $mode = $_REQUEST['mode'] ?? 'js';
    if ($mode == 'html') {
        // Режим iframe
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Новости с сайта ' . $config['home'] . '</title>
            <base target="_blank">
            <style>body {margin:0; padding:0;}</style>
        </head>
        <body>
            ' . plugin_news_informer_showTwig(array()) . '
        </body>
        </html>';
    } else {
        // Режим JavaScript (по умолчанию)
        header('Content-Type: text/javascript; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        $content = plugin_news_informer_showTwig(array());
        $content = str_replace(
            array("\r", "\n", '"', '</'),
            array('', '', '\"', '<\/'),
            $content
        );
        echo 'document.write("' . $content . '");';
    }
    exit;
}
function plugin_news_informer()
{
    global $template;
    $template['vars']['plugin_news_informer'] = plugin_news_informer_showTwig(array());
}
function plugin_news_informer_showTwig($params)
{
    global $mysql, $config, $twig, $twigLoader, $catz, $catmap;
    // Получаем настройки
    $count = isset($params['count']) ? intval($params['count']) : pluginGetVariable('news_informer', 'count');
    $cacheExpire = isset($params['cacheExpire']) ? intval($params['cacheExpire']) : pluginGetVariable('news_informer', 'cacheExpire');
    $templateName = isset($params['template']) ? $params['template'] : 'news_informer';
    // Проверка количества новостей
    if (($count < 1) || ($count > 20)) $count = 5;
    // Кеширование
    $cacheFileName = md5('news_informer' . $config['theme'] . $config['default_lang'] . $count) . '.txt';
    if ($cacheExpire > 0) {
        $cacheData = cacheRetrieveFile($cacheFileName, $cacheExpire, 'news_informer');
        if ($cacheData != false) {
            return $cacheData;
        }
    }
    // Получаем пути к шаблонам
    $tpath = locatePluginTemplates(array($templateName, 'entries'), 'news_informer', pluginGetVariable('news_informer', 'localsource'));
    // Получаем новости
    $news = array();
    $query = "SELECT n.*, c.name as cat_name FROM " . prefix . "_news n
              LEFT JOIN " . prefix . "_category c ON n.catid = c.id
              WHERE n.approve=1
              ORDER BY n.postdate DESC
              LIMIT " . $count;
    foreach ($mysql->select($query) as $row) {
        // Получаем изображение новости
        $image = '';
        if ($row['image'] && file_exists(images_dir . '/' . $row['image'])) {
            $image = images_url . '/' . $row['image'];
        }
        // Форматируем дату
        $date = date('d.m.Y H:i', $row['postdate']);
        // Получаем ссылку на категорию
        $cat_link = checkLinkAvailable('news', 'by.category') ?
            generateLink('news', 'by.category', array('category' => $catmap[$row['catid']])) :
            generateLink('core', 'plugin', array('plugin' => 'news', 'handler' => 'by.category'), array('category' => $catmap[$row['catid']]));
        // Генерируем ссылку на полную новость
        $news_link = checkLinkAvailable('news', '') ?
            generateLink('news', '', array('id' => $row['id'], 'name' => $row['alt_name'])) :
            generateLink('core', 'plugin', array('plugin' => 'news'), array('id' => $row['id']));
        $news[] = array(
            'link' => $news_link,
            'title' => $row['title'],
            'image' => $image,
            'category' => array(
                'name' => $row['cat_name'],
                'link' => $cat_link
            ),
            'date' => $date
        );
    }
    // Подготавливаем переменные для шаблона
    $tVars = array(
        'entries' => $news,
        'tpl_url' => tpl_url,
        'home' => home
    );
    // Загружаем и рендерим шаблон
    $xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
    $output = $xt->render($tVars);
    // Сохраняем в кеш
    if ($cacheExpire > 0) {
        cacheStoreFile($cacheFileName, $output, 'news_informer');
    }
    return $output;
}
// Регистрация AJAX обработчиков
register_plugin_page('news_informer', 'show', 'plugin_news_informer_ajax_show', 0);
register_plugin_page('news_informer', 'frame', 'plugin_news_informer_ajax_frame', 0);
function plugin_news_informer_ajax_show()
{
    header('Content-Type: application/javascript');
    echo 'document.write(\'' . addslashes(plugin_news_informer_showTwig(array())) . '\');';
    exit;
}
function plugin_news_informer_ajax_frame()
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Новости с сайта ' . home . '</title>
        <base target="_blank">
    </head>
    <body style="margin:0; padding:0;">
        ' . plugin_news_informer_showTwig(array()) . '
    </body>
    </html>';
    exit;
}
