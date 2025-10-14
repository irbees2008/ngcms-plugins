<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
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
	loadPluginLang('complain', 'main', '', '', ':');

	// Для AJAX-запросов всегда отдаём только контент (без обёртки темы)
	if (isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1) {
		$SUPRESS_TEMPLATE_SHOW = 1;
	} else {
		$SUPRESS_TEMPLATE_SHOW = pluginGetVariable('complain', 'extform') ? 1 : pluginGetVariable('complain', 'extform');
	}

	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('list.entry', 'list.header', 'infoblock'), 'complain', pluginGetVariable('complain', 'localsource'));
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
	// foreach ($mysql->select("select count(c.id) as ccount, c.id, c.status, c.complete, c.owner_id, (select name from ".uprefix."_users where id = c.owner_id) as owner_name, c.author_id, (select name from ".uprefix."_users where id = c.author_id) as author_name, c.publisher_id, (select name from ".uprefix."_users where id = c.publisher_id) as publisher_name, c.publisher_ip, date(c.date) as date, c.ds_id, c.entry_id, c.error_code, n.alt_name as n_alt_name, n.id as n_id, n.title as n_title, n.catid as n_catid, n.postdate as n_postdate from ".prefix."_complain c left join ".prefix."_news n on c.entry_id = n.id where ".join(" AND ", $where)." group by c.ds_id, c.entry_id, c.error_code") as $crow) {
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
	$tvars['vars'] = array('entries' => $entries, 'status_options' => $sselect, 'form_url' => generateLink('core', 'plugin', array('plugin' => 'complain', 'handler' => 'update')), 'ETEXT' => json_encode($etext));
	$tpl->vars('list.header', $tvars);
	$template['vars']['mainblock'] = $tpl->show('list.header');
}

function plugin_complain_add()
{

	global $template, $tpl, $lang, $mysql, $userROW;
	global $SUPRESS_TEMPLATE_SHOW;
	loadPluginLang('complain', 'main', '', '', ':');
	$SUPRESS_TEMPLATE_SHOW = 1;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('ext.form', 'infoblock'), 'complain', pluginGetVariable('complain', 'localsource'));
	// Check if we shouldn't show block for unregs
	if ((!is_array($userROW)) && (!pluginGetVariable('complain', 'allow_unreg'))) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$msg = $lang['complain:error.regonly'];
		if (!(isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1)) {
			$msg .= $lang['complain:link.close'];
		}
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $msg)));
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
	loadPluginLang('complain', 'main', '', '', ':');
	$SUPRESS_TEMPLATE_SHOW = 1;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('ext.form', 'infoblock', 'error.noentry', 'form.confirm'), 'complain', pluginGetVariable('complain', 'localsource'));
	// Check if we shouldn't show block for unregs
	if ((!is_array($userROW)) && (!pluginGetVariable('complain', 'allow_unreg'))) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$msg = $lang['complain:error.regonly'];
		if (!(isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1)) {
			$msg .= $lang['complain:link.close'];
		}
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $msg)));
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
		$msg = $lang['complain:error.noentry'];
		if (!(isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1)) {
			$msg .= $lang['complain:link.close'];
		}
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $msg)));
		$template['vars']['mainblock'] = $tpl->show('infoblock');

		return;
	}
	$errid = intval($_REQUEST['error']);
	$errtext = plugin_complain_resolve_error($errid);
	// Do not accept unresolvable errors
	if ($errtext === null) {
		$tpl->template('infoblock', $tpath['infoblock']);
		$msg = $lang['complain:error.unresolvable'];
		if (!(isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1)) {
			$msg .= $lang['complain:link.close'];
		}
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $msg)));
		$template['vars']['mainblock'] = $tpl->show('infoblock');

		return;
	}
	// Check reporter notification mode
	if (is_array($userROW)) {
		$flagNotify = ((pluginGetVariable('complain', 'inform_reporter') == '1') || ((pluginGetVariable('complain', 'inform_reporter') == '2') && ($_REQUEST['notify']))) ? 1 : 0;
		$publisherMail = $userROW['mail'];
	} else {
		if ((strlen($_REQUEST['mail']) < 70) && (preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $_REQUEST['mail']))) {
			$publisherMail = $_REQUEST['mail'];
		} else {
			$publisherMail = '';
		}
		$flagNotify = (pluginGetVariable('complain', 'allow_unreg_inform') && $publisherMail) ? 1 : 0;
	}
	// Text error description
	$errorText = ((is_array($userROW) && (pluginGetVariable('complain', 'allow_text') == 1)) || (pluginGetVariable('complain', 'allow_text') == 2)) ? $_REQUEST['error_text'] : '';
	// Fill flags variable
	$flags = $flagNotify ? 'N' : '';
	// Let's make a report
	$mysql->query("insert into " . prefix . "_complain (author_id, publisher_id, publisher_ip, publisher_mail, date, ds_id, entry_id, error_code, error_text, flags) values (" . db_squote($cdata['author_id']) . ", " . db_squote(is_array($userROW) ? $userROW['id'] : 0) . ", " . db_squote($ip) . ", " . db_squote($publisherMail) . ", now(), " . db_squote($cdata['ds_id']) . ", " . db_squote($cdata['id']) . ", " . db_squote($errid) . ", " . db_squote($errorText) . ", " . db_squote($flags) . ")");
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
	$msg = $lang['complain:info.accepted'];
	if (!(isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1)) {
		$msg .= $lang['complain:link.close'];
	}
	$tpl->vars('infoblock', array('vars' => array('infoblock' => $msg)));
	$template['vars']['mainblock'] = $tpl->show('infoblock');
}

function plugin_complain_update()
{

	global $template, $config, $tpl, $mysql, $lang, $userROW;
	global $SUPRESS_TEMPLATE_SHOW;
	loadPluginLang('complain', 'main', '', '', ':');
	$SUPRESS_TEMPLATE_SHOW = 1;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('infoblock'), 'complain', pluginGetVariable('complain', 'localsource'));
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
		// В AJAX-режиме сразу вернём таблицу, без инфоблока и ссылок
		if (isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1) {
			plugin_complain_screen();
			return 1;
		}
		$tpl->template('infoblock', $tpath['infoblock']);
		$tpl->vars('infoblock', array('vars' => array('infoblock' => $lang['complain:info.nothing'] . $link_admin)));
		$template['vars']['mainblock'] = $tpl->show('infoblock');

		return 1;
	}
	// Populate admins list
	$admins = explode("\n", pluginGetVariable('complain', 'admins'));
	// ** Check requested actions **
	// Change ownership
	if ($_REQUEST['setowner'] == '1') {
		// Admins can change all ownerships, users - can set ownership only for their news
		// that are not already owned by anyone
		$mysql->query("update " . prefix . "_complain set owner_id = " . db_squote($userROW['id']) . " where id in (" . join(",", $ilist) . ")" . (($userROW['status'] > 1 && (!in_array($userROW['name'], $admins))) ? ' and owner_id = 0 and author_id=' . db_squote($userROW['id']) : ''));
	}
	// Change status [ ONLY FOR NEWS OWNED BY ME ]
	if ($_REQUEST['setstatus'] == '1') {
		foreach ($mysql->select("select * from " . prefix . "_complain where id in (" . join(", ", $ilist) . ") and owner_id = " . db_squote($userROW['id'])) as $irow) {
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
		}
	}
	$tpl->template('infoblock', $tpath['infoblock']);
	$msg = $lang['complain:info.executed'];
	if (!(isset($_REQUEST['ajax']) && intval($_REQUEST['ajax']) == 1)) {
		$msg .= $link_admin;
	}
	$tpl->vars('infoblock', array('vars' => array('infoblock' => $msg)));
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
		// Show only in full news
		if ($mode['style'] != 'full') {
			$tvars['vars']['plugin_complain'] = '';

			return 1;
		}
		// Check if we shouldn't show block for unregs
		if ((!is_array($userROW)) && (!pluginGetVariable('complain', 'allow_unreg'))) {
			$tvars['vars']['plugin_complain'] = '';

			return 1;
		}
		// Определяем пути к шаблонам
		$tpath = locatePluginTemplates(array('int.link'), 'complain', pluginGetVariable('complain', 'localsource'));
		// Подключаем фронтенд-скрипт модалки (из плагина), без правок темы
		if (function_exists('register_htmlvar')) {
			plugin_complain_register_front_js();
		}
		// Always use external form link (opens in AJAX modal)
		$link = generateLink('core', 'plugin', array('plugin' => 'complain', 'handler' => 'add'), array('ds_id' => '1', 'entry_id' => $newsID));
		$txvars = array();
		$txvars['vars'] = array('link' => $link);
		$tpl->template('int.link', $tpath['int.link']);
		$tpl->vars('int.link', $txvars);
		$tvars['vars']['plugin_complain'] = $tpl->show('int.link');
	}
}

register_filter('news', 'complain', new ComplainNewsFilter);
register_plugin_page('complain', '', 'plugin_complain_screen', 0);
register_plugin_page('complain', 'add', 'plugin_complain_add', 0);
register_plugin_page('complain', 'post', 'plugin_complain_post', 0);
register_plugin_page('complain', 'update', 'plugin_complain_update', 0);

// ------------------------------
// FRONT JS injection (без правок темы)
// ------------------------------
function plugin_complain_register_front_js()
{
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;

	$js = <<<'JS'
// COMPLAIN plugin JS (injected)
(function(){
	function ensureModal(){
		var modal = document.getElementById('complain-modal');
		if (!modal){
			var wrap = document.createElement('div');
			wrap.id = 'complain-modal';
			wrap.className = 'modal-layer';
			wrap.style.display = 'none';
					wrap.innerHTML = '<div class="modal-box" style="position:absolute;left:50%;top:10%;transform:translateX(-50%);max-width:820px;width:calc(100% - 32px);margin:0 auto;background:#fff;border-radius:6px;box-shadow:0 12px 36px rgba(0,0,0,.25);">\
						<div class="modal-header" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #eee">\
							<div style="font-weight:600">&nbsp;</div>\
							<button type="button" class="modal-clouse" aria-label="Close" style="border:0;background:transparent;font-size:20px;line-height:1;cursor:pointer">×</button>\
						</div>\
						<div class="modal-body" style="padding:12px 14px;max-height:70vh;overflow:auto;">\
							<div class="modal-content"></div>\
						</div>\
			</div>';
			document.body.appendChild(wrap);
			// close handlers
			wrap.addEventListener('click', function(e){
				if (e.target === wrap || e.target.classList.contains('modal-clouse')){
					hideModal();
				}
			});
		}
		return document.getElementById('complain-modal');
	}
	function showOverlay(){
		var ov = document.getElementById('complain-overlay');
		if (ov) { return; }
		ov = document.createElement('div');
		ov.id = 'complain-overlay';
		ov.style.cssText = 'position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9998;display:block;';
		ov.addEventListener('click', hideModal);
		document.body.appendChild(ov);
	}
	function hideOverlay(){
		var ov = document.getElementById('complain-overlay');
		if (ov){ ov.parentNode.removeChild(ov); }
		// Чистим возможные старые оверлеи предыдущей версии (имели класс .shadow-bg)
		try {
			var olds = document.querySelectorAll('.shadow-bg');
			olds.forEach(function(el){ if (el && el.parentNode) el.parentNode.removeChild(el); });
		} catch(e){}
	}
	function showToastSafe(msg, type){
		try {
			// Предпочитаем Bootstrap Notify (как в комментариях)
			if (typeof window !== 'undefined' && window.jQuery && typeof jQuery.notify === 'function') {
				jQuery.notify({ message: msg }, { type: (type||'success'), placement: { from: 'top', align: 'right' } });
				return true;
			}
			// Кастомный showToast
			if (typeof showToast === 'function') { showToast(msg, { type: type || 'info' }); return true; }
			// Toastr
			if (window.toastr && typeof toastr.success === 'function') { (type==='error'?toastr.error:toastr.success)(msg); return true; }
		} catch(e){}
		// Fallback: lightweight inline notice
		try {
			var n = document.createElement('div');
			n.textContent = msg;
			n.style.cssText = 'position:fixed;left:50%;top:20px;transform:translateX(-50%);background:#2c7be5;color:#fff;padding:8px 12px;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.2);z-index:10000;font:14px/1.3 system-ui,-apple-system,Segoe UI,Roboto,Arial';
			if (type==='error'){ n.style.background = '#e55353'; }
			document.body.appendChild(n);
			setTimeout(function(){ n.style.transition='opacity .3s'; n.style.opacity='0'; setTimeout(function(){ n.remove(); }, 300); }, 1400);
			return true;
		} catch(e){}
		return false;
	}
		function showModal(html){
		var modal = ensureModal();
		modal.querySelector('.modal-content').innerHTML = html;
			showOverlay();
			modal.style.cssText += ';display:block;position:fixed;left:0;top:0;right:0;bottom:0;z-index:9999;overflow:auto;padding:10px;';
			// focus first focusable element
			setTimeout(function(){
				var sel = modal.querySelector('select, textarea, input, button');
				if (sel && sel.focus) sel.focus();
			}, 30);
	}
	function hideModal(){
		var modal = document.getElementById('complain-modal');
		if (modal){ modal.style.display = 'none'; }
		hideOverlay();
	}
		// Close on ESC
		document.addEventListener('keydown', function(e){ if (e.key === 'Escape'){ hideModal(); } });
	function ajaxGet(url, cb){
		var xhr = new XMLHttpRequest();
		xhr.open('GET', url, true);
		xhr.onreadystatechange = function(){ if (xhr.readyState==4){ cb(xhr.status, xhr.responseText); } };
		xhr.send();
	}
	function ajaxPost(url, data, cb){
		var xhr = new XMLHttpRequest();
		xhr.open('POST', url, true);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xhr.onreadystatechange = function(){ if (xhr.readyState==4){ cb(xhr.status, xhr.responseText); } };
		xhr.send(data);
	}
	function serialize(form){
		var p=[]; var els=form.elements;
		for (var i=0;i<els.length;i++){
			var e=els[i]; if (!e.name) continue;
			if ((e.type==='checkbox'||e.type==='radio') && !e.checked) continue;
			p.push(encodeURIComponent(e.name)+'='+encodeURIComponent(e.value));
		}
		return p.join('&');
	}

	// Delegate clicks on links with .complain-open
	document.addEventListener('click', function(e){
		var a = e.target.closest('a.complain-open');
		if (!a) return;
		e.preventDefault();
		var url = a.getAttribute('href');
		if (url.indexOf('ajax=1') === -1){ url += (url.indexOf('?')>-1 ? '&' : '?')+'ajax=1'; }
		ajaxGet(url, function(status, html){ showModal(html); });
	});

	// Delegate submits from complain forms
	document.addEventListener('submit', function(e){
			var f = e.target;
		if (f && f.classList && f.classList.contains('complain-form') && f.getAttribute('data-ajax') === 'true'){
			e.preventDefault();
			var url = f.getAttribute('action');
			var data = serialize(f);
			// Принудительно ajax=1 на submit
			if (url.indexOf('ajax=1') === -1){ url += (url.indexOf('?')>-1 ? '&' : '?')+'ajax=1'; }
			ajaxPost(url, data, function(status, html){
				var modal = document.getElementById('complain-modal');
				var isAdminList = f.getAttribute('data-list') === '1' || /handler=update/.test(url);
				var isExecuted = /(complain:info\.executed|Ваш запрос выполнен)/i.test(html);
				var isNothing = /(complain:info\.nothing|Вы не выбрали ни одного отчёта)/i.test(html);
				var isAccepted = /(complain:info\.accepted|Ваша жалоба принята|жалоба принята)/i.test(html);

				// Успех в админ-списке: показать уведомление и перезагрузить таблицу без показа инфоблока
				if (isAdminList && isExecuted){
					showToastSafe('Выполнено', 'success');
					var listUrl = url.replace(/([?&])handler=update(&|$)/,'$1').replace(/[?&]$/,'');
					listUrl = listUrl.replace(/\/update(\?|$)/,'$1');
					if (listUrl.indexOf('ajax=1') === -1){ listUrl += (listUrl.indexOf('?')>-1 ? '&' : '?')+'ajax=1'; }
					ajaxGet(listUrl, function(st2, html2){ if (modal){ modal.querySelector('.modal-content').innerHTML = html2; } });
					return;
				}

				// Успех в публичной форме: закрыть модалку и показать уведомление, не выводя инфоблок в модалке
				if (!isAdminList && isAccepted){
					showToastSafe('Жалоба отправлена', 'success');
					hideModal();
					return;
				}

				// Иначе (ошибка/валидация) — показать ответ в модалке
				// Если ничего не выбрано, просто перезагрузим список и покажем notify
				if (isAdminList && isNothing){
					showToastSafe('Вы не выбрали ни одного отчёта', 'info');
					var listUrl = url.replace(/([?&])handler=update(&|$)/,'$1').replace(/[?&]$/,'');
					listUrl = listUrl.replace(/\/update(\?|$)/,'$1');
					if (listUrl.indexOf('ajax=1') === -1){ listUrl += (listUrl.indexOf('?')>-1 ? '&' : '?')+'ajax=1'; }
					ajaxGet(listUrl, function(st2, html2){ if (modal){ modal.querySelector('.modal-content').innerHTML = html2; } });
					return;
				}
				if (modal){ modal.querySelector('.modal-content').innerHTML = html; }
			});
		}
	});

	// Кнопка с data-close-modal
	document.addEventListener('click', function(e){
		var btn = e.target.closest('[data-close-modal]');
		if (!btn) return;
		e.preventDefault();
		hideModal();
	});
})();
JS;

	register_htmlvar('plain', '<script>' . $js . '</script>');
}

// ------------------------------
// USERMENU link with badge for admins
// ------------------------------
function plugin_complain_usermenu()
{
	global $userROW, $mysql;
	if (!is_array($userROW) || ($userROW['status'] != 1)) {
		return;
	}
	// Count open complaints
	// Хотим именно "новые" жалобы: статус 0 (и по умолчанию они не complete)
	$rec = $mysql->record("SELECT COUNT(*) as cnt FROM " . prefix . "_complain WHERE status = 0 AND complete = 0");
	$cnt = intval($rec ? $rec['cnt'] : 0);
	$link = generateLink('core', 'plugin', array('plugin' => 'complain'), array('ajax' => '1'));

	// Inject front JS (modal) and append link into user menu via inline script
	plugin_complain_register_front_js();
	// Показываем число всегда, даже если 0
	$badge = ' (' . $cnt . ')';
	$inject = <<<HTML
<script>(function(){
	function addLink(){
		var list = document.querySelector('#profile .profile-block ul');
		if (!list) return;
		// Если ссылка уже есть в шаблоне, просто обновим текст и выйдем
		var existing = list.querySelector('a.complain-open');
		if (existing){ existing.textContent = 'Жалобы$badge'; existing.id = existing.id || 'complain-usermenu-link'; return; }
		if (document.getElementById('complain-usermenu-link')) return;
		var li = document.createElement('li');
		var a = document.createElement('a');
		a.id = 'complain-usermenu-link';
		a.href = '$link';
		a.className = 'complain-open';
		a.textContent = 'Жалобы$badge';
		li.appendChild(a);
		// Вставим перед выходом
		var logout = list.querySelector('a[href*="logout"], a[href*="action=logout"]');
		if (logout && logout.parentNode){ list.insertBefore(li, logout.parentNode); } else { list.appendChild(li); }
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', addLink); else addLink();
})();</script>
HTML;
	register_htmlvar('plain', $inject);
}

// ------------------------------
// CoreFilter: прокидываем переменные в usermenu.tpl
// ------------------------------
class ComplainCoreFilter extends CoreFilter
{
	public function showUserMenu(&$tVars)
	{
		global $mysql, $userROW;
		if (!is_array($userROW) || $userROW['status'] != 1) {
			return 0;
		}
		$cnt = intval($mysql->result("SELECT COUNT(*) FROM " . prefix . "_complain WHERE status = 0 AND complete = 0"));
		$link = generateLink('core', 'plugin', array('plugin' => 'complain'), array('ajax' => '1'));
		$tVars['p']['complain']['new_count'] = $cnt;
		$tVars['p']['complain']['link'] = $link;
		return 1;
	}
}

register_filter('core.userMenu', 'complain', new ComplainCoreFilter);

// Зарегистрируем обработчик usermenu
if (function_exists('registerActionHandler')) {
	registerActionHandler('usermenu', 'plugin_complain_usermenu');
}
