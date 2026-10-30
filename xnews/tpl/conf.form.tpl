<form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=save">
  <input type="hidden" name="id" value="{{ profile.id }}">
  <input type="hidden" name="token" value="{{ token }}">
  <div class="card mb-4">
    <div class="card-header"><b>{{ mode == 'add' ? 'Новый блок' : 'Параметры блока' }}</b></div>
    <div class="card-body">
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsBlockName">Идентификатор блока</label>
        <div class="col-sm-8">
          <input id="xnewsBlockName" class="form-control" type="text" name="block_name" maxlength="190" required value="{{ profile.block_name|e('html_attr') }}">
          <small class="form-text text-muted">Используется в Twig-вызове callPlugin('xnews.show', {'id': '...'}). Существующие вызовы сохраняются при миграции.</small>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsTemplate">Каталог шаблона</label>
        <div class="col-sm-8">
          <input id="xnewsTemplate" class="form-control" type="text" name="template" maxlength="190" pattern="[A-Za-z0-9_-]+" required value="{{ profile.template|e('html_attr') }}">
          <small class="form-text text-muted">Каталог должен содержать entries.tpl и xnews.tpl.</small>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsEnabled">Состояние</label>
        <div class="col-sm-8">
          <input type="hidden" name="enabled" value="0">
          <div class="custom-control custom-switch">
            <input id="xnewsEnabled" class="custom-control-input" type="checkbox" name="enabled" value="1" {% if profile.enabled %}checked{% endif %}>
            <label class="custom-control-label" for="xnewsEnabled">Блок включен</label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><b>Область показа и категории</b></div>
    <div class="card-body">
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsVisibility">Показывать блок</label>
        <div class="col-sm-8">
          <select id="xnewsVisibility" class="form-control" name="visibility_mode">
            <option value="0" {% if profile.visibility_mode == 0 %}selected{% endif %}>Везде</option>
            <option value="1" {% if profile.visibility_mode == 1 %}selected{% endif %}>Только на страницах категорий</option>
            <option value="2" {% if profile.visibility_mode == 2 %}selected{% endif %}>Только на страницах новостей</option>
            <option value="3" {% if profile.visibility_mode == 3 %}selected{% endif %}>На страницах категорий и новостей</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsVisibilityCategories">Категории для ограничения показа</label>
        <div class="col-sm-8">
          <input id="xnewsVisibilityCategories" class="form-control" type="text" name="visibility_categories" value="{{ profile.visibility_categories|e('html_attr') }}">
          <small class="form-text text-muted">ID через запятую. Пустое значение означает все категории.</small>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsCategoryMode">Источник категорий новостей</label>
        <div class="col-sm-8">
          <select id="xnewsCategoryMode" class="form-control" name="category_mode">
            <option value="0" {% if profile.category_mode == 0 %}selected{% endif %}>Список категорий</option>
            <option value="1" {% if profile.category_mode == 1 %}selected{% endif %}>Текущая категория</option>
            <option value="2" {% if profile.category_mode == 2 %}selected{% endif %}>Список и текущая категория</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsCategories">Категории новостей</label>
        <div class="col-sm-8">
          <input id="xnewsCategories" class="form-control" type="text" name="categories" value="{{ profile.categories|e('html_attr') }}">
          <small class="form-text text-muted">ID через запятую. Используется, если выбран список категорий.</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><b>Фильтры и сортировка</b></div>
    <div class="card-body">
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsMainMode">Новости с главной</label>
        <div class="col-sm-8">
          <select id="xnewsMainMode" class="form-control" name="main_mode">
            <option value="0" {% if profile.main_mode == 0 %}selected{% endif %}>Все</option>
            <option value="1" {% if profile.main_mode == 1 %}selected{% endif %}>Только с главной</option>
            <option value="2" {% if profile.main_mode == 2 %}selected{% endif %}>Только не с главной</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsPinMode">Закреплённые новости</label>
        <div class="col-sm-8">
          <select id="xnewsPinMode" class="form-control" name="pin_mode">
            <option value="0" {% if profile.pin_mode == 0 %}selected{% endif %}>Все</option>
            <option value="1" {% if profile.pin_mode == 1 %}selected{% endif %}>Только закреплённые</option>
            <option value="2" {% if profile.pin_mode == 2 %}selected{% endif %}>Только не закреплённые</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsFavMode">Закладки администратора</label>
        <div class="col-sm-8">
          <select id="xnewsFavMode" class="form-control" name="fav_mode">
            <option value="0" {% if profile.fav_mode == 0 %}selected{% endif %}>Все новости</option>
            <option value="1" {% if profile.fav_mode == 1 %}selected{% endif %}>Только из закладок</option>
            <option value="2" {% if profile.fav_mode == 2 %}selected{% endif %}>Не из закладок</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsOrder">Порядок вывода</label>
        <div class="col-sm-8">
          <select id="xnewsOrder" class="form-control" name="order_by">
            <option value="viewed" {% if profile.order_by == 'viewed' %}selected{% endif %}>Самые просматриваемые</option>
            <option value="commented" {% if profile.order_by == 'commented' %}selected{% endif %}>Самые комментируемые</option>
            <option value="random" {% if profile.order_by == 'random' %}selected{% endif %}>Случайные</option>
            <option value="last" {% if profile.order_by == 'last' %}selected{% endif %}>Последние</option>
            <option value="first" {% if profile.order_by == 'first' %}selected{% endif %}>Сначала старые</option>
            <option value="editdate" {% if profile.order_by == 'editdate' %}selected{% endif %}>По дате редактирования</option>
            <option value="rating" {% if profile.order_by == 'rating' %}selected{% endif %}>По рейтингу</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsMaxAge">Публикации за последние N дней</label>
        <div class="col-sm-8"><input id="xnewsMaxAge" class="form-control" type="number" min="0" name="max_age" value="{{ profile.max_age }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsCount">Количество новостей</label>
        <div class="col-sm-8"><input id="xnewsCount" class="form-control" type="number" min="1" name="news_count" required value="{{ profile.news_count }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="xnewsSkip">Пропустить первых новостей</label>
        <div class="col-sm-8"><input id="xnewsSkip" class="form-control" type="number" min="0" name="news_skip" value="{{ profile.news_skip }}"></div>
      </div>
      <div class="mb-3 row">
        <div class="col-sm-4">Дополнительные параметры</div>
        <div class="col-sm-8">
          <div class="custom-control custom-checkbox mb-2">
            <input type="hidden" name="skip_current" value="0">
            <input id="xnewsSkipCurrent" class="custom-control-input" type="checkbox" name="skip_current" value="1" {% if profile.skip_current %}checked{% endif %}>
            <label class="custom-control-label" for="xnewsSkipCurrent">Не показывать текущую новость (отключает кеширование)</label>
          </div>
          <div class="custom-control custom-checkbox mb-2">
            <input type="hidden" name="extract_embedded_items" value="0">
            <input id="xnewsEmbedded" class="custom-control-input" type="checkbox" name="extract_embedded_items" value="1" {% if profile.extract_embedded_items %}checked{% endif %}>
            <label class="custom-control-label" for="xnewsEmbedded">Извлекать изображения из текста новости</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="hidden" name="show_no_news" value="0">
            <input id="xnewsShowEmpty" class="custom-control-input" type="checkbox" name="show_no_news" value="1" {% if profile.show_no_news %}checked{% endif %}>
            <label class="custom-control-label" for="xnewsShowEmpty">Показывать блок, если новостей нет</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="text-center mb-4">
    <button type="submit" class="btn btn-success">{{ mode == 'add' ? 'Добавить блок' : 'Сохранить блок' }}</button>
    <a class="btn btn-secondary" href="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=list">Отмена</a>
  </div>
</form>
