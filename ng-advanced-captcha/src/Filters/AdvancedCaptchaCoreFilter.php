<?php

namespace Plugins\AdvancedCaptcha\Filters;

use Plugins\AdvancedCaptcha\AdvancedCaptcha;
use function Plugins\{notify, logger};

class AdvancedCaptchaCoreFilter
{
    /**
     * @var AdvancedCaptcha
     */
    protected $captcha;

    public function __construct(AdvancedCaptcha $captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * Добавление виджета капчи в форму регистрации
     */
    public function registrationForm(&$tVars)
    {
        $tVars['captcha_widget'] = $this->captcha->generateWidget('register');
    }

    /**
     * Проверка капчи при регистрации
     */
    public function registerUser($params)
    {
        if (! $this->captcha->verifying('register')) {
            $error = $this->captcha->rejectionReason();
            notify('error', $error);
            logger('ng-advanced-captcha: Registration blocked - ' . $error, 'warning');
            return [
                'status' => 0,
                'errorText' => $error,
            ];
        }

        logger('ng-advanced-captcha: Registration captcha verified', 'info');
        return true;
    }
}
