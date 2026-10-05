<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">{{ current_title }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="admin.php?mod=extras">{{ lang['csv_reimport:nav_plugins'] }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ current_title }}</li>
            </ol>
        </div>
    </div>
</div>
<div class="container-fluid">
    {{ entries|raw }}
</div>
