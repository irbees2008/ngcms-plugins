---
id: "qrcode"
icons: "<i class=\"fa fa-qrcode fa-3x\" aria-hidden=\"true\"></i>"
name: "QRcode"
version: "0.02"
acts: "news_full"
file: "qrcode.php"
config: "config.php"
type: "plugin"
description: "Генерирует QRcode"
author: "Dzmitry Khadorkin (KhadeR), irbees2008"
author_uri: "http://khadersg.com/me/"
minenginebuild: "3740369"
preinstall: "no"
---
# QRcode

## Описание

Плагин генерирует QR-код для новости. Библиотека `phpqrcode` подключается локально из папки плагина; внешние запросы не выполняются.

## Возможности

- Рендеринг через Twig с fallback на старую систему шаблонов.
- Встраивание изображения в формате Base64 или сохранение на сервере, если включена опция `upload`.
- Настройки размера, отступов и уровня коррекции ошибок (`L`, `M`, `Q`, `H`).
- Кеширование результатов и очистка неиспользуемых QR-кодов.
- Ручной вызов через `callPlugin`.

## Структура плагина

```text
qrcode/
├── phpqrcode/
│   ├── qrlib.php
│   ├── qrconst.php
│   └── ...
├── tpl/
│   └── qrcode.tpl
├── config.php
└── qrcode.php
```

## Использование в шаблоне новости

Автоматический вывод:

```twig
{% if pluginIsActive('qrcode') %}
    {{ plugin_qrcode }}
{% endif %}
```

Ручной вызов:

```twig
{% if pluginIsActive('qrcode') %}
    {{ callPlugin('qrcode.show', {news_id: news.id}) }}
{% endif %}
```
