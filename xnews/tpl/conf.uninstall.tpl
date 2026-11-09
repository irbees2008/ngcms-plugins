{% if mode == 'confirm' %}
  <div class="card border-danger">
    <div class="card-header bg-danger text-white">
      <h5 class="mb-0"><i class="fa fa-exclamation-triangle"></i> Удаление Xnews</h5>
    </div>
    <div class="card-body">
      <p>Будет удалён плагин Xnews, все профили и таблица <code>{{ table_name|e('html') }}</code>. Настройки Xnews также будут удалены без возможности восстановления.</p>
      <form method="post" action="{{ php_self }}?mod=extra-config">
        <input type="hidden" name="plugin" value="xnews">
        <input type="hidden" name="stype" value="deinstall">
        <input type="hidden" name="action" value="commit">
        <input type="hidden" name="token" value="{{ token }}">
        <button type="submit" class="btn btn-danger">Удалить плагин и данные</button>
        <a class="btn btn-secondary" href="{{ php_self }}?mod=extras">Отмена</a>
      </form>
    </div>
  </div>
{% else %}
  <div class="card border-success">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0"><i class="fa fa-check-circle"></i> Xnews удалён</h5>
    </div>
    <div class="card-body">
      <p>Плагин, его профили и таблица удалены.</p>
      <a class="btn btn-success" href="{{ php_self }}?mod=extras">Вернуться к списку плагинов</a>
    </div>
  </div>
{% endif %}
