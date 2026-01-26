<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
LoadPluginLang('uprofile_del', 'main', '', '', '#');
add_act('index', 'del_profile_user');
register_plugin_page('uprofile_del', 'user_id', 'del_profile');
register_plugin_page('uprofile_del', 'res_ok', 'del_profile');
register_plugin_page('uprofile_del', 'del_ok', 'del_profile');
function del_profile_user()
{
	global $config, $userROW, $mysql;
	// Получаем пользователей, которые запросили удаление (user_act = 1)
	foreach ($mysql->select("select * from " . uprefix . "_users where user_act = 1") as $urow) {
		$day_period = pluginGetVariable('uprofile_del', 'day_period') ? pluginGetVariable('uprofile_del', 'day_period') : '31';
		// Проверяем дату запроса на удаление (user_del_date), а не последнюю активность
		$del_date = isset($urow['user_del_date']) && $urow['user_del_date'] > 0 ? $urow['user_del_date'] : $urow['last'];
		$days_since_request = round((time() - $del_date) / 86400);
		// Удаляем только если прошло достаточно дней с момента ЗАПРОСА на удаление
		if ($days_since_request >= $day_period) {
			@include_once root . 'includes/classes/upload.class.php';
			@unlink($avatar_dir . $urow['id'] . '.*');
			$mysql->query("DELETE FROM " . uprefix . "_users where id = " . db_squote($urow['id']) . "");
			if (getPluginStatusActive('pm')) {
				$mysql->query("DELETE FROM " . prefix . "_pm WHERE from_id = '{$urow['id']}' AND folder = 'outbox'");
			}
			if (isset($_COOKIE['zz_auth']) && $_COOKIE['zz_auth'])
				$mysql->query("delete from " . uprefix . "_users_sessions where authcookie = " . db_squote($_COOKIE['zz_auth']) . "");
			//@header( "Location: {$_SERVER['REQUEST_URI']}" );
			$notif_pm = pluginGetVariable('uprofile_del', 'notif_pm') ? pluginGetVariable('uprofile_del', 'notif_pm') : '0';
			// Отправляем PM только если плагин pm активен и таблица существует
			if ($notif_pm == 1 && getPluginStatusActive('pm')) {
				try {
					$time = time() + ($config['date_adjust'] * 60);
					$mysql->query("INSERT INTO " . prefix . "_pm (subject, message, from_id, to_id, date, viewed, folder) values ('Выполнение запроса', 'Пользователь " . $urow['name'] . " был безвозвратно удален.', '" . $urow['id'] . "', '1', '$time', '0', 'inbox')");
					// Обновляем счётчики PM только если колонки существуют
					$mysql->query("UPDATE " . prefix . "_users set pm_all=pm_all+1, pm_unread=pm_unread+1 where id=1");
				} catch (Exception $e) {
					// Игнорируем ошибки PM
				}
			}
		}
	}
}
function del_profile($params)
{
	global $tpl, $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $userROW, $lang;
	// Если не передан ID, используем ID текущего пользователя
	$id_user = isset($params['id']) ? abs(intval($params['id'])) : (isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0);
	// Если ID не передан, используем ID текущего авторизованного пользователя
	if ($id_user == 0 && is_array($userROW) && isset($userROW['id'])) {
		$id_user = intval($userROW['id']);
	}
	$urow = $mysql->record("select * from " . uprefix . "_users where id = " . intval($id_user));
	$meta_group = str_replace('%user%', $urow['name'], $lang['uprofile_del']['meta_group']);
	$SYSTEM_FLAGS['info']['title']['group'] = $meta_group;
	$SYSTEM_FLAGS['info']['title']['item'] = $meta_group;
	$SYSTEM_FLAGS['info']['breadcrumbs'] = array(
		array('text' => $meta_group),
	);
	if (($id_user == 0) or $id_user != $urow['id']) {
		return msg(array("type" => "error", "info" => $lang['uprofile_del']['error_user_descr']));
	}
	if (!is_array($userROW)) {
		return msg(array("type" => "info", "text" => $lang['uprofile_del']['not_logged'], "info" => $lang['uprofile_del']['not_logged_descr']));
	}
	// Пользователь может удалить только свой профиль
	if ($id_user == $userROW['id']) {
		$user_del = "<form method=\"post\" name=\"userinfo\" id=\"userinfo\" enctype=\"multipart/form-data\">
			<br>Уважаемый пользователь <b>{$urow['name']}</b>!<br>
			Вы собираетесь удалить свой аккаунт с нашего сайта, если Вы создавали посты или добавляли какую либо информацию, то после удаления она сохранится за Вами и не будет удалена.<br>
			<input type=\"checkbox\" name=\"user_delete\" id=\"user_delete\" value=\"\"> <label for=\"user_delete\">Удалить аккаунт</label><br>
			<input type=\"submit\" value=\"{$lang['uprofile_del']['user_ok']}\" name=\"del_ok\" class=\"btn\">
			</form>";
		$user_res = "<form method=\"post\" name=\"userinfo\" id=\"userinfo\" enctype=\"multipart/form-data\">
			<br>Уважаемый пользователь <b>{$urow['name']}</b>!<br>
			Вы собираетесь восстановить свой аккаунт на сайта, для этого поставьте галочку.<br>
			<input type=\"checkbox\" name=\"user_restore\" id=\"user_restore\" value=\"\"> <label for=\"user_restore\">Восстановить аккаунт</label><br>
			<input type=\"submit\" value=\"{$lang['uprofile_del']['user_ok']}\" name=\"res_ok\" class=\"btn\">
			</form>";
	} else {
		$user_del = str_replace('%user%', $urow['name'], $lang['uprofile_del']['user_del']);
		$user_res = str_replace('%user%', $urow['name'], $lang['uprofile_del']['user_res']);
	}
	if (isset($_REQUEST['res_ok'])) {
		if (isset($_REQUEST['user_restore'])) {
			if (($userROW['status'] != 1 and $urow['status'] == 1)) return msg(array("type" => "info", "info" => $lang['uprofile_del']['grup_status']));
			// Снимаем флаг удаления И очищаем дату запроса
			$mysql->query("UPDATE " . uprefix . "_users SET user_act='0', user_del_date='0' WHERE id = '{$urow['id']}'");
			$notif_pm = pluginGetVariable('uprofile_del', 'notif_pm') ? pluginGetVariable('uprofile_del', 'notif_pm') : '0';
			// Отправляем PM только если плагин pm активен
			if ($notif_pm == 1 && getPluginStatusActive('pm')) {
				try {
					$time = time() + ($config['date_adjust'] * 60);
					$date_reg = LangDate("j Q Y - H:i:s", $urow['reg']);
					$date_last = LangDate("j Q Y - H:i:s", $urow['last']);
					$msg_text = "Пользователь " . $urow['name'] . " сделал запрос на восстановление своего профиля.\n";
					$msg_text .= "Последняя активность: " . $date_last . "\n";
					$msg_text .= "Дата регистрации: " . $date_reg;
					$mysql->query("INSERT INTO " . prefix . "_pm (subject, message, from_id, to_id, date, viewed, folder) values ('Запрос на восстановление профиля', '" . $mysql->safesql($msg_text) . "', '" . $urow['id'] . "', '1', '$time', '0', 'inbox')");
					$mysql->query("UPDATE " . prefix . "_users set pm_all=pm_all+1, pm_unread=pm_unread+1  where id=1");
				} catch (Exception $e) {
					// Игнорируем ошибки PM
				}
			}
			return msg(array('type' => 'info', 'text' => str_replace(array('%user%', '%site%'), array($urow['name'], $config['home_url']), $lang['uprofile_del']['user_days_res'])));
		} else {
			$user_link = checkLinkAvailable('uprofile_del', 'user_id') ?
				generateLink('uprofile_del', 'user_id', array('id' => $id_user)) :
				generateLink('core', 'plugin', array('plugin' => 'uprofile_del', 'handler' => 'user_id'), array('id' => $id_user));
			return msg(array('type' => 'error', 'info' => str_replace('%user_link%', $user_link, $lang['uprofile_del']['user_res_er'])));
		}
	}
	if (isset($_REQUEST['del_ok'])) {
		if (isset($_REQUEST['user_delete'])) {
			if (($userROW['status'] != 1 and $urow['status'] == 1)) return msg(array("type" => "info", "info" => $lang['uprofile_del']['grup_status']));
			// Устанавливаем флаг на удаление И сохраняем дату запроса
			$current_time = time();
			$mysql->query("UPDATE " . uprefix . "_users SET user_act='1', user_del_date='" . $current_time . "' WHERE id = '{$urow['id']}'");
			$notif_pm = pluginGetVariable('uprofile_del', 'notif_pm') ? pluginGetVariable('uprofile_del', 'notif_pm') : '0';
			$day_period = pluginGetVariable('uprofile_del', 'day_period') ? pluginGetVariable('uprofile_del', 'day_period') : '31';
			$site_exists = floor($day_period);
			$days = '' . $day_period . ' ' . Padeg($site_exists, $lang['uprofile_del']['days']) . '';
			// Отправляем PM только если плагин pm активен
			if ($notif_pm == 1 && getPluginStatusActive('pm')) {
				try {
					$time = time() + ($config['date_adjust'] * 60);
					$date_reg = LangDate("j Q Y - H:i:s", $urow['reg']);
					$date_last = LangDate("j Q Y - H:i:s", $urow['last']);
					$profile_url = $config['home_url'] . '/users/' . $urow['name'] . '.html';
					$msg_text = "Уважаемый " . $urow['name'] . "!\n\n";
					$msg_text .= "Вы создали запрос на удаление своего профиля.\n";
					$msg_text .= "В течение " . $days . " данный аккаунт будет удален с сайта " . $config['home_url'] . ".\n\n";
					$msg_text .= "Если в течение данного периода Вы вернетесь на сайт под своим логином, то данное действие аннулируется.\n\n";
					$msg_text .= "Если не хотите удалять свой профиль, перейдите по ссылке " . $profile_url . " и воспользуйтесь кнопкой восстановления.\n\n";
					$msg_text .= "---\n";
					$msg_text .= "Дата регистрации: " . $date_reg . "\n";
					$msg_text .= "Последняя активность: " . $date_last;
					$mysql->query("INSERT INTO " . prefix . "_pm (subject, message, from_id, to_id, date, viewed, folder) values ('Запрос на удаление профиля', '" . $mysql->safesql($msg_text) . "', '" . $urow['id'] . "', '1', '$time', '0', 'inbox')");
					$mysql->query("UPDATE " . prefix . "_users set pm_all=pm_all+1, pm_unread=pm_unread+1  where id=1");
				} catch (Exception $e) {
					// Игнорируем ошибки PM
				}
			}
			$url = checkLinkAvailable('uprofile', 'show') ?
				generateLink('uprofile', 'show', array('name' => $urow['name'], 'id' => $urow['id'])) :
				generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $urow['name'], 'id' => $urow['id']));
			return msg(array('type' => 'info', 'text' => str_replace(array('%days%', '%user%', '%site%', '%url%'), array($days, $urow['name'], $config['home_url'], $url), $lang['uprofile_del']['user_days_del'])));
		} else {
			$user_link = checkLinkAvailable('uprofile_del', 'user_id') ?
				generateLink('uprofile_del', 'user_id', array('id' => $id_user)) :
				generateLink('core', 'plugin', array('plugin' => 'uprofile_del', 'handler' => 'user_id'), array('id' => $id_user));
			return msg(array('type' => 'error', 'info' => str_replace('%user_link%', $user_link, $lang['uprofile_del']['user_del_er'])));
		}
	}
	if (isset($userROW['id']) && (intval($userROW['id']) > 0)) {
		if ($urow['user_act'] == 1) {
			$template['vars']['mainblock'] = $user_res;
		} else {
			$template['vars']['mainblock'] = $user_del;
		}
	}
}
LoadPluginLibrary('uprofile', 'lib');
class DelUserProfileFilter extends p_uprofileFilter
{
	function showProfile($userID, $SQLrow, &$tvars)
	{
		global $lang, $userROW;
		$user_link = checkLinkAvailable('uprofile_del', 'user_id') ?
			generateLink('uprofile_del', 'user_id', array('id' => $userID)) :
			generateLink('core', 'plugin', array('plugin' => 'uprofile_del', 'handler' => 'user_id'), array('id' => $userID));
		if ($SQLrow['user_act'] == 1) {
			$tvars['user']['del_profile'] = '<a href="' . $user_link . '">' . $lang['uprofile_del']['res_profile'] . '</a>';
		} else {
			$tvars['user']['del_profile'] = '<a href="' . $user_link . '">' . $lang['uprofile_del']['del_profile'] . '</a>';
		}
	}
}
pluginRegisterFilter('plugin.uprofile', 'uprofile_del', new DelUserProfileFilter);
