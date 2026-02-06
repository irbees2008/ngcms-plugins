<?php

namespace Plugins\Captcha\Filters;

use FilterComments;
use Plugins\Captcha\Captcha;
use function Plugins\{notify, logger};

/**
 * Фильтр для комментариев.
 */
class CaptchaCommentsFilter extends FilterComments
{
    /**
     * Экземпляр капчи.
     * @var Captcha
     */
    protected $captcha;

    /**
     * Создать экземпляр фильтра.
     * @param  Captcha  $captcha
     */
    public function __construct(Captcha $captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * Расширение формы добавления комментария.
     * @param  int  $newsID
     * @param  array  $tvars
     * @return void
     */
    public function addCommentsForm($newsID, &$tvars)
    {
        // Добавить HTML виджета в переменные шаблона
        $tvars['captcha_widget'] = $this->captcha->renderWidget();
    }

    /**
     * Проверка при добавлении комментария.
     * @param  array  $userRec
     * @param  array  $newsRec
     * @param  array  $tvars
     * @param  array  $SQL
     * @return mixed
     */
    public function addComments($userRec, $newsRec, &$tvars, &$SQL)
    {
        try {
            $this->captcha->validate();
            logger('ng-captcha: Comment captcha verified', 'info', 'captcha.log');
            return true;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            notify('error', $error);
            logger('ng-captcha: Comment blocked - ' . $error, 'warning', 'captcha.log');
            return [
                'errorText' => $error,
            ];
        }
    }
}
