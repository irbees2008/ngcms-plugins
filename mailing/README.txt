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

