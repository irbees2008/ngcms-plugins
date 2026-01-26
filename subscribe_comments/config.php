<?php
if (!defined('NGCMS')) {
	exit('HAL');
}
pluginsLoadConfig();
LoadPluginLang('subscribe_comments', 'config', '', '', '#');
$get_params = parse_url($_SERVER['HTTP_REFERER']);
$get_params = $get_params['query'];
parse_str($get_params, $get_params);
switch ($_REQUEST['action']) {
	case 'list_subscribe':
		show_list_subscribe();
		break;
	case 'list_subscribe_post':
		show_list_subscribe_post();
		break;
	case 'edit_subscribe':
		edit_subscribe();
		break;
	case 'modify':
		modify();
		if ($get_params['action'] == 'list_subscribe') show_list_subscribe();
		else show_list_subscribe_post();
		break;
	default:
		main();
}
function show_list_subscribe()
{

	global $twig, $mysql, $lang, $config;
	$tpath = locatePluginTemplates(array('main', 'list_subscribe', 'list_entries'), 'subscribe_comments', 1);
	$news_per_page = pluginGetVariable('subscribe_comments', 'admin_count');
	if (($news_per_page < 2) || ($news_per_page > 2000)) $news_per_page = 2;
	$pageNo = intval($_REQUEST['page']) ? $_REQUEST['page'] : 0;
	if ($pageNo < 1) $pageNo = 1;
	if (!$start_from) $start_from = ($pageNo - 1) * $news_per_page;
	$count = $mysql->result('SELECT count(id) from ' . prefix . '_subscribe_comments');
	$countPages = ceil($count / $news_per_page);
	foreach ($mysql->select('SELECT *, c.id as nid from ' . prefix . '_subscribe_comments AS c left join ' . prefix . '_news AS s on c.news_id=s.id ORDER BY c.id DESC LIMIT ' . $start_from . ', ' . $news_per_page) as $row) {
		$url = admin_url . "/admin.php?mod=news&amp;action=edit&amp;id=" . $row['news_id'];
		$tEntries[] = array(
			'id'        => $row['nid'],
			'page_name' => $row['title'],
			'page_url'  => $url,
			'email'     => $row['user_email'],
		);
	}
	$delayed_send = pluginGetVariable('subscribe_comments', 'delayed_send');

	$pvars = array(
		'pagesss' => generateAdminPagelist(array('current' => $pageNo, 'count' => $countPages, 'url' => admin_url . '/admin.php?mod=extra-config&plugin=subscribe_comments&action=list_subscribe' . ($_REQUEST['news_per_page'] ? '&news_per_page=' . $news_per_page : '') . ($_REQUEST['author'] ? '&author=' . $_REQUEST['author'] : '') . ($_REQUEST['sort'] ? '&sort=' . $_REQUEST['sort'] : '') . ($postdate ? '&postdate=' . $postdate : '') . ($author ? '&author=' . $author : '') . ($status ? '&status=' . $status : '') . '&page=%page%')),
		'entries' => $tEntries
	);

	$xt = $twig->loadTemplate($tpath['list_subscribe'] . 'list_subscribe.tpl');
	$entriesOutput = $xt->render($pvars);

	$tvars = array(
		'entries_cron' => '',
		'entries'      => $entriesOutput,
		'action'       => 'Список подписок',
		'tab_general_active' => '',
		'tab_list_active' => 'active',
		'tab_post_active' => '',
		'hide_delayed' => ($delayed_send != 1),
		'lang' => $lang
	);

	$xt = $twig->loadTemplate($tpath['main'] . 'main.tpl');
	print $xt->render($tvars);
}

function show_list_subscribe_post()
{

	global $twig, $mysql, $lang, $config;
	$tpath = locatePluginTemplates(array('main', 'list_subscribe_post', 'list_entries_post'), 'subscribe_comments', 1);
	$news_per_page = pluginGetVariable('subscribe_comments', 'admin_count');
	if (($news_per_page < 2) || ($news_per_page > 2000)) $news_per_page = 2;
	$pageNo = intval($_REQUEST['page']) ? $_REQUEST['page'] : 0;
	if ($pageNo < 1) $pageNo = 1;
	if (!$start_from) $start_from = ($pageNo - 1) * $news_per_page;
	$count = $mysql->result('SELECT count(id) from ' . prefix . '_subscribe_comments_temp');
	$countPages = ceil($count / $news_per_page);
	foreach ($mysql->select('SELECT * from ' . prefix . '_subscribe_comments_temp ORDER BY id DESC LIMIT ' . $start_from . ', ' . $news_per_page) as $row) {
		$url = admin_url . "/admin.php?mod=news&amp;action=edit&amp;id=" . $row['news_id'];
		$tEntries[] = array(
			'id'        => $row['id'],
			'page_name' => $row['news_title'],
			'page_url'  => $url,
			'com_text'  => $row['com_text'],
			'email'     => $row['user_email'],
		);
	}
	$delayed_send = pluginGetVariable('subscribe_comments', 'delayed_send');

	$pvars = array(
		'pagesss' => generateAdminPagelist(array('current' => $pageNo, 'count' => $countPages, 'url' => admin_url . '/admin.php?mod=extra-config&plugin=subscribe_comments&action=list_subscribe_post' . ($_REQUEST['news_per_page'] ? '&news_per_page=' . $news_per_page : '') . ($_REQUEST['author'] ? '&author=' . $_REQUEST['author'] : '') . ($_REQUEST['sort'] ? '&sort=' . $_REQUEST['sort'] : '') . ($postdate ? '&postdate=' . $postdate : '') . ($author ? '&author=' . $author : '') . ($status ? '&status=' . $status : '') . '&page=%page%')),
		'entries' => $tEntries
	);

	$xt = $twig->loadTemplate($tpath['list_subscribe_post'] . 'list_subscribe_post.tpl');
	$entriesOutput = $xt->render($pvars);

	$tvars = array(
		'entries_cron' => '',
		'entries'      => $entriesOutput,
		'action'       => 'Сформированные письма',
		'tab_general_active' => '',
		'tab_list_active' => '',
		'tab_post_active' => 'active',
		'hide_delayed' => ($delayed_send != 1),
		'lang' => $lang
	);

	$xt = $twig->loadTemplate($tpath['main'] . 'main.tpl');
	print $xt->render($tvars);
}

function modify()
{

	global $mysql;
	$selected_news = $_REQUEST['selected_subscribe_comments'];
	$subaction = $_REQUEST['subaction'];
	$id = implode(',', $selected_news);
	if (empty($id)) {
		return msg(array("type" => "error", "text" => "Вы не выбрали объектов"));
	}
	switch ($subaction) {
		case 'mass_delete':
			$del = true;
			break;
		case 'mass_delete_post':
			$del_post = true;
			break;
	}
	if (isset($del)) {
		$mysql->query("delete from " . prefix . "_subscribe_comments where id in ({$id})");
		msg(array("type" => "info", "info" => "Подписки с ID${id} удалены"));
	}
	if (isset($del_post)) {
		$mysql->query("delete from " . prefix . "_subscribe_comments_temp where id in ({$id})");
		msg(array("type" => "info", "info" => "Пиьсма с ID${id} удалены"));
	}
}

function main()
{

	global $twig, $mysql, $config, $template, $cron, $lang;
	$tpath = locatePluginTemplates(array('main', 'general_form', 'list_entries_cron', 'list_subscribe_cron'), 'subscribe_comments', 1);

	$delayed_send = pluginGetVariable('subscribe_comments', 'delayed_send');
	$entries_cron_output = '';
	if ($delayed_send == 1) {
		if ($config['syslog'] == '1') {
			$tEntriesCron = array();
			foreach ($mysql->select('SELECT * from ' . prefix . '_syslog where action = "subscribe_comments" ORDER BY id DESC LIMIT 5') as $row) {
				$tEntriesCron[] = array(
					'dt'    => $row['dt'],
					'stext' => $row['stext'],
				);
			}
			$pvars = array('entries_cron' => $tEntriesCron);
			$xt = $twig->loadTemplate($tpath['list_subscribe_cron'] . 'list_subscribe_cron.tpl');
			$entries_cron_output = $xt->render($pvars);
		}
		$cron_row = $cron->getConfig();
		foreach ($cron_row as $key => $value) {
			if ($value['plugin'] == 'subscribe_comments') {
				$cron_min = $value['min'];
				$cron_hour = $value['hour'];
				$cron_day = $value['day'];
				$cron_month = $value['month'];
			}
		}
		if (!isset($cron_min)) {
			$cron_min = '0,5,10,15,20,25,30,35,40,45,50,55';
		}
		if (!isset($cron_hour)) {
			$cron_hour = '*';
		}
		if (!isset($cron_day)) {
			$cron_day = '*';
		}
		if (!isset($cron_month)) {
			$cron_month = '*';
		}
		$cron->unregisterTask('subscribe_comments');
		$cron->registerTask('subscribe_comments', 'run', $cron_min, $cron_hour, $cron_day, $cron_month, '*');
	} else {
		$cron->unregisterTask('subscribe_comments');
	}
	if (isset($_REQUEST['submit'])) {
		pluginSetVariable('subscribe_comments', 'admin_count', intval($_REQUEST['admin_count']));
		pluginSetVariable('subscribe_comments', 'delayed_send', intval($_REQUEST['delayed_send']));
		pluginsSaveConfig();
		redirect_subscribe_comments('?mod=extra-config&plugin=subscribe_comments');
	}
	$admin_count = pluginGetVariable('subscribe_comments', 'admin_count');
	$delayed_send_val = pluginGetVariable('subscribe_comments', 'delayed_send');
	$delayed_send = '<option value="0" ' . (empty($delayed_send_val) ? 'selected' : '') . '>Нет</option><option value="1" ' . (!empty($delayed_send_val) ? 'selected' : '') . '>Да</option>';
	$pvars = array(
		'admin_count'  => isset($admin_count) ? $admin_count : '10',
		'delayed_send' => $delayed_send,
	);

	$xt = $twig->loadTemplate($tpath['general_form'] . 'general_form.tpl');
	$form_output = $xt->render($pvars);

	$tvars = array(
		'entries_cron' => $entries_cron_output,
		'entries'      => $form_output,
		'action'       => 'Общие настройки',
		'tab_general_active' => 'active',
		'tab_list_active' => '',
		'tab_post_active' => '',
		'hide_delayed' => ($delayed_send_val != 1),
		'lang' => $lang
	);

	$xt = $twig->loadTemplate($tpath['main'] . 'main.tpl');
	print $xt->render($tvars);
}

function redirect_subscribe_comments($url)
{

	if (headers_sent()) {
		echo "<script>document.location.href='{$url}';</script>\n";
	} else {
		header('HTTP/1.1 302 Moved Permanently');
		header("Location: {$url}");
	}
}

function input_filter_rev($text)
{

	$text = trim($text);
	$search = array("<", ">");
	$replace = array("&lt;", "&gt;");
	$text = preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", str_replace($search, $replace, $text));

	return $text;
}
