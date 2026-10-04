---
id: "robots_editor"
icons: "<i class=\"fa fa-android fa-3x\" aria-hidden=\"true\"></i>"
name: "Robots.txt Editor"
version: "1.5"
file: "robots_editor.php"
config: "config.php"
type: "plugin"
description: "Плагин для редактирования файла robots.txt"
author: "irbees2008"
minenginebuild: "23b3116"
title: "Редактор Robots.txt"
information: "Позволяет управлять файлом robots.txt через админ-панель"
preinstall: "no"
---

# =========================================================================== #
# NG CMS // Плагины // RSS экспрт новостей                                    #
# =========================================================================== #
# Плагин Robots.txt Editor для NGCMS
## 📋 Оглавление
- [Описание](#-описание)
- [Возможности](#-возможности)
- [Установка](#-установка)
- [Использование](#-использование)
- [Настройка прав доступа](#-настройка-прав-доступа)
- [Дополнительные правила](#-дополнительные-правила)
- [Системные разделы](#-системные-разделы)
- [Примеры](#-примеры)
- [Требования](#-требования)
- [Решение проблем](#-решение-проблем)
- [Лицензия](#-лицензия)
## 🚀 Описание
**Robots.txt Editor** - это мощный плагин для NGCMS, который предоставляет удобный визуальный интерфейс для редактирования файла `robots.txt` прямо из админ-панели. Больше не нужно вручную редактировать файлы через FTP - все настройки доступны через интуитивный веб-интерфейс.
## ✨ Возможности
- ✅ **Визуальный редактор** с таблицей чекбоксов
- ✅ **Поддержка 3х User-agent**: Yandex, Googlebot, Все остальные
- ✅ **AI-боты поддержка**: управление поисковыми и обучающими AI-ботами
- ✅ **Текстовое поле** для дополнительных правил
- ✅ **Автоматическое добавление** Sitemap и Host
- ✅ **Предпросмотр** в реальном времени
- ✅ **Автосохранение** при изменении настроек
- ✅ **Резервное копирование** файлов
- ✅ **Поддержка сложных путей** с регулярными выражениями
- ✅ **Детальная отладка** при ошибках
## 📦 Установка
1. **Скачайте плагин** и распакуйте архив
2. **Скопируйте папку** `robots_editor` в директорию:
   ```
   /engine/plugins/
   ```
3. **Активируйте плагин** в админ-панели NGCMS:
   - Перейдите в `Админ-панель → Плагины`
   - Найдите "Robots.txt Editor" в списке
   - Нажмите "Активировать"
4. **Настройте плагин** через конфигурацию
## 🎮 Использование
### Основной интерфейс
1. **Перейдите в настройки плагина**:
   ```
   Админ-панель → Плагины → Robots.txt Editor → Настройка
   ```
2. **Настройте параметры**:
   - Автоматическое добавление карты сайта
   - Права доступа для поисковых систем
   - Дополнительные правила
3. **Сохраните изменения** - файл `robots.txt` обновится автоматически
### Статус файла
Плагин отображает текущий статус файла:
- ✅ Файл существует и доступен для записи
- ⚠️ Файл существует, но недоступен для записи
- ℹ️ Файл не существует (будет создан автоматически)
## ⚙️ Настройка прав доступа
### Таблица правил
Плагин предоставляет таблицу с тремя колонками для каждого User-agent:
| Путь | Описание | Yandex | Googlebot | Все |
|------|----------|---------|-----------|-----|
| `/engine/` | Движок сайта | ☑ | ☑ | ☑ |
| `/uploads/images/` | Изображения | ☑ | ☑ | ☑ |
### Действия с чекбоксами
- **✅ Галочка установлена** → `Allow: /path/`
- **❌ Галочка снята** → `Disallow: /path/`
## 🤖 Управление AI-ботами
### AI Search боты (Поисковые)
Плагин поддерживает управление поисковыми AI-ботами, которые индексируют контент для AI-поиска:
- **OAI-SearchBot** - OpenAI ChatGPT Search
- **PerplexityBot** - Perplexity AI Search
- **Claude-Web** - Anthropic Claude Web
- **Claude-SearchBot** - Anthropic Claude SearchBot
**Настройка**: В админ-панели включите опцию **"Разрешить AI Search ботов"** (по умолчанию ВКЛ)
✅ **Рекомендация**: Включайте эту опцию, чтобы ваш контент появлялся в результатах AI-поиска (ChatGPT, Perplexity, Claude).
### AI Training боты (Обучение моделей)
Плагин позволяет блокировать ботов, которые собирают контент для обучения AI-моделей:
- **GPTBot** - OpenAI GPT обучение
- **ClaudeBot** - Anthropic Claude обучение
- **Google-Extended** - Google AI обучение
- **Meta-ExternalAgent** - Meta AI обучение
- **Bytespider** - ByteDance/TikTok AI
- **anthropic-ai** - Anthropic AI краулер
- **Omgilibot** - Omgili Bot
- **FacebookBot** - Facebook Bot
**Настройка**: В админ-панели включите опцию **"Блокировать AI Training ботов"** (по умолчанию ВКЛ)
✅ **Рекомендация**: Включайте эту опцию для защиты авторских прав на контент и предотвращения использования в обучении AI-моделей без вашего разрешения.
### Различие между Search и Training ботами
| Тип | Назначение | Рекомендация |
|-----|-----------|--------------|
| **AI Search** | Индексация для поиска (ChatGPT Search, Perplexity) | ✅ Разрешить (видимость в AI-поиске) |
| **AI Training** | Сбор данных для обучения моделей | ❌ Блокировать (защита контента) |
### Пример robots.txt с AI-ботами
```robots.txt
User-agent: *
Disallow: /engine/
Disallow: /templates/
Disallow: /admin/
# --- AI Search Bots (разрешены для индексации) ---
User-agent: OAI-SearchBot
Allow: /
Disallow: /engine/
Disallow: /admin/
User-agent: PerplexityBot
Allow: /
Disallow: /engine/
Disallow: /admin/
# --- AI Training Bots (блокированы для обучения) ---
User-agent: GPTBot
Disallow: /
User-agent: ClaudeBot
Disallow: /
User-agent: Google-Extended
Disallow: /
Sitemap: https://ваш-сайт.ru/gsmg.xml
Host: https://ваш-сайт.ru
```
## 📝 Дополнительные правила
### Текстовое поле для кастомных правил
В разделе "Дополнительные правила" можно добавить любые специальные директивы:
```robots.txt
Clean-param: ref /some_dir/
Crawl-delay: 2
User-agent: SpecialBot
Disallow: /private/
Allow: /public/
```
### Поддерживаемые дополнительные директивы:
- **`Clean-param`** - очистка параметров URL
- **`Crawl-delay`** - задержка между запросами
- **`User-agent`** - особые правила для конкретных ботов
- **Любые другие** стандартные директивы robots.txt
## 📁 Системные разделы
### 🗂️ Системные папки
- `/engine/` - Движок сайта NGCMS
- `/templates/` - Шаблоны сайта
- `/uploads/avatars/` - Аватары пользователей
- `/uploads/files/` - Загруженные файлы
- `/uploads/images/thumb/` - Миниатюры изображений
- `/uploads/photos/` - Фотографии пользователей
- `/plugin/` - Плагины (общее)
- `/lib/` - Библиотеки
- `/webstat/` - Веб-статистика
- `/cache/` - Кеш
- `/tmp/` - Временные файлы
- `/admin/` - Админ-панель
### 📄 Контентные разделы
- `/uploads/dsn/` - DSN файлы
- `/uploads/images/$` - Основные изображения
- `/plugin/gsmg/` - Карта сайта GSMG
- `/plugin/sitemap/` - Sitemap
- `/search/` - Поиск по сайту
- `/rss.xml` - RSS лента
- `/login/` - Страница входа
- `/logout/` - Выход из системы
- `/register/` - Регистрация
- `/activate/` - Активация аккаунта
- `/lostpassword/` - Восстановление пароля
- `/profile.html` - Профиль пользователя
- `/users/` - Пользователи
- `/page/` - Статические страницы
- `/*print` - Версия для печати
- `/*xml` - XML файлы
- `/*201*` - Архивные материалы
- `*/page/1$` - Первые страницы пагинации
## 🎯 Примеры
### Пример generated robots.txt
```robots.txt
User-agent: Yandex
Allow: /uploads/dsn/
Disallow: /engine/
Disallow: /templates/
Allow: /uploads/images/$
Disallow: /uploads/avatars/
Allow: /plugin/gsmg/
Disallow: /admin/
User-agent: Googlebot
Allow: /uploads/dsn/
Disallow: /engine/
Allow: /uploads/images/$
Disallow: /uploads/avatars/
Allow: /plugin/gsmg/
Disallow: /admin/
User-agent: *
Allow: /uploads/dsn/
Disallow: /engine/
Allow: /uploads/images/$
Disallow: /uploads/avatars/
Allow: /plugin/gsmg/
Disallow: /admin/
Clean-param: utm_source /news/
Crawl-delay: 2
Sitemap: https://ваш-сайт.ru/gsmg.xml
Host: https://ваш-сайт.ru
```
### Пример дополнительных правил
**Ограничение скорости сканирования:**
```robots.txt
Crawl-delay: 3
```
**Очистка параметров URL:**
```robots.txt
Clean-param: utm_source /news/
Clean-param: ref /products/
Clean-param: sessionid /forum/
```
**Особые правила для конкретного бота:**
```robots.txt
User-agent: SpecialBot
Disallow: /temp/
Allow: /public/files/
Crawl-delay: 5
```
## 📋 Требования
- **NGCMS** версии не ниже 2020731
- **PHP** 7.0 или выше
- **Права на запись** в корневую директорию сайта
## 🔧 Решение проблем
### Файл не сохраняется
1. **Проверьте права доступа**:
   ```bash
   chmod 666 /путь/к/сайту/robots.txt
   chmod 755 /путь/к/сайту/
   ```
2. **Проверьте файл отладки**:
   ```
   /корень_сайта/robots_debug.log
   ```
3. **Создайте файл вручную**:
   ```bash
   touch /путь/к/сайту/robots.txt
   chmod 666 /путь/к/сайту/robots.txt
   ```
### common проблемы
- **Файл создается в engine/** → Исправлено в последней версии
- **Не отображаются изменения** → Очистите кеш браузера
- **Ошибки прав доступа** → Настройте права на директорию
## 📞 Поддержка
При возникновении проблем:
1. Проверьте **файл отладки**: `robots_debug.log`
2. Убедитесь в **правах доступа** к корневой директории
3. Проверьте **ошибки PHP** в логах сервера
4. **Напишите issue** в репозитории плагина
## 📜 Лицензия
MIT License - подробности в файле LICENSE
## 👨‍💻 Автор
Разработано для сообщества NGCMS
## 🔗 Полезные ссылки
- [Официальный сайт NGCMS](https://ngcms.org/)
- [Спецификация robots.txt](https://developers.google.com/search/reference/robots_txt)
- [OpenAI Robots.txt](https://platform.openai.com/docs/bots)
- [Anthropic Claude Bots](https://support.anthropic.com/en/articles/8896518)
- [Perplexity Bot Documentation](https://docs.perplexity.ai/docs/perplexitybot)
- [Google AI Crawlers](https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers)
---
**💡 Совет:** Регулярно проверяйте и обновляйте ваш файл `robots.txt` для оптимальной индексации сайта поисковыми системами и управления доступом AI-ботов!
