<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Modified with ng-helpers v0.2.2 functions (2026-01-29)
// NOTE: On hosting, ng-helpers may load after this plugin
// Using wrapper functions with existence checks for compatibility

// Wrapper functions for ng-helpers compatibility
function ads_logger($message, $level = 'info', $file = 'plugin.log')
{
	if (function_exists('Plugins\\logger')) {
		return \Plugins\logger($message, $level, $file);
	}
	return true;
}

function ads_cache_get($key, $default = null)
{
	if (function_exists('Plugins\\cache_get')) {
		return \Plugins\cache_get($key, $default);
	}
	return $default;
}

function ads_cache_put($key, $value, $minutes = 60)
{
	if (function_exists('Plugins\\cache_put')) {
		return \Plugins\cache_put($key, $value, $minutes);
	}
	return false;
}

function ads_sanitize($data, $type = 'string')
{
	if (function_exists('Plugins\\sanitize')) {
		return \Plugins\sanitize($data, $type !== 'html');
	}
	return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function ads_benchmark($callback)
{
	if (function_exists('Plugins\\benchmark')) {
		return \Plugins\benchmark($callback);
	}
	$start = microtime(true);
	$result = $callback();
	return ['time' => microtime(true) - $start, 'result' => $result];
}

function ads_clamp($value, $min, $max)
{
	if (function_exists('Plugins\\clamp')) {
		return \Plugins\clamp($value, $min, $max);
	}
	return max($min, min($max, $value));
}

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
				$cacheData = ads_cache_get($cacheKey);

				if ($cacheData !== null) {
					$template['vars'][$tplVarName] .= $cacheData;
					ads_logger('Cache HIT for block: id=' . $blockIndexNum . ', block=' . $blockID, 'debug', 'ads_pro.log');
					continue;
				}

				ads_logger('Cache MISS for block: id=' . $blockIndexNum . ', block=' . $blockID, 'debug', 'ads_pro.log');

				$description = '';
				if (is_array($row = $mysql->record('select ads_blok from ' . prefix . '_ads_pro where id=' . db_squote($blockIndexNum)))) {
					// Sanitize based on type: 0=HTML, 2=TEXT
					if ($blockInfo['type'] == 0) {
						// HTML type - allow HTML but sanitize dangerous content
						$description = ads_sanitize($row['ads_blok'], 'html');
					} else {
						// TEXT type - escape all HTML and convert newlines
						$description = nl2br(ads_sanitize($row['ads_blok'], 'string'));
					}
				}

				$template['vars'][$tplVarName] .= $description;

				// Cache for 8 hours (30000 seconds ≈ 8.3 hours)
				$cacheDuration = ads_clamp(intval(pluginGetVariable('ads_pro', 'cache_duration', 30000)), 300, 86400);
				ads_cache_put($cacheKey, $description, $cacheDuration);

				ads_logger('Rendered and cached block: id=' . $blockIndexNum . ', size=' . strlen($description) . ' bytes', 'info', 'ads_pro.log');
			} else {
				// PHP type block - execute dynamically (no caching)
				$description = '';
				if (is_array($row = $mysql->record('select ads_blok from ' . prefix . '_ads_pro where id=' . db_squote($blockIndexNum)))) {
					$description = $row['ads_blok'];
				}

				ads_logger('Executing PHP block: id=' . $blockIndexNum . ', block=' . $blockID, 'info', 'ads_pro.log');

				// Benchmark PHP block execution
				$result = ads_benchmark(function () use ($description, &$out2) {
					ob_start();
					@eval($description);
					$out2 = ob_get_contents();
					ob_end_clean();
				});

				ads_logger('PHP block executed: id=' . $blockIndexNum . ', time=' . round($result['time'] * 1000, 2) . 'ms, output=' . strlen($out2) . ' bytes', 'info', 'ads_pro.log');

				$template['vars'][$tplVarName] .= $out2;
			}
		}
	}
}
// Функция синхронизации данных плагина с базой данных
function ads_pro_sync_with_database($dataConfig)
{
	global $mysql;

	ads_logger('Starting database synchronization', 'debug', 'ads_pro.log');

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
		ads_logger('Sync: removing ' . count($missingInDb) . ' orphaned blocks from config, IDs=' . implode(',', $missingInDb), 'warning', 'ads_pro.log');

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

	// Если есть ID в БД, которых нет в конфиге - восстанавливаем их
	if (!empty($missingInConfig)) {
		ads_logger('Sync: restoring ' . count($missingInConfig) . ' blocks from database, IDs=' . implode(',', $missingInConfig), 'warning', 'ads_pro.log');

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
						ads_logger('Restored block ' . $dbId . ' from original config', 'info', 'ads_pro.log');
						break 2;
					}
				}
			}
		}

		// Для ID, которых нет в исходной конфигурации, создаем базовую структуру
		$notFoundInOriginal = array_diff($missingInConfig, $foundInOriginal);

		foreach ($notFoundInOriginal as $dbId) {
			$row = $mysql->record('SELECT * FROM ' . prefix . '_ads_pro WHERE id = ' . intval($dbId));

			if ($row) {
				$blockName = 'fase'; // Default block name
				$dataConfig[$blockName][$dbId] = array(
					'description' => $row['description'] ? ads_sanitize($row['description'], 'string') : 'Восстановленный блок #' . $dbId,
					'type' => ads_clamp(intval($row['type']), 0, 2), // 0=HTML, 1=PHP, 2=TEXT
					'state' => ads_clamp(intval($row['state']), 0, 2), // 0=off, 1=on, 2=scheduled
					'start_view' => $row['start_view'] ? intval($row['start_view']) : null,
					'end_view' => $row['end_view'] ? intval($row['end_view']) : null,
					'location' => $row['location'] ? unserialize($row['location']) : array(1 => array('mode' => 0, 'view' => 0))
				);

				ads_logger('Restored block ' . $dbId . ' from database with basic structure', 'info', 'ads_pro.log');
			}
		}

		// Сохраняем обновленную конфигурацию
		pluginSetVariable('ads_pro', 'data', $dataConfig);
		pluginsSaveConfig();

		ads_logger('Database synchronization completed, config saved', 'info', 'ads_pro.log');
	} else {
		ads_logger('Database synchronization: no changes needed', 'debug', 'ads_pro.log');
	}

	return $dataConfig;
}
