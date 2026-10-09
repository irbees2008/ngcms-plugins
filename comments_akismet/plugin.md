---
id: "comments_akismet"
icons: "<i class=\"fa fa-exclamation-triangle fa-3x\" aria-hidden=\"true\"></i>"
name: "Антиспам на основе Akismet"
version: "0.03"
acts: "comments:add"
file: "antispam.php"
config: "config.php"
type: "plugin"
description: "Фильтрует спам в комментариях"
author: "wget"
author_uri: "http://ngcms.org/forum/profile.php?id=2348"
minenginebuild: "23b3116"
title: "Antispam"
information: "Фильтрует спам в комментариях"
preinstall: "no"
---
####################
# Akismet Antispam #
####################
Как получить API-ключ:
Для сервера rest.akismet.com:
Идем по ссылке https://akismet.com/signup/ и выбираем режим Personal.
Ползунок цены выкручиваем на минимум, получится $0.00/yr. Вводим свои
данные, жмем Continue. На указанную почту придет API-ключ.
