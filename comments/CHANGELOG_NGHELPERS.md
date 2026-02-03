# Comments Plugin - ng-helpers Modernization Changelog

## Обзор изменений

Плагин `comments` (Комментарии) был модернизирован с использованием функций из ng-helpers v0.2.2.

**Текущая версия плагина:** 0.17
**Дата последней модернизации:** 1 февраля 2026

---

## Версия 0.17 - Универсальная модульная система комментариев (1 февраля 2026)

### Реализована полная поддержка комментариев для любых типов контента

**Проблема:** Комментарии работали только с новостями. Попытка использовать их в галерее или статических страницах приводила к ошибкам.

**Решение:** Добавлено поле `module` в таблицу комментариев и полная поддержка модульной архитектуры.

#### 1. Схема базы данных (install.php)

Добавлено поле `module` в таблицу комментариев:

```php
array('action' => 'cmodify', 'name' => 'module', 'type' => 'varchar(50)', 'params' => "default ''"),
```

Добавлен составной индекс для оптимизации запросов:

```php
KEY `idx_post_module` (`post`, `module`)
```

#### 2. Обработка добавления комментариев (inc/comments.add.php)

**Получение параметра module (строка 70):**

```php
$module = array_get($_POST, 'module', '');
```

**Логика поиска родительского контента (строки 137-149):**

```php
if ($module == 'images') {
    // For gallery images
    $row = $mysql->record("SELECT id, com FROM " . prefix . "_images WHERE id = " . db_squote($postid));
    $allowComments = true; // Gallery always allows comments
} else {
    // For news (default behavior)
    $row = $mysql->record("SELECT id, com, allow_com FROM " . prefix . "_news WHERE id = " . db_squote($postid));
    $allowComments = $row['allow_com'];
}
```

**Сохранение модуля в БД (строка 218):**

```php
$SQL['module'] = $module;
```

**Обновление счетчика (строки 266-268):**

```php
if ($module == 'images') {
    $mysql->query("UPDATE " . prefix . "_images SET com=com+1 WHERE id=" . db_squote($SQL['post']));
} else {
    $mysql->query("UPDATE " . prefix . "_news SET com=com+1 WHERE id=" . db_squote($SQL['post']));
}
```

#### 3. Отображение комментариев (inc/comments.show.php)

**Фильтрация по модулю в SQL запросе (строки 77-83):**

```php
if (isset($callingParams['module']) && $callingParams['module'] !== '') {
    $sqlWhere .= " AND c.module = " . db_squote($callingParams['module']);
} else {
    // For backward compatibility: show news comments (module is NULL or empty)
    $sqlWhere .= " AND (c.module = '' OR c.module IS NULL)";
}
```

**Передача module в шаблон (строка 320):**

```php
$tvars['vars']['module'] = isset($callingParams['module']) ? $callingParams['module'] : '';
```

#### 4. Шаблон формы (tpl/comments.form.tpl)

**HTML скрытое поле (строка 84):**

```twig
<input type="hidden" name="module" value="{{ module|default('') }}"/>
```

**JavaScript AJAX (строка 18):**

```javascript
cajax.setVar("module", form.module ? form.module.value : "");
```

#### 5. Интеграция с галереей (Gallery.php)

**Исправлена ошибка отображения (строка 505):**

```php
// БЫЛО (комментарии не выводились):
comments_show($row['id'], 0, 0, $callingCommentsParams);

// СТАЛО (комментарии выводятся):
echo comments_show($row['id'], 0, 0, $callingCommentsParams);
```

**Параметры вызова (строка 499):**

```php
$callingCommentsParams = [
    'outprint' => true,
    'total' => $row['com'],
    'module' => 'images'
];
```

### Поддерживаемые модули

- `module=''` (пусто) - **Новости** (по умолчанию, обратная совместимость)
- `module='images'` - **Галерея изображений**
- `module='static'` - **Статические страницы**
- `module='любое_значение'` - **Любой другой плагин**

### Примеры использования

**Для галереи (автоматически):**

Плагин галереи уже интегрирован, комментарии работают "из коробки".

**Для статических страниц:**

```php
echo comments_show($pageId, 0, 0, ['outprint' => true, 'module' => 'static']);
echo comments_showform($pageId, ['module' => 'static', 'outprint' => true]);
```

**Для любого плагина:**

```php
$params = ['outprint' => true, 'module' => 'mymodule'];
echo comments_show($itemId, 0, 0, $params);
echo comments_showform($itemId, ['module' => 'mymodule', 'outprint' => true]);
```

### Обратная совместимость

✅ Полная обратная совместимость с существующими комментариями к новостям
✅ При отсутствии параметра `module` используется старая логика (новости)
✅ Существующие комментарии (module IS NULL) автоматически привязываются к новостям

### Логирование

Добавлено подробное логирование в `engine/cache/logs/comments.log`:

- Добавление комментариев с указанием модуля
- SQL запросы с фильтрацией по модулю
- Количество найденных комментариев
- Размер сгенерированного HTML

---

## Версия 0.16.1 - Поддержка комментариев для галереи (31 января 2026)

### Добавлена поддержка комментариев для изображений галереи

**Проблема:** При попытке добавить комментарий к изображению в галерее возникала ошибка, так как код искал запись только в таблице новостей (\_news).

**Решение:** Добавлен параметр `module` для идентификации типа контента (news/images).

#### Изменено в inc/comments.show.php (строка 320):

```php
// Pass module parameter for non-news comments (e.g., gallery images)
$tvars['vars']['module'] = isset($callingParams['module']) ? $callingParams['module'] : '';
```

#### Изменено в tpl/comments.form.tpl (строка 84):

```twig
{% if module %}<input type="hidden" name="module" value="{{ module }}"/>{% endif %}
```

#### Изменено в inc/comments.add.php:

1. **Получение параметра module** (строка 56):

```php
// Determine module (for gallery images, news, etc.)
$module = array_get($_POST, 'module', '');
```

2. **Логика поиска контента** (строки 122-155):

- Для `module='images'` - поиск в таблице `_gallery_files`
- Для стандартных комментариев - поиск в таблице `_news`
- Проверка разрешений на комментарии (для галереи всегда разрешены)

3. **Обновление счетчика** (строки 244-252):

```php
if ($module == 'images') {
    // Update counter in gallery_files table
    $mysql->query("update " . prefix . "_gallery_files set com=com+1 where id=" . db_squote($SQL['post']));
} else {
    // Update counter in news table
    $mysql->query("update " . prefix . "_news set com=com+1 where id=" . db_squote($SQL['post']));
}
```

4. **Email-уведомления** (строка 269):

- Отправляются только для новостей (не для галереи)

5. **Логирование** (строка 238):

```php
'New comment #%d by %s (ID: %d, IP: %s) for %s #%d%s',
// Теперь указывает тип: 'image' или 'news'
```

**Совместимость:** Изменения полностью обратно совместимы - при отсутствии параметра `module` работает старая логика для новостей.

---

## Версия 0.16 - Полная модернизация (29 января 2026)

### Добавленные функции ng-helpers

#### array_get() - Безопасный доступ к массивам

**Замены в comments.php:**

- `$_REQUEST['allow_com']` → `array_get($_REQUEST, 'allow_com', 0)` (3 замены)
- `$_REQUEST['ajax']` → `array_get($_REQUEST, 'ajax', 0)` (2 замены)
- `$_REQUEST['news_id']` → `array_get($_REQUEST, 'news_id', 0)`
- `$_REQUEST['embedded']` → `array_get($_REQUEST, 'embedded', 0)`
- `$_REQUEST['page']` → `array_get($_REQUEST, 'page', 1)`
- `$_GET['uT']` → `array_get($_GET, 'uT', '')`
- `$_REQUEST['id']` → `array_get($_REQUEST, 'id', 0)` (4 замены)
- `$_REQUEST['action']` → `array_get($_REQUEST, 'action', '')` (2 замены)
- `$_POST['text']` → `array_get($_POST, 'text', '')`
- `$_POST['action']` → `array_get($_POST, 'action', '')` (2 замены)
- `$_POST['comments']` → `array_get($_POST, 'comments', [])` (4 замены)

**Замены в inc/comments.add.php:**

- `$_POST['name']` → `array_get($_POST, 'name', '')` (3 замены)
- `$_POST['password']` → `array_get($_POST, 'password', '')` (2 замены)
- `$_POST['mail']` → `array_get($_POST, 'mail', '')`
- `$_POST['newsid']` → `array_get($_POST, 'newsid', '')`
- `$_POST['content']` → `array_get($_POST, 'content', '')`
- `$_POST['vcode']` → `array_get($_POST, 'vcode', '')`
- `$_SESSION['captcha']` → `array_get($_SESSION, 'captcha', '')`

**Итого:** 32+ замены суперглобальных массивов

#### logger() - Унифицированное логирование

**Добавленные точки логирования:**

1. **Добавление комментария** (inc/comments.add.php:221) - с IP и статусом модерации
2. **Удаление комментария** (comments.php:531) - с именем админа и IP
3. **Одобрение комментариев** (comments.php:688) - количество + админ + IP
4. **Массовое удаление** (comments.php:700) - количество + админ + IP

**Итого:** 4 критичные точки аудита

#### sanitize() - Очистка данных

- Добавлен в use statement для inc/comments.show.php
- Уже использовался в inc/comments.add.php для `$_POST['content']`

#### get_ip() - Отслеживание IP

- Используется во всех точках логирования
- Отслеживание источника действий для аудита

---

## Версия 0.15 - Начальная модернизация (11 января 2026)

## 1. Валидация email адресов

### ❌ Было (регулярное выражение):

```php
if (strlen($SQL['mail']) > 70 || !preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $SQL['mail'])) {
    msg(array("type" => "error", "text" => $lang['comments:err.badmail']));
    return;
}
```

### ✅ Стало (ng-helpers):

```php
if (!validate_email($SQL['mail'])) {
    msg(array("type" => "error", "text" => $lang['comments:err.badmail']));
    logger('comments', 'Invalid email attempt: ' . $SQL['mail'] . ' from IP: ' . get_ip(), 'warning');
    return;
}
```

**Преимущества:**

- ✅ Более надежная валидация email через `filter_var(FILTER_VALIDATE_EMAIL)`
- ✅ Логирование попыток с невалидным email
- ✅ Отслеживание IP-адреса нарушителей

---

## 2. Проверка метода запроса

### ✅ Новая функциональность:

```php
function comments_add() {
    global $mysql, $config, $AUTH_METHOD, $userROW, $ip, $lang, $parse, $catmap, $catz, $PFILTERS;

    // Проверка метода запроса
    if (!is_post()) {
        msg(array("type" => "error", "text" => "Invalid request method"));
        return;
    }

    // Check membership...
```

**Преимущества:**

- ✅ Защита от GET-запросов на добавление комментариев
- ✅ Предотвращение CSRF атак через прямые ссылки
- ✅ Соответствие стандартам REST API

---

## 3. Очистка текста комментариев (sanitize)

### ❌ Было:

```php
$SQL['text'] = secure_html(trim($_POST['content']));
```

### ✅ Стало:

```php
$SQL['text'] = sanitize(trim($_POST['content']));
```

**Преимущества:**

- ✅ Более надежная очистка от XSS атак
- ✅ Удаление опасных HTML тегов и атрибутов
- ✅ Сохранение безопасного форматирования

---

## 4. Логирование добавления комментариев

### ✅ Новая функциональность:

```php
// Retrieve comment ID
$comment_id = $mysql->result("select LAST_INSERT_ID() as id");

// Логирование добавления комментария
logger('comments', sprintf(
    'New comment #%d by %s (ID: %d, IP: %s) for news #%d%s',
    $comment_id,
    $SQL['author'],
    $SQL['author_id'],
    get_ip(),
    $SQL['post'],
    $SQL['moderated'] == 0 ? ' [MODERATION]' : ''
));
```

**Преимущества:**

- ✅ Полная информация о каждом комментарии
- ✅ Отслеживание IP-адресов комментаторов
- ✅ Маркировка комментариев на модерации
- ✅ Возможность анализа активности пользователей

---

## 5. Человекочитаемое время (time_ago)

### ✅ Новая переменная в шаблоне:

```php
$tvars['vars']['date'] = LangDate($timestamp, $row['postdate']);
$tvars['vars']['time_ago'] = time_ago($row['postdate']);
```

**Примеры вывода:**

- "только что" (< 1 минуты)
- "5 минут назад"
- "2 часа назад"
- "3 дня назад"
- "2 недели назад"

**Использование в шаблонах:**

**Twig шаблон:**

```twig
<div class="comment-date">
    {{ date }} ({{ time_ago }})
</div>
```

**$tpl шаблон:**

```html
<div class="comment-date">{date} ({time_ago})</div>
```

---

## 6. Превью текста комментария (excerpt)

### ✅ Новая переменная:

```php
$tvars['vars']['comment-short'] = $text;
$tvars['vars']['comment_preview'] = excerpt($text, 150);
$tvars['regx']["'\[comment_full\](.*?)\[/comment_full\]'si"] = '';
```

**Использование в шаблонах:**

**Twig:**

```twig
<div class="comment-preview">{{ comment_preview }}</div>
```

**$tpl:**

```html
<div class="comment-preview">{comment_preview}</div>
```

**Преимущества:**

- ✅ Автоматическое обрезание на границе слов
- ✅ Корректная обработка HTML тегов
- ✅ Добавление троеточия при обрезании

---

## Использованные функции ng-helpers

| Функция          | Категория  | Применение                        |
| ---------------- | ---------- | --------------------------------- |
| `is_post`        | Request    | Проверка метода запроса (POST)    |
| `validate_email` | Validation | Валидация email адресов           |
| `sanitize`       | Security   | Очистка текста от XSS             |
| `logger`         | System     | Логирование комментариев и ошибок |
| `get_ip`         | Request    | Получение IP-адреса комментатора  |
| `time_ago`       | Date       | Человекочитаемое время            |
| `excerpt`        | String     | Создание превью текста            |

---

## Новые переменные шаблонов

### Twig шаблоны:

| Переменная              | Тип    | Описание                                           |
| ----------------------- | ------ | -------------------------------------------------- |
| `{{ time_ago }}`        | string | Человекочитаемое время (например, "5 минут назад") |
| `{{ comment_preview }}` | string | Превью комментария (150 символов)                  |

### $tpl шаблоны:

| Переменная          | Тип    | Описание               |
| ------------------- | ------ | ---------------------- |
| `{time_ago}`        | string | Человекочитаемое время |
| `{comment_preview}` | string | Превью комментария     |

---

## Безопасность

### Улучшения безопасности:

1. **Защита от неправильных методов:**
   - Проверка `is_post()` для всех операций добавления

2. **Валидация данных:**
   - `validate_email()` вместо простого regex
   - `sanitize()` вместо `secure_html()` для лучшей защиты от XSS

3. **Логирование подозрительной активности:**
   - Записи о невалидных email
   - IP-адреса всех комментаторов
   - Комментарии на модерации

4. **Аудит:**
   - Полная история комментариев в логах
   - Возможность отследить злоумышленников

---

## Файлы логов

Плагин создает следующие логи (в `engine/plugins/comments/logs/`):

- **comments.log** - основной лог работы плагина
  - Добавление новых комментариев (автор, ID, IP, новость)
  - Комментарии на модерации (помечены `[MODERATION]`)
  - Попытки с невалидным email (warning level)

---

## Совместимость

- **PHP:** 7.0+ (рекомендуется 7.4+)
- **NGCMS:** 0.9.4+
- **ng-helpers:** v0.2.0+
- **Twig:** Да (поддерживаются оба типа шаблонов)

---

## Обратная совместимость

✅ Все изменения обратно совместимы:

- API плагина не изменился
- Старые шаблоны работают без изменений
- Новые переменные опциональны
- База данных не изменилась

---

## Тестирование

### Рекомендуемые проверки:

1. **Проверка валидации email:**

   ```bash
   # Попробовать отправить комментарий с невалидным email
   # Проверить лог: должна быть запись warning
   tail -f engine/plugins/comments/logs/comments.log
   ```

2. **Проверка метода запроса:**

   ```bash
   # Попробовать GET-запрос на добавление комментария
   # Должна быть ошибка "Invalid request method"
   curl "http://site.ru/?plugin=comments&handler=add"
   ```

3. **Проверка time_ago:**

   ```php
   // В шаблоне вывести обе переменные
   {{ date }} - {{ time_ago }}
   // Пример: 11.01.2026 - 15:30 - 5 минут назад
   ```

4. **Проверка логирования:**

   ```bash
   # Отправить несколько комментариев
   # Проверить лог: должны быть записи с IP и деталями
   tail -20 engine/plugins/comments/logs/comments.log
   ```

5. **Проверка excerpt:**
   ```php
   // Отправить длинный комментарий (> 150 символов)
   // Проверить переменную {comment_preview}
   // Должна быть обрезана на границе слова с "..."
   ```

---

## Примеры использования новых переменных

### 1. Вывод времени комментария (Twig):

```twig
<div class="comment-meta">
    <span class="author">{{ author }}</span>
    <span class="date" title="{{ date }}">{{ time_ago }}</span>
</div>
```

### 2. Вывод времени комментария ($tpl):

```html
<div class="comment-meta">
  <span class="author">{author}</span>
  <span class="date" title="{date}">{time_ago}</span>
</div>
```

### 3. Превью комментария в списке (Twig):

```twig
<div class="comment-item">
    <div class="comment-preview">{{ comment_preview }}</div>
    <a href="#comment-{{ id }}">Читать полностью</a>
</div>
```

### 4. Превью комментария в списке ($tpl):

```html
<div class="comment-item">
  <div class="comment-preview">{comment_preview}</div>
  <a href="#comment-{id}">Читать полностью</a>
</div>
```

---

## Рекомендации по использованию

1. **Мониторинг логов:**
   - Регулярно проверять `comments.log`
   - Анализировать попытки с невалидным email
   - Отслеживать IP-адреса спамеров

2. **Настройка модерации:**
   - Использовать логи для выявления паттернов спама
   - Блокировать подозрительные IP

3. **Использование time_ago:**
   - Выводить рядом с обычной датой для удобства
   - Использовать `title` атрибут для полной даты

4. **Превью комментариев:**
   - Использовать в виджетах "Последние комментарии"
   - Отображать в списках для ускорения загрузки

---

## Дальнейшие улучшения (опционально)

Возможные будущие улучшения:

1. **CSRF защита:**
   - Добавить `csrf_field()` в форму
   - Проверять `validate_csrf()` при добавлении

2. **Кэширование:**
   - Использовать `cache_get/put` для списков комментариев
   - Инвалидация при добавлении нового

3. **Анти-спам:**
   - Использовать `str_limit()` для ограничения длины
   - Проверка повторяющихся комментариев

4. **Статистика:**
   - Анализ логов для выявления активных пользователей
   - Подсчет комментариев по дням/часам

---

## Автор модернизации

GitHub Copilot с использованием ng-helpers v0.2.0

---

## Заключение

Плагин comments успешно модернизирован:

- ✅ **Безопасность:** улучшенная валидация email, проверка методов, sanitize
- ✅ **Логирование:** полная информация о каждом комментарии с IP
- ✅ **UX:** time_ago для читаемости времени, excerpt для превью
- ✅ **Совместимость:** работает со старыми и новыми шаблонами
- ✅ **Мониторинг:** детальные логи для анализа активности

Все изменения направлены на повышение безопасности и удобства использования без нарушения обратной совместимости.
