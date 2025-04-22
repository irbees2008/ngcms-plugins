<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('xmenu', 'config');

function showXMenuConfig() {
    global $mysql, $tpl;
    
    $tpath = locatePluginTemplates(['mhead', 'ehead', 'efoot'], 'xmenu', 1);
    $catz = $mysql->select("SELECT * FROM " . prefix . "_category ORDER BY posorder");
    
    // Формируем таблицу категорий для TWIG
    $categories_html = '<table class="table table-striped "><tr><th>Категория</th>';
    for ($i = 1; $i <= 9; $i++) {
        $categories_html .= '<th>Меню ' . $i . '</th>';
    }
    $categories_html .= '</tr>';
    
    foreach ($catz as $cat) {
        if (!isset($cat['id']) || !isset($cat['name'])) continue;
        
        $xmenu = isset($cat['xmenu']) ? $cat['xmenu'] : '_________';
        $xmenu = str_pad(substr($xmenu, 0, 9), 9, '_');
        
        $categories_html .= '<tr><td>' . str_repeat('&nbsp;&nbsp;', $cat['poslevel'] ?? 0) . htmlspecialchars($cat['name']) . '</td>';
        
        for ($i = 1; $i <= 9; $i++) {
            $checked = (isset($xmenu[$i - 1]) && $xmenu[$i - 1] === '#') ? ' checked' : '';
            $categories_html .= '<td><input type="checkbox" name="cmenu[' . $cat['id'] . '][' . $i . ']" value="1"' . $checked . '></td>';
        }
        
        $categories_html .= '</tr>';
    }
    $categories_html .= '</table>';
    
    // Вывод интерфейса
    $tpl->template('mhead', $tpath['mhead']);
    $tpl->vars('mhead', []);
    $output = $tpl->show('mhead');
    
    $tpl->template('ehead', $tpath['ehead']);
    $tpl->vars('ehead', ['id' => 0, 'display' => 'block']);
    $output .= $tpl->show('ehead');
    
    $output .= $categories_html;
    
    $tpl->template('efoot', $tpath['efoot']);
    $tpl->vars('efoot', []);
    $output .= $tpl->show('efoot');
    
    return $output;
}

// Обработка сохранения
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    // Сохраняем только привязку категорий
    if (isset($_REQUEST['cmenu']) && is_array($_REQUEST['cmenu'])) {
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
    
    pluginsSaveConfig();
    header("Location: " . admin_url . "/admin.php?mod=extra-config&plugin=xmenu");
    exit;
}

// Формируем только необходимые настройки для TWIG
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