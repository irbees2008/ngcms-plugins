<?php

namespace Plugins\AdvancedCaptcha;

// Исключения.
use Exception;
use RuntimeException;
use Throwable;
use Plugins\AdvancedCaptcha\Exceptions\CaptchaException;
use Plugins\AdvancedCaptcha\Exceptions\VerificationFailedException;

// Типы капчи.
use Plugins\AdvancedCaptcha\Types\MathCaptcha;
use Plugins\AdvancedCaptcha\Types\TextCaptcha;
use Plugins\AdvancedCaptcha\Types\QuestionCaptcha;
use Plugins\AdvancedCaptcha\Types\CheckboxCaptcha;
use Plugins\AdvancedCaptcha\Types\SliderCaptcha;

// Сторонние зависимости.
use Plugins\Traits\Renderable;

// Используем функции из пространства `Plugins`.
use function Plugins\dd;
use function Plugins\setting;
use function Plugins\trans;
use function Plugins\{notify, logger, sanitize};

/**
 * Продвинутая защита форм сайта от интернет-ботов.
 */
class AdvancedCaptcha
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
    protected $plugin = 'ng-advanced-captcha';

    /**
     * Доступные типы капчи.
     * @var array
     */
    protected $availableTypes = [];

    /**
     * Текущий тип капчи.
     * @var string
     */
    protected $currentType;

    /**
     * Экземпляр текущей капчи.
     * @var object
     */
    protected $captchaInstance;

    /**
     * Имена всех шаблонов плагина.
     * @var array
     */
    protected $templates = [
        'captcha-widget',
        'captcha-math',
        'captcha-text',
        'captcha-question',
        'captcha-checkbox',
        'captcha-slider',
    ];

    /**
     * Сообщение о причине отказа выполнения действия.
     * @var string
     */
    protected $rejectionReason = 'Captcha verification failed.';

    /**
     * Создать экземпляр плагина.
     */
    public function __construct(array $params = [])
    {
        $this->configure($params);
        $this->initializeAvailableTypes();
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
        // Определить все пути к шаблонам.
        $this->defineTemplatePaths(
            (bool) setting($this->plugin, 'localsource', 0)
        );

        return $this;
    }

    /**
     * Инициализация доступных типов капчи.
     * @return void
     */
    protected function initializeAvailableTypes(): void
    {
        if (setting($this->plugin, 'type_math', true)) {
            $this->availableTypes[] = 'math';
        }
        if (setting($this->plugin, 'type_text', true)) {
            $this->availableTypes[] = 'text';
        }
        if (setting($this->plugin, 'type_question', true)) {
            $this->availableTypes[] = 'question';
        }
        if (setting($this->plugin, 'type_checkbox', true)) {
            $this->availableTypes[] = 'checkbox';
        }
        if (setting($this->plugin, 'type_slider', true)) {
            $this->availableTypes[] = 'slider';
        }

        // Если нет доступных типов, включить хотя бы математику
        if (empty($this->availableTypes)) {
            $this->availableTypes[] = 'math';
        }
    }

    /**
     * Регистрация маршрутов для API капчи.
     * @return void
     */
    public function registerRoutes(): void
    {
        // Регистрируем обработчик для генерации новой капчи
        if (isset($_GET['ng_captcha_generate'])) {
            $this->handleGenerateRequest();
            exit;
        }

        // Регистрируем обработчик для получения изображения
        if (isset($_GET['ng_captcha_image'])) {
            $this->handleImageRequest();
            exit;
        }
    }

    /**
     * Добавление ресурсов в переменную `htmlvars`.
     * @return void
     */
    public function registerAssets(): void
    {
        $pluginUrl = isset($GLOBALS['config']) ? $GLOBALS['config']['plugin_url'] : '/engine/plugins';

        // Добавляем CSS
        register_htmlvar('css', $pluginUrl . '/ng-advanced-captcha/assets/captcha.css');

        // Добавляем JavaScript
        register_htmlvar('js', $pluginUrl . '/ng-advanced-captcha/assets/captcha.js');
    }

    /**
     * Генерация HTML для виджета капчи.
     * @param  string  $formId
     * @return string
     */
    public function generateWidget(string $formId = 'comment'): string
    {
        $this->selectCaptchaType();
        $this->createCaptchaInstance();

        // Генерируем challenge и сохраняем в сессию
        $challenge = $this->captchaInstance->generateChallenge();

        if (!isset($_SESSION)) {
            session_start();
        }

        $sessionKey = 'ng_captcha_' . $formId;
        $_SESSION[$sessionKey] = [
            'type' => $this->currentType,
            'challenge' => $challenge,
            'answer' => $this->captchaInstance->getAnswer(),
            'time' => time(),
            'attempts' => 0,
        ];

        // Honeypot поле
        $honeypot = '';
        if (setting($this->plugin, 'use_honeypot', true)) {
            $honeypot = '<input type="text" name="website" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">';
        }

        return $this->view('captcha-widget', [
            'form_id' => $formId,
            'type' => $this->currentType,
            'challenge' => $challenge,
            'honeypot' => $honeypot,
            'plugin_url' => isset($GLOBALS['config']) ? $GLOBALS['config']['plugin_url'] : '/engine/plugins',
        ]);
    }

    /**
     * Выбор типа капчи.
     * @return void
     */
    protected function selectCaptchaType(): void
    {
        if (setting($this->plugin, 'random_type', true)) {
            $this->currentType = $this->availableTypes[array_rand($this->availableTypes)];
        } else {
            $this->currentType = $this->availableTypes[0];
        }
    }

    /**
     * Создание экземпляра выбранного типа капчи.
     * @return void
     */
    protected function createCaptchaInstance(): void
    {
        switch ($this->currentType) {
            case 'math':
                $this->captchaInstance = new MathCaptcha();
                break;
            case 'text':
                $this->captchaInstance = new TextCaptcha();
                break;
            case 'question':
                $this->captchaInstance = new QuestionCaptcha();
                break;
            case 'checkbox':
                $this->captchaInstance = new CheckboxCaptcha();
                break;
            case 'slider':
                $this->captchaInstance = new SliderCaptcha();
                break;
            default:
                $this->captchaInstance = new MathCaptcha();
        }
    }

    /**
     * Проверка капчи.
     * @param  string  $formId
     * @return bool
     */
    public function verifying(string $formId = 'comment'): bool
    {
        try {
            if (!isset($_SESSION)) {
                session_start();
            }

            $sessionKey = 'ng_captcha_' . $formId;

            // Проверка наличия данных в сессии
            if (!isset($_SESSION[$sessionKey])) {
                throw new VerificationFailedException(
                    trans($this->plugin . ':error.no-session')
                );
            }

            $sessionData = $_SESSION[$sessionKey];

            // Проверка honeypot
            if (setting($this->plugin, 'use_honeypot', true)) {
                if (!empty($_POST['website'])) {
                    logger('ng-advanced-captcha: Honeypot triggered', 'warning');
                    unset($_SESSION[$sessionKey]);
                    throw new VerificationFailedException(
                        trans($this->plugin . ':error.bot-detected')
                    );
                }
            }

            // Проверка минимального времени
            $minTime = (int) setting($this->plugin, 'min_time', 2);
            if ((time() - $sessionData['time']) < $minTime) {
                logger('ng-advanced-captcha: Too fast submission', 'warning');
                throw new VerificationFailedException(
                    trans($this->plugin . ':error.too-fast')
                );
            }

            // Проверка времени жизни сессии
            $timeout = (int) setting($this->plugin, 'session_timeout', 300);
            if ((time() - $sessionData['time']) > $timeout) {
                unset($_SESSION[$sessionKey]);
                throw new VerificationFailedException(
                    trans($this->plugin . ':error.timeout')
                );
            }

            // Проверка количества попыток
            $maxAttempts = (int) setting($this->plugin, 'max_attempts', 3);
            if ($sessionData['attempts'] >= $maxAttempts) {
                unset($_SESSION[$sessionKey]);
                throw new VerificationFailedException(
                    trans($this->plugin . ':error.max-attempts')
                );
            }

            // Увеличиваем счетчик попыток
            $_SESSION[$sessionKey]['attempts']++;

            // Получаем ответ пользователя
            $userAnswer = trim(sanitize($_POST['ng_captcha_answer'] ?? ''));

            if (empty($userAnswer)) {
                throw new VerificationFailedException(
                    trans($this->plugin . ':error.empty-answer')
                );
            }

            // Проверяем ответ
            $correctAnswer = $sessionData['answer'];

            // Для checkbox и slider проверяем токен и временные метки
            if (in_array($sessionData['type'], ['checkbox', 'slider'])) {
                $token = sanitize($_POST['ng_captcha_token'] ?? '');
                $interactions = json_decode(sanitize($_POST['ng_captcha_interactions'] ?? '[]'), true);

                if (!$this->validateToken($token, $sessionData)) {
                    throw new VerificationFailedException(
                        trans($this->plugin . ':error.invalid-token')
                    );
                }

                if (!$this->validateInteractions($interactions)) {
                    throw new VerificationFailedException(
                        trans($this->plugin . ':error.invalid-interaction')
                    );
                }
            }

            // Сравниваем ответы
            $isCorrect = $this->compareAnswers($userAnswer, $correctAnswer, $sessionData['type']);

            if (!$isCorrect) {
                throw new VerificationFailedException(
                    trans($this->plugin . ':error.wrong-answer')
                );
            }

            // Успешная проверка - удаляем данные из сессии
            unset($_SESSION[$sessionKey]);
            logger('ng-advanced-captcha: Verification successful, type: ' . $sessionData['type'], 'info');

            return true;
        } catch (VerificationFailedException $e) {
            $this->rejectionReason = $e->getMessage();
            logger('ng-advanced-captcha: Verification failed - ' . $e->getMessage(), 'warning');
            return false;
        } catch (Throwable $e) {
            logger('ng-advanced-captcha: Unexpected error - ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Сравнение ответов пользователя с правильным.
     * @param  string  $userAnswer
     * @param  string  $correctAnswer
     * @param  string  $type
     * @return bool
     */
    protected function compareAnswers(string $userAnswer, string $correctAnswer, string $type): bool
    {
        // Приводим к нижнему регистру для текстовых ответов
        if (in_array($type, ['text', 'question'])) {
            return mb_strtolower(trim($userAnswer)) === mb_strtolower(trim($correctAnswer));
        }

        // Для числовых ответов
        return trim($userAnswer) === trim($correctAnswer);
    }

    /**
     * Валидация токена для checkbox/slider.
     * @param  string  $token
     * @param  array  $sessionData
     * @return bool
     */
    protected function validateToken(string $token, array $sessionData): bool
    {
        // Простая проверка токена (можно усложнить)
        $expectedToken = md5($sessionData['time'] . $sessionData['answer'] . $_SERVER['REMOTE_ADDR']);
        return $token === $expectedToken;
    }

    /**
     * Валидация взаимодействий пользователя.
     * @param  array  $interactions
     * @return bool
     */
    protected function validateInteractions(array $interactions): bool
    {
        // Проверяем, что есть движения мыши/касания
        if (empty($interactions) || count($interactions) < 2) {
            return false;
        }

        // Проверяем, что взаимодействия распределены во времени
        $timestamps = array_column($interactions, 'time');
        $duration = max($timestamps) - min($timestamps);

        // Минимум 100мс между первым и последним событием
        return $duration >= 100;
    }

    /**
     * Обработка запроса на генерацию новой капчи.
     * @return void
     */
    protected function handleGenerateRequest(): void
    {
        header('Content-Type: application/json');

        try {
            $formId = sanitize($_GET['form_id'] ?? 'comment');
            $html = $this->generateWidget($formId);

            echo json_encode([
                'status' => 'success',
                'html' => $html,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Обработка запроса на получение изображения.
     * @return void
     */
    protected function handleImageRequest(): void
    {
        // Здесь можно генерировать изображение для TextCaptcha
        header('Content-Type: image/png');

        // Простое изображение для примера
        $image = imagecreate(200, 60);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);

        $text = sanitize($_GET['text'] ?? '');
        imagestring($image, 5, 10, 20, $text, $textColor);

        imagepng($image);
        imagedestroy($image);
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
