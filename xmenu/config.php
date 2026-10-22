<?php
if (!defined('NGCMS')) die('HAL');
global $mysql, $lang;
pluginsLoadConfig();
LoadPluginLang('xmenu', 'config', '', '', ':');
function showXMenuConfig()
{
    global $mysql, $twig, $lang;

    $tpath = locatePluginTemplates(['config.main'], 'xmenu', 1);
    if (empty($tpath['config.main'])) {
        return $lang['xmenu:err.no_templates'];
    }

    $menuCount = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
    $categories = $mysql->select('SELECT id, name, poslevel, xmenu FROM ' . prefix . '_category ORDER BY posorder');
    if ($mysql->error) {
        return $lang['xmenu:err.categories'] . ' ' . $mysql->error;
    }
    $staticPages = $mysql->select('SELECT id, title, alt_name, xmenu FROM ' . prefix . '_static ORDER BY id');
    if ($mysql->error) {
        return $lang['xmenu:err.static_pages'] . ' ' . $mysql->error;
    }

    $menuHeaders = '';
    for ($menuId = 1; $menuId <= $menuCount; $menuId++) {
        $menuHeaders .= '<th scope="col">' . htmlspecialchars($lang['xmenu:th.menu'], ENT_QUOTES, 'UTF-8') . ' ' . $menuId . '</th>';
    }

    $categoryRows = '';
    foreach ($categories as $category) {
        $menuFlags = str_pad(substr($category['xmenu'] ?? '', 0, $menuCount), $menuCount, '_');
        $categoryRows .= '<tr><th scope="row">' . str_repeat('&nbsp;&nbsp;', (int)($category['poslevel'] ?? 0)) . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '</th>';
        for ($menuId = 1; $menuId <= $menuCount; $menuId++) {
            $checked = ($menuFlags[$menuId - 1] === '#') ? ' checked' : '';
            $categoryRows .= '<td class="text-center"><input class="form-check-input" type="checkbox" name="cmenu[' . (int)$category['id'] . '][' . $menuId . ']" value="1"' . $checked . '></td>';
        }
        $categoryRows .= '</tr>';
    }
    if ($categoryRows === '') {
        $categoryRows = '<tr><td colspan="' . ($menuCount + 1) . '" class="text-muted">' . htmlspecialchars($lang['xmenu:empty.categories'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }

    $staticRows = '';
    foreach ($staticPages as $page) {
        $menuFlags = str_pad(substr($page['xmenu'] ?? '', 0, $menuCount), $menuCount, '_');
        $pageTitle = htmlspecialchars($page['title'] ?? $lang['xmenu:no_title'], ENT_QUOTES, 'UTF-8');
        $pageAltName = isset($page['alt_name']) && $page['alt_name'] !== '' ? ' (' . htmlspecialchars($page['alt_name'], ENT_QUOTES, 'UTF-8') . ')' : '';
        $staticRows .= '<tr><th scope="row">' . $pageTitle . $pageAltName . '</th>';
        for ($menuId = 1; $menuId <= $menuCount; $menuId++) {
            $checked = ($menuFlags[$menuId - 1] === '#') ? ' checked' : '';
            $staticRows .= '<td class="text-center"><input class="form-check-input" type="checkbox" name="smenu[' . (int)$page['id'] . '][' . $menuId . ']" value="1"' . $checked . '></td>';
        }
        $staticRows .= '</tr>';
    }
    if ($staticRows === '') {
        $staticRows = '<tr><td colspan="' . ($menuCount + 1) . '" class="text-muted">' . htmlspecialchars($lang['xmenu:empty.static_pages'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }

    $localSource = intval(extra_get_param('xmenu', 'localsource'));
    $cacheEnabled = intval(extra_get_param('xmenu', 'cache'));
    $cacheExpire = extra_get_param('xmenu', 'cacheExpire') ?: 3600;
    $documentationButton = '';
    $documentationModal = '';
    if (function_exists('plugin_documentation_markup')) {
        list($documentationButton, $documentationModal) = plugin_documentation_markup('xmenu');
    }

    return $twig->render($tpath['config.main'] . 'config.main.tpl', [
        'token' => genUToken('admin.extra-config'),
        'lang' => $lang,
        'documentation_button' => $documentationButton,
        'documentation_modal' => $documentationModal,
        'menu_count' => intval(extra_get_param('xmenu', 'menu_count')) ?: 9,
        'cache_expire' => $cacheExpire,
        'menu_headers' => $menuHeaders,
        'category_rows' => $categoryRows,
        'static_rows' => $staticRows,
        'local_source' => $localSource,
        'cache_enabled' => $cacheEnabled,
    ]);
}
// Обработка сохранения
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    // Сохраняем основные настройки
    $params = [
        'localsource' => intval($_REQUEST['localsource'] ?? 0),
        'cache' => intval($_REQUEST['cache'] ?? 0),
        'cacheExpire' => intval($_REQUEST['cacheExpire'] ?? 3600),
        'menu_count' => intval($_REQUEST['menu_count'] ?? 9)
    ];
    // Сохраняем каждую настройку отдельно
    foreach ($params as $key => $value) {
        extra_set_param('xmenu', $key, $value);
    }
    $menu_count = $params['menu_count'];
    // Получаем текущие данные для сравнения
    $current_catz = $mysql->select("SELECT id, xmenu FROM " . prefix . "_category");
    $current_static = $mysql->select("SELECT id, xmenu FROM " . prefix . "_static");
    // ОБРАБОТКА КАТЕГОРИЙ
    foreach ($current_catz as $cat) {
        $new_xline = str_repeat('_', $menu_count);
        // Устанавливаем флаги из формы
        if (isset($_REQUEST['cmenu'][$cat['id']]) && is_array($_REQUEST['cmenu'][$cat['id']])) {
            foreach ($_REQUEST['cmenu'][$cat['id']] as $menu_id => $val) {
                if ($menu_id >= 1 && $menu_id <= $menu_count && $val == '1') {
                    $new_xline[$menu_id - 1] = '#';
                }
            }
        }
        // Обновляем только если изменилось
        $current_xmenu = isset($cat['xmenu']) ? str_pad(substr($cat['xmenu'], 0, $menu_count), $menu_count, '_') : str_repeat('_', $menu_count);
        if ($current_xmenu !== $new_xline) {
            $mysql->query("UPDATE " . prefix . "_category SET xmenu = " . db_squote($new_xline) . " WHERE id = " . db_squote($cat['id']));
        }
    }
    // ОБРАБОТКА СТАТИЧЕСКИХ СТРАНИЦ
    foreach ($current_static as $page) {
        $new_xline = str_repeat('_', $menu_count);
        // Устанавливаем флаги из формы
        if (isset($_REQUEST['smenu'][$page['id']]) && is_array($_REQUEST['smenu'][$page['id']])) {
            foreach ($_REQUEST['smenu'][$page['id']] as $menu_id => $val) {
                if ($menu_id >= 1 && $menu_id <= $menu_count && $val == '1') {
                    $new_xline[$menu_id - 1] = '#';
                }
            }
        }
        // Обновляем только если изменилось
        $current_xmenu = isset($page['xmenu']) ? str_pad(substr($page['xmenu'], 0, $menu_count), $menu_count, '_') : str_repeat('_', $menu_count);
        if ($current_xmenu !== $new_xline) {
            $mysql->query("UPDATE " . prefix . "_static SET xmenu = " . db_squote($new_xline) . " WHERE id = " . db_squote($page['id']));
        }
    }
    pluginsSaveConfig();
    msg(['type' => 'info', 'text' => $lang['xmenu:msg.saved']]);
    header("Location: " . admin_url . "/admin.php?mod=extra-config&plugin=xmenu");
    exit;
}
// Формируем конфигурационную страницу
print showXMenuConfig();
