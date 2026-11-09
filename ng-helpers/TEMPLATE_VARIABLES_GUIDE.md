# Новые переменные шаблонов в модернизированных плагинах

## 📋 Краткая справка

### 1. **lastcomments** - Последние комментарии

**Система шаблонов:** Twig

**Новая переменная:** `{time_ago}` / `{{ entry.time_ago }}`

**Использование:**

**Старый синтаксис (конвертируется автоматически):**

```twig
<span class="comment-time">{time_ago}</span>
```

**Twig синтаксис:**

```twig
<span class="comment-time">{{ entry.time_ago }}</span>
```

**Значения:**

- "только что"
- "5 минут назад"
- "2 часа назад"
- "вчера"
- "3 дня назад"

**Конверсия шаблона:**

```php
$conversionConfig = array(
    '{time_ago}' => '{{ entry.time_ago }}',
    // автоматически в lastcomments.php
);
```

---

### 2. **rating** - Рейтинги

**Система шаблонов:** Старая ($tpl), не Twig

**Новая переменная:** `{rating_percent}`

**Использование:**

```html
<div class="rating-bar">
  <div class="rating-fill" style="width: {rating_percent}%"></div>
</div>

<span>Рейтинг: {rating_percent}% ({votes} голосов)</span>
```

**Доступные переменные:**

```html
{rating}
<!-- Средний рейтинг: 4 -->
{rating_percent}
<!-- Процент: 80% (НОВОЕ!) -->
{votes}
<!-- Количество голосов: 25 -->
```

**Примечание:** Переменные доступны напрямую через `{переменная}` без Twig синтаксиса, так как плагин использует старую систему шаблонов.

---

### 3. **similar** - Похожие новости

**Система шаблонов:** Старая ($tpl), не Twig

**Новые переменные:** Нет

**Изменения:** Все улучшения касаются внутренней логики (кэширование, array_pluck, логирование). Переменные шаблонов остались без изменений.

---

## 🔧 Технические детали

### Twig конверсия (lastcomments)

**Как работает:**

1. Старый синтаксис `{переменная}` в шаблоне
2. Система конверсии преобразует в `{{ entry.переменная }}`
3. Twig рендерит шаблон

**Файл:** `lastcomments.php`, строки ~213-232

```php
$conversionConfig = array(
    '{tpl_url}'       => '{{ tpl_url }}',
    '{link}'          => '{{ entry.link }}',
    '{date}'          => '{{ entry.date }}',
    '{time_ago}'      => '{{ entry.time_ago }}',  // <-- ДОБАВЛЕНО
    '{author}'        => '{{ entry.author }}',
    // ...
);
$twigLoader->setConversion($tpath[$tpl_prefix . 'lastcomments'] . $tpl_prefix . "lastcomments" . '.tpl', $conversionConfig, $conversionConfigRegex);
```

### Старая система (rating, similar)

**Как работает:**

1. Переменная устанавливается в `$tvars['vars']['переменная']`
2. Передается в шаблон через `$tpl->vars()`
3. Доступна напрямую как `{переменная}`

**Пример (rating.php):**

```php
$tvars['vars']['rating'] = round(($data['rating'] / $data['votes']), 0);
$tvars['vars']['rating_percent'] = percentage($data['rating'], $data['votes'] * 5);
$tvars['vars']['votes'] = $data['votes'];
$tpl->vars('rating', $tvars);
```

---

## 📝 Примеры использования

### lastcomments + time_ago

**Простой вариант:**

```html
<div class="comment-meta">{time_ago}</div>
```

**С tooltip:**

```html
<span class="time-ago" title="{date}">{time_ago}</span>
```

**Twig условие:**

```twig
{% if entry.time_ago %}
    <span>{{ entry.time_ago }}</span>
{% else %}
    <span>{{ entry.date }}</span>
{% endif %}
```

### rating + rating_percent

**Прогресс-бар:**

```html
<div class="rating-progress">
  <div class="rating-bar" style="width: {rating_percent}%"></div>
</div>
<div class="rating-text">{rating}/5 ({votes} голосов)</div>
```

**Цветной badge:**

```html
<span class="badge" style="background: hsl({rating_percent}, 70%, 50%)">
  {rating_percent}%
</span>
```

**Звезды + процент:**

```html
<div class="rating-widget">
  <div class="stars rating-{rating}">★★★★★</div>
  <div class="percent">{rating_percent}%</div>
</div>
```

---

## ✅ Checklist обновления шаблонов

### lastcomments

- [ ] Открыть `tpl/entries.tpl` или `tpl/pp_entries.tpl`
- [ ] Добавить `{time_ago}` или `{{ entry.time_ago }}`
- [ ] Сохранить и проверить отображение
- [ ] Опционально: добавить tooltip с полной датой

### rating

- [ ] Открыть `tpl/skins/basic/rating.tpl` (или свой скин)
- [ ] Добавить `{rating_percent}` в нужное место
- [ ] Использовать для прогресс-бара или процентов
- [ ] Сохранить и проверить

### similar

- [ ] Ничего не требуется
- [ ] Все работает автоматически
- [ ] Кэширование включается само

---

## 🎨 CSS примеры

### Стили для time_ago (lastcomments)

```css
.time-ago {
  color: #888;
  font-size: 0.9em;
  font-style: italic;
}

.comment-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
```

### Стили для rating_percent (rating)

```css
.rating-progress {
  width: 100%;
  height: 20px;
  background: #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
}

.rating-bar {
  height: 100%;
  background: linear-gradient(90deg, #ff6b6b, #4ecdc4);
  transition: width 0.3s ease;
}

.rating-text {
  margin-top: 5px;
  font-size: 0.9em;
  color: #666;
}
```

---

## 🔍 Отладка

### Проверка Twig конверсии (lastcomments)

1. Откройте шаблон `tpl/entries.tpl`
2. Используйте `{time_ago}`
3. Проверьте в браузере - должна отобразиться переменная
4. Если пусто - проверьте массив `$conversionConfig` в PHP

### Проверка старой системы (rating)

1. Откройте `rating.tpl`
2. Добавьте `{rating_percent}`
3. Если пусто - проверьте `$tvars['vars']['rating_percent']` в PHP
4. Используйте `var_dump($tvars)` для отладки

---

## 📚 Документация

Полная документация для каждого плагина:

- `breadcrumbs/CHANGELOG_NGHELPERS.md`
- `feedback/CHANGELOG_NGHELPERS.md`
- `lastcomments/CHANGELOG_NGHELPERS.md`
- `similar/CHANGELOG_NGHELPERS.md`
- `rating/CHANGELOG_NGHELPERS.md`
