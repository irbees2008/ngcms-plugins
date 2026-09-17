<?php

/**
 * Telegram Parser via MadelineProto
 *
 * УСТАНОВКА ЗАВИСИМОСТЕЙ:
 * Вариант 1 (рекомендуется):
 *   cd C:\OSPanel\home\it\engine\plugins\content_parser\lib\MadelineProto-8
 *   composer install
 *
 * Вариант 2:
 *   cd C:\OSPanel\home\it
 *   composer install
 */

if (!defined('NGCMS')) die('HAL');

// Инициализируем сессию для хранения состояния авторизации
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем bootstrap для MadelineProto
$bootstrapFile = __DIR__ . '/madelineproto_bootstrap.php';
$madelineProtoLoaded = false;

if (file_exists($bootstrapFile)) {
    $madelineProtoLoaded = include_once($bootstrapFile);
}

// Определяем доступность MadelineProto
if (!defined('MADELINEPROTO_AVAILABLE')) {
    define('MADELINEPROTO_AVAILABLE', $madelineProtoLoaded && class_exists('danog\\MadelineProto\\API'));
}

// Устанавливаем глобальный обработчик ошибок для Revolt EventLoop
// Это решает проблему с несинхронизированным временем Windows/NTP
if (MADELINEPROTO_AVAILABLE && class_exists('Revolt\\EventLoop')) {
    \Revolt\EventLoop::setErrorHandler(function (\Throwable $e) {
        $logFile = __DIR__ . '/telegram_auth_debug.log';

        // Логируем все ошибки EventLoop для отладки
        if (file_exists($logFile)) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] [Global EventLoop Error] " . $e->getMessage() . "\n", FILE_APPEND);
        }

        // Полностью игнорируем ошибки времени NTP
        if (
            strpos($e->getMessage(), 'sync your date using NTP') !== false
            || strpos($e->getMessage(), 'too new compared to') !== false
            || strpos($e->getMessage(), 'too old compared to') !== false
        ) {
            // Ничего не делаем - полностью игнорируем
            return;
        }

        // Все остальные ошибки пробрасываем
        throw $e;
    });
}

use function Plugins\logger;

/**
 * Проверка статуса авторизации MadelineProto
 * @return array ['authorized' => bool, 'phone' => string|null]
 */
function checkTelegramAuth()
{
    if (!MADELINEPROTO_AVAILABLE) {
        return [
            'authorized' => false,
            'error' => 'MadelineProto не загружена',
            'debug' => [
                'constant_defined' => defined('MADELINEPROTO_AVAILABLE'),
                'class_exists' => class_exists('danog\\MadelineProto\\API'),
                'vendor_file' => file_exists(__DIR__ . '/lib/MadelineProto-8/vendor/autoload.php')
            ]
        ];
    }

    $sessionFile = __DIR__ . '/telegram_session.madeline';

    if (!file_exists($sessionFile)) {
        return [
            'authorized' => false,
            'message' => 'Сессия не создана. Нажмите "Авторизоваться в Telegram"',
            'session_file' => $sessionFile
        ];
    }

    try {
        $API = getTelegramAPI();
        $me = $API->getSelf();

        $phone = isset($me['phone']) ? $me['phone'] : 'unknown';
        $username = isset($me['username']) ? $me['username'] : null;
        $firstName = isset($me['first_name']) ? $me['first_name'] : '';

        // Если getSelf() вернул 'unknown' - значит сессия битая
        if ($phone === 'unknown' || empty($me) || !isset($me['id'])) {
            return [
                'authorized' => false,
                'error' => 'Авторизация не завершена или сессия повреждена. Удалите старую сессию и пройдите авторизацию заново.',
                'action_needed' => 'reauth',
                'session_file' => $sessionFile,
                'debug' => ['getSelf_result' => $me]
            ];
        }

        return [
            'authorized' => true,
            'phone' => $phone,
            'username' => $username,
            'first_name' => $firstName,
        ];
    } catch (\Throwable $e) {
        // Проверяем на AUTH_KEY_UNREGISTERED - значит авторизация не завершена
        if (strpos($e->getMessage(), 'AUTH_KEY_UNREGISTERED') !== false) {
            return [
                'authorized' => false,
                'error' => 'Авторизация не завершена. Удалите старую сессию и пройдите авторизацию заново.',
                'action_needed' => 'reauth',
                'session_file' => $sessionFile
            ];
        }

        return [
            'authorized' => false,
            'error' => $e->getMessage(),
            'action_needed' => 'reauth',
            'session_file' => $sessionFile
        ];
    }
}

/**
 * Начало авторизации - отправка номера телефона
 * @param string $phone Номер телефона
 * @return array Результат
 */
function startTelegramAuth($phone)
{
    $logFile = __DIR__ . '/telegram_auth_debug.log';
    file_put_contents($logFile, "  [startTelegramAuth] Начало функции\n", FILE_APPEND);

    if (!MADELINEPROTO_AVAILABLE) {
        file_put_contents($logFile, "  [startTelegramAuth] MadelineProto недоступна\n", FILE_APPEND);
        return ['success' => false, 'error' => 'MadelineProto не загружена'];
    }

    file_put_contents($logFile, "  [startTelegramAuth] MadelineProto доступна\n", FILE_APPEND);

    $sessionFile = __DIR__ . '/telegram_session.madeline';
    file_put_contents($logFile, "  [startTelegramAuth] Session file: " . $sessionFile . "\n", FILE_APPEND);

    $apiId = pluginGetVariable('content_parser', 'tg_api_id');
    $apiHash = pluginGetVariable('content_parser', 'tg_api_hash');
    file_put_contents($logFile, "  [startTelegramAuth] API ID: " . $apiId . ", API Hash: " . substr($apiHash, 0, 10) . "...\n", FILE_APPEND);

    $apiId = $apiId ? (int)$apiId : 0;
    $apiHash = $apiHash ?: '';

    if (empty($apiId) || empty($apiHash)) {
        file_put_contents($logFile, "  [startTelegramAuth] API credentials пусты\n", FILE_APPEND);
        return ['success' => false, 'error' => 'API ID и API Hash не настроены'];
    }

    try {
        file_put_contents($logFile, "  [startTelegramAuth] Создание Settings...\n", FILE_APPEND);
        $settings = new \danog\MadelineProto\Settings();

        // Настройки приложения
        $appInfo = new \danog\MadelineProto\Settings\AppInfo();
        $appInfo->setApiId($apiId);
        $appInfo->setApiHash($apiHash);
        $settings->setAppInfo($appInfo);

        // Настройки соединения - увеличиваем таймауты
        $connection = new \danog\MadelineProto\Settings\Connection();
        $connection->setTimeout(30); // Увеличиваем таймаут до 30 секунд
        $connection->setRetry(true); // Включаем повторные попытки
        $settings->setConnection($connection);

        // Минимальный уровень логирования
        $settings->getLogger()->setLevel(\danog\MadelineProto\Logger::ERROR);

        file_put_contents($logFile, "  [startTelegramAuth] Создание API объекта...\n", FILE_APPEND);

        ob_start();
        $API = new \danog\MadelineProto\API($sessionFile, $settings);

        file_put_contents($logFile, "  [startTelegramAuth] Вызов phoneLogin...\n", FILE_APPEND);
        // Отправляем код на номер
        $sentCode = $API->phoneLogin($phone);

        file_put_contents($logFile, "  [startTelegramAuth] phoneLogin успешен\n", FILE_APPEND);
        file_put_contents($logFile, "  [startTelegramAuth] sentCode: " . json_encode($sentCode) . "\n", FILE_APPEND);

        // КРИТИЧНО: Дожидаемся сохранения состояния сессии
        // В MadelineProto v8 это происходит асинхронно
        file_put_contents($logFile, "  [startTelegramAuth] Ожидание сохранения сессии...\n", FILE_APPEND);

        // Уничтожаем объект API явно, чтобы вызвать деструктор и сохранение
        unset($API);

        // Даем время на запись файла
        usleep(100000); // 100ms

        file_put_contents($logFile, "  [startTelegramAuth] Сессия сохранена\n", FILE_APPEND);

        // Сохраняем phone_code_hash в сессию для следующего шага
        if (session_status() === PHP_SESSION_NONE) {
            file_put_contents($logFile, "  [startTelegramAuth] Запуск сессии...\n", FILE_APPEND);
            session_start();
        }

        $_SESSION['tg_phone'] = $phone;
        $_SESSION['tg_phone_code_hash'] = $sentCode['phone_code_hash'] ?? '';
        $_SESSION['tg_auth_started'] = time(); // Метка времени начала авторизации

        file_put_contents($logFile, "  [startTelegramAuth] phone_code_hash: " . $_SESSION['tg_phone_code_hash'] . "\n", FILE_APPEND);

        ob_end_clean();

        return [
            'success' => true,
            'message' => 'Код отправлен в Telegram',
            'type' => $sentCode['type']['_'] ?? 'unknown'
        ];
    } catch (\Throwable $e) {
        file_put_contents($logFile, "  [startTelegramAuth] EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        file_put_contents($logFile, "  [startTelegramAuth] File: " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
        ob_end_clean();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Завершение авторизации - ввод кода подтверждения
 * @param string $code Код из Telegram
 * @return array Результат
 */
function completeTelegramAuth($code)
{
    $logFile = __DIR__ . '/telegram_auth_debug.log';
    file_put_contents($logFile, "  [completeTelegramAuth] Начало функции\n", FILE_APPEND);

    if (!MADELINEPROTO_AVAILABLE) {
        return ['success' => false, 'error' => 'MadelineProto не загружена'];
    }

    if (empty($_SESSION['tg_phone']) || empty($_SESSION['tg_phone_code_hash'])) {
        return ['success' => false, 'error' => 'Сессия авторизации не найдена. Начните заново.'];
    }

    $sessionFile = __DIR__ . '/telegram_session.madeline';
    file_put_contents($logFile, "  [completeTelegramAuth] Session file: " . $sessionFile . "\n", FILE_APPEND);
    file_put_contents($logFile, "  [completeTelegramAuth] Phone from session: " . $_SESSION['tg_phone'] . "\n", FILE_APPEND);

    try {
        file_put_contents($logFile, "  [completeTelegramAuth] Получение API объекта...\n", FILE_APPEND);
        $API = getTelegramAPI();

        $phone = $_SESSION['tg_phone'];
        $phoneCodeHash = $_SESSION['tg_phone_code_hash'];

        file_put_contents($logFile, "  [completeTelegramAuth] Используем phone: " . $phone . "\n", FILE_APPEND);
        file_put_contents($logFile, "  [completeTelegramAuth] phoneCodeHash: " . substr($phoneCodeHash, 0, 20) . "...\n", FILE_APPEND);

        ob_start();

        // WORKAROUND: MadelineProto не сохраняет промежуточное состояние авторизации между HTTP запросами
        // Решение: Повторно вызываем phoneLogin() с тем же номером в том же запросе
        file_put_contents($logFile, "  [completeTelegramAuth] Повторный вызов phoneLogin для восстановления контекста...\n", FILE_APPEND);

        try {
            // Это не отправит новый код, а восстановит контекст авторизации
            $sentCode = $API->phoneLogin($phone);
            file_put_contents($logFile, "  [completeTelegramAuth] phoneLogin успешен (контекст восстановлен)\n", FILE_APPEND);
        } catch (\Throwable $phoneError) {
            file_put_contents($logFile, "  [completeTelegramAuth] phoneLogin failed: " . $phoneError->getMessage() . "\n", FILE_APPEND);
            // Продолжаем даже если ошибка - может быть авторизация уже начата
        }

        file_put_contents($logFile, "  [completeTelegramAuth] Вызов completePhoneLogin с кодом: " . $code . "\n", FILE_APPEND);

        try {
            $authorization = $API->completePhoneLogin($code);
            file_put_contents($logFile, "  [completeTelegramAuth] completePhoneLogin успешен\n", FILE_APPEND);
            file_put_contents($logFile, "  [completeTelegramAuth] Authorization: " . json_encode($authorization) . "\n", FILE_APPEND);
        } catch (\Throwable $loginError) {
            file_put_contents($logFile, "  [completeTelegramAuth] completePhoneLogin FAILED: " . $loginError->getMessage() . "\n", FILE_APPEND);
            throw $loginError;
        }

        ob_end_clean();

        // Очищаем временные данные
        unset($_SESSION['tg_phone'], $_SESSION['tg_phone_code_hash'], $_SESSION['tg_auth_started']);

        if (isset($authorization['_']) && $authorization['_'] === 'account.password') {
            // Требуется 2FA пароль
            $_SESSION['tg_need_2fa'] = true;
            return [
                'success' => false,
                'need_2fa' => true,
                'message' => 'Требуется пароль 2FA'
            ];
        }

        return [
            'success' => true,
            'message' => 'Авторизация успешна!',
            'user' => [
                'phone' => $authorization['user']['phone'] ?? '',
                'username' => $authorization['user']['username'] ?? null,
                'first_name' => $authorization['user']['first_name'] ?? ''
            ]
        ];
    } catch (\Throwable $e) {
        file_put_contents($logFile, "  [completeTelegramAuth] EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        file_put_contents($logFile, "  [completeTelegramAuth] File: " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
        ob_end_clean();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Завершение авторизации с 2FA паролем
 * @param string $password Пароль 2FA
 * @return array Результат
 */
function complete2FATelegramAuth($password)
{
    if (!MADELINEPROTO_AVAILABLE) {
        return ['success' => false, 'error' => 'MadelineProto не загружена'];
    }

    if (empty($_SESSION['tg_need_2fa'])) {
        return ['success' => false, 'error' => '2FA не требуется'];
    }

    $sessionFile = __DIR__ . '/telegram_session.madeline';

    try {
        $API = getTelegramAPI();

        ob_start();
        $authorization = $API->complete2faLogin($password);
        ob_end_clean();

        // Очищаем временные данные
        unset($_SESSION['tg_need_2fa']);

        return [
            'success' => true,
            'message' => 'Авторизация успешна!',
            'user' => [
                'phone' => $authorization['user']['phone'] ?? '',
                'username' => $authorization['user']['username'] ?? null,
                'first_name' => $authorization['user']['first_name'] ?? ''
            ]
        ];
    } catch (\Throwable $e) {
        ob_end_clean();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Получить экземпляр MadelineProto API
 * @return \danog\MadelineProto\API
 */
function getTelegramAPI()
{
    if (!MADELINEPROTO_AVAILABLE) {
        throw new Exception('MadelineProto не загружена. Установите зависимости: cd lib/MadelineProto-8 && composer install');
    }

    static $API = null;

    // ВАЖНО: Не используем кеш после авторизации, чтобы получить свежий экземпляр
    // if ($API !== null) {
    //     return $API;
    // }

    $sessionFile = __DIR__ . '/telegram_session.madeline';

    // Получаем API credentials из настроек
    $apiId = pluginGetVariable('content_parser', 'tg_api_id');
    $apiHash = pluginGetVariable('content_parser', 'tg_api_hash');

    // Преобразуем к нужным типам
    $apiId = $apiId ? (int)$apiId : 0;
    $apiHash = $apiHash ?: '';

    // Настройки приложения
    $settings = new \danog\MadelineProto\Settings();
    $appInfo = new \danog\MadelineProto\Settings\AppInfo();
    $appInfo->setApiId($apiId);
    $appInfo->setApiHash($apiHash);
    $settings->setAppInfo($appInfo);

    // Настройки соединения - увеличиваем таймауты
    $connection = new \danog\MadelineProto\Settings\Connection();
    $connection->setTimeout(30); // Увеличиваем таймаут до 30 секунд
    $connection->setRetry(true); // Включаем повторные попытки
    $settings->setConnection($connection);

    // Уровень логирования
    $settings->getLogger()->setLevel(\danog\MadelineProto\Logger::ERROR);

    try {
        // Глобальный обработчик ошибок EventLoop уже установлен в начале файла
        // Подавляем вывод предупреждений (включая Windows performance warning)
        ob_start();
        $API = new \danog\MadelineProto\API($sessionFile, $settings);

        // Для публичных каналов авторизация НЕ нужна
        // Для приватных каналов требуется авторизация через CLI:
        // php -f engine/plugins/content_parser/telegram_auth.php
        // (если нужен доступ к приватным каналам, создайте telegram_auth.php скрипт)

        ob_end_clean();

        logger('Telegram API initialized (no auth required for public channels)', 'info', 'content_parser.log');
        return $API;
    } catch (\Throwable $e) {
        ob_end_clean(); // Очищаем буфер в случае ошибки
        logger('Telegram API init failed: ' . $e->getMessage(), 'error', 'content_parser.log');
        throw new Exception('Не удалось инициализировать Telegram API: ' . $e->getMessage());
    }
}

/**
 * Парсинг канала через MadelineProto (с авторизацией)
 * @param string $channelName Имя канала (@channel или channel)
 * @param int $count Количество постов
 * @return array Массив постов
 */
function parseTelegramChannelWithAuth($channelName, $count)
{
    $startTime = microtime(true);

    $channelName = normalizeTelegramChannel($channelName);
    if ($channelName === '') {
        throw new Exception('Некорректное имя канала Telegram');
    }

    $sessionFile = __DIR__ . '/telegram_session.madeline';
    logger('[MadelineProto Parse] Starting parse for @' . $channelName . ', session exists: ' . (file_exists($sessionFile) ? 'yes' : 'NO'), 'info', 'content_parser.log');

    try {
        $API = getTelegramAPI();
        logger('[MadelineProto Parse] API initialized', 'info', 'content_parser.log');

        // Проверяем авторизацию
        try {
            $me = $API->getSelf();
            logger('[MadelineProto Parse] Authorized as: ' . ($me['username'] ?? $me['phone'] ?? 'unknown'), 'info', 'content_parser.log');
        } catch (\Throwable $e) {
            logger('[MadelineProto Parse] Authorization check failed: ' . $e->getMessage(), 'error', 'content_parser.log');
            throw new Exception('Не авторизованы в Telegram. Пройдите авторизацию через кнопку "Авторизоваться в Telegram"');
        }
    } catch (\Throwable $e) {
        throw new Exception('MadelineProto недоступен: ' . $e->getMessage() . '. Убедитесь, что библиотека установлена через composer require danog/madelineproto');
    }

    $items = [];

    try {
        logger('[MadelineProto Parse] Getting channel info for @' . $channelName, 'info', 'content_parser.log');

        // Получаем информацию о канале
        $channelInfo = $API->getInfo('@' . $channelName);

        logger('[MadelineProto Parse] Channel info: type=' . ($channelInfo['type'] ?? 'unknown') . ', id=' . ($channelInfo['bot_api_id'] ?? 'unknown'), 'info', 'content_parser.log');

        if (!isset($channelInfo['type']) || !in_array($channelInfo['type'], ['channel', 'supergroup'])) {
            throw new Exception('Указанный username не является каналом (тип: ' . ($channelInfo['type'] ?? 'unknown') . ')');
        }

        $peer = $channelInfo['bot_api_id'];

        // Запрашиваем больше сообщений чем нужно, т.к. некоторые могут быть служебными (messageService)
        $requestLimit = min($count * 3 + 10, 100);
        logger('[MadelineProto Parse] Fetching messages from channel (requested: ' . $count . ', limit: ' . $requestLimit . ')', 'info', 'content_parser.log');

        // Получаем историю сообщений
        $messages = $API->messages->getHistory([
            'peer' => $peer,
            'offset_id' => 0,
            'offset_date' => 0,
            'add_offset' => 0,
            'limit' => $requestLimit,
            'max_id' => 0,
            'min_id' => 0,
            'hash' => 0,
        ]);

        logger('[MadelineProto Parse] Got ' . (isset($messages['messages']) ? count($messages['messages']) : 0) . ' messages', 'info', 'content_parser.log');

        if (!isset($messages['messages']) || !is_array($messages['messages'])) {
            throw new Exception('Не удалось получить сообщения из канала');
        }

        if (empty($messages['messages'])) {
            throw new Exception('Канал не содержит сообщений или вы не подписаны на него');
        }

        $parsed = 0;
        foreach ($messages['messages'] as $msg) {
            if ($parsed >= $count) {
                break;
            }

            // Пропускаем служебные сообщения
            if (!isset($msg['message']) || $msg['_'] !== 'message') {
                continue;
            }

            $text = $msg['message'] ?? '';

            // Пропускаем пустые сообщения
            if (empty($text) && empty($msg['media'])) {
                continue;
            }

            // Генерируем заголовок
            $title = mb_substr($text, 0, 100);
            if (mb_strlen($text) > 100) {
                $title .= '...';
            }
            if (empty($title)) {
                $title = 'Telegram пост';
            }

            // Извлекаем изображение
            $localImage = '';
            if (isset($msg['media'])) {
                // extractImageFromTelegramMedia() уже загружает файл на сервер и возвращает путь
                $localImage = extractImageFromTelegramMedia($API, $msg['media']);
            }

            // Формируем контент
            $body = '';
            if (!empty($localImage)) {
                $body .= '[img]' . $localImage . '[/img]' . "\n\n";
            }
            $body .= $text;

            // Дата публикации
            $postDate = $msg['date'] ?? time();

            $items[] = [
                'title' => secure_html($title),
                'content' => $body,
                'image' => $localImage,
                'postdate' => $postDate,
            ];

            $parsed++;
        }

        if (empty($items)) {
            throw new Exception('Не удалось извлечь посты из канала');
        }

        $elapsed = (microtime(true) - $startTime) * 1000;
        logger('Telegram parsed (MadelineProto): channel=' . $channelName . ', items=' . $parsed . ', elapsed=' . round($elapsed, 2) . 'ms', 'info', 'content_parser.log');

        return $items;
    } catch (\Throwable $e) {
        logger('Telegram parse error: ' . $e->getMessage(), 'error', 'content_parser.log');
        throw new Exception('Ошибка парсинга Telegram: ' . $e->getMessage());
    }
}

/**
 * Извлечь URL изображения из медиа объекта Telegram
 * @param \danog\MadelineProto\API $API
 * @param array $media
 * @return string|null
 */
function extractImageFromTelegramMedia($API, $media)
{
    if (!isset($media['_'])) {
        return null;
    }

    try {
        switch ($media['_']) {
            case 'messageMediaPhoto':
                // Фото
                if (isset($media['photo'])) {
                    // Скачиваем фото во временную директорию
                    $tempFile = sys_get_temp_dir() . '/tg_' . uniqid() . '.jpg';
                    // ВАЖНО: downloadToFile требует переменную по ссылке
                    $mediaToDownload = $media;
                    $API->downloadToFile($mediaToDownload, $tempFile);

                    // Конвертируем локальный файл в URL через загрузку на сервер
                    $uploadedPath = uploadLocalFileToServer($tempFile, 'image');
                    @unlink($tempFile);

                    return $uploadedPath;
                }
                break;

            case 'messageMediaDocument':
                // Документ (может быть изображением)
                if (isset($media['document']['mime_type']) && strpos($media['document']['mime_type'], 'image/') === 0) {
                    $ext = getExtensionFromMimeType($media['document']['mime_type']);
                    $tempFile = sys_get_temp_dir() . '/tg_' . uniqid() . '.' . $ext;
                    // ВАЖНО: downloadToFile требует переменную по ссылке
                    $mediaToDownload = $media;
                    $API->downloadToFile($mediaToDownload, $tempFile);

                    $uploadedPath = uploadLocalFileToServer($tempFile, 'image');
                    @unlink($tempFile);

                    return $uploadedPath;
                }
                break;
        }
    } catch (\Throwable $e) {
        logger('Failed to extract media: ' . $e->getMessage(), 'error', 'content_parser.log');
    }

    return null;
}

/**
 * Загрузить локальный файл на сервер NGCMS
 * @param string $localPath Путь к локальному файлу
 * @param string $type Тип: 'image' или 'file'
 * @return string|false Путь к загруженному файлу или false
 */
function uploadLocalFileToServer($localPath, $type = 'image')
{
    global $config;

    if (!file_exists($localPath)) {
        return false;
    }

    // Определяем целевую директорию
    $baseDir = ($type == 'image') ? $config['images_dir'] : $config['files_dir'];

    // Нормализуем слэши и убираем trailing slash для baseDir
    $baseDir = str_replace('\\', '/', $baseDir);
    $baseDir = rtrim($baseDir, '/');

    // Генерируем уникальное имя
    $ext = pathinfo($localPath, PATHINFO_EXTENSION);
    $filename = date('Y-m-d') . '_' . uniqid() . '.' . $ext;

    // Формируем полный путь для копирования (БЕЗ двойного слеша)
    $targetPath = $baseDir . '/' . $filename;

    // Копируем файл
    if (copy($localPath, $targetPath)) {
        // Нормализуем путь и извлекаем относительный путь через regex
        $targetNormalized = str_replace('\\', '/', $targetPath);

        // Используем regex для извлечения пути от /uploads или /files
        if (preg_match('#/(uploads|files)/.+$#i', $targetNormalized, $matches)) {
            $relativePath = $matches[0];
        } else {
            // Фолбэк: убираем root вручную
            $rootNormalized = rtrim(str_replace('\\', '/', root), '/');
            $relativePath = str_replace($rootNormalized, '', $targetNormalized);
            // Добавляем ведущий слеш если его нет
            if (substr($relativePath, 0, 1) !== '/') {
                $relativePath = '/' . $relativePath;
            }
        }

        return $relativePath;
    }

    return false;
}

/**
 * Получить расширение файла по MIME-типу
 * @param string $mimeType
 * @return string
 */
function getExtensionFromMimeType($mimeType)
{
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp',
    ];

    return $map[$mimeType] ?? 'jpg';
}

/**
 * Проверить, установлена ли MadelineProto
 * @return bool
 */
function isMadelineProtoInstalled()
{
    return defined('MADELINEPROTO_AVAILABLE') && MADELINEPROTO_AVAILABLE;
}
