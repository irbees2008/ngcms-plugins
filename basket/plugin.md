---
id: "basket"
icons: "<i class=\"fa fa-cart-plus fa-3x\" aria-hidden=\"true\"></i>"
name: "Shop basket"
version: "0.09"
acts: "core, admin:mod:news, admin:mod:categories"
file: "basket.php"
config: "config.php"
install: "install.php"
deinstall: "deinstall.php"
type: "plugin"
description: "Корзина заказа"
author: "Vitaly A. Ponomarev,irbees2008,copilot"
author_uri: "http://ngcms.org"
actions:
  - "rpc;lib/librpc.php"
minenginebuild: "111111"
title: "Корзина заказа"
information: "Позволяет откладывать товары в корзину"
preinstall: "no"
---
# Корзина товаров

Плагин `basket` добавляет в NGCMS корзину товаров. Товарами могут быть новости и
строки таблиц дополнительных полей плагина `xfields`. Оформление заказа выполняется
через форму плагина `feedback`.

В версии 2.x добавлены сохранение заказов в базе данных, страница подтверждения
заказа и интеграция с плагином `payments`.

**Зависимости:** `xfields` и `feedback`. Плагин `payments` используется для оплаты
заказов.

## Установка

1. Установите плагины `xfields` и `feedback`.
2. Установите `basket` (или переустановите его для обновления).
   При установке создаются или обновляются таблицы:
   - `{prefix}_basket` — товары в корзинах;
   - `{prefix}_basket_orders` — оформленные заказы.
3. Откройте **Плагины → basket → Настройки** и настройте плагин.
4. Создайте в `feedback` форму оформления заказа и укажите её ID в настройках
   `basket`.

## Таблица заказов

Таблица `{prefix}_basket_orders` содержит оформленные заказы:

| Поле | Описание |
| --- | --- |
| `id` | ID заказа. |
| `user_id` | ID пользователя; `0` означает гостя. |
| `cookie` | Значение `ngTrackID` из cookie для гостевого заказа. |
| `uniqid` | Уникальный ключ заказа (MD5), используемый в URL. |
| `total` | Сумма заказа. |
| `status` | Статус заказа: `new` или `paid`. |
| `payment_method` | Способ оплаты, заполняемый плагином `payments`. |
| `items_json` | JSON-массив товаров на момент оформления заказа. |
| `contact_json` | JSON с данными формы `feedback` (`$_POST`). |
| `created_at` | Unix-время создания заказа. |
| `paid_at` | Unix-время оплаты. |

## Настройки

Параметры доступны в разделе **Плагины → basket → Настройки**.

| Параметр | Описание |
| --- | --- |
| `feedback_form` | ID формы `feedback` для оформления заказа (обязательный). |
| `ntable_flag` | Включает корзину для таблиц `xfields` внутри новостей. |
| `ntable_xfield` | Поле `xfields`, по которому в строках таблицы определяется доступность корзины. |
| `ntable_price` | Поле `xfields` с ценой товара в таблице. |
| `ntable_itemname` | Формат названия товара из таблицы. Подстановки: `{title}`, `{xt:NAME}`, `{x:NAME}`. |
| `news_flag` | Включает добавление самих новостей в корзину. |
| `news_xfield` | Поле `xfields`, по которому определяется доступность корзины для новости. |
| `news_price` | Поле `xfields` с ценой новости. |
| `news_itemname` | Формат названия товара-новости. Подстановки: `{title}`, `{x:NAME}`. |

## URL плагина

| Адрес | Назначение |
| --- | --- |
| `/basket/` | Просмотр корзины. |
| `/basket/update/` | Обновление количества товаров (POST). |
| `/basket/order/` | Страница подтверждения заказа. |
| `/basket/order/?order_id=N` | Просмотр заказа по ID из ссылки в письме. |
| `/basket/add/?ds=X&id=N` | Добавление товара; внутренний URL плагина. |

## Переменные в шаблонах новостей

Когда `news_flag = 1`, `NewsFilter` добавляет переменную `{{ basket_link }}` —
ссылку для добавления новости в корзину. BBCode-тег `[basket]...[/basket]`
показывает содержимое только для новости, для которой включена корзина.

Пример для `news.tpl`:

```html
[basket]
  <a href="{{ basket_link }}">В корзину — {{ xfields_price }} ₽</a>
[/basket]
```

## Шаблоны

Шаблоны расположены в каталоге `tpl/`.

### `tpl/total.tpl`

Виджет корзины, который выводится на страницах через `add_act`.

| Переменная | Описание |
| --- | --- |
| `{{ count }}` | Количество позиций в корзине. |
| `{{ price }}` | Сумма заказа числом. |
| `{{ price_formatted }}` | Отформатированная сумма, например `1 234.50`. |

### `tpl/list.tpl`

Шаблон страницы `/basket/`.

| Переменная | Описание |
| --- | --- |
| `{{ recs }}` | Количество позиций. |
| `{{ entries }}` | Массив товаров с полями `id`, `title`, `price`, `price_formatted`, `count`, `sum`, `sum_formatted` и `xfields`. |
| `{{ total }}` | Итоговая сумма числом. |
| `{{ total_formatted }}` | Отформатированная итоговая сумма. |
| `{{ form_url }}` | URL формы `feedback` для оформления заказа. |

### `tpl/lfeedback.tpl`

Блок со списком товаров внутри формы `feedback`, отображаемый перед подтверждением.
Доступны переменные `{{ recs }}`, `{{ entries }}`, `{{ total }}` и
`{{ total_formatted }}`; структура `entries` такая же, как в `tpl/list.tpl`.

### `tpl/order.tpl`

Шаблон страницы `/basket/order/` с подтверждением заказа и кнопкой оплаты.

| Переменная | Описание |
| --- | --- |
| `{{ order }}` | Данные заказа из `basket_orders`, включая `id`, `total`, `status`, `uniqid` и `created_at`. |
| `{{ items }}` | Декодированный JSON-массив товаров с полями `id`, `title`, `price`, `count` и `xfields`. |
| `{{ pay_link }}` | URL страницы оплаты `/payments/pay/...` или `null`. |
| `{{ pay_active }}` | `true`, если установлен плагин `payments`. |

### `tpl/basket.tpl`

Вспомогательный шаблон; при необходимости используйте его в своей теме.

## Оформление заказа

1. Пользователь добавляет товары по адресу `/basket/add/?ds=X&id=N`.
2. Просматривает корзину на `/basket/`.
3. Переходит к форме `feedback` по адресу `/feedback/?id=FORM_ID` (значение
   передаётся в `form_url`).
4. Заполняет и отправляет форму.
5. Обработчик `onProcessNotify` сохраняет заказ в `basket_orders` и очищает корзину.
6. Пользователь перенаправляется на `/basket/order/`.
7. Если установлен `payments`, на странице заказа отображается кнопка перехода
   к оплате по адресу `/payments/pay/`.
8. После оплаты статус заказа меняется на `paid`.

## Примеры шаблонов

### Кнопка добавления товара из таблицы `xfields`

Пример для шаблона таблицы `xfields` (`tdata`):

```twig
{% if flags.basket_allow %}
  <a href="{{ basket_link }}" class="btn-cart">В корзину</a>
{% endif %}
```

### Виджет корзины в шапке сайта

Пример для `header.tpl`:

```twig
{% if plugin_basket is defined %}
  {{ plugin_basket|raw }}
{% endif %}
```

Пример содержимого `tpl/total.tpl`:

```html
<a href="/basket/" class="cart-icon">
  🛒 {{ count }} товар(а) на {{ price_formatted }} ₽
</a>
```
