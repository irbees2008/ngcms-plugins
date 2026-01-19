## Коллекция вспомогательных функций для плагинов системы NGCMS

### Версия 0.2.1 - Money Formatting Edition

**Что нового в 0.2.1:**

- ➕ **formatMoney()** - форматирование денежных сумм с настраиваемыми разделителями
- 📦 Интеграция с плагином basket для отображения цен
- 📚 Расширенная документация с примерами

**Версия 0.2.0 - Extended Edition:**

- ✅ **60+ новых функций** для комплексной разработки плагинов
- 🔒 Валидация данных (email, URL, телефон, даты)
- 📦 Расширенная работа с массивами (pluck, flatten, only, except)
- 🔤 Преобразования строк (camelCase, snake_case, before/after)
- 📅 Работа с датами ("5 минут назад", форматирование)
- 🌐 HTTP хелперы (AJAX, mobile detection)
- 🐛 Debug инструменты (dump, logger, benchmark)
- 🔐 Безопасность (хеширование, шифрование)
- 🎨 HTML хелперы (link_to, image_tag, mailto)
- 🧮 Работа с числами (проценты, локализация, ограничения)

### Системные требования

Перед использованием плагина вам необходимо убедиться, что ваш сервер соответствует следующим требованиям:

- PHP >= 7.0.0

### Подключение

Плагин выпускается в двух вариациях, каждая из которых поддерживает свой тип кодировки: UTF-8 и Windows-1251. Для подключения вы можете просто скачать плагин в необходимой для вашего проекта кодировке:

- [UTF-8](https://codeload.github.com/russsiq/ng-helpers/zip/master)
- [Windows-1251](https://codeload.github.com/russsiq/ng-helpers/zip/windows-1251)
  Либо воспользуйтесь менеджером Composer:

```bash
composer require russsiq/ng-helpers:dev-master
```

> Обратите внимание, что кодировка UTF-8 является основной. Вы можете указать кодировку Windows-1251:

```bash
composer require russsiq/ng-helpers:dev-windows-1251
```

### Настройка

В данный момент плагин не имеет настроек, но активация плагина в панели управления является обязательной.

### Использование

Так как плагин использует пространство имен `Plugins`, то перед использованием отдельно взятой функции, вам необходимо явное указание имени функции, в том случае, если текущее пространство отличается:

```php
// Импортирование необходимых функций (PHP 5.6+)
use function Plugins\functionName;
// Импортирование необходимых функций
// с использованием псевдонима функции (PHP 5.6+)
use function Plugins\functionName as func;
```

<details>
<summary>Доступные методы</summary>
**Базовые функции:**
- [array_dotset](#method-array_dotset)
- [array_get](#method-array_get)
- [cache](#method-cache)
- [cacheRemember](#method-cacheRemember)
- [catz](#method-catz)
- [config](#method-config)
- [database](#method-database)
- [dd](#method-dd)
- [pageInfo](#method-pageInfo)
- [request](#method-request)
- [setting](#method-setting)
- [trans](#method-trans)
- [value](#method-value)
- [view](#method-view)
**Работа с окружением и сессиями:**
- [env](#method-env)
- [session](#method-session)
**Навигация и HTTP:**
- [redirect](#method-redirect)
- [url](#method-url)
- [abort](#method-abort)
**Работа с формами:**
- [old](#method-old)
- [sanitize](#method-sanitize)
**Работа со строками:**
- [str_limit](#method-str_limit)
- [starts_with](#method-starts_with)
- [ends_with](#method-ends_with)
- [str_contains](#method-str_contains)
**Проверка значений:**
- [filled](#method-filled)
- [blank](#method-blank)
- [optional](#method-optional)
**Утилиты:**
- [tap](#method-tap)
- [now](#method-now)
- [collect](#method-collect)
- [retry](#method-retry)
- [paginate](#method-paginate)
- [formatBytes](#method-formatBytes)
- [slug](#method-slug)
- [transliterate](#method-transliterate)
- [excerpt](#method-excerpt)
- [breadcrumb](#method-breadcrumb)
- [truncate_html](#method-truncate_html)
- [csrf_field](#method-csrf_field)
- [csrf_token](#method-csrf_token)
- [validate_csrf](#method-validate_csrf)
- [cache_get](#method-cache_get)
- [cache_put](#method-cache_put)
- [cache_forget](#method-cache_forget)
- [random_string](#method-random_string)
- [get_ip](#method-get_ip)
**Валидация:**
- [validate_email](#method-validate_email)
- [validate_url](#method-validate_url)
- [validate_phone](#method-validate_phone)
- [validate_date](#method-validate_date)
**Массивы:**
- [array_only](#method-array_only)
- [array_except](#method-array_except)
- [array_flatten](#method-array_flatten)
- [array_first](#method-array_first)
- [array_last](#method-array_last)
- [array_pluck](#method-array_pluck)
**Строки:**
- [str_snake](#method-str_snake)
- [str_camel](#method-str_camel)
- [str_studly](#method-str_studly)
- [str_before](#method-str_before)
- [str_after](#method-str_after)
- [str_between](#method-str_between)
**Даты:**
- [time_ago](#method-time_ago)
- [format_date](#method-format_date)
**HTTP:**
- [is_ajax](#method-is_ajax)
- [is_post](#method-is_post)
- [is_get](#method-is_get)
- [get_user_agent](#method-get_user_agent)
- [is_mobile](#method-is_mobile)
**Debug:**
- [dump](#method-dump)
- [logger](#method-logger)
- [benchmark](#method-benchmark)
**Безопасность:**
- [hash_make](#method-hash_make)
- [hash_check](#method-hash_check)
- [encrypt](#method-encrypt)
- [decrypt](#method-decrypt)
**Пути:**
- [storage_path](#method-storage_path)
- [public_path](#method-public_path)
- [plugin_path](#method-plugin_path)
**Условные:**
- [when](#method-when)
- [unless](#method-unless)
- [transform](#method-transform)
**JSON:**
- [json_validate](#method-json_validate)
- [json_decode_safe](#method-json_decode_safe)
**Числа:**
- [number_format_locale](#method-number_format_locale)
- [percentage](#method-percentage)
- [clamp](#method-clamp)
- [formatMoney](#method-formatMoney)
**HTML:**
- [link_to](#method-link_to)
- [image_tag](#method-image_tag)
- [mailto](#method-mailto)
- [notify](#method-notify)
</details>
<a name="method-array_dotset"></a>
#### `array_dotset`
Установить значение элементу массива, используя «точечную» нотацию.:
```php
use function Plugins\array_dotset;
$array = [
    'keys' => [
        'first' => 450,
        'second' => 460
    ],
];
array_dotset($array, 'keys.second', 800);
// [
//     'keys' => [
//         'first' => 450,
//         'second' => 800
//     ],
//
// ]
```
<a name="method-cache"></a>
#### `cache`
Получить данные из кэша, либо сохранить указанные данные в кэш.
```php
use function Plugins\cache;
// Сохранить данные в кеш.
cache($plugin, md5('key'), 'value');
```
```php
use function Plugins\cache;
// Получить данные из кеша.
$value = cache($plugin, md5('key'));
```
> Обратите внимание, что указываемый плагин `$plugin` должен иметь настройку `cache`, значение которой должно быть установлено как `true`.
<a name="method-cacheRemember"></a>
#### `cacheRemember`
Получить данные из кэша, либо выполнить замыкание и сохранить результат в кэш.
```php
use function Plugins\cacheRemember;
$rows = cacheRemember($plugin, md5('key'), function () {
    return database()
        ->select(
            "select * from `".prefix."_news` where `approve` = 1 order by `views` desc limit 20"
        );
});
```
> Обратите внимание, что указываемый плагин `$plugin` должен иметь настройку `cache`, значение которой должно быть установлено как `true`.
<a name="method-catz"></a>
#### `catz`
Получить категорию по идентификатору, либо массив всех категорий.
```php
use function Plugins\catz;
$categories = catz();
// Итерация всех категорий.
foreach ($categories as $id => $data) {
    //
}
```
```php
use function Plugins\catz;
// Получить категорию с идентификатором `28`.
$category = catz(28);
```
<a name="method-config"></a>
#### `config`
Получить значение конфигурации системы.
```php
use function Plugins\config;
$value = config('key', 'default');
```
<a name="method-database"></a>
#### `database`
Получить текущее подключение к базе данных.
```php
use function Plugins\database;
$rows = database()->select(
    "select * from `".prefix."_news` where `approve` = 1 order by `views` desc limit 20"
);
```
> Обратите внимание, что при использовании данной функции вы несёте полную ответственность [за выполняемые запросы к БД](https://ru.wikipedia.org/wiki/Внедрение_SQL-кода).
<a name="method-dd"></a>
#### `dd`
Распечатать переменную, массив переменных или объект и прекратить выполнение скрипта.
```php
use function Plugins\dd;
$array = [
    'keys' => [
        'first' => 450,
        'second' => 460
    ],
];
dd($array);
// Array
// (
//     [keys] => Array
//         (
//             [first] => 450
//             [second] => 460
//         )
//
// )
```
> Обратите внимание, что использование данной функции допустимо только в режиме отладки приложения.
<a name="method-pageInfo"></a>
#### `pageInfo`
Установить системную информацию о текущей странице.
```php
use function Plugins\pageInfo;
pageInfo('key', 'value');
```
```php
// Примеры использования.
$breadcrumbs[] = [
    'link' => $this->pluginLink,
    'text' => trans('x_filter:title'),
];
pageInfo('info.breadcrumbs', $breadcrumbs);
pageInfo('meta.description', $description);
pageInfo('meta.keywords', $keywords);
```
<a name="method-request"></a>
#### `request`
Получить значение из глобального `$_REQUEST`.
```php
use function Plugins\request;
$value = request('key', 'default');
```
```php
// Пример использования.
$currentPage = (int) request('page', 1);
```
<a name="method-setting"></a>
#### `setting`
Получить настройку плагина по ключу, либо задать массив настроек.
```php
use function Plugins\setting;
// Получить настройку плагина.
$value = setting($plugin, 'key', 'default');
// Задать массив настроек плагина.
setting($plugin, [
    'key' => 'value',
    'another_key' => 'another_value'
]);
```
Примеры использования:
```php
// Получить настройку плагина.
$cacheExpire = (int) setting($plugin, 'cacheExpire', 60);
```
```php
// Задать массив настроек плагина.
setting($plugin, [
    // Использовать кеширование данных.
    'cache' => 0,
    // Период обновления кеша.
    'cacheExpire' => 60,
]);
```
<a name="method-starts_with"></a>
#### `starts_with`
Определить, начинается ли переданная строка с указанной подстроки.
```php
use function Plugins\starts_with;
$result = starts_with('Строка для примера', 'Строка ');
// true
```
<a name="method-trans"></a>
#### `trans`
Получить перевод строки.
```php
use function Plugins\trans;
$string = trans('key');
```
```php
// Пример использования.
$charset = trans('encoding');
```
<a name="method-value"></a>
#### `value`
Возвращает значение по умолчанию для переданного значения.
```php
use function Plugins\value;
$result = value(true);
// true
$result = value(function () {
    return false;
});
// false
```
<a name="method-view"></a>
#### `view`
Выводит шаблон с заданным контекстом и возвращает его в виде строки.
```php
use function Plugins\view;
$context = [
    'key' => 'value'
];
return view($template, $context);
```
---
## Новые функции
<a name="method-env"></a>
#### `env`
Получить значение переменной окружения с автоматическим преобразованием типов.
```php
use function Plugins\env;
$debug = env('APP_DEBUG', false);
// Автоматически преобразует строки 'true'/'false' в boolean
$apiKey = env('API_KEY', 'default-key');
```
<a name="method-session"></a>
#### `session`
Получить или установить значение сессии.
```php
use function Plugins\session;
// Получить значение
$userId = session('user_id', 0);
// Установить значение
session(['user_id' => 123, 'username' => 'John']);
// Получить все данные сессии
$allData = session();
```
<a name="method-redirect"></a>
#### `redirect`
Перенаправить пользователя на указанный URL с поддержкой HTTP кодов.
```php
use function Plugins\redirect;
// Обычное перенаправление (302)
redirect('/admin/dashboard');
// Постоянное перенаправление (301)
redirect('/new-page', 301);
```
<a name="method-url"></a>
#### `url`
Сгенерировать полный URL для указанного пути.
```php
use function Plugins\url;
// Базовый URL
$link = url('admin/news');
// http://example.com/admin/news
// С параметрами
$link = url('search', ['q' => 'test', 'page' => 2]);
// http://example.com/search?q=test&page=2
```
<a name="method-abort"></a>
#### `abort`
Прервать выполнение скрипта с HTTP кодом ошибки.
```php
use function Plugins\abort;
// 404 Not Found
if (!$post) {
    abort(404);
}
// С пользовательским сообщением
abort(403, 'У вас нет доступа к этой странице');
```
<a name="method-old"></a>
#### `old`
Получить старое значение из сессии (для форм после редиректа с ошибками).
```php
use function Plugins\old;
// В форме
<input type="text" name="title" value="<?= old('title', $news['title']) ?>">
// После неудачной валидации сохраните данные:
session(['_old_input' => $_POST]);
redirect('/admin/news/edit');
```
<a name="method-sanitize"></a>
#### `sanitize`
Очистить данные от потенциально опасных символов (защита от XSS).
```php
use function Plugins\sanitize;
$clean = sanitize($_POST['comment']);
// Очищает HTML теги и экранирует спецсимволы
// Массив данных
$cleanData = sanitize($_POST);
// Без удаления тегов (только экранирование)
$safe = sanitize($input, false);
```
<a name="method-str_limit"></a>
#### `str_limit`
Обрезать строку до указанной длины с поддержкой многобайтовых символов.
```php
use function Plugins\str_limit;
$short = str_limit($longText, 100);
// Первые 100 символов...
$preview = str_limit($description, 50, '→');
// Первые 50 символов→
```
<a name="method-array_get"></a>
#### `array_get`
Безопасно получить значение из массива, используя «точечную» нотацию.
```php
use function Plugins\array_get;
$config = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306
    ]
];
$host = array_get($config, 'database.host');
// localhost
$timeout = array_get($config, 'database.timeout', 30);
// 30 (значение по умолчанию)
```
<a name="method-filled"></a>
#### `filled`
Определить, является ли значение "заполненным" (не пустым).
```php
use function Plugins\filled;
if (filled($request['email'])) {
    // Email указан
}
filled('test');    // true
filled('');        // false
filled(null);      // false
filled(0);         // true
filled([]);        // false
```
<a name="method-blank"></a>
#### `blank`
Определить, является ли значение "пустым".
```php
use function Plugins\blank;
if (blank($user['avatar'])) {
    $user['avatar'] = '/images/default-avatar.png';
}
blank('');         // true
blank(null);       // true
blank([]);         // true
blank('test');     // false
blank(0);          // false
```
<a name="method-optional"></a>
#### `optional`
Безопасный доступ к свойствам объекта или элементам массива (предотвращает ошибки).
```php
use function Plugins\optional;
// Без optional - может выдать ошибку
$name = $user->profile->name;
// С optional - безопасно
$name = optional($user)->profile->name;
// Вернёт null, если $user или profile равны null
// С callback
$name = optional($user, function($u) {
    return $u->getName();
});
```
<a name="method-tap"></a>
#### `tap`
Вызвать замыкание с переданным значением и вернуть это значение (полезно для chaining).
```php
use function Plugins\tap;
$result = tap($news, function($item) {
    $item['views']++;
    database()->query("UPDATE news SET views = {$item['views']} WHERE id = {$item['id']}");
});
// Вернёт $news с увеличенным счетчиком
```
<a name="method-now"></a>
#### `now`
Получить текущую дату и время.
```php
use function Plugins\now;
$timestamp = now();
// Unix timestamp
$date = now('Y-m-d H:i:s');
// 2026-01-11 14:30:00
$year = now('Y');
// 2026
```
<a name="method-collect"></a>
#### `collect`
Создать коллекцию с дополнительными методами для работы с массивами.
```php
use function Plugins\collect;
$collection = collect([1, 2, 3, 4, 5]);
// Фильтрация
$filtered = $collection->filter(function($item) {
    return $item > 2;
});
// Преобразование
$mapped = $collection->map(function($item) {
    return $item * 2;
});
// Получить первый/последний элемент
$first = $collection->first();
$last = $collection->last();
// Извлечь значения
$users = collect($usersArray)->pluck('name')->toArray();
// Количество элементов
$count = $collection->count();
```
<a name="method-ends_with"></a>
#### `ends_with`
Определить, заканчивается ли переданная строка указанной подстрокой.
```php
use function Plugins\ends_with;
$result = ends_with('image.jpg', '.jpg');
// true
$result = ends_with('photo.png', ['.jpg', '.png', '.gif']);
// true
```
<a name="method-str_contains"></a>
#### `str_contains`
Определить, содержит ли строка указанную подстроку.
```php
use function Plugins\str_contains;
if (str_contains($email, '@')) {
    // Email содержит @
}
$result = str_contains('Привет мир', ['мир', 'world']);
// true
```
<a name="method-retry"></a>
#### `retry`
Повторить выполнение функции указанное количество раз при возникновении исключения.
```php
use function Plugins\retry;
// Попробовать 3 раза
$result = retry(3, function() {
    return makeApiRequest();
});
// С задержкой 100ms между попытками
$result = retry(5, function($attempt) {
    echo "Попытка #{$attempt}";
    return connectToDatabase();
}, 100);
```
---
## Дополнительные функции для работы с плагинами
<a name="method-paginate"></a>
#### `paginate`
Создать постраничную навигацию (обертка над системной функцией).
```php
use function Plugins\paginate;
$currentPage = (int) request('page', 1);
$totalPages = ceil($totalRecords / $perPage);
$pagination = paginate($currentPage, $totalPages, [
    'pluginName' => 'myplugin'
]);
// Вывод в шаблоне
echo $pagination;
```
<a name="method-formatBytes"></a>
#### `formatBytes`
Форматировать размер файла в удобочитаемый вид.
```php
use function Plugins\formatBytes;
echo formatBytes(1024);           // 1 KB
echo formatBytes(1048576);        // 1 MB
echo formatBytes(1073741824);     // 1 GB
echo formatBytes(1536, 1);        // 1.5 KB
```
<a name="method-slug"></a>
#### `slug`
Создать URL-friendly строку (slug) из текста с транслитерацией.
```php
use function Plugins\slug;
$slug = slug('Привет Мир!');
// privet-mir
$slug = slug('Hello World 2024', '_');
// hello_world_2024
```
<a name="method-transliterate"></a>
#### `transliterate`
Транслитерация кириллицы в латиницу.
```php
use function Plugins\transliterate;
$text = transliterate('Привет, мир!');
// Privet, mir!
$text = transliterate('Москва');
// Moskva
```
<a name="method-excerpt"></a>
#### `excerpt`
Создать выдержку из текста с удалением HTML тегов.
```php
use function Plugins\excerpt;
$html = '<p>Это длинный текст с <strong>HTML</strong> тегами...</p>';
$short = excerpt($html, 50);
// Это длинный текст с HTML тегами...
$preview = excerpt($description, 100, ' →');
```
<a name="method-breadcrumb"></a>
#### `breadcrumb`
Создать хлебные крошки из массива элементов.
```php
use function Plugins\breadcrumb;
$items = [
    ['title' => 'Главная', 'url' => '/'],
    ['title' => 'Новости', 'url' => '/news'],
    ['title' => 'Текущая статья']  // Без URL - последний элемент
];
$breadcrumbs = breadcrumb($items);
// <a href="/">Главная</a> / <a href="/news">Новости</a> / Текущая статья
// С пользовательским разделителем
$breadcrumbs = breadcrumb($items, ' → ');
```
<a name="method-truncate_html"></a>
#### `truncate_html`
Обрезать HTML текст без повреждения тегов (закрывает открытые теги).
```php
use function Plugins\truncate_html;
$html = '<p>Это <strong>важный</strong> текст с <a href="#">ссылкой</a></p>';
$truncated = truncate_html($html, 20);
// <p>Это <strong>важный</strong> текст...</p>
```
<a name="method-csrf_field"></a>
#### `csrf_field`
Сгенерировать скрытое поле с CSRF токеном для форм.
```php
use function Plugins\csrf_field;
<form method="POST">
    <?= csrf_field() ?>
    <!-- Другие поля формы -->
    <button type="submit">Отправить</button>
</form>
```
<a name="method-csrf_token"></a>
#### `csrf_token`
Получить текущий CSRF токен.
```php
use function Plugins\csrf_token;
$token = csrf_token();
// Использовать в AJAX запросах
```
<a name="method-validate_csrf"></a>
#### `validate_csrf`
Проверить CSRF токен на валидность.
```php
use function Plugins\validate_csrf;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        abort(403, 'Недопустимый CSRF токен');
    }
    // Обработка формы
}
```
<a name="method-cache_get"></a>
#### `cache_get`
Получить значение из файлового кэша.
```php
use function Plugins\cache_get;
$data = cache_get('my_key', []);
// Вернет [] если значение не найдено или истекло
```
<a name="method-cache_put"></a>
#### `cache_put`
Сохранить значение в файловый кэш с указанием времени жизни.
```php
use function Plugins\cache_put;
$data = ['news' => $newsArray];
cache_put('latest_news', $data, 30);  // На 30 минут
```
<a name="method-cache_forget"></a>
#### `cache_forget`
Удалить значение из кэша.
```php
use function Plugins\cache_forget;
cache_forget('old_data');
```
<a name="method-random_string"></a>
#### `random_string`
Сгенерировать случайную строку (для токенов, ID и т.д.).
```php
use function Plugins\random_string;
$token = random_string(32);
// d4f67a9b2c1e8f3a...
$id = random_string(16);
```
<a name="method-get_ip"></a>
#### `get_ip`
Получить реальный IP адрес пользователя (с учетом прокси и CDN).
```php
use function Plugins\get_ip;
$userIp = get_ip();
// Учитывает HTTP_X_FORWARDED_FOR и другие заголовки
// Запись в лог
$log = "User {$userIp} visited page";
```
---
## Расширенные функции
### Валидация
<a name="method-validate_email"></a>
#### `validate_email` • `validate_url` • `validate_phone` • `validate_date`
```php
use function Plugins\{validate_email, validate_url, validate_phone, validate_date};
validate_email('test@example.com');  // true
validate_url('https://ngcms.org');   // true
validate_phone('+7-999-123-45-67');  // true
validate_date('2026-01-11', 'Y-m-d'); // true
```
### Массивы
<a name="method-array_only"></a>
#### `array_only` • `array_except`
```php
use function Plugins\{array_only, array_except};
$data = ['name' => 'John', 'age' => 30, 'email' => 'john@test.com'];
$filtered = array_only($data, ['name', 'email']);
// ['name' => 'John', 'email' => 'john@test.com']
$without = array_except($data, ['age']);
// ['name' => 'John', 'email' => 'john@test.com']
```
<a name="method-array_flatten"></a>
#### `array_flatten` • `array_first` • `array_last` • `array_pluck`
```php
use function Plugins\{array_flatten, array_first, array_last, array_pluck};
array_flatten([[1, 2], [3, 4]]); // [1, 2, 3, 4]
array_first([1, 2, 3]);          // 1
array_last([1, 2, 3]);           // 3
$users = [
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25]
];
array_pluck($users, 'name');     // ['John', 'Jane']
```
### Строки
<a name="method-str_snake"></a>
#### `str_snake` • `str_camel` • `str_studly`
```php
use function Plugins\{str_snake, str_camel, str_studly};
str_snake('helloWorld');   // hello_world
str_camel('hello_world');  // helloWorld
str_studly('hello_world'); // HelloWorld
```
<a name="method-str_before"></a>
#### `str_before` • `str_after` • `str_between`
```php
use function Plugins\{str_before, str_after, str_between};
str_before('hello-world', '-');      // hello
str_after('hello-world', '-');       // world
str_between('hello [world]!', '[', ']'); // world
```
### Даты
<a name="method-time_ago"></a>
#### `time_ago` • `format_date`
```php
use function Plugins\{time_ago, format_date};
time_ago(time() - 300);              // 5 минут назад
time_ago(strtotime('-2 hours'));     // 2 часа назад
time_ago(strtotime('-1 day'), 'en'); // 1 day ago
format_date(time(), 'd.m.Y H:i');    // 11.01.2026 14:30
```
### HTTP
<a name="method-is_ajax"></a>
#### `is_ajax` • `is_post` • `is_get` • `is_mobile`
```php
use function Plugins\{is_ajax, is_post, is_get, is_mobile, get_user_agent};
if (is_ajax()) {
    echo json_encode(['status' => 'ok']);
}
if (is_post()) {
    // Обработка POST запроса
}
if (is_mobile()) {
    // Показать мобильную версию
}
$ua = get_user_agent(); // Mozilla/5.0...
```
### Debug
<a name="method-dump"></a>
#### `dump` • `logger` • `benchmark`
```php
use function Plugins\{dump, logger, benchmark};
// Вывод без остановки
dump($data, $moreData);
// Запись в лог
logger('User logged in', 'info', 'auth.log');
logger('Database error', 'error');
// Замер производительности
$result = benchmark(function() {
    // Тяжелые вычисления
    return processData();
});
// ['result' => ..., 'time' => 0.123, 'memory' => 2048000]
```
### Безопасность
<a name="method-hash_make"></a>
#### `hash_make` • `hash_check` • `encrypt` • `decrypt`
```php
use function Plugins\{hash_make, hash_check, encrypt, decrypt};
// Хеширование пароля
$hash = hash_make('secret123');
hash_check('secret123', $hash); // true
// Шифрование
$encrypted = encrypt('sensitive data', 'my-key');
$decrypted = decrypt($encrypted, 'my-key');
```
### Пути
<a name="method-storage_path"></a>
#### `storage_path` • `public_path` • `plugin_path`
```php
use function Plugins\{storage_path, public_path, plugin_path};
storage_path('images/photo.jpg');    // /path/uploads/images/photo.jpg
public_path('css/style.css');        // /path/css/style.css
plugin_path('myplugin', 'config.php'); // /path/engine/plugins/myplugin/config.php
```
### Условные хелперы
<a name="method-when"></a>
#### `when` • `unless` • `transform`
```php
use function Plugins\{when, unless, transform};
when($user->isAdmin(), function() {
    showAdminPanel();
});
unless($user->isBanned(), function() {
    allowAccess();
}, function() {
    showBanMessage();
});
$result = transform($value, function($val) {
    return strtoupper($val);
}, 'default');
```
### JSON
<a name="method-json_validate"></a>
#### `json_validate` • `json_decode_safe`
```php
use function Plugins\{json_validate, json_decode_safe};
$json = '{"name":"John","age":30}';
if (json_validate($json)) {
    $data = json_decode_safe($json);
}
// С дефолтом при ошибке
$data = json_decode_safe($badJson, ['error' => true]);
```
### Числа
<a name="method-number_format_locale"></a>
#### `number_format_locale` • `percentage` • `clamp`
```php
use function Plugins\{number_format_locale, percentage, clamp};
number_format_locale(1234567.89, 2, 'ru'); // 1 234 567,89
number_format_locale(1234567.89, 2, 'en'); // 1,234,567.89
percentage(75, 150);  // 50.0
clamp(15, 0, 10);     // 10 (ограничено максимумом)
```
<a name="method-formatMoney"></a>
#### `formatMoney`
Форматирует число как денежную сумму с настраиваемыми разделителями:
```php
use function Plugins\formatMoney;
// Стандартное форматирование (2 знака после точки, пробел между тысячами)
formatMoney(1234.56);              // "1 234.56"
// Без дробной части
formatMoney(1234.56, 0);           // "1 235"
// С запятой как разделителем дробной части
formatMoney(1234.56, 2, ',');      // "1 234,56"
// С запятой между тысячами и точкой для копеек
formatMoney(1234.56, 2, '.', ','); // "1,234.56"
// В шаблонах с валютой
$formatted = formatMoney($price) . ' ₽';
```
### HTML
<a name="method-link_to"></a>
#### `link_to` • `image_tag` • `mailto`
```php
use function Plugins\{link_to, image_tag, mailto};
echo link_to('/news', 'Новости', ['class' => 'nav-link']);
// <a href="/news" class="nav-link">Новости</a>
echo image_tag('/img/logo.png', 'Logo', ['width' => '200']);
// <img src="/img/logo.png" alt="Logo" width="200">
echo mailto('info@example.com', 'Написать нам');
// <a href="mailto:info@example.com">Написать нам</a>
```
<a name="method-notify"></a>
#### `notify`
Генерирует HTML-блок с toast-уведомлением для вставки через JavaScript:
```php
use function Plugins\notify;
// В AJAX-ответе плагина
return json_encode([
    'status' => 1,
    'data' => notify('success', 'Комментарий добавлен')
]);
// Типы уведомлений: 'success', 'error', 'info', 'warning'
echo notify('error', 'Заполните все поля');
echo notify('info', 'Сохранение...');
echo notify('warning', 'Проверьте данные');
// Автоматическое экранирование спецсимволов
$userMessage = $row['message'];
echo notify('success', $userMessage); // безопасно
// Обработка на стороне JavaScript:
// if (result.data.indexOf('<') !== -1) {
//     document.body.insertAdjacentHTML('beforeend', result.data);
// } else {
//     notify('success', result.data);
// }
```
### Лицензия
`ng-helpers` - программное обеспечение с открытым исходным кодом, распространяющееся по лицензии [MIT](https://choosealicense.com/licenses/mit/).
