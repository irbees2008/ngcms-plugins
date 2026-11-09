<form method="post" action="admin.php?mod=extra-config&amp;plugin=rss_import&amp;action=save">
  <input type="hidden" name="id" value="{{ profile.id }}">
  <input type="hidden" name="token" value="{{ token }}">
  <div class="card mb-4">
    <div class="card-header"><b>{{ mode == 'add' ? 'Новая RSS-лента' : 'Параметры RSS-ленты' }}</b></div>
    <div class="card-body">
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssProfileId">ID блока</label>
        <div class="col-sm-8">
          <input id="rssProfileId" class="form-control" type="text" name="profile_id" maxlength="100" pattern="[A-Za-z0-9_-]+" required value="{{ profile.profile_id|e('html_attr') }}">
          <small class="form-text text-muted">Используется в Twig-вызове callPlugin('rss_import.show', {'id': 'ID-блока'}).</small>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssTitle">Название блока</label>
        <div class="col-sm-8"><input id="rssTitle" class="form-control" type="text" name="title" maxlength="190" required value="{{ profile.title|e('html_attr') }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssFeedUrl">URL RSS-ленты</label>
        <div class="col-sm-8"><input id="rssFeedUrl" class="form-control" type="url" name="feed_url" maxlength="2000" required value="{{ profile.feed_url|e('html_attr') }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssTemplate">Каталог шаблонов</label>
        <div class="col-sm-8">
          <select id="rssTemplate" class="form-control" name="template" required>
            {% for option in template_options %}
              <option value="{{ option.value|e('html_attr') }}" {% if profile.template == option.value %}selected{% endif %}>{{ option.label|e('html') }}</option>
            {% endfor %}
          </select>
          <small class="form-text text-muted">В каталоге должны находиться <code>rss.tpl</code> и <code>entries.tpl</code>. Сначала ищется шаблон активной темы, затем шаблон плагина.</small>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssEnabled">Состояние</label>
        <div class="col-sm-8">
          <input type="hidden" name="enabled" value="0">
          <div class="custom-control custom-switch">
            <input id="rssEnabled" class="custom-control-input" type="checkbox" name="enabled" value="1" {% if profile.enabled %}checked{% endif %}>
            <label class="custom-control-label" for="rssEnabled">Блок включен</label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><b>Параметры отображения</b></div>
    <div class="card-body">
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssItemCount">Количество новостей</label>
        <div class="col-sm-8"><input id="rssItemCount" class="form-control" type="number" min="1" max="100" name="item_count" required value="{{ profile.item_count }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssTitleLength">Максимальная длина заголовка</label>
        <div class="col-sm-8"><input id="rssTitleLength" class="form-control" type="number" min="1" max="1000" name="title_length" required value="{{ profile.title_length }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssShortLength">Максимальная длина описания</label>
        <div class="col-sm-8"><input id="rssShortLength" class="form-control" type="number" min="1" max="10000" name="short_length" required value="{{ profile.short_length }}"></div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="rssImageSource">Источник изображения</label>
        <div class="col-sm-8">
          <select id="rssImageSource" class="form-control" name="image_source">
            <option value="enclosure" {% if profile.image_source == 'enclosure' %}selected{% endif %}>Вложение RSS (enclosure)</option>
            <option value="desc" {% if profile.image_source == 'desc' %}selected{% endif %}>Из описания новости</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <div class="col-sm-4">Дополнительные параметры</div>
        <div class="col-sm-8">
          <div class="custom-control custom-checkbox mb-2">
            <input type="hidden" name="include_content" value="0">
            <input id="rssIncludeContent" class="custom-control-input" type="checkbox" name="include_content" value="1" {% if profile.include_content %}checked{% endif %}>
            <label class="custom-control-label" for="rssIncludeContent">Показывать короткое описание новости</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="hidden" name="show_image" value="0">
            <input id="rssShowImage" class="custom-control-input" type="checkbox" name="show_image" value="1" {% if profile.show_image %}checked{% endif %}>
            <label class="custom-control-label" for="rssShowImage">Показывать изображение карточки</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="text-center mb-4">
    <button type="submit" class="btn btn-success">{{ mode == 'add' ? 'Добавить RSS-ленту' : 'Сохранить RSS-ленту' }}</button>
    <a class="btn btn-secondary" href="admin.php?mod=extra-config&amp;plugin=rss_import&amp;action=list">Отмена</a>
  </div>
</form>
