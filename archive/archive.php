<?php

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Integrated with ng-helpers v0.2.2 (2026-01-29)
// - cache_get/cache_put for caching archive data
// - logger for operation tracking
// - clamp for parameter validation
// - sanitize for input data safety

// Import ng-helpers functions
use function Plugins\{cache_get, cache_put, logger, clamp, sanitize};

// Check execution mode
if (!pluginGetVariable('archive', 'mode')) {
	add_act('index', 'plugin_archive'); // auto
} else {
	global $template;
	$template['vars']['plugin_archive'] = ''; // twig
}
// Load lang file
LoadPluginLang('archive', 'main', '', '', ':');
function plugin_archive()
{

	global $template, $config;
	$template['vars']['plugin_archive'] = plug_arch(pluginGetVariable('archive', 'maxnum') ? pluginGetVariable('archive', 'maxnum') : 12, pluginGetVariable('archive', 'counter') ? pluginGetVariable('archive', 'counter') : 0, pluginGetVariable('archive', 'tcounter') ? pluginGetVariable('archive', 'tcounter') : 0, false, pluginGetVariable('archive', 'cache') ? pluginGetVariable('archive', 'cacheExpire') : 0);
}

function plug_arch($maxnum, $counter, $tcounter, $overrideTemplateName, $cacheExpire)
{

	global $config, $mysql, $tpl, $template, $twig, $twigLoader, $langMonths, $lang;

	// Валидация параметров с помощью clamp()
	$maxnum = clamp(intval($maxnum), 1, 50);
	$counter = intval($counter);
	$tcounter = intval($tcounter);
	$cacheExpire = clamp(intval($cacheExpire), 0, 86400); // 0 до 24 часов

	logger('Generating archive: maxnum=' . $maxnum . ', cache=' . ($cacheExpire > 0 ? $cacheExpire . 's' : 'disabled') . ', called_from=' . basename($_SERVER['PHP_SELF'] ?? 'unknown'), 'info', 'archive.log');

	if ($overrideTemplateName) {
		$templateName = sanitize($overrideTemplateName);
	} else {
		$templateName = 'archive';
	}

	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheKey = 'archive_' . $config['theme'] . '_' . $templateName . '_' . $config['default_lang'];

	if ($cacheExpire > 0) {
		$cacheData = cache_get($cacheKey, false);
		if ($cacheData !== false) {
			// We got data from cache. Return it and stop
			logger('Cache HIT: ' . $cacheKey, 'info', 'archive.log');
			return $cacheData;
		}
		logger('Cache MISS: ' . $cacheKey, 'info', 'archive.log');
	}
	// Determine paths for all template files
	$tpath = locatePluginTemplates(array($templateName, 'entries'), 'archive', pluginGetVariable('archive', 'localsource'));
	// Load list
	$caseList = explode(',', $lang['archive:counter.case']);
	foreach ($mysql->select("SELECT month(from_unixtime(postdate)) as month, year(from_unixtime(postdate)) as year, COUNT(id) AS cnt FROM " . prefix . "_news WHERE approve = '1' GROUP BY year, month ORDER BY year DESC, month DESC limit $maxnum") as $row) {
		$month_link = checkLinkAvailable('news', 'by.month') ?
			generateLink('news', 'by.month', array('year' => $row['year'], 'month' => sprintf('%02u', $row['month']))) :
			generateLink('core', 'plugin', array('plugin' => 'news', 'handler' => 'by.month'), array('year' => $row['year'], 'month' => sprintf('%02u', $row['month'])));
		if ($tcounter) {
			// Determine current case
			$sCase = 99;
			$cnt = $row['cnt'];
			if ($cnt == 1) {
				$sCase = 1;
			} else if (($cnt >= 2) && ($cnt <= 4)) {
				$sCase = 2;
			} else if (($cnt >= 5) && ($cnt <= 20)) {
				$sCase = 4;
			} else {
				$tsCase = $sCase % 10;
				if ($tsCase == 0) {
					$sCase = 4;
				} else if ($tsCase == 1) {
					$sCase = 1;
				} else if (($tsCase >= 2) && ($tsCase <= 4)) {
					$sCase = 2;
				} else {
					$sCase = 4;
				}
			}
			$ctext = $caseList[$sCase - 1];
		} else {
			$ctext = '';
		}
		$tEntries[] = array(
			'link'    => $month_link,
			'title'   => $langMonths[$row['month'] - 1] . ' ' . $row['year'],
			'cnt'     => $row['cnt'],
			'counter' => $counter,
			'ctext'   => $ctext,
		);
	}
	$tVars['entries'] = $tEntries;
	$tVars['tpl_url'] = tpl_url;
	// Prepare conversion table
	$conversionConfig = array(
		'{archive}' => '{% for entry in entries %}{% include localPath(0) ~ "entries.tpl" %}{% endfor %}',
		'{tpl_url}' => '{{ tpl_url }}',
	);
	$conversionConfigE = array(
		'{link}'  => '{{ entry.link }}',
		'{title}' => '{{ entry.title }}',
		'{cnt}'   => '{{ entry.cnt }}',
		'{ctext}' => '{{ entry.ctext }}',
	);
	$conversionConfigRegex = array(
		'#\[counter\](.+?)\[/counter\]#is' => '{% if (entry.counter) %}$1{% endif %}',
	);
	$twigLoader->setConversion($tpath['archive'] . 'archive.tpl', $conversionConfig);
	$twigLoader->setConversion($tpath['entries'] . 'entries.tpl', $conversionConfigE, $conversionConfigRegex);
	// Предзагрузка шаблона entries [ чтобы отработал setConversion ] при его наличии
	if (isset($tpath['entries']))
		$twig->loadTemplate($tpath['entries'] . 'entries.tpl');

	$xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
	$output = $xt->render($tVars);

	if ($cacheExpire > 0) {
		cache_put($cacheKey, $output, $cacheExpire);
		logger('Cache saved: ' . $cacheKey . ' (TTL: ' . $cacheExpire . 's)', 'info', 'archive.log');
	}

	return $output;
}

//
// Show data block for xnews plugin
// Params:
// * maxnum		- Max num entries for archive (1-50)
// * counter		- Show counter in the entries
// * tcounter		- Show text counter in the entries
// * template	- Personal template for plugin
// * cacheExpire		- age of cache [in seconds, 0-86400]
function plugin_archive_showTwig($params)
{
	$maxnum = isset($params['maxnum']) ? intval($params['maxnum']) : pluginGetVariable('archive', 'maxnum');
	$counter = isset($params['counter']) ? intval($params['counter']) : false;
	$tcounter = isset($params['tcounter']) ? intval($params['tcounter']) : false;
	$template = isset($params['template']) ? sanitize($params['template']) : false;
	$cacheExpire = isset($params['cacheExpire']) ? intval($params['cacheExpire']) : 0;

	return plug_arch($maxnum, $counter, $tcounter, $template, $cacheExpire);
}

twigRegisterFunction('archive', 'show', 'plugin_archive_showTwig');
