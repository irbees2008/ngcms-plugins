<?php
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('xnews', 'config', '', 'tn', ':');
include_once __DIR__ . '/storage.php';

$action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : 'list';
if ($action === 'update') {
    xnews_require_post();
    if (!xnews_storage_should_offer_update()) {
        msgSticker('Обновление Xnews не требуется.', 'info');
    } else {
        try {
            xnews_storage_update();
            print_commit_complete('xnews');
        } catch (Throwable $e) {
            msgSticker('Не удалось обновить Xnews: ' . $e->getMessage(), 'error');
        }
    }
}

if (!mysql_table_exists(xnews_storage_table())) {
    xnews_render('settings', 'Обновление плагина', 'conf.update', array(
        'show_update_button' => xnews_storage_should_offer_update(),
    ));
    return;
}

switch ($action) {
    case 'add':
        xnews_edit();
        break;
    case 'edit':
        xnews_edit((int)($_GET['id'] ?? 0));
        break;
    case 'save':
        xnews_save();
        break;
    case 'delete':
        xnews_delete();
        break;
    case 'toggle':
        xnews_toggle();
        break;
    case 'move_up':
        xnews_move('up');
        break;
    case 'move_down':
        xnews_move('down');
        break;
    case 'settings':
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            xnews_save_settings();
        } else {
            xnews_settings();
        }
        break;
    case 'list':
    default:
        xnews_list();
        break;
}

function xnews_require_post()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || !isset($_POST['token'])
        || !is_string($_POST['token'])
        || !hash_equals((string)genUToken('admin.extra-config'), $_POST['token'])) {
        throw new RuntimeException('Invalid Xnews configuration request token.');
    }
}

function xnews_documentation_vars()
{
    if (!function_exists('plugin_documentation_markup')) {
        return array('documentation_button' => '', 'documentation_modal' => '');
    }

    list($button, $modal) = plugin_documentation_markup('xnews');
    return array('documentation_button' => $button, 'documentation_modal' => $modal);
}

function xnews_render($activeTab, $actionLabel, $bodyTemplate, $bodyVars)
{
    global $tpl, $lang;

    $tpath = locatePluginTemplates(array('conf.main', $bodyTemplate), 'xnews', 1);
    $token = htmlspecialchars((string)genUToken('admin.extra-config'), ENT_QUOTES, 'UTF-8');
    $storageExists = mysql_table_exists(xnews_storage_table());
    $profileCount = $storageExists ? xnews_storage_count() : 0;
    $canAddProfile = $storageExists && $profileCount < XNEWS_MAX_PROFILES;
    $showUpdateButton = $profileCount === 0 && xnews_storage_has_legacy_profiles();
    $bodyVars['token'] = $token;
    $bodyVars['profile_count'] = $profileCount;
    $bodyVars['profile_limit'] = XNEWS_MAX_PROFILES;
    $bodyVars['can_add_profile'] = $canAddProfile;
    $bodyVars['show_update_button'] = $showUpdateButton;
    $tpl->template($bodyTemplate, $tpath[$bodyTemplate]);
    $tpl->vars($bodyTemplate, array('vars' => $bodyVars));

    $tabs = array(
        'list' => '',
        'add' => '',
        'settings' => '',
    );
    $tabs[$activeTab] = 'active';
    $mainVars = array(
        'entries' => $tpl->show($bodyTemplate),
        'action' => $actionLabel,
        'profile_count' => $profileCount,
        'profile_limit' => XNEWS_MAX_PROFILES,
        'can_add_profile' => $canAddProfile,
        'storage_exists' => $storageExists,
        'show_update_button' => $showUpdateButton,
        'update_token' => $token,
        'tab_list_active' => $tabs['list'],
        'tab_add_active' => $tabs['add'],
        'tab_settings_active' => $tabs['settings'],
        'lang' => $lang,
    );
    $mainVars = array_merge($mainVars, xnews_documentation_vars());
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', array('vars' => $mainVars));
    print $tpl->show('conf.main');
}

function xnews_list()
{
    global $lang;

    $orderNames = array(
        'viewed' => $lang['tn:orderby_views'],
        'commented' => $lang['tn:orderby_comments'],
        'random' => $lang['tn:orderby_random'],
        'last' => $lang['tn:orderby_last'],
        'first' => 'Сначала старые',
        'editdate' => 'По дате редактирования',
        'rating' => 'По рейтингу',
    );
    $items = array();
    foreach (xnews_storage_get_all() as $row) {
        $order = (string)$row['order_by'];
        $items[] = array(
            'id' => (int)$row['id'],
            'name' => (string)$row['block_name'],
            'template' => (string)$row['template'],
            'count' => (int)$row['news_count'],
            'order' => isset($orderNames[$order]) ? $orderNames[$order] : $order,
            'enabled' => (int)$row['enabled'] === 1,
        );
    }

    xnews_render('list', 'Список блоков', 'conf.list', array(
        'items' => $items,
        'lang' => $lang,
    ));
}

function xnews_default_profile()
{
    return array(
        'id' => 0,
        'block_name' => '',
        'enabled' => 1,
        'template' => 'example',
        'visibility_mode' => 0,
        'visibility_categories' => '',
        'category_mode' => 0,
        'categories' => '',
        'main_mode' => 0,
        'pin_mode' => 0,
        'fav_mode' => 0,
        'skip_current' => 0,
        'extract_embedded_items' => 0,
        'show_no_news' => 0,
        'news_count' => 10,
        'news_skip' => 0,
        'max_age' => 0,
        'order_by' => 'viewed',
    );
}

function xnews_edit($id = 0)
{
    global $lang;

    if (!$id && xnews_storage_count() >= XNEWS_MAX_PROFILES) {
        xnews_render('list', 'Достигнут лимит профилей', 'conf.limit', array(
            'profile_count' => xnews_storage_count(),
            'profile_limit' => XNEWS_MAX_PROFILES,
            'lang' => $lang,
        ));
        return;
    }

    $profile = xnews_default_profile();
    if ($id) {
        $row = xnews_storage_get_by_id($id);
        if (!is_array($row)) {
            throw new RuntimeException('Xnews profile not found: #' . $id);
        }
        $profile = array_merge($profile, $row);
    }

    xnews_render($id ? 'list' : 'add', $id ? 'Редактирование блока' : 'Добавление блока', 'conf.form', array(
        'profile' => $profile,
        'mode' => $id ? 'edit' : 'add',
        'lang' => $lang,
    ));
}

function xnews_validate_category_list($value, $fieldLabel)
{
    if (!is_string($value)) {
        throw new RuntimeException('Некорректное значение поля «' . $fieldLabel . '».');
    }
    $value = trim($value);
    if ($value !== '' && !preg_match('/^\d+(?:\s*,\s*\d+)*$/', $value)) {
        throw new RuntimeException('В поле «' . $fieldLabel . '» укажите ID категорий через запятую.');
    }
    return $value;
}

function xnews_post_integer($key, $label, $min, $max)
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value < $min || $value > $max) {
        throw new RuntimeException('Проверьте значение поля «' . $label . '».');
    }
    return $value;
}

function xnews_profile_from_post()
{
    if (!isset($_POST['block_name']) || !is_string($_POST['block_name'])) {
        throw new RuntimeException('Идентификатор блока указан некорректно.');
    }
    $name = trim($_POST['block_name']);
    if ($name === '' || strlen($name) > 190) {
        throw new RuntimeException('Идентификатор блока обязателен и должен быть не длиннее 190 символов.');
    }

    if (!isset($_POST['template']) || !is_string($_POST['template'])) {
        throw new RuntimeException('Каталог шаблона указан некорректно.');
    }
    $template = trim($_POST['template']);
    if ($template === '') {
        $template = 'example';
    }
    if (strlen($template) > 190 || !preg_match('/^[A-Za-z0-9_-]+$/', $template)) {
        throw new RuntimeException('Укажите корректный идентификатор каталога шаблона.');
    }

    $allowed = array(
        'visibility_mode' => array(0, 1, 2, 3),
        'category_mode' => array(0, 1, 2),
        'main_mode' => array(0, 1, 2),
        'pin_mode' => array(0, 1, 2),
        'fav_mode' => array(0, 1, 2),
        'skip_current' => array(0, 1),
        'extract_embedded_items' => array(0, 1),
        'show_no_news' => array(0, 1),
    );
    $values = array();
    foreach ($allowed as $field => $choices) {
        $value = filter_var($_POST[$field] ?? null, FILTER_VALIDATE_INT);
        if ($value === false || !in_array($value, $choices, true)) {
            throw new RuntimeException('Некорректное значение параметра блока: ' . $field . '.');
        }
        $values[$field] = $value;
    }

    $orderBy = isset($_POST['order_by']) && is_string($_POST['order_by']) ? $_POST['order_by'] : '';
    if (!in_array($orderBy, array('viewed', 'commented', 'random', 'last', 'first', 'editdate', 'rating'), true)) {
        throw new RuntimeException('Некорректный порядок вывода новостей.');
    }

    return array(
        'block_name' => $name,
        'enabled' => isset($_POST['enabled']) && !is_array($_POST['enabled']) && (string)$_POST['enabled'] === '1' ? 1 : 0,
        'template' => $template,
        'visibility_mode' => $values['visibility_mode'],
        'visibility_categories' => xnews_validate_category_list($_POST['visibility_categories'] ?? '', 'Список категорий отображения'),
        'category_mode' => $values['category_mode'],
        'categories' => xnews_validate_category_list($_POST['categories'] ?? '', 'Список категорий новостей'),
        'main_mode' => $values['main_mode'],
        'pin_mode' => $values['pin_mode'],
        'fav_mode' => $values['fav_mode'],
        'skip_current' => $values['skip_current'],
        'extract_embedded_items' => $values['extract_embedded_items'],
        'show_no_news' => $values['show_no_news'],
        'news_count' => xnews_post_integer('news_count', 'Количество новостей', 1, 2147483647),
        'news_skip' => xnews_post_integer('news_skip', 'Пропустить новостей', 0, 2147483647),
        'max_age' => xnews_post_integer('max_age', 'Период публикации', 0, 2147483647),
        'order_by' => $orderBy,
    );
}

function xnews_save()
{
    global $mysql;

    xnews_require_post();
    $profileId = xnews_post_integer('id', 'ID блока', 0, 2147483647);
    if ($profileId === 0 && xnews_storage_count() >= XNEWS_MAX_PROFILES) {
        global $lang;
        xnews_render('list', 'Достигнут лимит профилей', 'conf.limit', array(
            'profile_count' => xnews_storage_count(),
            'profile_limit' => XNEWS_MAX_PROFILES,
            'lang' => $lang,
        ));
        return;
    }
    $profile = xnews_profile_from_post();
    $columns = array();
    $values = array();
    foreach ($profile as $key => $value) {
        $columns[] = $key . '=' . (is_string($value) ? db_squote($value) : (int)$value);
    }

    if ($profileId > 0) {
        if (!is_array(xnews_storage_get_by_id($profileId))) {
            throw new RuntimeException('Xnews profile not found: #' . $profileId);
        }
        xnews_storage_execute('UPDATE ' . xnews_storage_table() . ' SET ' . implode(', ', $columns)
            . ' WHERE id=' . $profileId . ' LIMIT 1', 'Unable to update Xnews profile.');
    } else {
        $order = $mysql->record('SELECT COALESCE(MAX(sort_order), -1) AS max_order FROM ' . xnews_storage_table());
        $sortOrder = (int)($order['max_order'] ?? -1) + 1;
        $columnNames = array_keys($profile);
        $sqlValues = array();
        foreach ($profile as $value) {
            $sqlValues[] = is_string($value) ? db_squote($value) : (int)$value;
        }
        xnews_storage_execute('INSERT INTO ' . xnews_storage_table() . ' (' . implode(', ', $columnNames)
            . ', sort_order) VALUES (' . implode(', ', $sqlValues) . ', ' . $sortOrder . ')',
            'Unable to create Xnews profile.');
    }

    print_commit_complete('xnews');
}

function xnews_delete()
{
    global $mysql;

    xnews_require_post();
    $id = xnews_post_integer('id', 'ID блока', 1, 2147483647);
    if (!is_array(xnews_storage_get_by_id($id))) {
        throw new RuntimeException('Xnews profile not found: #' . $id);
    }
    xnews_storage_execute('DELETE FROM ' . xnews_storage_table() . ' WHERE id=' . $id . ' LIMIT 1',
        'Unable to delete Xnews profile.');
    print_commit_complete('xnews');
}

function xnews_toggle()
{
    global $mysql;

    xnews_require_post();
    $id = xnews_post_integer('id', 'ID блока', 1, 2147483647);
    $profile = xnews_storage_get_by_id($id);
    if (!is_array($profile)) {
        throw new RuntimeException('Xnews profile not found: #' . $id);
    }
    $enabled = (int)$profile['enabled'] === 1 ? 0 : 1;
    xnews_storage_execute('UPDATE ' . xnews_storage_table() . ' SET enabled=' . $enabled . ' WHERE id=' . $id . ' LIMIT 1',
        'Unable to update Xnews profile state.');
    print_commit_complete('xnews');
}

function xnews_move($direction)
{
    global $mysql;

    xnews_require_post();
    $id = xnews_post_integer('id', 'ID блока', 1, 2147483647);
    $ids = array();
    foreach (xnews_storage_get_all() as $row) {
        $ids[] = (int)$row['id'];
    }
    $index = array_search($id, $ids, true);
    if ($index === false) {
        throw new RuntimeException('Xnews profile not found: #' . $id);
    }
    $target = $direction === 'up' ? $index - 1 : $index + 1;
    if (isset($ids[$target])) {
        $ids[$index] = $ids[$target];
        $ids[$target] = $id;
        foreach ($ids as $sortOrder => $profileId) {
            xnews_storage_execute('UPDATE ' . xnews_storage_table() . ' SET sort_order=' . $sortOrder
                . ' WHERE id=' . $profileId . ' LIMIT 1', 'Unable to reorder Xnews profiles.');
        }
    }
    print_commit_complete('xnews');
}

function xnews_settings()
{
    global $lang;

    xnews_render('settings', 'Настройки кеширования', 'conf.settings', array(
        'cache' => pluginGetVariable('xnews', 'cache') === null ? 1 : (int)pluginGetVariable('xnews', 'cache'),
        'cache_expire' => (int)pluginGetVariable('xnews', 'cacheExpire') ?: 60,
        'lang' => $lang,
    ));
}

function xnews_save_settings()
{
    xnews_require_post();
    $cache = filter_var($_POST['cache'] ?? null, FILTER_VALIDATE_INT);
    if (!in_array($cache, array(0, 1), true)) {
        throw new RuntimeException('Некорректное значение настройки кеширования.');
    }
    $cacheExpire = xnews_post_integer('cache_expire', 'Срок кеширования', 1, 86400);
    pluginSetVariable('xnews', 'cache', (string)$cache);
    pluginSetVariable('xnews', 'cacheExpire', (string)$cacheExpire);
    if (!pluginsSaveConfig()) {
        throw new RuntimeException('Не удалось сохранить настройки кеширования Xnews.');
    }
    print_commit_complete('xnews');
}
