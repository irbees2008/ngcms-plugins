<?php

namespace Plugins\AdvancedCaptcha\Filters;

use Plugins\AdvancedCaptcha\AdvancedCaptcha;
use function Plugins\{notify, logger};

class AdvancedCaptchaFeedbackFilter
{
    /**
     * @var AdvancedCaptcha
     */
    protected $captcha;

    public function __construct(AdvancedCaptcha $captcha)
    {
        $this->captcha = $captcha;
    }

    public function sendFeedback($params)
    {
        if (! $this->captcha->verifying('feedback')) {
            $error = $this->captcha->rejectionReason();
            notify('error', $error);
            logger('ng-advanced-captcha: Feedback blocked - ' . $error, 'warning');
            return [
                'status' => 0,
                'errorText' => $error,
            ];
        }

        logger('ng-advanced-captcha: Feedback captcha verified', 'info');
        return true;
    }
}
