<?php
if (!defined('NGCMS')) exit('HAL');
pluginsLoadConfig();
LoadPluginLang('ads_pro', 'config', '', '', ':');
//pluginSetVariable('ads_pro', 'data', array());
//pluginsSaveConfig();
// Helper function to render templates
function renderTemplate($templateName, $vars = [], $tpath = [], $tpl)
{
	$tpl->template($templateName, $tpath[$templateName]);
	$tpl->vars($templateName, $vars);
	return $tpl->show($templateName);
}

// Main function dispatcher
function dispatchAction()
{
	$actions = [
		'list' => 'showlist',
		'add' => 'add',
		'edit' => 'add',
		'add_submit' => 'add_submit',
		'edit_submit' => 'add_submit',
		'move_up' => fn() => move('up'),
		'move_down' => fn() => move('down'),
		'dell' => 'delete',
		'main_submit' => 'main_submit',
		'clear_cash' => 'clear_cash',
		'default' => 'main'
	];

	$action = $_REQUEST['action'] ?? 'default';
	if (array_key_exists($action, $actions)) {
		$handler = $actions[$action];
		is_callable($handler) ? $handler() : $handler();
	} else {
		main();
	}
}

// Main settings submission handler
function main_submit()
{
	global $tpl, $lang;

	$settings = ['support_news', 'news_cfg_sort', 'multidisplay_mode'];
	$chg = 0;

	foreach ($settings as $setting) {
		$newValue = intval($_REQUEST[$setting] ?? 0);
		if ($newValue !== pluginGetVariable('ads_pro', $setting)) {
			pluginSetVariable('ads_pro', $setting, $newValue);
			$chg++;
		}
	}

	if ($chg) {
		pluginsSaveConfig();
	}

	main();
}

// Get ad configuration by ID
function getAdsConfigById($id, $pConfig)
{
	foreach ($pConfig as $k => $v) {
		if (isset($v[$id])) {
			return ['name' => $k, 'config' => $v[$id]];
		}
	}
	return ['name' => '', 'config' => null];
}

// Add/Edit ad configuration
function add()
{
	global $tpl, $tpath, $lang;

	$id = intval($_REQUEST['id'] ?? 0);
	$pConfig = pluginGetVariable('ads_pro', 'data');
	$adConfig = getAdsConfigById($id, $pConfig);

	$tvars = [
		'vars' => [
			'id' => $id,
			'ad_name' => $adConfig['name'] ?? '',
			'ad_config' => $adConfig['config'] ?? ''
		]
	];

	$tpl->template('conf.edit', $tpath['conf.edit']);
	$tpl->vars('conf.edit', $tvars);
	print $tpl->show('conf.edit');
}

// Move ad configuration
function move($direction)
{
	// Implement the logic for moving ads up or down
	global $tpl;
	$tpl->vars('message', ['vars' => ['text' => "Moved $direction"]]);
	print $tpl->show('message');
}

// Delete ad configuration
function delete()
{
	global $tpl;

	$id = intval($_REQUEST['id'] ?? 0);
	$pConfig = pluginGetVariable('ads_pro', 'data');
	foreach ($pConfig as $k => $v) {
		if (isset($v[$id])) {
			unset($pConfig[$k][$id]);
			pluginSetVariable('ads_pro', 'data', $pConfig);
			pluginsSaveConfig();
			break;
		}
	}

	$tpl->vars('message', ['vars' => ['text' => 'Deleted successfully']]);
	print $tpl->show('message');
}

// Main function
function main()
{
	global $tpl, $tpath;

	$tvars = ['vars' => ['entries' => '']];
	print renderTemplate('conf.main', $tvars, $tpath, $tpl);
}

// Invoke the dispatcher
dispatchAction();