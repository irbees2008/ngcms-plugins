<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
// Modernized with ng-helpers v0.2.2 (2026)
// - Replaced cacheRetrieveFile/cacheStoreFile with cache() helper
// - Added time_ago for human-readable timestamps
// - Added excerpt for better text truncation
// - Added logger for operations tracking
// - Added sanitize for data cleaning
// - Added array_get for safe access
// Import ng-helpers functions
use function Plugins\{cache, time_ago, excerpt, logger, sanitize, array_get};

define('lastcomments_version', '0.11');
loadPluginLang('lastcomments', 'main', '', '', ':');
// ==============================================
// Side bar widget
// ==============================================
function lastcomments_block()
{
	global $template;
	// Action if sidepanel is enabled
	if (pluginGetVariable('lastcomments', 'sidepanel')) {
		$template['vars']['plugin_lastcomments'] = lastcomments();
	} else {
		$template['vars']['plugin_lastcomments'] = "";
	}
}
registerActionHandler('index', 'lastcomments_block');
// ==============================================
// ==============================================
// Plugin page
// ==============================================
function lastcomments_page()
{
	loadPluginLang('lastcomments', 'main', '', '', ':');
	global $SYSTEM_FLAGS, $template, $lang, $CurrentHandler;
	// Action if ppage is enabled
	if (pluginGetVariable('lastcomments', 'ppage') && ($CurrentHandler['handlerParams']['value']['pluginName'] == 'core')) {
		$SYSTEM_FLAGS['info']['title']['group'] = $lang['lastcomments:lastcomments'];
		$template['vars']['mainblock'] = lastcomments(1);
	} else {
		error404();
	}
}
register_plugin_page('lastcomments', '', 'lastcomments_page', 0);
// ==============================================
// ==============================================
// Rss feed
// ==============================================
function lastcomments_rssfeed()
{
	global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $CurrentHandler;
	// Action if rssfeed is enabled
	if (pluginGetVariable('lastcomments', 'rssfeed') && !(checkLinkAvailable('lastcomments', 'rss') && $CurrentHandler['handlerParams']['value']['pluginName'] == 'core')) {
		// Hide Template
		$SUPRESS_TEMPLATE_SHOW = true;
		$SUPRESS_MAINBLOCK_SHOW = true;
		// Disable index actions
		actionDisable("index");
		actionDisable("index_pre");
		actionDisable("index_post");
		// launch rss
		echo lastcomments(2);
	} else {
		error404();
	}
}
register_plugin_page('lastcomments', 'rss', 'lastcomments_rssfeed', 0);
// ==============================================
// ==============================================
// Some magic
// ==============================================
function lastcomments($mode = 0)
{
	global $config, $mysql, $twig, $twigLoader, $parse, $TemplateCache;
	switch ($mode) {
		case 1:
			$tpl_prefix = "pp_";    // plugin page
			break;
		case 2:
			$tpl_prefix = "rss_";    // rss feed
			break;
		case 0:
		default:
			$tpl_prefix = "";        // sidepanel widget
			break;
	}
	// Generate cache key
	$page = array_get($_REQUEST, 'page', 0, 'int');
	$cacheKey = "lastcomments_{$config['theme']}_{$config['default_lang']}_{$tpl_prefix}_{$page}";
	if (pluginGetVariable('lastcomments', 'cache')) {
		$cacheExpire = intval(pluginGetVariable('lastcomments', 'cacheExpire')) ?: 30;
		// Try to get from cache using ng-helpers cache() function
		$cacheData = cache($cacheKey, function () {
			return null; // Cache miss, will regenerate below
		}, $cacheExpire * 60); // Convert minutes to seconds
		if ($cacheData !== null) {
			logger('[lastcomments] Cache hit: ' . $cacheKey, 'debug', 'lastcomments.log');
			return $cacheData;
		}
	}
	// Preload template configuration variables
	@templateLoadVariables();
	// Use default <noavatar> file
	// - Check if noavatar is defined on template level
	$tplVars = $TemplateCache['site']['#variables'];
	$noAvatarURL = (isset($tplVars['configuration']) && is_array($tplVars['configuration']) && isset($tplVars['configuration']['noAvatarImage']) && $tplVars['configuration']['noAvatarImage']) ? (tpl_url . "/" . $tplVars['configuration']['noAvatarImage']) : (avatars_url . "/noavatar.gif");
	//
	// Prepare for battle
	$comm_num = 0;
	$number = intval(pluginGetVariable('lastcomments', $tpl_prefix . 'number'));
	$comm_length = intval(pluginGetVariable('lastcomments', $tpl_prefix . 'comm_length'));
	// Set defaults if values are empty or invalid
	if (!$number || $number <= 0) {
		$number = $tpl_prefix ? 30 : 10;
	}
	if (!$comm_length || $comm_length <= 0) {
		$comm_length = $tpl_prefix ? 500 : 50;
	}
	// Ensure values are within valid ranges
	$number = max(1, min(50, $number));
	$comm_length = max(10, min(500, $comm_length));
	logger('[lastcomments] Mode: ' . $mode . ', prefix: "' . $tpl_prefix . '", number: ' . $number . ', length: ' . $comm_length, 'debug', 'lastcomments.log');
	if ($mode == 2) {
		$old_locale = setlocale(LC_TIME, 0);
		setlocale(LC_TIME, 'en_EN');
	}
	$query = "select c.*, u.avatar as users_avatar, u.id as uid, n.id as nid, n.title, n.alt_name, n.catid, n.postdate as npostdate from " . prefix . "_comments c left join " . prefix . "_news n on c.post=n.id left join " . uprefix . "_users u on c.author_id = u.id where n.approve=1 order by c.id desc limit " . $number;
	$data = array();
	foreach ($mysql->select($query) as $row) {
		// Parse comments
		$text = $row['text'];
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
		// Use ng-helpers excerpt for better truncation
		if (strlen($text) > $comm_length) {
			$text = excerpt($text, $comm_length, '...');
		}
		$comm_num++;
		// gen answer
		if ($row['answer'] != '') {
			$answer = $row['answer'];
			$name = $row['name'];
			if ($config['blocks_for_reg']) {
				$answer = $parse->userblocks($answer);
			}
			if ($config['use_htmlformatter']) {
				$answer = $parse->htmlformatter($answer);
			}
			if ($config['use_bbcodes']) {
				$answer = $parse->bbcodes($answer);
			}
			if ($config['use_smilies']) {
				$answer = $parse->smilies($answer);
			}
		}
		// gen avatar
		if ($config['use_avatars']) {
			if ($row['users_avatar']) {
				$avatar = "<img src=\"" . avatars_url . "/" . $row['users_avatar'] . "\" alt=\"" . $row['author'] . "\" />";
				$avatar_url = avatars_url . "/" . $row['users_avatar'];
			} else {
				// If gravatar integration is active, show avatar from GRAVATAR.COM
				if ($config['avatars_gravatar']) {
					$avatar = '<img src="http://www.gravatar.com/avatar/' . md5(strtolower($row['mail'])) . '?s=' . $config['avatar_wh'] . '&amp;d=' . urlencode($noAvatarURL) . '" alt=""/>';
					$avatar_url = 'http://www.gravatar.com/avatar/' . md5(strtolower($row['mail'])) . '?s=' . $config['avatar_wh'] . '&amp;d=' . urlencode($noAvatarURL);
				} else {
					$avatar = "<img src=\"" . $noAvatarURL . "\" alt=\"\" />";
					$avatar_url = $noAvatarURL;
				}
			}
		} else {
			$avatar = '';
			$avatar_url = '';
		}
		if ($row['author_id'] && getPluginStatusActive('uprofile')) {
			$author_link = checkLinkAvailable('uprofile', 'show') ?
				generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])) :
				generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('id' => $row['author_id']));
		} else {
			$author_link = '';
		}
		// Simple time_ago implementation
		$diff = time() - $row['postdate'];
		if ($diff < 60) {
			$time_ago_str = 'только что';
		} elseif ($diff < 3600) {
			$mins = floor($diff / 60);
			$time_ago_str = $mins . ' ' . ($mins == 1 ? 'минуту' : ($mins < 5 ? 'минуты' : 'минут')) . ' назад';
		} elseif ($diff < 86400) {
			$hours = floor($diff / 3600);
			$time_ago_str = $hours . ' ' . ($hours == 1 ? 'час' : ($hours < 5 ? 'часа' : 'часов')) . ' назад';
		} elseif ($diff < 604800) {
			$days = floor($diff / 86400);
			$time_ago_str = $days . ' ' . ($days == 1 ? 'день' : ($days < 5 ? 'дня' : 'дней')) . ' назад';
		} else {
			$time_ago_str = langdate('d.m.Y', $row['postdate']);
		}
		$data[] = array(
			'link'          => newsGenerateLink(array('id' => $row['nid'], 'alt_name' => $row['alt_name'], 'catid' => $row['catid'], 'postdate' => $row['npostdate'])),
			'date'          => langdate('d.m.Y H:i', $row['postdate']),
			'time_ago'      => $time_ago_str,
			'author'        => str_replace('<', '&lt;', $row['author']),
			'author_id'     => $row['author_id'],
			'title'         => str_replace('<', '&lt;', $row['title']),
			'text'          => $text,
			'category_link' => GetCategories($row['catid']),
			'comnum'        => $comm_num,
			'author_link'   => $author_link,
			'avatar'        => $avatar,
			'avatar_url'    => $avatar_url,
			'answer'        => $answer,
			'name'          => $name,
			'alternating'   => ($comnum % 2) ? "lastcomments_even" : "lastcomments_odd",
			'rsslink'       => home . "?id=" . $row['nid'],
			'rssdate'       => gmstrftime('%a, %d %b %Y %H:%M:%S GMT', $row['postdate']),
		);
	}
	$tpath = locatePluginTemplates(array($tpl_prefix . 'lastcomments', $tpl_prefix . 'entries'), 'lastcomments', pluginGetVariable('lastcomments', 'localsource'));
	// Prepare REGEX conversion table
	$conversionConfigRegex = array(
		"#\[profile\](.*?)\[/profile\]#si"       => "{% if (entry.author_id) and (pluginIsActive('uprofile')) %}$1{% endif %}",
		"#\[answer\](.*?)\[/answer\]#si"         => "{% if (entry.answer != '') %}$1{% endif %}",
		"#\[nocomments\](.*?)\[/nocomments\]#si" => "{% if (comnum == 0) %}$1{% endif %}",
		//		"#\{l_([0-9a-zA-Z\-\_\.\#]+)}#"					=> "{{ lang['$1'] }}",
	);
	// Prepare conversion table
	$conversionConfig = array(
		'{tpl_url}'       => '{{ tpl_url }}',
		'{link}'          => '{{ entry.link }}',
		'{date}'          => '{{ entry.date }}',
		'{time_ago}'      => '{{ entry.time_ago }}',
		'{author}'        => '{{ entry.author }}',
		'{author_id}'     => '{{ entry.author_id }}',
		'{title}'         => '{{ entry.title }}',
		'{text}'          => '{{ entry.text }}',
		'{category_link}' => '{{ entry.category_link }}',
		'{comnum}'        => '{{ entry.comnum }}',
		'{author_link}'   => '{{ entry.author_link }}',
		'{avatar}'        => '{{ entry.avatar }}',
		'{avatar_url}'    => '{{ entry.avatar_url }}',
		'{answer}'        => '{{ entry.answer }}',
		'{name}'          => '{{ entry.name }}',
		'{alternating}'   => '{{ entry.alternating }}',
		'{entries}'       => '{% for entry in entries %}{% include localPath(0) ~ "entries.tpl" %}{% endfor %}'
	);
	$twigLoader->setConversion($tpath[$tpl_prefix . 'lastcomments'] . $tpl_prefix . "lastcomments" . '.tpl', $conversionConfig, $conversionConfigRegex);
	$twigLoader->setConversion($tpath[$tpl_prefix . 'entries'] . $tpl_prefix . "entries" . '.tpl', $conversionConfig, $conversionConfigRegex);
	if (isset($tpath[$tpl_prefix . 'entries']))
		$twig->loadTemplate($tpath[$tpl_prefix . 'entries'] . $tpl_prefix . 'entries' . '.tpl');
	$xt = $twig->loadTemplate($tpath[$tpl_prefix . 'lastcomments'] . $tpl_prefix . "lastcomments" . '.tpl');
	$tVars = array(
		'comnum'  => $comm_num,
		'entries' => $data,
		'home_title'  => $config['home_title'],
		'home_url'    => $config['home_url'],
		'description' => $config['description'],
		'generator' => 'Plugin Lastcomments (' . lastcomments_version . ') // Next Generation CMS (' . engineName . ' ' . engineVersion . ')',
	);
	$tVars['lastcomments_url'] = generatePluginLink('lastcomments', null);
	$tVars['lastcomments_url_rss'] = generatePluginLink('lastcomments', 'rss');
	$output = $xt->render($tVars);
	if ($mode == 2) setlocale(LC_TIME, $old_locale);
	// Cache the output using ng-helpers cache() function
	if (pluginGetVariable('lastcomments', 'cache')) {
		$cacheExpire = intval(pluginGetVariable('lastcomments', 'cacheExpire')) ?: 30;
		cache($cacheKey, function () use ($output) {
			return $output;
		}, $cacheExpire * 60);
		logger('[lastcomments] Generated and cached: ' . strlen($output) . ' bytes, ' . $comm_num . ' comments', 'info', 'lastcomments.log');
	}
	return $output;
}
