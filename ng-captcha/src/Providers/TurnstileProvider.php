<?php

namespace Plugins\Captcha\Providers;

use function Plugins\{setting, trans};

/**
 * Провайдер Cloudflare Turnstile.
 */
class TurnstileProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'turnstile';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiScript(): string
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiVerify(): string
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenFieldName(): string
    {
        return 'cf-turnstile-response';
    }

    /**
     * {@inheritdoc}
     */
    public function renderWidget(): string
    {
        $theme = 'auto';
        $size = 'normal';

        if (function_exists('Plugins\setting')) {
            $theme = setting($this->plugin, 'turnstile_theme', 'auto');
            $size = setting($this->plugin, 'turnstile_size', 'normal');
        }

        return $this->view('turnstile-widget', [
            'site_key' => $this->siteKey,
            'theme' => $theme,
            'size' => $size,
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
}
