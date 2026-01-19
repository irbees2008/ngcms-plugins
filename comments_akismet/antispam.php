<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('naHAL!1');

use function Plugins\{logger, get_ip, sanitize};

// Load LIBRARY
loadPluginLibrary('comments', 'lib');
include_once(root . "/plugins/comments_akismet/inc/Akismet.class.php");

class AntispamFilterComments extends FilterComments
{

	function addComments($userRec, $newsRec, &$tvars, &$SQL)
	{

		$akis = new Akismet(home, pluginGetVariable('comments_akismet', 'akismet_apikey'));
		$akis->setAkismetServer(pluginGetVariable('comments_akismet', 'akismet_server'));

		if ($akis->isKeyValid()) {
			$akis->setCommentAuthor($SQL['author']);
			$akis->setCommentAuthorEmail($SQL['mail']);
			$akis->setCommentContent($SQL['text']);

			if ($akis->isCommentSpam()) {
				logger('comments_akismet', 'SPAM BLOCKED: author=' . sanitize($SQL['author']) . ', email=' . sanitize($SQL['mail']) . ', ip=' . get_ip() . ', news_id=' . ($newsRec['id'] ?? 'unknown'));
				return array('result' => 0, 'errorText' => 'Akismet blocked your comment!');
			} else {
				logger('comments_akismet', 'Comment approved: author=' . sanitize($SQL['author']) . ', ip=' . get_ip() . ', news_id=' . ($newsRec['id'] ?? 'unknown'));
				return 1;
			}
		} else {
			logger('comments_akismet', 'ERROR: Invalid API key - ' . pluginGetVariable('comments_akismet', 'akismet_apikey'));
			return array('result' => 0, 'errorText' => 'Akismet key is invalid! ' . pluginGetVariable('comments_akismet', 'akismet_apikey'));
		}
	}
}

register_filter('comments', 'antispam', new AntispamFilterComments);
