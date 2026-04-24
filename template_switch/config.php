<?php

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();
LoadPluginLang('template_switch', 'config');

$cfg = array();
array_push($cfg, array('descr' => $lang['template_switch_description']));

$tpl_list = array();
$tpl_list = array_merge($tpl_list, ListFiles('../templates', ''));

$cfgX = array();
$profile_count = intval(pluginGetVariable('template_switch', 'count'));
if (!$profile_count) {
	$profile_count = 3;
}

array_push($cfgX, array(
	'name' => 'count',
	'title' => $lang['template_switch_count'],
	'descr' => $lang['template_switch_count_desc'],
	'type' => 'input',
	'html_flags' => ' size="5"',
	'value' => $profile_count
));

array_push($cfgX, array(
	'name' => 'selfpage',
	'title' => $lang['template_switch_selfpage'],
	'descr' => $lang['template_switch_selfpage_desc'],
	'type' => 'select',
	'values' => array('1' => $lang['yesa'], '0' => $lang['noa']),
	'value' => pluginGetVariable('template_switch', 'selfpage')
));

array_push($cfg, array(
	'mode' => 'group',
	'title' => '<b>' . $lang['template_switch_commonconfig'] . '</b>',
	'entries' => $cfgX
));

for ($i = 1; $i <= $profile_count; $i++) {
	$cfgX = array();

	// Основные поля профиля
	array_push($cfgX, array(
		'name' => 'profile' . $i . '_active',
		'title' => $lang['template_switch_flagactive'],
		'type' => 'select',
		'values' => array('1' => $lang['yesa'], '0' => $lang['noa']),
		'value' => pluginGetVariable('template_switch', 'profile' . $i . '_active')
	));

	array_push($cfgX, array(
		'name' => 'profile' . $i . '_template',
		'title' => $lang['template_switch_template'],
		'descr' => $lang['template_switch_template_desc'],
		'type' => 'select',
		'values' => $tpl_list,
		'value' => pluginGetVariable('template_switch', 'profile' . $i . '_template'),
		'onchange' => "updateProfileFields($i, this)"
	));

	array_push($cfgX, array(
		'name' => 'profile' . $i . '_name',
		'title' => '',
		'type' => 'hidden',
		'value' => pluginGetVariable('template_switch', 'profile' . $i . '_name'),
		'id' => 'profile' . $i . '_name'
	));

	array_push($cfgX, array(
		'name' => 'profile' . $i . '_id',
		'title' => '',
		'type' => 'hidden',
		'value' => pluginGetVariable('template_switch', 'profile' . $i . '_id'),
		'id' => 'profile' . $i . '_id'
	));

	// Новое поле: ссылка на описание шаблона
	array_push($cfgX, array(
		'name' => 'profile' . $i . '_description_link',
		'title' => $lang['template_switch_description_link'],
		'descr' => $lang['template_switch_description_link_desc'],
		'type' => 'input',
		'html_flags' => ' size="50"',
		'value' => pluginGetVariable('template_switch', 'profile' . $i . '_description_link')
	));

	// Поле для предпросмотра (только для чтения)
	// Поле для предпросмотра (только для чтения)
	$profile_id = pluginGetVariable('template_switch', 'profile' . $i . '_id');
	if (empty($profile_id)) {
		// Генерируем ID из названия шаблона, если он не задан
		$template_name = pluginGetVariable('template_switch', 'profile' . $i . '_template');
		$profile_id = strtolower(preg_replace('/[^a-z0-9]/', '', $template_name));
	}
	$preview_url = home . '/plugin/template_switch/?profile=' . $profile_id;
	array_push($cfgX, array(
		'name' => 'profile' . $i . '_preview_link',
		'title' => $lang['template_switch_preview_link'],
		'descr' => $lang['template_switch_preview_link_desc'],
		'type' => 'input',
		'html_flags' => ' size="50" readonly',
		'value' => $preview_url
	));

	array_push($cfg, array(
		'mode' => 'group',
		'title' => '<b>' . $lang['template_switch_profile'] . ' #' . $i . '</b>',
		'entries' => $cfgX
	));
}

// Добавляем JavaScript для обновления полей
echo <<<HTML
<script>
function updateProfileFields(profileNum, selectElement) {
    var profileName = selectElement.options[selectElement.selectedIndex].text;
    var profileId = profileName.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    document.getElementById('profile' + profileNum + '_name').value = profileName;
    document.getElementById('profile' + profileNum + '_id').value = profileId;
    
    // Обновляем ссылку предпросмотра
    var previewField = document.querySelector('[name="profile' + profileNum + '_preview_link"]');
    if (previewField) {
        previewField.value = window.location.origin + '/plugin/template_switch/?profile=' + profileId;
    }
}
</script>
HTML;

// RUN
if ($_REQUEST['action'] == 'commit') {
	// Генерируем ID для профилей, где он не указан
	for ($i = 1; $i <= $profile_count; $i++) {
		$id = $_REQUEST['profile' . $i . '_id'];
		if (empty($id)) {
			$template_name = $_REQUEST['profile' . $i . '_template'];
			$_REQUEST['profile' . $i . '_id'] = strtolower(preg_replace('/[^a-z0-9]/', '', $template_name));
		}
	}

	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
}
if ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}