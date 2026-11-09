<?php

// Защита от попыток взлома.
if (!defined('NGCMS')) {
    die('HAL');
}

// Если не активированы помощники, то выходим.
if (!getPluginStatusActive('ng-helpers')) {
    return false;
}

// Если помощники активированы, но по каким-то причинам не были подгружены.
load_extras('core', 'ng-helpers');

// Подгрузка библиотек-файлов плагина.
loadPluginLibrary('ng-captcha', 'autoload');

// Подгрузка языкового файла плагина.
LoadPluginLang('ng-captcha', 'main', '', '', ':');

// Плагин использует отрисовку шаблонов, подгружаем трейт.
loadPluginLibrary('ng-helpers', 'renderable');

// Используем функции из пространства `Plugins`.
use function Plugins\{setting, logger};

// Не выполнять инициализацию на странице настроек плагина
if (defined('ADMIN_FILE') && isset($_GET['mod']) && $_GET['mod'] === 'extra-config') {
    return true;
}

// Проверяем, что капчу нужно использовать только для гостей сайта.
if (function_exists('Plugins\setting') && setting('ng-captcha', 'guests_only', true)) {
    global $userROW;

    if (is_array($userROW) && is_numeric($userROW['id'])) {
        return true;
    }
}

try {
    // Создаем экземпляр капчи
    $captcha = new Plugins\Captcha\Captcha();

    // Регистрируем JavaScript API
    $captcha->registerJavaScript();

    // Регистрируем дополнительный JavaScript (для Google и Yandex)
    $captcha->registerAttachJavaScript();

    // Создаем и регистрируем фильтр для регистрации пользователей
    $coreFilter = new Plugins\Captcha\Filters\CaptchaCoreFilter($captcha);
    pluginRegisterFilter('core.registerUser', 'ng-captcha', $coreFilter);
    pluginRegisterFilter('core.registrationForm', 'ng-captcha', $coreFilter);

    // Если активирован плагин комментариев
    if (getPluginStatusActive('comments')) {
        loadPluginLibrary('comments', 'lib');

        pluginRegisterFilter('comments', 'ng-captcha', new Plugins\Captcha\Filters\CaptchaCommentsFilter($captcha));
    }

    // Если активирован плагин обратной связи
    if (getPluginStatusActive('feedback')) {
        loadPluginLibrary('feedback', 'common');

        pluginRegisterFilter('feedback', 'ng-captcha', new Plugins\Captcha\Filters\CaptchaFeedbackFilter($captcha));
    }

    if (function_exists('Plugins\logger')) {
        logger('ng-captcha: Plugin initialized with provider - ' . setting('ng-captcha', 'provider', 'google'), 'info', 'captcha.log');
    }
} catch (Exception $e) {
    // Логируем ошибку если logger доступен
    if (function_exists('Plugins\logger')) {
        logger('ng-captcha: Initialization error - ' . $e->getMessage(), 'error', 'captcha.log');
    }
}
