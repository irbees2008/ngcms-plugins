<?php
if (!defined('NGCMS')) {
	die("Don't you figure you're so cool?");
}
pluginsLoadConfig();
$plugin = 'autokeys';
$cfg = array();
array_push($cfg, array('name' => 'activate_add', 'title' => 'Автоматическое создание при добавлении новости<br/><small><b>Да</b> - ключевые слова будут автоматически создаваться при добавлении новости', 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => pluginGetVariable('autokeys', 'activate_add')));
array_push($cfg, array('name' => 'activate_edit', 'title' => 'Автоматическое пересоздание при изменении новости<br/><small><b>Да</b> - ключевые слова будут автоматически пересоздаваться при изменении новости', 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => pluginGetVariable('autokeys', 'activate_edit')));
array_push($cfg, array('name' => 'length', 'title' => 'Минимальная длина слова', 'descr' => '(хороший вариант 5)', 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable('autokeys', 'length')));
array_push($cfg, array('name' => 'sub', 'title' => 'Максимальная длина слова', 'descr' => 'По умолчанию не ограничено', 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable('autokeys', 'sub')));
array_push($cfg, array('name' => 'occur', 'title' => 'Минимальное число повторений слова', 'descr' => '(хороший вариант 2)', 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable('autokeys', 'occur')));
array_push($cfg, array('name' => 'block_y', 'title' => '<b>Нежелательные слова</b>', 'descr' => 'включение/выключение опции', 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => pluginGetVariable('autokeys', 'block_y')));
// Список популярных стоп-слов по умолчанию
$default_stop_words = "и\nв\nво\nне\nчто\nон\nна\nя\nс\nсо\nкак\nа\nто\nвсе\nот\nтак\nно\nже\nза\nпо\nпри\nдля\nиз\nнет\nбыли\nего\nее\nбыло\nеще\nэтот\nуже\nили\nк\nкогда\nони\nесть\nвы\nкоторый\nона\nсвою\nчтобы\nмог\nо\nу\nних\nтеперь\nдаже\nтолько\nвот\nсебя\nчем\nбудет\nпод\nтакже\nсказала\nможет\nсвоих\nсвой\nкто\nдо\nвас\nхорошо\nгде\nпочему\nможно\nпотому\nбыть\nсвоих\nкаждый\nочень\nвсегда\nконечно\nсовсем\nчерез\nпервую\nмежду\nэтих\nбыла\nбыли\nбудут\nсвое\nнашей\nваша\nих\nли\nмои\nсвои\nту\nсвою\nтех\nтем\nтой\nтому\nтого\nтакая\nтакое\nтакие\nтаких\nснова\nнесколько\nсейчас\nчего\nкакой\nкоторой\nкоторых\nкоторое\nкакие\nкакая\nникакой\nникаких\nничего\nникогда\nоднако\nпоэтому\nпочти\nразве\nсразу\nследует\nследующий\nтакой\nтому\nтут\nтогда\nтот\nтою\nтуто\nтута\nтаким\nэтим\nэтой\nэто\nэту\nчуть\nчего\nчему\nчем\nгде\nкуда\nоткуда\nсколько\nпотому\nчтобы\nкогда\nесли\nкак\nсловно\nбудто\nхотя\nпока\nпрежде\nзатем\nпотом\nпосле\nперед\nоколо";

// Добавляем настройку для списка нежелательных слов
array_push($cfg, array(
	'name' => 'block',
	'title' => 'Список нежелательных слов<br><br><i>На каждой строке вводится по одному слову. Слова из этого списка не будут попадать в keywords.</i>',
	'type' => 'text',
	'html_flags' => 'rows=8 cols=60',
	'value' => pluginGetVariable('autokeys', 'block') ?: $default_stop_words // Используем стоп-слова по умолчанию, если значение не задано
));
array_push($cfg, array('name' => 'good_y', 'title' => '<b>Желаемые слова</b>', 'descr' => 'включение/выключение опции', 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => pluginGetVariable('autokeys', 'good_y')));
array_push($cfg, array('name' => 'good', 'title' => 'Список желаемых слов<br><br><i>На каждой строке вводится по одноу слову. Слова из этого всегда будут попадать в keywords.</i>', 'type' => 'text', 'html_flags' => 'rows=8 cols=60', 'value' => pluginGetVariable('autokeys', 'good')));
array_push($cfg, array('name' => 'add_title', 'title' => 'Учитывать заголовок', 'descr' => 'Добавления заголовка новости к тексту новости для генерации ключевых слов<br />значение от 0 до бесконечности: <br />0 - не добавлять, 1 - добавлять, 2 - добавить два раза', 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable('autokeys', 'add_title')));
array_push($cfg, array('name' => 'sum', 'title' => 'Длина ключевых слов', 'descr' => 'Длина всех ключевых слов генерируемых плагином (По умолчанию <=245 симолов)', 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable('autokeys', 'sum')));
array_push($cfg, array('name' => 'count', 'title' => 'Количество ключевых слов', 'descr' => 'Количество ключевых слов генерируемых плагином (По умолчанию неограниченное количество)', 'type' => 'input', 'html_flags' => 'style="width: 200px;"', 'value' => pluginGetVariable('autokeys', 'count')));
array_push($cfg, array('name' => 'good_b', 'title' => '<b>Усиление слов</b>', 'descr' => 'Усиление слов в теге [b]', 'type' => 'select', 'values' => array(0 => $lang['noa'], 1 => $lang['yesa']), 'value' => pluginGetVariable('autokeys', 'good_b')));

if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}