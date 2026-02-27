<?php

// Защита от попыток взлома.
if (!defined('NGCMS')) {
    die('HAL');
}

// Если не активированы помощники, то выходим.
if (! getPluginStatusActive('ng-helpers')) {
    return false;
}

// КРИТИЧЕСКИ ВАЖНО: Загружаем ng-helpers ПЕРЕД использованием его классов/трейтов
// Это гарантирует регистрацию автозагрузчика для namespace Plugins\NgHelpers\
$ngHelpersFile = root . 'engine/plugins/ng-helpers/ng-helpers.php';
if (file_exists($ngHelpersFile) && !defined('NG_HELPERS_VERSION')) {
    require_once $ngHelpersFile;
    $GLOBALS['PLUGINS']['loaded']['ng-helpers'] = 1;
    // DEBUG: раскомментируйте для диагностики
    // error_log('[ng-advanced-captcha] ng-helpers.php загружен: ' . (defined('NG_HELPERS_VERSION') ? 'OK' : 'FAIL'));
}

// Явно проверяем доступность трейта Renderable и загружаем его если нужно
if (!trait_exists('Plugins\\NgHelpers\\Traits\\Renderable', false)) {
    $traitFile = root . 'engine/plugins/ng-helpers/src/Traits/Renderable.php';
    if (file_exists($traitFile)) {
        require_once $traitFile;
        // DEBUG: раскомментируйте для диагностики
        // error_log('[ng-advanced-captcha] Renderable.php загружен вручную');
    } else {
        // КРИТИЧЕСКАЯ ОШИБКА: трейт не найден
        error_log('[ng-advanced-captcha] ОШИБКА: Trait Renderable не найден! Путь: ' . $traitFile);
        return false;
    }
}

// Загружаем классы плагина (они используют Renderable трейт)
loadPluginLibrary('ng-advanced-captcha', 'autoload');

// Подгрузка языкового файла плагина.
LoadPluginLang('ng-advanced-captcha', 'main', '', '', ':');

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
