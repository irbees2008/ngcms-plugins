---
id: "lastcomments"
icons: "<i class=\"fa fa-commenting fa-3x\" aria-hidden=\"true\"></i>"
name: "Последние комментарии"
version: "0.14"
acts: "index, ppages"
file: "lastcomments.php"
config: "config.php"
install: "install.php"
deinstall: "uninstall.php"
type: "plugin"
description: "Вывод последних комментариев"
author: "SwiZZeR, EsCaPeR, Vitaly, Megaket4up,irbees2008"
minenginebuild: "23b3116"
title: "Последние комментарии"
information: "Отображение последних комментариев, оставленных на страницах сайта"
preinstall: "no"
preinstall_vars: "cache=\"1\"; cacheExpire=\"3600\";"
---
# Последние комментарии

## Отображение плагина

### Боковая панель

1. Включите генерацию боковой панели в настройках плагина.
2. Добавьте `{{ plugin_lastcomments }}` в `main.tpl`.

### Страница плагина

1. Включите собственную страницу в настройках плагина.
2. Страница доступна по адресу `/plugin/lastcomments/`.

Ссылку можно сформировать в шаблоне:

```twig
<a href="{{ lastcomments_url }}" title="{{ lang['lastcomments:lastcomments_desc'] }}">
    {{ lang['lastcomments:lastcomments_desc'] }}
</a>
```

### RSS-лента

1. Включите RSS-ленту плагина в настройках.
2. Лента доступна по адресу `/plugin/lastcomments/rss/`.

Ссылка на RSS:

```twig
<a href="{{ lastcomments_url_rss }}" title="{{ lang['lastcomments:lastcomments_rss'] }}">
    {{ lang['lastcomments:lastcomments_rss'] }}
</a>
```

## Шаблоны боковой панели

Для отображения используются `lastcomments.tpl` (оболочка списка) и
`entries.tpl` (шаблон строки комментария).

### `lastcomments.tpl`

- `{{ entries }}` — строки комментариев.
- Условие `{% if comnum == 0 %}...{% endif %}` позволяет вывести содержимое,
  когда комментариев нет.

### `entries.tpl`

Доступные переменные:

- `{{ entry.link }}` — ссылка на новость с комментарием.
- `{{ entry.date }}` — дата комментария.
- `{{ entry.date|date('d.m.Y H:i:s') }}` — дата в формате `дд.мм.гггг чч:мм:сс`.
- `{{ entry.short_text }}` — сокращённый текст комментария.
- `{{ entry.full_text }}` — полный текст комментария.
- `{{ entry.author }}` — автор комментария.
- `{{ entry.avatar }}` и `{{ entry.avatar_url }}` — аватар и его URL.
- `{{ entry.title }}` — заголовок новости.
- `{{ entry.text }}` — текст комментария.
- `{{ entry.author_link }}` — ссылка на профиль пользователя.
- `{{ entry.category_link }}` — список категорий новости.
- `{{ entry.comnum }}` — номер комментария.
- `{{ entry.name }}` — автор ответа.
- `{{ entry.answer }}` — текст ответа.
- `{{ entry.alternating }}` — класс чётности: `lastcomments_odd` или
  `lastcomments_even`.

Доступные условные блоки:

- `{% if entry.author_id and pluginIsActive('uprofile') %}...{% endif %}` —
  информация о зарегистрированном авторе.
- `{% if entry.answer %}...{% endif %}` — блок ответа.

## Собственная страница плагина

Используется шаблон `pp_lastcomments.tpl`. Набор параметров аналогичен шаблонам
боковой панели; переменные записи доступны в формате `{{ entry.var_name }}`.

## Локализация

Файл локализации: `engine/plugins/lastcomments/lang/ru/main.ini`.
