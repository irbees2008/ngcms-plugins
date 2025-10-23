<?php
if (!defined('NGCMS')) {
	exit('HAL');
}
pluginsLoadConfig();
LoadPluginLang('auth_social', 'config', '', '', '#');
switch ($_REQUEST['action']) {
	case 'options':
		show_options();
		break;
	default:
		show_options();
}
function show_options()
{

	global $tpl, $mysql, $lang, $twig;
	$tpath = locatePluginTemplates(array('config/main', 'config/general.from'), 'auth_social', 1);
	if (isset($_REQUEST['submit'])) {
		pluginSetVariable('auth_social', 'vk_client_id', secure_html($_REQUEST['vk_client_id']));
		pluginSetVariable('auth_social', 'vk_client_secret', secure_html($_REQUEST['vk_client_secret']));
		//pluginSetVariable('auth_social', 'vk_redirect_uri', intval($_REQUEST['vk_redirect_uri']));
		pluginSetVariable('auth_social', 'github_client_id', secure_html($_REQUEST['github_client_id']));
		pluginSetVariable('auth_social', 'github_client_secret', secure_html($_REQUEST['github_client_secret']));
		pluginSetVariable('auth_social', 'yandex_client_id', secure_html($_REQUEST['yandex_client_id']));
		pluginSetVariable('auth_social', 'yandex_client_secret', secure_html($_REQUEST['yandex_client_secret']));
		//pluginSetVariable('auth_social', 'yandex_redirect_uri', intval($_REQUEST['yandex_redirect_uri']));
		pluginSetVariable('auth_social', 'google_client_id', secure_html($_REQUEST['google_client_id']));
		pluginSetVariable('auth_social', 'google_client_secret', secure_html($_REQUEST['google_client_secret']));
		//pluginSetVariable('auth_social', 'google_redirect_uri', intval($_REQUEST['google_redirect_uri']));
		pluginSetVariable('auth_social', 'facebook_client_id', secure_html($_REQUEST['facebook_client_id']));
		pluginSetVariable('auth_social', 'facebook_client_secret', secure_html($_REQUEST['facebook_client_secret']));
		//pluginSetVariable('auth_social', 'facebook_redirect_uri', intval($_REQUEST['facebook_redirect_uri']));
		// removed providers: twitter, steam, twitch, odnoklassniki, mailru
		pluginsSaveConfig();
		redirect_auth_social('?mod=extra-config&plugin=auth_social');
	}
	$vk_client_id = pluginGetVariable('auth_social', 'vk_client_id');
	$vk_client_secret = pluginGetVariable('auth_social', 'vk_client_secret');
	//$vk_redirect_uri = pluginGetVariable('auth_social', 'vk_redirect_uri');
	$github_client_id = pluginGetVariable('auth_social', 'github_client_id');
	$github_client_secret = pluginGetVariable('auth_social', 'github_client_secret');
	$yandex_client_id = pluginGetVariable('auth_social', 'yandex_client_id');
	$yandex_client_secret = pluginGetVariable('auth_social', 'yandex_client_secret');
	//$yandex_redirect_uri = pluginGetVariable('auth_social', 'yandex_redirect_uri');
	$google_client_id = pluginGetVariable('auth_social', 'google_client_id');
	$google_client_secret = pluginGetVariable('auth_social', 'google_client_secret');
	//$google_redirect_uri = pluginGetVariable('auth_social', 'google_redirect_uri');
	$facebook_client_id = pluginGetVariable('auth_social', 'facebook_client_id');
	$facebook_client_secret = pluginGetVariable('auth_social', 'facebook_client_secret');
	//$facebook_redirect_uri = pluginGetVariable('auth_social', 'facebook_redirect_uri');
	// removed providers vars
	$xt = $twig->loadTemplate($tpath['config/general.from'] . 'config/general.from.tpl');
	$tVars = array(
		'skins_url' => skins_url,
		'home'      => home,
		'tpl_home'  => admin_url,
		'vk_client_id'     => $vk_client_id,
		'vk_client_secret' => $vk_client_secret,
		//'vk_redirect_uri' => $vk_redirect_uri,
		'github_client_id'     => $github_client_id,
		'github_client_secret' => $github_client_secret,
		'yandex_client_id'     => $yandex_client_id,
		'yandex_client_secret' => $yandex_client_secret,
		//'yandex_redirect_uri' => $yandex_redirect_uri,
		'google_client_id'     => $google_client_id,
		'google_client_secret' => $google_client_secret,
		//'google_redirect_uri' => $google_redirect_uri,
		'facebook_client_id'     => $facebook_client_id,
		'facebook_client_secret' => $facebook_client_secret,
		//'facebook_redirect_uri' => $facebook_redirect_uri,

	);
	$xg = $twig->loadTemplate($tpath['config/main'] . 'config/main.tpl');
	$tVars = array(
		'entries' => $xt->render($tVars),
	);
	print $xg->render($tVars);
}

function redirect_auth_social($url)
{

	if (headers_sent()) {
		echo "<script>document.location.href='{$url}';</script>\n";
	} else {
		header('HTTP/1.1 302 Moved Permanently');
		header("Location: {$url}");
	}
}
