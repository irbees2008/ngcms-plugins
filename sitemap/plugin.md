---
id: "sitemap"
icons: "<i class=\"fa fa-sitemap fa-3x\" aria-hidden=\"true\"></i>"
name: "Site Map"
version: "1.3"
acts: "ppages"
file: "sitemap.php"
config: "config.php"
type: "plugin"
description: "Карта сайта (modernized with ng-helpers v0.2.2, PHP 8.0+)"
author: "Wolverine, kt2k, NGCMS Team"
author_uri: "http://digitalplace.ru/"
minenginebuild: "23b3116"
preinstall: "no"
---
# Карта сайта

Плагин формирует карту сайта из категорий, новостей и статических страниц.

## Требования

- PHP 8.0+
- ng-helpers v0.2.2+

## Изменения 2026 года

- Обновлено до ng-helpers v0.2.2
- Заменено кеширование: cacheRetrieveFile/cache_put → cache()
- Добавлено логирование: engine/data/logs/sitemap.log

## Шаблон `sitemap.tpl`

| Переменная | Описание |
| --- | --- |
| `entries` | Объекты категорий, новостей и статических страниц. Структуру можно посмотреть через `debugValue(entries)`. |
| `pagination` | Постраничная навигация. |
| `counts` | Количество объектов: `counts.countCatz`, `counts.countNews`, `counts.countStatic`. |
| `news_per_page` | Количество новостей на странице. |
| `pages_count` | Общее количество страниц. |
| `page` | Номер текущей страницы. |
