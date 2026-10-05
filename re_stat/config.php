<?php
if(!defined('NGCMS')) exit('HAL');

pluginsLoadConfig();
loadPluginLang('re_stat', 'main', '', '', ':');
ver_ver();

switch ($_REQUEST['action']) {
	case 'edit': case 'add': editform(); break;
	case 'confirm': editform(); break;
	case 'delete': delete(); break;
	case 're_map': re_map(); showlist(); break;
	default: showlist();
}

function showlist()
{
	global $tpl, $mysql, $lang;
	$static_page = $mysql->select('select `id`, `title` from '.prefix.'_static order by `title`, `id`');
	$tpath = locatePluginTemplates(array('conf.list', 'conf.list.row'), 're_stat');
	$output = ''; $no = 1; $t_values = array(); $values = pluginGetVariable('re_stat', 'values');
	foreach($values as $key => $row) {
		$title = '';
		foreach ($static_page as $page) if (intval($page['id']) == $row['id']){$title = $page['title']; break;}
		$pvars['vars'] = array (
			'id' => $key,
			'no' => $no ++,
			'code' => $row['code'],
			'title' => ($title ? $title : '<font color="red">' . $lang['re_stat:missing_page'] . '</font>'),
			'error' => '',
			'edit' => $lang['re_stat:edit'],
			'delete' => $lang['re_stat:delete'],
			);
		if (in_array($row['code'], $t_values, true)) $pvars['vars']['error'] = '<font color="red">' . $lang['re_stat:duplicate_code'] . '</font>';
		$t_values[] = $row['code'];
		$tpl->template('conf.list.row', $tpath['conf.list.row']);
		$tpl -> vars('conf.list.row', $pvars);
		$output .= $tpl->show('conf.list.row');
	}
	$tvars['vars']['entries'] = $output;
	$tvars['vars']['l_list'] = $lang['re_stat:list'];
	$tvars['vars']['l_add'] = $lang['re_stat:add'];
	$tvars['vars']['l_actions_aria'] = $lang['re_stat:actions_aria'];
	$tvars['vars']['l_number'] = $lang['re_stat:number'];
	$tvars['vars']['l_code'] = $lang['re_stat:code'];
	$tvars['vars']['l_static_page'] = $lang['re_stat:static_page'];
	$tvars['vars']['l_action'] = $lang['re_stat:action'];
	$tvars['vars']['l_rebuild_map'] = $lang['re_stat:rebuild_map'];
	$tpl->template('conf.list', $tpath['conf.list']);
	$tpl->vars('conf.list', $tvars);
	print $tpl->show('conf.list');
}

function editform()
{
	global $mysql, $tpl, $config, $lang;
	if (!isset($_REQUEST['id'])) {
		msg(array('type' => 'info', 'info' => '<font color="red">' . $lang['re_stat:error.edit_id_undefined'] . '</font>'));
		showlist();	return false; }
	$id = intval($_REQUEST['id']);
	$values = pluginGetVariable('re_stat', 'values');
	if ($id != -1 && !is_array($values)) {
		msg(array('type' => 'info', 'info' => '<font color="red">' . $lang['re_stat:error.no_values_edit'] . '</font>'));
		showlist();	return false; }
	if ($id != -1 && !array_key_exists($id, $values)) {
		msg(array('type' => 'info', 'info' => '<font color="red">' . sprintf($lang['re_stat:error.id_missing'], $id) . '</font>'));
		showlist(); return false; } 
	$if_error = false; $idstat = 0; $code = '';
	if (isset($_REQUEST['code']) && isset($_REQUEST['idstat'])){
		$code = secure_html(convert($_REQUEST['code']));
		if (!$code) { 
			msg(array('type' => 'info', 'info' => '<font color="red">' . $lang['re_stat:error.empty_code'] . '</font>'));
			$if_error = true; }
		foreach ($values as $key => $row) if ($row['code'] === $code && $key != $id){
			msg(array('type' => 'info', 'info' => '<font color="red">' . $lang['re_stat:error.duplicate_code'] . '</font>'));
			$if_error = true; }
		if (!$if_error){
			$idstat = intval($_REQUEST['idstat']);
			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			if ($id == -1) {
				$values[] = array('code' => $code, 'id' => $idstat);
			} else {
				$ULIB->removeCommand('re_stat', $values[$id]['code']);
				$values[$id]['code'] = $code;
				$values[$id]['id'] = $idstat;}
			pluginSetVariable('re_stat', 'values', $values);
			pluginsSaveConfig();
			$title = '<font color="red">' . $lang['re_stat:missing_page'] . '</font>';
			foreach ($mysql->select('select `title` from '.prefix.'_static where `id`='.$idstat.' limit 1') as $page) $title = $page['title'];
			$ULIB->registerCommand('re_stat', $code, array('vars' => array(), 'descr' => array ($config['default_lang'] => $title)));
			$ULIB->saveConfig();
			showlist();
			return;
		}
	}
	$static_page = $mysql->select('select `id`, `title` from '.prefix.'_static order by `title`, `id`');
	$tpath = locatePluginTemplates(array('conf.edit'), 're_stat');
	$statlist = array();
	foreach ($static_page as $row)
		$statlist[$row['id']] = $row['title'];
	$tvars['vars']['statlist'] = MakeDropDown($statlist, 'idstat', ($if_error?$idstat:(isset($values[$id]['id'])?$values[$id]['id']:-1)));
	$tvars['vars']['code'] = ($if_error?$code:(isset($values[$id]['code'])?$values[$id]['code']:''));
	$tvars['vars']['id'] = $id;
	$tvars['vars']['l_list'] = $lang['re_stat:list'];
	$tvars['vars']['l_add'] = $lang['re_stat:add'];
	$tvars['vars']['l_edit'] = $lang['re_stat:edit'];
	$tvars['vars']['l_actions_aria'] = $lang['re_stat:actions_aria'];
	$tvars['vars']['l_list_item'] = $lang['re_stat:list_item'];
	$tvars['vars']['l_code'] = $lang['re_stat:code'];
	$tvars['vars']['l_static_page'] = $lang['re_stat:static_page'];
	$tvars['regx']['/\[add\](.*?)\[\/add\]/si'] = '';
	$tvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = '';
	if ($id == -1) $tvars['regx']['/\[add\](.*?)\[\/add\]/si'] = '$1'; else $tvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = '$1';
	$tpl->template('conf.edit', $tpath['conf.edit']);
	$tpl->vars('conf.edit', $tvars);
	print $tpl->show('conf.edit');
}

function delete()
{
	global $lang;
	if (!isset($_REQUEST['id'])) {
		msg(array('type' => 'info', 'info' => '<font color="red">' . $lang['re_stat:error.delete_id_undefined'] . '</font>'));
		showlist();	return false; }
	$id = intval($_REQUEST['id']);
	$values = pluginGetVariable('re_stat', 'values');
	if (!is_array($values)) {
		msg(array('type' => 'info', 'info' => '<font color="red">' . $lang['re_stat:error.no_values_delete'] . '</font>'));
		showlist();	return false; }
	if (!array_key_exists($id, $values)) {
		msg(array('type' => 'info', 'info' => '<font color="red">' . sprintf($lang['re_stat:error.id_missing'], $id) . '</font>'));
		showlist(); return false; }

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
	foreach ($values as $key => $row){
		$title = '<font color="red">' . $lang['re_stat:missing_page'] . '</font>';
		foreach ($mysql->select('select `title` from '.prefix.'_static where `id`='.$row['id'].' limit 1') as $page) $title = $page['title'];
		$ULIB->registerCommand('re_stat', $row['code'], array('vars' => array(), 'descr' => array ($config['default_lang'] => $title)));
	}
	$ULIB->saveConfig();
	msg(array('type' => 'info', 'info' => '<font color="green">' . $lang['re_stat:map_rebuilt'] . '</font>'));
}

function ver_ver()
{
	global $mysql, $PLUGINS, $lang;
	$versionbase = pluginGetVariable('re_stat', 'version');
	if (isset($PLUGINS['config']['re_stat']) && !$versionbase) $versionbase = '0.01';
	else if (!$versionbase) {$versionbase = '0.02'; pluginSetVariable('re_stat', 'version', $versionbase); pluginsSaveConfig();}
	switch ($versionbase) {
	case '0.01':
		$count = 0; $values = array();
		if (isset($PLUGINS['config']['re_stat'])) $count = count($PLUGINS['config']['re_stat']) / 2;
		$static_page = $mysql->select('select `id`, `alt_name` from '.prefix.'_static');
		for ($i = 0; $i < $count; $i ++){
			$id = 0;
			foreach ($static_page as $page) 
				if ($page['alt_name'] == pluginGetVariable('re_stat', 'altstat'.$i))
					{$id = intval($page['id']); break;}
			$values[] = array('id' => $id, 'code' => pluginGetVariable('re_stat', 'code'.$i));
			unset($PLUGINS['config']['re_stat']['code'.$i]); 
			unset($PLUGINS['config']['re_stat']['altstat'.$i]);
		}
		pluginSetVariable('re_stat', 'values', $values);
		pluginSetVariable('re_stat', 'version', '0.02');
		pluginsSaveConfig();
		msg(array('type' => 'info', 'info' => '<font color="green">' . $lang['re_stat:config_migrated'] . '</font>'));
		re_map();
	}
}