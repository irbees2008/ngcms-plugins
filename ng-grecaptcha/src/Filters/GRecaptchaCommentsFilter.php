<?php

namespace Plugins\GRecaptcha\Filters;

// Сторонние зависимости.
use FilterComments;
use Plugins\GRecaptcha\GRecaptcha;
use function Plugins\dd;
use function Plugins\{notify, logger};

class GRecaptchaCommentsFilter extends FilterComments
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

    public function addCommentsForm($newsID, &$tvars) {}

    public function addComments($userRec, $newsRec, &$tvars, &$SQL)
    {
        if (! $this->recaptcha->verifying()) {
            $error = $this->recaptcha->rejectionReason();
            notify('error', $error);
            logger('ng-grecaptcha: Comment blocked - ' . $error, 'warning');
            return [
                'errorText' => $error,
            ];
        }

        logger('ng-grecaptcha: Comment captcha verified', 'info');
        return true;
    }
}
