<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Configuration file for plugin
//
// Preload config file
pluginsLoadConfig();
// Set default values if values are not set [for new variables]
$pluginDefaults = array(
	'global_default'     => 1,
	'default_news'       => 2,
	'default_categories' => 2,
);
foreach ($pluginDefaults as $k => $v) {
	if (pluginGetVariable('comments', $k) == null)
		pluginSetVariable('comments', $k, $v);
}
// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => 'Плагин реализует базовый функционал работы с комментариями.<br/><font color="red"><b>Обратите внимание:</b> плагин для своей работы использует файлы-шаблоны из <u>основного шаблона сайта</u></font>.'));
$cfgX = array();
array_push($cfgX, array('name' => 'regonly', 'title' => "Комментарии только для зарегистрированных", 'descr' => '<b>Да</b> - комментарии могут оставлять только зарегистрированные пользователи<br/><b>Нет</b> - комментарии может оставить любой посетитель', 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'regonly'))));
array_push($cfgX, array('name' => 'guest_edup_lock', 'title' => "Запретить гостям использовать <b>email</b>'ы зарегистрированных пользователей", 'descr' => '<b>Да</b> - гости не смогут в качестве email адреса указывать адрес уже зарегистрированных пользоваталей<br/><b>Нет</b> - гость может использовать любой валидный email адрес', 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'guest_edup_lock'))));
array_push($cfgX, array('name' => 'backorder', 'title' => "Очередность отображения комментариев", 'descr' => "<b>Прямая</b> - отображение в порядке добавления<br/><b>Обратная</b> - самые новые показываются первыми", 'type' => 'select', 'values' => array('0' => 'Прямая', '1' => 'Обратная'), 'value' => intval(pluginGetVariable($plugin, 'backorder'))));
array_push($cfgX, array('name' => 'maxlen', 'title' => "Максимальный размер", 'descr' => "Укажите максимальное кол-во символов для комментариев (например: <b>200</b>; <b>0</b> - не ограничивать)", 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'maxlen')));
array_push($cfgX, array('name' => 'maxwlen', 'title' => "Автоурезание слов в комментариях", 'descr' => "В случае превышения заданного числа, в слово будет автоматически будет добавляться пробел (например: <b>50</b>)", 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'maxwlen')));
array_push($cfgX, array('name' => 'multi', 'title' => "Разрешить множественные комментарии", 'descr' => "<b>Да</b> - пользователь может оставлять последовательно несколько комментариев<br/><b>Нет</b> - пользователю запрещено размещать последовательно несколько комментариев (необходимо дождаться комментария другого пользователя)", 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'multi'))));
array_push($cfgX, array('name' => 'author_multi', 'title' => "Разрешить множественные комментарии <u>для автора</u>", 'descr' => "<b>Да</b> - автор может оставлять последовательно несколько комментариев<br/><b>Нет</b> - автору запрещено размещать последовательно несколько комментариев", 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'author_multi'))));
array_push($cfgX, array('name' => 'moderation', 'title' => "Модерация комментариев", 'descr' => "<b>Да</b> - новые комментарии требуют одобрения модератора<br/><b>Нет</b> - комментарии публикуются сразу", 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'moderation'))));
loadPluginLang('comments', 'admin', '', '', ':');
array_push($cfgX, array('name' => 'moderation_groups', 'title' => $lang['comments:config.moderation_groups'], 'descr' => $lang['comments:config.moderation_groups#desc'], 'type' => 'input', 'value' => pluginGetVariable($plugin, 'moderation_groups')));
array_push($cfgX, array('name' => 'timestamp', 'title' => "Формат отображения даты/времени", 'descr' => "Помощь по работе функции: <a href=\"http://php.net/date/\" target=\"_blank\">php.net/date</a><br/>Значение по умолчанию: <b>j.m.Y - H:i</b>", 'type' => 'input', 'value' => pluginGetVariable($plugin, 'timestamp')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Общие настройки</b>', 'entries' => $cfgX));
$cfgX = array();
array_push($cfgX, array('name' => 'global_default', 'title' => "По умолчанию комментарии", 'descr' => '<b>разрешены</b> - будут разрешены в новости если они явно не запрещены<br/><b>запрещены</b> - будут запрещены новости если они явно не разрешены', 'type' => 'select', 'values' => array('0' => 'запрещены', '1' => 'разрешены'), 'value' => intval(pluginGetVariable($plugin, 'global_default'))));
array_push($cfgX, array('name' => 'default_news', 'title' => "Значение доступности при добавлении новостей", 'descr' => 'При добавлении новостей по умолчанию будет устанавливаться:<br/><b>запретить</b> - комментарии будут запрещены<br/><b>разрешить</b> - комментарии будут разрешены<br/><b>по умолчанию</b> - флаг разрешения/запрета комментариев будет браться из настроек главной категории новости', 'type' => 'select', 'values' => array('0' => 'запрещены', '1' => 'разрешены', '2' => 'по умолчанию'), 'value' => intval(pluginGetVariable($plugin, 'default_news'))));
array_push($cfgX, array('name' => 'default_categories', 'title' => "Значение доступности при добавлении категорий", 'descr' => 'При добавлении категорий по умолчанию будет устанавливаться:<br/><b>запретить</b> - по умолчанию комментарии в новостях этой категории запрещены<br/><b>разрешить</b> - по умолчанию комментарии в этой категории будут разрешены<br/><b>по умолчанию</b> - флаг разрешения/запрета комментариев будет браться из параметра "по умолчанию комментарии"', 'type' => 'select', 'values' => array('0' => 'запрещены', '1' => 'разрешены', '2' => 'по умолчанию'), 'value' => intval(pluginGetVariable($plugin, 'default_categories'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Настройки по умолчанию</b>', 'entries' => $cfgX, 'toggle' => true, 'toggle.mode' => 'hide'));
$cfgX = array();
// Единый параметр количества комментариев на страницу (используется для AJAX и отдельного просмотра)
array_push($cfgX, array('name' => 'multi_mcount', 'title' => "Кол-во комментариев на страницу", 'descr' => "Укажите лимит комментариев на одну страницу (и при AJAX подгрузке).<br/>(<b>0</b> - не показывать комментарии автоматически)", 'type' => 'input', 'html_flags' => 'size=\"4\"', 'value' => pluginGetVariable($plugin, 'multi_mcount')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Постраничное отображение</b>', 'entries' => $cfgX, 'toggle' => true, 'toggle.mode' => 'hide'));
$cfgX = array();
array_push($cfgX, array('name' => 'inform_author', 'title' => "Оповещать автора новости по email о новом комментарии", 'descr' => "<b>Да</b> - при добавлении каждого комментария автор будет получать e-mail сообщение<br/><b>Нет</b> - автор не будет получать e-mail нотификаций", 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'inform_author'))));
array_push($cfgX, array('name' => 'inform_admin', 'title' => "Оповещать администратора о новом комментарии", 'descr' => "<b>Да</b> - при добавлении каждого комментария администратор будет получать e-mail сообщение<br/><b>Нет</b> - администратор(ы) не будет получать e-mail нотификаций", 'type' => 'select', 'values' => array('0' => 'Нет', '1' => 'Да'), 'value' => intval(pluginGetVariable($plugin, 'inform_admin'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Настройки оповещений</b>', 'entries' => $cfgX, 'toggle' => true, 'toggle.mode' => 'hide'));
// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
