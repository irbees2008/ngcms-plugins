<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
// Load lang file
LoadPluginLang('robots_editor', 'config', '', '', ':');
// Получаем корневую директорию сайта (на уровень выше engine)
function get_site_root()
{
    return dirname(root) . '/';
}
// Define system directories and patterns
function robots_editor_get_system_items()
{
    return array(
        'system' => array(
            'title' => 'Системные папки',
            'items' => array(
                '/engine/' => 'Движок сайта',
                '/templates/' => 'Шаблоны',
                '/uploads/avatars/' => 'Аватары пользователей',
                '/uploads/files/' => 'Загруженные файлы',
                '/uploads/images/thumb/' => 'Миниатюры изображений',
                '/uploads/photos/' => 'Фотографии пользователей',
                '/plugin/' => 'Плагины (общее)',
                '/lib/' => 'Библиотеки',
                '/webstat/' => 'Веб-статистика',
                '/cache/' => 'Кеш',
                '/tmp/' => 'Временные файлы',
                '/admin/' => 'Админ-панель'
            )
        ),
        'content' => array(
            'title' => 'Контентные разделы',
            'items' => array(
                '/uploads/dsn/' => 'DSN файлы',
                '/uploads/images/$' => 'Основные изображения',
                '/plugin/gsmg/' => 'Карта сайта GSMG',
                '/plugin/sitemap/' => 'Sitemap',
                '/search/' => 'Поиск',
                '/rss.xml' => 'RSS лента',
                '/login/' => 'Вход',
                '/logout/' => 'Выход',
                '/register/' => 'Регистрация',
                '/activate/' => 'Активация',
                '/lostpassword/' => 'Восстановление пароля',
                '/profile.html' => 'Профиль',
                '/users/' => 'Пользователи',
                '/page/' => 'Страницы',
                '/*print' => 'Версия для печати',
                '/*xml' => 'XML файлы',
                '/*201*' => 'Архивные материалы',
                '*/page/1$' => 'Первые страницы пагинации'
            )
        )
    );
}
// Function to generate robots.txt content
function robots_editor_generate_content()
{
    $rules = pluginGetVariable('robots_editor', 'rules');
    $custom_rules = pluginGetVariable('robots_editor', 'custom_rules');
    $auto_sitemap = pluginGetVariable('robots_editor', 'auto_sitemap');
    $content = "";
    // Generate rules for each user-agent
    $user_agents = array(
        'Yandex' => 'Yandex',
        'Googlebot' => 'Googlebot',
        '*' => 'Все остальные'
    );
    foreach ($user_agents as $ua => $title) {
        $content .= "User-agent: $ua\n";
        if (is_array($rules) && isset($rules[$ua])) {
            foreach ($rules[$ua] as $path => $allowed) {
                if ($allowed) {
                    $content .= "Allow: $path\n";
                } else {
                    $content .= "Disallow: $path\n";
                }
            }
        }
        $content .= "\n";
    }
    // Add custom rules - ДОБАВЛЕНО правильное отображение
    if (!empty($custom_rules)) {
        $content .= trim($custom_rules) . "\n\n";
    }
    // Add sitemap if enabled
    if ($auto_sitemap) {
        $sitemap_url = home . '/gsmg.xml';
        $content .= "Sitemap: " . $sitemap_url . "\n";
    }
    // Add host
    $content .= "Host: " . home . "\n";
    return $content;
}
// Function to save robots.txt
function robots_editor_save_file()
{
    $content = robots_editor_generate_content();
    $site_root = get_site_root();
    $path = $site_root . 'robots.txt';
    // Детальная отладка
    $debug_info = array();
    $debug_info[] = "Корень сайта: " . $site_root;
    $debug_info[] = "Полный путь: " . $path;
    $debug_info[] = "Файл существует: " . (file_exists($path) ? "Да" : "Нет");
    if (file_exists($path)) {
        $debug_info[] = "Размер: " . filesize($path) . " байт";
        $debug_info[] = "Доступен для чтения: " . (is_readable($path) ? "Да" : "Нет");
        $debug_info[] = "Доступен для записи: " . (is_writable($path) ? "Да" : "Нет");
        // Пытаемся изменить права если нельзя записать
        if (!is_writable($path)) {
            $old_perms = fileperms($path);
            $debug_info[] = "Текущие права: " . decoct($old_perms & 0777);
            if (@chmod($path, 0666)) {
                $debug_info[] = "Права изменены на: 0666";
            } else {
                $debug_info[] = "Не удалось изменить права!";
            }
        }
    }
    // Проверяем директорию
    $dir = dirname($path);
    $debug_info[] = "Директория доступна для записи: " . (is_writable($dir) ? "Да" : "Нет");
    // Пытаемся сохранить файл
    $result = @file_put_contents($path, $content);
    $debug_info[] = "Результат записи: " . ($result !== false ? "Успешно (" . $result . " байт)" : "Ошибка");
    // Если не удалось, пробуем альтернативные методы
    if ($result === false) {
        // Метод 1: через fopen
        if ($handle = @fopen($path, 'w')) {
            $result = fwrite($handle, $content);
            fclose($handle);
            $debug_info[] = "Метод fopen: " . ($result !== false ? "Успешно" : "Ошибка");
        } else {
            $debug_info[] = "Метод fopen: Не удалось открыть файл";
        }
    }
    // Логируем отладочную информацию
     $debug_log = implode("\n", $debug_info);
     @file_put_contents($site_root . 'robots_debug.log', $debug_log . "\n\n", FILE_APPEND);
     return $result !== false;
}
// Get current settings or set defaults
$current_rules = pluginGetVariable('robots_editor', 'rules');
$custom_rules = pluginGetVariable('robots_editor', 'custom_rules');
$auto_sitemap = pluginGetVariable('robots_editor', 'auto_sitemap');
// Set defaults if not set
if ($auto_sitemap === null) {
    pluginSetVariable('robots_editor', 'auto_sitemap', 1);
    $auto_sitemap = 1;
}
// Set default rules if not set
if (!is_array($current_rules)) {
    $system_items = robots_editor_get_system_items();
    $default_rules = array();
    $user_agents = array('Yandex', 'Googlebot', '*');
    foreach ($user_agents as $ua) {
        foreach ($system_items as $category) {
            foreach ($category['items'] as $path => $title) {
                // Set default permissions based on your example
                $default_rules[$ua][$path] = in_array($path, array(
                    '/uploads/dsn/',
                    '/uploads/images/$',
                    '/plugin/gsmg/',
                    '/plugin/sitemap/'
                )) ? 1 : 0;
            }
        }
    }
    pluginSetVariable('robots_editor', 'rules', $default_rules);
    $current_rules = $default_rules;
}
// Process form submission
// Process form submission
if ($_REQUEST['action'] == 'commit') {
    // Save rules
    $user_agents = array('Yandex', 'Googlebot', '*');
    $system_items = robots_editor_get_system_items();
    $new_rules = array();
    foreach ($user_agents as $ua) {
        $new_rules[$ua] = array();
        foreach ($system_items as $category) {
            foreach ($category['items'] as $path => $title) {
                $new_rules[$ua][$path] = isset($_POST['rule_' . $ua . '_' . md5($path)]) ? 1 : 0;
            }
        }
    }
    // Save settings - ФИКС: получаем кастомные правила из POST
    $cfg = array(
        'rules' => $new_rules,
        'custom_rules' => $_POST['custom_rules'], // правильно получаем из POST
        'auto_sitemap' => isset($_POST['auto_sitemap']) ? 1 : 0
    );
    // Update plugin variables
    foreach ($cfg as $key => $value) {
        pluginSetVariable('robots_editor', $key, $value);
    }
    // Save robots.txt
    if (robots_editor_save_file()) {
        $saved_content = robots_editor_generate_content();
        $site_root = get_site_root();
        $file_path = $site_root . 'robots.txt';
        msg(array("type" => "info", "text" => $lang['robots_editor:saved']));
        msg(array("type" => "info", "text" => "Файл сохранен: " . $file_path));
    } else {
        $error = "Ошибка при сохранении robots.txt! ";
        $site_root = get_site_root();
        $path = $site_root . 'robots.txt';
        $error .= "<br>Подробности в файле: " . $site_root . 'robots_debug.log';
        $error .= "<br>Попробуйте выполнить вручную:";
        $error .= "<br><code>chmod 666 " . $path . "</code>";
        msg(array("type" => "error", "text" => $error));
    }
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, array());
    print_commit_complete($plugin);
} else {
    $system_items = robots_editor_get_system_items();
    $user_agents = array(
        'Yandex' => 'Yandex',
        'Googlebot' => 'Googlebot',
        '*' => 'Все поисковые системы'
    );
    // Проверяем текущий robots.txt
    $site_root = get_site_root();
    $current_robots_path = $site_root . 'robots.txt';
    $current_robots_content = '';
    $file_exists = file_exists($current_robots_path);
    if ($file_exists) {
        $current_robots_content = file_get_contents($current_robots_path);
        $file_size = filesize($current_robots_path);
        $file_writable = is_writable($current_robots_path);
    }
    // Generate configuration page
    $cfgX = array();
    // File status info
    $status_html = '<div class="alert ' . ($file_exists ? ($file_writable ? 'alert-success' : 'alert-warning') : 'alert-info') . '">';
    $status_html .= '<strong>Статус файла robots.txt:</strong> ';
    if ($file_exists) {
        $status_html .= 'Файл существует (' . $file_size . ' байт)';
        if (!$file_writable) {
            $status_html .= '<br><span style="color: red;">Внимание: файл недоступен для записи!</span>';
        }
    } else {
        $status_html .= 'Файл не существует, будет создан автоматически';
    }
    $status_html .= '<br><strong>Путь:</strong> ' . $current_robots_path;
    $status_html .= '</div>';
    array_push($cfgX, array(
        'name' => 'file_status',
        'title' => 'Статус файла',
        'descr' => $status_html,
        'type' => 'plain'
    ));
    // Auto sitemap option
    array_push($cfgX, array(
        'name' => 'auto_sitemap',
        'title' => $lang['robots_editor:auto_sitemap'],
        'descr' => $lang['robots_editor:auto_sitemap#desc'],
        'type' => 'select',
        'values' => array('1' => 'Да', '0' => 'Нет'),
        'value' => $auto_sitemap
    ));
    // Rules table
    $rules_html = '<div class="robots-rules-table">';
    foreach ($system_items as $category) {
        $rules_html .= '<h4>' . $category['title'] . '</h4>';
        $rules_html .= '<table class="table table-bordered table-striped">';
        $rules_html .= '<thead><tr><th>Путь</th><th>Описание</th>';
        foreach ($user_agents as $ua => $title) {
            $rules_html .= '<th>' . $title . '</th>';
        }
        $rules_html .= '</tr></thead><tbody>';
        foreach ($category['items'] as $path => $title) {
            $rules_html .= '<tr>';
            $rules_html .= '<td><code>' . htmlspecialchars($path) . '</code></td>';
            $rules_html .= '<td>' . htmlspecialchars($title) . '</td>';
            foreach ($user_agents as $ua => $ua_title) {
                $checked = (isset($current_rules[$ua][$path]) && $current_rules[$ua][$path]) ? 'checked' : '';
                $rules_html .= '<td class="text-center">';
                $rules_html .= '<input type="checkbox" name="rule_' . $ua . '_' . md5($path) . '" ' . $checked . ' value="1">';
                $rules_html .= '</td>';
            }
            $rules_html .= '</tr>';
        }
        $rules_html .= '</tbody></table>';
    }
    $rules_html .= '</div>';
    array_push($cfgX, array(
        'name' => 'rules_table',
        'title' => 'Настройки доступа',
        'descr' => $rules_html,
        'type' => 'plain'
    ));
    // Custom rules - ФИКС: правильное текстовое поле
    $custom_rules_html = '<textarea name="custom_rules" rows="6" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Добавьте дополнительные правила robots.txt. Например:&#10;Clean-param: ref /some_dir/&#10;Crawl-delay: 2&#10;User-agent: SpecialBot&#10;Disallow: /private/">' . htmlspecialchars($custom_rules ? $custom_rules : '') . '</textarea>';
    $custom_rules_html .= '<p class="help-block">Можно добавить любые дополнительные директивы: Clean-param, Crawl-delay, User-agent и др.</p>';
    array_push($cfgX, array(
        'name' => 'custom_rules_info',
        'title' => $lang['robots_editor:custom_rules'],
        'descr' => $custom_rules_html,
        'type' => 'plain'
    ));
    // Preview
    $preview_content = robots_editor_generate_content();
    $preview_html = '<div class="alert alert-info">';
    $preview_html .= '<h4>Предпросмотр robots.txt:</h4>';
    $preview_html .= '<pre style="max-height: 300px; overflow: auto; background: #f8f9fa; padding: 10px; border: 1px solid #ddd;">';
    $preview_html .= htmlspecialchars($preview_content);
    $preview_html .= '</pre>';
    $preview_html .= '</div>';
    array_push($cfgX, array(
        'name' => 'preview',
        'title' => 'Предпросмотр',
        'descr' => $preview_html,
        'type' => 'plain'
    ));
    $cfg = array(array(
        'mode' => 'group',
        'title' => $lang['robots_editor:group_config'],
        'entries' => $cfgX
    ));
    // Add custom CSS
    echo '<style>
    .robots-rules-table {
        margin-bottom: 20px;
    }
    .robots-rules-table h4 {
        margin-top: 20px;
        margin-bottom: 10px;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
    }
    .robots-rules-table table {
        margin-bottom: 20px;
        width: 100%;
    }
    .robots-rules-table th {
        background: #007bff;
        color: white;
        text-align: center;
        vertical-align: middle;
    }
    .robots-rules-table td {
        vertical-align: middle;
    }
    .robots-rules-table td:first-child {
        font-family: monospace;
        background: #f8f9fa;
    }
    .robots-rules-table .text-center {
        text-align: center;
    }
    .alert-success { background-color: #dff0d8; border-color: #d6e9c6; color: #3c763d; }
    .alert-warning { background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; }
    .alert-info { background-color: #d9edf7; border-color: #bce8f1; color: #31708f; }
    pre {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .help-block {
        color: #666;
        font-size: 12px;
        margin-top: 5px;
    }
    </style>';
    generate_config_page($plugin, $cfg);
}
