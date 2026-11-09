---
id: "neighboring_news"
icons: "<i class=\"fa fa-newspaper-o fa-3x\" aria-hidden=\"true\"></i>"
name: "Соседние новости"
version: "0.72"
acts: "news"
file: "neighboring_news.php"
config: "config.php"
type: "plugin"
description: "Предыдущая и следующая новости"
author: "Alexey N. Zhukov (Wolverine)"
author_uri: "http://digitalplace.ru/"
minenginebuild: "23b3116"
title: "Предыдущая и следующая новости"
information: "Добавляет новые переменные управления следующей и предыдущей новостью."
preinstall: "no"
---
# Предыдущая и следующая новости

Плагин выводит в теле новости ссылки на предыдущую и следующую публикации.

## Использование (Twig)

После включения плагина в шаблонах `news.full.tpl` или `news.short.tpl` доступна переменная `{{ neighboring_news }}`.
Так как плагин возвращает HTML, выводите её с фильтром `raw`:

```twig
{% if neighboring_news %}
  {{ neighboring_news|raw }}
{% endif %}
```

## Шаблоны плагина

- `tpl/neighboring_news.tpl` — оформление контейнера блока `neighboring_news`.
  - `{{ next_news }}` и `{{ previous_news }}` — уже отрендеренные HTML-фрагменты отдельных ссылок. Внутри контейнера их следует выводить с `|raw`.
- `tpl/previous_news.tpl` — оформление предыдущей новости.
  - Переменные: `{{ link }}` (URL), `{{ date }}` (дата), `{{ author }}` (автор), `{{ title }}` (заголовок).
- `tpl/next_news.tpl` — оформление следующей новости.
  - Переменные: `{{ link }}` (URL), `{{ date }}` (дата), `{{ author }}` (автор), `{{ title }}` (заголовок).

## Локализация (опционально)

В `lang/russian/main.ini` доступны ключи:

- `neighboring_news_prev = Предыдущая новость`
- `neighboring_news_next = Следующая новость`

Их можно использовать в шаблонах при необходимости подписей.

## Заметки по стилям

Базовые стили для контейнера добавлены прямо в `neighboring_news.tpl`. Рекомендуется вынести их в отдельный CSS и подключить через `register_stylesheet` для продакшена.

## Совместимость

Плагин использует синтаксис Twig. Наследие в виде `{var}` и блоков вида `[...][/...]` не применяется.

## Поддержка автора

- WebMoney: `Z185759217217`, `R128203457262`
- Яндекс.Деньги: `41001246158060`
