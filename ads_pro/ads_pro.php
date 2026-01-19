<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
use function Plugins\{cache_get, cache_put, logger, sanitize};
// Classes for traffic handling
// - static pages
class ADSProStaticFilter extends StaticFilter
{
	function showStatic($staticID, $SQLstatic, &$tvars, $mode)
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
	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{
		global $adsPRO_cache;
		if ($mode['style'] == 'full') {
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
$adsPRO_cache = array(
	'flag.static'   => false,
	'static.id'     => null,
	'flag.news'     => false,
	'news.id'       => null,
	'flag.category' => false,
	'category.id'   => null,
	'flag.main'     => false
);
// Main function of plugin
function plugin_ads_pro()
{
	global $template, $config, $CurrentHandler, $catmap, $mysql, $adsPRO_cache;
	$dataConfig = pluginGetVariable('ads_pro', 'data');
	if (!is_array($dataConfig)) return;
	// Синхронизация с базой данных при переносе сайта
	$dataConfig = ads_pro_sync_with_database($dataConfig);
	if ($CurrentHandler['params'][0] == '/') $adsPRO_cache['flag.main'] = true;
	if (isset($CurrentHandler['params']['catid'])) {
		$adsPRO_cache['flag.category'] = true;
		$adsPRO_cache['category.id'] = $CurrentHandler['params']['catid'];
	} else if (isset($CurrentHandler['params']['category'])) {
		$adsPRO_cache['flag.category'] = true;
		$adsPRO_cache['category.id'] = array_search($CurrentHandler['params']['category'], $catmap);
	}
	if ($CurrentHandler['pluginName']) {
		$adsPRO_cache['flag.plugin'] = true;
		$adsPRO_cache['plugin.id'] = $CurrentHandler['pluginName'];
	}
	// Indexing structure for block display
	$blockDisplayList = array();
	$t_time = time();
	foreach ($dataConfig as $blockID => $blockRecords) {
		if (!$blockID) continue;
		// Обеспечиваем совместимость с Twig: добавляем префикс к числовым ID
		$tplVarName = $blockID;
		if (is_numeric($blockID)) {
			$tplVarName = 'block_' . $blockID;
		}
		// Initiate block output if it's not filled yet
		if (!isset($template['vars'][$tplVarName])) $template['vars'][$tplVarName] = '';
		// Scan all records of this block
		foreach ($blockRecords as $blockIndexNum => $blockInfo) {
			//print "<pre>ADS_PRO_DATA [$blockID][$blockIndexNum]:".var_export($blockInfo, true)."</pre>";
			// Skip inactive blocks
			if (!$blockInfo['state']) continue;
			// By default block is visible
			$blockIsVisible = true;
			// Skip blocks if they're displayed `by time` & shouldn't be displayed now
			if ($blockInfo['state'] == 2) {
				if ($blockInfo['start_view'] && $blockInfo['start_view'] > $t_time)
					$blockIsVisible = false;
				if ($blockInfo['end_view'] && $blockInfo['end_view'] <= $t_time)
					$blockIsVisible = false;
			}
			// Skip block if it's marked as `not to be displayed`
			if (!$blockIsVisible) continue;
			// Process location flags [if configured for specific block]
			if (is_array($blockInfo['location'])) {
				$blockIsVisible = false;
				$if_break = false;
				foreach ($blockInfo['location'] as $locRecord) {
					// Scan visibility parameters
					// view == 0 - display
					// view == 1 - do not display
					switch ($locRecord['mode']) {
						// Everywhere
						case 0:
							if ($locRecord['view']) {
								$blockIsVisible = false;
								$if_break = true;
							} else $blockIsVisible = true;
							break;
						// Main page
						case 1:
							if ($adsPRO_cache['flag.main']) {
								if ($locRecord['view']) {
									$blockIsVisible = false;
									$if_break = true;
								} else $blockIsVisible = true;
							}
							break;
						// Everywhere EXCEPT main page
						case 2:
							if (!$adsPRO_cache['flag.main']) {
								if ($locRecord['view']) {
									$blockIsVisible = false;
									$if_break = true;
								} else $blockIsVisible = true;
							}
							break;
						// In category
						case 3:
							if ($adsPRO_cache['flag.category']) {
								if (!$locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								} else if ($adsPRO_cache['category.id'] == $locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								}
							}
							break;
						// In static page
						case 4:
							if ($adsPRO_cache['flag.static']) {
								if (!$locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								} else if ($adsPRO_cache['static.id'] == $locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								}
							}
							break;
						// In news page
						case 5:
							if ($adsPRO_cache['flag.news']) {
								if (!$locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								} else if ($adsPRO_cache['news.id'] == $locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								}
							}
							break;
						// In plugin
						case 6:
							if ($adsPRO_cache['flag.plugin']) {
								if (!$locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								} else if ($adsPRO_cache['plugin.id'] == $locRecord['id']) {
									if ($locRecord['view']) {
										$blockIsVisible = false;
										$if_break = true;
									} else $blockIsVisible = true;
								}
							}
							break;
					}
					if ($if_break) break;
				}
				if (!$blockIsVisible) continue;
			}
			// Fine, block is visible, add it into display list
			$blockDisplayList[$blockID][] = $blockIndexNum;
		}
	}
	//print "<pre>ADS INDEXING:".var_export($blockDisplayList, true)."</pre>";
	// Scan blocks, marked to be displayed
	foreach ($blockDisplayList as $blockID => $blockRecList) {
		// Обеспечиваем совместимость с Twig: добавляем префикс к числовым ID
		$tplVarName = $blockID;
		if (is_numeric($blockID)) {
			$tplVarName = 'block_' . $blockID;
		}
		// Process multidisplay mode
		if ((count($blockRecList) > 1) && (($mdm = pluginGetVariable('ads_pro', 'multidisplay_mode')) > 0)) {
			// - First active
			if ($mdm == 1) {
				$blockRecList = array($blockRecList[0]);
			}
			// - Random
			if ($mdm == 2) {
				$blockRecList = array($blockRecList[array_rand($blockRecList)]);
			}
		}
		foreach ($blockRecList as $blockIndexNum) {
			// Retrieve block info
			$blockInfo = $dataConfig[$blockID][$blockIndexNum];
			//print "<pre>BID:".var_export($blockInfo, true)."</pre>";
			// Cache non-PHP ads blocks
			if ($blockInfo['type'] != 1) {
				$cacheKey = 'ads_pro:' . $blockID . ':' . $blockIndexNum . ':' . $blockInfo['type'];
				$cacheData = cache_get($cacheKey);
				if ($cacheData !== null) {
					$template['vars'][$tplVarName] .= $cacheData;
					continue;
				}
				$description = '';
				if (is_array($row = $mysql->record('select ads_blok from ' . prefix . '_ads_pro where id=' . db_squote($blockIndexNum)))) {
					$description = $blockInfo['type'] ? nl2br(htmlspecialchars($row['ads_blok'])) : sanitize($row['ads_blok'], 'html');
				}
				$template['vars'][$tplVarName] .= $description;
				cache_put($cacheKey, $description, 30000);
			} else {
				$description = '';
				if (is_array($row = $mysql->record('select ads_blok from ' . prefix . '_ads_pro where id=' . db_squote($blockIndexNum)))) {
					$description = $row['ads_blok'];
				}
				logger('ads_pro', 'Executing PHP block: id=' . $blockIndexNum . ', block=' . $blockID);
				ob_start();
				@eval($description);
				$out2 = ob_get_contents();
				ob_end_clean();
				$template['vars'][$tplVarName] .= $out2;
			}
		}
	}
}
// Функция синхронизации данных плагина с базой данных
function ads_pro_sync_with_database($dataConfig)
{
	global $mysql;
	// Получаем все ID из базы данных
	$dbIds = array();
	foreach ($mysql->select('SELECT id FROM ' . prefix . '_ads_pro') as $row) {
		$dbIds[] = intval($row['id']);
	}
	// Получаем все ID из конфигурации
	$configIds = array();
	foreach ($dataConfig as $blockName => $blocks) {
		foreach ($blocks as $blockId => $blockData) {
			$configIds[] = intval($blockId);
		}
	}
	// Проверяем, есть ли расхождения
	$missingInDb = array_diff($configIds, $dbIds);
	$missingInConfig = array_diff($dbIds, $configIds);
	// Если есть ID в конфиге, которых нет в БД - удаляем их из конфига
	if (!empty($missingInDb)) {
		logger('ads_pro', 'Sync: removing from config, IDs=' . implode(',', $missingInDb));
		foreach ($dataConfig as $blockName => &$blocks) {
			foreach ($blocks as $blockId => $blockData) {
				if (in_array(intval($blockId), $missingInDb)) {
					unset($blocks[$blockId]);
				}
			}
			// Удаляем пустые блоки
			if (empty($blocks)) {
				unset($dataConfig[$blockName]);
			}
		}
	}
	// Если есть ID в БД, которых нет в конфиге - пытаемся найти их в исходных данных
	if (!empty($missingInConfig)) {
		// Сначала ищем в исходной конфигурации
		$originalConfig = pluginGetVariable('ads_pro', 'data');
		$foundInOriginal = array();
		foreach ($missingInConfig as $dbId) {
			// Ищем этот ID в исходной конфигурации
			foreach ($originalConfig as $blockName => $blocks) {
				foreach ($blocks as $blockId => $blockData) {
					if (intval($blockId) == $dbId) {
						// Найден в исходной конфигурации - восстанавливаем
						$dataConfig[$blockName][$dbId] = $blockData;
						$foundInOriginal[] = $dbId;
						break 2;
					}
				}
			}
		}
		// Для ID, которых нет в исходной конфигурации, создаем базовую структуру
		$notFoundInOriginal = array_diff($missingInConfig, $foundInOriginal);
		if (!empty($notFoundInOriginal)) {
			logger('ads_pro', 'Sync: restoring blocks from DB, IDs=' . implode(',', $notFoundInOriginal));
		}
		foreach ($notFoundInOriginal as $dbId) {
			$row = $mysql->record('SELECT * FROM ' . prefix . '_ads_pro WHERE id = ' . intval($dbId));
			if ($row) {
				$blockName = 'fase';
				$dataConfig[$blockName][$dbId] = array(
					'description' => $row['description'] ? sanitize($row['description'], 'string') : 'Восстановленный блок',
					'type' => intval($row['type']),
					'state' => intval($row['state']),
					'start_view' => $row['start_view'] ? intval($row['start_view']) : null,
					'end_view' => $row['end_view'] ? intval($row['end_view']) : null,
					'location' => $row['location'] ? unserialize($row['location']) : array(1 => array('mode' => 0, 'view' => 0))
				);
			}
		}
		// Сохраняем обновленную конфигурацию
		pluginSetVariable('ads_pro', 'data', $dataConfig);
		pluginsSaveConfig();
	}
	return $dataConfig;
}
