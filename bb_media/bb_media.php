<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger};

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
				logger('bb_media', 'WARNING: No player handler found, using fallback');
			}
		}

		logger('bb_media', 'BBmediaNewsfilter initialized: player=' . ($player_name ?: 'videojs'));
	}

	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{

		$processed = false;

		if (($t = bbMediaProcess($tvars['vars']['short-story'])) !== false) {
			$tvars['vars']['short-story'] = $t;
			$processed = true;
		}
		if (($t = bbMediaProcess($tvars['vars']['full-story'])) !== false) {
			$tvars['vars']['full-story'] = $t;
			$processed = true;
		}
		if (($t = bbMediaProcess($tvars['vars']['news']['short'])) !== false) {
			$tvars['vars']['news']['short'] = $t;
			$processed = true;
		}
		if (($t = bbMediaProcess($tvars['vars']['news']['full'])) !== false) {
			$tvars['vars']['news']['full'] = $t;
			$processed = true;
		}

		if ($processed) {
			logger('bb_media', 'News processed: id=' . $newsID . ', title=' . ($SQLnews['title'] ?? 'unknown'));
		}
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
		if (($t = bbMediaProcess($tvars['content'])) !== false) {
			$tvars['content'] = $t;
			logger('bb_media', 'Static page processed: id=' . $staticID . ', title=' . ($SQLstatic['title'] ?? 'unknown'));
		}
	}
}

// Preload plugin tags
register_filter('static', 'bb_media', new BBmediaStaticFilter);
register_filter('news', 'bb_media', new BBmediaNewsFilter);
