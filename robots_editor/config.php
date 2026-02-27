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
    global $lang;
    return array(
        'system' => array(
            'title' => $lang['robots_editor:sys.cat.system'],
            'items' => array(
                '/engine/' => $lang['robots_editor:sys.engine'],
                '/templates/' => $lang['robots_editor:sys.templates'],
                '/uploads/avatars/' => $lang['robots_editor:sys.avatars'],
                '/uploads/files/' => $lang['robots_editor:sys.files'],
                '/uploads/images/thumb/' => $lang['robots_editor:sys.thumbs'],
                '/uploads/photos/' => $lang['robots_editor:sys.photos'],
                '/plugin/' => $lang['robots_editor:sys.plugin'],
                '/lib/' => $lang['robots_editor:sys.lib'],
                '/webstat/' => $lang['robots_editor:sys.webstat'],
                '/cache/' => $lang['robots_editor:sys.cache'],
                '/tmp/' => $lang['robots_editor:sys.tmp'],
                '/admin/' => $lang['robots_editor:sys.admin']
            )
        ),
        'content' => array(
            'title' => $lang['robots_editor:sys.cat.content'],
            'items' => array(
                '/uploads/dsn/' => $lang['robots_editor:sys.dsn'],
                '/uploads/images/$' => $lang['robots_editor:sys.images'],
                '/plugin/gsmg/' => $lang['robots_editor:sys.gsmg'],
                '/plugin/sitemap/' => $lang['robots_editor:sys.sitemap'],
                '/search/' => $lang['robots_editor:sys.search'],
                '/rss.xml' => $lang['robots_editor:sys.rss'],
                '/login/' => $lang['robots_editor:sys.login'],
                '/logout/' => $lang['robots_editor:sys.logout'],
                '/register/' => $lang['robots_editor:sys.register'],
                '/activate/' => $lang['robots_editor:sys.activate'],
                '/lostpassword/' => $lang['robots_editor:sys.lostpassword'],
                '/profile.html' => $lang['robots_editor:sys.profile'],
                '/users/' => $lang['robots_editor:sys.users'],
                '/page/' => $lang['robots_editor:sys.page'],
                '/*print' => $lang['robots_editor:sys.print'],
                '/*xml' => $lang['robots_editor:sys.xml'],
                '/*201*' => $lang['robots_editor:sys.archive'],
                '*/page/1$' => $lang['robots_editor:sys.pagination']
            )
        )
    );
}
// Get AI bots configuration
function robots_editor_get_ai_bots()
{
    global $lang;
    return array(
        'search' => array(
            'title' => $lang['robots_editor:ai.search.title'],
            'bots' => array(
                'OAI-SearchBot' => 'OpenAI Search (ChatGPT)',
                'PerplexityBot' => 'Perplexity AI Search',
                'Claude-Web' => 'Anthropic Claude Search',
                'Claude-SearchBot' => 'Anthropic Claude SearchBot'
            )
        ),
        'training' => array(
            'title' => $lang['robots_editor:ai.training.title'],
            'bots' => array(
                'GPTBot' => 'OpenAI GPTBot',
                'ClaudeBot' => 'Anthropic ClaudeBot',
                'Google-Extended' => 'Google AI',
                'Meta-ExternalAgent' => 'Meta AI',
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
    global $lang;
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
        '*' => $lang['robots_editor:ua.other']
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
        $content .= $lang['robots_editor:gen.search_bots'] . "\n";
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
        $content .= $lang['robots_editor:gen.training_bots'] . "\n";
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
    // Save settings - ФИКС: получаем кастомные правила из POST
    $cfg = array(
        'rules' => $new_rules,
        'custom_rules' => $_POST['custom_rules'], // правильно получаем из POST
        'auto_sitemap' => isset($_POST['auto_sitemap']) ? 1 : 0,
        'ai_search_allowed' => isset($_POST['ai_search_allowed']) ? 1 : 0,
        'ai_training_blocked' => isset($_POST['ai_training_blocked']) ? 1 : 0
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
        msg(array("type" => "info", "text" => sprintf($lang['robots_editor:msg.saved_path'], $file_path)));
    } else {
        $site_root = get_site_root();
        $path = $site_root . 'robots.txt';
        $error = $lang['robots_editor:save_error'] . " ";
        $error .= "<br>" . sprintf($lang['robots_editor:msg.save_error_details'], $site_root . 'robots_debug.log');
        $error .= "<br>" . $lang['robots_editor:msg.save_error_manual'];
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
        '*' => $lang['robots_editor:ua.all']
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
    $status_html .= '<strong>' . $lang['robots_editor:ui.file_status.header'] . '</strong> ';
    if ($file_exists) {
        $status_html .= sprintf($lang['robots_editor:ui.file_status.exists'], $file_size);
        if (!$file_writable) {
            $status_html .= '<br><span style="color: red;">' . $lang['robots_editor:ui.file_status.not_writable'] . '</span>';
        }
    } else {
        $status_html .= $lang['robots_editor:ui.file_status.not_exists'];
    }
    $status_html .= '<br><strong>' . $lang['robots_editor:ui.file_status.path'] . '</strong> ' . $current_robots_path;
    $status_html .= '</div>';
    array_push($cfgX, array(
        'name' => 'file_status',
        'title' => $lang['robots_editor:ui.file_status.title'],
        'descr' => $status_html,
        'type' => 'plain'
    ));
    // Auto sitemap option
    array_push($cfgX, array(
        'name' => 'auto_sitemap',
        'title' => $lang['robots_editor:auto_sitemap'],
        'descr' => $lang['robots_editor:auto_sitemap#desc'],
        'type' => 'select',
        'values' => array('1' => $lang['robots_editor:ui.opt.yes'], '0' => $lang['robots_editor:ui.opt.no']),
        'value' => $auto_sitemap
    ));
    // AI Search bots option
    array_push($cfgX, array(
        'name' => 'ai_search_allowed',
        'title' => $lang['robots_editor:ai_search_allowed'],
        'descr' => $lang['robots_editor:ai_search_allowed#desc'],
        'type' => 'select',
        'values' => array('1' => $lang['robots_editor:ui.opt.yes'], '0' => $lang['robots_editor:ui.opt.no']),
        'value' => $ai_search_allowed
    ));
    // AI Training bots option
    array_push($cfgX, array(
        'name' => 'ai_training_blocked',
        'title' => $lang['robots_editor:ai_training_blocked'],
        'descr' => $lang['robots_editor:ai_training_blocked#desc'],
        'type' => 'select',
        'values' => array('1' => $lang['robots_editor:ui.opt.yes'], '0' => $lang['robots_editor:ui.opt.no']),
        'value' => $ai_training_blocked
    ));
    // Rules table
    $rules_html = '<div class="robots-rules-table">';
    foreach ($system_items as $category) {
        $rules_html .= '<h4>' . $category['title'] . '</h4>';
        $rules_html .= '<table class="table table-bordered table-striped">';
        $rules_html .= '<thead><tr><th>' . $lang['robots_editor:ui.rules.col.path'] . '</th><th>' . $lang['robots_editor:ui.rules.col.descr'] . '</th>';
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
        'title' => $lang['robots_editor:ui.rules.title'],
        'descr' => $rules_html,
        'type' => 'plain'
    ));
    // Custom rules - ФИКС: правильное текстовое поле
    $custom_rules_html = '<textarea name="custom_rules" rows="6" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="' . htmlspecialchars($lang['robots_editor:ui.custom_rules.placeholder']) . '">' . htmlspecialchars($custom_rules ? $custom_rules : '') . '</textarea>';
    $custom_rules_html .= '<p class="help-block">' . $lang['robots_editor:ui.custom_rules.help'] . '</p>';
    array_push($cfgX, array(
        'name' => 'custom_rules_info',
        'title' => $lang['robots_editor:custom_rules'],
        'descr' => $custom_rules_html,
        'type' => 'plain'
    ));
    // Preview
    $preview_content = robots_editor_generate_content();
    $preview_html = '<div class="alert alert-info">';
    $preview_html .= '<h4>' . $lang['robots_editor:ui.preview.header'] . '</h4>';
    $preview_html .= '<pre style="max-height: 300px; overflow: auto; background: #f8f9fa; padding: 10px; border: 1px solid #ddd;">';
    $preview_html .= htmlspecialchars($preview_content);
    $preview_html .= '</pre>';
    $preview_html .= '</div>';
    array_push($cfgX, array(
        'name' => 'preview',
        'title' => $lang['robots_editor:ui.preview.title'],
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
