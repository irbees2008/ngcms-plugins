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

Карта сайта

Требования:
- PHP 8.0+
- ng-helpers v0.2.2+

Модернизация (2026):
- Обновлено до ng-helpers v0.2.2
- Заменено кеширование: cacheRetrieveFile/cache_put → cache()
- Добавлено логирование: engine/data/logs/sitemap.log
- Требуется PHP 8.0+

Шаблоны:
===sitemap.tpl===
{{ entries }}       - массив объектов - категрии, новости, статика (структуру подробнее смотреть через {{ debugValue(entries) }} )
{{ pagination }}    - постраничная навигация
{{ counts }}        - массив с общим количеством объектов ({{ counts.countCatz }}, {{ counts.countNews }}, {{ counts.countStatic }})
{{ news_per_page }} - количество новостей на странице
{{ pages_count }}   - количество страниц
{{ page }}          - номер текущей страницы
