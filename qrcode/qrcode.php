<?php
if (!defined('NGCMS')) die('HAL');

class QRcodeNewsFilter extends NewsFilter
{
	function showNews($newsID, $SQLnews, &$tvars, $mode = array())
	{
		global $config, $twig, $tpl;

		require_once __DIR__ . '/phpqrcode/qrlib.php';

		$cacheFileName = md5('qrcode' . $newsID . $config['theme'] . $config['default_lang']) . '.txt';
		$cacheExpire = pluginGetVariable('qrcode', 'cacheExpire') ?: 3600;

		if ($cacheExpire > 0) {
			$cacheData = cacheRetrieveFile($cacheFileName, $cacheExpire, 'qrcode');
			if ($cacheData !== false) {
				$tvars['vars']['plugin_qrcode'] = $cacheData;
				return 1;
			}
		}

		$url = newsGenerateLink($SQLnews, false, 0, true);
		$size = min((int)pluginGetVariable('qrcode', 'chs'), 500) ?: 150;
		$margin = (int)pluginGetVariable('qrcode', 'margin') ?: 4;
		$errorCorrection = pluginGetVariable('qrcode', 'chld') ?: 'L';

		// Генерация QR-кода
		if (pluginGetVariable('qrcode', 'upload')) {
			$qrData = $this->generateAndUploadQR($newsID, $url, $size, $margin, $errorCorrection);
		} else {
			$qrData = $this->generateQR($url, $size, $margin, $errorCorrection);
		}

		$tVars = [
			'qrcode' => $qrData,
			'title' => $SQLnews['title'],
			'size' => $size
		];

		if (class_exists('Twig\Environment')) {
			try {
				$tpath = locatePluginTemplates(['qrcode'], 'qrcode', pluginGetVariable('qrcode', 'localsource'));
				$template = $twig->load($tpath['qrcode'] . 'qrcode.tpl');
				$output = $template->render($tVars);
			} catch (Exception $e) {
				$output = $this->renderLegacyTemplate($tVars);
			}
		} else {
			$output = $this->renderLegacyTemplate($tVars);
		}

		$tvars['vars']['plugin_qrcode'] = $output;
		if ($cacheExpire > 0) {
			cacheStoreFile($cacheFileName, $output, 'qrcode');
		}
		return 1;
	}

	private function generateQR($text, $size, $margin, $errorCorrection)
	{
		ob_start();
		$errorLevel = constant('QR_ECLEVEL_' . $errorCorrection);
		QRcode::png($text, null, $errorLevel, $size / 25, $margin);
		return 'data:image/png;base64,' . base64_encode(ob_get_clean());
	}

	private function generateAndUploadQR($newsID, $text, $size, $margin, $errorCorrection)
	{
		global $mysql, $fmanager;

		@include_once root . 'includes/classes/upload.class.php';
		@include_once root . 'includes/inc/file_managment.php';
		@include_once root . 'includes/classes/image_managment.php';

		$fmanager = new file_managment();
		$imanager = new image_managment();

		// Генерация временного файла
		$tempFile = tempnam(sys_get_temp_dir(), 'qrcode_');
		$errorLevel = constant('QR_ECLEVEL_' . $errorCorrection);
		QRcode::png($text, $tempFile, $errorLevel, $size / 25, $margin);

		// Загрузка на сервер
		$fmanager->get_limits('image');
		$dir = $fmanager->dname;

		if (!is_dir($dir . '/qrcode')) {
			$fmanager->category_create('image', 'qrcode');
		}

		$fparam = array(
			'type' => 'image',
			'category' => 'qrcode',
			'manual' => 1,
			'file' => $tempFile,
			'name' => 'qrcode_' . $newsID . '.png',
			'replace' => 1
		);

		$up = $fmanager->file_upload($fparam);
		unlink($tempFile);

		if (is_array($sz = $imanager->get_size($dir . '/qrcode/' . $up[1]))) {
			$mysql->query("UPDATE " . prefix . "_" . $fmanager->tname . " SET 
                width=" . db_squote($sz[1]) . ", 
                height=" . db_squote($sz[2]) . ",
                description=" . $newsID . " 
                WHERE id=" . db_squote($up[0]));
		}

		return $fmanager->uname . '/qrcode/' . $up[1];
	}

	private function renderLegacyTemplate($vars)
	{
		global $tpl;

		$tpath = locatePluginTemplates(['qrcode'], 'qrcode', pluginGetVariable('qrcode', 'localsource'));
		$tpl->template('qrcode', $tpath['qrcode']);
		$tpl->vars('qrcode', ['vars' => $vars]);
		return $tpl->show('qrcode');
	}
}

register_filter('news', 'qrcode', new QRcodeNewsFilter);

if (function_exists('twigRegisterFunction')) {
	function plugin_qrcode_twig($params)
	{
		global $mysql;

		if (empty($params['news_id'])) return '';

		$news = $mysql->record("SELECT * FROM " . prefix . "_news WHERE id=" . db_squote($params['news_id']));
		if (!$news) return '';

		$filter = new QRcodeNewsFilter();
		$tvars = [];
		$filter->showNews($params['news_id'], $news, $tvars);

		return $tvars['vars']['plugin_qrcode'] ?? '';
	}

	twigRegisterFunction('qrcode', 'show', 'plugin_qrcode_twig');
}
