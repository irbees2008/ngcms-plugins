---
id: "calendar"
icons: "<i class=\"fa fa-calendar fa-3x\" aria-hidden=\"true\"></i>"
name: "Show news calendar"
version: "0.14"
acts: "index, twig, rpc"
file: "calendar.php"
config: "config.php"
type: "widget"
description: "Показать календарь"
author: "Vitaly A. Ponomarev"
author_uri: "http://ngcms.org/"
minenginebuild: "23b3116"
title: "Календарь новостей"
information: "Отображение данных о новостях по выбранному месяцу, при этом дни, когда были новости, выделяются."
preinstall: "no"
preinstall_vars: "cache=\"1\"; cacheExpire=\"3600\";"
---
# Календарь новостей

Плагин отображает календарь с датами публикации новостей и количеством новостей за
каждый день.

## Режимы работы

- **Автоматический запуск (устаревший вариант):** календарь генерируется при каждом
  формировании страницы сайта.
- **Вызов через Twig:** календарь генерируется функцией из шаблона. Можно вывести
  несколько независимых календарей и вызывать их только при необходимости.

Для календаря используется Twig-шаблон `calendar.tpl`.

## Переменные шаблона

| Переменная | Описание |
| --- | --- |
| `currentMonth.name` | Название выбранного месяца. |
| `currentMonth.link` | Ссылка на новости за выбранный месяц. |
| `currentEntry.month` | Номер месяца текущего вызова. |
| `currentEntry.year` | Год текущего вызова. |
| `currentEntry.template` | Используемый шаблон. |
| `currentEntry.categories` | Список категорий, по которым строится календарь. |
| `prevMonth.link` | Ссылка на предыдущий месяц. |
| `nextMonth.link` | Ссылка на следующий месяц. |
| `flags.havePrevMonth` | Можно ли перейти к предыдущему месяцу. |
| `flags.haveNextMonth` | Можно ли перейти к следующему месяцу. |
| `flags.ajax` | Шаблон формируется через AJAX-вызов. |
| `weekdays` | Короткие названия дней недели: индексы от `0` (воскресенье) до `6` (суббота). |

`weeks` — календарь, сгруппированный по неделям. Каждый элемент дня может содержать:

- `dayNo` — номер дня месяца; пустое значение означает, что в этой ячейке нет даты.
  Например, если месяц начинается со среды, ячейки понедельника и вторника пусты.
- `countNews` — количество новостей за этот день.
- `className` — CSS-класс: `calendar_class_weekday`, `calendar_class_weekend`,
  `calendar_class_today_weekday` или `calendar_class_today_weekend`.
- `link` — ссылка на новости за этот день.
- `isToday` — `true`, если дата соответствует сегодняшнему дню.
- `isWeekDay` — `true` для рабочего дня (понедельник–пятница).
- `isWeekEnd` — `true` для выходного (суббота–воскресенье).

Также доступны глобальные переменные `tpl_url` (URL шаблонов сайта) и `lang`
(массив языковых переменных).

## Использование в шаблонах

В автоматическом режиме блок календаря доступен в `main.tpl` через переменную
`{plugin_calendar}`.

Для генерации календаря Twig-функцией `callPlugin()` используйте:

```twig
{{ callPlugin('calendar.show', {'cache': 60 }) }}
```

Параметры функции `calendar.show`:

| Параметр | Описание |
| --- | --- |
| `year` | Год. |
| `month` | Месяц. |
| `offset` | Смещение относительно указанного месяца: `prev` или `next`. |
| `template` | Имя шаблона. |
| `category` | Список категорий; если не задан, используются все категории. |
| `cache` | Время кеширования; по умолчанию `0`. |

## Переключение месяца без перезагрузки

Для переключения календаря без обновления страницы используется AJAX/RPC-метод
`plugin.calendar.show`. Он принимает параметры `year`, `month` и `offset`.

Пример JavaScript-функции для вызова метода:

```javascript
function ng_calendar_walk(month, year, offset) {
    $.post('/engine/rpc.php', {
        json: 1,
        methodName: 'plugin.calendar.show',
        rndval: new Date().getTime(),
        params: json_encode({
            year: year,
            offset: offset,
            month: month
        })
    }, function (data) {
        // Ответ содержит status и data (HTML календаря).
        try {
            resTX = eval('(' + data + ')');
        } catch (err) {
            alert('Error parsing JSON output. Result: ' + linkTX.response);
        }

        if (!resTX.status) {
            ngNotifyWindow(
                'Error [' + resTX.errorCode + ']: ' + resTX.errorText,
                'ERROR'
            );
        } else {
            $('#ngCalendarDiv').html(resTX.data);
        }
    }, 'text').error(function () {
        ngHideLoading();
        ngNotifyWindow('HTTP error during request', 'ERROR');
    });
}
```
