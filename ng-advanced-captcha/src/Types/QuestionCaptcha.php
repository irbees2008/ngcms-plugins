<?php

namespace Plugins\AdvancedCaptcha\Types;

/**
 * Вопрос-ответ капча.
 */
class QuestionCaptcha
{
    protected $questions = [
        ['q' => 'Сколько дней в неделе?', 'a' => '7'],
        ['q' => 'Сколько месяцев в году?', 'a' => '12'],
        ['q' => 'Сколько часов в сутках?', 'a' => '24'],
        ['q' => 'Сколько минут в часе?', 'a' => '60'],
        ['q' => 'Какого цвета снег?', 'a' => 'белый'],
        ['q' => 'Какого цвета небо?', 'a' => 'голубой'],
        ['q' => 'Сколько сезонов в году?', 'a' => '4'],
        ['q' => 'Сколько пальцев на руке?', 'a' => '5'],
        ['q' => 'Столица России?', 'a' => 'москва'],
        ['q' => 'Два плюс два?', 'a' => '4'],
        ['q' => 'Сколько колес у автомобиля?', 'a' => '4'],
        ['q' => 'Как называется первый день недели?', 'a' => 'понедельник'],
        ['q' => 'Сколько букв в слове "кот"?', 'a' => '3'],
        ['q' => 'Что идет после вторника?', 'a' => 'среда'],
        ['q' => 'Десять минус пять?', 'a' => '5'],
    ];

    protected $selectedQuestion;
    protected $answer;

    /**
     * Генерация вопроса.
     * @return string
     */
    public function generateChallenge(): string
    {
        $this->selectedQuestion = $this->questions[array_rand($this->questions)];
        $this->answer = $this->selectedQuestion['a'];

        return $this->selectedQuestion['q'];
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
