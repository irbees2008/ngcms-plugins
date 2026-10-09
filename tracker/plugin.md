---
id: "tracker"
icons: "<i class=\"fa fa-magnet fa-3x\" aria-hidden=\"true\"></i>"
name: "BitTorrent tracker"
version: "0.04"
acts: "ppages, news_full, news:show:one, admin:mod:news"
file: "announce.php"
config: "config.php"
install: "install.php"
deinstall: "deinstall.php"
type: "plugin"
description: "BitTorrent треккер"
author: "Vitaly A. Ponomarev"
author_uri: "http://ngcms.org/"
minenginebuild: "23b3116"
title: "BitTorrent трекер"
information: "Позволяет превратить ваш сайт в BitTorrent трекер"
preinstall: "no"
---
# BitTorrent-трекер

Плагин позволяет использовать NGCMS в качестве BitTorrent-трекера.

> **Внимание:** это облегчённая версия трекера, не оптимизированная для высоких нагрузок.

## Запуск

1. Разрешите загрузку файлов с расширением `.torrent` в настройках системы: «Настройки» → «Настройки системы» → «Файлы» → «Расширения файлов».
2. Сообщите пользователям URL трекера. Его можно посмотреть в настройках плагина.
