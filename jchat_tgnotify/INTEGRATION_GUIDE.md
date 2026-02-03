# Интеграция уведомлений в другие плагины

## Как добавить уведомления в свой плагин

### 1. Проверка наличия функции

```php
if (function_exists('ngcms_tg_notify')) {
    // Функция доступна
}
```

### 2. Отправка уведомления

```php
ngcms_tg_notify('TYPE', [
    'title'    => 'Заголовок',
    'author'   => 'Имя автора',
    'text'     => 'Текст сообщения',
    'url'      => 'https://site.ru/link',
    'datetime' => date('Y-m-d H:i:s'),
]);
```

## Примеры интеграции

### Feedback (Обратная связь)

Файл: `engine/plugins/feedback/feedback.php`

В функцию `plugin_feedback_post()` после успешной отправки добавить:

```php
// После строки с msg(array("type" => "info", ...))
// Примерно на строке 300-400

// Отправка уведомления в Telegram
if (function_exists('ngcms_tg_notify')) {
    $feedbackData = [
        'title'    => $frow['title'] ?? 'Обратная связь',
        'author'   => $userROW['name'] ?? ($_REQUEST['fld_name'] ?? 'Гость'),
        'text'     => strip_tags($_REQUEST['fld_message'] ?? ''),
        'url'      => home . '/admin.php?mod=extra-config&plugin=feedback',
        'datetime' => date('Y-m-d H:i:s'),
    ];
    ngcms_tg_notify('feedback', $feedbackData);
}
```

### PM (Личные сообщения)

Файл: `engine/plugins/pm/pm.php`

В функцию `pm_send()` после INSERT в базу добавить:

```php
// После успешной вставки сообщения в БД
// Найти строку с $mysql->query("INSERT INTO ...")

if (function_exists('ngcms_tg_notify')) {
    $pmData = [
        'title'    => 'Личное сообщение',
        'author'   => $userROW['name'],
        'text'     => strip_tags($message),
        'url'      => home . generatePluginLink('pm', null, ['action' => 'read', 'id' => $messageId]),
        'datetime' => date('Y-m-d H:i:s'),
    ];
    ngcms_tg_notify('pm', $pmData);
}
```

### Complain (Жалобы на новости)

Файл: `engine/plugins/complain/complain.php`

В функцию `plugin_complain_add()` после успешной отправки:

```php
// После msg(array("type" => "info", ...))

if (function_exists('ngcms_tg_notify')) {
    $complainData = [
        'title'    => 'Жалоба на новость',
        'author'   => $userROW['name'] ?? 'Гость',
        'text'     => strip_tags($_POST['description'] ?? ''),
        'url'      => home . '/admin.php?mod=extra-config&plugin=complain',
        'datetime' => date('Y-m-d H:i:s'),
    ];
    ngcms_tg_notify('complain', $complainData);
}
```

### Forum Complaints (Жалобы на форуме)

Файл: `engine/plugins/forum/action/complaints.php`

После INSERT в `_forum_complaints`:

```php
// После строки $mysql->query('INSERT INTO ...')

if (function_exists('ngcms_tg_notify')) {
    $forumComplainData = [
        'title'    => 'Жалоба на сообщение форума',
        'author'   => $userROW['name'],
        'text'     => strip_tags($message),
        'url'      => home . link_topic($id, 'pid') . '#' . $id,
        'datetime' => date('Y-m-d H:i:s', $time),
    ];
    ngcms_tg_notify('complain', $forumComplainData);
}
```

### Comments (Комментарии)

Файл: `engine/plugins/comments/comments.php`

В функцию добавления комментария после INSERT:

```php
// После успешной вставки комментария

if (function_exists('ngcms_tg_notify')) {
    $commentData = [
        'title'    => 'Новый комментарий к новости',
        'author'   => $userROW['name'] ?? $_POST['name'] ?? 'Гость',
        'text'     => strip_tags($commentText),
        'url'      => home . newsGenerateLink($newsRow),
        'datetime' => date('Y-m-d H:i:s'),
    ];
    ngcms_tg_notify('comment', $commentData);
}
```

## Настройка

1. Установите плагин `jchat_tgnotify`
2. Перейдите в админ-панель → Плагины → jChat Telegram Уведомления
3. Включите уведомления и настройте:
   - Bot Token (от @BotFather)
   - Chat ID (ваш ID из @userinfobot)
4. Включите нужные типы уведомлений (галочки)
5. Добавьте код интеграции в соответствующие плагины

## Типы уведомлений

- `jchat` - Сообщения в чате (уже интегрировано)
- `feedback` - Формы обратной связи
- `pm` - Личные сообщения
- `complain` - Жалобы
- `comment` - Комментарии

## Безопасность

Функция `ngcms_tg_notify()`:

- Автоматически санитизирует все данные через `sanitize()`
- Удаляет HTML-теги из текста
- Логирует все операции в `engine/data/logs/plugins.log`
- Использует SSL верификацию для HTTPS запросов

## Отладка

Все уведомления логируются в `engine/data/logs/plugins.log`:

```
[ngcms_tg_notify] Уведомление feedback успешно отправлено
[ngcms_tg_notify] Уведомления типа pm отключены
```
