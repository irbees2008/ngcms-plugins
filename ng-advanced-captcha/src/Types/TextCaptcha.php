<?php

namespace Plugins\AdvancedCaptcha\Types;

/**
 * Текстовая капча (случайная строка цифр и букв).
 */
class TextCaptcha
{
    protected $text;

    /**
     * Генерация текста.
     * @return string
     */
    public function generateChallenge(): string
    {
        $length = rand(5, 7);
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Исключаем похожие символы
        $text = '';

        for ($i = 0; $i < $length; $i++) {
            $text .= $characters[rand(0, strlen($characters) - 1)];
        }

        $this->text = $text;

        return $text;
    }

    /**
     * Получить правильный ответ.
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->text;
    }
}
