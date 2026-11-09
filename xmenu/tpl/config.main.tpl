<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">{{ lang['xmenu:page_title'] }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ lang['xmenu:page_title'] }}</li>
            </ol>
        </div>
    </div>
</div>
<div class="card plugin-settings-card">
    <h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
        <span>xmenu</span>{{ documentation_button|raw }}
    </h5>
    <div class="card-body plugin-settings-content">
<form action="admin.php?mod=extra-config&amp;plugin=xmenu" method="post">
    <input type="hidden" name="action" value="commit">
    <input type="hidden" name="token" value="{{ token }}">
    <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" href="#xmenu-settings" data-toggle="tab" role="tab">{{ lang['xmenu:tab.settings'] }}</a></li>
        <li class="nav-item"><a class="nav-link" href="#xmenu-categories" data-toggle="tab" role="tab">{{ lang['xmenu:tab.categories'] }}</a></li>
        <li class="nav-item"><a class="nav-link" href="#xmenu-static" data-toggle="tab" role="tab">{{ lang['xmenu:tab.static_pages'] }}</a></li>
    </ul>
    <div class="tab-content">
        <section class="tab-pane fade show active" id="xmenu-settings" role="tabpanel">
            <div class="form-group">
                <label for="xmenu-menu-count">{{ lang['xmenu:menu_count'] }}</label>
                <input class="form-control" type="number" id="xmenu-menu-count" name="menu_count" value="{{ menu_count }}" min="1">
                <small class="form-text text-muted">{{ lang['xmenu:menu_count#help'] }}</small>
            </div>
            <div class="form-group">
                <label for="xmenu-source">{{ lang['xmenu:localsource'] }}</label>
                <select class="form-control" id="xmenu-source" name="localsource">
                    <option value="0"{% if local_source == 0 %} selected{% endif %}>{{ lang['xmenu:lsrc_site'] }}</option>
                    <option value="1"{% if local_source == 1 %} selected{% endif %}>{{ lang['xmenu:lsrc_plugin'] }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="xmenu-cache">{{ lang['xmenu:cache'] }}</label>
                <select class="form-control" id="xmenu-cache" name="cache">
                    <option value="1"{% if cache_enabled == 1 %} selected{% endif %}>{{ lang['xmenu:cache_yes'] }}</option>
                    <option value="0"{% if cache_enabled == 0 %} selected{% endif %}>{{ lang['xmenu:cache_no'] }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="xmenu-cache-expire">{{ lang['xmenu:cache_expire'] }}</label>
                <input class="form-control" type="number" id="xmenu-cache-expire" name="cacheExpire" value="{{ cache_expire }}" min="0">
            </div>
        </section>
        <section class="tab-pane fade" id="xmenu-categories" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th scope="col">{{ lang['xmenu:th.category'] }}</th>{{ menu_headers|raw }}</tr></thead>
                    <tbody>{{ category_rows|raw }}</tbody>
                </table>
            </div>
        </section>
        <section class="tab-pane fade" id="xmenu-static" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th scope="col">{{ lang['xmenu:th.page'] }}</th>{{ menu_headers|raw }}</tr></thead>
                    <tbody>{{ static_rows|raw }}</tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="mt-3 text-right">
        <button class="btn btn-primary" type="submit">{{ lang['xmenu:button.save'] }}</button>
    </div>
</form>
</div>
</div>
{{ documentation_modal|raw }}
