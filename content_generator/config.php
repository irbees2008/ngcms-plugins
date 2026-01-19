<?php
// Защита от прямого доступа
if (!defined('NGCMS')) {
    exit('HAL');
}
// Подключаем конфигурацию плагина
pluginsLoadConfig();
// Основная функция для отображения интерфейса автоматизации
function automation()
{
    global $twig, $PHP_SELF;
    // Обработка сохранения настроек
    if (isset($_POST['save']) && $_POST['save'] == '1') {
        // Значения из формы
        $newsCount   = max(1, intval($_POST['news_count'] ?? 50));
        $staticCount = max(1, intval($_POST['static_count'] ?? 20));
        $maxAllowed  = max($newsCount, $staticCount, intval($_POST['max_allowed'] ?? 1000));
        pluginSetVariable('content_generator', 'news_count', $newsCount);
        pluginSetVariable('content_generator', 'static_count', $staticCount);
        pluginSetVariable('content_generator', 'max_allowed', $maxAllowed);
    }
    // Чтение сохранённых значений / дефолты
    $newsCount   = intval(pluginGetVariable('content_generator', 'news_count')) ?: 50;
    $staticCount = intval(pluginGetVariable('content_generator', 'static_count')) ?: 20;
    $maxAllowed  = intval(pluginGetVariable('content_generator', 'max_allowed')) ?: 1000;
    // Определяем пути к шаблонам
    $tpath = locatePluginTemplates(
        ['config/main', 'config/automation'],
        'content_generator',
        1
    );
    // Проверяем существование шаблонов
    if (empty($tpath['config/main']) || empty($tpath['config/automation'])) {
        die('Ошибка: Не найдены необходимые шаблоны.');
    }
    try {
        // Загружаем основной шаблон
        $mainTemplate = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
        // Загружаем шаблон автоматизации
        $automationTemplate = $twig->loadTemplate($tpath['config/automation'] . 'config/automation.tpl');
        // Переменные для шаблона автоматизации
        $tVarsAutomation = [
            'news_count'   => $newsCount,
            'static_count' => $staticCount,
            'max_allowed'  => $maxAllowed,
            'plugin_url'   => admin_url . '/admin.php?mod=extra-config&plugin=content_generator',
        ];
        // Рендерим шаблон автоматизации
        $renderedAutomation = $automationTemplate->render($tVarsAutomation);
        // Переменные для основного шаблона
        $tVarsMain = [
            'entries'       => $renderedAutomation,
            'php_self'      => $PHP_SELF,
            'plugin_url'    => admin_url . '/admin.php?mod=extra-config&plugin=content_generator',
            'skins_url'     => skins_url,
            'admin_url'     => admin_url,
            'home'          => home,
            'current_title' => 'Automation',
        ];
        // Выводим основной шаблон
        echo $mainTemplate->render($tVarsMain);
    } catch (Exception $e) {
        // Обработка ошибок Twig
        die('Ошибка шаблонизатора: ' . $e->getMessage());
    }
}
// Основной обработчик запросов
switch ($_REQUEST['action'] ?? '') {
    default:
        automation();
        break;
}
