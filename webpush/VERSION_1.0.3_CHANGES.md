# WebPush Plugin v1.0.3 - Критические исправления

**Дата:** 2026-01-XX
**Версия:** 1.0.3
**Предыдущая:** 1.0.2

## 🔴 Проблема

Плагин был настроен, библиотека Composer установлена, VAPID ключи присутствуют, файл webpush-sw.js в корне сайта есть, но **кнопка Web Push не отображалась на сайте**.

## 🔍 Причины

1. **Использование устаревшего движка шаблонов:**

   ```php
   // СТАРЫЙ КОД (НЕ РАБОТАЕТ):
   $tpl->template('webpush', $tpath['webpush']);
   $tpl->vars('webpush', $tvars);
   $template['vars']['webpush'] = $tpl->show('webpush');
   ```

2. **Неправильное получение настроек:**

   ```php
   // СТАРЫЙ КОД:
   $enabled = extra_get_param('webpush', 'enabled');
   ```

3. **Отсутствие переменной в шаблоне:**
   - В `templates/default/main.tpl` не было вывода `{{ webpush }}`

## ✅ Исправления

### 1. Переход на Twig вместо $tpl

**webpush.php, функция webpush_inject_code():**

```php
// НОВЫЙ КОД (РАБОТАЕТ):
try {
    $xt = $twig->loadTemplate($tpath['webpush'] . 'webpush.tpl');
    $template['vars']['webpush'] = $xt->render($tvars);

    logger('webpush', 'Code injected successfully');
} catch (Exception $e) {
    $template['vars']['webpush'] = '<!-- WebPush: Error rendering template: ' . htmlspecialchars($e->getMessage()) . ' -->';
    logger('webpush', 'Error rendering template: ' . $e->getMessage());
}
```

**Изменения:**

- ✅ Заменено `$tpl->template()`, `$tpl->vars()`, `$tpl->show()`
- ✅ Используется `$twig->loadTemplate()` и `$xt->render()`
- ✅ Добавлена обработка ошибок через `try/catch`

### 2. Использование pluginGetVariable()

```php
// НОВЫЙ КОД:
$enabled = pluginGetVariable('webpush', 'enabled');
$showButton = pluginGetVariable('webpush', 'show_button');
$subscribeText = pluginGetVariable('webpush', 'subscribe_text') ?: 'Включить уведомления';
$publicKey = pluginGetVariable('webpush', 'vapid_public');
```

**Изменения:**

- ✅ Все вызовы `extra_get_param()` заменены на `pluginGetVariable()`
- ✅ Корректное получение настроек из БД

### 3. Интеграция с ng-helpers

```php
// Import ng-helpers functions
use function Plugins\{logger, get_ip, is_mobile};

// Логирование операций:
logger('webpush', sprintf(
    'Injecting code: enabled=%d, showButton=%d, template=%s, IP=%s',
    $enabled,
    $showButton,
    $tpath['webpush'],
    get_ip()
));
```

**Изменения:**

- ✅ Подключены функции `logger()`, `get_ip()`, `is_mobile()`
- ✅ Детальное логирование всех операций
- ✅ Отладочные сообщения в комментариях HTML при ошибках

### 4. Добавление переменной в шаблон

**templates/default/main.tpl (перед `</body>`):**

```twig
{# Web Push уведомления #}
{% if webpush is defined %}{{ webpush|raw }}{% endif %}
```

**Размещение:**

```twig
<script src="{{ tpl_url }}/js/script.js"></script>
{# Вывот накопленных уведомлений (notify.js должен быть подключен выше) #}
{{ notify|raw }}
{# Web Push уведомления #}
{% if webpush is defined %}{{ webpush|raw }}{% endif %}
{# Отладочная информация... #}
...
</body>
```

## 📋 Структура переменных шаблона

**Передаваемые переменные в webpush.tpl:**

```php
$tvars = [
    'endpoint' => home . '/engine/plugins/webpush/endpoint.php',
    'subscribe_text' => pluginGetVariable('webpush', 'subscribe_text') ?: 'Включить уведомления',
    'unsubscribe_text' => $GLOBALS['lang']['webpush:unsubscribe_text'] ?? 'Отключить уведомления',
    'js_path' => home . '/engine/plugins/webpush/js/webpush.js',
    'public_key' => pluginGetVariable('webpush', 'vapid_public'),
];
```

## 🧪 Проверка работоспособности

После обновления до v1.0.3:

1. **Откройте главную страницу сайта**
2. **Проверьте исходный код** (Ctrl+U):

   - Должен быть блок с классом `webpush-container`
   - Скрипт `/engine/plugins/webpush/js/webpush.js`
   - Данные VAPID ключа

3. **Проверьте консоль браузера** (F12):

   - Не должно быть ошибок JavaScript
   - При клике на кнопку должен запроситься permission для уведомлений

4. **Проверьте логи:**
   ```
   /engine/plugins/webpush/logs/webpush.log
   ```
   Должны быть записи:
   ```
   [2026-01-XX HH:MM:SS] [info] Injecting code: enabled=1, showButton=1, template=...
   [2026-01-XX HH:MM:SS] [info] Code injected successfully | IP: xxx.xxx.xxx.xxx
   ```

## 🔧 Отладка

### Если кнопка не появляется:

1. **Проверьте настройки плагина** (в админке):

   - `enabled` = true
   - `show_button` = true

2. **Проверьте исходный код страницы:**

   ```html
   <!-- Если видите это, плагин отключен -->
   <!-- WebPush: disabled -->

   <!-- Если видите это, кнопка скрыта настройками -->
   <!-- WebPush: button hidden -->

   <!-- Если видите это, шаблон не найден -->
   <!-- WebPush: template not found -->
   ```

3. **Проверьте логи:**

   ```bash
   tail -f C:\OSPanel\home\test.ru\engine\plugins\webpush\logs\webpush.log
   ```

4. **Проверьте переменную в шаблоне:**
   Убедитесь, что в `templates/default/main.tpl` есть:
   ```twig
   {% if webpush is defined %}{{ webpush|raw }}{% endif %}
   ```

## 📝 Список измененных файлов

1. ✅ **engine/plugins/webpush/webpush.php**

   - Функция `webpush_inject_code()` полностью переписана
   - Добавлен import ng-helpers функций
   - Логирование всех операций

2. ✅ **engine/plugins/webpush/version**

   - Обновлена версия: 1.0.2 → 1.0.3

3. ✅ **templates/default/main.tpl**
   - Добавлен вывод `{{ webpush|raw }}`

## 🎯 Результат

После применения исправлений:

- ✅ Кнопка Web Push отображается на всех страницах
- ✅ Работает подписка/отписка от уведомлений
- ✅ Логируются все операции
- ✅ Совместимость с ng-helpers v0.2.0+
- ✅ Современный Twig вместо устаревшего $tpl

## ⚠️ Требования

- NGCMS 0.9.3+
- PHP 7.4+
- Twig 3.x
- ng-helpers v0.2.0+ (опционально, для расширенного логирования)
- HTTPS (обязательно для Web Push API)
- Composer пакет `minishlink/web-push`

## 👨‍💻 Автор изменений

**Модернизация:** NGCMS Team
**Базис:** ng-helpers v0.2.0 подход
**Дата:** 2026-01-XX
