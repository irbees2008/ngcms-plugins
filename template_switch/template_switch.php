<?php

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

add_act('core', 'plugin_template_switch');
add_act('index', 'plugin_template_switch_menu');
register_plugin_page('template_switch', '', 'template_switch_redirector', 0);
register_htmlvar('index', 'plugin_template_switch_menu');
// В начало файла после проверки NGCMS
if (isset($_GET['get_description'])) {
	$template_id = intval($_GET['get_description']);
	$description_link = pluginGetVariable('template_switch', 'profile' . $template_id . '_description_link');
	if ($description_link) {
		header("Location: " . $description_link);
		exit;
	}
}
// Если это запрос на предпросмотр и включена selfpage
if (isset($_GET['profile']) && pluginGetVariable('template_switch', 'selfpage')) {
	$templateID = $_GET['profile'];

	// Ищем профиль по ID
	$sw_count = intval(pluginGetVariable('template_switch', 'count'));
	for ($i = 1; $i <= $sw_count; $i++) {
		if (pluginGetVariable('template_switch', 'profile' . $i . '_id') == $templateID) {
			@setcookie('sw_template', $i, time() + 365 * 24 * 60 * 60, '/');
			break;
		}
	}
}
function plugin_template_switch()
{
	global $config;
	// Если это запрос на предпросмотр и включена selfpage
	if (isset($_GET['profile']) && pluginGetVariable('template_switch', 'selfpage')) {
		$templateID = $_GET['profile'];

		// Ищем профиль по ID
		$sw_count = intval(pluginGetVariable('template_switch', 'count'));
		for ($i = 1; $i <= $sw_count; $i++) {
			$profile_id = pluginGetVariable('template_switch', 'profile' . $i . '_id');
			if (empty($profile_id)) {
				// Если ID не задан, генерируем его из названия шаблона
				$template_name = pluginGetVariable('template_switch', 'profile' . $i . '_template');
				$profile_id = strtolower(preg_replace('/[^a-z0-9]/', '', $template_name));
			}

			if ($profile_id == $templateID) {
				@setcookie('sw_template', $i, time() + 365 * 24 * 60 * 60, '/');
				break;
			}
		}
	}
	// Get chosen template
	$sw_template = $_COOKIE['sw_template'];

	$sw_count = intval(pluginGetVariable('template_switch', 'count'));
	if (!$sw_count) {
		$sw_count = 3;
	}

	// If template is not selected, we can show default value
	if (!$sw_template) {
		// Check if we have default profile for this domain
		for ($i = 0; $i <= $sw_count; $i++) {
			$dlist = pluginGetVariable('template_switch', 'profile' . $i . '_domains');
			if (!$dlist) continue;
			if (!is_array($darr = explode("\n", $dlist))) continue;
			$is_catched = 0;
			foreach ($darr as $dname) {
				$dname = trim($dname);
				if (!$dname) continue;
				// Check if domain fits our domain
				if (($_SERVER['SERVER_NAME'] == $dname) || ($_SERVER['HTTP_HOST'] == $dname)) {
					$is_catched = 1;
				}
			}
			if ($is_catched) {
				$sw_template = $i;
				break;
			}
		}
	}

	if (($sw_template > 0) && ($sw_template <= $sw_count) && pluginGetVariable('template_switch', 'profile' . $sw_template . '_active')) {
		if (pluginGetVariable('template_switch', 'profile' . $sw_template . '_template')) {
			$config['theme'] = pluginGetVariable('template_switch', 'profile' . $sw_template . '_template');
		}
		if (pluginGetVariable('template_switch', 'profile' . $sw_template . '_lang')) {
			$config['default_lang'] = pluginGetVariable('template_switch', 'profile' . $sw_template . '_lang');
		}
	}
}

function plugin_template_switch_menu()
{
	global $template, $tpl, $lang;

	if (isset($_GET['template_switch_frame'])) {
		return;
	}

	if (isset($GLOBALS['template_switch_rendered']) || isset($_GET['template_switch_frame'])) {
		return;
	}

	static $executed = false;
	if ($executed) return;
	$executed = true;

	$GLOBALS['template_switch_rendered'] = true;
	$list = '';
	$sw_count = intval(pluginGetVariable('template_switch', 'count'));
	if (!$sw_count) {
		$sw_count = 3;
	}

	// Получаем текущий выбранный шаблон
	$current_template = $_COOKIE['sw_template'] ?? 1;

	// Получаем ссылку на описание для текущего шаблона
	$description_link = pluginGetVariable('template_switch', 'profile' . $current_template . '_description_link');

	for ($i = 1; $i <= $sw_count; $i++) {
		if (pluginGetVariable('template_switch', 'profile' . $i . '_active')) {
			$profileName = pluginGetVariable('template_switch', 'profile' . $i . '_name');
			if (empty($profileName)) {
				$profileName = pluginGetVariable('template_switch', 'profile' . $i . '_template');
			}
			$list .= "<option value='$i'>" . htmlspecialchars($profileName) . "</option>\n";
		}
	}

	LoadPluginLang('template_switch', 'main', '', 'template_switch');

	$tpath = locatePluginTemplates(array('template_switch'), 'template_switch', pluginGetVariable('template_switch', 'localsource'));
	$tpl->template('template_switch', $tpath['template_switch']);

	$tvars['vars']['current_template'] = $current_template;
	$tvars['vars']['is_frame'] = isset($_GET['template_switch_frame']);
	$tvars['vars']['list'] = $list;
	$tvars['vars']['description_link'] = $description_link; // Передаем ссылку в шаблон

	$tpl->vars('template_switch', $tvars);
	$template['vars']['template_switch'] = $tpl->show('template_switch');
	register_htmlvar('plain', '{template_switch}');
}

function template_switch_redirector()
{
	$templateID = $_REQUEST['profile'];

	// Scan for template with this ID
	$sw_count = intval(pluginGetVariable('template_switch', 'count'));
	if (!$sw_count) {
		$sw_count = 3;
	}

	$templateNum = 0;
	for ($i = 1; $i <= $sw_count; $i++) {
		if (pluginGetVariable('template_switch', 'profile' . $i . '_id') == $templateID) {
			$templateNum = $i;
			break;
		}
	}

	// Set cookie with template ID
	@setcookie('sw_template', $templateNum, time() + 365 * 24 * 60 * 60, '/');

	// Redirect user:
	// if `redirect` is set - to specified URL
	// if `redirect` is not set - to root directory of the site
	@header("Location: " . (pluginGetVariable('template_switch', 'profile' . $i . '_redirect') ? pluginGetVariable('template_switch', 'profile' . $i . '_redirect') : home));
}
