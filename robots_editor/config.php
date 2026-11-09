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
// Define system directories and patterns
function robots_editor_get_system_items()
{
    global $multiDomainName;
    // Determine upload path based on multisite
    $uploadPath = '/uploads/';
    if (!empty($multiDomainName) && $multiDomainName !== 'main') {
        $uploadPath = '/uploads/multi/' . $multiDomainName . '/';
    }
    return array(
        'system' => array(
            'title' => 'Системные папки (блокировать)',
            'items' => array(
                '/engine/' => 'Движок сайта (весь)',
                '/lib/' => 'JS/CSS библиотеки',
                '/vendor/' => 'Composer пакеты',
                '/templates/' => 'Шаблоны',
                $uploadPath . 'avatars/' => 'Аватары пользователей',
                $uploadPath . 'files/' => 'Загруженные файлы'
            )
        ),
        'content' => array(
            'title' => 'Контентные разделы (открыть для индексации)',
            'items' => array(
                $uploadPath . 'dsn/' => 'DSN файлы',
                $uploadPath . 'images/$' => 'Изображения контента',
                '/gsmg/' => 'Карта сайта GSMG',
                '/search/' => 'Поиск',
                '/rss.xml' => 'RSS лента',
                '/login/' => 'Вход',
                '/logout/' => 'Выход',
                '/register/' => 'Регистрация',
                '/activate/' => 'Активация',
                '/lostpassword/' => 'Восстановление пароля',
                '/profile.html' => 'Профиль',
                '/users/' => 'Пользователи',
                '/page/' => 'Статические страницы',
                '/*print' => 'Версия для печати',
                '/*xml' => 'XML файлы',
                '/*201*' => 'Архивные материалы',
                '*/page/1$' => 'Первые страницы пагинации'
            )
        )
    );
}
// Get AI bots configuration
function robots_editor_get_ai_bots()
{
    return array(
        'search' => array(
            'title' => 'AI Search боты (для поиска)',
            'bots' => array(
                'OAI-SearchBot' => 'OpenAI Search (ChatGPT)',
                'PerplexityBot' => 'Perplexity AI Search',
                'Claude-Web' => 'Anthropic Claude Search',
                'Claude-SearchBot' => 'Anthropic Claude SearchBot'
            )
        ),
        'training' => array(
            'title' => 'AI Training боты (обучение)',
            'bots' => array(
                'GPTBot' => 'OpenAI GPTBot (обучение)',
                'ClaudeBot' => 'Anthropic ClaudeBot (обучение)',
                'Google-Extended' => 'Google AI (обучение)',
                'Meta-ExternalAgent' => 'Meta AI (обучение)',
                'Bytespider' => 'ByteDance/TikTok AI',
                'anthropic-ai' => 'Anthropic AI',
                'Omgilibot' => 'Omgili Bot',
                'FacebookBot' => 'Facebook Bot'
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
    $ai_search_allowed = pluginGetVariable('robots_editor', 'ai_search_allowed');
    $ai_training_blocked = pluginGetVariable('robots_editor', 'ai_training_blocked');
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
    // Add AI Search bots (if enabled)
    if ($ai_search_allowed) {
        $ai_bots = robots_editor_get_ai_bots();
        $content .= "# --- AI Search Bots (разрешены для индексации) ---\n";
        foreach ($ai_bots['search']['bots'] as $bot => $title) {
            $content .= "\nUser-agent: $bot\n";
            $content .= "Allow: /\n";
            // Block only system directories for AI search bots
            if (is_array($rules) && isset($rules['*'])) {
                foreach ($rules['*'] as $path => $allowed) {
                    if (!$allowed) {
                        $content .= "Disallow: $path\n";
                    }
                }
            }
        }
        $content .= "\n";
    }
    // Add AI Training bots (blocked if enabled)
    if ($ai_training_blocked) {
        $ai_bots = robots_editor_get_ai_bots();
        $content .= "# --- AI Training Bots (блокированы для обучения) ---\n";
        foreach ($ai_bots['training']['bots'] as $bot => $title) {
            $content .= "\nUser-agent: $bot\n";
            $content .= "Disallow: /\n";
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
    global $multiDomainName;
    $content = robots_editor_generate_content();
    // Determine path based on multisite context
    if (!empty($multiDomainName) && $multiDomainName !== 'main') {
        // Multisite: save to engine/conf/multi/{site_id}/robots.txt
        $path = root . 'conf/multi/' . $multiDomainName . '/robots.txt';
        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    } else {
        // Main site: save to engine/conf/robots.txt
        $path = root . 'conf/robots.txt';
    }
    // Детальная отладка
    $debug_info = array();
    $debug_info[] = "Мультисайт: " . ($multiDomainName ?: 'main');
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
    $log_dir = dirname($path);
    @file_put_contents($log_dir . '/robots_debug.log', $debug_log . "\n\n", FILE_APPEND);
    return $result !== false;
}
// Get current settings or set defaults
$current_rules = pluginGetVariable('robots_editor', 'rules');
$custom_rules = pluginGetVariable('robots_editor', 'custom_rules');
$auto_sitemap = pluginGetVariable('robots_editor', 'auto_sitemap');
$ai_search_allowed = pluginGetVariable('robots_editor', 'ai_search_allowed');
$ai_training_blocked = pluginGetVariable('robots_editor', 'ai_training_blocked');
// Set defaults if not set
if ($auto_sitemap === null) {
    pluginSetVariable('robots_editor', 'auto_sitemap', 1);
    $auto_sitemap = 1;
}
// Set default AI settings
if ($ai_search_allowed === null) {
    pluginSetVariable('robots_editor', 'ai_search_allowed', 1);
    $ai_search_allowed = 1;
}
if ($ai_training_blocked === null) {
    pluginSetVariable('robots_editor', 'ai_training_blocked', 1);
    $ai_training_blocked = 1;
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
    // Save settings - получаем все данные из POST
    $cfg = array(
        'rules' => $new_rules,
        'custom_rules' => isset($_POST['custom_rules']) ? $_POST['custom_rules'] : '',
        'auto_sitemap' => isset($_POST['auto_sitemap']) ? (int)$_POST['auto_sitemap'] : 0,
        'ai_search_allowed' => isset($_POST['ai_search_allowed']) ? (int)$_POST['ai_search_allowed'] : 0,
        'ai_training_blocked' => isset($_POST['ai_training_blocked']) ? (int)$_POST['ai_training_blocked'] : 0
    );
    // Update plugin variables
    foreach ($cfg as $key => $value) {
        pluginSetVariable('robots_editor', $key, $value);
    }
    // Save robots.txt
    if (robots_editor_save_file()) {
        global $multiDomainName;
        $saved_content = robots_editor_generate_content();
        // Show actual file path
        if (!empty($multiDomainName) && $multiDomainName !== 'main') {
            $file_path = root . 'conf/multi/' . $multiDomainName . '/robots.txt';
        } else {
            $file_path = root . 'conf/robots.txt';
        }
        msg(array("type" => "info", "text" => $lang['robots_editor:saved']));
        msg(array("type" => "info", "text" => "Файл сохранен: " . str_replace('\\', '/', $file_path)));
        msg(array("type" => "info", "text" => "Доступен по адресу: " . home . "/robots.txt"));
    } else {
        global $multiDomainName;
        $error = "Ошибка при сохранении robots.txt! ";
        // Show actual file path
        if (!empty($multiDomainName) && $multiDomainName !== 'main') {
            $path = root . 'conf/multi/' . $multiDomainName . '/robots.txt';
            $log_path = root . 'conf/multi/' . $multiDomainName . '/robots_debug.log';
        } else {
            $path = root . 'conf/robots.txt';
            $log_path = root . 'conf/robots_debug.log';
        }
        $error .= "<br>Подробности в файле: " . $log_path;
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
    global $multiDomainName;
    if (!empty($multiDomainName) && $multiDomainName !== 'main') {
        $current_robots_path = root . 'conf/multi/' . $multiDomainName . '/robots.txt';
    } else {
        $current_robots_path = root . 'conf/robots.txt';
    }
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
    $status_html .= '<strong>Статус файла robots.txt:</strong><br>';
    if ($file_exists) {
        $status_html .= 'Файл существует (' . $file_size . ' байт)';
        if (!$file_writable) {
            $status_html .= '<br><span style="color: red;">⚠️ Внимание: файл недоступен для записи!</span>';
        }
    } else {
        $status_html .= 'Файл не существует, будет создан автоматически';
    }
    $status_html .= '<hr style="margin: 10px 0;">';
    $status_html .= '<strong>Путь:</strong><br>' . htmlspecialchars($current_robots_path);
    $status_html .= '<hr style="margin: 10px 0;">';
    $status_html .= '<strong>Доступен по URL:</strong><br><a href="' . home . '/robots.txt" target="_blank">' . home . '/robots.txt</a>';
    $status_html .= '</div>';
    // Build Bootstrap 4 Tabs Interface
    $tabs_html = '<div class="robots-editor-tabs">';
    // Nav Tabs
    $tabs_html .= '<ul class="nav nav-tabs mb-3" role="tablist">';
    $tabs_html .= '<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-settings" role="tab">⚙️ Основные настройки</a></li>';
    $tabs_html .= '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-system" role="tab">🗂️ Системные папки</a></li>';
    $tabs_html .= '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-content" role="tab">📁 Контентные разделы</a></li>';
    $tabs_html .= '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-custom" role="tab">✏️ Дополнительные правила</a></li>';
    $tabs_html .= '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-preview" role="tab">👁️ Предпросмотр</a></li>';
    $tabs_html .= '</ul>';
    // Tab Content
    $tabs_html .= '<div class="tab-content">';
    // TAB 1: Basic Settings
    $tabs_html .= '<div class="tab-pane fade show active" id="tab-settings" role="tabpanel">';
    $tabs_html .= '<div class="card"><div class="card-body">';
    $tabs_html .= '<h5 class="card-title">Основные настройки robots.txt</h5>';
    $tabs_html .= '<div class="form-group row">';
    $tabs_html .= '<label class="col-md-4 col-form-label">' . $lang['robots_editor:auto_sitemap'] . '</label>';
    $tabs_html .= '<div class="col-md-8">';
    $tabs_html .= '<select name="auto_sitemap" class="form-control">';
    $tabs_html .= '<option value="1"' . ($auto_sitemap ? ' selected' : '') . '>Да</option>';
    $tabs_html .= '<option value="0"' . (!$auto_sitemap ? ' selected' : '') . '>Нет</option>';
    $tabs_html .= '</select>';
    $tabs_html .= '<small class="form-text text-muted">' . $lang['robots_editor:auto_sitemap#desc'] . '</small>';
    $tabs_html .= '</div></div>';
    $tabs_html .= '<div class="form-group row">';
    $tabs_html .= '<label class="col-md-4 col-form-label">Разрешить AI Search ботов</label>';
    $tabs_html .= '<div class="col-md-8">';
    $tabs_html .= '<select name="ai_search_allowed" class="form-control">';
    $tabs_html .= '<option value="1"' . ($ai_search_allowed ? ' selected' : '') . '>Да</option>';
    $tabs_html .= '<option value="0"' . (!$ai_search_allowed ? ' selected' : '') . '>Нет</option>';
    $tabs_html .= '</select>';
    $tabs_html .= '<small class="form-text text-muted">Разрешить поисковым AI-ботам (ChatGPT Search, Perplexity, Claude Search) индексировать сайт.</small>';
    $tabs_html .= '</div></div>';
    $tabs_html .= '<div class="form-group row">';
    $tabs_html .= '<label class="col-md-4 col-form-label">Блокировать AI Training ботов</label>';
    $tabs_html .= '<div class="col-md-8">';
    $tabs_html .= '<select name="ai_training_blocked" class="form-control">';
    $tabs_html .= '<option value="1"' . ($ai_training_blocked ? ' selected' : '') . '>Да</option>';
    $tabs_html .= '<option value="0"' . (!$ai_training_blocked ? ' selected' : '') . '>Нет</option>';
    $tabs_html .= '</select>';
    $tabs_html .= '<small class="form-text text-muted">Запретить AI-ботам (GPTBot, ClaudeBot, Google-Extended) использовать контент для обучения.</small>';
    $tabs_html .= '</div></div>';
    $tabs_html .= '</div></div>';
    $tabs_html .= '</div>';
    // TAB 2 & 3: System and Content folders
    $category_tabs = array(
        'system' => array('id' => 'tab-system', 'title' => 'Системные папки'),
        'content' => array('id' => 'tab-content', 'title' => 'Контентные разделы')
    );
    foreach ($system_items as $category_key => $category) {
        $tab_info = $category_tabs[$category_key];
        $tabs_html .= '<div class="tab-pane fade" id="' . $tab_info['id'] . '" role="tabpanel">';
        $tabs_html .= '<div class="card"><div class="card-body">';
        $tabs_html .= '<h5 class="card-title">' . $category['title'] . '</h5>';
        $tabs_html .= '<div class="table-responsive">';
        $tabs_html .= '<table class="table table-bordered table-striped table-hover">';
        $tabs_html .= '<thead class="thead-dark"><tr>';
        $tabs_html .= '<th width="30%">Путь</th>';
        $tabs_html .= '<th width="30%">Описание</th>';
        foreach ($user_agents as $ua => $title) {
            $tabs_html .= '<th class="text-center" width="13%">' . $title . '</th>';
        }
        $tabs_html .= '</tr></thead><tbody>';
        foreach ($category['items'] as $path => $title) {
            $tabs_html .= '<tr>';
            $tabs_html .= '<td><code>' . htmlspecialchars($path) . '</code></td>';
            $tabs_html .= '<td>' . htmlspecialchars($title) . '</td>';
            foreach ($user_agents as $ua => $ua_title) {
                $checked = (isset($current_rules[$ua][$path]) && $current_rules[$ua][$path]) ? 'checked' : '';
                $tabs_html .= '<td class="text-center">';
                $tabs_html .= '<input type="checkbox" name="rule_' . $ua . '_' . md5($path) . '" ' . $checked . ' value="1">';
                $tabs_html .= '</td>';
            }
            $tabs_html .= '</tr>';
        }
        $tabs_html .= '</tbody></table>';
        $tabs_html .= '</div>';
        $tabs_html .= '</div></div>';
        $tabs_html .= '</div>';
    }
    // TAB 4: Custom Rules
    $tabs_html .= '<div class="tab-pane fade" id="tab-custom" role="tabpanel">';
    $tabs_html .= '<div class="card"><div class="card-body">';
    $tabs_html .= '<h5 class="card-title">Дополнительные правила</h5>';
    $tabs_html .= '<div class="form-group">';
    $tabs_html .= '<textarea name="custom_rules" rows="10" class="form-control" placeholder="Добавьте дополнительные правила robots.txt. Например:&#10;Clean-param: ref /some_dir/&#10;Crawl-delay: 2&#10;User-agent: SpecialBot&#10;Disallow: /private/">' . htmlspecialchars($custom_rules ? $custom_rules : '') . '</textarea>';
    $tabs_html .= '<small class="form-text text-muted">Можно добавить любые дополнительные директивы: Clean-param, Crawl-delay, User-agent и др.</small>';
    $tabs_html .= '</div>';
    $tabs_html .= '</div></div>';
    $tabs_html .= '</div>';
    // TAB 5: Preview
    $preview_content = robots_editor_generate_content();
    $tabs_html .= '<div class="tab-pane fade" id="tab-preview" role="tabpanel">';
    $tabs_html .= '<div class="row">';
    // Left column - Preview
    $tabs_html .= '<div class="col-lg-8">';
    $tabs_html .= '<div class="card"><div class="card-body">';
    $tabs_html .= '<h5 class="card-title">Предпросмотр robots.txt</h5>';
    $tabs_html .= '<pre style="max-height: 500px; overflow: auto; background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">';
    $tabs_html .= htmlspecialchars($preview_content);
    $tabs_html .= '</pre>';
    $tabs_html .= '</div></div>';
    $tabs_html .= '</div>';
    // Right column - Status
    $tabs_html .= '<div class="col-lg-4">';
    $tabs_html .= '<div class="card"><div class="card-body">';
    $tabs_html .= '<h5 class="card-title">Статус файла</h5>';
    $tabs_html .= $status_html;
    $tabs_html .= '</div></div>';
    $tabs_html .= '</div>';
    $tabs_html .= '</div>';
    $tabs_html .= '</div>';
    // Close tab-content and wrapper
    $tabs_html .= '</div>';
    $tabs_html .= '</div>';
    // Add tabs to config
    array_push($cfgX, array(
        'name' => 'robots_tabs',
        'title' => '',
        'descr' => $tabs_html,
        'type' => 'html'
    ));
    $cfg = array(array(
        'mode' => 'group',
        'title' => $lang['robots_editor:group_config'],
        'entries' => $cfgX
    ));
    // Output Bootstrap 4 compatible CSS
    echo '<style>
    /* Force full width for this plugin */
    .robots-editor-tabs {
        width: 100% !important;
        max-width: none !important;
    }
    /* Override NGCMS table layout */
    table.extra-config tr td[colspan="2"] {
        padding: 0 !important;
    }
    table.extra-config tr td[width="50%"] {
        width: 100% !important;
    }
    /* Tabs styling */
    .robots-editor-tabs .nav-tabs {
        border-bottom: 2px solid #007bff;
        margin-bottom: 20px;
    }
    .robots-editor-tabs .nav-link {
        color: #495057;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 20px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .robots-editor-tabs .nav-link:hover {
        border-bottom-color: #0056b3;
        background-color: #f8f9fa;
    }
    .robots-editor-tabs .nav-link.active {
        color: #007bff;
        background-color: transparent;
        border-bottom-color: #007bff;
        font-weight: bold;
    }
    .robots-editor-tabs .card {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .robots-editor-tabs .card-title {
        color: #007bff;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    .robots-editor-tabs .table thead th {
        background-color: #007bff;
        color: white;
        font-weight: 600;
        border: none;
    }
    .robots-editor-tabs .table td code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9em;
    }
    .robots-editor-tabs input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .robots-editor-tabs .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    .robots-editor-tabs .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .robots-editor-tabs .alert {
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .robots-editor-tabs .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    .robots-editor-tabs .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }
    .robots-editor-tabs .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    .robots-editor-tabs pre {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    .robots-editor-tabs .form-text {
        color: #6c757d;
        font-size: 0.875rem;
    }
    .robots-editor-tabs .tab-pane {
        display: none;
    }
    .robots-editor-tabs .tab-pane.show {
        display: block;
    }
    </style>';
    // Output JavaScript for tabs functionality
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tab switching functionality
        var tabLinks = document.querySelectorAll(".robots-editor-tabs .nav-link");
        tabLinks.forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                // Remove active class from all tabs
                tabLinks.forEach(function(l) {
                    l.classList.remove("active");
                });
                // Hide all tab panes
                var tabPanes = document.querySelectorAll(".robots-editor-tabs .tab-pane");
                tabPanes.forEach(function(pane) {
                    pane.classList.remove("show", "active");
                });
                // Add active class to clicked tab
                link.classList.add("active");
                // Show corresponding tab pane
                var targetId = link.getAttribute("href");
                var targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.add("show", "active");
                }
            });
        });
    });
    </script>';
    generate_config_page($plugin, $cfg);
}
