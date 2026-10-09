---
id: "rss_yandex"
icons: "<i class=\"fa fa-rss fa-3x\" aria-hidden=\"true\"></i>"
name: "Экспорт RSS потока для Яndex"
version: "0.04"
acts: "ppages"
file: "rss_yandex.php"
config: "config.php"
type: "plugin"
description: "Экспорт RSS потока для Яndex (ng-helpers v0.2.2, PHP 8.0+)"
author: "Vitaly A. Ponomarev, NGCMS Team"
author_uri: "http://ngcms.org/"
minenginebuild: "23b3116"
title: "Экспорт RSS потока для Яndex"
information: "Генерирует специальный RSS поток для поисковика Яndex"
preinstall: "no"
preinstall_vars: "feed_title_format=\"site_title\"; news_title=\"1\"; news_count=\"30\"; use_hide=\"1\""
---
# RSS Yandex Plugin

RSS-лента для Яндекс.Новостей.

## Требования

- PHP 8.0+
- ng-helpers v0.2.2+

## URL

```
https://ваш-сайт.ru/index.php?do=rss_yandex
https://ваш-сайт.ru/index.php?do=rss_yandex&category=название
```

## Логи

`engine/data/logs/rss_yandex.log`
