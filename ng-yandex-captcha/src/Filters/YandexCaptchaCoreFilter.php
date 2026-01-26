<?php

namespace Plugins\YandexCaptcha\Filters;

use Plugins\YandexCaptcha\YandexCaptcha;
use function Plugins\{notify, logger};

class YandexCaptchaCoreFilter
{
    /**
     * @var YandexCaptcha
     */
    protected $captcha;

    public function __construct(YandexCaptcha $captcha)
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
        if (! $this->captcha->verifying()) {
            $error = $this->captcha->rejectionReason();
            notify('error', $error);
            logger('ng-yandex-captcha: Registration blocked - ' . $error, 'warning');
            return [
                'status' => 0,
                'errorText' => $error,
            ];
        }

        logger('ng-yandex-captcha: Registration captcha verified', 'info');
        return true;
    }
}
