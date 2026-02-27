<?php
/*
 * Plugin's "Private message" configuration file for NextGeneration CMS (http://ngcms.ru/)
 * Copyright (C) 2011 Alexey N. Zhukov (http://digitalplace.ru)
 * http://digitalplace.ru
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */
# protect against hack attempts
if (!defined('NGCMS')) die('Galaxy in danger');
# preload config file
PluginsLoadConfig();
LoadPluginLang($plugin, 'config', '', '', ':');
# fill configuration parameters
$cfg = array();
array_push($cfg, array(
	'name'   => 'rebuild',
	'title'  => $lang['pm:rebuild.title'],
	'descr'  => $lang['pm:rebuild.descr'],
	'type'   => 'select',
	'value'  => 0,
	'values' => array(0 => $lang['pm:rebuild.no'], 1 => $lang['pm:rebuild.yes']),
	'nosave' => 1
));
$cfgX = array();
array_push($cfgX, array(
	'name'  => 'max_messages',
	'title' => $lang['pm:max_messages.title'],
	'type'  => 'input',
	'value' => intval(pluginGetVariable($plugin, 'max_messages') ? intval(pluginGetVariable($plugin, 'max_messages')) : 100)
));
array_push($cfgX, array(
	'name'  => 'msg_per_page',
	'title' => $lang['pm:msg_per_page.title'],
	'type'  => 'input',
	'value' => intval(pluginGetVariable($plugin, 'msg_per_page') ? intval(pluginGetVariable($plugin, 'msg_per_page')) : 10)
));
array_push($cfgX, array(
	'name'  => 'title_length',
	'title' => $lang['pm:title_length.title'],
	'type'  => 'input',
	'value' => intval(pluginGetVariable($plugin, 'title_length') ? intval(pluginGetVariable($plugin, 'title_length')) : 50)
));
array_push($cfgX, array(
	'name'  => 'message_length',
	'title' => $lang['pm:message_length.title'],
	'type'  => 'input',
	'value' => intval(pluginGetVariable($plugin, 'message_length') ? intval(pluginGetVariable($plugin, 'message_length')) : 3000)
));
array_push($cfg, array(
	'mode'    => 'group',
	'title'   => $lang['pm:group.general'],
	'entries' => $cfgX
));
$cfgX = array();
array_push($cfgX, array(
	'name'   => 'localsource',
	'title'  => $lang['pm:localsource.title'],
	'type'   => 'select',
	'values' => array('0' => $lang['pm:localsource.opt.site'], '1' => $lang['pm:localsource.opt.plugin']),
	'value'  => intval(pluginGetVariable($plugin, 'localsource'))
));
array_push($cfg, array(
	'mode'    => 'group',
	'title'   => $lang['pm:group.display'],
	'entries' => $cfgX
));
// RUN
if ($_REQUEST['action'] == 'commit') {
	if ($_REQUEST['rebuild']) {
		$mysql->query('UPDATE ' . prefix . '_users SET `pm_sync` = 0');
	}
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}
