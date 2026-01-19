<?php
# protect against hack attempts
if (!defined('NGCMS')) die('Galaxy in danger');

// Modified with ng-helpers v0.2.0 functions (2026)
// - Added email validation
// - Added secure random string generation
// - Added logging support

// Import ng-helpers functions
use function Plugins\{validate_email, random_string, logger, get_ip};

# preload required libraries
//loadPluginLibrary('uprofile', 'lib');
//loadPluginLibrary('comments', 'lib');
loadPluginLibrary('uprofile', 'lib');
register_plugin_page('auth_social', '', 'socialAuth', 0);
// Path-based callbacks per provider (VK requires no query params)
// VK ID (PKCE) callback route
register_plugin_page('auth_social', 'vkid', 'socialAuth', 0);
register_plugin_page('auth_social', 'yandex', 'socialAuth', 0);
register_plugin_page('auth_social', 'google', 'socialAuth', 0);
register_plugin_page('auth_social', 'facebook', 'socialAuth', 0);
register_plugin_page('auth_social', 'github', 'socialAuth', 0);
add_act('usermenu', 'auth_social_links');
//register_plugin_page('auth_social', 'register' , 'socialRegister', 0);
//register_plugin_page('auth_social', 'delete' , 'loginzaDelete', 0);
function socialAuth($params = [])
{
	global $config, $template, $tpl, $mysql, $userROW, $AUTH_METHOD, $CurrentHandler;
	require_once ($_SERVER['DOCUMENT_ROOT']) . '/engine/plugins/auth_social/lib/SocialAuther/autoload.php';
	// Log request with absolute path
	// $logFile = $_SERVER['DOCUMENT_ROOT'] . '/engine/plugins/auth_social/log.txt';
	$entry = '[' . date('Y-m-d H:i:s') . '] socialAuth handler=' . ($CurrentHandler['handlerName'] ?? 'none') . ' uri=' . ($_SERVER['REQUEST_URI'] ?? '') . ' GET=' . json_encode($_GET, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
	// @file_put_contents($logFile, $entry, FILE_APPEND);

	/* DEBUG: Output to screen
	if (empty($_GET['code']) && empty($_GET['state'])) {
		echo '<div style="background: yellow; padding: 20px; margin: 20px; border: 2px solid red;">';
		echo '<h2>socialAuth() executed</h2>';
		echo '<p>Handler: ' . htmlspecialchars($CurrentHandler['handlerName'] ?? 'none') . '</p>';
		echo '<p>URI: ' . htmlspecialchars($_SERVER['REQUEST_URI']) . '</p>';
		echo '<p>Log file: ' . htmlspecialchars($logFile) . '</p>';
		echo '<p>GET: ' . htmlspecialchars(print_r($_GET, true)) . '</p>';
		echo '</div>';
	}
	*/

	$adapterConfigs = array(
		// VK ID uses PKCE; handled by Adapter\Vkid
		'vkid'          => array(
			'client_id'     => pluginGetVariable('auth_social', 'vkid_client_id'),
			'client_secret' => pluginGetVariable('auth_social', 'vkid_client_secret'),
			'redirect_uri'  => home . "/plugin/auth_social/vkid/",
			'scope'         => (pluginGetVariable('auth_social', 'vkid_scope') ?: 'email'),
		),
		'yandex'        => array(
			'client_id'     => pluginGetVariable('auth_social', 'yandex_client_id'),
			'client_secret' => pluginGetVariable('auth_social', 'yandex_client_secret'),
			'redirect_uri'  => home . "/plugin/auth_social/?provider=yandex"
		),
		'google'        => array(
			'client_id'     => pluginGetVariable('auth_social', 'google_client_id'),
			'client_secret' => pluginGetVariable('auth_social', 'google_client_secret'),
			'redirect_uri'  => home . "/plugin/auth_social/?provider=google"
		),
		'facebook'      => array(
			'client_id'     => pluginGetVariable('auth_social', 'facebook_client_id'),
			'client_secret' => pluginGetVariable('auth_social', 'facebook_client_secret'),
			'redirect_uri'  => home . "/plugin/auth_social/?provider=facebook"
		),
		'github'        => array(
			'client_id'     => pluginGetVariable('auth_social', 'github_client_id'),
			'client_secret' => pluginGetVariable('auth_social', 'github_client_secret'),
			'redirect_uri'  => home . "/plugin/auth_social/?provider=github"
		),
	);
	$adapters = array();
	foreach ($adapterConfigs as $adapter => $settings) {
		$class = 'SocialAuther\\Adapter\\' . ucfirst($adapter);
		if (!class_exists($class)) {
			continue;
		}
		$adapters[$adapter] = new $class($settings);
	}
	// Determine provider: prefer route handler name, fallback to query param
	$provider = null;
	if (isset($CurrentHandler['handlerName']) && array_key_exists($CurrentHandler['handlerName'], $adapters)) {
		$provider = $CurrentHandler['handlerName'];
	} elseif (isset($_GET['provider']) && array_key_exists($_GET['provider'], $adapters)) {
		$provider = $_GET['provider'];
	}
	if ($provider) {
		// Log provider detection
		// @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] provider=$provider config=" . json_encode($adapterConfigs[$provider], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
		// If no code in GET - redirect to OAuth provider
		if (!isset($_GET['code']) && !isset($_GET['oauth_token'])) {
			if ($provider === 'vkid') {
				// VK ID redirect with PKCE
				if (session_status() === PHP_SESSION_NONE) {
					@session_start();
				}
				$state = random_string(32);
				$codeVerifier = random_string(64);
				$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
				$_SESSION['vkid_state'] = $state;
				$_SESSION['vkid_code_verifier'] = $codeVerifier;

				logger('auth_social', 'VK ID OAuth redirect initiated, IP: ' . get_ip());

				$params = [
					'response_type' => 'code',
					'client_id' => $adapterConfigs[$provider]['client_id'],
					'redirect_uri' => $adapterConfigs[$provider]['redirect_uri'],
					'state' => $state,
					'code_challenge' => $codeChallenge,
					'code_challenge_method' => 'S256',
					'scope' => $adapterConfigs[$provider]['scope']
				];
				$authUrl = 'https://id.vk.com/authorize?' . http_build_query($params);
				// @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] redirecting to VK: state=$state, $authUrl\n", FILE_APPEND);
				header('Location: ' . $authUrl);
				exit;
			}
		}
		$auther = new SocialAuther\SocialAuther($adapters[$provider]);
		$authResult = $auther->authenticate();
		// @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] authenticate result=" . ($authResult ? 'true' : 'false') . "\n", FILE_APPEND);
		if ($authResult) {
			// Log user data
			$userData = [
				'provider' => $auther->getProvider(),
				'social_id' => $auther->getSocialId(),
				'name' => $auther->getName(),
				'email' => $auther->getEmail(),
				'social_page' => $auther->getSocialPage()
			];

			// Валидация email
			$email = $auther->getEmail();
			if ($email && !validate_email($email)) {
				logger('auth_social', 'Invalid email from ' . $auther->getProvider() . ': ' . $email . ', IP: ' . get_ip(), 'warning');
				die('Invalid email address received from social network');
			}

			logger('auth_social', 'Successful OAuth authentication: ' . $auther->getProvider() . ' user ' . $auther->getName() . ' (' . $email . '), IP: ' . get_ip());

			// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] user data=" . json_encode($userData, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
			// Check existing user
			$checkQuery = "SELECT * FROM " . uprefix . "_users WHERE (`provider` = '" . $auther->getProvider() . "' AND `social_id` = '" . $auther->getSocialId() . "') OR `social_page` = " . db_squote($auther->getSocialPage()) . " LIMIT 1";
			// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] checking user: " . $checkQuery . "\n", FILE_APPEND);
			$record = $mysql->record(
				$checkQuery
			);
			// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] existing user found=" . (is_array($record) ? 'yes id=' . ($record['id'] ?? 'null') : 'no') . "\n", FILE_APPEND);
			if (!$record) {
				// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] creating new user\n", FILE_APPEND);
				logger('auth_social', 'Creating new user from ' . $auther->getProvider() . ': ' . $auther->getName() . ' (' . $auther->getEmail() . '), IP: ' . get_ip());
				try {
					$birthday_value = $auther->getBirthday();
					// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] birthday raw=" . json_encode($birthday_value) . "\n", FILE_APPEND);
					$values = array(
						EncodePassword(MakeRandomPassword()),
						$auther->getProvider(),
						$auther->getSocialId(),
						$auther->getName(),
						$auther->getEmail(),
						$auther->getSocialPage(),
						$auther->getSex(),
						$birthday_value ? date('Y-m-d', strtotime($birthday_value)) : '0000-00-00',
						'', // Avatar будет обработан отдельно
						time() + ($config['date_adjust'] * 60),
						time() + ($config['date_adjust'] * 60)
					);
					$query = "INSERT INTO " . uprefix . "_users (`pass`, `provider`, `social_id`, `name`, `mail`, `social_page`, `sex`, `birthday`, `avatar`, `reg`, `last`) VALUES ('";
					$query .= implode("', '", $values) . "')";
					// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] insert query=" . $query . "\n", FILE_APPEND);
					$insertResult = $mysql->query($query);
					// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] insert result=" . ($insertResult ? 'success' : 'failed') . "\n", FILE_APPEND);
					if (!$insertResult) {
						// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] INSERT failed, searching for existing user\n", FILE_APPEND);
						$user_doreg = $mysql->record("SELECT * FROM " . uprefix . "_users WHERE social_page = " . db_squote($auther->getSocialPage()) . " OR (provider = '" . $auther->getProvider() . "' AND social_id = '" . $auther->getSocialId() . "') ORDER BY id DESC LIMIT 1");
						if (is_array($user_doreg)) {
							$userid = $user_doreg['id'];
							// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] user already exists, found id=" . $userid . "\n", FILE_APPEND);
						} else {
							// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] cannot create or find user\n", FILE_APPEND);
							throw new Exception("Failed to insert user and cannot find existing user");
						}
					} else {
						$lastIdRow = $mysql->record("SELECT LAST_INSERT_ID() as id");
						$userid = $lastIdRow['id'];
						// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] new user id=" . $userid . "\n", FILE_APPEND);
						if (empty($userid) || $userid == 0) {
							// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] LAST_INSERT_ID failed, searching by social_page\n", FILE_APPEND);
							$user_doreg = $mysql->record("SELECT * FROM " . uprefix . "_users WHERE social_page = " . db_squote($auther->getSocialPage()) . " OR (provider = '" . $auther->getProvider() . "' AND social_id = '" . $auther->getSocialId() . "') ORDER BY id DESC LIMIT 1");
							if (is_array($user_doreg)) {
								$userid = $user_doreg['id'];
								// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] found user by fallback search, id=" . $userid . "\n", FILE_APPEND);
							} else {
								throw new Exception("User was inserted but cannot find ID");
							}
						} else {
							$user_doreg = $mysql->record("SELECT * FROM " . uprefix . "_users WHERE id = " . intval($userid));
							// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] user_doreg found=" . (is_array($user_doreg) ? 'yes id=' . $user_doreg['id'] : 'no') . "\n", FILE_APPEND);
						}
					}

					// Process avatar for new user
					$avatar = '';
					$get_avatar = $auther->getAvatar();
					if (!empty($get_avatar)) {
						if (addAvatarToFiles('newavatar', $get_avatar)) {
							@include_once root . 'includes/classes/upload.class.php';
							if (!empty($_FILES['newavatar']['name'])) {
								$fmanage = new file_managment();
								$imanage = new image_managment();
								$origName = strtolower(basename($_FILES['newavatar']['name']));
								$ext = pathinfo($origName, PATHINFO_EXTENSION);
								$safeExt = preg_match('/^(jpe?g|png|gif)$/i', $ext) ? strtolower($ext) : 'jpg';
								$fname = 'u' . intval($userid) . '_' . substr(md5($origName . microtime(true)), 0, 12) . '.' . $safeExt;
								$ftmp = $_FILES['newavatar']['tmp_name'];

								if (@copy($ftmp, $config['avatars_dir'] . $fname)) {
									$sz = $imanage->get_size($config['avatars_dir'] . $fname);
									$mysql->query("insert into " . prefix . "_images (name, orig_name, folder, date, user, owner_id, category) values (" . db_squote($fname) . ", " . db_squote($origName) . ", '', unix_timestamp(now()), " . db_squote($auther->getName()) . ", " . db_squote($userid) . ", '1')");
									$rowID = $mysql->record("select LAST_INSERT_ID() as id");
									if ($rowID && isset($rowID['id'])) {
										$mysql->query("update " . prefix . "_images set width=" . db_squote($sz['1']) . ", height=" . db_squote($sz['2']) . " where id = " . db_squote($rowID['id']));
									}
									$avatar = $fname;
								}

								// Clean up temp file
								@unlink($ftmp);
							}
						}

						if (!empty($avatar)) {
							$mysql->query(
								"UPDATE `" . uprefix . "_users` SET `activation` = '', `avatar` = " . db_squote($avatar) .
									" WHERE id = " . intval($userid)
							);
						}
					}
				} catch (Exception $e) {
					// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] ERROR creating user: " . $e->getMessage() . "\n", FILE_APPEND);
					die('Error creating user: ' . $e->getMessage());
				}
			} else {
				// User already exists
				$userFromDb = new stdClass();
				$userFromDb->provider = $record['provider'];
				$userFromDb->socialId = $record['social_id'];
				$userFromDb->name = $record['name'];
				$userFromDb->email = $record['email'];
				$userFromDb->socialPage = $record['social_page'];
				$userFromDb->sex = $record['sex'];
				$userFromDb->birthday = date('m.d.Y', strtotime($record['birthday']));
			}
			$user = new stdClass();
			$user->provider = $auther->getProvider();
			$user->socialId = $auther->getSocialId();
			$user->name =  $auther->getName();
			$user->email = $auther->getEmail();
			$user->socialPage = $auther->getSocialPage();
			$user->sex = $auther->getSex();
			$user->birthday = $auther->getBirthday();
			if (isset($userFromDb) && $userFromDb != $user) {
				$idToUpdate = $record['id'];
				$birthday = date('Y-m-d', strtotime($user->birthday));
				$get_avatar = $auther->getAvatar();
				$avatar = '';
				if (!empty($get_avatar)) {
					addAvatarToFiles('newavatar', $get_avatar);
					@include_once root . 'includes/classes/upload.class.php';
					if (!empty($_FILES['newavatar']['name'])) {
						$fmanage = new file_managment();
						$imanage = new image_managment();
						$origName = strtolower(basename($_FILES['newavatar']['name']));
						$ext = pathinfo($origName, PATHINFO_EXTENSION);
						$safeExt = preg_match('/^(jpe?g|png|gif)$/i', $ext) ? strtolower($ext) : 'jpg';
						$fname = 'u' . intval($idToUpdate) . '_' . substr(md5($origName . microtime(true)), 0, 12) . '.' . $safeExt;
						$ftmp = $_FILES['newavatar']['tmp_name'];
						$mysql->query("insert into " . prefix . "_images (name, orig_name, folder, date, user, owner_id, category) values (" . db_squote($fname) . ", " . db_squote($origName) . ", '', unix_timestamp(now()), " . db_squote($auther->getName()) . ", " . db_squote($idToUpdate) . ", '1')");
						$rowID = $mysql->record("select LAST_INSERT_ID() as id");
						if (@copy($ftmp, $config['avatars_dir'] . $fname)) {
							$sz = $imanage->get_size($config['avatars_dir'] . $fname);
							$mysql->query("update " . prefix . "_images set width=" . db_squote($sz['1']) . ", height=" . db_squote($sz['2']) . " where id = " . db_squote($rowID['id']) . " ");
							$avatar = $fname;
						}
					}
				}
				$mysql->query(
					"UPDATE " . uprefix . "_users SET " .
						"`social_id` = " . db_squote($user->socialId) . ", `name` = " . db_squote($user->name) . ", `mail` = " . db_squote($user->email) . ", " .
						"`social_page` = " . db_squote($user->socialPage) . ", `sex` = " . db_squote($user->sex) . ", " .
						"`birthday` = " . db_squote($birthday) .
						(!empty($avatar) ? ", `avatar` = " . db_squote($avatar) : '') .
						" WHERE `id` = " . db_squote($idToUpdate)
				);
			}
			$_SESSION['user'] = $user;
			$user_dologin = $mysql->record("SELECT * FROM " . uprefix . "_users WHERE social_page = " . db_squote($auther->getSocialPage()));
			try {
				// $logFile = (defined('root') ? root : ($_SERVER['DOCUMENT_ROOT'] . '/')) . 'engine/plugins/auth_social/log.txt';
				// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] user_dologin found=" . (is_array($user_dologin) ? 'yes id=' . ($user_dologin['id'] ?? 'null') : 'no') . "\n", FILE_APPEND);
			} catch (Throwable $e) {
			}
			if (is_array($user_dologin)) {
				$auth = $AUTH_METHOD[$config['auth_module']];
				$auth->save_auth($user_dologin);
				try {
					// $logFile = (defined('root') ? root : ($_SERVER['DOCUMENT_ROOT'] . '/')) . 'engine/plugins/auth_social/log.txt';
					// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] login saved, redirecting to home\n", FILE_APPEND);
				} catch (Throwable $e) {
				}
				header('Location: ' . $config['home_url']);
				return;
			}
		} else {
			try {
				// $logFile = (defined('root') ? root : ($_SERVER['DOCUMENT_ROOT'] . '/')) . 'engine/plugins/auth_social/log.txt';
				// // @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] authenticate FAILED for provider=" . $provider . "\n", FILE_APPEND);
			} catch (Throwable $e) {
			}
		}
		header('Location: ' . $config['home_url']);
	} else {
		header('Location: ' . $config['home_url']);
	}
}
// Callback для add_act('usermenu', 'auth_social_links')
// Выводит набор ссылок авторизации через доступные провайдеры.
// Если пользователь уже авторизован - ничего не возвращает.
function auth_social_links()
{
	global $userROW, $config;
	// Не показываем, если пользователь залогинен
	if (is_array($userROW)) {
		return '';
	}
	// Подключаем автолоадер
	if (!class_exists('SocialAuther\\SocialAuther')) {
		// Используем константу root, если определена
		if (defined('root')) {
			@require_once root . 'plugins/auth_social/lib/SocialAuther/autoload.php';
		} else {
			@require_once ($_SERVER['DOCUMENT_ROOT']) . '/engine/plugins/auth_social/lib/SocialAuther/autoload.php';
		}
	}
	$providers = ['yandex', 'google', 'facebook', 'github', 'vkid'];
	$links = [];
	foreach ($providers as $p) {
		$clientId = pluginGetVariable('auth_social', $p . '_client_id');
		$clientSecret = pluginGetVariable('auth_social', $p . '_client_secret');
		if (!$clientId || !$clientSecret) {
			continue; // пропускаем не настроенных
		}
		$settings = [
			'client_id'     => $clientId,
			'client_secret' => $clientSecret,
			'redirect_uri'  => ($p === 'vkid') ? (home . '/plugin/auth_social/vkid/') : (home . '/plugin/auth_social/?provider=' . $p),
		];
		$className = 'SocialAuther\\Adapter\\' . ucfirst($p);
		try {
			if (!class_exists($className)) {
				continue;
			}
			$adapter = new $className($settings);
			$authUrl = $adapter->getAuthUrl();
			$links[] = '<a class="auth-social-link auth-social-link--' . $p . '" href="' . htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') . '" rel="nofollow" title="' . ucfirst($p) . '">' . ucfirst($p) . '</a>';
		} catch (Throwable $e) {
			// Тихо игнорируем ошибки конкретного провайдера
		}
	}
	if (!$links) {
		return '';
	}
	// Оборачиваем в контейнер (можно стилизовать через .auth-social-links)
	return '<div class="auth-social-links">' . implode(' ', $links) . '</div>';
}
class SocialAuthCoreFilter extends CoreFilter
{
	function showUserMenu(&$tVars)
	{
		global $mysql, $userROW, $lang;
		require_once root . 'plugins/auth_social/lib/SocialAuther/autoload.php';
		$adapterConfigs = array(
			'vkid'          => array(
				'client_id'     => pluginGetVariable('auth_social', 'vkid_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'vkid_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/vkid/",
				'scope'         => (pluginGetVariable('auth_social', 'vkid_scope') ?: 'email'),
			),
			'yandex'        => array(
				'client_id'     => pluginGetVariable('auth_social', 'yandex_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'yandex_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=yandex"
			),
			'google'        => array(
				'client_id'     => pluginGetVariable('auth_social', 'google_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'google_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=google"
			),
			'facebook'      => array(
				'client_id'     => pluginGetVariable('auth_social', 'facebook_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'facebook_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=facebook"
			),
			'github'        => array(
				'client_id'     => pluginGetVariable('auth_social', 'github_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'github_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=github"
			)
		);
		$adapters = array();
		foreach ($adapterConfigs as $adapter => $settings) {
			$class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
			$adapters[$adapter] = new $class($settings);
		}
		foreach ($adapters as $title => $adapter) {
			// VK ID требует PKCE, поэтому ссылка ведет на наш обработчик, а не напрямую на VK
			if ($title === 'vkid') {
				$tVars['p']['auth_social'][$title] = array(
					'authUrl' => home . '/plugin/auth_social/vkid/',
					'title'   => 'VK ID'
				);
			} else {
				$tVars['p']['auth_social'][$title] = array(
					'authUrl' => $adapter->getAuthUrl(),
					'title'   => ucfirst($title)
				);
			}
		}
	}

	function loginAction(&$tVars)
	{
		global $mysql, $userROW, $lang;
		// Не показываем социальную авторизацию, если пользователь уже залогинен
		if (is_array($userROW)) {
			return;
		}
		require_once root . 'plugins/auth_social/lib/SocialAuther/autoload.php';
		$adapterConfigs = array(
			'vkid'          => array(
				'client_id'     => pluginGetVariable('auth_social', 'vkid_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'vkid_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/vkid/",
				'scope'         => (pluginGetVariable('auth_social', 'vkid_scope') ?: 'email'),
			),
			'yandex'        => array(
				'client_id'     => pluginGetVariable('auth_social', 'yandex_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'yandex_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=yandex"
			),
			'google'        => array(
				'client_id'     => pluginGetVariable('auth_social', 'google_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'google_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=google"
			),
			'facebook'      => array(
				'client_id'     => pluginGetVariable('auth_social', 'facebook_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'facebook_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=facebook"
			),
			'github'        => array(
				'client_id'     => pluginGetVariable('auth_social', 'github_client_id'),
				'client_secret' => pluginGetVariable('auth_social', 'github_client_secret'),
				'redirect_uri'  => home . "/plugin/auth_social/?provider=github"
			)
		);
		$adapters = array();
		foreach ($adapterConfigs as $adapter => $settings) {
			$class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
			if (class_exists($class)) {
				$adapters[$adapter] = new $class($settings);
			}
		}
		foreach ($adapters as $title => $adapter) {
			// VK ID требует PKCE, поэтому ссылка ведет на наш обработчик, а не напрямую на VK
			if ($title === 'vkid') {
				$tVars['p']['auth_social'][$title] = array(
					'authUrl' => home . '/plugin/auth_social/vkid/',
					'title'   => 'VK ID'
				);
			} else {
				$tVars['p']['auth_social'][$title] = array(
					'authUrl' => $adapter->getAuthUrl(),
					'title'   => ucfirst($title)
				);
			}
		}
	}
}
register_filter('core.userMenu', 'auth_social', new SocialAuthCoreFilter);
register_filter('core.login', 'auth_social', new SocialAuthCoreFilter);
if (class_exists('p_uprofileFilter')) {
	class uSocialFilter extends p_uprofileFilter
	{
		function showProfile($userID, $SQLrow, &$tvars)
		{
			/*
			if (empty($SQLrow['loginza_id'])) {
				$tvars['regx']['/\[if-loginza\](.*?)\[\/if-loginza\]/si'] = '';
				$tvars['vars']['loginza_account'] = '';
			}
			else {
				$tvars['regx']['/\[if-loginza\](.*?)\[\/if-loginza\]/si'] = '$1';
				$tvars['vars']['loginza_account'] = $SQLrow['loginza_id'];
			}
			*/
		}
		function editProfileForm($userID, $SQLrow, &$tvars)
		{
			/*
			if (empty($SQLrow['loginza_id'])) {
				$tvars['regx']['/\[if-loginza\](.*?)\[\/if-loginza\]/si'] = '';
				$tvars['regx']['/\[if-not-loginza\](.*?)\[\/if-not-loginza\]/si'] = '$1';
				$tvars['vars']['loginza_account'] = '';
			}
			else {
				$tvars['regx']['/\[if-loginza\](.*?)\[\/if-loginza\]/si'] = '$1';
				$tvars['regx']['/\[if-not-loginza\](.*?)\[\/if-not-loginza\]/si'] = '';
				$tvars['vars']['loginza_account'] = $SQLrow['loginza_id'];
			}
			*/
		}
		function editProfile($userID, $SQLrow, &$SQLnew)
		{
			global $lang, $config, $mysql;
			$SQLnew['sex'] = secure_html($_REQUEST['editsex']);
			$SQLnew['birthday'] = secure_html($_REQUEST['editbirthday']);
		}
	}
	register_filter('plugin.uprofile', 'auth_social', new uSocialFilter);
}
/**
 * Add to $_FILES from external url
 * sample usage: addAvatarToFiles('google_favicon', 'http://google.com/favicon.ico');
 * @since  17.12.12 17:23
 * @author mekegi
 *
 * @param string $key
 * @param string $url sample http://some.tld/path/to/file.ext
 */
function addAvatarToFiles($key, $url)
{
	$scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
	if (!in_array($scheme, ['http', 'https'], true)) {
		return false;
	}

	$tempName = tempnam(sys_get_temp_dir(), 'avatar_');
	$imgRawData = @file_get_contents($url);
	if ($imgRawData === false) {
		return false;
	}

	file_put_contents($tempName, $imgRawData);
	$info = @getimagesize($tempName);
	if (!$info || empty($info['mime'])) {
		@unlink($tempName);
		return false;
	}

	// Extract filename from URL, remove query parameters
	$urlPath = parse_url($url, PHP_URL_PATH);
	$originalName = basename($urlPath);
	if (empty($originalName)) {
		$originalName = 'avatar.jpg';
	}

	$_FILES[$key] = array(
		'name'     => $originalName,
		'type'     => $info['mime'],
		'tmp_name' => $tempName,
		'error'    => 0,
		'size'     => strlen($imgRawData),
	);

	return true;
}
