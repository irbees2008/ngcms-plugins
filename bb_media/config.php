<?php
if (!defined('NGCMS')) exit('HAL');
pluginsLoadConfig();
LoadPluginLang('bb_media', 'config', '', '', ':');

switch ($_REQUEST['action']) {
	case 'general_submit':
		general_submit();
		main();
		break;
	default:
		main();
}

function general_submit() {

	global $lang;

	if (isset($_POST['submit'])){
		pluginSetVariable('bb_media', 'player_name', $_POST['player_name']);
		pluginSetVariable('bb_media', 'theme_player', $_POST['theme_player']);

		pluginsSaveConfig();
		msg(array('type' => 'info', 'info' => $lang['bb_media:save_general']));
	}
}

function main() {

	global $tpl, $lang;
	$tpath = locatePluginTemplates(array('conf.main', 'conf.general.form'), 'bb_media', 1);
	
	function getPlayersNames($path) {

		$dirs = array_filter(glob($path . '*'), 'is_dir');
		$dirNames = array();
		foreach ($dirs as $key => $dir) {
			$basename = basename($dir);
			$dirNames[$basename] = $basename;
		}

		return $dirNames;
	}

	$dirNames = getPlayersNames(__DIR__ . '/players/');

    $lout = '';
    foreach ($dirNames as $k => $v) {
        $lout .= '<option value="'.$k.'"'.(pluginGetVariable('bb_media', 'player_name') == $k ? ' selected="selected"' : '').'>'.$v.'</option>';
    }

	$ttvars['vars']['player_name'] = $lout;
	
	$theme_player = pluginGetVariable('bb_media', 'theme_player');
	$ttvars['vars']['theme_player'] = '<option value="default" '.($theme_player=='default'?'selected':'').'>Стандарт</option><option value="city" '.($theme_player=='city'?'selected':'').'>Город</option><option value="sea" '.($theme_player=='sea'?'selected':'').'>Море</option><option value="fantasy" '.($theme_player=='fantasy'?'selected':'').'>Фантазия</option><option value="forest" '.($theme_player=='forest'?'selected':'').'>Лес</option>';

	$ttvars['vars']['action'] = $lang['bb_media:settings'];
	
	$tpl->template('conf.general.form', $tpath['conf.general.form']);
	$tpl->vars('conf.general.form', $ttvars);
	
	$tvars['vars']['entries'] = $tpl->show('conf.general.form');
	$tvars['vars']['action'] = $lang['bb_media:settings'];
	
	$tpl->template('conf.main', $tpath['conf.main']);
	$tpl->vars('conf.main', $tvars);
	print $tpl->show('conf.main');
}
