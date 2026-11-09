<?php

namespace Plugins\Captcha;

use Plugins\Captcha\Contracts\CaptchaProviderInterface;
use Plugins\Captcha\Providers\GoogleRecaptchaProvider;
use Plugins\Captcha\Providers\TurnstileProvider;
use Plugins\Captcha\Providers\YandexCaptchaProvider;
use RuntimeException;

use function Plugins\{setting, logger};

/**
 * Универсальный класс капчи с поддержкой разных провайдеров.
 */
class Captcha
{
    /**
     * Идентификатор плагина.
     * @var string
     */
    protected $plugin = 'ng-captcha';

    /**
     * Текущий провайдер капчи.
     * @var CaptchaProviderInterface|null
     */
    protected $provider;

    /**
     * Создать экземпляр капчи.
     */
    public function __construct()
    {
        $this->provider = $this->createProvider();
    }

    /**
     * Создать провайдера на основе настроек.
     * @return CaptchaProviderInterface|null
     * @throws RuntimeException
     */
    protected function createProvider(): ?CaptchaProviderInterface
    {
        try {
            $providerName = 'google'; // default

            if (function_exists('Plugins\setting')) {
                $providerName = setting($this->plugin, 'provider', 'google');
            }

            switch ($providerName) {
                case 'google':
                    return new GoogleRecaptchaProvider();

                case 'turnstile':
                    return new TurnstileProvider();

                case 'yandex':
                    return new YandexCaptchaProvider();

                default:
                    if (function_exists('Plugins\logger')) {
                        logger($this->plugin . ': Unknown provider - ' . $providerName, 'error', 'captcha.log');
                    }
                    throw new RuntimeException('Unknown captcha provider: ' . $providerName);
            }
        } catch (\Exception $e) {
            if (function_exists('Plugins\logger')) {
                logger($this->plugin . ': Provider creation error - ' . $e->getMessage(), 'error', 'captcha.log');
            }
            // Возвращаем Google по умолчанию при ошибке
            return new GoogleRecaptchaProvider();
        }
    }

    /**
     * Получить текущего провайдера.
     * @return CaptchaProviderInterface|null
     */
    public function getProvider(): ?CaptchaProviderInterface
    {
        return $this->provider;
    }

    /**
     * Зарегистрировать JavaScript API.
     * @return void
     */
    public function registerJavaScript(): void
    {
        if ($this->provider) {
            $this->provider->registerJavaScript();
        }
    }

    /**
     * Зарегистрировать дополнительный JavaScript (для Google и Yandex).
     * @return void
     */
    public function registerAttachJavaScript(): void
    {
        if ($this->provider && method_exists($this->provider, 'registerAttachJavaScript')) {
            $this->provider->registerAttachJavaScript();
        }
    }

    /**
     * Получить HTML виджета.
     * @return string
     */
    public function renderWidget(): string
    {
        if ($this->provider) {
            return $this->provider->renderWidget();
        }

        return '';
    }

    /**
     * Валидировать капчу.
     * @param  string|null  $token
     * @param  string  $remoteIp
     * @return bool
     * @throws \Plugins\Captcha\Exceptions\ValidationException
     */
    public function validate(?string $token = null, string $remoteIp = ''): bool
    {
        if (!$this->provider) {
            throw new RuntimeException('Captcha provider not initialized');
        }

        return $this->provider->validate($token, $remoteIp);
    }

    /**
     * Проверить капчу (без исключений).
     * @param  string|null  $token
     * @param  string  $remoteIp
     * @return bool
     */
    public function verify(?string $token = null, string $remoteIp = ''): bool
    {
        try {
            return $this->validate($token, $remoteIp);
        } catch (\Exception $e) {
            logger($this->plugin . ': Verification failed - ' . $e->getMessage(), 'warning', 'captcha.log');
            return false;
        }
    }
}
