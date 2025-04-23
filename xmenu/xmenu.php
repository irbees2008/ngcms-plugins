<?php
if (!defined('NGCMS')) die('HAL');

// Регистрация TWIG-функции
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

	// Параметры по умолчанию
	$defaults = [
		'menu_id' => 1,
		'template' => 'xmenu',
		'show_news' => 1,
		'show_static' => 1,
		'debug' => false
	];
	$params = array_merge($defaults, $params);

	// Получаем категории
	$catz = $mysql->select("SELECT id, name, alt, xmenu FROM " . prefix . "_category ORDER BY posorder");

	// Получаем статические страницы
	$static_pages = $mysql->select("SELECT id, title, alt_name, xmenu FROM " . prefix . "_static ORDER BY id");

	$menuItems = [];

	// Обрабатываем категории
	foreach ($catz as $cat) {
		if (!isset($cat['xmenu']) || strlen($cat['xmenu']) < 9 || $cat['xmenu'][$params['menu_id'] - 1] != '#') {
			continue;
		}

		$menuItems[] = [
			'type' => 'category',
			'id' => $cat['id'],
			'name' => $cat['name'],
			'url' => generateCategoryLink($cat),
			'active' => isset($currentCategory) && ($currentCategory == $cat['id']),
			'news_count' => $params['show_news'] ? getCategoryNewsCount($cat['id']) : 0
		];
	}

	// Обрабатываем статические страницы
	if ($params['show_static']) {
		foreach ($static_pages as $page) {
			if (!isset($page['xmenu']) || strlen($page['xmenu']) < 9 || $page['xmenu'][$params['menu_id'] - 1] != '#') {
				continue;
			}

			$menuItems[] = [
				'type' => 'static',
				'id' => $page['id'],
				'name' => $page['title'],
				'url' => generateStaticLink($page),
				'active' => isset($currentStatic) && ($currentStatic == $page['id']),
				'alt_name' => $page['alt_name'] ?? ''
			];
		}
	}

	// Рендеринг шаблона
	$tpath = locatePluginTemplates([$params['template']], 'xmenu', extra_get_param('xmenu', 'localsource'));

	if (!file_exists($tpath[$params['template']] . $params['template'] . '.tpl')) {
		return "<!-- XMenu Error: Template not found -->";
	}

	$tVars = [
		'items' => $menuItems,
		'menu_id' => $params['menu_id'],
		'show_news' => $params['show_news'],
		'show_static' => $params['show_static'],
		'tpl_url' => tpl_url
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
