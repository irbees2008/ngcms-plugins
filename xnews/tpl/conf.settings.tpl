<form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=settings">
  <input type="hidden" name="token" value="{{ token }}">
  <div class="card">
    <div class="card-header"><b>Настройки кеширования</b></div>
    <div class="card-body">
      <div class="mb-3 row">
        <label class="col-sm-6 col-form-label" for="xnewsCache">Использовать кеширование данных</label>
        <div class="col-sm-6">
          <select id="xnewsCache" class="form-control" name="cache">
            <option value="1" {% if cache == 1 %}selected{% endif %}>Да</option>
            <option value="0" {% if cache == 0 %}selected{% endif %}>Нет</option>
          </select>
        </div>
      </div>
      <div class="mb-3 row">
        <label class="col-sm-6 col-form-label" for="xnewsCacheExpire">Период обновления кеша, секунд</label>
        <div class="col-sm-6">
          <input id="xnewsCacheExpire" class="form-control" type="number" min="1" max="86400" name="cache_expire" value="{{ cache_expire }}" required>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-3">
    <button type="submit" class="btn btn-success">Сохранить настройки</button>
  </div>
</form>
