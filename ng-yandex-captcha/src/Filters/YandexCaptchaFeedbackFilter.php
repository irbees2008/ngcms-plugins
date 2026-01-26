<?php

namespace Plugins\YandexCaptcha\Filters;

use Plugins\YandexCaptcha\YandexCaptcha;
use function Plugins\{notify, logger};

class YandexCaptchaFeedbackFilter
{
    /**
     * @var YandexCaptcha
     */
    protected $captcha;

    public function __construct(YandexCaptcha $captcha)
    {
        $this->captcha = $captcha;
    }

    public function sendFeedback($params)
    {
        if (! $this->captcha->verifying()) {
            $error = $this->captcha->rejectionReason();
            notify('error', $error);
            logger('ng-yandex-captcha: Feedback blocked - ' . $error, 'warning');
            return [
                'status' => 0,
                'errorText' => $error,
            ];
        }

        logger('ng-yandex-captcha: Feedback captcha verified', 'info');
        return true;
    }
}
