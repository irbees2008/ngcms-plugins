<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
// Load lang file
LoadPluginLang('robots_editor', 'config', '', '', ':');
// Function to generate robots.txt content (duplicated from config.php for TWIG)
function robots_editor_generate_content_twig()
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
