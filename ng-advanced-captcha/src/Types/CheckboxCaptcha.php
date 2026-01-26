<?php

namespace Plugins\AdvancedCaptcha\Types;

/**
 * Checkbox "Я не робот" капча с проверкой взаимодействия.
 */
class CheckboxCaptcha
{
    protected $token;

    /**
     * Генерация токена.
     * @return string
     */
    public function generateChallenge(): string
    {
        $this->token = bin2hex(random_bytes(16));
        return 'Я не робот';
    }

    /**
     * Получить токен.
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->token;
    }
}
