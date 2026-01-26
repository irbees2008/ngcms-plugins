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
loadPluginLibrary('ng-yandex-captcha', 'autoload');

// Подгрузка языкового файла плагина.
LoadPluginLang('ng-yandex-captcha', 'main', '', '', ':');

// Плагин использует отрисовку шаблонов, подгружаем трейт.
loadPluginLibrary('ng-helpers', 'renderable');

// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\{notify, logger, sanitize};

// Проверяем, что капчу нужно использовать только для гостей сайта.
if (setting('ng-yandex-captcha', 'guests_only', true)) {
    global $userROW;

    if (is_array($userROW) && is_numeric($userROW['id'])) {
        return true;
    }
}

$yandexCaptcha = new Plugins\YandexCaptcha\YandexCaptcha();

// Добавление JavaScript API в переменную `htmlvars`.
$yandexCaptcha->registerAPIJavaScript();

// Добавление JavaScript из шаблона в переменную `htmlvars`.
$yandexCaptcha->registerAttachJavaScript();

// Создаем фильтр для регистрации
$coreFilter = new Plugins\YandexCaptcha\Filters\YandexCaptchaCoreFilter($yandexCaptcha);

pluginRegisterFilter('core.registerUser', 'ng-yandex-captcha', $coreFilter);
pluginRegisterFilter('core.registrationForm', 'ng-yandex-captcha', $coreFilter);

// Если активирован плагин комментариев.
if (getPluginStatusActive('comments')) {
    loadPluginLibrary('comments', 'lib');

    pluginRegisterFilter('comments', 'ng-yandex-captcha', new Plugins\YandexCaptcha\Filters\YandexCaptchaCommentsFilter($yandexCaptcha));
}

// Если активирован плагин обратной связи.
if (getPluginStatusActive('feedback')) {
    loadPluginLibrary('feedback', 'common');

    pluginRegisterFilter('feedback', 'ng-yandex-captcha', new Plugins\YandexCaptcha\Filters\YandexCaptchaFeedbackFilter($yandexCaptcha));
}
