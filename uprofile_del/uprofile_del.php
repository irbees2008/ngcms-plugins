<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

LoadPluginLang('uprofile_del', 'main', '', '', '#');

register_plugin_page('uprofile_del','user_id','del_profile');

function del_profile($params) {
	global $tpl, $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $userROW, $lang;
	
	$id_user = isset($params['id'])?abs(intval($params['id'])):abs(intval($_REQUEST['id']));
	
	$urow = $mysql->record("select * from " . uprefix . "_users where id = " . intval($id_user));

	$meta_group = str_replace('%user%', $urow['name'], $lang['uprofile_del']['meta_group']);
	$SYSTEM_FLAGS['info']['title']['group'] = $meta_group;
	$SYSTEM_FLAGS['info']['title']['item'] = $meta_group;
	$SYSTEM_FLAGS['info']['breadcrumbs'] = array(
		array('text' => $meta_group),
	);
			
	if(($id_user == 0) OR $id_user != $urow['id']){
		return msg(array("type" => "error", "info" => $lang['uprofile_del']['error_user_descr']));
	}

	if(!is_array($userROW)){
		return msg(array("type" => "info", "text" => $lang['uprofile_del']['not_logged'], "info" => $lang['uprofile_del']['not_logged_descr']));
	}
	
	if( $id_user == $userROW['id']) {
		
		$user_del = "<form method=\"post\" name=\"userinfo\" id=\"userinfo\" enctype=\"multipart/form-data\">
			<br>Уважаемый пользователь <b>{$urow['name']}</b>!<br>
			Вы собираетесь удалить свой аккаунт с нашего сайта, если Вы создавали посты или добавляли какую либо информацию, то после удаления она сохранится за Вами и не будет удалена.<br>
			<input type=\"checkbox\" name=\"user_delete\" id=\"user_delete\" value=\"\"> <label for=\"user_delete\">Удалить аккаунт</label><br>
			<input type=\"submit\" value=\"{$lang['uprofile_del']['user_ok']}\" name=\"del_ok\" class=\"btn\">
			</form>";					
	} else {
		
		$user_del = str_replace('%user%', $urow['name'], $lang['uprofile_del']['user_del']);
					
	}
	
	if (isset($_REQUEST['del_ok'])){
		
		if (isset($_REQUEST['user_delete'])){
			
			if (($userROW['status'] != 1 AND $urow['status'] == 1 )) return msg(array("type" => "info", "info" => $lang['uprofile_del']['grup_status']));
			
			@include_once root . 'includes/classes/upload.class.php';
			@unlink($avatar_dir . $urow['id'] . '.*');
			
			$mysql->query("DELETE FROM " . uprefix . "_users where id = " . db_squote($urow['id']) . "");
			if (getPluginStatusActive('pm')) {
				$mysql->query("DELETE FROM " . prefix . "_pm WHERE from_id = '{$urow['id']}' AND folder = 'outbox'");
			}
			
			if (isset($_COOKIE['zz_auth']) && $_COOKIE['zz_auth'])
				$mysql->query("delete from " . uprefix . "_users_sessions where authcookie = " . db_squote($_COOKIE['zz_auth']) . "");
			
			@header( "Location: {$_SERVER['REQUEST_URI']}" );

		} else {

			$user_link = checkLinkAvailable('uprofile_del', 'user_id') ?
				generateLink('uprofile_del', 'user_id', array('id' => $id_user)) :
				generateLink('core', 'plugin', array('plugin' => 'uprofile_del', 'handler' => 'user_id'), array('id' => $id_user));
			
			return msg(array('type' => 'error', 'info' => str_replace('%user_link%', $user_link, $lang['uprofile_del']['user_del_er'])));
		}
	}

	
	if (isset($userROW['id']) && (intval($userROW['id']) > 0)) {
		$template['vars']['mainblock'] = $user_del;
	}
 
}

LoadPluginLibrary('uprofile', 'lib');

class DelUserProfileFilter extends p_uprofileFilter {

	function showProfile($userID, $SQLrow, &$tvars) {
		global $lang;

		$user_link = checkLinkAvailable('uprofile_del', 'user_id') ?
			generateLink('uprofile_del', 'user_id', array('id' => $userID)) :
			generateLink('core', 'plugin', array('plugin' => 'uprofile_del', 'handler' => 'user_id'), array('id' => $userID));
			
		$tvars['user']['del_profile'] = '<a href="'.$user_link.'">'.$lang['uprofile_del']['del_profile'].'</a>';
	
	}
}

pluginRegisterFilter('plugin.uprofile', 'uprofile_del', new DelUserProfileFilter);

?>