# Инструкция по ручной установке библиотек

Если у вас нет Composer, скачайте библиотеки вручную:

## Необходимые библиотеки:

1. **minishlink/web-push** (основная)
   - Скачать: https://github.com/web-push-libs/web-push-php/archive/refs/heads/master.zip
   - Распаковать в: `lib/vendor/minishlink/web-push/`
2. **spomky-labs/base64url**
   - Скачать: https://github.com/Spomky-Labs/base64url/archive/refs/heads/master.zip
   - Распаковать в: `lib/vendor/spomky-labs/base64url/`
3. **paragonie/constant_time_encoding**
   - Скачать: https://github.com/paragonie/constant_time_encoding/archive/refs/heads/master.zip
   - Распаковать в: `lib/vendor/paragonie/constant_time_encoding/`
4. **web-token/jwt-core**
   - Скачать: https://github.com/web-token/jwt-core/archive/refs/heads/master.zip
   - Распаковать в: `lib/vendor/web-token/jwt-core/`
5. **web-token/jwt-signature**
   - Скачать: https://github.com/web-token/jwt-signature/archive/refs/heads/master.zip
   - Распаковать в: `lib/vendor/web-token/jwt-signature/`

## Структура после распаковки:

```
engine/plugins/webpush/lib/vendor/
├── autoload.php (уже создан)
├── minishlink/
│   └── web-push/
│       └── src/
├── spomky-labs/
│   └── base64url/
│       └── src/
├── paragonie/
│   └── constant_time_encoding/
│       └── src/
└── web-token/
    ├── jwt-core/
    └── jwt-signature/
```

## Или используйте Composer:

```bash
cd engine/plugins/webpush
composer install
```

Это автоматически скачает все библиотеки и создаст правильный autoload.php
