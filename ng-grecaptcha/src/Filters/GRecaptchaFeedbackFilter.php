<?php

namespace Plugins\GRecaptcha\Filters;

// Сторонние зависимости.
use FeedbackFilter;
use Plugins\GRecaptcha\GRecaptcha;
use function Plugins\dd;
use function Plugins\{notify, logger};

class GRecaptchaFeedbackFilter extends FeedbackFilter
{
    /**
     * [protected description]
     * @var GRecaptcha
     */
    protected $recaptcha;

    public function __construct(GRecaptcha $recaptcha)
    {
        $this->recaptcha = $recaptcha;
    }

    public function onShow($formID, $formStruct, $formData, &$tvars) {}

    public function onProcessEx($formID, $formStruct, $formData, $flagHTML, &$tVars, &$tResult)
    {
        if (! $this->recaptcha->verifying()) {
            $error = $this->recaptcha->rejectionReason();
            $tResult['rawmsg'] = $error;
            notify('error', $error);
            logger('ng-grecaptcha: Feedback blocked - ' . $error, 'warning');

            return false;
        }

        logger('ng-grecaptcha: Feedback captcha verified', 'info');
        return true;
    }
}
