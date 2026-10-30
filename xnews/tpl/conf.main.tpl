<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">Xnews</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Xnews - {{ action }}</li>
      </ol>
    </div>
  </div>
</div>
<div class="card plugin-settings-card">
  <h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
    <span>Xnews</span>{{ documentation_button|raw }}
  </h5>
  <div class="card-body plugin-settings-content">
    {% if show_update_button %}
      <form method="post" action="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=update" class="d-flex justify-content-end mb-3">
        <input type="hidden" name="token" value="{{ update_token }}">
        <button type="submit" class="btn btn-outline-primary">
          <i class="fa fa-refresh" aria-hidden="true"></i> Обновить плагин
        </button>
      </form>
    {% endif %}
    <ul class="nav nav-tabs nav-fill mb-3 d-md-flex d-block" role="tablist">
      <li class="nav-item">
        <a href="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=list" class="nav-link {{ tab_list_active }}">Блоки</a>
      </li>
      <li class="nav-item">
        {% if can_add_profile %}
          <a href="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=add" class="nav-link {{ tab_add_active }}">Добавить блок ({{ profile_count }}/{{ profile_limit }})</a>
        {% else %}
          <span class="nav-link disabled" aria-disabled="true" title="Достигнут лимит блоков">Добавить блок ({{ profile_count }}/{{ profile_limit }})</span>
        {% endif %}
      </li>
      <li class="nav-item">
        <a href="admin.php?mod=extra-config&amp;plugin=xnews&amp;action=settings" class="nav-link {{ tab_settings_active }}">Кеширование</a>
      </li>
    </ul>
    {{ entries|raw }}
  </div>
</div>
{{ documentation_modal|raw }}
