<?php
if (!defined('NGCMS')) exit('HAL');
pluginsLoadConfig();
LoadPluginLang('category_access', 'config', '', '', ':');

function category_access_tpl_path($templates)
{
	static $cache = array();
	$templateList = (array)$templates;
	$missing = array();
	foreach ($templateList as $tplName) {
		if (!isset($cache[$tplName])) {
			$missing[] = $tplName;
		}
	}
	if ($missing) {
		$paths = locatePluginTemplates($missing, 'category_access', 1);
		foreach ($missing as $tplName) {
			$cache[$tplName] = $paths[$tplName];
		}
	}
	if (is_array($templates)) {
		$result = array();
		foreach ($templates as $tplName) {
			$result[$tplName] = $cache[$tplName];
		}
		return $result;
	}
	return $cache[$templates];
}

function category_access_render_tpl($template, $vars = array())
{
	global $twig, $lang;
	$paths = category_access_tpl_path(array($template));
	$xt = $twig->loadTemplate($paths[$template] . $template . '.tpl');
	$defaultVars = array(
		'lang' => $lang,
		'admin_url' => admin_url,
	);
	return $xt->render(array_merge($defaultVars, $vars));
}

function category_access_render_page($action, $innerTemplate, $vars = array())
{
	$content = category_access_render_tpl($innerTemplate, $vars);
	return category_access_render_tpl('conf.main', array(
		'action' => $action,
		'content' => $content,
	));
}
switch ($_REQUEST['action']) {
	case 'list_user':
		show_list_user();
		break;
	case 'list_category':
		show_list_category();
		break;
	case 'add_user':
		add_user();
		break;
	case 'add_category':
		add_category();
		break;
	case 'move_up':
		// Функционал перемещения отсутствует в текущей версии — безопасная заглушка
		msg(array('type' => 'info', 'info' => $lang['category_access:info_not_implemented'] ?? 'Not implemented'));
		main();
		break;
	case 'move_down':
		// Функционал перемещения отсутствует в текущей версии — безопасная заглушка
		msg(array('type' => 'info', 'info' => $lang['category_access:info_not_implemented'] ?? 'Not implemented'));
		main();
		break;
	case 'dell_user':
		delete_user();
		break;
	case 'dell_category':
		delete_category();
		break;
	case 'general_submit':
		general_submit();
		main();
		break;
	case 'clear_cash':
		// Очистка кеша отсутствует — безопасная заглушка
		msg(array('type' => 'info', 'info' => $lang['category_access:info_not_implemented'] ?? 'Not implemented'));
		main();
	default:
		main();
}
function validate($string)
{

	$chars = 'abcdefghijklmnopqrstuvwxyz_.0123456789';
	if ($string == '') return true;
	foreach (str_split($string) as $char)
		if (stripos($chars, $char) === false)
			return false;

	return true;
}

function general_submit()
{

	global $lang;
	$if_error = false;
	$guest = isset($_POST['guest']) ? intval($_POST['guest']) : 0;
	$coment = isset($_POST['coment']) ? intval($_POST['coment']) : 0;
	$journ = isset($_POST['journ']) ? intval($_POST['journ']) : 0;
	$moder = isset($_POST['moder']) ? intval($_POST['moder']) : 0;
	$admin = isset($_POST['admin']) ? intval($_POST['admin']) : 0;
	$message = isset($_POST['message']) ? $_POST['message'] : '';
	if (!$if_error) {
		pluginSetVariable('category_access', 'guest', $guest);
		pluginSetVariable('category_access', 'coment', $coment);
		pluginSetVariable('category_access', 'journ', $journ);
		pluginSetVariable('category_access', 'moder', $moder);
		pluginSetVariable('category_access', 'admin', $admin);
		pluginSetVariable('category_access', 'message', $message);
		pluginsSaveConfig();
		msg(array('type' => 'info', 'info' => $lang['category_access:info_save_general']));
	}
}

function main()
{
	global $lang;
	$guest = pluginGetVariable('category_access', 'guest');
	$coment = pluginGetVariable('category_access', 'coment');
	$journ = pluginGetVariable('category_access', 'journ');
	$moder = pluginGetVariable('category_access', 'moder');
	$admin = pluginGetVariable('category_access', 'admin');
	$message = pluginGetVariable('category_access', 'message');
	$ttvars = array(
		'guest_list' => MakeDropDown(array(0 => $lang['category_access:label_close'], 1 => $lang['category_access:label_protect'], 2 => $lang['category_access:label_open']), 'guest', $guest),
		'coment_list' => MakeDropDown(array(0 => $lang['category_access:label_close'], 1 => $lang['category_access:label_protect'], 2 => $lang['category_access:label_open']), 'coment', $coment),
		'journ_list' => MakeDropDown(array(0 => $lang['category_access:label_close'], 1 => $lang['category_access:label_protect'], 2 => $lang['category_access:label_open']), 'journ', $journ),
		'moder_list' => MakeDropDown(array(0 => $lang['category_access:label_close'], 1 => $lang['category_access:label_protect'], 2 => $lang['category_access:label_open']), 'moder', $moder),
		'admin_list' => MakeDropDown(array(0 => $lang['category_access:label_close'], 1 => $lang['category_access:label_protect'], 2 => $lang['category_access:label_open']), 'admin', $admin),
		'message' => $message,
	);
	echo category_access_render_page($lang['category_access:button_general'], 'conf.general.form', $ttvars);
}

function show_list_user()
{
	global $lang, $catz, $catmap;
	$users = pluginGetVariable('category_access', 'users');
	if (!is_array($users)) {
		$users = array();
	}
	$output = '';
	foreach ($users as $user => $category) {
		$output .= category_access_render_tpl('conf.list.user.row', array(
			'user' => $user,
			'category' => $catz[$catmap[$category]]['name'],
		));
	}
	$inner = array(
		'entries' => $output,
		'table_title' => $lang['category_access:button_list_user'],
	);
	echo category_access_render_page($lang['category_access:button_list_user'], 'conf.list.user', $inner);
}

function show_list_category()
{
	global $lang, $catz, $catmap;
	$categorys = pluginGetVariable('category_access', 'categorys');
	if (!is_array($categorys)) {
		$categorys = array();
	}
	$output = '';
	foreach ($categorys as $cat) {
		$output .= category_access_render_tpl('conf.list.row', array(
			'category' => $cat,
			'category_name' => $catz[$catmap[$cat]]['name'],
		));
	}
	$inner = array(
		'entries' => $output,
		'table_title' => $lang['category_access:button_list_category'],
	);
	echo category_access_render_page($lang['category_access:button_list_category'], 'conf.list', $inner);
}

function add_user()
{

	global $lang, $catz, $catmap, $mysql;
	$users = pluginGetVariable('category_access', 'users');
	if (!is_array($users)) {
		$users = array();
	}
	$if_add = true;
	$user = '';
	$category = 0;
	if (isset($_GET['user'])) {
		if (!array_key_exists($_GET['user'], $users)) {
			msg(array('type' => 'error', 'info' => $lang['category_access:error_not_exists'], 'text' => $lang['category_access:error_val_title']));
			show_list_user();

			return;
		}
		$user = $_GET['user'];
		$category = $users[$user];
		$if_add = false;
	}
	if (isset($_POST['user']) && isset($_POST['category'])) {
		$user = $_POST['user'];
		$category = $_POST['category'];
		$if_error = false;
		if (!$user || !validate($user)) {
			msg(array('type' => 'error', 'info' => sprintf($lang['category_access:error_validate'], $lang['category_access:label_user_name']), 'text' => $lang['category_access:error_val_title']));
			$if_error = true;
		}
		if (!array_key_exists($category, $catmap)) {
			msg(array('type' => 'error', 'info' => $lang['category_access:error_category'], 'text' => $lang['category_access:error_val_title']));
			$if_error = true;
		}
		if ($if_add && array_key_exists($user, $users)) {
			msg(array('type' => 'error', 'info' => sprintf($lang['category_access:error_exists'], $user), 'text' => $lang['category_access:error_val_title']));
			$if_error = true;
		}
		if (!$if_error) {
			$users[$user] = $category;
			pluginSetVariable('category_access', 'users', $users);
			pluginsSaveConfig();
			msg(array('type' => 'info', 'info' => $lang['category_access:info_save_general']));
			show_list_user();

			return;
		}
	}
	$category_list = array();
	foreach ($catmap as $key => $val) {
		$category_list[$key] = $catz[$val]['name'];
	}
	$user_list = array();
	foreach ($mysql->select('select ' . prefix . '_users.name  from ' . prefix . '_users order by ' . prefix . '_users.name asc') as $row) {
		if (array_key_exists($row['name'], $users) && ($if_add || $row['name'] != $user)) {
			continue;
		}
		$user_list[$row['name']] = $row['name'];
	}
	$formTitle = $if_add ? $lang['category_access:button_add_user'] : $lang['category_access:button_edit_user'];
	$ttvars = array(
		'user' => $user,
		'user_list' => MakeDropDown($user_list, 'user', $user),
		'category_list' => MakeDropDown($category_list, 'category', $category),
		'is_add' => $if_add,
		'submit_label' => $if_add ? $lang['category_access:button_add_user'] : $lang['category_access:button_edit_user'],
		'form_title' => $formTitle,
	);
	echo category_access_render_page($formTitle, 'conf.add_edit_user.form', $ttvars);
}

function add_category()
{

	global $lang, $catz, $catmap;
	$categorys = pluginGetVariable('category_access', 'categorys');
	if (!is_array($categorys)) {
		$categorys = array();
	}
	if (isset($_POST['category']) && is_array($_POST['category'])) {
		foreach ($_POST['category'] as $category) {
			if (!array_key_exists($category, $catmap)) {
				msg(array('type' => 'error', 'info' => sprintf($lang['category_access:error_category_not_add'], $category), 'text' => $lang['category_access:error_val_title']));
				continue;
			}
			if (in_array($category, $categorys)) {
				msg(array('type' => 'error', 'info' => sprintf($lang['category_access:error_category_not_add'], $catz[$catmap[$category]]['name']), 'text' => $lang['category_access:error_val_title']));
				continue;
			}
			$categorys[] = $category;
		}
		pluginSetVariable('category_access', 'categorys', $categorys);
		pluginsSaveConfig();
		msg(array('type' => 'info', 'info' => $lang['category_access:info_save_general']));
		show_list_category();

		return;
	}
	$entries = '';
	foreach ($catmap as $key => $val) {
		if (in_array($key, $categorys)) {
			continue;
		}
		$entries .= category_access_render_tpl('conf.add_edit.category.row', array(
			'category' => $key,
			'category_name' => $catz[$val]['name'],
		));
	}
	$inner = array(
		'entries' => $entries,
		'table_title' => $lang['category_access:button_add_category'],
	);
	echo category_access_render_page($lang['category_access:button_add_category'], 'conf.add_edit.category', $inner);
}

function delete_user()
{

	global $lang;
	$users = pluginGetVariable('category_access', 'users');
	if (!is_array($users)) {
		$users = array();
	}
	if (!isset($_REQUEST['user']) || !array_key_exists($_REQUEST['user'], $users)) {
		msg(array('type' => 'error', 'info' => $lang['category_access:error_not_exists_user'], 'text' => $lang['category_access:error_val_title']));
		show_list_user();

		return;
	}
	$user = $_REQUEST['user'];
	if (isset($_POST['commit'])) {
		if ($_POST['commit'] == 'yes') {
			unset($users[$user]);
			pluginSetVariable('category_access', 'users', $users);
			pluginsSaveConfig();
			msg(array('type' => 'info', 'info' => $lang['category_access:info_save_general']));
		}
		show_list_user();

		return;
	}
	$ttvars = array(
		'user' => $user,
		'commit' => sprintf($lang['category_access:desc_commit_user'], $user),
	);
	echo category_access_render_page($lang['category_access:title_commit'], 'conf.commit.user', $ttvars);
}

function delete_category()
{

	global $lang, $catz, $catmap;
	$categorys = pluginGetVariable('category_access', 'categorys');
	if (!is_array($categorys)) {
		$categorys = array();
	}
	if (!isset($_REQUEST['category']) || !in_array($_REQUEST['category'], $categorys)) {
		msg(array('type' => 'error', 'info' => $lang['category_access:error_not_exists'], 'text' => $lang['category_access:error_val_title']));
		show_list_category();

		return;
	}
	$category = $_REQUEST['category'];
	if (isset($_POST['commit'])) {
		if ($_POST['commit'] == 'yes') {
			unset($categorys[array_search($category, $categorys)]);
			pluginSetVariable('category_access', 'categorys', $categorys);
			pluginsSaveConfig();
			msg(array('type' => 'info', 'info' => $lang['category_access:info_save_general']));
		}
		show_list_category();

		return;
	}
	$ttvars = array(
		'category' => $category,
		'commit' => sprintf($lang['category_access:desc_commit_category'], $catz[$catmap[$category]]['name']),
	);
	echo category_access_render_page($lang['category_access:title_commit'], 'conf.commit.form', $ttvars);
}
