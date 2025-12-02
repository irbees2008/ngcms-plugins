<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
//
// Регистрация модуля аутентификации
//

global $AUTH_METHOD;

class auth_basic extends CoreAuthPlugin
{
	// Авторизация пользователя
	public function login($auto_scan = 1, $username = '', $password = '')
	{
		global $mysql, $config;
		$username = $username ?: (isset($_POST['username']) ? trim($_POST['username']) : '');
		$password = $password ?: (isset($_POST['password']) ? $_POST['password'] : '');
		if ($username == '' || $password == '') {
			return 'ERR:NOT_ENTERED';
		}
		// Вытаскиваем пользователя (регистр логина игнорируем)
		$row = $mysql->record('SELECT * FROM ' . uprefix . '_users WHERE lower(name) = ' . db_squote(strtolower($username)) . ' LIMIT 1');
		if (!is_array($row)) {
			return 'ERR:NOT_ENTERED';
		}
		// Требуется активация?
		if (!empty($row['activation'])) {
			return 'ERR:NEED.ACTIVATE';
		}
		// Проверка пароля. Все типы регистрации сохраняют пароль либо сразу, либо после активации.
		if ($row['pass'] != EncodePassword($password)) {
			return 'ERR:NOT_ENTERED';
		}
		return $row;
	}

	// Сохранение авторизации (cookie + запись в сессии пользователей)
	public function save_auth($dbrow)
	{
		global $mysql, $ip, $config;
		if (!is_array($dbrow) || empty($dbrow['id'])) {
			return false;
		}
		$auth_cookie = substr(md5(uniqid(mt_rand(), true)), 0, 16);
		// Удаляем старую сессию если была
		if (isset($_COOKIE['zz_auth']) && $_COOKIE['zz_auth']) {
			$mysql->query('DELETE FROM ' . uprefix . '_users_sessions WHERE authcookie = ' . db_squote($_COOKIE['zz_auth']) . ' LIMIT 1');
		}
		// Пишем новую сессию
		$mysql->query('INSERT INTO ' . uprefix . '_users_sessions (userID, ip, last, authcookie) VALUES (' . db_squote($dbrow['id']) . ', ' . db_squote($ip) . ', ' . db_squote(time()) . ', ' . db_squote($auth_cookie) . ')');
		// Куки ("remember" даёт длинный срок хранения)
		@setcookie('zz_auth', $auth_cookie, ($config['remember'] ? (time() + 3600 * 24 * 365) : 0), '/');
		// На всякий случай кладём в сессию ID пользователя
		$_SESSION['auth_user_id'] = $dbrow['id'];
		return true;
	}

	// Проверка авторизации (по cookie или сессии)
	public function check_auth()
	{
		global $mysql;
		$auth_cookie = isset($_COOKIE['zz_auth']) ? $_COOKIE['zz_auth'] : '';
		$userID = null;
		if ($auth_cookie) {
			$srow = $mysql->record('SELECT * FROM ' . uprefix . '_users_sessions WHERE authcookie = ' . db_squote($auth_cookie) . ' LIMIT 1');
			if (is_array($srow)) {
				$userID = intval($srow['userID']);
				// Обновляем таймстамп последней активности
				$mysql->query('UPDATE ' . uprefix . '_users_sessions SET last = ' . db_squote(time()) . ' WHERE authcookie = ' . db_squote($auth_cookie) . ' LIMIT 1');
			}
		}
		if (!$userID && isset($_SESSION['auth_user_id'])) {
			$userID = intval($_SESSION['auth_user_id']);
		}
		if (!$userID) {
			return false;
		}
		$row = $mysql->record('SELECT * FROM ' . uprefix . '_users WHERE id = ' . db_squote($userID) . ' LIMIT 1');
		if (!is_array($row) || !empty($row['activation'])) {
			return false;
		}
		$row['authcookie'] = $auth_cookie;
		return $row;
	}

	// Сброс авторизации
	public function drop_auth()
	{
		global $mysql, $userROW;
		if (isset($_COOKIE['zz_auth']) && $_COOKIE['zz_auth']) {
			$mysql->query('DELETE FROM ' . uprefix . '_users_sessions WHERE authcookie = ' . db_squote($_COOKIE['zz_auth']) . ' LIMIT 1');
			@setcookie('zz_auth', '', time() - 3600 * 24 * 365, '/');
		}
		unset($_SESSION['auth_user_id']);
		return true;
	}
	// Вернуть массив параметров для формы регистрации
	function get_reg_params()
	{
		global $config, $lang;
		$params = array();
		LoadPluginLang('auth_basic', 'auth', '', 'auth');

		array_push($params, array(
			'name'  => 'login',
			'id'    => 'reg_login',
			'title' => $lang['auth_login'],
			'descr' => $lang['auth_login_descr'],
			'type'  => 'input'
		));

		if ($config['register_type'] >= 3) {
			array_push($params, array(
				'id'    => 'reg_password',
				'name'  => 'password',
				'title' => $lang['auth_pass'],
				'descr' => $lang['auth_pass_descr'],
				'type'  => 'password'
			));
			array_push($params, array(
				'id'    => 'reg_password2',
				'name'  => 'password2',
				'title' => $lang['auth_pass2'],
				'descr' => $lang['auth_pass2_descr'],
				'type'  => 'password'
			));
		}

		array_push($params, array(
			'name'  => 'email',
			'id'    => 'reg_email',
			'title' => $lang['auth_email'],
			'descr' => $lang['auth_email_descr'],
			'type'  => 'input'
		));

		return $params;
	}

	// Провести регистрацию
	// params = параметры полученные из get_reg_params()
	// values = значения для вышеуказанных параметров
	// msg    = сообщение об ошибке
	// Возвращаемые значения:
	// 0 - ошибка
	// 1 - всё ok (требуется дополнительное действие)
	// >1 - ID созданного пользователя
	function register(&$params, $values, &$msg)
	{
		global $config, $mysql, $lang, $tpl, $UGROUP;
		LoadPluginLang('auth_basic', 'auth', '', 'auth');

		$error = 0;
		$userid = 0;

		$values['login'] = trim($values['login']);
		// Проверки логина
		if (strlen($values['login']) < 3) {
			$msg = $lang['auth_login_short'];
			return 0;
		}
		$csError = false;
		switch (pluginGetVariable('auth_basic', 'regcharset')) {
			case 0:
				if (!preg_match('#^[A-Za-z0-9\.\_\-]+$#s', $values['login'])) {
					$csError = true;
				}
				break;
			case 1:
				if (!preg_match('#^[А-Яа-яёЁ0-9\.\_\-]+$#s', $values['login'])) {
					$csError = true;
				}
				break;
			case 2:
				if (!preg_match('#^[А-Яа-яёЁA-Za-z0-9\.\_\-]+$#s', $values['login'])) {
					$csError = true;
				}
				break;
			case 3:
				if (!preg_match('#^[\x21-\x7e\xc0-\xffёЁ]+$#s', $values['login'])) {
					$csError = true;
				}
				break;
			case 4:
				break;
		}
		if (preg_match('/[&<>\"' . "'" . ']/', $values['login']) || $csError) {
			$msg = $lang['auth_login_html'];
			return 0;
		}

		// Пароль
		if ($config['register_type'] >= 3) {
			if (strlen($values['password']) < 3) {
				$msg = $lang['auth_pass_short'];
				return 0;
			}
			if ($values['password'] != $values['password2']) {
				$msg = $lang['auth_pass_diff'];
				return 0;
			}
		}

		// Email
		if ((strlen($values['email']) > 70) || (!preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $values['email']))) {
			$msg = $lang['auth_email_wrong'];
			return 0;
		}

		$row = $mysql->record("select * from " . uprefix . "_users where lower(name)=" . db_squote(strtolower($values['login'])) . " or mail=" . db_squote($values['email']));
		if (is_array($row)) {
			if (strtolower($row['mail']) == strtolower($values['email'])) {
				$msg = $lang['auth_email_dup'];
				return 0;
			}
			$msg = $lang['auth_login_dup'];
			return 0;
		}

		// Время/группа
		$add_time = time() + ($config['date_adjust'] * 60);
		$regGroup = intval(pluginGetVariable('auth_basic', 'regstatus'));
		if (!isset($UGROUP[$regGroup])) {
			$regGroup = 4;
		}

		// Подготовка динамического списка колонок по фактической схеме БД
		$baseColumns = array('name', 'pass', 'mail', 'status', 'reg', 'last');
		$activationNeeded = in_array(intval($config['register_type']), array(2, 4), true);
		if ($activationNeeded) {
			$baseColumns[] = 'activation';
		}
		$existingCols = array();
		foreach ($mysql->select('SHOW COLUMNS FROM ' . uprefix . '_users') as $c) {
			$existingCols[$c['Field']] = 1;
		}
		$finalCols = array();
		foreach ($baseColumns as $colName) {
			if (isset($existingCols[$colName])) {
				$finalCols[] = $colName;
			}
		}

		switch ($config['register_type']) {
			// 0 - Мгновенная [автогенерация пароля, без email нотификации]
			case 0:
				$newpassword = MakeRandomPassword();
				$insertValues = array(
					'name' => db_squote($values['login']),
					'pass' => db_squote(EncodePassword($newpassword)),
					'mail' => db_squote($values['email']),
					'status' => $regGroup,
					'reg' => db_squote($add_time),
					'last' => db_squote($add_time),
				);
				$cols = array();
				$vals = array();
				foreach ($finalCols as $c) {
					if (isset($insertValues[$c])) {
						$cols[] = $c;
						$vals[] = $insertValues[$c];
					}
				}
				$sql = 'INSERT INTO ' . uprefix . '_users (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
				@file_put_contents(root . '/engine/trash/register_error.log', date('c') . "\tTYPE0_PRE\tSQL=" . $sql . "\n", FILE_APPEND);
				$mysql->query($sql);
				$userid = $mysql->lastid('users');
				// Перенос уведомления в flash, чтобы показать после редиректа
				$_SESSION['flash_notify'] = array(
					'text' => $lang['msgo_registered'],
					'info' => str_replace(array('{login}', '{password}'), array($values['login'], $newpassword), $lang['auth_reg.success0']),
					'type' => 'info'
				);
				break;

			// 1 - Простая [автогенерация пароля, с email нотификацией]
			case 1:
				$newpassword = MakeRandomPassword();
				$insertValues = array(
					'name' => db_squote($values['login']),
					'pass' => db_squote(EncodePassword($newpassword)),
					'mail' => db_squote($values['email']),
					'status' => $regGroup,
					'reg' => db_squote($add_time),
					'last' => db_squote($add_time),
				);
				$cols = array();
				$vals = array();
				foreach ($finalCols as $c) {
					if (isset($insertValues[$c])) {
						$cols[] = $c;
						$vals[] = $insertValues[$c];
					}
				}
				$sql = 'INSERT INTO ' . uprefix . '_users (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
				@file_put_contents(root . '/engine/trash/register_error.log', date('c') . "\tTYPE1_PRE\tSQL=" . $sql . "\n", FILE_APPEND);
				$mysql->query($sql);
				$userid = $mysql->lastid('users');
				$tvars['vars'] = array('login' => $values['login'], 'home' => home, 'password' => $newpassword);
				$tvars['regx'] = array('#\[activation\].+?\[\/activation]#is' => '');
				$tpl->template('register', GetPluginLangDir('auth_basic'));
				$tpl->vars('register', $tvars);
				$msg = $tpl->show('register');
				sendEmailMessage($values['email'], $lang['letter_title'], $msg, false, false, 'html');
				$_SESSION['flash_notify'] = array(
					'text' => $lang['msgo_registered'],
					'info' => str_replace(array('{login}', '{password}', '{email}'), array($values['login'], $newpassword, $values['email']), $lang['auth_reg.success1']),
					'type' => 'info'
				);
				break;

			// 2 - С подтверждением [автогенерация пароля, пароль отправляется на email]
			case 2:
				$newpassword = MakeRandomPassword();
				$actcode = MakeRandomPassword();
				$insertValues = array(
					'name' => db_squote($values['login']),
					'pass' => db_squote(EncodePassword($newpassword)),
					'mail' => db_squote($values['email']),
					'status' => $regGroup,
					'reg' => db_squote($add_time),
					'last' => db_squote($add_time),
					'activation' => db_squote($actcode),
				);
				$cols = array();
				$vals = array();
				foreach ($finalCols as $c) {
					if (isset($insertValues[$c])) {
						$cols[] = $c;
						$vals[] = $insertValues[$c];
					}
				}
				$sql = 'INSERT INTO ' . uprefix . '_users (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
				@file_put_contents(root . '/engine/trash/register_error.log', date('c') . "\tTYPE2_PRE\tSQL=" . $sql . "\n", FILE_APPEND);
				$mysql->query($sql);
				$userid = $mysql->lastid('users');
				$link = generatePluginLink('core', 'activation', array('userid' => $userid, 'code' => $actcode), array(), false, true);
				$tvars['vars'] = array('login' => $values['login'], 'home' => home, 'password' => $newpassword, 'activate_url' => $link);
				$tvars['regx'] = array('#\[activation\](.+?)\[\/activation]#is' => '$1');
				$tpl->template('register', GetPluginLangDir('auth_basic'));
				$tpl->vars('register', $tvars);
				$msg = $tpl->show('register');
				sendEmailMessage($values['email'], $lang['letter_title'], $msg, 'html');
				$_SESSION['flash_notify'] = array(
					'text' => $lang['msgo_registered'],
					'info' => str_replace(array('{login}', '{password}', '{email}'), array($values['login'], $newpassword, $values['email']), $lang['auth_reg.success2']),
					'type' => 'info'
				);
				break;

			// 3 - Ручная с нотификацией [ручная генерация пароля, email нотификация]
			case 3:
				$insertValues = array(
					'name' => db_squote($values['login']),
					'pass' => db_squote(EncodePassword($values['password'])),
					'mail' => db_squote($values['email']),
					'status' => $regGroup,
					'reg' => db_squote($add_time),
					'last' => db_squote($add_time),
				);
				$cols = array();
				$vals = array();
				foreach ($finalCols as $c) {
					if (isset($insertValues[$c])) {
						$cols[] = $c;
						$vals[] = $insertValues[$c];
					}
				}
				$sql = 'INSERT INTO ' . uprefix . '_users (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
				@file_put_contents(root . '/engine/trash/register_error.log', date('c') . "\tTYPE3_PRE\tSQL=" . $sql . "\n", FILE_APPEND);
				$mysql->query($sql);
				$userid = $mysql->lastid('users');
				$tvars['vars'] = array('login' => $values['login'], 'home' => home, 'password' => $values['password']);
				$tvars['regx'] = array('#\[activation\].+?\[\/activation]#is' => '');
				$tpl->template('register', GetPluginLangDir('auth_basic'));
				$tpl->vars('register', $tvars);
				$msg = $tpl->show('register');
				sendEmailMessage($values['email'], $lang['letter_title'], $msg, 'html');
				$_SESSION['flash_notify'] = array(
					'text' => $lang['msgo_registered'],
					'info' => str_replace(array('{login}', '{password}', '{email}'), array($values['login'], $values['password'], $values['email']), $lang['auth_reg.success3']),
					'type' => 'info'
				);
				break;

			// 4 - Ручная с подтверждением [ручная генерация пароля, подтверждение email]
			case 4:
				$actcode = MakeRandomPassword();
				$insertValues = array(
					'name' => db_squote($values['login']),
					'pass' => db_squote(EncodePassword($values['password'])),
					'mail' => db_squote($values['email']),
					'status' => $regGroup,
					'reg' => db_squote($add_time),
					'last' => db_squote($add_time),
					'activation' => db_squote($actcode),
				);
				$cols = array();
				$vals = array();
				foreach ($finalCols as $c) {
					if (isset($insertValues[$c])) {
						$cols[] = $c;
						$vals[] = $insertValues[$c];
					}
				}
				$sql = 'INSERT INTO ' . uprefix . '_users (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
				@file_put_contents(root . '/engine/trash/register_error.log', date('c') . "\tTYPE4_PRE\tSQL=" . $sql . "\n", FILE_APPEND);
				$mysql->query($sql);
				$userid = $mysql->lastid('users');
				$link = generatePluginLink('core', 'activation', array('userid' => $userid, 'code' => $actcode), array(), false, true);
				$tvars['vars'] = array('login' => $values['login'], 'home' => home, 'password' => $values['password'], 'activate_url' => $link);
				$tvars['regx'] = array('#\[activation\](.+?)\[\/activation]#is' => '$1');
				$tpl->template('register', GetPluginLangDir('auth_basic'));
				$tpl->vars('register', $tvars);
				$msg = $tpl->show('register');
				sendEmailMessage($values['email'], $lang['letter_title'], $msg, 'html');
				$_SESSION['flash_notify'] = array(
					'text' => $lang['msgo_registered'],
					'info' => str_replace(array('{login}', '{password}', '{email}'), array($values['login'], $values['password'], $values['email']), $lang['auth_reg.success4']),
					'type' => 'info'
				);
		}

		return ($userid > 0) ? $userid : 1;
	}

	// Вернуть массив параметров, необходимых для восстановления пароля
	function get_restorepw_params()
	{
		global $config, $lang;
		$params = array();
		LoadPluginLang('auth_basic', 'auth', '', 'auth');
		$mode = pluginGetVariable('auth_basic', 'restorepw');
		if (!$mode) {
			return false;
		}
		array_push($params, array('text' => $lang['auth_restore_' . $mode]));
		if ($mode != 'email') {
			array_push($params, array('name' => 'login', 'title' => $lang['auth_login'], 'type' => 'input'));
		}
		if ($mode != 'login') {
			array_push($params, array('name' => 'email', 'title' => $lang['auth_email'], 'type' => 'input'));
		}
		return $params;
	}

	// Восстановить пароль
	function restorepw(&$params, $values, &$msg)
	{
		global $config, $mysql, $lang, $tpl;
		$error = 0;
		$values['login'] = trim($values['login']);
		$values['email'] = trim($values['email']);
		LoadPluginLang('auth_basic', 'auth', '', 'auth');
		$mode = pluginGetVariable('auth_basic', 'restorepw');
		if (!$mode) {
			$msg = $lang['auth_norestore'];
			return 0;
		}
		$px = array();
		if ($mode != 'email') {
			if (!$values['login']) {
				$msg = $lang['auth_login_require'];
				return 0;
			}
			array_push($px, 'name = ' . db_squote($values['login']));
		}
		if ($mode != 'login') {
			if (!$values['email']) {
				$msg = $lang['auth_email_require'];
				return 0;
			}
			array_push($px, 'mail = ' . db_squote($values['email']));
		}
		$query = 'select * from ' . uprefix . '_users where ' . implode(' and ', $px);
		$row = $mysql->record($query);
		if (is_array($row)) {
			$newpassword = MakeRandomPassword();
			$mysql->query('UPDATE ' . uprefix . '_users SET newpw=' . db_squote(EncodePassword($newpassword)) . ' WHERE id=' . $row['id']);
			$tvars['vars'] = array(
				'login' => $row['name'],
				'home'  => home,
				'newpw' => $newpassword
			);
			$tvars['vars']['pwurl'] = generatePluginLink('core', 'lostpassword', array('userid' => $row['id'], 'code' => EncodePassword($newpassword)), array(), false, true);
			$tpl->template('restorepw', GetPluginLangDir('auth_basic'));
			$tpl->vars('restorepw', $tvars);
			sendEmailMessage($row['mail'], $lang['auth_mail_subj'], $tpl->show('restorepw'));
			msg(array('text' => $lang['msgo_sent']));
			return 1;
		} else {
			$msg = $lang['auth_nouser'];
			return 0;
		}
	}

	// AJAX: онлайн-проверка валидности параметров регистрации
	function onlineCheckRegistration($params)
	{
		global $config, $mysql;
		$results = array();
		if (isset($params['login'])) {
			$params['login'] =  trim($params['login']);
			if (strlen($params['login']) < 3) {
				$results['login'] = 2;
				goto endLoginCheck;
			}
			$csError = false;
			switch (pluginGetVariable('auth_basic', 'regcharset')) {
				case 0:
					if (!preg_match('#^[A-Za-z0-9\.\_\-]+$#s', $params['login'])) {
						$csError = true;
					}
					break;
				case 1:
					if (!preg_match('#^[А-Яа-яёЁ0-9\.\_\-]+$#s', $params['login'])) {
						$csError = true;
					}
					break;
				case 2:
					if (!preg_match('#^[А-Яа-яёЁA-Za-z0-9\.\_\-]+$#s', $params['login'])) {
						$csError = true;
					}
					break;
				case 3:
					if (!preg_match('#^[\x21-\x7e\xc0-\xffёЁ]+$#s', $params['login'])) {
						$csError = true;
					}
					break;
				case 4:
					break;
			}
			if (preg_match('/[&<>\"' . "'" . ']/', $params['login']) || $csError) {
				$results['login'] = 3;
				goto endLoginCheck;
			}
			$row = $mysql->record('select * from ' . uprefix . '_users where lower(name)=' . db_squote(strtolower($params['login'])));
			if (is_array($row)) {
				$results['login'] = 1;
				goto endLoginCheck;
			}
			$results['login'] = 100;
		}
		endLoginCheck:
		if (isset($params['email'])) {
			$params['email'] = trim($params['email']);
			if (strlen($params['email']) > 70) {
				$results['email'] = 2;
				goto endEmailCheck;
			}
			if (!preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $params['email'])) {
				$results['email'] = 3;
				goto endEmailCheck;
			}
			$row = $mysql->record('select * from ' . uprefix . '_users where lower(mail)=' . db_squote($params['email']));
			if (is_array($row)) {
				$results['email'] = 1;
				goto endEmailCheck;
			}
			$results['email'] = 100;
		}
		endEmailCheck:
		return $results;
	}

	// Подтверждение восстановления пароля
	function confirm_restorepw(&$msg, $reqid = null, $reqsecret = null)
	{
		global $config, $mysql, $lang, $tpl;
		LoadPluginLang('auth_basic', 'auth', '', 'auth');
		$row = $mysql->record('select * from ' . uprefix . '_users where id = ' . db_squote($reqid));
		if (is_array($row)) {
			if ($reqsecret == $row['newpw']) {
				$msg = $lang['auth_newpw_ok'];
				$mysql->query('update ' . uprefix . '_users set pass=newpw where id = ' . db_squote($reqid));
				return 1;
			}
		}
		$msg = $lang['auth_newpw_fail'];
		return 0;
	}
}

// Регистрация метода аутентификации
$AUTH_METHOD['basic'] = new auth_basic;
$AUTH_CAPABILITIES['basic'] = array('login' => '1', 'db' => '1');
if (pluginGetVariable('auth_basic', 'en_dbprefix')) {
	$config['uprefix'] = pluginGetVariable('auth_basic', 'dbprefix');
}
