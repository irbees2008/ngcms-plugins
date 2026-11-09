<?php
if (!defined('NGCMS')) {
    die('HAL');
}

pluginsLoadConfig();
LoadPluginLang('similar', 'config', '', 'similar', '#');

global $lang, $twig, $mysql;
$similarLang = isset($lang['similar']) && is_array($lang['similar'])
    ? $lang['similar']
    : [];
$requiredLangKeys = [
    'config_title',
    'action_settings',
    'config_description',
    'enabled',
    'yes_label',
    'no_label',
    'count',
    'count_help',
    'template_source',
    'site_template',
    'plugin_template',
    'template_source_help',
    'integration',
    'integration_enabled',
    'integration_mode',
    'integration_help',
    'mode_export',
    'mode_short',
    'mode_full',
    'save',
    'rebuild',
    'rebuild_help',
    'clear_cache',
    'cache_cleared',
    'rebuild_done',
    'config_saved',
    'config_invalid',
];
foreach ($requiredLangKeys as $key) {
    if (!isset($similarLang[$key]) || !is_string($similarLang[$key]) || $similarLang[$key] === '') {
        throw new RuntimeException('Missing similar plugin configuration translation: ' . $key);
    }
}

function similar_config_value($key, $default)
{
    $value = pluginGetVariable('similar', $key);
    return $value === null || $value === false || $value === '' ? $default : $value;
}

function similar_config_clear_cache_or_fail()
{
    include_once __DIR__ . '/inc/similar.php';
    if (!plugin_similar_clear_cache()) {
        throw new RuntimeException('Unable to clear similar plugin output cache.');
    }
}

$settings = [
    'localsource' => (int) similar_config_value('localsource', 1),
    'similar_enabled' => (int) similar_config_value('similar_enabled', 1),
    'count' => (int) similar_config_value('count', 5),
    'pcall' => (int) similar_config_value('pcall', 0),
    'pcall_mode' => (int) similar_config_value('pcall_mode', 0),
];
$settings['localsource'] = $settings['localsource'] === 0 ? 0 : 1;
$settings['similar_enabled'] = $settings['similar_enabled'] === 1 ? 1 : 0;
$settings['count'] = $settings['count'] >= 1 && $settings['count'] <= 20 ? $settings['count'] : 5;
$settings['pcall'] = $settings['pcall'] === 1 ? 1 : 0;
$settings['pcall_mode'] = in_array($settings['pcall_mode'], [0, 1, 2], true) ? $settings['pcall_mode'] : 0;

$toasts = [];
if (isset($_POST['operation'])) {
    if (!isset($_REQUEST['action'], $_POST['token'])
        || $_REQUEST['action'] !== 'commit'
        || !is_string($_POST['token'])
        || !hash_equals((string) genUToken('admin.extra-config'), $_POST['token'])) {
        throw new RuntimeException('Invalid similar plugin configuration request token.');
    }
    if (!is_string($_POST['operation'])) {
        throw new RuntimeException('Invalid similar plugin configuration operation.');
    }

    if ($_POST['operation'] === 'save_settings') {
        $localsource = filter_var($_POST['localsource'] ?? null, FILTER_VALIDATE_INT);
        $enabled = filter_var($_POST['similar_enabled'] ?? null, FILTER_VALIDATE_INT);
        $count = filter_var($_POST['count'] ?? null, FILTER_VALIDATE_INT);
        $pcall = filter_var($_POST['pcall'] ?? '0', FILTER_VALIDATE_INT);
        $pcallMode = filter_var($_POST['pcall_mode'] ?? null, FILTER_VALIDATE_INT);

        if (!in_array($localsource, [0, 1], true)
            || !in_array($enabled, [0, 1], true)
            || $count === false || $count < 1 || $count > 20
            || !in_array($pcall, [0, 1], true)
            || !in_array($pcallMode, [0, 1, 2], true)) {
            $toasts[] = ['type' => 'error', 'message' => $similarLang['config_invalid']];
        } else {
            $settings = [
                'localsource' => $localsource,
                'similar_enabled' => $enabled,
                'count' => $count,
                'pcall' => $pcall,
                'pcall_mode' => $pcallMode,
            ];
            foreach ($settings as $key => $value) {
                pluginSetVariable('similar', $key, $value);
            }
            if (!pluginsSaveConfig()) {
                throw new RuntimeException('Unable to save similar plugin settings.');
            }
            similar_config_clear_cache_or_fail();
            $toasts[] = ['type' => 'success', 'message' => $similarLang['config_saved']];
        }
    } elseif ($_POST['operation'] === 'rebuild') {
        $mysql->query('truncate table ' . prefix . '_similar_index');
        $mysql->query('update ' . prefix . '_news set similar_status = 0');
        similar_config_clear_cache_or_fail();
        $toasts[] = ['type' => 'success', 'message' => $similarLang['rebuild_done']];
    } elseif ($_POST['operation'] === 'clear_cache') {
        similar_config_clear_cache_or_fail();
        $toasts[] = ['type' => 'success', 'message' => $similarLang['cache_cleared']];
    } else {
        throw new RuntimeException('Unknown similar plugin configuration operation.');
    }
}

$templatePaths = locatePluginTemplates(['config'], 'similar', 1, '', 'config');
if (empty($templatePaths['config'])) {
    throw new RuntimeException('The similar plugin configuration template could not be located.');
}

$view = $twig->loadTemplate($templatePaths['config'] . 'config.tpl');
$documentationButton = '';
$documentationModal = '';
if (function_exists('plugin_documentation_markup')) {
    list($documentationButton, $documentationModal) = plugin_documentation_markup('similar');
}
$toastJson = json_encode($toasts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($toastJson === false) {
    throw new RuntimeException('Unable to encode similar plugin toast notifications.');
}
print $view->render([
    'settings' => $settings,
    'toast_json' => $toastJson,
    'token' => htmlspecialchars((string) genUToken('admin.extra-config'), ENT_QUOTES, 'UTF-8'),
    'lang' => $similarLang,
    'plugin_id' => 'similar',
    'action' => $similarLang['action_settings'],
    'extras_label' => isset($lang['extras']) ? $lang['extras'] : 'Plugins',
    'documentation_button' => $documentationButton,
    'documentation_modal' => $documentationModal,
]);
