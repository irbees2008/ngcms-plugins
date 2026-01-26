<?php

namespace Plugins\YandexCaptcha;

// Исключения.
use Exception;
use RuntimeException;
use Throwable;
use Plugins\YandexCaptcha\Exceptions\MissingVariableException;
use Plugins\YandexCaptcha\Exceptions\VerificationFailedException;

// Базовые расширения PHP.
use stdClass;

// Сторонние зависимости.
use Plugins\YandexCaptcha\Filters\YandexCaptchaCoreFilter;
use Plugins\YandexCaptcha\Filters\YandexCaptchaCommentsFilter;
use Plugins\YandexCaptcha\Filters\YandexCaptchaFeedbackFilter;
use Plugins\Traits\Renderable;

// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\trans;
use function Plugins\{notify, logger, sanitize};

/**
 * Защита форм сайта от интернет-ботов с Яндекс SmartCaptcha.
 */
class YandexCaptcha
{
    use Renderable;

    /**
     * Номер версии плагина.
     * @const string
     */
    const VERSION = '1.0.0';

    /**
     * Идентификатор плагина.
     * @var string
     */
    protected $plugin = 'ng-yandex-captcha';

    /**
     * URL-адрес сервиса `создания` виджета капчи.
     * @var string
     */
    protected $apiRender = 'https://smartcaptcha.yandexcloud.net/captcha.js';

    /**
     * URL-адрес сервиса `проверки` токена пользователя.
     * @var string
     */
    protected $apiVerify = 'https://smartcaptcha.yandexcloud.net/validate';

    /**
     * Ключ сайта (клиентский ключ), используемый для отображения виджета.
     * @var string
     */
    protected $siteKey;

    /**
     * Серверный ключ, используемый для `проверки` токена пользователя.
     * @var string
     */
    protected $serverKey;

    /**
     * Значение поля капчи из формы, переданное в методе `validate`.
     * @var string|null
     */
    protected $userToken;

    /**
     * Имена всех шаблонов плагина.
     * @var array
     */
    protected $templates = [
        'yandex-input',
        'yandex-script',
    ];

    /**
     * Маркер того, что уже был прикреплен JavaScript-перехватчик форм.
     * @var bool
     */
    protected $attachedJavascript;

    /**
     * Сообщение о причине отказа выполнения действия.
     * @var array
     */
    protected $rejectionReason = 'Yandex SmartCaptcha protected.';

    /**
     * Создать экземпляр плагина.
     */
    public function __construct(array $params = [])
    {
        $this->configure($params);
    }

    /**
     * Получить номер версии плагина.
     * @return string
     */
    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * Конфигурирование параметров плагина.
     * @param  array  $params
     * @return $this
     */
    public function configure(array $params = []): self
    {
        // Сначала зададим настройки из плагина.
        $this->siteKey = trim(sanitize(setting($this->plugin, 'site_key', null)));
        $this->serverKey = trim(sanitize(setting($this->plugin, 'server_key', null)));

        // Теперь зададим переданные через форму.
        $this->userToken = trim(sanitize($_POST['smart-token'] ?? ''));

        // Определить все пути к шаблонам.
        $this->defineTemplatePaths(
            (bool) setting($this->plugin, 'localsource', 0)
        );

        return $this;
    }

    /**
     * Добавление JavaScript API в переменную `htmlvars`.
     * @return void
     */
    public function registerAPIJavaScript(): void
    {
        if (
            $this->siteKey
            && setting($this->plugin, 'use_api_js', true)
        ) {
            register_htmlvar('js', $this->apiRender);
        }
    }

    /**
     * Добавление JavaScript из шаблона в переменную `htmlvars`.
     * @param  string  $action  Действие, выполняемое пользователем.
     * @return void
     */
    public function registerAttachJavaScript(string $action = 'send_form'): void
    {
        // Если включено формирование переменной `htmlvars`.
        if (
            $this->siteKey
            && setting($this->plugin, 'use_attach_js', true)
            && ! $this->attachedJavascript
        ) {
            register_htmlvar('plain', $this->view('yandex-script', [
                'site_key' => $this->siteKey,
                'action' => $action,
            ]));

            $this->attachedJavascript = true;
        }
    }

    /**
     * Проверка капчи.
     * @return bool
     */
    public function verifying()
    {
        try {
            $this->ensureNecessaryVariables();

            $verified = $this->touchAnswer();

            if (! $verified->status || $verified->status !== 'ok') {
                throw new VerificationFailedException(
                    $verified->message ?? 'Verification failed'
                );
            }

            logger('ng-yandex-captcha: Verification successful', 'info');
            return true;
        } catch (MissingVariableException $e) {
            $this->rejectionReason = $e->getMessage();
            logger('ng-yandex-captcha: Missing variable - ' . $e->getMessage(), 'warning');

            return false;
        } catch (VerificationFailedException $e) {
            $this->rejectionReason = $e->getMessage();
            logger('ng-yandex-captcha: Verification failed - ' . $e->getMessage(), 'warning');

            return false;
        } catch (Throwable $e) {
            logger('ng-yandex-captcha: Unexpected error - ' . $e->getMessage(), 'error');

            throw $e;
        }
    }

    /**
     * Проверка необходимых переменных.
     * @throws MissingVariableException
     */
    protected function ensureNecessaryVariables(): void
    {
        if (empty($this->siteKey)) {
            throw new MissingVariableException(
                trans($this->plugin . ':error.empty-site-key')
            );
        }

        if (empty($this->serverKey)) {
            throw new MissingVariableException(
                trans($this->plugin . ':error.empty-server-key')
            );
        }

        if (empty($this->userToken)) {
            throw new MissingVariableException(
                trans($this->plugin . ':error.missing-input-response')
            );
        }
    }

    /**
     * Отправка запроса на верификацию к серверу Яндекс.
     * @return stdClass
     */
    protected function touchAnswer(): stdClass
    {
        $data = $this->prepareData();

        if (extension_loaded('curl') and function_exists('curl_init')) {
            $answer = $this->getCurlAnswer($data);
        } elseif (ini_get('allow_url_fopen')) {
            $answer = $this->getFopenAnswer($data);
        } else {
            logger('ng-yandex-captcha: Neither cURL nor allow_url_fopen available', 'error');
            throw new RuntimeException('Not supported: cURL, allow_fopen_url.');
        }

        $answer = json_decode($answer);

        if (JSON_ERROR_NONE !== json_last_error()) {
            logger('ng-yandex-captcha: JSON decode error - ' . json_last_error_msg(), 'error');
            throw new RuntimeException('JSON answer error.');
        }

        return $answer;
    }

    /**
     * Подготовка данных для отправки.
     * @return array
     */
    protected function prepareData(): array
    {
        return [
            'secret' => $this->serverKey,
            'token' => $this->userToken,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
    }

    /**
     * Получение ответа через cURL.
     * @param  array  $data
     * @return string
     */
    protected function getCurlAnswer(array $data): string
    {
        $ch = curl_init($this->apiVerify);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $answer = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            logger('ng-yandex-captcha: cURL error - ' . $error, 'error');
            throw new RuntimeException('cURL error: ' . $error);
        }

        curl_close($ch);

        return $answer;
    }

    /**
     * Получение ответа через file_get_contents.
     * @param  array  $data
     * @return string
     */
    protected function getFopenAnswer(array $data): string
    {
        $context = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 10,
            ]
        ]);

        $answer = file_get_contents($this->apiVerify, false, $context);

        if ($answer === false) {
            logger('ng-yandex-captcha: file_get_contents failed', 'error');
            throw new RuntimeException('Failed to get response from server.');
        }

        return $answer;
    }

    /**
     * Получить причину отказа.
     * @return string
     */
    public function rejectionReason(): string
    {
        return $this->rejectionReason;
    }
}
