<?php

namespace Plugins\GRecaptcha\Filters;

// Сторонние зависимости.
use CoreFilter;
use Plugins\GRecaptcha\GRecaptcha;
use function Plugins\{notify, logger};

class GRecaptchaCoreFilter extends CoreFilter
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

    public function registerUserForm(&$tvars) {}

    public function registerUser($params, &$msg)
    {
        if (! $this->recaptcha->verifying()) {
            $msg = $this->recaptcha->rejectionReason();
            notify('error', $msg);
            logger('ng-grecaptcha: Registration blocked - ' . $msg, 'warning');

            return false;
        }

        logger('ng-grecaptcha: Registration captcha verified', 'info');
        return true;
    }
}
