<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
LoadPluginLang('xmenu', 'config', '', 'xmenu', ':');
function showXMenuConfig()
{
    global $mysql, $tpl, $lang;
    $tpath = locatePluginTemplates(['mhead', 'ehead', 'efoot'], 'xmenu', 1);
    if (!$tpath) {
        return $lang['xmenu:err.no_templates'];
    }
    // Получаем количество меню из конфига
    $menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
    // Получаем категории
    $catz = $mysql->select("SELECT id, name, poslevel, xmenu FROM " . prefix . "_category ORDER BY posorder");
    if ($mysql->error) {
        return $lang['xmenu:err.categories'] . ' ' . $mysql->error;
    }
    // Получаем статические страницы
    $static_pages = $mysql->select("SELECT id, title, alt_name, xmenu FROM " . prefix . "_static ORDER BY id");
    if ($mysql->error) {
        return $lang['xmenu:err.static_pages'] . ' ' . $mysql->error;
    }
    // Формируем таблицу категорий
    $categories_html = '<div class="xmenu-section"><h3>' . $lang['xmenu:header.categories'] . '</h3>';
    $categories_html .= '<table class="table table-striped"><thead><tr><th>' . $lang['xmenu:th.category'] . '</th>';
    for ($i = 1; $i <= $menu_count; $i++) {
        $categories_html .= '<th>' . $lang['xmenu:th.menu'] . ' ' . $i . '</th>';
    }
    $categories_html .= '</tr></thead><tbody>';
    foreach ($catz as $cat) {
        $xmenu = isset($cat['xmenu']) ? $cat['xmenu'] : str_repeat('_', $menu_count);
        $xmenu = str_pad(substr($xmenu, 0, $menu_count), $menu_count, '_');
        $categories_html .= '<tr><td>' . str_repeat('&nbsp;&nbsp;', $cat['poslevel'] ?? 0) . htmlspecialchars($cat['name']) . '</td>';
        for ($i = 1; $i <= $menu_count; $i++) {
            $checked = (isset($xmenu[$i - 1]) && $xmenu[$i - 1] === '#') ? ' checked' : '';
            $categories_html .= '<td><input type="checkbox" name="cmenu[' . $cat['id'] . '][' . $i . ']" value="1"' . $checked . '></td>';
        }
        $categories_html .= '</tr>';
    }
    $categories_html .= '</tbody></table></div>';
    // Формируем таблицу статических страниц
    $static_html = '<div class="xmenu-section"><h3>' . $lang['xmenu:header.static'] . '</h3>';
    $static_html .= '<table class="table table-striped"><thead><tr><th>' . $lang['xmenu:th.page'] . '</th>';
    for ($i = 1; $i <= $menu_count; $i++) {
        $static_html .= '<th>' . $lang['xmenu:th.menu'] . ' ' . $i . '</th>';
    }
    $static_html .= '</tr></thead><tbody>';
    foreach ($static_pages as $page) {
        $xmenu = isset($page['xmenu']) ? $page['xmenu'] : str_repeat('_', $menu_count);
        $xmenu = str_pad(substr($xmenu, 0, $menu_count), $menu_count, '_');
        $page_title = htmlspecialchars($page['title'] ?? $lang['xmenu:no_title']);
        $page_altname = isset($page['alt_name']) ? ' (' . htmlspecialchars($page['alt_name']) . ')' : '';
        $static_html .= '<tr><td>' . $page_title . $page_altname . '</td>';
        for ($i = 1; $i <= $menu_count; $i++) {
            $checked = (isset($xmenu[$i - 1]) && $xmenu[$i - 1] === '#') ? ' checked' : '';
            $static_html .= '<td><input type="checkbox" name="smenu[' . $page['id'] . '][' . $i . ']" value="1"' . $checked . '></td>';
        }
        $static_html .= '</tr>';
    }
    $static_html .= '</tbody></table></div>';
    // Вывод интерфейса
    $output = '';
    if (isset($tpath['mhead'])) {
        $tpl->template('mhead', $tpath['mhead']);
        $tpl->vars('mhead', []);
        $output .= $tpl->show('mhead');
    }
    if (isset($tpath['ehead'])) {
        $tpl->template('ehead', $tpath['ehead']);
        $tpl->vars('ehead', ['id' => 0, 'display' => 'block']);
        $output .= $tpl->show('ehead');
    }
    $output .= $categories_html . $static_html;
    if (isset($tpath['efoot'])) {
        $tpl->template('efoot', $tpath['efoot']);
        $tpl->vars('efoot', []);
        $output .= $tpl->show('efoot');
    }
    return $output;
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
$cfg = [
    [
        'type' => 'hidden',
        'name' => 'action',
        'value' => 'commit'
    ],
    [
        'mode' => 'group',
        'title' => $lang['xmenu:group.main'],
        'entries' => [
            [
                'name' => 'menu_count',
                'title' => $lang['xmenu:menu_count'],
                'type' => 'input',
                'value' => intval(extra_get_param('xmenu', 'menu_count')) ?: 9,
                'help' => $lang['xmenu:menu_count#help']
            ],
            [
                'name' => 'localsource',
                'title' => $lang['xmenu:localsource'],
                'type' => 'select',
                'values' => ['0' => $lang['xmenu:lsrc_site'], '1' => $lang['xmenu:lsrc_plugin']],
                'value' => intval(extra_get_param('xmenu', 'localsource'))
            ]
        ]
    ],
    [
        'mode' => 'group',
        'title' => $lang['xmenu:group.cache'],
        'entries' => [
            [
                'name' => 'cache',
                'title' => $lang['xmenu:cache'],
                'type' => 'select',
                'values' => ['1' => $lang['yesa'], '0' => $lang['noa']],
                'value' => intval(extra_get_param('xmenu', 'cache'))
            ],
            [
                'name' => 'cacheExpire',
                'title' => $lang['xmenu:cache_expire'],
                'type' => 'input',
                'value' => extra_get_param('xmenu', 'cacheExpire') ?: '3600'
            ]
        ]
    ],
    [
        'type' => 'flat',
        'input' => showXMenuConfig()
    ]
];
generate_config_page($plugin, $cfg);
