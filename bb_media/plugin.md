---
id: "bb_media"
icons: "<i class=\"fa fa-television fa-3x\" aria-hidden=\"true\"></i>"
name: "MEDIA bb code"
version: "0.13"
acts: "static, index, news_short, news_full"
file: "bb_media.php"
config: "config.php"
install: "install.php"
type: "plugin"
description: "BB код [MEDIA]"
author: "Vitaly A. Ponomarev"
minenginebuild: "23b3116"
title: "BB код [MEDIA]"
preinstall: "no"
---
# Поддержка BBCode `[MEDIA]`

Плагин добавляет поддержку BBCode-тега `[media]` для отображения медиафайлов.

## Поддерживаемые плееры

Можно выбрать один из двух плееров.

### JW Player

Для воспроизведения используется JW Player. В документации плагина указаны
следующие поддерживаемые типы:

- **Видео:** FLV7, FLV8, H.264, YouTube.
- **Аудио:** MP3, AAC.
- **Изображения:** JPG, GIF, PNG.

Плагин также может отображать PDF-файлы внутри документа с помощью HTML-элемента
`<object>`. Для просмотра в браузере пользователя должен быть доступен PDF-плагин.

### Video.js

Для воспроизведения используется [Video.js](http://www.videojs.com/).

## Использование BBCode

Тег можно использовать без дополнительных параметров:

```text
[media]URL_медиафайла[/media]
```

Или задать ширину, высоту и изображение предварительного просмотра:

```text
[media width="Ширина" height="Высота" preview="URL_изображения"]URL_медиафайла[/media]
```

Параметры `width`, `height` и `preview` необязательны и могут использоваться
по отдельности. Ширину и высоту можно указывать в пикселях или процентах,
например `90%`. В документации плагина для `preview` указано, что URL должен
начинаться с `http://` и вести на изображение с расширением `.png` или `.jpg`.

Параметры `autoplay`, `loop`, `muted`, `preload` и `controls` принимают значения
`true`, `false` или `auto`.

Для вставки видео с YouTube используйте URL страницы видео в качестве содержимого
тега.

## Примеры

```text
[media width="400" height="200"]http://my.ru/video.mp4[/media]
[media]http://www.youtube.com/watch?v=3pBdrQSsoiI&feature=dir[/media]
```

Пример HTML-разметки для отображения PDF:

```html
<object type="application/pdf" data="URL_файла"></object>
```
