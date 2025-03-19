<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
include_once root . "/includes/news.php";

register_plugin_page('rss_export', '', 'plugin_rss_export', 0);
register_plugin_page('rss_export', 'category', 'plugin_rss_export_category', 0);

function plugin_rss_export()
{
	plugin_rss_export_generate();
}

function plugin_rss_export_category($params)
{
	plugin_rss_export_generate($params['category']);
}

function plugin_rss_export_generate($catname = '')
{
	global $lang, $PFILTERS, $template, $config, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $mysql, $catz, $parse;

	// Disable executing of `index` action (widget plugins and so on..)
	actionDisable('index');

	// Suppress templates
	$SUPRESS_TEMPLATE_SHOW = 1;
	$SUPRESS_MAINBLOCK_SHOW = 1;

	// Break if category specified & doesn't exist
	if (($catname != '') && (!isset($catz[$catname]))) {
		header('HTTP/1.1 404 Not found');
		exit;
	}

	// Set correct HTTP headers for RSS feed
	header('Content-Type: application/rss+xml; charset=utf-8');

	// Generate header
	$xcat = (($catname != '') && isset($catz[$catname])) ? $catz[$catname] : '';

	// Generate cache file name
	$cacheFileName = md5('rss_export' . $config['theme'] . $config['home_url'] . $config['default_lang'] . (is_array($xcat) ? $xcat['id'] : '') . pluginGetVariable('rss_export', 'use_hide') . is_array($userROW)) . '.txt';

	if (pluginGetVariable('rss_export', 'cache')) {
		$cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('rss_export', 'cacheExpire'), 'rss_export');
		if ($cacheData != false) {
			print $cacheData;
			return;
		}
	}

	// Generate output
	$output = plugin_rss_export_mk_header($xcat);

	$limit = pluginGetVariable('rss_export', 'news_count');
	$delay = intval(pluginGetVariable('rss_export', 'delay'));

	if ((!is_numeric($limit)) || ($limit < 0) || ($limit > 500)) {
		$limit = 50;
	}

	$old_locale = setlocale(LC_TIME, 0);
	setlocale(LC_TIME, 'en_EN');

	if (is_array($xcat)) {
		$orderBy = ($xcat['orderby'] && in_array($xcat['orderby'], array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc'))) ? $xcat['orderby'] : 'id desc';
		$query = "select * from " . prefix . "_news where catid regexp '\\\\b(" . $xcat['id'] . ")\\\\b' and approve=1 " . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . "order by " . $orderBy;
	} else {
		$query = "select * from " . prefix . "_news where approve=1" . (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '') . " order by id desc";
	}

	// Fetch SQL record
	$sqlData = $mysql->select($query . " limit $limit");

	// Check if enclosure is requested and used for "images" field
	$xFList = array();
	$encImages = array();
	$enclosureIsImages = false;
	if (pluginGetVariable('rss_export', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
		$xFList = xf_configLoad();
		$eFieldName = pluginGetVariable('rss_export', 'xfEnclosure');
		if (isset($xFList['news'][$eFieldName]) && ($xFList['news'][$eFieldName]['type'] == 'images')) {
			$enclosureIsImages = true;
			$nAList = array();
			foreach ($sqlData as $row) {
				if ($row['num_images'] > 0)
					$nAList[] = $row['id'];
			}
			$iQuery = "select * from " . prefix . "_images where (linked_ds = 1) and (linked_id in (" . join(",", $nAList) . ")) and (plugin = 'xfields') and (pidentity = " . db_squote($eFieldName) . ")";
			foreach ($mysql->select($iQuery) as $row) {
				if (!isset($encImages[$row['linked_id']]))
					$encImages[$row['linked_id']] = $row;
			}
		}
	}

	$truncateLen = intval(pluginGetVariable('rss_export', 'truncate'));
	if ($truncateLen < 0)
		$truncateLen = 0;

	foreach ($sqlData as $row) {
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

		$output .= "  <item>\n";
		$output .= "   <title><![CDATA[" . secure_html($row['title']) . "]]></title>\n";
		$output .= "   <link><![CDATA[" . newsGenerateLink($row, false, 0, true) . "]]></link>\n";
		$output .= "   <description><![CDATA[" . strip_tags($content) . "]]></description>\n";

		if ($enclosure != '') {
			// Определяем размер файла и MIME-тип
			$fileSize = 0;
			$mimeType = 'application/octet-stream'; // Значение по умолчанию

			if (filter_var($enclosure, FILTER_VALIDATE_URL)) {
				// Удаленный файл
				$headers = @get_headers($enclosure, 1);
				$fileSize = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;
				$mimeType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'application/octet-stream';
			} else {
				// Локальный файл
				if (file_exists($enclosure)) {
					$fileSize = @filesize($enclosure);
					$mimeType = mime_content_type($enclosure);
				}
			}

			// Формируем тег <enclosure>
			$output .= '   <enclosure url="' . $enclosure . '" length="' . $fileSize . '" type="' . $mimeType . '" />' . "\n";
		}

		$output .= "   <category>" . GetCategories($row['catid'], true) . "</category>\n";
		$output .= "   <guid isPermaLink=\"false\">" . home . "?id=" . $row['id'] . "</guid>\n";
		$output .= "   <pubDate>" . gmstrftime('%a, %d %b %Y %H:%M:%S GMT', $row['postdate']) . "</pubDate>\n";
		$output .= "  </item>\n";
	}

	setlocale(LC_TIME, $old_locale);
	$output .= " </channel>\n</rss>\n";

	print $output;

	if (pluginGetVariable('rss_export', 'cache')) {
		cacheStoreFile($cacheFileName, $output, 'rss_export');
	}
}

function plugin_rss_export_mk_header($xcat)
{
	global $config;

	$line = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
$line .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
    $line .= " <channel>\n";
        $line .= '
        <atom:link href="' . $config['home_url'] . '/rss.xml" rel="self" type="application/rss+xml" />' . "\n";

        if (pluginGetVariable('rss_export', 'feed_title_format') == 'handy') {
        $line .= " <title>
            <![CDATA[" . pluginGetVariable('rss_export', 'feed_title_value') . "]]>
        </title>\n";
        } else if ((pluginGetVariable('rss_export', 'feed_title_format') == 'site_title') && is_array($xcat)) {
        $line .= " <title>
            <![CDATA[" . $config['home_title'] . (is_array($xcat) ? ' :: ' . $xcat['name'] : '') . "]]>
        </title>\n";
        } else {
        $line .= " <title>
            <![CDATA[" . $config['home_title'] . "]]>
        </title>\n";
        }

        $line .= "
        <link>
        <![CDATA[" . $config['home_url'] . "]]>
        </link>\n";
        $line .= " <language>ru</language>\n";
        $line .= " <description>
            <![CDATA[" . $config['description'] . "]]>
        </description>\n";
        $line .= " <generator>
            <![CDATA[Plugin RSS_EXPORT (0.07) // Next Generation CMS (" . engineVersion . ")]]>
        </generator>\n";

        return $line;
        }