<?php

namespace Plugins\AdvancedCaptcha\Types;

/**
 * Slider капча (перетащить слайдер до конца).
 */
class SliderCaptcha
{
    protected $token;

    /**
     * Генерация задачи.
     * @return string
     */
    public function generateChallenge(): string
    {
        $this->token = bin2hex(random_bytes(16));
        return 'Перетащите ползунок вправо';
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
