<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
// Load lang file
LoadPluginLang('robots_editor', 'config', '', '', ':');

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

// Function to generate robots.txt content (duplicated from config.php for TWIG)
function robots_editor_generate_content_twig()
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

    // Add custom rules
    if (!empty($custom_rules)) {
        $content .= $custom_rules . "\n\n";
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
// TWIG function for frontend if needed
function plugin_robots_editor_show()
{
    return robots_editor_generate_content_twig();
}
twigRegisterFunction('robots_editor', 'show', 'plugin_robots_editor_show');
