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
loadPluginLibrary('ng-turnstile', 'autoload');

// Подгрузка языкового файла плагина.
LoadPluginLang('ng-turnstile', 'main', '', '', ':');

// Плагин использует отрисовку шаблонов, подгружаем трейт.
loadPluginLibrary('ng-helpers', 'renderable');

// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\{notify, logger, sanitize};

// Проверяем, что капчу нужно использовать только для гостей сайта.
if (setting('ng-turnstile', 'guests_only', true)) {
    global $userROW;

    if (is_array($userROW) && is_numeric($userROW['id'])) {
        return true;
    }
}

$turnstile = new Plugins\Turnstile\Turnstile();

// Добавление JavaScript API в переменную `htmlvars`.
$turnstile->registerAPIJavaScript();

// Добавление виджета в переменную `htmlvars`.
$turnstile->registerWidget();

// Создаем фильтр для регистрации
$coreFilter = new Plugins\Turnstile\Filters\TurnstileCoreFilter($turnstile);

pluginRegisterFilter('core.registerUser', 'ng-turnstile', $coreFilter);
pluginRegisterFilter('core.registrationForm', 'ng-turnstile', $coreFilter);

// Если активирован плагин комментариев.
if (getPluginStatusActive('comments')) {
    loadPluginLibrary('comments', 'lib');

    pluginRegisterFilter('comments', 'ng-turnstile', new Plugins\Turnstile\Filters\TurnstileCommentsFilter($turnstile));
}

// Если активирован плагин обратной связи.
if (getPluginStatusActive('feedback')) {
    loadPluginLibrary('feedback', 'common');

    pluginRegisterFilter('feedback', 'ng-turnstile', new Plugins\Turnstile\Filters\TurnstileFeedbackFilter($turnstile));
}
