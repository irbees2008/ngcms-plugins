<?php

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, cache_get, cache_put, get_ip, sanitize};

add_act('core', 'check_pda');

function check_pda()
{
    global $twig;

    // Проверяем, что Twig инициализирован
    if (!isset($twig)) {
        logger('Twig not initialized yet, skipping Mobile Detect extension', 'warning', 'check_pda.log');
        return;
    }

    logger('Initializing Mobile Detect extension', 'info', 'check_pda.log');
    require_once __DIR__ . '/MobileDetect.php';

    // Проверяем, не добавлено ли уже расширение
    try {
        $twig->addExtension(new Twig_Extension_MobileDetect());
        logger('Mobile Detect extension successfully added', 'info', 'check_pda.log');
    } catch (Exception $e) {
        // Расширение уже добавлено, игнорируем ошибку
        logger('Mobile Detect extension already initialized: ' . sanitize($e->getMessage(), 'string'), 'debug', 'check_pda.log');
    }
}
