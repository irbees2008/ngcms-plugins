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

# =========================================================================== #
# NG CMS // Плагины // BitTorrent трекер                                      #
# =========================================================================== #

Плагин позволяет превратить NGCMS в BitTorrent трекер.

!!!                                                                         !!!
!!! Внимание, это облегченная версия трекера и она не оптимизирована для    !!!
!!! работы под высокими нагрузками.                                         !!!
!!!                                                                         !!!

Для запуска трекера вам необходимо:
1. Разрешить загрузку файлов с расширением .torrent:
   настройки => настройки системы => файлы => расширения файлов
2. Оповестить пользователей об URL'е для трекера ( URL вашего трекера
   можно увидеть в настройках плагина
