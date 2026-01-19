<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, get_ip, sanitize, validate_email};
// Ensure plugin assets are attached globally so links with class `complain-open` work on any page
$home = isset($config['home_url']) ? rtrim($config['home_url'], '/') : '';
$cssPath = $home . '/engine/plugins/complain/tpl/complain.css';
if (function_exists('locatePluginTemplates') && function_exists('register_stylesheet')) {
	$tpathCSS = locatePluginTemplates(array(':complain.css'), 'complain', 1);
	if (is_array($tpathCSS) && !empty($tpathCSS['url::complain.css'])) {
		register_stylesheet($tpathCSS['url::complain.css'] . '/complain.css');
	} else {
		// Fallback: direct URL
		if (function_exists('register_htmlvar')) {
			register_htmlvar('plain', '<link rel="stylesheet" href="' . htmlspecialchars($home . '/engine/plugins/complain/tpl/complain.css', ENT_COMPAT | ENT_HTML401, 'UTF-8') . '">');
		}
	}
} else if (function_exists('register_htmlvar')) {
	// Minimal fallback if APIs are not available
	register_htmlvar('plain', '<link rel="stylesheet" href="' . htmlspecialchars($home . '/engine/plugins/complain/tpl/complain.css', ENT_COMPAT | ENT_HTML401, 'UTF-8') . '">');
}
// Provide correct count endpoint URL for JS based on current routing
if (function_exists('generateLink') && function_exists('register_htmlvar')) {
	$countURL = generateLink('core', 'plugin', array('plugin' => 'complain', 'handler' => 'count'));
	register_htmlvar('plain', '<script>window.NG_COMPLAIN_COUNT_URL = ' . json_encode($countURL) . ';</script>');
}
// Inline loader that appends complain script only (notify подключается в шаблоне сайта)
$loader = '<script type="text/javascript">(function(){'
	. 'if(window.__complainAssetsLoaded){return;} window.__complainAssetsLoaded=1;'
	. 'var head=document.head||document.documentElement;'
	. 'function addCSS(h){var l=document.createElement("link"); l.rel="stylesheet"; l.href=h; head.appendChild(l);}'
	. 'function addJS(s){var sc=document.createElement("script"); sc.src=s; head.appendChild(sc);}'
	. 'addJS(' . json_encode($home . '/engine/plugins/complain/tpl/complain.js') . ');'
	. '})();</script>';
if (function_exists('register_htmlvar')) {
	register_htmlvar('plain', $loader);
} else if (isset($EXTRA_HTML_VARS)) {
	$EXTRA_HTML_VARS[] = ['type' => 'js', 'data' => $home . '/engine/plugins/complain/tpl/complain.js'];
}
// Author     - Author of object under report
// Publisher  - Person, who made a reported. Can be anonymous
// Owner      - Person, who is busy with solving of this problem
// Flags:
// N - inform reporter about status changes of incident
function plugin_complain_resolve_error($id)
{
	foreach (explode("\n", pluginGetVariable('complain', 'errlist')) as $erow) {
		if (preg_match('#^(\d+)\|(.+?)$#', trim($erow), $m) && ($m[1] == $id)) {
			return $m[2];
		}
	}
	return null;
}
function plugin_complain_screen()
{
	global $template, $tpl, $lang, $mysql, $userROW;
	global $SUPRESS_TEMPLATE_SHOW;
	global $EXTRA_HTML_VARS, $config;
	loadPluginLang('complain', 'main', '', '', ':');
	// Attach plugin JS once
	if (!isset($GLOBALS['__complain_js_attached'])) {
		$jsPath = rtrim($config['home_url'], '/') . '/engine/plugins/complain/tpl/complain.js';
		$EXTRA_HTML_VARS[] = ['type' => 'js', 'data' => $jsPath];
		$GLOBALS['__complain_js_attached'] = 1;
	}
	// Suppress outer template only for AJAX requests
	$SUPRESS_TEMPLATE_SHOW = (isset($_REQUEST['ajax']) && intval($_REQUEST['ajax'])) ? 1 : 0;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('list.entry', 'list.header', 'infoblock'), 'complain', 1);
	// No access for unregistered users
	if (!is_array($userROW)) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:error.regonly'])));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return 1;
	}
	// Fetch error list
	$elist = array();
	foreach (explode("\n", pluginGetVariable('complain', 'errlist')) as $erow) {
		if (preg_match('#^(\d+)\|(.+?)$#', trim($erow), $m)) {
			$elist[$m[1]] = $m[2];
		}
	}
	// Show list of complains
	$tpl->template('list.entry', $tpath['list.entry']);
	// Prepare filters
	$where = array('(c.complete = 0)');
	// Populate admins array
	$admins = preg_split("/\r\n|\n/", pluginGetVariable('complain', 'admins'));
	// Non admins will see only complains in which they are involved
	if (($userROW['status'] > 1) && (!in_array($userROW['name'], $admins))) {
		$where[] = '((c.publisher_id = ' . intval($userROW['id']) . ') or (c.owner_id = ' . intval($userROW['id']) . ') or (c.author_id = ' . intval($userROW['id']) . '))';
	}
	$entries = '';
	$etext = array();
	foreach ($mysql->select("select c.id, c.status, c.complete, c.owner_id, (select name from " . uprefix . "_users where id = c.owner_id) as owner_name, c.author_id, (select name from " . uprefix . "_users where id = c.author_id) as author_name, c.publisher_id, (select name from " . uprefix . "_users where id = c.publisher_id) as publisher_name, c.publisher_ip, date(c.date) as date, time(c.date) as time, c.ds_id, c.entry_id, c.error_code, c.error_text, n.alt_name as n_alt_name, n.id as n_id, n.title as n_title, n.catid as n_catid, n.postdate as n_postdate from " . prefix . "_complain c left join " . prefix . "_news n on c.entry_id = n.id where " . join(" AND ", $where)) as $crow) {
		$tvars = array();
		$tvars['vars'] = array(
			'id'             => $crow['id'],
			'date'           => $crow['date'],
			'time'           => $crow['time'],
			'error'          => $elist[$crow['error_code']] . ($crow['error_text'] ? ' (<span style="cursor: pointer;" onclick="alert(ETEXT[' . $crow['id'] . ']);">*</span>)' : ''),
			'ccount'         => ($crow['ccount'] > 1) ? ('(<b>' . $crow['ccount'] . '</b>)') : '',
			'title'          => $crow['n_title'],
			'link'           => newsGenerateLink(array('catid' => $crow['n_catid'], 'alt_name' => $crow['n_alt_name'], 'id' => $crow['n_id'], 'postdate' => $crow['n_postdate']), false, 0, true),
			'publisher_name' => $crow['publisher_id'] ? $crow['publisher_name'] : '',
			'publisher_ip'   => $crow['publisher_ip'],
			'author_name'    => $crow['author_name'],
			'owner_name'     => $crow['owner_id'] ? '<b>' . $crow['owner_name'] . '</b>' : $lang['complain:noowner'],
			'status'         => $lang['complain:status.' . $crow['status']],
		);
		if ($crow['error_text'])
			$etext[$crow['id']] = $crow['error_text'];
		// Check if user have enough permissions to make any changes in this report
		if (($userROW['status'] == 1) ||
			(in_array($userROW['name'], $admins)) ||
			($userROW['id'] == $crow['owner_id']) ||
			(($crow['author_id'] == $userROW['id']) &&
				(($crow['owner_id'] == $userROW['id']) || (!$crow['owner_id']))
			)
		) {
			$tvars['regx']['#\[perm\](.+?)\[\/perm\]#is'] = '$1';
		} else {
			$tvars['regx']['#\[perm\](.+?)\[\/perm\]#is'] = '';
		}
		$tpl->vars('list.entry', $tvars);
		$entries .= $tpl->show('list.entry');
	}
	$sselect = '';
	for ($i = 2; $i < 5; $i++) $sselect .= '<option value="' . $i . '">' . $lang['complain:status.' . $i] . '</option>';
	$tpl->template('list.header', $tpath['list.header']);
	$tvars = array();
	$tvars['regx']['#\[extform\](.*?)\[\/extform\]#is'] = $SUPRESS_TEMPLATE_SHOW ? '$1' : '';
	$tvars['vars'] = array(
		'entries'        => $entries,
		'status_options' => $sselect,
		'form_url'       => generateLink('core', 'plugin', array('plugin' => 'complain', 'handler' => 'update')),
		'refresh_url'    => generateLink('core', 'plugin', array('plugin' => 'complain')), // screen handler URL for refresh
		'ETEXT'          => json_encode($etext)
	);
	$tpl->vars('list.header', $tvars);
	$template['vars']['mainblock'] = $tpl->show('list.header');
}
function plugin_complain_add()
{
	global $template, $tpl, $lang, $mysql, $userROW;
	global $SUPRESS_TEMPLATE_SHOW;
	global $EXTRA_HTML_VARS, $config;
	loadPluginLang('complain', 'main', '', '', ':');
	// Attach plugin JS once
	if (!isset($GLOBALS['__complain_js_attached'])) {
		$jsPath = rtrim($config['home_url'], '/') . '/engine/plugins/complain/tpl/complain.js';
		$EXTRA_HTML_VARS[] = ['type' => 'js', 'data' => $jsPath];
		$GLOBALS['__complain_js_attached'] = 1;
	}
	$SUPRESS_TEMPLATE_SHOW = (isset($_REQUEST['ajax']) && intval($_REQUEST['ajax'])) ? 1 : 0;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('ext.form', 'infoblock'), 'complain', 1);
	// Check if we shouldn't show block for unregs
	if ((!is_array($userROW)) && (!pluginGetVariable('complain', 'allow_unreg'))) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:error.regonly'] . $lang['complain:link.close'])));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return 1;
	}
	// Prepare error list
	$err = '';
	foreach (explode("\n", pluginGetVariable('complain', 'errlist')) as $erow) {
		if (preg_match('#^(\d+)\|(.+?)$#', trim($erow), $m)) {
			$err .= '<option value="' . $m[1] . '">' . htmlspecialchars($m[2], ENT_COMPAT | ENT_HTML401, 'utf-8') . '</option>' . "\n";
		}
	}
	$txvars = array();
	$txvars['vars'] = array('ds_id' => intval($_REQUEST['ds_id']), 'entry_id' => intval($_REQUEST['entry_id']), 'errorlist' => $err);
	$txvars['regx']['#\[notify\](.*?)\[/notify\]#is'] = ((is_array($userROW)) && (pluginGetVariable('complain', 'inform_reporter') == 2)) ? '$1' : '';
	$txvars['regx']['#\[email\](.*?)\[/email\]#is'] = ((!is_array($userROW)) && pluginGetVariable('complain', 'allow_unreg_inform')) ? '$1' : '';
	$txvars['regx']['#\[text\](.*?)\[/text\]#is'] = ((is_array($userROW) && (pluginGetVariable('complain', 'allow_text') == 1)) || (pluginGetVariable('complain', 'allow_text') == 2)) ? '$1' : '';
	$txvars['vars']['form_url'] = generateLink('core', 'plugin', array('plugin' => 'complain', 'handler' => 'post'));
	$tpl->template('ext.form', $tpath['ext.form']);
	$tpl->vars('ext.form', $txvars);
	$template['vars']['mainblock'] = $tpl->show('ext.form');
}
function plugin_complain_post()
{
	global $template, $tpl, $mysql, $lang, $userROW, $ip, $config;
	global $SUPRESS_TEMPLATE_SHOW;
	global $EXTRA_HTML_VARS;
	loadPluginLang('complain', 'main', '', '', ':');
	// Attach plugin JS once
	if (!isset($GLOBALS['__complain_js_attached'])) {
		$jsPath = rtrim($config['home_url'], '/') . '/engine/plugins/complain/tpl/complain.js';
		$EXTRA_HTML_VARS[] = ['type' => 'js', 'data' => $jsPath];
		$GLOBALS['__complain_js_attached'] = 1;
	}
	$SUPRESS_TEMPLATE_SHOW = (isset($_REQUEST['ajax']) && intval($_REQUEST['ajax'])) ? 1 : 0;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('ext.form', 'infoblock', 'error.noentry', 'form.confirm'), 'complain', 1);
	// Check if we shouldn't show block for unregs
	if ((!is_array($userROW)) && (!pluginGetVariable('complain', 'allow_unreg'))) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:error.regonly'] . $lang['complain:link.close'])));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return 1;
	}
	// Check if reference storage & entry exists, fetch entrie's params
	$cdata = array();
	switch (intval($_REQUEST['ds_id'])) {
		case 1:
			if (is_array($dse = $mysql->record("select n.*, u.mail from " . prefix . "_news n left join " . uprefix . "_users u on n.author_id = u.id where n.id = " . db_squote($_REQUEST['entry_id']) . " and n.approve=1"))) {
				$cdata['ds_id'] = intval($_REQUEST['ds_id']);
				$cdata['id'] = $dse['id'];
				$cdata['title'] = $dse['title'];
				$cdata['link'] = newsGenerateLink($dse, false, 0, true);
				$cdata['author'] = $dse['author'];
				$cdata['author_id'] = $dse['author_id'];
				$cdata['author_mail'] = $dse['mail'];
			}
			break;
		default:
	}
	// Check if data entry was not found
	if (!isset($cdata['id'])) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:error.noentry'] . $lang['complain:link.close'])));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return;
	}
	$errid = intval($_REQUEST['error']);
	$errtext = plugin_complain_resolve_error($errid);
	// Do not accept unresolvable errors
	if ($errtext === null) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:error.unresolvable'] . $lang['complain:link.close'])));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return;
	}
	// Check reporter notification mode
	if (is_array($userROW)) {
		$flagNotify = ((pluginGetVariable('complain', 'inform_reporter') == '1') || ((pluginGetVariable('complain', 'inform_reporter') == '2') && ($_REQUEST['notify']))) ? 1 : 0;
		$publisherMail = $userROW['mail'];
	} else {
		// Use validate_email for proper email validation
		if (!empty($_REQUEST['mail']) && validate_email($_REQUEST['mail'])) {
			$publisherMail = $_REQUEST['mail'];
		} else {
			$publisherMail = '';
			if (!empty($_REQUEST['mail'])) {
				logger('complain', 'Invalid email provided: ' . sanitize($_REQUEST['mail']) . ', IP=' . get_ip());
			}
		}
		$flagNotify = (pluginGetVariable('complain', 'allow_unreg_inform') && $publisherMail) ? 1 : 0;
	}
	// Text error description with sanitization
	$errorText = ((is_array($userROW) && (pluginGetVariable('complain', 'allow_text') == 1)) || (pluginGetVariable('complain', 'allow_text') == 2)) ? sanitize($_REQUEST['error_text']) : '';
	// Fill flags variable
	$flags = $flagNotify ? 'N' : '';
	// Let's make a report
	$mysql->query("insert into " . prefix . "_complain (author_id, publisher_id, publisher_ip, publisher_mail, date, ds_id, entry_id, error_code, error_text, flags) values (" . db_squote($cdata['author_id']) . ", " . db_squote(is_array($userROW) ? $userROW['id'] : 0) . ", " . db_squote($ip) . ", " . db_squote($publisherMail) . ", now(), " . db_squote($cdata['ds_id']) . ", " . db_squote($cdata['id']) . ", " . db_squote($errid) . ", " . db_squote($errorText) . ", " . db_squote($flags) . ")");

	// Log complain creation
	$userInfo = is_array($userROW) ? $userROW['name'] : 'guest';
	logger('complain', 'New complaint: error=' . $errid . ', entry=' . $cdata['id'] . ', user=' . $userInfo . ', IP=' . get_ip());
	// Write a mail (if needed)
	if (pluginGetVariable('complain', 'inform_author') || pluginGetVariable('complain', 'inform_admin') || pluginGetVariable('complain', 'inform_admins')) {
		$tmvars = array(
			'title'      => $cdata['title'],
			'link'       => $cdata['link'],
			'link_admin' => generateLink('core', 'plugin', array('plugin' => 'complain'), array(), false, true),
			'error'      => $errtext
		);
		$mail_text = str_replace(
			array('\n', '{title}', '{link}', '{error}', '{link_admin}'),
			array("\n", $tmvars['title'], $tmvars['link'], $errtext, $tmvars['link_admin']),
			$lang['complain:mail.open.body']
		);
		// Inform author
		if (pluginGetVariable('complain', 'inform_author') && strlen($cdata['author_mail'])) {
			zzMail($cdata['author_mail'], $lang['complain:mail.open.subj'], $mail_text, 'text');
			logger('complain', 'Email sent to author: ' . $cdata['author'] . ', email=' . $cdata['author_mail']);
		}
		// Inform site admins
		if (pluginGetVariable('complain', 'inform_admin')) {
			// Send to all admins
			foreach ($mysql->select("select mail from " . uprefix . "_users where status = 1") as $urow) {
				if (strlen($urow['mail'])) {
					zzMail($urow['mail'], $lang['complain:mail.open.subj'], $mail_text, 'text');
				}
			}
		}
		// Inform PLUGIN admins
		if (pluginGetVariable('complain', 'inform_admins')) {
			foreach (explode("\n", pluginGetVariable('complain', 'admins')) as $admin_name) {
				if ($urow = $mysql->record("select mail from " . uprefix . "_users where name = " . db_squote($admin_name))) {
					if (strlen($urow['mail'])) {
						zzMail($urow['mail'], $lang['complain:mail.open.subj'], $mail_text, 'text');
					}
				}
			}
		}
	}
	$tpl->template('infoblock', $tpath['infoblock']);
	// Add hidden success marker for JS to auto-close modal and show toast
	$successMsg = $lang['complain:info.accepted'];
	$marker = '<div class="complain-result" data-status="ok" style="display:none" data-message="' . htmlspecialchars($successMsg, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"></div>';
	$tpl->vars('infoblock', array('vars' => array('infoblock' => $marker . $lang['complain:info.accepted'] . $lang['complain:link.close'])));
	$template['vars']['mainblock'] = $tpl->show('infoblock');
}
function plugin_complain_update()
{
	global $template, $config, $tpl, $mysql, $lang, $userROW;
	global $SUPRESS_TEMPLATE_SHOW;
	global $EXTRA_HTML_VARS;
	loadPluginLang('complain', 'main', '', '', ':');
	// Attach plugin JS once
	if (!isset($GLOBALS['__complain_js_attached'])) {
		$jsPath = rtrim($config['home_url'], '/') . '/engine/plugins/complain/tpl/complain.js';
		$EXTRA_HTML_VARS[] = ['type' => 'js', 'data' => $jsPath];
		$GLOBALS['__complain_js_attached'] = 1;
	}
	$SUPRESS_TEMPLATE_SHOW = (isset($_REQUEST['ajax']) && intval($_REQUEST['ajax'])) ? 1 : 0;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('infoblock'), 'complain', 1);
	$link_admin = str_replace('{link}', generateLink('core', 'plugin', array('plugin' => 'complain')), $lang['complain:link.admin']);
	// Only registered users are allowed here
	if (!is_array($userROW)) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:error.regonly'])));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return 1;
	}
	// Fetch list of affected incidents
	$ilist = array();
	foreach ($_REQUEST as $k => $v) {
		if (preg_match('#^inc_(\d+)$#', $k, $m) && ($v == "1"))
			array_push($ilist, $m[1]);
	}
	// Exit if no incidents are marked
	if (!count($ilist)) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:info.nothing'] . $link_admin)));
		$template['vars']['mainblock'] = $tpl->show('infoblock');
		return 1;
	}
	// Populate admins list and check admin rights
	$admins = explode("\n", pluginGetVariable('complain', 'admins'));
	$isAdmin = ($userROW['status'] == 1) || in_array($userROW['name'], $admins);
	// ** Check requested actions **
	// Change ownership
	if ($_REQUEST['setowner'] == '1') {
		// Admins can change all ownerships, users - can set ownership only for their news
		// that are not already owned by anyone
		$mysql->query("update " . prefix . "_complain set owner_id = " . db_squote($userROW['id']) . " where id in (" . join(",", $ilist) . ")" . (($userROW['status'] > 1 && (!in_array($userROW['name'], $admins))) ? ' and owner_id = 0 and author_id=' . db_squote($userROW['id']) : ''));
		logger('complain', 'Owner changed for incidents: ' . join(',', $ilist) . ', new_owner=' . $userROW['name'] . ', IP=' . get_ip());
	}
	// Change status
	if ($_REQUEST['setstatus'] == '1') {
		$ownerCond = $isAdmin ? '' : (" and owner_id = " . db_squote($userROW['id']));
		foreach ($mysql->select("select * from " . prefix . "_complain where id in (" . join(", ", $ilist) . ")" . $ownerCond) as $irow) {
			$newstatus = intval($_REQUEST['newstatus']);
			// If 'N' flag is set in `flags` field - we should make a notification of an author
			if (strpos($irow['flags'], 'N') !== false) {
				// // If links found and "inform_reporter" flag is ON and status is really changed - send message
				// if (pluginGetVariable('complain', 'inform_reporter') && $irow['publisher_id'] && (is_array($prec = $mysql->record("select * from ".uprefix."_users where id = ".db_squote($irow['publisher_id']))) && $prec['mail']) && ($irow['status'] != $newstatus)) {
				// We're ready to send mail
				// Check if reference storage & entry exists, fetch entrie's params
				$cdata = array();
				switch (intval($irow['ds_id'])) {
					case 1:
						if (is_array($dse = $mysql->record("select n.*, u.mail from " . prefix . "_news n left join " . uprefix . "_users u on n.author_id = u.id where n.id = " . db_squote($irow['entry_id'])))) {
							$cdata['ds_id'] = intval($_REQUEST['ds_id']);
							$cdata['id'] = $dse['id'];
							$cdata['title'] = $dse['title'];
							$cdata['link'] = newsGenerateLink($dse, false, 0, true);
							$cdata['author'] = $dse['author'];
							$cdata['author_id'] = $dse['author_id'];
							$cdata['author_mail'] = $dse['mail'];
						}
						break;
					default:
				}
				$mail_text = str_replace(
					array('\n', '{title}', '{link}', '{status}', '{error}'),
					array("\n", $cdata['title'], $cdata['link'], $lang['complain:status.' . $newstatus], plugin_complain_resolve_error($irow['error_code'])),
					$lang['complain:mail.status.body']
				);
				zzMail($irow['publisher_mail'], $lang['complain:mail.status.subj'], $mail_text, 'html');
			}
			// Update report status
			$mysql->query("update " . prefix . "_complain set status = " . db_squote($newstatus) . ((($newstatus == 3) || ($newstatus == 4)) ? ", complete = 1, rdate = now()" : '') . " where id = " . db_squote($irow['id']));
			logger('complain', 'Status changed: id=' . $irow['id'] . ', old_status=' . $irow['status'] . ', new_status=' . $newstatus . ', user=' . $userROW['name'] . ', IP=' . get_ip());
		}
	}
	$tpl->template('infoblock', $tpath['infoblock']);
	$successMsg = $lang['complain:info.executed'];
	$marker = '<div class="complain-result" data-status="ok" style="display:none" data-message="' . htmlspecialchars($successMsg, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"></div>';
	$tpl->vars('infoblock', array('vars' => array('infoblock' => $marker . $lang['complain:info.executed'] . $link_admin)));
	$template['vars']['mainblock'] = $tpl->show('infoblock');
}
//
// Фильтр новостей (для генерации блока "сообщить о проблеме")
//
class ComplainNewsFilter extends NewsFilter
{
	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{
		global $tpl, $mysql, $userROW;
		// Делаем кнопку жалобы доступной всегда (не только в полной новости)
		// Но при запрете для незарегистрированных - скрываем
		if ((!is_array($userROW)) && (!pluginGetVariable('complain', 'allow_unreg'))) {
			$tvars['vars']['plugin_complain'] = '';
			return 1;
		}
		// Акцент: всегда используем внешнюю форму (AJAX-модалку)
		$tpath = locatePluginTemplates(array('int.link'), 'complain', 1);
		$link = generateLink('core', 'plugin', array('plugin' => 'complain', 'handler' => 'add'), array('ds_id' => '1', 'entry_id' => $newsID, 'ajax' => 1));
		$txvars = array();
		$txvars['vars'] = array('link' => $link);
		$tpl->template('int.link', $tpath['int.link']);
		$tpl->vars('int.link', $txvars);
		$tvars['vars']['plugin_complain'] = $tpl->show('int.link');
		return;
	}
}
register_filter('news', 'complain', new ComplainNewsFilter);
register_plugin_page('complain', '', 'plugin_complain_screen', 0);
register_plugin_page('complain', 'add', 'plugin_complain_add', 0);
register_plugin_page('complain', 'post', 'plugin_complain_post', 0);
register_plugin_page('complain', 'update', 'plugin_complain_update', 0);
// JSON endpoint: unresolved complains count for current user/admin
function plugin_complain_count()
{
	global $mysql, $userROW;
	$where = array('(c.complete = 0)');
	$admins = preg_split("/\r\n|\n/", pluginGetVariable('complain', 'admins'));
	if (!is_array($userROW)) {
		// guests: no access
		$where[] = '0=1';
	} else if (($userROW['status'] > 1) && (!in_array($userROW['name'], $admins))) {
		$where[] = '((c.publisher_id = ' . intval($userROW['id']) . ') or (c.owner_id = ' . intval($userROW['id']) . ') or (c.author_id = ' . intval($userROW['id']) . '))';
	}
	$cnt = intval($mysql->result("select count(*) from " . prefix . "_complain c where " . join(" AND ", $where)));
	@header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array('count' => $cnt));
	exit;
}
register_plugin_page('complain', 'count', 'plugin_complain_count', 0);
