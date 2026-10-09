---
id: "other_user_news"
icons: "<i class=\"fa fa-user-plus fa-3x\" aria-hidden=\"true\"></i>"
name: "Other user news"
version: "0.2"
acts: "twig,index"
file: "other_user_news.php"
config: "config.php"
type: "plugin"
description: "Другие новости от пользователя"
author: "Rostunov Sergey"
author_uri: "http://rostunov.com"
minenginebuild: "23b3116"
information: "Выводит другие новости от автора текущей новости"
preinstall: "no"
---
# Другие новости от пользователя

## Модернизация с ng-helpers v0.2.2 (2026-02-03)

- Заменено `cacheRetrieveFile/cacheStoreFile` на `cache_get/cache_put`
- Добавлено логирование операций кеша (`engine/logs/other_user_news.log`)
- Требуется **PHP 8.0+** и **NGCMS 23b3116+**

Плагин выводит другие новости того же пользователя на странице полной новости.

Для работы плагина используется единый TWIG шаблон (по умолчанию other_user_news.tpl).

## Переменные шаблона

- `{{tpl_url}}` — путь к активному шаблону сайта.
- `{{author}}` — имя пользователя, добавившего новость.
- `{{author_link}}` — ссылка на страницу пользователя.

Цикл `{% for entry in entries %}...{% endfor %}` выводит новости. Внутри цикла доступны:

- `{{entry.title}}` — заголовок новости;
- `{{entry.news_url}}` — URL новости;
- `{{entry.short_news}}` — краткий текст новости;
- `{{entry.postdate}}` — дата публикации;
- другие поля записи `news`, например `{{entry.views}}`.

## Использование

Примеры вызова Twig-функции `callPlugin()`:

```twig
{{ callPlugin('other_user_news.show', {'number' : 2, 'mode' : 'dt'}) }}
{{ callPlugin('other_user_news.show', {'number' : 10, 'mode' : 'view', 'template': 'other_user_news', 'cacheExpire': 60 }) }}
```

| Параметр | Описание |
| --- | --- |
| `number` | Количество отображаемых новостей. |
| `mode` | Сортировка: `view`, `com`, `dt` или `rnd`. |
| `template` | Имя шаблона. |
| `cacheExpire` | Время кеширования; по умолчанию `0`. |

Плагин поддерживает кеширование и размещение собственных шаблонов внутри шаблона сайта.
