---
id: "top_active_users"
icons: "<i class=\"fa fa-users fa-3x\" aria-hidden=\"true\"></i>"
name: "Top active users"
version: "0.4"
acts: "index"
file: "top_active_users.php"
config: "config.php"
type: "plugin"
description: "Топ активных пользователей"
author: "Rostunov Sergey"
author_uri: "http://rostunov.com"
minenginebuild: "23b3116"
preinstall: "no"
---
# Топ активных пользователей

Плагин выводит список активных пользователей с сортировкой по количеству
новостей или комментариев, дате регистрации либо в случайном порядке.

Для работы плагина используется единый TWIG шаблон (по умолчанию top_active_users.tpl).

## Переменные шаблона

- `{{tpl_url}}` — путь к активному шаблону сайта.

Цикл `{% for entry in entries %}...{% endfor %}` выводит строки информационного блока.
Доступны переменные:

- `{{entry.use_avatars}}` — настройка CMS, определяющая, используются ли аватары;
- `{{entry.avatar_url}}` — URL аватара;
- `{{entry.name}}` — имя пользователя;
- `{{entry.link}}` — ссылка на профиль;
- `{{entry.ulink}}` — ссылка на блог пользователя (плагин ublog);
- `{{entry.mail}}` — email пользователя;
- `{{entry.last}}` — дата последнего входа;
- `{{entry.reg}}` — дата регистрации;
- `{{entry.news}}` — количество новостей;
- `{{entry.com}}` — количество комментариев.

## Использование

Примеры вызова Twig-функции `callPlugin()`:

```twig
{{ callPlugin('top_active_users.show', {'number' : 12, 'mode' : 'news', 'template': 'top_active_users', 'cacheExpire': 60}) }}
{{ callPlugin('top_active_users.show', {'number' : 10, 'mode' : 'com' }) }}
```

| Параметр | Описание |
| --- | --- |
| `number` | Количество отображаемых пользователей. |
| `mode` | Сортировка: `news`, `com`, `last` или `rnd`. |
| `template` | Имя шаблона. |
| `cacheExpire` | Время кеширования; по умолчанию `0`. |

Плагин поддерживает кеширование и размещение собственных шаблонов внутри шаблона сайта.
