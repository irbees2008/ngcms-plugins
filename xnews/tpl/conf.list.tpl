<div class="table-responsive">
  {% if profile_count > profile_limit %}
    <div class="alert alert-warning">
      Сохранено {{ profile_count }} профилей при лимите {{ profile_limit }}. Удалите лишние профили, чтобы снова добавлять блоки.
    </div>
  {% elseif not can_add_profile %}
    <div class="alert alert-info">
      Достигнут лимит: {{ profile_count }} из {{ profile_limit }} блоков. Чтобы добавить блок, сначала удалите существующий.
    </div>
  {% endif %}
  <table class="table table-sm table-striped align-middle">
    <thead class="thead-dark">
      <tr>
        <th>Порядок</th>
        <th>ID блока</th>
        <th>Шаблон</th>
        <th>Количество</th>
        <th>Состояние</th>
        <th class="text-right">Действия</th>
      </tr>
    </thead>
    <tbody>
      {% for item in items %}
        <tr>
          <td class="text-nowrap">
            <form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=move_up" class="d-inline">
              <input type="hidden" name="id" value="{{ item.id }}">
              <input type="hidden" name="token" value="{{ token }}">
              <button type="submit" class="btn btn-sm btn-outline-primary" title="Переместить вверх" aria-label="Переместить вверх"><i class="fa fa-arrow-up"></i></button>
            </form>
            <form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=move_down" class="d-inline">
              <input type="hidden" name="id" value="{{ item.id }}">
              <input type="hidden" name="token" value="{{ token }}">
              <button type="submit" class="btn btn-sm btn-outline-primary" title="Переместить вниз" aria-label="Переместить вниз"><i class="fa fa-arrow-down"></i></button>
            </form>
          </td>
          <td>{{ item.name|e('html') }}</td>
          <td>{{ item.template|e('html') }}</td>
          <td>{{ item.count }}</td>
          <td>{{ item.enabled ? 'Включен' : 'Выключен' }}</td>
          <td class="text-right text-nowrap">
            <form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=toggle" class="d-inline">
              <input type="hidden" name="id" value="{{ item.id }}">
              <input type="hidden" name="token" value="{{ token }}">
              <button type="submit" class="btn btn-sm {{ item.enabled ? 'btn-outline-success' : 'btn-outline-secondary' }}" title="{{ item.enabled ? 'Выключить' : 'Включить' }}" aria-label="{{ item.enabled ? 'Выключить' : 'Включить' }}">
                <i class="fa fa-power-off"></i>
              </button>
            </form>
            <a href="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=edit&amp;id={{ item.id }}" class="btn btn-sm btn-outline-primary" title="Редактировать" aria-label="Редактировать"><i class="fa fa-pencil"></i></a>
            <form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=delete" class="d-inline" onsubmit="return confirm('Удалить блок {{ item.name|e('js') }}?');">
              <input type="hidden" name="id" value="{{ item.id }}">
              <input type="hidden" name="token" value="{{ token }}">
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить" aria-label="Удалить"><i class="fa fa-trash"></i></button>
            </form>
          </td>
        </tr>
      {% else %}
        <tr><td colspan="6" class="text-center text-muted">Блоки ещё не добавлены.</td></tr>
      {% endfor %}
    </tbody>
  </table>
</div>
