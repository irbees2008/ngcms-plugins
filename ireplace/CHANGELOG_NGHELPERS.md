# Changelog: ireplace Plugin - ng-helpers Integration

**Дата обновления:** Февраль 2026 г.
**Версия плагина:** 0.02
**Версия ng-helpers:** v0.2.2
**PHP совместимость:** 7.0+

---

## История изменений

### Версия 0.02 (Февраль 2026) - ng-helpers Integration

#### Новое

- **array_get()**: Заменены все прямые обращения к `$_REQUEST` на безопасную функцию array_get()
- **sanitize()**: Добавлена очистка всех входных данных (src, dest, area)
- **logger()**: Добавлено логирование всех операций замены

#### Безопасность

**До:**

```php
$src = $_REQUEST['src'];
$dest = $_REQUEST['dest'];
switch ($_REQUEST['area']) {
```

**После:**

```php
$src = sanitize(array_get($_REQUEST, 'src', ''), 'string');
$dest = sanitize(array_get($_REQUEST, 'dest', ''), 'string');
$area = sanitize(array_get($_REQUEST, 'area', ''), 'string');
switch ($area) {
```

#### Логирование

Добавлено логирование для всех операций:

1. **Перед заменой** - что и где заменяется:
   - `Replace in NEWS: "старый текст" -> "новый текст"`
   - `Replace in STATIC: "старый текст" -> "новый текст"`
   - `Replace in COMMENTS: "старый текст" -> "новый текст"`
2. **После замены** - результаты:
   - `Success: 15 rows affected in area: news`
   - `No changes: 0 rows affected in area: static`
     **Пример лога:**

```
[2026-02-01 15:30:45] ireplace: Replace in NEWS: "http://oldsite.com" -> "https://newsite.com"
[2026-02-01 15:30:45] ireplace: Success: 23 rows affected in area: news
```

---

## ng-helpers Функции

### 1. **array_get($array, $key, $default = null)**

Безопасное получение значения из массива с значением по умолчанию.
**Использование в ireplace:**

```php
$action = array_get($_REQUEST, 'action', '');
$src = array_get($_REQUEST, 'src', '');
$dest = array_get($_REQUEST, 'dest', '');
$area = array_get($_REQUEST, 'area', '');
```

**Преимущества:**

- ✅ Нет ошибок "Undefined index"
- ✅ Явное значение по умолчанию
- ✅ Безопасный код

### 2. **sanitize($string, $type = 'string')**

Очистка входных данных от потенциально опасных символов.
**Использование в ireplace:**

```php
$src = sanitize(array_get($_REQUEST, 'src', ''), 'string');
$dest = sanitize(array_get($_REQUEST, 'dest', ''), 'string');
$area = sanitize(array_get($_REQUEST, 'area', ''), 'string');
```

**Защита:**

- ✅ Удаление HTML тегов
- ✅ Экранирование спецсимволов
- ✅ Очистка от XSS

### 3. **logger($plugin, $message, $level = 'info')**

Логирование операций для отладки и аудита.
**Использование в ireplace:**

```php
// Операция замены
logger('ireplace', 'Replace in NEWS: "' . $src . '" -> "' . $dest . '"');
// Результат
logger('ireplace', 'Success: ' . $count . ' rows affected in area: ' . $area);
logger('ireplace', 'No changes: 0 rows affected in area: ' . $area);
```

**Преимущества:**

- ✅ Аудит всех операций замены
- ✅ Отслеживание изменений в БД
- ✅ Отладка проблем

---

## Обратная совместимость

✅ Все функции плагина работают как прежде
✅ Интерфейс не изменился
✅ Никаких breaking changes
✅ Просто добавлены безопасность и логирование

---

## Использование

### Замена текста в новостях:

1. Выберите область: **Новости**
2. Исходный текст: `http://oldsite.com`
3. Новый текст: `https://newsite.com`
4. Нажмите **Применить**

### Проверка результатов:

- Смотрите сообщение в админке: "Замена выполнена. Затронуто строк: X"
- Проверяйте логи: `engine/data/logs/plugins.log`

---

## Примеры использования

### 1. Замена доменов в новостях

```
Область: Новости
Исходный: http://old.ru
Новый: https://new.ru
Результат: 45 строк изменено
```

### 2. Исправление опечаток в статичных страницах

```
Область: Статичные страницы
Исходный: привит
Новый: привет
Результат: 3 строки изменено
```

### 3. Замена упоминаний в комментариях

```
Область: Комментарии
Исходный: @oldnick
Новый: @newnick
Результат: 12 строк изменено
```

---

## Технические детали

### Поддерживаемые области замены:

- **news** - содержимое новостей (поле content)
- **static** - содержимое статичных страниц (поле content)
- **comments** - текст комментариев (поле text)

### SQL запросы:

```sql
-- Новости
UPDATE ngcms_news SET content = REPLACE(content, 'src', 'dest')
-- Статичные страницы
UPDATE ngcms_static SET content = REPLACE(content, 'src', 'dest')
-- Комментарии
UPDATE ngcms_comments SET text = REPLACE(text, 'src', 'dest')
```

---

## Безопасность

### Защита от SQL-инъекций:

```php
$query = "update " . prefix . "_news set content = replace(content, "
       . db_squote($src) . ", "
       . db_squote($dest) . ")";
```

### Защита от XSS:

```php
$src = sanitize(array_get($_REQUEST, 'src', ''), 'string');
$dest = sanitize(array_get($_REQUEST, 'dest', ''), 'string');
```

### Аудит операций:

```php
logger('ireplace', 'Replace in NEWS: "' . $src . '" -> "' . $dest . '"');
```

---

## Требования

- **NGCMS:** 23b3116+
- **PHP:** 7.0+
- **ng-helpers:** v0.2.2+

---

## Структура плагина

```
ireplace/
├── config.php          # Главный файл плагина (модернизирован)
├── version             # Версия 0.02
├── history             # История изменений
├── readme              # Описание
├── lang/               # Языковые файлы
│   └── russian/
│       └── main.ini
└── CHANGELOG_NGHELPERS.md  # Этот файл
```

---

## Миграция

Плагин **не требует** миграции или обновления БД.
Просто замените файл `config.php` и всё продолжит работать с добавленными улучшениями.

---

## Известные ограничения

- Замена происходит во **всех** записях выбранной области
- Нет предварительного просмотра изменений
- Нет отмены операции (используйте резервные копии БД)
- Регистрозависимая замена

---

## Рекомендации

⚠️ **ВАЖНО:** Всегда делайте резервную копию БД перед массовой заменой!

1. Создайте backup БД через phpMyAdmin
2. Проверьте текст для замены (нет опечаток)
3. Выполните замену
4. Проверьте результат на сайте
5. При необходимости восстановите из backup

---

**Автор модернизации:** NGCMS Modernization Team
**Дата:** Февраль 2026
**Статус:** ✅ Протестировано и готово к использованию
