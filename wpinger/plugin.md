---
id: "wpinger"
icons: "<i class=\"fa fa-rss fa-3x\" aria-hidden=\"true\"></i>"
name: "Weblog pinger"
version: "0.03"
acts: "admin:mod:news"
file: "wpinger.php"
config: "config.php"
install: "install.php"
type: "plugin"
description: "Weblog pinged extension"
author: "Vitaly A. Ponomarev"
author_uri: "http://ngcms.org/"
minenginebuild: "23b3116"
title: "Weblog pinger"
information: "Плагин для информирования внешних систем об обновлениях на вашем сайте"
preinstall: "no"
---

# =========================================================================== #
# NG CMS // Плагины // Информирование поисковых систем об обновлениях         #
# =========================================================================== #

Плагин позволяет информировать внешние системы (обычно - поисковые сервера)
об обновлениях на вашем сайте.
Для информирования используется стандартная XML-RPC команда weblogUpdates.ping
