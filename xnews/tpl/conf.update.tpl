<div class="alert alert-warning">
  {% if show_update_button %}
    <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Требуется обновление Xnews</h5>
    <p>В базе пока нет блоков, а старые настройки найдены в plugdata.php. Нажмите кнопку, чтобы перенести их в таблицу плагина.</p>
    <form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=update">
      <input type="hidden" name="token" value="{{ token }}">
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-refresh" aria-hidden="true"></i> Обновить плагин
      </button>
    </form>
  {% else %}
    <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Таблица Xnews не найдена</h5>
    <p>Старые блоки для переноса не обнаружены. Выполните стандартную установку плагина из раздела управления плагинами.</p>
  {% endif %}
</div>
