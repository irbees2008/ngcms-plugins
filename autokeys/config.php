<?php
if (!defined('NGCMS')) {
	die("Don't you figure you're so cool?");
}

pluginsLoadConfig();

$cfg = [];

$cfg[] = [
	'name' => 'activate_add',
	'title' => 'Автоматическое создание при добавлении новости<br/><small><b>Да</b> - ключевые слова будут автоматически создаваться при добавлении новости',
	'type' => 'select',
	'values' => [0 => $lang['noa'], 1 => $lang['yesa']],
	'value' => pluginGetVariable('autokeys', 'activate_add')
];

$cfg[] = [
	'name' => 'activate_edit',
	'title' => 'Автоматическое пересоздание при изменении новости<br/><small><b>Да</b> - ключевые слова будут автоматически пересоздаваться при изменении новости',
	'type' => 'select',
	'values' => [0 => $lang['noa'], 1 => $lang['yesa']],
	'value' => pluginGetVariable('autokeys', 'activate_edit')
];

$cfg[] = [
	'name' => 'length',
	'title' => 'Минимальная длина слова',
	'descr' => '(хороший вариант 5)',
	'type' => 'input',
	'html_flags' => 'style="width: 200px;"',
	'value' => pluginGetVariable('autokeys', 'length')
];

$cfg[] = [
	'name' => 'sub',
	'title' => 'Максимальная длина слова',
	'descr' => 'По умолчанию не ограничено',
	'type' => 'input',
	'html_flags' => 'style="width: 200px;"',
	'value' => pluginGetVariable('autokeys', 'sub')
];

$cfg[] = [
	'name' => 'occur',
	'title' => 'Минимальное число повторений слова',
	'descr' => '(хороший вариант 2)',
	'type' => 'input',
	'html_flags' => 'style="width: 200px;"',
	'value' => pluginGetVariable('autokeys', 'occur')
];

$cfg[] = [
	'name' => 'block_y',
	'title' => '<b>Нежелательные слова</b>',
	'descr' => 'включение/выключение опции',
	'type' => 'select',
	'values' => [0 => $lang['noa'], 1 => $lang['yesa']],
	'value' => pluginGetVariable('autokeys', 'block_y')
];

$cfg[] = [
	'name' => 'block',
	'title' => 'Список нежелательных слов<br><br><i>На каждой строке вводится по одному слову. Слова из этого списка не будут попадать в keywords.</i>',
	'type' => 'text',
	'html_flags' => 'rows=8 cols=60',
	'value' => pluginGetVariable('autokeys', 'block')
];

$cfg[] = [
	'name' => 'good_y',
	'title' => '<b>Желаемые слова</b>',
	'descr' => 'включение/выключение опции',
	'type' => 'select',
	'values' => [0 => $lang['noa'], 1 => $lang['yesa']],
	'value' => pluginGetVariable('autokeys', 'good_y')
];

$cfg[] = [
	'name' => 'good',
	'title' => 'Список желаемых слов<br><br><i>На каждой строке вводится по одному слову. Слова из этого списка всегда будут попадать в keywords.</i>',
	'type' => 'text',
	'html_flags' => 'rows=8 cols=60',
	'value' => pluginGetVariable('autokeys', 'good')
];

$cfg[] = [
	'name' => 'add_title',
	'title' => 'Учитывать заголовок',
	'descr' => 'Добавление заголовка новости к тексту новости для генерации ключевых слов<br />значение от 0 до бесконечности: <br />0 - не добавлять, 1 - добавлять, 2 - добавить два раза',
	'type' => 'input',
	'html_flags' => 'style="width: 200px;"',
	'value' => pluginGetVariable('autokeys', 'add_title')
];

$cfg[] = [
	'name' => 'sum',
	'title' => 'Длина ключевых слов',
	'descr' => 'Длина всех ключевых слов, генерируемых плагином (по умолчанию <=245 символов)',
	'type' => 'input',
	'html_flags' => 'style="width: 200px;"',
	'value' => pluginGetVariable('autokeys', 'sum')
];

$cfg[] = [
	'name' => 'count',
	'title' => 'Количество ключевых слов',
	'descr' => 'Количество ключевых слов, генерируемых плагином (по умолчанию неограниченное количество)',
	'type' => 'input',
	'html_flags' => 'style="width: 200px;"',
	'value' => pluginGetVariable('autokeys', 'count')
];

$cfg[] = [
	'name' => 'good_b',
	'title' => '<b>Усиление слов</b>',
	'descr' => 'Усиление слов в теге [b]',
	'type' => 'select',
	'values' => [0 => $lang['noa'], 1 => $lang['yesa']],
	'value' => pluginGetVariable('autokeys', 'good_b')
];

if ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes('autokeys', $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page('autokeys', $cfg);
}