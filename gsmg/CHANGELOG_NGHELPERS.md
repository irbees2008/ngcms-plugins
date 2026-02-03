# GSMG Plugin - ng-helpers Modernization Changelog

## Обзор изменений

Плагин `gsmg` (Google SiteMap Generator) был модернизирован с использованием функций из ng-helpers v0.2.2.

**Текущая версия плагина:** 0.14
**Дата модернизации:** 1 февраля 2026

---

## Версия 0.14 - Модернизация с ng-helpers (1 февраля 2026)

### Добавленные функции ng-helpers

#### 1. array_get() - Безопасный доступ к массивам

**config.php:**

```php
// Было:
if ($_REQUEST['action'] == 'commit') {

// Стало:
if (array_get($_REQUEST, 'action', '') == 'commit') {
```

**Преимущества:**

- ✅ Защита от Notice/Warning при отсутствии ключа
- ✅ Безопасное значение по умолчанию
- ✅ Единообразный подход к работе с массивами

#### 2. logger() - Унифицированное логирование

Добавлено детальное логирование всех этапов генерации sitemap в файл `engine/cache/logs/gsmg.log`.

**gsmg.php - Логирование событий:**

1. **Начало генерации** (строка ~15):

```php
logger('gsmg', sprintf('Sitemap generation started from IP: %s', get_ip()), 'info', 'gsmg.log');
```

2. **Использование кэша** (строка ~23):

```php
logger('gsmg', 'Sitemap served from cache', 'info', 'gsmg.log');
```

3. **Сохранение частей sitemap** (строка ~163):

```php
if (file_put_contents($filePath, $content) !== false) {
    logger('gsmg', sprintf('Sitemap part saved: %s (URLs: %d)', $fileName, substr_count($content, '<url>')), 'info', 'gsmg.log');
} else {
    logger('gsmg', sprintf('Failed to save sitemap part: %s', $fileName), 'error', 'gsmg.log');
}
```

4. **Кэширование индекса** (строка ~178):

```php
logger('gsmg', 'Sitemap index cached successfully', 'info', 'gsmg.log');
```

5. **Завершение генерации** (строка ~182):

```php
logger('gsmg', sprintf('Sitemap generation completed. Total parts: %d', count($sitemapParts)), 'info', 'gsmg.log');
```

**Типы логируемых событий:**

- `info` - информационные сообщения о процессе генерации
- `error` - ошибки при сохранении файлов

**Логируемая информация:**

- IP-адрес запроса на генерацию
- Использование кэша
- Количество сохраненных URL в каждой части
- Ошибки записи файлов
- Общее количество частей sitemap

#### 3. get_ip() - Отслеживание IP-адресов

Используется для отслеживания источника запросов на генерацию sitemap:

```php
logger('gsmg', sprintf('Sitemap generation started from IP: %s', get_ip()), 'info', 'gsmg.log');
```

**Преимущества:**

- ✅ Аудит запросов на генерацию
- ✅ Выявление потенциальных злоупотреблений
- ✅ Анализ источников трафика

---

## Структура изменений

### Файл gsmg.php

**Добавлено:**

- Use-директивы для ng-helpers функций (строки 5-8)
- Логирование начала генерации (строка ~15)
- Логирование использования кэша (строка ~23)
- Логирование сохранения файлов с проверкой ошибок (строки ~163-167)
- Логирование кэширования (строка ~178)
- Логирование завершения (строка ~182)

**До модернизации:**

```php
<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

register_plugin_page('gsmg', '', 'plugin_gsmg_screen', 0);
```

**После модернизации:**

```php
<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Import ng-helpers functions
use function Plugins\array_get;
use function Plugins\logger;
use function Plugins\sanitize;
use function Plugins\get_ip;

register_plugin_page('gsmg', '', 'plugin_gsmg_screen', 0);
```

### Файл config.php

**Изменения:**

1. Добавлено `use function Plugins\array_get;`
2. Замена `$_REQUEST['action']` на `array_get($_REQUEST, 'action', '')`

---

## Примеры логов

### Успешная генерация:

```
[2026-02-01 10:30:15] [INFO] Sitemap generation started from IP: 192.168.1.100
[2026-02-01 10:30:16] [INFO] Sitemap part saved: sitemap_part0.xml (URLs: 1523)
[2026-02-01 10:30:16] [INFO] Sitemap index cached successfully
[2026-02-01 10:30:16] [INFO] Sitemap generation completed. Total parts: 1
```

### Генерация с кэшем:

```
[2026-02-01 10:35:20] [INFO] Sitemap generation started from IP: 192.168.1.100
[2026-02-01 10:35:20] [INFO] Sitemap served from cache
```

### Генерация с ошибкой:

```
[2026-02-01 10:40:10] [INFO] Sitemap generation started from IP: 192.168.1.100
[2026-02-01 10:40:11] [ERROR] Failed to save sitemap part: sitemap_part0.xml
[2026-02-01 10:40:11] [INFO] Sitemap generation completed. Total parts: 1
```

### Большой сайт (несколько частей):

```
[2026-02-01 11:00:00] [INFO] Sitemap generation started from IP: 192.168.1.100
[2026-02-01 11:00:03] [INFO] Sitemap part saved: sitemap_part0.xml (URLs: 50000)
[2026-02-01 11:00:05] [INFO] Sitemap part saved: sitemap_part1.xml (URLs: 50000)
[2026-02-01 11:00:06] [INFO] Sitemap part saved: sitemap_part2.xml (URLs: 15234)
[2026-02-01 11:00:06] [INFO] Sitemap index cached successfully
[2026-02-01 11:00:06] [INFO] Sitemap generation completed. Total parts: 3
```

---

## Использованные функции ng-helpers

| Функция     | Категория | Применение                             |
| ----------- | --------- | -------------------------------------- |
| `array_get` | Array     | Безопасный доступ к $\_REQUEST         |
| `logger`    | System    | Логирование процесса генерации sitemap |
| `get_ip`    | Request   | Отслеживание IP-адреса запросов        |

---

## Преимущества модернизации

1. **Прозрачность** - полное логирование всех этапов генерации
2. **Отладка** - легко определить проблемы через логи
3. **Аудит** - отслеживание кто и когда генерировал sitemap
4. **Безопасность** - защищенный доступ к суперглобальным массивам
5. **Мониторинг** - анализ производительности генерации
6. **Ошибки** - фиксация проблем с записью файлов

---

## Обратная совместимость

✅ **Полная обратная совместимость**
✅ Работа с существующими настройками
✅ Совместимость со старыми кэш-файлами
✅ Все существующие функции без изменений

---

## Требования

- **ng-helpers** >= 0.2.2
- **PHP** >= 5.6 (для use function)
- **NGCMS** >= MinEngineBuild 23b3116

---

## Файлы изменены

- `gsmg.php` - основной файл плагина
- `config.php` - файл конфигурации
- `version` - обновлена версия до 0.14
- `history` - добавлена запись о версии 0.14
- `CHANGELOG_NGHELPERS.md` - этот файл (новый)

---

## Дальнейшие улучшения

Потенциальные улучшения для будущих версий:

- [ ] Добавить валидацию сгенерированного XML
- [ ] Добавить статистику времени генерации
- [ ] Добавить уведомления об ошибках через email
- [ ] Добавить сжатие XML файлов (gzip)
- [ ] Добавить автоматическую отправку в Google Search Console

---

**Автор модернизации:** GitHub Copilot
**Дата:** 1 февраля 2026
