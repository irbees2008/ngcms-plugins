---
id: "ognews"
icons: "<i class=\"fa Example of terminal fa-terminal fa-3x\" aria-hidden=\"true\"></i>"
name: "Open Graph Tags"
version: "0.05"
config: "config.php"
type: "plugin"
description: "ognews example"
author: "Rostunov Sergey,irbees2008"
author_uri: "http://rostunov.com/  https://ngcmshak.ru"
actions:
  - "news; ognews.php"
minenginebuild: "23b3116"
title: "ognews example"
information: "Протокол Open Graph - это разметка, которую вы можете добавить к своим HTML-документам,"
preinstall: "no"
---
# Open Graph для новостей

Open Graph — разметка HTML-документа, которая задаёт сведения о странице и управляет
фрагментом, показываемым при публикации ссылки в социальных сетях и мессенджерах,
например Facebook, LinkedIn, X/Twitter, VKontakte, Одноклассники, Slack, WhatsApp,
Viber и Telegram.

Страница плагина и обсуждение: [ngcms.org](http://ngcms.org/forum/viewtopic.php?id=3794).

## Установка и настройка

1. Скопируйте файлы плагина в `engine/plugins/` вашего сайта.
2. Включите плагин в панели администратора.
3. Откройте `config.php` и задайте максимальную длину метаданных:
   - `og:title` и `twitter:title`;
   - `og:description` и `twitter:description` (краткое описание или полный текст);
   - `article:tag` (ключевые слова).
4. При значении длины `0` обрезка не применяется.

## Настройка шаблона

Откройте `main.tpl` активного шаблона сайта и найдите открывающий тег `<html>`.
В исходном шаблоне он может выглядеть так:

```twig
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang['langcode'] }}" lang="{{ lang['langcode'] }}" dir="ltr">
```

Добавьте атрибут `prefix`:

```twig
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang['langcode'] }}" lang="{{ lang['langcode'] }}" dir="ltr" prefix="og: http://ogp.me/ns#">
```

Атрибут объявляет использование словаря Open Graph.
