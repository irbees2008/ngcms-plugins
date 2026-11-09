<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">RSS Import</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">RSS Import - {{ action }}</li>
      </ol>
    </div>
  </div>
</div>
<div class="card plugin-settings-card">
  <h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
    <span>RSS Import</span>{{ documentation_button|raw }}
  </h5>
  <div class="card-body plugin-settings-content">
    <ul class="nav nav-tabs nav-fill mb-3 d-md-flex d-block" role="tablist">
      <li class="nav-item">
        <a href="admin.php?mod=extra-config&amp;plugin=rss_import&amp;action=list" class="nav-link {{ tab_list_active }}">RSS-ленты</a>
      </li>
      <li class="nav-item">
        {% if can_add_profile %}
          <a href="admin.php?mod=extra-config&amp;plugin=rss_import&amp;action=add" class="nav-link {{ tab_add_active }}">Добавить ленту ({{ profile_count }}/{{ profile_limit }})</a>
        {% else %}
          <span class="nav-link disabled" aria-disabled="true" title="Достигнут лимит профилей">Добавить ленту ({{ profile_count }}/{{ profile_limit }})</span>
        {% endif %}
      </li>
      <li class="nav-item">
        <a href="admin.php?mod=extra-config&amp;plugin=rss_import&amp;action=settings" class="nav-link {{ tab_settings_active }}">Кеширование</a>
      </li>
    </ul>
    {{ entries|raw }}
  </div>
</div>
{{ documentation_modal|raw }}
