<?php

namespace Plugins\Captcha\Providers;

use function Plugins\{setting, trans};

/**
 * Провайдер Google reCAPTCHA v3.
 */
class GoogleRecaptchaProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiScript(): string
    {
        return 'https://www.google.com/recaptcha/api.js?render=' . $this->siteKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiVerify(): string
    {
        return 'https://www.google.com/recaptcha/api/siteverify';
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenFieldName(): string
    {
        return 'g-recaptcha-response';
    }

    /**
     * {@inheritdoc}
     */
    public function renderWidget(): string
    {
        return $this->view('google-widget', [
            'site_key' => $this->siteKey,
            'action' => 'submit',
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
                'response' => $token,
            ];

            if ($remoteIp) {
                $params['remoteip'] = $remoteIp;
            }

            $result = $this->makeRequest($params);

            if (!$result->success) {
                return [
                    'success' => false,
                    'score' => null,
                    'errors' => $result->{'error-codes'} ?? ['Unknown error'],
                ];
            }

            // Проверить минимальный score (для v3)
            $minScore = 0.5;

            if (function_exists('Plugins\setting')) {
                $minScore = (float) setting($this->plugin, 'google_min_score', 0.5);
            }

            $score = $result->score ?? 0.0;

            if ($score < $minScore) {
                $errorMsg = function_exists('\Plugins\trans') ?
                    \Plugins\trans('ng-captcha:error_low_score') :
                    'Low security score';
                return [
                    'success' => false,
                    'score' => $score,
                    'errors' => [$errorMsg],
                ];
            }

            return [
                'success' => true,
                'score' => $score,
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
     * Регистрация дополнительного JavaScript для перехватчика форм.
     * @return void
     */
    public function registerAttachJavaScript(): void
    {
        $useAttachJs = true;

        if (function_exists('Plugins\setting')) {
            $useAttachJs = setting($this->plugin, 'use_attach_js', true);
        }

        if ($this->siteKey && $useAttachJs && function_exists('register_htmlvar')) {
            register_htmlvar('plain', $this->view('google-script', [
                'site_key' => $this->siteKey,
                'action' => 'submit',
            ]));
        }
    }
}
