<?php

namespace Plugins\AdvancedCaptcha\Filters;

// Сторонние зависимости.
use FilterComments;
use Plugins\AdvancedCaptcha\AdvancedCaptcha;
use function Plugins\dd;
use function Plugins\{notify, logger};

class AdvancedCaptchaCommentsFilter extends FilterComments
{
    /**
     * @var AdvancedCaptcha
     */
    protected $captcha;

    public function __construct(AdvancedCaptcha $captcha)
    {
        $this->captcha = $captcha;
    }

    public function addCommentsForm($newsID, &$tvars)
    {
        // Добавляем виджет капчи в переменные шаблона
        $tvars['vars']['captcha_widget'] = $this->captcha->generateWidget('comment_' . $newsID);
    }

    public function addComments($userRec, $newsRec, &$tvars, &$SQL)
    {
        if (! $this->captcha->verifying('comment_' . $newsRec['id'])) {
            $error = $this->captcha->rejectionReason();
            notify('error', $error);
            logger('ng-advanced-captcha: Comment blocked - ' . $error, 'warning');
            return [
                'errorText' => $error,
            ];
        }

        logger('ng-advanced-captcha: Comment captcha verified', 'info');
        return true;
    }
}
