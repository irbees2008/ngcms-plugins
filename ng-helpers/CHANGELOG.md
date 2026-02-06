# Changelog for ng-helpers
## [0.2.2] - 2026-01-29
### Added
- **csrf_field()** - генерация скрытого поля с CSRF токеном для защиты форм
- **csrf_token()** - получение CSRF токена из сессии
- **validate_csrf()** - проверка CSRF токена из POST запроса с использованием hash_equals
- **logger()** - запись сообщений в лог-файлы с уровнями важности (info, warning, error, debug)
  - Автоматическое создание директории engine/logs
  - Формат: [дата время] [уровень] сообщение
- **is_post()** - проверка метода запроса POST
- **is_get()** - проверка метода запроса GET
- **is_ajax()** - определение AJAX запросов через заголовок X-Requested-With
- **validate_phone()** - валидация номеров телефонов (10-15 цифр)
### Compatibility
- Добавлена полная поддержка для плагина feedback с модернизацией по CHANGELOG_NGHELPERS.md
- Все функции используют пространство имён Plugins\ для избежания конфликтов
### Security
- 🔒 CSRF защита - предотвращение подделки межсайтовых запросов
- 📝 Логирование попыток взлома и подозрительной активности
- 🛡️ Безопасное сравнение токенов через hash_equals
---
## [0.2.1] - 2026-01-16
### Added
- **formatMoney()** - функция для форматирования денежных сумм
  - Поддержка настраиваемых разделителей целой и дробной части
  - Поддержка настраиваемого разделителя тысяч
  - По умолчанию: 2 знака после запятой, пробел между тысячами
  - Примеры: `formatMoney(1234.56)` => `"1 234.56"`
### Fixed
- Интеграция с плагином basket - устранена ошибка `Call to undefined function formatMoney()`
### Documentation
- Добавлена документация по функции formatMoney() в README.md
- Обновлен список доступных функций
---
## [0.2.0] - 2026-01-11
### Added
- 60+ новых вспомогательных функций для разработки плагинов
- Валидация данных (email, URL, телефон, даты)
- Расширенная работа с массивами (pluck, flatten, only, except)
- Преобразования строк (camelCase, snake_case, before/after)
- Работа с датами ("5 минут назад", форматирование)
- HTTP хелперы (AJAX, mobile detection)
- Debug инструменты (dump, logger, benchmark)
- Безопасность (хеширование, шифрование)
- HTML хелперы (link_to, image_tag, mailto)
- Работа с числами (проценты, локализация, ограничения)
# Новые переменные шаблонов в модернизированных плагинах
## 📋 Краткая справка
### 1. **lastcomments** - Последние комментарии
**Система шаблонов:** Twig
**Новая переменная:** `{time_ago}` / `{{ entry.time_ago }}`
**Использование:**
**Старый синтаксис (конвертируется автоматически):**
```twig
<span class="comment-time">{time_ago}</span>
```
**Twig синтаксис:**
```twig
<span class="comment-time">{{ entry.time_ago }}</span>
```
**Значения:**
- "только что"
- "5 минут назад"
- "2 часа назад"
- "вчера"
- "3 дня назад"
**Конверсия шаблона:**
```php
$conversionConfig = array(
    '{time_ago}' => '{{ entry.time_ago }}',
    // автоматически в lastcomments.php
);
```
---
### 2. **rating** - Рейтинги
**Система шаблонов:** Старая ($tpl), не Twig
**Новая переменная:** `{rating_percent}`
**Использование:**
```html
<div class="rating-bar">
  <div class="rating-fill" style="width: {rating_percent}%"></div>
</div>
<span>Рейтинг: {rating_percent}% ({votes} голосов)</span>
```
**Доступные переменные:**
```html
{rating}
<!-- Средний рейтинг: 4 -->
{rating_percent}
<!-- Процент: 80% (НОВОЕ!) -->
{votes}
<!-- Количество голосов: 25 -->
```
**Примечание:** Переменные доступны напрямую через `{переменная}` без Twig синтаксиса, так как плагин использует старую систему шаблонов.
---
### 3. **similar** - Похожие новости
**Система шаблонов:** Старая ($tpl), не Twig
**Новые переменные:** Нет
**Изменения:** Все улучшения касаются внутренней логики (кэширование, array_pluck, логирование). Переменные шаблонов остались без изменений.
---
## 🔧 Технические детали
### Twig конверсия (lastcomments)
**Как работает:**
1. Старый синтаксис `{переменная}` в шаблоне
2. Система конверсии преобразует в `{{ entry.переменная }}`
3. Twig рендерит шаблон
**Файл:** `lastcomments.php`, строки ~213-232
```php
$conversionConfig = array(
    '{tpl_url}'       => '{{ tpl_url }}',
    '{link}'          => '{{ entry.link }}',
    '{date}'          => '{{ entry.date }}',
    '{time_ago}'      => '{{ entry.time_ago }}',  // <-- ДОБАВЛЕНО
    '{author}'        => '{{ entry.author }}',
    // ...
);
$twigLoader->setConversion($tpath[$tpl_prefix . 'lastcomments'] . $tpl_prefix . "lastcomments" . '.tpl', $conversionConfig, $conversionConfigRegex);
```
### Старая система (rating, similar)
**Как работает:**
1. Переменная устанавливается в `$tvars['vars']['переменная']`
2. Передается в шаблон через `$tpl->vars()`
3. Доступна напрямую как `{переменная}`
**Пример (rating.php):**
```php
$tvars['vars']['rating'] = round(($data['rating'] / $data['votes']), 0);
$tvars['vars']['rating_percent'] = percentage($data['rating'], $data['votes'] * 5);
$tvars['vars']['votes'] = $data['votes'];
$tpl->vars('rating', $tvars);
```
---
## 📝 Примеры использования
### lastcomments + time_ago
**Простой вариант:**
```html
<div class="comment-meta">{time_ago}</div>
```
**С tooltip:**
```html
<span class="time-ago" title="{date}">{time_ago}</span>
```
**Twig условие:**
```twig
{% if entry.time_ago %}
    <span>{{ entry.time_ago }}</span>
{% else %}
    <span>{{ entry.date }}</span>
{% endif %}
```
### rating + rating_percent
**Прогресс-бар:**
```html
<div class="rating-progress">
  <div class="rating-bar" style="width: {rating_percent}%"></div>
</div>
<div class="rating-text">{rating}/5 ({votes} голосов)</div>
```
**Цветной badge:**
```html
<span class="badge" style="background: hsl({rating_percent}, 70%, 50%)">
  {rating_percent}%
</span>
```
**Звезды + процент:**
```html
<div class="rating-widget">
  <div class="stars rating-{rating}">★★★★★</div>
  <div class="percent">{rating_percent}%</div>
</div>
```
---
## ✅ Checklist обновления шаблонов
### lastcomments
- [ ] Открыть `tpl/entries.tpl` или `tpl/pp_entries.tpl`
- [ ] Добавить `{time_ago}` или `{{ entry.time_ago }}`
- [ ] Сохранить и проверить отображение
- [ ] Опционально: добавить tooltip с полной датой
### rating
- [ ] Открыть `tpl/skins/basic/rating.tpl` (или свой скин)
- [ ] Добавить `{rating_percent}` в нужное место
- [ ] Использовать для прогресс-бара или процентов
- [ ] Сохранить и проверить
### similar
- [ ] Ничего не требуется
- [ ] Все работает автоматически
- [ ] Кэширование включается само
---
## 🎨 CSS примеры
### Стили для time_ago (lastcomments)
```css
.time-ago {
  color: #888;
  font-size: 0.9em;
  font-style: italic;
}
.comment-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
```
### Стили для rating_percent (rating)
```css
.rating-progress {
  width: 100%;
  height: 20px;
  background: #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
}
.rating-bar {
  height: 100%;
  background: linear-gradient(90deg, #ff6b6b, #4ecdc4);
  transition: width 0.3s ease;
}
.rating-text {
  margin-top: 5px;
  font-size: 0.9em;
  color: #666;
}
```
---
## 🔍 Отладка
### Проверка Twig конверсии (lastcomments)
1. Откройте шаблон `tpl/entries.tpl`
2. Используйте `{time_ago}`
3. Проверьте в браузере - должна отобразиться переменная
4. Если пусто - проверьте массив `$conversionConfig` в PHP
### Проверка старой системы (rating)
1. Откройте `rating.tpl`
2. Добавьте `{rating_percent}`
3. Если пусто - проверьте `$tvars['vars']['rating_percent']` в PHP
4. Используйте `var_dump($tvars)` для отладки
---
## 📚 Документация
Полная документация для каждого плагина:
- `breadcrumbs/CHANGELOG_NGHELPERS.md`
- `feedback/CHANGELOG_NGHELPERS.md`
- `lastcomments/CHANGELOG_NGHELPERS.md`
- `similar/CHANGELOG_NGHELPERS.md`
- `rating/CHANGELOG_NGHELPERS.md`
# Список плагинов для модификации с использованием ng-helpers v0.2.0
## ✅ Модернизированные плагины (Готово!)
| №   | Плагин           | Новые функции                                                                              | Новые переменные шаблонов             | Система |
| --- | ---------------- | ------------------------------------------------------------------------------------------ | ------------------------------------- | ------- |
| 1   | **breadcrumbs**  | `cache_get`, `cache_put`                                                                   | -                                     | Twig    |
| 2   | **feedback**     | `validate_email`, `csrf_field`, `validate_csrf`, `sanitize`, `logger`, `get_ip`, `is_post` | -                                     | Twig    |
| 3   | **lastcomments** | `cache_get`, `cache_put`, `time_ago`, `excerpt`, `clamp`                                   | `{time_ago}` → `{{ entry.time_ago }}` | Twig    |
| 4   | **similar**      | `array_pluck`, `cache_get`, `cache_put`, `cache_forget`, `logger`                          | -                                     | $tpl    |
| 5   | **rating**       | `is_ajax`, `get_ip`, `percentage`, `clamp`, `logger`, `cache_forget`                       | `{rating_percent}`                    | $tpl    |
| 6   | **mailing**      | `benchmark`, `logger`                                                                      | -                                     | -       |
| 7   | **gallery**      | `cache_get`, `cache_put`, `formatBytes`, `logger`, `get_ip`                                | -                                     | Twig    |
| 8   | **comments**     | `is_post`, `validate_email`, `sanitize`, `logger`, `get_ip`, `time_ago`, `excerpt`         | `{time_ago}`, `{comment_preview}`     | Mixed   |
| 9   | **auth_social**  | `random_string`, `validate_email`, `logger`, `get_ip`                                      | -                                     | -       |
| 10  | **webpush**      | `logger`, `get_ip`, `validate_url` (standalone), `is_mobile` (standalone)                  | -                                     | -       |
| 11  | **archive**      | `cache_get`, `cache_put`, `logger`                                                         | -                                     | Twig    |
**Документация:** Все плагины имеют файл `CHANGELOG_NGHELPERS.md` с подробным описанием изменений.
---
# Список плагинов для модификации с использованием ng-helpers v0.2.0
## 🎯 Приоритетные плагины (высокая польза)
### 1. **breadcrumbs** - Хлебные крошки
**Что улучшить:**
- ✅ Заменить ручную генерацию HTML на `breadcrumb()`
- ✅ Использовать `array_pluck()` для извлечения данных категорий
- ✅ Применить `cache_get()` / `cache_put()` вместо `cacheRetrieveFile()`
**Примеры применения:**
```php
use function Plugins\breadcrumb;
// Было:
$location[] = array('url' => $url, 'title' => $title, 'link' => $link);
// Стало:
$items = [
    ['title' => 'Главная', 'url' => '/'],
    ['title' => $category['name'], 'url' => $categoryUrl],
    ['title' => $currentPage]
];
$output = breadcrumb($items, ' → ');
```
---
### 2. **feedback** - Форма обратной связи
**Что улучшить:**
- ✅ `validate_email()` для проверки email полей
- ✅ `validate_phone()` для проверки телефона
- ✅ `sanitize()` для очистки пользовательских данных
- ✅ `csrf_field()` / `validate_csrf()` для защиты форм
- ✅ `is_post()` для проверки типа запроса
- ✅ `logger()` для логирования отправленных форм
- ✅ `get_ip()` для сохранения IP отправителя
**Примеры:**
```php
use function Plugins\{validate_email, sanitize, csrf_field, validate_csrf, logger, get_ip};
// Валидация
if (!validate_email($_POST['email'])) {
    $errors[] = 'Неверный email';
}
// Очистка
$message = sanitize($_POST['message']);
// CSRF защита
if (!validate_csrf()) {
    abort(403);
}
// Логирование
logger("Feedback from {$email} IP: " . get_ip(), 'info', 'feedback.log');
```
---
### 3. **lastcomments** - Последние комментарии
**Что улучшить:**
- ✅ `cache_get()` / `cache_put()` вместо `cacheRetrieveFile()`
- ✅ `time_ago()` для отображения времени комментариев
- ✅ `excerpt()` для создания превью комментариев
- ✅ `str_limit()` для обрезки текста
- ✅ `paginate()` для постраничной навигации
**Примеры:**
```php
use function Plugins\{cache_get, cache_put, time_ago, excerpt};
// Кэширование
$cacheKey = "lastcomments_{$config['theme']}_{$lang}";
if ($data = cache_get($cacheKey)) {
    return $data;
}
// Генерация данных
$result = generateComments();
cache_put($cacheKey, $result, 30);
// Форматирование
foreach ($comments as &$comment) {
    $comment['time_ago'] = time_ago($comment['postdate']);
    $comment['preview'] = excerpt($comment['text'], 150);
}
```
---
### 4. **similar** - Похожие новости
**Что улучшить:**
- ✅ `array_pluck()` для извлечения тегов
- ✅ `cache_remember()` уже используется, можно дополнить `cache_get/put`
- ✅ `collect()` для работы с коллекциями новостей
- ✅ `array_first()` / `array_last()` для выборки
**Примеры:**
```php
use function Plugins\{array_pluck, collect};
// Извлечение ID похожих новостей
$similarIds = array_pluck($similarRows, 'refNewsID');
// Работа с коллекцией
$similar = collect($similarRows)
    ->filter(function($item) {
        return $item['dimension'] == 1;
    })
    ->pluck('refNewsTitle')
    ->toArray();
```
---
### 5. **rating** - Рейтинги
**Что улучшить:**
- ✅ `is_ajax()` для обработки AJAX голосования
- ✅ `get_ip()` для учета IP голосующих
- ✅ `percentage()` для вычисления процентов рейтинга
- ✅ `json_decode_safe()` для безопасной работы с JSON
- ✅ `clamp()` для ограничения значений рейтинга
**Примеры:**
```php
use function Plugins\{is_ajax, get_ip, percentage, clamp};
if (is_ajax()) {
    $rating = clamp($_POST['rating'], 1, 5);
    $ip = get_ip();
    // Сохранение
    saveVote($newsId, $rating, $ip);
    // Ответ
    $percent = percentage($positiveVotes, $totalVotes);
    echo json_encode(['success' => true, 'percent' => $percent]);
}
```
---
### 6. **mailing** - Рассылка
**Что улучшить:**
- ✅ `validate_email()` для проверки подписчиков
- ✅ `array_pluck()` для извлечения email из БД
- ✅ `benchmark()` для замера производительности рассылки
- ✅ `logger()` для логирования отправок
- ✅ `chunk()` через `array_chunk()` для пакетной отправки
**Примеры:**
```php
use function Plugins\{validate_email, array_pluck, benchmark, logger};
$emails = array_pluck($subscribers, 'email');
$validEmails = array_filter($emails, 'Plugins\validate_email');
$result = benchmark(function() use ($validEmails) {
    return sendMailing($validEmails);
});
logger("Sent {$result['result']} emails in {$result['time']}s", 'info', 'mailing.log');
```
---
### 7. **gallery** - Галерея
**Что улучшить:**
- ✅ `formatBytes()` для отображения размера файлов
- ✅ `image_tag()` для генерации тегов изображений
- ✅ `paginate()` для постраничной навигации галереи
- ✅ `slug()` для создания URL галерей
- ✅ `truncate_html()` для описаний изображений
**Примеры:**
```php
use function Plugins\{formatBytes, image_tag, paginate, slug};
foreach ($images as &$img) {
    $img['size_formatted'] = formatBytes($img['filesize']);
    $img['html'] = image_tag($img['url'], $img['title'], ['class' => 'gallery-img']);
    $img['slug'] = slug($img['title']);
}
$pagination = paginate($currentPage, $totalPages, ['gallery' => $galleryId]);
```
---
### 8. **comments** - Комментарии
**Что улучшить:**
- ✅ `sanitize()` для очистки текста комментариев
- ✅ `validate_email()` для проверки email комментатора
- ✅ `csrf_field()` для защиты формы
- ✅ `is_post()` для проверки отправки
- ✅ `get_ip()` для сохранения IP
- ✅ `time_ago()` для отображения времени
- ✅ `is_ajax()` для AJAX комментариев
---
### 9. **auth_social** - Социальная авторизация
**Что улучшить:**
- ✅ `validate_email()` для проверки email из соцсетей
- ✅ `encrypt()` / `decrypt()` для хранения токенов
- ✅ `random_string()` для генерации state токенов
- ✅ `session()` для хранения данных авторизации
- ✅ `json_decode_safe()` для парсинга ответов API
---
### 10. **webpush** - Web Push уведомления
**Что улучшить:**
- ✅ `validate_url()` для проверки endpoint
- ✅ `is_mobile()` для определения устройства
- ✅ `json_validate()` для проверки payload
- ✅ `encrypt()` для шифрования данных
- ✅ `logger()` для логирования отправок
---
## 🔧 Средний приоритет
### 11. **archive** - Архив новостей
- ✅ `paginate()` для навигации по архиву
- ✅ `format_date()` для форматирования дат
- ✅ `cache_get/put()` для кэширования архива
### 12. **calendar** - Календарь
- ✅ `format_date()` для форматирования
- ✅ `cache_get/put()` для кэширования событий
- ✅ `array_pluck()` для извлечения дат
### 13. **faq** - FAQ
- ✅ `paginate()` для навигации
- ✅ `slug()` для URL вопросов
- ✅ `truncate_html()` для превью ответов
- ✅ `cache_get/put()` для кэширования
### 14. **guestbook** - Гостевая книга
- ✅ `sanitize()` для очистки сообщений
- ✅ `validate_email()` для проверки email
- ✅ `csrf_field()` для защиты
- ✅ `get_ip()` для IP
- ✅ `time_ago()` для времени
### 15. **sitemap** - Карта сайта
- ✅ `format_date()` для lastmod
- ✅ `cache_get/put()` для кэширования
- ✅ `array_pluck()` для извлечения URL
### 16. **rss_export** - RSS экспорт
- ✅ `format_date()` для pubDate
- ✅ `cache_get/put()` для кэширования фида
- ✅ `excerpt()` для описаний
### 17. **tags** - Теги
- ✅ `slug()` для URL тегов
- ✅ `array_pluck()` для извлечения тегов
- ✅ `paginate()` для навигации по тегам
### 18. **voting** - Опросы
- ✅ `is_ajax()` для голосования
- ✅ `get_ip()` для учета IP
- ✅ `percentage()` для результатов
- ✅ `cache_forget()` для сброса кэша после голосования
### 19. **pm** - Личные сообщения
- ✅ `sanitize()` для очистки сообщений
- ✅ `time_ago()` для времени
- ✅ `paginate()` для навигации по сообщениям
- ✅ `excerpt()` для превью
### 20. **search** (если есть плагин)
- ✅ `sanitize()` для очистки запросов
- ✅ `excerpt()` для результатов поиска
- ✅ `paginate()` для результатов
- ✅ `cache_get/put()` для кэширования популярных запросов
---
## 📊 Низкий приоритет (но можно улучшить)
### 21-30. Прочие плагины
- **autokeys** - `slug()`, `transliterate()`
- **booking** - `validate_email()`, `validate_phone()`, `format_date()`
- **complain** - `sanitize()`, `get_ip()`, `csrf_field()`
- **content_parser** - `str_between()`, `str_before()`, `str_after()`
- **holiday_decor** - `format_date()`, `cache_get/put()`
- **ipban** - `get_ip()`, `array_flatten()`
- **news_templates** - `slug()`, `excerpt()`
- **robots_editor** - `validate_url()`, `array_flatten()`
- **subscribe_comments** - `validate_email()`, `csrf_field()`
- **xfields** - `sanitize()`, `array_pluck()`
---
## 📝 Универсальные улучшения для всех плагинов
### Безопасность
```php
// Добавить во все формы
use function Plugins\{csrf_field, validate_csrf, sanitize};
// В форме
<?= csrf_field() ?>
// При обработке
if (!validate_csrf()) abort(403);
$data = sanitize($_POST);
```
### Кэширование
```php
// Заменить везде
use function Plugins\{cache_get, cache_put, cache_forget};
// Было:
cacheRetrieveFile($file, $expire, $plugin);
cacheStoreFile($file, $data, $plugin);
// Стало:
cache_get("plugin_{$key}");
cache_put("plugin_{$key}", $data, 30);
```
### Валидация
```php
// Добавить проверки
use function Plugins\{validate_email, validate_url, validate_phone};
if (!validate_email($email)) {
    $errors[] = 'Invalid email';
}
```
### Логирование
```php
// Добавить логирование важных событий
use function Plugins\logger;
logger("User action: {$action}", 'info', 'plugin.log');
logger("Error: {$error}", 'error', 'plugin.log');
```
---
## 🎯 План модернизации
### Этап 1: Критичные плагины (1-5)
Модернизировать плагины с формами и пользовательским вводом для повышения безопасности.
### Этап 2: Популярные плагины (6-10)
Улучшить производительность через кэширование и оптимизацию.
### Этап 3: Остальные плагины (11-30)
Постепенная модернизация для единообразия кода.
---
## 💡 Преимущества модернизации
1. **Безопасность** - CSRF защита, валидация, санитизация
2. **Производительность** - эффективное кэширование
3. **Читаемость** - единообразный код
4. **Отладка** - централизованное логирование
5. **Поддержка** - меньше дублирования кода
6. **Расширяемость** - легче добавлять новые функции
---
**Всего плагинов для улучшения: 30+**
**Потенциальная экономия кода: 40-60%**
**Улучшение безопасности: значительное**
