<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('xmenu', 'config');

function showXMenuConfig() {
    global $mysql, $tpl;
    
    $tpath = locatePluginTemplates(['mhead', 'ehead', 'efoot'], 'xmenu', 1);
    if (!$tpath) {
        return 'Ошибка: не найдены шаблоны плагина';
    }
    
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
    for ($i = 1; $i <= 9; $i++) {
        $categories_html .= '<th>Меню ' . $i . '</th>';
    }
    $categories_html .= '</tr></thead><tbody>';
    
    foreach ($catz as $cat) {
        $xmenu = isset($cat['xmenu']) ? $cat['xmenu'] : '_________';
        $xmenu = str_pad(substr($xmenu, 0, 9), 9, '_');
        
        $categories_html .= '<tr><td>' . str_repeat('&nbsp;&nbsp;', $cat['poslevel'] ?? 0) . htmlspecialchars($cat['name']) . '</td>';
        
        for ($i = 1; $i <= 9; $i++) {
            $checked = (isset($xmenu[$i - 1]) && $xmenu[$i - 1] === '#') ? ' checked' : '';
            $categories_html .= '<td><input type="checkbox" name="cmenu[' . $cat['id'] . '][' . $i . ']" value="1"' . $checked . '></td>';
        }
        
        $categories_html .= '</tr>';
    }
    $categories_html .= '</tbody></table></div>';
    
    // Формируем таблицу статических страниц
    $static_html = '<div class="xmenu-section"><h3>Статические страницы</h3>';
    $static_html .= '<table class="table table-striped"><thead><tr><th>Страница</th>';
    for ($i = 1; $i <= 9; $i++) {
        $static_html .= '<th>Меню ' . $i . '</th>';
    }
    $static_html .= '</tr></thead><tbody>';
    
    foreach ($static_pages as $page) {
        $xmenu = isset($page['xmenu']) ? $page['xmenu'] : '_________';
        $xmenu = str_pad(substr($xmenu, 0, 9), 9, '_');
        
        $page_title = htmlspecialchars($page['title'] ?? 'Без названия');
        $page_altname = isset($page['alt_name']) ? ' (' . htmlspecialchars($page['alt_name']) . ')' : '';
        
        $static_html .= '<tr><td>' . $page_title . $page_altname . '</td>';
        
        for ($i = 1; $i <= 9; $i++) {
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
    // Сохраняем привязку категорий
    if (isset($_REQUEST['cmenu'])) {
        foreach ($mysql->select("SELECT id FROM " . prefix . "_category") as $cat) {
            $xline = '_________';
            if (isset($_REQUEST['cmenu'][$cat['id']])) {
                $xline = '';
                for ($i = 1; $i <= 9; $i++) {
                    $xline .= (!empty($_REQUEST['cmenu'][$cat['id']][$i])) ? '#' : '_';
                }
            }
            $mysql->query("UPDATE " . prefix . "_category SET xmenu = " . db_squote($xline) . " WHERE id = " . db_squote($cat['id']));
        }
    }
    
    // Сохраняем привязку статических страниц
    if (isset($_REQUEST['smenu'])) {
        foreach ($mysql->select("SELECT id FROM " . prefix . "_static") as $page) {
            $xline = '_________';
            if (isset($_REQUEST['smenu'][$page['id']])) {
                $xline = '';
                for ($i = 1; $i <= 9; $i++) {
                    $xline .= (!empty($_REQUEST['smenu'][$page['id']][$i])) ? '#' : '_';
                }
            }
            $mysql->query("UPDATE " . prefix . "_static SET xmenu = " . db_squote($xline) . " WHERE id = " . db_squote($page['id']));
        }
    }
    
    pluginsSaveConfig();
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
        'type' => 'flat',
        'input' => showXMenuConfig()
    ],
    [
        'mode' => 'group',
        'title' => 'Дополнительные настройки',
        'entries' => [
            [
                'name' => 'localsource',
                'title' => "Источник шаблонов",
                'type' => 'select',
                'values' => ['0' => 'Шаблон сайта', '1' => 'Плагин'],
                'value' => intval(extra_get_param('xmenu', 'localsource'))
            ],
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
    ]
];

generate_config_page($plugin, $cfg);