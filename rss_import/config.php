<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('rss_import', 'config', '', '', ':');
include_once __DIR__ . '/storage.php';

$action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : 'list';
if ($action === 'update') {
    rss_import_require_post();
    rss_import_storage_update();
    print_commit_complete('rss_import');
}

if (!rss_import_storage_table_exists() || rss_import_storage_should_offer_update()) {
    rss_import_render('settings', 'Обновление плагина', 'conf.update', array(
        'has_legacy_profiles' => rss_import_storage_has_legacy_profiles(),
    ));
    return;
}

switch ($action) {
    case 'add':
        rss_import_edit();
        break;
    case 'edit':
        rss_import_edit((int)($_GET['id'] ?? 0));
        break;
    case 'save':
        rss_import_save();
        break;
    case 'delete':
        rss_import_delete();
        break;
    case 'toggle':
        rss_import_toggle();
        break;
    case 'move_up':
        rss_import_move('up');
        break;
    case 'move_down':
        rss_import_move('down');
        break;
    case 'settings':
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            rss_import_save_settings();
        } else {
            rss_import_settings();
        }
        break;
    case 'list':
    default:
        rss_import_list();
        break;
}

function rss_import_require_post()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || !isset($_POST['token'])
        || !is_string($_POST['token'])
        || !hash_equals((string)genUToken('admin.extra-config'), $_POST['token'])) {
        throw new RuntimeException('Invalid RSS Import configuration request token.');
    }
}

function rss_import_render($activeTab, $actionLabel, $bodyTemplate, $bodyVars)
{
    global $tpl, $lang;

    $tpath = locatePluginTemplates(array('conf.main', $bodyTemplate), 'rss_import', 1);
    $token = htmlspecialchars((string)genUToken('admin.extra-config'), ENT_QUOTES, 'UTF-8');
    $storageExists = rss_import_storage_table_exists();
    $profileCount = $storageExists ? rss_import_storage_count() : 0;
    $canAddProfile = $storageExists && $profileCount < RSS_IMPORT_MAX_PROFILES;
    $bodyVars['token'] = $token;
    $bodyVars['profile_count'] = $profileCount;
    $bodyVars['profile_limit'] = RSS_IMPORT_MAX_PROFILES;
    $bodyVars['can_add_profile'] = $canAddProfile;
    $tpl->template($bodyTemplate, $tpath[$bodyTemplate]);
    $tpl->vars($bodyTemplate, array('vars' => $bodyVars));

    $tabs = array('list' => '', 'add' => '', 'settings' => '');
    $tabs[$activeTab] = 'active';
    $mainVars = array(
        'entries' => $tpl->show($bodyTemplate),
        'action' => $actionLabel,
        'profile_count' => $profileCount,
        'profile_limit' => RSS_IMPORT_MAX_PROFILES,
        'can_add_profile' => $canAddProfile,
        'storage_exists' => $storageExists,
        'tab_list_active' => $tabs['list'],
        'tab_add_active' => $tabs['add'],
        'tab_settings_active' => $tabs['settings'],
        'lang' => $lang,
    );
    if (function_exists('plugin_documentation_markup')) {
        list($mainVars['documentation_button'], $mainVars['documentation_modal']) = plugin_documentation_markup('rss_import');
    } else {
        $mainVars['documentation_button'] = '';
        $mainVars['documentation_modal'] = '';
    }
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', array('vars' => $mainVars));
    print $tpl->show('conf.main');
}

function rss_import_list()
{
    $items = array();
    foreach (rss_import_storage_get_all() as $row) {
        $items[] = array(
            'id' => (int)$row['id'],
            'profile_id' => (string)$row['profile_id'],
            'title' => (string)$row['title'],
            'template' => (string)$row['template'],
            'feed_url' => (string)$row['feed_url'],
            'item_count' => (int)$row['item_count'],
            'enabled' => (int)$row['enabled'] === 1,
        );
    }
    rss_import_render('list', 'Список RSS-лент', 'conf.list', array('items' => $items));
}

function rss_import_default_profile()
{
    return array(
        'id' => 0,
        'profile_id' => '',
        'title' => '',
        'enabled' => 1,
        'feed_url' => '',
        'template' => 'rss1',
        'item_count' => 10,
        'title_length' => 100,
        'short_length' => 100,
        'include_content' => 0,
        'show_image' => 1,
        'image_source' => 'enclosure',
    );
}

function rss_import_template_options()
{
    global $config;

    $theme = $config['theme'] ?? 'default';
    $directories = array(
        site_root . 'templates/' . $theme . '/plugins/rss_import',
        tpl_site . 'plugins/rss_import',
        extras_dir . '/rss_import/tpl',
    );
    $options = array();
    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            continue;
        }
        foreach (new DirectoryIterator($directory) as $entry) {
            if ($entry->isDot() || !$entry->isDir()) {
                continue;
            }
            $template = $entry->getFilename();
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $template)
                || !is_file($entry->getPathname() . '/rss.tpl')
                || !is_file($entry->getPathname() . '/entries.tpl')) {
                continue;
            }
            $options[$template] = array('value' => $template, 'label' => $template);
        }
    }
    ksort($options, SORT_NATURAL | SORT_FLAG_CASE);
    return array_values($options);
}

function rss_import_edit($id = 0)
{
    if (!$id && rss_import_storage_count() >= RSS_IMPORT_MAX_PROFILES) {
        rss_import_render('list', 'Достигнут лимит профилей', 'conf.limit', array(
            'profile_count' => rss_import_storage_count(),
            'profile_limit' => RSS_IMPORT_MAX_PROFILES,
        ));
        return;
    }

    $profile = rss_import_default_profile();
    if ($id) {
        $row = rss_import_storage_get_by_id($id);
        if (!is_array($row)) {
            throw new RuntimeException('RSS Import profile not found: #' . $id);
        }
        $profile = array_merge($profile, $row);
    }
    $templateOptions = rss_import_template_options();
    $hasTemplate = false;
    foreach ($templateOptions as $option) {
        if ($option['value'] === $profile['template']) {
            $hasTemplate = true;
            break;
        }
    }
    if (!$hasTemplate && preg_match('/^[A-Za-z0-9_-]+$/', (string)$profile['template'])) {
        $templateOptions[] = array('value' => $profile['template'], 'label' => $profile['template'] . ' (не найден)');
    }

    rss_import_render($id ? 'list' : 'add', $id ? 'Редактирование RSS-ленты' : 'Добавление RSS-ленты', 'conf.form', array(
        'profile' => $profile,
        'mode' => $id ? 'edit' : 'add',
        'template_options' => $templateOptions,
    ));
}

function rss_import_post_integer($key, $label, $min, $max)
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value < $min || $value > $max) {
        throw new RuntimeException('Проверьте значение поля «' . $label . '».');
    }
    return $value;
}

function rss_import_profile_from_post()
{
    $profileId = trim((string)($_POST['profile_id'] ?? ''));
    if ($profileId === '' || strlen($profileId) > 100 || !preg_match('/^[A-Za-z0-9_-]+$/', $profileId)) {
        throw new RuntimeException('Укажите ID блока латинскими буквами, цифрами, дефисом или подчёркиванием (до 100 символов).');
    }
    $title = trim((string)($_POST['title'] ?? ''));
    if ($title === '' || mb_strlen($title) > 190) {
        throw new RuntimeException('Название RSS-блока обязательно и не должно превышать 190 символов.');
    }
    $feedUrl = trim((string)($_POST['feed_url'] ?? ''));
    $urlParts = parse_url($feedUrl);
    if ($feedUrl === '' || mb_strlen($feedUrl) > 2000 || !filter_var($feedUrl, FILTER_VALIDATE_URL)
        || !is_array($urlParts) || !in_array(strtolower($urlParts['scheme'] ?? ''), array('http', 'https'), true)) {
        throw new RuntimeException('Укажите корректный URL RSS-ленты.');
    }
    $template = trim((string)($_POST['template'] ?? ''));
    if ($template === '' || strlen($template) > 190 || !preg_match('/^[A-Za-z0-9_-]+$/', $template)) {
        throw new RuntimeException('Выберите корректный каталог шаблона.');
    }
    $templateExists = false;
    foreach (rss_import_template_options() as $option) {
        if ($option['value'] === $template) {
            $templateExists = true;
            break;
        }
    }
    if (!$templateExists) {
        throw new RuntimeException('Выбранный каталог шаблонов не найден или не содержит rss.tpl и entries.tpl.');
    }
    $imageSource = (string)($_POST['image_source'] ?? '');
    if (!in_array($imageSource, array('desc', 'enclosure'), true)) {
        throw new RuntimeException('Некорректный источник изображения.');
    }

    return array(
        'profile_id' => $profileId,
        'title' => $title,
        'enabled' => isset($_POST['enabled']) && !is_array($_POST['enabled']) && (string)$_POST['enabled'] === '1' ? 1 : 0,
        'feed_url' => $feedUrl,
        'template' => $template,
        'item_count' => rss_import_post_integer('item_count', 'Количество новостей', 1, 100),
        'title_length' => rss_import_post_integer('title_length', 'Длина заголовка', 1, 1000),
        'short_length' => rss_import_post_integer('short_length', 'Длина описания', 1, 10000),
        'include_content' => isset($_POST['include_content']) && !is_array($_POST['include_content']) && (string)$_POST['include_content'] === '1' ? 1 : 0,
        'show_image' => isset($_POST['show_image']) && !is_array($_POST['show_image']) && (string)$_POST['show_image'] === '1' ? 1 : 0,
        'image_source' => $imageSource,
    );
}

function rss_import_save()
{
    global $mysql;

    rss_import_require_post();
    $id = rss_import_post_integer('id', 'ID профиля', 0, 2147483647);
    if ($id === 0 && rss_import_storage_count() >= RSS_IMPORT_MAX_PROFILES) {
        throw new RuntimeException('Достигнут лимит RSS-профилей.');
    }
    $profile = rss_import_profile_from_post();
    $duplicate = rss_import_storage_get_by_profile_id($profile['profile_id']);
    if (is_array($duplicate) && (int)$duplicate['id'] !== $id) {
        throw new RuntimeException('Блок с таким ID уже существует.');
    }

    $columns = array();
    $values = array();
    foreach ($profile as $key => $value) {
        $columns[] = $key . '=' . (is_string($value) ? db_squote($value) : (int)$value);
        $values[] = is_string($value) ? db_squote($value) : (int)$value;
    }
    if ($id > 0) {
        if (!is_array(rss_import_storage_get_by_id($id))) {
            throw new RuntimeException('RSS Import profile not found: #' . $id);
        }
        rss_import_storage_execute('UPDATE ' . rss_import_storage_table() . ' SET ' . implode(', ', $columns)
            . ' WHERE id=' . $id . ' LIMIT 1', 'Unable to update RSS Import profile.');
    } else {
        $order = $mysql->record('SELECT COALESCE(MAX(sort_order), -1) AS max_order FROM ' . rss_import_storage_table());
        $sortOrder = (int)($order['max_order'] ?? -1) + 1;
        rss_import_storage_execute('INSERT INTO ' . rss_import_storage_table() . ' (' . implode(', ', array_keys($profile))
            . ', sort_order) VALUES (' . implode(', ', $values) . ', ' . $sortOrder . ')',
            'Unable to create RSS Import profile.');
    }
    print_commit_complete('rss_import');
}

function rss_import_delete()
{
    rss_import_require_post();
    $id = rss_import_post_integer('id', 'ID профиля', 1, 2147483647);
    if (!is_array(rss_import_storage_get_by_id($id))) {
        throw new RuntimeException('RSS Import profile not found: #' . $id);
    }
    rss_import_storage_execute('DELETE FROM ' . rss_import_storage_table() . ' WHERE id=' . $id . ' LIMIT 1',
        'Unable to delete RSS Import profile.');
    print_commit_complete('rss_import');
}

function rss_import_toggle()
{
    rss_import_require_post();
    $id = rss_import_post_integer('id', 'ID профиля', 1, 2147483647);
    $profile = rss_import_storage_get_by_id($id);
    if (!is_array($profile)) {
        throw new RuntimeException('RSS Import profile not found: #' . $id);
    }
    $enabled = (int)$profile['enabled'] === 1 ? 0 : 1;
    rss_import_storage_execute('UPDATE ' . rss_import_storage_table() . ' SET enabled=' . $enabled . ' WHERE id=' . $id . ' LIMIT 1',
        'Unable to update RSS Import profile state.');
    print_commit_complete('rss_import');
}

function rss_import_move($direction)
{
    rss_import_require_post();
    $id = rss_import_post_integer('id', 'ID профиля', 1, 2147483647);
    $ids = array();
    foreach (rss_import_storage_get_all() as $row) {
        $ids[] = (int)$row['id'];
    }
    $index = array_search($id, $ids, true);
    if ($index === false) {
        throw new RuntimeException('RSS Import profile not found: #' . $id);
    }
    $target = $direction === 'up' ? $index - 1 : $index + 1;
    if (isset($ids[$target])) {
        $ids[$index] = $ids[$target];
        $ids[$target] = $id;
        foreach ($ids as $sortOrder => $profileId) {
            rss_import_storage_execute('UPDATE ' . rss_import_storage_table() . ' SET sort_order=' . $sortOrder
                . ' WHERE id=' . $profileId . ' LIMIT 1', 'Unable to reorder RSS Import profiles.');
        }
    }
    print_commit_complete('rss_import');
}

function rss_import_settings()
{
    rss_import_render('settings', 'Настройки кеширования', 'conf.settings', array(
        'cache' => pluginGetVariable('rss_import', 'cache') === null ? 1 : (int)pluginGetVariable('rss_import', 'cache'),
        'cache_expire' => (int)pluginGetVariable('rss_import', 'cacheExpire') ?: 60,
    ));
}

function rss_import_save_settings()
{
    rss_import_require_post();
    $cache = filter_var($_POST['cache'] ?? null, FILTER_VALIDATE_INT);
    if (!in_array($cache, array(0, 1), true)) {
        throw new RuntimeException('Некорректное значение настройки кеширования.');
    }
    $cacheExpire = rss_import_post_integer('cache_expire', 'Срок кеширования', 1, 86400);
    pluginSetVariable('rss_import', 'cache', (string)$cache);
    pluginSetVariable('rss_import', 'cacheExpire', (string)$cacheExpire);
    if (!pluginsSaveConfig()) {
        throw new RuntimeException('Не удалось сохранить настройки кеширования RSS Import.');
    }
    print_commit_complete('rss_import');
}
