<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, array_get, sanitize, cache_get, cache_put};

class BBmediaNewsfilter extends NewsFilter
{

	public function __construct()
	{
		// Получаем имя плеера из настроек или используем значение по умолчанию
		$player_name = pluginGetVariable('bb_media', 'player_name');

		// Если плеер не выбран в настройках, используем videojs по умолчанию
		if (empty($player_name)) {
			$player_name = 'videojs'; // или 'html5', если хотите использовать HTML5 по умолчанию
		}

		$player_handler = __DIR__ . '/players/' . $player_name . '/bb_media.php';

		if (file_exists($player_handler)) {
			include_once($player_handler);
		} else {
			// Дополнительная проверка - если файла нет, попробуем HTML5 плеер
			$fallback_handler = __DIR__ . '/players/HTML5player/bb_media.php';
			if (file_exists($fallback_handler)) {
				include_once($fallback_handler);
			} else {
				// Минимальная реализация, чтобы плагин не ломал сайт
				if (!function_exists('bbMediaProcess')) {
					function bbMediaProcess($content)
					{
						return $content;
					}
				}
				logger('WARNING: No player handler found, using fallback', 'warning', 'bb_media.log');
			}
		}

		logger('BBmediaNewsfilter initialized: player=' . ($player_name ?: 'videojs'), 'info', 'bb_media.log');
	}

	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{

		$processed = false;

		// Используем array_get для безопасного доступа к вложенным массивам
		$vars = array_get($tvars, 'vars', []);
		$shortStory = array_get($vars, 'short-story', '');
		if (($t = bbMediaProcess($shortStory)) !== false) {
			$tvars['vars']['short-story'] = $t;
			$processed = true;
		}

		$fullStory = array_get($vars, 'full-story', '');
		if (($t = bbMediaProcess($fullStory)) !== false) {
			$tvars['vars']['full-story'] = $t;
			$processed = true;
		}

		$news = array_get($vars, 'news', []);
		$newsShort = array_get($news, 'short', '');
		if (($t = bbMediaProcess($newsShort)) !== false) {
			$tvars['vars']['news']['short'] = $t;
			$processed = true;
		}

		$newsFull = array_get($news, 'full', '');
	}
}

class BBmediaStaticFilter extends StaticFilter
{

	public function __construct()
	{

		$player_name = pluginGetVariable('bb_media', 'player_name');
		$player_handler = __DIR__ . '/players/' . $player_name . '/bb_media.php';
		if (file_exists($player_handler)) {
			include_once($player_handler);
		}
	}

	public function showStatic($staticID, $SQLstatic, &$tvars, $mode)
	{
		$content = array_get($tvars, 'content', '');
		if (($t = bbMediaProcess($content)) !== false) {
			$tvars['content'] = $t;
			$title = sanitize(array_get($SQLstatic, 'title', 'unknown'), 'string');
			logger('Static page processed: id=' . $staticID . ', title=' . $title, 'info', 'bb_media.log');
		}
	}
}

// Preload plugin tags
register_filter('static', 'bb_media', new BBmediaStaticFilter);
register_filter('news', 'bb_media', new BBmediaNewsFilter);
