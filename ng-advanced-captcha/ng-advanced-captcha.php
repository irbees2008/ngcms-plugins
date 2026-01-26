<?php

// Защита от попыток взлома.
if (!defined('NGCMS')) {
    die('HAL');
}

// Если не активированы помощники, то выходим.
if (! getPluginStatusActive('ng-helpers')) {
    return false;
}

// Если помощники активированы,
// но по каким-то причинам не были подгружены.
load_extras('core', 'ng-helpers');

// Подгрузка библиотек-файлов плагина.
loadPluginLibrary('ng-advanced-captcha', 'autoload');

// Подгрузка языкового файла плагина.
LoadPluginLang('ng-advanced-captcha', 'main', '', '', ':');

// Плагин использует отрисовку шаблонов, подгружаем трейт.
loadPluginLibrary('ng-helpers', 'renderable');

// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\{notify, logger, sanitize};

// Проверяем, что капчу нужно использовать только для гостей сайта.
if (setting('ng-advanced-captcha', 'guests_only', true)) {
    global $userROW;

    if (is_array($userROW) && is_numeric($userROW['id'])) {
        return true;
    }
}

$advancedCaptcha = new Plugins\AdvancedCaptcha\AdvancedCaptcha();

// Регистрируем маршруты для API капчи
$advancedCaptcha->registerRoutes();

// Добавление JavaScript и CSS в переменную `htmlvars`.
$advancedCaptcha->registerAssets();

// Создаем фильтр для регистрации
$coreFilter = new Plugins\AdvancedCaptcha\Filters\AdvancedCaptchaCoreFilter($advancedCaptcha);

// Регистрируем фильтр для проверки капчи при регистрации
pluginRegisterFilter('core.registerUser', 'ng-advanced-captcha', $coreFilter);

// Регистрируем фильтр для добавления виджета в форму регистрации
pluginRegisterFilter('core.registrationForm', 'ng-advanced-captcha', $coreFilter);

// Если активирован плагин комментариев.
if (getPluginStatusActive('comments')) {
    loadPluginLibrary('comments', 'lib');

    pluginRegisterFilter('comments', 'ng-advanced-captcha', new Plugins\AdvancedCaptcha\Filters\AdvancedCaptchaCommentsFilter($advancedCaptcha));
}

// Если активирован плагин обратной связи.
if (getPluginStatusActive('feedback')) {
    loadPluginLibrary('feedback', 'common');

    pluginRegisterFilter('feedback', 'ng-advanced-captcha', new Plugins\AdvancedCaptcha\Filters\AdvancedCaptchaFeedbackFilter($advancedCaptcha));
}
