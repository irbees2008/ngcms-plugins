---
id: "faq"
icons: "<i class=\"fa fa-question fa-3x\" aria-hidden=\"true\"></i>"
name: "Вопросы и ответы (modernized with ng-helpers v0.2.2)"
version: "0.2"
acts: "ppages, index"
file: "faq.php"
config: "config.php"
install: "install.php"
deinstall: "uninstall.php"
type: "plugin"
description: "Плагин \"Вопросы и ответы\" с поддержкой ng-helpers v0.2.2: безопасный доступ к массивам через array_get(), обновленный формат логирования logger(message, level, file), улучшенная безопасность"
author: "Rostunov Sergey"
author_uri: "http://rostunov.com"
minenginebuild: "23b3116"
title: "Вопросы и ответы"
preinstall: "no"
---
# Вопросы и ответы

## Модернизация с ng-helpers v0.2.2

- `array_get()` обеспечивает безопасный доступ к `$_REQUEST` без предупреждений
  `Undefined index`.
- Для логирования используется формат `logger(message, level, file)`.
- 13 прямых обращений к `$_REQUEST` заменены вызовами `array_get()`.
- Повышены безопасность и стабильность плагина.

## Шаблон страницы FAQ

Для страницы плагина используется Twig-шаблон `faq_page.tpl`.

Доступны переменные:

- `{{tpl_url}}` — путь к активному шаблону сайта.
- Цикл `{% for entry in entries %}...{% endfor %}` — строки списка вопросов и ответов.
  Внутри цикла:
  - `{{entry.id}}` — ID записи;
  - `{{entry.question}}` — текст вопроса;
  - `{{entry.answer}}` — текст ответа.

## Виджет FAQ

Для генерации блока в шаблоне сайта используется Twig-шаблон `faq_block.tpl`.
Доступны переменные `{{tpl_url}}`, `{{entry.id}}`, `{{entry.question}}` и
`{{entry.answer}}` (последние три — внутри цикла `entries`).

Пример вызова:

```twig
{{ callPlugin('faq.show', {'maxnum' : 3, 'template': 'faq_block', 'order' : 'ASC', 'cacheExpire': '360'}) }}
```

| Параметр | Описание |
| --- | --- |
| `maxnum` | Количество записей в блоке. |
| `template` | Имя шаблона. |
| `order` | Сортировка: `ASC` или `DESC`. |
| `cacheExpire` | Время кеширования. |
