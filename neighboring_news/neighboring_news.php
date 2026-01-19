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

// Modified with ng-helpers v0.2.0 functions (2026)
// - Added cache_get/cache_put for caching neighboring news
// - Added logger for operations tracking
// - Added time_ago for relative timestamps

// Import ng-helpers functions
use function Plugins\{cache_get, cache_put, logger, time_ago};

class NeighboringNewsFilter extends NewsFilter
{

	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{
		global $mysql, $config, $tpl, $catz, $catmap, $CurrentHandler;

		$style = $mode['style'] ?? '';
		$fullEnabled  = pluginGetVariable('neighboring_news', 'full_mode')  && $style == 'full';
		$shortEnabled = pluginGetVariable('neighboring_news', 'short_mode') && $style == 'short';
		if (!$fullEnabled && !$shortEnabled) {
			return 1;
		}
		if (!intval($SQLnews['catid'])) {
			$tvars['vars']['neighboring_news'] = '';
			return 1;
		}

		// Check cache
		$cacheExpire = intval(pluginGetVariable('neighboring_news', 'cache_expire') ?? 0);
		if ($cacheExpire > 0) {
			$cacheKey = 'neighboring_news:' . $newsID . ':' . $style . ':' . md5($SQLnews['catid']);
			$cached = cache_get($cacheKey);
			if ($cached !== null) {
				$tvars['vars']['neighboring_news'] = $cached;
				return 1;
			}
		}

		$tpath = locatePluginTemplates(['neighboring_news', 'next_news', 'previous_news'], 'neighboring_news', pluginGetVariable('neighboring_news', 'localsource'));

		// Главная категория новости
		$fcat = array_shift(explode(',', $SQLnews['catid']));
		$compareStrict = intval(pluginGetVariable('neighboring_news', 'compare')) == 1;
		$catFilter = $compareStrict ? $fcat : "'" . $SQLnews['catid'] . "'";

		// Определяем сортировку
		if ($style == 'short' && $CurrentHandler['params']['category'] != '') {
			$sort = explode(' ', $catz[$CurrentHandler['params']['category']]['orderby']);
			$catFilterShort = $catz[$CurrentHandler['params']['category']]['id'];
		} elseif ($style == 'full') {
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
				$catCond = "catid = $catFilter";
			}

			$sql = "SELECT id, alt_name, catid, postdate, author, author_id, title FROM " . prefix . "_news WHERE APPROVE='1' AND $orderField $moreLess '" . $SQLnews[$orderField] . "' AND $catCond ORDER BY $orderField $newOrder LIMIT 1";
			return $mysql->record($sql);
		};

		$rowNext = $fetchNeighbor('next');
		$rowPrev = $fetchNeighbor('prev');

		$buildItem = function ($row, $templateName) use ($tpl, $tpath, $config) {
			if (!$row || !$row['alt_name']) return '';
			$authorLink = $row['author'];
			if ($row['author_id'] && getPluginStatusActive('uprofile')) {
				$alink = checkLinkAvailable('uprofile', 'show') ?
					generateLink('uprofile', 'show', ['name' => $row['author'], 'id' => $row['author_id']]) :
					generateLink('core', 'plugin', ['plugin' => 'uprofile', 'handler' => 'show'], ['id' => $row['author_id']]);
				$authorLink = '<a href="' . $config['home_url'] . $alink . '">' . htmlspecialchars($row['author']) . '</a>';
			}
			$tpl->template($templateName, $tpath[$templateName]);
			$tpl->vars($templateName, ['vars' => [
				'link'     => newsGenerateLink(['id' => $row['id'], 'alt_name' => $row['alt_name'], 'catid' => $row['catid'], 'postdate' => $row['postdate']], false, 0, true),
				'date'     => langdate('d.m.Y', $row['postdate']),
				'time_ago' => time_ago($row['postdate']),
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

		// Save to cache
		if ($cacheExpire > 0 && $output) {
			$cacheKey = 'neighboring_news:' . $newsID . ':' . $style . ':' . md5($SQLnews['catid']);
			cache_put($cacheKey, $output, $cacheExpire);
			logger('neighboring_news', 'Cached: newsid=' . $newsID . ', style=' . $style . ', has_next=' . ($rowNext ? 'yes' : 'no') . ', has_prev=' . ($rowPrev ? 'yes' : 'no'));
		}

		return 1;
	}
}
register_filter('news', 'neighboring_news', new NeighboringNewsFilter);
