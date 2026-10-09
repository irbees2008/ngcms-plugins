---
id: "eshop"
icons: "<i class=\"fa fa-shopping-cart\" aria-hidden=\"true\"></i>"
name: "Интернет магазин"
version: "0.6"
file: "eshop.php"
config: "config.php"
install: "install.php"
deinstall: "uninstall.php"
type: "plugin"
description: "Организует на сайте интернет магазин"
author: "Rostunov S U"
author_uri: "http://rostunov.com"
actions:
  - "ppages; eshop.php"
  - "core, twig; info_eshop.php"
  - "index, twig; block_eshop.php"
  - "rpc; rpc_eshop.php"
minenginebuild: "23b3116"
title: "Интернет магазин"
preinstall: "no"
---
# Интернет магазин

## Установка

1. Загрузите плагин в `engine/plugins/`.
2. При необходимости загрузите тестовый шаблон `eshop2` в `/templates/` и выберите
   его в настройках CMS.
3. Установите плагин `eshop` в административной панели.
4. При необходимости настройте URL.

> **Внимание:** при установке плагин перезаписывает настройки URL. Резервная копия
> исходных настроек сохраняется в `engine/plugins/eshop/install_tmp/backup/`.

## Работа с валютами

После установки доступны три предустановленные валюты: USD, RUB и UAH. Основной
считается валюта, которая занимает первую позицию в таблице валют.

## Основная валюта

- Все цены продукции в административной панели задаются в основной валюте.
- При первом посещении магазина цены также отображаются в основной валюте.

Настройте основную валюту до добавления продукции и заказов: позднее её смена
может быть затруднительна.

Например, если продукт стоил 100 RUB, после смены основной валюты на USD его цена
останется равной 100, но уже будет интерпретироваться как USD.

При смене основной валюты цены товаров и заказов не конвертируются по курсу.



## Работа с системами оплаты

URL-адреса для платёжной системы:

- **Fail URL:** `http://sitename.ru/eshop/payment/?result=1&payment_id={payment_id}`
- **Result URL:** `http://sitename.ru/eshop/payment/?result=2&payment_id={payment_id}`
- **Success URL:** `http://sitename.ru/eshop/payment/?result=3&payment_id={payment_id}`

Переход на платёжную страницу можно реализовать формой в шаблоне `order_eshop.tpl`:

### Пример формы

```html
<form method="get" action="{{ payment.link }}" target="_blank">
    <input type="hidden" value="{{ formEntry.id }}" name="order_id">
    <input type="hidden" value="{{ formEntry.uniqid }}" name="order_uniqid">
    <input type="hidden" value="{{ payment.systems[2].name }}" name="payment_id">
    <div>
        <button type="submit">Оплатить</button>
    </div>
</form>
```

### Переменные в форме

- `payment.link` — URL обработчика формы (по умолчанию `/eshop/payment/`).
- `formEntry` — данные заказа; нужно передать параметры `id` и `uniqid`.
- `payment.systems` — список доступных систем оплаты; параметр `name` передаётся
  как `payment_id`.



## Экспорт

В CSV с разделителем `;`; первая строка содержит заголовки.

## Импорт

Импорт выполняется из CSV с разделителем `;`; первая строка содержит заголовки.
Порядок столбцов важен:

```text
id;code;url;name;price;compare_price;stock;annotation;body;active;featured;stocked;meta_title;meta_keywords;meta_description;date;editdate;cat_name;cid;images;xfields_source_id;xfields_source_url
```

Столбцы `cat_name` и `images` не учитываются.

Чтобы импортировать дополнительные изображения, создайте каталог
`engine/plugins/eshop/import/images/{ID_товара}/` и поместите изображения в него.

Например, для товара с ID `438` используйте
`engine/plugins/eshop/import/images/438/`.


## API

Адрес API по умолчанию: `/eshop/api/v{version}/`.

## Методы API v1


### GET `get-orders`

GET /eshop/api/v1/?type=get-orders&token={token}[&order_id={order_id}&from={ГГГГ-ММ-ДД}&to={ГГГГ-ММ-ДД}]

**Пример ответа:**

```json

{
  "data": {
    "1": {
      "id": "1",
      "author_id": "1",
      "uniqid": "3a5c8edbb6",
      "dt": "1505070388",
      "paid": "1",
      "type": "1",
      "name": "admin",
      "address": "5 South Main Street, Polar Express for 52017",
      "phone": "7324466721",
      "email": "",
      "comment": "12344",
      "ip": "127.0.0.1",
      "total_price": "21.00",
      "mail": "",
      "pass": "",
      "news": "0",
      "status": "1",
      "last": "1505172285",
      "reg": "1504953353",
      "site": "",
      "icq": "",
      "where_from": "",
      "info": "",
      "avatar": "",
      "photo": "",
      "activation": "",
      "newpw": "",
      "authcookie": ""
    }
  },
  "status": "OK"
}
```


### GET `get-order-products`

GET /eshop/api/v1/?type=get-order-products&token={token}[&order_id=1&from={ГГГГ-ММ-ДД}&to={ГГГГ-ММ-ДД}]

**Пример ответа:**

```json

{
   "data":{
      "1":{
         "positions":[
            {
               "id":"1",
               "order_id":"1",
               "linked_id":"1",
               "title":"p1",
               "count":"1",
               "price":"21.00",
               "sum":"    21.00",
               "xfields":{
                  "item":{
                     "id":"1",
                     "url":"p1",
                     "code":"p1",
                     "name":"p1",
                     "active":"1",
                     "featured":"0",
                     "position":"0",
                     "curl":"test1",
                     "category":"test1",
                     "image_filepath":"1505070301-dodo_123.png",
                     "v_id":"3",
                     "v_sku":"",
                     "v_name":"1",
                     "v_amount":"1",
                     "price":"21.00",
                     "compare_price":"0.00",
                     "stock":"5",
                     "view_link":"\/p1.html"
                  }
               }
            }
         ],
         "purchases":[

         ],
         "total":23
      },
      "2":{
         "positions":[
            {
               "id":"2",
               "order_id":"2",
               "linked_id":"3",
               "title":"ffffffffffffff",
               "count":"2",
               "price":"1.00",
               "sum":"     2.00",
               "xfields":{
                  "item":{
                     "id":"3",
                     "url":"ffffffffffffff",
                     "code":"fff",
                     "name":"ffffffffffffff",
                     "active":"1",
                     "featured":"0",
                     "position":"0",
                     "curl":"sub2",
                     "category":"sub2",
                     "image_filepath":null,
                     "v_id":"7",
                     "v_sku":"",
                     "v_name":"",
                     "v_amount":"99",
                     "price":"1.00",
                     "compare_price":"2.00",
                     "stock":"5",
                     "view_link":"\/ffffffffffffff.html"
                  }
               }
            }
         ],
         "purchases":[

         ],
         "total":23
      }
   },
   "status":"OK"
}
```


### GET `get-features`

GET /eshop/api/v1/?type=get-features&token={token}

**Пример ответа:**

```json

{
  "data": [
    {
      "id": "1",
      "name": "test",
      "position": "0",
      "ftype": "0",
      "fdefault": "123",
      "foptions": "",
      "in_filter": "0",
      "required": "0"
    },
    {
      "id": "2",
      "name": "test2",
      "position": "0",
      "ftype": "2",
      "fdefault": "2",
      "foptions": "{\"1\":\"t1\",\"2\":\"t2\"}",
      "in_filter": "1",
      "required": "0"
    }
  ],
  "status": "OK"
}
```


### GET `get-variants`

GET /eshop/api/v1/?type=get-variants&token={token}[&product_id={ID}]

**Пример ответа:**

```json

{
  "data": [
    {
      "id": "9",
      "product_id": "2",
      "sku": "",
      "name": "\u0421\u0438\u043d\u0438\u0439",
      "price": "12.00",
      "compare_price": "24.00",
      "stock": "5",
      "position": "0",
      "amount": "88",
      "attachment": ""
    },
    {
      "id": "8",
      "product_id": "2",
      "sku": "",
      "name": "\u041a\u0440\u0430\u0441\u043d\u044b\u0439",
      "price": "11.00",
      "compare_price": "22.00",
      "stock": "5",
      "position": "0",
      "amount": "3",
      "attachment": ""
    }
  ],
  "status": "OK"
}
```


### POST `update-order-statuses`

POST /eshop/api/v1/?type=update-order-statuses&token={token}

**Пример запроса:**

```json

[
  {
    "order_id" : "1",
    "status": "1"
  },
  {
    "order_id" : "2",
    "status": "0"
  }
]
```


**Пример ответа:**

```json

{
  "data": [
    {
      "id": "1",
      "status": "OK"
    },
    {
      "id": "",
      "status": "error",
      "message": "Item with this ID does not exist"
    }
  ],
  "status": "OK"
}
```


### POST `update-variants`

POST /eshop/api/v1/?type=update-variants&token={token}

**Пример запроса:**

```json

[
  {
    "id" : "9",
    "name": "Зеленый",
    "count": "44",
    "price": "1025",
    "price_old": "2077",
    "sku": "100012333"
  },
  {
    "product_id" : "2",
    "name": "Черный",
    "count": "5",
    "price": "25",
    "price_old": "121",
    "sku": "15112333"
  }
]
```


**Пример ответа:**

```json

{
   "data":[
      {
         "id":"9",
         "status":"OK"
      },
      {
         "id":"11",
         "status":"OK"
      }
   ],
   "status":"OK"
}
```


### POST `update-products`

POST /eshop/api/v1/?type=update-products&token={token}

### Добавление нового продукта

**Пример запроса:**

```json

    [
      {
        "name": "Название продукта 1",
        "short_description": "Короткое описание продукта 1",
        "description": "Полное описание продукта 1",
        "vendor_code": "100000123"
      },
      {
        "name": "Название продукта 2",
        "short_description": "Короткое описание продукта 2",
        "description": "Полное описание продукта 2",
        "vendor_code": "100000321"
      }
    ]
```


**Пример ответа:**

```json

    {
      "data": [
        {
          "id": 7,
          "status": "OK"
        },
        {
          "id": 8,
          "status": "OK"
        }
      ],
      "status": "OK"
    }
```


### Обновление существующего продукта

**Пример запроса:**

```json

    [
      {
        "id" : "7",
        "name": "Название продукта 12",
        "short_description": "Короткое описание продукта 12",
        "description": "Полное описание продукта 12",
        "vendor_code": "100000777"
      },
      {
        "id" : "8",
        "name": "Название продукта 22",
        "short_description": "Короткое описание продукта 22",
        "description": "Полное описание продукта 22",
        "vendor_code": "100000555"
      }
    ]
```


**Пример ответа:**

```json

    {
      "data": [
        {
          "id": "7",
          "status": "OK"
        },
        {
          "id": "8",
          "status": "OK"
        }
      ],
      "status": "OK"
    }
```


### POST `update-features`

POST /eshop/api/v1/?type=update-features&token={token}

**Пример запроса:**

```json

[
  {
    "id" : "1",
    "product_id": "2",
    "value": "Текст1"
  }
]
```


**Пример ответа:**

```json

{
   "data":[
      {
         "id":"",
         "status":"OK"
      }
   ],
   "status":"OK"
}
```



## Методы API v2


### GET `get-orders`

GET /eshop/api/v2/?type=get-orders&token={token}[&order_id={order_id}&from={ГГГГ-ММ-ДД}&to={ГГГГ-ММ-ДД}]

**Пример ответа:**

```json

{
  "data": {
    "5": {
      "dt": "2017-10-30 03:42:04",
      "paid": "0",
      "name": "fsdfs",
      "address": "dsfsdf",
      "phone": "fsdfsdf",
      "email": "admin@test003.loc",
      "comment": "",
      "total_price": "70.00",
      "order_id": "5",
      "paymentType": "Наличными при получении",
      "deliveryType": "Самовывоз",
      "positions": [
        {
          "linked_id": "20",
          "count": "3",
          "price": "22.00",
          "sum": "    66.00",
          "code": "120825AG",
          "name": "120825AG \u041a\u0430\u0431\u043b\u0443\u0447\u043a\u0430",
          "product_id": "19604"
        },
        {
          "linked_id": "21",
          "count": "4",
          "price": "1.00",
          "sum": "     4.00",
          "code": "120680AG",
          "name": "120680AG \u041a\u0430\u0431\u043b\u0443\u0447\u043a\u0430",
          "product_id": "19605"
        }
      ]
    },
    "6": {
      "dt": "2017-10-30 03:38:01",
      "paid": "1",
      "name": "re",
      "address": "dfgdfgdf",
      "phone": "gfdg",
      "email": "admin@test003.loc",
      "comment": "",
      "total_price": "154.00",
      "order_id": "6",
      "paymentType": "Банковской картой",
      "deliveryType": "Адресная доставка курьером",
      "positions": [
        {
          "linked_id": "20",
          "count": "7",
          "price": "22.00",
          "sum": "   154.00",
          "code": "120825AG",
          "name": "120825AG \u041a\u0430\u0431\u043b\u0443\u0447\u043a\u0430",
          "product_id": "19604"
        }
      ]
    }
  },
  "status": "OK"
}
```


### POST `update-products`

POST /eshop/api/v2/?type=update-products&token={token}

### Добавление нового продукта

**Пример запроса:**

```json

    {
      "products": [
        {
          "id": 19604,
          "vendor_code": "120825AG",
          "name": "120825AG Каблучка123",
          "short_description": "120825AG Каблучка",
          "description": "120825AG Каблучка",
          "price": null,
          "price_old": 123,
          "count": 1
        },
        {
          "id": 19605,
          "vendor_code": "120680AG",
          "name": "120680AG Каблучка",
          "short_description": "120680AG Каблучка",
          "description": "120680AG Каблучка",
          "price": null,
          "price_old": null,
          "count": 1
        }
      ]
    }
```


**Пример ответа:**

```json

    {
      "data": [
        {
          "id": "19604",
          "status": "OK"
        },
        {
          "id": "19605",
          "status": "OK"
        }
      ],
      "status": "OK"
    }
```


### Обновление существующего продукта

**Пример запроса:**

```json

    [
      {
        "id" : "7",
        "name": "Название продукта 12",
        "short_description": "Короткое описание продукта 12",
        "description": "Полное описание продукта 12",
        "vendor_code": "100000777"
      },
      {
        "id" : "8",
        "name": "Название продукта 22",
        "short_description": "Короткое описание продукта 22",
        "description": "Полное описание продукта 22",
        "vendor_code": "100000555"
      }
    ]
```


**Пример ответа:**

```json

    {
      "data": [
        {
          "id": "7",
          "status": "OK"
        },
        {
          "id": "8",
          "status": "OK"
        }
      ],
      "status": "OK"
    }
```


### POST `update-options`

POST /eshop/api/v2/?type=update-options&token={token}

**Пример запроса:**

```json

{
    "params": [
        {
            "id": "9052ffd1-4767-11e6-b348-00155d02ac06",
            "product_id": 19605,
            "name": "17,5",
            "count": 2
        }
    ]
}
```


**Пример ответа:**

```json

{
  "data": [
    {
      "id": "9052ffd1-4767-11e6-b348-00155d02ac06",
      "status": "OK"
    }
  ],
  "status": "OK"
}
```




## Страницы и шаблоны

| URL | Назначение и шаблон |
| --- | --- |
| `/[{alt}/][page/{page}/]` | Страницы категорий (`eshop.tpl`). |
| `/{alt}.html` | Страницы товаров (`show_eshop.tpl`). |
| `/eshop/search/[page/{page}/]` | Поиск (`search_eshop.tpl`). |
| `/eshop/stocks/[page/{page}/]` | Акционные товары (`stocks_eshop.tpl`). |
| `/eshop/compare/` | Сравнение товаров (`compare_eshop.tpl`). |
| `/eshop/yml_export/` | Экспорт товаров в XML (`yml_export_eshop.tpl`). |
| `/eshop/ebasket_list/` | Корзина и форма заказа (`ebasket/list.tpl`). |
| `/eshop/order/?id={id}&uniqid={uniqid}` | Страница заказа (`order_eshop.tpl`). |
| `/eshop/currency/?id={id}` | Переключение валюты. |
| `/eshop/payment/?payment_id={payment_id}&order_id={order_id}&order_uniqid={order_uniqid}` | Запрос и результат оплаты (`payment_eshop.tpl`). |
| `/eshop/api/?type={method}` | API-запросы. |

Другие шаблоны и файлы:

- `variables.ini` — переменные постраничной навигации.
- `comments.form_eshop.tpl` — форма отзыва на странице товара.
- `comments.show_eshop.tpl` — список отзывов на странице товара.
- `viewed_block_eshop.tpl` — просмотренные товары.
- `likes_eshop.tpl` — блок кнопки «Нравится» (+1).
- `mail/lfeedback.tpl` — письмо о новом заказе.
- `mail/lfeedback_comment.tpl` — письмо о новом отзыве.

Список переменных шаблона можно посмотреть с помощью `debugContext(0)` и
`debugValue(varName)`.


## Переменные и вызовы в main.tpl

### Блок товаров

Вызов `callPlugin('eshop.show', ...)` выводит блок товаров:

```twig
{{ callPlugin('eshop.show', {'number' : 10, 'mode' : 'stocked', 'template': 'block_eshop'}) }}
```

### Доступные параметры

- `number` — количество товаров.
- `mode` — режим: `last` (новые), `stocked` (акционные), `featured` (рекомендуемые),
  `view` (просматриваемые) или `rnd` (случайные).
- `template` — шаблон; например, `block_eshop` использует
  `block/block_eshop.tpl`. По умолчанию используется `block_eshop`.
- `cat` — категории для вывода; по умолчанию все категории.
- `products` — ID товаров для вывода; по умолчанию все товары.
- `cacheExpire` — время кеширования в секундах; по умолчанию кеширование отключено.

### Дерево категорий

Вызов `callPlugin('eshop.show_catz_tree', ...)` выводит дерево категорий.
Шаблон плагина по умолчанию: `plugins/eshop/tpl/cats_tree.tpl`.

```twig
{{ callPlugin('eshop.show_catz_tree', {'template': 'block_cats_tree'}) }}
```

### Доступные параметры

- `template` — шаблон вывода; например, `block_cats_tree` использует
  `block/block_cats_tree.tpl`. По умолчанию используется `block_cats_tree`.

### Другие блоки магазина

- `{{ callPlugin('eshop.total', {}) }}` — блок корзины с количеством и ценой
  (`ebasket/total.tpl`).
- `{{ callPlugin('eshop.notify', {}) }}` — блоки оформления заказа, включая добавление
  товара в корзину и заказ в один клик (`ebasket/notify.tpl`).
- `{{ callPlugin('eshop.compare', {}) }}` — количество товаров, добавленных к сравнению
  (`compare_block_eshop.tpl`).

Также в `main.tpl` доступны стандартные Twig-проверки для условного вывода блоков:

- `pluginIsActive('eshop')` — плагин активен.
- `isHandler('eshop')` — текущая страница принадлежит плагину.
- `isHandler('eshop:show')` — открыта страница товара.
- `isHandler('eshop:search')` — открыта страница поиска.
- `isHandler('eshop:stocks')` — открыта страница акционных товаров.
- `isHandler('eshop:compare')` — открыта страница сравнения.
- `isHandler('eshop:ebasket_list')` — открыта корзина и форма заказа.
- `isHandler('eshop:order')` — открыта страница заказа.
- Проверка `handler.pluginName == 'eshop'` и пустого `handler.handlerName` определяет
  страницы категорий.
- Проверки `handler.params.alt` позволяют выбрать отдельную страницу товара или
  категорию по её `alt-name`.


## Переменные шаблона profile.tpl (uprofile)


`{{ debugValue(shop.orders) }}` выводит массив заказов текущего пользователя.

### Пример вывода блока

```twig
{% for order in eshop.orders %}
    {{ order.id }}
    {{ order.order_link }}
    {{ order.dt|date("d.m.Y H:i") }}
    {{ (order.total_price * system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
    {% if (order.paid == 0) %}Не оплачен{% else %}Оплачен{% endif %}
{% endfor %}
```


## Переменные в шаблонах


`{{ debugValue(system_flags.eshop.currency) }}` выводит массив валют,
`{{ debugValue(system_flags.eshop.current_currency) }}` — выбранную валюту.

### Пример вывода блока со списком валют

```twig
{% for cc in system_flags.eshop.currency %}
    <li{% if (system_flags.eshop.current_currency.id == cc.id) %} class="active"{% endif %}><a href="{{ cc.currency_link }}">{{ cc.code }}</a></li>
{% endfor %}
```

2. {{ system_flags.eshop.description_order }} - блок описания покупки (берется из админки), {{ system_flags.eshop.description_delivery }} - блок описания доставки (берется из админки), {{ system_flags.eshop.description_phones }} - блок телефоны магазина (берется из админки)


Часть функционала использует RPC запросы для взаимодействия клиент-сервер

## AJAX-интеграция и действия с товарами

### 1. Добавление продукции в корзину

```javascript
rpcEshopRequest('eshop_ebasket_manage', {'action': 'add', 'ds':1, 'id':id, 'count':count, 'variant_id': variant_id }, function (resTX) {
    document.getElementById('tinyBask').innerHTML = resTX['update'];
});
```


### Обновление блока корзины

```javascript
rpcEshopRequest('eshop_ebasket_manage', {'action': 'update' }, function (resTX) {
    document.getElementById('tinyBask').innerHTML = resTX['update'];
});
```


**Шаблоны по умолчанию:** ebasket/total.tpl



### 2. Удаление продукции из заказа (корзины)

```javascript
rpcEshopRequest('eshop_ebasket_manage', {'action': 'delete', 'id':id, 'linked_ds':linked_ds, 'linked_id':linked_id }, function (resTX) {
    location.reload();
});
```


**Шаблоны по умолчанию:** ebasket/list.tpl



### 3. Обновление количества продукции в заказе (корзине)

```javascript
rpcEshopRequest('eshop_ebasket_manage', {'action': 'update_count',  'id':id, 'linked_ds':linked_ds, 'linked_id':linked_id,'count':count }, function (resTX) {
    click_this.val(count);
    
    var total = parseFloat(count * price).toFixed(2);
    click_this.parent().parent().parent().parent().parent().parent().find("td[class='frame-cur-sum-price frame-sum']").find("span[class='price']").text(total);

    var sum = 0;
    $("td[class='frame-cur-sum-price frame-sum'").each(function() {
        sum = sum + parseFloat($(this).find("span[class='price']").text());
    });
    $("#finalAmount").text(sum.toFixed(2));
});
```


**Шаблоны по умолчанию:** ebasket/list.tpl



### 4. Добавление быстрого заказа

```javascript
rpcEshopRequest('eshop_ebasket_manage', {'action': 'add_fast', 'ds':1, 'id':id, 'count':count, 'type': '2', 'name': name, 'phone': phone, 'address': address, 'variant_id': variant_id}, function (resTX) {
    $("div#fastorder-frame").html("<label><div align='center'>Заказ добавлен. В ближайшее время вам перезвонит наш манеджер.</div></label>");
});
```


Добавление заказа с кнопки "Узнать о наличии":
```javascript
rpcEshopRequest('eshop_ebasket_manage', {'action': 'add_fast', 'ds':1, 'id':id, 'count':count, 'type': '3', 'name': name, 'phone': phone, 'address': address, 'variant_id': variant_id}, function (resTX) {
    $("div#fastprice-frame").html("<label><div align='center'>Спасибо. В ближайшее время вам перезвонит наш манеджер.</div></label>");
});
```


**Шаблоны по умолчанию:** ebasket/total.tpl, ebasket/notify.tpl



### 5. Добавление / удаление продукции к сравнению

```javascript
rpcEshopRequest('eshop_compare', {'action': 'add', 'id':id }, function (resTX) {
    $('.compare-button').html(resTX['update']);
});
```


```javascript
rpcEshopRequest('eshop_compare', {'action': 'remove', 'id':id }, function (resTX) {
    $('.compare-button').html(resTX['update']);
});
```


**Шаблоны по умолчанию:** compare_block_eshop.tpl



### 6. Лайк продукции

```javascript
rpcEshopRequest('eshop_likes_result', {'action': 'do_like', 'id' : id }, function (resTX) {
    $(".ratebox2").html(resTX['update']);
});
```


**Шаблоны по умолчанию:** likes_eshop.tpl



### 7. Вывод блока просмотренной продукции

```javascript
var page_stack = br.storage.get('page_stack');
if(page_stack != null) {
    page_stack_str = page_stack.join(",");
    rpcEshopRequest('eshop_viewed', {'action': 'show', 'page_stack':page_stack_str }, function (resTX) {
        $('#ViewedProducts').html(resTX['update']);
    });
}
```


**Шаблоны по умолчанию:** viewed_block_eshop.tpl


На странице продукции (Шаблон вывода show_eshop.tpl) должено быть объявлено добавление ID продукции в localStorage

```javascript
br.storage.prependUnique('page_stack', {{ id }}, 25);
```



### 8. Добавление отзыва

```javascript
rpcEshopRequest('eshop_comments_add', { 'comment_author' : $('#comment_author').val(), 'comment_email' : $('#comment_email').val(), 'comment_text' : $('#comment_text').val(), 'product_id' : {{id}} }, function (resTX) {
    if ((resTX['data']['eshop_comments']>0)&&(resTX['data']['eshop_comments'] < 100)) {
        $(".error_text").html("<div class='msg js-msg'><div class='error error'><span class='icon_info'></span><div class='text-el'><p>"+resTX['data']['eshop_comments_text']+"</p></div></div></div>");
        $(".product-comment").html(""+resTX['data']['eshop_comments_show']+"");
    } else {
        $(".error_text").html("");
        $("#comment_text").val("");
        $(".product-comment").html(""+resTX['data']['eshop_comments_show']+"");
    }
});
```


**Шаблоны по умолчанию:** comments.form_eshop.tpl



### 9. Вывод списка отзывов

```javascript
rpcEshopRequest('eshop_comments_show', {'product_id' : {{id}}}, function (resTX) {
    $(".error_text").html("");
    $(".product-comment").html(""+resTX['data']['eshop_comments_show']+"");
});
```


**Шаблоны по умолчанию:** comments.show_eshop.tpl



### 10. Вывод блока с продукцией (аналог callPlugin('eshop.show'), с постраничной навигацией на AJAX)

```javascript
rpcEshopRequest('eshop_amain', {'action': 'show', 'number':8, 'mode':'last', 'page':0 }, function (resTX) {
    if ((resTX['data']['prd_main']>0)&&(resTX['data']['prd_main'] < 100)) {
        $("div#mainProductsPreview").html(""+resTX['data']['prd_main_text']+"");
        $("div#mainPagesPreview").html(""+resTX['data']['prd_main_pages_text']+"");
    } else {
        $("div#mainProductsPreview").html(""+resTX['data']['prd_main_text']+"");
        $("div#mainPagesPreview").html(""+resTX['data']['prd_main_pages_text']+"");
    }
});
```


**Шаблоны по умолчанию:** block/main_block_eshop.tpl, block/main_block_eshop_pages.tpl, main_variables.ini
