<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize};

// Load library
include_once(root . "/plugins/autokeys/lib/class.php");

// News filtering class
class autoKeysNewsFilter extends NewsFilter
{

	function addNews(&$tvars, &$SQL)
	{

		if ($_POST['autokeys_generate'] == 1) {
			$content = sanitize($SQL['content'] ?? '', 'html');
			$title = sanitize($SQL['title'] ?? '', 'string');
			$keywords = akeysGetKeys(array('content' => $content, 'title' => $title));
			$SQL['keywords'] = $keywords;
			logger('autokeys', 'Add news: generated ' . count(explode(', ', $keywords)) . ' keywords');
		}

		return 1;
	}

	function editNews($newsID, $SQLold, &$SQLnew, &$tvars)
	{

		if ($_POST['autokeys_generate'] == 1) {
			$content = sanitize($SQLnew['content'] ?? '', 'html');
			$title = sanitize($SQLnew['title'] ?? '', 'string');
			$keywords = akeysGetKeys(array('content' => $content, 'title' => $title));
			$SQLnew['keywords'] = $keywords;
			logger('autokeys', 'Edit news: newsID=' . $newsID . ', generated ' . count(explode(', ', $keywords)) . ' keywords');
		}

		return 1;
	}

	function editNewsForm($newsID, $SQLold, &$tvars)
	{

		global $twig;
		$tpath = locatePluginTemplates(array('editnews'), 'autokeys', pluginGetVariable('autokeys', 'localsource'));
		$xt = $twig->loadTemplate($tpath['editnews'] . '/editnews.tpl');
		$tvars['plugin']['autokeys'] = $xt->render(array('flags' => array('checked' => pluginGetVariable('autokeys', 'activate_edit'))));

		return 1;
	}

	function addNewsForm(&$tvars)
	{

		global $twig;
		$tpath = locatePluginTemplates(array('addnews'), 'autokeys', pluginGetVariable('autokeys', 'localsource'));
		$xt = $twig->loadTemplate($tpath['addnews'] . '/addnews.tpl');
		$tvars['plugin']['autokeys'] = $xt->render(array('flags' => array('checked' => pluginGetVariable('autokeys', 'activate_add'))));

		return 1;
	}
}

register_filter('news', 'autokeys', new autoKeysNewsFilter);
