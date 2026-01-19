<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Modified with ng-helpers v0.2.0 functions (2026)
// - Replaced cacheRetrieveFile/cacheStoreFile with cache_get/cache_put
// - Added truncate_html for answer previews
// - Added logging support

// Import ng-helpers functions
use function Plugins\{cache_get, cache_put, truncate_html, logger};

register_plugin_page('faq', '', 'plugin_faq');
function plugin_faq()
{

	global $catz, $twig, $catmap, $mysql, $config, $userROW, $tpl, $parse, $template, $lang, $PFILTERS, $SYSTEM_FLAGS, $CurrentHandler;
	$title_plg = 'Вопросы и ответы';
	$SYSTEM_FLAGS['info']['title']['group'] = isset($title_plg) ? $title_plg : $SYSTEM_FLAGS['info']['title']['group'];
	$tpath = locatePluginTemplates(array('faq_page'), 'faq', 1);
	$xt = $twig->loadTemplate($tpath['faq_page'] . 'faq_page.tpl');
	foreach (
		$mysql->select("SELECT *
				FROM " . prefix . "_faq WHERE (active = 1)
				ORDER BY id ASC") as $row
	) {
		$tEntry[] = array(
			'id'       => $row['id'],
			'question' => $row['question'],
			'answer'   => $row['answer'],
			'answer_preview' => truncate_html($row['answer'], 200),
		);
	}
	$tVars = array(
		'entries' => isset($tEntry) ? $tEntry : '',
		'home'    => home,
	);
	$template['vars']['mainblock'] = $xt->render($tVars);
}

function plug_faq($maxnum, $overrideTemplateName, $order, $cacheExpire)
{

	global $config, $mysql, $tpl, $template, $twig, $twigLoader, $langMonths, $lang;
	if (($maxnum < 1) || ($maxnum > 50)) $maxnum = 12;
	if ($overrideTemplateName) {
		$templateName = $overrideTemplateName;
	} else {
		$templateName = 'faq_block';
	}
	if ($order != 'ASC' && $order != 'DESC') {
		$order = 'DESC';
	}
	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheKey = 'faq:' . md5($config['theme'] . $templateName . $config['default_lang'] . $order);
	if ($cacheExpire > 0) {
		$cacheData = cache_get($cacheKey);
		if ($cacheData !== null) {
			// We got data from cache. Return it and stop
			return $cacheData;
		}
	}
	foreach ($mysql->select("SELECT * FROM " . prefix . "_faq WHERE active = '1' ORDER BY id " . $order . " limit $maxnum") as $row) {
		$tEntries[] = array(
			'id'       => $row['cnt'],
			'question' => $row['question'],
			'answer'   => $row['answer'],
			'answer_preview' => truncate_html($row['answer'], 150),
		);
	}
	$tVars['entries'] = $tEntries;
	$tVars['tpl_url'] = tpl_url;
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array($templateName), 'faq', pluginGetVariable('faq', 'localsource'));
	$xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
	$output = $xt->render($tVars);
	if ($cacheExpire > 0) {
		cache_put($cacheKey, $output, $cacheExpire);
		logger('faq', 'FAQ block cached: ' . count($tEntries) . ' entries, order: ' . $order . ', expire: ' . $cacheExpire . 's');
	}

	return $output;
}

// Params:
// * maxnum		- Max num entries
// * template	- Personal template for plugin
// * cacheExpire		- age of cache [in seconds]
function plugin_faq_showTwig($params)
{

	global $CurrentHandler, $config;

	return plug_faq($params['maxnum'], isset($params['template']) ? $params['template'] : false, isset($params['order']) ? $params['order'] : 'DESC', isset($params['cacheExpire']) ? $params['cacheExpire'] : 0);
}

twigRegisterFunction('faq', 'show', 'plugin_faq_showTwig');
