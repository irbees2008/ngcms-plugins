<?php

namespace Plugins\Captcha\Filters;

use CoreFilter;
use Plugins\Captcha\Captcha;
use function Plugins\{notify, logger};

/**
 * Фильтр для регистрации пользователей (core).
 */
class CaptchaCoreFilter extends CoreFilter
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
     * Расширение формы регистрации.
     * @param  array  $tvars
     * @return void
     */
    public function registerUserForm(&$tvars)
    {
        // Добавить HTML виджета в переменные шаблона
        $tvars['plugin']['captcha_widget'] = $this->captcha->renderWidget();
    }

    /**
     * Проверка при регистрации пользователя.
     * @param  array  $params
     * @param  string  $msg
     * @return bool
     */
    public function registerUser($params, &$msg)
    {
        try {
            $this->captcha->validate();
            logger('ng-captcha: Registration captcha verified', 'info', 'captcha.log');
            return true;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            notify('error', $msg);
            logger('ng-captcha: Registration blocked - ' . $msg, 'warning', 'captcha.log');
            return false;
        }
    }
}
