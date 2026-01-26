<?php

namespace Plugins\Turnstile;

// Исключения.
use Exception;
use RuntimeException;
use Throwable;
use Plugins\Turnstile\Exceptions\MissingVariableException;
use Plugins\Turnstile\Exceptions\VerificationFailedException;

// Базовые расширения PHP.
use stdClass;

// Сторонние зависимости.
use Plugins\Turnstile\Filters\TurnstileCoreFilter;
use Plugins\Turnstile\Filters\TurnstileCommentsFilter;
use Plugins\Turnstile\Filters\TurnstileFeedbackFilter;
use Plugins\Traits\Renderable;

// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\trans;
use function Plugins\{notify, logger, sanitize};

/**
 * Защита форм сайта от интернет-ботов с Cloudflare Turnstile.
 * Невидимая для пользователей альтернатива традиционным CAPTCHA.
 */
class Turnstile
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
    protected $plugin = 'ng-turnstile';

    /**
     * URL-адрес JavaScript API Turnstile.
     * @var string
     */
    protected $apiScript = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    /**
     * URL-адрес сервиса `проверки` токена пользователя.
     * @var string
     */
    protected $apiVerify = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Ключ сайта (Site Key), используемый для отображения виджета.
     * @var string
     */
    protected $siteKey;

    /**
     * Секретный ключ (Secret Key), используемый для `проверки` токена пользователя.
     * @var string
     */
    protected $secretKey;

    /**
     * Значение поля токена из формы.
     * @var string|null
     */
    protected $userToken;

    /**
     * Имена всех шаблонов плагина.
     * @var array
     */
    protected $templates = [
        'turnstile-widget',
    ];

    /**
     * Сообщение о причине отказа выполнения действия.
     * @var string
     */
    protected $rejectionReason = 'Cloudflare Turnstile verification failed.';

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
        // Сначала зadadим настройки из плагина.
        $this->siteKey = trim(sanitize(setting($this->plugin, 'site_key', null)));
        $this->secretKey = trim(sanitize(setting($this->plugin, 'secret_key', null)));

        // Теперь зададим переданные через форму.
        $this->userToken = trim(sanitize($_POST['cf-turnstile-response'] ?? ''));

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
        if ($this->siteKey) {
            register_htmlvar('js', $this->apiScript . '?render=explicit');
        }
    }

    /**
     * Добавление виджета (для шаблонов).
     * @return void
     */
    public function registerWidget(): void
    {
        // Виджет будет добавляться динамически через фильтры
    }

    /**
     * Генерация HTML виджета.
     * @param  string  $formId
     * @return string
     */
    public function generateWidget(string $formId = 'comment'): string
    {
        if (!$this->siteKey) {
            return '';
        }

        $theme = setting($this->plugin, 'theme', 'auto');
        $size = setting($this->plugin, 'size', 'normal');
        $appearance = setting($this->plugin, 'appearance', 'always');

        return $this->view('turnstile-widget', [
            'site_key' => $this->siteKey,
            'theme' => $theme,
            'size' => $size,
            'appearance' => $appearance,
            'form_id' => $formId,
        ]);
    }

    /**
     * Проверка токена Turnstile.
     * @return bool
     */
    public function verifying(): bool
    {
        try {
            $this->ensureNecessaryVariables();

            $verified = $this->touchAnswer();

            if (!$verified->success) {
                $errorCodes = $verified->{'error-codes'} ?? ['unknown-error'];
                throw new VerificationFailedException(
                    $this->getErrorMessage(array_shift($errorCodes))
                );
            }

            logger('ng-turnstile: Verification successful', 'info');
            return true;
        } catch (MissingVariableException $e) {
            $this->rejectionReason = $e->getMessage();
            logger('ng-turnstile: Missing variable - ' . $e->getMessage(), 'warning');

            return false;
        } catch (VerificationFailedException $e) {
            $this->rejectionReason = $e->getMessage();
            logger('ng-turnstile: Verification failed - ' . $e->getMessage(), 'warning');

            return false;
        } catch (Throwable $e) {
            logger('ng-turnstile: Unexpected error - ' . $e->getMessage(), 'error');

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

        if (empty($this->secretKey)) {
            throw new MissingVariableException(
                trans($this->plugin . ':error.empty-secret-key')
            );
        }

        if (empty($this->userToken)) {
            throw new MissingVariableException(
                trans($this->plugin . ':error.missing-input-response')
            );
        }
    }

    /**
     * Отправка запроса на верификацию к серверу Cloudflare.
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
            logger('ng-turnstile: Neither cURL nor allow_url_fopen available', 'error');
            throw new RuntimeException('Not supported: cURL, allow_fopen_url.');
        }

        $answer = json_decode($answer);

        if (JSON_ERROR_NONE !== json_last_error()) {
            logger('ng-turnstile: JSON decode error - ' . json_last_error_msg(), 'error');
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
            'secret' => $this->secretKey,
            'response' => $this->userToken,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
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
            logger('ng-turnstile: cURL error - ' . $error, 'error');
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
            logger('ng-turnstile: file_get_contents failed', 'error');
            throw new RuntimeException('Failed to get response from server.');
        }

        return $answer;
    }

    /**
     * Получить сообщение об ошибке по коду.
     * @param  string  $errorCode
     * @return string
     */
    protected function getErrorMessage(string $errorCode): string
    {
        $messages = [
            'missing-input-secret' => trans($this->plugin . ':error.missing-input-secret'),
            'invalid-input-secret' => trans($this->plugin . ':error.invalid-input-secret'),
            'missing-input-response' => trans($this->plugin . ':error.missing-input-response'),
            'invalid-input-response' => trans($this->plugin . ':error.invalid-input-response'),
            'bad-request' => trans($this->plugin . ':error.bad-request'),
            'timeout-or-duplicate' => trans($this->plugin . ':error.timeout-or-duplicate'),
            'internal-error' => trans($this->plugin . ':error.internal-error'),
        ];

        return $messages[$errorCode] ?? trans($this->plugin . ':error.unknown');
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
