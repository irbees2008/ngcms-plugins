<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
// uninstall.php исполняется напрямую через extra-config.php (без вызова функции)
// Здесь выполняем подтверждение/удаление и чистим привязки.
pluginsLoadConfig();
// Показываем страницу подтверждения удаления
if (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'commit') {
    generate_install_page('ai_rewriter', 'Плагин AI Rewriter будет удалён. Таблицы БД не создавались.', 'deinstall');
    return;
}
// Чистим привязки в engine/conf/plugins.php
$confFile = __DIR__ . '/../../conf/plugins.php';
if (is_file($confFile) && is_writable($confFile)) {
    include $confFile; // создаёт $array
    if (isset($array) && is_array($array)) {
        $changed = false;
        if (isset($array['actions']['rpc']['ai_rewriter'])) { unset($array['actions']['rpc']['ai_rewriter']); $changed = true; }
        if (isset($array['actions']['core']['ai_rewriter'])) { unset($array['actions']['core']['ai_rewriter']); $changed = true; }
        if (isset($array['actions']['news']['ai_rewriter'])) { unset($array['actions']['news']['ai_rewriter']); $changed = true; }
        if ($changed) {
            $export = var_export($array, true);
            file_put_contents($confFile, "<?php \$array = $export; ?>");
        }
    }
}
// Помечаем плагин удалённым
plugin_mark_deinstalled('ai_rewriter');
// Возвращаемся к списку плагинов
global $PHP_SELF;
header('Location: ' . $PHP_SELF . '?mod=extras');
exit;
