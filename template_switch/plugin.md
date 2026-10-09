---
id: "template_switch"
icons: "<i class=\"fa fa-puzzle-piece fa-3x\" aria-hidden=\"true\"></i>"
name: "Просмотр шаблонов"
version: "0.01"
acts: "core, index"
file: "template_switch.php"
config: "config.php"
type: "plugin"
description: "Переключение шаблонов"
author: "irbees2008"
author_uri: "http://ngcmshak.ru/"
minenginebuild: "3740369"
title: "Поддержка мульти//шаблонов"
information: "Позволяет на одном сайте одновременно использовать разные шаблоны"
preinstall: "no"
---
# Переключение шаблонов (`template_switch`)

Сервисный плагин `template_switch` позволяет использовать разные шаблоны сайта: посетитель выбирает скин в меню либо одному сайту назначаются разные скины на разных доменах.

## Профили

Настройки профиля:

- **Активен** — признак активности профиля.
- **Шаблон** — шаблон сайта, используемый при активации профиля.

Идентификатор профиля в ссылке должен состоять только из латинских букв и цифр. Ссылки:

- ЧПУ: `/plugin/template_switch/?profile=<ID>`.
- Без ЧПУ: `?action=plugin&plugin=template_switch&profile=<ID>`.

## Cookies

Для хранения активного профиля используется cookie `sw_template`. Если cookies отключены, плагин работает только при размещении профилей на разных доменных именах.

## Меню выбора

Меню загружается из `tpl/template_switch.tpl`. Шаблон можно изменить, сохранив функциональность работы с cookies.
