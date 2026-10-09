---
id: "rss_import"
icons: "<i class=\"fa fa-rss fa-3x\" aria-hidden=\"true\"></i>"
name: "RSS news importer"
version: "0.04"
acts: "index, ppages"
file: "rss_import.php"
config: "config.php"
type: "plugin"
description: "Импорт новостей через RSS (ng-helpers v0.2.2, PHP 8.0+)"
author: "Dzmitry Khadorkin, irbees2008, NGCMS Team"
minenginebuild: "23b3116"
title: "RSS news importer"
information: "Импорт новостей через RSS"
preinstall: "no"
preinstall_vars: "cache=\"1\"; cacheExpire=\"3600\";"
---
# RSS Import Plugin

Импорт внешних RSS-лент.

## Требования

- PHP 8.0+
- ng-helpers v0.2.2+

## Использование

```twig
{{ rss1 }}
{{ rss2 }}
```

## Логи

`engine/data/logs/rss_import.log`
