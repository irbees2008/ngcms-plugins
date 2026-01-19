# AVTEK Callback (avtek_callback) — документация (актуальная версия: embed-only)

## Что делает плагин
AVTEK Callback — конструктор форм обратной связи для NGCMS:
- Создание нескольких форм в админке (разные поля, разные получатели).
- Отправка заявок на Email (и при необходимости — в Telegram/CRM через настройки/хук).
- Хранение заявок в базе (раздел «Заявки») + экспорт XLS.
- Вставка формы в шаблоны NGCMS через TWIG (рекомендуется) или через fetch (fallback).

> В этой версии **модальное окно не используется** (оставляем только встроенную форму / embed).

---

## Требования
- NGCMS
- PHP 8.x

---

## Установка
1) Загрузить папку плагина:
`/engine/plugins/avtek_callback/`

2) В админке NGCMS:
**Утилиты → Плагины (extras) → avtek_callback → Установить**

3) Настройки плагина:
`/engine/admin.php?mod=extra-config&plugin=avtek_callback`

---

## Создание формы
Админка → **Формы** → **+ Новая форма**:
- Название, slug
- Email получатели (если пусто — берутся из «Глобальных настроек»)
- Выбор стандартных полей + кастомные поля
- Переименование label каждого поля (на нужный язык/формулировку)
- Сохранить

---

## Вставка формы в шаблон (TWIG) — основной способ
Вставьте в нужное место шаблона темы (например, `main.tpl` или нужный `.tpl`):

```twig
{{ callPlugin('avtek_callback.show', {'id': 1, 'mode': 'embed'})|raw }}
```

- `id` — ID формы из админки
- `|raw` обязателен, иначе HTML будет экранирован

---

## Если TWIG-вставка не выводится — fallback через fetch (HTML+JS)
Вставьте в шаблон:

```html
<div id="avtek_cb_embed_1"></div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  try {
    const html = await fetch('/plugin/avtek_callback/?sub=render&form_id=1&mode=embed', {credentials:'same-origin'}).then(r=>r.text());
    const el = document.getElementById('avtek_cb_embed_1');
    if (el) el.innerHTML = html || '';
  } catch (e) {}
});
</script>
```

---

## ВАЖНО: чтобы после отправки НЕ происходил переход на /?sub=submit
По умолчанию endpoint отправки:
`/plugin/avtek_callback/?sub=submit`

Если форма отправляется обычным POST (без JS), браузер **перейдёт** на этот URL.
Чтобы **оставаться на текущей странице** и показывать **только текст “Спасибо…”** (или делать редирект),
нужно добавить **один общий JS-перехватчик submit** (вставляется 1 раз на сайте).

### Скрипт AJAX-отправки (вставить один раз в шаблон, например в main.tpl перед </body>)
```html
<script>
(function () {
  function isCallbackForm(form) {
    if (!form || !form.action) return false;
    return form.action.indexOf('/plugin/avtek_callback/') !== -1 &&
           form.action.indexOf('sub=submit') !== -1;
  }

  document.addEventListener('submit', async function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!isCallbackForm(form)) return;

    e.preventDefault();

    const fd = new FormData(form);

    // Управление поведением
    const successText = form.dataset.successText || 'Спасибо! Заявка отправлена.';
    const successMode = form.dataset.successMode || 'inline';   // inline | redirect
    const redirectUrl = form.dataset.redirectUrl || '';

    try {
      const r = await fetch(form.action, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });

      if (!r.ok) throw new Error('HTTP ' + r.status);

      if (successMode === 'redirect' && redirectUrl) {
        window.location.href = redirectUrl;
        return;
      }

      // Inline: заменить форму на короткий текст
      form.innerHTML = '<div class="avtek_cb_thanks">' + successText + '</div>';

    } catch (err) {
      alert('Ошибка отправки формы. Проверь соединение/настройки.');
    }
  }, true);
})();
</script>
```

---

## Endpoint для проверки (диагностика)
Проверить, что форма рендерится:
`/plugin/avtek_callback/?sub=render&form_id=1&mode=embed`

---

## Примечание по Telegram/CRM
Разделы Telegram/CRM остаются в настройках, но могут требовать отдельной настройки токена/endpoint.
Если нужно — добавим в документацию конкретные примеры под Telegram Bot API и популярные CRM webhook.
