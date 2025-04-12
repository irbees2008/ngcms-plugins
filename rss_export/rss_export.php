<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Очищаем все возможные буферы вывода
while (ob_get_level()) ob_end_clean();

// Устанавливаем заголовок XML ДО любого вывода
header('Content-Type: application/xml; charset=utf-8');

include_once root . "/includes/news.php";

register_plugin_page('rss_export', '', 'plugin_rss_export', 0);
register_plugin_page('rss_export', 'category', 'plugin_rss_export_category', 0);

function plugin_rss_export() {
    plugin_rss_export_generate();
}

function plugin_rss_export_category($params) {
    plugin_rss_export_generate($params['category']);
}

function plugin_rss_export_generate($catname = '')
{
	global $lang, $PFILTERS, $template, $config, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $mysql, $catz, $parse;

	actionDisable('index');
	$SUPRESS_TEMPLATE_SHOW = 1;
	$SUPRESS_MAINBLOCK_SHOW = 1;

	if (($catname != '') && (!isset($catz[$catname]))) {
		header('HTTP/1.1 404 Not found');
		exit;
	}

	$xcat = (($catname != '') && isset($catz[$catname])) ? $catz[$catname] : '';
	$cacheFileName = md5('rss_export' . $config['theme'] . $config['home_url'] . $config['default_lang'] . (is_array($xcat) ? $xcat['id'] : '') . pluginGetVariable('rss_export', 'use_hide') . is_array($userROW)) . '.txt';

	if (pluginGetVariable('rss_export', 'cache')) {
		$cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('rss_export', 'cacheExpire'), 'rss_export');
		if ($cacheData != false) {
			echo $cacheData;
			exit;
		}
	}

	// Получаем текущий URL ленты
$current_rss_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Генерация XML
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/">' . "\n";
echo "<channel>\n";
echo '<atom:link href="' . htmlspecialchars($current_rss_url, ENT_QUOTES, 'UTF-8') . '" rel="self" type="application/rss+xml" />' . "\n";
	// Заголовок канала
	if (pluginGetVariable('rss_export', 'feed_title_format') == 'handy') {
		echo "<title><![CDATA[" . htmlspecialchars(pluginGetVariable('rss_export', 'feed_title_value'), ENT_QUOTES, 'UTF-8') . "]]></title>\n";
	} else if ((pluginGetVariable('rss_export', 'feed_title_format') == 'site_title') && is_array($xcat)) {
		echo "<title><![CDATA[" . htmlspecialchars($config['home_title'] . (is_array($xcat) ? ' :: ' . $xcat['name'] : ''), ENT_QUOTES, 'UTF-8') . "]]></title>\n";
	} else {
		echo "<title><![CDATA[" . htmlspecialchars($config['home_title'], ENT_QUOTES, 'UTF-8') . "]]></title>\n";
	}

	echo "<link>" . htmlspecialchars($config['home_url'], ENT_QUOTES, 'UTF-8') . "</link>\n";
	echo "<language>ru</language>\n";
	echo "<description><![CDATA[" . htmlspecialchars($config['description'], ENT_QUOTES, 'UTF-8') . "]]></description>\n";
	echo "<generator><![CDATA[Plugin RSS_EXPORT (0.07) // Next Generation CMS (" . engineVersion . ")]]></generator>\n";

	// Инициализация xfields
	$xFList = array();
	$encImages = array();
	$enclosureIsImages = false;

	if (pluginGetVariable('rss_export', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
		$xFList = xf_configLoad();
		$eFieldName = pluginGetVariable('rss_export', 'xfEnclosure');
		if (isset($xFList['news'][$eFieldName]) && ($xFList['news'][$eFieldName]['type'] == 'images')) {
			$enclosureIsImages = true;
		}
	}

	// Получение новостей
	$limit = pluginGetVariable('rss_export', 'news_count');
	$delay = intval(pluginGetVariable('rss_export', 'delay'));
	if ((!is_numeric($limit)) || ($limit < 0) || ($limit > 500)) $limit = 50;

	$old_locale = setlocale(LC_TIME, 0);
	setlocale(LC_TIME, 'en_EN');

	if (is_array($xcat)) {
		$orderBy = ($xcat['orderby'] && in_array($xcat['orderby'], array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc'))) ? $xcat['orderby'] : 'id desc';
		$query = "select * from " . prefix . "_news where catid regexp '\\\\b(" . $xcat['id'] . ")\\\\b' and approve=1 " . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . "order by " . $orderBy;
	} else {
		$query = "select * from " . prefix . "_news where approve=1" . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . " order by id desc";
	}

	$sqlData = $mysql->select($query . " limit $limit");

	// Подготовка изображений для enclosure
	if ($enclosureIsImages) {
		$nAList = array();
		foreach ($sqlData as $row) {
			if ($row['num_images'] > 0)
				$nAList[] = $row['id'];
		}
		if (count($nAList)) {
			$iQuery = "select * from " . prefix . "_images where (linked_ds = 1) and (linked_id in (" . join(",", $nAList) . ")) and (plugin = 'xfields') and (pidentity = " . db_squote($eFieldName) . ")";
			foreach ($mysql->select($iQuery) as $row) {
				if (!isset($encImages[$row['linked_id']]))
					$encImages[$row['linked_id']] = $row;
			}
		}
	}

	$truncateLen = intval(pluginGetVariable('rss_export', 'truncate'));
	if ($truncateLen < 0) $truncateLen = 0;

	foreach ($sqlData as $row) {
		// Обработка контента
		$export_mode = 'export_body';
		switch (pluginGetVariable('rss_export', 'content_show')) {
			case '1':
				$export_mode = 'export_short';
				break;
			case '2':
				$export_mode = 'export_full';
				break;
		}

		$content = news_showone($row['id'], '', array('emulate' => $row, 'style' => $export_mode, 'plugin' => 'rss_export'));

		if ($truncateLen > 0) {
			$content = $parse->truncateHTML($content, $truncateLen, '...');
		}

		// Удаляем нежелательные теги
		$content = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $content);
		$content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);

		// Заменяем HTML-сущности на числовые
		$content = preg_replace_callback('/&(?!amp;|lt;|gt;|quot;|apos;)/', function ($m) {
			return '&amp;';
		}, $content);

		// Обработка enclosure
		$enclosure = '';
		if (pluginGetVariable('rss_export', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
			include_once(root . "/plugins/xfields/xfields.php");
			if (is_array($xfd = xf_decode($row['xfields'])) && isset($xfd[pluginGetVariable('rss_export', 'xfEnclosure')])) {
				if ($enclosureIsImages) {
					if (isset($encImages[$row['id']])) {
						$enclosure = ($encImages[$row['id']]['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $encImages[$row['id']]['folder'] . '/' . $encImages[$row['id']]['name'];
					}
				} else {
					$enclosure = $xfd[pluginGetVariable('rss_export', 'xfEnclosure')];
				}
			}
		}

		echo "  <item>\n";
		echo "   <title><![CDATA[" . ((pluginGetVariable('rss_export', 'news_title') == 1) && GetCategories($row['catid'], true) ? htmlspecialchars(GetCategories($row['catid'], true) . ' :: ', ENT_QUOTES, 'UTF-8') : '') . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . "]]></title>\n";
		echo "   <link>" . htmlspecialchars(newsGenerateLink($row, false, 0, true), ENT_QUOTES, 'UTF-8') . "</link>\n";
		echo "   <description><![CDATA[" . $content . "]]></description>\n";

		if ($enclosure != '') {
			echo '   <enclosure url="' . htmlspecialchars($enclosure, ENT_QUOTES, 'UTF-8') . '" length="0" type="' . ($enclosureIsImages ? 'image/jpeg' : 'application/octet-stream') . '" />' . "\n";
		}

		echo "   <category>" . htmlspecialchars(GetCategories($row['catid'], true), ENT_QUOTES, 'UTF-8') . "</category>\n";
		echo "   <guid isPermaLink=\"false\">" . htmlspecialchars(home . "?id=" . $row['id'], ENT_QUOTES, 'UTF-8') . "</guid>\n";

		// Проверка даты на валидность
		$pubDate = ($row['postdate'] > time()) ? time() : $row['postdate'];
		echo "   <pubDate>" . gmdate('r', $pubDate) . "</pubDate>\n";

		echo "  </item>\n";
	}

	setlocale(LC_TIME, $old_locale);
	echo " </channel>\n</rss>\n";

	if (pluginGetVariable('rss_export', 'cache')) {
		cacheStoreFile($cacheFileName, ob_get_contents(), 'rss_export');
	}
	exit;
}