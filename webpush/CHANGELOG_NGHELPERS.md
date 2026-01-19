# WebPush Plugin - ng-helpers Modernization Changelog
## Обзор изменений
Плагин `webpush` (Web Push уведомления) был модернизирован с подходами из ng-helpers v0.2.0+.
## Версия файлов: webpush.php, endpoint.php, send.php
**Дата модернизации:** 11 января 2026
---
## 1. Логирование всех операций
### ✅ Новая функциональность:
**Автономная функция логирования для standalone скриптов:**
```php
function webpush_log($message, $level = 'info') {
    global $root;
    $logDir = $root . '/engine/plugins/webpush/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/webpush.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $entry = "[$timestamp] [$level] $message | IP: $ip\n";
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}
```
**Преимущества:**
- ✅ Работает в standalone скриптах (endpoint.php, send.php)
- ✅ Автоматическое создание директории логов
- ✅ Фиксация IP-адресов
- ✅ Уровни логирования (info, warning, error)
---
## 2. Валидация URL endpoint
### ✅ Новая проверка:
```php
// Валидация URL endpoint
if (!validate_webpush_url($endpoint)) {
    webpush_log('Invalid endpoint URL: ' . substr($endpoint, 0, 100), 'warning');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid endpoint URL'], JSON_UNESCAPED_UNICODE);
    exit;
}
```
**Функция валидации:**
```php
function validate_webpush_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
```
**Преимущества:**
- ✅ Защита от невалидных URL подписок
- ✅ Предотвращение ошибок при отправке уведомлений
- ✅ Логирование попыток с неправильными URL
- ✅ Фильтрация подозрительных данных
---
## 3. Определение типа устройства
### ✅ Новая функциональность:
```php
function is_webpush_mobile() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/(android|iphone|ipad|mobile)/i', $ua);
}
// При подписке
$deviceType = is_webpush_mobile() ? 'mobile' : 'desktop';
webpush_log('Subscribe: ' . parse_url($endpoint, PHP_URL_HOST) . ' (' . $deviceType . ')');
```
**Преимущества:**
- ✅ Статистика по типам устройств
- ✅ Возможность целевых рассылок
- ✅ Аналитика мобильных vs десктоп подписчиков
---
## 4. Улучшенное логирование операций
### Логируемые события:
**1. Запрос публичного ключа:**
```php
webpush_log('Public key requested');
```
**2. Подписка:**
```php
webpush_log('Subscribe: fcm.googleapis.com (mobile)');
```
**3. Отписка:**
```php
webpush_log('Unsubscribe: fcm.googleapis.com');
```
**4. Отправка уведомлений:**
```php
webpush_log(sprintf(
    'Push sent: title="%s", sent=%d, removed=%d, total=%d',
    substr($title, 0, 50),
    $sent,
    $removed,
    count($subscriptions)
));
```
**5. Генерация VAPID ключей:**
```php
webpush_log('VAPID keys generated');
```
**6. Ошибки:**
```php
webpush_log('WebPush disabled, endpoint access denied', 'warning');
webpush_log('Send attempt with invalid secret', 'warning');
webpush_log('Invalid endpoint URL: ...', 'warning');
webpush_log('VAPID key generation failed: ...', 'error');
```
**7. Запрос статистики (webpush.php):**
```php
logger('webpush', sprintf('Stats requested: total=%d, today=%d, week=%d, IP=%s',
    $stats['total'], $stats['today'], $stats['week'], get_ip()));
```
---
## Использованные функции ng-helpers
| Функция     | Категория | Применение                                       |
| ----------- | --------- | ------------------------------------------------ |
| `logger`    | System    | Логирование в webpush.php (интегрирован с NGCMS) |
| `get_ip`    | Request   | Получение IP для логирования статистики          |
| `is_mobile` | Request   | Определение мобильных устройств (концепция)      |
**Примечание:** Для standalone скриптов (endpoint.php, send.php) созданы автономные функции логирования по образцу ng-helpers.
---
## Файлы логов
Плагин создает следующие логи (в `engine/plugins/webpush/logs/`):
- **webpush.log** - единый лог всех операций
  - Запросы публичного ключа
  - Подписки и отписки (с типом устройства)
  - Отправка уведомлений (с результатами)
  - Генерация VAPID ключей
  - Попытки доступа с невалидными данными (warning)
  - Ошибки операций (error)
---
## Безопасность
### Улучшения безопасности:
1. **Валидация endpoint:**
   - Проверка URL формата через `filter_var(FILTER_VALIDATE_URL)`
   - Предотвращение инъекций невалидных данных
   - Логирование подозрительных попыток
2. **Аудит операций:**
   - Все действия логируются с IP-адресами
   - Warning для неудачных попыток
   - Error для критических ошибок
3. **Защита от несанкционированного доступа:**
   - Логирование попыток с неверным секретным ключом
   - Отслеживание IP-адресов злоумышленников
4. **Мониторинг:**
   - Статистика подписок с IP
   - История отправленных уведомлений
   - Аналитика по устройствам
---
## Совместимость
- **PHP:** 7.4+ (рекомендуется 8.0+)
- **NGCMS:** 0.9.4+
- **ng-helpers:** v0.2.0+ (для webpush.php)
- **Web Push API:** Service Worker совместимые браузеры
---
## Обратная совместимость
✅ Все изменения обратно совместимы:
- API плагина не изменился
- База данных не изменилась
- JSON ответы сохранены
- Конфигурация совместима
---
## Тестирование
### Рекомендуемые проверки:
1. **Проверка логирования подписок:**
   ```bash
   # Подписаться на уведомления через браузер
   # Проверить лог: должна быть запись "Subscribe"
   tail -f engine/plugins/webpush/logs/webpush.log
   ```
2. **Проверка валидации URL:**
   ```bash
   # Попробовать отправить невалидный endpoint (через dev tools)
   # Должна быть ошибка 400 и warning в логе
   tail -20 engine/plugins/webpush/logs/webpush.log
   ```
3. **Проверка отправки уведомлений:**
   ```bash
   # Отправить тестовое уведомление
   curl -X POST "http://site.ru/engine/plugins/webpush/send.php?secret=YOUR_SECRET" \
        -d "title=Test&body=Message"
   # Проверить лог: "Push sent: title=..."
   tail -5 engine/plugins/webpush/logs/webpush.log
   ```
4. **Проверка определения устройства:**
   ```bash
   # Подписаться с мобильного и десктопа
   # В логе должно быть: (mobile) и (desktop)
   grep "Subscribe" engine/plugins/webpush/logs/webpush.log
   ```
5. **Проверка статистики:**
   ```php
   // Вызвать webpush_get_stats()
   // В логе должна появиться запись с цифрами
   ```
---
## Примеры логов
### 1. Запрос публичного ключа:
```
[2026-01-11 15:30:15] [info] Public key requested | IP: 192.168.1.100
```
### 2. Подписка (мобильное устройство):
```
[2026-01-11 15:30:45] [info] Subscribe: fcm.googleapis.com (mobile) | IP: 192.168.1.100
```
### 3. Подписка (десктоп):
```
[2026-01-11 15:31:20] [info] Subscribe: updates.push.services.mozilla.com (desktop) | IP: 192.168.1.101
```
### 4. Отписка:
```
[2026-01-11 15:32:10] [info] Unsubscribe: fcm.googleapis.com | IP: 192.168.1.100
```
### 5. Отправка уведомлений:
```
[2026-01-11 15:35:00] [info] Push sent: title="Новая статья на сайте", sent=127, removed=3, total=130 | IP: 127.0.0.1
```
### 6. Невалидный endpoint (warning):
```
[2026-01-11 15:36:30] [warning] Invalid endpoint URL: not-a-valid-url | IP: 192.168.1.200
```
### 7. Попытка с неверным секретом (warning):
```
[2026-01-11 15:37:15] [warning] Send attempt with invalid secret | IP: 192.168.1.250
```
### 8. Генерация VAPID ключей:
```
[2026-01-11 15:40:00] [info] VAPID keys generated | IP: 127.0.0.1
```
### 9. Запрос статистики (из webpush.php):
```
[2026-01-11 16:00:00] [info] Stats requested: total=127, today=15, week=48, IP=192.168.1.10
```
---
## Рекомендации по использованию
1. **Мониторинг логов:**
   - Регулярно проверять `webpush.log`
   - Обращать внимание на warning и error
   - Анализировать популярность push-уведомлений
2. **Безопасность:**
   - Отслеживать IP с warning записями
   - Блокировать IP при массовых попытках с невалидными данными
   - Менять send_secret при подозрении на компрометацию
3. **Аналитика:**
   - Использовать логи для анализа устройств (mobile vs desktop)
   - Отслеживать динамику подписок/отписок
   - Оценивать эффективность рассылок (sent/total)
4. **Производительность:**
   - При большом объеме настроить ротацию логов
   - Использовать removed статистику для очистки БД
5. **Отладка:**
   - При проблемах с push проверить логи
   - IP-адреса помогают идентифицировать проблемных пользователей
---
## Дальнейшие улучшения (опционально)
Возможные будущие улучшения:
1. **Кэширование:**
   - Использовать `cache_get/put` для VAPID ключей
   - Кэширование статистики подписок
2. **Улучшенная аналитика:**
   - Парсинг логов для графиков
   - Статистика по браузерам
   - Карта подписчиков по IP
3. **Уведомления:**
   - Alert при большом количестве removed
   - Email при ошибках отправки
4. **A/B тестирование:**
   - Разные уведомления для mobile/desktop
   - Анализ эффективности по устройствам
---
## Автор модернизации
GitHub Copilot с использованием подходов ng-helpers v0.2.0
---
## Заключение
Плагин webpush успешно модернизирован:
- ✅ **Логирование:** полная история всех операций с IP
- ✅ **Валидация:** проверка URL endpoint перед сохранением
- ✅ **Аналитика:** определение типа устройства (mobile/desktop)
- ✅ **Безопасность:** логирование подозрительной активности
- ✅ **Мониторинг:** детальные логи для отладки и аудита
Все изменения направлены на улучшение мониторинга Web Push уведомлений и повышение безопасности без нарушения обратной совместимости.
