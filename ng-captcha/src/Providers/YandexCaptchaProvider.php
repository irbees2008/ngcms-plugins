<?php

namespace Plugins\Captcha\Providers;

use function Plugins\{setting, trans};

/**
 * Провайдер Яндекс SmartCaptcha.
 */
class YandexCaptchaProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'yandex';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiScript(): string
    {
        return 'https://smartcaptcha.yandexcloud.net/captcha.js';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiVerify(): string
    {
        return 'https://smartcaptcha.yandexcloud.net/validate';
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenFieldName(): string
    {
        return 'smart-token';
    }

    /**
     * {@inheritdoc}
     */
    public function renderWidget(): string
    {
        return $this->view('yandex-widget', [
            'site_key' => $this->siteKey,
            'random_id' => bin2hex(random_bytes(8)),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $token, string $remoteIp = ''): array
    {
        try {
            $params = [
                'secret' => $this->secretKey,
                'token' => $token,  // Яндекс использует 'token', а не 'response'
            ];

            if ($remoteIp) {
                $params['ip'] = $remoteIp;  // Яндекс использует 'ip', а не 'remoteip'
            }

            $result = $this->makeRequest($params);

            // Яндекс возвращает 'status' вместо 'success'
            $success = isset($result->status) && $result->status === 'ok';

            if (!$success) {
                return [
                    'success' => false,
                    'score' => null,
                    'errors' => [$result->message ?? 'Verification failed'],
                ];
            }

            return [
                'success' => true,
                'score' => null,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'score' => null,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Регистрация дополнительного JavaScript.
     * @return void
     */
    public function registerAttachJavaScript(): void
    {
        $useAttachJs = true;

        if (function_exists('Plugins\setting')) {
            $useAttachJs = setting($this->plugin, 'use_attach_js', true);
        }

        if ($this->siteKey && $useAttachJs && function_exists('register_htmlvar')) {
            register_htmlvar('plain', $this->view('yandex-script', [
                'site_key' => $this->siteKey,
            ]));
        }
    }
}
