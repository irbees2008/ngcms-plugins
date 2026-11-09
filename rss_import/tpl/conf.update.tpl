<div class="alert alert-warning">
  {% if has_legacy_profiles %}
    <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Требуется обновление RSS Import</h5>
    <p>Старые настройки RSS-лент будут перенесены в отдельные профили. Перед переносом будет создана резервная копия файла конфигурации.</p>
  {% else %}
    <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Требуется установка профилей RSS</h5>
    <p>Для нового интерфейса будет создана таблица профилей RSS.</p>
  {% endif %}
  <form method="post" action="admin.php?mod=extra-config&amp;plugin=rss_import&amp;action=update">
    <input type="hidden" name="token" value="{{ token }}">
    <button type="submit" class="btn btn-primary">
      <i class="fa fa-refresh" aria-hidden="true"></i> Обновить плагин
    </button>
  </form>
</div>
