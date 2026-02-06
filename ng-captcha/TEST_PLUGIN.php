<?php

/**
 * Тестовый скрипт для проверки ng-captcha плагина
 * Запустить: php TEST_PLUGIN.php
 */

// Эмуляция NGCMS окружения
define('NGCMS', true);

echo "=== Тест плагина ng-captcha ===\n\n";

// Тест 1: Autoload
echo "1. Проверка autoload...\n";
require_once __DIR__ . '/autoload.php';

try {
    $captchaClass = class_exists('Plugins\Captcha\Captcha');
    $googleClass = class_exists('Plugins\Captcha\Providers\GoogleRecaptchaProvider');
    $turnstileClass = class_exists('Plugins\Captcha\Providers\TurnstileProvider');
    $yandexClass = class_exists('Plugins\Captcha\Providers\YandexCaptchaProvider');

    if ($captchaClass && $googleClass && $turnstileClass && $yandexClass) {
        echo "   ✓ Все классы загружены\n";
    } else {
        echo "   ✗ Ошибка загрузки классов:\n";
        echo "     - Captcha: " . ($captchaClass ? 'OK' : 'FAIL') . "\n";
        echo "     - Google: " . ($googleClass ? 'OK' : 'FAIL') . "\n";
        echo "     - Turnstile: " . ($turnstileClass ? 'OK' : 'FAIL') . "\n";
        echo "     - Yandex: " . ($yandexClass ? 'OK' : 'FAIL') . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Ошибка: " . $e->getMessage() . "\n";
}

// Тест 2: Структура файлов
echo "\n2. Проверка структуры файлов...\n";
$requiredFiles = [
    'ng-captcha.php',
    'config.php',
    'version',
    'autoload.php',
    'README.md',
    'LICENSE',
    'src/Captcha.php',
    'src/Contracts/CaptchaProviderInterface.php',
    'src/Providers/AbstractProvider.php',
    'src/Providers/GoogleRecaptchaProvider.php',
    'src/Providers/TurnstileProvider.php',
    'src/Providers/YandexCaptchaProvider.php',
    'src/Filters/CaptchaCoreFilter.php',
    'src/Filters/CaptchaCommentsFilter.php',
    'src/Filters/CaptchaFeedbackFilter.php',
    'tpl/google-widget.tpl',
    'tpl/google-script.tpl',
    'tpl/turnstile-widget.tpl',
    'tpl/yandex-widget.tpl',
    'tpl/yandex-script.tpl',
    'lang/russian/config.ini',
    'lang/russian/main.ini',
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    echo "   ✓ Все файлы на месте (" . count($requiredFiles) . " файлов)\n";
} else {
    echo "   ✗ Отсутствуют файлы:\n";
    foreach ($missingFiles as $file) {
        echo "     - $file\n";
    }
}

// Тест 3: Проверка интерфейса
echo "\n3. Проверка интерфейса CaptchaProviderInterface...\n";
try {
    $interface = new ReflectionClass('Plugins\Captcha\Contracts\CaptchaProviderInterface');
    $methods = $interface->getMethods();
    $requiredMethods = [
        'getName',
        'getSiteKey',
        'getSecretKey',
        'getApiScript',
        'getApiVerify',
        'getTokenFieldName',
        'registerJavaScript',
        'renderWidget',
        'verify',
        'validate'
    ];

    $foundMethods = array_map(function ($m) {
        return $m->getName();
    }, $methods);
    $missing = array_diff($requiredMethods, $foundMethods);

    if (empty($missing)) {
        echo "   ✓ Все методы интерфейса определены (" . count($foundMethods) . " методов)\n";
    } else {
        echo "   ✗ Отсутствуют методы:\n";
        foreach ($missing as $method) {
            echo "     - $method\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Ошибка: " . $e->getMessage() . "\n";
}

// Тест 4: Проверка провайдеров
echo "\n4. Проверка провайдеров...\n";
$providers = [
    'Google reCAPTCHA v3' => 'Plugins\Captcha\Providers\GoogleRecaptchaProvider',
    'Cloudflare Turnstile' => 'Plugins\Captcha\Providers\TurnstileProvider',
    'Yandex SmartCaptcha' => 'Plugins\Captcha\Providers\YandexCaptchaProvider',
];

foreach ($providers as $name => $class) {
    try {
        $reflection = new ReflectionClass($class);
        $implements = $reflection->implementsInterface('Plugins\Captcha\Contracts\CaptchaProviderInterface');

        if ($implements) {
            echo "   ✓ $name - реализует интерфейс\n";
        } else {
            echo "   ✗ $name - НЕ реализует интерфейс\n";
        }
    } catch (Exception $e) {
        echo "   ✗ $name - ошибка: " . $e->getMessage() . "\n";
    }
}

// Тест 5: Проверка фильтров (требуют NGCMS окружение)
echo "\n5. Проверка фильтров...\n";
$filters = [
    'CaptchaCoreFilter' => 'src/Filters/CaptchaCoreFilter.php',
    'CaptchaCommentsFilter' => 'src/Filters/CaptchaCommentsFilter.php',
    'CaptchaFeedbackFilter' => 'src/Filters/CaptchaFeedbackFilter.php',
];

foreach ($filters as $name => $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path) && filesize($path) > 0) {
        echo "   ✓ $name (" . filesize($path) . " байт)\n";
    } else {
        echo "   ✗ $name - не найден или пустой\n";
    }
}

// Тест 6: Проверка шаблонов
echo "\n6. Проверка шаблонов...\n";
$templates = [
    'google-widget.tpl',
    'google-script.tpl',
    'turnstile-widget.tpl',
    'yandex-widget.tpl',
    'yandex-script.tpl',
];

foreach ($templates as $tpl) {
    $path = __DIR__ . '/tpl/' . $tpl;
    if (file_exists($path) && filesize($path) > 0) {
        echo "   ✓ $tpl (" . filesize($path) . " байт)\n";
    } else {
        echo "   ✗ $tpl - не найден или пустой\n";
    }
}

// Тест 7: Проверка языковых файлов
echo "\n7. Проверка языковых файлов...\n";
$langFiles = [
    'lang/russian/config.ini',
    'lang/russian/main.ini',
];

foreach ($langFiles as $lang) {
    $path = __DIR__ . '/' . $lang;
    if (file_exists($path) && filesize($path) > 0) {
        echo "   ✓ $lang (" . filesize($path) . " байт)\n";
    } else {
        echo "   ✗ $lang - не найден или пустой\n";
    }
}

// Тест 8: Проверка синтаксиса основных файлов
echo "\n8. Проверка синтаксиса PHP файлов...\n";
$phpFiles = [
    'ng-captcha.php',
    'config.php',
    'autoload.php',
    'src/Captcha.php',
];

$syntaxErrors = 0;
foreach ($phpFiles as $file) {
    $path = __DIR__ . '/' . $file;
    $output = [];
    $return = 0;
    exec("php -l \"$path\" 2>&1", $output, $return);

    if ($return === 0) {
        echo "   ✓ $file\n";
    } else {
        echo "   ✗ $file - " . implode("\n     ", $output) . "\n";
        $syntaxErrors++;
    }
}

// Итоги
echo "\n" . str_repeat("=", 50) . "\n";
echo "ИТОГИ ТЕСТИРОВАНИЯ:\n";
echo str_repeat("=", 50) . "\n";

if ($syntaxErrors === 0 && empty($missingFiles)) {
    echo "✓ Плагин готов к использованию!\n";
    echo "\nСледующие шаги:\n";
    echo "1. Активируйте плагин в админ-панели\n";
    echo "2. Выберите провайдера в настройках\n";
    echo "3. Укажите ключи Site Key и Secret Key\n";
    echo "4. Сохраните настройки\n";
} else {
    echo "✗ Обнаружены проблемы:\n";
    if ($syntaxErrors > 0) {
        echo "  - Синтаксические ошибки: $syntaxErrors файл(ов)\n";
    }
    if (!empty($missingFiles)) {
        echo "  - Отсутствующие файлы: " . count($missingFiles) . "\n";
    }
    echo "\nИсправьте ошибки перед использованием плагина.\n";
}

echo "\n";
