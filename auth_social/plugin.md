---
id: "auth_social"
icons: "<i class=\"fa fa-sign-in fa-3x\" aria-hidden=\"true\"></i>"
name: "social networks auth"
version: "0.8"
acts: "ppages, action.ppages.uprofile, usermenu"
file: "social.php"
config: "config.php"
install: "install.php"
deinstall: "uninstall.php"
type: "plugin"
description: "Аутентификация через ВК, Google, Yandex, Facebook и т.д."
author: "Rostunov Sergey, Stanislav+ИИ"
author_uri: "http://rostunov.com/"
minenginebuild: "23b3116"
information: "Аутентификация через ВК, Google, Yandex, Facebook и т.д."
preinstall: "no"
---
# Авторизация через социальные сети

Плагин не является самостоятельным модулем авторизации: он дополняет уже
работающий плагин авторизации, например `auth_basic`.

Поддерживаются VK ID, Yandex, Google, Facebook и GitHub.

## Установка и настройка

1. Зарегистрируйте приложения у нужных провайдеров. В качестве `redirect_uri`
   укажите соответствующие адреса:

   | Провайдер | Адрес обратного вызова |
   | --- | --- |
   | VK ID | `https://sitename.ru/plugin/auth_social/vkid/` |
   | Yandex | `https://sitename.ru/plugin/auth_social/yandex/` |
   | Google | `https://sitename.ru/plugin/auth_social/google/` |
   | Facebook | `https://sitename.ru/plugin/auth_social/facebook/` |
   | GitHub | `https://sitename.ru/plugin/auth_social/github/` |

   Дополнительные сведения о регистрации приложений: [SocialAuther](https://github.com/stanislas-prime/SocialAuther).
   Для VK ID получите Client ID и Client Secret в [документации VK ID](https://id.vk.com/about/business/go/docs/ru/vkid/latest/vk-id/connection/create-app).
   Запрашиваемый scope для VK ID — `email`, но VK может не вернуть адрес даже при его запросе.
   Для GitHub рекомендуется scope `read:user, user:email`.
2. Включите плагин и внесите в его настройки данные приложений:
   - VK ID: `vkid_client_id`, `vkid_client_secret`, `vkid_scope`;
   - другие провайдеры: `client_id`, `client_secret`, `public_key` (если требуется).
3. Добавьте ссылки авторизации в `usermenu.tpl`. Примеры:

   ```html
   <!-- VK ID -->
   <a href="{{p.auth_social.vkid.authUrl}}" title="VK ID"><img src="/engine/plugins/auth_social/social/VK.png" alt="VK ID"/></a>

   <!-- Остальные провайдеры -->
   <a href="{{p.auth_social.yandex.authUrl}}" title="{{p.auth_social.yandex.title}}"><img src="/engine/plugins/auth_social/social/ya.png" alt="{{p.auth_social.yandex.title}}"/></a>
   <a href="{{p.auth_social.google.authUrl}}" title="{{p.auth_social.google.title}}"><img src="/engine/plugins/auth_social/social/G.png" alt="{{p.auth_social.google.title}}"/></a>
   <a href="{{p.auth_social.facebook.authUrl}}" title="{{p.auth_social.facebook.title}}"><img src="/engine/plugins/auth_social/social/FB.png" alt="{{p.auth_social.facebook.title}}"/></a>
   <a href="{{p.auth_social.github.authUrl}}" title="{{p.auth_social.github.title}}"><img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="{{p.auth_social.github.title}}" style="width:32px;height:32px"/></a>
   ```

## Интеграция с ng-helpers

Плагин использует функции ng-helpers для валидации и логирования:

- `validate_email()` проверяет адреса электронной почты, полученные от OAuth-провайдеров, и помогает не допустить регистрацию с некорректным адресом.
- `random_string()` генерирует криптографически стойкие строки для PKCE-параметров `state` и `code_verifier`.
- `logger()` записывает операции OAuth, регистрации и IP-адреса в `engine/cache/logs/auth_social.log`.
- `get_ip()` получает IP-адрес посетителя, в том числе при работе через прокси и CDN.

Примеры записей журнала:

```text
[2026-01-29 14:30:45] [INFO] VK ID OAuth redirect initiated, IP: 192.168.1.100
[2026-01-29 14:30:46] [INFO] Successful OAuth authentication: vkid user Иван (ivan@example.com), IP: 192.168.1.100
[2026-01-29 14:31:20] [WARNING] Invalid email from google: invalid-email, IP: 192.168.1.100
```

## Особенности VK ID

- Используется OAuth 2.0 с PKCE (Proof Key for Code Exchange).
- Реализован отдельный адаптер `Adapter\Vkid`.
- Поддерживаются `code_verifier` и `code_challenge` для обмена токенами.
- Состояние сессии управляется автоматически для защиты от CSRF.
- Аватары загружаются при первой регистрации.
- Email может быть недоступен даже при запросе `scope=email`.

## Интеграция с профилем uprofile

При установке плагин добавляет в таблицу `users` поля `provider`, `social_id`,
`social_page`, `sex` и `birthday`. Данные могут отсутствовать, а их формат может
зависеть от социальной сети.

### Шаблон `templates/ваш_шаблон/plugins/uprofile/users.tpl`

Доступны переменные `userRec.provider` (код провайдера: `vkid`, `yandex`, `google`,
`facebook` или `github`), `userRec.social_id` (ID пользователя), `userRec.social_page`
(ссылка на профиль), `userRec.sex` (пол, `male`, `female` или пустое значение) и
`userRec.birthday` (дата в формате `YYYY-MM-DD`).

```twig
{% if (userRec.provider) and (userRec.social_page) %}
    <tr>
        <td>Профиль соцсети:</td>
        <td class="second"><a href="{{ userRec.social_page }}" target="_blank">{{ userRec.provider }}</a></td>
    </tr>
{% endif %}
{% if (userRec.provider) and (userRec.sex) %}
    <tr>
        <td>Пол:</td>
        <td class="second">{% if userRec.sex == 'male' %}Мужской{% elseif userRec.sex == 'female' %}Женский{% else %}{{ userRec.sex }}{% endif %}</td>
    </tr>
{% endif %}
{% if (userRec.provider) and (userRec.birthday) %}
    <tr>
        <td>Дата рождения:</td>
        <td class="second">{{ userRec.birthday }}</td>
    </tr>
{% endif %}
```

### Шаблон `templates/ваш_шаблон/plugins/uprofile/profile.tpl`

В форме редактирования профиля можно использовать `userRec.sex` и
`userRec.birthday`:

```html
<div class="label label-table">
    <label>Пол:</label>
    <select name="editsex" class="input">
        <option value="">Не указан</option>
        <option value="male"{% if userRec.sex == 'male' %} selected{% endif %}>Мужской</option>
        <option value="female"{% if userRec.sex == 'female' %} selected{% endif %}>Женский</option>
    </select>
</div>
<div class="label label-table">
    <label>Дата рождения (YYYY-MM-DD):</label>
    <input type="date" name="editbirthday" value="{{ userRec.birthday }}" class="input" />
</div>
```

## Отладка и логирование

Основной журнал ng-helpers находится в `engine/cache/logs/auth_social.log`. Он
содержит сведения о перенаправлениях OAuth, успешных аутентификациях и создании
пользователей, предупреждения о некорректном email и IP-адреса.

Для включения дополнительной отладки раскомментируйте строки `// DEBUG:` в файлах:

- `engine/plugins/auth_social/social.php`;
- `engine/plugins/auth_social/lib/SocialAuther/Adapter/Vkid.php`.

Детальные отладочные записи сохраняются в `engine/plugins/auth_social/log.txt`.

## Известные ограничения

1. VK ID может не возвращать email даже при scope `email`.
2. Длинные URL аватаров не сохраняются в поле `avatar`; аватары загружаются локально.
3. При первой регистрации через социальную сеть пользователь получает случайный пароль.
4. Для корректной работы OAuth-провайдеров требуется HTTPS.
