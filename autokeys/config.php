<?php
if (!defined('NGCMS')) {
	die("Don't you figure you're so cool?");
}
global $lang;
pluginsLoadConfig();
$plugin = 'autokeys';
LoadPluginLang($plugin, 'config', '', '', ':');
$cfg = array();
$boolValues = array(0 => $lang['autokeys:bool.no'], 1 => $lang['autokeys:bool.yes']);
array_push($cfg, array('name' => 'activate_add', 'title' => $lang['autokeys:activate_add'], 'descr' => $lang['autokeys:activate_add#desc'], 'type' => 'select', 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'activate_add')));
array_push($cfg, array('name' => 'activate_edit', 'title' => $lang['autokeys:activate_edit'], 'descr' => $lang['autokeys:activate_edit#desc'], 'type' => 'select', 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'activate_edit')));
array_push($cfg, array('name' => 'length', 'title' => $lang['autokeys:length'], 'descr' => $lang['autokeys:length#desc'], 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable($plugin, 'length')));
array_push($cfg, array('name' => 'sub', 'title' => $lang['autokeys:sub'], 'descr' => $lang['autokeys:sub#desc'], 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable($plugin, 'sub')));
array_push($cfg, array('name' => 'occur', 'title' => $lang['autokeys:occur'], 'descr' => $lang['autokeys:occur#desc'], 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable($plugin, 'occur')));
array_push($cfg, array('name' => 'block_y', 'title' => $lang['autokeys:block_y'], 'descr' => $lang['autokeys:block_y#desc'], 'type' => 'select', 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'block_y')));
// Список популярных стоп-слов по умолчанию
$default_stop_words = "и\nв\nво\nне\nчто\nон\nна\nя\nс\nсо\nкак\nа\nто\nвсе\nот\nтак\nно\nже\nза\nпо\nпри\nдля\nиз\nнет\nбыли\nего\nее\nбыло\nеще\nэтот\nуже\nили\nк\nкогда\nони\nесть\nвы\nкоторый\nона\nсвою\nчтобы\nмог\nо\nу\nних\nтеперь\nдаже\nтолько\nвот\nсебя\nчем\nбудет\nпод\nтакже\nсказала\nможет\nсвоих\nсвой\nкто\nдо\nвас\nхорошо\nгде\nпочему\nможно\nпотому\nбыть\nсвоих\nкаждый\nочень\nвсегда\nконечно\nсовсем\nчерез\nпервую\nмежду\nэтих\nбыла\nбыли\nбудут\nсвое\nнашей\nваша\nих\nли\nмои\nсвои\nту\nсвою\nтех\nтем\nтой\nтому\nтого\nтакая\nтакое\nтакие\nтаких\nснова\nнесколько\nсейчас\nчего\nкакой\nкоторой\nкоторых\nкоторое\nкакие\nкакая\nникакой\nникаких\nничего\nникогда\nоднако\nпоэтому\nпочти\nразве\nсразу\nследует\nследующий\nтакой\nтому\nтут\nтогда\nтот\nтою\nтуто\nтута\nтаким\nэтим\nэтой\nэто\nэту\nчуть\nчего\nчему\nчем\nгде\nкуда\nоткуда\nсколько\nпотому\nчтобы\nкогда\nесли\nкак\nсловно\nбудто\nхотя\nпока\nпрежде\nзатем\nпотом\nпосле\nперед\nоколо";

// Добавляем настройку для списка нежелательных слов
array_push($cfg, array(
	'name' => 'block',
	'title' => $lang['autokeys:block'],
	'descr' => $lang['autokeys:block#desc'],
	'type' => 'text',
	'html_flags' => 'rows=8 cols=60',
	'value' => pluginGetVariable($plugin, 'block') ?: $default_stop_words // Используем стоп-слова по умолчанию, если значение не задано
));
array_push($cfg, array('name' => 'good_y', 'title' => $lang['autokeys:good_y'], 'descr' => $lang['autokeys:good_y#desc'], 'type' => 'select', 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'good_y')));
array_push($cfg, array('name' => 'good', 'title' => $lang['autokeys:good'], 'descr' => $lang['autokeys:good#desc'], 'type' => 'text', 'html_flags' => 'rows=8 cols=60', 'value' => pluginGetVariable($plugin, 'good')));
array_push($cfg, array('name' => 'add_title', 'title' => $lang['autokeys:add_title'], 'descr' => $lang['autokeys:add_title#desc'], 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable($plugin, 'add_title')));
array_push($cfg, array('name' => 'sum', 'title' => $lang['autokeys:sum'], 'descr' => $lang['autokeys:sum#desc'], 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable($plugin, 'sum')));
array_push($cfg, array('name' => 'count', 'title' => $lang['autokeys:count'], 'descr' => $lang['autokeys:count#desc'], 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable($plugin, 'count')));
array_push($cfg, array('name' => 'good_b', 'title' => $lang['autokeys:good_b'], 'descr' => $lang['autokeys:good_b#desc'], 'type' => 'select', 'values' => $boolValues, 'value' => pluginGetVariable($plugin, 'good_b')));

if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}