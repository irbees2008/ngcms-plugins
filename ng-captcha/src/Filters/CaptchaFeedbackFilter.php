<?php

namespace Plugins\Captcha\Filters;

use FeedbackFilter;
use Plugins\Captcha\Captcha;
use function Plugins\{notify, logger};

/**
 * Фильтр для обратной связи (feedback).
 */
class CaptchaFeedbackFilter extends FeedbackFilter
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
     * Расширение формы обратной связи.
     * @param  int  $formID
     * @param  array  $formStruct
     * @param  array  $formData
     * @param  array  $tvars
     * @return void
     */
    public function onShow($formID, $formStruct, $formData, &$tvars)
    {
        // Добавить HTML виджета в переменные шаблона
        $tvars['captcha_widget'] = $this->captcha->renderWidget();
    }

    /**
     * Проверка при отправке формы.
     * @param  int  $formID
     * @param  array  $formStruct
     * @param  array  $formData
     * @param  bool  $flagHTML
     * @param  array  $tVars
     * @param  array  $tResult
     * @return bool
     */
    public function onProcessEx($formID, $formStruct, $formData, $flagHTML, &$tVars, &$tResult)
    {
        try {
            $this->captcha->validate();
            logger('ng-captcha: Feedback captcha verified', 'info', 'captcha.log');
            return true;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $tResult['rawmsg'] = $error;
            notify('error', $error);
            logger('ng-captcha: Feedback blocked - ' . $error, 'warning', 'captcha.log');
            return false;
        }
    }
}
