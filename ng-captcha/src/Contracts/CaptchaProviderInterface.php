<?php

namespace Plugins\Captcha\Contracts;

/**
 * Интерфейс провайдера капчи.
 */
interface CaptchaProviderInterface
{
    /**
     * Получить идентификатор провайдера.
     * @return string
     */
    public function getName(): string;

    /**
     * Получить ключ сайта.
     * @return string
     */
    public function getSiteKey(): string;

    /**
     * Получить секретный ключ.
     * @return string
     */
    public function getSecretKey(): string;

    /**
     * Получить URL API скрипта.
     * @return string
     */
    public function getApiScript(): string;

    /**
     * Получить URL API для верификации.
     * @return string
     */
    public function getApiVerify(): string;

    /**
     * Получить имя поля токена в POST запросе.
     * @return string
     */
    public function getTokenFieldName(): string;

    /**
     * Добавить JavaScript API в htmlvars.
     * @return void
     */
    public function registerJavaScript(): void;

    /**
     * Получить HTML виджета капчи.
     * @return string
     */
    public function renderWidget(): string;

    /**
     * Проверить токен капчи.
     * @param  string  $token
     * @param  string  $remoteIp
     * @return array [success => bool, score => float|null, errors => array]
     */
    public function verify(string $token, string $remoteIp = ''): array;

    /**
     * Валидация капчи (проверка с исключением при ошибке).
     * @param  string|null  $token
     * @param  string  $remoteIp
     * @return bool
     * @throws \Plugins\Captcha\Exceptions\ValidationException
     */
    public function validate(?string $token = null, string $remoteIp = ''): bool;
}
