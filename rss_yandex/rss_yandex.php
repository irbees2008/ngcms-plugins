<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

include_once root . "/includes/news.php";

register_plugin_page('rss_yandex', '', 'plugin_rss_yandex', 0);
register_plugin_page('rss_yandex', 'category', 'plugin_rss_yandex_category', 0);

function plugin_rss_yandex() {
	plugin_rss_yandex_generate();
}

function plugin_rss_yandex_category($params) {

	plugin_rss_yandex_generate($params['category']);
}

function plugin_rss_yandex_generate($catname = '') {

	global $lang, $PFILTERS, $template, $config, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW, $mysql, $catz, $parse;
	
	// Initiate instance of TWIG engine with string loader
	//$twigString = new Twig_Environment($twigStringLoader);
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
	// Generate cache file name [ we should take into account SWITCHER plugin ]
	// Take into account: FLAG: use_hide, check if user is logged in
	$cacheFileName = md5('rss_yandex' . $config['theme'] . $config['home_url'] . $config['default_lang'] . (is_array($xcat) ? $xcat['id'] : '') . pluginGetVariable('rss_yandex', 'use_hide') . is_array($userROW)) . '.txt';
	if (pluginGetVariable('rss_yandex', 'cache')) {
		$cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('rss_yandex', 'cacheExpire'), 'rss_yandex');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			print $cacheData;

			return;
		}
	}
	// Generate output
	$output = plugin_rss_yandex_mk_header($xcat);
	$maxAge = pluginGetVariable('rss_yandex', 'news_age');
	$delay = intval(pluginGetVariable('rss_yandex', 'delay'));
	if ((!is_numeric($maxAge)) || ($maxAge < 0) || ($maxAge > 100)) {
		$maxAge = 10;
	}
	$old_locale = setlocale(LC_TIME, 0);
	setlocale(LC_TIME, 'en_EN');
	$query = '';
	$orderBy = "id desc";
	if (is_array($xcat)) {
		$orderBy = ($xcat['orderby'] && in_array($xcat['orderby'], array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc'))) ? $xcat['orderby'] : 'id desc';
		$query = "select * from " . prefix . "_news where catid regexp '[[:<:]](" . $xcat['id'] . ")[[:>:]]' and approve=1 ";
	} else {
		$query = "select * from " . prefix . "_news where approve=1 ";
	}
	$query .= (($delay > 0) ? (" and ((postdate + " . intval($delay * 60) . ") < unix_timestamp(now())) ") : '');
	$query .= " and ((postdate + " . intval($maxAge * 86400) . ") > unix_timestamp(now())) ";
	$query .= "" . " order by " . $orderBy;
	// Fetch SQL record
	$sqlData = $mysql->select($query . " limit 100");
	// Check if enclosure is requested and used for "images" field
	$xFList = array();
	$encImages = array();
	$enclosureIsImages = false;
	if (pluginGetVariable('rss_yandex', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
		$xFList = xf_configLoad();
		$eFieldName = pluginGetVariable('rss_yandex', 'xfEnclosure');
		if (isset($xFList['news'][$eFieldName]) && ($xFList['news'][$eFieldName]['type'] == 'images')) {
			$enclosureIsImages = true;
			// Prepare list of news with attached images
			$nAList = array();
			foreach ($sqlData as $row) {
				if ($row['num_images'] > 0)
					$nAList [] = $row['id'];
			}
			$iQuery = "select * from " . prefix . "_images where (linked_ds = 1) and (linked_id in (" . join(",", $nAList) . ")) and (plugin = 'xfields') and (pidentity = " . db_squote($eFieldName) . ")";
			foreach ($mysql->select($iQuery) as $row) {
				if (!isset($encImages[$row['linked_id']]))
					$encImages[$row['linked_id']] = $row;
			}
		}
	}

	foreach ($sqlData as $row) {
		// Make standard system call in 'export' mode
		$newsVars = news_showone($row['id'], '', array(
			'emulate' => $row,
			'style' => 'exportVars',
			'extractEmbeddedItems' => pluginGetVariable('rss_yandex', 'textEnclosureEnabled') ? 1 : 0,
			'plugin' => 'rss_yandex'
		));

		$export_mode = 'export_body';
		switch (pluginGetVariable('rss_yandex', 'full_format')) {
			case '1':
				$export_mode = 'export_short';
				break;
			case '2':
				$export_mode = 'export_full';
				break;
		}
		$content = news_showone($row['id'], '', array('emulate' => $row, 'style' => $export_mode, 'plugin' => 'rss_yandex'));

		$enclosureList = array();

		// Check if Enclosure `xfields` integration is activated
		if (pluginGetVariable('rss_yandex', 'xfEnclosureEnabled') && getPluginStatusActive('xfields')) {
			include_once(root . "/plugins/xfields/xfields.php");
			if (is_array($xfd = xf_decode($row['xfields'])) && isset($xfd[pluginGetVariable('rss_yandex', 'xfEnclosure')])) {
				$enclosureUrl = '';
				if ($enclosureIsImages) {
					if (isset($encImages[$row['id']])) {
						$enclosureUrl = ($encImages[$row['id']]['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $encImages[$row['id']]['folder'] . '/' . $encImages[$row['id']]['name'];
					}
				} else {
					$enclosureUrl = $xfd[pluginGetVariable('rss_yandex', 'xfEnclosure')];
				}

				if ($enclosureUrl) {
					$fileSize = 0;
					$mimeType = 'application/octet-stream';

					if (filter_var($enclosureUrl, FILTER_VALIDATE_URL)) {
						$headers = @get_headers($enclosureUrl, 1);
						$fileSize = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;
						$mimeType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'application/octet-stream';
					} else {
						if (file_exists($enclosureUrl)) {
							$fileSize = @filesize($enclosureUrl);
							$mimeType = mime_content_type($enclosureUrl);
						}
					}

					$enclosureList[] = '   <enclosure url="' . $enclosureUrl . '" length="' . $fileSize . '" type="' . $mimeType . '" />';
				}
			}
		}

		// Check if embedded items should be exported in enclosure
		if (pluginGetVariable('rss_yandex', 'textEnclosureEnabled') && isset($newsVars['news']['embed']['images']) && is_array($newsVars['news']['embed']['images'])) {
			foreach ($newsVars['news']['embed']['images'] as $url) {
				if (!preg_match('#^http(s{0,1})\:\/\/#', $url)) {
					$url = home . $url;
				}

				$fileSize = 0;
				$mimeType = 'image/jpeg';

				if (filter_var($url, FILTER_VALIDATE_URL)) {
					$headers = @get_headers($url, 1);
					$fileSize = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;
					$mimeType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'image/jpeg';
				}

				$enclosureList[] = '   <enclosure url="' . $url . '" length="' . $fileSize . '" type="' . $mimeType . '" />';
			}
		}

		$newsTitleFormat = str_replace(
			array('%site_title%', '%news_title%', '%cat_title%'),
			array($config['home_title'], secure_html($row['title']), GetCategories($row['catid'], true)),
			pluginGetVariable('rss_yandex', 'news_title')
		);

		// Обрезаем описание до 500 символов и убираем HTML-теги
		$shortDescription = strip_tags($newsVars['short-story']); // Убираем HTML-теги
		$shortDescription = mb_substr($shortDescription, 0, 500, 'UTF-8'); // Обрезаем до 500 символов
		if (mb_strlen($newsVars['short-story'], 'UTF-8') > 500) {
			$shortDescription .= '...'; // Добавляем многоточие, если текст был обрезан
		}

		$output .= "  <item>\n";
		$output .= "   <title><![CDATA[" . $newsTitleFormat . "]]></title>\n";
		$output .= "   <link><![CDATA[" . newsGenerateLink($row, false, 0, true) . "]]></link>\n";
		$output .= "   <pubDate>" . gmstrftime('%a, %d %b %Y %H:%M:%S GMT', $row['postdate']) . "</pubDate>\n";
		$output .= "   <yandex:full-text>" . htmlspecialchars($content) . "</yandex:full-text>\n";
		$output .= "   <description><![CDATA[" . htmlspecialchars($shortDescription) . "]]></description>\n";

		// Generate list of enclosures
		$output .= join("\n", $enclosureList);
		if (count($enclosureList)) {
			$output .= "\n";
		}

		if (is_array($xcat)) {
			$main_cat_name = $xcat['name'];
		} else {
			$main_cat_name = explode(',', GetCategories($row['catid'], true));
			$main_cat_name = $main_cat_name[0];
		}
		$output .= "   <category>" . $main_cat_name . "</category>\n";
		$output .= "   <guid isPermaLink=\"false\">" . home . "?id=" . $row['id'] . "</guid>\n";
		$output .= "  </item>\n";
	}
	setlocale(LC_TIME, $old_locale);
	$output .= " </channel>\n</rss>\n";
	// Print output
	print $output;
	if (pluginGetVariable('rss_yandex', 'cache')) {
		cacheStoreFile($cacheFileName, $output, 'rss_yandex');
	}
}

function plugin_rss_yandex_mk_header($xcat) {

	global $config;
	// Initiate instance of TWIG engine with string loader

	$feedTitleFormat = str_replace('%site_title%', $config['home_title'], pluginGetVariable('rss_yandex', 'feed_title')); 
	// Generate RSS header
	$line = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$line .= ' <rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:turbo="http://turbo.yandex.ru" version="2.0">' . "\n";
    $line .= " <channel>\n";
        // Channel title
        $line .= " <title>
            <![CDATA[" . $feedTitleFormat/* $config['home_title'] . (is_array($xcat) ? ' :: ' . $xcat['name'] : '') */ . "]]>
        </title>\n";
        // LINK
        $line .= "
        <link>
        <![CDATA[" . $config['home_url'] . "]]>
        </link>\n";
        // Description
        $line .= " <description>
            <![CDATA[" . $config['description'] . "]]>
        </description>\n";
        // Image
        $imgInfo = array(
        'url' => pluginGetVariable('rss_yandex', 'feed_image_url') ? pluginGetVariable('rss_yandex', 'feed_image_url') :
        'http://ngcms.ru/templates/ngcms2/images/logo.png',
        'title' => pluginGetVariable('rss_yandex', 'feed_image_title') ? pluginGetVariable('rss_yandex',
        'feed_image_title') : 'Next generation CMS demo RSS feed',
        'link' => pluginGetVariable('rss_yandex', 'feed_image_link') ? pluginGetVariable('rss_yandex',
        'feed_image_link') : 'http://ngcms.ru/',
        );
        $line .= " <image>\n";
            $line .= " <url>" . $imgInfo['url'] . "</url>\n";
            $line .= " <title>
                <![CDATA[" . $imgInfo['title'] . "]]>
            </title>\n";
            $line .= "
            <link>" . $imgInfo['link'] . "</link>\n";
            $line .= "
        </image>\n";
        $line .= " <generator>
            <![CDATA[Plugin rss_yandex (0.01) // Next Generation CMS (" . engineVersion . ")]]>
        </generator>\n";

        return $line;
        }