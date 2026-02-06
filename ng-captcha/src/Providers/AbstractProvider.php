<?php

namespace Plugins\Captcha\Providers;

use Plugins\Captcha\Contracts\CaptchaProviderInterface;
use Plugins\Captcha\Exceptions\MissingVariableException;
use Plugins\Captcha\Exceptions\ValidationException;
use Plugins\Captcha\Exceptions\VerificationException;
use RuntimeException;
use stdClass;

use function Plugins\{logger, sanitize, setting};

/**
 * Абстрактный базовый класс для провайдеров капчи.
 */
abstract class AbstractProvider implements CaptchaProviderInterface
{
    /**
     * Идентификатор плагина.
     * @var string
     */
    protected $plugin = 'ng-captcha';

    /**
     * Ключ сайта.
     * @var string
     */
    protected $siteKey;

    /**
     * Секретный ключ.
     * @var string
     */
    protected $secretKey;

    /**
     * IP адрес пользователя.
     * @var string
     */
    protected $remoteIp;

    /**
     * Токен из формы.
     * @var string|null
     */
    protected $userToken;

    /**
     * Создать экземпляр провайдера.
     */
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Конфигурирование провайдера.
     * @return void
     */
    protected function configure(): void
    {
        try {
            // Загрузить настройки провайдера из конфига
            $siteKey = '';
            $secretKey = '';
            $localsource = false;

            if (function_exists('Plugins\setting')) {
                $siteKey = setting($this->plugin, 'site_key', '');
                $secretKey = setting($this->plugin, 'secret_key', '');
                $localsource = (bool) setting($this->plugin, 'localsource', false);
            }

            if (function_exists('Plugins\sanitize')) {
                $this->siteKey = trim(sanitize($siteKey));
                $this->secretKey = trim(sanitize($secretKey));
            } else {
                $this->siteKey = trim($siteKey);
                $this->secretKey = trim($secretKey);
            }

            // Получить токен из POST
            $fieldName = $this->getTokenFieldName();
            $token = $_POST[$fieldName] ?? '';

            if (function_exists('Plugins\sanitize')) {
                $this->userToken = trim(sanitize($token));
            } else {
                $this->userToken = trim($token);
            }

            // IP пользователя
            $this->remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';

            // Определить пути к шаблонам
            if (method_exists($this, 'defineTemplatePaths')) {
                $this->defineTemplatePaths($localsource);
            }
        } catch (\Exception $e) {
            // Тихо обрабатываем ошибки конфигурации
            $this->siteKey = '';
            $this->secretKey = '';
            $this->userToken = '';
            $this->remoteIp = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * {@inheritdoc}
     */
    public function registerJavaScript(): void
    {
        $useApiJs = true;

        if (function_exists('Plugins\setting')) {
            $useApiJs = setting($this->plugin, 'use_api_js', true);
        }

        if ($this->siteKey && $useApiJs && function_exists('register_htmlvar')) {
            register_htmlvar('js', $this->getApiScript());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(?string $token = null, string $remoteIp = ''): bool
    {
        $token = $token ?? $this->userToken;
        $remoteIp = $remoteIp ?: $this->remoteIp;

        if (empty($token)) {
            logger($this->getName() . ': Empty captcha token', 'warning', 'captcha.log');
            $errorMsg = function_exists('\Plugins\trans') ?
                \Plugins\trans('ng-captcha:error_empty_token') :
                'Empty captcha token';
            throw new ValidationException($errorMsg);
        }

        if (empty($this->secretKey)) {
            logger($this->getName() . ': Secret key not configured', 'error', 'captcha.log');
            $errorMsg = function_exists('\Plugins\trans') ?
                \Plugins\trans('ng-captcha:error_no_secret_key') :
                'Secret key not configured';
            throw new MissingVariableException($errorMsg);
        }

        $result = $this->verify($token, $remoteIp);

        if (!$result['success']) {
            $errors = implode(', ', $result['errors'] ?? ['Unknown error']);
            logger($this->getName() . ': Verification failed - ' . $errors, 'warning', 'captcha.log');
            throw new ValidationException($errors);
        }

        logger($this->getName() . ': Verification successful', 'info', 'captcha.log');
        return true;
    }

    /**
     * Выполнить HTTP запрос к API верификации.
     * @param  array  $params
     * @return stdClass
     * @throws RuntimeException
     */
    protected function makeRequest(array $params): stdClass
    {
        $query = http_build_query($params);
        $apiUrl = $this->getApiVerify();

        // Используем cURL если доступен
        if (extension_loaded('curl') && function_exists('curl_init')) {
            $response = $this->curlRequest($apiUrl, $query);
        } elseif (ini_get('allow_url_fopen')) {
            $response = $this->fopenRequest($apiUrl, $query);
        } else {
            logger($this->getName() . ': Neither cURL nor allow_url_fopen available', 'error', 'captcha.log');
            throw new RuntimeException('cURL or allow_url_fopen required');
        }

        $decoded = json_decode($response);

        if (JSON_ERROR_NONE !== json_last_error()) {
            logger($this->getName() . ': JSON decode error - ' . json_last_error_msg(), 'error', 'captcha.log');
            throw new RuntimeException('Invalid JSON response');
        }

        return $decoded;
    }

    /**
     * HTTP запрос через cURL.
     * @param  string  $url
     * @param  string  $query
     * @return string
     */
    protected function curlRequest(string $url, string $query): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            logger($this->getName() . ': cURL error - ' . $error, 'error', 'captcha.log');
            throw new RuntimeException('cURL error: ' . $error);
        }

        return $response;
    }

    /**
     * HTTP запрос через file_get_contents.
     * @param  string  $url
     * @param  string  $query
     * @return string
     */
    protected function fopenRequest(string $url, string $query): string
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $query,
                'timeout' => 10,
            ],
            'ssl' => [
                'verify_peer' => false,
            ],
        ];

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            logger($this->getName() . ': fopen error - ' . ($error['message'] ?? 'Unknown'), 'error', 'captcha.log');
            throw new RuntimeException('fopen error');
        }

        return $response;
    }
}
