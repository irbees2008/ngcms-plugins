<?php

namespace Plugins\Turnstile\Filters;

use Plugins\Turnstile\Turnstile;
use function Plugins\{notify, logger};

class TurnstileCoreFilter
{
    /**
     * @var Turnstile
     */
    protected $turnstile;

    public function __construct(Turnstile $turnstile)
    {
        $this->turnstile = $turnstile;
    }

    /**
     * Добавление виджета капчи в форму регистрации
     */
    public function registrationForm(&$tVars)
    {
        $tVars['captcha_widget'] = $this->turnstile->generateWidget('register');
    }

    /**
     * Проверка капчи при регистрации
     */
    public function registerUser($params)
    {
        if (! $this->turnstile->verifying()) {
            $error = $this->turnstile->rejectionReason();
            notify('error', $error);
            logger('ng-turnstile: Registration blocked - ' . $error, 'warning');
            return [
                'status' => 0,
                'errorText' => $error,
            ];
        }

        logger('ng-turnstile: Registration captcha verified', 'info');
        return true;
    }
}
