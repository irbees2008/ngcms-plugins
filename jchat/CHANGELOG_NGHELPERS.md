# Changelog: jchat Plugin - ng-helpers Integration

**Дата обновления:** Февраль 2026 г.
**Версия плагина:** 0.11
**Версия ng-helpers:** v0.2.2
**PHP совместимость:** 7.0+

---

## История изменений

### Версия 0.11 (Февраль 2026) - ng-helpers Integration

#### Новое

- **array_get()**: Заменены все прямые обращения к `$_REQUEST` на безопасную функцию array_get()
- **sanitize()**: Добавлена очистка всех входных данных (имя, текст сообщения)
- **logger()**: Добавлено логирование всех операций чата
- **get_ip()**: Заменено использование глобальной переменной $ip на функцию get_ip()

#### Безопасность

**До:**

```php
$name = $_REQUEST['name'];
$text = $_REQUEST['text'];
$postText = substr(secure_html(trim($_REQUEST['text'])), 0, $maxlen);
```

**После:**

```php
$name = sanitize(array_get($_REQUEST, 'name', ''), 'string');
$text = sanitize(array_get($_REQUEST, 'text', ''), 'string');
$postText = substr(trim($text), 0, $maxlen);
$ip = get_ip();
```

#### Логирование

Добавлено логирование для всех операций:
**Просмотр чата:**

```
logger('jchat', 'Show: lastEvent=123, start=0');
```

**Добавление сообщения:**

```
logger('jchat', 'Message added: author=Username, author_id=5, IP=192.168.1.1');
logger('jchat', 'Add failed: No name specified, IP=192.168.1.1');
logger('jchat', 'Add failed: Rate limit exceeded, IP=192.168.1.1');
```

**Удаление сообщения:**

```
logger('jchat', 'Message deleted: id=42, admin=AdminName');
logger('jchat', 'Delete failed: Permission denied, IP=192.168.1.1');
```

**Административные операции:**

```
logger('jchat', 'Purging old messages: deleting 100 records');
logger('jchat', 'Purge completed: 100 messages deleted');
logger('jchat', 'Reload event triggered by admin');
```

---

## ng-helpers Функции

### 1. **array_get($array, $key, $default = null)**

Безопасное получение значения из массива.
**Использование в jchat:**

```php
$lastEvent = intval(array_get($_REQUEST, 'lastEvent', 0));
$start = intval(array_get($_REQUEST, 'start', 0));
$name = sanitize(array_get($_REQUEST, 'name', ''), 'string');
$text = sanitize(array_get($_REQUEST, 'text', ''), 'string');
$id = intval(array_get($_REQUEST, 'id', 0));
```

**Места замены:**

- `plugin_jchat_show()` - параметры запроса
- `plugin_jchat_index()` - start параметр
- `plugin_jchat_add()` - имя и текст сообщения
- `plugin_jchat_del()` - ID сообщения
- `config.php` - административные параметры

### 2. **sanitize($string, $type = 'string')**

Очистка входных данных.
**Использование в jchat:**

```php
$name = sanitize(array_get($_REQUEST, 'name', ''), 'string');
$text = sanitize(array_get($_REQUEST, 'text', ''), 'string');
$SQL['author'] = sanitize(substr(trim($name), 0, 30), 'string');
```

**Защита:**

- ✅ Очистка имени пользователя (гости)
- ✅ Очистка текста сообщения
- ✅ Защита от XSS
- ✅ Защита от SQL-инъекций (с db_squote)

### 3. **logger($plugin, $message, $level = 'info')**

Логирование операций.
**Использование в jchat:**

```php
// Операции просмотра
logger('jchat', 'Show: lastEvent=' . $lastEvent . ', start=' . $start);
// Добавление сообщений
logger('jchat', 'Message added: author=' . $SQL['author'] . ', author_id=' . ($SQL['author_id'] ?? 0) . ', IP=' . $ip);
logger('jchat', 'Add failed: No name specified, IP=' . $ip);
logger('jchat', 'Add failed: Guest not allowed, IP=' . $ip);
logger('jchat', 'Add failed: Rate limit exceeded, IP=' . $ip);
// Удаление сообщений
logger('jchat', 'Message deleted: id=' . $id . ', admin=' . ($userROW['name'] ?? 'unknown'));
logger('jchat', 'Delete failed: Permission denied, IP=' . $ip);
logger('jchat', 'Delete failed: Item not found, id=' . $id . ', admin=' . ($userROW['name'] ?? 'unknown'));
// Администрирование
logger('jchat', 'Purging old messages: deleting ' . $dc . ' records');
logger('jchat', 'Purge completed: ' . $dc . ' messages deleted');
logger('jchat', 'Reload event triggered by admin');
```

### 4. **get_ip()**

Безопасное определение IP адреса.
**Использование в jchat:**

```php
// Заменена глобальная переменная $ip
$ip = get_ip();
// Используется в:
- Rate limit проверке
- Сохранении в БД
- Логировании
```

---

## Обратная совместимость

✅ Все функции чата работают как прежде
✅ AJAX API не изменился
✅ Шаблоны совместимы
✅ Интеграция с jchat_tgnotify сохранена
✅ Никаких breaking changes

---

## Основные возможности плагина

### AJAX Chat

- Обмен сообщениями в реальном времени
- Автообновление через AJAX
- Поддержка аватаров пользователей
- Интеграция с uprofile

### Режимы работы

1. **Panel Mode** - боковая панель на всех страницах
2. **Window Mode** - отдельное окно чата
3. **External Mode** - внешнее окно чата

### Контроль доступа

- **Off (0)** - только авторизованные пользователи
- **Read-Only (1)** - гости могут читать, но не писать
- **Read-Write (2)** - гости могут писать

### Rate Limiting

- Ограничение частоты отправки сообщений
- Настраиваемый интервал (секунды)
- Проверка по IP адресу

### Модерация

- Удаление сообщений (только админы)
- Просмотр IP отправителей
- Очистка старых сообщений
- Принудительная перезагрузка чата

---

## Примеры использования

### 1. Добавление сообщения

**AJAX запрос:**

```javascript
POST /plugin/jchat/add
{
  name: "Guest User",
  text: "Hello world!",
  lastEvent: 0,
  start: 0
}
```

**Лог:**

```
[2026-02-01 16:45:30] jchat: Message added: author=Guest User, author_id=0, IP=192.168.1.100
```

### 2. Удаление сообщения (админ)

**AJAX запрос:**

```javascript
POST /plugin/jchat/del
{
  id: 42,
  lastEvent: 150,
  start: 0
}
```

**Лог:**

```
[2026-02-01 16:46:15] jchat: Message deleted: id=42, admin=AdminName
```

### 3. Ограничение по скорости

**Ситуация:** Пользователь пытается отправить 2 сообщения подряд
**Лог:**

```
[2026-02-01 16:47:00] jchat: Message added: author=User, author_id=5, IP=192.168.1.100
[2026-02-01 16:47:02] jchat: Add failed: Rate limit exceeded, IP=192.168.1.100
```

### 4. Очистка старых сообщений

**Действие:** Админ оставляет последние 50 сообщений
**Лог:**

```
[2026-02-01 16:50:00] jchat: Purging old messages: deleting 250 records
[2026-02-01 16:50:01] jchat: Purge completed: 250 messages deleted
```

---

## Безопасность

### Защита от XSS

```php
// Очистка входных данных
$name = sanitize(array_get($_REQUEST, 'name', ''), 'string');
$text = sanitize(array_get($_REQUEST, 'text', ''), 'string');
```

### Защита от SQL-инъекций

```php
// Использование db_squote для всех SQL параметров
$query = "select id from " . prefix . "_jchat where ip = " . db_squote($ip);
```

### Rate Limiting

```php
// Проверка частоты отправки
$rate_limit = intval(pluginGetVariable('jchat', 'rate_limit'));
if (is_array($mysql->record("select id from " . prefix . "_jchat
    where (ip = " . db_squote($ip) . ")
    and (postdate + " . $rate_limit . ') > ' . time()))) {
    logger('jchat', 'Add failed: Rate limit exceeded, IP=' . $ip);
    // Block request
}
```

### Контроль доступа

```php
// Проверка прав на удаление
if (!is_array($userROW) || ($userROW['status'] > 1)) {
    logger('jchat', 'Delete failed: Permission denied, IP=' . $ip);
    // Deny access
}
```

---

## Структура БД

### Таблица: `{prefix}_jchat`

```sql
CREATE TABLE `ngcms_jchat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chatid` int(11) DEFAULT 1,
  `postdate` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `author_id` int(11) DEFAULT 0,
  `text` text NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chatid` (`chatid`),
  KEY `postdate` (`postdate`)
);
```

### Таблица: `{prefix}_jchat_events`

```sql
CREATE TABLE `ngcms_jchat_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chatid` int(11) DEFAULT 1,
  `postdate` int(11) NOT NULL,
  `type` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
);
```

**Типы событий:**

- `1` - Новое сообщение
- `2` - Удаление сообщения
- `3` - Перезагрузка чата

---

## Интеграция с другими плагинами

### jchat_tgnotify

Уведомления в Telegram при новых сообщениях:

```php
if (function_exists('jchat_tgnotify_send')) {
    jchat_tgnotify_send([
        'author'   => $SQL['author'],
        'text'     => strip_tags($postText),
        'datetime' => date('Y-m-d H:i:s', $SQL['postdate']),
        'ip'       => $ip,
        'url'      => $_SERVER['HTTP_REFERER'] ?? '',
        'is_guest' => !is_array($userROW),
    ]);
}
```

### uprofile

Ссылки на профили пользователей:

```php
if (getPluginStatusActive('uprofile')) {
    $row['profile_link'] = generatePluginLink('uprofile', 'show',
        array('name' => $row['author'], 'id' => $row['author_id']));
}
```

---

## Конфигурация

### Основные настройки

- **access** - Уровень доступа для гостей (0/1/2)
- **rate_limit** - Лимит частоты сообщений (секунды)
- **maxlen** - Максимальная длина сообщения
- **maxwlen** - Максимальная длина слова
- **format_time** - Формат времени (%H:%M)
- **format_date** - Формат даты (%d.%m.%Y %H:%M)

### Panel Mode

- **enable_panel** - Включить боковую панель
- **refresh** - Интервал обновления (секунды)
- **history** - Количество сообщений в истории
- **maxidle** - Максимальное время простоя (секунды)
- **order** - Порядок сообщений (0=старые→новые, 1=новые→старые)

### Window Mode

- **enable_win** - Включить оконный режим
- **win_mode** - Тип окна (0=внутреннее, 1=внешнее)
- **win_refresh** - Интервал обновления
- **win_history** - Количество сообщений
- **win_maxidle** - Максимальное время простоя
- **win_order** - Порядок сообщений

---

## Требования

- **NGCMS:** 23b3116+
- **PHP:** 7.0+
- **MySQL:** 5.5+
- **ng-helpers:** v0.2.2+
- **JavaScript:** Включен в браузере

---

## Структура плагина

```
jchat/
├── jchat.php                    # Главный файл (модернизирован)
├── config.php                   # Конфигурация (модернизирован)
├── install.php                  # Установка
├── deinstall.php                # Удаление
├── version                      # Версия 0.11
├── history                      # История изменений
├── readme                       # Описание
├── AVATARS_README.md            # Документация по аватарам
├── CHANGELOG_NGHELPERS.md       # Этот файл
├── lang/                        # Языковые файлы
│   └── russian/
│       ├── config.ini
│       └── main.ini
└── tpl/                         # Шаблоны
    ├── jchat.tpl                # Боковая панель
    ├── jchat.main.tpl           # Полное окно
    └── jchat.self.tpl           # Внешнее окно
```

---

## Миграция

Плагин **не требует** миграции или обновления БД.
Просто замените файлы `jchat.php` и `config.php`, и всё продолжит работать с добавленными улучшениями.

---

## Отладка

### Просмотр логов

```bash
tail -f engine/data/logs/plugins.log | grep jchat
```

### Типичные логи

```
[2026-02-01 16:00:00] jchat: Show: lastEvent=0, start=0
[2026-02-01 16:00:15] jchat: Message added: author=TestUser, author_id=1, IP=127.0.0.1
[2026-02-01 16:00:16] jchat: Add failed: Rate limit exceeded, IP=127.0.0.1
[2026-02-01 16:05:30] jchat: Message deleted: id=123, admin=Admin
[2026-02-01 16:10:00] jchat: Purging old messages: deleting 100 records
[2026-02-01 16:10:01] jchat: Purge completed: 100 messages deleted
```

---

## Известные ограничения

- Один глобальный чат (chatid всегда = 1)
- Нет приватных сообщений
- Нет истории редактирования
- Нет markdown форматирования
- Максимум 500 символов на сообщение

---

## Рекомендации

✅ Настройте rate_limit (рекомендуется 5-10 секунд)
✅ Регулярно очищайте старые сообщения
✅ Мониторьте логи на спам
✅ Используйте CAPTCHA для гостей (через внешние плагины)
✅ Настройте Telegram уведомления (плагин jchat_tgnotify)

---

**Автор модернизации:** NGCMS Modernization Team
**Дата:** Февраль 2026
**Статус:** ✅ Протестировано и готово к использованию
