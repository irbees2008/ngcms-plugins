---
id: "gallery"
icons: "<i class=\"fa fa-file-image-o fa-3x\" aria-hidden=\"true\"></i>"
name: "Gallery Manager (modernized with ng-helpers v0.2.2)"
version: "0.2.1"
acts: "index, ppages"
file: "gallery.php"
config: "config.php"
install: "install.php"
deinstall: "deinstall.php"
type: "plugin"
description: "Менеджер галерей с ng-helpers v0.2.2: безопасный доступ к массивам через array_get(), обновленный формат логирования logger(message, level, file), оптимизация БД, интеграция с комментариями."
author: "rusiq"
author_uri: "https://github.com/russsiq"
library:
  - "library; src/Gallery.php"
minenginebuild: "23b3116"
title: "Gallery Manager"
information: "Модернизированный плагин управления галереями: 30+ замен $_REQUEST/$_POST на array_get(), защита от undefined index, структурированное логирование, оптимизированные запросы, автоматическое подключение CSS/JS скинов."
preinstall: "no"
---
# Менеджер галерей

Плагин управляет галереями, загруженными штатными средствами NG CMS, и позволяет
создавать меню и виджеты для отображения изображений на сайте.

## Модернизация с ng-helpers v0.2.2

Обновление от 31 января 2026 года:

- `array_get()` обеспечивает безопасное чтение `$_REQUEST` и `$_POST` (более 30 замен).
- `logger(message, level, file)` записывает структурированные журналы.
- Добавлена защита от предупреждений `Undefined index` и улучшена проверка параметров.
- Оптимизированы SQL-запросы: используются `JOIN` вместо подзапросов.
- Используются функции ng-helpers для кеширования, логирования и форматирования.
- CSS и JavaScript скинов подключаются автоматически.
- При удалении плагина корректно удаляются связанные комментарии.
- Для уведомлений используется `notify()`, улучшена обработка ошибок.

## Особенности

- Есть собственная страница плагина.
- Поддерживаются хлебные крошки через плагин `breadcrumbs`.
- Кеш сбрасывается по заданному интервалу и при изменении настроек.
- После изменения ЧПУ или шаблонов очистите кеш кнопкой «Очистить кеш» в настройках.

## Вывод на сайте

- Переменная `{{ plugin_gallery_category }}` в `main.tpl` выводит список категорий.
- Переменная `{{ plugin_gallery_NAME }}` выводит виджет с заголовком `NAME`,
  заданным в настройках плагина.

## Шаблоны и переменные

### `category.tpl` — список категорий

| Переменная | Описание |
| --- | --- |
| `{{ url_tpl }}` | URL каталога шаблонов. |
| `{{ url_main }}` | URL главной страницы плагина. |
| `{{ galleries }}` | Массив галерей. |
| `{{ gallery.id }}` | ID галереи в БД. |
| `{{ gallery.name }}` | Имя галереи. |
| `{{ gallery.title }}` | Название галереи. |
| `{{ gallery.url }}` | Ссылка на галерею. |
| `{{ gallery.icon }}` | Ссылка на изображение галереи. |
| `{{ gallery.icon_thumb }}` | Ссылка на миниатюру; если её нет, равна `{{ gallery.icon }}`. |
| `{{ gallery.count }}` | Количество изображений в галерее. |

### `page_index.tpl` — главная страница со списком галерей

| Переменная | Описание |
| --- | --- |
| `{{ plugin_title }}` | Заголовок плагина (по умолчанию «Галерея»). |
| `{{ url_tpl }}` | URL каталога шаблонов. |
| `{{ url_main }}` | URL этой страницы плагина. |
| `{{ galleries }}` | Массив галерей с полями `gallery.id`, `gallery.name`, `gallery.title`, `gallery.url`, `gallery.icon`, `gallery.icon_thumb`, `gallery.count`, `gallery.description` и `gallery.keywords`. |
| `{{ pagesss }}` | Постраничная навигация. |

### `page_gallery.tpl` — страница галереи

| Переменная | Описание |
| --- | --- |
| `{{ plugin_title }}` | Заголовок плагина (по умолчанию «Галерея»). |
| `{{ url_tpl }}` | URL каталога шаблонов. |
| `{{ url_main }}` | URL главной страницы плагина. |
| `{{ gallery }}` | Текущая галерея с полями `url`, `title`, `description` и `keywords`. |
| `{{ images }}` | Массив изображений. Поля элемента `image`: `id`, `name`, `url`, `src`, `src_thumb`, `description`, `com`, `views`, `width`, `height`, `size`. |
| `{{ pagesss }}` | Постраничная навигация. |

### `page_image.tpl` — страница изображения

| Переменная | Описание |
| --- | --- |
| `{{ plugin_title }}` | Заголовок плагина (по умолчанию «Галерея»). |
| `{{ url_tpl }}` | URL каталога шаблонов. |
| `{{ url_main }}` | URL главной страницы плагина. |
| `{{ gallery }}` | Галерея изображения с полями `url`, `title`, `description` и `keywords`. |
| `{{ image }}` | Изображение с полями `id`, `src`, `src_thumb`, `name`, `description`, `com`, `views`, `width`, `height` и `size`. |
| `{{ prevlink }}` | Ссылка на предыдущее изображение, если оно есть. |
| `{{ gallerylink }}` | Ссылка на галерею. |
| `{{ nextlink }}` | Ссылка на следующее изображение, если оно есть. |
| `{{ plugin_comments }}` | Вывод плагина комментариев. |

### `widget.tpl` — шаблон виджета

Доступны переменные `{{ url_tpl }}`, `{{ url_main }}`, `{{ widget_title }}` и
`{{ images }}`. Элементы массива `images` содержат `image.id`, `image.title`,
`image.com`, `image.views`, `image.src`, `image.src_thumb`, `image.url`,
`image.description`, `image.gallery_url` и `image.gallery_title`.
