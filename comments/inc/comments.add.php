<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, get_ip, validate_email, is_post, sanitize, array_get};
//
// Params for filtering and processing
//
function comments_add()
{
	global $mysql, $config, $AUTH_METHOD, $userROW, $ip, $lang, $parse, $catmap, $catz, $PFILTERS;
	// Проверка метода запроса
	if (!is_post()) {
		msg(array("type" => "error", "text" => "Invalid request method"));
		return;
	}
	// Check membership
	// If login/pass is entered (either logged or not)
	if (array_get($_POST, 'name', '') && array_get($_POST, 'password', '')) {
		$auth = $AUTH_METHOD[$config['auth_module']];
		$user = $auth->login(0, array_get($_POST, 'name', ''), array_get($_POST, 'password', ''));
		if (!is_array($user)) {
			msg(array("type" => "error", "text" => $lang['comments:err.password']));
			return;
		}
	}
	// Entered data have higher priority then login data
	$memberRec = null;
	if (is_array($user)) {
		$SQL['author'] = $user['name'];
		$SQL['author_id'] = $user['id'];
		$SQL['mail'] = $user['mail'];
		$is_member = 1;
		$memberRec = $user;
	} else if (is_array($userROW)) {
		$SQL['author'] = $userROW['name'];
		$SQL['author_id'] = $userROW['id'];
		$SQL['mail'] = $userROW['mail'];
		$is_member = 1;
		$memberRec = $userROW;
	} else {
		$SQL['author'] = secure_html(trim(array_get($_POST, 'name', '')));
		$SQL['author_id'] = 0;
		$SQL['mail'] = secure_html(trim(array_get($_POST, 'mail', '')));
		$is_member = 0;
	}
	// CSRF protection variables
	$sValue = '';
	$SQL['post'] = 0;
	$newsidValue = array_get($_POST, 'newsid', '');
	logger('Comment add attempt: newsid=' . $newsidValue . ', module=' . array_get($_POST, 'module', ''), 'info', 'comments.log');
	if (preg_match('#^(\d+)\#(.+)$#', $newsidValue, $m)) {
		$SQL['post'] = intval($m[1]);
		$sValue = $m[2];
		$expectedToken = genUToken('comment.add.' . $SQL['post']);
		logger('CSRF check: post_id=' . $SQL['post'] . ', token_match=' . ($sValue == $expectedToken ? 'yes' : 'no'), 'info', 'comments.log');
	} else {
		logger('Failed to parse newsid value: ' . $newsidValue, 'warning', 'comments.log');
	}
	if (!$SQL['post'] || $sValue != genUToken('comment.add.' . $SQL['post'])) {
		logger('Comment rejected: post_id=' . $SQL['post'] . ', IP=' . get_ip(), 'warning', 'comments.log');
		msg(array("type" => "error", "text" => $lang['comments:err.regonly']));
		return;
	}
	// Determine module (for gallery images, news, etc.)
	$module = array_get($_POST, 'module', '');
	logger('Comment processing: post_id=' . $SQL['post'] . ', module=' . $module, 'info', 'comments.log');
	// Обрабатываем текст комментария - используем sanitize с отключением strip_tags
	$SQL['text'] = sanitize(trim(array_get($_POST, 'content', '')), false);
	logger('After sanitize, text length: ' . strlen($SQL['text']), 'info', 'comments.log');
	// If user is not logged, make some additional tests
	if (!$is_member) {
		logger('User not logged, checking regonly', 'info', 'comments.log');
		// Check if unreg are allowed to make comments
		if (pluginGetVariable('comments', 'regonly')) {
			logger('Regonly enabled, rejecting', 'warning', 'comments.log');
			msg(array("type" => "error", "text" => $lang['comments:err.regonly']));
			return;
		}
		// Check captcha for unregistered visitors
		if ($config['use_captcha']) {
			$vcode = array_get($_POST, 'vcode', '');
			if ($vcode != array_get($_SESSION, 'captcha', '')) {
				msg(array("type" => "error", "text" => $lang['comments:err.vcode']));
				return;
			}
			// Update captcha
			$_SESSION['captcha'] = rand(00000, 99999);
		}
		if (!$SQL['author']) {
			msg(array("type" => "error", "text" => $lang['comments:err.name']));
			return;
		}
		if (!$SQL['mail']) {
			msg(array("type" => "error", "text" => $lang['comments:err.mail']));
			return;
		}
		// Check if author name use incorrect symbols. Check should be done only for unregs
		if ((!$SQL['author_id']) && (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $SQL['author']) || strlen($SQL['author']) > 60)) {
			msg(array("type" => "error", "text" => $lang['comments:err.badname']));
			return;
		}
		if (!validate_email($SQL['mail'])) {
			msg(array("type" => "error", "text" => $lang['comments:err.badmail']));
			logger('Invalid email attempt: ' . $SQL['mail'] . ' from IP: ' . get_ip(), 'warning', 'comments.log');
			return;
		}
		// Check if guest wants to use email of already registered user
		if (pluginGetVariable('comments', 'guest_edup_lock')) {
			if (is_array($mysql->record("select * from " . uprefix . "_users where mail = " . db_squote($SQL['mail']) . " limit 1"))) {
				msg(array("type" => "error", "text" => $lang['comments:err.edupmail']));
				return;
			}
		}
	}
	$maxlen = intval(pluginGetVariable('comments', 'maxlen'));
	if (($maxlen) && (strlen($SQL['text']) > $maxlen || strlen($SQL['text']) < 2)) {
		msg(array("type" => "error", "text" => str_replace('{maxlen}', pluginGetVariable('comments', 'maxlen'), $lang['comments:err.badtext'])));
		return;
	}
	// Check for flood
	if (checkFlood(0, $ip, 'comments', 'add', $is_member ? $memberRec : null, $is_member ? null : $SQL['author'])) {
		msg(array("type" => "error", "text" => str_replace('{timeout}', $config['flood_time'], $lang['comments:err.flood'])));
		return;
	}
	// Check for bans
	if ($ban_mode = checkBanned($ip, 'comments', 'add', $is_member ? $memberRec : null, $is_member ? null : $SQL['author'])) {
		// If hidden mode is active - say that news is not found
		if ($ban_mode == 2) {
			msg(array("type" => "error", "text" => $lang['comments:err.notfound']));
		} else {
			msg(array("type" => "error", "text" => $lang['comments:err.ipban']));
		}
		return;
	}
	// Locate item (news or other module item like gallery image)
	if ($module == 'images') {
		// For gallery images - look in images table
		logger('Looking for image in _images with id=' . $SQL['post'], 'info', 'comments.log');
		if ($news_row = $mysql->record("select * from " . prefix . "_images where id = " . db_squote($SQL['post']))) {
			// Gallery images always allow comments if they are shown
			$allowCom = 1;
			logger('Image found: ' . ($news_row['title'] ?? 'no title'), 'info', 'comments.log');
		} else {
			logger('Image NOT found in _images, id=' . $SQL['post'], 'warning', 'comments.log');
			msg(array("type" => "error", "text" => $lang['comments:err.notfound']));
			return;
		}
	} else {
		// Default: news comments
		if ($news_row = $mysql->record("select * from " . prefix . "_news where id = " . db_squote($SQL['post']))) {
			// Determine if comments are allowed in  this specific news
			$allowCom = $news_row['allow_com'];
			if ($allowCom == 2) {
				// `Use default` - check master category
				$masterCat = intval(array_shift(explode(',', $news_row['catid'])));
				if ($masterCat && isset($catmap[$masterCat])) {
					$allowCom = intval($catz[$catmap[$masterCat]]['allow_com']);
				}
				// If we still have 2 (no master category or master category also have 'default' - fetch plugin's config
				if ($allowCom == 2) {
					$allowCom = pluginGetVariable('comments', 'global_default');
				}
			}
			if (!$allowCom) {
				msg(array("type" => "error", "text" => $lang['comments:err.forbidden']));
				return;
			}
		} else {
			msg(array("type" => "error", "text" => $lang['comments:err.notfound']));
			return;
		}
	}
	// Check for multiple comments block [!!! ADMINS CAN DO IT IN ANY CASE !!!]
	$multiCheck = 0;
	// Make tests only for non-admins
	if (!is_array($userROW)) {
		// Not logged
		$multiCheck = !intval(pluginGetVariable('comments', 'multi'));
	} else {
		// Logged. Skip admins
		if ($userROW['status'] != 1) {
			// Check for author
			$multiCheck = !intval(pluginGetVariable('comments', (($userROW['id'] == $news_row['author_id']) ? 'author_' : '') . 'multi'));
		}
	}
	if ($multiCheck) {
		// Locate last comment for this news
		if (is_array($lpost = $mysql->record("select author_id, author, ip, mail from " . prefix . "_comments where post=" . db_squote($SQL['post']) . " order by id desc limit 1"))) {
			// Check for post from the same user
			if (is_array($userROW)) {
				if ($userROW['id'] == $lpost['author_id']) {
					msg(array("type" => "error", "text" => $lang['comments:err.multilock']));
					return;
				}
			} else {
				//print "Last post: ".$lpost['id']."<br>\n";
				if (($lpost['author'] == $SQL['author']) || ($lpost['mail'] == $SQL['mail'])) {
					msg(array("type" => "error", "text" => $lang['comments:err.multilock']));
					return;
				}
			}
		}
	}
	$SQL['postdate'] = time() + ($config['date_adjust'] * 60);
	if (pluginGetVariable('comments', 'maxwlen') > 1) {
		$SQL['text'] = preg_replace('/(\S{' . intval(pluginGetVariable('comments', 'maxwlen')) . '})(?!\s)/', '$1 ', $SQL['text']);
		if ((!$SQL['author_id']) && (strlen($SQL['author']) > pluginGetVariable('comments', 'maxwlen'))) {
			$SQL['author'] = substr($SQL['author'], 0, pluginGetVariable('comments', 'maxwlen')) . " ...";
		}
	}
	$SQL['text'] = str_replace("\r\n", "<br />", $SQL['text']);
	$SQL['ip'] = $ip;
	$SQL['reg'] = ($is_member) ? '1' : '0';
	$SQL['module'] = $module; // Save module type (images/news)
	// Модерация: проверяем группы пользователей
	$needModeration = false;
	if (pluginGetVariable('comments', 'moderation')) {
		$moderationGroups = pluginGetVariable('comments', 'moderation_groups');
		if ($moderationGroups) {
			$groups = array_map('trim', explode(',', $moderationGroups));
			$userStatus = is_array($userROW) ? $userROW['status'] : 0;
			$needModeration = in_array($userStatus, $groups);
		} else {
			// Старая логика: незарегистрированные и пользователи со статусом > 2
			$needModeration = (!is_array($userROW) || ($userROW['status'] > 2));
		}
	}
	$SQL['moderated'] = $needModeration ? 0 : 1;
	// RUN interceptors
	load_extras('comments:add');
	if (is_array($PFILTERS['comments']))
		foreach ($PFILTERS['comments'] as $k => $v) {
			$pluginResult = $v->addComments($memberRec, $news_row, $tvars, $SQL);
			if ((is_array($pluginResult) && ($pluginResult['result'])) || (!is_array($pluginResult) && $pluginResult))
				continue;
			msg(array("type" => "error", "text" => str_replace(array('{plugin}', '{errorText}'), array($k, (is_array($pluginResult) && isset($pluginResult['errorText']) ? $pluginResult['errorText'] : '')), $lang['comments:err.' . ((is_array($pluginResult) && isset($pluginResult['errorText'])) ? 'e' : '') . 'pluginlock'])));
			return 0;
		}
	// Create comment
	$vnames = array();
	$vparams = array();
	foreach ($SQL as $k => $v) {
		$vnames[] = $k;
		$vparams[] = db_squote($v);
	}
	$mysql->query("insert into " . prefix . "_comments (" . implode(",", $vnames) . ") values (" . implode(",", $vparams) . ")");
	// Retrieve comment ID
	$comment_id = $mysql->result("select LAST_INSERT_ID() as id");
	// Логирование добавления комментария
	logger(sprintf(
		'New comment #%d by %s (ID: %d, IP: %s) for %s #%d%s',
		$comment_id,
		$SQL['author'],
		$SQL['author_id'],
		get_ip(),
		$module == 'images' ? 'image' : 'news',
		$SQL['post'],
		$SQL['moderated'] == 0 ? ' [MODERATION]' : ''
	), 'info', 'comments.log');
	// Update comment counter (only if comment is approved)
	if ($SQL['moderated'] == 1) {
		if ($module == 'images') {
			// Update counter in _images table
			$mysql->query("update " . prefix . "_images set com=com+1 where id=" . db_squote($SQL['post']));
		} else {
			// Update counter in news table
			$mysql->query("update " . prefix . "_news set com=com+1 where id=" . db_squote($SQL['post']));
		}
	}
	// Update counter for user
	if ($SQL['author_id']) {
		$mysql->query("update " . prefix . "_users set com=com+1 where id = " . db_squote($SQL['author_id']));
	}
	// Update flood protect database
	checkFlood(1, $ip, 'comments', 'add', $is_member ? $memberRec : null, $is_member ? null : $SQL['author']);
	// RUN interceptors
	if (is_array($PFILTERS['comments']))
		foreach ($PFILTERS['comments'] as $k => $v)
			$v->addCommentsNotify($memberRec, $news_row, $tvars, $SQL, $comment_id);

	// Telegram notification (only for approved comments)
	if ($SQL['moderated'] == 1 && getPluginStatusActive('jchat_tgnotify')) {
		@include_once(root . 'plugins/jchat_tgnotify/jchat_tgnotify.php');
		if (function_exists('ngcms_tg_notify')) {
			$newsTitle = $news_row['title'] ?? 'Новость #' . $SQL['post'];
			ngcms_tg_notify('comment', [
				'title'    => 'Комментарий к: ' . $newsTitle,
				'author'   => $SQL['author'],
				'text'     => strip_tags(str_replace("<br />", "\n", $SQL['text'])),
				'url'      => home . newsGenerateLink($news_row),
				'datetime' => date('Y-m-d H:i:s', $SQL['postdate']),
			]);
		}
	}

	// Email informer (only for news)
	if (($module != 'images') && (pluginGetVariable('comments', 'inform_author') || pluginGetVariable('comments', 'inform_admin'))) {
		$alink = ($SQL['author_id']) ? generatePluginLink('uprofile', 'show', array('name' => $SQL['author'], 'id' => $SQL['author_id']), array(), false, true) : '';
		$body = str_replace(
			array(
				'{username}',
				'[userlink]',
				'[/userlink]',
				'{comment}',
				'{newslink}',
				'{newstitle}'
			),
			array(
				$SQL['author'],
				($SQL['author_id']) ? '<a href="' . $alink . '">' : '',
				($SQL['author_id']) ? '</a>' : '',
				$parse->bbcodes($parse->smilies(secure_html($SQL['text']))),
				newsGenerateLink($news_row, false, 0, true),
				$news_row['title'],
			),
			$lang['notice']
		);
		if (pluginGetVariable('comments', 'inform_author')) {
			// Determine author's email
			if (is_array($umail = $mysql->record("select * from " . uprefix . "_users where id = " . db_squote($news_row['author_id'])))) {
				zzMail($umail['mail'], $lang['newcomment'], $body, 'html');
			}
		}
		if (pluginGetVariable('comments', 'inform_admin'))
			zzMail($config['admin_mail'], $lang['newcomment'], $body, 'html');
	}
	@setcookie("com_username", urlencode($SQL['author']), 0, '/');
	@setcookie("com_usermail", urlencode($SQL['mail']), 0, '/');
	return array($news_row, $comment_id);
}
