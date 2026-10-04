---
id: "mailing"
icons: "<i class=\"fa fa-envelope fa-3x\" aria-hidden=\"true\"></i>"
name: "Email-рассылки"
version: "2.0.0"
acts: "core, index, admin, admin:mod:news, twig"
file: "mailing.php"
config: "config.php"
install: "install.php"
deinstall: "uninstall.php"
type: "plugin"
description: "Плагин для массовых email-рассылок с поддержкой сегментации, вложений, отложенной отправки и авто-рассылки новых новостей. Требует PHP 8.1+"
author: "NGCMS Community"
author_uri: "https://ngcms.org/"
minenginebuild: "23b3116"
title: "Email Рассылки"
information: "Профессиональная система email-рассылок с очередью, отпиской, вложениями и интеграцией с Twig"
preinstall: "no"
---

Mailing plugin (NGCMS)
=====================

Функции:
- Рассылки по зарегистрированным пользователям
- Сегментация (по группам/status, best effort) + лимит
- Вложения (любой тип файла, ограничения = настройки PHP upload)
- Отписка (ссылка на сайте) + List-Unsubscribe header
- Отложенная отправка (через очередь)
- Авто-рассылка новых новостей (через периодический scan)

Установка:
1) Скопируйте папку `mailing/` в:  /engine/plugins/mailing/
2) Активируйте плагин в админке NGCMS.
3) Откройте настройки: admin.php?mod=extra-config&plugin=mailing
4) Заполните From/SMTP (если PHPMailer доступен).

CRON (рекомендуется):
- В настройках задайте `cron_secret`.
- Добавьте в cron:
  */5 * * * * curl -s "https://вашсайт.tld/?mailing_cron=1&secret=СЕКРЕТ" >/dev/null

Если cron нельзя:
- Включите "обработку по посещениям" (enable_tick) и задайте шанс запуска.

Важно:
- Точные имена полей таблиц users/news могут отличаться в вашей сборке NGCMS.
  Если видите SQL-ошибки - адаптируйте функции в lib/queue.php:
  mailing_select_users_for_segment(), mailing_autonews_scan_and_queue().
- Iframe в письмах почти всегда режется почтовиками. Для YouTube используйте {YOUTUBE:URL}
  — плагин подставит кликабельную картинку.
