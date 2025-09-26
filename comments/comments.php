<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
$lang = LoadLang("comments", "site");
class CommentsNewsFilter extends NewsFilter
{
	function addNewsForm(&$tvars)
	{
		global $lang;
		loadPluginLang('comments', 'config', '', '', ':');
		for ($ix = 0; $ix <= 2; $ix++) {
			$tvars['plugin']['comments']['acom:' . $ix] = (pluginGetVariable('comments', 'default_news') == $ix) ? 'selected="selected"' : '';
		}
	}
	function addNews(&$tvars, &$SQL)
	{
		$SQL['allow_com'] = intval($_REQUEST['allow_com']);
		return 1;
	}
	function editNewsForm($newsID, $SQLnews, &$tvars)
	{
		global $lang, $mysql, $config, $parse, $tpl, $PHP_SELF;
		loadPluginLang('comments', 'config', '', '', ':');
		// List comments
		$comments = '';
		$tpl->template('comments', tpl_actions . 'news');
		foreach ($mysql->select("select * from " . prefix . "_comments where post='" . $newsID . "' order by id") as $crow) {
			$text = $crow['text'];
			if ($config['blocks_for_reg']) {
				$text = $parse->userblocks($text);
			}
			if ($config['use_bbcodes']) {
				$text = $parse->bbcodes($text);
			}
			if ($config['use_htmlformatter']) {
				$text = $parse->htmlformatter($text);
			}
			if ($config['use_smilies']) {
				$text = $parse->smilies($text);
			}
			$txvars['vars'] = array(
				'php_self'   => $PHP_SELF,
				'com_author' => $crow['author'],
				'com_post'   => $crow['post'],
				'com_url'    => ($crow['url']) ? $crow['url'] : $PHP_SELF . '?mod=users&action=edituser&id=' . $crow['author_id'],
				'com_id'     => $crow['id'],
				'com_ip'     => $crow['ip'],
				'com_time'   => LangDate(pluginGetVariable('comments', 'timestamp'), $crow['postdate']),
				'com_part'   => $text
			);
			if ($crow['reg']) {
				$txvars['vars']['[userlink]'] = '';
				$txvars['vars']['[/userlink]'] = '';
			} else {
				$txvars['regx']["'\\[userlink\\].*?\\[/userlink\\]'si"] = $crow['author'];
			}
			$tpl->vars('comments', $txvars);
			$comments .= $tpl->show('comments');
		}
		$tvars['plugin']['comments']['list'] = $comments;
		$tvars['plugin']['comments']['count'] = $SQLnews['com'] ? $SQLnews['com'] : $lang['noa'];
		for ($ix = 0; $ix <= 2; $ix++) {
			$tvars['plugin']['comments']['acom:' . $ix] = ($SQLnews['allow_com'] == $ix) ? 'selected="selected"' : '';
		}
	}
	function editNews($newsID, $SQLold, &$SQLnew, &$tvars)
	{
		$SQLnew['allow_com'] = intval($_REQUEST['allow_com']);
		return 1;
	}
	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{
		global $catmap, $catz, $config, $userROW, $template, $lang, $tpl;
		// Determine if comments are allowed in  this specific news
		$allowCom = $SQLnews['allow_com'];
		if ($allowCom == 2) {
			// `Use default` - check master category
			$masterCat = intval(array_shift(explode(',', $SQLnews['catid'])));
			if ($masterCat && isset($catmap[$masterCat])) {
				$allowCom = intval($catz[$catmap[$masterCat]]['allow_com']);
			}
			// If we still have 2 (no master category or master category also have 'default' - fetch plugin's config
			if ($allowCom == 2) {
				$allowCom = pluginGetVariable('comments', 'global_default');
			}
		}
		// Get comment counts
		global $mysql;
		$published_count = intval($mysql->result("SELECT COUNT(*) FROM " . prefix . "_comments WHERE post=" . db_squote($newsID) . " AND moderated=1"));
		$pending_count = intval($mysql->result("SELECT COUNT(*) FROM " . prefix . "_comments WHERE post=" . db_squote($newsID) . " AND moderated=0"));
		$total_count = $published_count + $pending_count;
		// Fill variables within news template
		$tvars['vars']['comments-num'] = $published_count;
		$tvars['vars']['comnum'] = $published_count;
		$tvars['vars']['comments-pending'] = $pending_count;
		$tvars['vars']['comments-total'] = $total_count;
		// Also add to template variables with correct names
		$tvars['vars']['comments_num'] = $published_count;
		$tvars['vars']['comments_pending'] = $pending_count;
		$tvars['vars']['comments_total'] = $total_count;
		$tvars['regx']['[\[comheader\](.*)\[/comheader\]]'] = ($published_count) ? '$1' : '';
		// Blocks [comments] .. [/comments] and [nocomments] .. [/nocomments]
		$tvars['regx']['[\[comments\](.*)\[/comments\]]'] = ($published_count) ? '$1' : '';
		$tvars['regx']['[\[nocomments\](.*)\[/nocomments\]]'] = ($published_count) ? '' : '$1';
		// Check if we need to add comments block:
		//	* style == full
		//  * emulate == false
		//  * plugin == not set
		if (!(($mode['style'] == 'full') && (!$mode['emulate']) && (!isset($mode['plugin'])))) {
			// No, we don't need to show comments
			$tvars['vars']['plugin_comments'] = '';
			return 1;
		}
		// ******************************************** //
		// Yeah, let's show comments here
		// ******************************************** //
		// Prepare params for call
		$callingCommentsParams = array('outprint' => true, 'total' => $SQLnews['com']);
		// Set default template path
		$templatePath = tpl_site . 'plugins/comments';
		$fcat = array_shift(explode(",", $SQLnews['catid']));
		// Check if there is a custom mapping
		if ($fcat && $catmap[$fcat] && ($ctname = $catz[$catmap[$fcat]]['tpl'])) {
			// Check if directory exists
			if (is_dir(tpl_site . 'ncustom/' . $ctname)) {
				$callingCommentsParams['overrideTemplatePath'] = tpl_site . 'ncustom/' . $ctname;
				$templatePath = tpl_site . 'ncustom/' . $ctname;
			}
		}
		include_once(root . "/plugins/comments/inc/comments.show.php");
		// Check if we need pagination
		$flagMoreComments = false;
		$skipCommShow = false;
		if (pluginGetVariable('comments', 'multipage')) {
			$multi_mcount = intval(pluginGetVariable('comments', 'multi_mcount'));
			// If we have comments more than for one page - activate pagination
			if (($multi_mcount >= 0) && ($SQLnews['com'] > $multi_mcount)) {
				$callingCommentsParams['limitCount'] = $multi_mcount;
				$flagMoreComments = true;
				if (!$multi_mcount)
					$skipCommShow = true;
			}
		}
		$tcvars = array();
		// Show comments [ if not skipped ]
		$tcvars['vars']['entries'] = $skipCommShow ? '' : comments_show($newsID, 0, 0, $callingCommentsParams);
		// If multipage is used and we have more comments - show
		if ($flagMoreComments) {
			$link = checkLinkAvailable('comments', 'show') ?
				generateLink('comments', 'show', array('news_id' => $newsID)) :
				generateLink('core', 'plugin', array('plugin' => 'comments', 'handler' => 'show'), array('news_id' => $newsID));
			$tcvars['vars']['more_comments'] = str_replace(array('{link}', '{count}'), array($link, $SQLnews['com']), $lang['comments:link.more']);
			$tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '$1';
		} else {
			$tcvars['vars']['more_comments'] = '';
			$tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '';
		}
		// Show form for adding comments
		if ($allowCom && (!pluginGetVariable('comments', 'regonly') || is_array($userROW))) {
			$tcvars['vars']['form'] = comments_showform($newsID, $callingCommentsParams);
			$tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '';
			$tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
		} else {
			$tcvars['vars']['form'] = '';
			$tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = $allowCom ? '$1' : '';
			$tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = $allowCom ? '' : '$1';
		}
		// Use Twig template from plugin
		global $twig;
		$templateFile = root . '/plugins/comments/tpl/comments.container.tpl';
		if (file_exists($templateFile)) {
			$templateContent = file_get_contents($templateFile);
			$twigTemplate = $twig->createTemplate($templateContent);
			$tcvars['vars']['is_external'] = false;
			$tcvars['vars']['regonly'] = $allowCom && !$tcvars['vars']['form'];
			$tcvars['vars']['commforbidden'] = !$allowCom;
			loadPluginLang('comments', 'site', '', '', ':');
			$tcvars['vars']['lang'] = $lang;
			$tvars['vars']['plugin_comments'] = $twigTemplate->render($tcvars['vars']);
			$tvars['vars']['comments'] = $tvars['vars']['plugin_comments'];
		} else {
			// Fallback to old template
			$tcvars['regx']['#\[comheader\](.*)\[/comheader\]#is'] = ($SQLnews['com']) ? '$1' : '';
			$tpl->template('comments.internal', $templatePath);
			$tpl->vars('comments.internal', $tcvars);
			$tvars['vars']['plugin_comments'] = $tpl->show('comments.internal');
			// Ensure `comments` is also populated for templates that render {{ comments }}
			$tvars['vars']['comments'] = $tvars['vars']['plugin_comments'];
		}
	}
}
class CommentsFilterAdminCategories extends FilterAdminCategories
{
	function addCategory(&$tvars, &$SQL)
	{
		$SQL['allow_com'] = intval($_REQUEST['allow_com']);
		return 1;
	}
	function addCategoryForm(&$tvars)
	{
		global $lang;
		loadPluginLang('comments', 'config', '', '', ':');
		$allowCom = pluginGetVariable('comments', 'default_categories');
		$ms = '<select name="allow_com">';
		$cv = array('0' => 'запретить', '1' => 'разрешить', '2' => 'по умолчанию');
		for ($i = 0; $i < 3; $i++) {
			$ms .= '<option value="' . $i . '"' . (($allowCom == $i) ? ' selected="selected"' : '') . '>' . $cv[$i] . '</option>';
		}
		$tvars['extend'] .= '<tr><td width="70%" class="contentEntry1">' . $lang['comments:categories.comments'] . '<br/><small>' . $lang['comments:categories.comments#desc'] . '</small></td><td width="30%" class="contentEntry2">' . $ms . '</td></tr>';
		return 1;
	}
	function editCategoryForm($categoryID, $SQL, &$tvars)
	{
		global $lang;
		loadPluginLang('comments', 'config', '', '', ':');
		if (!isset($SQL['allow_com'])) {
			$SQL['allow_com'] = pluginGetVariable('comments', 'default_categories');
		}
		$ms = '<select name="allow_com">';
		$cv = array('0' => 'запретить', '1' => 'разрешить', '2' => 'по умолчанию');
		for ($i = 0; $i < 3; $i++) {
			$ms .= '<option value="' . $i . '"' . (($SQL['allow_com'] == $i) ? ' selected="selected"' : '') . '>' . $cv[$i] . '</option>';
		}
		$tvars['extend'] .= '<tr><td width="70%" class="contentEntry1">' . $lang['comments:categories.comments'] . '<br/><small>' . $lang['comments:categories.comments#desc'] . '</small></td><td width="30%" class="contentEntry2">' . $ms . '</td></tr>';
		return 1;
	}
	function editCategory($categoryID, $SQL, &$SQLnew, &$tvars)
	{
		$SQLnew['allow_com'] = intval($_REQUEST['allow_com']);
		return 1;
	}
}
function plugin_comments_add()
{
	global $config, $catz, $catmap, $tpl, $template, $lang, $SUPRESS_TEMPLATE_SHOW;
	$SUPRESS_TEMPLATE_SHOW = 1;
	// Connect library
	include_once(root . "/plugins/comments/inc/comments.show.php");
	include_once(root . "/plugins/comments/inc/comments.add.php");
	// Call comments_add() to ADD COMMENT
	if (is_array($addResult = comments_add())) {
		// Ok.
		// Check if AJAX mode is turned OFF
		if (!$_REQUEST['ajax']) {
			// We should JUMP to this new comment
			// Make FULL news link
			$nlink = newsGenerateLink($addResult[0]);
			// Make redirect to full news
			@header("Location: " . $nlink);
			return 1;
		}
		// AJAX MODE.
		// Let's print (ONLY) new comment
		$SQLnews = $addResult[0];
		$commentId = $addResult[1];
		// Check if we need to override news template
		$callingCommentsParams = array('outprint' => true);
		// Set default template path
		$templatePath = tpl_dir . $config['theme'];
		// Find first category
		$fcat = array_shift(explode(",", $SQLnews['catid']));
		// Check if there is a custom mapping
		if ($fcat && $catmap[$fcat] && ($ctname = $catz[$catmap[$fcat]]['tpl'])) {
			// Check if directory exists
			if (is_dir($templatePath . '/ncustom/' . $ctname))
				$callingCommentsParams['overrideTemplatePath'] = $templatePath . '/ncustom/' . $ctname;
		}
		$output = array(
			'status' => 1,
			'rev'    => intval(pluginGetVariable('comments', 'backorder')),
			'data' => comments_show($SQLnews['id'], $commentId, $SQLnews['com'] + 1, $callingCommentsParams),
		);
		print json_encode($output);
		$template['vars']['mainblock'] = '';
		return 1;
	} else {
		// Some errors.
		if ($_REQUEST['ajax']) {
			// AJAX MODE - return error in JSON
			$output = array(
				'status' => 0,
				'data' => $template['vars']['mainblock'],
			);
			print json_encode($output);
			$template['vars']['mainblock'] = '';
		} else {
			// NON-AJAX MODE: show notification and auto-redirect back
			$url = secure_html(($_REQUEST['referer']) ? $_REQUEST['referer'] : '/');
			// Сообщения уже собраны в $template['vars']['mainblock'] вызовами msg() выше.
			// Добавим ссылку для возврата и авто-редирект.
			$linkText = isset($lang['comments:err.redir.url']) ? $lang['comments:err.redir.url'] : 'Вернуться назад';
			$template['vars']['mainblock'] .= "\n<div style=\"margin-top:10px\"><a href=\"{$url}\">{$linkText}</a></div>\n" .
				"<script>setTimeout(function(){ window.location.href='" . str_replace("'", "\\'", $url) . "'; }, 3000);</script>";
		}
	}
}
// Show dedicated page for comments
function plugin_comments_show()
{
	global $config, $catz, $mysql, $catmap, $tpl, $template, $lang, $SUPRESS_TEMPLATE_SHOW, $userROW, $TemplateCache, $SYSTEM_FLAGS;
	// Load lang file, that is required for [hide]..[/hide] block
	$lang = LoadLang('news', 'site');
	$SYSTEM_FLAGS['info']['title']['group'] = $lang['comments:header.title'];
	include_once(root . "/plugins/comments/inc/comments.show.php");
	// Try to fetch news
	$newsID = intval($_REQUEST['news_id']);
	if (!$newsID || !is_array($newsRow = $mysql->record("select * from " . prefix . "_news where id = " . $newsID))) {
		error404();
		return;
	}
	$SYSTEM_FLAGS['info']['title']['item'] = $newsRow['title'];
	// Prepare params for call
	// AJAX is turned off by default
	$callingCommentsParams = array('noajax' => 1, 'outprint' => true);
	// Set default template path [from site template / comments plugin subdirectory]
	$templatePath = tpl_site . 'plugins/comments';
	$fcat = array_shift(explode(",", $newsRow['catid']));
	// Check if there is a custom mapping
	if ($fcat && $catmap[$fcat] && ($ctname = $catz[$catmap[$fcat]]['tpl'])) {
		// Check if directory exists
		if (is_dir(tpl_site . 'ncustom/' . $ctname))
			$callingCommentsParams['overrideTemplatePath'] = tpl_site . 'ncustom/' . $ctname;
		$templatePath = tpl_site . 'ncustom/' . $ctname;
	}
	// Check if we need pagination
	$page = 0;
	$pageCount = 0;
	// If we have comments more than for one page - activate pagination
	$multi_scount = intval(pluginGetVariable('comments', 'multi_scount'));
	if (($multi_scount > 0) && ($newsRow['com'] > $multi_scount)) {
		// Page count
		$pageCount = ceil($newsRow['com'] / $multi_scount);
		// Check if user wants to access not first page
		$page = intval($_REQUEST['page']);
		if ($page < 1) $page = 1;
		$callingCommentsParams['limitCount'] = intval(pluginGetVariable('comments', 'multi_scount'));
		$callingCommentsParams['limitStart'] = ($page - 1) * intval(pluginGetVariable('comments', 'multi_scount'));
	}
	// Pass total number of comments
	$callingCommentsParams['total'] = $newsRow['com'];
	// Show comments
	$tcvars = array();
	$tcvars['vars']['entries'] = comments_show($newsID, 0, 0, $callingCommentsParams);
	if ($pageCount > 1) {
		$paginationParams = checkLinkAvailable('comments', 'show') ?
			array('pluginName' => 'comments', 'pluginHandler' => 'show', 'params' => array('news_id' => $newsID), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
			array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'comments', 'handler' => 'show'), 'xparams' => array('news_id' => $newsID), 'paginator' => array('page', 1, false));
		templateLoadVariables(true);
		$navigations = $TemplateCache['site']['#variables']['navigation'];
		$tcvars['vars']['more_comments'] = generatePagination($page, 1, $pageCount, 10, $paginationParams, $navigations, true);
		$tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '$1';
	} else {
		$tcvars['vars']['more_comments'] = '';
		$tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '';
	}
	// Enable AJAX in case if we are on last page
	if ($page == $pageCount)
		$callingCommentsParams['noajax'] = 0;
	$allowCom = $newsRow['allow_com'];
	// Show form for adding comments
	if ($newsRow['allow_com'] && (!pluginGetVariable('comments', 'regonly') || is_array($userROW))) {
		$tcvars['vars']['form'] = comments_showform($newsID, $callingCommentsParams);
		$tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '';
		$tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
	} else {
		$tcvars['vars']['form'] = '';
		$tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = $allowCom ? '$1' : '';
		$tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = $allowCom ? '' : '$1';
	}
	// Use Twig template from plugin
	global $twig;
	$templateFile = root . '/plugins/comments/tpl/comments.container.tpl';
	if (file_exists($templateFile)) {
		$templateContent = file_get_contents($templateFile);
		$twigTemplate = $twig->createTemplate($templateContent);
		$tcvars['vars']['is_external'] = true;
		$tcvars['vars']['link'] = newsGenerateLink($newsRow);
		$tcvars['vars']['title'] = secure_html($newsRow['title']);
		$tcvars['vars']['regonly'] = $allowCom && !$tcvars['vars']['form'];
		$tcvars['vars']['commforbidden'] = !$allowCom;
		loadPluginLang('comments', 'site', '', '', ':');
		$tcvars['vars']['lang'] = $lang;
		$template['vars']['mainblock'] .= $twigTemplate->render($tcvars['vars']);
	} else {
		// Fallback to old template
		$tcvars['vars']['link'] = newsGenerateLink($newsRow);
		$tcvars['vars']['title'] = secure_html($newsRow['title']);
		$tcvars['regx']['[\[comheader\](.*)\[/comheader\]]'] = ($newsRow['com']) ? '$1' : '';
		$tpl->template('comments.external', $templatePath);
		$tpl->vars('comments.external', $tcvars);
		$template['vars']['mainblock'] .= $tpl->show('comments.external');
	}
}
// Delete comment
function plugin_comments_delete()
{
	global $mysql, $config, $userROW, $lang, $tpl, $template, $SUPRESS_MAINBLOCK_SHOW, $SUPRESS_TEMPLATE_SHOW;
	$output = array();
	$params = array();
	// First: check if user have enough permissions
	if (!is_array($userROW) || ($userROW['status'] > 2) || ($_GET['uT'] != genUToken(intval($_REQUEST['id'])))) {
		// Not allowed
		$output['status'] = 0;
		$output['data'] = $lang['perm.denied'];
	} else {
		// Second: check if this comment exists
		$comid = intval($_REQUEST['id']);
		if (($comid) && ($row = $mysql->record("select * from " . prefix . "_comments where id=" . db_squote($comid)))) {
			$mysql->query("delete from " . prefix . "_comments where id=" . db_squote($comid));
			$mysql->query("update " . uprefix . "_users set com=com-1 where id=" . db_squote($row['author_id']));
			$mysql->query("update " . prefix . "_news set com=com-1 where id=" . db_squote($row['post']));
			$output['status'] = 1;
			$output['data'] = $lang['comments:deleted.text'];
			$params['newsid'] = $row['post'];
		} else {
			$output['status'] = 0;
			$output['data'] = $lang['comments:err.nocomment'];
		}
	}
	$SUPRESS_TEMPLATE_SHOW = 1;
	// Check if we run AJAX request
	if ($_REQUEST['ajax']) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
	} else {
		// NON-AJAX mode
		// Fetch news record
		if ($nrow = $mysql->record("select * from " . prefix . "_news where id = " . db_squote($params['newsid']))) {
			$url = newsGenerateLink($nrow);
		} else {
			$url = $config['home_url'];
		}
		// Показать уведомление и авто-редирект
		$msgType = $output['status'] ? 'info' : 'error';
		msg(array('type' => $msgType, 'text' => $output['data']));
		$linkText = $output['status'] ? (isset($lang['comments:deleted.url']) ? $lang['comments:deleted.url'] : 'Вернуться к новости') : (isset($lang['comments:err.redir.url']) ? $lang['comments:err.redir.url'] : 'Вернуться назад');
		$template['vars']['mainblock'] .= "\n<div style=\"margin-top:10px\"><a href=\"{$url}\">{$linkText}</a></div>\n" .
			"<script>setTimeout(function(){ window.location.href='" . str_replace("'", "\\'", $url) . "'; }, 2000);</script>";
	}
}
// Edit comment
function plugin_comments_edit()
{
	global $mysql, $config, $userROW, $lang, $parse, $SUPRESS_TEMPLATE_SHOW;
	$SUPRESS_TEMPLATE_SHOW = 1;
	$output = array();
	$comment_id = intval($_REQUEST['id']);
	// Проверка прав
	if (!is_array($userROW) || ($userROW['status'] > 2)) {
		$output['status'] = 0;
		$output['data'] = 'Недостаточно прав';
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
	}
	if ($_REQUEST['action'] == 'get') {
		// Получение текста комментария
		if ($row = $mysql->record("select * from " . prefix . "_comments where id=" . db_squote($comment_id))) {
			$output['status'] = 1;
			$output['text'] = str_replace('<br />', "\n", $row['text']);
		} else {
			$output['status'] = 0;
			$output['data'] = 'Комментарий не найден';
		}
	} elseif ($_REQUEST['action'] == 'save') {
		// Сохранение отредактированного комментария
		if ($row = $mysql->record("select * from " . prefix . "_comments where id=" . db_squote($comment_id))) {
			$new_text = secure_html(trim($_POST['text']));
			$new_text = str_replace("\r\n", "<br />", $new_text);
			$edit_date = time() + ($config['date_adjust'] * 60);
			// Проверяем наличие колонки edit_date, чтобы избежать SQL-ошибки на старых БД
			$col = $mysql->record("SHOW COLUMNS FROM " . prefix . "_comments LIKE 'edit_date'");
			if ($col) {
				$updOK = $mysql->query("update " . prefix . "_comments set text=" . db_squote($new_text) . ", edit_date=" . db_squote($edit_date) . " where id=" . db_squote($comment_id));
			} else {
				// Колонки нет — обновляем только текст
				$updOK = $mysql->query("update " . prefix . "_comments set text=" . db_squote($new_text) . " where id=" . db_squote($comment_id));
			}
			if (!$updOK) {
				$output['status'] = 0;
				$output['data'] = 'Не удалось сохранить комментарий (ошибка БД)';
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode($output);
				exit;
			}
			// Формируем HTML для отображения
			$display_text = $new_text;
			if ($config['blocks_for_reg']) {
				$display_text = $parse->userblocks($display_text);
			}
			if ($config['use_bbcodes']) {
				$display_text = $parse->bbcodes($display_text);
			}
			if ($config['use_htmlformatter']) {
				$display_text = $parse->htmlformatter($display_text);
			}
			if ($config['use_smilies']) {
				$display_text = $parse->smilies($display_text);
			}
			$timestamp = pluginGetVariable('comments', 'timestamp');
			if (!$timestamp) $timestamp = 'j.m.Y - H:i';
			$edit_info = '<br/><small><i>Изменено: ' . LangDate($timestamp, $edit_date) . '</i></small>';
			$output['status'] = 1;
			$output['html'] = $display_text . $edit_info;
		} else {
			$output['status'] = 0;
			$output['data'] = 'Комментарий не найден';
		}
	}
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($output);
	exit;
}
// Comments moderation
function plugin_comments_moderation()
{
	global $mysql, $userROW, $lang, $twig, $SUPRESS_TEMPLATE_SHOW, $main_admin;
	$SUPRESS_TEMPLATE_SHOW = 1;
	// Check permissions
	if (!is_array($userROW) || ($userROW['status'] > 2)) {
		msg(array("type" => "error", "text" => $lang['perm.denied']));
		return;
	}
	// Check if moderation is enabled
	if (!pluginGetVariable('comments', 'moderation')) {
		msg(array("type" => "info", "text" => $lang['comments:moderation.disabled']));
		return;
	}
	// Handle actions
	if ($_POST['action']) {
		switch ($_POST['action']) {
			case 'approve':
				if ($_POST['comments'] && is_array($_POST['comments'])) {
					foreach ($_POST['comments'] as $comment_id) {
						$comment_id = intval($comment_id);
						$mysql->query("UPDATE " . prefix . "_comments SET moderated=1 WHERE id=" . db_squote($comment_id));
						// Update comment counter in news
						if ($comment = $mysql->record("SELECT post FROM " . prefix . "_comments WHERE id=" . db_squote($comment_id))) {
							$mysql->query("UPDATE " . prefix . "_news SET com=com+1 WHERE id=" . db_squote($comment['post']));
						}
					}
					msg(array("type" => "info", "text" => $lang['comments:moderation.approved']));
				}
				break;
			case 'delete':
				if ($_POST['comments'] && is_array($_POST['comments'])) {
					foreach ($_POST['comments'] as $comment_id) {
						$comment_id = intval($comment_id);
						$mysql->query("DELETE FROM " . prefix . "_comments WHERE id=" . db_squote($comment_id));
					}
					msg(array("type" => "info", "text" => $lang['comments:moderation.deleted']));
				}
				break;
		}
	}
	// Get pending comments
	$comments = array();
	foreach ($mysql->select("SELECT c.*, n.title as news_title, n.alt_name, n.catid FROM " . prefix . "_comments c LEFT JOIN " . prefix . "_news n ON c.post = n.id WHERE c.moderated=0 ORDER BY c.postdate DESC") as $row) {
		$row['text_preview'] = strip_tags(str_replace('<br />', ' ', $row['text']));
		if (strlen($row['text_preview']) > 100) {
			$row['text_preview'] = substr($row['text_preview'], 0, 100) . '...';
		}
		$row['date_formatted'] = LangDate('j.m.Y H:i', $row['postdate']);
		// Generate proper news link
		$row['news_link'] = newsGenerateLink($row);
		$comments[] = $row;
	}
	// Load language
	loadPluginLang('comments', 'admin', '', '', ':');
	// Use Twig template from plugin
	$templatePath = root . '/plugins/comments/admin/tpl/comments_moderation.tpl';
	if (file_exists($templatePath)) {
		$templateContent = file_get_contents($templatePath);
		$template = $twig->createTemplate($templateContent);
		$main_admin = $template->render(array(
			'comments' => $comments,
			'count' => count($comments),
			'php_self' => 'admin.php',
			'lang' => $lang
		));
	} else {
		$main_admin = '<div class="alert alert-error">Template not found</div>';
	}
}
// Add comments variable to news template
function comments_add_to_news($newsID, &$tvars)
{
	if (pluginGetVariable('comments', 'enabled')) {
		ob_start();
		comments_show($newsID);
		comments_showform($newsID);
		$comments_output = ob_get_clean();
		$tvars['vars']['comments'] = $comments_output;
	}
}
// Register filter for news display
if (!class_exists('CommentsNewsFilter')) {
	class CommentsNewsFilter
	{
		function showNews($newsID, $row, &$tvars)
		{
			comments_add_to_news($newsID, $tvars);
		}
	}
}
if (!isset($PFILTERS['news'])) $PFILTERS['news'] = array();
$PFILTERS['news'][] = new CommentsNewsFilter();
loadPluginLang('comments', 'main', '', '', ':');
register_filter('news', 'comments', new CommentsNewsFilter);
register_admin_filter('categories', 'comments', new CommentsFilterAdminCategories);
register_plugin_page('comments', 'add', 'plugin_comments_add', 0);
register_plugin_page('comments', 'show', 'plugin_comments_show', 0);
register_plugin_page('comments', 'delete', 'plugin_comments_delete', 0);
register_plugin_page('comments', 'edit', 'plugin_comments_edit', 0);
register_plugin_page('comments', 'moderation', 'plugin_comments_moderation', 0);
