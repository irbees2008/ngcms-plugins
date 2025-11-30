<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

function plugin_ai_rewriter_install($action)
{
    switch ($action) {
        case 'confirm':
            generate_install_page('ai_rewriter', 'Плагин рерайта новостей ИИ будет установлен. Таблицы БД не требуются.');
            break;
        case 'autoapply':
        case 'apply':
            // Defaults
            pluginSetVariable('ai_rewriter', 'provider', '');
            pluginSetVariable('ai_rewriter', 'model', 'gpt-4o-mini');
            pluginSetVariable('ai_rewriter', 'api_key', '');
            pluginSetVariable('ai_rewriter', 'api_base', '');
            pluginSetVariable('ai_rewriter', 'originality', 60);
            pluginSetVariable('ai_rewriter', 'enable_on_add', 0);
            pluginSetVariable('ai_rewriter', 'enable_on_edit', 0);
            pluginSetVariable('ai_rewriter', 'tone', '');
            pluginSetVariable('ai_rewriter', 'temperature', 0.7);
            pluginSetVariable('ai_rewriter', 'timeout', 20);
            pluginsSaveConfig();
            plugin_mark_installed('ai_rewriter');
            break;
    }
    return true;
}
