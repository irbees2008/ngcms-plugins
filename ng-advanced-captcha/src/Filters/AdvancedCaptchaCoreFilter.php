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
    public function registerUserForm(&$tVars)
    {
        $tVars['captcha_widget'] = $this->captcha->generateWidget('register');
    }

    /**
     * Добавление виджета капчи в форму восстановления пароля
     */
    public function lostpasswordForm(&$tVars)
    {
        $tVars['captcha_widget'] = $this->captcha->generateWidget('lostpassword');
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

    /**
     * Проверка капчи при восстановлении пароля
     */
    public function lostpassword(&$msg, &$params, &$values)
    {
        if (! $this->captcha->verifying('lostpassword')) {
            $error = $this->captcha->rejectionReason();
            $msg = $error;
            logger('ng-advanced-captcha: Lostpassword blocked - ' . $error, 'warning');
            return false;
        }

        logger('ng-advanced-captcha: Lostpassword captcha verified', 'info');
        return true;
    }

    /**
     * Обработка после успешной регистрации пользователя
     *
     * @param int $userID ID зарегистрированного пользователя
     * @param array $userRec Массив данных пользователя
     * @return int
     */
    public function registerUserNotify($userID, $userRec)
    {
        // Очищаем данные капчи из сессии после успешной регистрации
        if (isset($_SESSION['captcha'])) {
            unset($_SESSION['captcha']);
        }

        logger('ng-advanced-captcha: User #' . $userID . ' registered successfully', 'info');
        return 1;
    }
}
