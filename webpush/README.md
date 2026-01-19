# Web Push плагин для NGCMS

Плагин для отправки push-уведомлений подписчикам сайта. Работает на базе VAPID (Web Push Protocol).

## Требования

- **PHP 8.0+**
- **HTTPS** (кроме localhost для тестирования)
- **Composer** пакет `minishlink/web-push`
- Современный браузер с поддержкой Service Workers и Push API

## Установка

### 1. Установка библиотеки Web Push

#### Способ A: Через Composer (рекомендуется)

В папке плагина выполните:

```bash
cd engine/plugins/webpush
composer install
```

Или если composer не установлен глобально:

```bash
cd engine/plugins/webpush
php composer.phar install
```

#### Способ B: Ручная установка с GitHub

Если у вас нет Composer, можно установить библиотеку вручную:

1. Скачайте библиотеку с GitHub:
   - [minishlink/web-push](https://github.com/web-push-libs/web-push-php/releases)
   - Скачайте последнюю версию (ZIP архив)
2. Распакуйте архив в `vendor/minishlink/web-push/`
3. Скачайте зависимости:
   - [spomky-labs/base64url](https://github.com/Spomky-Labs/base64url/releases) → `vendor/spomky-labs/base64url/`
   - [paragonie/constant_time_encoding](https://github.com/paragonie/constant_time_encoding/releases) → `vendor/paragonie/constant_time_encoding/`
   - [web-token/jwt-signature](https://github.com/web-token/jwt-framework/releases) → `vendor/web-token/jwt-signature/`
4. Создайте файл `vendor/autoload.php`:
   ```php
   <?php
   // Простой autoloader для ручной установки
   spl_autoload_register(function ($class) {
       $prefix = 'Minishlink\\WebPush\\';
       $base_dir = __DIR__ . '/minishlink/web-push/src/';
       $len = strlen($prefix);
       if (strncmp($prefix, $class, $len) !== 0) {
           return;
       }
       $relative_class = substr($class, $len);
       $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
       if (file_exists($file)) {
           require $file;
       }
   });
   ```
   > **Примечание:** Способ через Composer проще и автоматически разрешает все зависимости. Ручная установка требует больше действий.

### 2. Установка плагина

1. Скопируйте папку `webpush` в `engine/plugins/`
2. В админ-панели NGCMS перейдите в раздел управления плагинами
3. Найдите плагин "Web Push Уведомления" и нажмите "Установить"
4. Подтвердите установку

### 3. Генерация VAPID ключей

После установки плагина выполните запрос для генерации ключей:

```
GET /engine/plugins/webpush/send.php?action=genkeys&secret=ВАШ_СЕКРЕТНЫЙ_КЛЮЧ
```

Секретный ключ можно найти в настройках плагина (параметр `send_secret`).
Ответ будет содержать публичный и приватный ключи:

```json
{
  "ok": true,
  "publicKey": "...",
  "privateKey": "..."
}
```

### 4. Сохранение ключей

Скопируйте полученные ключи и вставьте их в настройки плагина:

- **VAPID Public Key** - публичный ключ
- **VAPID Private Key** - приватный ключ (храните в секрете!)

### 5. Добавление в шаблон

В файле `templates/ваша_тема/main.tpl` (или общий шаблон) добавьте перед закрывающим тегом `</body>`:

```twig
{webpush}
```

## Настройка

Все настройки доступны в админ-панели в разделе плагинов:

### Основные настройки

- **Включить Web Push уведомления** - активация плагина
- **Показывать кнопку подписки** - автоматически показывать кнопку на сайте
- **Текст кнопки подписки** - текст на кнопке

### VAPID настройки

- **VAPID Public Key** - публичный ключ (генерируется автоматически)
- **VAPID Private Key** - приватный ключ (генерируется автоматически)
- **VAPID Subject** - email или URL сайта (например: `mailto:admin@example.com`)

### Внешний вид уведомлений

- **Иконка уведомления** - путь к изображению 192x192px
- **Badge иконка** - путь к монохромной иконке 96x96px

### Безопасность

- **Секретный ключ для отправки** - токен для защиты API отправки

## Использование

### Подписка посетителей

После установки и настройки на сайте автоматически появится кнопка "Включить уведомления". При клике посетитель:

1. Увидит запрос браузера на разрешение уведомлений
2. После разрешения будет подписан на уведомления
3. Кнопка изменится на "Отключить уведомления"

### Отправка уведомлений

Для отправки push-уведомления всем подписчикам используйте POST запрос:

```bash
POST /engine/plugins/webpush/send.php?secret=ВАШ_СЕКРЕТНЫЙ_КЛЮЧ
Параметры (form-data или x-www-form-urlencoded):
- title: Заголовок уведомления
- body: Текст уведомления
- url: URL для перехода при клике (например: /news/123)
- icon: (необязательно) Путь к иконке
- badge: (необязательно) Путь к badge
```

#### Пример с curl:

```bash
curl -X POST "https://example.com/engine/plugins/webpush/send.php?secret=YOUR_SECRET" \
  -d "title=Новая статья" \
  -d "body=На сайте опубликована новая статья!" \
  -d "url=/news/123"
```

#### Пример с PHP:

```php
$secret = 'ваш_секретный_ключ';
$data = [
    'title' => 'Новая статья',
    'body' => 'На сайте опубликована новая статья!',
    'url' => '/news/123'
];
$ch = curl_init("https://example.com/engine/plugins/webpush/send.php?secret={$secret}");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
echo "Отправлено: " . $result['sent'] . " уведомлений\n";
```

### Ответ API отправки:

```json
{
  "ok": true,
  "sent": 42,
  "removed": 2,
  "total": 44
}
```

- `sent` - количество успешно отправленных уведомлений
- `removed` - количество удалённых мёртвых подписок
- `total` - общее количество подписок

## Интеграция с новостями

Вы можете автоматически отправлять уведомления при публикации новостей, добавив код в соответствующее место системы:

```php
// После создания новости
$newsTitle = "Новая статья";
$newsUrl = "/news/123";
// Отправка уведомления
$secret = extra_get_param('webpush', 'send_secret');
$postData = [
    'title' => $newsTitle,
    'body' => 'На сайте опубликована новая статья!',
    'url' => $newsUrl
];
// ... выполнить POST запрос к send.php
```

## Структура файлов

```
engine/plugins/webpush/
├── version              # Метаданные плагина
├── install.php          # Установка (создание таблицы БД)
├── uninstall.php        # Деинсталляция
├── config.php           # Настройки в админке
├── webpush.php          # Основная логика
├── endpoint.php         # API для подписок (публичный)
├── send.php             # API для отправки (защищённый)
├── lang/
│   └── russian/
│       └── main.ini     # Локализация
├── tpl/
│   └── webpush.tpl      # Twig шаблон кнопки
├── js/
│   └── webpush.js       # Клиентский скрипт
└── sw/
    └── webpush-sw.js    # Service Worker (копируется в корень)
```

## База данных

Плагин создаёт таблицу `prefix_webpush_subscriptions`:
| Поле | Тип | Описание |
|------|-----|----------|
| id | int | ID подписки |
| hash | varchar(64) | Хеш endpoint (уникальный) |
| endpoint | text | URL endpoint браузера |
| p256dh | varchar(255) | Публичный ключ клиента |
| auth | varchar(255) | Auth токен |
| user_agent | varchar(255) | User Agent браузера |
| ip | varchar(45) | IP адрес |
| created | int | Время создания (timestamp) |
| updated | int | Время обновления (timestamp) |

## Безопасность

1. **HTTPS обязателен** - Web Push работает только по защищённому соединению
2. **Секретный ключ** - храните `send_secret` в безопасности, не публикуйте его
3. **VAPID Private Key** - никогда не передавайте приватный ключ клиенту
4. **Валидация** - все входные данные проверяются и экранируются

## Совместимость браузеров

- ✅ Chrome 42+
- ✅ Firefox 44+
- ✅ Edge 17+
- ✅ Opera 37+
- ✅ Safari 16+ (macOS 13+, iOS 16.4+)
- ❌ Internet Explorer (не поддерживается)

## Устранение проблем

### Кнопка не появляется

- Проверьте, что плагин включен в настройках
- Убедитесь, что в шаблоне добавлена переменная `{webpush}`
- Проверьте настройку "Показывать кнопку подписки"

### Ошибка "VAPID keys are empty"

- Сгенерируйте ключи через `send.php?action=genkeys&secret=...`
- Скопируйте их в настройки плагина

### Уведомления не приходят

- Проверьте, что сайт работает по HTTPS
- Убедитесь, что VAPID ключи сохранены
- Проверьте, что `composer require minishlink/web-push` выполнен
- Посмотрите логи ошибок PHP

### Service Worker не регистрируется

- Проверьте, что файл `webpush-sw.js` находится в корне сайта
- Убедитесь, что сайт работает по HTTPS (или localhost)

## Changelog

### Version 1.0.0

- Первый релиз
- Подписка на уведомления
- Отправка push-уведомлений
- База данных для хранения подписок
- Автоматическая очистка мёртвых подписок
- Генерация VAPID ключей
- Twig шаблоны
- Локализация (русский)
- Адаптивный дизайн кнопки

## Лицензия

GPL v2 или выше

## Автор

NGCMS Team

## Поддержка

При возникновении проблем создайте issue в репозитории проекта или обратитесь на форум NGCMS.
