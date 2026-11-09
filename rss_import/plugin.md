---
id: "rss_import"
icons: "<i class=\"fa fa-rss fa-3x\" aria-hidden=\"true\"></i>"
name: "RSS news importer"
version: "0.05"
acts: "index, ppages, twig"
file: "rss_import.php"
config: "config.php"
install: "install.php"
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
{{ callPlugin('rss_import.show', {'id': 'ID-блока'}) }}
```

`rss1`, `rss2` и далее сохраняются как совместимые переменные для перенесённых старых блоков. Новые профили выводятся по `profile_id` через `callPlugin`.

## Профили и шаблоны

Настройки плагина позволяют создавать RSS-профили с отдельным URL, параметрами отображения и каталогом шаблонов. Старые настройки `rss1`…`rss20` переносятся в профили при обновлении; перед миграцией сохраняется копия `engine/conf/plugdata.php`.

Для выбранного каталога должны быть доступны оба Twig-файла:

- `templates/ИМЯ_ТЕМЫ/plugins/rss_import/КАТАЛОГ/rss.tpl` и `entries.tpl`;
- либо `engine/plugins/rss_import/tpl/КАТАЛОГ/rss.tpl` и `entries.tpl`.

Сначала проверяется активная тема, затем каталог плагина. В поставку входят каталоги `rss1` и `rss2`.

## Логи

`engine/cache/logs/rss_import.log`
