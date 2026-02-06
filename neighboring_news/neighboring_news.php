<?php
/*
 * NeighboringNews for NGCMS
 * Copyright (C) 2010 Alexey N. Zhukov (http://digitalplace.ru)
 * http://digitalplace.ru
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */
// Protect against hack attempts
if (!defined('NGCMS')) die('Galaxy in danger');

// Modernized with ng-helpers v0.2.2 (2026)

// Wrapper functions for ng-helpers compatibility
function nn_cache($key, $callback, $minutes = 60)
{
	if (function_exists('Plugins\\cache')) {
		return \Plugins\cache($key, $callback, $minutes);
	}
	return $callback();
}

function nn_logger($message, $level = 'info', $file = 'plugin.log')
{
	if (function_exists('Plugins\\logger')) {
		return \Plugins\logger($message, $level, $file);
	}
	return true;
}

function nn_sanitize($data, $type = 'string')
{
	if (function_exists('Plugins\\sanitize')) {
		return \Plugins\sanitize($data, $type !== 'html');
	}
	return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function nn_array_get($array, $key, $default = null)
{
	if (function_exists('Plugins\\array_get')) {
		return \Plugins\array_get($array, $key, $default);
	}
	return $array[$key] ?? $default;
}

class NeighboringNewsFilter extends NewsFilter
{

	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{
		global $mysql, $config, $tpl, $catz, $catmap, $CurrentHandler;

		$style = $mode['style'] ?? '';
		$fullEnabled  = pluginGetVariable('neighboring_news', 'full_mode')  && $style == 'full';
		$shortEnabled = pluginGetVariable('neighboring_news', 'short_mode') && $style == 'short';

		nn_logger('[neighboring_news] Called: newsid=' . $newsID . ', style=' . $style . ', full_enabled=' . ($fullEnabled ? 'yes' : 'no') . ', short_enabled=' . ($shortEnabled ? 'yes' : 'no'), 'debug', 'neighboring_news.log');

		if (!$fullEnabled && !$shortEnabled) {
			nn_logger('[neighboring_news] Disabled for style: ' . $style, 'debug', 'neighboring_news.log');
			return 1;
		}

		// Log category info for debugging
		$catid = $SQLnews['catid'] ?? '0';
		nn_logger('[neighboring_news] Category check: newsid=' . $newsID . ', catid="' . $catid . '"', 'debug', 'neighboring_news.log');

		// Check cache
		$cacheExpire = intval(pluginGetVariable('neighboring_news', 'cache_expire') ?? 0);
		nn_logger('[neighboring_news] Cache settings: expire=' . $cacheExpire, 'debug', 'neighboring_news.log');

		if ($cacheExpire > 0) {
			$cacheKey = 'neighboring_news_' . $newsID . '_' . $style . '_' . md5($SQLnews['catid']);

			// Try to get from cache
			$cached = nn_cache($cacheKey, function () {
				return null; // Will generate below
			}, $cacheExpire * 60); // Convert minutes to seconds

			if ($cached !== null) {
				nn_logger('[neighboring_news] Cache hit: newsid=' . $newsID . ', style=' . $style, 'debug', 'neighboring_news.log');
				$tvars['vars']['neighboring_news'] = $cached;
				return 1;
			}
		}

		nn_logger('[neighboring_news] Starting generation: newsid=' . $newsID, 'debug', 'neighboring_news.log');

		$tpath = locatePluginTemplates(['neighboring_news', 'next_news', 'previous_news'], 'neighboring_news', pluginGetVariable('neighboring_news', 'localsource'));

		// Главная категория новости
		$fcat = array_shift(explode(',', $SQLnews['catid']));
		$compareStrict = intval(pluginGetVariable('neighboring_news', 'compare')) == 1;
		$catFilter = $compareStrict ? $fcat : "'" . $SQLnews['catid'] . "'";

		// Определяем сортировку
		if ($style == 'short' && $CurrentHandler['params']['category'] != '') {
			$sort = explode(' ', $catz[$CurrentHandler['params']['category']]['orderby']);
			$catFilterShort = $catz[$CurrentHandler['params']['category']]['id'];
		} elseif ($style == 'full' && intval($fcat) > 0 && isset($catmap[$fcat])) {
			$sort = explode(' ', $catz[$catmap[$fcat]]['orderby']);
		} else {
			$sort = explode(' ', $config['default_newsorder']);
		}

		$orderField = $sort[0];
		$orderDir   = strtolower($sort[1]);

		// Функция построения соседней новости (direction: next|prev)
		$fetchNeighbor = function (string $direction) use ($mysql, $SQLnews, $orderField, $orderDir, $catFilter, $style, $CurrentHandler) {
			// Для направления меняем операторы
			$isAsc = ($orderDir == 'asc');
			$moreLess = ($direction == 'next') ? ($isAsc ? '<' : '>') : ($isAsc ? '>' : '<');
			$newOrder = ($direction == 'next') ? ($isAsc ? 'desc' : 'asc') : ($isAsc ? 'desc' : 'asc');

			// Базовое условие по категориям
			if ($style == 'short' && $CurrentHandler['params']['category'] != '') {
				$cid = intval($CurrentHandler['params']['category']);
				$catCond = "(catid LIKE '%,$cid,%' OR catid LIKE '%,$cid' OR catid LIKE '$cid,%' OR catid = $cid)";
			} else {
				// Если catid = 0 (нет категории), ищем другие новости с catid = 0 или '0'
				if (intval($catFilter) == 0) {
					$catCond = "(catid = '0' OR catid = 0 OR catid IS NULL OR catid = '')";
				} else {
					$catCond = "catid = $catFilter";
				}
			}

			$sql = "SELECT id, alt_name, catid, postdate, author, author_id, title FROM " . prefix . "_news WHERE APPROVE='1' AND $orderField $moreLess '" . $SQLnews[$orderField] . "' AND $catCond ORDER BY $orderField $newOrder LIMIT 1";
			return $mysql->record($sql);
		};

		$rowNext = $fetchNeighbor('next');
		$rowPrev = $fetchNeighbor('prev');

		nn_logger('[neighboring_news] Neighbors found: newsid=' . $newsID . ', next=' . ($rowNext ? 'yes(id=' . $rowNext['id'] . ')' : 'no') . ', prev=' . ($rowPrev ? 'yes(id=' . $rowPrev['id'] . ')' : 'no'), 'debug', 'neighboring_news.log');

		$buildItem = function ($row, $templateName) use ($tpl, $tpath, $config) {
			if (!$row || !$row['alt_name']) return '';
			$authorLink = $row['author'];
			if ($row['author_id'] && getPluginStatusActive('uprofile')) {
				$alink = checkLinkAvailable('uprofile', 'show') ?
					generateLink('uprofile', 'show', ['name' => $row['author'], 'id' => $row['author_id']]) :
					generateLink('core', 'plugin', ['plugin' => 'uprofile', 'handler' => 'show'], ['id' => $row['author_id']]);
				$authorLink = '<a href="' . $config['home_url'] . $alink . '">' . htmlspecialchars($row['author']) . '</a>';
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

			$tpl->template($templateName, $tpath[$templateName]);
			$tpl->vars($templateName, ['vars' => [
				'link'     => newsGenerateLink(['id' => $row['id'], 'alt_name' => $row['alt_name'], 'catid' => $row['catid'], 'postdate' => $row['postdate']], false, 0, true),
				'date'     => langdate('d.m.Y', $row['postdate']),
				'time_ago' => $time_ago_str,
				'author'   => $authorLink,
				'title'    => $row['title'],
			]]);
			return $tpl->show($templateName);
		};

		$nextHTML = $buildItem($rowNext, 'next_news');
		$prevHTML = $buildItem($rowPrev, 'previous_news');

		$tpl->template('neighboring_news', $tpath['neighboring_news']);
		$tpl->vars('neighboring_news', ['vars' => [
			'next_news'     => $nextHTML,
			'previous_news' => $prevHTML,
		]]);
		$output = ($nextHTML || $prevHTML) ? $tpl->show('neighboring_news') : '';
		$tvars['vars']['neighboring_news'] = $output;

		nn_logger('[neighboring_news] Output generated: newsid=' . $newsID . ', has_output=' . ($output ? 'yes(len=' . strlen($output) . ')' : 'no'), 'debug', 'neighboring_news.log');

		// Save to cache
		if ($cacheExpire > 0 && $output) {
			$cacheKey = 'neighboring_news_' . $newsID . '_' . $style . '_' . md5($SQLnews['catid']);

			nn_cache($cacheKey, function () use ($output) {
				return $output;
			}, $cacheExpire * 60);

			nn_logger('[neighboring_news] Generated and cached: newsid=' . $newsID . ', style=' . $style . ', size=' . strlen($output) . ' bytes, has_next=' . ($rowNext ? 'yes' : 'no') . ', has_prev=' . ($rowPrev ? 'yes' : 'no'), 'info', 'neighboring_news.log');
		} elseif (!$output) {
			nn_logger('[neighboring_news] No neighbors found: newsid=' . $newsID . ', style=' . $style, 'debug', 'neighboring_news.log');
		}

		return 1;
	}
}
register_filter('news', 'neighboring_news', new NeighboringNewsFilter);
