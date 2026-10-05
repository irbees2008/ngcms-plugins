<?php
if (!defined('NGCMS')) die('HAL');

LoadPluginLang('weather', 'main', '', '', ':');

function plugin_weather_show($params)
{
    global $config, $twig, $lang;

    // Получаем параметры
    $api_key = pluginGetVariable('weather', 'api_key');
    $city = isset($params['city']) ? $params['city'] : 'Moscow';
    $units = isset($params['units']) ? $params['units'] : 'metric';
    $template_name = isset($params['template']) ? $params['template'] : 'weather';
    $cache_expire = pluginGetVariable('weather', 'cache_expire');

    // Генерируем ключ кеша
    $cache_key = md5('weather_' . $city . '_' . $units . '_' . $config['theme'] . '_' . $config['default_lang']);

    // Пытаемся получить из кеша
    if ($cache_expire > 0) {
        $cached = cacheRetrieveFile($cache_key . '.txt', $cache_expire, 'weather');
        if ($cached !== false) {
            return $cached;
        }
    }

    // Получаем данные о погоде
    $weather_language = ($config['default_lang'] == 'english') ? 'en' : 'ru';
    $weather_data = get_weather_data($city, $api_key, $units, $weather_language);

    if (!$weather_data) {
        return $lang['weather:error'];
    }

    // Добавляем единицы измерения
    $weather_data['units'] = ($units == 'metric') ? '°C' : '°F';
    $weather_data['wind_unit'] = ($units == 'metric') ? $lang['weather:wind_unit_metric'] : $lang['weather:wind_unit_imperial'];

    // Готовим шаблон
    $tpath = locatePluginTemplates(array($template_name), 'weather', pluginGetVariable('weather', 'localsource'));

    // Загружаем шаблон через TWIG
    $template = $twig->loadTemplate($tpath[$template_name] . $template_name . '.tpl');
    $output = $template->render(array(
        'weather' => $weather_data,
        'lang' => array(
            'title' => $lang['weather:title'],
            'humidity' => $lang['weather:humidity'],
            'wind' => $lang['weather:wind']
        ),
        'tpl_url' => tpl_url
    ));

    // Сохраняем в кеш
    if ($cache_expire > 0) {
        cacheStoreFile($cache_key . '.txt', $output, 'weather');
    }

    return $output;
}

function get_weather_data($city, $api_key, $units, $language = 'ru')
{
    $url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $api_key . "&units=" . $units . "&lang=" . $language;

    $response = @file_get_contents($url);
    if (!$response) return false;

    $data = json_decode($response, true);
    if ($data['cod'] != 200) return false;

    return array(
        'city' => $data['name'],
        'temp' => round($data['main']['temp']),
        'icon' => "http://openweathermap.org/img/w/" . $data['weather'][0]['icon'] . ".png",
        'description' => $data['weather'][0]['description'],
        'humidity' => $data['main']['humidity'],
        'wind' => $data['wind']['speed']
    );
}

// Регистрируем TWIG функцию
twigRegisterFunction('weather', 'show', 'plugin_weather_show');
