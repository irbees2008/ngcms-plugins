---
id: "payments"
icons: "<i class=\"fa fa-credit-card fa-3x\" aria-hidden=\"true\"></i>"
name: "Payments"
version: "1.0.0"
file: "payments.php"
config: "config.php"
install: "install.php"
type: "plugin"
description: "Платёжные адаптеры для магазина"
author: "copilot"
author_uri: "https://ngcms.org/"
minenginebuild: "111111"
title: "Платёжные системы"
information: "Обеспечивает приём онлайн-оплаты заказов через Robokassa, LiqPay, YooMoney, Unitpay, Pay2Pay, Privat24, FreeKassa."
preinstall: "no"
---
# Платёжные шлюзы

**Версия:** 1.0.0  
**Зависимости:** `basket`, `feedback`, `xfields`

## Описание

Плагин подключает платёжные шлюзы к корзине (`basket`). После оформления заказа через форму `feedback` пользователь перенаправляется на страницу выбора оплаты.

Поддерживаемые адаптеры: Robokassa, LiqPay, Unitpay, Pay2Pay и Privat24.

## Установка

1. Убедитесь, что установлены и настроены `xfields`, `feedback` и `basket`.
2. Установите плагин `payments` через панель администратора: «Плагины» → `payments` → «Установить». Будет создана таблица `{prefix}_payments_transactions`.
3. Откройте настройки плагина: «Плагины» → `payments` → «Настройки».
   - Укажите валюту по умолчанию (ISO-код: `RUB`, `UAH`, `USD`).
   - Включите нужные адаптеры и заполните ключи API.
4. В настройках плагина `basket` укажите форму `feedback` для оформления заказа.

## Настройка адаптеров

Каждый адаптер настраивается в разделе «Настройки» плагина `payments`.

### Robokassa

- `payments_robokassa_enabled` — включить (1/0).
- `payments_robokassa_label` — название для пользователя.
- `payments_robokassa_mrh_login` — логин магазина.
- `payments_robokassa_mrh_pass1` — пароль 1.
- `payments_robokassa_mrh_pass2` — пароль 2.
- `payments_robokassa_test_mode` — тестовый режим (1/0).

### LiqPay

- `payments_liqpay_enabled` — включить.
- `payments_liqpay_public_key` — public key.
- `payments_liqpay_private_key` — private key.

### Unitpay

- `payments_unitpay_enabled` — включить.
- `payments_unitpay_public_key` — public key.
- `payments_unitpay_secret_key` — secret key.
- `payments_unitpay_project_id` — ID проекта.

### Pay2Pay

- `payments_pay2pay_enabled` — включить.
- `payments_pay2pay_shop_id` — Shop ID.
- `payments_pay2pay_secret` — Secret key.

### Privat24

- `payments_privat24_enabled` — включить.
- `payments_privat24_merchant_id` — Merchant ID.
- `payments_privat24_merchant_password` — Merchant password.

## URL-страницы плагина

| URL | Назначение |
|---|---|
| `/payments/pay/?order_id=N&uniqid=X` | Выбор способа оплаты |
| `/payments/pay/?order_id=N&uniqid=X&method=Y` | Перенаправление на платёжный шлюз |
| `/payments/success/?order_id=N` | Успешная оплата |
| `/payments/fail/?order_id=N` | Неудачная оплата |
| `/payments/notify/?method=robokassa` | IPN-callback Robokassa |
| `/payments/notify/?method=liqpay` | IPN-callback LiqPay |
| `/payments/status/?order_id=N&uniqid=X` | AJAX-статус заказа (JSON) |

## Шаблоны (`tpl/`)

### `tpl/pay.tpl`

Страница выбора способа оплаты. Доступны переменные:

- `{{ order }}` — массив с данными заказа (`id`, `total`, `status` и другие).
- `{{ order.id }}` — ID заказа.
- `{{ order.total }}` — сумма заказа.
- `{{ order.uniqid }}` — уникальный ключ заказа.
- `{{ items }}` — массив товаров (`title`, `price`, `count`).
- `{{ adapters }}` — список кодов активных адаптеров, например `['robokassa', ...]`.

Пример кнопок оплаты:

```twig
{% for method in adapters %}
  <a href="/payments/pay/?order_id={{ order.id }}&uniqid={{ order.uniqid }}&method={{ method }}">
    Оплатить через {{ method }}
  </a>
{% endfor %}
```

### `tpl/success.tpl`

Страница успешной оплаты. Переменная `{{ order }}` содержит данные заказа или `null`.

### `tpl/fail.tpl`

Страница неудачной оплаты. Переменная `{{ order }}` содержит данные заказа или `null`.

## Интеграция с basket

При оформлении заказа обработчик `onProcessNotify` плагина `basket` автоматически:

1. Сохраняет заказ в таблицу `basket_orders`.
2. Сохраняет ID и `uniqid` заказа в сессию.
3. Перенаправляет на `/basket/order/?order_id=N`.

На странице `/basket/order/` находится кнопка «Перейти к оплате», ведущая на `/payments/pay/`.

## Добавление нового адаптера

1. Создайте папку `engine/plugins/payments/adapters/{name}/`.
2. Создайте файл `engine/plugins/payments/adapters/{name}/adapter.php`.
3. Реализуйте функции перенаправления на шлюз и обработки IPN-callback:

```php
function payments_adapter_redirect(int $orderId, array $order, array $cfg): void
{
    header('Location: https://gateway.example/?amount=' . $order['total']);
}

function payments_adapter_notify(array $data, array $cfg): void
{
    $orderId = intval($data['order_id']);
    payments_mark_paid($orderId, 'mygateway', $data['transaction_id'], $data);
    echo 'OK';
}
```

4. Добавьте настройки адаптера в `config.php` (массив `$adapterMeta`).

## Проверка статуса оплаты (AJAX)

Запрос: `GET /payments/status/?order_id=N&uniqid=X`.

Ответ JSON: `{"status": "new"}`, `{"status": "paid"}` или `{"status": "error"}`.

Пример периодической проверки статуса:

```javascript
setInterval(() => {
  fetch('/payments/status/?order_id={{ order.id }}&uniqid={{ order.uniqid }}')
    .then(r => r.json())
    .then(d => {
      if (d.status === 'paid') location.reload();
    });
}, 5000);
```
