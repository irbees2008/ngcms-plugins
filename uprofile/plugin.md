---
id: "uprofile"
icons: "<i class=\"fa fa-user-o fa-3x\" aria-hidden=\"true\"></i>"
name: "Users profile"
version: "0.16"
acts: "ppages,rpc"
file: "uprofile.php"
config: "config.php"
type: "plugin"
description: "Управление/отображение профиля пользователя"
author: "Vitaly A. Ponomarev,irbees2008"
author_uri: "http://ngcms.org/"
library:
  - "lib; lib/uprofile.lib.php"
minenginebuild: "23b3116"
title: "Профиль пользователя"
information: "Позволяет пользователя просматривать чужие профили и редактировать свой."
preinstall: "yes"
---
# Просмотр и редактирование профиля пользователя

Плагин предоставляет просмотр профилей пользователей и редактирование собственного профиля.

Используются шаблоны:

- `users.tpl` — просмотр своего или чужого профиля.
- `profile.tpl` — редактирование собственного профиля.

## Шаблон `users.tpl`

Шаблон отображает профиль любого пользователя, в том числе собственный в режиме «как меня видят другие». Некоторые переменные сохранены для совместимости с предыдущими версиями.

Доступные переменные:

- `user` — данные пользователя из таблицы `users`:
  - `id` — ID пользователя.
  - `name` — логин.
  - `news` — количество новостей.
  - `com` — количество комментариев.
  - `status` — название группы пользователя.
  - `last` — дата и время последнего посещения.
  - `reg` — дата и время регистрации.
  - `from` — поле «Откуда».
  - `info` — поле «Информация обо мне».
  - `flags.hasAvatar` — признак наличия аватара.
  - `flags.isOwnProfile` — признак просмотра собственного профиля.
- `token` — токен безопасности для RPC-функции `plugin.uprofile.editForm`.

Дополнительные поля плагина `xfields` доступны в ветке `p.xfields`. Для просмотра структуры можно использовать:

```twig
{{ debugValue(p.xfields) }}
```

### Переключение собственного профиля в режим редактирования

Чтобы заменить содержимое профиля формой редактирования:

1. Добавьте ссылку с обработчиком `onclick="ng_uprofile_editCall(); return false"`.
2. Поместите содержимое `users.tpl` в элемент с ID `uprofileReplaceForm`.

```twig
{% if user.flags.isOwnProfile %}
<script>
function ng_uprofile_editCall() {
    $.post('/engine/rpc.php', {
        json: 1,
        methodName: 'plugin.uprofile.editForm',
        rndval: new Date().getTime(),
        params: json_encode({ token: '{{ token }}' })
    }, function (data) {
        try {
            resTX = eval('(' + data + ')');
        } catch (err) {
            alert('Error parsing JSON output. Result: ' + linkTX.response);
        }
        if (!resTX.status) {
            ngNotifyWindow('Error [' + resTX.errorCode + ']: ' + resTX.errorText, 'ERROR');
        } else {
            $('#uprofileReplaceForm').html(resTX.data);
        }
    }).error(function () {
        ngNotifyWindow('HTTP error during request', 'ERROR');
    });
}
</script>
{% endif %}
```

## Шаблон `profile.tpl`

Шаблон используется для редактирования собственного профиля. Доступны переменные:

- `user` — данные пользователя:
  - `id`, `name`, `news`, `com`, `status`, `last`, `reg`, `email`, `from`, `info`;
  - `flags.hasAvatar` — признак наличия аватара.
- `flags.avatarAllowed` — разрешены ли пользователям аватары.
- `info_sizelimit_text` — сообщение о превышении ограничения поля «Информация обо мне».
- `info_sizelimit` — максимальный размер поля `info` в символах.
- `form_action` — URL для отправки формы сохранения профиля.
- `token` — токен безопасности для изменения профиля.

Форма должна отправлять POST-запрос на `{{ form_action }}` и содержать скрытое поле `token` со значением `{{ token }}`.

Поля формы:

| Имя поля | Назначение |
|---|---|
| `editemail` | Значение `{{ user.email }}` |
| `editfrom` | Значение `{{ user.from }}` |
| `editabout` | Значение `{{ user.about }}` |
| `editpassword` | Новый пароль |
| `oldpass` | Старый пароль; требуется только при смене пароля |
| `newavatar` | Поле типа `file` для загрузки аватара |
| `delavatar` | Поле типа `checkbox` для удаления аватара |

Пример формы управления аватаром:

```twig
{% if flags.avatarAllowed %}
    <input type="file" name="newavatar" size="40" />
    {% if user.flags.hasAvatar %}
        <img src="{{ user.avatar }}" style="margin: 5px; border: 0" alt="" />
        <input type="checkbox" name="delavatar" id="delavatar" class="check" />
        <label for="delavatar">{{ lang.uprofile['delete'] }}</label>
    {% endif %}
{% else %}
    {{ lang.uprofile['avatars_denied'] }}
{% endif %}
```

### Интеграция с закладками

Для вывода количества закладок и ссылки на них используйте:

```twig
{% if user.bookmarks_count %}
    {{ lang.uprofile['bookmarks'] }}:
    <a href="{{ user.bookmarks_link }}">{{ user.bookmarks_count }}</a>
{% else %}
    {{ lang.uprofile['bookmarks'] }}: 0
{% endif %}
```

В `users.tpl` также поддерживается переменная `{plugin_bookmarks}`.
