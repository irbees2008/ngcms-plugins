<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Classes for traffic handling
// - static pages
class ADSProStaticFilter extends StaticFilter
{

	public function showStatic($staticID, $SQLstatic, &$tvars, $mode): int
	{
		global $adsPRO_cache;
		$adsPRO_cache['flag.static'] = true;
		$adsPRO_cache['static.id'] = $staticID;
		return 1;
	}
}

// - news
class ADSProNewsFilter extends NewsFilter
{

	public function showNews($newsID, $SQLnews, &$tvars, $mode = []): int
	{
		global $adsPRO_cache;
		if (($mode['style'] ?? '') === 'full') {
			$adsPRO_cache['flag.news'] = true;
			$adsPRO_cache['news.id'] = $newsID;
		}
		return 1;
	}
}

// Initiate interceptors
// - for common pages [ process page, generate output ]
add_act('index_post', 'plugin_ads_pro');

// - for extracting data from static pages
register_filter('static', 'ads_pro', new ADSProStaticFilter);

// - for extracting data from news
if (pluginGetVariable('ads_pro', 'support_news')) {
	register_filter('flag.news', 'ads_pro', new ADSProNewsFilter);
}

// Initiate global internal variable
$adsPRO_cache = [
	'flag.static'   => false,
	'static.id'     => null,
	'flag.news'     => false,
	'news.id'       => null,
	'flag.category' => false,
	'category.id'   => null,
	'flag.main'     => false,
	'flag.plugin'   => false,
	'plugin.id'     => null,
];

// Main function of plugin
function plugin_ads_pro()
{
	global $template, $config, $CurrentHandler, $catmap, $mysql, $adsPRO_cache;

	$dataConfig = pluginGetVariable('ads_pro', 'data');
	if (!is_array($dataConfig)) return;

	if ($CurrentHandler['params'][0] === '/') {
		$adsPRO_cache['flag.main'] = true;
	}

	if (isset($CurrentHandler['params']['catid'])) {
		$adsPRO_cache['flag.category'] = true;
		$adsPRO_cache['category.id'] = $CurrentHandler['params']['catid'];
	} elseif (isset($CurrentHandler['params']['category'])) {
		$adsPRO_cache['flag.category'] = true;
		$adsPRO_cache['category.id'] = array_search($CurrentHandler['params']['category'], $catmap);
	}

	if ($CurrentHandler['pluginName']) {
		$adsPRO_cache['flag.plugin'] = true;
		$adsPRO_cache['plugin.id'] = $CurrentHandler['pluginName'];
	}

	// Indexing structure for block display
	$blockDisplayList = [];
	$t_time = time();

	foreach ($dataConfig as $blockID => $blockRecords) {
		if (!$blockID) continue;

		// Initiate block output if it's not filled yet
		if (!isset($template['vars'][$blockID])) {
			$template['vars'][$blockID] = '';
		}

		foreach ($blockRecords as $blockIndexNum => $blockInfo) {
			if (empty($blockInfo['state'])) continue;

			// Determine block visibility
			$blockIsVisible = true;

			if ($blockInfo['state'] === 2) {
				if (($blockInfo['start_view'] ?? 0) > $t_time || ($blockInfo['end_view'] ?? PHP_INT_MAX) <= $t_time) {
					$blockIsVisible = false;
				}
			}

			if (!$blockIsVisible) continue;

			if (is_array($blockInfo['location'] ?? null)) {
				$blockIsVisible = false;

				foreach ($blockInfo['location'] as $locRecord) {
					if (processBlockVisibility($locRecord, $adsPRO_cache)) {
						$blockIsVisible = true;
						break;
					}
				}
			}

			if ($blockIsVisible) {
				$blockDisplayList[$blockID][] = $blockIndexNum;
			}
		}
	}

	foreach ($blockDisplayList as $blockID => $blockRecList) {
		$mdm = pluginGetVariable('ads_pro', 'multidisplay_mode') ?? 0;
		if (count($blockRecList) > 1 && $mdm > 0) {
			$blockRecList = handleMultidisplayMode($blockRecList, $mdm);
		}

		foreach ($blockRecList as $blockIndexNum) {
			$blockInfo = $dataConfig[$blockID][$blockIndexNum];
			$template['vars'][$blockID] .= renderBlockContent($blockInfo, $blockIndexNum);
		}
	}
}

function processBlockVisibility(array $locRecord, array $adsPRO_cache): bool
{
	switch ($locRecord['mode']) {
		case 0:
			return !$locRecord['view'];
		case 1:
			return $adsPRO_cache['flag.main'] && !$locRecord['view'];
		case 2:
			return !$adsPRO_cache['flag.main'] && !$locRecord['view'];
		case 3:
			return ($adsPRO_cache['flag.category'] && (!$locRecord['id'] || $adsPRO_cache['category.id'] == $locRecord['id'])) && !$locRecord['view'];
		case 4:
			return ($adsPRO_cache['flag.static'] && (!$locRecord['id'] || $adsPRO_cache['static.id'] == $locRecord['id'])) && !$locRecord['view'];
		case 5:
			return ($adsPRO_cache['flag.news'] && (!$locRecord['id'] || $adsPRO_cache['news.id'] == $locRecord['id'])) && !$locRecord['view'];
		case 6:
			return ($adsPRO_cache['flag.plugin'] && (!$locRecord['id'] || $adsPRO_cache['plugin.id'] == $locRecord['id'])) && !$locRecord['view'];
		default:
			return false;
	}
}

function handleMultidisplayMode(array $blockRecList, int $mdm): array
{
	return match ($mdm) {
		1 => [$blockRecList[0]],
		2 => [$blockRecList[array_rand($blockRecList)]],
		default => $blockRecList,
	};
}

function renderBlockContent(array $blockInfo, int $blockIndexNum): string
{
	global $mysql;

	$cacheFileName = md5("ads_pro_{$blockInfo['type']}_{$blockIndexNum}.txt");
	$cacheData = cacheRetrieveFile($cacheFileName, 30000, 'ads_pro');

	if ($cacheData !== false) {
		return $cacheData;
	}

	$description = '';
	if (is_array($row = $mysql->record('SELECT ads_blok FROM ' . prefix . '_ads_pro WHERE id=' . db_squote($blockIndexNum)))) {
		$description = $blockInfo['type'] ? nl2br(htmlspecialchars($row['ads_blok'])) : $row['ads_blok'];
	}

	if ($blockInfo['type'] == 1) {
		ob_start();
		eval($description);
		$description = ob_get_clean();
	}

	cacheStoreFile($cacheFileName, $description, 'ads_pro');
	return $description;
}