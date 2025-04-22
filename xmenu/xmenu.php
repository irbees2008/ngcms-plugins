<?php
if (!defined('NGCMS')) die('HAL');

// Включаем подробное логирование ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Режим работы (авто/TWIG)
$plugin_mode = extra_get_param('xmenu', 'mode');
if (!$plugin_mode) {
	add_act('index', 'plugin_xmenu'); // Автоматический режим
} else {
	global $template;
	$template['vars']['plugin_xmenu'] = ''; // TWIG режим
}

function plugin_xmenu()
{
	global $template;
	$output = plug_xmenu_show(array('debug' => true));
	$template['vars']['plugin_xmenu'] = $output;
	error_log("XMenu Output: " . $output); // Логируем результат
}

function plug_xmenu_show($params)
{
	global $mysql, $tpl, $twig, $currentCategory;

	$defaults = array(
		'menu_id' => 1,
		'template' => 'xmenu',
		'show_news' => 1,
		'debug' => false
	);
	$params = array_merge($defaults, $params);

	error_log("XMenu: Starting with params: " . print_r($params, true));

	// Получаем ВСЕ категории с xmenu-разметкой
	$query = "SELECT id, name, alt, xmenu FROM " . prefix . "_category ORDER BY posorder";
	error_log("XMenu SQL: " . $query);

	$catz = $mysql->select($query);
	error_log("XMenu Found categories: " . count($catz));
	if ($params['debug']) {
		error_log("XMenu: Query result: " . print_r($catz, true));
	}
	$menuItems = array();
	foreach ($catz as $cat) {
		error_log("Processing category ID: " . $cat['id'] . " with xmenu: " . $cat['xmenu']);

		// Проверяем, включена ли категория в текущее меню
		if (!isset($cat['xmenu']) || strlen($cat['xmenu']) < 9 || $cat['xmenu'][$params['menu_id'] - 1] != '#') {
			error_log("Skipping category ID: " . $cat['id'] . " - not enabled for this menu");
			continue;
		}

		$link = generateCategoryLink($cat, $params['debug']);
		error_log("Generated link for cat " . $cat['id'] . ": " . $link);

		$menuItems[] = array(
			'id' => $cat['id'],
			'name' => $cat['name'],
			'url' => $link,
			'active' => isset($currentCategory) && ($currentCategory == $cat['id']),
			'news_count' => $params['show_news'] ? getCategoryNewsCount($cat['id']) : 0
		);
	}

	error_log("Final menu items: " . print_r($menuItems, true));

	// Рендеринг шаблона
	$tpath = locatePluginTemplates(array($params['template']), 'xmenu', extra_get_param('xmenu', 'localsource'));
	error_log("Template path: " . print_r($tpath, true));

	if (!file_exists($tpath[$params['template']] . $params['template'] . '.tpl')) {
		error_log("Template file not found: " . $tpath[$params['template']] . $params['template'] . '.tpl');
		return "<!-- XMenu Error: Template not found -->";
	}

	$tVars = array(
		'items' => $menuItems,
		'menu_id' => $params['menu_id'],
		'show_news' => $params['show_news'],
		'tpl_url' => tpl_url
	);

	try {
		$xt = $twig->loadTemplate($tpath[$params['template']] . $params['template'] . '.tpl');
		$output = $xt->render($tVars);
		error_log("Rendered output: " . substr($output, 0, 200) . "...");
		return $output;
	} catch (Exception $e) {
		error_log("XMenu Twig Error: " . $e->getMessage());
		return "<!-- XMenu Twig Error -->";
	}
}
foreach ($catz as $cat) {
	if (!isset($cat['xmenu']) || $cat['xmenu'][$params['menu_id'] - 1] != '#') {
		if ($params['debug']) error_log("XMenu: Skipping category {$cat['id']} due to xmenu filter");
		continue;
	}

	if ($params['debug']) {
		error_log("XMenu: Processing category data: " . print_r($cat, true));
	}

	// Проверка наличия обязательных полей
	if (!isset($cat['id']) || !isset($cat['name']) || !isset($cat['alt'])) {
		if ($params['debug']) error_log("XMenu: Invalid category data for cat {$cat['id']}");
		continue;
	}

	$menuItems[] = array(
		'id' => $cat['id'],
		'name' => $cat['name'],
		'url' => generateCategoryLink($cat, $params['debug']),
		'active' => isset($currentCategory) && ($currentCategory == $cat['id']),
		'news_count' => $params['show_news'] ? getCategoryNewsCount($cat['id']) : 0
	);
}
function generateCategoryLink($cat, $debug = false)
{
	// Проверка входных данных
	if (!is_array($cat)) {
		if ($debug) {
			error_log("XMenu: Invalid category data format");
		}
		return '#';
	}

	if (!isset($cat['id']) || !isset($cat['alt'])) {
		if ($debug) {
			error_log("XMenu: Missing required category fields (id or alt)");
		}
		return '#';
	}

	// Используем alt или ID категории, если alt пуст
	$alt = !empty($cat['alt']) ? $cat['alt'] : 'category-' . $cat['id'];

	// Очищаем от недопустимых символов
	$alt = preg_replace('/[^a-z0-9\-]/', '', strtolower($alt));

	// Формируем URL
	$link = '/' . $alt . '.html';

	if ($debug) {
		error_log("XMenu: Generated link for cat {$cat['id']}: {$link}");
	}

	return $link;
}
function getCategoryNewsCount($cat_id)
{
	global $mysql;
	$query = "SELECT COUNT(*) as cnt FROM " . prefix . "_news WHERE approve=1 AND catid=" . db_squote($cat_id);
	error_log("News count query: " . $query);

	$row = $mysql->record($query);
	$count = $row ? $row['cnt'] : 0;
	error_log("News count for cat $cat_id: $count");

	return $count;
}

// Регистрация TWIG функции
function plugin_xmenu_showTwig($params)
{
	return plug_xmenu_show($params);
}
twigRegisterFunction('xmenu', 'show', 'plugin_xmenu_showTwig');
