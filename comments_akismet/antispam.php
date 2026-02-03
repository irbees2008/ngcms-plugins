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
				logger('SPAM BLOCKED: author=' . sanitize($SQL['author'], 'string') . ', email=' . sanitize($SQL['mail'], 'email') . ', ip=' . get_ip() . ', news_id=' . ($newsRec['id'] ?? 'unknown'), 'warning', 'comments_akismet.log');
				return array('result' => 0, 'errorText' => 'Akismet blocked your comment!');
			} else {
				logger('Comment approved: author=' . sanitize($SQL['author'], 'string') . ', ip=' . get_ip() . ', news_id=' . ($newsRec['id'] ?? 'unknown'), 'debug', 'comments_akismet.log');
				return 1;
			}
		} else {
			logger('ERROR: Invalid API key - ' . pluginGetVariable('comments_akismet', 'akismet_apikey'), 'error', 'comments_akismet.log');
			return array('result' => 0, 'errorText' => 'Akismet key is invalid! ' . pluginGetVariable('comments_akismet', 'akismet_apikey'));
		}
	}
}

register_filter('comments', 'antispam', new AntispamFilterComments);
