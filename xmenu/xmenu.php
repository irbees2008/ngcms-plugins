<?php
if (!defined('NGCMS')) die('HAL');
global $template;
$template['vars']['plugin_xmenu'] = '';
twigRegisterFunction('xmenu', 'show', 'plugin_xmenu_showTwig');
function plugin_xmenu_showTwig($params = [])
{
	return plug_xmenu_show($params);
}
function plug_xmenu_show($params = [])
{
	global $mysql, $twig, $currentCategory, $currentStatic;
	$defaults = [
		'menu_id' => 1,
		'template' => 'xmenu',
		'show_news' => 1,
		'show_static' => 1,
		'debug' => false,
		'current_as_text' => true
	];
	$params = array_merge($defaults, $params);
	$max_menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
	if ($params['menu_id'] < 1 || $params['menu_id'] > $max_menu_count) {
		return "<!-- XMenu Error: Invalid menu_id -->";
	}
	// Получаем текущий URL для точного сравнения
	$current_url = $_SERVER['REQUEST_URI'];
	$current_url = preg_replace('/\?.*/', '', $current_url); // Удаляем параметры запроса
	// Получаем категории
	$catz = $mysql->select("SELECT id, name, alt, xmenu FROM " . prefix . "_category ORDER BY posorder");
	$static_pages = $mysql->select("SELECT id, title, alt_name, xmenu FROM " . prefix . "_static ORDER BY id");
	$menuItems = [];
	// Обрабатываем категории
	foreach ($catz as $cat) {
		$xmenu = isset($cat['xmenu']) ? $cat['xmenu'] : str_repeat('_', $max_menu_count);
		$xmenu = str_pad(substr($xmenu, 0, $max_menu_count), $max_menu_count, '_');
		if (!isset($xmenu[$params['menu_id'] - 1]) || $xmenu[$params['menu_id'] - 1] != '#') {
			continue;
		}
		$cat_url = generateCategoryLink($cat);
		$is_current = ($current_url == $cat_url) || (isset($currentCategory) && ($currentCategory == $cat['id']));
		$menuItems[] = [
			'type' => 'category',
			'id' => $cat['id'],
			'name' => $cat['name'],
			'url' => $is_current && $params['current_as_text'] ? '' : $cat_url,
			'is_current' => $is_current,
			'active' => $is_current,
			'news_count' => $params['show_news'] ? getCategoryNewsCount($cat['id']) : 0
		];
	}
	// Обрабатываем статические страницы
	if ($params['show_static']) {
		foreach ($static_pages as $page) {
			$xmenu = isset($page['xmenu']) ? $page['xmenu'] : str_repeat('_', $max_menu_count);
			$xmenu = str_pad(substr($xmenu, 0, $max_menu_count), $max_menu_count, '_');
			if (!isset($xmenu[$params['menu_id'] - 1]) || $xmenu[$params['menu_id'] - 1] != '#') {
				continue;
			}
			$page_url = generateStaticLink($page);
			$is_current = ($current_url == parse_url($page_url, PHP_URL_PATH)) || (isset($currentStatic) && ($currentStatic == $page['id']));
			$menuItems[] = [
				'type' => 'static',
				'id' => $page['id'],
				'name' => $page['title'],
				'url' => $is_current && $params['current_as_text'] ? '' : $page_url,
				'active' => $is_current,
				'is_current' => $is_current,
				'alt_name' => $page['alt_name'] ?? ''
			];
		}
	}
	$tpath = locatePluginTemplates([$params['template']], 'xmenu', extra_get_param('xmenu', 'localsource'));
	if (!file_exists($tpath[$params['template']] . $params['template'] . '.tpl')) {
		return "<!-- XMenu Error: Template not found -->";
	}
	$tVars = [
		'items' => $menuItems,
		'menu_id' => $params['menu_id'],
		'show_news' => $params['show_news'],
		'show_static' => $params['show_static'],
		'tpl_url' => tpl_url,
		'current_url' => $current_url // Для отладки
	];
	try {
		$xt = $twig->loadTemplate($tpath[$params['template']] . $params['template'] . '.tpl');
		return $xt->render($tVars);
	} catch (Exception $e) {
		return "<!-- XMenu Twig Error -->";
	}
}
function generateCategoryLink($cat)
{
	$alt = !empty($cat['alt']) ? $cat['alt'] : 'category-' . $cat['id'];
	$alt = preg_replace('/[^a-z0-9\-]/', '', strtolower($alt));
	return '/' . $alt . '.html';
}
function generateStaticLink($page)
{
	global $config;
	// Генерируем ссылку так же, как в listStatic()
	if (checkLinkAvailable('static', '')) {
		return generateLink('static', '', ['altname' => $page['alt_name'], 'id' => $page['id']], [], false, true);
	} else {
		return generateLink('core', 'plugin', ['plugin' => 'static'], ['altname' => $page['alt_name'], 'id' => $page['id']], false, true);
	}
}
function getCategoryNewsCount($cat_id)
{
	global $mysql;
	$row = $mysql->record("SELECT COUNT(*) as cnt FROM " . prefix . "_news WHERE approve=1 AND catid=" . db_squote($cat_id));
	return $row ? $row['cnt'] : 0;
}
