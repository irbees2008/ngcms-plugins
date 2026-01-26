<?php

namespace Plugins\Turnstile\Filters;

// Сторонние зависимости.
use FilterComments;
use Plugins\Turnstile\Turnstile;
use function Plugins\dd;
use function Plugins\{notify, logger};

class TurnstileCommentsFilter extends FilterComments
{
    /**
     * @var Turnstile
     */
    protected $turnstile;

    public function __construct(Turnstile $turnstile)
    {
        $this->turnstile = $turnstile;
    }

    public function addCommentsForm($newsID, &$tvars)
    {
        // Добавляем виджет в переменные шаблона
        $tvars['vars']['turnstile_widget'] = $this->turnstile->generateWidget('comment_' . $newsID);
    }

    public function addComments($userRec, $newsRec, &$tvars, &$SQL)
    {
        if (! $this->turnstile->verifying()) {
            $error = $this->turnstile->rejectionReason();
            notify('error', $error);
            logger('ng-turnstile: Comment blocked - ' . $error, 'warning');
            return [
                'errorText' => $error,
            ];
        }

        logger('ng-turnstile: Comment captcha verified', 'info');
        return true;
    }
}
