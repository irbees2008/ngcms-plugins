# Обновление плагина Similar до версии 2026-05-24

## Что изменено

### 1. Добавлена поддержка Twig

Плагин теперь использует современный Twig-движок для рендеринга шаблонов вместо устаревшего TPL.

### 2. Современные переменные news.\*

Вместо старых переменных `{date}`, `{url}`, `{title}` теперь используется полный объект `news` с современным синтаксисом:

- `{{ news.date }}` - дата новости
- `{{ news.url.full }}` - полная ссылка
- `{{ news.title }}` - заголовок
- `{{ news.author.name }}` - автор
- `{{ news.short }}` - краткое содержание
- `{{ news.categories.masterText }}` - категория
- `{{ news.embed.images[0] }}` - первое изображение
- `{{ news.embed.imgCount }}` - количество изображений

### 3. Использование newsFillVariables()

Плагин использует функцию `newsFillVariables()` из `libnews.php`, которая создает полноценный объект news со всеми современными переменными и методами.

### 4. Fallback на старый движок

Если Twig-шаблон не найден, плагин автоматически переключается на старый TPL-движок для обратной совместимости.

## Что нужно сделать после обновления

### Шаг 1: Проверить шаблоны

Убедитесь, что файлы `similar.tpl` и `similar_entry.tpl` используют современный синтаксис:

**similar.tpl:**

```twig
<div class="articles-slider">
	<span class="next-slide"></span>
	<span class="prev-slide"></span>
	<div class="article-slider">
		<ul>
			{{ entries }}
		</ul>
	</div>
</div>
```

**similar_entry.tpl:**

```twig
<li class="article">
	<span class="article-img">
		{% if (news.embed.imgCount > 0) %}
			<img src="{{ news.embed.images[0] }}" width="315" height="161"/>
		{% else %}
			<img src="{{ tpl_url }}/images/img-none.png" width="315" height="161"/>
		{% endif %}
		<div class="article-cat"><a href="#">Категория</a></div>
	</span>
	<span class="article-title"><a href="{{ news.url.full }}">{{ news.title }}</a></span>
	<span class="article-meta"><span>{{ news.date }}</span> | <span>{{ news.author.name }}</span></span>
</li>
```

### Шаг 2: Восстановить индексы

1. Откройте админ-панель: `http://localhost/admin.php?mod=extra-config`
2. Найдите плагин **"Похожие новости (similar)"**
3. Нажмите кнопку **"Восстановление индексов"**
4. Дождитесь завершения процесса (может занять несколько минут в зависимости от количества новостей)

### Шаг 3: Очистить кэш

```powershell
Remove-Item "C:\OSPanel\home\it\engine\cache\similar\*" -Recurse -Force
Remove-Item "C:\OSPanel\home\it\engine\cache\main\_templates\*" -Recurse -Force
```

### Шаг 4: Проверить работу

1. Откройте любую полную новость на сайте
2. Проверьте блок "Рекомендуем похожее:"
3. Убедитесь, что новости отображаются корректно с изображениями, заголовками и датами

## Откат изменений

Если что-то пошло не так, можно откатиться к старой версии:

```powershell
Copy-Item "C:\OSPanel\home\it\engine\plugins\similar\similar.php.backup" `
          "C:\OSPanel\home\it\engine\plugins\similar\similar.php" -Force
```

## Технические детали

### Изменения в коде

**Добавлено в начало файла:**

```php
// Load news functions library for newsFillVariables()
include_once root . 'includes/inc/libnews.php';
```

**Заменена логика создания переменных:**

```php
// Старый код (УДАЛЕНО):
$txvars['vars']['date'] = str_replace(...);
$txvars['vars']['title'] = $similar['si_refNewsTitle'];
$txvars['vars']['url'] = newsGenerateLink($similar);

// Новый код:
$tvarsEntry = newsFillVariables($similar, 0, 0, 0, []);
```

**Добавлен Twig-рендеринг (ИСПРАВЛЕНО 24.05.2026):**

```php
// ПРАВИЛЬНЫЙ способ работы с Twig в NGCMS:
if (file_exists($tpath['similar_entry'])) {
    $templateContent = file_get_contents($tpath['similar_entry']);
    $twigTemplate = $twig->createTemplate($templateContent);
    $entryOutput = $twigTemplate->render($tvarsEntry['vars']);
}
```

**ВАЖНО:** Используется `createTemplate()` (создание из строки), а не `loadTemplate()` (загрузка по пути), так как Twig в NGCMS ожидает абсолютный путь к файлу для чтения через `file_get_contents()`, а `loadTemplate()` работает с относительными путями от базовой директории шаблонов.

## Преимущества обновления

✅ Полная совместимость с современными шаблонами NGCMS
✅ Доступ ко всем переменным объекта news (категории, изображения, xfields и т.д.)
✅ Поддержка плагинов через фильтры showNewsPre() и showNews()
✅ Логирование для отладки через logger()
✅ Кэширование результатов на 5 минут
✅ Fallback на старый движок для обратной совместимости

## Поддержка

Если возникли проблемы:

1. Проверьте лог-файл: `engine/cache/logs/similar.log`
2. Убедитесь, что плагин "tags" активен (similar зависит от него)
3. Проверьте, что в настройках плагина установлено корректное количество новостей (1-20)
4. Убедитесь, что восстановление индексов выполнено после обновления

---

**Дата обновления:** 24 мая 2026 г.
**Версия:** 2026-05-24
**Совместимость:** NGCMS 0.9.7rc2 и выше
