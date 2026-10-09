---
id: "avatar_news"
icons: "<i class=\"fa fa-user-o fa-3x\" aria-hidden=\"true\"></i>"
name: "Avatar in News"
version: "0.1"
acts: "news_full"
file: "avatar_news.php"
type: "plugin"
description: "Вывод аватара пользователя, который добавил новость"
author: "stdex"
author_uri: "http://rozard.ngdemo.ru/"
minenginebuild: "23b3116"
title: "Вывод аватара пользователя, который добавил новость"
information: "Вывод аватара пользователя, который добавил новость"
preinstall: "no"
---
# Avatar in News

## Как вывести аватар в новости

Плагин добавляет в контекст шаблона полной новости переменную `avatar_in_news`. Она содержит URL аватара автора, а не готовый HTML-код, поэтому изображение нужно вывести в шаблоне самостоятельно.

Откройте Twig-шаблон полной новости активной темы и вставьте в нужное место:

```twig
{% if avatar_in_news %}
    <img src="{{ avatar_in_news }}" alt="Аватар автора" class="news-author-avatar" loading="lazy">
{% endif %}
```

При необходимости добавьте оформление в CSS активной темы:

```css
.news-author {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.news-author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}
```

Если у автора загружен аватар, используется его файл. Если аватар не задан, плагин учитывает настройку Gravatar в NGCMS; когда Gravatar выключен, выводится стандартное изображение `noavatar.gif`. Аватар добавляется только в шаблон полной новости (`news_full`).
