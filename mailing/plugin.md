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
# Mailing plugin (NGCMS)

## Возможности

- Рассылки зарегистрированным пользователям.
- Сегментация по группам и статусу (best effort) с ограничением количества получателей.
- Вложения любого типа; ограничения зависят от настроек PHP upload.
- Отписка по ссылке на сайте и заголовок `List-Unsubscribe`.
- Отложенная отправка через очередь.
- Автоматическая рассылка новых новостей при периодическом сканировании.

## Установка

1. Скопируйте папку `mailing/` в `/engine/plugins/mailing/`.
2. Активируйте плагин в админке NGCMS.
3. Откройте настройки: `admin.php?mod=extra-config&plugin=mailing`.
4. Заполните From/SMTP (если PHPMailer доступен).

## CRON

Рекомендуется настроить CRON:

1. Задайте `cron_secret` в настройках.
2. Добавьте задание, запускаемое каждые пять минут:

   ```cron
   */5 * * * * curl -s "https://вашсайт.tld/?mailing_cron=1&secret=СЕКРЕТ" >/dev/null
   ```

Если CRON настроить нельзя, включите обработку по посещениям (`enable_tick`) и задайте шанс запуска.

## Примечания

- Точные имена полей таблиц `users` и `news` могут отличаться в вашей сборке NGCMS. При SQL-ошибках адаптируйте функции `mailing_select_users_for_segment()` и `mailing_autonews_scan_and_queue()` в `lib/queue.php`.
- Iframe в письмах почти всегда блокируется почтовыми клиентами. Для YouTube используйте `{YOUTUBE:URL}` — плагин подставит кликабельную картинку.
