<?php

namespace Plugins\AdvancedCaptcha\Types;

/**
 * Математическая капча (примеры сложения, вычитания, умножения).
 */
class MathCaptcha
{
    protected $question;
    protected $answer;

    /**
     * Генерация задачи.
     * @return string
     */
    public function generateChallenge(): string
    {
        $operations = ['+', '-', '*'];
        $operation = $operations[array_rand($operations)];

        switch ($operation) {
            case '+':
                $a = rand(1, 20);
                $b = rand(1, 20);
                $this->answer = (string)($a + $b);
                $this->question = "$a + $b = ?";
                break;
            case '-':
                $a = rand(10, 30);
                $b = rand(1, $a - 1);
                $this->answer = (string)($a - $b);
                $this->question = "$a - $b = ?";
                break;
            case '*':
                $a = rand(2, 10);
                $b = rand(2, 10);
                $this->answer = (string)($a * $b);
                $this->question = "$a × $b = ?";
                break;
        }

        return $this->question;
    }

    /**
     * Получить правильный ответ.
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }
}
