<?php

namespace Plugins\YandexCaptcha\Filters;

// Сторонние зависимости.
use FilterComments;
use Plugins\YandexCaptcha\YandexCaptcha;
use function Plugins\dd;
use function Plugins\{notify, logger};

class YandexCaptchaCommentsFilter extends FilterComments
{
    /**
     * @var YandexCaptcha
     */
    protected $captcha;

    public function __construct(YandexCaptcha $captcha)
    {
        $this->captcha = $captcha;
    }

    public function addCommentsForm($newsID, &$tvars) {}

    public function addComments($userRec, $newsRec, &$tvars, &$SQL)
    {
        if (! $this->captcha->verifying()) {
            $error = $this->captcha->rejectionReason();
            notify('error', $error);
            logger('ng-yandex-captcha: Comment blocked - ' . $error, 'warning');
            return [
                'errorText' => $error,
            ];
        }

        logger('ng-yandex-captcha: Comment captcha verified', 'info');
        return true;
    }
}
