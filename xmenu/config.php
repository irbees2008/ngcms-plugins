<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
LoadPluginLang('xmenu', 'config');
function showXMenuConfig()
{
    global $mysql, $tpl;
    $tpath = locatePluginTemplates(['mhead', 'ehead', 'efoot'], 'xmenu', 1);
    if (!$tpath) {
        return 'Ошибка: не найдены шаблоны плагина';
    }
    // Получаем количество меню из конфига
    $menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
    // Получаем категории
    $catz = $mysql->select("SELECT id, name, poslevel, xmenu FROM " . prefix . "_category ORDER BY posorder");
    if ($mysql->error) {
        return 'Ошибка при получении категорий: ' . $mysql->error;
    }
    // Получаем статические страницы
    $static_pages = $mysql->select("SELECT id, title, alt_name, xmenu FROM " . prefix . "_static ORDER BY id");
    if ($mysql->error) {
        return 'Ошибка при получении статических страниц: ' . $mysql->error;
    }
    // Формируем таблицу категорий
    $categories_html = '<div class="xmenu-section"><h3>Категории</h3>';
    $categories_html .= '<table class="table table-striped"><thead><tr><th>Категория</th>';
    for ($i = 1; $i <= $menu_count; $i++) {
        $categories_html .= '<th>Меню ' . $i . '</th>';
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
    $static_html = '<div class="xmenu-section"><h3>Статические страницы</h3>';
    $static_html .= '<table class="table table-striped"><thead><tr><th>Страница</th>';
    for ($i = 1; $i <= $menu_count; $i++) {
        $static_html .= '<th>Меню ' . $i . '</th>';
    }
    $static_html .= '</tr></thead><tbody>';
    foreach ($static_pages as $page) {
        $xmenu = isset($page['xmenu']) ? $page['xmenu'] : str_repeat('_', $menu_count);
        $xmenu = str_pad(substr($xmenu, 0, $menu_count), $menu_count, '_');
        $page_title = htmlspecialchars($page['title'] ?? 'Без названия');
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
    // Сохраняем привязку категорий
    if (isset($_REQUEST['cmenu']) && is_array($_REQUEST['cmenu'])) {
        foreach ($_REQUEST['cmenu'] as $cat_id => $menus) {
            if (!is_array($menus)) continue;
            $xline = str_repeat('_', $params['menu_count']);
            foreach ($menus as $menu_id => $val) {
                if ($menu_id >= 1 && $menu_id <= $params['menu_count']) {
                    $xline[$menu_id - 1] = ($val == '1') ? '#' : '_';
                }
            }
            $mysql->query("UPDATE " . prefix . "_category SET xmenu = " . db_squote($xline) . " WHERE id = " . db_squote($cat_id));
        }
    }
    // Сохраняем привязку статических страниц
    if (isset($_REQUEST['smenu']) && is_array($_REQUEST['smenu'])) {
        foreach ($_REQUEST['smenu'] as $page_id => $menus) {
            if (!is_array($menus)) continue;
            $xline = str_repeat('_', $params['menu_count']);
            foreach ($menus as $menu_id => $val) {
                if ($menu_id >= 1 && $menu_id <= $params['menu_count']) {
                    $xline[$menu_id - 1] = ($val == '1') ? '#' : '_';
                }
            }
            $mysql->query("UPDATE " . prefix . "_static SET xmenu = " . db_squote($xline) . " WHERE id = " . db_squote($page_id));
        }
    }
    pluginsSaveConfig();
    msg(['type' => 'info', 'text' => 'Настройки успешно сохранены']);
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
        'title' => 'Основные настройки',
        'entries' => [
            [
                'name' => 'menu_count',
                'title' => "Количество меню",
                'type' => 'input',
                'value' => intval(extra_get_param('xmenu', 'menu_count')) ?: 9,
                'help' => 'Укажите количество различных меню, которые будут использоваться в плагине'
            ],
            [
                'name' => 'localsource',
                'title' => "Источник шаблонов",
                'type' => 'select',
                'values' => ['0' => 'Шаблон сайта', '1' => 'Плагин'],
                'value' => intval(extra_get_param('xmenu', 'localsource'))
            ]
        ]
    ],
    [
        'mode' => 'group',
        'title' => 'Настройки кеширования',
        'entries' => [
            [
                'name' => 'cache',
                'title' => "Кеширование",
                'type' => 'select',
                'values' => ['1' => 'Да', '0' => 'Нет'],
                'value' => intval(extra_get_param('xmenu', 'cache'))
            ],
            [
                'name' => 'cacheExpire',
                'title' => "Время жизни кеша (сек)",
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
