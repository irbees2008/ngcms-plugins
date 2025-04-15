<?php
if (!defined('NGCMS')) die('HAL');

add_act('index_post', 'simple_title_pro');

function simple_title_pro()
{
	global $template, $SYSTEM_FLAGS, $CurrentHandler, $mysql, $config, $catz, $catmap;

	$pageNo = !empty($CurrentHandler['params']['page']) ? str_replace('%count%', intval($CurrentHandler['params']['page']), pluginGetVariable('simple_title_pro', 'num_title')) : '';

	$html = !empty($SYSTEM_FLAGS['info']['title']['secure_html']) ? str_replace('%html%', $SYSTEM_FLAGS['info']['title']['secure_html'], pluginGetVariable('simple_title_pro', 'html_secure')) : '';

	switch ($CurrentHandler['pluginName']) {
		case 'news':
			if ($CurrentHandler['handlerName'] == 'by.category') {
				if (isset($SYSTEM_FLAGS['news']['currentCategory.alt'])) {
					$cat_name[] = $catz[$SYSTEM_FLAGS['news']['currentCategory.alt']]['name'];
					$id = $catz[$CurrentHandler['params']['category']]['parent'];
					while ($id <> 0) {
						$cat_name[] = $catz[$catmap[$id]]['name'];
						$id = $catz[$catmap[$id]]['parent'];
					}
					$cat_name = implode(" / ", $cat_name);
				} else {
					$cat_name = 'Нет категории';
				}

				$cacheFileName = md5('block_directory_sites_cat' . $SYSTEM_FLAGS['news']['currentCategory.id'] . $config['default_lang']) . '.txt';

				// Получаем заголовок из БД
				$title_rec = $mysql->record('SELECT title FROM ' . prefix . '_simple_title_pro WHERE cat_id = ' . db_squote($SYSTEM_FLAGS['news']['currentCategory.id']) . ' LIMIT 1');
				$title = is_array($title_rec) && isset($title_rec['title']) ? $title_rec['title'] : pluginGetVariable('simple_title_pro', 'c_title');

				if (true) {
					cacheStoreFile($cacheFileName, $title, 'simple_title_pro');
				}

				$title = preg_replace('/\[([^\[\]]+)\]/', (isset($pageNo) && $pageNo) ? '\\1' : '', $title);
				$template['vars']['titles'] = trim(str_replace(
					array('%cat%', '%home%', '%num%'),
					array($cat_name, $SYSTEM_FLAGS['info']['title']['header'], $pageNo),
					$title
				));
			}

			if ($CurrentHandler['handlerName'] == 'news') {
				if (isset($SYSTEM_FLAGS['news']['currentCategory.alt'])) {
					$cat_name[] = $catz[$SYSTEM_FLAGS['news']['currentCategory.alt']]['name'];
					$id = $catz[$CurrentHandler['params']['category']]['parent'];
					while ($id <> 0) {
						$cat_name[] = $catz[$catmap[$id]]['name'];
						$id = $catz[$catmap[$id]]['parent'];
					}
					$cat_name = implode(" / ", $cat_name);
				} else {
					$cat_name = 'Нет категории';
				}

				$cacheFileName = md5('block_directory_sites_news' . $SYSTEM_FLAGS['news']['db.id'] . $config['default_lang']) . '.txt';

				// Получаем заголовок из БД
				$title_rec = $mysql->record('SELECT title FROM ' . prefix . '_simple_title_pro WHERE news_id = ' . db_squote($SYSTEM_FLAGS['news']['db.id']) . ' LIMIT 1');
				$title = is_array($title_rec) && isset($title_rec['title']) ? $title_rec['title'] : pluginGetVariable('simple_title_pro', 'n_title');

				if (true) {
					cacheStoreFile($cacheFileName, $title, 'simple_title_pro');
				}

				$title = preg_replace('/\[([^\[\]]+)\]/', (isset($pageNo) && $pageNo) ? '\\1' : '', $title);
				$template['vars']['titles'] = trim(str_replace(
					array('%cat%', '%title%', '%home%', '%num%'),
					array($cat_name, $SYSTEM_FLAGS['info']['title']['item'], $SYSTEM_FLAGS['info']['title']['header'], $pageNo),
					$title
				));
			}

			if ($CurrentHandler['handlerName'] == 'main') {
				$m_title = preg_replace('/\[([^\[\]]+)\]/', (isset($pageNo) && $pageNo) ? '\\1' : '', pluginGetVariable('simple_title_pro', 'm_title'));
				$template['vars']['titles'] = trim(str_replace(
					array('%home%', '%num%'),
					array($SYSTEM_FLAGS['info']['title']['header'], $pageNo),
					$m_title
				));
			}
			break;

		case 'static':
			$cacheFileName = md5('block_directory_sites_static' . $SYSTEM_FLAGS['static']['db.id'] . $config['default_lang']) . '.txt';

			// Получаем заголовок из БД
			$title_rec = $mysql->record('SELECT title FROM ' . prefix . '_simple_title_pro WHERE static_id = ' . db_squote($SYSTEM_FLAGS['static']['db.id']) . ' LIMIT 1');
			$title = is_array($title_rec) && isset($title_rec['title']) ? $title_rec['title'] : pluginGetVariable('simple_title_pro', 'static_title');

			if (true) {
				cacheStoreFile($cacheFileName, $title, 'simple_title_pro');
			}

			$template['vars']['titles'] = trim(str_replace(
				array('%static%', '%home%'),
				array($SYSTEM_FLAGS['info']['title']['item'], $SYSTEM_FLAGS['info']['title']['header']),
				$title
			));
			break;

		default:
			$list_plugin = array_map('trim', explode(',', pluginGetVariable('simple_title_pro', 'p_title')));
			if (isset($CurrentHandler['pluginName']) && $CurrentHandler['pluginName']) {
				if (!in_array($CurrentHandler['pluginName'], $list_plugin)) {
					$o_title = preg_replace('/\[([^\[\]]+)\]/', (isset($pageNo) && $pageNo) ? '\\1' : '', pluginGetVariable('simple_title_pro', 'o_title'));
					$template['vars']['titles'] = trim(str_replace(
						array('%home%', '%other%', '%html%', '%num%'),
						array($SYSTEM_FLAGS['info']['title']['header'], $SYSTEM_FLAGS['info']['title']['group'], $html, $pageNo),
						$o_title
					));
				}
			} else {
				$e_title = pluginGetVariable('simple_title_pro', 'e_title');
				$template['vars']['titles'] = trim(str_replace(
					array('%home%', '%other%'),
					array($SYSTEM_FLAGS['info']['title']['header'], $SYSTEM_FLAGS['info']['title']['group']),
					$e_title
				));
			}
	}
}