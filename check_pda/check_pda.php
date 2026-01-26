<?php

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, cache_get, cache_put, get_ip};

add_act('core', 'check_pda');

function check_pda()
{

	global $twig;

	logger('check_pda', 'Initializing Mobile Detect extension');
	require_once 'MobileDetect.php';

	// Проверяем, не добавлено ли уже расширение
	try {
		$twig->addExtension(new Twig_Extension_MobileDetect());
	} catch (Exception $e) {
		// Расширение уже добавлено, игнорируем ошибку
		logger('check_pda', 'Mobile Detect extension already initialized');
	}
}
