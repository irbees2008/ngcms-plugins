<?php
if (!defined('NGCMS')) exit('HAL');

pluginsLoadConfig();
loadPluginLang('re_stat', 'config', '', '', ':');
ver_ver();

switch ($_REQUEST['action']) {
	case 'edit':
	case 'add':
		editform();
		break;
	case 'confirm':
		editform();
		break;
	case 'delete':
		delete();
		break;
	case 're_map':
		re_map();
		showlist();
		break;
	default:
		showlist();
}

function showlist()
{
	global $mysql, $lang, $twig;
	$static_page = $mysql->select('select `id`, `title` from ' . prefix . '_static order by `title`, `id`');
	$tpath = locatePluginTemplates(array('conf.list'), 're_stat');
	$values = pluginGetVariable('re_stat', 'values');
	$t_values = array();
	$entries = array();
	$no = 1;
	foreach ($values as $key => $row) {
		$title = '';
		foreach ($static_page as $page) if (intval($page['id']) == $row['id']) {
			$title = $page['title'];
			break;
		}
		$entries[] = array(
			'id'    => $key,
			'no'    => $no++,
			'code'  => htmlspecialchars($row['code']),
			'title' => ($title ? $title : $lang['re_stat:err.no_page']),
			'error' => in_array($row['code'], $t_values, true) ? $lang['re_stat:err.duplicate_code'] : '',
		);
		$t_values[] = $row['code'];
	}
	$xt = $twig->loadTemplate($tpath['conf.list'] . 'conf.list.tpl');
	print $xt->render(array(
		'entries'        => $entries,
		'l_extras'       => $lang['extras'],
		'lbl_list'       => $lang['re_stat:tpl.btn.list'],
		'lbl_add'        => $lang['re_stat:tpl.btn.add'],
		'lbl_remap'      => $lang['re_stat:tpl.btn.remap'],
		'lbl_col_no'     => $lang['re_stat:tpl.col.no'],
		'lbl_col_code'   => $lang['re_stat:tpl.col.code'],
		'lbl_col_page'   => $lang['re_stat:tpl.col.page'],
		'lbl_col_action' => $lang['re_stat:tpl.col.action'],
		'lbl_edit'       => $lang['re_stat:tpl.row.edit'],
		'lbl_delete'     => $lang['re_stat:tpl.row.delete'],
	));
}

function editform()
{
	global $mysql, $twig, $config, $lang;
	if (!isset($_REQUEST['id'])) {
		msg(array('type' => 'info', 'info' => $lang['re_stat:err.no_id_edit']));
		showlist();
		return false;
	}
	$id = intval($_REQUEST['id']);
	$values = pluginGetVariable('re_stat', 'values');
	if ($id != -1 && !is_array($values)) {
		msg(array('type' => 'info', 'info' => $lang['re_stat:err.no_values_edit']));
		showlist();
		return false;
	}
	if ($id != -1 && !array_key_exists($id, $values)) {
		msg(array('type' => 'info', 'info' => sprintf($lang['re_stat:err.no_key'], $id)));
		showlist();
		return false;
	}
	$if_error = false;
	$idstat = 0;
	$code = '';
	if (isset($_REQUEST['code']) && isset($_REQUEST['idstat'])) {
		$code = secure_html(convert($_REQUEST['code']));
		if (!$code) {
			msg(array('type' => 'info', 'info' => $lang['re_stat:err.empty_code']));
			$if_error = true;
		}
		foreach ($values as $key => $row) if ($row['code'] === $code && $key != $id) {
			msg(array('type' => 'info', 'info' => $lang['re_stat:err.duplicate_code_value']));
			$if_error = true;
		}
		if (!$if_error) {
			$idstat = intval($_REQUEST['idstat']);
			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			if ($id == -1) {
				$values[] = array('code' => $code, 'id' => $idstat);
			} else {
				$ULIB->removeCommand('re_stat', $values[$id]['code']);
				$values[$id]['code'] = $code;
				$values[$id]['id'] = $idstat;
			}
			pluginSetVariable('re_stat', 'values', $values);
			pluginsSaveConfig();
			$title = $lang['re_stat:err.no_page'];
			foreach ($mysql->select('select `title` from ' . prefix . '_static where `id`=' . $idstat . ' limit 1') as $page) $title = $page['title'];
			$ULIB->registerCommand('re_stat', $code, array('vars' => array(), 'descr' => array($config['default_lang'] => $title)));
			$ULIB->saveConfig();
			showlist();
			return;
		}
	}
	$static_page = $mysql->select('select `id`, `title` from ' . prefix . '_static order by `title`, `id`');
	$tpath = locatePluginTemplates(array('conf.edit'), 're_stat');
	$statlist = array();
	foreach ($static_page as $row)
		$statlist[$row['id']] = $row['title'];
	$xt = $twig->loadTemplate($tpath['conf.edit'] . 'conf.edit.tpl');
	print $xt->render(array(
		'statlist'     => MakeDropDown($statlist, 'idstat', ($if_error ? $idstat : (isset($values[$id]['id']) ? $values[$id]['id'] : -1))),
		'code'         => ($if_error ? $code : (isset($values[$id]['code']) ? $values[$id]['code'] : '')),
		'id'           => $id,
		'l_extras'     => $lang['extras'],
		'lbl_list'     => $lang['re_stat:tpl.btn.list'],
		'lbl_add'      => $lang['re_stat:tpl.btn.add'],
		'lbl_edit'     => $lang['re_stat:tpl.btn.edit'],
		'lbl_col_code' => $lang['re_stat:tpl.col.code'],
		'lbl_col_page' => $lang['re_stat:tpl.col.page'],
		'lbl_item'     => $lang['re_stat:tpl.item'],
	));
}

function delete()
{
	global $lang;
	if (!isset($_REQUEST['id'])) {
		msg(array('type' => 'info', 'info' => $lang['re_stat:err.no_id_del']));
		showlist();
		return false;
	}
	$id = intval($_REQUEST['id']);
	$values = pluginGetVariable('re_stat', 'values');
	if (!is_array($values)) {
		msg(array('type' => 'info', 'info' => $lang['re_stat:err.no_values_del']));
		showlist();
		return false;
	}
	if (!array_key_exists($id, $values)) {
		msg(array('type' => 'info', 'info' => sprintf($lang['re_stat:err.no_key'], $id)));
		showlist();
		return false;
	}

	$ULIB = new urlLibrary();
	$ULIB->loadConfig();
	$ULIB->removeCommand('re_stat', $values[$id]['code']);
	$ULIB->saveConfig();

	unset($values[$id]);
	pluginSetVariable('re_stat', 'values', $values);
	pluginsSaveConfig();
	showlist();
}

function re_map()
{
	global $mysql, $config, $lang;
	$ULIB = new urlLibrary();
	$ULIB->loadConfig();
	if (isset($ULIB->CMD['re_stat']))
		unset($ULIB->CMD['re_stat']);
	$values = pluginGetVariable('re_stat', 'values');
	foreach ($values as $key => $row) {
		$title = $lang['re_stat:err.no_page'];
		foreach ($mysql->select('select `title` from ' . prefix . '_static where `id`=' . $row['id'] . ' limit 1') as $page) $title = $page['title'];
		$ULIB->registerCommand('re_stat', $row['code'], array('vars' => array(), 'descr' => array($config['default_lang'] => $title)));
	}
	$ULIB->saveConfig();
	msg(array('type' => 'info', 'info' => $lang['re_stat:ok.remap']));
}

function ver_ver()
{
	global $mysql, $PLUGINS, $lang;
	$versionbase = pluginGetVariable('re_stat', 'version');
	if (isset($PLUGINS['config']['re_stat']) && !$versionbase) $versionbase = '0.01';
	else if (!$versionbase) {
		$versionbase = '0.02';
		pluginSetVariable('re_stat', 'version', $versionbase);
		pluginsSaveConfig();
	}
	switch ($versionbase) {
		case '0.01':
			$count = 0;
			$values = array();
			if (isset($PLUGINS['config']['re_stat'])) $count = count($PLUGINS['config']['re_stat']) / 2;
			$static_page = $mysql->select('select `id`, `alt_name` from ' . prefix . '_static');
			for ($i = 0; $i < $count; $i++) {
				$id = 0;
				foreach ($static_page as $page)
					if ($page['alt_name'] == pluginGetVariable('re_stat', 'altstat' . $i)) {
						$id = intval($page['id']);
						break;
					}
				$values[] = array('id' => $id, 'code' => pluginGetVariable('re_stat', 'code' . $i));
				unset($PLUGINS['config']['re_stat']['code' . $i]);
				unset($PLUGINS['config']['re_stat']['altstat' . $i]);
			}
			pluginSetVariable('re_stat', 'values', $values);
			pluginSetVariable('re_stat', 'version', '0.02');
			pluginsSaveConfig();
			msg(array('type' => 'info', 'info' => $lang['re_stat:ok.updated']));
			re_map();
	}
}
