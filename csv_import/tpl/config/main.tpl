<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">{{ current_title }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="admin.php?mod=extras">{{ lang['csv_import:nav_plugins'] }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ current_title }}</li>
            </ol>
        </div>
    </div>
</div>
<div class="container-fluid">
    <ul class="nav nav-tabs nav-fill mb-3 d-md-flex d-block" role="tablist">
        <li class="nav-item">
            <a href="admin.php?mod=extra-config&amp;plugin=csv_import"
               class="nav-link{% if active == 'files' %} active{% endif %}">
                <i class="fa fa-upload mr-1"></i>{{ lang['csv_import:tab_files'] }}
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=settings"
               class="nav-link{% if active == 'settings' %} active{% endif %}">
                <i class="fa fa-cog mr-1"></i>{{ lang['csv_import:tab_settings'] }}
            </a>
        </li>
    </ul>
    {{ entries|raw }}
</div>
